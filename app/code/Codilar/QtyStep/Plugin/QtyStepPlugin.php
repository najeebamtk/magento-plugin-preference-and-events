<?php
namespace Codilar\QtyStep\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class QtyStepPlugin
{
    /**
     * Product that must be bought in multiples of QTY_STEP
     */
    private const RESTRICTED_SKU = 't-shirt00';

    /**
     * Allowed step: 2 means 2, 4, 6, ...
     */
    private const QTY_STEP = 2;

    /**
     * Before plugin: runs before a product is added to the cart (product page, category page)
     *
     * @param Cart $subject
     * @param Product|int|string $productInfo
     * @param DataObject|array|int|null $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        if ($productInfo instanceof Product && $productInfo->getSku() === self::RESTRICTED_SKU) {
            $this->validateQty($this->getRequestedQty($requestInfo));
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * Before plugin: runs before cart quantities are changed (cart page, mini cart)
     *
     * @param Cart $subject
     * @param array $data [quote item id => ['qty' => new qty], ...]
     * @return array
     * @throws LocalizedException
     */
    public function beforeUpdateItems(Cart $subject, $data)
    {
        foreach ($data as $itemId => $itemInfo) {
            $item = $subject->getQuote()->getItemById($itemId);
            if (!$item || $item->getSku() !== self::RESTRICTED_SKU || !isset($itemInfo['qty'])) {
                continue;
            }

            $qty = (float) $itemInfo['qty'];
            if ($qty > 0) {
                $this->validateQty($qty);
            }
        }

        return [$data];
    }

    /**
     * @param DataObject|array|int|null $requestInfo
     * @return float
     */
    private function getRequestedQty($requestInfo)
    {
        if (is_numeric($requestInfo)) {
            return (float) $requestInfo;
        }
        if (is_array($requestInfo) && !empty($requestInfo['qty'])) {
            return (float) $requestInfo['qty'];
        }
        if ($requestInfo instanceof DataObject && $requestInfo->getQty()) {
            return (float) $requestInfo->getQty();
        }

        return 1;
    }

    /**
     * @param float $qty
     * @return void
     * @throws LocalizedException
     */
    private function validateQty($qty)
    {
        if (fmod($qty, self::QTY_STEP) != 0) {
            throw new LocalizedException(
                __('This product can only be bought in multiples of %1 (e.g. %1, %2, %3).', self::QTY_STEP, self::QTY_STEP * 2, self::QTY_STEP * 3)
            );
        }
    }
}
