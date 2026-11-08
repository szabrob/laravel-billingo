<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentExportOtherOptions: string
{
    case All = 'all';
    case Expired = 'expired';
    case Outstanding = 'outstanding';
}
