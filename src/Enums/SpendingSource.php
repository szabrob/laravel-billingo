<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum SpendingSource: string
{
    case All = 'all';
    case Manual = 'manual';
    case Nav = 'nav';
}
