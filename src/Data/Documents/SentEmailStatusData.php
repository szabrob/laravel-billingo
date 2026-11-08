<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\SentEmailStatus;

#[MapName(SnakeCaseMapper::class)]
class SentEmailStatusData extends BillingoData
{
    public function __construct(
        public ?SentEmailStatus $status = null,
        #[WithCastAndTransformer(DateCast::class, format: 'Y-m-d H:i:s')]
        public ?CarbonImmutable $date = null,
    ) {}
}
