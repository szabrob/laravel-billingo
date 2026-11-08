<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Currencies;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Currency;

#[MapName(SnakeCaseMapper::class)]
class ConversionRateData extends BillingoData
{
    public function __construct(
        public ?Currency $fromCurrency = null,
        public ?Currency $toCurrency = null,
        public ?float $conversationRate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $date = null,
    ) {}
}
