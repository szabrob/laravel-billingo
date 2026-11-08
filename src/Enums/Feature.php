<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum Feature: string
{
    case ApiLimitBasic = 'api_limit_basic';
    case ApiLimitMax = 'api_limit_max';
    case ApiLimitPro = 'api_limit_pro';
    case ApiLimitProLimited = 'api_limit_pro_limited';
    case ApiLimitStandard = 'api_limit_standard';
    case BanksyncPlus = 'banksync_plus';
    case BillingoBusiness = 'billingo_business';
    case BillingoOne = 'billingo_one';
    case CampaignManagerBasic = 'campaign_manager_basic';
    case CampaignManagerStandard = 'campaign_manager_standard';
    case CeginfoEnterprise = 'ceginfo_enterprise';
    case CeginfoPro = 'ceginfo_pro';
    case CeginfoStandard = 'ceginfo_standard';
    case FlatTaxPlus = 'flat_tax_plus';
    case Innovator = 'innovator';
    case Inventory = 'inventory';
    case LimitIncrease = 'limit_increase';
    case Master = 'master';
    case PartnermonitorBasic = 'partnermonitor_basic';
    case PartnermonitorPro = 'partnermonitor_pro';
    case PartnermonitorStandard = 'partnermonitor_standard';
    case Softpos = 'softpos';
    case SubscriptionBasic = 'subscription_basic';
    case SubscriptionPro = 'subscription_pro';
    case SubscriptionStandard = 'subscription_standard';
    case TendermonitorStandard = 'tendermonitor_standard';
    case TendermonitorVip = 'tendermonitor_vip';
    case Whitelabel = 'whitelabel';
}
