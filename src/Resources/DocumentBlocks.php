<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Illuminate\Support\LazyCollection;
use Szabrob\LaravelBillingo\Data\DocumentBlocks\CreateDocumentBlockData;
use Szabrob\LaravelBillingo\Data\DocumentBlocks\DocumentBlockData;
use Szabrob\LaravelBillingo\Enums\DocumentBlockType;
use Szabrob\LaravelBillingo\Support\PaginatedResponse;

final class DocumentBlocks extends Resource
{
    /**
     * @return PaginatedResponse<DocumentBlockData>
     */
    public function list(int $page = 1, int $perPage = 25, ?DocumentBlockType $type = null): PaginatedResponse
    {
        $response = $this->connector->get('document-blocks', array_filter([
            'page' => $page,
            'per_page' => $perPage,
            'type' => $type?->value,
        ]));

        return PaginatedResponse::fromArray($this->json($response), DocumentBlockData::class);
    }

    /**
     * @return LazyCollection<int, DocumentBlockData>
     */
    public function lazy(?DocumentBlockType $type = null, int $perPage = 100): LazyCollection
    {
        return $this->lazily(fn (int $page): PaginatedResponse => $this->list($page, $perPage, $type));
    }

    public function create(CreateDocumentBlockData $documentBlock): void
    {
        $this->connector->post('document-blocks', $documentBlock->toPayload());
    }
}
