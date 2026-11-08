<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\DocumentExports;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class LedgerNumberData extends BillingoData
{
    public function __construct(
        public ?string $bevetel = null,
        public ?string $vevo = null,
        public ?string $penztar = null,
        public ?string $afa = null,
    ) {}
}
