<?php
namespace Codilar\CategoryAttribute\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSaleEndDateAttribute implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private EavSetupFactory $eavSetupFactory
    ) {
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(Category::ENTITY, 'sale_end_date', [
            // --- basic definition (eav_attribute) ---
            'type'         => 'datetime',
            'label'        => 'Sale End Date',
            'input'        => 'date',
            'backend'      => Datetime::class,
            'required'     => false,
            'user_defined' => true,
            'sort_order'   => 100,

            // --- attribute set group (eav_entity_attribute) ---
            'group' => 'General Information',

            // --- category settings (catalog_eav_attribute) ---
            'global'  => ScopedAttributeInterface::SCOPE_STORE,
            'visible' => true,
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
