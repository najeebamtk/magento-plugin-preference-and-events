<?php

namespace Codilar\ReturnManagement\Model\ResourceModel\ReturnPolicy;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnPolicy as ReturnPolicyResource;
use Codilar\ReturnManagement\Model\ReturnPolicy;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'policy_id';

    protected function _construct()
    {
        $this->_init(ReturnPolicy::class, ReturnPolicyResource::class);
    }
}
