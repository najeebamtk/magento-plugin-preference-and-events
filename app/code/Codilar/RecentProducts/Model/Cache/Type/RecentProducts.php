<?php
declare(strict_types=1);

namespace Codilar\RecentProducts\Model\Cache\Type;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class RecentProducts extends TagScope
{
    /** Must match name="" in cache.xml */
    public const TYPE_IDENTIFIER = 'codilar_recent_products';

    /** Every entry saved for this type gets this tag */
    public const CACHE_TAG = 'CODILAR_RECENT_PRODUCTS';

    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }
}
