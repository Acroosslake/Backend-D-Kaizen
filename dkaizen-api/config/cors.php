<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],

    'allowed_methods' => ['*'],

    // ✅ Leemos la URL del Front directamente de tu .env para que no falle
    'allowed_origins' => [
        'https://fuzzy-space-halibut-jjxvp95q665rfpwqj-5173.app.github.dev',
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ✅ Obligatorio para Sanctum / JWT
    'supports_credentials' => true, 
];