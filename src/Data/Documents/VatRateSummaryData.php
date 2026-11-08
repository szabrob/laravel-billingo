<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class VatRateSummaryData extends BillingoData
{
    public function __construct(
        public ?string $vatName = null,
        public ?float $vatPercentage = null,
        public ?float $vatRateNetAmount = null,
        public ?float $vatRateVatAmount = null,
        public ?float $vatRateVatAmountLocal = null,
        public ?float $vatRateGrossAmount = null,
    ) {}
}
