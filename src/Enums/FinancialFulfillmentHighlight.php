<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum FinancialFulfillmentHighlight: string
{
    case Basic = 'basic';
    case Highlighted = 'highlighted';
    case PaidStamp = 'paid_stamp';
}
