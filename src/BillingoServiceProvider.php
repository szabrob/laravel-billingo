<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Szabrob\LaravelBillingo\Console\Commands\CheckCommand;

class BillingoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-billingo.php', 'laravel-billingo');

        $this->app->singleton(Billingo::class, function (Application $app): Billingo {
            $config = $app->make(Repository::class);
            $apiKey = $config->get('laravel-billingo.api_key');

            return new Billingo(
                apiKey: is_string($apiKey) && $apiKey !== '' ? $apiKey : null,
                baseUrl: $config->string('laravel-billingo.base_url', 'https://api.billingo.hu/v3'),
                timeout: $config->integer('laravel-billingo.timeout', 30),
                retries: $config->integer('laravel-billingo.rate_limit.retries', 2),
                maxRetryDelay: $config->integer('laravel-billingo.rate_limit.max_delay', 10),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/laravel-billingo.php' => config_path('laravel-billingo.php'),
        ], ['laravel-billingo', 'laravel-billingo-config']);

        $this->commands([
            CheckCommand::class,
        ]);
    }
}
