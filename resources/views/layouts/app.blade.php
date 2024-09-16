<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head class="h-full bg-white">
    <meta name="description" content="The simple and free way to share your event schedule">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta property="og:title" content="Event Schedule">
    <meta property="og:description" content="The simple and free way to share your event schedule">
    <meta property="og:image" content="https://eventschedule.com/images/background.jpg">
    <meta property="og:url" content="https://eventschedule.com">
    <meta property="og:site_name" content="Event Schedule">

    <meta name="twitter:title" content="Event Schedule">
    <meta name="twitter:description" content="The simple and free way to share your event schedule">
    <meta name="twitter:image" content="https://eventschedule.com/images/background.jpg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image:alt" content="Event Schedule">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.99em%22 font-size=%2275%22>📅</text></svg>">

    <meta http-equiv="Content-Security-Policy" content="
        img-src 'self' data: https://maps.gstatic.com https://maps.googleapis.com https://eventschedule.nyc3.cdn.digitaloceanspaces.com https://*.ytimg.com;
        frame-src 'self';
    ">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
        dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', '{{ config('services.google.analytics') }}');

    function onPopUpClick(event) {
        event.stopPropagation();
        var menu = $('#pop-up-menu');
        if (menu.is(':hidden')) {
            menu.show();
            $(document).on('click', hidePopUp);
        } else {
            hidePopUp();
        }
    }

    function hidePopUp() {
        $('#pop-up-menu').hide();
        $(document).off('click', hidePopUp);
    }

    $(document).on('click', '#pop-up-menu', function(event) {
        event.stopPropagation();
    });
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Event Schedule</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">


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
    });

    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const openButton = document.getElementById('open-sidebar');
        const closeButton = document.getElementById('close-sidebar');

        function toggleMenu() {
            const isOpen = sidebar.getAttribute('data-state') === 'open';
            if (isOpen) {
                $('#sidebar').show();
                sidebar.setAttribute('data-state', 'closed');
            } else {
                $('#sidebar').hide();
                sidebar.setAttribute('data-state', 'open');
            }
        }

        openButton.addEventListener('click', toggleMenu);
        closeButton.addEventListener('click', toggleMenu);

        @if (session('message'))
        Toastify({
            text: "{{ session('message') }}",
            duration: 3000,
            gravity: 'bottom',
            position: 'center',
            stopOnFocus: true,
            style: {
                background: '#4BB543',
            }
        }).showToast();
        @endif
    });

    </script>

    <style>
        .editor-toolbar {
            background-color: white;
        }
    </style>

    {{ isset($head) ? $head : '' }}
</head>
<body class="font-sans antialiased h-full bg-gray-50">

    {{ $slot }}

</body>
</html>