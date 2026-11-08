<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Data\DocumentBlocks;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Enums\DocumentBlockType;
use Szabrob\LaravelBillingo\Enums\FinancialFulfillmentHighlight;
use Szabrob\LaravelBillingo\Enums\InvoiceNumberFormat;

#[MapName(SnakeCaseMapper::class)]
class CreateDocumentBlockData extends BillingoData
{
    public function __construct(
        public ?string $name = null,
        public ?string $prefix = null,
        public ?DocumentBlockType $type = null,
        public ?InvoiceNumberFormat $invoiceNumberFormat = null,
        public ?string $senderEmail = null,
        public ?string $senderName = null,
        public ?string $bccEmail = null,
        public ?bool $createInvoiceFromPaidProforma = null,
        public ?bool $fulfillmentDateFromProformaPaymentDate = null,
        public ?bool $attachXmlToInvoicePdf = null,
        public ?bool $advancePaidInstant = null,
        public ?FinancialFulfillmentHighlight $financialFulfillmentHighlight = null,
        public ?bool $entitlementsInDocumentComment = null,
        public ?int $bankAccountId = null,
    ) {}
}
