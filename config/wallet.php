<?php

return [
    'apple' => [
        'enabled' => env('APPLE_WALLET_ENABLED', false),
        'certificate_path' => env('APPLE_WALLET_CERTIFICATE_PATH'),
        'certificate_password' => env('APPLE_WALLET_CERTIFICATE_PASSWORD'),
        'wwdr_certificate_path' => env('APPLE_WALLET_WWDR_CERTIFICATE_PATH'),
        'pass_type_identifier' => env('APPLE_WALLET_PASS_TYPE_IDENTIFIER'),
        'team_identifier' => env('APPLE_WALLET_TEAM_IDENTIFIER'),
        'organization_name' => env('APPLE_WALLET_ORGANIZATION_NAME', config('app.name')),
        'background_color' => env('APPLE_WALLET_BACKGROUND_COLOR', 'rgb(78,129,250)'),
        'foreground_color' => env('APPLE_WALLET_FOREGROUND_COLOR', 'rgb(255,255,255)'),
        'label_color' => env('APPLE_WALLET_LABEL_COLOR', 'rgb(255,255,255)'),
        'debug' => env('APPLE_WALLET_DEBUG', env('APP_DEBUG', false)),
        'log_channel' => env('APPLE_WALLET_LOG_CHANNEL'),
        'debug_dump_path' => env('APPLE_WALLET_DEBUG_DUMP_PATH', storage_path('app/wallet/debug-dumps')),
    ],

    'google' => [
        'enabled' => env('GOOGLE_WALLET_ENABLED', false),
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID'),
        'issuer_name' => env('GOOGLE_WALLET_ISSUER_NAME', config('app.name')),
        'class_suffix' => env('GOOGLE_WALLET_CLASS_SUFFIX', 'event'),
        'service_account_json' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_JSON'),
        'service_account_json_path' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_JSON_PATH'),
    ],
];
