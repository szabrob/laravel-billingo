<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\DocumentBlocks;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DocumentBlockType;

#[MapName(SnakeCaseMapper::class)]
class DocumentBlockData extends BillingoData
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $prefix = null,
        public ?string $customField1 = null,
        public ?string $customField2 = null,
        public ?DocumentBlockType $type = null,
    ) {}
}
