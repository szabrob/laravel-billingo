<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DocumentType;
use Szabrob\LaravelBillingo\Enums\DontSendToNavReason;
use Szabrob\LaravelBillingo\Enums\OnlinePayment;
use Szabrob\LaravelBillingo\Enums\Round;

#[MapName(SnakeCaseMapper::class)]
class DocumentSettingsData extends BillingoData
{
    public function __construct(
        public ?bool $mediatedService = null,
        public ?bool $withoutFinancialFulfillment = null,
        public ?OnlinePayment $onlinePayment = null,
        public ?bool $shouldSendEmail = null,
        public ?Round $round = null,
        public ?bool $noSendOnlineszamlaByUser = null,
        public ?DontSendToNavReason $dontSendToNavReason = null,
        public ?string $orderNumber = null,
        public ?int $placeId = null,
        public ?bool $instantPayment = null,
        public ?DocumentType $selectedType = null,
        public ?InstantPaymentRequestData $instantPaymentRequest = null,
        public ?bool $isWaybillHidePrices = null,
    ) {}
}
