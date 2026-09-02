<x-app-layout :title="$guestTitle()">

    <noscript>
      <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 p-4 text-center text-base">
        JavaScript is required to use Event Schedule. Please enable JavaScript in your browser.
      </div>
    </noscript>

    @php
        $subdomain = $role->subdomain;
        if ($event && !isset($otherRole)) {
            $otherRole = $event->getOtherRole($subdomain);
        }
        $jsonLdFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
        // Event text resolved by language rather than by a "showing translation" boolean, which
        // inverts for an aggregated event whose language pair differs from this schedule's.
        // $role's OWN translated*() calls below need no such treatment - $guestLang came from it.
        $guestLang = $role->displayLanguageCode();
        $guestEventName = ($event && $event->exists) ? $event->nameInLanguage($guestLang, $role) : null;
        $guestEventDescriptionHtml = ($event && $event->exists) ? $event->descriptionHtmlInLanguage($guestLang, $role) : null;
        $isUnverifiedRole = $role && $role->exists
            && !($role->email && $role->email_verified_at)
            && !($role->phone && $role->phone_verified_at);

        // Demo schedules exist to be LOOKED AT from /examples, not to rank. They are seeded
        // fabricated venues and events, and Googlebot was spending a quarter of its crawl on them:
        // countyfairgrounds, weekendyogaretreat, battleofthebands, karateclub and painting alone
        // took ~165k of 637k requests in 89 days, several of them out-crawling the tenant that
        // earns 44% of the whole property's clicks. Thousands of thin fabricated event pages are
        // also a site-wide quality signal this domain should not be sending. They stay fully
        // viewable and linked from /examples - only the indexing goes.
        $isDemoRole = is_demo_role($role);

        // Language variants. The PRIMARY language lives on the clean URL - that is the URL the
        // sitemap submits and the one people link to - so only the alternate language carries
        // ?lang=. Appending it unconditionally made every submitted URL a non-canonical
        // "alternate": Google was told to index a URL that then pointed it at a second one, which
        // is what the property's ~99k "Alternate page with proper canonical tag" rows were, and it
        // doubled the crawlable URL space for nothing.
        //
        // ?lang[]=x hands is_valid_language_code() an array against its ?string signature. The
        // old call site only ran inside the alternate-language branch; this one runs on every
        // guest render, so it takes the same is_string() guard RoleController::viewGuest() uses.
        $guestPrimaryLang = $role->language_code;
        $guestTargetLang = $role->translation_language_code ?: 'en';
        $guestHasAltLang = $guestPrimaryLang != $guestTargetLang;
        $guestRequestedLang = is_string(request()->lang) ? request()->lang : null;
        $guestShownLang = is_valid_language_code($guestRequestedLang)
            ? $guestRequestedLang
            : (session()->has('translate') ? $guestTargetLang : $guestPrimaryLang);
        $guestLangSuffix = ($guestHasAltLang && $guestShownLang !== $guestPrimaryLang)
            ? '?lang=' . $guestShownLang
            : '';
    @endphp

    <x-slot name="meta">
        @if ($noIndex || request()->embed || request('graphic') || (isset($event) && $event->exists && ($event->is_private || $event->is_draft)) || $isUnverifiedRole || $isDemoRole)
            <meta name="robots" content="noindex, nofollow">
        @else
            <meta name="robots" content="index, follow">
        @endif

        @if ($guestHasAltLang)
            @php
                $hreflangBase = ($event && $event->exists) ? $event->getCanonicalUrl($date ?? null) : $role->getCanonicalUrl();
            @endphp
            <link rel="alternate" hreflang="{{ $guestTargetLang }}" href="{{ $hreflangBase }}?lang={{ $guestTargetLang }}">
            <link rel="alternate" hreflang="{{ $guestPrimaryLang }}" href="{{ $hreflangBase }}">
            <link rel="alternate" hreflang="x-default" href="{{ $hreflangBase }}">
        @endif

        @php
            $localeMap = ['en' => 'en_US', 'es' => 'es_ES', 'de' => 'de_DE', 'fr' => 'fr_FR', 'it' => 'it_IT', 'pt' => 'pt_BR', 'he' => 'he_IL', 'nl' => 'nl_NL', 'ar' => 'ar_SA', 'et' => 'et_EE', 'ru' => 'ru_RU', 'ro' => 'ro_RO'];
            $ogLocale = $localeMap[$role->language_code] ?? 'en_US';
        @endphp
        <meta property="og:locale" content="{{ $ogLocale }}">

        @if ($event && $event->exists && ($passwordGate ?? false))
            @php
                // The schedule's own logo, never the event's flyer: this page is password gated,
                // so its imagery is precisely what the owner chose not to make public. And never
                // /images/social/home.jpg, which put an Event Schedule advert in the WhatsApp
                // preview of somebody else's private event.
                $gateOgImage = $role->profile_image_url ?: null;
            @endphp
            <meta name="description" content="{{ __('messages.event_password_required') }}">
            <meta property="og:type" content="event">
            <meta property="og:title" content="{{ __('messages.event_password_required') }}">
            <meta property="og:description" content="{{ __('messages.event_password_required') }}">
            @if ($gateOgImage)
            <meta property="og:image" content="{{ $gateOgImage }}">
            @endif
            <meta property="og:url" content="{{ $event->getCanonicalUrl($date) }}">
            <meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
            <meta name="twitter:title" content="{{ __('messages.event_password_required') }}">
            <meta name="twitter:description" content="{{ __('messages.event_password_required') }}">
            @if ($gateOgImage)
            <meta name="twitter:image" content="{{ $gateOgImage }}">
            @endif
            <meta name="twitter:card" content="{{ $gateOgImage ? 'summary_large_image' : 'summary' }}">
            <meta name="twitter:site" content="@ScheduleEvent">
        @elseif ($event && $event->exists && !$event->is_draft)
            @if ($galleryMode)
                @php
                    $galleryTitle = $guestEventName . ' - ' . __('messages.photo_gallery');
                    $firstPhoto = $event->approvedPhotos->first();
                    if (!$firstPhoto) {
                        foreach ($event->parts as $part) {
                            $firstPhoto = $part->approvedPhotos->first();
                            if ($firstPhoto) break;
                        }
                    }
                    // Never /images/social/home.jpg: an event with no photo, no flyer and no
                    // schedule or venue logo advertises no image, and the scraper falls back to
                    // the page's own contents rather than to an advert of ours.
                    $galleryOgImage = $firstPhoto ? $firstPhoto->photo_url : $event->getImageUrl();
                @endphp
                <link rel="canonical" href="{{ $event->getCanonicalPhotoGalleryUrl($date) }}">
                <meta name="description" content="{{ $galleryTitle }}">
                <meta property="og:type" content="website">
                <meta property="og:title" content="{{ $galleryTitle }}">
                <meta property="og:description" content="{{ $event->getMetaDescription($date, $guestLang, $role) }}">
                @if ($galleryOgImage)
                <meta property="og:image" content="{{ $galleryOgImage }}">
                <meta property="og:image:alt" content="{{ $galleryTitle }}">
                @endif
                <meta property="og:url" content="{{ $event->getCanonicalPhotoGalleryUrl($date) }}">
                <meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
                <meta name="twitter:title" content="{{ $galleryTitle }}">
                <meta name="twitter:description" content="{{ $event->getMetaDescription($date, $guestLang, $role) }}">
                @if ($galleryOgImage)
                <meta name="twitter:image" content="{{ $galleryOgImage }}">
                <meta name="twitter:image:alt" content="{{ $galleryTitle }}">
                @endif
                <meta name="twitter:card" content="{{ $galleryOgImage ? 'summary_large_image' : 'summary' }}">
                <meta name="twitter:site" content="@ScheduleEvent">
            @else
            <link rel="canonical" href="{{ $event->getCanonicalUrl($date) }}{{ $guestLangSuffix }}">
            <meta name="description" content="{{ $event->getMetaDescription($date, $guestLang, $role) }}">
            <meta property="og:type" content="event">
            <meta property="og:title" content="{{ $guestEventName }}">
            <meta property="og:description" content="{{ $event->getMetaDescription($date, $guestLang, $role) }}">
            {{-- getImageUrl() already cascades flyer -> schedule logo -> venue logo, so null here
                 means the owner has no image anywhere. Advertising none lets the scraper fall
                 back to their own page, which beats handing it an advert of ours. --}}
            @php $eventOgImage = $event->getImageUrl(); @endphp
            @if ($eventOgImage)
            <meta property="og:image" content="{{ $eventOgImage }}">
            <meta property="og:image:alt" content="{{ $guestEventName }}">
            @endif
            <meta property="og:url" content="{{ $event->getCanonicalUrl($date) }}">
            <meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
            <meta name="twitter:title" content="{{ $guestEventName }}">
            <meta name="twitter:description" content="{{ $event->getMetaDescription($date, $guestLang, $role) }}">
            @if ($eventOgImage)
            <meta name="twitter:image" content="{{ $eventOgImage }}">
            <meta name="twitter:image:alt" content="{{ $guestEventName }}">
            @endif
            <meta name="twitter:card" content="{{ $eventOgImage ? 'summary_large_image' : 'summary' }}">
            <meta name="twitter:site" content="@ScheduleEvent">
            @endif
        @elseif ($role->exists)
            <link rel="canonical" href="{{ $role->getCanonicalUrl() }}{{ $guestLangSuffix }}">
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
            @endif
            {{-- No @else: a schedule with no logo used to advertise US in its own link preview.
                 Advertising nothing is the right fallback. It does not guarantee a picture-less
                 card - Facebook's crawler will pick one out of the page body - but whatever it
                 finds there is the owner's, which an advert of ours never is. --}}
            @if ($image = $role->profile_image_url)
            <meta property="og:image" content="{{ $image }}">
            <meta property="og:image:alt" content="{{ $name ?? $role->translatedName() }}">
            <meta name="twitter:image" content="{{ $image }}">
            <meta name="twitter:image:alt" content="{{ $name ?? $role->translatedName() }}">
            @endif
            <meta property="og:type" content="website">
            <meta property="og:url" content="{{ $role->getCanonicalUrl() }}">
            <meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
            <meta name="twitter:card" content="{{ $role->profile_image_url ? 'summary_large_image' : 'summary' }}">
            <meta name="twitter:site" content="@ScheduleEvent">
        @endif
    </x-slot>

    <x-slot name="head">

        {{-- Use the schedule's logo as the favicon on its guest pages (Pro/Enterprise) --}}
        @if ($role->isPro() && $role->profile_image_url)
            <link rel="icon" href="{{ $role->profile_image_url }}">
            <link rel="apple-touch-icon" href="{{ $role->profile_image_url }}">
        @endif

        {{-- This schedule's own manifest, never the platform one. Not plan-gated, unlike the
             favicon above: that gate chooses between a tenant's logo and a neutral default,
             whereas the alternative here is showing OUR logo full screen to their audience. --}}
        @include('partials.web-app-manifest', ['manifestRole' => $role])

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

        .gp-banner a {
            text-decoration: underline;
            font-weight: 600;
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
            background-attachment: {{ (($event && !request()->embed && !request()->graphic) || $role->activeEventLayout() === 'list') ? 'fixed' : 'scroll' }};
            display: flex;
            flex-direction: column;
            @if ($event && $otherRole && $otherRole->isClaimed() && $otherRole->hasConfiguredBackground())
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

        /* GP Language Switcher */
        .gp-lang-switcher { background-color: #f3f4f6; }
        .gp-lang-active { background-color: #fff; color: #111827; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .gp-lang-inactive { color: #6b7280; }
        .gp-lang-inactive:hover { color: #374151; }
        .dark .gp-lang-switcher { background: linear-gradient(135deg, rgb(var(--ap-border)), rgb(var(--ap-surface))); border: 1px solid rgba(255,255,255,0.06); }
        .dark .gp-lang-active { background-color: rgb(var(--ap-rail-deep)); color: rgb(var(--ap-ink-2)); box-shadow: inset 0 2px 4px rgba(0,0,0,0.5); }
        .dark .gp-lang-inactive { color: rgb(var(--ap-ink-3)); }
        .dark .gp-lang-inactive:hover { color: rgb(var(--ap-ink-2)); background-color: rgba(255,255,255,0.1); }

        </style>

        @if ($event && $event->exists && $event->starts_at && !$event->is_draft && !($passwordGate ?? false))
            @php
                $eventName = $guestEventName;
                $eventDescription = trim(strip_tags((string) $guestEventDescriptionHtml));
                if (empty($eventDescription)) {
                    $eventDescription = $eventName . ' - ' . __('messages.event');
                }
                $eventUrl = $event->getCanonicalUrl($date ?? null);
                // The block below is already @if-guarded, so null simply omits "image" rather
                // than asserting to Google that this event looks like an Event Schedule advert.
                $eventImage = $event->getImageUrl();
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
@if (count($offers))
                "offers": @json(count($offers) === 1 ? $offers[0] : $offers, $jsonLdFlags),
@endif
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
                $roleUrl = $role->getCanonicalUrl();
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
                
                // Suppressed for unverified schedules to avoid seeding structured-data backlinks.
                $sameAs = [];
                if ($role->social_links && !$isUnverifiedRole) {
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

        @php
            // A schedule serving on its own active custom domain IS the site, so the breadcrumb
            // roots at the schedule rather than at marketing_url(). Google discards a breadcrumb
            // whose first item sits on a different domain, which is what the marketing root was on
            // every custom domain. Same predicate the canonical uses, so the two always agree.
            $breadcrumbRootsAtSchedule = $role->servesOnCustomDomain();
        @endphp

        @if ($event && $event->exists && $event->starts_at && !$event->is_draft && !($passwordGate ?? false))
            <script type="application/ld+json" {!! nonce_attr() !!}>
            {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": [
                    @if (! $breadcrumbRootsAtSchedule)
                    {
                        "@type": "ListItem",
                        "position": 1,
                        "name": @json(__('messages.home')),
                        "item": "{{ marketing_url() }}"
                    },
                    @endif
                    {
                        "@type": "ListItem",
                        "position": {{ $breadcrumbRootsAtSchedule ? 1 : 2 }},
                        "name": @json($role->translatedName(), $jsonLdFlags),
                        "item": "{{ $role->getCanonicalUrl() }}"
                    },
                    {
                        "@type": "ListItem",
                        "position": {{ $breadcrumbRootsAtSchedule ? 2 : 3 }},
                        "name": @json($guestEventName, $jsonLdFlags),
                        "item": "{{ $event->getCanonicalUrl($date ?? null) }}"
                    }
                ]
            }
            </script>
        @elseif ($role->exists && ! $breadcrumbRootsAtSchedule)
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
                        "item": "{{ $role->getCanonicalUrl() }}"
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
                            'name' => $guestEventName . ($video->eventPart ? ' - ' . $video->eventPart->nameInLanguage($guestLang, $event->getTranslationLanguageCode()) : ''),
                            'description' => trim(strip_tags($guestEventDescriptionHtml)) ?: $guestEventName,
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
                content_name: @json($guestEventName),
                content_type: 'product',
                content_category: '{{ $event->getSchemaAttendanceMode() === "https://schema.org/OnlineEventAttendanceMode" ? "online_event" : "event" }}'
            });
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1" /></noscript>
        @endif
        @endif

        @include('partials.site-head-code')

        {{ isset($head) ? $head : '' }}
    </x-slot>

    <x-slot name="footCode">@include('partials.site-foot-code')</x-slot>

    @php
        $isRtl = $role->isRtl();
        $isRoleRtl = in_array($role->language_code, ['ar', 'he']);
    @endphp

    <div id="main-content" tabindex="-1" class="flex-grow relative">
        {{-- The owner's announcement bar, full-bleed against the top edge so it reads as a
             page-level notice. Sitting above the language switcher also means the switcher's
             own pt-4 supplies the gap beneath the bar. Opt-in per view: see
             AppGuestLayout::$bannerBar. --}}
        @if ($bannerBar)
            @include('role.partials.guest-banner')
        @endif

        @php
            $showMobileBanner = false;
            $mobileBannerUrl = '';

            if ($event && $otherRole && $otherRole->isClaimed() && $otherRole->hasConfiguredBackground() && $otherRole->background == 'image') {
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

        @php
            $switcherLanguages = config('app.supported_languages');
            $switcherTarget = $role->translation_language_code ?: 'en';
            $switcherTargetName = isset($switcherLanguages[$switcherTarget]) ? __('messages.' . $switcherLanguages[$switcherTarget]) : strtoupper($switcherTarget);
            $switcherAuthoredName = isset($switcherLanguages[$role->language_code]) ? __('messages.' . $switcherLanguages[$role->language_code]) : strtoupper($role->language_code);
        @endphp
        @if (! request()->embed && $role->offersTranslation() && ! $hasInlineLangToggle)
            <div class="container mx-auto flex justify-end {{ $isRtl ? 'pl-5' : 'pr-5' }} pt-4">
                <div class="gp-lang-switcher flex items-center rounded-full p-1 text-sm shadow-md z-50 {{ $isRtl ? 'flex-row-reverse' : '' }}" translate="no">
                    @if(session()->has('translate') || request()->lang == $switcherTarget)
                        <span class="gp-lang-active px-3 py-1.5 rounded-full font-medium" title="{{ $switcherTargetName }}" aria-label="{{ $switcherTargetName }}">{{ strtoupper($switcherTarget) }}</span>
                        {{-- fullUrlWithQuery, not url(), so switching language keeps the rest of
                             the query string (?layout=, ?category=, ?month= ...) intact. --}}
                        <a href="{{ str_replace('http://', 'https://', request()->fullUrlWithQuery(['lang' => $role->language_code])) }}"
                           class="gp-lang-inactive px-3 py-1.5 rounded-full font-medium transition-all duration-200"
                           title="{{ $switcherAuthoredName }}" aria-label="{{ $switcherAuthoredName }}">
                            {{ strtoupper($role->language_code) }}
                        </a>
                    @else
                        <a href="{{ str_replace('http://', 'https://', request()->fullUrlWithQuery(['lang' => $switcherTarget])) }}"
                           class="gp-lang-inactive px-3 py-1.5 rounded-full font-medium transition-all duration-200"
                           title="{{ $switcherTargetName }}" aria-label="{{ $switcherTargetName }}">
                            {{ strtoupper($switcherTarget) }}
                        </a>
                        <span class="gp-lang-active px-3 py-1.5 rounded-full font-medium" title="{{ $switcherAuthoredName }}" aria-label="{{ $switcherAuthoredName }}">{{ strtoupper($role->language_code) }}</span>
                    @endif
                </div>
            </div>
        @endif

        {{ $slot }}
    </div>

    @if ($cart)
        @include('partials.guest-cart', ['role' => $role])
    @endif

    {{-- Monetization slot. Deliberately outside #main-content (and so outside the
         #calendar-app Vue mount), directly above the free-tier branding footer that it
         shares a gate with. Opt-in per view: see AppGuestLayout::$adSlot. --}}
    @if ($adSlot)
        @include('partials.promo-slot')
    @endif

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

    @php
        // Which of the chip's jobs applies here, or null for none - see
        // Role::creditChipReason(). Off the nexus it is unconditional, whatever the tenant's
        // plan; hosted and is_nexus are independent env vars, so the reasons are ordered
        // there rather than unpicked here.
        $creditReason = $role->creditChipReason();

        // Tagged per reason so the /admin traffic sources report can tell an operator's own
        // platform apart from a selfhost install apart from a granted plan. The marketing
        // layout builds its canonical from request()->path(), so the query string
        // self-canonicalizes away. The chip always points at eventschedule.com rather than
        // marketing_url(): it is the license attribution, and that is not the operator's to
        // rebrand.
        $creditUtm = [
            'selfhost' => '?utm_source=selfhost&utm_medium=footer',
            'saas' => '?utm_source=saas&utm_medium=footer',
            'granted_plan' => '?utm_source=granted-plan&utm_medium=footer',
        ];
        $creditUrl = 'https://eventschedule.com'.($creditUtm[$creditReason] ?? '');
    @endphp

    @if (! request()->embed && $creditReason)
    <div class="flex justify-{{ $isRtl ? 'start' : 'end' }} p-4 {{ $role->show_accessibility_widget ? 'es-a11y-credit-clear' : '' }}">
        {{-- Per the AAL license, please do not remove the link to Event Schedule --}}
        <a href="{{ $creditUrl }}" target="_blank" rel="noopener" title="{{ __('messages.powered_by_event_schedule') }}"
           class="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm ring-1 ring-black/5 backdrop-blur transition-colors hover:bg-white hover:text-gray-900">
            <span aria-hidden="true" class="flex h-4 w-4 items-center justify-center rounded-[5px] bg-gradient-to-br from-[#4E81FA] to-[#22D3EE] text-[8px] font-black leading-none text-white">ES</span>
            <span>Event Schedule</span>
        </a>
    </div>
    @endif

    @if ($role->show_accessibility_widget)
        @include('partials.accessibility-widget')
    @endif

</x-app-layout>