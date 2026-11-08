<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Utils;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\TaxNumberCheckResult;

#[MapName(SnakeCaseMapper::class)]
class TaxNumberCheckData extends BillingoData
{
    /**
     * @param  array<int, TaxpayerAddressData>|null  $addresses
     */
    public function __construct(
        public ?string $taxNumber = null,
        public ?TaxNumberCheckResult $result = null,
        public ?string $taxpayerName = null,
        public ?string $taxpayerShortName = null,
        public ?array $addresses = null,
    ) {}
}
