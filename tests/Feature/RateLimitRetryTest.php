<?php

declare(strict_types=1);

use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Exceptions\RateLimitException;
use Szabrob\LaravelBillingo\Facades\Billingo as BillingoFacade;

beforeEach(function () {
    Http::preventStrayRequests();
    Sleep::fake();
});

function tooManyRequests(string $retryAfter = '3'): mixed
{
    return Http::response(['error' => ['message' => 'Too many requests.']], 429, ['Retry-After' => $retryAfter]);
}

it('has rate limit retry defaults', function () {
    expect(config('laravel-billingo.rate_limit'))->toBe(['retries' => 2, 'max_delay' => 10]);
});

it('retries rate limited requests after the retry-after delay', function () {
    Http::fake(['*' => Http::sequence()
        ->pushResponse(tooManyRequests('3'))
        ->push(['epoch' => 1]),
    ]);

    expect(BillingoFacade::utils()->serverTime()->epoch)->toBe(1);

    Http::assertSentCount(2);
    Sleep::assertSequence([Sleep::for(3)->seconds()]);
});

it('waits one second when no retry-after header is sent', function () {
    Http::fake(['*' => Http::sequence()
        ->push(['error' => ['message' => 'Too many requests.']], 429)
        ->push(['epoch' => 1]),
    ]);

    BillingoFacade::utils()->serverTime();

    Sleep::assertSlept(fn (CarbonInterval $duration): bool => (int) $duration->totalMilliseconds === 1000);
});

it('throws a rate limit exception once the retries are exhausted', function () {
    Http::fake(['*' => tooManyRequests('2')]);

    expect(fn () => BillingoFacade::utils()->serverTime())->toThrow(RateLimitException::class, 'Too many requests.');

    Http::assertSentCount(3);
    Sleep::assertSleptTimes(2);
});

it('does not retry when billingo asks to wait longer than the maximum delay', function () {
    Http::fake(['*' => tooManyRequests('60')]);

    expect(fn () => BillingoFacade::utils()->serverTime())->toThrow(RateLimitException::class);

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});

it('can disable retries', function () {
    config()->set('laravel-billingo.rate_limit.retries', 0);
    app()->forgetInstance(Billingo::class);
    BillingoFacade::clearResolvedInstances();
    Http::fake(['*' => tooManyRequests('1')]);

    expect(fn () => BillingoFacade::utils()->serverTime())->toThrow(RateLimitException::class);

    Http::assertSentCount(1);
});

it('does not retry other failed responses', function () {
    Http::fake(['*' => Http::response(['error' => ['message' => 'Server error.']], 500)]);

    expect(fn () => BillingoFacade::utils()->serverTime())->toThrow(BillingoException::class, 'Server error.');

    Http::assertSentCount(1);
    Sleep::assertNeverSlept();
});
