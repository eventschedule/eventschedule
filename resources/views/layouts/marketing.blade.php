<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(request()->get('lang'), ['he', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Event Schedule - The simple way to share your event schedule' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">

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
        @include('layouts.sentry')
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
        // Marketing pages are English-only for SEO. The page bodies are not translated,
        // so we canonicalize every ?lang= variant onto the clean English URL and do not
        // emit hreflang language alternates. The ?lang= switcher still works for users.
        $path = request()->path();
        $basePath = $path === '/' ? config('app.url') : config('app.url') . '/' . ltrim(rtrim($path, '/'), '/');
    @endphp
    <link rel="canonical" href="{{ $canonical ?? $basePath }}">
    <meta name="description" content="{{ $description ?? 'The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.' }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
    <meta name="author" content="Event Schedule">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonical ?? $basePath }}">
    <meta property="og:title" content="{{ $title ?? 'Event Schedule' }}">
    <meta property="og:description" content="{{ $description ?? 'The simple and free way to share your event schedule' }}">
    @php
        if (isset($socialImage) && str_starts_with($socialImage, 'http')) {
            $ogImage = $socialImage;
        } else {
            $pathSlug = trim(request()->path(), '/') ?: 'home';
            $pathSlug = str_replace('/', '-', $pathSlug);
            // Fall back from an exact page image to a section image (e.g. docs-getting-started -> docs), then home.
            $section = explode('-', $pathSlug)[0];
            // JPEG first: that is what app:generate-social-images writes now, because WhatsApp
            // silently renders no preview at all for an image over roughly 300 KB and the PNG
            // captures were 460 KB. The .png files are the pre-JPEG generation, still on disk for
            // links already shared, and still the answer for any slug that has no .jpg yet.
            $ogImagePath = function (string $slug): ?string {
                foreach (['jpg', 'png'] as $extension) {
                    if (file_exists(public_path("images/social/{$slug}.{$extension}"))) {
                        return config('app.url') . "/images/social/{$slug}.{$extension}";
                    }
                }

                return null;
            };
            $ogImage = $ogImagePath($pathSlug)
                ?? ($section !== $pathSlug ? $ogImagePath($section) : null)
                ?? $ogImagePath('home')
                ?? config('app.url') . '/images/social/home.jpg';
        }

        // $socialImage can be a page's own file (a blog post's featured image), so read the type
        // off the URL rather than assuming whatever the generator currently writes.
        $ogImageType = match (strtolower(pathinfo((string) parse_url($ogImage, PHP_URL_PATH), PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/png',
        };
    @endphp
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:type" content="{{ $ogImageType }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $title ?? 'Event Schedule' }}">
    <meta property="og:site_name" content="Event Schedule">
    @php
        $ogLocaleMap = [
            'en' => 'en_US', 'es' => 'es_ES', 'de' => 'de_DE',
            'fr' => 'fr_FR', 'it' => 'it_IT', 'pt' => 'pt_PT',
            'he' => 'he_IL', 'nl' => 'nl_NL', 'ar' => 'ar_SA', 'et' => 'et_EE', 'ru' => 'ru_RU', 'ro' => 'ro_RO',
        ];
        $currentOgLocale = $ogLocaleMap[app()->getLocale()] ?? 'en_US';
    @endphp
    <meta property="og:locale" content="{{ $currentOgLocale }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $canonical ?? $basePath }}">
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
        "@id": "{{ config('app.url') }}/#website",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "description": "The simple and free way to share your event schedule. Perfect for musicians, venues, event organizers, and vendors.",
        "publisher": {
            "@id": "{{ config('app.url') }}/#organization"
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "{{ config('app.url') }}/search?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "@id": "{{ config('app.url') }}/#organization",
        "name": "Event Schedule",
        "url": "{{ config('app.url') }}",
        "description": "Event Schedule is an open-source platform for sharing events, selling tickets, and bringing communities together.",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ config('app.url') }}/images/dark_logo.png",
            "width": 712,
            "height": 140
        },
        "sameAs": [
            "https://github.com/eventschedule/eventschedule",
            "https://www.facebook.com/appeventschedule",
            "https://www.instagram.com/eventschedule/",
            "https://youtube.com/@EventSchedule",
            "https://x.com/ScheduleEvent",
            "https://www.linkedin.com/company/eventschedule/"
        ],
        "foundingDate": "2024",
        "contactPoint": {
            "@type": "ContactPoint",
            "email": "support@eventschedule.com",
            "contactType": "customer service"
        }
    }
    </script>
    {{ $structuredData ?? '' }}

    @if (!request()->is('/') && !request()->is(''))
    <!-- BreadcrumbList Schema for subpages -->
    @php
        // A title reaches this as a RENDERED slot, so it is already HTML-escaped and may hold a
        // quote or an entity. Decode it back to plain text and let json_encode do the escaping.
        // A Blade echo tag HTML-escapes but does NOT JSON-escape, so interpolating the title that
        // way put a raw double quote inside a JSON string and invalidated the whole block.
        $crumbName = fn ($value) => trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'));

        // The deepest crumb IS this page, so it has to be the URL the canonical claims rather
        // than whatever host happened to serve the request. $basePath is the config('app.url')
        // form built for the canonical above; blog pages pass their own $canonical because they
        // live on the blog host.
        $selfUrl = trim((string) ($canonical ?? $basePath));
        $pageName = $crumbName($breadcrumbTitle ?? $title ?? 'Page');

        $breadcrumbs = [['name' => 'Home', 'url' => config('app.url')]];
        $path = request()->path();
        $skipBreadcrumb = false;
        if ($path === 'docs') {
            $breadcrumbs[] = ['name' => 'Documentation', 'url' => $selfUrl];
        } elseif (str_starts_with($path, 'docs/')) {
            // Every page under /docs/ is an <x-docs-page> and renders its own
            // visible BreadcrumbList via <x-docs-breadcrumb>; skip the layout's
            // to avoid a duplicate. /docs/selfhost used to be special-cased
            // above this branch and so emitted a second one.
            $skipBreadcrumb = true;
        } elseif (str_starts_with($path, 'features/')) {
            $breadcrumbs[] = ['name' => 'Features', 'url' => url('/features')];
            $breadcrumbs[] = ['name' => $pageName, 'url' => $selfUrl];
        } elseif (str_starts_with($path, 'for-')) {
            $breadcrumbs[] = ['name' => 'Use Cases', 'url' => url('/use-cases')];
            $breadcrumbs[] = ['name' => $pageName, 'url' => $selfUrl];
        } elseif (str_ends_with($path, '-alternative')) {
            $breadcrumbs[] = ['name' => 'Compare', 'url' => url('/compare')];
            $breadcrumbs[] = ['name' => $pageName, 'url' => $selfUrl];
        } elseif (str_ends_with($path, '-replacement')) {
            $breadcrumbs[] = ['name' => 'Replace', 'url' => url('/replace')];
            $breadcrumbs[] = ['name' => $pageName, 'url' => $selfUrl];
        } elseif (str_starts_with($path, 'blog/')) {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => blog_url()];
            $breadcrumbs[] = ['name' => $pageName, 'url' => $selfUrl];
        } elseif ($path === 'blog') {
            $breadcrumbs[] = ['name' => 'Blog', 'url' => $selfUrl];
        } elseif (in_array($path, ['stripe', 'google-calendar', 'caldav', 'invoiceninja'])) {
            $breadcrumbs[] = ['name' => 'Integrations', 'url' => url('/features/integrations')];
            $breadcrumbs[] = ['name' => $pageName, 'url' => $selfUrl];
        } elseif ($path === 'use-cases') {
            $breadcrumbs[] = ['name' => 'Use Cases', 'url' => $selfUrl];
        } elseif ($path === 'features') {
            $breadcrumbs[] = ['name' => 'Features', 'url' => $selfUrl];
        } elseif ($path === 'compare') {
            $breadcrumbs[] = ['name' => 'Compare', 'url' => $selfUrl];
        } elseif ($path === 'replace') {
            $breadcrumbs[] = ['name' => 'Replace', 'url' => $selfUrl];
        } else {
            $breadcrumbs[] = ['name' => $pageName, 'url' => $selfUrl];
        }

        $breadcrumbPayload = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($i, $crumb) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ], array_keys($breadcrumbs), $breadcrumbs),
        ];
    @endphp
    @if (! $skipBreadcrumb)
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! json_encode($breadcrumbPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
    @endif

    @include('partials.google-analytics')

    {{ $preload ?? '' }}

    @vite(array_merge([
        'resources/css/marketing-app.css',
        'resources/css/marketing.css',
        'resources/js/marketing.js',
    ], ($docs ?? false) ? [
        'resources/css/docs.css',
        'resources/js/docs.js',
    ] : []))

    @if ($docs ?? false)
        {{-- Motion gate. Hidden pre-reveal states only apply when this class is
             present, so no-JS visitors, crawlers and reduced-motion users always
             see everything. Skipped on anchor arrival: docs are routinely entered
             mid-page from search, the AP Help button and release notes, and a hero
             animating in above an already-scrolled viewport just reads as jank.

             Deliberately gated on $docs rather than hoisted to every marketing
             page - marketing.css hides [data-reveal] content behind html.es-anim,
             and a page that never loads the reveal observer would hide it forever. --}}
        <script {!! nonce_attr() !!}>
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && !window.location.hash) {
                document.documentElement.classList.add('es-anim');
            }
        </script>
    @endif

    @include('partials.theme-script', ['variants' => false])


    @if(config('app.hosted') && !config('app.is_nexus'))
    <link rel="alternate" type="application/rss+xml" title="Event Schedule Blog" href="https://blog.eventschedule.com/feed">
    @else
    <link rel="alternate" type="application/rss+xml" title="Event Schedule Blog" href="{{ route('blog.feed') }}">
    @endif

    {{ $headMeta ?? '' }}

</head>
<body class="font-sans antialiased bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-blue-600 focus:px-4 focus:py-2 focus:text-white ltr:focus:left-4 rtl:focus:right-4">
        {{ __('accessibility.skip_to_main') }}
    </a>

    @include('marketing.partials.header')

    <main id="main-content">
        {{ $slot }}
    </main>

    @include('marketing.partials.footer')

    @include('partials.cookie-banner')

    @guest
        {{-- Anonymous marketing HTML is cached at the edge (see docs/CACHING.md and
             App\Http\Middleware\CacheableMarketingResponse), so the origin never sees most
             page views and the two things the server used to do per visit have to happen in
             the browser instead: count the view, and remember where the visitor came from.

             Both are gated on @guest: a signed-in visitor is not a funnel prospect and needs
             no signup attribution. --}}

        @php($esVisitRoute = \App\Http\Middleware\TrackMarketingVisit::isCountableRouteName(\Illuminate\Support\Facades\Route::currentRouteName()) ? \Illuminate\Support\Facades\Route::currentRouteName() : null)

        @if ($esVisitRoute)
            {{-- Page-view beacon. Flagging the request here is what makes TrackMarketingVisit
                 stand down, so exactly one of the two counts any given view. --}}
            @php(request()->attributes->set(\App\Http\Middleware\TrackMarketingVisit::BEACON_ATTRIBUTE, true))
            <script {!! nonce_attr() !!}>
                (function () {
                    var url = @json(url('/marketing/visit'));
                    var body = JSON.stringify({ route: @json($esVisitRoute) });
                    try {
                        if (navigator.sendBeacon && navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }))) {
                            return;
                        }
                    } catch (e) {}
                    try {
                        fetch(url, {
                            method: 'POST',
                            keepalive: true,
                            credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/json' },
                            body: body
                        }).catch(function () {});
                    } catch (e) {}
                })();
            </script>
        @endif

        {{-- First-touch attribution. This replaces exactly what the server session used to
             hold for the marketing-to-signup hop (landing page, off-site referrer, ?utm_* and
             ?ref=), so it is strictly necessary in the same sense the session cookie it stands
             in for was, and is deliberately NOT gated on cookie consent. The consented 30-day
             marketing cookies CaptureUtmParameters writes are unchanged and still gated.

             Written by the browser, so it never appears in a server response and cannot make
             a page uncacheable. Session-scoped (no expiry) and first-touch (never overwritten
             while it exists). Read back at sign-up by CaptureUtmParameters::clientAttribution(). --}}
        <script {!! nonce_attr() !!}>
            (function () {
                try {
                    if (/(?:^|;\s*)es_attribution=/.test(document.cookie)) {
                        return;
                    }

                    var data = { landing: location.pathname.replace(/^\/+/, '') || '/' };

                    if (document.referrer) {
                        var a = document.createElement('a');
                        a.href = document.referrer;
                        var base = function (h) { return h.toLowerCase().split('.').slice(-2).join('.'); };
                        if (a.hostname && base(a.hostname) !== base(location.hostname)) {
                            // 512, not 2048: encodeURIComponent roughly triples the URL
                            // characters in a referrer, so an untrimmed one on its own could
                            // push the whole cookie past the limit below and lose the landing
                            // page and the utm_* values with it.
                            data.referrer = document.referrer.slice(0, 512);
                        }
                    }

                    var params = new URLSearchParams(location.search);
                    ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'ref'].forEach(function (key) {
                        var value = params.get(key);
                        if (value) {
                            data[key] = value.slice(0, 255);
                        }
                    });

                    var value = encodeURIComponent(JSON.stringify(data));
                    if (value.length > 2048 && data.referrer) {
                        // The referrer is the only unbounded field left, and the least
                        // valuable: drop it rather than the landing page and the campaign.
                        delete data.referrer;
                        value = encodeURIComponent(JSON.stringify(data));
                    }
                    if (value.length > 2048) {
                        return;
                    }

                    var domain = @json(config('session.domain'));
                    document.cookie = 'es_attribution=' + value
                        + '; path=/'
                        + (domain ? '; domain=' + domain : '')
                        + '; samesite=lax'
                        + (location.protocol === 'https:' ? '; secure' : '');
                } catch (e) {}
            })();
        </script>
    @endguest

    {{-- @if (config('app.is_testing'))
        <style {!! nonce_attr() !!}>
            @keyframes es-ping { 0% { transform: scale(1); opacity: 0.5; } 100% { transform: scale(3); opacity: 0; } }
        </style>
        <div class="fixed bottom-4 right-4 z-50 pointer-events-none" style="width:24px;height:24px">
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316;animation:es-ping 3s cubic-bezier(0,0,0.2,1) infinite"></div>
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316;animation:es-ping 3s cubic-bezier(0,0,0.2,1) 1.5s infinite"></div>
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316"></div>
        </div>
    @endif --}}

</body>
</html>
