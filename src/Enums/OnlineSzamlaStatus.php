<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum OnlineSzamlaStatus: string
{
    case Aborted = 'aborted';
    case Done = 'done';
    case DontSendExternalImport = 'dont_send_external_import';
    case DontSendForeign = 'dont_send_foreign';
    case DontSendOss = 'dont_send_oss';
    case DontSendOther = 'dont_send_other';
    case EmptyOrganizationCountryCode = 'empty_organization_country_code';
    case EmptyPartnerCountryCode = 'empty_partner_country_code';
    case EmptyTax = 'empty_tax';
    case Forbidden = 'forbidden';
    case InvalidAddress = 'invalid_address';
    case InvalidClient = 'invalid_client';
    case InvalidConversionRate = 'invalid_conversion_rate';
    case InvalidCustomer = 'invalid_customer';
    case InvalidInvoiceReference = 'invalid_invoice_reference';
    case InvalidPostalcode = 'invalid_postalcode';
    case InvalidRequestSignature = 'invalid_request_signature';
    case InvalidSecurityUser = 'invalid_security_user';
    case InvalidTax = 'invalid_tax';
    case InvalidTaxNumber = 'invalid_tax_number';
    case InvalidUserRelation = 'invalid_user_relation';
    case InvalidVatData = 'invalid_vat_data';
    case InvoiceNumberNotUnique = 'invoice_number_not_unique';
    case KobakProcessing = 'kobak_processing';
    case MissingDocumentItemName = 'missing_document_item_name';
    case NavWarn = 'nav_warn';
    case NoOnlineSzamlaSettings = 'no_online_szamla_settings';
    case NoSendByUser = 'no_send_by_user';
    case NonExistTaxNumber = 'non_exist_tax_number';
    case NotUnique = 'not unique';
    case NotChecked = 'not_checked';
    case NotRegisteredCustomer = 'not_registered_customer';
    case Processing = 'processing';
    case Received = 'received';
    case Saved = 'saved';
    case SendFailed = 'send_failed';
    case Sent = 'sent';
    case Started = 'started';
    case TechnicalError = 'technical_error';
    case UnderTaxLimit = 'under_tax_limit';
    case UserHasInvalidKobak = 'user_has_invalid_kobak';
    case UserHasnotKobak = 'user_hasnot_kobak';
    case ValidationOk = 'validation_ok';
}
