<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'backend' => env('BACKEND_GOOGLE_KEY'),
        'maps' => env('MAPS_API_KEY'),
        'analytics' => env('ANALYTICS_ID'),
        'gemini_key' => env('GEMINI_API_KEY'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'webhook_secret' => env('GOOGLE_WEBHOOK_SECRET'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'stripe_platform' => [
        'key' => env('STRIPE_PLATFORM_KEY'),
        'secret' => env('STRIPE_PLATFORM_SECRET'),
        'webhook_secret' => env('STRIPE_PLATFORM_WEBHOOK_SECRET'),
        'price_monthly' => env('STRIPE_PRICE_MONTHLY'),
        'price_yearly' => env('STRIPE_PRICE_YEARLY'),
        'enterprise_price_monthly' => env('STRIPE_ENTERPRISE_PRICE_MONTHLY'),
        'enterprise_price_yearly' => env('STRIPE_ENTERPRISE_PRICE_YEARLY'),
    ],

    'invoiceninja' => [
        'api_key' => env('INVOICENINJA_API_KEY'),
    ],

    'capturekit' => [
        'key' => env('CAPTURE_KIT_ACCESS_KEY'),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

];
