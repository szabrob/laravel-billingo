<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Szabrob\LaravelBillingo\Data\Documents\CancelDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateFromProformaData;
use Szabrob\LaravelBillingo\Data\Documents\CreateModificationDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateReceiptData;
use Szabrob\LaravelBillingo\Data\Documents\CustomItemData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentFilterData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentRemindersData;
use Szabrob\LaravelBillingo\Data\Documents\PaymentData;
use Szabrob\LaravelBillingo\Data\Documents\ReceiptCustomItemData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentBlockType;
use Szabrob\LaravelBillingo\Enums\DocumentInsertType;
use Szabrob\LaravelBillingo\Enums\DocumentLanguage;
use Szabrob\LaravelBillingo\Enums\DocumentType;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\PaymentStatus;
use Szabrob\LaravelBillingo\Enums\ReceiptVat;
use Szabrob\LaravelBillingo\Enums\UnitPriceType;
use Szabrob\LaravelBillingo\Enums\Vat;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Exceptions\DocumentNotReadyException;
use Szabrob\LaravelBillingo\Facades\Billingo;

/**
 * A HUF document with one ad-hoc line: 2 × 10 000 HUF net + 27% VAT = 25 400 HUF gross.
 */
function sandboxDocument(int $partnerId, DocumentInsertType $type, ?string $vendorId = null, ?bool $electronic = true): CreateDocumentData
{
    return new CreateDocumentData(
        partnerId: $partnerId,
        blockId: sandboxBlockId(DocumentBlockType::Invoice),
        type: $type,
        fulfillmentDate: CarbonImmutable::today(),
        dueDate: CarbonImmutable::today(),
        paymentMethod: PaymentMethod::Cash,
        language: DocumentLanguage::Hu,
        currency: Currency::HUF,
        vendorId: $vendorId,
        electronic: $electronic,
        items: [
            new CustomItemData(name: 'Integration test', unitPrice: 10000, unitPriceType: UnitPriceType::Net, quantity: 2, unit: 'db', vat: Vat::Percent27),
        ],
        comment: 'Created by the laravel-billingo integration tests.',
    );
}

function sandboxCancel(?DocumentData $document): void
{
    if ($document?->id !== null) {
        rescue(fn () => Billingo::documents()->cancel((int) $document->id, new CancelDocumentData(cancellationReason: 'laravel-billingo integration test')), report: false);
    }
}

function sandboxDeletePartner(?int $id): void
{
    if ($id !== null) {
        rescue(fn () => Billingo::partners()->delete($id), report: false);
    }
}

it('saves, copies and deletes draft documents', function () {
    sandbox();
    $partner = sandboxPartner();

    try {
        $draft = Billingo::documents()->create(sandboxDocument((int) $partner->id, DocumentInsertType::Draft));

        expect($draft->type)->toBe(DocumentType::Draft)
            ->and($draft->grossTotal)->toEqual(25400)
            ->and(Billingo::documents()->get((int) $draft->id)->partner?->id)->toBe($partner->id);

        $copy = Billingo::documents()->copy((int) $draft->id);

        expect($copy->id)->not->toBe($draft->id)
            ->and($copy->type)->toBe(DocumentType::Draft);

        Billingo::documents()->delete((int) $copy->id);
        Billingo::documents()->delete((int) $draft->id);

        expect(fn () => Billingo::documents()->get((int) $draft->id))->toThrow(BillingoException::class);
    } finally {
        sandboxDeletePartner($partner->id);
    }
});

it('saves a document without the optional electronic field', function () {
    sandbox();
    $partner = sandboxPartner();

    try {
        // The API spec marks "electronic" as optional; receipts are rejected without it ("The electronic field must be present").
        $draft = Billingo::documents()->create(sandboxDocument((int) $partner->id, DocumentInsertType::Draft, electronic: null));

        expect($draft->type)->toBe(DocumentType::Draft);

        Billingo::documents()->delete((int) $draft->id);
    } finally {
        sandboxDeletePartner($partner->id);
    }
});

it('issues an invoice and manages its payments, pdf and cancellation', function () {
    sandboxIssuingDocuments();
    $partner = sandboxPartner();
    $vendorId = str_replace(' ', '-', sandboxName('invoice'));
    $invoice = null;

    try {
        $invoice = Billingo::documents()->create(sandboxDocument((int) $partner->id, DocumentInsertType::Invoice, $vendorId));
        $id = (int) $invoice->id;

        expect($invoice->type)->toBe(DocumentType::Invoice)
            ->and($invoice->invoiceNumber)->toBeString()->not->toBeEmpty()
            ->and($invoice->grossTotal)->toEqual(25400)
            ->and($invoice->currency)->toBe(Currency::HUF)
            ->and($invoice->invoiceDate?->toDateString())->toBe(CarbonImmutable::today()->toDateString());

        // Lookups
        expect(Billingo::documents()->findByVendorId($vendorId)->id)->toBe($id)
            ->and(Billingo::documents()->list(new DocumentFilterData(partnerId: $partner->id))->data->pluck('id')->all())->toContain($id)
            ->and(Billingo::documents()->publicUrl($id))->toStartWith('https://')
            ->and(Billingo::documents()->reminders($id))->toBeInstanceOf(DocumentRemindersData::class);

        // PDF, generated asynchronously
        $pdf = sandboxEventually(fn (): string => Billingo::documents()->download($id), DocumentNotReadyException::class, 120);

        expect($pdf)->toStartWith('%PDF');

        // Payments
        $payments = Billingo::documents()->updatePayments($id, [
            new PaymentData(date: CarbonImmutable::today(), price: 25400, paymentMethod: PaymentMethod::Cash),
        ]);

        expect($payments)->toHaveCount(1)
            ->and($payments->first()?->price)->toEqual(25400)
            ->and(Billingo::documents()->payments($id))->toHaveCount(1)
            ->and(Billingo::documents()->get($id)->paymentStatus)->toBe(PaymentStatus::Paid);

        Billingo::documents()->deletePayments($id);

        expect(Billingo::documents()->payments($id))->toHaveCount(0)
            ->and(Billingo::documents()->get($id)->paymentStatus)->not->toBe(PaymentStatus::Paid);

        // Cancellation
        Billingo::documents()->cancel($id, new CancelDocumentData(cancellationReason: 'laravel-billingo integration test'));
        $invoice = null;

        expect(Billingo::documents()->get($id)->cancelled)->toBeTrue();
    } finally {
        sandboxCancel($invoice);
        sandboxDeletePartner($partner->id);
    }
});

it('sends an invoice by e-mail', function () {
    sandboxIssuingDocuments();
    $email = getenv('BILLINGO_SANDBOX_EMAIL');

    if (! is_string($email) || $email === '') {
        $this->markTestSkipped('Set BILLINGO_SANDBOX_EMAIL to send documents by e-mail.');
    }

    $partner = sandboxPartner();
    $invoice = null;

    try {
        $invoice = Billingo::documents()->create(sandboxDocument((int) $partner->id, DocumentInsertType::Invoice));

        expect(Billingo::documents()->send((int) $invoice->id, [$email]))->toContain($email);
    } finally {
        sandboxCancel($invoice);
        sandboxDeletePartner($partner->id);
    }
});

it('issues a modification invoice', function () {
    sandboxIssuingDocuments();
    $partner = sandboxPartner();
    $invoice = null;

    try {
        $invoice = Billingo::documents()->create(sandboxDocument((int) $partner->id, DocumentInsertType::Invoice));

        // Documents what a negative quantity on a modification invoice means: one of the two lines is taken back.
        $modification = Billingo::documents()->createModification((int) $invoice->id, new CreateModificationDocumentData(
            comment: 'laravel-billingo integration test',
            items: [
                new CustomItemData(name: 'Integration test', unitPrice: 10000, unitPriceType: UnitPriceType::Net, quantity: -1, unit: 'db', vat: Vat::Percent27),
            ],
        ));

        expect($modification->type)->toBe(DocumentType::Modification)
            ->and($modification->grossTotal)->toEqual(-12700);
    } finally {
        sandboxCancel($invoice);
        sandboxDeletePartner($partner->id);
    }
});

it('turns a proforma into an invoice', function () {
    sandboxIssuingDocuments();
    $partner = sandboxPartner();
    $invoice = null;

    try {
        $proforma = Billingo::documents()->create(sandboxDocument((int) $partner->id, DocumentInsertType::Proforma));

        expect($proforma->type)->toBe(DocumentType::Proforma);

        $invoice = Billingo::documents()->createFromProforma((int) $proforma->id, new CreateFromProformaData(
            fulfillmentDate: CarbonImmutable::today(),
        ));

        expect($invoice->type)->toBe(DocumentType::Invoice)
            ->and($invoice->grossTotal)->toEqual(25400);
    } finally {
        sandboxCancel($invoice);
        sandboxDeletePartner($partner->id);
    }
});

it('issues, prints and cancels a receipt', function () {
    sandboxIssuingDocuments();
    $receipt = null;

    try {
        $receipt = Billingo::documents()->createReceipt(new CreateReceiptData(
            blockId: sandboxBlockId(DocumentBlockType::Receipt),
            type: DocumentType::Receipt,
            paymentMethod: PaymentMethod::Cash,
            currency: Currency::HUF,
            items: [
                new ReceiptCustomItemData(unitPrice: 1270, vat: ReceiptVat::Percent27, name: 'Integration test', quantity: 2, unit: 'db'),
            ],
        ));

        expect($receipt->type)->toBe(DocumentType::Receipt)
            ->and($receipt->grossTotal)->toEqual(2540)
            ->and(Billingo::documents()->posPrint((int) $receipt->id, size: 80))->not->toBeEmpty();
    } finally {
        sandboxCancel($receipt);
    }
});
