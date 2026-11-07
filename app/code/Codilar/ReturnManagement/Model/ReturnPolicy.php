<?php

namespace Codilar\ReturnManagement\Model;

use Magento\Framework\Model\AbstractModel;

class ReturnPolicy extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\ReturnPolicy::class);
    }
}
