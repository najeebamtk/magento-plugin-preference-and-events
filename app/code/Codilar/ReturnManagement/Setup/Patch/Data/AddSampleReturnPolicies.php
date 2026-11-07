<?php

namespace Codilar\ReturnManagement\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Gives every existing product a 30-day return policy, so there is test data.
 */
class AddSampleReturnPolicies implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();

        $productIds = $connection->fetchCol(
            $connection->select()->from($this->moduleDataSetup->getTable('catalog_product_entity'), 'entity_id')
        );

        $rows = [];
        foreach ($productIds as $productId) {
            $rows[] = [
                'product_id' => (int) $productId,
                'is_returnable' => 1,
                'return_window_days' => 30,
            ];
        }

        if ($rows) {
            $connection->insertMultiple($this->moduleDataSetup->getTable('codilar_return_policy'), $rows);
        }

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
