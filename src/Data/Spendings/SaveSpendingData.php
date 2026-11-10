<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Spendings;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\SpendingCategory;

#[MapName(SnakeCaseMapper::class)]
class SaveSpendingData extends BillingoData
{
    /**
     * The API spec (3.0.15) marks the invoice number, dates and conversion rate as optional,
     * but the Billingo API rejects spendings without them.
     */
    public function __construct(
        public Currency $currency,
        public float $totalGross,
        public float $totalGrossHuf,
        public float $totalVatAmount,
        public float $totalVatAmountHuf,
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $fulfillmentDate,
        public SpendingCategory $category,
        public PaymentMethod $paymentMethod,
        public string $invoiceNumber,
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $invoiceDate,
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $dueDate,
        public float $conversionRate = 1.0,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $paidAt = null,
        public ?string $comment = null,
        public ?int $partnerId = null,
    ) {}
}
