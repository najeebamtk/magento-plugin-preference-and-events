<?php

namespace Codilar\School\Controller\School;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * URL: /school/school/index
 * Shows the "add a school" form.
 */
class Index extends Action
{
    private PageFactory $pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        // Loads layout/school_school_index.xml
        $page->addHandle('school_school_index');
        return $page;
    }
}
