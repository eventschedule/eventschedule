<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Event Schedule</title>

    @if ($event && $event->role->description_html)
    <meta name="description" content="{{ trim(strip_tags($event->role->description_html)) }}">
    @elseif ($role->description_html)
    <meta name="description" content="{{ trim(strip_tags($role->description_html)) }}">
    @endif

    @if ($event)
    <meta property="og:title" content="{{ $event->role->name }}">
    <meta property="og:description" content="{{ $event->getMetaDescription() }}">
    <meta name="twitter:title" content="{{ $event->role->name }}">
    <meta name="twitter:description" content="{{ $event->getMetaDescription() }}">
    @else
    <meta property="og:title" content="{{ $role->name }}">
    <meta property="og:description" content="{{ trim(strip_tags($role->description_html)) }}">
    <meta name="twitter:title" content="{{ $role->name }}">
    <meta name="twitter:description" content="{{ trim(strip_tags($role->description_html)) }}">
    @endif

    <meta property="og:url" content="https://eventschedule.com">
    <meta property="og:site_name" content="Event Schedule">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image:alt" content="Event Schedule">

    @if ($event)
    <meta property="og:image" content="{{ $event->getImageUrl() }}">
    <meta name="twitter:image" content="{{ $event->getImageUrl() }}">
    @elseif ($role->profile_image_url)
    <meta property="og:image" content="{{ $role->profile_image_url }}">
    <meta name="twitter:image" content="{{ $role->profile_image_url }}">
    @endif

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.99em%22 font-size=%2275%22>📅</text></svg>">

    <meta http-equiv="Content-Security-Policy" content="
        img-src 'self' data: https://eventschedule.nyc3.cdn.digitaloceanspaces.com;
        frame-src 'self' https://www.youtube.com;
    ">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
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
    </script>            

    <!-- Fonts -->     
    <link href="https://fonts.googleapis.com/css2?family={{ $role->font_family }}:wght@400;700&display=swap" rel="stylesheet">
    @if ($event)
        <link href="https://fonts.googleapis.com/css2?family={{ $otherRole->font_family }}:wght@400;700&display=swap" rel="stylesheet">
    @endif

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
    body {
        font-family: '{{ $role->font_family }}', sans-serif !important;
        min-height: 100%;
        background-attachment: scroll;
        @if ($event && $otherRole->user_id)
            color: {{ $otherRole->font_color }} !important;
            background-color: #888 !important;
            @if ($otherRole->background == 'gradient')
                background-image: linear-gradient({{ $otherRole->background_rotation }}deg, {{ $otherRole->background_colors }});
            @elseif ($otherRole->background == 'image')
                background-image: url("{{ $otherRole->background_image_url }}");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                height: 100%;
                margin: 0;
            @endif
        @else
            color: {{ $role->font_color }} !important;
            background-color: #888 !important;
            @if ($role->background == 'gradient')
                background-image: linear-gradient({{ $role->background_rotation }}deg, {{ $role->background_colors }});
            @elseif ($role->background == 'image')
                background-image: url("{{ $role->background_image_url }}");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                height: 100%;
                margin: 0;
            @endif
        @endif
    }

    @if ($event)
        @if ($subdomain == $event->venue->subdomain)
        #event_title {
        @else
        #venue_title {
        @endif
            font-family: '{{ $otherRole->font_family }}', sans-serif !important;
        }
    }
    @endif
    </style>

</head>

@yield('content')

</html>