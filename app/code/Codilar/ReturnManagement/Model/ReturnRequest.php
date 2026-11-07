<?php

namespace Codilar\ReturnManagement\Model;

use Magento\Framework\Model\AbstractModel;

class ReturnRequest extends AbstractModel
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Status code => label. Used by the admin grid, admin status dropdown and customer page.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_REJECTED => __('Rejected'),
            self::STATUS_COMPLETED => __('Completed'),
        ];
    }

    /**
     * Reason code => label. Only the code is saved in the database.
     */
    public static function getReasons(): array
    {
        return [
            'wrong_product' => __('Wrong product received'),
            'damaged' => __('Damaged product'),
            'not_as_described' => __('Product not as described'),
            'quality_issue' => __('Quality issue'),
            'other' => __('Other'),
        ];
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\ReturnRequest::class);
    }
}
