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
                <link rel="canonical" href="{{ $event->getGuestUrl(false, $date) }}&{{ 'lang=' . (request()->lang ?? (session()->has('translate') ? 'en' : $role->language_code)) }}">
            @else
                <link rel="canonical" href="{{ $event->getGuestUrl(false, $date) }}">
            @endif
            @if ($event->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->translatedDescription())) }}">
            @elseif ($event->role() && $event->role()->description_html)
            <meta name="description" content="{{ trim(strip_tags($event->role()->translatedDescription())) }}">
            @endif
            <meta property="og:title" content="{{ $event->translatedName() }}">
            <meta property="og:description" content="{{ $event->getMetaDescription($date) }}">
            <meta property="og:image" content="{{ $event->getImageUrl() }}">
            <meta property="og:url" content="{{ $event->getGuestUrl(false, $date) }}">
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
            <meta property="og:url" content="{{ $role->getGuestUrl() }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:card" content="summary_large_image">
        @endif
    </x-slot>

    <x-slot name="head">

        @foreach($fonts as $font)
            <link href="https://fonts.googleapis.com/css2?family={{ str_replace(['_', ' '], '+', $font) }}:wght@400;700&display=swap" rel="stylesheet">
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif !important;
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
                    @if (!$showMobileBackground)
                    @media (min-width: 768px) {
                    @endif
                        @if ($otherRole->background_image)
                            background-image: url("{{ asset('images/backgrounds/' . $otherRole->background_image . '.png') }}");
                        @else
                            background-image: url("{{ $otherRole->background_image_url }}");
                        @endif
                        background-size: cover;
                        background-position: center;
                        height: 100%;
                        margin: 0;
                    @if (!$showMobileBackground)
                    }
                    @endif
                @endif
            @else
                @if ($role->background == 'gradient')
                    background-image: linear-gradient({{ $role->background_rotation }}deg, {{ $role->background_colors }});
                @elseif ($role->background == 'solid')
                    background-color: {{ $role->background_color }} !important;
                @elseif ($role->background == 'image')
                    @if (!$showMobileBackground)
                    @media (min-width: 768px) {
                    @endif
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
                    @if (!$showMobileBackground)
                    }
                    @endif
                @endif
            @endif
        }

        @if ($role->custom_css)
        {!! $role->custom_css !!}
        @endif

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
                $eventUrl = $event->getGuestUrl(false, $date ?? null);
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
    
    @php
        $isRtl = $role->isRtl();
    @endphp

    <div class="flex-grow relative">
        @php
            $showMobileBanner = false;
            $mobileBannerUrl = '';

            if ($event && $otherRole && $otherRole->isClaimed() && $otherRole->background == 'image') {
                $showMobileBanner = true;
                $mobileBannerUrl = $otherRole->background_image
                    ? asset('images/backgrounds/' . $otherRole->background_image . '.png')
                    : $otherRole->background_image_url;
            } elseif ($role->background == 'image') {
                $showMobileBanner = true;
                $mobileBannerUrl = $role->background_image
                    ? asset('images/backgrounds/' . $role->background_image . '.png')
                    : $role->background_image_url;
            }
        @endphp

        @if (! request()->embed && $role->language_code != 'en' && ! ($event && $event->exists))
            <div class="container mx-auto flex {{ $isRtl ? 'justify-start pl-5' : 'justify-end pr-5' }} pt-4">
                <div class="flex items-center rounded-full bg-gray-100 dark:bg-gray-800 p-1 text-sm shadow-md z-50 {{ $isRtl ? 'flex-row-reverse' : '' }}" translate="no">
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

    @if (! request()->embed && config('app.hosted') && $role->showBranding())
    <footer class="bg-gray-800">
      <div class="container mx-auto relative flex flex-row justify-center items-center py-5 px-5">
        <!-- Per the AAL license, please do not remove the link to Event Schedule -->
        @if (config('app.is_nexus'))
            <p class="text-[#F5F9FE] text-base text-center flex items-center justify-center gap-2 {{ $isRtl ? 'flex-row-reverse' : '' }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                <span>{!! str_replace(':link', '<a href="' . marketing_url() . '" target="_blank" class="text-white hover:underline">' . marketing_domain() . '</a>',  __('messages.try_event_schedule')) !!}</span>
                <span>•</span>
                <span>{!! __('messages.supported_by', ['link' => '<a href="https://invoiceninja.com" target="_blank" class="text-white hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>']) !!}</span>
            </p>
        @else
            <p class="text-[#F5F9FE] text-base text-center" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                {!! str_replace(':link', '<a href="' . marketing_url() . '" target="_blank" class="text-white hover:underline">' . marketing_domain() . '</a>',  __('messages.try_event_schedule')) !!}
            </p>
        @endif
      </div>
    </footer>
    @endif

    @if (! request()->embed && ! config('app.is_nexus') && $role->showBranding())
    <div class="flex justify-{{ $isRtl ? 'start' : 'end' }} p-4">
        <a href="https://eventschedule.com" target="_blank" title="{{ __('messages.powered_by_event_schedule') }}" class="block rounded-full bg-gray-400 p-[1px]">
            <img src="{{ url('/images/favicon.png') }}" alt="Event Schedule" class="h-6 w-6 rounded-full">
        </a>
    </div>
    @endif

</x-app-layout>