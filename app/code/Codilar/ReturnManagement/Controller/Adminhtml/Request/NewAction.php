<?php

namespace Codilar\ReturnManagement\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * "Add Return" form page (URL returnmanagement/request/new).
 * The class is called NewAction because "new" is a reserved word in PHP;
 * Magento maps the URL part "new" to NewAction automatically.
 */
class NewAction extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_ReturnManagement::request_create';

    private PageFactory $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_ReturnManagement::requests');
        $resultPage->getConfig()->getTitle()->prepend(__('Add Return'));

        return $resultPage;
    }
}
