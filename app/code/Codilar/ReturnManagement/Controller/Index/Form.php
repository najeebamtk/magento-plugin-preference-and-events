<?php

namespace Codilar\ReturnManagement\Controller\Index;

use Codilar\ReturnManagement\Model\Eligibility;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Return request form for one order (/returns/index/form/order_id/X).
 */
class Form implements HttpGetActionInterface, AccountInterface
{
    private RequestInterface $request;
    private PageFactory $resultPageFactory;
    private RedirectFactory $resultRedirectFactory;
    private ManagerInterface $messageManager;
    private CustomerSession $customerSession;
    private Eligibility $eligibility;

    public function __construct(
        RequestInterface $request,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        CustomerSession $customerSession,
        Eligibility $eligibility
    ) {
        $this->request = $request;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->eligibility = $eligibility;
    }

    public function execute()
    {
        // Customer ID from the session; order_id from the URL is checked for ownership
        $order = $this->eligibility->getCustomerOrder(
            (int) $this->request->getParam('order_id'),
            (int) $this->customerSession->getCustomerId()
        );
        if (!$order) {
            $this->messageManager->addErrorMessage(__('Order not found.'));
            return $this->resultRedirectFactory->create()->setPath('returns/index/index');
        }

        $reason = $this->eligibility->getIneligibleReason($order);
        if ($reason !== null) {
            $this->messageManager->addErrorMessage($reason);
            return $this->resultRedirectFactory->create()->setPath('returns/index/index');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Request a Return for Order #%1', $order->getIncrementId()));
        $resultPage->getLayout()->getBlock('codilar.returns.form')->setOrder($order);

        // Keep "My Returns" highlighted in the sidebar
        $navigation = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigation) {
            $navigation->setActive('returns/index/index');
        }

        return $resultPage;
    }
}
