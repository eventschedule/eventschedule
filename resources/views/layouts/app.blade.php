<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head class="h-full bg-white">
    <title>{{ $title ?? 'Event Schedule' }}</title>
    <!-- Version: {{ config('self-update.version_installed') }} -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="sitemap" type="application/xml" href="{{ config('app.url') . route('sitemap', [], false) }}">    
    
    @if (config('app.sentry_js_dsn'))
        <script src="{{ config('app.sentry_js_dsn') }}" crossorigin="anonymous"></script>
    @endif

    @if (isset($meta))
        {{ $meta }}
    @else
        <link rel="canonical" href="{{ url()->current() }}">
        <meta name="description" content="The simple and free way to share your event schedule">
        <meta property="og:title" content="Event Schedule">
        <meta property="og:description" content="The simple and free way to share your event schedule">
        <meta property="og:image" content="{{ config('app.url') }}/images/background.jpg">
        <meta property="og:url" content="' . request()->url() . '">
        <meta property="og:site_name" content="Event Schedule">
        <meta name="twitter:title" content="Event Schedule">
        <meta name="twitter:description" content="The simple and free way to share your event schedule">
        <meta name="twitter:image" content="{{ config('app.url') }}/images/background.jpg">
        <meta name="twitter:image:alt" content="Event Schedule">
        <meta name="twitter:card" content="summary_large_image">
    @endif    

    <meta http-equiv="Content-Security-Policy" content="
        img-src 'self' data: https://maps.gstatic.com https://maps.googleapis.com https://*.ytimg.com https://eventschedule.test:5173 {{ config('app.hosted') ? 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com' : '' }};
        frame-src 'self' https://www.youtube.com https://www.googletagmanager.com;
    ">

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/toastify-js.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/toastify.min.css') }}">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics') }}');

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
            background: #333;
            color: #fff;
            border-radius: 4px;
            display: none;
            font-size: 12px;
            z-index: 9999;
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

        .custom-content * {
            all: revert;
        }

        .custom-content pre,
        .custom-content code {
            white-space: pre-wrap;
        }

    </style>

    <script>
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

    {{ isset($head) ? $head : '' }}

</head> 
<body class="font-sans antialiased h-full bg-gray-50">

    {{ $slot }}

</body>
</html>