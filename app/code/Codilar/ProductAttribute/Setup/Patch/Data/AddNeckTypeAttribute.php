<?php
namespace Codilar\ProductAttribute\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddNeckTypeAttribute implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(Product::ENTITY, 'neck_type', [
            // --- basic definition (eav_attribute) ---
            'type'         => 'int',
            'label'        => 'Neck Type',
            'input'        => 'select',
            'source'       => Table::class,
            'required'     => false,
            'user_defined' => true,
            'sort_order'   => 50,

            // --- dropdown options (eav_attribute_option + _option_value) ---
            'option' => [
                'values' => ['Round Neck', 'V-Neck', 'Polo Collar', 'Hooded'],
            ],

            // --- where it appears in admin (eav_entity_attribute) ---
            'group' => 'General',

            // --- product-only settings (catalog_eav_attribute) ---
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible'                 => true,
            'visible_on_front'        => true,
            'used_in_product_listing' => true,
            'filterable'              => 1,
            'searchable'              => true,
            'is_used_in_grid'         => true,
            'is_filterable_in_grid'   => true,
        ]);
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
