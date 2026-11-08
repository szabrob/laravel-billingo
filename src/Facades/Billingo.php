<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Szabrob\LaravelBillingo\Billingo withApiKey(string $apiKey)
 * @method static \Szabrob\LaravelBillingo\Resources\BankAccounts bankAccounts()
 * @method static \Szabrob\LaravelBillingo\Resources\Currencies currencies()
 * @method static \Szabrob\LaravelBillingo\Resources\DocumentBlocks documentBlocks()
 * @method static \Szabrob\LaravelBillingo\Resources\DocumentExports documentExports()
 * @method static \Szabrob\LaravelBillingo\Resources\Documents documents()
 * @method static \Szabrob\LaravelBillingo\Resources\Inventory inventory()
 * @method static \Szabrob\LaravelBillingo\Resources\Organization organization()
 * @method static \Szabrob\LaravelBillingo\Resources\Partners partners()
 * @method static \Szabrob\LaravelBillingo\Resources\Products products()
 * @method static \Szabrob\LaravelBillingo\Resources\Spendings spendings()
 * @method static \Szabrob\LaravelBillingo\Resources\Utils utils()
 *
 * @see \Szabrob\LaravelBillingo\Billingo
 */
class Billingo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Szabrob\LaravelBillingo\Billingo::class;
    }
}
