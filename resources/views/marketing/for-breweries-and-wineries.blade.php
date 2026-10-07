<x-marketing-layout>
    <x-slot name="title">Brewery and Winery Event Calendar | Most Nights Are Free</x-slot>
    <x-slot name="description">Nobody buys a ticket to trivia night. Set the taproom week once, take requests from bands and food trucks, and keep free places on the tours that fill up.</x-slot>
    <x-slot name="breadcrumbTitle">For Breweries and Wineries</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Alfa Slab One') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Playfair Display') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Great Vibes') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Bitter') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Breweries & Wineries"
        description="A taproom calendar of mostly free events - music, quizzes, visiting food trucks - with ticketing for the tours and tastings that need it."
        audience="Breweries, Wineries & Tasting Rooms"
        keywords="taproom calendar, brewery events, winery tasting schedule, brewery tour tickets, tasting room calendar, free brewery scheduling" />
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
           For-breweries-and-wineries "Two Labels" styles.

           Beer and wine are sold by their labels, and the two trades
           dress differently: the can is loud, flat-coloured and set in
           slab; the bottle is quiet, serif, a little gold. So the page
           has two voices and a seam between them. The hero sets one
           headline in both dresses and cuts it on a diagonal; down the
           page the taproom's sections speak in the can's voice, the
           tasting room's in the bottle's, and the shared ones sit on
           kraft.

           The argument is unchanged: most nights are free and need only
           a calendar, and the one priced row on the board is where
           selling starts. The board is a row of tap handles, four green
           and one copper.

           Everything is scoped under #bw. The labels, the can, the
           bottle and the two fixed bands keep their own colours in both
           modes, as printed things do; only the room changes.
           ============================================================== */

        @property --bw-s {
            syntax: "<percentage>";
            inherits: true;
            initial-value: 50%;
        }

        #bw {
            --bw-kraft: #e7d7b9;
            --bw-kraft-2: #dccaa6;
            --bw-paper: #f7efdf;
            --bw-card: #fbf6ea;
            --bw-ink: #1d1712;
            --bw-ink-2: #463b30;
            --bw-ink-3: #5a4d3f;
            --bw-line: rgba(29, 23, 18, 0.22);
            --bw-hop-ink: #2f5524;
            --bw-copper-ink: #84421a;
            --bw-vin: #5e1a28;
            --bw-rule: #a8843a;
            --bw-slab: 'Alfa Slab One', 'Rockwell Extra Bold', Rockwell, Georgia, serif;
            --bw-serif: 'Playfair Display', Didot, 'Bodoni MT', Georgia, serif;
            --bw-script: 'Great Vibes', 'Snell Roundhand', 'Brush Script MT', cursive;
            --bw-text: 'Bitter', Georgia, 'Times New Roman', serif;
            --bw-mono: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
            --bw-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.2 0 0 0 0 0.14 0 0 0 0 0.08 0 0 0 0.5 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)' opacity='0.5'/%3E%3C/svg%3E");
            position: relative;
            background-color: var(--bw-kraft);
            color: var(--bw-ink);
            font-family: var(--bw-text);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #bw {
            --bw-kraft: #15110e;
            --bw-kraft-2: #1b1612;
            --bw-paper: #201913;
            --bw-card: #271f18;
            --bw-ink: #f1e6d2;
            --bw-ink-2: #d3c6b0;
            --bw-ink-3: #ab9e89;
            --bw-line: rgba(241, 230, 210, 0.2);
            --bw-hop-ink: #a9d196;
            --bw-copper-ink: #e6a878;
            --bw-vin: #dcbf74;
            --bw-rule: #b99549;
        }

        /* The bar above takes the kraft, so the page reads as one sheet. */
        body > header.sticky {
            background-color: rgba(231, 215, 185, 0.9);
            border-bottom-color: rgba(29, 23, 18, 0.18);
        }
        .dark body > header.sticky {
            background-color: rgba(21, 17, 14, 0.9);
            border-bottom-color: rgba(241, 230, 210, 0.14);
        }

        #bw ::selection { background: #b4632a; color: #fff; }
        #bw a:focus-visible,
        #bw summary:focus-visible,
        #bw input:focus-visible {
            outline: 3px solid #b4632a;
            outline-offset: 3px;
        }
        #bw .bw-on-dark a:focus-visible,
        #bw .bw-on-dark summary:focus-visible { outline-color: #dcbf74; }

        .bw-wrap { width: min(100% - 2.5rem, 74rem); margin-inline: auto; }
        .bw-sec { position: relative; padding-block: clamp(4.25rem, 8.5vw, 7.5rem); }
        .bw-sec-kraft { background-image: var(--bw-grain); }
        .bw-sec-kraft2 { background-color: var(--bw-kraft-2); background-image: var(--bw-grain); }
        .bw-sec-paper { background-color: var(--bw-paper); }
        .dark .bw-sec-kraft,
        .dark .bw-sec-kraft2 { background-image: none; }

        /* ---------------------------------------------------------------
           The two voices
           --------------------------------------------------------------- */
        .bw-kick {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-family: var(--bw-text);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--bw-ink-2);
        }
        .bw-h2 { text-wrap: balance; color: var(--bw-ink); }
        /* The can: slab, close-set, with the second colour in the accent. */
        .bw-h2-hop {
            font-family: var(--bw-slab);
            font-weight: 400;
            font-size: clamp(2.05rem, 4.5vw, 3.5rem);
            line-height: 1.06;
            letter-spacing: -0.005em;
        }
        .bw-h2-hop span { color: var(--bw-copper-ink); }
        /* The bottle: a high-contrast serif, and the accent written by hand. */
        .bw-h2-vin {
            font-family: var(--bw-serif);
            font-weight: 700;
            font-size: clamp(2.2rem, 4.9vw, 3.9rem);
            line-height: 1.08;
            letter-spacing: -0.01em;
        }
        .bw-h2-vin span {
            font-family: var(--bw-script);
            font-weight: 400;
            font-size: 1.32em;
            line-height: 0.8;
            letter-spacing: 0;
            color: var(--bw-vin);
        }
        /* Shared sections wear both: the serif speaks and the slab answers. */
        .bw-h2-both {
            font-family: var(--bw-serif);
            font-weight: 700;
            font-size: clamp(2.2rem, 4.9vw, 3.9rem);
            line-height: 1.08;
            letter-spacing: -0.01em;
        }
        .bw-h2-both span {
            font-family: var(--bw-slab);
            font-weight: 400;
            font-size: 0.84em;
            letter-spacing: 0;
            color: var(--bw-hop-ink);
        }
        .bw-lede { color: var(--bw-ink-2); font-size: 1.125rem; max-width: 40rem; }
        .bw-head-c { display: grid; justify-items: center; gap: 1.1rem; text-align: center; margin-bottom: clamp(2.5rem, 5vw, 4rem); }
        .bw-head-l { display: grid; justify-items: start; gap: 1.1rem; }

        /* The crown cap: a crimped disc, used for numbers, bullets and the free tag. */
        .bw-cap {
            --c: #3d6b2f;
            --t: #f7efdf;
            --sz: 3.1rem;
            display: inline-grid;
            place-items: center;
            flex: none;
            width: var(--sz);
            aspect-ratio: 1;
            background-color: var(--c);
            background-image:
                radial-gradient(circle at 34% 28%, rgba(255, 255, 255, 0.42), rgba(255, 255, 255, 0) 40%),
                radial-gradient(circle closest-side, rgba(0, 0, 0, 0) 0 74%, rgba(0, 0, 0, 0.3) 76% 100%);
            color: var(--t);
            clip-path: circle(50%);
            -webkit-mask: radial-gradient(circle closest-side, #000 90%, transparent 91.5%), repeating-conic-gradient(#000 0 10.2857deg, transparent 0 17.1428deg);
            mask: radial-gradient(circle closest-side, #000 90%, transparent 91.5%), repeating-conic-gradient(#000 0 10.2857deg, transparent 0 17.1428deg);
            font-family: var(--bw-slab);
            font-weight: 400;
            font-size: calc(var(--sz) * 0.27);
            letter-spacing: 0.03em;
            line-height: 1;
            text-transform: uppercase;
        }
        .bw-cap-sm { --sz: 2.1rem; }
        .bw-cap-gold { --c: #d6a53a; --t: #1d1712; }
        .bw-cap-gold.bw-tap-cost { font-size: 1.15rem; }
        .bw-cap-copper { --c: #96501f; }
        .bw-cap-wine { --c: #5e1a28; --t: #f0dca6; }
        .bw-cap-steel { --c: #c5cad0; --t: #1d1712; }

        /* Plan tags, one per trade. */
        .bw-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.28rem 0.7rem 0.24rem 0.3rem;
            border: 1.5px solid currentColor;
            border-radius: 999px;
            font-family: var(--bw-text);
            font-weight: 700;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1;
            white-space: nowrap;
            color: var(--bw-hop-ink);
        }
        .bw-tag::before {
            content: "";
            width: 0.95rem;
            aspect-ratio: 1;
            background-color: currentColor;
            clip-path: circle(50%);
            -webkit-mask: radial-gradient(circle closest-side, #000 78%, transparent 80%), repeating-conic-gradient(#000 0 15deg, transparent 0 30deg);
            mask: radial-gradient(circle closest-side, #000 78%, transparent 80%), repeating-conic-gradient(#000 0 15deg, transparent 0 30deg);
        }
        .bw-tag-paid { color: var(--bw-copper-ink); }
        .bw-note { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 0.9rem; color: var(--bw-ink-2); font-size: 1rem; }

        /* Lists capped the brewery way. */
        .bw-caps { display: grid; gap: 0.95rem; }
        .bw-caps li { position: relative; padding-inline-start: 1.9rem; color: var(--bw-ink-2); }
        .bw-caps li::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0.36em;
            width: 1.05rem;
            aspect-ratio: 1;
            background-color: var(--bw-hop-ink);
            clip-path: circle(50%);
            -webkit-mask: radial-gradient(circle closest-side, #000 78%, transparent 80%), repeating-conic-gradient(#000 0 15deg, transparent 0 30deg);
            mask: radial-gradient(circle closest-side, #000 78%, transparent 80%), repeating-conic-gradient(#000 0 15deg, transparent 0 30deg);
        }
        .bw-caps li b { color: var(--bw-ink); font-weight: 700; }

        /* Buttons: a painted plate with a pressed lower edge. */
        .bw-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 1rem 1.6rem 0.95rem;
            border-radius: 0.55rem;
            background-color: #3d6b2f;
            color: #f7efdf;
            font-family: var(--bw-slab);
            font-size: 1.0625rem;
            letter-spacing: 0.02em;
            line-height: 1;
            box-shadow: inset 0 0 0 2px rgba(247, 239, 223, 0.22), 0 0.32rem 0 #223f1a;
            transition: translate 0.16s ease, box-shadow 0.16s ease;
        }
        .bw-btn:hover { translate: 0 0.16rem; box-shadow: inset 0 0 0 2px rgba(247, 239, 223, 0.22), 0 0.16rem 0 #223f1a; }
        .bw-btn svg { width: 1.15rem; height: 1.15rem; transition: translate 0.16s ease; }
        .bw-btn:hover svg { translate: 0.22rem 0; }
        .bw-btn-vin {
            background-color: transparent;
            color: var(--bw-ink);
            font-family: var(--bw-serif);
            font-weight: 700;
            font-size: 1.125rem;
            letter-spacing: 0;
            box-shadow: inset 0 0 0 1.5px var(--bw-ink), inset 0 0 0 4px transparent, inset 0 0 0 5px var(--bw-rule);
        }
        .bw-btn-vin:hover { translate: none; background-color: var(--bw-ink); color: var(--bw-kraft); box-shadow: inset 0 0 0 1.5px var(--bw-ink), inset 0 0 0 4px transparent, inset 0 0 0 5px var(--bw-rule); }
        .bw-btn-vin:hover svg { translate: 0 0.2rem; }
        .bw-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--bw-ink);
            border-bottom: 2px solid var(--bw-copper-ink);
            padding-bottom: 0.12rem;
            transition: gap 0.2s ease;
        }
        .bw-more:hover { gap: 0.85rem; }
        .bw-more svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           The rail: section index, wide screens only
           --------------------------------------------------------------- */
        .bw-rail { display: none; }
        @media (min-width: 1560px) {
            /* The nav is a box the size of the page that clips the rail, so the rail stays fixed
               to the screen and still ends where the page does, instead of riding on over the
               site footer. */
            .bw-rail { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .bw-rail ol { position: fixed; left: 1.25rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.5rem; pointer-events: auto; }
            /* The rail is printed in the kraft's own ink, which cannot be read on the painted
               bands (the hero, the can's green, the cellar, the finale): those pass over it. The
               shelf goes one higher still, so the can and the bottle keep standing on the hero. */
            .bw-stage, .bw-band-hop, .bw-cellar, .bw-fin { z-index: 41; }
            .bw-shelf { z-index: 42; }
            .bw-rail a { display: flex; align-items: center; gap: 0.6rem; min-height: 1.5rem; color: var(--bw-ink-2); font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
            .bw-rail a i {
                width: 0.85rem;
                aspect-ratio: 1;
                background-color: var(--bw-ink-3);
                clip-path: circle(50%);
                -webkit-mask: radial-gradient(circle closest-side, #000 74%, transparent 76%), repeating-conic-gradient(#000 0 15deg, transparent 0 30deg);
                mask: radial-gradient(circle closest-side, #000 74%, transparent 76%), repeating-conic-gradient(#000 0 15deg, transparent 0 30deg);
                transition: scale 0.25s ease, background-color 0.25s ease;
            }
            .bw-rail a span { opacity: 0; translate: -0.3rem 0; transition: opacity 0.2s ease, translate 0.2s ease; }
            .bw-rail a:hover span, .bw-rail a:focus-visible span, .bw-rail a.is-active span { opacity: 1; translate: 0 0; }
            .bw-rail a.is-active i { background-color: #b4632a; scale: 1.45; }
        }

        /* ---------------------------------------------------------------
           Hero: one headline, two dresses, cut on the bias
           --------------------------------------------------------------- */
        .bw-hero { position: relative; }
        .bw-stage {
            --bw-s: 50%;
            --bw-h: clamp(21rem, 54vh, 31rem);
            --bw-k: 0.24;
            --bw-fs: clamp(2.5rem, 6.6vw, 5.9rem);
            --bw-lh: calc(var(--bw-fs) * 1.1);
            --bw-e: 2rem;
            --bw-g: 1.25rem;
            --bw-gap: calc(var(--bw-fs) * 0.34);
            position: relative;
            height: var(--bw-h);
            overflow: hidden;
            transition: --bw-s 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .bw-side { position: absolute; inset: 0; }
        .bw-side-vin { background-color: #f7efdf; color: #5e1a28; }
        .bw-side-hop {
            background-color: #3d6b2f;
            color: #f7efdf;
            clip-path: polygon(0 0, calc(var(--bw-s) + var(--bw-k) * var(--bw-h) / 2) 0, calc(var(--bw-s) - var(--bw-k) * var(--bw-h) / 2) 100%, 0 100%);
        }
        .bw-seam {
            position: absolute;
            inset: 0;
            background-color: #1d1712;
            clip-path: polygon(calc(var(--bw-s) + var(--bw-k) * var(--bw-h) / 2 - 2px) 0, calc(var(--bw-s) + var(--bw-k) * var(--bw-h) / 2 + 2px) 0, calc(var(--bw-s) - var(--bw-k) * var(--bw-h) / 2 + 2px) 100%, calc(var(--bw-s) - var(--bw-k) * var(--bw-h) / 2 - 2px) 100%);
            pointer-events: none;
        }
        /* Flat can art on the left: a low copper sun and a run of pinstripes. */
        .bw-art { position: absolute; inset: 0; pointer-events: none; }
        .bw-art-hop {
            background:
                radial-gradient(circle at 4% 126%, #b4632a 0 17rem, rgba(180, 99, 42, 0) 17.05rem),
                radial-gradient(circle at 4% 126%, rgba(247, 239, 223, 0) 0 18.6rem, rgba(247, 239, 223, 0.85) 18.65rem 18.95rem, rgba(247, 239, 223, 0) 19rem),
                repeating-linear-gradient(104deg, rgba(34, 63, 26, 0) 0 2.6rem, rgba(34, 63, 26, 0.55) 2.6rem 2.95rem),
                var(--bw-grain);
        }
        /* A label's furniture on the right: a wine ground rising, and hairlines of gold. */
        .bw-art-vin {
            background:
                radial-gradient(circle at 92% 124%, #5e1a28 0 20rem, rgba(94, 26, 40, 0) 20.05rem),
                radial-gradient(circle at 92% 124%, rgba(168, 132, 58, 0) 0 21.1rem, #a8843a 21.15rem 21.3rem, rgba(168, 132, 58, 0) 21.35rem 21.75rem, #a8843a 21.8rem 21.95rem, rgba(168, 132, 58, 0) 22rem),
                var(--bw-grain);
        }
        .bw-art-vin::after {
            content: "";
            position: absolute;
            inset: 1rem;
            border: 1px solid #a8843a;
            outline: 1px solid #a8843a;
            outline-offset: 3px;
            opacity: 0.55;
        }
        .bw-eyewrap { display: block; }
        .bw-head {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin: 0;
        }
        .bw-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            column-gap: var(--bw-gap);
            align-items: center;
            height: var(--bw-lh);
            white-space: nowrap;
        }
        .bw-row > span:first-child { justify-self: end; text-align: end; }
        .bw-row > span:last-child { justify-self: start; }
        /* Each line steps sideways by the seam's own slope, so the cut always falls in a word space. */
        .bw-row-1 { translate: calc(var(--bw-k) * -1 * ((var(--bw-e) + var(--bw-g)) / 2 - var(--bw-lh) / 2)) 0; }
        .bw-row-2 { translate: calc(var(--bw-k) * -1 * ((var(--bw-e) + var(--bw-g)) / 2 + var(--bw-lh) / 2)) 0; }
        #bw .bw-eye {
            height: var(--bw-e);
            column-gap: 1.1rem;
            margin-bottom: var(--bw-g);
            translate: calc(var(--bw-k) * ((var(--bw-g) + 2 * var(--bw-lh)) / 2)) 0;
            font-family: var(--bw-text);
            font-weight: 700;
            font-size: clamp(0.68rem, 1.05vw, 0.8125rem);
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .bw-head-hop { font-family: var(--bw-slab); font-weight: 400; text-transform: uppercase; }
        .bw-head-hop .bw-row-1,
        .bw-head-hop .bw-row-2 { font-size: calc(var(--bw-fs) * 0.8); line-height: 1; text-shadow: 0.045em 0.055em 0 #223f1a; }
        .bw-head-hop .bw-row-2 em { font-style: normal; color: #f6c98a; }
        .bw-head-vin { font-family: var(--bw-serif); font-weight: 700; }
        .bw-head-vin .bw-row-1,
        .bw-head-vin .bw-row-2 { font-size: var(--bw-fs); line-height: 1; letter-spacing: -0.012em; }
        .bw-head-vin .bw-row-2 em { font-family: var(--bw-script); font-style: normal; font-weight: 400; font-size: 1.42em; letter-spacing: 0; line-height: 0.7; }
        .bw-head-vin .bw-eye { font-family: var(--bw-serif); letter-spacing: 0.2em; }

        @media (max-width: 759px) {
            .bw-stage { --bw-h: 30rem; }
            .bw-side-hop { clip-path: polygon(0 0, 100% 0, 100% calc(56% - 1.15rem), 0 calc(56% + 1.15rem)); }
            .bw-seam { clip-path: polygon(0 calc(56% + 1.15rem - 2px), 100% calc(56% - 1.15rem - 2px), 100% calc(56% - 1.15rem + 2px), 0 calc(56% + 1.15rem + 2px)); }
            .bw-head { display: grid; grid-template-rows: minmax(0, 1fr) auto 44%; padding-inline: 1.25rem; }
            .bw-row,
            #bw .bw-eye { display: block; height: auto; translate: none; white-space: normal; text-align: center; }
            #bw .bw-eye { margin-bottom: 1rem; font-size: 0.72rem; }
            .bw-eyewrap { align-self: end; }
            .bw-head-hop .bw-row-1 { font-size: clamp(2.1rem, 11.2vw, 3.3rem); line-height: 1.02; padding-bottom: 2.7rem; text-wrap: balance; }
            /* The bottle's copy of this line lies under the can's half and is never meant to
               be read here. It ended level with the seam, so from about 430px up, where it fits
               on one line, the feet of "a ticket" showed below the cut. */
            .bw-head-vin .bw-row-1 { visibility: hidden; }
            .bw-row-2 { align-self: start; padding-top: 2.2rem; }
            .bw-head-vin .bw-row-2 { font-size: clamp(2.5rem, 13vw, 3.9rem); line-height: 1.05; }
            .bw-head-vin .bw-row-2 em { display: inline-block; line-height: 0.9; margin-inline-start: 0.14em; }
            .bw-art-hop { background: radial-gradient(circle at 0% 0%, #b4632a 0 7.5rem, rgba(180, 99, 42, 0) 7.55rem), repeating-linear-gradient(104deg, rgba(34, 63, 26, 0) 0 2.2rem, rgba(34, 63, 26, 0.55) 2.2rem 2.5rem), var(--bw-grain); }
            .bw-art-vin { background: radial-gradient(circle at 100% 112%, #5e1a28 0 8.5rem, rgba(94, 26, 40, 0) 8.55rem), var(--bw-grain); }
            .bw-art-vin::after { inset: 0.6rem; }
        }

        /* The shelf: a can and a bottle, and what the page is for between them. */
        .bw-shelf {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: end;
            gap: clamp(1.25rem, 4vw, 3.5rem);
        }
        .bw-shelf-copy { align-self: center; padding-block: 2.25rem 2.5rem; text-align: center; display: grid; justify-items: center; }
        .bw-shelf-copy p { max-width: 34rem; font-size: clamp(1.0625rem, 1.5vw, 1.2rem); color: var(--bw-ink-2); }
        .bw-cta { display: flex; flex-wrap: wrap; justify-content: center; gap: 1rem 1.1rem; margin-top: 1.6rem; }

        /* The can: aluminium, a wrapped label, and the light that says it is round. */
        .bw-can {
            position: relative;
            font-size: clamp(0.72rem, 1.15vw, 1rem);
            width: 9.4em;
            height: 17.6em;
            margin-top: -7.5em;
            filter: drop-shadow(0 1.1em 0.9em rgba(29, 23, 18, 0.38));
        }
        .bw-can-lid {
            position: absolute;
            top: 0;
            left: 0.45em;
            right: 0.45em;
            height: 2.1em;
            border-radius: 50%;
            background: radial-gradient(ellipse at 50% 58%, #8f969d 0 34%, #dfe3e6 36% 47%, #8a9198 50% 70%, #c9ced3 72% 100%);
        }
        .bw-can-lid::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 42%;
            width: 1.5em;
            height: 0.62em;
            translate: -50% 0;
            border-radius: 50%;
            background: #70777e;
            box-shadow: inset 0 0.08em 0.1em rgba(0, 0, 0, 0.45);
        }
        .bw-can-body {
            position: absolute;
            inset: 1.05em 0 0 0;
            overflow: hidden;
            border-radius: 0.95em 0.95em 0.85em 0.85em / 1.55em 1.55em 0.95em 0.95em;
            background: linear-gradient(#c3c8cd, #9aa1a8 9%, #b6bcc2 12%, #b6bcc2 90%, #8d949b 94%, #6f767d);
        }
        .bw-can-label {
            position: absolute;
            inset: 1.55em 0 1.15em 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            color: #f7efdf;
            background:
                radial-gradient(circle at 50% 41%, #b4632a 0 3.25em, rgba(180, 99, 42, 0) 3.3em),
                radial-gradient(circle at 50% 41%, rgba(247, 239, 223, 0) 0 3.75em, #f7efdf 3.8em 3.95em, rgba(247, 239, 223, 0) 4em),
                repeating-linear-gradient(104deg, rgba(34, 63, 26, 0) 0 1.1em, rgba(34, 63, 26, 0.6) 1.1em 1.28em),
                #3d6b2f;
        }
        .bw-can-top { margin-top: 1.05em; font-family: var(--bw-text); font-weight: 700; font-size: 0.62em; letter-spacing: 0.3em; text-transform: uppercase; }
        .bw-can-big { margin-top: 0.86em; font-family: var(--bw-slab); font-size: 2.3em; line-height: 1; text-transform: uppercase; text-shadow: 0.05em 0.06em 0 #223f1a; }
        .bw-can-sub { margin-top: 0.5em; font-family: var(--bw-slab); font-size: 0.74em; letter-spacing: 0.04em; text-transform: uppercase; }
        .bw-can-foot {
            margin-top: auto;
            align-self: stretch;
            padding: 0.55em 0 0.45em;
            background: #f7efdf;
            color: #1d1712;
            font-family: var(--bw-mono);
            font-size: 0.5em;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .bw-can-body::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.55) 0, rgba(0, 0, 0, 0.14) 13%, rgba(255, 255, 255, 0.32) 27%, rgba(255, 255, 255, 0.04) 42%, rgba(0, 0, 0, 0) 60%, rgba(0, 0, 0, 0.2) 83%, rgba(0, 0, 0, 0.56) 100%);
            pointer-events: none;
        }

        /* The bottle: dark glass, a foil capsule, and the label that sells it. */
        .bw-bottle {
            position: relative;
            font-size: clamp(0.72rem, 1.15vw, 1rem);
            width: 8.6em;
            height: 24.6em;
            margin-top: -14.5em;
            filter: drop-shadow(0 1.1em 0.9em rgba(29, 23, 18, 0.4));
        }
        .bw-bottle-neck {
            position: absolute;
            top: 0;
            left: 50%;
            width: 2.75em;
            height: 9em;
            translate: -50% 0;
            border-radius: 0.3em 0.3em 0 0;
            background:
                linear-gradient(90deg, rgba(0, 0, 0, 0.5) 0, rgba(255, 255, 255, 0.24) 30%, rgba(0, 0, 0, 0) 55%, rgba(0, 0, 0, 0.5) 100%),
                linear-gradient(#5e1a28 0 3.4em, #c9a85a 3.4em 3.62em, #5e1a28 3.62em 3.9em, #14261a 3.9em);
        }
        .bw-bottle-body {
            position: absolute;
            inset: 7.2em 0 0 0;
            overflow: hidden;
            border-radius: 3.1em 3.1em 0.8em 0.8em / 4.6em 4.6em 0.8em 0.8em;
            background: linear-gradient(90deg, #08120b 0, #1c3323 18%, #36573f 28%, #17291c 46%, #0d1a11 78%, #060d08 100%);
        }
        .bw-bottle-label {
            position: absolute;
            inset: 5.3em 0.5em 2em 0.5em;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: #f7efdf;
            color: #1d1712;
            clip-path: var(--bw-deckle);
        }
        .bw-bottle-label::before {
            content: "";
            position: absolute;
            inset: 0.5em;
            border: 1px solid #a8843a;
            outline: 1px solid #a8843a;
            outline-offset: 2px;
        }
        .bw-crest {
            display: grid;
            place-items: center;
            width: 2em;
            aspect-ratio: 1;
            border-radius: 50%;
            background: #5e1a28;
            color: #f0dca6;
            font-family: var(--bw-serif);
            font-weight: 700;
            font-size: 0.82em;
            box-shadow: 0 0 0 2px #f7efdf, 0 0 0 3px #a8843a;
        }
        .bw-bl-script { margin-top: 0.18em; font-family: var(--bw-script); font-size: 1.9em; line-height: 1; color: #5e1a28; }
        .bw-bl-name { margin-top: 0.1em; font-family: var(--bw-serif); font-weight: 700; font-size: 0.78em; letter-spacing: 0.24em; text-transform: uppercase; }
        .bw-bl-rule { width: 2.4em; height: 1px; margin-block: 0.5em 0.42em; background: #a8843a; }
        .bw-bl-year { font-family: var(--bw-serif); font-size: 0.74em; letter-spacing: 0.3em; }
        .bw-bottle-body::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.42) 0, rgba(255, 255, 255, 0.16) 24%, rgba(255, 255, 255, 0) 40%, rgba(0, 0, 0, 0) 66%, rgba(0, 0, 0, 0.46) 100%);
            pointer-events: none;
        }

        @media (max-width: 759px) {
            .bw-shelf { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 0 1.5rem; }
            .bw-can { justify-self: end; font-size: 0.6rem; margin-top: -5em; }
            .bw-bottle { justify-self: start; font-size: 0.6rem; grid-column: 2; grid-row: 1; margin-top: -10.6em; }
            .bw-shelf-copy { grid-column: 1 / -1; grid-row: 2; padding-block: 1.75rem 2.25rem; }
        }

        /* The board: five taps, four green handles and one copper. */
        .bw-board {
            position: relative;
            background-color: var(--bw-card);
            border-radius: 0 0 0.9rem 0.9rem;
            box-shadow: 0 1.4rem 2.2rem -1.4rem rgba(29, 23, 18, 0.55);
        }
        .dark .bw-board { box-shadow: 0 0 0 1px rgba(241, 230, 210, 0.12), 0 1.4rem 2.2rem -1.4rem #000; }
        .bw-board-rail {
            height: 1.05rem;
            background: repeating-linear-gradient(90deg, rgba(0, 0, 0, 0) 0 5rem, rgba(0, 0, 0, 0.16) 5rem 5.06rem), linear-gradient(#6b4226, #3d2413);
            box-shadow: 0 0.2rem 0.3rem rgba(0, 0, 0, 0.25);
        }
        .bw-board-title {
            padding: 1.1rem 1.4rem 0;
            text-align: center;
            font-family: var(--bw-text);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--bw-ink-2);
        }
        .bw-taps { display: grid; grid-template-columns: minmax(0, 1fr); }
        .bw-tap {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            grid-template-areas: "handle day cost" "handle what cost";
            align-items: center;
            column-gap: 1rem;
            padding: 1.1rem 1.25rem;
        }
        .bw-tap + .bw-tap { border-top: 1px solid var(--bw-line); }
        .bw-handle {
            --h: #3d6b2f;
            grid-area: handle;
            position: relative;
            display: grid;
            place-items: center;
            width: 2.1rem;
            height: 4.6rem;
            margin-bottom: 0.7rem;
            border-radius: 0.9rem 0.9rem 0.45rem 0.45rem / 1.3rem 1.3rem 0.45rem 0.45rem;
            background-color: var(--h);
            background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.32), rgba(255, 255, 255, 0.26) 34%, rgba(0, 0, 0, 0) 60%, rgba(0, 0, 0, 0.28));
            transform-origin: 50% 110%;
            transition: rotate 0.35s cubic-bezier(0.34, 1.5, 0.64, 1);
        }
        .bw-handle::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            width: 0.85rem;
            height: 0.7rem;
            translate: -50% 0;
            border-radius: 0 0 0.2rem 0.2rem;
            background: linear-gradient(90deg, #7b838a, #e3e6e9 40%, #6f767d);
        }
        .bw-handle i {
            writing-mode: vertical-rl;
            rotate: 180deg;
            font-family: var(--bw-slab);
            font-style: normal;
            font-size: 0.72rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #f7efdf;
        }
        .bw-tap-paid .bw-handle { --h: #b4632a; }
        html.es-anim #bw .bw-handle { animation: bw-pull 1s cubic-bezier(0.34, 1.4, 0.64, 1) calc(1.3s + var(--i, 0) * 0.14s) both; }
        @keyframes bw-pull { 0%, 100% { rotate: 0deg; } 45% { rotate: 13deg; } }
        .bw-tap:hover .bw-handle { rotate: 9deg; }
        .bw-tap-day { grid-area: day; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--bw-ink-3); }
        .bw-tap-what { grid-area: what; min-width: 0; }
        .bw-tap-name { font-family: var(--bw-slab); font-size: 1.3rem; line-height: 1.15; color: var(--bw-ink); }
        .bw-tap-note { margin-top: 0.2rem; font-size: 0.95rem; color: var(--bw-ink-2); }
        .bw-tap-cost { grid-area: cost; --sz: 3.9rem; font-size: 0.82rem; }
        .bw-board-line {
            margin-top: 1.75rem;
            text-align: center;
            font-family: var(--bw-serif);
            font-weight: 700;
            font-size: clamp(1.2rem, 2.2vw, 1.6rem);
            color: var(--bw-ink);
            text-wrap: balance;
        }
        @media (min-width: 900px) {
            .bw-taps { grid-template-columns: repeat(5, minmax(0, 1fr)); }
            .bw-tap {
                grid-template-columns: minmax(0, 1fr);
                grid-template-areas: "handle" "day" "what" "cost";
                justify-items: center;
                align-content: start;
                row-gap: 0.3rem;
                padding: 1.4rem 1rem 1.6rem;
                text-align: center;
            }
            .bw-tap + .bw-tap { border-top: 0; border-inline-start: 1px solid var(--bw-line); }
            .bw-handle { width: 2.5rem; height: 6rem; margin-bottom: 1.2rem; }
            .bw-handle i { font-size: 0.8rem; }
            .bw-tap-what { min-height: 4.6rem; }
            .bw-tap-cost { margin-top: 0.6rem; --sz: 4.5rem; font-size: 0.9rem; }
        }
        .bw-hero-foot { padding-bottom: clamp(3.5rem, 7vw, 6rem); }

        /* ---------------------------------------------------------------
           01. Why it is free: four back labels
           --------------------------------------------------------------- */
        .bw-backs { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 640px) { .bw-backs { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .bw-backs { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.4rem; } }
        .bw-back-wrap { filter: drop-shadow(0 0.8rem 0.7rem rgba(29, 23, 18, 0.22)); rotate: var(--r, 0deg); transition: rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.3s ease; }
        .bw-back-wrap:hover { rotate: 0deg; translate: 0 -0.35rem; }
        /* Each of these wrappers is also what the reveal watches, and the shared reveal ends on
           filter: none, which outranks a class: every label lost its shadow once it had been
           revealed. The id puts the shadows back on top (the sheets are cut with clip-path, so
           it cannot be a box-shadow). */
        #bw .bw-back-wrap { filter: drop-shadow(0 0.8rem 0.7rem rgba(29, 23, 18, 0.22)); }
        #bw .bw-lab-wrap { filter: drop-shadow(0 1rem 0.9rem rgba(29, 23, 18, 0.26)); }
        #bw .bw-who-wrap { filter: drop-shadow(0 0.9rem 0.8rem rgba(29, 23, 18, 0.24)); }
        #bw .bw-neck { filter: drop-shadow(0 0.7rem 0.6rem rgba(29, 23, 18, 0.26)); }
        #bw .bw-fin-wrap { filter: drop-shadow(0 1.6rem 1.6rem rgba(0, 0, 0, 0.42)); }
        .bw-back { display: flex; flex-direction: column; height: 100%; background-color: #f7efdf; color: #1d1712; }
        .bw-back p { padding: 1rem 1.25rem 1.4rem; color: #463b30; font-size: 1rem; }
        .bw-back-can { border-radius: 0.6rem; overflow: hidden; }
        .bw-back-can h3 {
            padding: 1.5rem 1.25rem 1.2rem;
            background-color: #3d6b2f;
            background-image: repeating-linear-gradient(104deg, rgba(34, 63, 26, 0) 0 1.5rem, rgba(34, 63, 26, 0.5) 1.5rem 1.7rem);
            color: #f7efdf;
            font-family: var(--bw-slab);
            font-weight: 400;
            font-size: 1.4rem;
            line-height: 1.12;
            min-height: 6.3rem;
            display: flex;
            align-items: flex-end;
        }
        .bw-back-can:nth-of-type(1) h3 { background-color: #3d6b2f; }
        .bw-back-btl { clip-path: var(--bw-deckle); position: relative; }
        .bw-back-btl::before { content: ""; position: absolute; inset: 0.55rem; border: 1px solid #a8843a; outline: 1px solid #a8843a; outline-offset: 2px; pointer-events: none; }
        .bw-back-btl h3 {
            padding: 1.75rem 1.5rem 0.2rem;
            font-family: var(--bw-serif);
            font-weight: 700;
            font-size: 1.5rem;
            line-height: 1.14;
            color: #5e1a28;
            min-height: 5.9rem;
            display: flex;
            align-items: flex-end;
        }
        .bw-back-btl p { padding: 0.8rem 1.5rem 1.75rem; }
        .bw-why-note { justify-content: center; margin-top: clamp(2.25rem, 4vw, 3.25rem); text-align: center; }

        /* ---------------------------------------------------------------
           02. The rhythm: the can's band, and a label proof
           --------------------------------------------------------------- */
        .bw-band-hop {
            background-color: #35602a;
            background-image: repeating-linear-gradient(104deg, rgba(34, 63, 26, 0) 0 3.4rem, rgba(34, 63, 26, 0.5) 3.4rem 3.8rem);
            color: #f7efdf;
        }
        #bw .bw-band-hop .bw-kick { color: #e6efd9; }
        #bw .bw-band-hop .bw-h2 { color: #f7efdf; }
        #bw .bw-band-hop .bw-h2-hop span { color: #f6c98a; }
        #bw .bw-band-hop .bw-lede,
        #bw .bw-band-hop .bw-caps li,
        #bw .bw-band-hop .bw-note { color: #e6efd9; }
        #bw .bw-band-hop .bw-caps li b { color: #fff; }
        #bw .bw-band-hop .bw-caps li::before { background-color: #f6c98a; }
        #bw .bw-band-hop .bw-tag { color: #f7efdf; }
        .bw-duo { display: grid; gap: clamp(2.5rem, 5vw, 4.5rem); grid-template-columns: minmax(0, 1fr); align-items: center; }
        @media (min-width: 980px) { .bw-duo { grid-template-columns: minmax(0, 1.02fr) minmax(0, 0.98fr); } }
        .bw-duo-copy { display: grid; gap: 1.4rem; justify-items: start; }
        .bw-proof {
            position: relative;
            padding: clamp(1.4rem, 3vw, 2.2rem);
            background-color: #fbf6ea;
            color: #1d1712;
            box-shadow: 0 1.6rem 2.4rem -1.2rem rgba(0, 0, 0, 0.5);
            rotate: 1.2deg;
        }
        /* Crop marks at the four corners of the sheet. */
        .bw-proof::before {
            content: "";
            position: absolute;
            inset: 0.45rem;
            pointer-events: none;
            background:
                linear-gradient(#1d1712, #1d1712) 0 0.7rem / 0.5rem 1px no-repeat,
                linear-gradient(#1d1712, #1d1712) 0.7rem 0 / 1px 0.5rem no-repeat,
                linear-gradient(#1d1712, #1d1712) 100% 0.7rem / 0.5rem 1px no-repeat,
                linear-gradient(#1d1712, #1d1712) calc(100% - 0.7rem) 0 / 1px 0.5rem no-repeat,
                linear-gradient(#1d1712, #1d1712) 0 calc(100% - 0.7rem) / 0.5rem 1px no-repeat,
                linear-gradient(#1d1712, #1d1712) 0.7rem 100% / 1px 0.5rem no-repeat,
                linear-gradient(#1d1712, #1d1712) 100% calc(100% - 0.7rem) / 0.5rem 1px no-repeat,
                linear-gradient(#1d1712, #1d1712) calc(100% - 0.7rem) 100% / 1px 0.5rem no-repeat;
            opacity: 0.7;
        }
        .bw-proof-label { display: grid; grid-template-columns: minmax(0, 1fr); border: 2px solid #1d1712; }
        .bw-proof-art {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 5.6rem;
            padding: 0.6rem 1.2rem 0.6rem 1.05rem;
            background:
                radial-gradient(circle at 3.6rem 50%, #b4632a 0 2.35rem, rgba(180, 99, 42, 0) 2.4rem),
                radial-gradient(circle at 3.6rem 50%, rgba(247, 239, 223, 0) 0 2.7rem, #f7efdf 2.75rem 2.86rem, rgba(247, 239, 223, 0) 2.9rem),
                repeating-linear-gradient(104deg, rgba(34, 63, 26, 0) 0 1.1rem, rgba(34, 63, 26, 0.6) 1.1rem 1.3rem),
                #3d6b2f;
            color: #f7efdf;
            font-family: var(--bw-slab);
            line-height: 1;
            text-transform: uppercase;
            text-shadow: 0.05em 0.06em 0 #223f1a;
        }
        .bw-proof-art b { width: 5.1rem; font-weight: 400; font-size: 1.05rem; text-align: center; }
        .bw-proof-art span { font-size: clamp(1.1rem, 2.4vw, 1.5rem); letter-spacing: 0.02em; text-align: end; }
        .bw-proof-panel { padding: 1.15rem 1.2rem 1.25rem; min-width: 0; }
        .bw-proof-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.3rem 1rem; padding-bottom: 0.7rem; border-bottom: 2px solid #1d1712; }
        .bw-proof-head h3 { font-family: var(--bw-slab); font-weight: 400; font-size: 1.15rem; line-height: 1.15; }
        .bw-proof-head span { font-family: var(--bw-mono); font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: #463b30; }
        .bw-proof-row { display: flex; align-items: baseline; gap: 0.6rem; padding-block: 0.62rem; border-bottom: 1px dashed rgba(29, 23, 18, 0.3); }
        .bw-proof-row b { font-weight: 700; }
        .bw-proof-row i { flex: 1; min-width: 0.75rem; border-bottom: 2px dotted rgba(29, 23, 18, 0.35); translate: 0 -0.28em; }
        .bw-proof-row span { font-family: var(--bw-mono); font-size: 0.78rem; color: #463b30; text-align: end; }
        @media (max-width: 520px) {
            .bw-proof-row { flex-wrap: wrap; gap: 0.1rem 0.6rem; }
            .bw-proof-row i { display: none; }
            .bw-proof-row span { flex-basis: 100%; text-align: start; }
        }
        .bw-proof-sub { margin-top: 0.95rem; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; color: #84421a; }
        .bw-proof-panel > p:last-child { margin-top: 0.3rem; font-size: 0.95rem; color: #463b30; }
        .bw-proof-bar { display: flex; align-items: center; gap: 0.35rem; margin-top: 1rem; font-family: var(--bw-mono); font-size: 0.62rem; letter-spacing: 0.14em; text-transform: uppercase; color: #5a4d3f; }
        .bw-proof-bar i { width: 1.1rem; height: 0.7rem; background: var(--c); box-shadow: inset 0 0 0 1px rgba(29, 23, 18, 0.25); }
        .bw-proof-bar span { margin-inline-start: auto; }

        /* ---------------------------------------------------------------
           03. When you sell one: two bottle labels
           --------------------------------------------------------------- */
        .bw-pair { display: grid; gap: 2rem; grid-template-columns: minmax(0, 1fr); max-width: 60rem; margin-inline: auto; }
        @media (min-width: 820px) { .bw-pair { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2.5rem; } }
        .bw-lab-wrap { filter: drop-shadow(0 1rem 0.9rem rgba(29, 23, 18, 0.26)); }
        .bw-lab {
            position: relative;
            height: 100%;
            padding: 2.4rem 2rem 2.3rem;
            background-color: #fbf6ea;
            color: #1d1712;
            clip-path: var(--bw-deckle);
            text-align: center;
        }
        .bw-lab::before { content: ""; position: absolute; inset: 0.7rem; border: 1px solid #a8843a; outline: 1px solid #a8843a; outline-offset: 3px; pointer-events: none; }
        .bw-lab-crest {
            display: grid;
            place-items: center;
            width: 3rem;
            aspect-ratio: 1;
            margin: 0 auto 0.9rem;
            border-radius: 50%;
            background-color: #5e1a28;
            color: #f0dca6;
            font-family: var(--bw-serif);
            font-weight: 700;
            font-size: 1.05rem;
            box-shadow: 0 0 0 3px #fbf6ea, 0 0 0 4px #a8843a;
        }
        .bw-lab h3 { font-family: var(--bw-serif); font-weight: 700; font-size: clamp(1.55rem, 2.6vw, 2rem); line-height: 1.12; color: #5e1a28; text-wrap: balance; }
        #bw .bw-lab .bw-tag { margin-top: 0.85rem; color: #2f5524; }
        .bw-lab-lede { margin-top: 0.9rem; font-family: var(--bw-script); font-size: 1.75rem; line-height: 1.1; color: #5e1a28; }
        .bw-lab ul { display: grid; gap: 0.75rem; margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid rgba(168, 132, 58, 0.7); text-align: start; }
        .bw-lab li { position: relative; padding-inline-start: 1.5rem; color: #463b30; font-size: 1rem; }
        /* A cork, end on, for the winery's bullets. */
        .bw-lab li::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0.42em;
            width: 0.8rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background-color: #c49a63;
            background-image: radial-gradient(circle at 35% 30%, rgba(255, 255, 255, 0.45), rgba(255, 255, 255, 0) 50%), radial-gradient(circle at 70% 70%, rgba(94, 60, 26, 0.5) 0 14%, rgba(94, 60, 26, 0) 16%);
            box-shadow: inset 0 0 0 1px rgba(94, 60, 26, 0.45);
        }
        .bw-lab-paid { background-color: #5e1a28; color: #f7efdf; }
        .bw-lab-paid::before { border-color: #c9a85a; outline-color: #c9a85a; }
        .bw-lab-paid .bw-lab-crest { background-color: #f0dca6; color: #5e1a28; box-shadow: 0 0 0 3px #5e1a28, 0 0 0 4px #c9a85a; }
        .bw-lab-paid h3 { color: #f0dca6; }
        #bw .bw-lab-paid .bw-tag { color: #f0dca6; }
        .bw-lab-paid .bw-lab-lede { color: #f7efdf; }
        .bw-lab-paid ul { border-top-color: rgba(201, 168, 90, 0.7); }
        .bw-lab-paid li { color: #f3e6d2; }
        .bw-ticket-foot { max-width: 46rem; margin: clamp(2.25rem, 4vw, 3.25rem) auto 0; text-align: center; font-family: var(--bw-serif); font-size: clamp(1.1rem, 1.7vw, 1.3rem); line-height: 1.5; color: var(--bw-ink-2); text-wrap: pretty; }

        /* ---------------------------------------------------------------
           04. Other people: keg collars on the board
           --------------------------------------------------------------- */
        .bw-ask { background-color: var(--bw-card); box-shadow: 0 1.5rem 2.2rem -1.4rem rgba(29, 23, 18, 0.5); rotate: -1deg; }
        .dark .bw-ask { box-shadow: 0 0 0 1px rgba(241, 230, 210, 0.12), 0 1.5rem 2.2rem -1.4rem #000; }
        .bw-ask-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 1rem 1.25rem 0.9rem; background-color: #3d6b2f; color: #f7efdf; }
        .bw-ask-head h3 { font-family: var(--bw-slab); font-weight: 400; font-size: 1.3rem; line-height: 1.1; }
        .bw-ask-head span { font-family: var(--bw-mono); font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase; }
        .bw-ask-list { display: grid; gap: 0.7rem; padding: 1.1rem 1.1rem 0.4rem; }
        .bw-collar {
            display: grid;
            grid-template-columns: 3.3rem minmax(0, 1fr);
            align-items: center;
            gap: 0.9rem;
            padding: 0.7rem 1rem 0.7rem 0.6rem;
            background-color: var(--bw-kraft);
            border-radius: 2.2rem 0.5rem 0.5rem 2.2rem;
            -webkit-mask: radial-gradient(circle at 2.25rem 50%, transparent 0 0.95rem, #000 1rem);
            mask: radial-gradient(circle at 2.25rem 50%, transparent 0 0.95rem, #000 1rem);
        }
        .bw-collar-body { grid-column: 2; display: grid; gap: 0.15rem 1rem; align-items: center; min-width: 0; }
        @media (min-width: 520px) { .bw-collar-body { grid-template-columns: 7.4rem minmax(0, 1fr); } }
        .bw-collar-when { font-family: var(--bw-slab); font-size: 0.95rem; line-height: 1.1; color: var(--bw-copper-ink); text-transform: uppercase; white-space: nowrap; }
        .bw-collar-who { font-weight: 700; color: var(--bw-ink); line-height: 1.25; }
        .bw-collar-what { font-size: 0.9rem; color: var(--bw-ink-2); }
        .bw-ask-foot { padding: 0.7rem 1.25rem 1.2rem; font-size: 0.95rem; color: var(--bw-ink-2); }
        .bw-points { display: grid; gap: 1.25rem; }
        .bw-point { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.2rem 1rem; align-items: start; }
        .bw-point .bw-cap { grid-row: span 2; margin-top: 0.15rem; }
        .bw-point-t { font-family: var(--bw-slab); font-size: 1.2rem; line-height: 1.2; color: var(--bw-ink); }
        .bw-point-d { color: var(--bw-ink-2); }

        /* ---------------------------------------------------------------
           05. Who it is for: six labels off the same line
           --------------------------------------------------------------- */
        .bw-whos { display: grid; gap: 1.75rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 680px) { .bw-whos { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .bw-whos { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2rem; } }
        .bw-who-wrap { filter: drop-shadow(0 0.9rem 0.8rem rgba(29, 23, 18, 0.24)); transition: translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1); }
        .bw-who-wrap:hover { translate: 0 -0.4rem; rotate: -0.6deg; }
        .bw-who { position: relative; display: flex; flex-direction: column; height: 100%; background-color: #fbf6ea; color: #1d1712; }
        .bw-who-face { container-type: inline-size; position: relative; display: flex; flex-direction: column; justify-content: space-between; gap: 1.4rem; min-height: 9.2rem; padding: 1.2rem 1.4rem 1.25rem; background-color: var(--a); color: var(--t); }
        .bw-who-top { font-weight: 700; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; }
        .bw-who h3 { font-size: clamp(1.65rem, 11cqi, 2.5rem); line-height: 1.04; text-wrap: balance; }
        .bw-who > p { padding: 1.1rem 1.4rem 1.4rem; color: #463b30; font-size: 1rem; }
        .bw-who > a { margin-top: auto; padding: 0 1.4rem 1.4rem; align-self: flex-start; }
        .bw-who > a span { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; color: #1d1712; border-bottom: 2px solid #84421a; transition: gap 0.2s ease; }
        .bw-who > a:hover span { gap: 0.7rem; }
        .bw-who > a svg { width: 1rem; height: 1rem; }
        /* The can's dress: slab, a pinstripe, and the fall of light across a cylinder. */
        .bw-who-can { border-radius: 0.7rem; overflow: hidden; }
        .bw-who-can .bw-who-face { background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.26), rgba(255, 255, 255, 0.14) 22%, rgba(0, 0, 0, 0) 55%, rgba(0, 0, 0, 0.22)), repeating-linear-gradient(104deg, rgba(0, 0, 0, 0) 0 1.6rem, rgba(0, 0, 0, 0.12) 1.6rem 1.8rem); }
        .bw-who-can h3 { font-family: var(--bw-slab); font-weight: 400; }
        /* The bottle's dress: serif, a deckled sheet, a double rule of gold. */
        .bw-who-btl { clip-path: var(--bw-deckle); }
        .bw-who-btl .bw-who-face::after { content: ""; position: absolute; inset: 0.55rem; border: 1px solid var(--g, #a8843a); outline: 1px solid var(--g, #a8843a); outline-offset: 2px; pointer-events: none; }
        .bw-who-btl .bw-who-face { align-items: center; text-align: center; padding-inline: 1.8rem; }
        .bw-who-btl h3 { font-family: var(--bw-serif); font-weight: 700; letter-spacing: -0.005em; }
        .bw-who-btl > p { padding-inline: 1.8rem; text-align: center; }
        .bw-who-btl > a { align-self: center; }

        /* ---------------------------------------------------------------
           06. How it works: three caps on the cellar floor
           --------------------------------------------------------------- */
        .bw-cellar {
            background-color: #17120e;
            background-image: radial-gradient(60rem 28rem at 50% 0%, rgba(180, 99, 42, 0.2), rgba(180, 99, 42, 0)), radial-gradient(40rem 22rem at 85% 100%, rgba(94, 26, 40, 0.4), rgba(94, 26, 40, 0));
            color: #f1e6d2;
        }
        #bw .bw-cellar .bw-kick { color: #d3c6b0; }
        #bw .bw-cellar .bw-h2 { color: #f1e6d2; }
        #bw .bw-cellar .bw-h2-both span { color: #a9d196; }
        .bw-steps { display: grid; gap: 2.75rem 2rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 860px) { .bw-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .bw-step { display: grid; justify-items: center; text-align: center; }
        .bw-step .bw-cap { --sz: clamp(6rem, 11vw, 8rem); font-size: calc(var(--sz) * 0.36); transition: rotate 0.9s cubic-bezier(0.34, 1.4, 0.64, 1), scale 0.9s cubic-bezier(0.34, 1.4, 0.64, 1); transition-delay: var(--reveal-delay, 0s); }
        html.es-anim #bw .bw-step[data-reveal]:not(.is-revealed) .bw-cap { rotate: -140deg; scale: 0.6; }
        .bw-step h3 { margin-top: 1.5rem; font-family: var(--bw-serif); font-weight: 700; font-size: 1.6rem; line-height: 1.15; text-wrap: balance; }
        .bw-step p { margin-top: 0.7rem; max-width: 21rem; color: #d3c6b0; }

        /* ---------------------------------------------------------------
           Key features: a tasting flight
           --------------------------------------------------------------- */
        .bw-flight { position: relative; display: grid; gap: 0.25rem; grid-template-columns: minmax(0, 1fr); margin-top: clamp(2.5rem, 5vw, 3.75rem); }
        .bw-pour { position: relative; display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 1.1rem; padding: 0.9rem 0.5rem; border-bottom: 1px solid var(--bw-line); }
        .bw-glass {
            position: relative;
            display: block;
            font-size: 0.62rem;
            width: 4.5em;
            height: 6.7em;
            clip-path: polygon(0 0, 100% 0, 85% 100%, 15% 100%);
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.62), rgba(255, 255, 255, 0.2) 26%, rgba(255, 255, 255, 0.1) 62%, rgba(255, 255, 255, 0.5));
        }
        .dark .bw-glass { background: linear-gradient(90deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.08) 26%, rgba(255, 255, 255, 0.04) 62%, rgba(255, 255, 255, 0.24)); }
        .bw-glass i {
            position: absolute;
            inset: 19% 0 0 0;
            background-color: var(--liq);
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0.42) 0 9%, rgba(255, 255, 255, 0) 30%, rgba(0, 0, 0, 0) 60%, rgba(0, 0, 0, 0.26));
            transform-origin: 50% 100%;
            transition: scale 1.5s cubic-bezier(0.3, 0.9, 0.3, 1);
            transition-delay: calc(var(--i) * 0.16s + 0.2s);
        }
        .bw-glass i::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: -15%;
            height: 16%;
            border-radius: 45% 45% 0 0 / 70% 70% 0 0;
            background: var(--foam, #fff6df);
        }
        .bw-glass::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 6%; background: rgba(255, 255, 255, 0.5); }
        html.es-anim #bw .bw-flight[data-reveal]:not(.is-revealed) .bw-glass i { scale: 1 0.03; }
        .bw-pour-text { min-width: 0; }
        .bw-pour-name { display: block; font-family: var(--bw-slab); font-size: 1.25rem; line-height: 1.15; color: var(--bw-ink); }
        .bw-pour:nth-child(even) .bw-pour-name { font-family: var(--bw-serif); font-weight: 700; font-size: 1.4rem; }
        .bw-pour-desc { display: block; margin-top: 0.25rem; font-size: 0.95rem; color: var(--bw-ink-2); }
        .bw-pour > svg { width: 1.25rem; height: 1.25rem; color: var(--bw-copper-ink); transition: translate 0.2s ease; }
        .bw-pour:hover > svg { translate: 0.3rem 0; }
        .bw-paddle { display: none; }
        @media (min-width: 900px) {
            .bw-flight { grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 1.25rem; padding-inline: 1.5rem; }
            .bw-pour { grid-template-columns: minmax(0, 1fr); justify-items: center; align-content: start; gap: 0; padding: 0 0.25rem; border-bottom: 0; text-align: center; }
            .bw-glass { font-size: 1.4rem; transition: translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1); }
            .bw-pour:hover .bw-glass { translate: 0 -0.6rem; }
            .bw-pour-text { margin-top: 3.6rem; }
            .bw-pour > svg { margin-top: 0.8rem; }
            .bw-pour:hover > svg { translate: 0.3rem 0; }
            /* The paddle the glasses stand in: a plank, five wells and a handle. */
            .bw-paddle {
                display: block;
                position: absolute;
                left: 0;
                right: -2.5rem;
                top: calc(1.4rem * 6.7 - 1.05rem);
                height: 2.3rem;
                border-radius: 0.5rem 1.6rem 1.6rem 0.5rem;
                background: repeating-linear-gradient(90deg, rgba(0, 0, 0, 0) 0 7rem, rgba(0, 0, 0, 0.12) 7rem 7.06rem), linear-gradient(#7a4c2c, #5a3620 55%, #3f2515);
                box-shadow: 0 0.9rem 1rem -0.5rem rgba(29, 23, 18, 0.55);
            }
            .bw-paddle::after { content: ""; position: absolute; right: 0.9rem; top: 50%; width: 0.75rem; aspect-ratio: 1; translate: 0 -50%; border-radius: 50%; background: var(--bw-kraft); box-shadow: inset 0 0.12rem 0.2rem rgba(0, 0, 0, 0.55); }
            .bw-pour .bw-glass { z-index: 1; }
        }
        @supports (animation-timeline: view()) {
            html.es-anim #bw .bw-glass i {
                animation: bw-pour linear both;
                animation-timeline: view();
                animation-range: entry calc(30% + var(--i) * 9%) entry calc(110% + var(--i) * 9%);
            }
        }
        @keyframes bw-pour { from { scale: 1 0.03; } to { scale: 1 1; } }
        .bw-flight-more { margin-top: 2.75rem; text-align: center; }

        /* ---------------------------------------------------------------
           The price list and the closing strip are shared partials. They
           keep their words and their prices; only the print changes.
           --------------------------------------------------------------- */
        #bw .bw-plans > section { background-color: var(--bw-paper); }
        #bw .bw-plans h2 { font-family: var(--bw-serif); font-weight: 700; letter-spacing: -0.01em; font-size: clamp(2rem, 4.2vw, 3.2rem); line-height: 1.1; color: var(--bw-ink); }
        #bw .bw-plans h2 + p { color: var(--bw-ink-2); font-size: 1.0625rem; }
        #bw .bw-plans .grid > div { background-color: var(--bw-card); border: 1px solid var(--bw-rule); border-radius: 0.3rem; outline: 1px solid var(--bw-rule); outline-offset: -6px; box-shadow: 0 1rem 1.6rem -1.1rem rgba(29, 23, 18, 0.45); color: var(--bw-ink); }
        #bw .bw-plans .grid > div span,
        #bw .bw-plans .grid > div p,
        #bw .bw-plans .grid > div li { color: var(--bw-ink-2); }
        #bw .bw-plans .grid > div .text-3xl { font-family: var(--bw-slab); font-weight: 400; font-size: 2.6rem; color: var(--bw-ink); }
        #bw .bw-plans .grid > div .uppercase { color: var(--bw-ink); letter-spacing: 0.2em; }
        #bw .bw-plans .grid > div svg { color: var(--bw-hop-ink); }
        #bw .bw-plans .grid > div:nth-child(2) { background-color: #35602a; border-color: #223f1a; outline-color: rgba(247, 239, 223, 0.45); }
        #bw .bw-plans .grid > div:nth-child(2) span,
        #bw .bw-plans .grid > div:nth-child(2) p,
        #bw .bw-plans .grid > div:nth-child(2) li { color: #e9f0de; }
        #bw .bw-plans .grid > div:nth-child(2) .text-3xl,
        #bw .bw-plans .grid > div:nth-child(2) .uppercase { color: #f7efdf; }
        #bw .bw-plans .grid > div:nth-child(2) svg { color: #f6c98a; }
        #bw .bw-plans .grid > div:nth-child(2) .rounded-full { background-color: #f7efdf; color: #1d1712; }
        #bw .bw-plans a.font-medium { color: var(--bw-ink); border-bottom: 2px solid var(--bw-copper-ink); }
        #bw .bw-plans a.rounded-2xl { background: #3d6b2f; color: #f7efdf; border-radius: 0.55rem; font-family: var(--bw-slab); font-weight: 400; box-shadow: inset 0 0 0 2px rgba(247, 239, 223, 0.22), 0 0.32rem 0 #223f1a; }
        #bw .bw-plans a.rounded-2xl:hover { transform: translateY(0.16rem); box-shadow: inset 0 0 0 2px rgba(247, 239, 223, 0.22), 0 0.16rem 0 #223f1a; }

        #bw .bw-keep > section { background-color: var(--bw-kraft-2); border-top: 1px solid var(--bw-line); }
        #bw .bw-keep h2 { font-family: var(--bw-serif); font-weight: 700; color: var(--bw-ink); }
        #bw .bw-keep p.uppercase { color: var(--bw-copper-ink); letter-spacing: 0.18em; }
        #bw .bw-keep .grid > a { background-color: var(--bw-card); border: 1px solid var(--bw-line); border-radius: 0.5rem; }
        #bw .bw-keep .grid > a:hover { border-color: var(--bw-copper-ink); }
        #bw .bw-keep .grid > a > span:first-child { display: none; }
        #bw .bw-keep .grid > a h3 { color: var(--bw-ink); font-family: var(--bw-slab); font-weight: 400; }
        #bw .bw-keep .grid > a p { color: var(--bw-ink-2); }
        #bw .bw-keep .grid > a > span:last-child,
        #bw .bw-keep a.self-start { color: var(--bw-copper-ink); }

        /* ---------------------------------------------------------------
           Related pages: four neck tags on their strings
           --------------------------------------------------------------- */
        .bw-necks { display: grid; gap: 2.5rem 1.5rem; grid-template-columns: repeat(2, minmax(0, 1fr)); padding-top: 1.75rem; }
        @media (min-width: 900px) { .bw-necks { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 2rem; } }
        .bw-neck { position: relative; display: block; transform-origin: 50% -1.6rem; transition: rotate 0.5s cubic-bezier(0.34, 1.5, 0.64, 1); filter: drop-shadow(0 0.7rem 0.6rem rgba(29, 23, 18, 0.26)); }
        .bw-neck:nth-child(odd) { rotate: -1.6deg; }
        .bw-neck:nth-child(even) { rotate: 1.4deg; }
        .bw-neck:hover { rotate: 0deg; }
        .bw-neck::before { content: ""; position: absolute; left: 50%; bottom: calc(100% - 1.5rem); width: 2px; height: 3.2rem; translate: -50% 0; background: #8a6a3f; z-index: 1; }
        .bw-neck-card {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 11.5rem;
            padding: 3.4rem 1.25rem 1.35rem;
            background-color: #fbf6ea;
            color: #1d1712;
            clip-path: polygon(24% 0, 76% 0, 100% 17%, 100% 100%, 0 100%, 0 17%);
            text-align: center;
        }
        .bw-neck-card::before { content: ""; position: absolute; left: 50%; top: 1rem; width: 0.95rem; aspect-ratio: 1; translate: -50% 0; border-radius: 50%; background-color: var(--bw-kraft-2); box-shadow: 0 0 0 3px #a8843a; }
        .bw-neck small { font-weight: 700; font-size: 0.68rem; letter-spacing: 0.18em; text-transform: uppercase; color: #5a4d3f; }
        .bw-neck strong { display: block; margin-top: 0.4rem; font-family: var(--bw-slab); font-weight: 400; font-size: clamp(1.25rem, 2.3vw, 1.7rem); line-height: 1.08; color: #2f5524; }
        .bw-neck:nth-child(even) strong { font-family: var(--bw-serif); font-weight: 700; color: #5e1a28; font-size: clamp(1.35rem, 2.5vw, 1.85rem); }
        .bw-necks-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1.25rem; margin-bottom: 2.5rem; }

        /* ---------------------------------------------------------------
           07. Questions, asked across the bar
           --------------------------------------------------------------- */
        .bw-faq-grid { display: grid; gap: 2.5rem 4rem; grid-template-columns: minmax(0, 1fr); align-items: start; }
        @media (min-width: 1000px) {
            .bw-faq-grid { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
            .bw-faq-head { position: sticky; top: 6.5rem; }
        }
        .bw-faq { border-top: 2px solid var(--bw-ink); }
        .bw-faq details { border-bottom: 1px solid var(--bw-line); }
        .bw-faq summary { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 1rem; padding: 1.15rem 0.2rem; cursor: pointer; }
        .bw-faq summary .bw-cap { --sz: 2.1rem; font-size: 1rem; transition: rotate 0.5s cubic-bezier(0.34, 1.5, 0.64, 1), background-color 0.3s ease; }
        .bw-faq details[open] summary .bw-cap { rotate: 360deg; --c: #96501f; }
        .bw-faq h3 { font-family: var(--bw-serif); font-weight: 700; font-size: 1.3rem; line-height: 1.25; color: var(--bw-ink); }
        .bw-faq details p { padding: 0 0.2rem 1.5rem 3.1rem; max-width: 46rem; color: var(--bw-ink-2); }
        @media (max-width: 560px) { .bw-faq details p { padding-inline-start: 0.2rem; } }

        /* ---------------------------------------------------------------
           The finale: the label, waiting for a name
           --------------------------------------------------------------- */
        .bw-fin { position: relative; overflow: clip; padding-block: clamp(4.5rem, 9vw, 7.5rem); background-color: #5e1a28; }
        .bw-fin-hop {
            position: absolute;
            inset: 0;
            background-color: #3d6b2f;
            background-image: repeating-linear-gradient(104deg, rgba(34, 63, 26, 0) 0 3rem, rgba(34, 63, 26, 0.55) 3rem 3.35rem);
            clip-path: polygon(0 0, 62% 0, 38% 100%, 0 100%);
        }
        .bw-fin-vin { position: absolute; inset: 0; background: radial-gradient(circle at 96% 110%, rgba(201, 168, 90, 0) 0 18rem, rgba(201, 168, 90, 0.8) 18.05rem 18.2rem, rgba(201, 168, 90, 0) 18.25rem 18.6rem, rgba(201, 168, 90, 0.8) 18.65rem 18.8rem, rgba(201, 168, 90, 0) 18.85rem); }
        .bw-fin-wrap { position: relative; width: min(100% - 2.5rem, 40rem); margin-inline: auto; filter: drop-shadow(0 1.6rem 1.6rem rgba(0, 0, 0, 0.42)); }
        .bw-fin-label { position: relative; padding: clamp(2.6rem, 6vw, 3.6rem) clamp(1.5rem, 5vw, 3.2rem); background-color: #fbf6ea; color: #1d1712; clip-path: var(--bw-deckle); text-align: center; }
        .bw-fin-label::before { content: ""; position: absolute; inset: 0.8rem; border: 1px solid #a8843a; outline: 1px solid #a8843a; outline-offset: 3px; pointer-events: none; }
        #bw .bw-fin .bw-kick { color: #463b30; }
        #bw .bw-fin .bw-h2 { margin-top: 1rem; color: #1d1712; font-size: clamp(2rem, 4.6vw, 3.2rem); }
        #bw .bw-fin .bw-h2-both span { color: #2f5524; }
        .bw-fin-label > p { position: relative; margin: 1.1rem auto 0; max-width: 30rem; color: #463b30; }
        .bw-fin-form { position: relative; display: grid; gap: 0.95rem; margin-top: 1.75rem; }
        #bw .bw-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1rem;
            border: 1.5px solid #1d1712;
            border-radius: 0.4rem;
            background-color: #fff;
            font-family: var(--bw-mono);
            font-weight: 700;
            font-size: clamp(0.9rem, 3.2vw, 1.1rem);
            transition: box-shadow 0.2s ease;
        }
        #bw .bw-claim:focus-within { border-color: #1d1712; box-shadow: 0 0 0 4px rgba(180, 99, 42, 0.4); }
        #bw .bw-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #1d1712; box-shadow: none; outline: none; }
        #bw .bw-claim input::placeholder { color: #7d705f; }
        .bw-claim span { flex: none; color: #5a4d3f; user-select: none; }
        /* On a phone the box you type in stays at 16px, under which iOS zooms the page on
           focus. The suffix gives up the room instead, so "your-brewery" is whole (Safari cut it
           short even at the old size), and the two sizes share a baseline. */
        @media (max-width: 500px) {
            #bw .bw-claim { align-items: baseline; padding-inline: 0.65rem; font-size: max(0.75rem, 3.2vw); }
            #bw .bw-claim input { align-self: baseline; font-size: 1rem; }
        }
        .bw-fin-note { font-size: 0.95rem; }
        #bw .bw-fin-label .bw-fin-note { margin-top: 1rem; color: #5a4d3f; }

        @media (prefers-reduced-motion: reduce) {
            .bw-stage,
            .bw-btn, .bw-btn svg, .bw-handle, .bw-back-wrap, .bw-who-wrap, .bw-neck, .bw-glass, .bw-glass i,
            .bw-step .bw-cap, .bw-faq summary .bw-cap, .bw-more, .bw-pour > svg { transition: none; }
            .bw-glass i, .bw-handle { animation: none; }
        }
    </style>

    @php
        // The week board. `price` null means free; the counts in the copy are
        // derived from this array so the argument and the table cannot drift.
        $week = [
            ['Wednesday', 'Quiz night',        null, 'Runs itself, fills the room'],
            ['Thursday',  'Food truck visits', null, 'Their van, your taps'],
            ['Friday',    'Live music',        null, 'A duo in the corner'],
            ['Saturday',  'Brewery tour',      15,   'Twelve places, booked ahead'],
            ['Sunday',    'Yoga in the yard',  null, 'Ends at the bar'],
        ];
        // Spelled out so the sentence under the board reads as prose, and
        // derived from the board so the count and the table cannot drift.
        $words = [1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'four', 5 => 'five'];
        $paidCount = count(array_filter($week, fn ($r) => $r[2] !== null));
        $freeCount = count($week) - $paidCount;
        $faqs = [
            [
                'q' => 'Is Event Schedule free for a taproom?',
                'a' => 'Almost all of what a taproom runs is free forever: the weekly nights as recurring events, date exceptions for the weeks you are shut, free registration with a capacity for a tour that is limited but not paid, sub-schedules with their own shareable links, booking requests from bands and food trucks, two-way calendar sync and an embeddable calendar. Free registration has no monthly ceiling on it, however many names come through. Putting a price on a tour or tasting is the one part that needs Pro, at '.plan_price($proMonthly).' a month, which a taproom charging for its tours pays and a taproom running a free week never does. Zero platform fees on sales either way.',
            ],
            [
                'q' => 'Most of our events are free. Is that a problem?',
                'a' => 'It is the normal case here, and nothing about it is second class. A free event still takes registrations up to a capacity, still appears on your public calendar, and still lands in the live calendar feed your regulars subscribe to on their phones. The events are how you fill the room on a Tuesday, not a revenue line in their own right.',
            ],
            [
                'q' => 'How do I run a tour that is free but limited?',
                'a' => 'Set the event as free and give it a capacity. People register rather than pay, the count is kept for each date separately, and it stops taking names when the places are gone. If you would rather charge, the same event becomes a ticket with a price, a quantity and QR check-in at the door.',
            ],
            [
                'q' => 'Can bands and food trucks book themselves in?',
                'a' => 'Turn on booking requests and they can ask for a date through your page rather than a direct message you lose. Every request waits for you to accept it, and you are emailed when new ones are pending. Once you accept, adding them to the event as a participant offers the date to their own schedule, where it appears when they accept it (or straight away if they have approved you), so you are not both keeping the same listing up to date.',
            ],
            [
                'q' => 'Can we email people about a release?',
                'a' => 'Two ways. Somebody who leaves an email address on your page and confirms it gets a short digest automatically when you put new dates up, at most one every few days, and it costs nothing from the allowance. A release you want to write properly about is a newsletter you send yourself, and that is what the allowance counts: 10 emails a month free, 100 on Pro, counted per recipient rather than per send, so a single newsletter to a hundred followers uses a hundred of them.',
            ],
            [
                'q' => 'Can people ask to hear when tour tickets go on sale?',
                'a' => 'Yes, and it costs them only an email address. Switch on the "Notify me" card, put the tour or a release tasting up before it sells, and its page offers "Tell me when tickets go on sale". They hear once when you open sales, once if it is cancelled and again shortly before it starts, plus any change notice you choose to send. It is free on every plan, it is not a subscription to your schedule, and it does not touch the newsletter allowance. The Tickets panel in the event editor shows how many are waiting, which is a fair guide to whether a second tour would fill.',
            ],
            [
                'q' => 'How do people pay for a tour, and can I refund one?',
                'a' => 'Through your own Stripe or PayPal account, an Invoice Ninja invoice, a payment link or cash on the day, or Payfast if you sell in rand, chosen per event. Event Schedule takes no platform fee on any of them. If a tour does not run, refund it from the Sales page: a Stripe or PayPal sale goes back through the provider, in full or in part, and a partial refund leaves the ticket valid. Every other method is marked as refunded instead, which records it without moving money, so you return that one yourself.',
            ],
        ];
        $dotSections = [
            ['top', 'The week'],
            ['why', 'Why it is free'],
            ['rhythm', 'The rhythm'],
            ['ticket', 'When you sell one'],
            ['guests', 'Other people'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];

        // The deckle: one rough edge, cut once and reused on every bottle label. A fixed run of
        // offsets rather than a random one, so the sheet tears the same way on every load.
        $bwJag = [0.0, 0.9, 0.25, 1.3, 0.55, 0.1, 1.1, 0.4, 1.4, 0.3, 0.85, 0.05, 1.2, 0.6, 0.2, 1.0, 0.45, 1.3, 0.15, 0.7, 0.0];
        $bwLast = count($bwJag) - 1;
        $bwEdge = [];
        foreach ($bwJag as $bwI => $bwJ) {
            $bwEdge[] = $bwJ.'% '.round($bwI * 100 / $bwLast, 2).'%';
        }
        for ($bwI = $bwLast; $bwI >= 0; $bwI--) {
            $bwEdge[] = (100 - $bwJag[($bwI * 7 + 3) % ($bwLast + 1)]).'% '.round($bwI * 100 / $bwLast, 2).'%';
        }
        $bwDeckle = 'polygon('.implode(', ', $bwEdge).')';

        $bwArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $bwDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="bw" style="--bw-deckle: {{ $bwDeckle }};">

        <nav class="bw-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot"><i aria-hidden="true"></i><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: one headline in two dresses, and the week board     -->
        <!-- ============================================================ -->
        <section id="top" class="bw-hero">
            <div class="bw-stage" id="bw-stage">
                {{-- The bottle's dress lies underneath, whole. It is the same headline again, so it is
                     kept from assistive tech; the can's dress above it carries the real h1. --}}
                <div class="bw-side bw-side-vin" aria-hidden="true">
                    <div class="bw-art bw-art-vin"></div>
                    <div class="bw-head bw-head-vin">
                        <span class="bw-eyewrap"><span class="bw-eye bw-row"><span>Brewery and winery event calendar,</span> <span>for tasting rooms too</span></span></span>
                        <span class="bw-row bw-row-1 es-fade-up es-d-1"><span>Nobody buys</span> <span>a ticket</span></span>
                        <span class="bw-row bw-row-2 es-fade-up es-d-2"><span>to trivia</span> <span><em>night.</em></span></span>
                    </div>
                </div>
                <div class="bw-side bw-side-hop">
                    <div class="bw-art bw-art-hop" aria-hidden="true"></div>
                    <h1 class="bw-head bw-head-hop">
                        <x-marketing.hero-eyebrow spacing="bw-eyewrap" class="bw-eye bw-row"><span>Brewery and winery event calendar,</span> <span>for tasting rooms too</span></x-marketing.hero-eyebrow>
                        <span class="bw-row bw-row-1 es-fade-up es-d-1"><span>Nobody buys</span> <span>a ticket</span></span>
                        <span class="bw-row bw-row-2 es-fade-up es-d-2"><span>to <em>trivia</em></span> <span>night.</span></span>
                    </h1>
                </div>
                <div class="bw-seam" aria-hidden="true"></div>
            </div>

            <div class="bw-wrap bw-hero-foot">
                <div class="bw-shelf">
                    <div class="bw-can es-fade-up es-d-3" aria-hidden="true">
                        <div class="bw-can-lid"></div>
                        <div class="bw-can-body">
                            <div class="bw-can-label">
                                <span class="bw-can-top">Taproom</span>
                                <span class="bw-can-big">Free</span>
                                <span class="bw-can-sub">{{ $freeCount }} nights in {{ count($week) }}</span>
                                <span class="bw-can-foot">Batch 001 &middot; Set once</span>
                            </div>
                        </div>
                    </div>

                    <div class="bw-shelf-copy">
                        <p class="es-fade-up es-d-3">
                            Your events are not the product. They are the reason somebody comes in on a
                            Tuesday and buys three pints. So most of the calendar is free, and most of
                            what you need to run it is free too.
                        </p>
                        <div class="bw-cta es-fade-up es-d-4">
                            <a href="{{ app_url('/sign_up?type=venue') }}" class="bw-btn">
                                Put the week up
                                {!! $bwArrow !!}
                            </a>
                            <a href="#why" class="bw-btn bw-btn-vin">
                                See what costs nothing
                                {!! $bwDown !!}
                            </a>
                        </div>
                    </div>

                    <div class="bw-bottle es-fade-up es-d-3" aria-hidden="true">
                        <div class="bw-bottle-neck"></div>
                        <div class="bw-bottle-body">
                            <div class="bw-bottle-label">
                                <span class="bw-crest">ES</span>
                                <span class="bw-bl-script">Cellar Door</span>
                                <span class="bw-bl-name">Tasting</span>
                                <span class="bw-bl-rule"></span>
                                <span class="bw-bl-year">2026</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- The week board. The price column is the argument. -->
                <div class="bw-board es-fade-up es-d-5">
                    <div class="bw-board-rail" aria-hidden="true"></div>
                    <p class="bw-board-title">This week at the taproom</p>
                    <div class="bw-taps">
                        @foreach ($week as $i => [$wDay, $wName, $wPrice, $wNote])
                            <div @class(['bw-tap', 'bw-tap-paid' => $wPrice !== null])>
                                <span class="bw-handle" aria-hidden="true" style="--i: {{ $i }};"><i>{{ substr($wDay, 0, 3) }}</i></span>
                                <span class="bw-tap-day">{{ $wDay }}</span>
                                <div class="bw-tap-what">
                                    <p class="bw-tap-name">{{ $wName }}</p>
                                    <p class="bw-tap-note">{{ $wNote }}</p>
                                </div>
                                @if ($wPrice === null)
                                    <span class="bw-cap bw-tap-cost">Free</span>
                                @else
                                    <span class="bw-cap bw-cap-gold bw-tap-cost">${{ $wPrice }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <p class="bw-board-line es-fade-up es-d-5">
                    {{ $words[$paidCount] ?? $paidCount }} of these sells a ticket. The other {{ $words[$freeCount] ?? $freeCount }} sell beer.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. Why it is free (01)                                       -->
        <!-- ============================================================ -->
        <section id="why" class="bw-sec bw-sec-kraft2" style="scroll-margin-top: 4.5rem;">
            <div class="bw-wrap">
                <div class="bw-head-c">
                    <p class="bw-kick" data-reveal><span class="bw-cap bw-cap-sm" aria-hidden="true">01</span>Why it is free</p>
                    <h2 class="bw-h2 bw-h2-both" data-reveal style="--reveal-delay: 0.08s;">
                        The calendar is the <span>reason to come in</span>.
                    </h2>
                    <p class="bw-lede" data-reveal style="--reveal-delay: 0.16s;">
                        A restaurant sells the dinner. A comedy club sells the ticket. You sell what
                        people drink once they are here, which means the calendar has a different job -
                        and the parts that do that job cost nothing.
                    </p>
                </div>

                <div class="bw-backs" data-reveal-group="90">
                    @foreach ([
                        ['The weekly nights', 'Every repeating night is one recurring event, with the weeks you are shut taken out. Set it up once in January.'],
                        ['A free tour with a limit', 'Registration with a capacity, counted for each date on its own. Limited does not have to mean paid.'],
                        ['Its own link per strand', 'Sub-schedules keep music, tours and private hire apart, and each can be shared as a filtered link.'],
                        ['On the site you have', 'Embed the calendar in your own page, and sync both ways with Google, Outlook or CalDAV.'],
                    ] as $bwIdx => [$t, $d])
                        <div class="bw-back-wrap" data-reveal style="--r: {{ [-1.4, 1.1, -0.8, 1.5][$bwIdx] }}deg;">
                            <article @class(['bw-back', 'bw-back-can' => $bwIdx % 2 === 0, 'bw-back-btl' => $bwIdx % 2 === 1])>
                                <h3>{{ $t }}</h3>
                                <p>{{ $d }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>

                <p class="bw-note bw-why-note" data-reveal>
                    <span class="bw-tag">Free plan</span>
                    <span>
                        All four, on the free plan, with no cap on how many events you put up.
                    </span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The rhythm (02)                                           -->
        <!-- ============================================================ -->
        <section id="rhythm" class="bw-sec bw-band-hop bw-on-dark" style="scroll-margin-top: 4.5rem;">
            <div class="bw-wrap bw-duo">
                <div class="bw-duo-copy">
                    <p class="bw-kick" data-reveal><span class="bw-cap bw-cap-sm bw-cap-gold" aria-hidden="true">02</span>The rhythm</p>
                    <h2 class="bw-h2 bw-h2-hop" data-reveal style="--reveal-delay: 0.08s;">
                        Set the week once, <span>not every week</span>.
                    </h2>
                    <p class="bw-lede" data-reveal style="--reveal-delay: 0.16s;">
                        The quiz is always Wednesday. Music is always Friday. Those are three or four
                        recurring events, not two hundred posts a year, and the only weeks you touch
                        are the ones you are closed.
                    </p>
                    <ul class="bw-caps" data-reveal style="--reveal-delay: 0.2s;">
                        @foreach ([
                            ['One night, one event', 'Choose the day and the time. Move the quiz to eight and every future Wednesday moves with it.'],
                            ['Take the shut weeks out', 'A date exception drops the week you are closed for the holidays without disturbing the pattern.'],
                            ['Strands, not one long list', 'Music on one sub-schedule, tours on another. Each one has a link you can send on its own.'],
                            ['On the regulars\' phones', 'Every night\'s Add to Calendar menu offers the whole taproom calendar as a live feed. Subscribe once, and new nights and moved ones turn up without anyone checking the page.'],
                        ] as [$t, $d])
                            <li>
                                <span><b>{{ $t }}</b> <span>- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="bw-note" data-reveal>
                        <span class="bw-tag">Free plan</span>
                        <span>Recurring events, date exceptions, sub-schedules and the calendar feed are all on the free plan.</span>
                    </p>
                </div>

                <div class="bw-proof" data-reveal="right">
                    <div class="bw-proof-label">
                        <div class="bw-proof-art" aria-hidden="true"><b>Set<br>once</b><span>Taproom week</span></div>
                        <div class="bw-proof-panel">
                            <div class="bw-proof-head">
                                <h3>What you actually set up</h3>
                                <span>4 events, once</span>
                            </div>
                            @foreach ([
                                ['Quiz night', 'Every Wednesday, 8:00pm'],
                                ['Live music', 'Every Friday, 8:30pm'],
                                ['Brewery tour', 'Every Saturday, 2:00pm'],
                                ['Yoga in the yard', 'Every Sunday, 10:00am'],
                            ] as [$rName, $rWhen])
                                <div class="bw-proof-row">
                                    <b>{{ $rName }}</b>
                                    <i aria-hidden="true"></i>
                                    <span>{{ $rWhen }}</span>
                                </div>
                            @endforeach
                            <p class="bw-proof-sub">Date exceptions</p>
                            <p>
                                Closed the 25th and the 1st. Both taken out; every other week runs as normal.
                            </p>
                        </div>
                    </div>
                    <div class="bw-proof-bar" aria-hidden="true">
                        <i style="--c: #3d6b2f;"></i><i style="--c: #b4632a;"></i><i style="--c: #f7efdf;"></i><i style="--c: #1d1712;"></i>
                        <span>Label proof &middot; 02</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. When you sell one (03)                                    -->
        <!-- ============================================================ -->
        <section id="ticket" class="bw-sec bw-sec-kraft" style="scroll-margin-top: 4.5rem;">
            <div class="bw-wrap">
                <div class="bw-head-c">
                    <p class="bw-kick" data-reveal><span class="bw-cap bw-cap-sm bw-cap-wine" aria-hidden="true">03</span>When you sell one</p>
                    <h2 class="bw-h2 bw-h2-vin" data-reveal style="--reveal-delay: 0.08s;">
                        The tour is the <span>exception</span>.
                    </h2>
                    <p class="bw-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Twelve people, a glass at each tank, somebody has to be paid to run it. A brewery
                        tour is worth charging for - and it is the only row on the board that is.
                    </p>
                </div>

                <div class="bw-pair" data-reveal-group="130">
                    <div class="bw-lab-wrap" data-reveal>
                        <article class="bw-lab">
                            <span class="bw-lab-crest" aria-hidden="true">I</span>
                            <h3>Limited, and free</h3>
                            <span class="bw-tag">Free plan</span>
                            <p class="bw-lab-lede">When you want the count but not the money.</p>
                            <ul>
                                @foreach ([
                                    'People register instead of paying.',
                                    'A capacity per date, so a full Saturday leaves next Saturday open.',
                                    'It stops taking names when the places are gone.',
                                ] as $point)
                                    <li>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    </div>
                    <div class="bw-lab-wrap" data-reveal>
                        <article class="bw-lab bw-lab-paid">
                            <span class="bw-lab-crest" aria-hidden="true">II</span>
                            <h3>Paid, with a door to scan</h3>
                            <span class="bw-tag">Pro plan</span>
                            <p class="bw-lab-lede">The same event, with a price on it.</p>
                            <ul>
                                @foreach ([
                                    'A price and a quantity, counted per date the same way.',
                                    'QR check-in, so the person on the door is not holding a printout.',
                                    'Paid into your own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash, with no platform fee on top.',
                                ] as $point)
                                    <li>
                                        <span>{{ $point }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </article>
                    </div>
                </div>

                <p class="bw-ticket-foot" data-reveal>
                    Keeping a place on it is free, for as many names as turn up. Putting a price on it
                    is Pro, at {{ plan_price($proMonthly) }} a month, and that is the only part of the
                    board that costs anything. Everything above it stays free.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Other people (04)                                         -->
        <!-- ============================================================ -->
        <section id="guests" class="bw-sec bw-sec-kraft2" style="scroll-margin-top: 4.5rem;">
            <div class="bw-wrap bw-duo">
                <div class="bw-ask" data-reveal="left">
                    <div class="bw-ask-head">
                        <h3>Waiting for you</h3>
                        <span>4 requests</span>
                    </div>
                    <div class="bw-ask-list">
                        @foreach ([
                            ['Fri 14 Mar', 'Marlowe Duo', 'Live music, 8:30pm'],
                            ['Sat 15 Mar', 'El Camion', 'Food truck, 12 to 8'],
                            ['Thu 20 Mar', 'Slice Machine', 'Food truck, 5 to 9'],
                            ['Sun 23 Mar', 'Ash Weller', 'Solo set, 4:00pm'],
                        ] as [$qWhen, $qWho, $qWhat])
                            <div class="bw-collar">
                                <div class="bw-collar-body">
                                    <span class="bw-collar-when">{{ $qWhen }}</span>
                                    <div>
                                        <p class="bw-collar-who">{{ $qWho }}</p>
                                        <p class="bw-collar-what">{{ $qWhat }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="bw-ask-foot">
                        None of them are on the public calendar until you accept them.
                    </p>
                </div>

                <div class="bw-duo-copy">
                    <p class="bw-kick" data-reveal><span class="bw-cap bw-cap-sm bw-cap-copper" aria-hidden="true">04</span>Other people</p>
                    <h2 class="bw-h2 bw-h2-hop" data-reveal style="--reveal-delay: 0.08s;">
                        Half the calendar is <span>somebody else's van</span>.
                    </h2>
                    <p class="bw-lede" data-reveal style="--reveal-delay: 0.16s;">
                        A duo on Friday, a van in the yard on Thursday, a quiz host who does it for beer.
                        You make the drink; a lot of the programme is other people's work, and right
                        now they are asking you for dates in four different apps.
                    </p>
                    <div class="bw-points" data-reveal-group="100">
                        @foreach ([
                            ['They ask through the page', 'Turn on booking requests and the asks arrive attached to the date they are for, instead of a message you scroll past.'],
                            ['Nothing posts without you', 'Every request waits for you to accept it, so the public calendar only shows what you agreed to.'],
                            ['One entry, two calendars', 'Add them to the event as a participant and the date is offered to their own schedule, for them to accept. A duo who is not on Event Schedule yet gets a page with your dates on it, credited to you, and can claim it with the email address you added.'],
                        ] as $bwIdx => [$t, $d])
                            <div class="bw-point" data-reveal>
                                <span class="bw-cap bw-cap-sm" aria-hidden="true">{{ ['A', 'B', 'C'][$bwIdx] }}</span>
                                <p class="bw-point-t">{{ $t }}</p>
                                <p class="bw-point-d">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                    <p class="bw-note" data-reveal>
                        <span class="bw-tag">Free plan</span>
                        <span>Requests and participants cost nothing, and neither does a schedule for the van.</span>
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Who it is for (05)                                        -->
        <!-- ============================================================ -->
        <section id="who" class="bw-sec bw-sec-kraft" style="scroll-margin-top: 4.5rem;">
            <div class="bw-wrap">
                <div class="bw-head-c">
                    <p class="bw-kick" data-reveal><span class="bw-cap bw-cap-sm" aria-hidden="true">05</span>Who it is for</p>
                    <h2 class="bw-h2 bw-h2-both" data-reveal style="--reveal-delay: 0.08s;">
                        Anywhere with <span>a bar and a back room</span>.
                    </h2>
                </div>

                @php
                    // Name, description, blog slug, dress, face colour, type colour, rule colour, a word for the neck.
                    $bwWho = [
                        ['Craft Breweries', 'A taproom open five nights with something on most of them, and one paid tour at the weekend.', 'for-craft-breweries', 'can', '#3d6b2f', '#f7efdf', '', 'Taproom'],
                        ['Wineries', 'Tastings booked ahead and a cellar door open at weekends, kept on separate strands of the same link.', 'for-wineries', 'btl', '#5e1a28', '#f0dca6', '#c9a85a', 'Cellar door'],
                        ['Cideries', 'A pressing day in autumn and a quiet room in February. Recurring nights plus the one-offs that follow the season.', 'for-cideries', 'can', '#96501f', '#f7efdf', '', 'Press house'],
                        ['Distilleries', 'Tours that need paying for and a capacity that matters, alongside a tasting room that does not.', 'for-distilleries', 'btl', '#1d1712', '#dcbf74', '#c9a85a', 'Still room'],
                        ['Meaderies', 'A small room and a loyal list. Followers you can email yourself, and a calendar people can subscribe to.', 'for-meaderies', 'btl', '#d9a93a', '#1d1712', '#1d1712', 'Small batch'],
                        ['Brewpubs', 'Food and beer under one roof, so the kitchen\'s ticketed nights and the free ones sit on the same calendar.', 'for-brewpubs', 'can', '#f0e4c9', '#2f5524', '', 'Kitchen and taps'],
                    ];
                @endphp

                <div class="bw-whos" data-reveal-group="80">
                    @foreach ($bwWho as $bwIdx => [$bwName, $bwDesc, $bwSlug, $bwDress, $bwFace, $bwType, $bwGold, $bwNeck])
                        @php $bwPost = get_sub_audience_blog($bwSlug); @endphp
                        <div class="bw-who-wrap" data-reveal>
                            <article class="bw-who bw-who-{{ $bwDress }}" style="--a: {{ $bwFace }}; --t: {{ $bwType }};{{ $bwGold ? ' --g: '.$bwGold.';' : '' }}">
                                <div class="bw-who-face">
                                    <span class="bw-who-top" aria-hidden="true">No. {{ str_pad($bwIdx + 1, 2, '0', STR_PAD_LEFT) }} &middot; {{ $bwNeck }}</span>
                                    <h3>{{ $bwName }}</h3>
                                </div>
                                <p>{{ $bwDesc }}</p>
                                @if ($bwPost)
                                    <a href="{{ blog_url('/' . $bwPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $bwName }}">
                                        <span>Learn more {!! $bwArrow !!}</span>
                                    </a>
                                @endif
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. How it works (06, the cellar)                             -->
        <!-- ============================================================ -->
        <section id="how" class="bw-sec bw-cellar bw-on-dark" style="scroll-margin-top: 4.5rem;">
            <div class="bw-wrap">
                <div class="bw-head-c">
                    <p class="bw-kick" data-reveal><span class="bw-cap bw-cap-sm bw-cap-gold" aria-hidden="true">06</span>How it works</p>
                    <h2 class="bw-h2 bw-h2-both" data-reveal style="--reveal-delay: 0.08s;">
                        An hour in January, <span>then the year runs</span>.
                    </h2>
                </div>

                <div class="bw-steps" data-reveal-group="160">
                    @foreach ([
                        ['01', 'Put the repeating nights up', 'One recurring event per night, with the weeks you are shut taken out. Most of them are free and stay free.'],
                        ['02', 'Open the requests', 'Bands and vans ask for dates through the page. You accept the ones you want and nothing else appears.'],
                        ['03', 'Charge for the one that needs it', 'The tour gets a price, a quantity and a QR code on the door. Charging is the Pro half; free places on the rest are not.'],
                    ] as $bwIdx => [$n, $t, $d])
                        <div class="bw-step" data-reveal>
                            <span @class(['bw-cap', 'bw-cap-steel' => $bwIdx === 1, 'bw-cap-gold' => $bwIdx === 2])>{{ $n }}</span>
                            <h3>{{ $t }}</h3>
                            <p>{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Key features: a tasting flight                            -->
        <!-- ============================================================ -->
        <section class="bw-sec bw-sec-kraft">
            <div class="bw-wrap">
                <div class="bw-head-c" style="margin-bottom: 0;">
                    <h2 class="bw-h2 bw-h2-vin" data-reveal>Key features</h2>
                </div>

                @php
                    // Name, description, URL, what is in the glass, and the head on it.
                    $bwFlight = [
                        ['Recurring Events', 'A weekly night set up once, with the shut weeks taken out', marketing_url('/features/recurring-events'), '#f0c24f', '#fff8e6'],
                        ['Sub-schedules', 'Music, tours and private hire on their own strands and links', marketing_url('/features/sub-schedules'), '#d98a2b', '#fff3da'],
                        ['Ticketing', 'For the paid tour: a quantity, QR check-in and zero platform fees', marketing_url('/features/ticketing'), '#a8501f', '#f6e4c4'],
                        ['PayPal', 'Take tour and tasting tickets through your own PayPal account, on the Pro plan', marketing_url('/paypal'), '#2a1a12', '#e2c79a'],
                        ['Embed Calendar', 'Put the taproom calendar on the site you already have', marketing_url('/features/embed-calendar'), '#6b1d2a', '#8a3040'],
                    ];
                @endphp

                <div class="bw-flight" data-reveal>
                    <div class="bw-paddle" aria-hidden="true"></div>
                    @foreach ($bwFlight as $bwIdx => [$bwName, $bwDesc, $bwUrl, $bwLiq, $bwFoam])
                        <a href="{{ $bwUrl }}" class="bw-pour">
                            <span class="bw-glass" aria-hidden="true" style="--liq: {{ $bwLiq }}; --foam: {{ $bwFoam }}; --i: {{ $bwIdx }};"><i></i></span>
                            <span class="bw-pour-text">
                                <span class="bw-pour-name">{{ $bwName }}</span>
                                <span class="bw-pour-desc">{{ $bwDesc }}</span>
                            </span>
                            {!! $bwArrow !!}
                        </a>
                    @endforeach
                </div>

                <p class="bw-flight-more" data-reveal>
                    <a href="{{ marketing_url('/features') }}" class="bw-more">
                        See all features
                        {!! $bwArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <div class="bw-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 9. Related pages: four neck tags                             -->
        <!-- ============================================================ -->
        <section class="bw-sec bw-sec-kraft2">
            <div class="bw-wrap">
                <div class="bw-necks-head">
                    <h2 class="bw-h2 bw-h2-hop" data-reveal>Related pages</h2>
                    <a href="{{ marketing_url('/use-cases') }}" class="bw-more" data-reveal>
                        See all use cases
                        {!! $bwArrow !!}
                    </a>
                </div>

                <div class="bw-necks" data-reveal-group="90">
                    @foreach ([
                        ['/for-bars', 'Bars'],
                        ['/for-restaurants', 'Restaurants'],
                        ['/for-food-trucks-and-vendors', 'Food Trucks'],
                        ['/for-music-venues', 'Music Venues'],
                    ] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="bw-neck" data-reveal>
                            <div class="bw-neck-card">
                                <small>Event Schedule for</small>
                                <strong>{{ $relName }}</strong>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. FAQ (07)                                                 -->
        <!-- ============================================================ -->
        <section id="faq" class="bw-sec bw-sec-kraft" style="scroll-margin-top: 4.5rem;">
            <div class="bw-wrap bw-faq-grid">
                <div class="bw-faq-head bw-head-l">
                    <p class="bw-kick" data-reveal><span class="bw-cap bw-cap-sm bw-cap-wine" aria-hidden="true">07</span>Questions</p>
                    <h2 class="bw-h2 bw-h2-vin" data-reveal style="--reveal-delay: 0.08s;">
                        Asked <span>across the bar</span>.
                    </h2>
                </div>

                <div class="bw-faq" data-reveal>
                    @foreach ($faqs as $faq)
                        <details name="faq">
                            <summary>
                                <span class="bw-cap" aria-hidden="true">?</span>
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
        <!-- 11. Finale: the label, waiting for a name                    -->
        <!-- ============================================================ -->
        <section id="claim" class="bw-fin" style="scroll-margin-top: 4rem;">
            <div class="bw-fin-vin" aria-hidden="true"></div>
            <div class="bw-fin-hop" aria-hidden="true"></div>
            <div class="bw-fin-wrap" data-reveal="panel">
                <div class="bw-fin-label">
                    <p class="bw-kick"><span class="bw-cap bw-cap-sm" aria-hidden="true">ES</span>Free forever</p>
                    <h2 class="bw-h2 bw-h2-both">
                        Put the week up and <span>leave it there</span>.
                    </h2>
                    <p>
                        The nights, the strands, the requests and every free place you keep cost
                        nothing at all. Pay only when a tour starts carrying a price.
                    </p>

                    <div class="bw-fin-form">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim bw-claim">
                            <input id="es-claim-input" type="text" placeholder="your-brewery" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="bw-btn">
                            Put the week up
                            {!! $bwArrow !!}
                        </a>
                    </div>

                    <p class="bw-fin-note">No credit card required</p>
                </div>
            </div>
        </section>

        <div class="bw-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    {{-- The seam leans a little toward the pointer. With no script, on touch and under reduced
         motion it rests in the middle, which is the finished state. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var stage = document.getElementById('bw-stage');
            if (!stage
                || !window.matchMedia('(hover: hover) and (pointer: fine)').matches
                || !window.matchMedia('(min-width: 760px)').matches
                || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            stage.addEventListener('pointermove', function (event) {
                var box = stage.getBoundingClientRect();
                var across = (event.clientX - box.left) / box.width - 0.5;
                stage.style.setProperty('--bw-s', (50 + across * 9).toFixed(2) + '%');
            });
            stage.addEventListener('pointerleave', function () {
                stage.style.removeProperty('--bw-s');
            });
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
