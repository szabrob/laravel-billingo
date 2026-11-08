<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;

#[MapName(SnakeCaseMapper::class)]
class PaymentData extends BillingoData
{
    public function __construct(
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $date,
        public float $price,
        public PaymentMethod $paymentMethod,
        public ?string $voucherNumber = null,
        public ?float $conversionRate = null,
    ) {}
}
