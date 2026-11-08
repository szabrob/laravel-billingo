<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum TaxNumberCheckResult: string
{
    case ExternalNavServiceUnreachable = 'external_nav_service_unreachable';
    case InvalidTaxNumber = 'invalid_tax_number';
    case NoOnlineSzamlaSettings = 'no_online_szamla_settings';
    case NonExistTaxNumber = 'non_exist_tax_number';
    case PostalCodeOfThisTaxNumberIsInvalid = 'postal_code_of_this_tax_number_is_invalid.';
    case ValidationOk = 'validation_ok';
}
