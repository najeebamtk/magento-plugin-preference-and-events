<?php

namespace Codilar\ReturnManagement\Controller\Adminhtml\Request;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Codilar\ReturnManagement\Model\ReturnRequest;
use Codilar\ReturnManagement\Model\ReturnRequestFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Saves the new status chosen on the detail page.
 */
class Save extends Action implements HttpPostActionInterface
{
    // Separate permission: a role can view requests but not change them
    public const ADMIN_RESOURCE = 'Codilar_ReturnManagement::request_save';

    private ReturnRequestFactory $returnRequestFactory;
    private ReturnRequestResource $returnRequestResource;

    public function __construct(
        Context $context,
        ReturnRequestFactory $returnRequestFactory,
        ReturnRequestResource $returnRequestResource
    ) {
        parent::__construct($context);
        $this->returnRequestFactory = $returnRequestFactory;
        $this->returnRequestResource = $returnRequestResource;
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $status = (string) $this->getRequest()->getParam('status');
        $resultRedirect = $this->resultRedirectFactory->create();

        // Only accept one of our 4 known statuses
        if (!array_key_exists($status, ReturnRequest::getStatuses())) {
            $this->messageManager->addErrorMessage(__('Invalid status.'));
            return $resultRedirect->setPath('*/*/view', ['id' => $id]);
        }

        $returnRequest = $this->returnRequestFactory->create();
        $this->returnRequestResource->load($returnRequest, $id);
        if (!$returnRequest->getId()) {
            $this->messageManager->addErrorMessage(__('This return request no longer exists.'));
            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $returnRequest->setStatus($status);
            $this->returnRequestResource->save($returnRequest);
            $this->messageManager->addSuccessMessage(__('Status changed to "%1".', ReturnRequest::getStatuses()[$status]));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not save the status: %1', $e->getMessage()));
        }

        return $resultRedirect->setPath('*/*/view', ['id' => $id]);
    }
}
