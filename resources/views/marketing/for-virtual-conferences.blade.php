<x-marketing-layout>
    <x-slot name="title">Virtual Conference Agenda: How to Build and Publish One</x-slot>
    <x-slot name="description">How to build a virtual conference agenda: timed sessions, tracks, speakers, time zones and the join link, published free with Event Schedule.</x-slot>
    <x-slot name="breadcrumbTitle">For Virtual Conferences</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typeface, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Space Grotesk') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Virtual Conference Agenda"
        description="How to build and publish a virtual conference agenda: sessions with start and end times, tracks as sub-schedules, speakers, time zones, calendar links and the join link delivered with registration."
        audience="Virtual Conference Organizers"
        keywords="virtual conference agenda, virtual event agenda, virtual conference schedule, online conference agenda, conference agenda software" />
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to build and publish a virtual conference agenda with Event Schedule",
        "description": "Each conference day is an event, and each session is a timed part of its agenda.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Create the day",
                "text": "Create one event per conference day, tick Online and paste the link people join. Tick In-person as well for a hybrid day."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Add the sessions",
                "text": "On the Agenda tab, add each session as a part with a name, a start time, an end time and an optional description, then put the parts in order."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "File the tracks",
                "text": "For parallel tracks or rooms, make one event per track on the same date and file each under its own sub-schedule."
            },
            {
                "@type": "HowToStep",
                "position": 4,
                "name": "Open registration",
                "text": "Turn on free registration with a capacity limit, or sell priced ticket types on Pro. Everyone who registers gets the full join link on their ticket page."
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

           A track is an event of its own filed under a sub-schedule, so
           the agenda's columns are TRACKS on one date, each event's parts
           placed on a shared time grid by named lines (t0900 ... t1600).
           The track chips work with :has() and no script, and a container
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
        .vc-tier-pro { background: var(--vc-ink); border-color: var(--vc-ink); color: var(--vc-bg); }
        .vc-tier-pro::before { background: #fb923c; }
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

        /* ---------------------------------------------------------------
           01 What goes on an agenda
           --------------------------------------------------------------- */
        .vc-duo { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
        @media (min-width: 900px) { .vc-duo { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 3.5rem; } }
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

        /* ---------------------------------------------------------------
           02 Building it: a part, opened
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
           03 Tracks: three events on one date, on one time grid
           --------------------------------------------------------------- */
        .vc-day-1 { --s: #22d3ee; --sd: var(--vc-c1); }
        .vc-day-2 { --s: #fb923c; --sd: var(--vc-c4); }
        .vc-day-3 { --s: #4ade80; --sd: var(--vc-c2); }

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
           04 Joining: one link per event (a band that stays dark)
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
           07 Registration or tickets: the rail, and the board
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
           08 Read more
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
           09 Questions
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
           10 Close: your name on the badge
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
            .vc-btn-holo::before { animation: none; }
            .vc-badge,
            .vc-grid,
            .vc-btn,
            .vc-kf-row { transition: none; }
        }
    </style>

    @php
        // ------------------------------------------------------------------
        // The main stage's running order, printed on the hero badge. The product
        // shape: ONE Event, plus ordered EventPart rows carrying name /
        // description / start_time / end_time. A block's height IS its duration,
        // so 0.085rem per minute turns the list into a proportional timetable.
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

        // Three parallel tracks on one date. Each track is its own event, filed under
        // a sub-schedule, with its own running order and its own join link. Offsets
        // are minutes from 09:00, and the third value marks a break, which is a part
        // like any other.
        $vcTracks = [
            ['Main stage', 'main-stage', '09:00 to 13:00', 7, [[0, 25, false], [25, 50, false], [75, 15, true], [90, 45, false], [135, 60, false], [195, 15, true], [210, 30, false]]],
            ['Workshops', 'workshops', '10:00 to 16:00', 9, [[60, 30, false], [90, 45, false], [135, 15, true], [150, 60, false], [210, 45, false], [255, 45, false], [300, 15, true], [315, 60, false], [375, 45, false]]],
            ['Community', 'community', '09:30 to 13:00', 5, [[30, 45, false], [75, 15, true], [90, 60, false], [150, 45, false], [195, 45, false]]],
        ];

        $faqs = [
            [
                'q' => 'What should a virtual conference agenda include?',
                'a' => 'For every session: a title that says what it is, the speaker, a start and an end time, and a short description for anything longer than a short talk. For the day: its date, the time zone beside every time, the breaks, and how people join. Publish it as a web page rather than a PDF, so it reads on a phone and can be corrected when a slot moves.',
            ],
            [
                'q' => 'How long should a virtual conference day be?',
                'a' => 'Three to five hours of live programme suits most online audiences, with a 10 to 15 minute break every 60 to 90 minutes and five minutes between speakers. If the content needs more time, add a day rather than stretch one.',
            ],
            [
                'q' => 'How do I schedule a virtual conference across time zones?',
                'a' => 'Pick the two regions most of your attendees are in and find the four hours they share: 13:00 to 17:00 UTC suits the Americas and Europe, 08:00 to 12:00 UTC suits Europe and Asia-Pacific, and 22:00 to 02:00 UTC suits the Americas and Asia-Pacific. In Event Schedule, set the schedule\'s timezone before you add events: the event page names the zone beside the start, and calendar entries land at each attendee\'s own local hour.',
            ],
            [
                'q' => 'How do attendees get the link to join?',
                'a' => 'They register or buy a ticket. The public event page shows only the domain of the join link, such as zoom.us. Everyone who registers gets a ticket page with the full link, and the reminder email before the start carries it too when reminders are on. If the link changes, saving the event asks whether to email the people registered.',
            ],
            [
                'q' => 'Can I run parallel tracks or rooms?',
                'a' => 'Yes. Make each track its own event on the same date, with its own agenda, join link and registration limit, and file each track\'s events under a sub-schedule, which has a color and an address of its own and can filter the calendar. It sorts events; it does not hide them.',
            ],
            [
                'q' => 'Does each session get its own join link?',
                'a' => 'A link belongs to an event. If a day is one event with its sessions as agenda parts, the whole day shares one link, which suits a single stream. If sessions need separate links, separate registration or separate calendar entries, make each session an event of its own.',
            ],
            [
                'q' => 'Can a hybrid conference use the same agenda?',
                'a' => 'Yes. Tick both In-person and Online on the event: the page shows the venue and the online option together, and the agenda is the same for both audiences.',
            ],
            [
                'q' => 'Is the agenda free?',
                'a' => 'Yes. Adding parts, giving them names, times and descriptions, reordering them and publishing them are free on every plan, along with the join link, free registration and calendar links. Importing an existing agenda from pasted text or a photo with AI is on the Enterprise plan. Selling priced tickets is Pro.',
            ],
        ];

        // The worked half day: 205 minutes of programme and 35 of breaks.
        $vcSample = [
            ['13:00', 'Welcome, and how today works', 10, false],
            ['13:10', 'Opening keynote', 35, false],
            ['13:45', 'Break', 10, true],
            ['13:55', 'Talk: what we measured', 25, false],
            ['14:20', 'Talk: what we changed', 25, false],
            ['14:45', 'Break', 15, true],
            ['15:00', 'Panel, four speakers', 40, false],
            ['15:40', 'Break', 10, true],
            ['15:50', 'Workshop: hands on', 45, false],
            ['16:35', 'Lightning talks', 15, false],
            ['16:50', 'Close, and what comes next', 10, false],
        ];

        // Three four-hour windows on Tue 3 Mar 2026. Offsets that day: Los Angeles UTC-8,
        // New York UTC-5, London UTC+0, Berlin UTC+1, Bengaluru UTC+5:30, Singapore UTC+8,
        // Tokyo UTC+9, Sydney UTC+11 (daylight time until 5 April).
        $vcWindows = [
            ['Americas and Europe', '13:00 to 17:00 UTC', 'The west coast starts early and Asia-Pacific is asleep.', [['New York', '08:00 to 12:00'], ['London', '13:00 to 17:00'], ['Berlin', '14:00 to 18:00'], ['Los Angeles', '05:00 to 09:00']]],
            ['Europe and Asia-Pacific', '08:00 to 12:00 UTC', 'Morning in Europe, afternoon and evening in Asia and Australia.', [['London', '08:00 to 12:00'], ['Bengaluru', '13:30 to 17:30'], ['Singapore', '16:00 to 20:00'], ['Sydney', '19:00 to 23:00']]],
            ['Americas and Asia-Pacific', '22:00 to 02:00 UTC', 'An American afternoon is the next morning in Tokyo and Sydney.', [['Los Angeles', '14:00 to 18:00'], ['New York', '17:00 to 21:00'], ['Tokyo', '07:00 to 11:00, Wed'], ['Sydney', '09:00 to 13:00, Wed']]],
        ];

        $dotSections = [
            ['top', 'The agenda'],
            ['what', 'What goes on it'],
            ['build', 'Building it'],
            ['tracks', 'Tracks'],
            ['join', 'Joining'],
            ['zones', 'Time zones'],
            ['changes', 'Changes'],
            ['tickets', 'Registration'],
            ['faq', 'Questions'],
            ['claim', 'Start'],
        ];
    @endphp

    @php
        // ------------------------------------------------------------------
        // Derived views of the data above, and set dressing for the agenda.
        // ------------------------------------------------------------------
        $vcArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $vcDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';

        // Session names for each track. The main stage's are the badge's own.
        $vcNames = [
            array_column($run, 1),
            ['Workshops open: setup and accounts', 'Workshop: tracing a slow request', 'Break', 'Workshop: shipping without a freeze', 'Open floor: ask the maintainers', 'Workshop: budgets, alerts and sleep', 'Break', 'Clinic: bring your own dashboard', 'Show and tell'],
            ['Community welcome', 'Break', 'Panel: the next five years of on-call', 'Community lightning round', 'Meet the maintainers'],
        ];
        // A part can carry a description. A few sample ones, keyed track-part.
        $vcAbstracts = [
            '0-1' => 'Where the platform goes next, and what it cost to get here.',
            '0-4' => 'Bring a laptop. We add three traces to a real service.',
            '2-2' => 'Four people who carry the pager, and one who never has.',
        ];
        $vcKeys = ['0-1', '2-2'];

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

        {{-- Hero: the answer first, and the badge on its lanyard --}}
        <section class="vc-hero" id="top">
            <div class="vc-hero-dots" aria-hidden="true"></div>
            <div class="vc-hero-glow" aria-hidden="true"></div>

            <div class="vc-wrap vc-hero-grid">
                <div class="vc-hero-copy">
                    <h1 class="vc-h1">
                        <x-marketing.hero-eyebrow class="vc-eyebrow es-fade-up es-d-1">
                            How to build a virtual conference agenda
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Every session on the clock.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="vc-holo-text">join link</span> with the ticket.</span></span>
                    </h1>

                    <p class="vc-lede es-fade-up es-d-2">
                        A virtual conference agenda gives every session a title, a speaker, a start and an end time in a named time zone, and tells people how to join. Publish it as a page, not a PDF, so it reads on a phone and stays right when a slot moves. In Event Schedule each conference day is an event and each session a timed part of its agenda, free on every plan.
                    </p>

                    <div class="vc-cta es-fade-up es-d-3">
                        <a href="#what" class="vc-btn vc-btn-ghost">
                            What goes on the agenda
                            {!! $vcDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="vc-btn vc-btn-holo">
                            Create your conference schedule
                            {!! $vcArrow !!}
                        </a>
                    </div>
                </div>

                {{-- The badge. The main stage's running order is printed on it: one event, its parts inside. --}}
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
                                    <small>Main stage</small>
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
        </section>

        {{-- 01 What goes on a virtual conference agenda, in any tool --}}
        <section id="what" class="vc-sec vc-alt" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>01</b><i></i>What goes on a virtual conference agenda</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Shorter days, <span class="vc-holo-text">named breaks.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        An online programme is not a venue programme moved to a screen: every changeover happens live, and half the audience has their own work open beside the stream. Whatever tool you use, an agenda people can follow has five parts.
                    </p>
                </header>

                <div class="vc-set">
                    <article class="vc-panel vc-marks" data-reveal="panel">
                        <h3 class="vc-h3">A four-hour day that works</h3>
                        <p class="vc-note" style="margin-top: 0.6rem;">13:00 to 17:00 UTC, one track, eleven parts.</p>
                        <ol class="vc-read">
                            @foreach ($vcSample as [$sampleTime, $sampleName, $sampleMins, $sampleIsGap])
                                <li class="@if ($sampleIsGap) is-gap @endif">
                                    <time>{{ $sampleTime }}</time>
                                    <span>{{ $sampleName }}</span>
                                    <small>{{ $sampleMins }}m</small>
                                </li>
                            @endforeach
                        </ol>
                        <p class="vc-note">240 minutes: 205 of programme and 35 of breaks. The longest run without a pause is 70 minutes, and each talk is booked 5 minutes short of its slot, which is the changeover.</p>
                    </article>

                    <div class="vc-three" data-reveal-group="100">
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #22d3ee;" aria-hidden="true">1</span>
                            <h3 class="vc-h3">A title that says what it is</h3>
                            <p>"Panel: shipping in the open" tells people more than "Session 3". Put the speaker in the title or the description, and add a sentence or two of abstract for anything longer than a short talk.</p>
                        </article>
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #4ade80;" aria-hidden="true">2</span>
                            <h3 class="vc-h3">A start and an end</h3>
                            <p>Print both, so somebody can plan an afternoon around the one session they came for. Keynotes of 30 to 45 minutes, talks of 20 to 30 and workshops of 45 to 90 suit a screen better than venue-length slots.</p>
                        </article>
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #fde047;" aria-hidden="true">3</span>
                            <h3 class="vc-h3">The time zone beside every time</h3>
                            <p>"Starts at 9" means nothing to a global audience. Name the zone, and give people a calendar entry that lands at their own local hour.</p>
                        </article>
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #fb923c;" aria-hidden="true">4</span>
                            <h3 class="vc-h3">Breaks, on the agenda</h3>
                            <p>Ten to fifteen minutes every 60 to 90 minutes, named so people trust they exist. Leave five minutes in each slot for the changeover, or one overrun makes everything after it late.</p>
                        </article>
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #22d3ee;" aria-hidden="true">5</span>
                            <h3 class="vc-h3">How to join</h3>
                            <p>Say where the stream is and how people get the link. Handing it out with registration, rather than posting it, tells you how many to expect and keeps it off the open web.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        {{-- 02 Building it in Event Schedule: a part, opened --}}
        <section id="build" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>02</b><i></i>Building it in Event Schedule</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            One event per day. <span class="vc-holo-text">Sessions inside it.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Create the conference day as an event and tick Online (tick In-person too for a hybrid day, and the venue sits beside the link). Then open its Agenda tab: each session is a part.
                    </p>
                </header>

                <div class="vc-set">
                    {{-- One part of the agenda, open in the editor --}}
                    <div class="vc-editor" aria-hidden="true" data-reveal>
                        <div class="vc-editor-bar"><span>Agenda &middot; Cloud Summit, day 1</span><span class="vc-sw">Show times</span></div>
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

                    <div class="vc-three" data-reveal-group="100">
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #22d3ee;" aria-hidden="true">1</span>
                            <div class="vc-titled">
                                <h3 class="vc-h3">A name, and a description if it helps</h3>
                                <span class="vc-tier">Free</span>
                            </div>
                            <p>The name is the only thing a part needs. "+ Add Description" opens a box for the abstract or the speaker's line, written in Markdown.</p>
                        </article>
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #4ade80;" aria-hidden="true">2</span>
                            <div class="vc-titled">
                                <h3 class="vc-h3">A start and an end</h3>
                                <span class="vc-tier">Free</span>
                            </div>
                            <p>Each part takes its own start and end time, both optional. The Show times switch hides them while the programme is still only titles, and the choice is remembered for the schedule's next event.</p>
                        </article>
                        <article data-reveal>
                            <span class="vc-pin" style="--pin: #fb923c;" aria-hidden="true">3</span>
                            <div class="vc-titled">
                                <h3 class="vc-h3">An order you can change</h3>
                                <span class="vc-tier">Free</span>
                            </div>
                            <p>Drag a part by its handle or use its up and down arrows. The order on the form is the order on the event page, which draws the agenda as a timeline with each part's times.</p>
                        </article>
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
                            <h3 class="vc-h3">Already have the programme in a document</h3>
                            <span class="vc-tier vc-tier-ent">Enterprise</span>
                        </div>
                        <p>
                            Paste the agenda as text, or choose a photo of a printed one, and AI import fills the parts in, times and all. That is on the Enterprise plan, with a daily limit. Typing the parts yourself is free on every plan. See the <x-link href="{{ marketing_url('/docs/creating-events') }}#agenda">agenda guide</x-link>.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 03 Tracks and rooms: three events on one date, one sub-schedule each --}}
        <section id="tracks" class="vc-sec vc-alt" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>03</b><i></i>Tracks, rooms and speakers</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            A track is <span class="vc-holo-text">a sub-schedule.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Parallel sessions are separate events on the same date, one per track or room, each with its own agenda, join link and registration count. File each track's events under a sub-schedule and visitors can read one track or all of them.
                    </p>
                </header>

                <div class="vc-panel vc-marks" data-reveal="panel">
                    <div class="vc-ag" style="margin-top: 0; padding-top: 0; border-top: 0;">
                        <fieldset class="vc-filter">
                            <legend class="sr-only">Show tracks</legend>
                            <span class="vc-filter-k" aria-hidden="true">Tue 3 Mar</span>
                            <input type="radio" name="vc-day" id="vc-f0" class="vc-f" checked>
                            <label for="vc-f0">All tracks</label>
                            @foreach ($vcTracks as $trackIndex => [$trackName])
                                <input type="radio" name="vc-day" id="vc-f{{ $trackIndex + 1 }}" class="vc-f">
                                <label for="vc-f{{ $trackIndex + 1 }}" class="vc-day-{{ $trackIndex + 1 }}"><i aria-hidden="true"></i>{{ $trackName }}</label>
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

                            @foreach ($vcTracks as $trackIndex => [$trackName, $trackSlug, $trackHours, $trackParts, $trackTicks])
                                <section class="vc-day vc-day-{{ $trackIndex + 1 }}" aria-labelledby="vc-day-{{ $trackIndex + 1 }}-h">
                                    <label for="vc-f{{ $trackIndex + 1 }}" class="vc-day-pick" aria-hidden="true"></label>
                                    <header class="vc-day-head">
                                        <span class="vc-day-bar" aria-hidden="true"></span>
                                        <h3 id="vc-day-{{ $trackIndex + 1 }}-h">{{ $trackName }}</h3>
                                        <p class="vc-day-date">Tue 3 Mar</p>
                                        <p class="vc-day-meta"><span>/{{ $trackSlug }}</span><span>{{ $trackHours }}</span><span>{{ $trackParts }} parts</span></p>
                                        <span class="vc-day-short" aria-hidden="true">T{{ $trackIndex + 1 }}</span>
                                    </header>
                                    <ol class="vc-day-parts">
                                        @foreach ($trackTicks as $tickIndex => [$tickAt, $tickFor, $tickIsGap])
                                            <li class="vc-part @if ($tickIsGap) is-break @endif @if (in_array($trackIndex.'-'.$tickIndex, $vcKeys, true)) is-key @endif" style="grid-row: {{ $vcLine($tickAt) }} / {{ $vcLine($tickAt + $tickFor) }};">
                                                <span class="vc-part-t">{{ $vcClock($tickAt) }}</span>
                                                @if ($tickIsGap)
                                                    <span class="vc-part-n">{{ $vcNames[$trackIndex][$tickIndex] }}</span>
                                                    <span class="vc-part-d">{{ $tickFor }} min</span>
                                                @else
                                                    <span class="vc-part-d">{{ $tickFor }} min</span>
                                                    <span class="vc-part-n">{{ $vcNames[$trackIndex][$tickIndex] }}</span>
                                                    @isset($vcAbstracts[$trackIndex.'-'.$tickIndex])
                                                        <span class="vc-part-a">{{ $vcAbstracts[$trackIndex.'-'.$tickIndex] }}</span>
                                                    @endisset
                                                @endif
                                            </li>
                                        @endforeach
                                        @php $vcTrackEnd = end($trackTicks)[0] + end($trackTicks)[1]; @endphp
                                        <li class="vc-day-end" aria-hidden="true" style="top: calc(var(--vc-min) * {{ $vcTrackEnd }});"><span>{{ $vcClock($vcTrackEnd) }}</span>end of track</li>
                                    </ol>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="vc-cells" data-reveal>
                    <article>
                        <div class="vc-titled">
                            <h3 class="vc-h3">A sub-schedule per track</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>Create Main stage, Workshops and Community as sub-schedules. Each gets a color and an address of its own, such as /workshops, to hand to the people who only want that track, and visitors can filter the calendar by it. A sub-schedule sorts events; it does not hide them.</p>
                    </article>
                    <article>
                        <div class="vc-titled">
                            <h3 class="vc-h3">Or one event per session</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>When every session needs its own link, its own capacity or its own calendar entry, make each session an event instead of a part. People then register for exactly the sessions they want.</p>
                    </article>
                    <article>
                        <div class="vc-titled">
                            <h3 class="vc-h3">Speakers</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>Name speakers on the event's Participants tab. They appear on the event page, linked to their own schedule if they have one. Participants belong to the event, not to one part, so on a day of many sessions write each speaker into their part as well.</p>
                    </article>
                </div>

                <p class="vc-strand-note" data-reveal>
                    More in the guides to <x-link href="{{ marketing_url('/docs/creating-schedules') }}#customize-subschedules">sub-schedules</x-link> and <x-link href="{{ marketing_url('/docs/creating-events') }}#participants">participants</x-link>.
                </p>
            </div>
        </section>

        {{-- 04 Joining: the link, the zone and the calendar (a band that stays dark) --}}
        <section id="join" class="vc-sec vc-band" style="scroll-margin-top: 4rem;">
            <div class="vc-band-glow" aria-hidden="true"></div>
            <div class="vc-wrap" style="position: relative;">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>04</b><i></i>Join link, time zones, calendars</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            One link per event. <span class="vc-holo-text">Sent with the ticket.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Paste the stream's URL into the event. Event Schedule stores a link, not an integration, so Zoom, Microsoft Teams, Google Meet, YouTube Live or your own player all work.
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
                        <div class="vc-cell-art" aria-hidden="true"><span>zoom.us</span><span>ticket page</span><span>reminder</span></div>
                        <div class="vc-titled">
                            <h3 class="vc-h3">The link comes with registration</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>The public event page shows only the link's domain, such as zoom.us. Everyone who registers or buys gets a ticket page with the full link, and the reminder email carries it too. Reminders go 3 hours or 1, 2 or 3 days ahead, and are on by default on Pro and Enterprise.</p>
                    </article>
                    <article>
                        <div class="vc-cell-art" aria-hidden="true"><span>13:00 UTC</span><span>08:00 New York</span><span>21:00 Singapore</span></div>
                        <div class="vc-titled">
                            <h3 class="vc-h3">The zone, named and converted</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>Set the schedule's timezone before you add events. The event page prints the start with the zone beside it, and the part times are the clock times you typed, in that zone. Calendar entries carry the exact moment, so each attendee sees the day at their own local hour.</p>
                    </article>
                    <article>
                        <div class="vc-cell-art" aria-hidden="true"><span class="is-ics">day-1.ics</span><span>Google</span><span>Outlook</span><span>Feed</span></div>
                        <div class="vc-titled">
                            <h3 class="vc-h3">Add to calendar</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <p>The event page offers Google Calendar, Outlook and an .ics file, and the confirmation email carries a calendar invite. Attendees can also subscribe to your schedule's live calendar feed, which updates itself when a date moves.</p>
                    </article>
                </div>

                <p class="vc-band-foot" data-reveal>
                    A join link belongs to one event, so it covers that event's whole agenda.
                    <a href="{{ marketing_url('/features/online-events') }}">
                        How online events work
                        {!! $vcArrow !!}
                    </a>
                </p>
            </div>
        </section>

        {{-- 05 Time zones: three windows on one date --}}
        <section id="zones" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>05</b><i></i>Scheduling across time zones</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            No hour suits everyone. <span class="vc-holo-text">Pick two regions.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Look at where your registrants are, choose the two regions with the most people, and put the live programme in the four hours they share. These are the three windows that come up most, worked out for Tue 3 Mar 2026.
                    </p>
                </header>

                <div class="vc-cells" style="margin-top: 0;" data-reveal>
                    @foreach ($vcWindows as [$windowName, $windowUtc, $windowFor, $windowCities])
                        <article>
                            <h3 class="vc-h3">{{ $windowName }}</h3>
                            <p class="vc-note" style="margin-top: 0.4rem;">{{ $windowUtc }}</p>
                            <p>{{ $windowFor }}</p>
                            <dl class="vc-fields">
                                @foreach ($windowCities as [$cityName, $cityHours])
                                    <div>
                                        <dt>{{ $cityName }}</dt>
                                        <dd>{{ $cityHours }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </article>
                    @endforeach
                </div>

                <p class="vc-strand-note" data-reveal>
                    Reach the region you left out by repeating the keynote as a second event in its window, rotating the window by day, or sending the recording. In 2026 the United States changes its clocks on 8 March and Europe on 29 March, so check the weeks between. For an audience that reads another language, a schedule can offer a second one, and its events and their agenda items are translated automatically on every plan.
                </p>
            </div>
        </section>

        {{-- 06 When the agenda changes --}}
        <section id="changes" class="vc-sec vc-alt" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap">
                <header class="vc-head">
                    <div>
                        <p class="vc-kick" data-reveal><b>06</b><i></i>When the agenda changes</p>
                        <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Fix it once. <span class="vc-holo-text">Everyone reads the fix.</span>
                        </h2>
                    </div>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        A published agenda page is correct for everyone the moment you save. What is left is telling the people who already registered.
                    </p>
                </header>

                <div class="vc-duo" data-reveal-group="110">
                    <article class="vc-panel vc-marks" data-reveal="panel">
                        <div class="vc-titled">
                            <h3 class="vc-h3">What updates by itself</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <div class="vc-rail">
                            <ul>
                                <li>The event page shows the saved agenda the moment you save, and your schedule's page and the calendar embedded on your conference site lead to it.</li>
                                <li>People subscribed to your calendar feed get a moved date or time without doing anything.</li>
                            </ul>
                        </div>
                    </article>

                    <article class="vc-panel vc-marks" data-reveal="panel">
                        <div class="vc-titled">
                            <h3 class="vc-h3">What you tell people</h3>
                            <span class="vc-tier">Free</span>
                        </div>
                        <div class="vc-rail">
                            <ul>
                                <li>Change a one-time event's date or start, or its join link, and saving asks "Notify attendees of this change?" Add a note, preview the email and send it. The email moves the entry in their calendar too.</li>
                                <li>On eventschedule.com, registered attendees are emailed once your schedule has its own email settings; a selfhosted install only needs a working mailer.</li>
                                <li>Moving a session inside the day does not ask, because the day's own time has not changed. When a headline slot moves, tell people yourself: a newsletter can go to everyone registered for that one event.</li>
                            </ul>
                        </div>
                    </article>
                </div>

                <p class="vc-strand-note" data-reveal>
                    The detail is in <x-link href="{{ marketing_url('/docs/creating-events') }}#notify-attendees">notifying attendees of changes</x-link>.
                </p>
            </div>
        </section>

        {{-- 07 Registration or tickets --}}
        <section id="tickets" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap vc-take">
                <div>
                    <p class="vc-kick" data-reveal><b>07</b><i></i>Registration or tickets</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Free to register. <span class="vc-holo-text">Pro to charge.</span>
                    </h2>
                    <p class="vc-take-lede" data-reveal style="--reveal-delay: 0.15s;">
                        A free conference needs registration with a capacity limit, which is on every plan with no cap on sign-ups. Charging is Pro at {{ plan_price($proMonthly) }} a month: named ticket types with their own prices and quantities, paid into your own Stripe or PayPal account. Event Schedule takes no platform fee on either.
                    </p>

                    <div class="vc-rail" data-reveal>
                        <ul>
                            <li>Places are counted per event, so a full workshop track or a full day does not close the others.</li>
                            <li>On Pro, the cart lets a buyer add several days or tracks and pay once, and each event keeps its own ticket page with its own join link.</li>
                        </ul>
                    </div>

                    <p class="vc-take-foot" data-reveal>
                        See <a href="{{ marketing_url('/features/registration') }}" class="vc-link">free registration</a> and <a href="{{ marketing_url('/features/ticketing') }}" class="vc-link">ticketing</a>.
                    </p>
                </div>

                <div class="vc-board" data-reveal="panel">
                    <div class="vc-panel vc-marks">
                        <h3 class="vc-h3">Two ways in</h3>
                        <div class="vc-tiers">
                            <div>
                                <b>Free registration</b>
                                <small>with a capacity limit</small>
                                <span>Free</span>
                            </div>
                            <div>
                                <b>Priced ticket types</b>
                                <small>own price and quantity</small>
                                <span>Pro</span>
                            </div>
                            <div>
                                <b>Discount codes and add-ons</b>
                                <small>on priced tickets</small>
                                <span>Pro</span>
                            </div>
                        </div>
                        <div class="vc-fee">
                            <span>Platform fee</span>
                            <b class="vc-holo-text">{{ plan_price(0) }}</b>
                        </div>
                        <p class="vc-note">Your payment processor's own fee is the only cut.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 08 Where to read more --}}
        <section class="vc-sec vc-alt">
            <div class="vc-wrap vc-kf">
                <div>
                    <p class="vc-kick" data-reveal><b>08</b><i></i>Read more</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s; font-size: clamp(2.1rem, 4.2vw, 3.4rem);">The details, step by step</h2>
                    <p class="vc-kf-more" data-reveal style="--reveal-delay: 0.15s;">
                        <a href="{{ marketing_url('/features') }}" class="vc-more">
                            See all features
                            {!! $vcArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $vcFeatures = [
                        ['The agenda tab', 'Parts, times, descriptions and order, in the user guide', marketing_url('/docs/creating-events').'#agenda'],
                        ['Online events', 'One join link per event, on any platform that gives you a URL', marketing_url('/features/online-events')],
                        ['Sub-schedules', 'One per track or room, each with a color and an address', marketing_url('/features/sub-schedules')],
                        ['Notifying attendees', 'What is emailed when a date, a time or a link changes', marketing_url('/docs/creating-events').'#notify-attendees'],
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

        {{-- 09 Questions --}}
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="vc-sec" style="scroll-margin-top: 4rem;">
            <div class="vc-wrap vc-faq">
                <div class="vc-faq-head">
                    <p class="vc-kick" data-reveal><b>09</b><i></i>Questions</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="vc-sub" data-reveal style="--reveal-delay: 0.15s;">
                        What organizers ask before they put a programme online.
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

        {{-- 10 Close: your name on the badge --}}
        <section id="claim" class="vc-sec vc-band" style="scroll-margin-top: 4rem;">
            <div class="vc-band-glow" aria-hidden="true"></div>
            <div class="vc-wrap vc-close" style="position: relative;">
                <div>
                    <p class="vc-kick" data-reveal><b>10</b><i></i>Start</p>
                    <h2 class="vc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Publish the agenda. <span class="vc-holo-text">Keep one link.</span>
                    </h2>
                    <p class="vc-close-sub" data-reveal style="--reveal-delay: 0.15s;">
                        The agenda, the join link, free registration and the calendar links are free on every plan. Charging for a place is {{ plan_price($proMonthly) }} a month, and Event Schedule takes nothing from what you sell.
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

                {{-- The second badge takes whatever name is typed --}}
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
