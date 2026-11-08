<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DocumentType;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\PaymentStatus;

/**
 * Query filters for listing documents.
 */
#[MapName(SnakeCaseMapper::class)]
class DocumentFilterData extends BillingoData
{
    public function __construct(
        public ?int $blockId = null,
        public ?int $partnerId = null,
        public ?PaymentMethod $paymentMethod = null,
        public ?PaymentStatus $paymentStatus = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $startDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $endDate = null,
        public ?int $startNumber = null,
        public ?int $endNumber = null,
        public ?int $startYear = null,
        public ?int $endYear = null,
        public ?DocumentType $type = null,
        public ?string $query = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $paidStartDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $paidEndDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $fulfillmentStartDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $fulfillmentEndDate = null,
        #[WithCastAndTransformer(DateCast::class, format: 'Y-m-d H:i:s')]
        public ?CarbonImmutable $lastModifiedDate = null,
    ) {}
}
