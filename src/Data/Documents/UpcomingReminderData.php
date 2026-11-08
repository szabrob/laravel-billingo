<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class UpcomingReminderData extends BillingoData
{
    /**
     * @param  array<int, string>|null  $emails
     */
    public function __construct(
        public ?string $subject = null,
        public ?array $emails = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $sendAt = null,
    ) {}
}
