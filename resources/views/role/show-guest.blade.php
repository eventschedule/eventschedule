<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ str_replace(':name', $role->name, __(':name | Event Schedule'))  }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family={{ $role->font_family }}:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
    body {
        font-family: '{{ $role->font_family }}', sans-serif !important;
        color: {{ $role->font_color }} !important;
        @if ($role->background == 'color')
            background-color: {{ $role->background_color }};
        @elseif ($role->background == 'gradient')
            background-image: linear-gradient({{ $role->background_rotation }}deg, {{ $role->background_colors }});
        @elseif ($role->background == 'image')
            background-image: url("{{ $role->background_image_url }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100%;
            margin: 0;
        @endif
    }
    </style>

</head>

<body class="antialiased dark:bg-black dark:text-white/50">

    <div class="p-10 max-w-4xl mx-auto px-4">
        <div class="flex items-start justify-between pb-6">
            <div>
                @if ($role->profile_image_url)
                <img src="{{ $role->profile_image_url }}" style="max-height:100px" />
                @else
                <div class="text-4xl font-bold">
                    {{ $role->name }}
                </div>
                @endif
            </div>
            <div>
                <a href="">
                    <button type="button" style="background-color: {{ $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        {{ __('Follow') }}
                    </button>
                </a>
            </div>
        </div>

        @include('role/partials/calendar', ['showAdd' => false, 'route' => 'guest'])

        <div class="py-6">
            <div class="flex items-start justify-between pb-6">
                <div>
                    {{ $role->description }}
                </div>
                <div>
                @if ($role->accept_talent_requests)
                <a href="">
                    <button type="button" style="background-color: {{ $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        {{ __('Sign up to Perform') }}
                    </button>
                </a>
                @endif

                @if ($role->accept_vendor_requests)
                <a href="">
                    <button type="button" style="background-color: {{ $role->accent_color }}"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2">
                        {{ __('Sign up to Vend') }}
                    </button>
                </a>
                @endif
                </div>
            </div>
        </div>

        @if ($role->youtube_links)
            <div class="container mx-auto py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach (json_decode($role->youtube_links) as $link)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <iframe class="w-full h-64" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($link->url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between pb-6">
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
                @if($role->email)
                <div class="mt-2 flex items-center text-sm">
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"
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
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"
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
                    <svg class="mr-1.5 h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"
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

</body>