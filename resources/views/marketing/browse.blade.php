<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Browse Upcoming Events | Event Schedule</x-slot>
    <x-slot name="description">Discover upcoming public events, shows, classes, and meetups happening across Event Schedule. Browse what's on, or search for something specific.</x-slot>
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

           /browse is not an audience page, it is the rack outside the
           kiosk: other people's editions, racked so a passer-by can read
           the mastheads at a glance. The concept has to argue the
           product, and here it does that literally - this page IS the
           rack, and the claim is that a schedule earns a slot on it by
           publishing, not by applying. Devices: the front-page sheet
           with its fold crease (hero), the wire rack rail the result
           cards sit on, a printer's spec block listing exactly what the
           rack requires (a real table, read straight out of
           MarketingController::publicUpcomingEventsQuery), and the
           out-of-town rack for federated listings.

           COLOUR: the page keeps the press blue it already had. That is
           deliberate and is not a hue grab - /browse is platform
           furniture rather than an audience pitch, so the brand's own
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
           page's own is THE RACK: an inset shelf with a wire rail above
           and below every result grid (local, out-of-town and
           admin-pulled), the fold crease on the front-page sheet, the
           spec block of what the rack requires, and the empty slot in
           the finale. Sharpen those; do not reach for more newsprint.

           DELIBERATELY NOT PINNED: the sheet is not a fixed physical
           object. A newsstand racks a day edition and a late edition, so
           the stock changes with the colour mode and only the ink stays
           constant. The two .es-news-band panels ARE pinned (the drop
           happens in the dark in both modes), so they carry
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
        /* The masthead line is sized here rather than with arbitrary-value
           utilities: this page ships no CSS build, so a Tailwind class that is
           not already in the compiled bundle silently does nothing. */
        .es-news-h1 { font-size: 2.4rem; line-height: 0.98; }
        @media (min-width: 640px) { .es-news-h1 { font-size: 3.25rem; } }
        /* 3.5rem, not 4.5rem: measured, the first masthead line needs about
           800px and the sheet gives it 766, so anything larger wraps
           mid-phrase and stops reading as a masthead. */
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

        /* --- The counter: the search slot --- */
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
        /* Edition slug: the little mono plate a section is filed under. */
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

        /* An empty rack, which is a real state on a real newsstand. */
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

        /* --- The spec block --- */
        .es-news-tbl { width: 100%; border-collapse: collapse; text-align: left; }
        .es-news-tbl th,
        .es-news-tbl td { padding: 0.7rem 0.7rem; vertical-align: top; }
        .es-news-tbl thead th { border-bottom: 2px solid rgba(16, 22, 32, 0.55); }
        .dark .es-news-tbl thead th { border-bottom-color: rgba(233, 237, 243, 0.45); }
        .es-news-tbl tbody tr { border-top: 1px solid rgba(16, 22, 32, 0.1); }
        .dark .es-news-tbl tbody tr { border-top-color: rgba(233, 237, 243, 0.1); }
        .es-news-tbl tbody tr:first-child { border-top: 0; }

        /* --- Rack labels: the two federated filter selects --- */
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

        /* --- Plan pills --- */
        .es-news-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border: 1px solid rgba(29, 78, 216, 0.42);
            border-radius: 0.25rem;
            color: #1d4ed8;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-news-plan { border-color: rgba(127, 176, 255, 0.45); color: #7fb0ff; }
        .es-news-plan-pro { border-color: rgba(16, 22, 32, 0.35); color: #101620; }
        .dark .es-news-plan-pro { border-color: rgba(233, 237, 243, 0.38); color: #e9edf3; }

        /* --- Chips --- */
        .es-news-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.8rem;
            border: 1px solid rgba(16, 22, 32, 0.16);
            border-radius: 9999px;
            background-color: rgba(255, 255, 255, 0.75);
            color: #4d545e;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .dark .es-news-chip {
            border-color: rgba(233, 237, 243, 0.16);
            background-color: rgba(233, 237, 243, 0.05);
            color: #a9b3c1;
        }

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

        /* --- The pinned dark bands: the drop, and the finale ---
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
        /* The empty slot in the finale: two rails and the plate between them.
           Fixed values only, no .dark variant, so it is pinned by construction. */
        .es-news-slot { display: flex; flex-direction: column; gap: 0.85rem; }
        .es-news-plate {
            border: 1px dashed rgba(233, 237, 243, 0.26);
            border-radius: 1rem;
            background-color: #161d27;
            padding: 1.1rem 1.25rem 1.25rem;
        }
        /* The masthead nobody has set yet. Bars, not words. */
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
        $rackCount = $events->count();
        $networkCount = $federatedEvents->count();
        $hasNetworkSection = $networkCount > 0 || $federatedCountry || $federatedLanguage;

        // What an event has to be for this page to rack it. Every row is read out
        // of MarketingController::publicUpcomingEventsQuery() and the extra
        // clauses in browse(), not invented: date window, is_private / is_draft /
        // is_cancelled, event_role.is_accepted, the verified-schedule filter
        // (publicScheduleFilter: email_verified_at OR phone_verified_at), the
        // flyer-or-schedule-photo requirement, and Event::excludeLikelyTest().
        //
        // Row 1 says "for as long as it is set to repeat" on purpose: the date
        // clause keeps a recurring event on days_of_week alone and never reads
        // recurring_end_type, so "while it still has dates" would be false for a
        // recurrence that has finished.
        //
        // Two clauses are deliberately NOT rows, because neither is a thing a
        // publisher can act on: the demo-schedule exclusion, and the optional
        // config('app.search_exclude_country') filter. roles.is_unlisted is also
        // left out - it gates the rack, but no view or controller exposes it, so
        // naming it would send readers hunting for a setting that is not there.
        $rackChecks = [
            [
                'Upcoming',
                'A one-off is racked while its date is today or later, a multi-day event while it is still running, and a repeating event for as long as it is set to repeat.',
                'The event date, or its recurrence',
            ],
            [
                'Public',
                'Not a draft, not unlisted, not cancelled. Anything you have not announced yet is invisible here, which is the point of a draft.',
                "The event's visibility",
            ],
            [
                'Accepted',
                'The schedule it is listed under has accepted it, so an event somebody has requested but nobody has approved never shows up.',
                "The schedule's Requests tab",
            ],
            [
                'A verified schedule',
                'The schedule behind it confirmed either an email address or a phone number. One free step, once.',
                'Schedule settings',
            ],
            [
                'A picture',
                'The event has its own flyer, or the talent or venue schedule behind it has a profile photo. A rack is read at a glance, so a listing without a cover does not get one.',
                'The event flyer, or the schedule photo',
            ],
            [
                'A real name',
                'Placeholders stay off. "Test", "asdf", "untitled event" and one character typed three times never rack at all, and a name starting "sample" or "demo" racks only if the event carries something real: a description, a flyer, a link, tickets or registration.',
                'The event name',
            ],
        ];

        $faqs = [
            [
                'q' => 'How do events get onto this page?',
                'a' => 'There is nothing to submit and nothing to pay. A public, upcoming event on a verified schedule is racked automatically, as long as it carries a picture: its own flyer, or the profile photo of the talent or venue schedule behind it. The rack itself holds two dozen at a time, soonest first, so search is the way to reach the rest. Drafts, unlisted events, cancelled events and events a schedule has not accepted stay off.',
            ],
            [
                'q' => 'Do I need an account to browse?',
                'a' => 'No. This page is public and free to read, and every card opens the event on the schedule that published it. You need an account to publish your own events, and to follow a schedule, which keeps it on your following list and lets that schedule email you. Nothing is sent automatically either way.',
            ],
            [
                'q' => 'Why are some listings from other websites?',
                'a' => 'Those are federated listings. Event Schedule is open source, so other people run their own copies of it, and the operator of one of those installs can switch federation on to share their public events here. Every one of those cards links straight back to the site that published it, and you can narrow them by country or by language.',
            ],
            [
                'q' => 'Can I browse by city?',
                'a' => 'Not as a filter on this page, and it would waste your time to pretend otherwise. The out-of-town rack filters by country and by language. Each card names the schedule and its city, and search matches a schedule name, city or web address as well as event names, so typing a city into the search box finds the schedules based there.',
            ],
            [
                'q' => 'How do I get my own events on the rack?',
                'a' => 'Create a schedule, confirm your email address, and publish a public event with a picture. That is free forever, and so are recurring dates, sub-schedules, two-way calendar sync, registration with a capacity limit, the embeddable calendar and 10 newsletter emails a month. Selling tickets is $'.$proMonthly.' a month on the Pro plan, and Event Schedule takes zero platform fees on what you sell.',
            ],
        ];

        $dotSections = [
            ['top', 'The masthead'],
            ['rack', "Today's rack"],
        ];
        if ($hasNetworkSection) {
            $dotSections[] = ['network', 'Out of town'];
        }
        foreach ([
            ['drop', 'The drop'],
            ['requires', 'What the rack requires'],
            ['yours', 'Your own title'],
            ['faq', 'Questions'],
            ['claim', 'Start a title'],
        ] as $extraSection) {
            $dotSections[] = $extraSection;
        }
    @endphp

    <div id="es-news-page" class="es-news-page">

    <!-- ============================================================ -->
    <!-- 1. The masthead                                              -->
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
                        <p class="es-news-folio">Event Schedule &middot; Public editions</p>
                        <p class="es-news-folio">Soonest first &middot; <span class="es-news-num">{{ $rackCount }}</span> on the rack</p>
                    </div>

                    <div class="flex items-start justify-between gap-6">
                        {{-- No es-balance here: the two masthead lines are broken by hand,
                             and text-wrap:balance re-breaks the first one mid-phrase. --}}
                        {{-- Not "every upcoming edition": browse() ends in ->limit(24), so the
                             rack holds the soonest two dozen and the masthead must not promise
                             the whole platform. --}}
                        <h1 class="es-news-h1 es-news-serif es-news-ink mb-6 font-black">
                            <span class="es-mask"><span class="es-mask-line">Upcoming events,</span></span>
                            <span class="es-mask es-mask-2"><span class="es-mask-line">on <span class="es-news-accent">one rack.</span></span></span>
                        </h1>
                        <div class="es-news-stamp es-news-folio mt-2 hidden shrink-0 text-center sm:block" aria-hidden="true">
                            Free
                            <span>to read</span>
                        </div>
                    </div>

                    <div class="es-news-rule mb-6" aria-hidden="true"></div>

                    <p class="es-fade-up es-d-2 es-news-muted max-w-2xl text-lg">
                        Public events from schedules across Event Schedule, soonest first, up to two dozen at a time. Every card opens the event on the schedule that published it, not a copy of it here.
                    </p>

                    <form action="{{ marketing_url('/search') }}" method="GET" class="es-fade-up es-d-3 mt-7">
                        <label for="browse-search" class="es-news-folio mb-2 block">Search events and schedules</label>
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
                        <p class="es-news-muted mt-3 text-sm">
                            Matches event names, and a schedule's name, city or web address. A city name finds the schedules based there.
                        </p>
                    </form>
                </div>
            </div>

            <div class="es-fade-up es-d-4 mt-8 flex flex-wrap items-center justify-center gap-2">
                <span class="es-news-chip">Public only</span>
                <span class="es-news-chip">No account needed</span>
                <span class="es-news-chip">Links to the source</span>
                <span class="es-news-chip">Open source platform</span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Today's rack: the local listings                          -->
    <!-- ============================================================ -->
    <section id="rack" class="scroll-mt-24 pb-16 lg:pb-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if(session('message'))
                <div class="es-news-card mb-8 px-4 py-3 text-sm">
                    <span class="es-news-ink">{{ session('message') }}</span>
                </div>
            @endif

            <div class="es-news-banner mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-3">
                <div>
                    <div class="es-news-slug mb-3"><span>Section 01</span></div>
                    <h2 class="es-news-serif es-news-ink text-2xl font-black md:text-3xl">{{ __('messages.upcoming_events') }}</h2>
                </div>
                <p class="es-news-folio">
                    <span class="es-news-num">{{ $rackCount }}</span> racked &middot; soonest first
                </p>
            </div>

            <div class="es-news-shelf p-4 sm:p-6">
                <div class="es-news-rail mb-6" aria-hidden="true"></div>

                @if($rackCount > 0)
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($events as $event)
                            @include('marketing.partials.event-card', ['event' => $event])
                        @endforeach
                    </div>
                @else
                    {{-- Empty state. An empty rack is a real state on a real
                         newsstand, so it is drawn as one rather than apologised for. --}}
                    <div class="es-news-empty px-6 py-14 text-center">
                        <p class="es-news-folio mb-4">Nothing racked today</p>
                        <h3 class="es-news-serif es-news-ink mb-4 text-2xl font-black">No upcoming events yet</h3>
                        <p class="es-news-muted mx-auto mb-8 max-w-md">
                            There are no public events to show right now. Be the first to share yours.
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
                {{-- The rack is capped at 24 by browse(). Say so here rather than let the
                     masthead imply the whole platform fits on one shelf. --}}
                <p class="es-news-folio mt-4 text-center">
                    Two dozen slots, soonest first &middot;
                    <a href="{{ marketing_url('/search') }}" class="es-news-link underline">Search finds the rest</a>
                </p>
            </div>
        </div>
    </section>

    {{-- Federated listings from other Event Schedule installs.

         Their own rack rather than mixed into the grid above: provenance
         stays obvious, and the local query keeps its single ordering and limit.
         Not rendered at all when the network has nothing to show. --}}
    @if($hasNetworkSection)
        <!-- ============================================================ -->
        <!-- 3. The out-of-town rack                                      -->
        <!-- ============================================================ -->
        <section id="network" class="scroll-mt-24 pb-16 lg:pb-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="es-news-banner mb-8 flex flex-wrap items-end justify-between gap-x-6 gap-y-4">
                    <div>
                        <div class="es-news-slug mb-3"><span>Section 02</span></div>
                        <h2 class="es-news-serif es-news-ink text-2xl font-black md:text-3xl">{{ __('messages.federation_browse_heading') }}</h2>
                        <p class="es-news-muted mt-2 max-w-xl text-sm">{{ __('messages.federation_browse_intro') }}</p>
                    </div>

                    {{-- Plain GET filters. In-person events make location filtering
                         essential, and keeping this server-side means the page stays
                         crawlable, shareable, and free of a JS mount. --}}
                    <form method="GET" action="{{ marketing_url('/browse') }}" class="flex flex-wrap items-center gap-2">
                        <span class="es-news-folio" aria-hidden="true">Rack labels</span>
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
                            <p class="es-news-folio mb-3">Empty rack</p>
                            <p class="es-news-muted">{{ __('messages.federation_browse_no_results') }}</p>
                        </div>
                    @endif

                    <div class="es-news-rail mt-6" aria-hidden="true"></div>
                    <p class="es-news-folio mt-4 text-center">Every out-of-town card links back to the site that printed it</p>
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
                        <div class="es-news-slug mb-3"><span>Pulled</span></div>
                        <h2 class="es-news-serif es-news-ink text-2xl font-black md:text-3xl">Hidden events</h2>
                        <p class="es-news-muted mt-2 max-w-2xl text-sm">Only admins see this. These events are hidden from the homepage, Browse, and search. Click Unhide to restore one.</p>
                    </div>
                    <p class="es-news-folio"><span class="es-news-num">{{ $hiddenEvents->count() }}</span> pulled</p>
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
    <!-- 4. The drop (pinned dark band)                               -->
    <!-- ============================================================ -->
    <section id="drop" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-news-band noise relative overflow-hidden rounded-[2rem] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-news-slug mb-6" data-reveal aria-hidden="true"><span>Section 03</span></div>
                    <p class="es-news-folio mb-4" data-reveal style="--reveal-delay: 0.05s;">The drop</p>
                    <h2 class="es-news-serif es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nobody stacks this rack <span class="es-news-lit">by hand.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-news-card p-6" data-reveal="panel">
                        <p class="es-news-folio mb-3">Nothing to submit</p>
                        <h3 class="es-news-serif mb-2 text-lg font-bold text-white">Publish, and you are on it</h3>
                        <p class="es-news-muted text-sm">The rack is a query over public events, run when this page loads. There is no form, no queue and no editor deciding whether your night is interesting.</p>
                    </div>
                    <div class="es-news-card p-6" data-reveal="panel">
                        <p class="es-news-folio mb-3">It rolls off</p>
                        {{-- The query keeps a recurring event on the strength of days_of_week
                             alone, with no check on recurring_end_type, so "stays up while it
                             still has dates" would be false for a recurrence that has ended.
                             What is true: it stays up while it is set to recur. --}}
                        <h3 class="es-news-serif mb-2 text-lg font-bold text-white">Yesterday is gone</h3>
                        <p class="es-news-muted text-sm">A one-off drops off the rack once its date has passed, without anyone tidying up. A weekly or monthly night stays up for as long as it is set to repeat.</p>
                    </div>
                    <div class="es-news-card p-6" data-reveal="panel">
                        <p class="es-news-folio mb-3">Held back</p>
                        <h3 class="es-news-serif mb-2 text-lg font-bold text-white">A draft is on no rack</h3>
                        <p class="es-news-muted text-sm">Not announced yet? Keep it a draft and it appears nowhere public, here or on your own page, until you publish it. Drafts are free on every plan.</p>
                    </div>
                </div>

                <p class="es-news-muted mt-10 text-center" data-reveal>
                    Same for the reverse: unpublish or cancel an event and it leaves the rack on the next page load.
                    <a href="#requires" class="es-news-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        What the rack requires
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. What the rack requires: the spec block                    -->
    <!-- ============================================================ -->
    <section id="requires" class="scroll-mt-24 py-16 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-10 max-w-3xl text-center">
                <div class="es-news-slug mb-6" data-reveal aria-hidden="true"><span>Section 04</span></div>
                <h2 class="es-news-serif es-balance es-news-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    What the rack <span class="es-news-accent">requires</span>
                </h2>
                <p class="es-news-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Six checks, all of them free to pass, read straight out of the query this page runs.
                </p>
            </div>

            <div class="es-news-card p-4 sm:p-7" data-reveal="panel">
                <table class="es-news-tbl">
                    <caption class="sr-only">The six conditions an event must meet before it appears on the Browse rack</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-news-folio">No.</th>
                            <th scope="col" class="es-news-folio">The check</th>
                            <th scope="col" class="es-news-folio">What it means</th>
                            <th scope="col" class="es-news-folio hidden md:table-cell">Where it is set</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rackChecks as $checkIndex => [$checkName, $checkMeans, $checkWhere])
                            <tr>
                                <td class="es-news-num es-news-accent text-sm">{{ str_pad($checkIndex + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <th scope="row" class="es-news-ink whitespace-nowrap text-sm font-bold">{{ $checkName }}</th>
                                <td class="es-news-muted text-sm">{{ $checkMeans }}</td>
                                <td class="es-news-muted hidden text-xs md:table-cell">{{ $checkWhere }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-news-hr es-news-muted mt-5 pt-4 text-xs">
                    Site admins can also pull a listing from the platform's discovery pages without touching the event itself, which is how spam and duplicates come off the rack.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Your own title: bento                                     -->
    <!-- ============================================================ -->
    <section id="yours" class="es-news-hr scroll-mt-24 py-16 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-news-slug mb-6" data-reveal aria-hidden="true"><span>Section 05</span></div>
                <h2 class="es-news-serif es-balance es-news-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Your own title on the rack
                </h2>
                <p class="es-news-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    A schedule is the thing that gets racked. Here is what one costs, and what it does the rest of the month.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-news-card relative flex h-full flex-col overflow-hidden p-7">
                        {{-- flex-1 + mt-auto on the closing note: es-tilt-inner is the flex
                             column, so the note only drops to the card's foot if this wrapper
                             grows with it. --}}
                        <div class="relative z-10 flex flex-1 flex-col">
                            <p class="es-news-folio mb-3">Publishing</p>
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-news-serif es-news-ink text-xl font-bold">Publishing is the free part</h3>
                                <span class="es-news-plan">Free</span>
                            </div>
                            <p class="es-news-muted mb-4">Unlimited events and schedules. Weekly and monthly patterns with exceptions for the dates you skip, sub-schedules to keep strands apart, and free registration with a capacity limit counted per date.</p>
                            <p class="es-news-muted mt-auto text-sm">None of that is a trial. It is the free plan.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-news-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <p class="es-news-folio mb-3">The address</p>
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-news-serif es-news-ink text-xl font-bold">Your own address</h3>
                                <span class="es-news-plan">Free</span>
                            </div>
                            <p class="es-news-muted">Every schedule gets its own web address, and the same calendar embeds on the site you already have.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-news-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <p class="es-news-folio mb-3">The till</p>
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-news-serif es-news-ink text-xl font-bold">Zero platform fees</h3>
                                <span class="es-news-plan es-news-plan-pro">Pro</span>
                            </div>
                            <p class="es-news-muted">Connect Stripe and sell from the event page, with QR check-in on the door. Event Schedule takes nothing out of the sale.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-news-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-1 flex-col">
                            <p class="es-news-folio mb-3">Readers</p>
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-news-serif es-news-ink text-xl font-bold">Readers you can write to</h3>
                                <span class="es-news-plan">Free</span>
                            </div>
                            <p class="es-news-muted mb-4">People who follow your schedule leave you a name and an email address, and you write to them when you have something to say. Open and click rates come back afterwards.</p>
                            <p class="es-news-muted mt-auto text-sm">10 emails a month on Free, 100 on Pro, 1,000 on Enterprise, counted per recipient. Nothing is ever sent on your behalf.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-news-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <p class="es-news-folio mb-3">Syndication</p>
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-news-serif es-news-ink text-xl font-bold">Two-way calendar sync</h3>
                                <span class="es-news-plan">Free</span>
                            </div>
                            <p class="es-news-muted">Google, Outlook and CalDAV, in both directions, so the calendar you already keep stays the one you keep.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-news-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-1 flex-col">
                            <p class="es-news-folio mb-3">Circulation</p>
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-news-serif es-news-ink text-xl font-bold">What the listing did</h3>
                                <span class="es-news-plan">Free</span>
                            </div>
                            <p class="es-news-muted mb-4">Built-in analytics count page views, the devices people read on, where they came from and which listings they opened. Circulation figures, after the fact. There is no live reader counter, because there is no such thing.</p>
                            <p class="es-news-muted mt-auto text-sm">
                                There is also a QR code for your schedule on every plan, for the poster in the window.
                                <a href="{{ marketing_url('/pricing') }}" class="es-news-link font-medium hover:underline">See what each plan costs</a>
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
    <!-- 7. Related pages                                             -->
    <!-- ============================================================ -->
    <section class="es-news-hr py-14">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-news-serif es-news-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Elsewhere in the kiosk</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/search', 'Search', 'Find one title'], ['/use-cases', 'Use cases', 'Who racks here'], ['/features', 'Features', 'What a schedule does'], ['/pricing', 'Pricing', 'Free, Pro, Enterprise']] as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-news-hover es-news-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-news-hover-title es-news-ink mb-1 text-sm font-bold transition-colors">{{ $relName }}</span>
                        <span class="es-news-muted mb-3 text-xs">{{ $relBlurb }}</span>
                        <span class="es-news-hover-arrow es-news-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Open
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-news-hr scroll-mt-24 py-16 lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <div class="es-news-slug mb-6" data-reveal aria-hidden="true"><span>Section 06</span></div>
                <h2 class="es-news-serif es-balance es-news-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Questions at the counter
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
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Finale: start a title (pinned dark band)                  -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="mx-auto max-w-6xl">
            <div class="es-news-band noise relative overflow-hidden rounded-[2rem] px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-news-folio mb-4">Free forever</p>
                    <h2 class="es-news-serif es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        {{ __('messages.create_your_own_schedule') }}
                    </h2>
                    <p class="es-news-muted mx-auto mb-10 max-w-2xl text-lg">
                        {{ __('messages.share_events_cta') }}
                    </p>

                    {{-- The empty slot. The page has spent nine sections describing a rack;
                         the finale is the gap in it, and the address you type below is the
                         masthead that fills it. The unset masthead is drawn as bars rather
                         than placeholder words: nothing here is text, so there is no ink to
                         measure and nothing to read out. Every value is fixed, so the slot
                         renders identically with .dark on and off like the band around it. --}}
                    <div class="es-news-slot mx-auto mb-8 max-w-2xl text-left">
                        <div class="es-news-rail" aria-hidden="true"></div>
                        <div class="es-news-plate">
                            <p class="es-news-folio">Your slot &middot; unprinted</p>
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
                            <input id="es-claim-input" type="text" placeholder="your-title" autocomplete="off" spellcheck="false" maxlength="30"
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

                    <p class="es-news-muted mt-6 text-sm">No credit card required</p>
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
