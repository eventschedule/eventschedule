<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Circus & Acrobatics | Tours, Tickets</x-slot>
    <x-slot name="description">Every circus show, aerial class and festival stop on one link. Zero platform fees on tickets, rigging specs for bookers, and a calendar fans subscribe to.</x-slot>
    <x-slot name="breadcrumbTitle">For Circus & Acrobatics</x-slot>

    <x-slot name="headMeta">
        {{-- The bill's own wood type, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Holtwood One SC') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Ultra') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Rokkitt') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Circus & Acrobatics"
        description="Every circus show, aerial class and festival stop on one link. Zero platform fees on tickets, rigging specs for bookers, and a calendar fans subscribe to."
        audience="Circus & Acrobatic Performers"
        keywords="circus schedule, acrobat show calendar, circus performer booking, circus event management, free circus scheduling, aerial class passes, circus troupe schedule" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How circus performers share their schedule with Event Schedule",
        "description": "Get your performance schedule online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your acts",
                "text": "Shows, workshops, festival appearances. Import from Google Calendar or add manually."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Add to your website, social bios, and booking portfolio. Planners see everything."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Build your following",
                "text": "Fans who sign up with their email get a digest of the dates you add, automatically. You write the newsletter and choose when it goes out."
            }
        ]
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
           For-circus-acrobatics "The Broadside" styles. The page is a
           barn wall of circus bills: wood type in three faces, every
           display line set to the full measure of its sheet, ornaments
           that are glyphs, a big-top valance, a border of chase bulbs
           and tickets with perforated ends.

           Everything is scoped under #ci. A bill carries its own stock
           and inks as custom properties (--stock, --ink, --a1 ...), so
           a navy or red sheet re-inks whatever is printed on it. The
           shared es-* reveal system still drives the entrances.
           ============================================================== */

        @property --ci-turn {
            syntax: '<angle>';
            inherits: false;
            initial-value: 0deg;
        }

        #ci {
            --ci-wall: #6b4e36;
            --ci-seam: rgba(0, 0, 0, 0.26);
            --ci-plank: rgba(255, 255, 255, 0.05);
            --ci-woodtype: 'Holtwood One SC', 'Rockwell Extra Bold', 'Rockwell', 'Roboto Slab', Georgia, serif;
            --ci-fatface: 'Ultra', 'Rockwell Extra Bold', 'Rockwell', 'Roboto Slab', Georgia, serif;
            --ci-slab: 'Rokkitt', 'Rockwell', 'Roboto Slab', 'Courier New', Georgia, serif;
            --ci-roman: Georgia, 'Times New Roman', Times, serif;
            --ci-glyph: 'Apple Symbols', 'Segoe UI Symbol', 'Noto Sans Symbols2', 'Noto Sans Symbols', 'DejaVu Sans', 'Arial Unicode MS', serif;
            /* Paper tooth: dark flecks. Ink wear: a mask that thins the print in specks. */
            --ci-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='g'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.13 0 0 0 0 0.1 0 0 0 0 0.08 0 0 0 -1.3 0.62'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23g)'/%3E%3C/svg%3E");
            --ci-wear: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='260' height='260'%3E%3Cfilter id='w'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.7' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 -3.2 2.5'/%3E%3C/filter%3E%3Crect width='260' height='260' filter='url(%23w)'/%3E%3C/svg%3E");
            position: relative;
            padding-bottom: 1.25rem;
            background-color: var(--ci-wall);
            background-image:
                repeating-linear-gradient(90deg, var(--ci-seam) 0 2px, transparent 2px 8.5rem),
                repeating-linear-gradient(90deg, var(--ci-plank) 0 8.5rem, transparent 8.5rem 17rem, rgba(0, 0, 0, 0.07) 17rem 25.5rem),
                var(--ci-grain);
            color: #221a14;
            font-family: var(--ci-roman);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        /* By night the wall is the dark outside the tent, with a few stars in it. */
        .dark #ci {
            --ci-wall: #070b16;
            --ci-seam: rgba(255, 255, 255, 0.035);
            --ci-plank: rgba(255, 255, 255, 0.012);
            background-image:
                radial-gradient(1.5px 1.5px at 12% 22%, rgba(255, 236, 190, 0.8) 50%, transparent 52%),
                radial-gradient(1px 1px at 71% 64%, rgba(255, 236, 190, 0.7) 50%, transparent 52%),
                radial-gradient(1.5px 1.5px at 88% 14%, rgba(255, 236, 190, 0.6) 50%, transparent 52%),
                radial-gradient(1px 1px at 34% 81%, rgba(255, 236, 190, 0.7) 50%, transparent 52%),
                repeating-linear-gradient(90deg, var(--ci-seam) 0 2px, transparent 2px 8.5rem);
            background-size: 23rem 19rem, 17rem 29rem, 31rem 23rem, 27rem 17rem, auto;
        }

        /* The bar above takes the cream of the stock by day and the night outside after dark. */
        body > header.sticky {
            background-color: rgba(244, 230, 200, 0.92);
            border-bottom-color: rgba(34, 26, 20, 0.28);
        }
        .dark body > header.sticky {
            background-color: rgba(7, 11, 22, 0.9);
            border-bottom-color: rgba(226, 167, 46, 0.25);
        }

        #ci ::selection { background: #c62828; color: #fff; }

        /* ---------------------------------------------------------------
           A bill: one sheet of stock, a thick and thin printed frame
           --------------------------------------------------------------- */
        .ci-bill {
            --stock: #f4e6c8;
            --ink: #221a14;
            --ink-2: #4a3a2c;
            --a1: #c62828;
            --a1-ink: #a81f1f;
            --a2: #1c2a4a;
            --frame: #221a14;
            --panel: #fbf3df;
            --ray: rgba(198, 40, 40, 0.13);
            --focus: #1c2a4a;
            --tab: #c62828;
            --btn: #c62828;
            --btn-hover: #1c2a4a;
            position: relative;
            width: min(100% - 1.2rem, 66rem);
            margin-inline: auto;
            padding: clamp(2.2rem, 6vw, 4.25rem) clamp(1.15rem, 5vw, 4rem);
            background-color: var(--stock);
            background-image: var(--ci-grain);
            color: var(--ink);
            border: 3px solid var(--frame);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.35), 0 1.5rem 2.4rem -1.5rem rgba(0, 0, 0, 0.7);
            text-align: center;
        }
        .ci-bill::before {
            content: "";
            position: absolute;
            inset: 0.45rem;
            border: 1px solid var(--frame);
            pointer-events: none;
        }
        .ci-bill.ci-straw { --stock: #efd58c; --a1: #a81f1f; --panel: #f8e9b6; }
        .ci-bill.ci-navy {
            --stock: #1c2a4a; --ink: #f4e6c8; --ink-2: #dccfae; --a1: #f0b93c; --a1-ink: #f0b93c; --a2: #f4e6c8;
            --frame: #e2a72e; --panel: #25365e; --ray: rgba(240, 185, 60, 0.1); --focus: #f0b93c;
        }
        .dark .ci-bill {
            --stock: #101a33; --ink: #f4e6c8; --ink-2: #d9cba9; --a1: #ff7a66; --a1-ink: #ff9483; --a2: #f0b93c;
            --frame: #c9962a; --panel: #17254c; --ray: rgba(240, 185, 60, 0.085); --focus: #f0b93c;
            --btn-hover: #e2a72e;
        }
        .dark .ci-bill.ci-straw { --stock: #15244b; --panel: #1d2f5e; }
        /* After dark the navy sheet is the one bill left under a lamp. */
        .dark .ci-bill.ci-navy {
            --stock: #f1e3c3; --ink: #221a14; --ink-2: #4a3a2c; --a1: #c62828; --a1-ink: #a81f1f; --a2: #1c2a4a;
            --frame: #221a14; --panel: #fbf3df; --ray: rgba(198, 40, 40, 0.13); --focus: #1c2a4a; --btn-hover: #1c2a4a;
        }
        .ci-bill.ci-red,
        .dark .ci-bill.ci-red {
            --stock: #b71f1f; --ink: #f8ecd2; --ink-2: #f8ecd2; --a1: #f8ecd2; --a1-ink: #f8ecd2; --a2: #f8ecd2;
            --frame: #f8ecd2; --panel: #a01919; --ray: rgba(248, 236, 210, 0.12); --focus: #f8ecd2;
            --tab: #1c2a4a; --btn: #1c2a4a; --btn-hover: #0f1a33;
        }
        .ci-row { padding-block: clamp(0.45rem, 1.4vw, 0.85rem); }

        #ci a:focus-visible,
        #ci summary:focus-visible,
        #ci input:focus-visible {
            outline: 3px solid var(--focus, #f0b93c);
            outline-offset: 3px;
        }

        /* ---------------------------------------------------------------
           The type case. A display line is set to the measure: its size
           is --fit hundredths of the sheet's width, one figure per line.
           --------------------------------------------------------------- */
        .ci-stack { container-type: inline-size; }
        .ci-stack > * + * { margin-top: 0.5rem; }
        .ci-fit {
            display: block;
            white-space: nowrap;
            text-align: center;
            font-size: clamp(1.5rem, 7vw, 4rem);
            font-size: min(calc(var(--fit, 8) * 1cqi), var(--cap, 40rem));
        }
        .ci-wood { font-family: var(--ci-woodtype); font-weight: 400; line-height: 1; }
        .ci-fat { font-family: var(--ci-fatface); font-weight: 400; line-height: 1.1; }
        .ci-caps { font-family: var(--ci-slab); font-weight: 700; text-transform: uppercase; letter-spacing: 0.11em; line-height: 1.1; }
        .ci-wear { -webkit-mask-image: var(--ci-wear); mask-image: var(--ci-wear); }
        .ci-a1 { color: var(--a1); }
        .ci-a2 { color: var(--a2); }
        @container (max-width: 30rem) {
            .ci-fit.ci-long { white-space: normal; font-size: 1.05rem; line-height: 1.3; letter-spacing: 0.08em; text-wrap: balance; }
        }

        /* Rules of ornaments: glyphs between a thick and a thin line. */
        .ci-orn {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-block: 1rem;
            color: var(--frame);
        }
        .ci-orn::before,
        .ci-orn::after { content: ""; flex: 1; height: 7px; border-top: 3px solid currentColor; border-bottom: 1px solid currentColor; }
        .ci-orn i {
            font-family: var(--ci-glyph);
            font-style: normal;
            font-variant-emoji: text;
            font-size: 1.35rem;
            line-height: 1;
            color: var(--a1);
            white-space: nowrap;
        }
        .ci-orn i::before { content: var(--g, "\2605\2002\2766\2002\2605"); }

        /* Ribbon banners, cut to a swallowtail at both ends. */
        .ci-ribbon {
            display: inline-block;
            padding: 0.55em 2.1em 0.42em;
            background: var(--tab);
            color: #f8ecd2;
            font-family: var(--ci-slab);
            font-weight: 700;
            font-size: clamp(0.8rem, 2.9vw, 1.05rem);
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.25;
            text-wrap: balance;
            clip-path: polygon(0 0, 100% 0, calc(100% - 0.8em) 50%, 100% 100%, 0 100%, 0.8em 50%);
        }
        @media (max-width: 640px) { .ci-ribbon { padding-inline: 1.6em; letter-spacing: 0.11em; } }
        @supports (animation-timeline: view()) {
            html.es-anim #ci .ci-ribbon {
                animation: ci-unfurl linear both;
                animation-timeline: view();
                animation-range: entry 0% entry 90%;
            }
        }
        @keyframes ci-unfurl {
            from { clip-path: polygon(50% 0, 50% 0, 50% 50%, 50% 100%, 50% 100%, 50% 50%); }
            to { clip-path: polygon(0 0, 100% 0, calc(100% - 0.8em) 50%, 100% 100%, 0 100%, 0.8em 50%); }
        }

        .ci-h2 { margin-top: 1.4rem; }
        .ci-sub {
            max-width: 40rem;
            margin: 1.3rem auto 0;
            font-style: italic;
            font-size: 1.15rem;
            color: var(--ink-2);
            text-wrap: balance;
        }
        .ci-lead { font-family: var(--ci-slab); font-size: 1.35rem; line-height: 1.35; text-wrap: balance; }
        .ci-bill p a:not(.ci-btn),
        .ci-hand {
            color: var(--a1-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 0.18em;
        }
        .ci-bill p a:not(.ci-btn):hover { text-decoration-style: double; }

        /* The pointing hand, used the way a job printer used it. */
        .ci-hand {
            display: inline-flex;
            align-items: center;
            gap: 0.6em;
            font-family: var(--ci-slab);
            font-size: 1.15rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .ci-hand::before,
        .ci-btn i,
        .ci-pointed::before {
            content: "\261B";
            font-family: var(--ci-glyph);
            font-style: normal;
            font-variant-emoji: text;
            font-weight: 400;
            text-decoration: none;
            display: inline-block;
            transition: translate 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .ci-btn i { content: none; font-size: 1.35em; line-height: 0.7; }
        .ci-btn i::before { content: "\261B"; }
        .ci-hand:hover::before { translate: 0.35em 0; }

        /* Buttons: a painted sign with its corners clipped. */
        .ci-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1.05rem 1.9rem 0.9rem;
            background: var(--btn);
            color: #f8ecd2;
            font-family: var(--ci-slab);
            font-weight: 700;
            font-size: clamp(1.02rem, 3.6vw, 1.2rem);
            letter-spacing: clamp(0.06em, 0.5vw, 0.12em);
            text-transform: uppercase;
            line-height: 1.1;
            text-align: center;
            clip-path: polygon(0.6rem 0, calc(100% - 0.6rem) 0, 100% 0.6rem, 100% calc(100% - 0.6rem), calc(100% - 0.6rem) 100%, 0.6rem 100%, 0 calc(100% - 0.6rem), 0 0.6rem);
            box-shadow: inset 0 0 0 5px var(--btn), inset 0 0 0 6.5px rgba(248, 236, 210, 0.75);
            transition: background-color 0.2s ease, box-shadow 0.2s ease, translate 0.2s ease;
        }
        .ci-btn:hover { --btn: var(--btn-hover); translate: 0 -2px; }
        .dark .ci-bill:not(.ci-red):not(.ci-navy) .ci-btn:hover { color: #221a14; }
        .ci-btn:hover i { translate: 0.35em 0; }
        #ci .ci-btn:focus-visible { outline: 3px solid #f8ecd2; outline-offset: -6px; }
        .ci-btn-line {
            clip-path: none;
            background: transparent;
            color: var(--ink);
            border: 4px double var(--ink);
            box-shadow: none;
            padding: 0.85rem 1.7rem 0.7rem;
        }
        .ci-btn-line:hover { background: var(--ink); color: var(--stock); translate: 0 -2px; }
        #ci .ci-btn-line:focus-visible { outline: 3px solid var(--focus); outline-offset: 3px; }

        /* ---------------------------------------------------------------
           The valance: striped canvas with a scalloped hem
           --------------------------------------------------------------- */
        .ci-valance-wrap { position: relative; z-index: 2; filter: drop-shadow(0 5px 5px rgba(0, 0, 0, 0.4)); }
        .ci-valance {
            --r: 1.25rem;
            height: 3.5rem;
            background:
                linear-gradient(#1c2a4a, #1c2a4a) top / 100% 0.55rem no-repeat,
                linear-gradient(#e2a72e, #e2a72e) 0 0.55rem / 100% 0.2rem no-repeat,
                linear-gradient(90deg, #c62828 50%, #f4e6c8 50%) calc(50% + var(--r)) 0 / calc(var(--r) * 4) 100%;
            -webkit-mask:
                linear-gradient(#000 0 0) top / 100% calc(100% - var(--r)) no-repeat,
                radial-gradient(circle var(--r) at 50% 0, #000 97%, transparent 100%) 50% 100% / calc(var(--r) * 2) var(--r) repeat-x;
            mask:
                linear-gradient(#000 0 0) top / 100% calc(100% - var(--r)) no-repeat,
                radial-gradient(circle var(--r) at 50% 0, #000 97%, transparent 100%) 50% 100% / calc(var(--r) * 2) var(--r) repeat-x;
        }
        .dark .ci-valance {
            background:
                linear-gradient(#0f1a33, #0f1a33) top / 100% 0.55rem no-repeat,
                linear-gradient(#e2a72e, #e2a72e) 0 0.55rem / 100% 0.2rem no-repeat,
                linear-gradient(90deg, #a81f1f 50%, #e6d6b2 50%) calc(50% + var(--r)) 0 / calc(var(--r) * 4) 100%;
        }

        /* ---------------------------------------------------------------
           Chase bulbs: a running light round the bills that matter
           --------------------------------------------------------------- */
        .ci-bulbs {
            position: relative;
            width: min(100% - 1.2rem, 66rem);
            margin-inline: auto;
            padding: 22px;
            background:
                radial-gradient(circle at 11px 11px, #e2a72e 0 5px, transparent 5.6px),
                radial-gradient(circle at calc(100% - 11px) 11px, #e2a72e 0 5px, transparent 5.6px),
                radial-gradient(circle at 11px calc(100% - 11px), #e2a72e 0 5px, transparent 5.6px),
                radial-gradient(circle at calc(100% - 11px) calc(100% - 11px), #e2a72e 0 5px, transparent 5.6px),
                #141f3b;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.35), 0 1.5rem 2.4rem -1.5rem rgba(0, 0, 0, 0.7);
        }
        .dark .ci-bulbs { background-color: #0c1427; box-shadow: 0 0 0 1px rgba(226, 167, 46, 0.3), 0 0 3.5rem -0.5rem rgba(240, 185, 60, 0.3); }
        .ci-bulbs > .ci-bill { width: auto; margin: 0; box-shadow: none; }
        .ci-chase {
            --on: #fff6d2;
            --mid: #e2a72e;
            --off: #7c5a18;
            --b1: var(--on);
            --b2: var(--off);
            --b3: var(--mid);
            position: absolute;
            inset: 0;
            pointer-events: none;
            animation: ci-chase 1.05s steps(1) infinite;
        }
        @keyframes ci-chase {
            0% { --b1: var(--on); --b2: var(--off); --b3: var(--mid); }
            33.33% { --b1: var(--mid); --b2: var(--on); --b3: var(--off); }
            66.66% { --b1: var(--off); --b2: var(--mid); --b3: var(--on); }
        }
        .ci-chase i { position: absolute; filter: drop-shadow(0 0 4px rgba(255, 214, 120, 0.55)); }
        .dark .ci-chase i { filter: drop-shadow(0 0 6px rgba(255, 214, 120, 0.9)); }
        .ci-chase i:nth-child(1),
        .ci-chase i:nth-child(3) { left: 22px; right: 22px; height: 22px; background-size: 72px 22px; background-repeat: round no-repeat; }
        .ci-chase i:nth-child(2),
        .ci-chase i:nth-child(4) { top: 22px; bottom: 22px; width: 22px; background-size: 22px 72px; background-repeat: no-repeat round; }
        .ci-chase i:nth-child(1) {
            top: 0;
            background-image:
                radial-gradient(circle at 12px 50%, var(--b1) 0 5px, transparent 5.6px),
                radial-gradient(circle at 36px 50%, var(--b2) 0 5px, transparent 5.6px),
                radial-gradient(circle at 60px 50%, var(--b3) 0 5px, transparent 5.6px);
        }
        .ci-chase i:nth-child(2) {
            right: 0;
            background-image:
                radial-gradient(circle at 50% 12px, var(--b1) 0 5px, transparent 5.6px),
                radial-gradient(circle at 50% 36px, var(--b2) 0 5px, transparent 5.6px),
                radial-gradient(circle at 50% 60px, var(--b3) 0 5px, transparent 5.6px);
        }
        .ci-chase i:nth-child(3) {
            bottom: 0;
            background-image:
                radial-gradient(circle at 60px 50%, var(--b1) 0 5px, transparent 5.6px),
                radial-gradient(circle at 36px 50%, var(--b2) 0 5px, transparent 5.6px),
                radial-gradient(circle at 12px 50%, var(--b3) 0 5px, transparent 5.6px);
        }
        .ci-chase i:nth-child(4) {
            left: 0;
            background-image:
                radial-gradient(circle at 50% 60px, var(--b1) 0 5px, transparent 5.6px),
                radial-gradient(circle at 50% 36px, var(--b2) 0 5px, transparent 5.6px),
                radial-gradient(circle at 50% 12px, var(--b3) 0 5px, transparent 5.6px);
        }
        @media (max-width: 640px) {
            .ci-bulbs { padding: 16px; }
            .ci-chase i:nth-child(1),
            .ci-chase i:nth-child(3) { left: 16px; right: 16px; height: 16px; background-size: 54px 16px; }
            .ci-chase i:nth-child(2),
            .ci-chase i:nth-child(4) { top: 16px; bottom: 16px; width: 16px; background-size: 16px 54px; }
            .ci-chase i:nth-child(1) { background-image: radial-gradient(circle at 9px 50%, var(--b1) 0 3.6px, transparent 4.2px), radial-gradient(circle at 27px 50%, var(--b2) 0 3.6px, transparent 4.2px), radial-gradient(circle at 45px 50%, var(--b3) 0 3.6px, transparent 4.2px); }
            .ci-chase i:nth-child(2) { background-image: radial-gradient(circle at 50% 9px, var(--b1) 0 3.6px, transparent 4.2px), radial-gradient(circle at 50% 27px, var(--b2) 0 3.6px, transparent 4.2px), radial-gradient(circle at 50% 45px, var(--b3) 0 3.6px, transparent 4.2px); }
            .ci-chase i:nth-child(3) { background-image: radial-gradient(circle at 45px 50%, var(--b1) 0 3.6px, transparent 4.2px), radial-gradient(circle at 27px 50%, var(--b2) 0 3.6px, transparent 4.2px), radial-gradient(circle at 9px 50%, var(--b3) 0 3.6px, transparent 4.2px); }
            .ci-chase i:nth-child(4) { background-image: radial-gradient(circle at 50% 45px, var(--b1) 0 3.6px, transparent 4.2px), radial-gradient(circle at 50% 27px, var(--b2) 0 3.6px, transparent 4.2px), radial-gradient(circle at 50% 9px, var(--b3) 0 3.6px, transparent 4.2px); }
            .ci-bulbs { background: radial-gradient(circle at 8px 8px, #e2a72e 0 3.6px, transparent 4.2px), radial-gradient(circle at calc(100% - 8px) 8px, #e2a72e 0 3.6px, transparent 4.2px), radial-gradient(circle at 8px calc(100% - 8px), #e2a72e 0 3.6px, transparent 4.2px), radial-gradient(circle at calc(100% - 8px) calc(100% - 8px), #e2a72e 0 3.6px, transparent 4.2px), #141f3b; }
            .dark .ci-bulbs { background-color: #0c1427; }
        }

        /* ---------------------------------------------------------------
           The sunburst: rays turning one ray's width, for ever
           --------------------------------------------------------------- */
        .ci-burst {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            -webkit-mask-image: radial-gradient(ellipse 78% 66% at 50% var(--at, 34%), #000 8%, transparent 100%);
            mask-image: radial-gradient(ellipse 78% 66% at 50% var(--at, 34%), #000 8%, transparent 100%);
        }
        .ci-burst::before {
            content: "";
            position: absolute;
            left: 50%;
            top: var(--at, 34%);
            width: 200%;
            aspect-ratio: 1;
            margin: -100% 0 0 -100%;
            background: repeating-conic-gradient(var(--ray) 0 5deg, transparent 5deg 10deg);
            animation: ci-rays 8s linear infinite;
        }
        @keyframes ci-rays { to { rotate: 10deg; } }
        .ci-over { position: relative; }

        /* ---------------------------------------------------------------
           The first bill
           --------------------------------------------------------------- */
        .ci-hero { padding-top: 1.6rem; }
        .ci-hero-bill { overflow: hidden; padding-bottom: 0; }
        #ci .es-hero-eyebrow { margin-bottom: 1.5rem; }
        .ci-h1 > .ci-fit { padding-bottom: 0.06em; }
        .ci-lede { margin-top: 0.4rem; }
        .ci-lede > * + * { margin-top: 0.55rem; }
        .ci-starred::before,
        .ci-starred::after {
            content: "\2605";
            font-family: var(--ci-glyph);
            font-variant-emoji: text;
            font-size: 0.42em;
            vertical-align: 0.5em;
            margin-inline: 0.7em;
            color: var(--a2);
        }
        .ci-lede-tail { display: block; padding-top: 0.5rem; font-style: italic; font-size: 1.2rem; color: var(--ink-2); text-wrap: balance; }
        .ci-cta { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 1rem 1.25rem; margin-top: 2.1rem; }
        .ci-types {
            margin: clamp(2rem, 5vw, 3rem) calc(clamp(1.15rem, 5vw, 4rem) * -1) 0;
            padding-block: 0.85rem 0.7rem;
            border-top: 3px double var(--frame);
            background: var(--a2);
            color: var(--stock);
        }
        .ci-types .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .ci-type { display: inline-flex; align-items: center; font-family: var(--ci-slab); font-weight: 700; font-size: 1.4rem; letter-spacing: 0.14em; text-transform: uppercase; white-space: nowrap; }
        .ci-type::after { content: "\2605"; font-family: var(--ci-glyph); font-variant-emoji: text; font-size: 0.8em; margin-inline: 1.1em; color: #e2a72e; }
        .dark .ci-types { color: #101a33; }

        /* ---------------------------------------------------------------
           Six acts: a wall of bills, two abreast
           --------------------------------------------------------------- */
        .ci-acts {
            display: grid;
            gap: clamp(0.9rem, 2.8vw, 1.7rem);
            width: min(100% - 1.2rem, 66rem);
            margin: clamp(0.9rem, 2.8vw, 1.7rem) auto 0;
            align-items: start;
        }
        @media (min-width: 880px) {
            .ci-acts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            /* Pasted up by hand, so none of them hangs quite true until you straighten it. */
            .ci-act { transition: rotate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1); }
            .ci-act:nth-child(odd) { rotate: -0.5deg; }
            .ci-act:nth-child(even) { rotate: 0.45deg; }
            .ci-act:nth-child(3n) { rotate: 0.3deg; }
            .ci-act:hover { rotate: 0deg; translate: 0 -0.3rem; }
        }
        .ci-act { width: auto; margin: 0; padding: clamp(1.9rem, 4vw, 2.75rem) clamp(1.15rem, 3.4vw, 2.5rem) clamp(1.7rem, 3.5vw, 2.4rem); }
        .ci-act-call { font-style: italic; font-size: 1.05rem; color: var(--ink-2); }
        .ci-act-no {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            margin-top: 0.35rem;
            font-family: var(--ci-slab);
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            line-height: 1;
        }
        .ci-act-no::before,
        .ci-act-no::after { content: ""; flex: 1; max-width: 5.5rem; height: 7px; border-top: 3px solid var(--frame); border-bottom: 1px solid var(--frame); }
        .ci-act-no b { font-family: var(--ci-fatface); font-weight: 400; font-size: 1.9rem; letter-spacing: 0.04em; color: var(--a1); }
        .ci-act-kind { margin-top: 0.75rem; font-family: var(--ci-slab); font-weight: 700; font-size: 0.98rem; letter-spacing: 0.24em; text-transform: uppercase; color: var(--a1-ink); }
        .ci-act-title { margin-top: 0.9rem; }
        .ci-act-title > * + * { margin-top: 0.3rem; }
        .ci-act .ci-orn { margin-block: 0.9rem 1rem; }
        .ci-act-more { margin-top: 0.9rem; font-size: 1rem; color: var(--ink-2); text-wrap: pretty; }
        .ci-cut { margin-top: 1.7rem; }

        /* The printed matter pinned to a bill is always paper, whatever the hour. */
        .ci-slip {
            position: relative;
            padding: 1.05rem 1.2rem 0.95rem;
            background-color: #fbf3df;
            background-image: var(--ci-grain);
            color: #221a14;
            border: 2px solid #221a14;
            font-family: var(--ci-slab);
            font-size: 1.1rem;
            line-height: 1.25;
            text-align: start;
            rotate: var(--r, 0deg);
        }
        .ci-slip-head { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.5rem; border-bottom: 4px double #221a14; font-weight: 700; font-size: 0.95rem; letter-spacing: 0.13em; text-transform: uppercase; }
        .ci-slip-foot { margin-top: 0.75rem; font-weight: 700; font-size: 0.92rem; letter-spacing: 0.11em; text-transform: uppercase; text-align: center; }
        .ci-pointed::before { margin-inline-end: 0.55em; color: #a81f1f; }
        .ci-perf {
            --hole: 0.36rem;
            border-inline-width: 0;
            padding-inline: 1.5rem;
            -webkit-mask:
                radial-gradient(circle at 0 50%, transparent 0 var(--hole), #000 calc(var(--hole) + 0.5px)) 0 0 / 51% 1.05rem repeat-y,
                radial-gradient(circle at 100% 50%, transparent 0 var(--hole), #000 calc(var(--hole) + 0.5px)) 100% 0 / 51% 1.05rem repeat-y;
            mask:
                radial-gradient(circle at 0 50%, transparent 0 var(--hole), #000 calc(var(--hole) + 0.5px)) 0 0 / 51% 1.05rem repeat-y,
                radial-gradient(circle at 100% 50%, transparent 0 var(--hole), #000 calc(var(--hole) + 0.5px)) 100% 0 / 51% 1.05rem repeat-y;
        }

        /* Act I: the route card */
        /* A flex row, not a grid: Safari keeps an auto grid track at the width the fallback face
           gave it before the fat face arrived, and the date then ran into the name beside it. */
        .ci-route-row { display: flex; align-items: baseline; gap: 0.8rem; padding-block: 0.55rem 0.45rem; border-bottom: 1px dotted rgba(34, 26, 20, 0.6); }
        .ci-route-row b { flex: none; font-family: var(--ci-fatface); font-weight: 400; font-size: 0.86rem; color: #a81f1f; white-space: nowrap; }
        .ci-route-row span { flex: 1 1 0; min-width: 0; font-weight: 700; font-size: 1.22rem; }
        .ci-route-row em { flex: none; font-family: var(--ci-roman); font-size: 0.9rem; color: #4a3a2c; }

        /* Act II: the rider */
        .ci-rider dl > div { display: flex; align-items: baseline; gap: 0.5rem; padding-top: 0.55rem; }
        .ci-rider dt { display: flex; flex: 1; align-items: baseline; gap: 0.5rem; min-width: 0; }
        .ci-rider dt::after { content: ""; flex: 1; min-width: 1rem; border-bottom: 2px dotted rgba(34, 26, 20, 0.55); translate: 0 -0.22em; }
        .ci-rider dd { font-weight: 700; white-space: nowrap; }

        /* Act III: the class card, punched as it is used */
        .ci-pass { text-align: center; }
        .ci-pass-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; }
        .ci-pass-top span:first-child { font-family: var(--ci-fatface); font-size: 1.2rem; text-align: start; }
        .ci-pass-top span:last-child { font-family: var(--ci-woodtype); font-size: 1.35rem; color: #a81f1f; }
        .ci-pass-kind { margin-top: 0.3rem; font-weight: 700; font-size: 0.9rem; letter-spacing: 0.2em; text-transform: uppercase; text-align: start; }
        .ci-punch { display: flex; justify-content: center; gap: clamp(0.25rem, 1.2vw, 0.5rem); margin-block: 1rem 0.7rem; }
        .ci-punch i { flex: 0 1 1.5rem; min-width: 0; aspect-ratio: 1; border-radius: 50%; border: 2px solid #221a14; }
        .ci-punch i.is-used { background: var(--stock); border-color: rgba(34, 26, 20, 0.45); box-shadow: inset 0 2px 3px rgba(0, 0, 0, 0.5); }
        .ci-pass-used { font-family: var(--ci-roman); font-style: italic; font-size: 0.95rem; color: #4a3a2c; }

        /* Act IV: the company */
        .ci-company-row { display: grid; grid-template-columns: 2.7rem minmax(0, 1fr) auto; align-items: center; gap: 0.85rem; padding-block: 0.6rem; border-bottom: 1px dotted rgba(34, 26, 20, 0.6); }
        .ci-company-row > b { display: grid; place-items: center; width: 2.7rem; aspect-ratio: 1; border-radius: 50%; background: #1c2a4a; color: #f4e6c8; font-family: var(--ci-fatface); font-weight: 400; }
        .ci-company-row strong { display: block; font-family: var(--ci-fatface); font-weight: 400; font-size: 1.15rem; line-height: 1.2; }
        .ci-company-row small { font-family: var(--ci-roman); font-style: italic; font-size: 0.95rem; color: #4a3a2c; }
        .ci-company-row > i { font-style: normal; font-family: var(--ci-glyph); font-variant-emoji: text; color: #a81f1f; }
        .ci-company-row > i::before { content: "\2605"; }

        /* Act V: two admissions and what the house takes */
        .ci-tix-title { font-family: var(--ci-fatface); font-size: 1.35rem; line-height: 1.2; }
        .ci-tix { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem; margin-top: 1rem; }
        .ci-tick { padding-block: 0.95rem 0.8rem; text-align: center; }
        .ci-tick small { display: block; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.3em; text-transform: uppercase; color: #4a3a2c; }
        .ci-tick strong { display: block; margin-top: 0.25rem; font-weight: 700; font-size: 1.1rem; line-height: 1.15; }
        .ci-tick b { display: block; font-family: var(--ci-woodtype); font-weight: 400; font-size: 2.1rem; line-height: 1.2; color: #a81f1f; }
        .ci-tick em { display: block; font-style: normal; font-size: 0.7rem; letter-spacing: 0.24em; color: #4a3a2c; }
        .ci-fee { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-top: 1.1rem; padding: 0.65rem 0.95rem 0.5rem; border: 2px dashed var(--frame); font-family: var(--ci-slab); font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
        .ci-fee span:last-child { font-family: var(--ci-woodtype); font-weight: 400; font-size: 1.3rem; letter-spacing: 0; color: var(--a1); }
        .ci-cut-foot { margin-top: 0.85rem; font-family: var(--ci-slab); font-weight: 700; font-size: 0.92rem; letter-spacing: 0.11em; text-transform: uppercase; }
        .ci-cut-foot.ci-pointed::before { color: var(--a1); }

        /* Act VI: the planner's card */
        .ci-kit-url { padding: 0.75rem 0.8rem 0.6rem; border: 2px solid #221a14; font-weight: 700; font-size: clamp(0.95rem, 3.4vw, 1.2rem); text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .ci-kit-tabs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.4rem; margin-block: 0.75rem; }
        .ci-kit-tabs span { padding: 0.45rem 0.15rem 0.3rem; background: #1c2a4a; color: #f4e6c8; font-weight: 700; font-size: clamp(0.62rem, 2.2vw, 0.8rem); letter-spacing: 0.1em; text-transform: uppercase; text-align: center; }
        .ci-kit-btn { padding: 0.75rem 0.5rem 0.6rem; background: #a81f1f; color: #f8ecd2; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; text-align: center; clip-path: polygon(0.5rem 0, calc(100% - 0.5rem) 0, 100% 0.5rem, 100% calc(100% - 0.5rem), calc(100% - 0.5rem) 100%, 0.5rem 100%, 0 calc(100% - 0.5rem), 0 0.5rem); }

        /* ---------------------------------------------------------------
           Three rings, each with a follow spot going round its curb
           --------------------------------------------------------------- */
        .ci-rings { display: grid; justify-items: center; gap: 1.75rem 1rem; margin-top: clamp(2rem, 5vw, 3rem); }
        @media (min-width: 900px) { .ci-rings { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ci-ring {
            position: relative;
            display: grid;
            place-items: center;
            width: min(100%, 18.5rem);
            aspect-ratio: 1;
            border-radius: 50%;
            border: 0.6rem solid var(--a1);
            background: radial-gradient(circle, rgba(226, 167, 46, 0.3) 0%, rgba(226, 167, 46, 0.1) 55%, transparent 72%);
            box-shadow: 0 0 0 0.2rem var(--stock), 0 0 0 0.34rem var(--frame), inset 0 0 0 0.2rem var(--stock), inset 0 0 0 0.3rem var(--frame);
        }
        .ci-ring::after {
            content: "";
            position: absolute;
            inset: -0.6rem;
            border-radius: 50%;
            background: conic-gradient(from var(--ci-turn), transparent 0 290deg, rgba(255, 240, 190, 0.95) 335deg, transparent 360deg);
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 0.6rem), #000 calc(100% - 0.6rem + 0.5px));
            mask: radial-gradient(farthest-side, transparent calc(100% - 0.6rem), #000 calc(100% - 0.6rem + 0.5px));
            animation: ci-spot 5s linear infinite;
            animation-delay: var(--d, 0s);
            pointer-events: none;
        }
        @keyframes ci-spot { to { --ci-turn: 360deg; } }
        .ci-ring-in { padding: 0 2.1rem; }
        .ci-ring-no { font-family: var(--ci-slab); font-weight: 700; font-size: 0.95rem; letter-spacing: 0.26em; text-transform: uppercase; color: var(--a1-ink); }
        .ci-ring-no::before,
        .ci-ring-no::after { content: "\2605"; font-family: var(--ci-glyph); font-variant-emoji: text; margin-inline: 0.6em; font-size: 0.8em; }
        .ci-ring h3 { margin-top: 0.45rem; font-family: var(--ci-fatface); font-weight: 400; font-size: 1.4rem; line-height: 1.15; text-wrap: balance; }
        .ci-ring p { margin-top: 0.55rem; font-size: 0.95rem; line-height: 1.45; color: var(--ink-2); text-wrap: balance; }
        .ci-after { margin-top: clamp(1.75rem, 4vw, 2.5rem); }

        /* ---------------------------------------------------------------
           The ring goes online: a wire, and three things it says
           --------------------------------------------------------------- */
        .ci-wire { max-width: 36rem; margin: clamp(1.9rem, 4.5vw, 2.75rem) auto 0; --r: -0.7deg; }
        .ci-wire-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 4px double #221a14; }
        .ci-wire-top strong { font-family: var(--ci-fatface); font-weight: 400; font-size: 1.25rem; line-height: 1.2; }
        .ci-wire-stamp { flex: none; padding: 0.35rem 0.85rem 0.2rem; border: 2.5px solid #a81f1f; border-radius: 999px; color: #a81f1f; font-weight: 700; font-size: 0.9rem; letter-spacing: 0.18em; text-transform: uppercase; rotate: -6deg; }
        .ci-wire-label { margin-top: 0.9rem; font-weight: 700; font-size: 0.82rem; letter-spacing: 0.2em; text-transform: uppercase; color: #4a3a2c; }
        .ci-wire-url { margin-top: 0.3rem; padding: 0.55rem 0.7rem; background: rgba(34, 26, 20, 0.07); border-bottom: 2px solid #221a14; font-family: ui-monospace, 'SF Mono', Menlo, Consolas, 'Courier New', monospace; font-size: clamp(0.74rem, 3.2vw, 0.98rem); overflow-wrap: anywhere; }
        .ci-wire-note { margin-top: 0.55rem; font-family: var(--ci-roman); font-style: italic; font-size: 0.93rem; line-height: 1.45; color: #4a3a2c; }
        .ci-wire-rows { margin-top: 0.9rem; border-top: 1px dotted rgba(34, 26, 20, 0.6); }
        .ci-wire-rows > div { display: grid; grid-template-columns: 4.6rem minmax(0, 1fr); gap: 0.8rem; align-items: baseline; padding-block: 0.55rem 0.45rem; border-bottom: 1px dotted rgba(34, 26, 20, 0.6); }
        .ci-wire-rows > div > span:first-child { font-weight: 700; font-size: 0.82rem; letter-spacing: 0.2em; text-transform: uppercase; color: #a81f1f; }
        .ci-wire-rows span span { font-family: var(--ci-roman); font-style: italic; font-size: 0.93rem; color: #4a3a2c; }
        .ci-wire-fee { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-top: 0.95rem; padding: 0.65rem 0.85rem 0.5rem; background: #1c2a4a; color: #f4e6c8; font-weight: 700; font-size: 0.95rem; letter-spacing: 0.12em; text-transform: uppercase; }
        .ci-wire-fee span:last-child { font-family: var(--ci-woodtype); font-weight: 400; font-size: 1.25rem; letter-spacing: 0; color: #f0b93c; }
        .ci-boons { display: grid; gap: 2rem 1.75rem; margin-top: clamp(2.2rem, 5vw, 3.2rem); }
        @media (min-width: 860px) { .ci-boons { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ci-boon::before { content: "\261B"; display: block; font-family: var(--ci-glyph); font-variant-emoji: text; font-size: 2.9rem; line-height: 1; color: var(--a1); rotate: 90deg; }
        .ci-boon h3 { margin-top: 0.7rem; font-family: var(--ci-fatface); font-weight: 400; font-size: 1.3rem; line-height: 1.2; text-wrap: balance; }
        .ci-boon p { margin-top: 0.55rem; color: var(--ink-2); text-wrap: pretty; }

        /* ---------------------------------------------------------------
           On every route: six stands, set three lines deep
           --------------------------------------------------------------- */
        .ci-stands { margin-top: clamp(1.6rem, 4vw, 2.4rem); }
        .ci-stands > * + * { margin-top: 0.7rem; }
        .ci-stand-row h3 { display: inline; font: inherit; }
        .ci-stand-row > span { font-family: var(--ci-glyph); font-variant-emoji: text; font-size: 0.5em; vertical-align: 0.42em; margin-inline: 0.7em; color: #f0b93c; }

        /* ---------------------------------------------------------------
           The company: six turns, ruled off like a printed table
           --------------------------------------------------------------- */
        .ci-table { display: grid; gap: 1px; margin-top: clamp(2rem, 5vw, 3rem); background: var(--frame); border: 4px double var(--frame); }
        @media (min-width: 680px) { .ci-table { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 980px) { .ci-table.ci-three { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ci-cell { display: flex; flex-direction: column; padding: 1.7rem 1.4rem 1.5rem; background-color: var(--stock); background-image: var(--ci-grain); }
        .ci-cell-no { font-family: var(--ci-glyph); font-variant-emoji: text; font-size: 1.1rem; line-height: 1; color: var(--a1); }
        .ci-cell h3 { margin-top: 0.6rem; font-size: 1.45rem; line-height: 1.15; text-wrap: balance; }
        .ci-cell:nth-child(odd) h3 { font-family: var(--ci-fatface); font-weight: 400; }
        .ci-cell:nth-child(even) h3 { font-family: var(--ci-woodtype); font-weight: 400; font-size: 1.25rem; color: var(--a1); }
        .ci-cell p { margin-top: 0.65rem; font-size: 1rem; color: var(--ink-2); text-wrap: pretty; }
        .ci-cell .ci-hand { margin-top: auto; padding-top: 1.1rem; align-self: center; font-size: 1rem; }
        a.ci-cell { transition: background-color 0.2s ease, color 0.2s ease; }
        a.ci-cell .ci-cell-no { display: inline-block; font-size: 1.7rem; transition: translate 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
        a.ci-cell:hover { background-color: var(--a2); color: var(--stock); }
        a.ci-cell:hover h3,
        a.ci-cell:hover p { color: inherit; }
        a.ci-cell:hover .ci-cell-no { translate: 0.5rem 0; color: inherit; }
        #ci a.ci-cell:focus-visible { outline-offset: -6px; }

        /* ---------------------------------------------------------------
           Three steps: three seals, turning as the sheet goes by
           --------------------------------------------------------------- */
        .ci-steps { display: grid; gap: 2.75rem 1.5rem; margin-top: clamp(2.2rem, 5vw, 3.2rem); }
        @media (min-width: 860px) { .ci-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ci-seal { position: relative; isolation: isolate; display: grid; place-items: center; width: 9.5rem; aspect-ratio: 1; margin: 0 auto 3.7rem; }
        .ci-seal::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: var(--a1);
            -webkit-mask: radial-gradient(circle, #000 0 60%, transparent 60.6%), repeating-conic-gradient(#000 0 7.5deg, transparent 7.5deg 15deg);
            mask: radial-gradient(circle, #000 0 60%, transparent 60.6%), repeating-conic-gradient(#000 0 7.5deg, transparent 7.5deg 15deg);
        }
        .ci-seal::after {
            content: "";
            position: absolute;
            inset: 21%;
            border-radius: 50%;
            background: var(--stock);
            box-shadow: inset 0 0 0 3px var(--stock), inset 0 0 0 4.5px var(--frame);
        }
        .ci-seal b { position: relative; z-index: 1; font-family: var(--ci-fatface); font-weight: 400; font-size: 2.3rem; line-height: 1; color: var(--a2); }
        .ci-seal i { position: absolute; top: 78%; left: 50%; z-index: -1; width: 2.1rem; height: 4.6rem; margin-left: -1.05rem; background: var(--a1-ink); clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%); transform-origin: 50% 0; rotate: 16deg; }
        .ci-seal i + i { rotate: -16deg; background: var(--a2); }
        @supports (animation-timeline: view()) {
            html.es-anim #ci .ci-seal::before {
                animation: ci-cog linear both;
                animation-timeline: view();
                animation-range: entry 0% exit 100%;
            }
        }
        @keyframes ci-cog { from { rotate: -60deg; } to { rotate: 60deg; } }
        .ci-step h3 { font-family: var(--ci-fatface); font-weight: 400; font-size: 1.5rem; line-height: 1.15; }
        .ci-step p { max-width: 21rem; margin: 0.65rem auto 0; color: var(--ink-2); text-wrap: pretty; }

        /* ---------------------------------------------------------------
           Handbills for the neighbours
           --------------------------------------------------------------- */
        .ci-hands { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem; margin-top: clamp(1.8rem, 4.5vw, 2.6rem); }
        @media (min-width: 900px) { .ci-hands { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.1rem; } }
        .ci-handbill {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.5rem;
            min-height: 9rem;
            padding: 1.2rem 0.8rem 1rem;
            /* The name is sized from the handbill it is printed on (13cqi below), so the longest
               word, "Performers", stays inside the frame when two of them share a phone. */
            container-type: inline-size;
            background-color: var(--panel);
            background-image: var(--ci-grain);
            border: 2px solid var(--frame);
            outline: 1px solid var(--frame);
            outline-offset: -7px;
            rotate: var(--r, 0deg);
            transition: rotate 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), translate 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }
        .ci-handbill:hover { rotate: 0deg; translate: 0 -0.4rem; box-shadow: 0 1.2rem 1.6rem -1rem rgba(0, 0, 0, 0.6); }
        #ci .ci-handbill:focus-visible { outline: 3px solid var(--focus); outline-offset: 3px; }
        .ci-handbill small { font-family: var(--ci-slab); font-weight: 700; font-size: 0.78rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--ink-2); }
        .ci-handbill strong { font-family: var(--ci-fatface); font-weight: 400; font-size: min(clamp(1.15rem, 2.2vw, 1.5rem), 13cqi); line-height: 1.12; color: var(--a1); text-wrap: balance; }
        .ci-handbill:nth-child(even) strong { font-family: var(--ci-woodtype); font-size: clamp(1rem, 1.9vw, 1.25rem); color: var(--a2); }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the print changes.
           --------------------------------------------------------------- */
        #ci .ci-plans > section { padding-block: 0; background: transparent; }
        #ci .ci-plans > section > div { padding-inline: 0; max-width: none; }
        #ci .ci-plans h2 { font-family: var(--ci-fatface); font-weight: 400; font-size: clamp(1.75rem, 4.4vw, 3rem); line-height: 1.12; letter-spacing: 0; color: var(--a1); }
        #ci .ci-plans h2 + p { font-family: var(--ci-roman); font-style: italic; font-size: 1.1rem; color: var(--ink-2); }
        #ci .ci-plans .grid > div { background-color: var(--panel); background-image: var(--ci-grain); border: 2px solid var(--frame); border-radius: 0; text-align: start; color: var(--ink); }
        #ci .ci-plans .grid > div:hover { box-shadow: 0 1.2rem 1.6rem -1rem rgba(0, 0, 0, 0.55); }
        #ci .ci-plans .grid > div:nth-child(2) { outline: 2px solid var(--frame); outline-offset: 3px; }
        #ci .ci-plans .grid > div span,
        #ci .ci-plans .grid > div p,
        #ci .ci-plans .grid > div li { color: var(--ink-2); }
        #ci .ci-plans .grid > div .text-3xl { font-family: var(--ci-woodtype); font-weight: 400; font-size: 2.2rem; letter-spacing: 0; color: var(--ink); }
        #ci .ci-plans .grid > div .uppercase { font-family: var(--ci-slab); font-size: 1.05rem; letter-spacing: 0.2em; color: var(--a1-ink); }
        #ci .ci-plans .grid > div .rounded-full { background: var(--tab); color: #f8ecd2; border-radius: 0; font-family: var(--ci-slab); font-size: 0.72rem; letter-spacing: 0.12em; }
        #ci .ci-plans .grid > div svg { color: var(--a1-ink); }
        #ci .ci-plans a.font-medium { color: var(--a1-ink); font-family: var(--ci-slab); font-weight: 700; font-size: 1.1rem; letter-spacing: 0.08em; text-transform: uppercase; text-decoration: underline; text-decoration-thickness: 2px; text-underline-offset: 0.18em; }
        /* Under about 372px the link broke into two lines and left its arrow alone at the far edge. */
        @media (max-width: 400px) { #ci .ci-plans a.font-medium { font-size: 0.95rem; letter-spacing: 0.04em; } }
        #ci .ci-plans a.rounded-2xl {
            background: var(--btn);
            color: #f8ecd2;
            border-radius: 0;
            font-family: var(--ci-slab);
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            clip-path: polygon(0.6rem 0, calc(100% - 0.6rem) 0, 100% 0.6rem, 100% calc(100% - 0.6rem), calc(100% - 0.6rem) 100%, 0.6rem 100%, 0 calc(100% - 0.6rem), 0 0.6rem);
            box-shadow: inset 0 0 0 5px var(--btn), inset 0 0 0 6.5px rgba(248, 236, 210, 0.75);
        }
        #ci .ci-plans a.rounded-2xl:hover { --btn: var(--btn-hover); transform: translateY(-2px); }
        .dark #ci .ci-plans a.rounded-2xl:hover { color: #221a14; }
        #ci .ci-plans a.rounded-2xl:focus-visible { outline: 3px solid #f8ecd2; outline-offset: -6px; }

        #ci .ci-keep > section { padding-block: 0; background: transparent; border-top: 0; }
        #ci .ci-keep > section > div { padding-inline: 0; max-width: none; text-align: start; }
        #ci .ci-keep h2 { font-family: var(--ci-fatface); font-weight: 400; font-size: clamp(1.7rem, 4vw, 2.6rem); line-height: 1.12; color: var(--ink); }
        #ci .ci-keep p.uppercase { font-family: var(--ci-slab); font-weight: 700; font-size: 1rem; letter-spacing: 0.22em; color: var(--a1-ink); }
        #ci .ci-keep .grid > a { background-color: var(--panel); background-image: var(--ci-grain); border: 2px solid var(--frame); border-radius: 0; }
        #ci .ci-keep .grid > a:hover { border-color: var(--frame); box-shadow: 0 1.2rem 1.6rem -1rem rgba(0, 0, 0, 0.55); }
        #ci .ci-keep .grid > a > span:first-child { display: none; }
        #ci .ci-keep .grid > a h3 { font-family: var(--ci-slab); font-weight: 700; font-size: 1.3rem; line-height: 1.2; color: var(--ink); }
        #ci .ci-keep .grid > a p { color: var(--ink-2); }
        #ci .ci-keep .grid > a > span:last-child,
        #ci .ci-keep a.self-start { color: var(--a1-ink); }

        /* ---------------------------------------------------------------
           Particulars: the questions
           --------------------------------------------------------------- */
        .ci-faq { max-width: 48rem; margin: clamp(2rem, 5vw, 3rem) auto 0; border-top: 4px double var(--frame); text-align: start; }
        .ci-faq details { border-bottom: 1px solid var(--frame); }
        .ci-faq details:last-child { border-bottom: 4px double var(--frame); }
        .ci-faq summary { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: baseline; gap: 0.9rem; padding: 1.15rem 0.3rem 1rem; cursor: pointer; }
        .ci-faq summary::before {
            content: "\261B";
            font-family: var(--ci-glyph);
            font-variant-emoji: text;
            font-size: 1.35rem;
            line-height: 1;
            color: var(--a1);
            transition: rotate 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .ci-faq details[open] summary::before { rotate: 90deg; }
        .ci-faq h3 { font-family: var(--ci-slab); font-weight: 700; font-size: 1.4rem; line-height: 1.2; }
        .ci-faq details p { padding: 0 0.3rem 1.5rem 2.6rem; color: var(--ink-2); }
        @media (max-width: 560px) { .ci-faq details p { padding-inline-start: 0.3rem; } }

        /* ---------------------------------------------------------------
           The ticket window: ADMIT ONE, with your name on it
           --------------------------------------------------------------- */
        .ci-finale { padding-top: clamp(1rem, 3vw, 2rem); }
        .ci-finale-bill { overflow: hidden; --at: 30%; }
        .ci-finale-p { margin-top: 1.3rem; font-family: var(--ci-slab); font-size: 1.45rem; line-height: 1.3; }
        .ci-admit {
            display: grid;
            max-width: 40rem;
            margin: clamp(2rem, 5vw, 3rem) auto 0;
            color: #221a14;
            filter: drop-shadow(0 1.1rem 1.1rem rgba(0, 0, 0, 0.4));
        }
        /* Restated under the page id: the shared reveal ends on `filter: none`, which outranks a
           plain class and took the shadow away as soon as the ticket was revealed. */
        #ci .ci-admit { filter: drop-shadow(0 1.1rem 1.1rem rgba(0, 0, 0, 0.4)); }
        @media (min-width: 640px) { .ci-admit { grid-template-columns: 8.5rem minmax(0, 1fr); } }
        .ci-admit-stub,
        .ci-admit-main { background-color: #f4e6c8; background-image: var(--ci-grain); }
        .ci-admit-stub {
            display: none;
            place-content: center;
            gap: 0.3rem;
            padding: 1rem 0.6rem 1rem 1.4rem;
            border-inline-end: 3px dashed rgba(34, 26, 20, 0.55);
            font-family: var(--ci-slab);
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            font-size: 0.8rem;
            -webkit-mask: radial-gradient(circle at 0 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 0 0 / 100% 1.15rem repeat-y;
            mask: radial-gradient(circle at 0 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 0 0 / 100% 1.15rem repeat-y;
        }
        @media (min-width: 640px) { .ci-admit-stub { display: grid; } }
        .ci-admit-stub b { font-family: var(--ci-fatface); font-weight: 400; font-size: 1.5rem; letter-spacing: 0.02em; color: #a81f1f; }
        .ci-admit-main {
            padding: 1.3rem 1.7rem 1.2rem 1.3rem;
            text-align: start;
            -webkit-mask: radial-gradient(circle at 100% 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 0 0 / 100% 1.15rem repeat-y;
            mask: radial-gradient(circle at 100% 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 0 0 / 100% 1.15rem repeat-y;
        }
        @media (max-width: 639px) {
            .ci-admit-main {
                padding-inline: 1.5rem;
                -webkit-mask:
                    radial-gradient(circle at 0 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 0 0 / 51% 1.15rem repeat-y,
                    radial-gradient(circle at 100% 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 100% 0 / 51% 1.15rem repeat-y;
                mask:
                    radial-gradient(circle at 0 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 0 0 / 51% 1.15rem repeat-y,
                    radial-gradient(circle at 100% 50%, transparent 0 0.4rem, #000 calc(0.4rem + 0.5px)) 100% 0 / 51% 1.15rem repeat-y;
            }
        }
        .ci-admit-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding-bottom: 0.55rem; border-bottom: 4px double #221a14; }
        .ci-admit-top span:first-child { font-family: var(--ci-woodtype); font-size: 1.55rem; line-height: 1; color: #a81f1f; }
        .ci-admit-top span:last-child { font-family: var(--ci-slab); font-weight: 700; font-size: 0.85rem; letter-spacing: 0.2em; text-transform: uppercase; }
        .ci-admit-label { display: block; margin-block: 0.95rem 0.45rem; font-family: var(--ci-slab); font-weight: 700; font-size: 0.95rem; letter-spacing: 0.2em; text-transform: uppercase; color: #4a3a2c; }
        .ci-admit-form { display: grid; gap: 0.85rem; }
        #ci .ci-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 0.9rem;
            border: 2px solid #221a14;
            background: #fffaf0;
            font-family: var(--ci-slab);
            font-weight: 700;
            font-size: clamp(1.05rem, 3.6vw, 1.3rem);
            transition: box-shadow 0.2s ease;
        }
        #ci .ci-claim:focus-within { border-color: #221a14; box-shadow: 0 0 0 4px rgba(28, 42, 74, 0.35); }
        #ci .ci-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: #221a14;
            box-shadow: none;
            outline: none;
        }
        #ci .ci-claim input::placeholder { color: #84735f; }
        .ci-claim span { flex: none; color: #4a3a2c; user-select: none; }
        /* On a phone the box had room for eight letters of an eleven-letter placeholder. The
           ticket takes a little of the bill's margin and of its own, and the type sets at 16px
           (the smallest that does not make iOS zoom the page). */
        @media (max-width: 420px) {
            .ci-admit { margin-inline: -0.3rem; }
            .ci-admit-main { padding-inline: 1.1rem; }
            #ci .ci-claim { padding-inline: 0.6rem; font-size: 1rem; }
        }
        #ci .ci-admit .ci-btn { --btn: #c62828; --btn-hover: #1c2a4a; color: #f8ecd2; }
        .ci-admit-note { margin-top: 0.85rem; font-style: italic; font-size: 0.98rem; color: #4a3a2c; }

        @media (prefers-reduced-motion: reduce) {
            .ci-chase,
            .ci-burst::before,
            .ci-ring::after { animation: none; }
            .ci-btn, .ci-btn i, .ci-hand::before, .ci-handbill, .ci-act, a.ci-cell, a.ci-cell .ci-cell-no, .ci-faq summary::before { transition: none; }
        }
    </style>

    @php
        $ciTypes = ['Aerial', 'Fire', 'Acrobatics', 'Juggling', 'Stilt Walking', 'Contortion', 'Trapeze', 'Hand Balancing'];

        $circusActs = [
            ['num' => 'I', 'eyebrow' => 'Festival Circuit', 'title' => 'Track Your Tour', 'desc' => 'Renaissance faires, Burning Man, Fringe festivals - show fans every stop on your summer circuit.'],
            ['num' => 'II', 'eyebrow' => 'Technical Requirements', 'title' => 'Rigging & Tech Specs', 'desc' => 'Ceiling height, rigging points, weight capacity, floor space - share specs venues actually need.'],
            ['num' => 'III', 'eyebrow' => 'Teaching', 'title' => 'Fill Your Workshops', 'desc' => 'Aerial basics, fire safety, acro fundamentals - share your class schedule with students.'],
            ['num' => 'IV', 'eyebrow' => 'Ensemble', 'title' => 'Coordinate Your Troupe', 'desc' => 'Aerialist, rigger, stage manager - on Enterprise the whole crew edits the same schedule.'],
            ['num' => 'V', 'eyebrow' => 'Ticketing', 'title' => 'Your Show, Your Revenue', 'desc' => 'Zero platform fees on every plan, so only your payment processor\'s fee comes off a paid ticket. QR tickets you scan at the door.'],
            ['num' => 'VI', 'eyebrow' => 'Booking', 'title' => 'Event Planner Kit', 'desc' => 'One link with your dates, videos, specs, and rates, plus a booking request form. Perfect for corporate bookers and wedding planners.'],
        ];

        // How each act's bill is set: its stock, how many words of the title go on the first
        // line, the face of each line, and each line's size as hundredths of the sheet's width.
        $ciSetting = [
            ['', 2, 'ci-caps', 'ci-wood ci-wear ci-a1', 13.45, 29.76],
            ['ci-straw', 2, 'ci-fat ci-a2', 'ci-wood ci-wear ci-a1', 15.78, 13.99],
            ['ci-navy', 2, 'ci-caps', 'ci-fat ci-wear ci-a1', 16.46, 13.98],
            ['', 1, 'ci-wood ci-wear ci-a1', 'ci-fat ci-a2', 12.79, 12.69],
            ['ci-straw', 2, 'ci-fat ci-a2', 'ci-wood ci-wear ci-a1', 14.32, 10.45],
            ['ci-red', 1, 'ci-caps', 'ci-wood ci-wear', 26.42, 12.23],
        ];

        $circusVenues = ['Big Tops', 'Theaters', 'Street', 'Festivals', 'Corporate', 'Cruise Ships'];
    @endphp

    <div id="ci">

        <div class="ci-valance-wrap" aria-hidden="true"><div class="ci-valance"></div></div>

        <!-- ============================================================ -->
        <!-- 1. The first bill: under the bulbs                           -->
        <!-- ============================================================ -->
        <section class="ci-row ci-hero" id="top">
            <div class="ci-bulbs">
                <span class="ci-chase" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                <div class="ci-bill ci-hero-bill">
                    <div class="ci-burst" aria-hidden="true"></div>
                    <div class="ci-over">
                        <h1 class="ci-stack ci-h1">
                            <x-marketing.hero-eyebrow class="ci-ribbon">The event schedule for circus &amp; acrobatics</x-marketing.hero-eyebrow>
                            <span class="es-mask ci-fit ci-wood ci-wear ci-a1" style="--fit: 10.79;"><span class="es-mask-line">Defy gravity.</span></span>
                            <span class="es-mask es-mask-2 ci-fit ci-fat ci-wear ci-a2" style="--fit: 10.81;"><span class="es-mask-line">Fill every seat.</span></span>
                        </h1>

                        <span class="ci-orn es-fade-up es-d-2" aria-hidden="true"><i></i></span>

                        <p class="ci-stack ci-lede es-fade-up es-d-2">
                            <span class="ci-fit ci-caps ci-long" style="--fit: 3.71; --cap: 2.3rem;">From the training studio to the big top,</span>
                            <span class="ci-fit ci-fat ci-wear ci-a1 ci-starred" style="--fit: 14.21; --cap: 5.6rem;">one link</span>
                            <span class="ci-fit ci-caps ci-long" style="--fit: 3.85; --cap: 2.3rem;">for every circus and acrobatics show.</span>
                            <span class="ci-lede-tail">Venues book you, fans follow you, no algorithm decides who sees it.</span>
                        </p>

                        <div class="ci-cta es-fade-up es-d-3">
                            <a href="#features" class="ci-btn ci-btn-line">See the program</a>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="ci-btn">Create your performance schedule <i aria-hidden="true"></i></a>
                        </div>

                        <!-- Performance-type marquee -->
                        <div class="ci-types es-fade-up es-d-4">
                            <div class="es-marquee" data-marquee="1">
                                <div class="es-marquee-track">
                                    @for ($tc = 0; $tc < 2; $tc++)
                                        @foreach ($ciTypes as $tag)
                                            <span @if ($tc === 1) aria-hidden="true" @endif class="ci-type">{{ $tag }}</span>
                                        @endforeach
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The program: six acts, six bills                          -->
        <!-- ============================================================ -->
        <section id="features" class="ci-row" style="scroll-margin-top: 4.5rem;">
            <div class="ci-bill ci-straw">
                <p class="ci-ribbon" data-reveal>Tonight's program</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-wood ci-wear ci-a1" style="--fit: 17.21; --cap: 9rem;">Six acts.</span>
                    <span class="ci-fit ci-fat ci-wear ci-a2" style="--fit: 9.71; --cap: 9rem;">One center ring.</span>
                </h2>
                <p class="ci-sub" data-reveal style="--reveal-delay: 0.16s;">Everything a circus and acrobatics career needs, announced one act at a time.</p>
            </div>

            <div class="ci-acts">
                @foreach ($circusActs as $ciIndex => $ciAct)
                    @php
                        [$ciStock, $ciBreak, $ciFaceOne, $ciFaceTwo, $ciFitOne, $ciFitTwo] = $ciSetting[$ciIndex];
                        $ciWords = explode(' ', $ciAct['title']);
                        $ciLineOne = implode(' ', array_slice($ciWords, 0, $ciBreak));
                        $ciLineTwo = implode(' ', array_slice($ciWords, $ciBreak));
                    @endphp
                    <article class="ci-bill ci-act {{ $ciStock }}" data-reveal style="--reveal-delay: {{ $ciIndex % 2 ? '0.12s' : '0s' }};">
                        <header>
                            <p class="ci-act-call">{{ $ciIndex === 5 ? 'For our final act' : 'And now, in the center ring' }}</p>
                            <div class="ci-act-no"><span>Act</span> <b>{{ $ciAct['num'] }}</b></div>
                            <div class="ci-act-kind">{{ $ciAct['eyebrow'] }}</div>
                        </header>

                        <h3 class="ci-stack ci-act-title">
                            <span class="ci-fit {{ $ciFaceOne }}" style="--fit: {{ $ciFitOne }}; --cap: 4.2rem;">{{ $ciLineOne }}</span>
                            <span class="ci-fit {{ $ciFaceTwo }}" style="--fit: {{ $ciFitTwo }}; --cap: 6.5rem;">{{ $ciLineTwo }}</span>
                        </h3>

                        <span class="ci-orn" aria-hidden="true" style="--g: '\2726';"><i></i></span>

                        <p class="ci-lead">{{ $ciAct['desc'] }}</p>

                        @if ($ciIndex === 0)
                            <p class="ci-act-more">Split the run into sub-schedules by tour leg, import dates from Google Calendar, and fans follow the whole circuit from one link, or subscribe to it from their own calendar app, which picks up a moved date on its own.</p>
                            <div class="ci-cut" aria-hidden="true">
                                <div class="ci-slip" style="--r: -0.8deg;">
                                    <div class="ci-slip-head"><span>Summer Circuit</span><span>3 stops</span></div>
                                    <div class="ci-route-row"><b>JUN 14</b><span>Oregon Country Fair</span><em>Main stage</em></div>
                                    <div class="ci-route-row"><b>AUG 25 - SEP 1</b><span>Black Rock City</span></div>
                                    <div class="ci-route-row"><b>SEP 12</b><span>Edmonton Fringe</span></div>
                                    <div class="ci-slip-foot ci-pointed">One link for the whole run</div>
                                </div>
                            </div>
                        @elseif ($ciIndex === 1)
                            <p class="ci-act-more">Write your technical rider into your schedule description and it sits at the top of your page, on the same link as your dates, so every booker reads it before they call.</p>
                            <div class="ci-cut" aria-hidden="true">
                                <div class="ci-slip ci-rider" style="--r: 0.7deg;">
                                    <div class="ci-slip-head" style="justify-content: center;"><span>Technical Rider - Aerial Silks</span></div>
                                    <dl>
                                        <div><dt>Ceiling height</dt><dd>6 m minimum</dd></div>
                                        <div><dt>Rigging points</dt><dd>2 certified points</dd></div>
                                        <div><dt>Weight rating</dt><dd>1,000 kg dynamic</dd></div>
                                        <div><dt>Floor space</dt><dd>6 x 6 m clear</dd></div>
                                    </dl>
                                    <div class="ci-slip-foot ci-pointed">On the same page as your dates</div>
                                </div>
                            </div>
                        @elseif ($ciIndex === 2)
                            <p class="ci-act-more">Weekly classes repeat themselves with <a href="{{ marketing_url('/features/recurring-events') }}">recurring events</a>, and on Pro you can sell multi-class <a href="{{ marketing_url('/features/passes') }}">passes</a> your students redeem across the term.</p>
                            <div class="ci-cut" aria-hidden="true">
                                <div class="ci-slip ci-perf ci-pass" style="--r: -1deg;">
                                    <div class="ci-pass-top">
                                        <span>Aerial Fundamentals</span>
                                        <span>$180</span>
                                    </div>
                                    <div class="ci-pass-kind">10-class pass</div>
                                    <div class="ci-punch">
                                        @for ($i = 0; $i < 10; $i++)
                                            <i @class(['is-used' => $i < 6])></i>
                                        @endfor
                                    </div>
                                    <div class="ci-pass-used">6 of 10 classes used</div>
                                    <div class="ci-slip-foot ci-pointed">Membership and drop-in options</div>
                                </div>
                            </div>
                        @elseif ($ciIndex === 3)
                            <p class="ci-act-more">A free schedule is one account. Enterprise adds up to five crew members and turns on <a href="{{ marketing_url('/features/availability') }}">availability</a>, where each of them marks the days they cannot work.</p>
                            <div class="ci-cut" aria-hidden="true">
                                <div class="ci-slip" style="--r: 0.8deg;">
                                    <div class="ci-slip-head" style="justify-content: center;"><span>The company</span></div>
                                    <div class="ci-company-row"><b>M</b><span><strong>Maya</strong><small>Aerialist</small></span><i></i></div>
                                    <div class="ci-company-row"><b>J</b><span><strong>Jonas</strong><small>Rigger</small></span><i></i></div>
                                    <div class="ci-company-row"><b>P</b><span><strong>Priya</strong><small>Stage manager</small></span><i></i></div>
                                    <div class="ci-slip-foot">Crew members and availability on Enterprise</div>
                                </div>
                            </div>
                        @elseif ($ciIndex === 4)
                            <p class="ci-act-more"><a href="{{ marketing_url('/stripe') }}">Stripe</a> or <a href="{{ marketing_url('/paypal') }}">PayPal</a> pays you directly, or take a payment link or cash at the gate. Charging for a seat is a Pro feature, which also brings promo codes for your regulars and waitlists for the sold-out nights. Announce a show before it goes on sale, switch on the "Notify me" card, and fans can leave just an email address to hear when it does.</p>
                            <div class="ci-cut" aria-hidden="true">
                                <div class="ci-tix-title">Saturday Night Spectacular</div>
                                <div class="ci-tix">
                                    <div class="ci-slip ci-perf ci-tick" style="--r: -1.6deg;">
                                        <small>Admit one</small>
                                        <strong>General Admission</strong>
                                        <b>$25</b>
                                        <em>No. 004127</em>
                                    </div>
                                    <div class="ci-slip ci-perf ci-tick" style="--r: 1.4deg;">
                                        <small>Admit one</small>
                                        <strong>Ringside</strong>
                                        <b>$45</b>
                                        <em>No. 000318</em>
                                    </div>
                                </div>
                                <div class="ci-fee"><span>Platform fee</span><span>{{ plan_price(0) }}</span></div>
                                <div class="ci-cut-foot ci-pointed">QR check-in at the door</div>
                            </div>
                        @else
                            <p class="ci-act-more">Keep private quotes as drafts until they are confirmed, so corporate gigs never leak onto your public calendar early.</p>
                            <div class="ci-cut" aria-hidden="true">
                                <div class="ci-slip" style="--r: -0.7deg;">
                                    <div class="ci-kit-url" dir="ltr">your-troupe.eventschedule.com</div>
                                    <div class="ci-kit-tabs">
                                        <span>Dates</span>
                                        <span>Videos</span>
                                        <span>Tech specs</span>
                                        <span>Rates</span>
                                    </div>
                                    <div class="ci-kit-btn">Booking inquiry</div>
                                </div>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Three rings: sub-schedules                                -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill">
                <p class="ci-ribbon" data-reveal>The three rings</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-fat ci-wear ci-a2" style="--fit: 12.84; --cap: 9rem;">Three rings.</span>
                    <span class="ci-fit ci-wood ci-wear ci-a1" style="--fit: 10.62; --cap: 9rem;">One schedule.</span>
                </h2>
                <p class="ci-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Sub-schedules split one calendar into rings. Each ring gets its own link and its own colour, and your schedule link still shows the lot.
                </p>

                <div class="ci-rings" data-reveal-group="140">
                    <div class="ci-ring" data-reveal="zoom">
                        <div class="ci-ring-in">
                            <div class="ci-ring-no">Ring I</div>
                            <h3>Troupe Shows</h3>
                            <p>Public performances under one banner. The ring most people turn up for.</p>
                        </div>
                    </div>
                    <div class="ci-ring" data-reveal="zoom" style="--d: -1.7s;">
                        <div class="ci-ring-in">
                            <div class="ci-ring-no">Ring II</div>
                            <h3>Classes &amp; Workshops</h3>
                            <p>Weekly aerial basics and intensives on their own calendar link.</p>
                        </div>
                    </div>
                    <div class="ci-ring" data-reveal="zoom" style="--d: -3.4s;">
                        <div class="ci-ring-in">
                            <div class="ci-ring-no">Ring III</div>
                            <h3>Corporate &amp; Private</h3>
                            <p>Quotes, galas, and festival buyouts. Hold each one as a draft until the contract is signed.</p>
                        </div>
                    </div>
                </div>

                <p class="ci-after" data-reveal>
                    <a href="{{ marketing_url('/features/sub-schedules') }}" class="ci-hand">Sub-schedules are free</a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The ring goes online (one link field)                     -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill ci-navy">
                <p class="ci-ribbon" data-reveal>And now, the ring goes online</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-caps" style="--fit: 8.52; --cap: 4.5rem;">A ring the size of</span>
                    <span class="ci-fit ci-wood ci-wear ci-a1" style="--fit: 17.70; --cap: 9rem;">one link</span>
                </h2>
                <p class="ci-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Event Schedule does not host the stream. Tick Online, paste the URL people join on, and the date, the running order and the tickets work exactly as they do for a room with a floor.
                </p>

                <!-- Online event mock: the link field and where the link shows up -->
                <div class="ci-slip ci-wire" aria-hidden="true" data-reveal="panel">
                    <div class="ci-wire-top">
                        <strong>Midnight Silks, from the studio</strong>
                        <span class="ci-wire-stamp">Online</span>
                    </div>
                    <div class="ci-wire-label">Link people join on</div>
                    <div class="ci-wire-url" dir="ltr">https://zoom.us/j/8471234567</div>
                    <div class="ci-wire-note">Zoom, Meet, Teams, YouTube Live, Twitch, or a page on your own site. It is a link, not an integration.</div>
                    <div class="ci-wire-rows">
                        <div>
                            <span>Listing</span>
                            <span>zoom.us <span>- the domain only, so nobody walks in uninvited</span></span>
                        </div>
                        <div>
                            <span>Ticket</span>
                            <span>The full join link, printed where the venue address would be</span>
                        </div>
                    </div>
                    <div class="ci-wire-fee">
                        <span>Platform fee on every ticket</span>
                        <span>{{ plan_price(0) }}</span>
                    </div>
                </div>

                <!-- Benefits -->
                <div class="ci-boons" data-reveal-group="90">
                    <div class="ci-boon" data-reveal>
                        <h3>Any platform you already use</h3>
                        <p>One link field takes any URL, so there is no account to connect and no token to reconnect</p>
                    </div>
                    <div class="ci-boon" data-reveal>
                        <h3>Ticket it like any other show</h3>
                        <p>Same ticket types, same zero platform fees, and the join link rides on the ticket. Free registration produces a ticket too</p>
                    </div>
                    <div class="ci-boon" data-reveal>
                        <h3>Or run it both ways at once</h3>
                        <p>Tick In person as well and the same date is a hybrid, with the room and the link on one listing</p>
                    </div>
                </div>

                <p class="ci-after" data-reveal>
                    <a href="{{ marketing_url('/features/online-events') }}" class="ci-btn">Learn about online events <i aria-hidden="true"></i></a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Where circus artists perform                              -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill ci-red">
                <p class="ci-ribbon" data-reveal>On every route</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-caps" style="--fit: 7.07; --cap: 4.5rem;">Where circus artists</span>
                    <span class="ci-fit ci-wood ci-wear" style="--fit: 17.38; --cap: 9rem;">perform</span>
                </h2>
                <p class="ci-sub" data-reveal style="--reveal-delay: 0.16s;">From street corners to the big top - one schedule for every stage</p>

                <div class="ci-stack ci-stands" data-reveal>
                    @foreach (array_chunk($circusVenues, 2) as $ciRowIndex => $ciPair)
                        <div class="ci-fit ci-stand-row {{ ['ci-fat', 'ci-wood ci-wear', 'ci-caps'][$ciRowIndex] }}" style="--fit: {{ [8.22, 8.58, 6.20][$ciRowIndex] }}; --cap: 5.5rem;">
                            <h3>{{ $ciPair[0] }}</h3><span aria-hidden="true">&#9733;</span><h3>{{ $ciPair[1] }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Perfect for: the company                                  -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill">
                <p class="ci-ribbon" aria-hidden="true" data-reveal>The whole company</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-caps" style="--fit: 6.07; --cap: 4.5rem;">Perfect for all types of</span>
                    <span class="ci-fit ci-fat ci-wear ci-a1" style="--fit: 8.64; --cap: 9rem;">circus performers</span>
                </h2>
                <p class="ci-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Whether you're a solo aerialist or a touring troupe, Event Schedule works for you.
                </p>

                @php
                    $ciCompany = [
                        ['Aerialists', 'Share your aerial silk, trapeze, and hoop performances. Let fans know where to catch your next show.', 'for-aerialists'],
                        ['Circus Troupes', 'Coordinate your ensemble\'s schedule and let audiences follow your collective performances.', 'for-circus-troupes'],
                        ['Fire Performers', 'Promote your fire dancing, breathing, and spinning shows at festivals and events.', 'for-fire-performers'],
                        ['Contortionists', 'Showcase your flexibility performances and build a dedicated following.', 'for-contortionists'],
                        ['Jugglers & Prop Artists', 'List your juggling, poi, and object manipulation shows and workshops.', 'for-jugglers-prop-artists'],
                        ['Stilt Walkers', 'Share your larger-than-life performances at parades, festivals, and corporate events.', 'for-stilt-walkers'],
                    ];
                @endphp
                <div class="ci-table ci-three" data-reveal>
                    @foreach ($ciCompany as [$ciName, $ciDesc, $ciSlug])
                        @php $ciPost = get_sub_audience_blog($ciSlug); @endphp
                        <article class="ci-cell">
                            <span class="ci-cell-no" aria-hidden="true">&#9733; &#9733; &#9733;</span>
                            <h3>{{ $ciName }}</h3>
                            <p>{{ $ciDesc }}</p>
                            @if ($ciPost)
                                <a href="{{ blog_url('/' . $ciPost->slug) }}" class="ci-hand" aria-label="Learn more about Event Schedule for {{ $ciName }}">Learn more</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. How it works: three seals                                 -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill ci-straw">
                <p class="ci-ribbon" data-reveal>Quick setup</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-caps" style="--fit: 6.70; --cap: 4.2rem;">Get your performance</span>
                    <span class="ci-fit ci-fat ci-wear ci-a2" style="--fit: 8.53; --cap: 7rem;">schedule online in</span>
                    <span class="ci-fit ci-wood ci-wear ci-a1" style="--fit: 12.76; --cap: 9rem;">three steps</span>
                </h2>

                <div class="ci-steps" data-reveal-group="140">
                    <div class="ci-step" data-reveal>
                        <div class="ci-seal" aria-hidden="true"><i></i><i></i><b>I</b></div>
                        <h3>Add your acts</h3>
                        <p>Shows, workshops, festival appearances. Import from Google Calendar or add manually.</p>
                    </div>
                    <div class="ci-step" data-reveal>
                        <div class="ci-seal" aria-hidden="true"><i></i><i></i><b>II</b></div>
                        <h3>Share one link</h3>
                        <p>Add to your website, social bios, and booking portfolio. Planners see everything.</p>
                    </div>
                    <div class="ci-step" data-reveal>
                        <div class="ci-seal" aria-hidden="true"><i></i><i></i><b>III</b></div>
                        <h3>Build your following</h3>
                        <p>Fans who sign up with their email get a digest of the dates you add. You write the newsletter and pick the moment it goes out.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Key features                                              -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill">
                <p class="ci-ribbon" aria-hidden="true" data-reveal>Also on the bill</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-fat ci-wear ci-a2" style="--fit: 12.39; --cap: 6.5rem;">Key features</span>
                </h2>

                @php
                    $ciFeatures = [
                        ['Ticketing', 'Sell tickets with QR check-in and zero platform fees', marketing_url('/features/ticketing')],
                        ['Event Graphics', 'Show posters generated from your events, on every plan', marketing_url('/features/event-graphics')],
                        ['Newsletters', 'Write your own updates and send them to your followers', marketing_url('/features/newsletters')],
                        ['Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', marketing_url('/features/calendar-sync')],
                    ];
                @endphp
                <div class="ci-table" data-reveal>
                    @foreach ($ciFeatures as [$ciFeatName, $ciFeatDesc, $ciFeatUrl])
                        <a href="{{ $ciFeatUrl }}" class="ci-cell">
                            <span class="ci-cell-no" aria-hidden="true">&#9755;</span>
                            <h3>{{ $ciFeatName }}</h3>
                            <p>{{ $ciFeatDesc }}</p>
                        </a>
                    @endforeach
                </div>

                <p class="ci-after" data-reveal>
                    <a href="{{ marketing_url('/features') }}" class="ci-hand">See all features</a>
                </p>
            </div>
        </section>

        <div class="ci-row">
            <div class="ci-bill ci-straw ci-plans">
                @include('marketing.partials.pricing-nudge')
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- 9. Related pages: handbills                                  -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill">
                <p class="ci-ribbon" aria-hidden="true" data-reveal>Playing the same towns</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-wood ci-wear ci-a1" style="--fit: 10.71; --cap: 6rem;">Related pages</span>
                </h2>

                <div class="ci-hands" data-reveal-group="80">
                    @foreach ([['/for-magicians', 'Magicians'], ['/for-dance-groups', 'Dance Groups'], ['/for-theater-performers', 'Theater Performers'], ['/for-visual-artists', 'Visual Artists']] as $ciRelIndex => [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="ci-handbill" data-reveal style="--r: {{ ['-1.4deg', '1deg', '-0.8deg', '1.5deg'][$ciRelIndex] }};">
                            <small>Event Schedule for</small>
                            <strong>{{ $relName }}</strong>
                        </a>
                    @endforeach
                </div>

                <p class="ci-after" data-reveal>
                    <a href="{{ marketing_url('/use-cases') }}" class="ci-hand">See all use cases</a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. FAQ: particulars                                         -->
        <!-- ============================================================ -->
        <section class="ci-row">
            <div class="ci-bill ci-straw">
                <p class="ci-ribbon" aria-hidden="true" data-reveal>Particulars</p>
                <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                    <span class="ci-fit ci-caps" style="--fit: 8.50; --cap: 4.5rem;">Frequently asked</span>
                    <span class="ci-fit ci-wood ci-wear ci-a1" style="--fit: 14.84; --cap: 9rem;">questions</span>
                </h2>
                <p class="ci-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Everything circus performers ask about Event Schedule.
                </p>

                @php
                    $faqs = [
                        [
                            'q' => 'Is Event Schedule free for circus performers?',
                            'a' => 'Yes, for most of it. Sharing your performance schedule, sub-schedules, two-way calendar sync, an embeddable calendar, free registration with a capacity, a calendar feed fans subscribe to, the booking request form and one bookable appointment type are all free forever, with no ceiling on how many people register. Putting a price on a ticket is the part that needs Pro. Newsletters are free up to 10 emails a month, counted per recipient rather than per send, with 100 on Pro and 1,000 on Enterprise.',
                        ],
                        [
                            'q' => 'Can I manage tour dates and local shows in one schedule?',
                            'a' => 'Yes. List all your performances in one place - touring shows, local gigs, festival appearances, and private events. Use sub-schedules to sort and colour-code by show type or tour leg, each with its own link. Your schedule link still shows everything in one calendar.',
                        ],
                        [
                            'q' => 'How do audiences discover my performances?',
                            'a' => 'Share your schedule link on social media, on your booking page, or embed the calendar on your website. Fans who leave an email address get a digest automatically when you add dates, at most one every few days. Beyond that you write the newsletter and choose when it goes out, and somebody who followed you from their own account only ever hears from you that way. Fans who would rather not give an email can subscribe to your calendar from your page instead. And on a single show, once you switch on the "Notify me" card, anyone can leave just an email address to hear when its tickets go on sale, if it is cancelled, and shortly before it starts, plus any change notice you send.',
                        ],
                        [
                            'q' => 'Can I sell tickets to my shows?',
                            'a' => 'Yes, on Pro, which is what opens paid checkout. Take payment through Stripe or PayPal straight to your own account, or through Payfast (rand only), Invoice Ninja, a payment link or cash, with as many ticket types as the night needs, each at its own fixed price and its own inventory per date. Zero platform fees on every plan, so the only deduction is your payment provider\'s own. If a show is rained off, a Stripe or PayPal sale can be refunded in full or in part from the Sales page, and the money goes back through the provider. A tier like Ringside is a ticket type sold by the number; a seat map the audience picks from is drawn on a venue schedule on Enterprise, so a troupe with its own big top would set that up as a venue.',
                        ],
                        [
                            'q' => 'Can I sell class passes for my aerial or acro classes?',
                            'a' => 'Yes. On the Pro plan you can sell multi-use passes alongside regular tickets - a 10-visit pass, an unlimited membership, a festival pass or a season pass on a recurring class. Passes are redeemable across the events you scope them to, usage is counted for you, and you can set a cancellation deadline for late drops. Zero platform fees apply to passes too.',
                        ],
                        [
                            'q' => 'Can my whole troupe manage one schedule?',
                            'a' => 'On Enterprise, yes. A free schedule is a single account. Enterprise adds team members, up to five on the hosted plans, so your rigger, stage manager, and performers can all update the calendar. Enterprise also turns on availability for talent schedules, where each member marks the days they cannot work and you see it against the calendar.',
                        ],
                        [
                            'q' => 'A festival listed my act before I joined. Is there already a page for me?',
                            'a' => 'There may be. When a festival or venue names an act that is not on Event Schedule, its event page still shows that act in the lineup by name, and the app creates a page for the act. That page says which schedule created it and that you have not claimed it, credits each date to the schedule that added it, and stays out of search engines until it is claimed. If it carries your email address, create an account or sign in with that address and press Claim this page: it becomes your schedule, and the festivals that already listed you keep listing you without asking again, while anyone new sends a request you accept. If it is not you, This is not me takes it down.',
                        ],
                    ];
                @endphp

                <div class="ci-faq" data-reveal>
                    @foreach ($faqs as $faq)
                        <details name="faq">
                            <summary>
                                <h3>{{ $faq['q'] }}</h3>
                            </summary>
                            <p>{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- 11. Grand finale: the ticket window                          -->
        <!-- ============================================================ -->
        <div class="ci-valance-wrap" aria-hidden="true" style="margin-top: 1rem;"><div class="ci-valance"></div></div>
        <section id="claim" class="ci-row ci-finale" style="scroll-margin-top: 4.5rem;">
            <div class="ci-bulbs">
                <span class="ci-chase" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                <div class="ci-bill ci-red ci-finale-bill">
                    <div class="ci-burst" aria-hidden="true"></div>
                    <div class="ci-over">
                        <p class="ci-ribbon" data-reveal>Ladies and gentlemen, take your bow</p>
                        <h2 class="ci-stack ci-h2" data-reveal style="--reveal-delay: 0.08s;">
                            <span class="ci-fit ci-wood ci-wear" style="--fit: 6.75; --cap: 9rem;">The show must go on.</span>
                            <span class="ci-fit ci-fat ci-wear" style="--fit: 5.49; --cap: 9rem;">Make sure they know where.</span>
                        </h2>
                        <p class="ci-finale-p" data-reveal style="--reveal-delay: 0.16s;">
                            Your art deserves an audience. Free forever.
                        </p>

                        <div class="ci-admit" id="ci-admit" data-reveal="panel">
                            <div class="ci-admit-stub" aria-hidden="true">
                                <span>No.</span>
                                <b>000001</b>
                                <span>Front row</span>
                            </div>
                            <div class="ci-admit-main">
                                <div class="ci-admit-top" aria-hidden="true"><span>Admit one</span><span>Good any night</span></div>
                                <label for="es-claim-input" class="ci-admit-label">Your schedule name</label>
                                <div class="ci-admit-form">
                                    <div dir="ltr" class="es-claim ci-claim">
                                        <input id="es-claim-input" type="text" placeholder="your-troupe" autocomplete="off" spellcheck="false" maxlength="30">
                                        <span>.eventschedule.com</span>
                                    </div>
                                    <a href="{{ app_url('/sign_up?type=talent') }}" class="ci-btn">Get Started Free <i aria-hidden="true"></i></a>
                                </div>
                                <p class="ci-admit-note">No credit card required</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="ci-row">
            <div class="ci-bill ci-keep">
                <x-marketing.related-pages />
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    <script {!! nonce_attr() !!}>
        (function () {
            var root = document.getElementById('ci');
            if (!root) {
                return;
            }

            {{-- Each display line carries its measured size in the markup. This sets it again from the
                 type actually on the page, so a line still runs edge to edge when a fallback face is
                 standing in for the wood type. --}}
            function fit() {
                root.querySelectorAll('.ci-fit').forEach(function (line) {
                    if (window.getComputedStyle(line).whiteSpace !== 'nowrap') {
                        return;
                    }
                    var probe = line.cloneNode(true);
                    probe.style.cssText = 'position:absolute;visibility:hidden;display:inline-block;width:auto;margin:0;animation:none;font-size:100px';
                    line.parentNode.appendChild(probe);
                    var em = probe.getBoundingClientRect().width / 100;
                    probe.remove();
                    if (em > 0) {
                        line.style.setProperty('--fit', (98.6 / em).toFixed(2));
                    }
                });
            }
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(fit);
            } else {
                window.addEventListener('load', fit);
            }

            {{-- The finale's confetti, in the bill's own inks rather than the site's blues. --}}
            var ticket = document.getElementById('ci-admit');
            if (!ticket || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    {{-- The library's ready-made cannon draws from a blob Worker, which the site's
                         policy refuses without an error, so nothing was ever drawn. A cannon made
                         here draws on the page itself. --}}
                    var fire = window.confetti.create(null, { resize: true });
                    var inks = ['#f4e6c8', '#e2a72e', '#1c2a4a', '#fff6d2'];
                    [[60, 0.06], [120, 0.94]].forEach(function (shot) {
                        fire({ particleCount: 80, angle: shot[0], spread: 60, startVelocity: 55, origin: { x: shot[1], y: 0.95 }, colors: inks, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.6 });
            io.observe(ticket);
        })();
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
