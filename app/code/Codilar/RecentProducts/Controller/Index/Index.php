<?php
declare(strict_types=1);

namespace Codilar\RecentProducts\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Opens the page /recentproducts (layout: recentproducts_index_index.xml)
 */
class Index implements HttpGetActionInterface
{
    public function __construct(
        private readonly PageFactory $pageFactory
    ) {
    }

    public function execute(): Page
    {
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set(__('Recent Products'));
        return $page;
    }
}
