<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Inventory;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class ProductQuantityData extends BillingoData
{
    public function __construct(
        public ?float $availableQuantity = null,
    ) {}
}
