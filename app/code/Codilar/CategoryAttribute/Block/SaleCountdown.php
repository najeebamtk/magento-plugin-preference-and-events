<?php
namespace Codilar\CategoryAttribute\Block;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class SaleCountdown extends Template
{
    public function __construct(
        Context $context,
        private Resolver $layerResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Unix timestamp when the sale ends (end of the chosen day, store timezone), or null if no active sale
     */
    public function getEndTimestamp(): ?int
    {
        $end = $this->getEndDate();

        return $end && $end->getTimestamp() > time() ? $end->getTimestamp() : null;
    }

    /**
     * Readable end date for the widget footer, e.g. "30 Nov 2026"
     */
    public function getEndDateLabel(): string
    {
        $end = $this->getEndDate();

        return $end ? $end->format('d M Y') : '';
    }

    public function getCategoryName(): string
    {
        return (string)$this->layerResolver->get()->getCurrentCategory()->getName();
    }

    private function getEndDate(): ?\DateTime
    {
        $saleEndDate = $this->layerResolver->get()->getCurrentCategory()->getData('sale_end_date');
        if (!$saleEndDate) {
            return null;
        }

        $timezone = new \DateTimeZone($this->_localeDate->getConfigTimezone());

        return new \DateTime(substr($saleEndDate, 0, 10) . ' 23:59:59', $timezone);
    }
}
