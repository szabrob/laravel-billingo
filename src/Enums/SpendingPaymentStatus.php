<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum SpendingPaymentStatus: string
{
    case All = 'all';
    case Expired = 'expired';
    case Paid = 'paid';
    case Unpaid = 'unpaid';
}
