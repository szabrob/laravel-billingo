<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Shared\DiscountData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentInsertType;
use Szabrob\LaravelBillingo\Enums\DocumentLanguage;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;

#[MapName(SnakeCaseMapper::class)]
class CreateDocumentData extends BillingoData
{
    /**
     * @param  array<int, ProductItemData|CustomItemData>|null  $items
     * @param  array<int, int>|null  $advanceInvoice
     */
    public function __construct(
        public int $partnerId,
        public int $blockId,
        public DocumentInsertType $type,
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $fulfillmentDate,
        #[WithCastAndTransformer(DateCast::class)]
        public CarbonImmutable $dueDate,
        public PaymentMethod $paymentMethod,
        public DocumentLanguage $language,
        public Currency $currency,
        public ?string $vendorId = null,
        public ?int $bankAccountId = null,
        public ?float $conversionRate = null,
        public ?bool $electronic = null,
        public ?bool $paid = null,
        public ?array $items = null,
        public ?string $comment = null,
        public ?DocumentSettingsData $settings = null,
        public ?array $advanceInvoice = null,
        public ?DiscountData $discount = null,
        public ?bool $instantPayment = null,
        public ?InstantPaymentRequestData $instantPaymentRequest = null,
    ) {}
}
