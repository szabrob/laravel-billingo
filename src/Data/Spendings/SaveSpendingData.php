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
        public ?float $conversionRate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $paidAt = null,
        public ?string $comment = null,
        public ?string $invoiceNumber = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $invoiceDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $dueDate = null,
        public ?int $partnerId = null,
    ) {}
}
