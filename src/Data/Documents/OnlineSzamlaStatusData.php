<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class OnlineSzamlaStatusData extends BillingoData
{
    /**
     * @param  array<int, OnlineSzamlaMessageData>|null  $messages
     */
    public function __construct(
        public ?string $transactionId = null,
        public ?string $status = null,
        public ?array $messages = null,
    ) {}
}
