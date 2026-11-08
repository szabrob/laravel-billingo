<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Illuminate\Support\LazyCollection;
use Szabrob\LaravelBillingo\Data\Products\ProductData;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;

final class Products extends Resource
{
    /**
     * @return PaginatedResponse<ProductData>
     */
    public function list(int $page = 1, int $perPage = 25, ?string $query = null): PaginatedResponse
    {
        $response = $this->connector->get('products', array_filter([
            'page' => $page,
            'per_page' => $perPage,
            'query' => $query,
        ]));

        return PaginatedResponse::fromArray($this->json($response), ProductData::class);
    }

    /**
     * @return LazyCollection<int, ProductData>
     */
    public function lazy(?string $query = null, int $perPage = 100): LazyCollection
    {
        return $this->lazily(fn (int $page): PaginatedResponse => $this->list($page, $perPage, $query));
    }

    public function get(int $id): ProductData
    {
        return ProductData::from($this->json($this->connector->get("products/{$id}")));
    }

    public function create(ProductData $product): ProductData
    {
        return ProductData::from($this->json($this->connector->post('products', $product->toPayload())));
    }

    public function update(int $id, ProductData $product): ProductData
    {
        return ProductData::from($this->json($this->connector->put("products/{$id}", $product->toPayload())));
    }

    public function delete(int $id): void
    {
        $this->connector->delete("products/{$id}");
    }
}
