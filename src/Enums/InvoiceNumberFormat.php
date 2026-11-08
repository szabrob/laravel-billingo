<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum InvoiceNumberFormat: string
{
    case Default = 'default';
    case ShortWithHyphen = 'short_with_hyphen';
}
