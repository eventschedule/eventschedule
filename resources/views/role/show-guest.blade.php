<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="description" content="Connecting venues, talent, and vendors with their audience">

    <meta property="og:title" content="Event Schedule">
    <meta property="og:description" content="Connecting venues, talent, and vendors with their audience">
    <meta property="og:image" content="https://eventschedule.com/images/background.jpg">
    <meta property="og:url" content="https://eventschedule.com">
    <meta property="og:site_name" content="Event Schedule">

    <meta name="twitter:title" content="Event Schedule">
    <meta name="twitter:description" content="Connecting venues, talent, and vendors with their audience">
    <meta name="twitter:image" content="https://eventschedule.com/images/background.jpg">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image:alt" content="Event Schedule">

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.99em%22 font-size=%2275%22>📅</text></svg>">

    <meta http-equiv="Content-Security-Policy" content="
        img-src 'self' data: https://eventschedule.nyc3.cdn.digitaloceanspaces.com;
        frame-src 'self' https://www.youtube.com;
    ">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google.analytics') }}');
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $role->name . ' | Event Schedule' }}</title>

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
        @if ($event)
            color: {{ $otherRole->font_color }} !important;
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

<body class="antialiased dark:bg-black dark:text-white/50">

    <div class="p-10 max-w-5xl mx-auto px-4">
        <div class="flex items-start justify-between pb-6">
            <a href="{{ $event ? $event->venue->getGuestUrl() : $role->getGuestUrl() }}">
                <div id="venue_title" class="text-4xl font-bold">
                    {{ $event ? $event->venue->name : $role->name }}
                </div>
            </a>
            <div>
                @if ($event)
                @if (! $user || ! $user->isConnected($event->role->subdomain))
                <a href="{{ route('role.follow', ['subdomain' => $event->role->subdomain]) }}" class="pl-1">
                    <button type="button" style="background-color: {{ $event->role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M21.61 2.39C21.61 2.39 16.66 .269 11 5.93C8.81 8.12 7.5 10.53 6.65 12.64C6.37 13.39 6.56 14.21 7.11 14.77L9.24 16.89C9.79 17.45 10.61 17.63 11.36 17.35C13.5 16.53 15.88 15.19 18.07 13C23.73 7.34 21.61 2.39 21.61 2.39M14.54 9.46C13.76 8.68 13.76 7.41 14.54 6.63S16.59 5.85 17.37 6.63C18.14 7.41 18.15 8.68 17.37 9.46C16.59 10.24 15.32 10.24 14.54 9.46M8.88 16.53L7.47 15.12L8.88 16.53M6.24 22L9.88 18.36C9.54 18.27 9.21 18.12 8.91 17.91L4.83 22H6.24M2 22H3.41L8.18 17.24L6.76 15.83L2 20.59V22M2 19.17L6.09 15.09C5.88 14.79 5.73 14.47 5.64 14.12L2 17.76V19.17Z" />
                            </svg>
                        {{ __('messages.follow') }}
                    </button>
                </a>
                @endif
                @else
                @if (! $user || ! $user->isConnected($role->subdomain))
                <a href="{{ route('role.follow', ['subdomain' => $role->subdomain]) }}" class="pl-1">
                    <button type="button" style="background-color: {{ $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M21.61 2.39C21.61 2.39 16.66 .269 11 5.93C8.81 8.12 7.5 10.53 6.65 12.64C6.37 13.39 6.56 14.21 7.11 14.77L9.24 16.89C9.79 17.45 10.61 17.63 11.36 17.35C13.5 16.53 15.88 15.19 18.07 13C23.73 7.34 21.61 2.39 21.61 2.39M14.54 9.46C13.76 8.68 13.76 7.41 14.54 6.63S16.59 5.85 17.37 6.63C18.14 7.41 18.15 8.68 17.37 9.46C16.59 10.24 15.32 10.24 14.54 9.46M8.88 16.53L7.47 15.12L8.88 16.53M6.24 22L9.88 18.36C9.54 18.27 9.21 18.12 8.91 17.91L4.83 22H6.24M2 22H3.41L8.18 17.24L6.76 15.83L2 20.59V22M2 19.17L6.09 15.09C5.88 14.79 5.73 14.47 5.64 14.12L2 17.76V19.17Z" />
                            </svg>
                        {{ __('messages.follow') }}
                    </button>
                </a>
                @endif
                @endif

                @if ($event || $role->isVenue())
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event ? $event->venue->bestAddress() : $role->bestAddress()) }}" target="_blank" class="pl-1">
                    <button type="button" style="background-color: {{ $event ? $event->role->accent_color : $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z" />
                            </svg>
                        {{ __('messages.map') }}
                    </button>
                </a>
                @endif
            </div>
        </div>

        @if ($event)

            <div id="event_title" class="py-16">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl lg:text-center">
                        <p class="mt-2 text-6xl font-bold tracking-tight">
                            <a href="{{ $event->role->getGuestUrl() }}">
                                {{ $event->role->name }}
                            </a>
                        </p>
                        <p class="mt-6 text-2xl leading-8">{{ $event->starts_at ? $event->localStartsAt(true) : __('messages.date_to_be_announced') . '...' }}</p>
                    </div>
                </div>
            </div>

            @if ($event->role->youtube_links)
                <div class="container mx-auto py-8">
                    <div class="grid grid-cols-1 md:grid-cols-{{ $event->role->getVideoColumns() }} gap-8">
                        @foreach (json_decode($event->role->youtube_links) as $link)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <iframe class="w-full" style="height:{{ $event->role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        
        @elseif (! $role->isVenue() && $role->youtube_links)

            @if ($role->youtube_links)
            <div class="container mx-auto py-8">
                <div class="grid grid-cols-1 md:grid-cols-{{ $role->getVideoColumns() }} gap-8">
                    @foreach (json_decode($role->youtube_links) as $link)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        @endif

        @include('role/partials/calendar', ['showAdd' => false, 'route' => 'guest'])

        <div class="py-6">
            <div class="flex items-start justify-between pb-6">
                <div>
                    {!! $role->description_html !!}
                </div>
                <div>
                @if ($role->isVenue() && $role->accept_talent_requests)
                <a href="{{ route('event.sign_up', ['subdomain' => $role->subdomain])}}">
                    <button type="button" style="background-color: {{ $event ? $event->role->accent_color : $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M19.59,3H22V5H20.41L16.17,9.24C15.8,8.68 15.32,8.2 14.76,7.83L19.59,3M12,8A4,4 0 0,1 16,12C16,13.82 14.77,15.42 13,15.87V16A5,5 0 0,1 8,21A5,5 0 0,1 3,16A5,5 0 0,1 8,11H8.13C8.58,9.24 10.17,8 12,8M12,10.5A1.5,1.5 0 0,0 10.5,12A1.5,1.5 0 0,0 12,13.5A1.5,1.5 0 0,0 13.5,12A1.5,1.5 0 0,0 12,10.5M6.94,14.24L6.23,14.94L9.06,17.77L9.77,17.06L6.94,14.24Z" />
                        </svg>
                        {{ __('messages.sign_up_to_perform') }}
                    </button>
                </a>
                @endif

                @if ($role->isVenue() && $role->accept_vendor_requests)
                <a href="{{ route('event.sign_up', ['subdomain' => $role->subdomain])}}">
                    <button type="button" style="background-color: {{ $event ? $event->role->accent_color : $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="white"
                                aria-hidden="true">
                                <path
                                    d="M12,13A5,5 0 0,1 7,8H9A3,3 0 0,0 12,11A3,3 0 0,0 15,8H17A5,5 0 0,1 12,13M12,3A3,3 0 0,1 15,6H9A3,3 0 0,1 12,3M19,6H17A5,5 0 0,0 12,1A5,5 0 0,0 7,6H5C3.89,6 3,6.89 3,8V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V8C21,6.89 20.1,6 19,6Z" />
                        </svg>
                        {{ __('messages.sign_up_to_vend') }}
                    </button>
                </a>
                @endif
                </div>
            </div>
        </div>
        
        @if ($role->isVenue() && $role->youtube_links && ! $event)
            <div class="container mx-auto py-8">
                <div class="grid grid-cols-1 md:grid-cols-{{ $role->getVideoColumns() }} gap-8">
                    @foreach (json_decode($role->youtube_links) as $link)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <iframe class="w-full" style="height:{{ $role->getVideoHeight() }}px" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between pb-10">
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                @if($role->email && $role->user_id)
                <div class="mt-2 flex items-center text-sm">
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                    </svg>
                    <div class="mt-1">
                        <a href="mailto:{{ $role->email }}">
                            {{ $role->email }}
                        </a>
                    </div>
                </div>
                @endif

                @if($role->phone)
                <div class="mt-2 flex items-center text-sm">
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M6.62,10.79C8.06,13.62 10.38,15.94 13.21,17.38L15.41,15.18C15.69,14.9 16.08,14.82 16.43,14.93C17.55,15.3 18.75,15.5 20,15.5A1,1 0 0,1 21,16.5V20A1,1 0 0,1 20,21A17,17 0 0,1 3,4A1,1 0 0,1 4,3H7.5A1,1 0 0,1 8.5,4C8.5,5.25 8.7,6.45 9.07,7.57C9.18,7.92 9.1,8.31 8.82,8.59L6.62,10.79Z" />
                    </svg>
                    <div class="mt-1">
                        <a href="tel:{{ $role->phone }}">
                            {{ $role->phone }}
                        </a>
                    </div>
                </div>
                @endif

                @if($role->website)
                <div class="mt-2 flex items-center text-sm">
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M10.59,13.41C11,13.8 11,14.44 10.59,14.83C10.2,15.22 9.56,15.22 9.17,14.83C7.22,12.88 7.22,9.71 9.17,7.76V7.76L12.71,4.22C14.66,2.27 17.83,2.27 19.78,4.22C21.73,6.17 21.73,9.34 19.78,11.29L18.29,12.78C18.3,11.96 18.17,11.14 17.89,10.36L18.36,9.88C19.54,8.71 19.54,6.81 18.36,5.64C17.19,4.46 15.29,4.46 14.12,5.64L10.59,9.17C9.41,10.34 9.41,12.24 10.59,13.41M13.41,9.17C13.8,8.78 14.44,8.78 14.83,9.17C16.78,11.12 16.78,14.29 14.83,16.24V16.24L11.29,19.78C9.34,21.73 6.17,21.73 4.22,19.78C2.27,17.83 2.27,14.66 4.22,12.71L5.71,11.22C5.7,12.04 5.83,12.86 6.11,13.65L5.64,14.12C4.46,15.29 4.46,17.19 5.64,18.36C6.81,19.54 8.71,19.54 9.88,18.36L13.41,14.83C14.59,13.66 14.59,11.76 13.41,10.59C13,10.2 13,9.56 13.41,9.17Z" />
                    </svg>
                    <div class="mt-1">
                        <a href="{{ $role->website }}" target="_blank">
                            {{ \App\Utils\UrlUtils::clean($role->website) }}
                        </a>
                    </div>
                </div>
                @endif

            </div>
            <div class="flex space-x-4">
                @if ($role->social_links)
                @foreach (json_decode($role->social_links) as $link)
                    <a href="{{ $link->url }}" target="_blank">
                        <x-url-icon>
                            {{ \App\Utils\UrlUtils::clean($link->url) }}
                        </x-url-icon>
                    </a>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    <footer class="bottom-0 left-0 right-0 bg-black text-white py-4 w-full">
        <div class="container mx-auto text-center">
            <p>
                {!! str_replace(':link', '<a href="' . url('/') . '" target="_blank">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
                •
                {!! str_replace(':link', '<a href="https://mudeo.app" target="_blank">mudeo | make music together</a>',  __('messages.supported_by')) !!}
            </p>
        </div>
    </footer>

</body>
</html>
