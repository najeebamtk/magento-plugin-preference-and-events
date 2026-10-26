<?php

namespace Codilar\HelloWorld\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class MaxQtyPlugin
{
    private const RESTRICTED_PRODUCT_SKU = 't-shirt01';
    private const MAX_QTY = 4;

    public function beforeAddProduct(
        Cart $subject,
        $productInfo,
        $requestInfo = null
    ): array {
        // Resolve the product SKU.
        if ($productInfo instanceof Product) {
            $productSku = (string) $productInfo->getSku();
        } else {
            // Cart::addProduct() can receive a product identifier.
            $productSku = '';
            if (is_numeric($productInfo)) {
                $product = $subject->getQuote()
                    ->getStore()
                    ->getWebsiteId();

                // Resolve the product through Magento's product repository
                // in the production-ready version.
            }
        }

        if ($productSku !== self::RESTRICTED_PRODUCT_SKU) {
            return [$productInfo, $requestInfo];
        }

        // Read the requested quantity.
        $requestedQty = 1;

        if (is_numeric($requestInfo)) {
            $requestedQty = (float) $requestInfo;
        } elseif (is_array($requestInfo) && isset($requestInfo['qty'])) {
            $requestedQty = (float) $requestInfo['qty'];
        } elseif ($requestInfo instanceof DataObject) {
            $requestedQty = (float) $requestInfo->getData('qty');
        }

        // Calculate existing quantity for the same product.
        $existingQty = 0;

        foreach ($subject->getQuote()->getAllVisibleItems() as $item) {
            if ($item->getSku() === self::RESTRICTED_PRODUCT_SKU) {
                $existingQty += (float) $item->getQty();
            }
        }

        if (($existingQty + $requestedQty) > self::MAX_QTY) {
            throw new LocalizedException(
                __('You cannot add more than %1 units of this product to your cart.', self::MAX_QTY)
            );
        }

        return [$productInfo, $requestInfo];
    }
}
