<x-marketing-layout>
    <x-slot name="title">Open Mic and Reading Schedules for Poets | Event Schedule</x-slot>
    <x-slot name="description">Run open mic sign-ups, reading series, and workshops from one link. Free registration with a capacity limit, and recurring dates that skip the holidays.</x-slot>
    <x-slot name="breadcrumbTitle">For Spoken Word</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('EB Garamond') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lekton') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Spoken Word"
        description="Run open mic sign-ups, reading series, and workshops from one link. Free registration with a capacity limit, recurring dates, and zero platform fees on tickets."
        audience="Poets, Storytellers, and Open Mic Hosts"
        keywords="open mic schedule, poetry reading calendar, open mic sign up sheet, spoken word event management, poetry slam scheduling, storytelling event calendar, free open mic software" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to run an open mic sign-up list with Event Schedule",
        "description": "Put your open mic list online in three steps.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Start the list",
                "text": "Add the mic as a recurring event, turn on registration, and set how many spots there are. Skip a date when the room is closed for a holiday."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Put the link in your bio, on the venue's site, and on the back of your chapbook, or embed the calendar on a page you already have."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Let them sign up",
                "text": "Poets take a spot themselves. You can see the list from anywhere, and the page stops taking names once every spot is gone."
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
           For-spoken-word "The Chapbook" styles. The page is a small-press
           pamphlet on a binder's board: a cover, then leaves. Every section
           is a spread of two pages with a running head and a folio, the
           headings are set as verse, and there are two inks, black and one
           vermilion.

           Everything is scoped under #sw and drawn with the page's own
           tokens. The shared es-* reveal system (marketing.css,
           marketing-home.js) still drives the entrances.
           ============================================================== */

        #sw {
            --sw-desk: #dcd4c3;
            --sw-leaf: #f8f4ea;
            --sw-leaf-2: #f1ecdf;
            --sw-ink: #1a1714;
            --sw-ink-2: #453e36;
            --sw-ink-3: #675e53;
            --sw-rule: rgba(26, 23, 20, 0.28);
            --sw-hair: rgba(26, 23, 20, 0.14);
            --sw-red: #c8371f;
            --sw-red-ink: #a82c16;
            --sw-btn: #bd3119;
            --sw-on-btn: #f8f3e8;
            --sw-gutter: rgba(70, 52, 24, 0.16);
            --sw-bite: 0 1px 0 rgba(255, 255, 255, 0.6);
            --sw-lift: 0 1px 0 rgba(26, 23, 20, 0.1), 0 1.4rem 2.6rem -1.6rem rgba(40, 28, 8, 0.55);
            --sw-outer: 5rem;
            --sw-inner: 3.5rem;
            --sw-serif: 'EB Garamond', 'Iowan Old Style', 'Palatino Linotype', Palatino, 'Book Antiqua', Georgia, serif;
            --sw-ital: 'Hoefler Text', Baskerville, 'Iowan Old Style', 'Palatino Linotype', Palatino, 'Book Antiqua', Georgia, serif;
            --sw-type: 'Lekton', 'Courier New', ui-monospace, monospace;
            --sw-tooth: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.24 0 0 0 0 0.17 0 0 0 0 0.08 0 0 0 0.16 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)'/%3E%3C/svg%3E");
            --sw-deckle-v: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='200'%3E%3Cpath d='M0,0 L3.9,0 L3.4,4 L2.2,7 L2.2,14 L2.2,21 L2.2,28 L2.2,31 L2.2,34 L2.2,41 L2.2,48 L2.9,52 L5.2,59 L5.7,66 L7.4,69 L7.4,72 L6.3,76 L6.5,80 L5.5,87 L3.9,96 L4.2,103 L3.6,107 L4.7,114 L2.4,121 L2.4,125 L2.2,132 L2.2,137 L2.2,143 L3.7,147 L5.2,156 L5.6,159 L5.5,166 L6.7,171 L7.3,176 L5.3,179 L3.6,185 L2.2,190 L2.2,196 L3.9,200 L0,200 Z'/%3E%3C/svg%3E");
            --sw-deckle-h: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='8'%3E%3Cpath d='M0,0 L0,7.2 L3,7.4 L10,7.4 L15,6.6 L20,7.1 L27,7.4 L30,7.4 L35,7.3 L44,5.0 L53,6.1 L62,6.5 L71,7.4 L76,7.4 L85,6.6 L91,5.9 L98,3.9 L101,2.4 L106,2.2 L110,2.2 L116,2.2 L122,2.2 L127,4.2 L133,6.1 L138,7.2 L143,7.4 L149,7.4 L153,5.2 L157,3.8 L161,2.2 L168,2.2 L173,2.2 L179,2.4 L186,2.7 L190,3.7 L197,6.1 L200,7.2 L200,0 Z'/%3E%3C/svg%3E");
            position: relative;
            background-color: var(--sw-desk);
            background-image: var(--sw-tooth);
            color: var(--sw-ink);
            font-family: var(--sw-serif);
            font-size: 1.22rem;
            line-height: 1.5;
            font-variant-numeric: oldstyle-nums proportional-nums;
            font-kerning: normal;
            font-synthesis: weight small-caps;
            hanging-punctuation: first;
            padding-bottom: 1px;
        }
        .dark #sw {
            --sw-desk: #0b0a09;
            --sw-leaf: #1a1815;
            --sw-leaf-2: #211e1a;
            --sw-ink: #ede6d6;
            --sw-ink-2: #cbc2b0;
            --sw-ink-3: #a1988a;
            --sw-rule: rgba(237, 230, 214, 0.3);
            --sw-hair: rgba(237, 230, 214, 0.14);
            --sw-red: #f0603f;
            --sw-red-ink: #f47d5f;
            --sw-btn: #f0603f;
            --sw-on-btn: #14110f;
            --sw-gutter: rgba(0, 0, 0, 0.48);
            --sw-bite: 0 1px 0 rgba(0, 0, 0, 0.8);
            --sw-lift: 0 0 0 1px rgba(237, 230, 214, 0.07), 0 1.4rem 2.6rem -1.4rem #000;
            --sw-tooth: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.95 0 0 0 0 0.9 0 0 0 0 0.8 0 0 0 0.07 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23n)'/%3E%3C/svg%3E");
        }

        /* The bar above takes the colour of the board the book lies on. */
        body > header.sticky {
            background-color: rgba(220, 212, 195, 0.9);
            border-bottom-color: rgba(26, 23, 20, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(11, 10, 9, 0.9);
            border-bottom-color: rgba(237, 230, 214, 0.12);
        }

        #sw ::selection { background: var(--sw-red); color: #fff; }
        #sw a:focus-visible,
        #sw summary:focus-visible,
        #sw input:focus-visible {
            outline: 2px solid var(--sw-red);
            outline-offset: 3px;
        }
        #sw p { text-wrap: pretty; }

        .sw-wrap { width: min(100% - 2rem, 78rem); margin-inline: auto; }

        /* ---------------------------------------------------------------
           The voices: roman, a borrowed italic, small capitals, and the
           typewriter for the small matter
           --------------------------------------------------------------- */
        .sw-it { font-family: var(--sw-ital); font-style: italic; }
        .sw-sc { font-variant-caps: all-small-caps; letter-spacing: 0.12em; }
        .sw-tw {
            font-family: var(--sw-type);
            font-weight: 700;
            font-size: 0.74rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-variant-numeric: lining-nums tabular-nums;
            line-height: 1.4;
        }
        .sw-red { color: var(--sw-red); }

        /* Verse: every line its own block, stepped by --i. */
        .sw-vl { display: block; padding-inline-start: calc(var(--i, 0) * var(--sw-step, 1.5em) + 1em); text-indent: -1em; }
        /* An opening quotation mark hangs in the margin, so the letters align. */
        .sw-vl-q { text-indent: -1.42em; }
        .sw-verse {
            font-weight: 400;
            font-size: clamp(2.05rem, 9.2cqi, 3.35rem);
            line-height: 1.07;
            letter-spacing: -0.008em;
            font-variant-ligatures: common-ligatures discretionary-ligatures;
            font-variant-numeric: oldstyle-nums;
            text-shadow: var(--sw-bite);
        }
        /* The lines of a heading take ink one after another as the leaf arrives. */
        html.es-anim #sw [data-reveal="ink"] { opacity: 1; }
        #sw [data-reveal="ink"] .sw-vl { transition: opacity 1.2s ease calc(var(--n, 0) * 0.2s + 0.1s); }
        html.es-anim #sw [data-reveal="ink"]:not(.is-revealed) .sw-vl { opacity: 0.14; }

        .sw-kick { display: block; color: var(--sw-ink-2); font-size: 1.08rem; }
        .sw-part {
            display: block;
            margin-bottom: 0.35rem;
            color: var(--sw-red);
            font-size: 1.55rem;
            letter-spacing: 0.08em;
            font-variant-numeric: lining-nums;
        }
        .sw-lede { color: var(--sw-ink-2); }
        .sw-kick + .sw-verse { margin-top: 1.3rem; }
        .sw-part + .sw-verse { margin-top: 0.9rem; }

        /* Links in running text, and the two the cover sends you away with. */
        #sw .sw-prose a,
        .sw-more {
            color: var(--sw-ink);
            text-decoration: underline;
            text-decoration-color: var(--sw-red);
            text-decoration-thickness: 1px;
            text-underline-offset: 0.22em;
            transition: color 0.2s ease;
        }
        #sw .sw-prose a:hover,
        .sw-more:hover { color: var(--sw-red-ink); }
        .sw-more { display: inline-flex; align-items: center; gap: 0.45rem; }
        .sw-more svg { width: 0.85em; height: 0.85em; flex: none; transition: translate 0.25s ease; }
        .sw-more:hover svg { translate: 0.2em 0; }
        .sw-more-down:hover svg { translate: 0 0.2em; }
        .sw-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.7rem 1.6rem 0.78rem;
            background: var(--sw-btn);
            color: var(--sw-on-btn);
            font-variant-caps: all-small-caps;
            font-weight: 700;
            font-size: 1.42rem;
            letter-spacing: 0.14em;
            line-height: 1.1;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22), 0 0.6rem 1.2rem -0.7rem rgba(60, 20, 5, 0.8);
            transition: background-color 0.25s ease, color 0.25s ease;
        }
        .sw-btn:hover { background: var(--sw-ink); color: var(--sw-leaf); }
        .sw-btn svg { width: 0.7em; height: 0.7em; flex: none; transition: translate 0.25s ease; }
        .sw-btn:hover svg { translate: 0.25em 0; }

        /* ---------------------------------------------------------------
           The ribbon marker: how far into the book you are
           --------------------------------------------------------------- */
        .sw-ribbon { display: none; }
        @supports (animation-timeline: scroll()) {
            @media (min-width: 1440px) {
                .sw-ribbon {
                    display: block;
                    position: fixed;
                    z-index: 30;
                    top: 4rem;
                    right: clamp(1.1rem, calc((100vw - 78rem) / 4), 3.5rem);
                    width: 0.7rem;
                    height: 46vh;
                    background: linear-gradient(90deg, #a82c16, #d2452c 45%, #b5321a);
                    clip-path: polygon(0 0, 100% 0, 100% 100%, 50% calc(100% - 0.5rem), 0 100%);
                    transform-origin: 50% 0;
                    animation: sw-ribbon linear both;
                    animation-timeline: scroll(root);
                    pointer-events: none;
                }
            }
        }
        @keyframes sw-ribbon {
            0% { height: 2.2rem; opacity: 1; }
            94% { height: 46vh; opacity: 1; }
            100% { height: 46vh; opacity: 0; }
        }

        /* ---------------------------------------------------------------
           The cover
           --------------------------------------------------------------- */
        .sw-hero { padding-block: clamp(2.25rem, 5vw, 4.25rem) clamp(3rem, 6vw, 5rem); overflow: clip; }
        .sw-hero-grid { display: grid; gap: 3rem; align-items: center; justify-items: center; }
        @media (min-width: 1000px) {
            .sw-hero-grid {
                width: min(100% - 2rem, 70rem);
                grid-template-columns: minmax(0, 32rem) minmax(0, 1fr);
                gap: clamp(3rem, 7vw, 6.5rem);
                justify-items: stretch;
            }
        }
        /* The pamphlet lifts off the board when you reach for it. */
        .sw-lift { position: relative; width: min(100%, 33rem); rotate: -1deg; perspective: 1600px; }
        .sw-cover {
            position: relative;
            transform-origin: 0 50%;
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.9s ease;
            padding: clamp(1.1rem, 3.4vw, 1.5rem);
            background-color: var(--sw-leaf);
            background-image: linear-gradient(to right, var(--sw-gutter), transparent 1.3rem), var(--sw-tooth);
            box-shadow:
                1px 1px 0 var(--sw-leaf-2), 2px 2px 0 var(--sw-rule),
                3px 3px 0 var(--sw-leaf), 4px 4px 0 var(--sw-rule),
                5px 5px 0 var(--sw-leaf-2), 6px 6px 0 var(--sw-hair),
                0 2.4rem 3.4rem -2rem rgba(40, 28, 8, 0.7);
        }
        @media (hover: hover) {
            .sw-lift:hover .sw-cover { transform: rotateY(-13deg); box-shadow: 1px 1px 0 var(--sw-leaf-2), 2px 2px 0 var(--sw-rule), 3px 3px 0 var(--sw-leaf), 4px 4px 0 var(--sw-rule), 5px 5px 0 var(--sw-leaf-2), 6px 6px 0 var(--sw-hair), 1.5rem 3rem 4rem -1.6rem rgba(40, 28, 8, 0.75); }
        }
        .dark .sw-cover { box-shadow: 1px 1px 0 #24211d, 2px 2px 0 #0b0a09, 3px 3px 0 #24211d, 4px 4px 0 #0b0a09, 5px 5px 0 #24211d, 0 2.4rem 3.4rem -1.6rem #000; }
        /* Two staples through the fold. */
        .sw-cover::before {
            content: "";
            position: absolute;
            left: -2px;
            top: 0;
            bottom: 0;
            width: 5px;
            background:
                linear-gradient(90deg, #7c7c7c, #f1f1f1 45%, #8a8a8a) 0 21% / 100% 1.6rem no-repeat,
                linear-gradient(90deg, #7c7c7c, #f1f1f1 45%, #8a8a8a) 0 79% / 100% 1.6rem no-repeat;
            border-radius: 2px;
            filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.35));
        }
        .sw-cover-frame {
            position: relative;
            container-type: inline-size;
            display: flex;
            flex-direction: column;
            min-height: clamp(34rem, 62vw, 41rem);
            padding: clamp(1.6rem, 5vw, 2.6rem) clamp(0.9rem, 4.4vw, 2.2rem) clamp(1.4rem, 4vw, 2rem) clamp(2.1rem, 9vw, 3.9rem);
            border: 1px solid var(--sw-red);
            outline: 1px solid var(--sw-red);
            outline-offset: 4px;
        }
        .sw-series {
            display: block;
            text-wrap: balance;
            margin-bottom: clamp(1.6rem, 9cqi, 3rem);
            font-family: var(--sw-type);
            font-weight: 700;
            font-size: 0.74rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.5;
            color: var(--sw-ink-2);
            text-shadow: none;
        }
        .sw-poem { counter-reset: sw-ln; }
        .sw-poem .sw-vl { counter-increment: sw-ln; position: relative; }
        .sw-poem .sw-vl-n::before {
            content: counter(sw-ln);
            position: absolute;
            inset-inline-start: clamp(-2.6rem, -8cqi, -1.6rem);
            top: 50%;
            translate: 0 -50%;
            font-family: var(--sw-serif);
            font-size: 0.95rem;
            font-weight: 400;
            letter-spacing: 0;
            text-indent: 0;
            font-variant-numeric: oldstyle-nums;
            color: var(--sw-red-ink);
            text-shadow: none;
        }
        .sw-title {
            font-weight: 400;
            font-size: clamp(2.3rem, 11.6cqi, 3.9rem);
            line-height: 1.04;
            letter-spacing: -0.012em;
            font-variant-ligatures: common-ligatures discretionary-ligatures;
            text-shadow: var(--sw-bite);
        }
        .sw-caesura {
            display: block;
            margin-block: clamp(1.2rem, 6cqi, 2rem) clamp(1rem, 5cqi, 1.6rem);
            color: var(--sw-red);
            font-size: 1.5rem;
            line-height: 1;
            animation: sw-breathe 6s ease-in-out infinite;
        }
        @keyframes sw-breathe { 0%, 100% { opacity: 0.35; } 50% { opacity: 1; } }
        .sw-stanza { --sw-step: 1.15em; font-size: clamp(1.02rem, 4.75cqi, 1.42rem); line-height: 1.42; color: var(--sw-ink-2); }
        /* Read aloud: each line of the cover takes its ink in turn. */
        html.es-anim #sw .sw-poem .sw-vl { animation: sw-ink 1.1s ease both; animation-delay: calc(0.35s + var(--n, 0) * 0.34s); }
        @keyframes sw-ink { from { opacity: 0.1; } to { opacity: 1; } }
        .sw-device {
            margin-top: auto;
            padding-top: clamp(1.6rem, 8cqi, 2.6rem);
            display: flex;
            align-items: center;
            gap: 0.9rem;
            color: var(--sw-ink-2);
        }
        .sw-device::before { content: ""; flex: 1; height: 1px; background: var(--sw-red); }
        .sw-device i { font-style: normal; color: var(--sw-red); font-size: 1.3rem; line-height: 1; }

        /* On a desk-sized window the cover is the hero: wider, with the verse set larger. */
        @media (min-width: 1000px) {
            .sw-hero { padding-top: clamp(2rem, 3vw, 2.5rem); }
            .sw-hero-grid {
                width: min(100% - 2rem, 78rem);
                grid-template-columns: minmax(0, clamp(30rem, 45vw, 42rem)) minmax(0, 1fr);
                gap: clamp(2.5rem, 6vw, 5.5rem);
            }
            .sw-lift { width: 100%; }
            .sw-series { margin-bottom: clamp(1.6rem, 6.5cqi, 2.4rem); }
            .sw-title { font-size: clamp(2.6rem, 12cqi, 4.2rem); }
            .sw-caesura { margin-block: clamp(1.1rem, 4.5cqi, 1.6rem) clamp(0.9rem, 3.5cqi, 1.3rem); }
            .sw-stanza { font-size: clamp(1.1rem, 4.75cqi, 1.5rem); }
            .sw-device { padding-top: clamp(1.4rem, 5.5cqi, 2rem); }
        }
        .sw-blurb { display: grid; gap: 2.25rem; justify-items: center; width: 100%; }
        @media (min-width: 1000px) { .sw-blurb { justify-items: start; } }
        .sw-blurb-kick { display: flex; align-items: center; gap: 0.9rem; color: var(--sw-ink-2); }
        .sw-blurb-kick::before { content: ""; width: 2.5rem; height: 1px; background: var(--sw-red-ink); }
        .sw-cta { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 1.25rem 2rem; }

        /* The slip tucked inside: tonight's list, typed. */
        .sw-slip {
            position: relative;
            width: min(100%, 19.5rem);
            padding: 2.4rem 1.25rem 1.1rem;
            background-color: var(--sw-leaf-2);
            background-image: var(--sw-tooth);
            box-shadow: var(--sw-lift);
            font-family: var(--sw-type);
            font-size: 0.86rem;
            font-variant-numeric: lining-nums tabular-nums;
            line-height: 1.3;
            rotate: -1.6deg;
        }
        .sw-front { display: grid; justify-items: center; }
        .sw-slip::before {
            content: "";
            position: absolute;
            top: 0.8rem;
            left: 50%;
            width: 0.7rem;
            aspect-ratio: 1;
            translate: -50% 0;
            border-radius: 50%;
            background: var(--sw-desk);
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.4);
        }
        .sw-slip-head { display: flex; justify-content: space-between; gap: 0.75rem; padding-bottom: 0.6rem; border-bottom: 1px solid var(--sw-ink); }
        .sw-slip-row {
            display: grid;
            grid-template-columns: 1.6rem auto minmax(0.75rem, 1fr) auto;
            align-items: baseline;
            gap: 0.4rem;
            min-height: 2.05rem;
            padding-top: 0.55rem;
            border-bottom: 1px solid var(--sw-hair);
        }
        .sw-slip-row > :first-child { color: var(--sw-ink-3); }
        .sw-dots { border-bottom: 1px dotted var(--sw-rule); translate: 0 -0.3em; min-width: 0.75rem; }
        .sw-slip-feature { color: var(--sw-red-ink); font-weight: 700; }
        .sw-slip-gone > :nth-child(2) { text-decoration: line-through; text-decoration-color: var(--sw-red); }
        .sw-slip-foot { display: flex; justify-content: space-between; align-items: baseline; gap: 0.75rem; margin-top: 0.9rem; font-family: var(--sw-ital); font-style: italic; font-size: 0.95rem; color: var(--sw-ink-2); }
        .sw-slip-foot b { font: 700 0.74rem var(--sw-type); letter-spacing: 0.14em; text-transform: uppercase; color: var(--sw-red-ink); white-space: nowrap; }

        /* ---------------------------------------------------------------
           The leaves: every section is a spread of two pages
           --------------------------------------------------------------- */
        .sw-book { display: grid; gap: clamp(1.25rem, 3.4vw, 3.25rem); padding-bottom: clamp(3rem, 6vw, 5rem); }
        .sw-pair { display: grid; gap: clamp(1.25rem, 3.4vw, 3.25rem); scroll-margin-top: 5.25rem; }
        .sw-spread { position: relative; display: grid; gap: 1.1rem; scroll-margin-top: 5.25rem; }
        .sw-page {
            --sw-pg: var(--sw-leaf);
            position: relative;
            container-type: inline-size;
            min-width: 0;
            padding: 4.4rem 1.5rem 4.2rem;
            background-color: var(--sw-pg);
            background-image: var(--sw-tooth);
            box-shadow: var(--sw-lift);
            scroll-margin-top: 5.25rem;
        }
        .sw-through { --sw-pg: color-mix(in srgb, var(--sw-leaf) 95%, #bd3119); }
        /* Deckle: the fore edge and the foot are torn, not cut. */
        .sw-page::after,
        .sw-page::before {
            content: "";
            position: absolute;
            background-color: var(--sw-pg);
            pointer-events: none;
        }
        .sw-page::after {
            top: 0;
            bottom: 0;
            left: calc(100% - 1px);
            width: 8px;
            -webkit-mask: var(--sw-deckle-v) 0 0 / 8px 200px repeat-y;
            mask: var(--sw-deckle-v) 0 0 / 8px 200px repeat-y;
        }
        .sw-page::before {
            left: 0;
            right: 0;
            top: calc(100% - 1px);
            height: 8px;
            -webkit-mask: var(--sw-deckle-h) 0 0 / 200px 8px repeat-x;
            mask: var(--sw-deckle-h) 0 0 / 200px 8px repeat-x;
        }
        .sw-staples { display: none; }
        .sw-head {
            position: absolute;
            top: 1.7rem;
            left: 1.5rem;
            right: 1.5rem;
            text-align: center;
            color: var(--sw-ink-3);
            font-size: 0.98rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sw-folio {
            position: absolute;
            bottom: 1.45rem;
            left: 0;
            right: 0;
            text-align: center;
            color: var(--sw-red-ink);
            font-size: 1.05rem;
            line-height: 1;
        }
        .sw-folio::before,
        .sw-folio::after { content: ""; display: inline-block; vertical-align: middle; width: 1.1rem; height: 1px; margin-inline: 0.6rem; background: currentColor; opacity: 0.55; }

        @media (min-width: 1000px) {
            .sw-spread { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 0; }
            .sw-page { min-height: 44rem; padding-block: 5.25rem 4.9rem; }
            .sw-verso {
                padding-inline: var(--sw-outer) var(--sw-inner);
                background-image: linear-gradient(to left, var(--sw-gutter), transparent 2.8rem), var(--sw-tooth);
            }
            .sw-recto {
                padding-inline: var(--sw-inner) var(--sw-outer);
                background-image: linear-gradient(to right, var(--sw-gutter), transparent 2.8rem), var(--sw-tooth);
            }
            .sw-verso::after { left: auto; right: calc(100% - 1px); scale: -1 1; }
            .sw-verso .sw-head { left: var(--sw-outer); right: var(--sw-inner); }
            .sw-recto .sw-head { left: var(--sw-inner); right: var(--sw-outer); }
            .sw-head { top: 2.1rem; }
            .sw-folio { bottom: 1.8rem; }
            .sw-verso .sw-folio { left: var(--sw-outer); right: var(--sw-inner); }
            .sw-recto .sw-folio { left: var(--sw-inner); right: var(--sw-outer); }
            /* The fold, and in the middle of the book the staples that show there. */
            .sw-recto { box-shadow: inset 1px 0 0 rgba(20, 12, 0, 0.32), var(--sw-lift); }
            .sw-staples {
                display: block;
                position: absolute;
                z-index: 3;
                top: 0;
                bottom: 0;
                left: -2.5px;
                width: 5px;
                background:
                    linear-gradient(90deg, #7c7c7c, #f1f1f1 45%, #8a8a8a) 0 23% / 100% 1.7rem no-repeat,
                    linear-gradient(90deg, #7c7c7c, #f1f1f1 45%, #8a8a8a) 0 77% / 100% 1.7rem no-repeat;
                border-radius: 2px;
                filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.4));
            }
            .sw-page-mid { display: flex; flex-direction: column; justify-content: center; }
        }
        /* Leaves settle as they arrive. */
        @supports (animation-timeline: view()) {
            html.es-anim #sw .sw-page,
            html.es-anim #sw .sw-insert,
            html.es-anim #sw .sw-own {
                animation: sw-settle linear both;
                animation-timeline: view();
                animation-range: entry 0px entry 240px;
            }
        }
        @keyframes sw-settle { from { translate: 0 2rem; } to { translate: 0 0; } }

        /* What goes on a page. */
        .sw-page h2 + .sw-lede,
        .sw-page h2 + p { margin-top: 1.4rem; }
        .sw-stack > * + * { margin-top: 1.1rem; }
        .sw-rule-s { display: block; width: 3.2rem; height: 1px; margin-block: 1.6rem; background: var(--sw-red); border: 0; }
        .sw-drop::first-letter {
            -webkit-initial-letter: 3;
            initial-letter: 3;
            color: var(--sw-red);
            font-weight: 400;
            margin-inline-end: 0.07em;
        }
        @supports not ((initial-letter: 3) or (-webkit-initial-letter: 3)) {
            .sw-drop::first-letter { float: left; font-size: 3.9em; line-height: 0.8; padding: 0.06em 0.08em 0 0; }
        }
        .sw-ast { display: block; text-align: center; color: var(--sw-red); font-size: 1.6rem; line-height: 1; letter-spacing: 0.3em; }

        /* Numbered and rubricated entries, with the plan set in the margin as a gloss. */
        .sw-items { display: grid; gap: 1.9rem; }
        .sw-item { position: relative; display: grid; grid-template-columns: 2.3rem minmax(0, 1fr); column-gap: 0.4rem; }
        .sw-item > * { grid-column: 2; }
        .sw-item-no {
            grid-column: 1;
            grid-row: 1 / span 4;
            padding-top: 0.6rem;
            color: var(--sw-red-ink);
        }
        .sw-item-pil { grid-column: 1; grid-row: 1 / span 4; color: var(--sw-red); font-size: 1.45rem; line-height: 1.25; }
        .sw-item h3 { font-size: 1.5rem; font-weight: 400; line-height: 1.18; text-wrap: balance; text-shadow: var(--sw-bite); }
        .sw-item p { margin-top: 0.55rem; color: var(--sw-ink-2); }
        .sw-gloss { display: block; margin-bottom: 0.15rem; color: var(--sw-red-ink); }
        @media (min-width: 1000px) {
            .sw-item > .sw-gloss { grid-column: 1 / -1; position: absolute; top: 0.5rem; width: 3.4rem; margin: 0; }
            .sw-recto .sw-gloss { left: calc(100% + 0.9rem); text-align: left; }
            .sw-verso .sw-gloss { right: calc(100% + 0.9rem); text-align: right; }
        }
        .sw-foot {
            margin-top: 2.2rem;
            padding-top: 0.9rem;
            font-size: 1.04rem;
            line-height: 1.45;
            color: var(--sw-ink-2);
            background: linear-gradient(var(--sw-rule), var(--sw-rule)) 0 0 / 5.5rem 1px no-repeat;
        }
        .sw-foot::before { content: "\2020\00a0"; color: var(--sw-red-ink); }
        .sw-dag::after { content: "\2020"; color: var(--sw-red-ink); font-size: 0.8em; vertical-align: 0.35em; margin-inline-start: 0.08em; }
        .sw-list { display: grid; gap: 0.85rem; margin-top: 1.5rem; color: var(--sw-ink-2); }
        .sw-list li { display: grid; grid-template-columns: 1.8rem minmax(0, 1fr); }
        .sw-list li::before { content: "\00b6"; color: var(--sw-red); }

        /* Plates: the form and the running order, set as printed matter. */
        .sw-plate { margin: 0; }
        .sw-plate figcaption { margin-top: 0.9rem; text-align: center; color: var(--sw-ink-3); }
        @media (min-width: 1000px) {
            .sw-plate { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: end; column-gap: 0.7rem; }
            .sw-plate figcaption { margin: 0 0 0.3rem; writing-mode: vertical-rl; rotate: 180deg; }
            .sw-front { grid-template-columns: auto auto; justify-content: center; }
        }
        .sw-print {
            padding: 1.5rem 1.4rem 1.2rem;
            border: 1px solid var(--sw-ink);
            outline: 1px solid var(--sw-ink);
            outline-offset: 3px;
            background: var(--sw-leaf-2);
        }
        .sw-print-head { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 1px solid var(--sw-ink); }
        .sw-print-head .sw-sc { font-size: 1.25rem; }
        .sw-form { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem 1.4rem; margin-top: 1.2rem; }
        .sw-form > div:nth-child(-n+2) { grid-column: 1 / -1; }
        .sw-form dt { color: var(--sw-ink-3); }
        .sw-form dd { margin: 0.2rem 0 0; padding-bottom: 0.25rem; border-bottom: 1px dotted var(--sw-rule); font-family: var(--sw-ital); font-style: italic; font-size: 1.2rem; line-height: 1.3; }
        .sw-print-foot { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 1.3rem; color: var(--sw-ink-3); }
        .sw-stamp { padding: 0.25rem 0.55rem 0.15rem; border: 1px solid var(--sw-red-ink); color: var(--sw-red-ink); rotate: -3deg; }
        .sw-order { margin-top: 0.4rem; }
        .sw-order li { display: grid; grid-template-columns: 2rem auto minmax(1rem, 1fr) auto; align-items: baseline; gap: 0.45rem; padding-block: 0.5rem 0.4rem; border-bottom: 1px solid var(--sw-hair); }
        .sw-order li > :first-child { color: var(--sw-ink-3); }
        .sw-order li > :nth-child(2) { font-size: 1.16rem; min-width: 0; }
        .sw-order li > :last-child { color: var(--sw-ink-2); }
        .sw-order .sw-order-feature > :nth-child(2) { color: var(--sw-red-ink); font-weight: 700; }
        .sw-order .sw-order-feature > :last-child { color: var(--sw-red-ink); }
        .sw-print-note { margin-top: 0.9rem; font-size: 1rem; color: var(--sw-ink-2); }

        /* Glossaries and indexes. */
        .sw-gloss-list { display: grid; gap: 0.95rem; margin-top: 1.7rem; }
        .sw-gloss-list li { display: grid; grid-template-columns: 2.3rem minmax(0, 1fr); column-gap: 0.4rem; color: var(--sw-ink-2); }
        .sw-gloss-list li > :first-child { padding-top: 0.6rem; color: var(--sw-red-ink); }
        .sw-gloss-list b { font-weight: 400; font-variant-caps: all-small-caps; letter-spacing: 0.1em; font-size: 1.16em; color: var(--sw-ink); }
        .sw-who { display: grid; gap: 1.25rem; margin-top: 1.7rem; }
        .sw-who h3 { display: inline; font-size: 1.16em; font-weight: 400; font-variant-caps: all-small-caps; letter-spacing: 0.1em; color: var(--sw-ink); }
        .sw-who p { display: inline; color: var(--sw-ink-2); }
        .sw-who a { margin-inline-start: 0.4rem; white-space: nowrap; font-size: 1.05rem; }
        .sw-index { margin-top: 1.2rem; }
        .sw-index a { display: grid; grid-template-columns: auto minmax(1.5rem, 1fr) auto; align-items: baseline; gap: 0.6rem; padding-block: 0.42rem; color: var(--sw-ink); transition: color 0.2s ease; }
        .sw-index a:hover { color: var(--sw-red-ink); }
        .sw-index a:hover .sw-dots { border-color: var(--sw-red); }
        .sw-index a > :last-child { color: var(--sw-red-ink); font-variant-numeric: oldstyle-nums tabular-nums; }
        .sw-see { display: grid; margin-top: 1.1rem; border-top: 1px solid var(--sw-hair); }
        .sw-see a { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 0.2rem 1rem; padding-block: 0.7rem 0.75rem; border-bottom: 1px solid var(--sw-hair); transition: padding 0.25s ease; }
        .sw-see a:hover { padding-inline-start: 0.5rem; }
        .sw-see b { display: block; font-weight: 400; font-variant-caps: all-small-caps; letter-spacing: 0.1em; font-size: 1.2em; line-height: 1.2; }
        .sw-see small { display: block; font-size: 1.02rem; line-height: 1.35; color: var(--sw-ink-2); }
        .sw-see svg { width: 1rem; height: 1rem; color: var(--sw-red); transition: translate 0.25s ease; }
        .sw-see a:hover svg { translate: 0.25rem 0; }
        .sw-h2s { font-size: 1.9rem; font-weight: 400; line-height: 1.1; text-shadow: var(--sw-bite); }

        /* Front matter: a book of nights, and the contents. */
        .sw-nights { max-width: 31rem; font-family: var(--sw-ital); font-style: italic; font-size: clamp(1.45rem, 2.7vw, 2.1rem); line-height: 1.32; text-wrap: balance; text-align: center; text-shadow: var(--sw-bite); }
        .sw-nights li { display: inline; white-space: nowrap; }
        .sw-nights li:not(:last-child)::after { content: "\00b7"; margin-inline: 0.42em 0.16em; color: var(--sw-red); font-style: normal; }
        @media (min-width: 1000px) { .sw-nights { text-align: start; } }
        .sw-contents-h { text-align: center; font-size: 1.5rem; }

        /* Three stanzas. */
        .sw-stanzas { display: grid; gap: 1.9rem; margin-top: 2rem; }
        .sw-stz { display: grid; grid-template-columns: 2.6rem minmax(0, 1fr); column-gap: 0.4rem; }
        .sw-stz-no { color: var(--sw-red); font-size: 1.45rem; line-height: 1.3; font-variant-numeric: lining-nums; }
        .sw-stz h3 { font-size: 1.5rem; font-weight: 400; line-height: 1.2; text-shadow: var(--sw-bite); }
        .sw-stz p { margin-top: 0.4rem; color: var(--sw-ink-2); }

        /* ---------------------------------------------------------------
           The second colour: one spread printed solid
           --------------------------------------------------------------- */
        #sw .sw-flood .sw-page {
            --sw-pg: #bd3119;
            color: #f8f3e8;
            box-shadow: 0 1.4rem 2.6rem -1.6rem rgba(40, 10, 0, 0.7);
        }
        #sw .sw-flood .sw-head,
        #sw .sw-flood .sw-folio,
        #sw .sw-flood .sw-kick,
        #sw .sw-flood .sw-part,
        #sw .sw-flood p,
        #sw .sw-flood .sw-tw { color: #f8f3e8; }
        #sw .sw-flood .sw-verse { text-shadow: 0 1px 0 rgba(90, 15, 0, 0.5); }
        #sw .sw-flood .sw-red { color: #1a1714; text-shadow: 0 1px 0 rgba(255, 150, 120, 0.3); }
        .sw-frags { display: grid; gap: 2.1rem; }
        .sw-frag { display: grid; grid-template-columns: 2.6rem minmax(0, 1fr); column-gap: 0.4rem; }
        .sw-frag > * { grid-column: 2; }
        .sw-frag > i { grid-column: 1; grid-row: 1 / span 3; font-family: var(--sw-ital); font-size: 1.3rem; line-height: 1.6; color: #f8f3e8; }
        .sw-frag h3 { margin-top: 0.15rem; font-size: clamp(1.7rem, 7cqi, 2.2rem); font-weight: 400; line-height: 1.12; text-shadow: 0 1px 0 rgba(90, 15, 0, 0.5); }
        .sw-frag p { margin-top: 0.45rem; }
        .sw-coda { margin-top: 2.2rem; }
        .sw-coda::before { content: ""; display: block; width: 3.2rem; height: 1px; margin-bottom: 1.1rem; background: currentColor; }
        #sw .sw-flood a { color: #f8f3e8; text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.22em; white-space: nowrap; }
        #sw .sw-flood a:hover { color: #1a1714; }
        #sw .sw-flood a:focus-visible { outline-color: #f8f3e8; }

        /* ---------------------------------------------------------------
           The loose insert (the shared plan band) and the endpaper (the
           shared closing strip): their words and prices, this book's type
           --------------------------------------------------------------- */
        .sw-insert { position: relative; background-color: var(--sw-leaf-2); background-image: var(--sw-tooth); box-shadow: var(--sw-lift); }
        .sw-insert-tab { position: absolute; top: 1.4rem; left: 0; right: 0; text-align: center; color: var(--sw-ink-3); }
        #sw .sw-insert > section { background: transparent; }
        #sw .sw-insert h2 { font-family: var(--sw-serif); font-weight: 400; font-size: clamp(2rem, 4.4vw, 2.9rem); line-height: 1.1; letter-spacing: -0.008em; color: var(--sw-ink); text-shadow: var(--sw-bite); }
        #sw .sw-insert h2 + p { color: var(--sw-ink-2); font-size: 1.16rem; }
        #sw .sw-insert .grid { gap: 0; border-block: 1px solid var(--sw-rule); }
        #sw .sw-insert .grid > div { background: transparent; border: 0; border-radius: 0; box-shadow: none; transform: none; color: var(--sw-ink); padding: 1.9rem 1.6rem 1.7rem; }
        #sw .sw-insert .grid > div + div { border-top: 1px solid var(--sw-hair); }
        @media (min-width: 768px) {
            #sw .sw-insert .grid > div + div { border-top: 0; border-inline-start: 1px solid var(--sw-hair); }
        }
        #sw .sw-insert .grid > div:nth-child(2) { background: var(--sw-leaf); }
        #sw .sw-insert .grid > div span,
        #sw .sw-insert .grid > div p,
        #sw .sw-insert .grid > div li { color: var(--sw-ink-2); font-size: 1.04rem; line-height: 1.4; }
        #sw .sw-insert .grid > div .uppercase { font-family: var(--sw-serif); font-size: 1.25rem; font-weight: 400; font-variant-caps: all-small-caps; letter-spacing: 0.14em; color: var(--sw-ink); }
        #sw .sw-insert .grid > div .text-3xl { font-weight: 400; font-size: 2.9rem; line-height: 1; letter-spacing: -0.01em; font-variant-numeric: lining-nums; color: var(--sw-ink); }
        #sw .sw-insert .grid > div .rounded-full { padding: 0.2rem 0.5rem 0.1rem; background: transparent; border: 1px solid var(--sw-red-ink); border-radius: 0; color: var(--sw-red-ink); font: 700 0.66rem var(--sw-type); letter-spacing: 0.12em; }
        #sw .sw-insert .grid > div svg { color: var(--sw-red); }
        #sw .sw-insert .grid > div > p:last-child { font-family: var(--sw-ital); font-style: italic; }
        #sw .sw-insert a.font-medium { color: var(--sw-ink); text-decoration: underline; text-decoration-color: var(--sw-red); text-decoration-thickness: 1px; text-underline-offset: 0.22em; }
        #sw .sw-insert a.rounded-2xl { background: var(--sw-btn); color: var(--sw-on-btn); border-radius: 0; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22); font-variant-caps: all-small-caps; font-weight: 700; font-size: 1.3rem; letter-spacing: 0.14em; transform: none; }
        #sw .sw-insert a.rounded-2xl:hover { background: var(--sw-ink); color: var(--sw-leaf); }

        #sw .sw-keep > section { background: transparent; border-top: 1px solid var(--sw-rule); }
        #sw .sw-keep h2 { font-family: var(--sw-serif); font-weight: 400; font-size: clamp(1.9rem, 4vw, 2.6rem); color: var(--sw-ink); text-shadow: var(--sw-bite); }
        #sw .sw-keep p.uppercase { font-family: var(--sw-type); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.14em; color: var(--sw-red-ink); }
        #sw .sw-keep .grid > a { background-color: var(--sw-leaf); background-image: var(--sw-tooth); border: 0; border-radius: 0; box-shadow: var(--sw-lift); }
        #sw .sw-keep .grid > a > span:first-child { display: none; }
        #sw .sw-keep .grid > a h3 { font-weight: 400; font-size: 1.3rem; font-variant-caps: all-small-caps; letter-spacing: 0.1em; color: var(--sw-ink); }
        #sw .sw-keep .grid > a:hover h3 { color: var(--sw-red-ink); }
        #sw .sw-keep .grid > a p { color: var(--sw-ink-2); font-size: 1.04rem; line-height: 1.4; }
        #sw .sw-keep .grid > a > span:last-child,
        #sw .sw-keep a.self-start { color: var(--sw-red-ink); font-size: 1rem; }

        /* ---------------------------------------------------------------
           Notes: the questions
           --------------------------------------------------------------- */
        .sw-notes { counter-reset: sw-note var(--from, 0); margin-top: 1.9rem; border-top: 1px solid var(--sw-rule); }
        .sw-recto > .sw-notes { margin-top: 0; }
        .sw-notes details { counter-increment: sw-note; border-bottom: 1px solid var(--sw-rule); }
        .sw-notes summary { display: grid; grid-template-columns: 1.9rem minmax(0, 1fr) 1rem; align-items: baseline; gap: 0.4rem; padding-block: 0.95rem 0.9rem; cursor: pointer; }
        .sw-notes summary::before { content: counter(sw-note); color: var(--sw-red-ink); font-variant-numeric: oldstyle-nums; }
        .sw-notes h3 { font-size: 1.24rem; font-weight: 400; line-height: 1.3; text-wrap: balance; }
        .sw-notes summary i { position: relative; align-self: center; width: 0.8rem; height: 0.8rem; }
        .sw-notes summary i::before,
        .sw-notes summary i::after { content: ""; position: absolute; left: 0; right: 0; top: calc(50% - 0.5px); height: 1px; background: var(--sw-red-ink); transition: rotate 0.3s ease; }
        .sw-notes summary i::after { rotate: 90deg; }
        .sw-notes details[open] summary i::after { rotate: 0deg; }
        .sw-notes details p { padding: 0 0.2rem 1.25rem 2.3rem; color: var(--sw-ink-2); font-size: 1.12rem; }

        /* ---------------------------------------------------------------
           Your own title page, and the colophon
           --------------------------------------------------------------- */
        .sw-last { padding-block: clamp(1rem, 3vw, 2rem) clamp(3.5rem, 7vw, 6rem); scroll-margin-top: 4.5rem; }
        .sw-own {
            position: relative;
            width: min(100% - 2rem, 37rem);
            margin-inline: auto;
            padding: clamp(1.1rem, 3.4vw, 1.5rem);
            background-color: var(--sw-leaf);
            background-image: var(--sw-tooth);
            box-shadow:
                1px 1px 0 var(--sw-leaf-2), 2px 2px 0 var(--sw-rule),
                3px 3px 0 var(--sw-leaf), 4px 4px 0 var(--sw-rule),
                0 2.4rem 3.4rem -2rem rgba(40, 28, 8, 0.7);
            rotate: 0.8deg;
        }
        .dark .sw-own { box-shadow: 1px 1px 0 #24211d, 2px 2px 0 #0b0a09, 3px 3px 0 #24211d, 0 2.4rem 3.4rem -1.6rem #000; }
        .sw-own-frame {
            container-type: inline-size;
            padding: clamp(2rem, 7vw, 3.4rem) clamp(1.1rem, 5vw, 2.6rem) clamp(1.8rem, 5vw, 2.4rem);
            border: 1px solid var(--sw-red);
            outline: 1px solid var(--sw-red);
            outline-offset: 4px;
            text-align: center;
        }
        .sw-own .sw-verse { font-size: clamp(2.6rem, 13.5cqi, 4.4rem); }
        .sw-own .sw-vl { padding-inline-start: 0; text-indent: 0; }
        .sw-own .sw-lede { margin: 1.4rem auto 0; max-width: 25rem; }
        .sw-sign { position: relative; margin-top: 2.2rem; padding-block: 1.4rem 1.2rem; border-block: 1px solid var(--sw-rule); }
        .sw-sign-no { position: absolute; left: 0; top: 1.95rem; color: var(--sw-red-ink); }
        .sw-sign-name { display: block; font-family: var(--sw-ital); font-style: italic; font-size: clamp(1.7rem, 9cqi, 2.9rem); line-height: 1.15; overflow-wrap: anywhere; padding-inline: 1.6rem; }
        .sw-sign-host { display: block; margin-top: 0.4rem; color: var(--sw-ink-3); }
        .sw-own-form { display: grid; gap: 1.1rem; justify-items: center; margin-top: 1.8rem; }
        #sw .sw-claim {
            display: flex;
            align-items: baseline;
            width: 100%;
            min-width: 0;
            padding: 1rem 0.9rem;
            border: 0;
            border-bottom: 1px solid var(--sw-ink);
            background: var(--sw-leaf-2);
            font-family: var(--sw-type);
            font-size: clamp(1rem, 4.4cqi, 1.1rem);
            font-variant-numeric: lining-nums;
            transition: box-shadow 0.2s ease;
        }
        #sw .sw-claim:focus-within { border-color: var(--sw-red); box-shadow: 0 2px 0 var(--sw-red); }
        #sw .sw-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            font-weight: 700;
            color: var(--sw-ink);
            box-shadow: none;
            outline: none;
        }
        #sw .sw-claim input::placeholder { color: var(--sw-ink-3); opacity: 1; font-weight: 400; }
        .sw-claim span { flex: none; color: var(--sw-ink-2); user-select: none; }
        .sw-own-note { margin-top: 1.1rem; font-family: var(--sw-ital); font-style: italic; font-size: 1.05rem; color: var(--sw-ink-2); }
        .sw-colophon { width: min(100% - 2rem, 30rem); margin: clamp(2.5rem, 5vw, 4rem) auto 0; text-align: center; color: var(--sw-ink-2); font-size: 1.05rem; line-height: 1.5; }
        .sw-colophon b { display: block; margin-bottom: 0.4rem; font-weight: 400; color: var(--sw-ink); }
        .sw-colophon i { display: block; margin-bottom: 0.9rem; font-style: normal; color: var(--sw-red-ink); font-size: 1.4rem; line-height: 1; }

        @media (prefers-reduced-motion: reduce) {
            .sw-caesura { animation: none; opacity: 1; }
            .sw-ribbon { display: none; }
            #sw [data-reveal="ink"] .sw-vl,
            .sw-btn, .sw-more, .sw-more svg, .sw-btn svg, .sw-see a, .sw-see svg, .sw-index a, .sw-cover { transition: none; }
            .sw-lift:hover .sw-cover { transform: none; }
        }
    </style>

    @php
        $faqs = [
            [
                'q' => 'Is Event Schedule free for open mics and readings?',
                'a' => 'Yes. Sharing your schedule, running recurring nights, taking free registrations with a capacity limit, generating a flyer for each night, and syncing with Google, Outlook, or CalDAV are all free forever, and no ceiling sits on the sign-ups. Charging at the door is the Pro plan at '.plan_price($proMonthly).' a month, which also adds custom questions on the sign-up form. Event Schedule charges zero platform fees on tickets either way.',
            ],
            [
                'q' => 'Can poets sign up for a slot themselves?',
                'a' => 'Yes. Turn on registration for the night and set how many spots there are; on a recurring mic the count is per date. Performers claim a spot from the event page, the page shows how many are left, and it stops taking names once they are gone. A poet who cannot make it cancels from the link in their confirmation email, which frees the spot, and once a night is full the next poet can join its waitlist and is emailed when a spot opens. Registration and its waitlist are free on every plan.',
            ],
            [
                'q' => "Can I ask performers what they're reading?",
                'a' => 'Yes, on the Pro plan. Custom fields let you add your own questions to the sign-up form, so a poet answers "what are you reading", "how long is it", or a content note when they take a spot, and each answer is stored with their registration. You can also let people submit a whole event for your schedule, on any plan, and it lands on your Requests tab. On Pro your own fields can appear on that form too, where a text answer can be checked against a pattern with a hint you write.',
            ],
            [
                'q' => 'How do I run a mic that happens every second Tuesday?',
                'a' => 'Set it up once as a recurring event with a day-of-week pattern, then add date exceptions for the weeks the room is closed or the holiday lands on your night. Recurring events and exceptions are free on every plan.',
            ],
            [
                'q' => 'Can I keep the open mic, the feature series, and workshops on one page?',
                'a' => 'Yes. Sub-schedules split one schedule into strands, so the weekly mic, the featured reading series, and your workshops each sit in their own section of the same link. Sub-schedules are free on every plan.',
            ],
            [
                'q' => 'Can I sell tickets to a featured reading?',
                'a' => 'Yes, on the Pro plan at '.plan_price($proMonthly).' a month, which is what lets a ticket carry a price. Take the money through your own Stripe or PayPal account, or by payment link or cash, with the QR code scanned at the door like any other. Pro adds the live check-in dashboard as well, and it is also what a season pass across the whole series needs. A night you run for nothing takes free registrations on any plan. Event Schedule takes zero platform fees on any of it, so the only deduction is your payment provider\'s own, and a Stripe or PayPal sale can be refunded in full or in part from the Sales page.',
            ],
            [
                'q' => 'Can people ask to hear about a night before sign-up opens?',
                'a' => 'Yes, once you switch on the "Notify me" card. Anyone can then leave just an email address on the night\'s page, with no account and no subscription to your schedule. They get a reminder shortly before it starts, word if it is cancelled, and any change notice you send, and if the night sells tickets, one email when they go on sale. Registration takes names from the moment you switch it on, so there is no opening email for it.',
            ],
            [
                'q' => 'A bookstore listed me as its feature. Is there a page for me already?',
                'a' => 'There may be. When a bookstore, venue or series names a reader who is not on Event Schedule, its event page still shows that reader by name, and the app creates a page for them. That page says which schedule created it and that you have not claimed it, credits each date to the schedule that added it, and stays out of search engines until it is claimed. If it carries your email address, create an account or sign in with that address and press Claim this page: it becomes your schedule, and the series that already listed you keep listing you without asking again, while anyone new sends a request you accept. If it is not you, This is not me takes it down.',
            ],
        ];

        $dotSections = [
            ['top', 'The list'],
            ['tonight', 'Tonight'],
            ['slots', 'The slots'],
            ['whos-up', "Who's up"],
            ['order', 'Running order'],
            ['rest', 'The rest of it'],
            ['rooms', 'The rooms'],
            ['who', 'Perfect for'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Line 01'],
        ];
    @endphp

    @php
        $swArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0l-6-6m6 6l-6 6" /></svg>';
        $swDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m0 0l-6-6m6 6l6-6" /></svg>';
        // The running head of every left-hand page, and the folio each section opens on.
        $swTitle = 'Every open mic has a list';
        $swFolios = ['tonight' => 2, 'slots' => 4, 'whos-up' => 6, 'order' => 8, 'rest' => 10, 'rooms' => 14, 'who' => 15, 'steps' => 16, 'faq' => 18, 'claim' => 20];
    @endphp

    <div id="sw">
        <div class="sw-ribbon" aria-hidden="true"></div>

        <!-- ============================================================ -->
        <!-- The cover                                                    -->
        <!-- ============================================================ -->
        <section class="sw-hero" id="top">
            <div class="sw-wrap sw-hero-grid">
                <div class="sw-lift es-fade-up es-d-1">
                <div class="sw-cover">
                    <div class="sw-cover-frame">
                        <div class="sw-poem">
                        <h1 class="sw-title">
                            <x-marketing.hero-eyebrow class="sw-series">Open mic schedule for poets, storytellers, and hosts</x-marketing.hero-eyebrow>
                            <span class="sw-vl" style="--n: 0;">Every open mic</span>
                            <span class="sw-vl" style="--n: 1; --i: 1;">has a list.</span>
                            <span class="sw-vl sw-red" style="--n: 2;">Yours should</span>
                            <span class="sw-vl sw-red" style="--n: 3; --i: 1;">outlive</span>
                            <span class="sw-vl sw-vl-n sw-red" style="--n: 4; --i: 2;">the night.</span>
                        </h1>
                        <span class="sw-caesura" aria-hidden="true">&Vert;</span>
                        <p class="sw-stanza">
                            <span class="sw-vl" style="--n: 5;">Sign-ups, features, workshops,</span>
                            <span class="sw-vl" style="--n: 6; --i: 1;">and book launches on one link.</span>
                            <span class="sw-vl" style="--n: 7;">Free registration with a capacity limit,</span>
                            <span class="sw-vl" style="--n: 8; --i: 1;">recurring dates that skip the holidays,</span>
                            <span class="sw-vl sw-vl-n" style="--n: 9; --i: 2;">and no platform fees when you sell.</span>
                        </p>
                        </div>
                        <div class="sw-device sw-tw" aria-hidden="true"><i>&#10086;</i> Event Schedule &middot; No. 1</div>
                    </div>
                </div>
                </div>

                <div class="sw-blurb">
                    <p class="sw-blurb-kick sw-tw es-fade-up es-d-2" aria-hidden="true">A chapbook in ten leaves</p>

                    <!-- The kinds of night, said as a litany -->
                    <ul class="sw-nights es-fade-up es-d-3" aria-label="Kinds of night">
                        @foreach (['Open Mic', 'Slam', 'Featured Reading', 'Storytelling', 'Workshop', 'Book Launch', 'Lit Fest', 'Chapbook Release', 'Salon', 'Author Q&A'] as $chip)
                            <li>{{ $chip }}</li>
                        @endforeach
                    </ul>

                    <div class="sw-cta es-fade-up es-d-4">
                        <a href="#slots" class="sw-more sw-more-down">
                            See how the list works
                            {!! $swDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="sw-btn">
                            Start your list
                            {!! $swArrow !!}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="sw-wrap sw-book">

            <!-- ============================================================ -->
            <!-- Front matter: a book of nights, and the contents             -->
            <!-- ============================================================ -->
            <div class="sw-spread">
                <div class="sw-page sw-verso sw-page-mid">
                    <!-- The frontispiece: tonight's list, typed -->
                    <figure class="sw-plate sw-front" aria-hidden="true">
                        <div class="sw-slip">
                            <div class="sw-slip-head sw-tw">
                                <span>Tuesday open mic</span>
                                <span>12 SLOTS</span>
                            </div>
                            <div class="sw-slip-row"><span>01</span><span>mara g.</span><span class="sw-dots"></span><span>3 min</span></div>
                            <div class="sw-slip-row"><span>02</span><span>dez</span><span class="sw-dots"></span><span>3 min</span></div>
                            <div class="sw-slip-row sw-slip-feature"><span>03</span><span>feature</span><span class="sw-dots"></span><span>15 min</span></div>
                            <div class="sw-slip-row sw-slip-gone"><span>04</span><span>jonah</span><span class="sw-dots"></span><span>no show</span></div>
                            <div class="sw-slip-row"><span>05</span></div>
                            <div class="sw-slip-row"><span>06</span></div>
                            <p class="sw-slip-foot">
                                <span>Anyone with the link can take a spot.</span>
                                <b>8 left</b>
                            </p>
                        </div>
                        <figcaption class="sw-tw">Frontispiece</figcaption>
                    </figure>
                    <span class="sw-folio" aria-hidden="true">ii</span>
                </div>
                <nav class="sw-page sw-recto sw-page-mid sw-through" aria-label="Contents">
                    <p class="sw-contents-h sw-sc">Contents</p>
                    <span class="sw-rule-s" style="margin-inline: auto;" aria-hidden="true"></span>
                    <ol class="sw-index">
                        @foreach ($dotSections as [$sectionId, $sectionLabel])
                            @continue(! isset($swFolios[$sectionId]))
                            <li>
                                <a href="#{{ $sectionId }}">
                                    <span>{{ $sectionLabel }}</span>
                                    <span class="sw-dots" aria-hidden="true"></span>
                                    <span>{{ $swFolios[$sectionId] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                    <span class="sw-folio" aria-hidden="true">iii</span>
                </nav>
            </div>

            <!-- ============================================================ -->
            <!-- I. Tonight: the list that only exists in the room            -->
            <!-- ============================================================ -->
            <section class="sw-spread sw-flood" id="tonight">
                <div class="sw-page sw-verso sw-page-mid">
                    <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                    <span class="sw-part" aria-hidden="true">I</span>
                    <p class="sw-kick sw-sc">Tonight, and then never again</p>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">The list exists</span>
                        <span class="sw-vl" style="--n: 1; --i: 1;">in one room,</span>
                        <span class="sw-vl" style="--n: 2; --i: 2;">on one sheet,</span>
                        <span class="sw-vl sw-red" style="--n: 3;">for about</span>
                        <span class="sw-vl sw-red" style="--n: 4; --i: 1;">four hours.</span>
                    </h2>
                    <span class="sw-folio" aria-hidden="true">2</span>
                </div>
                <div class="sw-page sw-recto sw-page-mid">
                    <span class="sw-head sw-sc" aria-hidden="true">Tonight</span>
                    <div class="sw-frags">
                        <div class="sw-frag">
                            <i aria-hidden="true">i.</i>
                            <p class="sw-tw">The clipboard</p>
                            <h3>One copy, by the door</h3>
                            <p>Whoever arrives first writes first. Everyone else texts you to ask if there is still room.</p>
                        </div>
                        <div class="sw-frag">
                            <i aria-hidden="true">ii.</i>
                            <p class="sw-tw">The group chat</p>
                            <h3>
                                <span data-count-to="212">212</span> unread
                            </h3>
                            <p>The date is in there somewhere, above four photos of a dog and a poll about a bar.</p>
                        </div>
                        <div class="sw-frag">
                            <i aria-hidden="true">iii.</i>
                            <p class="sw-tw">The DMs</p>
                            <h3>
                                &ldquo;Can I get on?&rdquo; &times;<span data-count-to="9">9</span>
                            </h3>
                            <p>Nine separate conversations, three platforms, and one of them is asking about a night that already happened.</p>
                        </div>
                    </div>
                    <p class="sw-coda">
                        A schedule holds the same list, and it is still there on Wednesday.
                        <a href="#slots">See the slots</a>
                    </p>
                    <span class="sw-folio" aria-hidden="true">3</span>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- II. The slots: one link is the list                          -->
            <!-- ============================================================ -->
            <section class="sw-spread" id="slots">
                <div class="sw-page sw-verso sw-page-mid sw-through">
                    <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                    <span class="sw-part" aria-hidden="true">II</span>
                    <p class="sw-kick sw-sc">One link is the list</p>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">Put the sheet</span>
                        <span class="sw-vl" style="--n: 1; --i: 1;">somewhere</span>
                        <span class="sw-vl sw-red" style="--n: 2;">the room cannot</span>
                        <span class="sw-vl sw-red" style="--n: 3; --i: 1;">lose it.</span>
                    </h2>
                    <p class="sw-lede sw-dag">Three things do almost all the work, and all three are on the free plan.</p>
                    <span class="sw-folio" aria-hidden="true">4</span>
                </div>
                <div class="sw-page sw-recto">
                    <span class="sw-head sw-sc" aria-hidden="true">The slots</span>
                    <ol class="sw-items">
                        <li class="sw-item">
                            <span class="sw-item-no sw-tw" aria-hidden="true">01</span>
                            <span class="sw-gloss sw-tw">Free</span>
                            <h3>Registration, with a cap</h3>
                            <p>
                                Turn on registration for the night and say how many spots there are. The page shows how many are left, and once they are gone it stops taking names instead of quietly overbooking you.
                            </p>
                        </li>
                        <li class="sw-item">
                            <span class="sw-item-no sw-tw" aria-hidden="true">02</span>
                            <span class="sw-gloss sw-tw">Free</span>
                            <h3>Every second Tuesday, minus the holiday</h3>
                            <p>
                                Set the pattern once as a recurring event, then add exceptions for the weeks the room is closed. You are not rebuilding the same night twenty-six times a year.
                            </p>
                        </li>
                        <li class="sw-item">
                            <span class="sw-item-no sw-tw" aria-hidden="true">03</span>
                            <span class="sw-gloss sw-tw">Free</span>
                            <h3>The mic, the series, the workshop</h3>
                            <p>
                                Sub-schedules split one link into strands, so the weekly mic, the featured reading series, and your workshops each get their own section without needing their own page.
                            </p>
                        </li>
                    </ol>
                    <p class="sw-foot">
                        Free also covers unlimited events, two-way Google, Outlook, and CalDAV sync, an embeddable calendar, online and hybrid readings, a waitlist for a full night, and a calendar feed anyone can subscribe to, so a moved night updates itself.
                    </p>
                    <span class="sw-folio" aria-hidden="true">5</span>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- III. Who's up: submissions and the questions you ask         -->
            <!-- ============================================================ -->
            <section class="sw-spread" id="whos-up">
                <div class="sw-page sw-verso sw-prose">
                    <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                    <span class="sw-part" aria-hidden="true">III</span>
                    <p class="sw-kick sw-sc">Who's up</p>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">Stop asking</span>
                        <span class="sw-vl sw-vl-q" style="--n: 1; --i: 1;">&ldquo;what are you</span>
                        <span class="sw-vl" style="--n: 2; --i: 2;">reading&rdquo;</span>
                        <span class="sw-vl sw-red" style="--n: 3;">at the door.</span>
                    </h2>
                    <p class="sw-lede sw-drop">
                        Anyone can put an event forward for your schedule, on any plan, and it lands on your Requests tab. On Pro you add your own questions to the sign-up form, so the things you would otherwise shout across a loud room arrive written down.
                    </p>
                    <ul class="sw-list">
                        <li>
                            <span>Ask what they are reading, how long it runs, and whether it needs a content note.</span>
                        </li>
                        <li>
                            <span>Your own fields can go on the form for putting an event forward too, where a text answer can be checked against a pattern you set, with a hint you write, in the browser and again on the server.</span>
                        </li>
                        <li>
                            <span>Export the answers with your sales when you need a running order on paper after all.</span>
                        </li>
                    </ul>
                    <span class="sw-folio" aria-hidden="true">6</span>
                </div>
                <div class="sw-page sw-recto sw-page-mid">
                    <span class="sw-head sw-sc" aria-hidden="true">Who's up</span>
                    <figure class="sw-plate" aria-hidden="true">
                        <div class="sw-print">
                            <div class="sw-print-head">
                                <span class="sw-sc">Take a spot</span>
                                <span class="sw-tw sw-red">Pro</span>
                            </div>
                            <dl class="sw-form">
                                <div>
                                    <dt class="sw-tw">Your name</dt>
                                    <dd>mara g.</dd>
                                </div>
                                <div>
                                    <dt class="sw-tw">What are you reading?</dt>
                                    <dd>two new ones + the bridge poem</dd>
                                </div>
                                <div>
                                    <dt class="sw-tw">How long?</dt>
                                    <dd>3 min</dd>
                                </div>
                                <div>
                                    <dt class="sw-tw">Content note</dt>
                                    <dd>grief</dd>
                                </div>
                            </dl>
                            <div class="sw-print-foot">
                                <span class="sw-tw">Spot 05 of 12</span>
                                <span class="sw-tw sw-stamp">Saved</span>
                            </div>
                        </div>
                        <figcaption class="sw-tw">Plate I</figcaption>
                    </figure>
                    <span class="sw-folio" aria-hidden="true">7</span>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- IV. The running order: the same list, at the back of the room -->
            <!-- ============================================================ -->
            <section class="sw-spread" id="order">
                <div class="sw-page sw-verso">
                    <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                    <span class="sw-part" aria-hidden="true">IV</span>
                    <p class="sw-kick sw-sc">Doors</p>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">The same list,</span>
                        <span class="sw-vl sw-red" style="--n: 1;">in your hand</span>
                        <span class="sw-vl sw-red" style="--n: 2; --i: 1;">at the back</span>
                        <span class="sw-vl sw-red" style="--n: 3; --i: 2;">of the room.</span>
                    </h2>
                    <figure class="sw-plate" aria-hidden="true" style="margin-top: 2.2rem;">
                        <div class="sw-print">
                            <div class="sw-print-head">
                                <span class="sw-sc">Tue 8:00 PM</span>
                                <span class="sw-tw sw-red">Full</span>
                            </div>
                            <ol class="sw-order">
                                @foreach ([['01', 'Mara G.', '3 min', false], ['02', 'Dez', '3 min', false], ['03', 'Feature: A. Oyelaran', '15 min', true], ['04', 'Jonah', '3 min', false], ['05', 'Priya', '3 min', false], ['06', 'Tomas', '3 min', false]] as [$ordNum, $ordName, $ordLen, $ordFeature])
                                    <li @class(['sw-order-feature' => $ordFeature])>
                                        <span class="sw-tw">{{ $ordNum }}</span>
                                        <span>{{ $ordName }}</span>
                                        <span class="sw-dots"></span>
                                        <span class="sw-tw">{{ $ordLen }}</span>
                                    </li>
                                @endforeach
                            </ol>
                            <p class="sw-print-note sw-it">Updates as people sign up.</p>
                        </div>
                        <figcaption class="sw-tw">Plate II</figcaption>
                    </figure>
                    <span class="sw-folio" aria-hidden="true">8</span>
                </div>
                <div class="sw-page sw-recto sw-page-mid">
                    <span class="sw-head sw-sc" aria-hidden="true">Running order</span>
                    <div class="sw-items">
                        <div class="sw-item">
                            <span class="sw-item-pil" aria-hidden="true">&para;</span>
                            <span class="sw-gloss sw-tw">Free</span>
                            <h3>The night promotes itself</h3>
                            <p>Generate a flyer from the event and post it, instead of rebuilding the same graphic in a design tool every second Tuesday.</p>
                        </div>
                        <div class="sw-item">
                            <span class="sw-item-pil" aria-hidden="true">&para;</span>
                            <h3>It is in their calendar, not just yours</h3>
                            <p>Two-way sync with Google, Outlook, and CalDAV on your side. On theirs, an .ics download for any one date, or a live feed of the whole series that updates itself when a night moves.</p>
                        </div>
                        <div class="sw-item">
                            <span class="sw-item-pil" aria-hidden="true">&para;</span>
                            <h3>It lives on the venue's site too</h3>
                            <p>Embed the calendar on the bookstore's page or your own site, so the list is wherever people already look.</p>
                        </div>
                    </div>
                    <span class="sw-folio" aria-hidden="true">9</span>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- V. The rest of it: two spreads, and the staples between them  -->
            <!-- ============================================================ -->
            <section class="sw-pair" id="rest">
                <div class="sw-spread sw-centre">
                    <div class="sw-page sw-verso sw-page-mid">
                        <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                        <span class="sw-part" aria-hidden="true">V</span>
                        <p class="sw-kick sw-sc">The rest of it</p>
                        <h2 class="sw-verse" data-reveal="ink">
                            <span class="sw-vl" style="--n: 0;">Everything</span>
                            <span class="sw-vl" style="--n: 1; --i: 1;">a scene needs</span>
                            <span class="sw-vl sw-red" style="--n: 2;">that is not</span>
                            <span class="sw-vl sw-red" style="--n: 3; --i: 1;">the writing.</span>
                        </h2>
                        <span class="sw-ast" aria-hidden="true" style="margin-top: 3rem; text-align: start;">&#8258;</span>
                        <span class="sw-folio" aria-hidden="true">10</span>
                    </div>
                    <div class="sw-page sw-recto">
                        <span class="sw-staples" aria-hidden="true"></span>
                        <span class="sw-head sw-sc" aria-hidden="true">The rest of it</span>
                        <div class="sw-items">
                            <div class="sw-item">
                                <span class="sw-item-pil" aria-hidden="true">&para;</span>
                                <span class="sw-gloss sw-tw">Free</span>
                                <h3>The scene's mailing list</h3>
                                <p>
                                    Regulars who sign up with their email and confirm it get a digest of the nights you add, automatically and at most one every three days, and you write to the whole list directly when you have news. No algorithm deciding which regulars find out.
                                </p>
                                <p>
                                    The digest sits outside your newsletter allowance, which is 10 emails a month on Free and 100 on Pro, counted per recipient. For a room of regulars that is real, and it is worth knowing the number before you plan around it.
                                </p>
                            </div>
                            <div class="sw-item">
                                <span class="sw-item-pil" aria-hidden="true">&para;</span>
                                <span class="sw-gloss sw-tw">Free</span>
                                <h3>Clips from the night</h3>
                                <p>
                                    Attendees add photos, video, and comments to the event with just a name and an email. Everything waits in an approval queue, so the page stays yours. Free covers 25 photos per schedule.
                                </p>
                            </div>
                        </div>
                        <span class="sw-folio" aria-hidden="true">11</span>
                    </div>
                </div>

                <div class="sw-spread">
                    <div class="sw-page sw-verso">
                        <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                        <div class="sw-items">
                            <div class="sw-item">
                                <span class="sw-item-pil" aria-hidden="true">&para;</span>
                                <span class="sw-gloss sw-tw">Pro</span>
                                <h3>When the feature is ticketed</h3>
                                <p>
                                    Take payment through Stripe or PayPal, or cash on the night, and sell straight from the schedule with QR check-in at the door. Putting a price on the night is the Pro plan, and Event Schedule takes zero platform fees, so what is left after processing is yours.
                                </p>
                                <p>
                                    Announce the feature before tickets are on sale, switch on the "Notify me" card, and people can leave just an email address to hear when they go on sale. Pro adds discount codes for the regulars and a pass that covers a whole season of the series.
                                </p>
                            </div>
                            <div class="sw-item">
                                <span class="sw-item-pil" aria-hidden="true">&para;</span>
                                <span class="sw-gloss sw-tw">Pro</span>
                                <h3>Let the room decide</h3>
                                <p>
                                    Put a poll on the event and let people vote on the theme, the next feature, or which night of the month the mic should move to.
                                </p>
                            </div>
                        </div>
                        <span class="sw-folio" aria-hidden="true">12</span>
                    </div>
                    <div class="sw-page sw-recto">
                        <span class="sw-head sw-sc" aria-hidden="true">The rest of it</span>
                        <div class="sw-items">
                            <div class="sw-item">
                                <span class="sw-item-pil" aria-hidden="true">&para;</span>
                                <span class="sw-gloss sw-tw">Free</span>
                                <h3>Read to the people who could not come</h3>
                                <p>
                                    Mark a night as an online event and the link sits on the same schedule as the in-person ones. Hybrid workshops and virtual salons do not need a second home.
                                </p>
                                <p>
                                    Analytics on the free plan show which nights people actually opened, which is a better read on the scene than a like count.
                                </p>
                            </div>
                            <div class="sw-item">
                                <span class="sw-item-pil" aria-hidden="true">&para;</span>
                                <span class="sw-gloss sw-tw">Pro</span>
                                <h3>Ask afterwards</h3>
                                <p>
                                    Collect star ratings and written comments from people who were there, so the next night is planned on something better than the vibe at the bar.
                                </p>
                            </div>
                        </div>
                        <span class="sw-folio" aria-hidden="true">13</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- VI and VII. The rooms, and who it is for                     -->
            <!-- ============================================================ -->
            <div class="sw-spread">
                <section class="sw-page sw-verso" id="rooms">
                    <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                    <span class="sw-part" aria-hidden="true">VI</span>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">Where the list</span>
                        <span class="sw-vl sw-red" style="--n: 1; --i: 1;">goes up</span>
                    </h2>
                    <p class="sw-lede">
                        One schedule covers all of them, whether you host the night or just read at it.
                    </p>
                    <ol class="sw-gloss-list">
                        @foreach ([['01', 'Coffee shops', 'Weeknight mics with a sign-up by the register'], ['02', 'Bookstores', 'Launches, signings, and author Q&As'], ['03', 'Bars and lounges', 'Late slams and monthly showcases'], ['04', 'Universities', 'Student series, visiting readers, workshops'], ['05', 'Lit festivals', 'Multi-day programmes across several rooms'], ['06', 'Online', 'Virtual salons and hybrid workshops']] as [$roomNum, $roomName, $roomBlurb])
                            <li>
                                <span class="sw-tw" aria-hidden="true">{{ $roomNum }}</span>
                                <span><b>{{ $roomName }}</b> &ensp;{{ $roomBlurb }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <span class="sw-folio" aria-hidden="true">14</span>
                </section>
                <section class="sw-page sw-recto" id="who">
                    <span class="sw-head sw-sc" aria-hidden="true">Perfect for</span>
                    <span class="sw-part" aria-hidden="true">VII</span>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">Built for how poets</span>
                        <span class="sw-vl sw-red" style="--n: 1; --i: 1;">actually work</span>
                    </h2>
                    <p class="sw-lede">
                        Whether you're on the slam circuit, running the room, or launching a collection
                    </p>
                    @php
                        $swWho = [
                            ['Slam Poets', "Competition circuit, team slams, regional bouts. Track your season and let fans follow where you're bouting next.", 'for-slam-poets'],
                            ['Spoken Word Artists', 'Performance poetry with music, movement, multimedia. Share your theatrical shows and collaborations.', 'for-spoken-word-artists'],
                            ['Page Poets', 'Book launches, literary readings, publication events. Promote your collections alongside appearances.', 'for-page-poets'],
                            ['Open Mic Hosts', 'Running your own series? Set the night up once as a recurring event and let poets take their own spots.', 'for-poetry-open-mic-hosts'],
                            ['Literary Curators', 'Organizing reading series, festivals, salon events. Aggregate your programming in one place and embed it anywhere.', 'for-literary-curators'],
                            ['Storytellers', 'Oral storytelling, narrative performance, and story slams. Share your upcoming shows and captivate new audiences.', 'for-storytellers'],
                        ];
                    @endphp
                    <div class="sw-who">
                        @foreach ($swWho as [$swWhoName, $swWhoDesc, $swWhoSlug])
                            @php $swWhoPost = get_sub_audience_blog($swWhoSlug); @endphp
                            <article>
                                <h3>{{ $swWhoName }}</h3>&ensp;
                                <p>{{ $swWhoDesc }}</p>
                                @if ($swWhoPost)
                                    <a href="{{ blog_url('/' . $swWhoPost->slug) }}" class="sw-more" aria-label="Learn more about Event Schedule for {{ $swWhoName }}">Learn more {!! $swArrow !!}</a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    <span class="sw-folio" aria-hidden="true">15</span>
                </section>
            </div>

            <!-- ============================================================ -->
            <!-- VIII. Three steps, then the further reading                  -->
            <!-- ============================================================ -->
            <div class="sw-spread">
                <section class="sw-page sw-verso" id="steps">
                    <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                    <span class="sw-part" aria-hidden="true">VIII</span>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">Three <span class="sw-red">steps</span></span>
                    </h2>
                    <div class="sw-stanzas">
                        @foreach ([['1', 'Start the list', 'Add the mic as a recurring event, turn on registration, and set how many spots there are. Skip the weeks the room is closed.'], ['2', 'Share one link', 'Your bio, the venue\'s site, the back of your chapbook. Or embed the calendar on a page you already have.'], ['3', 'Let them sign up', 'Poets take a spot themselves. You see the list from anywhere, and it closes itself when the spots are gone.']] as [$stepNum, $stepTitle, $stepBody])
                            <div class="sw-stz">
                                <span class="sw-stz-no" aria-hidden="true">{{ ['i.', 'ii.', 'iii.'][$stepNum - 1] }}</span>
                                <div>
                                    <h3>{{ $stepTitle }}</h3>
                                    <p>{{ $stepBody }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <span class="sw-folio" aria-hidden="true">16</span>
                </section>
                <div class="sw-page sw-recto">
                    <span class="sw-head sw-sc" aria-hidden="true">Three steps</span>
                    <section>
                        <h2 class="sw-h2s">Key features</h2>
                        <div class="sw-see">
                            <a href="{{ marketing_url('/features/recurring-events') }}">
                                <span><b>Recurring Events</b><small>Set the night once, with exceptions for the weeks you skip</small></span>
                                {!! $swArrow !!}
                            </a>
                            <a href="{{ marketing_url('/features/custom-fields') }}">
                                <span><b>Custom Fields</b><small>Ask what they're reading right on the sign-up form</small></span>
                                {!! $swArrow !!}
                            </a>
                            <a href="{{ marketing_url('/features/ticketing') }}">
                                <span><b>Ticketing</b><small>Sell tickets with QR check-in and zero platform fees</small></span>
                                {!! $swArrow !!}
                            </a>
                            <a href="{{ marketing_url('/features/newsletters') }}">
                                <span><b>Newsletters</b><small>Send event updates directly to followers' inboxes</small></span>
                                {!! $swArrow !!}
                            </a>
                        </div>
                        <p style="margin-top: 1.1rem;">
                            <a href="{{ marketing_url('/features') }}" class="sw-more">
                                See all features
                                {!! $swArrow !!}
                            </a>
                        </p>
                    </section>
                    <section style="margin-top: 2.6rem;">
                        <h2 class="sw-h2s">Related pages</h2>
                        <ul class="sw-index">
                            @foreach ([['/for-comedians', 'Comedians'], ['/for-musicians', 'Musicians'], ['/for-theater-performers', 'Theater Performers'], ['/for-libraries', 'Libraries']] as [$relHref, $relName])
                                <li>
                                    <a href="{{ marketing_url($relHref) }}">
                                        <span>For {{ $relName }}</span>
                                        <span class="sw-dots" aria-hidden="true"></span>
                                        <span>Read more</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <p style="margin-top: 1.1rem;">
                            <a href="{{ marketing_url('/use-cases') }}" class="sw-more">
                                See all use cases
                                {!! $swArrow !!}
                            </a>
                        </p>
                    </section>
                    <span class="sw-folio" aria-hidden="true">17</span>
                </div>
            </div>

            <!-- The plan band, tipped in as a loose insert -->
            <div class="sw-insert">
                <span class="sw-insert-tab sw-tw" aria-hidden="true">Loose insert</span>
                @include('marketing.partials.pricing-nudge')
            </div>

            <!-- ============================================================ -->
            <!-- IX. Notes: the questions                                     -->
            <!-- ============================================================ -->
            <x-seo.faq-schema :items="$faqs" />

            <section class="sw-spread" id="faq">
                <div class="sw-page sw-verso">
                    <span class="sw-head sw-sc" aria-hidden="true">{{ $swTitle }}</span>
                    <span class="sw-part" aria-hidden="true">IX</span>
                    <p class="sw-kick sw-sc" aria-hidden="true">Notes</p>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">Frequently asked</span>
                        <span class="sw-vl sw-red" style="--n: 1; --i: 1;">questions</span>
                    </h2>
                    <p class="sw-lede">
                        Everything poets and hosts ask before they start the list.
                    </p>
                    <div class="sw-notes">
                        @foreach (array_slice($faqs, 0, 3) as $faq)
                            <details name="faq">
                                <summary>
                                    <h3>{{ $faq['q'] }}</h3>
                                    <i aria-hidden="true"></i>
                                </summary>
                                <p>{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                    <span class="sw-folio" aria-hidden="true">18</span>
                </div>
                <div class="sw-page sw-recto">
                    <span class="sw-head sw-sc" aria-hidden="true">Questions</span>
                    <div class="sw-notes" style="--from: 3;">
                        @foreach (array_slice($faqs, 3) as $faq)
                            <details name="faq">
                                <summary>
                                    <h3>{{ $faq['q'] }}</h3>
                                    <i aria-hidden="true"></i>
                                </summary>
                                <p>{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                    <span class="sw-folio" aria-hidden="true">19</span>
                </div>
            </section>
        </div>

        <!-- ============================================================ -->
        <!-- Your own title page: line 01 is open                         -->
        <!-- ============================================================ -->
        <section class="sw-last" id="claim">
            <div class="sw-own">
                <div class="sw-own-frame">
                    <p class="sw-kick sw-sc">Free forever</p>
                    <h2 class="sw-verse" data-reveal="ink">
                        <span class="sw-vl" style="--n: 0;">Line 01</span>
                        <span class="sw-vl sw-red" style="--n: 1;">is open.</span>
                    </h2>
                    <p class="sw-lede">
                        Start the list tonight. Nobody has to DM you to get on it, and it is still there in the morning.
                    </p>

                    <!-- The first line of the next book: the name writes itself in -->
                    <div class="sw-sign" aria-hidden="true">
                        <span class="sw-sign-no sw-tw">01</span>
                        <span class="sw-sign-name" id="sw-sign">your-name</span>
                        <span class="sw-sign-host sw-tw">.eventschedule.com</span>
                    </div>

                    <div class="sw-own-form">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim sw-claim">
                            <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="sw-btn">
                            Start your list
                            {!! $swArrow !!}
                        </a>
                    </div>
                    <p class="sw-own-note">No credit card required</p>
                </div>
            </div>

            <p class="sw-colophon">
                <i aria-hidden="true">&#10086;</i>
                <b class="sw-sc">Colophon</b>
                Set in EB Garamond, with Lekton for the small matter, in two inks: black, and one vermilion.
            </p>
        </section>

        <div class="sw-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    {{-- Write the claimed name onto the first line of the title page, applying the
         same slug transform as the shared claim-input sanitizer. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('sw-sign');
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
            });
        })();
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
