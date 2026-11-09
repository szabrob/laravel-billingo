<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Facades;

use Illuminate\Support\Facades\Facade;
use Szabrob\LaravelBillingo\Testing\BillingoFake;

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
 * @method static \Szabrob\LaravelBillingo\Testing\BillingoFake stub(array<string, mixed> $responses)
 * @method static \Illuminate\Support\Collection<int, \Illuminate\Http\Client\Request> recorded(string|\Closure|null $endpoint = null, \Closure|null $callback = null)
 * @method static void assertSent(string|\Closure $endpoint, \Closure|null $callback = null)
 * @method static void assertNotSent(string|\Closure $endpoint, \Closure|null $callback = null)
 * @method static void assertSentCount(int $count)
 * @method static void assertNothingSent()
 *
 * @see \Szabrob\LaravelBillingo\Billingo
 */
class Billingo extends Facade
{
    /**
     * Replace the Billingo client with a fake that answers API requests with the given responses.
     *
     * @param  array<string, mixed>  $responses  Keyed by endpoint, e.g. "documents/*" or "POST partners".
     */
    public static function fake(array $responses = []): BillingoFake
    {
        $root = static::getFacadeRoot();

        if ($root instanceof BillingoFake) {
            return $root->stub($responses);
        }

        /** @var \Szabrob\LaravelBillingo\Billingo $root */
        static::swap($fake = new BillingoFake($root, $responses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Szabrob\LaravelBillingo\Billingo::class;
    }
}
