<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum KobakReceiptStatus: string
{
    case ErrorNoAccess = 'error_no_access';
    case ErrorPermanent = 'error_permanent';
    case Pending = 'pending';
    case Success = 'success';
}
