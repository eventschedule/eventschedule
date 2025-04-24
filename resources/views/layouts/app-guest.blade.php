<x-app-layout :title="$role->name . ' | Event Schedule'">

    @php
        $subdomain = $role->subdomain;
        if ($event) {
            $otherRole = $event->getOtherRole($subdomain);
        }
    @endphp

    <x-slot name="meta">
        @if ($role->language_code != 'en')
            <link rel="alternate" hreflang="en" href="{{ str_replace('http://', 'https://', request()->fullUrlWithQuery(['lang' => 'en'])) }}">
            <link rel="alternate" hreflang="{{ $role->language_code }}" href="{{ str_replace('http://', 'https://', request()->fullUrlWithQuery(['lang' => $role->language_code])) }}">
        @endif

        @if ($event && $event->exists) 
            @if ($role->language_code != 'en')
                <link rel="canonical" href="{{ $event->getGuestUrl() }}&{{ 'lang=' . (request()->lang ?? (session()->has('translate') ? 'en' : $role->language_code)) }}">
            @else
                <link rel="canonical" href="{{ $event->getGuestUrl() }}">
            @endif
            @if ($event->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->translatedDescription())) }}">
            @elseif ($event->role() && $event->role()->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->role()->translatedDescription())) }}">
            @endif
            <meta property="og:title" content="{{ $event->translatedName() }}">
            <meta property="og:description" content="{{ $event->getMetaDescription($date) }}">
            <meta property="og:image" content="{{ $event->getImageUrl() }}">
            <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $event->translatedName() }}">
            <meta name="twitter:description" content="{{ $event->getMetaDescription($date) }}">
            <meta name="twitter:image" content="{{ $event->getImageUrl() }}">
            <meta name="twitter:image:alt" content="{{ $event->translatedName() }}">
            <meta name="twitter:card" content="summary_large_image">
        @elseif ($role->exists)
            @if ($role->language_code != 'en')
                <link rel="canonical" href="{{ $role->getGuestUrl() }}?{{ 'lang=' . (request()->lang ?? (session()->has('translate') ? 'en' : $role->language_code)) }}">
            @else
                <link rel="canonical" href="{{ $role->getGuestUrl() }}">
            @endif
            <meta name="description" content="{{ trim(strip_tags($role->translatedDescription())) }}">
            <meta property="og:title" content="{{ $role->translatedName() }}">
            <meta property="og:description" content="{{ trim(strip_tags($role->translatedDescription())) }}">
            <meta property="og:image" content="{{ $role->profile_image_url }}">
            <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $role->translatedName() }}">
            <meta name="twitter:description" content="{{ trim(strip_tags($role->translatedDescription())) }}">
            <meta name="twitter:image" content="{{ $role->profile_image_url }}">
            <meta name="twitter:image:alt" content="{{ $role->translatedName() }}">
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
    
    @if (! request()->embed && $role->showBranding())
        <header class="bg-[#f9fafb] dark:bg-[#151B26]">
            <div
            class="container mx-auto flex flex-row justify-between items-center py-7 pr-5"
            >
                <a href="https://www.eventschedule.com" target="_blank">
                    <x-application-logo />
                </a> 
                <div class="flex flex-row items-center gap-x-3 md:gap-x-12">
                    @if ($role->language_code != 'en')
                        <div class="flex items-center rounded-full bg-gray-100 dark:bg-gray-800 p-1 text-sm" translate="no">
                            @if(session()->has('translate'))
                                <span class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm px-3 py-1.5 rounded-full font-medium">EN</span>
                                <a href="{{ request()->url() }}?lang={{ $role->language_code }}" 
                                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 px-3 py-1.5 rounded-full font-medium transition-all duration-200">
                                    {{ strtoupper($role->language_code) }}
                                </a>
                            @else
                                <a href="{{ request()->url() }}?lang=en" 
                                   class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 px-3 py-1.5 rounded-full font-medium transition-all duration-200">
                                    EN
                                </a>
                                <span class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm px-3 py-1.5 rounded-full font-medium">{{ strtoupper($role->language_code) }}</span>
                            @endif
                        </div>
                    @endif            
                </div>
            </div>
        </header>
    @elseif (! request()->embed && $role->language_code != 'en' && ! isset($event))
        <div class="container mx-auto flex justify-end pr-5 pt-4">
            <div class="flex items-center rounded-full bg-gray-100 dark:bg-gray-800 p-1 text-sm shadow-md z-50" translate="no">
                @if(session()->has('translate'))
                    <span class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm px-3 py-1.5 rounded-full font-medium">EN</span>
                    <a href="{{ str_replace('http://', 'https://', request()->url()) }}?lang={{ $role->language_code }}" 
                       class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 px-3 py-1.5 rounded-full font-medium transition-all duration-200">
                        {{ strtoupper($role->language_code) }}
                    </a>
                @else
                    <a href="{{ str_replace('http://', 'https://', request()->url()) }}?lang=en" 
                       class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 px-3 py-1.5 rounded-full font-medium transition-all duration-200">
                        EN
                    </a>
                    <span class="bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm px-3 py-1.5 rounded-full font-medium">{{ strtoupper($role->language_code) }}</span>
                @endif
            </div>
        </div>    
    @endif

    {{ $slot }}

    @if (! request()->embed && $role->showBranding())
    <footer class="bg-[#151B26]">
      <div
        class="container mx-auto flex flex-row justify-center items-center py-8 px-5"
      >
        <p class="text-[#F5F9FE] text-base text-center">
            <!-- Per the AAL license, please do not remove the link to Event Schedule -->
            {!! str_replace(':link', '<a href="https://www.eventschedule.com" target="_blank" class="hover:underline">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
            @if (config('app.hosted'))
                •
                @if (config('app.custom_footer') && (($role->country_code == 'il' && $role->id != 6) || ($event && $event->venue && $event->venue->country_code == 'il' && $event->venue->id != 6)))
                    {!! config('app.custom_footer') !!}
                @else
                    {!! str_replace([':link1', ':link2'], [
                        '<a href="https://invoiceninja.com" target="_blank" class="hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>', 
                        '<a href="https://mudeo.app" target="_blank" class="hover:underline" title="Make music together">mudeo</a>'
                    ],  __('messages.supported_by_both')) !!}
                @endif
            @endif
        </p>
      </div>
    </footer>
    @endif

</x-app-layout>