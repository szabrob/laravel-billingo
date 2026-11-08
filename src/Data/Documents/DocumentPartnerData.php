<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Partners\PartnerShippingData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\PartnerTaxType;

#[MapName(SnakeCaseMapper::class)]
class DocumentPartnerData extends BillingoData
{
    /**
     * @param  array<int, string>|null  $emails
     */
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?AddressData $address = null,
        public ?array $emails = null,
        public ?string $taxcode = null,
        public ?string $iban = null,
        public ?string $swift = null,
        public ?string $accountNumber = null,
        public ?string $phone = null,
        public ?PartnerTaxType $taxType = null,
        public ?PartnerShippingData $partnerShipping = null,
    ) {}
}
