<?php

namespace Codilar\ReturnManagement\Block\Adminhtml\Request;

use Codilar\ReturnManagement\Model\ReturnRequest;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

/**
 * Data for the admin "Add Return" form (templates/request/create.phtml).
 * The order dropdown is filled by AJAX from the Orders controller.
 */
class Create extends Template
{
    private CustomerCollectionFactory $customerCollectionFactory;

    public function __construct(
        Context $context,
        CustomerCollectionFactory $customerCollectionFactory,
        array $data = []
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * All customers for the dropdown, as [id => "Name (email)"].
     */
    public function getCustomerOptions(): array
    {
        $customers = $this->customerCollectionFactory->create()
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->setOrder('firstname', 'asc');

        $options = [];
        foreach ($customers as $customer) {
            $options[(int) $customer->getId()] = $customer->getName() . ' (' . $customer->getEmail() . ')';
        }
        return $options;
    }

    public function getReasons(): array
    {
        return ReturnRequest::getReasons();
    }

    public function getOrdersUrl(): string
    {
        return $this->getUrl('*/*/orders');
    }

    public function getCreateUrl(): string
    {
        return $this->getUrl('*/*/create');
    }

    public function getBackUrl(): string
    {
        return $this->getUrl('*/*/index');
    }
}
