<?php
/**
 * Codilar_DailyCustomerReport CSV writer.
 */
declare(strict_types=1);

namespace Codilar\DailyCustomerReport\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Writes the customer report to var/export/customer_reports/customer_report_YYYY-MM-DD.csv.
 */
class CsvReportWriter
{
    private const REPORT_DIRECTORY = 'customer_reports';
    private const FILE_NAME_PREFIX = 'customer_report_';

    private const HEADERS = ['Customer ID', 'Full Name', 'Email', 'Registration Date'];

    /**
     * @param Filesystem $filesystem
     * @param Csv $csv
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Csv $csv,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * Write the customer rows to today's CSV file and return its absolute path.
     *
     * @param array[] $customers Rows from YesterdayCustomerProvider::getCustomers()
     * @return string
     * @throws FileSystemException
     */
    public function write(array $customers): string
    {
        $exportDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_EXPORT);
        $exportDirectory->create(self::REPORT_DIRECTORY);

        $fileName = self::FILE_NAME_PREFIX . $this->timezone->date()->format('Y-m-d') . '.csv';
        $absolutePath = $exportDirectory->getAbsolutePath(self::REPORT_DIRECTORY . '/' . $fileName);

        $rows = [self::HEADERS];
        foreach ($customers as $customer) {
            $rows[] = [
                $customer['customer_id'],
                $customer['full_name'],
                $customer['email'],
                $customer['registered_at'],
            ];
        }

        $this->csv->appendData($absolutePath, $rows, 'w');

        return $absolutePath;
    }
}
