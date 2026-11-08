<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum PaymentMethod: string
{
    case Aruhitel = 'aruhitel';
    case Bankcard = 'bankcard';
    case Barion = 'barion';
    case Barter = 'barter';
    case Cash = 'cash';
    case CashOnDelivery = 'cash_on_delivery';
    case CodBalance = 'cod_balance';
    case Combined = 'combined';
    case Coupon = 'coupon';
    case Credit = 'credit';
    case Ebay = 'ebay';
    case EloreUtalas = 'elore_utalas';
    case EpKartya = 'ep_kartya';
    case Kompenzacio = 'kompenzacio';
    case Levonas = 'levonas';
    case OnlineBankcard = 'online_bankcard';
    case Other = 'other';
    case Paylike = 'paylike';
    case Payoneer = 'payoneer';
    case Paypal = 'paypal';
    case PaypalUtolag = 'paypal_utolag';
    case Payu = 'payu';
    case PickPackPont = 'pick_pack_pont';
    case PostaiCsekk = 'postai_csekk';
    case Postautalvany = 'postautalvany';
    case Revolut = 'revolut';
    case SimplePay = 'simple_pay';
    case Skrill = 'skrill';
    case Stripe = 'stripe';
    case SzepCard = 'szep_card';
    case Transferwise = 'transferwise';
    case Upwork = 'upwork';
    case Utalvany = 'utalvany';
    case Valto = 'valto';
    case WireTransfer = 'wire_transfer';
}
