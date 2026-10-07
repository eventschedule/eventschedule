<x-marketing-layout>
    <x-slot name="title">Free Virtual Conference Agenda & Ticketing Software</x-slot>
    <x-slot name="description">Put a virtual conference online: one event per day with a timed agenda inside it, one join link, free registration or tickets, and zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Virtual Conferences</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typeface, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Space Grotesk') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Virtual Conferences"
        description="Run a virtual conference day as one event with its running order inside it: every session is a part with its own start and end time, published on one link with one join link and zero platform fees."
        audience="Virtual Conference Organizers"
        keywords="virtual conference platform, online conference scheduling, conference agenda, virtual summit, conference ticketing" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a virtual conference agenda online with Event Schedule",
        "description": "A conference day is one event. The running order goes inside it.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Create the day",
                "text": "One event per conference day: its date, its start time, how long it runs, and the link people join."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Type the running order",
                "text": "Add each session as a part of the agenda with a name, a start time and an end time, then move the parts into order."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the doors",
                "text": "Free registration with a capacity limit on any plan, or named ticket types with prices on Pro. Share one link for the whole programme."
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
           For-virtual-conferences "Sessions" styles. The page is the site
           a conference makes for itself in the week before it opens: one
           spectacular object, the badge, and one useful one, the agenda.

           The product has no tracks and no rooms (the page says so), so
           the agenda's columns are DAYS: one event per date, its parts
           placed on a shared time grid by named lines (t0900 ... t1600).
           The day chips work with :has() and no script, and a container
           query re-lays the grid as a list on a narrow screen.

           The hologram is four colours and no more: cyan, green, yellow,
           orange. Everything is scoped under #vc.
           ============================================================== */

        @property --vc-foil { syntax: '<angle>'; inherits: true; initial-value: 0deg; }
        @property --vc-spin { syntax: '<angle>'; inherits: false; initial-value: 0deg; }
        @property --vc-now { syntax: '<integer>'; inherits: true; initial-value: 102; }

        #vc {
            --vc-bg: #f6f5f1;
            --vc-bg-2: #eceae3;
            --vc-card: #ffffff;
            --vc-ink: #0e0f12;
            --vc-ink-2: #3b3e45;
            --vc-ink-3: #5a5e66;
            --vc-line: rgba(14, 15, 18, 0.13);
            --vc-line-2: rgba(14, 15, 18, 0.32);
            /* The hologram as ink (c) and as light (b). On paper the ink set is deepened. */
            --vc-c1: #0c6478;
            --vc-c2: #166534;
            --vc-c3: #92400e;
            --vc-c4: #c2410c;
            --vc-btn: #0e0f12;
            --vc-on-btn: #f6f5f1;
            --vc-holo: linear-gradient(100deg, var(--vc-c1), var(--vc-c2) 36%, var(--vc-c3) 68%, var(--vc-c4));
            --vc-sans: 'Space Grotesk', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --vc-mono: ui-monospace, 'SF Mono', SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            --vc-top: 4.3rem;
            position: relative;
            background: var(--vc-bg);
            color: var(--vc-ink);
            font-family: var(--vc-sans);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #vc,
        #vc .vc-band {
            --vc-bg: #0b0c0e;
            --vc-bg-2: #101216;
            --vc-card: #14161b;
            --vc-ink: #f4f3ee;
            --vc-ink-2: #c9cac6;
            --vc-ink-3: #a2a4a1;
            --vc-line: rgba(244, 243, 238, 0.14);
            --vc-line-2: rgba(244, 243, 238, 0.36);
            --vc-c1: #22d3ee;
            --vc-c2: #4ade80;
            --vc-c3: #fde047;
            --vc-c4: #fb923c;
            --vc-btn: #f4f3ee;
            --vc-on-btn: #0b0c0e;
            --vc-holo: linear-gradient(100deg, var(--vc-c1), var(--vc-c2) 36%, var(--vc-c3) 68%, var(--vc-c4));
        }

        /* The bar above takes the page ground. */
        body > header.sticky {
            background-color: rgba(246, 245, 241, 0.88);
            border-bottom-color: rgba(14, 15, 18, 0.13);
        }
        .dark body > header.sticky {
            background-color: rgba(11, 12, 14, 0.88);
            border-bottom-color: rgba(244, 243, 238, 0.14);
        }

        #vc ::selection { background: #fde047; color: #0e0f12; }
        #vc a:focus-visible,
        #vc summary:focus-visible,
        #vc .vc-f:focus-visible + label {
            outline: 2.5px solid var(--vc-c1);
            outline-offset: 3px;
        }

        .vc-wrap { width: min(100% - 2.5rem, 80rem); margin-inline: auto; }
        .vc-sec { position: relative; padding-block: clamp(4.5rem, 9vw, 8.5rem); border-top: 1px solid var(--vc-line); }
        .vc-alt { background: var(--vc-bg-2); }
        .vc-band { background: var(--vc-bg); color: var(--vc-ink); overflow: clip; }

        /* Type */
        .vc-kick {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-family: var(--vc-mono);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.3;
            color: var(--vc-ink-2);
        }
        .vc-kick b { font-weight: 700; color: var(--vc-ink); }
        .vc-kick i { flex: none; width: 2.75rem; height: 1px; background: var(--vc-line-2); }
        .vc-h2 {
            margin-top: 1.1rem;
            font-size: clamp(2.3rem, 5.6vw, 4.6rem);
            font-weight: 700;
            letter-spacing: -0.045em;
            line-height: 0.98;
            text-wrap: balance;
        }
        .vc-holo-text {
            background: var(--vc-holo);
            background-size: 180% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
            animation: vc-shift 9s ease-in-out infinite alternate;
        }
        @keyframes vc-shift { from { background-position: 0% 50%; } to { background-position: 100% 50%; } }
        .vc-sub { color: var(--vc-ink-2); font-size: clamp(1.06rem, 1.4vw, 1.2rem); max-width: 40rem; }
        .vc-head { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.4rem 4rem; margin-bottom: clamp(2.25rem, 5vw, 4rem); }
        @media (min-width: 960px) {
            .vc-head { grid-template-columns: minmax(0, 1.25fr) minmax(0, 0.75fr); align-items: end; }
            .vc-head .vc-sub { padding-bottom: 0.5rem; }
        }
        .vc-head-solo { display: block; }
        .vc-link {
            color: var(--vc-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--vc-c4);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.22em;
        }
        .vc-link:hover { text-decoration-color: var(--vc-c1); }
        .vc-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--vc-mono);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--vc-ink);
            padding-bottom: 0.2rem;
            border-bottom: 2px solid var(--vc-c4);
            transition: gap 0.2s ease;
        }
        .vc-more:hover { gap: 0.85rem; }
        .vc-more svg { width: 1rem; height: 1rem; }

        /* Tier tags: Free is a ring with a green lamp, the paid ones are solid. */
        .vc-tier {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.24rem 0.6rem 0.2rem;
            border: 1px solid var(--vc-line-2);
            border-radius: 999px;
            font-family: var(--vc-mono);
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            line-height: 1.2;
            white-space: nowrap;
            color: var(--vc-ink);
        }
        .vc-tier::before { content: ""; width: 0.42rem; aspect-ratio: 1; border-radius: 50%; background: var(--vc-c2); }
        .vc-tier-ent { background: var(--vc-ink); border-color: var(--vc-ink); color: var(--vc-bg); }
        .vc-tier-ent::before { background: #fde047; }
        .vc-titled { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.75rem; }
        .vc-h3 { font-size: 1.3rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1.15; }

        /* Buttons */
        .vc-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.45rem;
            border: 1.5px solid var(--vc-btn);
            border-radius: 999px;
            background: var(--vc-btn);
            color: var(--vc-on-btn);
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
            line-height: 1.1;
            transition: translate 0.2s ease;
        }
        .vc-btn svg { width: 1.1rem; height: 1.1rem; transition: translate 0.2s ease; }
        .vc-btn:hover { translate: 0 -2px; }
        .vc-btn:hover svg { translate: 3px 0; }
        /* A ring of foil stands a hair off the primary button and turns. */
        .vc-btn-holo::before {
            content: "";
            position: absolute;
            inset: -5.5px;
            padding: 2.5px;
            border-radius: inherit;
            background: conic-gradient(from var(--vc-spin), #22d3ee, #4ade80, #fde047, #fb923c, #22d3ee);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            mask-composite: exclude;
            animation: vc-spin 5s linear infinite;
            pointer-events: none;
        }
        @keyframes vc-spin { to { --vc-spin: 360deg; } }
        .vc-btn-ghost { background: transparent; color: var(--vc-ink); border-color: var(--vc-line-2); }
        .vc-btn-ghost:hover { border-color: var(--vc-ink); }
        .vc-btn-ghost:hover svg { translate: 0 3px; }

        /* A panel, with registration marks at its corners. */
        .vc-panel {
            position: relative;
            min-width: 0;
            background: var(--vc-card);
            border: 1px solid var(--vc-line);
            border-radius: 0.5rem;
            padding: clamp(1.25rem, 2.4vw, 1.9rem);
        }
        .vc-marks::before {
            content: "";
            position: absolute;
            inset: -7px;
            pointer-events: none;
            --m: var(--vc-line-2);
            background:
                linear-gradient(var(--m), var(--m)) 0 0 / 14px 1px no-repeat,
                linear-gradient(var(--m), var(--m)) 0 0 / 1px 14px no-repeat,
                linear-gradient(var(--m), var(--m)) 100% 0 / 14px 1px no-repeat,
                linear-gradient(var(--m), var(--m)) 100% 0 / 1px 14px no-repeat,
                linear-gradient(var(--m), var(--m)) 0 100% / 14px 1px no-repeat,
                linear-gradient(var(--m), var(--m)) 0 100% / 1px 14px no-repeat,
                linear-gradient(var(--m), var(--m)) 100% 100% / 14px 1px no-repeat,
                linear-gradient(var(--m), var(--m)) 100% 100% / 1px 14px no-repeat;
        }
        .vc-note { margin-top: 1.1rem; font-size: 0.9rem; line-height: 1.55; color: var(--vc-ink-3); }

        /* ---------------------------------------------------------------
           The foil. Bands of the four colours slide across silver as the
           badge turns; a fine line screen and a soft highlight ride on top.
           --vc-tx and --vc-ty (-1 to 1) come from the pointer.
           --------------------------------------------------------------- */
        .vc-foil {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            background: linear-gradient(135deg, #dfe4e8, #fbfcfc 42%, #d3d9de);
            color: #0e0f12;
            animation: vc-foil 7s ease-in-out infinite alternate;
        }
        .vc-foil::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: repeating-linear-gradient(calc(118deg + var(--vc-foil) + var(--vc-tx, 0) * 24deg), #22d3ee 0%, #4ade80 5%, #fde047 10%, #fb923c 15%, #22d3ee 20%);
            background-size: 260% 260%;
            background-position: calc(50% + var(--vc-tx, 0) * 40%) calc(50% + var(--vc-ty, 0) * 40%);
            opacity: 0.92;
        }
        .vc-foil::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                repeating-linear-gradient(-52deg, rgba(255, 255, 255, 0.6) 0 1px, transparent 1px 4px),
                radial-gradient(120% 90% at calc(50% + var(--vc-tx, 0) * 60%) calc(15% + var(--vc-ty, 0) * 50%), rgba(255, 255, 255, 0.75), transparent 55%);
            mix-blend-mode: soft-light;
        }
        @keyframes vc-foil { to { --vc-foil: 32deg; } }

        /* A pattern that reads as a code and is not one: a single element and its shadows. */
        .vc-qr { --qr: 3.3rem; flex: none; width: var(--qr); height: var(--qr); }
        .vc-qr i { display: block; width: 1em; height: 1em; background: currentColor; font-size: calc(var(--qr) / 15); }

        /* ---------------------------------------------------------------
           Hero: the headline, and the badge on its lanyard
           --------------------------------------------------------------- */
        .vc-hero { position: relative; overflow: clip; }
        .vc-hero-dots {
            position: absolute;
            inset: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, var(--vc-line-2) 1px, transparent 1.6px);
            background-size: 30px 30px;
            -webkit-mask-image: radial-gradient(110% 90% at 72% 28%, #000 8%, transparent 70%);
            mask-image: radial-gradient(110% 90% at 72% 28%, #000 8%, transparent 70%);
            opacity: 0.75;
        }
        .vc-hero-glow {
            position: absolute;
            right: -4%;
            top: 4%;
            width: min(44rem, 70vw);
            aspect-ratio: 1;
            border-radius: 50%;
            pointer-events: none;
            background: conic-gradient(from var(--vc-foil), #22d3ee, #4ade80, #fde047, #fb923c, #22d3ee);
            filter: blur(90px);
            opacity: 0.14;
            animation: vc-foil 9s ease-in-out infinite alternate;
        }
        .dark .vc-hero-glow { opacity: 0.2; }
        .vc-hero-grid {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem 3rem;
            padding-block: clamp(2.25rem, 5vw, 3.75rem) clamp(2.25rem, 4vw, 3.5rem);
        }
        @media (min-width: 1000px) {
            .vc-hero-grid { grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr); align-items: center; }
        }
        .vc-hero-copy { container-type: inline-size; min-width: 0; }
        .vc-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 1.5rem;
            font-family: var(--vc-mono);
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            line-height: 1.4;
            color: var(--vc-ink-2);
        }
        .vc-eyebrow::before {
            content: "";
            flex: none;
            width: 0.65rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: conic-gradient(#22d3ee, #4ade80, #fde047, #fb923c, #22d3ee);
        }
        .vc-h1 {
            font-size: clamp(2.6rem, 13.4cqi, 5.9rem);
            font-weight: 700;
            letter-spacing: -0.052em;
            line-height: 0.93;
            text-wrap: balance;
        }
        .vc-lede { margin-top: 1.6rem; max-width: 36rem; font-size: clamp(1.08rem, 1.5vw, 1.25rem); color: var(--vc-ink-2); }
        .vc-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.1rem 1.1rem; margin-top: 2.1rem; }

        .vc-hang { min-width: 0; }
        /* Stacked, the lanyard has no bar to come out from under, so it fades in above the clip.
           It is cut to the length that shows, so nothing unseen lies over the buttons above. */
        @media (max-width: 999px) {
            .vc-hang { margin-top: 2.25rem; }
            .vc-rig .vc-strap { height: 7.5rem; -webkit-mask-image: linear-gradient(to top, #000 0, #000 4.5rem, transparent 7.25rem); mask-image: linear-gradient(to top, #000 0, #000 4.5rem, transparent 7.25rem); }
        }
        .vc-rig {
            position: relative;
            width: min(100%, 22.5rem);
            margin-inline: auto;
            padding-top: 6.5rem;
            transform-origin: 50% -16rem;
            animation: vc-sway 7s ease-in-out infinite alternate;
        }
        @keyframes vc-sway { from { rotate: -0.9deg; } to { rotate: 0.9deg; } }
        .vc-strap {
            position: absolute;
            left: 50%;
            bottom: calc(100% - 6.2rem);
            width: 1.9rem;
            height: 30rem;
            margin-left: -0.95rem;
            overflow: hidden;
            background: #0e0f12;
            color: #f4f3ee;
            transform-origin: 50% 100%;
            rotate: -15deg;
            box-shadow: inset 2px 0 0 rgba(255, 255, 255, 0.12), inset -2px 0 0 rgba(0, 0, 0, 0.35);
            /* A mask hides the strap from the eye and not from the finger: stacked, its unseen
               length lay across the buttons above it and took a fifth of their taps. */
            pointer-events: none;
        }
        .vc-strap + .vc-strap { rotate: 15deg; }
        .dark .vc-strap { background: #f4f3ee; color: #0e0f12; box-shadow: inset 2px 0 0 rgba(255, 255, 255, 0.6), inset -2px 0 0 rgba(0, 0, 0, 0.2); }
        .vc-strap i {
            position: absolute;
            inset: 0;
            writing-mode: vertical-rl;
            font: 700 0.58rem/1.9rem var(--vc-mono);
            letter-spacing: 0.24em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .vc-clip {
            position: absolute;
            left: 50%;
            top: 5.6rem;
            z-index: 3;
            width: 1.5rem;
            height: 2.5rem;
            margin-left: -0.75rem;
            border-radius: 0.3rem 0.3rem 0.75rem 0.75rem;
            background: linear-gradient(90deg, #7f858c, #eceef0 38%, #b3b8bd 58%, #666b72);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.45), inset 0 -0.55rem 0 -0.2rem rgba(0, 0, 0, 0.3);
        }
        .vc-badge {
            --vc-tx: 0.65;
            --vc-ty: -0.3;
            --bd-bg: linear-gradient(165deg, #ffffff, #eef0f1);
            --bd-ink: #0e0f12;
            --bd-ink-2: #4a4e56;
            --bd-line: rgba(14, 15, 18, 0.14);
            --bd-part: #dcf7fb;
            --bd-part-edge: #8adfee;
            position: relative;
            overflow: hidden;
            border-radius: 1.05rem;
            background: var(--bd-bg);
            color: var(--bd-ink);
            box-shadow:
                0 0 0 1px var(--bd-line),
                calc(var(--vc-tx) * -24px) calc(28px + var(--vc-ty) * 12px) 60px -22px rgba(0, 0, 0, 0.5);
            transform: perspective(1200px) rotateY(calc(var(--vc-tx) * 18deg)) rotateX(calc(var(--vc-ty) * -11deg));
            transform-origin: 50% 0;
            transition: transform 0.35s cubic-bezier(0.2, 0.7, 0.2, 1), box-shadow 0.35s ease;
        }
        .dark .vc-badge {
            --bd-bg: linear-gradient(165deg, #1b1e24, #121418);
            --bd-ink: #f4f3ee;
            --bd-ink-2: #b6b8b5;
            --bd-line: rgba(244, 243, 238, 0.2);
            --bd-part: #123840;
            --bd-part-edge: #1f7d8c;
        }
        /* The light that crosses the whole badge as it turns. */
        .vc-badge::after {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(calc(112deg + var(--vc-tx) * 18deg), transparent 28%, rgba(255, 255, 255, 0.5) calc(47% + var(--vc-tx) * 26%), transparent calc(60% + var(--vc-tx) * 26%));
            mix-blend-mode: soft-light;
        }
        .vc-badge-slot {
            display: block;
            width: 3.3rem;
            height: 0.72rem;
            margin: 0.75rem auto 0.7rem;
            border-radius: 1rem;
            background: var(--vc-bg);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.45), 0 0 0 1px var(--bd-line);
        }
        .vc-badge-head { padding: 0.95rem 1.05rem 1rem; }
        .vc-badge-top,
        .vc-badge-foot small,
        .vc-badge-no {
            font-family: var(--vc-mono);
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .vc-badge-top { display: flex; justify-content: space-between; gap: 0.75rem; }
        .vc-badge-title { margin-top: 0.7rem; font-size: 1.85rem; font-weight: 700; letter-spacing: -0.045em; line-height: 0.98; }
        .vc-badge-sub { margin-top: 0.55rem; font-size: 0.8rem; font-weight: 700; line-height: 1.3; }

        .vc-run { position: relative; display: grid; gap: 0.2rem; padding: 0.85rem 0.9rem 0.6rem; }
        .vc-run-row { display: grid; grid-template-columns: 2.5rem minmax(0, 1fr); gap: 0.5rem; }
        .vc-run-t { padding-top: 0.34rem; font: 600 0.66rem/1.2 var(--vc-mono); color: var(--bd-ink-2); }
        .vc-run-b {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            overflow: hidden;
            padding: 0.3rem 0.5rem 0.24rem;
            border: 1px solid var(--bd-part-edge);
            border-radius: 0.3rem;
            background: var(--bd-part);
        }
        .vc-run-b.is-gap {
            align-items: center;
            border: 1px dashed var(--bd-line);
            background: repeating-linear-gradient(135deg, transparent 0 5px, var(--bd-line) 5px 6px);
            color: var(--bd-ink-2);
        }
        .vc-run-n { font-size: 0.78rem; font-weight: 700; line-height: 1.2; }
        .vc-run-d { flex: none; padding-top: 0.14rem; font: 600 0.6rem/1.2 var(--vc-mono); color: var(--bd-ink-2); }
        .vc-run-end { font: 600 0.62rem/1.2 var(--vc-mono); color: var(--bd-ink-2); padding-top: 0.34rem; }
        .vc-run-now {
            position: absolute;
            left: 0.55rem;
            right: 0.55rem;
            top: 41%;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, #22d3ee, #4ade80, #fde047, #fb923c);
            box-shadow: 0 0 10px rgba(34, 211, 238, 0.7);
            animation: vc-run-now 16s linear infinite;
        }
        @keyframes vc-run-now { from { top: 4%; } to { top: 93%; } }
        .vc-badge-foot {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: end;
            gap: 0.8rem;
            padding: 0.85rem 1.05rem 1rem;
            border-top: 1px dashed var(--bd-line);
        }
        .vc-badge-foot b { display: block; font-size: 1.05rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; line-height: 1.1; }
        .vc-badge-foot small { display: flex; align-items: center; gap: 0.4rem; margin-top: 0.4rem; color: var(--bd-ink-2); }
        .vc-badge-foot small::before { content: ""; width: 1.4rem; height: 0.42rem; border-radius: 1rem; background: #22d3ee; }
        .vc-badge-no { color: var(--bd-ink-2); }
        .vc-hang-cap { max-width: 22rem; margin: 3.25rem auto 0; font-size: 0.88rem; line-height: 1.5; color: var(--vc-ink-3); text-align: center; }

        /* The ticker: every kind of conference, set like a sponsor strip. */
        .vc-ticker { position: relative; border-top: 1px solid var(--vc-line); padding-block: 0.95rem; }
        .vc-ticker .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .vc-tick {
            display: inline-flex;
            align-items: center;
            font-family: var(--vc-mono);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            white-space: nowrap;
            color: var(--vc-ink-2);
        }
        .vc-tick::after { content: "/"; margin-inline: 1.5rem; color: var(--vc-c4); }

        /* ---------------------------------------------------------------
           01 The unit
           --------------------------------------------------------------- */
        .vc-duo { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
        @media (min-width: 900px) { .vc-duo { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 3.5rem; } }
        .vc-duo-arrow { display: none; }
        @media (min-width: 900px) {
            .vc-duo-arrow {
                position: absolute;
                left: 50%;
                top: 50%;
                z-index: 2;
                display: grid;
                place-items: center;
                width: 2.6rem;
                aspect-ratio: 1;
                translate: -50% -50%;
                border-radius: 50%;
                background: var(--vc-ink);
                color: var(--vc-bg);
            }
            .vc-duo-arrow svg { width: 1.15rem; height: 1.15rem; }
        }
        .vc-fields { margin-top: 1.1rem; border-top: 1px solid var(--vc-line); }
        .vc-fields > div {
            display: grid;
            grid-template-columns: 8rem minmax(0, 1fr);
            gap: 0.75rem;
            align-items: baseline;
            padding-block: 0.62rem;
            border-bottom: 1px solid var(--vc-line);
        }
        .vc-fields dt { font-family: var(--vc-mono); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--vc-ink-3); }
        .vc-fields dd { font-weight: 700; text-align: end; }
        .vc-read { margin-top: 1.1rem; border-top: 1px solid var(--vc-line); }
        .vc-read li {
            display: grid;
            grid-template-columns: 3rem minmax(0, 1fr) auto;
            gap: 0.75rem;
            align-items: baseline;
            padding-block: 0.62rem;
            border-bottom: 1px solid var(--vc-line);
        }
        .vc-read time,
        .vc-read small { font-family: var(--vc-mono); font-size: 0.78rem; font-weight: 600; color: var(--vc-ink-3); }
        .vc-read span { font-weight: 700; }
        .vc-read .is-gap span { font-weight: 400; color: var(--vc-ink-3); }
        .vc-stats { display: grid; grid-template-columns: minmax(0, 1fr); margin-top: clamp(2.5rem, 5vw, 4rem); border-block: 1px solid var(--vc-line-2); }
        @media (min-width: 760px) { .vc-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .vc-stat { padding: 1.75rem 0 1.9rem; }
        .vc-stat + .vc-stat { border-top: 1px solid var(--vc-line); }
        @media (min-width: 760px) {
            .vc-stat { padding-inline: 1.75rem; }
            .vc-stat:first-child { padding-inline-start: 0; }
            .vc-stat + .vc-stat { border-top: 0; border-inline-start: 1px solid var(--vc-line); }
        }
        .vc-stat h3 { font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--vc-ink-2); }
        .vc-stat-n { display: inline-block; margin-block: 0.4rem 0.6rem; padding-inline-end: 0.08em; font-size: clamp(4.5rem, 9vw, 7.5rem); font-weight: 700; letter-spacing: -0.06em; line-height: 0.85; }
        .vc-stat p:last-child { max-width: 22rem; color: var(--vc-ink-2); font-size: 1rem; }

        /* Four clocks at one minute. Day faces are lit, night faces are not. */
        .vc-clocks {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.75rem 1rem;
            margin-top: 2.75rem;
            align-items: start;
        }
        @media (min-width: 760px) { .vc-clocks { grid-template-columns: minmax(0, 1.3fr) repeat(4, minmax(0, 1fr)); gap: 1.5rem; align-items: center; } }
        .vc-clocks-k { grid-column: 1 / -1; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; line-height: 1.5; color: var(--vc-ink-2); }
        @media (min-width: 760px) { .vc-clocks-k { grid-column: auto; } }
        /* Five columns need about 980px: under that a city had 35px to be named in, and
           "Bengaluru" ran on under the next clock. Until then the caption keeps its own row. */
        @media (min-width: 760px) and (max-width: 979px) {
            #vc .vc-clocks { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            #vc .vc-clocks-k { grid-column: 1 / -1; }
        }
        /* And on the narrowest phones the face gives the name the few pixels it was short of. */
        @media (max-width: 374px) {
            #vc .vc-clock { gap: 0.55rem; }
            #vc .vc-clock-face { width: 4.1rem; }
        }
        .vc-clock { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 0.85rem; }
        .vc-clock-face {
            --face: #ffffff;
            --hand: #0e0f12;
            position: relative;
            width: 4.4rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--face);
            box-shadow: 0 0 0 1.5px var(--vc-line-2), 0 10px 22px -14px rgba(0, 0, 0, 0.5);
        }
        .vc-clock.is-night .vc-clock-face { --face: #15171c; --hand: #f4f3ee; }
        .vc-clock-face::before {
            content: "";
            position: absolute;
            inset: 7%;
            border-radius: 50%;
            background: repeating-conic-gradient(from -1.5deg, var(--hand) 0 3deg, transparent 3deg 30deg);
            -webkit-mask: radial-gradient(circle, transparent 0 60%, #000 61%);
            mask: radial-gradient(circle, transparent 0 60%, #000 61%);
            opacity: 0.6;
        }
        .vc-clock-face::after { content: ""; position: absolute; inset: 46%; border-radius: 50%; background: var(--hand); }
        .vc-clock-face i { position: absolute; left: 50%; bottom: 50%; border-radius: 3px; background: var(--hand); transform-origin: 50% 100%; }
        .vc-clock-face i:nth-child(1) { width: 3px; height: 25%; margin-left: -1.5px; rotate: var(--h); }
        .vc-clock-face i:nth-child(2) { width: 2px; height: 37%; margin-left: -1px; rotate: var(--m); }
        .vc-clock-face i:nth-child(3) { width: 1px; height: 40%; margin-left: -0.5px; background: #fb923c; animation: vc-second 60s steps(60) infinite; }
        @keyframes vc-second { to { rotate: 360deg; } }
        .vc-clock b { display: block; font-size: 0.95rem; font-weight: 700; line-height: 1.2; }
        .vc-clock small { display: block; font-family: var(--vc-mono); font-size: 0.78rem; font-weight: 600; color: var(--vc-ink-3); }

        /* ---------------------------------------------------------------
           02 Setting the running order: a part, opened
           --------------------------------------------------------------- */
        .vc-set { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 3.5rem; align-items: start; }
        @media (min-width: 980px) { .vc-set { grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr); } }
        .vc-editor { position: relative; max-width: 31rem; }
        @media (min-width: 980px) { .vc-editor { position: sticky; top: calc(var(--vc-top) + 1.5rem); } }
        .vc-editor-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 1.1rem; margin-bottom: 0.8rem; font-family: var(--vc-mono); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--vc-ink-2); }
        .vc-editor-bar span:first-child { margin-inline-end: auto; color: var(--vc-ink); }
        .vc-sw { display: inline-flex; align-items: center; gap: 0.5rem; }
        .vc-sw::before { content: ""; width: 1.9rem; height: 1.05rem; border-radius: 1rem; background: radial-gradient(circle at 72% 50%, #ffffff 0 0.36rem, transparent 0.38rem) var(--vc-c2); }
        .vc-pcard { border: 1px solid var(--vc-line); border-radius: 0.5rem; background: var(--vc-card); }
        .vc-pcard + .vc-pcard { margin-top: 0.55rem; }
        .vc-pcard.is-dim { display: flex; align-items: baseline; gap: 0.9rem; padding: 0.7rem 1rem; color: var(--vc-ink-3); }
        .vc-pcard.is-dim time { font-family: var(--vc-mono); font-size: 0.76rem; font-weight: 600; }
        .vc-pcard.is-dim b { font-weight: 700; color: var(--vc-ink-2); }
        .vc-pcard.is-open { padding: 1.1rem 1.1rem 1.2rem; border-color: var(--vc-line-2); box-shadow: 0 26px 50px -34px rgba(0, 0, 0, 0.55); }
        .vc-pcard-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 0.9rem; font-family: var(--vc-mono); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--vc-ink-2); }
        .vc-ud { display: inline-flex; align-items: center; gap: 0.35rem; }
        .vc-ud i { display: grid; place-items: center; width: 1.9rem; aspect-ratio: 1; border: 1px solid var(--vc-line-2); border-radius: 0.35rem; color: var(--vc-ink); }
        .vc-ud i::before { content: ""; width: 0.45rem; aspect-ratio: 1; border: solid currentColor; border-width: 2px 0 0 2px; rotate: 45deg; translate: 0 0.12rem; }
        .vc-ud i + i::before { rotate: 225deg; translate: 0 -0.12rem; }
        .vc-fld { position: relative; display: grid; gap: 0.3rem; margin-top: 0.7rem; }
        .vc-fld > span:first-child { font-family: var(--vc-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--vc-ink-3); }
        .vc-inp { display: block; padding: 0.6rem 0.75rem; border: 1px solid var(--vc-line-2); border-radius: 0.35rem; font-weight: 700; line-height: 1.3; }
        .vc-inp-mono { font-family: var(--vc-mono); font-size: 0.95rem; }
        .vc-inp-area { min-height: 4.2rem; font-weight: 400; font-size: 0.95rem; color: var(--vc-ink-2); }
        .vc-fld-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
        .vc-pin {
            display: inline-grid;
            place-items: center;
            flex: none;
            width: 1.7rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--pin, #fde047);
            color: #0e0f12;
            font-family: var(--vc-mono);
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1;
        }
        .vc-editor .vc-pin { position: absolute; right: -0.85rem; top: 1.25rem; box-shadow: 0 0 0 3px var(--vc-card); }
        .vc-editor .vc-pcard-head .vc-pin { position: static; box-shadow: none; }
        .vc-three { display: grid; gap: 0; border-top: 1px solid var(--vc-line-2); }
        .vc-three > article { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.4rem 1.1rem; padding-block: 1.5rem 1.6rem; border-bottom: 1px solid var(--vc-line); }
        .vc-three p { grid-column: 2; color: var(--vc-ink-2); }
        .vc-three .vc-pin { margin-top: 0.1rem; }
        .vc-scan-card { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem 3rem; align-items: center; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 760px) { .vc-scan-card { grid-template-columns: auto minmax(0, 1fr); } }
        .vc-scan-card p { margin-top: 0.75rem; color: var(--vc-ink-2); }
        .vc-scan { display: flex; align-items: center; gap: 1rem; }
        .vc-scan-sheet { position: relative; overflow: hidden; display: grid; gap: 0.42rem; width: 6.2rem; padding: 0.8rem 0.7rem; border: 1px solid var(--vc-line-2); border-radius: 0.3rem; background: var(--vc-bg); rotate: -4deg; }
        .vc-scan-sheet i { height: 0.3rem; border-radius: 1rem; background: var(--vc-line-2); }
        .vc-scan-sheet i:nth-child(odd) { width: 78%; }
        .vc-scan-sheet i:first-child { width: 55%; height: 0.45rem; background: var(--vc-ink-2); }
        .vc-scan-sheet b { position: absolute; left: 0; right: 0; top: 0; height: 2px; background: linear-gradient(90deg, #22d3ee, #4ade80, #fde047, #fb923c); box-shadow: 0 0 12px 2px rgba(74, 222, 128, 0.6); animation: vc-scan 3.2s ease-in-out infinite alternate; }
        @keyframes vc-scan { from { top: 6%; } to { top: 92%; } }
        .vc-scan > svg { flex: none; width: 1.3rem; height: 1.3rem; color: var(--vc-ink-3); }
        .vc-scan-out { display: grid; gap: 0.35rem; width: 8.6rem; }
        .vc-scan-out li { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 0.5rem; padding: 0.38rem 0.5rem; border: 1px solid var(--vc-line); border-radius: 0.3rem; background: var(--vc-bg); font: 700 0.64rem/1 var(--vc-mono); color: var(--vc-ink-2); }
        .vc-scan-out li i { height: 0.3rem; border-radius: 1rem; background: var(--vc-line-2); }

        /* ---------------------------------------------------------------
           03 The programme: the strip, then the agenda itself
           --------------------------------------------------------------- */
        .vc-day-1 { --s: #22d3ee; --sd: var(--vc-c1); }
        .vc-day-2 { --s: #fb923c; --sd: var(--vc-c4); }
        .vc-day-3 { --s: #4ade80; --sd: var(--vc-c2); }

        .vc-prog-top { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.4rem 1.5rem; margin-bottom: 1rem; }
        .vc-prog-top p { font-family: var(--vc-mono); font-size: 0.74rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; }
        .vc-prog-top p + p { color: var(--vc-ink-2); letter-spacing: 0.08em; }
        .vc-strip { width: 100%; border-collapse: collapse; text-align: start; }
        .vc-strip th,
        .vc-strip td { padding: 0; text-align: start; }
        .vc-strip thead th { padding-bottom: 0.6rem; font-family: var(--vc-mono); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--vc-ink-3); }
        .vc-strip .vc-strip-n { text-align: end; }
        .vc-strip-day th,
        .vc-strip-day td { padding-top: 0.85rem; border-top: 1px solid var(--vc-line); vertical-align: baseline; }
        .vc-strip-day th { font-size: 1.15rem; font-weight: 700; letter-spacing: -0.02em; padding-inline-end: 1rem; white-space: nowrap; }
        .vc-strip-day td { font-family: var(--vc-mono); font-size: 0.8rem; font-weight: 600; color: var(--vc-ink-2); padding-inline-end: 1rem; white-space: nowrap; }
        @media (max-width: 480px) {
            .vc-strip-day th { font-size: 1rem; padding-inline-end: 0.5rem; }
            .vc-strip-day td { font-size: 0.68rem; padding-inline-end: 0.5rem; }
        }
        .vc-strip-day td.vc-strip-n { padding-inline-end: 0; font-size: 1.05rem; font-weight: 700; color: var(--vc-ink); }
        .vc-strip-ticks td { padding-block: 0.6rem 0.9rem; }
        .vc-ruler { display: grid; grid-template-columns: repeat(9, minmax(0, 1fr)); padding-bottom: 0.3rem; font-family: var(--vc-mono); font-size: 0.66rem; font-weight: 600; color: var(--vc-ink-3); }
        .vc-ruler span { border-inline-start: 1px solid var(--vc-line-2); padding-inline-start: 0.3rem; line-height: 1.5; }
        .vc-track { position: relative; height: 1.15rem; border-radius: 0.2rem; background: repeating-linear-gradient(90deg, var(--vc-line) 0 1px, transparent 1px 11.111%) var(--vc-bg-2); }
        .vc-tickmark { position: absolute; top: 0; bottom: 0; border-radius: 0.14rem; background: var(--s); box-shadow: inset 0 0 0 1px rgba(14, 15, 18, 0.35); }
        .vc-tickmark.is-gap { background: transparent; box-shadow: inset 0 0 0 1.5px var(--vc-line-2); }

        .vc-ag { container: vc-ag / inline-size; position: relative; margin-top: 2.25rem; padding-top: 1.75rem; border-top: 1px dashed var(--vc-line-2); }
        .vc-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; }
        .vc-filter-k { margin-inline-end: 0.6rem; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--vc-ink-2); }
        #vc .vc-f { position: absolute; width: 1px; height: 1px; margin: -1px; padding: 0; overflow: hidden; clip-path: inset(50%); border: 0; opacity: 0; }
        .vc-filter label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.95rem 0.45rem;
            border: 1.5px solid var(--vc-line-2);
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1.2;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .vc-filter label i { width: 0.6rem; aspect-ratio: 1; border-radius: 50%; background: var(--s); box-shadow: 0 0 0 1px rgba(14, 15, 18, 0.35); }
        .vc-filter label:hover { border-color: var(--vc-ink); }
        .vc-f:checked + label { background: var(--vc-ink); border-color: var(--vc-ink); color: var(--vc-bg); }

        .vc-grid {
            --vc-5: 0.78rem;
            --vc-min: calc(var(--vc-5) / 5);
            position: relative;
            display: grid;
            grid-template-columns: [time] 3.4rem [d1] minmax(0, 1fr) [d2] minmax(0, 1fr) [d3] minmax(0, 1fr) [end];
            column-gap: 0.6rem;
            padding-bottom: 1.75rem;
            transition: grid-template-columns 0.55s cubic-bezier(0.3, 0.7, 0.2, 1);
        }
        .vc-gutter { grid-column: time; grid-row: t0900 / -1; position: relative; }
        .vc-gutter span { position: absolute; left: 0; translate: 0 -50%; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 600; color: var(--vc-ink-3); }
        .vc-lines { grid-column: d1 / -1; grid-row: t0900 / -1; background: repeating-linear-gradient(to bottom, var(--vc-line-2) 0 1px, transparent 1px calc(var(--vc-min) * 60)); border-bottom: 1px solid var(--vc-line-2); opacity: 0.55; }
        .vc-now { grid-column: time / -1; grid-row: t0900 / -1; position: relative; z-index: 3; pointer-events: none; }
        .vc-now-line { position: absolute; left: 0; right: 0; top: calc(var(--vc-now) * var(--vc-min)); height: 2px; background: linear-gradient(90deg, #22d3ee, #4ade80, #fde047, #fb923c); box-shadow: 0 0 12px rgba(253, 224, 71, 0.55); }
        .vc-now-tag { position: absolute; left: 0; top: 50%; translate: 0 -50%; padding: 0.22rem 0.4rem 0.18rem; border-radius: 0.25rem; background: #fde047; color: #0e0f12; font: 700 0.64rem/1 var(--vc-mono); letter-spacing: 0.08em; white-space: nowrap; }
        @supports (animation-timeline: view()) and (top: calc(round(down, 5px, 2px))) {
            html.es-anim #vc .vc-now { animation: vc-now linear both; animation-timeline: view(); animation-range: cover 22% cover 78%; }
            html.es-anim #vc .vc-now-tag b { display: none; }
            html.es-anim #vc .vc-now-tag::after {
                /* The minute is rounded before it is split: Safari holds a fraction here while
                   the page scrolls, and 119.7 read as 10:60. */
                counter-reset: vc-h calc(9 + round(down, round(nearest, var(--vc-now), 1) / 60, 1)) vc-m mod(round(nearest, var(--vc-now), 1), 60);
                content: counter(vc-h, decimal-leading-zero) ":" counter(vc-m, decimal-leading-zero);
            }
        }
        @keyframes vc-now { from { --vc-now: 6; } to { --vc-now: 414; } }

        .vc-day { position: relative; grid-row: 1 / -1; display: grid; grid-template-rows: subgrid; min-width: 0; }
        .vc-day-1 { grid-column: d1; }
        .vc-day-2 { grid-column: d2; }
        .vc-day-3 { grid-column: d3; }
        .vc-day-head {
            grid-row: head;
            position: sticky;
            top: var(--vc-top);
            z-index: 4;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.1rem 0.75rem;
            align-items: baseline;
            margin-bottom: 0.75rem;
            padding: 0.85rem 0.9rem 0.8rem;
            border: 1px solid var(--vc-line-2);
            border-radius: 0.45rem;
            background: var(--vc-card);
            overflow: hidden;
        }
        .vc-day-bar { grid-column: 1 / -1; width: 2.4rem; height: 0.36rem; margin-bottom: 0.45rem; border-radius: 1rem; background: var(--s); box-shadow: 0 0 0 1px rgba(14, 15, 18, 0.3); }
        .vc-day-head h3 { font-size: 1.5rem; font-weight: 700; letter-spacing: -0.04em; line-height: 1; white-space: nowrap; }
        .vc-day-date { font-family: var(--vc-mono); font-size: 0.76rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; white-space: nowrap; }
        .vc-day-meta { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 0.1rem 0.9rem; margin-top: 0.35rem; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 600; color: var(--vc-ink-2); }
        .vc-day-meta span:first-child { color: var(--sd); font-weight: 700; }
        .vc-day-short { display: none; }
        .vc-day-pick { display: none; }
        .vc-day-parts { position: relative; grid-row: t0900 / -1; display: grid; grid-template-rows: subgrid; }
        .vc-day-end { position: absolute; left: 0; right: 0; padding-top: 0.4rem; font-family: var(--vc-mono); font-size: 0.7rem; font-weight: 600; color: var(--vc-ink-3); }
        .vc-day-end span { margin-inline-end: 0.5rem; font-weight: 700; color: var(--sd); }
        .vc-part {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-content: start;
            gap: 0.1rem 0.6rem;
            min-height: 0;
            overflow: hidden;
            margin-block: 1.5px;
            padding: 0.42rem 0.65rem 0.4rem;
            border: 1px solid var(--vc-line-2);
            border-radius: 0.4rem;
            background: var(--vc-card);
        }
        @supports (background: color-mix(in srgb, red 10%, blue)) {
            .vc-part { background: color-mix(in srgb, var(--s) 17%, var(--vc-card)); border-color: color-mix(in srgb, var(--s) 62%, var(--vc-line-2)); }
        }
        .vc-part-t,
        .vc-part-d { font-family: var(--vc-mono); font-size: 0.7rem; font-weight: 600; line-height: 1.35; color: var(--vc-ink-2); white-space: nowrap; }
        .vc-part-n { grid-column: 1 / -1; font-size: 0.95rem; font-weight: 700; letter-spacing: -0.01em; line-height: 1.22; }
        .vc-part-a { grid-column: 1 / -1; margin-top: 0.25rem; font-size: 0.84rem; line-height: 1.4; color: var(--vc-ink-2); }
        .vc-part.is-key { background: var(--s); border-color: transparent; color: #0e0f12; }
        .vc-part.is-key .vc-part-t,
        .vc-part.is-key .vc-part-d,
        .vc-part.is-key .vc-part-a { color: #0e0f12; }
        .vc-part.is-break {
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-content: center;
            align-items: center;
            border: 1px dashed var(--vc-line-2);
            background: repeating-linear-gradient(135deg, transparent 0 6px, var(--vc-line) 6px 7px);
        }
        .vc-part.is-break .vc-part-n { grid-column: auto; font-size: 0.8rem; font-weight: 600; color: var(--vc-ink-2); }

        /* One day chosen: it takes the room, the other two fold to a sliver you can press. */
        #vc .vc-ag:has(#vc-f1:checked) .vc-grid { grid-template-columns: [time] 3.4rem [d1] minmax(0, 1fr) [d2] minmax(0, 0.06fr) [d3] minmax(0, 0.06fr) [end]; }
        #vc .vc-ag:has(#vc-f2:checked) .vc-grid { grid-template-columns: [time] 3.4rem [d1] minmax(0, 0.06fr) [d2] minmax(0, 1fr) [d3] minmax(0, 0.06fr) [end]; }
        #vc .vc-ag:has(#vc-f3:checked) .vc-grid { grid-template-columns: [time] 3.4rem [d1] minmax(0, 0.06fr) [d2] minmax(0, 0.06fr) [d3] minmax(0, 1fr) [end]; }
        #vc .vc-ag:has(#vc-f1:checked) :is(.vc-day-2, .vc-day-3) :is(.vc-part > *, .vc-day-end, .vc-day-head > :not(.vc-day-short)),
        #vc .vc-ag:has(#vc-f2:checked) :is(.vc-day-1, .vc-day-3) :is(.vc-part > *, .vc-day-end, .vc-day-head > :not(.vc-day-short)),
        #vc .vc-ag:has(#vc-f3:checked) :is(.vc-day-1, .vc-day-2) :is(.vc-part > *, .vc-day-end, .vc-day-head > :not(.vc-day-short)) { display: none; }
        #vc .vc-ag:has(#vc-f1:checked) :is(.vc-day-2, .vc-day-3) :is(.vc-day-short, .vc-day-pick),
        #vc .vc-ag:has(#vc-f2:checked) :is(.vc-day-1, .vc-day-3) :is(.vc-day-short, .vc-day-pick),
        #vc .vc-ag:has(#vc-f3:checked) :is(.vc-day-1, .vc-day-2) :is(.vc-day-short, .vc-day-pick) { display: block; }
        #vc .vc-ag:has(#vc-f1:checked) .vc-day-1 .vc-part,
        #vc .vc-ag:has(#vc-f2:checked) .vc-day-2 .vc-part,
        #vc .vc-ag:has(#vc-f3:checked) .vc-day-3 .vc-part { padding-inline: 1rem; }
        .vc-day-short { font-family: var(--vc-mono); font-size: 0.8rem; font-weight: 700; text-align: center; grid-column: 1 / -1; }
        .vc-day-pick { position: absolute; inset: 0; z-index: 5; cursor: pointer; border-radius: 0.45rem; }
        .vc-day-pick:hover { box-shadow: 0 0 0 2px var(--s); }
        .vc-ag-cap { margin-top: 1.5rem; }

        /* A narrow container: the grid becomes a list, each day under its own bar. */
        @container vc-ag (max-width: 52rem) {
            .vc-grid { display: block; }
            .vc-gutter,
            .vc-lines,
            .vc-now { display: none; }
            .vc-day { display: block; }
            .vc-day + .vc-day { margin-top: 1.5rem; }
            .vc-day-head { margin-bottom: 0.5rem; }
            .vc-day-parts { display: block; }
            .vc-day-end { position: static; padding: 0.2rem 0.7rem 0; }
            .vc-part { grid-template-columns: 3.1rem minmax(0, 1fr) auto; align-items: baseline; margin-block: 0.35rem; padding: 0.6rem 0.7rem; }
            .vc-part-n { grid-column: auto; }
            .vc-part-d { order: 2; }
            .vc-part-a { order: 3; grid-column: 2 / -1; margin-top: 0; }
            .vc-part.is-break { grid-template-columns: 3.1rem minmax(0, 1fr) auto; padding-block: 0.4rem; }
            #vc .vc-ag:has(#vc-f1:checked) :is(.vc-day-2, .vc-day-3),
            #vc .vc-ag:has(#vc-f2:checked) :is(.vc-day-1, .vc-day-3),
            #vc .vc-ag:has(#vc-f3:checked) :is(.vc-day-1, .vc-day-2) { display: none; }
        }
        .vc-strand-note { max-width: 46rem; margin: clamp(2rem, 4vw, 3rem) auto 0; color: var(--vc-ink-2); }

        /* ---------------------------------------------------------------
           04 In session: seven parts, one link (a band that stays dark)
           --------------------------------------------------------------- */
        .vc-band-glow {
            position: absolute;
            left: 50%;
            top: -18rem;
            width: min(70rem, 130vw);
            height: 30rem;
            translate: -50% 0;
            border-radius: 50%;
            pointer-events: none;
            background: conic-gradient(from calc(180deg + var(--vc-foil)) at 50% 50%, #22d3ee, #4ade80, #fde047, #fb923c, #22d3ee);
            filter: blur(100px);
            opacity: 0.2;
            animation: vc-foil 9s ease-in-out infinite alternate;
        }
        .vc-onelink { position: relative; max-width: 54rem; margin-inline: auto; }
        .vc-onelink-parts { display: flex; gap: 3px; }
        .vc-onelink-parts span { position: relative; flex: 1 1 0; min-width: 0; height: 2.6rem; border-radius: 0.25rem; background: #22d3ee; }
        .vc-onelink-parts span.is-gap { background: repeating-linear-gradient(135deg, transparent 0 5px, rgba(244, 243, 238, 0.28) 5px 6px); box-shadow: inset 0 0 0 1px rgba(244, 243, 238, 0.3); }
        .vc-onelink-parts i { position: absolute; left: 0.35rem; top: 0.3rem; font: 700 0.62rem/1 var(--vc-mono); color: #0e0f12; }
        .vc-onelink-parts .is-gap i { display: none; }
        .vc-onelink-brace { position: relative; height: 1.5rem; margin: 0.5rem 0.2rem 1.6rem; border: 1.5px solid var(--vc-line-2); border-top: 0; border-radius: 0 0 0.7rem 0.7rem; }
        .vc-onelink-brace::after { content: ""; position: absolute; left: 50%; top: 100%; width: 1.5px; height: 1.6rem; background: var(--vc-line-2); }
        .vc-onelink-url {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.9rem;
            max-width: 36rem;
            margin-inline: auto;
            padding: 0.7rem 0.7rem 0.7rem 1.1rem;
            border: 1.5px solid var(--vc-line-2);
            border-radius: 999px;
            background: var(--vc-card);
        }
        .vc-onelink-url small { font-family: var(--vc-mono); font-size: 0.66rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--vc-ink-3); }
        .vc-onelink-url span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: var(--vc-mono); font-size: 0.9rem; font-weight: 600; }
        .vc-onelink-url b { padding: 0.5rem 1rem; border-radius: 999px; background: #f4f3ee; color: #0e0f12; font-size: 0.9rem; font-weight: 700; line-height: 1.1; }
        /* The address is the point of this field, and on one line of a phone it was cut to
           "https://stream.ex...". There the label goes above it and it takes two lines. */
        @media (max-width: 640px) {
            .vc-onelink-url { grid-template-columns: minmax(0, 1fr) auto; gap: 0.2rem 0.75rem; padding: 0.75rem 0.75rem 0.8rem 1.1rem; border-radius: 1.3rem; }
            .vc-onelink-url small { grid-column: 1 / -1; }
            .vc-onelink-url span { overflow: visible; white-space: normal; overflow-wrap: anywhere; font-size: 0.8rem; line-height: 1.35; }
        }
        .vc-cells { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1px; margin-top: clamp(2.5rem, 5vw, 4rem); border: 1px solid var(--vc-line); border-radius: 0.5rem; background: var(--vc-line); overflow: hidden; }
        @media (min-width: 960px) { .vc-cells { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .vc-cells > article { padding: 1.6rem 1.5rem 1.75rem; background: var(--vc-card); }
        .vc-cells p { margin-top: 0.8rem; color: var(--vc-ink-2); font-size: 1rem; }
        .vc-cell-art { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; min-height: 2.4rem; margin-bottom: 1.2rem; font-family: var(--vc-mono); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; color: var(--vc-ink-2); }
        .vc-cell-art span { padding: 0.3rem 0.55rem 0.26rem; border: 1px solid var(--vc-line-2); border-radius: 999px; line-height: 1.2; }
        .vc-cell-art .is-full { background: #fb923c; border-color: transparent; color: #0e0f12; }
        .vc-cell-art .is-ics { border-radius: 0.3rem; background: #f4f3ee; border-color: transparent; color: #0e0f12; }
        .vc-band-foot { margin-top: 2.5rem; text-align: center; color: var(--vc-ink-2); }
        .vc-band-foot a { display: inline-flex; align-items: center; gap: 0.4rem; margin-inline-start: 0.4rem; color: var(--vc-ink); font-weight: 700; border-bottom: 2px solid #fb923c; transition: gap 0.2s ease; }
        .vc-band-foot a:hover { gap: 0.7rem; }
        .vc-band-foot svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           05 The takings: the run-up, and the price board
           --------------------------------------------------------------- */
        .vc-take { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .vc-take { grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); }
            .vc-board { position: sticky; top: calc(var(--vc-top) + 1.5rem); }
        }
        .vc-take-lede { margin-top: 1.4rem; max-width: 40rem; font-size: 1.15rem; color: var(--vc-ink-2); }
        .vc-rail { position: relative; margin-top: 2.25rem; padding-inline-start: 1.9rem; }
        .vc-rail::before { content: ""; position: absolute; left: 0.3rem; top: 0.4rem; bottom: 0.4rem; width: 2px; background: linear-gradient(to bottom, var(--vc-c1), var(--vc-c2) 36%, var(--vc-c3) 68%, var(--vc-c4)); border-radius: 2px; }
        .vc-phase { position: relative; margin-top: 1.9rem; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--vc-ink); }
        .vc-phase:first-child { margin-top: 0; }
        .vc-phase::before { content: ""; position: absolute; left: -1.9rem; top: 0.05rem; width: 0.76rem; aspect-ratio: 1; border-radius: 50%; background: var(--vc-bg); box-shadow: inset 0 0 0 2.5px var(--vc-ink); }
        .vc-phase span { margin-inline-start: 0.6rem; font-weight: 600; color: var(--vc-ink-3); }
        .vc-rail ul { margin-top: 0.7rem; }
        .vc-rail li { padding-block: 0.75rem; border-bottom: 1px solid var(--vc-line); color: var(--vc-ink-2); }
        .vc-rail li:first-child { border-top: 1px solid var(--vc-line); }
        .vc-take-foot { margin-top: 1.75rem; color: var(--vc-ink-2); }
        .vc-tiers { margin-top: 1.25rem; border-top: 1px solid var(--vc-line-2); }
        .vc-tiers > div { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.1rem 1rem; align-items: baseline; padding-block: 0.8rem; border-bottom: 1px solid var(--vc-line); }
        .vc-tiers b { font-weight: 700; }
        .vc-tiers small { grid-column: 1; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 600; color: var(--vc-ink-3); }
        .vc-tiers span { grid-column: 2; grid-row: 1 / span 2; align-self: center; font-family: var(--vc-mono); font-size: 1.25rem; font-weight: 700; }
        .vc-fee { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-top: 1.1rem; }
        .vc-fee span { font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--vc-ink-2); }
        .vc-fee b { font-size: clamp(3rem, 6vw, 4.5rem); font-weight: 700; letter-spacing: -0.05em; line-height: 0.9; }

        /* ---------------------------------------------------------------
           06 Afterwards: six cells on hairlines
           --------------------------------------------------------------- */
        .vc-after { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1px; border: 1px solid var(--vc-line); border-radius: 0.5rem; background: var(--vc-line); overflow: hidden; counter-reset: vc-after; }
        @media (min-width: 760px) { .vc-after { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .vc-after { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .vc-after > article { position: relative; counter-increment: vc-after; padding: 1.5rem 1.5rem 1.8rem; background: var(--vc-card); }
        .vc-after > article::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 3px; background: linear-gradient(90deg, #22d3ee, #4ade80, #fde047, #fb923c); scale: 0 1; transform-origin: 0 50%; transition: scale 0.45s cubic-bezier(0.2, 0.7, 0.2, 1); }
        .vc-after > article:hover::before { scale: 1 1; }
        .vc-after-no { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.6rem; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; color: var(--vc-ink-3); }
        .vc-after-no::before { content: "A." counter(vc-after, decimal-leading-zero); }
        .vc-after p { margin-top: 0.85rem; color: var(--vc-ink-2); font-size: 1rem; }
        .vc-after p + p { font-size: 0.9rem; color: var(--vc-ink-3); }

        /* ---------------------------------------------------------------
           07 Perfect for: six badges on one rail
           --------------------------------------------------------------- */
        .vc-wall { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 1.5rem; }
        @media (min-width: 700px) { .vc-wall { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1060px) { .vc-wall { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3rem 2rem; } }
        .vc-mini-wrap { position: relative; min-width: 0; padding-top: 1.75rem; }
        .vc-mini-wrap::before { content: ""; position: absolute; left: 50%; top: 0; width: 4.5rem; height: 4px; margin-left: -2.25rem; border-radius: 4px; background: var(--vc-ink); }
        .vc-mini {
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            border-radius: 0.9rem;
            background: var(--vc-card);
            box-shadow: 0 0 0 1px var(--vc-line), 0 22px 44px -30px rgba(0, 0, 0, 0.55);
            rotate: var(--r, 0deg);
            transform-origin: 50% -1.75rem;
            transition: rotate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1), box-shadow 0.3s ease;
        }
        .vc-mini:hover { rotate: 0deg; box-shadow: 0 0 0 1px var(--vc-line-2), 0 30px 50px -30px rgba(0, 0, 0, 0.6); }
        .vc-mini::before { content: ""; position: absolute; left: 50%; bottom: 100%; width: 0.9rem; height: 1.75rem; margin-left: -0.45rem; background: var(--vc-ink); }
        .vc-mini-slot { display: block; width: 2.6rem; height: 0.6rem; margin: 0.7rem auto 0.6rem; border-radius: 1rem; background: var(--vc-bg); box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.4), 0 0 0 1px var(--vc-line); }
        .vc-mini-foil { display: flex; justify-content: space-between; gap: 1rem; padding: 0.7rem 1.1rem 0.6rem; font-family: var(--vc-mono); font-size: 0.66rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; line-height: 1.3; }
        .vc-mini-foil::before { transition: background-position 0.9s cubic-bezier(0.2, 0.7, 0.2, 1); }
        .vc-mini:hover .vc-mini-foil::before { background-position: 92% 8%; }
        .vc-mini-body { display: flex; flex-direction: column; flex: 1; padding: 1.2rem 1.1rem 1.3rem; }
        .vc-mini h3 { font-size: 1.55rem; font-weight: 700; letter-spacing: -0.035em; line-height: 1.05; }
        .vc-mini p { margin-top: 0.75rem; color: var(--vc-ink-2); font-size: 1rem; }
        .vc-mini a { align-self: flex-start; margin-top: auto; padding-top: 1.1rem; }
        .vc-mini a span { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; border-bottom: 2px solid var(--vc-c4); transition: gap 0.2s ease; }
        .vc-mini a:hover span { gap: 0.7rem; }
        .vc-mini a svg { width: 1rem; height: 1rem; }
        .vc-mini-bars { height: 1.5rem; margin: 0 1.1rem 1.1rem; background: repeating-linear-gradient(90deg, var(--vc-ink) 0 2px, transparent 2px 5px, var(--vc-ink) 5px 6px, transparent 6px 10px, var(--vc-ink) 10px 13px, transparent 13px 15px); opacity: 0.8; }

        /* ---------------------------------------------------------------
           08 Three steps
           --------------------------------------------------------------- */
        .vc-steps { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 2.5rem; padding-top: 2rem; }
        @media (min-width: 860px) { .vc-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .vc-steps::before { content: ""; position: absolute; inset: 0 0 auto 0; height: 3px; border-radius: 3px; background: linear-gradient(90deg, #22d3ee, #4ade80, #fde047, #fb923c); transform-origin: 0 50%; transition: scale 1.4s cubic-bezier(0.2, 0.7, 0.2, 1) 0.2s; }
        html.es-anim #vc .vc-steps:not(.is-revealed)::before { scale: 0 1; }
        .vc-step-no { display: block; font-size: clamp(4.5rem, 9vw, 7rem); font-weight: 700; letter-spacing: -0.06em; line-height: 0.85; color: transparent; -webkit-text-stroke: 1.5px var(--vc-ink); }
        .vc-steps h3 { margin-top: 1rem; font-size: 1.6rem; font-weight: 700; letter-spacing: -0.03em; line-height: 1.1; }
        .vc-steps p { margin-top: 0.7rem; max-width: 24rem; color: var(--vc-ink-2); }

        /* ---------------------------------------------------------------
           09 Key features, and the pages next door
           --------------------------------------------------------------- */
        .vc-kf { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 4rem; align-items: start; }
        @media (min-width: 960px) { .vc-kf { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); } }
        .vc-kf-more { margin-top: 1.75rem; }
        .vc-kf-list { border-top: 1px solid var(--vc-line-2); }
        .vc-kf-row { display: grid; grid-template-columns: 3rem minmax(0, 1fr) auto; align-items: center; gap: 1rem; padding: 1.2rem 0.5rem 1.15rem 0; border-bottom: 1px solid var(--vc-line); transition: padding 0.25s ease, background-color 0.25s ease; }
        .vc-kf-row:hover { padding-inline: 0.9rem 0.9rem; background: var(--vc-card); }
        .vc-kf-row > span:first-child { font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; color: var(--vc-ink-3); }
        .vc-kf-row strong { display: block; font-size: 1.45rem; font-weight: 700; letter-spacing: -0.03em; line-height: 1.1; }
        .vc-kf-row small { display: block; margin-top: 0.3rem; font-size: 0.98rem; color: var(--vc-ink-2); }
        .vc-kf-row svg { width: 1.4rem; height: 1.4rem; transition: translate 0.25s ease; }
        .vc-kf-row:hover svg { translate: 0.3rem 0; }
        .vc-also-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1.25rem; margin-bottom: 2.25rem; }
        .vc-also { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
        @media (min-width: 900px) { .vc-also { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; } }
        .vc-also a { position: relative; display: flex; flex-direction: column; justify-content: space-between; gap: 2.5rem; min-height: 9.5rem; padding: 1.1rem 1.1rem 1rem; border: 1px solid var(--vc-line-2); border-radius: 0.5rem; background: var(--vc-card); transition: translate 0.25s ease, border-color 0.25s ease; }
        .vc-also a:hover { translate: 0 -4px; border-color: var(--vc-ink); }
        .vc-also strong { font-size: clamp(1.15rem, 2vw, 1.5rem); font-weight: 700; letter-spacing: -0.03em; line-height: 1.1; }
        .vc-also small { display: inline-flex; align-items: center; gap: 0.4rem; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--vc-ink-2); }
        .vc-also svg { width: 0.9rem; height: 0.9rem; transition: translate 0.2s ease; }
        .vc-also a:hover svg { translate: 3px 0; }

        /* The plan band and the closing strip are shared partials: their words and prices stay. */
        #vc .vc-nudge > section { background: var(--vc-bg); border-top: 1px solid var(--vc-line); }
        #vc .vc-nudge h2 { font-family: var(--vc-sans); font-weight: 700; letter-spacing: -0.045em; line-height: 1; font-size: clamp(2rem, 4.4vw, 3.4rem); color: var(--vc-ink); }
        #vc .vc-nudge h2 + p { color: var(--vc-ink-2); font-size: 1.0625rem; }
        #vc .vc-nudge .grid > div { background: var(--vc-card); border: 1px solid var(--vc-line); border-radius: 0.5rem; box-shadow: none; color: var(--vc-ink); }
        #vc .vc-nudge .grid > div[class*="border-blue"] { border-color: var(--vc-ink); }
        #vc .vc-nudge .grid > div:hover { box-shadow: 0 22px 44px -28px rgba(0, 0, 0, 0.45); }
        #vc .vc-nudge .grid > div span,
        #vc .vc-nudge .grid > div p,
        #vc .vc-nudge .grid > div li { color: var(--vc-ink-2); }
        #vc .vc-nudge .grid > div .text-3xl { font-weight: 700; letter-spacing: -0.045em; font-size: 2.7rem; color: var(--vc-ink); }
        #vc .vc-nudge .grid > div .uppercase { font-family: var(--vc-mono); color: var(--vc-ink); }
        #vc .vc-nudge .grid > div .rounded-full { background: #fde047; color: #0e0f12; font-family: var(--vc-mono); }
        #vc .vc-nudge .grid > div svg { color: var(--vc-c2); }
        #vc .vc-nudge a.font-medium { color: var(--vc-ink); border-bottom: 2px solid var(--vc-c4); }
        #vc .vc-nudge a.rounded-2xl { background: var(--vc-btn); color: var(--vc-on-btn); border-radius: 999px; box-shadow: none; font-weight: 700; }

        #vc .vc-keep > section { background: var(--vc-bg-2); border-top: 1px solid var(--vc-line); }
        #vc .vc-keep h2 { font-weight: 700; letter-spacing: -0.045em; font-size: clamp(1.9rem, 3.6vw, 2.8rem); line-height: 1; color: var(--vc-ink); }
        #vc .vc-keep p.uppercase { font-family: var(--vc-mono); letter-spacing: 0.14em; color: var(--vc-ink-2); }
        #vc .vc-keep .grid > a { background: var(--vc-card); border: 1px solid var(--vc-line); border-radius: 0.5rem; }
        #vc .vc-keep .grid > a:hover { border-color: var(--vc-ink); box-shadow: 0 22px 44px -30px rgba(0, 0, 0, 0.5); }
        #vc .vc-keep .grid > a > span:first-child { height: 3px; background: linear-gradient(90deg, #22d3ee, #4ade80, #fde047, #fb923c); }
        #vc .vc-keep .grid > a h3 { color: var(--vc-ink); }
        #vc .vc-keep .grid > a p { color: var(--vc-ink-2); }
        #vc .vc-keep .grid > a > span:last-child,
        #vc .vc-keep a.self-start { color: var(--vc-c1); }

        /* ---------------------------------------------------------------
           10 Questions
           --------------------------------------------------------------- */
        .vc-faq { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.25rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .vc-faq { grid-template-columns: minmax(0, 0.72fr) minmax(0, 1.28fr); }
            .vc-faq-head { position: sticky; top: calc(var(--vc-top) + 1.5rem); }
        }
        .vc-faq-head .vc-h2 { font-size: clamp(2.3rem, 4.6vw, 3.8rem); }
        .vc-faq-head .vc-sub { margin-top: 1.25rem; }
        .vc-qa { counter-reset: vc-q; border-top: 1px solid var(--vc-line-2); }
        .vc-qa details { counter-increment: vc-q; border-bottom: 1px solid var(--vc-line); }
        .vc-qa summary { display: grid; grid-template-columns: 3rem minmax(0, 1fr) 1.4rem; align-items: start; gap: 0.75rem; padding: 1.3rem 0.25rem 1.2rem 0; cursor: pointer; }
        .vc-qa summary::before { content: "Q." counter(vc-q, decimal-leading-zero); padding-top: 0.3rem; font-family: var(--vc-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; color: var(--vc-ink-3); }
        .vc-qa h3 { font-size: 1.25rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1.25; }
        .vc-qa summary i { position: relative; width: 1.4rem; height: 1.4rem; margin-top: 0.15rem; }
        .vc-qa summary i::before,
        .vc-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .vc-qa summary i::after { rotate: 90deg; }
        .vc-qa details[open] summary i::after { rotate: 0deg; }
        .vc-qa details p { padding: 0 0.25rem 1.6rem 3.75rem; max-width: 48rem; color: var(--vc-ink-2); }
        @media (max-width: 560px) { .vc-qa details p { padding-inline-start: 0; } }

        /* ---------------------------------------------------------------
           11 Close: your name on the badge
           --------------------------------------------------------------- */
        .vc-close { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 4rem; align-items: center; }
        @media (min-width: 980px) { .vc-close { grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr); } }
        .vc-close .vc-h2 { font-size: clamp(2.6rem, 6.4vw, 5.4rem); }
        .vc-close-sub { margin-top: 1.5rem; max-width: 38rem; font-size: 1.15rem; color: var(--vc-ink-2); }
        .vc-close-form { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.1rem; max-width: 38rem; margin-top: 2.25rem; }
        @media (min-width: 640px) { .vc-close-form { grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 1.25rem; } }
        #vc .vc-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.1rem;
            border: 1.5px solid rgba(244, 243, 238, 0.42);
            border-radius: 0.6rem;
            background: rgba(244, 243, 238, 0.06);
            --vc-claim-fs: clamp(0.88rem, 3.1vw, 1.02rem);
            font-family: var(--vc-mono);
            font-weight: 600;
            font-size: var(--vc-claim-fs);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        #vc .vc-claim:focus-within { border-color: #f4f3ee; box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.3); }
        /* iOS zooms the page when a field under 16px takes focus, so what is typed is never
           smaller than that. The placeholder and the suffix keep the row's own size. */
        #vc .vc-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; font-size: max(1rem, var(--vc-claim-fs)); color: #f4f3ee; box-shadow: none; outline: none; }
        #vc .vc-claim input::placeholder { color: #a9aba8; font-size: var(--vc-claim-fs); }
        @media (max-width: 340px) { #vc .vc-claim { padding-inline: 0.8rem; } }
        .vc-claim span { flex: none; color: #c9cac6; user-select: none; }
        .vc-close-note { margin-top: 1.25rem; color: var(--vc-ink-3); font-size: 0.95rem; }
        .vc-pass-rig { position: relative; width: min(100%, 18.5rem); margin-inline: auto; padding-top: 4.25rem; }
        .vc-pass-rig .vc-strap { bottom: calc(100% - 4rem); height: 18rem; background: #f4f3ee; color: #0e0f12; box-shadow: inset 2px 0 0 rgba(255, 255, 255, 0.6), inset -2px 0 0 rgba(0, 0, 0, 0.2); }
        .vc-pass-rig .vc-clip { top: 3.4rem; }
        /* Stacked, the pass hangs under the form, and the lanyard ran up across the name field and
           the button. It fades in above the clip instead, as the one in the hero does. */
        @media (max-width: 979px) {
            .vc-pass-rig .vc-strap { height: 5.25rem; -webkit-mask-image: linear-gradient(to top, #000 0, #000 2.3rem, transparent 5rem); mask-image: linear-gradient(to top, #000 0, #000 2.3rem, transparent 5rem); }
        }
        .vc-pass {
            position: relative;
            container-type: inline-size;
            overflow: hidden;
            border-radius: 1.05rem;
            background: linear-gradient(165deg, #ffffff, #eceef0);
            color: #0e0f12;
            box-shadow: 0 30px 60px -24px rgba(0, 0, 0, 0.8), 0 0 60px -10px rgba(34, 211, 238, 0.25);
            transform: perspective(1000px) rotateY(-9deg) rotateX(3deg) rotate(2deg);
        }
        .vc-pass .vc-badge-slot { background: #0b0c0e; box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.6); }
        .vc-pass-head { display: flex; justify-content: space-between; gap: 1rem; padding: 1.5rem 1.1rem 1.4rem; font-family: var(--vc-mono); font-size: 0.66rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; line-height: 1.3; }
        .vc-pass-body { padding: 1.4rem 1.1rem 1.3rem; }
        .vc-pass-k { font-family: var(--vc-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #4a4e56; }
        .vc-pass-name {
            --len: 11;
            display: block;
            margin-top: 0.5rem;
            font-family: var(--vc-mono);
            font-weight: 700;
            font-size: min(2rem, calc(100cqi / (var(--len) * 0.68)));
            letter-spacing: -0.02em;
            line-height: 1.1;
            white-space: nowrap;
        }
        .vc-pass-host { display: block; margin-top: 0.3rem; font-family: var(--vc-mono); font-size: 0.8rem; font-weight: 600; color: #4a4e56; }
        .vc-pass-foot { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: end; gap: 0.9rem; padding: 1rem 1.1rem 1.2rem; border-top: 1px dashed rgba(14, 15, 18, 0.22); }
        .vc-pass-foot b { display: block; font-size: 1.15rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; line-height: 1.1; }
        .vc-pass-foot small { display: block; margin-top: 0.35rem; font-family: var(--vc-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #4a4e56; }

        /* ---------------------------------------------------------------
           The index down the edge, on wide screens only
           --------------------------------------------------------------- */
        .vc-index { display: none; }
        @media (min-width: 1560px) {
            /* The nav is a box the size of the page that clips the index, so the index stays fixed
               to the screen and still ends where the page does instead of riding over the site
               footer. The blend sits on that box, which is what meets the page. */
            .vc-index { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; mix-blend-mode: difference; color: #ffffff; }
            .vc-index ol { position: fixed; right: 1.1rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.1rem; justify-items: end; pointer-events: auto; }
            .vc-index a { display: flex; align-items: center; gap: 0.6rem; padding: 0.28rem 0; font-family: var(--vc-mono); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; opacity: 0.55; transition: opacity 0.25s ease; }
            .vc-index a span { opacity: 0; translate: 0.3rem 0; transition: opacity 0.25s ease, translate 0.25s ease; }
            .vc-index a::after { content: ""; width: 0.8rem; height: 2px; background: currentColor; transition: width 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
            .vc-index a:hover,
            .vc-index a:focus-visible,
            .vc-index a.is-active { opacity: 1; }
            .vc-index a:hover span,
            .vc-index a:focus-visible span,
            .vc-index a.is-active span { opacity: 1; translate: 0 0; }
            .vc-index a.is-active::after { width: 2.2rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .vc-holo-text,
            .vc-foil,
            .vc-hero-glow,
            .vc-band-glow,
            .vc-rig,
            .vc-run-now,
            .vc-scan-sheet b,
            .vc-btn-holo::before,
            .vc-clock-face i:nth-child(3) { animation: none; }
            .vc-badge,
            .vc-grid,
            .vc-mini,
            .vc-btn,
            .vc-also a,
            .vc-kf-row,
            .vc-steps::before,
            .vc-after > article::before,
            .vc-mini-foil::before { transition: none; }
        }
    </style>

    @php
        // ------------------------------------------------------------------
        // Day one's running order. The product shape: ONE Event, plus ordered
        // EventPart rows carrying name / description / start_time / end_time.
        // A block's height IS its duration, so 0.085rem per minute turns the
        // list into a proportional timetable rather than a stack of equals.
        // ------------------------------------------------------------------
        $minuteRem = 0.085;
        $run = [
            ['09:00', 'Doors, welcome and housekeeping', 25, false],
            ['09:25', 'Opening keynote', 50, false],
            ['10:15', 'Break', 15, true],
            ['10:30', 'Panel: shipping in the open', 45, false],
            ['11:15', 'Workshop: instrumenting your stack', 60, false],
            ['12:15', 'Break', 15, true],
            ['12:30', 'Lightning talks', 30, false],
        ];
        // A break is a part too, which the hero says out loud, so the count is all rows.
        $runParts = count($run);

        // The three-day programme. Each day is its own event on its own date,
        // with its own running order and its own join link. Offsets are
        // minutes from 09:00 across a 09:00-18:00 window (540 minutes), and
        // the third value marks a break, which is a part like any other and
        // so gets a tick - hollow, exactly as in the hero. Ticks are drawn
        // 3px short of their true width so that abutting parts stay
        // countable instead of fusing into one bar.
        $window = 540;
        $programme = [
            ['Day 1', 'Tue 3 Mar', '09:00 to 13:00', 7, [[0, 25, false], [25, 50, false], [75, 15, true], [90, 45, false], [135, 60, false], [195, 15, true], [210, 30, false]]],
            ['Day 2', 'Wed 4 Mar', '10:00 to 16:00', 9, [[60, 30, false], [90, 45, false], [135, 15, true], [150, 60, false], [210, 45, false], [255, 45, false], [300, 15, true], [315, 60, false], [375, 45, false]]],
            ['Day 3', 'Thu 5 Mar', '09:30 to 13:00', 5, [[30, 45, false], [75, 15, true], [90, 60, false], [150, 45, false], [195, 45, false]]],
        ];

        // The event itself: the fields you fill in once for the whole day.
        $dayFields = [
            ['Event', 'Cloud Summit, day 1'],
            ['Date', 'Tue 3 Mar 2026'],
            ['Starts', '09:00'],
            ['Runs for', '4 hours'],
            ['Online', 'yes, one join link'],
            ['Sub-schedule', 'Main programme'],
        ];

        $faqs = [
            [
                'q' => 'Can I schedule a multi-day virtual conference?',
                'a' => 'Yes. Each conference day is one event on its own date, with its own join link, and the day\'s sessions go inside it as parts of the agenda. Every part has a name, an optional description and its own start and end time, so attendees read the whole running order on one link. Sub-schedules can file the days under a strand and give that strand a color.',
            ],
            [
                'q' => 'Does each session get its own streaming link?',
                'a' => 'One join link per event. A day is one event, so the day has one link and every part of its running order sits behind that link. If two sessions genuinely need two different links, make them two events on the same date. Any platform works, because all Event Schedule stores is the URL: Zoom, Microsoft Teams, Google Meet, YouTube Live, or anything else that gives you one.',
            ],
            [
                'q' => 'What about tracks and rooms?',
                'a' => 'There are none, and it is better to say so before you move a programme across. Event Schedule has one running order per event. Parallel sessions are separate events on the same date, and a sub-schedule can keep a strand together and color it, which is organizing and color-coding rather than access control. There is no room inventory and nothing is hidden by a sub-schedule.',
            ],
            [
                'q' => 'Is the agenda free?',
                'a' => 'Yes. Adding parts, naming them, giving them start and end times, writing a description for each one, moving them into order and publishing the running order are all free forever, along with the join link, calendar sync and the embeddable calendar. Agenda scanning, which reads a printed or emailed programme and fills the parts in for you, is on the Enterprise plan. Typing them costs nothing.',
            ],
            [
                'q' => 'Can I sell different ticket types for my conference?',
                'a' => 'Yes, on Pro. Create as many named ticket types as the conference needs, each with its own price, quantity and sales window. Pro at '.plan_price($proMonthly).' a month is what opens paid checkout, and it adds discount codes, add-ons and individual tickets, which give every attendee their own confirmation email and QR code; custom questions collect what you need at checkout. Event Schedule charges zero platform fees at every plan level: attendees pay through your own Stripe or PayPal account, or through Invoice Ninja, a payment link or cash, and your processor\'s fee is the only cut. For a free conference, registration with a capacity limit is unlimited on the free plan.',
            ],
            [
                'q' => 'Can I announce the conference before tickets go on sale?',
                'a' => 'Yes. Publish the day with its running order and open ticket sales later. Until they open, and with the free "Notify me" card switched on, the event page offers "Tell me when tickets go on sale": a visitor leaves an email address, nothing else, and gets one email the moment tickets are on sale, a reminder shortly before the day starts, a notice if it is cancelled, and any change notice you choose to send. The event editor shows you how many people are waiting. It is free on every plan, it is not a subscription to your schedule, and it does not draw on your newsletter allowance.',
            ],
            [
                'q' => 'Can I refund an attendee who cannot make it?',
                'a' => 'Yes, from the Sales page, on Pro. A Stripe or PayPal payment goes back through the provider, in full or for part of the amount, and the sale only changes once the money has moved. A partial refund leaves the ticket valid; a full refund puts the place back on sale for somebody else. A sale taken through Invoice Ninja, Payfast, a payment link or cash is marked as refunded for your records, and you return that money yourself.',
            ],
            [
                'q' => 'How do attendees hear about the next edition?',
                'a' => 'Two ways. Somebody who left an email address on your page and confirmed it is sent a digest on its own when you post the sessions, batched rather than one message per talk. Somebody who pressed Follow from their own account is reached only by a newsletter you write, which is what you want when the programme is set and there is something to say about it: 10 emails a month on the free plan, 100 on Pro and 1,000 on Enterprise, counted one per recipient. Followers also show up with their name and email on your followers tab, so the audience is yours rather than a platform\'s.',
            ],
        ];

        $dotSections = [
            ['top', 'The running order'],
            ['unit', 'One event'],
            ['run', 'Setting it'],
            ['programme', 'The programme'],
            ['session', 'In session'],
            ['tickets', 'The takings'],
            ['after', 'Afterwards'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Close'],
        ];
    @endphp

    @php
        // ------------------------------------------------------------------
        // Derived views of the data above, and set dressing for the agenda.
        // ------------------------------------------------------------------
        $vcArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $vcDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';

        // The page's data gives days two and three a start, a length and a break flag for each
        // part (the ticks) and nothing else, so the names below are sample set dressing. Day one's
        // are the running order's own.
        $vcNames = [
            array_column($run, 1),
            ['Welcome back, and the day in brief', 'Workshop: tracing a slow request', 'Break', 'Workshop: shipping without a freeze', 'Open floor: ask the maintainers', 'Workshop: budgets, alerts and sleep', 'Break', 'Clinic: bring your own dashboard', 'Show and tell'],
            ['Keynote: what we got wrong', 'Break', 'Panel: the next five years of on-call', 'Community lightning round', 'Closing notes, and next year'],
        ];
        // A part can carry a description. A few sample ones, keyed day-part.
        $vcAbstracts = [
            '0-1' => 'Where the platform goes next, and what it cost to get here.',
            '0-4' => 'Bring a laptop. We add three traces to a real service.',
            '1-3' => 'Release trains, flags, and what to do on a Friday.',
            '1-7' => 'Ten-minute slots. First come, first looked at.',
            '2-2' => 'Four people who carry the pager, and one who never has.',
        ];
        $vcKeys = ['0-1', '2-0'];
        // The strand each day is filed under: a sub-schedule, which is a name and a color.
        $vcStrands = ['Main programme', 'Workshop day', 'Community day'];

        // The agenda's rows: a named grid line every five minutes from 09:00 to 16:00, so a part
        // is placed by the clock (grid-row: t0925 / t1015) rather than by counting rows.
        $vcClock = fn (int $offset) => sprintf('%02d:%02d', intdiv(540 + $offset, 60), (540 + $offset) % 60);
        $vcLine = fn (int $offset) => 't'.sprintf('%02d%02d', intdiv(540 + $offset, 60), (540 + $offset) % 60);
        $vcSpan = 420;
        $vcRows = '[head] auto ';
        for ($vcAt = 0; $vcAt < $vcSpan; $vcAt += 5) {
            $vcRows .= '['.$vcLine($vcAt).'] var(--vc-5) ';
        }
        $vcRows .= '['.$vcLine($vcSpan).']';

        // A pattern that reads as a code at a glance: three finders and seeded noise, drawn as
        // the shadows of one small square. It encodes nothing.
        $vcCode = function (string $seed, int $size = 15): string {
            $lit = function (int $x, int $y) use ($seed, $size): bool {
                foreach ([[0, 0], [$size - 5, 0], [0, $size - 5]] as [$fx, $fy]) {
                    if ($x >= $fx && $x < $fx + 5 && $y >= $fy && $y < $fy + 5) {
                        $dx = $x - $fx;
                        $dy = $y - $fy;

                        return $dx === 0 || $dx === 4 || $dy === 0 || $dy === 4 || ($dx === 2 && $dy === 2);
                    }
                    if ($x >= $fx - 1 && $x <= $fx + 5 && $y >= $fy - 1 && $y <= $fy + 5) {
                        return false;
                    }
                }

                return (crc32($seed.':'.$x.':'.$y) & 3) < 2;
            };
            $dots = [];
            for ($y = 0; $y < $size; $y++) {
                for ($x = 0; $x < $size; $x++) {
                    if (($x || $y) && $lit($x, $y)) {
                        $dots[] = $x.'em '.$y.'em 0 0.04em currentColor';
                    }
                }
            }

            return implode(', ', $dots);
        };

        $vcStrap = str_repeat('Cloud Summit 2026 · ', 7);
    @endphp

    <div id="vc">

        <nav class="vc-index es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot"><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- Hero: the headline, and the badge on its lanyard             -->
        <!-- ============================================================ -->
        <section class="vc-hero" id="top">
            <div class="vc-hero-dots" aria-hidden="true"></div>
            <div class="vc-hero-glow" aria-hidden="true"></div>

            <div class="vc-wrap vc-hero-grid">
                <div class="vc-hero-copy">
                    <h1 class="vc-h1">
                        <x-marketing.hero-eyebrow class="vc-eyebrow es-fade-up es-d-1">
                            Virtual conference agenda for online summit organizers
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">A conference day is one event.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="vc-holo-text">agenda</span> goes inside it.</span></span>
                    </h1>

                    <p class="vc-lede es-fade-up es-d-2">
                        Enter each session as a part of the day, with its own name and its own start and end time. Your virtual conference agenda publishes as one running order, on one link, with one link to join, and it is free on every plan.
                    </p>

                    <div class="vc-cta es-fade-up es-d-3">
                        <a href="#run" class="vc-btn vc-btn-ghost">
                            See how a day is built
                            {!! $vcDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="vc-btn vc-btn-holo">
                            Create your conference schedule
                            {!! $vcArrow !!}
                        </a>
                    </div>
                </div>

                <!-- The badge. The day's running order is printed on it: one event, its parts inside. -->
                <figure class="vc-hang es-fade-up es-d-2">
                    <div class="vc-rig" aria-hidden="true">
                        <span class="vc-strap"><i>{{ $vcStrap }}</i></span>
                        <span class="vc-strap"><i>{{ $vcStrap }}</i></span>
                        <span class="vc-clip"></span>
                        <div class="vc-badge" id="vc-badge">
                            <span class="vc-badge-slot"></span>
                            <div class="vc-foil vc-badge-head">
                                <div class="vc-badge-top"><span>Cloud Summit 2026</span><span>TUE 3 MAR</span></div>
                                <div class="vc-badge-title">Cloud Summit, day 1</div>
                                <div class="vc-badge-sub">One event &middot; {{ $runParts }} parts &middot; one link to join</div>
                            </div>
                            <div class="vc-run">
                                <span class="vc-run-now"></span>
                                @foreach ($run as [$partTime, $partName, $partMins, $partIsGap])
                                    <div class="vc-run-row">
                                        <div class="vc-run-t">{{ $partTime }}</div>
                                        <div class="vc-run-b @if ($partIsGap) is-gap @endif" style="min-height: {{ $partIsGap ? '1.35rem' : '2.1rem' }}; height: {{ round($partMins * $minuteRem, 3) }}rem;">
                                            <div class="vc-run-n">{{ $partName }}</div>
                                            @if (! $partIsGap)
                                                <div class="vc-run-d">{{ $partMins }} min</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                <div class="vc-run-row">
                                    <div class="vc-run-t">13:00</div>
                                    <div class="vc-run-end">end of day</div>
                                </div>
                            </div>
                            <div class="vc-badge-foot">
                                <span class="vc-qr"><i style="box-shadow: {{ $vcCode('cloud-summit') }};"></i></span>
                                <div>
                                    <b>Organizer</b>
                                    <small>Main programme</small>
                                </div>
                                <span class="vc-badge-no">No. 0001</span>
                            </div>
                        </div>
                    </div>
                    <figcaption class="vc-hang-cap">
                        Each block is as tall as it is long. A break is a part with a name and a time too, which is why the gaps are on the page.
                    </figcaption>
                </figure>
            </div>

            <!-- Conference-type ticker -->
            <div class="vc-ticker es-fade-up es-d-4">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach (['Tech Summits', 'Industry Conferences', 'Company Retreats', 'Professional Summits', 'Annual Meetings', 'Panel Events', 'Developer Cons', 'Hybrid Events'] as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="vc-tick">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 01 The unit: what you enter, what they read                  -->
        <!-- ============================================================ -->
        <section id="unit" class="vc-sec vc-alt" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>01</b><i></i>The unit</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Seven parts. <span class="vc-holo-text">One event.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Most calendars would have you enter that morning as seven separate events. Here you fill the day in once and type the running order underneath it, so the date, the join link and the tickets are stated once and cannot disagree with each other.
                    </p>
                </header>

                <div class="vc-duo" data-reveal-group="110">
                    <span class="vc-duo-arrow" aria-hidden="true">{!! $vcArrow !!}</span>

                    <!-- What you enter -->
                    <article class="vc-panel vc-marks" data-reveal="panel">
                        <div class="vc-titled">
                            <h3 class="vc-h3">What you enter</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p class="vc-note" style="margin-top: 0.6rem;">The event: one date, one start, one duration, one link.</p>
                        <dl class="vc-fields">
                            @foreach ($dayFields as [$fieldKey, $fieldVal])
                                <div>
                                    <dt>{{ $fieldKey }}</dt>
                                    <dd>{{ $fieldVal }}</dd>
                                </div>
                            @endforeach
                        </dl>
                        <p class="vc-note">Move the date here and the whole running order moves with it, because the parts live inside the event. The clock times on the parts are the ones you typed, so those you edit yourself.</p>
                    </article>

                    <!-- What they read -->
                    <article class="vc-panel vc-marks" data-reveal="panel">
                        <div class="vc-titled">
                            <h3 class="vc-h3">What they read</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p class="vc-note" style="margin-top: 0.6rem;">The parts: the running order, in order, with times.</p>
                        <ol class="vc-read">
                            @foreach ($run as $runIndex => [$partTime, $partName, $partMins, $partIsGap])
                                <li class="@if ($partIsGap) is-gap @endif">
                                    <time>{{ $partTime }}</time>
                                    <span>{{ $partName }}</span>
                                    <small>{{ $partMins }}m</small>
                                </li>
                            @endforeach
                        </ol>
                        <p class="vc-note">The first few parts show on the schedule page with their times, and the rest sit behind a "more" line so a long day does not swamp the calendar.</p>
                    </article>
                </div>

                <div class="vc-stats" data-reveal-group="90">
                    <div class="vc-stat" data-reveal>
                        <h3>Events to enter</h3>
                        <p class="vc-stat-n vc-holo-text"><span data-count-to="1">1</span></p>
                        <p>One date, one start time, one duration. Not seven chances to mistype a time zone.</p>
                    </div>
                    <div class="vc-stat" data-reveal>
                        <h3>Parts inside it</h3>
                        <p class="vc-stat-n vc-holo-text"><span data-count-to="7">7</span></p>
                        <p>As many as the day has. Each one carries a name, a description, a start and an end.</p>
                    </div>
                    <div class="vc-stat" data-reveal>
                        <h3>Links to share</h3>
                        <p class="vc-stat-n vc-holo-text"><span data-count-to="1">1</span></p>
                        <p>The programme, the sign-up and the way in are the same page.</p>
                    </div>
                </div>

                <!-- The same minute on four clocks: 09:00 on Tue 3 Mar, as it falls elsewhere -->
                <div class="vc-clocks" aria-hidden="true" data-reveal>
                    <p class="vc-clocks-k">One start time, 09:00 on Tue 3 Mar<br>The same minute on four clocks</p>
                    @foreach ([['Lisbon', '09:00', 9, 0, false], ['New York', '04:00', 4, 0, true], ['Bengaluru', '14:30', 14, 30, false], ['Sydney', '20:00', 20, 0, true]] as [$cityName, $cityTime, $cityHour, $cityMin, $cityNight])
                        <div class="vc-clock @if ($cityNight) is-night @endif">
                            <span class="vc-clock-face" style="--h: {{ (($cityHour % 12) + $cityMin / 60) * 30 }}deg; --m: {{ $cityMin * 6 }}deg;"><i></i><i></i><i></i></span>
                            <span>
                                <b>{{ $cityName }}</b>
                                <small>{{ $cityTime }}</small>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 02 Setting the running order: a part, opened                 -->
        <!-- ============================================================ -->
        <section id="run" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>02</b><i></i>Setting the running order</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Name it, time it, <span class="vc-holo-text">move it into place.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Three things make a session, and all three are on the free plan.
                    </p>
                </header>

                <div class="vc-set">
                    <!-- One part of the agenda, open in the editor -->
                    <div class="vc-editor" aria-hidden="true" data-reveal>
                        <div class="vc-editor-bar"><span>Agenda &middot; Cloud Summit, day 1</span><span class="vc-sw">Times</span><span class="vc-sw">Descriptions</span></div>
                        <div class="vc-pcard is-dim"><time>10:15</time><b>Break</b></div>
                        <div class="vc-pcard is-open">
                            <div class="vc-pcard-head">
                                <span>Part 4 of 7</span>
                                <span class="vc-ud"><span class="vc-pin" style="--pin: #fb923c;">3</span><i></i><i></i></span>
                            </div>
                            <div class="vc-fld">
                                <span>Name</span>
                                <span class="vc-inp">Panel: shipping in the open</span>
                                <span class="vc-pin" style="--pin: #22d3ee;">1</span>
                            </div>
                            <div class="vc-fld vc-fld-2">
                                <div class="vc-fld" style="margin-top: 0;"><span>Starts</span><span class="vc-inp vc-inp-mono">10:30</span></div>
                                <div class="vc-fld" style="margin-top: 0;"><span>Ends</span><span class="vc-inp vc-inp-mono">11:15</span></div>
                                <span class="vc-pin" style="--pin: #4ade80;">2</span>
                            </div>
                            <div class="vc-fld">
                                <span>Description</span>
                                <span class="vc-inp vc-inp-area">Four teams on what broke when they went public.</span>
                            </div>
                        </div>
                        <div class="vc-pcard is-dim"><time>11:15</time><b>Workshop: instrumenting your stack</b></div>
                    </div>

                    <div>
                        <div class="vc-three" data-reveal-group="100">
                            <article data-reveal>
                                <span class="vc-pin" style="--pin: #22d3ee;" aria-hidden="true">1</span>
                                <div class="vc-titled">
                                    <h3 class="vc-h3">A name, and not much else</h3>
                                    <span class="vc-tier">Free</span>
                                </div>
                                <p>The name is the only thing a part needs. Add a description in markdown when the session deserves an abstract, and leave it empty when the title says it all.</p>
                            </article>
                            <article data-reveal>
                                <span class="vc-pin" style="--pin: #4ade80;" aria-hidden="true">2</span>
                                <div class="vc-titled">
                                    <h3 class="vc-h3">A start and an end</h3>
                                    <span class="vc-tier">Free</span>
                                </div>
                                <p>Each part takes its own start time and its own end time, and both are optional. Two switches in the agenda editor, remembered for the whole schedule, decide whether it asks you for times and descriptions at all, so a programme that is still only titles stays a list of titles.</p>
                            </article>
                            <article data-reveal>
                                <span class="vc-pin" style="--pin: #fb923c;" aria-hidden="true">3</span>
                                <div class="vc-titled">
                                    <h3 class="vc-h3">An order you can change</h3>
                                    <span class="vc-tier">Free</span>
                                </div>
                                <p>Every part card has an up and a down button, so a session moves one row at a time. Switch times and descriptions off and the editor collapses to a drag-and-drop list of titles. When a speaker swaps slot the morning is a click away from correct, not a re-typed agenda.</p>
                            </article>
                        </div>

                    </div>
                </div>

                <div class="vc-panel vc-scan-card" data-reveal="panel">
                    <div class="vc-scan" aria-hidden="true">
                        <div class="vc-scan-sheet"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><b></b></div>
                        {!! $vcArrow !!}
                        <ol class="vc-scan-out">
                            <li>09:00<i></i></li>
                            <li>09:25<i></i></li>
                            <li>10:15<i></i></li>
                            <li>10:30<i></i></li>
                        </ol>
                    </div>
                    <div>
                        <div class="vc-titled">
                            <h3 class="vc-h3">Already have the programme written somewhere</h3>
                            <span class="vc-tier vc-tier-ent">Enterprise</span>
                        </div>
                        <p>
                            Agenda scanning reads a programme from a photo or from pasted text and fills the parts in for you, times and all. Being straight about the tier: that one is on the Enterprise plan, and there is a daily limit on it. Typing the parts yourself is free and always will be, which is why the rest of this page does not depend on it.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 03 The programme: three days, three events                   -->
        <!-- ============================================================ -->
        <section id="programme" class="vc-sec vc-alt" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>03</b><i></i>The programme</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Three days is three events, <span class="vc-holo-text">stacked.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        One event per date, each with its own running order and its own way in. Read down the strip and you are reading the whole conference at once.
                    </p>
                </header>

                <div class="vc-panel vc-marks" data-reveal="panel">
                    <div class="vc-prog-top">
                        <p>Cloud Summit 2026</p>
                        <p>21 parts across 3 days</p>
                    </div>

                    <table class="vc-strip">
                        <caption class="sr-only">Cloud Summit 2026: each conference day with its date, the hours it runs and the number of agenda parts inside it</caption>
                        <thead>
                            <tr>
                                <th scope="col">Day</th>
                                <th scope="col">Date</th>
                                <th scope="col">Runs</th>
                                <th scope="col" class="vc-strip-n">Parts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr aria-hidden="true">
                                <td colspan="4">
                                    <div class="vc-ruler">
                                        @foreach (range(9, 17) as $hour)
                                            <span>{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                            @foreach ($programme as $dayIndex => [$dayName, $dayDate, $dayHours, $dayParts, $dayTicks])
                                <tr class="vc-strip-day">
                                    <th scope="row">{{ $dayName }}</th>
                                    <td>{{ $dayDate }}</td>
                                    <td>{{ $dayHours }}</td>
                                    <td class="vc-strip-n">{{ $dayParts }}</td>
                                </tr>
                                <tr class="vc-strip-ticks vc-day-{{ $dayIndex + 1 }}" aria-hidden="true">
                                    <td colspan="4">
                                        <div class="vc-track">
                                            @foreach ($dayTicks as [$tickAt, $tickFor, $tickIsGap])
                                                <span class="vc-tickmark @if ($tickIsGap) is-gap @endif" style="inset-inline-start: {{ round($tickAt / $window * 100, 2) }}%; width: calc({{ round($tickFor / $window * 100, 2) }}% - 3px);"></span>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="vc-note">Each mark is one part of that day's running order, as wide as the part is long, drawn where it falls between 09:00 and 18:00. The hollow ones are the breaks. Count them and you get the number in the Parts column.</p>

                    <!-- The same three days, opened: one column per event, its parts on the clock -->
                    <div class="vc-ag">
                        <fieldset class="vc-filter">
                            <legend class="sr-only">Show days</legend>
                            <span class="vc-filter-k" aria-hidden="true">Open</span>
                            <input type="radio" name="vc-day" id="vc-f0" class="vc-f" checked>
                            <label for="vc-f0">All three days</label>
                            @foreach ($programme as $dayIndex => [$dayName])
                                <input type="radio" name="vc-day" id="vc-f{{ $dayIndex + 1 }}" class="vc-f">
                                <label for="vc-f{{ $dayIndex + 1 }}" class="vc-day-{{ $dayIndex + 1 }}"><i aria-hidden="true"></i>{{ $dayName }}</label>
                            @endforeach
                        </fieldset>

                        <div class="vc-grid" style="grid-template-rows: {{ $vcRows }};">
                            <div class="vc-gutter" aria-hidden="true">
                                @foreach (range(0, 7) as $vcHour)
                                    <span style="top: calc(var(--vc-min) * {{ $vcHour * 60 }});">{{ $vcClock($vcHour * 60) }}</span>
                                @endforeach
                            </div>
                            <div class="vc-lines" aria-hidden="true"></div>
                            <div class="vc-now" aria-hidden="true"><div class="vc-now-line"><span class="vc-now-tag">Now <b>10:42</b></span></div></div>

                            @foreach ($programme as $dayIndex => [$dayName, $dayDate, $dayHours, $dayParts, $dayTicks])
                                <section class="vc-day vc-day-{{ $dayIndex + 1 }}" aria-labelledby="vc-day-{{ $dayIndex + 1 }}-h">
                                    <label for="vc-f{{ $dayIndex + 1 }}" class="vc-day-pick" aria-hidden="true"></label>
                                    <header class="vc-day-head">
                                        <span class="vc-day-bar" aria-hidden="true"></span>
                                        <h3 id="vc-day-{{ $dayIndex + 1 }}-h">{{ $dayName }}</h3>
                                        <p class="vc-day-date">{{ $dayDate }}</p>
                                        <p class="vc-day-meta"><span>{{ $vcStrands[$dayIndex] }}</span><span>{{ $dayHours }}</span><span>{{ $dayParts }} parts</span></p>
                                        <span class="vc-day-short" aria-hidden="true">D{{ $dayIndex + 1 }}</span>
                                    </header>
                                    <ol class="vc-day-parts">
                                        @foreach ($dayTicks as $tickIndex => [$tickAt, $tickFor, $tickIsGap])
                                            <li class="vc-part @if ($tickIsGap) is-break @endif @if (in_array($dayIndex.'-'.$tickIndex, $vcKeys, true)) is-key @endif" style="grid-row: {{ $vcLine($tickAt) }} / {{ $vcLine($tickAt + $tickFor) }};">
                                                <span class="vc-part-t">{{ $vcClock($tickAt) }}</span>
                                                @if ($tickIsGap)
                                                    <span class="vc-part-n">{{ $vcNames[$dayIndex][$tickIndex] }}</span>
                                                    <span class="vc-part-d">{{ $tickFor }} min</span>
                                                @else
                                                    <span class="vc-part-d">{{ $tickFor }} min</span>
                                                    <span class="vc-part-n">{{ $vcNames[$dayIndex][$tickIndex] }}</span>
                                                    @isset($vcAbstracts[$dayIndex.'-'.$tickIndex])
                                                        <span class="vc-part-a">{{ $vcAbstracts[$dayIndex.'-'.$tickIndex] }}</span>
                                                    @endisset
                                                @endif
                                            </li>
                                        @endforeach
                                        @php $vcDayEnd = end($dayTicks)[0] + end($dayTicks)[1]; @endphp
                                        <li class="vc-day-end" aria-hidden="true" style="top: calc(var(--vc-min) * {{ $vcDayEnd }});"><span>{{ $vcClock($vcDayEnd) }}</span>end of day</li>
                                    </ol>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>

                <p class="vc-strand-note" data-reveal>
                    Sub-schedules keep a strand of the programme together and give it a color, which is useful for a workshop day or a members-only strand you want to point people at. Worth saying plainly: a sub-schedule organizes and colors, it does not restrict who can see what, and there are no rooms and no tracks. To hide a day until you announce it, leave it as a draft.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 04 In session: seven parts, one link (a band that stays dark) -->
        <!-- ============================================================ -->
        <section id="session" class="vc-sec vc-band" style="scroll-margin-top: 4rem;">
            <div class="vc-band-glow" aria-hidden="true"></div>
            <div class="vc-wrap" style="position: relative;">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>04</b><i></i>In session</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            One link. <span class="vc-holo-text">Wherever you stream.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Mark the day as an online event and paste the URL. Event Schedule stores a link, not an integration, so it has no opinion about where the conference actually happens.
                    </p>
                </header>

                <div class="vc-onelink" aria-hidden="true" data-reveal>
                    <div class="vc-onelink-parts">
                        @foreach ($run as [$partTime, $partName, $partMins, $partIsGap])
                            <span class="@if ($partIsGap) is-gap @endif" style="flex-grow: {{ $partMins }};"><i>{{ $partTime }}</i></span>
                        @endforeach
                    </div>
                    <div class="vc-onelink-brace"></div>
                    <div class="vc-onelink-url">
                        <small>Event URL</small>
                        <span>https://stream.example/cloud-summit/day-1</span>
                        <b>Join</b>
                    </div>
                </div>

                <div class="vc-cells" data-reveal>
                    <article>
                        <div class="vc-cell-art" aria-hidden="true"><span>Zoom</span><span>Teams</span><span>Meet</span><span>YouTube Live</span></div>
                        <div class="vc-titled">
                            <h3 class="vc-h3">Any platform</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>Zoom, Microsoft Teams, Google Meet, YouTube Live, a webinar tool, your own player. Anything that hands you a URL works, because the URL is the whole integration.</p>
                    </article>
                    <article>
                        <div class="vc-cell-art" aria-hidden="true"><span class="is-ics">day-1.ics</span><span>Google</span><span>Outlook</span><span>CalDAV</span></div>
                        <div class="vc-titled">
                            <h3 class="vc-h3">It lands in their calendar</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>Attendees download an .ics for the day, or subscribe to your schedule's live calendar feed, which updates itself when a date moves. Your own side syncs two ways with Google, Outlook and CalDAV.</p>
                    </article>
                    <article>
                        <div class="vc-cell-art" aria-hidden="true"><span>Day 1 &middot; 118 left</span><span class="is-full">Day 2 &middot; full</span><span>Day 3 &middot; 64 left</span></div>
                        <div class="vc-titled">
                            <h3 class="vc-h3">Cap the room</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>Free registration with a capacity limit, and the number of places left is counted for each date separately, so day two filling up says nothing about day three.</p>
                    </article>
                </div>

                <p class="vc-band-foot" data-reveal>
                    One join link belongs to one event, so it covers the whole running order.
                    <a href="{{ marketing_url('/features/online-events') }}">
                        How online events work
                        {!! $vcArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 05 The takings: registration and ticket types. Deliberately
             NOT called "at the door": there is no door on a virtual
             conference.                                                   -->
        <!-- ============================================================ -->
        <section id="tickets" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap vc-take">
                <div>
                    <p class="vc-kick" data-reveal><b>05</b><i></i>The takings</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Name your prices. <span class="vc-holo-text">Keep the money.</span>
                    </h2>
                    <p class="vc-take-lede" data-reveal style="--reveal-delay: 0.15s;">
                        A free conference needs nothing but registration and a capacity, and that is on the free plan, however many people come. Charging for one is Pro at {{ plan_price($proMonthly) }}: named ticket types, each with its own price, quantity and sales window, plus individual tickets, discount codes and add-ons. Event Schedule takes nothing from either.
                    </p>

                    <!-- The run-up, in the order it happens -->
                    <div class="vc-rail" data-reveal>
                        <p class="vc-phase" aria-hidden="true">T minus 6 weeks<span>before sales open</span></p>
                        <ul>
                            <li>Announce before you sell. Switch on the "Notify me" card and, until tickets open, the event page offers "Tell me when tickets go on sale": a visitor leaves an email address and hears the moment sales start, and the event editor shows you how many are waiting.</li>
                        </ul>
                        <p class="vc-phase" aria-hidden="true">T minus 4 weeks<span>on sale</span></p>
                        <ul>
                            <li>Zero platform fees at every plan level. Attendees pay through your own Stripe or PayPal account, or you take payment through Invoice Ninja, a payment link or cash, and the processor's own fee is the only cut anybody takes.</li>
                            <li>Individual tickets give everyone in a company's group booking their own confirmation email and their own QR code, instead of one person holding twelve.</li>
                            <li>Custom questions on the ticket, another Pro one, collect what the conference actually needs: a job title for the badge, an accessibility requirement, which workshop somebody picked.</li>
                            <li>Quantities are counted for each date on its own, and on Pro a waitlist catches the people who arrive after a day has sold out.</li>
                        </ul>
                        <p class="vc-phase" aria-hidden="true">T plus 1 day<span>afterwards</span></p>
                        <ul>
                            <li>Refunds come off the Sales page. A Stripe or PayPal payment goes back through the provider, in full or in part, and a full refund puts the place back on sale.</li>
                        </ul>
                    </div>

                    <p class="vc-take-foot" data-reveal>
                        See the detail on the <a href="{{ marketing_url('/features/ticketing') }}" class="vc-link">ticketing page</a>.
                    </p>
                </div>

                <div class="vc-board" data-reveal="panel">
                    <div class="vc-panel vc-marks">
                        <div class="vc-titled">
                            <h3 class="vc-h3">Ticket types</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <div class="vc-tiers">
                            @foreach ([['Full pass', 'all three days', '$149'], ['Single day', 'any one day', '$59'], ['Early bird', 'sales window closes 31 Jan', '$99'], ['Community rate', 'limited quantity', '$25'], ['Student place', 'limited quantity', '$0']] as [$tierName, $tierScope, $tierPrice])
                                <div>
                                    <b>{{ $tierName }}</b>
                                    <small>{{ $tierScope }}</small>
                                    <span>{{ $tierPrice }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="vc-fee">
                            <span>Platform fee</span>
                            <b class="vc-holo-text">{{ plan_price(0) }}</b>
                        </div>
                        <p class="vc-note">
                            The $0 row runs on the free plan and always will; the priced rows are what Pro is for. A conference with nothing to charge for turns the event over to free registration instead. These are counts, not seats: an online room has nowhere to sit, and the seating plans Enterprise adds are for venues with actual rows in them.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 06 Afterwards                                                -->
        <!-- ============================================================ -->
        <section id="after" class="vc-sec vc-alt" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head vc-head-solo">
                    <p class="vc-kick" data-reveal><b>06</b><i></i>Afterwards</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        When the stream stops.
                    </h2>
                </header>

                <div class="vc-after" data-reveal>
                    <article>
                        <div class="vc-after-no"><span class="vc-tier">Free</span></div>
                        <h3 class="vc-h3">Feedback that knows which session it is about</h3>
                        <p>Attendees add a photo, a video or a comment and pick the part of the running order it belongs to, so the note about the workshop is filed under the workshop rather than under a four hour day. Nothing appears until you approve it.</p>
                        <p>The free plan covers 25 photos per schedule; Pro lifts the cap and lets you download the lot as a zip. Star ratings collected after the event are a Pro feature.</p>
                    </article>
                    <article>
                        <div class="vc-after-no"><span class="vc-tier">Free</span></div>
                        <h3 class="vc-h3">Write to the people who came</h3>
                        <p>A confirmed email subscriber hears about the sessions you add without you doing anything. The newsletter is the one you write when next year's programme is set: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted one per recipient.</p>
                    </article>
                    <article>
                        <div class="vc-after-no"><span class="vc-tier">Free</span></div>
                        <h3 class="vc-h3">What the programme page did</h3>
                        <p>Built-in analytics show page views, the devices people read on, which countries they read from and where the traffic came from, right down to the referrer and the campaign tag. Enough to tell whether the programme page did its job.</p>
                    </article>
                    <article>
                        <div class="vc-after-no"><span class="vc-tier">Free</span></div>
                        <h3 class="vc-h3">On the conference site you already built</h3>
                        <p>Embed the calendar in a page of your own so the programme lives where sponsors and speakers link to it, and switch the schedule to its list layout when a conference reads better as a list than as a month.</p>
                        <p>The ticket form embeds too, on Pro, so people can register without leaving your site.</p>
                    </article>
                    <article>
                        <div class="vc-after-no"><span class="vc-tier">Free</span></div>
                        <h3 class="vc-h3">Announce when you are ready</h3>
                        <p>A day you have not announced sits on your calendar as a draft and never appears publicly until you say so. Internal and unlisted visibility, including a password on the link, are Enterprise.</p>
                    </article>
                    <article>
                        <div class="vc-after-no"><span class="vc-tier">Free</span></div>
                        <h3 class="vc-h3">Next year, from this year</h3>
                        <p>Clone a day and you get its running order with it, which is most of the work of the next edition already done. On Pro you can also save a day as a reusable template. On any plan you can generate a share graphic that lays your next dates out from their flyer images, with the date printed on each if you switch that on.</p>
                        <p>A programme committee that needs more than one login is on Enterprise, which allows up to five team members. The free plan is a single member, so plan the handover if a colleague has to post the schedule.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 07 Perfect for: six badges on one rail                       -->
        <!-- ============================================================ -->
        <section id="who" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>07</b><i></i>Perfect for</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Every kind of <span class="vc-holo-text">virtual conference</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        A tech summit or an annual meeting, a half day or a whole week. Also see Event Schedule for <a href="{{ marketing_url('/for-webinars') }}" class="vc-link">webinars</a>.
                    </p>
                </header>

                @php
                    $vcWho = [
                        ['Tech Companies', 'Product launches, developer conferences, hackathons. One event per day, the talks inside it, and a link that works wherever you stream.', 'for-tech-company-conferences', '-1.6deg'],
                        ['Professional Associations', 'Annual meetings, certification events, member summits. Publish the running order with times so members can plan the day around one session.', 'for-professional-association-conferences', '1.1deg'],
                        ['Nonprofits & NGOs', 'Fundraising events, awareness conferences, volunteer summits. Reach supporters anywhere, and Event Schedule takes no cut of what the tickets raise.', 'for-nonprofit-conferences', '-0.7deg'],
                        ['Corporate Teams', 'All-hands meetings, training summits, leadership offsites. One link your whole team follows, with the agenda for the day printed on it.', 'for-corporate-team-conferences', '1.4deg'],
                        ['Academic Institutions', 'Research symposiums, faculty conferences, student events. Every paper gets its own slot, with an abstract underneath it.', 'for-academic-conferences', '-1.2deg'],
                        ['Industry Groups', 'Trade shows, networking events, expert panels. Build a following, then write to them when the next programme is set.', 'for-industry-group-conferences', '0.8deg'],
                    ];
                @endphp

                <div class="vc-wall" data-reveal-group="80">
                    @foreach ($vcWho as $vcWhoIndex => [$vcWhoName, $vcWhoText, $vcWhoSlug, $vcWhoTilt])
                        @php $vcWhoPost = get_sub_audience_blog($vcWhoSlug); @endphp
                        <div class="vc-mini-wrap" data-reveal>
                            <article class="vc-mini" style="--r: {{ $vcWhoTilt }};">
                                <span class="vc-mini-slot" aria-hidden="true"></span>
                                <div class="vc-foil vc-mini-foil" style="--vc-tx: {{ ($vcWhoIndex % 3 - 1) * 0.7 }}; --vc-ty: {{ $vcWhoIndex < 3 ? -0.4 : 0.5 }};" aria-hidden="true"><span>Organizer</span><span>No. {{ str_pad($vcWhoIndex + 1, 2, '0', STR_PAD_LEFT) }}</span></div>
                                <div class="vc-mini-body">
                                    <h3>{{ $vcWhoName }}</h3>
                                    <p>{{ $vcWhoText }}</p>
                                    @if ($vcWhoPost)
                                        <a href="{{ blog_url('/' . $vcWhoPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $vcWhoName }}">
                                            <span>Learn more {!! $vcArrow !!}</span>
                                        </a>
                                    @endif
                                </div>
                                <div class="vc-mini-bars" aria-hidden="true"></div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 08 Three steps                                               -->
        <!-- ============================================================ -->
        <section class="vc-sec vc-alt">
            <div class="vc-wrap">
                <header class="vc-head vc-head-solo">
                    <p class="vc-kick" data-reveal><b>08</b><i></i>Three steps</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        From blank to published.
                    </h2>
                </header>

                <ol class="vc-steps" data-reveal>
                    @foreach ([
                        ['01', 'Create the day', 'One event per conference day: its date, its start time, how long it runs, and the link people join.'],
                        ['02', 'Type the running order', 'Add each session as a part with a name, a start and an end. Move the parts into order, and write an abstract where one helps.'],
                        ['03', 'Open the doors', 'Free registration with a capacity limit on any plan, or named ticket types with prices on Pro. Share one link for the whole programme.'],
                    ] as [$stepNum, $stepTitle, $stepBody])
                        <li>
                            <span class="vc-step-no" aria-hidden="true">{{ $stepNum }}</span>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 09 Key features                                              -->
        <!-- ============================================================ -->
        <section class="vc-sec">
            <div class="vc-wrap vc-kf">
                <div>
                    <p class="vc-kick" data-reveal><b>09</b><i></i>Key features</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s; font-size: clamp(2.1rem, 4.2vw, 3.4rem);">What a conference actually leans on</h2>
                    <p class="vc-kf-more" data-reveal style="--reveal-delay: 0.15s;">
                        <a href="{{ marketing_url('/features') }}" class="vc-more">
                            See all features
                            {!! $vcArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $vcFeatures = [
                        ['Online Events', 'One join link per event, on any platform that gives you a URL', marketing_url('/features/online-events')],
                        ['Ticketing', 'Named ticket types, QR codes, and zero platform fees', marketing_url('/features/ticketing')],
                        ['Analytics', 'Track page views, devices, and traffic sources', marketing_url('/features/analytics')],
                        ['Newsletters', 'Write to the people who follow your schedule', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <div class="vc-kf-list" data-reveal-group="70">
                    @foreach ($vcFeatures as $vcFeatureIndex => [$vcFeatureName, $vcFeatureText, $vcFeatureUrl])
                        <a href="{{ $vcFeatureUrl }}" class="vc-kf-row" data-reveal>
                            <span aria-hidden="true">F.{{ str_pad($vcFeatureIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span>
                                <strong>{{ $vcFeatureName }}</strong>
                                <small>{{ $vcFeatureText }}</small>
                            </span>
                            {!! $vcArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="vc-nudge">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- Related pages                                                -->
        <!-- ============================================================ -->
        <section class="vc-sec vc-alt">
            <div class="vc-wrap">
                <div class="vc-also-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>10</b><i></i>Also online</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s; font-size: clamp(2.1rem, 4.2vw, 3.4rem);">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="vc-more" data-reveal>
                        See all use cases
                        {!! $vcArrow !!}
                    </a>
                </div>
                <div class="vc-also" data-reveal-group="70">
                    @foreach ([['/for-webinars', 'Webinars'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal>
                            <strong>For {{ $relName }}</strong>
                            <small>
                                Read more
                                {!! $vcArrow !!}
                            </small>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11 Questions                                                 -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap vc-faq">
                <div class="vc-faq-head">
                    <p class="vc-kick" data-reveal><b>11</b><i></i>Questions</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        What conference organizers ask before they move a programme across.
                    </p>
                </div>

                <div class="vc-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
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
        </section>

        <!-- ============================================================ -->
        <!-- 12 Close: your name on the badge                             -->
        <!-- ============================================================ -->
        <section id="claim" class="vc-sec vc-band" style="scroll-margin-top: 4rem;">
            <div class="vc-band-glow" aria-hidden="true"></div>
            <div class="vc-wrap vc-close" style="position: relative;">
                <div>
                    <p class="vc-kick" data-reveal><b>12</b><i></i>Close</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        One event. <span class="vc-holo-text">The whole day inside it.</span>
                    </h2>
                    <p class="vc-close-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Publishing the running order, the join link and the calendar sync is free forever, and so is registration. Charging for a seat is {{ plan_price($proMonthly) }} a month. Event Schedule takes nothing out of what you sell either way.
                    </p>

                    <div class="vc-close-form" data-reveal style="--reveal-delay: 0.22s;">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim vc-claim">
                            <input id="es-claim-input" type="text" placeholder="your-summit" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="vc-btn vc-btn-holo">
                            Get Started Free
                            {!! $vcArrow !!}
                        </a>
                    </div>

                    <p class="vc-close-note" data-reveal>No credit card required</p>
                </div>

                <!-- The second badge takes whatever name is typed -->
                <div class="vc-pass-rig" aria-hidden="true" data-reveal="zoom" id="vc-pass">
                    <span class="vc-strap"><i>{{ str_repeat('Event Schedule · ', 6) }}</i></span>
                    <span class="vc-strap"><i>{{ str_repeat('Event Schedule · ', 6) }}</i></span>
                    <span class="vc-clip"></span>
                    <div class="vc-pass">
                        <span class="vc-badge-slot"></span>
                        <div class="vc-foil vc-pass-head" style="--vc-tx: -0.4; --vc-ty: 0.3;"><span>Organizer pass</span><span>2026</span></div>
                        <div class="vc-pass-body">
                            <span class="vc-pass-k">Schedule</span>
                            <span class="vc-pass-name" id="vc-pass-name">your-summit</span>
                            <span class="vc-pass-host">.eventschedule.com</span>
                        </div>
                        <div class="vc-pass-foot">
                            <span class="vc-qr" style="--qr: 3.6rem;"><i style="box-shadow: {{ $vcCode('your-summit') }};"></i></span>
                            <div>
                                <b>Organizer</b>
                                <small>No. 0001</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="vc-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- Three small things the page cannot do in CSS: the hero badge turns to face the pointer,
         the finale badge takes the name typed beside it, and the finale throws foil-coloured
         confetti once. With no script the badges rest at their own angle and the name reads
         your-summit. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

            var hero = document.querySelector('#vc .vc-hero');
            var badge = document.getElementById('vc-badge');
            if (hero && badge && fine && !still) {
                var tx = 0.65, ty = -0.3, queued = false;
                var apply = function () {
                    queued = false;
                    badge.style.setProperty('--vc-tx', tx.toFixed(3));
                    badge.style.setProperty('--vc-ty', ty.toFixed(3));
                };
                var aim = function (x, y) {
                    tx = Math.max(-1, Math.min(1, x));
                    ty = Math.max(-1, Math.min(1, y));
                    if (!queued) {
                        queued = true;
                        requestAnimationFrame(apply);
                    }
                };
                hero.addEventListener('pointermove', function (event) {
                    var box = badge.getBoundingClientRect();
                    aim((event.clientX - (box.left + box.width / 2)) / (window.innerWidth * 0.6), (event.clientY - (box.top + box.height / 2)) / (window.innerHeight * 0.6));
                });
                hero.addEventListener('pointerleave', function () {
                    aim(0.65, -0.3);
                });
            }

            var input = document.getElementById('es-claim-input');
            var name = document.getElementById('vc-pass-name');
            if (input && name) {
                input.addEventListener('input', function () {
                    requestAnimationFrame(function () {
                        var typed = input.value.replace(/-+$/, '') || 'your-summit';
                        name.textContent = typed;
                        name.style.setProperty('--len', Math.max(11, typed.length));
                    });
                });
            }

            var pass = document.getElementById('vc-pass');
            if (pass && !still && 'IntersectionObserver' in window) {
                var seen = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                            return;
                        }
                        seen.disconnect();
                        var foil = ['#22d3ee', '#4ade80', '#fde047', '#fb923c', '#f4f3ee'];
                        {{-- The library's default cannon draws from a blob worker, which the
                             site's content policy refuses without throwing, so nothing was ever
                             drawn. One made here draws on the page instead. --}}
                        var fire = typeof window.confetti.create === 'function' ? window.confetti.create(null, { resize: true }) : window.confetti;
                        [[60, 0.05], [120, 0.95]].forEach(function (shot) {
                            fire({ particleCount: 70, angle: shot[0], spread: 58, startVelocity: 52, origin: { x: shot[1], y: 0.95 }, colors: foil, disableForReducedMotion: true });
                        });
                    });
                }, { threshold: 0.6 });
                seen.observe(pass);
            }
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
