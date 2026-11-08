<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Organization;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Casts\EnumListCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Feature;

#[MapName(SnakeCaseMapper::class)]
class SubscriptionData extends BillingoData
{
    /**
     * @param  array<int, Feature>|null  $features
     */
    public function __construct(
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $expirationDate = null,
        #[WithCastAndTransformer(EnumListCast::class, Feature::class)]
        public ?array $features = null,
    ) {}
}
