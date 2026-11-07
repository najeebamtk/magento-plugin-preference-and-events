<?php

namespace Codilar\ReturnManagement\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ReturnPolicy extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('codilar_return_policy', 'policy_id');
    }
}
