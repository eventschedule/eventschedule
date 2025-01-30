<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head class="h-full bg-white">
    <title>{{ $title ?? 'Event Schedule' }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="canonical" href="{{ url()->current() }}">

    @if (config('services.sentry.dsn'))
        <script src="https://js.sentry-cdn.com/dbdc1097b55788aa2f56e31faa53a7fc.min.js" crossorigin="anonymous"></script>
    @endif

    @if (isset($meta))
        {{ $meta }}
    @else
        <meta name="description" content="The simple and free way to share your event schedule">
        <meta property="og:title" content="Event Schedule">
        <meta property="og:description" content="The simple and free way to share your event schedule">
        <meta property="og:image" content="https://eventschedule.com/images/background.jpg">
        <meta property="og:url" content="' . request()->url() . '">
        <meta property="og:site_name" content="Event Schedule">
        <meta name="twitter:title" content="Event Schedule">
        <meta name="twitter:description" content="The simple and free way to share your event schedule">
        <meta name="twitter:image" content="https://eventschedule.com/images/background.jpg">
        <meta name="twitter:image:alt" content="Event Schedule">
        <meta name="twitter:card" content="summary_large_image">
    @endif    

    <meta http-equiv="Content-Security-Policy" content="
        img-src 'self' data: https://maps.gstatic.com https://maps.googleapis.com https://eventschedule.nyc3.cdn.digitaloceanspaces.com https://*.ytimg.com https://eventschedule.test:5173;
        frame-src 'self' https://www.youtube.com https://www.googletagmanager.com;
    ">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

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

        h1 {
        font-size: 2.25rem; /* Same as text-4xl in Tailwind */
        font-weight: 700; /* Same as font-bold */
        }

        h2 {
        font-size: 1.875rem; /* Same as text-3xl */
        font-weight: 600; /* Same as font-semibold */
        }

        h3 {
        font-size: 1.5rem; /* Same as text-2xl */
        font-weight: 600; /* Same as font-semibold */
        }

        h4 {
        font-size: 1.25rem; /* Same as text-xl */
        font-weight: 500; /* Same as font-medium */
        }

        h5 {
        font-size: 1.125rem; /* Same as text-lg */
        font-weight: 500; /* Same as font-medium */
        }

        h6 {
        font-size: 1rem; /* Same as text-base */
        font-weight: 400; /* Same as font-normal */
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