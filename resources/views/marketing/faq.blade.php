<x-marketing-layout>
    <x-slot name="title">FAQ | Event Schedule - Common Questions Answered</x-slot>
    <x-slot name="description">Find answers to frequently asked questions about Event Schedule. Learn about pricing, features, ticketing, Google Calendar sync, selfhosting, and more.</x-slot>
    <x-slot name="breadcrumbTitle">FAQ</x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           For-faq "The Front Desk" styles.

           CONCEPT: THE DESK IN THE LOBBY. Somebody arrives with a
           question before they sign up. A front desk does two things
           that no other object on this site does: it answers in ORDER,
           and when it cannot answer it POINTS YOU DOWN A CORRIDOR. So
           the page is built as a desk rather than as a stack of
           accordions, and the two things the desk does become the two
           structural devices:

             - THE DIRECTORY BOARD, the engraved plaque on the wall
               behind the desk. Nine corridors, numbered on a brass
               tile, ruled apart by a hairline the way a plaque's rows
               are, and each one carrying the RANGE OF FILING
               REFERENCES that lives down it (01.01 to 01.04). It is
               the hero AND it is the whole in-page navigation, which
               is the point: a directory is nav that admits how big the
               building is.
             - THE DOCKET. Every answer carries a filing reference
               (03.04) so it can be pointed at rather than described.
               The reference is the grouping made visible, and it is
               what the board's right-hand column indexes, so the hero,
               the corridor plates and the answers are one object
               system rather than three decorations.

           DELIBERATELY NO DOTTED LEADERS. /for-comedians owns the
           "numbered row + leader dots + right-hand value" rundown
           board and at least four sibling pages already use leader
           rows, so a fifth would be house furniture with a new
           colour on it. The reference range is the column a rundown
           board cannot carry, and the hairline rule reads as engraving
           rather than as a table of contents.

           And because the question people actually arrive with is "what
           do I get for nothing", the counter carries a RATE CARD: a real
           <table>, three plans across, eleven rows down, including the
           rows that say no. The metaphor and the product argument are
           the same sentence - the free plan is not a trial, so the desk
           can answer without taking your card.

           THE BOARD AND THE BAND ARE FIXED PHYSICAL OBJECTS. A brass-
           lettered navy plaque is the same plaque in a dark lobby, so
           .es-desk-board and .es-desk-band render IDENTICALLY with .dark
           on and off. Shared classes that flip with the colour mode are
           overridden inside them (grid-overlay, animate-shimmer,
           es-claim:focus-within), and es-aurora is deliberately kept
           OUT of both, because its opacity changes between modes.

           COLOUR: the page keeps its existing hue family, blue, but
           spends it as INK rather than as glow. Deep navy #1e3a8a and
           #1d4ed8 on paper, #a8c3ff on the plaque. It is not the shared
           brand blue -> sky -> cyan chrome gradient and it is not the
           bright cyan/sky the audience pages took; a directory board is
           painted, not lit.

           NEVER use text-gray-500 here: #6b7280 measures only 4.35 on
           this page's #f2f5fa ground. Use .es-desk-muted (6.62 on the
           ground, 7.23 on a white card, 7.42 on a dark card).

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-desk-page { background-color: #f2f5fa; color: #141b26; }
        .dark .es-desk-page { background-color: #0b1119; color: #e9eef7; }
        .es-desk-ink { color: #141b26; }
        .dark .es-desk-ink { color: #e9eef7; }
        .es-desk-muted { color: #4d5866; }
        .dark .es-desk-muted { color: #9aabc4; }
        .es-desk-accent { color: #1d4ed8; }
        .dark .es-desk-accent { color: #a8c3ff; }
        /* Always-lit accent, for use inside the fixed dark objects. */
        .es-desk-lit { color: #a8c3ff; }

        .text-gradient-desk {
            background-image: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 55%, #0e7490 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .text-gradient-desk {
            background-image: linear-gradient(135deg, #a8c3ff 0%, #93c5fd 55%, #67e8f9 100%);
        }
        /* The fixed dark objects need the light stops unconditionally.
           `background-image`, never the `background` shorthand: the
           shorthand resets background-clip and the text goes solid. */
        .es-desk-band .text-gradient-desk,
        .es-desk-board .text-gradient-desk {
            background-image: linear-gradient(135deg, #a8c3ff 0%, #93c5fd 55%, #67e8f9 100%);
        }

        /* --- Cards and secondary surfaces ---------------------------- */
        .es-desk-card {
            border: 1px solid rgba(20, 27, 38, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-desk-card {
            border-color: rgba(233, 238, 247, 0.12);
            background-color: #151b22;
        }
        .es-desk-sub {
            background-color: #e7ebf3;
        }
        .dark .es-desk-sub { background-color: #1b222a; }
        .es-desk-divide { border-color: rgba(20, 27, 38, 0.1); }
        .dark .es-desk-divide { border-color: rgba(233, 238, 247, 0.12); }

        /* --- Eyebrow / small caps ----------------------------------- */
        .es-desk-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4d5866;
        }
        .dark .es-desk-tag { color: #9aabc4; }
        .es-desk-band .es-desk-tag,
        .es-desk-board .es-desk-tag { color: #a8c3ff; }

        /* --- THE DIRECTORY BOARD ------------------------------------ */
        .es-desk-board {
            background-color: #101a2b;
            background-image: linear-gradient(168deg, #16233a 0%, #101a2b 52%, #0b1220 100%);
            border: 1px solid rgba(168, 195, 255, 0.16);
            border-radius: 1.25rem;
            box-shadow: inset 0 1px 0 rgba(233, 238, 247, 0.07), 0 24px 50px -28px rgba(9, 14, 24, 0.7);
        }
        .es-desk-board-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.15rem 1.1rem 0.8rem;
            border-bottom: 1px solid rgba(168, 195, 255, 0.16);
        }
        .es-desk-board-list { padding: 0.35rem 0.75rem 0.6rem; }
        /* Engraved rows: a hairline between them, no leader dots. */
        .es-desk-board-list li + li { border-top: 1px solid rgba(168, 195, 255, 0.13); }
        .es-desk-board-row {
            display: flex;
            align-items: baseline;
            gap: 0.7rem;
            padding: 0.6rem 0.35rem;
            transition: background-color 0.2s ease;
        }
        .es-desk-board-row:hover { background-color: rgba(168, 195, 255, 0.07); }
        .es-desk-board-num {
            flex: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.75rem;
            padding: 0.1rem 0.35rem;
            border-radius: 0.3rem;
            background-color: rgba(168, 195, 255, 0.14);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #a8c3ff;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .es-desk-board-row:hover .es-desk-board-num { background-color: #a8c3ff; color: #101a2b; }
        .es-desk-board-name {
            flex: 1 1 auto;
            min-width: 0;
            font-size: 0.94rem;
            font-weight: 600;
            color: #e9eef7;
        }
        /* The right-hand column a rundown board cannot carry: which filing
           references live down this corridor. */
        .es-desk-board-range {
            flex: none;
            white-space: nowrap;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            color: #9aabc4;
            transition: color 0.2s ease;
        }
        .es-desk-board-row:hover .es-desk-board-range { color: #e9eef7; }
        .es-desk-board-foot {
            padding: 0.85rem 1.1rem 1.15rem;
            border-top: 1px solid rgba(168, 195, 255, 0.16);
            font-size: 0.75rem;
            color: #9aabc4;
        }

        /* --- Corridor plate: the number + name that opens a group ---- */
        .es-desk-plate {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.4rem 0.95rem 0.4rem 0.55rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(20, 27, 38, 0.16);
            background-color: #ffffff;
        }
        .dark .es-desk-plate {
            border-color: rgba(233, 238, 247, 0.18);
            background-color: #151b22;
        }
        .es-desk-plate-num {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.45rem;
            border-radius: 0.3rem;
            background-color: #1e3a8a;
            color: #ffffff;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.06em;
        }
        .dark .es-desk-plate-num { background-color: #a8c3ff; color: #0b1119; }
        .es-desk-plate-name {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #141b26;
        }
        .dark .es-desk-plate-name { color: #e9eef7; }

        .es-desk-back {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #1d4ed8;
        }
        .dark .es-desk-back { color: #a8c3ff; }
        .es-desk-back:hover { text-decoration: underline; }

        /* --- THE DOCKET: one filed answer --------------------------- */
        .es-desk-docket {
            border: 1px solid rgba(20, 27, 38, 0.12);
            border-radius: 0.85rem;
            background-color: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .dark .es-desk-docket {
            border-color: rgba(233, 238, 247, 0.12);
            background-color: #151b22;
        }
        .es-desk-docket[open] { box-shadow: 0 14px 30px -22px rgba(15, 25, 45, 0.55); }
        .es-desk-summary {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 1rem 1.15rem;
            cursor: pointer;
        }
        .es-desk-ref {
            flex: none;
            padding-top: 0.1rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #1d4ed8;
        }
        .dark .es-desk-ref { color: #a8c3ff; }
        .es-desk-q {
            flex: 1 1 auto;
            min-width: 0;
            font-weight: 600;
            color: #141b26;
        }
        .dark .es-desk-q { color: #e9eef7; }
        .es-desk-arrow {
            flex: none;
            margin-top: 0.15rem;
            color: #4d5866;
            transition: transform 0.25s ease;
        }
        .dark .es-desk-arrow { color: #9aabc4; }
        .es-desk-docket[open] .es-desk-arrow { transform: rotate(180deg); }
        .es-desk-answer { padding: 0 1.15rem 1.15rem 3.05rem; }
        .es-desk-refer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem 1rem;
            margin-top: 0.85rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(20, 27, 38, 0.1);
        }
        .dark .es-desk-refer { border-top-color: rgba(233, 238, 247, 0.12); }

        /* --- THE RATE CARD ----------------------------------------- */
        .es-desk-rate { width: 100%; border-collapse: collapse; text-align: left; }
        .es-desk-rate th,
        .es-desk-rate td {
            padding: 0.7rem 0.85rem;
            font-size: 0.86rem;
            vertical-align: top;
            border-top: 1px solid rgba(20, 27, 38, 0.1);
        }
        .dark .es-desk-rate th,
        .dark .es-desk-rate td { border-top-color: rgba(233, 238, 247, 0.12); }
        .es-desk-rate thead th {
            border-top: 0;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4d5866;
            white-space: nowrap;
        }
        .dark .es-desk-rate thead th { color: #9aabc4; }
        .es-desk-rate tbody th {
            font-weight: 600;
            color: #141b26;
        }
        .dark .es-desk-rate tbody th { color: #e9eef7; }
        .es-desk-cell { color: #4d5866; }
        .dark .es-desk-cell { color: #9aabc4; }
        .es-desk-yes { font-weight: 700; color: #1e3a8a; }
        .dark .es-desk-yes { color: #a8c3ff; }
        .es-desk-scroll { overflow-x: auto; }

        /* --- Chips, plan pills, links, buttons ---------------------- */
        .es-desk-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(20, 27, 38, 0.14);
            background-color: rgba(255, 255, 255, 0.75);
            color: #4d5866;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-desk-chip {
            border-color: rgba(233, 238, 247, 0.16);
            background-color: #151b22;
            color: #9aabc4;
        }
        .es-desk-link { color: #1d4ed8; font-weight: 500; }
        .es-desk-link:hover { color: #141b26; text-decoration: underline; }
        .dark .es-desk-link { color: #a8c3ff; }
        .dark .es-desk-link:hover { color: #e9eef7; }
        /* The board and the band are dark in BOTH colour modes, so links
           inside them take the light ink unconditionally. */
        .es-desk-band .es-desk-link,
        .es-desk-board .es-desk-link { color: #a8c3ff; }
        .es-desk-band .es-desk-link:hover,
        .es-desk-board .es-desk-link:hover { color: #e9eef7; }

        .es-desk-btn {
            background-color: #1e40af;
            color: #ffffff;
            box-shadow: 0 18px 36px -16px rgba(30, 64, 175, 0.55);
        }
        .es-desk-btn:hover { background-color: #1b3893; box-shadow: 0 22px 44px -16px rgba(30, 64, 175, 0.65); }
        .dark .es-desk-btn { background-color: #a8c3ff; color: #0b1119; }
        .dark .es-desk-btn:hover { background-color: #c2d5ff; }
        /* Inside the fixed dark objects the button is the light one in both modes. */
        .es-desk-band .es-desk-btn { background-color: #a8c3ff; color: #0b1119; }
        .es-desk-band .es-desk-btn:hover { background-color: #c2d5ff; }

        .es-desk-ghost {
            border: 1px solid rgba(233, 238, 247, 0.28);
            background-color: rgba(233, 238, 247, 0.08);
            color: #e9eef7;
        }
        .es-desk-ghost:hover { background-color: rgba(233, 238, 247, 0.16); }

        /* --- Hover treatment shared by dockets and referral cards --- */
        .es-desk-hover:hover { border-color: rgba(29, 78, 216, 0.42); }
        .dark .es-desk-hover:hover { border-color: rgba(168, 195, 255, 0.42); }
        .es-desk-hover:hover .es-desk-hover-title { color: #1d4ed8; }
        .dark .es-desk-hover:hover .es-desk-hover-title { color: #a8c3ff; }
        .es-desk-hover-title { transition: color 0.2s ease; }

        /* --- THE REFERRAL BAND: fixed dark in both colour modes ----- */
        .es-desk-band {
            background-color: #0c1420;
            background-image: radial-gradient(125% 105% at 50% 0%, #16243a 0%, #0f1b2b 52%, #080e17 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(233, 238, 247, 0.05);
        }
        /* Shared classes carry their own `.dark` rules in marketing.css, so a
           fixed object has to pin them back. Verified with --bands. */
        .es-desk-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 238, 247, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 238, 247, 0.05) 1px, transparent 1px);
        }
        .es-desk-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-desk-band .es-claim:focus-within {
            border-color: rgba(168, 195, 255, 0.78);
            box-shadow: 0 0 0 4px rgba(168, 195, 255, 0.24);
        }
        .es-desk-slip {
            border: 1px solid rgba(233, 238, 247, 0.14);
            border-radius: 1rem;
            background-color: rgba(233, 238, 247, 0.05);
            transition: border-color 0.2s ease;
        }
        .es-desk-slip:hover { border-color: rgba(168, 195, 255, 0.45); }

        /* --- Counter rule: the double hairline under a section head -- */
        .es-desk-rule {
            height: 3px;
            border-top: 1px solid rgba(20, 27, 38, 0.16);
            border-bottom: 1px solid rgba(20, 27, 38, 0.16);
        }
        .dark .es-desk-rule {
            border-top-color: rgba(233, 238, 247, 0.16);
            border-bottom-color: rgba(233, 238, 247, 0.16);
        }
        .es-desk-band .es-desk-rule {
            border-top-color: rgba(233, 238, 247, 0.16);
            border-bottom-color: rgba(233, 238, 247, 0.16);
        }

        /* --- Shared-system recolours (brand blue by default) -------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(168, 195, 255, 0.11), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.62); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(168, 195, 255, 0.62); }
        .es-dot.is-active .es-dot-pip { background: #1e40af; }
        .dark .es-dot.is-active .es-dot-pip { background: #a8c3ff; }

        /* --- The plaque sheen: one slow pass, gated ----------------- */
        .es-desk-sheen {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            background: linear-gradient(105deg, transparent 38%, rgba(233, 238, 247, 0.07) 50%, transparent 62%);
            background-size: 260% 100%;
            animation: es-desk-sheen 11s ease-in-out infinite;
        }
        @keyframes es-desk-sheen {
            0%, 72%, 100% { background-position: 150% 0; }
            36% { background-position: -50% 0; }
        }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. */
        #es-desk-page a:focus-visible,
        #es-desk-page summary:focus-visible,
        #es-desk-page button:focus-visible {
            outline: 2px solid #1e40af;
            outline-offset: 3px;
        }
        .dark #es-desk-page a:focus-visible,
        .dark #es-desk-page summary:focus-visible,
        .dark #es-desk-page button:focus-visible {
            outline-color: #a8c3ff;
        }
        .es-desk-board a:focus-visible,
        .es-desk-band a:focus-visible,
        .es-desk-band button:focus-visible {
            outline-color: #a8c3ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-desk-sheen { animation: none; }
            .es-desk-arrow { transition: none; }
        }
    </style>

    @php
        // ------------------------------------------------------------------
        // Every answer on this page is a factual claim, so each one is
        // traceable to docs/FEATURES.md or to code. Plan tiers in particular:
        // newsletters are FREE (10/month), analytics is FREE, SELLING TICKETS
        // is free up to 25 paid tickets a month with no platform fee (the
        // ticketing extras - QR check-in, waitlist, promo codes, passes,
        // add-ons, CSV export - are Pro), APPOINTMENT BOOKING is free with one
        // appointment type, custom domains and multiple team members are
        // Enterprise, and the free plan has exactly ONE team member.
        // ------------------------------------------------------------------
        $github = 'https://github.com/eventschedule/eventschedule';

        // Prices come from the marketing.* view composer, the same values /pricing renders,
        // so the rate card cannot quietly disagree with it.
        $trialDays = (int) config('app.trial_days', 7);

        $faqGroups = [
            [
                'id' => 'start',
                'no' => '01',
                'name' => 'Getting started',
                'note' => 'What the thing is, and what it takes to have one.',
                'items' => [
                    [
                        'q' => 'What is Event Schedule?',
                        'a' => 'Event Schedule is an open-source platform for publishing a shareable event calendar and, when you want it, selling tickets from it. A musician posting gig dates, a venue publishing a lineup and a food truck posting where it will be parked all end up with the same object: one schedule at its own address, with the events on it, ready to send people to or embed in a site you already have.',
                        'links' => [['Who uses it', marketing_url('/use-cases')]],
                    ],
                    [
                        'q' => 'Do I need technical skills to use it?',
                        'a' => 'No. You create a schedule, add events, and share the link. If the details are already written down somewhere, paste the text or drop in a flyer image and AI parsing fills the fields for you to check before you save. That is on the free plan, up to 10 parses a day.',
                        'links' => [['How the AI import works', marketing_url('/features/ai')]],
                    ],
                    [
                        'q' => 'Is Event Schedule really free?',
                        'a' => 'Yes, and the free plan is not a trial that quietly expires. Unlimited events, a mobile-friendly public page at your own address, two-way Google, Outlook and CalDAV sync, sub-schedules, recurring events, free registration with a capacity limit, built-in analytics, the embeddable calendar, backup and restore, and newsletters at 10 emails a month all cost nothing, permanently. Selling tickets is on the free plan too, capped at 25 paid ones a month rather than charged for. The paid plans lift that ceiling and add a short list of other things, and the rate card above sets out exactly which.',
                        'links' => [['Pricing', marketing_url('/pricing')]],
                    ],
                    [
                        'q' => 'Can I embed my schedule on my website?',
                        'a' => 'Yes, on the free plan. Every schedule has an iframe embed code you copy into your own site. It is a window onto the same schedule rather than a copy of it, so it is current the moment you change an event.',
                        'links' => [['Sharing and embedding', marketing_url('/docs/sharing')], ['Embed calendar', marketing_url('/features/embed-calendar')]],
                    ],
                ],
            ],
            [
                'id' => 'cost',
                'no' => '02',
                'name' => 'What it costs',
                'note' => 'Three plans, two of them paid, and nothing taken from the door on any of them.',
                'items' => [
                    [
                        'q' => "What's the difference between Free and Pro?",
                        'a' => 'Free gives you the calendar: unlimited events, two-way calendar sync, sub-schedules, recurring events, built-in analytics, the embed, and newsletters at 10 emails a month. Pro is ' . plan_price($proMonthly) . ' a month and is mostly about selling: ticketing with QR check-in, the check-in dashboard, passes, promo codes, gift cards, appointment booking, the sold-out waitlist, custom fields, generated event graphics, webhooks, the REST API, custom CSS, and taking the Event Schedule branding off your public pages. It also raises newsletters to 100 emails a month.',
                        'links' => [['Compare the plans', marketing_url('/pricing')]],
                    ],
                    [
                        'q' => 'What does Enterprise add?',
                        'a' => 'Enterprise is ' . plan_price($entMonthly) . ' a month and adds a custom domain for your schedule, Internal and Unlisted event visibility with an optional password, up to five team members with availability tracking, scheduled graphic emails, event creation over WhatsApp, the AI generators for schedule and event copy, agenda scanning, and newsletters at 1,000 emails a month. Priority support comes with it.',
                        'links' => [['Pricing', marketing_url('/pricing')]],
                    ],
                    [
                        'q' => 'Do you take a percentage of my ticket sales?',
                        'a' => 'No. Event Schedule takes no cut of ticket revenue at all. Money moves through your own Stripe account, so the only thing off the top is Stripe\'s own processing fee, currently around 2.9% plus 30 cents a transaction in the United States. The rest is yours, and it lands in your account rather than in ours.',
                        'links' => [['How Stripe connects', marketing_url('/stripe')]],
                    ],
                    [
                        'q' => 'What happens when my free trial ends?',
                        'a' => 'The ' . $trialDays . '-day trial applies to a paid plan, not to the product. When it ends, the card on file is charged for Pro at ' . plan_price($proMonthly) . ' a month or ' . plan_price($proYearly) . ' a year, or Enterprise at ' . plan_price($entMonthly) . ' a month or ' . plan_price($entYearly) . ' a year. Cancel during the trial and nothing is charged. Cancel later and the schedule drops back to the free plan with every event and every record intact. You lose the paid features, not the calendar.',
                    ],
                    [
                        'q' => 'Can I cancel anytime?',
                        'a' => 'Yes. There is no contract and no cancellation fee. The schedule reverts to the free plan and stays live at the same address. You can also take a copy with you first: backup and restore is on the free plan, so an export is not something you have to pay to be allowed.',
                    ],
                    [
                        'q' => 'Can I add my team?',
                        'a' => 'Free and Pro include one team member, which is you. Multiple team members are an Enterprise feature, capped at five, and Enterprise adds an availability tab for tracking who is around on which day. Being blunt about it, do not sign up for the free plan expecting to invite your whole staff.',
                        'links' => [['Team members', marketing_url('/docs/managing-schedules#team')]],
                    ],
                ],
            ],
            [
                'id' => 'tickets',
                'no' => '03',
                'name' => 'Tickets and money',
                'note' => 'Selling tickets is on every plan, with no platform fee. What you charge stays between you and your audience.',
                'items' => [
                    [
                        'q' => 'How do I start selling tickets?',
                        'a' => 'Connect your Stripe account, which takes a couple of minutes, then add ticket types to an event with a name, a price and a quantity. Buyers pay on the event page and get an emailed ticket carrying a QR code. Ticketing is on the Pro plan.',
                        'links' => [['Ticketing', marketing_url('/features/ticketing')], ['Connect Stripe', marketing_url('/stripe')]],
                    ],
                    [
                        'q' => 'Can I create different ticket types for one event?',
                        'a' => 'Yes, as many as the event needs: general admission, early bird, concession, a group rate. Each type carries its own price, quantity, description and sales window, and the window is a pair of exact dates and times rather than a rule about the door. Quantity is counted per occurrence date, so a weekly event does not share one pool of stock across every week.',
                    ],
                    [
                        'q' => 'How do QR check-ins work?',
                        'a' => 'Each ticket email carries a QR code. Open the scan screen on any phone or tablet, point it at the code, and the ticket is verified and marked as used. A second scan of the same code is caught rather than argued about at the door. No dedicated hardware.',
                        'links' => [['Check-in', marketing_url('/docs/tickets')]],
                    ],
                    [
                        'q' => 'Can I track check-ins in real time?',
                        'a' => 'Yes, on Pro. The check-in dashboard shows how many people are in against how many sold, broken down by ticket type, with a recent activity feed of names and times. It refreshes itself every ten seconds and works on the phone in your hand at the door.',
                        'links' => [['Check-in dashboard', marketing_url('/docs/tickets#checkin-dashboard')]],
                    ],
                    [
                        'q' => 'Can I collect additional information from ticket buyers?',
                        'a' => 'Yes, with custom fields on Pro. A field can be a short text answer, a longer one, a dropdown, a multi-select, a date or a yes/no switch, and you attach it either to the order or to each individual ticket. Access needs, a meal choice, a shirt size, a school contact: ask at the point of purchase and the answer arrives with the sale.',
                        'links' => [['Custom fields', marketing_url('/features/custom-fields')]],
                    ],
                    [
                        'q' => 'Can I offer free tickets alongside paid ones?',
                        'a' => 'Yes. Free and paid ticket types sit on the same event, which covers comps, volunteer passes, and a free tier running alongside paid admission.',
                    ],
                    [
                        'q' => 'Can I take sign-ups for a free event without paying for Pro?',
                        'a' => 'Yes. Free registration is on the free plan, with an optional capacity limit counted per date, so a weekly session can hold twenty people this Thursday and twenty more next Thursday. Priced ticket types, card payment through your own Stripe account and scanning the QR at the door are all on the free plan as well, capped at 25 paid tickets a month. Pro is what removes the cap and adds the live check-in dashboard.',
                        'links' => [['Registration', marketing_url('/docs/tickets#registration')]],
                    ],
                    [
                        'q' => 'What happens when tickets sell out?',
                        'a' => 'A waitlist button appears in place of the sold-out ticket and people join with a name and an email. If stock comes back from a cancellation or a refund, the next person in line is emailed and has a 24-hour window to buy before the offer passes to the person behind them. Waitlists are on Pro.',
                        'links' => [['Waitlist', marketing_url('/docs/tickets#waitlist')]],
                    ],
                ],
            ],
            [
                'id' => 'dates',
                'no' => '04',
                'name' => 'Calendars and dates',
                'note' => 'Where the dates live, and what a schedule is actually made of.',
                'items' => [
                    [
                        'q' => 'Does Event Schedule sync with Google Calendar?',
                        'a' => 'Yes, both ways, on the free plan. Events you create here appear in the Google Calendar you connect, and events you add there come back here. Google pushes changes over a webhook rather than waiting for a nightly job. Outlook and Microsoft 365 sync the same way, and CalDAV covers the rest. You also choose, per schedule, what should happen locally when an event is deleted in the external calendar: keep it, mark it cancelled, or delete it too.',
                        'links' => [['Google Calendar sync', marketing_url('/google-calendar')], ['All calendar sync', marketing_url('/features/calendar-sync')]],
                    ],
                    [
                        'q' => 'Can my audience add events to their personal calendars?',
                        'a' => 'Yes. Every event page carries add-to-calendar buttons for Google, Apple and Outlook, and the Apple one is the .ics download, so anything that reads a calendar file is covered by the same button. On a recurring event each date gets its own set rather than one link for the whole series.',
                    ],
                    [
                        'q' => 'Can I create recurring events?',
                        'a' => 'Yes, on the free plan. An event can repeat daily, weekly, every few weeks on the days you choose, monthly on the same date, monthly on the same weekday, or yearly. Give the recurrence an end and it stops on its own: a closing date, or after a set number of occurrences. Individual dates can be added or taken out as exceptions, which is how a holiday or a one-off move gets handled without rebuilding the series.',
                        'links' => [['Recurring events', marketing_url('/features/recurring-events')]],
                    ],
                    [
                        'q' => "What's the difference between a schedule, a sub-schedule, and an event?",
                        'a' => 'A schedule is the calendar itself, the thing with a name and an address, like The Blue Note or DJ Sarah. Sub-schedules are strands within it that sort and colour-code what is on, such as a series or a strand of programming, so somebody can look at one strand instead of the whole year. Events are the individual dates. One thing worth knowing: a sub-schedule organises, it does not hide. Hiding something is a visibility setting on the event.',
                        'links' => [['Sub-schedules', marketing_url('/features/sub-schedules')]],
                    ],
                ],
            ],
            [
                'id' => 'audience',
                'no' => '05',
                'name' => 'Your audience',
                'note' => 'Following, emailing, and one thing this deliberately does not do.',
                'items' => [
                    [
                        'q' => 'Can I send newsletters to my followers?',
                        'a' => 'Yes, and on the free plan, not Pro. You compose in a block builder starting from a template, pull in your event listings, and send to followers, ticket buyers, a sub-schedule\'s audience or a list you import. Opens and clicks are reported afterwards. What changes with the plan is volume: 10 emails a month on Free, 100 on Pro, 1,000 on Enterprise, counted per recipient, so one letter to 40 followers uses 40 of the allowance. Uploading your own images into a newsletter is the one part that needs Pro.',
                        'links' => [['Newsletters', marketing_url('/docs/newsletters')]],
                    ],
                    [
                        'q' => 'Does Event Schedule have email marketing?',
                        'a' => 'The newsletter tool is the email marketing. It has reusable audience segments, imported lists, A/B tests, a scheduled send, and open and click tracking. There is no separate product to buy and no second bill for it.',
                        'links' => [['Recipients and segments', marketing_url('/docs/newsletters#recipients')]],
                    ],
                    [
                        // app:send-event-announcements, hourly on both rails. It reaches CONFIRMED
                        // role_subscribers only (AudienceResolver::announcementRecipients), floored at
                        // usage.audience_announcement_min_hours, and does NOT draw on the newsletter
                        // allowance. Account followers are a different list and get newsletters only.
                        'q' => 'Do my followers get an email automatically when I add an event?',
                        'a' => 'Some of them do, and the difference matters. Anyone who gave your schedule an email address and confirmed it is emailed a digest when you publish new public events, batched so a season announced in one sitting is one message rather than twenty, and never more often than once every three days. It does not count against your newsletter allowance. Somebody who followed you while signed in to their own account is on a separate list, and that one only ever hears from a newsletter you write yourself. You can switch the automatic digest off per schedule under Settings, Notifications.',
                        'links' => [['Followers', marketing_url('/docs/managing-schedules#followers')]],
                    ],
                    [
                        'q' => 'Can others submit events to my schedule?',
                        'a' => 'Yes. Turn on event requests and your schedule gets a public submission form. On venue and curator schedules you can also require approval, so nothing appears until you have looked at it. Submissions land in a pending queue and you are emailed when one arrives.',
                        'links' => [['Event requests', marketing_url('/docs/creating-schedules#engagement-requests')]],
                    ],
                ],
            ],
            [
                'id' => 'privacy',
                'no' => '06',
                'name' => 'Privacy and visibility',
                'note' => 'Who sees what, where the card details go, and what we do not do with any of it.',
                'items' => [
                    [
                        'q' => 'Who can see my schedule?',
                        'a' => 'By default the schedule is public, because being findable is usually the whole point of having one. You decide what appears on it, and every event carries its own visibility setting, so the page can be live while half of what is planned on it is not.',
                        'links' => [['Event visibility', marketing_url('/docs/creating-events#draft')]],
                    ],
                    [
                        'q' => 'Can I hide an event until I am ready?',
                        'a' => 'Yes. Draft is on the free plan: the event exists for you and is published nowhere, not on the page, the feeds, the graphics, a newsletter or the calendar sync. Enterprise adds two more options: Internal, which stays members-only for good, and Unlisted, which is off the schedule but reachable by direct link, with an optional password.',
                        'links' => [['Internal and unlisted events', marketing_url('/docs/creating-events#privacy')]],
                    ],
                    [
                        'q' => 'Is payment processing secure?',
                        'a' => 'Card details never reach us. Checkout runs through Stripe, which is PCI-DSS compliant, and the money lands in your own Stripe account. We hold the sale record, not the card number.',
                        'links' => [['Stripe', marketing_url('/stripe')]],
                    ],
                    [
                        'q' => 'Do you sell my data?',
                        'a' => 'No. Your data is not sold, shared or used for advertising. The built-in analytics are first-party and count page views without loading a third-party tracker. If that is still not close enough to the bone, selfhost it and nothing leaves your own server.',
                        'links' => [['Selfhosting', marketing_url('/selfhost')]],
                    ],
                ],
            ],
            [
                'id' => 'look',
                'no' => '07',
                'name' => 'Look and language',
                'note' => 'Making it look like you, in the language your audience reads.',
                'items' => [
                    [
                        'q' => "Can I customize my schedule's appearance?",
                        'a' => 'Yes. Accent colour, font, background, header image, profile image and a grid or list layout are all on the free plan, as is a logo-wall header that shows the venues you have played. Pro adds custom CSS for anything the settings do not reach, and takes the Event Schedule branding off your public pages. Enterprise puts the whole thing on a domain of your own.',
                        'links' => [['Schedule styling', marketing_url('/docs/schedule-styling')]],
                    ],
                    [
                        'q' => 'What languages does Event Schedule support?',
                        'a' => 'Twelve: Arabic, Dutch, English, Estonian, French, German, Hebrew, Italian, Portuguese, Romanian, Russian and Spanish. The right-to-left languages are laid out right to left rather than bolted onto a left-to-right page.',
                    ],
                    [
                        'q' => 'Does Event Schedule support multiple languages?',
                        'a' => 'Yes. Each schedule picks the language it is written in, so two schedules on one account can be in two different languages. You can also nominate one other language to translate into, and event names and descriptions are translated by AI into it with a language switch on your public page. That is free, and it is one target language at a time rather than all twelve at once.',
                        'links' => [['AI features', marketing_url('/features/ai')]],
                    ],
                ],
            ],
            [
                'id' => 'data',
                'no' => '08',
                'name' => 'What you can measure',
                'note' => 'Numbers you get, numbers you do not, and what the AI actually does.',
                'items' => [
                    [
                        'q' => 'Can I track who views my schedule?',
                        'a' => 'You get counts, not people, and being exact about it: the built-in dashboard reports page views over time, a desktop, mobile and tablet split, which countries those views came from, which domains referred them, and which UTM campaign tags they arrived with. It is on the free plan. What it does not report is any kind of person, not even a unique-visitor number, and it does not load a third-party tracker in order to get one.',
                        'links' => [['Analytics', marketing_url('/docs/analytics')]],
                    ],
                    [
                        'q' => 'Does Event Schedule integrate with Google Analytics?',
                        'a' => 'There is no Google Analytics integration, on purpose. The built-in dashboard covers views, devices, countries, referring domains and UTM campaigns first-party, so your audience does not get handed to an ad network in order for you to learn that Tuesday was busy.',
                        'links' => [['Analytics', marketing_url('/features/analytics')]],
                    ],
                    [
                        'q' => 'Can I import events from a flyer or image?',
                        'a' => 'Yes. Paste text or drop in an image and AI parsing pulls out the name, date, time, venue and description and fills the event form, where you check it before saving. It is on the free plan at 10 parses a day, 50 on Pro and 100 on Enterprise. It fills a form in; it does not publish anything by itself.',
                        'links' => [['Creating events', marketing_url('/docs/creating-events')], ['AI features', marketing_url('/features/ai')]],
                    ],
                ],
            ],
            [
                'id' => 'code',
                'no' => '09',
                'name' => 'Open source and selfhosting',
                'note' => 'The part you can read, run, and take with you.',
                'items' => [
                    [
                        'q' => 'Is Event Schedule open source?',
                        'a' => 'Yes, under the Attribution Assurance License. The whole application is on GitHub: read it, file an issue, send a patch, or fork it for something of your own.',
                        'links' => [['Open source', marketing_url('/open-source')], ['GitHub', $github, true]],
                    ],
                    [
                        'q' => 'Can I selfhost Event Schedule?',
                        'a' => 'Yes. Run it on your own server and it resolves to the Enterprise feature set at no cost, with the data on your own hardware. A selfhosted install even has a couple of things the hosted one does not, such as importing events from a URL or a city search, and one-click updates.',
                        'links' => [['Selfhosting guide', marketing_url('/selfhost')], ['GitHub', $github, true]],
                    ],
                    [
                        'q' => 'Is there an API?',
                        'a' => 'Yes, on Pro. The REST API covers events, schedules, sub-schedules and sales, plus read endpoints for post-event feedback and fan-submitted content, and there are outgoing webhooks for sales, event changes and check-ins.',
                        'links' => [['API reference', marketing_url('/docs/developer/api')]],
                    ],
                ],
            ],
        ];

        // One flat list, so the visible dockets and the FAQPage schema can
        // never drift apart: both read this array.
        $faqs = [];
        foreach ($faqGroups as $faqGroup) {
            foreach ($faqGroup['items'] as $faqItem) {
                $faqs[] = $faqItem;
            }
        }
        $faqCount = count($faqs);

        // The rate card. Verified row by row against docs/FEATURES.md.
        // A cell takes affirmative navy ink only when it is an INCLUSION. Denials
        // and ceilings take neutral ink, so no limit can read as a feature you are
        // being sold: that covers "No", a bare quantity (a 10-email allowance, a
        // one-member cap) and an "Up to 5". "Unlimited", "Zero" and a price are
        // inclusions and stay affirmative.
        $rateAffirmative = fn (string $cell) => ! (
            $cell === 'No'
            // A bare quantity (10 emails, 1 member) or one carrying its unit ("1 type").
            || preg_match('/^\d[\d,]*(\s|$)/', $cell)
            || str_starts_with($cell, 'Up to ')
        );
        $rateRows = [
            ['What it costs', plan_price(0) . ', permanently', plan_price($proMonthly) . ' / month or ' . plan_price($proYearly) . ' / year', plan_price($entMonthly) . ' / month or ' . plan_price($entYearly) . ' / year'],
            ['Events on your schedule', 'Unlimited', 'Unlimited', 'Unlimited'],
            ['Public page, embed and QR code', 'Yes', 'Yes', 'Yes'],
            ['Two-way Google, Outlook and CalDAV sync', 'Yes', 'Yes', 'Yes'],
            ['Built-in analytics', 'Yes', 'Yes', 'Yes'],
            ['Free registration with a capacity limit', 'Yes', 'Yes', 'Yes'],
            ['Newsletter emails a month', '10', '100', '1,000'],
            ['Paid tickets you can sell a month', 'Up to 25', 'Unlimited', 'Unlimited'],
            ['Platform fee on ticket sales', 'Zero', 'Zero', 'Zero'],
            ['Scan tickets at the door', 'Yes', 'Yes', 'Yes'],
            ['Live check-in dashboard, waitlist, promo codes and passes', 'No', 'Yes', 'Yes'],
            ['Appointment booking', '1 type', 'Unlimited types', 'Unlimited types'],
            ['Remove Event Schedule branding', 'No', 'Yes', 'Yes'],
            ['Team members', '1', '1', 'Up to 5'],
            ['Custom domain, Internal and Unlisted events', 'No', 'No', 'Yes'],
        ];

        $quickAnswers = [
            [
                'q' => 'Is it free?',
                'a' => 'Yes, and permanently. The calendar, the public page, calendar sync, analytics and 10 newsletter emails a month cost nothing and always did.',
                'ref' => '01.03',
                'href' => '#start',
            ],
            [
                'q' => 'Do you take a cut?',
                'a' => 'No. Zero platform fees on ticket sales. The money goes through your own Stripe account, so the only deduction is Stripe\'s.',
                'ref' => '02.03',
                'href' => '#cost',
            ],
            [
                'q' => 'Can I leave?',
                'a' => 'Yes. Cancel with no fee and the schedule stays live on the free plan. Backup and restore is free, so an export is never held back.',
                'ref' => '02.05',
                'href' => '#cost',
            ],
        ];

        $dotSections = [
            ['top', 'The desk'],
            ['quick', 'Three answers'],
            ['rate', 'The rate card'],
            ['start', '01 Getting started'],
            ['cost', '02 What it costs'],
            ['tickets', '03 Tickets and money'],
            ['dates', '04 Calendars and dates'],
            ['audience', '05 Your audience'],
            ['privacy', '06 Privacy'],
            ['look', '07 Look and language'],
            ['data', '08 What you can measure'],
            ['code', '09 Open source'],
            ['refer', 'Not answered here'],
            ['claim', 'Sign up'],
        ];
    @endphp

    <x-seo.faq-schema :items="$faqs" />

    <div id="es-desk-page" class="es-desk-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the desk, and the directory board behind it         -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(78svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(30, 64, 175, 0.2), rgba(30, 64, 175, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(14, 116, 144, 0.14), rgba(14, 116, 144, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-desk-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="es-desk-muted text-sm font-medium tracking-wide">{{ $faqCount }} answers, filed and numbered</span>
                    </div>

                    <h1 class="es-balance es-desk-ink mb-8 text-[2.5rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Ask at the desk.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Nobody needs your <span class="text-gradient-desk">card.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-desk-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Everything anybody asks before signing up, grouped by corridor and answered in order. Plans, tickets, calendars, privacy, and the parts we do not do. Still stuck? <a href="mailto:{{ config('app.support_email') }}" class="es-desk-link">Email us</a> and a person replies.
                    </p>

                    <div class="es-fade-up es-d-3 mb-8 flex flex-col items-start gap-3 sm:flex-row">
                        <a href="#rate" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See what free includes
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-desk-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <div class="es-fade-up es-d-4 flex flex-wrap gap-2">
                        <span class="es-desk-chip">Free plan, no expiry</span>
                        <span class="es-desk-chip">Zero platform fees</span>
                        <span class="es-desk-chip">Open source</span>
                    </div>
                </div>

                <!-- The directory board: the hero device AND the page's nav. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <nav class="es-desk-board relative overflow-hidden" aria-label="Question directory">
                        <div class="es-desk-sheen" aria-hidden="true"></div>
                        <div class="relative">
                            {{-- A real header row: the left column names the corridors,
                                 the right one names what the right column holds. --}}
                            <div class="es-desk-board-head">
                                <span class="es-desk-tag">Directory</span>
                                <span class="es-desk-tag">References</span>
                            </div>
                            <ul class="es-desk-board-list">
                                @foreach ($faqGroups as $boardGroup)
                                    <li>
                                        <a href="#{{ $boardGroup['id'] }}" class="es-desk-board-row">
                                            <span class="es-desk-board-num">{{ $boardGroup['no'] }}</span>
                                            <span class="es-desk-board-name">{{ $boardGroup['name'] }}</span>
                                            <span class="es-desk-board-range">{{ $boardGroup['no'] }}.01 to {{ $boardGroup['no'] }}.{{ str_pad(count($boardGroup['items']), 2, '0', STR_PAD_LEFT) }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="es-desk-board-foot">
                                {{ $faqCount }} answers in {{ count($faqGroups) }} corridors. Longer form lives in the <a href="{{ route('marketing.docs') }}" class="es-desk-link">user guide</a>.
                            </p>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The three questions everyone opens with                   -->
    <!-- ============================================================ -->
    <section id="quick" class="scroll-mt-24 border-t es-desk-divide py-16 lg:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <p class="es-desk-tag mb-4" data-reveal>Asked first, every time</p>
                <h2 class="es-balance es-desk-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three answers before you <span class="text-gradient-desk">walk down a corridor.</span>
                </h2>
                <div class="es-desk-rule mx-auto mt-6 max-w-xs" aria-hidden="true"></div>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ($quickAnswers as $quick)
                    <div class="es-desk-card flex flex-col p-6" data-reveal="panel">
                        <div class="mb-3 flex items-baseline gap-2">
                            <span class="es-desk-ref">{{ $quick['ref'] }}</span>
                            <h3 class="es-desk-ink text-lg font-bold">{{ $quick['q'] }}</h3>
                        </div>
                        <p class="es-desk-muted text-sm leading-relaxed">{{ $quick['a'] }}</p>
                        <a href="{{ $quick['href'] }}" class="es-desk-link mt-auto inline-flex items-center gap-1 pt-4 text-sm">
                            The long answer
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The rate card on the counter (a real record table)        -->
    <!-- ============================================================ -->
    <section id="rate" class="scroll-mt-24 border-t es-desk-divide py-20 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <p class="es-desk-tag mb-4" data-reveal>On the counter</p>
                <h2 class="es-balance es-desk-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    The rate card, <span class="text-gradient-desk">including the rows that say no.</span>
                </h2>
                <p class="es-desk-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Thirteen rows, three plans. If a page ever tells you newsletters, analytics or scanning a ticket at the door are paid features here, that page is out of date.
                </p>
            </div>

            <div class="es-desk-card p-4 sm:p-6" data-reveal="panel">
                <div class="es-desk-scroll">
                    <table class="es-desk-rate">
                        <caption class="sr-only">What each Event Schedule plan includes, with monthly and yearly prices</caption>
                        <thead>
                            <tr>
                                <th scope="col">What you asked about</th>
                                <th scope="col">Free</th>
                                <th scope="col">Pro</th>
                                <th scope="col">Enterprise</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rateRows as [$rateLabel, $rateFree, $ratePro, $rateEnt])
                                <tr>
                                    <th scope="row">{{ $rateLabel }}</th>
                                    <td class="{{ $rateAffirmative($rateFree) ? 'es-desk-yes' : 'es-desk-cell' }}">{{ $rateFree }}</td>
                                    <td class="{{ $rateAffirmative($ratePro) ? 'es-desk-yes' : 'es-desk-cell' }}">{{ $ratePro }}</td>
                                    <td class="{{ $rateAffirmative($rateEnt) ? 'es-desk-yes' : 'es-desk-cell' }}">{{ $rateEnt }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-desk-muted es-desk-sub mt-4 rounded-xl p-4 text-sm">
                    A selfhosted install resolves to the Enterprise feature set at no cost. Paid plans start with a {{ $trialDays }}-day trial, and cancelling drops a schedule back to Free without taking the calendar down.
                    <a href="{{ marketing_url('/pricing') }}" class="es-desk-link">Full pricing</a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The corridors: nine groups of numbered dockets            -->
    <!-- ============================================================ -->
    @foreach ($faqGroups as $groupIndex => $group)
        <section id="{{ $group['id'] }}" class="scroll-mt-24 border-t es-desk-divide py-16 lg:py-20">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="es-desk-plate" data-reveal>
                            <span class="es-desk-plate-num">{{ $group['no'] }}</span>
                            <h2 class="es-desk-plate-name">{{ $group['name'] }}</h2>
                        </div>
                        <a href="#top" class="es-desk-back">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                            Back to the directory
                        </a>
                    </div>
                    <p class="es-desk-muted mt-4 max-w-2xl">{{ $group['note'] }}</p>
                    <div class="es-desk-rule mt-5" aria-hidden="true"></div>
                </div>

                <div class="space-y-3" data-reveal-group="60">
                    @foreach ($group['items'] as $itemIndex => $faq)
                        <details name="faq-{{ $group['id'] }}" class="es-desk-docket es-desk-hover" data-reveal>
                            <summary class="es-desk-summary">
                                <span class="es-desk-ref" aria-hidden="true">{{ $group['no'] }}.{{ str_pad($itemIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="es-desk-q es-desk-hover-title">{{ $faq['q'] }}</span>
                                <svg aria-hidden="true" class="es-desk-arrow h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </summary>
                            <div class="es-desk-answer">
                                <p class="faq-answer es-desk-muted leading-relaxed">{{ $faq['a'] }}</p>
                                @if (! empty($faq['links']))
                                    <div class="es-desk-refer">
                                        <span class="es-desk-tag">Referred to</span>
                                        @foreach ($faq['links'] as $link)
                                            @if (count($link) > 2)
                                                <a href="{{ $link[1] }}" target="_blank" rel="noopener noreferrer" class="es-desk-link inline-flex items-center gap-1 text-sm">
                                                    {{ $link[0] }}
                                                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                </a>
                                            @else
                                                <a href="{{ $link[1] }}" class="es-desk-link inline-flex items-center gap-1 text-sm">
                                                    {{ $link[0] }}
                                                    <svg aria-hidden="true" class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>

                @if ($groupIndex === count($faqGroups) - 1)
                    <p class="es-desk-muted mt-8 text-sm">
                        That is the last corridor. Anything still unanswered goes to a person, below.
                    </p>
                @endif
            </div>
        </section>
    @endforeach

    <!-- ============================================================ -->
    <!-- 5. The referral slip (fixed dark in both colour modes)       -->
    <!-- ============================================================ -->
    <section id="refer" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-desk-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-desk-tag mb-4" data-reveal>Not answered here</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        Then the desk hands you a <span class="es-desk-lit">slip.</span>
                    </h2>
                    <div class="es-desk-rule mx-auto mt-6 max-w-xs" aria-hidden="true"></div>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-desk-slip flex flex-col p-6" data-reveal="panel">
                        <h3 class="mb-2 text-lg font-bold text-white">The long version</h3>
                        <p class="mb-4 text-sm text-gray-400">The user guide walks through every screen, with the settings named as they appear in the admin portal.</p>
                        <a href="{{ route('marketing.docs') }}" class="es-desk-link mt-auto inline-flex items-center gap-1 text-sm">
                            Read the user guide
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                    <div class="es-desk-slip flex flex-col p-6" data-reveal="panel">
                        <h3 class="mb-2 text-lg font-bold text-white">A person</h3>
                        <p class="mb-4 text-sm text-gray-400">Write in with the question as you would ask it. There is no ticket queue to navigate and no chatbot in front of it.</p>
                        <a href="mailto:{{ config('app.support_email') }}" class="es-desk-link mt-auto inline-flex items-center gap-1 text-sm">
                            {{ config('app.support_email') }}
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        </a>
                    </div>
                    <div class="es-desk-slip flex flex-col p-6" data-reveal="panel">
                        <h3 class="mb-2 text-lg font-bold text-white">The source</h3>
                        <p class="mb-4 text-sm text-gray-400">If the honest answer is "read the code", it is all public. Open an issue, or check whether somebody already has.</p>
                        <a href="{{ $github }}" target="_blank" rel="noopener noreferrer" class="es-desk-link mt-auto inline-flex items-center gap-1 text-sm">
                            GitHub
                            <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    Nothing on this page is behind a sign-up, and neither is the free plan. Here is
                    <x-link href="{{ marketing_url('/why-create-account') }}" class="font-semibold text-white underline">what a free account unlocks</x-link>.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Where to next (related pages strip)                       -->
    <!-- ============================================================ -->
    <section class="border-t es-desk-divide py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-desk-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Where to next</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/pricing', 'Pricing', 'Every row of the rate card, in full'], ['/features', 'Features', 'What is actually in the product'], ['/use-cases', 'Use cases', 'The same tool, by who is using it'], ['/selfhost', 'Selfhosting', 'Run the whole thing yourself']] as [$nextHref, $nextName, $nextBlurb])
                    <a href="{{ marketing_url($nextHref) }}" class="es-desk-card es-desk-hover flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-desk-hover-title es-desk-ink mb-2 text-sm font-semibold">{{ $nextName }}</span>
                        <span class="es-desk-muted mb-4 text-xs leading-relaxed">{{ $nextBlurb }}</span>
                        <span class="es-desk-link mt-auto inline-flex items-center gap-1 text-xs">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Finale                                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-desk-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-desk-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Take a name at the desk. <span class="es-desk-lit">Nothing to pay.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Pick an address, add your first event, and share the link. Ticketing and the rest are there when you want them, and no card is asked for until then.
                    </p>

                    <div class="mx-auto mb-8 flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="mailto:{{ config('app.support_email') }}" class="es-desk-ghost inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200">
                            Contact us
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-desk-btn group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
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
                        <span class="es-desk-card pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
