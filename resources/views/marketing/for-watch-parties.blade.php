<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Watch Parties | Movie Nights</x-slot>
    <x-slot name="description">Free, open-source watch party scheduling: one join link for any stream, registration with a cap per date, a running order and zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Watch Parties</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Bebas Neue') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Arimo') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Watch Parties"
        description="Free, open-source watch party scheduling software. Publish the running order, take free registrations against a per-date cap, and hand every registrant a confirmation page carrying the join link. Zero platform fees."
        audience="Watch Party Hosts"
        keywords="watch party platform, schedule watch parties, virtual watch party, online watch party hosting, group streaming events, watch party ticketing, movie night scheduling, free watch party app" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to host a watch party with Event Schedule",
        "description": "Three steps to run a screening night, not just to publish a stream link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Open the doors",
                "text": "Create the event, paste your join link into the one Event URL field, turn on free registration and set the cap for the room you can actually handle."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Post the running order",
                "text": "Add the parts of the night with their times: doors and chat, the introduction, the feature, the discussion afterwards. They publish on the event page with it."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Roll it",
                "text": "Every registrant gets a confirmation email linking to their own page, and the join link is on it. Afterwards, collect photos and comments from the people who were there."
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
           For-watch-parties "Movie Night" styles. The page is a film and
           you watch it: a countdown leader, a title card, then scenes.
           Every scene is a letterboxed frame with the section's heading
           set as its subtitle, a slate above it and the reading copy
           below. Running orders are strips of 35mm drawn to scale, the
           three steps are three consecutive frames, and the page ends
           on a credit roll and a last title card.

           A screen is dark whatever the room is doing, so everything
           inside .wp-screen, .wp-strip, .wp-reel and .wp-credits is
           painted with literal colours. The room around it follows the
           theme: a cream lobby at the matinee, the auditorium at night.

           Nothing here draws a player, a shared timeline or a viewer
           count. The product holds the night, not the stream.
           ============================================================== */

        @property --wp-sweep {
            syntax: '<angle>';
            inherits: false;
            initial-value: 0deg;
        }

        #wp {
            --wp-wall: #f3ecdc;
            --wp-wall-2: #eadfc8;
            --wp-card: #fbf7ec;
            --wp-ink: #17130f;
            --wp-ink-2: #463e33;
            --wp-ink-3: #675d4f;
            --wp-line: rgba(23, 19, 15, 0.18);
            --wp-frame: #17130f;
            --wp-hot: #8a1c1c;
            --wp-on-hot: #fbf7ec;
            --wp-btn: #17130f;
            --wp-btn-ink: #f3ecdc;
            --wp-hole: #f3ecdc;
            --wp-shadow: 0 1.4rem 2.2rem -1.5rem rgba(23, 19, 15, 0.55);
            --wp-display: 'Bebas Neue', 'Oswald', 'Arial Narrow', Impact, sans-serif;
            --wp-text: 'Arimo', Arial, 'Helvetica Neue', Helvetica, sans-serif;
            --wp-mono: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
            --wp-noise: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 0.6 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)'/%3E%3C/svg%3E");
            position: relative;
            background-color: var(--wp-wall);
            background-image: repeating-linear-gradient(90deg, rgba(138, 28, 28, 0.045) 0 1px, transparent 1px 4.5rem);
            color: var(--wp-ink);
            font-family: var(--wp-text);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #wp {
            --wp-wall: #070707;
            --wp-wall-2: #0e0d0c;
            --wp-card: #141312;
            --wp-ink: #f4f1e8;
            --wp-ink-2: #cbc6b8;
            --wp-ink-3: #a09a8c;
            --wp-line: rgba(244, 241, 232, 0.17);
            --wp-frame: rgba(244, 241, 232, 0.24);
            --wp-hot: #ffe14d;
            --wp-on-hot: #070707;
            --wp-btn: #ffe14d;
            --wp-btn-ink: #070707;
            --wp-hole: #070707;
            --wp-shadow: 0 0 0 0 transparent;
            background-image: none;
        }

        /* The bar above takes the colour of the room. */
        body > header.sticky {
            background-color: rgba(243, 236, 220, 0.9);
            border-bottom-color: rgba(23, 19, 15, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(7, 7, 7, 0.88);
            border-bottom-color: rgba(244, 241, 232, 0.14);
        }

        #wp ::selection { background: #ffe14d; color: #0b0b0a; }
        #wp a:focus-visible,
        #wp summary:focus-visible,
        #wp input:focus-visible {
            outline: 3px solid var(--wp-hot);
            outline-offset: 3px;
        }
        #wp .wp-screen a:focus-visible,
        #wp .wp-screen input:focus-visible,
        #wp .wp-credits a:focus-visible,
        #wp .wp-rail a:focus-visible { outline-color: #ffe14d; }

        .wp-wrap { width: min(100% - 2.5rem, 78rem); margin-inline: auto; }
        .wp-scene { padding-block: clamp(3.5rem, 7vw, 6rem); scroll-margin-top: 4.5rem; }
        .wp-alt { background-color: var(--wp-wall-2); }

        /* Type voices */
        .wp-d { font-family: var(--wp-display); font-weight: 400; text-transform: uppercase; letter-spacing: 0.02em; line-height: 0.95; }
        .wp-mono { font-family: var(--wp-mono); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.16em; text-transform: uppercase; line-height: 1.3; }
        .wp-kick { font-family: var(--wp-mono); font-weight: 700; font-size: 0.72rem; letter-spacing: 0.2em; text-transform: uppercase; line-height: 1.3; color: var(--wp-ink-3); }
        .wp-h2 { font-family: var(--wp-display); font-weight: 400; text-transform: uppercase; letter-spacing: 0.02em; line-height: 0.95; font-size: clamp(2.6rem, 6vw, 4.6rem); text-wrap: balance; }
        .wp-hot { color: var(--wp-hot); }
        .wp-nb { white-space: nowrap; }
        .wp-lede { max-width: 46rem; font-size: 1.175rem; color: var(--wp-ink-2); }
        .wp-fine { font-size: 0.95rem; color: var(--wp-ink-3); }
        #wp .wp-body a:not(.wp-btn),
        #wp .wp-faq-head a {
            color: var(--wp-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--wp-hot);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }
        #wp .wp-body a:not(.wp-btn):hover { color: var(--wp-hot); }

        /* Plan tags, cut like a ratings box */
        .wp-tag {
            display: inline-block;
            padding: 0.32rem 0.45rem 0.26rem;
            border: 1.5px solid currentColor;
            border-radius: 3px;
            font-family: var(--wp-mono);
            font-weight: 700;
            font-size: 0.68rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            line-height: 1;
            color: var(--wp-ink-2);
            white-space: nowrap;
        }
        .wp-tag-pro { background: var(--wp-hot); border-color: var(--wp-hot); color: var(--wp-on-hot); }
        .wp-tag-ent { background: var(--wp-ink); border-color: var(--wp-ink); color: var(--wp-wall); }

        /* Buttons only ever sit on a screen, so they are lit, not themed. */
        .wp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.5rem 0.78rem;
            border: 2px solid #ffe14d;
            background: #ffe14d;
            color: #0b0b0a;
            font-family: var(--wp-display);
            /* One weight exists. Left to inherit the bar's bold, the two buttons in the hero were
               drawn in a synthesised bold, which Safari also sets wider. */
            font-weight: 400;
            font-size: 1.35rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1;
            transition: background-color 0.2s ease, color 0.2s ease, translate 0.2s ease;
        }
        .wp-btn:hover { background: #fff3a6; border-color: #fff3a6; translate: 0 -2px; }
        .wp-btn svg { width: 1.15rem; height: 1.15rem; margin-top: -0.15rem; transition: translate 0.2s ease; }
        .wp-btn:hover svg { translate: 4px 0; }
        .wp-btn-ghost { background: transparent; color: #f4f1e8; border-color: rgba(244, 241, 232, 0.55); }
        .wp-btn-ghost:hover { background: #f4f1e8; border-color: #f4f1e8; color: #0b0b0a; }
        .wp-btn-ghost:hover svg { translate: 0 4px; }

        /* ---------------------------------------------------------------
           The slate: a clapperboard reduced to its information
           --------------------------------------------------------------- */
        .wp-slate {
            display: flex;
            align-items: stretch;
            width: fit-content;
            max-width: 100%;
            margin-bottom: 1.1rem;
            border: 2px solid var(--wp-ink);
            background: var(--wp-card);
            font-family: var(--wp-mono);
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1.1;
        }
        .wp-slate-sticks {
            flex: none;
            width: 3.4rem;
            background: repeating-linear-gradient(-55deg, var(--wp-ink) 0 0.55rem, var(--wp-card) 0.55rem 1.1rem);
            border-inline-end: 2px solid var(--wp-ink);
        }
        .wp-slate-cell {
            display: grid;
            align-content: center;
            gap: 0.2rem;
            padding: 0.45rem 0.8rem 0.4rem;
            border-inline-end: 1px solid var(--wp-line);
            color: var(--wp-ink);
            white-space: nowrap;
        }
        .wp-slate-cell:last-child { border-inline-end: 0; min-width: 0; white-space: normal; }
        .wp-slate-cell > i { font-style: normal; font-size: 0.56rem; letter-spacing: 0.2em; color: var(--wp-ink-3); }
        @media (max-width: 560px) { .wp-slate-prod { display: none; } }
        /* Under 390 the longest title ("The running order") is wider than the board. */
        @media (max-width: 389px) {
            .wp-slate-sticks { width: 2.4rem; }
            .wp-slate-cell { padding-inline: 0.55rem; }
        }

        /* ---------------------------------------------------------------
           The screen: two black bars and a picture between them
           --------------------------------------------------------------- */
        .wp-screen {
            position: relative;
            background: #000;
            color: #f4f1e8;
            box-shadow: var(--wp-shadow), 0 0 0 1px #000;
        }
        .dark .wp-screen { box-shadow: 0 0 0 1px rgba(244, 241, 232, 0.13), 0 0 7rem -2rem rgba(244, 241, 232, 0.12); }
        .wp-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            min-height: 1.9rem;
            padding: 0.2rem 1rem 0.1rem;
            font-family: var(--wp-mono);
            font-weight: 700;
            font-size: 0.6rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #8d877a;
            white-space: nowrap;
            overflow: hidden;
        }
        .wp-pic {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: minmax(0, 1fr) auto;
            container-type: inline-size;
            overflow: clip;
            isolation: isolate;
            min-height: 16rem;
            background-color: #0b0b0a;
            --wp-hole: #0b0b0a;
        }
        @media (min-width: 900px) { .wp-pic { aspect-ratio: 2.39 / 1; } }
        .wp-pic::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 3;
            background: radial-gradient(ellipse at 50% 45%, transparent 52%, rgba(0, 0, 0, 0.62) 100%);
            pointer-events: none;
        }
        .wp-pic > * { min-width: 0; }

        /* Subtitles: the section's heading, in the yellow every subtitle is set in. */
        .wp-sub {
            position: relative;
            z-index: 4;
            grid-row: 2;
            grid-column: 1 / -1;
            justify-self: center;
            max-width: min(100%, 58rem);
            padding: 0.5rem 1rem clamp(0.9rem, 2.4cqi, 1.9rem);
            font-family: var(--wp-text);
            font-weight: 700;
            font-size: clamp(1.02rem, 2.45cqi, 1.9rem);
            line-height: 1.26;
            text-align: center;
            text-wrap: balance;
            color: #ffe14d;
            text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000, 0 -2px 0 #000, 0 2px 0 #000, -2px 0 0 #000, 2px 0 0 #000, 0 0 0.6rem rgba(0, 0, 0, 0.85);
        }
        .wp-sub-2 > span { display: block; }
        @media (max-width: 480px) {
            .wp-sub { padding-inline: 0.5rem; font-size: 0.95rem; }
        }

        .wp-grain {
            position: absolute;
            inset: -12%;
            z-index: 2;
            background-image: var(--wp-noise);
            opacity: 0.11;
            pointer-events: none;
        }
        html.es-anim #wp .wp-grain-live { animation: wp-grain 0.48s steps(1) infinite; }
        @keyframes wp-grain {
            0% { translate: 0 0; }
            25% { translate: -4% 3%; }
            50% { translate: 3% -5%; }
            75% { translate: -2% -3%; }
        }
        .wp-scratch {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 31%;
            z-index: 2;
            width: 1px;
            background: linear-gradient(180deg, transparent, rgba(244, 241, 232, 0.6) 22%, rgba(244, 241, 232, 0.3) 70%, transparent);
            opacity: 0;
            pointer-events: none;
        }
        html.es-anim #wp .wp-scratch { animation: wp-scratch 7s steps(1) 2s infinite; }
        @keyframes wp-scratch {
            0% { opacity: 0; left: 31%; }
            6% { opacity: 0.7; left: 31%; }
            7.5% { opacity: 0.35; left: 31.3%; }
            9% { opacity: 0; left: 31%; }
            54% { opacity: 0; left: 68%; }
            55% { opacity: 0.6; left: 68%; }
            57% { opacity: 0; left: 67.7%; }
            100% { opacity: 0; left: 31%; }
        }
        /* Gate weave: the picture never sits quite still in the gate. */
        html.es-anim #wp .wp-weave { animation: wp-weave 2.6s steps(1) infinite; }
        @keyframes wp-weave {
            0% { translate: 0 0; }
            62% { translate: 1px 0; }
            65% { translate: 0 -1px; }
            68% { translate: 0 0; }
            88% { translate: -1px 1px; }
            91% { translate: 0 0; }
        }

        /* ---------------------------------------------------------------
           Scene one: the leader, then the title card
           --------------------------------------------------------------- */
        .wp-hero { padding-block: clamp(1.25rem, 3vw, 2.5rem) 0; }
        .wp-pic-hero { min-height: 24rem; background-image: radial-gradient(ellipse 60% 55% at 50% 38%, rgba(244, 241, 232, 0.085), transparent 70%); }
        /* The card's own border: a hairline all round and a heavier mark in each corner. */
        .wp-cardrule {
            position: absolute;
            inset: clamp(0.6rem, 1.6cqi, 1.25rem);
            z-index: 1;
            border: 1px solid rgba(244, 241, 232, 0.2);
            background:
                linear-gradient(#f4f1e8, #f4f1e8) 0 0 / 1.6rem 2px no-repeat,
                linear-gradient(#f4f1e8, #f4f1e8) 0 0 / 2px 1.6rem no-repeat,
                linear-gradient(#f4f1e8, #f4f1e8) 100% 0 / 1.6rem 2px no-repeat,
                linear-gradient(#f4f1e8, #f4f1e8) 100% 0 / 2px 1.6rem no-repeat,
                linear-gradient(#f4f1e8, #f4f1e8) 0 100% / 1.6rem 2px no-repeat,
                linear-gradient(#f4f1e8, #f4f1e8) 0 100% / 2px 1.6rem no-repeat,
                linear-gradient(#f4f1e8, #f4f1e8) 100% 100% / 1.6rem 2px no-repeat,
                linear-gradient(#f4f1e8, #f4f1e8) 100% 100% / 2px 1.6rem no-repeat;
            opacity: 0.55;
            pointer-events: none;
        }
        /* The cue mark: the dot in the corner that tells the booth the reel is nearly out. */
        .wp-cue {
            position: absolute;
            top: clamp(1.2rem, 3.4cqi, 2.6rem);
            right: clamp(1.2rem, 3.4cqi, 2.6rem);
            z-index: 4;
            width: clamp(1.1rem, 2.6cqi, 1.9rem);
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, transparent 0 34%, #f4f1e8 36% 62%, transparent 64%);
            opacity: 0;
        }
        html:not(.es-anim) #wp .wp-cue { opacity: 0.8; }
        html.es-anim #wp .wp-cue { animation: wp-cue 5.5s steps(1) infinite; }
        @keyframes wp-cue { 0%, 12% { opacity: 0.85; } 13%, 100% { opacity: 0; } }
        .wp-title {
            position: relative;
            z-index: 4;
            grid-row: 1;
            place-self: center;
            width: 100%;
            padding: clamp(1.6rem, 4vw, 3.2rem) clamp(1rem, 3vw, 2.5rem) 0.5rem;
            text-align: center;
        }
        .wp-h1 {
            font-family: var(--wp-display);
            font-weight: 400;
            font-size: clamp(2.9rem, 8.2cqi, 6.9rem);
            letter-spacing: 0.02em;
            line-height: 0.92;
            text-transform: uppercase;
            text-wrap: balance;
            color: #f4f1e8;
        }
        .wp-h1 .wp-y { color: #ffe14d; }
        .wp-eyebrow {
            display: inline-block;
            margin-bottom: clamp(0.8rem, 2cqi, 1.4rem);
            padding: 0.4rem 0.7rem 0.3rem;
            border: 1px solid rgba(244, 241, 232, 0.4);
            font-family: var(--wp-mono);
            font-weight: 700;
            font-size: 0.72rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1.3;
            color: #d9d3c4;
        }
        html.es-anim #wp .wp-h1 .es-mask .es-mask-line { animation-delay: 1.1s; }
        html.es-anim #wp .wp-h1 .es-mask-2 .es-mask-line { animation-delay: 1.24s; }
        html.es-anim #wp .wp-eyebrow.es-fade-up { animation-delay: 1.05s; }
        html.es-anim #wp .wp-sub-lede.es-fade-up { animation-delay: 1.6s; }

        /* The lede: a paragraph on a phone, the film's first subtitle on a wide screen. */
        .wp-sub-lede {
            font-weight: 400;
            font-size: 1.02rem;
            line-height: 1.5;
            color: #e6e1d3;
            text-shadow: none;
            padding-inline: 1.25rem;
            padding-bottom: 1.6rem;
            max-width: 34rem;
        }
        @media (min-width: 900px) {
            .wp-sub-lede {
                max-width: 100%;
                padding-bottom: clamp(1.5rem, 3.2cqi, 2.6rem);
                font-weight: 700;
                font-size: clamp(0.92rem, 1.52cqi, 1.3rem);
                line-height: 1.34;
                color: #ffe14d;
                text-shadow: -2px -2px 0 #000, 2px -2px 0 #000, -2px 2px 0 #000, 2px 2px 0 #000, 0 -2px 0 #000, 0 2px 0 #000, -2px 0 0 #000, 2px 0 0 #000, 0 0 0.6rem rgba(0, 0, 0, 0.85);
            }
            .wp-sub-lede > span { display: block; }
        }

        .wp-leader { display: none; }
        html.es-anim #wp .wp-leader {
            display: grid;
            place-items: center;
            position: absolute;
            inset: 0;
            z-index: 6;
            background:
                linear-gradient(#17130f, #17130f) 50% 0 / 2px 100% no-repeat,
                linear-gradient(#17130f, #17130f) 0 50% / 100% 2px no-repeat,
                #cfc8b8;
            color: #17130f;
            /* Safari keeps the finished leader visible at an opacity of nought, on top of the
               title: without this the headline under it could not be selected. */
            pointer-events: none;
            animation: wp-leader-off 0.2s linear 1.04s forwards;
        }
        .wp-leader-sweep,
        .wp-leader-ring {
            position: absolute;
            height: 74%;
            aspect-ratio: 1;
            border-radius: 50%;
        }
        .wp-leader-ring { border: 3px solid #17130f; box-shadow: inset 0 0 0 0.9rem #cfc8b8, inset 0 0 0 calc(0.9rem + 3px) #17130f; }
        html.es-anim #wp .wp-leader-sweep {
            background: conic-gradient(from 0deg, rgba(23, 19, 15, 0.4) var(--wp-sweep), transparent 0);
            animation: wp-sweep 0.52s linear 2;
        }
        .wp-leader b {
            grid-area: 1 / 1;
            position: relative;
            font-family: var(--wp-display);
            font-weight: 400;
            font-size: clamp(7rem, 26cqi, 19rem);
            line-height: 1;
            padding-top: 0.08em;
            opacity: 0;
        }
        /* No backwards fill: until its turn comes, a numeral rests at its own opacity of nought. */
        html.es-anim #wp .wp-leader b:nth-of-type(1) { animation: wp-leader-num 0.52s steps(1) 0s 1 forwards; }
        html.es-anim #wp .wp-leader b:nth-of-type(2) { animation: wp-leader-num 0.52s steps(1) 0.52s 1 forwards; }
        @keyframes wp-sweep { from { --wp-sweep: 0deg; } to { --wp-sweep: 360deg; } }
        @keyframes wp-leader-num { 0% { opacity: 1; } 100% { opacity: 0; } }
        @keyframes wp-leader-off { to { opacity: 0; visibility: hidden; } }

        .wp-bar-cta {
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.9rem 1.1rem;
            padding: 1.1rem 1rem 1.15rem;
            white-space: normal;
            overflow: visible;
            letter-spacing: 0;
        }
        @media (max-width: 620px) {
            .wp-bar > span + span { display: none; }
            .wp-bar-cta .wp-btn { width: 100%; font-size: 1.2rem; padding-inline: 0.9rem; text-align: center; }
        }
        /* The longer of the two labels is a few pixels wider than a 360px phone's button. */
        @media (max-width: 374px) {
            .wp-bar-cta .wp-btn { font-size: 1.1rem; }
        }

        /* ---------------------------------------------------------------
           Reels: a running order as a strip of film, drawn to scale
           --------------------------------------------------------------- */
        .wp-reelcard { margin-top: clamp(2rem, 4vw, 3.25rem); }
        .wp-reelcard-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.4rem 1.25rem;
            margin-bottom: 0.9rem;
        }
        .wp-reel-title { font-family: var(--wp-display); font-size: 2rem; letter-spacing: 0.03em; text-transform: uppercase; line-height: 1; }
        .wp-reelcard-head .wp-mono { color: var(--wp-hot); }
        .wp-reel-note { margin-inline-start: auto; font-family: var(--wp-mono); font-size: 0.8rem; color: var(--wp-ink-3); }
        .wp-reel-cap { max-width: 50rem; margin-top: 1.1rem; color: var(--wp-ink-2); }

        .wp-reel {
            position: relative;
            display: flex;
            gap: 0.3rem;
            padding: 1.3rem 0.3rem;
            background: #17140f;
            box-shadow: inset 0 0 0 1px rgba(244, 241, 232, 0.08);
        }
        .wp-reel::before,
        .wp-reel::after {
            content: "";
            position: absolute;
            inset-inline: 0;
            height: 0.55rem;
            background: repeating-linear-gradient(90deg, transparent 0 0.45rem, var(--wp-hole) 0.45rem 1.05rem, transparent 1.05rem 1.5rem);
        }
        .wp-reel::before { top: 0.36rem; }
        .wp-reel::after { bottom: 0.36rem; }
        .wp-fr {
            flex: var(--min) 1 0;
            min-width: 0;
            height: 9.75rem;
            display: grid;
            place-content: center;
            gap: 0.3rem;
            writing-mode: vertical-rl;
            background: #2a241a;
            color: #e6e1d3;
            font-family: var(--wp-mono);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            line-height: 1.2;
            white-space: nowrap;
        }
        .wp-fr-x { color: #b9b3a5; font-weight: 400; }
        .wp-fr-main {
            writing-mode: horizontal-tb;
            place-content: stretch;
            grid-template-rows: auto 1fr auto;
            padding: 0.7rem 0.9rem 0.6rem;
            background: #f4f1e8;
            color: #17130f;
        }
        .wp-fr-main .wp-fr-n {
            grid-row: 2;
            align-self: center;
            justify-self: center;
            font-family: var(--wp-display);
            font-weight: 400;
            font-size: clamp(1.9rem, 4vw, 3.4rem);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1;
        }
        .wp-fr-main .wp-fr-x { grid-row: 3; display: flex; justify-content: space-between; gap: 0.5rem; color: #463e33; font-weight: 700; }
        .wp-fr-blank { flex: 0 0 5rem; background: #221d15; }
        @media (max-width: 899px) {
            .wp-reel { flex-direction: column; padding: 0.3rem 1.3rem; }
            .wp-reel::before,
            .wp-reel::after {
                inset-inline: auto;
                inset-block: 0;
                top: 0;
                bottom: 0;
                width: 0.55rem;
                height: auto;
                background: repeating-linear-gradient(180deg, transparent 0 0.45rem, var(--wp-hole) 0.45rem 1.05rem, transparent 1.05rem 1.5rem);
            }
            .wp-reel::before { left: 0.36rem; }
            .wp-reel::after { right: 0.36rem; }
            .wp-fr,
            .wp-fr-main {
                flex: none;
                height: max(2.7rem, calc(var(--min) * 0.085rem));
                writing-mode: horizontal-tb;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            .wp-fr-main .wp-fr-n { font-size: 1.9rem; }
            .wp-fr-main .wp-fr-x { display: inline; }
            .wp-fr-blank { display: none; }
        }

        /* The ticker: every kind of night, frame after frame */
        .wp-ticker { margin-top: clamp(2.5rem, 5vw, 4rem); background: #17140f; }
        #wp .wp-ticker .es-marquee-track { gap: 0; padding-right: 0; }
        .wp-tick {
            position: relative;
            flex: none;
            display: grid;
            place-items: center;
            width: 15rem;
            height: 6.1rem;
            padding: 1.3rem 0.5rem 1.15rem;
            border-inline: 0.17rem solid #17140f;
            background: linear-gradient(#0b0b0a, #0b0b0a) 0 50% / 100% calc(100% - 2.6rem) no-repeat, #17140f;
            font-family: var(--wp-display);
            font-size: 1.55rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            line-height: 1;
            color: #f4f1e8;
            white-space: nowrap;
        }
        .wp-tick::before,
        .wp-tick::after {
            content: "";
            position: absolute;
            inset-inline: 0;
            height: 0.55rem;
            background: repeating-linear-gradient(90deg, transparent 0 0.45rem, var(--wp-hole) 0.45rem 1.05rem, transparent 1.05rem 1.5rem);
        }
        .wp-tick::before { top: 0.36rem; }
        .wp-tick::after { bottom: 0.36rem; }
        @media (prefers-reduced-motion: reduce) {
            .wp-ticker { padding-block: 0.5rem; }
        }

        /* ---------------------------------------------------------------
           Scene two: an empty house and a link on the screen
           --------------------------------------------------------------- */
        .wp-far {
            position: absolute;
            left: 50%;
            top: 9%;
            z-index: 1;
            translate: -50% 0;
            width: min(52%, 31rem);
            aspect-ratio: 2.39 / 1;
            display: grid;
            place-items: center;
            background: linear-gradient(#f4f1e8, #e4dfd0);
            box-shadow: 0 0 0 1px #000, 0 0 7rem 1.5rem rgba(244, 241, 232, 0.2);
            color: #17130f;
        }
        .wp-far span { font-family: var(--wp-mono); font-weight: 700; font-size: clamp(0.56rem, 1.7cqi, 1.05rem); letter-spacing: 0.02em; }
        .wp-far::after {
            content: "";
            position: absolute;
            top: 100%;
            left: -45%;
            right: -45%;
            height: 9rem;
            background: radial-gradient(ellipse at 50% 0, rgba(244, 241, 232, 0.16), transparent 68%);
        }
        .wp-seats {
            position: absolute;
            inset: auto 0 0 0;
            z-index: 1;
            height: 46%;
            background:
                radial-gradient(ellipse 46% 100% at 50% 100%, #7c1818 0 97%, transparent 100%) 0 100% / 4.4rem 2.7rem repeat-x,
                radial-gradient(ellipse 46% 100% at 50% 100%, #591111 0 97%, transparent 100%) 2.2rem calc(100% - 1.9rem) / 4.4rem 2.5rem repeat-x,
                radial-gradient(ellipse 46% 100% at 50% 100%, #3b0b0b 0 97%, transparent 100%) 0 calc(100% - 3.6rem) / 4.4rem 2.3rem repeat-x,
                radial-gradient(ellipse 46% 100% at 50% 100%, #260707 0 97%, transparent 100%) 2.2rem calc(100% - 5.1rem) / 4.4rem 2.1rem repeat-x;
        }
        .wp-trio { display: grid; gap: 2rem 2.5rem; margin-top: clamp(2rem, 4vw, 3rem); }
        @media (min-width: 820px) { .wp-trio { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wp-fig { padding-top: 1.1rem; border-top: 2px solid var(--wp-ink); }
        .wp-fig h3 { margin-block: 0.5rem 0.6rem; font-family: var(--wp-display); font-weight: 400; font-size: clamp(2.8rem, 5vw, 4rem); letter-spacing: 0.02em; text-transform: uppercase; line-height: 0.95; }
        .wp-fig h3 span { color: var(--wp-hot); }
        .wp-fig p:last-child { color: var(--wp-ink-2); }
        .wp-next { margin-top: 2.25rem; color: var(--wp-ink-2); }
        .wp-next a { display: inline-flex; align-items: center; gap: 0.35rem; margin-inline-start: 0.4rem; }
        .wp-next svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           Shared furniture below a frame
           --------------------------------------------------------------- */
        .wp-body { margin-top: clamp(1.75rem, 3.5vw, 2.75rem); }
        .wp-grid-3 { display: grid; gap: 1.25rem; margin-top: 2rem; }
        @media (min-width: 900px) { .wp-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wp-card {
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
            padding: 1.6rem 1.5rem 1.5rem;
            background: var(--wp-card);
            border: 1px solid var(--wp-frame);
            outline: 1px solid var(--wp-line);
            outline-offset: -7px;
            box-shadow: var(--wp-shadow);
        }
        .wp-card-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 0.75rem; }
        .wp-card h3 { font-family: var(--wp-display); font-weight: 400; font-size: 1.8rem; letter-spacing: 0.03em; text-transform: uppercase; line-height: 1; }
        .wp-card p { color: var(--wp-ink-2); font-size: 1rem; }
        .wp-card .wp-fine { color: var(--wp-ink-3); font-size: 0.92rem; }
        .wp-card-plain { background: transparent; border-style: dashed; box-shadow: none; outline: 0; }

        .wp-ticks { display: grid; gap: 0.85rem; margin-top: 1.4rem; }
        .wp-ticks li { display: grid; grid-template-columns: 1.2rem minmax(0, 1fr); gap: 0.5rem; color: var(--wp-ink-2); }
        .wp-ticks li::before {
            content: "";
            width: 0.62rem;
            height: 0.72rem;
            margin-top: 0.45rem;
            background: var(--wp-hot);
            clip-path: polygon(0 0, 100% 50%, 0 100%);
        }

        .wp-duo { display: grid; gap: 2.25rem 3.5rem; align-items: start; }
        @media (min-width: 900px) { .wp-duo { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }
        .wp-duo > div > .wp-kick { margin-bottom: 0.8rem; }
        .wp-duo p + p { margin-top: 0.9rem; }

        .wp-table { width: 100%; border-collapse: collapse; font-size: 0.98rem; }
        .wp-table thead th {
            padding: 0 0 0.6rem;
            font-family: var(--wp-mono);
            font-size: 0.68rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            text-align: start;
            color: var(--wp-ink-3);
        }
        .wp-table th[scope="row"] { text-align: start; font-weight: 700; }
        .wp-table tbody th,
        .wp-table tbody td { padding: 0.7rem 0 0.55rem; border-top: 1px solid var(--wp-line); }
        /* The inset keeps two figures apart on a phone, where a price and the quantity beside it read as one number. */
        .wp-table .wp-num { padding-inline-start: 0.8rem; text-align: end; font-family: var(--wp-mono); font-variant-numeric: tabular-nums; }
        .wp-table thead .wp-num { text-align: end; }
        .wp-table .wp-low { color: var(--wp-hot); font-weight: 700; }
        .wp-table .wp-meter-row td { padding: 0 0 0.65rem; border-top: 0; }
        .wp-meter { height: 0.4rem; background: var(--wp-line); }
        .wp-meter i { display: block; height: 100%; width: var(--pct); background: var(--wp-ink); transform-origin: 0 50%; transition: scale 1.1s cubic-bezier(0.22, 1, 0.36, 1) calc(var(--i, 0) * 0.12s + 0.2s); }
        html.es-anim #wp [data-reveal]:not(.is-revealed) .wp-meter i { scale: 0 1; }

        /* ---------------------------------------------------------------
           Scene three: a marathon, across the whole frame
           --------------------------------------------------------------- */
        .wp-pic-order { grid-template-rows: auto minmax(0, 1fr) auto; }
        .wp-pic-order > .wp-sub { grid-row: 3; }
        .wp-burn {
            position: relative;
            z-index: 4;
            grid-row: 1;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.3rem 1rem;
            padding: clamp(1rem, 2.6cqi, 1.9rem) clamp(1rem, 3cqi, 2.4rem) 0.75rem;
            font-family: var(--wp-mono);
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #b9b3a5;
        }
        .wp-burn strong { font-family: var(--wp-display); font-weight: 400; font-size: clamp(1.5rem, 3cqi, 2.3rem); letter-spacing: 0.04em; color: #f4f1e8; line-height: 1; }
        .wp-reel-tilt { position: relative; z-index: 1; grid-row: 2; align-self: center; margin-inline: -1.5rem; padding-block: 0.75rem; rotate: -1.6deg; }
        .wp-reel-tilt .wp-reel { background: #2b251b; box-shadow: 0 1.5rem 3rem -1rem #000; }
        .wp-reel-tilt .wp-fr:not(.wp-fr-main) { background: #3a3225; }
        .wp-reel-tilt .wp-fr-blank { background: #332c20; }
        @media (max-width: 899px) {
            .wp-reel-tilt { margin-inline: 1rem; rotate: none; }
        }

        /* ---------------------------------------------------------------
           Scene four: four doors, one hundred and twenty seats each
           --------------------------------------------------------------- */
        .wp-doors {
            position: relative;
            z-index: 1;
            grid-row: 1;
            align-self: center;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.5rem 1.25rem;
            padding: 1.5rem 1.25rem 0.5rem;
        }
        @media (min-width: 900px) {
            .wp-doors { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: clamp(1.5rem, 4.6cqi, 4rem); padding: clamp(1.25rem, 3cqi, 2.5rem) clamp(2rem, 7cqi, 6rem) 0; }
        }
        .wp-door { display: grid; gap: 0.6rem; }
        .wp-door-date { font-family: var(--wp-display); font-size: clamp(1.15rem, 2.3cqi, 1.75rem); letter-spacing: 0.05em; text-transform: uppercase; line-height: 1; color: #f4f1e8; }
        .wp-house {
            position: relative;
            display: block;
            width: 100%;
            aspect-ratio: 12 / 10;
            background: radial-gradient(circle, rgba(244, 241, 232, 0.2) 0 30%, transparent 33%) 0 0 / calc(100% / 12) 10%;
        }
        .wp-house::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle, #f4f1e8 0 30%, transparent 33%) 0 0 / calc(100% / 12) 10%;
            clip-path: polygon(0 0, 100% 0, 100% calc(var(--full) * 10%), calc(var(--rem) * 100% / 12) calc(var(--full) * 10%), calc(var(--rem) * 100% / 12) calc((var(--full) + 1) * 10%), 0 calc((var(--full) + 1) * 10%));
            transition: clip-path 1.3s cubic-bezier(0.22, 1, 0.36, 1) 0.25s;
        }
        html.es-anim #wp [data-reveal]:not(.is-revealed) .wp-house::before { clip-path: polygon(0 0, 100% 0, 100% 0, 0 0, 0 0, 0 0); }
        .wp-door-left { font-family: var(--wp-mono); font-weight: 700; font-size: 0.78rem; letter-spacing: 0.14em; text-transform: uppercase; color: #b9b3a5; }
        .wp-door-low { color: #ff7a6b; }
        .wp-sheet { margin-top: 2rem; padding: 1.5rem 1.5rem 1.3rem; }
        .wp-sheet .wp-fine { margin-top: 0.9rem; }

        /* ---------------------------------------------------------------
           Scene five: one field in the booth, one lit page out front
           --------------------------------------------------------------- */
        .wp-pic-link { grid-template-rows: auto auto auto auto; padding-top: 1.25rem; }
        .wp-pic-link > .wp-sub { grid-row: 4; }
        .wp-booth {
            position: relative;
            z-index: 4;
            grid-row: 1;
            justify-self: center;
            width: min(100% - 2.5rem, 21rem);
            display: grid;
            gap: 0.5rem;
            padding: 0.9rem 1rem 1rem;
            background: #1b1814;
            border: 1px solid rgba(244, 241, 232, 0.22);
            box-shadow: inset 0 0 2.5rem rgba(255, 196, 92, 0.1);
        }
        .wp-booth-k { font-family: var(--wp-mono); font-weight: 700; font-size: 0.62rem; letter-spacing: 0.22em; text-transform: uppercase; color: #b9b3a5; }
        .wp-booth-f {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 0.65rem 0.55rem;
            background: #0b0b0a;
            border: 1px solid rgba(255, 225, 77, 0.55);
            font-family: var(--wp-mono);
            font-size: 0.7rem;
            color: #ffe14d;
            white-space: nowrap;
            overflow: hidden;
        }
        .wp-booth-f svg { flex: none; width: 0.9rem; height: 0.9rem; }
        .wp-booth-f span { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        /* A phone's frame is narrower than the address: a little less inset, a little smaller print. */
        @media (max-width: 400px) {
            .wp-booth { width: min(100% - 1.5rem, 21rem); padding-inline: 0.75rem; }
            .wp-booth-f { gap: 0.4rem; padding-inline: 0.5rem; font-size: 0.64rem; }
        }
        .wp-beam {
            grid-row: 2;
            justify-self: center;
            width: min(100% - 2.5rem, 26rem);
            height: 3.25rem;
            background: linear-gradient(180deg, rgba(255, 243, 205, 0.5), rgba(255, 243, 205, 0.12));
            clip-path: polygon(46% 0, 54% 0, 100% 100%, 0 100%);
            filter: blur(3px);
        }
        .wp-lit {
            position: relative;
            z-index: 4;
            grid-row: 3;
            justify-self: center;
            width: min(100% - 2.5rem, 26rem);
            display: grid;
            gap: 0.45rem;
            padding: 1.25rem 1.25rem 1.2rem;
            background: #f4f1e8;
            color: #17130f;
            box-shadow: 0 0 5rem 0.5rem rgba(255, 243, 205, 0.22);
        }
        .wp-lit-k { font-family: var(--wp-mono); font-weight: 700; font-size: 0.62rem; letter-spacing: 0.22em; text-transform: uppercase; color: #5b5145; }
        .wp-lit-t { font-family: var(--wp-display); font-size: 2rem; letter-spacing: 0.03em; text-transform: uppercase; line-height: 1; }
        .wp-lit-d { font-family: var(--wp-mono); font-size: 0.76rem; color: #463e33; }
        .wp-lit-l {
            margin-top: 0.3rem;
            padding: 0.55rem 0.65rem 0.5rem;
            background: #17130f;
            color: #ffe14d;
            font-family: var(--wp-mono);
            font-size: 0.74rem;
            white-space: nowrap;
            overflow: hidden;
        }
        .wp-lit-q { display: flex; align-items: center; gap: 0.8rem; margin-top: 0.35rem; }
        .wp-lit-q p:first-child { font-weight: 700; font-size: 0.95rem; line-height: 1.2; }
        .wp-lit-q p + p { margin-top: 0.15rem; font-size: 0.78rem; line-height: 1.3; color: #463e33; }
        .wp-motes { display: none; }
        @media (min-width: 900px) {
            .wp-pic-link {
                grid-template-columns: minmax(0, 0.92fr) minmax(0, 0.42fr) minmax(0, 1fr);
                grid-template-rows: minmax(0, 1fr) auto;
                padding-top: 0;
            }
            .wp-pic-link > .wp-sub { grid-row: 2; }
            .wp-booth { grid-row: 1; grid-column: 1; align-self: start; justify-self: start; width: 21.5rem; margin: 2.6rem 0 0 2.25rem; }
            .wp-booth::after {
                content: "";
                position: absolute;
                left: calc(100% - 0.35rem);
                top: 50%;
                width: 1.5rem;
                aspect-ratio: 1;
                translate: 0 -50%;
                border-radius: 50%;
                background: radial-gradient(circle, #fff7da 0 22%, rgba(255, 243, 205, 0.5) 40%, #2a241a 44% 100%);
                box-shadow: 0 0 1.5rem 0.2rem rgba(255, 243, 205, 0.45);
            }
            .wp-lit { grid-row: 1; grid-column: 3; align-self: center; justify-self: start; width: min(100% - 2rem, 25rem); margin-block: 1.75rem 0.5rem; }
            .wp-beam {
                position: absolute;
                inset: 0;
                z-index: 1;
                grid-row: 1 / -1;
                grid-column: 1 / -1;
                justify-self: stretch;
                align-self: stretch;
                width: auto;
                height: auto;
                background: linear-gradient(90deg, rgba(255, 243, 205, 0.62) 18%, rgba(255, 243, 205, 0.2) 50%, transparent 66%);
                clip-path: polygon(24.1rem 5.6rem, 72% 9%, 72% 80%);
                filter: blur(7px);
                mix-blend-mode: screen;
            }
            .wp-motes { display: block; position: absolute; inset: 0; z-index: 2; pointer-events: none; }
            .wp-motes i {
                position: absolute;
                left: var(--x);
                top: var(--y);
                width: var(--s, 3px);
                aspect-ratio: 1;
                border-radius: 50%;
                background: #fff7da;
                opacity: 0.55;
            }
            html.es-anim #wp .wp-motes i { animation: wp-mote var(--d, 9s) ease-in-out var(--w, 0s) infinite alternate; }
        }
        @keyframes wp-mote {
            from { translate: 0 0; opacity: 0.15; }
            50% { opacity: 0.7; }
            to { translate: 1.4rem -1.9rem; opacity: 0.25; }
        }

        /* A code to scan: modules laid out by a rule in PHP. Decoration, never a working code. */
        .wp-qr {
            flex: none;
            display: block;
            width: 3.6rem;
            aspect-ratio: 1;
            padding: 0.3rem;
            background: #fff;
            color: #17130f;
            box-shadow: 0 0 0 1px rgba(23, 19, 15, 0.35);
        }
        .wp-qr svg { display: block; width: 100%; height: 100%; }

        /* ---------------------------------------------------------------
           Scene six: the credits roll, and a code to follow
           --------------------------------------------------------------- */
        .wp-pic-reach { grid-template-columns: minmax(0, 1fr) auto; column-gap: clamp(1rem, 5cqi, 4rem); padding-inline: clamp(1.25rem, 7cqi, 6rem); }
        .wp-roll {
            position: relative;
            z-index: 1;
            grid-row: 1;
            grid-column: 1;
            height: 100%;
            min-height: 11rem;
            max-height: 21rem;
            align-self: center;
            overflow: hidden;
            -webkit-mask-image: linear-gradient(180deg, transparent, #000 22%, #000 74%, transparent);
            mask-image: linear-gradient(180deg, transparent, #000 22%, #000 74%, transparent);
        }
        .wp-roll-in { display: grid; gap: 0.8rem; width: min(100%, 27rem); margin-inline: auto; padding-top: 1.5rem; }
        html.es-anim #wp .wp-roll-in { animation: wp-roll 22s linear infinite; }
        @keyframes wp-roll { to { translate: 0 -50%; } }
        .wp-roll-in b { display: block; margin-bottom: 0.3rem; font-family: var(--wp-display); font-weight: 400; font-size: clamp(0.95rem, 2.2cqi, 1.5rem); letter-spacing: 0.22em; text-transform: uppercase; text-align: center; color: #b9b3a5; }
        .wp-roll-in span { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 1.4rem; }
        .wp-roll-in span i { height: 0.3rem; background: rgba(244, 241, 232, 0.4); justify-self: end; width: var(--a, 60%); }
        .wp-roll-in span i + i { justify-self: start; width: var(--b, 70%); background: #f4f1e8; }
        .wp-follow { position: relative; z-index: 4; grid-row: 1; grid-column: 2; align-self: center; display: grid; justify-items: center; gap: 0.6rem; }
        .wp-follow .wp-qr { width: clamp(4.6rem, 13cqi, 8.5rem); padding: 0.5rem; }
        .wp-follow span { font-family: var(--wp-display); font-size: clamp(1.2rem, 2.6cqi, 2rem); letter-spacing: 0.2em; text-transform: uppercase; line-height: 1; color: #f4f1e8; }
        .wp-letter { display: grid; gap: 2rem 3.5rem; margin-top: 2.75rem; align-items: start; }
        @media (min-width: 960px) { .wp-letter { grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); } }
        .wp-letter h3 { margin-bottom: 0.9rem; font-family: var(--wp-display); font-weight: 400; font-size: clamp(2rem, 3.6vw, 2.8rem); letter-spacing: 0.02em; text-transform: uppercase; line-height: 0.98; text-wrap: balance; }
        .wp-letter p { color: var(--wp-ink-2); }
        .wp-letter p + p { margin-top: 0.9rem; }
        .wp-allow { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; margin-block: 0.4rem 0.3rem; }
        .wp-allow div { display: flex; flex-direction: column-reverse; gap: 0.2rem; padding-top: 0.6rem; border-top: 2px solid var(--wp-ink); }
        .wp-allow dt { font-family: var(--wp-mono); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--wp-ink-3); }
        .wp-allow dd { font-family: var(--wp-display); font-size: clamp(2.2rem, 4.4vw, 3.2rem); letter-spacing: 0.02em; line-height: 0.95; color: var(--wp-ink); }

        /* ---------------------------------------------------------------
           Scene seven: admit one, and nothing off the top
           --------------------------------------------------------------- */
        .wp-pic-tix { grid-template-rows: auto auto auto; align-items: center; }
        .wp-pic-tix > .wp-sub { grid-row: 3; }
        .wp-stubs { position: relative; z-index: 1; grid-row: 1; grid-column: 1; justify-self: center; display: grid; padding-block: 1.75rem 0.5rem; }
        .wp-stub {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 0 0.9rem;
            width: clamp(15rem, 34cqi, 21rem);
            padding: clamp(0.75rem, 1.8cqi, 1.1rem) clamp(1.25rem, 3cqi, 1.9rem);
            background: #f4f1e8;
            color: #17130f;
            rotate: var(--r, -3deg);
            -webkit-mask: radial-gradient(circle at 0 50%, transparent 0.5rem, #000 0.54rem) left / 51% 100% no-repeat, radial-gradient(circle at 100% 50%, transparent 0.5rem, #000 0.54rem) right / 51% 100% no-repeat;
            mask: radial-gradient(circle at 0 50%, transparent 0.5rem, #000 0.54rem) left / 51% 100% no-repeat, radial-gradient(circle at 100% 50%, transparent 0.5rem, #000 0.54rem) right / 51% 100% no-repeat;
        }
        .wp-stub + .wp-stub { margin-top: -0.55rem; }
        .wp-stub:nth-child(2) { --r: 2.5deg; translate: 8% 0; background: #ffe14d; }
        .wp-stub:nth-child(3) { --r: -1.2deg; translate: -3% 0; }
        .wp-stub b { font-family: var(--wp-display); font-weight: 400; font-size: clamp(2rem, 5cqi, 3rem); white-space: nowrap; letter-spacing: 0.05em; text-transform: uppercase; line-height: 1; }
        .wp-stub i { grid-column: 1; font-style: normal; font-family: var(--wp-mono); font-size: clamp(0.6rem, 1.3cqi, 0.72rem); white-space: nowrap; letter-spacing: 0.12em; text-transform: uppercase; color: #463e33; }
        .wp-stub em { grid-column: 2; grid-row: 1 / span 2; align-self: stretch; padding-left: 0.8rem; border-left: 2px dashed rgba(23, 19, 15, 0.5); text-align: center; font-style: normal; font-family: var(--wp-mono); font-weight: 700; font-size: clamp(0.6rem, 1.3cqi, 0.72rem); letter-spacing: 0.1em; writing-mode: vertical-rl; }
        .wp-cut { position: relative; z-index: 4; grid-row: 2; grid-column: 1; justify-self: center; display: grid; justify-items: center; text-align: center; padding-top: 0.75rem; }
        @media (min-width: 760px) {
            .wp-pic-tix { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); grid-template-rows: minmax(0, 1fr) auto; }
            .wp-pic-tix > .wp-sub { grid-row: 2; }
            .wp-cut { grid-row: 1; grid-column: 2; padding-top: 0; }
        }
        .wp-cut span { font-family: var(--wp-mono); font-weight: 700; font-size: clamp(0.56rem, 1.5cqi, 0.82rem); letter-spacing: 0.2em; text-transform: uppercase; color: #b9b3a5; }
        .wp-cut strong { font-family: var(--wp-display); font-weight: 400; font-size: clamp(6.5rem, 24cqi, 17rem); letter-spacing: 0.01em; line-height: 0.86; color: #f4f1e8; }
        .wp-board { padding: 1.5rem 1.5rem 1.4rem; }
        .wp-board .wp-fine { margin-top: 0.4rem; }
        .wp-board .wp-fine a { padding-block: 0.3rem; }
        .wp-duo-wide { margin-top: clamp(1.75rem, 3.5vw, 2.75rem); }
        @media (min-width: 900px) { .wp-duo-wide { grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr); } }

        /* ---------------------------------------------------------------
           Scene eight: the room votes between three title cards
           --------------------------------------------------------------- */
        .wp-ballot { position: relative; z-index: 1; grid-row: 1; align-self: center; display: grid; gap: clamp(0.9rem, 2.4cqi, 1.75rem); padding: clamp(1.25rem, 3.4cqi, 2.6rem) clamp(1.25rem, 5cqi, 4.5rem) 0.25rem; }
        .wp-ballot-q { font-family: var(--wp-display); font-size: clamp(1.25rem, 3.1cqi, 2.4rem); letter-spacing: 0.06em; text-transform: uppercase; line-height: 1; text-align: center; color: #f4f1e8; }
        .wp-ballot-row { display: grid; gap: 0.75rem; }
        @media (min-width: 700px) { .wp-ballot-row { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: clamp(0.75rem, 2.4cqi, 2rem); } }
        .wp-ballot-card {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.4rem 0.75rem;
            padding: 0.8rem 0.9rem 0.75rem;
            border: 1px solid rgba(244, 241, 232, 0.4);
            outline: 1px solid rgba(244, 241, 232, 0.18);
            outline-offset: -5px;
            color: #f4f1e8;
        }
        .wp-ballot-card > span { font-family: var(--wp-mono); font-weight: 700; font-size: 0.72rem; letter-spacing: 0.1em; color: #b9b3a5; }
        .wp-ballot-card b { font-family: var(--wp-display); font-weight: 400; font-size: clamp(1.15rem, 2.5cqi, 1.9rem); letter-spacing: 0.04em; text-transform: uppercase; line-height: 1; text-wrap: balance; }
        .wp-ballot-card i { grid-column: 1 / -1; height: 0.35rem; background: linear-gradient(90deg, #f4f1e8 calc(var(--pct) * 1%), rgba(244, 241, 232, 0.16) 0); }
        .wp-ballot-card:first-child { border-color: #ffe14d; }
        .wp-ballot-card:first-child i { background: linear-gradient(90deg, #ffe14d calc(var(--pct) * 1%), rgba(244, 241, 232, 0.16) 0); }
        .wp-ballot-card:first-child > span:last-of-type { color: #ffe14d; }
        @media (min-width: 700px) {
            .wp-ballot-card { grid-template-columns: minmax(0, 1fr) auto; align-content: space-between; aspect-ratio: 16 / 9; padding: clamp(0.75rem, 1.8cqi, 1.4rem); }
            .wp-ballot-card b { grid-column: 1 / -1; grid-row: 2; align-self: center; text-align: center; }
            .wp-ballot-card i { grid-row: 3; }
        }
        .wp-lobby { display: grid; gap: 1.25rem; }
        @media (min-width: 700px) { .wp-lobby { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .wp-lobby { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wp-mini { margin-top: auto; padding-top: 0.9rem; border-top: 1px solid var(--wp-line); }
        .wp-mini > .wp-kick { margin-bottom: 0.7rem; }
        .wp-bars { display: grid; gap: 0.55rem; }
        .wp-bars div { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.15rem 0.75rem; font-size: 0.86rem; color: var(--wp-ink); }
        .wp-bars div span:last-of-type { font-family: var(--wp-mono); color: var(--wp-ink-3); }
        .wp-bars .wp-meter { grid-column: 1 / -1; }
        .wp-code { margin-top: auto; padding: 0.8rem 0.9rem 0.75rem; background: #0b0b0a; color: #ffe14d; font-family: var(--wp-mono); font-size: 0.76rem; line-height: 1.5; overflow-wrap: anywhere; }
        .wp-sync { display: grid; gap: 0.45rem; font-family: var(--wp-mono); font-size: 0.8rem; color: var(--wp-ink); }
        .wp-sync-head { display: flex; justify-content: space-between; gap: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--wp-line); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--wp-ink-3); }
        .wp-sync-head span + span { color: var(--wp-hot); }
        .wp-sync-row { display: flex; align-items: center; gap: 0.5rem; }
        .wp-sync-row svg { flex: none; width: 0.9rem; height: 0.9rem; color: var(--wp-hot); }

        /* ---------------------------------------------------------------
           Three steps: three consecutive frames of 35mm
           --------------------------------------------------------------- */
        .wp-steps-sec { padding-bottom: clamp(3.5rem, 7vw, 6rem); overflow-x: clip; }
        .wp-steps-sec .wp-h2 { max-width: 50rem; margin-bottom: clamp(1.75rem, 4vw, 3rem); }
        .wp-strip { position: relative; background: #17140f; padding: 1.95rem 0; --wp-hole: var(--wp-wall); }
        .wp-alt .wp-strip { --wp-hole: var(--wp-wall-2); }
        .wp-strip::before,
        .wp-strip::after {
            content: "";
            position: absolute;
            inset-inline: 0;
            height: 0.62rem;
            background: repeating-linear-gradient(90deg, transparent 0 0.5rem, var(--wp-hole) 0.5rem 1.2rem, transparent 1.2rem 1.7rem);
        }
        .wp-strip::before { top: 0.4rem; }
        .wp-strip::after { bottom: 0.4rem; }
        .wp-strip-in { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.4rem; padding-inline: 1.25rem; }
        .wp-strip-blank { display: none; background: #0b0b0a; }
        .wp-strip-frame { position: relative; display: grid; align-content: start; gap: 0.6rem; padding: 1.75rem 1.5rem 1.9rem; background: #0b0b0a; color: #f4f1e8; }
        .wp-ring {
            display: grid;
            place-items: center;
            width: 4.6rem;
            aspect-ratio: 1;
            margin-bottom: 0.4rem;
            border-radius: 50%;
            border: 2px solid #f4f1e8;
            background:
                linear-gradient(rgba(244, 241, 232, 0.4), rgba(244, 241, 232, 0.4)) 50% 0 / 1px 100% no-repeat,
                linear-gradient(rgba(244, 241, 232, 0.4), rgba(244, 241, 232, 0.4)) 0 50% / 100% 1px no-repeat;
            box-shadow: inset 0 0 0 0.35rem #0b0b0a, inset 0 0 0 calc(0.35rem + 1px) rgba(244, 241, 232, 0.55);
            font-family: var(--wp-display);
            font-size: 2.6rem;
            line-height: 1;
            padding-top: 0.18rem;
            color: #ffe14d;
            text-shadow: 0 0 0 #0b0b0a, 2px 0 0 #0b0b0a, -2px 0 0 #0b0b0a, 0 2px 0 #0b0b0a, 0 -2px 0 #0b0b0a;
        }
        .wp-strip-frame h3 { font-family: var(--wp-display); font-weight: 400; font-size: clamp(2rem, 3.4vw, 2.7rem); letter-spacing: 0.03em; text-transform: uppercase; line-height: 1; }
        .wp-strip-frame p { color: #cbc6b8; font-size: 1rem; }
        .wp-edge { display: none; }
        @media (max-width: 899px) {
            .wp-strip { padding: 0.4rem 0; }
            .wp-strip::before,
            .wp-strip::after {
                inset-inline: auto;
                top: 0;
                bottom: 0;
                width: 0.62rem;
                height: auto;
                background: repeating-linear-gradient(180deg, transparent 0 0.5rem, var(--wp-hole) 0.5rem 1.2rem, transparent 1.2rem 1.7rem);
            }
            .wp-strip::before { left: 0.4rem; }
            .wp-strip::after { right: 0.4rem; }
            .wp-strip-in { padding-inline: 1.6rem; }
        }
        @media (min-width: 900px) {
            .wp-strip-in { grid-template-columns: minmax(1.25rem, 1fr) repeat(3, minmax(0, 26rem)) minmax(1.25rem, 1fr); padding-inline: 0; margin-inline: -2rem; }
            .wp-strip-blank { display: block; }
            .wp-edge {
                display: flex;
                justify-content: space-around;
                position: absolute;
                inset: auto 0 1.12rem 0;
                font-family: var(--wp-mono);
                font-size: 0.5rem;
                letter-spacing: 0.34em;
                text-transform: uppercase;
                line-height: 1;
                color: rgba(255, 225, 77, 0.62);
                white-space: nowrap;
                overflow: hidden;
            }
        }
        @supports (animation-timeline: view()) {
            @media (min-width: 1024px) {
                html.es-anim #wp .wp-strip-in {
                    animation: wp-drift linear both;
                    animation-timeline: view();
                }
            }
        }
        @keyframes wp-drift { from { translate: 1rem 0; } to { translate: -1rem 0; } }

        /* ---------------------------------------------------------------
           End credits: who it is for, what is in it, what to see next
           --------------------------------------------------------------- */
        .wp-credits {
            position: relative;
            padding-block: clamp(4rem, 9vw, 7.5rem);
            scroll-margin-top: 4rem;
            background-color: #070707;
            color: #f4f1e8;
            text-align: center;
        }
        .wp-credits-in { display: grid; justify-items: center; }
        .wp-credits-k { font-family: var(--wp-mono); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.3em; text-transform: uppercase; color: #a09a8c; }
        .wp-credits-h { margin-top: 0.9rem; font-family: var(--wp-display); font-weight: 400; font-size: clamp(2.2rem, 5vw, 3.9rem); letter-spacing: 0.05em; text-transform: uppercase; line-height: 0.98; text-wrap: balance; color: #f4f1e8; }
        .wp-credits-h span { color: #ffe14d; }
        .wp-credits-lede { max-width: 42rem; margin-top: 1.1rem; color: #cbc6b8; }
        #wp .wp-credits a { color: #f4f1e8; }
        #wp .wp-credits-lede a,
        #wp .wp-credits-more a {
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: #ffe14d;
            text-decoration-thickness: 2px;
            text-underline-offset: 0.22em;
        }
        #wp .wp-credits-lede a:hover,
        #wp .wp-credits-more a:hover { color: #ffe14d; }
        .wp-cast { display: grid; gap: clamp(1.5rem, 3vw, 2.25rem); width: 100%; max-width: 60rem; margin-top: clamp(2.25rem, 5vw, 3.75rem); }
        .wp-credit { display: grid; gap: 0.35rem; }
        .wp-credit h3,
        .wp-credit strong { font-family: var(--wp-display); font-weight: 400; font-size: clamp(1.6rem, 3vw, 2.3rem); letter-spacing: 0.06em; text-transform: uppercase; line-height: 1.02; color: #f4f1e8; }
        .wp-credit p,
        .wp-credit > span { color: #cbc6b8; font-size: 1rem; line-height: 1.5; }
        .wp-credit p + a { display: inline-block; margin-top: 0.4rem; font-weight: 700; text-decoration: underline; text-decoration-color: #ffe14d; text-decoration-thickness: 2px; text-underline-offset: 0.22em; }
        @media (min-width: 760px) {
            .wp-credit { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 2.25rem; align-items: baseline; }
            .wp-credit h3,
            .wp-credit strong { text-align: end; }
            /* Under the page id, so it outranks the centring that .wp-credit-link sets further
               down: without it a one-line credit sat centred in its half, out of line with the rest. */
            #wp .wp-credit > div,
            #wp .wp-credit > span { text-align: start; justify-content: flex-start; }
        }
        .wp-credit-link { padding-block: 0.15rem; transition: color 0.2s ease; }
        #wp .wp-credit-link:hover strong { color: #ffe14d; }
        .wp-credit-link > span { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; transition: gap 0.2s ease; }
        .wp-credit-link:hover > span { gap: 0.7rem; }
        .wp-credit-link svg { flex: none; width: 1rem; height: 1rem; color: #ffe14d; }
        @media (max-width: 759px) {
            .wp-credit-link > span { display: block; }
            .wp-credit-link svg { display: inline-block; vertical-align: -0.16em; margin-inline-start: 0.25rem; }
        }
        .wp-credits-gap { margin-top: clamp(4rem, 9vw, 7rem); }
        .wp-credits-more { margin-top: 2rem; }
        .wp-credits-more a { display: inline-flex; align-items: center; gap: 0.45rem; }
        .wp-credits-more svg { width: 1rem; height: 1rem; }
        @supports (animation-timeline: view()) {
            html.es-anim #wp .wp-credit {
                animation: wp-credit linear both;
                animation-timeline: view();
                animation-range: entry 0% entry 70%;
            }
        }
        @keyframes wp-credit { from { opacity: 0; translate: 0 2.75rem; } to { opacity: 1; translate: 0 0; } }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the print changes.
           --------------------------------------------------------------- */
        #wp .wp-plans > section { background: var(--wp-wall-2); }
        #wp .wp-plans h2 { font-family: var(--wp-display); font-weight: 400; text-transform: uppercase; letter-spacing: 0.02em; line-height: 0.95; font-size: clamp(2.3rem, 5vw, 3.9rem); color: var(--wp-ink); }
        #wp .wp-plans h2 + p { color: var(--wp-ink-2); font-size: 1.0625rem; }
        #wp .wp-plans .grid > div { background: var(--wp-card); border: 1px solid var(--wp-frame); border-radius: 0; outline: 1px solid var(--wp-line); outline-offset: -7px; box-shadow: var(--wp-shadow); color: var(--wp-ink); }
        #wp .wp-plans .grid > div[class*="border-blue"] { border-color: var(--wp-hot); outline-color: var(--wp-hot); }
        #wp .wp-plans .grid > div span,
        #wp .wp-plans .grid > div p,
        #wp .wp-plans .grid > div li { color: var(--wp-ink-2); }
        #wp .wp-plans .grid > div .text-3xl { font-family: var(--wp-display); font-weight: 400; font-size: 3.3rem; letter-spacing: 0.02em; color: var(--wp-ink); }
        #wp .wp-plans .grid > div .uppercase { font-family: var(--wp-mono); color: var(--wp-ink); }
        #wp .wp-plans .grid > div .rounded-full { background: var(--wp-hot); color: var(--wp-on-hot); border-radius: 3px; font-family: var(--wp-mono); }
        #wp .wp-plans .grid > div svg { color: var(--wp-hot); }
        #wp .wp-plans a.font-medium { color: var(--wp-ink); text-decoration: underline; text-decoration-color: var(--wp-hot); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        #wp .wp-plans a.rounded-2xl { background: var(--wp-btn); color: var(--wp-btn-ink); border-radius: 0; box-shadow: none; font-family: var(--wp-display); font-weight: 400; font-size: 1.3rem; letter-spacing: 0.06em; text-transform: uppercase; padding: 0.95rem 1.5rem 0.78rem; }

        #wp .wp-keep > section { background: var(--wp-wall-2); border-top: 1px solid var(--wp-line); }
        #wp .wp-keep h2 { font-family: var(--wp-display); font-weight: 400; text-transform: uppercase; letter-spacing: 0.02em; font-size: clamp(2rem, 4vw, 3rem); line-height: 1; color: var(--wp-ink); }
        #wp .wp-keep p.uppercase { font-family: var(--wp-mono); font-weight: 700; letter-spacing: 0.18em; color: var(--wp-hot); }
        #wp .wp-keep .grid > a { background: var(--wp-card); border: 1px solid var(--wp-frame); border-radius: 0; outline: 1px solid var(--wp-line); outline-offset: -7px; }
        #wp .wp-keep .grid > a:hover { border-color: var(--wp-hot); box-shadow: var(--wp-shadow); }
        #wp .wp-keep .grid > a > span:first-child { display: none; }
        #wp .wp-keep .grid > a h3 { color: var(--wp-ink); }
        #wp .wp-keep .grid > a p { color: var(--wp-ink-2); }
        #wp .wp-keep .grid > a > span:last-child,
        #wp .wp-keep a.self-start { color: var(--wp-hot); }

        /* ---------------------------------------------------------------
           Post-credits: the questions
           --------------------------------------------------------------- */
        .wp-faq-grid { display: grid; gap: 2.25rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .wp-faq-grid { grid-template-columns: minmax(0, 0.72fr) minmax(0, 1.28fr); }
            .wp-faq-head { position: sticky; top: 6.5rem; }
        }
        .wp-faq-head .wp-h2 { margin-block: 0.9rem 1rem; font-size: clamp(2.6rem, 5vw, 4.2rem); }
        .wp-qa { border-top: 2px solid var(--wp-ink); }
        .wp-qa details { border-bottom: 1px solid var(--wp-line); }
        .wp-qa summary {
            display: grid;
            grid-template-columns: 2.4rem minmax(0, 1fr) 1.4rem;
            align-items: start;
            gap: 0.75rem;
            padding: 1.25rem 0.25rem 1.1rem;
            cursor: pointer;
        }
        .wp-qa-no { padding-top: 0.28rem; font-family: var(--wp-mono); font-weight: 700; font-size: 0.8rem; letter-spacing: 0.08em; color: var(--wp-hot); }
        .wp-qa h3 { font-size: 1.16rem; font-weight: 700; line-height: 1.32; }
        .wp-qa summary i { position: relative; width: 1.4rem; height: 1.4rem; margin-top: 0.15rem; }
        .wp-qa summary i::before,
        .wp-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .wp-qa summary i::after { rotate: 90deg; }
        .wp-qa details[open] summary i::after { rotate: 0deg; }
        .wp-qa details p { padding: 0 0.25rem 1.5rem 3.4rem; max-width: 46rem; color: var(--wp-ink-2); }
        @media (max-width: 560px) { .wp-qa details p { padding-inline-start: 0.25rem; } }

        /* ---------------------------------------------------------------
           The last title card
           --------------------------------------------------------------- */
        .wp-pic-end { min-height: 26rem; }
        @media (min-width: 900px) { .wp-pic-end { aspect-ratio: 2.1 / 1; } }
        .wp-end-in { position: relative; z-index: 4; grid-row: 1 / -1; place-self: center; width: 100%; display: grid; justify-items: center; gap: 1.1rem; padding: clamp(2rem, 5vw, 3.5rem) 1.25rem; text-align: center; }
        .wp-end-k { font-family: var(--wp-mono); font-weight: 700; font-size: 0.72rem; letter-spacing: 0.28em; text-transform: uppercase; color: #ffe14d; }
        .wp-end-h { font-family: var(--wp-display); font-weight: 400; font-size: clamp(3rem, 9.4cqi, 7.6rem); letter-spacing: 0.02em; text-transform: uppercase; line-height: 0.9; text-wrap: balance; color: #f4f1e8; }
        .wp-end-h .wp-y { color: #ffe14d; }
        .wp-end-p { max-width: 42rem; color: #d9d3c4; }
        .wp-claimrow { display: grid; gap: 0.9rem; width: min(100%, 36rem); margin-top: 0.4rem; }
        @media (min-width: 700px) { .wp-claimrow { grid-template-columns: minmax(0, 1fr) auto; align-items: stretch; } }
        #wp .wp-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1rem;
            border: 2px solid rgba(244, 241, 232, 0.6);
            background: #0b0b0a;
            font-family: var(--wp-mono);
            font-weight: 700;
            font-size: clamp(1rem, 3.2vw, 1.05rem);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        /* Never under 16px: iOS zooms the page when a smaller field is focused. Under 390 the
           address at that size needs the row a little wider and the box a little less inset. */
        @media (max-width: 389px) {
            .wp-claimrow { width: calc(100% + 0.7rem); margin-inline: -0.35rem; }
            #wp .wp-claim { padding-inline: 0.4rem; }
        }
        #wp .wp-claim:focus-within { border-color: #ffe14d; box-shadow: 0 0 0 4px rgba(255, 225, 77, 0.25); }
        #wp .wp-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: #ffe14d;
            box-shadow: none;
            outline: none;
        }
        #wp .wp-claim input::placeholder { color: #a09a8c; }
        .wp-claim span { flex: none; color: #cbc6b8; user-select: none; }
        .wp-end-note { font-size: 0.92rem; color: #b9b3a5; }
        .wp-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }

        /* ---------------------------------------------------------------
           Scene selection: the section rail, on wide screens only
           --------------------------------------------------------------- */
        .wp-railbox { display: none; }
        @media (min-width: 1560px) {
            .wp-railbox { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .wp-rail {
                position: fixed;
                left: 1.1rem;
                top: 50%;
                translate: 0 -50%;
                padding: 0.35rem 0.85rem;
                background: #17140f;
                pointer-events: auto;
                --wp-hole: var(--wp-wall);
            }
            .wp-rail::before,
            .wp-rail::after {
                content: "";
                position: absolute;
                inset-block: 0;
                width: 0.36rem;
                background: repeating-linear-gradient(180deg, transparent 0 0.3rem, var(--wp-hole) 0.3rem 0.72rem, transparent 0.72rem 1.02rem);
            }
            .wp-rail::before { left: 0.22rem; }
            .wp-rail::after { right: 0.22rem; }
            .wp-rail ol { display: grid; gap: 0.22rem; }
            .wp-rail a {
                position: relative;
                display: grid;
                place-items: center;
                width: 2.1rem;
                height: 1.5rem;
                background: #0b0b0a;
                color: #a09a8c;
                font-family: var(--wp-mono);
                font-weight: 700;
                font-size: 0.6rem;
                letter-spacing: 0.06em;
                transition: background-color 0.2s ease, color 0.2s ease;
            }
            .wp-rail a:hover,
            .wp-rail a:focus-visible { color: #f4f1e8; }
            .wp-rail a.is-active { background: #ffe14d; color: #0b0b0a; }
            .wp-rail a > span {
                position: absolute;
                left: calc(100% + 1.1rem);
                padding: 0.35rem 0.55rem 0.28rem;
                background: #0b0b0a;
                color: #f4f1e8;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                white-space: nowrap;
                opacity: 0;
                translate: -0.3rem 0;
                pointer-events: none;
                transition: opacity 0.2s ease, translate 0.2s ease;
            }
            .wp-rail a:hover > span,
            .wp-rail a:focus-visible > span { opacity: 1; translate: 0 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            .wp-btn, .wp-btn svg, .wp-house::before, .wp-meter i, .wp-credit-link, .wp-credit-link > span, .wp-rail a, .wp-rail a > span, .wp-qa summary i::before, .wp-qa summary i::after { transition: none; }
        }
    </style>

    @php
        // One screening night. Minutes drive the proportional height, which is
        // the whole point: the feature is 112 of 167 minutes, and the 55
        // minutes around it are the part a stream link cannot hold.
        $order = [
            ['7:45 PM', 'Doors and chat', 15, false],
            ['8:00 PM', 'Introduction', 10, false],
            ['8:10 PM', 'Feature', 112, true],
            ['10:02 PM', 'Discussion', 30, false],
        ];

        // The same weekly event, four occurrences. rsvp_sold is a JSON map keyed
        // by date, so each Friday carries its own count against the one cap.
        $house = [
            ['Fri Nov 6', 118, 120],
            ['Fri Nov 13', 86, 120],
            ['Fri Nov 20', 41, 120],
            ['Fri Nov 27', 9, 120],
        ];

        $faqs = [
            [
                'q' => 'What streaming platforms work with watch parties?',
                'a' => 'Any of them, because Event Schedule does not integrate with any of them. An online event has one field, Event URL, and it takes whatever link you have: YouTube, Twitch, Discord, a Zoom or Teams room, or your own player. There is no platform picker and nothing to connect, so nothing breaks when you change platforms next month.',
            ],
            [
                'q' => 'How do I know how many people are coming?',
                'a' => 'Turn on free registration and set a cap. Registering takes a name and an email, one registration per email per date, and the remaining count is shown on the form as people take places. On a weekly series the cap is counted for each date on its own, so a full Friday does not close the following one. This is a registration list, not a live viewer count: Event Schedule never watches your stream.',
            ],
            [
                'q' => 'Can I charge for watch party access?',
                'a' => 'Yes, on Pro at '.plan_price($proMonthly).' a month, which is what a ticket with a price on it needs. Free registration is a different thing and stays unlimited on every plan. Create named ticket types with their own prices, quantities and sales windows, sell through your own Stripe or PayPal account, and keep everything: Event Schedule takes zero platform fees on ticket sales at every plan level. Scanning tickets in at the door is free on every plan, and Pro brings the live check-in dashboard, passes, promo codes and the ticket waitlist with it. Your processor charges its own fee (Stripe\'s is typically 2.9% + $0.30).',
            ],
            [
                'q' => 'Can I schedule recurring watch parties?',
                'a' => 'Yes, and it is one event rather than fifty. Pick the days of the week, add date exceptions for the weeks you are skipping, and end the series on a date or after a set number of screenings. Registration caps and ticket inventory are counted per occurrence. Two-way sync with Google Calendar, Outlook and CalDAV puts the next occurrence in your own calendar as a single entry; the subscribable iCal feed is the one that carries every date of the run.',
            ],
            [
                'q' => 'Do my followers get emailed when I add a screening?',
                'a' => 'If they left you an email address and confirmed it, yes: a screening you add reaches them as a digest on its own, batched and never more than one every few days. A newsletter is the other kind and you write that one, with the targeting to go with it, free: everyone who follows the schedule, everyone who registered for one particular screening, or one sub-schedule. The free plan covers 10 emails a month, Pro 100 and Enterprise 1,000, each recipient counting as one.',
            ],
            [
                'q' => 'Is Event Schedule free for hosting watch parties?',
                'a' => 'Yes. Unlimited events and screening series, one join link per event, free registration with per-date caps and no monthly ceiling, the published running order, built-in analytics, the embeddable calendar, two-way calendar sync and scanning people in at the door are all free forever. Pro at '.plan_price($proMonthly).' a month is what lets a ticket carry a price, and it adds the live check-in dashboard and the rest of the door tooling, and there are zero platform fees on ticket sales at any level. You can also selfhost Event Schedule on your own server, where every Enterprise feature is included.',
            ],
            [
                'q' => 'Can people get a reminder without registering?',
                'a' => 'Yes, once you switch on the free "Notify me" card. Somebody not ready to take a place can then press "Tell me if anything changes" on the event page and leave an email address, nothing else. They get a reminder shortly before the night, a notice if it is cancelled, and any change notice you choose to send, such as a new stream link, and every one of those emails unsubscribes in one click. Each Friday of a weekly series is its own list, it does not sign them up to your schedule, and it is free on every plan. Anyone who wants every date can subscribe to your schedule\'s live calendar feed instead, which updates itself and costs no email address.',
            ],
        ];

        $dotSections = [
            ['top', 'The screening'],
            ['why', 'Not a link'],
            ['order', 'The running order'],
            ['house', 'The house count'],
            ['link', 'The one socket'],
            ['reach', 'The call sheet'],
            ['tickets', 'When you charge'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Roll it'],
        ];

        $wpArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $wpDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $wpLink = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>';
        $wpSync = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';

        // A square of modules that reads as a code to scan: three finder squares, and the rest
        // switched on by a fixed rule. It encodes nothing and is only ever shown as decoration.
        $wpQrPath = '';
        for ($qy = 0; $qy < 21; $qy++) {
            for ($qx = 0; $qx < 21; $qx++) {
                $qOn = (($qx * 7 + $qy * 13 + $qx * $qy + intdiv($qx * $qx, 3)) % 5) < 2;
                foreach ([[0, 0], [14, 0], [0, 14]] as [$fx, $fy]) {
                    if ($qx >= $fx - 1 && $qx <= $fx + 7 && $qy >= $fy - 1 && $qy <= $fy + 7) {
                        $ring = max(abs($qx - ($fx + 3)), abs($qy - ($fy + 3)));
                        $qOn = $ring <= 1 || $ring === 3;
                    }
                }
                if ($qOn) {
                    $wpQrPath .= 'M'.$qx.' '.$qy.'h1v1h-1z';
                }
            }
        }
        $wpQr = '<svg viewBox="0 0 21 21" shape-rendering="crispEdges" fill="currentColor" aria-hidden="true"><path d="'.$wpQrPath.'" /></svg>';

        // The slate over each scene: stripes, then the fields a clapper carries.
        $wpSlate = fn (int $scene, string $name) => '<div class="wp-slate" data-reveal>'
            .'<i class="wp-slate-sticks" aria-hidden="true"></i>'
            .'<span class="wp-slate-cell wp-slate-prod" aria-hidden="true"><i>Prod.</i>Movie Night</span>'
            .'<span class="wp-slate-cell" aria-hidden="true"><i>Scene</i>'.str_pad((string) $scene, 2, '0', STR_PAD_LEFT).'</span>'
            .'<span class="wp-slate-cell" aria-hidden="true"><i>Take</i>1</span>'
            .'<p class="wp-slate-cell"><i aria-hidden="true">Title</i>'.e($name).'</p>'
            .'</div>';

        // The top bar of a frame: edge print, and the scene it belongs to.
        $wpBars = fn (int $scene) => '<div class="wp-bar" aria-hidden="true"><span>Event Schedule safety film</span><span>Sc '.str_pad((string) $scene, 2, '0', STR_PAD_LEFT).' / Tk 1</span></div>';
    @endphp

    <div id="wp">

        <!-- ============================================================ -->
        <!-- Scene 1. The leader, then the title card                     -->
        <!-- ============================================================ -->
        <section id="top" class="wp-hero">
            <div class="wp-wrap">
                <div class="wp-screen">
                    <div class="wp-bar" aria-hidden="true"><span>Event Schedule presents</span><span>Reel 1 &middot; Sc 01 / Tk 1</span></div>
                    <div class="wp-pic wp-pic-hero">
                        <div class="wp-grain wp-grain-live" aria-hidden="true"></div>
                        <i class="wp-scratch" aria-hidden="true"></i>
                        <i class="wp-cardrule" aria-hidden="true"></i>
                        <div class="wp-leader" aria-hidden="true">
                            <i class="wp-leader-sweep"></i>
                            <i class="wp-leader-ring"></i>
                            <b>3</b>
                            <b>2</b>
                        </div>

                        <div class="wp-title wp-weave">
                            <h1 class="wp-h1">
                                <x-marketing.hero-eyebrow class="wp-eyebrow es-fade-up es-d-1">Event schedule for watch parties</x-marketing.hero-eyebrow>
                                <span class="es-mask"><span class="es-mask-line">The link takes ten seconds.</span></span>
                                <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="wp-y">night</span> is the work.</span></span>
                            </h1>
                        </div>

                        <p class="wp-sub wp-sub-lede es-fade-up es-d-3">
                            <span>A watch party has a start time, a shape, a door that only fits so many, and a list of who said they were coming.</span>
                            <span>Event Schedule holds all four, free, and takes nothing at the door.</span>
                        </p>
                    </div>
                    <div class="wp-bar wp-bar-cta">
                        <a href="#order" class="wp-btn wp-btn-ghost">
                            See the running order
                            {!! $wpDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="wp-btn">
                            Create your screening schedule
                            {!! $wpArrow !!}
                        </a>
                    </div>
                </div>

                <!-- The running order, as a strip of film drawn to scale. -->
                <div class="wp-reelcard" data-reveal>
                    <div class="wp-reelcard-head">
                        <p class="wp-reel-title">Friday Movie Night</p>
                        <span class="wp-mono">Fri Nov 13</span>
                        <p class="wp-reel-note">Doors 7:45 PM &middot; out by 10:32 &middot; drawn to scale</p>
                    </div>
                    <ol class="wp-reel">
                        @foreach ($order as $oi => [$oTime, $oName, $oMin, $oMain])
                            <li class="wp-fr @if ($oMain) wp-fr-main @endif" style="--min: {{ $oMin }};">
                                <span class="wp-fr-n">{{ $oName }}</span>
                                <span class="wp-fr-x"><span>{{ $oTime }}</span> <span>{{ $oMin }}m</span></span>
                            </li>
                        @endforeach
                    </ol>
                    <p class="wp-reel-cap">
                        The feature is 112 of 167 minutes. The other 55 are doors, introduction and the conversation afterwards, and they are the reason people come back. Publish them and nobody has to ask when the film actually starts.
                    </p>
                </div>
            </div>

            <!-- Screening-type ticker -->
            <div class="wp-ticker">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach (['Premiere Screenings', 'Movie Nights', 'Sports Watch Parties', 'Series Finales', 'Documentary Screenings', 'Gaming Events', 'Reaction Streams', 'Marathon Nights'] as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="wp-tick">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 2. Not a link                                          -->
        <!-- ============================================================ -->
        <section id="why" class="wp-scene">
            <div class="wp-wrap">
                {!! $wpSlate(2, 'The unit') !!}
                <div class="wp-screen" data-reveal>
                    {!! $wpBars(2) !!}
                    <div class="wp-pic">
                        <div class="wp-far" aria-hidden="true"><span>watch.yourdomain.com/friday</span></div>
                        <div class="wp-seats" aria-hidden="true"></div>
                        <h2 class="wp-sub wp-sub-2">
                            <span>Every watch party tool sells you the stream.</span> <span>None of them run the night.</span>
                        </h2>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>

                <div class="wp-trio" data-reveal-group="110">
                    <div class="wp-fig" data-reveal>
                        <p class="wp-kick">The socket</p>
                        <h3>
                            <span data-count-to="1">1</span> link
                        </h3>
                        <p>An online event has one Event URL field and it takes any link. No platform to connect, so nothing to reconnect when you switch.</p>
                    </div>
                    <div class="wp-fig" data-reveal>
                        <p class="wp-kick">The door</p>
                        <h3>
                            <span data-count-to="120">120</span> places
                        </h3>
                        <p>Free registration with a cap, counted for each date on its own. A full Friday does not close the following one.</p>
                    </div>
                    <div class="wp-fig" data-reveal>
                        <p class="wp-kick">The take</p>
                        <h3>
                            <span>{{ plan_price(0) }}</span> platform fees
                        </h3>
                        <p>When you charge, you charge through your own Stripe or PayPal account and keep the lot. That is true on every plan, including free.</p>
                    </div>
                </div>

                <p class="wp-next wp-body" data-reveal>
                    The night is the unit. Everything below hangs off it.
                    <a href="#order">
                        Start with its shape
                        {!! $wpDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 3. The running order                                   -->
        <!-- ============================================================ -->
        <section id="order" class="wp-scene wp-alt">
            <div class="wp-wrap">
                {!! $wpSlate(3, 'The running order') !!}
                <div class="wp-screen" data-reveal>
                    {!! $wpBars(3) !!}
                    <div class="wp-pic wp-pic-order">
                        <div class="wp-burn">
                            <strong>Marathon night</strong>
                            <span>Sat Nov 21 &middot; 6h 05m</span>
                        </div>
                        @php
                            $marathon = [
                                ['5:30 PM', 'Doors and snacks', 30, false],
                                ['6:00 PM', 'Part one', 95, true],
                                ['7:35 PM', 'Interval', 20, false],
                                ['7:55 PM', 'Part two', 108, true],
                                ['9:43 PM', 'Interval', 15, false],
                                ['9:58 PM', 'Part three', 97, true],
                            ];
                        @endphp
                        <div class="wp-reel-tilt">
                            <ol class="wp-reel">
                                <li class="wp-fr wp-fr-blank" aria-hidden="true"></li>
                                @foreach ($marathon as $mi => [$mTime, $mName, $mMin, $mMain])
                                    <li class="wp-fr @if ($mMain) wp-fr-main @endif" style="--min: {{ $mMin }};">
                                        <span class="wp-fr-n">{{ $mName }}</span>
                                        <span class="wp-fr-x"><span>{{ $mTime }}</span> <span>{{ $mMin }}m</span></span>
                                    </li>
                                @endforeach
                                <li class="wp-fr wp-fr-blank" aria-hidden="true"></li>
                            </ol>
                        </div>
                        <h2 class="wp-sub">
                            A screening has a <span class="wp-nb">shape.</span>
                        </h2>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>

                <div class="wp-body">
                    <p class="wp-lede" data-reveal>
                        Add the parts of the night to the event, each with its own start and end time, and they publish on the event page underneath it. Free on every plan.
                    </p>
                    <div class="wp-grid-3" data-reveal-group="90">
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>Doors before the feature</h3>
                                <span class="wp-tag">Free</span>
                            </div>
                            <p>Fifteen minutes of people arriving is not dead time, it is the reason a watch party is not just watching. Put it on the sheet so people know to turn up for it.</p>
                        </div>
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>Discussion after it</h3>
                                <span class="wp-tag">Free</span>
                            </div>
                            <p>The half hour afterwards is the part regulars come back for. Give it a time and it stops being an accident that some people miss.</p>
                        </div>
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>A long sheet, scanned</h3>
                                <span class="wp-tag wp-tag-ent">Enterprise</span>
                            </div>
                            <p>For a marathon or a festival day with a dozen slots, point your phone's camera at the printed agenda and have the parts read off it instead of typing each one.</p>
                        </div>
                    </div>
                    <p class="wp-reel-cap" data-reveal>
                        Drawn to scale again, because the intervals are the thing people plan their evening around. Six hours is a commitment, and a sheet that shows where the breaks fall is the difference between a full room at 10 PM and an empty one.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 4. The house count: a real record, per date            -->
        <!-- ============================================================ -->
        <section id="house" class="wp-scene">
            <div class="wp-wrap">
                {!! $wpSlate(4, 'The house count') !!}
                <div class="wp-screen" data-reveal>
                    {!! $wpBars(4) !!}
                    <div class="wp-pic">
                        <div class="wp-doors" aria-hidden="true">
                            @foreach ($house as [$hDate, $hSold, $hCap])
                                <div class="wp-door">
                                    <span class="wp-door-date">{{ $hDate }}</span>
                                    <i class="wp-house" style="--full: {{ intdiv($hSold, 12) }}; --rem: {{ $hSold % 12 }};"></i>
                                    <span class="wp-door-left @if ($hCap - $hSold <= 5) wp-door-low @endif">{{ $hCap - $hSold }} left</span>
                                </div>
                            @endforeach
                        </div>
                        <h2 class="wp-sub">
                            One event. <span class="wp-nb">Four different doors.</span>
                        </h2>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>

                <div class="wp-body">
                    <p class="wp-lede" data-reveal>
                        A weekly movie night is one recurring event, and the cap you set is counted for each Friday on its own. So this is what the door actually looks like.
                    </p>

                    <div class="wp-card wp-sheet" data-reveal>
                        <table class="wp-table">
                            <caption class="sr-only">Friday Movie Night: registrations against a 120 place cap, for each of the next four dates</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col" class="wp-num">Registered</th>
                                    <th scope="col" class="wp-num">Cap</th>
                                    <th scope="col" class="wp-num">Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($house as $hi => [$hDate, $hSold, $hCap])
                                    @php
                                        $hLeft = $hCap - $hSold;
                                        $hPct = round($hSold / $hCap * 100);
                                    @endphp
                                    <tr>
                                        <th scope="row">{{ $hDate }}</th>
                                        <td class="wp-num">{{ $hSold }}</td>
                                        <td class="wp-num">{{ $hCap }}</td>
                                        <td class="wp-num @if ($hLeft <= 5) wp-low @endif">{{ $hLeft }}</td>
                                    </tr>
                                    <tr class="wp-meter-row">
                                        <td colspan="4">
                                            <div class="wp-meter" aria-hidden="true"><i style="--pct: {{ $hPct }}%; --i: {{ $hi }};"></i></div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="wp-fine">
                            Same event, same 120 place cap, four separate counts. The remaining number is shown on the registration form as places go, and one email address can only take a place once per date.
                        </p>
                    </div>

                    <div class="wp-grid-3" data-reveal-group="90">
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>Name and email</h3>
                                <span class="wp-tag">Free</span>
                            </div>
                            <p>That is the whole form, plus a phone number if you choose to ask for one. No account needed, so nobody bounces off a sign-up wall on their way to your movie night.</p>
                        </div>
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>Ask one more thing</h3>
                                <span class="wp-tag wp-tag-pro">Pro</span>
                            </div>
                            <p>Add your own questions to the form as text, a date, a toggle, a dropdown or a multi-select, and mark the ones you need answered. "Seen it before?" is a better icebreaker than anything you can improvise once people are in.</p>
                        </div>
                        <div class="wp-card wp-card-plain" data-reveal>
                            <div class="wp-card-head">
                                <h3>Not a viewer count</h3>
                            </div>
                            <p>This is a registration list. Event Schedule never touches your stream and cannot tell you how many people are watching right now. Anything that claims to would have to sit inside the platform.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 5. The one socket: the booth, the beam, the lit page   -->
        <!-- ============================================================ -->
        <section id="link" class="wp-scene wp-alt">
            <div class="wp-wrap">
                {!! $wpSlate(5, 'The one socket') !!}
                <div class="wp-screen" data-reveal>
                    {!! $wpBars(5) !!}
                    <div class="wp-pic wp-pic-link">
                        <div class="wp-beam" aria-hidden="true"></div>
                        <div class="wp-motes" aria-hidden="true">
                            <i style="--x: 36%; --y: 30%; --d: 9s;"></i>
                            <i style="--x: 41%; --y: 22%; --s: 2px; --d: 12s; --w: -3s;"></i>
                            <i style="--x: 45%; --y: 46%; --d: 10s; --w: -6s;"></i>
                            <i style="--x: 49%; --y: 28%; --s: 4px; --d: 14s; --w: -2s;"></i>
                            <i style="--x: 52%; --y: 60%; --s: 2px; --d: 11s; --w: -7s;"></i>
                            <i style="--x: 54%; --y: 38%; --d: 13s; --w: -5s;"></i>
                            <i style="--x: 47%; --y: 66%; --s: 2px; --d: 15s; --w: -9s;"></i>
                        </div>

                        <!-- In the booth: what the host fills in -->
                        <div class="wp-booth" aria-hidden="true">
                            <span class="wp-booth-k">Event URL</span>
                            <div class="wp-booth-f" dir="ltr">
                                {!! $wpLink !!}
                                <span>https://watch.yourdomain.com/friday</span>
                            </div>
                        </div>

                        <!-- Out front: the lit page. The same in both modes. -->
                        <div class="wp-lit" aria-hidden="true">
                            <p class="wp-lit-k">You are registered</p>
                            <p class="wp-lit-t">Friday Movie Night</p>
                            <p class="wp-lit-d">Fri Nov 13 &middot; 8:00 PM</p>
                            <div class="wp-lit-l" dir="ltr">
                                <span>watch.yourdomain.com/friday</span>
                            </div>
                            <div class="wp-lit-q">
                                <i class="wp-qr">{!! $wpQr !!}</i>
                                <div>
                                    <p>Scan for entry</p>
                                    <p>The same code whether the night is free or ticketed</p>
                                </div>
                            </div>
                        </div>

                        <h2 class="wp-sub">
                            One field in the booth. <span class="wp-nb">One lit page out front.</span>
                        </h2>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>

                <div class="wp-body wp-duo">
                    <div data-reveal>
                        <p class="wp-kick">What you fill in</p>
                        <p>
                            That is the integration. Whatever is behind that link is between you and the platform, which is why nothing here breaks when you move from one to another.
                        </p>
                        <ul class="wp-ticks">
                            <li>
                                <span>In your public calendar an online event shows its link's host where a venue name would go, so the row reads as online at a glance.</span>
                            </li>
                            <li>
                                <span>There is no platform picker and no connected-account list, so there is nothing to re-authorise at 7:40 on a Friday.</span>
                            </li>
                            <li>
                                <span>A hybrid night can have a venue as well, for the people who want to watch it in a room together.</span>
                            </li>
                        </ul>
                    </div>
                    <div data-reveal style="--reveal-delay: 0.1s;">
                        <p class="wp-kick">What they get</p>
                        <p>
                            This is the page the confirmation email links to, and the join link is on it. It carries a QR code too, so a hybrid night can scan the same registration in at the door, and scanning is free on every plan.
                        </p>
                        <p class="wp-fine">
                            To be exact about it: times on your public page are shown in your schedule's timezone. The .ics file for each date is stamped in UTC, so that is the one to trust and the one to tell people to use.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 6. The call sheet: who came, and how you reach them    -->
        <!-- ============================================================ -->
        <section id="reach" class="wp-scene">
            <div class="wp-wrap">
                {!! $wpSlate(6, 'The call sheet') !!}
                <div class="wp-screen" data-reveal>
                    {!! $wpBars(6) !!}
                    <div class="wp-pic wp-pic-reach">
                        <div class="wp-roll" aria-hidden="true">
                            <div class="wp-roll-in">
                                @for ($rollCopy = 0; $rollCopy < 2; $rollCopy++)
                                    <b>In order of appearance</b>
                                    @foreach ([[62, 78], [48, 64], [70, 86], [55, 58], [66, 72], [44, 90], [74, 66], [58, 80], [50, 62], [68, 74]] as [$rollA, $rollB])
                                        <span><i style="--a: {{ $rollA }}%;"></i><i style="--b: {{ $rollB }}%;"></i></span>
                                    @endforeach
                                @endfor
                            </div>
                        </div>
                        <div class="wp-follow" aria-hidden="true">
                            <i class="wp-qr">{!! $wpQr !!}</i>
                            <span>Follow</span>
                        </div>
                        <h2 class="wp-sub">
                            You own the <span class="wp-nb">list.</span>
                        </h2>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>

                <div class="wp-body">
                    <p class="wp-lede" data-reveal>
                        Registration gives you a name and an email for every place taken. Following gives you a standing audience. Both are free, and both are yours to email.
                    </p>

                    <div class="wp-grid-3" data-reveal-group="90">
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>Everyone who registered</h3>
                                <span class="wp-tag">Free</span>
                            </div>
                            <p>Scope a newsletter to one screening and it resolves to the people who took a place for it. That is the email you send the afternoon before with the viewing notes.</p>
                        </div>
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>Everyone who follows</h3>
                                <span class="wp-tag">Free</span>
                            </div>
                            <p>A Follow button on your schedule, and a downloadable QR code for the schedule page you can put on screen at the end of the night, so the room can follow while the credits roll.</p>
                        </div>
                        <div class="wp-card" data-reveal>
                            <div class="wp-card-head">
                                <h3>One strand of it</h3>
                                <span class="wp-tag">Free</span>
                            </div>
                            <p>Or a sub-schedule, so the documentary crowd hears about documentaries and the sports crowd does not. Pasted lists and the ticket waitlist work as targets too.</p>
                        </div>
                    </div>

                    <div class="wp-letter">
                        <div data-reveal>
                            <h3>The newsletter is the one you write</h3>
                            <p>
                                A screening you add does reach confirmed email subscribers on its own, as a batched digest. What never sends itself is the newsletter: no automation builder, no branching sequence, no drip. You write that one and you send it, which is slower and also the reason your list does not quietly rot. Open and click rates come back afterwards so you can tell whether Friday's note actually landed.
                            </p>
                            <p>
                                Automatic mail is kept for the things a person asked for: the confirmation every registrant gets the moment they take a place, the note to the waitlist when a full night frees up, and a reminder before the night for anybody who left an address with "Tell me if anything changes" instead of registering. And notifications run the other way too: when somebody asks you to add their screening to your calendar, you are the one who gets the email.
                            </p>
                        </div>
                        <div class="wp-card" data-reveal style="--reveal-delay: 0.1s;">
                            <p class="wp-kick">Emails a month</p>
                            <dl class="wp-allow">
                                <div>
                                    <dt>Free</dt>
                                    <dd>10</dd>
                                </div>
                                <div>
                                    <dt>Pro</dt>
                                    <dd>100</dd>
                                </div>
                                <div>
                                    <dt>Enterprise</dt>
                                    <dd>1,000</dd>
                                </div>
                            </dl>
                            <p class="wp-fine">Each recipient counts as one, so a note to sixty people is sixty. Selfhosted installs send through your own mail server and are not counted at all.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 7. When you charge                                     -->
        <!-- ============================================================ -->
        <section id="tickets" class="wp-scene wp-alt">
            <div class="wp-wrap">
                {!! $wpSlate(7, 'When you charge') !!}
                <div class="wp-screen" data-reveal>
                    {!! $wpBars(7) !!}
                    <div class="wp-pic wp-pic-tix">
                        <div class="wp-stubs" aria-hidden="true">
                            <div class="wp-stub"><b>Admit one</b><i>Premiere night</i><em>No. 0241</em></div>
                            <div class="wp-stub"><b>Admit one</b><i>Premiere night</i><em>No. 0242</em></div>
                            <div class="wp-stub"><b>Admit one</b><i>Premiere night</i><em>No. 0243</em></div>
                        </div>
                        <p class="wp-cut">
                            <span>Event Schedule's cut</span>
                            <strong>{{ plan_price(0) }}</strong>
                        </p>
                        <h2 class="wp-sub">
                            Nothing is taken at the <span class="wp-nb">door.</span>
                        </h2>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>

                <div class="wp-body wp-duo wp-duo-wide">
                    <div data-reveal>
                        <p class="wp-lede">
                            A paid premiere, a benefit screening, a festival day pass. A ticket that carries a price is Pro, at {{ plan_price($proMonthly) }} a month, which opens the door tooling with it; free registration is unlimited without it. Payments run through your own Stripe or PayPal account, and Event Schedule takes zero platform fees on every plan.
                        </p>
                        <ul class="wp-ticks">
                            <li>
                                <span>Named ticket types, each with its own price, quantity and sales window. Inventory is counted per date, like the registration cap.</span>
                            </li>
                            <li>
                                <span>Your own block of notes goes out in every buyer's confirmation email, so the join link and the house rules land with the ticket instead of in a separate mail you have to remember to send.</span>
                            </li>
                            <li>
                                <span>A pass valid across a run of screenings, once each, for a festival week or a season of Sunday documentaries. On Pro. Scanning them in at the door is free.</span>
                            </li>
                            <li>
                                <span>A waitlist that tells people when a full screening frees up, free while the door is free registration and Pro once you are selling tickets. Promo codes and gift cards are Pro if you want them.</span>
                            </li>
                            <li>
                                <span>Refunds come off the Sales page. A Stripe or PayPal payment goes back through the provider, in full or in part, and a full refund frees the place for somebody else.</span>
                            </li>
                        </ul>
                        <p class="wp-fine" style="margin-top: 1.4rem;">
                            The door here is a count, not a chart: priced ticket types with quantities, and nobody picking a specific seat. A screening room that really does allocate seats can draw one on Enterprise.
                        </p>
                    </div>

                    <div class="wp-card wp-board" data-reveal style="--reveal-delay: 0.1s;">
                        <div class="wp-card-head">
                            <p class="wp-reel-title">Premiere night</p>
                            <span class="wp-tag">Free, then Pro</span>
                        </div>
                        <table class="wp-table">
                            <caption class="sr-only">Ticket types for a premiere screening, with prices and quantities</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Ticket type</th>
                                    <th scope="col" class="wp-num">Price</th>
                                    <th scope="col" class="wp-num">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ([['Watch at home', '$8', '250'], ['In the room', '$18', '60'], ['Supporter', '$40', '25'], ['Festival pass, 6 nights', '$60', '40']] as [$tName, $tPrice, $tQty])
                                    <tr>
                                        <th scope="row">{{ $tName }}</th>
                                        <td class="wp-num">{{ $tPrice }}</td>
                                        <td class="wp-num">{{ $tQty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="wp-fine">
                            Stripe or PayPal charges its own standard processing fee on each payment, the same as it would anywhere. Event Schedule adds nothing on top, on any plan.
                        </p>
                        <p class="wp-fine">
                            See all <a href="{{ marketing_url('/features/ticketing') }}">ticketing features</a>.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 8. Everything else                                     -->
        <!-- ============================================================ -->
        <section id="rest" class="wp-scene">
            <div class="wp-wrap">
                {!! $wpSlate(8, 'Everything else') !!}
                <div class="wp-screen" data-reveal>
                    {!! $wpBars(8) !!}
                    <div class="wp-pic">
                        <div class="wp-ballot" aria-hidden="true">
                            <p class="wp-ballot-q">What are we watching in December?</p>
                            <div class="wp-ballot-row">
                                @foreach ([['The one with the boat', 44], ['Something in black and white', 31], ['Suggested by a viewer', 25]] as $pi => [$pName, $pPct])
                                    <div class="wp-ballot-card" style="--pct: {{ $pPct }};">
                                        <span>{{ chr(65 + $pi) }}</span>
                                        <b>{{ $pName }}</b>
                                        <span>{{ $pPct }}%</span>
                                        <i></i>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <h2 class="wp-sub">
                            The rest of the <span class="wp-nb">booth.</span>
                        </h2>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>

                {{-- Six lobby cards. Two columns from a tablet (three full rows), three on a
                     wide screen (two full rows), so no corner is ever left empty. --}}
                <div class="wp-body wp-lobby" data-reveal-group="80">
                    <!-- Analytics -->
                    <div class="wp-card" data-reveal>
                        <div class="wp-card-head">
                            <p class="wp-kick">Analytics</p>
                            <span class="wp-tag">Free</span>
                        </div>
                        <h3>Which post filled the room</h3>
                        <p>
                            Views by device, where the traffic came from, campaign tags on the links you post, rough locations and clicks on your social buttons. Built in, on every plan, with no third-party script and nothing to bolt on.
                        </p>
                        <div class="wp-mini" aria-hidden="true">
                            <p class="wp-kick">Where they came from</p>
                            <div class="wp-bars">
                                @foreach ([['Community chat', 62], ['Newsletter', 48], ['Search', 27], ['Direct', 19]] as $si => [$sName, $sPct])
                                    <div>
                                        <span>{{ $sName }}</span>
                                        <span>{{ $sPct }}</span>
                                        <div class="wp-meter"><i style="--pct: {{ $sPct }}%; --i: {{ $si }};"></i></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Embed -->
                    <div class="wp-card" data-reveal>
                        <div class="wp-card-head">
                            <p class="wp-kick">Embed</p>
                            <span class="wp-tag">Free</span>
                        </div>
                        <h3>On the site you already have</h3>
                        <p>Drop the calendar into your own page as an iframe and it keeps itself current. The registration form embeds the same way, free, and the ticket form on Pro.</p>
                        <p class="wp-code" dir="ltr" aria-hidden="true">&lt;iframe src="yourparty<wbr>.eventschedule.com<wbr>/?embed=true"&gt;</p>
                    </div>

                    <!-- Sub-schedules -->
                    <div class="wp-card" data-reveal>
                        <div class="wp-card-head">
                            <p class="wp-kick">Sub-schedules</p>
                            <span class="wp-tag">Free</span>
                        </div>
                        <h3>Strands on one link</h3>
                        <p>Keep the documentary series, the sports nights and the game launches apart with their own names and colours, so nobody reads the whole year to find one strand.</p>
                        <p class="wp-fine">A sub-schedule sorts and colours. It does not hide anything: to keep a screening off the public page while you plan it, leave it as a draft.</p>
                    </div>

                    <!-- Calendar sync -->
                    <div class="wp-card" data-reveal>
                        <div class="wp-card-head">
                            <p class="wp-kick">Calendar sync</p>
                            <span class="wp-tag">Free</span>
                        </div>
                        <h3>Both directions, three calendars</h3>
                        <p>
                            Google, Outlook or Microsoft 365, and anything speaking CalDAV. Move a screening in your own calendar and it moves here; you choose what happens locally when something is deleted over there. See <a href="{{ marketing_url('/features/calendar-sync') }}">calendar sync</a>.
                        </p>
                        <div class="wp-mini">
                            <div class="wp-sync" aria-hidden="true">
                                <div class="wp-sync-head">
                                    <span>Your schedule</span>
                                    <span>two-way</span>
                                </div>
                                @foreach (['Google Calendar', 'Outlook / M365', 'CalDAV'] as $ci => $cName)
                                    <div class="wp-sync-row">
                                        {!! $wpSync !!}
                                        <span>{{ $cName }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="wp-fine" style="margin-top: 0.7rem;">Plus a .ics download on every event and every date of a series.</p>
                        </div>
                    </div>

                    <!-- After the night -->
                    <div class="wp-card" data-reveal>
                        <div class="wp-card-head">
                            <p class="wp-kick">Afterwards</p>
                            <span class="wp-tag">Free</span>
                        </div>
                        <h3>Photos and reactions</h3>
                        <p>People who were there can post photos, YouTube clips and comments on the event with just a name and an email, and everything waits in an approval queue before it appears.</p>
                        <p class="wp-fine">Twenty-five photos per schedule on the free plan. Pro takes the cap off and adds a bulk download when you want the lot.</p>
                    </div>

                    <!-- Polls and feedback -->
                    <div class="wp-card" data-reveal>
                        <div class="wp-card-head">
                            <p class="wp-kick">Polls and feedback</p>
                            <span class="wp-tag wp-tag-pro">Pro</span>
                        </div>
                        <h3>Let the room pick the next one</h3>
                        <p>
                            Put a poll on the event and let viewers vote, or let them add their own suggestions, which land in a queue for you to approve rather than going straight up. After the night, ask for a rating and a comment. See <a href="{{ marketing_url('/features/polls') }}">polls</a>.
                        </p>
                        <p class="wp-fine">Viewer suggestions wait for your approval. On eventschedule.com, an opt-in email flags pending ones once your schedule has its own email settings.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Three steps, as three consecutive frames                     -->
        <!-- ============================================================ -->
        <section class="wp-scene wp-alt wp-steps-sec">
            <div class="wp-wrap">
                <h2 class="wp-h2" data-reveal>
                    How to host a watch party online in <span class="wp-hot">three steps</span>
                </h2>
            </div>
            <div class="wp-strip" data-reveal>
                <div class="wp-strip-in">
                    <i class="wp-strip-blank" aria-hidden="true"></i>
                    @foreach ([['1', 'Open the doors', 'Create the event, paste your join link into the one Event URL field, turn on free registration and set the cap for the room you can actually handle.'], ['2', 'Post the running order', 'Add the parts of the night with their times: doors and chat, the introduction, the feature, the discussion afterwards. They publish with the event.'], ['3', 'Roll it', 'Every registrant gets a confirmation email linking to their own page, with the join link on it. Afterwards, collect photos and comments from the people who were there.']] as [$stepNum, $stepTitle, $stepBody])
                        <div class="wp-strip-frame">
                            <span class="wp-ring">{{ $stepNum }}</span>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </div>
                    @endforeach
                    <i class="wp-strip-blank" aria-hidden="true"></i>
                </div>
                <p class="wp-edge" aria-hidden="true">
                    <span>Event Schedule safety film</span><span>5219 &middot; 12A</span><span>Event Schedule safety film</span><span>5219 &middot; 13A</span><span>Event Schedule safety film</span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- End credits: perfect for, key features, related pages        -->
        <!-- ============================================================ -->
        <section id="who" class="wp-credits">
            <div class="wp-wrap wp-credits-in">
                <p class="wp-credits-k" aria-hidden="true">The cast</p>
                <h2 class="wp-credits-h" data-reveal>
                    Watch party software for <span>every community</span>
                </h2>
                <p class="wp-credits-lede" data-reveal>
                    Whether it is a film club or a sports viewing party, Event Schedule works for you. Also see Event Schedule for <a href="{{ marketing_url('/for-live-concerts') }}">Live Concerts</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}">Virtual Conferences</a>.
                </p>

                @php
                    $wpCast = [
                        ['Film Clubs & Cinephiles', 'Classic screenings, director retrospectives and themed movie nights. Put the introduction and the discussion on the running order, where the club actually lives.', 'for-film-clubs-cinephiles'],
                        ['Content Creators & YouTubers', 'Premiere a new video with your audience, host a reaction watch-along, or sell tickets to an exclusive screening. One link, whatever you are streaming through.', 'for-content-creators-watch-parties'],
                        ['Sports Fan Communities', 'Game day watch parties, playoff screenings and draft nights. Free registration with a cap tells you how many are coming before the kickoff you cannot move.', 'for-sports-fan-watch-parties'],
                        ['Gaming Communities', 'Esports watch parties, launch nights and tournament screenings. Set the whole season up as one recurring event with the weeks you are skipping taken out.', 'for-gaming-community-watch-parties'],
                        ['Corporate & Team Building', 'Team movie nights and company screenings for people in different offices. The .ics file for each date is stamped in UTC, so everyone\'s own calendar shows their own hour.', 'for-corporate-team-watch-parties'],
                        ['Education & Documentary Groups', 'Documentary screenings with discussion, film series and classroom viewings. The discussion is a part of the event with its own time, not an afterthought.', 'for-education-documentary-watch-parties'],
                    ];
                @endphp
                <ul class="wp-cast">
                    @foreach ($wpCast as [$castName, $castBody, $castSlug])
                        @php $castPost = get_sub_audience_blog($castSlug); @endphp
                        <li class="wp-credit">
                            <h3>{{ $castName }}</h3>
                            <div>
                                <p>{{ $castBody }}</p>
                                @if ($castPost)
                                    <a href="{{ blog_url('/' . $castPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $castName }}">Learn more</a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <p class="wp-credits-k wp-credits-gap" aria-hidden="true">The crew</p>
                <h2 class="wp-credits-h" data-reveal>Key features</h2>
                @php
                    $wpCrew = [
                        ['Online Events', 'One join link per event, for whatever you are streaming through', marketing_url('/features/online-events')],
                        ['Recurring Events', 'A weekly movie night as one event, with the skipped weeks taken out', marketing_url('/features/recurring-events')],
                        ['Analytics', 'Views by device, referrers, campaign tags and locations', marketing_url('/features/analytics')],
                        ['Newsletters', 'Write it and send it, to followers or to one screening\'s registrants', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <ul class="wp-cast">
                    @foreach ($wpCrew as [$crewName, $crewBody, $crewUrl])
                        <li>
                            <a href="{{ $crewUrl }}" class="wp-credit wp-credit-link">
                                <strong>{{ $crewName }}</strong>
                                <span>{{ $crewBody }} {!! $wpArrow !!}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="wp-credits-more">
                    <a href="{{ marketing_url('/features') }}">
                        See all features
                        {!! $wpArrow !!}
                    </a>
                </p>

                <p class="wp-credits-k wp-credits-gap" aria-hidden="true">Also showing</p>
                <h2 class="wp-credits-h" data-reveal>Related pages</h2>
                <ul class="wp-cast">
                    @foreach ([['/for-live-concerts', 'Live Concerts'], ['/for-bars', 'Bars'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions']] as [$relHref, $relName])
                        <li>
                            <a href="{{ marketing_url($relHref) }}" class="wp-credit wp-credit-link">
                                <strong>For {{ $relName }}</strong>
                                <span>
                                    Read more
                                    {!! $wpArrow !!}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <p class="wp-credits-more">
                    <a href="{{ marketing_url('/use-cases') }}">
                        See all use cases
                        {!! $wpArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <div class="wp-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- Post-credits: the questions                                  -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="wp-scene">
            <div class="wp-wrap wp-faq-grid">
                <div class="wp-faq-head">
                    <p class="wp-kick" aria-hidden="true" data-reveal>Post-credits scene</p>
                    <h2 class="wp-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Frequently asked questions
                    </h2>
                    <p class="wp-lede" data-reveal style="--reveal-delay: 0.12s;">
                        What screening hosts ask before they move a series across.
                    </p>
                </div>
                <div class="wp-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq">
                            <summary>
                                <span class="wp-qa-no" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
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
        <!-- The last title card                                          -->
        <!-- ============================================================ -->
        <section id="claim" class="wp-scene wp-alt">
            <div class="wp-wrap">
                <div class="wp-screen" data-reveal>
                    <div class="wp-bar" aria-hidden="true"><span>The end</span><span>Coming soon</span></div>
                    <div class="wp-pic wp-pic-end" id="wp-end">
                        <div class="wp-grain wp-grain-live" aria-hidden="true"></div>
                        <i class="wp-scratch" aria-hidden="true"></i>
                        <i class="wp-cardrule" aria-hidden="true"></i>
                        <i class="wp-cue" aria-hidden="true"></i>
                        <div class="wp-end-in">
                            <p class="wp-end-k">Free forever</p>
                            <h2 class="wp-end-h wp-weave">
                                House lights down. <span class="wp-y wp-nb">Roll it.</span>
                            </h2>
                            <p class="wp-end-p">
                                The running order, the door and the list are free forever, and the list has no ceiling on it. {{ plan_price($proMonthly) }} a month is what puts a price on a seat, and nothing is taken at the door.
                            </p>
                            <div class="wp-claimrow">
                                <label for="es-claim-input" class="wp-sr">Your schedule name</label>
                                <div dir="ltr" class="es-claim wp-claim">
                                    <input id="es-claim-input" type="text" placeholder="your-party" autocomplete="off" spellcheck="false" maxlength="30">
                                    <span>.eventschedule.com</span>
                                </div>
                                <a href="{{ app_url('/sign_up?type=talent') }}" class="wp-btn">
                                    Get Started Free
                                    {!! $wpArrow !!}
                                </a>
                            </div>
                            <p class="wp-end-note">No credit card required</p>
                        </div>
                    </div>
                    <div class="wp-bar" aria-hidden="true"></div>
                </div>
            </div>
        </section>

        <!-- Scene selection: wide screens only -->
        <div class="wp-railbox">
            <nav class="wp-rail es-dotnav" aria-label="Page sections">
                <ol>
                    @foreach ($dotSections as [$sectionId, $sectionLabel])
                        <li>
                            <a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}">
                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                <span aria-hidden="true">{{ $sectionLabel }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <div class="wp-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- The finale throws popcorn, not the site's blue confetti. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var card = document.getElementById('wp-end');
            if (!card || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var popcorn = ['#ffe14d', '#fff6c2', '#f4f1e8', '#ffd21f'];
                    {{-- Its own cannon, drawn on the page's thread: the default one starts a blob worker, which the site's CSP refuses and logs. --}}
                    var throwIt = window.confetti.create(null, { resize: true });
                    [[62, 0.08], [118, 0.92]].forEach(function (shot) {
                        throwIt({ particleCount: 60, angle: shot[0], spread: 52, startVelocity: 50, scalar: 1.25, origin: { x: shot[1], y: 0.95 }, colors: popcorn, shapes: ['circle'], disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.6 });
            io.observe(card);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
