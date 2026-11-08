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

Optional fields default to `null` and are left out of the request payload.

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

Failed requests throw a `Szabrob\LaravelBillingo\Exceptions\BillingoException` (the HTTP response is available as `$exception->response`). More specific subclasses are thrown for some cases:

| Exception | When |
| --- | --- |
| `ValidationException` | HTTP 422; `errors()` returns the messages keyed by field |
| `RateLimitException` | HTTP 429; `retryAfter()` returns the seconds to wait |
| `DocumentNotReadyException` | The document PDF is not generated yet (HTTP 202 on download) |

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

The package uses Laravel's HTTP client, so you can fake Billingo responses with `Http::fake()`:

```php
Http::fake([
    'api.billingo.hu/v3/partners/*' => Http::response(['id' => 42, 'name' => 'Example Ltd.']),
]);
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
