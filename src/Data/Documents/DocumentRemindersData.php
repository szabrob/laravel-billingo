<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class DocumentRemindersData extends BillingoData
{
    /**
     * @param  array<int, SentReminderData>|null  $sentReminders
     * @param  array<int, UpcomingReminderData>|null  $upcomingReminders
     * @param  array<int, PostalReminderData>|null  $sentPostalMail
     */
    public function __construct(
        public ?array $sentReminders = null,
        public ?array $upcomingReminders = null,
        public ?array $sentPostalMail = null,
    ) {}
}
