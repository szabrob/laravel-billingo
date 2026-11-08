<?php

declare(strict_types=1);

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Billingo;

beforeEach(fn () => Http::preventStrayRequests());

it('reports a working connection with the organization details', function () {
    Http::fake(['api.billingo.hu/v3/organization' => Http::response([
        'tax_code' => '87654321-1-41',
        'subscription' => ['expiration_date' => '2027-01-01', 'features' => ['subscription_pro', 'inventory']],
        'has_nav_connection' => true,
    ])]);

    $this->artisan('billingo:check')
        ->expectsOutputToContain('The Billingo API connection is working.')
        ->expectsOutputToContain('87654321-1-41')
        ->expectsOutputToContain('2027-01-01')
        ->expectsOutputToContain('subscription_pro, inventory')
        ->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-API-KEY', 'test-api-key'));
});

it('checks the api key given as an option', function () {
    Http::fake(['*' => Http::response(['tax_code' => '87654321-1-41'])]);

    $this->artisan('billingo:check', ['--key' => 'other-key'])->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => $request->hasHeader('X-API-KEY', 'other-key'));
});

it('fails when the api key is rejected', function () {
    Http::fake(['*' => Http::response(['error' => ['message' => 'Unauthorized.']], 401)]);

    $this->artisan('billingo:check')
        ->expectsOutputToContain('Billingo API request failed (HTTP 401): Unauthorized.')
        ->assertFailed();
});

it('fails when no api key is configured', function () {
    config()->set('laravel-billingo.api_key', null);
    app()->forgetInstance(Billingo::class);

    $this->artisan('billingo:check')
        ->expectsOutputToContain('The Billingo API key is not configured.')
        ->assertFailed();

    Http::assertNothingSent();
});

it('fails when billingo cannot be reached', function () {
    Http::fake(fn () => throw new ConnectionException('Connection timed out.'));

    $this->artisan('billingo:check')
        ->expectsOutputToContain('Could not connect to the Billingo API: Connection timed out.')
        ->assertFailed();
});
