<?php

namespace Codilar\ReturnManagement\Model\Source;

use Codilar\ReturnManagement\Model\ReturnRequest;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Status options for the grid column + filter dropdown.
 */
class Status implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];
        foreach (ReturnRequest::getStatuses() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }
}
