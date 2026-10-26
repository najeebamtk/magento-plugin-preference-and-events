<?php
namespace Codilar\HelloWorld\Plugin;

use Magento\CatalogWidget\Block\Product\ProductsList;

class ProductUrlPlugin
{
    public function aroundGetProductUrl(
        ProductsList $subject,
        callable $proceed,
        $product,
        $additional = []
    ) {
        // BEFORE part: change the input
        $additional['_query'] = ['from' => 'homepage'];

        // ORIGINAL: Magento builds the URL using our changed input
        $url = $proceed($product, $additional);

        // AFTER part: change the output
        return $url . '#reviews';
    }
}
