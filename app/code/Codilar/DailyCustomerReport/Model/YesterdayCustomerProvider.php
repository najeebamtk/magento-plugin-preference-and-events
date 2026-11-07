<?php
/**
 * Codilar_DailyCustomerReport customer data provider.
 */
declare(strict_types=1);

namespace Codilar\DailyCustomerReport\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Fetches customers who registered yesterday (store timezone).
 */
class YesterdayCustomerProvider
{
    /**
     * @param CollectionFactory $customerCollectionFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private readonly CollectionFactory $customerCollectionFactory,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * Get customers registered yesterday, one array row per customer.
     *
     * @return array<int, array{customer_id: int, full_name: string, email: string, registered_at: string}>
     */
    public function getCustomers(): array
    {
        [$fromUtc, $toUtc] = $this->getYesterdayRangeInUtc();

        $collection = $this->customerCollectionFactory->create();
        $collection->addNameToSelect()
            ->addAttributeToFilter('created_at', ['gteq' => $fromUtc])
            ->addAttributeToFilter('created_at', ['lt' => $toUtc])
            ->setOrder('created_at', 'ASC');

        $customers = [];
        foreach ($collection as $customer) {
            $customers[] = [
                'customer_id' => (int)$customer->getId(),
                'full_name' => trim((string)$customer->getData('name')),
                'email' => (string)$customer->getEmail(),
                'registered_at' => $this->convertUtcToStoreTime((string)$customer->getCreatedAt()),
            ];
        }

        return $customers;
    }

    /**
     * Build yesterday's range [00:00 yesterday, 00:00 today) in store time, converted to UTC for the DB query.
     *
     * @return string[] [fromUtc, toUtc] in 'Y-m-d H:i:s' format
     */
    private function getYesterdayRangeInUtc(): array
    {
        $now = $this->timezone->date();
        $startOfYesterday = (clone $now)->modify('yesterday');
        $startOfToday = (clone $now)->modify('today');

        return [
            $this->timezone->convertConfigTimeToUtc($startOfYesterday),
            $this->timezone->convertConfigTimeToUtc($startOfToday),
        ];
    }

    /**
     * Convert a UTC database datetime to the store timezone for display.
     *
     * @param string $utcDateTime
     * @return string
     */
    private function convertUtcToStoreTime(string $utcDateTime): string
    {
        $date = new \DateTime($utcDateTime, new \DateTimeZone('UTC'));

        return $this->timezone->date($date)->format('Y-m-d H:i:s');
    }
}
