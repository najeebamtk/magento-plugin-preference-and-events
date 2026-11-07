<?php

namespace Codilar\ReturnManagement\Controller\Adminhtml\Request;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest\CollectionFactory;
use Codilar\ReturnManagement\Model\ReturnRequest;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Grid "Actions > Change Status" for all ticked rows.
 */
class MassStatus extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Codilar_ReturnManagement::request_save';

    private Filter $filter;
    private CollectionFactory $collectionFactory;
    private ReturnRequestResource $returnRequestResource;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ReturnRequestResource $returnRequestResource
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->returnRequestResource = $returnRequestResource;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create()->setPath('*/*/index');

        $status = (string) $this->getRequest()->getParam('status');
        if (!array_key_exists($status, ReturnRequest::getStatuses())) {
            $this->messageManager->addErrorMessage(__('Invalid status.'));
            return $resultRedirect;
        }

        try {
            // Filter turns the ticked rows (or "Select All" + grid filters) into a collection
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $updated = 0;
            foreach ($collection as $returnRequest) {
                $returnRequest->setStatus($status);
                $this->returnRequestResource->save($returnRequest);
                $updated++;
            }
            $this->messageManager->addSuccessMessage(
                __('%1 return request(s) changed to "%2".', $updated, ReturnRequest::getStatuses()[$status])
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect;
    }
}
