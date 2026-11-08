<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\ConsignmentStatus;

#[MapName(SnakeCaseMapper::class)]
class PostalReminderData extends BillingoData
{
    public function __construct(
        public ?ConsignmentStatus $status = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $sendAt = null,
    ) {}
}
