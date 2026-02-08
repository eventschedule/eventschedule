<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],

    'allowed_origins' => [env('APP_URL')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-API-Key', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
