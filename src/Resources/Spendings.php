<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Illuminate\Support\LazyCollection;
use Szabrob\LaravelBillingo\Data\Spendings\SaveSpendingData;
use Szabrob\LaravelBillingo\Data\Spendings\SpendingData;
use Szabrob\LaravelBillingo\Data\Spendings\SpendingFilterData;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;

final class Spendings extends Resource
{
    /**
     * @return PaginatedResponse<SpendingData>
     */
    public function list(?SpendingFilterData $filter = null, int $page = 1, int $perPage = 25): PaginatedResponse
    {
        $response = $this->connector->get('spendings', [
            ...($filter?->toPayload() ?? []),
            'page' => $page,
            'per_page' => $perPage,
        ]);

        return PaginatedResponse::fromArray($this->json($response), SpendingData::class);
    }

    /**
     * @return LazyCollection<int, SpendingData>
     */
    public function lazy(?SpendingFilterData $filter = null, int $perPage = 100): LazyCollection
    {
        return $this->lazily(fn (int $page): PaginatedResponse => $this->list($filter, $page, $perPage));
    }

    public function get(int $id): SpendingData
    {
        return SpendingData::from($this->json($this->connector->get("spendings/{$id}")));
    }

    public function create(SaveSpendingData $spending): SpendingData
    {
        return SpendingData::from($this->json($this->connector->post('spendings', $spending->toPayload())));
    }

    public function update(int $id, SaveSpendingData $spending): SpendingData
    {
        return SpendingData::from($this->json($this->connector->put("spendings/{$id}", $spending->toPayload())));
    }

    public function delete(int $id): void
    {
        $this->connector->delete("spendings/{$id}");
    }
}
