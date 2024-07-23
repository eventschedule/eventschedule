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
        <div class="flex items-center justify-between pb-6">
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
            {{ $role->description }}
        </div>

        <div class="flex items-center justify-between pb-6">
            <div class="py-6 text-xs">
                &copy; {{ now()->year }} {{ $role->name }}. {{ __('All rights reserved') }}.
            </div>
            <div>
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