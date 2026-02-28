<x-app-layout :title="($event && $event->exists ? $event->translatedName() . ' | ' : '') . $role->name . ' | Event Schedule'">

    <noscript>
      <div style="background: #fff3cd; color: #856404; padding: 16px; text-align: center; font-size: 1rem;">
        JavaScript is required to use Event Schedule. Please enable JavaScript in your browser.
      </div>
    </noscript>

    @php
        $subdomain = $role->subdomain;
        if ($event && !isset($otherRole)) {
            $otherRole = $event->getOtherRole($subdomain);
        }
        $jsonLdFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
    @endphp

    <x-slot name="meta">
        @if (request()->embed || request('graphic') || (isset($event) && $event->exists && $event->is_private))
            <meta name="robots" content="noindex, nofollow">
        @else
            <meta name="robots" content="index, follow">
        @endif

        @if ($role->language_code != 'en')
            @php
                $hreflangBase = ($event && $event->exists) ? $event->getGuestUrl(false, $date ?? null) : $role->getGuestUrl();
            @endphp
            <link rel="alternate" hreflang="en" href="{{ $hreflangBase }}?lang=en">
            <link rel="alternate" hreflang="{{ $role->language_code }}" href="{{ $hreflangBase }}?lang={{ $role->language_code }}">
            <link rel="alternate" hreflang="x-default" href="{{ $hreflangBase }}?lang={{ $role->language_code }}">
        @endif

        @php
            $localeMap = ['en' => 'en_US', 'es' => 'es_ES', 'de' => 'de_DE', 'fr' => 'fr_FR', 'it' => 'it_IT', 'pt' => 'pt_BR', 'he' => 'he_IL', 'nl' => 'nl_NL', 'ar' => 'ar_SA', 'et' => 'et_EE', 'ru' => 'ru_RU', 'ro' => 'ro_RO'];
            $ogLocale = $localeMap[$role->language_code] ?? 'en_US';
        @endphp
        <meta property="og:locale" content="{{ $ogLocale }}">

        @if ($event && $event->exists && ($passwordGate ?? false))
            <meta name="description" content="{{ __('messages.event_password_required') }}">
            <meta property="og:type" content="event">
            <meta property="og:title" content="{{ __('messages.event_password_required') }}">
            <meta property="og:description" content="{{ __('messages.event_password_required') }}">
            <meta property="og:image" content="{{ config('app.url') . '/images/social/home.png' }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:url" content="{{ $event->getGuestUrl(false, $date) }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ __('messages.event_password_required') }}">
            <meta name="twitter:description" content="{{ __('messages.event_password_required') }}">
            <meta name="twitter:image" content="{{ config('app.url') . '/images/social/home.png' }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:site" content="@ScheduleEvent">
        @elseif ($event && $event->exists)
            @if ($role->language_code != 'en')
                <link rel="canonical" href="{{ $event->getGuestUrl(false, $date) }}?{{ 'lang=' . (is_valid_language_code(request()->lang) ? request()->lang : (session()->has('translate') ? 'en' : $role->language_code)) }}">
            @else
                <link rel="canonical" href="{{ $event->getGuestUrl(false, $date) }}">
            @endif
            <meta name="description" content="{{ $event->getMetaDescription($date) }}">
            <meta property="og:type" content="event">
            <meta property="og:title" content="{{ $event->translatedName() }}">
            <meta property="og:description" content="{{ $event->getMetaDescription($date) }}">
            @php $eventOgImage = $event->getImageUrl() ?: (config('app.url') . '/images/social/home.png'); @endphp
            <meta property="og:image" content="{{ $eventOgImage }}">
            @if (! $event->getImageUrl())
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            @endif
            <meta property="og:image:alt" content="{{ $event->translatedName() }}">
            <meta property="og:url" content="{{ $event->getGuestUrl(false, $date) }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:title" content="{{ $event->translatedName() }}">
            <meta name="twitter:description" content="{{ $event->getMetaDescription($date) }}">
            <meta name="twitter:image" content="{{ $eventOgImage }}">
            <meta name="twitter:image:alt" content="{{ $event->translatedName() }}">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:site" content="@ScheduleEvent">
        @elseif ($role->exists)
            @if ($role->language_code != 'en')
                <link rel="canonical" href="{{ $role->getGuestUrl() }}?{{ 'lang=' . (is_valid_language_code(request()->lang) ? request()->lang : (session()->has('translate') ? 'en' : $role->language_code)) }}">
            @else
                <link rel="canonical" href="{{ $role->getGuestUrl() }}">
            @endif
            @if ($description = Str::limit(trim(strip_tags($role->translatedDescription())), 155))
            <meta name="description" content="{{ $description }}">
            <meta property="og:description" content="{{ $description }}">
            <meta name="twitter:description" content="{{ $description }}">
            @else
            @php
                $description = __('messages.view_schedule_for', ['name' => $role->translatedName()]);
                if ($role->translatedShortDescription()) {
                    $description .= ' - ' . $role->translatedShortDescription();
                }
                if ($role->isVenue() && $role->shortAddress()) {
                    $description .= ' | ' . $role->shortAddress();
                }
                $description = Str::limit($description, 155);
            @endphp
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
            <meta property="og:image:alt" content="{{ $name ?? $role->translatedName() }}">
            <meta name="twitter:image" content="{{ $image }}">
            @else
            <meta property="og:image" content="{{ config('app.url') . '/images/social/home.png' }}">
            <meta property="og:image:width" content="1200">
            <meta property="og:image:height" content="630">
            <meta property="og:image:alt" content="{{ $name ?? $role->translatedName() }}">
            <meta name="twitter:image" content="{{ config('app.url') . '/images/social/home.png' }}">
            @endif
            <meta property="og:type" content="website">
            <meta property="og:url" content="{{ $role->getGuestUrl() }}">
            <meta property="og:site_name" content="Event Schedule">
            <meta name="twitter:card" content="summary_large_image">
            <meta name="twitter:site" content="@ScheduleEvent">
        @endif
    </x-slot>

    <x-slot name="head">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        @foreach($fonts as $font)
            <link href="https://fonts.googleapis.com/css2?family={{ str_replace(['_', ' '], '+', $font) }}:wght@400;700&display=swap" rel="stylesheet">
        @endforeach

        <style {!! nonce_attr() !!}>
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
                            background-image: url("{{ asset('images/backgrounds/' . $otherRole->background_image . '.webp') }}");
                            background-image: image-set(
                                url("{{ asset('images/backgrounds/' . $otherRole->background_image . '.webp') }}") type("image/webp"),
                                url("{{ asset('images/backgrounds/' . $otherRole->background_image . '.png') }}") type("image/png")
                            );
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
                            url("{{ asset('images/backgrounds/' . $role->background_image . '.webp') }}");
                            background-image:
                                @if (request()->graphic)
                                    linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                                @endif
                            image-set(
                                url("{{ asset('images/backgrounds/' . $role->background_image . '.webp') }}") type("image/webp"),
                                url("{{ asset('images/backgrounds/' . $role->background_image . '.png') }}") type("image/png")
                            );
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

        @if ($role->custom_css && $role->isPro())
        {!! strip_tags($role->custom_css) !!}
        @endif

        </style>

        @if ($event && $event->exists && $event->starts_at && !($passwordGate ?? false))
            @php
                // Use translation if available, otherwise fall back to event methods
                $eventName = (isset($translation) && $translation && $translation->name_translated) ? $translation->name_translated : $event->translatedName();
                $eventDescriptionRaw = (isset($translation) && $translation && $translation->description_translated) ? ($translation->description_html_translated ?: $translation->description_translated) : $event->translatedDescription();
                $eventDescription = trim(strip_tags($eventDescriptionRaw));
                if (empty($eventDescription)) {
                    $eventDescription = $event->translatedName() . ' - ' . __('messages.event');
                }
                $eventUrl = $event->getGuestUrl(false, $date ?? null);
                $eventImage = $event->getImageUrl() ?: (config('app.url') . '/images/social/home.png');
                $startDate = $event->getSchemaStartDate($date ?? null);
                $endDate = $event->getSchemaEndDate($date ?? null);
                $location = $event->getSchemaLocation();
                $offers = $event->getSchemaOffers();
                $organizer = $event->getSchemaOrganizer();
                $performers = $event->getSchemaPerformers();
                $eventStatus = $event->getSchemaEventStatus();
                $attendanceMode = $event->getSchemaAttendanceMode();
            @endphp

            <script type="application/ld+json" {!! nonce_attr() !!}>
            {
                "@context": "https://schema.org",
                "@type": "Event",
                "name": @json($eventName, $jsonLdFlags),
                "description": @json($eventDescription, $jsonLdFlags),
                "startDate": @json($startDate, $jsonLdFlags),
                "endDate": @json($endDate, $jsonLdFlags),
                "url": @json($eventUrl, $jsonLdFlags),
                "eventStatus": @json($eventStatus, $jsonLdFlags),
                "eventAttendanceMode": @json($attendanceMode, $jsonLdFlags),
                "organizer": @json($organizer, $jsonLdFlags),
                "offers": @json(count($offers) === 1 ? $offers[0] : $offers, $jsonLdFlags),
                "location": @json($location, $jsonLdFlags),
                "isAccessibleForFree": {{ $event->isFree() ? 'true' : 'false' }},
                "inLanguage": "{{ $role->language_code }}"
                @if ($eventImage)
                ,
                "image": @json($eventImage, $jsonLdFlags)
                @endif
                @if ($performers)
                ,
                "performer": @json(count($performers) === 1 ? $performers[0] : $performers, $jsonLdFlags)
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

            <script type="application/ld+json" {!! nonce_attr() !!}>
            {
                "@context": "https://schema.org",
                "@type": @json($schemaType, $jsonLdFlags),
                "name": @json($roleName, $jsonLdFlags)
                @if ($roleDescription)
                ,
                "description": @json($roleDescription, $jsonLdFlags)
                @endif
                ,
                "url": @json($roleUrl, $jsonLdFlags)
                @if ($roleImage)
                ,
                "image": @json($roleImage, $jsonLdFlags)
                @endif
                @if ($address)
                ,
                "address": @json($address, $jsonLdFlags)
                @endif
                @if (!empty($sameAs))
                ,
                "sameAs": @json($sameAs, $jsonLdFlags)
                @endif
                ,
                "inLanguage": "{{ $role->language_code }}"
            }
            </script>
        @endif

        @if ($event && $event->exists && $event->starts_at && !($passwordGate ?? false))
            <script type="application/ld+json" {!! nonce_attr() !!}>
            {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": [
                    {
                        "@type": "ListItem",
                        "position": 1,
                        "name": @json(__('messages.home')),
                        "item": "{{ marketing_url() }}"
                    },
                    {
                        "@type": "ListItem",
                        "position": 2,
                        "name": @json($role->translatedName(), $jsonLdFlags),
                        "item": "{{ $role->getGuestUrl() }}"
                    },
                    {
                        "@type": "ListItem",
                        "position": 3,
                        "name": @json($event->translatedName(), $jsonLdFlags),
                        "item": "{{ $event->getGuestUrl(false, $date ?? null) }}"
                    }
                ]
            }
            </script>
        @elseif ($role->exists)
            <script type="application/ld+json" {!! nonce_attr() !!}>
            {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": [
                    {
                        "@type": "ListItem",
                        "position": 1,
                        "name": @json(__('messages.home')),
                        "item": "{{ marketing_url() }}"
                    },
                    {
                        "@type": "ListItem",
                        "position": 2,
                        "name": @json($role->translatedName(), $jsonLdFlags),
                        "item": "{{ $role->getGuestUrl() }}"
                    }
                ]
            }
            </script>
        @endif

        @if ($event && $event->exists && !($passwordGate ?? false))
            @php
                $allVideos = $event->approvedVideos;
                $videoSchemaItems = [];
                foreach ($allVideos as $video) {
                    $videoId = \App\Utils\UrlUtils::extractYouTubeVideoId($video->youtube_url);
                    if ($videoId) {
                        $videoSchemaItems[] = [
                            '@type' => 'VideoObject',
                            'name' => $event->translatedName() . ($video->eventPart ? ' - ' . $video->eventPart->translatedName() : ''),
                            'description' => trim(strip_tags($event->translatedDescription())) ?: $event->translatedName(),
                            'thumbnailUrl' => 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg',
                            'uploadDate' => $video->created_at->toIso8601String(),
                            'contentUrl' => $video->youtube_url,
                            'embedUrl' => 'https://www.youtube-nocookie.com/embed/' . $videoId,
                        ];
                    }
                }
            @endphp
            @foreach ($videoSchemaItems as $videoSchema)
            <script type="application/ld+json" {!! nonce_attr() !!}>
            @json($videoSchema + ['@context' => 'https://schema.org'], $jsonLdFlags)
            </script>
            @endforeach
        @endif

        {{-- Meta Pixel for boosted events --}}
        @if ($event && $event->exists && $event->activeBoostCampaign)
        @php $metaPixelId = config('services.meta.pixel_id'); @endphp
        @if ($metaPixelId)
        <script {!! nonce_attr() !!}>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $metaPixelId }}');
            fbq('track', 'PageView');
            fbq('track', 'ViewContent', {
                content_ids: ['{{ $event->id }}'],
                content_name: @json($event->translatedName()),
                content_type: 'product',
                content_category: '{{ $event->getSchemaAttendanceMode() === "https://schema.org/OnlineEventAttendanceMode" ? "online_event" : "event" }}'
            });
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" /></noscript>
        @endif
        @endif

        {{ isset($head) ? $head : '' }}
    </x-slot>
    
    @php
        $isRtl = $role->isRtl();
        $isRoleRtl = in_array($role->language_code, ['ar', 'he']);
    @endphp

    <div class="flex-grow relative">
        @php
            $showMobileBanner = false;
            $mobileBannerUrl = '';

            if ($event && $otherRole && $otherRole->isClaimed() && $otherRole->background == 'image') {
                $showMobileBanner = true;
                $mobileBannerUrl = $otherRole->background_image
                    ? asset('images/backgrounds/' . $otherRole->background_image . '.webp')
                    : $otherRole->background_image_url;
            } elseif ($role->background == 'image') {
                $showMobileBanner = true;
                $mobileBannerUrl = $role->background_image
                    ? asset('images/backgrounds/' . $role->background_image . '.webp')
                    : $role->background_image_url;
            }
        @endphp

        @if (! request()->embed && $role->language_code != 'en')
            <div class="container mx-auto flex justify-end {{ $isRtl ? 'pl-5' : 'pr-5' }} pt-4">
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
                <span>{!! str_replace(':link', '<bdi dir="ltr"><a href="' . marketing_url() . '" target="_blank" rel="noopener" class="text-white hover:underline">' . marketing_domain() . '</a></bdi>',  __('messages.try_event_schedule')) !!}</span>
                <span>•</span>
                <span>{!! __('messages.supported_by', ['link' => '<a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer nofollow" class="text-white hover:underline" title="Leading small-business platform to manage invoices, expenses & tasks">Invoice Ninja</a>']) !!}</span>
            </p>
        @else
            <p class="text-[#F5F9FE] text-base text-center" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
                {!! str_replace(':link', '<bdi dir="ltr"><a href="' . marketing_url() . '" target="_blank" rel="noopener" class="text-white hover:underline">' . marketing_domain() . '</a></bdi>',  __('messages.try_event_schedule')) !!}
            </p>
        @endif
      </div>
    </footer>
    @endif

    @if (! request()->embed && ! config('app.is_nexus') && $role->showBranding())
    <div class="flex justify-{{ $isRtl ? 'start' : 'end' }} p-4">
        <a href="https://eventschedule.com" target="_blank" rel="noopener" title="{{ __('messages.powered_by_event_schedule') }}" class="block rounded-full bg-gray-400 p-[1px]">
            <img src="{{ url('/images/logo.webp') }}" alt="Event Schedule" class="h-6 w-6 rounded-full">
        </a>
    </div>
    @endif

</x-app-layout>