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
class SpendingData extends BillingoData
{
    /**
     * @param  array<int, SpendingItemData>|null  $items
     */
    public function __construct(
        public ?int $id = null,
        public ?int $organizationId = null,
        public ?SpendingCategory $category = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $paidAt = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $fulfillmentDate = null,
        public ?SpendingPartnerData $partner = null,
        public ?string $invoiceNumber = null,
        public ?Currency $currency = null,
        public ?float $conversionRate = null,
        public ?float $totalGross = null,
        public ?float $totalGrossLocal = null,
        public ?float $totalVatAmount = null,
        public ?float $totalVatAmountLocal = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $invoiceDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $dueDate = null,
        public ?PaymentMethod $paymentMethod = null,
        public ?string $comment = null,
        public ?array $items = null,
        public ?bool $isCreatedByNav = null,
    ) {}
}
