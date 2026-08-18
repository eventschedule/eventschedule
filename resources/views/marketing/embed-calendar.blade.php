<x-marketing-layout>
    <x-slot name="title">Embed Calendar | Add Events to Any Website - Event Schedule</x-slot>
    <x-slot name="description">Embed your event calendar on any website with one line of code. Responsive iframe with dark mode support and 12 languages.</x-slot>
    <x-slot name="breadcrumbTitle">Embed Calendar</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Embed Calendar",
        "description": "Embed your event calendar on any website with one line of code. Responsive iframe with dark mode support and 12 languages.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Embeddable Calendar Widget"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Embed Calendar",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Website Integration Software",
        "operatingSystem": "Web",
        "description": "Embed your event calendar on any website with one line of code. Responsive iframe with dark mode and multilingual support.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Included free"
        },
        "featureList": [
            "One iframe tag, no script and no dependency",
            "Responsive width, with the height set in the tag",
            "Dark mode forced with a URL parameter or left to the visitor's system setting",
            "12 interface languages, including right-to-left layout",
            "Filter the frame down to a single sub-schedule",
            "Header, footer, banner and branding stripped inside the frame",
            "Served noindex so your own page is the one search engines read"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to embed your Event Schedule calendar on your own website",
        "description": "Copy one iframe tag out of your schedule, paste it into your page, and leave it alone.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Copy the tag",
                "text": "Open your schedule in the admin portal and choose Embed Schedule. The panel shows the embed URL, the iframe tag and a live preview of the calendar."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Paste it into your page",
                "text": "Drop the tag into your page's HTML wherever the calendar belongs. Any CMS block that accepts raw HTML or an embed will take it."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Leave it alone",
                "text": "The tag never changes again. Add an event or move a date in Event Schedule and the page on your own site is already correct."
            }
        ]
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
           Embed-calendar "The Paste" styles.

           THE CONCEPT. An embed is a transplant: one line leaves this
           product and lives in a document somebody else owns. So the
           page is built out of the two objects that transaction has -
           the SLIP you copied, and the RECTANGLE it reserves in the
           host page - and the product argument is the same sentence as
           the metaphor: you paste once, and the rectangle is never out
           of date again.

           DEVICES THIS PAGE MUST NOT BUILD. /open-source owns "The
           Commit Log": a spine with nodes, path chips and a unified
           diff. /for-ai-agents owns "The Console": a request/response
           ledger, a block-cursor status line and an always-dark code
           surface. /selfhost owns "The Terminal" with window chrome.
           So there is no gutter of line numbers, no +/- diff, no fake
           browser bar with three traffic lights (the first-wave version
           of this page had one, and it was a costume), and the code
           surface here goes the OTHER way: it is white in both colour
           modes, because it is a slip of paper you carry, not a
           terminal.

           MATERIAL / FIXED OBJECT. .es-paste-slip renders identically
           with .dark on and off, and so does .es-paste-band. Verify
           with --bands=.es-paste-band,.es-paste-slip (expect 0 diffs).
           Nothing inside either one may use a `dark:` utility or a
           shared class that carries its own .dark rule in
           marketing.css, so .grid-overlay, .animate-shimmer,
           .es-claim:focus-within and .es-paste-btn get band-scoped
           overrides AFTER the base rules, .es-paste-plan gets a
           slip-scoped one, and .es-aurora / .glass are never used
           inside a band (they flip opacity and cannot be pinned).

           COLOUR. The hue family stays what this page already had -
           blue - but it is spent as a SELECTION HIGHLIGHT rather than
           as gradient heading text, because a paste starts life as
           selected text. #0b4fc7 (6.29 on the cool ground) and #9cc0ff
           (11.6 on the ink ground). Deliberately not the shared brand
           ramp #4E81FA -> #0EA5E9 -> #22D3EE, and deliberately not
           /open-source's near-monochrome #1e40af / #8fb3ff. No cyan or
           sky stop: /for-djs, /for-venues and /for-dance-groups hold
           those.

           MUTED INK. Never text-gray-500 here: #6b7280 measures 4.83 on
           pure white but only ~4.4 on this page's tinted ground. Use
           .es-paste-muted (6.76 on the ground, 7.6 on a white card),
           .es-paste-dim inside a fixed-dark band (6.79), and
           .es-paste-slip-dim on the always-white slip (5.99).

           NO ARBITRARY-VALUE TAILWIND for anything design-critical: the
           build is not run during this campaign, so a class that is not
           already in public/build/assets/marketing-app-*.css silently
           does nothing. Every colour and material below is a real rule.

           BLADE RULE: no @supports() probe with a "#" hex in the
           condition - it breaks compilation of every later
           parenthesized directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-paste-page { background-color: #eef1f6; color: #111722; }
        .dark .es-paste-page { background-color: #0a0e14; color: #e6ecf5; }
        .es-paste-ink { color: #111722; }
        .dark .es-paste-ink { color: #e6ecf5; }
        .es-paste-muted { color: #4a5462; }
        .dark .es-paste-muted { color: #98a4b6; }
        .es-paste-accent { color: #0b4fc7; }
        .dark .es-paste-accent { color: #9cc0ff; }
        .es-paste-mono {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
        }
        /* Section separator, the tiny mono label size and its tracking, all as
           real rules: the arbitrary-value Tailwind classes this page reached for
           (border-[rgba(...)], text-[0.62rem], tracking-[0.24em]) are not in the
           built stylesheet and the build is never run during this campaign, so
           they silently did nothing. */
        .es-paste-rule { border-top: 1px solid rgba(17, 23, 34, 0.08); }
        .dark .es-paste-rule { border-top-color: rgba(230, 236, 245, 0.08); }
        .es-paste-xs { font-size: 0.62rem; }
        .es-paste-track { letter-spacing: 0.24em; }

        /* --- THE SIGNATURE: the accent is a selection highlight behind a
               run of words, not a gradient. A paste begins as a
               selection, and a highlight is also the one accent shape
               that cannot fail a contrast check by colour stop. ------ */
        .es-paste-sel {
            background-color: #c9dcff;
            color: #0d1727;
            padding: 0 0.18em;
            border-radius: 2px;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
        .dark .es-paste-sel { background-color: #1c4b96; color: #e8f0ff; }

        /* --- Cards ---------------------------------------------------- */
        .es-paste-card {
            background-color: #ffffff;
            border: 1px solid rgba(17, 23, 34, 0.12);
            border-radius: 1rem;
        }
        .dark .es-paste-card { background-color: #121926; border-color: rgba(230, 236, 245, 0.12); }

        /* --- The host document: someone else's page, with a rectangle
               reserved in the middle of it. This one DOES flip with the
               colour mode, because a host site is whatever the host's
               site is. -------------------------------------------- */
        .es-paste-host {
            background-color: #ffffff;
            border: 1px solid rgba(17, 23, 34, 0.14);
            border-radius: 0.9rem;
            box-shadow: 0 26px 55px -30px rgba(11, 30, 66, 0.4);
        }
        .dark .es-paste-host {
            background-color: #101724;
            border-color: rgba(230, 236, 245, 0.14);
            box-shadow: 0 26px 55px -30px rgba(0, 0, 0, 0.75);
        }
        .es-paste-host-bar {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 0.85rem;
            border-bottom: 1px solid rgba(17, 23, 34, 0.1);
        }
        .dark .es-paste-host-bar { border-color: rgba(230, 236, 245, 0.1); }
        .es-paste-host-mark {
            flex: none;
            width: 0.85rem; height: 0.85rem;
            border-radius: 0.25rem;
            background: rgba(17, 23, 34, 0.32);
        }
        .dark .es-paste-host-mark { background: rgba(230, 236, 245, 0.32); }
        /* The host page's own content, abstracted to bars so it reads as
           "not yours" without drawing a picture of a website. */
        .es-paste-bar { height: 0.4rem; border-radius: 9999px; background: rgba(17, 23, 34, 0.14); }
        .dark .es-paste-bar { background: rgba(230, 236, 245, 0.16); }
        .es-paste-bar-head { height: 0.8rem; border-radius: 0.2rem; background: rgba(17, 23, 34, 0.28); }
        .dark .es-paste-bar-head { background: rgba(230, 236, 245, 0.3); }

        /* --- The reserved rectangle, and marching ants. Marching ants
               are the selection border every operating system draws
               around something copied, which is exactly what this
               rectangle is waiting for. --------------------------- */
        .es-paste-slot {
            position: relative;
            background-color: #e3ebfb;
            border-radius: 0.5rem;
        }
        .dark .es-paste-slot { background-color: #141d2d; }
        .es-paste-ants {
            --es-ant: rgba(11, 79, 199, 0.9);
            background-image:
                repeating-linear-gradient(90deg, var(--es-ant) 0 7px, rgba(0, 0, 0, 0) 7px 14px),
                repeating-linear-gradient(90deg, var(--es-ant) 0 7px, rgba(0, 0, 0, 0) 7px 14px),
                repeating-linear-gradient(0deg, var(--es-ant) 0 7px, rgba(0, 0, 0, 0) 7px 14px),
                repeating-linear-gradient(0deg, var(--es-ant) 0 7px, rgba(0, 0, 0, 0) 7px 14px);
            background-size: 100% 2px, 100% 2px, 2px 100%, 2px 100%;
            background-position: 0 0, 0 100%, 0 0, 100% 0;
            background-repeat: no-repeat;
            animation: es-paste-march 1.6s linear infinite;
        }
        .dark .es-paste-ants { --es-ant: rgba(156, 192, 255, 0.9); }
        @keyframes es-paste-march {
            to { background-position: 14px 0, -14px 100%, 0 -14px, 100% 14px; }
        }

        /* --- THE SLIP: the thing on your clipboard. White in BOTH
               colour modes, because it is one physical object. Its inks
               carry no `dark:` twin by design. ------------------- */
        .es-paste-slip {
            background-color: #ffffff;
            border: 1px solid rgba(17, 23, 34, 0.16);
            border-radius: 0.7rem;
            box-shadow: 0 20px 44px -24px rgba(9, 20, 44, 0.6);
        }
        .es-paste-slip-ink { color: #111722; }
        .es-paste-slip-dim { color: #5a6473; }
        .es-paste-slip-accent { color: #0b4fc7; }
        .es-paste-slip-rule { border-top: 1px solid rgba(17, 23, 34, 0.1); }
        .es-paste-slip-code {
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.76rem;
            line-height: 1.85;
            word-break: break-word;
        }
        /* Pin the highlight on the slip: it is a white surface in both
           modes, so the light selection colour is the correct one even
           when the rest of the page has gone dark. */
        .es-paste-slip .es-paste-sel,
        .dark .es-paste-slip .es-paste-sel { background-color: #c9dcff; color: #0d1727; }
        .es-paste-tilt { transform: rotate(-1.1deg); }

        /* --- Fixed-dark band ----------------------------------------- */
        .es-paste-band {
            background-color: #101724;
            background-image: radial-gradient(120% 100% at 50% 0%, #16202f 0%, #0d1420 55%, #070a10 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 236, 245, 0.05);
        }
        /* Inks for the fixed-dark surfaces. No `dark:` twin on purpose. */
        .es-paste-bright { color: #e6ecf5; }
        .es-paste-dim { color: #9aa7ba; }
        .es-paste-lit { color: #9cc0ff; }
        /* Shared classes that flip with the colour mode, pinned inside a band. */
        .es-paste-band .es-paste-sel { background-color: #1c4b96; color: #e8f0ff; }
        .es-paste-band .es-paste-card { background-color: #121926; border-color: rgba(230, 236, 245, 0.12); }
        .es-paste-band .es-paste-btn {
            background-color: #9cc0ff;
            color: #0a1020;
            box-shadow: 0 18px 36px -14px rgba(11, 79, 199, 0.55);
        }
        .es-paste-band .es-paste-btn:hover { background-color: #bdd6ff; box-shadow: 0 22px 44px -14px rgba(11, 79, 199, 0.62); }
        .es-paste-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 236, 245, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 236, 245, 0.05) 1px, transparent 1px);
        }
        .es-paste-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-paste-band .es-claim:focus-within {
            border-color: rgba(156, 192, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(156, 192, 255, 0.22);
        }
        /* Ambient light inside a band, authored here rather than with
           .es-aurora, which flips its opacity with the colour mode. */
        .es-paste-glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(90px);
            pointer-events: none;
        }

        /* --- Section mark: the reserved-slot glyph, then the number --- */
        .es-paste-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4a5462;
        }
        .es-paste-mark::before {
            content: "";
            flex: none;
            width: 1.15rem; height: 0.7rem;
            border: 2px dashed #0b4fc7;
            border-radius: 2px;
        }
        .dark .es-paste-mark { color: #98a4b6; }
        .dark .es-paste-mark::before { border-color: #9cc0ff; }
        .es-paste-band .es-paste-mark { color: #9aa7ba; }
        .es-paste-band .es-paste-mark::before { border-color: #9cc0ff; }

        /* --- Chips --------------------------------------------------- */
        .es-paste-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(17, 23, 34, 0.16);
            background: rgba(255, 255, 255, 0.75);
            color: #4a5462;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-paste-chip {
            border-color: rgba(230, 236, 245, 0.16);
            background: rgba(230, 236, 245, 0.05);
            color: #aeb9c9;
        }

        /* --- Plan pills ---------------------------------------------- */
        .es-paste-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.12rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(11, 79, 199, 0.4);
            color: #0b4fc7;
        }
        .dark .es-paste-plan { border-color: rgba(156, 192, 255, 0.42); color: #9cc0ff; }
        .es-paste-plan-pro { border-color: rgba(17, 23, 34, 0.35); color: #111722; }
        .dark .es-paste-plan-pro { border-color: rgba(230, 236, 245, 0.38); color: #e6ecf5; }
        /* The slip is white in both modes, so the plan pill on it must not take the
           dark-mode ink (#9cc0ff measures 1.84 on white). Pinned after the base rules. */
        .es-paste-slip .es-paste-plan,
        .dark .es-paste-slip .es-paste-plan { border-color: rgba(11, 79, 199, 0.4); color: #0b4fc7; }

        /* --- The ledger: what travels with the line, what stays behind.
               Lives only inside the fixed-dark band, so its marks carry
               fixed colours. ------------------------------------- */
        .es-paste-row {
            display: flex;
            gap: 0.7rem;
            align-items: flex-start;
            padding: 0.7rem 0;
            border-top: 1px solid rgba(230, 236, 245, 0.1);
        }
        .es-paste-in {
            flex: none;
            margin-top: 0.4rem;
            width: 0.6rem; height: 0.6rem;
            border-radius: 2px;
            background: #9cc0ff;
        }
        .es-paste-out {
            flex: none;
            margin-top: 0.4rem;
            width: 0.6rem; height: 0.6rem;
            border-radius: 2px;
            border: 1px dashed rgba(230, 236, 245, 0.5);
        }

        /* --- The reference table ------------------------------------- */
        .es-paste-table { width: 100%; border-collapse: collapse; text-align: left; }
        .es-paste-table th,
        .es-paste-table td {
            padding: 0.7rem 0.7rem;
            vertical-align: top;
            font-size: 0.85rem;
            border-top: 1px solid rgba(17, 23, 34, 0.1);
        }
        .dark .es-paste-table th,
        .dark .es-paste-table td { border-color: rgba(230, 236, 245, 0.1); }
        .es-paste-table thead th {
            border-top: 0;
            padding-top: 0;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4a5462;
        }
        .dark .es-paste-table thead th { color: #98a4b6; }

        /* --- Links and buttons -------------------------------------- */
        .es-paste-link { color: #0b4fc7; }
        .es-paste-link:hover { color: #111722; }
        .dark .es-paste-link { color: #9cc0ff; }
        .dark .es-paste-link:hover { color: #e6ecf5; }

        .es-paste-btn {
            background-color: #0b4fc7;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(11, 79, 199, 0.55);
        }
        .es-paste-btn:hover { background-color: #093fa2; box-shadow: 0 22px 44px -14px rgba(11, 79, 199, 0.62); }
        .dark .es-paste-btn { background-color: #9cc0ff; color: #0a1020; }
        .dark .es-paste-btn:hover { background-color: #bdd6ff; }

        /* --- Card hover ---------------------------------------------- */
        .es-paste-hover:hover { border-color: rgba(11, 79, 199, 0.45); }
        .dark .es-paste-hover:hover { border-color: rgba(156, 192, 255, 0.45); }
        .es-paste-hover:hover .es-paste-hover-title,
        .es-paste-hover:hover .es-paste-hover-arrow { color: #0b4fc7; }
        .dark .es-paste-hover:hover .es-paste-hover-title,
        .dark .es-paste-hover:hover .es-paste-hover-arrow { color: #9cc0ff; }

        /* --- Shared-system recolours (brand blue by default) --------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(11, 79, 199, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(156, 192, 255, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(11, 79, 199, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(156, 192, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0b4fc7; }
        .dark .es-dot.is-active .es-dot-pip { background: #9cc0ff; }
        /* Dot-nav tooltip. dark:bg-[#121926] is not in the built stylesheet, so
           the tooltip kept its white ground under gray-300 ink in dark mode and
           measured 1.47:1. Real rules in both modes instead. */
        .es-paste-tip { background-color: #ffffff; border-color: rgba(17, 23, 34, 0.12); color: #374151; }
        .dark .es-paste-tip { background-color: #121926; border-color: rgba(230, 236, 245, 0.14); color: #d1d5db; }

        /* --- Focus rings. No border-radius here: setting it would change
               the element's own shape on focus. ------------------- */
        #es-paste-page a:focus-visible,
        #es-paste-page summary:focus-visible,
        #es-paste-page button:focus-visible {
            outline: 2px solid #0b4fc7;
            outline-offset: 3px;
        }
        .dark #es-paste-page a:focus-visible,
        .dark #es-paste-page summary:focus-visible,
        .dark #es-paste-page button:focus-visible { outline-color: #9cc0ff; }
        .es-paste-band a:focus-visible,
        .es-paste-band summary:focus-visible,
        .es-paste-band button:focus-visible { outline-color: #9cc0ff !important; }

        @media (prefers-reduced-motion: reduce) {
            .es-paste-ants { animation: none !important; }
            .es-paste-tilt { transform: none; }
        }
    </style>

    @php
        // The demo schedule this page frames live, further down.
        $demoUrl = route('role.view_guest', ['subdomain' => 'simpsons']);

        // Everything the tag and its URL will take. Each row is verified against
        // code: ?embed=true selects role/show-guest-embed and is the only value
        // SecurityHeaders will allow another site to frame; ?lang= is read by the
        // SetUserLanguage middleware; ?schedule= resolves a sub-schedule slug in
        // RoleController::viewGuest; ?dark=true is read by the theme script in
        // layouts/app.blade.php; ?month= and ?year= set the month the frame opens on;
        // ?layout= is resolved by requested_event_layout() and wins over the stored
        // roles.event_layout in Role::activeEventLayout().
        $knobs = [
            ['?embed=true', 'required', 'Renders the calendar on its own, with no header, footer or banner around it. It is also the only URL Event Schedule permits another site to frame.'],
            ['width', '100%', 'The frame takes the width of whatever column you drop it into.'],
            ['height', '800', 'You choose the height. Nothing measures the calendar and resizes the frame for you.'],
            ['loading', 'lazy', 'Plain HTML, not a product feature: the browser waits until the frame scrolls into view before fetching it.'],
            ['?layout=', 'list', 'Opens the frame as a list or as a month calendar, whatever the schedule itself is set to. Two frames on the same page can each take their own. The calendar wants about 768px of frame width; narrower than that it shows a day-by-day agenda instead.'],
            ['?lang=', 'de', 'Renders the calendar in one of 12 languages. Arabic and Hebrew lay the frame out right to left.'],
            ['?schedule=', 'jazz-nights', 'Shows a single sub-schedule instead of everything on the calendar.'],
            ['?dark=true', 'optional', 'Forces dark mode. Left off, the frame follows the visitor\'s own system setting.'],
            ['?month= &year=', '3 / 2027', 'Opens the frame on a specific month instead of the current one.'],
        ];

        // What the frame brings with it, and what it deliberately leaves behind.
        // Travels: roles.background / background_colors / font_family and Pro
        // custom_css are all applied by layouts/app-guest.blade.php regardless of
        // embed mode; the layout comes from Role::activeEventLayout() in
        // role/show-guest-embed.blade.php, which is roles.event_layout unless
        // ?layout= overrides it for that frame.
        $travels = [
            ['Every event you publish', 'The frame loads live, so a date you move this afternoon is already right on your site. You never re-paste the tag.'],
            ['Your schedule\'s own look', 'Background, colours and font come from your Appearance settings. Custom CSS, on Pro, applies inside the frame too.'],
            ['Your layout choice', 'Month calendar or list view, whichever your schedule is set to, or pinned per frame with ?layout=.'],
            ['Sub-schedules and their colours', 'Colour-coded on the calendar, and filterable down to one strand with a URL parameter.'],
            ['RSVP and ticket links', 'A click inside the frame opens in a new tab, so your own page is never replaced by an event page.'],
        ];

        $staysBehind = [
            ['Your schedule\'s header and footer', 'The frame is the calendar and nothing else. No banner, no navigation, no page chrome.'],
            ['The "Powered by Event Schedule" footer', 'Never inside the frame. Free schedules get it as a small credit line in the copied code, under the frame, and white-label on Pro removes it.'],
            ['Ads', 'A free schedule\'s own public pages can carry them. An embed never does, by design.'],
            ['The language switcher', 'Hidden inside a frame, which is why the language is set in the URL instead.'],
            ['Search engines', 'The embed URL is served noindex, nofollow, so the page that ranks is yours, not the frame inside it.'],
            ['Your view count', 'Loads inside a frame are deliberately not recorded as schedule views, so analytics stay a measure of your own schedule page.'],
        ];

        $faqs = [
            [
                'q' => 'How do I embed my schedule on my website?',
                'a' => 'Open your schedule in the admin portal and choose Embed Schedule. The panel shows the embed URL, the iframe tag and a live preview of the calendar before you take it anywhere. Copy the tag and paste it into your page\'s HTML wherever the calendar belongs. Any CMS block that accepts raw HTML or an embed will take it.',
            ],
            [
                'q' => 'Can I customize the embedded calendar\'s appearance?',
                'a' => 'The frame renders your schedule, so your own appearance settings come with it: background, colours, font, and whether events show as a month calendar or a list. On top of that the URL takes ?layout=calendar or ?layout=list to pin the layout for that frame, ?dark=true to force dark mode, ?schedule= to show a single sub-schedule and ?lang= to pick one of 12 languages. Custom CSS, on the Pro plan, applies inside the frame as well.',
            ],
            [
                'q' => 'Does the embed slow down my website?',
                'a' => 'It is one iframe, so the browser loads it alongside your page rather than waiting for it, and it brings no JavaScript, no stylesheet and no dependency into your own page. Add loading="lazy" to the tag and the browser will not fetch the calendar at all until it scrolls into view.',
            ],
            [
                'q' => 'Will the embedded calendar compete with my own page in search?',
                'a' => 'No. The embed URL is served noindex, nofollow, so search engines are pointed at the page you own rather than at the frame inside it. Framing is also only permitted on the ?embed=true URL: the ordinary schedule page refuses to be framed at all.',
            ],
            [
                'q' => 'Does it work on mobile?',
                'a' => 'Yes. A width of 100% means the frame takes the width of whatever column you drop it into, and the calendar inside it is responsive. The height is the one thing you choose: nothing measures the calendar and resizes the frame for you, so either give it room or let it scroll.',
            ],
            [
                'q' => 'Is embedding the calendar free?',
                'a' => 'Yes, on every plan and on selfhosted installs, with no limit on how many sites you paste it into. The separate widget embed, which puts the form itself on your page, splits in two: the RSVP form is free as well, and the ticket purchase form is a Pro feature.',
            ],
            [
                'q' => 'Do embed views show up in my analytics?',
                'a' => 'No. Loads inside a frame are deliberately not recorded as schedule views. Clicks through from the frame open your event page in a new tab, and those are ordinary views, so they are counted.',
            ],
        ];

        $dotSections = [
            ['top', 'The paste'],
            ['line', 'The line'],
            ['tag', 'The rectangle'],
            ['travels', 'What travels'],
            ['live', 'Live on this page'],
            ['steps', 'Three steps'],
            ['plans', 'What it costs'],
            ['faq', 'Questions'],
            ['claim', 'Paste it'],
        ];
    @endphp

    <div id="es-paste-page" class="es-paste-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the slip landing in someone else's page             -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-paste-glow" style="width: 620px; height: 620px; left: -150px; top: -120px; background: radial-gradient(circle at 35% 35%, rgba(11, 79, 199, 0.22), rgba(11, 79, 199, 0) 65%);"></div>
            <div class="es-paste-glow" style="width: 520px; height: 520px; right: -130px; top: 8%; background: radial-gradient(circle at 65% 40%, rgba(76, 130, 220, 0.16), rgba(76, 130, 220, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-paste-accent" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span class="es-paste-muted text-sm font-medium tracking-wide">Embed calendar &middot; free on every plan</span>
                    </div>

                    <h1 class="es-balance es-paste-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Paste it once.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">It is never <span class="es-paste-accent">wrong</span> again.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-paste-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Your calendar goes into the website you already have as <span class="es-paste-sel es-paste-mono">one iframe tag</span>. No script, no plugin, no build step. You keep adding events here, and the page on your own site is already up to date.
                    </p>

                    <div class="es-fade-up es-d-3 mb-9 flex flex-wrap gap-2">
                        <span class="es-paste-chip">No script tag</span>
                        <span class="es-paste-chip">No dependencies</span>
                        <span class="es-paste-chip">No re-pasting</span>
                    </div>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-paste-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Start for free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.sharing') }}" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the Sharing guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Their page, with a rectangle reserved in it, and the slip
                     that just landed on top. Decorative: the real snippet is
                     set as live text in the next section. -->
                <div class="es-fade-up es-d-4 relative pb-10" data-reveal>
                    <div class="es-paste-host overflow-hidden" aria-hidden="true">
                        <div class="es-paste-host-bar">
                            <span class="es-paste-host-mark"></span>
                            <span class="es-paste-bar" style="width: 3rem;"></span>
                            <span class="es-paste-bar" style="width: 2.2rem;"></span>
                            <span class="es-paste-bar" style="width: 2.6rem;"></span>
                            <span class="es-paste-mono es-paste-muted ms-auto text-[0.6rem] tracking-tight">your-website.com/events</span>
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="es-paste-bar es-paste-bar-head mb-4" style="width: 44%;"></div>
                            <div class="es-paste-bar mb-2" style="width: 92%;"></div>
                            <div class="es-paste-bar mb-6" style="width: 74%;"></div>

                            <div class="es-paste-slot es-paste-ants flex h-40 items-end justify-between p-3 sm:h-48">
                                <span class="es-paste-mono es-paste-accent text-[0.6rem] font-bold uppercase tracking-widest">width 100%</span>
                                <span class="es-paste-mono es-paste-accent text-[0.6rem] font-bold uppercase tracking-widest">height 800</span>
                            </div>

                            <div class="es-paste-bar mb-2 mt-6" style="width: 88%;"></div>
                            <div class="es-paste-bar" style="width: 61%;"></div>
                        </div>
                    </div>

                    <div class="es-paste-slip es-paste-tilt absolute bottom-0 left-5 right-5 p-4" aria-hidden="true">
                        <div class="es-paste-slip-dim es-paste-mono mb-2 text-[0.6rem] font-bold uppercase tracking-widest">On your clipboard</div>
                        <p class="es-paste-slip-code es-paste-slip-ink">
                            <span class="es-paste-slip-accent">&lt;iframe</span> <span class="es-paste-slip-dim">src=</span><span class="es-paste-sel">"...?embed=true"</span> <span class="es-paste-slip-dim">width=</span>"100%" <span class="es-paste-slip-dim">height=</span>"800"<span class="es-paste-slip-accent">&gt;&lt;/iframe&gt;</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The line, taken apart                                     -->
    <!-- ============================================================ -->
    <section id="line" class="scroll-mt-24 es-paste-rule py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-paste-mark mb-6" data-reveal><span>01 &middot; the clipboard</span></div>
                <h2 class="es-balance es-paste-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    This is <span class="es-paste-sel">the whole integration.</span>
                </h2>
                <p class="es-paste-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Not a sample of it. This is the entire thing that goes into your page, exactly as the Embed Schedule panel hands it to you.
                </p>
            </div>

            <div class="grid items-start gap-8 lg:grid-cols-[1.1fr_1fr]">
                <!-- The slip: white in both colour modes, because it is the one
                     physical object in this transaction. -->
                <div class="es-paste-slip p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <span class="es-paste-slip-dim es-paste-mono es-paste-xs font-bold uppercase tracking-widest">Embed Schedule &middot; copied</span>
                        <span class="es-paste-plan">Free</span>
                    </div>

                    <p class="es-paste-slip-code es-paste-slip-ink">
                        <span class="es-paste-slip-accent">&lt;iframe</span> <span class="es-paste-slip-dim">src=</span><span class="es-paste-sel">"https://your-schedule.eventschedule.com?embed=true"</span> <span class="es-paste-slip-dim">width=</span>"100%" <span class="es-paste-slip-dim">height=</span>"800" <span class="es-paste-slip-dim">frameborder=</span>"0" <span class="es-paste-slip-dim">style=</span>"border: none;"<span class="es-paste-slip-accent">&gt;&lt;/iframe&gt;</span>
                    </p>

                    <p class="es-paste-slip-code es-paste-slip-dim mt-3">
                        <span class="es-paste-slip-accent">&lt;p</span> style="font-size: 12px; text-align: right; opacity: 0.6;"<span class="es-paste-slip-accent">&gt;&lt;a</span> href="https://eventschedule.com"<span class="es-paste-slip-accent">&gt;</span>Powered by Event Schedule<span class="es-paste-slip-accent">&lt;/a&gt;&lt;/p&gt;</span>
                    </p>

                    <p class="es-paste-slip-rule es-paste-slip-dim mt-5 pt-4 text-sm leading-relaxed">
                        Two lines, and the second one is optional furniture: free schedules carry a small credit line under the frame, and white-label on the Pro plan takes it out of the copied code. The frame itself is one tag with your own subdomain in it.
                    </p>
                </div>

                <!-- What each part of the line is for. -->
                <div class="space-y-4" data-reveal-group="90">
                    @foreach ([
                        ['?embed=true', 'The part that matters. It renders the calendar on its own, and it is the only URL Event Schedule allows another site to frame. Point a plain schedule URL at an iframe and the browser refuses it.'],
                        ['width="100%"', 'The frame is as wide as the column you put it in, so it fits a narrow sidebar and a full-bleed section without being told twice.'],
                        ['height="800"', 'Your call, and the one honest limitation on this page: nothing measures the calendar and resizes the frame for you. Give it room, or let it scroll.'],
                        ['frameborder="0"', 'No browser border drawn around it, so the calendar reads as part of your page rather than a window cut into it.'],
                    ] as [$part, $why])
                        <div class="es-paste-card p-5" data-reveal>
                            <code class="es-paste-mono es-paste-accent text-sm font-bold">{{ $part }}</code>
                            <p class="es-paste-muted mt-2 text-sm leading-relaxed">{{ $why }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The rectangle: everything you can change is in the tag    -->
    <!-- ============================================================ -->
    <section id="tag" class="scroll-mt-24 es-paste-rule py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-paste-mark mb-6" data-reveal><span>02 &middot; the rectangle</span></div>
                <h2 class="es-balance es-paste-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    You are giving it <span class="es-paste-sel">a rectangle,</span> not a script.
                </h2>
                <p class="es-paste-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Which means everything you can change lives in the tag and its URL. There is no settings screen on your side of the fence, and nothing to keep in sync.
                </p>
            </div>

            <div class="es-paste-card overflow-x-auto p-5 sm:p-7" data-reveal="panel">
                <table class="es-paste-table">
                    <caption class="sr-only">Attributes of the iframe tag and query parameters of the embed URL, with what each one does</caption>
                    <thead>
                        <tr>
                            <th scope="col">Token</th>
                            <th scope="col" class="hidden sm:table-cell">Example</th>
                            <th scope="col">What it does</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($knobs as [$token, $example, $effect])
                            <tr>
                                <th scope="row" class="es-paste-mono es-paste-accent whitespace-nowrap font-bold">{{ $token }}</th>
                                <td class="es-paste-mono es-paste-muted hidden whitespace-nowrap sm:table-cell">{{ $example }}</td>
                                <td class="es-paste-muted">{{ $effect }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-paste-muted mt-5 text-xs leading-relaxed">
                    Query parameters go on the <code class="es-paste-mono">src</code> URL, after <code class="es-paste-mono">?embed=true</code>. The in-page language switcher is hidden inside a frame, so if you want a language other than the schedule's own, that is what <code class="es-paste-mono">?lang=</code> is for.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What travels with the line, what stays behind (dark band) -->
    <!-- ============================================================ -->
    <section id="travels" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-paste-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-paste-glow" style="width: 560px; height: 560px; left: 8%; top: -140px; background: radial-gradient(circle at 40% 40%, rgba(28, 75, 150, 0.5), rgba(28, 75, 150, 0) 65%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-paste-mark mb-6" data-reveal><span>03 &middot; the transplant</span></div>
                    <h2 class="es-balance es-paste-bright text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        What travels with the line, and <span class="es-paste-sel">what stays behind.</span>
                    </h2>
                    <p class="es-paste-dim mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        An embed is not a smaller copy of your schedule page. It is the calendar, on its own, with the page around it deliberately taken off.
                    </p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2" data-reveal-group="90">
                    <div class="es-paste-card p-6 sm:p-8" data-reveal="panel">
                        <div class="mb-1 flex items-center gap-3">
                            <span class="es-paste-in"></span>
                            <h3 class="es-paste-lit es-paste-mono text-xs font-bold uppercase es-paste-track">Travels with it</h3>
                        </div>
                        <div class="mt-4">
                            @foreach ($travels as [$inTitle, $inBody])
                                <div class="es-paste-row">
                                    <span class="es-paste-in" aria-hidden="true"></span>
                                    <div>
                                        <p class="es-paste-bright text-sm font-bold">{{ $inTitle }}</p>
                                        <p class="es-paste-dim mt-1 text-sm leading-relaxed">{{ $inBody }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="es-paste-card p-6 sm:p-8" data-reveal="panel">
                        <div class="mb-1 flex items-center gap-3">
                            <span class="es-paste-out"></span>
                            <h3 class="es-paste-dim es-paste-mono text-xs font-bold uppercase es-paste-track">Stays behind</h3>
                        </div>
                        <div class="mt-4">
                            @foreach ($staysBehind as [$outTitle, $outBody])
                                <div class="es-paste-row">
                                    <span class="es-paste-out" aria-hidden="true"></span>
                                    <div>
                                        <p class="es-paste-bright text-sm font-bold">{{ $outTitle }}</p>
                                        <p class="es-paste-dim mt-1 text-sm leading-relaxed">{{ $outBody }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Live on this page: the real thing, in a real frame        -->
    <!-- ============================================================ -->
    <section id="live" class="scroll-mt-24 es-paste-rule py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-paste-mark mb-6" data-reveal><span>04 &middot; proof</span></div>
                <h2 class="es-balance es-paste-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    It is <span class="es-paste-sel">on this page</span> right now.
                </h2>
                <p class="es-paste-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The rectangle below is not a screenshot. It is a demo schedule in an ordinary iframe, pasted into this marketing page the same way it would be pasted into yours.
                </p>
            </div>

            <div class="es-paste-card p-3 sm:p-4" data-reveal="panel">
                <div class="es-paste-slot es-paste-ants overflow-hidden p-1.5">
                    <iframe src="{{ $demoUrl }}?embed=true"
                            width="100%" height="800" loading="lazy"
                            title="Live demo of an embedded Event Schedule calendar"
                            style="border: none; display: block; border-radius: 6px; background: #ffffff;"></iframe>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 px-1">
                    <span class="es-paste-mono es-paste-muted text-[0.65rem] uppercase tracking-widest">iframe &middot; width 100% &middot; height 800</span>
                    <a href="{{ $demoUrl }}" target="_blank" rel="noopener" class="es-paste-link inline-flex items-center gap-1 text-sm font-medium hover:underline">
                        View the full schedule
                        <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </div>

            <p class="es-paste-muted mx-auto mt-6 max-w-2xl text-center text-sm leading-relaxed" data-reveal>
                Try a date. The event opens in a new tab rather than replacing what you are reading, which is the behaviour the calendar switches to as soon as it notices it is inside a frame.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Three steps                                              -->
    <!-- ============================================================ -->
    <section id="steps" class="scroll-mt-24 es-paste-rule py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-paste-mark mb-6" data-reveal><span>05 &middot; the ritual</span></div>
                <h2 class="es-balance es-paste-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Copy. Paste. <span class="es-paste-sel">Leave it alone.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Copy the tag', 'Open your schedule in the admin portal and choose Embed Schedule. The panel shows the embed URL, the tag, and a live preview of the calendar before you take it anywhere.'],
                    ['02', 'Paste it into your page', 'Drop the tag into your HTML wherever the calendar belongs. Any CMS block that accepts raw HTML or an embed will take it, because there is nothing to install.'],
                    ['03', 'Leave it alone', 'The tag never changes again. Add an event, move a date, cancel a night: the rectangle on your own site is already correct, because it is reading the live schedule.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-paste-card flex flex-col p-7" data-reveal="panel">
                        <div class="es-paste-accent es-paste-mono mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-paste-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-paste-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <x-link href="{{ route('marketing.docs.sharing') }}">Read the Sharing guide</x-link>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. What it costs, and which embed is which                   -->
    <!-- ============================================================ -->
    <section id="plans" class="scroll-mt-24 es-paste-rule py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-paste-mark mb-6" data-reveal><span>06 &middot; the price</span></div>
                <h2 class="es-balance es-paste-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The calendar embed is <span class="es-paste-sel">free.</span>
                </h2>
                <p class="es-paste-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    There are two embeds in Event Schedule and they are easy to mix up, so here they are side by side.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-paste-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="es-paste-plan">Free</span>
                        <span class="es-paste-muted es-paste-mono text-[0.65rem] uppercase tracking-widest">all plans</span>
                    </div>
                    <h3 class="es-paste-ink mb-2 text-lg font-bold">The calendar embed</h3>
                    <p class="es-paste-muted text-sm leading-relaxed">This page. Free on every plan, with no limit on how many sites you paste it into, and free on selfhosted installs too.</p>
                </div>

                <div class="es-paste-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="es-paste-plan es-paste-plan-pro">Pro</span>
                        <span class="es-paste-muted es-paste-mono text-[0.65rem] uppercase tracking-widest">the other one</span>
                    </div>
                    <h3 class="es-paste-ink mb-2 text-lg font-bold">The ticket widget</h3>
                    <p class="es-paste-muted mb-4 text-sm leading-relaxed">A different tag for a different job: it puts the form itself on your page, rather than a calendar that links to it. The RSVP version is free on every plan; putting the ticket purchase form on your page is the Pro half.</p>
                    <div class="mt-auto">
                        <x-link href="{{ marketing_url('/features/embed-tickets') }}">Embed tickets</x-link>
                    </div>
                </div>

                <div class="es-paste-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="es-paste-plan">Free</span>
                        <span class="es-paste-muted es-paste-mono text-[0.65rem] uppercase tracking-widest">selfhosted</span>
                    </div>
                    <h3 class="es-paste-ink mb-2 text-lg font-bold">On your own server</h3>
                    <p class="es-paste-muted text-sm leading-relaxed">The same tag, with your install's own address in it instead of a subdomain. The embed is not gated on a plan, a licence key or a site count.</p>
                </div>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-paste-card p-6 md:col-span-1" data-reveal>
                    <h3 class="es-paste-ink mb-1 text-base font-bold">Popular with</h3>
                    <p class="es-paste-muted text-sm">The people who already have a website and just want the dates on it.</p>
                </div>
                @foreach ([['/for-venues', 'Venues', 'One page for the whole room\'s programme, pasted into the site you already run.'], ['/for-libraries', 'Libraries', 'Story times, clubs and talks on the branch page, without a web team in the loop.']] as [$popHref, $popName, $popBlurb])
                    <a href="{{ marketing_url($popHref) }}" class="es-paste-card es-paste-hover group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-paste-hover-title es-paste-ink mb-2 text-base font-bold transition-colors">For {{ $popName }}</span>
                        <span class="es-paste-muted mb-4 text-sm leading-relaxed">{{ $popBlurb }}</span>
                        <span class="es-paste-hover-arrow es-paste-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-widest transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <a href="{{ marketing_url('/for-community-centers') }}" class="es-paste-card es-paste-hover group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                    <span class="es-paste-hover-title es-paste-ink mb-2 text-base font-bold transition-colors">For Community Centers</span>
                    <span class="es-paste-muted mb-4 text-sm leading-relaxed">Every class, group and hire on the noticeboard page, kept current by whoever runs the calendar.</span>
                    <span class="es-paste-hover-arrow es-paste-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-widest transition-colors">
                        Read more
                        <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>
                <div class="es-paste-card flex flex-col p-6 md:col-span-2" data-reveal>
                    <h3 class="es-paste-ink mb-1 text-base font-bold">Everything else on the free plan</h3>
                    <p class="es-paste-muted text-sm leading-relaxed">The calendar itself, two-way Google, Outlook and CalDAV sync, RSVP with a capacity per date, sub-schedules, built-in analytics, newsletters to 10 recipients a month and selling up to 25 tickets a month are all free. Pro takes the ceiling off ticket sales and adds the check-in dashboard, the ticket purchase widget and custom fields. Event Schedule takes zero platform fees on ticket sales, on every plan.</p>
                    <div class="mt-auto pt-4">
                        <x-link href="{{ marketing_url('/pricing') }}">See pricing</x-link>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                             -->
    <!-- ============================================================ -->
    <section class="es-paste-rule py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-paste-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Calendar Sync"
                        description="Two-way sync with Google Calendar"
                        :url="marketing_url('/features/calendar-sync')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Sub-schedules"
                        description="Organize events into categories, and filter the embed down to one"
                        :url="marketing_url('/features/sub-schedules')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Tickets"
                        description="Put the purchase or RSVP form itself on any website"
                        :url="marketing_url('/features/embed-tickets')"
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
                        name="Custom Fields"
                        description="Collect additional info from attendees with custom form fields"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-paste-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 es-paste-rule py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-paste-mark mb-6" data-reveal><span>07 &middot; questions</span></div>
                <h2 class="es-balance es-paste-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-paste-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they put a frame on a page they care about.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-paste-card es-paste-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-paste-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-paste-accent es-paste-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-paste-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-paste-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-paste-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale                                                  -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-paste-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-paste-glow" style="width: 620px; height: 620px; left: 50%; margin-left: -310px; top: -220px; background: radial-gradient(circle at 50% 50%, rgba(28, 75, 150, 0.55), rgba(28, 75, 150, 0) 62%);"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-paste-lit es-paste-mono mb-4 text-xs font-bold uppercase tracking-[0.3em]">Free on every plan</p>
                    <h2 class="es-balance es-paste-bright mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight md:text-5xl">
                        Take the name, take <span class="es-paste-sel">the line.</span>
                    </h2>
                    <p class="es-paste-dim mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Claim a schedule, add your first event, and copy one tag into the site you already have. Nothing to install, and nothing to keep in sync afterwards.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-paste-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-paste-dim mt-6 text-sm">No credit card required</p>
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
                        <span class="es-paste-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
