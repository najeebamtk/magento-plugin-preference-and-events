<?php

namespace Codilar\ReturnManagement\Controller\Adminhtml\Request;

use Codilar\ReturnManagement\Model\Eligibility;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface as OrderCollectionFactory;

/**
 * AJAX for the Add Return form: all orders of one customer as JSON.
 * Called by create.phtml when the customer dropdown changes (?customer_id=5).
 * Orders that cannot be returned come back with "disabled" + the reason, so the dropdown greys them out.
 */
class Orders extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_ReturnManagement::request_create';

    private JsonFactory $resultJsonFactory;
    private OrderCollectionFactory $orderCollectionFactory;
    private Eligibility $eligibility;
    private TimezoneInterface $timezone;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        OrderCollectionFactory $orderCollectionFactory,
        Eligibility $eligibility,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->eligibility = $eligibility;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('customer_id');
        $result = [];

        if ($customerId > 0) {
            $orders = $this->orderCollectionFactory->create($customerId)
                ->addFieldToSelect('*')
                ->setOrder('created_at', 'desc');

            foreach ($orders as $order) {
                $ineligibleReason = $this->eligibility->getIneligibleReason($order);
                $label = sprintf(
                    '#%s - %s - %s - %s',
                    $order->getIncrementId(),
                    $this->timezone->formatDate($order->getCreatedAt(), \IntlDateFormatter::MEDIUM),
                    $order->formatPriceTxt($order->getGrandTotal()),
                    $order->getStatusLabel()
                );

                $result[] = [
                    'value' => (int) $order->getId(),
                    'label' => $ineligibleReason === null ? $label : $label . ' (' . $ineligibleReason . ')',
                    'disabled' => $ineligibleReason !== null,
                ];
            }
        }

        return $this->resultJsonFactory->create()->setData(['orders' => $result]);
    }
}
