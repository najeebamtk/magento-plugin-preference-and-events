<?php

namespace Codilar\School\Model\ResourceModel\School;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * A list of schools. Used by the list page and by the "code is unique" check.
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Codilar\School\Model\School::class,
            \Codilar\School\Model\ResourceModel\School::class
        );
    }
}
