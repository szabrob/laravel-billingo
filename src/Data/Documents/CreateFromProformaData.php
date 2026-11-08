<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DocumentFormat;
use Szabrob\LaravelBillingo\Enums\DocumentType;

#[MapName(SnakeCaseMapper::class)]
class CreateFromProformaData extends BillingoData
{
    public function __construct(
        public ?DocumentType $documentType = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $fulfillmentDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $dueDate = null,
        public ?DocumentFormat $documentFormat = null,
        public ?string $comment = null,
    ) {}
}
