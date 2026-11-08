<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Partners;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Shared\DiscountData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentForm;
use Szabrob\LaravelBillingo\Enums\DocumentLanguage;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;

#[MapName(SnakeCaseMapper::class)]
class PartnerBillingSettingsData extends BillingoData
{
    public function __construct(
        public ?PaymentMethod $paymentMethod = null,
        public ?DocumentForm $documentForm = null,
        public ?int $dueDays = null,
        public ?Currency $documentCurrency = null,
        public ?DocumentLanguage $templateLanguageCode = null,
        public ?DiscountData $discount = null,
        public ?CustomLabelData $customLabel = null,
    ) {}
}
