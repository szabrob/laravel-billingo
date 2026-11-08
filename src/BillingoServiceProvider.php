<?php

declare(strict_types=1);

namespace LaravelBillingo\Billingo;

use Illuminate\Support\ServiceProvider;
use LaravelBillingo\Billingo\Console\Commands\BillingoCommand;

class BillingoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-billingo.php', 'laravel-billingo');

        $this->app->singleton(Billingo::class);
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
            BillingoCommand::class,
        ]);
    }
}
