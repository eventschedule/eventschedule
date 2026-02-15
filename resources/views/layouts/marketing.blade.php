<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(request()->get('lang'), ['he', 'ar']) ? 'rtl' : 'ltr' }}">
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
    <script {!! nonce_attr() !!}>
        window.addEventListener('load', function() {
            var s = document.createElement('script');
            s.src = "{{ config('app.sentry_js_dsn') }}";
            s.crossOrigin = 'anonymous';
            document.head.appendChild(s);
        });
    </script>
    @endif

    <!-- Theme color -->
    <meta name="theme-color" content="#4E81FA" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0a0a0f" media="(prefers-color-scheme: dark)">

    <!-- SEO Meta Tags -->
    @php
        $path = request()->path();
        $basePath = $path === '/' ? config('app.url') : config('app.url') . '/' . ltrim(rtrim($path, '/'), '/');
        $canonicalPath = $basePath;
        $langParam = request()->get('lang');
        $validLangs = ['en', 'es', 'de', 'fr', 'it', 'pt', 'he', 'nl', 'ar', 'et', 'ru'];
        if ($langParam && in_array($langParam, $validLangs)) {
            $canonicalPath = $basePath . '?lang=' . $langParam;
        }
    @endphp
    <link rel="canonical" href="{{ $canonicalPath }}">
    <!-- Hreflang tags for all supported languages -->
    <link rel="alternate" hreflang="x-default" href="{{ $basePath }}">
    <link rel="alternate" hreflang="en" href="{{ $basePath }}?lang=en">
    <link rel="alternate" hreflang="es" href="{{ $basePath }}?lang=es">
    <link rel="alternate" hreflang="de" href="{{ $basePath }}?lang=de">
    <link rel="alternate" hreflang="fr" href="{{ $basePath }}?lang=fr">
    <link rel="alternate" hreflang="it" href="{{ $basePath }}?lang=it">
    <link rel="alternate" hreflang="pt" href="{{ $basePath }}?lang=pt">
    <link rel="alternate" hreflang="he" href="{{ $basePath }}?lang=he">
    <link rel="alternate" hreflang="nl" href="{{ $basePath }}?lang=nl">
    <link rel="alternate" hreflang="ar" href="{{ $basePath }}?lang=ar">
    <link rel="alternate" hreflang="et" href="{{ $basePath }}?lang=et">
    <link rel="alternate" hreflang="ru" href="{{ $basePath }}?lang=ru">
    <meta name="description" content="{{ $description ?? 'The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.' }}">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="author" content="Event Schedule">
    <meta name="keywords" content="{{ $keywords ?? 'event schedule, event calendar, ticketing, QR check-in, event management, venues, performers, sell tickets' }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonicalPath }}">
    <meta property="og:title" content="{{ $title ?? 'Event Schedule' }}">
    <meta property="og:description" content="{{ $description ?? 'The simple and free way to share your event schedule' }}">
    @php
        $ogImage = isset($socialImage)
            ? (str_starts_with($socialImage, 'http') ? $socialImage : config('app.url') . '/images/' . $socialImage)
            : config('app.url') . '/images/social/home.png';
    @endphp
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $title ?? 'Event Schedule' }}">
    <meta property="og:site_name" content="Event Schedule">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
    @php
        $ogLocaleMap = [
            'en' => 'en_US', 'es' => 'es_ES', 'de' => 'de_DE',
            'fr' => 'fr_FR', 'it' => 'it_IT', 'pt' => 'pt_PT',
            'he' => 'he_IL', 'nl' => 'nl_NL', 'ar' => 'ar_SA', 'et' => 'et_EE', 'ru' => 'ru_RU',
        ];
        $currentOgLocale = str_replace('-', '_', app()->getLocale());
    @endphp
    @foreach($ogLocaleMap as $lang => $ogLocale)
        @if($ogLocale !== $currentOgLocale)
    <meta property="og:locale:alternate" content="{{ $ogLocale }}">
        @endif
    @endforeach

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $canonicalPath }}">
    <meta name="twitter:title" content="{{ $title ?? 'Event Schedule' }}">
    <meta name="twitter:description" content="{{ $description ?? 'The simple and free way to share your event schedule' }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="twitter:image:alt" content="{{ $title ?? 'Event Schedule' }}">
    <meta name="twitter:site" content="@ScheduleEvent">

    <!-- Structured Data -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "description": "{{ $description ?? 'The simple and free way to share your event schedule' }}",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "{{ config('app.url') }}/?search={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
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
        } elseif (str_starts_with($path, 'features/')) {
            $breadcrumbs[] = ['name' => 'Features', 'url' => url('/features')];
            $breadcrumbs[] = ['name' => $breadcrumbTitle ?? $title ?? 'Page', 'url' => url()->current()];
        } elseif (str_starts_with($path, 'for-')) {
            $breadcrumbs[] = ['name' => 'Use Cases', 'url' => url('/use-cases')];
            $breadcrumbs[] = ['name' => $breadcrumbTitle ?? $title ?? 'Page', 'url' => url()->current()];
        } elseif (str_ends_with($path, '-alternative')) {
            $breadcrumbs[] = ['name' => 'Compare', 'url' => url('/compare')];
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
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}" {!! nonce_attr() !!}></script>
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

    {{ $preload ?? '' }}

    @vite([
        'resources/css/marketing-app.css',
        'resources/css/marketing.css',
        'resources/js/marketing.js',
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


    {{ $headMeta ?? '' }}

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
