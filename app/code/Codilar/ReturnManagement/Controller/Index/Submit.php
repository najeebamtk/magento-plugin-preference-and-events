<?php

namespace Codilar\ReturnManagement\Controller\Index;

use Codilar\ReturnManagement\Model\Eligibility;
use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Codilar\ReturnManagement\Model\ReturnRequest;
use Codilar\ReturnManagement\Model\ReturnRequestFactory;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Saves the return request + its items (POST from form.phtml).
 */
class Submit implements HttpPostActionInterface, AccountInterface
{
    private RequestInterface $request;
    private RedirectFactory $resultRedirectFactory;
    private ManagerInterface $messageManager;
    private CustomerSession $customerSession;
    private FormKeyValidator $formKeyValidator;
    private Eligibility $eligibility;
    private ReturnRequestFactory $returnRequestFactory;
    private ReturnRequestResource $returnRequestResource;
    private LoggerInterface $logger;

    public function __construct(
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        FormKeyValidator $formKeyValidator,
        Eligibility $eligibility,
        ReturnRequestFactory $returnRequestFactory,
        ReturnRequestResource $returnRequestResource,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->eligibility = $eligibility;
        $this->returnRequestFactory = $returnRequestFactory;
        $this->returnRequestResource = $returnRequestResource;
        $this->logger = $logger;
    }

    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create()->setPath('returns/index/index');

        // 1. Form key: the request really came from our form
        if (!$this->formKeyValidator->validate($this->request)) {
            $this->messageManager->addErrorMessage(__('Invalid form key. Please refresh the page and try again.'));
            return $redirect;
        }

        // 2. Customer ID only from the session; order must belong to that customer
        $customerId = (int) $this->customerSession->getCustomerId();
        $orderId = (int) $this->request->getParam('order_id');
        $order = $this->eligibility->getCustomerOrder($orderId, $customerId);
        if (!$order) {
            $this->messageManager->addErrorMessage(__('Order not found.'));
            return $redirect;
        }

        // 3. Reason must be one of our codes
        $reason = (string) $this->request->getParam('reason');
        if (!array_key_exists($reason, ReturnRequest::getReasons())) {
            $this->messageManager->addErrorMessage(__('Please select a return reason.'));
            return $redirect->setPath('returns/index/form', ['order_id' => $orderId]);
        }

        // 4. Re-check every rule on the server (status, shipment, policy, window, duplicate)
        $ineligibleReason = $this->eligibility->getIneligibleReason($order);
        if ($ineligibleReason !== null) {
            $this->messageManager->addErrorMessage($ineligibleReason);
            return $redirect;
        }

        $returnRequest = $this->returnRequestFactory->create();
        $returnRequest->setData([
            'customer_id' => $customerId,
            'order_id' => (int) $order->getId(),
            'reason' => $reason,
            'comments' => trim((string) $this->request->getParam('comments')) ?: null,
            'status' => ReturnRequest::STATUS_PENDING,
        ]);

        // 5. Request + items saved in one transaction
        try {
            $this->returnRequestResource->saveWithItems($returnRequest, $this->eligibility->getReturnItems($order));
            $this->messageManager->addSuccessMessage(
                __('Your return request for order #%1 has been submitted.', $order->getIncrementId())
            );
        } catch (AlreadyExistsException) {
            // The unique key on order_id caught a duplicate (e.g. double click)
            $this->messageManager->addErrorMessage(__('A return request already exists for this order.'));
        } catch (\Exception $e) {
            // Real error goes to var/log/system.log; the customer sees a friendly message
            $this->logger->error('Return request save failed: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Could not submit your return request. Please try again.'));
        }

        return $redirect;
    }
}
