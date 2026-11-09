---
name: laravel-billingo-development
description: >
  Integrate the Billingo (billingo.hu) invoicing API v3 into Laravel applications with szabrob/laravel-billingo:
  create and send invoices and receipts, manage partners, products, bank accounts and spendings using laravel-data DTOs.
license: MIT
metadata:
  author: Szabó Róbert
---

# Laravel Billingo

Use this skill when a Laravel application needs to call the Billingo API v3 through the `szabrob/laravel-billingo` package.

## Primary Goal

- apply the package's `Billingo` facade, resource methods, Data objects and enums in the smallest correct way

## Workflow

### 1. Install and configure

- `composer require szabrob/laravel-billingo`
- set `BILLINGO_API_KEY` in `.env` (optional: `BILLINGO_BASE_URL`, `BILLINGO_TIMEOUT`, `BILLINGO_RATE_LIMIT_RETRIES`, `BILLINGO_RATE_LIMIT_MAX_DELAY`)
- publish config only when it must be customized: `php artisan vendor:publish --tag="laravel-billingo-config"` (creates `config/laravel-billingo.php`)

- verify the key: `php artisan billingo:check` (or `--key=...` for another key); exits non-zero on failure

### 2. Call a resource through the facade

- `Szabrob\LaravelBillingo\Facades\Billingo` (or inject `Szabrob\LaravelBillingo\Billingo`)
- resources: `bankAccounts()`, `currencies()`, `documentBlocks()`, `documentExports()`, `documents()`, `inventory()`, `organization()`, `partners()`, `products()`, `spendings()`, `utils()`
- per-account keys: `Billingo::withApiKey($key)->documents()->...` (does not change the default client)

### 3. Build requests with Data objects and enums

- request/response DTOs live in `Szabrob\LaravelBillingo\Data\{BankAccounts,Currencies,DocumentBlocks,DocumentExports,Documents,Inventory,Organization,Partners,Products,Shared,Spendings,Utils}`
- enums live in `Szabrob\LaravelBillingo\Enums` (e.g. `Currency::HUF`, `Vat::Percent27`, `PaymentMethod::WireTransfer`, `DocumentLanguage::Hu`, `Country::HU`)
- properties are camelCase; dates are `CarbonImmutable`; optional fields default to `null` and are omitted from the payload
- invoice items: `ProductItemData` (saved product by id) or `CustomItemData` (ad-hoc line); receipt items: `ReceiptProductItemData` / `ReceiptCustomItemData`

### 4. Handle lists and errors

- `list()` returns `Szabrob\LaravelBillingo\Support\PaginatedResponse` (`data`, `total`, `perPage`, `currentPage`, `lastPage`, `hasMorePages()`); `lazy()` iterates all pages
- document/spending filters: `DocumentFilterData`, `SpendingFilterData`
- HTTP 429 responses are retried automatically (default 2 retries, honoring `Retry-After` up to `rate_limit.max_delay` seconds); a `RateLimitException` is thrown only after that
- catch `Szabrob\LaravelBillingo\Exceptions\BillingoException`; subclasses: `ValidationException` (`errors()`), `RateLimitException` (`retryAfter()`), `DocumentNotReadyException` (PDF not generated yet), `ConnectionFailedException` (network error or timeout; `response` is `null`)

### 5. Common workflows

- look up `blockId` with `documentBlocks()->lazy(DocumentBlockType::Invoice)` and bank accounts with `bankAccounts()->lazy()`; store the IDs in config
- issue documents from queued jobs; store the returned Billingo partner/document IDs on the app's models so retries do not create duplicates, and pass the app's order identifier as `vendorId` (`findByVendorId()` looks it up)
- PDFs are generated asynchronously: on `DocumentNotReadyException` release the job and download later
- issued invoices cannot be edited: `cancel($id, CancelDocumentData)` (sztornó) or `createModification($id, CreateModificationDocumentData)` (módosító számla); only drafts can be `delete()`d
- proforma (díjbekérő): create with `DocumentInsertType::Proforma`, then `createFromProforma($id, CreateFromProformaData)`; drafts: `DocumentInsertType::Draft` + `createFromDraft()`
- receipts (nyugta): `createReceipt(CreateReceiptData)` with `DocumentType::Receipt`, no partner needed; `posPrint($id, 58|80)`
- payments: `updatePayments($id, [PaymentData])` replaces the payment history; overdue invoices: `DocumentFilterData(paymentStatus: PaymentStatus::Expired)`
- tax numbers: `utils()->checkTaxNumber()` (`result === TaxNumberCheckResult::ValidationOk`); NAV status: `documents()->onlineSzamlaStatus($id)`
- accounting exports: `documentExports()->create()` returns an ID, `poll($id)->state === DocumentExportState::Success`, then `download($id)`

## Rules, References, and Templates

- no additional resource files for this skill
- the package README documents every resource method

## Examples

Create and send an invoice:

```php
use Carbon\CarbonImmutable;
use Szabrob\LaravelBillingo\Data\Documents\CreateDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CustomItemData;
use Szabrob\LaravelBillingo\Enums\{Currency, DocumentInsertType, DocumentLanguage, PaymentMethod, UnitPriceType, Vat};
use Szabrob\LaravelBillingo\Facades\Billingo;

$invoice = Billingo::documents()->create(new CreateDocumentData(
    partnerId: $partnerId,
    blockId: $blockId,
    type: DocumentInsertType::Invoice,
    fulfillmentDate: CarbonImmutable::today(),
    dueDate: CarbonImmutable::today()->addDays(8),
    paymentMethod: PaymentMethod::WireTransfer,
    language: DocumentLanguage::Hu,
    currency: Currency::HUF,
    items: [new CustomItemData(name: 'Consulting', unitPrice: 10000, unitPriceType: UnitPriceType::Net, quantity: 1, unit: 'hour', vat: Vat::Percent27)],
));

Billingo::documents()->send($invoice->id);
```

Release a queued job when rate limiting persists after the automatic retries:

```php
try {
    Billingo::partners()->create($partner);
} catch (\Szabrob\LaravelBillingo\Exceptions\RateLimitException $e) {
    $this->release($e->retryAfter() ?? 60);
}
```

Test application code without hitting Billingo:

```php
Billingo::fake([
    'POST documents' => new DocumentData(id: 1001, invoiceNumber: 'INV-1'), // keys: "[METHOD ]path", `*` wildcard, first match wins
    'documents/*/download' => '%PDF-fake',                                  // also arrays, Http::response(), Http::sequence(), closures
]);
// ... run code ...
Billingo::assertSent('POST documents', fn (Request $request) => $request['partner_id'] === 42);
Billingo::assertNotSent('documents/*/cancel');
Billingo::assertSentCount(1); // also assertNothingSent(), recorded()
```

Unmatched Billingo requests get an empty `200` response; no API key is needed; query strings are ignored when matching.

## Anti-patterns

- do not call the Billingo API with raw `Http::` requests when a resource method exists
- do not pass snake_case arrays to resource methods; construct the Data objects instead
- do not read `env('BILLINGO_API_KEY')` in application code; use config or `withApiKey()`
- do not fake Billingo with raw `Http::fake()` URL patterns; use `Billingo::fake()`
- do not instantiate `Connector` or resource classes directly; go through the facade or the `Billingo` class
