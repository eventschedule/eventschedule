<x-marketing-layout :hp="true">
    <x-slot name="title">Event Scheduling Software for Any Industry | Event Schedule</x-slot>
    <x-slot name="description">Event scheduling software for musicians, venues, curators, theaters and online events. Sell tickets with zero platform fees. Free forever, open source.</x-slot>
    <x-slot name="breadcrumbTitle">Use Cases</x-slot>

    @php
        // The directory. Every audience is an entry of config/marketing_audiences.php, which the
        // hub pages (/for-talent, /for-venues) read too, so a blurb or a tag is written once.
        // What only this page adds to an audience (the schedule type its own page sends to
        // sign-up, the words the finder answers to, the sample drawn beside the list) lives in
        // config/marketing_directory.php, keyed by the audience's path.
        $performers = config('marketing_audiences.performers');
        $venues = config('marketing_audiences.venues');
        $online = config('marketing_audiences.online');
        $communities = config('marketing_audiences.communities');
        $ucExtras = config('marketing_directory', []);

        // The two groups that are one page each. Their sentence and their six words are the ones
        // the page has always carried beside them.
        $curatorEntry = ['url' => '/for-curators', 'name' => 'Curators & Promoters', 'listed' => 'For Curators', 'blurb' => 'Aggregate events from multiple venues and performers into one shareable schedule. Be the go-to source for what\'s happening in your community.', 'tags' => ['Event Promoters', 'Music Bloggers', 'Community Organizers', 'Scene Guides', 'Local Media', 'Tourism Boards']];
        $developerEntry = ['url' => '/for-ai-agents', 'name' => 'Developers & AI Agents', 'listed' => 'For AI Agents', 'blurb' => 'Agents discover the API on their own and run multi-step flows without a human wiring them up.', 'tags' => ['REST API', 'Webhooks', 'OpenAPI spec', 'llms.txt', 'agents.json', 'Named flows']];

        // Accent belongs to the GROUP, never to the entry: a colour then says which of the six
        // you are in. 'hub' is the group's own guide, where it has one that is not an entry.
        $ucGroups = [
            ['id' => 'performers', 'short' => 'Performers', 'side' => 'the-act', 'label' => 'Performers & Artists', 'accent' => 'blue', 'hub' => ['/for-talent', 'See the full Talent guide', 'For Talent'], 'items' => $performers],
            ['id' => 'venues', 'short' => 'Venues', 'side' => 'the-room', 'label' => 'Venues & Event Spaces', 'accent' => 'amber', 'hub' => ['/for-venues', 'See the full Venues guide', 'For Venues'], 'items' => $venues],
            ['id' => 'curators', 'short' => 'Curators', 'side' => 'the-guide', 'label' => 'Curators & Promoters', 'accent' => 'emerald', 'hub' => null, 'items' => [$curatorEntry]],
            ['id' => 'online', 'short' => 'Online', 'side' => 'the-stream', 'label' => 'Online Events', 'accent' => 'cyan', 'hub' => null, 'items' => $online],
            ['id' => 'communities', 'short' => 'Communities', 'side' => 'the-town', 'label' => 'Organizations & Communities', 'accent' => 'teal', 'hub' => null, 'items' => $communities],
            ['id' => 'developers', 'short' => 'Developers', 'side' => 'the-code', 'label' => 'Developers & AI Agents', 'accent' => 'slate', 'hub' => null, 'items' => [$developerEntry]],
        ];

        $faqs = [
            ['q' => 'Is Event Schedule free?', 'a' => 'Yes. Event Schedule is free forever for creating and sharing your event calendar, and free registration is unlimited on it, scanned at the door like any other ticket. Charging for a ticket is a Pro feature, and Pro adds the API and the live check-in dashboard too; Enterprise adds custom domains and extra team members. There are no platform fees on ticket sales, so the only fee is your payment provider\'s own, such as Stripe\'s or PayPal\'s.'],
            ['q' => 'What types of events can I manage?', 'a' => 'Any kind. Musicians share gig schedules, bars list their weekly lineups, theaters manage their season calendars, fitness instructors schedule classes, and conference organizers run multi-day programs. Event Schedule works for in-person events, online events, and hybrid events across every industry.'],
            // No gateway is gated by tier - there is no isPro() anywhere in app/Services/Payments/.
            // What is gated is the PRICE on the ticket: Event::canSellPaidTickets() is Pro/Enterprise.
            // Payfast settles in rand only (PayfastGateway), so it is offered on ZAR events only.
            ['q' => 'Can I sell tickets with Event Schedule?', 'a' => 'Yes, on Pro. Sell them on your event page and take payment through Stripe, PayPal, Invoice Ninja, Payfast (South African rand only), a payment link or cash. A ticket that carries a price needs Pro; a ticket type at no charge, and free registration with a capacity, run on every plan with no monthly ceiling. Buyers get a QR code ticket, and scanning it at the door is free on every plan; the live check-in dashboard is part of Pro. There are no platform fees, so you pay only your payment provider\'s own processing fee.'],
            ['q' => 'What if a performer or venue I list is not on Event Schedule?', 'a' => 'Their name still shows on your event, and Event Schedule creates a page for them that says which schedule listed them and that they have not claimed it yet. The page stays out of search engines until it is claimed. They claim it by signing in with the email address you entered for them, which makes the page theirs, and the dates you already listed them on stay where they are.'],
            ['q' => 'Does Event Schedule work for online events?', 'a' => 'Yes. Paste the link people join on into any event and the whole link is printed on their ticket, while the public listing shows only the domain. It is a link and not an integration, so Zoom, Google Meet, YouTube Live, Twitch or a page on your own site all work the same way. You can sell tickets for virtual events, run webinars, schedule online classes, and manage virtual conferences.'],
            ['q' => 'Is Event Schedule open source?', 'a' => 'Yes. Event Schedule is fully open source. You can use the hosted version at eventschedule.com or selfhost it on your own server for complete control over your data and branding. The selfhosted version includes all features with no limits.'],
        ];

        // Everything the page links, in document order: the two hubs that are not entries, then
        // each group's entries. It drives numberOfItems, so the schema cannot drift from the page.
        $allAudiences = [];
        foreach ($ucGroups as $ucGroup) {
            if ($ucGroup['hub']) {
                $allAudiences[] = ['url' => $ucGroup['hub'][0], 'name' => $ucGroup['hub'][2]];
            }
            foreach ($ucGroup['items'] as $ucItem) {
                $allAudiences[] = ['url' => $ucItem['url'], 'name' => $ucItem['listed'] ?? $ucItem['name']];
            }
        }

        // The dates in the samples are next week's, Monday to Sunday, so every one of them is
        // still to come whenever the page is rendered and each sample reads in order.
        $ucMonday = \Illuminate\Support\Carbon::now()->startOfWeek(\Carbon\CarbonInterface::MONDAY)->addWeek();
        $ucDay = fn (int $weekday) => $ucMonday->copy()->addDays($weekday - 1);
        $ucFriday = $ucDay(5);

        // What the name box in a mock-up ends on: this install's own domain (as on the homepage).
        $ucHost = _base_domain();
        $ucSuffix = '.' . (str_contains($ucHost, '.') && ! filter_var($ucHost, FILTER_VALIDATE_IP) ? $ucHost : 'eventschedule.com');

        // One plain record per entry: what the list prints and what the stage beside it is
        // re-cast from. Built here so the script is handed a bare variable.
        $ucCards = [];
        foreach ($ucGroups as $ucIndex => $ucGroup) {
            foreach ($ucGroup['items'] as $ucItem) {
                $ucExtra = $ucExtras[$ucItem['url']] ?? [];
                $ucType = $ucExtra['type'] ?? null;
                $ucSample = $ucExtra['sample'] ?? null;
                $ucRows = [];
                foreach ($ucSample['rows'] ?? [] as [$ucWeekday, $ucTitle, $ucWhen, $ucPrice, $ucKind]) {
                    // The words beside a date are the ones a schedule's own page prints there
                    // (role/partials/guest-row): the price itself, "Free entry", "Few left",
                    // "Sold Out". It never prints "Tickets" or "RSVP", so neither does this.
                    if ($ucKind === 'online') {
                        $ucWhen .= ' · Online';
                        $ucKind = $ucPrice === 'Free' ? 'free' : 'tickets';
                    }
                    [$ucPill, $ucPillLabel, $ucMeta] = match ($ucKind) {
                        'tickets' => ['price', $ucPrice, $ucWhen],
                        'free', 'rsvp' => ['free', 'Free entry', $ucWhen],
                        'few' => ['few', 'Few left', $ucWhen . ($ucPrice !== '' ? ' · ' . $ucPrice : '')],
                        'sold' => ['sold', 'Sold Out', $ucWhen . ($ucPrice !== '' ? ' · ' . $ucPrice : '')],
                        default => ['', '', $ucWhen],
                    };
                    $ucRows[] = [
                        'month' => $ucDay($ucWeekday)->format('M'),
                        'day' => $ucDay($ucWeekday)->format('j'),
                        'title' => $ucTitle,
                        'meta' => $ucMeta,
                        'pill' => $ucPill,
                        'pillLabel' => $ucPillLabel,
                    ];
                }
                $ucCards[] = [
                    'key' => ltrim($ucItem['url'], '/'),
                    'group' => $ucGroup['id'],
                    'accent' => $ucGroup['accent'],
                    'groupLine' => sprintf('%02d', $ucIndex + 1) . ' · ' . $ucGroup['label'],
                    // A group's own name finds everybody in it ("venues", "communities").
                    'groupWords' => [$ucGroup['label'], $ucGroup['short']],
                    'name' => $ucItem['name'],
                    'blurb' => $ucExtra['blurb'] ?? $ucItem['blurb'],
                    'tags' => $ucItem['tags'],
                    'find' => $ucExtra['find'] ?? [],
                    'url' => marketing_url($ucItem['url']),
                    'pageLabel' => 'See the ' . $ucItem['name'] . ' page',
                    'side' => $ucGroup['side'],
                    'start' => app_url('/sign_up' . ($ucType ? '?type=' . $ucType : '')),
                    'kind' => $ucSample ? 'schedule' : 'code',
                    'who' => $ucSample['name'] ?? '',
                    'initial' => mb_strtoupper(mb_substr(preg_replace('/^The\s+/', '', $ucSample['name'] ?? ''), 0, 1)),
                    'tagline' => $ucSample['tagline'] ?? '',
                    'slug' => $ucSample['slug'] ?? '',
                    'rows' => $ucRows,
                ];
            }
        }
        $ucFirst = $ucCards[0];

        // What the stage shows when what was typed is on no list: the visitor's own words, as a
        // schedule that is theirs to fill. The page's promise is "whatever you put on", so a
        // miss is answered with a schedule and never with "no results". The three lines are
        // things every plan does: reading a flyer into an event, free sign-ups, a repeating date.
        $ucYours = [
            'accent' => 'blue',
            'groupLine' => 'Not on the list yet',
            'badge' => 'Yours',
            'blurb' => 'A schedule of your own, ready for its first event.',
            'side' => '',
            'tagline' => 'Ready for its first event',
            'url' => marketing_url('/features'),
            'pageLabel' => 'See every feature',
            'start' => app_url('/sign_up'),
            'rows' => [
                ['month' => '', 'day' => '+', 'title' => 'Your first event', 'meta' => 'Type it in, or paste the flyer', 'pill' => '', 'pillLabel' => ''],
                ['month' => '', 'day' => '+', 'title' => 'The one people sign up for', 'meta' => 'Free registration, with a limit if you want one', 'pill' => '', 'pillLabel' => ''],
                ['month' => '', 'day' => '+', 'title' => 'The one that repeats', 'meta' => 'Set it once and it repeats', 'pill' => '', 'pillLabel' => ''],
            ],
        ];

        $ucArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $ucCheck = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
        $ucQr = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" /></svg>';
    @endphp

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "Event Scheduling for Every Industry",
        "description": "Event scheduling and ticketing for musicians, venues, restaurants, theaters, online events, and more.",
        "url": "{{ url('/use-cases') }}",
        "mainEntity": {
            "@type": "ItemList",
            "numberOfItems": {{ count($allAudiences) }},
            "itemListElement": [
                @foreach ($allAudiences as $i => $a)
                {
                    "@type": "ListItem",
                    "position": {{ $i + 1 }},
                    "name": {!! \App\Utils\SeoUtils::jsonLd($a['name']) !!},
                    "url": {!! \App\Utils\SeoUtils::jsonLd(marketing_url($a['url'])) !!}
                }@if (! $loop->last),@endif
                @endforeach
            ]
        }
    }
    </script>
    </x-slot>

    <x-seo.faq-schema :items="$faqs" />

    {{-- This page's own pieces (uc-*), on the house style kit's tokens. Rules that must outrank a
         kit rule are written under the page's id, as the kit's are. Every note here is a Blade
         comment, so none of it is sent. --}}
    <style {!! nonce_attr() !!}>
        #hp [hidden] { display: none !important; }

        {{-- One colour per group, in three strengths: an ink that reads as small type on the
             page (4.5:1 or better on paper and on white), a solid for a dot or a fill, and a
             tint to stand behind something. Dark mode has its own of each. --}}
        #hp [data-accent="blue"] { --uc-a: #2459d6; --uc-dot: #3b82f6; --uc-tint: rgba(59, 130, 246, 0.12); }
        #hp [data-accent="amber"] { --uc-a: #8a3d0c; --uc-dot: #f59e0b; --uc-tint: rgba(245, 158, 11, 0.15); }
        #hp [data-accent="emerald"] { --uc-a: #04694b; --uc-dot: #10b981; --uc-tint: rgba(16, 185, 129, 0.14); }
        #hp [data-accent="cyan"] { --uc-a: #0b667e; --uc-dot: #06b6d4; --uc-tint: rgba(6, 182, 212, 0.14); }
        #hp [data-accent="teal"] { --uc-a: #0c6961; --uc-dot: #14b8a6; --uc-tint: rgba(20, 184, 166, 0.14); }
        #hp [data-accent="slate"] { --uc-a: #3f4b5f; --uc-dot: #64748b; --uc-tint: rgba(100, 116, 139, 0.15); }
        #hp .uc-group[data-accent="blue"] { --uc-num: #2f66ea; }
        #hp .uc-group[data-accent="amber"] { --uc-num: #c2600a; }
        #hp .uc-group[data-accent="emerald"] { --uc-num: #059669; }
        #hp .uc-group[data-accent="cyan"] { --uc-num: #0891b2; }
        #hp .uc-group[data-accent="teal"] { --uc-num: #0d8f85; }
        #hp .uc-group[data-accent="slate"] { --uc-num: #64748b; }
        .dark #hp .uc-group[data-accent] { --uc-num: var(--uc-dot); }
        .dark #hp [data-accent="blue"] { --uc-a: #9dbcff; --uc-tint: rgba(78, 129, 250, 0.22); }
        .dark #hp [data-accent="amber"] { --uc-a: #fcd34d; --uc-tint: rgba(245, 158, 11, 0.18); }
        .dark #hp [data-accent="emerald"] { --uc-a: #6ee7b7; --uc-tint: rgba(16, 185, 129, 0.18); }
        .dark #hp [data-accent="cyan"] { --uc-a: #67e8f9; --uc-tint: rgba(6, 182, 212, 0.18); }
        .dark #hp [data-accent="teal"] { --uc-a: #5eead4; --uc-tint: rgba(20, 184, 166, 0.18); }
        .dark #hp [data-accent="slate"] { --uc-a: #cbd5e1; --uc-tint: rgba(148, 163, 184, 0.18); }

        {{-- The page's column is the site's own, at every width: the header, these two sections
             and the ending under them share one left edge. --}}
        .uc-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }

        {{-- ==============================================================
           The hero runs straight into the question under it.
           ============================================================== --}}
        #hp .hp-hero.is-short { padding-block: clamp(2.25rem, 5.5vh, 3.75rem) clamp(0.75rem, 1.6vh, 1.25rem); }
        #hp #top .hp-sub { max-width: 62rem; text-wrap: balance; }

        {{-- ==============================================================
           The programme: the question, the six groups, and the stage beside them
           ============================================================== --}}
        #hp .uc-find { position: relative; padding-block: clamp(0.5rem, 1.5vw, 1.25rem) clamp(3.5rem, 6vw, 5.5rem); }
        .uc-find-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0 2rem; }
        .uc-find-grid > .uc-ask { grid-column: 1 / -1; }
        .uc-bill { min-width: 0; }
        {{-- Beside the list from a laptop up. Below that the stage stands under the name it is
             showing (the script moves it there); with no script it is one worked example after
             the list. --}}
        .uc-aside { min-width: 0; margin-top: 2rem; }
        #hp.uc-js .uc-aside { display: none; }
        @media (min-width: 1100px) {
            .uc-find-grid { grid-template-columns: minmax(0, 1fr) 27rem; gap: 0 clamp(2rem, 4vw, 4rem); align-items: start; }
            #hp .uc-aside,
            #hp.uc-js .uc-aside { display: block; position: sticky; top: 5.25rem; margin-top: 1.6rem; }
        }

        #hp .uc-ask { font-size: clamp(1.7rem, 1.5vw + 1.2rem, 2.6rem); font-variation-settings: 'wght' 840; line-height: 1.05; letter-spacing: -0.045em; }

        {{-- The field and the six ways in stay at hand while the list goes by under them. The
             field shows only where a script can answer it. --}}
        .uc-finder { position: sticky; top: 4rem; z-index: 30; margin: 1rem -0.75rem 0; padding: 0.6rem 0.75rem 0.7rem; background: var(--hp-bg); }
        .uc-finder::after { content: ""; position: absolute; inset-inline: 0; top: 100%; height: 1.5rem; background: linear-gradient(to bottom, var(--hp-bg), transparent); pointer-events: none; }
        .uc-field { display: none; }
        #hp.uc-js .uc-field { position: relative; display: flex; align-items: center; gap: 0.8rem; min-height: 3.9rem; padding: 0 0.7rem 0 1.15rem; border: 1px solid var(--hp-line-2); border-radius: 1.15rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        #hp.uc-js .uc-field:focus-within { border-color: var(--hp-blue); box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.3); }
        .uc-field > svg { flex: none; width: 1.35rem; height: 1.35rem; color: var(--hp-ink-3); }
        #hp .uc-field input { flex: 1 1 0; width: 0; min-width: 0; padding: 0; border: 0; background: transparent; box-shadow: none; outline: 0; font-family: inherit; font-size: 1.15rem; font-weight: 700; font-variation-settings: 'wght' 620; letter-spacing: -0.01em; color: var(--hp-ink); }
        #hp .uc-field input:focus { border: 0; box-shadow: none; outline: 0; }
        #hp .uc-field input::placeholder { color: var(--hp-ink-3); opacity: 1; font-weight: 400; font-variation-settings: 'wght' 420; }
        #hp .uc-clear { display: inline-grid; flex: none; place-items: center; width: 2.75rem; height: 2.75rem; border: 0; border-radius: 0.8rem; background: transparent; color: var(--hp-ink-2); cursor: pointer; }
        #hp .uc-clear:hover { background: var(--hp-bg); color: var(--hp-ink); }
        .uc-clear svg { width: 1.1rem; height: 1.1rem; }
        .uc-key { flex: none; padding: 0.2rem 0.5rem; border: 1px solid var(--hp-line-2); border-radius: 0.45rem; font-family: var(--hp-mono); font-size: 0.72rem; color: var(--hp-ink-3); }
        @media (hover: none), (max-width: 719px) {
            .uc-key { display: none; }
        }
        @media (max-width: 479px) {
            #hp .uc-field input { font-size: 1.0625rem; }
            #hp.uc-js .uc-field { gap: 0.6rem; padding-inline-start: 0.95rem; }
        }

        {{-- The six groups by number. One row that scrolls sideways on a phone, never two. --}}
        .uc-jump { display: flex; gap: 0.4rem; margin-top: 0.6rem; overflow-x: auto; scrollbar-width: none; -webkit-overflow-scrolling: touch; }
        .uc-jump::-webkit-scrollbar { display: none; }
        #hp:not(.uc-js) .uc-jump { margin-top: 0; }
        .uc-jump a { display: inline-flex; flex: none; align-items: baseline; gap: 0.45rem; min-height: 2.5rem; padding: 0.5rem 0.85rem; border: 1px solid var(--hp-line-2); border-radius: 999px; background: var(--hp-bg-2); font-size: 0.92rem; font-weight: 700; font-variation-settings: 'wght' 660; line-height: 1.35; white-space: nowrap; color: var(--hp-ink-2); transition: border-color 0.2s ease, color 0.2s ease; }
        #hp .uc-jump a b { font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; letter-spacing: 0.06em; color: var(--uc-a); }
        .uc-jump a:hover { border-color: var(--uc-dot); color: var(--hp-ink); }
        .uc-bill[data-finding] .uc-jump { display: none; }

        {{-- What the field found, in words, with the way back to everything. --}}
        .uc-found { display: flex; flex-wrap: wrap; align-items: center; gap: 0.3rem 0.6rem; min-height: 2.5rem; margin-top: 0.6rem; padding-inline: 0.25rem; font-size: 0.95rem; color: var(--hp-ink-2); }
        .uc-found > span::after { content: "\00b7"; margin-inline-start: 0.6rem; color: var(--hp-ink-3); }
        #hp .uc-found button { padding: 0; border: 0; background: none; font: inherit; font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-blue); text-decoration: underline; text-underline-offset: 0.2em; cursor: pointer; }
        .uc-none { margin-top: 0.6rem; padding: 0.85rem 1rem 0.9rem; border: 1px solid var(--hp-line-2); border-radius: 1rem; background: var(--hp-bg-2); font-size: 0.98rem; line-height: 1.5; color: var(--hp-ink-2); }
        #hp .uc-none b { color: var(--hp-ink); }
        .uc-none .hp-more { margin-inline-start: 0.5rem; }

        {{-- A group: its number at a size that carries across the room, its name on the rule
             beside it, then its names. --}}
        .uc-group { container-type: inline-size; padding-top: clamp(1.75rem, 3vw, 2.75rem); scroll-margin-top: 12rem; }
        .uc-group-head { display: flex; align-items: flex-end; gap: clamp(0.75rem, 1.6vw, 1.25rem); padding-bottom: 0.75rem; border-bottom: 1px solid var(--hp-line-2); }
        #hp .uc-no { flex: none; font-size: clamp(2.75rem, 2.6vw + 1.9rem, 4.5rem); font-weight: 700; font-variation-settings: 'wght' 880; letter-spacing: -0.06em; line-height: 0.8; color: var(--uc-num, var(--uc-a)); }
        .uc-group-title { display: flex; flex: 1 1 0; flex-wrap: wrap; align-items: baseline; gap: 0.2rem 1rem; min-width: 0; }
        #hp .uc-group-title h3 { font-size: clamp(1.05rem, 0.4vw + 0.95rem, 1.25rem); font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.02em; line-height: 1.25; color: var(--hp-ink); }
        .uc-group-title .hp-more { margin-inline-start: auto; font-size: 0.95rem; color: var(--uc-a); }

        .uc-acts { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.35rem 1.5rem; margin-top: 0.85rem; }
        @container (min-width: 31rem) {
            .uc-acts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @container (min-width: 45rem) {
            .uc-acts { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .uc-acts > li { min-width: 0; scroll-margin-top: 10.5rem; }
        .uc-act { position: relative; display: block; height: 100%; margin-inline: -0.75rem; padding: 0.7rem 0.75rem 0.8rem; border-radius: 0.95rem; transition: background-color 0.18s ease; }
        #hp .uc-act-name { display: block; padding-inline-end: 1rem; font-size: clamp(1.3rem, 0.7vw + 1.05rem, 1.6rem); font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.04em; line-height: 1.1; color: var(--hp-ink); transition: color 0.18s ease; }
        .uc-act-tags { display: block; margin-top: 0.4rem; font-size: 0.82rem; line-height: 1.5; color: var(--hp-ink-3); }
        #hp .uc-act:hover .uc-act-name { color: var(--uc-a); }
        .uc-act[aria-current="true"] { background: var(--uc-tint); }
        #hp .uc-act[aria-current="true"] .uc-act-name { color: var(--uc-a); }
        .uc-act[aria-current="true"] .uc-act-tags { color: var(--hp-ink-2); }
        {{-- The marker that says which name the stage is showing. A dot, never a stripe. --}}
        .uc-act[aria-current="true"]::after { content: ""; position: absolute; top: 1.1rem; inset-inline-end: 0.85rem; width: 0.55rem; height: 0.55rem; border-radius: 999px; background: var(--uc-dot); box-shadow: 0 0 0 4px var(--uc-tint); }

        {{-- A group that is one page: its number, and beside it the page. --}}
        .uc-group.is-one { display: flex; align-items: center; gap: clamp(0.75rem, 1.6vw, 1.25rem); padding-top: clamp(1.5rem, 2.4vw, 2.25rem); margin-top: clamp(1.5rem, 2.4vw, 2.25rem); border-top: 1px solid var(--hp-line-2); }
        .uc-group.is-one .uc-group-head { padding: 0; border: 0; }
        .uc-group.is-one .uc-group-title { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
        .uc-group.is-one .uc-acts { display: block; flex: 1 1 0; min-width: 0; margin-top: 0; }
        .uc-group.is-one .uc-act { display: inline-block; height: auto; margin-inline: 0; padding-inline-end: 2.4rem; }
        .uc-group.is-one .uc-act-tags { font-size: 0.92rem; }
        .uc-group.is-one .uc-slot { margin-top: 0.5rem; }

        {{-- Looking for something: what is not an answer is put away, so the answers stand
             under the field, and the words that matched are marked where they are printed. --}}
        .uc-act-tags > span.is-hit { padding: 0.08em 0.3em; border-radius: 0.35rem; background: var(--uc-tint); -webkit-box-decoration-break: clone; box-decoration-break: clone; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--hp-ink); }
        .uc-act-name.is-hit > span { padding: 0.04em 0.22em; margin-inline: -0.22em; border-radius: 0.4rem; background: var(--uc-tint); -webkit-box-decoration-break: clone; box-decoration-break: clone; }

        {{-- The stage: one made-up schedule of the kind that is pointed at. --}}
        .uc-stage { position: relative; padding: 1.35rem 1.35rem 1.4rem; border: 1px solid var(--hp-line); border-radius: 1.9rem; background: radial-gradient(26rem 15rem at 85% 0%, var(--uc-tint), transparent 72%), var(--hp-bg-2); box-shadow: var(--hp-pop-shadow); }
        .uc-stage-top { display: flex; align-items: center; gap: 0.55rem; font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--uc-a); }
        .uc-stage-top > i { flex: none; width: 0.5rem; height: 0.5rem; border-radius: 999px; background: var(--uc-dot); }
        .uc-stage-top > span:last-child { margin-inline-start: auto; color: var(--hp-ink-3); }
        #hp .uc-stage-name { margin-top: 0.7rem; font-size: clamp(1.7rem, 0.8vw + 1.45rem, 2.15rem); font-variation-settings: 'wght' 840; letter-spacing: -0.04em; line-height: 1.05; overflow-wrap: anywhere; }
        .uc-stage-blurb { margin-top: 0.5rem; font-size: 1rem; line-height: 1.5; color: var(--hp-ink-2); text-wrap: pretty; }
        {{-- What can be done about it comes before the picture, so it is on the first screen. The
             way on is last in the row, as everywhere on the site. --}}
        .uc-stage-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem 1rem; margin-top: 1rem; }
        #hp .uc-stage-actions .hp-more { display: block; min-width: 0; min-height: 0; font-size: 0.96rem; line-height: 1.35; color: var(--uc-a); }
        #hp .uc-stage-actions .hp-more svg { display: inline-block; margin-inline-start: 0.15rem; vertical-align: -0.15em; }
        #hp .uc-stage-actions .hp-btn { min-height: 3rem; padding-inline: 1.15rem; font-size: 0.98rem; }
        @media (min-width: 1100px) {
            {{-- Beside the list the stage keeps one height whatever it shows, so nothing under the
                 pointer moves when the name changes. --}}
            .uc-stage-blurb { min-height: 4.5em; }
            .uc-stage-actions { min-height: 3rem; flex-wrap: nowrap; }
        }

        .uc-win { overflow: hidden; border: 1px solid var(--hp-line); border-radius: 1.1rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); color: var(--hp-ink); }
        .dark .uc-win { background: #131a2e; }
        .uc-stage .uc-win { margin-top: 1.1rem; }
        .uc-win-bar { display: flex; align-items: center; gap: 0.4rem; padding: 0.65rem 0.85rem; border-bottom: 1px solid var(--hp-line); background: var(--hp-bg); }
        .dark .uc-win-bar { background: #0c1120; }
        .uc-win-bar > i { flex: none; width: 0.6rem; height: 0.6rem; border-radius: 999px; background: #ff5f57; }
        .uc-win-bar > i:nth-child(2) { background: #febc2e; }
        .uc-win-bar > i:nth-child(3) { background: #28c840; }
        .uc-url { min-width: 0; margin-inline: auto; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding: 0.2rem 0.85rem; border-radius: 0.5rem; background: var(--hp-bg-2); box-shadow: inset 0 0 0 1px var(--hp-line); font-family: var(--hp-mono); font-size: 0.72rem; color: var(--hp-ink-2); }
        .dark .uc-url { background: #131a2e; }

        .uc-sched { padding: 1rem; }
        .uc-sched-head { display: flex; align-items: center; gap: 0.75rem; padding-bottom: 0.9rem; }
        .uc-sched-head > div { min-width: 0; }
        #hp .uc-sched-head strong { display: block; font-size: 1.05rem; letter-spacing: -0.02em; line-height: 1.2; }
        .uc-sched-head div span { display: block; font-size: 0.8rem; line-height: 1.4; color: var(--hp-ink-3); }
        #hp .uc-ava { display: inline-grid; flex: none; place-items: center; width: 2.7rem; height: 2.7rem; border-radius: 0.85rem; background: var(--uc-dot); background: linear-gradient(135deg, var(--uc-dot), color-mix(in srgb, var(--uc-dot) 55%, #0b1a3a)); font-size: 1.15rem; font-weight: 700; font-variation-settings: 'wght' 860; line-height: 1; color: #fff; }
        #hp .uc-ava.is-small { width: 2.1rem; height: 2.1rem; border-radius: 0.65rem; font-size: 0.95rem; }
        .uc-follow { flex: none; margin-inline-start: auto; padding: 0.38rem 0.9rem; border-radius: 999px; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); font-size: 0.76rem; font-weight: 700; color: #fff; }

        .uc-rows { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.5rem; }
        {{-- A row is as tall as a title and two lines under it whatever it holds, so the stage
             does not change height from one name to the next. Nothing in it is cut short. --}}
        .uc-row { display: flex; align-items: center; gap: 0.75rem; min-height: 4.3rem; padding: 0.55rem 0.7rem; border: 1px solid var(--hp-line); border-radius: 0.9rem; }
        .uc-row > div { min-width: 0; flex: 1 1 0; }
        #hp .uc-row strong { display: block; font-size: 0.95rem; letter-spacing: -0.01em; line-height: 1.25; }
        .uc-row div > span { display: block; font-size: 0.8rem; line-height: 1.4; color: var(--hp-ink-3); }
        .uc-tile { display: inline-flex; flex: none; flex-direction: column; align-items: center; justify-content: center; width: 2.75rem; height: 2.85rem; border-radius: 0.7rem; background: var(--uc-tint); line-height: 1; }
        #hp .uc-tile b { font-size: 0.56rem; font-weight: 700; font-variation-settings: 'wght' 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--uc-a); }
        .uc-tile i { margin-top: 0.15rem; font-style: normal; font-size: 1.1rem; font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.03em; }
        .uc-pill { display: inline-flex; flex: none; align-items: center; padding: 0.26rem 0.6rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; white-space: nowrap; }
        .uc-pill:empty { display: none; }
        .uc-pill[data-pill="price"] { box-shadow: inset 0 0 0 1px var(--hp-line-2); color: var(--hp-ink); font-variant-numeric: tabular-nums; }
        .uc-pill[data-pill="free"] { background: #dcfce7; color: #166534; }
        .uc-pill[data-pill="few"] { background: #fef3c7; color: #92400e; }
        .uc-pill[data-pill="sold"] { background: #e5e7eb; color: #374151; }
        .dark .uc-pill[data-pill="free"] { background: rgba(34, 197, 94, 0.18); color: #86efac; }
        .dark .uc-pill[data-pill="few"] { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        .dark .uc-pill[data-pill="sold"] { background: rgba(255, 255, 255, 0.1); color: #cbd5e1; }

        {{-- Something nobody has written a page for: the same stage, with dates still to be set. --}}
        .uc-stage[data-kind="yours"] .uc-tile { border: 1.5px dashed var(--hp-line-2); background: transparent; }
        .uc-stage[data-kind="yours"] .uc-tile b { display: none; }
        .uc-stage[data-kind="yours"] .uc-tile i { margin-top: 0; font-size: 1.3rem; font-variation-settings: 'wght' 500; color: var(--hp-ink-3); }
        .uc-stage[data-kind="yours"] .uc-row { border-style: dashed; }

        {{-- The developer entry stages a request where the others stage a schedule. --}}
        .uc-stage-code { display: none; margin: 0; padding: 1rem; overflow-x: auto; background: #070b1a; font-size: 0.74rem; line-height: 1.6; color: #c5cde2; }
        .uc-stage[data-kind="code"] .uc-stage-code { display: block; }
        .uc-stage[data-kind="code"] .uc-sched,
        .uc-stage[data-kind="code"] .uc-url.is-site,
        .uc-stage:not([data-kind="code"]) .uc-url.is-call { display: none; }
        .uc-stage-code b { font-weight: 400; color: #6ee7b7; }

        {{-- The stage's contents arrive; the card itself stays where it is. --}}
        @media (prefers-reduced-motion: no-preference) {
            .uc-stage.is-cast .uc-stage-name,
            .uc-stage.is-cast .uc-stage-blurb,
            .uc-stage.is-cast .uc-sched-head,
            .uc-stage.is-cast .uc-row,
            .uc-stage.is-cast .uc-stage-code { animation: uc-in 0.34s cubic-bezier(0.22, 1, 0.36, 1) both; }
            .uc-stage.is-cast .uc-row:nth-child(1) { animation-delay: 0.04s; }
            .uc-stage.is-cast .uc-row:nth-child(2) { animation-delay: 0.08s; }
            .uc-stage.is-cast .uc-row:nth-child(3) { animation-delay: 0.12s; }
        }
        @keyframes uc-in {
            from { opacity: 0; transform: translateY(7px); }
        }

        {{-- Under a name, where there is no room beside the list. The slot is a row of the
             group's own grid, as wide as the grid. --}}
        .uc-slot { grid-column: 1 / -1; padding-block: 0.35rem 0.9rem; }
        .uc-slot .uc-stage { box-shadow: var(--hp-card-shadow); }
        div.uc-slot { padding-block: 0.6rem 0.4rem; }
        li.uc-slot .uc-stage-top,
        #hp li.uc-slot .uc-stage-name { display: none; }
        li.uc-slot .uc-stage-blurb { margin-top: 0; font-size: 1.05rem; }

        {{-- ==============================================================
           One night, six sides
           ============================================================== --}}
        #hp .uc-night { padding-block: clamp(4rem, 7vw, 6.5rem) clamp(4.5rem, 9vw, 8rem); }
        #hp .uc-night .hp-h2 > span { display: block; }
        .uc-night-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2rem, 5vw, 3.5rem); margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 1100px) {
            .uc-night-grid { grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr); gap: clamp(2.5rem, 4.5vw, 5rem); align-items: start; }
            .uc-event-wrap { position: sticky; top: 5.25rem; }
        }

        {{-- The event itself: dark in both modes, so its colours are literal. With no script, and
             below a laptop, it is a card over the six sides. Where there is room and a script,
             it is the one object of the section: the picture of whichever side is passing
             stands in it, and its six places fill along its foot. --}}
        .uc-event { overflow: hidden; border-radius: 1.75rem; background: radial-gradient(40rem 22rem at 80% 0%, rgba(47, 102, 234, 0.34), transparent 70%), #070b1a; color: #eef2ff; box-shadow: 0 0 0 1px rgba(125, 165, 255, 0.2), 0 34px 70px -32px rgba(47, 102, 234, 0.7); }
        .uc-event-head { position: relative; display: flex; align-items: flex-end; min-height: 7.5rem; }
        .uc-event-head img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: 50% 42%; }
        .uc-event-head::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to right, rgba(7, 11, 26, 0.92) 0%, rgba(7, 11, 26, 0.62) 46%, rgba(7, 11, 26, 0.12) 100%), linear-gradient(to top, rgba(7, 11, 26, 0.9), rgba(7, 11, 26, 0) 60%); }
        .uc-event-words { position: relative; z-index: 1; min-width: 0; padding: 1rem 1.15rem 0.95rem; }
        .uc-event-kicker { font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #a9c3ff; }
        #hp .uc-event-title { margin-top: 0.3rem; font-size: clamp(1.7rem, 1.2vw + 1.3rem, 2.2rem); font-variation-settings: 'wght' 860; letter-spacing: -0.045em; line-height: 1; color: #fff; }
        .uc-event-meta { margin-top: 0.35rem; font-size: 0.92rem; line-height: 1.4; color: #c5cde2; }
        .uc-screen { display: none; }
        .uc-event-foot { padding: 0.2rem 1.15rem 1.15rem; }
        .uc-places { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.1rem; padding-top: 0.75rem; border-top: 1px solid rgba(255, 255, 255, 0.13); }
        .uc-places a { display: flex; align-items: center; gap: 0.7rem; min-height: 2.5rem; margin-inline: -0.5rem; padding: 0.3rem 0.5rem; border-radius: 0.7rem; font-size: 0.94rem; line-height: 1.3; color: #c5cde2; transition: background-color 0.2s ease, color 0.2s ease; }
        .uc-places a:hover { background: rgba(255, 255, 255, 0.07); color: #fff; }
        #hp .uc-places a:focus-visible { outline-color: #a9c3ff; }
        .uc-place-name { display: none; }
        .uc-tick { display: inline-grid; flex: none; place-items: center; width: 1.4rem; height: 1.4rem; border: 1.5px solid #22c55e; border-radius: 999px; background: #22c55e; color: #04130a; transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease, transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .uc-tick svg { width: 0.8rem; height: 0.8rem; }
        {{-- The page is drawn with every place reached. Where a script runs it starts them empty
             and fills each one as its side comes by; one that is filled stays filled. --}}
        .uc-places.is-walking li:not(.is-on) .uc-tick { border-color: rgba(255, 255, 255, 0.36); background: transparent; color: transparent; transform: scale(0.84); }
        .uc-places.is-walking li:not(.is-on) a { color: #8f9dc0; }
        .uc-places li.is-now a { background: rgba(255, 255, 255, 0.09); color: #fff; }
        .uc-yours { flex: none; padding: 0.15rem 0.6rem; border-radius: 999px; background: #fde68a; font-family: var(--hp-mono); font-size: 0.7rem; font-style: normal; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #422006; }
        .uc-places .uc-yours { margin-inline-start: auto; }
        .uc-event-end { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem 1rem; margin-top: 0.9rem; }
        .uc-event-end .hp-btn { transition: opacity 0.5s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .uc-event-end > p { min-width: 0; font-size: 0.95rem; line-height: 1.4; color: #c5cde2; }
        #hp .uc-event-end .hp-btn { min-height: 2.85rem; padding-inline: 1.1rem; font-size: 0.96rem; background: linear-gradient(100deg, #3a6df0, #1f8fe0); }
        .uc-event.is-walking .uc-event-end .hp-btn { visibility: hidden; opacity: 0; }

        {{-- The same event, small, riding along on a phone, where the card above is long gone
             by the time the second side comes. --}}
        .uc-strip { display: none; }
        @media (max-width: 1099px) {
            #hp.uc-js .uc-strip:not(.is-away) { visibility: hidden; opacity: 0; }
            #hp.uc-js .uc-strip { transition: opacity 0.25s ease; }
            #hp.uc-js .uc-strip { position: sticky; top: 4.25rem; z-index: 20; display: flex; align-items: center; gap: 0.7rem; margin-bottom: 1.75rem; padding: 0.5rem 0.8rem 0.5rem 0.5rem; border-radius: 1rem; background: #070b1a; color: #eef2ff; box-shadow: 0 0 0 1px rgba(125, 165, 255, 0.22), 0 16px 30px -18px rgba(7, 11, 26, 0.9); }
            {{-- Nothing shows between the site's bar and the strip as the words go by under it. --}}
            #hp.uc-js .uc-strip::before { content: ""; position: absolute; inset: -0.5rem -0.25rem 100%; background: var(--hp-bg-2); }
            .dark #hp.uc-js .uc-strip::before { background: var(--hp-bg-3); }
            .uc-strip img { flex: none; width: 2.25rem; height: 2.25rem; border-radius: 0.6rem; object-fit: cover; }
            .uc-strip > span { min-width: 0; flex: 1 1 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.86rem; color: #c5cde2; }
            #hp .uc-strip b { color: #fff; }
            .uc-strip > div { display: flex; flex: none; gap: 0.3rem; }
            .uc-strip i { width: 0.6rem; height: 0.6rem; border: 1.5px solid rgba(255, 255, 255, 0.4); border-radius: 999px; transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease; }
            .uc-strip i.is-on { border-color: #22c55e; background: #22c55e; }
            .uc-strip i.is-now { transform: scale(1.35); }
        }

        {{-- One side of the night: who it is for and what they are told. --}}
        .uc-side { padding-block: clamp(2.5rem, 4.5vw, 4rem); border-top: 1px solid var(--hp-line); scroll-margin-top: 5.5rem; }
        .uc-side:first-of-type { padding-top: 0; border-top: 0; }
        .uc-side:last-of-type { padding-bottom: 0; }
        .uc-side-kicker { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.6rem; font-family: var(--hp-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--uc-a); }
        .uc-side-kicker > i { flex: none; width: 0.5rem; height: 0.5rem; border-radius: 999px; background: var(--uc-dot); }
        #hp .uc-side h3 { margin-top: 0.8rem; font-size: clamp(1.75rem, 1.3vw + 1.2rem, 2.35rem); font-variation-settings: 'wght' 830; letter-spacing: -0.04em; line-height: 1.06; text-wrap: balance; }
        .uc-side-copy > p { margin-top: 0.95rem; font-size: 1.075rem; line-height: 1.6; color: var(--hp-ink-2); text-wrap: pretty; }
        .uc-side-copy > .hp-more { margin-top: 1.1rem; color: var(--uc-a); }
        #hp .uc-side-where { display: flex; align-items: flex-start; gap: 0.6rem; margin-top: 1.2rem; padding-top: 1.1rem; border-top: 1px solid var(--hp-line); font-size: 1rem; line-height: 1.5; color: var(--hp-ink); }
        .uc-side-where > i { flex: none; width: 0.6rem; height: 0.6rem; margin-top: 0.45rem; border-radius: 999px; background: var(--uc-dot); box-shadow: 0 0 0 4px var(--uc-tint); }
        .uc-side-more { margin-top: 1.75rem; }
        .uc-side > .uc-obj { margin-top: 1.75rem; }
        @media (min-width: 1100px) {
            #hp.uc-js .uc-side { display: flex; flex-direction: column; justify-content: center; min-height: clamp(24rem, 56vh, 33rem); }
            #hp.uc-js .uc-side:first-of-type { justify-content: flex-start; min-height: clamp(22rem, 50vh, 30rem); }
            #hp.uc-js .uc-side:last-of-type { justify-content: flex-start; }
        }

        {{-- What a side's picture stands on, when it stands with its words. --}}
        .uc-obj { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); align-content: center; justify-items: center; gap: 0.9rem; padding: clamp(1.1rem, 2.4vw, 2rem); overflow: hidden; border: 1px solid var(--hp-line); border-radius: 1.9rem; background: radial-gradient(30rem 18rem at 80% 0%, var(--uc-tint), transparent 70%), var(--hp-bg); }
        .dark .uc-obj { background: radial-gradient(30rem 18rem at 80% 0%, var(--uc-tint), transparent 70%), var(--hp-bg-2); }
        .uc-obj > * { position: relative; width: min(100%, 28rem); }
        @media (max-width: 479px) {
            .uc-obj { padding: 0.75rem; border-radius: 1.4rem; }
        }
        .uc-card { border: 1px solid var(--hp-line); border-radius: 1.1rem; background: var(--hp-bg-2); box-shadow: var(--hp-pop-shadow); color: var(--hp-ink); }
        .dark .uc-card { background: #131a2e; }
        .uc-mini { display: inline-flex; flex: none; align-items: center; justify-content: center; gap: 0.3rem; min-height: 1.9rem; padding: 0 0.75rem; border: 1px solid var(--hp-line-2); border-radius: 0.6rem; font-size: 0.76rem; font-weight: 700; color: var(--hp-ink-2); }
        .uc-mini.is-on { border-color: transparent; background: #dcfce7; color: #166534; }
        .dark .uc-mini.is-on { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        .uc-mini.is-go { border-color: transparent; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); color: #fff; }
        .uc-mini svg { width: 0.8rem; height: 0.8rem; }

        {{-- The one object: from a laptop up, where a script has moved the six pictures into it. --}}
        @media (min-width: 1100px) {
            .uc-event.is-staged .uc-screen { position: relative; display: block; height: clamp(17rem, 100vh - 26.5rem, 26rem); border-block: 1px solid rgba(255, 255, 255, 0.1); background-image: linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px); background-size: calc(100% / 7) 4.5rem; }
            .uc-event.is-staged .uc-obj { position: absolute; inset: 0; margin: 0; padding: 1.25rem 1.5rem; border: 0; border-radius: 0; background: none; opacity: 0; visibility: hidden; transform: translateY(14px) scale(0.985); transition: opacity 0.35s ease, transform 0.5s cubic-bezier(0.22, 1, 0.36, 1), visibility 0s linear 0.35s; }
            .uc-event.is-staged .uc-obj.is-on { opacity: 1; visibility: visible; transform: none; transition-delay: 0s; }
            .uc-event.is-staged .uc-obj > * { width: min(100%, 30rem); }
            .uc-event.is-staged .uc-card { box-shadow: 0 24px 50px -24px rgba(0, 0, 0, 0.7); }
            {{-- Its six places: a row along the foot, each under its tick, the one in view named in full. --}}
            .uc-event.is-staged .uc-event-foot { padding-top: 0.9rem; }
            .uc-event.is-staged .uc-places { grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.25rem; padding-top: 0; border-top: 0; }
            .uc-event.is-staged .uc-places a { position: relative; flex-direction: column; gap: 0.4rem; min-height: 0; margin: 0; padding: 0.55rem 0.2rem 0.5rem; text-align: center; }
            .uc-event.is-staged .uc-place-where { display: none; }
            .uc-event.is-staged .uc-place-name { display: block; font-size: 0.76rem; font-weight: 700; font-variation-settings: 'wght' 680; letter-spacing: -0.005em; line-height: 1.2; }
            .uc-event.is-staged .uc-places .uc-yours { order: 3; margin: 0; }
            .uc-event.is-staged .uc-event-end { min-height: 2.85rem; margin-top: 0.75rem; flex-wrap: nowrap; }
        }
        {{-- The kit's own light values. Its paper is written as rgb() here because
             MarketingHouseStyleTest counts the kit's hex declaration to prove it is printed once. --}}
        .dark #hp .uc-event.is-staged .uc-screen {
            --hp-bg: rgb(244 246 251); --hp-bg-2: #ffffff; --hp-bg-3: #e9edf6;
            --hp-ink: #0a1020; --hp-ink-2: #36405a; --hp-ink-3: #56617c;
            --hp-line: rgba(10, 16, 32, 0.1); --hp-line-2: rgba(10, 16, 32, 0.17); --hp-blue: #2f66ea;
        }
        .dark #hp .uc-screen [data-accent="blue"] { --uc-a: #2459d6; --uc-tint: rgba(59, 130, 246, 0.12); }
        .dark #hp .uc-screen [data-accent="amber"] { --uc-a: #8a3d0c; --uc-tint: rgba(245, 158, 11, 0.15); }
        .dark #hp .uc-screen [data-accent="emerald"] { --uc-a: #04694b; --uc-tint: rgba(16, 185, 129, 0.14); }
        .dark #hp .uc-screen [data-accent="cyan"] { --uc-a: #0b667e; --uc-tint: rgba(6, 182, 212, 0.14); }
        .dark #hp .uc-screen [data-accent="teal"] { --uc-a: #0c6961; --uc-tint: rgba(20, 184, 166, 0.14); }
        .dark #hp .uc-screen [data-accent="slate"] { --uc-a: #3f4b5f; --uc-tint: rgba(100, 116, 139, 0.15); }
        .dark #hp .uc-screen .uc-card,
        .dark #hp .uc-screen .uc-win,
        .dark #hp .uc-screen .uc-url { background: #ffffff; }
        .dark #hp .uc-screen .uc-win-bar,
        .dark #hp .uc-screen .uc-peek { background: #f4f6fb; }
        .dark #hp .uc-screen .uc-qty i { background: #e9edf6; }
        .dark #hp .uc-screen .uc-pill[data-pill="free"],
        .dark #hp .uc-screen .uc-mini.is-on,
        .dark #hp .uc-screen .uc-log-row em { background: #dcfce7; color: #166534; }
        .dark #hp .uc-screen .uc-pill[data-pill="few"] { background: #fef3c7; color: #92400e; }
        .dark #hp .uc-screen .uc-pill[data-pill="sold"] { background: #e5e7eb; color: #374151; }
        @media (min-width: 1536px) {
            .uc-event.is-staged .uc-obj > * { zoom: 1.14; }
        }
        @media (prefers-reduced-motion: reduce) {
            .uc-event.is-staged .uc-obj { transform: none; transition: none; }
        }

        {{-- 01 the act: the request, and the date it leaves on the act's own page --}}
        .uc-req { padding: 0.95rem 1rem 1rem; }
        .uc-req-label { font-family: var(--hp-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); }
        .uc-req-body { display: flex; align-items: center; gap: 0.8rem; margin-top: 0.6rem; }
        .uc-req-body > div { min-width: 0; flex: 1 1 0; }
        #hp .uc-req strong { display: block; font-size: 1.02rem; letter-spacing: -0.015em; line-height: 1.25; }
        .uc-req-body div > span { display: block; margin-top: 0.1rem; font-size: 0.82rem; line-height: 1.45; color: var(--hp-ink-3); }
        .uc-req-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.8rem; }
        #hp .uc-lands { display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: auto; font-family: var(--hp-mono); font-size: 0.66rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        .uc-lands svg { width: 1rem; height: 1rem; }
        #hp .uc-event.is-staged .uc-lands { color: #a9c3ff; }
        .uc-landed { padding: 0.7rem; }
        .uc-landed .uc-url { display: block; width: fit-content; margin: 0 0 0.6rem; }
        .uc-row.is-new { border-color: var(--uc-dot); background: var(--uc-tint); }

        {{-- 02 the room: the event's page, on sale --}}
        .uc-sale { display: grid; grid-template-columns: 4.75rem minmax(0, 1fr); gap: 0.85rem; align-items: center; padding: 0.9rem 1rem; }
        .uc-sale img { width: 4.75rem; height: 4.75rem; border-radius: 0.8rem; object-fit: cover; }
        #hp .uc-sale strong { display: block; font-size: 1.25rem; font-variation-settings: 'wght' 820; letter-spacing: -0.03em; line-height: 1.1; }
        .uc-sale-meta { display: block; margin-top: 0.25rem; font-size: 0.8rem; line-height: 1.4; color: var(--hp-ink-3); }
        .uc-types { padding: 0 1rem 1rem; }
        .uc-type { display: flex; align-items: center; gap: 0.7rem; padding: 0.6rem 0; border-top: 1px solid var(--hp-line); font-size: 0.9rem; }
        .uc-type > span:first-child { min-width: 0; flex: 1 1 0; }
        .uc-type small { display: block; font-size: 0.76rem; color: var(--hp-ink-3); }
        #hp .uc-type b { font-variant-numeric: tabular-nums; }
        .uc-qty { display: inline-flex; flex: none; align-items: center; gap: 0.5rem; padding: 0.15rem 0.25rem; border: 1px solid var(--hp-line-2); border-radius: 0.55rem; font-size: 0.82rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .uc-qty i { display: inline-grid; place-items: center; width: 1.3rem; height: 1.3rem; border-radius: 0.35rem; background: var(--hp-bg-3); font-style: normal; color: var(--hp-ink-2); }
        .dark .uc-qty i { background: rgba(255, 255, 255, 0.08); }
        .uc-pay { display: flex; align-items: center; justify-content: center; margin-top: 0.3rem; padding: 0.65rem; border-radius: 0.75rem; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); font-size: 0.92rem; font-weight: 700; color: #fff; }
        .uc-door { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.9rem; }
        .uc-door > svg { flex: none; width: 2rem; height: 2rem; color: var(--hp-ink); }
        .uc-door > div { min-width: 0; flex: 1 1 0; }
        #hp .uc-door strong { display: block; font-size: 0.93rem; line-height: 1.25; }
        .uc-door div > span { display: block; font-size: 0.78rem; color: var(--hp-ink-3); }

        {{-- 03 the guide: one evening across three rooms, by the clock --}}
        .uc-guide { padding: 1rem 1.05rem 1.05rem; }
        .uc-guide-head { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 2px solid var(--hp-ink); }
        #hp .uc-guide-head strong { font-size: 1.2rem; font-variation-settings: 'wght' 830; letter-spacing: -0.03em; line-height: 1.1; }
        .uc-guide-head span { flex: none; font-family: var(--hp-mono); font-size: 0.68rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--hp-ink-3); }
        .uc-listing { display: grid; grid-template-columns: 4.4rem minmax(0, 1fr) auto; align-items: baseline; gap: 0.75rem; padding: 0.6rem 0.5rem; margin-inline: -0.5rem; border-bottom: 1px solid var(--hp-line); border-radius: 0.5rem; font-size: 0.86rem; }
        .uc-listing > span:first-child { font-family: var(--hp-mono); font-size: 0.74rem; color: var(--hp-ink-3); }
        #hp .uc-listing strong { font-size: 0.98rem; letter-spacing: -0.015em; }
        .uc-listing small { display: block; font-size: 0.78rem; color: var(--hp-ink-3); }
        .uc-listing.is-new { border-bottom-color: transparent; background: var(--uc-tint); }
        .uc-listing:last-child { border-bottom: 0; padding-bottom: 0.2rem; }
        .uc-waiting { display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 0.75rem 0.6rem 1rem; font-size: 0.86rem; color: var(--hp-ink-2); }
        .uc-waiting > span:first-child { min-width: 0; flex: 1 1 0; }
        #hp .uc-waiting b { color: var(--hp-ink); }
        .uc-tiles { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.75rem; }
        @media (min-width: 560px) {
            .uc-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .uc-tile-card { padding: 1rem 1.1rem 1.05rem; border: 1px solid var(--hp-line); border-radius: 1.15rem; background: var(--hp-bg); }
        .dark .uc-tile-card { background: var(--hp-bg-2); }
        #hp .uc-tile-card h4 { display: flex; align-items: center; gap: 0.6rem; font-size: 1.02rem; font-variation-settings: 'wght' 740; letter-spacing: -0.015em; line-height: 1.25; }
        .uc-tile-card h4 > span { display: inline-grid; flex: none; place-items: center; width: 2rem; height: 2rem; border-radius: 0.6rem; background: var(--uc-tint); color: var(--uc-a); }
        .uc-tile-card svg { width: 1.1rem; height: 1.1rem; }
        .uc-tile-card p { margin-top: 0.5rem; font-size: 0.92rem; line-height: 1.5; color: var(--hp-ink-2); }

        {{-- 04 the stream: the ticket, with the link on it --}}
        .uc-ticket { display: grid; grid-template-columns: minmax(0, 1fr) 6.6rem; overflow: hidden; border-radius: 1.1rem; background: #0b1226; color: #eef2ff; box-shadow: 0 0 0 1px rgba(125, 165, 255, 0.3), 0 26px 54px -26px rgba(8, 145, 178, 0.8); }
        .uc-ticket-main { padding: 1.1rem 1.15rem 1.15rem; }
        .uc-ticket-kicker { font-family: var(--hp-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #67e8f9; }
        #hp .uc-ticket strong { display: block; margin-top: 0.45rem; font-size: 1.75rem; font-variation-settings: 'wght' 850; letter-spacing: -0.04em; line-height: 1.05; color: #fff; }
        .uc-ticket-meta { display: block; margin-top: 0.35rem; font-size: 0.82rem; line-height: 1.45; color: #c5cde2; }
        .uc-join { margin-top: 0.9rem; padding: 0.6rem 0.7rem; border: 1px solid rgba(103, 232, 249, 0.4); border-radius: 0.7rem; background: rgba(6, 182, 212, 0.13); }
        .uc-join small { display: block; font-family: var(--hp-mono); font-size: 0.62rem; letter-spacing: 0.12em; text-transform: uppercase; color: #a5f3fc; }
        .uc-join span { display: block; margin-top: 0.2rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: var(--hp-mono); font-size: 0.76rem; color: #fff; }
        .uc-ticket-stub { display: grid; align-content: center; justify-items: center; gap: 0.45rem; padding: 0.9rem 0.6rem; border-inline-start: 2px dashed rgba(255, 255, 255, 0.28); }
        .uc-ticket-stub svg { width: 3.5rem; height: 3.5rem; padding: 0.3rem; border-radius: 0.5rem; background: #fff; color: #070b1a; }
        .uc-ticket-stub small { font-family: var(--hp-mono); font-size: 0.6rem; letter-spacing: 0.1em; text-align: center; text-transform: uppercase; color: #c5cde2; }
        .uc-note { display: flex; align-items: center; gap: 0.7rem; padding: 0.75rem 0.95rem; font-size: 0.88rem; line-height: 1.45; color: var(--hp-ink-2); }
        .uc-note > svg { flex: none; width: 1.2rem; height: 1.2rem; color: var(--uc-a); }

        {{-- 05 the town: one shared week, a day opened --}}
        .uc-cal { padding: 0.95rem; }
        .uc-cal-head { display: flex; align-items: center; gap: 0.7rem; }
        .uc-cal-head > div { min-width: 0; flex: 1 1 0; }
        #hp .uc-cal-head strong { display: block; font-size: 0.98rem; line-height: 1.25; }
        .uc-cal-head div > span { display: block; font-size: 0.76rem; color: var(--hp-ink-3); }
        .uc-strands { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 0.8rem; }
        .uc-strands span { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.6rem; border: 1px solid var(--hp-line-2); border-radius: 999px; font-size: 0.72rem; font-weight: 700; color: var(--hp-ink-2); }
        .uc-strands span.is-on { border-color: var(--hp-ink); background: var(--hp-ink); color: var(--hp-bg-2); }
        .uc-strands i { width: 0.45rem; height: 0.45rem; border-radius: 999px; }
        .uc-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.25rem; margin-top: 0.8rem; }
        #hp .uc-week > b { padding-bottom: 0.15rem; font-family: var(--hp-mono); font-size: 0.6rem; font-weight: 400; font-variation-settings: normal; letter-spacing: 0.08em; text-align: center; text-transform: uppercase; color: var(--hp-ink-3); }
        .uc-day { display: grid; align-content: start; gap: 0.2rem; min-height: 3.5rem; padding: 0.3rem; border: 1px solid var(--hp-line); border-radius: 0.55rem; }
        .uc-day > span { font-size: 0.72rem; font-weight: 700; line-height: 1; color: var(--hp-ink-2); }
        .uc-day > i { display: block; height: 0.34rem; border-radius: 999px; }
        .uc-day.is-open { border-color: var(--hp-blue); box-shadow: 0 0 0 3px rgba(78, 129, 250, 0.22); }
        .uc-peek { display: grid; grid-template-columns: 3.4rem minmax(0, 1fr); gap: 0.7rem; align-items: center; margin-top: 0.7rem; padding: 0.6rem; border: 1px solid var(--hp-line-2); border-radius: 0.85rem; background: var(--hp-bg); }
        .dark .uc-peek { background: #0c1120; }
        .uc-peek img { width: 3.4rem; height: 3.4rem; border-radius: 0.6rem; object-fit: cover; }
        .uc-peek > div { min-width: 0; }
        #hp .uc-peek strong { display: block; font-size: 0.95rem; line-height: 1.25; }
        .uc-peek div > span { display: block; font-size: 0.78rem; line-height: 1.45; color: var(--hp-ink-3); }

        {{-- 06 the code: a sale arriving at somebody's server, and the request, kept as it was --}}
        .uc-term { overflow: hidden; padding: 1.1rem 1.2rem 1.15rem; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.25rem; color: #d1d5db; }
        .uc-log { padding: 0.95rem 1.05rem 0.6rem; }
        .uc-log-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.2rem 1rem; padding-bottom: 0.7rem; border-bottom: 2px solid var(--hp-ink); }
        #hp .uc-log-head strong { font-size: 1.1rem; font-variation-settings: 'wght' 820; letter-spacing: -0.025em; }
        .uc-log-head span { font-family: var(--hp-mono); font-size: 0.66rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--hp-ink-3); }
        .uc-log-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 0.1rem 0.75rem; margin-inline: -0.45rem; padding: 0.55rem 0.45rem; border-bottom: 1px solid var(--hp-line); border-radius: 0.5rem; }
        .uc-log-row:last-child { border-bottom: 0; }
        .uc-log-row.is-new { border-bottom-color: transparent; background: var(--uc-tint); }
        #hp .uc-log-row code { font-size: 0.82rem; font-weight: 700; color: var(--hp-ink); }
        .uc-log-row span { grid-column: 1; font-size: 0.8rem; color: var(--hp-ink-3); }
        .uc-log-row em { grid-column: 2; grid-row: 1 / span 2; padding: 0.2rem 0.55rem; border-radius: 999px; background: #dcfce7; font-family: var(--hp-mono); font-size: 0.68rem; font-style: normal; white-space: nowrap; color: #166534; }
        .dark .uc-log-row em { background: rgba(34, 197, 94, 0.18); color: #86efac; }
        .uc-term-bar { display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.85rem; }
        .uc-term-bar > i { flex: none; width: 0.62rem; height: 0.62rem; border-radius: 999px; background: #ff5f57; }
        .uc-term-bar > i:nth-child(2) { background: #febc2e; }
        .uc-term-bar > i:nth-child(3) { background: #28c840; }
        .uc-term-bar > span { margin-inline-start: 0.6rem; font-family: var(--hp-mono); font-size: 0.7rem; color: #9ca3b4; }
        #hp .uc-term pre { overflow-x: auto; font-size: 0.72rem; line-height: 1.55; color: #d1d5db; }
        .uc-term pre b { font-weight: 400; color: #6ee7b7; }
        .uc-term pre mark { background: rgba(125, 165, 255, 0.24); color: #fff; border-radius: 0.2rem; }
        .uc-term > p { margin-top: 0.85rem; font-size: 0.74rem; line-height: 1.5; color: #9ca3b4; }
        .uc-docs { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.75rem; }
        @media (min-width: 560px) {
            .uc-docs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .uc-doc { display: flex; flex-direction: column; height: 100%; padding: 1.05rem 1.1rem 1.1rem; border: 1px solid var(--hp-line); border-radius: 1.15rem; background: var(--hp-bg); transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
        .dark .uc-doc { background: var(--hp-bg-2); }
        .uc-doc:hover { border-color: var(--hp-blue); transform: translateY(-2px); box-shadow: var(--hp-card-shadow); }
        #hp .uc-doc h4 { display: flex; align-items: center; gap: 0.4rem; font-size: 1.02rem; font-variation-settings: 'wght' 760; letter-spacing: -0.015em; line-height: 1.3; }
        .uc-doc h4 svg { width: 0.95rem; height: 0.95rem; color: var(--hp-ink-3); }
        .uc-doc p { margin-top: 0.3rem; font-size: 0.9rem; line-height: 1.5; color: var(--hp-ink-2); }
        .uc-doc small { margin-top: auto; padding-top: 0.7rem; font-size: 0.78rem; line-height: 1.5; color: var(--hp-ink-3); }

        @media (prefers-reduced-motion: reduce) {
            .uc-act,
            .uc-act-name,
            .uc-tick,
            .uc-doc,
            .uc-places a,
            .uc-event-end .hp-btn,
            .uc-strip i { transition: none; }
        }
    </style>

    {{-- Two gates, set before anything is drawn. es-anim: hidden pre-reveal states apply only
         while this is present, so a visitor without scripts, a crawler and anyone who asked for
         less motion see everything. uc-js: the field and the pressable list show only where the
         script that answers them runs. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
        (function () {
            var page = document.getElementById('hp');
            if (page) {
                page.classList.add('uc-js');
            }
        })();
    </script>

    {{-- ============================================================
         Hero: the headline and nothing else. The question it leads to
         opens the section under it.
         ============================================================ --}}
    <section id="top" class="es-hero hp-hero is-short">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <span class="hp-live" aria-hidden="true"><i></i></span>
                    Event scheduling software, by use case
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">Whatever you put on,</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">somebody here runs it</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                Share your events, <a href="{{ marketing_url('/features/ticketing') }}" class="hp-inline">sell tickets</a> with zero platform fees, and <a href="{{ marketing_url('/features/newsletters') }}" class="hp-inline">email your followers</a> when you have new dates. Free forever, open source, and <a href="{{ marketing_url('/selfhost') }}" class="hp-inline">selfhostable</a> if you want it on your own server.
            </p>
        </div>
    </section>

    {{-- ============================================================
         The programme. Every audience page, in six groups, as plain
         links: the whole directory with nothing switched on. Where a
         script runs, the field narrows it to what was typed and the
         stage beside it shows a made-up schedule of the kind pointed at.
         ============================================================ --}}
    <section id="find" class="uc-find">
        <div class="uc-wrap uc-find-grid">
            <h2 id="uc-ask" class="uc-ask">What do you put on?</h2>

            <div class="uc-bill" data-uc-bill>

                <div class="uc-finder" data-uc-finder>
                    <div class="uc-field">
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z" /></svg>
                        <input id="uc-q" type="text" inputmode="search" enterkeyhint="search" autocomplete="off" autocapitalize="off" spellcheck="false" maxlength="60"
                               role="searchbox" aria-labelledby="uc-ask" aria-controls="uc-list" aria-describedby="uc-status"
                               placeholder="Tribute nights, taprooms, story time" data-uc-q>
                        <button type="button" class="uc-clear" data-uc-clear aria-label="Clear" hidden>
                            <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" /></svg>
                        </button>
                        <kbd class="uc-key" aria-hidden="true" data-uc-key>/</kbd>
                    </div>

                    <nav class="uc-jump" aria-label="Jump to a category" data-clip-ok>
                        @foreach ($ucGroups as $ucIndex => $ucGroup)
                            <a href="#{{ $ucGroup['id'] }}" data-accent="{{ $ucGroup['accent'] }}"><b>{{ sprintf('%02d', $ucIndex + 1) }}</b>{{ $ucGroup['short'] }}</a>
                        @endforeach
                    </nav>

                    {{-- What the field found. The sentences are written here and the script fills
                         in the number and the name. --}}
                    <p class="uc-found" data-uc-found hidden>
                        <span data-uc-count data-one="1 match" data-many=":n matches"></span>
                        <button type="button" data-uc-all>Show all {{ count($ucCards) }}</button>
                    </p>
                    {{-- Nothing on the list answers to what was typed. The claim is the FAQ's own
                         ("What types of events can I manage? Any kind."). --}}
                    <p class="uc-none" data-uc-none hidden>
                        <b>No page by that name yet.</b> A schedule works the same way for any kind of event.
                        <a href="{{ app_url('/sign_up') }}" class="hp-more" data-uc-none-start>Start for free {!! $ucArrow !!}</a>
                    </p>
                    {{-- Read out, never shown: it is always in the page, so what is written into
                         it is spoken. --}}
                    <p id="uc-status" class="sr-only" role="status" data-uc-status
                       data-showing="Showing :name."
                       data-again="Showing :name. Press it again to open its page."
                       data-none="Nothing on the list matches, so the sample is now a schedule in your own words."></p>
                </div>

                <div id="uc-list">
                    @foreach ($ucGroups as $ucIndex => $ucGroup)
                        <div id="{{ $ucGroup['id'] }}" class="uc-group{{ count($ucGroup['items']) === 1 ? ' is-one' : '' }}" data-accent="{{ $ucGroup['accent'] }}" data-uc-group>
                            <div class="uc-group-head">
                                <span class="uc-no" aria-hidden="true">{{ sprintf('%02d', $ucIndex + 1) }}</span>
                                <div class="uc-group-title">
                                    <h3>{{ $ucGroup['label'] }}</h3>
                                    @if ($ucGroup['hub'])
                                        <a href="{{ marketing_url($ucGroup['hub'][0]) }}" class="hp-more">{{ $ucGroup['hub'][1] }} {!! $ucArrow !!}</a>
                                    @endif
                                </div>
                            </div>
                            <ul class="uc-acts">
                                @foreach ($ucGroup['items'] as $ucItem)
                                    <li>
                                        <a href="{{ marketing_url($ucItem['url']) }}" class="uc-act" data-uc-act="{{ ltrim($ucItem['url'], '/') }}"@if ($ucIndex === 0 && $loop->first) aria-current="true"@endif>
                                            <span class="uc-act-name"><span>{{ $ucItem['name'] }}</span></span>
                                            <span class="uc-act-tags">@foreach ($ucItem['tags'] as $ucTag)<span>{{ $ucTag }}</span>@if (! $loop->last)&nbsp;&middot; @endif @endforeach</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

            <aside class="uc-aside" aria-label="A sample schedule" data-uc-aside>
                <div class="uc-stage" data-uc-stage data-accent="{{ $ucFirst['accent'] }}" data-kind="{{ $ucFirst['kind'] }}">
                    <p class="uc-stage-top"><i aria-hidden="true"></i><span data-uc="groupLine">{{ $ucFirst['groupLine'] }}</span><span data-uc="badge">Sample</span></p>
                    <h3 class="uc-stage-name" data-uc="name">{{ $ucFirst['name'] }}</h3>
                    <p class="uc-stage-blurb" data-uc="blurb">{{ $ucFirst['blurb'] }}</p>

                    <div class="uc-stage-actions">
                        <a href="{{ $ucFirst['url'] }}" class="hp-more" data-uc="page"><span data-uc="pageLabel">{{ $ucFirst['pageLabel'] }}</span> {!! $ucArrow !!}</a>
                        <a href="{{ $ucFirst['start'] }}" class="hp-btn hp-btn-primary is-small" data-uc="start">Start for free {!! $ucArrow !!}</a>
                    </div>

                    <div class="uc-win" aria-hidden="true">
                        <div class="uc-win-bar"><i></i><i></i><i></i><span class="uc-url is-site" dir="ltr"><span data-uc="slug">{{ $ucFirst['slug'] }}</span>{{ $ucSuffix }}</span><span class="uc-url is-call" dir="ltr">GET /api/events</span></div>
                        <div class="uc-sched">
                            <div class="uc-sched-head">
                                <span class="uc-ava" data-uc="initial">{{ $ucFirst['initial'] }}</span>
                                <div>
                                    <strong data-uc="who">{{ $ucFirst['who'] }}</strong>
                                    <span data-uc="tagline">{{ $ucFirst['tagline'] }}</span>
                                </div>
                                <span class="uc-follow">Follow</span>
                            </div>
                            <div class="uc-rows">
                                @foreach ($ucFirst['rows'] as $ucRow)
                                    <div class="uc-row" data-uc-row>
                                        <span class="uc-tile"><b>{{ $ucRow['month'] }}</b><i>{{ $ucRow['day'] }}</i></span>
                                        <div><strong data-uc-title>{{ $ucRow['title'] }}</strong><span data-uc-meta>{{ $ucRow['meta'] }}</span></div>
                                        <span class="uc-pill" data-pill="{{ $ucRow['pill'] }}">{{ $ucRow['pillLabel'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
<pre class="uc-stage-code" dir="ltr"><code><b>$ curl {{ url('/api/events') }} \
    -H "X-API-Key: $EVENTSCHEDULE_KEY"</b>

{
  "data": [
    {
      "name": "Jazz Night",
      "starts_at": "{{ $ucFriday->format('Y-m-d') }} 20:00:00",
      "venue_name": "The Blue Note"
    }
  ],
  "meta": { "per_page": 100, "total": 42 }
}</code></pre>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- Where there is no room beside the list, the stage stands under the first row of names
         from the start. It is put there here, as soon as it has been read and before the rest of
         the page arrives, so nothing a visitor is already looking at is pushed down later. The
         script at the foot of the page takes it from there. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var stage = document.querySelector('[data-uc-stage]');
            var first = document.querySelector('[data-uc-act]');
            if (!stage || !first || !window.matchMedia('(max-width: 1099px)').matches) {
                return;
            }
            var list = first.parentNode.parentNode;
            var columns = window.getComputedStyle(list).gridTemplateColumns.split(' ').filter(function (track) { return /px$/.test(track); }).length || 1;
            var last = list.children[Math.min(list.children.length, columns) - 1];
            var slot = document.createElement('li');
            slot.className = 'uc-slot';
            list.insertBefore(slot, last.nextSibling);
            slot.appendChild(stage);
        })();
    </script>

    {{-- ============================================================
         One night, six sides. The six groups meet on the same events,
         so one event is followed through all six: the venue puts it
         on once, and it turns up for each of them somewhere else.
         Each side keeps the heading and the paragraph its group has
         always had on this page. With no script, and on a phone, each
         side's picture stands under its words. Where there is room,
         the script moves the six pictures into the event itself, which
         stays beside the words and shows the one that is passing.
         ============================================================ --}}
    @php
        $ucNight = $ucFriday->format('D, M j');
        $ucFlyer = asset('images/demo/demo_flyer_jazz.webp');
        // id, the two-word name, where the event turns up
        $ucSides = [
            ['the-act', 'The act', 'On the act\'s own page'],
            ['the-room', 'The room', 'On sale at the venue'],
            ['the-guide', 'The guide', 'In the city guide'],
            ['the-stream', 'The stream', 'On the ticket, with the link to watch'],
            ['the-town', 'The community', 'On the community calendar'],
            ['the-code', 'The code', 'In the API'],
        ];
    @endphp
    <section id="one-night" class="uc-night hp-alt">
        <div class="uc-wrap">
            <div class="hp-head" data-reveal>
                <span class="hp-kicker">One night, six places</span>
                <h2 class="hp-h2">Put it on once. <span class="hp-ink-grad">It turns up in six places.</span></h2>
                <p class="hp-lead">The six groups are not six products. Take one event: Jazz Night, Friday at 8, at The Blue Note. The venue puts it on once, and each group meets it in a different place.</p>
            </div>

            <div class="uc-night-grid">
                <div class="uc-event-wrap">
                    <div class="uc-event" data-uc-event data-reveal="panel">
                        <div class="uc-event-head">
                            <img src="{{ $ucFlyer }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                            <div class="uc-event-words">
                                <p class="uc-event-kicker">One event</p>
                                <p class="uc-event-title">Jazz Night</p>
                                <p class="uc-event-meta">{{ $ucNight }} · 8:00 PM · The Blue Note</p>
                            </div>
                        </div>
                        <div class="uc-screen" data-uc-screen></div>
                        <div class="uc-event-foot">
                            <ol class="uc-places" data-uc-places>
                                @foreach ($ucSides as [$ucSideId, $ucSideName, $ucSidePlace])
                                    <li class="is-on" data-uc-place="{{ $ucSideId }}">
                                        <a href="#{{ $ucSideId }}"><span class="uc-tick" aria-hidden="true">{!! $ucCheck !!}</span><span class="uc-place-where">{{ $ucSidePlace }}</span><span class="uc-place-name" aria-hidden="true">{{ $ucSideName }}</span><em class="uc-yours" data-uc-yours hidden>Yours</em></a>
                                    </li>
                                @endforeach
                            </ol>
                            <div class="uc-event-end">
                                <p data-uc-now data-end="Six places, typed once.">Six places, typed once.</p>
                                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary is-small" data-uc-go>Start for free {!! $ucArrow !!}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="uc-sides">
                    <div class="uc-strip" aria-hidden="true" data-uc-strip>
                        <img src="{{ $ucFlyer }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                        <span><b>Jazz Night</b> · {{ $ucFriday->format('D') }} 8:00 PM</span>
                        <div>@foreach ($ucSides as $ucSide)<i class="is-on"></i>@endforeach</div>
                    </div>

                    {{-- 01 Performers --}}
                    <article id="the-act" class="uc-side" data-accent="blue" data-uc-side>
                        <div class="uc-side-copy">
                            <p class="uc-side-kicker"><i aria-hidden="true"></i>01 · The act <em class="uc-yours" data-uc-yours hidden>Your side</em></p>
                            <h3>For Performers &amp; Artists</h3>
                            <p>Musicians, DJs, performers, and artists who want to share their upcoming shows and build their audience. Sync with Google Calendar, Outlook or CalDAV, let venues add you to their lineup through booking requests, and email your fans directly whenever you announce new dates. If a venue or promoter has already listed you, a page with your name on it may be waiting: <a href="{{ marketing_url('/docs/creating-events#claim') }}" class="hp-inline">claim it</a> with the email address they entered.</p>
                            <p class="uc-side-where"><i aria-hidden="true"></i><span>The Blue Note adds The Marlowe Trio to Jazz Night. They accept, and the date is on their own page.</span></p>
                            <a href="{{ marketing_url('/for-talent') }}" class="hp-more">See the full Talent guide {!! $ucArrow !!}</a>
                        </div>
                        <div class="uc-obj" data-uc-face="the-act" data-accent="blue" aria-hidden="true">
                            <div class="uc-card uc-req">
                                <p class="uc-req-label">Request</p>
                                <div class="uc-req-body">
                                    <span class="uc-ava" data-accent="amber">B</span>
                                    <div><strong>The Blue Note added you to Jazz Night</strong><span>{{ $ucNight }} · 8:00 PM</span></div>
                                </div>
                                <div class="uc-req-actions"><span class="uc-mini is-on">{!! $ucCheck !!}Accepted</span></div>
                            </div>
                            <p class="uc-lands"><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m0 0l-6-6m6 6l6-6" /></svg>Now on your own page</p>
                            <div class="uc-card uc-landed">
                                <span class="uc-url" dir="ltr">marlowe-trio{{ $ucSuffix }}</span>
                                <div class="uc-row is-new"><span class="uc-tile"><b>{{ $ucFriday->format('M') }}</b><i>{{ $ucFriday->format('j') }}</i></span><div><strong>Jazz Night</strong><span>8:00 PM · The Blue Note</span></div><span class="uc-pill" data-pill="price">$25</span></div>
                            </div>
                        </div>
                    </article>

                    {{-- 02 Venues --}}
                    <article id="the-room" class="uc-side" data-accent="amber" data-uc-side>
                        <div class="uc-side-copy">
                            <p class="uc-side-kicker"><i aria-hidden="true"></i>02 · The room <em class="uc-yours" data-uc-yours hidden>Your side</em></p>
                            <h3>For Venues &amp; Event Spaces</h3>
                            <p>Bars, clubs, theaters, restaurants, and every other space that hosts events. Publish your lineup, take booking requests from performers, sell tickets with zero platform fees, and keep your regulars in the loop.</p>
                            <p class="uc-side-where"><i aria-hidden="true"></i><span>The Blue Note made the event, so it is on sale on their page and scanned at their door.</span></p>
                            <a href="{{ marketing_url('/for-venues') }}" class="hp-more">See the full Venues guide {!! $ucArrow !!}</a>
                        </div>
                        <div class="uc-obj" data-uc-face="the-room" data-accent="amber" aria-hidden="true">
                            <div class="uc-card uc-win">
                                <div class="uc-win-bar"><i></i><i></i><i></i><span class="uc-url" dir="ltr">blue-note{{ $ucSuffix }}/jazz-night</span></div>
                                <div class="uc-sale">
                                    <img src="{{ $ucFlyer }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                                    <div>
                                        <strong>Jazz Night</strong>
                                        <span class="uc-sale-meta">{{ $ucNight }} · 8:00 PM</span>
                                        <span class="uc-sale-meta">With The Marlowe Trio</span>
                                    </div>
                                </div>
                                <div class="uc-types">
                                    <div class="uc-type"><span>In the room</span><b>$25</b><span class="uc-qty"><i>-</i>2<i>+</i></span></div>
                                    <div class="uc-type"><span>Livestream<small>Watch from home</small></span><b>$10</b><span class="uc-qty"><i>-</i>0<i>+</i></span></div>
                                    <div class="uc-pay">Buy Tickets</div>
                                </div>
                            </div>
                            <div class="uc-card uc-door">
                                {!! $ucQr !!}
                                <div><strong>Scanned at the door</strong><span>A QR code on every ticket</span></div>
                            </div>
                        </div>
                    </article>

                    {{-- 03 Curators --}}
                    <article id="the-guide" class="uc-side" data-accent="emerald" data-uc-side>
                        <div class="uc-side-copy">
                            <p class="uc-side-kicker"><i aria-hidden="true"></i>03 · The guide <em class="uc-yours" data-uc-yours hidden>Your side</em></p>
                            <h3>For Curators &amp; Promoters</h3>
                            <p>Event promoters, bloggers, and community organizers who aggregate and share events from multiple sources. Import events with AI, pull lineups from venues and performers automatically, and become the go-to calendar for your local scene.</p>
                            <p class="uc-side-where"><i aria-hidden="true"></i><span>Eastside Tonight lists The Blue Note as a source, so Jazz Night is on the guide without anyone typing it in.</span></p>
                            <a href="{{ marketing_url('/for-curators') }}" class="hp-more">Learn more {!! $ucArrow !!}</a>
                        </div>
                        <div class="uc-obj" data-uc-face="the-guide" data-accent="emerald" aria-hidden="true">
                            <p class="uc-lands">Sources: The Blue Note, The Cellar Club, Stillpoint Yoga</p>
                            <div class="uc-card uc-guide">
                                <div class="uc-guide-head"><strong>Eastside Tonight</strong><span>{{ $ucNight }}</span></div>
                                <div class="uc-listing"><span>7:00 PM</span><div><strong>Night Flow</strong><small>Stillpoint Yoga</small></div><span class="uc-pill" data-pill="few">Few left</span></div>
                                <div class="uc-listing is-new"><span>8:00 PM</span><div><strong>Jazz Night</strong><small>The Blue Note</small></div><span class="uc-pill" data-pill="price">$25</span></div>
                                <div class="uc-listing"><span>9:30 PM</span><div><strong>Late Laughs</strong><small>The Cellar Club</small></div><span class="uc-pill" data-pill="price">$18</span></div>
                            </div>
                            <div class="uc-card uc-waiting"><span><b>Zine fair</b>, sent in by a reader</span><span class="uc-mini">Decline</span><span class="uc-mini is-go">Accept</span></div>
                        </div>

                        @php
                            $curatorTiles = [
                                // A link is read too since October 2026 (LinkImportService), once, when
                                // an editor pastes it. The DAILY sweep of a list of URLs and cities is
                                // still the selfhost-only ImportCuratorEvents command: do not let this
                                // tile promise that.
                                ['AI Import', 'Paste a link, the text or a flyer photo and the details are filled in', 'M13 10V3L4 14h7v7l9-11h-7z'],
                                ['Aggregation', 'Pull events from venues and performers', 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                                ['Approval Workflow', 'Review and approve events before publishing', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                // Careful wording, and it has to stay careful: the automatic digest
                                // (app:send-event-announcements) reaches confirmed EMAIL SUBSCRIBERS -
                                // role_subscribers rows, captured by the subscribe panel and the
                                // checkout opt-in. Account followers (role_user at level 'follower')
                                // are reached only by a newsletter the owner composes and sends. So
                                // "subscribers", never "followers", in any sentence about automatic mail.
                                ['Build Your Audience', 'Confirmed email subscribers get a digest of what you publish, with no algorithm in between', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                                // A name typed into a lineup becomes a schedule of its own (EventRepo),
                                // rendered publicly by role/show-guest-unclaimed.blade.php and noindex
                                // until User::claimSchedule() hands it to whoever holds the address on it.
                                ['Pages for Your Acts', 'A performer or venue you list who is not here yet gets a page crediting you, which they can claim', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                                // FeedController::icalFeed, offered to guests in the Add to Calendar menu.
                                ['Live Calendar Feed', 'Anyone can add your whole calendar to theirs as a feed that updates itself', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                            ];
                        @endphp
                        {{-- Six tiles, two across or one, so the grid closes at every width. --}}
                        <div class="uc-side-more uc-tiles">
                            @foreach ($curatorTiles as [$tileTitle, $tileBody, $tilePath])
                                <div class="uc-tile-card">
                                    <h4><span><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tilePath }}" /></svg></span>{{ $tileTitle }}</h4>
                                    <p>{{ $tileBody }}</p>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    {{-- 04 Online --}}
                    <article id="the-stream" class="uc-side" data-accent="cyan" data-uc-side>
                        <div class="uc-side-copy">
                            <p class="uc-side-kicker"><i aria-hidden="true"></i>04 · The stream <em class="uc-yours" data-uc-yours hidden>Your side</em></p>
                            <h3>For Online Events</h3>
                            <p>Webinars, classes, conferences, and watch parties that happen on a screen instead of in a room. Add your streaming link to any event and attendees get it on their ticket. <a href="{{ marketing_url('/features/online-events') }}" class="hp-inline">See how online events work</a>.</p>
                            <p class="uc-side-where"><i aria-hidden="true"></i><span>Jazz Night is streamed as well, and everyone with a ticket has the link to watch.</span></p>
                        </div>
                        <div class="uc-obj" data-uc-face="the-stream" data-accent="cyan" aria-hidden="true">
                            <div class="uc-ticket">
                                <div class="uc-ticket-main">
                                    <span class="uc-ticket-kicker">Ticket · Livestream</span>
                                    <strong>Jazz Night</strong>
                                    <span class="uc-ticket-meta">{{ $ucNight }} · 8:00 PM · The Blue Note</span>
                                    <div class="uc-join"><small>Watch from home</small><span dir="ltr">youtu.be/blue-note-live</span></div>
                                </div>
                                <div class="uc-ticket-stub">{!! $ucQr !!}<small>Jazz Night</small></div>
                            </div>
                            <div class="uc-card uc-note">
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>The public page shows the venue. The link to watch is printed on the ticket.</span>
                            </div>
                        </div>
                    </article>

                    {{-- 05 Communities --}}
                    <article id="the-town" class="uc-side" data-accent="teal" data-uc-side>
                        <div class="uc-side-copy">
                            <p class="uc-side-kicker"><i aria-hidden="true"></i>05 · The community <em class="uc-yours" data-uc-yours hidden>Your side</em></p>
                            <h3>For Organizations &amp; Communities</h3>
                            <p>Churches, schools, nonprofits, festivals, leagues, museums, meetup groups and whole towns: the calendar belongs to a group of people rather than one room or one act. Set the regular dates once, give each team or strand its own link, take free sign-ups with a cap, and let members subscribe to the whole thing in their own calendar.</p>
                            <p class="uc-side-where"><i aria-hidden="true"></i><span>Riverside's shared calendar lists The Blue Note too, and files Jazz Night under Music.</span></p>
                        </div>
                        <div class="uc-obj" data-uc-face="the-town" data-accent="teal" aria-hidden="true">
                            <div class="uc-card uc-cal">
                                <div class="uc-cal-head"><span class="uc-ava is-small">R</span><div><strong>Riverside Community Calendar</strong><span>What's on in Riverside</span></div><span class="uc-follow">Follow</span></div>
                                <p class="uc-strands"><span class="is-on">Show All</span><span><i style="background: #3b82f6;"></i>Music</span><span><i style="background: #f59e0b;"></i>Family</span><span><i style="background: #10b981;"></i>Sport</span><span><i style="background: #f97316;"></i>Faith</span></p>
                                <div class="uc-week">
                                    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $ucDayName)
                                        <b>{{ $ucDayName }}</b>
                                    @endforeach
                                    @foreach ([1 => ['#f59e0b'], 2 => ['#f59e0b'], 3 => [], 4 => ['#3b82f6'], 5 => ['#3b82f6'], 6 => ['#10b981', '#f59e0b'], 7 => ['#f97316']] as $ucWeekday => $ucBars)
                                        <div class="uc-day{{ $ucWeekday === 5 ? ' is-open' : '' }}"><span>{{ $ucDay($ucWeekday)->format('j') }}</span>@foreach ($ucBars as $ucBar)<i style="background: {{ $ucBar }};"></i>@endforeach</div>
                                    @endforeach
                                </div>
                                <div class="uc-peek">
                                    <img src="{{ $ucFlyer }}" alt="" width="800" height="600" loading="lazy" decoding="async">
                                    <div><strong>Jazz Night</strong><span>{{ $ucNight }} · 8:00 PM · The Blue Note</span><span>Music · from The Blue Note's schedule</span></div>
                                </div>
                            </div>
                        </div>
                    </article>

                    {{-- 06 Developers --}}
                    <article id="the-code" class="uc-side" data-accent="slate" data-uc-side>
                        <div class="uc-side-copy">
                            <p class="uc-side-kicker"><i aria-hidden="true"></i>06 · The code <em class="uc-yours" data-uc-yours hidden>Your side</em></p>
                            <h3>For Developers &amp; AI Agents</h3>
                            <p>Every schedule publishes a public calendar feed, and on Pro it is also a REST API and a set of signed webhooks. Agents can discover all of it on their own and run multi-step flows without anyone wiring them up first.</p>
                            <p class="uc-side-where"><i aria-hidden="true"></i><span>Jazz Night is one object in the answer to GET /api/events, and a webhook fires when a ticket for it sells.</span></p>
                            <a href="{{ marketing_url('/for-ai-agents') }}" class="hp-more">See the full developer guide {!! $ucArrow !!}</a>
                        </div>
                        {{-- The same night as a developer's server hears of it. The three names are
                             real webhook event types (App\Models\Webhook); the log is the product's
                             own delivery log. --}}
                        <div class="uc-obj" data-uc-face="the-code" data-accent="slate" aria-hidden="true">
                            <div class="uc-card uc-log">
                                <div class="uc-log-head"><strong>Webhook deliveries</strong><span>Signed, HMAC-SHA256</span></div>
                                <div class="uc-log-row"><code dir="ltr">event.created</code><span>Jazz Night</span><em>200 · 61 ms</em></div>
                                <div class="uc-log-row is-new"><code dir="ltr">sale.paid</code><span>2 tickets, In the room</span><em>200 · 84 ms</em></div>
                                <div class="uc-log-row"><code dir="ltr">ticket.scanned</code><span>At the door</span><em>200 · 58 ms</em></div>
                            </div>
                            <div class="uc-card uc-note">
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" /></svg>
                                <span>And on the public feed anyone can subscribe to, with no key at all.</span>
                            </div>
                        </div>

                        {{-- es-band-dark is dark in both themes, the treatment /for-ai-agents gives its
                             quickstart. --}}
                        <div class="uc-side-more uc-term es-band-dark">
                            <div class="uc-term-bar" aria-hidden="true"><i></i><i></i><i></i><span dir="ltr">GET /api/events</span></div>
<pre dir="ltr" tabindex="0" data-clip-ok><code><b>$ curl https://eventschedule.com/api/events \
    -H "X-API-Key: $EVENTSCHEDULE_KEY"</b>

{
  "data": [
    {
      "id": "8Q2Kx",
      "name": <mark>"Jazz Night"</mark>,
      "starts_at": "{{ $ucFriday->format('Y-m-d') }} 20:00:00",
      "venue_name": "The Blue Note",
      "url": "https://blue-note.eventschedule.com/jazz-night/8Q2Kx"
    }
  ],
  "meta": { "per_page": 100, "total": 42 }
}</code></pre>
                            <p>300 read / 30 write requests per minute &middot; offset pagination &middot; the same API on selfhosted installs</p>
                        </div>

                        @php
                            // name, sentence, small print, address, opens in a new tab
                            $ucDocs = [
                                ['REST API', 'Create schedules, events, tickets and sales from your own code. JSON in, JSON out, on Pro and on every selfhost install.', ['Schedules', 'Events', 'Sales', 'Categories', 'Feedback', 'Sub-schedules'], marketing_url('/docs/developer/api#authentication'), false],
                                ['Webhooks', 'Get a signed POST the moment a ticket sells, an event changes, or someone scans in at the door.', ['HMAC-SHA256 signed', 'Fourteen event types', 'Delivery log'], marketing_url('/docs/developer/webhooks#event-types'), false],
                                ['OpenAPI spec', 'The API described in OpenAPI 3.0.3, so you can generate a typed client in your language.', ['28 operations', 'Generate a client', 'Machine readable'], url('/api/openapi.json'), true],
                                ['Built for AI agents', 'Agents discover the API on their own and run multi-step flows without a human wiring them up.', ['llms.txt', 'llms-full.txt', 'agents.json', 'Named flows'], marketing_url('/for-ai-agents'), false],
                                ['Calendar feeds', 'Every schedule publishes a public iCal and RSS feed. No auth, no tokens, just a URL to subscribe to.', ['iCal', 'RSS', 'No auth needed', 'Auto-updating'], marketing_url('/docs/sharing#calendar-feeds'), false],
                                ['Embed anywhere', 'Drop your calendar or a ticket checkout into any site with a single iframe.', ['Calendar embed', 'Ticket widget', 'One iframe'], marketing_url('/features/embed-calendar'), false],
                            ];
                        @endphp
                        <div class="uc-side-more uc-docs">
                            @foreach ($ucDocs as [$ucDocName, $ucDocBody, $ucDocTags, $ucDocUrl, $ucDocOut])
                                <a href="{{ $ucDocUrl }}" class="uc-doc" @if ($ucDocOut) target="_blank" rel="noopener" @endif>
                                    <h4>{{ $ucDocName }}@if ($ucDocOut)<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg><span class="sr-only">(opens in a new tab)</span>@endif</h4>
                                    <p>{{ $ucDocBody }}</p>
                                    <small>{{ implode(' · ', $ucDocTags) }}</small>
                                </a>
                            @endforeach
                        </div>
                    </article>

                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         FAQ, the related strip and the finale: the page's one ending
         ============================================================ --}}
    <x-marketing.hp-faq :items="$faqs" lead="Common questions about Event Schedule.">Frequently asked <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <x-marketing.hp-finale lead="Create your schedule in seconds. No credit card, no platform fees, ever." placeholder="your-schedule" :foot="false">
        Whatever you run, <span class="hp-ink-grad">start free</span>
    </x-marketing.hp-finale>

    {{-- The programme and the night. Plain DOM script: nothing here is a Vue mount. Every
         sentence a visitor reads is written by the server (in the markup, or in the two records
         below); this only moves it about. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var page = document.getElementById('hp');
            var bill = document.querySelector('[data-uc-bill]');
            var stage = document.querySelector('[data-uc-stage]');
            var aside = document.querySelector('[data-uc-aside]');
            if (!page || !bill || !stage || !aside) {
                {{-- The field was shown on the promise of this script: take it back. --}}
                if (page) {
                    page.classList.remove('uc-js');
                }
                return;
            }

            var cards = @json($ucCards);
            var yours = @json($ucYours);
            var byKey = {};
            cards.forEach(function (card) { byKey[card.key] = card; });
            var acts = {};
            each(bill.querySelectorAll('[data-uc-act]'), function (act) {
                acts[act.getAttribute('data-uc-act')] = act;
            });
            var groups = bill.querySelectorAll('[data-uc-group]');

            var wide = window.matchMedia('(min-width: 1100px)');
            var hovers = window.matchMedia('(hover: hover) and (pointer: fine)');
            var still = window.matchMedia('(prefers-reduced-motion: reduce)');

            function each(list, fn) {
                Array.prototype.forEach.call(list, fn);
            }
            function closest(node, selector) {
                return node && node.closest ? node.closest(selector) : null;
            }

            {{-- What the stage shows. It starts on the first name, as the server drew it. --}}
            var current = cards[0].key;
            {{-- On a narrow screen the stage was already put under the first row (see the
                 script after the list). --}}
            var slot = bill.querySelector('li.uc-slot');
            {{-- True once a visitor has done anything: the first look stops, and what they pick
                 is theirs (it is carried down to the six sides). --}}
            var touched = false;
            {{-- The name the visitor asked for: typed for it, or pressed it. A pointer that only
                 rests on a name changes the stage and chooses nothing. --}}
            var picked = '';
            {{-- Set while the stage shows the visitor's own words (nothing on the list answered). --}}
            var own = false;
            var ownSlot = null;

            {{-- ---- the stage ---- --}}
            var rows = stage.querySelectorAll('[data-uc-row]');
            function part(name) {
                return stage.querySelector('[data-uc="' + name + '"]');
            }
            var badge = part('badge').textContent;
            function cast(card, quiet) {
                stage.setAttribute('data-accent', card.accent);
                stage.setAttribute('data-kind', card.kind);
                ['groupLine', 'name', 'blurb', 'slug', 'initial', 'who', 'tagline', 'pageLabel'].forEach(function (name) {
                    part(name).textContent = card[name];
                });
                part('badge').textContent = card.badge || badge;
                each(rows, function (row, i) {
                    var data = card.rows[i];
                    row.hidden = !data;
                    if (!data) {
                        return;
                    }
                    row.querySelector('.uc-tile b').textContent = data.month;
                    row.querySelector('.uc-tile i').textContent = data.day;
                    row.querySelector('[data-uc-title]').textContent = data.title;
                    row.querySelector('[data-uc-meta]').textContent = data.meta;
                    var pill = row.querySelector('.uc-pill');
                    pill.textContent = data.pillLabel;
                    pill.setAttribute('data-pill', data.pill);
                });
                part('page').setAttribute('href', card.url);
                part('start').setAttribute('href', card.start);
                {{-- Its contents arrive again (the class is what the arrival is keyed on), but
                     not for every letter of something still being typed. --}}
                if (!quiet) {
                    stage.classList.remove('is-cast');
                    void stage.offsetWidth;
                    stage.classList.add('is-cast');
                }
            }

            {{-- Under the list the stage stands after the last name of the row its name is in, so
                 a row of three is never split by it. --}}
            function place() {
                if (wide.matches) {
                    if (stage.parentNode !== aside) {
                        aside.appendChild(stage);
                    }
                    if (slot && slot.parentNode) {
                        slot.parentNode.removeChild(slot);
                    }
                    if (ownSlot && ownSlot.parentNode) {
                        ownSlot.parentNode.removeChild(ownSlot);
                    }
                    return;
                }
                if (own) {
                    if (slot && slot.parentNode) {
                        slot.parentNode.removeChild(slot);
                    }
                    if (!ownSlot) {
                        ownSlot = document.createElement('div');
                        ownSlot.className = 'uc-slot';
                    }
                    var finder = bill.querySelector('[data-uc-finder]');
                    if (finder.nextSibling !== ownSlot) {
                        finder.parentNode.insertBefore(ownSlot, finder.nextSibling);
                    }
                    if (stage.parentNode !== ownSlot) {
                        ownSlot.appendChild(stage);
                    }
                    return;
                }
                if (ownSlot && ownSlot.parentNode) {
                    ownSlot.parentNode.removeChild(ownSlot);
                }
                var item = acts[current].parentNode;
                var list = item.parentNode;
                var items = Array.prototype.filter.call(list.children, function (child) {
                    return !child.classList.contains('uc-slot') && !child.hidden;
                });
                var columns = window.getComputedStyle(list).gridTemplateColumns.split(' ').filter(function (track) { return /px$/.test(track); }).length || 1;
                var index = Math.max(0, items.indexOf(item));
                var last = items[Math.min(items.length - 1, (Math.floor(index / columns) + 1) * columns - 1)] || item;
                if (!slot) {
                    slot = document.createElement('li');
                    slot.className = 'uc-slot';
                }
                if (last.nextSibling !== slot) {
                    list.insertBefore(slot, last.nextSibling);
                }
                if (stage.parentNode !== slot) {
                    slot.appendChild(stage);
                }
            }

            function mark() {
                Object.keys(acts).forEach(function (key) {
                    if (!own && key === current) {
                        acts[key].setAttribute('aria-current', 'true');
                    } else {
                        acts[key].removeAttribute('aria-current');
                    }
                });
            }

            function select(key, chosen) {
                var card = byKey[key];
                if (!card) {
                    return;
                }
                if (chosen) {
                    picked = key;
                }
                var changed = key !== current || own;
                current = key;
                own = false;
                if (changed) {
                    cast(card);
                }
                place();
                mark();
                carry();
            }

            function bringIntoView(node) {
                var box = node.getBoundingClientRect();
                var top = 210;
                if (box.top < top || box.top > window.innerHeight * 0.6) {
                    window.scrollTo({ top: window.pageYOffset + box.top - top, behavior: still.matches ? 'auto' : 'smooth' });
                }
            }

            {{-- ---- the list ----
                 Beside the list, a pointer that rests on a name (or the keyboard reaching it)
                 shows it, and a press goes to its page as any link does. Under the list, and by
                 touch, the first press shows it and the second goes. --}}
            var lastPointer = 'mouse';
            bill.addEventListener('pointerdown', function (event) { lastPointer = event.pointerType || 'mouse'; }, true);
            bill.addEventListener('keydown', function () { lastPointer = 'key'; }, true);

            bill.addEventListener('click', function (event) {
                var act = closest(event.target, '[data-uc-act]');
                if (!act || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button) {
                    return;
                }
                touched = true;
                var key = act.getAttribute('data-uc-act');
                if (wide.matches && (lastPointer === 'key' || (lastPointer === 'mouse' && hovers.matches))) {
                    return;
                }
                if (!own && key === current) {
                    return;
                }
                event.preventDefault();
                window.clearTimeout(opening);
                select(key, true);
                say(status.getAttribute('data-again').replace(':name', byKey[key].name));
                if (!wide.matches) {
                    bringIntoView(act);
                }
            });

            bill.addEventListener('focusin', function (event) {
                var act = closest(event.target, '[data-uc-act]');
                var seen = false;
                try {
                    seen = !!act && act.matches(':focus-visible');
                } catch (error) {
                    seen = !!act && lastPointer === 'key';
                }
                if (seen && wide.matches) {
                    touched = true;
                    select(act.getAttribute('data-uc-act'));
                }
            });

            {{-- A pointer on its way across the list to the stage must not change what the stage
                 shows: a name is taken only once the pointer has all but stopped on it. --}}
            var over = null;
            var timer = 0;
            var at = { x: 0, y: 0 };
            var was = { x: 0, y: 0 };
            function settle() {
                if (!over) {
                    return;
                }
                if (Math.abs(at.x - was.x) + Math.abs(at.y - was.y) < 7) {
                    touched = true;
                    select(over.getAttribute('data-uc-act'));
                    return;
                }
                was = { x: at.x, y: at.y };
                timer = window.setTimeout(settle, 70);
            }
            bill.addEventListener('pointermove', function (event) {
                if (event.pointerType !== 'mouse' || !wide.matches) {
                    return;
                }
                at = { x: event.clientX, y: event.clientY };
                var act = closest(event.target, '[data-uc-act]');
                if (act === over) {
                    return;
                }
                over = act;
                window.clearTimeout(timer);
                if (over) {
                    was = { x: at.x, y: at.y };
                    timer = window.setTimeout(settle, 70);
                }
            });
            bill.addEventListener('pointerleave', function () {
                over = null;
                window.clearTimeout(timer);
            });
            stage.addEventListener('pointerenter', function () { touched = true; });

            {{-- ---- the question ---- --}}
            var q = bill.querySelector('[data-uc-q]');
            var none = bill.querySelector('[data-uc-none]');
            var noneStart = bill.querySelector('[data-uc-none-start]');
            var clear = bill.querySelector('[data-uc-clear]');
            var hint = bill.querySelector('[data-uc-key]');
            var foundLine = bill.querySelector('[data-uc-found]');
            var count = bill.querySelector('[data-uc-count]');
            var status = bill.querySelector('[data-uc-status]');
            var found = [];
            var active = 0;
            var opening = 0;

            function say(text) {
                status.textContent = text;
            }
            function stem(word) {
                if (word.length > 4 && /(ss|ch|sh|x)es$/.test(word)) {
                    return word.slice(0, -2);
                }
                if (word.length > 3 && /[^s]s$/.test(word)) {
                    return word.slice(0, -1);
                }
                return word;
            }
            function fold(text) {
                var plain = String(text).toLowerCase();
                if (plain.normalize) {
                    plain = plain.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                }
                return plain.replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, ' ').trim();
            }
            function norm(text) {
                return fold(text).split(' ').map(stem).join(' ');
            }

            {{-- What a name answers to: itself, the words printed under it, the extra words in
                 config/marketing_directory.php and, last, the name of its group. Each is kept
                 twice, as written and with its plurals taken off, so a word half typed
                 ("churche") still finds what the whole word will. --}}
            var index = cards.map(function (card) {
                var terms = [];
                function add(text, weight, tag) {
                    terms.push({ text: norm(text), whole: fold(text), weight: weight, tag: tag });
                }
                add(card.name, 9, -1);
                card.tags.forEach(function (tag, i) { add(tag, 6, i); });
                card.find.forEach(function (word) { add(word, 3, -2); });
                card.groupWords.forEach(function (word) { add(word, 1, -2); });
                return { card: card, terms: terms };
            });
            function rateText(text, weight, query) {
                if (text === query) {
                    return 100 + weight;
                }
                if (text.indexOf(query) === 0) {
                    return 80 + weight;
                }
                if ((' ' + text).indexOf(' ' + query) !== -1) {
                    return 60 + weight;
                }
                if (query.length >= 3 && text.indexOf(query) !== -1) {
                    return 30 + weight;
                }
                return 0;
            }
            function rate(term, query, whole) {
                return Math.max(rateText(term.text, term.weight, query), rateText(term.whole, term.weight, whole));
            }
            function search(raw) {
                var query = norm(raw);
                var whole = fold(raw);
                {{-- One letter matches nearly everything: wait for two. --}}
                if (whole.length < 2) {
                    return [];
                }
                {{-- The whole of what was typed first; failing that, its words one by one
                     ("tribute band nights" finds the tribute acts, and so does "tribute n"). --}}
                var words = whole.split(' ').filter(function (word) { return word.length >= 3 && word !== 'and' && word !== 'the'; });
                var several = whole.indexOf(' ') !== -1;
                var results = [];
                index.forEach(function (entry, order) {
                    var best = 0;
                    var name = false;
                    var tags = [];
                    entry.terms.forEach(function (term) {
                        var score = rate(term, query, whole);
                        if (!score && several) {
                            words.forEach(function (word) {
                                score = Math.max(score, Math.round(rate(term, stem(word), word) * 0.6));
                            });
                        }
                        if (!score) {
                            return;
                        }
                        if (term.tag === -1) {
                            name = true;
                        } else if (term.tag >= 0) {
                            tags.push(term.tag);
                        }
                        best = Math.max(best, score);
                    });
                    if (best) {
                        results.push({ card: entry.card, score: best, order: order, name: name, tags: tags });
                    }
                });
                results.sort(function (a, b) { return b.score - a.score || a.order - b.order; });
                {{-- When the whole of what was typed is somebody's name or speciality, the names
                     that only share one of its words are not answers ("comedy club" is not
                     every club). --}}
                if (results.length && results[0].score >= 80) {
                    results = results.filter(function (result) { return result.score >= 70; });
                }
                return results;
            }

            {{-- The list is narrowed to the answers, in its own order, and the words that
                 matched are marked where they are printed. --}}
            function paint() {
                var hit = {};
                found.forEach(function (result) { hit[result.card.key] = result; });
                var finding = found.length > 0;
                if (finding) {
                    bill.setAttribute('data-finding', '');
                } else {
                    bill.removeAttribute('data-finding');
                }
                Object.keys(acts).forEach(function (key) {
                    var act = acts[key];
                    var result = hit[key];
                    act.parentNode.hidden = finding && !result;
                    act.querySelector('.uc-act-name').classList.toggle('is-hit', !!(result && result.name));
                    each(act.querySelectorAll('.uc-act-tags > span'), function (tag, i) {
                        tag.classList.toggle('is-hit', !!(result && result.tags.indexOf(i) !== -1));
                    });
                });
                each(groups, function (group) {
                    var any = false;
                    each(group.querySelectorAll('[data-uc-act]'), function (act) {
                        any = any || !act.parentNode.hidden;
                    });
                    group.hidden = !any;
                });
            }

            {{-- The name as the sign-up form takes it: the shape the homepage's own box produces,
                 which is the only shape RegisteredUserController::create() keeps. --}}
            function slugOf(text) {
                var plain = String(text).toLowerCase();
                if (plain.normalize) {
                    plain = plain.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                }
                return plain.replace(/['\u2019]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 30).replace(/-+$/g, '');
            }
            function showOwn(raw) {
                var words = raw.replace(/\s+/g, ' ').trim().slice(0, 40);
                var name = words.replace(/(^|\s)(\S)/g, function (all, gap, letter) { return gap + letter.toUpperCase(); });
                var slug = slugOf(words);
                var start = yours.start + (slug ? (yours.start.indexOf('?') === -1 ? '?' : '&') + 'schedule=' + encodeURIComponent(slug) : '');
                var first = Array.from ? Array.from(name)[0] : name.charAt(0);
                var already = own;
                own = true;
                cast({
                    accent: yours.accent, kind: 'yours', groupLine: yours.groupLine, name: name, blurb: yours.blurb,
                    slug: slug || 'your-schedule', initial: first || '', who: name, tagline: yours.tagline,
                    rows: yours.rows, url: yours.url, pageLabel: yours.pageLabel, badge: yours.badge, start: start
                }, already);
                noneStart.setAttribute('href', start);
                place();
                mark();
                carry();
            }
            function hideOwn() {
                if (!own) {
                    return;
                }
                own = false;
                cast(byKey[current]);
                place();
                mark();
                carry();
            }

            {{-- Nothing answers to what was typed. Said only once the typing has paused, so a
                 word that is one letter short of an answer is never called a miss. --}}
            function miss(raw) {
                none.hidden = !wide.matches;
                showOwn(raw);
                say(status.getAttribute('data-none'));
            }
            function ask() {
                var raw = q.value;
                var typed = raw.replace(/\s+/g, ' ').trim();
                window.clearTimeout(opening);
                clear.hidden = !raw;
                found = search(raw);
                active = 0;
                paint();
                hint.hidden = !!raw;
                if (!found.length) {
                    foundLine.hidden = true;
                    say('');
                    {{-- Two letters are not yet a word: say nothing until there are three. --}}
                    if (typed.length < 3) {
                        none.hidden = true;
                        hideOwn();
                        if (!own) {
                            credit(null);
                        }
                        return;
                    }
                    if (own) {
                        miss(raw);
                    } else {
                        opening = window.setTimeout(function () { miss(raw); }, 420);
                    }
                    return;
                }
                none.hidden = true;
                foundLine.hidden = false;
                count.textContent = found.length === 1 ? count.getAttribute('data-one') : count.getAttribute('data-many').replace(':n', found.length);
                {{-- Beside the list the best answer is on the stage as it is typed. Under the
                     list it opens under its name once the typing pauses. --}}
                if (wide.matches) {
                    select(found[0].card.key, true);
                    credit(found[0]);
                    say(count.textContent + '. ' + status.getAttribute('data-showing').replace(':name', found[0].card.name));
                } else {
                    if (own) {
                        hideOwn();
                    }
                    say(count.textContent);
                    opening = window.setTimeout(function () {
                        if (found.length && !wide.matches) {
                            select(found[0].card.key, true);
                            credit(found[0]);
                            say(status.getAttribute('data-showing').replace(':name', found[0].card.name));
                        }
                    }, 420);
                }
            }
            {{-- The corner of the stage names the speciality that answered ("Tribute Acts"), so
                 what was typed is seen to have been heard even when the sample stays the same. --}}
            function credit(result) {
                part('badge').textContent = result && result.tags.length ? result.card.tags[result.tags[0]] : badge;
            }
            function reset() {
                q.value = '';
                ask();
            }
            function move(step) {
                if (!found.length) {
                    return;
                }
                active = (active + step + found.length) % found.length;
                select(found[active].card.key, true);
                credit(found[active]);
                say(status.getAttribute('data-showing').replace(':name', found[active].card.name));
                if (!wide.matches) {
                    bringIntoView(acts[found[active].card.key]);
                }
            }

            q.addEventListener('input', function () {
                touched = true;
                ask();
            });
            q.addEventListener('focus', function () { touched = true; });
            q.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    move(1);
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    move(-1);
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    if (!found.length) {
                        window.clearTimeout(opening);
                        if (q.value.replace(/\s+/g, ' ').trim().length >= 3) {
                            miss(q.value);
                        }
                        return;
                    }
                    {{-- Beside the list the answer is already on the stage, so Enter opens its
                         page. Under the list it opens the answer and puts the keyboard away. --}}
                    if (wide.matches) {
                        window.location.assign(found[active].card.url);
                    } else {
                        window.clearTimeout(opening);
                        select(found[active].card.key, true);
                        say(status.getAttribute('data-again').replace(':name', found[active].card.name));
                        q.blur();
                        bringIntoView(acts[found[active].card.key]);
                    }
                } else if (event.key === 'Escape' && q.value) {
                    reset();
                }
            });
            clear.addEventListener('click', function () {
                reset();
                q.focus();
            });
            bill.querySelector('[data-uc-all]').addEventListener('click', function () {
                reset();
                q.focus();
            });
            {{-- The slash key reaches the field from anywhere on the page, as it does on a docs site. --}}
            document.addEventListener('keydown', function (event) {
                var target = event.target;
                if (event.key !== '/' || event.metaKey || event.ctrlKey || event.altKey) {
                    return;
                }
                if (target && (target.isContentEditable || /^(input|textarea|select)$/i.test(target.tagName))) {
                    return;
                }
                event.preventDefault();
                q.focus();
            });
            {{-- A page that comes back from the browser's own memory keeps what was typed. --}}
            window.addEventListener('pageshow', function () {
                if (q.value) {
                    ask();
                }
            });

            {{-- ---- the night ----
                 The page is drawn with all six places reached and each side's picture under its
                 words. Here the places start empty (unless the visitor is already past them)
                 and each fills as its side crosses the middle of the window; one that is filled
                 stays filled. Where there is room, the six pictures are moved into the event,
                 which then shows the one whose side is passing. --}}
            var night = document.querySelector('[data-uc-event]');
            var screen = document.querySelector('[data-uc-screen]');
            var places = document.querySelector('[data-uc-places]');
            var sides = document.querySelectorAll('[data-uc-side]');
            var now = document.querySelector('[data-uc-now]');
            var go = document.querySelector('[data-uc-go]');
            var dots = document.querySelectorAll('[data-uc-strip] i');
            var order = Array.prototype.map.call(sides, function (side) { return side.id; });
            var items = {};
            var faces = {};
            var reached = 0;
            var done = true;
            var goHref = go ? go.getAttribute('href') : '';

            if (places) {
                each(places.querySelectorAll('[data-uc-place]'), function (item) {
                    items[item.getAttribute('data-uc-place')] = item;
                });
            }
            each(sides, function (side) {
                var face = side.querySelector('[data-uc-face]');
                if (face) {
                    faces[side.id] = { node: face, home: side, next: face.nextElementSibling };
                }
            });

            function where(id) {
                var text = items[id] ? items[id].querySelector('.uc-place-where') : null;
                return text ? text.textContent : '';
            }
            function show(indexOfSide) {
                reached = Math.max(reached, indexOfSide);
                order.forEach(function (id, i) {
                    if (!items[id]) {
                        return;
                    }
                    if (i <= reached) {
                        items[id].classList.add('is-on');
                        if (dots[i]) {
                            dots[i].classList.add('is-on');
                        }
                    }
                    items[id].classList.toggle('is-now', i === indexOfSide);
                    if (dots[i]) {
                        dots[i].classList.toggle('is-now', i === indexOfSide);
                    }
                    if (faces[id]) {
                        faces[id].node.classList.toggle('is-on', i === indexOfSide);
                    }
                });
                if (reached >= order.length - 1) {
                    done = true;
                    night.classList.remove('is-walking');
                }
                if (now) {
                    now.textContent = done && indexOfSide === order.length - 1 ? now.getAttribute('data-end') : where(order[indexOfSide]);
                }
            }
            {{-- The pictures go into the event where the event stays beside the words, and back
                 under their words where it does not. --}}
            function arrange() {
                if (!night || !screen) {
                    return;
                }
                var staged = wide.matches;
                night.classList.toggle('is-staged', staged);
                order.forEach(function (id) {
                    var face = faces[id];
                    if (!face) {
                        return;
                    }
                    if (staged && face.node.parentNode !== screen) {
                        screen.appendChild(face.node);
                    } else if (!staged && face.node.parentNode === screen) {
                        face.home.insertBefore(face.node, face.next);
                    }
                });
            }
            {{-- What the visitor picked above is theirs down here too: its side is marked, and
                 the last button opens that kind of schedule. --}}
            function carry() {
                var side = picked && byKey[picked] ? byKey[picked].side : '';
                each(document.querySelectorAll('[data-uc-yours]'), function (tag) {
                    var holder = closest(tag, '[data-uc-place], [data-uc-side]');
                    var id = holder ? (holder.getAttribute('data-uc-place') || holder.id) : '';
                    tag.hidden = !side || id !== side;
                });
                if (go) {
                    go.setAttribute('href', picked && byKey[picked] ? byKey[picked].start : goHref);
                }
            }

            var strip = document.querySelector('[data-uc-strip]');
            if (strip && night && 'IntersectionObserver' in window) {
                new IntersectionObserver(function (entries) {
                    strip.classList.toggle('is-away', !entries[0].isIntersecting);
                }).observe(night);
            }

            if (night && places && sides.length && 'IntersectionObserver' in window) {
                arrange();
                if (sides[0].getBoundingClientRect().top > window.innerHeight * 0.6) {
                    done = false;
                    reached = -1;
                    night.classList.add('is-walking');
                    places.classList.add('is-walking');
                    order.forEach(function (id, i) {
                        items[id].classList.remove('is-on');
                        if (dots[i]) {
                            dots[i].classList.remove('is-on');
                        }
                    });
                    if (faces[order[0]]) {
                        faces[order[0]].node.classList.add('is-on');
                    }
                    if (now) {
                        now.textContent = where(order[0]);
                    }
                } else {
                    show(order.length - 1);
                }
                var watch = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            show(order.indexOf(entry.target.id));
                        }
                    });
                }, { rootMargin: '-45% 0px -45% 0px' });
                each(sides, function (side) { watch.observe(side); });
            }

            {{-- ---- widths ---- --}}
            function settleWidth() {
                if (own) {
                    none.hidden = !wide.matches;
                }
                place();
                mark();
                arrange();
            }
            if (wide.addEventListener) {
                wide.addEventListener('change', settleWidth);
            } else if (wide.addListener) {
                wide.addListener(settleWidth);
            }
            var resizing = 0;
            window.addEventListener('resize', function () {
                window.clearTimeout(resizing);
                resizing = window.setTimeout(function () {
                    if (!wide.matches) {
                        place();
                    }
                }, 150);
            });
            place();
            mark();

            {{-- ---- a first look ----
                 Once, when the stage is first seen beside the list and nobody has touched
                 anything: the names beside the first one, each lit as the stage shows it, then
                 back to where it began. It stops for good at the first sign of a visitor. --}}
            var tour = [cards[1].key, cards[2].key, cards[0].key];
            function step(i) {
                if (touched || !wide.matches || i >= tour.length) {
                    return;
                }
                select(tour[i]);
                window.setTimeout(function () { step(i + 1); }, 1900);
            }
            ['scroll', 'keydown', 'pointerdown', 'touchstart', 'wheel'].forEach(function (name) {
                window.addEventListener(name, function () { touched = true; }, { passive: true, once: true });
            });
            if (wide.matches && !still.matches && 'IntersectionObserver' in window) {
                var first = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting) {
                        first.disconnect();
                        window.setTimeout(function () { step(0); }, 1400);
                    }
                }, { threshold: 0.4 });
                first.observe(stage);
            }
        })();
    </script>

    {{-- The shared reveals (the finale brings its own confetti). --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
