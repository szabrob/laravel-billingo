<?php

declare(strict_types=1);

namespace LaravelBillingo\Billingo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \LaravelBillingo\Billingo\Billingo
 */
class Billingo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \LaravelBillingo\Billingo\Billingo::class;
    }
}
