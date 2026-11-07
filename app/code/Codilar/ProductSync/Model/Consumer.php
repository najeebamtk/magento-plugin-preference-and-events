<?php
namespace Codilar\ProductSync\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

/**
 * CONSUMER: reads {"sync_id": X} from RabbitMQ and sends that product to the external API.
 *
 * - status already success/failed -> skip (never processed again)
 * - API OK                        -> status = success
 * - API error, attempts < 3       -> retry_count + 1, put message back in the queue
 * - API error, attempt 3          -> status = failed
 */
class Consumer
{
    const API_URL      = 'https://get-products.free.beeceptor.com/products';
    const MAX_ATTEMPTS = 3;
    const TOPIC        = 'codilar.product.sync';
    const FAILED_TOPIC = 'codilar.product.sync.failed';

    public function __construct(
        private ResourceConnection $resource,
        private CurlFactory $curlFactory,
        private PublisherInterface $publisher,
        private LoggerInterface $logger
    ) {
    }

    public function process(string $message): void
    {
        $syncId = (int)(json_decode($message, true)['sync_id'] ?? 0);

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('codilar_product_sync');

        // 1. Load the row saved by the observer
        $row = $connection->fetchRow("SELECT * FROM $table WHERE sync_id = ?", [$syncId]);
        if (!$row) {
            $this->logger->error("Sync #$syncId NOT FOUND in table, message ignored");
            return;
        }

        // 2. Already finished? Don't process again
        if ($row['status'] !== 'pending') {
            $this->logger->info("Sync #$syncId SKIPPED, already {$row['status']}");
            return;
        }

        // 3. This attempt number
        $attempt = (int)$row['retry_count'] + 1;
        $this->logger->info("Sync #$syncId ATTEMPT $attempt/" . self::MAX_ATTEMPTS . " SKU={$row['sku']}");

        try {
            // 4. Send product data to the external API
            $curl = $this->curlFactory->create();
            $curl->setTimeout(10);
            $curl->addHeader('Content-Type', 'application/json');
            $curl->post(self::API_URL, $row['payload']);

            $status = $curl->getStatus();
            if ($status < 200 || $status >= 300) {
                throw new \Exception("API returned HTTP $status");
            }

            // 5a. Success
            $connection->update(
                $table,
                ['status' => 'success', 'retry_count' => $attempt, 'last_error' => null],
                ['sync_id = ?' => $syncId]
            );
            $this->logger->info("Sync #$syncId SUCCESS on attempt $attempt (HTTP $status)");
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->logger->error("Sync #$syncId ATTEMPT $attempt FAILED: $error");

            if ($attempt >= self::MAX_ATTEMPTS) {
                // 5b. No attempts left -> mark as failed
                $connection->update(
                    $table,
                    ['status' => 'failed', 'retry_count' => $attempt, 'last_error' => $error],
                    ['sync_id = ?' => $syncId]
                );
                $this->logger->error("Sync #$syncId marked FAILED after $attempt attempts");

                // Also move it to the failed queue in RabbitMQ
                $this->publisher->publish(self::FAILED_TOPIC, json_encode([
                    'sync_id'  => $syncId,
                    'sku'      => $row['sku'],
                    'attempts' => $attempt,
                    'error'    => $error,
                ]));
                $this->logger->error("Sync #$syncId MOVED to queue " . self::FAILED_TOPIC);
            } else {
                // 5c. Save the count, then put the message back in the queue to retry
                $connection->update(
                    $table,
                    ['retry_count' => $attempt, 'last_error' => $error],
                    ['sync_id = ?' => $syncId]
                );
                $this->publisher->publish(self::TOPIC, $message);
                $this->logger->info("Sync #$syncId RE-QUEUED for attempt " . ($attempt + 1));
            }
        }
    }
}
