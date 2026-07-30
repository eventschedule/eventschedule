<x-marketing-layout>
    <x-slot name="title">Outlook Calendar Sync & Integration - Event Schedule</x-slot>
    <x-slot name="description">Real-time two-way sync with Outlook and Microsoft 365. OAuth authentication, Microsoft Graph change notifications, and Teams meeting links for smooth event management.</x-slot>
    <x-slot name="breadcrumbTitle">Outlook Calendar</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Outlook Calendar Sync",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Real-time two-way sync with Outlook and Microsoft 365. OAuth authentication, Microsoft Graph change notifications, and Teams meeting links for smooth event management.",
        "featureList": [
            "Two-way Outlook Calendar sync",
            "Microsoft 365 and personal Microsoft account support",
            "OAuth sign-in with Microsoft, with automatic token refresh",
            "Microsoft Graph change notifications, with a fifteen-minute polling fallback",
            "Per-schedule sync direction: to Outlook, from Outlook, both ways or off",
            "Optional Microsoft Teams meetings for online events",
            "Per-schedule policy for events deleted in Outlook",
            "Published iCal feed anyone can subscribe to from Outlook"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free on every plan"
        },
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
           For-outlook-calendar "The Meeting Request" styles.

           THE CONCEPT IS THE DOCUMENT, NOT THE ARROWS. Everyone inside
           Microsoft 365 knows one object cold: the meeting request.
           Subject, When, Where, a body, a sensitivity, sometimes a Teams
           join link. That object is literally what this integration
           builds - MicrosoftCalendarService::buildEventPayload() writes
           subject / start / end / timeZone / location / body /
           sensitivity, and the inbound delta reads the same fields back.
           So the page is laid out AS the request: a header block in the
           hero, a header block again for what arrives, and every section
           eyebrow is a field name.

           ANTI-COLLISION, and this is binding:
           - /features/calendar-sync owns "The Round Trip": an itinerary
             with two LEGS, a printed ticket stub, and blue-outbound /
             teal-return as a two-hue direction code. This page must not
             restate that. So there is NO second hue here at all:
             direction is carried by LABELS and by the mono field names,
             never by colour. One accent, structure does the work.
           - /google-calendar HAS BEEN REBUILT (an earlier note here said
             it was still first-wave; that was stale). It is now "The
             Invitation" and its signature object, .es-invite-note, is
             ALSO a pinned field-by-field card. Two sibling sync pages
             cannot carry the same object, so the separation is: that one
             is the HUMAN card (a calendar entry as a guest would read
             it, blue margin notes naming the source field), this one is
             the WIRE payload (a real Graph endpoint in the header strip,
             a mono field column, and the ANSWER ROW that only Outlook
             has - a meeting request is answered, and the answer here is
             the schedule's sync direction). Do not add margin-source
             notes, a stamp eyebrow, a marquee of chips or a "distribution
             list" section: those are /google-calendar's.
           - Per-member calendar sync is GOOGLE ONLY (Event::
             dispatchCalendarSync only loops getMembersWithCalendarSync()
             for Google), so this page never claims it.

           COLOUR: the page keeps its existing Outlook-blue family, but
           uses the DARKER end of it in light mode. #0078d4 measures only
           4.41 on white and would fail AA as body ink, so light mode
           runs on #005a9e (7.10 on white, 6.44 on the page ground) and
           dark mode on the Windows accent #4cc2ff (9.82 on #080b11).
           NO GRADIENT HEADING TEXT anywhere: a gradient is scored at
           every stop, and a bright Outlook blue stop on a light ground
           is the standard failure in this codebase. Solid accent words
           only.

           NEVER use text-gray-500 on this ground - #6b7280 measures
           about 4.4 on #f1f4f9. Use .es-req-muted (6.96 light,
           7.71 dark).

           FIXED PHYSICAL OBJECTS, pinned and verified with the
           verifier's --bands flag:
           - .es-req-doc, the request itself. A request looks the same
             whichever client opened it, so it renders IDENTICALLY with
             `.dark` on and off. Nothing inside it may use a `dark:`
             utility or a shared class that carries its own `.dark` rule,
             hence its own label / value / note / foot inks. It is a
             white surface, so muted text inside it is measured against
             WHITE (gray-400 on white is 2.43 and is never used here).
           - .es-req-band, the always-dark bands. They re-ink
             .grid-overlay, .animate-shimmer and .es-claim:focus-within
             AFTER the base rules. Deliberately no .es-aurora inside a
             band: the shared rule flips its opacity with the colour mode
             and would break the pin.

           BLADE RULE for this block: never use @supports probes here. A
           "#" hex inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-req-page { background-color: #f1f4f9; color: #0f1622; }
        .dark .es-req-page { background-color: #080b11; color: #e7edf5; }
        .es-req-ink { color: #0f1622; }
        .dark .es-req-ink { color: #e7edf5; }
        .es-req-muted { color: #4a5462; }
        .dark .es-req-muted { color: #98a3b2; }
        .es-req-accent { color: #005a9e; }
        .dark .es-req-accent { color: #4cc2ff; }
        /* Always-lit accent, for the fixed-dark bands in both colour modes. */
        .es-req-lit { color: #4cc2ff; }

        .es-req-rule { border-top: 1px solid rgba(15, 22, 34, 0.1); }
        .dark .es-req-rule { border-top-color: rgba(231, 237, 245, 0.1); }

        /* --- The mono voice: field names, tokens, endpoints --- */
        .es-req-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4a5462;
        }
        .dark .es-req-tag { color: #98a3b2; }

        .es-req-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(15, 22, 34, 0.16);
            background: #ffffff;
            color: #0f1622;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .dark .es-req-mark { border-color: rgba(231, 237, 245, 0.18); background: rgba(231, 237, 245, 0.05); color: #e7edf5; }
        .es-req-mark::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #005a9e;
        }
        .dark .es-req-mark::before { background: #4cc2ff; }

        .es-req-token {
            display: inline-flex;
            align-self: flex-start;
            padding: 0.16rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 90, 158, 0.35);
            color: #005a9e;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .dark .es-req-token { border-color: rgba(76, 194, 255, 0.4); color: #4cc2ff; }

        .es-req-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 22, 34, 0.16);
            background: rgba(255, 255, 255, 0.72);
            color: #4a5462;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .dark .es-req-chip { border-color: rgba(231, 237, 245, 0.16); background: rgba(231, 237, 245, 0.05); color: #b0bac6; }

        /* --- Cards --- */
        .es-req-card {
            border: 1px solid rgba(15, 22, 34, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-req-card { border-color: rgba(231, 237, 245, 0.12); background: rgba(231, 237, 245, 0.04); }

        .es-req-caution {
            border: 1px solid rgba(15, 22, 34, 0.3);
            border-radius: 0.85rem;
            background: rgba(15, 22, 34, 0.04);
        }
        .dark .es-req-caution { border-color: rgba(231, 237, 245, 0.32); background: rgba(231, 237, 245, 0.06); }

        /* ==============================================================
           THE REQUEST. A fixed object: identical with `.dark` on and off.
           ============================================================== */
        .es-req-doc {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(15, 22, 34, 0.14);
            border-radius: 0.95rem;
            background: #ffffff;
            box-shadow: 0 26px 54px -30px rgba(15, 22, 34, 0.5);
        }
        .es-req-doc::before {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            inset-inline-start: 0;
            width: 3px;
            background: #005a9e;
        }
        .es-req-doc-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.4rem 1rem;
            padding: 0.7rem 1.15rem;
            background: #eaf0f7;
            border-bottom: 1px solid rgba(15, 22, 34, 0.12);
        }
        .es-req-doc-kind {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #005a9e;
        }
        .es-req-doc-path {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            color: #4a5462;
            word-break: break-all;
        }
        .es-req-rows { margin: 0; padding: 0.2rem 1.15rem 0.5rem; }
        .es-req-row {
            display: grid;
            gap: 0.1rem 1rem;
            padding: 0.72rem 0;
            border-top: 1px solid rgba(15, 22, 34, 0.09);
        }
        .es-req-row:first-child { border-top: 0; }
        .es-req-label {
            margin: 0;
            padding-top: 0.15rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #5b6572;
        }
        .es-req-value { margin: 0; font-weight: 600; color: #0f1622; }
        .es-req-note { margin: 0.2rem 0 0; font-size: 0.8rem; line-height: 1.5; color: #4a5462; }
        /* The answer row. Only Outlook's object has one: a request is
           answered from a short list, and the schedule's answer IS the sync
           direction. Fixed inks, because it lives inside the pinned doc. */
        .es-req-doc-reply {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem 0.5rem;
            padding: 0.75rem 1.15rem 0.9rem;
            border-top: 1px solid rgba(15, 22, 34, 0.09);
        }
        .es-req-doc-reply-label {
            margin: 0 0.3rem 0 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #5b6572;
        }
        .es-req-answer {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.55rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(15, 22, 34, 0.16);
            background: #ffffff;
            color: #4a5462;
            font-size: 0.72rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .es-req-answer-on {
            border-color: rgba(0, 90, 158, 0.45);
            background: #eef4fa;
            color: #005a9e;
            font-weight: 800;
            box-shadow: inset 0 2px 4px rgba(15, 22, 34, 0.08);
        }
        .es-req-doc-foot {
            padding: 0.85rem 1.15rem;
            background: #f6f9fc;
            border-top: 1px solid rgba(15, 22, 34, 0.1);
            font-size: 0.8rem;
            line-height: 1.55;
            color: #4a5462;
        }
        .es-req-doc-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 90, 158, 0.35);
            color: #005a9e;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        @media (min-width: 640px) {
            .es-req-row { grid-template-columns: 9rem 1fr; }
        }

        /* ==============================================================
           THE RESPONSE STRIP. Outlook answers a request with one of a
           short list; this schedule answers with one of four directions.
           Three of them are the stored values of
           roles.microsoft_sync_direction ('to', 'from', 'both'); the
           fourth is the UNSET default, which the app labels "No sync"
           (role/edit.blade.php radio value=""), so the fourth token here
           says `no sync` rather than inventing an `off` column value.
           ============================================================== */
        .es-req-strip {
            display: grid;
            gap: 1px;
            overflow: hidden;
            border: 1px solid rgba(15, 22, 34, 0.12);
            border-radius: 1rem;
            background: rgba(15, 22, 34, 0.12);
        }
        .dark .es-req-strip { border-color: rgba(231, 237, 245, 0.12); background: rgba(231, 237, 245, 0.12); }
        .es-req-seg {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            padding: 1.15rem 1.1rem;
            background: #ffffff;
        }
        .dark .es-req-seg { background: #0d1218; }
        .es-req-seg-on { background: #eef4fa; box-shadow: inset 0 2px 4px rgba(15, 22, 34, 0.07); }
        .dark .es-req-seg-on { background: #111a22; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5); }
        @media (min-width: 768px) {
            .es-req-strip { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        /* ==============================================================
           THE DELIVERY LADDER. Lives only inside a fixed-dark band, so
           its inks are absolute rather than mode-flipping.
           ============================================================== */
        .es-req-ladder { display: grid; gap: 1.15rem; }
        .es-req-rung {
            display: grid;
            gap: 0.3rem 1.1rem;
            align-items: start;
            padding-inline-start: 0.9rem;
            border-inline-start: 2px solid rgba(76, 194, 255, 0.35);
        }
        .es-req-rung-key {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #4cc2ff;
        }
        .es-req-ping {
            position: relative;
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            margin-inline-end: 0.4rem;
            border-radius: 9999px;
            background: #4cc2ff;
        }
        .es-req-ping::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: #4cc2ff;
            animation: es-req-ping 2.4s ease-out infinite;
        }
        @keyframes es-req-ping {
            0% { transform: scale(1); opacity: 0.7; }
            70%, 100% { transform: scale(3.2); opacity: 0; }
        }
        @media (min-width: 640px) {
            .es-req-rung { grid-template-columns: 7rem 1fr; }
        }

        /* --- Annotated terms, on a mode-flipping card (NOT inside the pinned
               request, which has its own fixed inks). --- */
        .es-req-terms {
            display: grid;
            gap: 0.15rem 1rem;
            padding: 0.7rem 0;
            border-top: 1px solid rgba(15, 22, 34, 0.1);
        }
        .dark .es-req-terms { border-top-color: rgba(231, 237, 245, 0.1); }
        .es-req-terms:first-child { border-top: 0; padding-top: 0; }
        @media (min-width: 640px) {
            .es-req-terms { grid-template-columns: 6rem 1fr; }
        }

        /* --- A plain marked list. Tailwind's preflight strips list markers,
               so the page has to put them back. --- */
        .es-req-list { padding-inline-start: 1.15rem; list-style: disc outside; }

        /* --- Dot-nav tooltip. Its own rule because dark:bg-[#12161d] is an
               arbitrary value that is not in the built bundle. --- */
        .es-req-tip { background: #ffffff; color: #374151; border-color: rgba(15, 22, 34, 0.14); }
        .dark .es-req-tip { background: #12161d; color: #d1d5db; border-color: rgba(231, 237, 245, 0.14); }

        /* --- The record table --- */
        .es-req-table { width: 100%; border-collapse: collapse; }
        .es-req-table th,
        .es-req-table td {
            padding: 0.7rem 0.8rem;
            vertical-align: top;
            text-align: start;
            border-top: 1px solid rgba(15, 22, 34, 0.1);
        }
        .dark .es-req-table th,
        .dark .es-req-table td { border-top-color: rgba(231, 237, 245, 0.1); }
        .es-req-table thead th { border-top: 0; padding-top: 0; }
        .es-req-table tbody th { color: #0f1622; font-weight: 700; }
        .dark .es-req-table tbody th { color: #e7edf5; }
        .es-req-th {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #4a5462;
        }
        .dark .es-req-th { color: #98a3b2; }

        /* --- Plan pills --- */
        .es-req-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(0, 90, 158, 0.4);
            color: #005a9e;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-req-plan { border-color: rgba(76, 194, 255, 0.42); color: #4cc2ff; }
        .es-req-plan-pro { border-color: rgba(15, 22, 34, 0.35); color: #0f1622; }
        .dark .es-req-plan-pro { border-color: rgba(231, 237, 245, 0.38); color: #e7edf5; }

        /* --- Buttons and links --- */
        .es-req-btn {
            background-color: #005a9e;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(0, 90, 158, 0.5);
        }
        .es-req-btn:hover { background-color: #00456f; box-shadow: 0 22px 44px -14px rgba(0, 90, 158, 0.6); }
        .dark .es-req-btn { background-color: #4cc2ff; color: #08111a; }
        .dark .es-req-btn:hover { background-color: #7ad2ff; }

        .es-req-link { color: #005a9e; }
        .es-req-link:hover { color: #0f1622; }
        .dark .es-req-link { color: #4cc2ff; }
        .dark .es-req-link:hover { color: #e7edf5; }

        .es-req-hover:hover { border-color: rgba(0, 90, 158, 0.45); }
        .dark .es-req-hover:hover { border-color: rgba(76, 194, 255, 0.45); }
        .es-req-hover:hover .es-req-hover-title,
        .es-req-hover:hover .es-req-hover-arrow { color: #005a9e; }
        .dark .es-req-hover:hover .es-req-hover-title,
        .dark .es-req-hover:hover .es-req-hover-arrow { color: #4cc2ff; }

        /* ==============================================================
           FIXED-DARK BANDS. Same object in both colour modes, so every
           shared class that carries its own `.dark` rule is re-inked here.
           ============================================================== */
        .es-req-band {
            background-color: #0a0f16;
            background-image: radial-gradient(120% 100% at 50% 0%, #101a25 0%, #0b1219 55%, #05080c 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(231, 237, 245, 0.05);
        }
        .es-req-band .es-req-muted { color: #98a3b2; }
        .es-req-band .es-req-tag { color: #4cc2ff; }
        .es-req-band .es-req-card { border-color: rgba(231, 237, 245, 0.14); background: rgba(231, 237, 245, 0.05); }
        .es-req-band .es-req-mark { border-color: rgba(231, 237, 245, 0.18); background: rgba(231, 237, 245, 0.05); color: #e7edf5; }
        .es-req-band .es-req-mark::before { background: #4cc2ff; }
        .es-req-band .es-req-btn { background-color: #4cc2ff; color: #08111a; }
        .es-req-band .es-req-btn:hover { background-color: #7ad2ff; }
        .es-req-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 237, 245, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 237, 245, 0.05) 1px, transparent 1px);
        }
        .es-req-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-req-band .es-claim:focus-within {
            border-color: rgba(76, 194, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(76, 194, 255, 0.22);
        }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(0, 90, 158, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(76, 194, 255, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(0, 90, 158, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(76, 194, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #005a9e; }
        .dark .es-dot.is-active .es-dot-pip { background: #4cc2ff; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-req-page a:focus-visible,
        #es-req-page summary:focus-visible,
        #es-req-page input:focus-visible,
        #es-req-page button:focus-visible {
            outline: 2px solid #005a9e;
            outline-offset: 3px;
        }
        .dark #es-req-page a:focus-visible,
        .dark #es-req-page summary:focus-visible,
        .dark #es-req-page input:focus-visible,
        .dark #es-req-page button:focus-visible {
            outline-color: #4cc2ff;
        }
        .es-req-band a:focus-visible,
        .es-req-band summary:focus-visible,
        .es-req-band input:focus-visible,
        .es-req-band button:focus-visible {
            outline-color: #4cc2ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-req-ping::after { animation: none !important; }
        }
    </style>

    @php
        // The outbound request, field by field. Each row is a real key in the
        // Graph payload MicrosoftCalendarService::buildEventPayload() builds.
        $outbound = [
            ['Subject', 'Thursday Night Jazz', 'The event name, exactly as you typed it.'],
            ['When', 'Thu 15 Oct 2026, 8:00 PM to 10:00 PM', 'The start time plus the event duration, stamped with the schedule timezone rather than yours.'],
            ['Where', 'The Blue Room, 118 Main Street', 'The venue address on the event.'],
            ['Body', 'Doors at 7:30. Two sets.', 'The event description, or your own calendar description template if you set one.'],
            ['Sensitivity', 'normal', 'Switches to private when the event is unlisted.'],
            ['Online meeting', 'none, this one has a venue', 'A Teams meeting instead, when the event has no venue and the schedule asked for one.'],
        ];

        // The inbound request. Same document, read the other way, from the
        // calendarView/delta response.
        $inbound = [
            ['Subject', 'Sound check with Marla', 'Becomes the event name.'],
            ['When', 'Wed 21 Oct 2026, 4:00 PM to 6:00 PM', 'Start becomes the start, and the length of the item becomes the duration in whole hours.'],
            ['Where', 'The Blue Room', 'Matched against the venues on your schedule, and added as one if it is new.'],
            ['Body', 'Bring the spare cable.', "Outlook's HTML body is converted back to Markdown, and only rewritten when it actually changed."],
        ];

        // The answers a schedule can give. 'to', 'from' and 'both' are the stored
        // values of roles.microsoft_sync_direction; the fourth is the unset default,
        // which the app labels "No sync".
        $directions = [
            ['To Outlook', 'to', 'Events from Event Schedule appear in Outlook. Nothing in Outlook is read.', false],
            ['From Outlook', 'from', 'Events from Outlook appear in Event Schedule. Nothing is written back.', false],
            ['Both ways', 'both', 'Create, edit or delete in either place and the other side follows.', true],
            ['Off', 'no sync', 'The account stays connected, the sync simply does not run. Every schedule starts here, so nothing moves until you answer.', false],
        ];

        // The four conditions on role->microsoft_create_teams_meetings.
        $teamsRules = [
            ['Toggle', 'On, per schedule', 'Off by default. It sits beside the sync direction on the Outlook tab.'],
            ['Event', 'No venue', 'An event with a venue is a room, not a meeting, so nothing is created for it.'],
            ['Account', 'Work or school', 'Personal Microsoft accounts cannot host a Teams meeting. The Outlook item is still created, just without one.'],
            ['Link', 'Yours wins', 'The join link is only saved when the event has no link yet. A link you typed is never overwritten.'],
        ];

        // What the request carries, in both directions.
        $record = [
            ['Subject', 'The event name', 'Renames the event'],
            ['Start and end', 'Start time plus duration, in the schedule timezone', 'Start and length become start and duration'],
            ['All day', 'A whole-day item when the event has no duration', 'A one-day item arrives with no duration, a longer one with its length'],
            ['Location', 'The venue address', 'Matched to a venue, or added as one'],
            ['Body', 'The description, or your calendar template', 'Converted back to Markdown'],
            ['Sensitivity', 'Private for an unlisted event, normal otherwise', 'Not read back'],
            ['Online meeting', 'A Teams meeting when you ask for one, and its join link comes straight back to the event link field while that field is empty', 'Not read back: a Teams link on an item somebody else made stays in Outlook'],
        ];

        // How a change actually gets across, in the order it is tried.
        $ladder = [
            ['push', 'Microsoft Graph says something moved', 'Event Schedule holds a change-notification subscription on the calendar you picked. Outlook posts to it the moment an item changes, and Event Schedule then asks Graph what changed.', true],
            ['60 hrs', 'The subscription has a shelf life', 'Graph subscriptions expire. This one is created for about sixty hours and a nightly job renews it before it lapses, or recreates it if it already has.', false],
            ['15 min', 'A poll, every quarter hour', 'The safety net. A notification that never arrived is not a change that never lands, because the poll comes round anyway.', false],
            ['1 request', 'And the poll is cheap', 'Each schedule keeps a delta token, so the poll asks only for what changed since last time. A calendar with nothing new costs a single request.', false],
        ];

        // roles.calendar_delete_action, shared with the Google and CalDAV tabs.
        $deletions = [
            ['Keep it', 'ignore', 'The event stays exactly as it is. Outlook was tidied, your public calendar was not.'],
            ['Mark it cancelled', 'cancel', 'The event is flagged cancelled, which takes the date off your public calendar but keeps the record.'],
            ['Delete it', 'delete', 'The event is removed from Event Schedule as well.'],
        ];

        $steps = [
            ['01', 'Sign in with Microsoft', 'One OAuth round trip from your account settings. Event Schedule asks for calendar read and write, never sees a password, and keeps a refresh token so the connection does not need redoing.'],
            ['02', 'Pick the calendar and the direction', 'Your Outlook calendars are listed for you. Choose one, choose to, from, both or off, and optionally turn on Teams meetings and a deletion policy.'],
            ['03', 'Save an event', 'Saving, editing or deleting an event goes straight out to Outlook. Anything coming the other way arrives by notification, with the fifteen-minute poll behind it.'],
        ];

        $related = [
            ['/features/calendar-sync', 'Calendar sync', 'The whole two-way story, across every provider.'],
            ['/google-calendar', 'Google Calendar', 'The same integration, on the other big calendar.'],
            ['/caldav', 'CalDAV', 'Apple Calendar, Fastmail, Nextcloud and anything else that speaks CalDAV.'],
            ['/features/online-events', 'Online events', 'What the event link field is for, Teams or otherwise.'],
        ];

        $faqs = [
            [
                'q' => 'Is two-way Outlook sync available on the free plan?',
                'a' => 'Yes. Outlook Calendar sync is free on every plan, in both directions, including the optional Teams meetings and the deletion policy. There is no calendar feature behind the paywall.',
            ],
            [
                'q' => 'Which direction does it sync?',
                'a' => 'Whichever you choose, per schedule. To Outlook pushes your events out. From Outlook pulls Outlook items in. Both ways does both, so a change in either place reaches the other. Off leaves the account connected but stops the sync.',
            ],
            [
                'q' => 'How quickly do changes sync?',
                'a' => 'Near real time. Event Schedule holds a Microsoft Graph change-notification subscription on your calendar, so Outlook pushes the news as it happens. Behind that sits a poll every fifteen minutes, so a notification that goes missing does not cost you the change. The subscription itself lasts about sixty hours and is renewed nightly.',
            ],
            [
                'q' => 'Does it work with personal Microsoft accounts and work accounts?',
                'a' => 'Both. Personal Microsoft accounts such as Outlook.com, Hotmail and Live work, and so do Microsoft 365 work or school accounts. The one difference is Teams: personal accounts cannot host a Teams meeting, so the event is still created in Outlook, just without one attached.',
            ],
            [
                'q' => 'What happens if I delete an event in Outlook?',
                'a' => 'That is your call, set once per schedule: keep the event, mark it cancelled so the date leaves your public calendar, or delete it here too. The setting is shared with Google and CalDAV inbound sync. Event Schedule also checks why Graph reported the event as gone, and only applies the policy to a real deletion, so an event that merely moved out of the sync window is left alone.',
            ],
            [
                'q' => 'Can I sync a single event rather than the whole schedule?',
                'a' => 'Yes, once the schedule is set to send events out (to Outlook, or both ways). Each event then carries its own Outlook Calendar panel in the editor: sync just that one, or remove it from Outlook again. The schedule-level direction covers everything else.',
            ],
            [
                'q' => 'Do recurring events send every date to Outlook?',
                'a' => 'No. Outlook sync works at the event level, so a recurring event goes across as one Outlook item rather than one per date. If you want every date in a calendar, use the published iCal feed or the per-date iCal download, which do expand recurring events.',
            ],
            [
                'q' => 'Does Outlook Calendar sync work with selfhosted Event Schedule?',
                'a' => 'Yes. Register your own application in Microsoft Entra, then set the client ID, client secret, redirect URI, tenant and webhook secret in your environment file. The selfhost guide walks through the app registration, the permission scope and the webhook endpoint.',
            ],
        ];

        $dotSections = [
            ['top', 'The request'],
            ['record', 'One record'],
            ['direction', 'Direction'],
            ['fields', 'The fields'],
            ['teams', 'Teams'],
            ['delivery', 'Delivery'],
            ['cancelled', 'Cancelled'],
            ['inbound', 'Inbound'],
            ['feed', 'The feed'],
            ['how-it-works', 'Connecting'],
            ['faq', 'Questions'],
            ['claim', 'Connect'],
        ];
    @endphp

    <div id="es-req-page" class="es-req-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the request itself                                  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 68%, rgba(0, 90, 158, 0.28), rgba(0, 90, 158, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 30%, rgba(76, 194, 255, 0.18), rgba(76, 194, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#f25022" d="M1 1h10v10H1z"/>
                            <path fill="#7fba00" d="M12 1h10v10H12z"/>
                            <path fill="#00a4ef" d="M1 12h10v10H1z"/>
                            <path fill="#ffb900" d="M12 12h10v10H12z"/>
                        </svg>
                        <span class="es-req-muted text-sm font-medium tracking-wide">Microsoft 365 and Outlook</span>
                    </div>

                    <h1 class="es-balance es-req-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Every event you publish</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">is a <span class="es-req-accent">meeting request.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-req-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Subject, when, where, a body, sometimes a Teams link. Outlook already has a format for this, and Event Schedule writes it when you save an event and reads it when you edit one in Outlook.
                    </p>

                    <div class="es-fade-up es-d-3 mb-8 flex flex-wrap gap-2">
                        <span class="es-req-chip">Free on every plan</span>
                        <span class="es-req-chip">Two ways, or one</span>
                        <span class="es-req-chip">Teams optional</span>
                        <span class="es-req-chip">Selfhost supported</span>
                    </div>

                    <div class="es-fade-up es-d-4 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#how-it-works" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See how it connects
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-req-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The request. A fixed object: identical in light and dark. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-req-doc">
                        <div class="es-req-doc-head">
                            <span class="es-req-doc-kind">Meeting request</span>
                            <span class="es-req-doc-path">POST /me/calendars/{id}/events</span>
                        </div>
                        <dl class="es-req-rows">
                            @foreach ($outbound as [$field, $value, $note])
                                <div class="es-req-row">
                                    <dt class="es-req-label">{{ $field }}</dt>
                                    <dd class="es-req-value">
                                        {{ $value }}
                                        <p class="es-req-note">{{ $note }}</p>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                        {{-- The answer row. A meeting request is answered from a short list,
                             and the schedule's answer is the sync direction. --}}
                        <div class="es-req-doc-reply">
                            <p class="es-req-doc-reply-label">Answer</p>
                            @foreach ($directions as [$dirName, $dirToken, $dirBody, $dirOn])
                                <span class="es-req-answer @if ($dirOn) es-req-answer-on @endif">{{ $dirName }}@if ($dirOn)<span class="sr-only"> (chosen on this schedule)</span>@endif</span>
                            @endforeach
                        </div>
                        <p class="es-req-doc-foot">
                            <span class="es-req-doc-pill">One record</span>
                            One event in Event Schedule, one item on your Outlook calendar, and a mapping row that remembers they are the same thing. Every schedule starts unanswered, so nothing moves until you pick a direction.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. One record, two clients (fixed-dark band)                 -->
    <!-- ============================================================ -->
    <section id="record" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-req-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Record</span></div>
                    <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The unit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Not two calendars. <span class="es-req-lit">One record, two clients.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-req-card p-6" data-reveal="panel">
                        <p class="es-req-tag mb-3">Here</p>
                        <h3 class="mb-2 text-lg font-bold text-white">The event</h3>
                        <p class="es-req-muted text-sm">Name, date, duration, venue, description, visibility, tickets. The thing your audience sees on your public page.</p>
                    </div>
                    <div class="es-req-card p-6" data-reveal="panel">
                        <p class="es-req-tag mb-3">There</p>
                        <h3 class="mb-2 text-lg font-bold text-white">The calendar item</h3>
                        <p class="es-req-muted text-sm">One item on the Outlook calendar you picked, in the schedule timezone, marked private when the event is unlisted.</p>
                    </div>
                    <div class="es-req-card p-6" data-reveal="panel">
                        <p class="es-req-tag mb-3">Between</p>
                        <h3 class="mb-2 text-lg font-bold text-white">The mapping</h3>
                        <p class="es-req-muted text-sm">A row tying the Outlook item back to the event. It is the reason a second save is an edit rather than a duplicate.</p>
                    </div>
                </div>

                <p class="es-req-muted mx-auto mt-10 max-w-2xl text-center" data-reveal>
                    Everything below is that record, read from one side or the other.
                    <a href="#fields" class="es-req-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        See the fields
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The response strip: sync direction                        -->
    <!-- ============================================================ -->
    <section id="direction" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Response</span></div>
                <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Sync direction</p>
                <h2 class="es-balance es-req-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A request gets <span class="es-req-accent">one answer.</span>
                </h2>
                <p class="es-req-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Outlook answers an invitation with one choice from a short list. The answer row on the request at the top of this page is that choice, and these four are the whole list.
                </p>
            </div>

            <div class="es-req-strip" data-reveal="panel">
                @foreach ($directions as [$dirName, $dirToken, $dirBody, $dirOn])
                    <div class="es-req-seg @if ($dirOn) es-req-seg-on @endif">
                        <span class="es-req-token">{{ $dirToken }}</span>
                        <h3 class="es-req-ink text-lg font-bold">{{ $dirName }}</h3>
                        <p class="es-req-muted text-sm">{{ $dirBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-req-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-req-ink text-lg font-bold">One account, per schedule</h3>
                        <span class="es-req-plan">Free</span>
                    </div>
                    <p class="es-req-muted text-sm">The schedule owner connects the Microsoft account, and the sync runs on that account. The calendar and the direction belong to the schedule, so a second schedule can point at a different Outlook calendar entirely.</p>
                </div>
                <div class="es-req-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-req-ink text-lg font-bold">Or one event by hand</h3>
                        <span class="es-req-plan">Free</span>
                    </div>
                    <p class="es-req-muted text-sm">Answer to Outlook or both ways and every event also carries its own Outlook panel in the editor. Push a single event across from there on demand, or take that one back out of Outlook, without changing what the schedule does with the rest.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What is in the request                                    -->
    <!-- ============================================================ -->
    <section id="fields" class="es-req-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Fields</span></div>
                <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The payload</p>
                <h2 class="es-balance es-req-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Field by field, <span class="es-req-accent">both ways.</span>
                </h2>
                <p class="es-req-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    No mystery mapping. This is the request Event Schedule sends, and what it takes back out of one that arrives.
                </p>
            </div>

            <div class="es-req-card overflow-x-auto p-5 sm:p-7" data-reveal="panel">
                <table class="es-req-table">
                    <caption class="sr-only">How each meeting request field maps to an Event Schedule event, outbound and inbound</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-req-th">Request field</th>
                            <th scope="col" class="es-req-th">Sent to Outlook</th>
                            <th scope="col" class="es-req-th">Read back in</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($record as [$rField, $rOut, $rIn])
                            <tr>
                                <th scope="row" class="text-sm">{{ $rField }}</th>
                                <td class="es-req-muted text-sm">{{ $rOut }}</td>
                                <td class="es-req-muted text-sm">{{ $rIn }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="es-req-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Sync is per event, not per date: a recurring event crosses as one Outlook item. Every individual date is covered by the published feed further down this page.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Teams                                                     -->
    <!-- ============================================================ -->
    <section id="teams" class="es-req-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Online meeting</span></div>
                    <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Microsoft Teams</p>
                    <h2 class="es-balance es-req-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        The one platform we <span class="es-req-accent">name.</span>
                    </h2>
                    <p class="es-req-muted mb-5 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        An online event in Event Schedule carries a single link field, and that is deliberate: there are no streaming integrations to configure and nothing to break when a platform changes its API. Teams is the exception, and it arrives through this sync rather than through a settings page of its own.
                    </p>
                    <p class="es-req-muted" data-reveal style="--reveal-delay: 0.2s;">
                        Turn the toggle on and an online event synced to Outlook from a work or school account gets a Teams meeting created with it. The join link is written into the event link field while that field is still empty, so your public page has it too.
                        <a href="{{ marketing_url('/features/online-events') }}" class="es-req-link font-medium hover:underline">How online events work</a>
                    </p>
                </div>

                <div class="es-req-card p-7" data-reveal="panel">
                    <div class="mb-5 flex flex-wrap items-center gap-2">
                        <h3 class="es-req-ink text-lg font-bold">The exact conditions</h3>
                        <span class="es-req-plan">Free</span>
                    </div>
                    <dl>
                        @foreach ($teamsRules as [$tKey, $tValue, $tNote])
                            <div class="es-req-terms">
                                <dt class="es-req-tag">{{ $tKey }}</dt>
                                <dd class="m-0">
                                    <span class="es-req-ink font-semibold">{{ $tValue }}</span>
                                    <p class="es-req-muted mt-1 text-sm leading-relaxed">{{ $tNote }}</p>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Delivery ladder (fixed-dark band)                         -->
    <!-- ============================================================ -->
    <section id="delivery" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-req-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Delivery</span></div>
                    <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Microsoft Graph</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Four things stand between a change and <span class="es-req-lit">a missed change.</span>
                    </h2>
                </div>

                <div class="es-req-ladder" data-reveal-group="110">
                    @foreach ($ladder as [$lKey, $lTitle, $lBody, $lPing])
                        <div class="es-req-rung" data-reveal>
                            <p class="es-req-rung-key">
                                @if ($lPing)<span class="es-req-ping" aria-hidden="true"></span>@endif

                                {{ $lKey }}
                            </p>
                            <div>
                                <h3 class="mb-1 text-lg font-bold text-white">{{ $lTitle }}</h3>
                                <p class="es-req-muted text-sm leading-relaxed">{{ $lBody }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="es-req-muted mx-auto mt-10 max-w-2xl text-center text-sm" data-reveal>
                    Push first, poll behind it, and a token so the poll costs almost nothing. Near real time when the notification lands, and correct either way.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Cancelled                                                 -->
    <!-- ============================================================ -->
    <section id="cancelled" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Cancelled</span></div>
                <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Deleted in Outlook</p>
                <h2 class="es-balance es-req-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Somebody clears their calendar. <span class="es-req-accent">Then what?</span>
                </h2>
                <p class="es-req-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A deletion in Outlook is not automatically a decision about your public calendar, so you say once what it should mean.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($deletions as [$dTitle, $dToken, $dBody])
                    <div class="es-req-card flex flex-col p-7" data-reveal="panel">
                        <span class="es-req-token mb-4">{{ $dToken }}</span>
                        <h3 class="es-req-ink mb-2 text-lg font-bold">{{ $dTitle }}</h3>
                        <p class="es-req-muted text-sm">{{ $dBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-req-caution p-6" data-reveal="panel">
                    <p class="es-req-tag mb-3">Caution</p>
                    <p class="es-req-muted text-sm">Delete means delete. The other two choices are reversible; that one removes the event from Event Schedule, so the setting is worth reading twice before you pick it.</p>
                </div>
                <div class="es-req-card p-6" data-reveal="panel">
                    <p class="es-req-tag mb-3">A quieter guard</p>
                    <p class="es-req-muted text-sm">Graph also reports an item as gone when it simply moves out of the window being watched. Event Schedule checks the reason and applies your policy only to a genuine deletion, so a show pushed out to next spring is not quietly taken down.</p>
                </div>
            </div>

            <p class="es-req-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                One setting, shared with Google and CalDAV inbound sync, because it is a decision about your calendar rather than about a provider.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. What arrives from Outlook                                 -->
    <!-- ============================================================ -->
    <section id="inbound" class="es-req-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Inbound</span></div>
                    <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">From Outlook</p>
                    <h2 class="es-balance es-req-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        The same document, <span class="es-req-accent">read the other way.</span>
                    </h2>
                    <p class="es-req-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        An item added in Outlook becomes a real event here, not a shadow copy. It is accepted on the schedule the moment it lands, named from the subject, given a URL from your own event URL pattern, and filed under your default category if you set one.
                    </p>
                    <div class="es-req-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-req-ink text-base font-bold">Bookings are not overwritten</h3>
                            <span class="es-req-plan es-req-plan-pro">Pro</span>
                        </div>
                        <p class="es-req-muted text-sm">An appointment booked on your booking page also lands on your Outlook calendar, but the calendar never writes back over it. A rescheduled booking cannot be dragged back to its old time by a stale sync.</p>
                    </div>
                </div>

                <!-- The request again, arriving. Same fixed object. -->
                <div data-reveal>
                    <div class="es-req-doc">
                        <div class="es-req-doc-head">
                            <span class="es-req-doc-kind">Meeting request</span>
                            <span class="es-req-doc-path">GET /me/calendarView/delta</span>
                        </div>
                        <dl class="es-req-rows">
                            @foreach ($inbound as [$field, $value, $note])
                                <div class="es-req-row">
                                    <dt class="es-req-label">{{ $field }}</dt>
                                    <dd class="es-req-value">
                                        {{ $value }}
                                        <p class="es-req-note">{{ $note }}</p>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                        <p class="es-req-doc-foot">
                            <span class="es-req-doc-pill">Delta</span>
                            The delta response only carries what changed, and the token that says where to resume next time.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. The standing invitation: the published feed               -->
    <!-- ============================================================ -->
    <section id="feed" class="es-req-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Subscribe</span></div>
                    <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The other direction</p>
                    <h2 class="es-balance es-req-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        A standing invitation, for <span class="es-req-accent">everyone else.</span>
                    </h2>
                    <p class="es-req-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Graph sync connects your own account. Your audience does not need an account at all: every schedule publishes an iCal feed, and Outlook can subscribe to it from the web like any published internet calendar.
                    </p>
                    <ul class="es-req-list es-req-muted space-y-3 text-sm" data-reveal style="--reveal-delay: 0.2s;">
                        <li>Public events only. Drafts, unlisted events, cancelled events and password-protected events stay out of it.</li>
                        <li>Recurring events are expanded, one entry per date, ninety days ahead.</li>
                        <li>Each event also has its own iCal download, per date, for the person who wants one show and not the season.</li>
                    </ul>
                </div>

                <div class="es-req-doc" data-reveal="panel">
                    <div class="es-req-doc-head">
                        <span class="es-req-doc-kind">Published calendar</span>
                        <span class="es-req-doc-path">text/calendar</span>
                    </div>
                    <dl class="es-req-rows">
                        <div class="es-req-row">
                            <dt class="es-req-label">Feed URL</dt>
                            <dd class="es-req-value">your-schedule.eventschedule.com/feed/ical
                                <p class="es-req-note">Copyable from your schedule settings, alongside an RSS version of the same listing.</p>
                            </dd>
                        </div>
                        <div class="es-req-row">
                            <dt class="es-req-label">Calendar name</dt>
                            <dd class="es-req-value">Your schedule name
                                <p class="es-req-note">Carried in the feed with your timezone, so it lands correctly labelled in the subscriber's client.</p>
                            </dd>
                        </div>
                        <div class="es-req-row">
                            <dt class="es-req-label">Contents</dt>
                            <dd class="es-req-value">Upcoming public dates
                                <p class="es-req-note">One entry per date, each with a stable identifier so a client updates the existing entry instead of adding a second one.</p>
                            </dd>
                        </div>
                    </dl>
                    <p class="es-req-doc-foot">
                        <span class="es-req-doc-pill">Free</span>
                        Feeds, the iCal downloads and the embeddable calendar are all on the free plan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Connecting it                                            -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="es-req-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Setup</span></div>
                <p class="es-req-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Three steps</p>
                <h2 class="es-balance es-req-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Connect once. <span class="es-req-accent">Then forget it.</span>
                </h2>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                @foreach ($steps as [$sNum, $sTitle, $sBody])
                    <div class="es-req-card p-7" data-reveal="panel">
                        <div class="es-req-accent mb-3 font-mono text-2xl font-black">{{ $sNum }}</div>
                        <h3 class="es-req-ink mb-2 text-lg font-bold">{{ $sTitle }}</h3>
                        <p class="es-req-muted text-sm leading-relaxed">{{ $sBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-req-card mt-6 p-7" data-reveal="panel">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <h3 class="es-req-ink text-lg font-bold">Selfhosting it</h3>
                    <span class="es-req-token">.env</span>
                </div>
                <p class="es-req-muted mb-4 text-sm leading-relaxed">
                    On your own server, the integration runs on your own Microsoft Entra application rather than ours: register the app, then set the client ID, client secret, redirect URI, tenant and webhook secret. Nothing calls out to us, and the change notifications come to your own domain.
                </p>
                <a href="{{ marketing_url('/docs/selfhost/microsoft-calendar') }}" class="es-req-link inline-flex items-center gap-2 font-medium hover:underline">
                    Read the selfhost setup guide
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-req-rule py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-req-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($related as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-req-hover es-req-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-req-hover-title es-req-ink mb-2 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-req-muted mb-4 text-xs leading-relaxed">{{ $relBlurb }}</span>
                        <span class="es-req-hover-arrow es-req-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
                <a href="{{ marketing_url('/features/integrations') }}" class="es-req-link inline-flex items-center font-medium hover:underline">
                    See all integrations
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/features/ticketing') }}" class="es-req-link inline-flex items-center font-medium hover:underline">
                    Ticketing
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <section id="faq" class="es-req-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-req-mark mb-6" data-reveal aria-hidden="true"><span>Questions</span></div>
                <h2 class="es-balance es-req-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-req-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people in Microsoft 365 ask before they connect it.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-req-hover es-req-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-req-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-req-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-req-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-req-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-req-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-req-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-req-tag mb-4">Free on every plan</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Send the first request. <span class="es-req-lit">Both ways.</span>
                    </h2>
                    <p class="es-req-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Pick a name, connect Microsoft, choose a direction. Two-way Outlook sync, Teams meetings and the published feed cost nothing on any plan.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-req-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-req-muted mt-6 text-sm">No credit card required</p>
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
                        <span class="es-req-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
