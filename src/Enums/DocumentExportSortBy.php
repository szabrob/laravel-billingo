<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentExportSortBy: string
{
    case FulfillmentDate = 'fulfillment_date';
    case InvoiceDate = 'invoice_date';
    case InvoiceRawNumber = 'invoice_raw_number';
}
