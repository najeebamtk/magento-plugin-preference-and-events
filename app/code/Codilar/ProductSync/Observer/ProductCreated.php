<?php
namespace Codilar\ProductSync\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * Runs after a product is saved. For NEW products only:
 * 1. Saves the product data in codilar_product_sync (status = pending, retry_count = 0)
 * 2. After the product save is committed, publishes {"sync_id": X} to RabbitMQ
 */
class ProductCreated implements ObserverInterface
{
    const TOPIC = 'codilar.product.sync';

    public function __construct(
        private ResourceConnection $resource,
        private PublisherInterface $publisher,
        private LoggerInterface $logger
    ) {
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        // Only brand-new products; ignore edits of existing products
        if (!$product->isObjectNew()) {
            return;
        }

        // 1. Product data we want to send to the external API
        $payload = [
            'id'    => (int)$product->getId(),
            'sku'   => $product->getSku(),
            'name'  => $product->getName(),
            'price' => (float)$product->getPrice(),
            'type'  => $product->getTypeId(),
        ];

        // 2. Save it in our table (same DB transaction as the product save)
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('codilar_product_sync');
        $connection->insert($table, [
            'product_id' => $product->getId(),
            'sku'        => $product->getSku(),
            'payload'    => json_encode($payload),
            'status'     => 'pending',
        ]);
        $syncId = (int)$connection->lastInsertId($table);

        $this->logger->info("Sync #$syncId SAVED for new product SKU={$product->getSku()} (status=pending)");

        // 3. Publish only AFTER the product save is committed,
        //    so the consumer can always find the row
        $product->getResource()->addCommitCallback(function () use ($syncId) {
            try {
                $this->publisher->publish(self::TOPIC, json_encode(['sync_id' => $syncId]));
                $this->logger->info("Sync #$syncId PUBLISHED to queue " . self::TOPIC);
            } catch (\Exception $e) {
                // RabbitMQ down? The row stays "pending" in the table, so nothing is lost
                $this->logger->error("Sync #$syncId PUBLISH FAILED: " . $e->getMessage());
            }
        });
    }
}
