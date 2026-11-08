<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Spendings;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;

#[MapName(SnakeCaseMapper::class)]
class SpendingPartnerData extends BillingoData
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?string $taxCode = null,
        public ?AddressData $address = null,
        public ?string $iban = null,
        public ?string $swift = null,
        public ?string $accountNumber = null,
        public ?string $phone = null,
        public ?string $internalComment = null,
        public ?string $groupMemberTaxNumber = null,
    ) {}
}
