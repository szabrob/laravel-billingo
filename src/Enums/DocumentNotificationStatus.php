<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentNotificationStatus: string
{
    case Closed = 'closed';
    case Downloaded = 'downloaded';
    case Failed = 'failed';
    case None = 'none';
    case Opened = 'opened';
    case Readed = 'readed';
}
