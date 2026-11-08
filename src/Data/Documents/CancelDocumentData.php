<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class CancelDocumentData extends BillingoData
{
    public function __construct(
        public ?string $cancellationReason = null,
        public ?string $cancellationRecipients = null,
    ) {}
}
