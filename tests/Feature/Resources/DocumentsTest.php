<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Data\Documents\CancelDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateFromProformaData;
use Szabrob\LaravelBillingo\Data\Documents\CreateModificationDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateReceiptData;
use Szabrob\LaravelBillingo\Data\Documents\CustomItemData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentData;
use Szabrob\LaravelBillingo\Data\Documents\DocumentFilterData;
use Szabrob\LaravelBillingo\Data\Documents\PaymentData;
use Szabrob\LaravelBillingo\Data\Documents\ProductItemData;
use Szabrob\LaravelBillingo\Data\Documents\ReceiptCustomItemData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentInsertType;
use Szabrob\LaravelBillingo\Enums\DocumentLanguage;
use Szabrob\LaravelBillingo\Enums\DocumentType;
use Szabrob\LaravelBillingo\Enums\OnlineSzamlaStatus;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\PaymentStatus;
use Szabrob\LaravelBillingo\Enums\ReceiptVat;
use Szabrob\LaravelBillingo\Enums\UnitPriceType;
use Szabrob\LaravelBillingo\Enums\Vat;
use Szabrob\LaravelBillingo\Exceptions\DocumentNotReadyException;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => Http::preventStrayRequests());

function newInvoice(): CreateDocumentData
{
    return new CreateDocumentData(
        partnerId: 42,
        blockId: 7,
        type: DocumentInsertType::Invoice,
        fulfillmentDate: CarbonImmutable::parse('2026-11-08'),
        dueDate: CarbonImmutable::parse('2026-11-16'),
        paymentMethod: PaymentMethod::WireTransfer,
        language: DocumentLanguage::Hu,
        currency: Currency::HUF,
        electronic: true,
        items: [
            new ProductItemData(productId: 9, quantity: 2),
            new CustomItemData(name: 'Kiszállás', unitPrice: 5000, unitPriceType: UnitPriceType::Net, quantity: 1, unit: 'alkalom', vat: Vat::Percent27),
        ],
    );
}

it('creates an invoice with mixed items', function () {
    Http::fake(['*' => Http::response(billingoFixture('document'), 201)]);

    $document = Billingo::documents()->create(newInvoice());

    assertBillingoRequest('POST', 'documents', [
        'partner_id' => 42,
        'block_id' => 7,
        'type' => 'invoice',
        'fulfillment_date' => '2026-11-08',
        'due_date' => '2026-11-16',
        'payment_method' => 'wire_transfer',
        'language' => 'hu',
        'currency' => 'HUF',
        'electronic' => true,
        'items' => [
            ['product_id' => 9, 'quantity' => 2.0],
            ['name' => 'Kiszállás', 'unit_price' => 5000.0, 'unit_price_type' => 'net', 'quantity' => 1.0, 'unit' => 'alkalom', 'vat' => '27%'],
        ],
    ]);
    expect($document)->toBeInstanceOf(DocumentData::class)
        ->and($document->id)->toBe(1001);
});

it('maps a document response', function () {
    Http::fake(['*' => Http::response(billingoFixture('document'))]);

    $document = Billingo::documents()->get(1001);

    assertBillingoRequest('GET', 'documents/1001');
    expect($document->invoiceNumber)->toBe('INV-2026-1')
        ->and($document->type)->toBe(DocumentType::Invoice)
        ->and($document->paymentStatus)->toBe(PaymentStatus::Outstanding)
        ->and($document->grossTotal)->toBe(12700.0)
        ->and($document->invoiceDate?->toDateString())->toBe('2026-11-08')
        ->and($document->paidDate)->toBeNull()
        ->and($document->organization?->bankAccount?->name)->toBe('OTP')
        ->and($document->partner?->name)->toBe('Teszt Kft.')
        ->and($document->items)->toHaveCount(1)
        ->and($document->items[0]->vat ?? null)->toBe(Vat::Percent27)
        ->and($document->summary?->vatRateSummary[0]->vatRateVatAmount ?? null)->toBe(2700.0)
        ->and($document->onlineSzamlaStatus)->toBe(OnlineSzamlaStatus::Received);
});

it('lists documents with filters', function () {
    Http::fake(['*' => Http::response(['data' => [billingoFixture('document')], 'total' => 1, 'per_page' => 25, 'current_page' => 1, 'last_page' => 1])]);

    $page = Billingo::documents()->list(new DocumentFilterData(
        partnerId: 42,
        paymentStatus: PaymentStatus::Outstanding,
        startDate: CarbonImmutable::parse('2026-01-01'),
        type: DocumentType::Invoice,
    ));

    assertBillingoRequest('GET', 'documents?partner_id=42&payment_status=outstanding&start_date=2026-01-01&type=invoice&page=1&per_page=25');
    expect($page->data->first())->toBeInstanceOf(DocumentData::class)
        ->and(Billingo::documents()->lazy()->count())->toBe(1);
});

it('finds a document by vendor id', function () {
    Http::fake(['*' => Http::response(billingoFixture('document'))]);

    Billingo::documents()->findByVendorId('order 15/A');

    assertBillingoRequest('GET', 'documents/vendor/order%2015%2FA');
});

it('creates documents from drafts, proformas and modifications', function () {
    Http::fake(['*' => Http::response(billingoFixture('document'))]);

    Billingo::documents()->createFromDraft(5, newInvoice());
    Billingo::documents()->createFromProforma(6, new CreateFromProformaData(documentType: DocumentType::Invoice));
    Billingo::documents()->createModification(7, new CreateModificationDocumentData(comment: 'Javítás'));
    Billingo::documents()->copy(8);
    Billingo::documents()->cancel(9, new CancelDocumentData(cancellationReason: 'Hibás tétel'));

    assertBillingoRequest('PUT', 'documents/5');
    assertBillingoRequest('POST', 'documents/6/create-from-proforma', ['document_type' => 'invoice']);
    assertBillingoRequest('POST', 'documents/7/create-modification-document', ['comment' => 'Javítás']);
    assertBillingoRequest('POST', 'documents/8/copy');
    assertBillingoRequest('POST', 'documents/9/cancel', ['cancellation_reason' => 'Hibás tétel']);
});

it('creates receipts', function () {
    Http::fake(['*' => Http::response(billingoFixture('document'), 201)]);
    $receipt = new CreateReceiptData(
        blockId: 3,
        type: DocumentType::Receipt,
        paymentMethod: PaymentMethod::Cash,
        currency: Currency::HUF,
        name: 'Vevő',
        items: [new ReceiptCustomItemData(unitPrice: 1270, vat: ReceiptVat::Percent27, name: 'Kávé')],
    );

    Billingo::documents()->createReceipt($receipt);
    Billingo::documents()->createReceiptFromDraft(11, $receipt);

    assertBillingoRequest('POST', 'documents/receipt', [
        'block_id' => 3,
        'type' => 'receipt',
        'payment_method' => 'cash',
        'currency' => 'HUF',
        'name' => 'Vevő',
        'electronic' => false,
        'items' => [['unit_price' => 1270.0, 'vat' => '27%', 'name' => 'Kávé']],
    ]);
    assertBillingoRequest('PUT', 'documents/receipt/11');
});

it('deletes and archives documents', function () {
    Http::fake(['*' => Http::response(null, 204)]);

    Billingo::documents()->delete(5);
    Billingo::documents()->archive(6);

    assertBillingoRequest('DELETE', 'documents/5');
    assertBillingoRequest('PUT', 'documents/6/archive');
});

it('sends a document to the given addresses', function () {
    Http::fake(['*' => Http::response(['emails' => ['a@example.test', 'b@example.test']])]);

    $sent = Billingo::documents()->send(5, ['a@example.test', 'b@example.test']);

    assertBillingoRequest('POST', 'documents/5/send', ['emails' => ['a@example.test', 'b@example.test']]);
    expect($sent)->toBe(['a@example.test', 'b@example.test']);
});

it('sends a document to the partner when no address is given', function () {
    Http::fake(['*' => Http::response(['emails' => ['szamla@teszt.hu']])]);

    expect(Billingo::documents()->send(5))->toBe(['szamla@teszt.hu']);
    assertBillingoRequest('POST', 'documents/5/send', []);
});

it('downloads the document pdf', function () {
    Http::fake(['*' => Http::response('%PDF-1.7', 200, ['Content-Type' => 'application/pdf'])]);

    expect(Billingo::documents()->download(5))->toBe('%PDF-1.7')
        ->and(Billingo::documents()->posPrint(5, 58))->toBe('%PDF-1.7');
    assertBillingoRequest('GET', 'documents/5/download');
    assertBillingoRequest('GET', 'documents/5/print/pos?size=58');
});

it('throws when the pdf is not generated yet', function () {
    Http::fake(['*' => Http::response(['message' => 'Document PDF has not generated yet.'], 202)]);

    Billingo::documents()->download(5);
})->throws(DocumentNotReadyException::class, 'The PDF of Billingo document 5 has not been generated yet.');

it('rejects unsupported pos print sizes', function () {
    Billingo::documents()->posPrint(5, 100);
})->throws(InvalidArgumentException::class);

it('reads the public url, nav status and reminders', function () {
    Http::fake([
        '*/public-url' => Http::response(['public_url' => 'https://www.billingo.hu/access/abc']),
        '*/online-szamla' => Http::response(['transaction_id' => 'T1', 'status' => 'DONE', 'messages' => [['validation_result_code' => 'WARN', 'validation_error_code' => 'X', 'human_readable_message' => 'Figyelmeztetés']]]),
        '*/reminders' => Http::response(['sent_reminders' => [['type' => 'document_reminder', 'send_at' => '2026-11-01', 'recipients' => ['a@example.test'], 'status' => ['status' => 'opened', 'date' => '2026-11-01 10:00:00']]], 'upcoming_reminders' => [], 'sent_postal_mail' => []]),
    ]);

    $status = Billingo::documents()->onlineSzamlaStatus(5);
    $reminders = Billingo::documents()->reminders(5);

    expect(Billingo::documents()->publicUrl(5))->toBe('https://www.billingo.hu/access/abc')
        ->and($status->transactionId)->toBe('T1')
        ->and($status->messages[0]->humanReadableMessage ?? null)->toBe('Figyelmeztetés')
        ->and($reminders->sentReminders[0]->status?->date?->format('H:i') ?? null)->toBe('10:00');
});

it('manages document payments', function () {
    $payment = ['date' => '2026-11-10', 'price' => 12700, 'payment_method' => 'wire_transfer', 'voucher_number' => null, 'conversion_rate' => 1];
    Http::fake(['*' => Http::sequence()
        ->push([$payment])
        ->push([$payment])
        ->push([]),
    ]);

    $payments = Billingo::documents()->payments(5);
    $updated = Billingo::documents()->updatePayments(5, [
        new PaymentData(date: CarbonImmutable::parse('2026-11-10'), price: 12700, paymentMethod: PaymentMethod::WireTransfer),
    ]);
    $deleted = Billingo::documents()->deletePayments(5);

    expect($payments->first()?->price)->toBe(12700.0)
        ->and($updated)->toHaveCount(1)
        ->and($deleted)->toBeEmpty();
    assertBillingoRequest('PUT', 'documents/5/payments', [['date' => '2026-11-10', 'price' => 12700.0, 'payment_method' => 'wire_transfer']]);
    assertBillingoRequest('DELETE', 'documents/5/payments');
});
