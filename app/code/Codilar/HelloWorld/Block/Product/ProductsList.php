<?php
namespace Codilar\HelloWorld\Block\Product;

class ProductsList extends \Magento\CatalogWidget\Block\Product\ProductsList
{
    /**
     * Overrides Magento's PROTECTED _afterToHtml() to add a heading above the grid
     */
    protected function _afterToHtml($html)
    {
        $html = parent::_afterToHtml($html);   // Magento's original (trims the HTML)

        if ($html === '') {
            return $html;                      // no products → no heading
        }

        $heading = '<h2 class="codilar-new-arrivals">' . $this->escapeHtml(__('New Arrivals')) . '</h2>';
        return $heading . $html;
    }
}
