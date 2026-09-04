<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.calendar_sync_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.calendar_sync_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Calendar Sync</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Calendar Sync",
        "description": "Two-way calendar sync with Google Calendar, Outlook and Microsoft 365, or any CalDAV server. Events leave when you save them, and edits made in your calendar app come back. Free on every plan.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Calendar Synchronization"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Calendar Sync",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Calendar Synchronization Software",
        "operatingSystem": "Web",
        "description": "Two-way calendar sync with Google Calendar, Outlook and Microsoft 365, or any CalDAV server. Push events out, pull events in, or both. Free on every plan.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Included free on every plan"
        },
        "featureList": [
            "Google Calendar two-way sync with webhook push notifications",
            "Outlook and Microsoft 365 two-way sync over the Microsoft Graph API",
            "CalDAV two-way sync with any CalDAV server",
            "Per-schedule direction: out, back, or both",
            "Choose which calendar each schedule syncs with",
            "Per-schedule policy for events deleted in the connected calendar",
            "Calendar description template for outbound entries",
            "Add to Calendar buttons for Google Calendar, Apple Calendar and Outlook on event pages",
            "Subscribe-able iCal feed for a whole schedule"
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
           For calendar-sync "The Round Trip" styles. Renamed from
           "The Loop", which /features/recurring-events already held - a
           recurrence really is a loop, whereas a two-way sync is a
           round trip. Keep that note: it is the anti-collision record.

           THE PAGE IS AN ITINERARY WITH TWO LEGS, NOT A GRADIENT WITH
           A SYNC ICON. A round trip is bought as one fare and flown as
           two legs, and that is exactly the product: LEG 01 is what
           leaves here when you save, LEG 02 is what comes back when you
           edit in your calendar app. Every section is labelled as a
           leg, and the whole argument of the page is that the product
           sells you the return leg too.

           COLOUR IS A DIRECTION, NOT DECORATION. The page keeps its
           existing blue + teal family, but stops using it as one
           left-to-right gradient. Blue #1d4ed8 is ALWAYS outbound and
           teal #0f6f66 is ALWAYS the return leg, on every rail, plate,
           arrow and table tick. A reader can tell which way a sentence
           points before reading it. No gradient headline text anywhere:
           solid accent words only, so no bright stop is ever scored
           against a light ground.

           THE STUB IS A PRINTED TICKET, so it is pinned: .es-trip-stub
           and everything inside it render IDENTICALLY with `.dark` on
           and off (verified with the verifier's --bands flag). Nothing
           inside it may use a `dark:` utility or a shared class that
           carries its own `.dark` rule - hence the stub gets its own
           ink, muted, tag, rule and pill classes rather than reusing
           the page ones.

           The dark bands are fixed objects too, so .es-trip-band
           re-inks .grid-overlay, .animate-shimmer and
           .es-claim:focus-within after the base rules. Deliberately NO
           .es-aurora inside a band: the shared rule flips its opacity
           0.5 -> 0.55 with the colour mode and would break the pin.

           NEVER use text-gray-500 on this ground - #6b7280 measures
           only 4.4 on #f5f7f9. Use .es-trip-muted (7.15).

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-trip-page { background-color: #f5f7f9; color: #12171e; }
        .dark .es-trip-page { background-color: #0a0d12; color: #e8edf2; }
        .es-trip-ink { color: #12171e; }
        .dark .es-trip-ink { color: #e8edf2; }
        .es-trip-muted { color: #4b5460; }
        .dark .es-trip-muted { color: #9aa5b1; }

        /* --- The two directions. Blue leaves, teal returns. --- */
        .es-trip-out { color: #1d4ed8; }
        .dark .es-trip-out { color: #7fb2ff; }
        .es-trip-back { color: #0f6f66; }
        .dark .es-trip-back { color: #5eead4; }
        /* Always-bright variants, for the fixed-dark bands in both modes. */
        .es-trip-lit-out { color: #7fb2ff; }
        .es-trip-lit-back { color: #5eead4; }
        .es-trip-band .es-trip-muted { color: #9aa5b1; }

        /* --- Hairlines. These are page-local classes and NOT Tailwind
               arbitrary values, because no build runs during a rebuild:
               a `border-[rgba(18,23,30,0.08)]` that is not already in
               public/build/assets/marketing-app-*.css silently does
               nothing and the section separators vanish. --- */
        .es-trip-rule-t { border-top: 1px solid rgba(18, 23, 30, 0.08); }
        .dark .es-trip-rule-t { border-top-color: rgba(232, 237, 242, 0.08); }
        .es-trip-rule-y {
            border-top: 1px solid rgba(18, 23, 30, 0.08);
            border-bottom: 1px solid rgba(18, 23, 30, 0.08);
        }
        .dark .es-trip-rule-y {
            border-top-color: rgba(232, 237, 242, 0.08);
            border-bottom-color: rgba(232, 237, 242, 0.08);
        }
        .es-trip-hair { border: 1px solid rgba(18, 23, 30, 0.12); }
        .dark .es-trip-hair { border-color: rgba(232, 237, 242, 0.12); }

        /* --- Cards --- */
        .es-trip-card {
            border: 1px solid rgba(18, 23, 30, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-trip-card {
            border-color: rgba(232, 237, 242, 0.12);
            background: rgba(232, 237, 242, 0.04);
        }
        .es-trip-band .es-trip-card {
            border-color: rgba(232, 237, 242, 0.14);
            background: rgba(232, 237, 242, 0.05);
        }

        /* --- Fixed-dark bands --- */
        .es-trip-band {
            background-color: #0b1016;
            background-image: radial-gradient(120% 100% at 50% 0%, #13203a 0%, #0d1420 55%, #070a0f 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(232, 237, 242, 0.05);
        }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-trip-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 237, 242, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 237, 242, 0.05) 1px, transparent 1px);
        }
        .es-trip-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-trip-band .es-claim:focus-within {
            border-color: rgba(127, 178, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(127, 178, 255, 0.22);
        }

        /* --- The rail: two tracks running opposite ways at once. This is
               the motif, and it replaces the old single row of pulsing
               dots, which said "activity" but not "both directions". --- */
        .es-trip-rail { display: grid; gap: 0.55rem; }
        .es-trip-track {
            height: 3px;
            border-radius: 2px;
            background-repeat: repeat-x;
            background-size: 26px 3px;
        }
        .es-trip-track-out {
            background-image: linear-gradient(90deg, rgba(29, 78, 216, 0.85) 0, rgba(29, 78, 216, 0.85) 12px, rgba(29, 78, 216, 0) 12px, rgba(29, 78, 216, 0) 26px);
            animation: es-trip-run-out 2.6s linear infinite;
        }
        .es-trip-track-back {
            background-image: linear-gradient(90deg, rgba(15, 111, 102, 0.85) 0, rgba(15, 111, 102, 0.85) 12px, rgba(15, 111, 102, 0) 12px, rgba(15, 111, 102, 0) 26px);
            animation: es-trip-run-back 2.6s linear infinite;
        }
        .dark .es-trip-track-out,
        .es-trip-band .es-trip-track-out {
            background-image: linear-gradient(90deg, rgba(127, 178, 255, 0.8) 0, rgba(127, 178, 255, 0.8) 12px, rgba(127, 178, 255, 0) 12px, rgba(127, 178, 255, 0) 26px);
        }
        .dark .es-trip-track-back,
        .es-trip-band .es-trip-track-back {
            background-image: linear-gradient(90deg, rgba(94, 234, 212, 0.8) 0, rgba(94, 234, 212, 0.8) 12px, rgba(94, 234, 212, 0) 12px, rgba(94, 234, 212, 0) 26px);
        }
        @keyframes es-trip-run-out { to { background-position: 26px 0; } }
        @keyframes es-trip-run-back { to { background-position: -26px 0; } }

        /* --- The leg plate: every section is labelled as a leg. --- */
        .es-trip-leg {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.32rem 0.8rem;
            border-radius: 0.35rem;
            border: 1px solid rgba(18, 23, 30, 0.18);
            background: #ffffff;
            color: #12171e;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-trip-leg,
        .es-trip-band .es-trip-leg {
            border-color: rgba(232, 237, 242, 0.2);
            background: rgba(232, 237, 242, 0.05);
            color: #e8edf2;
        }
        .es-trip-leg::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #1d4ed8;
        }
        .dark .es-trip-leg::before,
        .es-trip-band .es-trip-leg::before { background: #7fb2ff; }
        /* Return-leg plate: same object, other direction. */
        .es-trip-leg-in::before { background: #0f6f66; }
        .dark .es-trip-leg-in::before,
        .es-trip-band .es-trip-leg-in::before { background: #5eead4; }

        /* --- Eyebrow --- */
        .es-trip-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5460;
        }
        .dark .es-trip-tag { color: #9aa5b1; }
        .es-trip-band .es-trip-tag { color: #7fb2ff; }

        /* --- Plan pill. Everything on this page is on the free plan, so
               there is deliberately one pill and no Pro variant. --- */
        .es-trip-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(29, 78, 216, 0.4);
            color: #1d4ed8;
        }
        .dark .es-trip-plan,
        .es-trip-band .es-trip-plan { border-color: rgba(127, 178, 255, 0.42); color: #7fb2ff; }

        /* --- Carrier chip --- */
        .es-trip-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(18, 23, 30, 0.16);
            background: rgba(255, 255, 255, 0.75);
            color: #4b5460;
            font-size: 0.74rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        /* The band copy is listed with the dark one because a chip inside a
           fixed-dark band must not flip with the page mode. */
        .dark .es-trip-chip,
        .es-trip-band .es-trip-chip {
            border-color: rgba(232, 237, 242, 0.16);
            background: rgba(232, 237, 242, 0.05);
            color: #b0b9c4;
        }

        /* ==============================================================
           THE STUB. A printed round-trip ticket, so it is the same
           physical object in both colour modes. Every rule below is
           mode-independent on purpose.
           ============================================================== */
        .es-trip-stub {
            position: relative;
            border-radius: 0.9rem;
            border: 1px solid rgba(20, 24, 28, 0.16);
            background-color: #f4f2ec;
            background-image: linear-gradient(180deg, #faf9f5 0%, #efece4 100%);
            box-shadow: 0 26px 50px -24px rgba(6, 10, 20, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.85);
            color: #14181c;
        }
        .es-trip-stub-ink { color: #14181c; }
        .es-trip-stub-muted { color: #4d5560; }
        .es-trip-stub-out { color: #1d4ed8; }
        .es-trip-stub-back { color: #0f6f66; }
        .es-trip-stub-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #4d5560;
        }
        .es-trip-stub-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            color: #14181c;
        }
        /* The tear line. A border, not a drawing. */
        .es-trip-stub-perf { border-top: 1px dashed rgba(20, 24, 28, 0.3); }
        .es-trip-stub-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.1rem 0.42rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(20, 24, 28, 0.24);
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #14181c;
        }
        /* One leg of the itinerary: marker, route, note. */
        .es-trip-stub-leg {
            display: grid;
            grid-template-columns: 3.4rem 1fr;
            gap: 0.75rem;
            align-items: start;
        }
        .es-trip-stub-marker {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding-top: 0.18rem;
            color: #4d5560;
        }
        /* Static tracks inside the stub: printed, so never animated. */
        .es-trip-stub-track {
            height: 2px;
            border-radius: 2px;
            background-repeat: repeat-x;
            background-size: 20px 2px;
        }
        .es-trip-stub-track-out {
            background-image: linear-gradient(90deg, rgba(29, 78, 216, 0.85) 0, rgba(29, 78, 216, 0.85) 9px, rgba(29, 78, 216, 0) 9px, rgba(29, 78, 216, 0) 20px);
        }
        .es-trip-stub-track-back {
            background-image: linear-gradient(90deg, rgba(15, 111, 102, 0.85) 0, rgba(15, 111, 102, 0.85) 9px, rgba(15, 111, 102, 0) 9px, rgba(15, 111, 102, 0) 20px);
        }

        /* --- The ledger: what you did there, what happened here. --- */
        .es-trip-ledger { display: grid; gap: 0.6rem; }
        .es-trip-ledger-row {
            display: grid;
            gap: 0.6rem;
            grid-template-columns: 1fr;
            align-items: stretch;
        }
        .es-trip-ledger-cell {
            border-radius: 0.7rem;
            border: 1px solid rgba(18, 23, 30, 0.12);
            background: #ffffff;
            padding: 0.9rem 1rem;
        }
        .dark .es-trip-ledger-cell {
            border-color: rgba(232, 237, 242, 0.12);
            background: rgba(232, 237, 242, 0.04);
        }
        /* The left cell happened in the calendar app, so it carries the
           return colour; the right cell is what Event Schedule did. */
        /* The rule runs the full height of the row, so the label is centred
           against it rather than pinned to the top of an empty cell. */
        .es-trip-ledger-there {
            display: flex;
            align-items: center;
            border-left: 3px solid #0f6f66;
        }
        .dark .es-trip-ledger-there { border-left-color: #5eead4; }
        .es-trip-ledger-here { border-left: 3px solid rgba(18, 23, 30, 0.25); }
        .dark .es-trip-ledger-here { border-left-color: rgba(232, 237, 242, 0.28); }
        .es-trip-ledger-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f6f66;
        }
        .dark .es-trip-ledger-arrow { color: #5eead4; }
        /* The column headings only make sense once the two sides sit side by
           side, so they are absent on a phone rather than stacked. */
        .es-trip-ledger-head { display: none; }
        @media (min-width: 768px) {
            .es-trip-ledger-row { grid-template-columns: 1fr 2.25rem 1fr; }
            .es-trip-ledger-head {
                display: grid;
                gap: 0.6rem;
                grid-template-columns: 1fr 2.25rem 1fr;
            }
        }

        /* --- The carriers table. min-width lives here rather than as a
               Tailwind arbitrary class so the horizontal scroll actually
               happens on a phone. --- */
        .es-trip-table { width: 100%; min-width: 44rem; border-collapse: collapse; text-align: left; }
        .es-trip-table th,
        .es-trip-table td { padding: 0.85rem 0.8rem; vertical-align: top; }
        .es-trip-table tbody tr { border-top: 1px solid rgba(18, 23, 30, 0.1); }
        .dark .es-trip-table tbody tr { border-top-color: rgba(232, 237, 242, 0.1); }
        .es-trip-table tbody th { white-space: normal; }
        .es-trip-carrier {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #12171e;
        }
        .dark .es-trip-carrier { color: #e8edf2; }

        /* --- The booking counter. The three-step section used to be the one
               place the itinerary stopped: plain numbered cards with no leg
               plate and no rail. It now sits ON the outbound track, with one
               stop per counter aligned to the three columns, so the close of
               the page is still part of the trip. Hidden below md, where the
               cards stack and a horizontal track would say nothing. --- */
        .es-trip-stepline { position: relative; display: none; height: 0.85rem; align-items: center; }
        @media (min-width: 768px) { .es-trip-stepline { display: flex; } }
        .es-trip-stepline .es-trip-rail { flex: 1 1 auto; }
        .es-trip-stops {
            position: absolute;
            inset: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            align-items: center;
        }
        .es-trip-stop {
            width: 0.6rem;
            height: 0.6rem;
            margin: 0 auto;
            border-radius: 9999px;
            background: #1d4ed8;
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.16);
        }
        .dark .es-trip-stop {
            background: #7fb2ff;
            box-shadow: 0 0 0 4px rgba(127, 178, 255, 0.18);
        }
        /* The step marker borrows the stub's printed-marker typography rather
           than a big display numeral, so a counter reads as an itinerary line. */
        .es-trip-stepmark {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-trip-stepmark { color: #7fb2ff; }

        /* --- Links, buttons, hover states --- */
        .es-trip-link { color: #1d4ed8; }
        .es-trip-link:hover { color: #12171e; }
        .dark .es-trip-link { color: #7fb2ff; }
        .dark .es-trip-link:hover { color: #e8edf2; }

        .es-trip-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(29, 78, 216, 0.5);
        }
        .es-trip-btn:hover { background-color: #1a45c0; box-shadow: 0 22px 44px -14px rgba(29, 78, 216, 0.6); }
        .dark .es-trip-btn { background-color: #7fb2ff; color: #0a0d12; }
        .dark .es-trip-btn:hover { background-color: #a3c8ff; }

        .es-trip-hover:hover { border-color: rgba(29, 78, 216, 0.45); }
        .dark .es-trip-hover:hover { border-color: rgba(127, 178, 255, 0.45); }
        .es-trip-hover:hover .es-trip-hover-title,
        .es-trip-hover:hover .es-trip-hover-arrow { color: #1d4ed8; }
        .dark .es-trip-hover:hover .es-trip-hover-title,
        .dark .es-trip-hover:hover .es-trip-hover-arrow { color: #7fb2ff; }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-trip-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.12), transparent 60%);
        }
        .dark .es-trip-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(127, 178, 255, 0.1), transparent 60%);
        }
        /* Dot-nav tooltip. Its own class, not `dark:bg-[#12171e]`, which is
           not in the built CSS and would leave dark ink on a white pill. */
        .es-trip-tip {
            border: 1px solid rgba(18, 23, 30, 0.14);
            background: #ffffff;
            color: #4b5460;
        }
        .dark .es-trip-tip {
            border-color: rgba(232, 237, 242, 0.14);
            background: #12171e;
            color: #d3d9df;
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(127, 178, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #1d4ed8, #0f6f66); }
        .dark .es-dot.is-active .es-dot-pip { background: linear-gradient(180deg, #7fb2ff, #5eead4); }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-trip-page a:focus-visible,
        #es-trip-page summary:focus-visible,
        #es-trip-page button:focus-visible,
        #es-trip-page input:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-trip-page a:focus-visible,
        .dark #es-trip-page summary:focus-visible,
        .dark #es-trip-page button:focus-visible,
        .dark #es-trip-page input:focus-visible {
            outline-color: #7fb2ff;
        }
        .es-trip-band a:focus-visible,
        .es-trip-band summary:focus-visible,
        .es-trip-band button:focus-visible,
        .es-trip-band input:focus-visible {
            outline-color: #7fb2ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-trip-track-out,
            .es-trip-track-back { animation: none !important; }
        }
    </style>

    @php
        // The carriers table. One row per question somebody actually asks before
        // connecting, three columns for the three carriers the product supports.
        $carrierRows = [
            [
                'label' => 'How you connect',
                'google' => 'Google sign-in (OAuth)',
                'microsoft' => 'Microsoft sign-in (OAuth, Graph)',
                'caldav' => 'Server URL, username, password',
            ],
            [
                'label' => 'What triggers the return leg',
                'google' => 'A push notification from Google, plus a sweep every fifteen minutes',
                'microsoft' => 'A Graph subscription, plus a sweep every fifteen minutes',
                'caldav' => 'A poll every fifteen minutes, skipped when the calendar tag has not moved',
            ],
            [
                'label' => 'Pick which calendar',
                'google' => 'Yes, per schedule',
                'microsoft' => 'Yes, per schedule',
                'caldav' => 'Yes, discovered from your server',
            ],
            [
                'label' => 'Direction',
                'google' => 'Out, back, or both',
                'microsoft' => 'Out, back, or both',
                'caldav' => 'Out, back, or both',
            ],
            [
                'label' => 'Deleted in the calendar app',
                'google' => 'Keep it, mark it cancelled, or delete it',
                'microsoft' => 'Keep it, mark it cancelled, or delete it',
                'caldav' => 'Not applied: CalDAV brings back new and changed events only',
            ],
            [
                'label' => 'On a selfhosted install',
                'google' => 'Needs your own Google API credentials',
                'microsoft' => 'Needs your own Azure app registration',
                'caldav' => 'Nothing to register, just the server URL',
            ],
        ];

        // The return-leg ledger: what you did in the calendar app, and what
        // Event Schedule did about it.
        $ledger = [
            [
                'there' => 'You added an event',
                'here' => 'It appears on your schedule, filed properly: your slug pattern applied, your default event category set, and the location line turned into a venue rather than left as a line of text.',
            ],
            [
                'there' => 'You moved it, renamed it, or changed the place',
                'here' => 'The same event is updated in place. It is matched by the id we stored when the copy was made, with a name-and-time fallback behind that on Google and Outlook, so you do not end up holding two of them.',
            ],
            [
                'there' => 'You deleted it',
                'here' => 'Whatever you told the schedule to do: keep it here, mark it cancelled, or delete it. Events with ticket sales or live ad spend are hidden rather than destroyed.',
            ],
        ];

        $faqs = [
            [
                'q' => 'How do I connect Google Calendar?',
                'a' => 'Connect your Google account once in Account Settings, then open your schedule, go to Edit and open Integrations. On the Google Calendar tab, pick which calendar this schedule uses and pick the direction: out, back, or both. If you chose out or both, events start moving from your next save. It is on the free plan.',
            ],
            [
                'q' => 'How often does the calendar sync?',
                'a' => 'Google and Outlook push a notification the moment something changes on their side, so the return leg usually runs within a minute or two. A sweep also runs every fifteen minutes as a backstop, and only re-reads what actually changed. CalDAV has no push, so it is polled on that same fifteen-minute schedule and skipped entirely when the calendar tag has not moved.',
            ],
            [
                'q' => 'Is the sync two-way?',
                'a' => 'Yes, and you choose. Out sends events to your calendar. Back brings events from your calendar onto your schedule. Both runs the round trip. Events you create, edit or delete here go out when you save, and events created or edited in your calendar app come back on the return leg. Deletions come back from Google and Outlook, where you decide what they should mean; CalDAV brings back new and changed events only. A change that arrives on the return leg is never echoed straight back out again.',
            ],
            [
                'q' => 'Can attendees add events to their personal calendars?',
                'a' => 'Yes. Event pages carry an Add to Calendar menu for Google Calendar, Apple Calendar and Microsoft Outlook, and no account is needed. The exception is a page already leading with a Register or Buy tickets button, which takes that spot. Either way it is a one-way copy of one event. For somebody who wants everything you do, the schedule also has an iCal feed URL they can subscribe to, which keeps updating as you add and edit events.',
            ],
            [
                'q' => 'What happens if I delete an event in my calendar app?',
                'a' => 'That is a per-schedule setting with three options: keep it here, mark it cancelled here, or delete it here. Cancelling is the reversible one and is the right choice once tickets are sold. If you choose delete, events that have ticket sales or live ad spend are hidden instead of destroyed, so the sales records survive.',
            ],
            [
                'q' => 'Does calendar sync cost anything?',
                'a' => 'No. Google Calendar, Outlook and CalDAV sync are all on the free plan, in both directions, and selfhosted installs get them too. The paid plans are about ticketing, graphics and branding, not about your calendar.',
            ],
        ];

        $dotSections = [
            ['top', 'The ticket'],
            ['out', 'Leg 01: out'],
            ['back', 'Leg 02: back'],
            ['carriers', 'The carriers'],
            ['audience', 'Your audience'],
            ['rest', 'Everything else'],
            ['faq', 'Questions'],
            ['claim', 'Book it'],
        ];
    @endphp

    <div id="es-trip-page" class="es-trip-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the round-trip stub                                 -->
    <!-- ============================================================ -->
    <section id="top" class="es-trip-hero es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(29, 78, 216, 0.22), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(15, 111, 102, 0.18), rgba(15, 111, 102, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-trip-out h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="es-trip-muted text-sm font-medium tracking-wide">Calendar sync, free on every plan</span>
                    </div>

                    <h1 class="es-balance es-trip-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A sync is a round trip.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Not a <span class="es-trip-out">one-way flight.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-trip-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Your events leave for Google Calendar, Outlook or any CalDAV server the moment you save them. Then the edits you make over there come home again. Two legs, one connection, nothing to pay.
                    </p>

                    <div class="es-fade-up es-d-2 mb-10 max-w-md" aria-hidden="true">
                        <div class="es-trip-rail">
                            <div class="es-trip-track es-trip-track-out"></div>
                            <div class="es-trip-track es-trip-track-back"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="es-trip-out text-[0.6rem] font-bold uppercase tracking-[0.2em]">Out</span>
                            <span class="es-trip-back text-[0.6rem] font-bold uppercase tracking-[0.2em]">Back</span>
                        </div>
                    </div>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#back" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the return leg
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-trip-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The stub. A printed ticket, so it does not change with the colour mode. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-trip-stub p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <div>
                                <p class="es-trip-stub-tag mb-1">Round trip &middot; calendar sync</p>
                                <h2 class="es-trip-stub-ink text-lg font-bold">your-schedule.eventschedule.com</h2>
                            </div>
                            <span class="es-trip-stub-pill">Fare: free</span>
                        </div>

                        <div class="es-trip-stub-perf space-y-4 pt-5" aria-hidden="true">
                            <div class="es-trip-stub-leg">
                                <div class="es-trip-stub-marker">Leg 01</div>
                                <div>
                                    <p class="es-trip-stub-out text-sm font-bold">Event Schedule &rarr; your calendar</p>
                                    <div class="es-trip-stub-track es-trip-stub-track-out my-2"></div>
                                    <p class="es-trip-stub-muted text-xs">On save. Created, edited and deleted events all go out.</p>
                                </div>
                            </div>

                            <div class="es-trip-stub-leg">
                                <div class="es-trip-stub-marker">Leg 02</div>
                                <div>
                                    <p class="es-trip-stub-back text-sm font-bold">Your calendar &rarr; Event Schedule</p>
                                    <div class="es-trip-stub-track es-trip-stub-track-back my-2"></div>
                                    <p class="es-trip-stub-muted text-xs">On a push notification, or on the fifteen-minute sweep.</p>
                                </div>
                            </div>
                        </div>

                        <dl class="es-trip-stub-perf mt-5 grid grid-cols-2 gap-x-4 gap-y-3 pt-5 sm:grid-cols-3">
                            <div>
                                <dt class="es-trip-stub-tag">Direction</dt>
                                <dd class="es-trip-stub-code mt-1 text-xs font-semibold">out &middot; back &middot; both</dd>
                            </div>
                            <div>
                                <dt class="es-trip-stub-tag">Carriers</dt>
                                <dd class="es-trip-stub-code mt-1 text-xs font-semibold">Google &middot; Outlook &middot; CalDAV</dd>
                            </div>
                            <div>
                                <dt class="es-trip-stub-tag">If deleted there</dt>
                                <dd class="es-trip-stub-code mt-1 text-xs font-semibold">keep / cancel / delete</dd>
                            </div>
                        </dl>

                        <p class="es-trip-stub-muted mt-5 text-xs">
                            One setting per schedule. Different schedules can fly different carriers and different directions.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Carrier marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @php
                                $marqueeChips = ['Google Calendar', 'Outlook', 'Microsoft 365', 'CalDAV', 'Apple Calendar', 'Nextcloud', 'Fastmail', 'iCal feed', 'Webhooks', 'Teams meetings'];
                            @endphp
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach ($marqueeChips as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-trip-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Leg 01: outbound (fixed-dark band)                        -->
    <!-- ============================================================ -->
    <section id="out" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-trip-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
                <div class="es-trip-rail absolute inset-x-8 bottom-8 opacity-60" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    <div class="es-trip-track es-trip-track-out"></div>
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-trip-leg mb-6" data-reveal><span>Leg 01 &middot; outbound</span></div>
                    <p class="es-trip-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What leaves here</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Save it once. It is <span class="es-trip-lit-out">already gone.</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        The outbound leg is not a nightly job you wait for. It leaves on the save.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-trip-card p-6" data-reveal="panel">
                        <p class="es-trip-tag mb-3">On save</p>
                        <h3 class="mb-2 text-lg font-bold text-white">Create, edit, delete</h3>
                        <p class="es-trip-muted text-sm">All three travel. Add a date and the entry appears; change the time and the same entry moves; delete the event and the entry goes with it. There is no "sync now" button you have to remember.</p>
                    </div>
                    <div class="es-trip-card p-6" data-reveal="panel">
                        <p class="es-trip-tag mb-3">The wording</p>
                        <h3 class="mb-2 text-lg font-bold text-white">Say it your way</h3>
                        <p class="es-trip-muted text-sm">A calendar description template decides what the entry says, using the same variables as event graphics: name, date, time, venue, city, link. Leave it empty and the event description travels as-is.</p>
                    </div>
                    <div class="es-trip-card p-6" data-reveal="panel">
                        <p class="es-trip-tag mb-3">Online events</p>
                        <h3 class="mb-2 text-lg font-bold text-white">A meeting, not just a slot</h3>
                        <p class="es-trip-muted text-sm">Switch it on and an online event with no venue leaves Outlook as a Microsoft Teams meeting. The join link is written back onto the event when it does not already have one, so the calendar entry is the thing people click.</p>
                    </div>
                </div>

                <p class="es-trip-muted mx-auto mt-10 max-w-2xl text-center text-sm" data-reveal>
                    Set the direction to <span class="es-trip-lit-out font-semibold">out</span> and you are done: a publish-only pipe from your schedule to your calendar app, and nothing you do in the calendar app can move your public listings.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Leg 02: the return leg, as a ledger                       -->
    <!-- ============================================================ -->
    <section id="back" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-trip-leg es-trip-leg-in mb-6" data-reveal><span>Leg 02 &middot; return</span></div>
                <p class="es-trip-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What comes back</p>
                <h2 class="es-balance es-trip-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The leg everyone else <span class="es-trip-back">forgets to sell you.</span>
                </h2>
                <p class="es-trip-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Plenty of tools will push a copy of your events into a calendar. The return leg is the one that matters on a Tuesday, when the change happens in the calendar app you already had open.
                </p>
            </div>

            <div class="es-trip-card p-5 sm:p-7" data-reveal="panel">
                <div class="es-trip-ledger-head mb-5">
                    <p class="es-trip-tag">In your calendar app</p>
                    <p aria-hidden="true"></p>
                    <p class="es-trip-tag">On your schedule</p>
                </div>

                <div class="es-trip-ledger">
                    @foreach ($ledger as $row)
                        <div class="es-trip-ledger-row">
                            <div class="es-trip-ledger-cell es-trip-ledger-there">
                                <p class="es-trip-ink text-sm font-bold">{{ $row['there'] }}</p>
                            </div>
                            <div class="es-trip-ledger-arrow" aria-hidden="true">
                                <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </div>
                            <div class="es-trip-ledger-cell es-trip-ledger-here">
                                <p class="es-trip-muted text-sm">{{ $row['here'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="es-trip-muted mt-5 es-trip-rule-t pt-4 text-xs">
                    Loop-safe on purpose: something that arrives on the return leg is not turned round and pushed straight back out again, so a single edit does not ping-pong between the two systems.
                </p>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-trip-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-trip-ink text-lg font-bold">A push, not a wait</h3>
                        <span class="es-trip-plan">Free</span>
                    </div>
                    <p class="es-trip-muted text-sm">Google and Microsoft both notify us the moment something changes on their side, so the return leg normally runs within a minute or two of you closing the dialog.</p>
                </div>
                <div class="es-trip-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-trip-ink text-lg font-bold">Only what changed</h3>
                        <span class="es-trip-plan">Free</span>
                    </div>
                    <p class="es-trip-muted text-sm">Each connection keeps a cursor, so a sync reads the handful of entries that moved instead of the whole year. A CalDAV calendar whose tag has not changed is skipped entirely.</p>
                </div>
                <div class="es-trip-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-trip-ink text-lg font-bold">A sweep behind it</h3>
                        <span class="es-trip-plan">Free</span>
                    </div>
                    <p class="es-trip-muted text-sm">A scheduled sweep runs every fifteen minutes for all three carriers, so a notification that never arrives costs you a quarter of an hour, not a missed show.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The carriers: a real comparison record                    -->
    <!-- ============================================================ -->
    <section id="carriers" class="scroll-mt-24 es-trip-rule-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-trip-leg mb-6" data-reveal><span>Choose a carrier</span></div>
                <h2 class="es-balance es-trip-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three carriers. <span class="es-trip-out">Both legs on all of them.</span>
                </h2>
                <p class="es-trip-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Every column below runs the round trip. The differences are how you sign in, how fast the return leg is, and how much you have to set up if you selfhost.
                </p>
            </div>

            <div class="es-trip-card overflow-x-auto p-2 sm:p-4" data-reveal="panel">
                <table class="es-trip-table text-sm">
                    <caption class="sr-only">Google Calendar, Outlook and CalDAV compared on how you connect, how fast the return leg runs, and what setup a selfhosted install needs</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-trip-tag">Feature</th>
                            <th scope="col">
                                <span class="es-trip-carrier">
                                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                    Google Calendar
                                </span>
                            </th>
                            <th scope="col">
                                <span class="es-trip-carrier">
                                    <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 23 23"><path fill="#f25022" d="M1 1h10v10H1z"/><path fill="#7fba00" d="M12 1h10v10H12z"/><path fill="#00a4ef" d="M1 12h10v10H1z"/><path fill="#ffb900" d="M12 12h10v10H12z"/></svg>
                                    Outlook / Microsoft 365
                                </span>
                            </th>
                            <th scope="col">
                                <span class="es-trip-carrier">
                                    <svg aria-hidden="true" class="es-trip-back h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    CalDAV
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carrierRows as $row)
                            <tr>
                                <th scope="row" class="es-trip-ink font-bold">{{ $row['label'] }}</th>
                                <td class="es-trip-muted">{{ $row['google'] }}</td>
                                <td class="es-trip-muted">{{ $row['microsoft'] }}</td>
                                <td class="es-trip-muted">{{ $row['caldav'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                <a href="{{ marketing_url('/google-calendar') }}" class="es-trip-hover es-trip-card group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                    <span class="es-trip-hover-title es-trip-ink mb-2 font-bold transition-colors">Google Calendar sync</span>
                    <span class="es-trip-muted mb-4 text-sm">One-click sign-in, push notifications, and a per-member calendar setting.</span>
                    <span class="es-trip-hover-arrow es-trip-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-[0.14em] transition-colors">
                        Read more
                        <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>
                <a href="{{ marketing_url('/outlook-calendar') }}" class="es-trip-hover es-trip-card group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                    <span class="es-trip-hover-title es-trip-ink mb-2 font-bold transition-colors">Outlook calendar sync</span>
                    <span class="es-trip-muted mb-4 text-sm">Microsoft 365 over the Graph API, with optional Teams meetings for online events.</span>
                    <span class="es-trip-hover-arrow es-trip-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-[0.14em] transition-colors">
                        Read more
                        <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>
                <a href="{{ marketing_url('/caldav') }}" class="es-trip-hover es-trip-card group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                    <span class="es-trip-hover-title es-trip-ink mb-2 font-bold transition-colors">CalDAV sync</span>
                    <span class="es-trip-muted mb-4 text-sm">The open standard. Point it at your own server: Nextcloud, Fastmail, anything that speaks CalDAV.</span>
                    <span class="es-trip-hover-arrow es-trip-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-[0.14em] transition-colors">
                        Read more
                        <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The audience's leg (fixed-dark band)                      -->
    <!-- ============================================================ -->
    <section id="audience" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-trip-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
                <div class="es-trip-rail absolute inset-x-8 bottom-8 opacity-60" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                    <div class="es-trip-track es-trip-track-out"></div>
                    <div class="es-trip-track es-trip-track-back"></div>
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-trip-leg mb-6" data-reveal><span>Your audience</span></div>
                    <p class="es-trip-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The third leg</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Get on their calendar, <span class="es-trip-lit-out">not just yours.</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        Two ways in, depending on whether somebody wants one night or everything you do.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                    <div class="es-trip-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="text-xl font-bold text-white">One night: add to calendar</h3>
                            <span class="es-trip-plan">Free</span>
                        </div>
                        <p class="es-trip-muted mb-5">Event pages carry an Add to Calendar menu, except where the page is already leading with a Register or Buy tickets button. No account, no sign-up, no app.</p>
                        <div class="mb-5 flex flex-wrap gap-2" aria-hidden="true">
                            <span class="es-trip-chip">Google Calendar</span>
                            <span class="es-trip-chip">Apple Calendar</span>
                            <span class="es-trip-chip">Outlook</span>
                        </div>
                        <p class="es-trip-muted text-sm">Being straight about it: this leg is one way, and it should be. It hands over a copy of one event, so if you later move the start time, their entry does not hear about it. Anybody who wants to stay current wants the feed instead.</p>
                    </div>

                    <div class="es-trip-card p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="text-xl font-bold text-white">Everything: the feed</h3>
                            <span class="es-trip-plan">Free</span>
                        </div>
                        <p class="es-trip-muted mb-5">Your schedule has an iCal feed URL, sitting on the Integrations tab next to an RSS one. Anybody can subscribe to it in Google Calendar, Apple Calendar or Outlook, and their calendar re-reads it on its own.</p>
                        <ul class="es-trip-muted space-y-3 text-sm">
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-trip-lit-back mt-0.5 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>A recurring event arrives as one entry per date for the next ninety days, not as one vague repeating blob.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-trip-lit-back mt-0.5 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Each entry keeps the same identifier every time the feed is re-read, so a subscribed calendar updates the entry it already has rather than collecting duplicates.</span>
                            </li>
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-trip-lit-back mt-0.5 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Only what is actually public travels. Drafts, private events, cancelled events and password-protected events stay off the feed.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    Want the schedule on your own website as well?
                    <a href="{{ marketing_url('/features/embed-calendar') }}" class="es-trip-lit-out inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Embed the calendar
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-trip-leg mb-6" data-reveal><span>The small print</span></div>
                <h2 class="es-balance es-trip-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    The rest of the itinerary.
                </h2>
                <p class="es-trip-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Six things that decide whether a sync survives contact with a real season.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-trip-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-trip-ink text-xl font-bold">Deleted over there is a decision, not an accident</h3>
                                <span class="es-trip-plan">Free</span>
                            </div>
                            <p class="es-trip-muted mb-4">Decide once, per schedule, what a deletion in the connected calendar means here.</p>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="es-trip-hair rounded-lg p-3">
                                    <p class="es-trip-ink text-sm font-bold">Keep it here</p>
                                    <p class="es-trip-muted mt-1 text-xs">The calendar is a working copy. Your listing is the record.</p>
                                </div>
                                <div class="es-trip-hair rounded-lg p-3">
                                    <p class="es-trip-ink text-sm font-bold">Mark it cancelled</p>
                                    <p class="es-trip-muted mt-1 text-xs">Hidden from the public page and reversible. The right answer once tickets exist.</p>
                                </div>
                                <div class="es-trip-hair rounded-lg p-3">
                                    <p class="es-trip-ink text-sm font-bold">Delete it here</p>
                                    <p class="es-trip-muted mt-1 text-xs">Gone. Except where money is involved: events with sales or live ad spend are hidden instead.</p>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-trip-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-trip-ink text-xl font-bold">Which calendar, exactly</h3>
                                <span class="es-trip-plan">Free</span>
                            </div>
                            <p class="es-trip-muted">Your calendar list is read from the account you connected, and you pick one per schedule. Gigs on the work calendar, the choir on another, nothing landing in the wrong place.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-trip-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <h3 class="es-trip-ink mb-4 text-xl font-bold">One schedule, several calendars</h3>
                            <p class="es-trip-muted">Google sync also has a per-member setting, so each person on a schedule can point it at their own calendar instead of sharing the owner's. Worth knowing where the line is: the setting costs nothing, but a schedule with more than one team member is on the Enterprise plan.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-trip-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-trip-ink text-xl font-bold">A recurring night leaves as one entry</h3>
                                <span class="es-trip-plan">Free</span>
                            </div>
                            <p class="es-trip-muted mb-4">A weekly night is one event here: a day-of-week pattern with exceptions for the dates it skips. The outbound leg carries it the same way, as a single calendar entry at the date and time on the event, so your own calendar does not fill up with fifty copies. The feed is the surface that expands the pattern, one entry per date, for anybody who wants every night in their own calendar.</p>
                            <p class="es-trip-muted text-sm">
                                <a href="{{ marketing_url('/features/recurring-events') }}" class="es-trip-link font-semibold hover:underline">How recurring events work</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-trip-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-trip-ink text-xl font-bold">Selfhosted</h3>
                                <span class="es-trip-plan">Free</span>
                            </div>
                            <p class="es-trip-muted">All three carriers work on your own server. Google wants its own API credentials and Outlook wants an Azure app registration; CalDAV wants a URL and nothing else, which is why it is the usual answer for a private install.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-trip-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-trip-ink text-xl font-bold">Turn one leg off whenever you like</h3>
                                <span class="es-trip-plan">Free</span>
                            </div>
                            <p class="es-trip-muted mb-4">Direction is one setting per schedule with four choices: out, back, both, or off. Nothing about connecting a calendar is a one-way door, and disconnecting stops the trip without touching the events either side.</p>
                            <div class="es-trip-rail max-w-sm" aria-hidden="true">
                                <div class="es-trip-track es-trip-track-out"></div>
                                <div class="es-trip-track es-trip-track-back"></div>
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
    <!-- 7. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="es-trip-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-trip-leg mb-6" data-reveal><span>Booking &middot; three counters</span></div>
                <h2 class="es-balance es-trip-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Booking the trip
                </h2>
                <p class="es-trip-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Three settings, once, on the schedule's Integrations tab. Then nothing.
                </p>
            </div>

            <div class="es-trip-stepline mb-5" aria-hidden="true" style="mask-image: linear-gradient(to right, transparent, black 14%, black 86%, transparent);">
                <div class="es-trip-rail">
                    <div class="es-trip-track es-trip-track-out"></div>
                </div>
                <div class="es-trip-stops">
                    <span class="es-trip-stop"></span>
                    <span class="es-trip-stop"></span>
                    <span class="es-trip-stop"></span>
                </div>
            </div>

            @php
                $steps = [
                    ['01', 'Connect the account', 'Sign in to Google or Microsoft once from Account Settings, or type a CalDAV server URL, username and password.'],
                    ['02', 'Pick the calendar and the direction', 'Choose which calendar this schedule uses, then out, back or both. Different schedules can make different choices.'],
                    ['03', 'Decide about deletions', 'On Google and Outlook, say what a deletion in the connected calendar should mean here: keep it, mark it cancelled, or delete it. Then stop thinking about sync.'],
                ];
            @endphp

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ($steps as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-trip-card p-7" data-reveal="panel">
                        <div class="es-trip-stepmark mb-3">Counter {{ $stepNum }}</div>
                        <h3 class="es-trip-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-trip-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="es-trip-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Step by step, with screenshots, in the
                <a href="{{ route('marketing.docs.creating_schedules') }}#integrations-google" class="es-trip-link font-semibold hover:underline">calendar sync guide</a>.
            </p>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 8. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-trip-rule-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-trip-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A day-of-week pattern with exceptions, so a weekly night is one event" :url="marketing_url('/features/recurring-events')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the same schedule on the website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="AI Features" description="Paste a flyer or a listing and have the event filled in for you" :url="marketing_url('/features/ai')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Built-in Analytics" description="Page views, devices and traffic sources, with no third-party trackers" :url="route('marketing.analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-trip-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Popular with                                              -->
    <!-- ============================================================ -->
    <section class="es-trip-rule-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-trip-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Popular with</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="70">
                @php
                    $popular = [
                        ['/for-musicians', 'Musicians', 'Gigs in the calendar you check on the way to the gig.'],
                        ['/for-venues', 'Venues', 'A room diary that agrees with the public listings.'],
                        ['/for-theaters', 'Theaters', 'One event for a whole run, and one feed entry per date.'],
                    ];
                @endphp
                @foreach ($popular as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-trip-hover es-trip-card group flex flex-col p-6 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-trip-hover-title es-trip-ink mb-2 font-bold transition-colors">For {{ $relName }}</span>
                        <span class="es-trip-muted mb-4 text-sm">{{ $relBlurb }}</span>
                        <span class="es-trip-hover-arrow es-trip-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-[0.14em] transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-trip-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 es-trip-rule-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-trip-leg mb-6" data-reveal><span>Before you board</span></div>
                <h2 class="es-balance es-trip-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-trip-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything people ask before they connect a calendar.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-trip-hover es-trip-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-trip-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-trip-out flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-trip-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-trip-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-trip-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-trip-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-trip-rail absolute inset-x-8 bottom-8 opacity-60" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        <div class="es-trip-track es-trip-track-out"></div>
                        <div class="es-trip-track es-trip-track-back"></div>
                    </div>
                </div>
                <div class="relative z-10">
                    <p class="es-trip-tag mb-4">Free on every plan</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Book the round trip. <span class="es-trip-lit-back">Both legs included.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300">
                        Connect Google Calendar, Outlook or any CalDAV server, choose your direction, and stop keeping two calendars in your head.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-trip-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full es-trip-tip px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
