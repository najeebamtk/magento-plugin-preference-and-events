<?php

namespace Codilar\ReturnManagement\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

/**
 * Grid data provider. Same as Magento's, except the "Search by keyword" box:
 * Magento's default keyword search needs a FULLTEXT index on the main table,
 * but our searchable values (order #, customer name, email) come from sales_order.
 * So the keyword is turned into a LIKE filter, handled in Grid\Collection::addFieldToFilter().
 */
class ReturnRequestDataProvider extends DataProvider
{
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() === 'fulltext') {
            // escape % and _ so they are searched as normal characters
            $keyword = addcslashes(trim((string) $filter->getValue()), '%_');
            $filter->setConditionType('like');
            $filter->setValue('%' . $keyword . '%');
        }

        parent::addFilter($filter);
    }
}
