<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum PaymentStatus: string
{
    case Expired = 'expired';
    case None = 'none';
    case Outstanding = 'outstanding';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
}
