<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum ShippingMode: string
{
    case DhlExpress = 'dhl_express';
    case Dpd = 'dpd';
    case ExpressOne = 'express_one';
    case FamaFutar = 'fama_futar';
    case Fedex = 'fedex';
    case Foxpost = 'foxpost';
    case GepardFutar = 'gepard_futar';
    case Gls = 'gls';
    case GoFutar = 'go_futar';
    case HomeDelivery = 'home_delivery';
    case Mpl = 'mpl';
    case None = 'none';
    case Packeta = 'packeta';
    case PersonalDelivery = 'personal_delivery';
    case Sameday = 'sameday';
    case Sprinter = 'sprinter';
    case TntExpress = 'tnt_express';
    case Ups = 'ups';
}
