<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCastAndTransformer;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Casts\DateCast;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Data\Shared\DiscountData;
use Szabrob\LaravelBillingo\Enums\CorrectionType;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentLanguage;
use Szabrob\LaravelBillingo\Enums\DocumentNotificationStatus;
use Szabrob\LaravelBillingo\Enums\DocumentType;
use Szabrob\LaravelBillingo\Enums\KobakReceiptStatus;
use Szabrob\LaravelBillingo\Enums\OnlineSzamlaStatus;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\PaymentStatus;

#[MapName(SnakeCaseMapper::class)]
class DocumentData extends BillingoData
{
    /**
     * @param  array<int, string>|null  $tags
     * @param  array<int, DocumentItemData>|null  $items
     * @param  array<int, RelatedDocumentData>|null  $relatedDocuments
     */
    public function __construct(
        public ?int $id = null,
        public ?string $invoiceNumber = null,
        public ?DocumentType $type = null,
        public ?bool $cancelled = null,
        public ?int $blockId = null,
        public ?PaymentStatus $paymentStatus = null,
        public ?PaymentMethod $paymentMethod = null,
        public ?float $grossTotal = null,
        public ?Currency $currency = null,
        public ?float $conversionRate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $invoiceDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $fulfillmentDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $dueDate = null,
        #[WithCastAndTransformer(DateCast::class)]
        public ?CarbonImmutable $paidDate = null,
        public ?DocumentOrganizationData $organization = null,
        public ?PartnerData $partner = null,
        public ?DocumentPartnerData $documentPartner = null,
        public ?bool $electronic = null,
        public ?string $comment = null,
        public ?array $tags = null,
        public ?DocumentNotificationStatus $notificationStatus = null,
        public ?DocumentLanguage $language = null,
        public ?array $items = null,
        public ?DocumentSummaryData $summary = null,
        public ?DocumentSettingsData $settings = null,
        public ?OnlineSzamlaStatus $onlineSzamlaStatus = null,
        public ?KobakReceiptStatus $kobakReceiptStatus = null,
        public ?string $kobakReceiptMessage = null,
        public ?array $relatedDocuments = null,
        public ?DiscountData $discount = null,
        public ?CorrectionType $correctionType = null,
        public ?int $recurringId = null,
    ) {}
}
