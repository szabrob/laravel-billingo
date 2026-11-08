<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum PartnerShowType: string
{
    case ShowAll = 'show_all';
    case ShowIncoming = 'show_incoming';
    case ShowOutgoing = 'show_outgoing';
}
