<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Partners;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\ShippingMode;

#[MapName(SnakeCaseMapper::class)]
class PartnerShippingData extends BillingoData
{
    public function __construct(
        public ?bool $match = null,
        public ?string $name = null,
        public ?ShippingMode $mode = null,
        public ?AddressData $address = null,
    ) {}
}
