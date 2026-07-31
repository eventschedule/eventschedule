<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Event Schedule Examples | Live Demo Schedules to Explore</x-slot>
    <x-slot name="description">Explore {{ $scheduleCount }} live demo schedules showcasing Event Schedule. See real examples for fitness studios, music venues, yoga retreats, community groups, and more.</x-slot>
    <x-slot name="breadcrumbTitle">Examples</x-slot>

    {{-- Structured Data for Rich Results. Built with json_encode so the payload
         cannot drift from the visible floor and so names with an apostrophe
         (Nate's Woodworking Shop) encode correctly. --}}
    <x-slot name="structuredData">
    @php
        $collectionPayload = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Event Schedule Examples',
            'description' => 'A gallery of ' . $scheduleCount . ' live demo schedules showcasing Event Schedule features for various industries',
            'url' => url('/examples'),
            'numberOfItems' => $scheduleCount,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Event Schedule',
                'url' => config('app.url'),
            ],
        ];
        $itemListPayload = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Live Event Schedule Demos',
            'description' => 'Explore real examples of Event Schedule in action',
            'numberOfItems' => $scheduleCount,
            'itemListElement' => array_values(array_map(function ($index, $schedule) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $schedule['name'],
                    'url' => $schedule['url'],
                ];
            }, array_keys($allSchedules), $allSchedules)),
        ];
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! json_encode($collectionPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! json_encode($itemListPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Demos "The Showroom" styles. The page is a lit showroom floor
           and every unit standing on it is a real published schedule at
           its own address, so the metaphor and the product argument are
           the same sentence: nothing here is a screenshot. Rooms hold
           units, each unit stands in a pool of light behind glass, and
           each one carries a plate telling you what you are looking at.

           WHY A VITRINE AND A PLATE, NOT A PHOTO CARD. The first-wave
           page laid each schedule's name and description ON TOP of its
           header photograph. Measured on that page, in one pass: 39 AA
           failures in light mode, because white type over a photo is
           scored against the SECTION ground rather than the image, so
           it can never pass. Here the photograph sits inside its own
           glass window and every word sits on a solid plate below it.
           Do not move text back over the images.

           COLOUR. This page keeps the blue it already had, but flat -
           NOT the shared blue/sky/cyan chrome gradient, which is house
           furniture on forty other pages and whose cyan stop measures
           2.43 on white. One blue-700 ink for the lit floor, one pale
           blue for after hours. The distinctiveness is meant to come
           from the material instead: cool showroom white, hairline
           plates, glass, and a pool of light under every unit.

           NEVER text-gray-500 here. It is 4.83 on pure white but only
           about 4.4 on this page's #f2f4f8 floor. Use es-show-muted
           (6.95 on the floor, 7.65 on a plate).

           The two dark bands are the same room with the lights down,
           so they must render IDENTICALLY with .dark on and off: every
           shared class used inside them (grid-overlay, animate-shimmer,
           es-claim:focus-within) carries a fixed override below.
           ============================================================== */

        /* --- The floor and the ink ------------------------------------ */
        .es-show-page { background-color: #f2f4f8; color: #0f1522; }
        .dark .es-show-page { background-color: #070a11; color: #e7ecf5; }
        .es-show-ink { color: #0f1522; }
        .dark .es-show-ink { color: #e7ecf5; }
        .es-show-muted { color: #4a5464; }
        .dark .es-show-muted { color: #98a3b6; }
        .es-show-accent { color: #1d4ed8; }
        .dark .es-show-accent { color: #8fb5ff; }
        /* Always lit, in both colour modes, for use inside the dark bands. */
        .es-show-lit { color: #8fb5ff; }

        /* The house hero height is a bare calc(Nsvh - 4rem). It is CLAMPED here
           because this page is the tallest on the site (18 unit cards): when a
           tool expands the viewport to the document height, svh expands with
           it, so the hero grows, so the document grows. That ran away on this
           page - a full-page capture came back 1440x51474, four times the real
           document. The min() pins it (measured: expanding the viewport to
           10391px now leaves the hero at 711px and the document unchanged)
           and costs nothing on a real window, where 84svh - 4rem is 692px at
           900px tall, under the 44rem ceiling, and the hero is content-bound
           anyway. NOTE this does not make captureBeyondViewport usable on this
           page - it still never returns here. Verify with --bands and with
           shot-region.mjs at several offsets instead of --shot. */
        .es-show-hero { min-height: min(calc(84svh - 4rem), 44rem); }
        .es-show-h1 { font-size: 2.3rem; line-height: 1.06; letter-spacing: -0.02em; }
        @media (min-width: 640px) {
            .es-show-h1 { font-size: 3.5rem; }
        }
        @media (min-width: 1024px) {
            .es-show-h1 { font-size: 4rem; }
        }

        /* --- The plate: every word on this page sits on one ----------- */
        .es-show-plate {
            background: #ffffff;
            border: 1px solid rgba(15, 21, 34, 0.10);
            border-radius: 1rem;
            box-shadow: 0 14px 34px -26px rgba(15, 21, 34, 0.55);
        }
        .dark .es-show-plate {
            background: rgba(230, 236, 246, 0.04);
            border-color: rgba(231, 236, 245, 0.10);
            box-shadow: none;
        }

        /* --- The vitrine: the glass a unit is displayed behind -------- */
        .es-show-vitrine {
            position: relative;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            background: linear-gradient(160deg, #e3e8f2 0%, #eff2f7 100%);
            border-bottom: 1px solid rgba(15, 21, 34, 0.10);
        }
        .dark .es-show-vitrine {
            background: linear-gradient(160deg, #151a24 0%, #0b0f16 100%);
            border-bottom-color: rgba(231, 236, 245, 0.10);
        }
        .es-show-vitrine picture {
            position: absolute;
            inset: 0;
            display: block;
        }
        .es-show-vitrine img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        /* The glass itself: one diagonal highlight, identical in both modes. */
        .es-show-vitrine::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(112deg, rgba(255, 255, 255, 0.26) 0%, rgba(255, 255, 255, 0.05) 34%, rgba(255, 255, 255, 0) 52%);
        }
        .es-show-unit:hover .es-show-vitrine img { transform: scale(1.05); }

        /* Blank vitrine, for a unit with no header image on file. */
        .es-show-blank {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image:
                linear-gradient(rgba(29, 78, 216, 0.10) 1px, transparent 1px),
                linear-gradient(90deg, rgba(29, 78, 216, 0.10) 1px, transparent 1px);
            background-size: 22px 22px;
            color: #1d4ed8;
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -0.03em;
        }
        .dark .es-show-blank {
            background-image:
                linear-gradient(rgba(143, 181, 255, 0.12) 1px, transparent 1px),
                linear-gradient(90deg, rgba(143, 181, 255, 0.12) 1px, transparent 1px);
            color: #8fb5ff;
        }

        /* --- The pool of light each unit stands in -------------------- */
        .es-show-unit { position: relative; }
        .es-show-unit::before {
            content: "";
            position: absolute;
            left: 7%;
            right: 7%;
            bottom: -13px;
            height: 26px;
            border-radius: 50%;
            opacity: 0;
            transition: opacity 0.35s ease;
            background: radial-gradient(ellipse at center, rgba(29, 78, 216, 0.20), rgba(29, 78, 216, 0) 70%);
        }
        .dark .es-show-unit::before {
            background: radial-gradient(ellipse at center, rgba(143, 181, 255, 0.22), rgba(143, 181, 255, 0) 70%);
        }
        .es-show-unit:hover::before { opacity: 1; }

        /* --- The maker's badge: the schedule's own logo --------------- */
        .es-show-badge {
            width: 2.75rem;
            height: 2.75rem;
            flex: none;
            overflow: hidden;
            border-radius: 0.7rem;
            border: 1px solid rgba(15, 21, 34, 0.12);
            background: #ffffff;
        }
        .dark .es-show-badge {
            border-color: rgba(231, 236, 245, 0.14);
            background: rgba(231, 236, 245, 0.06);
        }
        .es-show-badge img { width: 100%; height: 100%; object-fit: cover; }
        .es-show-letter {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            background: #1d4ed8;
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 800;
        }

        /* --- The plate's small print: asset tag, stamp, eyebrow ------- */
        .es-show-asset {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            color: #4a5464;
            /* An address is one line. Wrapping it mid-host reads as a typo. */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .es-show-asset { color: #98a3b6; }

        .es-show-stamp {
            display: inline-flex;
            flex: none;
            align-items: center;
            padding: 0.12rem 0.42rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(15, 21, 34, 0.16);
            color: #4a5464;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .dark .es-show-stamp { border-color: rgba(231, 236, 245, 0.18); color: #98a3b6; }

        /* The unit number, stamped on the glass rather than sharing the name
           row. It moved here because the row could not hold both a stamp and
           a full address: two of three cards truncated ".eventschedule.com"
           to an ellipsis, and the address IS the argument of this page.
           The fill must stay fully opaque - it sits over a photograph, so a
           translucent plate would score the type against whatever is behind
           it, and nothing on this page is allowed to do that. z-index lifts
           it above the vitrine's glass highlight (::after paints last). */
        .es-show-vstamp {
            position: absolute;
            top: 0.6rem;
            inset-inline-end: 0.6rem;
            z-index: 1;
            background: #ffffff;
            border-color: rgba(15, 21, 34, 0.18);
        }
        .dark .es-show-vstamp {
            background: #10141c;
            border-color: rgba(231, 236, 245, 0.22);
        }

        .es-show-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4a5464;
        }
        .dark .es-show-tag { color: #98a3b6; }

        /* --- Room marker and the rail that runs along the wall -------- */
        .es-show-num {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            flex: none;
            padding: 0.5rem 0.8rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(15, 21, 34, 0.14);
            background: #ffffff;
            color: #0f1522;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.06em;
        }
        .dark .es-show-num {
            border-color: rgba(231, 236, 245, 0.16);
            background: rgba(231, 236, 245, 0.05);
            color: #e7ecf5;
        }
        .es-show-num svg { color: #1d4ed8; }
        .dark .es-show-num svg { color: #8fb5ff; }

        .es-show-rail {
            height: 1px;
            background: linear-gradient(90deg, rgba(15, 21, 34, 0.20), rgba(15, 21, 34, 0.04) 70%, rgba(15, 21, 34, 0));
        }
        .dark .es-show-rail {
            background: linear-gradient(90deg, rgba(231, 236, 245, 0.24), rgba(231, 236, 245, 0.05) 70%, rgba(231, 236, 245, 0));
        }
        .es-show-hair { border-color: rgba(15, 21, 34, 0.09); }
        .dark .es-show-hair { border-color: rgba(231, 236, 245, 0.10); }

        /* --- Plan pills ---------------------------------------------- */
        .es-show-plan {
            display: inline-flex;
            flex: none;
            align-items: center;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(29, 78, 216, 0.42);
            color: #1d4ed8;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-show-plan { border-color: rgba(143, 181, 255, 0.45); color: #8fb5ff; }
        .es-show-plan-pro { border-color: rgba(15, 21, 34, 0.34); color: #0f1522; }
        .dark .es-show-plan-pro { border-color: rgba(231, 236, 245, 0.38); color: #e7ecf5; }
        .es-show-plan-ent { border-color: rgba(74, 84, 100, 0.5); color: #4a5464; }
        .dark .es-show-plan-ent { border-color: rgba(152, 163, 182, 0.5); color: #98a3b6; }

        /* --- Ceiling light: beams over the floor, plus the floor sheen  */
        .es-show-beam {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                conic-gradient(from 188deg at 24% -14%, transparent 0deg, rgba(29, 78, 216, 0.11) 8deg, transparent 17deg),
                conic-gradient(from 168deg at 76% -14%, transparent 0deg, rgba(14, 116, 233, 0.09) 8deg, transparent 17deg);
            mask-image: linear-gradient(to bottom, black 4%, transparent 72%);
            -webkit-mask-image: linear-gradient(to bottom, black 4%, transparent 72%);
            animation: es-show-sweep 20s ease-in-out infinite alternate;
        }
        .dark .es-show-beam {
            background:
                conic-gradient(from 188deg at 24% -14%, transparent 0deg, rgba(143, 181, 255, 0.13) 8deg, transparent 17deg),
                conic-gradient(from 168deg at 76% -14%, transparent 0deg, rgba(96, 150, 255, 0.11) 8deg, transparent 17deg);
        }
        @keyframes es-show-sweep {
            from { opacity: 0.7; transform: translateX(-1.2%); }
            to { opacity: 1; transform: translateX(1.2%); }
        }
        /* Floor tiles: the shared grid pattern, faded off at the walls.
           Written here rather than as an arbitrary Tailwind mask utility,
           because a value that is not already in the built CSS silently
           does nothing. */
        .es-show-tiles {
            mask-image: radial-gradient(ellipse 76% 66% at 50% 45%, black 25%, transparent 76%);
            -webkit-mask-image: radial-gradient(ellipse 76% 66% at 50% 45%, black 25%, transparent 76%);
        }
        .es-show-sheen {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 34%;
            pointer-events: none;
            background: linear-gradient(to top, rgba(37, 99, 235, 0.09), rgba(37, 99, 235, 0));
        }
        .dark .es-show-sheen {
            background: linear-gradient(to top, rgba(143, 181, 255, 0.10), rgba(143, 181, 255, 0));
        }

        /* --- Buttons, links, hover ------------------------------------ */
        .es-show-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 18px 38px -18px rgba(29, 78, 216, 0.65);
        }
        .es-show-btn:hover { background-color: #1740b8; }
        .es-show-link { color: #1d4ed8; }
        .es-show-link:hover { color: #0f1522; }
        .dark .es-show-link { color: #8fb5ff; }
        .dark .es-show-link:hover { color: #e7ecf5; }

        .es-show-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease; }
        .es-show-hover:hover {
            border-color: rgba(29, 78, 216, 0.45);
            box-shadow: 0 26px 50px -30px rgba(15, 21, 34, 0.6);
            transform: translateY(-3px);
        }
        .dark .es-show-hover:hover { border-color: rgba(143, 181, 255, 0.45); }
        .es-show-hover:hover .es-show-hover-title { color: #1d4ed8; }
        .dark .es-show-hover:hover .es-show-hover-title { color: #8fb5ff; }
        .es-show-open { color: #1d4ed8; }
        .dark .es-show-open { color: #8fb5ff; }
        .es-show-open svg { transition: transform 0.2s ease; }
        .es-show-hover:hover .es-show-open svg { transform: translate(2px, -2px); }

        /* --- Name chips on the entrance rail -------------------------- */
        .es-show-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 21, 34, 0.14);
            background: rgba(255, 255, 255, 0.72);
            color: #4a5464;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .dark .es-show-chip {
            border-color: rgba(231, 236, 245, 0.14);
            background: rgba(231, 236, 245, 0.05);
            color: #b0bbcc;
        }

        /* --- The lights-down band: identical in both colour modes ----- */
        .es-show-band {
            background-color: #0a0f1a;
            background-image: radial-gradient(120% 92% at 50% -12%, #141c2c 0%, #0c1220 56%, #070a11 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(231, 236, 245, 0.06);
        }
        .es-show-band .es-show-ink { color: #e7ecf5; }
        .es-show-band .es-show-muted { color: #9aa5b8; }
        .es-show-band .es-show-tag { color: #8fb5ff; }
        .es-show-band .es-show-plate {
            background: rgba(231, 236, 245, 0.05);
            border-color: rgba(231, 236, 245, 0.12);
            box-shadow: none;
        }
        .es-show-band .es-show-plan { border-color: rgba(143, 181, 255, 0.45); color: #8fb5ff; }
        .es-show-band .es-show-plan-pro { border-color: rgba(231, 236, 245, 0.38); color: #e7ecf5; }
        .es-show-band .es-show-plan-ent { border-color: rgba(154, 165, 184, 0.5); color: #9aa5b8; }
        .es-show-band .es-show-hair { border-color: rgba(231, 236, 245, 0.10); }
        .es-show-band .es-show-beam {
            background:
                conic-gradient(from 188deg at 24% -14%, transparent 0deg, rgba(143, 181, 255, 0.13) 8deg, transparent 17deg),
                conic-gradient(from 168deg at 76% -14%, transparent 0deg, rgba(96, 150, 255, 0.11) 8deg, transparent 17deg);
        }
        /* Shared classes that would otherwise flip with the colour mode. */
        .es-show-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 236, 245, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 236, 245, 0.05) 1px, transparent 1px);
        }
        .es-show-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-show-band .es-claim:focus-within {
            border-color: rgba(143, 181, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 181, 255, 0.24);
        }

        /* --- Shared-system recolours --------------------------------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(143, 181, 255, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(143, 181, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #8fb5ff; }

        /* --- Focus rings. No border-radius: an outline already follows
               the element's own shape, and setting it changes that shape. */
        #es-show-page a:focus-visible,
        #es-show-page summary:focus-visible,
        #es-show-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-show-page a:focus-visible,
        .dark #es-show-page summary:focus-visible,
        .dark #es-show-page button:focus-visible {
            outline-color: #8fb5ff;
        }
        .es-show-band a:focus-visible,
        .es-show-band summary:focus-visible,
        .es-show-band button:focus-visible {
            outline-color: #8fb5ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-show-beam { animation: none !important; transform: none !important; }
            .es-show-hover:hover { transform: none; }
            .es-show-unit:hover .es-show-vitrine img { transform: none; }
            .es-show-unit::before { transition: none; }
        }
    </style>

    @php
        // Room furniture. Icons are functional glyphs, one path each.
        $roomIcons = [
            'Fitness & Wellness' => '<svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>',
            'Music & Entertainment' => '<svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" /></svg>',
            'Community & Recreation' => '<svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
            'Creative & Workshops' => '<svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
            'Springfield' => '<svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
        ];
        $defaultRoomIcon = '<svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>';

        // A one-line note on each room, so the plaque says something.
        $roomNotes = [
            'Fitness & Wellness' => 'Classes that repeat, weekends that do not, and a cap on how many can come.',
            'Music & Entertainment' => 'A lineup that changes every week, with a page per date worth linking to.',
            'Community & Recreation' => 'Volunteer-run programs where the calendar is the whole website.',
            'Creative & Workshops' => 'Small-group sessions where the date and the sign-up are the same page.',
            'Springfield' => 'Simpsons-themed demo schedules showing how the platform works for a fictional town\'s venues and businesses.',
        ];
        $roomTitles = ['Springfield' => 'Springfield Demo Town'];
        $roomEyebrows = ['Springfield' => 'The model town'];

        // What kind of thing each unit is. Read off its own name and blurb,
        // never a claim about which plan or features that demo uses.
        $unitKinds = [
            'meditationclasses' => 'Mindfulness',
            'weekendyogaretreat' => 'Retreat',
            'hikingclub' => 'Outdoors',
            'battleofthebands' => 'Competition',
            'sufficientgroundscoffeemusic' => 'Cafe',
            'villageidiot' => 'Pub',
            'communityyouthgroup' => 'Youth',
            'karateclub' => 'Dojo',
            'countyfairgrounds' => 'Fairground',
            'nateswoodworkingshop' => 'Crafts',
            'painting' => 'Art studio',
            'pagesbooknookshop' => 'Bookshop',
            'simpsons' => 'Town',
            'demo-moestavern' => 'Bar',
            'demo-amphitheater' => 'Amphitheater',
            'demo-bowlarama' => 'Bowling',
            'demo-aztectheater' => 'Cinema',
            'demo-lardlad' => 'Donuts',
        ];

        // The spec sheet: what is visible on any of these pages, what that
        // part is called in Event Schedule, and what it costs to build.
        // Every row is checked against docs/FEATURES.md.
        $spec = [
            ['The address in the bar, like hikingclub.eventschedule.com', 'A schedule of your own on a subdomain, with nothing to install or host', 'Free'],
            ['A month grid, or a plain list of what is coming up', 'The calendar or list layout, set once per schedule', 'Free'],
            ['A class every Tuesday that nobody retyped fifty times', 'Recurring dates, with exceptions for the days you skip', 'Free'],
            ['Color-coded strands inside one schedule', 'Sub-schedules', 'Free'],
            ['Add to Google, Outlook or Apple Calendar', 'An iCal download per date, plus two-way Google, Outlook and CalDAV sync', 'Free'],
            ['The Follow button under the schedule name', 'Followers you can write to: 10 newsletter emails a month on Free, counted per recipient', 'Free'],
            ['Save me a place, and the count of places left', 'Free registration with an optional capacity, per date', 'Free'],
            ['Buy a ticket without leaving the page', 'Ticket types and card checkout, with zero platform fees. Free sells 25 paid tickets a month per schedule', 'Free'],
            ['Photos and comments from the people who came', 'Fan photos, video and comments, held in an approval queue. Free covers 25 photos per schedule', 'Free'],
            ['The line at the foot of a free schedule inviting you to make one of your own', 'The free-plan credit, which reads "Create your free schedule at eventschedule.com". Removing it is part of Pro', 'Pro'],
            ['A code shown at the door, and scanned on the way in', 'QR scanning, on every plan. The live check-in dashboard is the Pro half, and Pro also lifts the 25-a-month ceiling on what you sell', 'Free'],
            ['A schedule on its own domain rather than a subdomain', 'Custom domains', 'Enterprise'],
        ];

        // Counted, not asserted: the sentence under the table quotes these,
        // so it cannot drift out of step with the rows above it.
        $specFree = count(array_filter($spec, fn ($specRow) => $specRow[2] === 'Free'));

        $steps = [
            ['01', 'Claim the address', 'Sign up, name the schedule and pick its subdomain. The address is the whole thing: no install, no hosting, no plugin to keep up to date.'],
            // The AI-parse allowance is deliberately NOT quantified here.
            // Role::aiParseDailyLimit() branches on isOnTrial / isEnterprise
            // and falls through to the pro figure, so no single number is
            // true of a free schedule. It is capped, and that is all the
            // page claims.
            ['02', 'Put the events in', 'Add them by hand, sync a Google, Outlook or CalDAV calendar both ways, or paste the details and let AI pull out the date, time and venue. AI parsing is on the free plan too, with a daily cap.'],
            ['03', 'Open the doors', 'Share the link, embed the calendar on the site you already have, or print the QR code. People follow, and you email them when something is on.'],
        ];

        $faqs = [
            [
                'q' => 'Can I create a schedule like these?',
                'a' => 'Yes. Everything it takes to publish a schedule like the ones on this page is free forever: unlimited events, recurring dates, sub-schedules, your own colors and header image, free registration with a capacity, two-way calendar sync and an embeddable calendar. Selling starts free too, at 25 paid tickets a month per schedule, and scanning those codes at the door is free on every plan. Five dollars a month lifts the ceiling and adds the live check-in dashboard. Event Schedule charges no platform fees on ticket sales, on any plan.',
            ],
            [
                'q' => 'Are these real schedules?',
                'a' => 'They are real published pages, and they are demos. Event Schedule built them to show different kinds of programming side by side, from fitness and music to community groups and workshops, and each one is live at its own address rather than a screenshot or a video.',
            ],
            [
                'q' => 'How long does it take to set up?',
                'a' => 'Signing up, naming the schedule and publishing a first event takes a few minutes, and none of it needs technical skill. If your events are already in Google Calendar, Outlook or any CalDAV calendar, connect it and they sync both ways instead of being retyped.',
            ],
            [
                'q' => 'Can I see how one of these looks on a phone?',
                'a' => 'Open any unit on this page on your phone. These are the same public pages an audience gets, and the guest layout is responsive, so one schedule reflows to whatever screen it is opened on rather than being maintained twice.',
            ],
            [
                'q' => 'Can I put a calendar like this on my own website?',
                'a' => 'Yes, on every plan. The embeddable calendar drops your schedule into a page on the site you already have, so the calendar lives in two places without being maintained in two places. Embedding the registration form is free as well, and embedding the ticket purchase form is a Pro feature.',
            ],
        ];

        // Other floors of the same building. The icons are deliberately the
        // same glyphs as the matching rooms above, so a reader who walked the
        // floor recognises where each link leads. Each note describes only
        // free parts: recurring dates, a capacity, a page per date, an embed.
        $floors = [
            ['/for-fitness-and-yoga', 'Fitness & Yoga', 'Classes that repeat, with a cap on how many book.', $roomIcons['Fitness & Wellness']],
            ['/for-music-venues', 'Music Venues', 'A lineup per week, a page per date.', $roomIcons['Music & Entertainment']],
            ['/for-workshop-instructors', 'Workshops', 'Small sessions where the sign-up sits on the date.', $roomIcons['Creative & Workshops']],
            ['/for-community-centers', 'Community Centers', 'A calendar that is the whole website.', $roomIcons['Community & Recreation']],
        ];

        $unitNames = collect($allSchedules)->pluck('name')->filter()->values();

        $dotSections = [['top', 'The floor']];
        if (count($categories)) {
            $dotSections[] = ['floor', 'Directory'];
            $roomIndex = 0;
            foreach ($categories as $dotRoomName => $dotRoomUnits) {
                $roomIndex++;
                $dotSections[] = ['room-' . str_pad($roomIndex, 2, '0', STR_PAD_LEFT), $roomTitles[$dotRoomName] ?? $dotRoomName];
            }
        }
        $dotSections[] = ['lights', 'The parts'];
        $dotSections[] = ['build', 'Build yours'];
        $dotSections[] = ['trades', 'Other floors'];
        $dotSections[] = ['faq', 'Questions'];
        $dotSections[] = ['claim', 'Your space'];
    @endphp

    <div id="es-show-page" class="es-show-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the entrance to the floor                           -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero es-show-hero noise relative flex scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(29, 78, 216, 0.22), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(96, 150, 255, 0.16), rgba(96, 150, 255, 0) 65%);"></div>
            <div class="es-show-beam"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern es-show-tiles absolute inset-0"></div>
            <div class="es-show-sheen"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-show-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="es-show-muted text-sm font-medium tracking-wide">
                            <span data-count-to="{{ $scheduleCount }}">{{ $scheduleCount }}</span> live schedules, open to walk through
                        </span>
                    </div>

                    <h1 class="es-balance es-show-h1 es-show-ink mb-8 font-black">
                        <span class="es-mask"><span class="es-mask-line">Nothing on this floor</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">is a <span class="es-show-accent">screenshot.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-show-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Every unit below is a published Event Schedule page at its own address. Open one, click a date, read it on your phone, follow it. Then build yours out of the same parts.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#floor" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Walk the floor
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-show-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The spec plate every unit on this floor is made to -->
                <div class="es-bento group relative es-fade-up es-d-4" data-reveal="panel" data-tilt="4">
                    <div class="es-tilt-inner es-show-plate relative overflow-hidden p-6 sm:p-7">
                        <div class="relative z-10">
                            <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                                <p class="es-show-tag">Unit spec</p>
                                <span class="es-show-stamp">Any of them</span>
                            </div>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="es-show-tag mb-1">Address</dt>
                                    <dd class="es-show-asset">your-name.eventschedule.com</dd>
                                </div>
                                <div class="es-show-hair border-t pt-4">
                                    <dt class="es-show-tag mb-1">The parts</dt>
                                    <dd class="es-show-ink text-sm font-semibold">Events, recurring dates, sub-schedules, registration, followers</dd>
                                </div>
                                <div class="es-show-hair border-t pt-4">
                                    <dt class="es-show-tag mb-1">Built on</dt>
                                    <dd class="flex flex-wrap items-center gap-2">
                                        <span class="es-show-plan">Free</span>
                                        <span class="es-show-muted text-sm">forever, and not a trial</span>
                                    </dd>
                                </div>
                                <div class="es-show-hair border-t pt-4">
                                    <dt class="es-show-tag mb-1">Doors</dt>
                                    <dd class="es-show-muted text-sm">Open to anyone with the link. Nobody needs an account to look.</dd>
                                </div>
                            </dl>
                            <p class="es-show-muted es-show-hair mt-5 border-t pt-4 text-xs">
                                Every unit on this floor is one of these. So is the one you have not made yet.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>

            @if ($unitNames->isNotEmpty())
                <!-- The entrance rail: the real names, in order -->
                <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-4xl">
                    <div class="es-marquee-mask">
                        <div class="es-marquee" data-marquee="1">
                            <div class="es-marquee-track">
                                @for ($railCopy = 0; $railCopy < 2; $railCopy++)
                                    @foreach ($unitNames as $railName)
                                        <span @if ($railCopy === 1) aria-hidden="true" @endif class="es-show-chip">{{ $railName }}</span>
                                    @endforeach
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    @if (count($categories))
    <!-- ============================================================ -->
    <!-- 2. The directory: the whole floor as a record                -->
    <!-- ============================================================ -->
    <section id="floor" class="es-show-hair scroll-mt-24 border-t py-20 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-show-tag mb-4" data-reveal>The directory</p>
                <h2 class="es-balance es-show-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    {{ count($categories) }} rooms. <span class="es-show-accent">Everything in them is live.</span>
                </h2>
                <p class="es-show-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.14s;">
                    Pick a room, or read the whole floor. Every unit opens in a new tab, on the same public page its audience sees.
                </p>
            </div>

            <div class="es-show-plate p-5 sm:p-7" data-reveal="panel">
                <table class="w-full border-collapse text-left">
                    <caption class="sr-only">The floor directory: each room on this page, what is in it, and how many live schedules it holds</caption>
                    <thead>
                        <tr class="es-show-tag">
                            <th scope="col" class="pb-3 pe-3 font-bold">Room</th>
                            <th scope="col" class="pb-3 pe-3 font-bold">What is in it</th>
                            <th scope="col" class="pb-3 text-right font-bold">Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $dirName => $dirUnits)
                            @php
                                $dirNo = str_pad($loop->iteration, 2, '0', STR_PAD_LEFT);
                                $dirKinds = collect($dirUnits)
                                    ->map(fn ($dirUnit) => $unitKinds[$dirUnit['subdomain']] ?? 'Demo')
                                    ->implode(' &middot; ');
                            @endphp
                            <tr class="es-show-hair border-t">
                                <th scope="row" class="es-show-asset py-3 pe-3 align-top font-normal">{{ $dirNo }}</th>
                                <td class="py-3 pe-3 align-top">
                                    <a href="#room-{{ $dirNo }}" class="es-show-link text-sm font-bold hover:underline">{{ $roomTitles[$dirName] ?? $dirName }}</a>
                                    @if ($dirKinds !== '')
                                        <span class="es-show-muted mt-1 block text-xs">{!! $dirKinds !!}</span>
                                    @endif
                                </td>
                                <td class="es-show-ink py-3 text-right align-top font-mono text-sm font-bold">{{ count($dirUnits) }}</td>
                            </tr>
                        @endforeach
                        <tr class="es-show-hair border-t">
                            <th scope="row" class="es-show-tag py-3 pe-3 align-top">All</th>
                            <td class="es-show-muted py-3 pe-3 align-top text-sm">Published, and open in a new tab</td>
                            <td class="es-show-ink py-3 text-right align-top font-mono text-sm font-bold">{{ $scheduleCount }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The rooms                                                 -->
    <!-- ============================================================ -->
    @foreach ($categories as $categoryName => $schedules)
        @php
            $roomNo = str_pad($loop->iteration, 2, '0', STR_PAD_LEFT);
            $roomId = 'room-' . $roomNo;
            $roomTitle = $roomTitles[$categoryName] ?? $categoryName;
        @endphp
        <section id="{{ $roomId }}" aria-labelledby="{{ $roomId }}-heading" class="es-show-hair scroll-mt-24 border-t py-16 lg:py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-center gap-4" data-reveal>
                    <span class="es-show-num">
                        {!! $roomIcons[$categoryName] ?? $defaultRoomIcon !!}
                        <span>{{ $roomNo }}</span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="es-show-tag">{{ $roomEyebrows[$categoryName] ?? 'Room ' . $roomNo }}</p>
                        <h2 id="{{ $roomId }}-heading" class="es-show-ink text-2xl font-black tracking-tight sm:text-3xl">{{ $roomTitle }}</h2>
                    </div>
                    <span class="es-show-stamp">{{ count($schedules) }} live</span>
                </div>
                <div class="es-show-rail mb-8"></div>

                @if (! empty($roomNotes[$categoryName]))
                    <p class="es-show-muted mb-8 max-w-2xl" data-reveal>{{ $roomNotes[$categoryName] }}</p>
                @endif

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3" role="list" data-reveal-group="70">
                    @foreach ($schedules as $schedule)
                        @php $unitNo = $roomNo . '.' . ($loop->iteration); @endphp
                        <article class="es-show-unit" role="listitem" data-reveal>
                            <a href="{{ $schedule['url'] }}"
                               target="_blank"
                               rel="noopener"
                               aria-label="Open the {{ $schedule['name'] }} schedule in a new tab"
                               class="es-show-plate es-show-hover group flex h-full flex-col overflow-hidden">

                                <div class="es-show-vitrine">
                                    @if ($schedule['header_image_url'] ?? false)
                                        <picture>
                                            <source srcset="{{ url(webp_path($schedule['header_image_url'])) }}" type="image/webp">
                                            <img src="{{ url($schedule['header_image_url']) }}"
                                                 alt="The header image on the {{ $schedule['name'] }} schedule"
                                                 loading="lazy"
                                                 decoding="async"
                                                 width="400"
                                                 height="250">
                                        </picture>
                                    @else
                                        <span class="es-show-blank" aria-hidden="true">{{ strtoupper(substr($schedule['name'], 0, 1)) }}</span>
                                    @endif
                                    <span class="es-show-stamp es-show-vstamp">{{ $unitNo }}</span>
                                </div>

                                <div class="flex flex-1 flex-col p-5 sm:p-6">
                                    <div class="mb-3 flex items-start gap-3">
                                        <span class="es-show-badge">
                                            @if ($schedule['profile_image_url'] ?? false)
                                                <picture>
                                                    <source srcset="{{ url(webp_path($schedule['profile_image_url'])) }}" type="image/webp">
                                                    <img src="{{ url($schedule['profile_image_url']) }}"
                                                         alt="The {{ $schedule['name'] }} logo"
                                                         loading="lazy"
                                                         decoding="async"
                                                         width="44"
                                                         height="44">
                                                </picture>
                                            @else
                                                <span class="es-show-letter" aria-hidden="true">{{ strtoupper(substr($schedule['name'], 0, 1)) }}</span>
                                            @endif
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="es-show-hover-title es-show-ink text-lg font-bold leading-tight transition-colors">{{ $schedule['name'] }}</h3>
                                            <p class="es-show-asset mt-1">{{ $schedule['subdomain'] }}.eventschedule.com</p>
                                        </div>
                                    </div>

                                    @if ($schedule['description'] ?? false)
                                        <p class="es-show-muted text-sm">{{ $schedule['description'] }}</p>
                                    @endif

                                    <div class="es-show-hair mt-auto flex items-center justify-between gap-3 border-t pt-4">
                                        <span class="es-show-muted text-xs font-semibold uppercase tracking-widest">{{ $unitKinds[$schedule['subdomain']] ?? 'Demo' }}</span>
                                        <span class="es-show-open inline-flex items-center gap-1.5 text-sm font-semibold">
                                            Open it
                                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach
    @else
    <section class="es-show-hair scroll-mt-24 border-t py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <p class="es-show-muted">Demo schedules coming soon.</p>
        </div>
    </section>
    @endif

    <!-- ============================================================ -->
    <!-- 4. Lights down: what you are actually looking at             -->
    <!-- ============================================================ -->
    <section id="lights" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-show-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-show-beam"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-show-tag mb-4" data-reveal>Lights down</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        Look closer. <span class="es-show-lit">These are the parts.</span>
                    </h2>
                    <p class="es-show-muted text-lg" data-reveal style="--reveal-delay: 0.14s;">
                        No mystery, no mockups. Here is what a page like the ones above can show you, what that part is called in Event Schedule, and what it costs to build.
                    </p>
                </div>

                <div class="es-show-plate p-5 sm:p-7" data-reveal="panel">
                    <table class="w-full border-collapse text-left">
                        <caption class="sr-only">Each visible part of a published schedule, the feature behind it, and the plan it needs</caption>
                        <thead>
                            <tr class="es-show-tag">
                                <th scope="col" class="pb-3 pe-3 font-bold">What you can see</th>
                                <th scope="col" class="hidden pb-3 pe-3 font-bold sm:table-cell">The part</th>
                                <th scope="col" class="pb-3 text-right font-bold">Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($spec as [$specSee, $specPart, $specPlan])
                                <tr class="es-show-hair border-t">
                                    <th scope="row" class="es-show-ink py-3 pe-3 align-top text-sm font-bold">
                                        {{ $specSee }}
                                        <span class="es-show-muted mt-1 block text-xs font-normal sm:hidden">{{ $specPart }}</span>
                                    </th>
                                    <td class="es-show-muted hidden py-3 pe-3 align-top text-sm sm:table-cell">{{ $specPart }}</td>
                                    <td class="py-3 text-right align-top">
                                        @if ($specPlan === 'Free')
                                            <span class="es-show-plan">Free</span>
                                        @elseif ($specPlan === 'Pro')
                                            <span class="es-show-plan es-show-plan-pro">Pro</span>
                                        @else
                                            <span class="es-show-plan es-show-plan-ent">Enterprise</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="es-show-muted es-show-hair mt-5 border-t pt-4 text-xs">
                        The plan column is what that part costs to build. It is not a claim about which plan any one demo above is on.
                    </p>
                </div>

                <p class="es-show-muted mt-10 text-center" data-reveal>
                    {{ $specFree }} of those {{ count($spec) }} parts cost nothing. The {{ count($spec) - $specFree }} that cost something are dropping the credit line and a domain of your own. Selling starts free and so does scanning at the door, and Event Schedule takes nothing from the door on any plan.
                    <a href="{{ marketing_url('/pricing') }}" class="es-show-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        See the plans
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Build yours                                               -->
    <!-- ============================================================ -->
    <section id="build" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-show-tag mb-4" data-reveal>Build yours</p>
                <h2 class="es-balance es-show-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Three steps to a unit <span class="es-show-accent">of your own.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="110">
                @foreach ($steps as [$stepNo, $stepTitle, $stepBody])
                    <div class="es-show-plate flex flex-col p-7" data-reveal="panel">
                        <div class="es-show-accent mb-3 font-mono text-2xl font-black">{{ $stepNo }}</div>
                        <h3 class="es-show-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-show-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="es-show-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                One team member is included on the free plan. Adding more people to a schedule is part of Enterprise, capped at five.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Other floors: the same parts, fitted out for another trade -->
    <!-- ============================================================ -->
    <section id="trades" class="es-show-hair scroll-mt-24 border-t py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-show-tag mb-4" data-reveal>Other floors</p>
                <h2 class="es-balance es-show-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Same parts, <span class="es-show-accent">fitted out differently.</span>
                </h2>
                <p class="es-show-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.14s;">
                    Nothing on the floors below is a different product. It is this schedule, arranged the way one trade actually works.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="80">
                @foreach ($floors as [$floorHref, $floorName, $floorNote, $floorIcon])
                    <a href="{{ marketing_url($floorHref) }}" data-reveal class="es-show-plate es-show-hover group flex flex-col p-6 text-center">
                        <span class="es-show-accent mx-auto mb-3 inline-flex h-11 w-11 items-center justify-center">
                            {!! $floorIcon !!}
                        </span>
                        <span class="es-show-hover-title es-show-ink block text-sm font-semibold transition-colors">{{ $floorName }}</span>
                        <span class="es-show-muted es-show-hair mt-auto block border-t pt-3 text-xs leading-snug">{{ $floorNote }}</span>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-show-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-show-hair scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="es-show-tag mb-4" data-reveal>At the desk</p>
                <h2 class="es-balance es-show-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">
                    Frequently asked <span class="es-show-accent">questions</span>
                </h2>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-show-plate es-show-hover group p-6" data-reveal>
                        <summary class="es-show-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-show-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-show-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-show-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-show-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Finale: the empty space on the floor                      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-show-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-show-beam"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-show-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        There is a space on this floor <span class="es-show-lit">with your name on it.</span>
                    </h2>
                    <p class="es-show-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Publishing a schedule and its dates is free forever, and so are the first 25 paid tickets you sell each month and scanning them in at the door. Five dollars a month lifts that ceiling and adds the live check-in dashboard, and nothing is taken from the door on any plan.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-show-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-show-muted mt-6 text-sm">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-show-plate pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    {{-- NOTE: this renders NOTHING today. The component reads
         config('marketing_related.' . request()->path()) and there is no
         'examples' key in config/marketing_related.php, so the @if guard is
         false and the strip is absent. It is left in place so the page picks
         the strip up the moment that key is added. Until then the internal
         linking on this page is carried by "Other floors" above, which is
         why that section is a real content block and not four bare labels. --}}
    <x-marketing.related-pages />

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
