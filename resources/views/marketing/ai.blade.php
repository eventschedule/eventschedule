<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.ai_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.ai_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">AI Features</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule AI Features",
        "description": "Paste the text or drop the image and AI fills the event form: name, date, duration, venue, address, performers, price, currency and registration link. Agenda scanning, description writing, flyer and style generation, WhatsApp event creation and whole-schedule translation.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "AI-Powered Event Management"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule AI Features",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "AI Event Management Software",
        "operatingSystem": "Web",
        "description": "AI event parsing from pasted text or a dropped image, on every plan. Agenda scanning, description writing, flyer and style generation, graphic email text and WhatsApp event creation on Enterprise. Whole-schedule translation into {{ count(config('app.supported_languages')) }} languages, free.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "AI event parsing and translation are included free"
        },
        "featureList": [
            "Event parsing from pasted text",
            "Event parsing from a dropped, pasted or uploaded image (JPEG, PNG, GIF, WebP)",
            "One document can yield several events",
            "Extraction into the real event fields, including price, currency and country code",
            "Category matched against your own category list",
            "Venue resolution against venues you already have, before a new one is created",
            "Performer matching against talent already on the schedule",
            "Duplicate detection against events already on the schedule",
            "Agenda and setlist scanning into event parts (Enterprise)",
            "Custom agenda prompts per event or as a schedule default (Enterprise)",
            "Schedule and event description writing (Enterprise)",
            "Flyer image generation (Enterprise)",
            "Schedule style generation: profile, header and background images, accent colour and font (Enterprise)",
            "AI pass over graphic email text (Enterprise)",
            "Event creation over WhatsApp (Enterprise)",
            "Whole-schedule translation into {{ count(config('app.supported_languages')) }} languages",
            "REST API, OpenAPI 3.0 spec, llms.txt and agents.json for AI agents",
            "Selfhosted installs use their own API keys with no daily caps"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "AI event import, parse event from flyer, event data extraction, AI agenda scanning, AI event flyer, schedule translation",
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
           For-ai "The Spark" styles.

           THE CONCEPT IS A SPARK GAP, not a wand and not a swarm of
           twinkling stars (the first-wave page used both, and sparkles
           are the single most generic AI ornament there is). A spark gap
           has two terminals and a measured distance between them. On one
           side is the scrap somebody actually sent you: a WhatsApp
           message, a photo of a poster taped to a wall. On the other is
           the record the product needs: starts_at, duration, venue,
           ticket_price, event_country_code. The gap is real, it is
           narrow, and exactly one thing crosses it. That framing is also
           the product argument, because it forces the page to say what
           does NOT cross: the parser never reads a web page for event
           details. A registration link is followed for its preview image
           only (UrlUtils::getUrlMetadata pulls og:image and the final
           redirect target, nothing else), so you paste the text, not the
           link.

           DEVICES
             1. .es-spark-arcv / .es-spark-arc - the gap itself: two pads
                joined by a filament with one travelling node. Vertical in
                panels (which is how the real import screen is laid out:
                paste box above, parsed results below), horizontal in the
                section mark, so every section head is a small gap.
             2. .es-spark-scrap vs .es-spark-rec - recessed dashed scrap
                against a hard-edged record with a lit leading edge. The
                two terminals, repeated.
             3. .es-spark-table - the field manifest. A real <table>,
                because the thing being described IS a record. Field names
                are the literal keys GeminiUtils::parseEvent asks for.
             4. .es-spark-rung - the venue resolution ladder, indented one
                step per rung, in the order the code actually tries them
                (GeminiUtils::parseEvent rungs 1-3, then the save-time
                safety-net lookup in EventRepo::saveEvent, then creation).
                Indentation carries the "only if the rung above missed".

           COLOUR: the page keeps its inherited blue family but drops the
           three-stop brand chrome gradient (blue -> sky -> cyan), which
           is shared furniture and whose cyan stop measures 2.43 on white
           and failed AA four times on the first-wave page. One solid
           blue instead: #1d4ed8 light (6.09 on the #f2f4f8 ground),
           #93c5fd dark (10.91 on #080b12). Distinctiveness comes from
           the graphite worksheet ground, the monospace field language
           and the gap structure, not from a new hue.

           NEVER text-gray-500 here: 4.83 on pure white but only ~4.4 on
           this page's tinted ground. Use .es-spark-muted (#474e5c, 7.59)
           and .es-spark-bmuted (#9aa5bd, 7.15+) inside the dark bands.

           .es-spark-band is a FIXED-DARK object: it renders identically
           with .dark on and off, so the shared classes that flip inside
           it (.grid-overlay, .animate-shimmer, .es-claim:focus-within,
           .es-aurora) are pinned below.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-spark-page { background-color: #f2f4f8; color: #101623; }
        .dark .es-spark-page { background-color: #080b12; color: #e9edf6; }
        .es-spark-ink { color: #101623; }
        .dark .es-spark-ink { color: #e9edf6; }
        .es-spark-muted { color: #474e5c; }
        .dark .es-spark-muted { color: #9aa5bd; }
        .es-spark-accent { color: #1d4ed8; }
        .dark .es-spark-accent { color: #93c5fd; }
        /* Always-lit ink for the fixed-dark bands, in BOTH colour modes. */
        .es-spark-lit { color: #93c5fd; }
        .es-spark-bmuted { color: #9aa5bd; }
        .es-spark-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Rules and separators. These live here, not in a Tailwind
               arbitrary value: `border-[rgba(16,22,35,0.08)]` is not in the
               built stylesheet, so it would draw nothing at all. --- */
        .es-spark-edge {
            border-block: 1px solid rgba(16, 22, 35, 0.08);
        }
        .dark .es-spark-edge { border-block-color: rgba(233, 237, 246, 0.08); }
        .es-spark-sep { border-top: 1px solid rgba(16, 22, 35, 0.1); }
        .dark .es-spark-sep { border-top-color: rgba(233, 237, 246, 0.12); }
        .es-spark-ladder > li + li { border-top: 1px solid rgba(16, 22, 35, 0.08); }
        .dark .es-spark-ladder > li + li { border-top-color: rgba(233, 237, 246, 0.1); }

        /* --- Cards --- */
        .es-spark-card {
            border: 1px solid rgba(16, 22, 35, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-spark-card {
            border-color: rgba(233, 237, 246, 0.12);
            background: #111725;
        }
        .es-spark-band .es-spark-card {
            border-color: rgba(233, 237, 246, 0.14);
            background: rgba(233, 237, 246, 0.05);
        }

        /* --- Terminal one: the scrap. Recessed, dashed, unresolved. --- */
        .es-spark-scrap {
            border: 1px dashed rgba(16, 22, 35, 0.3);
            border-radius: 0.85rem;
            background: rgba(16, 22, 35, 0.04);
            padding: 1rem 1.1rem;
        }
        .dark .es-spark-scrap {
            border-color: rgba(233, 237, 246, 0.26);
            background: rgba(233, 237, 246, 0.05);
        }

        /* --- Terminal two: the record. Hard edge, lit leading rule. --- */
        .es-spark-rec {
            position: relative;
            border: 1px solid rgba(29, 78, 216, 0.35);
            border-radius: 0.85rem;
            background: rgba(29, 78, 216, 0.05);
            padding: 1rem 1.1rem;
            overflow: hidden;
        }
        .dark .es-spark-rec {
            border-color: rgba(147, 197, 253, 0.35);
            background: rgba(147, 197, 253, 0.07);
        }
        .es-spark-rec::before {
            content: "";
            position: absolute;
            inset-block: 0;
            inset-inline-start: 0;
            width: 3px;
            background: #1d4ed8;
        }
        .dark .es-spark-rec::before { background: #93c5fd; }

        /* A single parsed field: monospace key, plain value. */
        .es-spark-row {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.15rem 0.6rem;
            padding-block: 0.28rem;
            border-top: 1px solid rgba(16, 22, 35, 0.08);
        }
        .dark .es-spark-row { border-top-color: rgba(233, 237, 246, 0.1); }
        .es-spark-row:first-child { border-top: 0; }
        .es-spark-key {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            letter-spacing: 0.01em;
            color: #1d4ed8;
        }
        .dark .es-spark-key { color: #93c5fd; }
        .es-spark-val { font-size: 0.82rem; font-weight: 600; }

        /* --- The gap. Vertical in panels, horizontal in the section mark. --- */
        .es-spark-arcv {
            position: relative;
            width: 2px;
            height: 2.6rem;
            margin-inline: auto;
            border-radius: 1px;
            background: linear-gradient(180deg, rgba(29, 78, 216, 0.12), rgba(29, 78, 216, 0.5), rgba(29, 78, 216, 0.12));
        }
        .dark .es-spark-arcv {
            background: linear-gradient(180deg, rgba(147, 197, 253, 0.14), rgba(147, 197, 253, 0.55), rgba(147, 197, 253, 0.14));
        }
        .es-spark-arcv::after {
            content: "";
            position: absolute;
            inset-inline-start: -3px;
            top: 0;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #1d4ed8;
            box-shadow: 0 0 10px 2px rgba(29, 78, 216, 0.5);
            animation: es-spark-jumpv 3.4s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .dark .es-spark-arcv::after {
            background: #bfdbfe;
            box-shadow: 0 0 12px 3px rgba(147, 197, 253, 0.55);
        }
        @keyframes es-spark-jumpv {
            0% { top: 0; opacity: 0; }
            14% { opacity: 1; }
            64% { top: calc(100% - 8px); opacity: 1; }
            82%, 100% { top: calc(100% - 8px); opacity: 0; }
        }

        .es-spark-arc {
            position: relative;
            flex: none;
            width: 2.4rem;
            height: 2px;
            border-radius: 1px;
            background: linear-gradient(90deg, rgba(29, 78, 216, 0.14), rgba(29, 78, 216, 0.55), rgba(29, 78, 216, 0.14));
        }
        .dark .es-spark-arc,
        .es-spark-band .es-spark-arc {
            background: linear-gradient(90deg, rgba(147, 197, 253, 0.16), rgba(147, 197, 253, 0.6), rgba(147, 197, 253, 0.16));
        }
        .es-spark-arc::after {
            content: "";
            position: absolute;
            top: -3px;
            inset-inline-start: 0;
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #1d4ed8;
            box-shadow: 0 0 10px 2px rgba(29, 78, 216, 0.45);
            animation: es-spark-jumph 3.4s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .dark .es-spark-arc::after,
        .es-spark-band .es-spark-arc::after {
            background: #bfdbfe;
            box-shadow: 0 0 12px 3px rgba(147, 197, 253, 0.5);
        }
        @keyframes es-spark-jumph {
            0% { inset-inline-start: 0; opacity: 0; }
            14% { opacity: 1; }
            64% { inset-inline-start: calc(100% - 8px); opacity: 1; }
            82%, 100% { inset-inline-start: calc(100% - 8px); opacity: 0; }
        }

        /* --- Section mark: numeral, then a gap being crossed. --- */
        .es-spark-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
        }
        .es-spark-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            color: #1d4ed8;
        }
        .dark .es-spark-num { color: #93c5fd; }
        .es-spark-band .es-spark-num { color: #93c5fd; }

        /* --- Eyebrow --- */
        .es-spark-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #474e5c;
        }
        .dark .es-spark-tag { color: #9aa5bd; }
        .es-spark-band .es-spark-tag { color: #93c5fd; }

        /* --- Plan pills --- */
        .es-spark-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(29, 78, 216, 0.42);
            color: #1d4ed8;
        }
        .dark .es-spark-plan { border-color: rgba(147, 197, 253, 0.45); color: #93c5fd; }
        .es-spark-band .es-spark-plan { border-color: rgba(147, 197, 253, 0.45); color: #93c5fd; }
        .es-spark-plan-alt {
            border-color: rgba(16, 22, 35, 0.32);
            color: #101623;
        }
        .dark .es-spark-plan-alt { border-color: rgba(233, 237, 246, 0.36); color: #e9edf6; }

        /* --- Chips --- */
        .es-spark-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.32rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 22, 35, 0.16);
            background: rgba(255, 255, 255, 0.75);
            color: #474e5c;
            font-size: 0.76rem;
            font-weight: 600;
        }
        .dark .es-spark-chip {
            border-color: rgba(233, 237, 246, 0.16);
            background: rgba(233, 237, 246, 0.05);
            color: #9aa5bd;
        }

        /* --- The field manifest table --- */
        .es-spark-table { width: 100%; border-collapse: collapse; text-align: start; }
        .es-spark-table th,
        .es-spark-table td {
            padding: 0.5rem 0.6rem 0.5rem 0;
            vertical-align: top;
            text-align: start;
            border-top: 1px solid rgba(16, 22, 35, 0.09);
        }
        .dark .es-spark-table th,
        .dark .es-spark-table td { border-top-color: rgba(233, 237, 246, 0.1); }
        .es-spark-table thead th { border-top: 0; }
        .es-spark-group th {
            padding-top: 1.2rem;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-spark-group th { color: #93c5fd; }
        .es-spark-field {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            font-weight: 600;
            white-space: nowrap;
            color: #101623;
        }
        .dark .es-spark-field { color: #e9edf6; }

        /* --- The allowance board --- */
        .es-spark-board { width: 100%; border-collapse: collapse; text-align: start; }
        .es-spark-board th,
        .es-spark-board td {
            padding: 0.6rem 0.7rem 0.6rem 0;
            vertical-align: top;
            text-align: start;
            border-top: 1px solid rgba(16, 22, 35, 0.09);
            font-size: 0.85rem;
        }
        .dark .es-spark-board th,
        .dark .es-spark-board td { border-top-color: rgba(233, 237, 246, 0.1); }
        .es-spark-board thead th {
            border-top: 0;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #474e5c;
        }
        .dark .es-spark-board thead th { color: #9aa5bd; }

        /* --- The venue ladder: one indent step per rung the code tries. --- */
        .es-spark-rung {
            position: relative;
            padding-inline-start: calc(0.9rem + var(--rung, 0) * 1.15rem);
            padding-block: 0.75rem;
        }
        .es-spark-rung::before {
            content: "";
            position: absolute;
            top: 1.15rem;
            inset-inline-start: calc(var(--rung, 0) * 1.15rem);
            width: 0.55rem;
            height: 2px;
            border-radius: 1px;
            background: #1d4ed8;
        }
        .dark .es-spark-rung::before { background: #93c5fd; }
        .es-spark-rung-last::before { background: rgba(16, 22, 35, 0.32); }
        .dark .es-spark-rung-last::before { background: rgba(233, 237, 246, 0.34); }

        /* --- Fixed-dark band. Identical with .dark on and off. --- */
        .es-spark-band {
            background-color: #070a11;
            background-image: radial-gradient(120% 100% at 50% 0%, #10182b 0%, #0a1020 55%, #05070d 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(233, 237, 246, 0.05);
        }
        /* The two terminals as inline chips, for the one-line gap in the finale. */
        .es-spark-term {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.9rem;
            border-radius: 0.7rem;
            font-size: 0.78rem;
            font-weight: 600;
        }
        .es-spark-rec.es-spark-term { padding-inline-start: 1.1rem; }

        /* The terminals also have to stop flipping inside the fixed-dark band:
           .dark .es-spark-scrap / .es-spark-rec would otherwise repaint them
           when the visitor is in dark mode. Same specificity, so these must
           stay AFTER the base rules. */
        .es-spark-band .es-spark-scrap {
            border-color: rgba(233, 237, 246, 0.28);
            background: rgba(233, 237, 246, 0.06);
        }
        .es-spark-band .es-spark-rec {
            border-color: rgba(147, 197, 253, 0.35);
            background: rgba(147, 197, 253, 0.08);
        }
        .es-spark-band .es-spark-rec::before { background: #93c5fd; }
        .es-spark-band .es-spark-arcv {
            background: linear-gradient(180deg, rgba(147, 197, 253, 0.14), rgba(147, 197, 253, 0.55), rgba(147, 197, 253, 0.14));
        }
        .es-spark-band .es-spark-arcv::after {
            background: #bfdbfe;
            box-shadow: 0 0 12px 3px rgba(147, 197, 253, 0.55);
        }

        /* Shared classes that would otherwise flip with the colour mode. */
        .es-spark-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 237, 246, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 237, 246, 0.05) 1px, transparent 1px);
        }
        .es-spark-band .es-aurora { opacity: 0.5; }
        .es-spark-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-spark-band .es-claim:focus-within {
            border-color: rgba(147, 197, 253, 0.75);
            box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.24);
        }

        /* --- Links and buttons --- */
        .es-spark-link { color: #1d4ed8; }
        .es-spark-link:hover { color: #101623; }
        .dark .es-spark-link { color: #93c5fd; }
        .dark .es-spark-link:hover { color: #e9edf6; }

        .es-spark-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(29, 78, 216, 0.55);
        }
        .es-spark-btn:hover { background-color: #1e40af; box-shadow: 0 22px 44px -14px rgba(29, 78, 216, 0.65); }
        .dark .es-spark-btn { background-color: #93c5fd; color: #080b12; }
        .dark .es-spark-btn:hover { background-color: #bfdbfe; }
        .es-spark-band .es-spark-btn { background-color: #93c5fd; color: #080b12; }
        .es-spark-band .es-spark-btn:hover { background-color: #bfdbfe; }

        /* --- Hover affordance on cards that are links --- */
        .es-spark-hover:hover { border-color: rgba(29, 78, 216, 0.5); }
        .dark .es-spark-hover:hover { border-color: rgba(147, 197, 253, 0.5); }
        .es-spark-hover:hover .es-spark-hover-title { color: #1d4ed8; }
        .dark .es-spark-hover:hover .es-spark-hover-title { color: #93c5fd; }

        /* --- Recolour the shared hero spotlight to this page's blue --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(147, 197, 253, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(147, 197, 253, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #93c5fd; }

        /* Dot-nav tooltip. Its colours live here, not in a Tailwind arbitrary
           value: an unbuilt `dark:bg-[#111725]` does nothing, which left light
           ink on a white pill and failed AA twelve times. */
        .es-spark-tip {
            border: 1px solid rgba(16, 22, 35, 0.14);
            background: #ffffff;
            color: #474e5c;
        }
        .dark .es-spark-tip {
            border-color: rgba(233, 237, 246, 0.14);
            background: #111725;
            color: #e9edf6;
        }

        /* --- Focus rings. No border-radius here: it would reshape the element. --- */
        #es-spark-page a:focus-visible,
        #es-spark-page summary:focus-visible,
        #es-spark-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-spark-page a:focus-visible,
        .dark #es-spark-page summary:focus-visible,
        .dark #es-spark-page button:focus-visible {
            outline-color: #93c5fd;
        }
        .es-spark-band a:focus-visible,
        .es-spark-band summary:focus-visible,
        .es-spark-band button:focus-visible {
            outline-color: #93c5fd !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-spark-arcv::after,
            .es-spark-arc::after {
                animation: none !important;
                opacity: 1 !important;
            }
            .es-spark-arcv::after { top: calc(50% - 4px); }
            .es-spark-arc::after { inset-inline-start: calc(50% - 4px); }
        }
    </style>

    @php
        // A plausible near date: the parser is told the event falls in this month
        // or the next, and GeminiUtils::parseEvent then nulls any parsed date more
        // than three days in the past or more than two months out rather than
        // letting a guessed year through.
        $demo = \Illuminate\Support\Carbon::now()->addDays(18)->setTime(20, 0);

        // Terminal one: the kind of scrap people actually send a curator.
        $scrapLines = [
            'Sarah Johnson Trio',
            'Blue Note, '.$demo->format('D M j').', doors 8pm',
            '$25 on the door',
            'tickets: bluenote.example/tix',
        ];

        // Terminal two: what came out. Keys are the real field names.
        $landed = [
            ['event_name', 'Sarah Johnson Trio'],
            ['event_date_time', $demo->format('Y-m-d H:i')],
            ['venue_name', 'Blue Note'],
            ['performer_name', 'Sarah Johnson Trio'],
            ['ticket_price', '25'],
            ['ticket_currency', 'USD'],
            ['registration_url', 'bluenote.example/tix'],
        ];

        // The full manifest: every field GeminiUtils::parseEvent asks Gemini for,
        // grouped, with what actually lands in it.
        $manifest = [
            ['The event itself', [
                ['event_name', 'Kept in the language it was written in.'],
                ['event_short_name', 'A two to five word version, used to build the URL.'],
                ['short_description', 'A one line summary, capped at 200 characters.'],
                ['event_details', 'The long description, as markdown.'],
                ['category_name', 'Matched against your own category list: exact first, then partial, then a similarity match above 70 percent.'],
            ]],
            ['When', [
                ['event_date_time', 'YYYY-MM-DD HH:MM. With no time given at all it defaults to 20:00.'],
                ['event_duration', 'In hours. There is no separate end column: the end is the start plus the duration.'],
            ]],
            ['Where', [
                ['venue_name', 'The name the venue lookup starts from. See the ladder below.'],
                ['event_address', 'The street line only, without the city or the state. Falls back to your own street when the source names none.'],
                ['event_city', 'The city, which is what narrows the venue lookup to the right one.'],
                ['event_state', 'Region or state, when the source names one.'],
                ['event_postal_code', 'When the source names one.'],
                ['event_country_code', 'Normalised to a lowercase two letter ISO code, so "ISR" arrives as "il".'],
                ['venue_email', 'Only if the source actually printed one.'],
                ['venue_website', 'Only if the source actually printed one.'],
            ]],
            ['Who', [
                ['performer_name', 'One per performer. Several distinct performers become several events, not one crowded event.'],
                ['performer_email', 'Only if the source printed one.'],
                ['performer_website', 'Only if the source printed one.'],
            ]],
            ['Money and links', [
                ['ticket_price', 'The number only, with no currency symbol.'],
                ['ticket_currency', 'A currency code, when a price was mentioned.'],
                ['registration_url', 'Kept as the registration link, and its preview image becomes the event image.'],
            ]],
        ];

        // The venue resolution ladder, in the order the code tries it. Rungs 1 to 3
        // are GeminiUtils::parseEvent; rung 4 is the safety-net lookup in
        // EventRepo::saveEvent that runs however the event was submitted.
        $ladder = [
            ['01', 'A venue you already own with this name', 'Owning it is the strongest possible signal, so no city and no country are needed. Matched on the normalised name or the normalised translated name.', 0],
            ['02', 'Same city, and the same name or the same street', 'Plus the country when there is one. Both sides are normalised first, so curly quotes, long dashes, stray spaces and capitals cannot break a match that should have worked.', 1],
            ['03', 'A venue already connected to this schedule', 'Anywhere this schedule already shares an event with, including venues another admin or a calendar sync added. Name or street, no city required.', 2],
            ['04', 'One more look when you press save', 'The normalised name, plus the city and country when they are there, is looked up again at save time. That check does not care which screen the event came from: AI import, the ordinary event form or a guest submission all get it.', 3],
            ['05', 'Nothing matched', 'Only now is a venue created, from the address you just checked in the preview. Nothing is quietly merged into the wrong room.', 4],
        ];

        // Enterprise generation set. Every row is a separate controller action.
        $generates = [
            [
                'Schedule descriptions',
                'A short description and a full markdown description for the schedule itself, from its name, type and categories.',
                'RoleController::generateScheduleDetails',
            ],
            [
                'Event descriptions',
                'A category, a short description and a full description for one event, from its name and the context around it.',
                'EventController::generateEventDetails',
            ],
            [
                'A flyer image',
                'A poster generated from the event details you already typed, with optional style instructions, regenerated until it is right.',
                'EventController::generateFlyer',
            ],
            [
                'A whole schedule style',
                'Profile image, header image, background image, accent colour and font. Ask for all five or regenerate just one.',
                'RoleController::generateStyle',
            ],
            [
                'Graphic email text',
                'A pass over the event list text that goes out with your graphic emails, driven by a prompt you save on the schedule.',
                'GraphicController::processGraphicAIText',
            ],
            [
                'Event parts from an agenda',
                'Name, description, start time and end time for each line of a printed agenda or a setlist.',
                'EventController::parseEventParts',
            ],
        ];

        // Honest allowance board. Caps come from config/usage.php; every one of
        // them returns null (no cap) when the install is selfhosted.
        $board = [
            ['Parse an event from text or an image', 'Every plan', '10 to 100 a day, by plan'],
            ['Translate the whole schedule', 'Every plan', 'Scheduled background job'],
            ['Scan an agenda into event parts', 'Enterprise', '10 a day'],
            ['Write a schedule or event description', 'Enterprise', '50 a day'],
            ['Pass graphic email text through AI', 'Enterprise', '50 a day'],
            ['Generate a flyer or a style image', 'Enterprise', '3 a day on trial, 10 once paid'],
            ['Create an event over WhatsApp', 'Enterprise', 'Counts against the parse allowance'],
        ];

        // Language names come from config so the count, the chip strip and the FAQ
        // answer cannot drift apart the way the old page's "12" and its nine chips did.
        $langNames = collect(config('app.supported_languages'))->map(fn ($n) => ucfirst($n))->values()->sort()->values()->all();
        $langCount = count($langNames);
        $langList = implode(', ', array_slice($langNames, 0, -1)).' and '.$langNames[$langCount - 1];

        $faqs = [
            [
                'q' => 'What can the AI parser read?',
                'a' => 'Text you paste or type, and one image you drop, paste from the clipboard, or pick from a file dialog. Images can be JPEG, PNG, GIF or WebP, so a photo of a poster, a screenshot of a message and an exported flyer all work. What it cannot do is read a web page for you. A registration link is followed once for its preview image, but nothing on that page is mined for the details, so paste the text off the page rather than the link to it.',
            ],
            [
                'q' => 'Can one image become several events?',
                'a' => 'Yes. The parser returns a list, not a single record, and it is told to split distinct performers into separate events. A month grid or a festival line-up comes back as one row per event, each with its own date, venue and performer, and you save them one at a time or all together.',
            ],
            [
                'q' => 'Which AI features are free and which are Enterprise?',
                'a' => 'Event parsing and whole-schedule translation are on every plan, including the free one. Agenda scanning into event parts, description writing, flyer generation, schedule style generation, the AI pass over graphic email text and event creation over WhatsApp are Enterprise. Parsing is metered by a daily allowance rather than a plan gate, and a selfhosted install has no cap at all.',
            ],
            [
                'q' => 'Does the AI ever save anything without me?',
                'a' => 'Not from the import screen. Parsed events land in a preview where every field is an editable input, and nothing reaches your schedule until you save that row. The one exception is WhatsApp on Enterprise, which is designed to be hands free: it parses the message and creates the event, then messages you back a link so you can go and correct it.',
            ],
            [
                'q' => 'Will it create a duplicate venue?',
                'a' => 'It tries hard not to. Before making anything, it looks for a venue you already own with that name, then for a venue in the same city with the same name or street, then among the venues this schedule already shares an event with. When you press save the normalised name, city and country are looked up one more time, whatever screen the event came from. Only when all of that misses is a venue created, from the address you checked in the preview. Performers get a shorter version of the same treatment: the parsed name plus your country first, then the talent this schedule already works with.',
            ],
            [
                'q' => 'What if the event is already on my schedule?',
                'a' => 'The preview tells you. If an upcoming event on this schedule already has the same registration link, or the same start time plus the same venue address or the same performer, the row links to the event that exists instead of quietly making a second copy of it.',
            ],
            [
                'q' => 'Can I teach it my agenda format?',
                'a' => 'Yes, for agenda scanning. You can write instructions like "each line is a session, format is time then speaker then topic, ignore the lunch breaks" and either use them once or save them as the default for the whole schedule, with a per event override. Agenda scanning is an Enterprise feature.',
            ],
            [
                'q' => 'Which languages does it work in?',
                'a' => 'Parsing is not restricted to a list: fields are kept in the language they were written in, and where a translated twin exists the AI fills that too. The separate translation feature covers '.$langCount.' languages: '.$langList.'. Pick one target language for the schedule and visitors can switch between your language and it.',
            ],
            [
                'q' => 'Which models are behind this, and what if I selfhost?',
                'a' => 'Text parsing, writing and translation go to Google Gemini, and image generation to OpenAI, both configurable. A selfhosted install brings its own API keys, which is why the daily allowances do not apply there: you are paying the model provider directly rather than us.',
            ],
        ];

        $dotSections = [
            ['top', 'The gap'],
            ['crosses', 'What crosses'],
            ['manifest', 'The record'],
            ['match', 'The ladder'],
            ['agenda', 'The agenda'],
            ['generate', 'What else it lights'],
            ['languages', 'Languages'],
            ['whatsapp', 'WhatsApp'],
            ['allowance', 'Allowances'],
            ['next', 'Next'],
            ['faq', 'Questions'],
            ['claim', 'Start'],
        ];
    @endphp

    <div id="es-spark-page" class="es-spark-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the gap, and one thing crossing it                  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 32%, rgba(29, 78, 216, 0.2), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(147, 197, 253, 0.14), rgba(147, 197, 253, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-spark-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 3L5 14h5l-1 7 8-11h-5l1-7z" />
                        </svg>
                        <span class="es-spark-muted text-sm font-medium tracking-wide">AI features, described exactly</span>
                    </div>

                    <h1 class="es-balance es-spark-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A poster is not an event.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Something has to <span class="es-spark-accent">cross the gap.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-spark-muted mb-10 max-w-xl text-lg sm:text-xl">
                        On one side, the scrap somebody sent you. On the other, the fields a listing needs: a date, a duration, a venue, a price, a currency. Paste the text or drop the image, and the AI fills the form. You still get the last word on every field.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="es-spark-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Try it free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                        <a href="{{ route('marketing.docs.ai_import') }}" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Read the AI Import guide
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The gap, vertical: scrap above, record below, exactly as the
                     import screen is laid out. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-spark-card p-6 sm:p-7">
                        <p class="es-spark-tag mb-3">What you were sent</p>
                        <div class="es-spark-scrap">
                            <p class="es-spark-mono es-spark-muted text-[0.8rem] leading-relaxed">
                                @foreach ($scrapLines as $line)
                                    {{ $line }}@if (! $loop->last)<br>@endif
                                @endforeach
                            </p>
                        </div>

                        <div class="es-spark-arcv my-3" aria-hidden="true"></div>

                        <p class="es-spark-tag mb-3">What the form receives</p>
                        <div class="es-spark-rec">
                            @foreach ($landed as [$k, $v])
                                <div class="es-spark-row">
                                    <span class="es-spark-key">{{ $k }}</span>
                                    <span class="es-spark-val es-spark-ink">{{ $v }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-spark-muted es-spark-sep mt-5 pt-4 text-xs">
                            Every one of those is an editable input in the preview. Nothing is saved to your schedule until you save the row.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. What crosses, and what does not                           -->
    <!-- ============================================================ -->
    <section id="crosses" class="scroll-mt-24 es-spark-edge py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                    <span class="es-spark-num">01</span>
                    <span class="es-spark-arc"></span>
                </div>
                <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gap is narrow on purpose</p>
                <h2 class="es-balance es-spark-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Two inputs cross it. <span class="es-spark-accent">A link does not.</span>
                </h2>
                <p class="es-spark-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Most tools are vague about this and you find out the hard way. So here it is plainly: the parser reads what you hand it, and it does not go browsing.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-3" data-reveal-group="90">
                <div class="es-spark-card flex h-full flex-col p-7" data-reveal="panel">
                    <span class="es-spark-num mb-4">IN</span>
                    <h3 class="es-spark-ink mb-3 text-xl font-bold">Text you paste or type</h3>
                    <p class="es-spark-muted mb-5">A forwarded email, a message thread, a line off a listings page, a setlist. Any language: fields come back in the language they were written in, and the translated twins get filled alongside them.</p>
                    <div class="mt-auto flex flex-wrap gap-2">
                        <span class="es-spark-chip">Paste</span>
                        <span class="es-spark-chip">Type</span>
                        <span class="es-spark-chip">Any language</span>
                    </div>
                </div>

                <div class="es-spark-card flex h-full flex-col p-7" data-reveal="panel">
                    <span class="es-spark-num mb-4">IN</span>
                    <h3 class="es-spark-ink mb-3 text-xl font-bold">One image, however you have it</h3>
                    <p class="es-spark-muted mb-5">Drag it onto the box, paste it out of your clipboard, or pick a file. JPEG, PNG, GIF and WebP, which covers a phone photo of a poster and a screenshot of a group chat. The image can become the event image too.</p>
                    <div class="mt-auto flex flex-wrap gap-2">
                        <span class="es-spark-chip">Drop</span>
                        <span class="es-spark-chip">Paste</span>
                        <span class="es-spark-chip">Upload</span>
                    </div>
                </div>

                <div class="es-spark-card flex h-full flex-col p-7" data-reveal="panel">
                    <span class="es-spark-num mb-4">NOT IN</span>
                    <h3 class="es-spark-ink mb-3 text-xl font-bold">A URL for it to read</h3>
                    <p class="es-spark-muted mb-5">The parser never reads a web page for event details, so a bare link on its own gives it nothing to work with. Open the page, copy the text, paste that. A ticket link inside your text is kept as the registration link and followed once for its preview image, which becomes the event image, but the page behind it is never mined for the date, the venue or the price.</p>
                    <p class="es-spark-muted mt-auto text-sm">
                        A <a href="{{ marketing_url('/selfhost') }}" class="es-spark-link font-semibold hover:underline">selfhosted install</a> is the one exception: a schedule there can be given a list of source pages and cities and swept once a day by the import command. That runs on your own server with your own keys, so it is not part of the hosted service.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The record: the field manifest                            -->
    <!-- ============================================================ -->
    <section id="manifest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                    <span class="es-spark-num">02</span>
                    <span class="es-spark-arc"></span>
                </div>
                <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The far terminal</p>
                <h2 class="es-balance es-spark-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The record it has to <span class="es-spark-accent">land in.</span>
                </h2>
                <p class="es-spark-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Not a blob of text with a date somewhere in it. These are the fields the extraction actually asks for, and the ones you will be editing in the preview.
                </p>
            </div>

            <div class="es-spark-card overflow-x-auto p-6 sm:p-8" data-reveal="panel">
                <table class="es-spark-table">
                    <caption class="sr-only">Every field the AI parser extracts, grouped, with what lands in each one</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-spark-tag">Field</th>
                            <th scope="col" class="es-spark-tag">What lands in it</th>
                        </tr>
                    </thead>
                    @foreach ($manifest as [$groupName, $rows])
                        <tbody>
                            <tr class="es-spark-group">
                                <th scope="colgroup" colspan="2">{{ $groupName }}</th>
                            </tr>
                            @foreach ($rows as [$field, $desc])
                                <tr>
                                    <th scope="row" class="es-spark-field">{{ $field }}</th>
                                    <td class="es-spark-muted text-sm">{{ $desc }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endforeach
                </table>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="es-spark-card p-7" data-reveal="panel">
                    <h3 class="es-spark-ink mb-3 text-lg font-bold">Translated twins, filled in the same pass</h3>
                    <p class="es-spark-muted text-sm">Name, short name, short description, address, city, state, venue name and performer name each have a translated twin, and the twin is asked for only when the main field is not already English. If your schedule language is not English the AI is told outright: keep the original wording in the main field, put the translation in the twin, rather than quietly overwriting one with the other.</p>
                </div>
                <div class="es-spark-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-spark-ink text-lg font-bold">Your own custom fields, too</h3>
                        <span class="es-spark-plan es-spark-plan-alt">Pro</span>
                    </div>
                    <p class="es-spark-muted text-sm">Custom fields you have added to events are appended to the extraction list with their type, their options and any hint you wrote for them, so a dropdown comes back as one of your options and a switch comes back as yes or no.</p>
                    <p class="mt-auto pt-4">
                        <x-link href="{{ marketing_url('/features/custom-fields') }}">About custom fields</x-link>
                    </p>
                </div>
            </div>

            <div class="es-spark-card mt-4 p-7 sm:p-8" data-reveal="panel">
                <div class="grid items-center gap-8 md:grid-cols-2">
                    <div>
                        <h3 class="es-spark-ink mb-3 text-xl font-bold">One document, several events</h3>
                        <p class="es-spark-muted mb-4">The parser returns a list, not a single record, and it is told to split distinct performers into separate events. A month grid, a festival line-up or a weekend of three sets comes back as one row per event, each with its own date, venue and performer.</p>
                        <p class="es-spark-muted text-sm">Save them one at a time as you check them, or work down the list. A row that turns out to be nonsense is simply not saved.</p>
                    </div>
                    <div aria-hidden="true">
                        <div class="es-spark-scrap mb-3">
                            <p class="es-spark-mono es-spark-muted text-[0.7rem] leading-relaxed">one image: 3 acts, 3 dates</p>
                        </div>
                        <div class="es-spark-arcv mb-3"></div>
                        <div class="space-y-2">
                            @foreach ([['Sarah Johnson Trio', 'Blue Note'], ['The Wickers', 'Blue Note'], ['Ana Dorset', 'The Lamp Room']] as $ri => [$who, $where])
                                <div class="es-spark-rec">
                                    <div class="es-spark-row">
                                        <span class="es-spark-key">event_name</span>
                                        <span class="es-spark-val es-spark-ink">{{ $who }}</span>
                                    </div>
                                    <div class="es-spark-row">
                                        <span class="es-spark-key">venue_name</span>
                                        <span class="es-spark-val es-spark-ink">{{ $where }}</span>
                                    </div>
                                    <div class="es-spark-row">
                                        <span class="es-spark-key">event_date_time</span>
                                        <span class="es-spark-val es-spark-ink">{{ $demo->copy()->addDays($ri * 7)->format('Y-m-d H:i') }}</span>
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
    <!-- 4. The ladder: how a venue is resolved                       -->
    <!-- ============================================================ -->
    <section id="match" class="scroll-mt-24 es-spark-edge py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                    <span class="es-spark-num">03</span>
                    <span class="es-spark-arc"></span>
                </div>
                <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Before it creates anything</p>
                <h2 class="es-balance es-spark-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Five rungs down, then <span class="es-spark-accent">and only then</span>, a new venue.
                </h2>
                <p class="es-spark-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The expensive failure in event import is not a wrong date, it is nine copies of the same room. So the lookup climbs down a ladder, and each rung only runs because the one above it missed.
                </p>
            </div>

            <div class="es-spark-card p-6 sm:p-8" data-reveal="panel">
                <ol class="es-spark-ladder">
                    @foreach ($ladder as [$n, $test, $detail, $indent])
                        <li class="es-spark-rung @if ($loop->last) es-spark-rung-last @endif" style="--rung: {{ $indent }};">
                            <div class="mb-1 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                                <span class="es-spark-num" aria-hidden="true">{{ $n }}</span>
                                <h3 class="es-spark-ink text-base font-bold">{{ $test }}</h3>
                            </div>
                            <p class="es-spark-muted text-sm">{{ $detail }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <div class="es-spark-card p-7" data-reveal="panel">
                    <h3 class="es-spark-ink mb-3 text-lg font-bold">Performers, two rungs of their own</h3>
                    <p class="es-spark-muted text-sm">The parsed name as written, plus your schedule's country, preferring a record that already has an email. Then the talent this schedule has worked with before, on name alone. A match links the event to the performer who exists instead of making a second one.</p>
                </div>
                <div class="es-spark-card p-7" data-reveal="panel">
                    <h3 class="es-spark-ink mb-3 text-lg font-bold">Your own schedule is assumed</h3>
                    <p class="es-spark-muted text-sm">Importing into a venue schedule pins every event to that venue and uses its address. Importing into a talent schedule pins every event to that performer. There is nothing to match, because you already told us.</p>
                </div>
                <div class="es-spark-card p-7" data-reveal="panel">
                    <h3 class="es-spark-ink mb-3 text-lg font-bold">Already on the schedule</h3>
                    <p class="es-spark-muted text-sm">If an upcoming event here already shares the registration link, or the start time plus the venue address or the performer, the row links to the event that exists rather than making a near duplicate of it.</p>
                </div>
            </div>

            <p class="es-spark-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Matching is normalisation, not guesswork. Curly quotes, long dashes, non-breaking spaces and capitals are folded on both sides first, so "The Blue Note" and "the blue note" are the same room and neither becomes a new one.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The agenda: a second, finer gap                           -->
    <!-- ============================================================ -->
    <section id="agenda" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                        <span class="es-spark-num">04</span>
                        <span class="es-spark-arc"></span>
                    </div>
                    <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Inside one event</p>
                    <h2 class="es-balance es-spark-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        The same gap, <span class="es-spark-accent">one level down.</span>
                    </h2>
                    <p class="es-spark-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A conference day, a festival stage or a two hour set is not one thing, it is a running order. Point your camera at the printed agenda or upload the image, and each line becomes a part of the event with its own name, note and times.
                    </p>
                    <ul class="es-spark-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-spark-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Four things per line: a name, an optional note or speaker, a start time and an end time, in 24 hour form.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-spark-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A setlist with no times comes back in its printed order with the times left empty, rather than invented.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-spark-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Keep the photo of the agenda on the event if you want it, or throw it away after the scan.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-spark-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Agenda scanning is an Enterprise feature. Event parts themselves are free to build by hand on any plan.</span>
                        </li>
                    </ul>
                    <p class="mt-6" data-reveal>
                        <x-link href="{{ route('marketing.docs.scan_agenda') }}">Read the Scan Agenda guide</x-link>
                    </p>
                </div>

                <div data-reveal="panel">
                    <div class="es-spark-card p-6 sm:p-7">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <p class="es-spark-tag">The printed sheet</p>
                            <span class="es-spark-plan">Enterprise</span>
                        </div>
                        <div class="es-spark-scrap">
                            <p class="es-spark-mono es-spark-muted text-[0.8rem] leading-relaxed">
                                09:00 Opening keynote, R. Adeyemi<br>
                                10:30 Panel: touring on a budget<br>
                                12:00 Lunch<br>
                                13:30 Workshop A, studio 2
                            </p>
                        </div>

                        <div class="es-spark-arcv my-3" aria-hidden="true"></div>

                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <p class="es-spark-tag">Event parts</p>
                            <span class="es-spark-chip">Prompt: ignore the breaks</span>
                        </div>
                        <div class="es-spark-rec" aria-hidden="true">
                            @foreach ([['Opening keynote', 'R. Adeyemi', '09:00', '10:15'], ['Panel: touring on a budget', 'Main room', '10:30', '11:45'], ['Workshop A', 'Studio 2', '13:30', '15:00']] as [$pn, $pd, $ps, $pe])
                                <div class="es-spark-row">
                                    <span class="es-spark-key">{{ $ps }}-{{ $pe }}</span>
                                    <span class="es-spark-val es-spark-ink">{{ $pn }}</span>
                                    <span class="es-spark-muted text-xs">{{ $pd }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-spark-muted es-spark-sep mt-5 pt-4 text-xs">
                            Write the instructions once and save them as the schedule default, then override them on the one event that is laid out differently.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. What else the spark lights (fixed-dark band)              -->
    <!-- ============================================================ -->
    <section id="generate" class="scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-spark-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-4 py-16 sm:px-6 lg:px-8 lg:py-24 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 26% 26%, rgba(29, 78, 216, 0.3), rgba(29, 78, 216, 0) 62%);"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 66%, rgba(147, 197, 253, 0.16), rgba(147, 197, 253, 0) 62%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                        <span class="es-spark-num">05</span>
                        <span class="es-spark-arc"></span>
                    </div>
                    <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Enterprise generation</p>
                    <h2 class="es-balance mb-5 text-3xl font-black tracking-tight text-white md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Six more places it <span class="es-spark-lit">strikes.</span>
                    </h2>
                    <p class="es-spark-bmuted mx-auto max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Reading is on every plan. Writing and drawing sit on the Enterprise plan, each one a separate button you press deliberately rather than something running in the background.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                    @foreach ($generates as [$gTitle, $gBody, $gPath])
                        <div class="es-spark-card flex h-full flex-col p-7" data-reveal="panel">
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-white">{{ $gTitle }}</h3>
                                <span class="es-spark-plan">Enterprise</span>
                            </div>
                            <p class="es-spark-bmuted mb-5 text-sm">{{ $gBody }}</p>
                            <p class="es-spark-mono es-spark-lit mt-auto text-[0.7rem]">{{ $gPath }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mx-auto mt-10 max-w-3xl text-center" data-reveal>
                    <p class="es-spark-bmuted text-sm">
                        Text generation goes to Google Gemini and image generation to OpenAI, and both are configurable. Generated images arrive as ordinary uploads on your schedule, so you can regenerate, replace or delete them like any image you had uploaded yourself.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Translation                                               -->
    <!-- ============================================================ -->
    <section id="languages" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                        <span class="es-spark-num">06</span>
                        <span class="es-spark-arc"></span>
                    </div>
                    <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Free on every plan</p>
                    <h2 class="es-balance es-spark-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Write it once. <span class="es-spark-accent">Publish it twice.</span>
                    </h2>
                    <p class="es-spark-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Pick one target language for the schedule and the whole thing is translated in the background: the schedule name and descriptions, every event, event parts, sub-schedule names, your request terms. Visitors get a switch between your language and the target.
                    </p>
                    <ul class="es-spark-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-spark-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Translations are stored, not fetched per visit, so a guest page never waits on a model.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-spark-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Change the target language and the stored translations are cleared and rebuilt in the new one, including sub-schedule names.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-spark-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>You can edit any translated value by hand and keep your wording instead of the model's.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-spark-card p-6 sm:p-8">
                        <p class="es-spark-tag mb-4">{{ $langCount }} languages</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (config('app.supported_languages') as $langCode => $langName)
                                <span class="es-spark-chip">
                                    <span class="es-spark-mono es-spark-accent me-2 text-[0.7rem] uppercase">{{ $langCode }}</span>
                                    {{ ucfirst($langName) }}
                                </span>
                            @endforeach
                        </div>
                        <p class="es-spark-muted es-spark-sep mt-6 pt-5 text-sm">
                            Parsing is not limited to this list. It reads whatever the source is written in and keeps it that way. The list is what the schedule can be published in.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. WhatsApp                                                  -->
    <!-- ============================================================ -->
    <section id="whatsapp" class="scroll-mt-24 es-spark-edge py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                    <span class="es-spark-num">07</span>
                    <span class="es-spark-arc"></span>
                </div>
                <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gap, with no screen</p>
                <h2 class="es-balance es-spark-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Photograph the poster. <span class="es-spark-accent">Send it. Done.</span>
                </h2>
                <p class="es-spark-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The one place the AI does save without you. It is meant for the moment you are standing in front of a wall of flyers, so it goes all the way and then reports back.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.15fr_1fr]">
                <div class="es-spark-card flex flex-col p-7 sm:p-8" data-reveal="panel">
                    <div class="mb-5 flex flex-wrap items-center gap-2">
                        <h3 class="es-spark-ink text-xl font-bold">How it actually works</h3>
                        <span class="es-spark-plan">Enterprise</span>
                    </div>
                    <ol class="space-y-4">
                        @foreach ([
                            ['01', 'Verify your phone number once, in your account settings. That number is how the message is matched to you, so it is your phone that is authorised, not a number of ours that you share around.'],
                            ['02', 'Message the Event Schedule number on WhatsApp. Type the details, or just send the photo of the flyer.'],
                            ['03', 'The event is created on your default schedule, or on your only schedule if you have one. The photo you sent becomes the event image.'],
                            ['04', 'You get a reply with the name, the date and a link, so the first thing you can do is open it and fix whatever the poster got wrong.'],
                        ] as [$wn, $wtext])
                            <li class="flex gap-4">
                                <span class="es-spark-num mt-1 flex-none" aria-hidden="true">{{ $wn }}</span>
                                <p class="es-spark-muted text-sm leading-relaxed">{{ $wtext }}</p>
                            </li>
                        @endforeach
                    </ol>
                    <p class="es-spark-muted es-spark-sep mt-auto pt-5 text-xs">
                        The message only ever makes the event. Tickets, visibility, recurrence and everything else are still set the ordinary way afterwards, on the edit screen.
                    </p>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="es-spark-card p-7" data-reveal="panel">
                        <h3 class="es-spark-ink mb-3 text-lg font-bold">Auto-curation still applies</h3>
                        <p class="es-spark-muted text-sm">If the schedule is set to curate into others automatically, the new event goes out to them as well, the same as one you had typed in yourself, and waits for approval wherever that curator reviews submissions.</p>
                    </div>
                    <div class="es-spark-card p-7" data-reveal="panel">
                        <h3 class="es-spark-ink mb-3 text-lg font-bold">Already have it? It says so.</h3>
                        <p class="es-spark-muted text-sm">A message that resolves to an event already on your schedule gets a link back to that event instead of a duplicate.</p>
                    </div>
                    <div class="es-spark-card p-7" data-reveal="panel">
                        <h3 class="es-spark-ink mb-3 text-lg font-bold">Needs Twilio</h3>
                        <p class="es-spark-muted text-sm">WhatsApp runs through Twilio, so a selfhosted install has to configure that first before the feature exists at all.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Allowances: the honest board                              -->
    <!-- ============================================================ -->
    <section id="allowance" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                    <span class="es-spark-num">08</span>
                    <span class="es-spark-arc"></span>
                </div>
                <p class="es-spark-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Plans and allowances</p>
                <h2 class="es-balance es-spark-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    What is free, what is not, <span class="es-spark-accent">and where it stops.</span>
                </h2>
                <p class="es-spark-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Model calls cost real money, so the ones on the free plan are metered rather than pretended to be infinite. Here are the actual limits.
                </p>
            </div>

            <div class="es-spark-card overflow-x-auto p-6 sm:p-8" data-reveal="panel">
                <table class="es-spark-board">
                    <caption class="sr-only">Which AI features are on which plan, and their daily allowance</caption>
                    <thead>
                        <tr>
                            <th scope="col">Feature</th>
                            <th scope="col">Plan</th>
                            <th scope="col">Daily allowance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($board as [$bFeature, $bPlan, $bCap])
                            <tr>
                                <th scope="row" class="es-spark-ink pe-3 font-semibold">{{ $bFeature }}</th>
                                <td class="pe-3">
                                    <span class="es-spark-plan @if ($bPlan !== 'Every plan') es-spark-plan-alt @endif">{{ $bPlan }}</span>
                                </td>
                                <td class="es-spark-muted es-spark-mono text-xs">{{ $bCap }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2" data-reveal-group="90">
                <div class="es-spark-card flex flex-col p-7" data-reveal="panel">
                    <h3 class="es-spark-ink mb-3 text-lg font-bold">Selfhost and the caps disappear</h3>
                    <p class="es-spark-muted mb-4 text-sm">Every one of those allowances only exists on the hosted service. A selfhosted install supplies its own Gemini and OpenAI keys, pays the model provider directly and is not metered by us at all.</p>
                    <p class="mt-auto">
                        <x-link href="{{ route('marketing.docs.selfhost.ai') }}">Selfhosted AI setup</x-link>
                    </p>
                </div>
                <div class="es-spark-card flex flex-col p-7" data-reveal="panel">
                    <h3 class="es-spark-ink mb-3 text-lg font-bold">Agents can drive all of this</h3>
                    <p class="es-spark-muted mb-4 text-sm">There is a REST API with an OpenAPI 3.0 spec, plus llms.txt and agents.json so an agent can discover it and work without a human reading the docs first. API access is on the Pro plan.</p>
                    <p class="mt-auto">
                        <x-link href="{{ marketing_url('/for-ai-agents') }}">The API for AI agents</x-link>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Where to go next                                         -->
    <!-- ============================================================ -->
    <section id="next" class="scroll-mt-24 es-spark-edge py-20 lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                    <span class="es-spark-num">09</span>
                    <span class="es-spark-arc"></span>
                </div>
                <h2 class="es-balance es-spark-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Where this <span class="es-spark-accent">usually leads.</span>
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ([
                    ['Creating events', 'Everything the import screen hands off to: dates, recurrence, parts, visibility and tickets.', route('marketing.docs.creating_events')],
                    ['Event graphics', 'Turn the events you just imported into shareable images for Instagram, WhatsApp and email.', marketing_url('/features/event-graphics')],
                    ['Newsletters', 'Mail the line-up to your followers and ticket buyers. Free on every plan, at ten recipients a month.', route('marketing.newsletters')],
                    ['Calendar sync', 'Two-way sync with Google, Outlook and CalDAV, so an imported event lands in your own calendar too.', marketing_url('/features/calendar-sync')],
                    ['Recurring events', 'A weekly night is one event with a day-of-week pattern, not fifty rows to import.', marketing_url('/features/recurring-events')],
                    ['Custom fields', 'Add your own fields and the parser will extract them alongside the built-in ones.', marketing_url('/features/custom-fields')],
                ] as [$nTitle, $nBody, $nHref])
                    <a href="{{ $nHref }}" class="es-spark-card es-spark-hover group flex h-full flex-col p-7 transition-all duration-200 hover:-translate-y-0.5" data-reveal="panel">
                        <h3 class="es-spark-ink es-spark-hover-title mb-3 text-lg font-bold transition-colors">{{ $nTitle }}</h3>
                        <p class="es-spark-muted mb-5 text-sm">{{ $nBody }}</p>
                        <span class="es-spark-accent mt-auto inline-flex items-center gap-2 text-sm font-semibold transition-all group-hover:gap-3">
                            Read more
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3" data-reveal>
                <span class="es-spark-tag me-1">Most used by</span>
                @foreach ([['/for-curators', 'Curators'], ['/for-musicians', 'Musicians'], ['/for-venues', 'Venues']] as [$aHref, $aLabel])
                    <a href="{{ marketing_url($aHref) }}" class="es-spark-chip es-spark-hover transition-colors">{{ $aLabel }}</a>
                @endforeach
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
                <div class="es-spark-mark mb-6" data-reveal aria-hidden="true">
                    <span class="es-spark-num">10</span>
                    <span class="es-spark-arc"></span>
                </div>
                <h2 class="es-balance es-spark-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-spark-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    The ones worth answering precisely, because the vague answers are what waste your afternoon.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-spark-card es-spark-hover group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-spark-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-spark-num mt-1 flex-none" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-spark-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-spark-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-spark-muted mt-4 leading-relaxed ps-10">{{ $faq['a'] }}</p>
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
            <div class="es-spark-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 18%, rgba(29, 78, 216, 0.34), rgba(29, 78, 216, 0) 60%);"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-spark-tag mb-4">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Paste the mess. <span class="es-spark-lit">Keep the listing.</span>
                    </h2>
                    <p class="es-spark-bmuted mx-auto mb-8 max-w-2xl text-lg sm:text-xl">
                        Parsing and translation are included on the free plan, and Event Schedule takes zero platform fees on ticket sales.
                    </p>

                    {{-- The gap one last time, at the smallest scale the page uses:
                         one scrap, one filament, one record. --}}
                    <div class="mx-auto mb-10 flex max-w-xl flex-col items-center justify-center gap-2 sm:flex-row sm:gap-4">
                        <span class="es-spark-scrap es-spark-term es-spark-mono es-spark-bmuted">a photo on your phone</span>
                        <span class="es-spark-arcv sm:hidden" aria-hidden="true"></span>
                        <span class="es-spark-arc hidden sm:block" aria-hidden="true"></span>
                        <span class="es-spark-rec es-spark-term es-spark-mono text-white">an event on your schedule</span>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-spark-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-spark-bmuted mt-6 text-sm">No credit card required</p>
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
                        <span class="es-spark-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
