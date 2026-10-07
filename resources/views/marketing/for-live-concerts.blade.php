<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Live Concerts | Tours & Livestreams</x-slot>
    <x-slot name="description">Put a whole tour online at once: a room, a door time and an on-sale in every city, livestream tickets beside room tickets, and zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Live Concerts</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Big Shoulders Display') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Barlow') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Live Concerts"
        description="Put a whole tour routing online at once: a room, a door time and an on-sale in every city, sold from one address with zero platform fees."
        audience="Concert Promoters and Touring Shows"
        keywords="live concert streaming, virtual concert tickets, livestream concerts, tour routing, concert promoter calendar, gig schedule" />
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
           For-live-concerts "The Rig" styles. The page is a touring
           production seen from the floor: truss and moving lights
           overhead, the headline on the LED wall, the crowd as points
           of light, and the production office's paperwork in between.

           By day (light mode) it is load-in: a lit, empty arena, the
           lamps parked, the wall on a test pattern. By night the house
           lights are down. Two bands and every LED surface are fixed
           dark in both modes and carry literal colours.

           Everything is scoped under #lc. The shared es-* reveal system
           (marketing.css, marketing-home.js) still drives the entrances.
           ============================================================== */

        @property --lc-a {
            syntax: '<angle>';
            inherits: true;
            initial-value: 0deg;
        }

        #lc {
            --lc-bg: #e8e8e4;
            --lc-bg-2: #dcdcd7;
            --lc-panel: #f4f4f1;
            --lc-panel-2: #fbfbf9;
            --lc-paper: #fbfaf6;
            --lc-ink: #111214;
            --lc-ink-2: #34373d;
            --lc-ink-3: #565a62;
            --lc-line: rgba(17, 18, 20, 0.16);
            --lc-line-2: rgba(17, 18, 20, 0.36);
            --lc-steel: #7a7f88;
            --lc-steel-hi: #a4a9b2;
            --lc-amber: #ffb000;
            --lc-amber-ink: #7a4f00;
            --lc-red: #b81a10;
            --lc-on-red: #ffffff;
            --lc-focus: #111214;
            --lc-show: 0;
            --lc-display: 'Big Shoulders Display', 'Arial Narrow', 'Helvetica Neue', Impact, sans-serif;
            --lc-text: 'Barlow', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --lc-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.09 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)'/%3E%3C/svg%3E");
            position: relative;
            background-color: var(--lc-bg);
            background-image: var(--lc-grain);
            color: var(--lc-ink);
            font-family: var(--lc-text);
            font-size: 1.0625rem;
            line-height: 1.55;
        }
        .dark #lc,
        #lc .lc-night {
            --lc-bg: #050506;
            --lc-bg-2: #0b0c0f;
            --lc-panel: #111216;
            --lc-panel-2: #17191e;
            --lc-ink: #f3f1ea;
            --lc-ink-2: #c3c6cd;
            --lc-ink-3: #9094a0;
            --lc-line: rgba(243, 241, 234, 0.14);
            --lc-line-2: rgba(243, 241, 234, 0.3);
            --lc-steel: #3a3e47;
            --lc-steel-hi: #5a5f6a;
            --lc-amber-ink: #ffb000;
            --lc-red: #ff4a3d;
            --lc-on-red: #111214;
            --lc-focus: #ffb000;
            --lc-show: 1;
        }
        .dark #lc {
            --lc-paper: #e2dfd5;
            background-image: none;
        }

        /* The bar above takes the arena's ground, so the page reads as one room. */
        body > header.sticky {
            background-color: rgba(232, 232, 228, 0.9);
            border-bottom-color: rgba(17, 18, 20, 0.14);
        }
        .dark body > header.sticky {
            background-color: rgba(5, 5, 6, 0.88);
            border-bottom-color: rgba(243, 241, 234, 0.12);
        }

        #lc ::selection { background: #ffb000; color: #111214; }
        #lc a:focus-visible,
        #lc summary:focus-visible,
        #lc input:focus-visible {
            outline: 3px solid var(--lc-focus);
            outline-offset: 3px;
        }

        .lc-wrap { width: min(100% - 2.5rem, 78rem); margin-inline: auto; }
        .lc-sec { position: relative; padding-block: clamp(4.5rem, 9vw, 8rem); scroll-margin-top: 4rem; }
        .lc-alt { background-color: var(--lc-bg-2); background-image: var(--lc-grain); }
        .dark #lc .lc-alt { background-image: none; }
        .lc-night { background-color: #050506; color: #f3f1ea; }

        /* Type: the tall industrial face for anything shouted, Barlow for the rest. */
        .lc-d {
            font-family: var(--lc-display);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.012em;
            line-height: 0.92;
        }
        .lc-h2 { font-size: clamp(2.7rem, 6.6vw, 5.6rem); text-wrap: balance; }
        .lc-k {
            font-family: var(--lc-text);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            line-height: 1.3;
            color: var(--lc-ink-3);
        }
        .lc-sub { max-width: 40rem; color: var(--lc-ink-2); font-size: 1.15rem; }
        /* The accent in a heading: a strip of amber tape by day, amber light by night. */
        .lc-hot {
            /* The tape is cut to the capitals, so it never covers the line above it. */
            background-image: linear-gradient(to bottom, transparent 0 17%, #ffb000 17% 91%, transparent 91%);
            color: #111214;
            padding-inline: 0.14em;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
        .dark #lc .lc-hot,
        #lc .lc-night .lc-hot { background-image: none; color: #ffb000; padding: 0; }
        .lc-a {
            color: var(--lc-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--lc-amber);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }
        .lc-a:hover { color: var(--lc-amber-ink); }

        .lc-head { display: grid; gap: 1.15rem; justify-items: start; }
        .lc-head-row { display: flex; align-items: center; gap: 0.9rem; }
        .lc-head-mid { justify-items: center; text-align: center; margin-inline: auto; }
        .lc-head-mid .lc-sub { margin-inline: auto; }

        /* Dot-matrix type: plain text seen through a grid of dots. */
        .lc-led {
            --lc-pitch: max(2px, 0.06em);
            font-family: var(--lc-display);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1;
            color: var(--lc-led-ink, #ffb000);
            -webkit-mask-image: radial-gradient(circle closest-side at 50% 50%, #000 0 80%, transparent 98%);
            mask-image: radial-gradient(circle closest-side at 50% 50%, #000 0 80%, transparent 98%);
            -webkit-mask-size: var(--lc-pitch) var(--lc-pitch);
            mask-size: var(--lc-pitch) var(--lc-pitch);
        }
        /* A section number, lit on its own small module. */
        .lc-num {
            display: inline-grid;
            place-items: center;
            min-width: 3.2rem;
            padding: 0.55rem 0.6rem 0.4rem;
            background-color: #0a0b0d;
            border-radius: 3px;
            box-shadow: inset 0 0 0 1px #2a2c33, 0 0 0 1px #000;
        }
        .lc-num .lc-led { font-size: 2rem; }

        .lc-tier {
            display: inline-flex;
            align-items: center;
            padding: 0.24rem 0.5rem 0.18rem;
            border: 1.5px solid var(--lc-ink-3);
            border-radius: 2px;
            font-family: var(--lc-text);
            font-weight: 700;
            font-size: 0.68rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1;
            color: var(--lc-ink-2);
            white-space: nowrap;
        }
        .lc-tier-pro { background-color: var(--lc-amber); border-color: var(--lc-amber); color: #111214; }
        .lc-tier-ent { background-color: var(--lc-ink); border-color: var(--lc-ink); color: var(--lc-bg); }

        .lc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 0.95rem 1.5rem 0.8rem;
            border: 2px solid #111214;
            border-radius: 3px;
            background-color: #ffb000;
            color: #111214;
            font-family: var(--lc-display);
            font-weight: 700;
            font-size: 1.4rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            line-height: 1;
            white-space: nowrap;
            transition: background-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }
        .lc-btn:hover { background-color: #ffc640; box-shadow: 0 0 0 4px rgba(255, 176, 0, 0.3); }
        .dark #lc .lc-btn,
        #lc .lc-night .lc-btn { border-color: #ffb000; }
        .dark #lc .lc-btn:hover,
        #lc .lc-night .lc-btn:hover { box-shadow: 0 0 0 1px rgba(255, 176, 0, 0.6), 0 0 2.2rem rgba(255, 176, 0, 0.45); }
        .lc-btn svg { width: 1.25rem; height: 1.25rem; transition: translate 0.2s ease; }
        .lc-btn:hover svg { translate: 0.25rem 0; }
        #lc .lc-btn-ghost,
        .dark #lc .lc-btn-ghost,
        #lc .lc-night .lc-btn-ghost { background-color: transparent; color: var(--lc-ink); border-color: var(--lc-ink); }
        #lc .lc-btn-ghost:hover { background-color: var(--lc-ink); color: var(--lc-bg); box-shadow: none; }
        #lc .lc-btn-ghost:hover svg { translate: 0 0.25rem; }
        /* Under 390px the longest label is wider than the column, and ran off the screen. */
        @media (max-width: 389px) {
            #lc .lc-cta .lc-btn { padding-inline: 1rem; font-size: 1.25rem; white-space: normal; text-align: center; }
        }
        /* The padding above the label is handed back as margin: a 24px target, nothing moved. */
        .lc-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--lc-ink);
            border-bottom: 2px solid var(--lc-amber);
            margin-top: -0.2rem;
            padding-block: 0.2rem;
            transition: gap 0.2s ease;
        }
        .lc-more:hover { gap: 0.9rem; }
        .lc-more svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           The rig: truss, moving heads, haze. Each head carries one
           registered angle; its body and its beam both turn on it. By
           day --lc-show is 0, which parks every lamp pointing down.
           --------------------------------------------------------------- */
        .lc-rig { position: absolute; inset: 0; overflow: clip; pointer-events: none; }
        .lc-truss {
            position: absolute;
            inset: 0 0 auto 0;
            height: 2.6rem;
            background:
                linear-gradient(var(--lc-steel-hi), var(--lc-steel-hi)) 0 0 / 100% 5px no-repeat,
                linear-gradient(var(--lc-steel-hi), var(--lc-steel-hi)) 0 100% / 100% 5px no-repeat,
                repeating-linear-gradient(90deg, var(--lc-steel) 0 4px, transparent 4px 5.2rem),
                repeating-linear-gradient(58deg, transparent 0 1.25rem, var(--lc-steel) 1.25rem calc(1.25rem + 3px)),
                repeating-linear-gradient(-58deg, transparent 0 1.25rem, var(--lc-steel) 1.25rem calc(1.25rem + 3px));
        }
        .lc-mh { position: absolute; top: 2.6rem; left: var(--x); width: 0; height: 0; --lc-a: var(--rest); }
        .lc-mh-yoke {
            position: absolute;
            left: -1.15rem;
            top: 0;
            width: 2.3rem;
            height: 2.1rem;
            border: 0.3rem solid #25272d;
            border-top: 0;
            border-radius: 0 0 0.6rem 0.6rem;
        }
        .lc-mh-yoke::before {
            content: "";
            position: absolute;
            left: 50%;
            top: -0.2rem;
            translate: -50% 0;
            width: 1rem;
            height: 0.6rem;
            border-radius: 2px;
            background-color: #25272d;
        }
        .lc-mh-head {
            position: absolute;
            left: -0.8rem;
            top: 0.3rem;
            width: 1.6rem;
            height: 2rem;
            border-radius: 0.4rem 0.4rem 0.75rem 0.75rem;
            background: linear-gradient(90deg, #15161a, #3a3d45 45%, #15161a);
            transform-origin: 50% 0.55rem;
            rotate: calc(var(--lc-a) * var(--lc-show));
        }
        .lc-mh-head::after {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -0.12rem;
            translate: -50% 0;
            width: 1.15rem;
            height: 0.42rem;
            border-radius: 50%;
            background-color: rgb(var(--c));
            opacity: calc(0.4 + var(--lc-show) * 0.6);
            box-shadow: 0 0 calc(var(--lc-show) * 0.9rem) rgb(var(--c) / 0.9);
        }
        .lc-mh-beam {
            position: absolute;
            left: 0;
            top: 0.85rem;
            width: var(--bw, 23rem);
            height: var(--bl, 44rem);
            translate: -50% 0;
            transform-origin: 50% 0;
            rotate: calc(var(--lc-a) * var(--lc-show));
            clip-path: polygon(47.5% 0, 52.5% 0, 100% 100%, 0 100%);
            background: linear-gradient(to bottom, rgb(var(--c) / 0.46) 0%, rgb(var(--c) / 0.16) 42%, rgb(var(--c) / 0) 92%);
            filter: blur(6px);
            opacity: 0.3;
        }
        .dark #lc .lc-mh-beam,
        #lc .lc-night .lc-mh-beam { mix-blend-mode: screen; opacity: 0.85; }
        .lc-mh-1 { --x: 9%; --c: 255 176 0; --rest: -20deg; --from: -30deg; --to: -6deg; --dur: 13s; }
        .lc-mh-2 { --x: 29.5%; --c: 236 244 255; --rest: -8deg; --from: -22deg; --to: 10deg; --dur: 17s; }
        .lc-mh-3 { --x: 50%; --c: 155 232 255; --rest: 3deg; --from: -15deg; --to: 15deg; --dur: 11s; }
        .lc-mh-4 { --x: 70.5%; --c: 236 244 255; --rest: 9deg; --from: -9deg; --to: 23deg; --dur: 19s; }
        .lc-mh-5 { --x: 91%; --c: 255 176 0; --rest: 21deg; --from: 5deg; --to: 31deg; --dur: 23s; }
        @media (max-width: 760px) {
            .lc-mh-1, .lc-mh-3, .lc-mh-5 { display: none; }
            .lc-mh-2 { --x: 20%; --c: 255 176 0; --from: -20deg; --to: 2deg; --rest: -10deg; }
            .lc-mh-4 { --x: 80%; --c: 155 232 255; --from: -2deg; --to: 20deg; --rest: 10deg; }
            .lc-mh-beam { --bw: 13rem; --bl: 30rem; }
        }
        html.es-anim.dark #lc .lc-live .lc-mh,
        html.es-anim #lc .lc-night.lc-live .lc-mh { animation: lc-sweep var(--dur) ease-in-out infinite alternate; }
        @keyframes lc-sweep {
            from { --lc-a: var(--from); }
            to { --lc-a: var(--to); }
        }
        .lc-haze {
            position: absolute;
            inset: 8% -12% 18% -12%;
            background:
                radial-gradient(38% 46% at 28% 42%, rgba(255, 255, 255, 0.07), transparent 70%),
                radial-gradient(44% 52% at 74% 58%, rgba(155, 232, 255, 0.06), transparent 70%);
            opacity: var(--lc-show);
        }
        html.es-anim #lc .lc-live .lc-haze { animation: lc-drift 31s ease-in-out infinite alternate; }
        @keyframes lc-drift {
            from { translate: -3% 0; }
            to { translate: 4% -3%; }
        }
        /* The blinders: one soft flash as the show arrives, never repeated. */
        .lc-blinder {
            position: absolute;
            inset: 0;
            background: radial-gradient(70% 55% at 50% 0%, rgba(255, 236, 200, 0.85), transparent 70%);
            opacity: 0;
        }
        html.es-anim.dark #lc .lc-hero .lc-blinder { animation: lc-blind 1.3s ease-out 0.9s 1 both; }
        @keyframes lc-blind {
            0% { opacity: 0; }
            14% { opacity: 0.5; }
            100% { opacity: 0; }
        }

        /* ---------------------------------------------------------------
           The LED wall. The wall is a dark physical object in both
           modes. The unlit pixel grid is painted on the screen element;
           the lit layer has exactly the same box and is cut by a dot
           mask of the same pitch, so lit and unlit pixels line up.
           --------------------------------------------------------------- */
        .lc-wall {
            position: relative;
            container-type: inline-size;
            background-color: #0a0b0d;
            border-radius: 4px;
            box-shadow: 0 0 0 3px #1b1d22, 0 0 0 4px #000, 0 2.5rem 5rem -2.2rem rgba(0, 0, 0, 0.7);
        }
        /* The chains it flies on. */
        .lc-wall::before {
            content: "";
            position: absolute;
            left: 9%;
            right: 9%;
            bottom: 100%;
            height: 3.4rem;
            background:
                linear-gradient(var(--lc-steel), var(--lc-steel)) 0 0 / 3px 100% no-repeat,
                linear-gradient(var(--lc-steel), var(--lc-steel)) 33.33% 0 / 3px 100% no-repeat,
                linear-gradient(var(--lc-steel), var(--lc-steel)) 66.66% 0 / 3px 100% no-repeat,
                linear-gradient(var(--lc-steel), var(--lc-steel)) 100% 0 / 3px 100% no-repeat;
        }
        /* Panel seams. */
        .lc-wall::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 3;
            pointer-events: none;
            background:
                linear-gradient(90deg, rgba(0, 0, 0, 0.75) 0 1px, transparent 1px) 0 0 / calc(100% / var(--lc-cols, 8)) 100%,
                linear-gradient(rgba(0, 0, 0, 0.75) 0 1px, transparent 1px) 0 0 / 100% calc(100% / var(--lc-rows, 3));
        }
        .lc-screen { position: relative; margin: 0; --lc-pitch: 6px; }
        .lc-screen::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle closest-side at 50% 50%, #1e2026 0 56%, transparent 74%) 0 0 / var(--lc-pitch) var(--lc-pitch);
        }
        .lc-wall-glow { position: relative; display: block; }
        .dark #lc .lc-wall-glow,
        #lc .lc-night .lc-wall-glow { filter: drop-shadow(0 0 0.5rem rgba(255, 190, 90, 0.3)) drop-shadow(0 0 1px rgba(255, 255, 255, 0.4)); }
        .lc-wall-lit {
            display: block;
            padding: 3.7rem 2.9cqi 2.5rem;
            text-align: center;
            -webkit-mask-image: radial-gradient(circle closest-side at 50% 50%, #000 0 84%, transparent 100%);
            mask-image: radial-gradient(circle closest-side at 50% 50%, #000 0 84%, transparent 100%);
            -webkit-mask-size: var(--lc-pitch) var(--lc-pitch);
            mask-size: var(--lc-pitch) var(--lc-pitch);
        }
        .lc-line {
            display: block;
            font-family: var(--lc-display);
            font-weight: 700;
            font-size: var(--lc-fit, 9.8cqi);
            letter-spacing: 0.014em;
            line-height: 0.98;
            text-transform: uppercase;
            white-space: nowrap;
            color: #ece7d8;
        }
        .lc-hot-led { color: #f2a81a; }
        @supports ((-webkit-background-clip: text) or (background-clip: text)) {
            .dark #lc .lc-line,
            #lc .lc-night .lc-line {
                color: transparent;
                background-image: linear-gradient(100deg, #f6f3ea 0 41%, #9be8ff 50%, #f6f3ea 59% 100%);
                background-size: 320% 100%;
                background-position: 100% 0;
                -webkit-background-clip: text;
                background-clip: text;
            }
            .dark #lc .lc-hot-led,
            #lc .lc-night .lc-hot-led {
                color: transparent;
                background-image: linear-gradient(100deg, #ffb000 0 41%, #fff3cc 50%, #ffb000 59% 100%);
                background-size: 320% 100%;
                background-position: 100% 0;
                -webkit-background-clip: text;
                background-clip: text;
            }
            html.es-anim.dark #lc .lc-live .lc-line,
            html.es-anim.dark #lc .lc-live .lc-hot-led,
            html.es-anim #lc .lc-night.lc-live .lc-line,
            html.es-anim #lc .lc-night.lc-live .lc-hot-led { animation: lc-scan 9s steps(60) 1.6s infinite; }
        }
        @keyframes lc-scan {
            0% { background-position: 100% 0; }
            45%, 100% { background-position: 0% 0; }
        }
        /* Content wipes onto the wall a column at a time. */
        html.es-anim #lc .lc-hero .lc-wall-lit { animation: lc-wipe 1s steps(26) 0.35s both; }
        @keyframes lc-wipe {
            from { clip-path: inset(0 100% 0 0); }
            to { clip-path: inset(0 0 0 0); }
        }

        /* The source label on the wall's top edge: the h1's keyword line. */
        #lc .lc-screen > .es-hero-eyebrow {
            position: absolute;
            z-index: 2;
            inset: 0.95rem auto auto 1.15rem;
            max-width: calc(100% - 2.3rem);
            line-height: 1.2;
        }
        .lc-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.35rem 0.6rem 0.28rem;
            background-color: #0a0b0d;
            font-family: var(--lc-text);
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #f3d38a;
        }
        .lc-eyebrow::before {
            content: "";
            flex: none;
            width: 0.5rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background-color: #ffb000;
            box-shadow: 0 0 0.5rem rgba(255, 176, 0, 0.9);
        }
        /* Load-in furniture: the test pattern, and what the desk says. */
        .lc-wall-test { position: absolute; inset: 0; z-index: 1; pointer-events: none; }
        .lc-wall-test::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.07) 0 1px, transparent 1px) 0 0 / calc(100% / 16) 100%,
                linear-gradient(rgba(255, 255, 255, 0.07) 0 1px, transparent 1px) 0 0 / 100% calc(100% / 6);
        }
        .lc-wall-test::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            translate: -50% -50%;
            height: 88%;
            aspect-ratio: 1;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 50%;
        }
        .dark #lc .lc-wall-test::before,
        .dark #lc .lc-wall-test::after,
        #lc .lc-night .lc-wall-test::before,
        #lc .lc-night .lc-wall-test::after { display: none; }
        .lc-wall-test span {
            position: absolute;
            z-index: 1;
            bottom: 0.7rem;
            padding: 0.15rem 0.4rem;
            background-color: #0a0b0d;
            font-family: var(--lc-text);
            font-weight: 700;
            font-size: 0.62rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #8d919c;
        }
        .lc-wall-test span:nth-of-type(odd) { left: 1.15rem; }
        .lc-wall-test span:nth-of-type(even) { right: 1.15rem; }
        .lc-wall-test .lc-nite { display: none; }
        .dark #lc .lc-wall-test .lc-nite,
        #lc .lc-night .lc-wall-test .lc-nite { display: block; }
        .dark #lc .lc-wall-test .lc-day,
        #lc .lc-night .lc-wall-test .lc-day { display: none; }

        /* ---------------------------------------------------------------
           Hero
           --------------------------------------------------------------- */
        .lc-hero { position: relative; isolation: isolate; overflow: clip; padding-top: 6.4rem; scroll-margin-top: 4rem; }
        .dark #lc .lc-hero { background: radial-gradient(120% 65% at 50% 0%, #191b21 0%, #08090b 58%, #050506 100%); }
        .lc-hero-in { position: relative; }
        .lc-deck {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 2rem 3.5rem;
            align-items: start;
            margin-top: 2.5rem;
        }
        @media (min-width: 980px) { .lc-deck { grid-template-columns: minmax(0, 1.12fr) minmax(0, 0.88fr); } }
        .lc-lede { max-width: 37rem; font-size: clamp(1.1rem, 1.5vw, 1.3rem); color: var(--lc-ink-2); }
        .lc-cta { display: flex; flex-wrap: wrap; gap: 0.9rem 1rem; margin-top: 1.7rem; }
        /* The house board beside the desk: the tour, tonight, the count. */
        .lc-board {
            background-color: #0a0b0d;
            color: #f3f1ea;
            border-radius: 4px;
            box-shadow: 0 0 0 3px #1b1d22, 0 0 0 4px #000, 0 1.5rem 3rem -1.6rem rgba(0, 0, 0, 0.7);
        }
        .lc-board-top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.8rem 1.1rem 0.65rem;
            border-bottom: 1px solid rgba(243, 241, 234, 0.14);
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #b9bcc4;
        }
        .lc-board-on { display: inline-flex; align-items: center; gap: 0.5rem; color: #f3d38a; }
        .lc-board-on::before { content: ""; width: 0.45rem; aspect-ratio: 1; border-radius: 50%; background-color: #ffb000; }
        .lc-board-name { display: block; padding: 1rem 1.1rem 0.85rem; font-size: clamp(2.1rem, 4.6vw, 3rem); --lc-led-ink: #f6f3ea; }
        .lc-board-cells { display: grid; grid-template-columns: 1.5fr 1fr 0.8fr; border-top: 1px solid rgba(243, 241, 234, 0.14); }
        .lc-board-cells > div { padding: 0.8rem 1.1rem 0.75rem; min-width: 0; }
        .lc-board-cells > div + div { border-inline-start: 1px solid rgba(243, 241, 234, 0.14); }
        .lc-board-cells dt { font-weight: 700; font-size: 0.65rem; letter-spacing: 0.2em; text-transform: uppercase; color: #9a9ea9; }
        .lc-board-cells dd { margin-top: 0.4rem; font-size: 2rem; white-space: nowrap; }
        .lc-board-cap { margin-top: 0.9rem; font-size: 0.875rem; color: var(--lc-ink-3); }

        /* Show types, lettered like flight-case labels. */
        .lc-types { position: relative; z-index: 2; min-width: 0; margin-top: clamp(2.5rem, 5vw, 3.75rem); overflow: hidden; }
        .lc-types .es-marquee-track { gap: 0.75rem; padding-right: 0.75rem; }
        .lc-type {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.9rem 0.42rem;
            border: 1.5px solid var(--lc-line-2);
            border-radius: 2px;
            font-size: 1.15rem;
            letter-spacing: 0.08em;
            white-space: nowrap;
            color: var(--lc-ink-2);
        }
        .lc-type::before { content: ""; width: 0.5rem; aspect-ratio: 1; background-color: var(--lc-amber); }

        /* The floor. By day a line of hazard tape; by night the crowd:
           lattices of dots at three depths, coloured by one gradient that
           steps across them the way wristbands change in a wave. */
        .lc-floor { position: relative; margin-top: clamp(2rem, 4vw, 3rem); }
        .lc-tape { height: 0.9rem; background: repeating-linear-gradient(-45deg, #ffb000 0 0.9rem, #111214 0.9rem 1.8rem); }
        .dark #lc .lc-tape { display: none; }
        .lc-crowd { display: none; position: relative; height: clamp(5.5rem, 12vw, 9.5rem); overflow: hidden; }
        .dark #lc .lc-crowd,
        #lc .lc-night .lc-crowd { display: block; }
        .lc-crowd::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 1;
            background: linear-gradient(to bottom, #050506 0%, rgba(5, 5, 6, 0.35) 38%, transparent 66%, rgba(5, 5, 6, 0.55) 100%);
        }
        .lc-crowd i {
            position: absolute;
            left: 0;
            right: 0;
            background: linear-gradient(90deg, #fff3d6 0 36%, #9be8ff 46% 54%, #fff3d6 64% 100%) 0 0 / 340% 100%;
        }
        .lc-crowd i:nth-child(1) {
            top: 0;
            height: 52%;
            opacity: 0.6;
            -webkit-mask: radial-gradient(circle at 50% 50%, #000 0 0.6px, transparent 1.2px) 2px 3px / 9px 7px, radial-gradient(circle at 50% 50%, #000 0 0.6px, transparent 1.2px) 6px 1px / 13px 11px;
            mask: radial-gradient(circle at 50% 50%, #000 0 0.6px, transparent 1.2px) 2px 3px / 9px 7px, radial-gradient(circle at 50% 50%, #000 0 0.6px, transparent 1.2px) 6px 1px / 13px 11px;
            --tw: 3.1s;
            --td: -0.6s;
        }
        .lc-crowd i:nth-child(2) {
            top: 24%;
            height: 54%;
            opacity: 0.85;
            -webkit-mask: radial-gradient(circle at 50% 50%, #000 0 1px, transparent 1.8px) 4px 2px / 21px 17px, radial-gradient(circle at 50% 50%, #000 0 1px, transparent 1.8px) 13px 9px / 29px 23px, radial-gradient(circle at 50% 50%, #000 0 0.8px, transparent 1.6px) 7px 12px / 47px 13px;
            mask: radial-gradient(circle at 50% 50%, #000 0 1px, transparent 1.8px) 4px 2px / 21px 17px, radial-gradient(circle at 50% 50%, #000 0 1px, transparent 1.8px) 13px 9px / 29px 23px, radial-gradient(circle at 50% 50%, #000 0 0.8px, transparent 1.6px) 7px 12px / 47px 13px;
            --tw: 2.3s;
            --td: -1.4s;
        }
        .lc-crowd i:nth-child(3) {
            top: 50%;
            height: 50%;
            -webkit-mask: radial-gradient(circle at 50% 50%, #000 0 1.7px, transparent 3.4px) 9px 6px / 43px 31px, radial-gradient(circle at 50% 50%, #000 0 1.5px, transparent 3px) 27px 17px / 61px 37px, radial-gradient(circle at 50% 50%, #000 0 1.3px, transparent 2.6px) 3px 21px / 79px 23px;
            mask: radial-gradient(circle at 50% 50%, #000 0 1.7px, transparent 3.4px) 9px 6px / 43px 31px, radial-gradient(circle at 50% 50%, #000 0 1.5px, transparent 3px) 27px 17px / 61px 37px, radial-gradient(circle at 50% 50%, #000 0 1.3px, transparent 2.6px) 3px 21px / 79px 23px;
            --tw: 1.9s;
            --td: -0.2s;
        }
        .lc-crowd i:nth-child(4) {
            top: 18%;
            height: 82%;
            background: #ffb000;
            opacity: 0.9;
            -webkit-mask: radial-gradient(circle at 50% 50%, #000 0 1.1px, transparent 2.2px) 17px 11px / 37px 29px;
            mask: radial-gradient(circle at 50% 50%, #000 0 1.1px, transparent 2.2px) 17px 11px / 37px 29px;
            --tw: 2.7s;
            --td: -2s;
        }
        html.es-anim #lc .lc-live .lc-crowd i { animation: lc-wave 18s steps(44) infinite, lc-twinkle var(--tw) ease-in-out var(--td) infinite alternate; }
        @keyframes lc-wave {
            from { background-position: 100% 0; }
            to { background-position: 0% 0; }
        }
        @keyframes lc-twinkle {
            from { opacity: 0.4; }
            to { opacity: 1; }
        }

        /* House lights go down as the page starts to move. */
        @supports (animation-timeline: scroll()) {
            html.es-anim.dark #lc .lc-hero::before {
                content: "";
                position: absolute;
                inset: 0;
                z-index: -1;
                background: radial-gradient(100% 70% at 50% 100%, rgba(150, 138, 118, 0.3), rgba(150, 138, 118, 0.12) 60%, transparent 100%);
                animation: lc-house linear both;
                animation-timeline: scroll(root);
                animation-range: 0 55vh;
            }
            html.es-anim.dark #lc .lc-hero .lc-mh-beam {
                animation: lc-rise linear both;
                animation-timeline: scroll(root);
                animation-range: 0 45vh;
            }
        }
        @keyframes lc-house {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes lc-rise {
            from { opacity: 0.5; }
            to { opacity: 0.85; }
        }

        /* ---------------------------------------------------------------
           02 The routing: the back of the laminate
           --------------------------------------------------------------- */
        .lc-route { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 3.5rem; align-items: start; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 1040px) { .lc-route { grid-template-columns: minmax(0, 1.55fr) minmax(0, 0.75fr); } }
        .lc-lam { position: relative; padding-top: 6.5rem; min-width: 0; }
        /* The lanyard: two runs of webbing that meet at the clip and rise out of sight. */
        .lc-lam::before,
        .lc-lam::after {
            content: "";
            position: absolute;
            left: calc(50% - 0.8rem);
            top: -3.2rem;
            width: 1.6rem;
            height: 10.8rem;
            transform-origin: 50% 100%;
            background: repeating-linear-gradient(0deg, #ffb000 0 1.2rem, #d99400 1.2rem 1.32rem);
            -webkit-mask-image: linear-gradient(to top, #000 45%, transparent 96%);
            mask-image: linear-gradient(to top, #000 45%, transparent 96%);
        }
        .lc-lam::before { rotate: -13deg; }
        .lc-lam::after { rotate: 13deg; }
        .lc-lam-card {
            position: relative;
            z-index: 1;
            overflow: hidden;
            background-color: var(--lc-panel-2);
            border: 1px solid var(--lc-line-2);
            border-radius: 1.5rem;
            box-shadow: 0 2.5rem 4.5rem -3rem rgba(0, 0, 0, 0.6);
        }
        .lc-lam-top {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-end;
            gap: 0.5rem 1.5rem;
            padding: 3.1rem 1.6rem 1.15rem;
            background-color: #0a0b0d;
            color: #f3f1ea;
        }
        /* The punched slot, and the clip the strap ends in. */
        .lc-lam-top::before {
            content: "";
            position: absolute;
            left: 50%;
            top: 1.05rem;
            translate: -50% 0;
            width: 4.6rem;
            height: 0.9rem;
            border-radius: 999px;
            background-color: var(--lc-bg);
            box-shadow: inset 0 2px 3px rgba(0, 0, 0, 0.55);
        }
        .lc-lam-top::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 0.55rem;
            translate: -50% 0;
            width: 2.1rem;
            height: 1.25rem;
            border-radius: 3px;
            background: linear-gradient(#c9cdd4, #7d828b);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.5);
        }
        .lc-lam-top .lc-led { font-size: clamp(1.9rem, 4.4vw, 2.7rem); }
        .lc-lam-top small { font-weight: 700; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; color: #b9bcc4; }
        .lc-dates { width: 100%; border-collapse: collapse; }
        .lc-dates caption { caption-side: top; padding: 1.1rem 1.6rem 0.85rem; text-align: start; }
        .lc-dates th,
        .lc-dates td { padding: 0.85rem 0.8rem 0.75rem; text-align: start; vertical-align: middle; }
        .lc-dates tr > :first-child { padding-inline-start: 1.6rem; }
        .lc-dates tr > :last-child { padding-inline-end: 1.6rem; }
        .lc-dates thead th { padding-block: 0.6rem 0.5rem; border-block: 2px solid var(--lc-ink); font-size: 0.68rem; }
        .lc-dates tbody tr + tr { border-top: 1px solid var(--lc-line); }
        .lc-date-d { white-space: nowrap; font-size: 1.9rem; }
        .lc-date-d small { display: block; margin-bottom: 0.2rem; font-family: var(--lc-text); font-size: 0.65rem; letter-spacing: 0.2em; color: var(--lc-ink-3); }
        .lc-date-room { font-weight: 400; }
        .lc-date-pair { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.1rem 0.7rem; }
        .lc-date-city { order: -1; font-size: 1.9rem; }
        .lc-date-hall { font-weight: 700; font-size: 0.95rem; color: var(--lc-ink-2); }
        .lc-date-doors { font-size: 1.5rem; color: var(--lc-ink-2); }
        .lc-st {
            display: inline-flex;
            padding: 0.34rem 0.55rem 0.26rem;
            border: 1.5px solid transparent;
            border-radius: 2px;
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1;
            white-space: nowrap;
        }
        .lc-st-live { background-color: var(--lc-amber); border-color: var(--lc-amber); color: #111214; }
        .lc-st-gone { background-color: var(--lc-red); border-color: var(--lc-red); color: var(--lc-on-red); }
        .lc-st-soon { border-style: dashed; border-color: var(--lc-ink-3); color: var(--lc-ink-2); }
        .lc-dates tbody tr.lc-now { background-color: rgba(255, 176, 0, 0.2); }
        .lc-dates tbody tr.lc-now .lc-date-d small,
        #lc .lc-dates tbody tr.lc-now .lc-date-doors { color: var(--lc-ink-2); }
        .lc-dates tbody tr { transition: opacity 0.2s ease; }
        .lc-dates tbody:has(tr:hover) tr:not(:hover) { opacity: 0.5; }
        .lc-now-tag { margin-inline-start: 0.2rem; padding: 0.22rem 0.42rem 0.15rem; background-color: var(--lc-ink); color: var(--lc-bg); font-weight: 700; font-size: 0.6rem; letter-spacing: 0.18em; text-transform: uppercase; line-height: 1; align-self: center; }
        @media (max-width: 640px) {
            .lc-dates thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
            .lc-dates,
            .lc-dates tbody { display: block; }
            .lc-dates caption { display: block; padding-inline: 1.1rem; }
            .lc-dates tbody tr {
                display: grid;
                grid-template-columns: 4.6rem minmax(0, 1fr) auto;
                grid-template-areas: "d r s" "d o s";
                align-items: center;
                column-gap: 0.75rem;
                padding: 0.85rem 1.1rem 0.75rem;
            }
            .lc-dates tbody th,
            .lc-dates tbody td { display: block; padding: 0; }
            #lc .lc-dates tr > :first-child { grid-area: d; padding: 0; }
            .lc-dates tbody th { grid-area: r; }
            .lc-dates tbody td:nth-child(3) { grid-area: o; }
            #lc .lc-dates tr > :last-child { grid-area: s; padding: 0; }
            .lc-date-d { font-size: 1.55rem; }
            .lc-date-city { font-size: 1.55rem; }
            .lc-date-doors { font-size: 0.9rem; font-family: var(--lc-text); font-weight: 400; letter-spacing: 0; text-transform: none; color: var(--lc-ink-3); }
            .lc-date-doors::before { content: "Doors "; }
            .lc-lam-top { padding-inline: 1.1rem; }
        }
        .lc-notes { display: grid; gap: 0; }
        .lc-note { padding-block: 1.3rem 1.4rem; border-top: 2px solid var(--lc-ink); }
        .lc-note:last-child { border-bottom: 2px solid var(--lc-ink); }
        .lc-note .lc-k { color: var(--lc-amber-ink); }
        .lc-note p + p { margin-top: 0.55rem; color: var(--lc-ink-2); }
        /* Three figures on three house boards. */
        .lc-stats { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 820px) { .lc-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .lc-stat {
            padding: 1.5rem 1.4rem 1.4rem;
            background-color: #0a0b0d;
            color: #c9ccd3;
            border-radius: 4px;
            box-shadow: 0 0 0 3px #1b1d22, 0 0 0 4px #000;
        }
        .lc-stat .lc-led { display: block; font-size: clamp(4rem, 8vw, 5.6rem); }
        .lc-stat:nth-child(2) .lc-led { --lc-led-ink: #f6f3ea; }
        .lc-stat:nth-child(3) .lc-led { --lc-led-ink: #9be8ff; }
        .lc-stat p { margin-top: 1rem; font-size: 1rem; }

        /* ---------------------------------------------------------------
           03 The day sheet: the stage clock, and the sheet taped up
           --------------------------------------------------------------- */
        .lc-day-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 4rem; align-items: start; }
        @media (min-width: 1000px) { .lc-day-grid { grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr); } }
        .lc-ticks { display: grid; gap: 0.9rem; margin-top: 1.75rem; }
        .lc-ticks li { display: grid; grid-template-columns: 1.1rem minmax(0, 1fr); gap: 0.8rem; color: var(--lc-ink-2); }
        .lc-ticks li::before { content: ""; width: 0.7rem; height: 0.7rem; margin-top: 0.45rem; background-color: var(--lc-amber); }
        .lc-ticks .lc-tier { margin-inline-start: 0.5rem; vertical-align: 0.12em; }
        .lc-clock { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.6rem; margin-bottom: 1.5rem; }
        .lc-clock div {
            padding: 0.8rem 0.9rem 0.7rem;
            background-color: #0a0b0d;
            border-radius: 3px;
            box-shadow: inset 0 0 0 1px #2a2c33, 0 0 0 1px #000;
        }
        .lc-clock span { display: block; font-weight: 700; font-size: 0.62rem; letter-spacing: 0.2em; text-transform: uppercase; color: #9a9ea9; }
        .lc-clock b { display: block; margin-top: 0.45rem; font-size: clamp(1.9rem, 4.6vw, 2.9rem); }
        .lc-clock div:nth-child(2) b { --lc-led-ink: #f6f3ea; }
        .lc-clock div:nth-child(3) b { --lc-led-ink: #ff5a4d; }
        .lc-sheet {
            position: relative;
            padding: 1.9rem 1.6rem 1.4rem;
            background-color: var(--lc-paper);
            color: #15161a;
            rotate: -0.7deg;
            box-shadow: 0 1.6rem 3rem -1.8rem rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(0, 0, 0, 0.08);
        }
        /* Two strips of gaffer tape hold it to the case lid. */
        .lc-sheet::before,
        .lc-sheet::after {
            content: "";
            position: absolute;
            top: -0.55rem;
            width: 4.6rem;
            height: 1.3rem;
            background-color: #17181c;
            opacity: 0.92;
        }
        .lc-sheet::before { left: 1.2rem; rotate: -3deg; }
        .lc-sheet::after { right: 1.2rem; rotate: 2.5deg; }
        .lc-sheet-top { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.3rem 1rem; padding-bottom: 0.8rem; border-bottom: 2px solid #15161a; }
        .lc-sheet-top h3 { font-size: clamp(1.7rem, 3.4vw, 2.3rem); }
        .lc-sheet-top span { font-weight: 700; font-size: 0.8rem; letter-spacing: 0.16em; text-transform: uppercase; color: #4a4d55; }
        .lc-sheet-k { margin-top: 0.9rem; font-weight: 700; font-size: 0.68rem; letter-spacing: 0.22em; text-transform: uppercase; color: #4a4d55; }
        /* The whole night on one bar: each part as wide as it is long. */
        .lc-strip { display: flex; gap: 2px; height: 1.5rem; margin-top: 0.6rem; }
        .lc-strip i { flex-basis: 0; min-width: 0; background-color: #b9b6ab; }
        .lc-strip i.is-set { background-color: #ffb000; }
        .lc-parts { margin-top: 0.9rem; }
        .lc-part {
            position: relative;
            display: grid;
            grid-template-columns: 3.6rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.9rem;
            padding: 0.7rem 0.6rem 0.6rem;
            border-bottom: 1px solid rgba(21, 22, 26, 0.16);
        }
        .lc-part > * { position: relative; }
        .lc-part-fill { position: absolute; inset: 0 auto 0 0; background-color: rgba(21, 22, 26, 0.09); }
        .lc-part.is-set .lc-part-fill { background-color: #ffb000; }
        .lc-part-t { font-size: 1.5rem; }
        .lc-part-n { display: block; font-weight: 700; line-height: 1.25; }
        .lc-part-note { display: block; font-size: 0.85rem; color: #3d4047; }
        .lc-part-len { font-weight: 700; font-size: 0.8rem; letter-spacing: 0.08em; color: #3d4047; white-space: nowrap; }
        .lc-sheet-cap { margin-top: 1rem; font-size: 0.875rem; color: #3d4047; }

        /* ---------------------------------------------------------------
           04 On sale: the box office memo and the pass sheet
           --------------------------------------------------------------- */
        .lc-office { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 3.5rem; align-items: start; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 1040px) { .lc-office { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }
        .lc-memos { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0 2rem; }
        @media (min-width: 620px) { .lc-memos { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .lc-memo { padding-block: 1.3rem 1.5rem; border-top: 2px solid var(--lc-ink); }
        .lc-memo-top { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.7rem; }
        .lc-memo h3 { font-size: 1.65rem; }
        .lc-memo p { margin-top: 0.6rem; font-size: 1rem; color: var(--lc-ink-2); }
        .lc-passsheet { padding: 1.6rem 1.5rem 1.4rem; background-color: var(--lc-panel); border: 2px solid var(--lc-steel); border-radius: 4px; }
        .lc-passsheet-top { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem 1rem; }
        .lc-passsheet-top h3 { font-size: clamp(1.7rem, 3.4vw, 2.3rem); }
        .lc-stream-tag { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.3rem 0.55rem 0.22rem; border: 1.5px solid var(--lc-ink-3); border-radius: 2px; font-weight: 700; font-size: 0.68rem; letter-spacing: 0.16em; text-transform: uppercase; line-height: 1; color: var(--lc-ink-2); }
        .lc-stream-tag::before { content: ""; width: 0.45rem; aspect-ratio: 1; border-radius: 50%; background-color: var(--lc-amber); }
        .lc-passes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem; margin-top: 1.3rem; }
        @media (min-width: 520px) { .lc-passes { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (min-width: 1040px) and (max-width: 1240px) { .lc-passes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .lc-pass {
            position: relative;
            display: grid;
            align-content: start;
            gap: 0.2rem;
            min-width: 0;
            padding: 2.5rem 0.85rem 0.9rem;
            border-radius: 0.8rem;
            background-color: var(--pb);
            color: var(--pf);
            box-shadow: 0 0 0 1.5px var(--pe, transparent), 0 1rem 1.6rem -1.2rem rgba(0, 0, 0, 0.7);
        }
        /* The strap slot. */
        .lc-pass::before { content: ""; position: absolute; left: 50%; top: 0.85rem; translate: -50% 0; width: 2.1rem; height: 0.5rem; border-radius: 999px; background-color: var(--lc-panel); box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.5); }
        .lc-pass-0 { --pb: #ffb000; --pf: #111214; }
        .lc-pass-1 { --pb: #9be8ff; --pf: #111214; }
        .lc-pass-2 { --pb: #f6f3ea; --pf: #111214; --pe: rgba(17, 18, 20, 0.5); }
        .lc-pass-3 { --pb: #111214; --pf: #ffb000; --pe: #ffb000; }
        .lc-pass-3::before { background-color: #ffb000; box-shadow: none; }
        .lc-pass-name { font-size: 1.5rem; overflow-wrap: anywhere; }
        .lc-pass-note { min-height: 2.6em; font-size: 0.78rem; line-height: 1.3; }
        .lc-pass-stock { margin-top: 0.7rem; padding-top: 0.6rem; border-top: 1.5px solid currentColor; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; }
        .lc-pass-price { font-size: 2.4rem; }
        .lc-fee {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 1.3rem;
            padding: 0.8rem 1rem 0.7rem;
            background-color: #0a0b0d;
            border-radius: 3px;
            box-shadow: inset 0 0 0 1px #2a2c33, 0 0 0 1px #000;
        }
        .lc-fee > span:first-child { font-weight: 700; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; color: #b9bcc4; }
        .lc-fee .lc-led { font-size: 2.5rem; }
        .lc-passsheet-note { margin-top: 1rem; font-size: 0.9rem; color: var(--lc-ink-3); }
        .lc-memo-wide { margin-top: 1.75rem; border-bottom: 2px solid var(--lc-ink); }

        /* ---------------------------------------------------------------
           05 One address: front of house, lights down in both modes
           --------------------------------------------------------------- */
        .lc-foh {
            overflow: clip;
            padding-bottom: 0;
            background-image:
                radial-gradient(52% 42% at 16% 0%, rgba(255, 176, 0, 0.17), transparent 72%),
                radial-gradient(52% 42% at 84% 0%, rgba(155, 232, 255, 0.12), transparent 72%);
        }
        .lc-foh-cards { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 900px) { .lc-foh-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .lc-foh-card { display: flex; flex-direction: column; padding: 1.6rem 1.5rem 1.5rem; background-color: #0f1013; border: 1px solid rgba(243, 241, 234, 0.16); border-radius: 4px; }
        .lc-foh-card h3 { font-size: 1.8rem; }
        .lc-foh-card p { margin-top: 0.75rem; color: #c3c6cd; font-size: 1rem; }
        .lc-foh-foot { margin-top: 2.25rem; text-align: center; color: #c3c6cd; }
        .lc-foh-foot a { display: inline-flex; align-items: center; gap: 0.4rem; margin-inline-start: 0.4rem; color: #f3f1ea; font-weight: 700; border-bottom: 2px solid #ffb000; transition: gap 0.2s ease; }
        .lc-foh-foot a:hover { gap: 0.7rem; }
        .lc-foh-foot svg { width: 1rem; height: 1rem; }
        .lc-foh .lc-floor { margin-top: clamp(2.5rem, 5vw, 4rem); }

        /* ---------------------------------------------------------------
           06 Everything else: six road cases
           --------------------------------------------------------------- */
        .lc-cases { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.4rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 900px) {
            .lc-cases { grid-template-columns: repeat(12, minmax(0, 1fr)); }
            .lc-case { grid-column: span 5; }
            .lc-case:nth-child(1),
            .lc-case:nth-child(4),
            .lc-case:nth-child(6) { grid-column: span 7; }
        }
        .lc-case-in {
            position: relative;
            height: 100%;
            padding: 2rem 1.7rem 1.7rem;
            background-color: var(--lc-panel);
            border: 2px solid var(--lc-steel);
            border-radius: 3px;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .lc-case:hover .lc-case-in { border-color: var(--lc-ink); box-shadow: 0 1.4rem 2.4rem -1.8rem rgba(0, 0, 0, 0.6); }
        /* Corner brackets, as a flight case has. */
        .lc-case-in::before {
            content: "";
            position: absolute;
            inset: -2px;
            pointer-events: none;
            --b: var(--lc-ink);
            --s: 1.15rem;
            --w: 5px;
            background:
                linear-gradient(var(--b), var(--b)) 0 0 / var(--s) var(--w) no-repeat,
                linear-gradient(var(--b), var(--b)) 0 0 / var(--w) var(--s) no-repeat,
                linear-gradient(var(--b), var(--b)) 100% 0 / var(--s) var(--w) no-repeat,
                linear-gradient(var(--b), var(--b)) 100% 0 / var(--w) var(--s) no-repeat,
                linear-gradient(var(--b), var(--b)) 0 100% / var(--s) var(--w) no-repeat,
                linear-gradient(var(--b), var(--b)) 0 100% / var(--w) var(--s) no-repeat,
                linear-gradient(var(--b), var(--b)) 100% 100% / var(--s) var(--w) no-repeat,
                linear-gradient(var(--b), var(--b)) 100% 100% / var(--w) var(--s) no-repeat;
        }
        .lc-case-top { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.75rem; }
        .lc-case-no { font-weight: 700; font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--lc-ink-3); margin-inline-start: auto; }
        .lc-case h3 { font-size: clamp(1.7rem, 3vw, 2.2rem); }
        .lc-case p { margin-top: 0.85rem; color: var(--lc-ink-2); }
        .lc-case p.lc-fine { font-size: 0.95rem; color: var(--lc-ink-3); }
        .lc-case-split { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem 2rem; align-items: center; }
        @media (min-width: 620px) { .lc-case-split { grid-template-columns: minmax(0, 1fr) 12.5rem; } }
        /* The stream link: one field. The three chips keep their own brand colours. */
        .lc-patch { padding: 1rem 1rem 0.9rem; background-color: var(--lc-bg-2); border: 1px solid var(--lc-line-2); border-radius: 3px; }
        .lc-patch-list { display: grid; gap: 0.5rem; margin-top: 0.75rem; text-align: center; }
        .lc-chip { padding: 0.4rem 0.5rem; border-radius: 0.5rem; font-weight: 700; font-size: 0.8rem; }
        .lc-patch-note { margin-top: 0.75rem; font-size: 0.78rem; line-height: 1.4; color: var(--lc-ink-3); }

        /* ---------------------------------------------------------------
           07 Perfect for: six banners flown from one truss
           --------------------------------------------------------------- */
        .lc-fly { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.4rem 1.6rem; margin-top: clamp(1rem, 3vw, 2.25rem); padding: 2.5rem 0.8rem 0; overflow: clip; }
        @media (min-width: 700px) { .lc-fly { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .lc-fly { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .lc-ban { position: relative; min-width: 0; }
        /* The truss section above each banner; the sections butt up into one line. */
        .lc-ban::before {
            content: "";
            position: absolute;
            left: -0.8rem;
            right: -0.8rem;
            bottom: calc(100% + 1.1rem);
            height: 1.15rem;
            background:
                linear-gradient(var(--lc-steel-hi), var(--lc-steel-hi)) 0 0 / 100% 3px no-repeat,
                linear-gradient(var(--lc-steel-hi), var(--lc-steel-hi)) 0 100% / 100% 3px no-repeat,
                repeating-linear-gradient(58deg, transparent 0 0.55rem, var(--lc-steel) 0.55rem calc(0.55rem + 2px)),
                repeating-linear-gradient(-58deg, transparent 0 0.55rem, var(--lc-steel) 0.55rem calc(0.55rem + 2px));
        }
        /* Two straps. */
        .lc-ban::after {
            content: "";
            position: absolute;
            left: 14%;
            right: 14%;
            bottom: 100%;
            height: 1.1rem;
            background:
                linear-gradient(var(--lc-steel), var(--lc-steel)) 0 0 / 2px 100% no-repeat,
                linear-gradient(var(--lc-steel), var(--lc-steel)) 100% 0 / 2px 100% no-repeat;
        }
        .lc-ban-in {
            display: flex;
            flex-direction: column;
            height: 100%;
            padding: 1.5rem 1.4rem 1.4rem;
            background-color: var(--lc-panel);
            border: 1px solid var(--lc-line-2);
            border-top: 0.55rem solid var(--lc-ink);
            transform-origin: 50% 0;
            transition: rotate 0.5s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .lc-ban:nth-child(odd):hover .lc-ban-in { rotate: 0.8deg; }
        .lc-ban:nth-child(even):hover .lc-ban-in { rotate: -0.8deg; }
        .lc-ban-no { font-weight: 700; font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--lc-amber-ink); }
        .lc-ban h3 { margin-top: 0.6rem; font-size: clamp(1.8rem, 3.2vw, 2.3rem); text-wrap: balance; }
        .lc-ban p { margin-top: 0.7rem; color: var(--lc-ink-2); font-size: 1rem; }
        .lc-ban a { margin-top: auto; padding-top: 1.1rem; align-self: flex-start; }
        .lc-ban a span { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; border-bottom: 2px solid var(--lc-amber); transition: gap 0.2s ease; }
        .lc-ban a:hover span { gap: 0.7rem; }
        .lc-ban a svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           Three steps: the cue stack. One grid, each cue a subgrid row.
           --------------------------------------------------------------- */
        .lc-cues { display: grid; grid-template-columns: auto minmax(0, 0.8fr) minmax(0, 1.5fr) auto; margin-top: clamp(2.5rem, 5vw, 4rem); border-bottom: 2px solid var(--lc-ink); }
        .lc-cue { display: grid; grid-template-columns: subgrid; grid-column: 1 / -1; align-items: center; column-gap: 1.75rem; padding-block: 1.5rem 1.4rem; border-top: 2px solid var(--lc-ink); }
        @supports not (grid-template-columns: subgrid) {
            .lc-cue { grid-template-columns: 3.2rem minmax(0, 0.8fr) minmax(0, 1.5fr) 4rem; }
        }
        .lc-cue h3 { font-size: clamp(1.8rem, 3.2vw, 2.4rem); }
        .lc-cue p { color: var(--lc-ink-2); }
        .lc-go {
            display: grid;
            place-items: center;
            width: 4rem;
            aspect-ratio: 1;
            border: 2px solid var(--lc-ink-3);
            border-radius: 6px;
            font-size: 1.5rem;
            color: var(--lc-ink-3);
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .lc-cue:first-child .lc-go,
        .lc-cue:hover .lc-go { background-color: #ffb000; border-color: #ffb000; color: #111214; }
        .lc-cues:hover .lc-cue:first-child:not(:hover) .lc-go { background-color: transparent; border-color: var(--lc-ink-3); color: var(--lc-ink-3); }
        .dark #lc .lc-cue:hover .lc-go { box-shadow: 0 0 1.6rem rgba(255, 176, 0, 0.45); }
        @media (max-width: 760px) {
            .lc-cues { grid-template-columns: auto minmax(0, 1fr); }
            .lc-cue { grid-template-columns: auto minmax(0, 1fr); column-gap: 1.1rem; row-gap: 0.5rem; align-items: start; }
            @supports (grid-template-columns: subgrid) { .lc-cue { grid-template-columns: subgrid; } }
            .lc-cue p { grid-column: 2; }
            .lc-go { display: none; }
        }

        /* ---------------------------------------------------------------
           Key features: the patch sheet. Related pages: stage doors.
           --------------------------------------------------------------- */
        .lc-two { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 4rem; align-items: start; }
        @media (min-width: 960px) { .lc-two { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .lc-two .lc-h2 { font-size: clamp(2.6rem, 5.4vw, 4.4rem); }
        .lc-chs { border-top: 2px solid var(--lc-ink); }
        .lc-ch {
            display: grid;
            grid-template-columns: 4.4rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 1rem;
            padding: 1.2rem 0.75rem 1.05rem;
            border-bottom: 2px solid var(--lc-ink);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .lc-ch:hover { background-color: #ffb000; color: #111214; }
        .lc-ch-no { font-weight: 700; font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--lc-amber-ink); }
        .lc-ch strong { display: block; font-family: var(--lc-display); font-weight: 700; font-size: 1.9rem; letter-spacing: 0.012em; text-transform: uppercase; line-height: 1; }
        .lc-ch small { display: block; margin-top: 0.3rem; font-size: 0.95rem; color: var(--lc-ink-2); }
        #lc .lc-ch:hover .lc-ch-no,
        #lc .lc-ch:hover small { color: #111214; }
        .lc-ch svg { width: 1.5rem; height: 1.5rem; transition: translate 0.2s ease; }
        .lc-ch:hover svg { translate: 0.3rem 0; }
        .lc-two-more { margin-top: 1.6rem; }
        .lc-doors { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.1rem; }
        @media (min-width: 900px) { .lc-doors { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .lc-door {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2.2rem;
            min-height: 9.5rem;
            padding: 1.1rem 1.1rem 1rem;
            background-color: #0a0b0d;
            color: #f3f1ea;
            border-radius: 3px;
            box-shadow: 0 0 0 3px #1b1d22, 0 0 0 4px #000;
            transition: box-shadow 0.25s ease;
        }
        .lc-door:hover { box-shadow: 0 0 0 3px #ffb000, 0 0 0 4px #000; }
        .lc-door .lc-led { font-size: clamp(1.7rem, 3.4vw, 2.3rem); overflow-wrap: anywhere; }
        .lc-door-go { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; font-size: 0.78rem; letter-spacing: 0.16em; text-transform: uppercase; color: #c9ccd3; transition: gap 0.2s ease; }
        .lc-door:hover .lc-door-go { gap: 0.75rem; color: #ffb000; }
        .lc-door-go svg { width: 1rem; height: 1rem; }
        .lc-doors-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 1.25rem; margin-bottom: 2.25rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the dress changes.
           --------------------------------------------------------------- */
        #lc .lc-plans > section { background-color: transparent; }
        #lc .lc-plans h2 { font-family: var(--lc-display); font-weight: 700; text-transform: uppercase; letter-spacing: 0.012em; line-height: 0.95; font-size: clamp(2.3rem, 5vw, 3.9rem); color: var(--lc-ink); }
        #lc .lc-plans h2 + p { color: var(--lc-ink-2); font-size: 1.0625rem; }
        #lc .lc-plans .grid > div { background-color: var(--lc-panel); border: 2px solid var(--lc-steel); border-radius: 3px; color: var(--lc-ink); }
        #lc .lc-plans .grid > div:nth-child(2) { border-color: var(--lc-ink); box-shadow: 0 -0.5rem 0 0 var(--lc-amber) inset; }
        #lc .lc-plans .grid > div:hover { box-shadow: 0 1.4rem 2.4rem -1.8rem rgba(0, 0, 0, 0.6); }
        #lc .lc-plans .grid > div:nth-child(2):hover { box-shadow: 0 -0.5rem 0 0 var(--lc-amber) inset, 0 1.4rem 2.4rem -1.8rem rgba(0, 0, 0, 0.6); }
        #lc .lc-plans .grid > div span,
        #lc .lc-plans .grid > div p,
        #lc .lc-plans .grid > div li { color: var(--lc-ink-2); }
        #lc .lc-plans .grid > div .text-3xl { font-family: var(--lc-display); font-weight: 700; font-size: 3.2rem; line-height: 1; color: var(--lc-ink); }
        #lc .lc-plans .grid > div .uppercase { color: var(--lc-ink); letter-spacing: 0.2em; }
        #lc .lc-plans .grid > div .rounded-full { background-color: #ffb000; color: #111214; border-radius: 2px; }
        #lc .lc-plans .grid > div svg { color: var(--lc-amber-ink); }
        #lc .lc-plans a.font-medium { color: var(--lc-ink); border-bottom: 2px solid var(--lc-amber); }
        #lc .lc-plans a.rounded-2xl { background: #ffb000; color: #111214; border: 2px solid #111214; border-radius: 3px; box-shadow: none; font-family: var(--lc-display); font-weight: 700; font-size: 1.25rem; letter-spacing: 0.05em; text-transform: uppercase; }
        .dark #lc .lc-plans a.rounded-2xl { border-color: #ffb000; }
        #lc .lc-plans a.rounded-2xl:hover { transform: none; background: #ffc640; box-shadow: 0 0 0 4px rgba(255, 176, 0, 0.3); }

        #lc .lc-keep > section { background-color: var(--lc-bg-2); border-top: 2px solid var(--lc-ink); }
        #lc .lc-keep h2 { font-family: var(--lc-display); font-weight: 700; text-transform: uppercase; font-size: clamp(2.1rem, 4vw, 3.1rem); line-height: 1; color: var(--lc-ink); }
        #lc .lc-keep p.uppercase { letter-spacing: 0.22em; color: var(--lc-amber-ink); }
        #lc .lc-keep .grid > a { background-color: var(--lc-panel); border: 2px solid var(--lc-steel); border-radius: 3px; }
        #lc .lc-keep .grid > a:hover { border-color: var(--lc-ink); box-shadow: 0 1.4rem 2.4rem -1.8rem rgba(0, 0, 0, 0.6); }
        #lc .lc-keep .grid > a > span:first-child { display: none; }
        #lc .lc-keep .grid > a h3 { color: var(--lc-ink); }
        #lc .lc-keep .grid > a p { color: var(--lc-ink-2); }
        #lc .lc-keep .grid > a > span:last-child,
        #lc .lc-keep a.self-start { color: var(--lc-amber-ink); }

        /* ---------------------------------------------------------------
           08 Questions
           --------------------------------------------------------------- */
        .lc-faq-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .lc-faq-grid { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
            .lc-faq-head { position: sticky; top: 6.5rem; }
        }
        .lc-faq-head .lc-h2 { font-size: clamp(2.6rem, 5.2vw, 4.4rem); }
        .lc-qa { border-top: 2px solid var(--lc-ink); }
        .lc-qa details { border-bottom: 2px solid var(--lc-ink); }
        .lc-qa summary { display: grid; grid-template-columns: 2.6rem minmax(0, 1fr) 1.5rem; align-items: start; gap: 0.9rem; padding: 1.3rem 0.25rem 1.2rem; cursor: pointer; }
        .lc-qa-no { padding-top: 0.15rem; font-size: 1.6rem; --lc-led-ink: var(--lc-amber-ink); }
        .lc-qa h3 { font-size: 1.2rem; font-weight: 700; line-height: 1.3; }
        .lc-qa summary i { position: relative; width: 1.5rem; height: 1.5rem; margin-top: 0.1rem; }
        .lc-qa summary i::before,
        .lc-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background-color: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .lc-qa summary i::after { rotate: 90deg; }
        .lc-qa details[open] summary i::after { rotate: 0deg; }
        .lc-qa details p { padding: 0 0.25rem 1.6rem 3.75rem; max-width: 47rem; color: var(--lc-ink-2); }
        @media (max-width: 560px) { .lc-qa details p { padding-inline-start: 0.25rem; } }

        /* ---------------------------------------------------------------
           The show: the rig again, lit in both modes, and the wall
           spelling whatever name is typed.
           --------------------------------------------------------------- */
        .lc-show { position: relative; isolation: isolate; overflow: clip; padding-top: 7.2rem; scroll-margin-top: 4rem; background-image: radial-gradient(120% 60% at 50% 0%, #191b21 0%, #08090b 58%, #050506 100%); }
        .lc-show-in { position: relative; text-align: center; }
        .lc-show .lc-wall { --lc-cols: 6; --lc-rows: 2; max-width: 62rem; margin-inline: auto; }
        .lc-show .lc-screen { --lc-pitch: 6px; }
        .lc-show .lc-wall-lit { padding: 2.4rem 2.5cqi 2.1rem; }
        .lc-show .lc-line { --lc-fit: 13.2cqi; }
        .lc-show-k { position: relative; z-index: 2; margin-bottom: 1.4rem; color: #f3d38a; }
        .lc-show-sub { position: relative; z-index: 2; max-width: 40rem; margin: 2rem auto 0; font-size: 1.2rem; color: #d7d9de; }
        .lc-claimbox { position: relative; z-index: 2; width: min(100%, 44rem); margin: 2.25rem auto 0; }
        .lc-claimrow { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; }
        @media (min-width: 680px) { .lc-claimrow { grid-template-columns: minmax(0, 1fr) auto; } }
        .lc-claim-label { display: block; margin-bottom: 0.6rem; text-align: start; color: #b9bcc4; }
        #lc .lc-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.1rem;
            background-color: #0a0b0d;
            border: 2px solid #5a5f6a;
            border-radius: 3px;
            font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
            font-weight: 700;
            /* Never under 16px: iOS zooms the page when a smaller field takes focus. */
            font-size: clamp(1rem, 3.2vw, 1.1rem);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        #lc .lc-claim:focus-within { border-color: #ffb000; box-shadow: 0 0 0 4px rgba(255, 176, 0, 0.25); }
        #lc .lc-claim input { flex: 1; min-width: 0; border: 0; background-color: transparent; padding-inline: 0; text-align: right; font: inherit; color: #f3f1ea; box-shadow: none; outline: none; }
        #lc .lc-claim input::placeholder { color: #8d919c; }
        .lc-claim span { flex: none; color: #b9bcc4; user-select: none; }
        @media (max-width: 359px) { #lc .lc-claim span { font-size: 0.85rem; } }
        .lc-claim-note { margin-top: 1.1rem; font-size: 0.95rem; color: #b9bcc4; }
        /* The ticker along the wall's foot. */
        /* A long name takes the first row and the address drops to a second: on a phone the two
           on one row were wider than the ticker, which cut the name at both ends. */
        .lc-ticker { position: relative; z-index: 2; display: flex; flex-wrap: wrap; justify-content: center; row-gap: 0.3rem; max-width: 62rem; margin: 0.9rem auto 0; padding: 0.7rem 1rem 0.55rem; overflow: hidden; background-color: #0a0b0d; border-radius: 3px; box-shadow: 0 0 0 3px #1b1d22, 0 0 0 4px #000; white-space: nowrap; }
        .lc-ticker .lc-led { font-size: clamp(1.25rem, 4.4vw, 2rem); letter-spacing: 0.08em; }
        @media (max-width: 359px) { #lc .lc-ticker .lc-led { font-size: 1.1rem; } }
        .lc-ticker .lc-led + .lc-led { --lc-led-ink: #b9bcc4; }
        .lc-show .lc-floor { margin-top: clamp(2.5rem, 5vw, 4rem); }

        /* ---------------------------------------------------------------
           The cue rail: section nav on wide screens
           --------------------------------------------------------------- */
        /* The nav is a box the size of the page that clips the rail, so the rail stays fixed to the
           screen and still ends where the page does instead of riding over the site footer. */
        .lc-rail { display: none; }
        @media (min-width: 1500px) {
            .lc-rail { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .lc-rail ul { position: fixed; right: 1.1rem; top: 50%; translate: 0 -50%; display: grid; padding: 0.45rem 0.275rem; background-color: #0a0b0d; border-radius: 999px; box-shadow: 0 0 0 1px #2a2c33; pointer-events: auto; }
            .lc-rail a { position: relative; display: grid; place-items: center; width: 1.5rem; height: 1.5rem; }
            .lc-rail i { width: 0.45rem; height: 0.45rem; border-radius: 50%; background-color: #4a4e58; transition: background-color 0.25s ease, box-shadow 0.25s ease, scale 0.25s ease; }
            .lc-rail a:hover i { background-color: #c9ccd3; }
            .lc-rail a.is-active i { background-color: #ffb000; scale: 1.5; box-shadow: 0 0 0.6rem rgba(255, 176, 0, 0.9); }
            .lc-rail span { position: absolute; right: calc(100% + 0.9rem); top: 50%; translate: 0 -50%; padding: 0.3rem 0.55rem 0.22rem; background-color: #0a0b0d; color: #f3f1ea; font-weight: 700; font-size: 0.68rem; letter-spacing: 0.16em; text-transform: uppercase; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity 0.2s ease; }
            .lc-rail a:hover span,
            .lc-rail a:focus-visible span { opacity: 1; }
        }

        /* Phone: a finer pitch, a taller wall, the headline on four lines. */
        @media (max-width: 760px) {
            .lc-screen { --lc-pitch: 4px; }
            .lc-wall-lit { padding: 3.6rem 0.9rem 2.6rem; }
            .lc-line { --lc-fit: 15.4cqi; line-height: 1; }
            .lc-seg { display: block; }
            .lc-hero { padding-top: 5.6rem; }
            .lc-wall::before { height: 2.9rem; }
            .lc-show .lc-screen { --lc-pitch: 4px; }
            .lc-show .lc-wall-lit { padding: 2rem 0.9rem 1.8rem; }
            .lc-show .lc-line { --lc-fit: 16.4cqi; }
            .lc-wall { --lc-cols: 4; --lc-rows: 4; }
            .lc-show .lc-wall { --lc-cols: 4; --lc-rows: 4; }
            #lc .lc-screen > .es-hero-eyebrow { inset: 0.75rem auto auto 0.8rem; max-width: calc(100% - 1.6rem); }
            .lc-eyebrow { font-size: 0.62rem; letter-spacing: 0.16em; }
            .lc-wall-test span { bottom: 0.5rem; }
            /* The last cell is as wide as its label: at 0.6fr "Dates" was wider than its box. */
            .lc-board-cells { grid-template-columns: 1.7fr 1fr auto; }
            .lc-board-cells > div { padding-inline: 0.8rem; }
            .lc-board-cells dd { font-size: 1.55rem; }
        }

        html.es-anim #lc .lc-live.lc-off .lc-mh,
        html.es-anim #lc .lc-live.lc-off .lc-haze,
        html.es-anim #lc .lc-live.lc-off .lc-crowd i,
        html.es-anim #lc .lc-live.lc-off .lc-line,
        html.es-anim #lc .lc-live.lc-off .lc-hot-led { animation-play-state: paused; }

        @media (prefers-reduced-motion: reduce) {
            #lc .lc-mh,
            #lc .lc-haze,
            #lc .lc-blinder,
            #lc .lc-crowd i,
            #lc .lc-line,
            #lc .lc-hot-led,
            #lc .lc-wall-lit,
            #lc .lc-mh-beam,
            #lc .lc-hero::before { animation: none; }
            #lc .lc-btn,
            #lc .lc-dates tbody tr,
            #lc .lc-ban-in,
            #lc .lc-ch,
            #lc .lc-go,
            #lc .lc-door,
            #lc .lc-case-in { transition: none; }
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
                'a' => 'Yes. They are two named ticket types on the same date, so one can be "Standing" at thirty and the other "Livestream" at twelve, each with its own price, quantity and sales window. A ticket type with a price on it needs Pro, at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on the sale at every tier. The full stream link lives on the buyer\'s own ticket page: the public event page shows the room, or the domain you are streaming on when the date has no room at all.',
            ],
            [
                'q' => 'What streaming platforms does Event Schedule work with?',
                'a' => 'Any platform that gives you a URL: YouTube Live, Twitch, Instagram Live, Facebook Live, Vimeo, a custom RTMP front end. To be exact about what this is, it is one link field on the event rather than an integration - no accounts are connected and no viewer numbers come back. That is also why it never breaks when you change platforms.',
            ],
            [
                'q' => 'Is Event Schedule really free for streaming concerts?',
                'a' => 'Yes. Unlimited dates, the whole routing on one address, recurring residencies with date exceptions, sub-schedules, two-way Google, Outlook and CalDAV sync, the embeddable calendar, free registration with a capacity limit and no monthly ceiling, built-in analytics, ten newsletter emails a month (each recipient counts as one) and scanning a ticket at the door are all free forever. Selling a ticket that carries a price is '.plan_price($proMonthly).' a month on Pro, along with passes and the live check-in dashboard. There are zero platform fees on ticket sales at every tier, whether a date sells through your own Stripe or PayPal account, Invoice Ninja, a payment link or cash, so past the provider\'s own fee the money is yours.',
            ],
            [
                'q' => 'What happens when a date moves or gets pulled?',
                'a' => 'On a one-off date you change the date on the event, and saving asks whether to email its ticket buyers and anyone who asked to hear about that date; on eventschedule.com the buyers get it when your schedule sends through its own email settings. Cancelling a one-off date sends that email as part of cancelling. On a residency, a date exception takes a single night out of the pattern and guests simply see the day absent rather than crossed out, but it emails nobody, so tell that night\'s buyers yourself. Refunds go out from the Sales page: a Stripe or PayPal sale goes back through the provider, in full or in part, and only a full refund returns the ticket to stock. Being straight with you: there is no conflict detection anywhere in Event Schedule, so nothing will warn you that you have booked two shows on the same night. The routing table is where you catch that, which is why it is the first thing on this page.',
            ],
            [
                'q' => 'Can fans get an email when tickets go on sale?',
                'a' => 'Yes, on every plan. Switch on the "Notify me" card, put a date up before its tickets are on sale, and its page offers "Tell me when tickets go on sale": a fan leaves an email address, with no account and no name, and gets one email when tickets for that date open, a reminder 48 hours before it starts, word if it is cancelled, and any change notice you choose to send. Once the date is selling, the same list sits beside the buy button as "Tell me if anything changes". Each night of a residency keeps its own list, it is not a subscription to your schedule, it never counts against your newsletter allowance, and the event editor\'s Tickets panel shows how many people are waiting.',
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

    @php
        $lcArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $lcDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="lc">

    <!-- ============================================================ -->
    <!-- 1. Hero: the rig, the wall, the floor                        -->
    <!-- ============================================================ -->
    <section id="top" class="lc-hero lc-live">
        <div class="lc-wrap lc-hero-in">
            <div class="lc-wall es-fade-up es-d-1">
                <h1 class="lc-screen">
                    <x-marketing.hero-eyebrow class="lc-eyebrow">
                        <span>Event schedule for live concerts and touring shows</span>
                    </x-marketing.hero-eyebrow>
                    <span class="lc-wall-glow"><span class="lc-wall-lit">
                        <span class="lc-line"><span class="lc-seg">A tour is not</span> <span class="lc-seg">one event.</span></span>
                        <span class="lc-line"><span class="lc-seg">It is <span class="lc-hot-led">nine rooms</span></span> <span class="lc-seg">in a row.</span></span>
                    </span></span>
                </h1>
                <div class="lc-wall-test" aria-hidden="true">
                    <span class="lc-day">Test pattern</span>
                    <span class="lc-day">Lamps parked</span>
                    <span class="lc-nite">Show file</span>
                    <span class="lc-nite">Cue 01</span>
                </div>
            </div>

            <div class="lc-deck">
                <div>
                    <p class="lc-lede es-fade-up es-d-2">
                        Every live concert on the run has its own room, its own door time and its own on-sale. Put the whole routing up once, sell every night from a single address, and keep the takings: Event Schedule charges zero platform fees on ticket sales.
                    </p>

                    <div class="lc-cta es-fade-up es-d-3">
                        <a href="#routing" class="lc-btn lc-btn-ghost">
                            See the routing
                            {!! $lcDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="lc-btn">
                            Create your concert schedule
                            {!! $lcArrow !!}
                        </a>
                    </div>
                </div>

                <!-- The house board. Same physical object in both colour modes. -->
                <div class="es-fade-up es-d-4">
                    <div class="lc-board">
                        <div class="lc-board-top">
                            <span class="lc-board-on">Live on stage</span>
                            <span>Autumn routing</span>
                        </div>
                        <div class="lc-led lc-board-name">The Lantern Hours</div>
                        <dl class="lc-board-cells">
                            <div>
                                <dt>Tonight</dt>
                                <dd class="lc-led">Kings Hall</dd>
                            </div>
                            <div>
                                <dt>Doors</dt>
                                <dd class="lc-led">18:30</dd>
                            </div>
                            <div>
                                <dt>Dates</dt>
                                <dd class="lc-led"><span data-count-to="9">9</span></dd>
                            </div>
                        </dl>
                    </div>
                    <p class="lc-board-cap">
                        One schedule, one address. The nine dates behind this sign are nine events, each with its own room.
                    </p>
                </div>
            </div>
        </div>

        <div class="lc-rig" aria-hidden="true">
            <div class="lc-truss"></div>
            @for ($lamp = 1; $lamp <= 5; $lamp++)
                <div class="lc-mh lc-mh-{{ $lamp }}"><i class="lc-mh-beam"></i><i class="lc-mh-yoke"></i><i class="lc-mh-head"></i></div>
            @endfor
            <div class="lc-haze"></div>
            <div class="lc-blinder"></div>
        </div>

        <!-- Show-type marquee -->
        <div class="lc-types es-fade-up es-d-4">
            <div class="es-marquee" data-marquee="1">
                <div class="es-marquee-track">
                    @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                        @foreach (['Club tours', 'Album release shows', 'Support runs', 'Festival sets', 'Residencies', 'Acoustic sets', 'Jazz nights', 'DJ sets', 'All-dayers', 'Streamed shows'] as $chip)
                            <span @if ($chipCopy === 1) aria-hidden="true" @endif class="lc-d lc-type">{{ $chip }}</span>
                        @endforeach
                    @endfor
                </div>
            </div>
        </div>

        <div class="lc-floor" aria-hidden="true">
            <div class="lc-tape"></div>
            <div class="lc-crowd"><i></i><i></i><i></i><i></i></div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The routing: a record, so a real table                    -->
    <!-- ============================================================ -->
    <section id="routing" class="lc-sec">
        <div class="lc-wrap">
            <div class="lc-head">
                <div class="lc-head-row" data-reveal>
                    <span class="lc-num" aria-hidden="true"><span class="lc-led">02</span></span>
                    <p class="lc-k">The routing</p>
                </div>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                    The back of the poster is <span class="lc-hot">the whole job.</span>
                </h2>
                <p class="lc-sub" data-reveal style="--reveal-delay: 0.16s;">
                    A routing is a record, so it is a table here too. Nine dates, nine rooms, nine door times, and one public address that carries all of them.
                </p>
            </div>

            <div class="lc-route">
                <div data-reveal="panel">
                    <div class="lc-lam">
                        <div class="lc-lam-card">
                            <div class="lc-lam-top" aria-hidden="true">
                                <span class="lc-led">The Lantern Hours</span>
                                <small>Back of pass &middot; All dates</small>
                            </div>
                            <table class="lc-dates" role="table">
                                <caption class="lc-k">Autumn routing: date, room, doors and where the tickets stand</caption>
                                <thead role="rowgroup">
                                    <tr role="row">
                                        <th scope="col" role="columnheader" class="lc-k">Date</th>
                                        <th scope="col" role="columnheader" class="lc-k">Room</th>
                                        <th scope="col" role="columnheader" class="lc-k">Doors</th>
                                        <th scope="col" role="columnheader" class="lc-k">Tickets</th>
                                    </tr>
                                </thead>
                                <tbody role="rowgroup">
                                    @foreach ($routing as [$rDay, $rDate, $rRoom, $rCity, $rDoors, $rState, $rLabel])
                                        <tr role="row" @class(['lc-now' => $rRoom === 'Kings Hall'])>
                                            <td role="cell" class="lc-d lc-date-d"><small>{{ $rDay }} </small>{{ $rDate }}</td>
                                            <th scope="row" role="rowheader" class="lc-date-room">
                                                <span class="lc-date-pair">
                                                    <span class="lc-date-hall">{{ $rRoom }}</span>
                                                    <span class="lc-d lc-date-city">{{ $rCity }}</span>
                                                    @if ($rRoom === 'Kings Hall')
                                                        <span class="lc-now-tag" aria-hidden="true">Tonight</span>
                                                    @endif
                                                </span>
                                            </th>
                                            <td role="cell" class="lc-d lc-date-doors">{{ $rDoors }}</td>
                                            <td role="cell">
                                                <span class="lc-st @if ($rState === 'live') lc-st-live @elseif ($rState === 'gone') lc-st-gone @else lc-st-soon @endif">{{ $rLabel }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="lc-notes" data-reveal-group="100">
                    <div class="lc-note" data-reveal>
                        <p class="lc-k">The room</p>
                        <p>Each date carries its own venue, and a claimed room's name on your event page links to their schedule.</p>
                    </div>
                    <div class="lc-note" data-reveal>
                        <p class="lc-k">The stock</p>
                        <p>Ticket quantity is counted per date, so Manchester selling out has nothing to do with Glasgow.</p>
                    </div>
                    <div class="lc-note" data-reveal>
                        <p class="lc-k">The window</p>
                        <p>A ticket type's sales window is one start and one end, which is exactly right for a single dated show. Until it opens, and with the "Notify me" card switched on, fans can leave an email address on that date and get one email when it does.</p>
                    </div>
                </div>
            </div>

            <div class="lc-stats" data-reveal-group="90">
                <div class="lc-stat" data-reveal="panel">
                    <div class="lc-led">{{ plan_price(0) }}</div>
                    <p>Platform fees on every ticket you sell, on every plan. Your own Stripe or PayPal account, your money.</p>
                </div>
                <div class="lc-stat" data-reveal="panel">
                    <div class="lc-led">{{ plan_price($proMonthly) }}</div>
                    <p>A month for Pro. Free registration and scanning the door are already free; Pro is what puts a price on a ticket, and adds passes and the check-in dashboard.</p>
                </div>
                <div class="lc-stat" data-reveal="panel">
                    <div class="lc-led">10</div>
                    <p>Newsletter emails a month on the free plan, counted per recipient. Pro is 100, Enterprise 1,000.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The day sheet: proportional, because length is the point  -->
    <!-- ============================================================ -->
    <section id="doors" class="lc-sec lc-alt">
        <div class="lc-wrap lc-day-grid">
            <div>
                <div class="lc-head">
                    <div class="lc-head-row" data-reveal>
                        <span class="lc-num" aria-hidden="true"><span class="lc-led">03</span></span>
                        <p class="lc-k">The day sheet</p>
                    </div>
                    <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Doors at seven. On at <span class="lc-hot">twenty past eight.</span>
                    </h2>
                    <p class="lc-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Every date can carry a running order: named parts with a start and an end time, in order, published on the event page. The strip beside this is drawn from those times, which is why doors is a sliver and the headline set is most of the night.
                    </p>
                </div>
                <ul class="lc-ticks" data-reveal-group="90">
                    <li data-reveal>
                        <span>The support act stops being a rumour. People who came for the opener know when to be in the room.</span>
                    </li>
                    <li data-reveal>
                        <span>Times can be hidden if you would rather publish the order without committing to the clock.</span>
                    </li>
                    <li data-reveal>
                        <span>
                            <span>Running orders are free. Scanning a photo of the day sheet to fill one in is Enterprise.</span>
                            <span class="lc-tier lc-tier-ent">Enterprise</span>
                        </span>
                    </li>
                </ul>
            </div>

            <div>
                <!-- The stage clock: three times read off the same parts -->
                <div class="lc-clock" aria-hidden="true" data-reveal>
                    <div><span>Doors</span><b class="lc-led">{{ $clock($dayParts[0][2]) }}</b></div>
                    <div><span>On stage</span><b class="lc-led">{{ $clock($dayParts[3][2]) }}</b></div>
                    <div><span>Off stage</span><b class="lc-led">{{ $clock($dayParts[4][3]) }}</b></div>
                </div>

                <div data-reveal="panel">
                    <div class="lc-sheet">
                        <div class="lc-sheet-top">
                            <h3 class="lc-d">Kings Hall, London</h3>
                            <span>Sun Sep 20</span>
                        </div>

                        {{-- The day sheet. One row per event part; the bar behind each
                             row is its real duration over the longest part's, so the
                             headline set fills its row and the changeover is a stub.
                             The strip above the rows is the same five parts end to
                             end, each as wide as it is long. --}}
                        <p class="lc-sheet-k">Day sheet</p>
                        <div class="lc-strip" aria-hidden="true">
                            @foreach ($dayParts as [$pName, $pNote, $pFrom, $pTo, $pSet])
                                <i @class(['is-set' => $pSet]) style="flex-grow: {{ $pTo - $pFrom }};"></i>
                            @endforeach
                        </div>
                        <ul class="lc-parts">
                            @foreach ($dayParts as [$pName, $pNote, $pFrom, $pTo, $pSet])
                                <li class="lc-part @if ($pSet) is-set @endif">
                                    <span class="lc-part-fill"
                                          style="width: {{ round((($pTo - $pFrom) / $longestPart) * 100, 2) }}%;"
                                          aria-hidden="true"></span>
                                    <span class="lc-d lc-part-t">{{ $clock($pFrom) }}</span>
                                    <span>
                                        <span class="lc-part-n">{{ $pName }}</span>
                                        <span class="lc-part-note">{{ $pNote }}</span>
                                    </span>
                                    <span class="lc-part-len">{{ $spanLabel($pTo - $pFrom) }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="lc-sheet-cap">
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
    <section id="onsale" class="lc-sec">
        <div class="lc-wrap">
            <div class="lc-head">
                <div class="lc-head-row" data-reveal>
                    <span class="lc-num" aria-hidden="true"><span class="lc-led">04</span></span>
                    <p class="lc-k">On sale</p>
                </div>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Name the tickets. <span class="lc-hot">Keep the door.</span>
                </h2>
                <p class="lc-sub" data-reveal style="--reveal-delay: 0.16s; max-width: 52rem;">
                    Sales run through your own <a href="{{ marketing_url('/stripe') }}" class="lc-a">Stripe</a> or <a href="{{ marketing_url('/paypal') }}" class="lc-a">PayPal</a> account, or Invoice Ninja, a payment link or cash, and Event Schedule takes nothing from them. Being clear about the shape of it: these are named ticket types with prices and quantities, sold by the number. A reserved-seating room is an Enterprise thing, where you draw the venue once and the buyer picks their own seat off it.
                </p>
            </div>

            <div class="lc-office">
                <div class="lc-memos" data-reveal-group="80">
                    <div class="lc-memo" data-reveal>
                        <div class="lc-memo-top">
                            <h3 class="lc-d">Tiers that open and close</h3>
                            <span class="lc-tier lc-tier-pro">Pro</span>
                        </div>
                        <p>Each type gets a price, a quantity, a maximum per order and a sales window, so an early-bird allocation stops on its own. A type set at no charge goes out on any plan; a price on one is Pro.</p>
                    </div>
                    <div class="lc-memo" data-reveal>
                        <div class="lc-memo-top">
                            <h3 class="lc-d">Scanned on the way in</h3>
                            <span class="lc-tier">Free</span>
                        </div>
                        {{-- The wallet clause only where the install can issue a pass: GoogleWalletService::isConfigured() gates the button itself. --}}
                        <p>Every ticket carries a QR code, scanned from any phone on any plan{{ \App\Services\Wallet\GoogleWalletService::isConfigured() ? ', and buyers can save it to Google Wallet, where it scans the same way' : '' }}. Pro adds the check-in dashboard that breaks the running count down by ticket type.</p>
                    </div>
                    <div class="lc-memo" data-reveal>
                        <div class="lc-memo-top">
                            <h3 class="lc-d">Once a night is gone</h3>
                            <span class="lc-tier lc-tier-pro">Pro</span>
                        </div>
                        <p>Turn the waitlist on and people join for that date. If a return comes back, they are notified without you doing anything.</p>
                    </div>
                    <div class="lc-memo" data-reveal>
                        <div class="lc-memo-top">
                            <h3 class="lc-d">A free show, capped</h3>
                            <span class="lc-tier">Free</span>
                        </div>
                        <p>Registration with a capacity limit works on every plan, and the remaining count is tracked for each date of a residency separately.</p>
                    </div>
                    <div class="lc-memo" data-reveal>
                        <div class="lc-memo-top">
                            <h3 class="lc-d">Codes for the people you want back</h3>
                            <span class="lc-tier lc-tier-pro">Pro</span>
                        </div>
                        <p>Percentage or fixed discount codes with usage limits and an expiry. The volume rate that drops the price once somebody buys several at once sits on the ticket instead, and that part is free.</p>
                    </div>
                    <div class="lc-memo" data-reveal>
                        <div class="lc-memo-top">
                            <h3 class="lc-d">One ticket each</h3>
                            <span class="lc-tier lc-tier-pro">Pro</span>
                        </div>
                        <p>Per-attendee tickets give everyone in a party their own confirmation and their own code, instead of one person holding six.</p>
                    </div>
                </div>

                <div data-reveal-group="90">
                    <div class="lc-passsheet" data-reveal="panel">
                        <div class="lc-passsheet-top">
                            <h3 class="lc-d">Kings Hall, London</h3>
                            <span class="lc-stream-tag">Streamed too</span>
                        </div>
                        <div class="lc-passes">
                            @foreach ([['Standing', 'Doors 18:30', '$30', '412 of 500'], ['Balcony', 'Seated, limited', '$38', '96 of 100'], ['Livestream', 'Link on your ticket', '$12', 'No cap'], ['Residency pass', 'Every date, once each', '$85', '38 sold']] as [$tName, $tNote, $tPrice, $tStock])
                                <div class="lc-pass lc-pass-{{ $loop->index }}">
                                    <span class="lc-d lc-pass-name">{{ $tName }}</span>
                                    <span class="lc-pass-note">{{ $tNote }}</span>
                                    <span class="lc-pass-stock">{{ $tStock }}</span>
                                    <span class="lc-d lc-pass-price">{{ $tPrice }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="lc-fee">
                            <span>Platform fee</span>
                            <span class="lc-led">{{ plan_price(0) }}</span>
                        </div>
                        <p class="lc-passsheet-note">
                            Quantities are held per date. Read the detail on
                            <a href="{{ marketing_url('/features/ticketing') }}" class="lc-a">ticketing</a>.
                        </p>
                    </div>

                    <div class="lc-memo lc-memo-wide" data-reveal>
                        <div class="lc-memo-top">
                            <h3 class="lc-d">A pass for the whole run</h3>
                            <span class="lc-tier lc-tier-pro">Pro</span>
                        </div>
                        <p>A pass is a ticket type that stays valid across dates: a residency pass, a festival wristband, a members' pass. Set how many uses it has, how many it admits per event, and a cancellation deadline. It sells alongside single tickets rather than instead of them.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. One address: front of house, lights down in both modes    -->
    <!-- ============================================================ -->
    <section id="frontage" class="lc-sec lc-night lc-foh lc-live">
        <div class="lc-wrap">
            <div class="lc-head lc-head-mid">
                <div class="lc-head-row" data-reveal>
                    <span class="lc-num" aria-hidden="true"><span class="lc-led">05</span></span>
                    <p class="lc-k">One address</p>
                </div>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                    The list is yours. <span class="lc-hot">Not a platform's.</span>
                </h2>
                <p class="lc-sub" data-reveal style="--reveal-delay: 0.16s;">
                    People follow the schedule, you can see their name and email, and you write to them yourself. No algorithm sits between the announcement and the person who wanted it.
                </p>
            </div>

            <div class="lc-foh-cards" data-reveal-group="100">
                <div class="lc-foh-card" data-reveal="panel">
                    <div class="lc-memo-top">
                        <h3 class="lc-d">Follow at the merch table</h3>
                        <span class="lc-tier">Free</span>
                    </div>
                    <p>Every schedule has a downloadable QR code that points at your public page. Print it, tape it to the merch box, and the room signs itself up.</p>
                </div>
                <div class="lc-foh-card" data-reveal="panel">
                    <div class="lc-memo-top">
                        <h3 class="lc-d">Write to the right list</h3>
                        <span class="lc-tier">Free</span>
                    </div>
                    <p>Send to everyone, or to a segment: ticket buyers, the waitlist, one sub-schedule, a list you picked by hand. Open and click rates come back after.</p>
                </div>
                <div class="lc-foh-card" data-reveal="panel">
                    <div class="lc-memo-top">
                        <h3 class="lc-d">What sends itself, and what does not</h3>
                        <span class="lc-tier">Free</span>
                    </div>
                    <p>Worth knowing before you plan around it: fans who gave you their email on your schedule page get an automatic digest when you add dates, at most one every few days, and a fan who asked about one date hears when it goes on sale and again two days before. Account followers are not auto-notified, so reaching them means writing the email and pressing send. A moved date reaches its ticket buyers only if you send the notice offered on saving, a cancelled one as part of cancelling, and on eventschedule.com either way only when your schedule sends through its own email settings.</p>
                </div>
            </div>

            <p class="lc-foh-foot" data-reveal>
                Ten emails a month free, a hundred on Pro, a thousand on Enterprise, counted per recipient.
                <a href="{{ marketing_url('/features/newsletters') }}">
                    How newsletters work
                    {!! $lcArrow !!}
                </a>
            </p>
        </div>

        <div class="lc-floor" aria-hidden="true">
            <div class="lc-crowd"><i></i><i></i><i></i><i></i></div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Everything else a live concert needs: six road cases      -->
    <!-- ============================================================ -->
    <section id="rest" class="lc-sec">
        <div class="lc-wrap">
            <div class="lc-head">
                <div class="lc-head-row" data-reveal>
                    <span class="lc-num" aria-hidden="true"><span class="lc-led">06</span></span>
                    <p class="lc-k">Everything else</p>
                </div>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Everything else a live concert needs.
                </h2>
            </div>

            <div class="lc-cases" data-reveal-group="100">
                <!-- 1 -->
                <div class="lc-case" data-reveal="panel">
                    <div class="lc-case-in">
                        <div class="lc-case-top">
                            <h3 class="lc-d">On the site you already have</h3>
                            <span class="lc-tier">Free</span>
                            <span class="lc-case-no" aria-hidden="true">Case 1 of 6</span>
                        </div>
                        <p>Embed the calendar in your own site so the routing lives where people look you up, and sync two ways with Google, Outlook and CalDAV so the dates land in the calendar the crew actually reads.</p>
                        <p class="lc-fine">Any single date downloads as an .ics file, and a residency's individual dates do too, which is what a promoter forwards to a room's production manager. A residency syncs across as one entry, though: the subscribe feed is what unrolls every night of it, and fans can subscribe to it from your schedule page, with no email address, so a date that moves updates itself in their calendar.</p>
                    </div>
                </div>

                <!-- 2 -->
                <div class="lc-case" data-reveal="panel">
                    <div class="lc-case-in">
                        <div class="lc-case-top">
                            <h3 class="lc-d">Promoters asking for a date</h3>
                            <span class="lc-tier">Free</span>
                            <span class="lc-case-no" aria-hidden="true">Case 2 of 6</span>
                        </div>
                        <p>Turn requests on and whoever wants to book you fills in the night, the room and their own contact details. It lands on your requests tab and you get the email, rather than digging it out of six inboxes.</p>
                    </div>
                </div>

                <!-- 3 -->
                <div class="lc-case" data-reveal="panel">
                    <div class="lc-case-in">
                        <div class="lc-case-top">
                            <h3 class="lc-d">Announce when the deal is signed</h3>
                            <span class="lc-tier">Free</span>
                            <span class="lc-case-no" aria-hidden="true">Case 3 of 6</span>
                        </div>
                        <p>A date you have not announced sits as a draft: yours to see, never public until you say so. Sub-schedules keep the club tour and the festival dates on separate strands of the same address.</p>
                    </div>
                </div>

                <!-- 4 -->
                <div class="lc-case" data-reveal="panel">
                    <div class="lc-case-in">
                        <div class="lc-case-top">
                            <h3 class="lc-d">Which city is actually buying</h3>
                            <span class="lc-tier">Free</span>
                            <span class="lc-case-no" aria-hidden="true">Case 4 of 6</span>
                        </div>
                        <p>Built-in analytics count views per date and per device, sales and revenue per date, the countries the views came from, the referring domains, and the campaign tags on the links you posted. That is enough to route next autumn on evidence instead of memory.</p>
                        <p class="lc-fine">What it is not: it does not count who is watching a stream, and it imports no follower numbers from anywhere else. It measures your own pages.</p>
                    </div>
                </div>

                <!-- 5 -->
                <div class="lc-case" data-reveal="panel">
                    <div class="lc-case-in">
                        <div class="lc-case-top">
                            <h3 class="lc-d">The announce graphic</h3>
                            <span class="lc-tier">Free</span>
                            <span class="lc-case-no" aria-hidden="true">Case 5 of 6</span>
                        </div>
                        <p>Generate one share image of the dates coming up, in a story, square, portrait or landscape crop. It is built from the flyers already on those events, up to twenty of them, so a date with no flyer of its own sits this one out.</p>
                    </div>
                </div>

                <!-- 6: the streamed date. The chips carry real brand colours. -->
                <div class="lc-case" data-reveal="panel">
                    <div class="lc-case-in">
                        <div class="lc-case-split">
                            <div>
                                <div class="lc-case-top">
                                    <h3 class="lc-d">The night you also stream it</h3>
                                    <span class="lc-tier">Free</span>
                                    <span class="lc-case-no" aria-hidden="true">Case 6 of 6</span>
                                </div>
                                <p>Mark the date as an online event and paste the link to wherever the stream lives. Sell a livestream ticket type next to the standing ticket, and the link travels on the buyer's own ticket page.</p>
                                <p class="lc-fine">
                                    One link field, no accounts connected, nothing to break when you switch platforms.
                                    <a href="{{ marketing_url('/features/online-events') }}" class="lc-a">How online events work</a>
                                </p>
                            </div>
                            <div aria-hidden="true">
                                <div class="lc-patch">
                                    <p class="lc-k">Stream link</p>
                                    <div class="lc-patch-list">
                                        <div class="lc-chip bg-red-400/20 text-red-700 dark:text-red-300">YouTube Live</div>
                                        <div class="lc-chip bg-purple-400/20 text-purple-700 dark:text-purple-300">Twitch</div>
                                        <div class="lc-chip bg-pink-400/20 text-pink-700 dark:text-pink-300">Instagram Live</div>
                                    </div>
                                    <p class="lc-patch-note">Whichever you use, it is the same one field.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Perfect for: six banners flown from one truss             -->
    <!-- ============================================================ -->
    <section id="who" class="lc-sec lc-alt">
        <div class="lc-wrap">
            <div class="lc-head lc-head-mid">
                <div class="lc-head-row" data-reveal>
                    <span class="lc-num" aria-hidden="true"><span class="lc-led">07</span></span>
                </div>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Perfect for every <span class="lc-hot">genre and stage</span>
                </h2>
                <p class="lc-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Whether it is an intimate acoustic set or a festival stream, Event Schedule works for you. Also see <a href="{{ marketing_url('/for-musicians') }}" class="lc-a">Event Schedule for Musicians</a>.
                </p>
            </div>

            @php
                $lcStages = [
                    ['Solo Acoustic Artists', 'Intimate living room sessions and acoustic sets streamed to fans everywhere.', 'for-solo-acoustic-artists'],
                    ['Rock & Pop Bands', 'High-energy performances streamed from venues and studios to fans worldwide.', 'for-rock-pop-bands-live'],
                    ['Jazz & Blues Acts', 'Club sessions and late-night sets for a worldwide audience.', 'for-jazz-blues-acts'],
                    ['DJs & Electronic Artists', 'Live DJ sets, producer sessions, and festival streams for dance music fans.', 'for-djs-electronic-artists'],
                    ['Classical & Orchestra', 'Concert hall performances and recitals for remote audiences worldwide.', 'for-classical-orchestra'],
                    ['Cover & Tribute Bands', 'Fan-favorite shows streamed from bars and venues to audiences everywhere.', 'for-cover-tribute-bands-live'],
                ];
            @endphp

            <div class="lc-fly" data-reveal-group="70">
                @foreach ($lcStages as $lcStageIndex => [$lcStageName, $lcStageDesc, $lcStageSlug])
                    @php $lcStagePost = get_sub_audience_blog($lcStageSlug); @endphp
                    <article class="lc-ban" data-reveal>
                        <div class="lc-ban-in">
                            <span class="lc-ban-no" aria-hidden="true">Stage {{ chr(65 + $lcStageIndex) }}</span>
                            <h3 class="lc-d">{{ $lcStageName }}</h3>
                            <p>{{ $lcStageDesc }}</p>
                            @if ($lcStagePost)
                                <a href="{{ blog_url('/' . $lcStagePost->slug) }}" aria-label="Learn more about Event Schedule for {{ $lcStageName }}">
                                    <span>Learn more {!! $lcArrow !!}</span>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Three steps: the cue stack                                -->
    <!-- ============================================================ -->
    <section class="lc-sec">
        <div class="lc-wrap">
            <div class="lc-head">
                <p class="lc-k" data-reveal aria-hidden="true">Cue stack</p>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Three steps to the first date
                </h2>
            </div>

            <ol class="lc-cues" data-reveal-group="110">
                @foreach ([
                    ['01', 'Put the routing up', 'Add each date with its room and its door time. A weekly residency is one recurring event with a day-of-week pattern and date exceptions for the weeks you are out.'],
                    ['02', 'Set the running order', 'Doors, opener, changeover, headline. Named parts with start and end times, published on the event page in the order the night runs.'],
                    ['03', 'Open the sale', 'Connect Stripe or PayPal, name your ticket types, and give each one a price, a quantity and a window. Zero platform fees on what sells.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <li class="lc-cue" data-reveal>
                        <span class="lc-num"><span class="lc-led">{{ $stepNum }}</span></span>
                        <h3 class="lc-d">{{ $stepTitle }}</h3>
                        <p>{{ $stepBody }}</p>
                        <span class="lc-d lc-go" aria-hidden="true">Go</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features: the patch sheet                             -->
    <!-- ============================================================ -->
    <section class="lc-sec lc-alt">
        <div class="lc-wrap lc-two">
            <div>
                <p class="lc-k" data-reveal aria-hidden="true">Patch sheet</p>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.1rem;">Key features</h2>
                <p class="lc-two-more" data-reveal style="--reveal-delay: 0.16s;">
                    <a href="{{ marketing_url('/features') }}" class="lc-more lc-k">
                        See all features
                        {!! $lcArrow !!}
                    </a>
                </p>
            </div>

            @php
                $lcPatch = [
                    ['Ticketing', 'Named ticket types, QR check-in and zero platform fees', marketing_url('/features/ticketing')],
                    ['Recurring Events', 'Residencies with a day-of-week pattern and date exceptions', marketing_url('/features/recurring-events')],
                    ['Newsletters', 'Email the people who follow you, with open and click rates', marketing_url('/features/newsletters')],
                    ['Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', marketing_url('/features/calendar-sync')],
                ];
            @endphp
            <div class="lc-chs" data-reveal-group="70">
                @foreach ($lcPatch as $lcPatchIndex => [$lcPatchName, $lcPatchDesc, $lcPatchUrl])
                    <a href="{{ $lcPatchUrl }}" class="lc-ch" data-reveal>
                        <span class="lc-ch-no" aria-hidden="true">Ch {{ str_pad($lcPatchIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span>
                            <strong>{{ $lcPatchName }}</strong>
                            <small>{{ $lcPatchDesc }}</small>
                        </span>
                        {!! $lcArrow !!}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="lc-plans">
        @include('marketing.partials.pricing-nudge')
    </div>

    <!-- ============================================================ -->
    <!-- 10. Related pages: stage doors                               -->
    <!-- ============================================================ -->
    <section class="lc-sec lc-alt">
        <div class="lc-wrap">
            <div class="lc-doors-head">
                <div>
                    <p class="lc-k" data-reveal aria-hidden="true">Stage doors</p>
                    <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.1rem;">Related pages</h2>
                </div>
                <a href="{{ marketing_url('/use-cases') }}" class="lc-more lc-k" data-reveal>
                    See all use cases
                    {!! $lcArrow !!}
                </a>
            </div>
            <div class="lc-doors" data-reveal-group="80">
                @foreach ([['/for-musicians', 'Musicians'], ['/for-music-venues', 'Music Venues'], ['/for-djs', 'DJs'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="lc-door" data-reveal>
                        <span class="lc-led">For {{ $relName }}</span>
                        <span class="lc-door-go">
                            Read more
                            {!! $lcArrow !!}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="lc-sec">
        <div class="lc-wrap lc-faq-grid">
            <div class="lc-faq-head lc-head">
                <div class="lc-head-row" data-reveal>
                    <span class="lc-num" aria-hidden="true"><span class="lc-led">08</span></span>
                </div>
                <h2 class="lc-d lc-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Frequently asked questions
                </h2>
                <p class="lc-sub" data-reveal style="--reveal-delay: 0.16s;">
                    What promoters and tour managers ask before they move a routing across.
                </p>
            </div>

            <div class="lc-qa" data-reveal>
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq">
                        <summary>
                            <span class="lc-led lc-qa-no" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <h3>{{ $faq['q'] }}</h3>
                            <i aria-hidden="true"></i>
                        </summary>
                        <p>{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale: house lights down, in both modes                 -->
    <!-- ============================================================ -->
    <section id="claim" class="lc-show lc-night lc-live">
        <div class="lc-wrap lc-show-in">
            <p class="lc-k lc-show-k" data-reveal>Free forever</p>

            <div class="lc-wall" data-reveal="panel" id="lc-show-wall">
                <h2 class="lc-screen">
                    <span class="lc-wall-glow"><span class="lc-wall-lit">
                        <span class="lc-line"><span class="lc-seg">Put the</span> <span class="lc-seg">routing up.</span></span>
                        <span class="lc-line"><span class="lc-hot-led">Keep the door.</span></span>
                    </span></span>
                </h2>
            </div>
            <div class="lc-ticker" aria-hidden="true">
                <span class="lc-led" id="lc-mirror">your-band</span><span class="lc-led">.eventschedule.com</span>
            </div>

            <p class="lc-show-sub" data-reveal style="--reveal-delay: 0.1s;">
                Publishing the whole run is free forever, and so is scanning a ticket at the door. {{ plan_price($proMonthly) }} a month is what puts a price on one, and adds passes and the check-in dashboard. Nothing is taken from the sale on any plan.
            </p>

            <div class="lc-claimbox" data-reveal style="--reveal-delay: 0.18s;">
                <label for="es-claim-input" class="lc-k lc-claim-label">Your schedule name</label>
                <div class="lc-claimrow">
                    <div dir="ltr" class="es-claim lc-claim">
                        <input id="es-claim-input" type="text" placeholder="your-band" autocomplete="off" spellcheck="false" maxlength="30">
                        <span>.eventschedule.com</span>
                    </div>
                    <a href="{{ app_url('/sign_up?type=talent') }}" class="lc-btn">
                        Get started free
                        {!! $lcArrow !!}
                    </a>
                </div>
                <p class="lc-claim-note">No credit card required</p>
            </div>
        </div>

        <div class="lc-rig" aria-hidden="true">
            <div class="lc-truss"></div>
            @for ($lamp = 1; $lamp <= 5; $lamp++)
                <div class="lc-mh lc-mh-{{ $lamp }}"><i class="lc-mh-beam"></i><i class="lc-mh-yoke"></i><i class="lc-mh-head"></i></div>
            @endfor
            <div class="lc-haze"></div>
        </div>

        <div class="lc-floor" aria-hidden="true">
            <div class="lc-crowd"><i></i><i></i><i></i><i></i></div>
        </div>
    </section>

    <!-- The cue rail: section nav on wide screens -->
    <nav class="lc-rail es-dotnav" aria-label="Page sections">
        <ul>
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li>
                    <a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}">
                        <i aria-hidden="true"></i>
                        <span aria-hidden="true">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="lc-keep">
        <x-marketing.related-pages />
    </div>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- Three small things the shared engine does not do: rest the rig while it is off screen,
         spell the typed name on the wall's ticker, and fire the confetti cannons in the show's
         own colours rather than the site's blues. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            if (!('IntersectionObserver' in window)) {
                return;
            }

            var rest = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    entry.target.classList.toggle('lc-off', !entry.isIntersecting);
                });
            }, { rootMargin: '120px 0px' });
            document.querySelectorAll('#lc .lc-live').forEach(function (el) { rest.observe(el); });

            var input = document.getElementById('es-claim-input');
            var mirror = document.getElementById('lc-mirror');
            if (input && mirror) {
                input.addEventListener('input', function () {
                    window.requestAnimationFrame(function () {
                        mirror.textContent = input.value.replace(/-+$/, '') || input.getAttribute('placeholder');
                    });
                });
            }

            var wall = document.getElementById('lc-show-wall');
            if (!wall || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var cannons = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    cannons.disconnect();
                    var paper = ['#ffb000', '#f3f1ea', '#9be8ff', '#ffd980'];
                    {{-- The library's default cannon draws from a blob worker, which the site's
                         content policy refuses without throwing, so nothing was ever drawn.
                         One made here draws on the page instead. --}}
                    var cannon = typeof window.confetti.create === 'function' ? window.confetti.create(null, { resize: true }) : window.confetti;
                    [[60, 0.05], [120, 0.95]].forEach(function (shot) {
                        cannon({ particleCount: 90, angle: shot[0], spread: 62, startVelocity: 58, origin: { x: shot[1], y: 0.92 }, colors: paper, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.6 });
            cannons.observe(wall);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
