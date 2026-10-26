<?php

namespace Codilar\School\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * One school record. Holds the data for a single row in codilar_school.
 */
class School extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Codilar\School\Model\ResourceModel\School::class);
    }
}
