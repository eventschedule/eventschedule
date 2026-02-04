<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head class="h-full bg-white">
    <title>{{ $title ?? 'Event Schedule' }}</title>
    <!-- Version: {{ config('self-update.version_installed') }} -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="sitemap" type="application/xml" href="{{ config('app.url') . route('sitemap', [], false) }}">    
    
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
        <meta property="og:image" content="{{ config('app.url') }}/images/social/home.png">
        <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
        <meta property="og:site_name" content="Event Schedule">
        <meta name="twitter:title" content="Event Schedule">
        <meta name="twitter:description" content="The simple and free way to share your event schedule">
        <meta name="twitter:image" content="{{ config('app.url') }}/images/social/home.png">
        <meta name="twitter:image:alt" content="Event Schedule">
        <meta name="twitter:card" content="summary_large_image">
    @endif    

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
            // Get button position
            var button = event.currentTarget;
            var rect = button.getBoundingClientRect();

            // Position dropdown with fixed positioning
            var isRtl = document.documentElement.dir === 'rtl';
            if (isRtl) {
                menu.css({
                    'position': 'fixed',
                    'top': (rect.bottom + 4) + 'px',
                    'left': rect.left + 'px',
                    'right': 'auto',
                    'z-index': '1000'
                });
            } else {
                menu.css({
                    'position': 'fixed',
                    'top': (rect.bottom + 4) + 'px',
                    'right': (window.innerWidth - rect.right) + 'px',
                    'left': 'auto',
                    'z-index': '1000'
                });
            }

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

    <script {!! nonce_attr() !!}>
        window.appLocale = "{{ app()->getLocale() }}";
    </script>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

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
        
        /* RTL flex direction adjustments */
        .rtl .space-x-3 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        .rtl .space-x-10 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
        }
        .rtl .space-x-8 > :not([hidden]) ~ :not([hidden]) {
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
            background: #333;
            color: #fff;
            border-radius: 4px;
            display: none;
            font-size: 12px;
            z-index: 9999;
        }

        /* Event Popup with Glassmorphism */
        .event-popup {
            position: fixed;
            display: none;
            z-index: 10000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }

        .event-popup.show {
            display: block;
            opacity: 1;
        }

        .event-popup-content {
            display: flex;
            flex-direction: row;
            background: linear-gradient(135deg, rgba(249, 250, 251, 0.95) 0%, rgba(249, 250, 251, 0.9) 100%);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            max-width: 480px;
            min-width: 380px;
            pointer-events: auto;
        }

        .dark .event-popup-content {
            background: linear-gradient(135deg, rgba(37, 37, 38, 0.95) 0%, rgba(30, 30, 30, 0.9) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .event-popup-image {
            width: 160px;
            min-height: 100%;
            object-fit: cover;
            flex-shrink: 0;
            display: block;
        }

        .event-popup-body {
            padding: 16px;
            flex: 1;
            min-width: 0;
        }

        .event-popup-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 12px 0;
            line-height: 1.4;
        }

        .dark .event-popup-title {
            color: #f9fafb;
        }

        .event-popup-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 12px;
        }

        .event-popup-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6b7280;
        }

        .dark .event-popup-detail {
            color: #d1d5db;
        }

        .event-popup-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            color: #9ca3af;
        }

        .dark .event-popup-icon {
            color: #6b7280;
        }

        .event-popup-description {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .dark .event-popup-description {
            color: #d1d5db;
        }

        /* EasyMDE Toolbar Fixes */
        .editor-toolbar {
            background-color: #f8f9fa !important; /* Temporarily change to light gray for debugging */
            border-bottom: 1px solid #d1d5db !important;
        }

        .editor-toolbar button,
        .editor-toolbar a,
        .editor-toolbar .fa,
        .editor-toolbar i {
            color: #374151 !important;
            background-color: transparent !important;
            border: none !important;
            text-shadow: 0 0 1px rgba(0,0,0,0.5) !important; /* Add text shadow for visibility */
        }

        .editor-toolbar button:hover,
        .editor-toolbar a:hover {
            background-color: #f3f4f6 !important;
            color: #111827 !important;
        }

        .editor-toolbar button.active,
        .editor-toolbar a.active {
            background-color: #e5e7eb !important;
            color: #111827 !important;
        }

        .editor-toolbar .separator {
            border-left: 1px solid #d1d5db !important;
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
            color: #374151 !important;
            text-shadow: 0 0 1px rgba(0,0,0,0.5) !important;
        }

        .editor-toolbar button:hover:before,
        .editor-toolbar a:hover:before {
            color: #111827 !important;
        }

        /* More specific EasyMDE fixes for different icon types */
        .CodeMirror .editor-toolbar > * {
            color: #374151 !important;
        }

        .editor-toolbar > * {
            color: #374151 !important;
        }

        .editor-toolbar > button > i,
        .editor-toolbar > a > i {
            color: #374151 !important;
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

        /* EasyMDE Dark Mode Styles - Using custom Tailwind colors */
        .dark .editor-toolbar {
            background-color: #1A1A1A !important; /* Match sidebar dark gray */
            border: none !important;
        }

        .dark .editor-toolbar button,
        .dark .editor-toolbar a,
        .dark .editor-toolbar .fa,
        .dark .editor-toolbar i {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar button:hover,
        .dark .editor-toolbar a:hover {
            background-color: #1A1A1A !important; /* Match sidebar dark gray */
            color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .editor-toolbar button.active,
        .dark .editor-toolbar a.active {
            background-color: #525252 !important; /* gray-600 */
            color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .editor-toolbar .separator {
            border-left: 1px solid #525252 !important; /* gray-600 */
        }

        .dark .editor-toolbar button:before,
        .dark .editor-toolbar a:before {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar button:hover:before,
        .dark .editor-toolbar a:hover:before {
            color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .CodeMirror .editor-toolbar > * {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar > * {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .editor-toolbar > button > i,
        .dark .editor-toolbar > a > i {
            color: #d4d4d4 !important; /* gray-300 */
        }

        .dark .CodeMirror {
            background-color: #222222 !important; /* gray-900 - match standard text inputs */
            color: #f9f9f9 !important; /* gray-50 */
            border: none !important;
        }

        .dark .CodeMirror-cursor {
            border-color: #f9f9f9 !important; /* gray-50 */
        }

        .dark .CodeMirror-selected {
            background-color: #525252 !important; /* gray-600 */
        }

        .dark .CodeMirror-line::selection,
        .dark .CodeMirror-line > span::selection,
        .dark .CodeMirror-line > span > span::selection {
            background-color: #525252 !important; /* gray-600 */
        }

        .dark .CodeMirror-gutters {
            background-color: #2B2B2B !important; /* gray-800 */
            border-right: 1px solid #404040 !important; /* gray-700 */
        }

        .dark .CodeMirror-linenumber {
            color: #737373 !important; /* gray-500 */
        }

        .dark .CodeMirror-focused .CodeMirror-selected {
            background-color: #525252 !important; /* gray-600 */
        }

        .dark .EasyMDEContainer {
            border: 1px solid #2d2d30 !important; /* gray-700 - match form inputs */
            border-radius: 0.375rem !important; /* rounded-md */
        }

        .dark .EasyMDEContainer .CodeMirror,
        .dark .EasyMDEContainer .CodeMirror-focused {
            border: none !important;
        }

        .custom-content * {
            all: revert;
        }

        .custom-content pre,
        .custom-content code {
            white-space: pre-wrap;
        }

        .dark .custom-content a {
            color: #60a5fa; /* Tailwind blue-400 - readable on dark backgrounds */
        }

        .dark .custom-content a:visited {
            color: #a78bfa; /* Tailwind violet-400 - lighter purple, readable on dark */
        }

        .dark .custom-content a:hover {
            color: #93c5fd; /* Tailwind blue-300 - slightly lighter on hover */
        }

        .custom-content h1 {
            font-size: 1rem;
            font-weight: 700;
            margin-top: 1rem;
            margin-bottom: 0.25rem;
        }

        .custom-content h2 {
            font-size: 1rem;
            font-weight: 600;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .custom-content h3,
        .custom-content h4,
        .custom-content h5,
        .custom-content h6 {
            font-size: 1rem;
            font-weight: 600;
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="url"],
        select,
        textarea {
            transition: border-color 0.2s, box-shadow 0.2s;
            font-size: 1.15rem !important;
            line-height: 1.5 !important;
            padding: 0.75rem 1rem !important;
        }

        /* Exception for country picker - needs proper padding for flag */
        /* Default mode: flag on right */
        .country-select input,
        .country-select input[type="text"] {
            padding-right: 36px !important;
        }
        /* Inside mode: flag on left */
        .country-select.inside input,
        .country-select.inside input[type="text"] {
            padding-left: 52px !important;
            padding-right: 6px !important;
        }


    </style>

    <script {!! nonce_attr() !!}>
        $(document).ready(function() {
            $('.has-tooltip').hover(function(e) {
                var tooltipText = $(this).attr('data-tooltip');
                var tooltip = $('#tooltip');
                tooltip.html(tooltipText).css({
                    top: e.clientY + 10 + 'px',
                    left: e.clientX + 10 + 'px'
                }).fadeIn(0);

                // Calculate if the tooltip will go off the right edge of the screen
                var tooltipWidth = tooltip.outerWidth();
                var screenWidth = $(window).width();
                var tooltipRightEdge = e.clientX + 10 + tooltipWidth;

                if (tooltipRightEdge > screenWidth) {
                    tooltip.css({
                        left: e.clientX - tooltipWidth - 10 + 'px'
                    });
                }
            }, function() {
                $('#tooltip').fadeOut(0);
            });

            $('.has-tooltip').mousemove(function(e) {
                var tooltip = $('#tooltip');
                var tooltipWidth = tooltip.outerWidth();
                var screenWidth = $(window).width();
                var tooltipRightEdge = e.clientX + 10 + tooltipWidth;

                if (tooltipRightEdge > screenWidth) {
                    tooltip.css({
                        top: e.clientY + 10 + 'px',
                        left: e.clientX - tooltipWidth - 10 + 'px'
                    });
                } else {
                    tooltip.css({
                        top: e.clientY + 10 + 'px',
                        left: e.clientX + 10 + 'px'
                    });
                }
            });


            @if (session('message'))
            Toastify({
                text: {!! json_encode(session('message'), JSON_UNESCAPED_UNICODE) !!},
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#4BB543',
                }
            }).showToast();
            @elseif (session('error'))
            Toastify({
                text: {!! json_encode(session('error'), JSON_UNESCAPED_UNICODE) !!},
                close: true,
                duration: 10000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#FF0000',
                }
            }).showToast();
            @elseif (session('warning'))
            Toastify({
                text: {!! json_encode(session('warning'), JSON_UNESCAPED_UNICODE) !!},
                close: true,
                duration: 8000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#F59E0B',
                }
            }).showToast();
            @endif
        });

    </script>

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
                
                // Apply theme immediately to prevent flash
                applyTheme(theme);
                
                // Watch for system theme changes if in system mode
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

            // Initialize theme on page load
            initTheme();

            // Expose setTheme globally for theme toggle
            window.setTheme = function(theme) {
                setThemeInternal(theme);
                // Update buttons after a short delay to ensure DOM is ready
                setTimeout(function() {
                    if (typeof window.updateThemeButtons === 'function') {
                        window.updateThemeButtons();
                    }
                }, 10);
            };
            window.getCurrentTheme = function() {
                return localStorage.getItem(THEME_STORAGE_KEY) || THEMES.SYSTEM;
            };
            
            // Update buttons after theme system is initialized
            // Use requestAnimationFrame for immediate update on next paint
            requestAnimationFrame(function() {
                if (typeof window.updateThemeButtons === 'function') {
                    window.updateThemeButtons();
                } else {
                    // If navigation script hasn't loaded yet, try again after a short delay
                    setTimeout(function() {
                        if (typeof window.updateThemeButtons === 'function') {
                            window.updateThemeButtons();
                        }
                    }, 10);
                }
            });
        })();
    </script>

    {{ isset($head) ? $head : '' }}

</head> 
<body class="font-sans antialiased h-full bg-gray-50 dark:bg-gray-900">

    {{ $slot }}

    <div id="tooltip" class="hidden fixed z-50 px-3 py-2 text-sm text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-lg pointer-events-none max-w-xs"></div>

</body>
</html>
