<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\EmailType;

#[MapName(SnakeCaseMapper::class)]
class SentReminderData extends BillingoData
{
    /**
     * @param  array<int, string>|null  $recipients
     */
    public function __construct(
        public ?EmailType $type = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $deliveryAt = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $sendAt = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $openAt = null,
        public ?string $subject = null,
        public ?array $recipients = null,
        public ?SentEmailStatusData $status = null,
    ) {}
}
