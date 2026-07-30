<x-marketing-layout>
    <x-slot name="title">Virtual & Online Event Hosting - Event Schedule</x-slot>
    <x-slot name="description">Host virtual events from one link field: tick Online, paste the URL people join on, and the listing, the ticket and the search-engine markup all follow. Tick In person too and it is a hybrid.</x-slot>
    <x-slot name="breadcrumbTitle">Online Events</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Online Events",
        "description": "An online event is one link field on the event. Tick Online, paste the URL people join on, and the listing, the ticket and the search-engine markup follow. Tick In person as well and the same event is a hybrid.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "One link field on any event, for any platform",
            "In person and online on the same event, for hybrids",
            "Hybrid events published as MixedEventAttendanceMode",
            "An online-only listing shows the link's domain, never the join link",
            "The full join link printed on every ticket",
            "An Online filter that appears on your public schedule",
            "Free registration with a capacity limit counted per date",
            "Ticket sales through your own Stripe account with zero platform fees"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Online events are on the free plan"
        }
    }
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
           For online-events "Go Live" styles. KEEP THIS NICKNAME.

           THE CONCEPT IS THE SECOND BOX. On the event form, "In person"
           and "Online" are two independent CHECKBOXES, not a switch
           (event/edit.blade.php: isInPerson / isOnline). Going live is
           ticking the second one and pasting a link. Tick both and the
           same event keeps a room and a link, which is what a hybrid
           actually is in this product. So the page's repeating mark is
           a pair of boxes - [x][ ], [ ][x], [x][x] - printed above
           every section to say which combination that section is about.
           The metaphor and the feature story are the same sentence.

           MARK GRAMMAR, and it is binding. A section's mark shows the
           tick combination that section is ABOUT, so it can be read
           before the heading: [x][ ] a room, [ ][x] a link, [x][x] a
           hybrid. Sections whose argument only exists once the room
           stops being the location (where the link lands, no platform
           to connect, a door price on a link, timezones, going live)
           take [ ][x]. The one section about all three combinations
           carries all three pairs, generated from $combinations so the
           mark cannot drift from the table under it. Do not assign a
           mark for visual variety.

           THE SIGNATURE DEVICE IS A COMBINATION TABLE, not a diagram.
           Three legal tick combinations, and for each one what the
           public listing's location line says, what the ticket carries
           and what search engines are told. Every cell is read off the
           code: Event::getEventUrlDomain(), event/show-guest.blade.php
           (venue name wins, domain only when there is no venue),
           ticket/view.blade.php (event_url wins over the address), and
           Event::getSchemaAttendanceMode() (Offline / Online / Mixed).

           WHAT IT REFUSES TO DRAW. No platform integrations, no live
           viewer counts, no attendee caps that are not real. An online
           event is ONE generic `event_url` field whose help text is
           "The link attendees will use to join the online event". The
           page says that out loud, because "any link works" is the
           feature. The one real exception, the Outlook Teams toggle
           (roles.microsoft_create_teams_meetings), is named as an
           exception rather than dressed up as a platform integration.

           NEIGHBOURS: /for-webinars is "On Air", a control room in
           teal; /for-watch-parties is "The Screening"; /for-virtual-
           conferences is "The Agenda". None of them own a checkbox
           pair or a combination table, and this page has no rack
           panel, no running order and no proportional time axis.

           COLOUR: the page keeps its inherited blue family, but spends
           it as TWO FLAT INKS on backlit-glass surfaces instead of the
           three-stop sky gradient it used to carry, because a bright
           sky stop on a light ground scores about 2.4 and gradient
           heading text is scored stop by stop. Measured on this page's
           own grounds:
             ink    #0f1826 on #f2f5fa 16.30 / #e9eef7 on #080b12 16.91
             muted  #4b5568 on #f2f5fa  6.87 / #9aa8c0 on #080b12  8.19
             accent #0b57c2 on #f2f5fa  6.09 / #7db9ff on #080b12  9.61
             band   #e9eef7 on #0a1119 16.29, #9aa8c0 7.89, lit
                    #8cc2ff 10.18, and 8.38 on the band card #151c24
             button #ffffff on #0b57c2  6.65 / #08111f on #7db9ff  9.24

           NEVER text-gray-500 here: 4.83 on pure white but only ~4.4 on
           this tinted ground. Use .es-golive-muted (6.87).

           BLADE RULE for this block: never use an @supports probe. A
           "#" hex inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-golive-page { background-color: #f2f5fa; color: #0f1826; }
        .dark .es-golive-page { background-color: #080b12; color: #e9eef7; }
        .es-golive-alt { background-color: #e8edf7; }
        .dark .es-golive-alt { background-color: #0b0f17; }
        .es-golive-ink { color: #0f1826; }
        .dark .es-golive-ink { color: #e9eef7; }
        .es-golive-muted { color: #4b5568; }
        .dark .es-golive-muted { color: #9aa8c0; }
        .es-golive-accent { color: #0b57c2; }
        .dark .es-golive-accent { color: #7db9ff; }
        /* Always-lit inks, for the bands that stay dark in both colour modes.
           Never a dark: utility inside a band, and never an arbitrary Tailwind
           colour class: the build is frozen, so an arbitrary hex utility that
           is not already in the compiled CSS would be a silent no-op. */
        .es-golive-lit { color: #8cc2ff; }
        .es-golive-onink { color: #e9eef7; }
        .es-golive-onmuted { color: #9aa8c0; }

        /* Hairline divider inside a card. */
        .es-golive-hr { border-top: 1px solid rgba(15, 24, 38, 0.1); }
        .dark .es-golive-hr { border-top-color: rgba(233, 238, 247, 0.12); }

        /* --- Backlit glass: the page's material. A hairline of light on
               the top edge, a faint blue cast, no heavy borders. --- */
        .es-golive-card {
            border: 1px solid rgba(15, 24, 38, 0.1);
            border-radius: 1rem;
            background: #ffffff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 1px 2px rgba(15, 24, 38, 0.04);
        }
        .dark .es-golive-card {
            border-color: rgba(233, 238, 247, 0.1);
            background: rgba(233, 238, 247, 0.04);
            box-shadow: inset 0 1px 0 rgba(233, 238, 247, 0.06);
        }

        /* --- Fixed-dark band --- */
        .es-golive-band {
            background-color: #0a1119;
            background-image: radial-gradient(115% 90% at 50% 0%, #131d2a 0%, #0d1520 55%, #070b11 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(233, 238, 247, 0.05);
        }
        .es-golive-band .es-golive-card {
            border-color: rgba(233, 238, 247, 0.12);
            background: rgba(233, 238, 247, 0.05);
            box-shadow: inset 0 1px 0 rgba(233, 238, 247, 0.06);
        }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-golive-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 238, 247, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 238, 247, 0.05) 1px, transparent 1px);
        }
        .es-golive-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-golive-band .es-claim:focus-within {
            border-color: rgba(140, 194, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(140, 194, 255, 0.22);
        }

        /* --- The mark: two boxes, ticked or not. The page's glyph. --- */
        .es-golive-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
        }
        .es-golive-boxes { display: inline-flex; gap: 0.25rem; }
        /* Three pairs side by side (the combination section's mark). The gap between
           pairs has to beat the 0.25rem gap inside one, or six boxes read as a row of
           six instead of three pairs. */
        .es-golive-markset { display: inline-flex; gap: 0.7rem; }
        .es-golive-box {
            position: relative;
            width: 0.85rem;
            height: 0.85rem;
            flex: none;
            border-radius: 0.2rem;
            border: 1.5px solid rgba(15, 24, 38, 0.35);
        }
        .dark .es-golive-box { border-color: rgba(233, 238, 247, 0.38); }
        .es-golive-band .es-golive-box { border-color: rgba(233, 238, 247, 0.42); }
        .es-golive-box-on {
            border-color: #0b57c2;
            background: #0b57c2;
        }
        .dark .es-golive-box-on { border-color: #7db9ff; background: #7db9ff; }
        .es-golive-band .es-golive-box-on { border-color: #8cc2ff; background: #8cc2ff; }
        /* The tick: two strokes, drawn with a rotated corner. */
        .es-golive-box-on::after {
            content: "";
            position: absolute;
            left: 0.24rem;
            top: 0.06rem;
            width: 0.2rem;
            height: 0.42rem;
            border: solid #ffffff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .dark .es-golive-box-on::after { border-color: #08111f; }
        .es-golive-band .es-golive-box-on::after { border-color: #08111f; }
        .es-golive-mark-label {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #4b5568;
        }
        .dark .es-golive-mark-label { color: #9aa8c0; }
        .es-golive-band .es-golive-mark-label { color: #8cc2ff; }

        /* --- The live pip: one dot, one ring. Used twice on the page. --- */
        .es-golive-pip {
            position: relative;
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            flex: none;
            border-radius: 9999px;
            background: #0b57c2;
        }
        .dark .es-golive-pip { background: #7db9ff; }
        .es-golive-band .es-golive-pip { background: #8cc2ff; }
        .es-golive-pip::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            border: 1.5px solid rgba(11, 87, 194, 0.6);
            animation: es-golive-ring 3.2s ease-out infinite;
        }
        .dark .es-golive-pip::after { border-color: rgba(125, 185, 255, 0.6); }
        .es-golive-band .es-golive-pip::after { border-color: rgba(140, 194, 255, 0.6); }
        @keyframes es-golive-ring {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(3.6); opacity: 0; }
        }

        /* --- Mono link type. The URL is the object, so it is set. --- */
        .es-golive-url {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.82rem;
            word-break: break-all;
            color: #0b57c2;
        }
        .dark .es-golive-url { color: #7db9ff; }

        /* --- The form fragment in the hero: a real field, drawn. --- */
        .es-golive-field {
            border: 1px solid rgba(15, 24, 38, 0.16);
            border-radius: 0.5rem;
            background: #f7f9fd;
        }
        .dark .es-golive-field { border-color: rgba(233, 238, 247, 0.16); background: rgba(233, 238, 247, 0.04); }

        /* --- The ticket stub: torn along the top. --- */
        .es-golive-stub {
            border: 1px solid rgba(15, 24, 38, 0.12);
            border-top: 2px dashed rgba(15, 24, 38, 0.28);
            border-radius: 0 0 0.75rem 0.75rem;
            background: #ffffff;
        }
        .dark .es-golive-stub {
            border-color: rgba(233, 238, 247, 0.12);
            border-top-color: rgba(233, 238, 247, 0.3);
            background: rgba(233, 238, 247, 0.05);
        }

        /* --- The combination table --- */
        .es-golive-tablewrap { overflow-x: auto; }
        .es-golive-table { width: 100%; min-width: 40rem; border-collapse: collapse; text-align: left; }
        .es-golive-table th,
        .es-golive-table td { padding: 0.85rem 0.9rem; vertical-align: top; }
        .es-golive-table thead th {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #4b5568;
            border-bottom: 1px solid rgba(15, 24, 38, 0.14);
        }
        .dark .es-golive-table thead th { color: #9aa8c0; border-bottom-color: rgba(233, 238, 247, 0.14); }
        .es-golive-table tbody tr + tr { border-top: 1px solid rgba(15, 24, 38, 0.09); }
        .dark .es-golive-table tbody tr + tr { border-top-color: rgba(233, 238, 247, 0.09); }
        .es-golive-table tbody th { font-weight: 700; color: #0f1826; }
        .dark .es-golive-table tbody th { color: #e9eef7; }
        .es-golive-table tbody td { color: #4b5568; font-size: 0.9rem; }
        .dark .es-golive-table tbody td { color: #9aa8c0; }
        /* Hold the two narrow columns open so "In person only" and a schema.org
           value each sit on one line instead of breaking mid-word. */
        .es-golive-table th:first-child,
        .es-golive-table td:first-child { min-width: 8.5rem; }
        .es-golive-table th:last-child,
        .es-golive-table td:last-child { min-width: 13.75rem; }
        .es-golive-table .es-golive-url { font-size: 0.71rem; }

        /* --- Eyebrow, chips, plan tags --- */
        .es-golive-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5568;
        }
        .dark .es-golive-tag { color: #9aa8c0; }

        .es-golive-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 24, 38, 0.14);
            background: rgba(255, 255, 255, 0.75);
            color: #4b5568;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .dark .es-golive-chip {
            border-color: rgba(233, 238, 247, 0.16);
            background: rgba(233, 238, 247, 0.05);
            color: #9aa8c0;
        }

        .es-golive-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(11, 87, 194, 0.45);
            color: #0b57c2;
        }
        .dark .es-golive-plan { border-color: rgba(125, 185, 255, 0.45); color: #7db9ff; }
        .es-golive-band .es-golive-plan { border-color: rgba(140, 194, 255, 0.45); color: #8cc2ff; }
        .es-golive-plan-pro { border-color: rgba(15, 24, 38, 0.35); color: #0f1826; }
        .dark .es-golive-plan-pro { border-color: rgba(233, 238, 247, 0.38); color: #e9eef7; }
        .es-golive-band .es-golive-plan-pro { border-color: rgba(233, 238, 247, 0.38); color: #e9eef7; }

        /* --- Rules and links --- */
        .es-golive-rule-t { border-top: 1px solid rgba(15, 24, 38, 0.08); }
        .dark .es-golive-rule-t { border-top-color: rgba(233, 238, 247, 0.08); }

        .es-golive-link { color: #0b57c2; }
        .es-golive-link:hover { color: #0f1826; }
        .dark .es-golive-link { color: #7db9ff; }
        .dark .es-golive-link:hover { color: #e9eef7; }

        .es-golive-btn {
            background-color: #0b57c2;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(11, 87, 194, 0.55);
        }
        .es-golive-btn:hover { background-color: #09489f; box-shadow: 0 22px 44px -14px rgba(11, 87, 194, 0.65); }
        .dark .es-golive-btn { background-color: #7db9ff; color: #08111f; }
        .dark .es-golive-btn:hover { background-color: #9ecbff; }

        /* --- Hover treatment shared by FAQ rows and related cards --- */
        .es-golive-hover:hover { border-color: rgba(11, 87, 194, 0.45); }
        .dark .es-golive-hover:hover { border-color: rgba(125, 185, 255, 0.45); }
        .es-golive-hover:hover .es-golive-hover-title,
        .es-golive-hover:hover .es-golive-hover-arrow { color: #0b57c2; }
        .dark .es-golive-hover:hover .es-golive-hover-title,
        .dark .es-golive-hover:hover .es-golive-hover-arrow { color: #7db9ff; }

        /* --- Dot-nav tooltip --- */
        .es-golive-tip {
            border: 1px solid rgba(15, 24, 38, 0.12);
            background: #ffffff;
            color: #4b5568;
        }
        .dark .es-golive-tip { border-color: rgba(233, 238, 247, 0.12); background: #11141b; color: #9aa8c0; }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(11, 87, 194, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(125, 185, 255, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(11, 87, 194, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(125, 185, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0b57c2; }
        .dark .es-dot.is-active .es-dot-pip { background: #7db9ff; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-golive-page a:focus-visible,
        #es-golive-page summary:focus-visible,
        #es-golive-page button:focus-visible {
            outline: 2px solid #0b57c2;
            outline-offset: 3px;
        }
        .dark #es-golive-page a:focus-visible,
        .dark #es-golive-page summary:focus-visible,
        .dark #es-golive-page button:focus-visible {
            outline-color: #7db9ff;
        }
        .es-golive-band a:focus-visible,
        .es-golive-band summary:focus-visible,
        .es-golive-band button:focus-visible {
            outline-color: #8cc2ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            /* The only animation this page owns. Everything else it moves is a shared
               es-* effect, already killed in marketing.css. */
            .es-golive-pip::after { animation: none !important; opacity: 0; }
        }
    </style>

    @php
        // The three legal tick combinations, and what each one produces. Every cell is
        // read off the code, not invented: the listing's location line comes from
        // event/show-guest.blade.php (the venue name wins; Event::getEventUrlDomain()
        // only fills in when there is no venue), the ticket line from
        // ticket/view.blade.php (event_url wins over the address), and the last column
        // from Event::getSchemaAttendanceMode().
        $combinations = [
            [
                'boxes' => [true, false],
                'name' => 'In person only',
                'listing' => 'The venue name, with its address underneath and a map link.',
                'ticket' => 'The address, linked through to a map.',
                'schema' => 'OfflineEventAttendanceMode',
            ],
            [
                'boxes' => [false, true],
                'name' => 'Online only',
                'listing' => 'The link\'s domain on its own, so people can see it is a Zoom call without being able to walk into it.',
                'ticket' => 'The whole join link, live and clickable.',
                'schema' => 'OnlineEventAttendanceMode',
            ],
            [
                'boxes' => [true, true],
                'name' => 'Both, a hybrid',
                'listing' => 'The venue name and address, because the room is where most people are going.',
                'ticket' => 'The whole join link, for the people watching from home.',
                'schema' => 'MixedEventAttendanceMode',
            ],
        ];

        // Where one pasted URL actually turns up. Three stops, plus one honest gap.
        $stops = [
            [
                'label' => 'On the public listing',
                'title' => 'The domain, and nothing more',
                'body' => 'An online event with no venue shows the host of the link on its location line. Enough to tell somebody what they are joining, not enough to join it.',
                'sample' => 'meet.google.com',
            ],
            [
                'label' => 'On the ticket',
                'title' => 'The whole link',
                'body' => 'The ticket page carries the full join link, live and clickable, at the top where the address would be. A free registration produces a ticket too, so people who signed up without paying get it as well.',
                // What the ticket actually prints: UrlUtils::clean() drops the scheme and any
                // "www.", and the whole URL is the href. So no "https://" in this sample.
                'sample' => 'meet.google.com/kfr-hxbz-qde',
            ],
            [
                'label' => 'On your schedule',
                'title' => 'An Online filter, unasked for',
                // Scoped honestly: hasOnlineEvents in role/partials/calendar.blade.php reads
                // eventsForFilters, which is the events in the month being viewed (or every
                // future event in list view), not the whole calendar.
                'body' => 'As soon as the dates a visitor is looking at include an event with a link, an Online toggle appears in the filters on your public schedule. Nobody has to tag anything for it to show up.',
                'sample' => 'Online only',
            ],
        ];

        $platforms = ['Zoom', 'Google Meet', 'Microsoft Teams', 'YouTube Live', 'Twitch', 'Jitsi', 'Webex', 'Discord', 'A page on your own site'];

        $steps = [
            ['01', 'Tick Online', 'On the event, In person and Online are two separate boxes. Tick Online. Tick In person as well if the room is happening too.'],
            ['02', 'Paste the link', 'One field, any URL: a meeting room, a stream, a page on your own site. There is no account to connect first.'],
            ['03', 'Publish it', 'The listing, the ticket, the calendar file and the search-engine markup all take their cue from that one field.'],
        ];

        $useCases = [
            ['Webinars', 'Sessions, demos and training. One link, an audience anywhere.', '/for-webinars'],
            ['Online Classes', 'A course is a term: one recurring event that ends after a set number of sessions.', '/for-online-classes'],
            ['Virtual Conferences', 'A day with a running order printed inside one event.', '/for-virtual-conferences'],
            ['Live Concerts', 'Stream the room to the people who could not get to it.', '/for-live-concerts'],
            ['Live Q&A Sessions', 'An hour of alternating turns, published as an agenda.', '/for-live-qa-sessions'],
            ['Watch Parties', 'A screening is a night, not a link. The link is just the door.', '/for-watch-parties'],
        ];

        $faqs = [
            [
                'q' => 'Which platforms can I use for an online event?',
                'a' => 'Any of them, because it is a link and not an integration. Zoom, Google Meet, Microsoft Teams, YouTube Live, Twitch, Jitsi, a Discord invite or a page on your own site all work the same way: paste the URL people join on into the event\'s link field. There is no account to connect, nothing to reconnect when a token expires, and nothing read back from the platform, so Event Schedule does not show live viewer counts or who is in the room.',
            ],
            [
                'q' => 'Who can see the join link?',
                'a' => 'The public listing shows only the domain the event is hosted on, so a visitor can tell it is a Zoom call without being able to walk into it. The full link is printed on the ticket, at the top where the venue address would be. Free registration produces a ticket as well, so people who signed up without paying get the link the same way buyers do.',
            ],
            [
                'q' => 'Can one event be in person and online at the same time?',
                'a' => 'Yes. In person and Online are two independent checkboxes on the event, so a hybrid is one event with both ticked rather than two events to keep in step. The listing keeps the venue name and address, the ticket carries the join link, and the event is published to search engines as a mixed-attendance event.',
            ],
            [
                'q' => 'Can I sell tickets to an online event?',
                'a' => 'Yes. Ticketing is on the Pro plan at $5 a month and works exactly the same for an online event as for a room: named ticket types with their own prices and quantities, per-attendee tickets, and payment through your own Stripe account. Event Schedule charges zero platform fees, so past Stripe\'s own processing the money is yours. Free registration with a capacity limit is on the free plan, and the cap is counted per date.',
            ],
            [
                'q' => 'What time will people in other countries see?',
                'a' => 'The time you enter is anchored to your schedule\'s timezone rather than to whichever device typed it, and the public page shows it in that timezone for everybody. It does not rewrite itself per visitor, so if your audience is spread out it is worth naming the timezone in the event title or description. Add to calendar is the part that converts: the calendar file is stamped in UTC, so an attendee\'s own calendar shows the hour they should turn up.',
            ],
            [
                'q' => 'Does the join link go into the calendar file?',
                'a' => 'No. The .ics that Add to calendar downloads puts the venue address in the location field, and an online event has no address to put there, so the entry lands without a link. Tell people the link is on their ticket, or paste it into the event description where it will be part of the calendar entry\'s notes.',
            ],
            [
                'q' => 'Do I need a paid plan to run online events?',
                'a' => 'No. Online events are on the free plan, along with recurring dates, sub-schedules, the embeddable calendar, two-way calendar sync, built-in analytics and free registration. Ticketing, QR check-in and the waitlist are the Pro features, at $5 a month.',
            ],
        ];

        $dotSections = [
            ['top', 'The second box'],
            ['boxes', 'Two boxes'],
            ['shapes', 'Three shapes'],
            ['link', 'Where the link goes'],
            ['any', 'Any link at all'],
            ['sell', 'Charging for it'],
            ['time', 'The hour it starts'],
            ['rest', 'Everything else'],
            ['who', 'Who goes live'],
            ['faq', 'Questions'],
            ['claim', 'Go live'],
        ];
    @endphp

    <div id="es-golive-page" class="es-golive-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the second box                                      -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(78svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(11, 87, 194, 0.2), rgba(11, 87, 194, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(125, 185, 255, 0.16), rgba(125, 185, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <span class="es-golive-pip" aria-hidden="true"></span>
                        <span class="es-golive-muted text-sm font-medium tracking-wide">Online and hybrid events</span>
                    </div>

                    <h1 class="es-balance es-golive-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Going live is</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">one <span class="es-golive-accent">checkbox.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-golive-muted mb-10 max-w-xl text-lg sm:text-xl">
                        An event can be in a room, on a link, or both at once. Tick Online, paste the URL people join on, and the listing, the ticket and the markup search engines read all follow from that one field.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-golive-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.creating_events') }}" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Events guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The object: the piece of the event form that does all of this. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-golive-card p-6 sm:p-7">
                        <p class="es-golive-tag mb-5">The event form</p>

                        <div class="space-y-3" aria-hidden="true">
                            <div class="flex items-center gap-3">
                                <span class="es-golive-box"></span>
                                <span class="es-golive-muted text-sm font-medium">In person</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="es-golive-box es-golive-box-on"></span>
                                <span class="es-golive-ink text-sm font-semibold">Online</span>
                                <span class="es-golive-pip ms-1"></span>
                            </div>
                        </div>

                        <div class="mt-6" aria-hidden="true">
                            <p class="es-golive-ink mb-1.5 text-sm font-semibold">Event URL</p>
                            <div class="es-golive-field px-3 py-2.5">
                                <span class="es-golive-url">https://meet.google.com/kfr-hxbz-qde</span>
                            </div>
                            <p class="es-golive-muted mt-1.5 text-[0.7rem]">The link attendees will use to join the online event.</p>
                        </div>

                        <p class="es-golive-muted es-golive-hr mt-6 pt-4 text-xs">
                            That is the whole feature: one box and one field, on the same form you already fill in. Everything further down this page is what the product does with them.
                        </p>
                    </div>
                </div>
            </div>

            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach ($platforms as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-golive-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Two boxes, not a switch (fixed-dark band)                 -->
    <!-- ============================================================ -->
    <section id="boxes" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-golive-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-golive-mark mb-5" data-reveal>
                        <span class="es-golive-boxes" aria-hidden="true">
                            <span class="es-golive-box es-golive-box-on"></span>
                            <span class="es-golive-box es-golive-box-on"></span>
                        </span>
                        <span class="es-golive-mark-label">In person + Online</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Two boxes, <span class="es-golive-lit">not a switch.</span>
                    </h2>
                    <p class="mt-5 text-lg es-golive-onmuted" data-reveal style="--reveal-delay: 0.15s;">
                        Most calendars make you choose. Here they are independent, which is the only reason a hybrid can be one event instead of two.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-golive-card p-6" data-reveal="panel">
                        <div class="es-golive-mark mb-4">
                            <span class="es-golive-boxes" aria-hidden="true">
                                <span class="es-golive-box es-golive-box-on"></span>
                                <span class="es-golive-box"></span>
                            </span>
                            <span class="es-golive-mark-label">In person</span>
                        </div>
                        <h3 class="mb-2 text-lg font-bold es-golive-onink">A room</h3>
                        <p class="text-sm es-golive-onmuted">Pick the venue and its name and address go on the listing, the map link and the ticket. This is the box most events tick on their own.</p>
                    </div>
                    <div class="es-golive-card p-6" data-reveal="panel">
                        <div class="es-golive-mark mb-4">
                            <span class="es-golive-boxes" aria-hidden="true">
                                <span class="es-golive-box"></span>
                                <span class="es-golive-box es-golive-box-on"></span>
                            </span>
                            <span class="es-golive-mark-label">Online</span>
                        </div>
                        <h3 class="mb-2 text-lg font-bold es-golive-onink">A link</h3>
                        <p class="text-sm es-golive-onmuted">One field, holding the URL people join on. Untick it later and the link is cleared with it, so an event never keeps a dead room by accident.</p>
                    </div>
                    <div class="es-golive-card p-6" data-reveal="panel">
                        <div class="es-golive-mark mb-4">
                            <span class="es-golive-boxes" aria-hidden="true">
                                <span class="es-golive-box es-golive-box-on"></span>
                                <span class="es-golive-box es-golive-box-on"></span>
                            </span>
                            <span class="es-golive-mark-label">Both</span>
                        </div>
                        <h3 class="mb-2 text-lg font-bold es-golive-onink">A hybrid</h3>
                        <p class="text-sm es-golive-onmuted">Both ticked, and the event keeps its room and its link. One date, one description, one set of tickets, two ways in.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-golive-onmuted" data-reveal>
                    Nothing else about the event changes.
                    <a href="#shapes" class="es-golive-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        See what each combination produces
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The combination table                                     -->
    <!-- ============================================================ -->
    <section id="shapes" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                {{-- The one section that is about all three combinations gets all three pairs,
                     read straight off $combinations so the mark cannot drift from the table
                     underneath it. Pairs are held apart by the mark's own 0.55rem gap; the two
                     boxes inside a pair sit 0.25rem apart, so the grouping reads. --}}
                <div class="es-golive-mark mb-5" data-reveal>
                    <span class="es-golive-markset" aria-hidden="true">
                        @foreach ($combinations as $combo)
                            <span class="es-golive-boxes">
                                <span class="es-golive-box @if ($combo['boxes'][0]) es-golive-box-on @endif"></span>
                                <span class="es-golive-box @if ($combo['boxes'][1]) es-golive-box-on @endif"></span>
                            </span>
                        @endforeach
                    </span>
                    <span class="es-golive-mark-label">Three combinations</span>
                </div>
                <h2 class="es-balance es-golive-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One event, <span class="es-golive-accent">three shapes.</span>
                </h2>
                <p class="es-golive-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Two boxes give three sensible states, and each one changes what a visitor sees, what a ticket holder gets and what a search engine is told. Nothing here needs configuring.
                </p>
            </div>

            <div class="es-golive-card p-4 sm:p-7" data-reveal="panel">
                <div class="es-golive-tablewrap">
                    <table class="es-golive-table">
                        <caption class="sr-only">What each combination of the In person and Online checkboxes produces on the listing, on the ticket and in the event's structured data</caption>
                        <thead>
                            <tr>
                                <th scope="col">Ticked</th>
                                <th scope="col">The listing's location line</th>
                                <th scope="col">The ticket carries</th>
                                <th scope="col">Search engines are told</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($combinations as $combo)
                                <tr>
                                    <th scope="row">
                                        <span class="es-golive-mark">
                                            <span class="es-golive-boxes" aria-hidden="true">
                                                <span class="es-golive-box @if ($combo['boxes'][0]) es-golive-box-on @endif"></span>
                                                <span class="es-golive-box @if ($combo['boxes'][1]) es-golive-box-on @endif"></span>
                                            </span>
                                        </span>
                                        <span class="mt-2 block text-sm">{{ $combo['name'] }}</span>
                                    </th>
                                    <td>{{ $combo['listing'] }}</td>
                                    <td>{{ $combo['ticket'] }}</td>
                                    <td><span class="es-golive-url">{{ $combo['schema'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-golive-muted mt-5 text-xs">
                    The last column is the event's structured data, published on the event page so Google can tell an online event from a room and a hybrid from both.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Where the link shows up                                   -->
    <!-- ============================================================ -->
    <section id="link" class="es-golive-alt scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-golive-mark mb-5" data-reveal>
                    <span class="es-golive-boxes" aria-hidden="true">
                        <span class="es-golive-box"></span>
                        <span class="es-golive-box es-golive-box-on"></span>
                    </span>
                    <span class="es-golive-mark-label">One field, three places</span>
                </div>
                <h2 class="es-balance es-golive-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    You paste it once. <span class="es-golive-accent">It lands in three places.</span>
                </h2>
                <p class="es-golive-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    And it is deliberately not the same link in all three. The public gets the domain; the people who signed up get the door.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($stops as $stopIndex => $stop)
                    <div class="es-golive-card flex flex-col p-7" data-reveal="panel">
                        <p class="es-golive-tag mb-4">{{ $stop['label'] }}</p>
                        <h3 class="es-golive-ink mb-3 text-lg font-bold">{{ $stop['title'] }}</h3>
                        <p class="es-golive-muted mb-6 text-sm leading-relaxed">{{ $stop['body'] }}</p>
                        {{-- The first two samples are URLs, drawn exactly like the hero's URL
                             field: no leading glyph. The third is a filter switch, so it gets a
                             ticked box. The live pip is reserved for the two places on the page
                             that actually mean "on air", the hero and the finale. --}}
                        <div class="es-golive-field mt-auto flex items-center gap-2 px-3 py-2.5">
                            @if ($stopIndex === 2)
                                <span class="es-golive-box es-golive-box-on" aria-hidden="true"></span>
                            @endif
                            <span class="es-golive-url">{{ $stop['sample'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- The ticket stub, torn off: the one surface that carries the whole link. -->
            <div class="mx-auto mt-10 max-w-xl" data-reveal="panel">
                <div class="es-golive-stub p-6">
                    <div class="mb-4 flex items-baseline justify-between gap-3">
                        <span class="es-golive-ink text-base font-bold">Thursday Night Live</span>
                        <span class="es-golive-muted font-mono text-xs">Thu 8:00 PM</span>
                    </div>
                    <p class="es-golive-tag mb-2">Join here</p>
                    {{-- Printed the way the real ticket prints it: UrlUtils::clean() strips the
                         scheme, and the full URL stays behind it as the href. --}}
                    <p class="es-golive-url mb-5">meet.google.com/kfr-hxbz-qde</p>
                    <p class="es-golive-muted text-xs">
                        This is the ticket, not the listing. A paid ticket and a free registration both produce one, and both carry the link.
                    </p>
                </div>
            </div>

            <p class="es-golive-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                One honest gap: Add to calendar writes the venue address into the calendar entry, so an online event lands there without a link. Put it in the description if you want it in their calendar notes as well.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Any link at all                                           -->
    <!-- ============================================================ -->
    <section id="any" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-golive-mark mb-5" data-reveal>
                        <span class="es-golive-boxes" aria-hidden="true">
                            <span class="es-golive-box"></span>
                            <span class="es-golive-box es-golive-box-on"></span>
                        </span>
                        <span class="es-golive-mark-label">No integration</span>
                    </div>
                    <h2 class="es-balance es-golive-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                        If you can copy the link, <span class="es-golive-accent">it works.</span>
                    </h2>
                    <p class="es-golive-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.1s;">
                        There is no platform picker on this form, and that is the feature. Event Schedule holds a URL. Whatever is on the other end of it is yours to run.
                    </p>
                    <ul class="es-golive-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-golive-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Nothing to connect before you can publish, and no token to reconnect six months later.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-golive-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Move a series from one platform to another by editing one field on the event.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-golive-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Being straight with you: because nothing is read back, there are no live viewer counts and no in-room attendance here. What the platform knows stays with the platform.</span>
                        </li>
                    </ul>
                </div>

                <div class="space-y-6">
                    <div class="es-golive-card p-7" data-reveal="panel">
                        <p class="es-golive-tag mb-4">Links people paste in</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($platforms as $platform)
                                <span class="es-golive-chip">{{ $platform }}</span>
                            @endforeach
                        </div>
                        <p class="es-golive-muted mt-5 text-xs">
                            Examples, not integrations. None of these are connected to your account, and none of them need to be.
                        </p>
                    </div>

                    <div class="es-golive-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-golive-ink text-lg font-bold">The one exception</h3>
                            <span class="es-golive-plan">Free</span>
                        </div>
                        <p class="es-golive-muted text-sm leading-relaxed">
                            If your schedule syncs with Outlook, you can turn on "Create Teams meetings for online events". An event with no venue then gets a Teams meeting when it syncs out, and if it does not have a link yet, the Teams join link is written into its link field for you. It is the only place a link arrives on its own.
                        </p>
                        <a href="{{ marketing_url('/features/calendar-sync') }}" class="es-golive-link mt-4 inline-flex items-center gap-1 text-sm font-semibold transition-all hover:gap-2">
                            How calendar sync works
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Charging for it (fixed-dark band)                         -->
    <!-- ============================================================ -->
    <section id="sell" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-golive-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-golive-mark mb-5" data-reveal>
                        <span class="es-golive-boxes" aria-hidden="true">
                            <span class="es-golive-box"></span>
                            <span class="es-golive-box es-golive-box-on"></span>
                        </span>
                        <span class="es-golive-mark-label">Getting paid</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A link can have <span class="es-golive-lit">a door price.</span>
                    </h2>
                    <p class="mt-5 text-lg es-golive-onmuted" data-reveal style="--reveal-delay: 0.15s;">
                        Ticketing does not care whether the event has a room. Same ticket types, same checkout, same zero platform fees.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-golive-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-golive-onink">Sell seats to a stream</h3>
                            <span class="es-golive-plan es-golive-plan-pro">Pro</span>
                        </div>
                        <p class="text-sm es-golive-onmuted">Named ticket types with their own prices and quantities, paid through your own Stripe account. Event Schedule takes nothing from the sale.</p>
                    </div>
                    <div class="es-golive-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-golive-onink">One link each</h3>
                            <span class="es-golive-plan es-golive-plan-pro">Pro</span>
                        </div>
                        <p class="text-sm es-golive-onmuted">Per-attendee tickets give everybody in a booking their own confirmation and their own ticket, rather than one person forwarding the link to five.</p>
                    </div>
                    <div class="es-golive-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold es-golive-onink">Free, but counted</h3>
                            <span class="es-golive-plan">Free</span>
                        </div>
                        <p class="text-sm es-golive-onmuted">Registration with a capacity limit is on the free plan, and the cap is counted per date, so a weekly session fills and reopens on its own.</p>
                    </div>
                </div>

                <p class="mt-10 text-center es-golive-onmuted" data-reveal>
                    Running the room as well? QR check-in on the door and the join link on the ticket are the same ticket.
                    <a href="{{ marketing_url('/features/ticketing') }}" class="es-golive-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        How ticketing works
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The hour it starts                                        -->
    <!-- ============================================================ -->
    <section id="time" class="es-golive-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                {{-- Online only: this section's argument is what happens once the room stops
                     being the location, so the mark has to be the online-only pair. --}}
                <div class="es-golive-mark mb-5" data-reveal>
                    <span class="es-golive-boxes" aria-hidden="true">
                        <span class="es-golive-box"></span>
                        <span class="es-golive-box es-golive-box-on"></span>
                    </span>
                    <span class="es-golive-mark-label">Timezones</span>
                </div>
                <h2 class="es-balance es-golive-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    An audience with <span class="es-golive-accent">no single hour.</span>
                </h2>
                <p class="es-golive-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The moment the room stops being the location, your audience stops sharing a clock. Here is exactly how the time is handled, including the part that is on you.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-golive-card p-7" data-reveal="panel">
                    <h3 class="es-golive-ink mb-2 text-lg font-bold">Anchored to your schedule</h3>
                    <p class="es-golive-muted text-sm leading-relaxed">The time you type is stored against your schedule's timezone, not against the device that typed it. Edit an event from a hotel in another country and 8:00 PM still means 8:00 PM at home.</p>
                </div>
                <div class="es-golive-card p-7" data-reveal="panel">
                    <h3 class="es-golive-ink mb-2 text-lg font-bold">Add to calendar converts</h3>
                    <p class="es-golive-muted text-sm leading-relaxed">The calendar file is stamped in UTC, so when somebody saves the event their own calendar shows it in their own hours. The same is true of the subscribable feed.</p>
                </div>
                <div class="es-golive-card p-7" data-reveal="panel">
                    <h3 class="es-golive-ink mb-2 text-lg font-bold">The part that is on you</h3>
                    <p class="es-golive-muted text-sm leading-relaxed">The public page prints your schedule's time for everybody. It does not rewrite itself per visitor, so if you sell across timezones, name the zone in the title or the description.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="es-golive-alt scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-golive-mark mb-5" data-reveal>
                    <span class="es-golive-boxes" aria-hidden="true">
                        <span class="es-golive-box"></span>
                        <span class="es-golive-box es-golive-box-on"></span>
                    </span>
                    <span class="es-golive-mark-label">Everything else</span>
                </div>
                <h2 class="es-balance es-golive-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the calendar comes with it.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-golive-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-golive-ink text-xl font-bold">A weekly session is one event</h3>
                                <span class="es-golive-plan">Free</span>
                            </div>
                            <p class="es-golive-muted mb-4">Set the days it runs and it repeats on its own. Change the link once and every future date follows, which is the whole reason a recurring online series is worth setting up as one record.</p>
                            <p class="es-golive-muted text-sm">Skipping a week is a date exception: the date simply is not on the calendar, so nobody turns up to an empty room.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-golive-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-golive-ink text-xl font-bold">Announce when you are ready</h3>
                                <span class="es-golive-plan">Free</span>
                            </div>
                            <p class="es-golive-muted">A session you have not announced sits as a draft: on your calendar, off the public one, until you publish it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-golive-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-golive-ink text-xl font-bold">Strands, not folders</h3>
                                <span class="es-golive-plan">Free</span>
                            </div>
                            <p class="es-golive-muted">Sub-schedules group and colour-code what you run, so the online series and the room dates read apart on one link.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-golive-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-golive-ink text-xl font-bold">Tell the people who follow you</h3>
                                <span class="es-golive-plan">Free</span>
                            </div>
                            <p class="es-golive-muted mb-4">People follow your schedule and you write to them when the next session goes up. Newsletters are on the free plan, with open and click rates afterwards.</p>
                            <p class="es-golive-muted text-sm">The allowance counts recipients rather than sends: 10 a month on Free, 100 on Pro, 1,000 on Enterprise.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-golive-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-golive-ink text-xl font-bold">On the site you have</h3>
                                <span class="es-golive-plan">Free</span>
                            </div>
                            <p class="es-golive-muted">Embed the calendar on your own site so the sessions live where people already look you up.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-golive-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-golive-ink text-xl font-bold">What actually happened</h3>
                                <span class="es-golive-plan">Free</span>
                            </div>
                            <p class="es-golive-muted mb-4">Built-in analytics show page views, the devices people came on and where the traffic came from, so you can tell which announcement did the work.</p>
                            <p class="es-golive-muted text-sm">That is what they measure, and nothing more: they are page analytics, not audience analytics from inside your stream.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-golive-mark mb-5" data-reveal>
                    <span class="es-golive-boxes" aria-hidden="true">
                        <span class="es-golive-box"></span>
                        <span class="es-golive-box es-golive-box-on"></span>
                    </span>
                    <span class="es-golive-mark-label">Going live</span>
                </div>
                <h2 class="es-balance es-golive-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ($steps as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-golive-card p-7" data-reveal="panel">
                        <div class="es-golive-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-golive-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-golive-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Related features                                         -->
    <!-- ============================================================ -->
    <section class="es-golive-rule-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-golive-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Recurring Events"
                        description="Set a weekly session once and let it repeat, with skipped dates"
                        :url="marketing_url('/features/recurring-events')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell seats to a stream with zero platform fees"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your event schedule on any website"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Fan Videos"
                        description="Let fans share videos and comments on your events"
                        :url="marketing_url('/features/fan-videos')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-golive-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Who goes live                                            -->
    <!-- ============================================================ -->
    <section id="who" class="es-golive-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance es-golive-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal>
                    Who ticks the <span class="es-golive-accent">second box</span>
                </h2>
                <p class="es-golive-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Same field, different evenings. Each of these has a page of its own.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ($useCases as [$ucName, $ucBody, $ucHref])
                    <a href="{{ marketing_url($ucHref) }}" class="es-golive-hover es-golive-card group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                        <h3 class="es-golive-hover-title es-golive-ink mb-2 text-lg font-bold transition-colors">{{ $ucName }}</h3>
                        <p class="es-golive-muted mb-4 text-sm leading-relaxed">{{ $ucBody }}</p>
                        <span class="es-golive-hover-arrow es-golive-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-golive-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-golive-rule-t scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-golive-mark mb-5" data-reveal>
                    <span class="es-golive-boxes" aria-hidden="true">
                        <span class="es-golive-box es-golive-box-on"></span>
                        <span class="es-golive-box es-golive-box-on"></span>
                    </span>
                    <span class="es-golive-mark-label">Questions</span>
                </div>
                <h2 class="es-balance es-golive-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-golive-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they put a session online.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-golive-hover es-golive-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-golive-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-golive-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-golive-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-golive-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-golive-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-golive-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <div class="es-golive-mark mb-5 justify-center">
                        <span class="es-golive-pip" aria-hidden="true"></span>
                        <span class="es-golive-mark-label">Free plan</span>
                    </div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Tick the box. <span class="es-golive-lit">Go live.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-golive-onmuted">
                        Online events, recurring dates and free registration are on the free plan. Ticketing is five dollars a month, and nothing is taken from the sale.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-golive-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-golive-onmuted">No credit card required</p>
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
                        <span class="es-golive-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
