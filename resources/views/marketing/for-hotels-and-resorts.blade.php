<x-marketing-layout>
    <x-slot name="title">Hotel & Resort Guest Activity Calendar | Free Sign-Ups</x-slot>
    <x-slot name="description">Put your guest activities on one page: the standing week entered once, a QR code for the key-card sleeve, free sign-ups and tickets with no platform fee.</x-slot>
    <x-slot name="breadcrumbTitle">For Hotels & Resorts</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Marcellus') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Bellefair') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Mulish') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Hotels & Resorts"
        description="Put the week of guest activities on a page with your property's name on it, print the link on the key-card sleeve, and let guests read the card without asking the desk."
        audience="Hotels & Resorts"
        keywords="hotel activity calendar, resort event schedule, guest activity management, hotel entertainment calendar, free hotel scheduling" />
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
           For-hotels-and-resorts "The Daily Program" styles.

           The page is the card a good resort slips under the door each
           evening, and the small paper and brass things of a stay around
           it: heavy ivory stock with a blind-embossed border, a crest in
           a ring of type, the day set in one column with its times; a
           brass key fob numbering each section; the key-card sleeve; the
           desk's ledger; two door hangers on their knobs; luggage labels;
           a folded letter; a luggage tag to put a name on. One still
           horizon runs under the hero, and it does not move.

           What the page still refuses to draw, as before: no room grid
           and no per-space capacity (a sub-schedule is a name, a colour
           and a link), and no overlap warning (no such check exists).
           The door plates and fob numerals are furniture, not features.

           Everything is scoped under #hr with its own tokens. The shared
           es-* reveal system (marketing.css, marketing-home.js) drives
           the few entrances; nothing here follows the scroll.
           ============================================================== */

        @property --hr-spec {
            syntax: '<angle>';
            inherits: true;
            initial-value: 24deg;
        }

        #hr {
            --hr-ground: #f7f2e8;
            --hr-band: #e6dcc8;
            --hr-under: var(--hr-ground);
            --hr-stock: #fdfaf2;
            --hr-stock-2: #f3ecdb;
            --hr-ink: #17292c;
            --hr-ink-2: #3c5054;
            --hr-ink-3: #4d5f62;
            --hr-sea: #0e4a52;
            --hr-lagoon: #2f8f95;
            --hr-brass: #a8854a;
            --hr-bronze: #73531f;
            --hr-amber: #e08e45;
            --hr-line: rgba(14, 74, 82, 0.18);
            --hr-hi: rgba(255, 255, 255, 0.95);
            --hr-lo: rgba(96, 72, 30, 0.3);
            --hr-shadow: rgba(52, 40, 16, 0.26);
            --hr-btn: #0e4a52;
            --hr-btn-ink: #f7f2e8;
            --hr-display: 'Marcellus', 'Optima', 'Palatino Linotype', Palatino, 'Book Antiqua', Georgia, serif;
            --hr-serif: 'Bellefair', 'Hoefler Text', 'Baskerville', 'Palatino Linotype', Georgia, serif;
            --hr-text: 'Mulish', 'Avenir Next', 'Segoe UI', system-ui, sans-serif;
            --hr-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.3 0 0 0 0 0.22 0 0 0 0 0.08 0 0 0 0.07 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)'/%3E%3C/svg%3E");
            --hr-brass-metal: conic-gradient(from var(--hr-spec), #7f6230 0deg, #f0dba6 28deg, #b89658 62deg, #8a6c36 118deg, #dcc48b 172deg, #a68549 214deg, #f3e2b4 252deg, #9a7a3f 300deg, #7f6230 360deg);
            position: relative;
            background: var(--hr-ground);
            color: var(--hr-ink-2);
            font-family: var(--hr-text);
            font-size: 1.0625rem;
            line-height: 1.65;
        }
        .dark #hr {
            --hr-ground: #0c1f24;
            --hr-band: #0f2a30;
            --hr-stock: #14353d;
            --hr-stock-2: #183f48;
            --hr-ink: #f3ecdd;
            --hr-ink-2: #c8d3d1;
            --hr-ink-3: #a3b4b2;
            --hr-sea: #8fd3d2;
            --hr-lagoon: #5fb9bc;
            --hr-brass: #c9a460;
            --hr-bronze: #dab873;
            --hr-amber: #f0a55c;
            --hr-line: rgba(243, 236, 221, 0.16);
            --hr-hi: rgba(255, 255, 255, 0.07);
            --hr-lo: rgba(201, 164, 96, 0.55);
            --hr-shadow: rgba(0, 0, 0, 0.55);
            --hr-btn: #d2ad6b;
            --hr-btn-ink: #0c1f24;
        }

        /* The bar above takes the card's stock, so the page reads as one sheet. */
        body > header.sticky {
            background-color: rgba(247, 242, 232, 0.88);
            border-bottom-color: rgba(14, 74, 82, 0.14);
        }
        .dark body > header.sticky {
            background-color: rgba(12, 31, 36, 0.88);
            border-bottom-color: rgba(243, 236, 221, 0.12);
        }

        #hr ::selection { background: #0e4a52; color: #f7f2e8; }
        #hr a:focus-visible,
        #hr summary:focus-visible,
        #hr input:focus-visible {
            outline: 2px solid var(--hr-bronze);
            outline-offset: 4px;
        }

        .hr-wrap { width: min(100% - 2.5rem, 74rem); margin-inline: auto; }
        .hr-narrow { width: min(100% - 2.5rem, 54rem); margin-inline: auto; }
        .hr-section { position: relative; padding-block: clamp(4.5rem, 9vw, 7.5rem); scroll-margin-top: 4rem; }
        .hr-band { background: var(--hr-band); --hr-under: var(--hr-band); }

        /* ---------------------------------------------------------------
           Voices: inscriptional capitals, a quiet serif, a plain text face
           --------------------------------------------------------------- */
        .hr-kicker {
            font-family: var(--hr-display);
            font-size: 0.74rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            line-height: 1.5;
            color: var(--hr-bronze);
        }
        .hr-h2 {
            font-family: var(--hr-display);
            font-weight: 400;
            font-size: clamp(2.05rem, 4vw, 3.3rem);
            line-height: 1.12;
            letter-spacing: -0.005em;
            color: var(--hr-ink);
            text-wrap: balance;
        }
        .hr-mark { color: var(--hr-bronze); }
        .hr-lede { font-size: clamp(1.06rem, 1.35vw, 1.2rem); line-height: 1.7; color: var(--hr-ink-2); text-wrap: pretty; }
        .hr-fine { font-size: 0.9rem; line-height: 1.6; color: var(--hr-ink-3); }
        .hr-num { font-variant-numeric: oldstyle-nums tabular-nums; }

        .hr-head { display: grid; justify-items: center; text-align: center; gap: 1.1rem; max-width: 46rem; margin-inline: auto; margin-bottom: clamp(2.75rem, 5vw, 4rem); }
        .hr-head .hr-lede { max-width: 40rem; }
        .hr-head-start { justify-items: start; text-align: start; margin-inline: 0; margin-bottom: 2rem; }

        /* A hairline with a lozenge on it: the card's one ornament. */
        .hr-orn {
            position: relative;
            height: 1px;
            width: min(100%, 11rem);
            margin-inline: auto;
            background: linear-gradient(90deg, transparent, var(--hr-brass) 18%, var(--hr-brass) 82%, transparent);
        }
        .hr-orn::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 0.42rem;
            height: 0.42rem;
            translate: -50% -50%;
            rotate: 45deg;
            background: var(--hr-brass);
            box-shadow: 0 0 0 0.28rem var(--hr-orn-ground, var(--hr-stock));
        }
        .hr-hair { height: 1px; background: var(--hr-line); }

        /* Plan tags: a small engraved plate. */
        .hr-tag {
            display: inline-block;
            vertical-align: middle;
            padding: 0.22rem 0.5rem 0.16rem;
            border: 1px solid var(--hr-brass);
            border-radius: 1px;
            font-family: var(--hr-display);
            font-size: 0.62rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            line-height: 1.2;
            color: var(--hr-bronze);
            white-space: nowrap;
        }
        .hr-tag-pro { border-color: #0e4a52; background: #0e4a52; color: #f7f2e8; }
        .dark .hr-tag-pro { border-color: #d2ad6b; background: #d2ad6b; color: #0c1f24; }

        /* Buttons: an engraved plate, pressed a hair when touched. */
        .hr-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1.1rem 1.75rem 1rem;
            border-radius: 2px;
            background: var(--hr-btn);
            color: var(--hr-btn-ink);
            font-family: var(--hr-display);
            font-size: 0.86rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            line-height: 1.2;
            text-align: center;
            box-shadow: inset 0 0 0 4px var(--hr-btn), inset 0 0 0 5px rgba(233, 207, 146, 0.6), 0 1rem 1.5rem -1rem var(--hr-shadow);
            transition: translate 0.25s ease, box-shadow 0.25s ease;
        }
        .dark .hr-btn { box-shadow: inset 0 0 0 4px var(--hr-btn), inset 0 0 0 5px rgba(12, 31, 36, 0.45), 0 1rem 1.5rem -1rem var(--hr-shadow); }
        .hr-btn:hover { translate: 0 -2px; box-shadow: inset 0 0 0 4px var(--hr-btn), inset 0 0 0 5px rgba(233, 207, 146, 0.9), 0 1.4rem 1.8rem -1rem var(--hr-shadow); }
        .dark .hr-btn:hover { box-shadow: inset 0 0 0 4px var(--hr-btn), inset 0 0 0 5px rgba(12, 31, 36, 0.7), 0 1.4rem 1.8rem -1rem var(--hr-shadow); }
        .hr-btn svg { width: 1.05rem; height: 1.05rem; flex: none; transition: translate 0.25s ease; }
        .hr-btn:hover svg { translate: 3px 0; }
        .hr-btn-ghost { background: transparent; color: var(--hr-ink); box-shadow: inset 0 0 0 1px var(--hr-ink-3); }
        .dark .hr-btn-ghost { box-shadow: inset 0 0 0 1px var(--hr-ink-3); }
        .hr-btn-ghost:hover,
        .dark .hr-btn-ghost:hover { box-shadow: inset 0 0 0 1px var(--hr-ink), 0 1.2rem 1.6rem -1.1rem var(--hr-shadow); }
        .hr-btn-ghost:hover svg { translate: 0 3px; }
        .hr-link {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding-bottom: 0.3rem;
            border-bottom: 1px solid var(--hr-brass);
            font-family: var(--hr-display);
            font-size: 0.8rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--hr-ink);
            transition: gap 0.25s ease, border-color 0.25s ease;
        }
        .hr-link:hover { gap: 0.95rem; border-color: var(--hr-ink); }
        .hr-link svg { width: 0.95rem; height: 0.95rem; }

        /* ---------------------------------------------------------------
           Stock: the card everything is printed on, blind-embossed
           --------------------------------------------------------------- */
        .hr-card {
            position: relative;
            background-color: var(--hr-stock);
            background-image: var(--hr-grain);
            border-radius: 3px;
            box-shadow: 0 0 0 1px var(--hr-line), 0 1.8rem 2.8rem -1.9rem var(--hr-shadow), 0 0.3rem 0.6rem -0.35rem var(--hr-shadow);
            --hr-orn-ground: var(--hr-stock);
        }
        /* Two rules pressed into the stock: each a dark edge with a light one inside it. */
        .hr-emboss::before,
        .hr-emboss::after {
            content: "";
            position: absolute;
            pointer-events: none;
            border-radius: 2px;
        }
        .hr-emboss::before { inset: 0.7rem; box-shadow: inset 0 0 0 1px var(--hr-lo), inset 0 0 0 2px var(--hr-hi); }
        .hr-emboss::after { inset: 1rem; box-shadow: inset 0 0 0 1px var(--hr-hi), inset 0 0 0 2px var(--hr-lo); opacity: 0.6; }

        /* Brass: a key fob on its ring, numbering each section like a room. */
        .hr-fob { position: relative; display: inline-grid; justify-items: center; padding-top: 1.2rem; }
        .hr-fob-ring {
            position: absolute;
            top: 0;
            left: 50%;
            width: 1.7rem;
            height: 1.7rem;
            margin-left: -0.85rem;
            border-radius: 50%;
            border: 2px solid var(--hr-brass);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45), 0 1px 1px rgba(0, 0, 0, 0.18);
        }
        .hr-fob-tag {
            position: relative;
            display: grid;
            place-items: center;
            width: 3.2rem;
            height: 4.1rem;
            padding-top: 0.75rem;
            border-radius: 50%;
            background: var(--hr-brass-metal);
            font-family: var(--hr-display);
            font-weight: 400;
            font-size: 0.92rem;
            letter-spacing: 0.08em;
            color: #4e390f;
            text-shadow: 0 1px 0 rgba(255, 246, 220, 0.8);
            box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.6), inset 0 0 0 3px rgba(255, 240, 200, 0.3), 0 0.6rem 0.9rem -0.45rem var(--hr-shadow);
            transition: --hr-spec 1.1s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .hr-fob-tag::before {
            content: "";
            position: absolute;
            top: 0.42rem;
            left: 50%;
            width: 0.52rem;
            height: 0.52rem;
            margin-left: -0.26rem;
            border-radius: 50%;
            background: var(--hr-under);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.5);
        }
        .hr-head:hover .hr-fob-tag,
        .hr-fob:hover .hr-fob-tag { --hr-spec: 118deg; }
        .hr-fob { transform-origin: 50% 0.85rem; }
        html.es-anim #hr .hr-fob[data-reveal] { transition: opacity 0.5s ease, rotate 1.7s cubic-bezier(0.3, 1.7, 0.5, 1); }
        html.es-anim #hr .hr-fob[data-reveal]:not(.is-revealed) { rotate: -16deg; transform: none; }

        /* ---------------------------------------------------------------
           The floor directory: the section rail, on wide screens only
           --------------------------------------------------------------- */
        .hr-rail { display: none; }
        @media (min-width: 1560px) {
            /* The nav is a box the size of the page that clips the directory, so the directory
               stays fixed to the screen and still ends where the page does instead of riding
               over the site footer. */
            .hr-rail {
                display: block;
                position: absolute;
                inset: 0;
                z-index: 40;
                clip-path: inset(0);
                pointer-events: none;
            }
            .hr-rail ol { position: fixed; right: 1.5rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.1rem; justify-items: end; pointer-events: auto; }
            /* The directory is printed for the day's stock. The evening band passes over it:
               its dark ink on that ground could not be read. */
            .hr-fin { z-index: 41; }
            .hr-rail a { position: relative; display: flex; align-items: center; justify-content: flex-end; gap: 0.7rem; padding: 0.42rem 0; color: var(--hr-ink-3); }
            .hr-rail a::after {
                content: "";
                flex: none;
                width: 0.46rem;
                height: 0.46rem;
                rotate: 45deg;
                border: 1px solid currentColor;
                transition: background-color 0.3s ease, border-color 0.3s ease, scale 0.3s ease;
            }
            .hr-rail a span {
                font-family: var(--hr-display);
                font-size: 0.66rem;
                letter-spacing: 0.22em;
                text-transform: uppercase;
                white-space: nowrap;
                opacity: 0;
                translate: 0.4rem 0;
                transition: opacity 0.25s ease, translate 0.25s ease;
            }
            .hr-rail a:hover span,
            .hr-rail a:focus-visible span,
            .hr-rail a.is-active span { opacity: 1; translate: 0 0; }
            .hr-rail a.is-active { color: var(--hr-bronze); }
            .hr-rail a.is-active::after { background: var(--hr-brass); border-color: var(--hr-brass); scale: 1.25; }
        }

        /* ---------------------------------------------------------------
           Hero: the card, and the horizon it was written beside
           --------------------------------------------------------------- */
        .hr-hero { position: relative; overflow: clip; padding-top: clamp(2rem, 3.5vw, 2.75rem); }
        .hr-hero-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 2.75rem;
            padding-bottom: 15rem;
        }
        .hr-copy { min-width: 0; }
        @media (min-width: 1000px) {
            .hr-hero-grid {
                grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr);
                grid-template-areas: "copy card" "note card";
                grid-template-rows: auto 1fr;
                column-gap: clamp(2.5rem, 5vw, 4.5rem);
                row-gap: 1.6rem;
                align-items: start;
                padding-bottom: 12.5rem;
            }
            .hr-copy { grid-area: copy; padding-top: 1.25rem; }
            /* The card stands low on the right and crosses the horizon. */
            .hr-program-wrap { grid-area: card; align-self: end; margin-bottom: -8rem; }
            .hr-aside-note { grid-area: note; }
        }
        .hr-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            margin-bottom: 1.3rem;
            font-family: var(--hr-display);
            font-size: 0.76rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            line-height: 1.5;
            color: var(--hr-bronze);
        }
        .hr-eyebrow::after { content: ""; flex: none; width: 3rem; height: 1px; background: var(--hr-brass); }
        .hr-h1 {
            font-family: var(--hr-display);
            font-weight: 400;
            font-size: clamp(2.6rem, 5.1vw, 4.4rem);
            line-height: 1.06;
            letter-spacing: -0.01em;
            color: var(--hr-ink);
        }
        html.es-anim #hr .hr-mask-3 .es-mask-line { animation-delay: 0.42s; }
        .hr-hero-lede { max-width: 35rem; margin-top: 1.5rem; }
        .hr-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.1rem 2rem; margin-top: 1.9rem; }
        .hr-foot { max-width: 32rem; margin-top: 1.3rem; }
        .hr-aside-note { position: relative; max-width: 26rem; padding-top: 1.1rem; }
        .hr-aside-note::before { content: ""; position: absolute; top: 0; left: 0; width: 3rem; height: 1px; background: var(--hr-brass); }

        /* The program card */
        .hr-program-wrap { min-width: 0; }
        .hr-program {
            container-type: inline-size;
            width: min(100%, 29rem);
            margin-inline: auto;
            padding: 2.7rem 2.5rem 2.3rem;
            text-align: center;
            rotate: 0.6deg;
        }
        @media (max-width: 480px) { .hr-program { padding-inline: 1.9rem; } }
        .hr-sun-note {
            position: absolute;
            top: 1.75rem;
            display: grid;
            gap: 0.15rem;
            font-family: var(--hr-display);
            font-size: 0.54rem;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            line-height: 1.4;
            color: var(--hr-ink-3);
            white-space: nowrap;
        }
        .hr-sun-note b { font-weight: 400; font-size: 0.86rem; letter-spacing: 0.06em; color: var(--hr-ink-2); font-variant-numeric: oldstyle-nums; }
        .hr-sun-note-a { left: 1.8rem; text-align: start; }
        .hr-sun-note-b { right: 1.8rem; text-align: end; }
        @media (max-width: 420px) { .hr-sun-note { display: none; } }
        .hr-crest { display: block; width: 6.4rem; height: 6.4rem; margin: 0 auto 1.1rem; overflow: visible; }
        .hr-crest circle { fill: none; stroke: var(--hr-brass); stroke-width: 0.8; }
        .hr-crest .hr-crest-ring { font-family: var(--hr-display); font-size: 8.4px; letter-spacing: 0.2em; text-transform: uppercase; fill: var(--hr-bronze); }
        .hr-crest .hr-crest-mono { font-family: var(--hr-display); font-size: 27px; letter-spacing: 0.04em; fill: var(--hr-ink); }
        .hr-program-kicker { font-family: var(--hr-display); font-size: 0.68rem; letter-spacing: 0.34em; text-transform: uppercase; color: var(--hr-bronze); }
        .hr-program-name { margin: 0.35rem 0 1.15rem; font-family: var(--hr-display); font-size: clamp(2rem, 12.5cqi, 3.1rem); line-height: 1.05; color: var(--hr-ink); text-shadow: 0 1px 0 var(--hr-hi); }
        .hr-program-date { margin: 1.15rem 0 0.4rem; font-family: var(--hr-display); font-size: 0.72rem; letter-spacing: 0.26em; text-transform: uppercase; color: var(--hr-ink-2); }
        .hr-prog { display: grid; grid-template-columns: 3.2rem minmax(0, 1fr) auto; column-gap: 0.9rem; margin-top: 0.6rem; text-align: start; }
        .hr-prog li { display: grid; grid-column: 1 / -1; grid-template-columns: 3.2rem minmax(0, 1fr) auto; column-gap: 0.9rem; align-items: baseline; padding-block: 0.8rem 0.7rem; border-bottom: 1px solid var(--hr-line); }
        @supports (grid-template-columns: subgrid) {
            .hr-prog { grid-template-columns: auto minmax(0, 1fr) auto; }
            .hr-prog li { grid-template-columns: subgrid; }
        }
        .hr-prog li:first-child { border-top: 1px solid var(--hr-line); }
        .hr-prog-time { font-family: var(--hr-display); font-size: 0.95rem; letter-spacing: 0.04em; color: var(--hr-bronze); font-variant-numeric: oldstyle-nums tabular-nums; }
        .hr-prog-what { min-width: 0; }
        .hr-prog-what b { display: block; font-family: var(--hr-serif); font-weight: 400; font-size: 1.32rem; line-height: 1.2; color: var(--hr-ink); }
        .hr-prog-what small { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.15rem; font-size: 0.78rem; color: var(--hr-ink-3); }
        .hr-prog-join { font-family: var(--hr-display); font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--hr-ink-3); white-space: nowrap; }
        .hr-prog-join.is-kept { color: var(--hr-bronze); }
        .hr-pip { flex: none; display: inline-block; width: 0.55rem; height: 0.55rem; border-radius: 50%; box-shadow: 0 0 0 1px var(--hr-stock), 0 0 0 2px var(--hr-line); }
        .hr-program-url { margin-top: 1.2rem; font-family: var(--hr-display); font-size: clamp(0.72rem, 3.6cqi, 0.86rem); letter-spacing: 0.1em; color: var(--hr-ink-2); }

        /* The horizon, still: a few flat bands, a low sun, light on the water. */
        .hr-sea { position: absolute; inset: auto 0 0 0; height: 12.5rem; pointer-events: none; }
        .hr-sea-sky { position: absolute; inset: 0 0 56% 0; overflow: hidden; background: linear-gradient(to bottom, rgba(224, 142, 69, 0) 0%, rgba(224, 142, 69, 0.16) 100%); }
        .hr-sun {
            position: absolute;
            left: 50%;
            bottom: -3.3rem;
            width: 6.6rem;
            height: 6.6rem;
            margin-left: -3.3rem;
            border-radius: 50%;
            background: var(--hr-amber);
            box-shadow: 0 0 0 1.5rem rgba(224, 142, 69, 0.1), 0 0 3.5rem 2.6rem rgba(224, 142, 69, 0.12);
        }
        .hr-sea-water {
            position: absolute;
            inset: 44% 0 0 0;
            background: linear-gradient(to bottom, #fbe7c0 0 2px, #9ccbc8 2px 22%, #5aabad 22% 46%, #2f8f95 46% 72%, #166a73 72% 100%);
        }
        .hr-glitter {
            position: absolute;
            left: 50%;
            top: 3px;
            bottom: 0;
            width: 12rem;
            margin-left: -6rem;
            background: repeating-linear-gradient(to bottom, rgba(255, 236, 198, 0.95) 0 3px, transparent 3px 10px);
            clip-path: polygon(36% 0, 64% 0, 100% 100%, 0 100%);
            -webkit-mask-image: linear-gradient(to bottom, #000 0%, rgba(0, 0, 0, 0.25) 78%, transparent 100%);
            mask-image: linear-gradient(to bottom, #000 0%, rgba(0, 0, 0, 0.25) 78%, transparent 100%);
        }
        @media (min-width: 1000px) {
            .hr-sun { left: 46%; }
            .hr-glitter { left: 46%; }
        }
        .dark .hr-sea-sky { background: linear-gradient(to bottom, rgba(243, 236, 221, 0) 0%, rgba(243, 236, 221, 0.05) 100%); }
        .dark .hr-sun { background: #efe6d2; box-shadow: 0 0 0 1.5rem rgba(239, 230, 210, 0.05), 0 0 3.5rem 2.6rem rgba(239, 230, 210, 0.07); }
        .dark .hr-sea-water { background: linear-gradient(to bottom, #6f8f92 0 2px, #17434b 2px 22%, #12363d 22% 46%, #0d2a30 46% 72%, #091e23 72% 100%); }
        .dark .hr-glitter { background: repeating-linear-gradient(to bottom, rgba(243, 236, 221, 0.7) 0 3px, transparent 3px 10px); }
        /* Lanterns on the terrace: warm dots along the lower edge, lit at night. */
        .hr-lanterns {
            position: absolute;
            inset: auto 0 1.1rem 0;
            height: 1.2rem;
            background: radial-gradient(circle at 50% 50%, #ffd596 0 2.5px, rgba(255, 190, 110, 0.4) 3px 6px, rgba(255, 190, 110, 0) 9px) 0 50% / 4.6rem 100% repeat-x;
            opacity: 0;
        }
        .dark .hr-lanterns,
        .hr-fin .hr-lanterns { opacity: 1; }
        html.es-anim .dark #hr .hr-lanterns,
        html.es-anim #hr .hr-fin .hr-lanterns { animation: hr-lantern 5.5s ease-in-out infinite; }
        @keyframes hr-lantern { 0%, 100% { opacity: 1; } 42% { opacity: 0.82; } 58% { opacity: 0.94; } }

        /* ---------------------------------------------------------------
           101 The standing week: the programme sheet
           --------------------------------------------------------------- */
        .hr-sheet { padding: clamp(1.9rem, 4vw, 3rem) clamp(1.5rem, 4vw, 3.2rem); }
        .hr-sheet-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.5rem 1.5rem; margin-bottom: 1.5rem; padding-bottom: 1.1rem; border-bottom: 1px solid var(--hr-brass); }
        .hr-sheet-head b { font-family: var(--hr-display); font-weight: 400; font-size: 1.5rem; color: var(--hr-ink); }
        .hr-table { width: 100%; border-collapse: collapse; }
        .hr-table thead th { padding: 0 0.75rem 0.85rem; font-family: var(--hr-display); font-weight: 400; font-size: 0.66rem; letter-spacing: 0.26em; text-transform: uppercase; text-align: start; color: var(--hr-bronze); border-bottom: 1px solid var(--hr-line); }
        .hr-table tbody tr { border-bottom: 1px solid var(--hr-line); transition: opacity 0.3s ease; }
        .hr-table tbody tr:last-child { border-bottom: 0; }
        .hr-table tbody th { padding: 1rem 0.75rem; font-family: var(--hr-serif); font-weight: 400; font-size: 1.4rem; line-height: 1.2; text-align: start; color: var(--hr-ink); }
        .hr-table td { padding: 1rem 0.75rem; vertical-align: middle; }
        .hr-table thead th:first-child,
        .hr-table tbody th { padding-inline-start: 0; }
        .hr-table thead th:last-child,
        .hr-table td:last-child { padding-inline-end: 0; }
        @media (hover: hover) {
            #hr .hr-table tbody:has(tr:hover) tr:not(:hover) { opacity: 0.5; }
        }
        .hr-strand { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.86rem; color: var(--hr-ink-2); }
        .hr-dow { display: inline-flex; gap: 3px; }
        .hr-dow span {
            display: grid;
            place-items: center;
            width: 1.5rem;
            height: 1.5rem;
            font-family: var(--hr-display);
            font-size: 0.62rem;
            color: var(--hr-ink-3);
            background: var(--hr-stock-2);
            box-shadow: inset 0 0 0 1px var(--hr-line);
        }
        .hr-dow .is-on { background: #0e4a52; color: #f7f2e8; box-shadow: none; }
        .dark .hr-dow .is-on { background: #d2ad6b; color: #0c1f24; }
        .hr-time { font-family: var(--hr-display); font-size: 1.02rem; letter-spacing: 0.04em; color: var(--hr-ink); white-space: nowrap; font-variant-numeric: oldstyle-nums tabular-nums; }
        .hr-join { display: inline-block; padding: 0.24rem 0.55rem 0.18rem; font-family: var(--hr-display); font-size: 0.62rem; letter-spacing: 0.2em; text-transform: uppercase; line-height: 1.2; color: var(--hr-ink-2); box-shadow: inset 0 0 0 1px var(--hr-line); white-space: nowrap; }
        .hr-join.is-kept { color: var(--hr-bronze); box-shadow: inset 0 0 0 1px var(--hr-brass); }
        .hr-join.is-ticket { background: #0e4a52; color: #f7f2e8; box-shadow: none; }
        .dark .hr-join.is-ticket { background: #d2ad6b; color: #0c1f24; }
        .hr-join-note { display: block; margin-top: 0.3rem; font-size: 0.8rem; color: var(--hr-ink-3); }
        .hr-sheet-foot { margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--hr-line); padding-inline-end: max(0px, calc(100% - 52rem)); }
        @media (max-width: 759px) {
            /* The column heads stay for assistive tech (the roles are spelled out in the
               markup) and leave the layout; each row then names its own parts. */
            .hr-table thead,
            .hr-table thead tr { display: block; }
            .hr-table thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
            .hr-table thead th { display: block; position: absolute; width: 1px; height: 1px; padding: 0; border: 0; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
            .hr-table,
            .hr-table tbody { display: block; }
            .hr-table tbody tr { display: grid; grid-template-columns: minmax(0, 1fr) auto; grid-template-areas: "name time" "strand strand" "days join"; gap: 0.55rem 1rem; align-items: center; padding-block: 1.1rem; }
            .hr-table tbody th,
            .hr-table td { padding: 0; }
            .hr-table tbody th { grid-area: name; }
            .hr-td-strand { grid-area: strand; }
            .hr-td-days { grid-area: days; }
            .hr-td-time { grid-area: time; text-align: end; }
            .hr-td-join { grid-area: join; text-align: end; }
        }

        /* Four weeks of one activity, entered and published */
        .hr-month { margin-top: 1.5rem; padding: clamp(1.75rem, 4vw, 2.75rem) clamp(1.5rem, 4vw, 3.2rem); }
        .hr-month-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.4rem 1.5rem; margin-bottom: 1.6rem; }
        .hr-month-head h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.45rem; line-height: 1.2; color: var(--hr-ink); }
        .hr-month-head span { font-family: var(--hr-display); font-size: 0.72rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--hr-ink-3); }
        .hr-weeks { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem 1.25rem; margin-top: 0.8rem; }
        @media (min-width: 760px) { .hr-weeks { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem; } }
        .hr-wk-cells { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 3px; }
        .hr-wk-cells span { aspect-ratio: 1; background: var(--hr-stock-2); box-shadow: inset 0 0 0 1px var(--hr-line); }
        .hr-wk-cells .is-on { background: #0e4a52; box-shadow: none; }
        .dark .hr-wk-cells .is-on { background: #d2ad6b; }
        .hr-wk-label { margin-top: 0.45rem; font-family: var(--hr-display); font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--hr-ink-3); }
        .hr-month .hr-fine { margin-top: 0.9rem; }
        .hr-month-rule { margin-block: 1.75rem; }
        .hr-month-foot { margin-top: 1.6rem; padding-top: 1.25rem; border-top: 1px solid var(--hr-line); padding-inline-end: max(0px, calc(100% - 52rem)); }

        /* House notes: three columns, hairlines between */
        .hr-notes { display: grid; gap: 2rem; margin-top: clamp(2.5rem, 5vw, 3.75rem); }
        @media (min-width: 860px) { .hr-notes { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0; } }
        .hr-note { position: relative; padding-top: 1.4rem; border-top: 1px solid var(--hr-brass); }
        @media (min-width: 860px) {
            .hr-note { padding: 1.5rem 2rem 0; }
            .hr-note:first-child { padding-inline-start: 0; }
            .hr-note:last-child { padding-inline-end: 0; }
            .hr-note + .hr-note::before { content: ""; position: absolute; top: 1.5rem; bottom: 0; left: 0; width: 1px; background: var(--hr-line); }
        }
        .hr-note-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.7rem; margin-bottom: 0.7rem; }
        .hr-note h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.3rem; line-height: 1.25; color: var(--hr-ink); }
        .hr-note p { font-size: 0.98rem; }

        /* ---------------------------------------------------------------
           102 The sleeve
           --------------------------------------------------------------- */
        .hr-split { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem; align-items: center; }
        @media (min-width: 1000px) { .hr-split { grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr); gap: clamp(3rem, 6vw, 5.5rem); } }
        .hr-points { display: grid; gap: 1.5rem; margin-top: 2.25rem; }
        .hr-point { position: relative; padding-inline-start: 1.7rem; }
        .hr-point::before { content: ""; position: absolute; left: 0.15rem; top: 0.62rem; width: 0.48rem; height: 0.48rem; rotate: 45deg; background: var(--hr-brass); }
        .hr-point-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem 0.7rem; }
        .hr-point h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.22rem; line-height: 1.3; color: var(--hr-ink); }
        .hr-point p { margin-top: 0.4rem; font-size: 0.98rem; }

        .hr-sleeve-wrap { position: relative; width: min(100%, 27rem); margin-inline: auto; padding-top: 4.2rem; filter: drop-shadow(0 1.3rem 1.1rem var(--hr-shadow)); }
        /* Restated under the page id: the shared reveal ends on `filter: none`, which outranks a plain
           class and took the shadow away as soon as the element was revealed. */
        #hr .hr-sleeve-wrap { filter: drop-shadow(0 1.3rem 1.1rem var(--hr-shadow)); }
        /* The key card, a thumb's width out of its sleeve */
        .hr-keycard {
            position: absolute;
            top: 0;
            left: 9%;
            right: 9%;
            height: 9rem;
            border-radius: 0.7rem;
            background: linear-gradient(135deg, #12565f 0%, #0b3a41 100%);
            box-shadow: inset 0 0 0 1px rgba(233, 207, 146, 0.4), 0 0.8rem 1.4rem -0.8rem var(--hr-shadow);
            rotate: -2deg;
        }
        .hr-keycard::before { content: ""; position: absolute; left: 0; right: 0; top: 1.15rem; height: 1.15rem; background: linear-gradient(90deg, #c9a460, #f0dba6 40%, #b08d50 70%, #dcc48b); }
        .hr-keycard::after { content: "LB"; position: absolute; right: 1.1rem; top: 2.75rem; font-family: var(--hr-display); font-size: 0.9rem; letter-spacing: 0.14em; color: #e2c483; }
        .hr-sleeve {
            padding: 3.1rem 2.2rem 2rem;
            -webkit-mask: radial-gradient(circle 2.3rem at 50% -0.55rem, transparent 97%, #000 100%);
            mask: radial-gradient(circle 2.3rem at 50% -0.55rem, transparent 97%, #000 100%);
        }
        @media (max-width: 480px) { .hr-sleeve { padding-inline: 1.7rem; } }
        .hr-sleeve-top { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 1.25rem; align-items: start; }
        .hr-sleeve-title { margin-top: 0.5rem; font-family: var(--hr-display); font-size: clamp(1.35rem, 5vw, 1.6rem); line-height: 1.15; color: var(--hr-ink); text-wrap: balance; }
        .hr-sleeve .hr-fine { margin-top: 0.7rem; }
        .hr-code { display: grid; grid-template-columns: repeat(9, 1fr); gap: 1px; width: 5.6rem; padding: 0.45rem; background: #fdfaf2; box-shadow: inset 0 0 0 1px rgba(23, 41, 44, 0.25); }
        .hr-code i { aspect-ratio: 1; background: transparent; }
        .hr-code .is-on { background: #17292c; }
        .hr-sleeve-url { margin-top: 1.3rem; font-family: var(--hr-display); font-size: 1.02rem; letter-spacing: 0.06em; color: var(--hr-ink); overflow-wrap: anywhere; }
        .hr-pills { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1.2rem; }
        .hr-pill { padding: 0.3rem 0.65rem 0.24rem; font-family: var(--hr-display); font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; line-height: 1.3; color: var(--hr-ink-2); box-shadow: inset 0 0 0 1px var(--hr-line); }
        .hr-pill.is-own { color: var(--hr-bronze); box-shadow: inset 0 0 0 1px var(--hr-brass); }

        /* ---------------------------------------------------------------
           103 Keeping a place: the desk's book, ruled
           --------------------------------------------------------------- */
        .hr-ledger { display: grid; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 900px) { .hr-ledger { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .hr-page { position: relative; display: flex; flex-direction: column; padding: clamp(1.75rem, 4vw, 2.75rem) clamp(1.5rem, 4vw, 2.9rem); }
        .hr-page + .hr-page { border-top: 1px solid var(--hr-line); }
        @media (min-width: 900px) {
            .hr-page + .hr-page { border-top: 0; }
            /* The gutter: the fold of the book, a shade and a light. */
            .hr-page + .hr-page::before { content: ""; position: absolute; inset: 0 auto 0 0; width: 2.5rem; background: linear-gradient(90deg, var(--hr-shadow), transparent); opacity: 0.35; pointer-events: none; }
            .hr-page:first-child::after { content: ""; position: absolute; inset: 0 0 0 auto; width: 1.5rem; background: linear-gradient(270deg, var(--hr-shadow), transparent); opacity: 0.22; pointer-events: none; }
        }
        .hr-page-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; padding-bottom: 0.95rem; border-bottom: 1px solid var(--hr-brass); }
        .hr-page-head h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.5rem; line-height: 1.2; color: var(--hr-ink); }
        .hr-count { margin-top: 1.4rem; font-family: var(--hr-display); font-size: clamp(3rem, 6vw, 4.2rem); line-height: 1; color: var(--hr-ink); font-variant-numeric: oldstyle-nums; }
        .hr-count span { font-family: var(--hr-serif); font-size: 1.5rem; color: var(--hr-ink-3); }
        .hr-count-sub { margin-top: 0.5rem; font-size: 0.95rem; color: var(--hr-ink-2); }
        .hr-meter { height: 3px; margin-top: 1.1rem; background: var(--hr-line); }
        .hr-meter-fill { height: 100%; background: var(--hr-brass); }
        /* The ruled lines themselves: one per place, a name on the ones taken. */
        .hr-lines { columns: var(--cols, 2); column-gap: 1.5rem; margin-block: 1.5rem 1.6rem; }
        .hr-lines li { display: flex; align-items: flex-end; gap: 0.55rem; height: 1.42rem; break-inside: avoid; border-bottom: 1px solid var(--hr-line); }
        .hr-lines small { flex: none; width: 1.15rem; font-family: var(--hr-display); font-size: 0.56rem; line-height: 1.5; color: var(--hr-ink-3); }
        .hr-lines i { height: 0.22rem; margin-bottom: 0.34rem; border-radius: 0.2rem; background: var(--hr-sea); opacity: 0.55; width: var(--w, 60%); }
        .hr-page-note { margin-top: auto; padding-top: 1.2rem; border-top: 1px solid var(--hr-line); font-size: 0.98rem; }

        /* ---------------------------------------------------------------
           104 The strands
           --------------------------------------------------------------- */
        .hr-strands { padding: clamp(2.75rem, 5vw, 3.25rem) clamp(1.5rem, 4vw, 2.75rem) clamp(1.75rem, 4vw, 2.5rem); }
        /* Five ribbon markers hanging from the head of the card, one per strand. */
        .hr-ribbons { position: absolute; top: -0.6rem; right: 2rem; display: flex; gap: 0.5rem; align-items: flex-start; }
        .hr-ribbons i { display: block; width: 0.8rem; height: var(--h, 3rem); clip-path: polygon(0 0, 100% 0, 100% 100%, 50% calc(100% - 0.45rem), 0 100%); box-shadow: inset 0 0.6rem 0.5rem -0.4rem rgba(0, 0, 0, 0.35); }
        .hr-strands-kicker { margin-bottom: 1.2rem; padding-inline-end: 6.5rem; }
        @media (max-width: 480px) { .hr-ribbons { right: 1.25rem; gap: 0.4rem; } .hr-ribbons i { width: 0.7rem; } }
        .hr-strand-row { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 0.9rem; padding-block: 0.95rem; border-bottom: 1px solid var(--hr-line); }
        .hr-strand-row:first-of-type { border-top: 1px solid var(--hr-line); }
        .hr-enamel { width: 1.05rem; height: 1.05rem; border-radius: 50%; box-shadow: inset 0 0.15rem 0.2rem rgba(255, 255, 255, 0.35), inset 0 -0.15rem 0.2rem rgba(0, 0, 0, 0.25), 0 0 0 2px var(--hr-stock), 0 0 0 3px var(--hr-brass); }
        .hr-strand-row b { font-family: var(--hr-serif); font-weight: 400; font-size: 1.4rem; line-height: 1.2; color: var(--hr-ink); }
        .hr-strand-row span { font-family: var(--hr-display); font-size: 0.82rem; letter-spacing: 0.08em; color: var(--hr-ink-3); }
        .hr-strands .hr-fine { margin-top: 1.4rem; }
        .hr-aside { margin-top: 2rem; padding: 1.4rem 1.5rem 1.3rem; }
        .hr-aside h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.15rem; color: var(--hr-ink); }
        .hr-aside p { margin-top: 0.5rem; font-size: 0.98rem; }
        .hr-free-line { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.8rem; margin-top: 1.6rem; font-size: 0.95rem; }

        /* ---------------------------------------------------------------
           105 Behind the desk: two doors, a hanger on each knob
           --------------------------------------------------------------- */
        .hr-doors { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
        @media (min-width: 900px) { .hr-doors { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2rem; } }
        .hr-door {
            position: relative;
            padding: 4.4rem clamp(0.9rem, 3vw, 2.25rem) clamp(1.25rem, 3vw, 2.25rem);
            border-radius: 3px;
            background: var(--hr-band);
            box-shadow: inset 0 0 0 1px var(--hr-line), inset 0 0 0 0.55rem var(--hr-band), inset 0 0 0 calc(0.55rem + 1px) var(--hr-lo), inset 0 0 0 calc(0.55rem + 2px) var(--hr-hi);
        }
        .hr-door-plate {
            position: absolute;
            top: 1.5rem;
            left: 50%;
            translate: -50% 0;
            padding: 0.42rem 1.1rem 0.34rem;
            border-radius: 2px;
            background: linear-gradient(100deg, #d9bf83, #f0dfb4 45%, #d0b071);
            box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.45), 0 1px 2px rgba(0, 0, 0, 0.25);
            font-family: var(--hr-display);
            font-size: 0.64rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #3d2c0b;
            white-space: nowrap;
        }
        .hr-hanger {
            position: relative;
            padding: 6.6rem clamp(1.25rem, 3vw, 2.1rem) clamp(1.5rem, 3vw, 2.1rem);
            border-radius: 1.4rem 1.4rem 0.4rem 0.4rem;
            box-shadow: 0 1.6rem 2.4rem -1.6rem var(--hr-shadow);
            -webkit-mask: radial-gradient(circle 2.1rem at 50% 3.3rem, transparent 97%, #000 100%);
            mask: radial-gradient(circle 2.1rem at 50% 3.3rem, transparent 97%, #000 100%);
        }
        .hr-hanger-desk { background: linear-gradient(180deg, #11545d 0%, #0d444c 100%); color: #cddad8; }
        .hr-hanger-guest { background-color: #fdfaf2; background-image: var(--hr-grain); color: #3c5054; }
        .hr-hang { position: relative; filter: drop-shadow(0 1.1rem 1rem var(--hr-shadow)); }
        /* The knob the hanger hangs on, seen through its cut-out. */
        .hr-knob {
            position: absolute;
            z-index: 2;
            top: 1.75rem;
            left: 50%;
            width: 3.1rem;
            height: 3.1rem;
            margin-left: -1.55rem;
            border-radius: 50%;
            background: var(--hr-brass-metal);
            box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.55), inset 0 0.5rem 0.6rem -0.3rem rgba(255, 246, 220, 0.55), 0 0.5rem 0.7rem -0.2rem rgba(0, 0, 0, 0.45);
            transition: --hr-spec 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .hr-knob::after { content: ""; position: absolute; inset: 34%; border-radius: 50%; background: radial-gradient(circle at 40% 35%, #f7e9c1, #a78547 70%); box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.4); }
        .hr-door:hover .hr-knob { --hr-spec: 140deg; }
        .hr-hang-title { font-family: var(--hr-display); font-size: 0.78rem; letter-spacing: 0.3em; text-transform: uppercase; text-align: center; }
        .hr-hanger-desk .hr-hang-title { color: #e9cf92; }
        .hr-hanger-guest .hr-hang-title { color: #73531f; }
        .hr-hang-rule { height: 1px; margin: 1.1rem 0 0.4rem; }
        .hr-hanger-desk .hr-hang-rule { background: rgba(233, 207, 146, 0.45); }
        .hr-hanger-guest .hr-hang-rule { background: rgba(115, 83, 31, 0.4); }
        .hr-hang-list li { position: relative; margin-inline: -0.75rem; padding: 0.95rem 0.75rem 0.9rem; border-radius: 2px; transition: background-color 0.3s ease, box-shadow 0.3s ease; }
        .hr-hang-list li + li::before { content: ""; position: absolute; left: 0.75rem; right: 0.75rem; top: 0; height: 1px; }
        .hr-hanger-desk .hr-hang-list li + li::before { background: rgba(243, 236, 221, 0.14); }
        .hr-hanger-guest .hr-hang-list li + li::before { background: rgba(14, 74, 82, 0.14); }
        .hr-hang-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem 0.65rem; }
        .hr-hang-list h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.12rem; line-height: 1.3; }
        .hr-hanger-desk h3 { color: #f7f2e8; }
        .hr-hanger-guest h3 { color: #17292c; }
        .hr-hang-list p { margin-top: 0.3rem; font-size: 0.94rem; line-height: 1.6; }
        #hr .hr-hanger-desk .hr-tag { border-color: rgba(233, 207, 146, 0.7); color: #e9cf92; background: transparent; }
        #hr .hr-hanger-desk .hr-tag-pro { border-color: #e2c483; background: #e2c483; color: #17292c; }
        #hr .hr-hanger-guest .hr-tag { border-color: #a8854a; color: #73531f; background: transparent; }
        /* A line on one hanger and its other side on the other light together. */
        @media (hover: hover) {
            #hr .hr-doors:has([data-k="draft"]:hover) [data-k="draft"],
            #hr .hr-doors:has([data-k="dinner"]:hover) [data-k="dinner"],
            #hr .hr-doors:has([data-k="date"]:hover) [data-k="date"],
            #hr .hr-doors:has([data-k="calendar"]:hover) [data-k="calendar"] { box-shadow: inset 0 0 0 1px #c9a460; }
            #hr .hr-doors:has([data-k="draft"]:hover) .hr-hanger-desk [data-k="draft"],
            #hr .hr-doors:has([data-k="dinner"]:hover) .hr-hanger-desk [data-k="dinner"],
            #hr .hr-doors:has([data-k="date"]:hover) .hr-hanger-desk [data-k="date"],
            #hr .hr-doors:has([data-k="calendar"]:hover) .hr-hanger-desk [data-k="calendar"] { background: rgba(233, 207, 146, 0.12); }
            #hr .hr-doors:has([data-k="draft"]:hover) .hr-hanger-guest [data-k="draft"],
            #hr .hr-doors:has([data-k="dinner"]:hover) .hr-hanger-guest [data-k="dinner"],
            #hr .hr-doors:has([data-k="date"]:hover) .hr-hanger-guest [data-k="date"],
            #hr .hr-doors:has([data-k="calendar"]:hover) .hr-hanger-guest [data-k="calendar"] { background: rgba(168, 133, 74, 0.13); }
        }
        .hr-pair-hint { display: none; }
        @media (hover: hover) and (min-width: 900px) {
            .hr-pair-hint { display: block; margin-top: 1.75rem; text-align: center; font-family: var(--hr-display); font-size: 0.66rem; letter-spacing: 0.26em; text-transform: uppercase; color: var(--hr-ink-3); }
        }
        .hr-desk-foot { max-width: 44rem; margin: clamp(2.25rem, 4vw, 3rem) auto 0; text-align: center; font-size: 0.98rem; }

        /* ---------------------------------------------------------------
           106 Who it is for: six luggage labels
           --------------------------------------------------------------- */
        .hr-stays { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem 2.5rem; }
        @media (min-width: 700px) { .hr-stays { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .hr-stays { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3.5rem 3rem; } }
        .hr-stay { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 1.4rem; align-items: start; }
        .hr-stay-art { display: grid; place-items: center; width: 8.8rem; height: 9rem; }
        .hr-stay h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.4rem; line-height: 1.2; color: var(--hr-ink); }
        .hr-stay p { margin-top: 0.55rem; font-size: 0.98rem; }
        .hr-stay a { display: inline-block; margin-top: 0.9rem; }
        @media (max-width: 420px) {
            .hr-stay { grid-template-columns: minmax(0, 1fr); gap: 1rem; }
            .hr-stay-art { justify-self: start; }
        }
        .hr-sticker {
            position: relative;
            display: grid;
            place-items: center;
            align-content: center;
            gap: 0.2rem;
            width: 8.16rem;
            aspect-ratio: 1;
            text-align: center;
            font-family: var(--hr-display);
            text-transform: uppercase;
            background: var(--bg);
            color: var(--fg);
            rotate: var(--r, -4deg);
            box-shadow: inset 0 0 0 0.3rem var(--bg), inset 0 0 0 calc(0.3rem + 1px) var(--fg), 0 0.9rem 1.2rem -0.8rem var(--hr-shadow);
            transition: rotate 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dark .hr-sticker { box-shadow: inset 0 0 0 0.3rem var(--bg), inset 0 0 0 calc(0.3rem + 1px) var(--fg), 0 0 0 1px rgba(243, 236, 221, 0.28), 0 0.9rem 1.2rem -0.8rem var(--hr-shadow); }
        .hr-stay:hover .hr-sticker { rotate: 0deg; }
        .hr-sticker small { font-size: 0.54rem; letter-spacing: 0.22em; line-height: 1.3; max-width: 6.2rem; }
        .hr-sticker b { font-weight: 400; font-size: 2rem; line-height: 1; letter-spacing: 0.05em; }
        .hr-sticker-oval { width: 6.6rem; aspect-ratio: 3 / 4; border-radius: 50%; }
        .hr-sticker-round { border-radius: 50%; }
        .hr-sticker-round svg { position: absolute; inset: 0.55rem; width: calc(100% - 1.1rem); height: calc(100% - 1.1rem); }
        .hr-sticker-round text { font-family: var(--hr-display); font-size: 10.5px; letter-spacing: 0.24em; text-transform: uppercase; fill: currentColor; }
        .hr-sticker-plaque { width: 8.8rem; aspect-ratio: 4 / 3; border-radius: 0.35rem; }
        .hr-sticker-arch { width: 7rem; aspect-ratio: 4 / 5; border-radius: 3.5rem 3.5rem 0.4rem 0.4rem; }
        .hr-sticker-diamond { width: 6.2rem; border-radius: 0.3rem; rotate: 45deg; }
        .hr-stay:hover .hr-sticker-diamond { rotate: 45deg; }
        .hr-sticker > span { display: grid; justify-items: center; gap: 0.2rem; }
        .hr-sticker-diamond > span { rotate: -45deg; }
        .hr-sticker-stamp {
            border: 0.34rem solid transparent;
            box-shadow: inset 0 0 0 0.26rem var(--bg), inset 0 0 0 calc(0.26rem + 1px) var(--fg);
            -webkit-mask: linear-gradient(#000 0 0) padding-box, radial-gradient(circle 0.2rem at 50% 50%, transparent 96%, #000 100%) -0.34rem -0.34rem / 0.68rem 0.68rem round;
            mask: linear-gradient(#000 0 0) padding-box, radial-gradient(circle 0.2rem at 50% 50%, transparent 96%, #000 100%) -0.34rem -0.34rem / 0.68rem 0.68rem round;
        }

        /* ---------------------------------------------------------------
           107 How it works: three keys on the rack
           --------------------------------------------------------------- */
        .hr-keys { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem; }
        @media (min-width: 860px) {
            .hr-keys { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2.5rem; padding-top: 0.5rem; }
            /* The rail the keys hang from */
            .hr-keys::before { content: ""; position: absolute; top: 0; left: 4%; right: 4%; height: 0.36rem; border-radius: 0.2rem; background: linear-gradient(180deg, #e9d199, #a8854a 55%, #7f6230); box-shadow: 0 0.3rem 0.4rem -0.15rem var(--hr-shadow); }
        }
        .hr-key { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 1.4rem; align-items: start; align-content: start; }
        @media (min-width: 860px) { .hr-key { grid-template-columns: minmax(0, 1fr); justify-items: center; text-align: center; gap: 1.5rem; } }
        .hr-keyfob { position: relative; display: grid; justify-items: center; padding-top: 2rem; }
        .hr-keyfob::before { content: ""; position: absolute; top: 0; left: 50%; width: 2.4rem; height: 2.9rem; margin-left: -1.2rem; border-radius: 50%; border: 3px solid var(--hr-brass); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), 0 2px 2px rgba(0, 0, 0, 0.2); }
        .hr-keyfob b {
            position: relative;
            display: grid;
            place-items: center;
            width: 5.4rem;
            height: 7rem;
            padding-top: 1rem;
            border-radius: 50%;
            background: var(--hr-brass-metal);
            font-family: var(--hr-display);
            font-weight: 400;
            font-size: 2rem;
            letter-spacing: 0.04em;
            color: #4e390f;
            text-shadow: 0 1px 0 rgba(255, 246, 220, 0.8);
            box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.6), inset 0 0 0 5px rgba(255, 240, 200, 0.28), inset 0 0 0 6px rgba(78, 57, 15, 0.35), 0 1rem 1.3rem -0.7rem var(--hr-shadow);
            transition: --hr-spec 1.2s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .hr-keyfob b::before { content: ""; position: absolute; top: 0.7rem; left: 50%; width: 0.8rem; height: 0.8rem; margin-left: -0.4rem; border-radius: 50%; background: var(--hr-under); box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.5); }
        .hr-key:hover .hr-keyfob b { --hr-spec: 130deg; }
        .hr-key h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.5rem; line-height: 1.2; color: var(--hr-ink); }
        .hr-key p { margin-top: 0.6rem; max-width: 22rem; font-size: 0.98rem; }

        /* ---------------------------------------------------------------
           The directory: key features
           --------------------------------------------------------------- */
        .hr-dir-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.25rem 4rem; align-items: start; }
        @media (min-width: 960px) { .hr-dir-grid { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .hr-dir { border-top: 1px solid var(--hr-brass); }
        .hr-dir-row { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr) auto; align-items: center; gap: 1rem; padding: 1.25rem 0.25rem 1.15rem; border-bottom: 1px solid var(--hr-line); color: var(--hr-ink); transition: padding 0.3s ease, background-color 0.3s ease; }
        .hr-dir-row:hover { padding-inline: 1rem 0.75rem; background: var(--hr-stock); }
        .hr-dir-no { font-family: var(--hr-display); font-size: 0.86rem; letter-spacing: 0.1em; color: var(--hr-bronze); font-variant-numeric: oldstyle-nums; }
        .hr-dir-row strong { display: block; font-family: var(--hr-display); font-weight: 400; font-size: 1.32rem; line-height: 1.2; }
        .hr-dir-row small { display: block; margin-top: 0.25rem; font-size: 0.95rem; color: var(--hr-ink-2); }
        .hr-dir-row svg { width: 1.2rem; height: 1.2rem; color: var(--hr-bronze); transition: translate 0.3s ease; }
        .hr-dir-row:hover svg { translate: 0.3rem 0; }
        .hr-dir-more { margin-top: 1.75rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and prices; only the stock changes.
           --------------------------------------------------------------- */
        #hr .hr-plans > section { background: var(--hr-ground); }
        #hr .hr-plans h2 { font-family: var(--hr-display); font-weight: 400; font-size: clamp(2rem, 3.6vw, 2.9rem); line-height: 1.15; letter-spacing: -0.005em; color: var(--hr-ink); }
        #hr .hr-plans h2 + p { color: var(--hr-ink-2); font-size: 1.0625rem; }
        #hr .hr-plans .grid > div { background-color: var(--hr-stock); background-image: var(--hr-grain); border: 0; border-radius: 3px; color: var(--hr-ink); box-shadow: 0 0 0 1px var(--hr-line), inset 0 0 0 0.55rem var(--hr-stock), inset 0 0 0 calc(0.55rem + 1px) var(--hr-lo), inset 0 0 0 calc(0.55rem + 2px) var(--hr-hi), 0 1.6rem 2.4rem -1.8rem var(--hr-shadow); padding: 2.2rem 2rem 1.9rem; }
        #hr .hr-plans .grid > div:nth-child(2) { box-shadow: 0 0 0 1px var(--hr-brass), inset 0 0 0 0.55rem var(--hr-stock), inset 0 0 0 calc(0.55rem + 1px) var(--hr-brass), inset 0 0 0 calc(0.55rem + 2px) var(--hr-hi), 0 1.6rem 2.4rem -1.8rem var(--hr-shadow); }
        #hr .hr-plans .grid > div span,
        #hr .hr-plans .grid > div p,
        #hr .hr-plans .grid > div li { color: var(--hr-ink-2); }
        #hr .hr-plans .grid > div .text-3xl { font-family: var(--hr-display); font-weight: 400; font-size: 2.6rem; color: var(--hr-ink); }
        #hr .hr-plans .grid > div .uppercase { font-family: var(--hr-display); font-weight: 400; letter-spacing: 0.26em; color: var(--hr-bronze); }
        #hr .hr-plans .grid > div .rounded-full { border-radius: 1px; background: transparent; border: 1px solid var(--hr-brass); color: var(--hr-bronze); font-family: var(--hr-display); font-weight: 400; letter-spacing: 0.18em; }
        #hr .hr-plans .grid > div svg { color: var(--hr-brass); }
        #hr .hr-plans a.font-medium { color: var(--hr-ink); border-bottom: 1px solid var(--hr-brass); padding-bottom: 0.2rem; }
        #hr .hr-plans a.rounded-2xl { border-radius: 2px; background: var(--hr-btn); color: var(--hr-btn-ink); font-family: var(--hr-display); font-weight: 400; font-size: 0.86rem; letter-spacing: 0.2em; text-transform: uppercase; box-shadow: inset 0 0 0 4px var(--hr-btn), inset 0 0 0 5px rgba(233, 207, 146, 0.6), 0 1rem 1.5rem -1rem var(--hr-shadow); }
        .dark #hr .hr-plans a.rounded-2xl { box-shadow: inset 0 0 0 4px var(--hr-btn), inset 0 0 0 5px rgba(12, 31, 36, 0.45), 0 1rem 1.5rem -1rem var(--hr-shadow); }

        #hr .hr-keep > section { background: var(--hr-band); border-top: 1px solid var(--hr-line); }
        #hr .hr-keep h2 { font-family: var(--hr-display); font-weight: 400; font-size: clamp(1.8rem, 3vw, 2.4rem); color: var(--hr-ink); }
        #hr .hr-keep p.uppercase { font-family: var(--hr-display); font-weight: 400; letter-spacing: 0.3em; color: var(--hr-bronze); }
        #hr .hr-keep .grid > a { background-color: var(--hr-stock); background-image: var(--hr-grain); border: 0; border-radius: 3px; box-shadow: 0 0 0 1px var(--hr-line); }
        #hr .hr-keep .grid > a:hover { box-shadow: 0 0 0 1px var(--hr-brass), 0 1.4rem 2rem -1.4rem var(--hr-shadow); }
        #hr .hr-keep .grid > a > span:first-child { display: none; }
        #hr .hr-keep .grid > a h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.15rem; color: var(--hr-ink); }
        #hr .hr-keep .grid > a p { color: var(--hr-ink-2); }
        #hr .hr-keep .grid > a > span:last-child,
        #hr .hr-keep a.self-start { color: var(--hr-bronze); }

        /* ---------------------------------------------------------------
           Down the corridor: related pages as door plates
           --------------------------------------------------------------- */
        .hr-corridor-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: end; gap: 1.25rem; margin-bottom: 2.25rem; }
        .hr-plates { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        @media (min-width: 620px) { .hr-plates { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; } }
        @media (min-width: 1040px) { .hr-plates { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .hr-plate {
            position: relative;
            display: grid;
            gap: 0.3rem;
            padding: 1.5rem 2.6rem 1.35rem;
            border-radius: 3px;
            background: linear-gradient(100deg, #d9bf83 0%, #f0dfb4 42%, #d6b877 72%, #e7d19b 100%);
            color: #2d2008;
            text-align: center;
            box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.5), inset 0 0 0 4px rgba(255, 244, 214, 0.35), inset 0 0 0 5px rgba(78, 57, 15, 0.25), 0 1rem 1.4rem -1rem var(--hr-shadow);
            transition: translate 0.3s ease, box-shadow 0.3s ease;
        }
        .hr-plate:hover { translate: 0 -3px; box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.5), inset 0 0 0 4px rgba(255, 244, 214, 0.55), inset 0 0 0 5px rgba(78, 57, 15, 0.25), 0 1.5rem 1.8rem -1.1rem var(--hr-shadow); }
        /* Two screws */
        .hr-plate::before,
        .hr-plate::after { content: ""; position: absolute; top: 50%; width: 0.55rem; height: 0.55rem; margin-top: -0.28rem; border-radius: 50%; background: radial-gradient(circle at 35% 30%, #fff3cf, #8a6c36 75%); box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.5); }
        .hr-plate::before { left: 0.9rem; }
        .hr-plate::after { right: 0.9rem; }
        .hr-plate small { font-family: var(--hr-display); font-size: 0.6rem; letter-spacing: 0.26em; text-transform: uppercase; color: #3d2c0b; }
        .hr-plate strong { font-family: var(--hr-display); font-weight: 400; font-size: 1.3rem; line-height: 1.2; text-shadow: 0 1px 0 rgba(255, 246, 220, 0.7); }
        #hr .hr-plate:focus-visible { outline-color: var(--hr-ink); }

        /* ---------------------------------------------------------------
           108 Questions: a letter, folded in three
           --------------------------------------------------------------- */
        .hr-letter {
            padding: clamp(2.25rem, 5vw, 3.75rem) clamp(1.4rem, 5vw, 3.75rem) clamp(2rem, 4vw, 3rem);
            background-image:
                linear-gradient(to bottom, transparent calc(33.33% - 1.6rem), var(--hr-fold-hi) 33.33%, var(--hr-fold-lo) 33.33%, transparent calc(33.33% + 2.2rem)),
                linear-gradient(to bottom, transparent calc(66.66% - 1.6rem), var(--hr-fold-hi) 66.66%, var(--hr-fold-lo) 66.66%, transparent calc(66.66% + 2.2rem)),
                var(--hr-grain);
            --hr-fold-lo: rgba(73, 54, 22, 0.075);
            --hr-fold-hi: rgba(255, 255, 255, 0.7);
        }
        .dark .hr-letter { --hr-fold-lo: rgba(0, 0, 0, 0.2); --hr-fold-hi: rgba(255, 255, 255, 0.035); }
        .hr-letter-head { display: grid; justify-items: center; gap: 0.5rem; margin-bottom: 2rem; text-align: center; }
        .hr-letter-head svg { width: 5.2rem; height: 5.2rem; margin-bottom: 0.3rem; }
        .hr-letter-with { font-family: var(--hr-serif); font-style: italic; font-size: 1.15rem; color: var(--hr-ink-3); }
        .hr-qa { border-top: 1px solid var(--hr-brass); }
        .hr-qa details { border-bottom: 1px solid var(--hr-line); }
        .hr-qa summary { display: grid; grid-template-columns: minmax(0, 1fr) 1.2rem; gap: 1.25rem; align-items: start; padding: 1.3rem 0.1rem 1.2rem; cursor: pointer; }
        .hr-qa h3 { font-family: var(--hr-display); font-weight: 400; font-size: 1.22rem; line-height: 1.35; color: var(--hr-ink); }
        .hr-qa summary i { position: relative; width: 1.2rem; height: 1.2rem; margin-top: 0.3rem; color: var(--hr-bronze); }
        .hr-qa summary i::before,
        .hr-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 0.5px) 0 auto 0; height: 1px; background: currentColor; transition: rotate 0.35s cubic-bezier(0.22, 1, 0.36, 1); }
        .hr-qa summary i::after { rotate: 90deg; }
        .hr-qa details[open] summary i::after { rotate: 0deg; }
        .hr-qa details p { padding: 0 2.4rem 1.6rem 0.1rem; max-width: 46rem; font-size: 1rem; color: var(--hr-ink-2); }
        @media (max-width: 560px) { .hr-qa details p { padding-inline-end: 0.1rem; } }

        /* ---------------------------------------------------------------
           The luggage tag: put a name on it. Evening, in both modes.
           --------------------------------------------------------------- */
        .hr-fin {
            position: relative;
            overflow: clip;
            padding-block: clamp(4.5rem, 9vw, 7.5rem) clamp(6rem, 10vw, 8.5rem);
            scroll-margin-top: 4rem;
            background-color: #0b3138;
            background-image: linear-gradient(180deg, #0d3a42 0%, #0b3138 46%, #082328 100%);
            color: #cfdcda;
            text-align: center;
        }
        .hr-fin-in { position: relative; z-index: 1; }
        .hr-fin .hr-kicker { color: #e2c483; }
        .hr-fin .hr-h2 { margin-top: 1.1rem; color: #f7f2e8; }
        .hr-fin .hr-mark { color: #e9cf92; }
        .hr-fin-lede { max-width: 38rem; margin: 1.4rem auto 0; font-size: clamp(1.06rem, 1.35vw, 1.2rem); line-height: 1.7; }
        .hr-ltag-wrap { position: relative; width: min(100%, 31rem); margin: 6.5rem auto 0; filter: drop-shadow(0 1.6rem 1.4rem rgba(0, 0, 0, 0.45)); }
        #hr .hr-ltag-wrap { filter: drop-shadow(0 1.6rem 1.4rem rgba(0, 0, 0, 0.45)); }
        /* The strap, down from above and through the eyelet */
        .hr-strap {
            position: absolute;
            z-index: 2;
            left: 50%;
            bottom: calc(100% - 3.1rem);
            width: 1.5rem;
            height: 8.6rem;
            margin-left: -0.75rem;
            border-radius: 0 0 0.75rem 0.75rem;
            background: linear-gradient(90deg, #5f3a1c, #8b5a2b 30%, #9a6a37 50%, #7b4e24 78%, #5a3618);
            box-shadow: inset 0 0 0 1px rgba(40, 22, 8, 0.6), 0 0.4rem 0.6rem -0.2rem rgba(0, 0, 0, 0.5);
        }
        .hr-strap::before { content: ""; position: absolute; inset: 0.3rem 0.26rem 0.5rem; border-inline: 1px dashed rgba(255, 228, 184, 0.55); }
        .hr-strap::after { content: ""; position: absolute; left: -0.3rem; right: -0.3rem; top: 2.9rem; height: 1.15rem; border-radius: 2px; background: linear-gradient(180deg, #f0dba6, #a8854a 60%, #7f6230); box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.6), 0 2px 3px rgba(0, 0, 0, 0.35); }
        .hr-ltag {
            position: relative;
            padding: 5.4rem clamp(1.4rem, 5vw, 2.6rem) 2.1rem;
            background-color: #fdfaf2;
            background-image: var(--hr-grain);
            color: #3c5054;
            text-align: start;
            clip-path: polygon(3.4rem 0, calc(100% - 3.4rem) 0, 100% 3.4rem, 100% 100%, 0 100%, 0 3.4rem);
            -webkit-mask: radial-gradient(circle 0.62rem at 50% 2.35rem, transparent 96%, #000 100%);
            mask: radial-gradient(circle 0.62rem at 50% 2.35rem, transparent 96%, #000 100%);
        }
        /* The eyelet's brass grommet */
        .hr-ltag::before { content: ""; position: absolute; top: 1.37rem; left: 50%; width: 1.96rem; height: 1.96rem; margin-left: -0.98rem; border-radius: 50%; border: 0.36rem solid #b89658; box-shadow: inset 0 0 0 1px rgba(78, 57, 15, 0.6), 0 0 0 1px rgba(78, 57, 15, 0.45); }
        .hr-ltag::after { content: ""; position: absolute; inset: 4.4rem 1rem 1rem; pointer-events: none; box-shadow: inset 0 0 0 1px rgba(96, 72, 30, 0.3), inset 0 0 0 2px rgba(255, 255, 255, 0.95); }
        .hr-ltag-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding-bottom: 0.8rem; border-bottom: 1px solid #a8854a; font-family: var(--hr-display); font-size: 0.64rem; letter-spacing: 0.26em; text-transform: uppercase; color: #73531f; }
        .hr-ltag-label { display: block; margin: 1.3rem 0 0.5rem; font-family: var(--hr-display); font-size: 0.7rem; letter-spacing: 0.3em; text-transform: uppercase; color: #4d5f62; }
        .hr-ltag-form { position: relative; z-index: 1; display: grid; gap: 1.1rem; }
        #hr .hr-claim {
            display: flex;
            align-items: baseline;
            min-width: 0;
            padding: 1rem 0.15rem 0.85rem;
            border: 0;
            border-bottom: 1px solid #17292c;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            font-family: var(--hr-serif);
            font-size: clamp(1.15rem, 4.4vw, 1.5rem);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        #hr .hr-claim:focus-within { border-color: #73531f; box-shadow: 0 2px 0 #73531f; }
        #hr .hr-claim input {
            flex: 1;
            min-width: 0;
            margin-block: -1rem -0.85rem;
            padding: 1rem 0 0.85rem;
            border: 0;
            background: transparent;
            box-shadow: none;
            outline: none;
            font: inherit;
            color: #17292c;
            text-align: right;
        }
        #hr .hr-claim input::placeholder { color: #6a7a7c; }
        .hr-claim span { flex: none; color: #4d5f62; user-select: none; }
        #hr .hr-ltag .hr-btn { background: #0e4a52; color: #f7f2e8; box-shadow: inset 0 0 0 4px #0e4a52, inset 0 0 0 5px rgba(233, 207, 146, 0.6), 0 1rem 1.5rem -1rem rgba(52, 40, 16, 0.4); }
        #hr .hr-ltag .hr-btn:hover { box-shadow: inset 0 0 0 4px #0e4a52, inset 0 0 0 5px rgba(233, 207, 146, 0.9), 0 1.4rem 1.8rem -1rem rgba(52, 40, 16, 0.4); }
        #hr .hr-ltag a:focus-visible,
        #hr .hr-ltag input:focus-visible { outline-color: #73531f; }
        .hr-ltag-note { position: relative; z-index: 1; margin-top: 1.1rem; text-align: center; font-size: 0.92rem; color: #4d5f62; }
        .hr-ltag-return { position: relative; z-index: 1; margin-top: 0.9rem; text-align: center; font-family: var(--hr-display); font-size: 0.58rem; letter-spacing: 0.3em; text-transform: uppercase; color: #4d5f62; }

        @media (prefers-reduced-motion: reduce) {
            .hr-lanterns { animation: none !important; }
            .hr-btn, .hr-btn svg, .hr-link, .hr-fob, .hr-fob-tag, .hr-knob, .hr-keyfob b, .hr-sticker, .hr-plate, .hr-dir-row, .hr-dir-row svg, .hr-table tbody tr, .hr-hang-list li { transition: none; }
        }
    </style>

    @php
        // ---------------------------------------------------------------
        // The standing card. One row per activity that runs on a pattern.
        // 'days' is the seven-character days_of_week string the product
        // actually stores, indexed from Sunday, so the strip below is a
        // direct rendering of that column. A once-a-month dinner is NOT
        // expressible as a day-of-week pattern, so it is entered as its
        // own dated activity and its row says so rather than pretending.
        // 'join' is the honest mechanism: nothing, a free sign-up with a
        // capacity, or a ticket.
        // ---------------------------------------------------------------
        $dowLetters = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        $dowNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $strands = [
            'wellness' => ['Wellness', '#2f7d64'],
            'family'   => ['Family',   '#b07d1f'],
            'water'    => ['Water',    '#1f6f93'],
            'music'    => ['Music',    '#8a4a2f'],
            'dining'   => ['Dining',   '#6d4c14'],
        ];

        $programme = [
            ['Sunrise yoga',   'wellness', '0010101', '7:00',  'Place kept', '12 mats',   'free', false],
            ['Kids club',      'family',   '1111111', '10:00', 'Drop in',    'no limit',  'free', false],
            ['Reef walk',      'water',    '0000001', '16:00', 'Place kept', '8 places',  'free', false],
            ['Terrace trio',   'music',    '0000011', '19:30', 'Drop in',    'no limit',  'free', false],
            ['Sunset sail',    'water',    '0000100', '17:45', 'Ticket',     '$60',       'pro',  false],
            ['Cellar dinner',  'dining',   null,      '19:30', 'Ticket',     '$95',       'pro',  true],
        ];

        // Four weeks of sunrise yoga, twice. The upper strip is the single
        // recurring activity: days_of_week '0010101' indexed from Sunday, so
        // it lands on Tuesday, Thursday and Saturday. The lower strip is what
        // the page publishes once ONE date exception is set on the Thursday in
        // week three - slot 18, because 18 % 7 is 4 and index 4 is Thursday.
        // The excepted slot goes back to being an ordinary empty day, which is
        // exactly what the product does: the date is removed, not annotated.
        $yogaPattern = '0010101';
        $exceptSlot = 18;
        $entered = [];
        $published = [];
        foreach (range(0, 27) as $i) {
            $on = $yogaPattern[$i % 7] === '1';
            $entered[] = $on;
            $published[] = ($i === $exceptSlot) ? false : $on;
        }
        $weekLabels = ['Mar 1', 'Mar 8', 'Mar 15', 'Mar 22'];

        // Today's card in the hero. Times are the property's own, and the four
        // rows are exactly the ones whose pattern above lights Saturday, so the
        // card and the standing table describe the same week.
        $today = [
            ['7:00',  'Sunrise yoga',  'wellness', 'Place kept'],
            ['10:00', 'Kids club',     'family',   'Drop in'],
            ['16:00', 'Reef walk',     'water',    'Place kept'],
            ['19:30', 'Terrace trio',  'music',    'Drop in'],
        ];

        // The printed module block on the sleeve. Decorative: a fixed
        // pattern of filled squares with the three corner finders a
        // printed code has, not a scannable code.
        $codeRows = [
            '111010111',
            '100010001',
            '101010101',
            '100000001',
            '110101011',
            '000110100',
            '111010111',
            '100011001',
            '101010101',
        ];

        // The book. Two lines, two mechanisms, and the meters are
        // computed from the same figures the text prints.
        $book = [
            ['Sunrise yoga',  'Thursday',  9,  12, 'Sign-ups', 'free', 'No money changes hands. A capacity, counted for this date only, and a free waitlist once the mats are gone.'],
            ['Cellar dinner', 'Saturday',  22, 30, 'Tickets',  'pro',  '$95 a head, through your own Stripe or PayPal account, or paid at the desk. Zero platform fees on every plan, and putting a price on a ticket is what Pro is for.'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for hotels and resorts?',
                'a' => 'Yes. The activity page and its link, the QR code, standing activities that repeat on chosen days of the week, date exceptions, sub-schedules, free sign-ups with a capacity and a waitlist when they fill, the embeddable calendar, two-way Google, Outlook and CalDAV sync and built-in analytics are all free forever. Newsletters are on the free plan too, at 10 emails a month counted per recipient, which Pro raises to 100 and Enterprise to 1,000. Putting a price on an experience is where Pro starts, at '.plan_price($proMonthly).' a month, and it brings the rest of the desk kit with it: promo codes, passes, a waitlist for sold-out tickets, add-ons and the live check-in dashboard. Event Schedule charges zero platform fees on sales, on every plan.',
            ],
            [
                'q' => 'How do guests find out what is on during their stay?',
                'a' => 'Your schedule has its own address and a QR code you can download and print, so the link can go on the key-card sleeve, the room folder, a sign by the pool or the pre-arrival email. You can also embed the same calendar in the website you already have. Nothing is installed and no account is needed to read it. A guest who wants the week in their own phone can subscribe to the schedule\'s calendar from the page, and a session you move follows them there. A guest who leaves an email address gets next week\'s additions as one digest without you lifting a finger, and anything more considered than that is a newsletter you write.',
            ],
            [
                'q' => 'Can I set up the activities that run every week?',
                'a' => 'Yes, on the free plan. An activity can repeat on chosen days of the week at a start time, so sunrise yoga on Tuesday, Thursday and Saturday is one entry rather than three more every week. Date exceptions take individual dates out for the week the court is being resurfaced, and guests simply do not see that day offered. One recurring activity carries one start time, so a morning session and an evening session are two entries.',
            ],
            [
                'q' => 'Can guests reserve a place, and can I sell the paid experiences?',
                'a' => 'Both. Reserving a place is free and has no ceiling; charging for one is where Pro starts. A free activity can take sign-ups with a capacity, and the count is kept for each date separately, so a full Tuesday does not close Thursday, and a full date offers a free waitlist instead. Paid experiences use ticketing: named ticket types with their own prices and quantities, QR scanning at the door, payment through your own Stripe or PayPal account, a payment link or cash at the desk, and no platform fee from us on any plan. A ticket type at no charge goes out on the free plan; the moment one carries a price the schedule needs Pro. Promo codes, which is how you would carry a resident rate for the people staying with you, are a Pro feature too.',
            ],
            [
                'q' => 'Can I refund a guest who cancels a paid experience?',
                'a' => 'Yes, on Pro, from the Sales page, in full or in part. A Stripe or PayPal payment goes back to the guest through that provider before the sale is marked refunded, and a partial refund leaves the booking valid. A full refund returns the place, so a seat at the cellar dinner can be sold again. A payment taken at the desk, by payment link or any other way shows Mark as Refunded, which records it without moving money. Event Schedule does not email the guest about a refund, so the desk should.',
            ],
            [
                'q' => 'Can guests ask to hear when tickets for a special dinner go on sale?',
                'a' => 'Yes, on every plan. Switch on the "Notify me" card, publish the New Year dinner before tickets are on sale, and its page offers "Tell me when tickets go on sale". A guest leaves just an email address and gets one email when tickets go on sale, one if you cancel the dinner, and a reminder shortly before it starts, plus a change notice if you choose to send one when you move it. It is not a subscription to your schedule, every email has a one-click unsubscribe, and how many people are waiting shows in the event editor, never on the public page.',
            ],
            [
                'q' => 'Can I keep the pool, the spa, the kids club and the conference programme apart?',
                'a' => 'Yes, with sub-schedules, free on every plan. Each one has a name, a colour and its own link, so the spa can point a sign at its own strand of the same calendar. Being straight about what they are: they organise and colour-code, they are not rooms with their own capacity, and nothing is checking whether two activities overlap. A conference day can carry its own agenda inside the event as parts, and anything you are not ready to show stays a Draft, which is members-only until you publish it.',
            ],
            [
                'q' => 'Can more than one person keep the card up to date?',
                'a' => 'The free plan is one team member, and multiple team members are an Enterprise feature capped at five. In between, calendar sync does a lot of the work: the schedule syncs two ways with Google, Outlook or CalDAV, so whoever runs the programme can work in the calendar they already have and the public page follows. Booking requests are free as well, so an act or a planner can ask about a date and it waits for you to accept it before it appears anywhere. On Enterprise, a viewer login is read-only and sees no sales, but can scan tickets at the door. And before whoever set the schedule up moves on, they can hand it to a colleague, on any plan.',
            ],
        ];

        $dotSections = [
            ['top', 'The card'],
            ['rack', 'The standing week'],
            ['sleeve', 'The sleeve'],
            ['book', 'Keeping a place'],
            ['strands', 'The strands'],
            ['desk', 'Behind the desk'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    @php
        $hrArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $hrDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="hr">

        <!-- The floor directory: section rail, wide screens only -->
        <nav class="hr-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot"><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the card under the door                             -->
        <!-- ============================================================ -->
        <section id="top" class="hr-hero">
            <div class="hr-wrap hr-hero-grid">
                <div class="hr-copy">
                    <h1 class="hr-h1">
                        <x-marketing.hero-eyebrow class="hr-eyebrow es-fade-up es-d-1">Guest activity calendar for hotels</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">The desk closes</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">at eleven.</span></span>
                        <span class="es-mask hr-mask-3"><span class="es-mask-line hr-mark">The card does not.</span></span>
                    </h1>

                    <p class="hr-lede hr-hero-lede es-fade-up es-d-2">
                        Guests ask the same four questions all week, and the answer lives with
                        whoever is on the desk plus a printed sheet that went out of date on
                        Tuesday. Put the week on a page with your property's name on it, then
                        print the link on the key-card sleeve and let the card answer at six
                        in the morning.
                    </p>

                    <div class="hr-cta es-fade-up es-d-3">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="hr-btn">
                            Put the week on a page
                            {!! $hrArrow !!}
                        </a>
                        <a href="#rack" class="hr-link">
                            See the standing card
                            {!! $hrDown !!}
                        </a>
                    </div>

                    <p class="hr-fine hr-foot es-fade-up es-d-4">
                        Free forever for the page, the link, the QR code and the standing week.
                    </p>
                </div>

                <!-- The card. Tomorrow's programme, as a guest holds it. -->
                <div class="hr-program-wrap es-fade-up es-d-3">
                    <div class="hr-card hr-emboss hr-program">
                        <span class="hr-sun-note hr-sun-note-a" aria-hidden="true">Sunrise <b>6:12</b></span>
                        <span class="hr-sun-note hr-sun-note-b" aria-hidden="true">Sunset <b>7:48</b></span>

                        <svg class="hr-crest" viewBox="0 0 120 120" aria-hidden="true">
                            <defs><path id="hr-crest-path" d="M60,60 m-45,0 a45,45 0 1,1 90,0 a45,45 0 1,1 -90,0" /></defs>
                            <circle cx="60" cy="60" r="58" />
                            <circle cx="60" cy="60" r="34" />
                            <text class="hr-crest-ring" textLength="278" lengthAdjust="spacing"><textPath href="#hr-crest-path" textLength="278" lengthAdjust="spacing">Lantern Bay &#183; Guest programme &#183;</textPath></text>
                            <text class="hr-crest-mono" x="60" y="69.5" text-anchor="middle">LB</text>
                        </svg>

                        <p class="hr-program-kicker">Today at</p>
                        <p class="hr-program-name">Lantern Bay</p>
                        <div class="hr-orn" aria-hidden="true"></div>
                        <p class="hr-program-date hr-num">Saturday, 14 March</p>

                        <ul class="hr-prog">
                            @foreach ($today as [$tTime, $tName, $tStrand, $tJoin])
                                <li>
                                    <span class="hr-prog-time">{{ $tTime }}</span>
                                    <span class="hr-prog-what">
                                        <b>{{ $tName }}</b>
                                        <small><i class="hr-pip" style="background: {{ $strands[$tStrand][1] }};" aria-hidden="true"></i>{{ $strands[$tStrand][0] }}</small>
                                    </span>
                                    <span class="hr-prog-join @if ($tJoin === 'Place kept') is-kept @endif">{{ $tJoin }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="hr-program-url">lanternbay.eventschedule.com</p>
                    </div>
                </div>

                <p class="hr-fine hr-aside-note es-fade-up es-d-4">
                    The same card your desk already keeps. The difference is that this one is a
                    page, so it is right at midnight, and the only thing you ever print is the
                    line at the bottom.
                </p>
            </div>

            <!-- The horizon. It does not move. -->
            <div class="hr-sea" aria-hidden="true">
                <div class="hr-sea-sky"><i class="hr-sun"></i></div>
                <div class="hr-sea-water"><i class="hr-glitter"></i></div>
                <div class="hr-lanterns"></div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The standing week (101): recurring + exceptions           -->
        <!-- ============================================================ -->
        <section id="rack" class="hr-section">
            <div class="hr-wrap">
                <div class="hr-head">
                    <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">101</b></span>
                    <p class="hr-kicker" data-reveal>The standing week</p>
                    <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Most of the week is <span class="hr-mark">the same week</span>.
                    </h2>
                    <p class="hr-lede" data-reveal style="--reveal-delay: 0.12s;">
                        Yoga on the lawn Tuesday, Thursday and Saturday at seven. Kids club every
                        morning. The trio on the terrace at the weekend. Enter that rhythm once and
                        the week draws itself for as long as it runs.
                    </p>
                </div>

                <div class="hr-card hr-sheet" data-reveal="panel">
                    <div class="hr-sheet-head" aria-hidden="true">
                        <b>Lantern Bay</b>
                        <span class="hr-kicker">The standing programme</span>
                    </div>

                    <table class="hr-table" role="table">
                        <caption class="sr-only">The standing programme at Lantern Bay: each activity with its strand, the days it repeats, its start time and how a guest joins</caption>
                        <thead role="rowgroup">
                            <tr role="row">
                                <th role="columnheader" scope="col">Activity</th>
                                <th role="columnheader" scope="col">Strand</th>
                                <th role="columnheader" scope="col">Repeats</th>
                                <th role="columnheader" scope="col">Time</th>
                                <th role="columnheader" scope="col">How to join</th>
                            </tr>
                        </thead>
                        <tbody role="rowgroup">
                            @foreach ($programme as [$pName, $pStrand, $pDays, $pTime, $pJoin, $pNote, $pTier, $pDated])
                                <tr role="row">
                                    <th role="rowheader" scope="row">{{ $pName }}</th>
                                    <td role="cell" class="hr-td-strand">
                                        <span class="hr-strand">
                                            <i class="hr-pip" style="background: {{ $strands[$pStrand][1] }};" aria-hidden="true"></i>
                                            {{ $strands[$pStrand][0] }}
                                        </span>
                                    </td>
                                    <td role="cell" class="hr-td-days">
                                        @if ($pDays)
                                            <span class="sr-only">
                                                @foreach (str_split($pDays) as $dIdx => $dOn)
                                                    @if ($dOn === '1'){{ $dowNames[$dIdx] }}. @endif
                                                @endforeach
                                            </span>
                                            <span class="hr-dow" aria-hidden="true">
                                                @foreach (str_split($pDays) as $dIdx => $dOn)
                                                    <span class="@if ($dOn === '1') is-on @endif">{{ $dowLetters[$dIdx] }}</span>
                                                @endforeach
                                            </span>
                                        @else
                                            <span class="hr-fine hr-num">one date</span>
                                        @endif
                                    </td>
                                    <td role="cell" class="hr-td-time"><span class="hr-time">{{ $pTime }}</span></td>
                                    <td role="cell" class="hr-td-join">
                                        <span class="hr-join @if ($pJoin === 'Ticket') is-ticket @elseif ($pJoin === 'Place kept') is-kept @endif">{{ $pJoin }}</span>
                                        <span class="hr-join-note hr-num">
                                            {{ $pNote }}@if ($pTier === 'pro') &middot; Pro @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <p class="hr-fine hr-sheet-foot">
                        The lit squares are the days the activity repeats, read from Sunday, which is
                        exactly how the pattern is stored. The cellar dinner has no pattern on purpose:
                        a repeat is by day of the week, so a once-a-month dinner is entered as its own
                        dated activity. The four free rows keep places on any plan, with no ceiling on
                        how many. The two with a price on them are what Pro is for.
                    </p>
                </div>

                <!-- One row of the table above, opened out over four weeks. The
                     lower strip is the same activity after a single date
                     exception, and the excepted Thursday is simply an empty
                     slot: the product removes the date rather than marking it. -->
                <div class="hr-card hr-month" data-reveal="panel">
                    <div class="hr-month-head">
                        <h3>Sunrise yoga, four weeks of March</h3>
                        <span class="hr-num">Tuesday &middot; Thursday &middot; Saturday &middot; 7:00</span>
                    </div>

                    <p class="hr-kicker">What you entered</p>
                    <div class="hr-weeks" aria-hidden="true">
                        @foreach ($weekLabels as $wIdx => $wLabel)
                            <div class="hr-wk">
                                <div class="hr-wk-cells">
                                    @foreach (range(0, 6) as $dOffset)
                                        <span class="@if ($entered[$wIdx * 7 + $dOffset]) is-on @endif"></span>
                                    @endforeach
                                </div>
                                <p class="hr-wk-label">{{ $wLabel }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="hr-fine">One activity. Twelve sessions, and nothing to re-enter.</p>

                    <div class="hr-hair hr-month-rule" aria-hidden="true"></div>

                    <p class="hr-kicker">What the page publishes</p>
                    <div class="hr-weeks" aria-hidden="true">
                        @foreach ($weekLabels as $wIdx => $wLabel)
                            <div class="hr-wk">
                                <div class="hr-wk-cells">
                                    @foreach (range(0, 6) as $dOffset)
                                        <span class="@if ($published[$wIdx * 7 + $dOffset]) is-on @endif"></span>
                                    @endforeach
                                </div>
                                <p class="hr-wk-label">{{ $wLabel }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="hr-fine">
                        One date exception, on Thursday 19 March. Eleven mornings instead of twelve.
                    </p>

                    <p class="hr-fine hr-month-foot">
                        <span class="sr-only">Both strips describe the same activity: it runs on Tuesday, Thursday and Saturday for four weeks from 1 March, and a date exception removes Thursday 19 March.</span>
                        Worth being exact about the second strip: the excepted morning is not
                        labelled cancelled and not struck through. The date comes out, so a guest
                        reading that week sees a Wednesday and a Friday and no reason to ask what
                        happened to Thursday.
                    </p>
                </div>

                <div class="hr-notes" data-reveal-group="100">
                    @foreach ([
                        ['One entry, not fifty-two', 'Pick the days and the start time and it keeps appearing. Nothing to re-enter on Sunday night, and nothing that quietly stops because somebody was on holiday.'],
                        ['The weeks it does not run', 'A date exception takes a single date out. Yoga does not run the Thursday the lawn is being cut, and guests are simply not offered that morning rather than being told at the door.'],
                        ['Morning and evening are two entries', 'One repeating activity carries one start time, so a sunrise class and a sunset class are two entries. A little more setup, and no confusion about which one somebody signed up for.'],
                    ] as [$rTitle, $rDesc])
                        <div class="hr-note" data-reveal>
                            <div class="hr-note-head">
                                <h3>{{ $rTitle }}</h3>
                                <span class="hr-tag">Free</span>
                            </div>
                            <p>{{ $rDesc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The sleeve (102): the QR, the link, the embed             -->
        <!-- ============================================================ -->
        <section id="sleeve" class="hr-section hr-band">
            <div class="hr-wrap hr-split">
                <div>
                    <div class="hr-head hr-head-start">
                        <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">102</b></span>
                        <p class="hr-kicker" data-reveal>The sleeve</p>
                        <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Print the link. <span class="hr-mark">Never the week</span>.
                        </h2>
                        <p class="hr-lede" data-reveal style="--reveal-delay: 0.12s;">
                            A printed week is wrong the first time something moves, and reprinting it is
                            somebody's Thursday. Print the address instead. It never changes, so the
                            sleeve you had made in March is still correct in November.
                        </p>
                    </div>

                    <ul class="hr-points" data-reveal-group="90">
                        @foreach ([
                            ['A QR code you can print', 'Free', 'Download your schedule\'s code and put it where guests already look: the key-card sleeve, the room folder, the lift, a sign by the pool, the pre-arrival email. It opens the page in a browser, with nothing to install and no account needed to read it.'],
                            ['The calendar, inside your own site', 'Free', 'Embed the same calendar in the page your website already has, so the "What\'s on" tab stops being a PDF from last season.'],
                            ['A list that hears about new dates', 'Free', 'Guests who leave an email address get a digest when you add activities, batched and no more than one every few days, and it does not draw on the allowance. A newsletter you write does: 10 emails a month free, 100 on Pro and 1,000 on Enterprise, each recipient counting as one.'],
                            ['A calendar that keeps up', 'Free', 'From the page, a guest can subscribe to the whole card as a live calendar on their phone. Move a session or take a date out and it follows there too, and it costs them no email address.'],
                        ] as [$sTitle, $sPlan, $sDesc])
                            <li class="hr-point" data-reveal>
                                <div class="hr-point-head">
                                    <h3>{{ $sTitle }}</h3>
                                    <span class="hr-tag">{{ $sPlan }}</span>
                                </div>
                                <p>{{ $sDesc }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- The sleeve: the second printed object, same stock. -->
                <div class="hr-sleeve-wrap" data-reveal="panel">
                    <div class="hr-keycard" aria-hidden="true"></div>
                    <div class="hr-card hr-emboss hr-sleeve">
                        <div class="hr-sleeve-top">
                            <div>
                                <p class="hr-kicker">Room key</p>
                                <p class="hr-sleeve-title">What is on this week</p>
                                <p class="hr-fine">Point a camera at the code, or type the line below.</p>
                            </div>
                            <div class="hr-code" aria-hidden="true">
                                @foreach ($codeRows as $row)
                                    @foreach (str_split($row) as $cell)
                                        <i class="@if ($cell === '1') is-on @endif"></i>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>

                        <p class="hr-sleeve-url">lanternbay.eventschedule.com</p>
                        <p class="hr-fine">
                            One address for the whole property. It is the same page the desk reads
                            from, so nobody is working off two versions of Tuesday.
                        </p>

                        <div class="hr-pills">
                            <span class="hr-pill">Key-card sleeve</span>
                            <span class="hr-pill">Room folder</span>
                            <span class="hr-pill">Pool sign</span>
                            <span class="hr-pill">Pre-arrival email</span>
                            <span class="hr-pill is-own">Your own website</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Keeping a place (103): sign-ups free, priced tickets Pro  -->
        <!-- ============================================================ -->
        <section id="book" class="hr-section">
            <div class="hr-wrap">
                <div class="hr-head">
                    <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">103</b></span>
                    <p class="hr-kicker" data-reveal>Keeping a place</p>
                    <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Twelve mats means <span class="hr-mark">twelve names</span>.
                    </h2>
                    <p class="hr-lede" data-reveal style="--reveal-delay: 0.12s;">
                        Some things guests just turn up to. Some things have a number: the mats, the
                        boat, the seats in the cellar. Two mechanisms, and the difference is whether
                        money is involved.
                    </p>
                </div>

                <!-- The desk's book, open: one page for each mechanism. The
                     ruled lines are the places, drawn from the same figures
                     the text prints, with a stroke on each one taken. -->
                <div class="hr-card hr-ledger" data-reveal="panel">
                    @foreach ($book as [$bName, $bDay, $bTaken, $bTotal, $bKind, $bTier, $bNote])
                        <div class="hr-page">
                            <div class="hr-page-head">
                                <h3>{{ $bName }}</h3>
                                @if ($bTier === 'pro')
                                    <span class="hr-tag hr-tag-pro">Pro</span>
                                @else
                                    <span class="hr-tag">Free</span>
                                @endif
                            </div>

                            <p class="hr-count">
                                {{ $bTaken }}<span> of {{ $bTotal }}</span>
                            </p>
                            <p class="hr-count-sub">{{ strtolower($bKind) }} taken for {{ $bDay }} &middot; {{ $bTotal - $bTaken }} left</p>

                            <div class="hr-meter" aria-hidden="true">
                                <div class="hr-meter-fill" style="width: {{ (int) round($bTaken / $bTotal * 100) }}%;"></div>
                            </div>

                            <ol class="hr-lines" style="--cols: {{ $bTotal > 12 ? 3 : 2 }};" aria-hidden="true">
                                @foreach (range(1, $bTotal) as $bLine)
                                    <li>
                                        <small>{{ $bLine }}</small>
                                        @if ($bLine <= $bTaken)
                                            <i style="--w: {{ 38 + (($bLine * 37) % 46) }}%;"></i>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>

                            <p class="hr-page-note">{{ $bNote }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="hr-notes" data-reveal-group="90">
                    @foreach ([
                        ['Counted for each date', 'Free', 'A full Tuesday does not close Thursday. Every date keeps its own count, which is the only way a standing activity with a limit can work at all.'],
                        ['A ticket for the paid ones', 'Pro', 'Named ticket types with their own prices and quantities, QR scanning at the door, and payment through your own Stripe or PayPal account, a payment link or cash at the desk. Event Schedule takes nothing from the ticket price on any plan. Charging for a place is the Pro half; keeping one is not.'],
                        ['A rate for people staying with you', 'Pro', 'A promo code carries a resident rate that the desk can hand out. Nothing is verifying who is a guest, so the code is what does it.'],
                    ] as [$kTitle, $kPlan, $kDesc])
                        <div class="hr-note" data-reveal>
                            <div class="hr-note-head">
                                <h3>{{ $kTitle }}</h3>
                                @if ($kPlan === 'Pro')
                                    <span class="hr-tag hr-tag-pro">Pro</span>
                                @else
                                    <span class="hr-tag">Free</span>
                                @endif
                            </div>
                            <p>{{ $kDesc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The strands (104): sub-schedules, and what they are not   -->
        <!-- ============================================================ -->
        <section id="strands" class="hr-section hr-band">
            <div class="hr-wrap hr-split">
                <!-- Five strands filed on one card, each with the colour its
                     owner picked. The colour is a free-form hex, so it is
                     set inline and nothing about it is baked into the
                     stylesheet. -->
                <div class="hr-card hr-strands" data-reveal="panel">
                    <div class="hr-ribbons" aria-hidden="true">
                        @foreach (array_values($strands) as $ribbonIdx => [$ribbonLabel, $ribbonColor])
                            <i style="background: {{ $ribbonColor }}; --h: {{ 2.6 + (($ribbonIdx * 3) % 5) * 0.35 }}rem;"></i>
                        @endforeach
                    </div>
                    <p class="hr-kicker hr-strands-kicker">Five strands, one address</p>
                    @foreach ($strands as $sKey => [$sLabel, $sColor])
                        <div class="hr-strand-row">
                            <i class="hr-enamel" style="background: {{ $sColor }};" aria-hidden="true"></i>
                            <b>{{ $sLabel }}</b>
                            <span>/{{ $sKey }}</span>
                        </div>
                    @endforeach
                    <p class="hr-fine">
                        Each strand keeps its own link on the same address, so the spa can point
                        a sign at its own list without asking for its own website. The colour is
                        yours to pick, and it is the same colour the strand wears on the card.
                    </p>
                </div>

                <div>
                    <div class="hr-head hr-head-start">
                        <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">104</b></span>
                        <p class="hr-kicker" data-reveal>The strands</p>
                        <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                            The spa and the kids club, <span class="hr-mark">one card</span>.
                        </h2>
                        <p class="hr-lede" data-reveal style="--reveal-delay: 0.12s;">
                            Sub-schedules give each part of the programme a name and a colour, so a
                            family arriving on Friday can read the family strand and a couple on a
                            wellness break can read theirs. Free on every plan.
                        </p>
                    </div>

                    <div class="hr-card hr-aside" data-reveal>
                        <h3>What a strand is not</h3>
                        <p>
                            It is a label, not a room. A strand has a name, a colour and a link and
                            nothing else: no capacity of its own, and nothing in it is hidden from
                            anybody. Nothing here checks whether two activities overlap either, so
                            the ballroom is still your call. Anything you are not ready to show yet
                            stays a Draft, which stays members-only until you publish it.
                        </p>
                    </div>

                    <p class="hr-free-line" data-reveal>
                        <span class="hr-tag">Free</span>
                        <span>Strands, Drafts and the address itself cost nothing.</span>
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Behind the desk (105): two doors, a hanger on each        -->
        <!-- ============================================================ -->
        <section id="desk" class="hr-section">
            <div class="hr-wrap">
                <div class="hr-head">
                    <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">105</b></span>
                    <p class="hr-kicker" data-reveal>Behind the desk</p>
                    <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                        What the desk keeps, <span class="hr-mark">and what the card says</span>.
                    </h2>
                    <p class="hr-lede" data-reveal style="--reveal-delay: 0.12s;">
                        A desk has a drawer as well as a card rack. The two sides are not the same
                        list, and only one of them is public.
                    </p>
                </div>

                <div class="hr-doors" data-reveal-group="110">
                    <div class="hr-door" data-reveal="panel">
                        <span class="hr-door-plate" aria-hidden="true">Staff only</span>
                        <div class="hr-hang">
                            <span class="hr-knob" aria-hidden="true"></span>
                            <div class="hr-hanger hr-hanger-desk">
                                <p class="hr-hang-title">In the drawer</p>
                                <div class="hr-hang-rule" aria-hidden="true"></div>
                                <ul class="hr-hang-list">
                                    @foreach ([
                                        ['A Draft nobody can see', 'Free', 'The New Year dinner exists, with its price and its date, and stays members-only until you publish it.', 'draft'],
                                        ['An enquiry waiting on you', 'Free', 'Booking requests arrive through the page and wait until you accept one, and the schedule can email you when a new one is sitting there. Nothing appears publicly first.', ''],
                                        ['Who is waiting for the dinner', 'Free', 'Publish the New Year dinner before tickets open, switch on the "Notify me" card, and guests can ask to be told when they do. How many asked shows on the event\'s Tickets panel, and nowhere public.', 'dinner'],
                                        ['A date taken out', 'Free', 'The exception for the Wednesday the pool is drained. It removes the date rather than annotating it.', 'date'],
                                        ['Your own calendar', 'Free', 'Two-way sync with Google, Outlook or CalDAV, so whoever runs the programme works where they already work.', 'calendar'],
                                        ['Tonight\'s running count', 'Pro', 'A scan at the door reads the ticket and marks it used on every plan. The live count and the breakdown by ticket type are the Pro half, and staff-side only.', ''],
                                    ] as [$dTitle, $dPlan, $dDesc, $dKey])
                                        <li @if ($dKey) data-k="{{ $dKey }}" @endif>
                                            <div class="hr-hang-head">
                                                <h3>{{ $dTitle }}</h3>
                                                <span class="hr-tag @if ($dPlan === 'Pro') hr-tag-pro @endif">{{ $dPlan }}</span>
                                            </div>
                                            <p>{{ $dDesc }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="hr-door" data-reveal="panel">
                        <span class="hr-door-plate" aria-hidden="true">Guests</span>
                        <div class="hr-hang">
                            <span class="hr-knob" aria-hidden="true"></span>
                            <div class="hr-hanger hr-hanger-guest">
                                <p class="hr-hang-title">On the card</p>
                                <div class="hr-hang-rule" aria-hidden="true"></div>
                                <ul class="hr-hang-list">
                                    @foreach ([
                                        ['Only what you published', 'Free', 'The public page carries exactly what you put on it. Nothing arrives on it because somebody else asked.', 'draft'],
                                        ['Times in the property\'s own zone', 'Free', 'The schedule holds a time zone, so a guest reading the page in another one still sees seven in the morning here.', ''],
                                        ['A day that is simply not offered', 'Free', 'An excepted date does not appear as cancelled. It is not there, which is what a guest actually needs to know.', 'date'],
                                        ['One tap to their own phone', 'Free', 'A date on the card adds itself to Google, Apple or Outlook as a single calendar entry, so Thursday\'s sunset sail is in their own week.', 'calendar'],
                                        ['Told when the dinner goes on sale', 'Free', 'With the "Notify me" card switched on, a guest leaves an email address on the dinner\'s page and hears when tickets go on sale, if you cancel, and shortly before it starts. It asks for no account and no name.', 'dinner'],
                                        ['Nothing they did not ask for', 'Free', 'The card does not say who else signed up. A guest hears about new activities only if they left an email address and confirmed it, and then it is one digest every few days rather than a message per activity.', ''],
                                    ] as [$cTitle, $cPlan, $cDesc, $cKey])
                                        <li @if ($cKey) data-k="{{ $cKey }}" @endif>
                                            <div class="hr-hang-head">
                                                <h3>{{ $cTitle }}</h3>
                                                <span class="hr-tag">{{ $cPlan }}</span>
                                            </div>
                                            <p>{{ $cDesc }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="hr-pair-hint" aria-hidden="true">Rest on a line to find its other side</p>

                <p class="hr-desk-foot" data-reveal>
                    The counting side is worth a look too: built-in analytics record views per
                    activity, and sales against them, so next season's card is written from what
                    guests actually turned up to. Free on every plan.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Who it is for (106): six luggage labels                   -->
        <!-- ============================================================ -->
        <section id="who" class="hr-section hr-band">
            <div class="hr-wrap">
                <div class="hr-head">
                    <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">106</b></span>
                    <p class="hr-kicker" data-reveal>Who it is for</p>
                    <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Any property with a <span class="hr-mark">week to publish</span>.
                    </h2>
                    <p class="hr-lede" data-reveal style="--reveal-delay: 0.12s;">
                        From a twelve-room townhouse to a resort with five strands running at once.
                    </p>
                </div>

                @php
                    // Name, description, blog slug, then the label: its shape, the two
                    // lines of lettering either side of the initials, colours and lean.
                    $hrStays = [
                        ['Boutique Hotels', 'A tasting, a supper, a walk with somebody local. A handful of things a month, each one worth a page of its own.', 'for-boutique-hotels', 'oval', 'Boutique', 'BH', 'Hotels', '#0e4a52', '#f3e6c4', '-5deg'],
                        ['Beach Resorts', 'Pool sessions, water sports, sunset yoga, a bonfire on Saturday. A standing week with a limit on the boat.', 'for-beach-resorts', 'round', 'Beach', 'BR', 'Resorts', '#e8a060', '#17292c', '4deg'],
                        ['Conference Hotels', 'Session times, networking evenings and corporate dinners on one calendar, with each day\'s agenda listed inside the event.', 'for-conference-hotels', 'plaque', 'Conference', 'CH', 'Hotels', '#fdfaf2', '#0e4a52', '-3deg'],
                        ['Spa & Wellness Resorts', 'Meditation at six, breath work at eight, a workshop on Sunday. Small numbers, so every place is kept in advance.', 'for-spa-resorts', 'arch', 'Spa & Wellness', 'SW', 'Resorts', '#1f7379', '#f7f2e8', '3deg'],
                        ['Mountain Lodges', 'Guided walks, ski lessons, a fire and a talk. The programme changes with the season, and the address does not.', 'for-mountain-lodges', 'diamond', 'Mountain', 'ML', 'Lodges', '#7a5a26', '#f7ecd2', '45deg'],
                        ['Casino Hotels', 'Shows, tournaments, dining nights and late music. A busy card, sold where it needs to be sold.', 'for-casino-hotels', 'stamp', 'Casino', 'CH', 'Hotels', '#17292c', '#e2c483', '-4deg'],
                    ];
                @endphp

                <div class="hr-stays" data-reveal-group="70">
                    @foreach ($hrStays as $stayIdx => [$stayName, $stayDesc, $staySlug, $stayShape, $stayTop, $stayMono, $stayBottom, $stayBg, $stayFg, $stayLean])
                        @php $stayPost = get_sub_audience_blog($staySlug); @endphp
                        <article class="hr-stay" data-reveal>
                            <div class="hr-stay-art" aria-hidden="true">
                                <div class="hr-sticker hr-sticker-{{ $stayShape }}" style="--bg: {{ $stayBg }}; --fg: {{ $stayFg }}; --r: {{ $stayLean }};">
                                    @if ($stayShape === 'round')
                                        <svg viewBox="0 0 120 120">
                                            <defs><path id="hr-sticker-ring-{{ $stayIdx }}" d="M60,60 m-47,0 a47,47 0 1,1 94,0 a47,47 0 1,1 -94,0" /></defs>
                                            <text textLength="290" lengthAdjust="spacing"><textPath href="#hr-sticker-ring-{{ $stayIdx }}" textLength="290" lengthAdjust="spacing">{{ $stayTop }} {{ $stayBottom }} &#183; {{ $stayTop }} {{ $stayBottom }} &#183;</textPath></text>
                                        </svg>
                                        <b>{{ $stayMono }}</b>
                                    @else
                                        <span>
                                            <small>{{ $stayTop }}</small>
                                            <b>{{ $stayMono }}</b>
                                            <small>{{ $stayBottom }}</small>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <h3>{{ $stayName }}</h3>
                                <p>{{ $stayDesc }}</p>
                                @if ($stayPost)
                                    <a href="{{ blog_url('/' . $stayPost->slug) }}" class="hr-link" aria-label="Learn more about Event Schedule for {{ $stayName }}">
                                        Learn more
                                        {!! $hrArrow !!}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. How it works (107): three keys on the rack                -->
        <!-- ============================================================ -->
        <section id="how" class="hr-section">
            <div class="hr-wrap">
                <div class="hr-head">
                    <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">107</b></span>
                    <p class="hr-kicker" data-reveal>How it works</p>
                    <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                        An afternoon, <span class="hr-mark">then it runs</span>.
                    </h2>
                </div>

                <div class="hr-keys" data-reveal-group="100">
                    @foreach ([
                        ['01', 'Take the address', 'Sign up as a venue schedule, put the property\'s name on it, and name the strands: wellness, family, water, dining, whatever you actually run.'],
                        ['02', 'Enter the standing week', 'Add the activities that repeat, with their days and their start times, and set exceptions for the dates they do not run. Give the ones with a number a capacity.'],
                        ['03', 'Print the link', 'Download the QR code for the sleeve and the room folder, embed the calendar in your own site, and put the address in the pre-arrival email.'],
                    ] as [$hNum, $hTitle, $hDesc])
                        <div class="hr-key" data-reveal>
                            <span class="hr-keyfob" aria-hidden="true"><b>{{ $hNum }}</b></span>
                            <div>
                                <h3>{{ $hTitle }}</h3>
                                <p>{{ $hDesc }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Key features: the directory                               -->
        <!-- ============================================================ -->
        <section class="hr-section hr-band">
            <div class="hr-wrap hr-dir-grid">
                <div>
                    <p class="hr-kicker" data-reveal aria-hidden="true">In the directory</p>
                    <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s; margin-top: 1.1rem;">Key features</h2>
                    <p class="hr-dir-more" data-reveal style="--reveal-delay: 0.12s;">
                        <a href="{{ marketing_url('/features') }}" class="hr-link">
                            See all features
                            {!! $hrArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $hrDirectory = [
                        ['Recurring Events', 'The standing week as one entry, with exceptions for the dates it does not run', marketing_url('/features/recurring-events')],
                        ['Embed Calendar', 'Put the same calendar inside the hotel website you already have', marketing_url('/features/embed-calendar')],
                        ['Ticketing', 'Sell the paid experiences with QR check-in and zero platform fees', marketing_url('/features/ticketing')],
                        ['Sub-schedules', 'Give the pool, the spa and the kids club a colour and a link', marketing_url('/features/sub-schedules')],
                        ['Promo Codes', 'A resident rate the desk can hand to the people staying with you', marketing_url('/features/promo-codes')],
                    ];
                @endphp
                <div class="hr-dir" data-reveal-group="70">
                    @foreach ($hrDirectory as $dirIdx => [$dirName, $dirDesc, $dirUrl])
                        <a href="{{ $dirUrl }}" class="hr-dir-row" data-reveal>
                            <span class="hr-dir-no" aria-hidden="true">{{ $dirIdx + 1 }}</span>
                            <span>
                                <strong>{{ $dirName }}</strong>
                                <small>{{ $dirDesc }}</small>
                            </span>
                            {!! $hrArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="hr-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 10. Related pages: down the corridor                         -->
        <!-- ============================================================ -->
        <section class="hr-section hr-band">
            <div class="hr-wrap">
                <div class="hr-corridor-head">
                    <div>
                        <p class="hr-kicker" data-reveal aria-hidden="true">Down the corridor</p>
                        <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s; margin-top: 1.1rem;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="hr-link" data-reveal>
                        See all use cases
                        {!! $hrArrow !!}
                    </a>
                </div>

                <div class="hr-plates" data-reveal-group="70">
                    @foreach ([
                        ['/for-restaurants', 'Restaurants'],
                        ['/for-venues', 'Venues'],
                        ['/for-community-centers', 'Community Centers'],
                        ['/for-bars', 'Bars'],
                    ] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal class="hr-plate">
                            <small>Event Schedule for</small>
                            <strong>{{ $relName }}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. FAQ (108): a letter, folded in three                     -->
        <!-- ============================================================ -->
        <section id="faq" class="hr-section">
            <div class="hr-narrow">
                <div class="hr-head">
                    <span class="hr-fob" data-reveal="swing" aria-hidden="true"><i class="hr-fob-ring"></i><b class="hr-fob-tag">108</b></span>
                    <p class="hr-kicker" data-reveal>Questions</p>
                    <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Asked <span class="hr-mark">across the desk</span>.
                    </h2>
                </div>

                <div class="hr-card hr-letter" data-reveal="panel">
                    <div class="hr-letter-head" aria-hidden="true">
                        <svg class="hr-crest" viewBox="0 0 120 120">
                            <defs><path id="hr-letter-path" d="M60,60 m-45,0 a45,45 0 1,1 90,0 a45,45 0 1,1 -90,0" /></defs>
                            <circle cx="60" cy="60" r="58" />
                            <circle cx="60" cy="60" r="34" />
                            <text class="hr-crest-ring" textLength="278" lengthAdjust="spacing"><textPath href="#hr-letter-path" textLength="278" lengthAdjust="spacing">Lantern Bay &#183; The front desk &#183;</textPath></text>
                            <text class="hr-crest-mono" x="60" y="69.5" text-anchor="middle">LB</text>
                        </svg>
                        <p class="hr-letter-with">With our compliments</p>
                    </div>

                    <div class="hr-qa">
                        @foreach ($faqs as $faq)
                            <details name="faq">
                                <summary>
                                    <h3>{{ $faq['q'] }}</h3>
                                    <i aria-hidden="true"></i>
                                </summary>
                                <p class="faq-answer">{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- 12. Finale: the luggage tag                                  -->
        <!-- ============================================================ -->
        <section id="claim" class="hr-fin">
            <div class="hr-wrap hr-fin-in">
                <p class="hr-kicker" data-reveal>Free to start</p>
                <h2 class="hr-h2" data-reveal style="--reveal-delay: 0.06s;">
                    Nobody should have to <span class="hr-mark">ask twice</span>.
                </h2>
                <p class="hr-fin-lede" data-reveal style="--reveal-delay: 0.12s;">
                    The card, the address, the QR code, the standing week and the sign-up sheet
                    are free forever, with no limit on how many names you keep. Charging for a
                    place is Pro, and none of the ticket price comes to us on any plan.
                </p>

                <div class="hr-ltag-wrap" data-reveal="panel">
                    <span class="hr-strap" aria-hidden="true"></span>
                    <div class="hr-ltag">
                        <div class="hr-ltag-top" aria-hidden="true"><span>Guest programme</span><span>No. 001</span></div>
                        <label for="es-claim-input" class="hr-ltag-label"><span aria-hidden="true">Name</span><span class="sr-only">Your schedule name</span></label>
                        <div class="hr-ltag-form">
                            <div dir="ltr" class="es-claim hr-claim">
                                <input id="es-claim-input" type="text" placeholder="your-property" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=venue') }}" class="hr-btn">
                                Put the week on a page
                                {!! $hrArrow !!}
                            </a>
                        </div>
                        <p class="hr-ltag-note">No credit card required</p>
                        <p class="hr-ltag-return" aria-hidden="true">If found, return to the front desk</p>
                    </div>
                </div>
            </div>
            <div class="hr-lanterns" aria-hidden="true"></div>
        </section>

        <div class="hr-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
