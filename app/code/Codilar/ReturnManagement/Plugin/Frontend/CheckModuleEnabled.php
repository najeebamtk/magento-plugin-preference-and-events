<?php

namespace Codilar\ReturnManagement\Plugin\Frontend;

use Codilar\ReturnManagement\Model\Config;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Runs around execute() of our 3 customer controllers (see etc/frontend/di.xml).
 * Module disabled in the admin => show the 404 page instead of the real page.
 */
class CheckModuleEnabled
{
    private Config $config;
    private ForwardFactory $forwardFactory;

    public function __construct(Config $config, ForwardFactory $forwardFactory)
    {
        $this->config = $config;
        $this->forwardFactory = $forwardFactory;
    }

    public function aroundExecute(ActionInterface $subject, callable $proceed)
    {
        if (!$this->config->isEnabled()) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        return $proceed();
    }
}
