<?php

namespace Codilar\School\Block;

use Codilar\School\Model\ResourceModel\School\CollectionFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Block used by both the form and the list templates.
 * Named "School" on purpose (not "Product").
 */
class School extends Template
{
    private CollectionFactory $collectionFactory;
    private FormKey $formKey;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        FormKey $formKey,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->formKey = $formKey;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $data);
    }

    /**
     * All schools, newest first.
     */
    public function getSchools()
    {
        return $this->collectionFactory->create()
            ->setOrder('entity_id', 'DESC');
    }

    /**
     * The hidden form_key value the form must send back.
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Full URL for a stored logo path, e.g. school/logo.png.
     * Returns an empty string when there is no logo, so the
     * template can show the placeholder instead.
     */
    public function getLogoUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . ltrim($path, '/');
    }
}
