<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentExportState: string
{
    case Fail = 'fail';
    case Pending = 'pending';
    case Processing = 'processing';
    case Success = 'success';
    case Warning = 'warning';
}
