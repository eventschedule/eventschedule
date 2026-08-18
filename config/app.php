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

    // The currency this install quotes ITS OWN prices in - plan amounts on the marketing
    // site and the upgrade prompts, and the fallback default for a new event. A super-admin
    // can override it at /admin/settings; this only supplies the starting value.
    //
    // ?: not a second arg: .env.example ships keys present-but-empty and env() returns ''
    // for that, so a default argument would never fire and the symbol would come out blank.
    'platform_currency' => env('PLATFORM_CURRENCY') ?: 'USD',

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
    | Admin Portal Palettes
    |--------------------------------------------------------------------------
    |
    | The six --ap-* palettes, ported from the Flutter client's InTheme design
    | system: [name, brightness, page ground, card surface]. The last two are the
    | two-tone swatch shown in the picker, not the palette itself.
    |
    | This is the source for the NAMES: both pickers that offer them (the sidebar
    | footer's theme popover and the Appearance tab in profile settings) and the
    | allow-list the pre-paint script validates a stored choice against
    | (partials/theme-script.blade.php) all read it, so none of them can drift.
    |
    | What is NOT derived from here, and must be added by hand for a new palette:
    | the data-theme block in resources/css/app.css that defines its tokens, the
    | fallback copy in resources/css/marketing-app.css, and the variant_<name>
    | key in every language file under resources/lang.
    |
    */

    'ap_palettes' => [
        ['sand', 'light', '#F6F4EF', '#FFFFFF'],
        ['mist', 'light', '#ECEEF2', '#FFFFFF'],
        ['paper', 'light', '#FFFFFF', '#FAFAF9'],
        ['espresso', 'dark', '#15140F', '#1F1E18'],
        ['midnight', 'dark', '#0F1115', '#181B21'],
        ['carbon', 'dark', '#000000', '#0E0E0E'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme mode glyphs
    |--------------------------------------------------------------------------
    |
    | Sun / moon / monitor, as bare SVG path data on a 24x24 viewBox. Two views
    | draw these and they must not drift: components/theme-picker.blade.php puts
    | one above each mode's label, and layouts/sidebar-footer.blade.php ships all
    | three inside its popover trigger with two hidden, so the trigger's glyph can
    | track the active mode without building SVG in JS.
    |
    | The keys are the mode values themselves - they are what lands in the
    | `theme` localStorage key and what setTheme() is called with.
    |
    */

    'ap_theme_mode_icons' => [
        'light' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
        'dark' => 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z',
        'system' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
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

    /*
    |--------------------------------------------------------------------------
    | Maximum Tickets Per Order
    |--------------------------------------------------------------------------
    |
    | How long the guest quantity dropdown gets when a ticket has no "Max Per
    | Order" of its own. A convenience bound on the picker, not a purchase
    | limit: checkout only enforces the ticket's own max_per_order and its
    | remaining stock. Read by Ticket::toData() and by the guest ticket form.
    |
    | ?: rather than a second argument, and clamped: env() returns '' for a key
    | that is present but empty, so a default argument never fires and (int) ''
    | would be 0 - which reads through to "Sold Out" on every ticket of every
    | event. The upper bound keeps a fat-fingered value from rendering tens of
    | thousands of <option> nodes per row.
    |
    */

    'max_tickets_per_order' => min(100, max(1, (int) (env('MAX_TICKETS_PER_ORDER') ?: 20))),

];
