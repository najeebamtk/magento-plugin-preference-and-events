<?php
namespace Codilar\HelloWorld\Setup\Patch\Data;

use Codilar\HelloWorld\Model\ProductFactory;
use Codilar\HelloWorld\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddColorProducts implements DataPatchInterface
{
    public function __construct(
        private ProductFactory $productFactory,
        private ProductResource $productResource,
        private Filesystem $filesystem
    ) {
    }

    public function apply()
    {
        $products = [
            ['name' => 'Green T-Shirt', 'sku' => 'TSHIRT-GREEN', 'price' => 449.00, 'image' => 'green-tshirt.jpeg'],
            ['name' => 'Yellow Polo Shirt', 'sku' => 'POLO-YELLOW', 'price' => 799.00, 'image' => 'yellow-polo-shirt.jpeg'],
            ['name' => 'Purple Checked Shirt', 'sku' => 'SHIRT-PURPLE-CHECK', 'price' => 1099.00, 'image' => 'purple-checked-shirt.jpeg'],
        ];

        // pub/media folder, where the list page reads images from
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->create('helloworld');

        foreach ($products as $data) {
            // 1. Copy the image from this module into pub/media/helloworld/
            $source = __DIR__ . '/images/' . $data['image'];
            $target = 'helloworld/' . $data['image'];
            if (!$mediaDirectory->isExist($target)) {
                $mediaDirectory->getDriver()->copy($source, $mediaDirectory->getAbsolutePath($target));
            }
            $data['image'] = $target;

            // 2. Safety check: skip if this SKU is already in the table
            $product = $this->productFactory->create();
            $this->productResource->load($product, $data['sku'], 'sku');
            if ($product->getId()) {
                continue;
            }

            // 3. Save the row
            $product->setData($data);
            $this->productResource->save($product);
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
