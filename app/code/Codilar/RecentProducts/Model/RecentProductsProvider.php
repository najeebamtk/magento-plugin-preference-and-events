<?php
declare(strict_types=1);

namespace Codilar\RecentProducts\Model;

use Codilar\RecentProducts\Model\Cache\Type\RecentProducts as CacheType;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class RecentProductsProvider
{
    private const CACHE_KEY = 'codilar_recent_products_list';
    private const CACHE_LIFETIME = 1800; // 30 minutes in seconds
    private const LIMIT = 10;

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly CacheInterface $cache,
        private readonly SerializerInterface $serializer,
        private readonly StateInterface $cacheState,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getProducts(): array
    {
        $cacheEnabled = $this->cacheState->isEnabled(CacheType::TYPE_IDENTIFIER);

        // 1. Try the cache first
        if ($cacheEnabled) {
            $cached = $this->cache->load(self::CACHE_KEY);
            if ($cached) {
                $this->logger->info('RecentProducts cache HIT');
                return $this->serializer->unserialize($cached);
            }
        }

        // 2. Not in cache: run the collection (DB query)
        $this->logger->info('RecentProducts cache MISS - loading collection');
        $products = $this->loadFromDatabase();

        // 3. Save to cache for 30 minutes, tagged with our cache type tag
        if ($cacheEnabled) {
            $this->cache->save(
                $this->serializer->serialize($products),
                self::CACHE_KEY,
                [CacheType::CACHE_TAG],
                self::CACHE_LIFETIME
            );
        }

        return $products;
    }

    private function loadFromDatabase(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name', 'price'])
            ->setOrder('created_at', 'DESC')
            ->setOrder('entity_id', 'DESC') // tie-break: same created_at second
            ->setPageSize(self::LIMIT)
            ->setCurPage(1);

        // Run the query now, then log the exact SQL that ran.
        // (Only happens on a MISS - on a HIT this method is never called.)
        $collection->load();
        $this->logger->info('RecentProducts SQL: ' . $collection->getSelect()->__toString());

        $result = [];
        foreach ($collection as $product) {
            $result[] = [
                'id'         => (int) $product->getId(),
                'sku'        => $product->getSku(),
                'name'       => $product->getName(),
                'price'      => (float) $product->getPrice(),
                'created_at' => $product->getCreatedAt(),
            ];
        }
        return $result;
    }
}
