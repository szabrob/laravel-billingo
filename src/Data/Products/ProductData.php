<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Products;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\Entitlement;
use Szabrob\LaravelBillingo\Enums\Vat;

#[MapName(SnakeCaseMapper::class)]
class ProductData extends BillingoData
{
    public function __construct(
        public string $name,
        public Currency $currency,
        public Vat $vat,
        public string $unit,
        public ?int $id = null,
        public ?string $comment = null,
        public ?float $netUnitPrice = null,
        public ?float $grossUnitPrice = null,
        public ?string $generalLedgerNumber = null,
        public ?string $generalLedgerTaxcode = null,
        public ?Entitlement $entitlement = null,
        public ?string $ean = null,
        public ?string $sku = null,
        public ?bool $isManage = null,
        public ?float $purchasePrice = null,
        public ?bool $isGenerateEraseCode = null,
        public ?int $connectProductId = null,
        public ?bool $applyDiscount = null,
        public ?bool $applyQuantity = null,
        public ?bool $isConnectProduct = null,
        public ?bool $isDepositFee = null,
    ) {}
}
