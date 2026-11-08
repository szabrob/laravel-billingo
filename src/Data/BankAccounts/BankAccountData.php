<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\BankAccounts;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Currency;

#[MapName(SnakeCaseMapper::class)]
class BankAccountData extends BillingoData
{
    public function __construct(
        public string $name,
        public string $accountNumber,
        public Currency $currency,
        public ?int $id = null,
        public ?string $accountNumberIban = null,
        public ?string $swift = null,
        public ?bool $needQr = null,
    ) {}
}
