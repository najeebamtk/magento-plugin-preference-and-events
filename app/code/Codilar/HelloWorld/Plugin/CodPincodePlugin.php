<?php
namespace Codilar\HelloWorld\Plugin;

use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

class CodPincodePlugin
{
    /**
     * Pincodes where Cash on Delivery is not allowed
     */
    private const BLOCKED_PINCODES = ['682001', '673502', '695001'];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Around plugin: decides whether Magento's own COD check runs at all
     *
     * @param Cashondelivery $subject
     * @param callable $proceed
     * @param CartInterface|null $quote
     * @return bool
     */
    public function aroundIsAvailable(
        Cashondelivery $subject,
        callable $proceed,
        ?CartInterface $quote = null
    ) {
        $pincode = $quote ? trim((string) $quote->getShippingAddress()->getPostcode()) : '';

        if (in_array($pincode, self::BLOCKED_PINCODES, true)) {
            $this->logger->info(sprintf('COD blocked for quote %s, pincode %s', $quote->getId(), $pincode));
            return false;
        }

        return $proceed($quote);
    }
}
