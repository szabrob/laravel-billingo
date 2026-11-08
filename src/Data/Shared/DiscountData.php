<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Shared;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DiscountType;

#[MapName(SnakeCaseMapper::class)]
class DiscountData extends BillingoData
{
    public function __construct(
        public ?DiscountType $type = null,
        public ?float $value = null,
    ) {}
}
