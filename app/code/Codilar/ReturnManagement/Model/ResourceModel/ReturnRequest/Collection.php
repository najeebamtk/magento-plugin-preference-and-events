<?php

namespace Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Codilar\ReturnManagement\Model\ReturnRequest;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'request_id';

    protected function _construct()
    {
        $this->_init(ReturnRequest::class, ReturnRequestResource::class);
    }
}
