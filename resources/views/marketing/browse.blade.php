<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Browse Upcoming Events | Event Schedule</x-slot>
    <x-slot name="description">Upcoming live music, comedy, classes, markets and meetups, soonest first. Free to browse, no account needed. Search by event, city or schedule.</x-slot>
    <x-slot name="breadcrumbTitle">Browse</x-slot>

    {{-- Structured data: list only the publicly visible events --}}
    <x-slot name="structuredData">
    @php
        $itemListElements = [];
        $pos = 1;
        foreach ($events as $e) {
            $u = $e->getGuestUrl();
            if (! $u) {
                continue;
            }
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'url' => $u,
                'name' => $e->name,
            ];
        }
    @endphp
    @if(count($itemListElements))
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {{-- JSON_HEX_TAG is load-bearing: this block is echoed raw, and JSON_UNESCAPED_SLASHES
         means a closing script tag inside an event name would otherwise terminate the
         element and let the rest of the name run as markup. Escaping < and > costs
         nothing - JSON-LD consumers decode them back to the same string. --}}
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Upcoming events on Event Schedule',
        'url' => url('/browse'),
        'itemListElement' => $itemListElements,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
    @endif
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
           Browse "The Newsstand" styles.

           The visual concept is a newsstand: other people's events, set
           out so a passer-by can read them at a glance. Devices: the
           front-page sheet with its fold crease (hero), the wire rail
           every result grid sits on, the price stamp, the mono folio
           lines, the section plates, and the blank plate in the finale.

           THE COPY IS DELIBERATELY PLAIN. The motif is carried entirely
           by the visuals - no headline, label, counter, empty state,
           tooltip or alt string uses newsstand vocabulary, because a
           visitor looking for something to go to should not have to
           decode a metaphor to use a search box. Rack / edition /
           masthead / kiosk wording was removed on purpose in 2026-08.
           Do not "restore" it to the text.

           NOR DOES THE PAGE EXPLAIN ITSELF. This is a listing page, not
           a product tour: it does not describe its own query, its own
           limit, or how events come to be on it. Two sections that did
           exactly that (a "nobody curates this page" band and a table
           of the six query checks) were deleted in 2026-08 for that
           reason. If a fact is worth stating, it belongs in the FAQ,
           where the reader asked for it.

           COLOUR: the page keeps the press blue it already had. That is
           deliberate and is not a hue grab - /browse is a reader-facing
           discovery page rather than an audience pitch, so the brand's own
           blue is the honest ink, and every hue the rebuild campaign has
           claimed elsewhere (cyan/sky, amber, rose, copper, wine, rust,
           the greens, sepia) stays untouched.

           ANTI-COLLISION, MEASURED RATHER THAN ASSUMED. Print grammar is
           NOT this page's distinctive device and must not be claimed as
           one: /for-curators ("The Listings") already owns the newsprint
           nameplate, the rule grammar and the dotted leaders on a cream
           stock, /about ("The Colophon") owns the serif paper leaf with
           signature marks, and mono tabular folio lines appear on about
           fifty WP pages, i.e. they are house furniture. What is this
           page's own is THE SHELF: an inset shelf with a wire rail above
           and below every result grid (local, federated and
           admin-hidden), the fold crease on the front-page sheet, and
           the blank plate in the finale. Sharpen those three; do not
           reach for more newsprint.

           DELIBERATELY NOT PINNED: the sheet is not a fixed physical
           object. The paper changes with the colour mode and only the
           ink stays constant. The .es-news-band panel in the finale IS
           pinned (dark in both modes), so it carries
           .grid-overlay / .animate-shimmer / .es-claim:focus-within
           overrides after the base rules.

           NEVER use text-gray-500 on this page: on a cool paper ground
           #6b7280 measures about 4.4. Use .es-news-muted (#4d545e -
           6.9 on the page ground, 7.6 on a white sheet).

           No @supports() probes in this block: a "#" hex inside a
           parenthesized at-rule condition breaks Blade compilation of
           every later parenthesized directive.
           ============================================================== */

        /* --- Stock and ink --- */
        .es-news-page { background-color: #f1f3f6; color: #101620; }
        .dark .es-news-page { background-color: #0b0f16; color: #e9edf3; }
        .es-news-ink { color: #101620; }
        .dark .es-news-ink { color: #e9edf3; }
        .es-news-muted { color: #4d545e; }
        .dark .es-news-muted { color: #9aa5b4; }
        .es-news-accent { color: #1d4ed8; }
        .dark .es-news-accent { color: #7fb0ff; }
        /* Always-lit ink, for the pinned dark bands in both colour modes. */
        .es-news-lit { color: #7fb0ff; }

        /* --- Typography: mastheads are set in a serif, data in mono --- */
        .es-news-serif {
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            letter-spacing: -0.015em;
        }
        .es-news-folio {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            font-variant-numeric: tabular-nums;
            color: #4d545e;
        }
        .dark .es-news-folio { color: #9aa5b4; }
        .es-news-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
        }
        /* The h1 is sized here rather than with arbitrary-value
           utilities: this page ships no CSS build, so a Tailwind class that is
           not already in the compiled bundle silently does nothing. */
        .es-news-h1 { font-size: 2.4rem; line-height: 0.98; }
        @media (min-width: 640px) { .es-news-h1 { font-size: 3.25rem; } }
        /* 3.5rem, not 4.5rem: measured, the h1's first line needs about
           800px and the sheet gives it 766, so anything larger wraps
           mid-phrase. */
        @media (min-width: 1024px) { .es-news-h1 { font-size: 3.5rem; } }

        /* Hairline section divider. */
        .es-news-hr { border-top: 1px solid rgba(16, 22, 32, 0.1); }
        .dark .es-news-hr { border-top-color: rgba(233, 237, 243, 0.1); }

        /* Masked grid: the pattern fades out before it reaches the edges. */
        .es-news-mask {
            -webkit-mask-image: radial-gradient(ellipse 75% 65% at 50% 35%, #000 25%, transparent 75%);
            mask-image: radial-gradient(ellipse 75% 65% at 50% 35%, #000 25%, transparent 75%);
        }

        /* --- The front-page sheet --- */
        .es-news-sheet {
            background-color: #ffffff;
            border: 1px solid rgba(16, 22, 32, 0.14);
            box-shadow: 0 30px 60px -34px rgba(16, 22, 32, 0.35);
        }
        .dark .es-news-sheet {
            background-color: #131922;
            border-color: rgba(233, 237, 243, 0.12);
            box-shadow: 0 30px 60px -34px rgba(0, 0, 0, 0.7);
        }
        /* The fold. A sheet that has been folded once carries a shadow above
           the crease and a highlight below it; nothing is drawn, it is two
           gradients. */
        .es-news-crease {
            position: absolute;
            left: 0;
            right: 0;
            top: 47%;
            height: 3.5rem;
            pointer-events: none;
            background-image: linear-gradient(to bottom,
                rgba(16, 22, 32, 0) 0%,
                rgba(16, 22, 32, 0.055) 44%,
                rgba(16, 22, 32, 0.1) 50%,
                rgba(255, 255, 255, 0.85) 52%,
                rgba(255, 255, 255, 0) 100%);
        }
        .dark .es-news-crease {
            background-image: linear-gradient(to bottom,
                rgba(0, 0, 0, 0) 0%,
                rgba(0, 0, 0, 0.34) 44%,
                rgba(0, 0, 0, 0.46) 50%,
                rgba(233, 237, 243, 0.05) 52%,
                rgba(233, 237, 243, 0) 100%);
        }

        /* Double rule: a 2px press rule with a hairline under it. */
        .es-news-rule {
            height: 4px;
            background-image: linear-gradient(to bottom,
                rgba(16, 22, 32, 0.9) 0 2px,
                rgba(0, 0, 0, 0) 2px 3px,
                rgba(16, 22, 32, 0.3) 3px 4px);
        }
        .dark .es-news-rule {
            background-image: linear-gradient(to bottom,
                rgba(233, 237, 243, 0.85) 0 2px,
                rgba(0, 0, 0, 0) 2px 3px,
                rgba(233, 237, 243, 0.3) 3px 4px);
        }

        /* The price stamp, inked at an angle by hand. */
        .es-news-stamp {
            transform: rotate(-7deg);
            border: 2px solid rgba(29, 78, 216, 0.7);
            border-radius: 0.35rem;
            padding: 0.35rem 0.7rem;
            color: #1d4ed8;
            line-height: 1.05;
            box-shadow: inset 0 0 0 2px rgba(29, 78, 216, 0.16);
        }
        .es-news-stamp span {
            display: block;
            font-size: 0.55rem;
            letter-spacing: 0.14em;
        }
        .dark .es-news-stamp {
            border-color: rgba(127, 176, 255, 0.65);
            color: #7fb0ff;
            box-shadow: inset 0 0 0 2px rgba(127, 176, 255, 0.14);
        }

        /* --- The search field --- */
        .es-news-counter {
            background-color: #ffffff;
            border: 1px solid rgba(16, 22, 32, 0.2);
            border-radius: 0.9rem;
        }
        .dark .es-news-counter {
            background-color: #0f141c;
            border-color: rgba(233, 237, 243, 0.16);
        }
        .es-news-counter:focus-within {
            border-color: rgba(29, 78, 216, 0.8);
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.18);
        }
        .dark .es-news-counter:focus-within {
            border-color: rgba(127, 176, 255, 0.8);
            box-shadow: 0 0 0 4px rgba(127, 176, 255, 0.2);
        }
        .es-news-input {
            background-color: transparent;
            border: 0;
            border-radius: 0.9rem;
            color: #101620;
        }
        .es-news-input::placeholder { color: #6b7280; }
        .es-news-input:focus { outline: none; box-shadow: none; }
        .dark .es-news-input { color: #e9edf3; }
        .dark .es-news-input::placeholder { color: #9aa5b4; }

        /* --- Section masthead --- */
        .es-news-banner {
            border-top: 3px solid rgba(16, 22, 32, 0.9);
            border-bottom: 1px solid rgba(16, 22, 32, 0.28);
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }
        .dark .es-news-banner {
            border-top-color: rgba(233, 237, 243, 0.85);
            border-bottom-color: rgba(233, 237, 243, 0.28);
        }
        /* Section plate: the little mono label a section is filed under. */
        .es-news-slug {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.7rem;
            border: 1px solid rgba(16, 22, 32, 0.18);
            border-radius: 0.3rem;
            background-color: #ffffff;
            color: #101620;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-variant-numeric: tabular-nums;
        }
        .dark .es-news-slug {
            border-color: rgba(233, 237, 243, 0.2);
            background-color: #141a24;
            color: #e9edf3;
        }
        .es-news-slug::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background-color: #1d4ed8;
        }
        .dark .es-news-slug::before { background-color: #7fb0ff; }

        /* --- The shelf and its wire rail --- */
        .es-news-shelf {
            background-color: #e8ebf0;
            border: 1px solid rgba(16, 22, 32, 0.09);
            border-radius: 1.25rem;
            box-shadow: inset 0 14px 26px -20px rgba(16, 22, 32, 0.45);
        }
        .dark .es-news-shelf {
            background-color: #10151d;
            border-color: rgba(233, 237, 243, 0.08);
            box-shadow: inset 0 14px 26px -20px rgba(0, 0, 0, 0.8);
        }
        .es-news-rail {
            height: 7px;
            border-radius: 9999px;
            background-color: rgba(16, 22, 32, 0.07);
            background-image: repeating-linear-gradient(90deg,
                rgba(16, 22, 32, 0.3) 0 1px,
                rgba(0, 0, 0, 0) 1px 9px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
        }
        .dark .es-news-rail {
            background-color: rgba(233, 237, 243, 0.07);
            background-image: repeating-linear-gradient(90deg,
                rgba(233, 237, 243, 0.26) 0 1px,
                rgba(0, 0, 0, 0) 1px 9px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.07);
        }

        /* The empty state, drawn as an empty shelf rather than apologised for. */
        .es-news-empty {
            border: 1px dashed rgba(16, 22, 32, 0.28);
            border-radius: 1rem;
            background-color: rgba(255, 255, 255, 0.55);
        }
        .dark .es-news-empty {
            border-color: rgba(233, 237, 243, 0.24);
            background-color: rgba(233, 237, 243, 0.03);
        }

        /* --- Cards --- */
        .es-news-card {
            background-color: #ffffff;
            border: 1px solid rgba(16, 22, 32, 0.12);
            border-radius: 1rem;
        }
        .dark .es-news-card {
            background-color: #141a24;
            border-color: rgba(233, 237, 243, 0.12);
        }

        /* --- The two federated filter selects --- */
        .es-news-select {
            border: 1px solid rgba(16, 22, 32, 0.2);
            border-radius: 0.55rem;
            background-color: #ffffff;
            color: #101620;
            padding: 0.45rem 0.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
        }
        .dark .es-news-select {
            border-color: rgba(233, 237, 243, 0.18);
            background-color: #141a24;
            color: #e9edf3;
        }
        .es-news-select:focus {
            outline: 2px solid #1d4ed8;
            outline-offset: 2px;
        }
        .dark .es-news-select:focus { outline-color: #7fb0ff; }

        /* --- Links and buttons --- */
        .es-news-link { color: #1d4ed8; }
        .es-news-link:hover { color: #101620; }
        .dark .es-news-link { color: #7fb0ff; }
        .dark .es-news-link:hover { color: #e9edf3; }

        .es-news-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            box-shadow: 0 18px 34px -16px rgba(29, 78, 216, 0.6);
        }
        .es-news-btn:hover { background-color: #1740b4; }
        .dark .es-news-btn {
            background-color: #7fb0ff;
            color: #0b0f16;
            box-shadow: 0 18px 34px -16px rgba(127, 176, 255, 0.35);
        }
        .dark .es-news-btn:hover { background-color: #a3c6ff; }

        .es-news-ghost {
            border: 1px solid rgba(16, 22, 32, 0.2);
            background-color: #ffffff;
            color: #101620;
        }
        .es-news-ghost:hover { border-color: rgba(29, 78, 216, 0.55); }
        .dark .es-news-ghost {
            border-color: rgba(233, 237, 243, 0.16);
            background-color: #141a24;
            color: #e9edf3;
        }
        .dark .es-news-ghost:hover { border-color: rgba(127, 176, 255, 0.55); }

        .es-news-hover:hover { border-color: rgba(29, 78, 216, 0.45); }
        .dark .es-news-hover:hover { border-color: rgba(127, 176, 255, 0.45); }
        .es-news-hover:hover .es-news-hover-title,
        .es-news-hover:hover .es-news-hover-arrow { color: #1d4ed8; }
        .dark .es-news-hover:hover .es-news-hover-title,
        .dark .es-news-hover:hover .es-news-hover-arrow { color: #7fb0ff; }

        /* --- The pinned dark band: the finale ---
               Identical with .dark on and off. Every shared class that
               flips with the colour mode is re-pinned below. --- */
        .es-news-band {
            background-color: #0d1219;
            background-image: radial-gradient(120% 100% at 50% 0%, #16202c 0%, #0f151d 55%, #080b10 100%);
            border: 1px solid rgba(233, 237, 243, 0.09);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(233, 237, 243, 0.05);
            color: #e9edf3;
        }
        .es-news-band .es-news-muted { color: #9aa5b4; }
        .es-news-band .es-news-folio { color: #7fb0ff; }
        .es-news-band .es-news-card {
            background-color: #161d27;
            border-color: rgba(233, 237, 243, 0.13);
        }
        .es-news-band .es-news-slug {
            border-color: rgba(233, 237, 243, 0.2);
            background-color: #161d27;
            color: #e9edf3;
        }
        .es-news-band .es-news-slug::before { background-color: #7fb0ff; }
        .es-news-band .es-news-btn {
            background-color: #7fb0ff;
            color: #0b0f16;
            box-shadow: 0 18px 34px -16px rgba(127, 176, 255, 0.35);
        }
        .es-news-band .es-news-btn:hover { background-color: #a3c6ff; }
        /* The rail carries its own .dark rule, so a rail inside a pinned band has
           to be re-pinned here or the finale slot changes stock with the theme. */
        .es-news-band .es-news-rail {
            background-color: rgba(233, 237, 243, 0.07);
            background-image: repeating-linear-gradient(90deg,
                rgba(233, 237, 243, 0.26) 0 1px,
                rgba(0, 0, 0, 0) 1px 9px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.07);
        }
        /* The blank plate in the finale: two rails and the plate between them.
           Fixed values only, no .dark variant, so it is pinned by construction. */
        .es-news-slot { display: flex; flex-direction: column; gap: 0.85rem; }
        .es-news-plate {
            border: 1px dashed rgba(233, 237, 243, 0.26);
            border-radius: 1rem;
            background-color: #161d27;
            padding: 1.1rem 1.25rem 1.25rem;
        }
        /* The schedule nobody has made yet. Bars, not words. */
        .es-news-plate-lines {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.85rem;
        }
        .es-news-plate-lines span {
            display: block;
            height: 0.5rem;
            border-radius: 9999px;
            background-color: rgba(233, 237, 243, 0.13);
        }
        .es-news-plate-lines span:nth-child(1) { width: 68%; height: 0.95rem; }
        .es-news-plate-lines span:nth-child(2) { width: 44%; }
        .es-news-plate-lines span:nth-child(3) { width: 57%; }
        .es-news-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 237, 243, 0.05) 1px, rgba(0, 0, 0, 0) 1px),
                linear-gradient(90deg, rgba(233, 237, 243, 0.05) 1px, rgba(0, 0, 0, 0) 1px);
        }
        .es-news-band .animate-shimmer {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0), rgba(255, 255, 255, 0.15), rgba(0, 0, 0, 0));
            background-size: 200% 100%;
        }
        .es-news-band .es-claim:focus-within {
            border-color: rgba(127, 176, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(127, 176, 255, 0.22);
        }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.12), rgba(0, 0, 0, 0) 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(127, 176, 255, 0.12), rgba(0, 0, 0, 0) 60%);
        }
        /* Dot-nav tooltip. Painted here, not with dark:bg-[...] utilities, which
           are absent from the compiled bundle and left light ink on white. */
        .es-news-tip {
            border: 1px solid rgba(16, 22, 32, 0.14);
            background-color: #ffffff;
            color: #21262e;
        }
        .dark .es-news-tip {
            border-color: rgba(233, 237, 243, 0.12);
            background-color: #141a24;
            color: #d5dbe4;
        }

        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(127, 176, 255, 0.65); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #7fb0ff; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus, and outlines already follow it. --- */
        #es-news-page a:focus-visible,
        #es-news-page summary:focus-visible,
        #es-news-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-news-page a:focus-visible,
        .dark #es-news-page summary:focus-visible,
        .dark #es-news-page button:focus-visible {
            outline-color: #7fb0ff;
        }
        .es-news-band a:focus-visible,
        .es-news-band summary:focus-visible,
        .es-news-band button:focus-visible {
            outline-color: #7fb0ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-news-stamp { transform: none; }
        }
    </style>

    @php
        $eventCount = $events->count();
        $networkCount = $federatedEvents->count();
        $hasNetworkSection = $networkCount > 0 || $federatedCountry || $federatedLanguage;

        // Section plates number themselves, so the run stays 01, 02, 03 whether or
        // not the federated section renders. Hardcoding them left a gap.
        $browseSectionNo = 0;

        // Three questions a visitor actually asks. Anything about how the page is
        // built belongs here or nowhere - see the note in the style block.
        $faqs = [
            [
                'q' => 'Do I need an account to browse?',
                'a' => 'No. Everything here is free to read without signing in. You only need an account to follow a schedule or publish events of your own.',
            ],
            [
                'q' => 'Why are some listings from other websites?',
                'a' => 'Event Schedule is open source, so other people run their own copies of it and some choose to share their events here. Those cards open on the site that published them.',
            ],
            [
                'q' => 'Can I browse by city?',
                'a' => 'Not with a filter, but search will do it. Type in a city and you get the schedules based there.',
            ],
        ];

        $dotSections = [
            ['top', 'Search'],
            ['events', 'Upcoming events'],
        ];
        if ($hasNetworkSection) {
            $dotSections[] = ['network', 'From other sites'];
        }
        $dotSections[] = ['faq', 'Questions'];
        $dotSections[] = ['organizers', 'For organizers'];
    @endphp

    <div id="es-news-page" class="es-news-page">

    <!-- ============================================================ -->
    <!-- 1. Hero                                                      -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative scroll-mt-24 overflow-hidden py-14 lg:py-20">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(29, 78, 216, 0.22), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(127, 176, 255, 0.16), rgba(127, 176, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-news-mask grid-pattern absolute inset-0 bg-[size:60px_60px]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="es-news-sheet es-fade-up es-d-1 relative overflow-hidden rounded-2xl p-6 sm:p-9 lg:p-12">
                <div class="es-news-crease" aria-hidden="true"></div>

                <div class="relative z-10">
                    <div class="es-news-rule" aria-hidden="true"></div>

                    <div class="mt-4 mb-6 flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2">
                        <p class="es-news-folio">Event Schedule &middot; What's on</p>
                        <p class="es-news-folio">Soonest first &middot; <span class="es-news-num">{{ $eventCount }}</span> showing</p>
                    </div>

                    <div class="flex items-start justify-between gap-6">
                        {{-- No es-balance here: the two h1 lines are broken by hand,
                             and text-wrap:balance re-breaks the first one mid-phrase. --}}
                        <h1 class="es-news-h1 es-news-serif es-news-ink mb-6 font-black">
                            <span class="es-mask"><span class="es-mask-line">Upcoming events,</span></span>
                            <span class="es-mask es-mask-2"><span class="es-mask-line">all in <span class="es-news-accent">one place.</span></span></span>
                        </h1>
                        <div class="es-news-stamp es-news-folio mt-2 hidden shrink-0 text-center sm:block" aria-hidden="true">
                            Free
                            <span>to read</span>
                        </div>
                    </div>

                    <div class="es-news-rule mb-6" aria-hidden="true"></div>

                    {{-- What kind of events, not how the page works: "Event Schedule"
                         tells a first-time visitor nothing, and the cards only answer
                         it after a scroll. A noun list also survives the empty state. --}}
                    <p class="es-fade-up es-d-2 es-news-muted max-w-2xl text-lg">
                        Live music, comedy, classes, markets and more.
                    </p>

                    <form action="{{ marketing_url('/search') }}" method="GET" class="es-fade-up es-d-3 mt-7">
                        <label for="browse-search" class="es-news-folio mb-2 block">Search by event, city or schedule</label>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="es-news-counter relative flex-1">
                                <svg aria-hidden="true" class="absolute top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500 ltr:left-4 rtl:right-4 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    id="browse-search"
                                    type="search"
                                    name="q"
                                    placeholder="{{ __('messages.search') }}..."
                                    class="es-news-input w-full py-4 text-lg ltr:pl-12 ltr:pr-4 rtl:pl-4 rtl:pr-12"
                                >
                            </div>
                            <button type="submit" class="es-news-btn shrink-0 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                                {{ __('messages.search') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Upcoming events                                           -->
    <!-- ============================================================ -->
    <section id="events" class="scroll-mt-24 pb-16 lg:pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if(session('message'))
                <div class="es-news-card mb-8 px-4 py-3 text-sm">
                    <span class="es-news-ink">{{ session('message') }}</span>
                </div>
            @endif

            <div class="es-news-banner mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
                <div>
                    <div class="es-news-slug mb-3"><span>Section {{ str_pad(++$browseSectionNo, 2, '0', STR_PAD_LEFT) }}</span></div>
                    <h2 class="es-news-serif es-news-ink text-2xl font-black md:text-3xl">{{ __('messages.upcoming_events') }}</h2>
                </div>
                <p class="es-news-folio">
                    <span class="es-news-num">{{ $eventCount }}</span> events
                </p>
            </div>

            <div class="es-news-shelf p-4 sm:p-6">
                <div class="es-news-rail mb-6" aria-hidden="true"></div>

                @if($eventCount > 0)
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($events as $event)
                            @include('marketing.partials.event-card', ['event' => $event])
                        @endforeach
                    </div>
                @else
                    {{-- Empty state. An empty shelf is a real state, so it is drawn
                         as one rather than apologised for, and it goes straight to the
                         only thing that can be offered here instead of repeating
                         "empty" a third time. --}}
                    <div class="es-news-empty px-6 py-14 text-center">
                        <p class="es-news-folio mb-4">Empty</p>
                        <h3 class="es-news-serif es-news-ink mb-4 text-2xl font-black">No upcoming events yet</h3>
                        <p class="es-news-muted mx-auto mb-8 max-w-md">
                            If you run events, yours could be the first ones here. It is free to publish.
                        </p>
                        <a href="{{ app_url('/sign_up') }}" class="es-news-btn inline-flex items-center rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            {{ __('messages.create_your_schedule') }}
                            <svg aria-hidden="true" class="ml-2 h-5 w-5 rtl:ml-0 rtl:mr-2 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                @endif

                <div class="es-news-rail mt-6" aria-hidden="true"></div>
                {{-- browse() ends in ->limit(24), but do not print the cap: with fewer
                     than 24 events it would be false, and a reader wants the way out,
                     not the limit. --}}
                <p class="es-news-folio mt-4 text-center">
                    @if($eventCount > 0)Not seeing it? @endif
                    <a href="{{ marketing_url('/search') }}" class="es-news-link underline">Search for something specific</a>
                </p>
            </div>
        </div>
    </section>

    {{-- Federated listings from other Event Schedule installs.

         Their own section rather than mixed into the grid above: provenance
         stays obvious, and the local query keeps its single ordering and limit.
         Not rendered at all when the network has nothing to show. --}}
    @if($hasNetworkSection)
        <!-- ============================================================ -->
        <!-- 3. Events from other sites                                   -->
        <!-- ============================================================ -->
        <section id="network" class="scroll-mt-24 pb-16 lg:pb-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="es-news-banner mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-4">
                    <div>
                        <div class="es-news-slug mb-3"><span>Section {{ str_pad(++$browseSectionNo, 2, '0', STR_PAD_LEFT) }}</span></div>
                        <h2 class="es-news-serif es-news-ink text-2xl font-black md:text-3xl">{{ __('messages.federation_browse_heading') }}</h2>
                        <p class="es-news-muted mt-2 max-w-xl text-sm">{{ __('messages.federation_browse_intro') }}</p>
                    </div>

                    {{-- Plain GET filters. In-person events make location filtering
                         essential, and keeping this server-side means the page stays
                         crawlable, shareable, and free of a JS mount. --}}
                    <form method="GET" action="{{ marketing_url('/browse') }}" class="flex flex-wrap items-center gap-2">
                        <span class="es-news-folio" aria-hidden="true">Filter by</span>
                        <label for="federated-country" class="sr-only">{{ __('messages.federation_filter_all_countries') }}</label>
                        <select id="federated-country" name="country" data-auto-submit class="es-news-select">
                            <option value="">{{ __('messages.federation_filter_all_countries') }}</option>
                            @foreach($federatedCountries as $code)
                                <option value="{{ $code }}" @selected($federatedCountry === $code)>{{ \App\Utils\CountryUtils::getName($code) ?: $code }}</option>
                            @endforeach
                        </select>

                        <label for="federated-language" class="sr-only">{{ __('messages.federation_filter_all_languages') }}</label>
                        <select id="federated-language" name="lang" data-auto-submit class="es-news-select">
                            <option value="">{{ __('messages.federation_filter_all_languages') }}</option>
                            @foreach($federatedLanguages as $code)
                                <option value="{{ $code }}" @selected($federatedLanguage === $code)>{{ ucfirst(config('app.supported_languages')[$code] ?? $code) }}</option>
                            @endforeach
                        </select>

                        <noscript>
                            <button type="submit" class="es-news-btn rounded-lg px-4 py-2 text-sm font-semibold">{{ __('messages.filter') }}</button>
                        </noscript>
                    </form>
                </div>

                <div class="es-news-shelf p-4 sm:p-6">
                    <div class="es-news-rail mb-6" aria-hidden="true"></div>

                    @if($networkCount > 0)
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($federatedEvents as $federatedEvent)
                                @include('marketing.partials.federated-event-card', ['event' => $federatedEvent])
                            @endforeach
                        </div>

                        @if($federatedTotal > $networkCount && $federatedLimit < 96)
                            <div class="mt-8 text-center">
                                <a href="{{ request()->fullUrlWithQuery(['federated_limit' => $federatedLimit + 24]) }}#network"
                                   class="es-news-ghost inline-flex items-center rounded-2xl px-6 py-3 font-semibold transition-all duration-200">
                                    {{ __('messages.federation_show_more') }}
                                </a>
                            </div>
                        @endif
                    @else
                        {{-- Visitor-facing wording: federation_preview_empty is about the
                             sender's eligibility rules and means nothing to someone who
                             just picked a country. --}}
                        <div class="es-news-empty px-6 py-12 text-center">
                            {{-- Names the cause rather than restating the message below it,
                                 which is what makes the reset link make sense. This branch
                                 is only ever reached with a filter set. --}}
                            <p class="es-news-folio mb-3">Filtered</p>
                            <p class="es-news-muted">{{ __('messages.federation_browse_no_results') }}</p>
                            @if($federatedCountry || $federatedLanguage)
                                <p class="es-news-folio mt-4">
                                    <a href="{{ marketing_url('/browse') }}#network" class="es-news-link underline">Show every country and language</a>
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="es-news-rail mt-6" aria-hidden="true"></div>
                </div>
            </div>
        </section>

        {{-- Count the visit without touching the href, which must stay a direct
             followable link to the origin. Delegated rather than an inline
             handler, which CSP blocks. --}}
        <script {!! nonce_attr() !!}>
            // Submit the filter form on change. The AP layout has a shared
            // data-auto-submit handler, but marketing pages do not load it.
            document.addEventListener('change', function (e) {
                var control = e.target.closest('[data-auto-submit]');
                if (control && control.form) control.form.submit();
            });

            document.addEventListener('click', function (e) {
                var link = e.target.closest('[data-federated-click]');
                if (!link || !navigator.sendBeacon) return;
                var body = new FormData();
                body.append('_token', '{{ csrf_token() }}');
                navigator.sendBeacon('{{ marketing_url('/browse/federated/') }}' + link.dataset.federatedClick + '/click', body);
            });
        </script>
    @endif

    {{-- Admin-only: hidden events management --}}
    @if($hiddenEvents->count() > 0)
        <section class="pb-16 lg:pb-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="es-news-banner mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
                    <div>
                        <div class="es-news-slug mb-3"><span>Hidden</span></div>
                        <h2 class="es-news-serif es-news-ink text-2xl font-black md:text-3xl">Hidden events</h2>
                        <p class="es-news-muted mt-2 max-w-2xl text-sm">Only admins see this. These events are hidden from the homepage, Browse, and search.</p>
                    </div>
                    <p class="es-news-folio"><span class="es-news-num">{{ $hiddenEvents->count() }}</span> hidden</p>
                </div>

                <div class="es-news-shelf p-4 sm:p-6">
                    <div class="es-news-rail mb-6" aria-hidden="true"></div>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($hiddenEvents as $event)
                            @include('marketing.partials.event-card', ['event' => $event])
                        @endforeach
                    </div>
                    <div class="es-news-rail mt-6" aria-hidden="true"></div>
                </div>
            </div>
        </section>
    @endif

    <!-- ============================================================ -->
    <!-- 4. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-news-hr scroll-mt-24 py-16 lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <div class="es-news-slug mb-6" data-reveal aria-hidden="true"><span>Section {{ str_pad(++$browseSectionNo, 2, '0', STR_PAD_LEFT) }}</span></div>
                <h2 class="es-news-serif es-balance es-news-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Common <span class="es-news-accent">questions</span>
                </h2>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-news-hover es-news-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-news-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-news-num es-news-accent flex-none text-sm" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-news-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-news-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-news-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>

            {{-- The dot nav is desktop-only, so give a phone reader a way back to
                 the events before the organizer finale. --}}
            <p class="es-news-folio mt-10 text-center">
                <a href="#events" class="es-news-link underline">Back to the events</a> &middot;
                <a href="{{ marketing_url('/search') }}" class="es-news-link underline">Search for something specific</a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Finale: for organizers (pinned dark band)                 -->
    <!-- ============================================================ -->
    <section id="organizers" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="es-news-band noise relative overflow-hidden rounded-[2rem] px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-news-folio mb-4">For the people who run events</p>
                    <h2 class="es-news-serif es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Get your events <span class="es-news-lit">on this page</span>
                    </h2>
                    {{-- "with a picture" is deliberate: the flyer-or-schedule-photo
                         requirement is the one check an organizer can fail while doing
                         everything else right, and this is now the only place it is
                         named. Pricing detail belongs on /pricing, not on a listing
                         page. --}}
                    <p class="es-news-muted mx-auto mb-10 max-w-2xl text-lg">
                        Publish an event with a picture and it appears here automatically. No application, no fee.
                    </p>

                    {{-- The blank plate. Every section above shows a schedule somebody
                         already made; this is the one that is not there yet, and the
                         address you type below is the name that fills it. Drawn as bars
                         rather than placeholder words: nothing here is text, so there is
                         no ink to measure and nothing to read out. Every value is fixed,
                         so it renders identically with .dark on and off, like the band
                         around it. --}}
                    <div class="es-news-slot mx-auto mb-8 max-w-2xl text-left">
                        <div class="es-news-rail" aria-hidden="true"></div>
                        <div class="es-news-plate">
                            <p class="es-news-folio">Your schedule &middot; not set up yet</p>
                            <div class="es-news-plate-lines" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>
                        <div class="es-news-rail" aria-hidden="true"></div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-news-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            <span class="relative z-10 flex items-center gap-2">
                                {{ __('messages.get_started_free') }}
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-news-muted mt-6 text-sm">
                        No credit card required &middot;
                        <a href="{{ marketing_url('/features') }}" class="es-news-lit font-semibold">Features</a> &middot;
                        <a href="{{ marketing_url('/pricing') }}" class="es-news-lit font-semibold">Pricing</a>
                    </p>
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
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-500/60 dark:bg-white/30"></span>
                        <span class="es-news-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
