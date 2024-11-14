<x-app-layout :title="$role->name . ' | Event Schedule'">

    @php
        $subdomain = $role->subdomain;
        if ($event) {
            $otherRole = $event->getOtherRole($subdomain);
        }
    @endphp

    <x-slot name="meta">
        @if ($event && $event->exists) 
            @if ($event->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->description_html)) }}">
            @elseif ($event->role() && $event->role()->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->role()->description_html)) }}">
            @endif
            <meta property="og:title" content="{{ $event->name }}">
            <meta property="og:description" content="{{ $event->getMetaDescription($date) }}">
            <meta property="og:image" content="{{ $event->getImageUrl() }}">
            <meta property="og:url" content="{{ request()->url() }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $event->name }}">
            <meta name="twitter:description" content="{{ $event->getMetaDescription($date) }}">
            <meta name="twitter:image" content="{{ $event->getImageUrl() }}">
            <meta name="twitter:image:alt" content="{{ $event->name }}">
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
    </x-slot>

    <x-slot name="head">

        <link href="https://fonts.googleapis.com/css2?family={{ $role->font_family }}:wght@400;700&display=swap" rel="stylesheet">
        @if ($event && $event->role())
            <link href="https://fonts.googleapis.com/css2?family={{ $event->getOtherRole($role->subdomain)->font_family }}:wght@400;700&display=swap" rel="stylesheet">
        @endif

        <style>
        body {
            @media (prefers-color-scheme: dark) {
                color: #33383C !important;
            }
            @media (prefers-color-scheme: light) {
                color: #33383C !important;
            }
            font-family: '{{ $role->font_family }}', sans-serif !important;
            min-height: 100%;
            background-attachment: scroll;
            @if ($event && $otherRole && $otherRole->isClaimed())
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

        @if ($event && $otherRole)
            @if ($event->venue && $subdomain == $event->venue->subdomain)
            #event_title
            @else
            #venue_title
            @endif
            {
                font-family: '{{ $otherRole->font_family }}', sans-serif !important;
            }
        @endif
        </style>

    </x-slot>
    
    {{ $slot }}


    @if (! request()->embed)
    <footer class="bg-[#151B26]">
      <div
        class="container mx-auto flex flex-row justify-center items-center py-8 px-5"
      >
        <p class="text-[#F5F9FE] text-base text-center">
            {!! str_replace(':link', '<a href="' . url('/') . '" target="_blank" class="hover:underline">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
            •
            @if (($role->country_code == 'il' && $role->id != 6) || ($event && $event->venue && $event->venue->country_code == 'il' && $event->venue->id != 6))
            {!! str_replace(':link', '<a href="https://myjewishsoulmate.com" target="_blank" class="hover:underline">My Jewish Soulmate</a>',  __('messages.supported_by')) !!}
            @else
            {!! str_replace([':link1', ':link2'], ['<a href="https://invoiceninja.com" target="_blank" class="hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>', '<a href="https://mudeo.app" target="_blank" class="hover:underline" title="Make music together">mudeo</a>'],  __('messages.supported_by_both')) !!}
            @endif
        </p>
      </div>
    </footer>
    @endif

</x-app-layout>