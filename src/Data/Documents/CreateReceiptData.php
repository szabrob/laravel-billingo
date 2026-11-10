<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\Documents;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentType;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;

#[MapName(SnakeCaseMapper::class)]
class CreateReceiptData extends BillingoData
{
    /**
     * The API spec (3.0.15) marks "electronic" as optional, but the Billingo API rejects receipts without it.
     *
     * @param  array<int, string>|null  $emails
     * @param  array<int, ReceiptProductItemData|ReceiptCustomItemData>|null  $items
     */
    public function __construct(
        public int $blockId,
        public DocumentType $type,
        public PaymentMethod $paymentMethod,
        public Currency $currency,
        public ?string $vendorId = null,
        public ?int $partnerId = null,
        public ?string $name = null,
        public ?array $emails = null,
        public ?float $conversionRate = null,
        public bool $electronic = false,
        public ?array $items = null,
    ) {}
}
