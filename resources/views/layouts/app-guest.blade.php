<x-app-layout :title="$role->name . ' | Event Schedule'">

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
            @media (prefers-color-scheme: dark) {
                color: #33383C !important;
            }
            @media (prefers-color-scheme: light) {
                color: #33383C !important;
            }
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
                        background-image: url("{{ $otherRole->background_image_url }}");
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
                        url("{{ $role->background_image_url }}");
                    @endif
                    background-size: cover;
                    background-position: center;
                    height: 100%;
                    margin: 0;            
                @endif
            @endif
        }

        </style>

        @if ($event && $event->exists && $event->starts_at)
            @php
                // Use translation if available, otherwise fall back to event methods
                $eventName = (isset($translation) && $translation && $translation->name_translated) ? $translation->name_translated : $event->translatedName();
                $eventDescriptionRaw = (isset($translation) && $translation && $translation->description_translated) ? $translation->description_translated : $event->translatedDescription();
                $eventDescription = trim(strip_tags($eventDescriptionRaw));
                if (empty($eventDescription)) {
                    $eventDescription = $event->translatedName() . ' - ' . __('messages.event');
                }
                $eventUrl = $event->getGuestUrl();
                $eventImage = $event->getImageUrl();
                $startDate = $event->getSchemaStartDate($date ?? null);
                $endDate = $event->getSchemaEndDate($date ?? null);
                $location = $event->getSchemaLocation();
                $offers = $event->getSchemaOffers();
                $organizer = $event->getSchemaOrganizer();
                $performers = $event->getSchemaPerformers();
                $eventStatus = $event->getSchemaEventStatus();
            @endphp

            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Event",
                "name": {!! json_encode($eventName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "description": {!! json_encode($eventDescription, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "startDate": {!! json_encode($startDate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "endDate": {!! json_encode($endDate, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "url": {!! json_encode($eventUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "eventStatus": {!! json_encode($eventStatus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "organizer": {!! json_encode($organizer, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "offers": {!! json_encode(count($offers) === 1 ? $offers[0] : $offers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "location": {!! json_encode($location, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @if ($eventImage)
                ,
                "image": {!! json_encode($eventImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @endif
                @if ($performers)
                ,
                "performer": {!! json_encode(count($performers) === 1 ? $performers[0] : $performers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @endif
            }
            </script>
        @elseif ($role->exists)
            @php
                $roleName = $role->translatedName();
                $roleDescription = trim(strip_tags($role->translatedDescription()));
                $roleUrl = $role->getGuestUrl();
                $roleImage = $role->profile_image_url;
                
                // Determine schema type based on role type
                $schemaType = $role->isVenue() ? 'Organization' : ($role->isCurator() ? 'Organization' : 'Person');
                
                // Build address if venue
                $address = null;
                if ($role->isVenue() && ($role->formatted_address || $role->translatedAddress1() || $role->translatedCity())) {
                    $address = ['@type' => 'PostalAddress'];
                    if ($role->translatedAddress1()) {
                        $address['streetAddress'] = $role->translatedAddress1();
                        if ($role->translatedAddress2()) {
                            $address['streetAddress'] .= ', ' . $role->translatedAddress2();
                        }
                    }
                    if ($role->translatedCity()) {
                        $address['addressLocality'] = $role->translatedCity();
                    }
                    if ($role->translatedState()) {
                        $address['addressRegion'] = $role->translatedState();
                    }
                    if ($role->postal_code) {
                        $address['postalCode'] = $role->postal_code;
                    }
                    if ($role->country_code) {
                        $address['addressCountry'] = $role->country_code;
                    }
                }
                
                // Get social links
                $sameAs = [];
                if ($role->social_links) {
                    $socialLinks = json_decode($role->social_links, true);
                    if (is_array($socialLinks)) {
                        foreach ($socialLinks as $link) {
                            if (isset($link['url']) && $link['url']) {
                                $sameAs[] = $link['url'];
                            }
                        }
                    }
                }
            @endphp

            <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": {!! json_encode($schemaType, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!},
                "name": {!! json_encode($roleName, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @if ($roleDescription)
                ,
                "description": {!! json_encode($roleDescription, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @endif
                ,
                "url": {!! json_encode($roleUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @if ($roleImage)
                ,
                "image": {!! json_encode($roleImage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @endif
                @if ($address)
                ,
                "address": {!! json_encode($address, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @endif
                @if (!empty($sameAs))
                ,
                "sameAs": {!! json_encode($sameAs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                @endif
            }
            </script>
        @endif

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

    @if (! request()->embed && $role->showBranding())
    <footer class="bg-gray-800">
      <div
        class="container mx-auto flex flex-row justify-center items-center py-5 px-5"
      >
        <p class="text-[#F5F9FE] text-base text-center">
            <!-- Per the AAL license, please do not remove the link to Event Schedule -->
            {!! str_replace(':link', '<a href="https://www.eventschedule.com" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">eventschedule.com</a>',  __('messages.try_event_schedule')) !!}
            @if (config('app.hosted'))
                •
                {!! __('messages.supported_by', ['link' => '<a href="https://invoiceninja.com" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>']) !!}
            @endif
        </p>
      </div>
    </footer>
    @endif

</x-app-layout>