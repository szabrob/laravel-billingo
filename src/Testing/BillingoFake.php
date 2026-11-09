<?php

declare(strict_types=1);

namespace Szabrob\LaravelBillingo\Testing;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PHPUnit\Framework\Assert as PHPUnit;
use Szabrob\LaravelBillingo\Billingo;
use Szabrob\LaravelBillingo\Data\BillingoData;

/**
 * A Billingo client that answers its API requests with fake responses and records them for assertions.
 *
 * Requests still go through the real resources and Laravel's HTTP client, only requests to the
 * configured Billingo base URL are faked.
 */
final class BillingoFake extends Billingo
{
    /**
     * Fake responses keyed by endpoint pattern, e.g. "documents/*" or "POST partners".
     *
     * @var array<string, mixed>
     */
    private array $responses = [];

    /**
     * @param  array<string, mixed>  $responses
     */
    public function __construct(Billingo $billingo, array $responses = [])
    {
        parent::__construct(
            apiKey: $billingo->apiKey ?? 'billingo-fake-api-key',
            baseUrl: $billingo->baseUrl,
            timeout: $billingo->timeout,
            retries: $billingo->retries,
            maxRetryDelay: $billingo->maxRetryDelay,
        );

        $this->stub($responses);

        Http::fake(fn (Request $request): ?PromiseInterface => $this->respond($request));
    }

    /**
     * Register more fake responses. They take priority over the ones registered earlier.
     *
     * @param  array<string, mixed>  $responses
     */
    public function stub(array $responses): self
    {
        $this->responses = $responses + $this->responses;

        return $this;
    }

    /**
     * Get the Billingo requests that were sent, optionally filtered by endpoint and callback.
     *
     * @param  (Closure(Request): bool)|null  $callback
     * @return Collection<int, Request>
     */
    public function recorded(string|Closure|null $endpoint = null, ?Closure $callback = null): Collection
    {
        if ($endpoint instanceof Closure) {
            [$endpoint, $callback] = [null, $endpoint];
        }

        return Http::recorded(fn (Request $request): bool => $this->path($request) !== null)
            ->map(fn (array $pair): Request => $pair[0])
            ->filter(fn (Request $request): bool => ($endpoint === null || $this->matches($endpoint, $request))
                && ($callback === null || $callback($request)))
            ->values();
    }

    /**
     * Assert that a Billingo request matching the endpoint (e.g. "POST documents") and callback was sent.
     *
     * @param  (Closure(Request): bool)|null  $callback
     */
    public function assertSent(string|Closure $endpoint, ?Closure $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->recorded($endpoint, $callback)->isNotEmpty(),
            sprintf('An expected Billingo request [%s] was not sent.', $this->describe($endpoint)),
        );
    }

    /**
     * Assert that no Billingo request matching the endpoint and callback was sent.
     *
     * @param  (Closure(Request): bool)|null  $callback
     */
    public function assertNotSent(string|Closure $endpoint, ?Closure $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->recorded($endpoint, $callback)->isEmpty(),
            sprintf('An unexpected Billingo request [%s] was sent.', $this->describe($endpoint)),
        );
    }

    public function assertSentCount(int $count): void
    {
        $sent = $this->recorded()->count();

        PHPUnit::assertSame($count, $sent, sprintf('Expected %d Billingo requests, but %d were sent.', $count, $sent));
    }

    public function assertNothingSent(): void
    {
        PHPUnit::assertTrue($this->recorded()->isEmpty(), 'Billingo requests were sent unexpectedly.');
    }

    /**
     * Answer a request to the Billingo API, or return null for requests to other hosts.
     */
    private function respond(Request $request): ?PromiseInterface
    {
        if ($this->path($request) === null) {
            return null;
        }

        foreach ($this->responses as $endpoint => $response) {
            if ($this->matches($endpoint, $request)) {
                return $this->toResponse($response, $request);
            }
        }

        return Http::response([]);
    }

    private function toResponse(mixed $response, Request $request): PromiseInterface
    {
        return match (true) {
            $response instanceof PromiseInterface => $response,
            $response instanceof Closure, is_object($response) && is_callable($response) => $this->toResponse($response($request), $request),
            $response instanceof BillingoData => Http::response($response->toArray()),
            is_array($response), is_string($response) => Http::response($response),
            $response === null => Http::response([]),
            default => throw new InvalidArgumentException(sprintf('Unsupported fake Billingo response of type [%s].', get_debug_type($response))),
        };
    }

    /**
     * Determine if the request matches an endpoint pattern like "documents/*" or "POST documents/*\/send".
     */
    private function matches(string $endpoint, Request $request): bool
    {
        $path = $this->path($request);

        if ($path === null) {
            return false;
        }

        $method = null;

        if (preg_match('/^(GET|POST|PUT|PATCH|DELETE)\s+(.*)$/i', trim($endpoint), $parts) === 1) {
            [, $method, $endpoint] = $parts;
        }

        return ($method === null || strtoupper($method) === $request->method())
            && Str::is(trim($endpoint, " \n\r\t\v\0/"), $path);
    }

    /**
     * The request path relative to the Billingo base URL without the query string, or null for other hosts.
     */
    private function path(Request $request): ?string
    {
        $baseUrl = rtrim($this->baseUrl, '/').'/';
        $url = Str::before($request->url(), '?');

        return str_starts_with($url, $baseUrl) ? trim(substr($url, strlen($baseUrl)), '/') : null;
    }

    private function describe(string|Closure $endpoint): string
    {
        return $endpoint instanceof Closure ? 'callback' : $endpoint;
    }
}
