<?php
/**
 * Codilar_DailyCustomerReport cron entry point.
 */
declare(strict_types=1);

namespace Codilar\DailyCustomerReport\Cron;

use Codilar\DailyCustomerReport\Model\CsvReportWriter;
use Codilar\DailyCustomerReport\Model\YesterdayCustomerProvider;
use Psr\Log\LoggerInterface;

/**
 * Generates the daily customer registration report.
 */
class GenerateCustomerReport
{
    /**
     * @param YesterdayCustomerProvider $customerProvider
     * @param CsvReportWriter $reportWriter
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly YesterdayCustomerProvider $customerProvider,
        private readonly CsvReportWriter $reportWriter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Cron job entry point, called by Magento's cron scheduler.
     *
     * @return void
     * @throws \Exception
     */
    public function execute(): void
    {
        try {
            $customers = $this->customerProvider->getCustomers();
            $filePath = $this->reportWriter->write($customers);

            $this->logger->info(
                sprintf(
                    'Codilar_DailyCustomerReport: found %d customer(s) registered yesterday. Report saved to %s',
                    count($customers),
                    $filePath
                ),
                ['customer_ids' => array_column($customers, 'customer_id')]
            );
        } catch (\Exception $exception) {
            $this->logger->error(
                'Codilar_DailyCustomerReport: report generation failed. ' . $exception->getMessage(),
                ['exception' => $exception]
            );

            throw $exception;
        }
    }
}
