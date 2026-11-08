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
- catch `Szabrob\LaravelBillingo\Exceptions\BillingoException`; subclasses: `ValidationException` (`errors()`), `RateLimitException` (`retryAfter()`), `DocumentNotReadyException` (PDF not generated yet)

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
Http::fake(['api.billingo.hu/v3/*' => Http::response(['id' => 1001, 'invoice_number' => 'INV-1'], 201)]);
// ... run code ...
Http::assertSent(fn ($request) => $request->url() === 'https://api.billingo.hu/v3/documents');
```

## Anti-patterns

- do not call the Billingo API with raw `Http::` requests when a resource method exists
- do not pass snake_case arrays to resource methods; construct the Data objects instead
- do not read `env('BILLINGO_API_KEY')` in application code; use config or `withApiKey()`
- do not instantiate `Connector` or resource classes directly; go through the facade or the `Billingo` class
