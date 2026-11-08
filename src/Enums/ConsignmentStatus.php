<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Enums;

enum ConsignmentStatus: string
{
    case Cancelled = 'Cancelled';
    case Created = 'Created';
    case Delivered = 'Delivered';
    case DeliveredToManufacturing = 'DeliveredToManufacturing';
    case DeliveringError = 'DeliveringError';
    case Enveloping = 'Enveloping';
    case EnvelopingFinished = 'EnvelopingFinished';
    case ManifacturingFinished = 'ManifacturingFinished';
    case ManufacturingFinished = 'ManufacturingFinished';
    case Opened = 'Opened';
    case Ordered = 'Ordered';
    case OutputError = 'OutputError';
    case Posted = 'Posted';
    case PrinterFileCreated = 'PrinterFileCreated';
    case Printing = 'Printing';
    case PrintingError = 'PrintingError';
    case PrintingFinished = 'PrintingFinished';
    case Processed = 'Processed';
    case ProcessingError = 'ProcessingError';
    case Saved = 'Saved';
    case SendingError = 'SendingError';
    case Sent = 'Sent';
    case ValidatedBySystem = 'ValidatedBySystem';
    case WaitForProcessing = 'WaitForProcessing';
    case WrongAddress = 'WrongAddress';
}
