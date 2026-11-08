<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Illuminate\Support\LazyCollection;
use Szabrob\LaravelBillingo\Data\Partners\PartnerData;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;

final class Partners extends Resource
{
    /**
     * @return PaginatedResponse<PartnerData>
     */
    public function list(int $page = 1, int $perPage = 25, ?string $query = null): PaginatedResponse
    {
        $response = $this->connector->get('partners', array_filter([
            'page' => $page,
            'per_page' => $perPage,
            'query' => $query,
        ]));

        return PaginatedResponse::fromArray($this->json($response), PartnerData::class);
    }

    /**
     * @return LazyCollection<int, PartnerData>
     */
    public function lazy(?string $query = null, int $perPage = 100): LazyCollection
    {
        return $this->lazily(fn (int $page): PaginatedResponse => $this->list($page, $perPage, $query));
    }

    public function get(int $id): PartnerData
    {
        return PartnerData::from($this->json($this->connector->get("partners/{$id}")));
    }

    public function create(PartnerData $partner): PartnerData
    {
        return PartnerData::from($this->json($this->connector->post('partners', $partner->toPayload())));
    }

    public function update(int $id, PartnerData $partner): PartnerData
    {
        return PartnerData::from($this->json($this->connector->put("partners/{$id}", $partner->toPayload())));
    }

    public function delete(int $id): void
    {
        $this->connector->delete("partners/{$id}");
    }

    /**
     * Find an existing partner matching the given details, or create it.
     */
    public function guess(PartnerData $partner): PartnerData
    {
        return PartnerData::from($this->json($this->connector->post('partners/guess', $partner->toPayload())));
    }
}
