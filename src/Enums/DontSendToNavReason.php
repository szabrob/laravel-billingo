<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DontSendToNavReason: string
{
    case DontSendForeign = 'dont_send_foreign';
    case DontSendOss = 'dont_send_oss';
    case DontSendOther = 'dont_send_other';
}
