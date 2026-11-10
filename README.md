<div align="center">
    <h1>Laravel Billingo</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/szabrob/laravel-billingo"><img src="https://img.shields.io/packagist/v/szabrob/laravel-billingo.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-billingo"><img src="https://img.shields.io/packagist/php-v/szabrob/laravel-billingo.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-billingo"><img src="https://badge.laravel.cloud/badge/szabrob/laravel-billingo?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/szabrob/laravel-billingo/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/szabrob/laravel-billingo/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/szabrob/laravel-billingo"><img src="https://img.shields.io/packagist/dt/szabrob/laravel-billingo.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Laravel integration for the Billingo API - create, send and manage invoices, partners and products.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

You can install the package via Composer:

```bash
composer require szabrob/laravel-billingo
```

Add your [Billingo API v3 key](https://app.billingo.hu/api-key) to your `.env` file:

```dotenv
BILLINGO_API_KEY=your-api-key
```

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-billingo-config"
```

| Key | Env variable | Default |
| --- | --- | --- |
| `api_key` | `BILLINGO_API_KEY` | `null` |
| `base_url` | `BILLINGO_BASE_URL` | `https://api.billingo.hu/v3` |
| `timeout` | `BILLINGO_TIMEOUT` | `30` (seconds) |
| `rate_limit.retries` | `BILLINGO_RATE_LIMIT_RETRIES` | `2` |
| `rate_limit.max_delay` | `BILLINGO_RATE_LIMIT_MAX_DELAY` | `10` (seconds) |

### Checking the connection

Verify that the API key works and see the account details:

```bash
php artisan billingo:check

# Check another API key without changing the configuration
php artisan billingo:check --key=another-api-key
```

The command prints the organization's tax code, subscription expiry, enabled features and NAV connection status, and exits with a non-zero code when the request fails.

## Usage

Every Billingo API v3 resource is available through the `Billingo` facade (or by injecting `Szabrob\LaravelBillingo\Billingo`). Requests and responses use [`spatie/laravel-data`](https://spatie.be/docs/laravel-data) objects with camelCase properties, PHP enums and `CarbonImmutable` dates.

### Creating an invoice

```php
use Carbon\CarbonImmutable;
use Szabrob\LaravelBillingo\Data\Documents\CreateDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CustomItemData;
use Szabrob\LaravelBillingo\Data\Documents\ProductItemData;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentInsertType;
use Szabrob\LaravelBillingo\Enums\DocumentLanguage;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\UnitPriceType;
use Szabrob\LaravelBillingo\Enums\Vat;
use Szabrob\LaravelBillingo\Facades\Billingo;

$invoice = Billingo::documents()->create(new CreateDocumentData(
    partnerId: 42,
    blockId: 7,
    type: DocumentInsertType::Invoice,
    fulfillmentDate: CarbonImmutable::today(),
    dueDate: CarbonImmutable::today()->addDays(8),
    paymentMethod: PaymentMethod::WireTransfer,
    language: DocumentLanguage::Hu,
    currency: Currency::HUF,
    electronic: true,
    items: [
        // A product saved in Billingo
        new ProductItemData(productId: 9, quantity: 2),
        // An ad-hoc line item
        new CustomItemData(
            name: 'Consulting',
            unitPrice: 10000,
            unitPriceType: UnitPriceType::Net,
            quantity: 1,
            unit: 'hour',
            vat: Vat::Percent27,
        ),
    ],
));

$invoice->id;            // 1001
$invoice->invoiceNumber; // "INV-2026-1"
$invoice->grossTotal;    // 12700.0

Billingo::documents()->send($invoice->id);              // to the partner's e-mail addresses
$pdf = Billingo::documents()->download($invoice->id);   // raw PDF contents
```

Optional fields default to `null` and are left out of the request payload. See [Common workflows](#common-workflows) for queued invoicing, cancellations, receipts, payments and more.

### Partners and products

```php
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\Country;
use Szabrob\LaravelBillingo\Enums\PartnerTaxType;

$partner = Billingo::partners()->create(new PartnerData(
    name: 'Example Ltd.',
    address: new AddressData(countryCode: Country::HU, postCode: '1111', city: 'Budapest', address: 'Fő utca 1.'),
    emails: ['billing@example.com'],
    taxcode: '12345678-2-42',
    taxType: PartnerTaxType::HasTaxNumber,
));

Billingo::partners()->get($partner->id);
Billingo::partners()->update($partner->id, $partner);
Billingo::partners()->delete($partner->id);
```

### Listing and pagination

List methods return a single page as `Szabrob\LaravelBillingo\Support\PaginatedResponse`. Use `lazy()` to iterate over every page as a `LazyCollection`:

```php
use Szabrob\LaravelBillingo\Data\Documents\DocumentFilterData;
use Szabrob\LaravelBillingo\Enums\PaymentStatus;

$page = Billingo::partners()->list(page: 1, perPage: 50, query: 'Example');

$page->data;            // Collection<int, PartnerData>
$page->total;
$page->hasMorePages();

Billingo::documents()
    ->lazy(new DocumentFilterData(paymentStatus: PaymentStatus::Outstanding))
    ->each(fn ($document) => /* ... */);
```

### Available resources

| Resource | Methods |
| --- | --- |
| `bankAccounts()` | `list`, `lazy`, `get`, `create`, `update`, `delete` |
| `currencies()` | `conversionRate` |
| `documentBlocks()` | `list`, `lazy`, `create` |
| `documentExports()` | `create`, `poll`, `download` |
| `documents()` | `list`, `lazy`, `get`, `findByVendorId`, `create`, `createFromDraft`, `createReceipt`, `createReceiptFromDraft`, `createFromProforma`, `createModification`, `cancel`, `copy`, `delete`, `archive`, `send`, `download`, `posPrint`, `publicUrl`, `onlineSzamlaStatus`, `reminders`, `payments`, `updatePayments`, `deletePayments` |
| `inventory()` | `productQuantity` |
| `organization()` | `get` |
| `partners()` | `list`, `lazy`, `get`, `create`, `update`, `delete`, `guess` |
| `products()` | `list`, `lazy`, `get`, `create`, `update`, `delete` |
| `spendings()` | `list`, `lazy`, `get`, `create`, `update`, `delete` |
| `utils()` | `checkTaxNumber`, `convertLegacyId`, `serverTime` |

### Using another API key

To work with multiple Billingo accounts, switch the key at runtime. The configured default client is not modified:

```php
Billingo::withApiKey($company->billingo_api_key)->documents()->list();
```

### Error handling

Failed requests, including network errors, throw a `Szabrob\LaravelBillingo\Exceptions\BillingoException` (the HTTP response is available as `$exception->response`). More specific subclasses are thrown for some cases:

| Exception | When |
| --- | --- |
| `ValidationException` | HTTP 422; `errors()` returns the messages keyed by field |
| `RateLimitException` | HTTP 429; `retryAfter()` returns the seconds to wait |
| `DocumentNotReadyException` | The document PDF is not generated yet (HTTP 202 on download) |
| `ConnectionFailedException` | Billingo could not be reached (DNS failure, connection refused, timeout); `$exception->response` is `null` and the original `ConnectionException` is available via `getPrevious()` |

```php
use Szabrob\LaravelBillingo\Exceptions\RateLimitException;
use Szabrob\LaravelBillingo\Exceptions\ValidationException;

try {
    Billingo::partners()->create($partner);
} catch (ValidationException $exception) {
    $exception->errors(); // ['name' => ['The name field is required.']]
} catch (RateLimitException $exception) {
    $this->release($exception->retryAfter() ?? 60);
}
```

### Rate limiting

When Billingo responds with `429 Too Many Requests`, the request is retried automatically (twice by default) after waiting the number of seconds sent in the `Retry-After` header. If Billingo asks to wait longer than `rate_limit.max_delay` seconds, the request is not retried, so web requests are not blocked for long; a `RateLimitException` is thrown instead. Set `BILLINGO_RATE_LIMIT_RETRIES=0` to disable retries. Other failed responses are never retried.

### Testing

Call `Billingo::fake()` in your tests to answer Billingo API requests with fake responses and assert what was sent. Responses are keyed by the endpoint relative to the base URL, optionally prefixed with the HTTP method; `*` is a wildcard and the first matching key wins:

```php
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Data\Documents\DocumentData;
use Szabrob\LaravelBillingo\Facades\Billingo;

Billingo::fake([
    'POST documents' => new DocumentData(id: 1001, invoiceNumber: 'INV-2026-1'),
    'documents/*/download' => '%PDF-1.4 ...',
    'partners/*' => ['id' => 42, 'name' => 'Example Ltd.'],
    'POST partners' => Http::response(['message' => 'The given data was invalid.'], 422),
    'GET documents/*' => Http::sequence()->push(['id' => 1001])->push(['id' => 1002]),
    'products/*' => fn (Request $request) => ['id' => (int) basename($request->url())],
]);

// ... run your application code ...

Billingo::assertSent('POST documents', fn (Request $request) => $request['partner_id'] === 42);
Billingo::assertSent('documents/*/send');
Billingo::assertNotSent('DELETE partners/*');
Billingo::assertSentCount(2);
Billingo::assertNothingSent();
```

- Responses can be Data objects, arrays, strings (raw bodies such as PDFs), `Http::response()`, `Http::sequence()` or closures receiving the request.
- Unmatched Billingo requests get an empty `200` response, so you only need to fake the responses your code reads.
- The query string is ignored when matching, so `'documents'` matches every page of `documents()->list()` and `lazy()`.
- The real resources and Laravel's HTTP client are still used, so payload serialization and error handling behave as in production. No API key is needed, and only requests to the configured `base_url` are faked; `Http::preventStrayRequests()` keeps working for other hosts.
- Calling `Billingo::fake()` again adds responses that take priority over the earlier ones.

## Common workflows

### Finding the document block and bank account IDs

Every document is issued in a document block (invoice pad), and wire transfer invoices usually reference one of your bank accounts. Look up their IDs once and store them in your configuration:

```php
use Szabrob\LaravelBillingo\Enums\DocumentBlockType;

Billingo::documentBlocks()->lazy(DocumentBlockType::Invoice)
    ->each(fn ($block) => dump($block->id, $block->name));

Billingo::bankAccounts()->lazy()
    ->each(fn ($account) => dump($account->id, $account->name, $account->accountNumber));
```

### Invoicing a webshop order from a queued job

Billingo calls are network calls, so issue documents from a queued job. Store the Billingo IDs on your own models, so a retried job does not create a second partner or invoice, and pass your own order identifier as `vendorId` so the invoice can be looked up with `findByVendorId()` later:

```php
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Szabrob\LaravelBillingo\Data\Documents\CreateDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CustomItemData;
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\Country;
use Szabrob\LaravelBillingo\Enums\Currency;
use Szabrob\LaravelBillingo\Enums\DocumentInsertType;
use Szabrob\LaravelBillingo\Enums\DocumentLanguage;
use Szabrob\LaravelBillingo\Enums\PartnerTaxType;
use Szabrob\LaravelBillingo\Enums\PaymentMethod;
use Szabrob\LaravelBillingo\Enums\UnitPriceType;
use Szabrob\LaravelBillingo\Enums\Vat;
use Szabrob\LaravelBillingo\Exceptions\DocumentNotReadyException;
use Szabrob\LaravelBillingo\Exceptions\RateLimitException;
use Szabrob\LaravelBillingo\Facades\Billingo;

class IssueInvoice implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $customer = $this->order->customer;

        try {
            // 1. Create the partner once and remember its ID
            $customer->billingo_partner_id ??= Billingo::partners()->create(new PartnerData(
                name: $customer->company_name ?? $customer->name,
                address: new AddressData(
                    countryCode: Country::HU,
                    postCode: $customer->post_code,
                    city: $customer->city,
                    address: $customer->street,
                ),
                emails: [$customer->email],
                taxcode: $customer->tax_number,
                taxType: $customer->tax_number ? PartnerTaxType::HasTaxNumber : PartnerTaxType::NoTaxNumber,
            ))->id;
            $customer->save();

            // 2. Issue the invoice once and remember its ID
            if ($this->order->billingo_document_id === null) {
                $invoice = Billingo::documents()->create(new CreateDocumentData(
                    partnerId: $customer->billingo_partner_id,
                    blockId: config('services.billingo.block_id'),
                    type: DocumentInsertType::Invoice,
                    fulfillmentDate: CarbonImmutable::today(),
                    dueDate: CarbonImmutable::today(),
                    paymentMethod: PaymentMethod::Bankcard,
                    language: DocumentLanguage::Hu,
                    currency: Currency::HUF,
                    vendorId: 'order-'.$this->order->id,
                    electronic: true,
                    paid: true,
                    items: $this->order->lines->map(fn ($line) => new CustomItemData(
                        name: $line->name,
                        unitPrice: $line->gross_unit_price,
                        unitPriceType: UnitPriceType::Gross,
                        quantity: $line->quantity,
                        unit: 'db',
                        vat: Vat::Percent27,
                    ))->all(),
                ));

                $this->order->update([
                    'billingo_document_id' => $invoice->id,
                    'invoice_number' => $invoice->invoiceNumber,
                ]);

                // 3. E-mail it to the partner's addresses
                Billingo::documents()->send($invoice->id);
            }

            // 4. Keep a copy of the PDF
            Storage::put(
                "invoices/{$this->order->invoice_number}.pdf",
                Billingo::documents()->download($this->order->billingo_document_id),
            );
        } catch (DocumentNotReadyException) {
            $this->release(10); // the PDF is generated asynchronously, try again shortly
        } catch (RateLimitException $exception) {
            $this->release($exception->retryAfter() ?? 60);
        }
    }
}
```

`ValidationException` and other `BillingoException`s are not caught here, so the job fails and the error is reported. Billingo e-mails a public link to the invoice as well; use `Billingo::documents()->publicUrl($id)` to show the same link in your application.

### Cancelling and correcting invoices

Issued invoices cannot be edited or deleted (only drafts can be deleted). Cancel them with a cancellation invoice (*sztornó*), or issue a modification invoice (*módosító számla*) that refers to the original:

```php
use Szabrob\LaravelBillingo\Data\Documents\CancelDocumentData;
use Szabrob\LaravelBillingo\Data\Documents\CreateModificationDocumentData;

// Cancellation
$cancellation = Billingo::documents()->cancel($invoice->id, new CancelDocumentData(
    cancellationReason: 'The order was cancelled.',
    cancellationRecipients: 'billing@example.com',
));

// Modification: the items describe the correction, e.g. a negative quantity for a returned item
$modification = Billingo::documents()->createModification($invoice->id, new CreateModificationDocumentData(
    comment: 'One item was returned.',
    items: [
        new CustomItemData(name: 'Consulting', unitPrice: 10000, unitPriceType: UnitPriceType::Net, quantity: -1, unit: 'hour', vat: Vat::Percent27),
    ],
));
```

### Proforma invoices

Issue a proforma (*díjbekérő*) and turn it into an invoice after the customer paid:

```php
use Szabrob\LaravelBillingo\Data\Documents\CreateFromProformaData;

$proforma = Billingo::documents()->create(new CreateDocumentData(
    type: DocumentInsertType::Proforma,
    // ... the same fields as for an invoice
));

$invoice = Billingo::documents()->createFromProforma($proforma->id, new CreateFromProformaData(
    fulfillmentDate: CarbonImmutable::today(),
));
```

Use `DocumentInsertType::Draft` to save a draft in Billingo and `createFromDraft($draftId, $document)` to issue it later.

### Receipts

Receipts (*nyugta*) need no partner. The name and e-mail addresses of the buyer are optional:

```php
use Szabrob\LaravelBillingo\Data\Documents\CreateReceiptData;
use Szabrob\LaravelBillingo\Data\Documents\ReceiptCustomItemData;
use Szabrob\LaravelBillingo\Enums\DocumentType;
use Szabrob\LaravelBillingo\Enums\ReceiptVat;

$receipt = Billingo::documents()->createReceipt(new CreateReceiptData(
    blockId: config('services.billingo.receipt_block_id'),
    type: DocumentType::Receipt,
    paymentMethod: PaymentMethod::Cash,
    currency: Currency::HUF,
    emails: ['customer@example.com'],
    items: [
        new ReceiptCustomItemData(unitPrice: 1270, vat: ReceiptVat::Percent27, name: 'Coffee', quantity: 2, unit: 'db'),
    ],
));

$print = Billingo::documents()->posPrint($receipt->id, size: 58); // formatted for 58 or 80 mm wide POS printer paper
```

### Recording payments

Mark an invoice as paid, for example from a bank transfer or payment provider webhook. `updatePayments()` replaces the whole payment history of the document:

```php
use Szabrob\LaravelBillingo\Data\Documents\PaymentData;

Billingo::documents()->updatePayments($invoice->id, [
    new PaymentData(date: CarbonImmutable::today(), price: 12700, paymentMethod: PaymentMethod::WireTransfer),
]);

Billingo::documents()->payments($invoice->id);       // Collection<int, PaymentData>
Billingo::documents()->deletePayments($invoice->id); // mark as unpaid again
```

List the overdue invoices, for example to send your own reminders:

```php
use Szabrob\LaravelBillingo\Data\Documents\DocumentFilterData;
use Szabrob\LaravelBillingo\Enums\PaymentStatus;

Billingo::documents()
    ->lazy(new DocumentFilterData(paymentStatus: PaymentStatus::Expired))
    ->each(fn ($invoice) => /* ... */);
```

### Checking a Hungarian tax number

Validate a tax number against the NAV database and prefill the partner's details. This needs a live NAV connection in the Billingo account (it is not available in sandbox accounts):

```php
use Szabrob\LaravelBillingo\Enums\TaxNumberCheckResult;

$check = Billingo::utils()->checkTaxNumber('12345678-2-42');

if ($check->result === TaxNumberCheckResult::ValidationOk) {
    $check->taxpayerName; // "EXAMPLE KORLÁTOLT FELELŐSSÉGŰ TÁRSASÁG"
    $check->addresses;    // list<TaxpayerAddressData>
}
```

### NAV (Online Számla) status

Billingo reports invoices to NAV automatically. Check the result for a document:

```php
$status = Billingo::documents()->onlineSzamlaStatus($invoice->id);

$status->transactionId;
$status->status;
$status->messages; // list<OnlineSzamlaMessageData>
```

### Exporting documents for accounting

Exports are generated asynchronously: create one, poll until it is ready, then download the file:

```php
use Szabrob\LaravelBillingo\Data\DocumentExports\CreateDocumentExportData;
use Szabrob\LaravelBillingo\Enums\DocumentExportQueryType;
use Szabrob\LaravelBillingo\Enums\DocumentExportState;
use Szabrob\LaravelBillingo\Enums\DocumentExportType;

$exportId = Billingo::documentExports()->create(new CreateDocumentExportData(
    queryType: DocumentExportQueryType::FulfillmentDate,
    startDate: CarbonImmutable::parse('first day of last month'),
    endDate: CarbonImmutable::parse('last day of last month'),
    exportType: DocumentExportType::SimpleExcel,
));

// Later, e.g. from a delayed job:
if (Billingo::documentExports()->poll($exportId)->state === DocumentExportState::Success) {
    Storage::put('exports/last-month.xlsx', Billingo::documentExports()->download($exportId));
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Billingo! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Szabó Róbert](https://github.com/szabrob)
- [All Contributors](../../contributors)

## License

Laravel Billingo is open-sourced software licensed under the [MIT license](LICENSE.md).
