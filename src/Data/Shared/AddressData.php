<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Shared;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Country;

#[MapName(SnakeCaseMapper::class)]
class AddressData extends BillingoData
{
    public function __construct(
        public Country $countryCode,
        public string $postCode,
        public string $city,
        public string $address,
    ) {}
}
