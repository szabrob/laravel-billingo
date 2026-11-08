<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Billingo API Key
    |--------------------------------------------------------------------------
    |
    | The API v3 key generated at https://app.billingo.hu/api-key. It can be
    | overridden at runtime with Billingo::withApiKey().
    |
    */

    'api_key' => env('BILLINGO_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    */

    'base_url' => env('BILLINGO_BASE_URL', 'https://api.billingo.hu/v3'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum number of seconds to wait for a Billingo API response.
    |
    */

    'timeout' => (int) env('BILLINGO_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Retries
    |--------------------------------------------------------------------------
    |
    | When Billingo responds with HTTP 429, the request is retried after the
    | number of seconds given in the Retry-After header. Retries are skipped
    | when Billingo asks to wait longer than "max_delay" seconds, so web
    | requests are not blocked for too long. Set "retries" to 0 to disable.
    |
    */

    'rate_limit' => [
        'retries' => (int) env('BILLINGO_RATE_LIMIT_RETRIES', 2),
        'max_delay' => (int) env('BILLINGO_RATE_LIMIT_MAX_DELAY', 10),
    ],

];
