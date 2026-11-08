<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum Round: string
{
    case Five = 'five';
    case None = 'none';
    case One = 'one';
    case Ten = 'ten';
}
