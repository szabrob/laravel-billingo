<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\AssertionFailedError;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Data\Documents\DocumentData;
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Exceptions\ValidationException;
use Szabrob\LaravelBillingo\Facades\Billingo as BillingoFacade;
use Szabrob\LaravelBillingo\Testing\BillingoFake;

beforeEach(fn () => Http::preventStrayRequests());

it('swaps the facade and the container binding with a fake', function () {
    $fake = BillingoFacade::fake();

    expect($fake)->toBeInstanceOf(BillingoFake::class)
        ->and(BillingoFacade::getFacadeRoot())->toBe($fake)
        ->and(app(Billingo::class))->toBe($fake);
});

it('responds with data objects, arrays and strings', function () {
    BillingoFacade::fake([
        'documents/*/download' => '%PDF-fake',
        'documents/*' => new DocumentData(id: 1001, invoiceNumber: 'INV-2026-1'),
        'partners/*' => ['id' => 42, 'name' => 'Example Ltd.'],
    ]);

    expect(BillingoFacade::documents()->get(1001)->invoiceNumber)->toBe('INV-2026-1')
        ->and(BillingoFacade::documents()->download(1001))->toBe('%PDF-fake')
        ->and(BillingoFacade::partners()->get(42)->name)->toBe('Example Ltd.');
});

it('matches endpoints by http method', function () {
    BillingoFacade::fake([
        'GET partners/*' => ['id' => 1, 'name' => 'Fetched'],
        'PUT partners/*' => ['id' => 1, 'name' => 'Updated'],
    ]);

    expect(BillingoFacade::partners()->get(1)->name)->toBe('Fetched')
        ->and(BillingoFacade::partners()->update(1, new PartnerData(name: 'Updated'))->name)->toBe('Updated');
});

it('ignores the query string when matching list endpoints', function () {
    BillingoFacade::fake([
        'partners' => ['data' => [new PartnerData(id: 1, name: 'Example Ltd.')], 'total' => 1, 'current_page' => 1, 'last_page' => 1],
    ]);

    $page = BillingoFacade::partners()->list(page: 1, perPage: 50, query: 'Example');

    expect($page->total)->toBe(1)
        ->and($page->data->first()?->name)->toBe('Example Ltd.');
});

it('supports http responses, sequences and closures', function () {
    BillingoFacade::fake([
        'POST partners' => Http::response(['errors' => [['field' => 'name', 'message' => 'Required.']]], 422),
        'documents/1' => Http::sequence()->push(['id' => 1, 'invoice_number' => 'A'])->push(['id' => 1, 'invoice_number' => 'B']),
        'documents/*' => fn (Request $request): array => ['id' => (int) basename($request->url())],
    ]);

    expect(fn () => BillingoFacade::partners()->create(new PartnerData(name: '')))->toThrow(ValidationException::class)
        ->and(BillingoFacade::documents()->get(1)->invoiceNumber)->toBe('A')
        ->and(BillingoFacade::documents()->get(1)->invoiceNumber)->toBe('B')
        ->and(BillingoFacade::documents()->get(77)->id)->toBe(77);
});

it('responds with an empty successful response to unmatched billingo requests', function () {
    BillingoFacade::fake();

    BillingoFacade::documents()->delete(1);

    expect(BillingoFacade::documents()->get(1)->id)->toBeNull()
        ->and(BillingoFacade::partners()->lazy()->all())->toBe([]);
});

it('does not require an api key', function () {
    config()->set('laravel-billingo.api_key', null);
    app()->forgetInstance(Billingo::class);
    BillingoFacade::clearResolvedInstances();

    BillingoFacade::fake();
    BillingoFacade::utils()->serverTime();

    BillingoFacade::assertSent(fn (Request $request): bool => $request->hasHeader('X-API-KEY'));
});

it('keeps the configured api key and base url', function () {
    config()->set('laravel-billingo.base_url', 'https://sandbox.example.test/v3');
    app()->forgetInstance(Billingo::class);
    BillingoFacade::clearResolvedInstances();

    BillingoFacade::fake(['utils/time' => ['epoch' => 5]]);

    expect(BillingoFacade::utils()->serverTime()->epoch)->toBe(5);

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sandbox.example.test/v3/utils/time'
        && $request->hasHeader('X-API-KEY', 'test-api-key'));
});

it('does not fake requests to other hosts', function () {
    BillingoFacade::fake();
    Http::fake(['example.test/*' => Http::response('other')]);

    expect(Http::get('https://example.test/ping')->body())->toBe('other');

    BillingoFacade::assertNothingSent();
});

it('gives priority to responses added by a later fake call', function () {
    BillingoFacade::fake(['partners/*' => ['id' => 1, 'name' => 'First']]);
    $fake = BillingoFacade::fake(['partners/*' => ['id' => 1, 'name' => 'Second']]);

    expect(BillingoFacade::partners()->get(1)->name)->toBe('Second')
        ->and(BillingoFacade::getFacadeRoot())->toBe($fake);
});

it('fakes clients created with another api key', function () {
    BillingoFacade::fake(['utils/time' => ['epoch' => 9]]);

    expect(BillingoFacade::withApiKey('other-key')->utils()->serverTime()->epoch)->toBe(9);

    BillingoFacade::assertSent('GET utils/time', fn (Request $request): bool => $request->hasHeader('X-API-KEY', 'other-key'));
});

it('asserts sent requests by endpoint and payload', function () {
    BillingoFacade::fake();

    BillingoFacade::partners()->create(new PartnerData(name: 'Example Ltd.'));
    BillingoFacade::documents()->send(1001, ['billing@example.com']);

    BillingoFacade::assertSent('POST partners', fn (Request $request): bool => $request['name'] === 'Example Ltd.');
    BillingoFacade::assertSent('documents/*/send');
    BillingoFacade::assertNotSent('GET partners');
    BillingoFacade::assertNotSent('POST partners', fn (Request $request): bool => $request['name'] === 'Other');
    BillingoFacade::assertSentCount(2);

    expect(BillingoFacade::recorded())->toHaveCount(2);
});

it('fails assertions with a descriptive message', function (Closure $assertion, string $message) {
    BillingoFacade::fake();
    BillingoFacade::utils()->serverTime();

    expect($assertion)->toThrow(AssertionFailedError::class, $message);
})->with([
    'sent' => [fn () => BillingoFacade::assertSent('POST documents'), 'An expected Billingo request [POST documents] was not sent.'],
    'not sent' => [fn () => BillingoFacade::assertNotSent('utils/time'), 'An unexpected Billingo request [utils/time] was sent.'],
    'count' => [fn () => BillingoFacade::assertSentCount(3), 'Expected 3 Billingo requests, but 1 were sent.'],
    'nothing sent' => [fn () => BillingoFacade::assertNothingSent(), 'Billingo requests were sent unexpectedly.'],
]);
