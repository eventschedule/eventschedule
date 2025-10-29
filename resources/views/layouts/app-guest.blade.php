<x-app-layout :title="$role->name . ' | Event Schedule'" :force-light="true">

    <noscript>
      <div style="background: #fff3cd; color: #856404; padding: 16px; text-align: center; font-size: 1rem;">
        JavaScript is required to use Event Schedule. Please enable JavaScript in your browser.
      </div>
    </noscript>

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
            @php
                $canonicalEventUrl = $event->getGuestUrl();
                if ($role->language_code != 'en') {
                    $canonicalEventUrl = \App\Utils\UrlUtils::appendQueryParameters($canonicalEventUrl, [
                        'lang' => request()->lang ?? (session()->has('translate') ? 'en' : $role->language_code),
                    ]);
                }
            @endphp
            <link rel="canonical" href="{{ $canonicalEventUrl }}">
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
            @php
                $canonicalRoleUrl = $role->getGuestUrl();
                if ($role->language_code != 'en') {
                    $canonicalRoleUrl = \App\Utils\UrlUtils::appendQueryParameters($canonicalRoleUrl, [
                        'lang' => request()->lang ?? (session()->has('translate') ? 'en' : $role->language_code),
                    ]);
                }
            @endphp
            <link rel="canonical" href="{{ $canonicalRoleUrl }}">
            @if ($description = trim(strip_tags($role->translatedDescription())))
            <meta name="description" content="{{ $description }}">
            <meta property="og:description" content="{{ $description }}">
            <meta name="twitter:description" content="{{ $description }}">
            @endif
            @if ($name = $role->translatedName())
            <meta property="og:title" content="{{ $name }}">
            <meta name="twitter:title" content="{{ $name }}">
            <meta name="twitter:image:alt" content="{{ $name }}">
            @endif
            @if ($image = $role->profile_image_url)
            <meta property="og:image" content="{{ $image }}">
            <meta name="twitter:image" content="{{ $image }}">
            @endif
            <meta property="og:url" content="{{ str_replace('http://', 'https://', request()->url()) }}">
            <meta property="og:site_name" content="Event Schedule">
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
            color: #33383C !important;
            font-family: '{{ isset($otherRole) && $otherRole ? $otherRole->font_family : $role->font_family }}', sans-serif !important;
            min-height: 100vh;
            background-attachment: scroll;
            display: flex;
            flex-direction: column;
            @if ($event && $otherRole && $otherRole->isClaimed())
                @if ($otherRole->background == 'gradient')
                    background-image: linear-gradient({{ $otherRole->background_rotation }}deg, {{ $otherRole->background_colors }});
                @elseif ($otherRole->background == 'solid')
                    background-color: {{ $otherRole->background_color }} !important;
                @elseif ($otherRole->background == 'image')
                    @if ($otherRole->background_image)
                        background-image: url("{{ asset('images/backgrounds/' . $otherRole->background_image . '.png') }}");
                    @else
                        background-image: url("{{ storage_asset_url($otherRole->background_image_url) }}");
                    @endif
                    background-size: cover;
                    background-position: center;
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
                        background-image:
                            @if (request()->graphic)
                                linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                            @endif
                        url("{{ asset('images/backgrounds/' . $role->background_image . '.png') }}");
                    @else
                        background-image:
                            @if (request()->graphic)
                                linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                            @endif
                        url("{{ storage_asset_url($role->background_image_url) }}");
                    @endif
                    background-size: cover;
                    background-position: center;
                    height: 100%;
                    margin: 0;            
                @endif
            @endif
        }

        </style>

        {{ isset($head) ? $head : '' }}
    </x-slot>
    
    <div class="flex-grow">
        @if (! request()->embed && $role->showBranding() && config('app.hosted'))
            <header class="bg-[#f9fafb] dark:bg-gray-800">
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
        @elseif (! request()->embed && $role->language_code != 'en' && ! ($event && $event->exists))
            <div class="container mx-auto flex justify-end pr-5 pt-4">
                <div class="flex items-center rounded-full bg-gray-100 dark:bg-gray-800 p-1 text-sm shadow-md z-50" translate="no">
                    @if(session()->has('translate') || request()->lang == 'en')
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
    </div>

    <x-slot name="footer">
        @if (request()->embed)
            <div class="py-3 text-center text-xs text-gray-500 dark:text-gray-400">
                {!! str_replace(':link', '<a href="https://www.eventschedule.com" class="hover:underline" target="_blank">EventSchedule</a>', __('messages.powered_by_eventschedule')) !!}
            </div>
        @else
            <footer class="bg-gray-800">
                <div class="container mx-auto flex flex-row justify-center items-center py-5 px-5">
                    <p class="text-[#F5F9FE] text-sm sm:text-base text-center">
                        {!! str_replace(':link', '<a href="https://www.eventschedule.com" target="_blank" class="hover:underline">EventSchedule</a>', __('messages.powered_by_eventschedule')) !!}
                        @if ($role->showBranding())
                            <span class="block sm:inline sm:ml-2">
                                {!! str_replace(':link', '<a href="https://www.eventschedule.com" target="_blank" class="hover:underline">eventschedule.com</a>', __('messages.try_event_schedule')) !!}
                            </span>
                            @if (config('app.hosted'))
                                <span class="block sm:inline sm:ml-2">
                                    {!! __('messages.supported_by', ['link' => '<a href="https://invoiceninja.com" target="_blank" class="hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>']) !!}
                                </span>
                            @endif
                        @endif
                    </p>
                </div>
            </footer>
        @endif
    </x-slot>

</x-app-layout>