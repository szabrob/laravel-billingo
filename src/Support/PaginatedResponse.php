<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Support;

use ArrayIterator;
use Countable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Szabrob\LaravelBillingo\Data\BillingoData;

/**
 * A single page of a Billingo list endpoint.
 *
 * @template TData of BillingoData
 *
 * @implements IteratorAggregate<int, TData>
 */
final readonly class PaginatedResponse implements Countable, IteratorAggregate
{
    /**
     * @param  Collection<int, TData>  $data
     */
    public function __construct(
        public Collection $data,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
    ) {}

    /**
     * @template T of BillingoData
     *
     * @param  array<array-key, mixed>  $payload
     * @param  class-string<T>  $dataClass
     * @return self<T>
     */
    public static function fromArray(array $payload, string $dataClass): self
    {
        $items = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        /** @var Collection<int, T> $data */
        $data = $dataClass::collect(array_values($items), Collection::class);

        return new self(
            data: $data,
            total: self::int($payload['total'] ?? null, $data->count()),
            perPage: self::int($payload['per_page'] ?? null, $data->count()),
            currentPage: self::int($payload['current_page'] ?? null, 1),
            lastPage: self::int($payload['last_page'] ?? null, 1),
        );
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public function count(): int
    {
        return $this->data->count();
    }

    /**
     * @return ArrayIterator<int, TData>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data->all());
    }

    private static function int(mixed $value, int $default): int
    {
        return is_numeric($value) ? (int) $value : $default;
    }
}
