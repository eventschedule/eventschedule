<x-marketing-layout>
    <x-slot name="title">Self-Hosting Terms - Event Schedule</x-slot>
    <x-slot name="description">Terms for self-hosting Event Schedule - the rules and guidelines for running your own instance, including data ownership, your obligations, and liability.</x-slot>
    <x-slot name="breadcrumbTitle">Selfhosting Terms</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Self-Hosting Terms - Event Schedule",
        "description": "Terms for self-hosting Event Schedule - the rules and guidelines for running your own instance, including data ownership, your obligations, and liability.",
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "Self-Hosting Terms"
        }
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Self-hosting-terms "The Fine Print" styles. The page IS the
           instrument: a numbered deed on paper stock, clause bodies set
           in a system serif, numerals in tabular mono, and a plain
           language gloss in the margin beside every clause.

           WHY THIS CONCEPT ARGUES THE PRODUCT. The whole point of the
           selfhost terms is a boundary: you hold the database, so most
           of the clauses describe what Event Schedule *cannot* do. So
           the signature device is a hard vertical rule down the middle
           of a real <table> - "on your infrastructure" against "on
           eventschedule.com" - and a short list of everything that
           crosses it: REPORT_ERRORS (config/sentry.php leaves the DSN
           null without it), federation (FederationService::isEnabled()
           needs Setting::get('federation_enabled'), off by default),
           the release check (config/self-update.php, GitHub, read
           only) and whatever integrations you wire up with your own
           credentials. The same rule is drawn once more in the
           execution block, between the two sides that signed. The
           metaphor and the feature story are the same sentence.

           REVIEW NOTE: federation was missing from that list in the
           first pass, which made "this is that list" false - it is
           the one crossing that transmits actual event content to
           eventschedule.com, and it is exactly the "cloud-relay
           feature" the Data Use clause names. Any future edit to the
           crossings must keep the count in the heading, the cards and
           the boundary table in agreement.

           NOT ONE LEGAL WORD CHANGES. Every clause body and the recital
           are copied verbatim from the first-wave page. The margin
           glosses, the docket and the boundary table are NON-legal
           chrome, and the page says so out loud next to the first
           gloss. New chrome uses "selfhost"/"selfhosted"; the legal
           text keeps its own hyphenation.

           TYPOGRAPHY IS THE DIFFERENTIATOR, NOT HUE. The accent hue
           family is inherited from the first-wave page (blue), but the
           three-stop brand blue -> sky -> cyan gradient is shared
           chrome, so this page spends a single flat iron-gall ink
           (#1d4ed8 light, #93c5fd dark) and gets its identity from
           material instead: warm paper, hairline rules, a serif clause
           body, tabular numerals, a rotated AS IS stamp.

           THE SHEET IS MODE-ADAPTIVE ON PURPOSE. It is a document, not
           a fixed physical object like a chalkboard, so it is designed
           twice (paper in light, slate in dark) rather than pinned. No
           --bands invariant applies to it. The two fixed-dark bands
           (.es-fine-band) DO carry the shared-class overrides.

           NO DOT NAV. A deed has a schedule of clauses, so the clause
           index at the head of section 05 is the navigation; a floating
           dot rail would be furniture from the audience pages.

           NEVER text-gray-500 on this ground - it measures 4.83 on pure
           white but ~4.4 on warm paper. Use .es-fine-muted (7.4).
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-fine-page { background-color: #f5f5f2; color: #16181c; }
        .dark .es-fine-page { background-color: #0b0c0f; color: #e9ebef; }
        .es-fine-ink { color: #16181c; }
        .dark .es-fine-ink { color: #e9ebef; }
        .es-fine-muted { color: #4b5158; }
        .dark .es-fine-muted { color: #9aa1ab; }
        .es-fine-accent { color: #1d4ed8; }
        .dark .es-fine-accent { color: #93c5fd; }
        /* Always-lit ink for the fixed-dark bands, in both colour modes. */
        .es-fine-lit { color: #93c5fd; }

        /* --- Type stacks: system only, no web fonts ------------------ */
        .es-fine-serif {
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-size: 1.0625rem;
            line-height: 1.78;
        }
        .es-fine-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- The sheet: paper in light, slate in dark ---------------- */
        .es-fine-sheet {
            background-color: #ffffff;
            border: 1px solid rgba(22, 24, 28, 0.11);
            border-radius: 0.9rem;
            box-shadow: 0 1px 0 rgba(22, 24, 28, 0.04), 0 18px 40px -30px rgba(22, 24, 28, 0.35);
        }
        .dark .es-fine-sheet {
            background-color: #12141a;
            border-color: rgba(233, 235, 239, 0.11);
            box-shadow: 0 18px 40px -30px rgba(0, 0, 0, 0.8);
        }
        /* Clause body ink sits a touch softer than headings, still ~13:1. */
        .es-fine-body { color: #24272c; }
        .dark .es-fine-body { color: #d9dce2; }

        /* --- Hairline rules ----------------------------------------- */
        .es-fine-rule {
            height: 1px;
            background-color: rgba(22, 24, 28, 0.14);
        }
        .dark .es-fine-rule { background-color: rgba(233, 235, 239, 0.14); }
        .es-fine-hair { border-top: 1px solid rgba(22, 24, 28, 0.1); }
        .dark .es-fine-hair { border-top-color: rgba(233, 235, 239, 0.1); }
        /* The ruled line under the title, drawn in ink and fading out like a
           pen lift. Decorative only: no text sits on it. */
        .es-fine-nib {
            height: 2px;
            border-radius: 1px;
            background-color: rgba(29, 78, 216, 0.5);
            background-image: linear-gradient(90deg, #1d4ed8 0%, rgba(29, 78, 216, 0.35) 55%, rgba(29, 78, 216, 0) 100%);
        }
        .dark .es-fine-nib {
            background-color: rgba(147, 197, 253, 0.5);
            background-image: linear-gradient(90deg, #93c5fd 0%, rgba(147, 197, 253, 0.35) 55%, rgba(147, 197, 253, 0) 100%);
        }
        /* The rule under the page title draws itself in from the left. */
        .es-fine-draw {
            transform-origin: left center;
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-fine-draw { transform: scaleX(0); }

        /* --- Eyebrow / label ---------------------------------------- */
        .es-fine-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-tag { color: #9aa1ab; }
        .es-fine-band .es-fine-tag { color: #93c5fd; }

        /* --- Section numeral, set like a clause reference ------------ */
        .es-fine-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border: 1px solid rgba(22, 24, 28, 0.16);
            border-radius: 0.25rem;
            background-color: #ffffff;
            color: #16181c;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.05em;
        }
        .dark .es-fine-corner {
            border-color: rgba(233, 235, 239, 0.18);
            background-color: rgba(233, 235, 239, 0.05);
            color: #e9ebef;
        }
        .es-fine-band .es-fine-corner {
            border-color: rgba(233, 235, 239, 0.18);
            background-color: rgba(233, 235, 239, 0.05);
            color: #e9ebef;
        }

        /* --- The docket: instrument metadata, hairline rows ---------- */
        .es-fine-docket { border-top: 1px solid rgba(22, 24, 28, 0.14); }
        .dark .es-fine-docket { border-top-color: rgba(233, 235, 239, 0.14); }
        .es-fine-docket-row {
            display: grid;
            grid-template-columns: 8.5rem minmax(0, 1fr);
            gap: 0.75rem;
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(22, 24, 28, 0.08);
        }
        .dark .es-fine-docket-row { border-bottom-color: rgba(233, 235, 239, 0.08); }
        .es-fine-docket-key {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #4b5158;
            padding-top: 0.18rem;
        }
        .dark .es-fine-docket-key { color: #9aa1ab; }
        .es-fine-docket-val { font-size: 0.9rem; font-weight: 600; color: #16181c; }
        .dark .es-fine-docket-val { color: #e9ebef; }

        /* --- The AS IS stamp: type, rotated. Not an illustration. ---- */
        .es-fine-stamp {
            display: inline-block;
            transform: rotate(-5deg);
            padding: 0.45rem 0.8rem;
            border: 2px solid rgba(29, 78, 216, 0.4);
            border-radius: 0.35rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            line-height: 1.5;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-fine-stamp { border-color: rgba(147, 197, 253, 0.4); color: #93c5fd; }

        /* --- The boundary table: one hard rule down the middle ------- */
        .es-fine-table { width: 100%; border-collapse: collapse; text-align: start; }
        .es-fine-table th,
        .es-fine-table td {
            padding: 0.7rem 0.9rem;
            vertical-align: top;
            border-top: 1px solid rgba(22, 24, 28, 0.09);
        }
        .dark .es-fine-table th,
        .dark .es-fine-table td { border-top-color: rgba(233, 235, 239, 0.09); }
        .es-fine-table thead th { border-top: 0; }
        /* THE LINE. A doubled hairline, because a boundary in a deed is
           drawn once and then confirmed. */
        .es-fine-divide {
            border-left: 1px solid rgba(29, 78, 216, 0.5);
            box-shadow: inset 3px 0 0 -2px rgba(29, 78, 216, 0.5);
        }
        .dark .es-fine-divide {
            border-left-color: rgba(147, 197, 253, 0.5);
            box-shadow: inset 3px 0 0 -2px rgba(147, 197, 253, 0.5);
        }
        /* THE LINE again, in the execution block. Always the band's lit ink,
           in both colour modes, because the band is fixed dark. Stacks below
           the sm breakpoint, where there is no middle to draw down. */
        .es-fine-attest { padding-inline-start: 0; }
        @media (min-width: 640px) {
            .es-fine-attest {
                padding-inline-start: 1.5rem;
                border-left: 1px solid rgba(147, 197, 253, 0.5);
                box-shadow: inset 3px 0 0 -2px rgba(147, 197, 253, 0.5);
            }
        }
        .es-fine-scroll { overflow-x: auto; }
        @media (max-width: 480px) {
            .es-fine-table th,
            .es-fine-table td { padding: 0.55rem 0.6rem; }
        }

        /* --- Clause layout: numeral | body | margin gloss ------------ */
        .es-fine-clause {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem 2rem;
            padding: 1.75rem 0;
            border-top: 1px solid rgba(22, 24, 28, 0.1);
        }
        .dark .es-fine-clause { border-top-color: rgba(233, 235, 239, 0.1); }
        @media (min-width: 1024px) {
            .es-fine-clause { grid-template-columns: 3.25rem minmax(0, 1fr) 14.5rem; }
        }
        .es-fine-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #1d4ed8;
        }
        .dark .es-fine-num { color: #93c5fd; }

        /* Margin gloss: sans, small, held off the clause by a hairline. */
        .es-fine-gloss {
            border-inline-start: 2px solid rgba(29, 78, 216, 0.3);
            padding-inline-start: 0.9rem;
            font-size: 0.82rem;
            line-height: 1.65;
            color: #4b5158;
        }
        .dark .es-fine-gloss {
            border-inline-start-color: rgba(147, 197, 253, 0.3);
            color: #9aa1ab;
        }
        .es-fine-gloss-label {
            display: block;
            margin-bottom: 0.3rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-fine-gloss-label { color: #93c5fd; }

        /* Recital drop cap. */
        .es-fine-drop::first-letter {
            float: inline-start;
            margin-inline-end: 0.55rem;
            font-size: 3.1rem;
            line-height: 0.82;
            font-weight: 700;
            color: #1d4ed8;
        }
        .dark .es-fine-drop::first-letter { color: #93c5fd; }

        /* --- The schedule of clauses (index) ------------------------- */
        .es-fine-index-item {
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
            padding: 0.5rem 0.7rem;
            border: 1px solid rgba(22, 24, 28, 0.1);
            border-radius: 0.4rem;
            background-color: rgba(255, 255, 255, 0.65);
            font-size: 0.85rem;
            font-weight: 600;
            color: #16181c;
            transition: border-color 0.2s ease, color 0.2s ease;
        }
        .dark .es-fine-index-item {
            border-color: rgba(233, 235, 239, 0.1);
            background-color: rgba(233, 235, 239, 0.04);
            color: #e9ebef;
        }
        .es-fine-index-item:hover { border-color: rgba(29, 78, 216, 0.55); color: #1d4ed8; }
        .dark .es-fine-index-item:hover { border-color: rgba(147, 197, 253, 0.55); color: #93c5fd; }
        .es-fine-index-num {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 800;
            color: #1d4ed8;
        }
        .dark .es-fine-index-num { color: #93c5fd; }

        /* --- Cards -------------------------------------------------- */
        .es-fine-card {
            border: 1px solid rgba(22, 24, 28, 0.11);
            border-radius: 0.9rem;
            background-color: #ffffff;
        }
        .dark .es-fine-card {
            border-color: rgba(233, 235, 239, 0.11);
            background-color: rgba(233, 235, 239, 0.04);
        }
        .es-fine-band .es-fine-card {
            border-color: rgba(233, 235, 239, 0.13);
            background-color: rgba(233, 235, 239, 0.05);
        }

        /* --- Fixed-dark bands --------------------------------------- */
        .es-fine-band {
            background-color: #0d0f14;
            background-image: radial-gradient(120% 100% at 50% 0%, #171a22 0%, #10131a 55%, #080a0d 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(233, 235, 239, 0.05);
        }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-fine-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 235, 239, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 235, 239, 0.05) 1px, transparent 1px);
        }
        .es-fine-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-fine-band .es-fine-rule { background-color: rgba(233, 235, 239, 0.14); }

        /* --- Links and buttons -------------------------------------- */
        .es-fine-link { color: #1d4ed8; text-decoration-thickness: 1px; text-underline-offset: 2px; }
        .es-fine-link:hover { color: #16181c; text-decoration-line: underline; }
        .dark .es-fine-link { color: #93c5fd; }
        .dark .es-fine-link:hover { color: #e9ebef; }
        .es-fine-band .es-fine-link { color: #93c5fd; }
        .es-fine-band .es-fine-link:hover { color: #ffffff; }

        .es-fine-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 18px 36px -16px rgba(29, 78, 216, 0.6);
        }
        .es-fine-btn:hover { background-color: #1740b4; box-shadow: 0 22px 44px -16px rgba(29, 78, 216, 0.7); }
        .es-fine-ghost {
            border: 1px solid rgba(233, 235, 239, 0.22);
            color: #e9ebef;
        }
        .es-fine-ghost:hover { border-color: rgba(147, 197, 253, 0.6); color: #ffffff; }

        /* --- Related documents hover -------------------------------- */
        .es-fine-hover:hover { border-color: rgba(29, 78, 216, 0.5); }
        .dark .es-fine-hover:hover { border-color: rgba(147, 197, 253, 0.5); }
        .es-fine-hover:hover .es-fine-hover-title,
        .es-fine-hover:hover .es-fine-hover-arrow { color: #1d4ed8; }
        .dark .es-fine-hover:hover .es-fine-hover-title,
        .dark .es-fine-hover:hover .es-fine-hover-arrow { color: #93c5fd; }

        /* --- Shared-system recolour: the hero spotlight -------------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.1), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(147, 197, 253, 0.09), transparent 60%);
        }

        /* --- Focus rings. No border-radius here: an outline already
               follows the element's own shape. ------------------------ */
        #es-fine-page a:focus-visible,
        #es-fine-page button:focus-visible,
        #es-fine-page [tabindex]:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-fine-page a:focus-visible,
        .dark #es-fine-page button:focus-visible,
        .dark #es-fine-page [tabindex]:focus-visible { outline-color: #93c5fd; }
        .es-fine-band a:focus-visible,
        .es-fine-band button:focus-visible { outline-color: #93c5fd !important; }

        @media (prefers-reduced-motion: reduce) {
            .es-fine-draw { transition: none; }
            html.es-anim [data-reveal]:not(.is-revealed) .es-fine-draw { transform: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    @php
        // The instrument's front matter. Every row is drawn from the recital
        // below or from code: the plan checks in Role::isPro() / isEnterprise()
        // both return true when config('app.hosted') is false.
        $docket = [
            ['Applies to', 'Any instance you run yourself'],
            ['Counterparty', 'Event Schedule LLC'],
            ['Code license', 'Attribution Assurance License'],
            ['Governs', 'Support, connected services, future releases'],
            ['Does not govern', 'The data on your own server'],
            ['Feature gates', 'None on a selfhosted instance'],
        ];

        // THE LINE. Left column: what lives on the operator's own
        // infrastructure. Right column: what Event Schedule sees. Backed by
        // config/sentry.php (a null DSN unless REPORT_ERRORS is true),
        // config/self-update.php (release checks against the public GitHub
        // repository), FederationService::isEnabled() (off until an admin sets
        // federation_enabled at /admin/settings), and the standard Laravel
        // mail / Stripe / AI provider config, all of which read the instance's
        // own .env.
        $boundary = [
            ['The database', 'Schedules, events, sales, followers, all on your MySQL server', 'No access'],
            ['Uploaded files', 'Flyers, logos and event graphics on your disk or object store', 'No access'],
            ['Payments', 'Your own Stripe account and your own payouts', 'No platform fee, no access'],
            ['Outgoing email', 'Your mail server, configured in your .env', 'Not routed through us'],
            ['AI features', 'Your own provider API key, read from your .env', 'Not proxied by us'],
            ['Crash reports', 'Off unless you set REPORT_ERRORS=true', 'Received only if you opt in'],
            ['Update checks', 'Your instance asks GitHub for the newest release', 'Nothing, that request goes to GitHub'],
            ['Public listings', 'Federation stays off until an admin turns it on', 'Public event details, if you opt in'],
        ];

        // Everything in the codebase that crosses the line. Three of the four
        // are off until you switch them on; the fourth reads a version number.
        $crossings = [
            [
                'label' => 'Off by default',
                'title' => 'Crash reports',
                'body' => 'Error reporting is off out of the box. Until REPORT_ERRORS is set to true there is no reporting endpoint configured at all, so nothing is sent when something breaks.',
            ],
            [
                'label' => 'Off by default',
                'title' => 'Public listings',
                'body' => 'Federation shares your public events with the eventschedule.com listings, and every listing links back to the event on your own site. An admin has to turn it on, and any schedule can decline on its own.',
            ],
            [
                'label' => 'Your keys',
                'title' => 'Anything you connect',
                'body' => 'Stripe, Google and Microsoft calendars, CalDAV, AI parsing: each one runs on credentials you add yourself, and each one talks to that provider directly rather than through Event Schedule.',
            ],
            [
                'label' => 'Read only',
                'title' => 'Update checks',
                'body' => 'The updater compares your installed version against the public releases of the eventschedule/eventschedule repository. It reads a version number; it does not send your data anywhere.',
            ],
        ];

        // ------------------------------------------------------------------
        // THE CLAUSES. Bodies and headings are copied verbatim from the
        // first-wave page - not one legal word changes. The 'gloss' values
        // are a plain-language margin note and are NOT part of the terms,
        // which the page states next to the first one.
        // ------------------------------------------------------------------
        $clauses = [
            [
                'id' => 'data-ownership',
                'title' => 'Data Ownership & Access',
                'body' => 'Self-hosters own their data and bear full responsibility for it. <strong>Event Schedule cannot access, modify, or remove self-hosted data</strong> stored on your private infrastructure. Users must handle any losses or damages affecting their clients independently.',
                'gloss' => 'Your database, your uploads, your backups. There is no support tunnel into a selfhosted instance, so recovery is yours to plan: the settings screen exports a backup archive you download and keep somewhere other than the server.',
            ],
            [
                'id' => 'amendment-rights',
                'title' => 'Amendment Rights',
                'body' => 'Event Schedule may modify these terms regarding support, connected services, and future releases with notice via email, dashboards, or websites. Changes become binding seven days after notice, unless longer periods apply by law. While the open-source license for a specific version of the code is permanent, users must accept updated Terms to continue receiving official updates or technical support.',
                'gloss' => 'The license on the copy you already run does not expire. What can change is the arrangement around it: support, connected services and future releases.',
            ],
            [
                'id' => 'eligibility',
                'title' => 'Eligibility',
                'body' => 'Users must be 18 years of age or older and confirm compliance with applicable laws. Prior suspension or removal from Event Schedule services disqualifies new access to our official support and update channels.',
                'gloss' => 'Eighteen or older, and an account removed before does not get the official support and update channels back.',
            ],
            [
                'id' => 'personal-responsibility',
                'title' => 'Personal Responsibility',
                'body' => 'Users are responsible for securing their credentials and handling all legal obligations regarding data privacy, copyright, and international regulations independently.',
                'gloss' => 'Your keys and your privacy obligations. Nobody else can rotate an API key that only exists in your .env.',
            ],
            [
                'id' => 'your-obligations',
                'title' => 'Your Obligations',
                'body' => 'Users remain solely responsible for goods, services, and customer obligations facilitated through the platform.',
                'gloss' => 'If a ticket is sold through your instance, the ticket holder is your customer from checkout to the door.',
            ],
            [
                'id' => 'customer-service',
                'title' => 'Customer Service',
                'body' => 'Users provide their own customer support. Event Schedule only assists account users with platform functionality on the hosted version or via specific enterprise support agreements.',
                'gloss' => 'Guests write to your address, not ours. Direct help from Event Schedule covers hosted accounts, or a selfhosted deployment under an enterprise support agreement.',
            ],
            [
                'id' => 'data-use',
                'title' => 'Data Use & Privacy',
                'body' => 'For self-hosted instances, <strong>this section applies only to data explicitly transmitted to Event Schedule</strong> (e.g., via opted-in crash reports, update checks, or cloud-relay features). In such cases, you grant Event Schedule a non-exclusive, fully sublicensable, worldwide, royalty-free right to use, copy, and store that specific data solely for the purpose of providing services to your instance.',
                'gloss' => 'This clause can only reach what your instance actually sends. Leave crash reporting and federation switched off and there is nothing for it to act on: the release check reads a version number from GitHub, not from us.',
            ],
            [
                'id' => 'restricted-businesses',
                'title' => 'Restricted Businesses & Sanctions',
                'body' => 'Illegal activities and use in high-risk jurisdictions (Cuba, Iran, North Korea, Crimea, Syria) are prohibited. While the software is open source, Event Schedule does not provide support or services to prohibited categories, including gambling, telemarketing, unauthorized multi-level marketing, or weapons sales.',
                'gloss' => 'The clause is about who Event Schedule will support and serve, and it names the categories plainly.',
            ],
            [
                'id' => 'indemnity',
                'title' => 'Indemnity & Liability',
                'body' => 'Users indemnify Event Schedule against claims arising from platform use, agreement violations, or third-party rights infringement. Event Schedule disclaims liability for damages, lost profits, or data loss, as further detailed in the Attribution Assurance License.',
                'gloss' => 'As is, with all faults. If the server falls over mid-sale, that risk sits with the operator, which is the other half of holding all of the data.',
            ],
            [
                'id' => 'communication',
                'title' => 'Communication & Resolution',
                'body' => 'For privacy and data concerns, contact: <a href="mailto:legal@eventschedule.com" class="es-fine-link">legal@eventschedule.com</a>',
                'gloss' => 'One address for privacy and data questions.',
            ],
        ];

        $documents = [
            ['/terms', 'Terms of Service', 'The terms for the hosted app at eventschedule.com.'],
            ['/privacy', 'Privacy Policy', 'What the hosted app collects, and how to have it deleted.'],
            ['/selfhost', 'Selfhosting', 'What you get when you run it yourself, and what it costs to run.'],
            ['/docs/selfhost/installation', 'Installation guide', 'Requirements, install steps and the scheduler.'],
        ];
    @endphp

    <div id="es-fine-page" class="es-fine-page">

    <!-- ============================================================ -->
    <!-- 1. The instrument: title and docket                          -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative scroll-mt-24 overflow-hidden py-16 sm:py-20 lg:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 22% 55%, rgba(29, 78, 216, 0.16), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 28%, rgba(147, 197, 253, 0.14), rgba(147, 197, 253, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-[1.1fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-7 inline-flex items-center gap-2.5 rounded-full px-4 py-2">
                        <svg aria-hidden="true" class="h-4 w-4 es-fine-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span class="es-fine-tag">Selfhost terms</span>
                    </div>

                    <h1 class="es-balance es-fine-ink text-[2.5rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The fine print,</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">in <span class="es-fine-accent">full.</span></span></span>
                    </h1>

                    <div class="es-fade-up es-d-2 mt-7 max-w-md" data-reveal>
                        <div class="es-fine-nib es-fine-draw"></div>
                    </div>

                    <p class="es-fade-up es-d-2 es-fine-muted mt-6 max-w-xl text-lg">
                        Event Schedule LLC
                    </p>
                    <p class="es-fade-up es-d-3 es-fine-muted mt-3 max-w-xl">
                        These are the terms for running your own instance. The clauses below are reproduced word for word; the notes in the margin are a plain-language summary and are not part of them.
                    </p>

                    <div class="es-fade-up es-d-3 mt-8 flex flex-wrap items-center gap-4">
                        <a href="#clauses" class="glass inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            <span class="es-fine-ink">Read the clauses</span>
                            <svg aria-hidden="true" class="es-fine-ink h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="#boundary" class="es-fine-link inline-flex items-center gap-1.5 text-sm font-semibold">
                            Where the line is drawn
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The docket: the instrument's front matter -->
                <div class="es-fade-up es-d-4" data-reveal="panel">
                    <div class="es-fine-sheet p-6 sm:p-7">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <p class="es-fine-tag mb-1.5">At a glance</p>
                                <h2 class="es-fine-ink text-lg font-bold">Selfhosting Terms</h2>
                            </div>
                            <span class="es-fine-stamp" aria-hidden="true">As is<br>All faults</span>
                        </div>
                        <dl class="es-fine-docket">
                            @foreach ($docket as [$dKey, $dVal])
                                <div class="es-fine-docket-row">
                                    <dt class="es-fine-docket-key">{{ $dKey }}</dt>
                                    <dd class="es-fine-docket-val">{{ $dVal }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <p class="es-fine-muted mt-4 text-xs leading-relaxed">
                            Every Pro and Enterprise feature is included when you run it yourself: with hosting turned off, the app's plan checks pass for every schedule. Two things exist only here, on the far side of that switch: one-click updates from inside the admin panel, and importing events from a URL or by city. The license asks only that the original attribution stays in place.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Recital                                                   -->
    <!-- ============================================================ -->
    <section id="recital" class="scroll-mt-24 pb-16 lg:pb-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 text-center">
                <div class="es-fine-corner" data-reveal aria-hidden="true"><span>02</span></div>
            </div>
            <div class="es-fine-sheet p-6 sm:p-9" data-reveal="panel">
                <div class="es-fine-clause" style="border-top: 0; padding-top: 0;">
                    <p class="es-fine-num" aria-hidden="true">&sect;</p>
                    <div>
                        <h2 class="es-fine-tag mb-4">Recital</h2>
                        <p class="es-fine-serif es-fine-body es-fine-drop">
                            All features from the hosted app are included in the open-source code. By self-hosting Event Schedule, you accept the platform "as is" and "with all faults," assuming all risks associated with running and maintaining your own instance. Use of the source code is governed by the <strong>Attribution Assurance License</strong>; these Terms govern your relationship with Event Schedule as a service and support provider.
                        </p>
                    </div>
                    <aside class="es-fine-gloss">
                        <span class="es-fine-gloss-label">In plain terms</span>
                        Two separate things. The code is yours under the Attribution Assurance License. These terms cover the relationship with Event Schedule around it: support, connected services and updates. Everything in this margin is a summary for reading convenience and forms no part of the terms.
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The line: a real table with a hard rule down the middle    -->
    <!-- ============================================================ -->
    <section id="boundary" class="es-fine-hair scroll-mt-24 py-16 lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <div class="es-fine-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-fine-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The boundary</p>
                <h2 class="es-balance es-fine-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Most of these clauses exist because of <span class="es-fine-accent">one line.</span>
                </h2>
                <p class="es-fine-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    On a selfhosted instance the data sits on your side of it. That single fact is why the clauses below spend most of their words on what Event Schedule cannot do.
                </p>
            </div>

            <div class="es-fine-sheet p-4 sm:p-6" data-reveal="panel">
                <p class="es-fine-muted mb-2 px-1 text-xs sm:hidden">Scroll the table sideways for the second column.</p>
                <div class="es-fine-scroll" tabindex="0" role="region" aria-label="What runs on your infrastructure and what Event Schedule sees">
                    <table class="es-fine-table">
                        <caption class="sr-only">What runs on your infrastructure and what Event Schedule sees, item by item</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-fine-tag">Item</th>
                                <th scope="col" class="es-fine-tag">On your infrastructure</th>
                                <th scope="col" class="es-fine-tag es-fine-divide">On eventschedule.com</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($boundary as [$bItem, $bYours, $bOurs])
                                <tr>
                                    <th scope="row" class="es-fine-ink whitespace-nowrap text-sm font-bold">{{ $bItem }}</th>
                                    <td class="es-fine-muted text-sm">{{ $bYours }}</td>
                                    <td class="es-fine-divide es-fine-mono es-fine-accent text-xs font-bold uppercase tracking-wide">{{ $bOurs }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-fine-muted mt-4 px-1 text-xs">
                    The rule between the last two columns is the whole argument of this page. Nothing on the left of it is reachable from eventschedule.com. Only the last three rows leave your server at all, and the two that carry any of your own data are off until you turn them on.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What crosses the line (fixed-dark band)                   -->
    <!-- ============================================================ -->
    <section id="crossings" class="relative scroll-mt-24 px-2 py-12 sm:px-4 lg:py-16">
        <div class="es-fine-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-14 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-10 max-w-3xl text-center">
                    <div class="es-fine-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-fine-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What crosses it</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Four things cross it. <span class="es-fine-lit">Three start off.</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-400" data-reveal style="--reveal-delay: 0.15s;">
                        The Data Use clause below only reaches data your instance actually transmits. This is that list in full: three switches that begin in the off position, and one read-only version check.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2" data-reveal-group="110">
                    @foreach ($crossings as $crossing)
                        <div class="es-fine-card flex flex-col p-6" data-reveal="panel">
                            <p class="es-fine-tag mb-3">{{ $crossing['label'] }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $crossing['title'] }}</h3>
                            <p class="text-sm leading-relaxed text-gray-400">{{ $crossing['body'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mx-auto mt-10 max-w-2xl text-center">
                    <div class="es-fine-rule mb-5"></div>
                    <p class="text-gray-300" data-reveal>
                        Everything else stays where you put it.
                        <a href="#data-use" class="es-fine-link font-semibold">Read the clause it belongs to</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The clauses, with a schedule of clauses at the head       -->
    <!-- ============================================================ -->
    <section id="clauses" class="scroll-mt-24 py-16 lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <div class="es-fine-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-fine-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The terms</p>
                <h2 class="es-balance es-fine-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Ten clauses, word for word.
                </h2>
                <p class="es-fine-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Nothing here has been shortened for the rebuild. The margin note beside each clause is a summary and is not part of the terms.
                </p>
            </div>

            <!-- Schedule of clauses -->
            <nav class="mb-8" aria-label="Schedule of clauses">
                {{-- Ten clauses, so the column counts are factors of ten: 1, 2, 5.
                     A three-column index would leave a single item dangling in the
                     last row, which is exactly what a schedule of clauses must not do. --}}
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5" data-reveal-group="45">
                    @foreach ($clauses as $index => $clause)
                        <a href="#{{ $clause['id'] }}" class="es-fine-index-item" data-reveal>
                            <span class="es-fine-index-num" aria-hidden="true">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span>{{ $clause['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            </nav>

            <!-- The sheet -->
            <div class="es-fine-sheet p-5 sm:p-9" data-reveal="panel">
                @foreach ($clauses as $index => $clause)
                    <article id="{{ $clause['id'] }}" class="es-fine-clause scroll-mt-24">
                        <p class="es-fine-num" aria-hidden="true">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</p>
                        <div>
                            <h2 class="es-fine-ink mb-3 text-xl font-bold tracking-tight">{{ $clause['title'] }}</h2>
                            <p class="es-fine-serif es-fine-body">{!! $clause['body'] !!}</p>
                            @if ($clause['id'] === 'indemnity')
                                <p class="mt-5">
                                    <span class="es-fine-stamp">As is<br>All faults</span>
                                </p>
                            @endif
                        </div>
                        <aside class="es-fine-gloss">
                            <span class="es-fine-gloss-label">In plain terms</span>
                            {{ $clause['gloss'] }}
                        </aside>
                    </article>
                @endforeach
            </div>

            <p class="es-fine-muted mx-auto mt-6 max-w-3xl text-center text-sm" data-reveal>
                Running the hosted app at eventschedule.com instead? The
                <a href="{{ marketing_url('/terms-of-service') }}" class="es-fine-link font-medium">Terms of Service</a>
                and
                <a href="{{ marketing_url('/privacy') }}" class="es-fine-link font-medium">Privacy Policy</a>
                cover that arrangement.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Related documents                                         -->
    <!-- ============================================================ -->
    <section class="es-fine-hair scroll-mt-24 py-14">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <p class="es-fine-tag mb-3" data-reveal>Also on file</p>
                <h2 class="es-fine-ink text-2xl font-black tracking-tight md:text-3xl" data-reveal style="--reveal-delay: 0.05s;">Related documents</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($documents as [$docHref, $docName, $docBlurb])
                    <a href="{{ marketing_url($docHref) }}" class="es-fine-hover es-fine-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-fine-hover-title es-fine-ink mb-2 text-base font-semibold transition-colors">{{ $docName }}</span>
                        <span class="es-fine-muted mb-4 text-sm leading-relaxed">{{ $docBlurb }}</span>
                        <span class="es-fine-hover-arrow es-fine-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Execution block                                           -->
    <!-- ============================================================ -->
    <section id="execution" class="relative scroll-mt-24 px-2 pb-16 pt-4 sm:px-4 lg:pb-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-fine-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-14 sm:px-12 lg:py-20" data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>
                <div class="relative z-10 mx-auto max-w-3xl text-center">
                    <p class="es-fine-tag mb-4">Execution</p>
                    <h2 class="es-balance mx-auto mb-5 max-w-2xl text-3xl font-black tracking-tight text-white md:text-4xl">
                        Run it yourself. <span class="es-fine-lit">Own the whole of it.</span>
                    </h2>
                    <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-400">
                        No license fee, no per-event charge, no platform fee on ticket sales, and no plan gates once hosting is turned off. What you pay for is the server, Stripe's own processing, and whatever the keys you add yourself cost.
                    </p>

                    <div class="mx-auto mb-10 flex flex-col items-stretch justify-center gap-3 sm:flex-row sm:items-center">
                        <a href="mailto:legal@eventschedule.com" class="es-fine-ghost inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3.5 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            legal@eventschedule.com
                        </a>
                        <a href="{{ marketing_url('/docs/selfhost/installation') }}" class="es-fine-btn group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-xl px-7 py-3.5 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            <span class="relative z-10 flex items-center gap-2">
                                Read the installation guide
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    {{-- The rule from the boundary table, drawn once more: the two
                         sides of the instrument, divided by the same doubled hairline. --}}
                    <div class="mx-auto max-w-2xl text-left">
                        <div class="es-fine-rule mb-6"></div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <p class="es-fine-tag mb-2">Event Schedule LLC</p>
                                <p class="text-sm leading-relaxed text-gray-400">
                                    Provides the code under the Attribution Assurance License, and the support, connected services and releases these terms govern.
                                </p>
                            </div>
                            <div class="es-fine-attest">
                                <p class="es-fine-tag mb-2">You, the operator</p>
                                <p class="text-sm leading-relaxed text-gray-400">
                                    Hold the server, the database, the uploads and every key in the .env, and carry the risk that comes with holding them.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    </div>

    <x-marketing.related-pages />

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
