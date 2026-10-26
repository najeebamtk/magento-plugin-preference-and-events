<?php

namespace Codilar\HelloWorld\Block;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Codilar\HelloWorld\Model\ResourceModel\Product\CollectionFactory;

class Product extends Template
{
    private CollectionFactory $collectionFactory;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    public function getProducts()
    {
        return $this->collectionFactory->create()->setOrder('entity_id','DESC');
    }

    /**
     * Full URL for a stored image path, e.g. helloworld/images1.jpeg
     */
    public function getImageUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . ltrim($path, '/');
    }
}

