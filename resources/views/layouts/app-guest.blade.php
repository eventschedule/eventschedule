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
        // Event text resolved by language rather than by a "showing translation" boolean, which
        // inverts for an aggregated event whose language pair differs from this schedule's.
        // $role's OWN translated*() calls below need no such treatment - $guestLang came from it.
        $guestLang = $role->displayLanguageCode();
        $guestEventName = ($event && $event->exists) ? $event->nameInLanguage($guestLang, $role) : null;
        // user_id as well as the contact columns, because only one half was being asked.
        // AdminController::verifyScheduleEmail() stamps email_verified_at on whatever row it is
        // handed - including a placeholder, reachable in one click from the ?owner=unclaimed list -
        // and RoleController::verify() does the same for anyone holding the link. Either one used
        // to flip a page about a third party who never signed up to "index, follow".
        //
        // Deliberately NOT hasRealOwner(), which is the predicate showAds() uses. That one is also
        // false for a real customer whose owner pivot has drifted (the state
        // CheckData::checkRoleOwnership() repairs), and de-indexing a paying customer's live page
        // over a missing pivot row is a worse failure than the one being fixed. The residue is a
        // ConvertsLocationToVenue venue whose address an admin verified by hand, which is an admin
        // deliberately adopting it. Costs no query, unlike hasRealOwner().
        //
        // Still needed on its own: it also withholds the schedule's sameAs links below.
        $isUnverifiedRole = $role && $role->exists && ! $role->hasVerifiedContact();

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
        // Role::langQuerySuffix() is also what every JSON-LD url below carries, so the structured
        // data names exactly the URL the canonical tag does.
        $guestLangSuffix = $role->langQuerySuffix($guestShownLang);

        // The event's canonical: its undated URL on its home schedule (Event::canonicalTarget()).
        // For a recurring event that is the SERIES URL, the same on the undated page and on every
        // dated occurrence. The canonical tag, the hreflang alternates, the JSON-LD url and
        // offers, the breadcrumb and the password page's og:url all name it. $date stays what the
        // page SHOWS - the occurrence in the URL, or the next one on the undated page - for the
        // dates in the JSON-LD; only the URL loses it.
        $eventCanonicalUrl = ($event && $event->exists) ? $event->getCanonicalUrl() : null;

        // og:url alone keeps the occurrence a dated page is about, on the same home host: it is
        // the share target, and Google ignores it for canonicalization. See
        // AppGuestLayout::$occurrenceDate, which is null on the undated page.
        $eventShareUrl = ($occurrenceDate && $event && $event->exists)
            ? ($event->canonicalTarget($occurrenceDate)[0] ?? $eventCanonicalUrl)
            : $eventCanonicalUrl;

        // The photo gallery's own canonical, shared by the canonical tag, og:url and the hreflang
        // alternates, which used to point the gallery's language variants at the EVENT page.
        // Unlike the event page, a dated gallery is its own: it shows that night's photos
        // (Event::getCanonicalPhotoGalleryUrl()). The undated one is the series gallery.
        $galleryCanonicalUrl = ($galleryMode && $event && $event->exists)
            ? $event->getCanonicalPhotoGalleryUrl($occurrenceDate)
            : null;

        // The page's description, built ONCE for the description, og: and twitter: tags, which
        // used to be three calls that could drift apart. It is plain decoded text, so the {{ }}
        // below escapes it exactly once. On an event page the date comes from $occurrenceDate,
        // not $date: the undated series page backfills $date with the next occurrence, and a
        // series page is about every occurrence (GuestSeo::eventDescription()). The password gate
        // and the gallery keep their own tags below, and a draft's page carries the schedule's,
        // matching the branches of the meta slot.
        $guestMetaDescription = match (true) {
            ($passwordGate ?? false) || $galleryMode => '',
            $event && $event->exists && ! $event->is_draft => \App\Utils\GuestSeo::eventDescription($event, $occurrenceDate, $guestLang, $role),
            $role->exists => \App\Utils\GuestSeo::scheduleDescription($role, $upcoming),
            default => '',
        };
    @endphp

    <x-slot name="meta">
        {{-- The mobile LCP image, asked for before anything else in the head. On a phone the
             schedule page's largest paint is the background banner role/show-guest paints behind
             its header (the lab measured 11.2 s, on a 1.1MB original), and a CSS background is
             found only once the stylesheet and the markup that uses it have both arrived. Only the
             schedule page passes it (AppGuestLayout::$mobileBannerImage), and media= keeps a
             desktop from fetching a phone's image it will never paint. --}}
        @if ($mobileBannerImage)
        <link rel="preload" as="image" href="{{ $mobileBannerImage }}" media="(max-width: 767px)" fetchpriority="high">
        @endif

        {{-- The schedule half is Role::isIndexableHost(), the same rule both sitemaps apply, so
             neither submits a URL this tag then refuses. It covers an unverified schedule (above)
             and demo content: the /examples showcase and Springfield schedules exist to be LOOKED
             AT, not to rank. Googlebot was spending a quarter of its crawl on them -
             countyfairgrounds, weekendyogaretreat, battleofthebands, karateclub and painting alone
             took ~165k of 637k requests in 89 days, several out-crawling the tenant that earns 44%
             of the property's clicks - and thousands of thin fabricated event pages are a
             site-wide quality signal. They stay fully viewable; only the indexing goes. --}}
        @if ($noIndex || request()->embed || request('graphic') || (isset($event) && $event->exists && ($event->is_private || $event->is_draft)) || ($role->exists && ! $role->isIndexableHost()))
            <meta name="robots" content="noindex, nofollow">
        @else
            {{-- max-image-preview:large and the uncapped snippet and video previews, as on the
                 marketing pages: without them Google shows a tenant's event at thumbnail size. --}}
            <meta name="robots" content="{{ \App\Utils\SeoUtils::ROBOTS_INDEX }}">
        @endif

        @if ($guestHasAltLang)
            @php
                $hreflangBase = $galleryCanonicalUrl ?? $eventCanonicalUrl ?? $role->getCanonicalUrl();
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
            <meta property="og:url" content="{{ $eventCanonicalUrl }}">
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
                    // A dated gallery names its night (GuestSeo::galleryDate()), or every one of a
                    // series' galleries had this same title and description.
                    $galleryDate = \App\Utils\GuestSeo::galleryDate($event, $occurrenceDate);
                    $galleryTitle = $guestEventName . ' - ' . __('messages.photo_gallery') . ($galleryDate ? ' - ' . $galleryDate : '');
                    // The first photo the gallery SHOWS (AppGuestLayout::$galleryImage), never the
                    // event's first photo of any night: that previewed another night's photo.
                    // Never /images/social/home.jpg: an event with no photo, no flyer and no
                    // schedule or venue logo advertises no image, and the scraper falls back to
                    // the page's own contents rather than to an advert of ours.
                    $galleryOgImage = $galleryImage ?: $event->getImageUrl();
                    // $occurrenceDate, not $date: the undated gallery fills $date in with the next
                    // occurrence, and the series gallery names no date (GuestSeo::eventWhen()).
                    $galleryDescription = $event->getMetaDescription($occurrenceDate, $guestLang, $role);
                @endphp
                <link rel="canonical" href="{{ $galleryCanonicalUrl }}{{ $guestLangSuffix }}">
                <meta name="description" content="{{ $galleryTitle }}">
                <meta property="og:type" content="website">
                <meta property="og:title" content="{{ $galleryTitle }}">
                <meta property="og:description" content="{{ $galleryDescription }}">
                @if ($galleryOgImage)
                <meta property="og:image" content="{{ $galleryOgImage }}">
                <meta property="og:image:alt" content="{{ $galleryTitle }}">
                @endif
                <meta property="og:url" content="{{ $galleryCanonicalUrl }}">
                <meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
                <meta name="twitter:title" content="{{ $galleryTitle }}">
                <meta name="twitter:description" content="{{ $galleryDescription }}">
                @if ($galleryOgImage)
                <meta name="twitter:image" content="{{ $galleryOgImage }}">
                <meta name="twitter:image:alt" content="{{ $galleryTitle }}">
                @endif
                <meta name="twitter:card" content="{{ $galleryOgImage ? 'summary_large_image' : 'summary' }}">
                <meta name="twitter:site" content="@ScheduleEvent">
            @else
            <link rel="canonical" href="{{ $eventCanonicalUrl }}{{ $guestLangSuffix }}">
            <meta name="description" content="{{ $guestMetaDescription }}">
            <meta property="og:type" content="event">
            <meta property="og:title" content="{{ $guestEventName }}">
            <meta property="og:description" content="{{ $guestMetaDescription }}">
            {{-- Event::shareImage() cascades flyer -> performer -> venue -> the creating schedule,
                 uploads only, so null here means the owners have no image anywhere. Advertising
                 none lets the scraper fall back to their own page, which beats handing it an
                 advert of ours. --}}
            @php $eventOgImage = $event->shareImage(); @endphp
            @if ($eventOgImage)
            <meta property="og:image" content="{{ $eventOgImage['url'] }}">
            @if (isset($eventOgImage['width'], $eventOgImage['height']))
            <meta property="og:image:width" content="{{ $eventOgImage['width'] }}">
            <meta property="og:image:height" content="{{ $eventOgImage['height'] }}">
            @endif
            <meta property="og:image:alt" content="{{ $guestEventName }}">
            @endif
            <meta property="og:url" content="{{ $eventShareUrl }}">
            <meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
            <meta name="twitter:title" content="{{ $guestEventName }}">
            <meta name="twitter:description" content="{{ $guestMetaDescription }}">
            @if ($eventOgImage)
            <meta name="twitter:image" content="{{ $eventOgImage['url'] }}">
            <meta name="twitter:image:alt" content="{{ $guestEventName }}">
            @endif
            <meta name="twitter:card" content="{{ $eventOgImage ? 'summary_large_image' : 'summary' }}">
            <meta name="twitter:site" content="@ScheduleEvent">
            @endif
        @elseif ($role->exists)
            <link rel="canonical" href="{{ $role->getCanonicalUrl() }}{{ $guestLangSuffix }}">
            <meta name="description" content="{{ $guestMetaDescription }}">
            <meta property="og:description" content="{{ $guestMetaDescription }}">
            <meta name="twitter:description" content="{{ $guestMetaDescription }}">
            @if ($name = $role->translatedName())
            <meta property="og:title" content="{{ $name }}">
            <meta name="twitter:title" content="{{ $name }}">
            @endif
            {{-- No @else: a schedule with no picture used to advertise US in its own link preview.
                 Advertising nothing is the right fallback. It does not guarantee a picture-less
                 card - Facebook's crawler will pick one out of the page body - but whatever it
                 finds there is the owner's, which an advert of ours never is. Role::shareImage()
                 is the header, logo or background the owner UPLOADED, never built-in art. --}}
            @php $scheduleOgImage = $role->shareImage(); @endphp
            @if ($scheduleOgImage)
            <meta property="og:image" content="{{ $scheduleOgImage['url'] }}">
            @if (isset($scheduleOgImage['width'], $scheduleOgImage['height']))
            <meta property="og:image:width" content="{{ $scheduleOgImage['width'] }}">
            <meta property="og:image:height" content="{{ $scheduleOgImage['height'] }}">
            @endif
            <meta property="og:image:alt" content="{{ $name ?? $role->translatedName() }}">
            <meta name="twitter:image" content="{{ $scheduleOgImage['url'] }}">
            <meta name="twitter:image:alt" content="{{ $name ?? $role->translatedName() }}">
            @endif
            <meta property="og:type" content="website">
            <meta property="og:url" content="{{ $role->getCanonicalUrl() }}">
            <meta property="og:site_name" content="{{ $role->translatedName() ?: config('app.name') }}">
            <meta name="twitter:card" content="{{ $scheduleOgImage ? 'summary_large_image' : 'summary' }}">
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
        {{-- array_filter: the Role accessor reads an invalid font as null, as it does a missing one. --}}
        @foreach(array_filter($fonts) as $font)
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
                            background-image: url("{{ css_url(asset('images/backgrounds/' . $otherRole->background_image . '.webp')) }}");
                            background-image: image-set(
                                url("{{ css_url(asset('images/backgrounds/' . $otherRole->background_image . '.webp')) }}") type("image/webp"),
                                url("{{ css_url(asset('images/backgrounds/' . $otherRole->background_image . '.png')) }}") type("image/png")
                            );
                        @elseif ($showMobileBackground)
                            {{-- An upload, painted at every width here: a phone's derivative, and the
                                 desktop one from md up. Both fall back to the original until the
                                 derivatives exist, so the rule is never an empty url(). --}}
                            background-image: url("{{ css_url($otherRole->backgroundImageUrl(960)) }}");
                            @media (min-width: 768px) {
                                background-image: url("{{ css_url($otherRole->backgroundImageUrl(1920)) }}");
                            }
                        @else
                            background-image: url("{{ css_url($otherRole->backgroundImageUrl(1920)) }}");
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
                            url("{{ css_url(asset('images/backgrounds/' . $role->background_image . '.webp')) }}");
                            background-image:
                                @if (request()->graphic)
                                    linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                                @endif
                            image-set(
                                url("{{ css_url(asset('images/backgrounds/' . $role->background_image . '.webp')) }}") type("image/webp"),
                                url("{{ css_url(asset('images/backgrounds/' . $role->background_image . '.png')) }}") type("image/png")
                            );
                        @elseif ($showMobileBackground)
                            {{-- An upload, painted at every width here: a phone's derivative, and the
                                 desktop one from md up. On the schedule page this rule is desktop
                                 only (the @media above) and role/show-guest paints the phone's
                                 banner itself. Both fall back to the original until the derivatives
                                 exist, so the rule is never an empty url(). --}}
                            background-image:
                                @if (request()->graphic)
                                    linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                                @endif
                            url("{{ css_url($role->backgroundImageUrl(960)) }}");
                            @media (min-width: 768px) {
                                background-image:
                                    @if (request()->graphic)
                                        linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                                    @endif
                                url("{{ css_url($role->backgroundImageUrl(1920)) }}");
                            }
                        @else
                            background-image:
                                @if (request()->graphic)
                                    linear-gradient(rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0.5)),
                                @endif
                            url("{{ css_url($role->backgroundImageUrl(1920)) }}");
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

        @if ($galleryMode)
            {{-- No Event node and no schedule node. The gallery is ABOUT the event, not the event:
                 printing the same Event here made two URLs compete for one rich result, one of
                 them a page whose content is fan photos. The breadcrumb below still ties it to
                 the event page. --}}
        @elseif ($event && $event->exists && $event->starts_at && !$event->is_draft && !($passwordGate ?? false))
            {{-- Event::schemaNode() is where the rules live. It is dated by $date, the occurrence
                 this page shows (the next one on the undated page), while its url and offers name
                 the page's canonical - the SERIES on a recurring event - with this page's ?lang=,
                 exactly as the canonical tag above. The private join link (event_url) is never in it. --}}
            <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! \App\Utils\SeoUtils::jsonLd($event->schemaNode($date ?? null, $role, $guestLang)) !!}
            </script>
        @elseif ($role->exists)
            {{-- Role::schemaNode(): an EventVenue, Person or Organization with the page's upcoming
                 events, and no sameAs for an unverified schedule. $upcoming is only looked up by
                 the schedule page itself; everywhere else the node lists no events. --}}
            <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! \App\Utils\SeoUtils::jsonLd($role->schemaNode($guestLang, $isUnverifiedRole, $upcoming ?? collect())) !!}
            </script>

            {{-- The site's name is the schedule's, where the schedule is the site: its home, at the
                 root of its own host (Role::websiteSchemaNode()). --}}
            @if ($scheduleHome && ($websiteJsonLd = $role->websiteSchemaNode($guestLang)))
            <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! \App\Utils\SeoUtils::jsonLd($websiteJsonLd) !!}
            </script>
            @endif
        @endif

        @php
            // A schedule serving on its own active custom domain IS the site, so the breadcrumb
            // roots at the schedule rather than at marketing_url(). Google discards a breadcrumb
            // whose first item sits on a different domain, which is what the marketing root was on
            // every custom domain. Same predicate the canonical uses, so the two always agree.
            $breadcrumbRootsAtSchedule = $role->servesOnCustomDomain();
            // Crumbs in order; positions are numbered from the list, so dropping the marketing
            // root on a custom domain cannot leave a gap or a duplicate.
            $breadcrumbCrumbs = [];
            if (! $breadcrumbRootsAtSchedule) {
                $breadcrumbCrumbs[] = [__('messages.home'), marketing_url()];
            }
            $breadcrumbCrumbs[] = [$role->translatedName(), $role->getCanonicalUrl()];

            $breadcrumbForEvent = $event && $event->exists && $event->starts_at && ! $event->is_draft && ! ($passwordGate ?? false);
            if ($breadcrumbForEvent) {
                $breadcrumbCrumbs[] = [$guestEventName, $eventCanonicalUrl];
            }

            // A schedule page on its own domain would be a one-item trail, which says nothing.
            $breadcrumbJsonLd = null;
            if ($breadcrumbForEvent || ($role->exists && ! $breadcrumbRootsAtSchedule)) {
                $breadcrumbJsonLd = [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => array_map(fn ($crumb, $index) => [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => $crumb[0],
                        'item' => $crumb[1],
                    ], $breadcrumbCrumbs, array_keys($breadcrumbCrumbs)),
                ];
            }
        @endphp

        @if ($breadcrumbJsonLd)
            <script type="application/ld+json" {!! nonce_attr() !!}>
            {!! \App\Utils\SeoUtils::jsonLd($breadcrumbJsonLd) !!}
            </script>
        @endif

        {{-- Not on the gallery, which shows photos: a VideoObject belongs on the page that plays it. --}}
        @if ($event && $event->exists && !($passwordGate ?? false) && ! $galleryMode)
            @php
                $allVideos = $event->approvedVideos;
                $videoSchemaItems = [];
                // The Event node's own plain-text description (block-aware, entity-decoded), else the
                // event's name: a VideoObject needs one, and strip_tags() glued words across lines.
                $videoDescription = $allVideos->isNotEmpty()
                    ? ($event->getSchemaDescription($guestLang, $role) ?? \App\Utils\SeoUtils::cleanText($guestEventName))
                    : null;
                foreach ($allVideos as $video) {
                    $videoId = \App\Utils\UrlUtils::extractYouTubeVideoId($video->youtube_url);
                    if ($videoId) {
                        $videoSchemaItems[] = [
                            '@type' => 'VideoObject',
                            'name' => $guestEventName . ($video->eventPart ? ' - ' . $video->eventPart->nameInLanguage($guestLang, $event->getTranslationLanguageCode()) : ''),
                            'description' => $videoDescription,
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
            {!! \App\Utils\SeoUtils::jsonLd($videoSchema + ['@context' => 'https://schema.org']) !!}
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
            $switcherLanguages = config('app.supported_languages');
            $switcherTarget = $role->translation_language_code ?: 'en';
            $switcherTargetName = isset($switcherLanguages[$switcherTarget]) ? __('messages.' . $switcherLanguages[$switcherTarget]) : strtoupper($switcherTarget);
            $switcherAuthoredName = isset($switcherLanguages[$role->language_code]) ? __('messages.' . $switcherLanguages[$role->language_code]) : strtoupper($role->language_code);
        @endphp
        @if (! request()->embed && $role->offersTranslation() && ! $hasInlineLangToggle)
            <div id="gp-language-switcher" class="container mx-auto flex justify-end {{ $isRtl ? 'pl-5' : 'pr-5' }} pt-4">
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

    {{-- The dark footer strip: an operator's free-tier growth CTA, linking their own
         marketing_url(). eventschedule.com has none - its free tier carries the corner chip
         below instead - so Role::showFooterStrip() is false on the nexus whatever the plan. --}}
    @if (! request()->embed && $role->showFooterStrip())
    <footer class="bg-gray-800">
      <div class="container mx-auto relative flex flex-row justify-center items-center py-5 px-5">
        <!-- Per the AAL license, please do not remove the link to Event Schedule -->
        <p class="text-[#F5F9FE] text-base text-center" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
            {!! str_replace(':link', '<bdi dir="ltr"><a href="' . marketing_url() . '" target="_blank" rel="noopener" class="text-white hover:underline">' . marketing_domain() . '</a></bdi>',  __('messages.try_event_schedule')) !!}
        </p>
      </div>
    </footer>
    @endif

    @php
        // Which of the chip's jobs applies here, or null for none - see
        // Role::creditChipReason(). Off the nexus it is unconditional, whatever the tenant's
        // plan, bar a free tier already carrying the operator's strip; on the nexus it is the
        // free tier's credit and an admin-granted plan's. hosted and is_nexus are independent
        // env vars, so the reasons are ordered there rather than unpicked here.
        $creditReason = $role->creditChipReason();

        // Tagged per reason so the /admin traffic sources report can tell an operator's own
        // platform apart from a selfhost install apart from our own free tier apart from a
        // granted plan. The marketing layout builds its canonical from request()->path(), so the
        // query string self-canonicalizes away. The chip always points at eventschedule.com
        // rather than marketing_url(): it is the license attribution, and that is not the
        // operator's to rebrand.
        $creditUtm = [
            'selfhost' => '?utm_source=selfhost&utm_medium=footer',
            'saas' => '?utm_source=saas&utm_medium=footer',
            'free_plan' => '?utm_source=free-plan&utm_medium=footer',
            'granted_plan' => '?utm_source=granted-plan&utm_medium=footer',
        ];
        $creditUrl = 'https://eventschedule.com'.($creditUtm[$creditReason] ?? '');
    @endphp

    {{-- es-credit-chip: lifted clear of the mobile CTA bar by accessibility-widget.css. --}}
    @if (! request()->embed && $creditReason)
    <div class="es-credit-chip flex justify-{{ $isRtl ? 'start' : 'end' }} p-4 {{ $role->show_accessibility_widget ? 'es-a11y-credit-clear' : '' }}">
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