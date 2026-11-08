<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentFormat: string
{
    case Unspecified = '';
    case Electronic = 'electronic';
    case Traditional = 'traditional';
}
