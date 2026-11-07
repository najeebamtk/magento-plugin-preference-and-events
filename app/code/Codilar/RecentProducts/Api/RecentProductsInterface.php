<?php
declare(strict_types=1);

namespace Codilar\RecentProducts\Api;

/**
 * REST API contract: GET /V1/codilar/recent-products
 */
interface RecentProductsInterface
{
    /**
     * Get the 10 most recently created products (cached 30 minutes).
     *
     * @return mixed[]
     */
    public function getList(): array;
}
