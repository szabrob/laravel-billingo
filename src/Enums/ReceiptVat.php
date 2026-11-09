<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum ReceiptVat: string
{
    case None = 'none';
    case Percent5 = '5%';
    case Percent18 = '18%';
    case Percent27 = '27%';
    case AAM = 'AAM';
    case TAM = 'TAM';
}
