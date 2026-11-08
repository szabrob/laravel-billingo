<?php

declare(strict_types=1);

use LaravelBillingo\Billingo\Billingo;

it('resolves the singleton', function () {
    expect(app(Billingo::class))->toBeInstanceOf(Billingo::class);
});

it('returns the same instance from the container', function () {
    expect(app(Billingo::class))->toBe(app(Billingo::class));
});

it('merges the package config', function () {
    expect(config('laravel-billingo.placeholder'))->toBe('default');
});

it('registers the artisan command', function () {
    $this->artisan('laravel-billingo:placeholder')
        ->expectsOutputToContain('Billingo placeholder command executed.')
        ->assertSuccessful();
});
