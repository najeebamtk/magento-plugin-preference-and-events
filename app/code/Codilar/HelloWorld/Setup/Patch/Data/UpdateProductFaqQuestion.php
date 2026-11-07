<?php
namespace Codilar\HelloWorld\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Product as MagentoProductResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateProductFaqQuestion implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private MagentoProductResource $magentoProductResource
    ) {
    }

    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $faqTable = $this->moduleDataSetup->getTable('codilar_product_faq');

        // Find the product by SKU (IDs can differ on other machines)
        $productId = $this->magentoProductResource->getIdBySku('adidas shirt');
        if (!$productId) {
            return $this;
        }

        // UPDATE codilar_product_faq SET question = '...' WHERE product_id = ? AND question = ?
        $connection->update(
            $faqTable,
            ['question' => 'What sizes does this shirt come in?'],   // new value
            [
                'product_id = ?' => $productId,                       // which row
                'question = ?' => 'Which sizes are available?',
            ]
        );

        return $this;
    }

    public static function getDependencies()
    {
        // Make sure the FAQs are inserted before we try to update them
        return [AddProductFaqs::class];
    }

    public function getAliases()
    {
        return [];
    }
}
