<?php

namespace Codilar\ReturnManagement\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Return Management > Return Requests (the grid page).
 */
class Index extends Action implements HttpGetActionInterface
{
    // Admin users without this ACL permission get "Sorry, you need permissions..."
    public const ADMIN_RESOURCE = 'Codilar_ReturnManagement::requests';

    private PageFactory $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_ReturnManagement::requests');
        $resultPage->getConfig()->getTitle()->prepend(__('Return Requests'));

        return $resultPage;
    }
}
