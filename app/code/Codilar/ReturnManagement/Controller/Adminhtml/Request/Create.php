<?php

namespace Codilar\ReturnManagement\Controller\Adminhtml\Request;

use Codilar\ReturnManagement\Model\Eligibility;
use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Codilar\ReturnManagement\Model\ReturnRequest;
use Codilar\ReturnManagement\Model\ReturnRequestFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Saves the return request created by an admin (POST from request/create.phtml).
 * Same rules as the customer's Submit action; only the customer comes from the form, not a session.
 */
class Create extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_ReturnManagement::request_create';

    private Eligibility $eligibility;
    private ReturnRequestFactory $returnRequestFactory;
    private ReturnRequestResource $returnRequestResource;

    public function __construct(
        Context $context,
        Eligibility $eligibility,
        ReturnRequestFactory $returnRequestFactory,
        ReturnRequestResource $returnRequestResource
    ) {
        parent::__construct($context);
        $this->eligibility = $eligibility;
        $this->returnRequestFactory = $returnRequestFactory;
        $this->returnRequestResource = $returnRequestResource;
    }

    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('customer_id');
        $orderId = (int) $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();

        // 1. Order must belong to the chosen customer
        $order = $this->eligibility->getCustomerOrder($orderId, $customerId);
        if (!$order) {
            $this->messageManager->addErrorMessage(__('Order not found for this customer.'));
            return $resultRedirect->setPath('*/*/new');
        }

        // 2. Reason must be one of our codes
        $reason = (string) $this->getRequest()->getParam('reason');
        if (!array_key_exists($reason, ReturnRequest::getReasons())) {
            $this->messageManager->addErrorMessage(__('Please select a return reason.'));
            return $resultRedirect->setPath('*/*/new');
        }

        // 3. Re-check every rule on the server (status, shipment, policy, window, duplicate)
        $ineligibleReason = $this->eligibility->getIneligibleReason($order);
        if ($ineligibleReason !== null) {
            $this->messageManager->addErrorMessage($ineligibleReason);
            return $resultRedirect->setPath('*/*/new');
        }

        $returnRequest = $this->returnRequestFactory->create();
        $returnRequest->setData([
            'customer_id' => (int) $order->getCustomerId(),
            'order_id' => (int) $order->getId(),
            'reason' => $reason,
            'comments' => trim((string) $this->getRequest()->getParam('comments')) ?: null,
            'status' => ReturnRequest::STATUS_PENDING,
        ]);

        // 4. Request + items saved in one transaction
        try {
            $this->returnRequestResource->saveWithItems($returnRequest, $this->eligibility->getReturnItems($order));
            $this->messageManager->addSuccessMessage(
                __('Return request #%1 created for order #%2.', $returnRequest->getId(), $order->getIncrementId())
            );
            return $resultRedirect->setPath('*/*/view', ['id' => $returnRequest->getId()]);
        } catch (AlreadyExistsException) {
            // The unique key on order_id caught a duplicate (e.g. double click)
            $this->messageManager->addErrorMessage(__('A return request already exists for this order.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not create the return request: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
