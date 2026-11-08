<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Exceptions;

use Illuminate\Http\Client\Response;

/**
 * Thrown when Billingo has not generated the requested document PDF yet (HTTP 202).
 */
class DocumentNotReadyException extends BillingoException
{
    public function __construct(int $documentId, Response $response)
    {
        parent::__construct(
            sprintf('The PDF of Billingo document %d has not been generated yet. Try again later.', $documentId),
            $response->status(),
            $response,
        );
    }
}
