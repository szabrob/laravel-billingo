<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Entitlement;
use Szabrob\LaravelBillingo\Enums\Vat;

#[MapName(SnakeCaseMapper::class)]
class DocumentItemData extends BillingoData
{
    public function __construct(
        public ?int $productId = null,
        public ?string $name = null,
        public ?float $netUnitAmount = null,
        public ?float $quantity = null,
        public ?string $unit = null,
        public ?float $netAmount = null,
        public ?float $grossAmount = null,
        public ?Vat $vat = null,
        public ?float $vatAmount = null,
        public ?Entitlement $entitlement = null,
        public ?string $comment = null,
        public ?string $sku = null,
        public ?string $dataEraserCodes = null,
        public ?bool $isDepositFee = null,
        public ?string $ean = null,
        public ?bool $isConnectProduct = null,
        public ?ConnectedItemData $connectedItem = null,
        public ?ConnectedItemSettingData $connectDocumentItemSetting = null,
    ) {}
}
