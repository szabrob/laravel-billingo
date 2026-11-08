<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\DocumentExports;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class DocumentExportFilterExtraData extends BillingoData
{
    public function __construct(
        public ?string $tensoftVkod = null,
        public ?LedgerNumberData $ledgerNumber = null,
        public ?string $forintsoftKonyvelesiNaploSzam = null,
        public ?string $positiveLedgerNumber = null,
        public ?string $negativeLedgerNumber = null,
        public ?bool $rlbKata = null,
        public ?string $rlbNote = null,
        public ?string $novitaxNaplokod = null,
        public ?bool $useGrossValues = null,
    ) {}
}
