<?php
namespace Codilar\HelloWorld\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;

class MaxQtySaleablePlugin
{
    /**
     * Product ID to restrict
     */
        private const RESTRICTED_PRODUCT_SKU = 't-shirt01';


    /**
     * Maximum units allowed in cart for that product
     */
    private const MAX_QTY = 4;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(CheckoutSession $checkoutSession)
    {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * After plugin: runs after Product::isSaleable() returns its result
     *
     * @param Product $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsSaleable(Product $subject, $result)
    {
        if (!$result) {
            return $result;
        }

        if ($subject->getSku() !== self::RESTRICTED_PRODUCT_SKU) {
            return $result;
        }

        $qtyInCart = 0;
        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            if ($item->getSku() === self::RESTRICTED_PRODUCT_SKU) {
                $qtyInCart += (float) $item->getQty();
            }
        }

        if ($qtyInCart >= self::MAX_QTY) {
            $subject->setData('max_qty_message', __('You cannot add this item to cart more than %1 times.', self::MAX_QTY));
            return false;
        }

        return $result;
    }
}
