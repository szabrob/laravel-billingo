<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum CorrectionType: string
{
    case Advance = 'advance';
    case Canceled = 'canceled';
    case Cancellation = 'cancellation';
    case CertOfCompletion = 'cert_of_completion';
    case DCertOfCompletion = 'd_cert_of_completion';
    case Dossier = 'dossier';
    case Draft = 'draft';
    case DraftOffer = 'draft_offer';
    case DraftOrderForm = 'draft_order_form';
    case DraftWaybill = 'draft_waybill';
    case Invoice = 'invoice';
    case Modification = 'modification';
    case Modified = 'modified';
    case Offer = 'offer';
    case OrderForm = 'order_form';
    case Proforma = 'proforma';
    case Receipt = 'receipt';
    case ReceiptCancellation = 'receipt_cancellation';
    case Waybill = 'waybill';
}
