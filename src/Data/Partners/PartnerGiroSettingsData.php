<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Partners;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;

#[MapName(SnakeCaseMapper::class)]
class PartnerGiroSettingsData extends BillingoData
{
    public function __construct(
        public ?bool $giroDefaultSettings = null,
        public ?bool $giroPaymentRequestEnabled = null,
        public ?bool $giroDifferentAmountAllowed = null,
    ) {}
}
