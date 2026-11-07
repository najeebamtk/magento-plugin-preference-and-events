<?php
namespace Codilar\StoreSetup\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\StoreFactory;

class CreateIndiaHindiStoreView implements DataPatchInterface
{
    public function __construct(
        private GroupFactory $groupFactory,
        private GroupResource $groupResource,
        private StoreFactory $storeFactory,
        private StoreResource $storeResource,
        private WriterInterface $configWriter
    ) {
    }

    public function apply()
    {
        // Safety check: stop if the store view already exists
        $store = $this->storeFactory->create();
        $this->storeResource->load($store, 'in_hi', 'code');
        if ($store->getId()) {
            return $this;
        }

        // Find the existing India Store
        $group = $this->groupFactory->create();
        $this->groupResource->load($group, 'india_store', 'code');

        // Create the Store View inside it
        $store->setCode('in_hi')
            ->setName('India Hindi')
            ->setWebsiteId($group->getWebsiteId())
            ->setGroupId($group->getId())
            ->setSortOrder(1)
            ->setIsActive(1);
        $this->storeResource->save($store);
        // Order number tables for the new view are created by
        // Magento_SalesSequence's recurring setup at the end of setup:upgrade

        // Hindi language for this store view only
        $this->configWriter->save('general/locale/code', 'hi_IN', 'stores', $store->getId());

        return $this;
    }

    public static function getDependencies()
    {
        // India Store must exist first
        return [CreateIndiaWebsite::class];
    }

    public function getAliases()
    {
        return [];
    }
}
