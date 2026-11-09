<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Sleep;
use Szabrob\LaravelBillingo\Data\DocumentExports\CreateDocumentExportData;
use Szabrob\LaravelBillingo\Enums\DocumentExportQueryType;
use Szabrob\LaravelBillingo\Enums\DocumentExportState;
use Szabrob\LaravelBillingo\Enums\DocumentExportType;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => sandbox());

it('exports documents for accounting', function () {
    $id = Billingo::documentExports()->create(new CreateDocumentExportData(
        queryType: DocumentExportQueryType::InvoiceDate,
        startDate: CarbonImmutable::today()->subDays(30),
        endDate: CarbonImmutable::today(),
        exportType: DocumentExportType::SimpleCsv,
    ));

    expect($id)->not->toBeEmpty();

    $deadline = time() + 120;

    do {
        $status = Billingo::documentExports()->poll($id);
        $pending = in_array($status->state, [DocumentExportState::Pending, DocumentExportState::Processing], true);

        if ($pending) {
            Sleep::sleep(3);
        }
    } while ($pending && time() < $deadline);

    expect($status->state)->toBeIn([DocumentExportState::Success, DocumentExportState::Warning], (string) $status->message)
        ->and(Billingo::documentExports()->download($id))->not->toBeEmpty();
});
