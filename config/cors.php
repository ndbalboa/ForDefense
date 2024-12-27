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

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Define routes that should allow CORS

    'allowed_methods' => ['*'], // Allow all HTTP methods, e.g., GET, POST, PUT, DELETE

    'allowed_origins' => ['http://127.0.0.1:8000'], // Restrict to the specific frontend origin

    'allowed_origins_patterns' => [], // Use this for wildcard patterns if needed

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [], // Headers exposed to the client

    'max_age' => 0, // Cache duration for preflight requests (0 means no cache)

    'supports_credentials' => false, // Set true if using cookies/auth tokens in requests
];
