<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum DocumentExportType: string
{
    case Armada = 'armada';
    case AwsBatch = 'aws_batch';
    case ExPanda = 'ex_panda';
    case Forintsoft = 'forintsoft';
    case Hessyn = 'hessyn';
    case Ima = 'ima';
    case Infoteka = 'infoteka';
    case KulcsKonyv = 'kulcs_konyv';
    case Maxitax = 'maxitax';
    case NagyMachinator = 'nagy_machinator';
    case NavPtgszlah = 'nav_ptgszlah';
    case NavStatus = 'nav_status';
    case NavXml = 'nav_xml';
    case NavXmlAlias = 'nav_xml_alias';
    case Novitax = 'novitax';
    case ProformaOutstanding = 'proforma_outstanding';
    case Relax = 'relax';
    case Rlb = 'rlb';
    case Rlb60 = 'rlb60';
    case RlbDoubleEntry = 'rlb_double_entry';
    case SimpleCsv = 'simple_csv';
    case SimpleExcel = 'simple_excel';
    case SimpleExcelItems = 'simple_excel_items';
    case Tensoft = 'tensoft';
    case Tensoft29Dot65 = 'tensoft_29_dot_65';
    case UsedEraseCode = 'used_erase_code';
}
