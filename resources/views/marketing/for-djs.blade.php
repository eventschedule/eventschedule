<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for DJs | Set Times & Residencies</x-slot>
    <x-slot name="description">Put your DJ set times, residencies and guest spots on one link. Reach fans direct, no promoter middleman, and sell tickets with zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For DJs</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Rajdhani') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Titillium Web') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for DJs"
        description="Put your DJ set times, residencies and guest spots on one link. Reach fans direct, no promoter middleman, and sell tickets with zero platform fees."
        audience="DJs"
        keywords="DJ schedule, DJ set times, DJ residency schedule, DJ booking platform, DJ event calendar, DJ gig management, club DJ calendar, DJ link in bio, free DJ scheduling" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How DJs share their set times with Event Schedule",
        "description": "Three steps from your next set to a packed dancefloor.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your sets",
                "text": "Import from Google Cal or add manually. Residencies auto-repeat weekly or monthly."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Drop your link",
                "text": "Add to your RA profile, Linktree, SoundCloud bio. Anywhere fans find you."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Pack the dancefloor",
                "text": "Fans sign up for email, hear automatically when you're spinning, and show up ready to dance."
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
           For-djs "The Booth" styles. The page is a rack of hardware: a
           master display over two jog wheels, a six-channel mixer whose
           strips are the features, a crossfader that really fades, a
           deck with hot cues for the four slots of a night, a pad bank,
           a signal chain, a patch bay, the manual and the master out.

           Brushed silver by day, anodised graphite by night; the
           displays are black glass in both. Everything is scoped under
           #dj and drawn in CSS: metal from conic gradients, meters and
           waveforms from masks and clip-paths, levels and the playhead
           from registered custom properties.
           ============================================================== */

        @property --dj-p { syntax: '<percentage>'; inherits: true; initial-value: 38%; }
        @property --dj-lvl { syntax: '<percentage>'; inherits: true; initial-value: 60%; }

        #dj {
            --dj-plate: #d9dce0;
            --dj-plate-2: #cdd1d6;
            --dj-plate-hi: #e6e8eb;
            --dj-ink: #14161a;
            --dj-ink-2: #343941;
            --dj-ink-3: #4d535c;
            --dj-line: rgba(20, 22, 26, 0.2);
            --dj-edge-hi: rgba(255, 255, 255, 0.8);
            --dj-edge-lo: rgba(20, 22, 26, 0.3);
            --dj-grain-a: rgba(255, 255, 255, 0.22);
            --dj-grain-b: rgba(20, 22, 26, 0.04);
            --dj-sheen: rgba(255, 255, 255, 0.3);
            --dj-led: #ff7a1a;
            --dj-led-ink: #8f3804;
            --dj-vu-g: #3ddc84;
            --dj-vu-a: #ffb020;
            --dj-vu-r: #ff3b30;
            --dj-glass: #e9f1ec;
            --dj-glass-2: #b3bfbc;
            --dj-glass-3: #8b9796;
            --dj-bezel: #1b1d21;
            --dj-metal-a: #f6f7f8;
            --dj-metal-b: #9aa0a8;
            --dj-knurl-a: #b4b9c0;
            --dj-knurl-b: #6f757d;
            --dj-screw-a: #f2f3f4;
            --dj-screw-b: #a9aeb6;
            --dj-screw-c: #5d636b;
            --dj-display: 'Rajdhani', 'Bahnschrift', 'DIN Alternate', 'Arial Narrow', sans-serif;
            --dj-text: 'Titillium Web', 'Segoe UI', system-ui, -apple-system, sans-serif;
            --dj-mono: ui-monospace, 'SF Mono', 'JetBrains Mono', Menlo, Consolas, monospace;
            position: relative;
            background-color: var(--dj-plate);
            background-image:
                repeating-linear-gradient(0deg, var(--dj-grain-a) 0 1px, transparent 1px 3px),
                repeating-linear-gradient(0deg, var(--dj-grain-b) 0 1px, transparent 1px 7px);
            color: var(--dj-ink);
            font-family: var(--dj-text);
            font-size: 1.0625rem;
            line-height: 1.55;
        }
        .dark #dj {
            --dj-plate: #17191c;
            --dj-plate-2: #111316;
            --dj-plate-hi: #202328;
            --dj-ink: #eceef1;
            --dj-ink-2: #bcc1c9;
            --dj-ink-3: #959ca6;
            --dj-line: rgba(236, 238, 241, 0.14);
            --dj-edge-hi: rgba(255, 255, 255, 0.07);
            --dj-edge-lo: rgba(0, 0, 0, 0.7);
            --dj-grain-a: rgba(255, 255, 255, 0.018);
            --dj-grain-b: rgba(0, 0, 0, 0.28);
            --dj-sheen: rgba(255, 255, 255, 0.03);
            --dj-led-ink: #ff8f3d;
            --dj-bezel: #0a0b0d;
            --dj-metal-a: #5d626a;
            --dj-metal-b: #24272c;
            --dj-knurl-a: #4a4f57;
            --dj-knurl-b: #16181b;
            --dj-screw-a: #6a7078;
            --dj-screw-b: #33373d;
            --dj-screw-c: #050607;
        }

        /* The bar above is the lid of the same case. */
        body > header.sticky {
            background-color: rgba(217, 220, 224, 0.88);
            border-bottom-color: rgba(20, 22, 26, 0.18);
        }
        .dark body > header.sticky {
            background-color: rgba(23, 25, 28, 0.88);
            border-bottom-color: rgba(236, 238, 241, 0.12);
        }

        #dj ::selection { background: #ff7a1a; color: #14161a; }
        #dj a:focus-visible,
        #dj summary:focus-visible,
        #dj input:focus-visible {
            outline: 3px solid var(--dj-ink);
            outline-offset: 3px;
        }
        #dj .dj-screen a:focus-visible,
        #dj .dj-pad a:focus-visible,
        #dj .dj-screen input:focus-visible { outline-color: #ff7a1a; }

        .dj-wrap { width: min(100% - 2rem, 78rem); margin-inline: auto; }
        @media (min-width: 720px) { .dj-wrap { width: min(100% - 4.5rem, 78rem); } }

        /* A module: one panel of the rack, a seam above it and a screw in each corner. */
        .dj-mod {
            position: relative;
            padding-block: clamp(3.5rem, 7vw, 6.25rem);
            border-top: 1px solid var(--dj-edge-lo);
            box-shadow: inset 0 1px 0 var(--dj-edge-hi);
            background-image: linear-gradient(100deg, transparent 0 28%, var(--dj-sheen) 50%, transparent 72%);
        }
        .dj-mod::before {
            content: "";
            position: absolute;
            inset: 0.7rem;
            pointer-events: none;
            background:
                linear-gradient(35deg, transparent 43%, var(--dj-screw-c) 43% 57%, transparent 57%) 3px 3px / 8px 8px no-repeat,
                linear-gradient(110deg, transparent 43%, var(--dj-screw-c) 43% 57%, transparent 57%) calc(100% - 3px) 3px / 8px 8px no-repeat,
                linear-gradient(160deg, transparent 43%, var(--dj-screw-c) 43% 57%, transparent 57%) 3px calc(100% - 3px) / 8px 8px no-repeat,
                linear-gradient(75deg, transparent 43%, var(--dj-screw-c) 43% 57%, transparent 57%) calc(100% - 3px) calc(100% - 3px) / 8px 8px no-repeat,
                radial-gradient(circle closest-side, var(--dj-screw-a) 0 28%, var(--dj-screw-b) 76%, var(--dj-screw-c) 80% 94%, transparent 100%) 0 0 / 14px 14px no-repeat,
                radial-gradient(circle closest-side, var(--dj-screw-a) 0 28%, var(--dj-screw-b) 76%, var(--dj-screw-c) 80% 94%, transparent 100%) 100% 0 / 14px 14px no-repeat,
                radial-gradient(circle closest-side, var(--dj-screw-a) 0 28%, var(--dj-screw-b) 76%, var(--dj-screw-c) 80% 94%, transparent 100%) 0 100% / 14px 14px no-repeat,
                radial-gradient(circle closest-side, var(--dj-screw-a) 0 28%, var(--dj-screw-b) 76%, var(--dj-screw-c) 80% 94%, transparent 100%) 100% 100% / 14px 14px no-repeat;
        }
        .dj-mod > .dj-wrap { position: relative; }

        /* Silk-screened legends: the small mono caps printed on the plate. */
        .dj-silk {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: clamp(1.5rem, 3vw, 2.25rem);
            font-family: var(--dj-mono);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--dj-ink-3);
            white-space: nowrap;
        }
        .dj-silk b {
            padding: 0.18rem 0.42rem 0.14rem;
            border: 1.5px solid var(--dj-ink);
            border-radius: 3px;
            color: var(--dj-ink);
            font-weight: 700;
        }
        .dj-silk::after { content: ""; order: 2; flex: 1; min-width: 1rem; height: 1px; background: var(--dj-line); }
        .dj-silk-r { order: 3; display: none; align-items: center; gap: 0.5rem; }
        @media (min-width: 720px) { .dj-silk-r { display: inline-flex; } }

        .dj-h2 {
            font-family: var(--dj-display);
            font-weight: 700;
            font-size: clamp(2.2rem, 4.7vw, 3.9rem);
            line-height: 1.02;
            letter-spacing: -0.005em;
            text-wrap: balance;
        }
        .dj-sub { margin-top: 1rem; max-width: 38rem; font-size: 1.15rem; color: var(--dj-ink-2); }
        .dj-head { margin-bottom: clamp(2rem, 4vw, 3.25rem); }
        /* A word shown on a display, set into the middle of a printed heading. */
        .dj-lit {
            display: inline-block;
            padding: 0 0.26em;
            border-radius: 0.14em;
            background-color: #0b0d10;
            color: #ff7a1a;
            line-height: 1.14;
            text-shadow: 0 0 0.5em rgba(255, 122, 26, 0.6);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1), inset 0 0.1em 0.3em rgba(0, 0, 0, 0.9), 0 1px 0 var(--dj-edge-hi);
        }
        .dj-glow { color: #ff7a1a; text-shadow: 0 0 0.32em rgba(255, 122, 26, 0.55), 0 0 1.1em rgba(255, 122, 26, 0.3); }

        .dj-led {
            flex: none;
            display: inline-block;
            width: 0.5rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--c, #ff7a1a);
            box-shadow: 0 0 0.5rem var(--c, #ff7a1a), inset 0 0 0 1px rgba(0, 0, 0, 0.25);
        }
        .dj-blink { animation: dj-blink 1.1s steps(1) infinite; }
        @keyframes dj-blink { 50% { opacity: 0.2; } }
        @keyframes dj-spin { to { rotate: 360deg; } }

        .dj-arrow { width: 1.1em; height: 1.1em; flex: none; }
        .dj-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--dj-mono);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--dj-ink);
            border-bottom: 2px solid var(--dj-led);
            padding-bottom: 0.2rem;
            transition: gap 0.2s ease;
        }
        .dj-more:hover { gap: 0.85rem; }

        /* Black glass. Every display on the page is one of these, in both modes. */
        .dj-screen {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border: 0.5rem solid var(--dj-bezel);
            border-radius: 1.15rem;
            background-color: #0b0d10;
            color: #e9f1ec;
            box-shadow:
                0 0 0 1px var(--dj-edge-lo),
                0 1px 0 1px var(--dj-edge-hi),
                0 1.6rem 2.4rem -1.4rem rgba(0, 0, 0, 0.55);
        }
        .dj-screen::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                linear-gradient(118deg, rgba(255, 255, 255, 0.055) 0 17%, transparent 17.2%),
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.022) 0 1px, transparent 1px 3px),
                radial-gradient(120% 90% at 50% 0%, rgba(255, 122, 26, 0.07), transparent 60%);
        }
        .dj-oled { border-width: 0.22rem; border-radius: 0.6rem; box-shadow: 0 0 0 1px var(--dj-edge-lo), 0 1px 0 1px var(--dj-edge-hi); }
        .dj-oled::before { background: repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.022) 0 1px, transparent 1px 3px); }
        .dj-read {
            font-family: var(--dj-mono);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #8b9796;
        }
        .dj-read b { color: #e9f1ec; font-weight: 700; }

        /* Meters. A lit column behind a mask of segments, cut to a level that is
           a registered percentage, so it can be stepped like real LEDs. */
        .dj-vu {
            display: inline-flex;
            gap: 3px;
            height: 6.25rem;
            padding: 3px;
            border-radius: 3px;
            background: #0b0d10;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.09), 0 0 0 1px var(--dj-edge-lo), 0 1px 0 1px var(--dj-edge-hi);
        }
        .dj-vu i {
            position: relative;
            width: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            -webkit-mask: repeating-linear-gradient(to top, #000 0 0.3rem, transparent 0.3rem 0.42rem);
            mask: repeating-linear-gradient(to top, #000 0 0.3rem, transparent 0.3rem 0.42rem);
            animation: dj-vu var(--t, 1.9s) steps(3, jump-end) infinite;
            animation-delay: var(--d, 0s);
        }
        .dj-vu i::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, #3ddc84 0 60%, #ffb020 60% 84%, #ff3b30 84% 100%);
            clip-path: inset(calc(100% - var(--dj-lvl)) 0 0 0);
        }
        @keyframes dj-vu {
            0% { --dj-lvl: 34%; } 12% { --dj-lvl: 78%; } 24% { --dj-lvl: 52%; } 38% { --dj-lvl: 88%; }
            50% { --dj-lvl: 46%; } 63% { --dj-lvl: 70%; } 76% { --dj-lvl: 96%; } 88% { --dj-lvl: 58%; } 100% { --dj-lvl: 34%; }
        }
        .dj-bar {
            position: relative;
            height: 0.7rem;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.1);
            -webkit-mask: repeating-linear-gradient(90deg, #000 0 0.42rem, transparent 0.42rem 0.58rem);
            mask: repeating-linear-gradient(90deg, #000 0 0.42rem, transparent 0.42rem 0.58rem);
        }
        .dj-bar::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #3ddc84 0 60%, #ffb020 60% 84%, #ff3b30 84% 100%);
            clip-path: inset(0 calc(100% - var(--dj-lvl)) 0 0);
        }

        /* Knobs. A knurled skirt, a turned metal cap that never moves, and a pointer that does. */
        .dj-well { position: relative; display: grid; place-items: center; width: 4.1rem; aspect-ratio: 1; }
        .dj-well::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: var(--dj-ticks);
            -webkit-mask: radial-gradient(circle, transparent 0 82%, #000 83% 100%);
            mask: radial-gradient(circle, transparent 0 82%, #000 83% 100%);
        }
        .dj-knob {
            position: relative;
            width: 68%;
            aspect-ratio: 1;
            border-radius: 50%;
            background: repeating-conic-gradient(var(--dj-knurl-a) 0 5deg, var(--dj-knurl-b) 5deg 10deg);
            box-shadow: 0 0.4rem 0.6rem -0.15rem rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(0, 0, 0, 0.5);
        }
        .dj-knob::before {
            content: "";
            position: absolute;
            inset: 13%;
            border-radius: 50%;
            background: conic-gradient(from 210deg, var(--dj-metal-a), var(--dj-metal-b) 22%, var(--dj-metal-a) 40%, var(--dj-metal-b) 62%, var(--dj-metal-a) 80%, var(--dj-metal-b) 92%, var(--dj-metal-a));
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35), 0 1px 2px rgba(0, 0, 0, 0.55);
        }
        .dj-knob i { position: absolute; inset: 13%; border-radius: 50%; rotate: var(--a, 0deg); }
        .dj-knob i::after {
            content: "";
            position: absolute;
            left: calc(50% - 1.5px);
            top: 7%;
            width: 3px;
            height: 36%;
            border-radius: 2px;
            background: #ff7a1a;
            box-shadow: 0 0 5px rgba(255, 122, 26, 0.9), 0 0 0 0.5px rgba(0, 0, 0, 0.4);
        }
        @supports (animation-timeline: view()) {
            html.es-anim #dj .dj-knob i {
                animation: dj-dial linear both;
                animation-timeline: view();
                animation-range: entry 5% cover 45%;
            }
        }
        @keyframes dj-dial { from { rotate: -135deg; } to { rotate: var(--a, 0deg); } }

        /* Pads you press: the two transport buttons, reused wherever the page asks for a move. */
        .dj-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            min-height: 3.7rem;
            padding: 0.95rem 1.6rem 0.85rem;
            border-radius: 0.7rem;
            font-family: var(--dj-display);
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1;
            text-align: center;
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.2s ease;
        }
        .dj-btn svg { width: 1.15rem; height: 1.15rem; flex: none; }
        .dj-btn-play {
            background-color: #f97712;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0) 55%);
            color: #14161a;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 0 0 1px #a84a08, 0 0.38rem 0 #96420a, 0 0.5rem 1.7rem rgba(255, 122, 26, 0.5);
        }
        .dj-btn-play:hover { filter: brightness(1.07); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 0 0 1px #a84a08, 0 0.38rem 0 #96420a, 0 0.5rem 2.4rem rgba(255, 122, 26, 0.75); }
        .dj-btn-play:active { transform: translateY(0.3rem); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 0 0 1px #a84a08, 0 0.08rem 0 #96420a, 0 0.2rem 1.2rem rgba(255, 122, 26, 0.6); }
        .dj-btn-cue {
            background-color: #24272c;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.09), rgba(255, 255, 255, 0) 55%);
            color: #e9f1ec;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14), 0 0 0 1px #000, 0 0.38rem 0 #0b0c0e, 0 0.7rem 1.2rem -0.3rem rgba(0, 0, 0, 0.5);
        }
        .dj-btn-cue:hover { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14), inset 0 0 0 2px rgba(255, 122, 26, 0.85), 0 0 0 1px #000, 0 0.38rem 0 #0b0c0e, 0 0.5rem 1.6rem rgba(255, 122, 26, 0.35); }
        .dj-btn-cue:active { transform: translateY(0.3rem); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.14), inset 0 0 0 2px rgba(255, 122, 26, 0.85), 0 0 0 1px #000, 0 0.08rem 0 #0b0c0e; }

        /* ---------------------------------------------------------------
           01 Master: the main display over the two jog wheels
           --------------------------------------------------------------- */
        .dj-master { border-top: 0; box-shadow: none; padding-block: clamp(1.5rem, 2.4vw, 1.9rem) clamp(3rem, 6vw, 4.5rem); }
        .dj-master .dj-silk { margin-bottom: 1.1rem; }
        .dj-master-screen { container-type: inline-size; padding: clamp(1.1rem, 2vw, 1.7rem) clamp(1.1rem, 3vw, 2.5rem) 0; }
        .dj-status { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1.4rem; margin-bottom: clamp(1.1rem, 2cqi, 1.6rem); }
        .dj-status span:nth-child(n + 4) { display: none; }
        .dj-status-r { margin-inline-start: auto; }
        @media (min-width: 720px) { .dj-status span:nth-child(n + 4) { display: inline; } }
        .dj-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1rem;
            font-family: var(--dj-mono);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #b3bfbc;
        }
        .dj-eyebrow::before { content: ""; width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: #3ddc84; box-shadow: 0 0 0.5rem #3ddc84; }
        .dj-h1 {
            font-family: var(--dj-display);
            font-weight: 700;
            font-size: clamp(2.5rem, 13.4cqi, 3.9rem);
            line-height: 0.92;
            letter-spacing: 0.012em;
            text-transform: uppercase;
            text-wrap: balance;
        }
        .dj-h1-line { display: block; }
        @media (min-width: 760px) {
            .dj-h1 { font-size: clamp(3rem, 10.2cqi, 7.6rem); text-wrap: nowrap; }
            .dj-h1-line { white-space: nowrap; }
        }
        .dj-lede { max-width: 43rem; margin-top: clamp(0.9rem, 1.8cqi, 1.3rem); font-size: clamp(1.05rem, 1.6cqi, 1.3rem); color: #b3bfbc; }

        /* The overview: the month as one long track, three sets as hot cues, and a playhead. */
        .dj-over { position: relative; margin: clamp(1.25rem, 2.4cqi, 1.75rem) calc(clamp(1.1rem, 3vw, 2.5rem) * -1) 0; padding-top: 1.8rem; border-top: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.025); }
        .dj-win { position: relative; height: clamp(3.75rem, 7cqi, 5rem); }
        .dj-win::before { content: ""; position: absolute; inset: 0; background: repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.09) 0 1px, transparent 1px 6.25%); }
        .dj-wave { position: absolute; inset: 0.3rem 0; background: repeating-linear-gradient(90deg, #5b686a 0 2px, transparent 2px 3px); }
        .dj-wave::before { content: ""; position: absolute; inset: 0; background: repeating-linear-gradient(90deg, rgba(233, 241, 236, 0.85) 0 2px, transparent 2px 3px); transform: scaleY(0.34); }
        .dj-wave::after { content: ""; position: absolute; inset: 0; background: repeating-linear-gradient(90deg, #ff7a1a 0 2px, transparent 2px 3px); clip-path: inset(0 calc(100% - var(--dj-p)) 0 0); }
        .dj-head-line { position: absolute; top: -0.35rem; bottom: -0.35rem; left: var(--dj-p); width: 2px; margin-left: -1px; background: #fff; box-shadow: 0 0 0.6rem rgba(255, 255, 255, 0.9); }
        .dj-head-line::before { content: ""; position: absolute; left: -4px; top: -1px; border: 5px solid transparent; border-top-color: #fff; }
        html.es-anim #dj .dj-over .dj-win { animation: dj-play 30s linear infinite; }
        @keyframes dj-play { from { --dj-p: 0%; } to { --dj-p: 100%; } }
        .dj-cue {
            position: absolute;
            top: 0.35rem;
            left: var(--at);
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            white-space: nowrap;
            font-family: var(--dj-mono);
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #e9f1ec;
        }
        .dj-cue::after { content: ""; position: absolute; left: 0; top: 100%; width: 1px; height: 12rem; background: linear-gradient(var(--c), transparent); opacity: 0.8; }
        .dj-cue b { display: grid; place-items: center; width: 1.25rem; height: 1.25rem; border-radius: 0.2rem; background: var(--c); color: #0b0d10; }
        .dj-cue-end { flex-direction: row-reverse; translate: calc(-100% + 1px) 0; }
        .dj-cue-end::after { left: auto; right: 0; }
        .dj-cue em { font-style: normal; display: none; }
        @media (min-width: 640px) { .dj-cue em { display: inline; } }
        .dj-ruler { display: flex; justify-content: space-between; padding: 0.45rem clamp(1.1rem, 3vw, 2.5rem) 0.55rem; }

        .dj-surface { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; margin-top: clamp(1.75rem, 3vw, 2.4rem); align-items: start; }
        .dj-transport { min-width: 0; }
        .dj-jogs { display: grid; grid-template-columns: repeat(2, minmax(0, 10rem)); justify-content: center; gap: 1.5rem; }
        @media (min-width: 980px) {
            .dj-surface { grid-template-columns: minmax(0, 12.5rem) minmax(0, 1fr) minmax(0, 12.5rem); gap: 3rem; }
            .dj-jogs { display: contents; }
            .dj-transport { order: 2; }
            .dj-jog-b { order: 3; }
        }
        .dj-jog {
            position: relative;
            aspect-ratio: 1;
            container-type: inline-size;
            border-radius: 50%;
            background: repeating-conic-gradient(var(--dj-knurl-b) 0 0.9deg, var(--dj-knurl-a) 0.9deg 1.8deg);
            box-shadow: 0 0 0 1px var(--dj-edge-lo), 0 1.1rem 1.8rem -0.7rem rgba(0, 0, 0, 0.6), inset 0 0 0 2px rgba(255, 255, 255, 0.2);
        }
        .dj-jog-plate {
            position: absolute;
            inset: 8%;
            border-radius: 50%;
            background: conic-gradient(from 20deg, var(--dj-metal-a), var(--dj-metal-b) 12%, var(--dj-metal-a) 25%, var(--dj-metal-b) 38%, var(--dj-metal-a) 50%, var(--dj-metal-b) 62%, var(--dj-metal-a) 75%, var(--dj-metal-b) 88%, var(--dj-metal-a));
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.35), inset 0 0 1.4rem rgba(0, 0, 0, 0.22);
        }
        .dj-jog-plate::after { content: ""; position: absolute; inset: 24%; border-radius: 50%; box-shadow: 0 0 0 2px #ff7a1a, 0 0 1rem 2px rgba(255, 122, 26, 0.55); }
        .dj-jog-eye {
            position: absolute;
            inset: 30%;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #07080a;
            color: #e9f1ec;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12), inset 0 0 0.8rem #000;
        }
        .dj-jog-eye i {
            position: absolute;
            inset: 7%;
            border-radius: 50%;
            background: conic-gradient(from 0deg, #ff7a1a 0 12deg, rgba(255, 122, 26, 0.3) 12deg 70deg, transparent 70deg);
            -webkit-mask: radial-gradient(circle, transparent 0 66%, #000 67% 100%);
            mask: radial-gradient(circle, transparent 0 66%, #000 67% 100%);
            animation: dj-spin 1.8s linear infinite;
        }
        .dj-jog-b .dj-jog-eye i { animation-duration: 1.83s; animation-direction: reverse; }
        .dj-jog-eye span { font-family: var(--dj-mono); font-weight: 700; font-size: 11.5cqi; line-height: 1.1; text-align: center; }
        .dj-jog-eye small { display: block; font-size: 5.6cqi; letter-spacing: 0.2em; color: #b3bfbc; }

        .dj-cta { display: flex; flex-wrap: wrap; justify-content: center; gap: 1.4rem 1.25rem; }
        .dj-cta .dj-btn { flex: 1 1 15rem; max-width: 24rem; }
        .dj-browse { margin-top: 2rem; padding-block: 0.6rem; }
        .dj-browse .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .dj-genre { display: inline-flex; align-items: center; gap: 1.1rem; padding-inline-end: 1.1rem; color: #b3bfbc; white-space: nowrap; }
        .dj-genre::after { content: ""; width: 0.32rem; aspect-ratio: 1; border-radius: 50%; background: #ff7a1a; box-shadow: 0 0 0.4rem #ff7a1a; }
        .dj-browse-key { margin-top: 0.6rem; display: flex; justify-content: space-between; gap: 1rem; font-size: 0.66rem; }
        .dj-browse-key span { color: var(--dj-ink-3); font-family: var(--dj-mono); font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; }

        /* ---------------------------------------------------------------
           02 Input: the problem, on a display that has lost its signal
           --------------------------------------------------------------- */
        .dj-lost-screen { padding: clamp(1.5rem, 4vw, 3.5rem) clamp(1.1rem, 4vw, 3.5rem); }
        .dj-lost-h2 { margin-top: 1rem; max-width: 56rem; font-size: clamp(2.4rem, 6.4vw, 5.25rem); line-height: 0.98; }
        .dj-lost-sub { margin-top: 1.1rem; max-width: 36rem; font-size: 1.2rem; color: #b3bfbc; }
        .dj-faults { display: grid; gap: 1px; margin-top: clamp(2rem, 4vw, 3.25rem); background: rgba(255, 255, 255, 0.12); border-block: 1px solid rgba(255, 255, 255, 0.12); }
        @media (min-width: 820px) { .dj-faults { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .dj-fault { padding: 1.6rem clamp(0rem, 2vw, 1.75rem) 1.5rem; background: #0b0d10; }
        @media (min-width: 820px) { .dj-fault:first-child { padding-inline-start: 0; } .dj-fault:last-child { padding-inline-end: 0; } }
        .dj-fault .dj-bar { --dj-lvl: 8%; max-width: 13rem; animation: dj-low var(--t, 2.6s) steps(2, jump-end) infinite; }
        @keyframes dj-low { 0% { --dj-lvl: 5%; } 30% { --dj-lvl: 14%; } 55% { --dj-lvl: 3%; } 80% { --dj-lvl: 10%; } 100% { --dj-lvl: 5%; } }
        .dj-fault-cut .dj-bar { animation: none; --dj-lvl: 100%; }
        .dj-fault-cut .dj-bar::after { background: #ff3b30; clip-path: inset(0 0 0 80%); }
        .dj-fault-big { margin-top: 1rem; font-family: var(--dj-display); font-weight: 700; font-size: clamp(2.4rem, 4.6vw, 3.6rem); line-height: 1; text-transform: uppercase; letter-spacing: 0.02em; color: var(--c, #e9f1ec); }
        .dj-fault p { margin-top: 0.6rem; max-width: 21rem; color: #b3bfbc; }
        .dj-lost-foot { margin-top: 1.75rem; color: #b3bfbc; }
        .dj-lost-foot a { display: inline-flex; align-items: center; gap: 0.4rem; margin-inline-start: 0.5rem; color: #ff7a1a; font-weight: 700; border-bottom: 2px solid rgba(255, 122, 26, 0.6); transition: gap 0.2s ease; }
        .dj-lost-foot a:hover { gap: 0.7rem; }

        /* ---------------------------------------------------------------
           03 Mixer: six channel strips, then the crossfader
           --------------------------------------------------------------- */
        .dj-console {
            border-radius: 1.2rem;
            background-color: var(--dj-plate-hi);
            box-shadow: 0 0 0 1px var(--dj-edge-lo), inset 0 1px 0 var(--dj-edge-hi), 0 2rem 3rem -2rem rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        .dj-strips { display: grid; gap: 1px; background: var(--dj-line); border-bottom: 1px solid var(--dj-line); }
        @media (min-width: 720px) { .dj-strips { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .dj-strips { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .dj-ch { display: flex; flex-direction: column; padding: 1.4rem clamp(1rem, 2vw, 1.6rem) 1.6rem; background-color: var(--dj-plate-hi); min-width: 0; }
        .dj-tape {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.4rem;
            padding: 0.34rem 0.8rem 0.28rem;
            background-color: #f4f1e8;
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.6), rgba(0, 0, 0, 0.05));
            color: #14161a;
            font-family: var(--dj-mono);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            rotate: var(--r, -0.7deg);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.35);
            clip-path: polygon(0 8%, 2% 0, 98% 4%, 100% 14%, 99% 92%, 97% 100%, 3% 96%, 0 88%);
        }
        .dj-tape b { color: #8f3804; }
        .dj-ch-body { display: grid; grid-template-columns: 4.1rem minmax(0, 1fr); gap: clamp(0.9rem, 2vw, 1.4rem); flex: 1; }
        .dj-ctl { display: flex; flex-direction: column; align-items: center; gap: 0.3rem; }
        .dj-ctl > span { margin-bottom: 0.7rem; font-family: var(--dj-mono); font-size: 0.6rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--dj-ink-3); }
        .dj-ch-main { display: flex; flex-direction: column; min-width: 0; }
        .dj-ch h3 { font-family: var(--dj-display); font-weight: 700; font-size: clamp(1.55rem, 2.3vw, 1.9rem); line-height: 1.06; text-wrap: balance; }
        .dj-ch p { margin-top: 0.7rem; color: var(--dj-ink-2); font-size: 1rem; }
        .dj-ch p a { color: var(--dj-ink); font-weight: 700; text-decoration: underline; text-decoration-color: var(--dj-led); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        .dj-keys { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 1rem; }
        .dj-key {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.34rem 0.6rem 0.3rem;
            border-radius: 0.3rem;
            background: var(--dj-plate);
            box-shadow: inset 0 1px 0 var(--dj-edge-hi), 0 0 0 1px var(--dj-edge-lo), 0 2px 0 var(--dj-edge-lo);
            font-family: var(--dj-mono);
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--dj-ink-2);
        }
        .dj-key .dj-led { width: 0.4rem; }
        .dj-ch .dj-oled { margin-top: auto; }
        .dj-ch-main > .dj-oled { margin-top: 1.25rem; }
        .dj-ch-main > p + .dj-oled, .dj-ch-main > .dj-keys + .dj-oled { margin-top: auto; }
        .dj-ch-gap { flex: 1; min-height: 1.25rem; }

        .dj-rows { padding: 0.85rem 0.9rem 0.8rem; }
        .dj-rows > .dj-read { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
        .dj-row { display: grid; grid-template-columns: 3.4rem minmax(0, 1fr) auto; align-items: center; gap: 0.6rem; padding-block: 0.42rem; border-top: 1px solid rgba(255, 255, 255, 0.1); font-family: var(--dj-mono); font-size: 0.72rem; }
        .dj-row b { color: #ff7a1a; font-weight: 700; letter-spacing: 0.06em; }
        .dj-row span { min-width: 0; color: #e9f1ec; font-weight: 700; }
        .dj-row small { display: block; color: #8b9796; font-weight: 400; font-size: 0.66rem; }
        .dj-row em { font-style: normal; color: #b3bfbc; }

        .dj-night { padding: 1rem 0.9rem 0.9rem; text-align: center; }
        .dj-night strong { display: block; font-family: var(--dj-display); font-weight: 700; font-size: 2.1rem; line-height: 1; letter-spacing: 0.03em; color: #ff7a1a; text-shadow: 0 0 0.5em rgba(255, 122, 26, 0.45); }
        .dj-night-day { position: relative; height: 0.55rem; margin: 0.9rem 0 0.5rem; border-radius: 1px; background: rgba(255, 255, 255, 0.1); }
        .dj-night-day::before { content: ""; position: absolute; inset: 0 12% 0 46%; background: #ff7a1a; box-shadow: 0 0 0.6rem rgba(255, 122, 26, 0.6); }
        .dj-night-day::after { content: ""; position: absolute; left: 60%; top: -0.35rem; bottom: -0.35rem; width: 1px; background: #e9f1ec; }
        .dj-night-ends { display: flex; justify-content: space-between; }
        .dj-night-cap { margin-top: 0.5rem; color: #b3bfbc; }

        .dj-patch-ui { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); align-items: center; gap: 0.6rem; padding: 0.95rem 0.9rem; }
        .dj-patch-box { padding: 0.6rem 0.65rem 0.7rem; border-radius: 0.3rem; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16); }
        .dj-patch-box i { display: block; height: 0.3rem; margin-top: 0.45rem; border-radius: 1px; background: rgba(255, 255, 255, 0.16); }
        .dj-patch-box i:last-child { width: 62%; }
        .dj-patch-box.is-you { box-shadow: inset 0 0 0 1px #3ddc84; }
        .dj-patch-box.is-you i:first-of-type { background: #3ddc84; box-shadow: 0 0 0.5rem rgba(61, 220, 132, 0.6); }
        .dj-flow { display: flex; gap: 0.3rem; }
        .dj-flow i { width: 0.34rem; aspect-ratio: 1; border-radius: 50%; background: #ff7a1a; opacity: 0.2; animation: dj-flow 1.4s steps(1) infinite; animation-delay: calc(var(--i) * 0.2s); }
        @keyframes dj-flow { 0%, 30% { opacity: 1; box-shadow: 0 0 0.4rem #ff7a1a; } 31%, 100% { opacity: 0.2; box-shadow: none; } }

        /* The address is sized from the display it sits in, so its 24 characters fit at every width. */
        .dj-url { container-type: inline-size; padding: 0.85rem 0.9rem 0.9rem; }
        .dj-url-bar { display: flex; align-items: center; gap: 0.55rem; margin-top: 0.5rem; padding: 0.55rem 0.65rem; border-radius: 0.3rem; background: rgba(255, 255, 255, 0.07); font-family: var(--dj-mono); font-size: clamp(0.5rem, 6.8cqi - 2.8px, 0.78rem); font-weight: 700; color: #e9f1ec; }
        .dj-url-bar span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dj-outs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.4rem; margin-top: 0.6rem; }
        .dj-outs span { padding: 0.4rem 0.2rem 0.35rem; border-radius: 0.25rem; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16); text-align: center; font-family: var(--dj-mono); font-size: min(0.56rem, 4.1cqi); font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #b3bfbc; }

        .dj-flyer-ui { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 0.9rem; padding: 0.85rem 0.9rem; }
        .dj-flyer {
            width: 6.4rem;
            padding: 0.7rem 0.6rem 0.65rem;
            border-radius: 0.3rem;
            background-color: #f97712;
            background-image: radial-gradient(120% 80% at 100% 0%, rgba(255, 255, 255, 0.35), transparent 55%), repeating-linear-gradient(135deg, rgba(20, 22, 26, 0.16) 0 2px, transparent 2px 7px);
            color: #14161a;
            rotate: -3deg;
            box-shadow: 0 0.5rem 1rem -0.3rem rgba(255, 122, 26, 0.6);
        }
        .dj-flyer small { display: block; font-family: var(--dj-mono); font-size: 0.5rem; font-weight: 700; letter-spacing: 0.14em; }
        .dj-flyer strong { display: block; margin-block: 0.5rem 0.1rem; font-family: var(--dj-display); font-weight: 700; font-size: 1.5rem; line-height: 0.9; }
        .dj-flyer-sizes { display: grid; gap: 0.35rem; }
        .dj-flyer-sizes span { display: flex; align-items: center; gap: 0.5rem; }

        .dj-fee { padding: 0.95rem 0.9rem 0.9rem; }
        .dj-fee strong { display: block; margin-block: 0.25rem 0.2rem; font-family: var(--dj-display); font-weight: 700; font-size: 3rem; line-height: 0.95; color: #3ddc84; text-shadow: 0 0 0.5em rgba(61, 220, 132, 0.4); }
        .dj-fee-to { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.7rem; padding-top: 0.6rem; border-top: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.82rem; color: #e9f1ec; }

        /* The crossfader. --x runs from 0 (three places) to 1 (one link); a few lines of
           script copy the fader's value into it, and with no script it rests on 1. */
        .dj-xf { --x: 1; --dj-spread: 1; padding: clamp(1.4rem, 3vw, 2.25rem) clamp(1rem, 3vw, 2.25rem) clamp(1.5rem, 3vw, 2.25rem); background-color: var(--dj-plate-hi); }
        /* Below these the display is barely wider than the list, so the three posts scatter less far and stay on the glass. */
        @media (max-width: 819.98px) { .dj-xf { --dj-spread: 0.5; } }
        @media (max-width: 599.98px) { .dj-xf { --dj-spread: 0.35; } }
        .dj-xf-top { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem 1.5rem; margin-bottom: 1.25rem; }
        .dj-xf-top .dj-silk { margin: 0; flex: 1; }
        .dj-xf-stage { display: grid; place-items: center; min-height: 19rem; padding: 2.75rem 1rem 2rem; }
        .dj-xf-cap { position: absolute; top: 0.9rem; left: 1.1rem; }
        .dj-xf-cap-a { opacity: calc(1 - var(--x) * 2); }
        .dj-xf-cap-b { opacity: calc(var(--x) * 2 - 1); }
        .dj-xf-list { position: relative; width: min(100%, 27rem); }
        .dj-xf-url { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.6rem; padding: 0.6rem 0.75rem; border-radius: 0.35rem; background: rgba(255, 255, 255, 0.07); font-family: var(--dj-mono); font-size: clamp(0.7rem, 2.6vw, 0.85rem); font-weight: 700; opacity: calc(var(--x) * 1.5 - 0.5); }
        .dj-xf-slot { position: relative; height: 3.1rem; margin-top: 0.45rem; }
        .dj-xf-line,
        .dj-xf-src { position: absolute; inset: 0; display: grid; grid-template-columns: 4.2rem minmax(0, 1fr) auto; align-items: center; gap: 0.7rem; padding-inline: 0.75rem; border-radius: 0.35rem; font-family: var(--dj-mono); font-size: 0.78rem; }
        .dj-xf-line { box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14); opacity: calc(var(--x) * 1.6 - 0.6); }
        .dj-xf-line b { color: #ff7a1a; }
        .dj-xf-line span { color: #e9f1ec; font-weight: 700; }
        .dj-xf-line em { font-style: normal; color: #b3bfbc; }
        .dj-xf-src {
            grid-template-columns: minmax(0, 1fr) auto;
            background-color: var(--bg);
            color: var(--fg);
            opacity: calc(1.15 - var(--x) * 1.25);
            translate: calc(var(--tx) * var(--dj-spread) * (1 - var(--x))) calc(var(--ty) * (1 - var(--x)));
            rotate: calc(var(--r) * (1 - var(--x)));
            scale: calc(1 - 0.12 * (1 - var(--x)));
            box-shadow: 0 0.6rem 1.2rem -0.4rem rgba(0, 0, 0, 0.8);
        }
        .dj-xf-src small { display: block; font-size: 0.56rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; opacity: 0.8; }
        .dj-xf-src b { font-weight: 700; }
        .dj-xf-rail { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: clamp(0.75rem, 2vw, 1.5rem); margin-top: 1.5rem; }
        .dj-xf-end { display: grid; justify-items: center; gap: 0.2rem; font-family: var(--dj-mono); font-size: 0.62rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--dj-ink-3); }
        .dj-xf-end b { display: grid; place-items: center; width: 2rem; aspect-ratio: 1; border-radius: 0.3rem; border: 1.5px solid var(--dj-ink); color: var(--dj-ink); font-family: var(--dj-display); font-size: 1.2rem; line-height: 1; }
        .dj-xf-fader { position: relative; display: block; }
        .dj-xf-fader::before { content: ""; position: absolute; inset: auto 1.2rem -0.5rem; height: 0.5rem; background: repeating-linear-gradient(90deg, var(--dj-ink-3) 0 1px, transparent 1px 10%), linear-gradient(90deg, var(--dj-ink-3) 0 1px, transparent 1px) 100% 0 / 1px 100% no-repeat; }
        #dj .dj-xf-fader input {
            -webkit-appearance: none;
            appearance: none;
            display: block;
            width: 100%;
            height: 3.4rem;
            margin: 0;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
            cursor: ew-resize;
        }
        #dj .dj-xf-fader input::-webkit-slider-runnable-track { height: 0.55rem; border-radius: 0.3rem; background: #0b0d10; box-shadow: inset 0 1px 3px #000, 0 1px 0 var(--dj-edge-hi); }
        #dj .dj-xf-fader input::-moz-range-track { height: 0.55rem; border-radius: 0.3rem; background: #0b0d10; box-shadow: inset 0 1px 3px #000, 0 1px 0 var(--dj-edge-hi); }
        #dj .dj-xf-fader input::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 2.4rem;
            height: 3.4rem;
            margin-top: -1.43rem;
            border: 0;
            border-radius: 0.3rem;
            background: linear-gradient(90deg, transparent 0 45%, #ff7a1a 45% 55%, transparent 55%), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.1) 0 1px, transparent 1px 4px), linear-gradient(180deg, #3a3e45, #17191c);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 0 0 1px #000, 0 0.5rem 0.8rem -0.2rem rgba(0, 0, 0, 0.7);
        }
        #dj .dj-xf-fader input::-moz-range-thumb {
            width: 2.4rem;
            height: 3.4rem;
            border: 0;
            border-radius: 0.3rem;
            background: linear-gradient(90deg, transparent 0 45%, #ff7a1a 45% 55%, transparent 55%), repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.1) 0 1px, transparent 1px 4px), linear-gradient(180deg, #3a3e45, #17191c);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25), 0 0 0 1px #000, 0 0.5rem 0.8rem -0.2rem rgba(0, 0, 0, 0.7);
        }
        #dj .dj-xf:has(input:focus-visible) .dj-xf-stage { box-shadow: 0 0 0 2px #ff7a1a, 0 0 1.6rem rgba(255, 122, 26, 0.35); }
        #dj .dj-xf:not(.is-live) .dj-xf-rail { display: none; }
        .dj-xf-hint { color: var(--dj-ink-3); font-family: var(--dj-mono); font-size: 0.66rem; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; }
        #dj .dj-xf:not(.is-live) .dj-xf-hint { display: none; }

        /* ---------------------------------------------------------------
           04 Deck: the night as a track, four hot cues
           --------------------------------------------------------------- */
        .dj-deck { view-timeline: --dj-deck block; }
        .dj-deck-screen { padding-top: 2.4rem; }
        .dj-deck-screen .dj-win { height: clamp(5rem, 11vw, 8.5rem); --dj-p: 62.5%; }
        .dj-deck-screen .dj-cue { top: 0.75rem; font-size: 0.72rem; }
        .dj-deck-screen .dj-cue b { width: 1.5rem; height: 1.5rem; }
        .dj-deck-screen .dj-ruler { padding-inline: 1rem; }
        @supports (animation-timeline: view()) {
            html.es-anim #dj .dj-deck-screen .dj-win {
                animation: dj-run linear both;
                animation-timeline: --dj-deck;
                animation-range: cover 16% cover 58%;
            }
            html.es-anim #dj .dj-hot-key {
                animation: dj-arm linear both;
                animation-timeline: --dj-deck;
                animation-range: cover var(--from) cover var(--to);
            }
        }
        @keyframes dj-run { from { --dj-p: 3%; } to { --dj-p: 97%; } }
        @keyframes dj-arm { from { background-color: #2a2e34; color: #8b9796; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 0 0 1px #000, 0 0.3rem 0 #0b0c0e, 0 0 0 rgba(0, 0, 0, 0); } }
        .dj-hot { display: grid; gap: 2rem 0; margin-top: 2.25rem; }
        @media (min-width: 640px) { .dj-hot { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2.5rem 1.5rem; } }
        @media (min-width: 1040px) { .dj-hot { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; margin-top: 0; } }
        .dj-hot-slot { position: relative; display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0 1rem; align-content: start; }
        @media (min-width: 1040px) {
            .dj-hot-slot { display: block; padding: 3.25rem 1.5rem 0; text-align: center; }
            .dj-hot-slot::before { content: ""; position: absolute; left: 50%; top: 0; width: 1px; height: 3.25rem; background: linear-gradient(transparent, var(--dj-ink-3)); }
            .dj-hot-slot + .dj-hot-slot { box-shadow: -1px 0 0 var(--dj-line); }
        }
        .dj-hot-key {
            grid-row: span 3;
            display: grid;
            place-items: center;
            width: 3.4rem;
            aspect-ratio: 1;
            border-radius: 0.6rem;
            background-color: var(--c);
            color: #0b0d10;
            font-family: var(--dj-display);
            font-weight: 700;
            font-size: 1.7rem;
            line-height: 1;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 0 0 1px #000, 0 0.3rem 0 #0b0c0e, 0 0.3rem 1.4rem var(--c);
        }
        @media (min-width: 1040px) { .dj-hot-key { margin-inline: auto; } }
        .dj-hot-time { display: flex; align-items: center; gap: 0.6rem; font-family: var(--dj-mono); font-weight: 700; font-size: 1.05rem; letter-spacing: 0.08em; color: var(--dj-ink-3); }
        @media (min-width: 1040px) { .dj-hot-time { justify-content: center; margin-top: 1.1rem; } }
        .dj-onair { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.2rem 0.5rem 0.16rem; border-radius: 0.25rem; background-color: #0b0d10; color: #ff3b30; font-size: 0.62rem; letter-spacing: 0.16em; text-transform: uppercase; }
        .dj-onair .dj-led { --c: #ff3b30; width: 0.42rem; }
        .dj-hot-slot h3 { margin-top: 0.3rem; font-family: var(--dj-display); font-weight: 700; font-size: 1.9rem; line-height: 1.05; }
        .dj-hot-slot p { margin-top: 0.5rem; color: var(--dj-ink-2); font-size: 1rem; }

        /* ---------------------------------------------------------------
           05 Pads: the bank, one pad per kind of DJ
           --------------------------------------------------------------- */
        .dj-pads-head { display: grid; gap: 1.5rem; align-items: end; margin-bottom: clamp(2rem, 4vw, 3rem); }
        @media (min-width: 900px) { .dj-pads-head { grid-template-columns: minmax(0, 1fr) auto; } }
        .dj-switch { display: inline-flex; align-items: center; gap: 0.8rem; font-family: var(--dj-mono); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--dj-ink-3); }
        .dj-switch i { position: relative; width: 4.2rem; height: 1.7rem; border-radius: 0.3rem; background: #0b0d10; box-shadow: inset 0 1px 3px #000, 0 1px 0 var(--dj-edge-hi); }
        .dj-switch i::after { content: ""; position: absolute; top: 0.2rem; bottom: 0.2rem; right: 0.2rem; width: 1.9rem; border-radius: 0.2rem; background: repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.25) 0 1px, transparent 1px 4px), linear-gradient(180deg, #f6f7f8, #9aa0a8); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.5); }
        .dj-switch b { color: var(--dj-ink); }
        .dj-bank { display: grid; gap: 1.5rem 1.1rem; }
        @media (min-width: 620px) { .dj-bank { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .dj-bank { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.75rem 1.4rem; } }
        .dj-pad {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 12.5rem;
            padding: 2rem 1.4rem 1.3rem;
            border-radius: 0.95rem;
            background-color: #24272c;
            background-image: radial-gradient(90% 55% at 50% 0%, color-mix(in srgb, var(--c) 16%, transparent), transparent 75%), linear-gradient(160deg, rgba(255, 255, 255, 0.07), rgba(0, 0, 0, 0.25));
            color: #e9f1ec;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 0 0 1px #000, 0 0.45rem 0 #0b0c0e, 0 1.4rem 1.6rem -0.9rem rgba(0, 0, 0, 0.6);
            transition: transform 0.14s ease, box-shadow 0.14s ease;
        }
        .dj-pad::before {
            content: "";
            position: absolute;
            inset: 0.7rem 1.4rem auto;
            height: 0.24rem;
            border-radius: 0.2rem;
            background: var(--c);
            box-shadow: 0 0 0.9rem var(--c);
            opacity: 0.5;
            transition: opacity 0.2s ease;
        }
        .dj-pad:hover { transform: translateY(0.25rem); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.12), inset 0 0 2.5rem -1rem var(--c), 0 0 0 1px #000, 0 0.2rem 0 #0b0c0e, 0 0.8rem 1.6rem -0.6rem var(--c); }
        .dj-pad:hover::before { opacity: 1; }
        .dj-pad-no { font-family: var(--dj-mono); font-size: 0.66rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #a3afad; }
        .dj-pad h3 { margin-top: 0.35rem; font-family: var(--dj-display); font-weight: 700; font-size: 1.9rem; line-height: 1.05; }
        .dj-pad p { margin-top: 0.55rem; color: #b3bfbc; font-size: 1rem; }
        .dj-pad a { align-self: flex-start; margin-top: auto; padding-top: 1.1rem; }
        .dj-pad a span { display: inline-flex; align-items: center; gap: 0.4rem; color: #e9f1ec; font-weight: 700; border-bottom: 2px solid var(--c); transition: gap 0.2s ease; }
        .dj-pad a:hover span { gap: 0.7rem; }

        /* ---------------------------------------------------------------
           06 Signal chain: three steps, the level rising through them
           --------------------------------------------------------------- */
        .dj-chain { display: grid; gap: 0; }
        @media (min-width: 980px) { .dj-chain { grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr) auto minmax(0, 1fr); align-items: stretch; } }
        .dj-unit {
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1.5rem 1.4rem;
            border-radius: 1.1rem;
            background-color: var(--dj-plate-hi);
            box-shadow: 0 0 0 1px var(--dj-edge-lo), inset 0 1px 0 var(--dj-edge-hi), 0 1.4rem 2rem -1.4rem rgba(0, 0, 0, 0.45);
        }
        .dj-unit-top { display: flex; align-items: center; gap: 0.9rem; margin-bottom: 1.25rem; }
        .dj-digits { display: grid; place-items: center; min-width: 4rem; padding: 0.5rem 0.6rem 0.35rem; border-radius: 0.4rem; background-color: #0b0d10; color: #ff7a1a; font-family: var(--dj-display); font-weight: 700; font-size: 2.4rem; line-height: 1; letter-spacing: 0.06em; text-shadow: 0 0 0.45em rgba(255, 122, 26, 0.6); box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1), 0 1px 0 var(--dj-edge-hi); }
        .dj-unit-top .dj-silk { margin: 0; flex: 1; }
        .dj-unit h3 { font-family: var(--dj-display); font-weight: 700; font-size: 2rem; line-height: 1.05; }
        .dj-unit p { margin-top: 0.6rem; margin-bottom: 1.4rem; color: var(--dj-ink-2); }
        .dj-unit-meter { margin-top: auto; padding: 0.5rem; border-radius: 0.3rem; background-color: #0b0d10; box-shadow: 0 1px 0 var(--dj-edge-hi); }
        .dj-unit-meter .dj-bar { --dj-lvl: var(--lv); transition: --dj-lvl 1.2s steps(10) 0.3s; }
        html.es-anim #dj [data-reveal]:not(.is-revealed) .dj-unit-meter .dj-bar { --dj-lvl: 0%; }
        .dj-wire { display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 1rem; }
        @media (max-width: 979px) { .dj-wire { flex-direction: column; } }
        .dj-wire i { width: 0.55rem; aspect-ratio: 1; border-radius: 50%; background: var(--dj-led); opacity: 0.2; animation: dj-flow 1.4s steps(1) infinite; animation-delay: calc(var(--i) * 0.2s); }

        /* ---------------------------------------------------------------
           07 Sends: the patch bay of key features
           --------------------------------------------------------------- */
        .dj-sends { display: grid; gap: 1px; border-radius: 1.1rem; overflow: hidden; background: var(--dj-line); box-shadow: 0 0 0 1px var(--dj-edge-lo), 0 1.4rem 2rem -1.4rem rgba(0, 0, 0, 0.45); }
        @media (min-width: 640px) { .dj-sends { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .dj-sends { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .dj-send { position: relative; display: flex; flex-direction: column; padding: 1.5rem 1.4rem 1.4rem; background-color: var(--dj-plate-hi); transition: background-color 0.2s ease; }
        .dj-send:hover { background-color: var(--dj-plate); }
        .dj-send-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.2rem; }
        .dj-jack {
            width: 3.1rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, #050607 0 24%, #2b2f35 25% 33%, var(--dj-metal-a) 34% 50%, var(--dj-metal-b) 51% 62%, var(--dj-metal-a) 63% 72%, var(--dj-knurl-b) 73% 100%);
            box-shadow: 0 0.2rem 0.4rem rgba(0, 0, 0, 0.45), inset 0 0 0 1px rgba(0, 0, 0, 0.35);
        }
        .dj-send-led { display: inline-flex; align-items: center; gap: 0.5rem; font-family: var(--dj-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--dj-ink-3); }
        .dj-send-led .dj-led { --c: #3ddc84; opacity: 0.25; box-shadow: none; transition: opacity 0.2s ease, box-shadow 0.2s ease; }
        .dj-send:hover .dj-send-led .dj-led { opacity: 1; box-shadow: 0 0 0.6rem #3ddc84; }
        .dj-send strong { font-family: var(--dj-display); font-weight: 700; font-size: 1.7rem; line-height: 1.05; }
        .dj-send small { display: block; margin-top: 0.4rem; margin-bottom: 1.2rem; font-size: 1rem; color: var(--dj-ink-2); }
        .dj-send-go { margin-top: auto; display: inline-flex; align-items: center; gap: 0.4rem; color: var(--dj-led-ink); font-family: var(--dj-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; transition: gap 0.2s ease; }
        .dj-send:hover .dj-send-go { gap: 0.75rem; }
        .dj-sends-more { margin-top: 1.75rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the finish changes.
           --------------------------------------------------------------- */
        .dj-plans { border-top: 1px solid var(--dj-edge-lo); box-shadow: inset 0 1px 0 var(--dj-edge-hi); }
        #dj .dj-plans > section { background: transparent; }
        #dj .dj-plans h2 { font-family: var(--dj-display); font-weight: 700; font-size: clamp(2.1rem, 4.4vw, 3.4rem); line-height: 1.02; letter-spacing: -0.005em; color: var(--dj-ink); }
        #dj .dj-plans h2 + p { color: var(--dj-ink-2); font-size: 1.0625rem; }
        #dj .dj-plans .grid > div { background-color: var(--dj-plate-hi); border: 0; border-radius: 1.1rem; box-shadow: 0 0 0 1px var(--dj-edge-lo), inset 0 1px 0 var(--dj-edge-hi), 0 1.4rem 2rem -1.4rem rgba(0, 0, 0, 0.45); color: var(--dj-ink); }
        #dj .dj-plans .grid > div:nth-child(2) { box-shadow: 0 0 0 2px #ff7a1a, 0 0 1.8rem rgba(255, 122, 26, 0.3), 0 1.4rem 2rem -1.4rem rgba(0, 0, 0, 0.45); }
        #dj .dj-plans .grid > div span,
        #dj .dj-plans .grid > div p,
        #dj .dj-plans .grid > div li { color: var(--dj-ink-2); }
        #dj .dj-plans .grid > div .text-3xl { font-family: var(--dj-display); font-weight: 700; font-size: 3rem; line-height: 1; color: var(--dj-ink); }
        #dj .dj-plans .grid > div .uppercase { font-family: var(--dj-mono); color: var(--dj-ink); }
        #dj .dj-plans .grid > div .rounded-full { background-color: #0b0d10; color: #ff7a1a; border-radius: 0.25rem; font-family: var(--dj-mono); }
        #dj .dj-plans .grid > div svg { color: var(--dj-led-ink); }
        #dj .dj-plans a.font-medium { color: var(--dj-ink); border-bottom: 2px solid var(--dj-led); }
        #dj .dj-plans a.rounded-2xl { background-color: #f97712; background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0) 55%); color: #14161a; border-radius: 0.7rem; font-family: var(--dj-display); font-weight: 700; font-size: 1.2rem; letter-spacing: 0.04em; text-transform: uppercase; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 0 0 1px #a84a08, 0 0.38rem 0 #96420a, 0 0.5rem 1.7rem rgba(255, 122, 26, 0.5); }
        #dj .dj-plans a.rounded-2xl:hover { transform: none; filter: brightness(1.07); }

        .dj-keep { border-top: 1px solid var(--dj-edge-lo); box-shadow: inset 0 1px 0 var(--dj-edge-hi); }
        #dj .dj-keep > section { background: var(--dj-plate-2); border-top: 0; }
        #dj .dj-keep h2 { font-family: var(--dj-display); font-weight: 700; font-size: clamp(2rem, 4vw, 2.9rem); line-height: 1.02; color: var(--dj-ink); }
        #dj .dj-keep p.uppercase { font-family: var(--dj-mono); font-weight: 700; letter-spacing: 0.18em; color: var(--dj-led-ink); }
        #dj .dj-keep .grid > a { background-color: var(--dj-plate-hi); border: 0; border-radius: 1rem; box-shadow: 0 0 0 1px var(--dj-edge-lo), inset 0 1px 0 var(--dj-edge-hi); }
        #dj .dj-keep .grid > a:hover { box-shadow: 0 0 0 2px #ff7a1a, 0 0 1.4rem rgba(255, 122, 26, 0.3); }
        #dj .dj-keep .grid > a > span:first-child { display: none; }
        #dj .dj-keep .grid > a h3 { color: var(--dj-ink); }
        #dj .dj-keep .grid > a p { color: var(--dj-ink-2); }
        #dj .dj-keep .grid > a > span:last-child,
        #dj .dj-keep a.self-start { color: var(--dj-led-ink); }

        /* ---------------------------------------------------------------
           08 Inputs: four more lines into the desk
           --------------------------------------------------------------- */
        .dj-ins-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: end; gap: 1.25rem; margin-bottom: 2.25rem; }
        .dj-ins { display: grid; gap: 1.1rem; }
        @media (min-width: 560px) { .dj-ins { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .dj-ins { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .dj-in {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
            gap: 0.3rem 1rem;
            padding: 1.1rem 1.2rem 1rem;
            border-radius: 0.8rem;
            background-color: var(--dj-plate-hi);
            box-shadow: inset 0 1px 0 var(--dj-edge-hi), 0 0 0 1px var(--dj-edge-lo), 0 0.38rem 0 var(--dj-edge-lo), 0 1rem 1.2rem -0.7rem rgba(0, 0, 0, 0.4);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .dj-in:hover { transform: translateY(0.22rem); box-shadow: inset 0 1px 0 var(--dj-edge-hi), 0 0 0 1px var(--dj-edge-lo), 0 0.16rem 0 var(--dj-edge-lo), 0 0 1.4rem rgba(255, 122, 26, 0.35); }
        .dj-in small { grid-column: 1 / -1; display: flex; align-items: center; gap: 0.5rem; font-family: var(--dj-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--dj-ink-3); }
        .dj-in small .dj-led { opacity: 0.3; box-shadow: none; transition: opacity 0.2s ease, box-shadow 0.2s ease; }
        .dj-in:hover small .dj-led { opacity: 1; box-shadow: 0 0 0.6rem #ff7a1a; }
        .dj-in strong { font-family: var(--dj-display); font-weight: 700; font-size: 1.8rem; line-height: 1.05; }
        .dj-in svg { width: 1.3rem; height: 1.3rem; margin-bottom: 0.3rem; color: var(--dj-ink-3); }

        /* ---------------------------------------------------------------
           09 Manual: the questions, each on its own switch
           --------------------------------------------------------------- */
        .dj-manual-grid { display: grid; gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .dj-manual-grid { grid-template-columns: minmax(0, 0.74fr) minmax(0, 1.26fr); }
            .dj-manual-head { position: sticky; top: 6.5rem; }
        }
        .dj-plate-tag { display: inline-grid; gap: 0.3rem; margin-top: 2rem; padding: 0.9rem 1.1rem 0.8rem; border-radius: 0.35rem; background-color: var(--dj-plate-2); box-shadow: inset 0 1px 2px var(--dj-edge-lo), 0 1px 0 var(--dj-edge-hi); font-family: var(--dj-mono); font-size: 0.66rem; font-weight: 600; letter-spacing: 0.16em; text-transform: uppercase; color: var(--dj-ink-3); }
        .dj-qa { counter-reset: dj-q; border-top: 1px solid var(--dj-edge-lo); box-shadow: inset 0 1px 0 var(--dj-edge-hi); }
        .dj-qa details { counter-increment: dj-q; border-bottom: 1px solid var(--dj-edge-lo); box-shadow: 0 1px 0 var(--dj-edge-hi); }
        .dj-qa summary { display: grid; grid-template-columns: 2.9rem minmax(0, 1fr) auto; align-items: start; gap: 0.75rem; padding: 1.3rem 0.25rem 1.2rem; cursor: pointer; }
        .dj-qa summary::before { content: "Q." counter(dj-q, decimal-leading-zero); padding-top: 0.35rem; font-family: var(--dj-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; color: var(--dj-led-ink); }
        .dj-qa h3 { font-family: var(--dj-display); font-weight: 700; font-size: 1.45rem; line-height: 1.15; }
        .dj-qa summary i { position: relative; flex: none; width: 2.5rem; height: 1.3rem; margin-top: 0.2rem; border-radius: 0.65rem; background: #0b0d10; box-shadow: inset 0 1px 3px #000, 0 1px 0 var(--dj-edge-hi); }
        .dj-qa summary i::after { content: ""; position: absolute; top: 0.16rem; left: 0.16rem; width: 0.98rem; aspect-ratio: 1; border-radius: 50%; background: linear-gradient(180deg, #f6f7f8, #9aa0a8); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.5); transition: translate 0.25s cubic-bezier(0.34, 1.4, 0.64, 1), background 0.2s ease, box-shadow 0.2s ease; }
        .dj-qa details[open] summary i::after { translate: 1.2rem 0; background: #ff7a1a; box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.5), 0 0 0.7rem rgba(255, 122, 26, 0.9); }
        .dj-qa details p { padding: 0 0.25rem 1.6rem 3.9rem; max-width: 47rem; color: var(--dj-ink-2); }
        @media (max-width: 560px) { .dj-qa details p { padding-inline-start: 0.25rem; } }

        /* ---------------------------------------------------------------
           10 Master out: your name on the main display
           --------------------------------------------------------------- */
        .dj-out-screen { display: grid; gap: 1.5rem; padding: clamp(1.75rem, 5vw, 4rem) clamp(1.1rem, 4vw, 3rem); text-align: center; }
        @media (min-width: 900px) { .dj-out-screen { grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 3rem; } }
        .dj-out-screen .dj-vu { display: none; height: 21rem; gap: 5px; padding: 5px; }
        .dj-out-screen .dj-vu i { width: 0.9rem; -webkit-mask: repeating-linear-gradient(to top, #000 0 0.5rem, transparent 0.5rem 0.68rem); mask: repeating-linear-gradient(to top, #000 0 0.5rem, transparent 0.5rem 0.68rem); }
        @media (min-width: 900px) { .dj-out-screen .dj-vu { display: inline-flex; } }
        #dj .dj-out-screen.is-hit .dj-vu i { animation: none; --dj-lvl: 100%; }
        .dj-out-h2 { margin-top: 0.9rem; font-size: clamp(2.6rem, 7vw, 5.5rem); line-height: 0.98; }
        .dj-out-sub { margin: 1.1rem auto 0; max-width: 35rem; font-size: 1.2rem; color: #b3bfbc; }
        .dj-out-name { container-type: inline-size; margin: clamp(1.5rem, 3vw, 2.25rem) auto 0; max-width: 44rem; padding: 1.1rem 1rem 0.9rem; border-block: 1px solid rgba(255, 255, 255, 0.14); overflow: hidden; }
        /* --dj-fit is set by the script below when a typed name is wider than the display: the type
           shrinks to fit, and the line keeps its height so nothing under it moves. */
        .dj-out-name strong { display: block; font-family: var(--dj-display); font-weight: 700; font-size: calc(clamp(2.2rem, 12cqi, 5rem) * var(--dj-fit, 1)); line-height: clamp(2.2rem, 12cqi, 5rem); letter-spacing: 0.04em; text-transform: uppercase; white-space: nowrap; }
        .dj-out-name small { display: block; margin-top: 0.35rem; }
        .dj-out-form { display: grid; gap: 1.3rem; margin: 1.75rem auto 0; max-width: 27rem; }
        #dj .dj-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.1rem;
            border-radius: 0.7rem;
            background-color: #15181c;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.18);
            font-family: var(--dj-mono);
            font-weight: 700;
            font-size: clamp(1rem, 3.2vw, 1.05rem);
            transition: box-shadow 0.2s ease;
        }
        /* At 16px (anything smaller makes iOS zoom the page on focus) a phone needs the room for the placeholder. */
        @media (max-width: 420px) { #dj .dj-claim { padding-inline: 0.75rem; } }
        #dj .dj-claim:focus-within { border-color: transparent; box-shadow: inset 0 0 0 2px #ff7a1a, 0 0 1.4rem rgba(255, 122, 26, 0.35); }
        #dj .dj-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #e9f1ec; box-shadow: none; outline: none; }
        #dj .dj-claim input::placeholder { color: #8b9796; }
        .dj-claim span { flex: none; color: #b3bfbc; user-select: none; }
        .dj-out-note { margin-top: 1.25rem; color: #b3bfbc; font-size: 0.95rem; }

        @media (prefers-reduced-motion: reduce) {
            .dj-vu i, .dj-bar, .dj-blink, .dj-jog-eye i, .dj-flow i, .dj-wire i { animation: none; }
            .dj-flow i, .dj-wire i { opacity: 1; }
            .dj-btn, .dj-pad, .dj-in, .dj-more, .dj-qa summary i::after, .dj-unit-meter .dj-bar { transition: none; }
        }
    </style>

    @php
        // Eleven ticks across the 270 degrees a pot travels, printed around every knob.
        $djTickStops = [];
        for ($djTick = 0; $djTick <= 10; $djTick++) {
            $djAt = $djTick * 27;
            $djTickStops[] = 'var(--dj-ink-3) '.$djAt.'deg '.($djAt + 2.4).'deg';
            if ($djTick < 10) {
                $djTickStops[] = 'transparent '.($djAt + 2.4).'deg '.($djAt + 27).'deg';
            }
        }
        $djTicks = 'conic-gradient(from -136.2deg, '.implode(', ', $djTickStops).', transparent 272.4deg)';

        // A waveform as one clip-path: the outline of a track whose loudness follows $shape
        // (pairs of [until, level]), mirrored about the centre line. The bars are a gradient.
        $djWave = function (int $points, int $seed, array $shape): string {
            $top = [];
            $bottom = [];
            for ($i = 0; $i <= $points; $i++) {
                $t = $i / $points;
                $level = 0.3;
                foreach ($shape as [$until, $loud]) {
                    if ($t <= $until) {
                        $level = $loud;
                        break;
                    }
                }
                $noise = sin(($i + $seed) * 12.9898) * 43758.5453;
                $noise -= floor($noise);
                $kick = $i % 4 === 0 ? 1.0 : 0.7;
                $amp = max(0.07, min(1.0, $level * (0.45 + 0.55 * $noise) * $kick + ($i % 16 === 0 ? 0.12 * $level : 0)));
                $x = round($t * 100, 2);
                $top[] = $x.'% '.round(50 - $amp * 48, 1).'%';
                $bottom[] = $x.'% '.round(50 + $amp * 48, 1).'%';
            }

            return 'polygon('.implode(', ', array_merge($top, array_reverse($bottom))).')';
        };
        $djMonth = $djWave(180, 7, [[0.08, 0.3], [0.2, 0.55], [0.42, 0.95], [0.55, 0.4], [0.86, 1.0], [1.0, 0.34]]);
        $djNight = $djWave(200, 23, [[0.06, 0.22], [0.25, 0.42], [0.31, 0.3], [0.5, 0.62], [0.56, 0.4], [0.75, 1.0], [0.8, 0.5], [0.95, 0.86], [1.0, 0.3]]);

        $djArrow = '<svg class="dj-arrow" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $djDown = '<svg class="dj-arrow" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $djPlay = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15a1 1 0 0 0 1.53.85l12-7.5a1 1 0 0 0 0-1.7l-12-7.5A1 1 0 0 0 7 4.5z" /></svg>';
        $djGenres = ['House', 'Techno', 'DnB', 'Trance', 'Dubstep', 'Garage', 'Minimal', 'Electro', 'Breakbeat', 'Disco', 'Acid', 'Hardgroove'];
    @endphp

    <div id="dj" style="--dj-ticks: {{ $djTicks }};">

        <!-- ============================================================ -->
        <!-- 01. Master: the main display over the two jog wheels         -->
        <!-- ============================================================ -->
        <section class="dj-mod dj-master" id="top">
            <div class="dj-wrap">
                <p class="dj-silk es-fade-up es-d-1" aria-hidden="true"><b>01</b> Master <span class="dj-silk-r">Model ES-DJ &middot; 2 decks &middot; 6 channels</span></p>

                <div class="dj-screen dj-master-screen es-fade-up es-d-1">
                    <div class="dj-status dj-read" aria-hidden="true">
                        <span class="dj-led" style="--c: #3ddc84;"></span>
                        <span>Link</span>
                        <span><b>djnova.eventschedule.com</b></span>
                        <span class="dj-status-r">BPM <b>128.0</b></span>
                        <span>Key <b>8A</b></span>
                        <span>Rem <b>-02:14</b></span>
                    </div>

                    <h1 class="dj-h1">
                        <x-marketing.hero-eyebrow class="dj-eyebrow es-fade-up es-d-1">
                            Event Schedule for DJs &amp; Producers
                        </x-marketing.hero-eyebrow>
                        <span class="dj-h1-line es-fade-up es-d-2">Fill the dancefloor.</span>
                        <span class="dj-h1-line dj-glow es-fade-up es-d-3">Skip the algorithm.</span>
                    </h1>

                    <p class="dj-lede es-fade-up es-d-3">
                        Your residencies. Your guest spots. One link that never stops glowing. Fans hear it from you, not from a pay-to-play feed.
                    </p>

                    <!-- The overview: the month as one track, three sets as hot cues -->
                    <div class="dj-over es-fade-up es-d-4" aria-hidden="true" dir="ltr">
                        <span class="dj-cue" style="--at: 16%; --c: #ff7a1a;"><b>A</b> Fri 6 <em>&middot; Fabric</em></span>
                        <span class="dj-cue" style="--at: 44%; --c: #3ddc84;"><b>B</b> Sat 14 <em>&middot; Berghain</em></span>
                        <span class="dj-cue" style="--at: 72%; --c: #ffb020;"><b>C</b> Fri 20 <em>&middot; Fabric</em></span>
                        <div class="dj-win">
                            <div class="dj-wave" style="clip-path: {{ $djMonth }};"></div>
                            <i class="dj-head-line"></i>
                        </div>
                        <div class="dj-ruler dj-read"><span>Dec 01</span><span>08</span><span>15</span><span>22</span><span>Dec 31</span></div>
                    </div>
                </div>

                <div class="dj-surface es-fade-up es-d-4">
                    <div class="dj-transport">
                        <div class="dj-cta">
                            <a href="#features" class="dj-btn dj-btn-cue">
                                <span class="dj-led" aria-hidden="true"></span>
                                See the setup
                            </a>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="dj-btn dj-btn-play">
                                {!! $djPlay !!}
                                Create your DJ schedule
                            </a>
                        </div>

                        <!-- Genre browser -->
                        <div class="dj-screen dj-oled dj-browse">
                            <div class="es-marquee-mask">
                                <div class="es-marquee" data-marquee="1">
                                    <div class="es-marquee-track">
                                        @for ($genreCopy = 0; $genreCopy < 2; $genreCopy++)
                                            @foreach ($djGenres as $genre)
                                                <span @if ($genreCopy === 1) aria-hidden="true" @endif class="dj-read dj-genre">{{ $genre }}</span>
                                            @endforeach
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="dj-browse-key" aria-hidden="true"><span>Browse</span><span>Genre</span></div>
                    </div>

                    <div class="dj-jogs" aria-hidden="true">
                        <div class="dj-jog dj-jog-a">
                            <div class="dj-jog-plate"></div>
                            <div class="dj-jog-eye"><i></i><span>128.0<small>BPM</small></span></div>
                        </div>
                        <div class="dj-jog dj-jog-b">
                            <div class="dj-jog-plate"></div>
                            <div class="dj-jog-eye"><i></i><span>126.5<small>BPM</small></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 02. Input: the problem                                       -->
        <!-- ============================================================ -->
        <section class="dj-mod dj-lost">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>02</b> Input <span class="dj-silk-r"><span class="dj-led dj-blink" style="--c: #ff3b30;"></span> No signal</span></p>

                <div class="dj-screen dj-lost-screen">
                    <span class="dj-read" data-reveal>The problem</span>
                    <h2 class="dj-h2 dj-lost-h2" data-reveal style="--reveal-delay: 0.08s;">
                        You play at <span class="dj-glow">2 AM.</span> The algorithm posts at 9.
                    </h2>
                    <p class="dj-lost-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Your set announcement dies in a story. Your fans find out on Monday.
                    </p>

                    <div class="dj-faults" data-reveal-group="120">
                        <div class="dj-fault" data-reveal>
                            <div class="dj-bar" aria-hidden="true"></div>
                            <div class="dj-fault-big">Unseen</div>
                            <p>Your gig post is three swipes deep before doors even open.</p>
                        </div>
                        <div class="dj-fault" data-reveal style="--c: #ffb020; --t: 1.7s;">
                            <div class="dj-bar" aria-hidden="true"></div>
                            <div class="dj-fault-big">Scattered</div>
                            <p>One date on the club page, one on RA, one in a chat. No single place that is yours.</p>
                        </div>
                        <div class="dj-fault dj-fault-cut" data-reveal style="--c: #ff5a4f;">
                            <div class="dj-bar" aria-hidden="true"></div>
                            <div class="dj-fault-big">10-20%</div>
                            <p>of the door lost to ticket platform fees elsewhere. Event Schedule takes zero.</p>
                        </div>
                    </div>

                    <p class="dj-lost-foot" data-reveal>
                        Time to switch your own sign on.
                        <a href="#features">
                            See the setup
                            {!! $djDown !!}
                        </a>
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 03. Mixer: six channel strips, then the crossfader           -->
        <!-- ============================================================ -->
        <section id="features" class="dj-mod" style="scroll-margin-top: 4.5rem;">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>03</b> Mixer <span class="dj-silk-r">6 channels &middot; 1 crossfader</span></p>
                <div class="dj-head">
                    <h2 class="dj-h2" data-reveal>
                        Everything a working DJ needs, <span class="dj-lit">lit up</span>
                    </h2>
                </div>

                <div class="dj-console">
                    <div class="dj-strips">

                        <!-- CH 1. Residency tracker -->
                        <article class="dj-ch">
                            <div class="dj-tape"><b>CH 1</b> Residency Tracker</div>
                            <div class="dj-ch-body">
                                <div class="dj-ctl" aria-hidden="true">
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 48deg;"></i></div></div><span>Gain</span>
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: -32deg;"></i></div></div><span>EQ</span>
                                    <div class="dj-vu"><i></i><i style="--d: -0.7s;"></i></div>
                                </div>
                                <div class="dj-ch-main">
                                    <h3 data-reveal>Your residencies, on repeat</h3>
                                    <p data-reveal>Weekly at Fabric? Monthly guest spot at Berghain? Recurring events repeat themselves, and one-off bookings slot in beside them.</p>
                                    <div class="dj-keys">
                                        <span class="dj-key"><span class="dj-led" aria-hidden="true"></span>Weekly residencies</span>
                                        <span class="dj-key"><span class="dj-led" style="--c: #3ddc84;" aria-hidden="true"></span>Guest spots</span>
                                        <span class="dj-key"><span class="dj-led" style="--c: #ffb020;" aria-hidden="true"></span>One-off bookings</span>
                                    </div>
                                    <div class="dj-ch-gap"></div>
                                    <div class="dj-screen dj-oled dj-rows" aria-hidden="true">
                                        <div class="dj-read"><span>December</span><span>3 sets</span></div>
                                        <div class="dj-row"><b>FRI 6</b><span>Fabric<small>Every Friday</small></span><em>11 PM</em></div>
                                        <div class="dj-row"><b>SAT 14</b><span>Berghain<small>Guest spot</small></span><em>2 AM</em></div>
                                        <div class="dj-row"><b>FRI 20</b><span>Fabric<small>Every Friday</small></span><em>11 PM</em></div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <!-- CH 2. Late night -->
                        <article class="dj-ch">
                            <div class="dj-tape" style="--r: 0.6deg;"><b>CH 2</b> Late Night</div>
                            <div class="dj-ch-body">
                                <div class="dj-ctl" aria-hidden="true">
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 96deg;"></i></div></div><span>Gain</span>
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 20deg;"></i></div></div><span>EQ</span>
                                    <div class="dj-vu" style="--t: 2.3s;"><i style="--d: -0.4s;"></i><i style="--d: -1.2s;"></i></div>
                                </div>
                                <div class="dj-ch-main">
                                    <h3 data-reveal>Built for nights that cross midnight</h3>
                                    <p data-reveal>A set from 11 PM Saturday to 4 AM Sunday lists on Saturday, where your fans look for it.</p>
                                    <div class="dj-ch-gap"></div>
                                    <div class="dj-screen dj-oled dj-night" aria-hidden="true">
                                        <strong>11 PM - 4 AM</strong>
                                        <div class="dj-night-day"></div>
                                        <div class="dj-night-ends dj-read"><span>Sat</span><span>Sun</span></div>
                                        <div class="dj-night-cap dj-read">Saturday into Sunday</div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <!-- CH 3. Club sync -->
                        <article class="dj-ch">
                            <div class="dj-tape" style="--r: -0.4deg;"><b>CH 3</b> Club Sync</div>
                            <div class="dj-ch-body">
                                <div class="dj-ctl" aria-hidden="true">
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: -70deg;"></i></div></div><span>Gain</span>
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 64deg;"></i></div></div><span>EQ</span>
                                    <div class="dj-vu" style="--t: 1.6s;"><i style="--d: -0.2s;"></i><i style="--d: -0.9s;"></i></div>
                                </div>
                                <div class="dj-ch-main">
                                    <h3 data-reveal>Clubs book you, one tap lists it</h3>
                                    <p data-reveal>When a promoter adds you to a lineup, it lands in your requests. Accept it and the set is on your schedule too. No double entry, ever.</p>
                                    <div class="dj-ch-gap"></div>
                                    <div class="dj-screen dj-oled dj-patch-ui" aria-hidden="true">
                                        <div class="dj-patch-box"><span class="dj-read">Club</span><i></i><i></i></div>
                                        <div class="dj-flow"><i style="--i: 0;"></i><i style="--i: 1;"></i><i style="--i: 2;"></i><i style="--i: 3;"></i></div>
                                        <div class="dj-patch-box is-you"><span class="dj-read"><b>You</b></span><i></i><i></i></div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <!-- CH 4. One link -->
                        <article class="dj-ch">
                            <div class="dj-tape" style="--r: 0.5deg;"><b>CH 4</b> Share Link</div>
                            <div class="dj-ch-body">
                                <div class="dj-ctl" aria-hidden="true">
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 118deg;"></i></div></div><span>Gain</span>
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: -8deg;"></i></div></div><span>EQ</span>
                                    <div class="dj-vu" style="--t: 2.1s;"><i style="--d: -1.1s;"></i><i style="--d: -0.3s;"></i></div>
                                </div>
                                <div class="dj-ch-main">
                                    <h3 data-reveal>One link for RA, Linktree, SoundCloud</h3>
                                    <p data-reveal>Drop it in every bio. Fans see every upcoming set: residencies, guest spots, festivals. Or they <a href="{{ marketing_url('/docs/sharing#calendar-feeds') }}">subscribe to your calendar</a>, and a moved set time updates itself.</p>
                                    <div class="dj-ch-gap"></div>
                                    <div class="dj-screen dj-oled dj-url" aria-hidden="true" dir="ltr">
                                        <div class="dj-read">Your schedule link</div>
                                        <div class="dj-url-bar"><span class="dj-led"></span><span>djnova.eventschedule.com</span></div>
                                        <div class="dj-outs"><span>Resident Advisor</span><span>SoundCloud</span><span>Mixcloud</span></div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <!-- CH 5. Set-time flyers -->
                        <article class="dj-ch">
                            <div class="dj-tape" style="--r: -0.8deg;"><b>CH 5</b> Graphics</div>
                            <div class="dj-ch-body">
                                <div class="dj-ctl" aria-hidden="true">
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 30deg;"></i></div></div><span>Gain</span>
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 104deg;"></i></div></div><span>EQ</span>
                                    <div class="dj-vu" style="--t: 1.8s;"><i style="--d: -0.6s;"></i><i style="--d: -1.5s;"></i></div>
                                </div>
                                <div class="dj-ch-main">
                                    <h3 data-reveal>Flyers that make themselves</h3>
                                    <p data-reveal>Auto-generated set-time graphics, sized for stories, RA pages, and the group chat.</p>
                                    <div class="dj-ch-gap"></div>
                                    <div class="dj-screen dj-oled dj-flyer-ui" aria-hidden="true">
                                        <div class="dj-flyer">
                                            <small>THIS SATURDAY</small>
                                            <strong>DJ NOVA</strong>
                                            <small>@ FABRIC</small>
                                            <small>11 PM - 4 AM</small>
                                        </div>
                                        <div class="dj-flyer-sizes dj-read">
                                            <span><span class="dj-led"></span>Stories</span>
                                            <span><span class="dj-led" style="--c: #3ddc84;"></span>RA pages</span>
                                            <span><span class="dj-led" style="--c: #ffb020;"></span>Group chat</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <!-- CH 6. Ticket window -->
                        <article class="dj-ch">
                            <div class="dj-tape" style="--r: 0.7deg;"><b>CH 6</b> Ticketing</div>
                            <div class="dj-ch-body">
                                <div class="dj-ctl" aria-hidden="true">
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 132deg;"></i></div></div><span>Gain</span>
                                    <div class="dj-well"><div class="dj-knob"><i style="--a: 54deg;"></i></div></div><span>EQ</span>
                                    <div class="dj-vu" style="--t: 2.5s;"><i style="--d: -0.8s;"></i><i style="--d: -0.1s;"></i></div>
                                </div>
                                <div class="dj-ch-main">
                                    <h3 data-reveal>Your door, your money</h3>
                                    <p data-reveal>Advance tickets straight from your schedule. QR check-in at the door. The platform fee is a round number: zero.</p>
                                    <div class="dj-ch-gap"></div>
                                    <div class="dj-screen dj-oled dj-fee" aria-hidden="true">
                                        <div class="dj-read">Platform fee</div>
                                        <strong>{{ plan_price(0) }}</strong>
                                        <div class="dj-read">Only processing comes off</div>
                                        <div class="dj-fee-to"><span class="dj-led" style="--c: #3ddc84;"></span><span>Direct to your Stripe or PayPal</span></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- The crossfader: three places on A, one link on B -->
                    <div class="dj-xf" id="dj-xf">
                        <div class="dj-xf-top">
                            <p class="dj-silk" aria-hidden="true"><b>X</b> Crossfader</p>
                            <span class="dj-xf-hint" aria-hidden="true">Slide it</span>
                        </div>
                        <div class="dj-screen dj-xf-stage" aria-hidden="true" dir="ltr">
                            <span class="dj-read dj-xf-cap dj-xf-cap-a">Source <b>3 places</b></span>
                            <span class="dj-read dj-xf-cap dj-xf-cap-b">Source <b>1 link</b></span>
                            <div class="dj-xf-list">
                                <div class="dj-xf-url"><span class="dj-led" style="--c: #3ddc84;"></span>djnova.eventschedule.com</div>
                                <div class="dj-xf-slot">
                                    <div class="dj-xf-line"><b>FRI 6</b><span>Fabric</span><em>11 PM</em></div>
                                    <div class="dj-xf-src" style="--tx: -30%; --ty: -70%; --r: -6deg; --bg: #e8eaed; --fg: #14161a;"><span><small>Club page</small><b>Fri 6 &middot; Fabric</b></span><span>11 PM</span></div>
                                </div>
                                <div class="dj-xf-slot">
                                    <div class="dj-xf-line"><b>SAT 14</b><span>Berghain</span><em>2 AM</em></div>
                                    <div class="dj-xf-src" style="--tx: 30%; --ty: -10%; --r: 4deg; --bg: #2a2e34; --fg: #e9f1ec;"><span><small>RA</small><b>Sat 14 &middot; Berghain</b></span><span>2 AM</span></div>
                                </div>
                                <div class="dj-xf-slot">
                                    <div class="dj-xf-line"><b>FRI 20</b><span>Fabric</span><em>11 PM</em></div>
                                    <div class="dj-xf-src" style="--tx: -20%; --ty: 30%; --r: -3deg; --bg: #1f7a4a; --fg: #ffffff;"><span><small>Group chat</small><b>Fri 20 &middot; Fabric</b></span><span>11 PM</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="dj-xf-rail">
                            <span class="dj-xf-end" aria-hidden="true"><b>A</b>Scattered</span>
                            <label class="dj-xf-fader">
                                <span class="sr-only">Crossfade from three scattered posts to one link</span>
                                <input type="range" id="dj-xf-input" min="0" max="100" value="100" step="1">
                            </label>
                            <span class="dj-xf-end" aria-hidden="true"><b>B</b>One link</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 04. Deck: one night, whole career                            -->
        <!-- ============================================================ -->
        <section class="dj-mod dj-deck" id="set-times" style="scroll-margin-top: 4.5rem;">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>04</b> Deck <span class="dj-silk-r">Set times &middot; 4 hot cues</span></p>
                <div class="dj-head">
                    <h2 class="dj-h2" data-reveal>
                        One DJ schedule for <span class="dj-lit">every slot</span> on the bill
                    </h2>
                    <p class="dj-sub" data-reveal style="--reveal-delay: 0.08s;">
                        Start on the early slot. The schedule grows with you.
                    </p>
                </div>

                <div class="dj-screen dj-deck-screen" aria-hidden="true" dir="ltr" data-reveal>
                    <span class="dj-cue" style="--at: 12.5%; --c: #ff7a1a;"><b>A</b> <em>22:00</em></span>
                    <span class="dj-cue" style="--at: 37.5%; --c: #3ddc84;"><b>B</b> <em>00:00</em></span>
                    <span class="dj-cue" style="--at: 62.5%; --c: #ffb020;"><b>C</b> <em>02:00</em></span>
                    <span class="dj-cue dj-cue-end" style="--at: 87.5%; --c: #e9f1ec;"><b>D</b> <em>04:00</em></span>
                    <div class="dj-win">
                        <div class="dj-wave" style="clip-path: {{ $djNight }};"></div>
                        <i class="dj-head-line"></i>
                    </div>
                    <div class="dj-ruler dj-read"><span>Doors</span><span>Peak</span><span>Close</span></div>
                </div>

                <div class="dj-hot">
                    <div class="dj-hot-slot">
                        <span class="dj-hot-key" style="--c: #ff7a1a; --from: 18%; --to: 24%;" aria-hidden="true">A</span>
                        <div class="dj-hot-time" dir="ltr">22:00</div>
                        <h3>Open decks</h3>
                        <p>Add your first set in minutes. Type it in, import from Google Calendar, or let AI parse the booking email. Drafts are free until you are ready.</p>
                    </div>
                    <div class="dj-hot-slot">
                        <span class="dj-hot-key" style="--c: #3ddc84; --from: 28%; --to: 34%;" aria-hidden="true">B</span>
                        <div class="dj-hot-time" dir="ltr">00:00</div>
                        <h3>Resident</h3>
                        <p>Set your night to repeat weekly or monthly, clone a one-off in one tap, and let sub-schedules keep each club night or brand separate.</p>
                    </div>
                    <div class="dj-hot-slot">
                        <span class="dj-hot-key" style="--c: #ffb020; --from: 38%; --to: 44%;" aria-hidden="true">C</span>
                        <div class="dj-hot-time" dir="ltr">02:00 <span class="dj-onair"><span class="dj-led dj-blink" aria-hidden="true"></span>On air</span></div>
                        <h3>Headline</h3>
                        <p>Fans sign up for email and hear automatically when you add a date. One newsletter fills the floor before you are on.</p>
                    </div>
                    <div class="dj-hot-slot">
                        <span class="dj-hot-key" style="--c: #e9f1ec; --from: 48%; --to: 54%;" aria-hidden="true">D</span>
                        <div class="dj-hot-time" dir="ltr">04:00</div>
                        <h3>Festival closer</h3>
                        <p>On Enterprise, your manager or agency gets their own login. Venues add you to their lineups and you accept with one tap. You just play.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 05. Pads: every type of DJ                                   -->
        <!-- ============================================================ -->
        <section class="dj-mod" id="pads" style="scroll-margin-top: 4.5rem;">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>05</b> Pads <span class="dj-silk-r">Bank A &middot; 6 pads</span></p>
                <div class="dj-pads-head">
                    <div>
                        <h2 class="dj-h2" data-reveal>
                            Whether you spin vinyl or <span class="dj-lit">push buttons</span>
                        </h2>
                        <p class="dj-sub" data-reveal style="--reveal-delay: 0.08s;">
                            Event Schedule works for every type of DJ.
                        </p>
                    </div>
                    <div class="dj-switch" aria-hidden="true" data-reveal>Vinyl <i></i> <b>Buttons</b></div>
                </div>

                @php
                    $djPads = [
                        ['Resident DJs', 'Track your weekly slots and build loyal locals who know exactly where to find you.', 'for-resident-djs', '#ff7a1a'],
                        ['Touring DJs', 'Share your international dates with fans worldwide. They\'ll know when you\'re in their city.', 'for-touring-djs', '#3ddc84'],
                        ['B2B Partners', 'Show joint sets and collaborations. One event, on both schedules once your partner accepts.', 'for-b2b-djs', '#ffb020'],
                        ['Underground DJs', 'Warehouse parties, afters, secret locations. Share with your inner circle only.', 'for-underground-djs', '#ffb020'],
                        ['Open Format DJs', 'Weddings, corporate gigs, private events. Keep your public and private bookings organized.', 'for-open-format-djs', '#ff7a1a'],
                        ['Producers', 'Live sets, album launches, listening parties. Show fans where to hear your music live.', 'for-dj-producers', '#3ddc84'],
                    ];
                @endphp

                <div class="dj-bank" data-reveal-group="70">
                    @foreach ($djPads as $djPadIndex => [$djPadName, $djPadDesc, $djPadSlug, $djPadLed])
                        @php $djPost = get_sub_audience_blog($djPadSlug); @endphp
                        <article class="dj-pad" style="--c: {{ $djPadLed }};" data-reveal>
                            <span class="dj-pad-no" aria-hidden="true">Pad {{ $djPadIndex + 1 }}</span>
                            <h3>{{ $djPadName }}</h3>
                            <p>{{ $djPadDesc }}</p>
                            @if ($djPost)
                                <a href="{{ blog_url('/' . $djPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $djPadName }}">
                                    <span>Learn more {!! $djArrow !!}</span>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 06. Signal chain: how it works                               -->
        <!-- ============================================================ -->
        <section class="dj-mod" id="chain" style="scroll-margin-top: 4.5rem;">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>06</b> Signal chain <span class="dj-silk-r">In the booth</span></p>
                <div class="dj-head">
                    <h2 class="dj-h2" data-reveal>
                        Your DJ schedule, live in <span class="dj-lit">three steps</span>
                    </h2>
                </div>

                <div class="dj-chain">
                    <div class="dj-unit" data-reveal>
                        <div class="dj-unit-top" aria-hidden="true"><span class="dj-digits">01</span><p class="dj-silk">Input</p></div>
                        <h3>Add your sets</h3>
                        <p>Import from Google Cal or add manually. Residencies auto-repeat weekly or monthly.</p>
                        <div class="dj-unit-meter" aria-hidden="true"><div class="dj-bar" style="--lv: 32%;"></div></div>
                    </div>
                    <div class="dj-wire" aria-hidden="true"><i style="--i: 0;"></i><i style="--i: 1;"></i><i style="--i: 2;"></i><i style="--i: 3;"></i></div>
                    <div class="dj-unit" data-reveal style="--reveal-delay: 0.14s;">
                        <div class="dj-unit-top" aria-hidden="true"><span class="dj-digits">02</span><p class="dj-silk">Send</p></div>
                        <h3>Drop your link</h3>
                        <p>Add to your RA profile, Linktree, SoundCloud bio. Anywhere fans find you.</p>
                        <div class="dj-unit-meter" aria-hidden="true"><div class="dj-bar" style="--lv: 64%;"></div></div>
                    </div>
                    <div class="dj-wire" aria-hidden="true"><i style="--i: 0;"></i><i style="--i: 1;"></i><i style="--i: 2;"></i><i style="--i: 3;"></i></div>
                    <div class="dj-unit" data-reveal style="--reveal-delay: 0.28s;">
                        <div class="dj-unit-top" aria-hidden="true"><span class="dj-digits">03</span><p class="dj-silk">Master</p></div>
                        <h3>Pack the dancefloor</h3>
                        <p>Fans sign up for email, hear automatically when you're spinning, and show up ready to dance.</p>
                        <div class="dj-unit-meter" aria-hidden="true"><div class="dj-bar" style="--lv: 100%;"></div></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 07. Sends: key features                                      -->
        <!-- ============================================================ -->
        <section class="dj-mod">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>07</b> Sends <span class="dj-silk-r">4 outputs</span></p>
                <div class="dj-head">
                    <h2 class="dj-h2" data-reveal>Key <span class="dj-lit">features</span></h2>
                </div>

                @php
                    $djSends = [
                        ['Ticketing', 'Sell tickets with QR check-in and zero platform fees', marketing_url('/features/ticketing')],
                        ['Event Graphics', 'Auto-generate set-time flyers sized for your socials', marketing_url('/features/event-graphics')],
                        ['Newsletters', 'Send event updates directly to followers\' inboxes', marketing_url('/features/newsletters')],
                        ['Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', marketing_url('/features/calendar-sync')],
                    ];
                @endphp
                <div class="dj-sends" data-reveal>
                    @foreach ($djSends as $djSendIndex => [$djSendName, $djSendDesc, $djSendUrl])
                        <a href="{{ $djSendUrl }}" class="dj-send">
                            <span class="dj-send-top" aria-hidden="true">
                                <span class="dj-jack"></span>
                                <span class="dj-send-led"><span class="dj-led"></span>Out {{ $djSendIndex + 1 }}</span>
                            </span>
                            <strong>{{ $djSendName }}</strong>
                            <small>{{ $djSendDesc }}</small>
                            <span class="dj-send-go" aria-hidden="true">Patch in {!! $djArrow !!}</span>
                        </a>
                    @endforeach
                </div>
                <p class="dj-sends-more" data-reveal>
                    <a href="{{ marketing_url('/features') }}" class="dj-more">
                        See all features
                        {!! $djArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <div class="dj-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 08. Inputs: related pages                                    -->
        <!-- ============================================================ -->
        <section class="dj-mod">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>08</b> Inputs <span class="dj-silk-r">4 lines</span></p>
                <div class="dj-ins-head">
                    <h2 class="dj-h2" data-reveal>Related <span class="dj-lit">pages</span></h2>
                    <a href="{{ marketing_url('/use-cases') }}" class="dj-more" data-reveal>
                        See all use cases
                        {!! $djArrow !!}
                    </a>
                </div>

                <div class="dj-ins" data-reveal-group="70">
                    @foreach ([['/for-musicians', 'Musicians'], ['/for-nightclubs', 'Nightclubs'], ['/for-bars', 'Bars'], ['/for-live-concerts', 'Live Concerts']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="dj-in" data-reveal>
                            <small><span class="dj-led" aria-hidden="true"></span>Event Schedule for</small>
                            <strong>{{ $relName }}</strong>
                            {!! $djArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 09. Manual: FAQ                                              -->
        <!-- ============================================================ -->
        <section class="dj-mod" id="faq" style="scroll-margin-top: 4.5rem;">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>09</b> Manual <span class="dj-silk-r">Operating instructions</span></p>
                <div class="dj-manual-grid">
                    <div class="dj-manual-head">
                        <h2 class="dj-h2" data-reveal>
                            Frequently asked <span class="dj-lit">questions</span>
                        </h2>
                        <p class="dj-sub" data-reveal style="--reveal-delay: 0.08s;">
                            Everything DJs ask about Event Schedule.
                        </p>
                        <div class="dj-plate-tag" aria-hidden="true" data-reveal style="--reveal-delay: 0.16s;">
                            <span>Model ES-DJ</span>
                            <span>Platform fee 0%</span>
                            <span>Made for the booth</span>
                        </div>
                    </div>

                    <div class="dj-qa" data-reveal>
                        @php
                            $faqs = [
                                ['q' => 'Can I track both residencies and one-off bookings?', 'a' => 'Yes. Set up recurring events for your weekly or monthly residencies and they auto-repeat on your schedule. Add guest spots and festival bookings as one-off events. Everything shows up in one clean calendar that fans can follow.'],
                                ['q' => 'Does it handle late-night sets that cross midnight?', 'a' => 'Yes. Event Schedule handles overnight events correctly. A set that starts at 11 PM Saturday and ends at 4 AM Sunday displays properly on the Saturday listing, so fans know when to show up.'],
                                ['q' => 'What happens when a club adds me to their lineup?', 'a' => 'It arrives as a request on your schedule. Accept it and the set shows on your link too, with no double entry, and because both schedules share one event, a changed set time shows on both. The event page lists the whole lineup, whether or not every DJ on it has signed up.'],
                                ['q' => 'A promoter listed me before I joined. Is that page mine?', 'a' => 'It can be. Naming a DJ who is not on Event Schedule creates a page for them, so the name can appear on the lineup. The page says who created it and that you have not claimed it yet, credits each date to the club or promoter that added it, and stays out of search engines. Press Claim this page, sign in with the email address it carries, and it becomes your schedule, with those promoters still listing you. If it is not you, press This is not me.'],
                                ['q' => 'Can I sell advance tickets to my sets?', 'a' => 'Yes. Connect your own Stripe or PayPal account, or take cash at the door, and sell tickets directly from your schedule with zero platform fees. Each ticket includes a unique QR code for check-in at the door, and you keep 100% of the sale minus your payment provider\'s fee. A Stripe or PayPal sale can be refunded in full or in part from the Sales page, and the money goes back through the provider. Not on sale yet? Switch on the "Notify me" card and fans can leave just an email address on the event page to hear when tickets go on sale.'],
                                ['q' => 'Can fans add my set times to their own calendar?', 'a' => 'Yes. Your page offers a live calendar feed that fans can subscribe to in Google Calendar, Apple Calendar or Outlook, with no email address needed. Unlike a one-off download, it updates itself when a set time moves. Each event page also has its own Add to Calendar button.'],
                                ['q' => 'Can I keep private gigs and secret parties off my public schedule?', 'a' => 'Yes. Draft events are free and stay hidden until you publish them. On the Enterprise plan you can also mark events internal for your team only, or unlisted with an optional password, so a wedding, corporate booking, or secret location party is reachable only by direct link.'],
                                ['q' => 'Can I run separate club nights or brands on one schedule?', 'a' => 'Yes. Sub-schedules are free and let you group events by club night or brand, so your weekly techno night and your open format bookings stay organized under one account. Your link still shows everything in one place, and you can embed the calendar on any website.'],
                            ];
                        @endphp
                        @foreach ($faqs as ['q' => $q, 'a' => $a])
                            <details name="faq">
                                <summary>
                                    <h3>{{ $q }}</h3>
                                    <i aria-hidden="true"></i>
                                </summary>
                                <p>{{ $a }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- 10. Master out: your name on the main display                -->
        <!-- ============================================================ -->
        <section id="claim" class="dj-mod" style="scroll-margin-top: 4rem;">
            <div class="dj-wrap">
                <p class="dj-silk" aria-hidden="true"><b>10</b> Master out <span class="dj-silk-r"><span class="dj-led dj-blink" style="--c: #ff3b30;"></span> Live</span></p>

                <div class="dj-screen dj-out-screen" id="dj-out" data-reveal="panel">
                    <div class="dj-vu" aria-hidden="true" style="--t: 1.5s;"><i></i><i style="--d: -0.5s;"></i></div>

                    <div>
                        <span class="dj-read" aria-hidden="true">Now playing</span>
                        <h2 class="dj-h2 dj-out-h2">
                            Your name in <span class="dj-glow">lights.</span>
                        </h2>
                        <p class="dj-out-sub">
                            Stop posting into the void. Put every set on one link that never sleeps. Free forever.
                        </p>

                        <!-- The main display: mirrors the name typed below -->
                        <div class="dj-out-name" aria-hidden="true" dir="ltr">
                            <strong class="dj-glow" id="dj-out-name">dj-nova</strong>
                            <small class="dj-read">.eventschedule.com</small>
                        </div>

                        <div class="dj-out-form">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim dj-claim">
                                <input id="es-claim-input" type="text" placeholder="dj-name" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="dj-btn dj-btn-play">
                                {!! $djPlay !!}
                                Get Started Free
                            </a>
                        </div>
                        <p class="dj-out-note">No credit card required</p>
                    </div>

                    <div class="dj-vu" aria-hidden="true" style="--t: 1.5s;"><i style="--d: -0.9s;"></i><i style="--d: -0.2s;"></i></div>
                </div>
            </div>
        </section>

        <div class="dj-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    {{-- Two small wires. The crossfader copies its value into --x and plays itself once when it
         comes into view; the master display mirrors the name typed below it, with the same slug
         rules as the shared claim input, and the meters peak on every keystroke. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            var xf = document.getElementById('dj-xf');
            var fader = document.getElementById('dj-xf-input');
            if (xf && fader) {
                var set = function (value) {
                    xf.style.setProperty('--x', (value / 100).toFixed(3));
                    fader.setAttribute('aria-valuetext', value < 50 ? 'Three scattered posts' : 'One link');
                };
                set(fader.value);
                xf.classList.add('is-live');
                fader.addEventListener('input', function () { set(fader.value); });

                if (!calm && 'IntersectionObserver' in window) {
                    var touched = false;
                    ['pointerdown', 'keydown'].forEach(function (name) {
                        fader.addEventListener(name, function () { touched = true; });
                    });
                    fader.value = 0;
                    set(0);
                    var io = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (!entry.isIntersecting) { return; }
                            io.disconnect();
                            var start = null;
                            var step = function (now) {
                                if (touched) { return; }
                                if (start === null) { start = now; }
                                var t = Math.min(1, Math.max(0, (now - start - 500) / 1900));
                                var eased = t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
                                fader.value = Math.round(eased * 100);
                                set(fader.value);
                                if (t < 1) { requestAnimationFrame(step); }
                            };
                            requestAnimationFrame(step);
                        });
                    }, { threshold: 0.55 });
                    io.observe(xf);
                }
            }

            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('dj-out-name');
            var out = document.getElementById('dj-out');
            if (input && sign) {
                var fallback = sign.textContent;
                var timer = null;
                {{-- A long name is scaled down to the width of the display instead of being cut by it. --}}
                var fit = function () {
                    sign.style.setProperty('--dj-fit', '1');
                    if (sign.scrollWidth > sign.clientWidth) {
                        sign.style.setProperty('--dj-fit', (sign.clientWidth / sign.scrollWidth * 0.97).toFixed(3));
                    }
                };
                window.addEventListener('resize', fit);
                input.addEventListener('input', function () {
                    var slug = input.value.toLowerCase()
                        .replace(/['’]/g, '')
                        .replace(/[^a-z0-9-]+/g, '-')
                        .replace(/-{2,}/g, '-')
                        .replace(/^-+/, '')
                        .slice(0, 30);
                    sign.textContent = slug || fallback;
                    fit();
                    if (out && !calm) {
                        out.classList.add('is-hit');
                        clearTimeout(timer);
                        timer = setTimeout(function () { out.classList.remove('is-hit'); }, 160);
                    }
                });
            }
        })();
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
