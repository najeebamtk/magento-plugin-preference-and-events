<?php
namespace Codilar\HelloWorld\Plugin;

use Magento\Newsletter\Model\SubscriptionManager;
use Psr\Log\LoggerInterface;

class NewsletterEmailPlugin
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)   // "I need a logger"
    {
        $this->logger = $logger;                           // Magento delivers it
    }

    public function beforeSubscribe(SubscriptionManager $subject, string $email, int $storeId)
    {
        $email = strtolower(trim($email));
        $this->logger->info('Newsletter subscribe: ' . $email);   // writes to var/log/system.log
        return [$email, $storeId];
    }
}
