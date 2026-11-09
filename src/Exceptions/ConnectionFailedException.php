<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Exceptions;

use Illuminate\Http\Client\ConnectionException;

/**
 * Thrown when the Billingo API cannot be reached (DNS failure, connection refused, timeout, ...).
 */
class ConnectionFailedException extends BillingoException
{
    public static function fromConnectionException(ConnectionException $exception): self
    {
        return new self('Could not connect to the Billingo API: '.$exception->getMessage(), previous: $exception);
    }
}
