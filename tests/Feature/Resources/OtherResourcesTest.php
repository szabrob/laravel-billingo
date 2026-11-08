<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Data\DocumentBlocks\CreateDocumentBlockData;
use Szabrob\LaravelBillingo\Data\DocumentExports\CreateDocumentExportData;
use Szabrob\LaravelBillingo\Data\Spendings\SaveSpendingData;
use Szabrob\LaravelBillingo\Data\Spendings\SpendingFilterData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentBlockType;
use Szabrob\LaravelBillingo\Enums\DocumentExportQueryType;
use Szabrob\LaravelBillingo\Enums\DocumentExportState;
use Szabrob\LaravelBillingo\Enums\DocumentExportType;
use Szabrob\LaravelBillingo\Enums\Feature;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\SpendingCategory;
use Szabrob\LaravelBillingo\Enums\SpendingPaymentStatus;
use Szabrob\LaravelBillingo\Enums\TaxNumberCheckResult;
use Szabrob\LaravelBillingo\Facades\Billingo;

beforeEach(fn () => Http::preventStrayRequests());

it('reads currency conversion rates', function () {
    Http::fake(['*' => Http::response(['from_currency' => 'EUR', 'to_currency' => 'HUF', 'conversation_rate' => 395.5, 'date' => '2026-11-08'])]);

    $rate = Billingo::currencies()->conversionRate(Currency::EUR, Currency::HUF, CarbonImmutable::parse('2026-11-08'));

    assertBillingoRequest('GET', 'currencies?from=EUR&to=HUF&date=2026-11-08');
    expect($rate->conversationRate)->toBe(395.5)
        ->and($rate->fromCurrency)->toBe(Currency::EUR);
});

it('lists and creates document blocks', function () {
    Http::fake([
        'api.billingo.hu/v3/document-blocks?*' => Http::response(['data' => [['id' => 7, 'name' => 'Számlák', 'prefix' => 'INV', 'type' => 'invoice']], 'total' => 1, 'per_page' => 25, 'current_page' => 1, 'last_page' => 1]),
        'api.billingo.hu/v3/document-blocks' => Http::response(null, 201),
    ]);

    $blocks = Billingo::documentBlocks()->list(type: DocumentBlockType::Invoice);
    Billingo::documentBlocks()->create(new CreateDocumentBlockData(name: 'Új tömb', prefix: 'NEW', type: DocumentBlockType::Invoice));

    assertBillingoRequest('GET', 'document-blocks?page=1&per_page=25&type=invoice');
    assertBillingoRequest('POST', 'document-blocks', ['name' => 'Új tömb', 'prefix' => 'NEW', 'type' => 'invoice']);
    expect($blocks->data->first()?->prefix)->toBe('INV')
        ->and(Billingo::documentBlocks()->lazy(DocumentBlockType::Invoice)->count())->toBe(1);
});

it('creates, polls and downloads document exports', function () {
    Http::fake([
        '*/document-export' => Http::response(['id' => 'exp-1'], 201),
        '*/document-export/exp-1/poll' => Http::response(['id' => 'exp-1', 'state' => 'success', 'message' => '']),
        '*/document-export/exp-1/download' => Http::response('csv-content'),
    ]);

    $id = Billingo::documentExports()->create(new CreateDocumentExportData(
        queryType: DocumentExportQueryType::InvoiceDate,
        startDate: CarbonImmutable::parse('2026-10-01'),
        endDate: CarbonImmutable::parse('2026-10-31'),
        exportType: DocumentExportType::SimpleCsv,
    ));

    expect($id)->toBe('exp-1')
        ->and(Billingo::documentExports()->poll($id)->state)->toBe(DocumentExportState::Success)
        ->and(Billingo::documentExports()->download($id))->toBe('csv-content');
    assertBillingoRequest('POST', 'document-export', [
        'query_type' => 'invoice_date',
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-31',
        'export_type' => 'simple_csv',
    ]);
});

it('reads product inventory and organization data', function () {
    Http::fake([
        '*/inventory/product/9/quantity' => Http::response(['available_quantity' => 12.5]),
        '*/organization' => Http::response(['tax_code' => '87654321-1-41', 'subscription' => ['expiration_date' => '2027-01-01', 'features' => ['subscription_pro', 'inventory']], 'has_nav_connection' => true]),
    ]);

    $organization = Billingo::organization()->get();

    expect(Billingo::inventory()->productQuantity(9)->availableQuantity)->toBe(12.5)
        ->and($organization->taxCode)->toBe('87654321-1-41')
        ->and($organization->subscription?->features)->toBe([Feature::SubscriptionPro, Feature::Inventory])
        ->and($organization->subscription?->expirationDate?->toDateString())->toBe('2027-01-01');
});

it('manages spendings', function () {
    $spending = ['id' => 4, 'category' => 'software', 'currency' => 'HUF', 'total_gross' => 12700, 'fulfillment_date' => '2026-11-01', 'payment_method' => 'bankcard', 'partner' => ['id' => 1, 'name' => 'Szállító Kft.'], 'items' => []];
    Http::fake([
        'api.billingo.hu/v3/spendings?*' => Http::response(['data' => [$spending], 'total' => 1, 'per_page' => 25, 'current_page' => 1, 'last_page' => 1]),
        'api.billingo.hu/v3/spendings' => Http::response($spending, 201),
        'api.billingo.hu/v3/spendings/4' => Http::sequence()->push($spending)->push($spending)->push(null, 204),
    ]);
    $save = new SaveSpendingData(
        currency: Currency::HUF,
        totalGross: 12700,
        totalGrossHuf: 12700,
        totalVatAmount: 2700,
        totalVatAmountHuf: 2700,
        fulfillmentDate: CarbonImmutable::parse('2026-11-01'),
        category: SpendingCategory::Software,
        paymentMethod: PaymentMethod::Bankcard,
    );

    $page = Billingo::spendings()->list(new SpendingFilterData(query: 'Szállító', paymentStatus: SpendingPaymentStatus::Paid, category: SpendingCategory::Software));

    expect($page->data->first()?->partner?->name)->toBe('Szállító Kft.')
        ->and(Billingo::spendings()->create($save)->category)->toBe(SpendingCategory::Software)
        ->and(Billingo::spendings()->get(4)->id)->toBe(4)
        ->and(Billingo::spendings()->update(4, $save)->totalGross)->toBe(12700.0)
        ->and(Billingo::spendings()->lazy()->count())->toBe(1);
    Billingo::spendings()->delete(4);

    assertBillingoRequest('GET', 'spendings?q=Sz%C3%A1ll%C3%ADt%C3%B3&payment_status=paid&categories=software&page=1&per_page=25');
    assertBillingoRequest('POST', 'spendings', [
        'currency' => 'HUF',
        'total_gross' => 12700.0,
        'total_gross_huf' => 12700.0,
        'total_vat_amount' => 2700.0,
        'total_vat_amount_huf' => 2700.0,
        'fulfillment_date' => '2026-11-01',
        'category' => 'software',
        'payment_method' => 'bankcard',
    ]);
    assertBillingoRequest('DELETE', 'spendings/4');
});

it('calls the utility endpoints', function () {
    Http::fake([
        '*/utils/check-tax-number/*' => Http::response(['tax_number' => '12345678-2-42', 'result' => 'validation_ok', 'taxpayer_name' => 'TESZT KFT', 'addresses' => [['type' => 'HQ', 'country_code' => 'HU', 'postal_code' => '1111', 'city' => 'Budapest', 'street_name' => 'Fő', 'public_place_category' => 'utca', 'number' => '1']]]),
        '*/utils/convert-legacy-id/*' => Http::response(['id' => 1001, 'legacy_id' => 55]),
        '*/utils/time' => Http::response(['epoch' => 1794134400, 'formatted' => '2026-11-08 00:00:00', 'w3c' => '2026-11-08T00:00:00+01:00', 'timezone' => 'Europe/Budapest']),
    ]);

    $check = Billingo::utils()->checkTaxNumber('12345678-2-42');

    expect($check->result)->toBe(TaxNumberCheckResult::ValidationOk)
        ->and($check->addresses[0]->city ?? null)->toBe('Budapest')
        ->and(Billingo::utils()->convertLegacyId(55)->id)->toBe(1001)
        ->and(Billingo::utils()->serverTime()->timezone)->toBe('Europe/Budapest');
    assertBillingoRequest('GET', 'utils/check-tax-number/12345678-2-42');
});
