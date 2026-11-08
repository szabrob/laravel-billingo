<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum PartnerTaxType: string
{
    case Unspecified = '';
    case Foreign = 'FOREIGN';
    case HasTaxNumber = 'HAS_TAX_NUMBER';
    case NoTaxNumber = 'NO_TAX_NUMBER';
}
