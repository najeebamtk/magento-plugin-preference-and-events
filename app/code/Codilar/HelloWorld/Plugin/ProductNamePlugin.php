<?php
namespace Codilar\HelloWorld\Plugin;

use Magento\Catalog\Model\Product;

class ProductNamePlugin
{
    public function afterGetName(Product $subject, $result)
    {
        return $result . ' - SALE';
    }
}
