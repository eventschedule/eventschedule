<x-marketing-layout>
    <x-slot name="title">Sub-Schedules | Sort One Schedule Into Sections</x-slot>
    <x-slot name="description">A sub-schedule is a tabbed divider in one drawer: it gives a run of events a name, a colour and a URL. It sorts and it points. It never hides anything. Free on every plan.</x-slot>
    <x-slot name="breadcrumbTitle">Sub-Schedules</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Sub-Schedules",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Management Software",
        "operatingSystem": "Web",
        "description": "Sort one schedule into named sections. A sub-schedule carries a name, an English name, a URL slug and a colour, and gives visitors a filter and a direct link. It organises and colour-codes; it cannot hide an event. Free on every plan.",
        "featureList": [
            "Unlimited sub-schedules on every plan",
            "A name, an English name, a URL slug and a colour per sub-schedule",
            "A colour from a fixed palette of fourteen, shown as a dot beside the event",
            "Its own URL, so you can link straight to one section of your schedule",
            "A schedule filter for visitors, with an event count beside each name scoped to the view",
            "A shareable schedule query parameter that survives a click into an event",
            "One sub-schedule per event, per schedule",
            "A newsletter segment built from one sub-schedule's ticket holders and RSVPs",
            "Passes that can be scoped to a single sub-schedule on the Pro plan",
            "Full sub-schedule CRUD through the REST API on the Pro plan"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "url": "{{ url()->current() }}",
        "keywords": "sub-schedules, event categories, filter events, schedule sections, colour coded calendar",
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
           For sub-schedules "The Sort" styles.

           CONCEPT: a card-index drawer with tabbed dividers. Every event
           you have ever entered is already in the one drawer. A
           sub-schedule is a divider you slide in: it gets a tab, a
           colour and a position somebody can be pointed at. Nothing
           leaves the drawer when you add one.

           WHY THAT METAPHOR AND NOT A SET OF FOLDERS: `Group` is
           fillable on exactly name, name_en, slug and color. There is no
           visibility flag on it, so a sub-schedule CANNOT hide an event -
           hiding is Draft (members-only) or Unlisted (link only). A
           folder implies a lid. A divider does not. The whole page is
           built to make that one true sentence unmissable, because
           several earlier marketing pages got it wrong.

           WHY NOT THE FIRST-WAVE LANE MOTIF: the old file drew thirty
           coloured bars rising and falling in three full-bleed layers.
           It said "categories" and argued nothing, and the equaliser
           shape is already house furniture. The drawer is the argument;
           the four-field record table is the proof; the proportional
           count bar is the check that nothing went missing.

           COLOUR: sky, kept from the first-wave file per the campaign
           palette rule, but pushed dark on the light ground. The old
           #0ea5e9 / #06b6d4 gradient heading measured 2.4 to 2.9 on
           white and there are no gradient headings here at all: a
           gradient is scored per stop and this page gains nothing from
           one. Light accent #0369a1 (5.43 on the page stock), bright
           #7dd3fc reserved for the dark ground and the fixed-dark bands.

           DIFFERENTIATION: /for-djs, /for-venues and /for-dance-groups
           own sky and cyan as neon and as glass. Here the material is
           filing-card stock on a cool grey desk, so the distinctiveness
           is structural and typographic rather than hue: protruding
           divider tabs, uppercase mono tab labels, tabular figures for
           every count, and a real <table> whose four rows ARE the four
           fillable columns.

           NOT A CARD CATALOGUE, AND THIS BOUNDARY IS DELIBERATE.
           /for-libraries owns "The Catalog": the manila catalogue card,
           the date-due slip and the oak drawer .es-cat-drawer, all
           pinned identical in both colour modes; /search then disclaims
           the same family in its own header ("Nothing here is a card, a
           drawer or a stamped slip"). So nothing on this page is manila,
           nothing is stamped, and the drawer BODY is never drawn as a
           dark wooden object: what is drawn is the DIVIDER, on cool grey
           card stock that inverts with the colour mode like an ordinary
           panel. The only fixed-in-both-modes surfaces here are the two
           argument bands, which are grounds rather than objects. If a
           later page wants a filing metaphor, all three of these are
           taken and the remaining distance is small.

           The DIVIDER COLOURS are the product's own preset swatches
           (role/edit.blade.php), used as fills only and never as text
           ink, so they carry no contrast obligation. The four banned
           hues in that palette are simply not drawn.

           NEVER text-gray-500 on this stock (4.34). Use .es-sort-muted,
           which measures 6.95 light and 7.66 dark.

           BLADE RULE for this block: no @supports probes. A "#" hex
           inside a parenthesised at-rule condition breaks Blade
           compilation of every later parenthesised directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-sort-page { background-color: #f3f5f8; color: #0f1720; }
        .dark .es-sort-page { background-color: #0a0f16; color: #e6ecf3; }
        .es-sort-ink { color: #0f1720; }
        .dark .es-sort-ink { color: #e6ecf3; }
        .es-sort-muted { color: #4b5560; }
        .dark .es-sort-muted { color: #98a5b3; }
        .es-sort-accent { color: #0369a1; }
        .dark .es-sort-accent { color: #7dd3fc; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-sort-lit { color: #7dd3fc; }
        .es-sort-rule { border-color: rgba(15, 23, 32, 0.1); }
        .dark .es-sort-rule { border-color: rgba(230, 236, 243, 0.12); }

        /* --- Card stock --- */
        .es-sort-card {
            border: 1px solid rgba(15, 23, 32, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-sort-card {
            border-color: rgba(230, 236, 243, 0.12);
            background: rgba(230, 236, 243, 0.04);
        }
        .es-sort-band .es-sort-card {
            border-color: rgba(230, 236, 243, 0.14);
            background: rgba(230, 236, 243, 0.05);
        }

        /* --- Fixed-dark band: the desk the drawer sits on --- */
        .es-sort-band {
            background-color: #0b131c;
            background-image: radial-gradient(120% 100% at 50% 0%, #141d28 0%, #0e1721 55%, #070c12 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 236, 243, 0.05);
        }
        /* Shared classes that otherwise flip with the colour mode inside a band. */
        .es-sort-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 236, 243, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 236, 243, 0.05) 1px, transparent 1px);
        }
        .es-sort-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-sort-band .es-claim:focus-within {
            border-color: rgba(125, 211, 252, 0.75);
            box-shadow: 0 0 0 4px rgba(125, 211, 252, 0.22);
        }
        /* Ink for text sitting directly on the band, in BOTH colour modes.
           Not .es-sort-muted: that resolves to #4b5560 in light mode, which is
           unreadable on #0b131c. */
        .es-sort-band-ink { color: #e6ecf3; }
        .es-sort-band-muted { color: #98a5b3; }

        /* --- Eyebrow --- */
        .es-sort-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4b5560;
        }
        .dark .es-sort-tag { color: #98a5b3; }
        .es-sort-band .es-sort-tag { color: #7dd3fc; }

        /* --- Section numeral, drawn as a small index tab --- */
        .es-sort-num {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.32rem 0.9rem 0.32rem 0.7rem;
            border: 1px solid rgba(15, 23, 32, 0.18);
            border-start-start-radius: 0.25rem;
            border-start-end-radius: 0.6rem;
            border-end-end-radius: 0.6rem;
            border-end-start-radius: 0.25rem;
            background: #ffffff;
            color: #0f1720;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-sort-num { border-color: rgba(230, 236, 243, 0.2); background: rgba(230, 236, 243, 0.05); color: #e6ecf3; }
        .es-sort-band .es-sort-num { border-color: rgba(230, 236, 243, 0.2); background: rgba(230, 236, 243, 0.05); color: #e6ecf3; }
        .es-sort-num::before {
            content: "";
            width: 3px;
            align-self: stretch;
            border-radius: 2px;
            background: #0369a1;
        }
        .dark .es-sort-num::before { background: #7dd3fc; }
        .es-sort-band .es-sort-num::before { background: #7dd3fc; }

        /* --- Plan tags --- */
        .es-sort-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(3, 105, 161, 0.45);
            color: #0369a1;
        }
        .dark .es-sort-plan { border-color: rgba(125, 211, 252, 0.42); color: #7dd3fc; }
        .es-sort-band .es-sort-plan { border-color: rgba(125, 211, 252, 0.42); color: #7dd3fc; }
        .es-sort-plan-pro { border-color: rgba(15, 23, 32, 0.35); color: #0f1720; }
        .dark .es-sort-plan-pro { border-color: rgba(230, 236, 243, 0.38); color: #e6ecf3; }
        .es-sort-band .es-sort-plan-pro { border-color: rgba(230, 236, 243, 0.38); color: #e6ecf3; }

        /* ==============================================================
           THE DRAWER. One column of events, with tabbed dividers slotted
           in. The tabs deliberately overhang the rows on the leading
           edge, because that overhang IS the feature: a divider is what
           you can point at from outside the drawer.
           ============================================================== */
        .es-sort-drawer {
            border: 1px solid rgba(15, 23, 32, 0.12);
            border-radius: 0.9rem;
            background: #ffffff;
            padding: 0.9rem 0.9rem 0.9rem 1.5rem;
            overflow: hidden;
        }
        .dark .es-sort-drawer {
            border-color: rgba(230, 236, 243, 0.12);
            background: rgba(230, 236, 243, 0.04);
        }
        .es-sort-drawer-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            padding-bottom: 0.7rem;
            margin-bottom: 0.55rem;
            border-bottom: 1px solid rgba(15, 23, 32, 0.1);
        }
        .dark .es-sort-drawer-head { border-bottom-color: rgba(230, 236, 243, 0.12); }

        /* A divider. The coloured edge is the sub-schedule's own colour. */
        .es-sort-div {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-inline-start: -0.85rem;
            margin-top: 0.55rem;
            margin-bottom: 0.2rem;
            padding: 0.32rem 0.7rem;
            border: 1px solid rgba(15, 23, 32, 0.12);
            border-inline-start: 4px solid var(--tab, #0369a1);
            border-start-start-radius: 0.2rem;
            border-start-end-radius: 0.5rem;
            border-end-end-radius: 0.5rem;
            border-end-start-radius: 0.2rem;
            background: #f3f5f8;
            transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.55s ease;
            transition-delay: var(--div-delay, 0s);
        }
        .dark .es-sort-div {
            border-color: rgba(230, 236, 243, 0.14);
            background: rgba(230, 236, 243, 0.06);
        }
        /* The drawer is deliberately NOT repeated inside a fixed-dark band: it is
           the hero's object and the band's argument is made in words, so there are
           no .es-sort-band overrides for the divider and row parts. */
        /* Undrawn pre-state only, gated behind the motion class. */
        html.es-anim [data-reveal]:not(.is-revealed) .es-sort-div { transform: translateX(-16px); opacity: 0; }

        .es-sort-div-tab {
            flex: 1 1 auto;
            min-width: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.64rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #0f1720;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .dark .es-sort-div-tab { color: #e6ecf3; }
        .es-sort-div-count {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.68rem;
            font-weight: 800;
            color: #4b5560;
        }
        .dark .es-sort-div-count { color: #98a5b3; }

        /* An event sitting behind a divider. */
        .es-sort-row {
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
            padding: 0.28rem 0.2rem;
        }
        .es-sort-row-date {
            flex: none;
            width: 4.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.68rem;
            color: #4b5560;
        }
        .dark .es-sort-row-date { color: #98a5b3; }
        .es-sort-row-name {
            min-width: 0;
            font-size: 0.83rem;
            font-weight: 600;
            color: #0f1720;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .dark .es-sort-row-name { color: #e6ecf3; }
        /* The colour dot the product actually paints beside an event. */
        .es-sort-pip {
            flex: none;
            align-self: center;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
        }

        /* --- The record table: one row per stored column --- */
        .es-sort-table { width: 100%; border-collapse: collapse; text-align: start; }
        .es-sort-table th, .es-sort-table td {
            padding: 0.7rem 0.5rem;
            vertical-align: top;
            border-top: 1px solid rgba(15, 23, 32, 0.09);
        }
        .dark .es-sort-table th, .dark .es-sort-table td { border-top-color: rgba(230, 236, 243, 0.1); }
        .es-sort-table thead th { border-top: 0; padding-top: 0; }
        .es-sort-table th { text-align: start; }

        /* --- Mono specimen --- */
        .es-sort-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.78rem;
            letter-spacing: 0.01em;
        }
        .es-sort-url {
            display: block;
            padding: 0.55rem 0.7rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(15, 23, 32, 0.1);
            background: #f3f5f8;
            word-break: break-all;
        }
        .dark .es-sort-url { border-color: rgba(230, 236, 243, 0.12); background: rgba(230, 236, 243, 0.05); }
        .es-sort-band .es-sort-url { border-color: rgba(230, 236, 243, 0.12); background: rgba(230, 236, 243, 0.05); }

        /* --- The count bar: the tabs add up to the drawer --- */
        .es-sort-bar {
            display: flex;
            height: 1.6rem;
            gap: 2px;
            border-radius: 0.35rem;
            overflow: hidden;
        }
        /* Each segment grows in proportion to its own count, so the five
           shares stay exact after the 2px gaps are taken out of the row.
           A fixed `width: N%` plus gaps overflows the bar and the last
           tab is silently clipped, which is the one thing a bar captioned
           "the tabs have to add up" must not do. */
        .es-sort-seg {
            flex-grow: var(--seg-share, 1);
            flex-shrink: 1;
            flex-basis: 0;
            min-width: 0;
            transform-origin: left center;
            transition: transform 0.85s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--seg-delay, 0s);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-sort-seg { transform: scaleX(0); }
        .es-sort-key {
            display: inline-block;
            flex: none;
            width: 0.7rem;
            height: 0.7rem;
            border-radius: 0.18rem;
        }
        /* The Total row's swatch is a neutral placeholder, not a divider colour.
           Hard-coded ink at 12% vanished on the dark card, so it is a real rule
           with a value per mode instead of an inline style. */
        .es-sort-key-total { background: rgba(15, 23, 32, 0.14); }
        .dark .es-sort-key-total { background: rgba(230, 236, 243, 0.18); }

        /* --- The filter specimen: the control a visitor actually sees --- */
        .es-sort-select {
            border: 1px solid rgba(15, 23, 32, 0.16);
            border-radius: 0.45rem;
            background: #ffffff;
            overflow: hidden;
        }
        .dark .es-sort-select { border-color: rgba(230, 236, 243, 0.18); background: rgba(230, 236, 243, 0.05); }
        .es-sort-band .es-sort-select { border-color: rgba(230, 236, 243, 0.18); background: rgba(230, 236, 243, 0.05); }
        .es-sort-opt {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.5rem 0.7rem;
            font-size: 0.83rem;
            border-top: 1px solid rgba(15, 23, 32, 0.08);
        }
        .dark .es-sort-opt { border-top-color: rgba(230, 236, 243, 0.1); }
        /* Pinned inside a fixed-dark band: without this the separator flips with
           the colour mode and the same physical control renders three different
           hairlines in light mode than in dark. */
        .es-sort-band .es-sort-opt { border-top-color: rgba(230, 236, 243, 0.1); }
        .es-sort-opt-head { border-top: 0; font-weight: 700; }

        /* --- Chips --- */
        .es-sort-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(15, 23, 32, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4b5560;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
        }
        .dark .es-sort-chip {
            border-color: rgba(230, 236, 243, 0.16);
            background: rgba(230, 236, 243, 0.05);
            color: #aab6c2;
        }

        /* --- Links and buttons --- */
        .es-sort-link { color: #0369a1; }
        .es-sort-link:hover { color: #0f1720; }
        .dark .es-sort-link { color: #7dd3fc; }
        .dark .es-sort-link:hover { color: #e6ecf3; }

        /* The button INK lives here, not in a `dark:text-[#hex]` utility: an
           arbitrary value that is not already in the built marketing CSS is
           silently dropped, which left white text on #7dd3fc (1.67) in dark
           mode. White on #0369a1 measures 5.93; #06202e on #7dd3fc, 10.05. */
        .es-sort-btn {
            background-color: #0369a1;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(3, 105, 161, 0.5);
        }
        .es-sort-btn:hover { background-color: #075985; box-shadow: 0 22px 44px -14px rgba(3, 105, 161, 0.6); }
        .dark .es-sort-btn { background-color: #7dd3fc; color: #06202e; }
        .dark .es-sort-btn:hover { background-color: #a5e2ff; }

        /* Dot-nav tooltip. Same reason: `dark:bg-[#131a23]` never reached the
           bundle, so eleven labels rendered grey-on-white at 1.47. */
        .es-sort-tip {
            border: 1px solid rgba(15, 23, 32, 0.14);
            background: #ffffff;
            color: #0f1720;
        }
        .dark .es-sort-tip {
            border-color: rgba(230, 236, 243, 0.14);
            background: #131a23;
            color: #e6ecf3;
        }

        /* --- Hover treatment for FAQ / related cards --- */
        .es-sort-hover:hover { border-color: rgba(3, 105, 161, 0.45); }
        .dark .es-sort-hover:hover { border-color: rgba(125, 211, 252, 0.45); }
        .es-sort-hover:hover .es-sort-hover-title,
        .es-sort-hover:hover .es-sort-hover-arrow { color: #0369a1; }
        .dark .es-sort-hover:hover .es-sort-hover-title,
        .dark .es-sort-hover:hover .es-sort-hover-arrow { color: #7dd3fc; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(3, 105, 161, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(125, 211, 252, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(3, 105, 161, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(125, 211, 252, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0369a1; }
        .dark .es-dot.is-active .es-dot-pip { background: #7dd3fc; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-sort-page a:focus-visible,
        #es-sort-page summary:focus-visible,
        #es-sort-page button:focus-visible {
            outline: 2px solid #0369a1;
            outline-offset: 3px;
        }
        .dark #es-sort-page a:focus-visible,
        .dark #es-sort-page summary:focus-visible,
        .dark #es-sort-page button:focus-visible {
            outline-color: #7dd3fc;
        }
        .es-sort-band a:focus-visible,
        .es-sort-band summary:focus-visible,
        .es-sort-band button:focus-visible {
            outline-color: #7dd3fc !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-sort-div, .es-sort-seg {
                transition: none !important;
                transform: none !important;
                opacity: 1 !important;
            }
        }
    </style>

    @php
        // One arts centre's drawer. The colours are the product's own preset
        // swatches (role/edit.blade.php), minus the four hues the WP never uses.
        // The counts are the point of the page, so they have to add up: the five
        // dividers below total the 24 events in the drawer.
        $dividers = [
            ['Live Music',       'live-music',       '#0EA5E9', 7],
            ['Comedy',           'comedy',           '#F97316', 4],
            ['Workshops',        'workshops',        '#22C55E', 6],
            ['Family Matinees',  'family-matinees',  '#EAB308', 3],
            ['Film Club',        'film-club',        '#6B7280', 4],
        ];
        $drawerTotal = array_sum(array_column($dividers, 3));

        // The hero drawer shows three dividers with the events filed behind them.
        $drawerRows = [
            ['Live Music', '#0EA5E9', 7, [
                ['Fri Sep 04', 'Ember & Ash'],
                ['Fri Sep 11', 'The Longshore Four'],
            ]],
            ['Comedy', '#F97316', 4, [
                ['Tue Sep 08', 'Tuesday Night Standup'],
            ]],
            ['Workshops', '#22C55E', 6, [
                ['Wed Sep 09', 'Beginner Ukulele'],
                ['Sat Sep 12', 'Screenprinting, all ages'],
            ]],
        ];

        // Group::$fillable, in full. Four columns is the whole record.
        $record = [
            ['name', 'The name on the tab', 'Live Music', 'What visitors read in the filter dropdown, and what you pick from on the event form.'],
            ['name_en', 'An English name', 'Live Music', 'Filled in for you when your schedule is written in another language, or typed by hand.'],
            ['slug', 'The URL slug', 'live-music', 'Built from the name, editable afterwards. This is the part you can point somebody at.'],
            ['color', 'One of fourteen colours', '#0EA5E9', 'Painted as a dot beside the event on your calendar and list views.'],
        ];

        $faqs = [
            [
                'q' => 'What is a sub-schedule?',
                'a' => 'It is a named section inside one schedule. Create sub-schedules like "Live Music", "Comedy" and "Workshops", file each event under one of them, and your schedule gains a filter, a colour dot per event, and a direct URL for each section. Everything still lives on the same link.',
            ],
            [
                'q' => 'Can a sub-schedule hide events from the public?',
                'a' => 'No, and this is worth being exact about. A sub-schedule stores a name, an English name, a URL slug and a colour, and nothing else. There is no visibility setting on it, so filing an event under one never takes it off your public schedule. If you want an event hidden, set its visibility instead. Draft keeps it members-only until you publish it and is free on every plan; Internal and Unlisted go further and are on the Enterprise plan.',
            ],
            [
                'q' => 'Can one event be in two sub-schedules?',
                'a' => 'Not within the same schedule: an event is filed under one sub-schedule there, chosen from a dropdown on the event form. Where an event appears on more than one schedule, say a venue\'s and a curator\'s, each schedule files it under its own sub-schedule independently, because the link between the two is stored per schedule.',
            ],
            [
                'q' => 'How do visitors filter?',
                'a' => 'A "Schedule" dropdown appears in the filter panel on your public calendar, listing every sub-schedule with a count beside it, plus a "Show all" option. The count is for the view the visitor is on: the month showing in the calendar, or the whole upcoming run in list view. It only appears once you have more than one sub-schedule, since a single divider is not a choice.',
            ],
            [
                'q' => 'Can I link straight to one sub-schedule?',
                'a' => 'Yes. Each one gets its own address at your-schedule.eventschedule.com/its-slug, and the page loads already filtered to that section. There is also a schedule query parameter, which is what the filter adds to event links so a visitor who clicks through and comes back is still looking at the section they chose.',
            ],
            [
                'q' => 'Can I nest sub-schedules?',
                'a' => 'No. Sub-schedules are a single level of sorting inside a schedule. If you need a genuinely separate public page, create a second schedule rather than nesting.',
            ],
            [
                'q' => 'Are sub-schedules free?',
                'a' => 'Yes, on every plan, and there is no cap on how many you create. Two things that touch sub-schedules are on the Pro plan at $5 a month: a pass scoped to a single sub-schedule, and the REST API that creates, lists, updates and deletes them.',
            ],
        ];

        $dotSections = [
            ['top', 'The drawer'],
            ['record', 'What it stores'],
            ['lid', 'Not a lid'],
            ['colour', 'The colour'],
            ['count', 'The filter'],
            ['point', 'The address'],
            ['pro', 'On the Pro plan'],
            ['rest', 'Everything else'],
            ['who', 'Who sorts'],
            ['faq', 'Questions'],
            ['claim', 'Slide it in'],
        ];
    @endphp

    <div id="es-sort-page" class="es-sort-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the drawer                                          -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 32%, rgba(3, 105, 161, 0.2), rgba(3, 105, 161, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 42%, rgba(125, 211, 252, 0.14), rgba(125, 211, 252, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-sort-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h9" />
                        </svg>
                        <span class="es-sort-muted text-sm font-medium tracking-wide">Sub-schedules</span>
                    </div>

                    <h1 class="es-balance es-sort-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">One drawer.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Now with <span class="es-sort-accent">dividers.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-sort-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A sub-schedule is a tabbed divider you slide into the schedule you already have. It gives a run of events a name, a colour and an address people can be sent to. It does not take anything out of the drawer.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#record" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See what a divider stores
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-sort-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The drawer itself. Dividers overhang the events filed behind them. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-sort-drawer">
                        <div class="es-sort-drawer-head">
                            <span class="es-sort-mono es-sort-ink font-semibold">riverside-arts</span>
                            <span class="es-sort-mono es-sort-muted">September, {{ $drawerTotal }} events</span>
                        </div>

                        @foreach ($drawerRows as $dIndex => [$dName, $dColor, $dCount, $dRows])
                            <div class="es-sort-div" style="--tab: {{ $dColor }}; --div-delay: {{ 0.12 + $dIndex * 0.14 }}s;">
                                <span class="es-sort-div-tab">{{ $dName }}</span>
                                <span class="es-sort-div-count">{{ $dCount }}</span>
                            </div>
                            @foreach ($dRows as [$rDate, $rName])
                                <div class="es-sort-row">
                                    <span class="es-sort-pip" style="background: {{ $dColor }};" aria-hidden="true"></span>
                                    <span class="es-sort-row-date">{{ $rDate }}</span>
                                    <span class="es-sort-row-name">{{ $rName }}</span>
                                </div>
                            @endforeach
                        @endforeach

                        <p class="es-sort-muted mt-5 border-t pt-4 text-xs es-sort-rule">
                            Five dividers, {{ $drawerTotal }} events, one link. Add a divider and the drawer keeps every card that was already in it.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What people actually name their dividers -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Live Music', 'Comedy', 'Workshops', 'Film Club', 'Matinees', 'Youth', 'Markets', 'Talks', 'Classes', 'Late Shows'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-sort-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The record: everything a sub-schedule stores               -->
    <!-- ============================================================ -->
    <section id="record" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-sort-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The record</p>
                <h2 class="es-balance es-sort-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A divider stores <span class="es-sort-accent">four things.</span>
                </h2>
                <p class="es-sort-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    That is the entire record. Knowing what is on the card is the fastest way to know what a sub-schedule can and cannot do for you.
                </p>
            </div>

            <div class="es-sort-card p-6 sm:p-8" data-reveal="panel">
                <table class="es-sort-table">
                    <caption class="sr-only">The four fields stored on a sub-schedule, with an example value and what each one does</caption>
                    <thead>
                        <tr class="es-sort-tag">
                            <th scope="col">Field</th>
                            <th scope="col">Example</th>
                            <th scope="col" class="hidden sm:table-cell">What it does</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($record as [$fKey, $fLabel, $fExample, $fDoes])
                            <tr>
                                <th scope="row" class="align-top">
                                    <span class="es-sort-mono es-sort-accent font-bold">{{ $fKey }}</span>
                                    <span class="es-sort-muted mt-0.5 block text-xs font-normal">{{ $fLabel }}</span>
                                </th>
                                <td class="es-sort-mono es-sort-ink align-top">
                                    @if ($fKey === 'color')
                                        <span class="inline-flex items-center gap-2">
                                            <span class="es-sort-key" style="background: {{ $fExample }};" aria-hidden="true"></span>
                                            {{ $fExample }}
                                        </span>
                                    @else
                                        {{ $fExample }}
                                    @endif
                                    <span class="es-sort-muted mt-1 block text-xs sm:hidden">{{ $fDoes }}</span>
                                </td>
                                <td class="es-sort-muted hidden align-top text-sm sm:table-cell">{{ $fDoes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-sort-muted mt-6 border-t pt-4 text-sm es-sort-rule">
                    There is no fifth field. No visibility switch, no capacity, no separate design, no price. A sub-schedule is a label and a colour with an address attached, which is exactly why it is free and why there is no limit on how many you make.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. A divider is not a lid (fixed-dark band)                   -->
    <!-- ============================================================ -->
    <section id="lid" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-sort-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 28%, rgba(125, 211, 252, 0.13), rgba(125, 211, 252, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-sort-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The honest bit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A divider is <span class="es-sort-lit">not a lid.</span>
                    </h2>
                    <p class="es-sort-band-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Filing an event under a sub-schedule never removes it from your public schedule. If that is what you were after, the setting you want is on the event, not on the divider.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-sort-card p-6" data-reveal="panel">
                        <p class="es-sort-tag mb-3">It sorts</p>
                        <h3 class="es-sort-band-ink mb-2 text-lg font-bold">Everything stays listed</h3>
                        <p class="es-sort-band-muted text-sm">Your visitors still land on one calendar with all {{ $drawerTotal }} events on it. The dividers give them a way through, not a smaller pile.</p>
                    </div>
                    <div class="es-sort-card p-6" data-reveal="panel">
                        <p class="es-sort-tag mb-3">Draft hides</p>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-sort-band-ink text-lg font-bold">Members-only, until you say</h3>
                            <span class="es-sort-plan">Free</span>
                        </div>
                        <p class="es-sort-band-muted text-sm">An event set to Draft is visible to you and your schedule's members and to nobody else, until you publish it. That is the switch that hides something, and it costs nothing.</p>
                    </div>
                    <div class="es-sort-card p-6" data-reveal="panel">
                        <p class="es-sort-tag mb-3">Two more doors</p>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-sort-band-ink text-lg font-bold">Internal and Unlisted</h3>
                            <span class="es-sort-plan es-sort-plan-pro">Enterprise</span>
                        </div>
                        <p class="es-sort-band-muted text-sm">Internal is permanently members-only with no publish button. Unlisted is kept out of every listing while the direct link still opens. Both are on the Enterprise plan.</p>
                    </div>
                </div>

                <p class="es-sort-band-muted mx-auto mt-10 max-w-2xl text-center" data-reveal>
                    Sorting and hiding are two different jobs, and mixing them up is how a schedule ends up with an event nobody can find and nobody meant to bury.
                    <a href="{{ marketing_url('/features/private-events') }}" class="es-sort-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        How visibility works
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The colour                                                 -->
    <!-- ============================================================ -->
    <section id="colour" class="scroll-mt-24 border-t py-20 es-sort-rule lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-sort-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The colour</p>
                    <h2 class="es-balance es-sort-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        One dot, and the month <span class="es-sort-accent">reads itself.</span>
                    </h2>
                    <p class="es-sort-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Pick a colour from a fixed palette of fourteen and it shows up as a small dot beside every event filed under that divider, in the calendar grid, the list view and on a phone. Nobody has to read the titles to see that the second week is all workshops.
                    </p>
                    <ul class="es-sort-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-sort-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A fixed palette rather than a colour wheel, so two dividers never end up a shade apart.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-sort-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Leaving the colour blank is fine. A divider with no colour simply paints no dot.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-sort-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>If the event also carries a category with its own colour, the category wins the dot. Worth knowing before you wonder why one row looks wrong.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-sort-card p-6 sm:p-7">
                        <p class="es-sort-tag mb-4">September, filed</p>
                        @foreach ([['Fri Sep 04', 'Ember & Ash', '#0EA5E9'], ['Sat Sep 05', 'Screenprinting, all ages', '#22C55E'], ['Tue Sep 08', 'Tuesday Night Standup', '#F97316'], ['Wed Sep 09', 'Beginner Ukulele', '#22C55E'], ['Sun Sep 13', 'The Red Balloon, 11am', '#EAB308'], ['Thu Sep 17', 'Film Club: Rear Window', '#6B7280']] as [$cDate, $cName, $cColor])
                            <div class="es-sort-row">
                                <span class="es-sort-pip" style="background: {{ $cColor }};" aria-hidden="true"></span>
                                <span class="es-sort-row-date">{{ $cDate }}</span>
                                <span class="es-sort-row-name">{{ $cName }}</span>
                            </div>
                        @endforeach
                        <p class="es-sort-muted mt-5 border-t pt-4 text-xs es-sort-rule">
                            Same list, same order, same link. The only thing the dividers added was a colour on the left.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The filter and the count                                   -->
    <!-- ============================================================ -->
    <section id="count" class="scroll-mt-24 border-t py-20 es-sort-rule lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-sort-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The filter</p>
                <h2 class="es-balance es-sort-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The tabs have to <span class="es-sort-accent">add up.</span>
                </h2>
                <p class="es-sort-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Your visitors get a Schedule dropdown with a count beside every name, and the counts are scoped to what is in front of them: the month showing in the calendar, or the whole upcoming run in list view. So they always total the view, which is the thing that makes them worth reading.
                </p>
            </div>

            <div class="es-sort-card p-6 sm:p-8" data-reveal="panel">
                <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                    <span class="es-sort-tag">September, in view</span>
                    <span class="es-sort-mono es-sort-ink font-bold"><span data-count-to="{{ $drawerTotal }}">{{ $drawerTotal }}</span> events</span>
                </div>

                <div class="es-sort-bar" aria-hidden="true">
                    @foreach ($dividers as $sIndex => [$sName, $sSlug, $sColor, $sCount])
                        <div class="es-sort-seg" style="--seg-share: {{ $sCount }}; background: {{ $sColor }}; --seg-delay: {{ 0.1 + $sIndex * 0.11 }}s;"></div>
                    @endforeach
                </div>

                <div class="mt-4 grid gap-x-6 gap-y-2 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($dividers as [$sName, $sSlug, $sColor, $sCount])
                        <div class="flex items-center gap-2.5 border-t pt-2 es-sort-rule">
                            <span class="es-sort-key" style="background: {{ $sColor }};" aria-hidden="true"></span>
                            <span class="es-sort-ink flex-1 text-sm font-semibold">{{ $sName }}</span>
                            <span class="es-sort-mono es-sort-muted font-bold">{{ $sCount }}</span>
                        </div>
                    @endforeach
                    <div class="flex items-center gap-2.5 border-t pt-2 es-sort-rule">
                        <span class="es-sort-key es-sort-key-total" aria-hidden="true"></span>
                        <span class="es-sort-muted flex-1 text-sm font-semibold">Total</span>
                        <span class="es-sort-mono es-sort-ink font-bold">{{ $drawerTotal }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="es-sort-card p-6 sm:p-7" data-reveal="panel">
                    <h3 class="es-sort-ink mb-4 text-lg font-bold">What the visitor sees</h3>
                    <div class="es-sort-select" aria-hidden="true">
                        <div class="es-sort-opt es-sort-opt-head">
                            <span class="es-sort-ink flex-1">Show all</span>
                        </div>
                        @foreach ($dividers as [$sName, $sSlug, $sColor, $sCount])
                            <div class="es-sort-opt">
                                <span class="es-sort-key" style="background: {{ $sColor }};"></span>
                                <span class="es-sort-ink flex-1">{{ $sName }}</span>
                                <span class="es-sort-mono es-sort-muted">{{ $sCount }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-sort-muted mt-4 text-sm">A single dropdown in the filter panel, alongside the other filters your schedule offers.</p>
                </div>

                <div class="es-sort-card p-6 sm:p-7" data-reveal="panel">
                    <h3 class="es-sort-ink mb-4 text-lg font-bold">When it appears</h3>
                    <p class="es-sort-muted mb-4 text-sm leading-relaxed">
                        The Schedule filter shows up once your schedule has more than one sub-schedule. With a single divider there is nothing to choose between, so the control stays out of the way rather than offering a pointless list of one.
                    </p>
                    <p class="es-sort-muted text-sm leading-relaxed">
                        The counts are worked out from the events already in front of the visitor rather than stored anywhere, so they move as somebody pages through the months, and a section reading zero in September means nothing is filed into it in September, not that the section is empty.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The address                                                -->
    <!-- ============================================================ -->
    <section id="point" class="scroll-mt-24 border-t py-20 es-sort-rule lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-sort-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The address</p>
                <h2 class="es-balance es-sort-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Somewhere to <span class="es-sort-accent">point people.</span>
                </h2>
                <p class="es-sort-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A tab you cannot point at is not much of a tab. Every sub-schedule gets its own address, and the filter carries itself through a click into an event and back out again.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                <div class="es-sort-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-sort-ink text-lg font-bold">Its own page</h3>
                        <span class="es-sort-plan">Free</span>
                    </div>
                    <p class="es-sort-muted mb-5 text-sm leading-relaxed">
                        The slug hangs off your schedule's address. Open it and the calendar is already filtered to that section, straight from the server, so it works for a link in a printed programme as well as it works in an email.
                    </p>
                    <div class="mt-auto space-y-2">
                        @foreach (['live-music', 'workshops', 'film-club'] as $uSlug)
                            <span class="es-sort-url es-sort-mono es-sort-muted">riverside-arts.eventschedule.com/<span class="es-sort-accent font-bold">{{ $uSlug }}</span></span>
                        @endforeach
                    </div>
                </div>

                <div class="es-sort-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-sort-ink text-lg font-bold">A filter that travels</h3>
                        <span class="es-sort-plan">Free</span>
                    </div>
                    <p class="es-sort-muted mb-5 text-sm leading-relaxed">
                        When somebody has a section selected, every event link on the page carries a schedule parameter. Share that URL and whoever opens it sees the same filtered view you were looking at.
                    </p>
                    <div class="mt-auto space-y-2">
                        <span class="es-sort-url es-sort-mono es-sort-muted">riverside-arts.eventschedule.com/<span class="es-sort-ink">ember-and-ash</span>?<span class="es-sort-accent font-bold">schedule=live-music</span></span>
                        <p class="es-sort-muted text-xs">The parameter is read on the way in, so the visitor lands with the section already chosen rather than back at the full drawer.</p>
                    </div>
                </div>
            </div>

            <p class="es-sort-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Slugs are generated from the name and stay editable, so "Family Matinees" becomes /family-matinees on its own and you only touch it if you want something shorter.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. On the Pro plan (fixed-dark band)                          -->
    <!-- ============================================================ -->
    <section id="pro" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-sort-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 70%, rgba(125, 211, 252, 0.12), rgba(125, 211, 252, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-sort-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Two things upstairs</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Sell one section. <span class="es-sort-lit">Or script it.</span>
                    </h2>
                    <p class="es-sort-band-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Sub-schedules themselves are free. Two things that build on them sit on the Pro plan at five dollars a month.
                    </p>
                </div>

                <div class="grid items-start gap-6 lg:grid-cols-2" data-reveal-group="110">
                    <div class="es-sort-card p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-sort-band-ink text-lg font-bold">A pass for one section</h3>
                            <span class="es-sort-plan es-sort-plan-pro">Pro</span>
                        </div>
                        <p class="es-sort-band-muted mb-5 text-sm leading-relaxed">
                            A pass can be scoped to a single sub-schedule rather than to one event or to everything you run. Sell a workshops pass that covers the workshops and nothing else, with the covered events resolved from the divider rather than from a list you have to keep updating.
                        </p>
                        <div class="es-sort-select" aria-hidden="true">
                            <div class="es-sort-opt es-sort-opt-head">
                                <span class="es-sort-band-ink flex-1">Pass covers</span>
                            </div>
                            <div class="es-sort-opt">
                                <span class="es-sort-band-muted flex-1">Every event on this schedule</span>
                            </div>
                            <div class="es-sort-opt">
                                <span class="es-sort-key" style="background: #22C55E;"></span>
                                <span class="es-sort-band-ink flex-1 font-semibold">One sub-schedule: Workshops</span>
                            </div>
                            <div class="es-sort-opt">
                                <span class="es-sort-band-muted flex-1">Events you pick by hand</span>
                            </div>
                        </div>
                        <p class="es-sort-band-muted mt-4 text-xs">Passes are sold alongside single tickets, and Event Schedule charges zero platform fees on either.</p>
                    </div>

                    <div class="es-sort-card p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-sort-band-ink text-lg font-bold">Sorted by script</h3>
                            <span class="es-sort-plan es-sort-plan-pro">Pro</span>
                        </div>
                        <p class="es-sort-band-muted mb-5 text-sm leading-relaxed">
                            The REST API creates, lists, updates and deletes sub-schedules, and an event can be filed as it is created by passing the divider's slug. If your programme comes out of another system, it can arrive already sorted.
                        </p>
                        <div class="es-sort-url es-sort-mono es-sort-band-muted">
                            <span class="es-sort-lit font-bold">POST</span> /api/events<br>
                            <span class="es-sort-band-ink">"name"</span>: "Beginner Ukulele",<br>
                            <span class="es-sort-band-ink">"schedule"</span>: "<span class="es-sort-lit font-bold">workshops</span>"
                        </div>
                        <p class="es-sort-band-muted mt-4 text-xs">An unknown slug is rejected rather than quietly filed under nothing, so a typo in a script surfaces immediately.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                     -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-sort-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-sort-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the drawer.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-sort-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sort-ink text-xl font-bold">Filing an event takes one dropdown</h3>
                                <span class="es-sort-plan">Free</span>
                            </div>
                            <p class="es-sort-muted mb-4">The event form carries a Schedule field listing your sub-schedules. Pick one, or leave it empty and the event simply sits in the drawer without a divider in front of it.</p>
                            <p class="es-sort-muted text-sm">Changing your mind later is the same dropdown. Nothing about the event, its tickets or its URL depends on which divider it sits behind.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-sort-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sort-ink text-xl font-bold">Two names, one tab</h3>
                                <span class="es-sort-plan">Free</span>
                            </div>
                            <p class="es-sort-muted">A sub-schedule keeps a second, English name for schedules written in another language. It is filled in for you when you save, and you can overwrite it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-sort-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sort-ink text-xl font-bold">Two schedules, two filings</h3>
                                <span class="es-sort-plan">Free</span>
                            </div>
                            <p class="es-sort-muted">Where one event appears on two schedules, a venue's and a curator's say, each files it under its own divider. The filing is stored per schedule, so neither of you overwrites the other.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-sort-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sort-ink text-xl font-bold">It travels with the embed</h3>
                                <span class="es-sort-plan">Free</span>
                            </div>
                            <p class="es-sort-muted mb-4">Embed your calendar on the site you already run and the same filter comes with it, dividers, counts and colour dots included. The embed renders the same calendar as your public page, so the sorting is never set up twice.</p>
                            <p class="es-sort-muted text-sm">
                                The embed, the built-in analytics and two-way sync with Google, Outlook and CalDAV are all free.
                                <a href="{{ marketing_url('/features/embed-calendar') }}" class="es-sort-link font-medium hover:underline">How the embed works</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-sort-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sort-ink text-xl font-bold">Removing a divider is safe</h3>
                                <span class="es-sort-plan">Free</span>
                            </div>
                            <p class="es-sort-muted">Delete a sub-schedule and its events stay exactly where they were, simply unfiled. The drawer never loses a card because you took a tab out.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-sort-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sort-ink text-xl font-bold">A divider is also a mailing segment</h3>
                                <span class="es-sort-plan">Free</span>
                            </div>
                            <p class="es-sort-muted mb-4">A newsletter segment can be built from a sub-schedule: choose one and the segment collects everybody holding a ticket or an RSVP for an event filed under it. Write to the people who came to the workshops without writing to everyone.</p>
                            <p class="es-sort-muted text-sm">Paste the section's own link into the email and whoever clicks lands on that section rather than on the whole year. Newsletters are free at 10 emails a month, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Who sorts                                                  -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t py-20 es-sort-rule lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-sort-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Anyone with more than <span class="es-sort-accent">one kind of night</span>
                </h2>
                <p class="es-sort-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    If a stranger could not guess your programme from a single scroll, you have dividers to slide in.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ([
                    ['Venues running two kinds of night', 'The ticketed gigs and the free sessions run at different scales and draw different people. One divider each and the two stop competing for the same scroll.'],
                    ['Conferences and festivals', 'Tracks, workshops and the keynote strand each get a tab, so a delegate can pull up one track and leave the rest alone.'],
                    ['Community and arts centres', 'Classes, film club, family matinees and the hall hire calendar sit together on one link and still read separately.'],
                    ['Recurring series', 'The weekly quiz, the monthly market and the seasonal fair are three strands of the same year, not three schedules to maintain.'],
                    ['Curators of other people\'s events', 'Sort what arrives into the sections of your guide, then send each section to the readers who asked for it.'],
                    ['Teachers and studios', 'Beginner, intermediate and drop-in are the three questions every new student asks. Answer them with three tabs.'],
                ] as $uIndex => [$uTitle, $uBody])
                    <div class="es-sort-card flex flex-col p-6" data-reveal>
                        <div class="mb-4 flex items-center gap-2.5">
                            <span class="es-sort-key" style="background: {{ ['#0EA5E9', '#F97316', '#22C55E', '#EAB308', '#6B7280', '#14B8A6'][$uIndex] }};" aria-hidden="true"></span>
                            <span class="es-sort-tag">{{ str_pad($uIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h3 class="es-sort-ink mb-2 text-lg font-bold">{{ $uTitle }}</h3>
                        <p class="es-sort-muted text-sm leading-relaxed">{{ $uBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 border-t py-20 es-sort-rule lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-sort-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Cut the tabs', 'Open your schedule, go to Customize and add a sub-schedule for each strand of your programme. Give each one a name and a colour.'], ['02', 'File the events', 'Pick a sub-schedule on the event form. Existing events can be filed from the same dropdown whenever you next edit them.'], ['03', 'Point at a tab', 'Copy a sub-schedule URL out of the admin panel and use it wherever that section needs its own link.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-sort-card p-7" data-reveal="panel">
                        <div class="es-sort-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-sort-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-sort-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="es-sort-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                The whole thing is described step by step in the
                <a href="{{ route('marketing.docs.creating_schedules') }}" class="es-sort-link font-medium hover:underline">schedule setup guide</a>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Key features                                              -->
    <!-- ============================================================ -->
    <section class="border-t py-20 es-sort-rule">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-sort-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card
                        name="Private Events"
                        description="Draft, Internal and Unlisted: the settings that actually hide an event"
                        :url="marketing_url('/features/private-events')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Put the sorted calendar on the website you already have"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Ticketing"
                        description="Sell single tickets or a pass scoped to one sub-schedule"
                        :url="marketing_url('/features/ticketing')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Email the people who follow your schedule when a new strand opens"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="green"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-sort-link inline-flex items-center font-medium hover:underline">
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
    <!-- 12. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="border-t py-16 es-sort-rule">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-sort-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues'], ['/for-curators', 'Curators'], ['/for-community-centers', 'Community Centers'], ['/for-farmers-markets', 'Farmers Markets']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-sort-hover es-sort-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-sort-hover-title es-sort-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-sort-hover-arrow es-sort-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-sort-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-sort-num mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance es-sort-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-sort-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they start cutting tabs.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-sort-hover es-sort-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-sort-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-sort-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-sort-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-sort-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-sort-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 14. Finale                                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-sort-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-sort-tag mb-4">Free on every plan</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Slide the first divider in.
                    </h2>
                    <p class="es-sort-band-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        A name, a colour, a slug. It sorts and it points, it never hides, and the drawer keeps every card that was already in it. Unlimited dividers, unlimited events, nothing to pay.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-sort-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-sort-band-muted mt-6 text-sm">No credit card required</p>
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
                        <span class="es-sort-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
