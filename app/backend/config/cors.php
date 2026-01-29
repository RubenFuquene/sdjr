<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ],

    'allowed_origins' => array_filter(array_merge(
        env('CORS_ALLOWED_ORIGINS') ? explode(',', env('CORS_ALLOWED_ORIGINS')) : [],
        env('APP_ENV') === 'local' ? ['http://localhost:3000', 'http://127.0.0.1:3000'] : []
    )),

    'allowed_origins_patterns' => env('APP_ENV') === 'local' ? [
        '#^http://localhost:\d+$#',
        '#^http://127\.0\.0\.1:\d+$#',
        '#^https://.*\.vercel\.app$#',
    ] : [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-Socket-Id',
        'Origin',
        'Cache-Control',
    ],

    'exposed_headers' => [
        'X-Total-Count',
        'X-Rate-Limit-Remaining',
        'X-Rate-Limit-Limit',
    ],

    'max_age' => (int) env('CORS_MAX_AGE', 86400),

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', true),
];
