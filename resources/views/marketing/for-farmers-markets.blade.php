<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Farmers Markets | Vendor Calendar</x-slot>
    <x-slot name="description">Grow your market. Share market days, vendor lineups, and seasonal events. Email your community directly - no algorithm. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Farmers Markets</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Farmers Markets",
        "description": "Set the whole season out as one recurring market day with a closing date, take a washed-out Saturday back off the calendar, and let traders put themselves forward for a pitch.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Farmers Markets & Outdoor Markets"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Farmers Markets",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Farmers Market Event Management Software",
        "operatingSystem": "Web",
        "description": "Grow your market. Share market days, vendor lineups, and seasonal events. Email your community directly. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "A whole season as one recurring market day, ending on a closing date or after a set number of markets",
            "Date exceptions that take a washed-out Saturday off the calendar, and put a one-off date back on",
            "A second market day, such as a midweek evening, as its own event with its own hours",
            "Traders submitting themselves for a market day, with every submission waiting for your approval",
            "Named regular traders whose submissions are approved automatically",
            "Sub-schedules that keep produce, bakery, flowers and the winter market on their own strands of one link",
            "An agenda on each market day for demos, music and workshops",
            "Free RSVP with a places limit, counted per market date",
            "Pitch fees sold as ticket types, with stock counted per market date and free QR scanning at the gate",
            "Zero platform fees on sales through your own Stripe account, on every plan",
            "A downloadable QR code that puts your market page in a shopper's hand, one tap from following",
            "Newsletters you write and send to the people who follow the market",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "An embeddable calendar for the website you already have"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "farmers market calendar, market vendor schedule, farmers market events, outdoor market management, free farmers market scheduling",
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
           For-farmers-markets "The Market" styles.

           CONCEPT: a market is not an event. It is a season of the same
           morning - the vans in the square at half six, open at eight,
           swept by two - repeated every Saturday from opening day to
           closing day. So the page is built out of a SEASON OF MONTHS OF
           SATURDAYS, a PITCH LIST, and one MARKET MORNING, and each of
           those three is a real product mechanism rather than a picture:

             - The season strip is a recurring event with days_of_week
               and recurring_end_type = 'on_date' (Event.php:1337-1350).
               Months are grouped, and each month's width is its own
               number of Saturdays, so the strip is proportional to the
               year rather than decorative.
             - The hollow Saturday is recurring_exclude_dates
               (Event.php:1320). Guests see that date simply ABSENT -
               there is no cancelled banner and no strike-through, and
               the page says so instead of drawing one.
             - The pitch list is accept_requests + require_approval +
               approved_subdomains ("Events submitted from these
               schedules will be automatically approved"), which is why
               the table has exactly two states and no others.
             - Market morning is event parts (EventPart: name,
               description, start_time, end_time, sort_order), free,
               which is why each line in the mock carries a note.

           COLOUR: the page's existing lime-into-leaf family, kept but
           pulled much darker on light grounds so it can carry text.
           The old page used #84cc16 and #a3e635 as light-mode heading
           stops, which measure 1.38 and 1.20 on this ground. The accent
           is now #3f6212 in light and #a3e635 in dark, and the second
           colour is not a hue at all: it is the linen ground itself.

           NEVER text-gray-500 here. On #f6f5ef it drops to about 4.4.
           Use .es-mkt-muted: #4c5348 = 7.29 on the ground, 7.81 on a
           card, 6.72 on a sub-surface; #a0aa9a = 7.91 dark.

           TWO FIXED PHYSICAL OBJECTS, identical with .dark on and off:
           .es-mkt-band (the square before sunrise) and .es-mkt-board
           (the painted A-board). Shared classes carry their own .dark
           rules in marketing.css and are invisible to a grep of this
           file, so grid-overlay, animate-shimmer and es-claim are
           re-pinned for the band below.

           BLADE: no @supports() probes in this block - a "#" hex inside
           a parenthesised at-rule condition breaks compilation of every
           later parenthesised directive.
           ============================================================== */

        /* --- Ground and ink --------------------------------------- */
        .es-mkt-page { background-color: #f6f5ef; color: #171c15; }
        .dark .es-mkt-page { background-color: #0c110b; color: #e9ede7; }
        .es-mkt-ink { color: #171c15; }
        .dark .es-mkt-ink { color: #e9ede7; }
        .es-mkt-muted { color: #4c5348; }
        .dark .es-mkt-muted { color: #a0aa9a; }
        .es-mkt-accent { color: #3f6212; }
        .dark .es-mkt-accent { color: #a3e635; }
        /* Always-lit accent, for the fixed-dark band in both modes. */
        .es-mkt-lit { color: #a3e635; }

        .es-mkt-grad {
            background-image: linear-gradient(96deg, #365314, #4d7c0f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-mkt-grad,
        .es-mkt-band .es-mkt-grad {
            background-image: linear-gradient(96deg, #bef264, #a3e635);
        }

        .es-mkt-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Surfaces --------------------------------------------- */
        .es-mkt-card {
            background-color: #fdfdfa;
            border: 1px solid rgba(23, 28, 21, 0.13);
            border-radius: 0.75rem;
        }
        .dark .es-mkt-card {
            background-color: #151a14;
            border-color: rgba(233, 237, 231, 0.13);
        }
        .es-mkt-sub {
            background-color: #eeece2;
            border-radius: 0.5rem;
        }
        .dark .es-mkt-sub { background-color: #1b211a; }
        .es-mkt-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-mkt-hover:hover {
            border-color: rgba(63, 98, 18, 0.5);
            box-shadow: 0 12px 30px -20px rgba(23, 28, 21, 0.55);
        }
        .dark .es-mkt-hover:hover {
            border-color: rgba(163, 230, 53, 0.42);
            box-shadow: 0 12px 30px -20px rgba(0, 0, 0, 0.85);
        }

        /* --- THE SEASON ------------------------------------------------
           Months side by side, each month as wide as its own number of
           Saturdays, so the strip is a scale drawing of the season. Each
           slot is one market day, which is one occurrence of ONE event. */
        .es-mkt-season { display: flex; align-items: flex-end; gap: 0.6rem; }
        .es-mkt-month { min-width: 0; }
        .es-mkt-strip { display: flex; gap: 0.14rem; align-items: stretch; }
        .es-mkt-sat {
            flex: 1 1 0;
            min-width: 0;
            height: 2.3rem;
            border-radius: 0.16rem;
            background: #3f6212;
            transform-origin: bottom;
            transition: transform 0.75s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--sd, 0s);
        }
        .dark .es-mkt-sat { background: #a3e635; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-mkt-sat { transform: scaleY(0.14); }
        /* The washed-out Saturday: hollow, because the date is GONE from
           the listing rather than marked up as cancelled. */
        .es-mkt-sat-off {
            background: transparent;
            border: 1px dashed rgba(23, 28, 21, 0.32);
        }
        .dark .es-mkt-sat-off { border-color: rgba(233, 237, 231, 0.3); }
        /* The winter markets: a separate strand, so a lighter fill. */
        .es-mkt-sat-winter { background: rgba(63, 98, 18, 0.42); height: 1.5rem; }
        .dark .es-mkt-sat-winter { background: rgba(163, 230, 53, 0.42); }
        .es-mkt-monthlabel {
            margin-top: 0.35rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #4c5348;
        }
        .dark .es-mkt-monthlabel { color: #a0aa9a; }

        /* --- THE PITCH LIST ----------------------------------------
           A record, so it is a real table. Exactly two states, because
           the product has exactly two: pre-approved, or waiting. */
        .es-mkt-table { width: 100%; border-collapse: collapse; text-align: left; }
        .es-mkt-table th,
        .es-mkt-table td { padding: 0.7rem 0.6rem; vertical-align: middle; }
        .es-mkt-table tbody tr { border-top: 1px solid rgba(23, 28, 21, 0.1); }
        .dark .es-mkt-table tbody tr { border-top-color: rgba(233, 237, 231, 0.1); }
        .es-mkt-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
            border-radius: 999px;
            padding: 0.15rem 0.55rem;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .es-mkt-status-ok { background: #eaf0da; color: #3f6212; }
        .dark .es-mkt-status-ok { background: #1e2a10; color: #a3e635; }
        .es-mkt-status-wait {
            background: transparent;
            border: 1px solid rgba(23, 28, 21, 0.24);
            color: #4c5348;
        }
        .dark .es-mkt-status-wait { border-color: rgba(233, 237, 231, 0.26); color: #a0aa9a; }

        /* --- Eyebrow, numerals, plan tags ------------------------- */
        .es-mkt-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4c5348;
        }
        .dark .es-mkt-tag { color: #a0aa9a; }

        .es-mkt-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(23, 28, 21, 0.18);
            background: #fdfdfa;
            color: #171c15;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.05em;
        }
        .dark .es-mkt-corner { border-color: rgba(233, 237, 231, 0.2); background: #151a14; color: #e9ede7; }
        .es-mkt-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #3f6212;
        }
        .dark .es-mkt-corner::before { background: #a3e635; }

        /* Plan tiers only. Never reuse these for a state badge. */
        .es-mkt-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(23, 28, 21, 0.32);
            color: #4c5348;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-mkt-plan { border-color: rgba(233, 237, 231, 0.3); color: #a0aa9a; }
        .es-mkt-plan-free { border-color: rgba(63, 98, 18, 0.5); color: #3f6212; background: #eaf0da; }
        .dark .es-mkt-plan-free { border-color: rgba(163, 230, 53, 0.42); color: #a3e635; background: #1e2a10; }
        .es-mkt-plan-pro { border-color: rgba(23, 28, 21, 0.38); color: #171c15; }
        .dark .es-mkt-plan-pro { border-color: rgba(233, 237, 231, 0.4); color: #e9ede7; }

        /* --- Chips (hero marquee) --------------------------------- */
        .es-mkt-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 999px;
            border: 1px solid rgba(23, 28, 21, 0.16);
            background: #fdfdfa;
            color: #4c5348;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .dark .es-mkt-chip { border-color: rgba(233, 237, 231, 0.16); background: #151a14; color: #a0aa9a; }

        /* --- Buttons and links ------------------------------------ */
        .es-mkt-btn {
            background-color: #3f6212;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-mkt-btn:hover {
            background-color: #365314;
            transform: translateY(-1px);
            box-shadow: 0 16px 32px -16px rgba(54, 83, 20, 0.85);
        }
        .es-mkt-ghost {
            border: 1px solid rgba(23, 28, 21, 0.22);
            color: #171c15;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-mkt-ghost:hover { border-color: rgba(63, 98, 18, 0.55); background-color: rgba(63, 98, 18, 0.07); }
        .dark .es-mkt-ghost { border-color: rgba(233, 237, 231, 0.24); color: #e9ede7; }
        .dark .es-mkt-ghost:hover { border-color: rgba(163, 230, 53, 0.45); background-color: rgba(163, 230, 53, 0.09); }
        .es-mkt-link { color: #3f6212; }
        .es-mkt-link:hover { text-decoration: underline; }
        .dark .es-mkt-link { color: #a3e635; }

        /* Hairline between sections and inside cards. A page-local class
           rather than an arbitrary Tailwind colour, because no build runs
           during this campaign and a class the bundle has never seen would
           simply not paint. */
        .es-mkt-hr { border-top: 1px solid rgba(23, 28, 21, 0.1); }
        .dark .es-mkt-hr { border-top-color: rgba(233, 237, 231, 0.11); }

        /* Dot-nav tooltip: light values come from utilities, dark from here. */
        .dark .es-mkt-tip {
            border-color: rgba(233, 237, 231, 0.12);
            background-color: #151a14;
            color: #e9ede7;
        }

        /* --- FIXED OBJECT 1: the square before sunrise ---------------
           A resolvable background-color under the gradient, so text over
           it is scored against something real. No .dark variant: this
           band is the same hour of the same morning in both modes. */
        .es-mkt-band {
            background-color: #0b0f0a;
            background-image:
                radial-gradient(130% 90% at 50% 100%, rgba(63, 98, 18, 0.34) 0%, rgba(11, 15, 10, 0) 62%),
                linear-gradient(180deg, #141a12 0%, #0b0f0a 70%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(233, 237, 231, 0.05);
        }
        /* First light coming up off the horizon. Purely a gradient wash. */
        .es-mkt-dawn {
            position: absolute;
            inset: auto 0 0 0;
            height: 55%;
            background: radial-gradient(120% 100% at 50% 100%, rgba(163, 230, 53, 0.13), rgba(163, 230, 53, 0) 70%);
            animation: es-mkt-dawn 14s ease-in-out infinite alternate;
        }
        @keyframes es-mkt-dawn {
            from { opacity: 0.55; }
            to { opacity: 1; }
        }
        /* Everything inside the band is pinned, including the shared
           classes whose .dark rules live in marketing.css. */
        .es-mkt-band .es-mkt-ink { color: #e9ede7; }
        .es-mkt-band .es-mkt-muted { color: #a0aa9a; }
        .es-mkt-band .es-mkt-tag { color: #a3e635; }
        .es-mkt-band .es-mkt-num { color: #a3e635; }
        .es-mkt-band .es-mkt-card { background-color: #161c15; border-color: rgba(233, 237, 231, 0.14); }
        .es-mkt-band .es-mkt-sub { background-color: #161c15; }
        .es-mkt-band .es-mkt-corner { border-color: rgba(233, 237, 231, 0.2); background: #161c15; color: #e9ede7; }
        .es-mkt-band .es-mkt-corner::before { background: #a3e635; }
        .es-mkt-band .es-mkt-plan { border-color: rgba(163, 230, 53, 0.42); color: #a3e635; background: #1e2a10; }
        .es-mkt-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 237, 231, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 237, 231, 0.05) 1px, transparent 1px);
        }
        .es-mkt-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-mkt-band .es-claim:focus-within {
            border-color: rgba(163, 230, 53, 0.75);
            box-shadow: 0 0 0 4px rgba(163, 230, 53, 0.22);
        }

        /* --- FIXED OBJECT 2: the painted A-board ---------------------
           A real sign that stands at the entrance to the square. Same
           board in both colour modes, so it carries its own ink and
           borrows nothing that flips. */
        .es-mkt-board {
            background-color: #12180f;
            background-image: linear-gradient(168deg, #1a2214 0%, #12180f 55%, #0d1209 100%);
            border: 6px solid #2b3520;
            border-radius: 0.5rem;
            box-shadow: 0 22px 40px -26px rgba(23, 28, 21, 0.75), inset 0 1px 0 rgba(233, 237, 231, 0.07);
        }
        .es-mkt-board-ink { color: #eef3e6; }
        .es-mkt-board-muted { color: #a8b39d; }
        .es-mkt-board-tag {
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #bef264;
        }
        .es-mkt-board-rule { height: 1px; background: rgba(233, 237, 231, 0.16); }
        .es-mkt-qr { width: 100%; height: auto; display: block; }

        /* --- Shared chrome that defaults to brand blue ------------- */
        .es-dot:hover .es-dot-pip { background-color: rgba(63, 98, 18, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(163, 230, 53, 0.65); }
        .es-dot.is-active .es-dot-pip { background: #3f6212; }
        .dark .es-dot.is-active .es-dot-pip { background: #a3e635; }
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(63, 98, 18, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(163, 230, 53, 0.11), transparent 60%);
        }

        /* --- Focus rings. No border-radius here: an outline already
               follows the element's own shape. --------------------- */
        #es-mkt-page a:focus-visible,
        #es-mkt-page summary:focus-visible,
        #es-mkt-page button:focus-visible,
        #es-mkt-page input:focus-visible {
            outline: 2px solid #3f6212;
            outline-offset: 3px;
        }
        .dark #es-mkt-page a:focus-visible,
        .dark #es-mkt-page summary:focus-visible,
        .dark #es-mkt-page button:focus-visible,
        .dark #es-mkt-page input:focus-visible {
            outline-color: #a3e635;
        }
        .es-mkt-band a:focus-visible,
        .es-mkt-band summary:focus-visible,
        .es-mkt-band button:focus-visible,
        .es-mkt-band input:focus-visible {
            outline-color: #a3e635 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-mkt-dawn { animation: none !important; opacity: 0.8; }
            .es-mkt-sat { transition: none !important; transform: none !important; }
            .es-mkt-btn:hover { transform: none; }
        }
    </style>

    @php
        // Prices come from config, never hard-coded: an env override used to
        // silently desync the marketing copy from what billing actually charges.
        $proMonthly = (int) config('services.stripe_platform.price_monthly_amount', 9);

        // The free paid-ticket allowance, straight from the setting the app
        // enforces in Role::ticketSaleLimit(). Selling is FREE up to this many
        // paid tickets a month per schedule; Pro removes the ceiling.
        $freeTicketCap = (int) config('usage.ticket_sale_monthly_limit_free', 25);

        // ONE season, written down once. Every figure the page states is
        // derived from this, so the strip and the prose cannot drift apart.
        // Saturdays from opening day to closing day, grouped by month; each
        // month's slot count is what gives it its width in the strip.
        $seasonMonths = [
            ['May', [2, 9, 16, 23, 30]],
            ['Jun', [6, 13, 20, 27]],
            ['Jul', [4, 11, 18, 25]],
            ['Aug', [1, 8, 15, 22, 29]],
            ['Sep', [5, 12, 19, 26]],
            ['Oct', [3, 10, 17, 24, 31]],
        ];
        $rainedOff = 'Aug 8';

        $saturdays = [];
        foreach ($seasonMonths as [$mName, $mDays]) {
            foreach ($mDays as $mDay) {
                $saturdays[] = $mName . ' ' . $mDay;
            }
        }
        $totalSaturdays = count($saturdays);
        $marketDays = $totalSaturdays - 1;
        $openingDay = $saturdays[0];
        $closingDay = $saturdays[$totalSaturdays - 1];
        $winterMarkets = ['Dec 5', 'Dec 12'];

        // The pitch list. Two states only, because approved_subdomains is
        // the ONLY thing that skips require_approval.
        $pitches = [
            ['Hedgerow Farm', 'Produce', true],
            ['Loaf and Crumb', 'Bakery', true],
            ['Ninefold Apiary', 'Honey and preserves', true],
            ['Wolds Cut Flowers', 'Flowers and plants', false],
            ['Salt Marsh Dairy', 'Cheese', false],
            ['Two Rivers Press', 'Cider and juice', true],
        ];

        // Market morning, as event parts on one market day.
        $morning = [
            ['06:30', 'Traders arrive', 'Pitches marked out along the top of the square.'],
            ['08:00', 'Market opens', 'Twenty-two stalls, and the coffee cart by the cross.'],
            ['10:30', 'Chef demo', 'Thirty free places, taken by RSVP, counted on this date alone.'],
            ['12:00', 'Fiddle band', 'Forty minutes on the church steps.'],
            ['13:00', 'Market closes', 'Last hour is when the bakery discounts.'],
            ['14:00', 'Square swept', 'Cleared and handed back to the council.'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for farmers markets?',
                'a' => 'Yes. The whole season is free forever: a recurring market day with a closing date, date exceptions for the Saturdays you lose to weather, sub-schedules for produce, bakery, flowers and the winter market, an agenda on each market day, free RSVP with a places limit, a downloadable QR code that puts your market page in a shopper\'s hand, built-in analytics, two-way Google, Outlook and CalDAV sync, and an embeddable calendar. Newsletters are free too, at ten emails a month counted one per recipient, and go up to a hundred on Pro and a thousand on Enterprise. Selling is free as well, for the first '.$freeTicketCap.' paid tickets a month, with zero platform fees on what you sell; Pro at $'.$proMonthly.' a month takes that ceiling off.',
            ],
            [
                'q' => 'How do I set up a whole market season at once?',
                'a' => 'Create the market as one recurring event, pick the days of the week it runs and the hours, and give the recurrence an end: a closing date, or a number of market days. The season then stops on its own instead of still listing markets in February. A midweek evening market is its own event, because one recurring event has one start time.',
            ],
            [
                'q' => 'What happens when a market day is rained off?',
                'a' => 'Take that single date out of the recurrence as a date exception and it comes off the listing, leaving the rest of the season untouched. Being straight about what shoppers see: the date is simply absent rather than crossed out with a notice, so if you want to explain why, that is a newsletter or a note on the market page. The same panel has an include list that puts a one-off date on, for a bank holiday Monday market.',
            ],
            [
                'q' => 'Can traders put themselves forward for a market day?',
                'a' => 'Yes, on the free plan. Turn on submissions and traders can offer themselves for a date through your market page, with your terms shown on the form. Every submission waits for you to approve it, so nothing appears publicly that you have not agreed to, and you are emailed when new ones are waiting. Name your regulars as approved schedules and their submissions are approved automatically, which leaves you reading only the new ones. On the Pro plan you can add your own questions to that form.',
            ],
            [
                'q' => 'Can I charge for pitches and take the money online?',
                'a' => 'Yes, and the first '.$freeTicketCap.' paid tickets a month are on the free plan. A pitch fee is a named ticket type with its own price and stock, and the stock is counted per market date, so a full Saturday does not stop the following Saturday selling. Scanning the QR code at the gate on market morning is free too. Pro at $'.$proMonthly.' a month lifts the monthly ceiling and adds the live check-in dashboard and your own questions at checkout, such as whether they need power or how long the van is. Sales go through your own Stripe account and Event Schedule charges no platform fee on top.',
            ],
            [
                'q' => 'How do shoppers hear about the market?',
                'a' => 'They follow the market, and you email them. Your schedule has a QR code you can download and print on the A-board, the pitch sign or the tote bags, so somebody standing in front of you is one scan and one tap from following. Nothing is sent automatically: when you have something worth saying, a new trader or the first strawberries, you write a newsletter and send it. You can also embed the calendar on the website you already have, and shoppers can add market days to their own Google, Outlook or Apple calendar.',
            ],
            [
                'q' => 'Can I run a separate winter or evening market?',
                'a' => 'Yes, on every plan. Sub-schedules keep the winter market, the midweek evening market and the craft strand on their own strands of the same link, each with a link that shows only those events, so somebody looking for the December markets is not scrolling through the summer.',
            ],
        ];

        $dotSections = [
            ['top', 'The season'],
            ['season', 'Set it once'],
            ['rain', 'The rain call'],
            ['pitch', 'The pitch list'],
            ['morning', 'Market morning'],
            ['stalls', 'Pitch fees'],
            ['board', 'The A-board'],
            ['who', 'Perfect for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Opening day'],
        ];
    @endphp

    <div id="es-mkt-page" class="es-mkt-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the season, drawn to scale                          -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 30%, rgba(63, 98, 18, 0.24), rgba(63, 98, 18, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 58%, rgba(163, 230, 53, 0.16), rgba(163, 230, 53, 0) 62%); opacity: 0.45;"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
                <div>
                    <p class="es-mkt-tag es-fade-up es-d-1 mb-5">For farmers markets and outdoor markets</p>

                    <h1 class="es-balance es-mkt-ink mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A market is not</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">a Saturday. It is <span class="es-mkt-grad">a season</span>.</span></span>
                    </h1>

                    <p class="es-mkt-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Opening day in May, the same square every Saturday, closing day at the end of
                        October, then two winter markets. That is one recurring event with a closing
                        date, not {{ $totalSaturdays }} entries typed in by hand.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="#season" class="es-mkt-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how a season is set up
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-mkt-btn group inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Create your market's calendar
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The season, to scale: months of Saturdays. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-mkt-card p-6 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-mkt-ink text-lg font-bold">Saturday Market</h2>
                            <span class="es-mkt-muted es-mkt-num text-xs">{{ $openingDay }} to {{ $closingDay }}</span>
                        </div>
                        <p class="es-mkt-muted mb-6 text-sm">Saturdays, 8am to 1pm &middot; {{ $marketDays }} market days &middot; one rained off</p>

                        <div class="es-mkt-season" aria-hidden="true">
                            @foreach ($seasonMonths as [$mName, $mDays])
                                <div class="es-mkt-month" style="flex: {{ count($mDays) }} 1 0;">
                                    <div class="es-mkt-strip">
                                        @foreach ($mDays as $mDay)
                                            <div class="es-mkt-sat @if ($mName . ' ' . $mDay === $rainedOff) es-mkt-sat-off @endif" style="--sd: {{ 0.05 * $loop->parent->index }}s;"></div>
                                        @endforeach
                                    </div>
                                    <p class="es-mkt-monthlabel">{{ $mName }}</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-mkt-muted mt-4 text-[0.7rem]">
                            One slot is one market day. The dashed one is {{ $rainedOff }}, taken out as a date exception.
                        </p>

                        <div class="es-mkt-board-rule my-4" aria-hidden="true"></div>

                        <div class="flex items-end gap-4">
                            <div class="w-20 shrink-0" aria-hidden="true">
                                <div class="es-mkt-strip">
                                    @foreach ($winterMarkets as $wm)
                                        <div class="es-mkt-sat es-mkt-sat-winter"></div>
                                    @endforeach
                                </div>
                                <p class="es-mkt-monthlabel">Dec</p>
                            </div>
                            <p class="es-mkt-muted text-[0.7rem]">
                                {{ implode(' and ', $winterMarkets) }}: the winter market, on its own sub-schedule and its own link.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Market-type marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Produce', 'Bakery', 'Cut Flowers', 'Honey', 'Cheese', 'Preserves', 'Seedlings', 'Craft', 'Street Food', 'Winter Market'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-mkt-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Set it once (01)                                          -->
    <!-- ============================================================ -->
    <section id="season" class="scroll-mt-24 es-mkt-hr py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Set it once</p>
                <h2 class="es-balance es-mkt-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Opening day, closing day, and <span class="es-mkt-grad">every Saturday between</span>.
                </h2>
                <p class="es-mkt-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Three choices turn one event into a whole season, and all three are on the free plan.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['The days it runs', 'Pick the days of the week and the hours. A Saturday market is one tick on one event, not a new entry every week for six months.'],
                    ['The end of the season', 'Give the recurrence a closing date, or a number of market days. This is the setting that makes a season a season instead of a weekly listing that runs forever.'],
                    ['A second market day', 'A midweek evening market is its own event with its own hours, because one recurring event has one start time. Two events, two sets of hours, nothing ambiguous.'],
                ] as [$sT, $sD])
                    <div class="es-mkt-card es-mkt-hover p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-mkt-ink text-lg font-bold">{{ $sT }}</h3>
                            <span class="es-mkt-plan es-mkt-plan-free">Free</span>
                        </div>
                        <p class="es-mkt-muted text-sm">{{ $sD }}</p>
                    </div>
                @endforeach
            </div>

            <p class="es-mkt-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Change the hours once and every market day follows. Put the winter market on its own
                sub-schedule and it keeps its own link without splitting the market into two pages.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The rain call (02)                                        -->
    <!-- ============================================================ -->
    <section id="rain" class="scroll-mt-24 es-mkt-hr py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rain call</p>
                    <h2 class="es-balance es-mkt-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A washed-out Saturday comes <span class="es-mkt-grad">off the calendar</span>.
                    </h2>
                    <p class="es-mkt-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        You call it at six in the morning with wet hands. Take that one date out of the
                        recurrence and it is gone from the listing, and the other {{ $marketDays }} market
                        days are untouched.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['One date, not the season', 'A date exception removes a single occurrence. You are not rebuilding the recurrence and you are not deleting the market.'],
                            ['Dates can go back in, too', 'Next to it is an include list that adds a one-off date that is not on the usual day, for a bank holiday Monday market or a late-night in December.'],
                            ['What shoppers actually see', 'That Saturday is simply absent. There is no cancelled banner and no strike-through, so if the reason matters, send a newsletter or say it on the market page.'],
                        ] as [$rT, $rD])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-mkt-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-mkt-ink font-semibold">{{ $rT }}</span> <span class="es-mkt-muted">- {{ $rD }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-mkt-plan es-mkt-plan-free">Free</span>
                        <span class="es-mkt-muted ms-2 text-sm">Date exceptions are part of recurring events on every plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-mkt-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-mkt-ink text-lg font-bold">August</h3>
                            <span class="es-mkt-muted es-mkt-num text-xs">1 date out</span>
                        </div>

                        <div class="es-mkt-strip" aria-hidden="true">
                            @foreach ($seasonMonths[3][1] as $augDay)
                                <div class="es-mkt-sat @if ('Aug ' . $augDay === $rainedOff) es-mkt-sat-off @endif"></div>
                            @endforeach
                        </div>
                        <div class="mt-1.5 flex" aria-hidden="true">
                            @foreach ($seasonMonths[3][1] as $augDay)
                                <span class="es-mkt-monthlabel es-mkt-num flex-1 text-center">{{ $augDay }}</span>
                            @endforeach
                        </div>

                        <div class="mt-6 space-y-2.5">
                            @foreach ([
                                ['Aug 1', 'Market ran', '22 stalls'],
                                ['Aug 8', 'Rained off', 'date removed'],
                                ['Aug 15', 'Market ran', '24 stalls'],
                            ] as [$aDate, $aWhat, $aNote])
                                <div class="es-mkt-sub flex items-baseline gap-3 p-3.5">
                                    <span class="es-mkt-muted es-mkt-num w-14 shrink-0 text-xs font-bold uppercase">{{ $aDate }}</span>
                                    <span class="es-mkt-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $aWhat }}</span>
                                    <span class="es-mkt-muted es-mkt-num shrink-0 text-xs">{{ $aNote }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-mkt-muted mt-5 es-mkt-hr pt-4 text-xs">
                            The dashed slot is not a cancelled market. It is a date the recurrence no longer produces.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The pitch list (03) - a record, so a real table           -->
    <!-- ============================================================ -->
    <section id="pitch" class="scroll-mt-24 es-mkt-hr py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The pitch list</p>
                <h2 class="es-balance es-mkt-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Your regulars are in. <span class="es-mkt-grad">New traders wait for you</span>.
                </h2>
                <p class="es-mkt-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Turn on submissions and traders can offer themselves for a date through your market
                    page, with your terms on the form. Every one waits for your approval, and you are
                    emailed when new ones are waiting.
                </p>
            </div>

            <div class="es-mkt-card p-6 sm:p-8" data-reveal="panel">
                <table class="es-mkt-table">
                    <caption class="sr-only">This Saturday's pitch list: each trader, the sub-schedule they sit in, and whether their submission is approved automatically or waiting for the market to approve it</caption>
                    <thead>
                        <tr class="es-mkt-tag">
                            <th scope="col">Trader</th>
                            <th scope="col" class="hidden sm:table-cell">Sub-schedule</th>
                            <th scope="col" class="text-end sm:text-start">This Saturday</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pitches as [$pName, $pKind, $pApproved])
                            <tr>
                                <th scope="row" class="es-mkt-ink text-sm font-bold">
                                    {{ $pName }}
                                    <span class="es-mkt-muted block text-[0.65rem] font-normal sm:hidden">{{ $pKind }}</span>
                                </th>
                                <td class="es-mkt-muted hidden text-xs sm:table-cell">{{ $pKind }}</td>
                                <td class="text-end sm:text-start">
                                    @if ($pApproved)
                                        <span class="es-mkt-status es-mkt-status-ok">Pre-approved</span>
                                    @else
                                        <span class="es-mkt-status es-mkt-status-wait">Waiting on you</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-mkt-muted mt-5 es-mkt-hr pt-4 text-xs">
                    Two states, because the product has two. Name a trader as an approved schedule and
                    their submissions go straight on; everybody else waits. Nothing publishes itself.
                </p>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['Your terms on the form', false, 'Write the pitch rules once and they appear on the submission form: insurance, waste, what time the square closes to vehicles.'],
                    ['Ask your own questions', true, 'Add fields to the submission form, so a trader tells you the stall frontage or whether they need power while they are asking.'],
                    ['Sorted as it arrives', false, 'Sub-schedules keep produce, bakery, flowers and craft on separate strands of one link, each with a link that shows only that strand.'],
                ] as [$qT, $qIsPro, $qD])
                    <div class="es-mkt-card es-mkt-hover p-6" data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-mkt-ink text-base font-bold">{{ $qT }}</h3>
                            @if ($qIsPro)
                                <span class="es-mkt-plan es-mkt-plan-pro">Pro</span>
                            @else
                                <span class="es-mkt-plan es-mkt-plan-free">Free</span>
                            @endif
                        </div>
                        <p class="es-mkt-muted text-sm">{{ $qD }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Market morning (04) - fixed dark band                     -->
    <!-- ============================================================ -->
    <section id="morning" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-mkt-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
                <div class="es-mkt-dawn"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Market morning</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Half six, and the vans are <span class="es-mkt-lit">already in the square</span>.
                    </h2>
                    <p class="es-mkt-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        A market day is not one line on a calendar. Give the day an agenda and the demo,
                        the band and the last hour all sit inside the same market day.
                    </p>
                </div>

                <div class="grid gap-10 lg:grid-cols-[1.1fr_1fr] lg:gap-12">
                    <div data-reveal="panel">
                        <ol class="space-y-2.5">
                            @foreach ($morning as [$tTime, $tName, $tNote])
                                <li class="es-mkt-sub flex items-baseline gap-4 p-4">
                                    <span class="es-mkt-num w-12 shrink-0 text-xs font-bold">{{ $tTime }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="es-mkt-ink block text-sm font-semibold">{{ $tName }}</span>
                                        <span class="es-mkt-muted block text-xs">{{ $tNote }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                        <p class="es-mkt-muted mt-4 text-xs">
                            Each line is a part of the market day, with its own name and its own start
                            and finish. One event, one page, the whole morning on it.
                        </p>
                    </div>

                    <div class="grid gap-4" data-reveal-group="110">
                        @foreach ([
                            ['The day has a shape', 'Add parts to a market day for the chef demo, the fiddle band and the kids table. Shoppers read the morning rather than guessing when to turn up.'],
                            ['Places on the demo', 'A free event can take RSVPs with a limit, and the places are counted per market date, so a full demo in July leaves August alone.'],
                            ['One link, all season', 'Embed the calendar on the website you already have, and shoppers can put a market day straight into their own Google, Outlook or Apple calendar.'],
                        ] as [$mT, $mD])
                            <div class="es-mkt-card p-6" data-reveal="panel">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <h3 class="es-mkt-ink text-base font-bold">{{ $mT }}</h3>
                                    <span class="es-mkt-plan es-mkt-plan-free">Free</span>
                                </div>
                                <p class="es-mkt-muted text-sm">{{ $mD }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Pitch fees (05)                                           -->
    <!-- ============================================================ -->
    <section id="stalls" class="scroll-mt-24 es-mkt-hr py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-mkt-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-mkt-ink text-lg font-bold">Pitch fee</h3>
                                <span class="es-mkt-plan es-mkt-plan-free">Free</span>
                            </div>
                            <p class="es-mkt-muted mb-5 text-sm">A named ticket type with a price and a stock, counted per market date.</p>

                            <dl class="space-y-2.5">
                                @foreach ([
                                    ['Price', '$18 a pitch'],
                                    ['Stock', '30 per market day'],
                                    ['This Saturday', '24 taken, 6 left'],
                                    ['Next Saturday', '30 available'],
                                    ['At the gate', 'QR scan on the way in'],
                                ] as [$fK, $fV])
                                    <div class="es-mkt-sub flex items-baseline justify-between gap-3 p-3.5">
                                        <dt class="es-mkt-muted text-xs font-semibold uppercase tracking-wider">{{ $fK }}</dt>
                                        <dd class="es-mkt-ink es-mkt-num text-sm font-semibold">{{ $fV }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            <p class="es-mkt-muted mt-5 es-mkt-hr pt-4 text-xs">
                                A full Saturday does not stop the following Saturday selling. Each market
                                date keeps its own count. The free plan sells {{ $freeTicketCap }} paid
                                tickets a month, so thirty pitches every week is a Pro market.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Pitch fees</p>
                    <h2 class="es-balance es-mkt-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Take the pitch fee online. <span class="es-mkt-grad">We take none of it</span>.
                    </h2>
                    <p class="es-mkt-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        No envelope of notes and no chasing anybody in the car park. Sales run through
                        your own Stripe account, and Event Schedule takes no platform fee on top of what
                        Stripe charges.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Counted per market date', false, 'Thirty pitches means thirty on that date. Sell out on a Saturday in June and the rest of the season is unaffected.'],
                            ['Ask while they pay', true, 'Attach your own questions to the pitch fee: power, van length, insurance number. The answers arrive with the payment instead of in a separate thread.'],
                            ['Scan them in', false, 'Every buyer gets a QR code, and scanning it at the entrance to the square costs nothing. The live check-in dashboard, counting who is in as the morning goes on, is the Pro half.'],
                            ['Sell tickets too, if you need to', false, 'A ticketed cooking class or a harvest supper works the same way, out of the same monthly allowance and with the same zero platform fee.'],
                        ] as [$pT, $pIsPro, $pD])
                            <div class="es-mkt-card es-mkt-hover p-4" data-reveal>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="es-mkt-ink text-sm font-bold">{{ $pT }}</p>
                                    @if ($pIsPro)
                                        <span class="es-mkt-plan es-mkt-plan-pro">Pro</span>
                                    @else
                                        <span class="es-mkt-plan es-mkt-plan-free">Free</span>
                                    @endif
                                </div>
                                <p class="es-mkt-muted mt-1 text-sm">{{ $pD }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-mkt-muted mt-7 text-sm" data-reveal>
                        Selling is free for your first {{ $freeTicketCap }} paid tickets a month, and Pro
                        at ${{ $proMonthly }} a month has no ceiling at all. Publishing the season, taking
                        submissions and free RSVP places never count against it.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The A-board (06) - fixed physical object                   -->
    <!-- ============================================================ -->
    <section id="board" class="scroll-mt-24 es-mkt-hr py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The A-board</p>
                <h2 class="es-balance es-mkt-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The shopper in front of you is <span class="es-mkt-grad">an email address</span>.
                </h2>
                <p class="es-mkt-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    They are standing in your square with a bag of beans in their hand. That is the one
                    moment they will ever be easiest to reach, and it does not need an algorithm.
                </p>
            </div>

            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-14">
                <!-- The board itself. Same painted sign with .dark on or off. -->
                <div class="mx-auto w-full max-w-sm" data-reveal="panel">
                    <div class="es-mkt-board p-6 text-center">
                        <p class="es-mkt-board-tag mb-4">Saturday Market</p>
                        <div class="mx-auto w-40">
                            <svg class="es-mkt-qr" viewBox="0 0 21 21" aria-hidden="true" shape-rendering="crispEdges">
                                <rect x="0" y="0" width="21" height="21" fill="#eef3e6" />
                                @php
                                    // A stand-in code pattern: three finder squares plus a
                                    // deterministic module fill. Filled rects, not an outline
                                    // drawing.
                                    $qrModules = [];
                                    for ($qy = 0; $qy < 21; $qy++) {
                                        for ($qx = 0; $qx < 21; $qx++) {
                                            $inFinder = ($qx < 8 && $qy < 8) || ($qx > 12 && $qy < 8) || ($qx < 8 && $qy > 12);
                                            if ($inFinder) {
                                                continue;
                                            }
                                            if ((($qx * 7 + $qy * 13 + $qx * $qy) % 5) < 2) {
                                                $qrModules[] = [$qx, $qy];
                                            }
                                        }
                                    }
                                    $qrFinders = [[0, 0], [14, 0], [0, 14]];
                                @endphp
                                @foreach ($qrModules as [$qx, $qy])
                                    <rect x="{{ $qx }}" y="{{ $qy }}" width="1" height="1" fill="#12180f" />
                                @endforeach
                                @foreach ($qrFinders as [$fx, $fy])
                                    <rect x="{{ $fx }}" y="{{ $fy }}" width="7" height="7" fill="#12180f" />
                                    <rect x="{{ $fx + 1 }}" y="{{ $fy + 1 }}" width="5" height="5" fill="#eef3e6" />
                                    <rect x="{{ $fx + 2 }}" y="{{ $fy + 2 }}" width="3" height="3" fill="#12180f" />
                                @endforeach
                            </svg>
                        </div>
                        <p class="es-mkt-board-ink mt-4 text-sm font-bold uppercase tracking-wide">Scan for this week's stalls</p>
                        <div class="es-mkt-board-rule my-4" aria-hidden="true"></div>
                        <p class="es-mkt-board-muted es-mkt-num text-xs">your-market.eventschedule.com</p>
                        <p class="es-mkt-board-muted mt-3 text-[0.65rem]">Download the code from your schedule and print it once.</p>
                    </div>
                </div>

                <div>
                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Print the code once', 'Every schedule has a QR code you can download as an image and put on the A-board, the pitch signs or the tote bags. One scan opens your market page, and Follow is a tap from there.'],
                            ['You write the email', 'Nothing goes out on its own. When there is something worth saying, a new cheesemaker or the first strawberries, you write a newsletter and send it.'],
                            ['Ten a month, free', 'The free plan covers ten newsletter emails a month, counted one per recipient. Pro is a hundred and Enterprise is a thousand.'],
                            ['No algorithm in the middle', 'A market page and an email list are yours. Nobody decides how many of your shoppers get to see that you are open this week.'],
                        ] as [$bT, $bD])
                            <div class="es-mkt-card es-mkt-hover p-5" data-reveal>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="es-mkt-ink text-base font-bold">{{ $bT }}</h3>
                                    <span class="es-mkt-plan es-mkt-plan-free">Free</span>
                                </div>
                                <p class="es-mkt-muted mt-1 text-sm">{{ $bD }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-mkt-muted mt-6 text-sm" data-reveal>
                        Being straight about it: following does not trigger anything by itself. There is
                        no automatic notice when you add a market day, and that is deliberate. The list
                        is yours to write to, in your own words, when you have something to say.
                        <a href="{{ marketing_url('/features/newsletters') }}" class="es-mkt-link font-semibold">How newsletters work</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for (07)                                          -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-mkt-hr py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Perfect for</p>
                <h2 class="es-balance es-mkt-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Any market that comes back <span class="es-mkt-grad">next week</span>.
                </h2>
                <p class="es-mkt-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    From a weekly square to a two-weekend Christmas market, the season is the same shape.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Weekly Farmers Markets"
                    description="One recurring Saturday from opening day to closing day, with the traders who turn up every week pre-approved and the new ones waiting for you."
                    icon-color="lime"
                    blog-slug="for-weekly-farmers-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-6 3-9 8-9-1 6-4 9-8 9zm0 0c0-6-3-9-8-9 1 6 4 9 8 9zm0 0V9" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Artisan & Craft Markets"
                    description="Potters, printers and jewellers on their own sub-schedule, each with a link that shows only the craft strand of the market."
                    icon-color="orange"
                    blog-slug="for-artisan-craft-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Flea Markets & Swap Meets"
                    description="A monthly date that repeats, pitch fees taken online, and stock counted per date so a full month does not block the next one."
                    icon-color="lime"
                    blog-slug="for-flea-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Holiday & Seasonal Markets"
                    description="Two weekends in December that live on their own sub-schedule, so the winter market has its own link and does not disturb the summer season."
                    icon-color="orange"
                    blog-slug="for-holiday-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Night Markets"
                    description="A midweek evening market as its own event with its own hours, and an agenda for the food stalls and the band."
                    icon-color="lime"
                    blog-slug="for-night-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Specialty Food Markets"
                    description="Cheese, coffee and a chef demo with thirty free places, counted per market date so July selling out leaves August alone."
                    icon-color="orange"
                    blog-slug="for-specialty-food-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A2.704 2.704 0 003 15.546M9 6v2m3-2v2m3-2v2M9 3h6m-7 8h8a1 1 0 011 1v4a1 1 0 01-1 1H8a1 1 0 01-1-1v-4a1 1 0 011-1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. How it works (08)                                         -->
    <!-- ============================================================ -->
    <section id="how" class="scroll-mt-24 es-mkt-hr py-20 lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                <h2 class="es-balance es-mkt-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three decisions, <span class="es-mkt-grad">then it runs</span>.
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['01', 'Set the season', 'The market name, the square, the days of the week and the hours. Then give the recurrence a closing date so it stops on its own.'],
                    ['02', 'Open the pitch list', 'Turn on submissions, write your pitch terms, and name your regulars as approved schedules. Everybody else waits for you.'],
                    ['03', 'Print the code', 'Download your QR code, put it on the A-board, and start a list of shoppers who tapped Follow, yours to email with no algorithm in the middle.'],
                ] as [$hN, $hT, $hD])
                    <div class="es-mkt-card p-7" data-reveal="panel">
                        <p class="es-mkt-accent es-mkt-num mb-3 text-sm font-bold">{{ $hN }}</p>
                        <h3 class="es-mkt-ink mb-2 text-lg font-bold">{{ $hT }}</h3>
                        <p class="es-mkt-muted text-sm">{{ $hD }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="es-mkt-hr py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-mkt-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A whole season as one market day, ending on a closing date" :url="marketing_url('/features/recurring-events')" icon-color="lime">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-Schedules" description="Produce, craft and the winter market on their own links" :url="marketing_url('/features/sub-schedules')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Tell shoppers what is coming, in your own words" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Pitch fees and tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the season on the website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-mkt-link inline-flex items-center font-medium">
                    See all features
                    <svg aria-hidden="true" class="ms-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 11. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-mkt-hr py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-mkt-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-food-trucks-and-vendors', 'Food Trucks & Vendors'],
                    ['/for-breweries-and-wineries', 'Breweries & Wineries'],
                    ['/for-community-centers', 'Community Centers'],
                    ['/for-curators', 'Curators'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-mkt-card es-mkt-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-mkt-muted text-sm">Event Schedule for</div>
                            <div class="es-mkt-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-mkt-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-mkt-link inline-flex items-center font-medium">
                    See all use cases
                    <svg aria-hidden="true" class="ms-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 12. FAQ (09)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 es-mkt-hr py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-mkt-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <p class="es-mkt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-mkt-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked across <span class="es-mkt-grad">the trestle</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-mkt-card es-mkt-hover group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-mkt-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-mkt-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-mkt-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-mkt-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                    <div class="es-mkt-dawn"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-mkt-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put the whole season up <span class="es-mkt-grad">before opening day</span>.
                    </h2>
                    <p class="es-mkt-muted mx-auto mb-10 max-w-xl text-lg sm:text-xl">
                        The season, the pitch list and the email list cost nothing, and so do your first
                        {{ $freeTicketCap }} paid tickets a month. Unlimited selling is ${{ $proMonthly }}
                        a month, and none of what you take at the gate comes to us.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-market" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-300 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-mkt-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your market's calendar
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-mkt-muted mt-6 text-sm">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section dot navigation -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 es-mkt-tip">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
