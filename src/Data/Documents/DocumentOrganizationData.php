<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;

#[MapName(SnakeCaseMapper::class)]
class DocumentOrganizationData extends BillingoData
{
    public function __construct(
        public ?string $name = null,
        public ?string $taxNumber = null,
        public ?DocumentBankAccountData $bankAccount = null,
        public ?AddressData $address = null,
        public ?bool $smallTaxpayer = null,
        public ?string $evNumber = null,
        public ?string $euTaxNumber = null,
        public ?bool $cashSettled = null,
    ) {}
}
