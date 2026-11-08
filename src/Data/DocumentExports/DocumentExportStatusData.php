<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\DocumentExports;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DocumentExportState;

#[MapName(SnakeCaseMapper::class)]
class DocumentExportStatusData extends BillingoData
{
    public function __construct(
        public ?string $id = null,
        public ?DocumentExportState $state = null,
        public ?string $message = null,
    ) {}
}
