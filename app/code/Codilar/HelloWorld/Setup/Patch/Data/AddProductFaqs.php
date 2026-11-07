<?php
namespace Codilar\HelloWorld\Setup\Patch\Data;

use Magento\Catalog\Model\ResourceModel\Product as MagentoProductResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddProductFaqs implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
        private MagentoProductResource $magentoProductResource
    ) {
    }

    public function apply()
    {
        // FAQs grouped by the SKU of a real Magento product
        $faqsBySku = [
            'adidas shirt' => [
                ['question' => 'Which sizes are available?', 'answer' => 'Small, Medium and Large.'],
                ['question' => 'Is the colour fast after washing?', 'answer' => 'Yes, the colour does not fade.'],
            ],
            'double pocketshirt00' => [
                ['question' => 'Do both pockets have buttons?', 'answer' => 'Yes, both pockets have button flaps.'],
            ],
            'supremelong00' => [
                ['question' => 'Is this a full sleeve shirt?', 'answer' => 'Yes, it has full-length sleeves.'],
            ],
        ];

        $connection = $this->moduleDataSetup->getConnection();
        $faqTable = $this->moduleDataSetup->getTable('codilar_product_faq');

        $this->moduleDataSetup->startSetup();

        foreach ($faqsBySku as $sku => $faqs) {
            // 1. Find the product's entity_id in catalog_product_entity
            $productId = $this->magentoProductResource->getIdBySku($sku);
            if (!$productId) {
                // Product not in this store: skip, otherwise the foreign key would reject the insert
                continue;
            }

            foreach ($faqs as $faq) {
                // 2. Safety check: skip if this product already has this question
                $exists = $connection->fetchOne(
                    $connection->select()
                        ->from($faqTable, 'faq_id')
                        ->where('product_id = ?', $productId)
                        ->where('question = ?', $faq['question'])
                );
                if ($exists) {
                    continue;
                }

                // 3. Insert the row; product_id is the foreign key to catalog_product_entity
                $connection->insert($faqTable, [
                    'product_id' => $productId,
                    'question' => $faq['question'],
                    'answer' => $faq['answer'],
                ]);
            }
        }

        $this->moduleDataSetup->endSetup();

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
