<?php

declare(strict_types=1);

namespace LaravelBillingo\Billingo\Tests;

use LaravelBillingo\Billingo\BillingoServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BillingoServiceProvider::class,
        ];
    }
}
