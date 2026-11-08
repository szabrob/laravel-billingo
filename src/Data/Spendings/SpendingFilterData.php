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
use Szabrob\LaravelBillingo\Enums\SpendingDateType;
use Szabrob\LaravelBillingo\Enums\SpendingPaymentStatus;
use Szabrob\LaravelBillingo\Enums\SpendingSource;

/**
 * Query filters for listing spendings.
 */
#[MapName(SnakeCaseMapper::class)]
class SpendingFilterData extends BillingoData
{
    public function __construct(
        #[MapName('q')]
        public ?string $query = null,
        public ?SpendingDateType $spendingDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $startDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $endDate = null,
        public ?SpendingPaymentStatus $paymentStatus = null,
        public ?SpendingSource $spendingType = null,
        #[MapName('categories')]
        public ?SpendingCategory $category = null,
        #[MapName('currencies')]
        public ?Currency $currency = null,
        #[MapName('payment_methods')]
        public ?PaymentMethod $paymentMethod = null,
    ) {}
}
