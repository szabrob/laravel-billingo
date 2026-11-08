<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class DocumentSummaryData extends BillingoData
{
    /**
     * @param  array<int, VatRateSummaryData>|null  $vatRateSummary
     */
    public function __construct(
        public ?float $netAmount = null,
        public ?float $netAmountLocal = null,
        public ?float $grossAmountLocal = null,
        public ?float $vatAmount = null,
        public ?float $vatAmountLocal = null,
        public ?array $vatRateSummary = null,
    ) {}
}
