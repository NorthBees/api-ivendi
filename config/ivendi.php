<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Partner API key
    |--------------------------------------------------------------------------
    |
    | Issued by iVendi at partner onboarding and sent as the x-api-key header.
    | It identifies the partner (you), not the retailer.
    |
    */

    'api_key' => env('IVENDI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default retailer
    |--------------------------------------------------------------------------
    |
    | The quoteeId that identifies the retailer being quoted for. Usually set
    | per tenant via Ivendi::withQuotee() instead.
    |
    */

    'quotee_id' => env('IVENDI_QUOTEE_ID'),

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | The Connect API base URL is supplied by iVendi at onboarding.
    |
    */

    'base_url' => env('IVENDI_BASE_URL'),

    'accept_language' => env('IVENDI_ACCEPT_LANGUAGE', 'en-GB'),

    /*
    |--------------------------------------------------------------------------
    | Transport
    |--------------------------------------------------------------------------
    |
    | Retries only apply to connection failures and 429/502/503/504 responses.
    |
    */

    'timeout' => (int) env('IVENDI_TIMEOUT', 15),

    'connect_timeout' => (int) env('IVENDI_CONNECT_TIMEOUT', 5),

    'retry' => [
        'times' => (int) env('IVENDI_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('IVENDI_RETRY_SLEEP_MS', 250),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, each call logs its endpoint, status and duration. Request
    | bodies and API keys are never logged.
    |
    */

    'logging' => [
        'enabled' => (bool) env('IVENDI_LOG', false),
        'channel' => env('IVENDI_LOG_CHANNEL'),
    ],

];
