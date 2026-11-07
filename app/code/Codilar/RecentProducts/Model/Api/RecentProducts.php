<?php
declare(strict_types=1);

namespace Codilar\RecentProducts\Model\Api;

use Codilar\RecentProducts\Api\RecentProductsInterface;
use Codilar\RecentProducts\Model\RecentProductsProvider;

class RecentProducts implements RecentProductsInterface
{
    public function __construct(
        private readonly RecentProductsProvider $provider
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getList(): array
    {
        return $this->provider->getProducts();
    }
}
