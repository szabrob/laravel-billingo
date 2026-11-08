<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Entitlement;
use Szabrob\LaravelBillingo\Enums\UnitPriceType;
use Szabrob\LaravelBillingo\Enums\Vat;

#[MapName(SnakeCaseMapper::class)]
class CustomItemData extends BillingoData
{
    public function __construct(
        public string $name,
        public float $unitPrice,
        public UnitPriceType $unitPriceType,
        public float $quantity,
        public string $unit,
        public Vat $vat,
        public ?string $comment = null,
        public ?Entitlement $entitlement = null,
        public ?string $sku = null,
        public ?string $dataEraserCodes = null,
        public ?bool $isGenerateEraseCode = null,
    ) {}
}
