<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Spendings;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class SpendingItemData extends BillingoData
{
    public function __construct(
        public ?string $name = null,
        public ?float $quantity = null,
        public ?string $unit = null,
        public ?string $comment = null,
        public ?string $vat = null,
        public ?float $netUnitPrice = null,
        public ?float $netUnitPriceLocal = null,
        public ?float $netPrice = null,
        public ?float $netPriceLocal = null,
        public ?float $vatAmount = null,
        public ?float $vatAmountLocal = null,
        public ?float $grossPrice = null,
        public ?float $grossPriceLocal = null,
    ) {}
}
