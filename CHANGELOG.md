# Release Notes

All notable changes to `szabrob/laravel-billingo` are documented in this file. Releases follow [Semantic Versioning](https://semver.org/); while the version is `0.x`, minor releases may contain breaking changes.

## [Unreleased](https://github.com/szabrob/laravel-billingo/compare/v0.2.0...HEAD)

## [v0.2.0](https://github.com/szabrob/laravel-billingo/compare/v0.1.0...v0.2.0) - 2026-11-09

### Added

- `Billingo::fake()` testing helper: fake Billingo API responses by endpoint (Data objects, arrays, raw bodies, `Http::response()`, `Http::sequence()` or closures) and assert requests with `assertSent()`, `assertNotSent()`, `assertSentCount()` and `assertNothingSent()`

### Changed

- **Breaking:** network errors (DNS failure, connection refused, timeout) now throw `Szabrob\LaravelBillingo\Exceptions\ConnectionFailedException`, a `BillingoException` subclass, instead of Laravel's `ConnectionException`; the original exception is available via `getPrevious()`
- **Breaking:** the `ReceiptVat::none` enum case is renamed to `ReceiptVat::None` to match the naming of the other enum cases (the value is still `none`)

### Documentation

- New "Common workflows" README section: queued invoicing of webshop orders, cancellation and modification invoices, proforma invoices, receipts, payments, tax number checks, NAV status and accounting exports
- README section on testing with `Billingo::fake()`

### Upgrading from v0.1.0

- Replace `catch (\Illuminate\Http\Client\ConnectionException $e)` around Billingo calls with `catch (\Szabrob\LaravelBillingo\Exceptions\ConnectionFailedException $e)`, or catch `BillingoException` to handle every Billingo error
- Replace `ReceiptVat::none` with `ReceiptVat::None`

### Requirements

- PHP 8.3+
- Laravel 12 or 13

## [v0.1.0](https://github.com/szabrob/laravel-billingo/releases/tag/v0.1.0) - 2026-11-08

First pre-release of the Laravel integration for the [Billingo API v3](https://app.swaggerhub.com/apis/Billingo/Billingo/3.0.15).

### Features

- Coverage of the Billingo API v3 (spec 3.0.15) through the `Billingo` facade: `bankAccounts()`, `currencies()`, `documentBlocks()`, `documentExports()`, `documents()`, `inventory()`, `organization()`, `partners()`, `products()`, `spendings()` and `utils()`
- Request and response objects built on `spatie/laravel-data` with camelCase properties, `CarbonImmutable` dates and PHP enums (`Currency`, `Vat`, `PaymentMethod`, `DocumentType`, …); unset optional fields are omitted from request payloads
- Invoice, receipt, proforma, modification and cancellation documents, sending, PDF and POS downloads, payments, NAV (Online Számla) status and reminders
- Paginated `list()` methods returning `PaginatedResponse`, and `lazy()` methods iterating over every page as a `LazyCollection`
- Runtime API key switching with `Billingo::withApiKey()` for multiple Billingo accounts
- Automatic retry of `429 Too Many Requests` responses using the `Retry-After` header (configurable via `rate_limit.retries` and `rate_limit.max_delay`)
- Typed exceptions: `BillingoException`, `ValidationException` (`errors()`), `RateLimitException` (`retryAfter()`) and `DocumentNotReadyException`
- `php artisan billingo:check` command to verify the API key and connection
- Laravel Boost skill for AI-assisted integration

### Requirements

- PHP 8.3+
- Laravel 12 or 13

### Notes

- The `GET`/`PUT`/`DELETE /partners/{id}` endpoints are missing from the 3.0.15 spec; they are implemented according to spec 3.0.14.
- Enum values not present in the Billingo spec cause a `CannotCastEnum` exception when building response objects.
