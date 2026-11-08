<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Facades\Billingo as BillingoFacade;

it('merges the package config', function () {
    expect(config('laravel-billingo.base_url'))->toBe('https://api.billingo.hu/v3')
        ->and(config('laravel-billingo.timeout'))->toBe(30);
});

it('registers the client as a singleton', function () {
    expect(app(Billingo::class))
        ->toBeInstanceOf(Billingo::class)
        ->toBe(app(Billingo::class));
});

it('resolves the client through the facade', function () {
    expect(BillingoFacade::getFacadeRoot())->toBe(app(Billingo::class));
});

it('publishes the config file', function () {
    $target = config_path('laravel-billingo.php');
    File::delete($target);

    $this->artisan('vendor:publish', ['--tag' => 'laravel-billingo-config'])->assertSuccessful();

    expect($target)->toBeFile();

    File::delete($target);
});
