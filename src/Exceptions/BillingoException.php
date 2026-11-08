<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Exceptions;

use Illuminate\Http\Client\Response;
use RuntimeException;

class BillingoException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        public readonly ?Response $response = null,
    ) {
        parent::__construct($message, $code);
    }

    public static function fromResponse(Response $response): self
    {
        $message = self::messageFrom($response);

        return match ($response->status()) {
            422 => new ValidationException($message, $response),
            429 => new RateLimitException($message, $response),
            default => new self($message, $response->status(), $response),
        };
    }

    public static function missingApiKey(): self
    {
        return new self('The Billingo API key is not configured. Set BILLINGO_API_KEY or call Billingo::withApiKey().');
    }

    private static function messageFrom(Response $response): string
    {
        $message = $response->json('error.message') ?? $response->json('message');

        return is_string($message) && $message !== ''
            ? $message
            : sprintf('Billingo API request failed with status %d.', $response->status());
    }
}
