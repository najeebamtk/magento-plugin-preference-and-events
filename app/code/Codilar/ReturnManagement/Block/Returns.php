<?php

namespace Codilar\ReturnManagement\Block;

use Codilar\ReturnManagement\Model\Eligibility;
use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest\Collection as ReturnRequestCollection;
use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest\CollectionFactory as ReturnRequestCollectionFactory;
use Codilar\ReturnManagement\Model\ReturnRequest;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

/**
 * Data for the customer's My Returns page (list.phtml) and return form (form.phtml).
 * The customer ID always comes from the logged-in session, never from the browser.
 */
class Returns extends Template
{
    private CustomerSession $customerSession;
    private ReturnRequestCollectionFactory $returnRequestCollectionFactory;
    private Eligibility $eligibility;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ReturnRequestCollectionFactory $returnRequestCollectionFactory,
        Eligibility $eligibility,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->returnRequestCollectionFactory = $returnRequestCollectionFactory;
        $this->eligibility = $eligibility;
        parent::__construct($context, $data);
    }

    /**
     * Only this customer's orders that can be returned right now (rules are in Eligibility).
     *
     * @return Order[]
     */
    public function getEligibleOrders(): array
    {
        return $this->eligibility->getEligibleOrders((int) $this->customerSession->getCustomerId());
    }

    /**
     * Only this customer's return requests, with the order number from sales_order.
     */
    public function getReturnRequests(): ReturnRequestCollection
    {
        $collection = $this->returnRequestCollectionFactory->create()
            ->addFieldToFilter('main_table.customer_id', (int) $this->customerSession->getCustomerId())
            ->setOrder('main_table.created_at', 'desc');
        $collection->join(['so' => $collection->getTable('sales_order')], 'so.entity_id = main_table.order_id', ['increment_id']);

        return $collection;
    }

    /**
     * Order set by the Form controller (already ownership + eligibility checked).
     */
    public function getOrder(): Order
    {
        return $this->getData('order');
    }

    public function getReturnItems(): array
    {
        return $this->eligibility->getReturnItems($this->getOrder());
    }

    public function getReturnDeadline(): ?\DateTime
    {
        return $this->eligibility->getReturnDeadline($this->getOrder());
    }

    public function getReasons(): array
    {
        return ReturnRequest::getReasons();
    }

    public function getReasonLabel(string $code): string
    {
        return (string) (ReturnRequest::getReasons()[$code] ?? $code);
    }

    public function getStatusLabel(string $code): string
    {
        return (string) (ReturnRequest::getStatuses()[$code] ?? $code);
    }

    public function getOrderViewUrl(int $orderId): string
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    public function getFormUrl(int $orderId): string
    {
        return $this->getUrl('returns/index/form', ['order_id' => $orderId]);
    }

    public function getSubmitUrl(): string
    {
        return $this->getUrl('returns/index/submit');
    }

    public function getBackUrl(): string
    {
        return $this->getUrl('returns/index/index');
    }
}
