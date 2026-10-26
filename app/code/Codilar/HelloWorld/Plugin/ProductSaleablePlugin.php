<?php
namespace Codilar\HelloWorld\Plugin;

use Magento\Catalog\Model\Product;

class ProductSaleablePlugin
{
    public function aroundIsSaleable(Product $subject, callable $proceed)
    {
        // BEFORE: our own check
        if ($subject->getFinalPrice() > 200) {
            return false;            // stop here; the original never runs
        }

        // run the ORIGINAL isSaleable()
        $result = $proceed();

        // AFTER: we could change $result here if we wanted
        return $result;
    }
}
