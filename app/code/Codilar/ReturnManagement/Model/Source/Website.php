<?php

namespace Codilar\ReturnManagement\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Website options for the grid column + filter dropdown.
 */
class Website implements OptionSourceInterface
{
    private StoreManagerInterface $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            // string value, so it matches the website_id coming from the database row
            $options[] = ['value' => (string) $website->getId(), 'label' => $website->getName()];
        }
        return $options;
    }
}
