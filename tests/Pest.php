<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Data\Shared\AddressData;
use Szabrob\LaravelBillingo\Enums\Country;
use Szabrob\LaravelBillingo\Enums\DocumentBlockType;
use Szabrob\LaravelBillingo\Enums\PartnerTaxType;
use Szabrob\LaravelBillingo\Facades\Billingo as BillingoFacade;
use Szabrob\LaravelBillingo\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * @return array<string, mixed>
 */
function billingoFixture(string $name): array
{
    /** @var array<string, mixed> */
    return json_decode((string) file_get_contents(__DIR__."/Fixtures/{$name}.json"), true, flags: JSON_THROW_ON_ERROR);
}

/**
 * @param  array<string, mixed>|null  $body
 */
function assertBillingoRequest(string $method, string $url, ?array $body = null): void
{
    Http::assertSent(function (Request $request) use ($method, $url, $body): bool {
        return $request->method() === $method
            && $request->url() === 'https://api.billingo.hu/v3/'.$url
            && ($body === null || $request->data() === $body);
    });
}

/*
|--------------------------------------------------------------------------
| Integration tests (tests/Integration)
|--------------------------------------------------------------------------
|
| These tests call the real Billingo API. They are not part of the default
| test suites and only run with `composer test:integration` when the
| BILLINGO_SANDBOX_API_KEY environment variable is set.
|
*/

/**
 * Point the client to the Billingo test account, or skip the test when no key is configured.
 */
function sandbox(): void
{
    $apiKey = getenv('BILLINGO_SANDBOX_API_KEY');

    if (! is_string($apiKey) || $apiKey === '') {
        test()->markTestSkipped('Set BILLINGO_SANDBOX_API_KEY to run the Billingo integration tests.');
    }

    $baseUrl = getenv('BILLINGO_SANDBOX_BASE_URL');

    config()->set('laravel-billingo.api_key', $apiKey);
    config()->set('laravel-billingo.base_url', is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : 'https://api.billingo.hu/v3');
    config()->set('laravel-billingo.rate_limit', ['retries' => 5, 'max_delay' => 60]);

    app()->forgetInstance(Billingo::class);
    BillingoFacade::clearResolvedInstances();
}

/**
 * Allow tests that issue real documents (invoices, receipts, proformas), which cannot be deleted afterwards.
 */
function sandboxIssuingDocuments(): void
{
    sandbox();

    if (getenv('BILLINGO_SANDBOX_ISSUE_DOCUMENTS') !== 'true') {
        test()->markTestSkipped('Set BILLINGO_SANDBOX_ISSUE_DOCUMENTS=true to issue documents in the Billingo test account.');
    }

    if (BillingoFacade::organization()->get()->hasNavConnection !== false) {
        test()->markTestSkipped('The Billingo account has a NAV connection; documents are only issued in test accounts without one.');
    }
}

/**
 * A unique, recognizable name for data created by the integration tests.
 */
function sandboxName(string $suffix = ''): string
{
    return trim('laravel-billingo-it '.bin2hex(random_bytes(4)).' '.$suffix);
}

function sandboxBlockId(DocumentBlockType $type): int
{
    $block = BillingoFacade::documentBlocks()->lazy($type)->first();

    if ($block?->id === null) {
        test()->markTestSkipped("The Billingo test account has no {$type->value} document block.");
    }

    return $block->id;
}

function sandboxPartner(): PartnerData
{
    $email = getenv('BILLINGO_SANDBOX_EMAIL');

    return BillingoFacade::partners()->create(new PartnerData(
        name: sandboxName('partner'),
        address: new AddressData(
            countryCode: Country::HU,
            postCode: '1111',
            city: 'Budapest',
            address: 'Teszt utca 1.',
        ),
        emails: is_string($email) && $email !== '' ? [$email] : [],
        taxType: PartnerTaxType::NoTaxNumber,
    ));
}

/**
 * Call the callback until it stops throwing the given exception or the timeout is reached.
 *
 * @template TResult
 *
 * @param  Closure(): TResult  $callback
 * @param  class-string<Throwable>  $retryOn
 * @return TResult
 */
function sandboxEventually(Closure $callback, string $retryOn, int $seconds = 60): mixed
{
    $deadline = time() + $seconds;

    while (true) {
        try {
            return $callback();
        } catch (Throwable $exception) {
            if (! $exception instanceof $retryOn || time() >= $deadline) {
                throw $exception;
            }

            Sleep::sleep(2);
        }
    }
}
