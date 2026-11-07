<?php

namespace Codilar\ReturnManagement\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Reads our settings from Stores > Configuration > Codilar > Return Management.
 */
class Config
{
    public const XML_PATH_ENABLED = 'return_management/general/enabled';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Store view scope: the switch can be different per website / store view.
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
