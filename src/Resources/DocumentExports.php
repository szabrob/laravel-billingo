<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Resources;

use Szabrob\LaravelBillingo\Data\DocumentExports\CreateDocumentExportData;
use Szabrob\LaravelBillingo\Data\DocumentExports\DocumentExportStatusData;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;

final class DocumentExports extends Resource
{
    /**
     * Start an asynchronous document export and return its export ID.
     */
    public function create(CreateDocumentExportData $export): string
    {
        $response = $this->connector->post('document-export', $export->toPayload());
        $id = $this->json($response)['id'] ?? null;

        if (! is_string($id) && ! is_int($id)) {
            throw new BillingoException('Billingo did not return a document export ID.', $response->status(), $response);
        }

        return (string) $id;
    }

    public function poll(string $id): DocumentExportStatusData
    {
        return DocumentExportStatusData::from($this->json($this->connector->get("document-export/{$id}/poll")));
    }

    /**
     * Download a finished export as raw file contents.
     */
    public function download(string $id): string
    {
        return $this->connector->get("document-export/{$id}/download")->body();
    }
}
