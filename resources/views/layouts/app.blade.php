<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head class="h-full bg-white">
    <title>{{ $title ?? 'Event Schedule' }}</title>
    <!-- Version: {{ config('self-update.version_installed') }} -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="sitemap" type="application/xml" href="{{ url('sitemap.xml') }}">    
    
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

        .editor-toolbar {
            background-color: white;
        }

        .custom-content * {
            all: revert;
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