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

        @foreach($fonts as $font)
            <link href="https://fonts.googleapis.com/css2?family={{ $font }}:wght@400;700&display=swap" rel="stylesheet">
        @endforeach

        <style>
        @if (request()->embed)
        html {
            height: 100%;
        }
        @endif

        main {
            height: 100%;
        }

        body {
            @media (prefers-color-scheme: dark) {
                color: #33383C !important;
            }
            @media (prefers-color-scheme: light) {
                color: #33383C !important;
            }
            font-family: '{{ isset($otherRole) && $otherRole ? $otherRole->font_family : $role->font_family }}', sans-serif !important;
            min-height: 100%;
            background-attachment: scroll;
            @if ($event && $otherRole && $otherRole->isClaimed())
                @if ($otherRole->background == 'gradient')
                    background-image: linear-gradient({{ $otherRole->background_rotation }}deg, {{ $otherRole->background_colors }});
                @elseif ($otherRole->background == 'solid')
                    background-color: {{ $otherRole->background_color }} !important;
                @elseif ($otherRole->background == 'image')
                    @if ($otherRole->background_image)
                        background-image: url("{{ asset('images/backgrounds/' . $otherRole->background_image . '.png') }}");
                    @else
                        background-image: url("{{ $otherRole->background_image_url }}");
                    @endif
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    height: 100%;
                    margin: 0;
                @endif
            @else
                @if ($role->background == 'gradient')
                    background-image: linear-gradient({{ $role->background_rotation }}deg, {{ $role->background_colors }});
                @elseif ($role->background == 'solid')
                    background-color: {{ $role->background_color }} !important;
                @elseif ($role->background == 'image')
                    @if ($role->background_image)
                        background-image: url("{{ asset('images/backgrounds/' . $role->background_image . '.png') }}");
                    @else
                        background-image: url("{{ $role->background_image_url }}");
                    @endif
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    height: 100%;
                    margin: 0;
                @endif
            @endif
        }
        </style>

        {{ isset($head) ? $head : '' }}
    </x-slot>
    
    @if (! request()->embed)
    <header class="bg-white dark:bg-[#151B26]">
        <div
        class="container mx-auto flex flex-row justify-between items-center py-7 px-5"
        >
        <a href="https://eventschedule.com" target="_blank">
            <img class="h-10 w-auto" src="{{ url('images/black_logo_stacked.png') }}"/>
        </a> 
        <div class="flex flex-row gap-x-3 md:gap-x-10">
            <a
            href="{{ config('app.url') . route('login', [], false) }}"
            class="inline-flex items-center justify-center"
            >
            <button
                type="button"
                name="login"
                style="color: {{ $otherRole && $otherRole->accent_color ? $otherRole->accent_color : ($role && $role->accent_color ? $role->accent_color : '#4E81FA') }}"
                class="text-base duration-300 hover:opacity-90 disabled:cursor-not-allowed"
            >
                {{ __('messages.log_in') }}
            </button>
            </a>
            <a
            href="{{ config('app.url') . route('sign_up', [], false) }}"
            class="inline-flex items-center justify-center"
            >
            <button
                type="button"
                name="sign-up"
                style="background-color: {{ $otherRole && $otherRole->accent_color ? $otherRole->accent_color : ($role && $role->accent_color ? $role->accent_color : '#4E81FA') }}"
                class="inline-flex items-center rounded-md px-5 py-3 hover:opacity-90 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
            >
                {{ __('messages.sign_up') }}
            </button>
            </a>
        </div>
        </div>
    </header>
    @endif

    {{ $slot }}

    @if (! request()->embed)
    <footer class="bg-[#151B26]">
      <div
        class="container mx-auto flex flex-row justify-center items-center py-8 px-5"
      >
        <p class="text-[#F5F9FE] text-base text-center">
            {!! str_replace(':link', '<a href="' . config('app.url') . '" target="_blank" class="hover:underline">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
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