<x-marketing-layout>
    <x-slot name="title">Privacy-First Event Analytics - Event Schedule</x-slot>
    <x-slot name="description">Track page views, device breakdown, traffic sources, and conversion rates. Privacy-first analytics with no external services required.</x-slot>
    <x-slot name="breadcrumbTitle">Analytics</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Analytics",
        "description": "Track page views, device breakdown, traffic sources, and conversion rates. Privacy-first analytics with no external services required.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Analytics"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Analytics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Analytics Software",
        "operatingSystem": "Web",
        "description": "Privacy-first event analytics. Page views by day, week or month, device breakdown, eight traffic-source buckets, referrer domains, UTM parameters, country-level visitor locations and social link clicks, with no external services required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Page views by day, week or month across seven date ranges",
            "Device breakdown across desktop, mobile, tablet and unknown",
            "Eight traffic-source buckets including newsletter, boost and promo",
            "Top referrer domains and top UTM source, medium and campaign values",
            "Country-level visitor locations from a local lookup file",
            "Social link clicks by platform",
            "Top events by views, and views split by schedule",
            "Appearance views for talent and venue schedules",
            "Conversion rate, revenue per view and promo code performance with ticketing",
            "Check-in attendance rates, no-shows and arrival times with ticketing",
            "Crawler filtering and a daily per-visitor view cap",
            "No external analytics services and no tracking cookie"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
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
           For analytics "The Dashboard" styles. A dashboard is an
           instrument panel: it tells the driver how fast, how far and
           how much fuel. It does not keep a passenger list. That is
           exactly the shape of this feature, so the metaphor and the
           product argument are the same sentence.

           THE MATERIAL RULE, and the thing that makes the page look
           like nothing else on the site: COPY LIVES ON PAPER, DATA
           LIVES IN A LIT WELL. Every numeric readout sits in
           .es-dash-well - a recessed, always-dark instrument well with
           green LED numerals - and the well renders IDENTICALLY with
           .dark on and off, because a physical gauge does not repaint
           itself when the room lights change. The prose around it is
           mode-aware paper. Verified with the verifier's --bands flag.

           WHY NOT A CHART: /features/boost and the AP itself already
           draw line charts, and a marketing page cannot honestly draw
           a chart of somebody else's data. The signature devices are
           therefore INSTRUMENTS (odometer, segmented meters, a
           seven-detent range dial) plus a real <table> of the stored
           row, because the row IS the privacy argument: there is no
           visitor column, so "who was it" has nowhere to land.

           COLOUR: emerald, kept from the first-wave page. Deliberately
           NOT for-theaters' bottle green (#14532d / #86efac) and not
           the teal that /for-djs holds. Light ground #f4f7f5 with
           #047857; lit wells use #34d399.

           NEVER use text-gray-500 here: 4.83 on pure white but only
           ~4.2 on this page's tinted ground. Use .es-dash-muted
           (7.17 on the page ground, 7.74 on a white card).

           BLADE RULE for this block: no @supports probes. A "#" hex
           inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink ---------------------------------------- */
        .es-dash-page { background-color: #f4f7f5; color: #101613; }
        .dark .es-dash-page { background-color: #0a0f0c; color: #e6ece8; }
        .es-dash-ink { color: #101613; }
        .dark .es-dash-ink { color: #e6ece8; }
        .es-dash-muted { color: #4b5550; }
        .dark .es-dash-muted { color: #9aaba3; }
        .es-dash-accent { color: #047857; }
        .dark .es-dash-accent { color: #34d399; }
        /* Always-lit / always-dim inks, for the fixed-dark surfaces. These do
           NOT flip with the colour mode, because the surfaces they sit on do
           not either. Measured on the darkest of those grounds (#141b18):
           lit 9.11, fink 14.61, fmuted 7.28, dim 4.89. */
        .es-dash-lit { color: #34d399; }
        .es-dash-dim { color: #6b8f80; }
        .es-dash-fink { color: #e6ece8; }
        .es-dash-fmuted { color: #9aaba3; }
        .es-dash-hair { border-color: rgba(16, 22, 19, 0.1); }
        .dark .es-dash-hair { border-color: rgba(230, 236, 232, 0.12); }

        /* --- Paper cards ------------------------------------------- */
        .es-dash-card {
            border: 1px solid rgba(16, 22, 19, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-dash-card {
            border-color: rgba(230, 236, 232, 0.12);
            background: #111815;
        }

        /* --- Fixed-dark band -------------------------------------- */
        .es-dash-band {
            background-color: #0c1310;
            background-image: radial-gradient(120% 100% at 50% 0%, #13201b 0%, #0e1613 55%, #070b09 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 236, 232, 0.05);
        }

        /* --- The well: a recessed instrument readout --------------- */
        .es-dash-well {
            background-color: #0b1310;
            background-image: linear-gradient(180deg, #0e1714 0%, #0a110e 100%);
            border: 1px solid rgba(230, 236, 232, 0.1);
            border-radius: 1.1rem;
            box-shadow: inset 0 2px 12px rgba(0, 0, 0, 0.7), inset 0 -1px 0 rgba(230, 236, 232, 0.05);
        }

        /* --- LED numerals ----------------------------------------- */
        .es-dash-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-dash-readout {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #34d399;
            text-shadow: 0 0 18px rgba(52, 211, 153, 0.35);
        }

        /* --- Etched panel labels. Written as real rules, not Tailwind
               arbitrary values: no build runs during this campaign, so a
               text-[0.62rem] that is not already in the compiled bundle
               would silently do nothing. --- */
        .es-dash-label {
            font-size: 0.62rem;
            font-weight: 700;
            line-height: 1.4;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }
        .es-dash-fine { font-size: 0.68rem; line-height: 1.6; }
        /* The dot-nav tooltip ground, light and dark. */
        .es-dash-tip { background: #ffffff; }
        .dark .es-dash-tip { background: #141b18; }

        /* --- A record table: wide enough to stay readable, and it
               scrolls inside its own wrapper rather than the page. --- */
        .es-dash-table { width: 100%; min-width: 28rem; border-collapse: collapse; }

        /* --- Tick strip, the scale etched under a readout ---------- */
        .es-dash-ticks {
            height: 0.5rem;
            background-image: repeating-linear-gradient(90deg,
                rgba(230, 236, 232, 0.3) 0 1px, transparent 1px 11px);
        }

        /* --- Segmented meter: a bar with a hard scale over it ------ */
        .es-dash-meter {
            position: relative;
            height: 0.7rem;
            border-radius: 0.15rem;
            overflow: hidden;
            background: rgba(16, 22, 19, 0.1);
        }
        .dark .es-dash-meter { background: rgba(230, 236, 232, 0.1); }
        .es-dash-meter-fill {
            position: absolute;
            top: 0;
            bottom: 0;
            border-radius: 0.15rem;
            background: #047857;
        }
        .dark .es-dash-meter-fill { background: #34d399; }
        .es-dash-meter-scale {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(90deg,
                transparent 0 calc(10% - 1px), rgba(16, 22, 19, 0.28) calc(10% - 1px) 10%);
        }
        .dark .es-dash-meter-scale {
            background-image: repeating-linear-gradient(90deg,
                transparent 0 calc(10% - 1px), rgba(230, 236, 232, 0.24) calc(10% - 1px) 10%);
        }

        /* --- Sparkline columns (one per day) ----------------------- */
        .es-dash-col {
            flex: 1 1 0;
            min-width: 0;
            border-radius: 1px 1px 0 0;
            background: linear-gradient(to top, rgba(52, 211, 153, 0.3), #34d399);
        }

        /* --- Stacked source bar ----------------------------------- */
        .es-dash-src {
            display: flex;
            height: 1.7rem;
            border-radius: 0.3rem;
            overflow: hidden;
            border: 1px solid rgba(230, 236, 232, 0.14);
        }
        .es-dash-seg {
            min-width: 2px;
            border-inline-end: 1px solid rgba(7, 12, 10, 0.55);
        }
        .es-dash-seg:last-child { border-inline-end: 0; }

        /* --- Range dial: seven detents, one click each ------------- */
        .es-dash-dial {
            display: flex;
            align-items: stretch;
            border-radius: 0.4rem;
            overflow: hidden;
            border: 1px solid rgba(230, 236, 232, 0.14);
        }
        .es-dash-detent {
            flex: 1 1 0;
            min-width: 0;
            padding: 0.45rem 0.2rem;
            text-align: center;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6b8f80;
            border-inline-start: 1px solid rgba(230, 236, 232, 0.08);
        }
        .es-dash-detent:first-child { border-inline-start: 0; }
        .es-dash-detent-on { color: #0b1310; background: #34d399; }

        /* --- Indicator pips --------------------------------------- */
        .es-dash-pip {
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: #34d399;
            box-shadow: 0 0 8px rgba(52, 211, 153, 0.85);
        }

        /* --- A lit rail down the start edge of a stage card -------- */
        .es-dash-rail { border-inline-start: 2px solid rgba(4, 120, 87, 0.7); }
        .dark .es-dash-rail { border-inline-start-color: rgba(52, 211, 153, 0.7); }

        /* --- Eyebrow / labels ------------------------------------- */
        .es-dash-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5550;
        }
        .dark .es-dash-tag { color: #9aaba3; }

        /* --- Section numeral, on a bezelled plate ----------------- */
        .es-dash-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(16, 22, 19, 0.18);
            background: #ffffff;
            color: #101613;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-dash-corner {
            border-color: rgba(230, 236, 232, 0.2);
            background: #141b18;
            color: #e6ece8;
        }
        .es-dash-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #047857;
        }
        .dark .es-dash-corner::before { background: #34d399; }

        /* --- Plan pills ------------------------------------------- */
        .es-dash-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(4, 120, 87, 0.45);
            color: #047857;
        }
        .dark .es-dash-plan { border-color: rgba(52, 211, 153, 0.45); color: #34d399; }
        .es-dash-plan-pro { border-color: rgba(16, 22, 19, 0.35); color: #101613; }
        .dark .es-dash-plan-pro { border-color: rgba(230, 236, 232, 0.38); color: #e6ece8; }

        /* --- Buttons and links ------------------------------------ */
        /* The action switch is one physical colour in both modes: a
           green panel switch with white legend, 5.48 either way. */
        .es-dash-btn {
            background-color: #047857;
            box-shadow: 0 18px 36px -14px rgba(4, 120, 87, 0.5);
        }
        .es-dash-btn:hover {
            background-color: #065f46;
            box-shadow: 0 22px 44px -14px rgba(4, 120, 87, 0.6);
        }
        .es-dash-link { color: #047857; }
        .es-dash-link:hover { color: #101613; }
        .dark .es-dash-link { color: #34d399; }
        .dark .es-dash-link:hover { color: #e6ece8; }

        /* --- Chips ------------------------------------------------ */
        .es-dash-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 22, 19, 0.16);
            background: rgba(255, 255, 255, 0.72);
            color: #4b5550;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-dash-chip {
            border-color: rgba(230, 236, 232, 0.16);
            background: #141b18;
            color: #9aaba3;
        }

        /* --- FAQ / related hover --------------------------------- */
        .es-dash-hover:hover { border-color: rgba(4, 120, 87, 0.5); }
        .dark .es-dash-hover:hover { border-color: rgba(52, 211, 153, 0.5); }
        .es-dash-hover:hover .es-dash-hover-title,
        .es-dash-hover:hover .es-dash-hover-arrow { color: #047857; }
        .dark .es-dash-hover:hover .es-dash-hover-title,
        .dark .es-dash-hover:hover .es-dash-hover-arrow { color: #34d399; }

        /* --- Motion: one slow light sweep across a lit well ------- */
        .es-dash-sweep {
            position: absolute;
            inset: 0;
            border-radius: 1.1rem;
            background-image: linear-gradient(100deg,
                transparent 42%, rgba(52, 211, 153, 0.07) 50%, transparent 58%);
            background-size: 260% 100%;
            background-repeat: no-repeat;
            animation: es-dash-sweep 9s linear infinite;
        }
        @keyframes es-dash-sweep {
            from { background-position: 130% 0; }
            to { background-position: -130% 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-dash-sweep { animation: none; }
        }

        /* ==========================================================
           FIXED-SURFACE OVERRIDES. These come after the base + .dark
           rules on purpose: inside a lit well or a fixed-dark band the
           surface does not change with the colour mode, so anything
           that would normally flip has to be pinned. Shared classes
           carry their own .dark rules in marketing.css, which is why
           grid-overlay / animate-shimmer / es-claim are pinned here.
           ========================================================== */
        .es-dash-well .es-dash-meter,
        .es-dash-band .es-dash-meter { background: rgba(230, 236, 232, 0.1); }
        .es-dash-well .es-dash-meter-fill,
        .es-dash-band .es-dash-meter-fill { background: #34d399; }
        .es-dash-well .es-dash-meter-scale,
        .es-dash-band .es-dash-meter-scale {
            background-image: repeating-linear-gradient(90deg,
                transparent 0 calc(10% - 1px), rgba(230, 236, 232, 0.24) calc(10% - 1px) 10%);
        }
        .es-dash-well .es-dash-hair,
        .es-dash-band .es-dash-hair { border-color: rgba(230, 236, 232, 0.12); }
        .es-dash-band .es-dash-card { border-color: rgba(230, 236, 232, 0.14); background: #141b18; }
        .es-dash-band .es-dash-tag { color: #34d399; }
        .es-dash-band .es-dash-corner {
            border-color: rgba(230, 236, 232, 0.2);
            background: #141b18;
            color: #e6ece8;
        }
        .es-dash-band .es-dash-corner::before { background: #34d399; }
        .es-dash-band .es-dash-plan { border-color: rgba(52, 211, 153, 0.45); color: #34d399; }
        .es-dash-band .es-dash-plan-pro { border-color: rgba(230, 236, 232, 0.38); color: #e6ece8; }
        .es-dash-band .es-dash-rail { border-inline-start-color: rgba(52, 211, 153, 0.7); }
        /* The odometer strip hard-codes the brand-blue gradient in
           marketing.css, so each digit is re-inked as a green LED. */
        .es-dash-well .es-od-strip span {
            background: none;
            -webkit-text-fill-color: #34d399;
            color: #34d399;
        }
        .es-dash-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 236, 232, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 236, 232, 0.05) 1px, transparent 1px);
        }
        .es-dash-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-dash-band .es-claim:focus-within {
            border-color: rgba(52, 211, 153, 0.75);
            box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.22);
        }

        /* --- Shared-system recolours (brand blue by default) ------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(4, 120, 87, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(52, 211, 153, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(4, 120, 87, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(52, 211, 153, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #047857; }
        .dark .es-dot.is-active .es-dot-pip { background: #34d399; }

        /* --- Focus rings. No border-radius here: setting it would
               change the element's own shape on focus. --- */
        #es-dash-page a:focus-visible,
        #es-dash-page summary:focus-visible,
        #es-dash-page button:focus-visible {
            outline: 2px solid #047857;
            outline-offset: 3px;
        }
        .dark #es-dash-page a:focus-visible,
        .dark #es-dash-page summary:focus-visible,
        .dark #es-dash-page button:focus-visible {
            outline-color: #34d399;
        }
        .es-dash-band a:focus-visible,
        .es-dash-band summary:focus-visible,
        .es-dash-band button:focus-visible {
            outline-color: #34d399 !important;
        }
    </style>

    @php
        // Thirty days of daily views for the sparkline. Hand-written, not random,
        // so the shape is stable and the tail matches the stored rows shown in
        // section 04 to the view: Jul 25 = 88, Jul 26 = 95, Jul 27 = 77, Jul 28 = 84.
        $dailyViews = [34, 41, 29, 38, 52, 61, 44, 39, 47, 55, 72, 68, 51, 46, 58, 64, 49, 43, 57, 66, 81, 74, 59, 52, 63, 70, 88, 95, 77, 84];
        $peakViews = max($dailyViews);
        $periodViews = array_sum($dailyViews);

        // The seven ranges are exactly the ones AnalyticsController accepts.
        $ranges = ['7 days', '30 days', '90 days', 'This mo', 'Last mo', 'This yr', 'All'];
        $activeRange = 1;

        // The four device columns of analytics_daily, in period order.
        // "Unknown" is a real column, not a rounding error, so it is shown.
        $deviceRows = [
            ['Mobile', 1109],
            ['Desktop', 559],
            ['Tablet', 71],
            ['Unknown', 18],
        ];

        // The eight source buckets analytics_referrers_daily can hold: five it
        // works out from the referrer, plus three that a link parameter sets.
        $sourceRows = [
            ['Direct', 604, '#34d399'],
            ['Search', 391, '#17b586'],
            ['Social', 328, '#0f9b73'],
            ['Newsletter', 181, '#0c8562'],
            ['Email', 96, '#0a7052'],
            ['Boost', 88, '#085b43'],
            ['Promo', 41, '#064835'],
            ['Other', 28, '#3a4a44'],
        ];

        // One stored row per schedule per day. These are the real columns of
        // analytics_daily, and there is not a fifth one hiding off the edge.
        $storedRows = [
            ['Jul 28', 27, 53, 3, 1],
            ['Jul 27', 24, 49, 3, 1],
            ['Jul 26', 30, 60, 4, 1],
        ];

        // One stored row per schedule per day per source per domain.
        $sourceStoredRows = [
            ['Jul 28', 'direct', '-', 38],
            ['Jul 28', 'search', 'google.com', 19],
            ['Jul 28', 'social', 'instagram.com', 14],
        ];

        $faqs = [
            [
                'q' => 'Is analytics included on the free plan?',
                'a' => 'Yes. Built-in analytics is free on every plan, including selfhosted. Views by day, week or month, the device split, all eight traffic-source buckets, referrer domains, UTM values, country-level locations, social link clicks, top events and the per-schedule split are all on the free plan. The Revenue and Check-ins tabs are the exception, because they measure ticket sales, and ticketing is on the Pro plan at five dollars a month with zero platform fees.',
            ],
            [
                'q' => 'Do you send my visitors to Google Analytics or any other tracker?',
                'a' => 'No. The app counts views itself, into its own tables in its own database. No third-party analytics service is contacted, no analytics script from another company runs on your schedule page, and counting a view does not set a tracking cookie. On a selfhosted install the numbers never leave your own server, including the country lookup, which reads a database file that ships with the app.',
            ],
            [
                'q' => 'Can I see who visited my schedule?',
                'a' => 'No, and that is deliberate. A stored row is a schedule, a date and a set of counters, so there is no name, no email, no session and no page-by-page trail to look up. The IP address is never written down. It is hashed with a salt that changes at midnight, and that hash sits in the cache only so the same person is not counted twenty times; the address itself is read once against the country file that ships with the app, and then it is gone. If you want to reach the people who look you up, ask them to follow your schedule: followers give you their name and email on purpose, and you can email them from the newsletter tool.',
            ],
            [
                'q' => 'How accurate are the numbers?',
                'a' => 'They lean conservative on purpose. Over a hundred known crawler signatures are dropped before anything is counted, along with requests that do not behave like a browser, such as one that sends no Accept-Language header. The same visitor counts at most ten views per schedule per day. Your own visits as the owner or a team member are not counted, and views through an embedded calendar are not counted either. So the figure you read is a floor, not a boast.',
            ],
            [
                'q' => 'How precise are visitor locations?',
                'a' => 'Country only. The lookup file the app ships with resolves an IP to a country, and that is the whole of what gets stored: a schedule, a date, a two-letter country code and a count. There is no city, no region and no map pin.',
            ],
            [
                'q' => 'Can I look at one schedule, or one event, on its own?',
                'a' => 'Yes. Pick a schedule to see just its numbers, or leave it on all of them for a combined view with a per-schedule split. With a schedule selected you can narrow further to a single event and read that event\'s views on their own. Talent and venue schedules also get appearance views: the traffic your events pulled while listed on somebody else\'s schedule.',
            ],
        ];

        $dotSections = [
            ['top', 'The panel'],
            ['intake', 'The intake'],
            ['panel', 'The instruments'],
            ['record', 'One row'],
            ['sources', 'Where they came in'],
            ['money', 'Money and doors'],
            ['rest', 'Everything else'],
            ['faq', 'Questions'],
            ['claim', 'Switch it on'],
        ];
    @endphp

    <div id="es-dash-page" class="es-dash-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the instrument cluster                              -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(4, 120, 87, 0.2), rgba(4, 120, 87, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(52, 211, 153, 0.14), rgba(52, 211, 153, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <span class="es-dash-pip" aria-hidden="true"></span>
                        <span class="es-dash-muted text-sm font-medium tracking-wide">Built-in analytics, free on every plan</span>
                    </div>

                    <h1 class="es-balance es-dash-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Read the panel.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-dash-accent">Not the person.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-dash-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A visit to your schedule moves a counter: which day, which kind of device, which source, which country. It never writes down who. That is not a privacy setting you switch on, it is the shape of the data.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-dash-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.analytics') }}" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Analytics guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The cluster. A lit well: identical in light and dark, because
                     a gauge does not repaint when the room lights change. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-dash-well relative overflow-hidden p-6 sm:p-7">
                        <div class="es-dash-sweep" aria-hidden="true"></div>

                        <div class="relative">
                            <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
                                <div>
                                    <p class="es-dash-dim mb-1 es-dash-label">Total views, all time</p>
                                    <span class="es-dash-readout es-od text-4xl sm:text-5xl" data-odometer="12,480">12,480</span>
                                </div>
                                <div class="text-end">
                                    <p class="es-dash-dim mb-1 es-dash-label">Selected range</p>
                                    <span class="es-dash-readout text-2xl">{{ number_format($periodViews) }}</span>
                                    <span class="es-dash-lit ms-1 es-dash-num text-xs">+18.4%</span>
                                </div>
                            </div>

                            <!-- Seven detents, one per range the dashboard accepts. -->
                            <div class="es-dash-dial mb-4" aria-hidden="true">
                                @foreach ($ranges as $ri => $rLabel)
                                    <span class="es-dash-detent @if ($ri === $activeRange) es-dash-detent-on @endif">{{ $rLabel }}</span>
                                @endforeach
                            </div>

                            <!-- Thirty days, one column each. -->
                            <div class="flex h-24 items-end gap-[2px]" aria-hidden="true">
                                @foreach ($dailyViews as $di => $dv)
                                    <span class="es-bar es-dash-col" style="height: {{ round($dv / $peakViews * 100) }}%; --bd: {{ 0.25 + $di * 0.018 }}s;"></span>
                                @endforeach
                            </div>
                            <div class="es-dash-ticks mt-1.5" aria-hidden="true"></div>
                            <p class="es-dash-dim mt-1.5 es-dash-label">Daily views, last 30 days</p>

                            <div class="es-dash-hair mt-5 space-y-2.5 border-t pt-4">
                                <p class="es-dash-dim es-dash-label">Devices in range</p>
                                @foreach ($deviceRows as [$dName, $dCount])
                                    <div class="flex items-center gap-3">
                                        <span class="es-dash-fmuted es-dash-num w-20 shrink-0 text-xs">{{ $dName }}</span>
                                        <span class="es-dash-meter flex-1" aria-hidden="true">
                                            <span class="es-dash-meter-fill" style="inset-inline-start: 0; width: {{ round($dCount / $periodViews * 100) }}%;"></span>
                                            <span class="es-dash-meter-scale"></span>
                                        </span>
                                        <span class="es-dash-readout w-12 shrink-0 text-end text-xs">{{ number_format($dCount) }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-dash-dim mt-4 es-dash-fine">
                                Sample schedule. Four device counters, because the stored row has four: desktop, mobile, tablet and unknown.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- What the panel can be asked -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-4xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Page views', 'Devices', 'Traffic sources', 'Referrer domains', 'UTM tags', 'Countries', 'Top events', 'Social clicks', 'Per schedule', 'Appearances'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-dash-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The intake: three gates before a counter moves            -->
    <!-- ============================================================ -->
    <section id="intake" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-dash-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(52, 211, 153, 0.12), rgba(52, 211, 153, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-dash-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-dash-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The intake</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Three gates before the needle <span class="es-dash-lit">moves.</span>
                    </h2>
                    <p class="es-dash-fmuted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A raw hit count is a vanity number. Everything below runs on every request, in this order, before anything is added up.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-dash-card es-dash-rail p-6" data-reveal="panel">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="es-dash-lit es-dash-num text-xs">01</span>
                            <p class="es-dash-tag">Is it a browser?</p>
                        </div>
                        <h3 class="es-dash-fink mb-2 text-lg font-bold">
                            <span data-count-to="110">110</span> crawler signatures
                        </h3>
                        <p class="es-dash-fmuted text-sm">Search crawlers, SEO tools, link unfurlers and AI scrapers are matched by name and dropped. A request with no user agent at all is treated the same way, because real browsers always send one.</p>
                    </div>
                    <div class="es-dash-card es-dash-rail p-6" data-reveal="panel">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="es-dash-lit es-dash-num text-xs">02</span>
                            <p class="es-dash-tag">Does it act like one?</p>
                        </div>
                        <h3 class="es-dash-fink mb-2 text-lg font-bold">Two header checks</h3>
                        <p class="es-dash-fmuted text-sm">Every real browser sends an Accept-Language header and asks for specific content types. A request with no Accept-Language, or one that will accept any content type rather than a specific one, does not count.</p>
                    </div>
                    <div class="es-dash-card es-dash-rail p-6" data-reveal="panel">
                        <div class="mb-3 flex items-center gap-2">
                            <span class="es-dash-lit es-dash-num text-xs">03</span>
                            <p class="es-dash-tag">Counted already today?</p>
                        </div>
                        <h3 class="es-dash-fink mb-2 text-lg font-bold">
                            <span data-count-to="10">10</span> views a day, per visitor
                        </h3>
                        <p class="es-dash-fmuted text-sm">The same visitor tops out at ten counted views per schedule per day. The tally is keyed on a hash and expires at midnight, so it resets on its own.</p>
                    </div>
                </div>

                <div class="es-dash-card mx-auto mt-8 max-w-3xl p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="es-dash-pip" aria-hidden="true"></span>
                        <h3 class="es-dash-fink text-lg font-bold">Then the counter moves</h3>
                        <span class="es-dash-plan">Free</span>
                    </div>
                    <p class="es-dash-fmuted text-sm leading-relaxed">
                        One counter goes up on the schedule's row for today, and if the visit was to an event page, one on that event's row too. What makes the dedup work is a hash of the IP address salted with the date, so it changes every midnight and cannot be walked back to an address. The hash lives in the cache, never on the row. Your own visits as the owner or a team member are not counted, and neither are views through an embedded calendar.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The instruments                                           -->
    <!-- ============================================================ -->
    <section id="panel" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-dash-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-dash-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The instruments</p>
                <h2 class="es-balance es-dash-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Nine dials, and a <span class="es-dash-accent">dial for the range.</span>
                </h2>
                <p class="es-dash-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every reading below is on the free plan, on the tab that opens first.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Views over time', 'A line for the range you picked, grouped daily, weekly or monthly. Weekly is the one that stops a single quiet Tuesday from looking like a trend.'],
                    ['Devices', 'Desktop, mobile, tablet and unknown, worked out from the user agent. If two thirds of your traffic is a phone, your listing had better read well on a phone.'],
                    ['Traffic sources', 'Eight buckets: direct, search, social, email and other, plus newsletter, boost and promo when the link you shared says so.'],
                    ['Referrer domains', 'The top ten domains sending you traffic, by name. Useful for finding the local listings site you did not know was carrying you.'],
                    ['UTM tags', 'Top ten values for source, medium and campaign, each counted separately, so a poster QR code and a Facebook post can be told apart.'],
                    ['Visitor countries', 'A country-level split of where the views came from, resolved from a lookup file the app ships with. Country only, never a city.'],
                    ['Top events', 'Your ten most-viewed events for the range. This is the reading that tells you which show the interest is actually landing on.'],
                    ['Views per schedule', 'Run more than one schedule and each gets its own bar with its total for the range, so you can see which one is carrying the other.'],
                    ['Social link clicks', 'Clicks on the social links in your schedule settings, split by platform. Clicks leaving your page, counted the same careful way as views arriving.'],
                ] as [$iName, $iBody])
                    <div class="es-dash-card flex h-full flex-col p-6" data-reveal="panel">
                        <h3 class="es-dash-ink mb-3 text-lg font-bold">{{ $iName }}</h3>
                        <p class="es-dash-muted text-sm leading-relaxed">{{ $iBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-dash-card mt-8 p-6 sm:p-8" data-reveal="panel">
                <div class="grid items-center gap-8 lg:grid-cols-[1.1fr_1fr]">
                    <div>
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-dash-ink text-lg font-bold">The tenth dial is the range</h3>
                            <span class="es-dash-plan">Free</span>
                        </div>
                        <p class="es-dash-muted text-sm leading-relaxed">
                            Seven positions, and every reading on the page follows the one you pick: last 7 days, last 30, last 90, this month, last month, this year, all time. Pick a month that has closed and the comparison figure holds still instead of drifting under you. Every range except all time also shows the change against the stretch before it: the previous 7, 30 or 90 days, the month before, or last year.
                        </p>
                    </div>
                    <div class="es-dash-well relative overflow-hidden p-5">
                        <div class="es-dash-dial" aria-hidden="true">
                            @foreach ($ranges as $ri => $rLabel)
                                <span class="es-dash-detent @if ($ri === 3) es-dash-detent-on @endif">{{ $rLabel }}</span>
                            @endforeach
                        </div>
                        <div class="es-dash-hair mt-4 flex items-end justify-between gap-4 border-t pt-4">
                            <div>
                                <p class="es-dash-dim mb-1 es-dash-label">This month</p>
                                <span class="es-dash-readout text-3xl">1,043</span>
                            </div>
                            <div class="text-end">
                                <p class="es-dash-dim mb-1 es-dash-label">Vs previous</p>
                                <span class="es-dash-readout text-xl">+9.1%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. One row: the record itself                                -->
    <!-- ============================================================ -->
    <section id="record" class="es-dash-hair scroll-mt-24 border-y py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-dash-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-dash-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">One row</p>
                <h2 class="es-balance es-dash-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    This is the whole record. <span class="es-dash-accent">One row a day.</span>
                </h2>
                <p class="es-dash-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Most analytics keeps a line per visitor and adds it up later. This keeps the total and never keeps the line. Here is a schedule's last three days, column for column.
                </p>
            </div>

            <div class="es-dash-card p-6 sm:p-8" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-dash-table text-left">
                        <caption class="es-dash-muted mb-4 text-start text-xs">Three days of a schedule's stored view counters. Four device columns, one date, one schedule, and nothing else. The total is added up when you read it, not stored.</caption>
                        <thead>
                            <tr class="es-dash-tag">
                                <th scope="col" class="pb-3 font-bold">Day</th>
                                <th scope="col" class="pb-3 text-end font-bold">Desktop</th>
                                <th scope="col" class="pb-3 text-end font-bold">Mobile</th>
                                <th scope="col" class="pb-3 text-end font-bold">Tablet</th>
                                <th scope="col" class="pb-3 text-end font-bold">Unknown</th>
                                <th scope="col" class="pb-3 text-end font-bold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($storedRows as [$rDay, $rDesk, $rMob, $rTab, $rUnk])
                                <tr class="es-dash-hair border-t">
                                    <th scope="row" class="es-dash-ink es-dash-num py-3 pe-3 text-sm font-bold">{{ $rDay }}</th>
                                    <td class="es-dash-muted es-dash-num py-3 text-end text-sm">{{ $rDesk }}</td>
                                    <td class="es-dash-muted es-dash-num py-3 text-end text-sm">{{ $rMob }}</td>
                                    <td class="es-dash-muted es-dash-num py-3 text-end text-sm">{{ $rTab }}</td>
                                    <td class="es-dash-muted es-dash-num py-3 text-end text-sm">{{ $rUnk }}</td>
                                    <td class="es-dash-accent es-dash-num py-3 text-end text-sm font-bold">{{ $rDesk + $rMob + $rTab + $rUnk }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="es-dash-muted es-dash-hair mt-6 border-t pt-5 text-sm leading-relaxed">
                    There is no visitor column, no session, no name, no email and no page-by-page trail, so the question "who was that" has nowhere to land. Sources are stored the same way, one line per day per bucket, and a source line holds a domain at most.
                </p>

                <div class="mt-5 overflow-x-auto">
                    <table class="es-dash-table text-left">
                        <caption class="es-dash-muted mb-4 text-start text-xs">Three of the matching source rows for the same day.</caption>
                        <thead>
                            <tr class="es-dash-tag">
                                <th scope="col" class="pb-3 font-bold">Day</th>
                                <th scope="col" class="pb-3 font-bold">Source</th>
                                <th scope="col" class="pb-3 font-bold">Domain</th>
                                <th scope="col" class="pb-3 text-end font-bold">Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sourceStoredRows as [$sDay, $sSource, $sDomain, $sViews])
                                <tr class="es-dash-hair border-t">
                                    <th scope="row" class="es-dash-ink es-dash-num py-3 pe-3 text-sm font-bold">{{ $sDay }}</th>
                                    <td class="es-dash-muted es-dash-num py-3 pe-3 text-sm">{{ $sSource }}</td>
                                    <td class="es-dash-muted es-dash-num py-3 pe-3 text-sm">{{ $sDomain }}</td>
                                    <td class="es-dash-accent es-dash-num py-3 text-end text-sm font-bold">{{ $sViews }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="es-dash-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Want names? Ask for them. People who follow your schedule hand over a name and an email on purpose, and you can write to them from the newsletter tool: ten emails a month on Free, a hundred on Pro, a thousand on Enterprise, counted per recipient.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Where they came in                                        -->
    <!-- ============================================================ -->
    <section id="sources" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-dash-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-dash-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Where they came in</p>
                    <h2 class="es-balance es-dash-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Eight doors, and you know <span class="es-dash-accent">which one.</span>
                    </h2>
                    <p class="es-dash-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Five of the buckets are worked out from where the visit arrived from: direct, search, social, email, and other for everything else. Three are set by the link itself, so a newsletter, a boosted post and a promo code link land in their own bucket rather than blurring into social.
                    </p>
                    <ul class="es-dash-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <span class="mt-2 es-dash-pip flex-none" aria-hidden="true"></span>
                            <span>A link tag beats guesswork: if the link says newsletter or boost, that wins over the referring site.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <span class="mt-2 es-dash-pip flex-none" aria-hidden="true"></span>
                            <span>Visits from your own pages are read as direct rather than counted as a referral to yourself, custom domain included.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <span class="mt-2 es-dash-pip flex-none" aria-hidden="true"></span>
                            <span>Under the buckets sit the top ten referrer domains by name, and the top ten UTM source, medium and campaign values.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-dash-well relative overflow-hidden p-6 sm:p-7">
                        <p class="es-dash-dim mb-3 es-dash-label">Sources, selected range</p>
                        <div class="es-dash-src" aria-hidden="true">
                            @foreach ($sourceRows as [$sName2, $sCount, $sColor])
                                <span class="es-dash-seg" style="width: {{ round($sCount / $periodViews * 100, 2) }}%; background: {{ $sColor }};"></span>
                            @endforeach
                        </div>
                        <div class="es-dash-ticks mt-1.5" aria-hidden="true"></div>

                        <div class="es-dash-hair mt-5 grid grid-cols-2 gap-x-6 gap-y-3 border-t pt-4">
                            @foreach ($sourceRows as [$sName2, $sCount, $sColor])
                                <div class="flex items-baseline gap-2">
                                    <span class="mt-1 h-2 w-2 flex-none rounded-sm" style="background: {{ $sColor }};" aria-hidden="true"></span>
                                    <span class="es-dash-fmuted es-dash-num min-w-0 flex-1 truncate text-xs">{{ $sName2 }}</span>
                                    <span class="es-dash-readout text-xs">{{ number_format($sCount) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-dash-dim mt-4 es-dash-fine">
                            Sample schedule. Three of these eight only appear because the link carried a tag.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Money and doors (fixed-dark band)                         -->
    <!-- ============================================================ -->
    <section id="money" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-dash-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 34%, rgba(4, 120, 87, 0.22), rgba(4, 120, 87, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-dash-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-dash-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Money and doors</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Two more tabs, once you are <span class="es-dash-lit">selling tickets.</span>
                    </h2>
                    <p class="es-dash-fmuted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Views are the free half of the story. Sales and arrivals are the other half. Both tabs are there on every plan, but they only have numbers in them once there are ticket sales to count, and ticketing is on the Pro plan.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                    <div class="es-dash-card flex h-full flex-col p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-dash-fink text-xl font-bold">Revenue</h3>
                            <span class="es-dash-plan es-dash-plan-pro">Pro</span>
                        </div>
                        <p class="es-dash-fmuted mb-4 text-sm leading-relaxed">
                            Revenue for the range, split by currency when you sell in more than one. Conversion rate is paid sales against views. Revenue per view is shown only when the money is all in one currency, because averaging two currencies together would be a lie.
                        </p>
                        <ul class="es-dash-fmuted mt-auto space-y-2 text-sm">
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>Discount given away through promo codes, and a line per code.</span></li>
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>Your ten highest-earning events for the range.</span></li>
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>Boost funnel: spend, impressions, clicks, views and sales in one row.</span></li>
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>Newsletter funnel: sent, opens, clicks, views and sales.</span></li>
                        </ul>
                    </div>

                    <div class="es-dash-card flex h-full flex-col p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-dash-fink text-xl font-bold">Check-ins</h3>
                            <span class="es-dash-plan es-dash-plan-pro">Pro</span>
                        </div>
                        <p class="es-dash-fmuted mb-4 text-sm leading-relaxed">
                            The gap between sold and turned up, which is the number nobody has and everybody wants. Scanned codes on the door become an attendance rate, and the shortfall is stated as a no-show rate rather than left for you to subtract.
                        </p>
                        <ul class="es-dash-fmuted mt-auto space-y-2 text-sm">
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>Tickets sold and tickets checked in, for the range.</span></li>
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>Arrival times by hour, in the schedule's own timezone.</span></li>
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>Attendance per ticket type, so the cheap tier can be compared with the good one.</span></li>
                            <li class="flex gap-2"><span class="es-dash-lit flex-none">/</span><span>A table with a row per event: sold, checked in, rate.</span></li>
                        </ul>
                    </div>
                </div>

                <div class="es-dash-well relative mx-auto mt-8 max-w-3xl overflow-hidden p-6" data-reveal="panel">
                    <p class="es-dash-dim mb-4 es-dash-label">Newsletter funnel, one send</p>
                    <div class="space-y-2.5">
                        @foreach ([['Sent', 420, 100], ['Opened', 214, 51], ['Clicked', 63, 15], ['Views', 58, 14], ['Sales', 11, 3]] as [$fName, $fVal, $fPct])
                            <div class="flex items-center gap-3">
                                <span class="es-dash-fmuted es-dash-num w-16 shrink-0 text-xs">{{ $fName }}</span>
                                <span class="es-dash-meter flex-1" aria-hidden="true">
                                    <span class="es-dash-meter-fill" style="inset-inline-start: 0; width: {{ $fPct }}%;"></span>
                                    <span class="es-dash-meter-scale"></span>
                                </span>
                                <span class="es-dash-readout w-10 shrink-0 text-end text-xs">{{ $fVal }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-dash-dim mt-4 es-dash-fine">
                        Sample send. Newsletters themselves are free, at ten emails a month; the sales row at the bottom is the part that needs ticketing.
                    </p>
                </div>

                <p class="es-dash-fmuted mt-10 text-center"  data-reveal>
                    Zero platform fees on anything you sell.
                    <a href="{{ marketing_url('/features/ticketing') }}" class="es-dash-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        How ticketing works
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-dash-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-dash-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-dash-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the panel.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-dash-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-dash-ink text-xl font-bold">Narrow it to one thing</h3>
                                <span class="es-dash-plan">Free</span>
                            </div>
                            <p class="es-dash-muted mb-4">Read all your schedules together, or pick one. With one picked you can narrow again to a single event and read that event's views on their own, which is how you find out whether the poster or the venue was doing the work.</p>
                            <p class="es-dash-muted text-sm">Leave it on all schedules and you also get a per-schedule split, so a quiet second schedule is visible rather than absorbed.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-dash-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-dash-ink text-xl font-bold">Views you earned elsewhere</h3>
                                <span class="es-dash-plan">Free</span>
                            </div>
                            <p class="es-dash-muted">Talent and venue schedules get appearance views: the traffic your events pulled while listed on somebody else's schedule, plus which of those schedules sent the most.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-dash-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-dash-ink text-xl font-bold">Campaigns on the same line</h3>
                                <span class="es-dash-plan">Free</span>
                            </div>
                            <p class="es-dash-muted">Send a newsletter or run a boost and the views chart gains a second dashed line for the views that campaign brought in, so a spike has an explanation instead of a shrug.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-dash-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-dash-ink text-xl font-bold">Nothing phones home</h3>
                                <span class="es-dash-plan">Free</span>
                            </div>
                            <p class="es-dash-muted mb-4">There is no third-party analytics service behind this and no analytics script from another company on your schedule page. Counting a view does not set a tracking cookie either: the dedup key is a hash that expires at midnight.</p>
                            <p class="es-dash-muted text-sm">
                                Selfhost it and the numbers never leave your own database. The country lookup reads a file that ships with the app, so even that is a local read.
                                <a href="{{ marketing_url('/selfhost') }}" class="es-dash-link font-medium hover:underline">About selfhosting</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-dash-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-dash-ink text-xl font-bold">Reach, in countries</h3>
                                <span class="es-dash-plan">Free</span>
                            </div>
                            <p class="es-dash-muted">Ten countries, ranked, with a count each. Enough to know whether you are playing to your own town or to a diaspora, and not one field more.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-dash-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-dash-ink text-xl font-bold">Where they went next</h3>
                                <span class="es-dash-plan">Free</span>
                            </div>
                            <p class="es-dash-muted mb-4">Every social link in your schedule settings is counted when somebody follows it out, split by platform, and behind the same crawler filter and daily cap as a page view. It is the one reading that tells you which channel your audience actually uses.</p>
                            <p class="es-dash-muted text-sm">What it is not: there is no share counter, and no way to see what somebody did after they left. Follow-outs, not footsteps.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="es-dash-hair scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-dash-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps
                </h2>
                <p class="es-dash-muted mt-4 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    There is nothing to install and no tag to paste. The panel is already wired.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Publish and share the link', 'The counters start at zero and move on the first real visit. Nothing to switch on, nothing to configure.'],
                    ['02', 'Tag the links you post', 'Add utm_source, utm_medium or utm_campaign to a link and its values show up in the UTM lists, so a poster QR code and a Facebook post can be told apart. Newsletter and boost links are tagged for you.'],
                    ['03', 'Pick a range and read it', 'Open Analytics, turn the range dial, and read which day, which device, which door and which event.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-dash-card p-7" data-reveal="panel">
                        <div class="es-dash-accent es-dash-num mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-dash-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-dash-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-dash-hair border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-dash-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who followed you, then read the opens and clicks" :url="marketing_url('/features/newsletters')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell with zero platform fees, and unlock the Revenue and Check-ins tabs" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Event Boosting" description="Run an ad, then read the spend against the views and sales it brought" :url="marketing_url('/features/boost')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Repeat weekly on chosen days, with per-occurrence tickets" :url="marketing_url('/features/recurring-events')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-dash-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-dash-hair border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-dash-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues'], ['/for-curators', 'Curators'], ['/for-bars', 'Bars'], ['/for-musicians', 'Musicians']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-dash-hover es-dash-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-dash-hover-title es-dash-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-dash-hover-arrow es-dash-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-dash-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-dash-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-dash-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-dash-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask once they realise the panel is already running.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-dash-hover es-dash-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-dash-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-dash-accent es-dash-num flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-dash-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-dash-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-dash-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-dash-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-dash-tag mb-4">Free on every plan</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your panel is wired. It just <span class="es-dash-lit">reads zero.</span>
                    </h2>
                    <p class="es-dash-fmuted mx-auto mb-8 max-w-2xl text-lg">
                        Take a name, share the link, and watch the first counter move. No tag to paste, no third party, nothing to switch on.
                    </p>

                    <!-- The cold panel: the same instrument as the hero, before a single visit.
                         Every detent off, the odometer at zero, the scale flat. -->
                    <div class="es-dash-well relative mx-auto mb-10 max-w-md overflow-hidden p-5 text-start">
                        <div class="es-dash-sweep" aria-hidden="true"></div>
                        <div class="relative">
                            <p class="es-dash-dim mb-1 es-dash-label">Total views, all time</p>
                            <span class="es-dash-readout text-4xl">000,000</span>
                            <div class="es-dash-dial mt-4" aria-hidden="true">
                                @foreach ($ranges as $rLabel)
                                    <span class="es-dash-detent">{{ $rLabel }}</span>
                                @endforeach
                            </div>
                            <div class="es-dash-ticks mt-3" aria-hidden="true"></div>
                            <p class="es-dash-dim mt-2 es-dash-fine">
                                A new panel, before anything has visited. Nothing counted yet, and no visitor column waiting to be filled in.
                            </p>
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-dash-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-dash-fmuted mt-6 text-sm">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 es-dash-tip dark:text-gray-300">{{ $sectionLabel }}</span>
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
