<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Utils;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class ServerTimeData extends BillingoData
{
    public function __construct(
        public ?int $epoch = null,
        public ?string $formatted = null,
        public ?string $w3c = null,
        public ?string $timezone = null,
    ) {}
}
