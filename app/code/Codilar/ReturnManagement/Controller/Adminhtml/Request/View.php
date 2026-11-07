<?php

namespace Codilar\ReturnManagement\Controller\Adminhtml\Request;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Codilar\ReturnManagement\Model\ReturnRequestFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Detail page of one return request: order, items, comments and status form.
 */
class View extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_ReturnManagement::requests';

    private PageFactory $resultPageFactory;
    private ReturnRequestFactory $returnRequestFactory;
    private ReturnRequestResource $returnRequestResource;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ReturnRequestFactory $returnRequestFactory,
        ReturnRequestResource $returnRequestResource
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->returnRequestFactory = $returnRequestFactory;
        $this->returnRequestResource = $returnRequestResource;
    }

    public function execute()
    {
        $returnRequest = $this->returnRequestFactory->create();
        $this->returnRequestResource->load($returnRequest, (int) $this->getRequest()->getParam('id'));

        if (!$returnRequest->getId()) {
            $this->messageManager->addErrorMessage(__('This return request no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codilar_ReturnManagement::requests');
        $resultPage->getConfig()->getTitle()->prepend(__('Return Request #%1', $returnRequest->getId()));

        // Hand the loaded request to the block, so it does not load it again
        $resultPage->getLayout()->getBlock('return.request.view')->setReturnRequest($returnRequest);

        return $resultPage;
    }
}
