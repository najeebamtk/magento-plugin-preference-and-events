<?php
namespace Codilar\HelloWorld\Model;

use Magento\Framework\Model\AbstractModel;

class Product extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Codilar\HelloWorld\Model\ResourceModel\Product::class);
    }
}
