<x-marketing-layout>
    <x-slot name="title">Boost Events with Ad Campaigns - Event Schedule</x-slot>
    <x-slot name="description">Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics, no ad manager experience needed.</x-slot>
    <x-slot name="breadcrumbTitle">Boost</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Boost",
        "description": "Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Advertising Automation"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Boost",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Advertising Software",
        "operatingSystem": "Web",
        "description": "Turn your event details into live Facebook and Instagram ads. Automated targeting, budget control, and real-time analytics with no ad manager experience required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Ad budget starts at $10, plus a 20% service fee. Boost requires a Pro schedule."
        },
        "featureList": [
            "Facebook and Instagram campaigns built from an event you have already published",
            "Automatic targeting from the event's location, category and format",
            "Delivery across Facebook and Instagram, with the surface left to Meta",
            "Prepaid budgets from $10, with a per-campaign ceiling that grows with completed campaigns",
            "Impressions, reach, clicks, CTR, CPC, CPM and Meta Pixel conversions",
            "Pause, resume or cancel at any time",
            "Unspent budget and its share of the service fee refunded automatically",
            "On-network promotions billed by CPM or CPC where the site runs a promotions network"
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
           For-boost "The Launch" styles. A campaign here is a launch,
           not a broadcast. It needs CLEARANCE (a Pro schedule, a verified
           phone, an event that is actually published), a finite FUEL LOAD
           that is paid for before ignition, a WINDOW that closes when the
           event happens, and a DOWNLINK of numbers on the way. Whatever
           does not burn is returned. Every device on this page is one of
           those four, in that order.

           ANTI-COLLISION, treat as binding: "The Console" is
           /for-ai-agents and "The Dashboard" is /analytics, so this page
           is deliberately NOT a control room and NOT a stat wall. Its
           signature devices are the PRE-LAUNCH CLEARANCE CARD, the
           proportional WINDOW STRIP (three campaigns against one 30-day
           axis, because the window is clamped to 3-14 days), and the BURN
           BAR that reads left to right as charged, delivered, returned.

           COLOUR: the page keeps the burnt orange it was built with.
           Amber and gold are spoken for on seven other pages, so stay at
           the red end of the ramp (#a33a0a and #c2410c in light,
           #fb923c in dark) and never drift towards yellow.

           NEVER use text-gray-500 on this warm ground - #6b7280 measures
           about 4.4 here. Use .es-launch-muted (7.31 light / 7.28 dark).

           The pad band and the finale are the same object in both colour
           modes, so they are pinned: every shared class that flips with
           .dark (grid-overlay, animate-shimmer, es-claim:focus-within,
           es-aurora opacity) is overridden inside them.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-launch-page { background-color: #fbf8f6; color: #1a1512; }
        .dark .es-launch-page { background-color: #100c0a; color: #f2eae4; }
        .es-launch-ink { color: #1a1512; }
        .dark .es-launch-ink { color: #f2eae4; }
        .es-launch-muted { color: #5b5148; }
        .dark .es-launch-muted { color: #a99c92; }
        .es-launch-accent { color: #a33a0a; }
        .dark .es-launch-accent { color: #fb923c; }
        /* Always-lit ink for the two fixed-dark objects, in both modes. */
        .es-launch-lit { color: #fb923c; }
        .es-launch-onband { color: #f4ece6; }
        .es-launch-onband-muted { color: #b0a29a; }

        .es-launch-grad {
            background-image: linear-gradient(100deg, #7c2d12 0%, #c2410c 55%, #a33a0a 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-launch-grad {
            background-image: linear-gradient(100deg, #fdba74 0%, #fb923c 55%, #ffb37a 100%);
        }

        /* --- Surfaces --- */
        .es-launch-card {
            border: 1px solid rgba(26, 21, 18, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-launch-card {
            border-color: rgba(242, 234, 228, 0.12);
            background: #191210;
        }
        .es-launch-band .es-launch-card,
        .es-launch-finale .es-launch-card {
            border-color: rgba(242, 234, 228, 0.14);
            background: #1a1311;
        }
        .es-launch-inset {
            border: 1px solid rgba(26, 21, 18, 0.1);
            border-radius: 0.75rem;
            background: #f4efeb;
        }
        .dark .es-launch-inset {
            border-color: rgba(242, 234, 228, 0.1);
            background: #14100e;
        }
        .es-launch-band .es-launch-inset {
            border-color: rgba(242, 234, 228, 0.1);
            background: #14100e;
        }
        .es-launch-hr { border-top: 1px solid rgba(26, 21, 18, 0.1); }
        .dark .es-launch-hr { border-top-color: rgba(242, 234, 228, 0.1); }
        /* Pinned: the pad is dark in both modes, so its rules must be light in both. */
        .es-launch-band .es-launch-hr { border-top-color: rgba(242, 234, 228, 0.1); }
        .es-launch-edge { border-top: 1px solid rgba(26, 21, 18, 0.08); }
        .dark .es-launch-edge { border-top-color: rgba(242, 234, 228, 0.08); }

        /* --- The pad: one object, identical with .dark on or off --- */
        .es-launch-band {
            background-color: #0f0a08;
            background-image: radial-gradient(125% 100% at 50% 0%, #1d1310 0%, #140d0a 52%, #090605 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(242, 234, 228, 0.05);
        }
        .es-launch-finale {
            background-color: #0f0a08;
            background-image: radial-gradient(115% 130% at 50% 0%, #26150c 0%, #140d0a 55%, #090605 100%);
        }
        .es-launch-band .grid-overlay,
        .es-launch-finale .grid-overlay {
            background-image:
                linear-gradient(rgba(242, 234, 228, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(242, 234, 228, 0.05) 1px, transparent 1px);
        }
        .es-launch-band .animate-shimmer,
        .es-launch-finale .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-launch-band .es-aurora,
        .es-launch-finale .es-aurora { opacity: 0.5; }
        .es-launch-finale .es-claim:focus-within {
            border-color: rgba(251, 146, 60, 0.75);
            box-shadow: 0 0 0 4px rgba(251, 146, 60, 0.22);
        }

        /* --- Labels, numerals, chips --- */
        .es-launch-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #5b5148;
        }
        .dark .es-launch-tag { color: #a99c92; }
        .es-launch-band .es-launch-tag,
        .es-launch-finale .es-launch-tag { color: #fb923c; }

        .es-launch-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-launch-fine { font-size: 0.7rem; line-height: 1.5; }

        .es-launch-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(26, 21, 18, 0.18);
            background: #ffffff;
            color: #1a1512;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.06em;
        }
        .dark .es-launch-corner { border-color: rgba(242, 234, 228, 0.2); background: #191210; color: #f2eae4; }
        .es-launch-band .es-launch-corner { border-color: rgba(242, 234, 228, 0.2); background: #1a1311; color: #f4ece6; }
        .es-launch-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #c2410c;
        }
        .dark .es-launch-corner::before { background: #fb923c; }
        .es-launch-band .es-launch-corner::before { background: #fb923c; }

        .es-launch-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(26, 21, 18, 0.16);
            background: rgba(255, 255, 255, 0.75);
            color: #5b5148;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .dark .es-launch-chip {
            border-color: rgba(242, 234, 228, 0.16);
            background: rgba(242, 234, 228, 0.05);
            color: #b0a29a;
        }

        .es-launch-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(163, 58, 10, 0.45);
            color: #a33a0a;
        }
        .dark .es-launch-plan { border-color: rgba(251, 146, 60, 0.45); color: #fb923c; }
        .es-launch-band .es-launch-plan { border-color: rgba(251, 146, 60, 0.45); color: #fb923c; }
        .es-launch-plan-free { border-color: rgba(26, 21, 18, 0.35); color: #1a1512; }
        .dark .es-launch-plan-free { border-color: rgba(242, 234, 228, 0.38); color: #f2eae4; }

        /* --- Clearance rows: the pre-launch card --- */
        .es-launch-row {
            display: flex;
            align-items: flex-start;
            gap: 0.7rem;
            padding: 0.6rem 0;
        }
        .es-launch-row + .es-launch-row { border-top: 1px solid rgba(26, 21, 18, 0.08); }
        .dark .es-launch-row + .es-launch-row { border-top-color: rgba(242, 234, 228, 0.08); }
        .es-launch-check {
            flex: none;
            margin-top: 0.32rem;
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 2px;
            background: #c2410c;
        }
        .dark .es-launch-check { background: #fb923c; }
        .es-launch-check-open {
            background: transparent;
            border: 1px solid rgba(26, 21, 18, 0.35);
        }
        .dark .es-launch-check-open { border-color: rgba(242, 234, 228, 0.35); }

        .es-launch-led {
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background: #c2410c;
            box-shadow: 0 0 0 3px rgba(194, 65, 12, 0.18);
            animation: es-launch-blink 2.8s ease-in-out infinite;
        }
        .dark .es-launch-led { background: #fb923c; box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.2); }
        @keyframes es-launch-blink {
            0%, 100% { opacity: 1; }
            55% { opacity: 0.35; }
        }

        /* --- The window strip: three campaigns on one 30-day axis --- */
        .es-launch-track {
            position: relative;
            height: 1.4rem;
            border-radius: 0.3rem;
            background: rgba(26, 21, 18, 0.07);
        }
        .dark .es-launch-track { background: rgba(242, 234, 228, 0.08); }
        .es-launch-span {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            border-radius: 0.3rem;
            background: #c2410c;
        }
        .dark .es-launch-span { background: #fb923c; }
        .es-launch-mark {
            position: absolute;
            top: -0.3rem;
            bottom: -0.3rem;
            width: 2px;
            border-radius: 1px;
            background: #1a1512;
        }
        .dark .es-launch-mark { background: #f2eae4; }
        .es-launch-rule {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #5b5148;
        }
        .dark .es-launch-rule { color: #a99c92; }

        /* --- The burn bar: charged, delivered, returned --- */
        .es-launch-burn {
            display: flex;
            height: 2.4rem;
            overflow: hidden;
            border-radius: 0.5rem;
            border: 1px solid rgba(26, 21, 18, 0.12);
        }
        .dark .es-launch-burn { border-color: rgba(242, 234, 228, 0.12); }
        .es-launch-burn-spent {
            background: #c2410c;
            color: #ffffff;
        }
        .dark .es-launch-burn-spent { background: #fb923c; color: #1a1512; }
        .es-launch-burn-back {
            background: rgba(26, 21, 18, 0.07);
            color: #4f463e;
        }
        .dark .es-launch-burn-back { background: rgba(242, 234, 228, 0.08); color: #b0a29a; }
        .es-launch-burn-seg {
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Segments and bars draw themselves in when the panel reveals, and rest
           finished for no-JS, crawler and reduced-motion visitors. */
        .es-launch-grow {
            transform: scaleX(0.02);
            transform-origin: left;
            transition: transform 1.15s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--bd, 0.25s);
        }
        [data-reveal].is-revealed .es-launch-grow,
        html:not(.es-anim) .es-launch-grow { transform: none; }

        /* --- The downlink chart --- */
        .es-launch-bar {
            border-radius: 0.15rem 0.15rem 0 0;
            background: #c2410c;
        }
        .dark .es-launch-bar { background: #fb923c; }
        .es-launch-band .es-launch-bar { background: #fb923c; }

        /* --- The envelope table --- */
        .es-launch-table { width: 100%; border-collapse: collapse; text-align: left; }
        .es-launch-table th,
        .es-launch-table td {
            padding: 0.65rem 0.5rem;
            font-size: 0.9rem;
            vertical-align: middle;
        }
        .es-launch-table thead th {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #5b5148;
        }
        .dark .es-launch-table thead th { color: #a99c92; }
        .es-launch-table tbody tr { border-top: 1px solid rgba(26, 21, 18, 0.08); }
        .dark .es-launch-table tbody tr { border-top-color: rgba(242, 234, 228, 0.08); }
        .es-launch-table tbody th { font-weight: 700; color: #1a1512; }
        .dark .es-launch-table tbody th { color: #f2eae4; }
        .es-launch-table tbody td { color: #5b5148; }
        .dark .es-launch-table tbody td { color: #a99c92; }
        .es-launch-scroll { overflow-x: auto; }

        /* --- Spec rows in the two pad panels --- */
        .es-launch-spec { display: grid; gap: 0.15rem; padding: 0.55rem 0; }
        .es-launch-spec + .es-launch-spec { border-top: 1px solid rgba(242, 234, 228, 0.08); }
        .es-launch-spec dt {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #fb923c;
        }
        .es-launch-spec dd { font-size: 0.9rem; line-height: 1.55; color: #d8cec7; }

        /* --- Links, buttons, hover --- */
        .es-launch-link { color: #a33a0a; text-decoration: none; }
        .es-launch-link:hover { color: #1a1512; text-decoration: underline; }
        .dark .es-launch-link { color: #fb923c; }
        .dark .es-launch-link:hover { color: #f2eae4; }
        .es-launch-band .es-launch-link,
        .es-launch-finale .es-launch-link { color: #fb923c; }
        .es-launch-band .es-launch-link:hover,
        .es-launch-finale .es-launch-link:hover { color: #f4ece6; }

        .es-launch-btn {
            background-color: #c2410c;
            color: #ffffff;
            box-shadow: 0 18px 36px -16px rgba(194, 65, 12, 0.55);
        }
        .es-launch-btn:hover { background-color: #9a3412; box-shadow: 0 22px 44px -16px rgba(194, 65, 12, 0.65); }
        .dark .es-launch-btn { background-color: #fb923c; color: #1a1512; }
        .dark .es-launch-btn:hover { background-color: #fdba74; }
        /* Pinned to the dark-mode treatment: the finale panel is dark in both modes. */
        .es-launch-finale .es-launch-btn { background-color: #fb923c; color: #1a1512; }
        .es-launch-finale .es-launch-btn:hover { background-color: #fdba74; }

        .es-launch-ghost {
            border: 1px solid rgba(26, 21, 18, 0.18);
            background: rgba(255, 255, 255, 0.7);
            color: #1a1512;
        }
        .es-launch-ghost:hover { border-color: rgba(163, 58, 10, 0.5); }
        .dark .es-launch-ghost {
            border-color: rgba(242, 234, 228, 0.18);
            background: rgba(242, 234, 228, 0.05);
            color: #f2eae4;
        }
        .dark .es-launch-ghost:hover { border-color: rgba(251, 146, 60, 0.5); }

        .es-launch-hover:hover { border-color: rgba(163, 58, 10, 0.45); }
        .dark .es-launch-hover:hover { border-color: rgba(251, 146, 60, 0.45); }
        .es-launch-hover:hover .es-launch-hover-title,
        .es-launch-hover:hover .es-launch-hover-arrow { color: #a33a0a; }
        .dark .es-launch-hover:hover .es-launch-hover-title,
        .dark .es-launch-hover:hover .es-launch-hover-arrow { color: #fb923c; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(194, 65, 12, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(251, 146, 60, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(194, 65, 12, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(251, 146, 60, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #c2410c; }
        .dark .es-dot.is-active .es-dot-pip { background: #fb923c; }

        /* --- Focus rings. No border-radius here: setting it reshapes the
               element on focus, and outlines already follow its shape. --- */
        #es-launch-page a:focus-visible,
        #es-launch-page summary:focus-visible,
        #es-launch-page button:focus-visible {
            outline: 2px solid #a33a0a;
            outline-offset: 3px;
        }
        .dark #es-launch-page a:focus-visible,
        .dark #es-launch-page summary:focus-visible,
        .dark #es-launch-page button:focus-visible {
            outline-color: #fb923c;
        }
        .es-launch-band a:focus-visible,
        .es-launch-finale a:focus-visible,
        .es-launch-finale input:focus-visible {
            outline-color: #fb923c !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-launch-led { animation: none !important; }
            .es-launch-grow { transform: none !important; transition: none !important; }
        }
    </style>

    @php
        // The pre-launch clearance card. Everything here is a real gate in
        // BoostController::create(): the Pro check, the hosted phone check, the
        // draft check, and the trust-based budget ceiling.
        $clearance = [
            ['Schedule on Pro', 'Boost is a Pro feature. Publishing the event it advertises is free.', true],
            ['Phone verified', 'A verified phone number on the account, on eventschedule.com. It is a spend gate, not a marketing list.', true],
            ['Event published', 'A draft cannot be boosted: the ad needs somewhere real to land. An on-network promotion needs the event to be fully public, so unlisted is out too.', true],
            ['Budget inside your ceiling', 'Every schedule has a per-campaign ceiling. A brand new one starts at $10.', false],
            ['Card or boost credit', 'One prepayment covers the whole campaign. Nothing is billed later.', false],
        ];

        // Three campaigns on one 30-day axis. The default window opens at launch
        // and closes at the event, clamped to at least 3 and at most 14 days
        // (MetaAdsService::calculateDuration).
        $windows = [
            ['Event in 2 days', 2, 3, 'Clamped up to the 3-day minimum, so it runs past the door.'],
            ['Event in 10 days', 10, 10, 'The ordinary case: the window is the run-up to the event.'],
            ['Event in 30 days', 30, 14, 'Clamped down to the 14-day maximum, so it stops well before.'],
        ];

        // Role::calculateBoostLimitForCompletedCount() and
        // Role::calculateBoostMaxConcurrentForCompletedCount(), on the hosted site.
        $envelope = [
            ['0', 'New schedule', '$10', '1'],
            ['1', 'One landed', '$25', '1'],
            ['3', 'Three landed', '$50', '2'],
            ['5', 'Five landed', '$100', '2'],
            ['10', 'Ten landed', '$250', '3'],
            ['20', 'Twenty landed', '$500', '3'],
            ['50+', 'Fifty and up', '$1,000', '3'],
        ];

        // A daily impression series, drawn as a shape rather than a claim.
        $series = [26, 44, 61, 58, 79, 96, 88, 71, 54, 33];

        $faqs = [
            [
                'q' => 'Does Boost guarantee ticket sales or attendance?',
                'a' => 'No. Boost buys distribution: it puts your event in front of people who are not looking for it, on Facebook and Instagram or on other schedules\' pages. What happens next depends on the event, the image, the copy, the audience and the budget. Anyone promising a number is guessing.',
            ],
            [
                'q' => 'Do I need a paid plan to boost an event?',
                'a' => 'Yes. Boost is a Pro feature, and on eventschedule.com the account also needs a verified phone number before it can spend. Publishing the event, sharing the link, taking RSVPs and emailing your own followers are all free.',
            ],
            [
                'q' => 'How much does Boost cost?',
                'a' => 'You choose the ad budget, starting at $10, and Event Schedule adds a 20% service fee on top. The full breakdown is on screen before you launch. Your per-campaign ceiling starts at $10 for a brand new schedule and rises to $1,000 as campaigns complete, so the first one is deliberately small.',
            ],
            [
                'q' => 'Which platforms do Boost ads run on?',
                'a' => 'Facebook and Instagram, and you can restrict a campaign to just one of them. Which surface it appears on inside a platform, whether that is the feed, Stories or Reels, is Meta\'s delivery decision rather than a setting here. Separately, where the Event Schedule site you are on runs a promotions network, your event can also run as a promoted card on other schedules\' pages on that same site.',
            ],
            [
                'q' => 'How does targeting work?',
                'a' => 'Boost reads the event. Anything with a venue, hybrid included, targets a 25-mile radius around it, or the venue\'s country if the address has no coordinates. An online-only event targets countries instead. Age starts at 18 to 65 and interests are inferred from the event\'s category. Advanced mode lets you search Meta interests, change the age range, restrict the platform and set the objective.',
            ],
            [
                'q' => 'Can I pause or cancel a campaign?',
                'a' => 'Yes, at any time, from the campaign page. Pausing stops delivery and keeps the campaign; resuming picks it back up. Cancelling ends it and returns the part of the budget that was never delivered, plus that part\'s share of the service fee.',
            ],
            [
                'q' => 'What happens to unspent budget?',
                'a' => 'It comes back. If Meta rejects the ad, or you cancel before anything has been delivered, the whole amount is refunded. If the campaign finishes with budget left, the remainder and its proportional share of the service fee are refunded automatically. Refunds go back to the card you paid with, or to the schedule\'s boost credit if you paid from credit.',
            ],
            [
                'q' => 'Do I need a Meta Ads account?',
                'a' => 'No. Event Schedule holds the ad account, builds the creative from your event and handles delivery. You never touch Ads Manager, and you do not connect a Facebook Page of your own.',
            ],
            [
                'q' => 'What are on-network promotions?',
                'a' => 'A second channel that does not involve Meta at all: your event runs as a promoted card on the public pages of that site\'s free-plan schedules. You prepay a budget and pick CPM, where you pay per thousand times it is shown, or CPC, where impressions are free and you pay per click. Targeting by visitor country and by schedule type is optional. Reporting is by type of schedule, never by naming the pages you ran on.',
            ],
            [
                'q' => 'Will other schedules\' promotions appear on my pages?',
                'a' => 'Only while your schedule is on the Free plan, and only on a site whose operator runs a promotions network. Pro and Enterprise pages carry nothing at all. If you are on Free and would rather stay clear, "Do not show other schedules\' promotions" lives in your schedule\'s Advanced settings, it is free, and turning it on does not stop you buying promotions later. The same toggle covers ads if the site runs those too.',
            ],
        ];

        $dotSections = [
            ['top', 'The launch'],
            ['audience', 'Who it reaches'],
            ['pads', 'Two channels'],
            ['window', 'The window'],
            ['burn', 'What it costs'],
            ['envelope', 'The ceiling'],
            ['downlink', 'What comes back'],
            ['control', 'The controls'],
            ['honest', 'What it is not'],
            ['faq', 'Questions'],
            ['claim', 'Ignition'],
        ];
    @endphp

    <div id="es-launch-page" class="es-launch-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: clearance before ignition                           -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(194, 65, 12, 0.26), rgba(194, 65, 12, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(251, 146, 60, 0.18), rgba(251, 146, 60, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-launch-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 20l4-1 1-4 8-8a2.83 2.83 0 10-4-4l-8 8-4 1-1 4z" />
                        </svg>
                        <span class="es-launch-muted text-sm font-medium tracking-wide">Boost: paid reach for one event</span>
                    </div>

                    <h1 class="es-balance es-launch-ink mb-8 text-4xl font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Newsletters reach your list.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Boost reaches <span class="es-launch-grad">everyone else.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-launch-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Take an event you have already published and put money behind it: a Facebook and Instagram campaign through Meta, or a promoted card on other schedules' pages where the site runs a promotions network. You prepay a fixed budget, you can stop it at any point, and whatever it never spends is returned.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-launch-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.boost') }}" class="es-launch-ghost group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Boost guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The clearance card: the real gates, before any of the sell. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-launch-card p-6 sm:p-7">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <p class="es-launch-tag">Pre-launch</p>
                            <span class="es-launch-muted es-launch-mono inline-flex items-center gap-2 text-xs">
                                <span class="es-launch-led" aria-hidden="true"></span>
                                4 checks, 1 payment
                            </span>
                        </div>

                        <div>
                            @foreach ($clearance as [$cTitle, $cBody, $cDone])
                                <div class="es-launch-row">
                                    <span class="es-launch-check @if (! $cDone) es-launch-check-open @endif" aria-hidden="true"></span>
                                    <div class="min-w-0">
                                        <p class="es-launch-ink text-sm font-bold">{{ $cTitle }}</p>
                                        <p class="es-launch-muted es-launch-fine">{{ $cBody }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-launch-muted es-launch-hr mt-4 pt-4 text-xs">
                            Filled squares are standing facts about your account; hollow ones you settle at the moment you buy. Clear all five and the campaign is built and submitted as soon as the payment goes through. Meta still reviews the ad before it delivers anything, and a rejection is refunded in full.
                        </p>
                    </div>
                </div>
            </div>

            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Facebook', 'Instagram', 'Feeds', 'Stories', 'Reels', 'On-network cards', 'CPM', 'CPC', 'Radius targeting', 'Interests', 'Pixel conversions'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-launch-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Who it reaches: the list you have vs the room you do not   -->
    <!-- ============================================================ -->
    <section id="audience" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it reaches</p>
                <h2 class="es-balance es-launch-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    You already have two audiences. <span class="es-launch-grad">Only one is free.</span>
                </h2>
                <p class="es-launch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The people who follow your schedule cost nothing to reach and are yours to email. Everyone else has to be paid for. Boost is only ever about the second group.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                <div class="es-launch-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-launch-ink text-xl font-bold">People who already found you</h3>
                        <span class="es-launch-plan es-launch-plan-free">Free</span>
                    </div>
                    <p class="es-launch-muted mb-4">
                        Followers, ticket buyers and anyone you have shared the link with. You reach them with a newsletter you write and send: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient.
                    </p>
                    <p class="es-launch-muted mb-4 text-sm">
                        Worth being blunt about, because plenty of tools imply otherwise: nothing goes out on its own here. Adding a show does not email your followers. A newsletter is a thing you compose and hit send on.
                    </p>
                    <a href="{{ marketing_url('/features/newsletters') }}" class="es-launch-link mt-auto inline-flex items-center gap-1 text-sm font-semibold">
                        How newsletters work
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <div class="es-launch-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-launch-ink text-xl font-bold">People who have never heard of you</h3>
                        <span class="es-launch-plan">Pro</span>
                    </div>
                    <p class="es-launch-muted mb-4">
                        No relationship, no list, no reason to be looking. Reaching them means buying attention: an ad in a Facebook or Instagram feed, or a promoted card on a schedule page somebody else built an audience for.
                    </p>
                    <p class="es-launch-muted mb-4 text-sm">
                        That is the whole job of this page. A first campaign is small on purpose, and the money you do not use comes back, so the honest way to find out whether it works for your event is to spend $10 finding out.
                    </p>
                    <a href="#pads" class="es-launch-link mt-auto inline-flex items-center gap-1 text-sm font-semibold">
                        See both channels
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Two channels (fixed-dark band)                            -->
    <!-- ============================================================ -->
    <section id="pads" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-launch-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 20%, rgba(251, 146, 60, 0.16), rgba(251, 146, 60, 0) 60%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Ad channels: Meta and on-network</p>
                    <h2 class="es-balance es-launch-onband text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Off the platform, or <span class="es-launch-lit">across it.</span>
                    </h2>
                    <p class="es-launch-onband-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Same campaign screen, same wallet, two completely different rooms. One rents space from Meta. The other rents space from other people's schedules on the site you are already on.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2" data-reveal-group="120">
                    <div class="es-launch-card flex flex-col p-7 sm:p-8" data-reveal="panel">
                        <div class="mb-5 flex flex-wrap items-center gap-2">
                            <p class="es-launch-tag">Channel A</p>
                            <span class="es-launch-plan">Pro</span>
                        </div>
                        <h3 class="es-launch-onband mb-2 text-2xl font-bold">Meta ads</h3>
                        <p class="es-launch-onband-muted mb-5 text-sm">Facebook and Instagram, bought through Event Schedule's ad account.</p>
                        <dl class="mb-6">
                            <div class="es-launch-spec">
                                <dt>Placements</dt>
                                <dd>Facebook, Instagram, or only one of the two if you say so. Which surface it lands on inside them, feed, Stories or Reels, is Meta's own delivery decision.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Audience</dt>
                                <dd>Built from the event: a 25-mile radius around the venue whenever there is one, hybrid included, falling back to the venue's country if it has no coordinates. Online only targets countries instead. Ages 18 to 65 to start, interests inferred from the event's category.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Price</dt>
                                <dd>Your ad budget from $10 up to your ceiling, plus a 20% service fee. One prepayment.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Account</dt>
                                <dd>Not yours to set up. No Ads Manager, no Business Manager, no Page connection.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Review</dt>
                                <dd>Meta reviews every ad. A rejection comes back with Meta's reason and a full refund.</dd>
                            </div>
                        </dl>
                        <a href="{{ route('marketing.docs.boost') }}#quick-mode" class="es-launch-link mt-auto inline-flex items-center gap-1 text-sm font-semibold">
                            Quick and Advanced mode
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>

                    <div class="es-launch-card flex flex-col p-7 sm:p-8" data-reveal="panel">
                        <div class="mb-5 flex flex-wrap items-center gap-2">
                            <p class="es-launch-tag">Channel B</p>
                            <span class="es-launch-plan">Pro</span>
                        </div>
                        <h3 class="es-launch-onband mb-2 text-2xl font-bold">On-network promotions</h3>
                        <p class="es-launch-onband-muted mb-5 text-sm">A promoted card on other schedules' public pages, on the same site.</p>
                        <dl class="mb-6">
                            <div class="es-launch-spec">
                                <dt>Placements</dt>
                                <dd>The schedule and event pages of that site's free-plan schedules, in front of people already browsing events. Paid plans never display a promotion, so the inventory is exactly the site's free schedules.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Audience</dt>
                                <dd>Optional filters only: visitor country, and the type of schedule it runs on (talent, venue or curator). Leaving both empty reaches the most pages.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Price</dt>
                                <dd>Prepaid, with no service fee on top. CPM bills per thousand impressions; CPC makes impressions free and bills per click.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Review</dt>
                                <dd>Because it runs on other people's pages, your first campaigns wait for the site operator to approve them. A rejection is refunded in full, and a clean record removes the wait.</dd>
                            </div>
                            <div class="es-launch-spec">
                                <dt>Availability</dt>
                                <dd>Per site. You will only see it where the operator runs a promotions network, and eventschedule.com itself does not carry one.</dd>
                            </div>
                        </dl>
                        <a href="{{ route('marketing.docs.boost') }}#on-network" class="es-launch-link mt-auto inline-flex items-center gap-1 text-sm font-semibold">
                            How promotions are reviewed
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>

                <p class="es-launch-onband-muted mx-auto mt-10 max-w-3xl text-center text-sm" data-reveal>
                    Worth knowing which side of that you are on. Only free-plan pages carry promotions, and Boost needs Pro, so a schedule big enough to buy one is never hosting anyone else's. A free schedule that would rather stay clear has one toggle in its Advanced settings, free on every plan, and switching it on does not stop it buying promotions later. The same toggle covers ads where the site runs those too.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The window: proportional, clamped to 3 to 14 days         -->
    <!-- ============================================================ -->
    <section id="window" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Campaign dates</p>
                <h2 class="es-balance es-launch-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    A campaign has an <span class="es-launch-grad">end built into it.</span>
                </h2>
                <p class="es-launch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    For a dated event the default window opens the moment you pay and closes at the event itself, held between three days and fourteen. Nothing here keeps running because somebody forgot to switch it off.
                </p>
            </div>

            <div class="es-launch-card p-6 sm:p-8" data-reveal="panel">
                <div class="es-launch-rule mb-3" aria-hidden="true">
                    <span>The day you pay</span>
                    <span>Thirty days out</span>
                </div>

                <div class="space-y-6">
                    @foreach ($windows as $wi => [$wLabel, $wEvent, $wDays, $wNote])
                        <div>
                            <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                                <p class="es-launch-ink text-sm font-bold">{{ $wLabel }}</p>
                                <p class="es-launch-muted es-launch-mono text-xs">{{ $wDays }}-day window</p>
                            </div>
                            <div class="es-launch-track" aria-hidden="true">
                                <div class="es-launch-span es-launch-grow" style="width: {{ round($wDays / 30 * 100, 2) }}%; --bd: {{ 0.2 + $wi * 0.15 }}s;"></div>
                                <div class="es-launch-mark" style="left: {{ round($wEvent / 30 * 100, 2) }}%;"></div>
                            </div>
                            <p class="es-launch-muted es-launch-fine mt-1.5">{{ $wNote }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="es-launch-muted es-launch-fine mt-4">Each bar is one campaign against the same thirty days. The upright rule is the event.</p>

                <div class="es-launch-hr mt-6 grid gap-5 pt-5 sm:grid-cols-2">
                    <div>
                        <p class="es-launch-ink mb-1 text-sm font-bold">Set your own instead</p>
                        <p class="es-launch-muted text-sm">Advanced mode takes an explicit start and end date, and a budget that is either daily or spread across the whole run.</p>
                    </div>
                    <div>
                        <p class="es-launch-ink mb-1 text-sm font-bold">Two ways it ends</p>
                        <p class="es-launch-muted text-sm">The end date arrives, or the budget runs out. Whichever comes first, the campaign completes, and it settles up about a day later.</p>
                    </div>
                    <div>
                        <p class="es-launch-ink mb-1 text-sm font-bold">A repeating night is different</p>
                        <p class="es-launch-muted text-sm">The default aims at a single date, so a series that started weeks ago has nothing ahead to aim at and gets a flat three-day window. For those, set the dates yourself in Advanced mode.</p>
                    </div>
                    <div>
                        <p class="es-launch-ink mb-1 text-sm font-bold">Nothing renews</p>
                        <p class="es-launch-muted text-sm">There is no subscription and no rollover. A finished campaign stays finished until you deliberately start another one.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The burn: charged, delivered, returned                    -->
    <!-- ============================================================ -->
    <section id="burn" class="es-launch-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Budget and refunds</p>
                    <h2 class="es-balance es-launch-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        You pay once. <span class="es-launch-grad">Unburnt fuel comes back.</span>
                    </h2>
                    <p class="es-launch-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        The charge is your ad budget plus a 20% service fee, taken as a single prepayment from your card or from the schedule's boost credit. There is no second invoice, and no way for a campaign to overspend what you handed it.
                    </p>
                    <ul class="es-launch-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-launch-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span><span class="es-launch-ink font-semibold">Rejected by Meta.</span> The full amount is refunded, fee included.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-launch-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span><span class="es-launch-ink font-semibold">Cancelled before anything ran.</span> Also the full amount.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-launch-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span><span class="es-launch-ink font-semibold">Finished with budget left.</span> The remainder and its share of the fee are refunded automatically, to the card or back into boost credit.</span>
                        </li>
                    </ul>
                    <p class="es-launch-muted mt-6 text-sm">
                        Three emails mark the run: one when the campaign is created, one when 75% of the budget has gone, and one at the end with the final numbers and any refund.
                    </p>
                </div>

                <div data-reveal="panel">
                    <div class="es-launch-card p-6 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-launch-ink text-lg font-bold">One campaign, settled</h3>
                            <span class="es-launch-muted es-launch-mono text-xs">worked example</span>
                        </div>

                        <div class="es-launch-burn mb-2" aria-hidden="true">
                            <div class="es-launch-burn-seg es-launch-burn-spent" style="width: 55%;">$49.44 delivered</div>
                            <div class="es-launch-burn-seg es-launch-burn-back" style="width: 45%;">$40.56 returned</div>
                        </div>
                        <p class="es-launch-muted es-launch-fine mb-5">The bar is the $90.00 that was charged. It only ever splits two ways.</p>

                        <dl class="es-launch-inset es-launch-mono p-4 text-sm">
                            <div class="flex items-baseline justify-between gap-3 py-1">
                                <dt class="es-launch-muted">Ad budget</dt>
                                <dd class="es-launch-ink font-bold">$75.00</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3 py-1">
                                <dt class="es-launch-muted">Service fee, 20%</dt>
                                <dd class="es-launch-ink font-bold">$15.00</dd>
                            </div>
                            <div class="es-launch-hr mt-2 flex items-baseline justify-between gap-3 pt-2">
                                <dt class="es-launch-ink font-bold">Charged up front</dt>
                                <dd class="es-launch-ink font-bold">$90.00</dd>
                            </div>
                            <div class="es-launch-hr mt-2 flex items-baseline justify-between gap-3 pt-2">
                                <dt class="es-launch-muted">Ad spend Meta delivered</dt>
                                <dd class="es-launch-ink font-bold">$41.20</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3 py-1">
                                <dt class="es-launch-muted">Never delivered</dt>
                                <dd class="es-launch-ink font-bold">$33.80</dd>
                            </div>
                            <div class="es-launch-hr mt-2 flex items-baseline justify-between gap-3 pt-2">
                                <dt class="es-launch-accent font-bold">Refunded</dt>
                                <dd class="es-launch-accent font-bold">$40.56</dd>
                            </div>
                        </dl>
                        <p class="es-launch-muted es-launch-fine mt-3">
                            The refund is the undelivered $33.80 grossed back up by the same 20%, because you are not charged a fee on spend that never happened.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The envelope: the ceiling that grows                      -->
    <!-- ============================================================ -->
    <section id="envelope" class="es-launch-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Spending limits</p>
                <h2 class="es-balance es-launch-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Your first campaign is <span class="es-launch-grad">capped at ten dollars.</span>
                </h2>
                <p class="es-launch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Not a plan tier, a track record. Every schedule starts with a small per-campaign ceiling and one campaign at a time; both rise on their own as campaigns complete. It protects you from a first-day mistake and the site from someone else's.
                </p>
            </div>

            <div class="es-launch-card p-5 sm:p-7" data-reveal="panel">
                <div class="es-launch-scroll">
                    <table class="es-launch-table">
                        <caption class="sr-only">Boost spending limits by number of completed campaigns, on the hosted site</caption>
                        <thead>
                            <tr>
                                <th scope="col">Completed</th>
                                <th scope="col">Standing</th>
                                <th scope="col">Budget ceiling</th>
                                <th scope="col">At once</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($envelope as [$eCount, $eStanding, $eBudget, $eConc])
                                <tr>
                                    <th scope="row" class="es-launch-mono">{{ $eCount }}</th>
                                    <td>{{ $eStanding }}</td>
                                    <td class="es-launch-mono">{{ $eBudget }}</td>
                                    <td class="es-launch-mono">{{ $eConc }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-launch-muted es-launch-hr mt-5 pt-4 text-sm">
                    These are the hosted numbers, and only completed Meta campaigns move a schedule down the table. The same per-campaign ceiling caps an on-network promotion too, but promotions carry their own concurrency limit and never ratchet the ceiling up, so a run of cheap network buys cannot unlock a bigger Facebook spend. A selfhosted install uses whatever limits its operator configured instead.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Downlink: what comes back (fixed-dark band)               -->
    <!-- ============================================================ -->
    <section id="downlink" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-launch-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 70%, rgba(194, 65, 12, 0.2), rgba(194, 65, 12, 0) 60%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Campaign analytics</p>
                    <h2 class="es-balance es-launch-onband text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Numbers, <span class="es-launch-lit">while it is still flying.</span>
                    </h2>
                    <p class="es-launch-onband-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        The campaign page fills in as the campaign runs, so you can stop a bad one on day two instead of reading about it afterwards.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-[1.1fr_1fr]" data-reveal-group="110">
                    <div class="es-launch-card p-6 sm:p-7" data-reveal="panel">
                        <p class="es-launch-tag mb-4">Reported</p>
                        <div class="grid gap-x-6 gap-y-3 sm:grid-cols-2">
                            @foreach ([['Impressions', 'times the ad was shown'], ['Reach', 'people it reached'], ['Clicks', 'taps through to the event'], ['CTR', 'clicks over impressions'], ['CPC', 'what a click cost'], ['CPM', 'what a thousand cost'], ['Conversions', 'tracked with the Meta Pixel'], ['Budget used', 'delivered against what you prepaid']] as [$mName, $mNote])
                                <div>
                                    <p class="es-launch-onband es-launch-mono text-sm font-bold">{{ $mName }}</p>
                                    <p class="es-launch-onband-muted es-launch-fine">{{ $mNote }}</p>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-launch-onband-muted es-launch-hr mt-5 pt-4 text-sm">
                            An on-network promotion reports impressions, clicks, CTR, the countries it reached, the types of schedule it ran on and the ticket sales attributed to it. Individual host pages are never named.
                        </p>
                    </div>

                    <div class="es-launch-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                        <p class="es-launch-tag mb-4">Day by day</p>
                        <div class="es-launch-inset p-4" aria-hidden="true">
                            <div class="flex h-24 items-end justify-between gap-1.5">
                                @foreach ($series as $si => $sh)
                                    <div class="es-launch-bar es-bar w-full" style="height: {{ $sh }}%; --bd: {{ 0.15 + $si * 0.05 }}s;"></div>
                                @endforeach
                            </div>
                            <p class="es-launch-onband-muted es-launch-fine mt-2">Impressions per day across one ten-day window</p>
                        </div>
                        <p class="es-launch-onband-muted mt-5 text-sm">
                            The link the ad points at is your own event page with campaign tags attached, so the visit is recorded as paid traffic in your analytics and a ticket sale that follows carries the same tags.
                        </p>
                        <a href="{{ marketing_url('/features/analytics') }}" class="es-launch-link mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold">
                            What analytics measures
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. The controls: bento                                       -->
    <!-- ============================================================ -->
    <section id="control" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Quick and advanced mode</p>
                <h2 class="es-balance es-launch-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Two hands on it, or <span class="es-launch-grad">none.</span>
                </h2>
                <p class="es-launch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Quick mode writes the campaign from the event and asks you for a budget. Advanced mode hands over every dial. Both end up in the same place.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-launch-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-launch-ink text-xl font-bold">Quick mode reads the event</h3>
                                <span class="es-launch-plan">Pro</span>
                            </div>
                            <p class="es-launch-muted mb-4">Title, date, venue and image become the ad. The format is worked out from what the event actually is: a venue and no link makes it in-person, a link and no venue makes it online, both makes it hybrid, and the targeting and the copy follow from there.</p>
                            <p class="es-launch-muted text-sm">The button on the ad follows too. An event with tickets on sale gets Get tickets, or Sign up when it is online only; an event without them gets Learn more.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-launch-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-launch-ink text-xl font-bold">Pick the objective</h3>
                                <span class="es-launch-plan">Pro</span>
                            </div>
                            <p class="es-launch-muted">Awareness, traffic or engagement. It is the one setting that changes what Meta optimises the whole campaign towards, so it is worth choosing on purpose.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-launch-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-launch-ink text-xl font-bold">Pick the platform</h3>
                                <span class="es-launch-plan">Pro</span>
                            </div>
                            <p class="es-launch-muted">Facebook, Instagram, or both. Inside whichever you keep, Meta spreads the ad across its own surfaces: feeds, Stories, Reels.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-launch-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-launch-ink text-xl font-bold">Write it yourself</h3>
                                <span class="es-launch-plan">Pro</span>
                            </div>
                            <p class="es-launch-muted mb-4">Headline, primary text, link description and call to action are all editable, inside the lengths Meta accepts: 40 characters for the headline, 125 for the body, 30 for the description. If your schedule works in another language, one checkbox swaps in an English version of all three, and clearing it puts yours straight back.</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['Headline 40', 'Primary text 125', 'Description 30', 'Call to action', 'English version'] as $limitChip)
                                    <span class="es-launch-chip">{{ $limitChip }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-launch-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-launch-ink text-xl font-bold">Stop it whenever</h3>
                                <span class="es-launch-plan">Pro</span>
                            </div>
                            <p class="es-launch-muted">Pause and resume as often as you like, or cancel outright. Cancelling settles the money the same day rather than at the end of the window.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-launch-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-launch-ink text-xl font-bold">It lands on your own page</h3>
                                <span class="es-launch-plan es-launch-plan-free">Free</span>
                            </div>
                            <p class="es-launch-muted mb-4">Every click goes to your event page on your schedule, not to a rented landing page and not to a third-party listing. The people who arrive can follow you, RSVP or buy a ticket, and the ones who follow are yours to email long after the campaign has finished.</p>
                            <p class="es-launch-muted text-sm">
                                That is the part worth planning for. A campaign is a fortnight; a follower is not.
                                <a href="{{ marketing_url('/features/newsletters') }}" class="es-launch-link font-semibold">Newsletters take it from there</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. What it is not                                            -->
    <!-- ============================================================ -->
    <section id="honest" class="es-launch-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <p class="es-launch-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What it is not</p>
                <h2 class="es-balance es-launch-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The things a boost <span class="es-launch-grad">cannot do for you.</span>
                </h2>
                <p class="es-launch-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every square at the top of this page fills in eventually. These three never do, whatever you spend.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-launch-card p-7" data-reveal="panel">
                    <div class="mb-4 flex items-start gap-2.5">
                        <span class="es-launch-check es-launch-check-open" aria-hidden="true"></span>
                        <p class="es-launch-tag">Never clears</p>
                    </div>
                    <h3 class="es-launch-ink mb-2 text-lg font-bold">It is not a sales promise</h3>
                    <p class="es-launch-muted text-sm">You are buying impressions and clicks. Nobody, here or anywhere, can sell you attendance. A weak event with a strong campaign is still a weak event.</p>
                </div>
                <div class="es-launch-card p-7" data-reveal="panel">
                    <div class="mb-4 flex items-start gap-2.5">
                        <span class="es-launch-check es-launch-check-open" aria-hidden="true"></span>
                        <p class="es-launch-tag">Never clears</p>
                    </div>
                    <h3 class="es-launch-ink mb-2 text-lg font-bold">It is not exempt from review</h3>
                    <p class="es-launch-muted text-sm">Meta reviews every ad and can refuse one for reasons of its own. You get the reason it gave and the whole payment back, but you do not get an override.</p>
                </div>
                <div class="es-launch-card p-7" data-reveal="panel">
                    <div class="mb-4 flex items-start gap-2.5">
                        <span class="es-launch-check es-launch-check-open" aria-hidden="true"></span>
                        <p class="es-launch-tag">Never clears</p>
                    </div>
                    <h3 class="es-launch-ink mb-2 text-lg font-bold">It is not helped by narrowing</h3>
                    <p class="es-launch-muted text-sm">Every filter you add cuts the pool. Target hard enough and the campaign spends slowly or not at all. The money is not lost, but the fortnight is.</p>
                </div>
            </div>

            <p class="es-launch-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                The best preparation is unglamorous: a real image on the event, a description worth reading, and enough days before the door for the campaign to find anyone.
            </p>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Keep exploring                                           -->
    <!-- ============================================================ -->
    <section class="es-launch-edge scroll-mt-24 py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-launch-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Keep exploring</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                <a href="{{ marketing_url('/features/newsletters') }}" data-reveal class="es-launch-card es-launch-hover group flex flex-col p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                    <h3 class="es-launch-hover-title es-launch-ink mb-3 text-xl font-bold transition-colors">Newsletters</h3>
                    <p class="es-launch-muted mb-5">Email the followers a campaign brought you, with open and click rates afterwards. Free at 10 recipients a month.</p>
                    <span class="es-launch-hover-arrow es-launch-muted mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                        Learn more
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>

                <a href="{{ marketing_url('/features/analytics') }}" data-reveal class="es-launch-card es-launch-hover group flex flex-col p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-md">
                    <h3 class="es-launch-hover-title es-launch-ink mb-3 text-xl font-bold transition-colors">Analytics</h3>
                    <p class="es-launch-muted mb-5">Page views, sources and devices for your schedule and its events, so paid traffic can be told apart from the rest.</p>
                    <span class="es-launch-hover-arrow es-launch-muted mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                        Learn more
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>

                <div data-reveal class="es-launch-card flex flex-col p-7">
                    <h3 class="es-launch-ink mb-4 text-xl font-bold">Popular with</h3>
                    <div class="space-y-3">
                        @foreach ([['/for-venues', 'Venues'], ['/for-musicians', 'Musicians'], ['/for-bars', 'Bars']] as [$popHref, $popName])
                            <a href="{{ marketing_url($popHref) }}" class="es-launch-inset es-launch-hover group/link flex items-center justify-between p-3 transition-all">
                                <span class="es-launch-hover-title es-launch-ink text-sm font-semibold transition-colors">{{ $popName }}</span>
                                <svg aria-hidden="true" class="es-launch-hover-arrow es-launch-muted h-4 w-4 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="es-launch-link mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold">
                        All use cases
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-launch-edge scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-launch-corner mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance es-launch-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-launch-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they spend the first ten dollars.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-launch-card es-launch-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-launch-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-launch-accent es-launch-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-launch-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-launch-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-launch-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-launch-finale noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 15%, rgba(251, 146, 60, 0.24), rgba(251, 146, 60, 0) 60%);"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-launch-tag mb-4">Ignition</p>
                    <h2 class="es-balance es-launch-onband mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight md:text-5xl">
                        Put one event in front of <span class="es-launch-lit">people who have never heard of you.</span>
                    </h2>
                    <p class="es-launch-onband-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Claim your schedule and publish for free. When you have something worth spending on, ten dollars is enough to find out whether a campaign works for your event.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="es-launch-onband-muted shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-launch-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-launch-onband-muted mt-6 text-sm">No credit card to sign up. Boost is on the Pro plan, and every campaign is prepaid.</p>
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
                        <span class="es-launch-card pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
