<?php

namespace Codilar\School\Controller\School;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * URL: /school/school/lists
 * Shows all saved schools.
 *
 * The class is named Lists because PHP doesn't allow "List" as a class name.
 */
class Lists extends Action
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
        // Loads layout/school_school_lists.xml
        $page->addHandle('school_school_lists');
        return $page;
    }
}
