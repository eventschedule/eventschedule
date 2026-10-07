<x-marketing-layout>
    <x-slot name="title">Free Farmers Market Calendar | Vendor Applications & Fees</x-slot>
    <x-slot name="description">Put a whole farmers market season online as one recurring market day, let traders apply for a pitch, and take pitch fees with no platform fee. Free.</x-slot>
    <x-slot name="breadcrumbTitle">For Farmers Markets</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Amatic SC') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Bree Serif') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Cabin') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Farmers Markets"
        description="Set the whole season out as one recurring market day with a closing date, take a washed-out Saturday back off the calendar, and let traders put themselves forward for a pitch."
        audience="Farmers Markets & Outdoor Markets"
        keywords="farmers market calendar, market vendor schedule, farmers market events, outdoor market management, free farmers market scheduling" />
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
           For-farmers-markets "In Season" styles.

           The page is the market's own: kraft and crate wood, signs
           lettered by hand, tags on string. Two things only a market has
           carry it. The SEASON WHEEL in the hero turns the year so the
           season arcs over the top, with a notch for every market day;
           the PLAN OF THE SQUARE draws this Saturday's pitch list as
           pitches, in the two states the product really has.

           Two families of colour. The ground and its ink flip with the
           colour mode (cream by day, the evening market by night). Paper,
           kraft and wood are objects: they keep their own colours in both
           modes and re-scope the ink tokens for whatever sits on them, so
           nothing printed on a tag ever turns pale.
           ============================================================== */

        @property --fm-peg {
            syntax: '<angle>';
            inherits: true;
            initial-value: 0deg;
        }
        @property --fm-sweep {
            syntax: '<angle>';
            inherits: true;
            initial-value: 360deg;
        }

        #fm {
            --fm-ground: #fbf6ea;
            --fm-ground-2: #efe3ca;
            --fm-ink: #3b2c22;
            --fm-ink-2: #5a473a;
            --fm-line: rgba(59, 44, 34, 0.24);
            --fm-leaf: #2f6b2e;
            --fm-tomato: #ad2c1a;
            --fm-wave: #ee8a2f;
            --fm-paper: #fffaf0;
            --fm-kraft: #dcc9a3;
            --fm-kraft-2: #cbb489;
            --fm-tray: #c9bb9c;
            --fm-display: 'Amatic SC', 'Arial Narrow', 'Trebuchet MS', sans-serif;
            --fm-sub: 'Bree Serif', Georgia, 'Times New Roman', serif;
            --fm-text: 'Cabin', 'Trebuchet MS', 'Helvetica Neue', Arial, sans-serif;
            --fm-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.23 0 0 0 0 0.17 0 0 0 0 0.13 0 0 0 0.5 0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
            position: relative;
            background: var(--fm-ground);
            color: var(--fm-ink);
            font-family: var(--fm-text);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #fm {
            --fm-ground: #1b1612;
            --fm-ground-2: #241c16;
            --fm-ink: #f3ead8;
            --fm-ink-2: #cfc1aa;
            --fm-line: rgba(243, 234, 216, 0.22);
            --fm-leaf: #8cc878;
            --fm-tomato: #f47d68;
            --fm-wave: #f5a45a;
            --fm-paper: #eadfc8;
            --fm-kraft: #cdb98f;
            --fm-kraft-2: #bba678;
            --fm-tray: #b9ab8c;
        }

        /* Objects. Paper and kraft carry soil ink in both modes; wood carries paint. */
        #fm .fm-paper,
        #fm .fm-kraftp {
            --fm-ink: #3b2c22;
            --fm-ink-2: #4f3d30;
            --fm-line: rgba(59, 44, 34, 0.24);
            --fm-leaf: #1f4d24;
            --fm-tomato: #8f2012;
            color: #3b2c22;
        }
        #fm .fm-paper { background-color: var(--fm-paper); }
        #fm .fm-kraftp { background-color: var(--fm-kraft); }
        #fm .fm-wood {
            --fm-ink: #fbf6ea;
            --fm-ink-2: #e6d7ba;
            --fm-line: rgba(251, 246, 234, 0.24);
            --fm-leaf: #f5d76e;
            --fm-tomato: #f5b073;
            --fm-wave: #ee8a2f;
            color: #fbf6ea;
            background-color: #5a3d25;
            background-image:
                radial-gradient(circle at 1.1rem 3.6rem, #2a1b0f 0 0.2rem, transparent 0.24rem),
                radial-gradient(circle at calc(100% - 1.1rem) 3.6rem, #2a1b0f 0 0.2rem, transparent 0.24rem),
                repeating-linear-gradient(180deg, rgba(20, 12, 6, 0.55) 0 3px, transparent 3px 7.2rem),
                repeating-linear-gradient(91deg, rgba(255, 255, 255, 0.03) 0 1px, transparent 1px 7px),
                linear-gradient(180deg, #684629, #553920);
            background-size: 100% 7.2rem, 100% 7.2rem, auto, auto, auto;
        }

        /* The bar above takes the cream, so the page reads as one sheet. */
        body > header.sticky {
            background-color: rgba(251, 246, 234, 0.88);
            border-bottom-color: rgba(59, 44, 34, 0.18);
        }
        .dark body > header.sticky {
            background-color: rgba(27, 22, 18, 0.88);
            border-bottom-color: rgba(243, 234, 216, 0.16);
        }

        #fm ::selection { background: #f5d76e; color: #3b2c22; }
        #fm a:focus-visible,
        #fm summary:focus-visible,
        #fm input:focus-visible {
            outline: 3px solid var(--fm-wave);
            outline-offset: 3px;
        }

        .fm-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
        .fm-sec { position: relative; padding-block: clamp(4rem, 8vw, 7rem); }
        .fm-band { background-color: var(--fm-ground-2); }
        .fm-band::after { content: ""; position: absolute; inset: 0; background-image: var(--fm-grain); mix-blend-mode: multiply; opacity: 0.28; pointer-events: none; }
        .dark .fm-band::after { display: none; }
        /* The evening market: a lantern's worth of light at the head of each stall. */
        .dark #fm .fm-sec::before,
        .dark #fm .fm-hero::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 24rem;
            background: radial-gradient(ellipse 46% 100% at 50% 0%, rgba(245, 190, 110, 0.15), rgba(245, 190, 110, 0) 72%);
            pointer-events: none;
        }
        #fm .fm-sec > .fm-wrap,
        #fm .fm-hero > .fm-wrap { position: relative; }

        /* Lettering: the hand-drawn sign face, the slab for sub-heads, a plain face to read. */
        .fm-d {
            font-family: var(--fm-display);
            font-weight: 700;
            line-height: 0.95;
            letter-spacing: 0.02em;
            -webkit-text-stroke: 0.022em currentColor;
        }
        .fm-h2 { font-size: clamp(3.1rem, 6.8vw, 5.8rem); text-wrap: balance; }
        .fm-em {
            color: var(--fm-leaf);
            text-decoration: underline wavy var(--fm-wave);
            text-decoration-thickness: clamp(2px, 0.03em, 4px);
            text-underline-offset: 0.13em;
        }
        .fm-s { font-family: var(--fm-sub); font-weight: 400; line-height: 1.2; }
        .fm-lede { max-width: 40rem; font-size: 1.2rem; color: var(--fm-ink-2); }
        .fm-small { font-size: 0.95rem; color: var(--fm-ink-2); }
        .fm-link {
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--fm-wave);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }
        .fm-link:hover { color: var(--fm-leaf); }
        .fm-head { display: grid; gap: 1.1rem; justify-items: start; margin-bottom: clamp(2.25rem, 5vw, 3.75rem); }
        .fm-head.is-mid { justify-items: center; text-align: center; }
        .fm-head.is-mid .fm-lede { margin-inline: auto; }

        /* Swing tags. The hole is really punched: a mask, so whatever is behind shows through. */
        .fm-tag {
            display: inline-block;
            padding: 0.42rem 0.9rem 0.36rem 1.75rem;
            background: var(--fm-kraft);
            color: #3b2c22;
            font-family: var(--fm-text);
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.2;
            clip-path: polygon(0.75rem 0, 100% 0, 100% 100%, 0.75rem 100%, 0 50%);
            -webkit-mask: radial-gradient(circle at 1rem 50%, transparent 0.19rem, #000 0.22rem);
            mask: radial-gradient(circle at 1rem 50%, transparent 0.19rem, #000 0.22rem);
            rotate: var(--r, -2deg);
        }
        .fm-tier { --r: 0deg; padding-block: 0.3rem 0.24rem; font-size: 0.68rem; vertical-align: middle; }
        .fm-tier-free { background: #2f6330; color: #fbf6ea; }
        .fm-tier-pro { background: #a82a18; color: #fbf6ea; }

        /* Painted sign buttons, two nail heads each. */
        .fm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.9rem 1.85rem 0.82rem;
            border: 2px solid #1f4d24;
            border-radius: 0.45rem;
            background-color: #2f6330;
            background-image:
                radial-gradient(circle at 0.8rem 50%, #17391b 0 0.15rem, transparent 0.18rem),
                radial-gradient(circle at calc(100% - 0.8rem) 50%, #17391b 0 0.15rem, transparent 0.18rem);
            color: #fbf6ea;
            font-family: var(--fm-sub);
            font-size: 1.15rem;
            line-height: 1.15;
            box-shadow: inset 0 0 0 2px rgba(251, 246, 234, 0.16), 0 0.7rem 1.1rem -0.6rem rgba(31, 77, 36, 0.8);
            transition: translate 0.18s ease, box-shadow 0.18s ease;
        }
        .fm-btn:hover { translate: 0 -2px; box-shadow: inset 0 0 0 2px rgba(251, 246, 234, 0.16), 0 1.1rem 1.4rem -0.7rem rgba(31, 77, 36, 0.85); }
        .fm-btn svg { width: 1.15rem; height: 1.15rem; flex: none; transition: translate 0.18s ease; }
        .fm-btn:hover svg { translate: 3px 0; }
        .fm-btn-ghost {
            padding-inline: 0.4rem;
            border-color: transparent;
            border-bottom: 2px dashed var(--fm-ink);
            border-radius: 0;
            background: none;
            color: var(--fm-ink);
            font-size: 1.1rem;
            box-shadow: none;
        }
        .fm-btn-ghost:hover { box-shadow: none; translate: none; color: var(--fm-leaf); }
        .fm-btn-ghost:hover svg { translate: 0 3px; }

        /* The canopy: a striped awning seen head on, its valance pinked. */
        .fm-stall { filter: drop-shadow(0 0.55rem 0.5rem rgba(59, 44, 34, 0.22)); }
        /* Restated under the page id: the shared reveal ends on `filter: none`, which outranks a plain
           class and took the shadow away as soon as the element was revealed. */
        #fm .fm-stall { filter: drop-shadow(0 0.55rem 0.5rem rgba(59, 44, 34, 0.22)); }
        #fm .fm-hang { filter: drop-shadow(0 0.9rem 0.8rem rgba(59, 44, 34, 0.32)); }
        .fm-awn {
            --fm-pk: 0.6rem;
            --a: #3f7d3a;
            --sw: 2.75rem;
            height: 3.4rem;
            border-top: 0.4rem solid #3b2c22;
            background:
                linear-gradient(to bottom, rgba(0, 0, 0, 0.22), rgba(0, 0, 0, 0) 30%, rgba(0, 0, 0, 0) 66%, rgba(0, 0, 0, 0.14)),
                repeating-linear-gradient(90deg, var(--a) 0 var(--sw), #fbf6ea var(--sw) calc(var(--sw) * 2));
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - var(--fm-pk)), 98.75% 100%, 97.5% calc(100% - var(--fm-pk)), 96.25% 100%, 95% calc(100% - var(--fm-pk)), 93.75% 100%, 92.5% calc(100% - var(--fm-pk)), 91.25% 100%, 90% calc(100% - var(--fm-pk)), 88.75% 100%, 87.5% calc(100% - var(--fm-pk)), 86.25% 100%, 85% calc(100% - var(--fm-pk)), 83.75% 100%, 82.5% calc(100% - var(--fm-pk)), 81.25% 100%, 80% calc(100% - var(--fm-pk)), 78.75% 100%, 77.5% calc(100% - var(--fm-pk)), 76.25% 100%, 75% calc(100% - var(--fm-pk)), 73.75% 100%, 72.5% calc(100% - var(--fm-pk)), 71.25% 100%, 70% calc(100% - var(--fm-pk)), 68.75% 100%, 67.5% calc(100% - var(--fm-pk)), 66.25% 100%, 65% calc(100% - var(--fm-pk)), 63.75% 100%, 62.5% calc(100% - var(--fm-pk)), 61.25% 100%, 60% calc(100% - var(--fm-pk)), 58.75% 100%, 57.5% calc(100% - var(--fm-pk)), 56.25% 100%, 55% calc(100% - var(--fm-pk)), 53.75% 100%, 52.5% calc(100% - var(--fm-pk)), 51.25% 100%, 50% calc(100% - var(--fm-pk)), 48.75% 100%, 47.5% calc(100% - var(--fm-pk)), 46.25% 100%, 45% calc(100% - var(--fm-pk)), 43.75% 100%, 42.5% calc(100% - var(--fm-pk)), 41.25% 100%, 40% calc(100% - var(--fm-pk)), 38.75% 100%, 37.5% calc(100% - var(--fm-pk)), 36.25% 100%, 35% calc(100% - var(--fm-pk)), 33.75% 100%, 32.5% calc(100% - var(--fm-pk)), 31.25% 100%, 30% calc(100% - var(--fm-pk)), 28.75% 100%, 27.5% calc(100% - var(--fm-pk)), 26.25% 100%, 25% calc(100% - var(--fm-pk)), 23.75% 100%, 22.5% calc(100% - var(--fm-pk)), 21.25% 100%, 20% calc(100% - var(--fm-pk)), 18.75% 100%, 17.5% calc(100% - var(--fm-pk)), 16.25% 100%, 15% calc(100% - var(--fm-pk)), 13.75% 100%, 12.5% calc(100% - var(--fm-pk)), 11.25% 100%, 10% calc(100% - var(--fm-pk)), 8.75% 100%, 7.5% calc(100% - var(--fm-pk)), 6.25% 100%, 5% calc(100% - var(--fm-pk)), 3.75% 100%, 2.5% calc(100% - var(--fm-pk)), 1.25% 100%, 0 calc(100% - var(--fm-pk)));
        }
        .fm-awn-s { --fm-pk: 0.42rem; --sw: 1.5rem; height: 2.2rem; border-top-width: 0.28rem; clip-path: polygon(0 0, 100% 0, 100% calc(100% - var(--fm-pk)), 96.429% 100%, 92.857% calc(100% - var(--fm-pk)), 89.286% 100%, 85.714% calc(100% - var(--fm-pk)), 82.143% 100%, 78.571% calc(100% - var(--fm-pk)), 75% 100%, 71.429% calc(100% - var(--fm-pk)), 67.857% 100%, 64.286% calc(100% - var(--fm-pk)), 60.714% 100%, 57.143% calc(100% - var(--fm-pk)), 53.571% 100%, 50% calc(100% - var(--fm-pk)), 46.429% 100%, 42.857% calc(100% - var(--fm-pk)), 39.286% 100%, 35.714% calc(100% - var(--fm-pk)), 32.143% 100%, 28.571% calc(100% - var(--fm-pk)), 25% 100%, 21.429% calc(100% - var(--fm-pk)), 17.857% 100%, 14.286% calc(100% - var(--fm-pk)), 10.714% 100%, 7.143% calc(100% - var(--fm-pk)), 3.571% 100%, 0 calc(100% - var(--fm-pk))); }

        /* ---------------------------------------------------------------
           Hero: the season wheel
           --------------------------------------------------------------- */
        .fm-hero { position: relative; overflow: clip; padding-top: clamp(2.5rem, 5vw, 4.25rem); }
        .fm-hero-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 1024px) {
            .fm-hero-grid { grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr); gap: 3.25rem; min-height: calc(88svh - 4rem - 10.5rem); }
        }
        .fm-copy { container-type: inline-size; min-width: 0; }
        .fm-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
            font-family: var(--fm-text);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.35;
            color: var(--fm-leaf);
            -webkit-text-stroke: 0;
        }
        .fm-eyebrow::before { content: ""; flex: none; width: 0.85rem; height: 0.85rem; border-radius: 0 75% 0 75%; background: #3f7d3a; }
        .fm-h1 { font-family: var(--fm-display); font-weight: 700; font-size: clamp(3.7rem, 19.8cqi, 9.2rem); line-height: 0.86; letter-spacing: 0.02em; -webkit-text-stroke: 0.022em currentColor; }
        .fm-h1 .es-mask-line { white-space: nowrap; }
        #fm .fm-h1 .es-mask { padding-bottom: 0.24em; margin-bottom: -0.24em; }
        html.es-anim #fm .fm-mask-3 .es-mask-line { animation-delay: 0.41s; }
        .fm-hero .fm-lede { margin-top: 1.6rem; }
        .fm-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem 1.4rem; margin-top: 2rem; }

        .fm-wheelcol { min-width: 0; }
        .fm-wheel {
            position: relative;
            width: min(100% - 1.2rem, 33rem);
            aspect-ratio: 1;
            margin-inline: auto;
            container-type: inline-size;
            border-radius: 50%;
            background: var(--fm-paper);
            box-shadow:
                0 0 0 2px #3b2c22,
                0 0 0 0.6rem var(--fm-kraft),
                0 0 0 calc(0.6rem + 2px) #3b2c22,
                0 2rem 3rem -1.4rem rgba(59, 44, 34, 0.6);
        }
        .dark .fm-wheel { box-shadow: 0 0 0 2px #3b2c22, 0 0 0 0.6rem var(--fm-kraft), 0 0 0 calc(0.6rem + 2px) #120e0b, 0 0 5rem 0.5rem rgba(245, 190, 110, 0.14); }
        .fm-wheel > * { position: absolute; }
        /* The twelve months, a band of the year's colours with a dark line at each month's first day. */
        .fm-wheel-months {
            inset: 8%;
            border-radius: 50%;
            -webkit-mask: radial-gradient(closest-side, transparent 73.6%, #000 74.2%);
            mask: radial-gradient(closest-side, transparent 73.6%, #000 74.2%);
        }
        .fm-wheel-months::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background-image: var(--fm-grain); mix-blend-mode: multiply; opacity: 0.5; }
        .fm-wheel-names { inset: 0; width: 100%; height: 100%; }
        .fm-wheel-names text { font-family: var(--fm-text); font-weight: 700; font-size: 5.4px; letter-spacing: 0.2em; text-transform: uppercase; fill: #3b2c22; }
        /* The season itself: one arc, because it is one event. */
        .fm-wheel-arc {
            inset: 20.5%;
            border-radius: 50%;
            background: conic-gradient(from var(--fm-open), rgba(63, 125, 58, 0.3) 0 min(var(--fm-sweep, 360deg), var(--fm-span)), transparent 0);
            -webkit-mask: radial-gradient(closest-side, transparent 83%, #000 84%);
            mask: radial-gradient(closest-side, transparent 83%, #000 84%);
        }
        .fm-wheel-ticks { inset: 0; }
        .fm-tick {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 1.5cqi;
            height: 4.5cqi;
            margin: -2.25cqi 0 0 -0.75cqi;
            border-radius: 0.5cqi;
            background: #2f6330;
            transform: rotate(var(--a)) translateY(-27.2cqi);
        }
        .fm-tick.is-off { background: transparent; border: 0.32cqi dashed #a82a18; }
        .fm-tick.is-winter { background: #3b2c22; }
        /* The peg rides the rim, and travels the season as the page begins to scroll. */
        .fm-wheel-peg {
            left: 50%;
            top: 50%;
            width: 5.2cqi;
            height: 6.4cqi;
            margin: -3.2cqi 0 0 -2.6cqi;
            background: #d9412b;
            clip-path: polygon(0 0, 100% 0, 50% 100%);
            transform: rotate(var(--fm-peg)) translateY(-51.5cqi);
            filter: drop-shadow(0 0.15rem 0.15rem rgba(59, 44, 34, 0.5));
        }
        .fm-wheel-hub {
            inset: 27%;
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 0.9cqi;
            text-align: center;
            color: #3b2c22;
        }
        .fm-wheel-hub h2 { font-family: var(--fm-sub); font-weight: 400; font-size: clamp(0.95rem, 5cqi, 1.75rem); line-height: 1.1; }
        .fm-wheel-run { font-family: var(--fm-display); font-weight: 700; font-size: clamp(1.7rem, 9.6cqi, 3.4rem); line-height: 0.95; color: #1f4d24; white-space: nowrap; }
        .fm-wheel-facts { max-width: 39cqi; font-size: clamp(0.72rem, 2.75cqi, 0.95rem); line-height: 1.35; color: #4f3d30; }
        .fm-legend { display: grid; gap: 0.75rem 2rem; margin: 2.1rem auto 0; max-width: 33rem; }
        @media (min-width: 640px) { .fm-legend { grid-template-columns: 1fr 1fr; } }
        .fm-legend p { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.7rem; align-items: start; font-size: 0.9rem; line-height: 1.45; color: var(--fm-ink-2); }
        .fm-legend i { width: 0.5rem; height: 1.35rem; margin-top: 0.1rem; border-radius: 0.15rem; border: 0.12rem dashed var(--fm-tomato); }
        .fm-legend i.is-winter { border: 0; background: var(--fm-ink); }
        @supports (animation-timeline: scroll()) {
            html.es-anim #fm .fm-wheel {
                animation: fm-travel linear both;
                animation-timeline: scroll(root block);
                animation-range: 0px 640px;
            }
        }
        @keyframes fm-travel { from { --fm-peg: var(--fm-open); } to { --fm-peg: var(--fm-close); } }
        html.es-anim #fm .fm-wheel-arc { animation: fm-draw 1.7s cubic-bezier(0.3, 0.7, 0.2, 1) 0.35s backwards; }
        @keyframes fm-draw { from { --fm-sweep: 0deg; } to { --fm-sweep: 360deg; } }
        html.es-anim #fm .fm-tick { animation: fm-notch 0.4s ease-out backwards; animation-delay: calc(0.45s + var(--i) * 45ms); }
        @keyframes fm-notch { from { opacity: 0; } to { opacity: 1; } }

        /* Under the hero: a line of twine, and every kind of stall tagged on it. */
        .fm-twine { position: relative; margin-top: clamp(2.75rem, 5vw, 4rem); padding-block: 0.4rem 1.6rem; }
        .fm-twine::before { content: ""; position: absolute; inset: 1.38rem 0 auto 0; height: 2px; background: var(--fm-ink); opacity: 0.8; }
        .fm-twine .es-marquee-track { gap: 1.4rem; padding-right: 1.4rem; align-items: flex-start; padding-top: 0.42rem; }
        .fm-chip {
            position: relative;
            flex: none;
            padding: 1.3rem 0.9rem 0.5rem;
            background: var(--fm-kraft);
            color: #3b2c22;
            font-family: var(--fm-sub);
            font-size: 1.02rem;
            line-height: 1.1;
            white-space: nowrap;
            clip-path: polygon(0.6rem 0, calc(100% - 0.6rem) 0, 100% 0.6rem, 100% 100%, 0 100%, 0 0.6rem);
            -webkit-mask: radial-gradient(circle at 50% 0.62rem, transparent 0.25rem, #000 0.28rem);
            mask: radial-gradient(circle at 50% 0.62rem, transparent 0.25rem, #000 0.28rem);
            rotate: var(--r, 0deg);
            transform-origin: 50% 0;
        }
        .fm-chip:nth-child(3n) { --r: 2.5deg; }
        .fm-chip:nth-child(3n + 1) { --r: -2deg; }
        .fm-chip:nth-child(4n) { background: var(--fm-paper); }
        @media (prefers-reduced-motion: reduce) {
            .fm-twine::before { display: none; }
            .fm-twine .es-marquee-track { row-gap: 0.8rem; }
        }

        /* ---------------------------------------------------------------
           Set it once: the season, a stall for every market day
           --------------------------------------------------------------- */
        .fm-run { display: flex; flex-wrap: wrap; gap: 1.1rem 1rem; justify-content: center; padding: 1.9rem 1rem 1.5rem; }
        .fm-run-month { display: grid; gap: 0.45rem; justify-items: center; }
        .fm-run-month.is-winter { flex-basis: 100%; padding-top: 0.9rem; border-top: 1px dashed rgba(59, 44, 34, 0.4); }
        .fm-run-days { display: flex; gap: 0.22rem; }
        .fm-run-month p { font-weight: 700; font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; color: #4f3d30; }
        .fm-day {
            display: grid;
            grid-template-rows: 0.62em minmax(0, 1fr);
            width: 1.72em;
            height: 2.45em;
            font-size: clamp(0.95rem, 1.5vw, 1.32rem);
            background: #fffaf0;
            border: 1.5px solid #3b2c22;
            border-radius: 0.12em;
            overflow: hidden;
        }
        .fm-day::before { content: ""; background: repeating-linear-gradient(90deg, #3f7d3a 0 25%, #fbf6ea 25% 50%); border-bottom: 1.5px solid #3b2c22; }
        .fm-day b { display: grid; place-items: center; font-weight: 700; font-size: 0.62em; line-height: 1; color: #3b2c22; }
        .fm-day.is-off { background: transparent; border-style: dashed; border-color: #a82a18; }
        .fm-day.is-off::before { background: none; border-bottom-color: transparent; }
        .fm-day.is-off b { color: #8f2012; }
        .fm-day.is-winter::before { background: repeating-linear-gradient(90deg, #3b2c22 0 25%, #fbf6ea 25% 50%); }
        html.es-anim #fm [data-reveal]:not(.is-revealed) .fm-day { opacity: 0; translate: 0 0.5rem; }
        .fm-day { transition: opacity 0.45s ease, translate 0.45s ease; transition-delay: calc(var(--i, 0) * 28ms + 0.15s); }

        .fm-line3 { position: relative; display: grid; gap: 2.2rem 1.5rem; margin-top: 3.25rem; padding-top: 1.4rem; }
        @media (min-width: 860px) { .fm-line3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fm-line3::before { content: ""; position: absolute; inset: 0.75rem -0.5rem auto; height: 2px; background: var(--fm-ink); opacity: 0.8; }
        .fm-pegged { position: relative; padding: 1.9rem 1.4rem 1.4rem; rotate: var(--r, 0deg); transform-origin: 50% 0; box-shadow: 0 1rem 1.4rem -1rem rgba(59, 44, 34, 0.55); }
        .fm-pegged::before {
            content: "";
            position: absolute;
            left: calc(50% - 0.32rem);
            top: -1.15rem;
            width: 0.64rem;
            height: 2.1rem;
            border-radius: 0.14rem;
            background: linear-gradient(90deg, #b98a57, #d9b27c 45%, #b98a57);
            box-shadow: 0 0 0 1px rgba(59, 44, 34, 0.6), inset 0 -0.9rem 0 -0.75rem rgba(59, 44, 34, 0.5);
        }
        .fm-pegged h3 { display: inline; margin-inline-end: 0.5rem; font-size: 1.35rem; }
        .fm-pegged p { margin-top: 0.7rem; font-size: 1rem; color: var(--fm-ink-2); }
        .fm-foot { margin: 2.5rem auto 0; max-width: 42rem; text-align: center; }

        /* ---------------------------------------------------------------
           The rain call
           --------------------------------------------------------------- */
        .fm-duo { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 1000px) { .fm-duo { grid-template-columns: minmax(0, 1fr) minmax(0, 0.9fr); gap: 4.5rem; } }
        .fm-points { display: grid; gap: 1.15rem; margin-top: 2rem; }
        .fm-points li { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.85rem; }
        .fm-points li::before { content: ""; width: 0.95rem; height: 0.95rem; margin-top: 0.38rem; border-radius: 0 75% 0 75%; background: #3f7d3a; }
        .fm-points strong { display: block; font-family: var(--fm-sub); font-weight: 400; font-size: 1.15rem; line-height: 1.25; }
        .fm-points span { color: var(--fm-ink-2); }
        .fm-tierline { margin-top: 1.9rem; }
        .fm-tierline span:last-child { margin-inline-start: 0.5rem; }

        .fm-aug { padding: 1.6rem 1.5rem 1.4rem; border: 2px solid #3b2c22; box-shadow: 0 1.6rem 2.2rem -1.4rem rgba(59, 44, 34, 0.6); rotate: 0.8deg; }
        .fm-aug-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; }
        .fm-aug-top h3 { font-size: 3.1rem; }
        .fm-aug-top span { font-weight: 700; font-size: 0.72rem; letter-spacing: 0.16em; text-transform: uppercase; color: #8f2012; }
        .fm-aug .fm-run-days { justify-content: space-between; gap: 0.5rem; margin-top: 1.1rem; }
        .fm-aug .fm-day { font-size: clamp(1.5rem, 6.4vw, 2.5rem); position: relative; }
        .fm-aug .fm-day.is-off::after {
            content: "";
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(104deg, transparent 0 7px, rgba(59, 44, 34, 0.3) 7px 8px);
            background-size: 200% 200%;
            animation: fm-rain 0.7s linear infinite;
        }
        @keyframes fm-rain { from { background-position: 0 0; } to { background-position: -16px 64px; } }
        .fm-book { margin-top: 1.5rem; border-top: 2px solid #3b2c22; }
        .fm-book div { display: grid; grid-template-columns: 4.2rem minmax(0, 1fr) auto; gap: 0.75rem; align-items: baseline; padding: 0.7rem 0.15rem 0.6rem; border-bottom: 1px solid rgba(59, 44, 34, 0.28); }
        .fm-book div > :first-child { font-weight: 700; font-size: 0.78rem; letter-spacing: 0.12em; text-transform: uppercase; color: #4f3d30; }
        .fm-book div > :nth-child(2) { font-family: var(--fm-sub); font-size: 1.1rem; }
        .fm-book div > :last-child { font-size: 0.875rem; color: #4f3d30; }
        .fm-aug-note { margin-top: 1rem; font-size: 0.875rem; color: #4f3d30; }

        /* ---------------------------------------------------------------
           The pitch list: a plan of the square, and the list it is drawn from
           --------------------------------------------------------------- */
        .fm-plot { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem; align-items: start; }
        @media (min-width: 1040px) { .fm-plot { grid-template-columns: minmax(0, 1.28fr) minmax(0, 1fr); gap: 3rem; } }
        .fm-map { container-type: inline-size; container-name: fmmap; min-width: 0; }
        .fm-square {
            position: relative;
            display: grid;
            grid-template-columns: repeat(8, minmax(0, 1fr));
            grid-template-rows: auto 2.2cqi auto auto auto;
            gap: 1.7cqi;
            padding: 6.5cqi 3.4cqi 10cqi;
            border: 2px solid #3b2c22;
            border-radius: 1.1rem;
            background-color: #f1e7d0;
            background-image:
                linear-gradient(rgba(59, 44, 34, 0.08) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 44, 34, 0.08) 1px, transparent 1px);
            background-size: 4.4cqi 4.4cqi;
            box-shadow: 0 1.8rem 2.4rem -1.6rem rgba(59, 44, 34, 0.6);
            counter-reset: fm-p;
            color: #3b2c22;
        }
        .dark .fm-square { background-color: #e2d6bb; }
        .fm-square-label { position: absolute; font-weight: 700; font-size: clamp(0.52rem, 1.75cqi, 0.74rem); letter-spacing: 0.2em; text-transform: uppercase; color: #4f3d30; white-space: nowrap; }
        .fm-square-steps { top: 2.1cqi; left: 50%; translate: -50% 0; }
        .fm-square-way { bottom: -1px; left: 50%; translate: -50% 50%; padding: 0.2rem 0.7rem 0.12rem; background: #3b2c22; color: #fbf6ea; border-radius: 0.2rem; }
        .fm-cross { grid-column: 3 / span 4; grid-row: 4; display: flex; align-items: center; justify-content: center; gap: 1.4cqi; padding-block: 1cqi; }
        .fm-cross i { width: 5.6cqi; aspect-ratio: 1; border-radius: 50%; background: radial-gradient(circle, #3b2c22 0 22%, transparent 24% 46%, #3b2c22 48% 56%, transparent 58%); }
        .fm-cross b { padding: 0.9cqi 1.3cqi 0.7cqi; border: 1.5px solid #3b2c22; border-radius: 0.8cqi; background: #f5d76e; font-weight: 700; font-size: clamp(0.5rem, 1.7cqi, 0.72rem); letter-spacing: 0.12em; text-transform: uppercase; line-height: 1; }
        .fm-pitch { display: block; aspect-ratio: 1 / 0.96; counter-increment: fm-p; }
        .fm-pitch-in {
            display: grid;
            grid-template-rows: 32% minmax(0, 1fr);
            height: 100%;
            border: 1.5px solid #3b2c22;
            border-radius: 0.9cqi;
            overflow: hidden;
            background: #fffaf0;
            transition: translate 0.22s ease, box-shadow 0.22s ease, opacity 0.22s ease;
        }
        .fm-pitch-in::before { content: ""; background: repeating-linear-gradient(90deg, var(--c, #cbb489) 0 16.67%, #fffaf0 16.67% 33.33%); border-bottom: 1.5px solid #3b2c22; }
        .fm-pitch-in::after { content: counter(fm-p, decimal-leading-zero); display: grid; place-items: center; font-weight: 700; font-size: clamp(0.5rem, 2.25cqi, 0.95rem); line-height: 1; }
        .fm-pitch.is-wait .fm-pitch-in { border-style: dashed; border-color: #a82a18; background: transparent; }
        .fm-pitch.is-wait .fm-pitch-in::before { background: repeating-linear-gradient(135deg, var(--c) 0 3px, transparent 3px 7px); border-bottom-style: dashed; border-bottom-color: #a82a18; }
        .fm-pitch.is-named { cursor: pointer; }
        .fm-pitch.is-named .fm-pitch-in { box-shadow: 0 0.5cqi 0 #3b2c22; }
        .fm-pitch.is-named.is-wait .fm-pitch-in { box-shadow: none; }
        .fm-pitch.is-named:hover .fm-pitch-in,
        .fm-pitch.is-named:focus-visible .fm-pitch-in { translate: 0 -0.7cqi; box-shadow: 0 1.2cqi 0 #3b2c22; }
        #fm .fm-pitch.is-named:focus-visible { outline: none; }
        #fm .fm-pitch.is-named:focus-visible .fm-pitch-in { outline: 3px solid #ee8a2f; outline-offset: 2px; }
        .fm-square:has(.is-named:hover) .fm-pitch:not(:hover) .fm-pitch-in,
        .fm-square:has(.is-named:focus-visible) .fm-pitch:not(:focus-visible) .fm-pitch-in { opacity: 0.5; }
        /* The caption strip. Each named pitch carries its own line as three strings and prints
           them into the foot of the square while it is pointed at; :has() clears the resting line. */
        .fm-pitch.is-named::before,
        .fm-pitch.is-named::after,
        .fm-cap {
            position: absolute;
            bottom: 2.9cqi;
            white-space: nowrap;
            transition: opacity 0.2s ease;
        }
        .fm-pitch.is-named::before,
        .fm-pitch.is-named::after { opacity: 0; pointer-events: none; }
        .fm-pitch.is-named::before { left: 3.6cqi; content: "No. " counter(fm-p, decimal-leading-zero) "\2002" var(--fm-n); font-family: var(--fm-sub); font-size: clamp(0.8rem, 3.2cqi, 1.5rem); line-height: 1.1; }
        .fm-pitch.is-named::after { right: 3.6cqi; content: var(--fm-k) "\2002\00b7\2002" var(--fm-st); bottom: 3.3cqi; font-weight: 700; font-size: clamp(0.52rem, 1.75cqi, 0.76rem); letter-spacing: 0.14em; text-transform: uppercase; color: #4f3d30; }
        .fm-pitch.is-named:hover::before,
        .fm-pitch.is-named:hover::after,
        .fm-pitch.is-named:focus-visible::before,
        .fm-pitch.is-named:focus-visible::after { opacity: 1; }
        .fm-cap { left: 3.6cqi; right: 3.6cqi; bottom: 3.1cqi; font-size: clamp(0.62rem, 2.05cqi, 0.9rem); color: #4f3d30; overflow: hidden; text-overflow: ellipsis; }
        .fm-square:has(.is-named:hover) .fm-cap,
        .fm-square:has(.is-named:focus-visible) .fm-cap { opacity: 0; }
        @media (hover: none) { .fm-cap-hover { display: none; } }
        @media (hover: hover) { .fm-cap-touch { display: none; } }
        html.es-anim #fm .fm-map.is-revealed .fm-pitch-in { animation: fm-pitch 0.5s cubic-bezier(0.3, 1.4, 0.5, 1) backwards; animation-delay: calc(var(--i) * 42ms + 0.2s); }
        @keyframes fm-pitch { from { opacity: 0; scale: 0.5; } to { opacity: 1; scale: 1; } }
        .fm-keys { display: flex; flex-wrap: wrap; gap: 0.6rem 1.6rem; margin-top: 1.25rem; font-size: 0.9rem; color: var(--fm-ink-2); }
        .fm-keys span { display: inline-flex; align-items: center; gap: 0.55rem; }
        .fm-keys i { width: 1.5rem; height: 1.1rem; border: 1.5px solid var(--fm-ink); border-radius: 0.15rem; background: linear-gradient(to bottom, #3f7d3a 0 36%, var(--fm-paper) 36%); }
        .fm-keys i.is-wait { border-style: dashed; border-color: var(--fm-tomato); background: none; }
        @container fmmap (max-width: 30rem) {
            .fm-square { gap: 1.4cqi; padding: 8cqi 2.6cqi 9cqi; border-radius: 0.8rem; }
            .fm-pitch { aspect-ratio: 1; }
            .fm-cap, .fm-pitch.is-named::before, .fm-pitch.is-named::after { display: none; }
            .fm-square { padding-bottom: 6cqi; }
        }

        .fm-list { padding: 1.5rem 1.4rem 1.3rem; border: 2px solid #3b2c22; box-shadow: 0 1.6rem 2.2rem -1.5rem rgba(59, 44, 34, 0.6); }
        .fm-list-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 2px solid #3b2c22; }
        .fm-list-top strong { font-size: 2.6rem; }
        .fm-list-top span { font-weight: 700; font-size: 0.72rem; letter-spacing: 0.16em; text-transform: uppercase; color: #4f3d30; }
        .fm-list table { width: 100%; border-collapse: collapse; }
        .fm-list thead th { padding: 0.7rem 0.25rem 0.5rem; text-align: start; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: #4f3d30; }
        .fm-list thead th:last-child,
        .fm-list tbody td:last-child { text-align: end; }
        .fm-list tbody tr { border-top: 1px solid rgba(59, 44, 34, 0.28); scroll-margin-top: 7rem; transition: background-color 0.3s ease; }
        .fm-list tbody th { padding: 0.72rem 0.25rem 0.62rem; text-align: start; font-family: var(--fm-sub); font-weight: 400; font-size: 1.08rem; line-height: 1.25; }
        .fm-list tbody th small { display: block; font-family: var(--fm-text); font-size: 0.8rem; color: #4f3d30; }
        .fm-list tbody td { padding: 0.72rem 0.25rem 0.62rem; font-size: 0.9rem; color: #4f3d30; }
        .fm-list tbody tr:target { background: rgba(245, 215, 110, 0.55); }
        .fm-status { display: inline-block; padding: 0.28rem 0.6rem 0.22rem; border: 1.5px solid #1f4d24; border-radius: 0.2rem; background: #2f6330; color: #fbf6ea; font-weight: 700; font-size: 0.66rem; letter-spacing: 0.12em; text-transform: uppercase; white-space: nowrap; }
        .fm-status-wait { border: 1.5px dashed #8f2012; background: transparent; color: #8f2012; }
        .fm-list-note { margin-top: 1rem; padding-top: 0.9rem; border-top: 2px solid #3b2c22; font-size: 0.875rem; color: #4f3d30; }
        @media (max-width: 30rem) {
            .fm-list thead th:nth-child(2),
            .fm-list tbody td:nth-child(2) { display: none; }
        }
        @media (min-width: 30.01rem) { .fm-list tbody th small { display: none; } }
        .fm-plot:has(#fm-t1:hover) .fm-pitch[href="#fm-t1"] .fm-pitch-in,
        .fm-plot:has(#fm-t2:hover) .fm-pitch[href="#fm-t2"] .fm-pitch-in,
        .fm-plot:has(#fm-t3:hover) .fm-pitch[href="#fm-t3"] .fm-pitch-in,
        .fm-plot:has(#fm-t4:hover) .fm-pitch[href="#fm-t4"] .fm-pitch-in,
        .fm-plot:has(#fm-t5:hover) .fm-pitch[href="#fm-t5"] .fm-pitch-in,
        .fm-plot:has(#fm-t6:hover) .fm-pitch[href="#fm-t6"] .fm-pitch-in { translate: 0 -0.7cqi; box-shadow: 0 1.2cqi 0 #3b2c22, 0 0 0 0.5cqi #f5d76e; }

        .fm-trio { display: grid; gap: 1.25rem; margin-top: 2.75rem; }
        @media (min-width: 860px) { .fm-trio { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fm-card { padding: 1.4rem 1.35rem 1.3rem; border: 1.5px solid var(--fm-line); border-radius: 0.3rem; box-shadow: 0 1rem 1.4rem -1.1rem rgba(59, 44, 34, 0.5); transition: translate 0.22s ease, box-shadow 0.22s ease; }
        .fm-card:hover { translate: 0 -3px; box-shadow: 0 1.4rem 1.6rem -1.1rem rgba(59, 44, 34, 0.55); }
        .fm-card h3 { display: inline; margin-inline-end: 0.5rem; font-size: 1.22rem; }
        .fm-card p { margin-top: 0.6rem; font-size: 1rem; color: var(--fm-ink-2); }

        /* ---------------------------------------------------------------
           Market morning: the hours, painted on a board (wood in both modes)
           --------------------------------------------------------------- */
        .fm-morning { position: relative; overflow: clip; }
        .fm-morning::after {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 26rem;
            background: radial-gradient(ellipse 60% 100% at 18% 0%, rgba(245, 215, 110, 0.28), rgba(245, 215, 110, 0) 70%);
            pointer-events: none;
        }
        .fm-morning .fm-wrap { z-index: 1; }
        .fm-hours {
            position: relative;
            padding: 1.5rem 1.4rem 1.2rem 3.1rem;
            border: 3px solid #1c2f1f;
            border-radius: 0.4rem;
            background: linear-gradient(180deg, #33603a, #284d2f);
            box-shadow: inset 0 0 0 3px rgba(251, 246, 234, 0.14), 0 1.6rem 2.2rem -1.3rem rgba(0, 0, 0, 0.75);
        }
        .fm-hours::before { content: ""; position: absolute; left: 1.5rem; top: 2.2rem; bottom: 2rem; width: 2px; background: rgba(251, 246, 234, 0.35); }
        .fm-hours-sun {
            position: absolute;
            left: calc(1.5rem + 1px - 0.55rem);
            top: 1.75rem;
            width: 1.1rem;
            height: 1.1rem;
            border-radius: 50%;
            background: #f5d76e;
            box-shadow: 0 0 0 0.25rem rgba(245, 215, 110, 0.25), 0 0 1.4rem 0.3rem rgba(245, 215, 110, 0.55);
        }
        @supports (animation-timeline: view()) {
            html.es-anim #fm .fm-hours { view-timeline: --fm-day block; }
            html.es-anim #fm .fm-hours-sun {
                animation: fm-sun linear both;
                animation-timeline: --fm-day;
                animation-range: entry 60% exit 40%;
            }
        }
        @keyframes fm-sun { from { top: 1.75rem; } to { top: calc(100% - 2.9rem); } }
        .fm-hours li { display: grid; grid-template-columns: 4.6rem minmax(0, 1fr); gap: 0.9rem; align-items: baseline; padding-block: 0.7rem; border-bottom: 1px dashed rgba(251, 246, 234, 0.28); }
        .fm-hours li:last-child { border-bottom: 0; }
        .fm-hours time { font-size: 2.3rem; color: #f5d76e; }
        .fm-hours strong { display: block; font-size: 2.05rem; font-weight: 700; color: #fbf6ea; }
        .fm-hours span { display: block; margin-top: 0.15rem; font-size: 0.95rem; color: #e6d7ba; }
        .fm-hours-note { margin-top: 1.25rem; font-size: 0.95rem; color: #e6d7ba; }
        .fm-nailed { display: grid; gap: 1.5rem; }
        .fm-note { position: relative; padding: 1.5rem 1.35rem 1.25rem; rotate: var(--r, 0deg); box-shadow: 0 1.2rem 1.5rem -1rem rgba(0, 0, 0, 0.8); }
        .fm-note::before { content: ""; position: absolute; left: 50%; top: 0.55rem; width: 0.55rem; height: 0.55rem; margin-left: -0.275rem; border-radius: 50%; background: radial-gradient(circle at 35% 30%, #9b8f84, #2a1b0f 70%); }
        .fm-note h3 { display: inline; margin-inline-end: 0.5rem; font-size: 1.22rem; }
        .fm-note p { margin-top: 0.6rem; font-size: 1rem; color: #4f3d30; }
        #fm .fm-morning a:focus-visible { outline-color: #f5d76e; }

        /* ---------------------------------------------------------------
           Pitch fees: the price tag, and thirty to the tray
           --------------------------------------------------------------- */
        .fm-fee { position: relative; max-width: 27rem; margin-inline: auto; padding-top: 2.6rem; filter: drop-shadow(0 1.3rem 1.2rem rgba(59, 44, 34, 0.35)); rotate: -1.2deg; }
        .fm-fee::before { content: ""; position: absolute; left: calc(50% - 1px); top: -1.25rem; width: 2px; height: 5.6rem; background: var(--fm-ink); rotate: 5deg; transform-origin: 50% 100%; }
        .fm-fee-tag {
            position: relative;
            padding: 4.6rem 1.6rem 1.5rem;
            clip-path: polygon(24% 0, 76% 0, 100% 3.6rem, 100% 100%, 0 100%, 0 3.6rem);
            -webkit-mask: radial-gradient(circle at 50% 1.8rem, transparent 0.5rem, #000 0.54rem);
            mask: radial-gradient(circle at 50% 1.8rem, transparent 0.5rem, #000 0.54rem);
        }
        .fm-fee-tag::after { content: ""; position: absolute; inset: 0; background-image: var(--fm-grain); mix-blend-mode: multiply; opacity: 0.3; pointer-events: none; }
        .fm-fee-tag::before { content: ""; position: absolute; left: calc(50% - 1rem); top: 0.8rem; width: 2rem; height: 2rem; border-radius: 50%; border: 0.34rem solid #fffaf0; }
        .fm-fee-tag h3 { display: inline; margin-inline-end: 0.5rem; font-size: 3rem; }
        .fm-fee-tag > p { margin-top: 0.5rem; font-size: 0.98rem; color: #4f3d30; }
        .fm-fee dl { margin-top: 1.1rem; border-top: 2px solid #3b2c22; }
        .fm-fee dl > div { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding: 0.62rem 0.1rem 0.52rem; border-bottom: 1px dashed rgba(59, 44, 34, 0.45); }
        .fm-fee dt { font-weight: 700; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: #4f3d30; }
        .fm-fee dd { font-family: var(--fm-sub); font-size: 1.05rem; text-align: end; }
        .fm-fee-note { margin-top: 1rem; font-size: 0.875rem; color: #4f3d30; }
        .fm-trays { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; max-width: 27rem; margin: 2.25rem auto 0; }
        .fm-trays p { margin-top: 0.7rem; text-align: center; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--fm-ink-2); }
        .fm-tray {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 3%;
            padding: 5.5%;
            border-radius: 7% / 8.5%;
            background: var(--fm-tray);
            box-shadow: inset 0 0 0 2px rgba(59, 44, 34, 0.3), inset 0 0.5rem 0.9rem rgba(255, 255, 255, 0.25), 0 1rem 1.3rem -0.8rem rgba(59, 44, 34, 0.6);
        }
        .fm-tray i { position: relative; aspect-ratio: 1; border-radius: 50%; background: radial-gradient(circle at 50% 58%, rgba(59, 44, 34, 0.42), rgba(59, 44, 34, 0.14) 68%); }
        .fm-tray i.is-egg::after {
            content: "";
            position: absolute;
            inset: 7% 12% 6%;
            border-radius: 50% 50% 50% 50% / 56% 56% 44% 44%;
            background: radial-gradient(circle at 36% 28%, #fffaf0 0 10%, #ecd3ae 46%, #cfa87a 100%);
            box-shadow: 0 0.12rem 0.22rem rgba(59, 44, 34, 0.5);
        }
        html.es-anim #fm [data-reveal].is-revealed .fm-tray i.is-egg::after { animation: fm-egg 0.42s cubic-bezier(0.3, 1.5, 0.5, 1) backwards; animation-delay: calc(var(--i) * 38ms + 0.25s); }
        @keyframes fm-egg { from { opacity: 0; scale: 0.3; } to { opacity: 1; scale: 1; } }
        .fm-fees { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem; }
        @media (min-width: 1000px) {
            .fm-fees { grid-template-columns: minmax(0, 0.9fr) minmax(0, 1fr); grid-template-rows: auto 1fr; column-gap: 4.5rem; row-gap: 0; }
            .fm-fees-tag { grid-column: 1; grid-row: 1 / span 2; }
            .fm-fees-head { grid-column: 2; grid-row: 1; }
            .fm-fees-rest { grid-column: 2; grid-row: 2; }
            .fm-fees-rest { padding-top: 2rem; }
        }
        .fm-items { display: grid; gap: 1rem; margin-top: 2rem; }
        .fm-items .fm-card { padding: 1.05rem 1.2rem 1rem; }
        .fm-items .fm-card h3 { font-size: 1.12rem; }
        .fm-items .fm-card p { margin-top: 0.4rem; font-size: 0.98rem; }
        .fm-after { margin-top: 1.6rem; }

        /* ---------------------------------------------------------------
           The pitch sign: the code, stood in a crate
           --------------------------------------------------------------- */
        .fm-stand { position: relative; max-width: 22rem; margin-inline: auto; padding-bottom: 8.4rem; }
        .fm-sign { position: relative; z-index: 2; padding: 1.4rem 1.4rem 1.2rem; border: 2px solid #3b2c22; text-align: center; rotate: -2deg; box-shadow: 0 1.2rem 1.6rem -1.1rem rgba(59, 44, 34, 0.6); }
        .fm-sign-title { font-size: 2.5rem; color: #1f4d24; }
        .fm-sign svg { width: 10.5rem; height: 10.5rem; margin: 0.7rem auto 0; display: block; }
        .fm-sign-scan { margin-top: 0.9rem; font-family: var(--fm-sub); font-size: 1.15rem; line-height: 1.2; }
        .fm-sign-url { margin-top: 0.8rem; padding-top: 0.75rem; border-top: 1px dashed rgba(59, 44, 34, 0.5); font-weight: 700; font-size: 0.86rem; letter-spacing: 0.04em; color: #4f3d30; overflow-wrap: anywhere; }
        .fm-sign-how { margin-top: 0.45rem; font-size: 0.8rem; color: #4f3d30; }
        .fm-stand-stake { position: absolute; z-index: 1; left: calc(50% - 0.45rem); bottom: 3rem; width: 0.9rem; height: 9rem; background: linear-gradient(90deg, #a87c4b, #d2ab78 45%, #a87c4b); box-shadow: 0 0 0 1.5px #3b2c22; }
        .fm-stand-veg { position: absolute; z-index: 2; inset: auto 0.6rem 5.1rem 0.6rem; display: flex; justify-content: space-between; align-items: flex-end; }
        .fm-stand-veg i { flex: none; width: 15%; aspect-ratio: 1; border-radius: 50%; background: radial-gradient(circle at 34% 30%, rgba(255, 255, 255, 0.55) 0 9%, var(--c) 30%); box-shadow: inset 0 -0.3rem 0.5rem rgba(59, 44, 34, 0.3), 0 0 0 1.5px #3b2c22; }
        .fm-stand-veg i:nth-child(even) { translate: 0 -22%; }
        .fm-crate {
            position: absolute;
            z-index: 3;
            inset: auto 0 0 0;
            height: 6.2rem;
            border: 2px solid #3b2c22;
            border-radius: 0.2rem;
            background-color: #c9a36f;
            background-image:
                radial-gradient(circle at 0.7rem 1rem, #3b2c22 0 0.14rem, transparent 0.17rem),
                radial-gradient(circle at calc(100% - 0.7rem) 1rem, #3b2c22 0 0.14rem, transparent 0.17rem),
                repeating-linear-gradient(180deg, rgba(59, 44, 34, 0.55) 0 2px, transparent 2px 2.05rem),
                repeating-linear-gradient(91deg, rgba(59, 44, 34, 0.07) 0 1px, transparent 1px 6px),
                linear-gradient(180deg, #d6b382, #c1996a);
            background-size: 100% 2.05rem, 100% 2.05rem, auto, auto, auto;
            display: grid;
            place-items: center;
        }
        .fm-crate span { padding: 0.2rem 0.8rem 0.05rem; font-size: 1.9rem; color: #3b2c22; opacity: 0.8; letter-spacing: 0.12em; }

        /* ---------------------------------------------------------------
           Perfect for: six crates, labelled
           --------------------------------------------------------------- */
        .fm-crates { display: grid; gap: 1.5rem; }
        @media (min-width: 700px) { .fm-crates { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .fm-crates { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fm-box {
            display: flex;
            padding: 1.15rem;
            border: 2px solid #3b2c22;
            border-radius: 0.25rem;
            background-color: #c9a36f;
            background-image:
                radial-gradient(circle at 0.55rem 0.55rem, #3b2c22 0 0.13rem, transparent 0.16rem),
                radial-gradient(circle at calc(100% - 0.55rem) 0.55rem, #3b2c22 0 0.13rem, transparent 0.16rem),
                radial-gradient(circle at 0.55rem calc(100% - 0.55rem), #3b2c22 0 0.13rem, transparent 0.16rem),
                radial-gradient(circle at calc(100% - 0.55rem) calc(100% - 0.55rem), #3b2c22 0 0.13rem, transparent 0.16rem),
                repeating-linear-gradient(180deg, rgba(59, 44, 34, 0.5) 0 2px, transparent 2px 4.6rem),
                repeating-linear-gradient(91deg, rgba(59, 44, 34, 0.07) 0 1px, transparent 1px 6px),
                linear-gradient(180deg, #d6b382, #c1996a);
            box-shadow: 0 1.4rem 1.8rem -1.3rem rgba(59, 44, 34, 0.7);
        }
        .dark .fm-box,
        .dark .fm-crate { background-color: #b08a5a; }
        .fm-box-label { display: flex; flex-direction: column; flex: 1; min-width: 0; padding: 0 1.2rem 1.15rem; border: 2px solid #3b2c22; transition: rotate 0.25s ease, translate 0.25s ease; }
        .fm-box:hover .fm-box-label { rotate: -1.2deg; translate: 0 -3px; }
        .fm-box-band { margin: 0 -1.2rem 0.9rem; padding: 0.42rem 1.2rem 0.32rem; background: var(--c); color: var(--t, #fbf6ea); font-weight: 700; font-size: 0.68rem; letter-spacing: 0.18em; text-transform: uppercase; border-bottom: 2px solid #3b2c22; display: flex; justify-content: space-between; gap: 0.5rem; }
        .fm-box h3 { font-size: 2.7rem; text-wrap: balance; }
        .fm-box p { margin-top: 0.6rem; font-size: 1rem; color: #4f3d30; }
        .fm-box a { align-self: flex-start; margin-top: auto; padding-top: 1rem; }
        .fm-box a span { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; border-bottom: 2px solid #ee8a2f; transition: gap 0.2s ease; }
        .fm-box a:hover span { gap: 0.7rem; }
        .fm-box a svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           How it works: three boards on stakes
           --------------------------------------------------------------- */
        .fm-stakes { display: grid; gap: 2.5rem 1.75rem; }
        @media (min-width: 860px) { .fm-stakes { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fm-stakes { padding-bottom: 0.9rem; border-bottom: 0.5rem solid #6b4a2d; }
        .fm-staked { position: relative; padding-bottom: 3.4rem; rotate: var(--r, 0deg); transform-origin: 50% 100%; }
        .fm-staked::after { content: ""; position: absolute; left: calc(50% - 0.5rem); bottom: -0.9rem; width: 1rem; height: 4.6rem; background: linear-gradient(90deg, #a87c4b, #d2ab78 45%, #a87c4b); box-shadow: 0 0 0 1.5px #3b2c22; z-index: 0; }
        .fm-staked-board { position: relative; z-index: 1; padding: 1.2rem 1.4rem 1.4rem; border: 2px solid #3b2c22; box-shadow: 0 1rem 1.3rem -1rem rgba(59, 44, 34, 0.6); }
        .fm-staked .fm-staked-no { margin-top: 0; font-size: 4.8rem; line-height: 0.8; color: #a82a18; }
        .fm-staked h3 { margin-top: 0.5rem; font-size: 1.4rem; }
        .fm-staked p { margin-top: 0.55rem; font-size: 1rem; color: #4f3d30; }

        /* ---------------------------------------------------------------
           Key features: five tags on a rail
           --------------------------------------------------------------- */
        .fm-rail { position: relative; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.4rem 1rem; }
        @media (min-width: 900px) {
            .fm-rail { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1.25rem; padding-top: 2.8rem; }
            .fm-rail::before { content: ""; position: absolute; inset: 0.7rem -0.75rem auto; height: 3px; border-radius: 2px; background: var(--fm-ink); }
            .fm-hang::before { content: ""; position: absolute; left: calc(50% - 1px); top: calc(-2.1rem + var(--drop, 0rem) * -1); width: 2px; height: calc(3.3rem + var(--drop, 0rem)); background: var(--fm-ink); z-index: 0; }
            .fm-hang { margin-top: var(--drop, 0rem); }
        }
        .fm-hang { position: relative; display: block; filter: drop-shadow(0 0.9rem 0.8rem rgba(59, 44, 34, 0.32)); transition: rotate 0.35s cubic-bezier(0.3, 1.6, 0.5, 1); transform-origin: 50% 1.2rem; }
        .fm-hang:hover { rotate: var(--sw, 2.5deg); }
        .fm-hang:last-child:nth-child(odd) { grid-column: 1 / -1; }
        @media (min-width: 900px) { .fm-hang:last-child:nth-child(odd) { grid-column: auto; } }
        .fm-hang-tag {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 3.3rem 1rem 1.15rem;
            text-align: center;
            clip-path: polygon(26% 0, 74% 0, 100% 2.4rem, 100% 100%, 0 100%, 0 2.4rem);
            -webkit-mask: radial-gradient(circle at 50% 1.25rem, transparent 0.36rem, #000 0.4rem);
            mask: radial-gradient(circle at 50% 1.25rem, transparent 0.36rem, #000 0.4rem);
        }
        .fm-hang-tag strong { font-family: var(--fm-sub); font-weight: 400; font-size: 1.22rem; line-height: 1.15; }
        .fm-hang-tag small { display: block; margin-top: 0.5rem; font-size: 0.9rem; line-height: 1.4; color: #4f3d30; }
        .fm-hang-tag svg { width: 1.2rem; height: 1.2rem; margin: auto auto 0; padding-top: 0.6rem; box-sizing: content-box; color: #8f2012; transition: translate 0.2s ease; }
        .fm-hang:hover svg { translate: 4px 0; }
        .fm-more { margin-top: 2.25rem; text-align: center; }
        .fm-more a { display: inline-flex; align-items: center; gap: 0.45rem; }
        .fm-more svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the lettering changes.
           --------------------------------------------------------------- */
        #fm .fm-tiers > section { background: var(--fm-ground-2); }
        #fm .fm-tiers h2 { font-family: var(--fm-display); font-weight: 700; font-size: clamp(2.8rem, 5.6vw, 4.6rem); line-height: 0.95; letter-spacing: 0.015em; color: var(--fm-ink); }
        #fm .fm-tiers h2 + p { color: var(--fm-ink-2); font-size: 1.0625rem; }
        #fm .fm-tiers .grid > div { --fm-ink: #3b2c22; background: var(--fm-paper); border: 2px solid #3b2c22; border-radius: 0.3rem; box-shadow: 0 1.2rem 1.5rem -1.1rem rgba(59, 44, 34, 0.6); color: #3b2c22; }
        #fm .fm-tiers .grid > div:nth-child(2) { background: var(--fm-kraft); rotate: -0.8deg; }
        #fm .fm-tiers .grid > div span,
        #fm .fm-tiers .grid > div p,
        #fm .fm-tiers .grid > div li { color: #4f3d30; }
        #fm .fm-tiers .grid > div .text-3xl { font-family: var(--fm-display); font-weight: 700; font-size: 3.6rem; line-height: 1; color: #1f4d24; }
        #fm .fm-tiers .grid > div .uppercase { color: #3b2c22; }
        #fm .fm-tiers .grid > div .rounded-full { background: #a82a18; color: #fbf6ea; border-radius: 0.2rem; }
        #fm .fm-tiers .grid > div svg { color: #2f6330; }
        #fm .fm-tiers a.font-medium { color: var(--fm-ink); text-decoration: underline; text-decoration-color: var(--fm-wave); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        #fm .fm-tiers a.rounded-2xl { background: #2f6330; border: 2px solid #1f4d24; border-radius: 0.45rem; color: #fbf6ea; font-family: var(--fm-sub); font-weight: 400; font-size: 1.1rem; box-shadow: 0 0.7rem 1.1rem -0.6rem rgba(31, 77, 36, 0.8); }

        #fm .fm-keep > section { background: var(--fm-ground-2); border-top: 2px solid var(--fm-ink); }
        #fm .fm-keep h2 { font-family: var(--fm-display); font-weight: 700; font-size: clamp(2.6rem, 5vw, 3.8rem); line-height: 1; color: var(--fm-ink); }
        #fm .fm-keep p.uppercase { color: var(--fm-leaf); letter-spacing: 0.16em; }
        #fm .fm-keep .grid > a { background: var(--fm-paper); border: 2px solid #3b2c22; border-radius: 0.3rem; }
        #fm .fm-keep .grid > a:hover { border-color: #3b2c22; box-shadow: 0 1.2rem 1.4rem -1rem rgba(59, 44, 34, 0.6); }
        #fm .fm-keep .grid > a > span:first-child { display: none; }
        #fm .fm-keep .grid > a h3 { font-family: var(--fm-sub); font-weight: 400; font-size: 1.15rem; color: #3b2c22; }
        #fm .fm-keep .grid > a p { color: #4f3d30; }
        #fm .fm-keep .grid > a > span:last-child { color: #1f4d24; }
        #fm .fm-keep a.self-start { color: var(--fm-leaf); }

        /* ---------------------------------------------------------------
           Down the road: four neighbouring stalls
           --------------------------------------------------------------- */
        .fm-near { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.4rem 1.1rem; }
        @media (min-width: 900px) { .fm-near { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem; } }
        .fm-near a { display: flex; flex-direction: column; transition: translate 0.22s ease; }
        .fm-near a:hover { translate: 0 -4px; }
        .fm-near-front { flex: 1; display: flex; flex-direction: column; justify-content: space-between; gap: 1.5rem; min-height: 9rem; margin-inline: 0.5rem; padding: 1.1rem 1rem 0.9rem; border: 2px solid #3b2c22; border-top: 0; }
        .fm-near small { font-weight: 700; font-size: 0.68rem; letter-spacing: 0.16em; text-transform: uppercase; color: #4f3d30; }
        .fm-near strong { display: block; font-size: clamp(2rem, 4.4vw, 2.7rem); font-weight: 700; text-wrap: balance; }
        /* Two stalls abreast on a phone leave about a hundred pixels for the name: "Community" at
           the full size ran past the card's edge. */
        @media (max-width: 480px) {
            #fm .fm-near strong { font-size: 1.65rem; }
            #fm .fm-near-front { margin-inline: 0.35rem; padding-inline: 0.7rem; }
        }
        .fm-near svg { width: 1.3rem; height: 1.3rem; align-self: flex-end; color: #8f2012; transition: translate 0.2s ease; }
        .fm-near a:hover svg { translate: 4px 0; }

        /* ---------------------------------------------------------------
           Questions: asked across the trestle
           --------------------------------------------------------------- */
        .fm-trestle { max-width: 52rem; margin-inline: auto; }
        .fm-roll { margin-inline: clamp(0.4rem, 2vw, 1.25rem); padding: 0.5rem clamp(1rem, 3vw, 2rem) 2.25rem; border-inline: 2px solid #3b2c22; counter-reset: fm-q; -webkit-mask: conic-gradient(from -45deg at 50% 100%, #000 90deg, #0000 0) bottom / 1.1rem 0.55rem repeat-x, linear-gradient(#000 0 0) top / 100% calc(100% - 0.5rem) no-repeat; mask: conic-gradient(from -45deg at 50% 100%, #000 90deg, #0000 0) bottom / 1.1rem 0.55rem repeat-x, linear-gradient(#000 0 0) top / 100% calc(100% - 0.5rem) no-repeat; }
        .fm-roll details { counter-increment: fm-q; border-bottom: 1px dashed rgba(59, 44, 34, 0.5); }
        .fm-roll summary { display: grid; grid-template-columns: 2.6rem minmax(0, 1fr) 1.4rem; gap: 0.6rem; align-items: start; padding: 1.2rem 0.1rem 1.05rem; cursor: pointer; }
        .fm-roll summary::before { content: counter(fm-q); font-family: var(--fm-display); font-weight: 700; font-size: 2.3rem; line-height: 0.8; color: #a82a18; }
        .fm-roll h3 { font-size: 1.22rem; }
        .fm-roll summary i { position: relative; width: 1.4rem; height: 1.4rem; margin-top: 0.1rem; }
        .fm-roll summary i::before,
        .fm-roll summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: #3b2c22; transition: rotate 0.3s ease; }
        .fm-roll summary i::after { rotate: 90deg; }
        .fm-roll details[open] summary i::after { rotate: 0deg; }
        .fm-roll details p { padding: 0 0.2rem 1.4rem 3.2rem; color: #4f3d30; }
        @media (max-width: 34rem) { .fm-roll details p { padding-inline-start: 0.2rem; } }

        /* ---------------------------------------------------------------
           Opening day: a paper bag, and a label to write on
           --------------------------------------------------------------- */
        .fm-last { overflow: clip; }
        .fm-last-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem; align-items: center; }
        @media (min-width: 1000px) { .fm-last-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 0.92fr); gap: 4rem; } }
        .fm-last .fm-h2 { margin-block: 1.2rem 1.3rem; font-size: clamp(3.4rem, 8vw, 6.6rem); }
        .fm-bagwrap { position: relative; max-width: 28rem; margin-inline: auto; padding-top: 5.5rem; isolation: isolate; }
        .fm-greens { position: absolute; z-index: -1; inset: 0 8% auto 8%; height: 9rem; }
        .fm-greens i { position: absolute; bottom: 0; left: var(--x); width: var(--w, 3.4rem); height: var(--h, 7rem); border-radius: 80% 0 80% 0; background: linear-gradient(160deg, var(--c, #5c9b4c), #2f6330); rotate: var(--r, 0deg); transform-origin: 50% 100%; box-shadow: 0 0 0 1.5px #3b2c22; }
        .fm-greens i.is-round { border-radius: 50%; height: var(--w, 3.4rem); background: radial-gradient(circle at 34% 30%, rgba(255, 255, 255, 0.5) 0 9%, var(--c) 30%); }
        html.es-anim #fm [data-reveal]:not(.is-revealed) .fm-greens i { translate: 0 60%; }
        .fm-greens i { transition: translate 0.8s cubic-bezier(0.3, 1.3, 0.5, 1); transition-delay: calc(var(--i, 0) * 70ms + 0.3s); }
        .fm-bag {
            position: relative;
            padding: 2.4rem 1.5rem 2rem;
            background-image:
                linear-gradient(90deg, rgba(59, 44, 34, 0.16), rgba(59, 44, 34, 0) 9%, rgba(255, 255, 255, 0.14) 30%, rgba(59, 44, 34, 0) 52%, rgba(59, 44, 34, 0.1) 91%, rgba(59, 44, 34, 0.22)),
                var(--fm-grain);
            background-blend-mode: normal, multiply;
            -webkit-mask: conic-gradient(from 135deg at 50% 0, #000 90deg, #0000 0) top / 1rem 0.5rem repeat-x, linear-gradient(#000 0 0) bottom / 100% calc(100% - 0.48rem) no-repeat;
            mask: conic-gradient(from 135deg at 50% 0, #000 90deg, #0000 0) top / 1rem 0.5rem repeat-x, linear-gradient(#000 0 0) bottom / 100% calc(100% - 0.48rem) no-repeat;
        }
        .fm-bagwrap { filter: drop-shadow(0 1.8rem 1.6rem rgba(59, 44, 34, 0.4)); }
        .dark .fm-bagwrap { filter: drop-shadow(0 0 3.5rem rgba(245, 190, 110, 0.22)); }
        #fm .fm-bagwrap { filter: drop-shadow(0 1.8rem 1.6rem rgba(59, 44, 34, 0.4)); }
        .dark #fm .fm-bagwrap { filter: drop-shadow(0 0 3.5rem rgba(245, 190, 110, 0.22)); }
        .fm-bag-label { padding: 1.3rem 1.2rem 1.15rem; border: 2px solid #3b2c22; box-shadow: inset 0 0 0 0.3rem #fffaf0, inset 0 0 0 calc(0.3rem + 1.5px) #2f6330; }
        .fm-bag-label > strong { display: block; text-align: center; font-size: 2.6rem; color: #1f4d24; }
        .fm-bag-label label { display: block; margin-block: 0.8rem 0.4rem; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: #4f3d30; }
        .fm-bag-form { display: grid; gap: 0.9rem; }
        #fm .fm-claim { display: flex; align-items: center; min-width: 0; padding: 1rem 0.8rem; border: 0; border-bottom: 2px solid #3b2c22; background: rgba(59, 44, 34, 0.05); font-weight: 700; font-size: clamp(0.92rem, 3.4vw, 1.08rem); transition: box-shadow 0.2s ease, background-color 0.2s ease; }
        #fm .fm-claim:focus-within { border-color: #3b2c22; background: rgba(245, 215, 110, 0.4); box-shadow: 0 0 0 3px rgba(238, 138, 47, 0.55); }
        #fm .fm-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #3b2c22; box-shadow: none; outline: none; }
        #fm .fm-claim input::placeholder { color: #7a6656; }
        .fm-claim span { flex: none; color: #4f3d30; user-select: none; }
        /* Below 472px the label's own type is under 16px, and iOS zooms the page when a
           smaller field takes focus. What is typed is 16px; the placeholder keeps the
           size of the address it sits beside. */
        @media (max-width: 29.49rem) {
            #fm .fm-claim input { font-size: 1rem; }
            #fm .fm-claim input::placeholder { font-size: clamp(0.92rem, 3.4vw, 1.08rem); }
        }
        /* On the narrowest phones the label gives up some padding so "your-market" is not cut. */
        @media (max-width: 24.99rem) {
            #fm .fm-claim { padding-inline: 0.5rem; }
        }
        .fm-bag-form .fm-btn { width: 100%; }
        .fm-bag-fine { margin-top: 0.8rem; text-align: center; font-size: 0.9rem; color: #4f3d30; }

        /* The section index: pitch numbers down the edge, on wide screens only. */
        .fm-index { display: none; }
        @media (min-width: 1500px) {
            /* The nav is a box the size of the page that clips the index, so the index stays
               fixed to the screen and still ends where the page does instead of riding over
               the site footer. */
            .fm-index { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .fm-index ol { position: fixed; right: 1.25rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.3rem; counter-reset: fm-ix; pointer-events: auto; }
            .fm-index a { position: relative; display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border: 1.5px solid var(--fm-ink); border-radius: 50%; background: var(--fm-ground); counter-increment: fm-ix; transition: background-color 0.25s ease, scale 0.25s ease; }
            .fm-index a::before { content: counter(fm-ix); font-weight: 700; font-size: 0.72rem; line-height: 1; color: var(--fm-ink); }
            .fm-index a span { position: absolute; right: calc(100% + 0.6rem); padding: 0.25rem 0.6rem 0.18rem; background: var(--fm-kraft); color: #3b2c22; font-weight: 700; font-size: 0.7rem; letter-spacing: 0.12em; text-transform: uppercase; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; }
            .fm-index a:hover span,
            .fm-index a:focus-visible span { opacity: 1; }
            .fm-index a.is-active { background: #f5d76e; scale: 1.15; }
            .fm-index a.is-active::before { color: #3b2c22; }
        }

        @media (prefers-reduced-motion: reduce) {
            .fm-aug .fm-day.is-off::after { animation: none; }
            .fm-btn, .fm-card, .fm-hang, .fm-near a, .fm-box-label, .fm-pitch-in, .fm-day, .fm-greens i { transition: none; }
        }
    </style>

    @php
        // $proMonthly comes from the marketing.* view composer.

        // Where the tier line falls, straight from Event::canSellPaidTickets():
        // free RSVP places are unlimited on every plan, and a pitch fee with a
        // price on it needs Pro or Enterprise. There is no monthly allowance to
        // read out of config any more, so nothing here interpolates a number.

        // ONE season, written down once. Every figure the page states is
        // derived from this, so the strip and the prose cannot drift apart.
        // Saturdays from opening day to closing day, grouped by month; each
        // month's slot count is what gives it its width in the strip.
        $seasonMonths = [
            ['May', [2, 9, 16, 23, 30]],
            ['Jun', [6, 13, 20, 27]],
            ['Jul', [4, 11, 18, 25]],
            ['Aug', [1, 8, 15, 22, 29]],
            ['Sep', [5, 12, 19, 26]],
            ['Oct', [3, 10, 17, 24, 31]],
        ];
        $rainedOff = 'Aug 8';

        $saturdays = [];
        foreach ($seasonMonths as [$mName, $mDays]) {
            foreach ($mDays as $mDay) {
                $saturdays[] = $mName . ' ' . $mDay;
            }
        }
        $totalSaturdays = count($saturdays);
        $marketDays = $totalSaturdays - 1;
        $openingDay = $saturdays[0];
        $closingDay = $saturdays[$totalSaturdays - 1];
        $winterMarkets = ['Dec 5', 'Dec 12'];

        // The pitch list. Two states only, because approved_subdomains is
        // the ONLY thing that skips require_approval, and only for what a
        // trader sends signed in from their own schedule: a submission while
        // require_account is on (EventController::guestImportWithAccount()),
        // or an event of theirs they add the market to
        // (Role::autoAcceptsEventFrom()).
        $pitches = [
            ['Hedgerow Farm', 'Produce', true],
            ['Loaf and Crumb', 'Bakery', true],
            ['Ninefold Apiary', 'Honey and preserves', true],
            ['Wolds Cut Flowers', 'Flowers and plants', false],
            ['Salt Marsh Dairy', 'Cheese', false],
            ['Two Rivers Press', 'Cider and juice', true],
        ];

        // Market morning, as event parts on one market day.
        $morning = [
            ['06:30', 'Traders arrive', 'Pitches marked out along the top of the square.'],
            ['08:00', 'Market opens', 'Twenty-two stalls, and the coffee cart by the cross.'],
            ['10:30', 'Chef demo', 'Thirty free places, taken by RSVP, counted on this date alone.'],
            ['12:00', 'Fiddle band', 'Forty minutes on the church steps.'],
            ['13:00', 'Market closes', 'Last hour is when the bakery discounts.'],
            ['14:00', 'Square swept', 'Cleared and handed back to the council.'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for farmers markets?',
                'a' => 'Yes. The whole season is free forever: a recurring market day with a closing date, date exceptions for the Saturdays you lose to weather, sub-schedules for produce, bakery, flowers and the winter market, an agenda on each market day, free RSVP with a places limit, a downloadable QR code that puts your market page in a shopper\'s hand, built-in analytics, two-way Google, Outlook and CalDAV sync, and an embeddable calendar. Newsletters are free too, at ten emails a month counted one per recipient, and go up to a hundred on Pro and a thousand on Enterprise. Charging a pitch fee is the one part that needs Pro at '.plan_price($proMonthly).' a month, and there are zero platform fees on what you take whatever the plan.',
            ],
            [
                'q' => 'How do I set up a whole market season at once?',
                'a' => 'Create the market as one recurring event, pick the days of the week it runs and the hours, and give the recurrence an end: a closing date, or a number of market days. The season then stops on its own instead of still listing markets in February. A midweek evening market is its own event, because one recurring event has one start time.',
            ],
            [
                'q' => 'What happens when a market day is rained off?',
                'a' => 'Take that single date out of the recurrence as a date exception and it comes off the listing, leaving the rest of the season untouched. Being straight about what shoppers see: the date is simply absent rather than crossed out with a notice, so if you want to explain why, that is a newsletter or a note on the market page. The same panel has an include list that puts a one-off date on, for a bank holiday Monday market.',
            ],
            [
                'q' => 'Can traders put themselves forward for a market day?',
                'a' => 'Yes, on the free plan. Turn on submissions and traders can offer themselves for a date through your market page, with your terms shown on the form. Every submission waits for you to approve it, so nothing appears publicly that you have not agreed to, and you are emailed when new ones are waiting. Turn on Require Account and name your regulars as approved schedules: what they then submit, signed in, is approved automatically, which leaves you reading only the new ones. On the Pro plan you can add your own questions to that form.',
            ],
            [
                'q' => 'Can I charge for pitches and take the money online?',
                'a' => 'Yes, on Pro at '.plan_price($proMonthly).' a month, which is what opens paid checkout. A pitch fee is a named ticket type with its own price and stock, and the stock is counted per market date, so a full Saturday does not stop the following Saturday selling. Scanning the QR code at the gate on market morning is free on any plan. Pro also adds the live check-in dashboard and your own questions at checkout, such as whether they need power or how long the van is. Traders pay through your own Stripe or PayPal account, or by a payment link or cash, and Event Schedule charges no platform fee on top.',
            ],
            [
                'q' => 'Can I refund a pitch fee if a trader pulls out or the day is rained off?',
                'a' => 'Yes, on every plan, from the Sales page, in full or in part. A fee paid through Stripe or PayPal goes back to the trader through that provider, and only then is the sale marked refunded. A fee paid by cash, a payment link or any other way shows Mark as Refunded instead, which records the refund without moving money. A partial refund keeps the pitch booked, and a full one frees it for somebody else. Event Schedule does not email the trader about a refund, so a word from you is still worth sending.',
            ],
            [
                'q' => 'How do shoppers hear about the market?',
                'a' => 'They follow the market, and you email them. Your schedule has a QR code you can download and print on the A-board, the pitch sign or the tote bags, so somebody standing in front of you is one scan and one tap from being on the list. Once they confirm the address, new events you post reach them on their own, batched so a season posted in one sitting is one message. When there is something worth saying beyond the dates, a new trader or the first strawberries, that is a newsletter you write. You can also embed the calendar on the website you already have, and shoppers can subscribe to the market\'s calendar feed without giving an email address: the next three months of market days appear in their own Google, Outlook or Apple calendar and keep rolling forward, and a date you move or take out follows.',
            ],
            [
                'q' => 'Can I run a separate winter or evening market?',
                'a' => 'Yes, on every plan. Sub-schedules keep the winter market, the midweek evening market and the craft strand on their own strands of the same link, each with a link that shows only those events, so somebody looking for the December markets is not scrolling through the summer.',
            ],
        ];

        $dotSections = [
            ['top', 'The season'],
            ['season', 'Set it once'],
            ['rain', 'The rain call'],
            ['pitch', 'The pitch list'],
            ['morning', 'Market morning'],
            ['stalls', 'Pitch fees'],
            ['board', 'The pitch sign'],
            ['who', 'Perfect for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Opening day'],
        ];

        // ---- Drawing data. Everything below is read off the season above, so the
        // wheel, the run of stalls and the prose cannot drift apart.

        // The year in which those dates are Saturdays, and the turn that puts the
        // season over the top of the wheel instead of underneath it.
        $fmYear = 2026;
        $fmTurn = -210;
        $fmMonthNo = ['Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12];
        $fmAngle = function (string $label) use ($fmYear, $fmTurn, $fmMonthNo) {
            [$fmM, $fmD] = explode(' ', $label);
            $fmDayOfYear = (int) (new \DateTime(sprintf('%d-%02d-%02d', $fmYear, $fmMonthNo[$fmM], $fmD)))->format('z');

            return round($fmDayOfYear / 365 * 360 + $fmTurn, 2);
        };
        $fmOpen = $fmAngle($openingDay);
        $fmClose = $fmAngle($closingDay);

        // Twelve months round the rim, each in the colour of its part of the year,
        // with a dark hairline at the first of the month.
        $fmYearMonths = [
            ['Jan', 31, '#dde2d8'], ['Feb', 28, '#d3ddc8'], ['Mar', 31, '#c3d9a4'], ['Apr', 30, '#a6cc7c'],
            ['May', 31, '#84ba5c'], ['Jun', 30, '#6ba84c'], ['Jul', 31, '#f2d264'], ['Aug', 31, '#f5bc4e'],
            ['Sep', 30, '#ee9438'], ['Oct', 31, '#dd6a35'], ['Nov', 30, '#b98f66'], ['Dec', 31, '#cdd4cf'],
        ];
        $fmStops = [];
        $fmNames = [];
        $fmFrom = 0;
        foreach ($fmYearMonths as [$fmName, $fmLength, $fmColour]) {
            $fmA0 = round($fmFrom / 365 * 360, 2);
            $fmA1 = round(($fmFrom + $fmLength) / 365 * 360, 2);
            $fmStops[] = '#3b2c22 ' . $fmA0 . 'deg ' . ($fmA0 + 0.45) . 'deg';
            $fmStops[] = $fmColour . ' ' . ($fmA0 + 0.45) . 'deg ' . $fmA1 . 'deg';

            // Where the month's name goes: on the upper arc, read clockwise, or on
            // the lower arc, drawn the other way so the letters stay upright.
            $fmMid = ($fmFrom + $fmLength / 2) / 365 * 360 + $fmTurn;
            if ($fmMid < -180) {
                $fmMid += 360;
            }
            if ($fmMid >= -90 && $fmMid <= 90) {
                $fmNames[] = [$fmName, 'top', round(($fmMid + 90) / 180 * 100, 2)];
            } else {
                $fmClockwise = $fmMid < 0 ? $fmMid + 360 : $fmMid;
                $fmNames[] = [$fmName, 'bot', round((270 - $fmClockwise) / 180 * 100, 2)];
            }
            $fmFrom += $fmLength;
        }
        $fmConic = 'conic-gradient(from ' . $fmTurn . 'deg, ' . implode(', ', $fmStops) . ')';

        // The square: twenty-two pitches, as the morning has them. [column, row, index
        // into $pitches or null]. Numbered in walking order from the top left.
        $fmSquare = [
            [1, 1, 0], [2, 1, 1], [3, 1, null], [4, 1, 2], [5, 1, null], [6, 1, null], [7, 1, null], [8, 1, 5],
            [8, 3, null], [8, 4, null], [8, 5, null],
            [6, 5, null], [5, 5, 4], [4, 5, null], [3, 5, null],
            [1, 5, null], [1, 4, null], [1, 3, null],
            [3, 3, 3], [4, 3, null], [5, 3, null], [6, 3, null],
        ];
        $fmCanopies = ['#3f7d3a', '#e2b33c', '#ee8a2f', '#d9412b', '#7a5a3c', '#b9722a'];

        $fmArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $fmDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="fm">

        <nav class="fm-index es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}"><span aria-hidden="true">{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the season wheel                                    -->
        <!-- ============================================================ -->
        <section id="top" class="fm-hero" style="scroll-margin-top: 5rem;">
            <div class="fm-wrap fm-hero-grid">
                <div class="fm-copy">
                    <h1 class="fm-h1">
                        <x-marketing.hero-eyebrow class="fm-eyebrow es-fade-up es-d-1">Farmers market calendar, for outdoor markets too</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">A market is not</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">a Saturday.</span></span>
                        <span class="es-mask fm-mask-3"><span class="es-mask-line">It is <span class="fm-em">a season</span>.</span></span>
                    </h1>

                    <p class="fm-lede es-fade-up es-d-2">
                        Opening day in May, the same square every Saturday, closing day at the end of
                        October, then two winter markets. That is one recurring event with a closing
                        date, not {{ $totalSaturdays }} entries typed in by hand.
                    </p>

                    <div class="fm-cta es-fade-up es-d-3">
                        <a href="#season" class="fm-btn fm-btn-ghost">
                            See how a season is set up
                            {!! $fmDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="fm-btn">
                            Create your market's calendar
                            {!! $fmArrow !!}
                        </a>
                    </div>
                </div>

                <!-- The season, to scale: the year turned so the season is over the top. -->
                <div class="fm-wheelcol es-fade-up es-d-2">
                    <div class="fm-wheel" style="--fm-open: {{ $fmOpen }}deg; --fm-close: {{ $fmClose }}deg; --fm-span: {{ round($fmClose - $fmOpen, 2) }}deg; --fm-peg: {{ $fmOpen }}deg;">
                        <div class="fm-wheel-months" aria-hidden="true" style="background-image: {{ $fmConic }};"></div>
                        <svg class="fm-wheel-names" viewBox="0 0 200 200" aria-hidden="true">
                            <defs>
                                <path id="fm-arc-top" d="M 11.6,100 A 88.4,88.4 0 0 1 188.4,100" />
                                <path id="fm-arc-bot" d="M 7.2,100 A 92.8,92.8 0 0 0 192.8,100" />
                            </defs>
                            @foreach ($fmNames as [$fmName, $fmArc, $fmAt])
                                <text><textPath href="#fm-arc-{{ $fmArc }}" startOffset="{{ $fmAt }}%" text-anchor="middle">{{ $fmName }}</textPath></text>
                            @endforeach
                        </svg>
                        <div class="fm-wheel-arc" aria-hidden="true"></div>
                        <div class="fm-wheel-ticks" aria-hidden="true">
                            @foreach ($saturdays as $fmSat)
                                <i class="fm-tick @if ($fmSat === $rainedOff) is-off @endif" style="--a: {{ $fmAngle($fmSat) }}deg; --i: {{ $loop->index }};"></i>
                            @endforeach
                            @foreach ($winterMarkets as $wm)
                                <i class="fm-tick is-winter" style="--a: {{ $fmAngle($wm) }}deg; --i: {{ $totalSaturdays + $loop->index }};"></i>
                            @endforeach
                        </div>
                        <div class="fm-wheel-peg" aria-hidden="true"></div>
                        <div class="fm-wheel-hub">
                            <h2>Saturday Market</h2>
                            <p class="fm-wheel-run">{{ $openingDay }} to {{ $closingDay }}</p>
                            <p class="fm-wheel-facts">Saturdays, 8am to 1pm &middot; {{ $marketDays }} market days &middot; one rained off</p>
                        </div>
                    </div>

                    <div class="fm-legend">
                        <p>
                            <i aria-hidden="true"></i>
                            <span>One slot is one market day. The dashed one is {{ $rainedOff }}, taken out as a date exception.</span>
                        </p>
                        <p>
                            <i class="is-winter" aria-hidden="true"></i>
                            <span>{{ implode(' and ', $winterMarkets) }}: the winter market, on its own sub-schedule and its own link.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Every kind of stall, tagged on a line of twine -->
            <div class="fm-twine es-fade-up es-d-4">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach (['Produce', 'Bakery', 'Cut Flowers', 'Honey', 'Cheese', 'Preserves', 'Seedlings', 'Craft', 'Street Food', 'Winter Market'] as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="fm-chip">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. Set it once                                               -->
        <!-- ============================================================ -->
        <section id="season" class="fm-sec fm-band" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <span class="fm-tag" data-reveal>Set it once</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Opening day, closing day, and <span class="fm-em">every Saturday between</span>.
                    </h2>
                    <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Three choices turn one event into a whole season, and all three are on the free plan.
                    </p>
                </header>

                <!-- The run, a stall for every market day -->
                <div class="fm-stall" data-reveal aria-hidden="true">
                    <div class="fm-awn"></div>
                    <div class="fm-paper fm-run">
                        @php $fmDayNo = 0; @endphp
                        @foreach ($seasonMonths as [$mName, $mDays])
                            <div class="fm-run-month">
                                <div class="fm-run-days">
                                    @foreach ($mDays as $mDay)
                                        <span class="fm-day @if ($mName . ' ' . $mDay === $rainedOff) is-off @endif" style="--i: {{ $fmDayNo++ }};"><b>{{ $mDay }}</b></span>
                                    @endforeach
                                </div>
                                <p>{{ $mName }}</p>
                            </div>
                        @endforeach
                        <div class="fm-run-month is-winter">
                            <div class="fm-run-days">
                                @foreach ($winterMarkets as $wm)
                                    <span class="fm-day is-winter" style="--i: {{ $fmDayNo++ }};"><b>{{ explode(' ', $wm)[1] }}</b></span>
                                @endforeach
                            </div>
                            <p>Dec</p>
                        </div>
                    </div>
                </div>

                <div class="fm-line3" data-reveal-group="110">
                    @foreach ([
                        ['The days it runs', 'Pick the days of the week and the hours. A Saturday market is one tick on one event, not a new entry every week for six months.'],
                        ['The end of the season', 'Give the recurrence a closing date, or a number of market days. This is the setting that makes a season a season instead of a weekly listing that runs forever.'],
                        ['A second market day', 'A midweek evening market is its own event with its own hours, because one recurring event has one start time. Two events, two sets of hours, nothing ambiguous.'],
                    ] as [$sT, $sD])
                        <div class="fm-paper fm-pegged" data-reveal style="--r: {{ [-1.4, 0.8, -0.6][$loop->index] }}deg;">
                            <h3 class="fm-s">{{ $sT }}</h3>
                            <span class="fm-tag fm-tier fm-tier-free">Free</span>
                            <p>{{ $sD }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="fm-small fm-foot" data-reveal>
                    Change the hours once and every market day follows. Put the winter market on its own
                    sub-schedule and it keeps its own link without splitting the market into two pages.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The rain call                                             -->
        <!-- ============================================================ -->
        <section id="rain" class="fm-sec" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap fm-duo">
                <div>
                    <header class="fm-head" style="margin-bottom: 0;">
                        <span class="fm-tag" data-reveal>The rain call</span>
                        <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                            A washed-out Saturday comes <span class="fm-em">off the calendar</span>.
                        </h2>
                        <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                            You call it at six in the morning with wet hands. Take that one date out of the
                            recurrence and it is gone from the listing, and the other {{ $marketDays }} market
                            days are untouched.
                        </p>
                    </header>

                    <ul class="fm-points" data-reveal-group="90">
                        @foreach ([
                            ['One date, not the season', 'A date exception removes a single occurrence. You are not rebuilding the recurrence and you are not deleting the market.'],
                            ['Dates can go back in, too', 'Next to it is an include list that adds a one-off date that is not on the usual day, for a bank holiday Monday market or a late-night in December.'],
                            ['What shoppers actually see', 'That Saturday is simply absent. There is no cancelled banner and no strike-through, so if the reason matters, send a newsletter or say it on the market page.'],
                            ['Their calendars follow', 'A shopper who subscribed to the market\'s calendar loses that Saturday from their own the next time it checks the feed. A single date they downloaded is a copy, so that one stays.'],
                        ] as [$rT, $rD])
                            <li data-reveal>
                                <div><strong>{{ $rT }}</strong> <span>{{ $rD }}</span></div>
                            </li>
                        @endforeach
                    </ul>

                    <p class="fm-tierline" data-reveal>
                        <span class="fm-tag fm-tier fm-tier-free">Free</span>
                        <span class="fm-small">Date exceptions are part of recurring events on every plan.</span>
                    </p>
                </div>

                <div class="fm-paper fm-aug" data-reveal="zoom">
                    <div class="fm-aug-top">
                        <h3 class="fm-d">August</h3>
                        <span>1 date out</span>
                    </div>

                    <div class="fm-run-days" aria-hidden="true">
                        @foreach ($seasonMonths[3][1] as $augDay)
                            <span class="fm-day @if ('Aug ' . $augDay === $rainedOff) is-off @endif" style="--i: {{ $loop->index * 3 }};"><b>{{ $augDay }}</b></span>
                        @endforeach
                    </div>

                    <div class="fm-book">
                        @foreach ([
                            ['Aug 1', 'Market ran', '22 stalls'],
                            ['Aug 8', 'Rained off', 'date removed'],
                            ['Aug 15', 'Market ran', '24 stalls'],
                        ] as [$aDate, $aWhat, $aNote])
                            <div>
                                <span>{{ $aDate }}</span>
                                <span>{{ $aWhat }}</span>
                                <span>{{ $aNote }}</span>
                            </div>
                        @endforeach
                    </div>

                    <p class="fm-aug-note">
                        The dashed slot is not a cancelled market. It is a date the recurrence no longer produces.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The pitch list: the square, and the list it is drawn from -->
        <!-- ============================================================ -->
        <section id="pitch" class="fm-sec fm-band" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <span class="fm-tag" data-reveal>The pitch list</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Your regulars are in. <span class="fm-em">New traders wait for you</span>.
                    </h2>
                    <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Turn on submissions and traders can offer themselves for a date through your market
                        page, with your terms on the form. Every one waits for your approval, and you are
                        emailed when new ones are waiting.
                    </p>
                </header>

                <div class="fm-plot">
                    <!-- The plan. A picture of the list beside it: six of these pitches are its six lines. -->
                    <div class="fm-map" data-reveal>
                        <div class="fm-square" role="group" aria-label="Plan of the square, with the six traders on this Saturday's pitch list">
                            <span class="fm-square-label fm-square-steps" aria-hidden="true">Church steps</span>
                            @foreach ($fmSquare as $fmI => [$fmCol, $fmRow, $fmWho])
                                @if ($fmWho === null)
                                    <span class="fm-pitch" style="grid-column: {{ $fmCol }}; grid-row: {{ $fmRow }};" aria-hidden="true"><span class="fm-pitch-in" style="--i: {{ $fmI }};"></span></span>
                                @else
                                    @php [$fmTrader, $fmKind, $fmApproved] = $pitches[$fmWho]; @endphp
                                    <a href="#fm-t{{ $fmWho + 1 }}" class="fm-pitch is-named @if (! $fmApproved) is-wait @endif"
                                        style="grid-column: {{ $fmCol }}; grid-row: {{ $fmRow }}; --fm-n: '{{ $fmTrader }}'; --fm-k: '{{ $fmKind }}'; --fm-st: '{{ $fmApproved ? 'Pre-approved' : 'Waiting on you' }}';"
                                        aria-label="Pitch {{ $fmI + 1 }}: {{ $fmTrader }}, {{ $fmKind }}, {{ $fmApproved ? 'Pre-approved' : 'Waiting on you' }}. Go to its line in the list."><span class="fm-pitch-in" style="--i: {{ $fmI }}; --c: {{ $fmCanopies[$fmWho] }};"></span></a>
                                @endif
                            @endforeach
                            <span class="fm-cross" aria-hidden="true"><i></i><b>Coffee</b></span>
                            <span class="fm-cap" aria-hidden="true"><span class="fm-cap-hover">Point at a coloured pitch to read its line of the list.</span><span class="fm-cap-touch">The six coloured pitches are the six lines of the list.</span></span>
                            <span class="fm-square-label fm-square-way" aria-hidden="true">Way in</span>
                        </div>
                        <p class="fm-keys" aria-hidden="true">
                            <span><i></i> Pre-approved</span>
                            <span><i class="is-wait"></i> Waiting on you</span>
                        </p>
                    </div>

                    <div class="fm-paper fm-list" data-reveal style="--reveal-delay: 0.12s;">
                        <div class="fm-list-top" aria-hidden="true"><strong class="fm-d">This Saturday</strong><span>Pitch list</span></div>
                        <table>
                            <caption class="sr-only">This Saturday's pitch list: each trader, the sub-schedule they sit in, and whether their submission is approved automatically or waiting for the market to approve it</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Trader</th>
                                    <th scope="col">Sub-schedule</th>
                                    <th scope="col">This Saturday</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pitches as [$pName, $pKind, $pApproved])
                                    <tr id="fm-t{{ $loop->iteration }}">
                                        <th scope="row">
                                            {{ $pName }}
                                            <small>{{ $pKind }}</small>
                                        </th>
                                        <td>{{ $pKind }}</td>
                                        <td>
                                            @if ($pApproved)
                                                <span class="fm-status">Pre-approved</span>
                                            @else
                                                <span class="fm-status fm-status-wait">Waiting on you</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="fm-list-note">
                            Two states, because the product has two. Ask traders for an account, name one as an
                            approved schedule, and their submissions go straight on; everybody else waits.
                            Nothing publishes itself.
                        </p>
                    </div>
                </div>

                <div class="fm-trio" data-reveal-group="100">
                    @foreach ([
                        ['Your terms on the form', false, 'Write the pitch rules once and they appear on the submission form: insurance, waste, what time the square closes to vehicles.'],
                        ['Ask your own questions', true, 'Add fields to the submission form, so a trader tells you the stall frontage or whether they need power while they are asking.'],
                        ['Sorted as it arrives', false, 'Sub-schedules keep produce, bakery, flowers and craft on separate strands of one link, each with a link that shows only that strand.'],
                    ] as [$qT, $qIsPro, $qD])
                        <div class="fm-paper fm-card" data-reveal>
                            <h3 class="fm-s">{{ $qT }}</h3>
                            @if ($qIsPro)
                                <span class="fm-tag fm-tier fm-tier-pro">Pro</span>
                            @else
                                <span class="fm-tag fm-tier fm-tier-free">Free</span>
                            @endif
                            <p>{{ $qD }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Market morning: the hours, painted on a board              -->
        <!-- ============================================================ -->
        <section id="morning" class="fm-sec fm-wood fm-morning" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <span class="fm-tag" data-reveal>Market morning</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Half six, and the vans are <span class="fm-em">already in the square</span>.
                    </h2>
                    <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                        A market day is not one line on a calendar. Give the day an agenda and the demo,
                        the band and the last hour all sit inside the same market day.
                    </p>
                </header>

                <div class="fm-duo" style="align-items: start;">
                    <div data-reveal>
                        <div class="fm-hours">
                            <span class="fm-hours-sun" aria-hidden="true"></span>
                            <ol>
                                @foreach ($morning as [$tTime, $tName, $tNote])
                                    <li>
                                        <time class="fm-d">{{ $tTime }}</time>
                                        <div>
                                            <strong class="fm-d">{{ $tName }}</strong>
                                            <span>{{ $tNote }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                        <p class="fm-hours-note">
                            Each line is a part of the market day, with its own name and its own start
                            and finish. One event, one page, the whole morning on it.
                        </p>
                    </div>

                    <div class="fm-nailed" data-reveal-group="110">
                        @foreach ([
                            ['The day has a shape', 'Add parts to a market day for the chef demo, the fiddle band and the kids table. Shoppers read the morning rather than guessing when to turn up.'],
                            ['Places on the demo', 'A free event can take RSVPs with a limit, and the places are counted per market date, so a full demo in July leaves August alone.'],
                            ['One link, all season', 'Embed the calendar on the website you already have. A shopper can put one market day into their own Google, Outlook or Apple calendar, or subscribe to the market\'s calendar once and see the next three months of market days there, rolling forward.'],
                            ['The band gets its own page', 'Add the fiddle band to a market day and, if they are not on Event Schedule yet, they get a page listing the dates you gave them. It stays out of search until they claim it by signing in with the email address you entered for them. A band already on Event Schedule gets the date as a request to accept.'],
                        ] as [$mT, $mD])
                            <div class="fm-paper fm-note" data-reveal style="--r: {{ [0.9, -0.7, 0.5, -0.9][$loop->index] }}deg;">
                                <h3 class="fm-s">{{ $mT }}</h3>
                                <span class="fm-tag fm-tier fm-tier-free">Free</span>
                                <p>{{ $mD }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Pitch fees                                                -->
        <!-- ============================================================ -->
        <section id="stalls" class="fm-sec" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap fm-fees">
                <div class="fm-fees-head">
                    <header class="fm-head" style="margin-bottom: 0;">
                        <span class="fm-tag" data-reveal>Pitch fees</span>
                        <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Take the pitch fee online. <span class="fm-em">We take none of it</span>.
                        </h2>
                        <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                            No envelope of notes and no chasing anybody in the car park. Traders pay through
                            your own Stripe or <a href="{{ marketing_url('/paypal') }}" class="fm-link">PayPal</a>
                            account, and Event Schedule takes no platform fee on top of what they charge. A
                            payment link or cash on the morning still works for the traders who want it.
                        </p>
                    </header>
                </div>

                <div class="fm-fees-tag" data-reveal>
                    <div class="fm-fee">
                        <div class="fm-kraftp fm-fee-tag">
                            <h3 class="fm-d">Pitch fee</h3>
                            <span class="fm-tag fm-tier fm-tier-pro">Pro</span>
                            <p>A named ticket type with a price and a stock, counted per market date.</p>

                            <dl>
                                @foreach ([
                                    ['Price', '$18 a pitch'],
                                    ['Stock', '30 per market day'],
                                    ['This Saturday', '24 taken, 6 left'],
                                    ['Next Saturday', '30 available'],
                                    ['At the gate', 'QR scan on the way in'],
                                ] as [$fK, $fV])
                                    <div>
                                        <dt>{{ $fK }}</dt>
                                        <dd>{{ $fV }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            <p class="fm-fee-note">
                                A full Saturday does not stop the following Saturday selling. Each market
                                date keeps its own count. A pitch fee with a price on it is a Pro
                                feature, so a market charging its traders is a Pro market.
                            </p>
                        </div>
                    </div>

                    <!-- Thirty to the tray: each market date keeps its own. -->
                    <div class="fm-trays" aria-hidden="true">
                        <div>
                            <div class="fm-tray">@for ($fmEgg = 0; $fmEgg < 30; $fmEgg++)<i @if ($fmEgg < 24) class="is-egg" @endif style="--i: {{ $fmEgg }};"></i>@endfor</div>
                            <p>This Saturday</p>
                        </div>
                        <div>
                            <div class="fm-tray">@for ($fmEgg = 0; $fmEgg < 30; $fmEgg++)<i></i>@endfor</div>
                            <p>Next Saturday</p>
                        </div>
                    </div>
                </div>

                <div class="fm-fees-rest">
                    <div class="fm-items" data-reveal-group="90" style="margin-top: 0;">
                        @foreach ([
                            ['Counted per market date', false, 'Thirty pitches means thirty on that date. Sell out on a Saturday in June and the rest of the season is unaffected.'],
                            ['Ask while they pay', true, 'Attach your own questions to the pitch fee: power, van length, insurance number. The answers arrive with the payment instead of in a separate thread.'],
                            ['Scan them in', false, 'Every buyer gets a QR code, and scanning it at the entrance to the square costs nothing. The live check-in dashboard, counting who is in as the morning goes on, is the Pro half.'],
                            ['Money back when it rains', false, 'Refund a pitch fee from the Sales page, in full or in part. On Stripe or PayPal the money goes back to the trader through the provider; a cash or payment-link fee is marked as refunded for your records.'],
                            ['Sell tickets too, if you need to', false, 'A ticketed cooking class or a harvest supper works the same way, on Pro and with the same zero platform fee. Post it before tickets open, switch on the "Notify me" card, and shoppers can leave an email address to hear when they do.'],
                        ] as [$pT, $pIsPro, $pD])
                            <div class="fm-paper fm-card" data-reveal>
                                <h3 class="fm-s">{{ $pT }}</h3>
                                @if ($pIsPro)
                                    <span class="fm-tag fm-tier fm-tier-pro">Pro</span>
                                @else
                                    <span class="fm-tag fm-tier fm-tier-free">Free</span>
                                @endif
                                <p>{{ $pD }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="fm-small fm-after" data-reveal>
                        Charging a pitch fee is Pro, at {{ plan_price($proMonthly) }} a month with no ceiling
                        on what you sell. Publishing the season, taking submissions and free RSVP places
                        never need it.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. The pitch sign: the code, stood in a crate                 -->
        <!-- ============================================================ -->
        <section id="board" class="fm-sec fm-band" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <span class="fm-tag" data-reveal>The pitch sign</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The shopper in front of you is <span class="fm-em">an email address</span>.
                    </h2>
                    <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                        They are standing in your square with a bag of beans in their hand. That is the one
                        moment they will ever be easiest to reach, and it does not need an algorithm.
                    </p>
                </header>

                <div class="fm-duo">
                    <!-- The sign itself, the same painted card in both colour modes. -->
                    <div data-reveal>
                        <div class="fm-stand">
                            <div class="fm-paper fm-sign">
                                <p class="fm-d fm-sign-title">Saturday Market</p>
                                <svg viewBox="0 0 21 21" aria-hidden="true" shape-rendering="crispEdges">
                                    <rect x="0" y="0" width="21" height="21" fill="#fffaf0" />
                                    @php
                                        // A stand-in code pattern: three finder squares plus a
                                        // deterministic module fill. Filled rects, not an outline
                                        // drawing.
                                        $qrModules = [];
                                        for ($qy = 0; $qy < 21; $qy++) {
                                            for ($qx = 0; $qx < 21; $qx++) {
                                                $inFinder = ($qx < 8 && $qy < 8) || ($qx > 12 && $qy < 8) || ($qx < 8 && $qy > 12);
                                                if ($inFinder) {
                                                    continue;
                                                }
                                                if ((($qx * 7 + $qy * 13 + $qx * $qy) % 5) < 2) {
                                                    $qrModules[] = [$qx, $qy];
                                                }
                                            }
                                        }
                                        $qrFinders = [[0, 0], [14, 0], [0, 14]];
                                    @endphp
                                    @foreach ($qrModules as [$qx, $qy])
                                        <rect x="{{ $qx }}" y="{{ $qy }}" width="1" height="1" fill="#3b2c22" />
                                    @endforeach
                                    @foreach ($qrFinders as [$fx, $fy])
                                        <rect x="{{ $fx }}" y="{{ $fy }}" width="7" height="7" fill="#3b2c22" />
                                        <rect x="{{ $fx + 1 }}" y="{{ $fy + 1 }}" width="5" height="5" fill="#fffaf0" />
                                        <rect x="{{ $fx + 2 }}" y="{{ $fy + 2 }}" width="3" height="3" fill="#3b2c22" />
                                    @endforeach
                                </svg>
                                <p class="fm-sign-scan">Scan for this week's stalls</p>
                                <p class="fm-sign-url">your-market.eventschedule.com</p>
                                <p class="fm-sign-how">Download the code from your schedule and print it once.</p>
                            </div>
                            <div class="fm-stand-stake" aria-hidden="true"></div>
                            <div class="fm-stand-veg" aria-hidden="true">
                                <i style="--c: #d9412b;"></i><i style="--c: #ee8a2f;"></i><i style="--c: #d9412b;"></i><i style="--c: #6ba84c;"></i><i style="--c: #ee8a2f;"></i><i style="--c: #d9412b;"></i>
                            </div>
                            <div class="fm-crate" aria-hidden="true"><span class="fm-d">Pitch 07</span></div>
                        </div>
                    </div>

                    <div>
                        <div class="fm-items" data-reveal-group="90" style="margin-top: 0;">
                            @foreach ([
                                ['Print the code once', 'Every schedule has a QR code you can download as an image and put on the A-board, the pitch signs or the tote bags. One scan opens your market page, and Follow is a tap from there.'],
                                ['New events go out on their own', 'Add the winter market or a harvest supper and the list hears about it without you doing anything, batched and no more than one message every few days. A new cheesemaker or the first strawberries is a newsletter you write.'],
                                ['Ten a month, free', 'The free plan covers ten newsletter emails a month, counted one per recipient. Pro is a hundred and Enterprise is a thousand.'],
                                ['No algorithm in the middle', 'A market page and an email list are yours. Nobody decides how many of your shoppers get to see that you are open this week.'],
                            ] as [$bT, $bD])
                                <div class="fm-paper fm-card" data-reveal>
                                    <h3 class="fm-s">{{ $bT }}</h3>
                                    <span class="fm-tag fm-tier fm-tier-free">Free</span>
                                    <p>{{ $bD }}</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="fm-small fm-after" data-reveal>
                            Being exact about it: new events you add reach the list on their own, batched into
                            one digest rather than a message each, and never more than one every few days. The
                            Saturdays of one recurring season are one event, so the season goes out once, not
                            every week, and a trader's accepted submission belongs to the trader's schedule, so
                            it is not in your digest. Anything you want to say in your own words is a
                            newsletter, written when you have something to say.
                            <a href="{{ marketing_url('/features/newsletters') }}" class="fm-link">How newsletters work</a>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Perfect for: six crates                                   -->
        <!-- ============================================================ -->
        <section id="who" class="fm-sec" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <span class="fm-tag" data-reveal>Perfect for</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Any market that comes back <span class="fm-em">next week</span>.
                    </h2>
                    <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                        From a weekly square to a two-weekend Christmas market, the season is the same shape.
                    </p>
                </header>

                @php
                    $fmCrates = [
                        ['Weekly Farmers Markets', 'One recurring Saturday from opening day to closing day, with the traders who turn up every week pre-approved and the new ones waiting for you.', 'for-weekly-farmers-markets', '#2f6330', '#fbf6ea'],
                        ['Artisan & Craft Markets', 'Potters, printers and jewellers on their own sub-schedule, each with a link that shows only the craft strand of the market.', 'for-artisan-craft-markets', '#ee8a2f', '#3b2c22'],
                        ['Flea Markets & Swap Meets', 'A monthly date that repeats, pitch fees taken online, and stock counted per date so a full month does not block the next one.', 'for-flea-markets', '#f5d76e', '#3b2c22'],
                        ['Holiday & Seasonal Markets', 'Two weekends in December that live on their own sub-schedule, so the winter market has its own link and does not disturb the summer season.', 'for-holiday-markets', '#a82a18', '#fbf6ea'],
                        ['Night Markets', 'A midweek evening market as its own event with its own hours, and an agenda for the food stalls and the band.', 'for-night-markets', '#3b2c22', '#fbf6ea'],
                        ['Specialty Food Markets', 'Cheese, coffee and a chef demo with thirty free places, counted per market date so July selling out leaves August alone.', 'for-specialty-food-markets', '#6ba84c', '#3b2c22'],
                    ];
                @endphp

                <div class="fm-crates" data-reveal-group="70">
                    @foreach ($fmCrates as [$fmCrateName, $fmCrateText, $fmCrateSlug, $fmCrateBand, $fmCrateInk])
                        @php $fmPost = get_sub_audience_blog($fmCrateSlug); @endphp
                        <article class="fm-box" data-reveal>
                            <div class="fm-paper fm-box-label">
                                <div class="fm-box-band" style="--c: {{ $fmCrateBand }}; --t: {{ $fmCrateInk }};" aria-hidden="true"><span>Crate {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><span>In season</span></div>
                                <h3 class="fm-d">{{ $fmCrateName }}</h3>
                                <p>{{ $fmCrateText }}</p>
                                @if ($fmPost)
                                    <a href="{{ blog_url('/' . $fmPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $fmCrateName }}">
                                        <span>Learn more {!! $fmArrow !!}</span>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. How it works: three boards on stakes                      -->
        <!-- ============================================================ -->
        <section id="how" class="fm-sec fm-band" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <span class="fm-tag" data-reveal>How it works</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three decisions, <span class="fm-em">then it runs</span>.
                    </h2>
                </header>

                <div class="fm-stakes" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Set the season', 'The market name, the square, the days of the week and the hours. Then give the recurrence a closing date so it stops on its own.'],
                        ['02', 'Open the pitch list', 'Turn on submissions and Require Account, write your pitch terms, and name your regulars as approved schedules. Everybody else waits for you.'],
                        ['03', 'Print the code', 'Download your QR code, put it on the A-board, and start a list of shoppers who tapped Follow, yours to email with no algorithm in the middle.'],
                    ] as [$hN, $hT, $hD])
                        <div class="fm-staked" data-reveal style="--r: {{ [-1.2, 0.7, -0.5][$loop->index] }}deg;">
                            <div class="fm-paper fm-staked-board">
                                <p class="fm-d fm-staked-no" aria-hidden="true">{{ $hN }}</p>
                                <h3 class="fm-s">{{ $hT }}</h3>
                                <p>{{ $hD }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Key features: five tags on a rail                        -->
        <!-- ============================================================ -->
        <section class="fm-sec">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <h2 class="fm-d fm-h2" data-reveal>Key features</h2>
                </header>

                @php
                    $fmKeyFeatures = [
                        ['Recurring Events', 'A whole season as one market day, ending on a closing date', marketing_url('/features/recurring-events'), '0rem', '-2.5deg'],
                        ['Sub-Schedules', 'Produce, craft and the winter market on their own links', marketing_url('/features/sub-schedules'), '1.1rem', '2.5deg'],
                        ['Newsletters', 'Tell shoppers what is coming, in your own words', marketing_url('/features/newsletters'), '0.3rem', '-2deg'],
                        ['Ticketing', 'Pitch fees and tickets with QR check-in and zero platform fees', marketing_url('/features/ticketing'), '1.4rem', '2.5deg'],
                        ['Embed Calendar', 'Put the season on the website you already have', marketing_url('/features/embed-calendar'), '0.6rem', '-2.5deg'],
                    ];
                @endphp
                <div class="fm-rail" data-reveal-group="70">
                    @foreach ($fmKeyFeatures as [$fmKfName, $fmKfText, $fmKfUrl, $fmKfDrop, $fmKfSwing])
                        <a href="{{ $fmKfUrl }}" class="fm-hang" data-reveal style="--drop: {{ $fmKfDrop }}; --sw: {{ $fmKfSwing }};">
                            <span class="fm-kraftp fm-hang-tag">
                                <strong>{{ $fmKfName }}</strong>
                                <small>{{ $fmKfText }}</small>
                                {!! $fmArrow !!}
                            </span>
                        </a>
                    @endforeach
                </div>

                <p class="fm-more" data-reveal>
                    <a href="{{ marketing_url('/features') }}" class="fm-link">
                        See all features
                        {!! $fmArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <div class="fm-tiers">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 11. Related pages: four neighbouring stalls                  -->
        <!-- ============================================================ -->
        <section class="fm-sec">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <h2 class="fm-d fm-h2" data-reveal>Related pages</h2>
                </header>

                <div class="fm-near" data-reveal-group="70">
                    @foreach ([
                        ['/for-food-trucks-and-vendors', 'Food Trucks & Vendors'],
                        ['/for-breweries-and-wineries', 'Breweries & Wineries'],
                        ['/for-community-centers', 'Community Centers'],
                        ['/for-curators', 'Curators'],
                    ] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal class="fm-stall">
                            <span class="fm-awn fm-awn-s" style="--a: {{ ['#d9412b', '#ee8a2f', '#3f7d3a', '#3b2c22'][$loop->index] }};" aria-hidden="true"></span>
                            <span class="fm-paper fm-near-front">
                                <span>
                                    <small>Event Schedule for</small>
                                    <strong class="fm-d">{{ $relName }}</strong>
                                </span>
                                {!! $fmArrow !!}
                            </span>
                        </a>
                    @endforeach
                </div>

                <p class="fm-more" data-reveal>
                    <a href="{{ marketing_url('/use-cases') }}" class="fm-link">
                        See all use cases
                        {!! $fmArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 12. Questions: asked across the trestle                      -->
        <!-- ============================================================ -->
        <section id="faq" class="fm-sec fm-band" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap">
                <header class="fm-head is-mid">
                    <span class="fm-tag" data-reveal>Questions</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Asked across <span class="fm-em">the trestle</span>.
                    </h2>
                </header>

                <div class="fm-trestle" data-reveal>
                    <div class="fm-stall"><div class="fm-awn" style="--a: #d9412b;" aria-hidden="true"></div></div>
                    <div class="fm-paper fm-roll">
                        @foreach ($faqs as $faq)
                            <details name="faq">
                                <summary>
                                    <h3 class="fm-s">{{ $faq['q'] }}</h3>
                                    <i aria-hidden="true"></i>
                                </summary>
                                <p>{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- 13. Opening day: a paper bag, and a label to write on         -->
        <!-- ============================================================ -->
        <section id="claim" class="fm-sec fm-last" style="scroll-margin-top: 4rem;">
            <div class="fm-wrap fm-last-grid">
                <div>
                    <span class="fm-tag" data-reveal>Free to start</span>
                    <h2 class="fm-d fm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Put the whole season up <span class="fm-em">before opening day</span>.
                    </h2>
                    <p class="fm-lede" data-reveal style="--reveal-delay: 0.16s;">
                        The season, the pitch list, the email list and every free RSVP place cost nothing,
                        for as long as you want them. Charging a pitch fee is {{ plan_price($proMonthly) }}
                        a month, and none of what you take at the gate comes to us.
                    </p>
                </div>

                <div class="fm-bagwrap" data-reveal id="fm-bag">
                    <div class="fm-greens" aria-hidden="true">
                        <i style="--x: 4%; --r: -18deg; --h: 7.4rem; --i: 0;"></i>
                        <i style="--x: 17%; --r: -6deg; --h: 8.6rem; --c: #74ad5c; --i: 1;"></i>
                        <i class="is-round" style="--x: 34%; --w: 4.4rem; --c: #d9412b; --i: 2;"></i>
                        <i style="--x: 49%; --r: 8deg; --h: 8rem; --i: 3;"></i>
                        <i class="is-round" style="--x: 60%; --w: 3.8rem; --c: #ee8a2f; --i: 4;"></i>
                        <i style="--x: 74%; --r: 20deg; --h: 7rem; --c: #74ad5c; --i: 5;"></i>
                    </div>
                    <div class="fm-kraftp fm-bag">
                        <div class="fm-paper fm-bag-label">
                            <strong class="fm-d" aria-hidden="true">Saturday Market</strong>
                            <label for="es-claim-input">Your schedule name</label>
                            <div class="fm-bag-form">
                                <div dir="ltr" class="es-claim fm-claim">
                                    <input id="es-claim-input" type="text" placeholder="your-market" autocomplete="off" spellcheck="false" maxlength="30">
                                    <span>.eventschedule.com</span>
                                </div>
                                <a href="{{ app_url('/sign_up?type=venue') }}" class="fm-btn">
                                    Create your market's calendar
                                    {!! $fmArrow !!}
                                </a>
                            </div>
                            <p class="fm-bag-fine">No credit card required</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="fm-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- The finale's confetti, in the market's own colours rather than the site's blues. It
         is thrown from an instance of the cannon made here, not from the library's own:
         that one draws through a worker built from a blob, which the site's content
         security policy refuses without an error, so nothing was ever drawn and a dead
         canvas the size of the screen was left over the page. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var bag = document.getElementById('fm-bag');
            if (!bag || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var colours = ['#3f7d3a', '#6ba84c', '#d9412b', '#ee8a2f', '#f5d76e'];
                    var fire = window.confetti.create(null, { resize: true });
                    [[60, 0.08], [120, 0.92]].forEach(function (shot) {
                        fire({ particleCount: 60, angle: shot[0], spread: 55, startVelocity: 48, origin: { x: shot[1], y: 0.95 }, colors: colours, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.6 });
            io.observe(bag);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
