<x-marketing-layout>
    <x-slot name="title">CalDAV Calendar Sync - Event Schedule</x-slot>
    <x-slot name="description">Sync with any CalDAV-compatible calendar server. Works with Nextcloud, Radicale, Fastmail, iCloud, and more. Open standard, selfhosted friendly.</x-slot>
    <x-slot name="breadcrumbTitle">CalDAV</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - CalDAV Sync",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Sync with any CalDAV-compatible calendar server. Works with Nextcloud, Radicale, Fastmail, iCloud, and more. Open standard, selfhosted friendly.",
        "featureList": [
            "CalDAV sync over the published RFC 4791 protocol, free on every plan",
            "Automatic calendar discovery from a single server URL",
            "Per-schedule sync direction: to the calendar, from it, both ways or off",
            "iCalendar VEVENT payloads, readable by any calendar client",
            "Works with Apple Calendar and iCloud, Nextcloud, Fastmail and any conformant server",
            "HTTPS required, credentials stored encrypted",
            "Collection and resource tags so an unchanged calendar costs one request",
            "Selfhost friendly: both ends can run on your own infrastructure"
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
            ['From the calendar', 'from', 'Events on your calendar arrive here as real events on your schedule. Nothing is written back.', false],
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
            ['LOCATION', 'The address of the venue on the event', 'Matched against the venues on your schedule, and added as one if it is new'],
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
                'q' => 'Can I use CalDAV and Google Calendar at the same time?',
                'a' => 'Yes. They are separate connections with separate directions on the same schedule, so you can push to one and read from the other, or run both ways on both. Each schedule you own is configured on its own, so two schedules can point at two entirely different calendars.',
            ],
        ];

        $dotSections = [
            ['top', 'The standard'],
            ['servers', 'Interoperability'],
            ['how-it-works', 'On the wire'],
            ['direction', 'Direction'],
            ['record', 'The record'],
            ['timing', 'Timing'],
            ['checks', 'Idempotence'],
            ['security', 'Security'],
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
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-proto-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="es-proto-muted text-sm font-medium tracking-wide">CalDAV, the published standard</span>
                    </div>

                    <h1 class="es-balance es-proto-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The calendar protocol</span></span>
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
    <!-- 2. Interoperability                                          -->
    <!-- ============================================================ -->
    <section id="servers" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 1</span><span>Interoperability</span></div>
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
    <!-- 3. On the wire (fixed-dark band)                             -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-proto-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-proto-sec mb-6" data-reveal><span>&sect; 2</span><span>On the wire</span></div>
                    <h2 class="es-balance mt-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        Six requests. <span class="es-proto-lit">All of them in the spec.</span>
                    </h2>
                    <p class="es-proto-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        This is the conversation, in the order it happens. A polled read opens with one more property request, which is what &sect; 6 is about. Read both once and there is no magic left in the word integration.
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
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 3</span><span>Direction</span></div>
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
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 4</span><span>Conformance</span></div>
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
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 5</span><span>Timing</span></div>
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
                    <div class="es-proto-sec mb-6" data-reveal><span>&sect; 6</span><span>Idempotence</span></div>
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
                    <div class="es-proto-sec mb-6" data-reveal><span>&sect; 7</span><span>Security considerations</span></div>
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
    <!-- 9. Both ends open                                            -->
    <!-- ============================================================ -->
    <section id="open" class="es-proto-rule scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 8</span><span>Both ends open</span></div>
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
                    <p class="es-proto-muted mb-4 text-sm leading-relaxed">Run Event Schedule on your own machine, point it at your own calendar server, and no third party is involved in your calendar at all. The usage metering that exists on the hosted service does not run on your own install, so nothing is counted and nothing is reported.</p>
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
                <li>Your audience does not need CalDAV or an account: every schedule publishes an iCal feed, and every event has its own iCal download, per date.</li>
                <li>CalDAV, Google Calendar and Outlook are independent connections. Running one does not rule out the others.</li>
            </ul>
        </div>
    </section>

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
                <div class="es-proto-sec mb-6" data-reveal><span>&sect; 9</span><span>Questions</span></div>
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
                    <div class="es-proto-sec mb-6"><span>&sect; 10</span><span>Conformance statement</span></div>
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
