<x-marketing-layout>
    <x-slot name="title">Open Source Event Calendar - Licence, Selfhosting and the REST API</x-slot>
    <x-slot name="description">Event Schedule is open source under the Attribution Assurance License. Selfhost the whole thing on your own server, or drive the hosted version through the REST API. Every claim on this page has a file path.</x-slot>
    <x-slot name="breadcrumbTitle">Open Source</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareSourceCode",
        "name": "Event Schedule",
        "description": "Event Schedule is open source under the Attribution Assurance License (AAL), an OSI-approved licence adapted from the BSD licence. Selfhost it on your own server, where every feature resolves to the top tier, or drive the hosted version through the REST API.",
        "codeRepository": "https://github.com/eventschedule/eventschedule",
        "programmingLanguage": ["PHP", "JavaScript", "Vue.js"],
        "runtimePlatform": "Laravel 11",
        "license": "https://opensource.org/licenses/AAL",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "url": "{{ url()->current() }}"
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
           Open-source "The Commit Log" styles.

           THE CONCEPT. A commit log is a record: a spine, a node per
           entry, and a path to the thing that changed. That is also the
           only honest argument an open-source product can make on a
           marketing page - not "trust us" but "here is the file". So
           every section of this page is a log entry, and every claim
           carries the repository path that proves it. The metaphor and
           the feature story are the same sentence.

           DEVICES THIS PAGE MUST NOT BUILD. /selfhost owns "The
           Terminal" and a terminal-window chrome; /for-ai-agents owns
           "The Console" and a request/response ledger. There is no
           window bar, no prompt, no blinking shell and no
           request/response pair anywhere in here. The log's structures
           are its own: a spine with nodes, monospace path chips, a
           unified diff, and one real <table> of the API surface.

           COLOUR. The hue family stays what this page already had -
           blue - but pulled deep and near-monochrome so it reads as
           print rather than as the shared brand chrome. #1e40af on the
           cool paper and #8fb3ff on the ink ground. Deliberately NOT
           the brand #4E81FA -> #0EA5E9 -> #22D3EE ramp (that is shared
           chrome), and no cyan or sky stop, which /for-djs,
           /for-venues and /for-dance-groups hold.

           MUTED INK. Never text-gray-500 here: it measures 4.83 on pure
           white but only 4.4 on this page's cool ground. Use
           .es-commit-muted (7.20 on the ground, 7.73 on a white card).

           FIXED OBJECTS. .es-commit-band is the same dark surface in
           both colour modes, so every shared class inside it that
           carries its own .dark rule in marketing.css gets a band-scoped
           override AFTER the base rule: .grid-overlay, .animate-shimmer
           and .es-claim:focus-within. No .es-aurora or .glass inside a
           band - both flip opacity/background with the mode and cannot
           be pinned without fighting the shared sheet.

           NO ARBITRARY-VALUE TAILWIND for anything design-critical. The
           build is not run during this campaign, so a class that is not
           already in public/build/assets/marketing-app-*.css silently
           does nothing. Every colour, size and shade below is a real
           rule in this block.

           BLADE RULE: no @supports() probes with a "#" hex in the
           condition - that breaks compilation of every later
           parenthesized directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-commit-page { background-color: #f5f7fa; color: #101623; }
        .dark .es-commit-page { background-color: #0a0e15; color: #e7ecf5; }
        .es-commit-ink { color: #101623; }
        .dark .es-commit-ink { color: #e7ecf5; }
        .es-commit-muted { color: #4a5365; }
        .dark .es-commit-muted { color: #97a2b6; }
        .es-commit-accent { color: #1e40af; }
        .dark .es-commit-accent { color: #8fb3ff; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-commit-lit { color: #8fb3ff; }
        .es-commit-mono {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Heading accent. Near-monochrome blue; every stop clears 3:1
               against the ground it sits on, in both modes. ------------- */
        .es-commit-grad {
            background-image: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 55%, #1e40af 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-commit-grad {
            background-image: linear-gradient(135deg, #b9cdff 0%, #8fb3ff 55%, #6f9bff 100%);
        }
        .es-commit-band .es-commit-grad {
            background-image: linear-gradient(135deg, #b9cdff 0%, #8fb3ff 55%, #6f9bff 100%);
        }

        /* --- Cards. Opaque, so text over them is measured against the
               surface it is actually painted on. ---------------------- */
        .es-commit-card {
            background-color: #ffffff;
            border: 1px solid rgba(16, 22, 35, 0.12);
            border-radius: 0.9rem;
        }
        .dark .es-commit-card {
            background-color: #12181f;
            border-color: rgba(231, 236, 245, 0.12);
        }
        .es-commit-band .es-commit-card {
            background-color: #12181f;
            border-color: rgba(231, 236, 245, 0.12);
        }
        .es-commit-hair { border-top: 1px solid rgba(16, 22, 35, 0.1); }
        .dark .es-commit-hair { border-top-color: rgba(231, 236, 245, 0.12); }

        /* --- Fixed-dark band ------------------------------------------ */
        .es-commit-band {
            background-color: #0d131d;
            background-image: radial-gradient(120% 100% at 50% 0%, #16202e 0%, #101825 55%, #080c12 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(231, 236, 245, 0.05);
        }
        /* Shared classes that would otherwise flip with the colour mode. */
        .es-commit-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 236, 245, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 236, 245, 0.05) 1px, transparent 1px);
        }
        .es-commit-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-commit-band .es-claim:focus-within {
            border-color: rgba(143, 179, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 179, 255, 0.22);
        }
        /* Ink and muted ink are pinned inside a band: without these the light
           mode would paint #4a5365 on #0d131d, which is a 1.5:1 disaster and
           would also make the band render differently in the two modes. */
        .es-commit-band .es-commit-ink { color: #e7ecf5; }
        .es-commit-band .es-commit-muted { color: #97a2b6; }

        /* --- THE LOG. A spine, a node per entry, a path per entry. ---- */
        .es-commit-log { position: relative; }
        .es-commit-log::before {
            content: "";
            position: absolute;
            inset-inline-start: calc(0.375rem - 1px);
            top: 0.5rem;
            bottom: 0.5rem;
            width: 2px;
            border-radius: 1px;
            background-image: linear-gradient(180deg, rgba(30, 64, 175, 0.6), rgba(30, 64, 175, 0.14));
            transform-origin: top;
            transition: transform 0.95s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dark .es-commit-log::before {
            background-image: linear-gradient(180deg, rgba(143, 179, 255, 0.65), rgba(143, 179, 255, 0.16));
        }
        .es-commit-band .es-commit-log::before {
            background-image: linear-gradient(180deg, rgba(143, 179, 255, 0.65), rgba(143, 179, 255, 0.16));
        }
        /* The spine draws itself downward on reveal. It RESTS drawn, so a
           no-JS or reduced-motion visitor sees the finished line. */
        html.es-anim [data-reveal]:not(.is-revealed) .es-commit-log::before { transform: scaleY(0); }

        .es-commit-entry {
            position: relative;
            display: grid;
            grid-template-columns: 1.55rem minmax(0, 1fr);
            align-items: start;
        }
        .es-commit-entry + .es-commit-entry { margin-top: 1.35rem; }
        .es-commit-node {
            width: 0.75rem;
            height: 0.75rem;
            margin-top: 0.4rem;
            border-radius: 9999px;
            box-sizing: border-box;
            background-color: #1e40af;
        }
        .dark .es-commit-node { background-color: #8fb3ff; }
        .es-commit-band .es-commit-node { background-color: #8fb3ff; }
        /* An open node: nothing committed here yet. Used once, at HEAD. */
        .es-commit-node-open {
            background-color: transparent;
            border: 2px solid #8fb3ff;
        }
        .es-commit-subject {
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        /* --- The path chip. The page's signature typography. ---------- */
        .es-commit-ref {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            max-width: 100%;
            padding: 0.1rem 0.42rem;
            border-radius: 0.3rem;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.72rem;
            font-weight: 600;
            line-height: 1.5;
            border: 1px solid rgba(30, 64, 175, 0.28);
            background-color: rgba(30, 64, 175, 0.07);
            color: #1e40af;
            overflow-wrap: anywhere;
        }
        .dark .es-commit-ref {
            border-color: rgba(143, 179, 255, 0.3);
            background-color: rgba(143, 179, 255, 0.09);
            color: #8fb3ff;
        }
        .es-commit-band .es-commit-ref {
            border-color: rgba(143, 179, 255, 0.3);
            background-color: rgba(143, 179, 255, 0.09);
            color: #8fb3ff;
        }
        a.es-commit-ref:hover { border-color: rgba(30, 64, 175, 0.6); }
        .dark a.es-commit-ref:hover { border-color: rgba(143, 179, 255, 0.65); }

        /* --- Eyebrow and section numeral ----------------------------- */
        .es-commit-tag {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4a5365;
        }
        .dark .es-commit-tag { color: #97a2b6; }
        .es-commit-band .es-commit-tag { color: #8fb3ff; }

        .es-commit-num {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.32rem 0.72rem;
            border-radius: 0.35rem;
            border: 1px solid rgba(16, 22, 35, 0.16);
            background-color: #ffffff;
            color: #101623;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
        }
        .dark .es-commit-num {
            border-color: rgba(231, 236, 245, 0.2);
            background-color: #12181f;
            color: #e7ecf5;
        }
        .es-commit-band .es-commit-num {
            border-color: rgba(231, 236, 245, 0.2);
            background-color: #12181f;
            color: #e7ecf5;
        }
        .es-commit-num::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background-color: #1e40af;
        }
        .dark .es-commit-num::before { background-color: #8fb3ff; }
        .es-commit-band .es-commit-num::before { background-color: #8fb3ff; }

        /* --- THE DIFF. Lives only inside a fixed-dark band, so its
               colours are absolute and it renders identically in both
               modes by construction. ---------------------------------- */
        .es-commit-diff {
            border: 1px solid rgba(231, 236, 245, 0.13);
            border-radius: 0.9rem;
            background-color: #0f1620;
            overflow: hidden;
        }
        .es-commit-diff-head {
            padding: 0.6rem 0.95rem;
            border-bottom: 1px solid rgba(231, 236, 245, 0.11);
            background-color: #131c28;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            color: #97a2b6;
        }
        .es-commit-hunk { padding: 0.7rem 0.95rem; }
        .es-commit-hunk + .es-commit-hunk { border-top: 1px solid rgba(231, 236, 245, 0.08); }
        .es-commit-hunk-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #e7ecf5;
            margin-bottom: 0.3rem;
        }
        .es-commit-row {
            display: grid;
            grid-template-columns: 1rem minmax(0, 1fr);
            gap: 0.45rem;
            align-items: baseline;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.79rem;
            line-height: 1.65;
        }
        .es-commit-gutter { font-weight: 700; text-align: center; }
        .es-commit-row-out { color: #97a2b6; }
        .es-commit-row-in { color: #8fb3ff; font-weight: 600; }

        /* --- THE RECORD. A real table of the API surface. ------------- */
        .es-commit-scroll { overflow-x: auto; }
        .es-commit-table {
            width: 100%;
            min-width: 34rem;
            border-collapse: collapse;
            text-align: start;
        }
        .es-commit-table th,
        .es-commit-table td {
            padding: 0.45rem 0.6rem;
            text-align: start;
            vertical-align: middle;
            border-top: 1px solid rgba(16, 22, 35, 0.09);
        }
        .dark .es-commit-table th,
        .dark .es-commit-table td { border-top-color: rgba(231, 236, 245, 0.1); }
        .es-commit-table thead th {
            border-top: 0;
            padding-bottom: 0.6rem;
        }
        .es-commit-table tbody tr:hover { background-color: rgba(30, 64, 175, 0.05); }
        .dark .es-commit-table tbody tr:hover { background-color: rgba(143, 179, 255, 0.06); }
        .es-commit-path {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.78rem;
            font-weight: 600;
            color: #101623;
            white-space: nowrap;
        }
        .dark .es-commit-path { color: #e7ecf5; }
        .es-commit-verb {
            display: inline-block;
            min-width: 3.9rem;
            padding: 0.06rem 0.36rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(16, 22, 35, 0.28);
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.07em;
            text-align: center;
            color: #101623;
        }
        .dark .es-commit-verb { border-color: rgba(231, 236, 245, 0.3); color: #e7ecf5; }
        .es-commit-verb-read {
            border-color: rgba(30, 64, 175, 0.4);
            color: #1e40af;
        }
        .dark .es-commit-verb-read { border-color: rgba(143, 179, 255, 0.45); color: #8fb3ff; }

        /* --- Plan pills ---------------------------------------------- */
        .es-commit-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(30, 64, 175, 0.42);
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            color: #1e40af;
        }
        .dark .es-commit-plan { border-color: rgba(143, 179, 255, 0.45); color: #8fb3ff; }
        .es-commit-band .es-commit-plan { border-color: rgba(143, 179, 255, 0.45); color: #8fb3ff; }
        .es-commit-plan-pro { border-color: rgba(16, 22, 35, 0.34); color: #101623; }
        .dark .es-commit-plan-pro { border-color: rgba(231, 236, 245, 0.38); color: #e7ecf5; }
        .es-commit-band .es-commit-plan-pro { border-color: rgba(231, 236, 245, 0.38); color: #e7ecf5; }

        /* --- Chips (requirements row) -------------------------------- */
        .es-commit-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.32rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 22, 35, 0.16);
            background-color: #ffffff;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.74rem;
            font-weight: 600;
            color: #4a5365;
        }
        .dark .es-commit-chip {
            border-color: rgba(231, 236, 245, 0.16);
            background-color: #12181f;
            color: #97a2b6;
        }

        /* --- Buttons and links --------------------------------------- */
        .es-commit-btn {
            background-color: #1e40af;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(30, 64, 175, 0.55);
        }
        .es-commit-btn:hover { background-color: #1a3796; box-shadow: 0 22px 44px -14px rgba(30, 64, 175, 0.62); }
        .dark .es-commit-btn { background-color: #8fb3ff; color: #0a0e15; }
        .dark .es-commit-btn:hover { background-color: #aac6ff; }
        .es-commit-band .es-commit-btn { background-color: #8fb3ff; color: #0a0e15; }
        .es-commit-band .es-commit-btn:hover { background-color: #aac6ff; }

        .es-commit-ghost {
            border: 1px solid rgba(16, 22, 35, 0.2);
            background-color: #ffffff;
            color: #101623;
        }
        .es-commit-ghost:hover { border-color: rgba(30, 64, 175, 0.55); }
        .dark .es-commit-ghost {
            border-color: rgba(231, 236, 245, 0.2);
            background-color: #12181f;
            color: #e7ecf5;
        }
        .dark .es-commit-ghost:hover { border-color: rgba(143, 179, 255, 0.6); }

        .es-commit-link { color: #1e40af; }
        .es-commit-link:hover { color: #101623; }
        .dark .es-commit-link { color: #8fb3ff; }
        .dark .es-commit-link:hover { color: #e7ecf5; }
        .es-commit-band .es-commit-link { color: #8fb3ff; }
        .es-commit-band .es-commit-link:hover { color: #e7ecf5; }

        /* --- Hover treatment for FAQ / related cards ----------------- */
        .es-commit-hover:hover { border-color: rgba(30, 64, 175, 0.45); }
        .dark .es-commit-hover:hover { border-color: rgba(143, 179, 255, 0.5); }
        .es-commit-hover:hover .es-commit-hover-title,
        .es-commit-hover:hover .es-commit-hover-arrow { color: #1e40af; }
        .dark .es-commit-hover:hover .es-commit-hover-title,
        .dark .es-commit-hover:hover .es-commit-hover-arrow { color: #8fb3ff; }

        /* --- HEAD: the one entry nobody has written yet -------------- */
        /* A thin insertion bar, not a block cursor: /selfhost owns the terminal
           and this is a caret in a document, three pixels wide. */
        .es-commit-caret {
            display: inline-block;
            width: 3px;
            height: 0.95em;
            margin-inline-start: 0.1em;
            vertical-align: -0.1em;
            border-radius: 1px;
            background-color: #8fb3ff;
            animation: es-commit-blink 1.15s steps(1, end) infinite;
        }
        @keyframes es-commit-blink {
            0%, 49% { opacity: 1; }
            50%, 100% { opacity: 0; }
        }

        /* --- Shared-system recolours (brand blue by default) --------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(30, 64, 175, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(143, 179, 255, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(30, 64, 175, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(143, 179, 255, 0.65); }
        .es-dot.is-active .es-dot-pip { background: #1e40af; }
        .dark .es-dot.is-active .es-dot-pip { background: #8fb3ff; }

        /* --- Focus rings. No border-radius here: setting it would
               change the element's own shape on focus. ---------------- */
        #es-commit-page a:focus-visible,
        #es-commit-page summary:focus-visible,
        #es-commit-page input:focus-visible,
        #es-commit-page button:focus-visible {
            outline: 2px solid #1e40af;
            outline-offset: 3px;
        }
        .dark #es-commit-page a:focus-visible,
        .dark #es-commit-page summary:focus-visible,
        .dark #es-commit-page input:focus-visible,
        .dark #es-commit-page button:focus-visible {
            outline-color: #8fb3ff;
        }
        .es-commit-band a:focus-visible,
        .es-commit-band summary:focus-visible,
        .es-commit-band input:focus-visible,
        .es-commit-band button:focus-visible {
            outline-color: #8fb3ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-commit-caret { animation: none !important; opacity: 1; }
            .es-commit-log::before { transition: none !important; transform: none !important; }
        }
    </style>

    @php
        // The hero log: three entries, each pointing at a file in the repository.
        $heroLog = [
            [
                'ref' => 'LICENSE',
                'subject' => 'Attribution Assurance License',
                'body' => 'OSI-approved, adapted from the BSD licence. Permissive, not copyleft. One condition: keep the credit.',
            ],
            [
                'ref' => 'routes/api.php',
                'subject' => 'Twenty-four authenticated endpoints',
                'body' => 'Schedules, sub-schedules, events, categories, sales, and read access to attendee feedback.',
            ],
            [
                'ref' => 'composer.json',
                'subject' => 'Laravel 11 on PHP 8.2',
                'body' => 'MySQL for storage, Vue for the front end, Vite for the build. Nothing exotic to stand up.',
            ],
        ];

        // The diff: what actually changes when the install stops being ours.
        // Every row is backed by a gate that tests config('app.hosted').
        $diffRows = [
            ['Selling tickets with Stripe payouts', 'Free, up to 25 paid tickets a month', 'Included, no cap'],
            ['QR check-in, waitlists, promo codes and passes', 'Pro, five dollars a month', 'Included'],
            ['REST API and webhooks', 'Pro', 'Included'],
            ['Custom domain', 'Enterprise', 'The install is your domain'],
            ['Team members on one schedule', 'Up to five, on Enterprise', 'No cap'],
            ['Newsletter sends', '10, 100 or 1,000 recipients a month', 'No monthly cap'],
            ['The "Powered by" credit', 'Removed on Pro', 'Already gone'],
            ['Import from a URL or a city search', 'Not available', 'Selfhost only'],
            ['AI parsing and translation', 'Our key, with a daily cap per plan', 'Your own Gemini or OpenAI key, no daily cap'],
        ];

        // The API surface, transcribed from routes/api.php. 'read' marks a GET.
        $endpoints = [
            ['GET', '/api/schedules', 'Schedules', 'Every schedule the key can reach'],
            ['GET', '/api/schedules/{subdomain}', 'Schedules', 'One schedule, by subdomain'],
            ['POST', '/api/schedules', 'Schedules', 'Create a schedule'],
            ['PUT', '/api/schedules/{subdomain}', 'Schedules', 'Update it'],
            ['DELETE', '/api/schedules/{subdomain}', 'Schedules', 'Delete it'],
            ['GET', '/api/schedules/{subdomain}/groups', 'Sub-schedules', 'List the sub-schedules'],
            ['POST', '/api/schedules/{subdomain}/groups', 'Sub-schedules', 'Create one'],
            ['PUT', '/api/schedules/{subdomain}/groups/{group_id}', 'Sub-schedules', 'Rename or recolour it'],
            ['DELETE', '/api/schedules/{subdomain}/groups/{group_id}', 'Sub-schedules', 'Delete it'],
            ['GET', '/api/events', 'Events', 'List events, paginated'],
            ['GET', '/api/events/{id}', 'Events', 'One event'],
            ['POST', '/api/events/{subdomain}', 'Events', 'Create an event on a schedule'],
            ['PUT', '/api/events/{id}', 'Events', 'Update it'],
            ['DELETE', '/api/events/{id}', 'Events', 'Delete it'],
            ['POST', '/api/events/flyer/{event_id}', 'Events', 'Attach a flyer image'],
            ['GET', '/api/categories', 'Categories', 'The system category list'],
            ['GET', '/api/categories/{subdomain}', 'Categories', 'The effective list for one schedule'],
            ['GET', '/api/sales', 'Sales', 'List ticket sales'],
            ['GET', '/api/sales/{id}', 'Sales', 'One sale'],
            ['POST', '/api/sales', 'Sales', 'Record a sale'],
            ['PUT', '/api/sales/{id}', 'Sales', 'Update it'],
            ['DELETE', '/api/sales/{id}', 'Sales', 'Delete it'],
            ['GET', '/api/feedback', 'Feedback', 'Post-event ratings and comments'],
            ['GET', '/api/fan-content', 'Feedback', 'Fan photos, video and comments'],
        ];

        // Published surfaces, all served as static files out of public/.
        $specFiles = [
            ['/api/openapi.json', 'OpenAPI 3.0.3', 'Sixteen paths, twenty-six operations, request and response schemas.'],
            ['/llms.txt', 'Short brief', 'The product in one page: schedule types, auth, plans, rate limits.'],
            ['/llms-full.txt', 'Long brief', 'The same, expanded, for a model with room to read.'],
            ['/.well-known/agents.json', 'Agent flows', 'Four named flows with their steps written out: register and set up, create an event with tickets, sell tickets, manage a schedule.'],
        ];

        $requirements = [
            'PHP 8.2 or newer',
            'MySQL 5.7+ or MariaDB 10.3+',
            'Apache or Nginx',
            'HTTPS',
            'One cron entry',
        ];

        $faqs = [
            [
                'q' => 'What licence is Event Schedule under?',
                'a' => 'The Attribution Assurance License, an OSI-approved licence adapted from the BSD licence. composer.json declares it as AAL and the full text is the LICENSE file in the repository root. It is permissive rather than copyleft: use, modify and redistribute in source or binary form, provided the licence text travels with the code, and provided a binary redistribution displays the author name, "Event Schedule" and the project URL when the program launches. It is short. Read it rather than taking a paragraph on a marketing page for it.',
            ],
            [
                'q' => 'Do I get every feature if I selfhost?',
                'a' => 'Yes. Role::isPro() and Role::isEnterprise() both return true the moment config(\'app.hosted\') is false, so ticketing, the REST API, webhooks, custom fields, event graphics, custom domains, unlimited team members and uncapped newsletter sends are simply on. Two things you supply yourself: an AI key if you want the parsing and translation features, and your own Stripe account for payouts.',
            ],
            [
                'q' => 'Is the REST API free on eventschedule.com?',
                'a' => 'No. API access is a Pro feature at five dollars a month, and the check runs on reads as well as writes: under app/Http/Controllers/Api a single-resource route answers 403 for a free schedule, and a list route filters non-Pro schedules out with the wherePro() scope. On a selfhosted install it is on by default, because a selfhost resolves to the top tier.',
            ],
            [
                'q' => 'What are the API rate limits?',
                'a' => 'Three hundred GET requests a minute per IP and thirty writes a minute per IP, counted in ApiAuthentication. Ten failed attempts with the same key value block that key for fifteen minutes, and a key can carry an expiry date after which it stops working. Creating an event has a second, tighter throttle of thirty a minute.',
            ],
            [
                'q' => 'How do I install it on my own server?',
                'a' => 'Three ways. Softaculous does a one-click install on a cPanel host, the eventschedule/dockerfiles repository has images and a Compose file, or you install by hand: PHP 8.2 or newer, MySQL or MariaDB, Apache or Nginx with rewrites, HTTPS, and one cron entry running the scheduler every minute. The installation guide walks through all of it.',
            ],
            [
                'q' => 'Can I take my data out again?',
                'a' => 'Yes, on every plan. Backup and restore exports a schedule with its events, sub-schedules, ticket types, sales and appointment types, optionally with its images, and imports the same archive into another install. That is the honest test of no lock-in, and it is not behind a paywall.',
            ],
            [
                'q' => 'Do you accept contributions?',
                'a' => 'Issues and pull requests are open on GitHub. Read the code first: it is a fairly ordinary Laravel 11 application with Vue on the front end, and the parts most people want to change - views, translations, integrations - are where you would expect to find them.',
            ],
        ];

        $dotSections = [
            ['top', 'The log'],
            ['license', 'The licence'],
            ['diff', 'The diff'],
            ['api', 'The API'],
            ['spec', 'The spec'],
            ['install', 'The install'],
            ['exit', 'The exit'],
            ['rest', 'Also in the tree'],
            ['faq', 'Questions'],
            ['claim', 'HEAD'],
        ];
    @endphp

    <div id="es-commit-page" class="es-commit-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the log opens                                       -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(80svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(30, 64, 175, 0.2), rgba(30, 64, 175, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(111, 155, 255, 0.14), rgba(111, 155, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-commit-accent" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <span class="es-commit-muted text-sm font-medium tracking-wide">100% open source</span>
                    </div>

                    <h1 class="es-balance es-commit-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Every claim on this page</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">has a <span class="es-commit-grad">file path.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-commit-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Event Schedule is open source under the Attribution Assurance License. Selfhost the whole thing on your own server, or drive the hosted version through the REST API. Either way, the code you are trusting is code you can read.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="es-commit-ghost group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            Read the source
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-commit-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Start for free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <div class="es-fade-up es-d-4 mt-8">
                        @include('marketing.partials.github-star-badge')
                    </div>
                </div>

                <!-- The log, opening. Three entries, three real paths. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-commit-card p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-commit-ink text-lg font-bold">eventschedule/eventschedule</h2>
                            <span class="es-commit-tag">Public</span>
                        </div>

                        <div class="es-commit-log">
                            @foreach ($heroLog as $entry)
                                <div class="es-commit-entry">
                                    <span class="es-commit-node" aria-hidden="true"></span>
                                    <div>
                                        <span class="es-commit-ref">{{ $entry['ref'] }}</span>
                                        <p class="es-commit-subject es-commit-ink mt-1.5">{{ $entry['subject'] }}</p>
                                        <p class="es-commit-muted mt-1 text-sm">{{ $entry['body'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-commit-muted es-commit-hair mt-6 pt-4 text-xs">
                            Nothing on this page is behind a support ticket. If a sentence below makes a claim, the path next to it is where you check it.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The licence                                               -->
    <!-- ============================================================ -->
    <section id="license" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-commit-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The licence</p>
                <h2 class="es-balance es-commit-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Permissive, with one condition: <span class="es-commit-grad">keep the credit.</span>
                </h2>
                <p class="es-commit-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The Attribution Assurance License is OSI-approved and adapted from the BSD licence. It fits on one screen, which is the best argument for reading it instead of a summary.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-commit-card flex flex-col p-7" data-reveal="panel">
                    <span class="es-commit-ref self-start">LICENSE</span>
                    <h3 class="es-commit-ink mt-4 mb-3 text-lg font-bold">Use it and change it</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        Redistribution and use in source and binary forms, with or without modification, are permitted. Commercially too. Fork it, strip out what you do not need, run it for a client.
                    </p>
                </div>
                <div class="es-commit-card flex flex-col p-7" data-reveal="panel">
                    <span class="es-commit-ref self-start">LICENSE</span>
                    <h3 class="es-commit-ink mt-4 mb-3 text-lg font-bold">Carry the notice</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        Redistributed source has to display the licence text. A binary redistribution carries it in the documentation and shows the author name, "Event Schedule" and the project URL when the program launches.
                    </p>
                </div>
                <div class="es-commit-card flex flex-col p-7" data-reveal="panel">
                    <span class="es-commit-ref self-start">composer.json</span>
                    <h3 class="es-commit-ink mt-4 mb-3 text-lg font-bold">Not MIT, not AGPL</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        Permissive rather than copyleft, so your changes are yours to keep private. Also not MIT, which asks for no attribution display. If that distinction matters to a legal team, hand them the file.
                    </p>
                </div>
            </div>

            <p class="es-commit-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                The package manifest declares it as AAL, and
                <a href="https://opensource.org/licenses/AAL" target="_blank" rel="noopener noreferrer" class="es-commit-link font-medium hover:underline">the licence is listed at opensource.org</a>.
                None of this is legal advice, and none of it is a substitute for reading the file.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The diff (fixed-dark band): the mid-page moment            -->
    <!-- ============================================================ -->
    <section id="diff" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-commit-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-commit-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The diff</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Selfhost, and the plan tiers <span class="es-commit-lit">disappear.</span>
                    </h2>
                    <p class="es-commit-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        This is not a marketing promise, it is two early returns in one model. <span class="es-commit-mono es-commit-lit">isPro()</span> and <span class="es-commit-mono es-commit-lit">isEnterprise()</span> both return true the moment the app is not running hosted, so a selfhosted install resolves to the top tier and nothing on it sits behind a paywall.
                    </p>
                    <p class="mt-4" data-reveal style="--reveal-delay: 0.2s;">
                        <span class="es-commit-ref">app/Models/Role.php</span>
                    </p>
                </div>

                <div class="mx-auto max-w-2xl" data-reveal="panel">
                    <div class="es-commit-diff">
                        <div class="es-commit-diff-head">eventschedule.com &rarr; your own server</div>
                        @foreach ($diffRows as [$dName, $dOut, $dIn])
                            <div class="es-commit-hunk">
                                <p class="es-commit-hunk-name">{{ $dName }}</p>
                                <p class="es-commit-row es-commit-row-out">
                                    <span class="es-commit-gutter" aria-hidden="true">-</span>
                                    <span>{{ $dOut }}</span>
                                </p>
                                <p class="es-commit-row es-commit-row-in">
                                    <span class="es-commit-gutter" aria-hidden="true">+</span>
                                    <span>{{ $dIn }}</span>
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="es-commit-card mt-6 p-6" data-reveal>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-commit-ink text-lg font-bold">The line that goes the other way</h3>
                            <span class="es-commit-plan es-commit-plan-pro">Yours</span>
                        </div>
                        <p class="es-commit-muted text-sm leading-relaxed">
                            You take on the server, the database, the backups, the TLS certificate, the cron entry and the upgrade window. On eventschedule.com those are ours, and the bill is five dollars a month, or fifteen for the two Enterprise lines above. Selfhosting is not free, it is differently priced, and pretending otherwise would be the first false claim on this page.
                        </p>
                        <p class="mt-4">
                            <a href="{{ marketing_url('/selfhost') }}" class="es-commit-link inline-flex items-center gap-1 text-sm font-semibold transition-all hover:gap-2">
                                What selfhosting actually involves
                                <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The API: a real record                                    -->
    <!-- ============================================================ -->
    <section id="api" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-commit-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The API</p>
                <h2 class="es-balance es-commit-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Twenty-four endpoints, <span class="es-commit-grad">one header.</span>
                </h2>
                <p class="es-commit-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Send an <span class="es-commit-mono es-commit-accent">X-API-Key</span> header, get JSON back. Here is the whole surface, transcribed from the route file rather than described.
                </p>
            </div>

            <div class="es-commit-card p-5 sm:p-7" data-reveal="panel">
                <div class="es-commit-scroll">
                    <table class="es-commit-table">
                        <caption class="sr-only">The authenticated REST API surface: method, path, resource and what each endpoint does</caption>
                        <thead>
                            <tr class="es-commit-tag">
                                <th scope="col">Method</th>
                                <th scope="col">Path</th>
                                <th scope="col" class="hidden sm:table-cell">Resource</th>
                                <th scope="col">What it does</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($endpoints as [$eVerb, $ePath, $eRes, $eWhat])
                                <tr>
                                    <td>
                                        <span class="es-commit-verb @if ($eVerb === 'GET') es-commit-verb-read @endif">{{ $eVerb }}</span>
                                    </td>
                                    <th scope="row" class="es-commit-path">{{ $ePath }}</th>
                                    <td class="es-commit-muted hidden text-sm sm:table-cell">{{ $eRes }}</td>
                                    <td class="es-commit-muted text-sm">{{ $eWhat }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-commit-muted es-commit-hair mt-5 pt-4 text-xs">
                    Three routes sit outside the table because they are how you get a key in the first place, so they take none themselves: send a verification code, register, log in.
                    <span class="es-commit-ref">routes/api.php</span>
                </p>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-3" data-reveal-group="90">
                <div class="es-commit-card flex flex-col p-7" data-reveal="panel">
                    <span class="es-commit-ref self-start">ApiAuthentication.php</span>
                    <h3 class="es-commit-ink mt-4 mb-3 text-lg font-bold">The key</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        Generate a key in your account settings and send it in the header. Only a prefix is indexed for the lookup and the key itself is verified against a bcrypt hash, so the database never holds the usable value. A key can carry an expiry date.
                    </p>
                </div>
                <div class="es-commit-card flex flex-col p-7" data-reveal="panel">
                    <span class="es-commit-ref self-start">ApiAuthentication.php</span>
                    <h3 class="es-commit-ink mt-4 mb-3 text-lg font-bold">The limits</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        Three hundred reads a minute per IP, thirty writes a minute per IP. Ten failed attempts with the same key value block that key for fifteen minutes. Creating an event carries its own tighter throttle of thirty a minute.
                    </p>
                </div>
                <div class="es-commit-card flex flex-col p-7" data-reveal="panel">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="es-commit-ref">Api/*Controller.php</span>
                        <span class="es-commit-plan es-commit-plan-pro">Pro</span>
                    </div>
                    <h3 class="es-commit-ink mt-4 mb-3 text-lg font-bold">The gate</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        On eventschedule.com the API is a Pro feature at five dollars a month, and the check runs on reads too: a single-resource route answers 403, a list route filters non-Pro schedules out. On a selfhost the same check passes by default.
                    </p>
                    <p class="mt-auto pt-4">
                        <a href="{{ marketing_url('/docs/developer/api') }}" class="es-commit-link inline-flex items-center gap-1 text-sm font-semibold transition-all hover:gap-2">
                            API reference
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The spec                                                  -->
    <!-- ============================================================ -->
    <section id="spec" class="scroll-mt-24 border-y border-gray-200 py-20 dark:border-white/10 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-commit-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The spec</p>
                <h2 class="es-balance es-commit-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The surface is <span class="es-commit-grad">published,</span> not described.
                </h2>
                <p class="es-commit-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Four files, served straight out of the public directory. Point a client generator or an agent at them and skip the prose entirely.
                </p>
            </div>

            <div class="es-commit-card mx-auto max-w-3xl p-6 sm:p-8" data-reveal="panel">
                <div class="es-commit-log">
                    @foreach ($specFiles as [$sPath, $sKind, $sBody])
                        <div class="es-commit-entry">
                            <span class="es-commit-node" aria-hidden="true"></span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ url($sPath) }}" class="es-commit-ref" target="_blank" rel="noopener noreferrer">{{ $sPath }}</a>
                                    <span class="es-commit-tag">{{ $sKind }}</span>
                                </div>
                                <p class="es-commit-muted mt-1.5 text-sm">{{ $sBody }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="es-commit-muted es-commit-hair mt-6 pt-4 text-sm">
                    Agents get their own page, with the flows written out.
                    <a href="{{ marketing_url('/for-ai-agents') }}" class="es-commit-link font-medium hover:underline">Event Schedule for AI agents</a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The install                                               -->
    <!-- ============================================================ -->
    <section id="install" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-commit-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The install</p>
                <h2 class="es-balance es-commit-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three ways <span class="es-commit-grad">in.</span>
                </h2>
                <p class="es-commit-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Pick by how much of the stack you want to touch.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="es-commit-card es-commit-hover flex flex-col p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg" data-reveal="panel">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="es-commit-tag">01</span>
                        <span class="es-commit-plan">One click</span>
                    </div>
                    <h3 class="es-commit-hover-title es-commit-ink mt-4 mb-3 text-lg font-bold transition-colors">Softaculous</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        If your host runs cPanel with Softaculous, Event Schedule is in the installer library. Database, files and permissions are handled for you.
                    </p>
                    <span class="es-commit-hover-arrow es-commit-muted mt-auto inline-flex items-center gap-1 pt-5 text-sm font-medium transition-colors">
                        Open the listing
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>

                <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="es-commit-card es-commit-hover flex flex-col p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg" data-reveal="panel">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="es-commit-tag">02</span>
                        <span class="es-commit-plan">Compose</span>
                    </div>
                    <h3 class="es-commit-hover-title es-commit-ink mt-4 mb-3 text-lg font-bold transition-colors">Docker</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        Images and a Compose file live in their own repository, so the application repo stays free of deployment plumbing. On Alpine images the web user is the numeric UID 82, not www-data.
                    </p>
                    <span class="es-commit-hover-arrow es-commit-muted mt-auto inline-flex items-center gap-1 pt-5 text-sm font-medium transition-colors">
                        eventschedule/dockerfiles
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>

                <a href="{{ marketing_url('/docs/selfhost/installation') }}" class="es-commit-card es-commit-hover flex flex-col p-7 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg" data-reveal="panel">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="es-commit-tag">03</span>
                        <span class="es-commit-plan">By hand</span>
                    </div>
                    <h3 class="es-commit-hover-title es-commit-ink mt-4 mb-3 text-lg font-bold transition-colors">Five steps</h3>
                    <p class="es-commit-muted text-sm leading-relaxed">
                        Database, files, permissions, environment, cron. The guide names the PHP extensions, the ownership commands and the things that usually go wrong first.
                    </p>
                    <span class="es-commit-hover-arrow es-commit-muted mt-auto inline-flex items-center gap-1 pt-5 text-sm font-medium transition-colors">
                        Installation guide
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>
            </div>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-3" data-reveal>
                @foreach ($requirements as $req)
                    <span class="es-commit-chip">{{ $req }}</span>
                @endforeach
            </div>

            <p class="es-commit-muted mx-auto mt-6 max-w-xl text-center text-sm" data-reveal style="--reveal-delay: 0.05s;">
                The PHP version and the extension list are not a recommendation, they are the requirements block of the package manifest.
                <span class="es-commit-ref">composer.json</span>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The exit (fixed-dark band)                                -->
    <!-- ============================================================ -->
    <section id="exit" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-commit-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-commit-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The exit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The proof of no lock-in is <span class="es-commit-lit">the door.</span>
                    </h2>
                    <p class="es-commit-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Every vendor says you are not locked in. The test is whether the way out is a feature you can use today, on the free plan, without asking.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-commit-card flex flex-col p-6" data-reveal="panel">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="es-commit-ref">BackupService.php</span>
                            <span class="es-commit-plan">Free</span>
                        </div>
                        <h3 class="es-commit-ink mt-4 mb-2 text-lg font-bold">Take the data</h3>
                        <p class="es-commit-muted text-sm leading-relaxed">
                            Backup and restore exports a schedule with its events, sub-schedules, ticket types, sales and appointment types, optionally with the images, and imports the same archive into another install.
                        </p>
                    </div>
                    <div class="es-commit-card flex flex-col p-6" data-reveal="panel">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="es-commit-ref">the whole repository</span>
                            <span class="es-commit-plan">Free</span>
                        </div>
                        <h3 class="es-commit-ink mt-4 mb-2 text-lg font-bold">Take the code</h3>
                        <p class="es-commit-muted text-sm leading-relaxed">
                            What is on GitHub is the product, not a trimmed demo of it. eventschedule.com runs this application, which is why a selfhost gets the features rather than a subset of them.
                        </p>
                    </div>
                    <div class="es-commit-card flex flex-col p-6" data-reveal="panel">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="es-commit-ref">config/self-update.php</span>
                            <span class="es-commit-plan">Free</span>
                        </div>
                        <h3 class="es-commit-ink mt-4 mb-2 text-lg font-bold">Update when you choose</h3>
                        <p class="es-commit-muted text-sm leading-relaxed">
                            A selfhosted install can pull the next release from the admin area, or you can ignore the button and deploy from the tag yourself. Nobody moves your version for you.
                        </p>
                    </div>
                </div>

                <p class="es-commit-muted mt-10 text-center" data-reveal>
                    Underneath all of it is a MySQL database on hardware you chose, which is the part no export format can replace.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Also in the tree: bento                                   -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-commit-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Also in the tree</p>
                <h2 class="es-balance es-commit-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Things you only find by <span class="es-commit-grad">reading.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-commit-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="es-commit-ref">app/Jobs/SendWebhook.php</span>
                                <span class="es-commit-plan es-commit-plan-pro">Pro</span>
                            </div>
                            <h3 class="es-commit-ink mb-4 text-xl font-bold">Webhooks, signed</h3>
                            <p class="es-commit-muted mb-4">
                                Twelve event types, from <span class="es-commit-mono es-commit-accent">sale.created</span> through <span class="es-commit-mono es-commit-accent">ticket.scanned</span> to <span class="es-commit-mono es-commit-accent">feedback.submitted</span>. Each delivery carries an HMAC-SHA256 signature computed over the exact body, so you can verify it came from your install and not from somebody who guessed your endpoint.
                            </p>
                            <p class="es-commit-muted text-sm">
                                Three attempts, backing off thirty then sixty seconds. The secret is stored encrypted.
                                <a href="{{ marketing_url('/docs/developer/webhooks') }}" class="es-commit-link font-medium hover:underline">Webhook reference</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-commit-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="es-commit-ref">config/app.php</span>
                                <span class="es-commit-plan">Free</span>
                            </div>
                            <h3 class="es-commit-ink mb-4 text-xl font-bold">Twelve interface languages</h3>
                            <p class="es-commit-muted">
                                Arabic, German, English, Spanish, Estonian, French, Hebrew, Italian, Dutch, Portuguese, Romanian and Russian, with right-to-left handled properly. The language list is one array in the config, so adding a thirteenth is a translation job, not a code change.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-commit-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="es-commit-ref">app/Services/AuditService.php</span>
                                <span class="es-commit-plan">Free</span>
                            </div>
                            <h3 class="es-commit-ink mb-4 text-xl font-bold">Its own audit log</h3>
                            <p class="es-commit-muted">
                                Every schedule keeps a searchable log of what changed, filterable by date and category. A commit log for your calendar, in other words, and it is not a paid add-on.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-commit-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="es-commit-ref">app/Services/FederationService.php</span>
                                <span class="es-commit-plan">Free</span>
                            </div>
                            <h3 class="es-commit-ink mb-4 text-xl font-bold">Federation, off by default</h3>
                            <p class="es-commit-muted mb-4">
                                A selfhosted install can share its public events with the eventschedule.com listings, and every listing links back to the event on your own site. It is off until an administrator turns it on, and any individual schedule can opt out again.
                            </p>
                            <p class="es-commit-muted text-sm">
                                It is a setting on the instance, not a plan tier. eventschedule.com is the receiving end and runs a moderation queue instead.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-commit-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="es-commit-ref">.env.example</span>
                                <span class="es-commit-plan">Free</span>
                            </div>
                            <h3 class="es-commit-ink mb-4 text-xl font-bold">Bring your own everything</h3>
                            <p class="es-commit-muted">
                                SMTP, Stripe, Google or Microsoft calendar credentials, an AI key, a push app id. Each one is an environment variable you set, or leave unset, and the feature that needs it stays out of the way until you do.
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-commit-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="es-commit-ref">app/Utils/UrlUtils.php</span>
                                <span class="es-commit-plan">Free</span>
                            </div>
                            <h3 class="es-commit-ink mb-4 text-xl font-bold">No third-party calls you did not ask for</h3>
                            <p class="es-commit-muted mb-4">
                                Front-end libraries are vendored into the repository rather than pulled from a CDN, and the interface font is served from your own install. The third-party scripts that exist at all, analytics and web push, each sit behind an environment variable that ships empty, so a fresh install calls nobody.
                            </p>
                            <p class="es-commit-muted text-sm">
                                Outbound URL fetches go through a guard that refuses private and link-local addresses, inline scripts carry a nonce, and user markdown is purified before it renders. The one font request the app can still make is a guest-page typeface a schedule owner picked by name.
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
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t border-gray-200 py-20 dark:border-white/10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-commit-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Integrations" description="Calendar sync, Stripe, Invoice Ninja, Eventbrite import and webhooks" :url="marketing_url('/features/integrations')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-2 2a4 4 0 01-5.656-5.656l1-1m6.656-6.656l1-1a4 4 0 015.656 5.656l-2 2a4 4 0 01-5.656 0" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="White label" description="Remove the Event Schedule credit from your guest pages" :url="marketing_url('/features/white-label')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom domain" description="Serve a schedule from a domain you own, with SSL" :url="marketing_url('/features/custom-domain')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed calendar" description="Put your schedule on the site you already have" :url="marketing_url('/features/embed-calendar')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-commit-link inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-gray-200 py-16 dark:border-white/10">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-commit-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/selfhost', 'Selfhosting'], ['/for-ai-agents', 'AI Agents'], ['/docs/developer/api', 'API Reference'], ['/pricing', 'Pricing']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-commit-card es-commit-hover group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-commit-hover-title es-commit-ink mb-3 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-commit-hover-arrow es-commit-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/docs') }}" class="es-commit-link inline-flex items-center font-medium hover:underline">
                    Browse the documentation
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
                <div class="es-commit-num mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-commit-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-commit-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What developers ask before they clone it.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-commit-card es-commit-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-commit-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-commit-accent es-commit-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-commit-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-commit-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-commit-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale: HEAD                                             -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-commit-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10 mx-auto max-w-3xl text-center">
                    <p class="es-commit-tag mb-4">HEAD</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        The next entry is <span class="es-commit-grad">yours.</span><span class="es-commit-caret" aria-hidden="true"></span>
                    </h2>
                    <p class="es-commit-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Clone it and run it on your own hardware, or take a subdomain here and skip the server entirely. Publishing a calendar is free forever either way.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-commit-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <div class="es-commit-log mx-auto mt-10 max-w-md text-start">
                        <div class="es-commit-entry">
                            <span class="es-commit-node es-commit-node-open" aria-hidden="true"></span>
                            <div>
                                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="es-commit-link inline-flex items-center gap-2 text-sm font-semibold hover:underline">
                                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                    eventschedule/eventschedule
                                </a>
                                <p class="es-commit-muted mt-1 text-sm">Clone it, read it, open an issue. No credit card, and no account needed to look.</p>
                            </div>
                        </div>
                    </div>
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
                        <span class="es-commit-card pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
