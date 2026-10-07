<x-marketing-layout>
    <x-slot name="title">Event Calendars for Bars and Pubs | Fill Every Night</x-slot>
    <x-slot name="description">Put your bar's week on one link: quiz nights, live music, karaoke, the match. Recurring nights that skip holidays, and tickets with zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Bars</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Bevan') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Playfair Display') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lobster Two') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lora') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Bars and Pubs"
        description="Put your bar's whole week on one link. Recurring quiz nights and live music, free registration, and zero platform fees on ticket sales."
        audience="Bars, Pubs and Taprooms"
        keywords="bar event calendar, pub quiz night schedule, live music calendar for bars, bar event management software, free pub event calendar, recurring bar events" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a bar's weekly event calendar online with Event Schedule",
        "description": "Get your bar's week online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Chalk up the week",
                "text": "Add each regular night once as a recurring event and set the day it lands on. Use sub-schedules to keep live music, quiz nights and sports apart on the same page."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Put the link on your door, your bio and your bookings page, or embed the calendar on the website you already have."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Keep the regulars posted",
                "text": "People follow your schedule and you email them directly when something changes or a new night goes up. No algorithm decides who finds out."
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
           For-bars "The Local" styles. The page is a proper pub as its
           signwriter and glazier left it: a painted fascia lettered in
           gold leaf, the week on the mirror behind the bar, seven glasses
           on the counter with Thursday's standing empty, the house list,
           a window of six panes, the honours board, beer mats on a
           mahogany bar top, brass table numbers, pump clips, and a second
           fascia waiting for a name.

           A cream bar front by day; the pub after dark by night. The
           painted, glazed and gilded objects (fascia, mirror, bar back,
           honours board, bar top) keep their own colours in both modes.
           Everything is scoped under #ba.
           ============================================================== */

        @property --ba-sheen {
            syntax: '<percentage>';
            inherits: false;
            initial-value: -30%;
        }
        @property --ba-pour {
            syntax: '<percentage>';
            inherits: true;
            initial-value: 0%;
        }

        #ba {
            --ba-ground: #f3e9d2;
            --ba-ground-2: #eadcbd;
            --ba-card: #fbf5e6;
            --ba-ink: #14261d;
            --ba-ink-2: #3b4a40;
            --ba-ink-3: #4f5c53;
            --ba-line: rgba(20, 38, 29, 0.24);
            --ba-accent: #6e1b1b;
            --ba-rule: #0e3b2c;
            --ba-focus: #6e1b1b;
            --ba-tile-a: #0e3b2c;
            --ba-tile-b: #f3e9d2;
            --ba-tile-c: #6e1b1b;
            --ba-display: 'Bevan', 'Rockwell', 'Georgia', serif;
            --ba-head: 'Playfair Display', 'Georgia', serif;
            --ba-script: 'Lobster Two', 'Brush Script MT', cursive;
            --ba-text: 'Lora', 'Georgia', serif;
            --ba-brass: linear-gradient(180deg, #f2dfa0 0%, #dcbc62 28%, #b88d28 52%, #cba84c 74%, #eed890 100%);
            --ba-brush: repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.07) 0 1px, rgba(60, 40, 0, 0.025) 1px 4px);
            position: relative;
            background: var(--ba-ground);
            color: var(--ba-ink);
            font-family: var(--ba-text);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #ba {
            --ba-ground: #07140f;
            --ba-ground-2: #0a1c15;
            --ba-card: #0f2a20;
            --ba-ink: #f3e9d2;
            --ba-ink-2: #d5cab0;
            --ba-ink-3: #a9a088;
            --ba-line: rgba(243, 233, 210, 0.2);
            --ba-accent: #e6c35a;
            --ba-rule: #c99a2e;
            --ba-focus: #f0d57c;
            --ba-tile-a: #0a2a20;
            --ba-tile-b: #b9ad8d;
            --ba-tile-c: #4d1414;
        }

        /* The bar above takes the paint of the wall it hangs on. */
        body > header.sticky {
            background-color: rgba(243, 233, 210, 0.9);
            border-bottom-color: rgba(20, 38, 29, 0.18);
        }
        .dark body > header.sticky {
            background-color: rgba(7, 20, 15, 0.9);
            border-bottom-color: rgba(231, 207, 138, 0.16);
        }

        #ba ::selection { background: #c99a2e; color: #14261d; }
        #ba a:focus-visible,
        #ba summary:focus-visible,
        #ba input:focus-visible {
            outline: 3px solid var(--ba-focus);
            outline-offset: 3px;
        }
        #ba .ba-dark a:focus-visible,
        #ba .ba-dark summary:focus-visible { outline-color: #f0d57c; }

        .ba-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
        .ba-section { padding-block: clamp(4.5rem, 9vw, 7.5rem); }
        .ba-alt { background: var(--ba-ground-2); }

        /* --------------------------------------------------------------
           Voices: the signwriter's slab, the script, the book hand
           -------------------------------------------------------------- */
        .ba-h2 {
            font-family: var(--ba-display);
            font-weight: 400;
            font-size: clamp(1.9rem, 4.5vw, 3.5rem);
            line-height: 1.1;
            text-wrap: balance;
            color: var(--ba-ink);
        }
        .ba-h2 .ba-turn { color: var(--ba-accent); }
        .ba-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            font-family: var(--ba-script);
            font-weight: 700;
            font-size: 1.6rem;
            line-height: 1;
            color: var(--ba-accent);
        }
        .ba-kicker::before,
        .ba-kicker::after {
            content: "";
            width: 2.25rem;
            height: 0.4rem;
            border-block: 1px solid currentColor;
            opacity: 0.7;
        }
        .ba-sub { max-width: 40rem; font-size: 1.15rem; color: var(--ba-ink-2); }
        .ba-head { display: grid; justify-items: center; gap: 1.15rem; text-align: center; margin-bottom: clamp(2.5rem, 5vw, 4rem); }

        /* Gold leaf: a gilded gradient cut to the letters, a drop shade
           beneath the way reverse-glass signs are shaded, and a sheen
           that crosses once as the lettering comes into view. */
        .ba-gilt {
            --ba-sheen: -30%;
            color: #e6c35a;
            background-image:
                linear-gradient(104deg, rgba(255, 252, 232, 0) calc(var(--ba-sheen) - 9%), rgba(255, 252, 232, 0.96) var(--ba-sheen), rgba(255, 252, 232, 0) calc(var(--ba-sheen) + 9%)),
                linear-gradient(180deg, #fcecaf 0%, #eccb62 36%, #cf9f30 60%, #b0841f 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
            filter: drop-shadow(0.03em 0.045em 0 rgba(3, 14, 10, 0.9));
        }
        .ba-gilt-live { animation: ba-sheen 9s ease-in-out 1.2s infinite; }
        @keyframes ba-sheen {
            0%, 58% { --ba-sheen: -30%; }
            100% { --ba-sheen: 130%; }
        }
        html.es-anim #ba [data-reveal] .ba-gilt:not(.ba-gilt-live) { transition: --ba-sheen 1.9s cubic-bezier(0.4, 0, 0.2, 1) 0.45s; }
        html.es-anim #ba [data-reveal].is-revealed .ba-gilt:not(.ba-gilt-live) { --ba-sheen: 130%; }

        /* Brass: plaques, plates and the buttons that are made of them. */
        .ba-plaque {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 0.34rem 1.25rem 0.3rem;
            font-family: var(--ba-head);
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            line-height: 1.3;
            white-space: nowrap;
            color: #2a1f05;
            text-shadow: 0 1px 0 rgba(255, 246, 205, 0.5);
            background-color: #d3b25e;
            background-image: var(--ba-brush), var(--ba-brass);
            border-radius: 2px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6), inset 0 -1px 0 rgba(80, 56, 6, 0.45), 0 1px 2px rgba(4, 19, 13, 0.45);
        }
        .ba-plaque::before,
        .ba-plaque::after,
        .ba-btn::before,
        .ba-btn::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 0.3rem;
            height: 0.3rem;
            margin-top: -0.15rem;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #fbefc0, #8a6410 62%, #3f2c04);
        }
        .ba-plaque::before { left: 0.4rem; }
        .ba-plaque::after { right: 0.4rem; }
        .ba-plaque-pro {
            color: #f3e9d2;
            text-shadow: none;
            background-color: #6e1b1b;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0) 45%, rgba(0, 0, 0, 0.16));
            box-shadow: inset 0 0 0 1px rgba(231, 207, 138, 0.75), 0 1px 2px rgba(4, 19, 13, 0.45);
        }

        .ba-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 1.1rem 2.5rem 1rem;
            font-family: var(--ba-head);
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.25;
            color: #2a1f05;
            text-shadow: 0 1px 0 rgba(255, 246, 205, 0.55);
            background-color: #d3b25e;
            background-image: linear-gradient(104deg, rgba(255, 255, 255, 0) 38%, rgba(255, 255, 255, 0.55) 50%, rgba(255, 255, 255, 0) 62%), var(--ba-brush), var(--ba-brass);
            background-size: 260% 100%, auto, auto;
            background-position: 130% 0, 0 0, 0 0;
            border-radius: 3px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65), inset 0 -2px 0 rgba(80, 56, 6, 0.4), 0 2px 0 #6b4d0c, 0 1rem 1.5rem -0.8rem rgba(4, 19, 13, 0.75);
            transition: translate 0.2s ease, background-position 0.9s ease, box-shadow 0.2s ease;
        }
        .ba-btn::before,
        .ba-btn::after { width: 0.5rem; height: 0.5rem; margin-top: -0.25rem; box-shadow: 0 1px 0 rgba(255, 255, 255, 0.45); }
        .ba-btn::before { left: 0.85rem; }
        .ba-btn::after { right: 0.85rem; }
        .ba-btn:hover { translate: 0 -2px; background-position: -40% 0, 0 0, 0 0; }
        .ba-btn svg { flex: none; width: 1.1rem; height: 1.1rem; transition: translate 0.2s ease; }
        .ba-btn:hover svg { translate: 0.25rem 0; }
        .ba-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--ba-head);
            font-weight: 700;
            font-size: 0.92rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ba-ink);
            padding-bottom: 0.3rem;
            box-shadow: 0 2px 0 #c99a2e;
            transition: gap 0.2s ease, box-shadow 0.2s ease;
        }
        .ba-link:hover { gap: 0.85rem; box-shadow: 0 4px 0 #c99a2e; }
        .ba-link svg { width: 1rem; height: 1rem; }

        /* --------------------------------------------------------------
           The brass studs down the margin, on wide screens only
           -------------------------------------------------------------- */
        /* From 1620px, where the margin beside the 76rem column is wider than the longest label
           (at 1560 the label of the current section lay over the edge of the cards). The nav is a
           box the size of the page that clips the rail, so the studs stay fixed to the screen and
           still end where the page does instead of riding over the site footer. */
        .ba-rail { display: none; }
        @media (min-width: 1620px) {
            .ba-rail { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .ba-rail ol { position: fixed; right: 1.25rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.5rem; justify-items: end; pointer-events: auto; }
            .ba-rail a { display: flex; flex-direction: row-reverse; align-items: center; gap: 0.6rem; padding: 0.1rem; }
            .ba-rail i {
                width: 0.7rem;
                height: 0.7rem;
                border-radius: 50%;
                background: radial-gradient(circle at 35% 30%, #fbefc0, #b88d28 55%, #5d4409);
                box-shadow: 0 1px 2px rgba(4, 19, 13, 0.55);
                transition: scale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            }
            .ba-rail span {
                padding: 0.25rem 0.7rem 0.2rem;
                font-family: var(--ba-head);
                font-weight: 700;
                font-size: 0.66rem;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                white-space: nowrap;
                background: #0e3b2c;
                color: #f3e9d2;
                box-shadow: inset 0 0 0 1px rgba(231, 207, 138, 0.55);
                opacity: 0;
                translate: 0.3rem 0;
                transition: opacity 0.25s ease, translate 0.25s ease;
            }
            .ba-rail a:hover span,
            .ba-rail a:focus-visible span,
            .ba-rail a.is-active span { opacity: 1; translate: 0 0; }
            .ba-rail a.is-active i { scale: 1.45; }
        }

        /* --------------------------------------------------------------
           1. The front: fascia, frieze, the mirror with the week on it
           -------------------------------------------------------------- */
        .ba-hero { position: relative; overflow: clip; padding-top: clamp(3rem, 5vw, 4.25rem); }
        .dark .ba-hero {
            background-image:
                radial-gradient(34rem 22rem at 26% 9rem, rgba(255, 196, 110, 0.13), rgba(255, 196, 110, 0) 70%),
                radial-gradient(34rem 22rem at 74% 9rem, rgba(255, 196, 110, 0.13), rgba(255, 196, 110, 0) 70%);
        }
        .ba-fascia {
            position: relative;
            padding: clamp(1.5rem, 3.4vw, 2.75rem) clamp(1rem, 3vw, 2.75rem) clamp(1.6rem, 3.6vw, 3rem);
            text-align: center;
            color: #f3e9d2;
            background-color: #0e3b2c;
            background-image:
                radial-gradient(46% 130% at 20% -22%, rgba(255, 220, 150, 0.2), rgba(255, 220, 150, 0) 72%),
                radial-gradient(46% 130% at 80% -22%, rgba(255, 220, 150, 0.2), rgba(255, 220, 150, 0) 72%),
                linear-gradient(180deg, #14503f 0%, #0e3b2c 24%, #0a2d22 100%);
            border: 3px solid #c99a2e;
            border-image: linear-gradient(180deg, #f7e08b, #c99a2e 45%, #8a6410) 1;
            box-shadow:
                inset 0 0 0 5px #0a2a20,
                inset 0 0 0 6px rgba(231, 207, 138, 0.7),
                0 0.4rem 0 #06170f,
                0 2rem 3rem -1.5rem rgba(4, 19, 13, 0.8);
        }
        .dark .ba-fascia {
            background-image:
                radial-gradient(46% 130% at 20% -22%, rgba(255, 220, 150, 0.36), rgba(255, 220, 150, 0) 72%),
                radial-gradient(46% 130% at 80% -22%, rgba(255, 220, 150, 0.36), rgba(255, 220, 150, 0) 72%),
                linear-gradient(180deg, #14503f 0%, #0e3b2c 24%, #0a2d22 100%);
        }
        /* The cornice the lamps hang from. */
        .ba-fascia::before {
            content: "";
            position: absolute;
            inset: auto -0.7rem calc(100% + 3px) -0.7rem;
            height: 0.95rem;
            background: linear-gradient(180deg, #1b5d49, #0c3327);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), inset 0 -3px 0 rgba(0, 0, 0, 0.3), 0 3px 5px rgba(4, 19, 13, 0.5);
        }
        .ba-lamp {
            position: absolute;
            left: var(--x, 20%);
            top: -0.55rem;
            width: 2.9rem;
            height: 1.45rem;
            margin-left: -1.45rem;
            border-radius: 2.9rem 2.9rem 0 0 / 1.45rem 1.45rem 0 0;
            background: linear-gradient(180deg, #33332e, #0a0a09);
            border-bottom: 3px solid #dcbc62;
            box-shadow: 0 0.5rem 1.4rem 0.2rem rgba(255, 214, 130, 0.28);
            z-index: 2;
        }
        .dark .ba-lamp { box-shadow: 0 0.6rem 2.2rem 0.6rem rgba(255, 214, 130, 0.5); }
        .ba-lamp::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: 100%;
            width: 4px;
            height: 0.75rem;
            margin-left: -2px;
            background: linear-gradient(90deg, #8a6410, #f2dfa0, #8a6410);
        }
        .ba-fascia-in { container-type: inline-size; }
        .ba-h1 { font-family: var(--ba-display); font-weight: 400; line-height: 1.06; }
        #ba .ba-h1 .es-mask { padding-block: 0.1em 0.24em; margin-block: -0.1em -0.24em; }
        #ba .ba-eyebrow {
            margin-bottom: clamp(0.9rem, 2vw, 1.5rem);
            font-size: clamp(0.62rem, 1.9vw, 0.74rem);
            white-space: normal;
        }
        .ba-sign {
            font-size: clamp(1.5rem, 4.08cqi, 4.3rem);
            letter-spacing: 0.03em;
            text-transform: uppercase;
            text-wrap: balance;
        }
        @container (min-width: 44rem) {
            .ba-sign { white-space: nowrap; }
        }
        .ba-sign-2 {
            display: inline-block;
            margin-top: 0.12em;
            font-family: var(--ba-script);
            font-weight: 700;
            font-size: clamp(2rem, 6.5cqi, 5.6rem);
            line-height: 1.05;
            color: #f3e9d2;
            text-shadow: 0.03em 0.045em 0 rgba(3, 14, 10, 0.9);
        }

        /* The frieze: every kind of house, lettered along the stall riser. */
        .ba-frieze {
            margin-top: 0.4rem;
            padding-block: 0.7rem 0.6rem;
            background: #0a2a20;
            color: #e6c35a;
            box-shadow: inset 0 1px 0 rgba(231, 207, 138, 0.5), inset 0 -1px 0 rgba(231, 207, 138, 0.5), 0 0.3rem 0 #06170f;
        }
        .ba-frieze .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .ba-frieze-item {
            display: inline-flex;
            align-items: center;
            gap: 1.4rem;
            padding-inline-end: 1.4rem;
            font-family: var(--ba-head);
            font-weight: 700;
            font-size: 0.84rem;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .ba-frieze-item::after { content: "\2726"; font-size: 0.8em; letter-spacing: 0; color: #f3e9d2; opacity: 0.8; }

        .ba-front {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: clamp(2.5rem, 5vw, 4.5rem);
            align-items: center;
            padding-block: clamp(2.75rem, 5vw, 4.25rem) clamp(3.5rem, 7vw, 6rem);
        }
        @media (min-width: 980px) {
            .ba-front { grid-template-columns: minmax(0, 1fr) minmax(0, 26.5rem); }
        }
        @media (min-width: 1180px) {
            .ba-front { grid-template-columns: 10.5rem minmax(0, 1fr) minmax(0, 26.5rem); gap: 3.25rem; }
        }
        .ba-lede { max-width: 35rem; font-size: clamp(1.18rem, 1.75vw, 1.42rem); line-height: 1.55; color: var(--ba-ink-2); }
        .ba-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem 2rem; margin-top: 2.25rem; }
        .ba-licence { display: flex; flex-wrap: wrap; gap: 0.7rem; margin-top: 2.25rem; }

        /* The hanging sign: an iron bracket, two chains, a board that
           moves a little in the wind. Wide screens only. */
        .ba-swing { display: none; }
        @media (min-width: 1180px) {
            .ba-licence { display: none; }
            .ba-swing {
                position: relative;
                display: block;
                align-self: start;
                padding-top: 2.3rem;
            }
            .ba-swing::before {
                content: "";
                position: absolute;
                inset: 0 -0.9rem auto -0.6rem;
                height: 0.6rem;
                border-radius: 0.3rem;
                background: linear-gradient(180deg, #4a4a44, #0c0c0b);
                box-shadow: 0 2px 3px rgba(4, 19, 13, 0.4);
            }
            .ba-swing-board {
                position: relative;
                display: grid;
                justify-items: center;
                gap: 0.2rem;
                padding: 1.5rem 0.75rem 1.4rem;
                text-align: center;
                color: #f3e9d2;
                background-color: #6e1b1b;
                background-image: linear-gradient(155deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0) 42%, rgba(0, 0, 0, 0.18));
                border-radius: 5rem 5rem 0.3rem 0.3rem / 2.4rem 2.4rem 0.3rem 0.3rem;
                box-shadow: inset 0 0 0 4px #521313, inset 0 0 0 5px rgba(231, 207, 138, 0.85), 0 1.6rem 2rem -1.2rem rgba(4, 19, 13, 0.7);
                transform-origin: 50% -1.7rem;
                animation: ba-swing 5.5s ease-in-out infinite alternate;
            }
            .ba-swing-board::before {
                content: "";
                position: absolute;
                inset: -1.7rem 24% 100% 24%;
                border-inline: 2px dotted #4a4a44;
            }
            .ba-swing-board small { font-family: var(--ba-script); font-weight: 700; font-size: 1.25rem; line-height: 1; color: #f0d57c; }
            .ba-swing-board b { font-family: var(--ba-display); font-weight: 400; font-size: 1.85rem; line-height: 1.02; letter-spacing: 0.03em; text-transform: uppercase; }
            .ba-swing-board i { margin-top: 0.5rem; font-family: var(--ba-head); font-style: normal; font-weight: 700; font-size: 0.6rem; letter-spacing: 0.2em; text-transform: uppercase; color: #e9dcc0; }
        }
        @keyframes ba-swing {
            from { rotate: -1.7deg; }
            to { rotate: 1.7deg; }
        }

        /* The mirror behind the bar: a gilt frame, scooped corners, the
           week lettered on the glass. */
        .ba-mirror {
            position: relative;
            padding: 0.8rem;
            background: linear-gradient(135deg, #f7e08b 0%, #c99a2e 22%, #8a6410 46%, #d9b655 68%, #f7e9a8 84%, #b88d28 100%);
            box-shadow: inset 0 0 0 1px rgba(60, 40, 0, 0.55), inset 0 0 0 4px rgba(255, 246, 205, 0.35), 0 2rem 3rem -1.6rem rgba(4, 19, 13, 0.75);
            rotate: 0.6deg;
        }
        @media (max-width: 979px) { .ba-mirror { width: min(100%, 31rem); margin-inline: auto; } }
        .ba-glass {
            --ba-scoop: 1.15rem;
            position: relative;
            padding: 1.7rem 1.5rem 1.35rem;
            color: #f3e9d2;
            background-color: #0f3327;
            background-image:
                linear-gradient(118deg, rgba(255, 255, 255, 0) 28%, rgba(255, 255, 255, 0.09) 40%, rgba(255, 255, 255, 0) 50%, rgba(255, 255, 255, 0) 66%, rgba(255, 255, 255, 0.05) 74%, rgba(255, 255, 255, 0) 82%),
                linear-gradient(180deg, #164634, #0b271e);
            -webkit-mask:
                radial-gradient(circle at 0 0, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) top left / 51% 51% no-repeat,
                radial-gradient(circle at 100% 0, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) top right / 51% 51% no-repeat,
                radial-gradient(circle at 0 100%, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) bottom left / 51% 51% no-repeat,
                radial-gradient(circle at 100% 100%, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) bottom right / 51% 51% no-repeat;
            mask:
                radial-gradient(circle at 0 0, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) top left / 51% 51% no-repeat,
                radial-gradient(circle at 100% 0, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) top right / 51% 51% no-repeat,
                radial-gradient(circle at 0 100%, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) bottom left / 51% 51% no-repeat,
                radial-gradient(circle at 100% 100%, transparent var(--ba-scoop), #000 calc(var(--ba-scoop) + 0.5px)) bottom right / 51% 51% no-repeat;
        }
        /* The etched rule inside the glass, broken at the scoops. */
        .ba-glass::before {
            content: "";
            position: absolute;
            inset: 0.5rem;
            border: 1px solid rgba(231, 207, 138, 0.5);
            pointer-events: none;
        }
        .ba-glass > * { position: relative; }
        .ba-week-title {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            margin-bottom: 0.9rem;
            font-family: var(--ba-script);
            font-weight: 700;
            font-size: 2.1rem;
            line-height: 1;
            color: #f0d57c;
        }
        .ba-week-title::before,
        .ba-week-title::after { content: ""; flex: 1; height: 0.4rem; border-block: 1px solid rgba(231, 207, 138, 0.6); }
        .ba-week-row {
            display: grid;
            grid-template-columns: 3rem minmax(0, 1fr) auto;
            align-items: baseline;
            gap: 0.75rem;
            padding-block: 0.62rem 0.55rem;
            border-bottom: 1px solid rgba(231, 207, 138, 0.2);
        }
        .ba-week-day { font-family: var(--ba-head); font-weight: 700; font-size: 0.72rem; letter-spacing: 0.2em; text-transform: uppercase; color: #e6c35a; }
        .ba-week-night { font-family: var(--ba-head); font-weight: 700; font-size: 1.14rem; line-height: 1.25; }
        .ba-week-note { font-size: 0.84rem; color: #cfc6ab; text-align: end; }
        .ba-week-row.is-lit .ba-week-night { color: #f0d57c; }
        .ba-week-row.is-open .ba-week-night { font-family: Georgia, 'Times New Roman', serif; font-weight: 400; font-style: italic; color: #cfc6ab; }
        .ba-week-open { padding: 0.12rem 0.6rem 0.08rem; border: 1px dashed rgba(240, 213, 124, 0.8); font-family: var(--ba-head); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.2em; text-transform: uppercase; color: #f0d57c; }
        .ba-week-foot { margin-top: 0.95rem; font-size: 0.84rem; line-height: 1.5; color: #cfc6ab; text-align: center; }

        /* The tiled threshold: encaustic triangles between two bands. */
        .ba-tiles {
            height: 3rem;
            background-color: var(--ba-tile-b);
            background-image:
                linear-gradient(var(--ba-tile-c), var(--ba-tile-c)),
                linear-gradient(var(--ba-tile-c), var(--ba-tile-c)),
                conic-gradient(from 45deg at 50% 50%, var(--ba-tile-a) 0 25%, var(--ba-tile-b) 0 50%, var(--ba-tile-a) 0 75%, var(--ba-tile-b) 0);
            background-size: 100% 6px, 100% 6px, 3rem 3rem;
            background-position: 0 0, 0 100%, 50% 50%;
            background-repeat: no-repeat, no-repeat, repeat-x;
        }

        /* --------------------------------------------------------------
           Bar-back bands: painted green in both modes
           -------------------------------------------------------------- */
        .ba-dark {
            position: relative;
            color: #f3e9d2;
            background-color: #0e3b2c;
            background-image:
                repeating-linear-gradient(45deg, rgba(231, 207, 138, 0.05) 0 1px, rgba(231, 207, 138, 0) 1px 28px),
                repeating-linear-gradient(-45deg, rgba(231, 207, 138, 0.05) 0 1px, rgba(231, 207, 138, 0) 1px 28px),
                radial-gradient(60rem 30rem at 50% 0%, rgba(255, 214, 130, 0.1), rgba(255, 214, 130, 0) 70%);
        }
        .ba-dark .ba-h2 { color: #f3e9d2; }
        @media (min-width: 900px) { .ba-dark .ba-head .ba-h2 .ba-gilt { display: block; } }
        .ba-dark .ba-kicker { color: #f0d57c; }
        .ba-dark .ba-sub { color: #cfc6ab; }
        .ba-dark .ba-link { color: #f3e9d2; }

        /* 2. Seven glasses on the counter. Thursday's is the empty one. */
        .ba-round {
            position: relative;
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: clamp(0.3rem, 1.8vw, 1.6rem);
            align-items: end;
            width: min(100%, 46rem);
            margin: 0 auto clamp(2.75rem, 5vw, 4rem);
            padding-inline: clamp(0.4rem, 3vw, 2rem);
        }
        .ba-round-item { display: grid; justify-items: center; gap: 1.5rem; }
        .ba-pint {
            --ba-fill: 80%;
            position: relative;
            width: 100%;
            max-width: 4.4rem;
            aspect-ratio: 9 / 14;
            clip-path: polygon(0 0, 100% 0, 85% 100%, 15% 100%);
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.3) 0 7%, rgba(255, 255, 255, 0.08) 20%, rgba(255, 255, 255, 0.03) 68%, rgba(255, 255, 255, 0.22) 90%, rgba(255, 255, 255, 0.1));
        }
        .ba-pint::before {
            content: "";
            position: absolute;
            inset: auto 0 5% 0;
            height: var(--ba-fill);
            background: linear-gradient(90deg, #a8620f, #e09a26 26%, #f6c256 50%, #c87f17 80%, #96570b);
        }
        .ba-pint::after {
            content: "";
            position: absolute;
            inset: auto 0 calc(5% + var(--ba-fill)) 0;
            height: calc(var(--ba-fill) * 0.17);
            background: linear-gradient(180deg, #fffaf0, #efe0ba);
            border-radius: 45% 45% 0 0 / 55% 55% 0 0;
        }
        .ba-pint-thu { --ba-fill: var(--ba-pour); }
        @supports (animation-timeline: view()) {
            html.es-anim #ba .ba-pint-thu {
                animation: ba-pour linear both;
                animation-timeline: view();
                animation-range: cover 46% cover 78%;
            }
        }
        @keyframes ba-pour {
            from { --ba-pour: 0%; }
            to { --ba-pour: 80%; }
        }
        .ba-round-day { font-family: var(--ba-head); font-weight: 700; font-size: clamp(0.56rem, 1.7vw, 0.74rem); letter-spacing: 0.18em; text-transform: uppercase; color: #e6c35a; }
        .ba-round-item.is-thu .ba-round-day { color: #f3e9d2; }
        /* The counter they stand on. */
        .ba-round::after {
            content: "";
            position: absolute;
            inset: auto 0 1.55rem 0;
            height: 0.85rem;
            background: linear-gradient(180deg, #6b3a22 0%, #3d1f12 34%, #2a140b 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 0.5rem 0.9rem -0.3rem rgba(3, 14, 10, 0.7);
            border-radius: 2px;
        }
        .ba-etches { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
        @media (min-width: 860px) { .ba-etches { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ba-etch {
            position: relative;
            padding: 1.7rem 1.5rem 1.6rem;
            background-color: rgba(243, 233, 210, 0.07);
            background-image: linear-gradient(122deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 26%, rgba(255, 255, 255, 0) 70%, rgba(255, 255, 255, 0.05) 100%);
            border: 1px solid rgba(231, 207, 138, 0.42);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14), inset 0 0 0 5px rgba(8, 36, 27, 0.4), inset 0 0 0 6px rgba(231, 207, 138, 0.2);
            -webkit-backdrop-filter: blur(6px);
            backdrop-filter: blur(6px);
        }
        .ba-etch-tag { font-family: var(--ba-head); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.2em; text-transform: uppercase; color: #e6c35a; }
        .ba-etch h3 { margin-top: 0.8rem; font-family: var(--ba-head); font-weight: 700; font-size: 1.4rem; line-height: 1.2; color: #f3e9d2; }
        .ba-etch h3 .ba-gilt { font-family: var(--ba-display); font-weight: 400; font-size: 2.6em; line-height: 0.9; margin-inline-end: 0.08em; }
        .ba-etch p { margin-top: 0.7rem; color: #cfc6ab; font-size: 1rem; }
        .ba-etch-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 0.8rem; }
        .ba-etch-row h3 { margin-top: 0; }
        .ba-dark-foot { margin-top: 2.75rem; text-align: center; color: #cfc6ab; }
        .ba-dark-foot .ba-link { margin-inline-start: 0.6rem; }

        /* --------------------------------------------------------------
           3. The house list: a bar menu, three lines and the small print
           -------------------------------------------------------------- */
        .ba-menu {
            position: relative;
            width: min(100%, 56rem);
            margin-inline: auto;
            padding: clamp(2.25rem, 5vw, 3.75rem) clamp(1.25rem, 5vw, 4.5rem) clamp(1.75rem, 4vw, 3rem);
            background: var(--ba-card);
            border: 2px solid var(--ba-rule);
            outline: 1px solid var(--ba-rule);
            outline-offset: -0.6rem;
            box-shadow: 0 1.75rem 2.75rem -1.9rem rgba(4, 19, 13, 0.6);
        }
        .ba-menu .ba-head { margin-bottom: 0.75rem; padding-bottom: clamp(1.5rem, 3vw, 2.25rem); border-bottom: 3px double var(--ba-rule); }
        .ba-menu .ba-h2 { font-size: clamp(1.8rem, 3.9vw, 3rem); }
        .ba-menu-item { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.4rem 1.25rem; padding-block: 1.5rem; }
        .ba-menu-item + .ba-menu-item { border-top: 1px solid var(--ba-line); }
        .ba-menu-no { grid-row: span 2; font-family: var(--ba-display); font-size: 2.5rem; line-height: 0.95; color: var(--ba-accent); }
        .ba-menu-line { display: flex; align-items: baseline; gap: 0.75rem; }
        .ba-menu-line h3 { font-family: var(--ba-head); font-weight: 700; font-size: clamp(1.2rem, 2.2vw, 1.5rem); line-height: 1.25; }
        .ba-menu-line i { flex: 1; min-width: 1rem; border-bottom: 2px dotted var(--ba-line); translate: 0 -0.3em; }
        .ba-menu-line .ba-plaque { align-self: center; }
        .ba-menu-item p { color: var(--ba-ink-2); }
        .ba-menu-small { margin-top: 0.5rem; padding-top: 1.5rem; border-top: 3px double var(--ba-rule); font-size: 0.97rem; color: var(--ba-ink-2); text-align: center; text-wrap: pretty; }
        @media (max-width: 560px) {
            .ba-menu-item { grid-template-columns: minmax(0, 1fr); }
            .ba-menu-no { grid-row: auto; font-size: 2rem; }
            .ba-menu-line { flex-wrap: wrap; }
            .ba-menu-line i { display: none; }
        }

        /* --------------------------------------------------------------
           4. Who's playing: the bookings book
           -------------------------------------------------------------- */
        .ba-two { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2.5rem, 5vw, 4.5rem); align-items: center; }
        @media (min-width: 980px) { .ba-two { grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr); } }
        .ba-two .ba-h2 { margin-top: 1.1rem; font-size: clamp(1.85rem, 3.8vw, 3rem); }
        .ba-two-lede { margin-top: 1.4rem; font-size: 1.15rem; color: var(--ba-ink-2); }
        .ba-two-lede strong { font-weight: 700; color: var(--ba-ink); }
        .ba-ticks { display: grid; gap: 0.95rem; margin-top: 1.75rem; }
        .ba-ticks li { position: relative; padding-inline-start: 1.9rem; color: var(--ba-ink-2); }
        .ba-ticks li::before {
            content: "";
            position: absolute;
            left: 0.15rem;
            top: 0.42em;
            width: 0.8rem;
            height: 0.8rem;
            rotate: 45deg;
            background-color: #c99a2e;
            background-image: linear-gradient(135deg, #f7e9a8, #b88d28 60%, #8a6410);
            box-shadow: 0 1px 2px rgba(4, 19, 13, 0.4);
        }
        .ba-book {
            position: relative;
            padding: 1.6rem 1.5rem 1.4rem 2.6rem;
            background-color: var(--ba-card);
            background-image: repeating-linear-gradient(180deg, rgba(14, 59, 44, 0) 0 2.35rem, rgba(14, 59, 44, 0.16) 2.35rem calc(2.35rem + 1px));
            border: 1px solid var(--ba-line);
            box-shadow: 0.5rem 0.5rem 0 -0.25rem var(--ba-card), 0.5rem 0.5rem 0 calc(-0.25rem + 1px) var(--ba-line), 1rem 1rem 0 -0.5rem var(--ba-card), 1rem 1rem 0 calc(-0.5rem + 1px) var(--ba-line), 0 2rem 2.8rem -1.8rem rgba(4, 19, 13, 0.6);
            rotate: -0.8deg;
        }
        /* The ledger's ruled margin, drawn in the book, not down a card. */
        .ba-book::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 1.7rem;
            width: 0.3rem;
            border-inline: 1px solid rgba(110, 27, 27, 0.45);
        }
        .dark .ba-book::before { border-color: rgba(231, 207, 138, 0.4); }
        .ba-book-head { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 3px double var(--ba-rule); }
        .ba-book-head span:first-child { font-family: var(--ba-script); font-weight: 700; font-size: 1.9rem; line-height: 1; color: var(--ba-accent); }
        .ba-book-head span:last-child { font-family: var(--ba-head); font-weight: 700; font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ba-ink-3); }
        .ba-book-row { display: flex; align-items: center; gap: 1rem; padding-block: 0.95rem; border-bottom: 1px solid var(--ba-line); }
        .ba-book-row div { flex: 1; min-width: 0; }
        .ba-book-row strong { display: block; font-family: var(--ba-head); font-weight: 700; font-size: 1.15rem; line-height: 1.25; }
        .ba-book-row small { display: block; font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 0.94rem; color: var(--ba-ink-3); }
        .ba-book-note { margin-top: 1rem; font-size: 0.92rem; color: var(--ba-ink-3); }

        /* --------------------------------------------------------------
           5. The regulars: the snug, and the card propped on the bar
           -------------------------------------------------------------- */
        .ba-snug { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2.5rem, 5vw, 4rem); align-items: center; }
        @media (min-width: 980px) { .ba-snug { grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr); } }
        .ba-snug-list { display: grid; gap: 1.1rem; }
        .ba-bill {
            position: relative;
            width: min(100%, 21rem);
            margin-inline: auto;
            padding: 1.5rem 1.35rem 1.2rem;
            color: #14261d;
            background-color: #fbf5e6;
            box-shadow: inset 0 0 0 1px rgba(20, 38, 29, 0.3), inset 0 0 0 0.45rem #fbf5e6, inset 0 0 0 calc(0.45rem + 1px) rgba(20, 38, 29, 0.55), 0 2rem 2.6rem -1.4rem rgba(3, 14, 10, 0.85);
            rotate: 1.8deg;
        }
        /* The brass pin it hangs from. */
        .ba-bill::before {
            content: "";
            position: absolute;
            left: 50%;
            top: -0.45rem;
            width: 0.95rem;
            height: 0.95rem;
            margin-left: -0.475rem;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #fbefc0, #b88d28 55%, #5d4409);
            box-shadow: 0 2px 3px rgba(3, 14, 10, 0.6);
        }
        .ba-bill-head { display: grid; justify-items: center; gap: 0.1rem; padding: 0.4rem 0 0.8rem; border-bottom: 3px double rgba(20, 38, 29, 0.7); text-align: center; }
        .ba-bill-head span:first-child { font-family: var(--ba-head); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.24em; text-transform: uppercase; color: #3b4a40; }
        .ba-bill-head span:last-child { font-family: var(--ba-display); font-size: 1.45rem; line-height: 1.15; color: #0e3b2c; }
        .ba-bill-row { display: grid; grid-template-columns: 2.6rem minmax(0, 1fr) auto; align-items: baseline; gap: 0.6rem; padding: 0.5rem 0.45rem 0.42rem; border-bottom: 1px solid rgba(20, 38, 29, 0.16); font-size: 0.98rem; }
        .ba-bill-row span:first-child { font-family: var(--ba-head); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.18em; text-transform: uppercase; color: #3b4a40; }
        .ba-bill-row.is-new { background: #f0dc9a; font-weight: 700; }
        .ba-bill-row.is-new span:last-child { font-family: var(--ba-head); font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; color: #6e1b1b; }
        .ba-bill-foot { margin-top: 0.85rem; font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 0.86rem; color: #3b4a40; text-align: center; }

        /* --------------------------------------------------------------
           6. The window: six panes in a painted frame, lit from inside
           -------------------------------------------------------------- */
        .ba-window {
            position: relative;
            padding: 0.9rem;
            background-color: #0e3b2c;
            background-image: linear-gradient(180deg, #175643, #0b2f24);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.22), inset 0 0 0 1px rgba(3, 14, 10, 0.5), 0 2rem 3rem -1.8rem rgba(4, 19, 13, 0.7);
        }
        /* The sill. */
        .ba-window::after {
            content: "";
            position: absolute;
            inset: 100% -0.8rem auto -0.8rem;
            height: 0.9rem;
            background: linear-gradient(180deg, #1b5d49, #0a2a20);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 0.6rem 1rem -0.4rem rgba(4, 19, 13, 0.6);
        }
        .ba-panes { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.8rem; }
        @media (min-width: 700px) { .ba-panes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1060px) { .ba-panes { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ba-pane {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 2.1rem 1.6rem 1.7rem;
            color: var(--ba-ink);
            background-color: #f8f1df;
            background-image:
                linear-gradient(128deg, rgba(255, 255, 255, 0.75) 0%, rgba(255, 255, 255, 0) 34%),
                radial-gradient(110% 80% at 50% 112%, rgba(240, 178, 62, 0.34), rgba(240, 178, 62, 0) 68%);
            box-shadow: inset 0 0 0 1px rgba(3, 14, 10, 0.55), inset 0 0 1.4rem rgba(14, 59, 44, 0.12);
            transition: background-color 0.4s ease, box-shadow 0.4s ease;
        }
        .dark .ba-pane {
            background-color: #12362b;
            background-image:
                linear-gradient(128deg, rgba(255, 255, 255, 0.09) 0%, rgba(255, 255, 255, 0) 34%),
                radial-gradient(110% 80% at 50% 112%, rgba(255, 190, 90, 0.3), rgba(255, 190, 90, 0) 68%);
            box-shadow: inset 0 0 0 1px rgba(3, 14, 10, 0.8), inset 0 0 1.6rem rgba(255, 196, 110, 0.08);
        }
        .ba-pane:hover { box-shadow: inset 0 0 0 1px rgba(3, 14, 10, 0.55), inset 0 0 2.4rem rgba(240, 178, 62, 0.4); }
        .dark .ba-pane:hover { box-shadow: inset 0 0 0 1px rgba(3, 14, 10, 0.8), inset 0 0 2.6rem rgba(255, 196, 110, 0.26); }
        /* The etched border every pane carries, with a lozenge at the head. */
        .ba-pane::before {
            content: "";
            position: absolute;
            inset: 0.6rem;
            border: 1px solid var(--ba-line);
            pointer-events: none;
        }
        .ba-pane::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 0.6rem;
            width: 0.7rem;
            height: 0.7rem;
            margin: -0.35rem 0 0 -0.35rem;
            rotate: 45deg;
            background: #c99a2e;
            box-shadow: 0 0 0 3px var(--ba-pane-bg, #f8f1df);
        }
        .dark .ba-pane::after { --ba-pane-bg: #12362b; }
        .ba-pane-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 0.8rem; }
        .ba-pane h3 { font-family: var(--ba-head); font-weight: 700; font-size: 1.32rem; line-height: 1.25; }
        .ba-pane p { margin-top: 0.85rem; color: var(--ba-ink-2); }
        .ba-pane p + p { font-size: 0.95rem; }
        .ba-pane p a { color: var(--ba-ink); font-weight: 700; text-decoration: underline; text-decoration-color: #c99a2e; text-decoration-thickness: 2px; text-underline-offset: 0.18em; }
        @media (min-width: 1060px) { .ba-pane-wide { grid-column: span 2; } }

        /* --------------------------------------------------------------
           7. The honours board: the same seven nights in gold leaf
           -------------------------------------------------------------- */
        .ba-honours {
            position: relative;
            width: min(100%, 54rem);
            margin: 3.6rem auto 0;
            padding: clamp(1.4rem, 3vw, 2.25rem) clamp(1.1rem, 3.4vw, 2.75rem);
            color: #f3e9d2;
            background-color: #2c150c;
            background-image:
                repeating-linear-gradient(91deg, rgba(0, 0, 0, 0.16) 0 2px, rgba(255, 255, 255, 0.02) 2px 6px, rgba(0, 0, 0, 0) 6px 14px),
                linear-gradient(180deg, rgba(66, 34, 19, 0), #1e0d07 100%);
            border: 0.6rem solid #1d0d07;
            outline: 1px solid rgba(231, 207, 138, 0.7);
            outline-offset: -0.95rem;
            box-shadow: inset 0 0 2.5rem rgba(0, 0, 0, 0.5), 0 2.2rem 3rem -1.8rem rgba(4, 19, 13, 0.8);
        }
        .ba-honours-top {
            position: absolute;
            left: 50%;
            bottom: calc(100% + 0.55rem);
            width: min(64%, 21rem);
            height: 3.4rem;
            translate: -50% 0;
            display: grid;
            place-items: end center;
            padding-bottom: 0.35rem;
            background-color: #2c150c;
            background-image: linear-gradient(180deg, #4a2615, #24110a);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
            box-shadow: inset 0 2px 0 rgba(231, 207, 138, 0.65), inset 0 0 0 0.3rem #1d0d07;
            font-family: var(--ba-script);
            font-weight: 700;
            font-size: 1.6rem;
            line-height: 1;
        }
        .ba-honours-row {
            display: grid;
            grid-template-columns: 3.4rem auto minmax(1.5rem, 1fr) auto;
            align-items: baseline;
            gap: 0.9rem;
            padding-block: 0.85rem 0.75rem;
        }
        .ba-honours-row + .ba-honours-row { border-top: 1px solid rgba(231, 207, 138, 0.22); }
        .ba-honours-row { transition: opacity 0.3s ease; }
        .ba-honours:has(.ba-honours-row:hover) .ba-honours-row:not(:hover) { opacity: 0.5; }
        .ba-honours-day { font-family: var(--ba-head); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.22em; text-transform: uppercase; color: #e6c35a; }
        .ba-honours-name { font-family: var(--ba-display); font-size: clamp(1.1rem, 2.5vw, 1.6rem); line-height: 1.2; }
        .ba-honours-dots { border-bottom: 2px dotted rgba(231, 207, 138, 0.5); translate: 0 -0.3em; }
        .ba-honours-note { font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 0.98rem; color: #dbcfb2; text-align: end; }
        @media (max-width: 680px) {
            .ba-honours-row { grid-template-columns: 2.9rem minmax(0, 1fr); gap: 0.15rem 0.7rem; }
            .ba-honours-dots { display: none; }
            .ba-honours-note { grid-column: 2; text-align: start; }
        }

        /* --------------------------------------------------------------
           8. The bar top: six beer mats on polished mahogany
           -------------------------------------------------------------- */
        .ba-bartop {
            position: relative;
            color: #f3e9d2;
            background-color: #32180e;
            background-image:
                linear-gradient(180deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0) 12rem),
                repeating-linear-gradient(89deg, rgba(0, 0, 0, 0.18) 0 2px, rgba(255, 255, 255, 0.018) 2px 7px, rgba(0, 0, 0, 0) 7px 17px),
                linear-gradient(180deg, rgba(69, 35, 20, 0), #24110a);
            box-shadow: inset 0 0.5rem 0 #1d0d07, inset 0 calc(0.5rem + 2px) 0 rgba(231, 207, 138, 0.55), inset 0 -0.5rem 0 #1d0d07;
        }
        .ba-bartop .ba-h2 { color: #f3e9d2; }
        .ba-bartop .ba-sub { color: #dbcfb2; }
        .ba-bartop .ba-kicker { color: #f0d57c; }
        .ba-mats { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.25rem 2rem; justify-items: center; }
        @media (min-width: 720px) { .ba-mats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .ba-mats { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2.75rem 2rem; } }
        .ba-mat {
            position: relative;
            width: min(100%, 21.5rem);
            aspect-ratio: 1;
            display: grid;
            place-items: center;
            color: #14261d;
            background-color: #f7efda;
            background-image: radial-gradient(circle at 30% 22%, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0) 58%);
            border-radius: 1.6rem;
            box-shadow: inset 0 0 0 1px rgba(20, 38, 29, 0.25), 0 0.2rem 0 #cfc2a2, 0 1.6rem 2rem -1rem rgba(0, 0, 0, 0.75);
            rotate: var(--r, 0deg);
            transition: rotate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .ba-mat:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .ba-mat-round { border-radius: 50%; }
        .ba-mat-rim { position: absolute; inset: 0; width: 100%; height: 100%; }
        .ba-mat-rim text { font-family: var(--ba-head); font-weight: 700; font-size: 8.4px; letter-spacing: 0.12em; text-transform: uppercase; fill: var(--c, #0e3b2c); }
        .ba-mat-rim circle,
        .ba-mat-rim rect { fill: none; stroke: var(--c, #0e3b2c); stroke-width: 0.8; }
        .ba-mat-in { position: relative; display: grid; justify-items: center; gap: 0.5rem; width: 61%; text-align: center; }
        .ba-mat:not(.ba-mat-round) .ba-mat-in { width: 68%; }
        .ba-mat h3 { font-family: var(--ba-display); font-size: 1.16rem; line-height: 1.18; color: var(--c, #0e3b2c); text-wrap: balance; }
        .ba-mat p { font-size: 0.86rem; line-height: 1.42; color: #3b4a40; }
        .ba-mat a { font-family: var(--ba-head); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: #14261d; border-bottom: 2px solid #c99a2e; }
        .ba-mat a:hover { border-bottom-width: 4px; }
        #ba .ba-bartop a:focus-visible { outline-color: #6e1b1b; }
        /* The ring a wet glass left on one of them. */
        .ba-mat-wet::after {
            content: "";
            position: absolute;
            right: -9%;
            bottom: 3%;
            width: 44%;
            aspect-ratio: 1;
            border-radius: 50%;
            border: 0.5rem solid rgba(125, 82, 28, 0.17);
            box-shadow: 0 0 0 1px rgba(125, 82, 28, 0.06), inset 0 0 0 1px rgba(125, 82, 28, 0.08);
            pointer-events: none;
        }

        /* --------------------------------------------------------------
           9. Three steps: brass table numbers along the rail
           -------------------------------------------------------------- */
        .ba-steps { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 2.5rem; }
        @media (min-width: 860px) {
            .ba-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ba-steps::before {
                content: "";
                position: absolute;
                inset: 2.95rem 14% auto 14%;
                height: 0.5rem;
                border-radius: 0.25rem;
                background: linear-gradient(180deg, #f2dfa0, #b88d28 55%, #8a6410);
                box-shadow: 0 2px 3px rgba(4, 19, 13, 0.35);
            }
        }
        .ba-step { position: relative; display: grid; justify-items: center; align-content: start; gap: 0.9rem; text-align: center; }
        .ba-step-no {
            display: grid;
            place-items: center;
            width: 6.4rem;
            height: 6.4rem;
            border-radius: 50%;
            font-family: var(--ba-display);
            font-size: 2.1rem;
            line-height: 1;
            color: #2a1f05;
            text-shadow: 0 1px 0 rgba(255, 246, 205, 0.6);
            background-color: #d3b25e;
            background-image: conic-gradient(from 20deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0) 14%, rgba(60, 40, 0, 0.22) 30%, rgba(255, 255, 255, 0.4) 50%, rgba(255, 255, 255, 0) 64%, rgba(60, 40, 0, 0.22) 82%, rgba(255, 255, 255, 0.5)), radial-gradient(circle, #e9d188, #c7a24a);
            box-shadow: inset 0 0 0 0.3rem rgba(255, 246, 205, 0.35), inset 0 0 0 calc(0.3rem + 1px) rgba(80, 56, 6, 0.55), inset 0 0 0 0.62rem rgba(255, 246, 205, 0.14), inset 0 0 0 calc(0.62rem + 1px) rgba(80, 56, 6, 0.4), 0 0.25rem 0 #6b4d0c, 0 1rem 1.4rem -0.6rem rgba(4, 19, 13, 0.6);
            transition: rotate 0.6s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .ba-step:hover .ba-step-no { rotate: -10deg; }
        .ba-step h3 { margin-top: 0.5rem; font-family: var(--ba-display); font-size: 1.4rem; line-height: 1.2; }
        .ba-step p { max-width: 22rem; color: var(--ba-ink-2); }

        /* --------------------------------------------------------------
           10. Key features: four pump clips on the bar
           -------------------------------------------------------------- */
        .ba-taps { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2.5rem 1.25rem; }
        @media (min-width: 900px) { .ba-taps { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 2rem; } }
        .ba-tap { display: grid; justify-items: center; align-content: start; gap: 1.1rem; text-align: center; }
        .ba-pump { position: relative; display: grid; justify-items: center; width: 100%; padding-top: 4.9rem; }
        /* The handle: turned wood, a brass ferrule, a brass cap. */
        .ba-pull {
            position: absolute;
            left: 50%;
            top: 0;
            width: 1.7rem;
            height: 7.4rem;
            margin-left: -0.85rem;
            border-radius: 0.85rem 0.85rem 0.25rem 0.25rem;
            background:
                linear-gradient(180deg, rgba(0, 0, 0, 0) 0 78%, #8a6410 78% 80%, #f2dfa0 80% 84%, #b88d28 84% 90%, #6b4d0c 90% 100%),
                linear-gradient(180deg, #f2dfa0 0 5%, #8a6410 5% 7%, rgba(0, 0, 0, 0) 7%),
                linear-gradient(90deg, #0e0704, #6b3a22 32%, #3d1f12 62%, #100805);
            box-shadow: 0 0.4rem 0.8rem -0.2rem rgba(4, 19, 13, 0.55);
            transform-origin: 50% 96%;
            transition: rotate 0.55s cubic-bezier(0.34, 1.5, 0.64, 1);
        }
        .ba-tap:hover .ba-pull,
        .ba-tap:focus-visible .ba-pull { rotate: 15deg; }
        /* The drip tray under each one. */
        .ba-pump::after {
            content: "";
            width: min(118%, 13rem);
            height: 0.55rem;
            margin-top: 0.5rem;
            border-radius: 0.2rem;
            background: linear-gradient(180deg, #f2dfa0, #b88d28 50%, #6b4d0c);
            box-shadow: 0 0.4rem 0.7rem -0.3rem rgba(4, 19, 13, 0.6);
        }
        .ba-clip {
            position: relative;
            z-index: 1;
            width: min(100%, 10.5rem);
            aspect-ratio: 10 / 12.4;
            clip-path: polygon(50% 0, 100% 11%, 100% 66%, 50% 100%, 0 66%, 0 11%);
            background-color: #d3b25e;
            background-image: var(--ba-brush), var(--ba-brass);
            transition: rotate 0.4s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.4s cubic-bezier(0.34, 1.4, 0.64, 1);
            transform-origin: 50% 100%;
        }
        .ba-clip-face {
            position: absolute;
            inset: 0.35rem;
            clip-path: polygon(50% 0, 100% 11%, 100% 66%, 50% 100%, 0 66%, 0 11%);
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 0.3rem;
            padding: 12% 5% 24%;
            color: #f3e9d2;
            background-color: var(--c, #0e3b2c);
            background-image: linear-gradient(150deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0) 42%, rgba(0, 0, 0, 0.2));
        }
        .ba-clip-no { font-family: var(--ba-script); font-weight: 700; font-size: 1.05rem; line-height: 1; color: #f0d57c; }
        .ba-clip-name { font-family: var(--ba-display); font-size: clamp(0.7rem, 2.3vw, 0.9rem); line-height: 1.18; text-transform: uppercase; letter-spacing: 0.01em; }
        .ba-tap p { max-width: 15rem; font-size: 0.98rem; color: var(--ba-ink-2); }
        .ba-tap-go { display: inline-flex; align-items: center; gap: 0.35rem; font-family: var(--ba-head); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ba-ink); border-bottom: 2px solid #c99a2e; transition: gap 0.2s ease; }
        .ba-tap:hover .ba-tap-go { gap: 0.65rem; }
        .ba-tap-go svg { width: 0.85rem; height: 0.85rem; }
        .ba-center { margin-top: 3rem; text-align: center; }

        /* --------------------------------------------------------------
           The plan band and the closing strip are shared partials: their
           words and prices stay, only the paint changes.
           -------------------------------------------------------------- */
        #ba .ba-plans > section { background: var(--ba-ground); }
        #ba .ba-plans h2 { font-family: var(--ba-display); font-weight: 400; font-size: clamp(1.8rem, 4vw, 3rem); line-height: 1.12; letter-spacing: 0; color: var(--ba-ink); }
        #ba .ba-plans h2 + p { color: var(--ba-ink-2); font-size: 1.0625rem; }
        #ba .ba-plans .grid > div {
            color: var(--ba-ink);
            background: var(--ba-card);
            border: 2px solid var(--ba-rule);
            border-radius: 0;
            outline: 1px solid var(--ba-rule);
            outline-offset: -0.5rem;
            box-shadow: 0 1.5rem 2.2rem -1.7rem rgba(4, 19, 13, 0.6);
        }
        #ba .ba-plans .grid > div span,
        #ba .ba-plans .grid > div p,
        #ba .ba-plans .grid > div li { color: var(--ba-ink-2); }
        #ba .ba-plans .grid > div .text-3xl { font-family: var(--ba-display); font-weight: 400; font-size: 2.4rem; color: var(--ba-ink); }
        #ba .ba-plans .grid > div .uppercase { font-family: var(--ba-head); letter-spacing: 0.2em; color: var(--ba-accent); }
        #ba .ba-plans .grid > div svg { color: #b88d28; }
        #ba .ba-plans .grid > div:nth-child(2) { background: #0e3b2c; border-color: #c99a2e; outline-color: rgba(231, 207, 138, 0.7); }
        #ba .ba-plans .grid > div:nth-child(2) span,
        #ba .ba-plans .grid > div:nth-child(2) p,
        #ba .ba-plans .grid > div:nth-child(2) li { color: #dbd1b6; }
        #ba .ba-plans .grid > div:nth-child(2) .text-3xl { color: #f3e9d2; }
        #ba .ba-plans .grid > div:nth-child(2) .uppercase { color: #f0d57c; }
        #ba .ba-plans .grid > div:nth-child(2) svg { color: #e6c35a; }
        #ba .ba-plans .grid > div:nth-child(n) .rounded-full {
            font-family: var(--ba-head);
            letter-spacing: 0.14em;
            color: #2a1f05;
            background-color: #d3b25e;
            background-image: var(--ba-brush), var(--ba-brass);
            border-radius: 2px;
        }
        #ba .ba-plans a.font-medium { color: var(--ba-ink); font-family: var(--ba-head); font-weight: 700; text-decoration: underline; text-decoration-color: #c99a2e; text-decoration-thickness: 2px; text-underline-offset: 0.3em; }
        #ba .ba-plans a.rounded-2xl {
            padding: 1rem 2.1rem 0.95rem;
            font-family: var(--ba-head);
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #2a1f05;
            text-shadow: 0 1px 0 rgba(255, 246, 205, 0.55);
            background-color: #d3b25e;
            background-image: var(--ba-brush), var(--ba-brass);
            border-radius: 3px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65), inset 0 -2px 0 rgba(80, 56, 6, 0.4), 0 2px 0 #6b4d0c, 0 1rem 1.5rem -0.8rem rgba(4, 19, 13, 0.75);
        }

        #ba .ba-keep > section { background: var(--ba-ground-2); border-top: 3px double var(--ba-rule); }
        #ba .ba-keep h2 { font-family: var(--ba-display); font-weight: 400; color: var(--ba-ink); }
        #ba .ba-keep p.uppercase { font-family: var(--ba-head); font-weight: 700; letter-spacing: 0.2em; color: var(--ba-accent); }
        #ba .ba-keep .grid > a { background: var(--ba-card); border: 1px solid var(--ba-rule); border-radius: 0; outline: 1px solid var(--ba-line); outline-offset: -0.4rem; }
        #ba .ba-keep .grid > a:hover { border-color: #c99a2e; box-shadow: 0 1.4rem 2rem -1.5rem rgba(4, 19, 13, 0.7); }
        #ba .ba-keep .grid > a > span:first-child { display: none; }
        #ba .ba-keep .grid > a h3 { font-family: var(--ba-head); font-weight: 700; font-size: 1.1rem; color: var(--ba-ink); }
        #ba .ba-keep .grid > a p { color: var(--ba-ink-2); }
        #ba .ba-keep .grid > a > span:last-child,
        #ba .ba-keep a.self-start { color: var(--ba-accent); }

        /* --------------------------------------------------------------
           11. Down the road: four signs hanging from one bracket
           -------------------------------------------------------------- */
        .ba-signs {
            position: relative;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 2.75rem 1.25rem;
            padding-top: 2.4rem;
        }
        @media (min-width: 900px) { .ba-signs { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 2rem; } }
        /* The bracket. */
        .ba-signs::before {
            content: "";
            position: absolute;
            inset: 0 -0.5rem auto -0.5rem;
            height: 0.55rem;
            border-radius: 0.3rem;
            background: linear-gradient(180deg, #45453f, #0c0c0b);
            box-shadow: 0 2px 4px rgba(4, 19, 13, 0.4);
        }
        .ba-hang {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            min-height: 8.5rem;
            padding: 1.25rem 1.1rem 1.1rem;
            color: #f3e9d2;
            background-color: #0e3b2c;
            background-image: linear-gradient(160deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0) 45%);
            box-shadow: inset 0 0 0 3px #0a2a20, inset 0 0 0 4px rgba(231, 207, 138, 0.8), 0 1.3rem 1.8rem -1.1rem rgba(4, 19, 13, 0.75);
            transform-origin: 50% -1.9rem;
            transition: rotate 0.7s cubic-bezier(0.34, 1.8, 0.64, 1);
        }
        .ba-hang:nth-child(even) { background-color: #6e1b1b; box-shadow: inset 0 0 0 3px #521313, inset 0 0 0 4px rgba(231, 207, 138, 0.8), 0 1.3rem 1.8rem -1.1rem rgba(4, 19, 13, 0.75); }
        .ba-hang:hover { rotate: 2.4deg; }
        .ba-hang:nth-child(even):hover { rotate: -2.4deg; }
        /* Two chains to the bracket. */
        .ba-hang::before {
            content: "";
            position: absolute;
            inset: -1.9rem 18% 100% 18%;
            border-inline: 2px dotted #5a5a52;
        }
        @media (min-width: 900px) { .ba-hang:nth-child(even) { margin-top: 0.9rem; } .ba-hang:nth-child(even)::before { top: -2.8rem; } .ba-hang:nth-child(even) { transform-origin: 50% -2.8rem; } }
        .ba-hang strong { font-family: var(--ba-display); font-weight: 400; font-size: clamp(1rem, 2.2vw, 1.3rem); line-height: 1.2; text-wrap: balance; }
        .ba-hang span { display: inline-flex; align-items: center; gap: 0.35rem; margin-top: auto; font-family: var(--ba-head); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: #f0d57c; transition: gap 0.2s ease; }
        .ba-hang:hover span { gap: 0.65rem; }
        .ba-hang svg { width: 0.85rem; height: 0.85rem; }
        #ba .ba-hang:focus-visible { outline-color: #c99a2e; }

        /* --------------------------------------------------------------
           12. Ask at the bar
           -------------------------------------------------------------- */
        .ba-asks { counter-reset: ba-q; width: min(100%, 52rem); margin-inline: auto; border-top: 3px double var(--ba-rule); }
        .ba-asks details { counter-increment: ba-q; border-bottom: 1px solid var(--ba-line); }
        .ba-asks summary { display: grid; grid-template-columns: 2.5rem minmax(0, 1fr) 1.4rem; align-items: center; gap: 1rem; padding: 1.2rem 0.25rem; cursor: pointer; }
        .ba-asks summary::before {
            content: counter(ba-q);
            display: grid;
            place-items: center;
            width: 2.3rem;
            height: 2.3rem;
            border-radius: 50%;
            font-family: var(--ba-display);
            font-size: 0.95rem;
            color: #2a1f05;
            background-color: #d3b25e;
            background-image: radial-gradient(circle at 35% 28%, #fbefc0, #cba84c 55%, #a67c1c);
            box-shadow: inset 0 0 0 2px rgba(255, 246, 205, 0.4), 0 1px 0 #6b4d0c, 0 2px 4px rgba(4, 19, 13, 0.3);
        }
        .ba-asks h3 { font-family: var(--ba-head); font-weight: 700; font-size: 1.22rem; line-height: 1.3; }
        .ba-asks summary i { position: relative; width: 1.4rem; height: 1.4rem; }
        .ba-asks summary i::before,
        .ba-asks summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: var(--ba-accent); transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .ba-asks summary i::after { rotate: 90deg; }
        .ba-asks details[open] summary i::after { rotate: 0deg; }
        .ba-asks details[open] h3 { color: var(--ba-accent); }
        .ba-asks details p { padding: 0 0.25rem 1.6rem 3.75rem; color: var(--ba-ink-2); }
        @media (max-width: 560px) { .ba-asks details p { padding-inline-start: 0.25rem; } }

        /* --------------------------------------------------------------
           13. The second fascia: the board is blank, the brush is loaded
           -------------------------------------------------------------- */
        .ba-last { overflow: clip; text-align: center; }
        .ba-last-in { display: grid; justify-items: center; }
        .ba-last .ba-h2 { margin-top: 1.5rem; font-size: clamp(2rem, 5vw, 3.9rem); max-width: 62rem; }
        .ba-last .ba-h2 .ba-gilt { display: block; }
        .ba-last-sub { margin-top: 1.4rem; max-width: 40rem; font-size: 1.15rem; color: #cfc6ab; }
        .ba-last .ba-fascia { width: min(100%, 50rem); margin-top: clamp(3.25rem, 6vw, 4.5rem); background-color: #0a2a20; background-image: radial-gradient(60% 150% at 50% -30%, rgba(255, 220, 150, 0.32), rgba(255, 220, 150, 0) 72%), linear-gradient(180deg, #0f4334 0%, #0a2a20 30%, #071f18 100%); }
        .ba-name {
            display: block;
            font-family: var(--ba-display);
            /* The floor is low enough for thirty letters on a 360px phone: at 1.15rem a name past
               eighteen letters ran off the board there, and gold leaf is not painted outside its box. */
            font-size: clamp(0.6rem, calc(108cqi / max(var(--ba-len, 8), 8)), 4.4rem);
            line-height: 1.15;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .ba-name-tld { display: block; margin-top: 0.5rem; font-family: var(--ba-head); font-weight: 700; font-size: clamp(0.66rem, 2.2cqi, 0.9rem); letter-spacing: 0.3em; text-transform: uppercase; color: #cfc6ab; }
        .ba-claim-row { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; width: min(100%, 44rem); margin-top: 2.25rem; }
        @media (min-width: 720px) { .ba-claim-row { grid-template-columns: minmax(0, 1fr) auto; } }
        #ba .ba-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.2rem;
            font-family: var(--ba-text);
            font-weight: 700;
            font-size: clamp(1rem, 3.2vw, 1.1rem);
            color: #14261d;
            background: #fbf5e6;
            border: 2px solid #c99a2e;
            box-shadow: inset 0 0 0 3px #fbf5e6, inset 0 0 0 4px rgba(20, 38, 29, 0.4);
            transition: box-shadow 0.2s ease;
        }
        #ba .ba-claim:focus-within { border-color: #f0d57c; box-shadow: inset 0 0 0 3px #fbf5e6, inset 0 0 0 4px rgba(20, 38, 29, 0.4), 0 0 0 4px rgba(240, 213, 124, 0.4); }
        #ba .ba-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #14261d; box-shadow: none; outline: none; }
        #ba .ba-claim input::placeholder { color: #6a756c; }
        .ba-claim span { flex: none; color: #3b4a40; user-select: none; }
        .ba-last-note { margin-top: 1.4rem; font-family: Georgia, 'Times New Roman', serif; font-style: italic; color: #cfc6ab; }

        @media (prefers-reduced-motion: reduce) {
            .ba-gilt-live { animation: none; }
            .ba-swing-board { animation: none; }
            .ba-btn, .ba-link, .ba-mat, .ba-hang, .ba-clip, .ba-pull, .ba-step-no, .ba-pane, .ba-honours-row, .ba-rail i, .ba-rail span { transition: none; }
        }
    </style>

    @php
        // THU is deliberately blank: the empty night is this page's through-line.
        $barWeek = [
            ['Mon', 'Quiz night', '8pm start', false],
            ['Tue', 'Open mic', 'Sign up from 6', false],
            ['Wed', 'Vinyl night', 'Bring a record', false],
            ['Thu', '', '', true],
            ['Fri', 'The Howl', 'Live, 9pm', 'accent'],
            ['Sat', 'Karaoke', 'Til late', false],
            ['Sun', 'The match', 'Kick off 4pm', false],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for bars and pubs?',
                'a' => 'Yes. Sharing your calendar, running recurring weekly nights, splitting them into sub-schedules, taking free registrations, and syncing with Google, Outlook or CalDAV are all free forever. Newsletters are free too, at 10 emails a month, counted per recipient rather than per send. Free registration has no monthly ceiling on it, and a ticket is scanned at the door on any plan. Putting a price on a ticket is what the Pro plan is for at '.plan_price($proMonthly).' a month, along with the live check-in dashboard and the higher 100-a-month newsletter limit.',
            ],
            [
                'q' => 'Can I set up a night that repeats every week?',
                'a' => 'Yes, on every plan. Set the day-of-week pattern once and the night repeats itself, then add date exceptions for the weeks you are closed or the holiday lands on your quiz night. You can still add one-off events like a tap takeover or a big match alongside the regular lineup.',
            ],
            [
                'q' => 'How do I keep live music, quiz nights and sports apart on one page?',
                'a' => 'Sub-schedules split one schedule into strands, so your live music, your quiz night and your sports fixtures each sit in their own section of the same link. Sub-schedules are free on every plan.',
            ],
            [
                'q' => 'Can bands and DJs ask to play at my bar?',
                'a' => 'Yes. Turn on Accept requests in your schedule settings and performers can submit an event from your public page. Submissions land on your Requests tab, where you review each one and accept or decline it before anything appears on your calendar. This is not tied to a paid plan.',
            ],
            [
                'q' => 'How do I tell my regulars what is on this week?',
                'a' => 'People follow your schedule and you email them directly, so nothing decides who sees it except you. The free plan covers 10 newsletter emails a month and Pro raises it to 100, counted per recipient rather than per send, so it is worth knowing the number before you plan around it. Regulars can also sign up on your page with their name and email address, and once they confirm it they get a round-up of your new nights on their own, at most one every 72 hours. That round-up does not come out of the newsletter allowance.',
            ],
            [
                'q' => 'Can I sell tickets to a ticketed night?',
                'a' => 'Yes, on Pro at '.plan_price($proMonthly).' a month, which is what a ticket with a price on it needs. Take the money through your own Stripe or PayPal account, or a payment link or cash at the bar, and sell straight from your calendar. Scanning the QR code at the door is not gated at all, on any plan. Pro also brings the live check-in dashboard for a busy door, promo codes and add-ons. Event Schedule charges zero platform fees either way, so beyond the processor\'s own fee the money is yours. If a night is called off, refund it from the Sales page: a Stripe or PayPal sale goes back through the provider, in full or in part. Free registration with a capacity limit is there for the nights you do not charge for.',
            ],
            [
                'q' => 'Can regulars put our whole week in their own calendar?',
                'a' => 'Yes, on every plan. The Add to Calendar menu on any of your events, and the sign-up panel on your schedule page, offer a subscription to all your events: a live calendar feed that updates itself when a night moves or a new one goes up, and it needs no email address. A single night can still be downloaded as an .ics file, which is a one-off copy that never updates.',
            ],
        ];

        $dotSections = [
            ['top', 'The front'],
            ['thursday', 'The empty night'],
            ['week', 'The house list'],
            ['playing', "Who's playing"],
            ['regulars', 'The regulars'],
            ['rest', 'The rest of it'],
            ['rooms', 'Every kind of bar'],
            ['who', 'Perfect for'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Your name up'],
        ];

        $baArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $baDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="ba">

        <nav class="ba-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot"><i aria-hidden="true"></i><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. The front: fascia, frieze, the mirror behind the bar      -->
        <!-- ============================================================ -->
        <section id="top" class="ba-hero">
            <div class="ba-wrap">
                <div class="ba-fascia es-fade-up es-d-1">
                    <i class="ba-lamp" style="--x: 20%;" aria-hidden="true"></i>
                    <i class="ba-lamp" style="--x: 80%;" aria-hidden="true"></i>
                    <div class="ba-fascia-in">
                        <h1 class="ba-h1">
                            <x-marketing.hero-eyebrow class="ba-plaque ba-eyebrow">
                                Event calendar for bars and pubs
                            </x-marketing.hero-eyebrow>
                            <span class="es-mask"><span class="es-mask-line"><span class="ba-gilt ba-gilt-live ba-sign">Your week is chalked on a board</span></span></span>
                            <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="ba-sign-2">nobody sees after closing.</span></span></span>
                        </h1>
                    </div>
                </div>

                <!-- The frieze: every kind of house -->
                <div class="ba-frieze es-fade-up es-d-2">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Craft Beer', 'Wine Bar', 'Sports Bar', 'Cocktail Lounge', 'Irish Pub', 'Dive Bar', 'Taproom', 'Speakeasy', 'Beer Garden', 'Music Bar'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="ba-frieze-item">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="ba-front">
                    <div class="ba-swing es-fade-up es-d-2" aria-hidden="true">
                        <div class="ba-swing-board">
                            <small>The</small>
                            <b class="ba-gilt">Free</b>
                            <b class="ba-gilt">House</b>
                            <i>Open all hours</i>
                        </div>
                    </div>

                    <div>
                        <p class="ba-lede es-fade-up es-d-2">
                            Quiz nights, live music, karaoke, the match. Put the whole week on one link that still works when the shutters are down, with recurring dates that skip the holidays and no platform fees when you sell.
                        </p>

                        <div class="ba-cta es-fade-up es-d-3">
                            <a href="{{ app_url('/sign_up?type=venue') }}" class="ba-btn">
                                Create your bar's calendar
                                {!! $baArrow !!}
                            </a>
                            <a href="#week" class="ba-link">
                                See how the week works
                                {!! $baDown !!}
                            </a>
                        </div>

                        <div class="ba-licence es-fade-up es-d-4" aria-hidden="true">
                            <span class="ba-plaque">Free house</span>
                            <span class="ba-plaque">Open all hours</span>
                        </div>
                    </div>

                    <!-- The week, lettered on the mirror behind the bar -->
                    <div class="ba-mirror es-fade-up es-d-3">
                        <div class="ba-glass">
                            <div class="ba-week-title">This Week</div>

                            @foreach ($barWeek as [$day, $night, $detail, $state])
                                <div @class(['ba-week-row', 'is-open' => $state === true, 'is-lit' => $state === 'accent'])>
                                    <span class="ba-week-day">{{ $day }}</span>
                                    @if ($state === true)
                                        <span class="ba-week-night">nothing yet</span>
                                        <span class="ba-week-open">open</span>
                                    @else
                                        <span class="ba-week-night">{{ $night }}</span>
                                        <span class="ba-week-note">{{ $detail }}</span>
                                    @endif
                                </div>
                            @endforeach

                            <p class="ba-week-foot">
                                Wiped at closing. Rewritten every Monday. Gone from the internet entirely.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ba-tiles" aria-hidden="true"></div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The empty Thursday: seven glasses on the counter          -->
        <!-- ============================================================ -->
        <section id="thursday" class="ba-dark ba-section" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-head">
                    <p class="ba-kicker" data-reveal>The empty night</p>
                    <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Friday looks after itself. <span class="ba-gilt">Thursday is the one costing you.</span>
                    </h2>
                </div>

                <div class="ba-round" aria-hidden="true" data-reveal>
                    @foreach ($barWeek as [$day, $night, $detail, $state])
                        <div @class(['ba-round-item', 'is-thu' => $state === true])>
                            <span @class(['ba-pint', 'ba-pint-thu' => $state === true])></span>
                            <span class="ba-round-day">{{ $day }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="ba-etches" data-reveal-group="110">
                    <div class="ba-etch" data-reveal="panel">
                        <p class="ba-etch-tag">The board</p>
                        <h3>One copy, on the pavement</h3>
                        <p>It works on the people already walking past your door. Everyone else would have to guess.</p>
                    </div>
                    <div class="ba-etch" data-reveal="panel">
                        <p class="ba-etch-tag">The Thursdays</p>
                        <h3>
                            <span class="ba-gilt"><span data-count-to="52">52</span></span> a year
                        </h3>
                        <p>A quiet weeknight is not one bad night. It is the same night, fifty-two times, with the lights and the staff already paid for.</p>
                    </div>
                    <div class="ba-etch" data-reveal="panel">
                        <p class="ba-etch-tag">The fix</p>
                        <h3>Give it a reason</h3>
                        <p>A quiz, an open mic, a vinyl night. Set it up once, let it repeat, and tell the people who already like your bar.</p>
                    </div>
                </div>

                <p class="ba-dark-foot" data-reveal>
                    The board is right. It just needs to exist somewhere other than the pavement.
                    <a href="#week" class="ba-link">
                        Chalk up the week
                        {!! $baDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The house list: set it once, it repeats                   -->
        <!-- ============================================================ -->
        <section id="week" class="ba-section ba-alt" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-menu" data-reveal="panel">
                    <div class="ba-head">
                        <p class="ba-kicker">On the house</p>
                        <h2 class="ba-h2">
                            Write the week once. <span class="ba-turn">It writes itself after that.</span>
                        </h2>
                        <p class="ba-sub">
                            Three things carry a bar's calendar, and all three are on the free plan.
                        </p>
                    </div>

                    <div class="ba-menu-item">
                        <span class="ba-menu-no" aria-hidden="true">01</span>
                        <div class="ba-menu-line">
                            <h3>The quiz repeats itself</h3>
                            <i aria-hidden="true"></i>
                            <span class="ba-plaque">Free</span>
                        </div>
                        <p>
                            Set a night once with a day-of-week pattern and it comes back every week on its own. Add date exceptions for the weeks you are shut, or when the bank holiday lands on quiz night.
                        </p>
                    </div>

                    <div class="ba-menu-item">
                        <span class="ba-menu-no" aria-hidden="true">02</span>
                        <div class="ba-menu-line">
                            <h3>Music, quiz and sport stay apart</h3>
                            <i aria-hidden="true"></i>
                            <span class="ba-plaque">Free</span>
                        </div>
                        <p>
                            Sub-schedules split one link into strands, so somebody who only cares about the live music is not scrolling past six weeks of fixtures to find it.
                        </p>
                    </div>

                    <div class="ba-menu-item">
                        <span class="ba-menu-no" aria-hidden="true">03</span>
                        <div class="ba-menu-line">
                            <h3>Hold a spot without charging for it</h3>
                            <i aria-hidden="true"></i>
                            <span class="ba-plaque">Free</span>
                        </div>
                        <p>
                            Turn on registration for a night and set how many places there are. Quiz teams claim a table, the page shows how many are left, and it closes itself when they are gone.
                        </p>
                    </div>

                    <p class="ba-menu-small">
                        Free also covers unlimited events, two-way Google, Outlook and CalDAV sync, an embeddable calendar for the site you already have, and a calendar feed regulars can subscribe to, so the week sits in their own phone and a moved night updates itself.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Who's playing: the bookings book                          -->
        <!-- ============================================================ -->
        <section id="playing" class="ba-section" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap ba-two">
                <div>
                    <p class="ba-kicker" data-reveal>Who's playing</p>
                    <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Let the bands come to you, <span class="ba-turn">in one place.</span>
                    </h2>
                    <p class="ba-two-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Turn on <strong>Accept requests</strong> and performers can submit a night straight from your public page instead of finding you across three inboxes and a DM. Nothing lands on your calendar until you say so.
                    </p>
                    <ul class="ba-ticks" data-reveal-group="70">
                        <li data-reveal>Submissions collect on your Requests tab, where you accept or decline each one.</li>
                        <li data-reveal>Add your own questions to the form on Pro, so a band tells you the set length and what they need from the PA up front.</li>
                        <li data-reveal>Accepting a request is not a paid feature. It works on the free plan.</li>
                        <li data-reveal>Booked a band who is not on Event Schedule? Name them on the night anyway. The event page lists them, and they get a page of their own that stays out of search engines until they claim it.</li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="ba-book">
                        <div class="ba-book-head">
                            <span>Requests</span>
                            <span>3 waiting</span>
                        </div>

                        @foreach ([['The Howl', 'Fri 14 Mar', '4-piece, 45 min'], ['DJ Marren', 'Sat 22 Mar', 'Vinyl only, 2 hrs'], ['Quiz w/ Nadia', 'Thu 27 Mar', 'Hosted, 2 rounds']] as [$reqName, $reqDate, $reqNote])
                            <div class="ba-book-row">
                                <div>
                                    <strong>{{ $reqName }}</strong>
                                    <small>{{ $reqDate }} &middot; {{ $reqNote }}</small>
                                </div>
                                <span class="ba-plaque">Accept</span>
                            </div>
                        @endforeach

                        <p class="ba-book-note">Declined requests never touch your calendar.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The regulars: the snug                                    -->
        <!-- ============================================================ -->
        <section id="regulars" class="ba-dark ba-section" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-head">
                    <p class="ba-kicker" data-reveal>The regulars</p>
                    <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The people who already like your bar <span class="ba-gilt">should not be the hardest to reach.</span>
                    </h2>
                </div>

                <div class="ba-snug">
                    <div class="ba-snug-list" data-reveal-group="80">
                        <div class="ba-etch" data-reveal>
                            <div class="ba-etch-row">
                                <h3>They follow you, you email them</h3>
                                <span class="ba-plaque">Free</span>
                            </div>
                            <p>Nothing sits between the two of you deciding which regulars find out the quiz has moved. The free plan covers 10 newsletter emails a month and Pro raises it to 100, counted per recipient rather than per send.</p>
                        </div>
                        <div class="ba-etch" data-reveal>
                            <div class="ba-etch-row">
                                <h3>Paid reach, pointed outward</h3>
                                <span class="ba-plaque ba-plaque-pro">Pro</span>
                            </div>
                            <p>Boost puts an event in front of people on Facebook and Instagram who have <em>not</em> heard of you yet. That is worth paying for. Paying to reach the regulars you already have is not, which is why the newsletter above is free.</p>
                        </div>
                        <div class="ba-etch" data-reveal>
                            <div class="ba-etch-row">
                                <h3>The wall of who's played</h3>
                                <span class="ba-plaque">Free</span>
                            </div>
                            <p>Your schedule can show a wall of the acts that have played your room, pulled from the events you have both agreed on. It is the framed photos behind the bar, except people can find it from home.</p>
                        </div>
                    </div>

                    <div data-reveal="panel">
                        <!-- The round-up, printed and pinned up: it stays a lit cream card on the dark wall. -->
                        <div class="ba-bill">
                            <div class="ba-bill-head">
                                <span>This week at</span>
                                <span>The Anchor</span>
                            </div>
                            @foreach ([['Mon', 'Quiz night', false], ['Tue', 'Open mic', false], ['Wed', 'Vinyl night', false], ['Thu', 'Jazz trio', true], ['Fri', 'The Howl', false]] as [$mDay, $mName, $mNew])
                                <div @class(['ba-bill-row', 'is-new' => $mNew])>
                                    <span>{{ $mDay }}</span>
                                    <span>{{ $mName }}</span>
                                    @if ($mNew)<span>New</span>@endif
                                </div>
                            @endforeach
                            <p class="ba-bill-foot">Sent to everyone following this schedule.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. The rest of it: six panes in the window                   -->
        <!-- ============================================================ -->
        <section id="rest" class="ba-section ba-alt" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-head">
                    <p class="ba-kicker" data-reveal>The rest of it</p>
                    <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Everything else <span class="ba-turn">a room needs.</span>
                    </h2>
                </div>

                <div class="ba-window" data-reveal="panel">
                    <div class="ba-panes">
                        <!-- 1 -->
                        <div class="ba-pane ba-pane-wide">
                            <div class="ba-pane-head">
                                <h3>When the night is ticketed</h3>
                                <span class="ba-plaque ba-plaque-pro">Pro</span>
                            </div>
                            <p>
                                Sell straight from your calendar and scan the QR code at the door, which is free on any plan. Putting a price on the night is the Pro half. Take the money through your own Stripe or <a href="{{ marketing_url('/paypal') }}">PayPal</a> account, or a payment link or cash at the bar, and Event Schedule takes zero platform fees on any of it.
                            </p>
                            <p>
                                Before it goes on sale, and with the "Notify me" card switched on, people can leave an email address on the event page and hear when it does. On Pro: discount codes for the regulars, and a pass that covers a whole season of a night rather than one at a time.
                            </p>
                        </div>

                        <!-- 2 -->
                        <div class="ba-pane">
                            <div class="ba-pane-head">
                                <h3>Photos from the night</h3>
                                <span class="ba-plaque">Free</span>
                            </div>
                            <p>
                                Regulars add photos, video and comments to an event with just a name and an email. Everything waits in an approval queue, so your page stays yours. Free covers 25 photos per schedule.
                            </p>
                        </div>

                        <!-- 3 -->
                        <div class="ba-pane">
                            <div class="ba-pane-head">
                                <h3>On the site you already have</h3>
                                <span class="ba-plaque">Free</span>
                            </div>
                            <p>
                                Embed the calendar on your own site so the week lives where people already look you up, instead of only on a page they have to be told about.
                            </p>
                        </div>

                        <!-- 4 -->
                        <div class="ba-pane ba-pane-wide">
                            <div class="ba-pane-head">
                                <h3>Know which nights actually land</h3>
                                <span class="ba-plaque">Free</span>
                            </div>
                            <p>
                                Built-in analytics show page views, the devices people are on, and where the traffic came from. Enough to tell whether the quiz post did anything, without installing a thing.
                            </p>
                            <p>
                                On Pro, add a poll to the event and let the room vote on the theme or which night to move to.
                            </p>
                        </div>

                        <!-- 5 -->
                        <div class="ba-pane ba-pane-wide">
                            <div class="ba-pane-head">
                                <h3>The poster, without opening a design tool</h3>
                                <span class="ba-plaque ba-plaque-pro">Pro</span>
                            </div>
                            <p>
                                Generate a shareable graphic from an event and post it, rather than rebuilding the same quiz-night square every week.
                            </p>
                            <p>
                                Every night also syncs two ways with Google, Outlook and CalDAV, so what is on the wall and what is in your phone cannot drift apart.
                            </p>
                        </div>

                        <!-- 6 -->
                        <div class="ba-pane">
                            <div class="ba-pane-head">
                                <h3>Keep the room private</h3>
                                <span class="ba-plaque">Free</span>
                            </div>
                            <p>
                                A function or a staff party can sit on the calendar as a draft, visible to you but never published to the public page.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Every kind of bar: the honours board                      -->
        <!-- ============================================================ -->
        <section id="rooms" class="ba-section" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-head">
                    <h2 class="ba-h2" data-reveal>
                        Whatever is <span class="ba-turn">on the board</span>
                    </h2>
                    <p class="ba-sub" data-reveal style="--reveal-delay: 0.08s;">
                        The same seven nights, whatever kind of room you run.
                    </p>
                </div>

                <div class="ba-honours" data-reveal="panel">
                    <div class="ba-honours-top" aria-hidden="true"><span class="ba-gilt">What's on</span></div>
                    @foreach ([['Mon', 'Quiz and trivia', 'Teams book a table in advance'], ['Tue', 'Open mic and jams', 'Sign-ups from the door'], ['Wed', 'Tastings and takeovers', 'Limited places, ticketed'], ['Thu', 'Live music', 'The night you are filling'], ['Fri', 'DJs and late sets', 'Doors and last entry'], ['Sun', 'The match and roasts', 'Kick-off times up front']] as [$rDay, $rName, $rBlurb])
                        <div class="ba-honours-row">
                            <span class="ba-honours-day">{{ $rDay }}</span>
                            <span class="ba-honours-name"><span class="ba-gilt">{{ $rName }}</span></span>
                            <span class="ba-honours-dots" aria-hidden="true"></span>
                            <span class="ba-honours-note">{{ $rBlurb }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Perfect for: six beer mats on the bar top                 -->
        <!-- ============================================================ -->
        <section id="who" class="ba-bartop ba-section" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-head">
                    <h2 class="ba-h2" data-reveal>
                        Perfect for all types of <span class="ba-gilt">bars</span>
                    </h2>
                    <p class="ba-sub" data-reveal style="--reveal-delay: 0.08s;">
                        A taproom and a cocktail lounge have different crowds and the same quiet Thursday
                    </p>
                </div>

                @php
                    // Name, description, blog slug, round or square, the lettering round the rim, ink, tilt.
                    $baMats = [
                        ['Craft Beer Bars', 'Tap takeovers, brewery events, and beer release parties. Build a following of craft beer enthusiasts.', 'for-craft-beer-bars', true, 'Tap takeovers ✦ Brewery events ✦ Beer release parties ✦ Tap takeovers ✦', '#0e3b2c', '-4deg'],
                        ['Wine Bars', 'Wine tastings, vineyard dinners, and sommelier events. Educate and delight your wine-loving guests.', 'for-wine-bars', false, 'Wine tastings ✦ Vineyard dinners ✦ Sommelier events ✦ Wine tastings ✦ Vineyard dinners ✦', '#6e1b1b', '3deg'],
                        ['Sports Bars', 'Game day watch parties, trivia nights, and UFC events. Let fans know what\'s on the big screen.', 'for-sports-bars', true, 'Game day watch parties ✦ Trivia nights ✦ On the big screen ✦', '#0e3b2c', '2deg'],
                        ['Cocktail Lounges', 'Mixology classes, speakeasy nights, and cocktail competitions. Attract the craft cocktail crowd.', 'for-cocktail-lounges', false, 'Mixology classes ✦ Speakeasy nights ✦ Cocktail competitions ✦ Mixology classes ✦', '#0e3b2c', '-2deg'],
                        ['Irish & British Pubs', 'Pub quizzes, live traditional music, and St. Patrick\'s Day celebrations. Keep the craic alive.', 'for-irish-british-pubs', true, 'Pub quizzes ✦ Live traditional music ✦ Keep the craic alive ✦', '#6e1b1b', '-3deg'],
                        ['Dive Bars & Neighborhood Bars', 'Open mics, karaoke nights, and local band showcases. Your neighborhood\'s living room.', 'for-dive-bars', false, 'Open mics ✦ Karaoke nights ✦ Local band showcases ✦ Open mics ✦ Karaoke nights ✦', '#0e3b2c', '4deg'],
                    ];
                @endphp

                <div class="ba-mats" data-reveal-group="80">
                    @foreach ($baMats as $matIndex => [$matName, $matDesc, $matSlug, $matRound, $matRim, $matInk, $matTilt])
                        @php $matPost = get_sub_audience_blog($matSlug); @endphp
                        <article @class(['ba-mat', 'ba-mat-round' => $matRound, 'ba-mat-wet' => $matIndex === 1]) style="--c: {{ $matInk }}; --r: {{ $matTilt }};" data-reveal>
                            {{-- textLength is given twice on purpose: Firefox reads it only from the text element, Safari only
                                 from the textPath, and without it the lettering stops three quarters of the way round. --}}
                            <svg class="ba-mat-rim" viewBox="0 0 200 200" aria-hidden="true">
                                @if ($matRound)
                                    <defs><path id="ba-rim-{{ $matIndex }}" d="M100,100 m-87,0 a87,87 0 1,1 174,0 a87,87 0 1,1 -174,0" /></defs>
                                    <circle cx="100" cy="100" r="79" />
                                    <circle cx="100" cy="100" r="76.5" />
                                    <text textLength="540" lengthAdjust="spacing"><textPath href="#ba-rim-{{ $matIndex }}" textLength="540" lengthAdjust="spacing">{{ $matRim }}</textPath></text>
                                @else
                                    <defs><path id="ba-rim-{{ $matIndex }}" d="M34,13 H166 a21,21 0 0 1 21,21 V166 a21,21 0 0 1 -21,21 H34 a21,21 0 0 1 -21,-21 V34 a21,21 0 0 1 21,-21 Z" /></defs>
                                    <rect x="21" y="21" width="158" height="158" rx="14" />
                                    <rect x="23.5" y="23.5" width="153" height="153" rx="12" />
                                    <text textLength="652" lengthAdjust="spacing"><textPath href="#ba-rim-{{ $matIndex }}" textLength="652" lengthAdjust="spacing">{{ $matRim }}</textPath></text>
                                @endif
                            </svg>
                            <div class="ba-mat-in">
                                <h3>{{ $matName }}</h3>
                                <p>{{ $matDesc }}</p>
                                @if ($matPost)
                                    <a href="{{ blog_url('/' . $matPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $matName }}">Learn more</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Three steps: brass table numbers                          -->
        <!-- ============================================================ -->
        <section id="steps" class="ba-section" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-head">
                    <h2 class="ba-h2" data-reveal>
                        Three <span class="ba-turn">steps</span>
                    </h2>
                </div>

                <div class="ba-steps" data-reveal-group="120">
                    @foreach ([['01', 'Chalk up the week', 'Add each regular night once as a recurring event, and use sub-schedules to keep live music, quiz nights and sport apart on the same page.'], ['02', 'Share one link', 'On your door, in your bio, on your bookings page. Or embed the calendar straight into the website you already have.'], ['03', 'Keep the regulars posted', 'People follow your schedule, and you email them when something changes or a new night goes up.']] as [$stepNum, $stepTitle, $stepBody])
                        <div class="ba-step" data-reveal>
                            <div class="ba-step-no">{{ $stepNum }}</div>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Key features: four pump clips                            -->
        <!-- ============================================================ -->
        <section class="ba-section ba-alt">
            <div class="ba-wrap">
                <div class="ba-head">
                    <p class="ba-kicker" data-reveal aria-hidden="true">On tap</p>
                    <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                </div>

                @php
                    $baTaps = [
                        ['Recurring Events', 'Set a weekly night once, with exceptions for the weeks you close', marketing_url('/features/recurring-events'), '#0e3b2c'],
                        ['Sub-schedules', 'Keep live music, quiz nights and sport apart on one link', marketing_url('/features/sub-schedules'), '#6e1b1b'],
                        ['Ticketing', 'Sell tickets with QR check-in and zero platform fees', marketing_url('/features/ticketing'), '#32180e'],
                        ['Newsletters', 'Email the regulars who follow your schedule', marketing_url('/features/newsletters'), '#0e3b2c'],
                    ];
                @endphp
                <div class="ba-taps" data-reveal-group="70">
                    @foreach ($baTaps as $tapIndex => [$tapName, $tapDesc, $tapUrl, $tapPaint])
                        <a href="{{ $tapUrl }}" class="ba-tap" data-reveal>
                            <span class="ba-pump" aria-hidden="true">
                                <span class="ba-pull"></span>
                                <span class="ba-clip">
                                    <span class="ba-clip-face" style="--c: {{ $tapPaint }};">
                                        <span class="ba-clip-no">No. {{ $tapIndex + 1 }}</span>
                                        <span class="ba-clip-name">{{ $tapName }}</span>
                                    </span>
                                </span>
                            </span>
                            <span class="sr-only">{{ $tapName }}</span>
                            <p>{{ $tapDesc }}</p>
                            <span class="ba-tap-go" aria-hidden="true">Pull {!! $baArrow !!}</span>
                        </a>
                    @endforeach
                </div>

                <p class="ba-center" data-reveal>
                    <a href="{{ marketing_url('/features') }}" class="ba-link">
                        See all features
                        {!! $baArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <div class="ba-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 11. Related pages: four signs on one bracket                 -->
        <!-- ============================================================ -->
        <section class="ba-section ba-alt">
            <div class="ba-wrap">
                <div class="ba-head">
                    <p class="ba-kicker" data-reveal aria-hidden="true">Down the road</p>
                    <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                </div>

                <div class="ba-signs" data-reveal-group="70">
                    @foreach ([['/for-music-venues', 'Music Venues'], ['/for-breweries-and-wineries', 'Breweries & Wineries'], ['/for-restaurants', 'Restaurants'], ['/for-nightclubs', 'Nightclubs']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="ba-hang" data-reveal>
                            <strong>For {{ $relName }}</strong>
                            <span>
                                Read more
                                {!! $baArrow !!}
                            </span>
                        </a>
                    @endforeach
                </div>

                <p class="ba-center" data-reveal>
                    <a href="{{ marketing_url('/use-cases') }}" class="ba-link">
                        See all use cases
                        {!! $baArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 12. FAQ: ask at the bar                                      -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="ba-section" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap">
                <div class="ba-head">
                    <p class="ba-kicker" data-reveal aria-hidden="true">Ask at the bar</p>
                    <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked <span class="ba-turn">questions</span>
                    </h2>
                    <p class="ba-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Everything bar and pub owners ask before they put the week online.
                    </p>
                </div>

                <div class="ba-asks" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq">
                            <summary>
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
        <!-- 13. Finale: the second fascia, waiting for a name            -->
        <!-- ============================================================ -->
        <div class="ba-tiles" aria-hidden="true"></div>
        <section id="claim" class="ba-dark ba-section ba-last" style="scroll-margin-top: 4rem;">
            <div class="ba-wrap ba-last-in">
                <span class="ba-plaque" data-reveal>Free forever</span>
                <h2 class="ba-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Wipe the board. <span class="ba-gilt">Write it somewhere it stays.</span>
                </h2>
                <p class="ba-last-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Your week, on one link that works at four in the afternoon on a Thursday, when the person deciding where to go is nowhere near your door.
                </p>

                <!-- The fascia, repainted, with your name lettered on -->
                <div class="ba-fascia" aria-hidden="true" data-reveal="panel">
                    <i class="ba-lamp" style="--x: 50%;"></i>
                    <div class="ba-fascia-in">
                        <span class="ba-gilt ba-gilt-live ba-name" id="ba-signtext">your-bar</span>
                        <span class="ba-name-tld">.eventschedule.com</span>
                    </div>
                </div>

                <div class="ba-claim-row" data-reveal>
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div dir="ltr" class="es-claim ba-claim">
                        <input id="es-claim-input" type="text" placeholder="your-bar" autocomplete="off" spellcheck="false" maxlength="30">
                        <span>.eventschedule.com</span>
                    </div>
                    <a href="{{ app_url('/sign_up?type=venue') }}" class="ba-btn">
                        Create your calendar
                        {!! $baArrow !!}
                    </a>
                </div>

                <p class="ba-last-note">No credit card required</p>
            </div>
        </section>

        <div class="ba-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    {{-- Letter the claimed name onto the fascia as it is typed, with the same slug
         transform as the shared claim-input sanitizer, and tell the sign how long
         the name is so the lettering is set to fit the board. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('ba-signtext');
            if (!input || !sign) { return; }
            var fallback = sign.textContent;
            input.addEventListener('input', function () {
                var slug = input.value.toLowerCase()
                    .replace(/['’]/g, '')
                    .replace(/[^a-z0-9-]+/g, '-')
                    .replace(/-{2,}/g, '-')
                    .replace(/^-+/, '')
                    .slice(0, 30);
                sign.textContent = slug || fallback;
                sign.style.setProperty('--ba-len', String((slug || fallback).length));
            });
        })();
    </script>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
