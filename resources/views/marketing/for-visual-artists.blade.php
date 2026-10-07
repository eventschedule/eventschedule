<x-marketing-layout>
    <x-slot name="title">Free Artist Exhibition Calendar | Openings and Studio Visits</x-slot>
    <x-slot name="description">One page for your exhibitions, open studio days and workshops. Sell workshop places with zero platform fees, and let collectors subscribe to your calendar.</x-slot>
    <x-slot name="breadcrumbTitle">For Visual Artists</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Fraunces') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('DM Sans') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Satisfy') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Visual Artists"
        description="Build your collector base directly. Announce exhibitions, sell tickets to openings, and email collectors. Zero platform fees. Free forever."
        audience="Visual Artists"
        keywords="artist exhibition calendar, visual artist scheduling, gallery show management, art event calendar, free artist scheduling" />
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
           For-visual-artists "Pigment" styles. The gallery shows the
           finished thing; this page is where it is made. Its subject is
           colour as an artist handles it: paint chips, pans, tubes, a
           mixing row, washes laid over each other on paper.

           The engineering is one variable. Anything carrying .va-pg
           reads a single pigment (--va-pig) and derives the rest from
           it: the pale tint, the wash, the edge, and a deep ink that is
           held at a fixed lightness so it passes AA whatever the
           pigment is. Change the pigment and the section re-tints.
           Three layers, each a fallback for the next: plain colours,
           then color-mix(), then relative colour syntax.

           Everything is scoped under #va. The shared es-* reveal system
           (marketing.css, marketing-home.js) still drives the entrances.
           ============================================================== */

        @property --va-spread {
            syntax: '<number>';
            inherits: false;
            initial-value: 1;
        }

        #va {
            --va-paper: #faf8f3;
            --va-paper-2: #f2eee4;
            --va-sheet: #fffdf8;
            --va-ink: #1d1b1a;
            --va-ink-2: #4a4541;
            --va-ink-3: #68625c;
            --va-line: rgba(29, 27, 26, 0.14);
            --va-line-2: rgba(29, 27, 26, 0.28);
            --va-shade: rgba(29, 27, 26, 0.42);
            --va-red: #e03c31;
            --va-ochre: #c9952b;
            --va-viridian: #2f8f7a;
            --va-teal: #1ba7b4;
            --va-sienna: #a5512b;
            --va-black: #231f1e;
            --va-tape: rgba(233, 222, 190, 0.82);
            --va-blend: multiply;
            --va-o: 0.9;
            --va-display: 'Fraunces', 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif;
            --va-text: 'DM Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --va-hand: 'Satisfy', 'Snell Roundhand', 'Brush Script MT', cursive;
            --va-tooth: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='t'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.2 0 0 0 0 0.15 0 0 0 0 0.1 0 0 0 0.13 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23t)'/%3E%3C/svg%3E");
            position: relative;
            background: var(--va-paper);
            color: var(--va-ink);
            font-family: var(--va-text);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #va {
            --va-paper: #151312;
            --va-paper-2: #1b1816;
            --va-sheet: #23201d;
            --va-ink: #f4f0e8;
            --va-ink-2: #cfc8bd;
            --va-ink-3: #a69e93;
            --va-line: rgba(244, 240, 232, 0.14);
            --va-line-2: rgba(244, 240, 232, 0.28);
            --va-shade: rgba(0, 0, 0, 0.8);
            --va-tape: rgba(214, 200, 164, 0.62);
            --va-blend: screen;
            --va-o: 0.62;
            --va-tooth: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='t'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 0.95 0 0 0 0 0.88 0 0 0 0.07 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23t)'/%3E%3C/svg%3E");
        }

        /* The bar above takes the paper, so the page reads as one sheet. */
        body > header.sticky {
            background-color: rgba(250, 248, 243, 0.88);
            border-bottom-color: rgba(29, 27, 26, 0.12);
        }
        .dark body > header.sticky {
            background-color: rgba(21, 19, 18, 0.88);
            border-bottom-color: rgba(244, 240, 232, 0.12);
        }

        /* ---------------------------------------------------------------
           The pigment system
           --------------------------------------------------------------- */
        #va .va-p-red { --va-pig: var(--va-red); }
        #va .va-p-ochre { --va-pig: var(--va-ochre); }
        #va .va-p-viridian { --va-pig: var(--va-viridian); }
        #va .va-p-teal { --va-pig: var(--va-teal); }
        #va .va-p-sienna { --va-pig: var(--va-sienna); }
        #va .va-p-black { --va-pig: var(--va-black); }

        /* Layer one: plain colours, for a browser with neither function. */
        #va .va-pg {
            --va-tint: var(--va-paper-2);
            --va-edge: var(--va-line-2);
            --va-deep: var(--va-ink);
            --va-fill: var(--va-pig);
            --va-wfill: var(--va-fill);
            --va-paint:
                linear-gradient(90deg, rgba(0, 0, 0, 0.16) 0 54%, rgba(0, 0, 0, 0) 54%),
                repeating-linear-gradient(93deg, rgba(255, 255, 255, 0.08) 0 2px, rgba(255, 255, 255, 0) 2px 7px),
                var(--va-pig);
        }
        /* Layer two: the tint, the edge and the ink are mixed from the pigment. */
        @supports (color: color-mix(in oklab, red 50%, blue)) {
            #va .va-pg {
                --va-tint: color-mix(in oklab, var(--va-pig) 9%, var(--va-paper));
                --va-edge: color-mix(in oklab, var(--va-pig) 42%, var(--va-paper));
                --va-deep: color-mix(in oklab, var(--va-pig) 56%, black);
                --va-fill: color-mix(in srgb, var(--va-pig) 36%, transparent);
            }
            .dark #va .va-pg,
            #va .va-night.va-pg,
            #va .va-night .va-pg {
                --va-deep: color-mix(in oklab, var(--va-pig) 52%, white);
                --va-fill: color-mix(in srgb, var(--va-pig) 52%, transparent);
            }
        }
        /* Burnt sienna let down with water goes to a salmon. A painter warms it
           with a touch of ochre first, and so does the page. */
        @supports (color: color-mix(in oklab, red 50%, blue)) {
            #va .va-p-sienna.va-pg {
                --va-tint: color-mix(in oklab, color-mix(in oklab, var(--va-pig) 45%, var(--va-ochre)) 8%, var(--va-paper));
                --va-wfill: color-mix(in srgb, color-mix(in oklab, var(--va-pig) 45%, var(--va-ochre)) 40%, transparent);
            }
            .dark #va .va-p-sienna.va-pg,
            #va .va-night .va-p-sienna.va-pg {
                --va-wfill: color-mix(in srgb, color-mix(in oklab, var(--va-pig) 60%, var(--va-ochre)) 52%, transparent);
            }
        }

        /* Cadmium red is opaque: on this page it is paint, never a wash and never
           a ground. Let down with water it would be pink, so its tint stays paper
           and its edge stays close to the tube. */
        #va .va-p-red.va-pg { --va-tint: var(--va-paper-2); --va-edge: var(--va-red); }
        .dark #va .va-p-red.va-pg,
        #va .va-night .va-p-red.va-pg { --va-deep: #ff6f5a; }

        /* Layer three: the ink keeps the pigment's hue and chroma at a fixed
           lightness, which is what makes AA hold for any pigment at all. */
        @supports (color: oklch(from red l c h)) {
            #va .va-pg { --va-deep: oklch(from var(--va-pig) 0.43 calc(c * 0.95) h); }
            .dark #va .va-pg,
            #va .va-night.va-pg,
            #va .va-night .va-pg { --va-deep: oklch(from var(--va-pig) 0.83 calc(c * 0.72) h); }
        }

        #va ::selection { background: var(--va-ochre); color: #1d1b1a; }
        #va a:focus-visible,
        #va summary:focus-visible,
        #va input:focus-visible {
            outline: 3px solid var(--va-teal);
            outline-offset: 3px;
        }

        .va-defs { position: absolute; width: 0; height: 0; overflow: hidden; }
        .va-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }

        .va-sec {
            position: relative;
            isolation: isolate;
            padding-block: clamp(4.5rem, 9vw, 8rem);
            background: var(--va-tooth), var(--va-paper);
        }
        .va-sec-tint { background: var(--va-tooth), var(--va-tint); }

        /* ---------------------------------------------------------------
           Washes: translucent colour that multiplies where it crosses.
           The granulating edge is an SVG filter, and it only ever sits
           on these empty layers, never on anything that holds text.
           --------------------------------------------------------------- */
        .va-washes { position: absolute; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }
        .va-w {
            position: absolute;
            border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%;
            background:
                radial-gradient(closest-side, rgba(0, 0, 0, 0) 58%, var(--va-wfill) 88%, rgba(0, 0, 0, 0) 100%),
                var(--va-wfill);
            filter: url(#va-bleed);
            mix-blend-mode: var(--va-blend);
            opacity: calc(var(--va-o) * var(--va-spread));
            scale: calc(0.7 + 0.3 * var(--va-spread));
        }
        #va .va-night .va-w { mix-blend-mode: screen; opacity: calc(0.5 * var(--va-spread)); }
        html.es-anim #va .va-hero .va-w,
        html.es-anim #va .va-finale .va-w {
            animation: va-spread 2.6s cubic-bezier(0.22, 1, 0.36, 1) both;
            animation-delay: calc(var(--i, 0) * 0.22s + 0.15s);
        }
        @supports (animation-timeline: view()) {
            html.es-anim #va .va-sec:not(.va-hero) .va-w {
                animation: va-spread linear both;
                animation-timeline: view();
                animation-range: entry 0% cover 36%;
                animation-delay: 0s;
            }
        }
        @keyframes va-spread {
            from { --va-spread: 0.08; }
            to { --va-spread: 1; }
        }

        /* ---------------------------------------------------------------
           Type
           --------------------------------------------------------------- */
        .va-d { font-family: var(--va-display); font-weight: 400; letter-spacing: -0.018em; line-height: 1.04; }
        .va-h2 { font-size: clamp(2.2rem, 5.2vw, 4.1rem); text-wrap: balance; }
        .va-h2-sm { font-size: clamp(1.9rem, 3.8vw, 2.9rem); }
        .va-wet { color: var(--va-deep); }
        .va-wet-u { position: relative; white-space: nowrap; }
        .va-wet-u::after {
            content: "";
            position: absolute;
            inset: auto -3% 0.02em -3%;
            height: 0.36em;
            z-index: -1;
            border-radius: 40% 60% 50% 50% / 60% 40% 60% 40%;
            background: var(--va-ochre);
            opacity: 0.5;
            filter: url(#va-bleed-sm);
            mix-blend-mode: var(--va-blend);
        }
        #va .va-night .va-wet-u::after { mix-blend-mode: screen; }
        .va-tag {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--va-deep);
        }
        .va-sub { color: var(--va-ink-2); font-size: 1.15rem; max-width: 40rem; }
        .va-small { color: var(--va-ink-2); font-size: 0.92rem; }
        #va p a,
        #va li a.va-inline {
            color: var(--va-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--va-edge);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }

        /* A plan is a daub of paint and a word. */
        .va-tier {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--va-ink-2);
            white-space: nowrap;
        }
        .va-tier::before {
            content: "";
            width: 0.78rem;
            height: 0.66rem;
            border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%;
            background: var(--va-viridian);
            rotate: -14deg;
        }
        .va-tier-pro::before { background: var(--va-red); rotate: 22deg; }

        /* Buttons */
        .va-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 0.95rem 1.6rem;
            border-radius: 999px;
            background: var(--va-ink);
            color: var(--va-paper);
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.2;
            box-shadow: 0 0.8rem 1.4rem -0.8rem var(--va-shade);
            transition: translate 0.2s ease, box-shadow 0.2s ease;
        }
        .va-btn::before {
            content: "";
            width: 0.75rem;
            height: 0.66rem;
            border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%;
            background: var(--va-red);
            rotate: -18deg;
            transition: scale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), rotate 0.3s ease;
        }
        .va-btn:hover { translate: 0 -2px; box-shadow: 0 1.1rem 1.6rem -0.8rem var(--va-shade); }
        .va-btn:hover::before { scale: 1.35; rotate: 40deg; }
        .va-btn svg { width: 1.1rem; height: 1.1rem; transition: translate 0.2s ease; }
        .va-btn:hover svg { translate: 3px 0; }
        .va-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.95rem 0.15rem 0.5rem;
            border-bottom: 2px solid var(--va-ochre);
            font-weight: 700;
            color: var(--va-ink);
            transition: gap 0.2s ease;
        }
        .va-ghost:hover { gap: 0.8rem; }
        .va-ghost svg { width: 1.05rem; height: 1.05rem; }
        .va-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.2rem;
            border-bottom: 2px solid var(--va-edge);
            font-weight: 700;
            color: var(--va-ink);
            transition: gap 0.2s ease;
        }
        .va-more:hover { gap: 0.8rem; }
        #va p a.va-more { text-decoration: none; }
        .va-more svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           The paint chip: the mark of the page
           --------------------------------------------------------------- */
        .va-chip {
            flex: none;
            width: 6.4rem;
            background: var(--va-sheet);
            box-shadow: 0 0 0 1px var(--va-line), 0 0.8rem 1.2rem -0.8rem var(--va-shade);
            rotate: -3deg;
            color: var(--va-ink-3);
        }
        .va-chip > i { display: block; height: 4.5rem; background: var(--va-paint); }
        .dark #va .va-chip > i,
        .dark #va .va-paintblock { box-shadow: inset 0 0 0 1px rgba(244, 240, 232, 0.1); }
        #va .va-night .va-chip > i { box-shadow: inset 0 0 0 1px rgba(244, 240, 232, 0.24); }
        .va-chip > div { display: grid; gap: 0.12rem; padding: 0.5rem 0.55rem 0.6rem; }
        .va-chip b { font-size: 0.55rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--va-ink); }
        .va-chip span { font-family: var(--va-display); font-size: 0.82rem; line-height: 1.15; color: var(--va-ink); }
        .va-chip small { font-size: 0.52rem; letter-spacing: 0.05em; line-height: 1.4; font-variant-numeric: tabular-nums; }

        .va-head { display: grid; gap: 1.5rem 2.25rem; margin-bottom: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 46rem) {
            .va-head { grid-template-columns: auto minmax(0, 1fr); align-items: start; }
        }
        .va-head .va-h2 { margin-block: 0.9rem 1.1rem; }

        /* The key to the chart: one test stroke per strand, full strength, with
           the dry ragged ends a loaded brush leaves. */
        .va-palette { display: none; }
        @media (min-width: 66rem) {
            .va-head-pal { grid-template-columns: auto minmax(0, 1fr) auto; }
            .va-palette { display: grid; gap: 1.15rem; order: 3; align-self: center; margin-inline-end: 4rem; }
        }
        .va-key { display: grid; gap: 0.3rem; rotate: var(--r, 0deg); }
        .va-key i {
            display: block;
            width: var(--s, 9rem);
            height: 1.05rem;
            border-radius: 0.5rem 0.15rem 0.3rem 0.55rem;
            background:
                repeating-linear-gradient(1deg, rgba(0, 0, 0, 0.1) 0 1px, rgba(255, 255, 255, 0.1) 1px 4px),
                var(--va-pig);
            filter: url(#va-rough);
        }
        .va-key b { font-size: 0.6rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--va-deep); }

        /* A strip of artist's tape */
        .va-tape {
            position: absolute;
            z-index: 2;
            top: -0.6rem;
            left: 50%;
            width: 4.8rem;
            height: 1.25rem;
            translate: -50% 0;
            rotate: var(--tape-r, -4deg);
            background: var(--va-tape);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.14);
            clip-path: polygon(0 0, 3% 20%, 0 40%, 3% 60%, 0 80%, 3% 100%, 97% 100%, 100% 80%, 97% 60%, 100% 40%, 97% 20%, 100% 0);
        }

        /* A sheet of paper with a deckled edge: the filter sits on the empty
           layer behind, so the words on the sheet stay crisp. */
        .va-paper { position: relative; isolation: isolate; }
        .va-paper::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: var(--va-sheet);
            filter: url(#va-deckle) drop-shadow(0 1.4rem 1.6rem var(--va-shade));
            opacity: 0.999;
        }
        .dark #va .va-paper { --va-sheet: #2a2622; }
        .dark #va .va-paper::before { filter: url(#va-deckle) drop-shadow(0 1.2rem 1.5rem rgba(0, 0, 0, 0.75)); }

        /* ---------------------------------------------------------------
           Hero: three washes and three chips
           --------------------------------------------------------------- */
        .va-hero { padding-block: clamp(2.5rem, 6vw, 4.5rem) clamp(4rem, 8vw, 6.5rem); overflow: clip; }
        .va-hero .va-wrap { container-type: inline-size; }
        /* On a phone the washes sit behind the chips, clear of the words. */
        .va-hw-1 { width: 17rem; height: 15rem; right: -6rem; top: 34rem; }
        .va-hw-2 { width: 15rem; height: 13rem; left: -5rem; top: 52rem; }
        .va-hw-3 { width: 18rem; height: 14rem; right: -5rem; bottom: 2rem; }
        .va-hw-4 { width: 11rem; height: 9rem; left: -4.5rem; top: -4.5rem; }
        .va-hw-5 { width: 12rem; height: 10rem; right: -5rem; top: 4rem; }
        @media (min-width: 62rem) {
            .va-hw-1 { width: min(38rem, 62vw); height: 26rem; right: -5rem; top: 8rem; }
            .va-hw-2 { width: min(27rem, 52vw); height: 21rem; left: auto; right: 20%; top: 19rem; }
            .va-hw-3 { width: min(28rem, 52vw); height: 20rem; right: 42%; bottom: -6rem; }
            .va-hw-4 { width: 15rem; height: 12rem; left: -4rem; top: -4rem; }
            .va-hw-5 { width: 13rem; height: 10rem; right: 3rem; top: auto; bottom: 1rem; }
        }
        .va-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            margin-bottom: 1.5rem;
            font-family: var(--va-text);
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            line-height: 1.4;
            color: var(--va-ink-2);
        }
        .va-eyebrow::before {
            content: "";
            flex: none;
            width: 2.6rem;
            height: 0.7rem;
            background: linear-gradient(90deg, var(--va-red) 0 33.3%, var(--va-ochre) 33.3% 66.6%, var(--va-viridian) 66.6%);
        }
        .va-h1 { font-size: clamp(2.75rem, 8.6cqi, 6.5rem); line-height: 1; letter-spacing: -0.028em; text-wrap: balance; }
        .va-reg {
            position: absolute;
            width: 1.3rem;
            height: 1.3rem;
            color: var(--va-ink-3);
            background:
                linear-gradient(currentColor, currentColor) 50% 50% / 100% 1px no-repeat,
                linear-gradient(currentColor, currentColor) 50% 50% / 1px 100% no-repeat;
            opacity: 0.6;
        }
        .va-reg::after { content: ""; position: absolute; inset: 26%; border: 1px solid currentColor; border-radius: 50%; }
        .va-hero-grid { display: grid; gap: 3rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 62rem) {
            .va-hero-grid { grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.7fr); gap: 3.5rem; align-items: start; }
        }
        .va-lede { font-size: clamp(1.1rem, 1.5vw, 1.25rem); color: var(--va-ink-2); max-width: 30rem; }
        .va-lede + .va-lede { margin-top: 1rem; font-size: 1.02rem; }
        .va-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.75rem; margin-top: 2rem; }

        .va-cards { display: grid; gap: 2.25rem 1.25rem; padding-top: 0.75rem; }
        @media (min-width: 40rem) {
            .va-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); align-items: start; }
            .va-cards .va-card:nth-child(2) { margin-top: 1.9rem; }
            .va-cards .va-card:nth-child(3) { margin-top: 0.6rem; }
        }
        .va-card {
            position: relative;
            background: var(--va-sheet);
            box-shadow: 0 0 0 1px var(--va-line), 0 1.6rem 2.2rem -1.5rem var(--va-shade);
            rotate: var(--tilt, 0deg);
            transition: rotate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .va-card:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .va-paintblock { position: relative; height: 5.4rem; background: var(--va-paint); }
        /* The foot of the block is the same pigment let down with water. */
        .va-paintblock::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 1.15rem;
            background: linear-gradient(90deg, rgba(255, 253, 248, 0), rgba(255, 253, 248, 0.82));
        }
        .va-p-red .va-paintblock::after,
        .va-p-red.va-paintblock::after { background: linear-gradient(90deg, rgba(233, 178, 74, 0), rgba(238, 196, 112, 0.94)); }
        .va-card-body { padding: 1rem 1.1rem 1.1rem; }
        .va-card-meta { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.4rem 0.75rem; }
        .va-strand { font-size: 0.64rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--va-deep); }
        .va-card-title { margin-top: 0.55rem; font-family: var(--va-display); font-size: 1.5rem; line-height: 1.1; letter-spacing: -0.01em; }
        .va-card-where { margin-top: 0.25rem; font-size: 0.92rem; color: var(--va-ink-2); }
        .va-pencil { height: 1px; margin-block: 0.85rem 0.75rem; background: var(--va-line-2); }
        .va-card-when { font-size: 0.92rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .va-card-note { margin-top: 0.35rem; font-size: 0.82rem; color: var(--va-ink-3); }
        .va-card-code {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            margin-top: 0.9rem;
            font-size: 0.56rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--va-ink-3);
            font-variant-numeric: tabular-nums;
        }
        .va-hero-note { margin-top: 2rem; max-width: 38rem; font-size: 0.86rem; color: var(--va-ink-2); }
        .va-hero-note b { color: var(--va-deep); font-weight: 700; }

        /* ---------------------------------------------------------------
           01 The year: the colour chart of a working year
           --------------------------------------------------------------- */
        .va-year { padding: clamp(1.4rem, 3.4vw, 2.6rem); }
        .va-ledger { width: 100%; border-collapse: collapse; text-align: start; }
        .va-ledger thead th {
            padding: 0 0.9rem 0.8rem 0;
            text-align: start;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--va-ink-3);
        }
        .va-ledger .va-row { border-top: 1px solid var(--va-line); }
        .va-ledger tbody th,
        .va-ledger tbody td { padding: 0.95rem 0.9rem 0.15rem 0; vertical-align: top; text-align: start; }
        .va-ledger tbody th { font-family: var(--va-display); font-weight: 400; font-size: 1.2rem; line-height: 1.2; }
        .va-ledger tbody th small {
            display: block;
            margin-top: 0.2rem;
            font-family: var(--va-text);
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--va-deep);
        }
        .va-ledger tbody td { font-size: 0.9rem; color: var(--va-ink-2); }
        .va-ledger .va-dates { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .va-ledger .va-setup .va-tier { margin-inline-start: 0.5rem; }
        .va-ledger .va-trackcell { padding: 0.35rem 0 0.95rem; }
        .va-track {
            position: relative;
            height: 1.05rem;
            border-bottom: 1px solid var(--va-line-2);
            background: repeating-linear-gradient(90deg, var(--va-line-2) 0 1px, rgba(0, 0, 0, 0) 1px 10%);
        }
        .va-tapebar {
            position: absolute;
            top: 0.08rem;
            bottom: 0.14rem;
            min-width: 0.8rem;
            background: var(--va-pig);
            opacity: 0.9;
            clip-path: polygon(0 0, 2px 25%, 0 50%, 2px 75%, 0 100%, 100% 100%, calc(100% - 2px) 75%, 100% 50%, calc(100% - 2px) 25%, 100% 0);
            transform-origin: 0 50%;
            transition: transform 1s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--d, 0s);
        }
        html.es-anim #va [data-reveal]:not(.is-revealed) .va-tapebar { transform: scaleX(0); }
        .va-ruler {
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            padding-top: 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--va-ink-3);
        }
        .va-year-note { margin-top: 1.4rem; padding-top: 1.1rem; border-top: 1px solid var(--va-line); font-size: 0.86rem; color: var(--va-ink-2); max-width: 54rem; }
        @media (max-width: 45.99rem) {
            .va-ledger thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
            .va-ledger,
            .va-ledger tbody,
            .va-ledger tfoot,
            .va-ledger tr,
            .va-ledger td,
            .va-ledger tbody th { display: block; }
            .va-ledger .va-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; column-gap: 0.75rem; padding-top: 0.9rem; }
            .va-ledger tbody th,
            .va-ledger tbody td { padding: 0; }
            .va-ledger tbody th { grid-area: 1 / 1; }
            .va-ledger .va-dates { grid-area: 1 / 2; text-align: end; padding-top: 0.25rem; font-size: 0.82rem; }
            .va-ledger .va-where { grid-area: 2 / 1 / 3 / -1; margin-top: 0.35rem; }
            .va-ledger .va-setup { grid-area: 3 / 1 / 4 / -1; margin-top: 0.1rem; }
            .va-ledger .va-trackcell { padding: 0.55rem 0 0.95rem; }
            .va-ruler { font-size: 0.5rem; letter-spacing: 0.04em; }
        }

        /* ---------------------------------------------------------------
           02 What a pin holds: four test cards and a mixing row
           --------------------------------------------------------------- */
        .va-notes { display: grid; gap: 2.25rem 2.25rem; }
        @media (min-width: 48rem) { .va-notes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .va-note {
            position: relative;
            display: flex;
            flex-direction: column;
            background: var(--va-sheet);
            box-shadow: 0 0 0 1px var(--va-line), 0 1.4rem 2rem -1.5rem var(--va-shade);
            rotate: var(--tilt, 0deg);
            transition: rotate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .va-note:hover { rotate: 0deg; translate: 0 -0.35rem; }
        /* A graded wash: full strength at one end, almost water at the other. */
        .va-grade { height: 3.1rem; margin: 0.85rem 0.9rem 0; overflow: hidden; }
        .va-grade i {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, var(--va-pig) 0%, var(--va-pig) 14%, rgba(255, 253, 248, 0) 100%);
            filter: url(#va-bleed-sm);
        }
        /* Where the stroke runs out, red is carried on by ochre rather than by water. */
        .va-p-red .va-grade i {
            background:
                linear-gradient(90deg, var(--va-pig) 0%, var(--va-pig) 16%, rgba(224, 60, 49, 0) 62%),
                linear-gradient(90deg, var(--va-ochre) 0%, var(--va-ochre) 45%, rgba(201, 149, 43, 0) 100%);
        }
        .va-note-body { padding: 1rem 1.35rem 1.4rem; }
        .va-note-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1rem; }
        .va-note h3 { margin-top: 0.6rem; font-family: var(--va-display); font-weight: 400; font-size: 1.55rem; line-height: 1.15; letter-spacing: -0.01em; }
        .va-note p { margin-top: 0.6rem; font-size: 0.98rem; color: var(--va-ink-2); }

        .va-mix {
            display: grid;
            gap: 0.9rem 1.25rem;
            margin-top: clamp(2.75rem, 5vw, 4rem);
            padding-top: 1.75rem;
            border-top: 1px solid var(--va-line-2);
            --va-a: var(--va-ochre);
            --va-b: var(--va-red);
        }
        @media (min-width: 52rem) { .va-mix { grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; } }
        .va-mix-end { display: flex; align-items: center; gap: 0.75rem; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--va-ink-2); }
        .va-mix-end i { width: 3rem; height: 3rem; border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%; background: var(--va-pig); }
        .va-mix-steps { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.3rem; }
        /* Without color-mix each step is a slice of one long gradient; with
           it, each is mixed for real, in a space that keeps the middle clean. */
        .va-mix-steps i {
            height: 3rem;
            background: linear-gradient(90deg, var(--va-a), var(--va-b)) calc(var(--n) * 16.667%) 0 / 700% 100%;
            transition: opacity 0.6s ease, translate 0.6s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--n) * 0.08s + 0.1s);
        }
        @supports (color: color-mix(in oklab, red 50%, blue)) {
            .va-mix-steps i { background: color-mix(in oklab, var(--va-a) var(--p), var(--va-b)); }
        }
        html.es-anim #va [data-reveal]:not(.is-revealed) .va-mix-steps i { opacity: 0; translate: 0 0.6rem; }
        .va-mix-cap { grid-column: 1 / -1; text-align: center; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--va-ink-3); }

        /* ---------------------------------------------------------------
           03 Where you have shown: a tray of tubes
           --------------------------------------------------------------- */
        .va-split { display: grid; gap: 3rem; }
        @media (min-width: 62rem) { .va-split { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 4.5rem; align-items: center; } }
        .va-points { display: grid; gap: 1.1rem; margin-top: 1.75rem; }
        .va-points li { display: grid; grid-template-columns: 1.05rem minmax(0, 1fr); gap: 0.85rem; }
        .va-points li::before {
            content: "";
            width: 0.95rem;
            height: 0.8rem;
            margin-top: 0.42rem;
            border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%;
            background: var(--va-pig);
            rotate: calc(var(--k, 0) * 37deg);
        }
        .va-points strong { display: block; font-weight: 700; }
        .va-points span { color: var(--va-ink-2); font-size: 0.98rem; }
        .va-freeline { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.4rem 0.9rem; margin-top: 1.75rem; }

        .va-tubes-box { container-type: inline-size; }
        .va-tubes { display: grid; gap: 1.5rem 1.75rem; grid-template-columns: minmax(0, 27rem); }
        @container (min-width: 33rem) { .va-tubes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .va-tube { display: flex; align-items: center; height: 4.9rem; rotate: var(--tilt, 0deg); filter: drop-shadow(0 0.7rem 0.55rem var(--va-shade)); }
        .va-tube-flip { flex-direction: row-reverse; }
        .va-tube-crimp {
            flex: none;
            width: 0.85rem;
            height: 100%;
            border-radius: 0.1rem;
            background: repeating-linear-gradient(90deg, #d9d3c6 0 2px, #a8a193 2px 4px);
        }
        .va-tube-body {
            position: relative;
            flex: 1;
            min-width: 0;
            height: 88%;
            border-radius: 0.15rem 1.1rem 1.1rem 0.15rem / 0.15rem 46% 46% 0.15rem;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.42), rgba(255, 255, 255, 0) 28%, rgba(0, 0, 0, 0.16) 82%, rgba(0, 0, 0, 0.3)),
                var(--va-pig);
        }
        .va-tube-flip .va-tube-body { border-radius: 1.1rem 0.15rem 0.15rem 1.1rem / 46% 0.15rem 0.15rem 46%; }
        /* A tube nobody has claimed is bare metal: no colour on it yet. */
        .va-tube-bare .va-tube-body {
            background: linear-gradient(180deg, #f3efe6, #cfc8ba 42%, #a39c8e 58%, #ddd6c8);
        }
        .va-tube-label {
            position: absolute;
            inset: 15% 15% 15% 7%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-inline: 0.75rem;
            background: #fffdf8;
            color: #1d1b1a;
        }
        .va-tube-flip .va-tube-label { inset: 15% 7% 15% 15%; }
        .va-tube-label strong { font-family: var(--va-display); font-weight: 400; font-size: 1rem; line-height: 1.15; }
        .va-tube-label small { margin-top: 0.15rem; font-size: 0.56rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #5d5751; line-height: 1.3; }
        .va-tube-neck { flex: none; width: 0.45rem; height: 34%; background: linear-gradient(180deg, #ece6d9, #a39c8e); }
        .va-tube-cap {
            flex: none;
            width: 1.05rem;
            height: 50%;
            border-radius: 0.15rem;
            background: repeating-linear-gradient(180deg, #2b2725 0 2px, #151312 2px 4px);
        }
        .va-tubes-note { margin-top: 1.9rem; font-size: 0.84rem; color: var(--va-ink-2); }

        /* ---------------------------------------------------------------
           04 After the opening: black paper, in both modes
           --------------------------------------------------------------- */
        #va .va-night {
            --va-paper: #171413;
            --va-paper-2: #1e1a18;
            --va-sheet: #211d1a;
            --va-ink: #f4f0e8;
            --va-ink-2: #cfc8bd;
            --va-ink-3: #a69e93;
            --va-line: rgba(244, 240, 232, 0.14);
            --va-line-2: rgba(244, 240, 232, 0.28);
            --va-pig: #e9e2d4;
            background-color: #171413;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='240' height='240'%3E%3Cfilter id='t'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 0.95 0 0 0 0 0.88 0 0 0 0.08 0'/%3E%3C/filter%3E%3Crect width='240' height='240' filter='url(%23t)'/%3E%3C/svg%3E");
            color: #f4f0e8;
        }
        .dark #va .va-night { box-shadow: inset 0 1px 0 rgba(244, 240, 232, 0.12), inset 0 -1px 0 rgba(244, 240, 232, 0.12); }
        .va-night .va-head { text-align: center; grid-template-columns: minmax(0, 1fr); justify-items: center; }
        .va-night .va-sub { margin-inline: auto; }
        #va .va-night .va-tier::before { background: #57c2ad; }
        #va .va-night .va-tier-pro::before { background: #f0665b; }
        .va-duo { display: grid; gap: 1.5rem; }
        @media (min-width: 58rem) { .va-duo { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2rem; } }
        .va-plate { padding: clamp(1.4rem, 3vw, 2.1rem); background: var(--va-sheet); border: 1px solid var(--va-line); }
        .va-plate h3 { margin-top: 0.7rem; font-family: var(--va-display); font-weight: 400; font-size: 1.7rem; line-height: 1.15; letter-spacing: -0.01em; }
        .va-plate > p { margin-top: 0.8rem; font-size: 0.98rem; color: var(--va-ink-2); }
        .va-plate ul { display: grid; margin-top: 1.4rem; }
        .va-plate li { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.9rem; align-items: center; padding-block: 0.8rem; border-top: 1px solid var(--va-line); }
        .va-plate li i { width: 1.7rem; height: 1.5rem; border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%; background: var(--c); rotate: var(--r, 0deg); opacity: 0.92; }
        .va-plate li strong { display: block; font-weight: 700; }
        .va-plate li span { display: block; font-size: 0.86rem; color: var(--va-ink-3); }
        /* A flex row, not grid tracks: Safari keeps the widths it measured for auto tracks
           before the web fonts arrived, and then wraps "the default" inside a track that
           is a few pixels too narrow for the real face. */
        .va-plate .va-leader { display: flex; align-items: baseline; }
        .va-plate .va-leader::after { content: none; }
        .va-leader b { font-weight: 700; }
        .va-leader u { flex: 1 0 1rem; text-decoration: none; height: 0; border-bottom: 2px dotted var(--va-line-2); translate: 0 -0.25em; }
        .va-leader em { flex: none; font-style: normal; font-family: var(--va-display); font-size: 1.25rem; color: #f2c98a; }
        .va-plate-foot { margin-top: 1.2rem; font-size: 0.84rem; color: var(--va-ink-3); }
        .va-night-foot { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.7rem 2rem; margin-top: 2.25rem; font-size: 0.9rem; color: var(--va-ink-2); }
        .va-night-foot span { display: inline-flex; flex-wrap: wrap; align-items: baseline; gap: 0.3rem 0.7rem; }
        #va .va-night a:focus-visible { outline-color: #f2c98a; }

        /* ---------------------------------------------------------------
           05 Putting a price on it: three pans in a box
           --------------------------------------------------------------- */
        .va-pans { display: grid; gap: 1.75rem; }
        @media (min-width: 56rem) { .va-pans { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .va-pancard {
            display: flex;
            flex-direction: column;
            background: var(--va-sheet);
            box-shadow: 0 0 0 1px var(--va-line), 0 1.4rem 2rem -1.5rem var(--va-shade);
        }
        .va-pan {
            margin: 1rem 1rem 0;
            height: 6.6rem;
            padding: 0.45rem;
            border-radius: 0.55rem;
            background: linear-gradient(#f6f1e6, #e0d9ca);
            box-shadow: inset 0 0 0 1px rgba(29, 27, 26, 0.14), inset 0 2px 5px rgba(29, 27, 26, 0.16);
        }
        .va-pan i {
            display: block;
            height: 100%;
            border-radius: 0.3rem;
            background:
                radial-gradient(ellipse 30% 36% at 42% 58%, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0) 76%),
                repeating-linear-gradient(118deg, rgba(0, 0, 0, 0.06) 0 3px, rgba(255, 255, 255, 0.05) 3px 8px),
                var(--va-pig);
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.2), inset 0 0.55rem 0.7rem rgba(0, 0, 0, 0.26), inset 0 -0.15rem 0.3rem rgba(255, 255, 255, 0.2);
            transition: filter 0.5s ease;
        }
        .va-pancard:hover .va-pan i { filter: saturate(1.25) brightness(1.06); }
        .va-pan-body { display: flex; flex: 1; flex-direction: column; padding: 1.2rem 1.35rem 1.4rem; }
        .va-pan-body h3 { font-family: var(--va-display); font-weight: 400; font-size: 1.5rem; line-height: 1.15; letter-spacing: -0.01em; }
        .va-pan-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.4rem 0.9rem; margin-top: 0.55rem; }
        .va-pan-body > p { margin-top: 0.8rem; font-size: 0.97rem; color: var(--va-ink-2); }
        .va-pan-fig { margin-top: auto; padding-top: 1.4rem; }
        .va-pan-fig > div { padding: 0.85rem 1rem 0.9rem; background: var(--va-tint); border: 1px dashed var(--va-edge); }
        .va-pan-fig small { display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--va-ink-3); }
        .va-pan-fig b { font-family: var(--va-display); font-weight: 400; font-size: 1.9rem; line-height: 1.2; color: var(--va-deep); }
        .va-pan-fig span { font-size: 0.86rem; color: var(--va-ink-2); }
        .va-sell-note { margin: 2.5rem auto 0; max-width: 46rem; text-align: center; font-size: 0.92rem; color: var(--va-ink-2); }

        /* ---------------------------------------------------------------
           06 Telling people: one pigment, six strengths
           --------------------------------------------------------------- */
        .va-chart { display: grid; gap: 0 2.75rem; }
        @media (min-width: 44rem) { .va-chart { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 70rem) { .va-chart { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .va-dil { display: grid; grid-template-columns: 3.6rem minmax(0, 1fr); gap: 1.1rem; align-items: start; padding-block: 1.6rem; border-top: 1px solid var(--va-line-2); }
        .va-dil-sw { padding: 0.28rem 0.28rem 0; background: var(--va-sheet); box-shadow: 0 0 0 1px var(--va-line), 0 0.5rem 0.8rem -0.5rem var(--va-shade); rotate: var(--tilt, 0deg); }
        /* Strength by opacity first; then mixed down into the sheet itself. */
        .va-dil-sw i { display: block; aspect-ratio: 1; background: var(--va-pig); opacity: var(--o); }
        @supports (color: color-mix(in oklab, red 50%, blue)) {
            .va-dil-sw i { opacity: 1; background: color-mix(in oklab, var(--va-pig) var(--p), var(--va-sheet)); }
        }
        .va-dil-sw b { display: block; padding-block: 0.2rem 0.25rem; text-align: center; font-size: 0.52rem; font-weight: 700; letter-spacing: 0.1em; color: var(--va-ink-3); font-variant-numeric: tabular-nums; }
        .va-dil h3 { margin-top: 0.45rem; font-family: var(--va-display); font-weight: 400; font-size: 1.4rem; line-height: 1.15; letter-spacing: -0.01em; }
        .va-dil p { margin-top: 0.55rem; font-size: 0.96rem; color: var(--va-ink-2); }

        /* ---------------------------------------------------------------
           07 Perfect for: six swatch cards
           --------------------------------------------------------------- */
        .va-swatches { display: grid; gap: 2rem 1.75rem; }
        @media (min-width: 40rem) { .va-swatches { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 66rem) { .va-swatches { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2.5rem 2.25rem; } }
        .va-sw {
            display: flex;
            flex-direction: column;
            background: var(--va-sheet);
            box-shadow: 0 0 0 1px var(--va-line), 0 1.5rem 2.1rem -1.5rem var(--va-shade);
            rotate: var(--tilt, 0deg);
            transition: rotate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .va-sw:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .va-sw .va-paintblock { height: 7.2rem; }
        .va-sw-body { display: flex; flex: 1; flex-direction: column; padding: 1.15rem 1.3rem 0.4rem; }
        .va-sw h3 { font-family: var(--va-display); font-weight: 400; font-size: 1.6rem; line-height: 1.12; letter-spacing: -0.012em; }
        .va-sw p { margin-top: 0.6rem; font-size: 0.97rem; color: var(--va-ink-2); }
        .va-sw-link { margin-top: auto; padding-top: 1rem; align-self: flex-start; }
        .va-sw-link span { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; font-size: 0.95rem; border-bottom: 2px solid var(--va-edge); transition: gap 0.2s ease; }
        .va-sw-link:hover span { gap: 0.7rem; }
        .va-sw-link svg { width: 1rem; height: 1rem; }
        .va-sw-foot {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            margin: 1rem 1.3rem 0;
            padding-block: 0.7rem 0.85rem;
            border-top: 1px solid var(--va-line);
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--va-ink-3);
            font-variant-numeric: tabular-nums;
        }

        /* ---------------------------------------------------------------
           Key features: the fan deck, and the list it answers to
           --------------------------------------------------------------- */
        .va-kf { display: grid; gap: 2.5rem 4rem; align-items: center; }
        @media (min-width: 60rem) { .va-kf { grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr); } }
        .va-fan { position: relative; height: 19rem; --va-step: 15deg; }
        .va-fan:hover { --va-step: 19deg; }
        .va-leaf {
            position: absolute;
            left: calc(50% - 2.75rem);
            bottom: 0.5rem;
            width: 5.5rem;
            height: 17rem;
            display: flex;
            flex-direction: column;
            background: #fffdf8;
            color: #1d1b1a;
            box-shadow: 0 0 0 1px rgba(29, 27, 26, 0.16), 0 0.7rem 1rem -0.6rem var(--va-shade);
            transform-origin: 50% calc(100% - 1.3rem);
            transform: rotate(calc((var(--i) - 2) * var(--va-step))) translateY(var(--va-lift, 0rem));
            transition: transform 0.55s cubic-bezier(0.34, 1.3, 0.64, 1);
        }
        .va-leaf i { flex: none; height: 56%; background: var(--va-paint); }
        .va-leaf b { padding: 0.5rem 0.5rem 0; font-family: var(--va-display); font-weight: 400; font-size: 0.86rem; line-height: 1.12; }
        .va-leaf small { padding: 0.25rem 0.5rem 0; font-size: 0.5rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #5d5751; font-variant-numeric: tabular-nums; }
        .va-rivet {
            position: absolute;
            left: calc(50% - 0.45rem);
            bottom: 1.35rem;
            width: 0.9rem;
            height: 0.9rem;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 30%, #f7f3ea, #a8a193 60%, #6b655f);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        }
        html.es-anim #va .va-fan[data-reveal]:not(.is-revealed) { --va-step: 1.5deg; }
        /* The list picks a leaf out of the deck. */
        #va .va-kf:has(.va-kf-list li:nth-child(1) a:is(:hover, :focus-visible)) .va-leaf:nth-of-type(1),
        #va .va-kf:has(.va-kf-list li:nth-child(2) a:is(:hover, :focus-visible)) .va-leaf:nth-of-type(2),
        #va .va-kf:has(.va-kf-list li:nth-child(3) a:is(:hover, :focus-visible)) .va-leaf:nth-of-type(3),
        #va .va-kf:has(.va-kf-list li:nth-child(4) a:is(:hover, :focus-visible)) .va-leaf:nth-of-type(4),
        #va .va-kf:has(.va-kf-list li:nth-child(5) a:is(:hover, :focus-visible)) .va-leaf:nth-of-type(5) { --va-lift: -2rem; }
        .va-kf-list { border-bottom: 1px solid var(--va-line-2); }
        .va-kf-row {
            display: grid;
            grid-template-columns: 1.1rem minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: center;
            padding: 1.1rem 0.4rem;
            border-top: 1px solid var(--va-line-2);
            transition: background-color 0.25s ease, padding 0.25s ease;
        }
        .va-kf-row::before {
            content: "";
            width: 1rem;
            height: 0.85rem;
            border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%;
            background: var(--va-pig);
            transition: scale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .va-kf-row:hover { background: var(--va-tint); padding-inline: 1rem 0.6rem; }
        .va-kf-row:hover::before { scale: 1.4; }
        .va-kf-row strong { display: block; font-family: var(--va-display); font-weight: 400; font-size: 1.4rem; line-height: 1.15; letter-spacing: -0.01em; }
        .va-kf-row small { display: block; margin-top: 0.2rem; font-size: 0.95rem; color: var(--va-ink-2); }
        .va-kf-row svg { width: 1.25rem; height: 1.25rem; color: var(--va-deep); transition: translate 0.25s ease; }
        .va-kf-row:hover svg { translate: 0.3rem 0; }
        .va-kf-more { margin-top: 1.6rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials: they
           keep their words and their prices, and take the page's paper.
           --------------------------------------------------------------- */
        #va .va-plans > section { background: var(--va-tooth), var(--va-paper-2); }
        #va .va-plans h2 { font-family: var(--va-display); font-weight: 400; letter-spacing: -0.018em; line-height: 1.06; font-size: clamp(2rem, 4.2vw, 3.2rem); color: var(--va-ink); }
        #va .va-plans h2 + p { color: var(--va-ink-2); font-size: 1.0625rem; }
        #va .va-plans .grid > div { background: var(--va-sheet); border: 1px solid var(--va-line); border-radius: 0; box-shadow: 0 1.4rem 2rem -1.5rem var(--va-shade); color: var(--va-ink); }
        #va .va-plans .grid > div:nth-child(2) { border-color: var(--va-red); border-width: 2px; }
        #va .va-plans .grid > div span,
        #va .va-plans .grid > div p,
        #va .va-plans .grid > div li { color: var(--va-ink-2); }
        #va .va-plans .grid > div .text-3xl { font-family: var(--va-display); font-weight: 400; font-size: 2.6rem; color: var(--va-ink); }
        #va .va-plans .grid > div .uppercase { color: var(--va-ink); letter-spacing: 0.2em; }
        #va .va-plans .grid > div .rounded-full { background: var(--va-ink); color: var(--va-paper); letter-spacing: 0.1em; }
        #va .va-plans .grid > div svg { color: var(--va-viridian); }
        #va .va-plans a.font-medium { color: var(--va-ink); border-bottom: 2px solid var(--va-ochre); }
        #va .va-plans a.rounded-2xl { background: var(--va-ink); color: var(--va-paper); border-radius: 999px; box-shadow: 0 0.8rem 1.4rem -0.8rem var(--va-shade); }

        #va .va-keep > section { background: var(--va-tooth), var(--va-paper-2); border-top: 1px solid var(--va-line-2); }
        #va .va-keep h2 { font-family: var(--va-display); font-weight: 400; letter-spacing: -0.018em; font-size: clamp(1.9rem, 3.6vw, 2.7rem); color: var(--va-ink); }
        #va .va-keep p.uppercase { color: var(--va-ink-2); letter-spacing: 0.2em; font-weight: 700; }
        #va .va-keep .grid > a { background: var(--va-sheet); border: 1px solid var(--va-line); border-radius: 0; }
        #va .va-keep .grid > a:hover { border-color: var(--va-line-2); box-shadow: 0 1.4rem 2rem -1.5rem var(--va-shade); }
        #va .va-keep .grid > a > span:first-child { display: none; }
        #va .va-keep .grid > a h3 { font-family: var(--va-display); font-weight: 400; font-size: 1.25rem; color: var(--va-ink); }
        #va .va-keep .grid > a p { color: var(--va-ink-2); }
        #va .va-keep .grid > a > span:last-child,
        #va .va-keep a.self-start { color: var(--va-ink); }

        /* ---------------------------------------------------------------
           Related pages: four more chips
           --------------------------------------------------------------- */
        .va-rel-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1.25rem; margin-bottom: 2.5rem; }
        .va-rel { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem 1.25rem; }
        @media (min-width: 56rem) { .va-rel { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.75rem; } }
        .va-rel a {
            display: block;
            background: var(--va-sheet);
            box-shadow: 0 0 0 1px var(--va-line), 0 1.3rem 1.8rem -1.4rem var(--va-shade);
            rotate: var(--tilt, 0deg);
            transition: rotate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.45s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .va-rel a:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .va-rel .va-paintblock { height: 4.6rem; }
        .va-rel small { display: block; padding: 0.8rem 0.9rem 0; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--va-ink-3); }
        .va-rel strong { display: block; padding: 0.15rem 0.9rem 1rem; font-family: var(--va-display); font-weight: 400; font-size: clamp(1.15rem, 2vw, 1.45rem); line-height: 1.15; }

        /* ---------------------------------------------------------------
           08 Questions
           --------------------------------------------------------------- */
        .va-faq-grid { display: grid; gap: 2.5rem 4.5rem; align-items: start; }
        @media (min-width: 62rem) {
            .va-faq-grid { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); }
            .va-faq-head { position: sticky; top: 6.5rem; }
        }
        .va-faq-head .va-h2 { margin-block: 1rem 0; }
        .va-faq-head .va-chip { margin-bottom: 1.6rem; }
        .va-qa { border-bottom: 1px solid var(--va-line-2); }
        .va-qa details { border-top: 1px solid var(--va-line-2); }
        .va-qa details:nth-child(6n + 1) { --va-pig: var(--va-red); }
        .va-qa details:nth-child(6n + 2) { --va-pig: var(--va-ochre); }
        .va-qa details:nth-child(6n + 3) { --va-pig: var(--va-viridian); }
        .va-qa details:nth-child(6n + 4) { --va-pig: var(--va-teal); }
        .va-qa details:nth-child(6n + 5) { --va-pig: var(--va-sienna); }
        .va-qa details:nth-child(6n + 6) { --va-pig: var(--va-ink-3); }
        .va-qa summary { display: grid; grid-template-columns: 1.3rem minmax(0, 1fr); gap: 1.1rem; align-items: start; padding: 1.35rem 0.25rem; cursor: pointer; }
        .va-qa summary::before {
            content: "";
            width: 1.15rem;
            height: 1rem;
            margin-top: 0.45rem;
            border-radius: 46% 54% 58% 42% / 52% 44% 56% 48%;
            border: 2px solid var(--va-pig);
            transition: background-color 0.3s ease, scale 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), rotate 0.35s ease;
        }
        .va-qa details[open] summary::before { background: var(--va-pig); scale: 1.2; rotate: 28deg; }
        .va-qa h3 { font-family: var(--va-display); font-weight: 400; font-size: clamp(1.25rem, 2vw, 1.5rem); line-height: 1.25; letter-spacing: -0.01em; }
        .va-qa details p { padding: 0 0.25rem 1.6rem 2.65rem; max-width: 46rem; color: var(--va-ink-2); }
        @media (max-width: 34rem) { .va-qa details p { padding-inline-start: 0.25rem; } }

        /* ---------------------------------------------------------------
           Finale: a clean sheet, and a line to sign
           --------------------------------------------------------------- */
        .va-finale { overflow: clip; }
        .va-sheet {
            max-width: 52rem;
            margin-inline: auto;
            padding: clamp(2.25rem, 6vw, 4.5rem) clamp(1.4rem, 5vw, 4rem) clamp(2.5rem, 5vw, 3.75rem);
            text-align: center;
        }
        .va-sheet .va-reg { top: 1.1rem; left: 1.1rem; }
        .va-sheet .va-reg + .va-reg { left: auto; right: 1.1rem; }
        .va-sheet .va-h2 { margin-block: 1rem 1.2rem; }
        .va-sheet .va-sub { margin-inline: auto; }
        .va-strip { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); max-width: 40rem; height: 1.5rem; margin: 2.25rem auto 0; }
        .va-strip i { background: var(--va-pig); }
        .va-strip i:last-child { background: var(--va-ink); }
        .va-claimbox { max-width: 40rem; margin: 0 auto; padding: 1.15rem 1.15rem 1.25rem; background: var(--va-paper); border: 1px solid var(--va-line-2); border-top: 0; text-align: start; }
        .va-claimbox label { display: block; margin-bottom: 0.55rem; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--va-ink-3); }
        .va-claimrow { display: grid; gap: 0.75rem; }
        @media (min-width: 40rem) { .va-claimrow { grid-template-columns: minmax(0, 1fr) auto; } }
        #va .va-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1rem;
            border: 1px solid var(--va-line-2);
            border-radius: 0;
            background: var(--va-sheet);
            font-weight: 700;
            font-size: clamp(0.92rem, 3.2vw, 1.05rem);
            font-variant-numeric: tabular-nums;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        #va .va-claim:focus-within { border-color: var(--va-ink); box-shadow: 0 0 0 4px var(--va-line); }
        #va .va-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: var(--va-ink);
            box-shadow: none;
            outline: none;
        }
        #va .va-claim input::placeholder { color: var(--va-ink-3); opacity: 1; }
        .va-claim span { flex: none; color: var(--va-ink-3); user-select: none; }
        /* Below 500px the box's own type is under 16px, and iOS zooms the page when a
           smaller field takes focus. What is typed is 16px; the placeholder keeps the
           size of the address it sits beside. */
        @media (max-width: 31.24rem) {
            #va .va-claim input { font-size: 1rem; }
            #va .va-claim input::placeholder { font-size: clamp(0.92rem, 3.2vw, 1.05rem); }
        }
        /* On the narrowest phones the box gives up some padding so "your-studio" is not cut. */
        @media (max-width: 24.99rem) {
            #va .va-claimbox { padding-inline: 0.5rem; }
            #va .va-claim { padding-inline: 0.6rem; }
        }
        .va-claim-note { margin-top: 1.1rem; font-size: 0.9rem; color: var(--va-ink-3); }
        .va-sign {
            display: block;
            margin-top: 1.5rem;
            text-align: end;
            font-family: var(--va-hand);
            font-size: clamp(1.5rem, 3vw, 2rem);
            line-height: 1.1;
            color: var(--va-ink-2);
            rotate: -3deg;
            transform-origin: 100% 50%;
            overflow-wrap: anywhere;
        }

        /* ---------------------------------------------------------------
           The paint box: the section rail, on wide screens only
           --------------------------------------------------------------- */
        .va-rail { display: none; }
        @media (min-width: 87.5rem) {
            /* The nav is a box the size of the page that clips the paint box, so the box
               stays fixed to the screen and still ends where the page does instead of
               riding over the site footer. */
            .va-rail {
                display: block;
                position: absolute;
                inset: 0;
                z-index: 40;
                clip-path: inset(0);
                pointer-events: none;
            }
            .va-rail ol {
                position: fixed;
                right: 1.25rem;
                top: 50%;
                translate: 0 -50%;
                display: grid;
                padding: 0.25rem 0.275rem;
                border-radius: 0.5rem;
                background: #f1ebde;
                box-shadow: 0 0 0 1px rgba(29, 27, 26, 0.2), 0 0.8rem 1.4rem -0.8rem rgba(29, 27, 26, 0.6);
                pointer-events: auto;
            }
            /* Each pan sits in a 24px square, which is the link: the smallest target a
               finger or a pointer should be asked to hit. */
            .va-rail a { position: relative; display: grid; place-items: center; width: 1.5rem; height: 1.5rem; padding: 0; }
            .va-rail i {
                display: block;
                width: 1.25rem;
                height: 1.05rem;
                border-radius: 0.2rem;
                background: var(--va-pig);
                box-shadow: inset 0 0.2rem 0.3rem rgba(0, 0, 0, 0.3);
                scale: 0.82;
                transition: scale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
            }
            .va-rail a:hover i { scale: 1; }
            .va-rail a.is-active i { scale: 1.12; box-shadow: inset 0 0.2rem 0.3rem rgba(0, 0, 0, 0.3), 0 0 0 2px #1d1b1a; }
            .va-rail span {
                position: absolute;
                right: calc(100% + 0.9rem);
                top: 50%;
                translate: 0.3rem -50%;
                padding: 0.3rem 0.6rem;
                background: #1d1b1a;
                color: #f4f0e8;
                font-size: 0.68rem;
                font-weight: 700;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease, translate 0.2s ease;
            }
            .va-rail a:hover span,
            .va-rail a:focus-visible span { opacity: 1; translate: 0 -50%; }
        }

        @media (prefers-reduced-motion: reduce) {
            .va-w,
            .va-leaf,
            .va-card,
            .va-note,
            .va-sw,
            .va-rel a,
            .va-btn,
            .va-tapebar,
            .va-mix-steps i,
            .va-kf-row { animation: none; transition: none; }
        }
    </style>

    @php
        // The six pigments, by the names on the tube: name, colour index code,
        // series, and the opacity mark a tube carries.
        $pigments = [
            'red'      => ['Cadmium Red',  'PR108', 'Series 4', 'Opaque'],
            'ochre'    => ['Yellow Ochre', 'PY43',  'Series 1', 'Semi-opaque'],
            'viridian' => ['Viridian',     'PG18',  'Series 3', 'Transparent'],
            'teal'     => ['Cobalt Teal',  'PG50',  'Series 4', 'Semi-opaque'],
            'sienna'   => ['Burnt Sienna', 'PBr7',  'Series 1', 'Transparent'],
            'black'    => ['Ivory Black',  'PBk9',  'Series 1', 'Semi-opaque'],
        ];

        // The three strands. A sub-schedule carries a colour (Group.color) and a
        // slug of its own, so the page's palette is the product's own organising
        // device rather than decoration: each strand is a pigment.
        $strands = [
            'exhibitions' => ['Exhibitions', 'red'],
            'studio'      => ['Studio',      'ochre'],
            'teaching'    => ['Teaching',    'viridian'],
        ];

        // The wall in the hero: three cards, one per strand, each a real product
        // configuration. Rotations are fixed here so the wall is the same wall on
        // every render.
        $wall = [
            [
                'strand' => 'exhibitions',
                'title'  => 'Ten Windows',
                'where'  => 'Bell Street Gallery',
                'when'   => 'Mar 6 to Apr 4',
                'note'   => 'One entry, on the gallery\'s page and on yours',
                'tilt'   => '-1.6deg',
                'plan'   => null,
            ],
            [
                'strand' => 'studio',
                'title'  => 'Open studio',
                'where'  => 'The studio, 11am to 5pm',
                'when'   => 'First Saturday, Apr to Nov',
                'note'   => 'One recurring event, two dates taken out',
                'tilt'   => '1.2deg',
                'plan'   => null,
            ],
            [
                'strand' => 'teaching',
                'title'  => 'Monotype workshop',
                'where'  => 'Eight places, three left',
                'when'   => 'Sat Jun 20, 10am',
                'note'   => 'Places sold in advance, counted for that date',
                'tilt'   => '-0.8deg',
                'plan'   => 'Free',
            ],
        ];

        // The working year, March to December: ten months, so one month is ten
        // per cent. left/width are computed from the dates printed in the same
        // row, which is why the tape and the text cannot drift apart.
        $ledger = [
            ['Ten Windows',        'Bell Street Gallery', 'Mar 6 to Apr 4',      'exhibitions', 2,  10, 'One event, accepted onto both pages',        null],
            ['Open studio',        'The studio',          'First Sat, Apr to Nov', 'studio',    11,  79, 'One recurring event, two dates removed',     null],
            ['Spring Art Fair',    'Riverside Fair',      'May 8 to 11',         'exhibitions', 22,   3, 'One event, at the fair as the venue',        null],
            ['Monotype workshop',  'The studio',          'Sat Jun 20',          'teaching',    36,   3, 'Eight places, plus questions at checkout',   'Pro'],
            ['Slow Water',         'Kiln Room',           'Sep 4 to Oct 12',     'exhibitions', 61,  13, 'A Draft until the gallery announces it',     null],
            ['Studio visits',      'The studio',          'By appointment',      'studio',       1,  98, 'Bookable slots, not a fixed date',           null],
            ['Winter print sale',  'The studio',          'Dec 5 to 7',          'studio',      91,   3, 'Free to attend, with a capacity',            null],
        ];
        $months = ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // The header wall of venues, from Role::logoWallRoles(). Placeholder names
        // only: no third-party logo is drawn, and no real gallery is named.
        $plates = [
            ['Bell Street Gallery', 'Links to their page'],
            ['Kiln Room', 'Links to their page'],
            ['Riverside Fair', 'Logo only, until claimed'],
            ['The Annexe', 'Links to their page'],
            ['Harbour Print Room', 'Links to their page'],
            ['Fold Projects', 'Logo only, until claimed'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for visual artists?',
                'a' => 'Yes. The wall itself costs nothing: your public page and its permanent link, recurring open studio dates with individual dates taken out, sub-schedules with their own colour and their own link, Drafts that stay off the page until you announce, the header wall of the venues you have shown with, two-way Google, Outlook and CalDAV calendar sync, an embeddable calendar, built-in analytics, a downloadable QR code for your schedule, shareable graphics of your upcoming shows, free RSVP with a capacity, one bookable appointment type, QR check-in at the door, and up to 10 newsletter emails a month, counted per recipient rather than per send. Putting a price on a place is where the Pro plan starts, at '.plan_price($proMonthly).' a month, which also adds your own questions at checkout and more appointment types. Event Schedule charges zero platform fees on ticket sales on every plan.',
            ],
            [
                'q' => 'Can I list exhibitions, open studios and art fairs together?',
                'a' => 'Yes, and you can keep them apart at the same time. Sub-schedules sort one page into strands, each with its own colour and its own link, so you can send a gallery the exhibitions and a school the workshops without splitting yourself into two pages. To be clear about what a sub-schedule is: it organises and colour-codes, it does not hide anything. If a show is not announced yet, keep the event as a Draft.',
            ],
            [
                'q' => 'How do collectors and art lovers find out about a new show?',
                'a' => 'By email, by calendar and by link. Anyone can leave their email on your schedule, and once they confirm it they hear about the dates you add yourself, as one digest rather than a message per show. A show a gallery lists for you is not in that digest, so that is the one worth a newsletter, which you write and send in your own words. A collector who would rather not give an address can subscribe to your schedule as a live calendar from its page, so every show on it, the galleries\' included, turns up in their own calendar. On a ticketed opening that is not on sale yet, and with the "Notify me" card switched on, the event page also offers "Tell me when tickets go on sale": one email when they do, one if you cancel, and a reminder 48 hours before, plus any change notice you send. And there is the link: your schedule has one permanent address you can put in a bio, print on a show card, hand out as a QR code, or embed in the portfolio site you already have.',
            ],
            [
                'q' => 'Do my open studio Saturdays have to be entered one at a time?',
                'a' => 'No. One recurring event covers the whole run: pick the pattern, every week or the same weekday each month, set the hours, and give the recurrence an end, either a date or a number of dates. Date exceptions take out the weekends you are away without rebuilding anything, and every date is a real occurrence with its own page, its own iCal file and its own RSVP count.',
            ],
            [
                'q' => 'Can I sell places at a workshop or a ticketed opening?',
                'a' => 'Yes, on the Pro plan at '.plan_price($proMonthly).' a month, which is what lets a place carry a price. Create as many named ticket types as the event needs, each with its own price and quantity. The quantity is counted per occurrence date, so a full March does not stop April selling. Check people in with a QR code at the door on any plan, and take the money through your own Stripe or PayPal account, or as cash, a payment link or Invoice Ninja. Pro also adds your own questions at checkout. A free opening asks for none of it: registration with a capacity is on every plan. Event Schedule charges zero platform fees either way, so what you keep is the price less the processor\'s fee, and a Stripe or PayPal sale can be refunded in full or in part from the Sales page.',
            ],
            [
                'q' => 'What happens to the photographs people take at the opening?',
                'a' => 'They can go on the event. Visitors can add photos, videos and comments with just a name and an email, and everything lands in an approval queue first, so nothing is public until you have looked at it. A per-schedule setting can require an account instead. Free schedules hold up to 25 photos; the Pro plan removes the cap and lets you download an event\'s approved photos as a zip.',
            ],
            [
                'q' => 'Can I show a gallery\'s exhibition without retyping it?',
                'a' => 'Yes. When a gallery lists you on their event, it arrives on your schedule and waits for you to accept it, unless you have added that gallery to your Approved Schedules, in which case it goes straight on. Accept it and the same entry appears on both pages, so the dates cannot end up saying two different things. Nothing shows on your page that you have not agreed to.',
            ],
            [
                'q' => 'A gallery listed me, but I am not on Event Schedule. What happens?',
                'a' => 'The listing creates a page in your name. It says which schedule created it and that you have not claimed it, each date on it is credited to the schedule that added it, and it stays out of search engines until you claim it. Claim it by signing in with the email address on it: the page becomes your schedule, and the galleries that already list you stay approved, so their dates keep appearing. If the page carries no email address or phone number, there is nothing to check a claim against, so ask the gallery to send you an invitation. If the page is not you at all, "This is not me" asks for it to come down.',
            ],
        ];

        $dotSections = [
            ['top', 'The wall'],
            ['year', 'The year'],
            ['pin', 'What a pin holds'],
            ['plates', 'Where you have shown'],
            ['opening', 'After the opening'],
            ['sell', 'Putting a price on it'],
            ['tell', 'Telling people'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];

        // Which pigment each section is ground in, for the rail.
        $vaSectionPigment = [
            'top' => 'red', 'year' => 'red', 'pin' => 'ochre', 'plates' => 'sienna', 'opening' => 'black',
            'sell' => 'viridian', 'tell' => 'teal', 'who' => 'ochre', 'faq' => 'sienna', 'claim' => 'red',
        ];

        // The numbered chip that opens a section, as a tube manufacturer's card.
        $vaChip = function (string $key, string $no) use ($pigments): string {
            [$name, $code, $series] = $pigments[$key];

            return '<div class="va-chip va-pg va-p-'.$key.'" aria-hidden="true"><i></i><div><b>No. '.$no.'</b><span>'.$name.'</span><small>'.$code.' &#183; '.$series.'<br>&#9733;&#9733;&#9733; Lightfast I</small></div></div>';
        };

        $vaArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $vaDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="va">

        {{-- Four textures, as SVG filters: the bleeding, granulating edge of a wash, a
             smaller one for a single stroke, the rough edge of a loaded brush, and the
             deckle of a torn sheet. They are applied only to empty layers, never to
             anything that carries words. --}}
        <svg class="va-defs" width="0" height="0" aria-hidden="true" focusable="false">
            <defs>
                <filter id="va-bleed" x="-18%" y="-18%" width="136%" height="136%" color-interpolation-filters="sRGB">
                    <feTurbulence type="fractalNoise" baseFrequency="0.011 0.017" numOctaves="3" seed="11" result="warp" />
                    <feDisplacementMap in="SourceGraphic" in2="warp" scale="64" xChannelSelector="R" yChannelSelector="G" result="shape" />
                    <feTurbulence type="fractalNoise" baseFrequency="0.7" numOctaves="2" seed="4" result="grain" />
                    <feColorMatrix in="grain" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 -0.95 1.2" result="pores" />
                    <feComposite in="shape" in2="pores" operator="in" />
                </filter>
                <filter id="va-bleed-sm" x="-6%" y="-40%" width="112%" height="180%" color-interpolation-filters="sRGB">
                    <feTurbulence type="fractalNoise" baseFrequency="0.03 0.11" numOctaves="2" seed="7" result="warp" />
                    <feDisplacementMap in="SourceGraphic" in2="warp" scale="11" xChannelSelector="R" yChannelSelector="G" result="shape" />
                    <feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="2" seed="2" result="grain" />
                    <feColorMatrix in="grain" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 -0.8 1.15" result="pores" />
                    <feComposite in="shape" in2="pores" operator="in" />
                </filter>
                <filter id="va-rough" x="-6%" y="-40%" width="112%" height="180%" color-interpolation-filters="sRGB">
                    <feTurbulence type="fractalNoise" baseFrequency="0.035 0.16" numOctaves="2" seed="9" result="warp" />
                    <feDisplacementMap in="SourceGraphic" in2="warp" scale="9" xChannelSelector="R" yChannelSelector="G" />
                </filter>
                <filter id="va-deckle" x="-3%" y="-3%" width="106%" height="106%" color-interpolation-filters="sRGB">
                    <feTurbulence type="fractalNoise" baseFrequency="0.045" numOctaves="3" seed="5" result="warp" />
                    <feDisplacementMap in="SourceGraphic" in2="warp" scale="7" xChannelSelector="R" yChannelSelector="G" />
                </filter>
            </defs>
        </svg>

        <nav class="va-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot va-p-{{ $vaSectionPigment[$sectionId] }}" aria-label="{{ $sectionLabel }}"><i></i><span aria-hidden="true">{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: three washes, three chips                           -->
        <!-- ============================================================ -->
        <section id="top" class="va-sec va-hero" style="scroll-margin-top: 5rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w va-hw-1 va-pg va-p-ochre" style="--i: 0;"></i>
                <i class="va-w va-hw-2 va-pg va-p-sienna" style="--i: 1;"></i>
                <i class="va-w va-hw-3 va-pg va-p-viridian" style="--i: 2;"></i>
                <i class="va-w va-hw-4 va-pg va-p-teal" style="--i: 3;"></i>
                <i class="va-w va-hw-5 va-pg va-p-teal" style="--i: 4;"></i>
            </div>

            <div class="va-wrap">
                <h1 class="va-d va-h1">
                    <x-marketing.hero-eyebrow class="va-eyebrow es-fade-up es-d-1">Artist exhibition calendar for makers</x-marketing.hero-eyebrow>
                    <span class="es-mask"><span class="es-mask-line">Every date is already</span></span>
                    <span class="es-mask es-mask-2"><span class="es-mask-line">pinned to <span class="va-wet va-wet-u va-pg va-p-red">your wall</span>.</span></span>
                </h1>

                <div class="va-hero-grid">
                    <div>
                        <p class="va-lede es-fade-up es-d-2">
                            The card from the gallery. The fair confirmation. The note saying which
                            Saturdays the studio is open. It is all there by the door, and it is only
                            readable by people standing in the room.
                        </p>
                        <p class="va-lede es-fade-up es-d-2">
                            Event Schedule is that wall with an address on it: one page for your exhibitions, open studios and workshops.
                        </p>

                        <div class="va-cta es-fade-up es-d-3">
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="va-btn">
                                Start your wall
                                {!! $vaArrow !!}
                            </a>
                            <a href="#year" class="va-ghost">
                                See what goes on it
                                {!! $vaDown !!}
                            </a>
                        </div>
                    </div>

                    <!-- The colour chart: three chips, one per strand, each a real product configuration. -->
                    <div class="es-fade-up es-d-4">
                        <div class="va-cards">
                            @foreach ($wall as $w)
                                @php
                                    [$sName, $sPig] = $strands[$w['strand']];
                                    [$pName, $pCode] = $pigments[$sPig];
                                @endphp
                                <div class="va-card va-pg va-p-{{ $sPig }}" style="--tilt: {{ $w['tilt'] }}; --tape-r: {{ $loop->index === 1 ? '5deg' : '-4deg' }};">
                                    <span class="va-tape" aria-hidden="true"></span>
                                    <div class="va-paintblock" aria-hidden="true"></div>
                                    <div class="va-card-body">
                                        <div class="va-card-meta">
                                            <span class="va-strand">{{ $sName }}</span>
                                            @if ($w['plan'])
                                                <span class="va-tier {{ $w['plan'] === 'Pro' ? 'va-tier-pro' : '' }}">{{ $w['plan'] }}</span>
                                            @endif
                                        </div>
                                        <p class="va-card-title">{{ $w['title'] }}</p>
                                        <p class="va-card-where">{{ $w['where'] }}</p>
                                        <div class="va-pencil" aria-hidden="true"></div>
                                        <p class="va-card-when">{{ $w['when'] }}</p>
                                        <p class="va-card-note">{{ $w['note'] }}</p>
                                        <div class="va-card-code" aria-hidden="true"><span>{{ $pName }}</span><span>{{ $pCode }}</span></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <p class="va-hero-note">
                            <b class="va-pg va-p-red">Exhibitions</b>,
                            <b class="va-pg va-p-ochre">studio</b> and
                            <b class="va-pg va-p-viridian">teaching</b> are three sub-schedules.
                            The colours are the product's own way of sorting a page, not a decoration
                            on this one.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The year (01): the chart of a working year                -->
        <!-- ============================================================ -->
        <section id="year" class="va-sec va-pg va-p-red va-sec-tint" style="scroll-margin-top: 4rem;">
            <div class="va-wrap">
                <div class="va-head va-head-pal">
                    <div class="va-palette" aria-hidden="true" data-reveal>
                        @foreach ($strands as [$keyName, $keyPig])
                            <span class="va-key va-pg va-p-{{ $keyPig }}" style="--s: {{ ['10.5rem', '8rem', '9.25rem'][$loop->index] }}; --r: {{ ['-2deg', '1.5deg', '-1deg'][$loop->index] }};"><i></i><b>{{ $keyName }}</b></span>
                        @endforeach
                    </div>
                    <div data-reveal>{!! $vaChip('red', '01') !!}</div>
                    <div>
                        <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">The year</span>
                        <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s;">
                            Nine months of work,
                            <span class="va-wet va-wet-u">one wall</span>.
                        </h2>
                        <p class="va-sub" data-reveal style="--reveal-delay: 0.15s;">
                            A solo show, a fair booth, a studio that opens once a month, a workshop with
                            eight places in it. Four different kinds of thing, pinned to the same board,
                            on one link you only ever have to hand out once.
                        </p>
                    </div>
                </div>

                <div class="va-paper va-year" data-reveal="panel">
                    <span class="va-tape" aria-hidden="true" style="left: 9%; --tape-r: -32deg;"></span>
                    <span class="va-tape" aria-hidden="true" style="left: 91%; --tape-r: 31deg;"></span>
                    <table class="va-ledger">
                        <caption class="sr-only">A working year, March to December: each show with where it is, its dates, how it is set up in Event Schedule, and a bar showing when it runs.</caption>
                        <thead>
                            <tr>
                                <th scope="col">Show</th>
                                <th scope="col">Where</th>
                                <th scope="col">Dates</th>
                                <th scope="col">Set up as</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledger as $i => [$lName, $lWhere, $lDates, $lStrand, $lLeft, $lWidth, $lSetup, $lPlan])
                                @php [$sName, $sPig] = $strands[$lStrand]; @endphp
                                <tr class="va-row va-pg va-p-{{ $sPig }}">
                                    <th scope="row">
                                        {{ $lName }}
                                        <small>{{ $sName }}</small>
                                    </th>
                                    <td class="va-where">{{ $lWhere }}</td>
                                    <td class="va-dates">{{ $lDates }}</td>
                                    <td class="va-setup">
                                        {{ $lSetup }}
                                        @if ($lPlan)
                                            <span class="va-tier va-tier-pro">{{ $lPlan }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="va-p-{{ $sPig }}">
                                    <td colspan="4" class="va-trackcell">
                                        <div class="va-track" aria-hidden="true">
                                            <div class="va-tapebar" style="left: {{ $lLeft }}%; width: {{ $lWidth }}%; --d: {{ $i * 0.07 }}s;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">
                                    <div class="va-ruler" aria-hidden="true">
                                        @foreach ($months as $m)
                                            <span>{{ $m }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <p class="va-year-note">
                        The tape is the colour of the strand it belongs to, and its position comes
                        from the same dates the row prints. Only one row here needs the Pro plan,
                        the workshop: eight places with a price on them, and its own questions at
                        checkout. The rest of the wall, print sale included, is on the free plan.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. What a pin holds (02): four test cards and a mix          -->
        <!-- ============================================================ -->
        <section id="pin" class="va-sec va-pg va-p-ochre" style="scroll-margin-top: 4rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w" style="width: 22rem; height: 18rem; left: -8rem; top: 5rem;"></i>
                <i class="va-w va-pg va-p-sienna" style="width: 15rem; height: 12rem; left: 3rem; top: 15rem;"></i>
            </div>
            <div class="va-wrap">
                <div class="va-head">
                    <div data-reveal>{!! $vaChip('ochre', '02') !!}</div>
                    <div>
                        <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">What a pin holds</span>
                        <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s;">
                            A date is not the only thing <span class="va-wet">on the card</span>.
                        </h2>
                        <p class="va-sub" data-reveal style="--reveal-delay: 0.15s;">
                            Four things the wall in the studio already does, and how the page does them.
                            All four are on the free plan.
                        </p>
                    </div>
                </div>

                <div class="va-notes" data-reveal-group="100">
                    @foreach ([
                        ['studio', 'The Saturdays repeat', 'One recurring event covers the whole run: pick the pattern, every week or the same weekday each month, set the hours, and give it an end, either a closing date or a number of dates. Date exceptions take out the two weekends you are away, so a change to the pattern is not a rebuild.'],
                        ['exhibitions', 'The show nobody has announced', 'Keep the event as a Draft and it stays off your public page until the gallery has sent the invitations. Then publish it. A sub-schedule cannot do this, because a sub-schedule has no visibility of its own; hiding is what Draft is for.'],
                        ['teaching', 'Prints, paintings and teaching, sorted', 'Sub-schedules split one page into strands, each with its own colour and its own link. Send a school the workshops and a gallery the exhibitions, from a page you only maintain once.'],
                        ['exhibitions', 'The gallery already typed it', 'When a gallery lists you on their event it arrives on your schedule and waits for you to accept it, unless you have already approved that gallery. Accept it and the same entry shows on both pages, so the dates cannot end up saying two different things. Not on Event Schedule yet? The listing makes a page in your name, and signing in with the email address on it makes that page yours.'],
                    ] as $pi => [$pStrand, $pTitle, $pBody])
                        @php [$sName, $sPig] = $strands[$pStrand]; @endphp
                        <div data-reveal class="va-note va-pg va-p-{{ $sPig }}" style="--tilt: {{ $pi % 2 === 0 ? '-0.7deg' : '0.7deg' }};">
                            <div class="va-grade" aria-hidden="true"><i></i></div>
                            <div class="va-note-body">
                                <div class="va-note-meta">
                                    <span class="va-strand">{{ $sName }}</span>
                                    <span class="va-tier">Free</span>
                                </div>
                                <h3>{{ $pTitle }}</h3>
                                <p>{{ $pBody }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- The mixing row: two pages, and the one entry between them -->
                <div class="va-mix" aria-hidden="true" data-reveal>
                    <div class="va-mix-end"><i class="va-p-ochre"></i>Their page</div>
                    <div class="va-mix-steps">
                        @foreach (['100%', '83.33%', '66.67%', '50%', '33.33%', '16.67%', '0%'] as $mi => $mixP)
                            <i style="--n: {{ $mi }}; --p: {{ $mixP }};"></i>
                        @endforeach
                    </div>
                    <div class="va-mix-end"><i class="va-p-red"></i>Your page</div>
                    <p class="va-mix-cap">The same entry, on both</p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Where you have shown (03): a tray of tubes                -->
        <!-- ============================================================ -->
        <section id="plates" class="va-sec va-pg va-p-sienna va-sec-tint" style="scroll-margin-top: 4rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w" style="width: 26rem; height: 20rem; right: -6rem; bottom: -4rem;"></i>
            </div>
            <div class="va-wrap va-split">
                <div>
                    <div data-reveal style="margin-bottom: 1.75rem;">{!! $vaChip('sienna', '03') !!}</div>
                    <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">Where you have shown</span>
                    <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s; margin-block: 0.9rem 1.2rem;">
                        The galleries go up <span class="va-wet">on the wall too</span>.
                    </h2>
                    <p class="va-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Your page header can be a wall of the logos of the venues hosting your events,
                        each claimed one linking through to that venue's own schedule. It builds
                        itself out of shows you have already entered, so the exhibition history at
                        the top of your page is a side effect of keeping the dates straight.
                    </p>

                    <ul class="va-points" data-reveal-group="90">
                        @foreach ([
                            ['Only the shows you both agreed to', 'A venue appears once you have accepted their event, and a venue that runs its own schedule has to have accepted yours too. Nothing goes on your wall over somebody\'s objection.'],
                            ['Drafts stay off it', 'Draft, cancelled and unlisted events are excluded, so a show that has not been announced does not leak out through the header.'],
                            ['You set the order', 'Drag the ones that matter to the front. The rest fall in alphabetically behind them.'],
                        ] as $plIndex => [$plTitle, $plBody])
                            <li data-reveal style="--k: {{ $plIndex }};">
                                <div><strong>{{ $plTitle }}</strong> <span>{{ $plBody }}</span></div>
                            </li>
                        @endforeach
                    </ul>

                    <p class="va-freeline" data-reveal>
                        <span class="va-tier">Free</span>
                        <span class="va-small">Every plan. It is a banner-header option on your schedule, not an add-on.</span>
                    </p>
                </div>

                <!-- The tubes. One per venue: painted once it is claimed, bare metal until then. -->
                <div class="va-tubes-box" data-reveal="panel">
                    <div class="va-tubes">
                        @foreach ($plates as $pk => [$plateName, $plateNote])
                            @php $tubePig = ['red', 'teal', 'ochre', 'viridian', 'sienna', 'ochre'][$pk]; @endphp
                            <div class="va-tube va-p-{{ $tubePig }} {{ $pk % 2 === 1 ? 'va-tube-flip' : '' }} {{ str_starts_with($plateNote, 'Logo only') ? 'va-tube-bare' : '' }}" style="--tilt: {{ $pk % 2 === 0 ? '-1.4deg' : '1.2deg' }};">
                                <span class="va-tube-crimp" aria-hidden="true"></span>
                                <div class="va-tube-body">
                                    <div class="va-tube-label">
                                        <strong>{{ $plateName }}</strong>
                                        <small>{{ $plateNote }}</small>
                                    </div>
                                </div>
                                <span class="va-tube-neck" aria-hidden="true"></span>
                                <span class="va-tube-cap" aria-hidden="true"></span>
                            </div>
                        @endforeach
                    </div>
                    <p class="va-tubes-note">
                        A venue needs a picture on its own schedule to appear, and the wall holds up
                        to thirty-six of them. A venue that has claimed its schedule links through to
                        it. One you typed in yourself gets a page of its own, marked as unclaimed, and
                        its tile stays a plain logo until the venue claims that page with the email
                        address or phone number you entered for it. Names here are illustrative, and
                        the real wall shows the logos rather than the names.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. After the opening (04): black paper, in both modes        -->
        <!-- ============================================================ -->
        <section id="opening" class="va-sec va-night va-pg" style="scroll-margin-top: 4rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w va-pg va-p-ochre" style="width: 24rem; height: 18rem; left: -7rem; top: -3rem;"></i>
                <i class="va-w va-pg va-p-teal" style="width: 22rem; height: 17rem; right: -6rem; bottom: -4rem;"></i>
                <i class="va-w va-pg va-p-sienna" style="width: 13rem; height: 10rem; right: 9rem; bottom: 4rem;"></i>
            </div>
            <div class="va-wrap">
                <div class="va-head">
                    <div data-reveal>{!! $vaChip('black', '04') !!}</div>
                    <div>
                        <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">After the opening</span>
                        <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s;">
                            The wall fills up
                            <span class="va-wet va-wet-u va-pg va-p-ochre">on its own</span>.
                        </h2>
                        <p class="va-sub" data-reveal style="--reveal-delay: 0.15s;">
                            People photograph an opening whether you ask them to or not, and the pictures
                            end up somewhere you will never see them. They can go on the event instead.
                        </p>
                    </div>
                </div>

                <!-- Duplex: what visitors send, and what you let through. -->
                <div class="va-duo" data-reveal-group="110">
                    <div class="va-plate va-pg va-p-teal" data-reveal="panel">
                        <span class="va-tag">What they send</span>
                        <h3>A name, an email, and a photograph</h3>
                        <p>
                            Visitors add photos, videos and comments to the event without making an
                            account. If you would rather they signed in, a per-schedule setting asks
                            for an account instead.
                        </p>
                        <ul>
                            @foreach ([
                                ['Photographs', 'Of the room, the work, the crowd', '#f4f0e8', '-12deg'],
                                ['Video', 'A short clip of the space', '#e2b455', '18deg'],
                                ['Comments', 'What people said about a piece', '#46bfc9', '-28deg'],
                            ] as [$fTitle, $fBody, $fColour, $fTurn])
                                <li>
                                    <i aria-hidden="true" style="--c: {{ $fColour }}; --r: {{ $fTurn }};"></i>
                                    <div><strong>{{ $fTitle }}</strong><span>{{ $fBody }}</span></div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="va-plate va-pg va-p-ochre" data-reveal="panel">
                        <span class="va-tag">What you let through</span>
                        <h3>Nothing is public until you say so</h3>
                        <p>
                            Everything lands in an approval queue first. You are the editor of your
                            own page, which matters when the work in the photograph is yours.
                        </p>
                        <ul>
                            <li class="va-leader"><b>Held for approval</b><u aria-hidden="true"></u><em>the default</em></li>
                            <li class="va-leader"><b>Photos on a free schedule</b><u aria-hidden="true"></u><em>25</em></li>
                            <li class="va-leader"><b>Photos on Pro, and a zip per event</b><u aria-hidden="true"></u><em>no cap</em></li>
                        </ul>
                        <p class="va-plate-foot">
                            Star ratings and written feedback after the event are a separate Pro
                            feature, if you want to know what people actually thought.
                        </p>
                    </div>
                </div>

                <p class="va-night-foot" data-reveal>
                    <span><span class="va-tier">Free</span> Photos, videos and comments with the approval queue, on every plan.</span>
                    <span><span class="va-tier va-tier-pro">Pro</span> Removes the 25-photo cap and adds the per-event zip download.</span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Putting a price on it (05): three pans                    -->
        <!-- ============================================================ -->
        <section id="sell" class="va-sec va-pg va-p-viridian" style="scroll-margin-top: 4rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w" style="width: 25rem; height: 18rem; right: -8rem; top: 1rem;"></i>
                <i class="va-w va-pg va-p-ochre" style="width: 14rem; height: 11rem; right: 6rem; top: 9rem;"></i>
            </div>
            <div class="va-wrap">
                <div class="va-head">
                    <div data-reveal>{!! $vaChip('viridian', '05') !!}</div>
                    <div>
                        <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">Putting a price on it</span>
                        <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s;">
                            A free opening, a paid workshop, <span class="va-wet">an hour in the studio</span>.
                        </h2>
                        <p class="va-sub" data-reveal style="--reveal-delay: 0.15s;">
                            Three different ways of counting people, and the honest answer about which
                            plan each one is on.
                        </p>
                    </div>
                </div>

                <div class="va-pans" data-reveal-group="100">
                    <div class="va-pancard va-pg va-p-red" data-reveal>
                        <div class="va-pan" aria-hidden="true"><i></i></div>
                        <div class="va-pan-body">
                            <span class="va-strand">Exhibitions</span>
                            <div class="va-pan-head">
                                <h3>The opening, free but counted</h3>
                                <span class="va-tier">Free</span>
                            </div>
                            <p>
                                Turn on registration and put a capacity on it. The count is kept per date, so
                                a full first Saturday leaves the next one untouched, and the registration
                                page shows how many places are left.
                            </p>
                            <div class="va-pan-fig" aria-hidden="true">
                                <div>
                                    <small>Sat Mar 6, opening</small>
                                    <b>54</b> <span>of 80 registered</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="va-pancard va-pg va-p-viridian" data-reveal>
                        <div class="va-pan" aria-hidden="true"><i></i></div>
                        <div class="va-pan-body">
                            <span class="va-strand">Teaching</span>
                            <div class="va-pan-head">
                                <h3>The workshop, sold in advance</h3>
                                <span class="va-tier va-tier-pro">Pro</span>
                            </div>
                            <p>
                                Named ticket types with their own price and quantity, counted per occurrence
                                date. Scan a QR code at the door and take the money through your own Stripe
                                or PayPal account, or in cash. A place that costs money is the Pro plan;
                                Pro also adds your own questions at checkout.
                            </p>
                            <div class="va-pan-fig" aria-hidden="true">
                                <div>
                                    <small>Platform fee</small>
                                    <b>{{ plan_price(0) }}</b> <span>on every sale</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="va-pancard va-pg va-p-ochre" data-reveal>
                        <div class="va-pan" aria-hidden="true"><i></i></div>
                        <div class="va-pan-body">
                            <span class="va-strand">Studio</span>
                            <div class="va-pan-head">
                                <h3>Studio visits, by appointment</h3>
                                <span class="va-tier">Free</span>
                            </div>
                            <p>
                                Publish bookable slots instead of a fixed date. Set your weekly hours, how
                                far apart slots start, a buffer between them, and per-date overrides for the
                                days you are away or installing. Charge by Stripe, a payment link or cash,
                                or keep it free. One bookable type is free; Pro adds more.
                            </p>
                            <div class="va-pan-fig" aria-hidden="true">
                                <div>
                                    <small>Thursdays</small>
                                    <b>2pm</b> <span>to 5pm, 45 min</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="va-sell-note" data-reveal>
                    Event Schedule takes nothing out of a ticket price on any plan. You pay Stripe
                    or PayPal what they charge, the rest arrives in your own account, and a sale
                    through either can be refunded in full or in part from the Sales page. A
                    workshop here is places, not positions: numbered seats on a seat map belong to
                    venue schedules on the Enterprise plan.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Telling people (06): one pigment, six strengths           -->
        <!-- ============================================================ -->
        <section id="tell" class="va-sec va-pg va-p-teal va-sec-tint" style="scroll-margin-top: 4rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w" style="width: 24rem; height: 18rem; left: -9rem; bottom: -3rem;"></i>
            </div>
            <div class="va-wrap">
                <div class="va-head">
                    <div data-reveal>{!! $vaChip('teal', '06') !!}</div>
                    <div>
                        <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">Telling people</span>
                        <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s;">
                            One address, and a way <span class="va-wet">to write to it</span>.
                        </h2>
                        <p class="va-sub" data-reveal style="--reveal-delay: 0.15s;">
                            A confirmed email address hears about the dates you add yourself as a short
                            digest. Anything in your own words is a newsletter, and you decide when it is
                            worth sending.
                        </p>
                    </div>
                </div>

                <div class="va-chart" data-reveal-group="90">
                    @foreach ([
                        ['Free', 'Followers and newsletters', 'An email address on your list is told about the dates you add yourself, as a digest that goes out on its own and costs nothing from the allowance. A newsletter you write is what the allowance counts: 10 emails a month free, 100 on Pro and 1,000 on Enterprise, per recipient rather than per send.'],
                        ['Free', 'A QR code for the door', 'Download a QR code that opens your schedule and put it on the show card, the price list, or a card by the door of the studio. On every plan.'],
                        ['Free', 'On your site and in their calendar', 'Drop the calendar into the portfolio site you already have, so the dates there are your schedule\'s dates. Collectors can subscribe to the same schedule as a live calendar, and a new show turns up in theirs.'],
                        ['Free', 'Google, Outlook and CalDAV', 'Two-way sync, so the install week, the opening and the fair sit in the calendar you actually look at, and a change in either place reaches the other.'],
                        ['Free', 'Who is reading', 'Built-in analytics on your schedule: which shows people opened, and how the page is being found.'],
                        ['Free', 'A picture to post', 'Generate one shareable image out of the flyers of your upcoming events, up to twenty at a time, with your own header and footer text and the date on each if you want it. An event without a flyer image is not in it.'],
                    ] as $ti => [$tPlan, $tTitle, $tBody])
                        @php [$tStrength, $tOpacity] = [['100%', '1'], ['82%', '0.82'], ['64%', '0.64'], ['46%', '0.46'], ['30%', '0.3'], ['16%', '0.16']][$ti]; @endphp
                        <div data-reveal class="va-dil">
                            <div class="va-dil-sw" aria-hidden="true" style="--tilt: {{ $ti % 2 === 0 ? '-2deg' : '1.5deg' }};">
                                <i style="--p: {{ $tStrength }}; --o: {{ $tOpacity }};"></i>
                                <b>{{ rtrim($tStrength, '%') }}</b>
                            </div>
                            <div>
                                <span class="va-tier {{ $tPlan === 'Pro' ? 'va-tier-pro' : '' }}">{{ $tPlan }}</span>
                                <h3>{{ $tTitle }}</h3>
                                <p>{{ $tBody }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Perfect for (07): six swatch cards                        -->
        <!-- ============================================================ -->
        <section id="who" class="va-sec va-pg va-p-ochre" style="scroll-margin-top: 4rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w va-pg va-p-viridian" style="width: 20rem; height: 15rem; right: -6rem; top: 3rem;"></i>
                <i class="va-w va-pg va-p-teal" style="width: 13rem; height: 10rem; right: 6rem; top: 0rem;"></i>
            </div>
            <div class="va-wrap">
                <div class="va-head">
                    <div data-reveal>{!! $vaChip('ochre', '07') !!}</div>
                    <div>
                        <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">Perfect for</span>
                        <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s;">
                            Built for every <span class="va-wet">visual medium</span>
                        </h2>
                        <p class="va-sub" data-reveal style="--reveal-delay: 0.1s;">
                            Whether the work is oil, clay or pixels, the wall is the same wall.
                        </p>
                    </div>
                </div>

                @php
                    $vaMedia = [
                        ['Painters & Illustrators', 'Gallery openings, studio shows and art walks. Publish the dates once and hand out one link for all of them.', 'for-painters-illustrators', 'red', '-1deg'],
                        ['Sculptors & Installation Artists', 'Site-specific installations, gallery exhibitions and public unveilings. Tell people where the work actually is.', 'for-sculptors-installation-artists', 'sienna', '0.8deg'],
                        ['Photographers', 'Photo exhibitions, gallery talks and portfolio reviews. Openings on the calendar, bookable reviews alongside them.', 'for-photographers', 'black', '-0.6deg'],
                        ['Printmakers', 'Print exhibitions, studio sales and edition releases. Put the sale weekend on the wall and let people register for it.', 'for-printmakers', 'teal', '0.9deg'],
                        ['Mixed Media & Makers', 'Interdisciplinary shows, pop-ups and collaborations. One page holds an eclectic year without flattening it.', 'for-mixed-media-artists', 'ochre', '-0.8deg'],
                        ['Digital Artists', 'Screenings, launches and gallery shows. An online event carries the link people join on, next to the ones with an address.', 'for-digital-artists', 'viridian', '1deg'],
                    ];
                @endphp

                <div class="va-swatches" data-reveal-group="70">
                    @foreach ($vaMedia as [$mName, $mDesc, $mSlug, $mPig, $mTilt])
                        @php
                            $mPost = get_sub_audience_blog($mSlug);
                            [$pName, $pCode, $pSeries, $pOpacity] = $pigments[$mPig];
                        @endphp
                        <article class="va-sw va-pg va-p-{{ $mPig }}" data-reveal style="--tilt: {{ $mTilt }};">
                            <div class="va-paintblock" aria-hidden="true"></div>
                            <div class="va-sw-body">
                                <h3>{{ $mName }}</h3>
                                <p>{{ $mDesc }}</p>
                                @if ($mPost)
                                    <a href="{{ blog_url('/' . $mPost->slug) }}" class="va-sw-link" aria-label="Learn more about Event Schedule for {{ $mName }}">
                                        <span>Learn more {!! $vaArrow !!}</span>
                                    </a>
                                @endif
                            </div>
                            <div class="va-sw-foot" aria-hidden="true"><span>{{ $pName }} &#183; {{ $pCode }}</span><span>{{ $pOpacity }}</span></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Key features: the fan deck                                -->
        <!-- ============================================================ -->
        <section class="va-sec va-pg va-p-sienna va-sec-tint">
            <div class="va-wrap">
                @php
                    $vaFeatures = [
                        ['Recurring Events', 'Open studio dates as one entry, with the weekends you are away removed', marketing_url('/features/recurring-events'), 'ochre'],
                        ['Fan Photos & Videos', 'What visitors shot at the opening, held for your approval', marketing_url('/features/fan-videos'), 'teal'],
                        ['Ticketing', 'Places at a workshop, with QR check-in and zero platform fees', marketing_url('/features/ticketing'), 'red'],
                        ['Appointments', 'Bookable studio visits with your own hours and days off', marketing_url('/features/appointments'), 'viridian'],
                        ['Newsletters', 'You write it, you send it, to the people who followed you', marketing_url('/features/newsletters'), 'sienna'],
                    ];
                @endphp
                <div class="va-kf">
                    <div class="va-fan" aria-hidden="true" data-reveal>
                        @foreach ($vaFeatures as $fi => [$fName, $fDesc, $fUrl, $fPig])
                            <span class="va-leaf va-pg va-p-{{ $fPig }}" style="--i: {{ $fi }};"><i></i><b>{{ $fName }}</b><small>{{ $pigments[$fPig][1] }} &#183; {{ $pigments[$fPig][0] }}</small></span>
                        @endforeach
                        <span class="va-rivet"></span>
                    </div>

                    <div>
                        <h2 class="va-d va-h2 va-h2-sm" data-reveal style="margin-bottom: 1.75rem;">Key features</h2>
                        <ul class="va-kf-list" data-reveal-group="70">
                            @foreach ($vaFeatures as [$fName, $fDesc, $fUrl, $fPig])
                                <li data-reveal>
                                    <a href="{{ $fUrl }}" class="va-kf-row va-pg va-p-{{ $fPig }}">
                                        <span>
                                            <strong>{{ $fName }}</strong>
                                            <small>{{ $fDesc }}</small>
                                        </span>
                                        {!! $vaArrow !!}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="va-kf-more" data-reveal>
                            <a href="{{ marketing_url('/features') }}" class="va-more">
                                See all features
                                {!! $vaArrow !!}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="va-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 10. Related pages: four more chips                           -->
        <!-- ============================================================ -->
        <section class="va-sec va-pg va-p-teal">
            <div class="va-wrap">
                <div class="va-rel-head">
                    <h2 class="va-d va-h2 va-h2-sm" data-reveal>Related pages</h2>
                    <a href="{{ marketing_url('/use-cases') }}" class="va-more" data-reveal>
                        See all use cases
                        {!! $vaArrow !!}
                    </a>
                </div>
                <div class="va-rel" data-reveal-group="70">
                    @foreach ([
                        ['/for-art-galleries', 'Art Galleries', 'red', '-1.2deg'],
                        ['/for-dance-groups', 'Dance Groups', 'ochre', '0.9deg'],
                        ['/for-circus-acrobatics', 'Circus & Acrobatics', 'teal', '-0.7deg'],
                        ['/for-musicians', 'Musicians', 'viridian', '1.1deg'],
                    ] as [$relHref, $relName, $relPig, $relTilt])
                        <a href="{{ marketing_url($relHref) }}" data-reveal class="va-pg va-p-{{ $relPig }}" style="--tilt: {{ $relTilt }};">
                            <span class="va-paintblock" style="display: block;" aria-hidden="true"></span>
                            <small>Event Schedule for</small>
                            <strong>{{ $relName }}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. FAQ (08)                                                 -->
        <!-- ============================================================ -->
        <section id="faq" class="va-sec va-pg va-p-sienna va-sec-tint" style="scroll-margin-top: 4rem;">
            <div class="va-wrap va-faq-grid">
                <div class="va-faq-head">
                    <div data-reveal>{!! $vaChip('sienna', '08') !!}</div>
                    <span class="va-tag" data-reveal style="--reveal-delay: 0.05s;">Questions</span>
                    <h2 class="va-d va-h2" data-reveal style="--reveal-delay: 0.1s;">
                        Asked <span class="va-wet">in the studio</span>.
                    </h2>
                </div>

                <div class="va-qa" data-reveal>
                    @foreach ($faqs as $faq)
                        <details name="faq">
                            <summary>
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
        <!-- 12. Finale: a clean sheet, and a line to sign                -->
        <!-- ============================================================ -->
        <section id="claim" class="va-sec va-finale va-pg va-p-red" style="scroll-margin-top: 4rem;">
            <div class="va-washes" aria-hidden="true">
                <i class="va-w va-pg va-p-ochre" style="--i: 0; width: 30rem; height: 22rem; left: -8rem; top: 1rem;"></i>
                <i class="va-w va-pg va-p-sienna" style="--i: 1; width: 22rem; height: 17rem; left: 14%; bottom: -5rem;"></i>
                <i class="va-w va-pg va-p-viridian" style="--i: 2; width: 28rem; height: 21rem; right: -7rem; bottom: -2rem;"></i>
                <i class="va-w va-pg va-p-teal" style="--i: 3; width: 22rem; height: 16rem; right: 8%; top: -4rem;"></i>
            </div>
            <div class="va-wrap">
                <div class="va-paper va-sheet" id="va-sheet" data-reveal="panel">
                    <span class="va-reg" aria-hidden="true"></span>
                    <span class="va-reg" aria-hidden="true"></span>

                    <span class="va-tag">Free to start</span>
                    <h2 class="va-d va-h2">
                        Give the wall <span class="va-wet va-wet-u">an address</span>.
                    </h2>
                    <p class="va-sub">
                        The page, the dates, the gallery logos, the calendar sync and a free opening
                        with a capacity on it all cost nothing. Putting a price on a place is
                        {{ plan_price($proMonthly) }} a month, and none of the ticket price comes to us.
                    </p>

                    <div class="va-strip" aria-hidden="true">
                        @foreach (['red', 'ochre', 'viridian', 'teal', 'sienna', 'black'] as $stripPig)
                            <i class="va-p-{{ $stripPig }}"></i>
                        @endforeach
                    </div>
                    <div class="va-claimbox">
                        <label for="es-claim-input">Your schedule name</label>
                        <div class="va-claimrow">
                            <div dir="ltr" class="es-claim va-claim">
                                <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="va-btn">
                                Start your wall
                                {!! $vaArrow !!}
                            </a>
                        </div>
                    </div>

                    <p class="va-claim-note">No credit card required</p>
                    <span class="va-sign" aria-hidden="true"><span id="va-sign">your studio</span> &#8217;26</span>
                </div>
            </div>
        </section>

        <div class="va-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- The sheet is signed with whatever name is typed, and the finale throws paint, not
         the site's blues: six pigments, round as drops. The paint is thrown from an
         instance of the cannon made here, not from the library's own: that one draws
         through a worker built from a blob, which the site's content security policy
         refuses without an error, so nothing was ever drawn and a dead canvas the size
         of the screen was left over the page. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('es-claim-input');
            var sign = document.getElementById('va-sign');
            if (input && sign) {
                var blank = sign.textContent;
                input.addEventListener('input', function () {
                    sign.textContent = input.value.replace(/-+$/, '').replace(/-/g, ' ') || blank;
                });
            }

            var sheet = document.getElementById('va-sheet');
            if (!sheet || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var pigments = ['#e03c31', '#c9952b', '#2f8f7a', '#1ba7b4', '#a5512b', '#231f1e'];
                    var fire = window.confetti.create(null, { resize: true });
                    [[60, 0.08], [120, 0.92]].forEach(function (shot) {
                        fire({ particleCount: 60, angle: shot[0], spread: 55, startVelocity: 48, scalar: 1.15, origin: { x: shot[1], y: 0.95 }, colors: pigments, shapes: ['circle'], disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.6 });
            io.observe(sheet);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
