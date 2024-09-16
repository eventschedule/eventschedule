<x-app-layout :role="$role" :event="$event" :date="$date">

    @php
        $subdomain = $role->subdomain;
        if ($event) {
            $otherRole = $event->getOtherRole($subdomain);
        }
    @endphp

    <x-slot name="head">

        @if ($event && $event->exists) 
            @if ($event->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->description_html)) }}">
            @else ($event->role->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->role->description_html)) }}">
            @endif
            <meta property="og:title" content="{{ $event->role->name }}">
            <meta property="og:description" content="{{ $event->getMetaDescription($date) }}">
            <meta property="og:image" content="{{ $event->getImageUrl() }}">
            <meta property="og:url" content="{{ request()->url() }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $event->role->name }}">
            <meta name="twitter:description" content="{{ $event->getMetaDescription($date) }}">
            <meta name="twitter:image" content="{{ $event->getImageUrl() }}">
            <meta name="twitter:image:alt" content="{{ $event->role->name }}">
            <meta name="twitter:card" content="summary_large_image">
        @elseif ($role->exists)
            <meta name="description" content="{{ trim(strip_tags($role->description_html)) }}">
            <meta property="og:title" content="{{ $role->name }}">
            <meta property="og:description" content="{{ trim(strip_tags($role->description_html)) }}">
            <meta property="og:image" content="{{ $role->profile_image_url }}">
            <meta property="og:url" content="{{ request()->url() }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $role->name }}">
            <meta name="twitter:description" content="{{ trim(strip_tags($role->description_html)) }}">
            <meta name="twitter:image" content="{{ $role->profile_image_url }}">
            <meta name="twitter:image:alt" content="{{ $role->name }}">
            <meta name="twitter:card" content="summary_large_image">
        @endif

        <link href="https://fonts.googleapis.com/css2?family={{ $role->font_family }}:wght@400;700&display=swap" rel="stylesheet">
        @if ($event)
            <link href="https://fonts.googleapis.com/css2?family={{ $event->getOtherRole($role->subdomain)->font_family }}:wght@400;700&display=swap" rel="stylesheet">
        @endif

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

    </x-slot>

    {{ $slot }}

</x-app-layout>