<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class SendDocumentData extends BillingoData
{
    /**
     * @param  array<int, string>|null  $emails
     */
    public function __construct(
        public ?array $emails = null,
    ) {}
}
