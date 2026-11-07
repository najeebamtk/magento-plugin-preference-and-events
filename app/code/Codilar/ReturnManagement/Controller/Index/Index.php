<?php

namespace Codilar\ReturnManagement\Controller\Index;

use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * My Account > My Returns.
 * AccountInterface = Magento's customer plugin sends guests to the login page before execute() runs.
 */
class Index implements HttpGetActionInterface, AccountInterface
{
    private PageFactory $resultPageFactory;

    public function __construct(PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Returns'));

        return $resultPage;
    }
}
