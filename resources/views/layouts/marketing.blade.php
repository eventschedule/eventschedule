<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head class="h-full">
    <title>{{ $title ?? 'Event Schedule - The simple way to share your event schedule' }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    @if (config('app.hosted') || config('app.report_errors'))
        <script src="{{ config('app.sentry_js_dsn') }}" crossorigin="anonymous"></script>
    @endif

    <!-- SEO Meta Tags -->
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="description" content="{{ $description ?? 'The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.' }}">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Event Schedule">
    <meta name="keywords" content="{{ $keywords ?? 'event schedule, event calendar, ticketing, QR check-in, event management, venues, performers, sell tickets' }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
    <meta property="og:title" content="{{ $title ?? 'Event Schedule' }}">
    <meta property="og:description" content="{{ $description ?? 'The simple and free way to share your event schedule' }}">
    <meta property="og:image" content="{{ config('app.url') }}/images/background.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Event Schedule">
    <meta property="og:locale" content="en_US">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
    <meta name="twitter:title" content="{{ $title ?? 'Event Schedule' }}">
    <meta name="twitter:description" content="{{ $description ?? 'The simple and free way to share your event schedule' }}">
    <meta name="twitter:image" content="{{ config('app.url') }}/images/background.jpg">
    <meta name="twitter:image:alt" content="Event Schedule">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "description": "{{ $description ?? 'The simple and free way to share your event schedule' }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ config('app.url') }}/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "logo": "{{ config('app.url') }}/images/dark_logo.png",
        "sameAs": [
            "https://github.com/eventschedule/eventschedule"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "email": "support@eventschedule.com",
            "contactType": "customer service"
        }
    }
    </script>

    @if (config('services.google.analytics') && (! auth()->user() || ! auth()->user()->isAdmin()))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script {!! nonce_attr() !!}>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        try {
            dataLayer.push(arguments);
        } catch (e) {
            console.warn('Analytics data could not be cloned:', e);
        }
    }
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics') }}');
    </script>
    @endif

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <script {!! nonce_attr() !!}>
        // Theme Management
        (function() {
            const THEME_STORAGE_KEY = 'theme';
            const THEMES = {
                LIGHT: 'light',
                DARK: 'dark',
                SYSTEM: 'system'
            };

            function getSystemTheme() {
                return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            function applyTheme(theme) {
                const html = document.documentElement;
                const actualTheme = theme === THEMES.SYSTEM ? getSystemTheme() : theme;

                if (actualTheme === 'dark') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            }

            function initTheme() {
                const storedTheme = localStorage.getItem(THEME_STORAGE_KEY);
                const theme = storedTheme || THEMES.SYSTEM;
                applyTheme(theme);

                if (theme === THEMES.SYSTEM) {
                    watchSystemTheme();
                }
            }

            function watchSystemTheme() {
                const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                mediaQuery.addEventListener('change', function(e) {
                    const currentTheme = localStorage.getItem(THEME_STORAGE_KEY);
                    if (currentTheme === THEMES.SYSTEM) {
                        applyTheme(THEMES.SYSTEM);
                    }
                });
            }

            function setThemeInternal(theme) {
                localStorage.setItem(THEME_STORAGE_KEY, theme);
                applyTheme(theme);

                if (theme === THEMES.SYSTEM) {
                    watchSystemTheme();
                }
            }

            initTheme();

            window.setTheme = function(theme) {
                setThemeInternal(theme);
                setTimeout(function() {
                    if (typeof window.updateThemeButtons === 'function') {
                        window.updateThemeButtons();
                    }
                }, 10);
            };
            window.getCurrentTheme = function() {
                return localStorage.getItem(THEME_STORAGE_KEY) || THEMES.SYSTEM;
            };
        })();
    </script>

    <style>
        .gradient-text {
            background: linear-gradient(135deg, #4E81FA 0%, #7C3AED 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-gradient {
            background: linear-gradient(135deg, rgba(78, 129, 250, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
        }
        .dark .hero-gradient {
            background: linear-gradient(135deg, rgba(78, 129, 250, 0.2) 0%, rgba(124, 58, 237, 0.2) 100%);
        }
    </style>

</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    @include('marketing.partials.header')

    <main>
        {{ $slot }}
    </main>

    @include('marketing.partials.footer')

</body>
</html>
