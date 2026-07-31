<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Live Concerts | Promote Your Shows</x-slot>
    <x-slot name="description">Put a whole tour routing online at once: a room, a door time and an on-sale in every city. Sell livestream tickets beside room tickets, write to your followers yourself. Zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Live Concerts</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Live Concerts",
        "description": "Put a whole tour routing online at once: a room, a door time and an on-sale in every city, sold from one address with zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Concert Promoters and Touring Shows"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Live Concerts",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Concert and Tour Scheduling Software",
        "operatingSystem": "Web",
        "description": "A tour routing published as one schedule: every date with its own room and door time, its own ticket types, and a running order on the event page.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Every tour date is its own event with its own venue and door time",
            "One public address for the whole routing, embeddable on your own site",
            "A running order on the event page built from event parts",
            "Named ticket types with their own price, quantity and sales window",
            "Ticket inventory counted per occurrence date",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Free registration with a capacity limit for free shows",
            "Recurring residencies with day-of-week patterns and date exceptions",
            "Direct newsletters to your followers, with open and click rates",
            "Per-event views, sales and revenue in built-in analytics",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "An online date carries a link to wherever you are streaming"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "live concert streaming, virtual concert tickets, livestream concerts, tour routing, concert promoter calendar, gig schedule",
        "screenshot": "{{ asset('images/social/for-live-concerts.png') }}",
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
           For-live-concerts "Live On Stage" styles.

           THE OBJECT is the bulb-lit marquee outside the room, and the
           routing sheet the tour manager keeps behind it. Out front the
           sign says one town; the sheet says every city, every room,
           every door time. That is exactly the product's shape: a tour
           is not one event, it is a routing of dates, each with its own
           venue, its own door time and its own on-sale.

           NOT the tour poster: /for-musicians already owns that object
           (poster type, tears, rubber stamps, misregistration), and it
           is linked from this page's related strip. The only print
           material borrowed here is a plain dot screen as page texture.

           FOUR PLACES THE MARQUEE APPEARS, and it is the SAME PHYSICAL
           SIGN in all of them, so it is pinned dark in both colour
           modes: the hero sign, the letterboard in the day-sheet
           section (both .es-stage-sign), the mid-page frontage band
           (.es-stage-frontage) and the finale. Everything nested inside
           those two selectors must avoid `dark:` utilities and must
           override the shared classes that flip with the colour mode
           (.grid-overlay, .animate-shimmer, .es-claim:focus-within,
           .es-stage-card, .es-stage-plan, .es-stage-stamp).

           THE INFORMATION DEVICES, not decoration:
           - .es-stage-routing is a real table. A routing IS a record.
           - .es-stage-slat is the letterboard, and the bar behind each
             line is that part's REAL duration over the longest part, so
             the headline set is the widest slat and doors is a stub.
             Deliberately NOT a proportional vertical time axis:
             /for-music-venues owns the show day on a minute-scaled
             axis and claims it as its differentiator, so a second one
             here would make this page the derivative of a sibling it
             links to.

           COLOUR: the page keeps its established rose accent plus the
           warm amber of a bulb. Measured, not guessed - ink #181214 on
           paper #f8f5f4 is 17.05, muted #57494d is 7.85, rose #9f1239 is
           7.39, amber-brown #8a5410 is 5.77. In the dark: #f3ecee 16.69
           on #100c0e, muted #b3a8ab 7.68, rose #fda4af 10.27, bulb
           #fbbf24 11.64. Inside the fixed-dark sign and frontage the
           lit inks are used in BOTH modes (.es-stage-onink 16.5,
           .es-stage-onmuted 8.6, .es-stage-lit 10.2, .es-stage-bulbink
           11.5), so there is no `text-gray-500` anywhere on this page.

           BLADE RULE for this block: no @supports probes with a "#" hex
           inside the condition; it breaks Blade compilation of every
           later parenthesized directive.

           TAILWIND RULE for this page: this block is the ONLY place new
           colours, rules and small type sizes may be declared. An
           arbitrary utility this page invents (text-[#b3a8ab],
           border-[rgba(24,18,20,0.1)]) is never compiled, because the
           CSS bundle is built from the pages that existed at build time
           and this page cannot trigger a rebuild. So the edges, wells,
           tips and small sizes below exist as real classes instead.
           ============================================================== */

        /* --- Ground and ink -------------------------------------- */
        .es-stage-page { background-color: #f8f5f4; color: #181214; }
        .dark .es-stage-page { background-color: #100c0e; color: #f3ecee; }
        .es-stage-ink { color: #181214; }
        .dark .es-stage-ink { color: #f3ecee; }
        .es-stage-muted { color: #57494d; }
        .dark .es-stage-muted { color: #b3a8ab; }
        .es-stage-accent { color: #9f1239; }
        .dark .es-stage-accent { color: #fda4af; }
        /* Inks used INSIDE the fixed-dark sign and frontage, in both modes. */
        .es-stage-onink { color: #f3ecee; }
        .es-stage-onmuted { color: #b3a8ab; }
        .es-stage-lit { color: #fda4af; }
        .es-stage-bulbink { color: #fbbf24; }

        /* --- Dot screen: the material the page is printed on ------- */
        .es-stage-screen {
            background-image: radial-gradient(rgba(24, 18, 20, 0.16) 1px, transparent 1.4px);
            background-size: 7px 7px;
            opacity: 0.5;
        }
        .dark .es-stage-screen {
            background-image: radial-gradient(rgba(243, 236, 238, 0.16) 1px, transparent 1.4px);
            opacity: 0.32;
        }

        /* --- Rules, wells and tips. Real classes, because an
               arbitrary Tailwind colour invented here is never
               compiled into the bundle. -------------------------- */
        .es-stage-edge { border-color: rgba(24, 18, 20, 0.1); }
        .dark .es-stage-edge { border-color: rgba(243, 236, 238, 0.12); }
        .es-stage-edge-soft { border-color: rgba(24, 18, 20, 0.08); }
        .dark .es-stage-edge-soft { border-color: rgba(243, 236, 238, 0.08); }
        .es-stage-edge-hard { border-color: rgba(24, 18, 20, 0.16); }
        .dark .es-stage-edge-hard { border-color: rgba(243, 236, 238, 0.18); }
        .es-stage-well {
            border: 1px solid rgba(24, 18, 20, 0.12);
            background-color: rgba(24, 18, 20, 0.035);
        }
        .dark .es-stage-well {
            border-color: rgba(243, 236, 238, 0.12);
            background-color: rgba(243, 236, 238, 0.04);
        }
        .es-stage-tip {
            border: 1px solid rgba(24, 18, 20, 0.14);
            background-color: #ffffff;
            color: #181214;
        }
        .dark .es-stage-tip {
            border-color: rgba(243, 236, 238, 0.14);
            background-color: #1c1719;
            color: #f3ecee;
        }
        .es-stage-xs { font-size: 0.7rem; line-height: 1.35; }
        .es-stage-2xs { font-size: 0.62rem; line-height: 1.4; }

        /* --- Card stock: the page's card ------------------------- */
        .es-stage-card {
            border: 1px solid rgba(24, 18, 20, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
        }
        .dark .es-stage-card {
            border-color: rgba(243, 236, 238, 0.12);
            background-color: #1c1719;
        }

        /* --- The marquee: one sign, fixed dark in both modes ----- */
        .es-stage-sign,
        .es-stage-frontage {
            background-color: #120d10;
            background-image: radial-gradient(120% 100% at 50% 0%, #241a1e 0%, #171013 55%, #0c0709 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 233, 184, 0.07);
        }
        /* Shared classes that flip with the colour mode, pinned inside the sign. */
        .es-stage-frontage .grid-overlay {
            background-image:
                linear-gradient(rgba(255, 233, 184, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 233, 184, 0.05) 1px, transparent 1px);
        }
        .es-stage-frontage .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 233, 184, 0.18), transparent);
            background-size: 200% 100%;
        }
        .es-stage-frontage .es-claim:focus-within {
            border-color: rgba(253, 164, 175, 0.75);
            box-shadow: 0 0 0 4px rgba(253, 164, 175, 0.22);
        }
        .es-stage-frontage .es-stage-card {
            border-color: rgba(255, 233, 184, 0.14);
            background-color: #241d20;
        }

        /* --- Bulbs. A chase, not a twinkle: the delay steps along
               the row so the light appears to travel. -------------- */
        .es-stage-bulbrow {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0.75rem;
        }
        .es-stage-bulb {
            width: 7px;
            height: 7px;
            flex: none;
            border-radius: 9999px;
            background: radial-gradient(circle, #fff6e2 0%, #fbbf24 55%, rgba(251, 191, 36, 0) 78%);
            box-shadow: 0 0 8px 2px rgba(251, 191, 36, 0.45);
            opacity: 0.55;
            animation: es-stage-chase 1.9s ease-in-out infinite;
            animation-delay: var(--bulb-delay, 0s);
        }
        @keyframes es-stage-chase {
            0%, 68%, 100% { opacity: 0.42; box-shadow: 0 0 6px 1px rgba(251, 191, 36, 0.3); }
            18%           { opacity: 1;    box-shadow: 0 0 13px 4px rgba(251, 191, 36, 0.6); }
        }

        /* --- The sign face: letterboard slats and warm wash ------ */
        .es-stage-signface {
            border: 1px solid rgba(255, 233, 184, 0.18);
            border-radius: 0.5rem;
            background-color: #1b1215;
            background-image: repeating-linear-gradient(
                to bottom,
                rgba(255, 233, 184, 0.05) 0,
                rgba(255, 233, 184, 0.05) 1px,
                transparent 1px,
                transparent 11px);
            box-shadow: inset 0 0 34px rgba(251, 191, 36, 0.1);
        }
        .es-stage-signtype {
            font-size: 0.7rem;
            font-weight: 900;
            letter-spacing: 0.34em;
            text-transform: uppercase;
            color: #fbbf24;
            text-shadow: 0 0 18px rgba(251, 191, 36, 0.45);
        }
        .es-stage-signlabel {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .es-stage-signrule {
            height: 1px;
            background: linear-gradient(90deg, rgba(251, 191, 36, 0), rgba(251, 191, 36, 0.5), rgba(251, 191, 36, 0));
        }

        /* --- Eyebrow and the numbered stamp --------------------- */
        .es-stage-tag {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: #57494d;
        }
        .dark .es-stage-tag { color: #b3a8ab; }
        .es-stage-frontage .es-stage-tag { color: #fbbf24; }

        .es-stage-stamp {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid rgba(24, 18, 20, 0.18);
            background-color: #ffffff;
            color: #181214;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            font-size: 0.78rem;
            letter-spacing: 0.14em;
        }
        .dark .es-stage-stamp { border-color: rgba(243, 236, 238, 0.2); background-color: #1c1719; color: #f3ecee; }
        .es-stage-frontage .es-stage-stamp { border-color: rgba(255, 233, 184, 0.22); background-color: #241d20; color: #f3ecee; }
        .es-stage-stamp::before,
        .es-stage-stamp::after {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 9999px;
            background: #fbbf24;
            box-shadow: 0 0 7px 1px rgba(251, 191, 36, 0.5);
        }

        /* --- The routing sheet: a record, so a real table -------- */
        .es-stage-routing { width: 100%; border-collapse: collapse; }
        .es-stage-routing th,
        .es-stage-routing td { text-align: start; vertical-align: middle; padding: 0.7rem 0.6rem; }
        .es-stage-routing thead th {
            padding-top: 0;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #57494d;
            border-bottom: 2px solid rgba(24, 18, 20, 0.16);
        }
        .dark .es-stage-routing thead th { color: #b3a8ab; border-bottom-color: rgba(243, 236, 238, 0.18); }
        .es-stage-routing tbody tr { border-bottom: 1px solid rgba(24, 18, 20, 0.09); }
        .dark .es-stage-routing tbody tr { border-bottom-color: rgba(243, 236, 238, 0.09); }
        /* Deliberately NO font-size here: this class rides alongside
           text-3xl and text-2xl, and this block loads after the bundle,
           so a size here would win and flatten those figures. */
        .es-stage-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.01em;
        }
        /* On a phone the routing loses its padding and its weekday rather
           than its right-hand column. */
        @media (max-width: 640px) {
            .es-stage-routing th,
            .es-stage-routing td { padding: 0.6rem 0.3rem; }
            .es-stage-status { padding: 0.1rem 0.4rem; font-size: 0.58rem; letter-spacing: 0.06em; }
        }
        .es-stage-status {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            white-space: nowrap;
            padding: 0.1rem 0.5rem;
            border-radius: 9999px;
            border: 1px solid currentColor;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .es-stage-status-live { color: #9f1239; }
        .dark .es-stage-status-live { color: #fda4af; }
        .es-stage-status-gone { color: #57494d; }
        .dark .es-stage-status-gone { color: #b3a8ab; }
        .es-stage-status-soon { color: #7c4a0d; }
        .dark .es-stage-status-soon { color: #fbbf24; }

        /* --- The letterboard. Slats pushed into the sign face, one
               line per event part. The bar behind a line is its real
               duration over the longest part's, so the headline set is
               the widest slat on the board and doors is a stub. Fixed
               dark in both modes: it is the same sign as the hero. -- */
        .es-stage-slat {
            position: relative;
            display: grid;
            grid-template-columns: 3.3rem minmax(0, 1fr) auto;
            align-items: baseline;
            gap: 0 0.7rem;
            padding: 0.5rem 0.45rem;
            border-bottom: 1px solid rgba(255, 233, 184, 0.1);
        }
        .es-stage-slat:last-child { border-bottom: 0; }
        .es-stage-slat-fill {
            position: absolute;
            inset-block: 0.12rem;
            inset-inline-start: 0;
            z-index: 0;
            border-start-end-radius: 0.3rem;
            border-end-end-radius: 0.3rem;
            background-image: linear-gradient(90deg, rgba(251, 191, 36, 0.15), rgba(251, 191, 36, 0.03));
        }
        .es-stage-slat-set .es-stage-slat-fill {
            background-image: linear-gradient(90deg, rgba(253, 164, 175, 0.2), rgba(253, 164, 175, 0.04));
        }
        .es-stage-slat-time {
            position: relative;
            z-index: 1;
            font-size: 0.72rem;
            font-weight: 800;
            color: #fbbf24;
        }
        .es-stage-slat-body { position: relative; z-index: 1; min-width: 0; }
        .es-stage-slat-name {
            display: block;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #f3ecee;
        }
        .es-stage-slat-note { display: block; color: #b3a8ab; }
        .es-stage-slat-len {
            position: relative;
            z-index: 1;
            white-space: nowrap;
            font-size: 0.68rem;
            font-weight: 700;
            color: #b3a8ab;
        }

        /* --- Plan tags ----------------------------------------- */
        .es-stage-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(159, 18, 57, 0.4);
            color: #9f1239;
        }
        .dark .es-stage-plan { border-color: rgba(253, 164, 175, 0.45); color: #fda4af; }
        .es-stage-frontage .es-stage-plan { border-color: rgba(253, 164, 175, 0.45); color: #fda4af; }
        .es-stage-plan-pro { border-color: rgba(24, 18, 20, 0.35); color: #181214; }
        .dark .es-stage-plan-pro { border-color: rgba(243, 236, 238, 0.38); color: #f3ecee; }

        /* --- Chips (the marquee of show types) ------------------ */
        .es-stage-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(24, 18, 20, 0.16);
            background-color: rgba(255, 255, 255, 0.72);
            color: #57494d;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .dark .es-stage-chip {
            border-color: rgba(243, 236, 238, 0.16);
            background-color: rgba(243, 236, 238, 0.05);
            color: #b3a8ab;
        }

        /* --- The LIVE pill on the streamed date ----------------- */
        .es-stage-live {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 9999px;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #9f1239;
            background-color: rgba(159, 18, 57, 0.12);
        }
        .dark .es-stage-live { color: #fda4af; background-color: rgba(253, 164, 175, 0.16); }
        .es-stage-livedot {
            width: 6px;
            height: 6px;
            border-radius: 9999px;
            background: currentColor;
            animation: es-stage-pulse 1.7s ease-out infinite;
        }
        @keyframes es-stage-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(159, 18, 57, 0.5); }
            70%  { box-shadow: 0 0 0 7px rgba(159, 18, 57, 0); }
            100% { box-shadow: 0 0 0 0 rgba(159, 18, 57, 0); }
        }

        /* --- Links and buttons --------------------------------- */
        .es-stage-link { color: #9f1239; }
        .es-stage-link:hover { color: #181214; }
        .dark .es-stage-link { color: #fda4af; }
        .dark .es-stage-link:hover { color: #f3ecee; }

        /* White on #9f1239 is 8.02; #100c0e on #fda4af is 10.27. The
           colour lives here rather than on a text- utility so the two
           modes cannot drift apart. */
        .es-stage-btn {
            background-color: #9f1239;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(159, 18, 57, 0.55);
        }
        .es-stage-btn:hover { background-color: #85102f; box-shadow: 0 22px 44px -14px rgba(159, 18, 57, 0.65); }
        .dark .es-stage-btn { background-color: #fda4af; color: #100c0e; }
        .dark .es-stage-btn:hover { background-color: #fec2ca; }
        /* Inside the sign the button is part of the physical object, so
           it is the lit version in BOTH modes. */
        .es-stage-frontage .es-stage-btn {
            background-color: #fda4af;
            color: #100c0e;
            box-shadow: 0 18px 36px -14px rgba(253, 164, 175, 0.4);
        }
        .es-stage-frontage .es-stage-btn:hover { background-color: #fec2ca; box-shadow: 0 22px 44px -14px rgba(253, 164, 175, 0.5); }

        /* --- Hover treatment for the FAQ and related cards ----- */
        .es-stage-hover:hover { border-color: rgba(159, 18, 57, 0.45); }
        .dark .es-stage-hover:hover { border-color: rgba(253, 164, 175, 0.45); }
        .es-stage-hover:hover .es-stage-hover-title,
        .es-stage-hover:hover .es-stage-hover-arrow { color: #9f1239; }
        .dark .es-stage-hover:hover .es-stage-hover-title,
        .dark .es-stage-hover:hover .es-stage-hover-arrow { color: #fda4af; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(159, 18, 57, 0.14), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(253, 164, 175, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(159, 18, 57, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(253, 164, 175, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #9f1239; }
        .dark .es-dot.is-active .es-dot-pip { background: #fda4af; }

        /* --- Focus rings. No border-radius here: setting it would
               change the element's own shape on focus. ---------- */
        #es-stage-page a:focus-visible,
        #es-stage-page summary:focus-visible,
        #es-stage-page button:focus-visible {
            outline: 2px solid #9f1239;
            outline-offset: 3px;
        }
        .dark #es-stage-page a:focus-visible,
        .dark #es-stage-page summary:focus-visible,
        .dark #es-stage-page button:focus-visible {
            outline-color: #fda4af;
        }
        .es-stage-frontage a:focus-visible,
        .es-stage-frontage summary:focus-visible,
        .es-stage-frontage button:focus-visible {
            outline-color: #fbbf24 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-stage-bulb,
            .es-stage-livedot { animation: none !important; }
            .es-stage-bulb { opacity: 0.8; }
        }
    </style>

    @php
        // ------------------------------------------------------------------
        // The routing: nine dates, nine rooms. Each row is one event with
        // its own venue and its own door time - Event + venue_id, joined to
        // the room's own schedule through the event_role pivot.
        // ------------------------------------------------------------------
        $routing = [
            ['Thu', 'Sep 10', 'Marble Hall',      'Bristol',    '19:00', 'live', 'On sale'],
            ['Fri', 'Sep 11', 'The Gate Rooms',   'Cardiff',    '19:30', 'live', 'On sale'],
            ['Sat', 'Sep 12', 'Albert Yard',      'Manchester', '19:00', 'gone', 'Sold out'],
            ['Sun', 'Sep 13', 'Brickhouse',       'Leeds',      '19:30', 'soon', 'Waitlist'],
            ['Tue', 'Sep 15', 'The Loft',         'Glasgow',    '19:00', 'live', 'On sale'],
            ['Wed', 'Sep 16', 'Quay Chapel',      'Newcastle',  '19:30', 'live', 'Free RSVP'],
            ['Fri', 'Sep 18', 'Ironworks',        'Nottingham', '19:00', 'live', 'On sale'],
            ['Sat', 'Sep 19', 'The Hare Rooms',   'Birmingham', '19:00', 'soon', 'Opens Aug 8'],
            ['Sun', 'Sep 20', 'Kings Hall',       'London',     '18:30', 'live', 'On sale'],
        ];

        // ------------------------------------------------------------------
        // The day sheet, as slats on the letterboard. Minutes past midnight,
        // so each slat's bar is its real length measured against the longest
        // part: the headline set fills the slat, doors is a stub. This is
        // what an event part holds - a name, a start time and an end time.
        // ------------------------------------------------------------------
        $dayParts = [
            ['Doors',        'Room open, merch out',        19 * 60,      19 * 60 + 30, false],
            ['Local opener', 'The room the promoter picks', 19 * 60 + 30, 20 * 60 + 5,  false],
            ['Changeover',   'Backline swap',               20 * 60 + 5,  20 * 60 + 20, false],
            ['Headline set', 'The reason they came',        20 * 60 + 20, 22 * 60 + 20, true],
            ['Encore',       'Two songs, house lights out', 22 * 60 + 20, 22 * 60 + 45, false],
        ];

        // The bar behind each slat is that part's REAL duration measured
        // against the longest part, so the headline set is the widest slat on
        // the board and the changeover is a stub. Deliberately a horizontal
        // letterboard and NOT a minute-scaled vertical axis: /for-music-venues
        // owns that device and claims it as its differentiator, so a second one
        // here would make this page derivative of a sibling it links to.
        $longestPart = max(array_map(fn ($p) => $p[3] - $p[2], $dayParts));
        $clock = fn ($minute) => sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
        $spanLabel = function ($minutes) {
            $h = intdiv($minutes, 60);
            $m = $minutes % 60;

            return $h ? ($m ? $h.'h '.$m.'m' : $h.'h') : $m.'m';
        };

        $faqs = [
            [
                'q' => 'Do I need special equipment to stream a live concert?',
                'a' => 'No, and there is nothing to install. Event Schedule does not stream anything itself: it holds the date, the room, the running order and the tickets, and an online date carries one link to wherever the stream actually lives. Phone straight to Instagram Live, OBS into YouTube Live or Twitch, or a multi-camera truck - all Event Schedule needs is the URL.',
            ],
            [
                'q' => 'Can I sell virtual tickets and venue tickets for the same show?',
                'a' => 'Yes. They are two named ticket types on the same date, so one can be "Standing" at thirty and the other "Livestream" at twelve, each with its own price, quantity and sales window. Selling is free up to 25 paid tickets a month and five dollars a month on Pro past that, and Event Schedule charges zero platform fees on the sale either way. The full stream link lives on the buyer\'s own ticket page: the public event page shows the room, or the domain you are streaming on when the date has no room at all.',
            ],
            [
                'q' => 'What streaming platforms does Event Schedule work with?',
                'a' => 'Any platform that gives you a URL: YouTube Live, Twitch, Instagram Live, Facebook Live, Vimeo, a custom RTMP front end. To be exact about what this is, it is one link field on the event rather than an integration - no accounts are connected and no viewer numbers come back. That is also why it never breaks when you change platforms.',
            ],
            [
                'q' => 'Is Event Schedule really free for streaming concerts?',
                'a' => 'Yes. Unlimited dates, the whole routing on one address, recurring residencies with date exceptions, sub-schedules, two-way Google, Outlook and CalDAV sync, the embeddable calendar, free registration with a capacity limit, built-in analytics, ten newsletter recipients a month, selling up to 25 paid tickets a month and scanning them at the door are all free forever. Unlimited ticket sales, passes and the live check-in dashboard are five dollars a month on Pro. There are zero platform fees on ticket sales at every tier, and past your own Stripe account the money is yours.',
            ],
            [
                'q' => 'What happens when a date moves or gets pulled?',
                'a' => 'On a residency, a date exception takes that single night out of the pattern, and guests simply see the day absent rather than crossed out. On a one-off date you change the date on the event. Being straight with you: there is no conflict detection anywhere in Event Schedule, so nothing will warn you that you have booked two shows on the same night. The routing table is where you catch that, which is why it is the first thing on this page.',
            ],
            [
                'q' => 'Can the room show my date on its own calendar?',
                'a' => 'Yes, if the room runs a schedule here. Add the venue to the date and the venue name on your event page links straight to their schedule. Whether the date appears on their calendar is their call: it lands accepted if you are a member of that schedule or if they take requests without approval, and otherwise it waits on their requests tab and emails them that it arrived.',
            ],
        ];

        $dotSections = [
            ['top', 'Live on stage'],
            ['routing', 'The routing'],
            ['doors', 'The day sheet'],
            ['onsale', 'On sale'],
            ['frontage', 'One address'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Doors'],
        ];
    @endphp

    <div id="es-stage-page" class="es-stage-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the poster, and the sign it hangs beside            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(159, 18, 57, 0.28), rgba(159, 18, 57, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(251, 191, 36, 0.22), rgba(251, 191, 36, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="es-stage-screen absolute inset-0 [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-stage-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z" />
                        </svg>
                        <span class="es-stage-muted text-sm font-medium tracking-wide">For concert promoters and touring shows</span>
                    </div>

                    <h1 class="es-balance es-stage-ink mb-8 text-[2.4rem] font-black leading-[1.04] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A tour is not one event.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">It is <span class="es-stage-accent">nine rooms</span> in a row.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-stage-muted mb-6 max-w-xl text-lg sm:text-xl">
                        Every live concert on the run has its own room, its own door time and its own on-sale. Put the whole routing up once, sell every night from a single address, and keep the takings: Event Schedule charges zero platform fees on ticket sales.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#routing" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the routing
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-stage-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your concert schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The sign. Same physical object in both colour modes. -->
                <div class="es-fade-up es-d-4" data-reveal="panel">
                    <div class="es-stage-sign relative overflow-hidden rounded-3xl border border-white/10 p-4 shadow-2xl sm:p-5">
                        <div class="es-stage-bulbrow pb-3" aria-hidden="true">
                            @for ($b = 0; $b < 13; $b++)
                                <span class="es-stage-bulb" style="--bulb-delay: {{ round($b * -0.14, 2) }}s;"></span>
                            @endfor
                        </div>

                        <div class="es-stage-signface px-5 py-6 text-center sm:px-7 sm:py-8">
                            <div class="es-stage-signtype">Live on stage</div>
                            <div class="es-stage-signrule mx-auto my-4 w-2/3"></div>
                            <div class="es-stage-onink text-2xl font-black tracking-tight sm:text-3xl">The Lantern Hours</div>
                            <div class="es-stage-onmuted mt-1 text-xs font-semibold uppercase tracking-[0.22em]">Autumn routing</div>

                            <div class="es-stage-signrule mx-auto my-4 w-2/3"></div>

                            <dl class="mx-auto grid max-w-xs grid-cols-3 gap-2 text-center">
                                <div>
                                    <dt class="es-stage-onmuted es-stage-signlabel">Tonight</dt>
                                    <dd class="es-stage-bulbink mt-1 text-sm font-black">Kings Hall</dd>
                                </div>
                                <div>
                                    <dt class="es-stage-onmuted es-stage-signlabel">Doors</dt>
                                    <dd class="es-stage-bulbink es-stage-num mt-1 text-sm font-black">18:30</dd>
                                </div>
                                <div>
                                    <dt class="es-stage-onmuted es-stage-signlabel">Dates</dt>
                                    <dd class="es-stage-bulbink mt-1 text-sm font-black"><span data-count-to="9">9</span></dd>
                                </div>
                            </dl>
                        </div>

                        <div class="es-stage-bulbrow pt-3" aria-hidden="true">
                            @for ($b = 0; $b < 13; $b++)
                                <span class="es-stage-bulb" style="--bulb-delay: {{ round($b * -0.14, 2) }}s;"></span>
                            @endfor
                        </div>
                    </div>
                    <p class="es-stage-muted mt-4 text-center text-xs">
                        One schedule, one address. The nine dates behind this sign are nine events, each with its own room.
                    </p>
                </div>
            </div>

            <!-- Show-type marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Club tours', 'Album release shows', 'Support runs', 'Festival sets', 'Residencies', 'Acoustic sets', 'Jazz nights', 'DJ sets', 'All-dayers', 'Streamed shows'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-stage-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The routing: a record, so a real table                    -->
    <!-- ============================================================ -->
    <section id="routing" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-stage-stamp mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-stage-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The routing</p>
                <h2 class="es-balance es-stage-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The back of the poster is <span class="es-stage-accent">the whole job.</span>
                </h2>
                <p class="es-stage-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A routing is a record, so it is a table here too. Nine dates, nine rooms, nine door times, and one public address that carries all of them.
                </p>
            </div>

            <div class="es-stage-card p-5 sm:p-7" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-stage-routing">
                        <caption class="es-stage-tag pb-4 text-start">Autumn routing: date, room, doors and where the tickets stand</caption>
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Room</th>
                                <th scope="col" class="hidden sm:table-cell">Doors</th>
                                <th scope="col" class="text-end sm:text-start">Tickets</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($routing as [$rDay, $rDate, $rRoom, $rCity, $rDoors, $rState, $rLabel])
                                <tr>
                                    <td class="es-stage-num es-stage-muted whitespace-nowrap text-sm"><span class="hidden sm:inline">{{ $rDay }} </span>{{ $rDate }}</td>
                                    <th scope="row" class="es-stage-ink text-sm font-bold">
                                        {{ $rRoom }}
                                        <span class="es-stage-muted block es-stage-xs font-semibold uppercase tracking-[0.14em]">{{ $rCity }}</span>
                                    </th>
                                    <td class="es-stage-num es-stage-muted hidden text-sm sm:table-cell">{{ $rDoors }}</td>
                                    <td class="text-end sm:text-start">
                                        <span class="es-stage-status @if ($rState === 'live') es-stage-status-live @elseif ($rState === 'gone') es-stage-status-gone @else es-stage-status-soon @endif">{{ $rLabel }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 grid gap-4 es-stage-edge border-t pt-5 sm:grid-cols-3">
                    <div>
                        <p class="es-stage-tag mb-2">The room</p>
                        <p class="es-stage-muted text-sm">Each date carries its own venue, and a claimed room's name on your event page links to their schedule.</p>
                    </div>
                    <div>
                        <p class="es-stage-tag mb-2">The stock</p>
                        <p class="es-stage-muted text-sm">Ticket quantity is counted per date, so Manchester selling out has nothing to do with Glasgow.</p>
                    </div>
                    <div>
                        <p class="es-stage-tag mb-2">The window</p>
                        <p class="es-stage-muted text-sm">A ticket type's sales window is one start and one end, which is exactly right for a single dated show.</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3" data-reveal-group="90">
                <div class="es-stage-card p-6 text-center" data-reveal="panel">
                    <div class="es-stage-accent es-stage-num mb-1 text-3xl font-black">$0</div>
                    <p class="es-stage-muted text-sm">Platform fees on every ticket you sell, on every plan. Your own Stripe account, your money.</p>
                </div>
                <div class="es-stage-card p-6 text-center" data-reveal="panel">
                    <div class="es-stage-accent es-stage-num mb-1 text-3xl font-black">$5</div>
                    <p class="es-stage-muted text-sm">A month for Pro. The free plan sells 25 paid tickets a month and scans the door already; Pro takes the ceiling off and adds passes and the check-in dashboard.</p>
                </div>
                <div class="es-stage-card p-6 text-center" data-reveal="panel">
                    <div class="es-stage-accent es-stage-num mb-1 text-3xl font-black">10</div>
                    <p class="es-stage-muted text-sm">Newsletter emails a month on the free plan, counted per recipient. Pro is 100, Enterprise 1,000.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The day sheet: proportional, because length is the point  -->
    <!-- ============================================================ -->
    <section id="doors" class="scroll-mt-24 es-stage-edge-soft border-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-stage-stamp mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-stage-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The day sheet</p>
                    <h2 class="es-balance es-stage-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Doors at seven. On at <span class="es-stage-accent">twenty past eight.</span>
                    </h2>
                    <p class="es-stage-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Every date can carry a running order: named parts with a start and an end time, in order, published on the event page. The strip beside this is drawn from those times, which is why doors is a sliver and the headline set is most of the night.
                    </p>
                    <ul class="es-stage-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-stage-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>The support act stops being a rumour. People who came for the opener know when to be in the room.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-stage-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Times can be hidden if you would rather publish the order without committing to the clock.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-stage-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="flex flex-wrap items-center gap-2">
                                <span>Running orders are free. Scanning a photo of the day sheet to fill one in is Enterprise.</span>
                                <span class="es-stage-plan es-stage-plan-pro">Enterprise</span>
                            </span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-stage-card p-5 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">Kings Hall, London</h3>
                            <span class="es-stage-num es-stage-muted text-sm">Sun Sep 20</span>
                        </div>

                        {{-- The letterboard. One slat per event part; the bar behind
                             each line is its real duration over the longest part's, so
                             the headline set is the widest slat and the changeover is a
                             stub. The face is fixed dark in both colour modes, so every
                             ink in here is a literal value rather than a dark: variant. --}}
                        <div class="es-stage-signface p-3 sm:p-4">
                            <p class="es-stage-onmuted es-stage-signlabel">Day sheet</p>
                            <div class="es-stage-signrule" aria-hidden="true"></div>
                            <ul class="mt-1">
                                @foreach ($dayParts as [$pName, $pNote, $pFrom, $pTo, $pSet])
                                    <li class="es-stage-slat @if ($pSet) es-stage-slat-set @endif">
                                        <span class="es-stage-slat-fill"
                                              style="width: {{ round((($pTo - $pFrom) / $longestPart) * 100, 2) }}%;"
                                              aria-hidden="true"></span>
                                        <span class="es-stage-slat-time es-stage-num">{{ $clock($pFrom) }}</span>
                                        <span class="es-stage-slat-body">
                                            <span class="es-stage-slat-name">{{ $pName }}</span>
                                            <span class="es-stage-slat-note es-stage-2xs">{{ $pNote }}</span>
                                        </span>
                                        <span class="es-stage-slat-len es-stage-num">{{ $spanLabel($pTo - $pFrom) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <p class="es-stage-muted es-stage-edge mt-3 border-t pt-3 text-xs">
                            Five parts, and the bar behind each one is its real length. That is what an event part
                            holds: a name, a start and an end. Running orders are free.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. On sale                                                   -->
    <!-- ============================================================ -->
    <section id="onsale" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-stage-stamp mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-stage-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">On sale</p>
                <h2 class="es-balance es-stage-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Name the tickets. <span class="es-stage-accent">Keep the door.</span>
                </h2>
                <p class="es-stage-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Sales run through your own Stripe account and Event Schedule takes nothing from them. Being clear about the shape of it: these are named ticket types with prices and quantities, not a seating chart. There is no seat map, and nobody is choosing a specific seat.
                </p>
            </div>

            <div class="grid items-start gap-6 lg:grid-cols-[1.05fr_1fr]">
                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="90">
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">Tiers that open and close</h3>
                            <span class="es-stage-plan">Free</span>
                        </div>
                        <p class="es-stage-muted text-sm">Each type gets a price, a quantity, a maximum per order and a sales window, so an early-bird allocation stops on its own. Free to 25 paid tickets a month, uncapped on Pro.</p>
                    </div>
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">Scanned on the way in</h3>
                            <span class="es-stage-plan">Free</span>
                        </div>
                        <p class="es-stage-muted text-sm">Every ticket carries a QR code, scanned from any phone on any plan. Pro adds the check-in dashboard that breaks the running count down by ticket type.</p>
                    </div>
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">Once a night is gone</h3>
                            <span class="es-stage-plan es-stage-plan-pro">Pro</span>
                        </div>
                        <p class="es-stage-muted text-sm">Turn the waitlist on and people join for that date. If a return comes back, they are notified without you doing anything.</p>
                    </div>
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">A free show, capped</h3>
                            <span class="es-stage-plan">Free</span>
                        </div>
                        <p class="es-stage-muted text-sm">Registration with a capacity limit works on every plan, and the remaining count is tracked for each date of a residency separately.</p>
                    </div>
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">Codes for the people you want back</h3>
                            <span class="es-stage-plan es-stage-plan-pro">Pro</span>
                        </div>
                        <p class="es-stage-muted text-sm">Percentage or fixed discount codes with usage limits and an expiry. The volume rate that drops the price once somebody buys several at once sits on the ticket instead, and that part is free.</p>
                    </div>
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">One ticket each</h3>
                            <span class="es-stage-plan es-stage-plan-pro">Pro</span>
                        </div>
                        <p class="es-stage-muted text-sm">Per-attendee tickets give everyone in a party their own confirmation and their own code, instead of one person holding six.</p>
                    </div>
                </div>

                <div class="grid gap-4" data-reveal-group="90">
                    <div class="es-stage-card p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">Kings Hall, London</h3>
                            <span class="es-stage-live"><span class="es-stage-livedot" aria-hidden="true"></span>Streamed too</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ([['Standing', 'Doors 18:30', '$30', '412 of 500'], ['Balcony', 'Seated, limited', '$38', '96 of 100'], ['Livestream', 'Link on your ticket', '$12', 'No cap'], ['Residency pass', 'Every date, once each', '$85', '38 sold']] as [$tName, $tNote, $tPrice, $tStock])
                                <div class="flex items-baseline gap-3 es-stage-edge-soft border-b pb-2 text-sm last:border-0">
                                    <span class="es-stage-ink min-w-0 flex-1 truncate font-semibold">{{ $tName }}</span>
                                    <span class="es-stage-muted hidden truncate text-xs sm:inline">{{ $tNote }}</span>
                                    <span class="es-stage-num es-stage-muted">{{ $tStock }}</span>
                                    <span class="es-stage-ink es-stage-num font-bold">{{ $tPrice }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex items-baseline justify-between es-stage-edge-hard border-t-2 pt-3">
                            <span class="es-stage-tag">Platform fee</span>
                            <span class="es-stage-accent es-stage-num text-lg font-black">$0.00</span>
                        </div>
                        <p class="es-stage-muted mt-3 text-xs">
                            Quantities are held per date. Read the detail on
                            <a href="{{ marketing_url('/features/ticketing') }}" class="es-stage-link font-semibold hover:underline">ticketing</a>.
                        </p>
                    </div>

                    <div class="es-stage-card p-6 sm:p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-ink text-lg font-bold">A pass for the whole run</h3>
                            <span class="es-stage-plan es-stage-plan-pro">Pro</span>
                        </div>
                        <p class="es-stage-muted text-sm">A pass is a ticket type that stays valid across dates: a residency pass, a festival wristband, a members' pass. Set how many uses it has, how many it admits per event, and a cancellation deadline. It sells alongside single tickets rather than instead of them.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. One address (the frontage: fixed-dark marquee band)       -->
    <!-- ============================================================ -->
    <section id="frontage" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-stage-frontage noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-14 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(159, 18, 57, 0.3), rgba(159, 18, 57, 0) 62%); opacity: 0.55;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 62%, rgba(251, 191, 36, 0.2), rgba(251, 191, 36, 0) 62%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="es-stage-bulbrow relative z-10 mb-10" aria-hidden="true">
                @for ($b = 0; $b < 30; $b++)
                    <span class="es-stage-bulb" style="--bulb-delay: {{ round($b * -0.08, 2) }}s;"></span>
                @endfor
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-stage-stamp mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-stage-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">One address</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The list is yours. <span class="es-stage-lit">Not a platform's.</span>
                    </h2>
                    <p class="es-stage-onmuted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        People follow the schedule, you can see their name and email, and you write to them yourself. No algorithm sits between the announcement and the person who wanted it.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-onink text-lg font-bold">Follow at the merch table</h3>
                            <span class="es-stage-plan">Free</span>
                        </div>
                        <p class="es-stage-onmuted text-sm">Every schedule has a downloadable QR code that points at your public page. Print it, tape it to the merch box, and the room signs itself up.</p>
                    </div>
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-onink text-lg font-bold">Write to the right list</h3>
                            <span class="es-stage-plan">Free</span>
                        </div>
                        <p class="es-stage-onmuted text-sm">Send to everyone, or to a segment: ticket buyers, the waitlist, one sub-schedule, a list you picked by hand. Open and click rates come back after.</p>
                    </div>
                    <div class="es-stage-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-stage-onink text-lg font-bold">Nothing sends itself</h3>
                            <span class="es-stage-plan">Free</span>
                        </div>
                        <p class="es-stage-onmuted text-sm">Worth knowing before you plan around it: followers are not auto-notified when you add a date. You write the email and you press send. Ticket buyers are the exception, and they are told when a date they bought into changes or is cancelled.</p>
                    </div>
                </div>

                <p class="es-stage-onmuted mt-10 text-center" data-reveal>
                    Ten emails a month free, a hundred on Pro, a thousand on Enterprise, counted per recipient.
                    <a href="{{ marketing_url('/features/newsletters') }}" class="es-stage-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        How newsletters work
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>

            <div class="es-stage-bulbrow relative z-10 mt-10" aria-hidden="true">
                @for ($b = 0; $b < 30; $b++)
                    <span class="es-stage-bulb" style="--bulb-delay: {{ round($b * -0.08, 2) }}s;"></span>
                @endfor
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Everything else a live concert needs: bento               -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-stage-stamp mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-stage-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-stage-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything else a live concert needs.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-stage-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-stage-ink text-xl font-bold">On the site you already have</h3>
                                <span class="es-stage-plan">Free</span>
                            </div>
                            <p class="es-stage-muted mb-4">Embed the calendar in your own site so the routing lives where people look you up, and sync two ways with Google, Outlook and CalDAV so the dates land in the calendar the crew actually reads.</p>
                            <p class="es-stage-muted text-sm">Any single date downloads as an .ics file, and a residency's individual dates do too, which is what a promoter forwards to a room's production manager. A residency syncs across as one entry, though: the subscribe feed is what unrolls every night of it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-stage-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-stage-ink text-xl font-bold">Promoters asking for a date</h3>
                                <span class="es-stage-plan">Free</span>
                            </div>
                            <p class="es-stage-muted">Turn requests on and whoever wants to book you fills in the night, the room and their own contact details. It lands on your requests tab and you get the email, rather than digging it out of six inboxes.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-stage-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-stage-ink text-xl font-bold">Announce when the deal is signed</h3>
                                <span class="es-stage-plan">Free</span>
                            </div>
                            <p class="es-stage-muted">A date you have not announced sits as a draft: yours to see, never public until you say so. Sub-schedules keep the club tour and the festival dates on separate strands of the same address.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-stage-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-stage-ink text-xl font-bold">Which city is actually buying</h3>
                                <span class="es-stage-plan">Free</span>
                            </div>
                            <p class="es-stage-muted mb-4">Built-in analytics count views per date and per device, sales and revenue per date, the countries the views came from, the referring domains, and the campaign tags on the links you posted. That is enough to route next autumn on evidence instead of memory.</p>
                            <p class="es-stage-muted text-sm">What it is not: there are no live viewer counts and no follower numbers imported from anywhere else. It measures your own pages.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-stage-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-stage-ink text-xl font-bold">The announce graphic</h3>
                                <span class="es-stage-plan es-stage-plan-pro">Pro</span>
                            </div>
                            <p class="es-stage-muted">Generate one share image of the dates coming up, in a story, square, portrait or landscape crop. It is built from the flyers already on those events, up to twenty of them, so a date with no flyer of its own sits this one out.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6: the streamed date. The chips carry real brand colours. -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-stage-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-4 flex flex-wrap items-center gap-2">
                                    <h3 class="es-stage-ink text-xl font-bold">The night you also stream it</h3>
                                    <span class="es-stage-plan">Free</span>
                                </div>
                                <p class="es-stage-muted mb-4">Mark the date as an online event and paste the link to wherever the stream lives. Sell a livestream ticket type next to the standing ticket, and the link travels on the buyer's own ticket page.</p>
                                <p class="es-stage-muted text-sm">
                                    One link field, no accounts connected, nothing to break when you switch platforms.
                                    <a href="{{ marketing_url('/features/online-events') }}" class="es-stage-link font-semibold hover:underline">How online events work</a>
                                </p>
                            </div>
                            <div class="w-full shrink-0 lg:w-48" aria-hidden="true">
                                <div class="es-stage-well rounded-xl p-4">
                                    <p class="es-stage-tag mb-3">Stream link</p>
                                    <div class="space-y-2 text-center">
                                        <div class="es-ai-field rounded-lg bg-red-400/20 px-2 py-1.5 es-stage-xs font-semibold text-red-700 dark:text-red-300" style="--i: 0;">YouTube Live</div>
                                        <div class="es-ai-field rounded-lg bg-purple-400/20 px-2 py-1.5 es-stage-xs font-semibold text-purple-700 dark:text-purple-300" style="--i: 1;">Twitch</div>
                                        <div class="es-ai-field rounded-lg bg-pink-400/20 px-2 py-1.5 es-stage-xs font-semibold text-pink-700 dark:text-pink-300" style="--i: 2;">Instagram Live</div>
                                    </div>
                                    <p class="es-stage-muted es-stage-2xs mt-3">Whichever you use, it is the same one field.</p>
                                </div>
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
    <!-- 7. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-stage-edge-soft border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-stage-stamp mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <h2 class="es-balance es-stage-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Perfect for every <span class="es-stage-accent">genre and stage</span>
                </h2>
                <p class="es-stage-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Whether it is an intimate acoustic set or a festival stream, Event Schedule works for you. Also see <a href="{{ marketing_url('/for-musicians') }}" class="es-stage-link font-semibold hover:underline">Event Schedule for Musicians</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Solo Acoustic Artists"
                    description="Intimate living room sessions and acoustic sets streamed to fans everywhere."
                    icon-color="rose"
                    blog-slug="for-solo-acoustic-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Rock & Pop Bands"
                    description="High-energy performances streamed from venues and studios to fans worldwide."
                    icon-color="red"
                    blog-slug="for-rock-pop-bands-live"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Jazz & Blues Acts"
                    description="Club sessions and late-night sets for a worldwide audience."
                    icon-color="amber"
                    blog-slug="for-jazz-blues-acts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="DJs & Electronic Artists"
                    description="Live DJ sets, producer sessions, and festival streams for dance music fans."
                    icon-color="orange"
                    blog-slug="for-djs-electronic-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Classical & Orchestra"
                    description="Concert hall performances and recitals for remote audiences worldwide."
                    icon-color="rose"
                    blog-slug="for-classical-orchestra"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Cover & Tribute Bands"
                    description="Fan-favorite shows streamed from bars and venues to audiences everywhere."
                    icon-color="amber"
                    blog-slug="for-cover-tribute-bands-live"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-stage-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps to the first date
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Put the routing up', 'Add each date with its room and its door time. A weekly residency is one recurring event with a day-of-week pattern and date exceptions for the weeks you are out.'],
                    ['02', 'Set the running order', 'Doors, opener, changeover, headline. Named parts with start and end times, published on the event page in the order the night runs.'],
                    ['03', 'Open the sale', 'Connect Stripe, name your ticket types, give each one a price, a quantity and a window. Zero platform fees on what sells.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-stage-card p-7" data-reveal="panel">
                        <div class="es-stage-accent es-stage-num mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-stage-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-stage-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-stage-edge-soft border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-stage-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Named ticket types, QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Residencies with a day-of-week pattern and date exceptions" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow you, with open and click rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way sync with Google, Outlook and CalDAV" :url="marketing_url('/features/calendar-sync')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-stage-link inline-flex items-center font-medium hover:underline">
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
    <!-- 10. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-stage-edge-soft border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-stage-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-music-venues', 'Music Venues'], ['/for-djs', 'DJs'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-stage-hover es-stage-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-stage-hover-title es-stage-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-stage-hover-arrow es-stage-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-stage-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-stage-stamp mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-stage-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-stage-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What promoters and tour managers ask before they move a routing across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-stage-hover es-stage-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-stage-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-stage-accent es-stage-num flex-none font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-stage-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-stage-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-stage-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale: the sign, lit                                    -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-stage-frontage noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-14 text-center shadow-2xl sm:px-12 lg:py-20" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 18%, rgba(159, 18, 57, 0.32), rgba(159, 18, 57, 0) 60%); opacity: 0.6;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="es-stage-bulbrow relative z-10 mb-10" aria-hidden="true">
                    @for ($b = 0; $b < 26; $b++)
                        <span class="es-stage-bulb" style="--bulb-delay: {{ round($b * -0.09, 2) }}s;"></span>
                    @endfor
                </div>

                <div class="relative z-10">
                    <p class="es-stage-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Put the routing up. <span class="es-stage-lit">Keep the door.</span>
                    </h2>
                    <p class="es-stage-onmuted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                        Publishing the whole run is free forever, and so are your first 25 ticket sales a month and scanning them at the door. Five dollars a month takes the ceiling off and adds passes and the check-in dashboard, and nothing is taken from the sale.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-band" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="es-stage-onmuted shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-stage-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-stage-onmuted mt-6 text-sm">No credit card required</p>
                </div>

                <div class="es-stage-bulbrow relative z-10 mt-10" aria-hidden="true">
                    @for ($b = 0; $b < 26; $b++)
                        <span class="es-stage-bulb" style="--bulb-delay: {{ round($b * -0.09, 2) }}s;"></span>
                    @endfor
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 es-stage-tip whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
