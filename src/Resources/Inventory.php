<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Szabrob\LaravelBillingo\Data\Inventory\ProductQuantityData;

final class Inventory extends Resource
{
    public function productQuantity(int $productId): ProductQuantityData
    {
        return ProductQuantityData::from($this->json($this->connector->get("inventory/product/{$productId}/quantity")));
    }
}
