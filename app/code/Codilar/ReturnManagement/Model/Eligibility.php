<?php

namespace Codilar\ReturnManagement\Model;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnPolicy\CollectionFactory as PolicyCollectionFactory;
use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest\CollectionFactory as RequestCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface as OrderCollectionFactory;

/**
 * All return rules in one place. Used by the My Returns page, the form page and the submit action.
 *
 * Note: a Magento shipment means the order was SHIPPED, not that it was delivered,
 * so this is "shipped-order eligibility" and the return window starts at the latest shipment date.
 */
class Eligibility
{
    private OrderRepositoryInterface $orderRepository;
    private PolicyCollectionFactory $policyCollectionFactory;
    private RequestCollectionFactory $requestCollectionFactory;
    private OrderCollectionFactory $orderCollectionFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PolicyCollectionFactory $policyCollectionFactory,
        RequestCollectionFactory $requestCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->policyCollectionFactory = $policyCollectionFactory;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Only this customer's orders that can be returned right now.
     * SQL keeps only "complete" orders; getIneligibleReason() checks the remaining rules
     * (shipment, policy, return window, no existing request).
     * Used by My Returns (customer) and Add Return (admin).
     *
     * @return Order[]
     */
    public function getEligibleOrders(int $customerId): array
    {
        if ($customerId <= 0) {
            return [];
        }

        $orders = $this->orderCollectionFactory->create($customerId)
            ->addFieldToSelect('*')
            ->addFieldToFilter('state', Order::STATE_COMPLETE)
            ->setOrder('created_at', 'desc');

        $eligible = [];
        foreach ($orders as $order) {
            if ($this->getIneligibleReason($order) === null) {
                $eligible[] = $order;
            }
        }
        return $eligible;
    }

    /**
     * Ownership check: returns the order only if it belongs to this customer.
     */
    public function getCustomerOrder(int $orderId, int $customerId): ?Order
    {
        if ($orderId <= 0 || $customerId <= 0) {
            return null;
        }

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return (int) $order->getCustomerId() === $customerId ? $order : null;
    }

    /**
     * Null = order can be returned. Otherwise the message explaining why not.
     */
    public function getIneligibleReason(Order $order): ?string
    {
        if ($order->getState() !== Order::STATE_COMPLETE) {
            return (string) __('Only completed orders can be returned.');
        }
        if (!$order->hasShipments()) {
            return (string) __('This order has not been shipped yet.');
        }
        if ($this->hasReturnRequest($order)) {
            return (string) __('A return request already exists for this order.');
        }

        $windowDays = $this->getReturnWindowDays($order);
        if ($windowDays === null) {
            return (string) __('One or more products in this order cannot be returned.');
        }

        $deadline = $this->getReturnDeadline($order);
        if ($deadline < new \DateTime('now', new \DateTimeZone('UTC'))) {
            return (string) __('The %1-day return period for this order has ended.', $windowDays);
        }

        return null;
    }

    /**
     * Shortest return window of all products in the order,
     * or null if any product has no policy or is not returnable.
     */
    public function getReturnWindowDays(Order $order): ?int
    {
        $productIds = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $productIds[] = (int) $item->getProductId();
        }
        if (!$productIds) {
            return null;
        }

        $policies = $this->policyCollectionFactory->create()
            ->addFieldToFilter('product_id', ['in' => $productIds])
            ->addFieldToFilter('is_returnable', 1);

        $daysByProduct = [];
        foreach ($policies as $policy) {
            $daysByProduct[(int) $policy->getProductId()] = (int) $policy->getReturnWindowDays();
        }

        foreach ($productIds as $productId) {
            if (!isset($daysByProduct[$productId])) {
                return null;
            }
        }

        return min($daysByProduct);
    }

    /**
     * Latest shipment date + return window (UTC). Null if not shipped or not returnable.
     */
    public function getReturnDeadline(Order $order): ?\DateTime
    {
        $windowDays = $this->getReturnWindowDays($order);
        $lastShipment = null;
        foreach ($order->getShipmentsCollection() as $shipment) {
            if ($lastShipment === null || $shipment->getCreatedAt() > $lastShipment) {
                $lastShipment = $shipment->getCreatedAt();
            }
        }
        if ($windowDays === null || $lastShipment === null) {
            return null;
        }

        $deadline = new \DateTime($lastShipment, new \DateTimeZone('UTC'));
        return $deadline->modify('+' . $windowDays . ' days');
    }

    /**
     * Rows for codilar_return_request_item: the whole order, with SKU/name snapshots.
     * getAllVisibleItems() skips configurable "child" rows, so a product is not listed twice.
     */
    public function getReturnItems(Order $order): array
    {
        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                'order_item_id' => (int) $item->getItemId(),
                'product_id' => $item->getProductId() ? (int) $item->getProductId() : null,
                'product_sku' => (string) $item->getSku(),
                'product_name' => $item->getName(),
                'qty_requested' => (float) ($item->getQtyShipped() ?: $item->getQtyOrdered()),
            ];
        }
        return $items;
    }

    private function hasReturnRequest(Order $order): bool
    {
        return $this->requestCollectionFactory->create()
            ->addFieldToFilter('order_id', (int) $order->getId())
            ->getSize() > 0;
    }
}
