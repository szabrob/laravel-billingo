<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentBlockType: string
{
    case CertificateOfCompletion = 'certificate_of_completion';
    case Dossier = 'dossier';
    case Invoice = 'invoice';
    case Offer = 'offer';
    case OrderForm = 'order_form';
    case Receipt = 'receipt';
    case Waybill = 'waybill';
}
