<?php

namespace Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest\Grid;

use Codilar\ReturnManagement\Model\ResourceModel\ReturnRequest as ReturnRequestResource;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Rows for the admin grid: our request table + order number and customer from sales_order.
 */
class Collection extends SearchResult
{
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'codilar_return_request',
        $resourceModel = ReturnRequestResource::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $customerName = new \Zend_Db_Expr("CONCAT_WS(' ', so.customer_firstname, so.customer_lastname)");

        $this->getSelect()->join(
            ['so' => $this->getTable('sales_order')],
            'so.entity_id = main_table.order_id',
            [
                'increment_id',
                'customer_email',
                'customer_name' => $customerName,
                'order_status' => 'so.status',
            ]
        );

        // Store view of the order => its website
        $this->getSelect()->joinLeft(
            ['store' => $this->getTable('store')],
            'store.store_id = so.store_id',
            ['website_id']
        );

        // Grid filters use these names; map them to real columns (both tables have status, created_at...)
        $this->addFilterToMap('request_id', 'main_table.request_id');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('reason', 'main_table.reason');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('updated_at', 'main_table.updated_at');
        $this->addFilterToMap('store_id', 'so.store_id');
        $this->addFilterToMap('order_status', 'so.status');
        $this->addFilterToMap('website_id', 'store.website_id');
        $this->addFilterToMap('increment_id', 'so.increment_id');
        $this->addFilterToMap('customer_email', 'so.customer_email');
        $this->addFilterToMap('customer_name', $customerName);

        return $this;
    }

    /**
     * "Search by keyword" box: match ANY of these columns (OR), not just one.
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'fulltext') {
            $fields = ['increment_id', 'customer_name', 'customer_email', 'reason', 'status'];
            return parent::addFieldToFilter($fields, array_fill(0, count($fields), $condition));
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
