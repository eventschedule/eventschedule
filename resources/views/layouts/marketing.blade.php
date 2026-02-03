<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head class="h-full">
    <title>{{ $title ?? 'Event Schedule - The simple way to share your event schedule' }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">

    <!-- Preconnect to external resources -->
    @if (config('services.google.analytics'))
    <link rel="preconnect" href="https://www.googletagmanager.com">
    @endif

    <!-- DNS prefetch fallback for browsers that don't support preconnect -->
    @if (config('services.google.analytics'))
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    @endif
    @if (config('app.hosted') || config('app.report_errors'))
    <link rel="dns-prefetch" href="https://browser.sentry-cdn.com">
    @endif

    @if (config('app.hosted') || config('app.report_errors'))
        <script src="{{ config('app.sentry_js_dsn') }}" crossorigin="anonymous" defer></script>
    @endif

    <!-- Theme color -->
    <meta name="theme-color" content="#4E81FA" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0a0a0f" media="(prefers-color-scheme: dark)">

    <!-- SEO Meta Tags -->
    @php $canonicalPath = config('app.url') . '/' . ltrim(rtrim(request()->path(), '/'), '/'); @endphp
    <link rel="canonical" href="{{ $canonicalPath }}">
    <!-- Hreflang tags for all supported languages -->
    <link rel="alternate" hreflang="x-default" href="{{ $canonicalPath }}">
    <link rel="alternate" hreflang="en" href="{{ $canonicalPath }}?lang=en">
    <link rel="alternate" hreflang="es" href="{{ $canonicalPath }}?lang=es">
    <link rel="alternate" hreflang="de" href="{{ $canonicalPath }}?lang=de">
    <link rel="alternate" hreflang="fr" href="{{ $canonicalPath }}?lang=fr">
    <link rel="alternate" hreflang="it" href="{{ $canonicalPath }}?lang=it">
    <link rel="alternate" hreflang="pt" href="{{ $canonicalPath }}?lang=pt">
    <link rel="alternate" hreflang="he" href="{{ $canonicalPath }}?lang=he">
    <link rel="alternate" hreflang="nl" href="{{ $canonicalPath }}?lang=nl">
    <link rel="alternate" hreflang="ar" href="{{ $canonicalPath }}?lang=ar">
    <meta name="description" content="{{ $description ?? 'The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.' }}">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Event Schedule">
    <meta name="keywords" content="{{ $keywords ?? 'event schedule, event calendar, ticketing, QR check-in, event management, venues, performers, sell tickets' }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
    <meta property="og:title" content="{{ $title ?? 'Event Schedule' }}">
    <meta property="og:description" content="{{ $description ?? 'The simple and free way to share your event schedule' }}">
    <meta property="og:image" content="{{ config('app.url') }}/images/{{ $socialImage ?? 'social/home.png' }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $title ?? 'Event Schedule' }}">
    <meta property="og:site_name" content="Event Schedule">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
    <meta name="twitter:title" content="{{ $title ?? 'Event Schedule' }}">
    <meta name="twitter:description" content="{{ $description ?? 'The simple and free way to share your event schedule' }}">
    <meta name="twitter:image" content="{{ config('app.url') }}/images/{{ $socialImage ?? 'social/home.png' }}">
    <meta name="twitter:image:alt" content="Event Schedule">
    <meta name="twitter:site" content="@ScheduleEvent">

    <!-- Structured Data -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "description": "{{ $description ?? 'The simple and free way to share your event schedule' }}"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "logo": "{{ config('app.url') }}/images/dark_logo.png",
        "sameAs": [
            "https://github.com/eventschedule/eventschedule",
            "https://www.facebook.com/appeventschedule",
            "https://www.instagram.com/eventschedule/",
            "https://youtube.com/@EventSchedule",
            "https://x.com/ScheduleEvent",
            "https://www.linkedin.com/company/eventschedule/"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "email": "support@eventschedule.com",
            "contactType": "customer service"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "description": "The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "screenshot": "{{ config('app.url') }}/images/social/home.png",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        }
    }
    </script>

    {{ $structuredData ?? '' }}

    @if (!request()->is('/') && !request()->is(''))
    <!-- BreadcrumbList Schema for subpages -->
    @php
        $breadcrumbs = [['name' => 'Home', 'url' => config('app.url')]];
        $path = request()->path();
        if (str_starts_with($path, 'docs/selfhost/')) {
            $breadcrumbs[] = ['name' => 'Documentation', 'url' => url('/docs')];
            $breadcrumbs[] = ['name' => 'Selfhost', 'url' => url('/docs/selfhost')];
            $breadcrumbs[] = ['name' => $breadcrumbTitle ?? $title ?? 'Page', 'url' => url()->current()];
        } elseif ($path === 'docs/selfhost') {
            $breadcrumbs[] = ['name' => 'Documentation', 'url' => url('/docs')];
            $breadcrumbs[] = ['name' => 'Selfhost', 'url' => url()->current()];
        } elseif (str_starts_with($path, 'docs/developer/')) {
            $breadcrumbs[] = ['name' => 'Documentation', 'url' => url('/docs')];
            $breadcrumbs[] = ['name' => $breadcrumbTitle ?? $title ?? 'Page', 'url' => url()->current()];
        } elseif (str_starts_with($path, 'docs/') || $path === 'docs') {
            if ($path !== 'docs') {
                $breadcrumbs[] = ['name' => 'Documentation', 'url' => url('/docs')];
            }
            $breadcrumbs[] = ['name' => $breadcrumbTitle ?? $title ?? 'Page', 'url' => url()->current()];
        } else {
            $breadcrumbs[] = ['name' => $breadcrumbTitle ?? $title ?? 'Page', 'url' => url()->current()];
        }
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            @foreach ($breadcrumbs as $i => $crumb)
            @if ($i > 0),@endif
            {
                "@type": "ListItem",
                "position": {{ $i + 1 }},
                "name": "{{ $crumb['name'] }}",
                "item": "{{ $crumb['url'] }}"
            }
            @endforeach
        ]
    }
    </script>
    @endif

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
        'resources/css/marketing.css',
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


</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-blue-600 focus:text-white focus:rounded-lg">
        Skip to main content
    </a>

    @include('marketing.partials.header')

    <main id="main-content">
        {{ $slot }}
    </main>

    @include('marketing.partials.footer')

</body>
</html>
