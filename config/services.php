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
        'price_monthly_amount' => env('STRIPE_PRICE_MONTHLY_AMOUNT', '5'),
        'price_yearly_amount' => env('STRIPE_PRICE_YEARLY_AMOUNT', '50'),
        'enterprise_price_monthly' => env('STRIPE_ENTERPRISE_PRICE_MONTHLY'),
        'enterprise_price_yearly' => env('STRIPE_ENTERPRISE_PRICE_YEARLY'),
        'enterprise_price_monthly_amount' => env('STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT', '15'),
        'enterprise_price_yearly_amount' => env('STRIPE_ENTERPRISE_PRICE_YEARLY_AMOUNT', '150'),
    ],

    'invoiceninja' => [
        'api_key' => env('INVOICENINJA_API_KEY'),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],

    'digitalocean' => [
        'api_token' => env('DO_API_TOKEN'),
        'app_id' => env('DO_APP_ID'),
        'app_hostname' => env('DO_APP_HOSTNAME'),
    ],

    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'access_token' => env('META_ACCESS_TOKEN'),
        'ad_account_id' => env('META_AD_ACCOUNT_ID'),
        'pixel_id' => env('META_PIXEL_ID'),
        'markup_rate' => env('META_MARKUP_RATE', 0.20),
        'min_budget' => env('META_MIN_BUDGET', 10.00),
        'max_budget' => env('META_MAX_BUDGET', 1000.00),
        'boost_default_limit' => env('META_BOOST_DEFAULT_LIMIT', 10.00),
        'default_currency' => env('META_DEFAULT_CURRENCY', 'USD'),
        'api_version' => env('META_API_VERSION', 'v21.0'),
        'webhook_verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
        'max_concurrent_boosts' => env('META_MAX_CONCURRENT_BOOSTS', 3),
        'page_id' => env('META_PAGE_ID'),
    ],

];
