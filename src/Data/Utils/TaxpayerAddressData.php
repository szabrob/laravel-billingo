<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Utils;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class TaxpayerAddressData extends BillingoData
{
    public function __construct(
        public ?string $type = null,
        public ?string $countryCode = null,
        public ?string $region = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public ?string $streetName = null,
        public ?string $publicPlaceCategory = null,
        public ?string $number = null,
        public ?string $building = null,
        public ?string $staircase = null,
        public ?string $floor = null,
        public ?string $door = null,
        public ?string $lotNumber = null,
    ) {}
}
