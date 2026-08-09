<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => 'Event Schedule',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),
    'cron_secret' => env('APP_CRON_SECRET', ''),
    'sentry_js_dsn' => env('SENTRY_JS_DSN', 'https://js.sentry-cdn.com/e40010dda2802390fc7a031a3db09b63.min.js'),

    'hosted' => (bool) env('IS_HOSTED', false),
    // Selfhost only: lift the single-user restriction so visitors can create accounts.
    // Hosted installs always allow registration. See public_registration_enabled().
    'allow_registration' => (bool) env('ALLOW_REGISTRATION', false),
    'report_errors' => (bool) env('REPORT_ERRORS', false),
    'is_testing' => (bool) env('APP_TESTING', false),

    // Force the cookie consent banner on even when nothing else needs it. The banner
    // normally appears only when a consent-gated feature is configured (Google Analytics,
    // ADS_ENABLED, STAY22_ENABLED) - see consent_required(). Turn this on if you want the
    // 30-day UTM attribution cookies, which are written only after a visitor accepts.
    // Leaving it off means a bare install shows no banner and sets no attribution cookies.
    'cookie_consent_banner' => (bool) env('COOKIE_CONSENT_BANNER', false),

    // URLs per child sitemap file. The sitemaps.org limit is 50,000; the default leaves plenty of
    // headroom, including for a schedules page where each schedule also emits its sub-schedules.
    // Tests lower this to exercise pagination without seeding thousands of rows.
    'sitemap_urls_per_file' => (int) env('SITEMAP_URLS_PER_FILE', 10000),

    'is_nexus' => (bool) env('IS_NEXUS', false),
    // The upstream nexus app: receives shared translation suggestions AND federated
    // events. Separate from marketing_url, which operators may point at their own site -
    // anything that must reach the real upstream belongs here, not there.
    'nexus_url' => env('NEXUS_URL', 'https://eventschedule.com'),
    // Where published translation-override files live. Env-overridable so tests
    // never write into storage/app/lang of the working checkout. A relative env
    // value resolves under the app base path; an absolute value is used as-is
    // (e.g. a shared volume on a multi-server deploy).
    'lang_overrides_path' => env('LANG_OVERRIDES_PATH')
        ? (str_starts_with(env('LANG_OVERRIDES_PATH'), DIRECTORY_SEPARATOR) ? env('LANG_OVERRIDES_PATH') : base_path(env('LANG_OVERRIDES_PATH')))
        : storage_path('app/lang'),
    'logo_dark' => env('APP_LOGO_DARK', '/images/dark_logo.png'),
    'logo_light' => env('APP_LOGO_LIGHT', '/images/light_logo.png'),
    'marketing_url' => env('APP_MARKETING_URL', 'https://eventschedule.com'),
    'support_email' => env('SUPPORT_EMAIL', 'contact@eventschedule.com'),
    'trial_days' => (int) env('TRIAL_DAYS', 7),
    'search_exclude_country' => env('SEARCH_EXCLUDE_COUNTRY', ''),

    // Custom links shown in the admin sidebar (up to 3). A link only appears when
    // both its title and URL are set. Works in both selfhosted and hosted (SaaS) modes.
    'custom_links' => array_values(array_filter([
        ['title' => env('CUSTOM_LINK_1_TITLE', ''), 'url' => env('CUSTOM_LINK_1_URL', '')],
        ['title' => env('CUSTOM_LINK_2_TITLE', ''), 'url' => env('CUSTOM_LINK_2_URL', '')],
        ['title' => env('CUSTOM_LINK_3_TITLE', ''), 'url' => env('CUSTOM_LINK_3_URL', '')],
    ], fn ($link) => ! empty($link['title']) && ! empty($link['url']))),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    'event_categories' => [
        1 => 'Art & Culture',
        2 => 'Business Networking',
        3 => 'Community',
        4 => 'Concerts',
        5 => 'Education',
        6 => 'Food & Drink',
        7 => 'Health & Fitness',
        8 => 'Parties & Festivals',
        9 => 'Personal Growth',
        10 => 'Sports',
        11 => 'Spirituality',
        12 => 'Tech',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | This array contains all the language codes that are supported by the
    | application. These should correspond to the directories in resources/lang.
    |
    */

    'supported_languages' => [
        'ar' => 'arabic',
        'de' => 'german',
        'en' => 'english',
        'es' => 'spanish',
        'et' => 'estonian',
        'fr' => 'french',
        'he' => 'hebrew',
        'it' => 'italian',
        'nl' => 'dutch',
        'pt' => 'portuguese',
        'ro' => 'romanian',
        'ru' => 'russian',
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximum Sponsors
    |--------------------------------------------------------------------------
    |
    | The number of sponsor/partner logos that can be added to a schedule or to
    | an individual event. Referenced by the server-side caps, the admin editors
    | and the "maximum reached" message, so it only needs changing here.
    |
    */

    'max_sponsors' => 50,

];
