<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum SpendingDateType: string
{
    case DueDate = 'due_date';
    case FulfillmentDate = 'fulfillment_date';
    case InvoiceDate = 'invoice_date';
}
