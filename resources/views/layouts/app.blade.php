<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}" class="overflow-x-clip">
<head class="h-full bg-white">
    <title>{{ $title ?? 'Event Schedule' }}</title>
    <!-- Version: {{ config('self-update.version_installed') }} -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="sitemap" type="application/xml" href="{{ config('app.url') . route('sitemap', [], false) }}">    
    
    @if (config('app.hosted') || config('app.report_errors'))
        <script src="{{ config('app.sentry_js_dsn') }}" crossorigin="anonymous" {!! nonce_attr() !!}></script>
    @endif

    @if (isset($meta))
        {{ $meta }}
    @else
        <meta name="robots" content="noindex, nofollow">
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

    <script src="{{ asset('js/jquery.min.js') }}" {!! nonce_attr() !!}></script>
    <script type="text/javascript" src="{{ asset('js/toastify-js.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>window._ssI18n={noResults:@json(__('messages.no_results_found'))};</script>
    <script src="{{ asset('js/searchable-select.js') }}" defer {!! nonce_attr() !!}></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/toastify.min.css') }}">

    @if (config('services.google.analytics') && (! auth()->user() || ! auth()->user()->isAdmin()))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}" {!! nonce_attr() !!}></script>
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

    $(document).on('click', '.popup-toggle[data-popup-target]', function(e) {
        onPopUpClick($(this).attr('data-popup-target'), e);
    });

    $(document).on('click', 'div[data-popup-target]', function(e) {
        onPopUpClick($(this).attr('data-popup-target'), e);
    });
    </script>

    <script {!! nonce_attr() !!}>
        window.appLocale = "{{ app()->getLocale() }}";
        window.editorTranslations = {
            bold: @json(__('messages.editor_bold')),
            italic: @json(__('messages.editor_italic')),
            heading: @json(__('messages.editor_heading')),
            link: @json(__('messages.editor_link')),
            quote: @json(__('messages.editor_quote')),
            unorderedList: @json(__('messages.editor_unordered_list')),
            orderedList: @json(__('messages.editor_ordered_list')),
            preview: @json(__('messages.editor_preview')),
            guide: @json(__('messages.editor_guide'))
        };
    </script>

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    <style {!! nonce_attr() !!}>
        .rtl {
            direction: rtl;
            text-align: right;
        }
        
        [dir="rtl"] select,
        select.rtl,
        .rtl select {
            text-align: right;
            direction: rtl;
            background-position: left 0.5rem center;
            padding-left: 2.5rem;
            padding-right: 0.75rem;
        }

        [dir="rtl"] select option,
        select.rtl option,
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

        /* Undo Tailwind preflight for markdown content */
        .custom-content ul, .custom-content ol { list-style: revert; margin: revert; padding: revert; }
        .custom-content a { color: revert; text-decoration: revert; }
        .custom-content blockquote { margin: revert; }
        .custom-content hr { border: revert; height: revert; }
        .custom-content table, .custom-content th, .custom-content td { border: revert; padding: revert; }
        .custom-content img { display: revert; }
        .custom-content pre, .custom-content code { white-space: pre-wrap; font-family: revert; font-size: revert; }
        .custom-content strong, .custom-content b { font-weight: revert; }
        .custom-content em, .custom-content i { font-style: revert; }
        .custom-content sub { vertical-align: revert; font-size: revert; }
        .custom-content sup { vertical-align: revert; font-size: revert; }

        .dark .custom-content a { color: #60a5fa; }
        .dark .custom-content a:visited { color: #a78bfa; }
        .dark .custom-content a:hover { color: #93c5fd; }

        /* Heading sizes and spacing */
        .custom-content h1 { font-size: 1.8rem; font-weight: 700; margin: 0 0 0.25rem; }
        .custom-content h2 { font-size: 1.55rem; font-weight: 600; margin: 0 0 0.25rem; }
        .custom-content h3 { font-size: 1.3rem; font-weight: 600; margin: 0 0 0.25rem; }
        .custom-content h4, .custom-content h5, .custom-content h6 { font-size: 1.15rem; font-weight: 600; margin: 0 0 0.25rem; }

        .custom-content * + h1 { margin-top: 1rem; }
        .custom-content * + h2 { margin-top: 0.75rem; }
        .custom-content * + h3 { margin-top: 0.5rem; }
        .custom-content * + h4, .custom-content * + h5, .custom-content * + h6 { margin-top: 0.5rem; }

        /* Paragraph spacing */
        .custom-content p { margin: 0 0 0.5em; }
        .custom-content * + p { margin-top: 0.5em; }

        .cm-s-easymde .cm-header-1 { font-size: 1.8rem !important; }
        .cm-s-easymde .cm-header-2 { font-size: 1.55rem !important; }
        .cm-s-easymde .cm-header-3 { font-size: 1.3rem !important; }
        .cm-s-easymde .cm-header-4,
        .cm-s-easymde .cm-header-5,
        .cm-s-easymde .cm-header-6 { font-size: 1.15rem !important; }

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
            font-family: inherit !important;
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
            (function() {
                var key = '{{ uniqid("toast_") }}';
                if (!sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    Toastify({
                        text: @json(session('message'), JSON_UNESCAPED_UNICODE),
                        duration: 3000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#4BB543',
                        }
                    }).showToast();
                }
            })();
            @elseif (session('error'))
            (function() {
                var key = '{{ uniqid("toast_") }}';
                if (!sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    Toastify({
                        text: @json(session('error'), JSON_UNESCAPED_UNICODE),
                        close: true,
                        duration: 10000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#FF0000',
                        }
                    }).showToast();
                }
            })();
            @elseif (session('warning'))
            (function() {
                var key = '{{ uniqid("toast_") }}';
                if (!sessionStorage.getItem(key)) {
                    sessionStorage.setItem(key, '1');
                    Toastify({
                        text: @json(session('warning'), JSON_UNESCAPED_UNICODE),
                        close: true,
                        duration: 8000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#F59E0B',
                        }
                    }).showToast();
                }
            })();
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

            function safeGetItem(key) {
                try { return localStorage.getItem(key); } catch (e) { return null; }
            }
            function safeSetItem(key, value) {
                try { localStorage.setItem(key, value); } catch (e) {}
            }

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
                const storedTheme = safeGetItem(THEME_STORAGE_KEY);
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
                    const currentTheme = safeGetItem(THEME_STORAGE_KEY);
                    if (currentTheme === THEMES.SYSTEM) {
                        applyTheme(THEMES.SYSTEM);
                    }
                });
            }

            function setThemeInternal(theme) {
                safeSetItem(THEME_STORAGE_KEY, theme);
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
                return safeGetItem(THEME_STORAGE_KEY) || THEMES.SYSTEM;
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
<body class="font-sans antialiased h-full bg-gray-50 dark:bg-gray-900 overflow-x-clip">

    {{ $slot }}

    <div id="tooltip" class="hidden fixed z-50 px-3 py-2 text-sm text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-lg pointer-events-none max-w-xs"></div>

    @if (config('app.is_testing'))
        <style {!! nonce_attr() !!}>
            @keyframes es-ping { 0% { transform: scale(1); opacity: 0.5; } 100% { transform: scale(3); opacity: 0; } }
        </style>
        <div class="fixed bottom-4 right-4 z-50 pointer-events-none" style="width:24px;height:24px">
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316;animation:es-ping 3s cubic-bezier(0,0,0.2,1) infinite"></div>
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316;animation:es-ping 3s cubic-bezier(0,0,0.2,1) 1.5s infinite"></div>
            <div style="position:absolute;inset:0;border-radius:9999px;background:#f97316"></div>
        </div>
    @endif

</body>
</html>
