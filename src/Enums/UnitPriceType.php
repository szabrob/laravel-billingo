<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum UnitPriceType: string
{
    case Gross = 'gross';
    case Net = 'net';
}
