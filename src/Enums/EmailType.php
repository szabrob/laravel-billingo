<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum EmailType: string
{
    case Affiliate = 'affiliate';
    case ApiLimitWarning = 'api_limit_warning';
    case AuthLog = 'auth_log';
    case BankAccountSave = 'bank_account_save';
    case BankCardExpiryReminder = 'bank_card_expiry_reminder';
    case BankCardNextCharge = 'bank_card_next_charge';
    case BobExpiry = 'bob_expiry';
    case BusinessPlus = 'business_plus';
    case BuyApiLimit = 'buy_api_limit';
    case CannotRenewSubscription = 'cannot_renew_subscription';
    case CmContractNotification = 'cm_contract_notification';
    case CmSubscriptionLimit = 'cm_subscription_limit';
    case DocumentReminder = 'document_reminder';
    case EmailChanged = 'email_changed';
    case EmailTfaToken = 'email_tfa_token';
    case FakePincodeSms = 'fake_pincode_sms';
    case FlatTaxDeadlineTask = 'flat_tax_deadline_task';
    case NewInvoice = 'new_invoice';
    case OnlineSzamlaEmail = 'online_szamla_email';
    case OrganizationChanged = 'organization_changed';
    case PaidDocument = 'paid_document';
    case PartnermonitorNotification = 'partnermonitor_notification';
    case PasswordlessLogin = 'passwordless_login';
    case ProformaExpiredReminder = 'proforma_expired_reminder';
    case ProformaExpiryReminder = 'proforma_expiry_reminder';
    case SoftposCancelTransaction = 'softpos_cancel_transaction';
    case SoftposNotification = 'softpos_notification';
    case TaxpayerVerification = 'taxpayer_verification';
    case TmConsultation = 'tm_consultation';
    case TmNotification = 'tm_notification';
    case TmPrequalification = 'tm_prequalification';
    case TransactionExpiredReminder = 'transaction_expired_reminder';
    case TransactionExpiryReminder = 'transaction_expiry_reminder';
    case UnasAutosyncDisabled = 'unas_autosync_disabled';
    case UserChanged = 'user_changed';
    case UserInvitation = 'user_invitation';
    case UserRegistration = 'user_registration';
    case UserResetPassword = 'user_reset_password';
    case UserTerms = 'user_terms';
}
