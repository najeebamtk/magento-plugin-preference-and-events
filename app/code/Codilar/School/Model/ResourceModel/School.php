<?php

namespace Codilar\School\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Talks to the codilar_school table. "entity_id" is the primary key.
 */
class School extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('codilar_school', 'entity_id');
    }
}
