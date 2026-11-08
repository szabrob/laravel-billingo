<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum OnlinePayment: string
{
    case Unspecified = '';
    case Barion = 'Barion';
    case SimplePay = 'SimplePay';
    case Stripe = 'Stripe';
    case No = 'no';
}
