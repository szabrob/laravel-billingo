<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum SentEmailStatus: string
{
    case Bounced = 'bounced';
    case Clicked = 'clicked';
    case Complained = 'complained';
    case Delivered = 'delivered';
    case None = 'none';
    case Opened = 'opened';
    case Rejected = 'rejected';
    case RenderingFailed = 'rendering_failed';
    case Sent = 'sent';
}
