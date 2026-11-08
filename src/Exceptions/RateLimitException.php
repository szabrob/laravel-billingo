<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Exceptions;

use Illuminate\Http\Client\Response;

class RateLimitException extends BillingoException
{
    public function __construct(string $message, Response $response)
    {
        parent::__construct($message, $response->status(), $response);
    }

    /**
     * The number of seconds to wait before retrying, as sent in the Retry-After header.
     */
    public function retryAfter(): ?int
    {
        $header = $this->response?->header('Retry-After');

        return is_numeric($header) ? (int) $header : null;
    }
}
