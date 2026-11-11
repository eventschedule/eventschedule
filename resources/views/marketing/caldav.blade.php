<x-marketing-layout :hp="true">
    <x-slot name="title">CalDAV Calendar Sync for iCloud, Nextcloud & Fastmail</x-slot>
    <x-slot name="description">Two-way CalDAV sync with iCloud, Nextcloud, Fastmail or any CalDAV server, free on every plan. Server URLs, app passwords, CalDAV vs ICS, and error fixes.</x-slot>
    <x-slot name="breadcrumbTitle">CalDAV</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule - CalDAV Sync"
        description="What CalDAV is and how it differs from an ICS subscription, which apps speak it, the server URL and app password for iCloud, Fastmail and Nextcloud, and fixes for common errors." />
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
           For-caldav "The Open Protocol" styles.

           THE CONCEPT IS THE PUBLISHED STANDARD, NOT THE ARROWS. CalDAV
           is not a partnership, an API key or an integration someone
           granted us. It is a document anybody can read, and this
           integration is an implementation of it: CalDAVService speaks
           PROPFIND, REPORT, PUT and DELETE against a URL you type in,
           with Sabre\VObject writing plain iCalendar. So the page is
           laid out AS a specification. A pinned spec sheet in the hero,
           mono section marks numbered like clauses, a wire trace of the
           six requests the client actually makes, a conformance table,
           and a "Security Considerations" section, because every RFC
           has one. The metaphor and the feature story are the same
           sentence: nobody owns the protocol, which is why the same code
           reaches a server we have never heard of.

           ANTI-COLLISION, and this is binding:
           - /outlook-calendar owns "The Meeting Request": a pinned
             white document with label/value rows, a four-up response
             strip, and a delivery ladder. This page must NOT restate
             that shape. Its pinned object is a SPEC SHEET, keyed on
             clause names rather than message fields; its mid-page
             moment is a WIRE TRACE of HTTP verbs, which Outlook's page
             deliberately never shows because Graph hides them; and its
             record table is keyed on iCalendar PROPERTY names, not on
             message fields.
           - /features/calendar-sync owns "The Round Trip" (an itinerary
             with two legs and a two-hue direction code). Direction here
             is carried by mono enum VALUES (to / from / both / off),
             never by a second hue.
           - /google-calendar owns "The Invitation".
           - There is exactly ONE accent hue on this page. No gradient
             heading text anywhere: a gradient is scored at every stop
             and a bright teal stop on a light ground is the standard
             failure in this codebase.

           COLOUR, measured not guessed. The page keeps its existing
           teal family and runs the DARK end of it in light mode.
           Page ground #f1f5f4 (L 0.9046) and #07100f (L 0.0046).
             accent  #115e59  6.92 on the light ground, 7.59 on white
             lit     #2dd4bf  10.55 on the band, 10.34 on the dark ground
             muted   #46534f  7.20 on the light ground
             muted   #9aaba7  8.03 on the dark ground, 7.5 on the band top
             sheet muted #4d5a56  7.22 on the sheet's white
           NEVER text-gray-500 on this ground: #6b7280 measures about
           4.4 on #f1f5f4. Use .es-proto-muted.

           FIXED PHYSICAL OBJECTS, pinned and verified with the
           verifier's --bands flag:
           - .es-proto-sheet, the specification. A printed standard
             reads the same whoever opened it, so it renders IDENTICALLY
             with `.dark` on and off. Nothing inside it may use a `dark:`
             utility or a shared class that carries its own `.dark` rule
             (no glass, no aurora, no shimmer), hence its own key / value
             / gloss inks. It is a white surface, so muted text inside is
             measured against WHITE: gray-400 on white is 2.43 and is
             never used here.
           - .es-proto-band, the always-dark bands. They re-ink
             .grid-overlay, .animate-shimmer and .es-claim:focus-within
             AFTER the base rules, because those shared classes flip
             themselves in dark mode.

           BLADE RULE for this block: never use @supports probes here. A
           "#" hex inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-proto-page { background-color: #f1f5f4; color: #101c1a; }
        .dark .es-proto-page { background-color: #07100f; color: #e7efec; }
        .es-proto-ink { color: #101c1a; }
        .dark .es-proto-ink { color: #e7efec; }
        .es-proto-muted { color: #46534f; }
        .dark .es-proto-muted { color: #9aaba7; }
        .es-proto-accent { color: #115e59; }
        .dark .es-proto-accent { color: #2dd4bf; }
        /* Always-lit accent, for the fixed-dark bands in both colour modes. */
        .es-proto-lit { color: #2dd4bf; }

        .es-proto-rule { border-top: 1px solid rgba(16, 28, 26, 0.1); }
        .dark .es-proto-rule { border-top-color: rgba(231, 239, 236, 0.1); }

        /* --- The mono voice: clause names, properties, methods --- */
        .es-proto-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #46534f;
        }
        .dark .es-proto-tag { color: #9aaba7; }

        /* A section mark, numbered like a clause in a spec. The rule under
           it is the only ornament: an abstract stroke, not a drawing. */
        .es-proto-sec {
            display: inline-flex;
            align-items: baseline;
            gap: 0.55rem;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid #115e59;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #101c1a;
        }
        .dark .es-proto-sec { border-bottom-color: #2dd4bf; color: #e7efec; }

        .es-proto-token {
            display: inline-flex;
            align-self: flex-start;
            padding: 0.16rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(17, 94, 89, 0.35);
            color: #115e59;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            white-space: nowrap;
        }
        .dark .es-proto-token { border-color: rgba(45, 212, 191, 0.4); color: #2dd4bf; }

        .es-proto-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 28, 26, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #46534f;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.04em;
        }
        .dark .es-proto-chip { border-color: rgba(231, 239, 236, 0.16); background: rgba(231, 239, 236, 0.05); color: #b3c1bd; }

        /* --- Cards --- */
        .es-proto-card {
            border: 1px solid rgba(16, 28, 26, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-proto-card { border-color: rgba(231, 239, 236, 0.12); background: rgba(231, 239, 236, 0.04); }

        .es-proto-caution {
            border: 1px solid rgba(16, 28, 26, 0.3);
            border-radius: 0.85rem;
            background: rgba(16, 28, 26, 0.04);
        }
        .dark .es-proto-caution { border-color: rgba(231, 239, 236, 0.32); background: rgba(231, 239, 236, 0.06); }

        /* ==============================================================
           THE SPECIFICATION. A fixed object: identical with `.dark` on
           and off, because a printed standard does not have a night
           edition.
           ============================================================== */
        .es-proto-sheet {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(16, 28, 26, 0.14);
            border-radius: 0.6rem;
            background: #ffffff;
            box-shadow: 0 26px 54px -30px rgba(16, 28, 26, 0.5);
        }
        .es-proto-sheet-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.35rem 1rem;
            padding: 0.75rem 1.15rem;
            background: #eef2f1;
            border-bottom: 2px solid #115e59;
        }
        .es-proto-sheet-kind {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #115e59;
        }
        .es-proto-sheet-id {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            color: #4d5a56;
        }
        .es-proto-sheet-rows { margin: 0; padding: 0.2rem 1.15rem 0.5rem; }
        .es-proto-sheet-row {
            display: grid;
            gap: 0.1rem 1rem;
            padding: 0.7rem 0;
            border-top: 1px solid rgba(16, 28, 26, 0.09);
        }
        .es-proto-sheet-row:first-child { border-top: 0; }
        .es-proto-sheet-key {
            margin: 0;
            padding-top: 0.15rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #5a6763;
        }
        .es-proto-sheet-val { margin: 0; font-weight: 600; color: #101c1a; }
        .es-proto-sheet-gloss { margin: 0.2rem 0 0; font-size: 0.8rem; line-height: 1.5; color: #4d5a56; }
        .es-proto-sheet-foot {
            padding: 0.85rem 1.15rem;
            background: #f5f8f7;
            border-top: 1px solid rgba(16, 28, 26, 0.1);
            font-size: 0.8rem;
            line-height: 1.55;
            color: #4d5a56;
        }
        .es-proto-sheet-stamp {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.15rem 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(17, 94, 89, 0.35);
            color: #115e59;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        @media (min-width: 640px) {
            .es-proto-sheet-row { grid-template-columns: 8rem 1fr; }
        }

        /* ==============================================================
           THE ENUM STRIP. Four cells because roles.caldav_sync_direction
           has exactly four states, one of them being unset.
           ============================================================== */
        .es-proto-strip {
            display: grid;
            gap: 1px;
            overflow: hidden;
            border: 1px solid rgba(16, 28, 26, 0.12);
            border-radius: 1rem;
            background: rgba(16, 28, 26, 0.12);
        }
        .dark .es-proto-strip { border-color: rgba(231, 239, 236, 0.12); background: rgba(231, 239, 236, 0.12); }
        .es-proto-cell {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            padding: 1.15rem 1.1rem;
            background: #ffffff;
        }
        .dark .es-proto-cell { background: #0b1413; }
        .es-proto-cell-on { background: #eaf2f0; box-shadow: inset 0 2px 4px rgba(16, 28, 26, 0.07); }
        .dark .es-proto-cell-on { background: #101d1b; box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.5); }
        @media (min-width: 768px) {
            .es-proto-strip { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        /* ==============================================================
           THE WIRE TRACE. Lives only inside a fixed-dark band, so its
           inks are absolute rather than mode-flipping.
           ============================================================== */
        .es-proto-trace { display: grid; gap: 1.4rem; }
        /* Exactly TWO grid items per step, so nothing can wrap into the narrow
           first track. The hop number and the method chip share cell one. */
        .es-proto-step {
            display: grid;
            gap: 0.5rem 1.25rem;
            align-items: start;
            padding-inline-start: 1.1rem;
            border-inline-start: 2px solid rgba(45, 212, 191, 0.3);
        }
        .es-proto-hop { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.4rem 0.55rem; }
        .es-proto-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: rgba(154, 171, 167, 0.8);
        }
        .es-proto-verb {
            display: inline-flex;
            align-items: center;
            align-self: flex-start;
            padding: 0.2rem 0.55rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(45, 212, 191, 0.4);
            background: rgba(45, 212, 191, 0.08);
            color: #2dd4bf;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
        }
        .es-proto-path {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            color: #9aaba7;
            overflow-wrap: anywhere;
        }
        .es-proto-pip {
            position: relative;
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            margin-inline-end: 0.45rem;
            border-radius: 9999px;
            background: #2dd4bf;
        }
        .es-proto-pip::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            background: #2dd4bf;
            animation: es-proto-ping 2.6s ease-out infinite;
        }
        @keyframes es-proto-ping {
            0% { transform: scale(1); opacity: 0.7; }
            70%, 100% { transform: scale(3.2); opacity: 0; }
        }
        @media (min-width: 768px) {
            .es-proto-step { grid-template-columns: 8.5rem 1fr; }
            .es-proto-hop { flex-direction: column; align-items: flex-start; }
        }

        /* ==============================================================
           THE CONFORMANCE STATEMENT. The last clause of the document, and
           the bookend for the hero sheet: the same key / value voice,
           closing on the same OWNER row. It lives inside the fixed-dark
           finale band, so its inks are absolute rather than
           mode-flipping, or the band would stop being one object.
           ============================================================== */
        .es-proto-close {
            display: grid;
            gap: 1px;
            overflow: hidden;
            margin-inline: auto;
            max-width: 46rem;
            border: 1px solid rgba(45, 212, 191, 0.22);
            border-radius: 0.9rem;
            background: rgba(45, 212, 191, 0.22);
            text-align: start;
        }
        .es-proto-close-cell { padding: 0.8rem 1rem; background: #0a1413; }
        .es-proto-close-key {
            margin: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #9aaba7;
        }
        .es-proto-close-val {
            margin: 0.3rem 0 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #2dd4bf;
        }
        @media (min-width: 640px) {
            .es-proto-close { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 1024px) {
            .es-proto-close { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }

        /* --- Annotated clauses, on a mode-flipping card --- */
        .es-proto-terms {
            display: grid;
            gap: 0.15rem 1rem;
            padding: 0.7rem 0;
            border-top: 1px solid rgba(16, 28, 26, 0.1);
        }
        .dark .es-proto-terms { border-top-color: rgba(231, 239, 236, 0.1); }
        .es-proto-terms:first-child { border-top: 0; padding-top: 0; }
        @media (min-width: 640px) {
            .es-proto-terms { grid-template-columns: 7rem 1fr; }
        }

        /* --- A plain marked list. Tailwind's preflight strips list markers,
               so the page has to put them back. --- */
        .es-proto-list { padding-inline-start: 1.15rem; list-style: disc outside; }

        /* --- The conformance table --- */
        .es-proto-table { width: 100%; border-collapse: collapse; }
        .es-proto-table th,
        .es-proto-table td {
            padding: 0.7rem 0.8rem;
            vertical-align: top;
            text-align: start;
            border-top: 1px solid rgba(16, 28, 26, 0.1);
        }
        .dark .es-proto-table th,
        .dark .es-proto-table td { border-top-color: rgba(231, 239, 236, 0.1); }
        .es-proto-table thead th { border-top: 0; padding-top: 0; }
        .es-proto-table tbody th {
            color: #101c1a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            white-space: nowrap;
        }
        .dark .es-proto-table tbody th { color: #e7efec; }
        .es-proto-th {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #46534f;
        }
        .dark .es-proto-th { color: #9aaba7; }

        /* --- Plan pills --- */
        .es-proto-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(17, 94, 89, 0.4);
            color: #115e59;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-proto-plan { border-color: rgba(45, 212, 191, 0.42); color: #2dd4bf; }
        .es-proto-plan-pro { border-color: rgba(16, 28, 26, 0.35); color: #101c1a; }
        .dark .es-proto-plan-pro { border-color: rgba(231, 239, 236, 0.38); color: #e7efec; }

        /* --- Buttons and links --- */
        .es-proto-btn {
            background-color: #115e59;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(17, 94, 89, 0.5);
        }
        .es-proto-btn:hover { background-color: #0c4642; box-shadow: 0 22px 44px -14px rgba(17, 94, 89, 0.6); }
        .dark .es-proto-btn { background-color: #2dd4bf; color: #06110f; }
        .dark .es-proto-btn:hover { background-color: #6ee7d7; }

        .es-proto-link { color: #115e59; }
        .es-proto-link:hover { color: #101c1a; }
        .dark .es-proto-link { color: #2dd4bf; }
        .dark .es-proto-link:hover { color: #e7efec; }

        .es-proto-hover:hover { border-color: rgba(17, 94, 89, 0.45); }
        .dark .es-proto-hover:hover { border-color: rgba(45, 212, 191, 0.45); }
        .es-proto-hover:hover .es-proto-hover-title,
        .es-proto-hover:hover .es-proto-hover-arrow { color: #115e59; }
        .dark .es-proto-hover:hover .es-proto-hover-title,
        .dark .es-proto-hover:hover .es-proto-hover-arrow { color: #2dd4bf; }

        /* ==============================================================
           FIXED-DARK BANDS. Same object in both colour modes, so every
           shared class that carries its own `.dark` rule is re-inked
           here, AFTER the base rules.
           ============================================================== */
        .es-proto-band {
            background-color: #070f0e;
            background-image: radial-gradient(120% 100% at 50% 0%, #0c1917 0%, #081211 55%, #040a09 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(231, 239, 236, 0.05);
        }
        .es-proto-band .es-proto-muted { color: #9aaba7; }
        .es-proto-band .es-proto-tag { color: #2dd4bf; }
        .es-proto-band .es-proto-sec { border-bottom-color: #2dd4bf; color: #ffffff; }
        .es-proto-band .es-proto-card { border-color: rgba(231, 239, 236, 0.14); background: rgba(231, 239, 236, 0.05); }
        .es-proto-band .es-proto-token { border-color: rgba(45, 212, 191, 0.4); color: #2dd4bf; }
        .es-proto-band .es-proto-plan { border-color: rgba(45, 212, 191, 0.42); color: #2dd4bf; }
        .es-proto-band .es-proto-plan-pro { border-color: rgba(231, 239, 236, 0.38); color: #e7efec; }
        .es-proto-band .es-proto-btn { background-color: #2dd4bf; color: #06110f; }
        .es-proto-band .es-proto-btn:hover { background-color: #6ee7d7; }
        .es-proto-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 239, 236, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 239, 236, 0.05) 1px, transparent 1px);
        }
        .es-proto-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-proto-band .es-claim:focus-within {
            border-color: rgba(45, 212, 191, 0.75);
            box-shadow: 0 0 0 4px rgba(45, 212, 191, 0.22);
        }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(17, 94, 89, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(45, 212, 191, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(17, 94, 89, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(45, 212, 191, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #115e59; }
        .dark .es-dot.is-active .es-dot-pip { background: #2dd4bf; }

        /* --- Dot-nav tooltip. Its own rule because an arbitrary-value
               dark:bg-[...] is not in the built bundle. --- */
        .es-proto-tip { background: #ffffff; color: #374151; border-color: rgba(16, 28, 26, 0.14); }
        .dark .es-proto-tip { background: #101d1b; color: #d1d5db; border-color: rgba(231, 239, 236, 0.14); }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-proto-page a:focus-visible,
        #es-proto-page summary:focus-visible,
        #es-proto-page input:focus-visible,
        #es-proto-page button:focus-visible {
            outline: 2px solid #115e59;
            outline-offset: 3px;
        }
        .dark #es-proto-page a:focus-visible,
        .dark #es-proto-page summary:focus-visible,
        .dark #es-proto-page input:focus-visible,
        .dark #es-proto-page button:focus-visible {
            outline-color: #2dd4bf;
        }
        .es-proto-band a:focus-visible,
        .es-proto-band summary:focus-visible,
        .es-proto-band input:focus-visible,
        .es-proto-band button:focus-visible {
            outline-color: #2dd4bf !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-proto-pip::after { animation: none !important; }
        }
    </style>

    @php
        // The specification, clause by clause. Every line is a real property of
        // the implementation in app/Services/CalDAVService.php.
        $spec = [
            ['Protocol', 'CalDAV over HTTP', 'Calendaring extensions to WebDAV. Published, stable, and implemented independently at both ends.'],
            ['Transport', 'HTTPS, required', 'A plain http:// address is refused before a single request leaves the building.'],
            ['Auth', 'Basic, over TLS', 'Your username and a password, ideally an app password. Stored encrypted and never shown back to you.'],
            ['Payload', 'iCalendar VEVENT', 'The same .ics text your calendar app already understands. No private format in the middle.'],
            ['Discovery', 'PROPFIND', 'Three property requests turn one server URL into the list of calendars you pick from.'],
            ['Owner', 'Nobody', 'Which is the whole point. There is no partnership to renew and no API key to be revoked.'],
        ];

        // The three servers the setup screen itself names, plus the honest
        // fourth cell: there is no per-vendor code, so conformance is the
        // only requirement.
        $servers = [
            ['Apple Calendar', 'caldav.icloud.com', 'iCloud is one of the three hostnames suggested on the connection form. Generate an app-specific password in your Apple account rather than using your main one.', false],
            ['Fastmail', 'caldav.fastmail.com', 'Also suggested on the form, and also happier with an app password than with your account password.', false],
            ['Nextcloud', 'your-server/remote.php/dav', 'Your own server, your own data. Pair it with a selfhosted Event Schedule and no third party touches your calendar at all.', true],
            ['Anything conformant', 'PROPFIND /', 'Radicale, or any other server that answers the standard requests. There is no vendor-specific code here to wait for, which is why this list is not a list of partners.', false],
        ];

        // Terminology: the four ways an event reaches a calendar, because the
        // searcher usually arrives unsure which of them they need.
        $ways = [
            ['A download', '.ics file', 'One event, copied once. Change the event afterwards and the copy in the calendar stays as it was. This is what an Add to Calendar button hands over.', false],
            ['A subscription', 'iCal feed', 'An address the calendar app re-reads on its own timetable, which in some apps means every few hours. Read-only for the reader, and no password changes hands. Made for an audience.', false],
            ['A shared calendar', 'CalDAV', 'Read and write, signed in with a username and password, on a server you or your provider runs. Every app connected to it sees the same copy. Made for your own calendar.', true],
            ['A vendor API', 'Google, Microsoft', 'Google Calendar and Microsoft 365 have interfaces of their own, signed in through the provider. Event Schedule reaches them on separate Google Calendar and Outlook Calendar rows.', false],
        ];

        // Which apps speak CalDAV. General, well-known facts about each app, and
        // what that means for connecting Event Schedule.
        $speakers = [
            ['Apple Calendar', 'Yes. iCloud calendars are CalDAV underneath, and Apple Calendar on a Mac, iPhone or iPad can also add any other CalDAV account.', 'Connect Event Schedule to the server the calendar lives on. Every Apple device signed in to that account then shows the events.'],
            ['Thunderbird', 'Yes, built in.', 'Add the same server in Thunderbird and in Event Schedule, and both read and write one calendar.'],
            ['Android', 'Not built in. The DAVx5 app syncs a CalDAV account into the phone\'s own calendar.', 'Point DAVx5 and Event Schedule at the same server. The events appear in whichever calendar app the phone uses.'],
            ['Fastmail, Nextcloud, Radicale, Baïkal', 'Yes. These are servers: they hold the calendar.', 'Connect to them directly, with the address and password from the table below.'],
            ['Google Calendar', 'It has a CalDAV interface, but it signs in through Google\'s own OAuth, not with a password.', 'Use the Google Calendar row instead. It is free on every plan as well.'],
            ['Outlook and Microsoft 365', 'No. Microsoft\'s calendars are reached through Microsoft Graph.', 'Use the Outlook Calendar row instead. Also free on every plan.'],
        ];

        // Connecting: what each provider wants in the three fields.
        $providers = [
            ['iCloud', 'https://caldav.icloud.com', 'Your Apple Account email address', 'An app-specific password, made at account.apple.com under Sign-In and Security. Apple only offers these on an account with two-factor authentication turned on.'],
            ['Fastmail', 'https://caldav.fastmail.com/dav/', 'Your full Fastmail email address', 'An app password from Fastmail\'s privacy and security settings, given access to calendars.'],
            ['Nextcloud', 'https://your-server/remote.php/dav', 'Your Nextcloud login name', 'An app password, made in your personal settings under Security, in the devices and sessions list.'],
            ['Radicale, Baïkal, others', 'The CalDAV address your server\'s documentation gives', 'The account name on that server', 'That account\'s password, or an app password if the server offers them.'],
        ];

        // Connecting, in the order the form asks for things.
        $connectSteps = [
            ['Open the row', 'In Edit Schedule, choose Integrations and open the CalDAV Calendar row. Only the schedule\'s owner can connect a calendar; other members see how it is set and cannot change it.'],
            ['Type the address in full', 'Start it with https://. The hints under the Server URL field name the hosts without it, and the form refuses any address that does not begin that way, so caldav.icloud.com on its own is turned away.'],
            ['Add the username and password', 'From the table above. Paste an app password exactly as your provider shows it.'],
            ['Press Test Connection', 'A live request goes to your server. When it succeeds, the list of your calendars and the sync direction appear underneath.'],
            ['Pick the calendar', 'Choose one that holds only what belongs on your public schedule. With a direction that reads from it, every event on that calendar comes in, apart from ones marked private, declined or cancelled.'],
            ['Choose a direction and press Connect', 'To CalDAV, From CalDAV or Bidirectional Sync. The connection is saved at once and the page reloads, so save any other changes on the form first.'],
        ];

        // Troubleshooting: the messages the form shows, and what each one means.
        $caldavErrors = [
            ['CalDAV server must use HTTPS for security.', 'The address does not start with https://. A bare host name such as caldav.icloud.com counts, so type the scheme in front of it. A server that only answers plain http cannot be connected.'],
            ['Failed to connect: 401', 'The server refused the username or the password. The usual cause is an account password where the provider wants an app password, or a username that is not the full email address. Make a fresh app password and paste it in.'],
            ['Failed to connect: 301, 302, 307 or 308', 'The address redirects somewhere else, and redirects are not followed (see the security section above). Enter the address it points to. For Nextcloud that means including /remote.php/dav rather than the bare domain.'],
            ['Failed to connect: 404 or 405', 'A web server answered, but not its CalDAV endpoint. This is usually the address of the web interface you sign in to in a browser, not the DAV address.'],
            ['No calendars found on this server.', 'Signing in worked, but listing the calendars failed or came back empty. Check that the account has at least one calendar, and that the Server URL is the CalDAV address from the table above rather than a page of the provider\'s website.'],
            ['CalDAV target is not an allowed address', 'The server\'s name leads to a private or internal address: a home network range such as 192.168.x.x, a VPN or Tailscale address, or localhost. Event Schedule only contacts calendar servers on the public internet, hosted and selfhosted alike. The same message appears when the name does not resolve at all, so check the spelling too.'],
            ['A message about an SSL certificate', 'The server\'s certificate is self-signed or has expired. Give it one from a public certificate authority, Let\'s Encrypt included, and test again.'],
        ];

        // After it connects: the two quiet failures that show no message at all.
        $quiet = [
            ['My events are not in my calendar', [
                'The direction is To CalDAV or Bidirectional Sync, not From CalDAV.',
                'The event is published. Drafts are never written out.',
                'The event was saved after you connected. Events already published before that are written the next time each one is saved; connecting does not copy your back catalogue in one go.',
                'Your calendar app has fetched lately. On a phone, opening the calendar list and pulling down usually asks the server again.',
            ]],
            ['My calendar entries are not on my schedule', [
                'The direction is From CalDAV or Bidirectional Sync.',
                'Fifteen minutes have passed. Reading is polled, not pushed.',
                'You are looking at the drafts. New entries wait there unless Publish new events automatically is on in the same row; the Schedule tab says how many are waiting and has a Review button.',
                'The entry falls between 30 days ago and a year from now. Anything outside that window is not asked for.',
            ]],
        ];

        // The six requests the client makes, in the order it makes them.
        $trace = [
            ['PROPFIND', 'current-user-principal', 'Who am I on this server?', 'The first thing a CalDAV client asks. Your server answers with the address of your own principal, which is where everything else hangs from.', false],
            ['PROPFIND', 'calendar-home-set', 'And where do my calendars live?', 'One more property request, this time against your principal. The answer is the collection that holds your calendars.', false],
            ['PROPFIND', 'displayname, Depth: 1', 'List them, then.', 'One level down from the home collection, asking each child for its display name and its resource type. This is the entire content of what a settings page calls automatic discovery: you type a URL, and three property requests turn it into a dropdown.', false],
            ['REPORT', 'calendar-query, time-range', 'What is on this one?', 'A filtered report for VEVENT components inside a window: thirty days back and one year ahead. The response carries each event, and each event carries a tag saying which version of it you just read.', true],
            ['PUT', '{uid}.ics', 'Here is one event.', 'One event, one resource, at a URL built from the identifier we generated for it. Saving the same event again is a PUT to the same URL, which is an edit rather than a duplicate.', false],
            ['DELETE', '{uid}.ics', 'Take that one back off.', 'The same URL, the other verb. Deleting an event here removes the resource there, and so does turning a published event back into a draft.', false],
        ];

        // The four values of roles.caldav_sync_direction, the fourth being unset.
        $directions = [
            ['To the calendar', 'to', 'Events you publish here are written to your calendar. Nothing on your calendar is read.', false],
            ['From the calendar', 'from', 'Events on your calendar arrive here as real events on your schedule, as drafts until you publish them or choose to publish them as they arrive. Nothing is written back.', false],
            ['Both ways', 'both', 'Push on save, and read every quarter hour. A change on either side reaches the other.', true],
            ['Off', 'unset', 'The server stays connected and the credentials stay stored. The sync simply does not run.', false],
        ];

        // The record itself: iCalendar property names, and what each one carries
        // in each direction. Outbound is CalDAVService::buildVEvent, inbound is
        // parseVEvent plus createEventFromCalDAV / updateEventFromCalDAV.
        $record = [
            ['UID', 'An identifier we generate, kept on the row that links the event to this schedule', 'The key. It is the reason a second visit is an edit and not a second event'],
            ['SUMMARY', 'The event name, exactly as you typed it', 'Becomes the event name'],
            ['DESCRIPTION', 'The event description, or your own calendar description template if you set one', 'Converted from HTML back to Markdown, and only when the calendar actually sent one'],
            ['DTSTART', 'The start, as an absolute instant in UTC', 'Becomes the start, stored in UTC'],
            ['DTEND', 'Start plus the event duration, or two hours if none is set', 'The length becomes the duration, but only when an end was actually sent'],
            ['LOCATION', 'The address of the venue on the event', 'Matched against the venues you already have, and added as one if it is new'],
            ['URL', 'The public page for the event', 'Not read back'],
            ['CLASS', 'PRIVATE, when the event is unlisted', 'Not read back'],
            ['DTSTAMP, CREATED, LAST-MODIFIED', 'Stamped on every write', 'Not read back'],
        ];

        // When each direction actually runs.
        $timing = [
            ['Outbound', 'The moment you save', 'Creating, editing or deleting an event goes out to your calendar as part of that save. Nothing to press, no queue to watch.'],
            ['Inbound', 'Every fifteen minutes', 'A scheduled task walks the schedules set to read from a calendar. It takes a lock first, so a slow server can never leave two runs overlapping.'],
            ['The window', '30 days back, 365 ahead', 'The report asks for that range. An event edited long after it happened is outside it, and so is one scheduled more than a year out.'],
        ];

        // Idempotence: the guards that keep a two-way sync from eating itself.
        $checks = [
            ['Collection tag', 'ctag', 'Before reading anything, one property request asks the calendar for its own tag. Unchanged since last time means nothing has happened there, and the run stops at that single request.', false],
            ['Resource tag', 'etag', 'Every synced event keeps its own tag on the link row. Unchanged means skip it. Changed means reconcile the fields, and even then the write is guarded, so nothing is saved unless a value really differs.', false],
            ['One identifier, one event', 'uid', 'A repeating resource arrives as several instances sharing a single identifier. The first is handled and the rest are left alone, so a weekly booking does not land as fifty two events.', false],
            ['Bookings are not overwritten', 'appointment', 'An appointment booked on your booking page also appears on your calendar, and the calendar is never allowed to write back over it. A rescheduled booking cannot be dragged back to its old time by a stale copy.', true],
        ];

        // Security Considerations, because every specification has one.
        $security = [
            ['HTTPS', 'Required, checked twice', 'Both the server URL and the calendar URL have to be https. The form rejects anything else, and the client refuses the settings again on its own account, so a connection without TLS is never stored in the first place.'],
            ['Credentials', 'Encrypted at rest', 'Server, username, password and calendar URL are stored as one encrypted value, and held back from anything the application serialises, so they do not leak through an export or an error page.'],
            ['Passwords', 'Use an app password', 'The setup screen recommends one, and it is the right advice: an app password can be revoked at your provider without touching the rest of your account, and the sync simply stops.'],
            ['Addresses', 'Vetted, then pinned', 'Every address is checked against private and reserved ranges before it is contacted, including the ones your own server hands back during discovery, and the connection is pinned to the address that passed. Redirects are not followed, so a reply cannot send the client somewhere else.'],
            ['Logs', 'Redacted', 'When a request fails, the response is truncated and stripped of authorization headers and password-shaped strings before a single line is written.'],
            ['Requests', 'Throttled', 'Testing a server and listing its calendars are rate limited per account, so the connection form cannot be turned into a scanner.'],
        ];

        // The closing clause: the hero sheet's voice again, on the way out.
        $closing = [
            ['Protocol', 'RFC 4791'],
            ['Direction', 'to, from, both, off'],
            ['Price', 'Free, every plan'],
            ['Owner', 'Nobody'],
        ];

        $related = [
            ['/features/calendar-sync', 'Calendar sync', 'The whole two-way story, across every provider.'],
            ['/google-calendar', 'Google Calendar', 'The same idea on a proprietary API, with webhooks.'],
            ['/outlook-calendar', 'Outlook Calendar', 'Microsoft 365 and Teams, through Graph.'],
            ['/open-source', 'Open source', 'Read the client that speaks all of this.'],
        ];

        $faqs = [
            [
                'q' => 'Which CalDAV servers are supported?',
                'a' => 'Any server that implements CalDAV. The connection form suggests hostnames for three of them, iCloud for Apple Calendar, Nextcloud and Fastmail, but there is no vendor-specific code behind them: Radicale or any other conformant server is reached by exactly the same requests. If your server answers a PROPFIND for its calendar home, it works.',
            ],
            [
                'q' => 'Is CalDAV sync free?',
                'a' => 'Yes. CalDAV sync is on the free plan, in both directions, hosted or selfhosted. There is no calendar feature behind the paywall.',
            ],
            [
                'q' => 'Does CalDAV sync work with selfhosted Event Schedule?',
                'a' => 'Yes, and it is the combination the protocol was made for. Both Event Schedule and your calendar server can run on your own infrastructure, with nothing in between and no account with anyone. The usage metering that exists on the hosted service does not run on your own install either, so nothing about your calendar is counted or reported anywhere.',
            ],
            [
                'q' => 'How often does CalDAV sync run?',
                'a' => 'Outbound is immediate: saving, editing or deleting an event writes to your calendar as part of that save. Inbound runs every fifteen minutes, and starts by asking your calendar for its collection tag, so a calendar with nothing new costs one property request rather than a full read.',
            ],
            [
                'q' => 'Is my CalDAV password stored securely?',
                'a' => 'Yes. The server URL, username, password and calendar URL are stored as a single encrypted value and are excluded from anything the application serialises, so they are not exposed through an export or an error page. Use an app-specific password from your provider rather than your account password, and you can revoke the connection from their side at any time.',
            ],
            [
                'q' => 'What happens if I delete an event in my calendar app?',
                'a' => 'Nothing here. Reading from a calendar adds and updates events; it does not remove them. Deleting an event in Event Schedule does remove it from your calendar, and so does turning it back into a draft, but a deletion made in your calendar client leaves the event on your schedule for you to deal with. Google Calendar and Outlook sync do offer a policy for that; CalDAV does not.',
            ],
            [
                'q' => 'Do recurring events send every date to my calendar?',
                'a' => 'No. Sync works at the event level, so a recurring event crosses as one entry rather than one per date. If you want every individual date in a calendar, use the published iCal feed for your schedule, which expands recurring events into one entry per date for the next ninety days, or the per-date iCal download on the event itself.',
            ],
            [
                'q' => 'Can people subscribe to my schedule without CalDAV?',
                'a' => 'Yes. CalDAV is for your own calendar, the one you sync in both directions. Everybody else gets a read-only subscription: your pages offer Subscribe to all events from your schedule, in the Add to Calendar menu and beside the email sign-up. It is the schedule\'s live iCal feed, which Apple Calendar, Google Calendar, Outlook or any app that subscribes to a URL keeps re-reading, so a date you move moves for them too. It carries your public events, with each date of a recurring event for the next ninety days, and it asks for no email address, no account and no password.',
            ],
            [
                'q' => 'Can I use CalDAV and Google Calendar at the same time?',
                'a' => 'Yes. They are separate connections with separate directions on the same schedule, so you can push to one and read from the other, or run both ways on both. Each schedule you own is configured on its own, so two schedules can point at two entirely different calendars.',
            ],
            [
                'q' => 'What is the CalDAV server address for iCloud?',
                'a' => 'Type https://caldav.icloud.com as the Server URL, with the https:// in front. The username is your Apple Account email address, and the password is an app-specific password made at account.apple.com under Sign-In and Security, not the password you sign in to Apple with. Apple only offers app-specific passwords on accounts with two-factor authentication turned on.',
            ],
            [
                'q' => 'What is the difference between CalDAV and an ICS subscription?',
                'a' => 'An ICS subscription is a read-only address that a calendar app downloads again from time to time, on its own timetable. Nobody signs in and nobody can change anything through it, which makes it the right way to give your audience your dates. CalDAV is a two-way protocol: an app signs in to a server with a username and password, and can read, add, change and delete events there. Use CalDAV for your own calendar and the iCal feed for everybody else.',
            ],
            [
                'q' => 'Can I use CalDAV with Google Calendar or Outlook?',
                'a' => 'Not usefully. Google Calendar does have a CalDAV interface, but it signs in through Google\'s OAuth rather than a password, and Outlook and Microsoft 365 calendars are not CalDAV servers at all. Event Schedule connects to both through their own APIs instead, on the Google Calendar and Outlook Calendar rows under Integrations, and both are free on every plan.',
            ],
            [
                'q' => 'How do I see CalDAV events on an Android phone?',
                'a' => 'Android has no CalDAV support of its own. The usual answer is DAVx5, an app that signs in to a CalDAV server and copies its calendars into the phone\'s calendar, where any calendar app can show them. Connect Event Schedule and DAVx5 to the same server, such as Nextcloud or Fastmail, and the events you publish reach the phone through it.',
            ],
        ];

        $dotSections = [
            ['top', 'The standard'],
            ['what-is-caldav', 'Terminology'],
            ['servers', 'Interoperability'],
            ['connect', 'Connecting'],
            ['how-it-works', 'On the wire'],
            ['direction', 'Direction'],
            ['record', 'The record'],
            ['timing', 'Timing'],
            ['checks', 'Idempotence'],
            ['security', 'Security'],
            ['troubleshooting', 'Troubleshooting'],
            ['open', 'Both ends open'],
            ['faq', 'Questions'],
            ['claim', 'Connect'],
        ];
    @endphp

    <div id="es-proto-page" class="es-proto-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the specification itself                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 68%, rgba(17, 94, 89, 0.3), rgba(17, 94, 89, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 30%, rgba(45, 212, 191, 0.2), rgba(45, 212, 191, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <h1 class="es-balance es-proto-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="es-fade-up es-d-1 glass inline-flex items-center gap-3 rounded-full px-5 py-2.5 mb-8">
                            <svg aria-hidden="true" class="es-proto-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="es-proto-muted text-sm font-medium tracking-wide">CalDAV calendar sync, no lock-in</span>
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">CalDAV is the calendar protocol</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-proto-accent">nobody owns.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-proto-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Apple Calendar speaks CalDAV. Fastmail speaks it. Your own Nextcloud speaks it. Event Schedule speaks it too, in six standard requests against a URL you type in yourself. No partnership, no API key, no permission from anyone.
                    </p>

                    <div class="es-fade-up es-d-3 mb-8 flex flex-wrap gap-2">
                        <span class="es-proto-chip">Free on every plan</span>
                        <span class="es-proto-chip">Both directions, or one</span>
                        <span class="es-proto-chip">HTTPS only</span>
                        <span class="es-proto-chip">Selfhost to selfhost</span>
                    </div>

                    <div class="es-fade-up es-d-4 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#how-it-works" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See it on the wire
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-proto-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The specification. A fixed object: identical in light and dark. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-proto-sheet">
                        <div class="es-proto-sheet-head">
                            <span class="es-proto-sheet-kind">Specification</span>
                            <span class="es-proto-sheet-id">RFC 4791 / CalDAV</span>
                        </div>
                        <dl class="es-proto-sheet-rows">
                            @foreach ($spec as [$clause, $value, $gloss])
                                <div class="es-proto-sheet-row">
                                    <dt class="es-proto-sheet-key">{{ $clause }}</dt>
                                    <dd class="es-proto-sheet-val">
                                        {{ $value }}
                                        <p class="es-proto-sheet-gloss">{{ $gloss }}</p>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                        <p class="es-proto-sheet-foot">
                            <span class="es-proto-sheet-stamp">Free</span>
                            CalDAV sync is on the free plan, in both directions, on the hosted service and on your own server alike.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2a. Terminology: what CalDAV is, and what it is not          -->
    <!-- ============================================================ -->
    <section id="what-is-caldav" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 1</span><span>Terminology</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    One calendar on a server. <span class="es-proto-accent">Every app reads the same copy.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    CalDAV is the standard way for a calendar app to read and write events that live on a server. It was published in 2007 as RFC 4791, and it is built on WebDAV, which is itself an extension of HTTP. The calendar belongs to the server. Your phone, your laptop and Event Schedule are all clients of it, so a change one of them makes is what the others read next.
                </p>
            </div>

            <p class="es-proto-tag mb-4 text-center" data-reveal>Four ways an event reaches a calendar</p>
            <div class="es-proto-strip" data-reveal="panel">
                @foreach ($ways as [$wayName, $wayToken, $wayBody, $wayOn])
                    <div class="es-proto-cell @if ($wayOn) es-proto-cell-on @endif">
                        <span class="es-proto-token">{{ $wayToken }}</span>
                        <h3 class="es-proto-ink text-lg font-bold">{{ $wayName }}</h3>
                        <p class="es-proto-muted text-sm">{{ $wayBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-proto-card flex flex-col p-7" data-reveal="panel">
                    <p class="es-proto-tag mb-3">Pick CalDAV when</p>
                    <ul class="es-proto-list es-proto-muted space-y-2 text-sm leading-relaxed">
                        <li>The calendar is yours, or your team's, and you already live in it on your phone.</li>
                        <li>You want an entry you add in Apple Calendar or Thunderbird to turn up on your public schedule without retyping it.</li>
                        <li>Your calendar is on iCloud, Fastmail, Nextcloud or another server that speaks the standard.</li>
                        <li>You want to change a date in one place and have both sides agree.</li>
                    </ul>
                </div>
                <div class="es-proto-card flex flex-col p-7" data-reveal="panel">
                    <p class="es-proto-tag mb-3">Pick the iCal feed when</p>
                    <ul class="es-proto-list es-proto-muted space-y-2 text-sm leading-relaxed">
                        <li>The people reading are your audience, and they should see your dates without being able to change them.</li>
                        <li>Nobody should have to hand a password to anything.</li>
                        <li>They use Google Calendar or Outlook, which subscribe to an address happily and cannot add your CalDAV server as an account.</li>
                        <li>A refresh every few hours on their side is soon enough.</li>
                    </ul>
                    <a href="{{ marketing_url('/docs/sharing#calendar-feeds') }}" class="es-proto-link mt-auto inline-flex items-center gap-2 pt-4 text-sm font-medium hover:underline">
                        How the iCal feed works
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="es-proto-card mt-8 overflow-x-auto p-5 sm:p-7" data-reveal="panel">
                <table class="es-proto-table">
                    <caption class="es-proto-muted mb-4 text-start text-sm">Which calendar apps speak CalDAV, and what that means for connecting Event Schedule</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-proto-th">App</th>
                            <th scope="col" class="es-proto-th">Speaks CalDAV?</th>
                            <th scope="col" class="es-proto-th">With Event Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($speakers as [$spApp, $spSpeaks, $spHow])
                            <tr>
                                <th scope="row">{{ $spApp }}</th>
                                <td class="es-proto-muted text-sm">{{ $spSpeaks }}</td>
                                <td class="es-proto-muted text-sm">{{ $spHow }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="es-proto-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                The point to take away: Event Schedule connects to the server, not to each device. Connect it once to the account your calendar lives in, and every phone, tablet and laptop signed in to that account sees the same events.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Interoperability                                          -->
    <!-- ============================================================ -->
    <section id="servers" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 2</span><span>Interoperability</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Not a list of partners. <span class="es-proto-accent">A list of clients.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    These are the three hostnames the connection form suggests, and the honest fourth cell. Nothing here was negotiated with anybody.
                </p>
            </div>

            <div class="es-proto-strip" data-reveal="panel">
                @foreach ($servers as [$srvName, $srvHost, $srvBody, $srvOn])
                    <div class="es-proto-cell @if ($srvOn) es-proto-cell-on @endif">
                        <span class="es-proto-token">{{ $srvHost }}</span>
                        <h3 class="es-proto-ink text-lg font-bold">{{ $srvName }}</h3>
                        <p class="es-proto-muted text-sm">{{ $srvBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="es-proto-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                The connection lives on the schedule, set up once by its owner under Integrations.
                <a href="{{ marketing_url('/docs/creating-schedules#integrations-caldav') }}" class="es-proto-link font-medium hover:underline">The setup steps, in the guide</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2b. Connecting, provider by provider                         -->
    <!-- ============================================================ -->
    <section id="connect" class="es-proto-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 3</span><span>Connecting</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three fields. <span class="es-proto-accent">Most failures are one of them.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    A CalDAV connection is a server address, a username and a password. Each provider wants each of them in a particular shape, and the password is almost never the one you sign in with.
                </p>
            </div>

            <div class="es-proto-card overflow-x-auto p-5 sm:p-7" data-reveal="panel">
                <table class="es-proto-table">
                    <caption class="es-proto-muted mb-4 text-start text-sm">What to type in each field, by provider</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-proto-th">Provider</th>
                            <th scope="col" class="es-proto-th">Server URL</th>
                            <th scope="col" class="es-proto-th">Username</th>
                            <th scope="col" class="es-proto-th">Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($providers as [$pvName, $pvUrl, $pvUser, $pvPass])
                            <tr>
                                <th scope="row">{{ $pvName }}</th>
                                <td class="es-proto-muted text-sm"><span dir="ltr" class="break-all font-mono text-xs">{{ $pvUrl }}</span></td>
                                <td class="es-proto-muted text-sm">{{ $pvUser }}</td>
                                <td class="es-proto-muted text-sm">{{ $pvPass }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ol class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="80">
                @foreach ($connectSteps as [$stepTitle, $stepBody])
                    <li class="es-proto-card flex gap-4 p-7" data-reveal="panel">
                        <span class="es-proto-accent shrink-0 font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="es-proto-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                            <p class="es-proto-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="mt-6 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-proto-caution p-6" data-reveal="panel">
                    <p class="es-proto-tag mb-3">Use a calendar of its own</p>
                    <p class="es-proto-muted text-sm leading-relaxed">Make a new calendar on your server for this, called something like Public events, rather than connecting the one you keep your dentist in. With From CalDAV or Bidirectional Sync, every event on the chosen calendar is read in, apart from ones marked private, declined or cancelled. New entries wait as drafts until you publish them, but a calendar that only ever holds public dates is one less thing to review.</p>
                </div>
                <div class="es-proto-caution p-6" data-reveal="panel">
                    <p class="es-proto-tag mb-3">What happens next</p>
                    <p class="es-proto-muted text-sm leading-relaxed">Writing starts with your next save: a published event goes out each time it is saved, and one that was already published before you connected goes out the next time you save it. Reading starts with the next fifteen-minute run. Turn on Publish new events automatically in the same row if you would rather skip the drafts.</p>
                </div>
            </div>

            <p class="es-proto-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                The same steps, with every field named as it is on the screen, are in the user guide.
                <a href="{{ marketing_url('/docs/creating-schedules#integrations-caldav') }}" class="es-proto-link font-medium hover:underline">CalDAV Calendar in the guide</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. On the wire (fixed-dark band)                             -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-proto-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-proto-sec mb-6" data-reveal><span>&sect; 4</span><span>On the wire</span></div>
                    <h2 class="es-balance mt-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        Six requests. <span class="es-proto-lit">All of them in the spec.</span>
                    </h2>
                    <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        This is the conversation, in the order it happens. A polled read opens with one more property request, which is what &sect; 8 is about. Read both once and there is no magic left in the word integration.
                    </p>
                </div>

                <div class="es-proto-trace" data-reveal-group="110">
                    @foreach ($trace as [$verb, $path, $question, $body, $live])
                        <div class="es-proto-step" data-reveal>
                            <div class="es-proto-hop">
                                <span class="es-proto-num" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="es-proto-verb">
                                    @if ($live)
                                        <span class="es-proto-pip" aria-hidden="true"></span>
                                    @endif

                                    {{ $verb }}
                                </span>
                            </div>
                            <div>
                                <h3 class="mb-1 text-lg font-bold text-white">{{ $question }}</h3>
                                <p class="es-proto-path mb-2">{{ $path }}</p>
                                <p class="es-proto-muted text-sm leading-relaxed">{{ $body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                    <div class="es-proto-card p-7" data-reveal="panel">
                        <p class="es-proto-tag mb-3">No endpoint of ours</p>
                        <p class="es-proto-muted text-sm leading-relaxed">Not one of those six is an Event Schedule endpoint. They are the requests the standard defines, which is exactly why the same client reaches a server we have never heard of, running software written by somebody we have never met.</p>
                    </div>
                    <div class="es-proto-card p-7" data-reveal="panel">
                        <p class="es-proto-tag mb-3">Tested before it is saved</p>
                        <p class="es-proto-muted text-sm leading-relaxed">A connection is only stored once a live request to your server has succeeded. A typo in the hostname fails on the form in front of you, rather than silently at three in the morning.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Direction                                                 -->
    <!-- ============================================================ -->
    <section id="direction" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 5</span><span>Direction</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Four states. <span class="es-proto-accent">That is the whole enum.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    You choose one per schedule. There is no fifth setting hiding behind an upgrade.
                </p>
            </div>

            <div class="es-proto-strip" data-reveal="panel">
                @foreach ($directions as [$dirName, $dirValue, $dirBody, $dirOn])
                    <div class="es-proto-cell @if ($dirOn) es-proto-cell-on @endif">
                        <span class="es-proto-token">{{ $dirValue }}</span>
                        <h3 class="es-proto-ink text-lg font-bold">{{ $dirName }}</h3>
                        <p class="es-proto-muted text-sm">{{ $dirBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-proto-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-proto-ink text-lg font-bold">It sits on the schedule</h3>
                        <span class="es-proto-plan">Free</span>
                    </div>
                    <p class="es-proto-muted text-sm">The schedule owner adds the server, picks the calendar and picks the direction, and the sync runs on that connection. Every schedule you own is configured on its own, so a second one can point at a completely different calendar.</p>
                </div>
                <div class="es-proto-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-proto-ink text-lg font-bold">Drafts stay here</h3>
                        <span class="es-proto-plan">Free</span>
                    </div>
                    <p class="es-proto-muted text-sm">Only published events are written out. Put an event back into Draft and it is removed from your calendar again, so a show you are still arguing about does not appear on anybody's phone.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The record                                                -->
    <!-- ============================================================ -->
    <section id="record" class="es-proto-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 6</span><span>Conformance</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Property by property, <span class="es-proto-accent">both ways.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    No mystery mapping. These are iCalendar property names, what Event Schedule writes into each one, and what it takes back out of one that arrives.
                </p>
            </div>

            <div class="es-proto-card overflow-x-auto p-5 sm:p-7" data-reveal="panel">
                <table class="es-proto-table">
                    <caption class="sr-only">How each iCalendar VEVENT property maps to an Event Schedule event, outbound and inbound</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-proto-th">Property</th>
                            <th scope="col" class="es-proto-th">Written out</th>
                            <th scope="col" class="es-proto-th">Read back in</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($record as [$rProp, $rOut, $rIn])
                            <tr>
                                <th scope="row">{{ $rProp }}</th>
                                <td class="es-proto-muted text-sm">{{ $rOut }}</td>
                                <td class="es-proto-muted text-sm">{{ $rIn }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-proto-card p-7" data-reveal="panel">
                    <p class="es-proto-tag mb-3">Time</p>
                    <h3 class="es-proto-ink mb-2 text-lg font-bold">Both ends store the instant</h3>
                    <p class="es-proto-muted text-sm leading-relaxed">Times cross as UTC, which is what iCalendar is for: an absolute moment rather than a wall clock. Your client draws it in your own zone, and a colleague three timezones away sees the same show at their own eight o'clock.</p>
                </div>
                <div class="es-proto-card p-7" data-reveal="panel">
                    <p class="es-proto-tag mb-3">Arriving events</p>
                    <h3 class="es-proto-ink mb-2 text-lg font-bold">Real events, not shadows</h3>
                    <p class="es-proto-muted text-sm leading-relaxed">An event read from your calendar becomes a proper event on the schedule: accepted the moment it lands, given a URL from your own slug pattern, and filed under your default event category if you set one.</p>
                </div>
            </div>

            <p class="es-proto-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Sync is per event, not per date: a recurring event crosses as a single entry. Every individual date is covered by the published iCal feed for your schedule.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Timing                                                    -->
    <!-- ============================================================ -->
    <section id="timing" class="es-proto-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 7</span><span>Timing</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    One side is instant. <span class="es-proto-accent">The other is polled.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    CalDAV has no way for your server to call us, so the honest answer is a schedule rather than a webhook.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($timing as [$tKind, $tWhen, $tBody])
                    <div class="es-proto-card flex flex-col p-7" data-reveal="panel">
                        <p class="es-proto-tag mb-3">{{ $tKind }}</p>
                        <h3 class="es-proto-ink mb-2 text-lg font-bold">{{ $tWhen }}</h3>
                        <p class="es-proto-muted text-sm leading-relaxed">{{ $tBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-proto-caution mt-6 p-6" data-reveal="panel">
                <p class="es-proto-tag mb-3">Worth knowing</p>
                <p class="es-proto-muted text-sm leading-relaxed">Fifteen minutes is the interval for reading, not for writing. If you need a change on your public page the second you make it, make it here: outbound is part of the save. And if you want near-instant traffic in the other direction, Google Calendar and Outlook can push to us, because their APIs have somewhere to push to.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Idempotence (fixed-dark band)                             -->
    <!-- ============================================================ -->
    <section id="checks" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-proto-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-proto-sec mb-6" data-reveal><span>&sect; 8</span><span>Idempotence</span></div>
                    <h2 class="es-balance mt-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        The hard part of two-way sync is <span class="es-proto-lit">doing nothing.</span>
                    </h2>
                    <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        A loop that runs every quarter hour has to be able to decide that nothing happened. Four guards do that, and three of them are just properties the calendar already publishes about itself.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-2" data-reveal-group="110">
                    @foreach ($checks as [$cTitle, $cToken, $cBody, $cPro])
                        <div class="es-proto-card flex flex-col p-7" data-reveal="panel">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <span class="es-proto-token">{{ $cToken }}</span>
                                @if ($cPro)
                                    <span class="es-proto-plan es-proto-plan-pro">Pro</span>
                                @endif
                            </div>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $cTitle }}</h3>
                            <p class="es-proto-muted text-sm leading-relaxed">{{ $cBody }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="es-proto-muted mx-auto mt-10 max-w-2xl text-center text-sm" data-reveal>
                    There is one more guard behind those: an arriving event whose name and start time already exist on your schedule is treated as the same event and left alone, which catches the copy you typed in by hand the week before you connected anything.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Security considerations                                   -->
    <!-- ============================================================ -->
    <section id="security" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-proto-sec mb-6" data-reveal><span>&sect; 9</span><span>Security considerations</span></div>
                    <h2 class="es-balance es-proto-ink mb-5 mt-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                        Every specification has <span class="es-proto-accent">this section.</span>
                    </h2>
                    <p class="es-proto-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.1s;">
                        CalDAV asks you to hand a password to a program and let it talk to a server on your behalf. That deserves saying out loud, in the same plain terms as the rest of the page.
                    </p>
                    <div class="es-proto-caution p-6" data-reveal="panel">
                        <p class="es-proto-tag mb-3">Disconnecting</p>
                        <p class="es-proto-muted text-sm leading-relaxed">Disconnecting is not deleting. It clears the credentials, the stored tag and the direction, and the sync stops. Every event already written to your calendar stays on your calendar, and everything already read in stays here.</p>
                    </div>
                </div>

                <div class="es-proto-card p-7" data-reveal="panel">
                    <dl>
                        @foreach ($security as [$sKey, $sValue, $sBody])
                            <div class="es-proto-terms">
                                <dt class="es-proto-tag">{{ $sKey }}</dt>
                                <dd class="m-0">
                                    <span class="es-proto-ink font-semibold">{{ $sValue }}</span>
                                    <p class="es-proto-muted mt-1 text-sm leading-relaxed">{{ $sBody }}</p>
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8b. Troubleshooting: the messages, and the two silences      -->
    <!-- ============================================================ -->
    <section id="troubleshooting" class="es-proto-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 10</span><span>Troubleshooting</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    What the message means, <span class="es-proto-accent">and what to change.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Test Connection says why it failed, in the words below. When it fails with a number, that number is the HTTP status your server sent back, and the number tells you which field to look at.
                </p>
            </div>

            <div class="es-proto-card p-7" data-reveal="panel">
                <dl>
                    @foreach ($caldavErrors as [$errSays, $errMeans])
                        <div class="es-proto-terms">
                            <dt class="es-proto-ink font-mono text-sm font-semibold">{{ $errSays }}</dt>
                            <dd class="m-0">
                                <p class="es-proto-muted text-sm leading-relaxed">{{ $errMeans }}</p>
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2" data-reveal-group="90">
                @foreach ($quiet as [$quietTitle, $quietChecks])
                    <div class="es-proto-card flex flex-col p-7" data-reveal="panel">
                        <p class="es-proto-tag mb-3">Connected, but</p>
                        <h3 class="es-proto-ink mb-3 text-lg font-bold">{{ $quietTitle }}</h3>
                        <p class="es-proto-muted mb-2 text-sm">Check, in this order, that:</p>
                        <ul class="es-proto-list es-proto-muted space-y-2 text-sm leading-relaxed">
                            @foreach ($quietChecks as $quietCheck)
                                <li>{{ $quietCheck }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="es-proto-caution mt-6 p-6" data-reveal="panel">
                <p class="es-proto-tag mb-3">Changed your password?</p>
                <p class="es-proto-muted text-sm leading-relaxed">If you revoke the app password at your provider, or change the account password a connection was made with, the sync stops working at its next request. Disconnect in the CalDAV Calendar row, make a new app password, and connect again. Nothing already on either calendar is removed by doing that.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Both ends open                                            -->
    <!-- ============================================================ -->
    <section id="open" class="es-proto-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 11</span><span>Both ends open</span></div>
                <h2 class="es-balance es-proto-ink mt-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    An open standard on one side. <span class="es-proto-accent">Open source on the other.</span>
                </h2>
                <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    It is the same argument made twice: you can read what the protocol promises, and you can read what we actually do with it.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                <div class="es-proto-card flex flex-col p-7" data-reveal="panel">
                    <p class="es-proto-tag mb-3">Your server, both times</p>
                    <h3 class="es-proto-ink mb-3 text-xl font-bold">Selfhost to selfhost</h3>
                    <p class="es-proto-muted mb-4 text-sm leading-relaxed">Run Event Schedule on your own machine, point it at your own calendar server, and no third party is involved in your calendar at all. The calendar server does need an address on the public internet: private network addresses are refused even on your own install. The usage metering that exists on the hosted service does not run on your own install, so nothing is counted and nothing is reported.</p>
                    <div class="mt-auto flex flex-wrap gap-2">
                        <span class="es-proto-chip">No cloud dependency</span>
                        <span class="es-proto-chip">Full data ownership</span>
                        <span class="es-proto-chip">Nothing metered</span>
                    </div>
                </div>
                <div class="es-proto-card flex flex-col p-7" data-reveal="panel">
                    <p class="es-proto-tag mb-3">Read the client</p>
                    <h3 class="es-proto-ink mb-3 text-xl font-bold">The implementation is public</h3>
                    <p class="es-proto-muted mb-4 text-sm leading-relaxed">The requests it makes, the properties it asks for, the fields it writes and the guards that stop it looping are all in the repository. If you want to know exactly what happens to your calendar, you do not have to take our word for it.</p>
                    <a href="{{ marketing_url('/open-source') }}" class="es-proto-link mt-auto inline-flex items-center gap-2 font-medium hover:underline">
                        How open source works here
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>

            <ul class="es-proto-list es-proto-muted mx-auto mt-8 max-w-3xl space-y-2 text-sm" data-reveal>
                <li>Everything on this page is on the free plan. Zero platform fees on ticket sales is a separate promise, and also true.</li>
                <li>Your audience does not need CalDAV or an account: your pages offer Subscribe to all events from your schedule, a live iCal feed Apple Calendar or any other calendar app can subscribe to, and every event has its own iCal download, per date. <a href="{{ marketing_url('/docs/sharing#calendar-feeds') }}" class="es-proto-link font-medium hover:underline">How calendar subscriptions work</a></li>
                <li>CalDAV, Google Calendar and Outlook are independent connections. Running one does not rule out the others.</li>
            </ul>
        </div>
    </section>

    {{-- The shared plan band. Every one of these pages states its tiers inline
         with Free/Pro chips, but none of them offered a way to see what the
         tiers cost, or even a link to /pricing - a reader who got this far had
         to go back to the header. --}}
    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-proto-rule py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-proto-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($related as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-proto-hover es-proto-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-proto-hover-title es-proto-ink mb-2 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-proto-muted mb-4 text-xs leading-relaxed">{{ $relBlurb }}</span>
                        <span class="es-proto-hover-arrow es-proto-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
                <a href="{{ marketing_url('/features/integrations') }}" class="es-proto-link inline-flex items-center font-medium hover:underline">
                    See all integrations
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/docs/creating-schedules#integrations-caldav') }}" class="es-proto-link inline-flex items-center font-medium hover:underline">
                    CalDAV setup guide
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <section id="faq" class="es-proto-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 12</span><span>Questions</span></div>
                <h2 class="es-balance es-proto-ink mb-4 mt-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-proto-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they hand a calendar password to anything.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-proto-hover es-proto-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-proto-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-proto-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-proto-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-proto-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-proto-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
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
            <div class="es-proto-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <div class="es-proto-sec mb-6"><span>&sect; 13</span><span>Conformance statement</span></div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        One standard. <span class="es-proto-lit">Two calendars.</span>
                    </h2>
                    <p class="es-proto-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Pick a name, add your server, choose a direction. Both directions, hosted or on your own machine, at no cost on any plan.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-proto-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-proto-muted mt-6 text-sm">No credit card required</p>

                    <dl class="es-proto-close mt-10">
                        @foreach ($closing as [$closeKey, $closeVal])
                            <div class="es-proto-close-cell">
                                <dt class="es-proto-close-key">{{ $closeKey }}</dt>
                                <dd class="es-proto-close-val">{{ $closeVal }}</dd>
                            </div>
                        @endforeach
                    </dl>
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
                        <span class="es-proto-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
