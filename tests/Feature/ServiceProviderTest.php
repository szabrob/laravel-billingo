<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\BillingoServiceProvider;
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
    // Publish into a per-test directory: writing to the shared Testbench skeleton config
    // directory races with parallel test processes that load the configuration files.
    $configPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-billingo-'.bin2hex(random_bytes(8));
    File::ensureDirectoryExists($configPath);
    $this->app->useConfigPath($configPath);
    (new BillingoServiceProvider($this->app))->boot();

    try {
        $this->artisan('vendor:publish', ['--tag' => 'laravel-billingo-config'])->assertSuccessful();

        expect($configPath.DIRECTORY_SEPARATOR.'laravel-billingo.php')->toBeFile()
            ->and(File::get($configPath.DIRECTORY_SEPARATOR.'laravel-billingo.php'))
            ->toBe(File::get(__DIR__.'/../../config/laravel-billingo.php'));
    } finally {
        File::deleteDirectory($configPath);
    }
});
