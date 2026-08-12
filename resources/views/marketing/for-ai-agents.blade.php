<x-marketing-layout>
    @php
        // Single source for the price quoted in the JSON-LD offer and the FAQ,
        // read the same way /pricing reads it so an env override cannot desync
        // this page from billing.
        $proMonthly = (int) config('services.stripe_platform.price_monthly_amount', 9);
    @endphp

    <x-slot name="title">REST API for AI Agents & Developers - Event Schedule</x-slot>
    <x-slot name="description">27 REST endpoints, an OpenAPI 3.0 spec, llms.txt and agents.json. One POST creates the event, its ticket types and its public page. Zero platform fees on ticket sales.</x-slot>
    <x-slot name="breadcrumbTitle">For AI Agents</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule API",
        "applicationCategory": "DeveloperApplication",
        "operatingSystem": "Web",
        "description": "A REST API over the whole of Event Schedule: schedules, sub-schedules, events, recurrences, ticket types, sales, post-event feedback and fan content, with an OpenAPI 3.0 spec, llms.txt and agents.json so an agent can discover it and drive it without a human in the loop.",
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "USD",
            "description": "REST API access is part of the Pro plan. Selfhosted installations include it at no cost."
        },
        "featureList": [
            "27 REST endpoints across registration, schedules, sub-schedules, events, categories, sales, feedback and fan content",
            "OpenAPI 3.0 specification at /api/openapi.json",
            "llms.txt and llms-full.txt for LLM discovery",
            "agents.json describing four multi-step agent flows",
            "API key authentication through the X-API-Key header",
            "Recurring events with a seven-bit day-of-week mask and three ways to end",
            "Ticket types, agenda parts, members and a venue in the same create call",
            "HMAC-SHA256 signed webhooks for fourteen event types",
            "300 GET and 30 write requests per minute, per IP",
            "Encoded string IDs rather than sequential integers",
            "Zero platform fees on ticket sales"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "event API, scheduling API, AI agent event management, event automation API, REST API event scheduling, llms.txt, agents.json, OpenAPI",
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
           For-ai-agents "The Console" styles.

           THE CONCEPT IS THE REQUEST/RESPONSE LEDGER, NOT A TERMINAL
           WINDOW. /selfhost already owns the terminal, and a fake shell
           with three traffic lights is a costume anyway. What a
           developer actually reads is a transaction: a method, a path,
           a body, a status code, a body back. So every claim on this
           page is made in that form, and the page's structure is the
           ledger itself - a status line per section, a 27-row endpoint
           table, four exchanges, and a final exchange that hands you a
           key.

           MATERIAL: every code-bearing surface (.es-cons-term) is
           ALWAYS DARK, in both colour modes, exactly as the product's
           own docs shell renders code. That is the page's fixed
           physical object: pin it and verify with
           --bands=.es-cons-term,.es-cons-band (expect 0 diffs). Its
           inks are .es-cons-bright / .es-cons-dim / .es-cons-lit and
           carry no `dark:` variant, by design.

           COLOUR: the page keeps its existing cyan + emerald family,
           but spends it SEMANTICALLY rather than decoratively. Cyan is
           an identifier (keys, paths, GET), emerald is a value or a
           success (strings, 2xx, POST), amber is a mutation (PUT,
           numbers), red is a removal (DELETE, 4xx). Those four are the
           same four the API reference uses for its method dots, so the
           page and the docs are already the same system.

           NEVER text-gray-500 on this ground: #6b7280 measures 4.83 on
           pure white but only ~4.4 on #f2f4f5. Use .es-cons-muted
           (#4a545b, 7.02 on the light ground, 7.75 on a white card).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-cons-page { background-color: #f2f4f5; color: #0f1417; }
        .dark .es-cons-page { background-color: #080c0e; color: #e6eef1; }
        .es-cons-ink { color: #0f1417; }
        .dark .es-cons-ink { color: #e6eef1; }
        .es-cons-muted { color: #4a545b; }
        .dark .es-cons-muted { color: #93a3aa; }
        .es-cons-key { color: #155e75; }
        .dark .es-cons-key { color: #67e8f9; }

        /* Inks for the ALWAYS-DARK surfaces. No `dark:` twin: these must
           read the same with .dark on and off. */
        .es-cons-bright { color: #e6eef1; }
        .es-cons-dim { color: #9fb0b7; }
        .es-cons-lit { color: #67e8f9; }
        .es-cons-lit-ok { color: #6ee7b7; }

        .es-cons-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- The status line: this page's section mark. A solid block
               cursor, the section label, then a hairline to the edge. --- */
        .es-cons-mark {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4a545b;
        }
        .dark .es-cons-mark { color: #93a3aa; }
        .es-cons-mark::before {
            content: "";
            flex: none;
            width: 0.5rem;
            height: 0.95rem;
            border-radius: 1px;
            background: #155e75;
        }
        .dark .es-cons-mark::before { background: #67e8f9; }
        .es-cons-mark::after {
            content: "";
            flex: 1 1 auto;
            height: 1px;
            background: rgba(15, 20, 23, 0.14);
        }
        .dark .es-cons-mark::after { background: rgba(230, 238, 241, 0.14); }

        /* Section rules. A page-local class rather than an arbitrary
           `border-[rgba(...)]` utility, because Tailwind generates
           arbitrary values at BUILD time and this page ships no build. */
        .es-cons-hr { border-color: rgba(15, 20, 23, 0.08); }
        .dark .es-cons-hr { border-color: rgba(230, 238, 241, 0.08); }

        /* --- Cards on the mode-following ground --- */
        .es-cons-card {
            background: #ffffff;
            border: 1px solid rgba(15, 20, 23, 0.12);
            border-radius: 0.9rem;
        }
        .dark .es-cons-card {
            background: rgba(230, 238, 241, 0.045);
            border-color: rgba(230, 238, 241, 0.12);
        }

        /* --- THE CONSOLE SURFACE. Identical in both colour modes. --- */
        .es-cons-term {
            background-color: #0b1113;
            background-image: linear-gradient(180deg, #101719 0%, #0a1012 62%, #080d0f 100%);
            border: 1px solid rgba(230, 238, 241, 0.13);
            border-radius: 0.9rem;
            box-shadow: inset 0 1px 0 rgba(230, 238, 241, 0.06), 0 18px 40px -24px rgba(0, 0, 0, 0.55);
        }
        .es-cons-bar {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            flex-wrap: wrap;
            padding: 0.6rem 0.9rem;
            background: rgba(230, 238, 241, 0.03);
            border-bottom: 1px solid rgba(230, 238, 241, 0.1);
        }
        .es-cons-rule { border-top: 1px solid rgba(230, 238, 241, 0.1); }
        .es-cons-pre {
            margin: 0;
            padding: 0.9rem;
            overflow-x: auto;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.76rem;
            line-height: 1.65;
            color: #e6eef1;
            white-space: pre;
            tab-size: 2;
        }
        /* JSON tokens. Dark-surface only, so no mode twin. */
        .es-cons-t-key { color: #67e8f9; }
        .es-cons-t-str { color: #6ee7b7; }
        .es-cons-t-num { color: #fcd34d; }
        .es-cons-t-pun { color: #8fa1a8; }

        /* --- Method chips. Dark-surface only. --- */
        .es-cons-m {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.14rem 0.42rem;
            border-radius: 0.28rem;
            border: 1px solid transparent;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            white-space: nowrap;
        }
        .es-cons-m-get { color: #67e8f9; background: rgba(34, 211, 238, 0.13); border-color: rgba(103, 232, 249, 0.3); }
        .es-cons-m-post { color: #6ee7b7; background: rgba(52, 211, 153, 0.13); border-color: rgba(110, 231, 183, 0.3); }
        .es-cons-m-put { color: #fcd34d; background: rgba(251, 191, 36, 0.13); border-color: rgba(252, 211, 77, 0.3); }
        .es-cons-m-del { color: #fca5a5; background: rgba(248, 113, 113, 0.13); border-color: rgba(252, 165, 165, 0.32); }

        /* --- Status-code chips. Dark-surface only. --- */
        .es-cons-sc {
            display: inline-flex;
            align-items: center;
            gap: 0.34rem;
            flex: none;
            padding: 0.14rem 0.46rem;
            border-radius: 0.28rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            white-space: nowrap;
            color: #6ee7b7;
            background: rgba(52, 211, 153, 0.12);
            border: 1px solid rgba(110, 231, 183, 0.3);
        }
        .es-cons-sc::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 9999px;
            background: currentColor;
        }
        .es-cons-sc-err { color: #fca5a5; background: rgba(248, 113, 113, 0.12); border-color: rgba(252, 165, 165, 0.32); }

        /* --- The exchange: request pane, a flowing hairline, response pane. --- */
        .es-cons-flow {
            position: relative;
            overflow: hidden;
            height: 1px;
            background: rgba(230, 238, 241, 0.12);
        }
        .es-cons-flow::after {
            content: "";
            position: absolute;
            top: -1px;
            left: 0;
            width: 18%;
            height: 3px;
            border-radius: 2px;
            background: linear-gradient(90deg, rgba(34, 211, 238, 0), #22d3ee, #34d399, rgba(52, 211, 153, 0));
            animation: es-cons-run 2.8s linear infinite;
        }
        @keyframes es-cons-run {
            from { transform: translateX(-120%); }
            to { transform: translateX(600%); }
        }

        /* --- Blinking block cursor after the headline. Stepped, so it
               reads as a cursor rather than a soft pulse. --- */
        .es-cons-caret {
            display: inline-block;
            width: 0.56ch;
            height: 0.88em;
            margin-left: 0.12em;
            vertical-align: -0.05em;
            border-radius: 1px;
            background: #155e75;
            animation: es-cons-blink 1.1s steps(1) infinite;
        }
        .dark .es-cons-caret { background: #67e8f9; }
        @keyframes es-cons-blink {
            0%, 50% { opacity: 1; }
            50.01%, 100% { opacity: 0; }
        }

        /* --- Ledger rows. The table gets a floor width so the three
               columns never crush; its wrapper scrolls, not the page. --- */
        .es-cons-table { min-width: 38rem; }
        .es-cons-tr { border-top: 1px solid rgba(230, 238, 241, 0.08); }
        .es-cons-tr:hover { background: rgba(230, 238, 241, 0.035); }
        .es-cons-grp { background: rgba(230, 238, 241, 0.05); }

        /* --- Plan tags on the mode-following ground --- */
        .es-cons-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.42rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(21, 94, 117, 0.42);
            color: #155e75;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .dark .es-cons-plan { border-color: rgba(103, 232, 249, 0.42); color: #67e8f9; }
        .es-cons-plan-pro { border-color: rgba(15, 20, 23, 0.32); color: #0f1417; }
        .dark .es-cons-plan-pro { border-color: rgba(230, 238, 241, 0.35); color: #e6eef1; }
        /* A plan tag can also sit on an ALWAYS-DARK console surface, where the
           light-mode inks would be invisible (#0f1417 on #0b1113 measures 1.03).
           Pin both, after the .dark rules so source order settles the tie. */
        .es-cons-term .es-cons-plan { border-color: rgba(103, 232, 249, 0.42); color: #67e8f9; }
        .es-cons-term .es-cons-plan-pro { border-color: rgba(230, 238, 241, 0.35); color: #e6eef1; }

        /* --- Chips --- */
        .es-cons-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.72rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 20, 23, 0.14);
            background: rgba(255, 255, 255, 0.75);
            color: #4a545b;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .dark .es-cons-chip {
            border-color: rgba(230, 238, 241, 0.16);
            background: rgba(230, 238, 241, 0.05);
            color: #b0bcc2;
        }

        /* --- Gradient accent word. Light stops on the light ground
               (6.59 and 4.97), bright stops only in dark. --- */
        .es-cons-grad {
            background-image: linear-gradient(100deg, #155e75, #047857);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }
        .dark .es-cons-grad { background-image: linear-gradient(100deg, #67e8f9, #6ee7b7); }

        /* --- Links and buttons --- */
        .es-cons-link { color: #0e7490; }
        .es-cons-link:hover { color: #0f1417; }
        .dark .es-cons-link { color: #67e8f9; }
        .dark .es-cons-link:hover { color: #e6eef1; }

        /* The button carries its own ink, in CSS rather than as a
           `dark:text-[...]` utility, so the fill and the text can never
           disagree. White on #0e7490 is 5.36; #06222a on #22d3ee is 10.8. */
        .es-cons-btn {
            background-color: #0e7490;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(14, 116, 144, 0.5);
        }
        .es-cons-btn:hover { background-color: #155e75; box-shadow: 0 22px 44px -14px rgba(14, 116, 144, 0.6); }
        .dark .es-cons-btn { background-color: #22d3ee; color: #06222a; }
        .dark .es-cons-btn:hover { background-color: #67e8f9; }

        /* --- Hover accents on FAQ and related cards --- */
        .es-cons-hover:hover { border-color: rgba(14, 116, 144, 0.5); }
        .dark .es-cons-hover:hover { border-color: rgba(103, 232, 249, 0.45); }
        .es-cons-hover:hover .es-cons-hover-t { color: #0e7490; }
        .dark .es-cons-hover:hover .es-cons-hover-t { color: #67e8f9; }

        /* --- The fixed-dark band. Same in both modes, like .es-cons-term. --- */
        .es-cons-band {
            background-color: #070b0d;
            background-image: radial-gradient(120% 100% at 50% 0%, #101a1d 0%, #0a1114 55%, #050809 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 238, 241, 0.05);
        }
        /* Shared classes that flip with the colour mode. Pin them, or the
           band stops being the same object in light and dark. */
        .es-cons-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 238, 241, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 238, 241, 0.05) 1px, transparent 1px);
        }
        .es-cons-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-cons-band .es-claim:focus-within {
            border-color: rgba(103, 232, 249, 0.75);
            box-shadow: 0 0 0 4px rgba(103, 232, 249, 0.22);
        }
        .es-cons-band .es-cons-mark { color: #9fb0b7; }
        .es-cons-band .es-cons-mark::before { background: #67e8f9; }
        .es-cons-band .es-cons-mark::after { background: rgba(230, 238, 241, 0.14); }
        .es-cons-band .es-cons-card {
            background: rgba(230, 238, 241, 0.05);
            border-color: rgba(230, 238, 241, 0.13);
        }
        .es-cons-band .es-cons-link { color: #67e8f9; }
        .es-cons-band .es-cons-link:hover { color: #e6eef1; }
        .es-cons-band .es-cons-btn { background-color: #0e7490; color: #ffffff; }
        .es-cons-band .es-cons-btn:hover { background-color: #155e75; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(14, 116, 144, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(34, 211, 238, 0.11), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(14, 116, 144, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(103, 232, 249, 0.6); }
        .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #0e7490, #047857); }
        .dark .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #67e8f9, #6ee7b7); }

        /* --- Focus rings. No border-radius here: setting it would change
               the element's own shape on focus. --- */
        #es-cons-page a:focus-visible,
        #es-cons-page summary:focus-visible,
        #es-cons-page input:focus-visible,
        #es-cons-page button:focus-visible {
            outline: 2px solid #0e7490;
            outline-offset: 3px;
        }
        .dark #es-cons-page a:focus-visible,
        .dark #es-cons-page summary:focus-visible,
        .dark #es-cons-page input:focus-visible,
        .dark #es-cons-page button:focus-visible {
            outline-color: #67e8f9;
        }
        .es-cons-band a:focus-visible,
        .es-cons-band summary:focus-visible,
        .es-cons-band input:focus-visible,
        .es-cons-band button:focus-visible {
            outline-color: #67e8f9 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-cons-caret { animation: none; opacity: 1; }
            .es-cons-flow::after { animation: none; opacity: 0.55; }
        }
    </style>

    @php
        // Every row below is a route in routes/api.php. 27 of them, and the
        // count is quoted in the copy, so keep the two in step.
        $methodClass = [
            'GET' => 'es-cons-m-get',
            'POST' => 'es-cons-m-post',
            'PUT' => 'es-cons-m-put',
            'DELETE' => 'es-cons-m-del',
        ];

        $ledger = [
            ['Auth', 'No key required. Each of these has its own throttle.', [
                ['POST', '/api/register/send-code', 'Mail a six-digit verification code. Hosted mode only, five per address per hour.'],
                ['POST', '/api/register', 'Create the account and return a key. Three per IP per hour.'],
                ['POST', '/api/login', 'Mint a key for an account that has none. A live key returns 409 instead.'],
            ]],
            ['Schedules', 'A schedule is the tenant: a venue, a talent or a curator.', [
                ['GET', '/api/schedules', 'List the schedules you own or administer. Filter by name and by type.'],
                ['GET', '/api/schedules/{subdomain}', 'One schedule, with its sub-schedules inlined.'],
                ['POST', '/api/schedules', 'Create one. name and type are required; the subdomain is generated from the name.'],
                ['PUT', '/api/schedules/{subdomain}', 'Name, contact, description, timezone, language, address. Partial payloads are fine.'],
                ['DELETE', '/api/schedules/{subdomain}', 'Marks it deleted, so it and its pages go dark. Owner level only, not admin.'],
            ]],
            ['Sub-schedules', 'Named strands inside one schedule, each with its own colour.', [
                ['GET', '/api/schedules/{subdomain}/groups', 'id, name, slug and colour for each sub-schedule.'],
                ['POST', '/api/schedules/{subdomain}/groups', 'name is required, colour optional. The slug is generated.'],
                ['PUT', '/api/schedules/{subdomain}/groups/{group_id}', 'Rename it or recolour it.'],
                ['DELETE', '/api/schedules/{subdomain}/groups/{group_id}', 'Events survive; their sub-schedule reference is cleared.'],
            ]],
            ['Events', 'The big one. Tickets, agenda parts, members and recurrence all ride along.', [
                ['GET', '/api/events', 'Paginated, newest first. Ten filters, including tickets_enabled and rsvp_enabled.'],
                ['GET', '/api/events/{id}', 'One event with its tickets, members and agenda parts.'],
                ['POST', '/api/events/{subdomain}', 'Create an event on a schedule. Carries its own 30-per-minute throttle.'],
                ['PUT', '/api/events/{id}', 'Partial update. Recurrence, tickets and agenda parts survive being omitted.'],
                ['DELETE', '/api/events/{id}', 'Delete it, and withdraw it from any synced calendar.'],
                ['POST', '/api/events/flyer/{event_id}', 'Multipart upload of a flyer_image for an existing event.'],
            ]],
            ['Categories', 'Read-only lookups so you can send a category_id you know exists.', [
                ['GET', '/api/categories', 'Every system category, with its id and name.'],
                ['GET', '/api/categories/{subdomain}', 'The effective list for one schedule, including its own custom categories.'],
            ]],
            ['Sales', 'A sale is a buyer, a set of ticket quantities and a status.', [
                ['GET', '/api/sales', 'Filter by event, subdomain, status, buyer email or occurrence date.'],
                ['GET', '/api/sales/{id}', 'One sale with its ticket lines.'],
                ['POST', '/api/sales', 'Book a sale by hand. Created unpaid; free tickets are marked paid straight away.'],
                ['PUT', '/api/sales/{id}', 'Apply an action: mark_paid, refund or cancel.'],
                ['DELETE', '/api/sales/{id}', 'Soft delete. It stops appearing in listings.'],
            ]],
            ['Feeds', 'Read-only, for pulling audience content onto a site you already run.', [
                ['GET', '/api/feedback', 'Post-event star ratings and comments. Filter by minimum rating and date range.'],
                ['GET', '/api/fan-content', 'Approved comments, photos and videos. Submitter email addresses are never included.'],
            ]],
        ];

        $endpointCount = collect($ledger)->sum(fn ($g) => count($g[2]));

        // Flattened for the hero ticker.
        $ticker = [];
        foreach ($ledger as [, , $rows]) {
            foreach ($rows as [$m, $p, ]) {
                $ticker[] = [$m, $p];
            }
        }

        $discovery = [
            [
                'llms.txt', '/llms.txt', '39 lines',
                'The short one. Schedule types, the auth header, the rate limits, the deployment modes and a four-step getting-started list, so an agent can decide in one fetch whether this API is relevant at all.',
            ],
            [
                'llms-full.txt', '/llms-full.txt', '1,906 lines',
                'The whole reference in one file, so an agent never has to follow a link to finish a task. Every endpoint, every parameter, every error shape.',
            ],
            [
                '/.well-known/agents.json', '/.well-known/agents.json', '4 flows',
                'Named multi-step flows: register_and_setup, create_event_with_tickets, sell_tickets and manage_schedule. Each one lists its calls in order, so a plan is data rather than prompt engineering.',
            ],
            [
                '/api/openapi.json', '/api/openapi.json', 'OpenAPI 3.0',
                'The machine-readable spec. Generate a client, or generate tool definitions, in whatever language your agent is written in.',
            ],
        ];

        // The heading below counts these, so the two have to be changed together.
        $webhookEvents = [
            ['sale.created', 'A sale is created, still unpaid.'],
            ['sale.paid', 'Confirmed paid, whether by Stripe, Invoice Ninja, by hand or free.'],
            ['sale.refunded', 'A paid sale is refunded.'],
            ['sale.cancelled', 'A sale is cancelled.'],
            ['installment.paid', 'A payment of an installment plan is collected.'],
            ['installment.failed', 'A scheduled payment could not be collected. Read outcome for why.'],
            ['event.created', 'A new event exists.'],
            ['event.updated', 'An event changed.'],
            ['event.deleted', 'An event is gone.'],
            ['event.cancelled', 'An event is cancelled.'],
            ['ticket.scanned', 'A ticket QR code is scanned at the door.'],
            ['ticket.booked', 'A pass holder reserves a place on a date.'],
            ['ticket.booking_cancelled', 'A pass holder releases a reserved place.'],
            ['feedback.submitted', 'An attendee left a rating.'],
        ];

        $steps = [
            ['01', 'Get a key',
             'One unauthenticated POST to <span class="es-cons-mono es-cons-key">/api/register</span>, or to <span class="es-cons-mono es-cons-key">/api/register/send-code</span> first in hosted mode. The response body carries the key and its expiry.',
             'X-API-Key: es_live_...'],
            ['02', 'Create a schedule',
             '<span class="es-cons-mono es-cons-key">POST /api/schedules</span> with a name and a type. The subdomain is generated from the name and the public page exists immediately.',
             '{"name": "Synth Lab", "type": "venue"}'],
            ['03', 'Create events',
             '<span class="es-cons-mono es-cons-key">POST /api/events/{subdomain}</span>. Ticket types, agenda parts and recurrence go in the same body, so there is no second round trip.',
             '{"name": "Analog Night", "duration": 3}'],
        ];

        $faqs = [
            [
                'q' => 'Is the API free to use?',
                'a' => 'The REST API is part of the Pro plan at $'.$proMonthly.' a month, with a seven-day trial when you subscribe. Selfhosted installations are Pro by definition, so running your own copy unlocks every endpoint at no cost. Ticket sales carry zero platform fees on every plan and in both modes: you keep everything except your payment processor\'s cut.',
            ],
            [
                'q' => 'How does authentication work?',
                'a' => 'One header, X-API-Key. Get a key from POST /api/register or POST /api/login, or generate one in your account settings. Keys are valid for a year. Login only mints a key when the account has none, and returns 409 while one is still live, so store the key rather than calling login on every run. Accounts with two-factor authentication have to generate keys from the web UI. Every endpoint except register, send-code and login requires the header.',
            ],
            [
                'q' => 'What can I actually do with it?',
                'a' => $endpointCount.' endpoints across registration, schedules, sub-schedules, events, categories and sales, plus two read-only feeds for post-event feedback and fan-submitted content. Schedules, sub-schedules, events and sales have full create, read, update and delete; categories are read-only lookups. A single create call can carry ticket types, agenda parts, performing members, a venue and a recurrence pattern, so publishing a run of shows is one request rather than six.',
            ],
            [
                'q' => 'What is llms.txt, and why are there two of them?',
                'a' => 'llms.txt is an emerging convention for telling a language model what a site is and where its documentation lives. Event Schedule publishes both: llms.txt is a short routing summary an agent can read to decide whether this API is relevant, and llms-full.txt is the entire reference in one file, so an agent that has decided to proceed never needs to follow a link.',
            ],
            [
                'q' => 'What are the rate limits?',
                'a' => '300 GET requests a minute and 30 POST, PUT or DELETE requests a minute, counted per IP address. Creating an event carries its own 30-per-minute throttle on top of that, and the auth endpoints have their own tighter limits. Going over returns 429 with an error body.',
            ],
            [
                'q' => 'Are there webhooks, or do I have to poll?',
                'a' => 'There are webhooks, on the Pro plan. Fourteen event types cover sales, event changes, door scans, pass bookings and feedback. Each delivery is signed with HMAC-SHA256 in an X-Webhook-Signature header so you can verify it came from us, payloads match the shapes the API returns, and there is a delivery log in your settings for debugging.',
            ],
            [
                'q' => 'Which IDs does the API use?',
                'a' => 'Encoded strings, never sequential integers. An event, a ticket, a sale and a sub-schedule all identify themselves with a short opaque string, and an event\'s is the same string that appears in its public URL, so you can build a link straight from a response. Category IDs are the exception: they are small integers you read from the categories endpoint.',
            ],
            [
                'q' => 'Can I run this against my own installation?',
                'a' => 'Yes. Event Schedule is open source and the API is the same in both modes: same routes, same spec, same discovery files served from your own domain. On a selfhosted install the Pro gate returns true unconditionally, so nothing is held back.',
            ],
        ];

        $dotSections = [
            ['top', 'One call'],
            ['contract', 'The contract'],
            ['ledger', $endpointCount.' endpoints'],
            ['discovery', 'Discovery files'],
            ['calls', 'Three exchanges'],
            ['push', 'Push, not poll'],
            ['rest', 'Everything else'],
            ['who', 'What gets built'],
            ['start', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Your key'],
        ];
    @endphp

    <div id="es-cons-page" class="es-cons-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the signature exchange                              -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(14, 116, 144, 0.24), rgba(14, 116, 144, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(4, 120, 87, 0.2), rgba(4, 120, 87, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 text-[#0e7490] dark:text-[#67e8f9]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span class="es-cons-muted text-sm font-medium tracking-wide">For AI agents, tool builders and developers</span>
                    </div>

                    <h1 class="es-balance es-cons-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">One POST, and the</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">show is <span class="es-cons-grad">on sale.</span><span class="es-cons-caret" aria-hidden="true"></span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-cons-muted mb-6 max-w-xl text-lg sm:text-xl">
                        {{ $endpointCount }} REST endpoints over the whole product: schedules, sub-schedules, events, recurrences, ticket types, sales, feedback and fan content. JSON in, JSON out, one header.
                    </p>
                    <p class="es-fade-up es-d-2 es-cons-muted mb-10 max-w-xl text-base">
                        An OpenAPI 3.0 spec, <span class="es-cons-mono es-cons-key">llms.txt</span> and <span class="es-cons-mono es-cons-key">agents.json</span> ship with it, so an agent can discover this API and drive it without a human reading the docs first.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#ledger" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See all {{ $endpointCount }} endpoints
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ route('marketing.docs.developer.api') }}" class="es-cons-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Read the API reference
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The exchange. Request pane, flowing hairline, response pane. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-cons-term overflow-hidden">
                        <div class="es-cons-bar">
                            <span class="es-cons-m es-cons-m-post">POST</span>
                            <span class="es-cons-mono es-cons-lit truncate text-xs">/api/events/synth-lab</span>
                            <span class="es-cons-mono es-cons-dim ms-auto text-[0.625rem]">X-API-Key</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">{</span>
  <span class="es-cons-t-key">"name"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Analog Night"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"starts_at"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"2026-08-14 20:00:00"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"duration"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">3</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"tickets_enabled"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">true</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"tickets"</span><span class="es-cons-t-pun">: [{</span>
    <span class="es-cons-t-key">"type"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Advance"</span><span class="es-cons-t-pun">,</span>
    <span class="es-cons-t-key">"price"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">18</span><span class="es-cons-t-pun">,</span>
    <span class="es-cons-t-key">"quantity"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">120</span>
  <span class="es-cons-t-pun">}]</span>
<span class="es-cons-t-pun">}</span></pre>
                        <div class="es-cons-flow" aria-hidden="true"></div>
                        <div class="es-cons-bar">
                            <span class="es-cons-sc">201 CREATED</span>
                            <span class="es-cons-mono es-cons-dim text-[0.625rem]">application/json</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">{ </span><span class="es-cons-t-key">"data"</span><span class="es-cons-t-pun">: {</span>
  <span class="es-cons-t-key">"id"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Kd3Vq7"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"url"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"https://synth-lab.eventschedule.com/analog-night/Kd3Vq7"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"tickets"</span><span class="es-cons-t-pun">: [{</span> <span class="es-cons-t-key">"id"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"9pR3vB"</span><span class="es-cons-t-pun">,</span> <span class="es-cons-t-key">"type"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Advance"</span> <span class="es-cons-t-pun">}]</span>
<span class="es-cons-t-pun">}, </span><span class="es-cons-t-key">"meta"</span><span class="es-cons-t-pun">: {</span> <span class="es-cons-t-key">"message"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Event created successfully"</span> <span class="es-cons-t-pun">} }</span></pre>
                        <p class="es-cons-rule es-cons-dim px-4 py-3 text-xs">
                            That one call also writes the event to Google, Outlook or CalDAV if the schedule is connected to one, and fires an <span class="es-cons-mono es-cons-lit-ok">event.created</span> webhook.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ticker: every path on the surface, on the console rail. -->
            <div class="es-fade-up es-d-4 mt-14">
                <div class="es-cons-term overflow-hidden py-3">
                    <div class="es-marquee-mask">
                        <div class="es-marquee" data-marquee="1" aria-hidden="true">
                            <div class="es-marquee-track">
                                @for ($copy = 0; $copy < 2; $copy++)
                                    @foreach ($ticker as [$tMethod, $tPath])
                                        <span class="inline-flex flex-none items-center gap-2">
                                            <span class="es-cons-m {{ $methodClass[$tMethod] }}">{{ $tMethod }}</span>
                                            <span class="es-cons-mono es-cons-dim text-xs">{{ $tPath }}</span>
                                        </span>
                                    @endforeach
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The contract                                              -->
    <!-- ============================================================ -->
    <section id="contract" class="scroll-mt-24 es-cons-hr border-y py-20 lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <div class="es-cons-mark mb-5" data-reveal><span>02 &middot; the contract</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Four facts, and you can <span class="es-cons-grad">start writing.</span>
                </h2>
                <p class="es-cons-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    No SDK to install, no OAuth dance, no sandbox to request. The whole surface behaves the same way, which is the only property an agent really needs.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                <div class="es-cons-card flex flex-col p-6" data-reveal>
                    <div class="es-cons-mono es-cons-key mb-3 text-sm font-bold">X-API-Key</div>
                    <p class="es-cons-ink mb-2 text-sm font-semibold">One header</p>
                    <p class="es-cons-muted mt-auto text-sm">Register, generate a key in your settings, or log in when you have none. Keys last a year. Nothing else is required.</p>
                </div>
                <div class="es-cons-card flex flex-col p-6" data-reveal>
                    <div class="es-cons-mono es-cons-key mb-3 text-sm font-bold">300 / 30</div>
                    <p class="es-cons-ink mb-2 text-sm font-semibold">Requests a minute</p>
                    <p class="es-cons-muted mt-auto text-sm">300 reads and 30 writes a minute, per IP. Over the line you get a 429, not a silent drop.</p>
                </div>
                <div class="es-cons-card flex flex-col p-6" data-reveal>
                    <div class="es-cons-mono es-cons-key mb-3 text-sm font-bold">per_page &le; 500</div>
                    <p class="es-cons-ink mb-2 text-sm font-semibold">The big lists paginate</p>
                    <p class="es-cons-muted mt-auto text-sm">100 by default, 500 at most, with a <span class="es-cons-mono">meta</span> block carrying the page, the total and the bounds. Categories and sub-schedules come back whole.</p>
                </div>
                <div class="es-cons-card flex flex-col p-6" data-reveal>
                    <div class="es-cons-mono es-cons-key mb-3 text-sm font-bold">Kd3Vq7</div>
                    <p class="es-cons-ink mb-2 text-sm font-semibold">IDs are opaque strings</p>
                    <p class="es-cons-muted mt-auto text-sm">Never a sequential integer, and an event's is the same string that appears in its public URL, so you can build a link from a response.</p>
                </div>
            </div>

            <!-- The envelope: success and failure, side by side. -->
            <div class="mt-4 grid gap-4 lg:grid-cols-2" data-reveal-group="80">
                <div class="es-cons-term overflow-hidden" data-reveal>
                    <div class="es-cons-bar">
                        <span class="es-cons-sc">2xx</span>
                        <span class="es-cons-mono es-cons-dim text-[0.625rem]">the success envelope</span>
                    </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">{</span>
  <span class="es-cons-t-key">"data"</span><span class="es-cons-t-pun">: [ ... ],</span>
  <span class="es-cons-t-key">"meta"</span><span class="es-cons-t-pun">: {</span> <span class="es-cons-t-key">"current_page"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">1</span><span class="es-cons-t-pun">,</span> <span class="es-cons-t-key">"total"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">50</span> <span class="es-cons-t-pun">}</span>
<span class="es-cons-t-pun">}</span></pre>
                </div>
                <div class="es-cons-term overflow-hidden" data-reveal>
                    <div class="es-cons-bar">
                        <span class="es-cons-sc es-cons-sc-err">422</span>
                        <span class="es-cons-mono es-cons-dim text-[0.625rem]">field-level errors, always in the same place</span>
                    </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">{</span>
  <span class="es-cons-t-key">"error"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Validation failed"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"errors"</span><span class="es-cons-t-pun">: {</span> <span class="es-cons-t-key">"starts_at"</span><span class="es-cons-t-pun">: [</span><span class="es-cons-t-str">"must match Y-m-d H:i:s"</span><span class="es-cons-t-pun">] }</span>
<span class="es-cons-t-pun">}</span></pre>
                </div>
            </div>
            <p class="es-cons-muted mt-4 text-sm" data-reveal>
                401 for a bad key, 403 when the plan or the permission is missing, 404, 422 with the offending fields named, 429 when throttled. A model can branch on that without guessing.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The ledger: every endpoint, in one table                  -->
    <!-- ============================================================ -->
    <section id="ledger" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <div class="es-cons-mark mb-5" data-reveal><span>03 &middot; the ledger</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    The entire surface, <span class="es-cons-grad">on one page.</span>
                </h2>
                <p class="es-cons-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    {{ $endpointCount }} endpoints. Not a summary of them, all of them. Everything past <span class="es-cons-mono es-cons-key">/api/login</span> needs the key header, and API access is part of the Pro plan.
                </p>
            </div>

            <div class="es-cons-term overflow-hidden" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-cons-table w-full border-collapse text-left">
                        <caption class="sr-only">Every Event Schedule API endpoint, grouped by resource, with its HTTP method, path and behaviour</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-cons-mono es-cons-dim px-4 py-3 text-[0.625rem] font-bold uppercase tracking-[0.2em]">Method</th>
                                <th scope="col" class="es-cons-mono es-cons-dim px-4 py-3 text-[0.625rem] font-bold uppercase tracking-[0.2em]">Path</th>
                                <th scope="col" class="es-cons-mono es-cons-dim px-4 py-3 text-[0.625rem] font-bold uppercase tracking-[0.2em]">Behaviour</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledger as [$groupName, $groupNote, $rows])
                                <tr class="es-cons-grp es-cons-tr">
                                    <th scope="colgroup" colspan="3" class="px-4 py-2.5">
                                        <span class="es-cons-mono es-cons-lit text-[0.6875rem] font-bold uppercase tracking-[0.2em]">{{ $groupName }}</span>
                                        <span class="es-cons-dim ms-3 text-xs font-normal">{{ $groupNote }}</span>
                                    </th>
                                </tr>
                                @foreach ($rows as [$rMethod, $rPath, $rNote])
                                    <tr class="es-cons-tr">
                                        <td class="px-4 py-3 align-top"><span class="es-cons-m {{ $methodClass[$rMethod] }}">{{ $rMethod }}</span></td>
                                        <th scope="row" class="es-cons-mono es-cons-bright px-4 py-3 align-top text-xs font-semibold">{{ $rPath }}</th>
                                        <td class="es-cons-dim px-4 py-3 align-top text-xs">{{ $rNote }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="es-cons-rule flex flex-wrap items-center gap-x-3 gap-y-2 px-4 py-3">
                    <span class="es-cons-mono es-cons-dim text-[0.625rem] font-bold uppercase tracking-[0.2em]">Colour is the verb</span>
                    <span class="es-cons-m es-cons-m-get">GET</span><span class="es-cons-dim text-xs">read</span>
                    <span class="es-cons-m es-cons-m-post">POST</span><span class="es-cons-dim text-xs">create</span>
                    <span class="es-cons-m es-cons-m-put">PUT</span><span class="es-cons-dim text-xs">update</span>
                    <span class="es-cons-m es-cons-m-del">DELETE</span><span class="es-cons-dim text-xs">remove</span>
                </div>
            </div>
            <p class="es-cons-muted mt-4 text-sm" data-reveal>
                Full documentation for each one, with a cURL example and a response body, is in the <a href="{{ route('marketing.docs.developer.api') }}" class="es-cons-link font-medium hover:underline">API reference</a>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Discovery files                                           -->
    <!-- ============================================================ -->
    <section id="discovery" class="scroll-mt-24 es-cons-hr border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <div class="es-cons-mark mb-5" data-reveal><span>04 &middot; discovery</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Four files an agent can <span class="es-cons-grad">read first.</span>
                </h2>
                <p class="es-cons-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Documentation written for a person is a bad input for a model. These four are written for the model, live at fixed paths, and are served from any installation, hosted or your own.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($discovery as [$dName, $dHref, $dMeta, $dDesc])
                    <a href="{{ $dHref }}" data-reveal class="es-cons-hover es-cons-card group flex flex-col gap-4 p-6 transition-all duration-200 hover:shadow-md sm:flex-row sm:items-start">
                        <div class="flex-none sm:w-52">
                            <div class="es-cons-hover-t es-cons-mono es-cons-key text-sm font-bold transition-colors">{{ $dName }}</div>
                            <div class="es-cons-mono es-cons-muted mt-1 text-xs">{{ $dMeta }}</div>
                        </div>
                        <p class="es-cons-muted flex-1 text-sm leading-relaxed">{{ $dDesc }}</p>
                        <svg aria-hidden="true" class="es-cons-hover-t es-cons-muted h-5 w-5 flex-none transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Three exchanges you will actually write                    -->
    <!-- ============================================================ -->
    <section id="calls" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <div class="es-cons-mark mb-5" data-reveal><span>05 &middot; exchanges</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three calls you will <span class="es-cons-grad">actually write.</span>
                </h2>
                <p class="es-cons-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Filtering a calendar, standing up a weekly residency, and settling a sale. Everything else is a variation on these.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-3" data-reveal-group="90">

                <!-- a. Filtered read -->
                <div class="flex flex-col" data-reveal>
                    <div class="es-cons-term flex-1 overflow-hidden">
                        <div class="es-cons-bar">
                            <span class="es-cons-m es-cons-m-get">GET</span>
                            <span class="es-cons-mono es-cons-lit truncate text-xs">/api/events</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">?</span><span class="es-cons-t-key">subdomain</span><span class="es-cons-t-pun">=</span><span class="es-cons-t-str">synth-lab</span>
<span class="es-cons-t-pun">&amp;</span><span class="es-cons-t-key">starts_after</span><span class="es-cons-t-pun">=</span><span class="es-cons-t-str">2026-08-01</span>
<span class="es-cons-t-pun">&amp;</span><span class="es-cons-t-key">tickets_enabled</span><span class="es-cons-t-pun">=</span><span class="es-cons-t-num">1</span>
<span class="es-cons-t-pun">&amp;</span><span class="es-cons-t-key">per_page</span><span class="es-cons-t-pun">=</span><span class="es-cons-t-num">50</span></pre>
                        <div class="es-cons-flow" aria-hidden="true"></div>
                        <div class="es-cons-bar">
                            <span class="es-cons-sc">200 OK</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-key">"meta"</span><span class="es-cons-t-pun">: {</span>
  <span class="es-cons-t-key">"current_page"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">1</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"last_page"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">2</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"total"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">63</span>
<span class="es-cons-t-pun">}</span></pre>
                    </div>
                    <p class="es-cons-muted mt-4 text-sm">
                        Ten filters on the events list, including whether tickets or RSVP are switched on, a venue, a sub-schedule and a date window. You narrow server-side rather than pulling a year and filtering in the agent.
                    </p>
                </div>

                <!-- b. Recurrence as data -->
                <div class="flex flex-col" data-reveal>
                    <div class="es-cons-term flex-1 overflow-hidden">
                        <div class="es-cons-bar">
                            <span class="es-cons-m es-cons-m-post">POST</span>
                            <span class="es-cons-mono es-cons-lit truncate text-xs">/api/events/synth-lab</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-key">"schedule_type"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"recurring"</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"recurring_frequency"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"weekly"</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"days_of_week"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"0111110"</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"recurring_end_type"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"after_events"</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"recurring_end_value"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"14"</span></pre>
                        <div class="es-cons-flow" aria-hidden="true"></div>
                        <div class="es-cons-bar">
                            <span class="es-cons-sc">201 CREATED</span>
                            <span class="es-cons-mono es-cons-dim text-[0.625rem]">one event, fourteen dates</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-key">"schedule_type"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"recurring"</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"days_of_week"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"0111110"</span></pre>
                    </div>
                    <p class="es-cons-muted mt-4 text-sm">
                        A seven-character mask, Sunday first, so Monday to Friday is <span class="es-cons-mono es-cons-key">"0111110"</span>. Frequency is one of daily, weekly, every_n_weeks, monthly_date, monthly_weekday or yearly, and a run can end never, on a date, or after a set number of occurrences.
                    </p>
                </div>

                <!-- c. Settle a sale -->
                <div class="flex flex-col" data-reveal>
                    <div class="es-cons-term flex-1 overflow-hidden">
                        <div class="es-cons-bar">
                            <span class="es-cons-m es-cons-m-put">PUT</span>
                            <span class="es-cons-mono es-cons-lit truncate text-xs">/api/sales/7bQx2m</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">{</span> <span class="es-cons-t-key">"action"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"mark_paid"</span> <span class="es-cons-t-pun">}</span></pre>
                        <div class="es-cons-flow" aria-hidden="true"></div>
                        <div class="es-cons-bar">
                            <span class="es-cons-sc">200 OK</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-key">"status"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"paid"</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"payment_amount"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">36</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"total_quantity"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-num">2</span><span class="es-cons-t-pun">,</span>
<span class="es-cons-t-key">"tickets"</span><span class="es-cons-t-pun">: [{</span> <span class="es-cons-t-key">"type"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Advance"</span> <span class="es-cons-t-pun">}]</span></pre>
                    </div>
                    <p class="es-cons-muted mt-4 text-sm">
                        Three actions, and which ones are legal depends on where the sale is: <span class="es-cons-mono es-cons-key">mark_paid</span> from unpaid, <span class="es-cons-mono es-cons-key">refund</span> from paid, <span class="es-cons-mono es-cons-key">cancel</span> from either. You can also create a sale outright for a buyer who paid you off-platform.
                    </p>
                </div>
            </div>

            <div class="es-cons-card mt-6 p-6" data-reveal>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <span class="es-cons-plan es-cons-plan-pro mt-1">how matching works</span>
                    <p class="es-cons-muted flex-1 text-sm leading-relaxed">
                        You can name a venue or a performer instead of looking up an ID: send <span class="es-cons-mono es-cons-key">venue_name</span> with <span class="es-cons-mono es-cons-key">venue_address1</span>, or <span class="es-cons-mono es-cons-key">members</span> as a list of names and emails, and the API resolves them to schedules on your account. To be exact about what that is: it matches an existing schedule you own or follow, and returns a 422 naming the one it could not find. It does not invent a venue for you. Categories work the same way: send <span class="es-cons-mono es-cons-key">category</span> as a name and it is matched against that schedule's category list.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Push, not poll (fixed-dark band)                          -->
    <!-- ============================================================ -->
    <section id="push" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-cons-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-4 py-16 sm:px-8 lg:px-12 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mb-12 max-w-3xl">
                    <div class="es-cons-mark mb-5" data-reveal><span>06 &middot; webhooks</span></div>
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                        Or stop asking, and <span class="es-cons-lit">get told.</span>
                    </h2>
                    <p class="es-cons-dim text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        Polling a sales endpoint every minute is a waste of both our time. Register an endpoint and the traffic reverses: we POST to you, signed, the moment something happens.
                    </p>
                </div>

                <div class="grid gap-4 lg:grid-cols-[1fr_1.05fr]" data-reveal-group="90">
                    <!-- the delivery -->
                    <div data-reveal>
                        <div class="es-cons-term overflow-hidden">
                            <div class="es-cons-bar">
                                <span class="es-cons-m es-cons-m-post">POST</span>
                                <span class="es-cons-mono es-cons-lit truncate text-xs">https://your-app.example/hooks</span>
                            </div>
<pre class="es-cons-pre"><span class="es-cons-t-key">X-Webhook-Event</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">sale.paid</span>
<span class="es-cons-t-key">X-Webhook-Signature</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">sha256=&lt;hex&gt;</span>
<span class="es-cons-t-key">X-Webhook-Timestamp</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">2026-08-14T20:11:04+00:00</span>
<span class="es-cons-t-key">User-Agent</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">EventSchedule-Webhook/1.0</span>

<span class="es-cons-t-pun">{</span> <span class="es-cons-t-key">"event"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"sale.paid"</span><span class="es-cons-t-pun">,</span> <span class="es-cons-t-key">"data"</span><span class="es-cons-t-pun">: {</span> ... <span class="es-cons-t-pun">} }</span></pre>
                        </div>
                        <div class="es-cons-card mt-4 p-5">
                            <p class="es-cons-dim text-sm leading-relaxed">
                                The signature is an HMAC-SHA256 of the raw body, keyed on a secret shown once when you add the hook. Verify it before you trust the payload. <span class="es-cons-bright font-semibold">Key on <span class="es-cons-mono">data.id</span> plus the event type</span>, because a delivery can repeat and one sale fires several types. There is a delivery log in your settings when something goes wrong.
                            </p>
                            <p class="mt-4 text-sm">
                                <a href="{{ route('marketing.docs.developer.webhooks') }}" class="es-cons-link font-medium hover:underline">Webhook reference, with verification snippets</a>
                            </p>
                        </div>
                    </div>

                    <!-- the fourteen types -->
                    <div data-reveal>
                        <div class="es-cons-term overflow-hidden">
                            <div class="es-cons-bar">
                                <span class="es-cons-mono es-cons-lit text-xs font-bold uppercase tracking-[0.2em]">Fourteen event types</span>
                                <span class="es-cons-plan es-cons-plan-pro ms-auto">pro</span>
                            </div>
                            <ul>
                                @foreach ($webhookEvents as [$wName, $wDesc])
                                    <li class="@if (! $loop->first) es-cons-tr @endif flex flex-col gap-1 px-4 py-2.5 sm:flex-row sm:items-baseline sm:gap-4">
                                        <span class="es-cons-mono es-cons-lit-ok flex-none text-xs font-semibold sm:w-40">{{ $wName }}</span>
                                        <span class="es-cons-dim text-xs">{{ $wDesc }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Everything else: bento                                     -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <div class="es-cons-mark mb-5" data-reveal><span>07 &middot; the rest</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    The parts that make it <span class="es-cons-grad">safe to automate.</span>
                </h2>
                <p class="es-cons-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Details that only matter once your code is running unattended, which is exactly when they matter most.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="100">

                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-cons-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <span class="es-cons-plan">free and pro</span>
                            <span class="es-cons-chip">open source</span>
                        </div>
                        <h3 class="es-cons-ink mb-3 text-2xl font-bold tracking-tight lg:text-3xl">Your own install, same API</h3>
                        <p class="es-cons-muted mb-6 text-base leading-relaxed lg:text-lg">
                            Event Schedule is open source, and the API does not change when you host it yourself: same routes, same OpenAPI spec, same discovery files, served from your own domain. On a selfhosted install the Pro gate returns true unconditionally, so no endpoint is held back and no key talks to anyone else's server.
                        </p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="es-cons-chip">docker or bare metal</span>
                            <span class="es-cons-chip">your database</span>
                            <span class="es-cons-chip">no outbound calls required</span>
                        </div>
                        <p class="mt-5 text-sm">
                            <a href="{{ marketing_url('/selfhost') }}" class="es-cons-link font-medium hover:underline">How selfhosting works</a>
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-cons-card relative flex h-full flex-col overflow-hidden p-7">
                        <span class="es-cons-plan es-cons-plan-pro mb-4 self-start">pro</span>
                        <h3 class="es-cons-ink mb-3 text-xl font-bold">Flyers, as a second call</h3>
                        <p class="es-cons-muted mb-5 text-sm leading-relaxed">
                            Artwork is multipart, so it gets its own request. Create the event, then POST a <span class="es-cons-mono es-cons-key">flyer_image</span> to the flyer endpoint with the returned ID.
                        </p>
                        <div class="es-cons-term mt-auto overflow-hidden">
<pre class="es-cons-pre">curl -X POST <span class="es-cons-t-str">.../api/events/flyer/Kd3Vq7</span> \
  -H <span class="es-cons-t-str">"X-API-Key: $KEY"</span> \
  -F <span class="es-cons-t-str">"flyer_image=@night.jpg"</span></pre>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-cons-card relative flex h-full flex-col overflow-hidden p-7">
                        <span class="es-cons-plan mb-4 self-start">free and pro</span>
                        <h3 class="es-cons-ink mb-3 text-xl font-bold">Languages, made explicit</h3>
                        <p class="es-cons-muted mb-5 text-sm leading-relaxed">
                            Set <span class="es-cons-mono es-cons-key">language_code</span> on a schedule and its pages are served in that language; twelve are supported. A schedule can also nominate one translation target, and its own copy is machine-translated into it on a scheduled pass.
                        </p>
                        <div class="mt-auto flex flex-wrap gap-1.5">
                            @foreach (['ar', 'de', 'en', 'es', 'et', 'fr', 'he', 'it', 'nl', 'pt', 'ro', 'ru'] as $lc)
                                <span class="es-cons-mono es-cons-chip px-2 py-0.5 text-[0.6875rem] uppercase">{{ $lc }}</span>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-cons-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <span class="es-cons-plan es-cons-plan-pro mb-4 self-start">pro</span>
                        <h3 class="es-cons-ink mb-3 text-2xl font-bold tracking-tight lg:text-3xl">Money, without a middleman</h3>
                        <p class="es-cons-muted mb-6 text-base leading-relaxed lg:text-lg">
                            Ticket types created through the API sell through your own Stripe account, or through Invoice Ninja, a payment URL, or by hand. Event Schedule takes zero platform fees on ticket sales: the only deduction is your processor's. Sales come back through the sales endpoints and through <span class="es-cons-mono es-cons-key">sale.paid</span> webhooks, with the ticket lines attached.
                        </p>
                        <div class="mt-auto flex flex-wrap gap-2">
                            <span class="es-cons-chip">stripe</span>
                            <span class="es-cons-chip">invoiceninja</span>
                            <span class="es-cons-chip">payment_url</span>
                            <span class="es-cons-chip">manual</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner es-cons-card relative flex h-full flex-col overflow-hidden p-7">
                        <span class="es-cons-plan es-cons-plan-pro mb-4 self-start">pro</span>
                        <h3 class="es-cons-ink mb-3 text-xl font-bold">Read-only feeds</h3>
                        <p class="es-cons-muted mb-5 text-sm leading-relaxed">
                            Two endpoints exist purely so you can pull audience content somewhere else: post-event ratings and comments, and approved fan photos, videos and comments. Fan submissions carry a display name only; the ratings feed names the attendee, so treat it as owner-facing.
                        </p>
                        <p class="es-cons-muted mt-auto text-xs leading-relaxed">
                            Each kind of fan submission has its own ID sequence, so key on <span class="es-cons-mono es-cons-key">type</span> and <span class="es-cons-mono es-cons-key">id</span> together when you store a row.
                        </p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner es-cons-card relative flex h-full flex-col overflow-hidden p-7 lg:p-9">
                        <span class="es-cons-plan es-cons-plan-pro mb-4 self-start">pro</span>
                        <h3 class="es-cons-ink mb-3 text-2xl font-bold tracking-tight lg:text-3xl">Partial writes that keep their nerve</h3>
                        <p class="es-cons-muted mb-6 text-base leading-relaxed lg:text-lg">
                            <span class="es-cons-mono es-cons-key">PUT</span> takes the same body as create and applies only what you send. Recurrence configuration, ticket types and agenda parts are preserved when they are absent, so an agent that only knows the new start time cannot quietly erase a run's ticket tiers. Every write is scoped to the schedules the key's owner owns or administers, and anything outside that returns 403 rather than silently doing nothing.
                        </p>
                        <div class="mt-auto grid gap-3 sm:grid-cols-3">
                            <div class="es-cons-term p-4">
                                <div class="es-cons-mono es-cons-lit text-xs font-bold">401</div>
                                <p class="es-cons-dim mt-1 text-xs">Key missing, wrong or expired.</p>
                            </div>
                            <div class="es-cons-term p-4">
                                <div class="es-cons-mono es-cons-lit text-xs font-bold">403</div>
                                <p class="es-cons-dim mt-1 text-xs">Not your schedule, or the plan does not cover it.</p>
                            </div>
                            <div class="es-cons-term p-4">
                                <div class="es-cons-mono es-cons-lit text-xs font-bold">429</div>
                                <p class="es-cons-dim mt-1 text-xs">Throttled. Back off and retry.</p>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. What people build with it                                  -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-cons-hr border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <div class="es-cons-mark mb-5" data-reveal><span>08 &middot; callers</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    What people point at <span class="es-cons-grad">this API.</span>
                </h2>
                <p class="es-cons-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    An HTTP API has no opinion about what is calling it, which is the point.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="AI Assistants"
                    description="Turn a conversation into a published event. Register, create the schedule and create the event in three calls, then hand back the URL from the response."
                    icon-color="cyan"
                    blog-slug="for-ai-assistants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Developer Tools & Scripts"
                    description="A cron job, a CLI, a one-off migration. Generate a client from the OpenAPI spec and the whole surface is typed for you."
                    icon-color="teal"
                    blog-slug="for-developer-tools"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Community Bots"
                    description="A Discord, Slack or Telegram bot that creates the event when someone announces it in the channel, and posts the ticket link back."
                    icon-color="emerald"
                    blog-slug="for-community-bots"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Booking Platforms"
                    description="Keep your own front end and let Event Schedule hold the events, the ticket types and the sales. Webhooks push each paid sale straight back to you."
                    icon-color="sky"
                    blog-slug="for-booking-platforms"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Calendar Aggregators"
                    description="Pull a date window with the events filters, or take the iCal feed and skip the API entirely. Both come off the same schedule."
                    icon-color="blue"
                    blog-slug="for-calendar-aggregators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Custom Integrations"
                    description="Anything that speaks HTTP and JSON. If you would rather not write the client, the OpenAPI spec will write it for you."
                    icon-color="amber"
                    blog-slug="for-custom-integrations"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps                                               -->
    <!-- ============================================================ -->
    <section id="start" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 max-w-3xl">
                <div class="es-cons-mark mb-5" data-reveal><span>09 &middot; first run</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three requests from nothing to <span class="es-cons-grad">a live page.</span>
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ($steps as [$sNum, $sTitle, $sDesc, $sCode])
                    <div class="es-cons-card flex flex-col p-6" data-reveal>
                        <div class="es-cons-mono es-cons-key mb-4 text-sm font-bold">{{ $sNum }}</div>
                        <h3 class="es-cons-ink mb-2 text-lg font-bold">{{ $sTitle }}</h3>
                        <p class="es-cons-muted mb-5 text-sm leading-relaxed">{!! $sDesc !!}</p>
                        <div class="es-cons-term mt-auto overflow-hidden">
                            <div class="es-cons-mono es-cons-lit-ok overflow-x-auto whitespace-pre px-3 py-2.5 text-[0.7rem]">{{ $sCode }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="es-cons-hr border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cons-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Ticket types, QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way Google, Outlook and CalDAV sync on every plan" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the schedule on the site you already run" :url="marketing_url('/features/embed-calendar')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Page views, devices and traffic sources, free on every plan" :url="marketing_url('/features/analytics')" icon-color="cyan">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-cons-link inline-flex items-center font-medium hover:underline">
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
    <!-- 11. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-cons-hr border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cons-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-webinars', 'Webinars'], ['/for-virtual-conferences', 'Virtual Conferences'], ['/for-curators', 'Curators'], ['/for-online-classes', 'Online Classes']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-cons-hover es-cons-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-cons-hover-t es-cons-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-cons-muted mt-auto inline-flex items-center gap-1 text-xs font-medium">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-cons-link inline-flex items-center font-medium hover:underline">
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

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <div class="es-cons-mark mb-5" data-reveal><span>10 &middot; questions</span></div>
                <h2 class="es-balance es-cons-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-cons-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What developers ask before they write the first request.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-cons-hover es-cons-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-cons-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-cons-mono es-cons-key flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-cons-hover-t flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-cons-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-cons-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale: the exchange that hands you a key                -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-cons-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 shadow-2xl sm:px-12 lg:py-20" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10 grid items-center gap-12 lg:grid-cols-[1.05fr_1fr]">
                    <div>
                        <div class="es-cons-mark mb-5"><span>11 &middot; your key</span></div>
                        <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-5xl">
                            The last call is the <span class="es-cons-lit">first one.</span>
                        </h2>
                        <p class="es-cons-dim mb-10 max-w-xl text-lg">
                            Pick a name and start, or register straight from your code. Publishing a schedule and its dates is free forever, and so are the first 25 paid tickets a month; the API and unlimited ticket sales are ${{ $proMonthly }} a month, and Event Schedule takes nothing from the door.
                        </p>

                        <div class="flex max-w-2xl flex-col items-stretch gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-agent" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="es-cons-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                                <span class="relative z-10 flex items-center gap-2">
                                    Get started free
                                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </span>
                                <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                            </a>
                        </div>

                        <p class="es-cons-dim mt-6 text-sm">
                            No card to start. Or go straight to the <a href="{{ route('marketing.docs.developer.api') }}" class="es-cons-link font-medium hover:underline">API reference</a> and the <a href="/api/openapi.json" class="es-cons-link font-medium hover:underline">OpenAPI spec</a>.
                        </p>
                    </div>

                    <div class="es-cons-term overflow-hidden">
                        <div class="es-cons-bar">
                            <span class="es-cons-m es-cons-m-post">POST</span>
                            <span class="es-cons-mono es-cons-lit text-xs">/api/register</span>
                            <span class="es-cons-mono es-cons-dim ms-auto text-[0.625rem]">no key required</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">{</span>
  <span class="es-cons-t-key">"name"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"Your Agent"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"email"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"you@example.com"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"password"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"..."</span>
<span class="es-cons-t-pun">}</span></pre>
                        <div class="es-cons-flow" aria-hidden="true"></div>
                        <div class="es-cons-bar">
                            <span class="es-cons-sc">201 CREATED</span>
                        </div>
<pre class="es-cons-pre"><span class="es-cons-t-pun">{ </span><span class="es-cons-t-key">"data"</span><span class="es-cons-t-pun">: {</span>
  <span class="es-cons-t-key">"api_key"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"your_new_api_key"</span><span class="es-cons-t-pun">,</span>
  <span class="es-cons-t-key">"api_key_expires_at"</span><span class="es-cons-t-pun">:</span> <span class="es-cons-t-str">"2027-07-30T00:00:00Z"</span>
<span class="es-cons-t-pun">} }</span></pre>
                        <p class="es-cons-rule es-cons-dim px-4 py-3 text-xs">
                            In hosted mode, <span class="es-cons-mono es-cons-lit">POST /api/register/send-code</span> mails a six-digit code first, and you pass it as <span class="es-cons-mono es-cons-lit">verification_code</span>.
                        </p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#0f0f14] dark:text-gray-300">{{ $sectionLabel }}</span>
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
