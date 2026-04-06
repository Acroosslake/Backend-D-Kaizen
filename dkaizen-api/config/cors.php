<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],
    
    // ✅ URL EXACTA DE TU FRONTEND (sacada de tu captura)
    'allowed_origins' => [
        'https://congenial-space-lamp-v6q7w9q55464fxxr7-5173.app.github.dev',
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // ✅ Esto debe ser TRUE
];