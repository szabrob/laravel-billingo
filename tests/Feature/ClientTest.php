<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Exceptions\ConnectionFailedException;
use Szabrob\LaravelBillingo\Exceptions\RateLimitException;
use Szabrob\LaravelBillingo\Exceptions\ValidationException;
use Szabrob\LaravelBillingo\Facades\Billingo as BillingoFacade;

beforeEach(fn () => Http::preventStrayRequests());

it('authenticates requests with the configured api key', function () {
    Http::fake(['*' => Http::response(['epoch' => 1, 'formatted' => '', 'w3c' => '', 'timezone' => 'UTC'])]);

    BillingoFacade::utils()->serverTime();

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-API-KEY', 'test-api-key')
        && $request->hasHeader('Accept', 'application/json')
        && $request->url() === 'https://api.billingo.hu/v3/utils/time');
});

it('uses the configured base url', function () {
    config()->set('laravel-billingo.base_url', 'https://sandbox.example.test/v3/');
    app()->forgetInstance(Billingo::class);
    Http::fake(['*' => Http::response(['epoch' => 1])]);

    app(Billingo::class)->utils()->serverTime();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sandbox.example.test/v3/utils/time');
});

it('can switch the api key at runtime without changing the default client', function () {
    Http::fake(['*' => Http::response(['epoch' => 1])]);

    BillingoFacade::withApiKey('other-key')->utils()->serverTime();
    BillingoFacade::utils()->serverTime();

    $keys = Http::recorded()->map(fn (array $pair): array => $pair[0]->header('X-API-KEY'))->all();

    expect($keys)->toBe([['other-key'], ['test-api-key']]);
});

it('throws when no api key is configured', function () {
    config()->set('laravel-billingo.api_key', null);
    app()->forgetInstance(Billingo::class);

    app(Billingo::class)->utils()->serverTime();
})->throws(BillingoException::class, 'The Billingo API key is not configured.');

it('throws a validation exception with the field errors', function () {
    Http::fake(['*' => Http::response([
        'message' => 'The given data was invalid.',
        'errors' => [
            ['field' => 'name', 'message' => 'The name field is required.'],
            ['field' => 'name', 'message' => 'The name is too short.'],
        ],
    ], 422)]);

    try {
        BillingoFacade::partners()->list();
        $this->fail('Expected a validation exception.');
    } catch (ValidationException $exception) {
        expect($exception->getMessage())->toBe('The given data was invalid.')
            ->and($exception->getCode())->toBe(422)
            ->and($exception->errors())->toBe(['name' => ['The name field is required.', 'The name is too short.']]);
    }
});

it('throws a rate limit exception with the retry delay', function () {
    Http::fake(['*' => Http::response(['error' => ['message' => 'Too many requests.']], 429, ['Retry-After' => '17'])]);

    try {
        BillingoFacade::partners()->list();
        $this->fail('Expected a rate limit exception.');
    } catch (RateLimitException $exception) {
        expect($exception->getMessage())->toBe('Too many requests.')
            ->and($exception->retryAfter())->toBe(17);
    }
});

it('throws a billingo exception for other failed responses', function (int $status, mixed $body, string $message) {
    Http::fake(['*' => Http::response($body, $status)]);

    try {
        BillingoFacade::partners()->get(1);
        $this->fail('Expected a Billingo exception.');
    } catch (BillingoException $exception) {
        expect($exception)->not->toBeInstanceOf(ValidationException::class)
            ->and($exception->getMessage())->toBe($message)
            ->and($exception->getCode())->toBe($status)
            ->and($exception->response?->status())->toBe($status);
    }
})->with([
    'unauthorized' => [401, ['error' => ['message' => 'Unauthorized.']], 'Unauthorized.'],
    'payment required' => [402, ['error' => ['message' => 'Subscription expired.']], 'Subscription expired.'],
    'not found' => [404, ['error' => ['message' => 'Not found.']], 'Not found.'],
    'server error without body' => [500, '', 'Billingo API request failed with status 500.'],
]);

it('throws when a json response was expected but not returned', function () {
    Http::fake(['*' => Http::response('<html></html>')]);

    BillingoFacade::partners()->get(1);
})->throws(BillingoException::class, 'non-JSON response');

it('wraps connection failures in a billingo exception', function () {
    $previous = new ConnectionException('Connection timed out.');
    Http::fake(fn () => throw $previous);

    try {
        BillingoFacade::partners()->list();
        $this->fail('Expected a connection failed exception.');
    } catch (BillingoException $exception) {
        expect($exception)->toBeInstanceOf(ConnectionFailedException::class)
            ->and($exception->getMessage())->toBe('Could not connect to the Billingo API: Connection timed out.')
            ->and($exception->response)->toBeNull()
            ->and($exception->getPrevious())->toBe($previous);
    }
});

it('does not retry connection failures', function () {
    Http::fake(fn () => throw new ConnectionException('Connection refused.'));

    expect(fn () => BillingoFacade::utils()->serverTime())->toThrow(ConnectionFailedException::class);

    Http::assertSentCount(0);
});
