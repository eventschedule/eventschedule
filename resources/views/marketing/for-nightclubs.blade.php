<x-marketing-layout>
    <x-slot name="title">Nightclub Event Calendars | Entry, Capacity and Tickets</x-slot>
    <x-slot name="description">Run your club's door from one link: capacity limits, ticket tiers that change price on the clock, free QR scanning and zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Nightclubs</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Dela Gothic One') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Kanit') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Nightclubs"
        description="Run the entry side of your club from one link: capacity, cover, timed ticket tiers and QR check-in at the door, with zero platform fees on ticket sales."
        audience="Nightclubs and Dance Venues"
        keywords="nightclub event calendar, club night ticketing, door capacity management, QR check-in nightclub, recurring club nights, free nightclub scheduling" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to run a nightclub's entry and calendar with Event Schedule",
        "description": "Get your club's nights and door online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your nights",
                "text": "Set each regular night up once as a recurring event, and use sub-schedules to keep the house night, the hip-hop night and the headline shows apart on the same link."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Set the door",
                "text": "Turn on registration with a capacity limit for free nights, or add ticket types with their own sales windows so cover changes at a set time."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Scan them in",
                "text": "Every ticket carries a QR code. Scan it on the door, free on every plan, and on Pro the live check-in dashboard shows who is inside against the capacity you set."
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
           For-nightclubs "The Flyer" styles. Two faces of one night.

           By day the page is the flyer: black toner on white paper, run
           through a copier, cut and pasted, a little crooked, with one
           highlighter. By night it is the room: black, haze, and a fan
           of green lasers from the same point the printed rays came from.

           Everything is scoped under #nc with the page's own tokens. The
           shared es-* reveal system (marketing.css, marketing-home.js)
           still drives the entrances.
           ============================================================== */

        @property --nc-sweep {
            syntax: '<angle>';
            inherits: true;
            initial-value: 0deg;
        }

        #nc {
            --nc-paper: #f4f4f0;
            --nc-paper-2: #e8e8e3;
            --nc-card: #ffffff;
            --nc-ink: #0a0a0a;
            --nc-ink-2: #2b2b29;
            --nc-ink-3: #4f4f4c;
            --nc-rule: #0a0a0a;
            --nc-soft: rgba(10, 10, 10, 0.2);
            --nc-laser: #00ff66;
            --nc-red: #c21a12;
            --nc-block: #0a0a0a;
            --nc-on-block: #f4f4f0;
            --nc-on-block-2: #c9cac4;
            --nc-pop: #00ff66;
            --nc-on-pop: #0a0a0a;
            --nc-on-pop-2: #0f2a18;
            --nc-focus: #0a0a0a;
            --nc-ray: rgba(10, 10, 10, 0.62);
            --nc-halo: rgba(10, 10, 10, 0);
            --nc-display: 'Dela Gothic One', 'Arial Black', 'Helvetica Neue', Impact, sans-serif;
            --nc-text: 'Kanit', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --nc-mono: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
            /* Toner: sparse black speck for paper, and a mask that drops specks out of solid ink. */
            --nc-speck: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='s' x='0' y='0' width='100%25' height='100%25'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.95' numOctaves='2' seed='11' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 -38 0 0 0 8.3'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23s)'/%3E%3C/svg%3E");
            --nc-wear: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='340' height='340'%3E%3Cfilter id='w' x='0' y='0' width='100%25' height='100%25'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.7' numOctaves='2' seed='4' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 34 0 0 0 -8.2'/%3E%3C/filter%3E%3Crect width='340' height='340' filter='url(%23w)'/%3E%3C/svg%3E");
            position: relative;
            background: var(--nc-paper);
            color: var(--nc-ink);
            font-family: var(--nc-text);
            font-size: 1.0625rem;
            line-height: 1.55;
        }
        .dark #nc {
            --nc-paper: #050505;
            --nc-paper-2: #0a0b0a;
            --nc-card: #0f110f;
            --nc-ink: #f4f4f0;
            --nc-ink-2: #cfd0ca;
            --nc-ink-3: #a3a69f;
            --nc-rule: rgba(244, 244, 240, 0.3);
            --nc-soft: rgba(244, 244, 240, 0.16);
            --nc-red: #ff4b45;
            --nc-block: #0c0e0c;
            --nc-on-block: #f4f4f0;
            --nc-on-block-2: #c0c2bb;
            --nc-pop: #06100a;
            --nc-on-pop: #f4f4f0;
            --nc-on-pop-2: #c0c2bb;
            --nc-focus: #00ff66;
            --nc-ray: rgba(0, 255, 102, 0.95);
            --nc-halo: rgba(0, 255, 102, 0.16);
        }

        /* The bar above is the top edge of the sheet by day and of the room by night. */
        body > header.sticky {
            background-color: rgba(244, 244, 240, 0.9);
            border-bottom-color: #0a0a0a;
        }
        .dark body > header.sticky {
            background-color: rgba(5, 5, 5, 0.86);
            border-bottom-color: rgba(0, 255, 102, 0.28);
        }

        #nc ::selection { background: #00ff66; color: #050505; }
        #nc a:focus-visible,
        #nc summary:focus-visible,
        #nc input:focus-visible {
            outline: 3px solid var(--nc-focus);
            outline-offset: 3px;
        }
        #nc .nc-block a:focus-visible,
        #nc .nc-screen a:focus-visible { outline-color: #00ff66; }

        .nc-wrap { position: relative; z-index: 1; width: min(100% - 2.5rem, 80rem); margin-inline: auto; }
        .nc-sec { position: relative; overflow: clip; padding-block: clamp(4.5rem, 9vw, 8rem); }

        /* Paper sections carry what a copier adds: the lid shadow down both edges,
           a faint drum streak, and toner speck. None of it survives into the night. */
        .nc-paper::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(90deg, rgba(10, 10, 10, 0.13), transparent 2.4%, transparent 97.4%, rgba(10, 10, 10, 0.17)),
                linear-gradient(90deg, transparent 71.3%, rgba(10, 10, 10, 0.04) 71.4% 71.9%, transparent 72%),
                var(--nc-speck);
            opacity: 0.7;
        }
        .dark .nc-paper::before { display: none; }
        .nc-alt { background: var(--nc-paper-2); }

        /* Type voices: the flyer's heavy wide caps, the typed small print. */
        .nc-d {
            font-family: var(--nc-display);
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: -0.015em;
            line-height: 1.04;
        }
        .nc-m {
            font-family: var(--nc-mono);
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.35;
        }
        .nc-h2 { font-size: clamp(1.85rem, 5vw, 4.1rem); text-wrap: balance; }
        .nc-head { display: grid; justify-items: start; gap: 1.4rem; margin-bottom: clamp(2.5rem, 5vw, 4.25rem); }
        /* The heading takes the whole row instead of shrinking to its words: Firefox measures a
           shrunk heading without the highlight's outdent and then breaks "Three steps" in two. */
        .nc-head .nc-h2 { justify-self: stretch; }
        .nc-sub { max-width: 40rem; font-size: 1.15rem; color: var(--nc-ink-2); }

        /* A strip of type, cut out and stuck down a degree or two off true. */
        .nc-strip {
            display: inline-block;
            padding: 0.42rem 0.75rem 0.36rem;
            background: var(--nc-ink);
            color: var(--nc-paper);
            font-family: var(--nc-mono);
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.3;
            rotate: var(--r, -1.6deg);
        }
        .dark #nc .nc-strip {
            background: transparent;
            color: var(--nc-laser);
            box-shadow: inset 0 0 0 1.5px var(--nc-laser);
        }
        #nc .nc-block .nc-strip { background: #f4f4f0; color: #0a0a0a; box-shadow: none; }
        .dark #nc .nc-block .nc-strip { background: transparent; color: var(--nc-laser); box-shadow: inset 0 0 0 1.5px var(--nc-laser); }
        #nc .nc-popband .nc-strip { background: #0a0a0a; color: #f4f4f0; }

        /* The highlighter by day; by night the same words are simply lit. */
        .nc-hl {
            padding: 0 0.14em;
            margin: 0 -0.14em;
            color: #0a0a0a;
            background-image: linear-gradient(#00ff66, #00ff66);
            background-repeat: no-repeat;
            background-size: 100% 86%;
            background-position: 0 62%;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
        .dark #nc .nc-hl {
            background-image: none;
            color: var(--nc-laser);
            -webkit-text-stroke: 1.5px var(--nc-laser);
            text-shadow: 0 0 0.55em rgba(0, 255, 102, 0.5);
        }
        /* On the green stock the marker is black, like the type: it starts a hair lower so a
           wrapped heading's first line does not fuse with it ("Three nights," on a phone). */
        #nc .nc-popband .nc-hl { background-image: linear-gradient(#0a0a0a, #0a0a0a); background-size: 100% 80%; background-position: 0 73%; color: #00ff66; }
        .dark #nc .nc-popband .nc-hl { background-image: none; color: var(--nc-laser); }
        @supports (animation-timeline: view()) {
            /* The swipe only where the words read without it: on paper. On toner and on the
               green stock the marker is already down. At night every one of them lights up. */
            html.es-anim:not(.dark) #nc .nc-paper .nc-hl:not(.nc-hl-now),
            html.es-anim.dark #nc .nc-hl:not(.nc-hl-now) {
                animation: nc-swipe linear both;
                animation-timeline: view();
                animation-range: entry 15% cover 38%;
            }
            html.es-anim.dark #nc .nc-hl:not(.nc-hl-now) { animation-name: nc-lightup; }
        }
        @keyframes nc-swipe { from { background-size: 0% 86%; } to { background-size: 100% 86%; } }
        @keyframes nc-lightup {
            from { color: transparent; text-shadow: 0 0 0 rgba(0, 255, 102, 0); }
            to { color: #00ff66; text-shadow: 0 0 0.55em rgba(0, 255, 102, 0.5); }
        }

        /* Buttons: a block of toner with one corner cut. */
        .nc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1.05rem 1.3rem 1rem;
            background: var(--nc-ink);
            color: var(--nc-paper);
            font-family: var(--nc-display);
            font-size: 0.9rem;
            letter-spacing: 0.01em;
            text-transform: uppercase;
            line-height: 1.15;
            text-align: center;
            clip-path: polygon(0 0, calc(100% - 0.9rem) 0, 100% 0.9rem, 100% 100%, 0 100%);
            transition: background-color 0.18s ease, color 0.18s ease, translate 0.18s ease;
        }
        .nc-btn:hover { background: #00ff66; color: #0a0a0a; translate: 0 -2px; }
        .nc-btn svg { flex: none; width: 1.15rem; height: 1.15rem; transition: translate 0.18s ease; }
        .nc-btn:hover svg { translate: 4px 0; }
        .dark #nc .nc-btn { background: #00ff66; color: #050505; }
        .dark #nc .nc-btn:hover { background: #f4f4f0; }
        #nc .nc-btn-ghost {
            background: transparent;
            color: var(--nc-ink);
            box-shadow: inset 0 0 0 2px var(--nc-ink);
            clip-path: none;
        }
        #nc .nc-btn-ghost:hover { background: var(--nc-ink); color: var(--nc-paper); translate: none; }
        #nc .nc-btn-ghost:hover svg { translate: 0 4px; }
        .dark #nc .nc-btn-ghost { background: transparent; color: var(--nc-ink); box-shadow: inset 0 0 0 2px rgba(244, 244, 240, 0.7); }
        .dark #nc .nc-btn-ghost:hover { background: #f4f4f0; color: #050505; }
        .nc-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            /* The top padding, taken back by the margin, makes the link a 24px target without moving it. */
            padding-block: 0.15rem 0.2rem;
            margin-top: -0.15rem;
            border-bottom: 3px solid var(--nc-laser);
            transition: gap 0.2s ease;
        }
        .nc-more:hover { gap: 0.9rem; }
        .nc-more svg { width: 1rem; height: 1rem; }

        /* Price-sticker dots for the plan a thing lives on. */
        .nc-tier {
            display: inline-block;
            flex: none;
            padding: 0.2rem 0.6rem 0.14rem;
            border-radius: 999px;
            background: #00ff66;
            color: #0a0a0a;
            font-family: var(--nc-mono);
            font-weight: 700;
            font-size: 0.68rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.3;
            rotate: -4deg;
        }
        .nc-tier-pro { background: var(--nc-ink); color: var(--nc-paper); rotate: 3deg; }
        #nc .nc-block .nc-tier-pro { background: #f4f4f0; color: #0a0a0a; }
        .nc-item-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 0.9rem; }
        .nc-item-head h3 { font-size: clamp(1.1rem, 1.75vw, 1.45rem); }

        /* ---------------------------------------------------------------
           Section index, wide screens only
           --------------------------------------------------------------- */
        /* The nav is a box the size of the page that clips the rail, so the rail stays fixed to
           the screen and still ends where the page does instead of riding over the site footer. */
        .nc-index { display: none; }
        @media (min-width: 1560px) {
            .nc-index {
                display: block;
                position: absolute;
                inset: 0;
                z-index: 40;
                clip-path: inset(0);
                pointer-events: none;
            }
            .nc-index ul { position: fixed; right: 1.25rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.2rem; justify-items: end; pointer-events: auto; }
            .nc-index a {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: 0.6rem;
                padding: 0.32rem 0;
                color: var(--nc-ink-3);
            }
            .nc-index a::after {
                content: "";
                width: 0.8rem;
                height: 3px;
                background: currentColor;
                transition: width 0.3s cubic-bezier(0.22, 1, 0.36, 1), background-color 0.3s ease;
            }
            .nc-index a span {
                padding: 0.15rem 0.4rem 0.1rem;
                background: var(--nc-paper);
                opacity: 0;
                translate: 0.3rem 0;
                transition: opacity 0.2s ease, translate 0.2s ease;
            }
            .nc-index a:hover span,
            .nc-index a:focus-visible span { opacity: 1; translate: 0 0; }
            .nc-index a.is-active { color: var(--nc-ink); }
            .nc-index a.is-active::after { width: 2rem; background: var(--nc-laser); box-shadow: 0 0 0 1px var(--nc-ink); }
            .dark .nc-index a.is-active::after { box-shadow: 0 0 0.7rem rgba(0, 255, 102, 0.8); }
        }

        /* ---------------------------------------------------------------
           Lasers and haze. Printed rays by day, light by night.
           --------------------------------------------------------------- */
        .nc-laser {
            position: absolute;
            inset: 0;
            pointer-events: none;
            --o: 88% -7%;
            background-image: conic-gradient(from calc(186deg + var(--nc-sweep)) at var(--o),
                transparent 0deg,
                var(--nc-halo) 0.2deg, var(--nc-ray) 0.42deg, var(--nc-halo) 0.64deg, transparent 0.84deg,
                transparent 6deg, var(--nc-halo) 6.2deg, var(--nc-ray) 6.38deg, var(--nc-halo) 6.56deg, transparent 6.76deg,
                transparent 11deg, var(--nc-halo) 11.2deg, var(--nc-ray) 11.5deg, var(--nc-halo) 11.8deg, transparent 12deg,
                transparent 19deg, var(--nc-halo) 19.2deg, var(--nc-ray) 19.36deg, var(--nc-halo) 19.52deg, transparent 19.72deg,
                transparent 25deg, var(--nc-halo) 25.2deg, var(--nc-ray) 25.5deg, var(--nc-halo) 25.8deg, transparent 26deg,
                transparent 34deg, var(--nc-halo) 34.2deg, var(--nc-ray) 34.36deg, var(--nc-halo) 34.52deg, transparent 34.72deg,
                transparent 41deg, var(--nc-halo) 41.2deg, var(--nc-ray) 41.46deg, var(--nc-halo) 41.72deg, transparent 41.92deg,
                transparent 52deg, var(--nc-halo) 52.2deg, var(--nc-ray) 52.38deg, var(--nc-halo) 52.56deg, transparent 52.76deg,
                transparent 61deg, var(--nc-halo) 61.2deg, var(--nc-ray) 61.44deg, var(--nc-halo) 61.68deg, transparent 61.88deg,
                transparent 70deg, var(--nc-halo) 70.2deg, var(--nc-ray) 70.36deg, var(--nc-halo) 70.52deg, transparent 70.72deg,
                transparent 360deg);
            -webkit-mask-image: radial-gradient(62% 86% at var(--o), #000 0 30%, transparent 82%);
            mask-image: radial-gradient(62% 86% at var(--o), #000 0 30%, transparent 82%);
        }
        @media (max-width: 759px) {
            html:not(.dark) #nc .nc-hero .nc-laser {
                -webkit-mask-image: radial-gradient(95% 34% at var(--o), #000 0 30%, transparent 86%);
                mask-image: radial-gradient(95% 34% at var(--o), #000 0 30%, transparent 86%);
            }
        }
        .dark .nc-laser {
            mix-blend-mode: screen;
            -webkit-mask-image: radial-gradient(135% 150% at var(--o), #000 0 22%, transparent 84%);
            mask-image: radial-gradient(135% 150% at var(--o), #000 0 22%, transparent 84%);
        }
        .nc-laser-left { --o: 9% -9%; background-image: conic-gradient(from calc(104deg + var(--nc-sweep)) at var(--o),
                transparent 0deg,
                var(--nc-halo) 0.2deg, var(--nc-ray) 0.42deg, var(--nc-halo) 0.64deg, transparent 0.84deg,
                transparent 8deg, var(--nc-halo) 8.2deg, var(--nc-ray) 8.38deg, var(--nc-halo) 8.56deg, transparent 8.76deg,
                transparent 15deg, var(--nc-halo) 15.2deg, var(--nc-ray) 15.5deg, var(--nc-halo) 15.8deg, transparent 16deg,
                transparent 27deg, var(--nc-halo) 27.2deg, var(--nc-ray) 27.36deg, var(--nc-halo) 27.52deg, transparent 27.72deg,
                transparent 36deg, var(--nc-halo) 36.2deg, var(--nc-ray) 36.5deg, var(--nc-halo) 36.8deg, transparent 37deg,
                transparent 48deg, var(--nc-halo) 48.2deg, var(--nc-ray) 48.36deg, var(--nc-halo) 48.52deg, transparent 48.72deg,
                transparent 59deg, var(--nc-halo) 59.2deg, var(--nc-ray) 59.46deg, var(--nc-halo) 59.72deg, transparent 59.92deg,
                transparent 68deg, var(--nc-halo) 68.2deg, var(--nc-ray) 68.38deg, var(--nc-halo) 68.56deg, transparent 68.76deg,
                transparent 360deg); }
        html.es-anim.dark #nc .nc-laser.is-live { animation: nc-sweep 13s ease-in-out infinite alternate; }
        html.es-anim.dark #nc .nc-laser-left.is-live { animation-duration: 17s; animation-direction: alternate-reverse; }
        @keyframes nc-sweep { from { --nc-sweep: -7deg; } to { --nc-sweep: 8deg; } }
        /* A fixed-dark block needs the light version of the rays in both modes. */
        .nc-block .nc-laser { --nc-ray: rgba(0, 255, 102, 0.95); --nc-halo: rgba(0, 255, 102, 0.16); mix-blend-mode: screen; }

        .nc-haze { display: none; }
        .dark .nc-haze {
            display: block;
            position: absolute;
            inset: -20% -10%;
            pointer-events: none;
            background:
                radial-gradient(38% 46% at 78% 18%, rgba(0, 255, 102, 0.16), transparent 70%),
                radial-gradient(44% 40% at 18% 78%, rgba(0, 255, 102, 0.07), transparent 70%),
                radial-gradient(30% 34% at 52% 46%, rgba(244, 244, 240, 0.05), transparent 70%);
        }
        html.es-anim.dark #nc .nc-haze { animation: nc-drift 28s ease-in-out infinite alternate; }
        @keyframes nc-drift { from { translate: -2% -1%; } to { translate: 3% 2%; } }

        /* ---------------------------------------------------------------
           Hero: the flyer itself
           --------------------------------------------------------------- */
        .nc-hero { position: relative; overflow: clip; }
        .nc-hero-in { padding-block: clamp(2.5rem, 5vw, 4.5rem) clamp(3rem, 5vw, 4.5rem); }
        .nc-crop { display: none; }
        @media (min-width: 900px) {
            /* Printer's crop marks, as left on the master. */
            .nc-crop { display: block; position: absolute; inset: 1.1rem 1.1rem auto; height: 1.4rem; pointer-events: none; z-index: 1; }
            .nc-crop::before,
            .nc-crop::after {
                content: "";
                position: absolute;
                top: 0;
                width: 1.4rem;
                height: 1.4rem;
                background: linear-gradient(var(--nc-ink), var(--nc-ink)) 50% 0 / 1.5px 100% no-repeat, linear-gradient(var(--nc-ink), var(--nc-ink)) 0 50% / 100% 1.5px no-repeat;
                opacity: 0.55;
            }
            .nc-crop::before { left: 0; }
            .nc-crop::after { right: 0; }
        }
        .nc-hero-type { position: relative; container-type: inline-size; }
        .nc-eyebrow { margin-bottom: 1.6rem; --r: -1.2deg; }
        .dark #nc .nc-eyebrow { background: #00ff66; color: #050505; box-shadow: none; }
        .nc-h1 { font-size: clamp(2.35rem, 12.2cqi, 3.6rem); text-wrap: balance; }
        @media (min-width: 760px) {
            .nc-h1 { font-size: 7.9cqi; text-wrap: wrap; }
            .nc-h1 .es-mask-line { white-space: nowrap; }
        }
        .nc-h1 .es-mask { padding-bottom: 0.1em; margin-bottom: -0.1em; padding-inline: 0.16em; margin-inline: -0.16em; }
        html.es-anim #nc .nc-mask-3 .es-mask-line { animation-delay: 0.42s; }
        /* By day the headline is solid toner with the odd dropout. */
        html:not(.dark) #nc .nc-h1 .es-mask { -webkit-mask-image: var(--nc-wear); mask-image: var(--nc-wear); }
        .dark #nc .nc-h1 { text-shadow: 0 0 1.2em rgba(244, 244, 240, 0.16); }

        .nc-hero-foot { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem; margin-top: clamp(1.75rem, 3.5vw, 2.75rem); align-items: end; }
        @media (min-width: 980px) { .nc-hero-foot { grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 3rem; } }
        .nc-lede { max-width: 37rem; font-size: clamp(1.1rem, 1.55vw, 1.3rem); color: var(--nc-ink-2); }
        .nc-cta { display: flex; flex-wrap: wrap; gap: 0.9rem 1rem; margin-top: 1.9rem; }

        /* The hand stamp. Rough ink by day; under the lights it glows. */
        .nc-stamp {
            position: relative;
            display: grid;
            place-items: center;
            width: var(--w, 11rem);
            aspect-ratio: 1;
            border-radius: 50%;
            border: 0.3rem solid currentColor;
            color: var(--nc-ink);
            rotate: var(--r, -12deg);
            -webkit-mask-image: var(--nc-wear);
            mask-image: var(--nc-wear);
        }
        .nc-stamp::before { content: ""; position: absolute; inset: 0.42rem; border-radius: 50%; border: 0.1rem solid currentColor; }
        .nc-stamp svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        /* In the markup textLength is given twice on purpose: Firefox reads it only from the text
           element, Safari only from the textPath, and without it the ring stops part of the way round. */
        .nc-stamp text { font-family: var(--nc-mono); font-weight: 700; font-size: 11.5px; text-transform: uppercase; fill: currentColor; }
        .nc-stamp b { font-family: var(--nc-display); font-weight: 400; font-size: calc(var(--w, 11rem) * 0.27); line-height: 1; letter-spacing: -0.02em; }
        .dark #nc .nc-stamp {
            color: #00ff66;
            -webkit-mask-image: none;
            mask-image: none;
            filter: drop-shadow(0 0 0.5rem rgba(0, 255, 102, 0.6));
        }
        html.es-anim #nc [data-reveal="stamp"] { transition-duration: 0.32s; transition-timing-function: cubic-bezier(0.3, 1.4, 0.5, 1); }
        html.es-anim #nc [data-reveal="stamp"]:not(.is-revealed) { transform: scale(1.55); }
        .nc-hero-stamp { display: none; }
        @media (min-width: 760px) {
            .nc-hero-stamp { display: grid; position: absolute; right: 3.5%; top: 60%; translate: 0 -50%; --w: 14.5cqi; --r: 11deg; }
        }
        html.es-anim #nc .nc-hero-stamp { animation: nc-thump 0.34s cubic-bezier(0.3, 1.4, 0.5, 1) 1.15s both; }
        @keyframes nc-thump { from { opacity: 0; scale: 1.6; } to { opacity: 1; scale: 1; } }

        /* The clicker: a tally counter in the doorman's fist. */
        .nc-count { display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem 1.75rem; }
        .nc-clicker {
            position: relative;
            flex: none;
            display: grid;
            place-items: center;
            width: 9.75rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                radial-gradient(circle at 50% 50%, rgba(0, 0, 0, 0) 61%, rgba(0, 0, 0, 0.5) 62%, rgba(255, 255, 255, 0.55) 64%, rgba(0, 0, 0, 0) 66%),
                conic-gradient(from 35deg, #f6f6f6, #8e8e8e 12%, #e9e9e9 24%, #6f6f6f 38%, #f1f1f1 52%, #7f7f7f 66%, #dedede 80%, #8a8a8a 92%, #f6f6f6);
            box-shadow: 0 0 0 2px #0a0a0a, 0.5rem 0.6rem 0 rgba(10, 10, 10, 0.2);
            rotate: -7deg;
        }
        .dark .nc-clicker { box-shadow: 0 0 0 1px rgba(244, 244, 240, 0.3), 0 0 2.2rem rgba(0, 255, 102, 0.18); }
        .nc-clicker::before {
            content: "";
            position: absolute;
            top: -1.15rem;
            left: 50%;
            width: 2.3rem;
            height: 1.7rem;
            translate: -50% 0;
            border-radius: 0.5rem 0.5rem 0.15rem 0.15rem;
            background: linear-gradient(90deg, #6f6f6f, #f1f1f1 40%, #9a9a9a 70%, #5d5d5d);
            box-shadow: 0 0 0 2px #0a0a0a;
            z-index: -1;
        }
        .nc-clicker::after {
            content: "";
            position: absolute;
            left: -1.5rem;
            top: 50%;
            width: 3.2rem;
            aspect-ratio: 1;
            translate: 0 -50%;
            border-radius: 50%;
            border: 0.42rem solid #a9a9a9;
            box-shadow: 0 0 0 2px #0a0a0a, inset 0 0 0 2px #0a0a0a;
            z-index: -1;
        }
        .nc-wheels {
            display: flex;
            gap: 2px;
            padding: 4px;
            border-radius: 0.3rem;
            background: #0a0a0a;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
            font-family: var(--nc-mono);
            font-weight: 700;
            font-size: var(--fs, 1.9rem);
            line-height: 1.25;
            color: #f4f4f0;
        }
        .nc-wheels span {
            display: block;
            width: 0.82em;
            height: 1.25em;
            overflow: hidden;
            text-align: center;
            background: linear-gradient(#0a0a0a, #33342f 48%, #33342f 52%, #0a0a0a);
        }
        .nc-wheels i {
            display: block;
            font-style: normal;
            white-space: pre;
            translate: 0 calc(var(--n) * -1.25em);
            transition: translate 1.5s cubic-bezier(0.2, 0.9, 0.2, 1);
            transition-delay: calc(var(--i, 0) * 0.12s + 0.25s);
        }
        html.es-anim #nc [data-reveal]:not(.is-revealed) .nc-wheels i { translate: 0 0; }
        html.es-anim #nc .nc-hero .nc-wheels i { animation: nc-roll 1.6s cubic-bezier(0.2, 0.9, 0.2, 1) calc(var(--i, 0) * 0.12s + 0.7s) both; }
        @keyframes nc-roll { from { translate: 0 0; } }
        .nc-count-read { display: grid; gap: 0.55rem; min-width: 0; }
        .nc-count-of { font-family: var(--nc-mono); font-weight: 700; font-size: 1.9rem; letter-spacing: -0.02em; line-height: 1; }
        .nc-count-read p { max-width: 15rem; color: var(--nc-ink-3); font-size: 0.95rem; line-height: 1.4; }
        .nc-pills { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .nc-pill { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.3rem 0.6rem 0.24rem; box-shadow: inset 0 0 0 1.5px var(--nc-ink); }
        .nc-pill-on { background: #00ff66; color: #0a0a0a; box-shadow: none; }
        .nc-pill-on::before { content: ""; width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: #0a0a0a; }
        .dark .nc-pill { box-shadow: inset 0 0 0 1.5px rgba(244, 244, 240, 0.5); }
        .dark .nc-pill-on { box-shadow: 0 0 1.1rem rgba(0, 255, 102, 0.45); }
        .nc-count .nc-stamp { --w: 6.4rem; --r: 14deg; margin-inline-start: auto; margin-top: -1.5rem; }
        @media (max-width: 759px) {
            .nc-count { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 1rem 1.4rem; padding-inline-start: 1.2rem; }
            .nc-clicker { width: 8rem; }
            .nc-clicker .nc-wheels { --fs: 1.55rem; }
            .nc-count .nc-stamp { grid-column: 1 / -1; justify-self: end; margin-top: -0.4rem; }
        }
        @media (min-width: 760px) { .nc-count .nc-stamp { display: none; } }

        /* The tape across the foot of the flyer: every kind of night. */
        .nc-tape {
            position: relative;
            z-index: 1;
            width: 104%;
            margin-inline: -2%;
            padding-block: 0.8rem 0.65rem;
            background: var(--nc-block);
            color: var(--nc-on-block);
            rotate: -1.1deg;
            margin-bottom: 1.4rem;
        }
        .dark .nc-tape { box-shadow: 0 -1.5px 0 #00ff66, 0 1.5px 0 #00ff66, 0 0 2.5rem rgba(0, 255, 102, 0.25); }
        .nc-tape .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .nc-tape-word { display: inline-flex; align-items: center; gap: 1.5rem; padding-inline-end: 1.5rem; font-size: clamp(1.15rem, 2.2vw, 1.7rem); white-space: nowrap; }
        .nc-tape-word::after { content: "//"; font-family: var(--nc-mono); font-weight: 700; color: #00ff66; }
        @media (prefers-reduced-motion: reduce) { .nc-tape { rotate: none; } .nc-tape .es-marquee-track { row-gap: 0.3rem; } }

        /* ---------------------------------------------------------------
           Blocks: solid toner by day, a lit corner of the room by night
           --------------------------------------------------------------- */
        .nc-block { background: var(--nc-block); color: var(--nc-on-block); }
        html:not(.dark) #nc .nc-block::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: var(--nc-speck);
            filter: invert(1);
            opacity: 0.4;
        }
        .nc-block .nc-sub,
        .nc-block p { color: var(--nc-on-block-2); }
        .nc-three { display: grid; grid-template-columns: minmax(0, 1fr); border-block: 3px solid var(--nc-on-block); }
        .dark .nc-three { border-block-color: rgba(244, 244, 240, 0.35); }
        @media (min-width: 860px) { .nc-three { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .nc-dec { padding: 2rem 1.5rem 2.1rem; }
        .nc-dec:first-child { padding-inline-start: 0; }
        .nc-dec + .nc-dec { border-top: 1px solid rgba(244, 244, 240, 0.3); }
        @media (max-width: 859px) { .nc-dec { padding-inline: 0; } }
        @media (min-width: 860px) { .nc-dec + .nc-dec { border-top: 0; border-inline-start: 1px solid rgba(244, 244, 240, 0.3); } }
        .nc-dec-k { color: #00ff66; }
        .nc-dec h3 { margin-block: 0.9rem 0.8rem; font-size: clamp(1.3rem, 2.3vw, 1.9rem); text-wrap: balance; }
        .nc-dec h3 span { color: #00ff66; }
        .nc-dec p { max-width: 22rem; }
        .nc-dec-foot { margin-top: 2.25rem; }
        #nc .nc-dec-foot a { display: inline-flex; align-items: center; gap: 0.4rem; margin-inline-start: 0.5rem; color: #f4f4f0; font-weight: 700; border-bottom: 3px solid #00ff66; transition: gap 0.2s ease; }
        #nc .nc-dec-foot a:hover { gap: 0.75rem; }
        .nc-dec-foot svg { width: 1rem; height: 1rem; }
        /* The queue: heads in a line, thinning out toward the back. */
        .nc-queue {
            position: relative;
            z-index: 1;
            height: 1.1rem;
            margin-top: clamp(2.5rem, 5vw, 4rem);
            background: radial-gradient(circle at 50% 50%, #f4f4f0 0 0.3rem, transparent 0.34rem) 0 50% / 1.35rem 1.1rem repeat-x;
            -webkit-mask-image: linear-gradient(90deg, #000 0 18%, rgba(0, 0, 0, 0.12) 92%);
            mask-image: linear-gradient(90deg, #000 0 18%, rgba(0, 0, 0, 0.12) 92%);
        }
        .nc-queue::before { content: ""; position: absolute; inset: 0 auto 0 0; width: calc(1.35rem * 8); background: radial-gradient(circle at 50% 50%, #00ff66 0 0.3rem, transparent 0.34rem) 0 50% / 1.35rem 1.1rem repeat-x; }

        /* ---------------------------------------------------------------
           Paste-up cards: sheets of copy paper, cut by hand
           --------------------------------------------------------------- */
        .nc-sheet {
            position: relative;
            background: var(--nc-card);
            box-shadow: inset 0 0 0 2px var(--nc-rule), 0.45rem 0.55rem 0 var(--nc-soft);
        }
        .dark .nc-sheet { box-shadow: inset 0 0 0 1px rgba(244, 244, 240, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.6); }

        /* The list and the door price */
        .nc-entry { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 3.5rem; }
        @media (min-width: 1000px) {
            .nc-entry { grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); grid-template-areas: "a b" "a c"; align-items: start; }
            .nc-entry-a { grid-area: a; }
            .nc-entry-b { grid-area: b; }
            .nc-entry-c { grid-area: c; }
        }
        .nc-item > p { margin-top: 0.9rem; color: var(--nc-ink-2); }
        .nc-list { margin-top: 1.75rem; padding: 1.4rem 1.3rem 1.2rem; rotate: -1.3deg; }
        .nc-list-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 3px solid var(--nc-rule); }
        .nc-list-top b { font-family: var(--nc-display); font-weight: 400; font-size: 1.35rem; text-transform: uppercase; line-height: 1; }
        .nc-list-row { display: grid; grid-template-columns: 1.1rem minmax(0, 1fr) auto; align-items: center; gap: 0.8rem; padding-block: 0.62rem; border-bottom: 1px solid var(--nc-soft); }
        .nc-list-row i { width: 1.1rem; aspect-ratio: 1; box-shadow: inset 0 0 0 2px var(--nc-ink); }
        .nc-list-row.is-in i { background: linear-gradient(var(--nc-ink), var(--nc-ink)) 50% 50% / 55% 55% no-repeat; }
        .nc-list-row u { height: 0.72rem; width: var(--w, 60%); background: var(--nc-ink); text-decoration: none; }
        .dark .nc-list-row u { background: rgba(244, 244, 240, 0.55); }
        .nc-list-row.is-in span { color: var(--nc-ink-3); }
        .nc-list-row:not(.is-in) span { opacity: 0; }
        .nc-list-foot { display: flex; justify-content: space-between; gap: 1rem; padding-top: 0.85rem; }
        /* The count stays one chip: under 380px it broke into "59" over "LEFT". */
        .nc-list-foot span:last-child { flex: none; align-self: flex-start; padding: 0.1rem 0.4rem; background: #00ff66; color: #0a0a0a; white-space: nowrap; }

        .nc-board { margin-top: 1.75rem; padding: 1.2rem 1.3rem 0.5rem; rotate: 0.8deg; }
        .nc-board-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 0.85rem; border-bottom: 3px solid var(--nc-rule); }
        .nc-board-clock { padding: 0.25rem 0.55rem 0.15rem; background: #0a0a0a; color: #00ff66; font-family: var(--nc-mono); font-weight: 700; font-size: 1.15rem; letter-spacing: 0.06em; }
        .dark .nc-board-clock { box-shadow: 0 0 1.2rem rgba(0, 255, 102, 0.3); }
        .nc-board ul { display: grid; }
        .nc-board li { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 0.2rem 1.25rem; padding-block: 0.95rem 0.85rem; border-bottom: 1px solid var(--nc-soft); }
        .nc-board li:last-child { border-bottom: 0; }
        @media (min-width: 560px) { .nc-board li { grid-template-columns: minmax(0, 1fr) auto 8.4rem; } }
        .nc-board-name { font-family: var(--nc-display); font-size: clamp(1rem, 1.7vw, 1.3rem); text-transform: uppercase; line-height: 1.1; }
        .nc-board-price { font-family: var(--nc-mono); font-weight: 700; font-size: 1.3rem; }
        .nc-board-state { display: inline-flex; align-items: center; gap: 0.45rem; justify-self: start; }
        @media (min-width: 560px) { .nc-board-state { justify-self: end; } }
        .nc-board-state::before { content: ""; width: 0.55rem; aspect-ratio: 1; border-radius: 50%; background: #00ff66; box-shadow: 0 0 0 1.5px var(--nc-ink); }
        .dark .nc-board-state::before { box-shadow: 0 0 0.7rem rgba(0, 255, 102, 0.9); }
        .nc-board li.is-closed .nc-board-name,
        .nc-board li.is-closed .nc-board-price { color: var(--nc-ink-3); text-decoration: line-through; text-decoration-thickness: 0.14em; }
        .nc-board li.is-closed .nc-board-state { padding: 0.2rem 0.45rem 0.12rem; color: var(--nc-red); box-shadow: inset 0 0 0 2px var(--nc-red); rotate: -3deg; }
        .nc-board li.is-closed .nc-board-state::before { display: none; }
        .nc-six { display: flex; align-items: center; gap: 0.5rem; margin-top: 1.4rem; color: var(--nc-ink-3); }
        .nc-six i { width: 1.05rem; aspect-ratio: 1; border-radius: 50%; background: var(--nc-ink); }
        .nc-six i:nth-child(n + 6) { background: #00ff66; box-shadow: 0 0 0 2px var(--nc-ink); }
        .dark .nc-six i:nth-child(n + 6) { box-shadow: 0 0 0.8rem rgba(0, 255, 102, 0.8); }
        .nc-six span { margin-inline-start: 0.5rem; }

        /* ---------------------------------------------------------------
           The door itself: the scan and the count
           --------------------------------------------------------------- */
        .nc-scan { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem; align-items: center; }
        @media (min-width: 1000px) { .nc-scan { grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr); gap: 4.5rem; } }
        .nc-scan .nc-head { margin-bottom: 1.5rem; }
        .nc-scan-copy > p { max-width: 36rem; color: var(--nc-ink-2); font-size: 1.1rem; }
        .nc-ticks { display: grid; gap: 1rem; margin-top: 1.9rem; }
        .nc-ticks li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: 0.9rem; color: var(--nc-ink-2); }
        .nc-ticks li::before {
            content: "";
            width: 1.3rem;
            height: 1.3rem;
            margin-top: 0.18rem;
            background: #00ff66;
            box-shadow: 0 0 0 2px var(--nc-ink);
        }
        .dark .nc-ticks li::before { box-shadow: 0 0 0.9rem rgba(0, 255, 102, 0.7); }
        #nc .nc-ticks a,
        #nc .nc-clip a { color: var(--nc-ink); font-weight: 700; text-decoration: underline; text-decoration-color: #00ff66; text-decoration-thickness: 3px; text-underline-offset: 0.2em; }
        .nc-door { position: relative; padding-bottom: 2.5rem; }
        /* A screen is a screen at any hour: literal colours. */
        .nc-screen {
            position: relative;
            padding: 1.5rem 1.5rem 1.3rem;
            background: #060706;
            color: #eafff1;
            box-shadow: 0 0 0 2px #0a0a0a, 0.6rem 0.7rem 0 rgba(10, 10, 10, 0.2);
            rotate: 1.2deg;
        }
        .dark .nc-screen { box-shadow: 0 0 0 1px rgba(0, 255, 102, 0.4), 0 0 3.5rem rgba(0, 255, 102, 0.14); }
        .nc-screen-top { display: flex; justify-content: space-between; align-items: center; color: #b6c9bd; }
        .nc-live { display: inline-flex; align-items: center; gap: 0.45rem; color: #00ff66; }
        .nc-live::before { content: ""; width: 0.55rem; aspect-ratio: 1; border-radius: 50%; background: #00ff66; box-shadow: 0 0 0.7rem #00ff66; animation: nc-blip 1.8s ease-in-out infinite; }
        @keyframes nc-blip { 50% { opacity: 0.35; } }
        .nc-screen-big { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.75rem 1.1rem; margin-block: 1.4rem 1.5rem; }
        .nc-screen-big .nc-wheels { --fs: clamp(2.6rem, 6.5vw, 4.3rem); background: transparent; box-shadow: none; padding: 0; gap: 0.08em; color: #00ff66; text-shadow: 0 0 0.4em rgba(0, 255, 102, 0.55); }
        .nc-screen-big .nc-wheels span { background: none; width: 0.66em; }
        .nc-screen-big p { padding-bottom: 0.55rem; color: #b6c9bd; }
        .nc-tally { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: baseline; gap: 0.2rem 0.8rem; padding-block: 0.8rem; border-top: 1px solid rgba(234, 255, 241, 0.16); }
        .nc-tally span:first-child { font-weight: 700; }
        .nc-tally b { font-family: var(--nc-mono); font-size: 1.2rem; color: #eafff1; }
        .nc-tally small { color: #b6c9bd; font-family: var(--nc-mono); font-size: 0.8rem; }
        .nc-tally i { grid-column: 1 / -1; height: 0.45rem; margin-top: 0.35rem; background: linear-gradient(90deg, #00ff66 var(--p), rgba(234, 255, 241, 0.14) 0); }
        .nc-screen-foot { margin-top: 0.4rem; padding-top: 0.9rem; border-top: 1px solid rgba(234, 255, 241, 0.16); color: #b6c9bd; font-size: 0.9rem; }
        /* The ticket's code, half under the reader. */
        .nc-qr {
            position: absolute;
            left: -1.4rem;
            bottom: 0;
            width: clamp(6.2rem, 13vw, 8.4rem);
            padding: 0.55rem;
            background: #ffffff;
            color: #0a0a0a;
            box-shadow: 0 0 0 2px #0a0a0a, 0.4rem 0.5rem 0 rgba(10, 10, 10, 0.2);
            rotate: -8deg;
            overflow: hidden;
        }
        @media (max-width: 640px) { .nc-qr { left: auto; right: 0.5rem; } }
        .nc-qr svg { display: block; width: 100%; height: auto; }
        .nc-qr::after {
            content: "";
            position: absolute;
            inset-inline: 0;
            top: 12%;
            height: 3px;
            background: #00ff66;
            box-shadow: 0 0 0.8rem 0.15rem rgba(0, 255, 102, 0.8);
            animation: nc-scanline 2.8s ease-in-out infinite;
        }
        @keyframes nc-scanline { 50% { top: 84%; } }

        /* ---------------------------------------------------------------
           Who's playing: handbills pushed across the bar
           --------------------------------------------------------------- */
        .nc-play { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem; align-items: start; }
        @media (min-width: 1000px) { .nc-play { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 4.5rem; } }
        .nc-play-list { display: grid; }
        .nc-play-item { padding-block: 1.6rem 1.7rem; border-top: 1px solid rgba(244, 244, 240, 0.28); }
        .nc-play-item:first-child { border-top: 3px solid var(--nc-on-block); }
        .dark .nc-play-item:first-child { border-top-color: rgba(244, 244, 240, 0.4); }
        .nc-play-item p { margin-top: 0.8rem; max-width: 34rem; }
        .nc-reqs-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.6rem; }
        .nc-reqs-top b { font-family: var(--nc-display); font-weight: 400; font-size: 1.5rem; text-transform: uppercase; line-height: 1; }
        .nc-bills { display: grid; gap: 1.1rem; }
        .nc-bill {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.4rem 1rem;
            padding: 1.15rem 1.25rem 1.05rem;
            background: var(--bg, #ffffff);
            color: #0a0a0a;
            rotate: var(--r, -1deg);
            translate: var(--x, 0) 0;
            transition: rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .nc-bill:hover { rotate: 0deg; translate: 0 0; }
        #nc .nc-bill-name { color: #0a0a0a; font-family: var(--nc-display); font-size: clamp(1.35rem, 3vw, 2.1rem); text-transform: uppercase; line-height: 1; letter-spacing: -0.02em; overflow-wrap: anywhere; }
        #nc .nc-bill-meta { grid-column: 1; color: #2b2b29; }
        .nc-bill-ok { grid-column: 2; grid-row: 1 / span 2; padding: 0.4rem 0.6rem 0.3rem; color: #0a0a0a; box-shadow: inset 0 0 0 2.5px #0a0a0a; rotate: -7deg; }
        .nc-reqs-foot { margin-top: 1.6rem; font-size: 0.95rem; }

        /* ---------------------------------------------------------------
           Everything behind the door: six clippings
           --------------------------------------------------------------- */
        .nc-clips { column-gap: 2.25rem; }
        @media (min-width: 720px) { .nc-clips { columns: 2; } }
        @media (min-width: 1100px) { .nc-clips { columns: 3; } }
        .nc-clip {
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1.4rem 1.6rem;
            margin-bottom: 2.25rem;
            break-inside: avoid;
            rotate: var(--r, 0deg);
            transition: rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .nc-clip:hover { rotate: 0deg; }
        .nc-clip-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 0.9rem; margin-bottom: 1.1rem; border-bottom: 3px solid var(--nc-rule); color: var(--nc-ink-3); }
        .nc-clip h3 { font-size: clamp(1.1rem, 1.6vw, 1.35rem); text-wrap: balance; }
        .nc-clip p { margin-top: 0.9rem; color: var(--nc-ink-2); }
        .nc-clip p + p { padding-top: 0.9rem; border-top: 1px dashed var(--nc-soft); font-size: 0.95rem; color: var(--nc-ink-3); }

        /* ---------------------------------------------------------------
           Three nights: three flyers on coloured stock
           --------------------------------------------------------------- */
        .nc-popband { background: var(--nc-pop); color: var(--nc-on-pop); }
        .nc-popband .nc-sub { color: var(--nc-on-pop-2); }
        html:not(.dark) #nc .nc-popband::before { content: ""; position: absolute; inset: 0; pointer-events: none; background: var(--nc-speck); opacity: 0.6; }
        .nc-flyers { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.25rem; }
        @media (min-width: 860px) { .nc-flyers { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2rem; } }
        .nc-flyer {
            position: relative;
            container-type: inline-size;
            padding: 2.1rem 1.5rem 1.5rem;
            background: var(--bg);
            color: var(--fg);
            box-shadow: 0.55rem 0.65rem 0 rgba(10, 10, 10, 0.28);
            rotate: var(--r, 0deg);
            transition: rotate 0.35s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.35s ease;
        }
        .nc-flyer:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .dark .nc-flyer { box-shadow: 0 0 0 1px rgba(0, 255, 102, 0.35), 0 0 3rem rgba(0, 255, 102, 0.12); }
        /* A torn length of tape holds each one up. */
        .nc-flyer::before {
            content: "";
            position: absolute;
            top: -0.8rem;
            left: 50%;
            width: 5.5rem;
            height: 1.6rem;
            translate: -50% 0;
            rotate: var(--t, -3deg);
            background: rgba(244, 244, 240, 0.72);
            box-shadow: inset 0 0 0 1px rgba(10, 10, 10, 0.28);
            clip-path: polygon(0 12%, 4% 0, 9% 14%, 14% 2%, 100% 0, 97% 30%, 100% 58%, 96% 100%, 0 100%, 3% 60%);
        }
        .dark .nc-flyer::before { background: rgba(244, 244, 240, 0.16); }
        .nc-flyer-day { font-size: 31cqi; line-height: 0.92; letter-spacing: -0.04em; }
        .nc-flyer h3 { margin-top: 1.1rem; font-size: clamp(1.15rem, 7.4cqi, 1.6rem); }
        .nc-flyer p { margin-top: 0.5rem; }
        .nc-flyer-how { margin-top: 1.25rem; padding-top: 1rem; border-top: 2px dashed currentColor; }
        /* Only the black flyer lights up; paper under the lights is still paper. */
        .dark .nc-flyer-night .nc-flyer-day { color: #00ff66; text-shadow: 0 0 0.35em rgba(0, 255, 102, 0.5); }

        /* ---------------------------------------------------------------
           Perfect for: six wristbands off the roll
           --------------------------------------------------------------- */
        .nc-bands { display: grid; gap: 1rem; }
        .nc-band {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.5rem 1.75rem;
            align-items: center;
            padding: 1rem 4.6rem 1rem 1.1rem;
            min-height: 5.1rem;
            background: var(--bg);
            color: var(--fg);
            border-radius: 0.3rem 2.6rem 2.6rem 0.3rem;
            box-shadow: inset 0 0 0 2px #0a0a0a;
            rotate: var(--r, 0deg);
            transition: rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .dark .nc-band { box-shadow: inset 0 0 0 2px rgba(244, 244, 240, 0.4); }
        .nc-band:hover { rotate: 0deg; translate: 0.6rem 0; }
        @media (min-width: 900px) {
            .nc-band { grid-template-columns: 7.4rem minmax(0, 19rem) minmax(0, 1fr) auto; padding-inline-start: 0; }
        }
        /* The adhesive end, with its security cuts. */
        .nc-band::after {
            content: "";
            position: absolute;
            inset: 0 0 0 auto;
            width: 3.6rem;
            border-radius: 0 2.6rem 2.6rem 0;
            border-inline-start: 2px dashed var(--fg);
            background: repeating-linear-gradient(-58deg, transparent 0 0.34rem, var(--cut, rgba(10, 10, 10, 0.5)) 0.34rem 0.44rem);
            opacity: 0.6;
        }
        .nc-band-serial { display: grid; gap: 0.3rem; align-self: stretch; align-content: center; }
        @media (min-width: 900px) { .nc-band-serial { padding-inline: 1.1rem; border-inline-end: 2px dashed var(--fg); } }
        .nc-band-serial i { display: block; height: 0.9rem; max-width: 5rem; background: repeating-linear-gradient(90deg, var(--fg) 0 2px, transparent 2px 4px, var(--fg) 4px 5px, transparent 5px 9px, var(--fg) 9px 12px, transparent 12px 14px); }
        @media (max-width: 899px) { .nc-band-serial { display: flex; align-items: center; gap: 0.7rem; } .nc-band-serial i { width: 3.4rem; } }
        .nc-band h3 { font-size: clamp(1.1rem, 1.7vw, 1.4rem); text-wrap: balance; }
        .nc-band p { line-height: 1.45; }
        #nc .nc-band a { justify-self: start; white-space: nowrap; font-weight: 700; border-bottom: 3px solid currentColor; }
        #nc .nc-band a:focus-visible { outline-color: currentColor; }

        /* ---------------------------------------------------------------
           Three steps: three stamps on the back of the hand
           --------------------------------------------------------------- */
        .nc-steps { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.25rem 2.5rem; }
        @media (min-width: 860px) { .nc-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .nc-step .nc-stamp { --w: clamp(9rem, 15vw, 12.5rem); color: #f4f4f0; }
        .nc-step h3 { margin-top: 1.9rem; font-size: clamp(1.25rem, 2.1vw, 1.7rem); }
        .nc-step p { margin-top: 0.8rem; max-width: 23rem; }

        /* ---------------------------------------------------------------
           Key features: the small print down the side of the flyer
           --------------------------------------------------------------- */
        .nc-keys { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 980px) { .nc-keys { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); } }
        .nc-keys .nc-h2 { font-size: clamp(1.85rem, 3.5vw, 2.9rem); }
        .nc-keys .nc-more { margin-top: 1.45rem; }
        .nc-keys-list { display: grid; grid-template-columns: minmax(0, 1fr); border-top: 3px solid var(--nc-rule); }
        @media (min-width: 700px) { .nc-keys-list { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 2.5rem; } }
        .nc-key {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 1rem;
            padding: 1.35rem 0.6rem 1.25rem 0;
            border-bottom: 3px solid var(--nc-rule);
            transition: background-color 0.2s ease, color 0.2s ease, padding 0.25s ease;
        }
        .nc-key:hover { background: #00ff66; color: #0a0a0a; padding-inline: 0.9rem 0.6rem; }
        .nc-key b { display: block; font-family: var(--nc-display); font-weight: 400; font-size: 1.25rem; text-transform: uppercase; line-height: 1.1; }
        .nc-key small { display: block; margin-top: 0.4rem; font-size: 0.95rem; color: var(--nc-ink-2); }
        .nc-key:hover small { color: #0f2a18; }
        .nc-key svg { width: 1.4rem; height: 1.4rem; transition: translate 0.2s ease; }
        .nc-key:hover svg { translate: 0.3rem 0; }

        /* ---------------------------------------------------------------
           The shared plan band and closing strip, re-inked
           --------------------------------------------------------------- */
        #nc .nc-plans > section { background: var(--nc-paper-2); }
        #nc .nc-plans h2 { font-family: var(--nc-display); font-weight: 400; text-transform: uppercase; letter-spacing: -0.015em; line-height: 1.06; font-size: clamp(1.6rem, 3.6vw, 2.7rem); color: var(--nc-ink); }
        #nc .nc-plans h2 + p { color: var(--nc-ink-2); font-size: 1.0625rem; }
        #nc .nc-plans .grid > div { background: var(--nc-card); border: 0; border-radius: 0; box-shadow: inset 0 0 0 2px var(--nc-rule), 0.45rem 0.55rem 0 var(--nc-soft); }
        #nc .nc-plans .grid > div:nth-child(1) { rotate: -0.7deg; }
        #nc .nc-plans .grid > div:nth-child(3) { rotate: 0.6deg; }
        #nc .nc-plans .grid > div:hover { box-shadow: inset 0 0 0 2px var(--nc-rule), 0.45rem 0.55rem 0 #00ff66; }
        #nc .nc-plans .grid > div span,
        #nc .nc-plans .grid > div p,
        #nc .nc-plans .grid > div li { color: var(--nc-ink-2); }
        #nc .nc-plans .grid > div .text-3xl { font-family: var(--nc-display); font-weight: 400; font-size: 2.4rem; letter-spacing: -0.03em; color: var(--nc-ink); }
        #nc .nc-plans .grid > div .uppercase { font-family: var(--nc-mono); color: var(--nc-ink); }
        #nc .nc-plans .grid > div .rounded-full { background: #00ff66; color: #0a0a0a; font-family: var(--nc-mono); }
        #nc .nc-plans .grid > div svg { color: var(--nc-ink); }
        .dark #nc .nc-plans .grid > div svg { color: #00ff66; }
        #nc .nc-plans a.font-medium { color: var(--nc-ink); border-bottom: 3px solid #00ff66; }
        #nc .nc-plans a.rounded-2xl { background: var(--nc-ink); color: var(--nc-paper); border-radius: 0; box-shadow: none; font-family: var(--nc-display); font-weight: 400; font-size: 0.95rem; text-transform: uppercase; clip-path: polygon(0 0, calc(100% - 0.9rem) 0, 100% 0.9rem, 100% 100%, 0 100%); }
        #nc .nc-plans a.rounded-2xl:hover { background: #00ff66; color: #0a0a0a; }
        .dark #nc .nc-plans a.rounded-2xl { background: #00ff66; color: #050505; }
        .dark #nc .nc-plans a.rounded-2xl:hover { background: #f4f4f0; }

        #nc .nc-keep > section { background: var(--nc-paper); border-top: 3px solid var(--nc-rule); }
        #nc .nc-keep h2 { font-family: var(--nc-display); font-weight: 400; text-transform: uppercase; letter-spacing: -0.015em; font-size: clamp(1.5rem, 3vw, 2.2rem); line-height: 1.08; color: var(--nc-ink); }
        #nc .nc-keep p.uppercase { font-family: var(--nc-mono); font-weight: 700; letter-spacing: 0.16em; color: var(--nc-ink-3); }
        #nc .nc-keep .grid > a { background: var(--nc-card); border: 0; border-radius: 0; box-shadow: inset 0 0 0 2px var(--nc-rule); }
        #nc .nc-keep .grid > a:hover { box-shadow: inset 0 0 0 2px var(--nc-rule), 0.4rem 0.5rem 0 #00ff66; }
        #nc .nc-keep .grid > a > span:first-child { display: none; }
        #nc .nc-keep .grid > a h3 { color: var(--nc-ink); }
        #nc .nc-keep .grid > a p { color: var(--nc-ink-2); }
        #nc .nc-keep .grid > a > span:last-child,
        #nc .nc-keep a.self-start { color: var(--nc-ink); text-decoration: underline; text-decoration-color: #00ff66; text-decoration-thickness: 3px; text-underline-offset: 0.25em; }

        /* ---------------------------------------------------------------
           Related pages: take a number
           --------------------------------------------------------------- */
        .nc-tear { max-width: 46rem; margin-inline: auto; rotate: -0.8deg; }
        .nc-tear-top { padding: 2rem 1.75rem 2.25rem; display: grid; gap: 1.2rem; justify-items: start; }
        .nc-tear-top h2 { font-size: clamp(1.9rem, 5vw, 3.3rem); }
        .nc-tabs { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border-top: 2px dashed var(--nc-rule); }
        .nc-tab {
            display: flex;
            justify-content: center;
            align-items: center;
            height: clamp(12.5rem, 31vw, 15rem);
            padding: 1rem 0.2rem;
            transform-origin: 50% 0;
            transition: rotate 0.35s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.35s ease, background-color 0.2s ease, color 0.2s ease;
        }
        .nc-tab + .nc-tab { border-left: 2px dashed var(--nc-rule); }
        .nc-tab-in { display: block; writing-mode: vertical-rl; max-height: 100%; }
        .nc-tab-in > * { display: block; }
        .nc-tab:hover { rotate: var(--tr, -4deg); translate: 0 0.7rem; background: #00ff66; color: #0a0a0a; }
        .nc-tab b { font-family: var(--nc-display); font-weight: 400; font-size: clamp(0.8rem, 2.35vw, 1.3rem); text-transform: uppercase; line-height: 1.1; }
        .nc-tab-in span { margin-right: 0.45rem; color: var(--nc-ink-3); white-space: nowrap; }
        .nc-tab:hover .nc-tab-in span { color: #0f2a18; }

        /* ---------------------------------------------------------------
           Questions: the door policy, numbered
           --------------------------------------------------------------- */
        .nc-faq { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .nc-faq { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
            .nc-faq .nc-head { position: sticky; top: 6.5rem; margin-bottom: 0; }
        }
        .nc-faq .nc-h2 { font-size: clamp(1.85rem, 3.9vw, 3.1rem); }
        .nc-qa { border-top: 3px solid var(--nc-rule); }
        .nc-qa details { border-bottom: 3px solid var(--nc-rule); }
        .nc-qa summary { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr) 1.5rem; align-items: start; gap: 0.8rem; padding: 1.3rem 0.2rem 1.2rem; cursor: pointer; }
        .nc-qa summary > span { padding-top: 0.3rem; color: var(--nc-ink-3); font-size: 0.8rem; }
        .nc-qa h3 { font-size: 1.2rem; font-weight: 700; line-height: 1.3; }
        .nc-qa summary i { position: relative; width: 1.5rem; height: 1.5rem; margin-top: 0.1rem; }
        .nc-qa summary i::before,
        .nc-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1.5px) 0 auto 0; height: 3px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .nc-qa summary i::after { rotate: 90deg; }
        .nc-qa details[open] summary i::after { rotate: 0deg; }
        .nc-qa details[open] h3 span { background: linear-gradient(#00ff66, #00ff66) 0 82% / 100% 42% no-repeat; -webkit-box-decoration-break: clone; box-decoration-break: clone; }
        .dark .nc-qa details[open] h3 span { background: none; color: #00ff66; }
        .nc-qa details p { padding: 0 0.2rem 1.6rem 3.4rem; max-width: 47rem; color: var(--nc-ink-2); }
        @media (max-width: 560px) { .nc-qa details p { padding-inline-start: 0.2rem; } }

        /* ---------------------------------------------------------------
           Finale: your name on tonight's flyer, and the band to get in
           --------------------------------------------------------------- */
        .nc-claim-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 1000px) { .nc-claim-grid { grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); gap: 4rem; } }
        .nc-claim-grid .nc-head { margin-bottom: 0; }
        .nc-claim-grid .nc-h2 { font-size: clamp(1.9rem, 4.6vw, 3.7rem); }
        .nc-claim-sub { max-width: 34rem; font-size: 1.15rem; }
        .nc-mini {
            container-type: inline-size;
            padding: 1.5rem 1.4rem 1.4rem;
            background: #f4f4f0;
            color: #0a0a0a;
            rotate: 1.6deg;
            box-shadow: 0.6rem 0.7rem 0 rgba(0, 255, 102, 0.9);
        }
        .nc-mini-top { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.8rem; border-bottom: 3px solid #0a0a0a; }
        #nc .nc-mini-name { color: #0a0a0a; margin-top: 1rem; font-size: clamp(1.5rem, 11cqi, 3.2rem); line-height: 1; overflow-wrap: anywhere; }
        .nc-mini-name span { background: linear-gradient(#00ff66, #00ff66) 0 70% / 100% 78% no-repeat; padding: 0 0.12em; margin: 0 -0.12em; -webkit-box-decoration-break: clone; box-decoration-break: clone; }
        #nc .nc-mini-url { margin-top: 0.7rem; font-family: var(--nc-mono); font-weight: 700; font-size: 0.95rem; color: #2b2b29; }
        .nc-claimform { margin-top: 2.25rem; }
        .nc-claimform label { display: block; margin-bottom: 0.6rem; color: #c9cac4; }
        /* The band: the name is printed on it, and the button is the sticky end. */
        .nc-claim-row { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; }
        @media (min-width: 640px) { .nc-claim-row { grid-template-columns: minmax(0, 1fr) auto; gap: 0; } }
        #nc .nc-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.1rem;
            background: #ffffff;
            color: #0a0a0a;
            border: 0;
            border-radius: 0.3rem 0 0 0.3rem;
            box-shadow: inset 0 0 0 2px #0a0a0a;
            font-family: var(--nc-mono);
            font-weight: 700;
            font-size: clamp(1rem, 2.6vw, 1.1rem);
            transition: box-shadow 0.2s ease;
        }
        #nc .nc-claim:focus-within { border-color: transparent; box-shadow: inset 0 0 0 2px #0a0a0a, 0 0 0 4px rgba(0, 255, 102, 0.75); }
        #nc .nc-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #0a0a0a; box-shadow: none; outline: none; }
        #nc .nc-claim input::placeholder { color: #6b6b67; }
        .nc-claim > span { flex: none; color: #4f4f4c; user-select: none; }
        #nc .nc-claim-row .nc-btn { background: #00ff66; color: #050505; clip-path: none; border-radius: 0.3rem; padding-inline: 1.4rem 1.9rem; }
        @media (min-width: 640px) { #nc .nc-claim-row .nc-btn { border-radius: 0 2.6rem 2.6rem 0; } }
        #nc .nc-claim-row .nc-btn:hover { background: #f4f4f0; translate: none; }
        /* Where the finale first goes to two columns the left one cannot hold the band and its
           sticky end side by side: at 1024 the name had no room at all and the address was cut,
           and the placeholder only fits again from 1280. */
        @media (min-width: 1000px) and (max-width: 1279px) {
            .nc-claim-row { grid-template-columns: minmax(0, 1fr); gap: 0.9rem; }
            #nc .nc-claim-row .nc-btn { border-radius: 0.3rem; }
        }
        .nc-claim-note { margin-top: 1rem; font-size: 0.95rem; }

        @media (prefers-reduced-motion: reduce) {
            .nc-laser, .nc-haze, .nc-live::before, .nc-qr::after { animation: none !important; }
            .nc-wheels i, .nc-btn, .nc-bill, .nc-clip, .nc-flyer, .nc-band, .nc-tab, .nc-key { transition: none; }
            .nc-qr::after { top: 46%; }
        }
    </style>

    @php
        $clubWeekend = [
            ['Thu', 'Industry night', 'Free entry before midnight', 'Registration with a capacity limit'],
            ['Fri', 'House residency', 'Weekly, same DJs', 'One recurring event, set once'],
            ['Sat', 'Headline show', 'Ticketed, sells out', 'Timed tiers, then the waitlist'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for nightclubs?',
                'a' => 'Yes. Sharing your nights, running recurring residencies, splitting them into sub-schedules, taking free registrations with a capacity limit, and two-way sync with Google, Outlook or CalDAV are all free forever, with no ceiling on the names you take. Scanning the QR on a ticket at the door costs nothing on any plan. Charging cover is Pro at '.plan_price($proMonthly).' a month, which brings the live check-in dashboard for the door and passes with it. Event Schedule charges zero platform fees on tickets either way.',
            ],
            [
                'q' => 'Can people sign up for a free night without paying?',
                'a' => 'Yes, on every plan. Turn on registration for the night and set how many places there are. The page shows how many are left and stops taking names once they are gone, so a free night still has a real capacity rather than an open door.',
            ],
            [
                'q' => 'How do I charge less before a certain time?',
                'a' => 'Give the night more than one ticket type and put a sales window on each. A cheap early tier can stop selling at 11pm and a full-price tier take over after it, so cover changes on the clock without anyone editing the page at the door.',
            ],
            [
                'q' => 'Can I sell different ticket types for one night?',
                'a' => 'Yes: early bird, advance, on the door, table, as many as the night needs, each with its own price, quantity and sales window. Charging for them is Pro at '.plan_price($proMonthly).' a month, and Pro is where the rest of the door lives too - add-ons that attach to a ticket, promo codes, and the live check-in dashboard. A guest-list night that costs nothing to get into takes free registrations on any plan. Take payment through your own Stripe or PayPal account, or a payment link or cash on the door, and there are zero platform fees either way.'
            ],
            [
                'q' => 'Can DJs ask to play at my club?',
                'a' => 'Yes. Turn on Accept requests and artists can submit a night from your public page. Submissions land on your Requests tab, where you accept or decline before anything reaches your calendar. On Pro you can add your own questions to that form, so a DJ sends their genre and a link to their mixes with the request.',
            ],
            [
                'q' => 'What happens when a night sells out?',
                'a' => 'Turn on the waitlist for that event and people can join it once tickets are gone. If a ticket is released, the waitlist is notified automatically instead of you working through replies. The waitlist is a Pro feature.',
            ],
            [
                'q' => 'Can people ask to hear when tickets go on sale?',
                'a' => 'Yes, on every plan. Switch on the "Notify me" card, put a night up before tickets are ready, and the event page offers "Tell me when tickets go on sale". People leave an email address, with no account, and hear when tickets go on sale, if the night is cancelled, and again shortly before it starts, plus any change notice you choose to send. Each date of a weekly night keeps its own list, you see how many are waiting on the event\'s Tickets panel, and none of it counts against your newsletter allowance. It is not the waitlist, which is for a night that has already sold out.',
            ],
            [
                'q' => 'Can I refund a ticket if a night is cancelled?',
                'a' => 'Yes, from the Sales page, on Pro. A Stripe or PayPal sale goes back through the provider, in full or in part, and the sale only changes once the money has moved. A full refund puts those tickets back on sale, and a partial one leaves the ticket valid. A sale taken another way, like cash on the door or a payment link, is marked as refunded and you hand the money back yourself.',
            ],
        ];

        $dotSections = [
            ['top', 'The door'],
            ['decides', 'What it decides'],
            ['entry', 'The list'],
            ['scan', 'The door itself'],
            ['playing', "Who's playing"],
            ['rest', 'The rest of it'],
            ['weekend', 'The weekend'],
            ['who', 'Perfect for'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Open it'],
        ];
    @endphp

    @php
        $ncArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $ncDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $ncWheel = implode("\n", range(0, 9));

        // The code on a ticket, drawn as a code: three finders and a field of modules.
        $ncQr = 'M0,0h7v7h-7zM1,1v5h5v-5zM2,2h3v3h-3zM14,0h7v7h-7zM15,1v5h5v-5zM16,2h3v3h-3zM0,14h7v7h-7zM1,15v5h5v-5zM2,16h3v3h-3z';
        for ($ncRow = 0; $ncRow < 21; $ncRow++) {
            for ($ncCol = 0; $ncCol < 21; $ncCol++) {
                $ncFinder = ($ncRow < 8 && $ncCol < 8) || ($ncRow < 8 && $ncCol > 12) || ($ncRow > 12 && $ncCol < 8);
                if (! $ncFinder && crc32("door{$ncRow}x{$ncCol}") % 100 < 47) {
                    $ncQr .= "M{$ncCol},{$ncRow}h1v1h-1z";
                }
            }
        }
    @endphp

    <div id="nc">

        <nav class="nc-index es-dotnav" aria-label="Page sections">
            <ul>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot nc-m"><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ul>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the flyer                                           -->
        <!-- ============================================================ -->
        <section id="top" class="nc-hero nc-paper" style="scroll-margin-top: 5rem;">
            <div class="nc-laser" aria-hidden="true"></div>
            <div class="nc-haze" aria-hidden="true"></div>
            <div class="nc-crop" aria-hidden="true"></div>

            <div class="nc-wrap nc-hero-in">
                <div class="nc-hero-type">
                    <h1 class="nc-d nc-h1">
                        <x-marketing.hero-eyebrow class="nc-strip nc-eyebrow es-fade-up es-d-1">
                            <span>Nightclub event calendar and tickets</span>
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">The night is won </span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">at the door.</span></span>
                        <span class="es-mask nc-mask-3"><span class="es-mask-line"><span class="nc-hl nc-hl-now">Not in the booth.</span></span></span>
                    </h1>
                    <div class="nc-stamp nc-hero-stamp" aria-hidden="true">
                        <svg viewBox="0 0 200 200"><defs><path id="nc-ring-hero" d="M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0" /></defs><text textLength="494" lengthAdjust="spacing"><textPath href="#nc-ring-hero" textLength="494" lengthAdjust="spacing">Admit one &#183; tonight only &#183; admit one &#183; tonight only &#183;</textPath></text></svg>
                        <b>IN</b>
                    </div>
                </div>

                <div class="nc-hero-foot">
                    <div>
                        <p class="nc-lede es-fade-up es-d-2">
                            Capacity, cover, who is on for tonight, and who actually walked in. Put the entry side of your club on one link, with QR check-in on the door and zero platform fees when you sell.
                        </p>
                        <div class="nc-cta es-fade-up es-d-3">
                            <a href="#entry" class="nc-btn nc-btn-ghost">
                                See how entry works
                                {!! $ncDown !!}
                            </a>
                            <a href="{{ app_url('/sign_up?type=venue') }}" class="nc-btn">
                                Create your club's calendar
                                {!! $ncArrow !!}
                            </a>
                        </div>
                    </div>

                    <!-- The door's own count -->
                    <div class="nc-count es-fade-up es-d-4" aria-hidden="true">
                        <div class="nc-clicker">
                            <div class="nc-wheels">
                                @foreach ([0, 2, 4, 1] as $ncIndex => $ncDigit)
                                    <span><i style="--n: {{ $ncDigit }}; --i: {{ $ncIndex }};">{{ $ncWheel }}</i></span>
                                @endforeach
                            </div>
                        </div>
                        <div class="nc-count-read">
                            <span class="nc-count-of">/ 300</span>
                            <p>Checked in against tonight's capacity</p>
                            <div class="nc-pills">
                                <span class="nc-m nc-pill nc-pill-on">Doors open</span>
                                <span class="nc-m nc-pill">59 places left</span>
                            </div>
                        </div>
                        <div class="nc-stamp">
                            <svg viewBox="0 0 200 200"><defs><path id="nc-ring-small" d="M100,100 m-78,0 a78,78 0 1,1 156,0 a78,78 0 1,1 -156,0" /></defs><text textLength="482" lengthAdjust="spacing"><textPath href="#nc-ring-small" textLength="482" lengthAdjust="spacing">Admit one &#183; tonight &#183; admit one &#183; tonight &#183;</textPath></text></svg>
                            <b>IN</b>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Every kind of night, on a length of tape -->
            <div class="nc-tape es-fade-up es-d-5">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach (['House', 'Techno', 'Hip-Hop', 'Latin', 'Disco', 'Drum & Bass', 'Rooftop', 'Warehouse', 'Lounge', 'Residency'] as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="nc-d nc-tape-word">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. What the door decides                                     -->
        <!-- ============================================================ -->
        <section id="decides" class="nc-sec nc-block" style="scroll-margin-top: 4rem;">
            <div class="nc-laser nc-laser-left" aria-hidden="true"></div>
            <div class="nc-wrap">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal>What the door decides</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Nobody remembers the set. <span class="nc-hl">They remember the queue.</span>
                    </h2>
                </div>

                <div class="nc-three" data-reveal-group="110">
                    <div class="nc-dec" data-reveal>
                        <p class="nc-m nc-dec-k">Capacity</p>
                        <h3 class="nc-d">
                            <span>300</span> in, and no more
                        </h3>
                        <p>A number on a clipboard is a guess. A number the page enforces is a limit.</p>
                    </div>
                    <div class="nc-dec" data-reveal>
                        <p class="nc-m nc-dec-k">Cover</p>
                        <h3 class="nc-d">
                            Changes at 11pm
                        </h3>
                        <p>Cheap early, full price after. Someone has to remember to switch it, or the page does it on the clock.</p>
                    </div>
                    <div class="nc-dec" data-reveal>
                        <p class="nc-m nc-dec-k">The answer</p>
                        <h3 class="nc-d">One link runs entry</h3>
                        <p>Sign-ups, tiers, and the scan at the door, all reading from the same night.</p>
                    </div>
                </div>

                <p class="nc-dec-foot" data-reveal>
                    The booth is handled. This is the other half of the room.
                    <a href="#entry">
                        Start at the list
                        {!! $ncDown !!}
                    </a>
                </p>

                <div class="nc-queue" aria-hidden="true"></div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The list is a ticket                                      -->
        <!-- ============================================================ -->
        <section id="entry" class="nc-sec nc-paper" style="scroll-margin-top: 4rem;">
            <div class="nc-wrap">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal style="--r: 1.4deg;">The list</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The list is just <span class="nc-hl">a ticket that costs nothing.</span>
                    </h2>
                    <p class="nc-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Free entry, reduced cover and a full-price door are the same mechanic with different numbers on it.
                    </p>
                </div>

                <div class="nc-entry">
                    <article class="nc-item nc-entry-a" data-reveal>
                        <div class="nc-item-head">
                            <h3 class="nc-d">On the list, free, capped</h3>
                            <span class="nc-tier">Free</span>
                        </div>
                        <p>
                            Turn on registration and set how many places the night has. People claim one from the event page, it shows what is left, and it closes itself when they are gone. That is the guest list, without anyone keeping a separate one.
                        </p>
                        <div class="nc-sheet nc-list" aria-hidden="true">
                            <div class="nc-list-top"><b>The list</b><span class="nc-m">300 places</span></div>
                            @foreach ([[72, true], [54, true], [81, true], [63, true], [47, true], [68, true], [0, false], [0, false]] as [$ncWidth, $ncIn])
                                <div class="nc-list-row {{ $ncIn ? 'is-in' : '' }}"><i></i><u style="--w: {{ $ncWidth }}%;"></u><span class="nc-m">Claimed</span></div>
                            @endforeach
                            <div class="nc-list-foot nc-m"><span>Closes itself when full</span><span>59 left</span></div>
                        </div>
                    </article>

                    <article class="nc-item nc-entry-b" data-reveal style="--reveal-delay: 0.08s;">
                        <div class="nc-item-head">
                            <h3 class="nc-d">Cover that changes on the clock</h3>
                            <span class="nc-tier">Free</span>
                        </div>
                        <p>
                            Give a night more than one ticket type and put a sales window on each. The cheap tier stops selling at 11pm, the full-price tier takes over, and nobody has to remember to change anything at the door.
                        </p>
                        <div class="nc-sheet nc-board">
                            <div class="nc-board-top" aria-hidden="true"><span class="nc-m">On the door</span><span class="nc-board-clock">23:04</span></div>
                            <ul>
                                @foreach ([['Before 11pm', '$10', 'Closed 23:00', true], ['After 11pm', '$18', 'On sale', false], ['Group of 6+', '$15 each', 'On sale', false]] as [$tierName, $tierPrice, $tierState, $tierClosed])
                                    <li class="{{ $tierClosed ? 'is-closed' : '' }}">
                                        <span class="nc-board-name">{{ $tierName }}</span>
                                        <span class="nc-board-price">{{ $tierPrice }}</span>
                                        <span class="nc-m nc-board-state">{{ $tierState }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </article>

                    <article class="nc-item nc-entry-c" data-reveal style="--reveal-delay: 0.16s;">
                        <div class="nc-item-head">
                            <h3 class="nc-d">Tables and group rates</h3>
                            <span class="nc-tier">Free</span>
                        </div>
                        <p>
                            A table is a ticket type with a price and a quantity, and a group rate can kick in automatically once someone buys several at once. Anything that comes with the table can be an add-on that attaches to the booking, which is the one part of this that needs Pro.
                        </p>
                        <div class="nc-six" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i><i></i><span class="nc-m">The sixth one in</span></div>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The door itself                                           -->
        <!-- ============================================================ -->
        <section id="scan" class="nc-sec nc-paper nc-alt" style="scroll-margin-top: 4rem;">
            <div class="nc-wrap nc-scan">
                <div class="nc-scan-copy">
                    <div class="nc-head">
                        <p class="nc-strip" data-reveal>The door itself</p>
                        <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Know who is inside, <span class="nc-hl">not who bought.</span>
                        </h2>
                    </div>
                    <p data-reveal style="--reveal-delay: 0.16s;">
                        Every ticket carries a QR code, and scanning it on the way in is free on every plan. On Pro the check-in dashboard counts against the capacity you set, so the number on the clicker is the number in the room.
                    </p>
                    <ul class="nc-ticks" data-reveal style="--reveal-delay: 0.22s;">
                        <li>
                            <span>Real-time attendance with a per-ticket breakdown, so you can see which tier is actually turning up.</span>
                        </li>
                        <li>
                            <span>Per-attendee tickets give every guest in a group their own confirmation email and their own QR, so one person is not holding six.</span>
                        </li>
                        <li>
                            <span><a href="{{ marketing_url('/features/check-in') }}">Scanning at the door</a> is free on every plan, and so is the capacity limit on a free night. The live dashboard and per-attendee tickets are Pro.</span>
                        </li>
                        {{-- Only where the install can issue a pass: GoogleWalletService::isConfigured() gates the button itself. --}}
                        @if (\App\Services\Wallet\GoogleWalletService::isConfigured())
                        <li>
                            <span>Buyers can save a ticket to Google Wallet with the same QR on it, so nobody in the queue is digging through their email at the rope.</span>
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="nc-door" aria-hidden="true" data-reveal="right">
                    <div class="nc-screen">
                        <div class="nc-screen-top nc-m">
                            <span>Check-in</span>
                            <span class="nc-live">Live</span>
                        </div>
                        <div class="nc-screen-big">
                            <div class="nc-wheels">
                                @foreach ([2, 4, 1] as $ncIndex => $ncDigit)
                                    <span><i style="--n: {{ $ncDigit }}; --i: {{ $ncIndex }};">{{ $ncWheel }}</i></span>
                                @endforeach
                            </div>
                            <p class="nc-m">of 300 capacity</p>
                        </div>
                        @foreach ([['Before 11pm', '128', 'of 140'], ['After 11pm', '96', 'of 140'], ['Table 4', '17', 'of 20']] as [$ciName, $ciIn, $ciOf])
                            <div class="nc-tally">
                                <span>{{ $ciName }}</span>
                                <b>{{ $ciIn }}</b>
                                <small>{{ $ciOf }}</small>
                                <i style="--p: {{ round(100 * (int) $ciIn / max(1, (int) preg_replace('/\D/', '', $ciOf))) }}%;"></i>
                            </div>
                        @endforeach
                        <p class="nc-screen-foot">Updates as each code is scanned.</p>
                    </div>
                    <div class="nc-qr">
                        <svg viewBox="0 0 21 21" shape-rendering="crispEdges"><path fill="currentColor" fill-rule="evenodd" d="{{ $ncQr }}" /></svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Who's playing                                             -->
        <!-- ============================================================ -->
        <section id="playing" class="nc-sec nc-block" style="scroll-margin-top: 4rem;">
            <div class="nc-wrap">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal style="--r: 1.2deg;">Who's playing</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Let them come to you, <span class="nc-hl">and keep the calendar clean.</span>
                    </h2>
                </div>

                <div class="nc-play">
                    <div class="nc-play-list" data-reveal-group="100">
                        <div class="nc-play-item" data-reveal>
                            <div class="nc-item-head">
                                <h3 class="nc-d">Accept requests</h3>
                                <span class="nc-tier">Free</span>
                            </div>
                            <p>Switch it on and artists can submit a night from your public page. Everything waits on your Requests tab until you accept or decline it, so nothing reaches your calendar by surprise.</p>
                        </div>
                        <div class="nc-play-item" data-reveal>
                            <div class="nc-item-head">
                                <h3 class="nc-d">Ask what you need up front</h3>
                                <span class="nc-tier nc-tier-pro">Pro</span>
                            </div>
                            <p>Add your own questions to that form and a DJ sends their genre, set length and a link to their mixes with the request, instead of three replies later.</p>
                        </div>
                        <div class="nc-play-item" data-reveal>
                            <div class="nc-item-head">
                                <h3 class="nc-d">Residencies set themselves</h3>
                                <span class="nc-tier">Free</span>
                            </div>
                            <p>A weekly night is one recurring event with a day-of-week pattern, plus date exceptions for the weeks you are closed or the room is booked out.</p>
                        </div>
                    </div>

                    <div aria-hidden="true" data-reveal="right">
                        <div class="nc-reqs-top">
                            <b>Requests</b>
                            <span class="nc-strip" style="--r: 2deg;">3 waiting</span>
                        </div>
                        <div class="nc-bills">
                            @foreach ([['Kaya Sol', 'Sat 12 Apr', 'House', '2 hr'], ['NULL/VOID', 'Fri 18 Apr', 'Techno', '90 min'], ['Duo Prisma', 'Sat 26 Apr', 'Latin', 'live set']] as [$reqName, $reqDate, $reqGenre, $reqLen])
                                <div class="nc-bill" style="--r: {{ ['-1.6deg', '1.1deg', '-0.7deg'][$loop->index] }}; --x: {{ ['0', '1.2rem', '0.4rem'][$loop->index] }}; --bg: {{ ['#ffffff', '#00ff66', '#e8e8e3'][$loop->index] }};">
                                    <p class="nc-bill-name">{{ $reqName }}</p>
                                    <p class="nc-m nc-bill-meta">{{ $reqDate }} &middot; {{ $reqGenre }} &middot; {{ $reqLen }}</p>
                                    <span class="nc-m nc-bill-ok">Accept</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="nc-reqs-foot">Declined requests never touch your calendar.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. The rest of it: six clippings                             -->
        <!-- ============================================================ -->
        <section id="rest" class="nc-sec nc-paper" style="scroll-margin-top: 4rem;">
            <div class="nc-wrap">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal>The rest of it</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Everything behind <span class="nc-hl">the door.</span>
                    </h2>
                </div>

                <div class="nc-clips" data-reveal-group="80">
                    <!-- 1 -->
                    <article class="nc-sheet nc-clip" data-reveal style="--r: -0.8deg;">
                        <div class="nc-clip-top"><span class="nc-m" aria-hidden="true">No. 01</span><span class="nc-tier">Free</span></div>
                        <h3 class="nc-d">Tell the regulars first</h3>
                        <p>
                            People follow your schedule and you email them directly when a night goes up or a headliner is announced. Nothing sits between the two of you deciding who finds out.
                        </p>
                        <p>
                            Worth knowing the numbers before you plan around it: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.
                        </p>
                    </article>

                    <!-- 2 -->
                    <article class="nc-sheet nc-clip" data-reveal style="--r: 0.6deg;">
                        <div class="nc-clip-top"><span class="nc-m" aria-hidden="true">No. 02</span><span class="nc-tier nc-tier-pro">Pro</span></div>
                        <h3 class="nc-d">When it sells out</h3>
                        <p>
                            Turn on the waitlist and people can join once tickets are gone. If one is released they are notified automatically, instead of you working back through a hundred replies.
                        </p>
                    </article>

                    <!-- 3 -->
                    <article class="nc-sheet nc-clip" data-reveal style="--r: -0.4deg;">
                        <div class="nc-clip-top"><span class="nc-m" aria-hidden="true">No. 03</span><span class="nc-tier">Free</span></div>
                        <h3 class="nc-d">Every night in its own lane</h3>
                        <p>
                            Sub-schedules split one link into strands, so somebody who only comes for the techno night is not scrolling past two months of everything else to find it.
                        </p>
                        <p>
                            Built-in analytics show page views, devices and where the traffic came from, so you can tell which night the interest is actually landing on.
                        </p>
                    </article>

                    <!-- 4 -->
                    <article class="nc-sheet nc-clip" data-reveal style="--r: 0.7deg;">
                        <div class="nc-clip-top"><span class="nc-m" aria-hidden="true">No. 04</span><span class="nc-tier nc-tier-pro">Pro</span></div>
                        <h3 class="nc-d">Passes for the regulars</h3>
                        <p>
                            Sell a multi-use pass or a membership that works across a run of nights, with its own usage tracking and cancellation policy.
                        </p>
                    </article>

                    <!-- 5 -->
                    <article class="nc-sheet nc-clip" data-reveal style="--r: -0.6deg;">
                        <div class="nc-clip-top"><span class="nc-m" aria-hidden="true">No. 05</span><span class="nc-tier nc-tier-pro">Pro</span></div>
                        <h3 class="nc-d">The post, without opening a design tool</h3>
                        <p>
                            Generate a graphic from a night in a story, square, portrait or landscape crop, and post it. It is built from that event, so the date and the room are already right.
                        </p>
                        <p>
                            Running it online as well? Mark the night as an online event and paste the link to wherever you are streaming.
                            <a href="{{ marketing_url('/features/online-events') }}">How online events work</a>
                        </p>
                    </article>

                    <!-- 6 -->
                    <article class="nc-sheet nc-clip" data-reveal style="--r: 0.5deg;">
                        <div class="nc-clip-top"><span class="nc-m" aria-hidden="true">No. 06</span><span class="nc-tier">Free</span></div>
                        <h3 class="nc-d">On the site you already have</h3>
                        <p>
                            Embed the calendar on your own site so tonight is wherever people already look you up, and every night syncs two ways with Google, Outlook and CalDAV.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. The club weekend: three flyers                            -->
        <!-- ============================================================ -->
        <section id="weekend" class="nc-sec nc-popband" style="scroll-margin-top: 4rem;">
            <div class="nc-laser" aria-hidden="true"></div>
            <div class="nc-wrap">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal aria-hidden="true">This weekend</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three nights, one <span class="nc-hl">door</span>
                    </h2>
                    <p class="nc-sub" data-reveal style="--reveal-delay: 0.16s;">
                        The same page, set up three different ways.
                    </p>
                </div>

                <div class="nc-flyers" data-reveal-group="120">
                    @foreach ($clubWeekend as [$wDay, $wName, $wDetail, $wHow])
                        <article class="nc-flyer {{ $loop->index === 1 ? 'nc-flyer-night' : '' }}" data-reveal style="--r: {{ ['-2deg', '1.2deg', '-0.8deg'][$loop->index] }}; --t: {{ ['-4deg', '3deg', '-2deg'][$loop->index] }}; --bg: {{ ['#f4f4f0', '#0a0a0a', '#f4f4f0'][$loop->index] }}; --fg: {{ ['#0a0a0a', '#f4f4f0', '#0a0a0a'][$loop->index] }};">
                            <p class="nc-d nc-flyer-day">{{ $wDay }}</p>
                            <h3 class="nc-d">{{ $wName }}</h3>
                            <p>{{ $wDetail }}</p>
                            <p class="nc-m nc-flyer-how">{{ $wHow }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Perfect for: six wristbands                               -->
        <!-- ============================================================ -->
        <section id="who" class="nc-sec nc-paper" style="scroll-margin-top: 4rem;">
            <div class="nc-wrap">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal aria-hidden="true" style="--r: 1.3deg;">Off the roll</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Perfect for all types of <span class="nc-hl">clubs</span>
                    </h2>
                    <p class="nc-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Different crowds, different music, the same door.
                    </p>
                </div>

                @php
                    // name, description, blog slug, band stock, band ink, tilt
                    $ncBands = [
                        ['Dance Clubs & EDM Venues', 'House, techno, trance crowds. Big rooms, bigger sound systems, and lineups that matter.', 'for-dance-clubs-edm', '#ffffff', '#0a0a0a', '-0.5deg'],
                        ['Hip-Hop & Urban Clubs', 'Hip-hop nights, R&B showcases, urban music events. Build your scene\'s go-to spot.', 'for-hip-hop-clubs', '#00ff66', '#0a0a0a', '0.4deg'],
                        ['Latin Clubs', 'Salsa, bachata, reggaeton communities. Themed nights that keep dancers coming back.', 'for-latin-clubs', '#0a0a0a', '#f4f4f0', '-0.3deg'],
                        ['Rooftop Clubs', 'Sunset sessions, seasonal programming, skyline views. Weather-dependent vibes done right.', 'for-rooftop-clubs', '#d5d5cf', '#0a0a0a', '0.6deg'],
                        ['Underground & Warehouse', 'Intimate sets, warehouse parties, curated crowds. Where the real heads gather.', 'for-underground-clubs', '#ffffff', '#0a0a0a', '-0.6deg'],
                        ['VIP Lounges', 'Table-led nights, upscale nightlife, smaller rooms. Premium experiences with a strict door.', 'for-vip-lounges', '#0a0a0a', '#00ff66', '0.3deg'],
                    ];
                @endphp

                <div class="nc-bands" data-reveal-group="70">
                    @foreach ($ncBands as [$ncName, $ncDesc, $ncSlug, $ncBg, $ncFg, $ncTilt])
                        @php $ncPost = get_sub_audience_blog($ncSlug); @endphp
                        <article class="nc-band" data-reveal="left" style="--bg: {{ $ncBg }}; --fg: {{ $ncFg }}; --r: {{ $ncTilt }}; --cut: {{ $ncFg === '#0a0a0a' ? 'rgba(10, 10, 10, 0.5)' : 'rgba(244, 244, 240, 0.5)' }};">
                            <div class="nc-m nc-band-serial" aria-hidden="true"><span>No. {{ str_pad(417 + $loop->index * 113, 6, '0', STR_PAD_LEFT) }}</span><i></i></div>
                            <h3 class="nc-d">{{ $ncName }}</h3>
                            <p>{{ $ncDesc }}</p>
                            @if ($ncPost)
                                <a href="{{ blog_url('/' . $ncPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $ncName }}">Learn more</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Three steps: three stamps                                 -->
        <!-- ============================================================ -->
        <section id="steps" class="nc-sec nc-block" style="scroll-margin-top: 4rem;">
            <div class="nc-laser nc-laser-left" aria-hidden="true"></div>
            <div class="nc-wrap">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal aria-hidden="true">On the back of the hand</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three <span class="nc-hl">steps</span>
                    </h2>
                </div>

                <div class="nc-steps">
                    @foreach ([['01', 'Add your nights', 'Each regular night once as a recurring event, with sub-schedules keeping the house night, the hip-hop night and the headline shows apart.'], ['02', 'Set the door', 'Registration with a capacity limit for free nights, or ticket types with their own sales windows so cover changes on the clock.'], ['03', 'Scan them in', 'Every ticket carries a QR code. Scan it on the door, free on every plan, and on Pro the dashboard counts who is inside against the capacity you set.']] as [$stepNum, $stepTitle, $stepBody])
                        <div class="nc-step">
                            <div class="nc-stamp" aria-hidden="true" data-reveal="stamp" style="--r: {{ ['-11deg', '7deg', '-5deg'][$loop->index] }}; --reveal-delay: {{ $loop->index * 0.22 }}s;">
                                <svg viewBox="0 0 200 200"><defs><path id="nc-ring-step-{{ $loop->iteration }}" d="M100,100 m-80,0 a80,80 0 1,1 160,0 a80,80 0 1,1 -160,0" /></defs><text textLength="494" lengthAdjust="spacing"><textPath href="#nc-ring-step-{{ $loop->iteration }}" textLength="494" lengthAdjust="spacing">{{ $stepTitle }} &#183; {{ $stepTitle }} &#183; {{ $stepTitle }} &#183;</textPath></text></svg>
                                <b>{{ $stepNum }}</b>
                            </div>
                            <h3 class="nc-d" data-reveal style="--reveal-delay: {{ $loop->index * 0.22 + 0.1 }}s;">{{ $stepTitle }}</h3>
                            <p data-reveal style="--reveal-delay: {{ $loop->index * 0.22 + 0.16 }}s;">{{ $stepBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Key features                                             -->
        <!-- ============================================================ -->
        <section class="nc-sec nc-paper">
            <div class="nc-wrap nc-keys">
                <div>
                    <h2 class="nc-d nc-h2" data-reveal>Key features</h2>
                    <a href="{{ marketing_url('/features') }}" class="nc-m nc-more" data-reveal style="--reveal-delay: 0.1s;">
                        See all features
                        {!! $ncArrow !!}
                    </a>
                </div>

                @php
                    $ncKeys = [
                        ['Ticketing', 'Ticket types, QR check-in, and zero platform fees', marketing_url('/features/ticketing')],
                        ['Recurring Events', 'Set a residency once, with exceptions for the weeks you close', marketing_url('/features/recurring-events')],
                        ['Sub-schedules', 'Keep every night in its own lane on one link', marketing_url('/features/sub-schedules')],
                        ['Newsletters', 'Email the people who follow your schedule', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <div class="nc-keys-list" data-reveal-group="70">
                    @foreach ($ncKeys as [$ncKeyName, $ncKeyDesc, $ncKeyUrl])
                        <a href="{{ $ncKeyUrl }}" class="nc-key" data-reveal>
                            <span>
                                <b>{{ $ncKeyName }}</b>
                                <small>{{ $ncKeyDesc }}</small>
                            </span>
                            {!! $ncArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="nc-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 11. Related pages: take a number                             -->
        <!-- ============================================================ -->
        <section class="nc-sec nc-paper">
            <div class="nc-wrap">
                <div class="nc-sheet nc-tear" data-reveal="panel">
                    <div class="nc-tear-top">
                        <p class="nc-strip" aria-hidden="true">Take one</p>
                        <h2 class="nc-d">Related pages</h2>
                        <a href="{{ marketing_url('/use-cases') }}" class="nc-m nc-more">
                            See all use cases
                            {!! $ncArrow !!}
                        </a>
                    </div>
                    <div class="nc-tabs">
                        @foreach ([['/for-djs', 'DJs'], ['/for-music-venues', 'Music Venues'], ['/for-bars', 'Bars'], ['/for-venues', 'Venues']] as [$relHref, $relName])
                            <a href="{{ marketing_url($relHref) }}" class="nc-tab" style="--tr: {{ ['-4deg', '3deg', '-3deg', '4deg'][$loop->index] }};">
                                <span class="nc-tab-in">
                                    <b>For {{ $relName }}</b>
                                    <span class="nc-m">
                                        Read more
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 12. FAQ                                                      -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="nc-sec nc-paper nc-alt" style="scroll-margin-top: 4rem;">
            <div class="nc-wrap nc-faq">
                <div class="nc-head">
                    <p class="nc-strip" data-reveal aria-hidden="true" style="--r: 1.5deg;">Door policy</p>
                    <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked <span class="nc-hl">questions</span>
                    </h2>
                    <p class="nc-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Everything club owners ask about the door.
                    </p>
                </div>

                <div class="nc-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq">
                            <summary>
                                <span class="nc-m" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3><span>{{ $faq['q'] }}</span></h3>
                                <i aria-hidden="true"></i>
                            </summary>
                            <p>{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 13. Finale: your name on tonight's flyer                     -->
        <!-- ============================================================ -->
        <section id="claim" class="nc-sec nc-block" style="scroll-margin-top: 4rem;">
            <div class="nc-laser" aria-hidden="true"></div>
            <div class="nc-laser nc-laser-left" aria-hidden="true"></div>
            <div class="nc-wrap nc-claim-grid">
                <div>
                    <div class="nc-head">
                        <p class="nc-strip" data-reveal>Free forever</p>
                        <h2 class="nc-d nc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Unlock it. <span class="nc-hl">The queue is already outside.</span>
                        </h2>
                        <p class="nc-claim-sub" data-reveal style="--reveal-delay: 0.16s;">
                            Set the capacity, set the cover, and put the whole thing on one link before Friday.
                        </p>
                    </div>

                    <div class="nc-claimform" data-reveal style="--reveal-delay: 0.22s;">
                        <label for="es-claim-input" class="nc-m">Your schedule name</label>
                        <div class="nc-claim-row">
                            <div dir="ltr" class="es-claim nc-claim">
                                <input id="es-claim-input" type="text" placeholder="your-club" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=venue') }}" class="nc-btn">
                                Create your calendar
                                {!! $ncArrow !!}
                            </a>
                        </div>
                        <p class="nc-claim-note">No credit card required</p>
                    </div>
                </div>

                <!-- Tonight's flyer, with the name as it is typed -->
                <div class="nc-mini" id="nc-mini" aria-hidden="true" data-reveal="panel">
                    <div class="nc-mini-top nc-m"><span>Tonight</span><span>Doors open</span></div>
                    <p class="nc-d nc-mini-name"><span id="nc-sign">your-club</span></p>
                    <p class="nc-mini-url">.eventschedule.com</p>
                </div>
            </div>
        </section>

        <div class="nc-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- Three small jobs: print the typed name on tonight's flyer (the same slug transform as the
         shared claim-input sanitizer), sweep the lasers only while they are on screen, and fire
         the finale's confetti in the room's own colours rather than the site's blues. The
         confetti is fired from a cannon of the page's own, drawn on the page's thread: the
         library's default one hands its canvas to a worker made from a blob, the site's
         Content Security Policy refuses that worker without an exception, and nothing is drawn. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('nc-sign');
            if (input && sign) {
                var fallback = sign.textContent;
                input.addEventListener('input', function () {
                    var slug = input.value.toLowerCase()
                        .replace(/['’]/g, '')
                        .replace(/[^a-z0-9-]+/g, '-')
                        .replace(/-{2,}/g, '-')
                        .replace(/^-+/, '')
                        .slice(0, 30);
                    sign.textContent = slug || fallback;
                });
            }

            if (!('IntersectionObserver' in window)) {
                return;
            }

            var lasers = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    entry.target.classList.toggle('is-live', entry.isIntersecting);
                });
            });
            document.querySelectorAll('#nc .nc-laser').forEach(function (el) { lasers.observe(el); });

            var mini = document.getElementById('nc-mini');
            if (!mini || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var cannon = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    cannon.disconnect();
                    var fire = window.confetti.create(null, { resize: true });
                    [[60, 0.05], [120, 0.95]].forEach(function (shot) {
                        fire({ particleCount: 70, angle: shot[0], spread: 56, startVelocity: 54, origin: { x: shot[1], y: 0.95 }, colors: ['#00ff66', '#f4f4f0', '#9da09a'], shapes: ['square'], scalar: 0.9, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.6 });
            cannon.observe(mini);
        })();
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
