<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentInsertType: string
{
    case Advance = 'advance';
    case Draft = 'draft';
    case Invoice = 'invoice';
    case Proforma = 'proforma';
}
