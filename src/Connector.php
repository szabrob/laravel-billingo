<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Exceptions\BillingoException;

/**
 * Sends authenticated requests to the Billingo API v3.
 */
final class Connector
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $baseUrl,
        private readonly int $timeout,
    ) {}

    /**
     * @param  array<string, mixed>  $query
     */
    public function get(string $uri, array $query = []): Response
    {
        return $this->send('GET', $uri, ['query' => $query]);
    }

    /**
     * @param  array<array-key, mixed>  $body
     */
    public function post(string $uri, array $body = []): Response
    {
        return $this->send('POST', $uri, ['json' => $body]);
    }

    /**
     * @param  array<array-key, mixed>  $body
     */
    public function put(string $uri, array $body = []): Response
    {
        return $this->send('PUT', $uri, ['json' => $body]);
    }

    public function delete(string $uri): Response
    {
        return $this->send('DELETE', $uri);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function send(string $method, string $uri, array $options = []): Response
    {
        $response = $this->request()->send($method, ltrim($uri, '/'), $options);

        if ($response->failed()) {
            throw BillingoException::fromResponse($response);
        }

        return $response;
    }

    private function request(): PendingRequest
    {
        if ($this->apiKey === null || $this->apiKey === '') {
            throw BillingoException::missingApiKey();
        }

        return Http::baseUrl(rtrim($this->baseUrl, '/').'/')
            ->withHeaders(['X-API-KEY' => $this->apiKey])
            ->acceptJson()
            ->timeout($this->timeout);
    }
}
