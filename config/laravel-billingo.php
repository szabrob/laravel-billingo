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

];
