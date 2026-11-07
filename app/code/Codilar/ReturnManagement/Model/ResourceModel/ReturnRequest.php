<?php

namespace Codilar\ReturnManagement\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ReturnRequest extends AbstractDb
{
    public const ITEM_TABLE = 'codilar_return_request_item';

    protected function _construct()
    {
        // table name, primary key column
        $this->_init('codilar_return_request', 'request_id');
    }

    /**
     * Don't re-send the old updated_at value, so MySQL's on_update sets the current time.
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->unsetData('updated_at');
        return parent::_beforeSave($object);
    }

    /**
     * Save the request + its items in ONE database transaction.
     * If the items insert fails, the request row is rolled back too.
     *
     * @param \Codilar\ReturnManagement\Model\ReturnRequest $request
     * @param array $items rows: order_item_id, product_id, product_sku, product_name, qty_requested
     * @throws \Exception
     */
    public function saveWithItems(\Codilar\ReturnManagement\Model\ReturnRequest $request, array $items): void
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            $this->save($request);

            foreach (array_keys($items) as $key) {
                $items[$key]['request_id'] = (int) $request->getId();
            }
            $connection->insertMultiple($this->getTable(self::ITEM_TABLE), $items);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Items of one request (used by admin detail page and customer page).
     */
    public function getItems(int $requestId): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable(self::ITEM_TABLE))
            ->where('request_id = ?', $requestId);

        return $connection->fetchAll($select);
    }
}
