<?php

namespace Codilar\ReturnManagement\Block\Adminhtml\Request;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Codilar\ReturnManagement\Model\ReturnRequest;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Data for the admin detail page (templates/request/view.phtml).
 * The controller sets the loaded request with setReturnRequest().
 */
class View extends Template
{
    private ReturnRequestResource $returnRequestResource;
    private OrderRepositoryInterface $orderRepository;
    private ?OrderInterface $order = null;

    public function __construct(
        Context $context,
        ReturnRequestResource $returnRequestResource,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->returnRequestResource = $returnRequestResource;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    public function getReturnRequest(): ReturnRequest
    {
        return $this->getData('return_request');
    }

    public function getOrder(): OrderInterface
    {
        if ($this->order === null) {
            $this->order = $this->orderRepository->get((int) $this->getReturnRequest()->getOrderId());
        }
        return $this->order;
    }

    public function getItems(): array
    {
        return $this->returnRequestResource->getItems((int) $this->getReturnRequest()->getId());
    }

    public function getReasonLabel(): string
    {
        $reason = $this->getReturnRequest()->getReason();
        return (string) (ReturnRequest::getReasons()[$reason] ?? $reason);
    }

    public function getStatuses(): array
    {
        return ReturnRequest::getStatuses();
    }

    public function getSaveUrl(): string
    {
        return $this->getUrl('*/*/save');
    }

    public function getBackUrl(): string
    {
        return $this->getUrl('*/*/index');
    }

    public function getOrderViewUrl(): string
    {
        return $this->getUrl('sales/order/view', ['order_id' => $this->getOrder()->getEntityId()]);
    }
}
