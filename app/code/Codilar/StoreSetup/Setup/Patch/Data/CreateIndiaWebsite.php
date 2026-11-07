<?php
namespace Codilar\StoreSetup\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;

class CreateIndiaWebsite implements DataPatchInterface
{
    public function __construct(
        private WebsiteFactory $websiteFactory,
        private WebsiteResource $websiteResource,
        private GroupFactory $groupFactory,
        private GroupResource $groupResource,
        private StoreFactory $storeFactory,
        private StoreResource $storeResource,
        private WriterInterface $configWriter
    ) {
    }

    public function apply()
    {
        // Safety check: stop if the website already exists
        $website = $this->websiteFactory->create();
        $this->websiteResource->load($website, 'india_web', 'code');
        if ($website->getId()) {
            return $this;
        }

        // 1. Website
        $website->setCode('india_web')
            ->setName('India Website');
        $this->websiteResource->save($website);

        // 2. Store (called "Group" in code)
        $group = $this->groupFactory->create();
        $group->setWebsiteId($website->getId())
            ->setName('India Store')
            ->setCode('india_store')
            ->setRootCategoryId(2);
        $this->groupResource->save($group);

        // 3. Store View (called "Store" in code)
        $store = $this->storeFactory->create();
        $store->setCode('in_en')
            ->setName('India English')
            ->setWebsiteId($website->getId())
            ->setGroupId($group->getId())
            ->setIsActive(1);
        $this->storeResource->save($store);
        // Order number tables for the new view are created by
        // Magento_SalesSequence's recurring setup at the end of setup:upgrade

        // 4. Country settings for this website
        $this->configWriter->save('catalog/price/scope', 1); // 1 = prices per website

        $id = $website->getId();
        $this->configWriter->save('currency/options/base', 'INR', 'websites', $id);
        $this->configWriter->save('currency/options/default', 'INR', 'websites', $id);
        $this->configWriter->save('currency/options/allow', 'INR', 'websites', $id);
        $this->configWriter->save('general/locale/code', 'en_US', 'websites', $id);
        $this->configWriter->save('general/country/allow', 'IN', 'websites', $id);
        $this->configWriter->save('general/country/default', 'IN', 'websites', $id);

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
