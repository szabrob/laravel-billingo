<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\DocumentExports;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DocumentExportOtherOptions;
use Szabrob\LaravelBillingo\Enums\DocumentExportQueryType;
use Szabrob\LaravelBillingo\Enums\DocumentExportSortBy;
use Szabrob\LaravelBillingo\Enums\DocumentExportType;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;

#[MapName(SnakeCaseMapper::class)]
class CreateDocumentExportData extends BillingoData
{
    public function __construct(
        public DocumentExportQueryType $queryType,
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $startDate,
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $endDate,
        public DocumentExportType $exportType,
        public ?int $documentBlockId = null,
        public ?int $numberStartYear = null,
        public ?int $numberStartSequence = null,
        public ?int $numberEndYear = null,
        public ?int $numberEndSequence = null,
        public ?PaymentMethod $paymentMethod = null,
        public ?DocumentExportSortBy $sortBy = null,
        public ?DocumentExportOtherOptions $otherOptions = null,
        public ?DocumentExportFilterExtraData $filterExtra = null,
    ) {}
}
