<?php

namespace Codilar\ReturnManagement\Model\Source;

use Codilar\ReturnManagement\Model\ReturnRequest;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Reason options for the grid column + filter dropdown.
 */
class Reason implements OptionSourceInterface
{
    public function toOptionArray()
    {
        $options = [];
        foreach (ReturnRequest::getReasons() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }
}
