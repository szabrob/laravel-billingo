<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Closure;
use Illuminate\Http\Client\Response;
use Illuminate\Support\LazyCollection;
use Szabrob\LaravelBillingo\Connector;
use Szabrob\LaravelBillingo\Data\BillingoData;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;

abstract class Resource
{
    public function __construct(
        protected readonly Connector $connector,
    ) {}

    /**
     * @return array<array-key, mixed>
     */
    protected function json(Response $response): array
    {
        $json = $response->json();

        if (! is_array($json)) {
            throw new BillingoException('Billingo returned an unexpected, non-JSON response.', $response->status(), $response);
        }

        return $json;
    }

    /**
     * Iterate over every item of a paginated endpoint, fetching pages lazily.
     *
     * @template TData of BillingoData
     *
     * @param  Closure(int): PaginatedResponse<TData>  $page
     * @return LazyCollection<int, TData>
     */
    protected function lazily(Closure $page): LazyCollection
    {
        return LazyCollection::make(function () use ($page) {
            $current = 1;

            do {
                $response = $page($current++);

                yield from $response->data->all();
            } while ($response->hasMorePages());
        })->values();
    }
}
