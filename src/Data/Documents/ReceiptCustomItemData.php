<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\ReceiptVat;

#[MapName(SnakeCaseMapper::class)]
class ReceiptCustomItemData extends BillingoData
{
    public function __construct(
        public float $unitPrice,
        public ReceiptVat $vat,
        public ?string $name = null,
        public ?float $quantity = null,
        public ?string $unit = null,
    ) {}
}
