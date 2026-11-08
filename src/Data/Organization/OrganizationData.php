<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Organization;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class OrganizationData extends BillingoData
{
    public function __construct(
        public ?string $taxCode = null,
        public ?SubscriptionData $subscription = null,
        public ?bool $hasNavConnection = null,
        public ?bool $kobakReceiptFsEnabled = null,
        public ?bool $hasSuccessfulNavReceiptSubmission = null,
    ) {}
}
