<?php
namespace Codilar\HelloWorld\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CartAddMessage implements ObserverInterface
{
    private ManagerInterface $messageManager;

    public function __construct(ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getData('product');

        $this->messageManager->addSuccessMessage(
            __('Great choice! %1 is a customer favourite.', $product->getName())
        );
    }
}
