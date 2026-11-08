<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Szabrob\LaravelBillingo\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * @return array<string, mixed>
 */
function billingoFixture(string $name): array
{
    /** @var array<string, mixed> */
    return json_decode((string) file_get_contents(__DIR__."/Fixtures/{$name}.json"), true, flags: JSON_THROW_ON_ERROR);
}

/**
 * @param  array<string, mixed>|null  $body
 */
function assertBillingoRequest(string $method, string $url, ?array $body = null): void
{
    Http::assertSent(function (Request $request) use ($method, $url, $body): bool {
        return $request->method() === $method
            && $request->url() === 'https://api.billingo.hu/v3/'.$url
            && ($body === null || $request->data() === $body);
    });
}
