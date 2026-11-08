<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class DocumentBankAccountData extends BillingoData
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $accountNumber = null,
        public ?string $accountNumberIban = null,
        public ?string $swift = null,
    ) {}
}
