<?php
namespace Codilar\HelloWorld\Model\ResourceModel\Product;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Codilar\HelloWorld\Model\Product::class,
            \Codilar\HelloWorld\Model\ResourceModel\Product::class
        );
    }
}
