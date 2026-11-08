<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum SpendingCategory: string
{
    case AccountingAndAuditing = 'accounting_and_auditing';
    case Advertisement = 'advertisement';
    case Bonus = 'bonus';
    case BusinessInsurance = 'business_insurance';
    case Cleaning = 'cleaning';
    case ConstructionAndRepair = 'construction_and_repair';
    case Consulting = 'consulting';
    case ConsumerGoods = 'consumer_goods';
    case Contractors = 'contractors';
    case Development = 'development';
    case Dividends = 'dividends';
    case Donations = 'donations';
    case DutyTaxes = 'duty_taxes';
    case EducationAndTrainings = 'education_and_trainings';
    case Electricity = 'electricity';
    case Electronics = 'electronics';
    case EmployeeBenefits = 'employee_benefits';
    case Entertainment = 'entertainment';
    case EquipmentInsurance = 'equipment_insurance';
    case FederalTaxes = 'federal_taxes';
    case Fees = 'fees';
    case Financials = 'financials';
    case Fines = 'fines';
    case Gas = 'gas';
    case GasAndFuel = 'gas_and_fuel';
    case HealthInsurance = 'health_insurance';
    case HumanResources = 'human_resources';
    case Income = 'income';
    case IncomeTaxes = 'income_taxes';
    case Insurance = 'insurance';
    case Interest = 'interest';
    case Internet = 'internet';
    case Investments = 'investments';
    case Leasing = 'leasing';
    case Legal = 'legal';
    case LiabilityInsurance = 'liability_insurance';
    case Loans = 'loans';
    case Meals = 'meals';
    case Mortgage = 'mortgage';
    case OfficeRent = 'office_rent';
    case OnlineSubscriptions = 'online_subscriptions';
    case Other = 'other';
    case Overheads = 'overheads';
    case PayrollTaxes = 'payroll_taxes';
    case Phone = 'phone';
    case Prepayments = 'prepayments';
    case ProfessionalInsurance = 'professional_insurance';
    case PropertyTaxes = 'property_taxes';
    case RealEstate = 'real_estate';
    case Returns = 'returns';
    case Sales = 'sales';
    case Service = 'service';
    case ServiceAndParts = 'service_and_parts';
    case Shipping = 'shipping';
    case Software = 'software';
    case StaffOutsourcing = 'staff_outsourcing';
    case Stock = 'stock';
    case Storage = 'storage';
    case SuppliesAndFurniture = 'supplies_and_furniture';
    case TangibleAssets = 'tangible_assets';
    case TaxReturn = 'tax_return';
    case Taxes = 'taxes';
    case Taxi = 'taxi';
    case Transport = 'transport';
    case Travel = 'travel';
    case Utilities = 'utilities';
    case Vat = 'vat';
    case VehicleInsurance = 'vehicle_insurance';
    case Wages = 'wages';
    case Water = 'water';
}
