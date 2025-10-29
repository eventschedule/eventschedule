<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
    data-theme="{{ ($forceLight ?? false) ? 'light' : 'system' }}"
    @if ($forceLight ?? false) data-force-theme="light" @endif
>
<head>
    <title>{{ $title ?? 'Event Schedule' }}</title>
    <!-- Version: {{ config('self-update.version_installed') }} -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" href="{{ url('/images/favicon.png') }}">
    <link rel="sitemap" type="application/xml" href="{{ url(route('sitemap', [], false)) }}">
    
    @if (config('app.hosted') || config('app.report_errors'))
        <script src="{{ config('app.sentry_js_dsn') }}" crossorigin="anonymous"></script>
    @endif

    @if (isset($meta))
        {{ $meta }}
    @else
        <link rel="canonical" href="{{ url()->current() }}">
        <meta name="description" content="The simple and free way to share your event schedule">
        <meta property="og:title" content="Event Schedule">
        <meta property="og:description" content="The simple and free way to share your event schedule">
        <meta property="og:image" content="{{ url('/images/background.jpg') }}">
        <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
        <meta property="og:site_name" content="Event Schedule">
        <meta name="twitter:title" content="Event Schedule">
        <meta name="twitter:description" content="The simple and free way to share your event schedule">
        <meta name="twitter:image" content="{{ url('/images/background.jpg') }}">
        <meta name="twitter:image:alt" content="Event Schedule">
        <meta name="twitter:card" content="summary_large_image">
    @endif    

    <script {!! nonce_attr() !!}>
        (() => {
            const storageKey = 'theme';
            const root = document.documentElement;
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const forceLight = @json($forceLight ?? false);

            const getPreference = () => {
                if (forceLight) {
                    return 'light';
                }

                const stored = window.localStorage.getItem(storageKey);
                if (stored === 'light' || stored === 'dark' || stored === 'system') {
                    return stored;
                }

                return 'system';
            };

            const resolvePreference = (preference) => {
                if (forceLight) {
                    return 'light';
                }

                return preference === 'system'
                    ? (mediaQuery.matches ? 'dark' : 'light')
                    : preference;
            };

            const applyPreference = (preference) => {
                const resolved = resolvePreference(preference);

                root.classList.toggle('dark', resolved === 'dark');
                root.dataset.themePreference = forceLight ? 'light' : preference;
                root.dataset.themeResolved = resolved;
                root.style.colorScheme = resolved;
            };

            applyPreference(getPreference());
        })();
    </script>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/toastify-js.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/toastify.min.css') }}">

    @if (config('services.google.analytics') && (! auth()->user() || ! auth()->user()->isAdmin()))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script {!! nonce_attr() !!}>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        try {
            dataLayer.push(arguments);
        } catch (e) {
            // Handle DataCloneError silently
            console.warn('Analytics data could not be cloned:', e);
        }
    }
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics') }}');
    @else
    <script {!! nonce_attr() !!}>
    @endif

    function onPopUpClick(id, event) {
        event.stopPropagation();
        var menu = $('#' + id);
        if (menu.is(':hidden')) {
            menu.show();
            $(document).on('click', hidePopUp);
        } else {
            hidePopUp();
        }
    }

    function hidePopUp() {
        $('.pop-up-menu').hide();
        $(document).off('click', hidePopUp);
    }

    $(document).on('click', '.pop-up-menu', function(event) {
        event.stopPropagation();
    });
    </script>

    @if (config('app.load_vite_assets'))
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
        ])
    @else
        @php($viteAssets = vite_assets([
            'resources/css/app.css',
            'resources/js/app.js',
        ]))

        @foreach ($viteAssets['css'] as $stylesheet)
            <link rel="stylesheet" href="{{ $stylesheet }}">
        @endforeach

        @foreach ($viteAssets['js'] as $script)
            <script type="module" src="{{ $script }}" defer {!! nonce_attr() !!}></script>
        @endforeach
    @endif

    <style>
        .rtl {
            direction: rtl;
            text-align: right;
        }
        
        .rtl select {
            text-align: right;
            direction: rtl;
        }
        
        .rtl select option {
            text-align: right;
            direction: rtl;
        }
        
        /* RTL-specific spacing adjustments */
        .rtl .ml-2 { margin-left: 0; margin-right: 0.5rem; }
        .rtl .ml-4 { margin-left: 0; margin-right: 1rem; }
        .rtl .mr-1\.5 { margin-right: 0; margin-left: 0.375rem; }
        .rtl .mr-3 { margin-right: 0; margin-left: 0.75rem; }
        .rtl .-ml-0\.5 { margin-left: 0; margin-right: -0.125rem; }
        .rtl .-mr-1 { margin-right: 0; margin-left: -0.25rem; }
        .rtl .sm\\:ml-2 { margin-left: 0; margin-right: 0.5rem; }
        .rtl .lg\\:ml-2 { margin-left: 0; margin-right: 0.5rem; }
        .rtl .lg\\:ml-4 { margin-left: 0; margin-right: 1rem; }
        .rtl .xl\\:ml-3\.5 { margin-left: 0; margin-right: 0.875rem; }
        .rtl .xl\\:pl-3\.5 { padding-left: 0; padding-right: 0.875rem; }
        .rtl .xl\\:border-l { border-left: none; border-right: 1px solid; }
        
        /* RTL flex direction adjustments */
        .rtl .space-x-3 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        .rtl .space-x-10 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        
        /* RTL text alignment fixes */
        .rtl .text-left { text-align: right; }
        .rtl .lg\\:text-left { text-align: right; }
        
        /* RTL positioning adjustments */
        .rtl .right-4 { right: auto; left: 1rem; }
        .rtl .absolute.right-0 { right: auto; left: 0; }
        
        /* RTL flex justification adjustments */
        .rtl .justify-start { justify-content: flex-end; }
        .rtl .justify-end { justify-content: flex-start; }
        .rtl .lg\\:justify-start { justify-content: flex-end; }
        .rtl .lg\\:justify-end { justify-content: flex-start; }
        .rtl .sm\\:justify-end { justify-content: flex-start; }
        
        .tooltip {
            font-family: sans-serif !important;
            position: absolute;
            padding: 5px 10px;
            background-color: var(--color-surface-muted);
            color: var(--color-text-primary);
            border: 1px solid var(--color-border);
            border-radius: 4px;
            display: none;
            font-size: 12px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        /* EasyMDE Toolbar Fixes */
        .editor-toolbar {
            background-color: var(--color-surface-muted) !important;
            border-bottom: 1px solid var(--color-border) !important;
        }

        .editor-toolbar button,
        .editor-toolbar a,
        .editor-toolbar .fa,
        .editor-toolbar i {
            color: var(--color-text-primary) !important;
            background-color: transparent !important;
            border: none !important;
            text-shadow: none !important;
        }

        .editor-toolbar button:hover,
        .editor-toolbar a:hover {
            background-color: var(--color-surface) !important;
            color: var(--color-text-primary) !important;
        }

        .editor-toolbar button.active,
        .editor-toolbar a.active {
            background-color: var(--color-surface-muted) !important;
            color: var(--color-text-primary) !important;
        }

        .editor-toolbar .separator {
            border-left: 1px solid var(--color-border) !important;
            border-right: none !important;
            color: transparent !important;
            font-size: 0 !important;
            width: 1px !important;
            height: 18px !important;
            margin: 0 8px !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        /* Additional EasyMDE icon fixes */
        .editor-toolbar button:before,
        .editor-toolbar a:before {
            color: var(--color-text-primary) !important;
            text-shadow: none !important;
        }

        .editor-toolbar button:hover:before,
        .editor-toolbar a:hover:before {
            color: var(--color-text-primary) !important;
        }

        /* More specific EasyMDE fixes for different icon types */
        .CodeMirror .editor-toolbar > * {
            color: var(--color-text-primary) !important;
        }

        .editor-toolbar > * {
            color: var(--color-text-primary) !important;
        }

        .editor-toolbar > button > i,
        .editor-toolbar > a > i {
            color: var(--color-text-primary) !important;
        }

        /* Force visibility of all toolbar elements */
        .editor-toolbar > * {
            opacity: 1 !important;
            visibility: visible !important;
        }

        /* Style text-based buttons */
        .editor-toolbar .editor-button-text {
            font-weight: bold !important;
            font-size: 14px !important;
            padding: 6px 8px !important;
            min-width: 24px !important;
            text-align: center !important;
            display: inline-block !important;
        }

        .custom-content * {
            all: revert;
        }

        .custom-content pre,
        .custom-content code {
            white-space: pre-wrap;
        }

    </style>

    <script {!! nonce_attr() !!}>
        $(document).ready(function() {
            $('.has-tooltip').hover(function(e) {
                var tooltipText = $(this).attr('data-tooltip');
                var tooltip = $('#tooltip');
                tooltip.html(tooltipText).css({
                    top: e.pageY + 10 + 'px',
                    left: e.pageX + 10 + 'px'
                }).fadeIn(0);

                // Calculate if the tooltip will go off the right edge of the screen
                var tooltipWidth = tooltip.outerWidth();
                var screenWidth = $(window).width();
                var tooltipRightEdge = e.pageX + 10 + tooltipWidth;

                if (tooltipRightEdge > screenWidth) {
                    tooltip.css({
                        left: e.pageX - tooltipWidth - 10 + 'px'
                    });
                }
            }, function() {
                $('#tooltip').fadeOut(0);
            });

            $('.has-tooltip').mousemove(function(e) {
                var tooltip = $('#tooltip');
                var tooltipWidth = tooltip.outerWidth();
                var screenWidth = $(window).width();
                var tooltipRightEdge = e.pageX + 10 + tooltipWidth;

                if (tooltipRightEdge > screenWidth) {
                    tooltip.css({
                        top: e.pageY + 10 + 'px',
                        left: e.pageX - tooltipWidth - 10 + 'px'
                    });
                } else {
                    tooltip.css({
                        top: e.pageY + 10 + 'px',
                        left: e.pageX + 10 + 'px'
                    });
                }
            });


            @if (session('message'))
            Toastify({
                text: "{{ session('message') }}",
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#4BB543',
                }
            }).showToast();
            @elseif (session('error'))
            Toastify({
                text: "{{ session('error') }}",
                close: true,
                duration: 10000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#FF0000',
                }
            }).showToast();
            @endif
        });

    </script>

    @include('branding.styles')

    {{ isset($head) ? $head : '' }}

</head>
<body class="font-sans h-full bg-gray-50 text-gray-900 transition-colors duration-200 dark:bg-gray-950 dark:text-gray-100">

    <div data-theme-floating-wrapper class="fixed bottom-4 right-4 z-40">
        <label for="theme-preference-floating" class="sr-only">{{ __('Theme') }}</label>
        <div class="rounded-full bg-white/90 p-2 shadow-lg ring-1 ring-gray-200 backdrop-blur dark:bg-gray-900/90 dark:ring-gray-700">
            <select id="theme-preference-floating" data-theme-select data-theme-floating
                class="rounded-full border border-transparent bg-transparent px-3 py-1.5 text-sm font-medium text-gray-700 transition-colors duration-150 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-100 dark:focus:border-indigo-400">
                <option value="system">{{ __('System') }}</option>
                <option value="light">{{ __('Light') }}</option>
                <option value="dark">{{ __('Dark') }}</option>
            </select>
        </div>
    </div>

    {{ $slot }}

    @if (isset($footer))
        {{ $footer }}
    @else
        @include('branding.powered-by')
    @endif

</body>
</html>
