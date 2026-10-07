<x-marketing-layout>
    <x-slot name="title">Actor Schedules | A Credits List That Updates Itself</x-slot>
    <x-slot name="description">Set your schedule to List and it becomes your credits: every production dated, past work kept, and new bookings arriving from the companies that cast you.</x-slot>
    <x-slot name="breadcrumbTitle">For Theater Performers</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Cousine') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Dawning of a New Day') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Theater Performers"
        description="A public schedule that doubles as a credits list: past productions stay dated and visible, and companies that cast you can put the dates on your page for you to accept."
        audience="Theater Performers"
        keywords="actor schedule, theatre credits list, performer calendar, casting booking requests, actor resume online, theatre performance dates" />
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
           For-theater-performers "The Sides" styles.

           The page is the script an actor is handed at an audition and
           carries through rehearsal: Courier on three-hole paper, the
           revision pages in their production colours, their own lines
           under the highlighter and pencil in the margin. The layout
           system is the script format itself, measured in ch so the
           columns are true: action at 61, dialogue 38 wide and 10 in,
           the character cue 22 in, a parenthetical 16 in.

           The cast of this script is the product: each feature's name
           is a character cue and its description is the speech under
           it, a plan tier is the parenthetical. YOU only speak twice,
           in the dressing room and on the last page, and those are the
           highlighted lines.

           Everything is scoped under #tp. The shared es-* reveal system
           (marketing.css, marketing-home.js) drives the entrances.
           ============================================================== */

        #tp {
            --tp-desk: #d6d2c9;
            --tp-sheet: #fdfcf8;
            --tp-under: #efede6;
            --tp-ink: #1b1b1b;
            --tp-ink-2: #3a3a37;
            --tp-ink-3: #595954;
            --tp-rule: rgba(27, 27, 27, 0.24);
            --tp-hl: #fff04d;
            --tp-hl-blend: multiply;
            --tp-pencil: #5d6770;
            --tp-red: #a82c20;
            --tp-hole: #9b968b;
            --tp-edge: rgba(27, 27, 27, 0.11);
            --tp-drop: rgba(27, 27, 27, 0.42);
            --tp-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.07 0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
            --tp-mono: 'Cousine', 'Courier New', Courier, 'Nimbus Mono PS', 'Liberation Mono', monospace;
            --tp-hand: 'Dawning of a New Day', 'Bradley Hand', 'Segoe Script', cursive;
            --tp-dia-in: 3ch;
            --tp-dia-w: 30ch;
            --tp-cue-in: 8ch;
            --tp-par-in: 5.5ch;
            --tp-hx: 1.2rem;
            --tp-hr: 0.3rem;
            position: relative;
            background-color: var(--tp-desk);
            background-image: radial-gradient(110% 50rem at 50% -8rem, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0) 62%);
            color: var(--tp-ink);
            font-family: var(--tp-mono);
            font-size: 0.9375rem;
            line-height: 1.5;
        }
        .dark #tp {
            --tp-desk: #121315;
            --tp-sheet: #1e2023;
            --tp-under: #17191b;
            --tp-ink: #e6e3da;
            --tp-ink-2: #c8c5bc;
            --tp-ink-3: #a5a299;
            --tp-rule: rgba(230, 227, 218, 0.26);
            --tp-hl: rgba(255, 240, 77, 0.28);
            --tp-hl-blend: normal;
            --tp-pencil: #98a2ab;
            --tp-red: #ff9485;
            --tp-hole: #060607;
            --tp-edge: rgba(230, 227, 218, 0.1);
            --tp-drop: rgba(0, 0, 0, 0.75);
            --tp-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 0.06 0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
            /* The blue running lights backstage, not a desk lamp. */
            background-image:
                radial-gradient(64rem 44rem at 82% 2rem, rgba(104, 146, 184, 0.16), rgba(104, 146, 184, 0) 70%),
                radial-gradient(48rem 40rem at 6% 70rem, rgba(104, 146, 184, 0.08), rgba(104, 146, 184, 0) 70%);
        }
        @media (min-width: 720px) {
            #tp {
                --tp-dia-in: 10ch;
                --tp-dia-w: 38ch;
                --tp-cue-in: 22ch;
                --tp-par-in: 16ch;
                --tp-hx: 2.2rem;
                --tp-hr: 0.42rem;
                font-size: 1rem;
            }
        }

        /* The revision colours a production really issues, in order: white, blue,
           yellow, green, goldenrod, buff. Each sheet brings the highlighter that
           still shows on it. */
        #tp .tp-rev-blue { --tp-sheet: #d5e5f4; --tp-under: #c5d7ea; }
        #tp .tp-rev-yellow { --tp-sheet: #fff5b8; --tp-under: #f2e7a3; --tp-hl: #ffb84a; }
        #tp .tp-rev-green { --tp-sheet: #d7ebd2; --tp-under: #c6dcc0; }
        #tp .tp-rev-gold { --tp-sheet: #f4d680; --tp-under: #e5c66f; --tp-hl: #a8ee88; }
        #tp .tp-rev-buff { --tp-sheet: #efe1c4; --tp-under: #e1d2b2; }
        .dark #tp .tp-rev-blue { --tp-sheet: #1a2735; --tp-under: #15202c; }
        .dark #tp .tp-rev-yellow { --tp-sheet: #2b2814; --tp-under: #24210f; --tp-hl: rgba(255, 184, 74, 0.3); }
        .dark #tp .tp-rev-green { --tp-sheet: #1b291e; --tp-under: #162218; }
        .dark #tp .tp-rev-gold { --tp-sheet: #2f250e; --tp-under: #281f0a; --tp-hl: rgba(168, 238, 136, 0.26); }
        .dark #tp .tp-rev-buff { --tp-sheet: #2a2419; --tp-under: #231d14; }

        /* The bar above is the same table top. */
        body > header.sticky { background-color: rgba(214, 210, 201, 0.9); border-bottom-color: rgba(27, 27, 27, 0.14); }
        .dark body > header.sticky { background-color: rgba(18, 19, 21, 0.9); border-bottom-color: rgba(230, 227, 218, 0.12); }

        #tp ::selection { background: #fff04d; color: #1b1b1b; }
        #tp a:focus-visible,
        #tp summary:focus-visible,
        #tp input:focus-visible { outline: 3px solid var(--tp-ink); outline-offset: 3px; }

        .tp-wrap { width: min(100% - 2rem, 76rem); margin-inline: auto; }

        /* ---------------------------------------------------------------
           The sheet: three holes, a stack beneath it, a running header
           --------------------------------------------------------------- */
        .tp-leaf {
            position: relative;
            min-width: 0;
            padding: 0 1.15rem 2.5rem 2.75rem;
            background-color: var(--tp-sheet);
            background-image: var(--tp-grain);
            color: var(--tp-ink);
            box-shadow:
                0 0 0 1px var(--tp-edge),
                0.28rem 0.32rem 0 -1px var(--tp-under), 0.28rem 0.32rem 0 0 var(--tp-edge),
                0.56rem 0.64rem 0 -1px var(--tp-under), 0.56rem 0.64rem 0 0 var(--tp-edge),
                0 1.9rem 2.6rem -1.6rem var(--tp-drop);
        }
        .tp-leaf::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: calc(var(--tp-hx) * 2);
            pointer-events: none;
            background:
                radial-gradient(circle at var(--tp-hx) 5rem, var(--tp-hole) 0 var(--tp-hr), rgba(0, 0, 0, 0.3) calc(var(--tp-hr) + 0.02rem) calc(var(--tp-hr) + 0.07rem), rgba(0, 0, 0, 0) calc(var(--tp-hr) + 0.12rem)),
                radial-gradient(circle at var(--tp-hx) 50%, var(--tp-hole) 0 var(--tp-hr), rgba(0, 0, 0, 0.3) calc(var(--tp-hr) + 0.02rem) calc(var(--tp-hr) + 0.07rem), rgba(0, 0, 0, 0) calc(var(--tp-hr) + 0.12rem)),
                radial-gradient(circle at var(--tp-hx) calc(100% - 5rem), var(--tp-hole) 0 var(--tp-hr), rgba(0, 0, 0, 0.3) calc(var(--tp-hr) + 0.02rem) calc(var(--tp-hr) + 0.07rem), rgba(0, 0, 0, 0) calc(var(--tp-hr) + 0.12rem));
        }
        @media (min-width: 720px) {
            .tp-leaf { padding: 0 3.25rem 3rem 6rem; }
        }
        .tp-leaf-c { max-width: 50rem; margin-inline: auto; }
        @supports (animation-timeline: view()) {
            html.es-anim #tp .tp-scene .tp-leaf,
            html.es-anim #tp .tp-final .tp-leaf { animation: tp-settle linear both; animation-timeline: view(); animation-range: entry 0% entry 70%; }
            html.es-anim #tp .tp-desk-flip .tp-leaf { animation-name: tp-settle-b; }
        }
        @keyframes tp-settle { from { rotate: 1.7deg; } to { rotate: 0deg; } }
        @keyframes tp-settle-b { from { rotate: -1.7deg; } to { rotate: 0deg; } }
        /* The turned-down corner shows the next sheet in the stack. */
        .tp-ear {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 2.3rem;
            aspect-ratio: 1;
            background: linear-gradient(315deg, var(--tp-under) 0 50%, var(--tp-sheet) 50%);
            box-shadow: -1px -1px 0 var(--tp-edge), -0.25rem -0.25rem 0.4rem -0.2rem var(--tp-drop);
        }

        /* The running header follows you down the page, as it heads every page of a script. */
        .tp-slug {
            position: sticky;
            top: 4rem;
            z-index: 4;
            container-type: scroll-state;
            margin-bottom: 1.5rem;
            background: var(--tp-sheet) var(--tp-grain);
        }
        .tp-slug-in {
            display: flex;
            align-items: baseline;
            gap: 1.5ch;
            padding: 1.05rem 0 0.55rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0);
            font-size: 0.8125rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            color: var(--tp-ink-3);
            transition: border-color 0.25s ease;
        }
        .tp-kicker { color: var(--tp-ink); font-weight: 700; }
        .tp-slug-rev { display: none; }
        @media (min-width: 720px) { .tp-slug-rev { display: block; } }
        .tp-pg { margin-inline-start: auto; color: var(--tp-ink); }
        #tp { counter-reset: tp-pg; }
        .tp-leaf { counter-increment: tp-pg; }
        .tp-pg::after { content: counter(tp-pg) "."; }
        @supports (container-type: scroll-state) {
            @container scroll-state(stuck: top) {
                .tp-slug-in { border-bottom-color: var(--tp-rule); }
            }
        }
        /* Safari and Firefox cannot tell when the header is stuck, and without its rule the
           lines passing under it are cut off in mid-air. There the rule is simply always drawn. */
        @supports not (container-type: scroll-state) {
            .tp-slug-in { border-bottom-color: var(--tp-rule); }
        }

        /* ---------------------------------------------------------------
           The format. One size, one face; the indents do the talking.
           --------------------------------------------------------------- */
        .tp-sline {
            position: relative;
            display: flex;
            justify-content: space-between;
            gap: 2ch;
            font-weight: 700;
            text-transform: uppercase;
        }
        .tp-sline b { font-weight: 700; }
        #tp .tp-dark .tp-sline b:first-child { position: static; }
        @media (min-width: 720px) {
            .tp-sline b:first-child { position: absolute; right: calc(100% + 1.5ch); }
        }
        .tp-h2 {
            margin-top: 1.5rem;
            max-width: 26ch;
            font-size: clamp(1.3rem, 1rem + 1.7vw, 2.1rem);
            line-height: 1.24;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
            text-decoration-thickness: 0.07em;
            text-underline-offset: 0.17em;
            text-wrap: balance;
        }
        .tp-action { margin-top: 1.5rem; max-width: 61ch; }
        .tp-ex { position: relative; margin-top: 1.5rem; }
        .tp-cue { display: block; margin-inline-start: var(--tp-cue-in); font-weight: 700; text-transform: uppercase; }
        .tp-paren { margin-inline-start: var(--tp-par-in); }
        .tp-paren::before { content: "("; }
        .tp-paren::after { content: ")"; }
        .tp-dia { margin-inline-start: var(--tp-dia-in); max-width: var(--tp-dia-w); }
        .tp-trans { margin-top: 1.5rem; text-align: right; text-transform: uppercase; }
        .tp-cont { margin-top: 2.25rem; text-align: right; color: var(--tp-ink-3); }
        .tp-more { margin-top: 1.5rem; margin-inline-start: var(--tp-cue-in); color: var(--tp-ink-3); }
        /* A revised line carries an asterisk in the right margin. */
        .tp-chg::after {
            content: "*";
            position: absolute;
            top: 0;
            right: -0.85rem;
            font-weight: 700;
        }
        @media (min-width: 720px) { .tp-chg::after { right: -1.9rem; } }
        .tp-note { display: flex; gap: 1.5ch; align-items: baseline; margin-top: 2rem; max-width: 61ch; font-size: 0.875em; color: var(--tp-ink-2); }
        .tp-tag { flex: none; padding: 0.05em 0.8ch; border: 1.5px solid currentColor; font-weight: 700; text-transform: uppercase; color: var(--tp-ink); }

        #tp .tp-leaf a[class*="text-blue-600"],
        #tp .tp-insert a[class*="text-blue-600"] {
            display: inline;
            color: var(--tp-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-thickness: 0.08em;
            text-underline-offset: 0.18em;
        }
        #tp .tp-leaf a[class*="text-blue-600"]:hover,
        #tp .tp-insert a[class*="text-blue-600"]:hover { background: #fff04d; color: #1b1b1b; }

        /* The highlighter: one pass of a chisel tip, a little short at each end. */
        .tp-hl {
            padding-inline: 0.3ch;
            margin-inline: -0.3ch;
            background-image: linear-gradient(100deg, rgba(0, 0, 0, 0) 0.7%, var(--tp-hl) 2.4%, var(--tp-hl) 97.4%, rgba(0, 0, 0, 0) 99.2%);
            background-repeat: no-repeat;
            background-size: 100% 82%;
            background-position: 0 58%;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
            mix-blend-mode: var(--tp-hl-blend);
            transition: background-size 0.9s cubic-bezier(0.3, 0.7, 0.2, 1) 0.5s;
        }
        html.es-anim #tp [data-reveal]:not(.is-revealed) .tp-hl { background-size: 0% 82%; }

        /* Pencil: blocking, breaths and reminders. Never read aloud. */
        .tp-pencil {
            display: none;
            position: absolute;
            font-family: var(--tp-hand);
            font-size: 1.6rem;
            line-height: 1;
            white-space: nowrap;
            color: var(--tp-pencil);
            rotate: var(--r, -5deg);
            pointer-events: none;
        }
        .tp-pencil u { text-decoration-thickness: 1.5px; text-underline-offset: 0.2em; }
        @media (min-width: 1080px) { .tp-pencil { display: block; } }
        .tp-ex .tp-pencil { left: calc(var(--tp-dia-in) + var(--tp-dia-w) + 2ch); top: 2.1rem; }
        .tp-circle {
            display: inline-block;
            margin: -0.12em -0.5ch;
            padding: 0.12em 0.5ch;
            border: 1.5px solid var(--tp-pencil);
            border-radius: 58% 42% 55% 45% / 62% 48% 58% 44%;
            rotate: -2.2deg;
        }

        /* Buttons are typed too. */
        .tp-btn {
            display: inline-flex;
            align-items: center;
            gap: 1ch;
            padding: 0.85rem 1.35rem;
            border-radius: 2px;
            background: var(--tp-ink);
            color: var(--tp-sheet);
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            transition: background-color 0.2s ease, color 0.2s ease, translate 0.2s ease;
        }
        .tp-btn:hover { background: #fff04d; color: #1b1b1b; translate: 0 -2px; }
        .tp-btn svg { width: 1.05em; height: 1.05em; flex: none; transition: translate 0.2s ease; }
        .tp-btn:hover svg { translate: 0.3ch 0; }
        .tp-go {
            display: inline-flex;
            align-items: center;
            gap: 1ch;
            padding-block: 0.3rem;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-thickness: 0.08em;
            text-underline-offset: 0.2em;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0) 0, #fff04d 0) no-repeat 0 60% / 0% 78%;
            transition: background-size 0.35s cubic-bezier(0.3, 0.7, 0.2, 1), color 0.2s ease;
        }
        .tp-go:hover { background-size: 100% 78%; color: #1b1b1b; }
        .tp-go svg { width: 1em; height: 1em; flex: none; }

        /* ---------------------------------------------------------------
           Section tabs: index dividers down the edge of the script
           --------------------------------------------------------------- */
        /* The nav is a box the size of the page that clips the tabs, so they stay fixed to the
           screen and still end where the script does instead of riding over the site footer. */
        .tp-tabs { display: none; }
        @media (min-width: 1360px) {
            #tp .tp-tabs { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .tp-tabs ol { position: fixed; right: 0; top: 50%; translate: 0 -50%; display: grid; gap: 0.3rem; justify-items: end; pointer-events: auto; }
            .tp-tabs a {
                display: flex;
                gap: 1.2ch;
                max-width: 2.3rem;
                overflow: hidden;
                padding: 0.4rem 0.9rem 0.34rem 0.8rem;
                border-radius: 0.35rem 0 0 0.35rem;
                background: var(--c, #fdfcf8);
                color: #1b1b1b;
                font-size: 0.6875rem;
                font-weight: 700;
                letter-spacing: 0.04em;
                line-height: 1.3;
                text-transform: uppercase;
                white-space: nowrap;
                box-shadow: -1px 1px 2px rgba(0, 0, 0, 0.28);
                opacity: 0.86;
                transition: max-width 0.35s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.2s ease, padding 0.25s ease;
            }
            .tp-tabs a b { flex: none; width: 1.2ch; text-align: center; }
            .tp-tabs a span { opacity: 0; transition: opacity 0.2s ease; }
            .tp-tabs a:hover span,
            .tp-tabs a:focus-visible span { opacity: 1; }
            .tp-tabs a:hover,
            .tp-tabs a:focus-visible { max-width: 15rem; opacity: 1; }
            .tp-tabs a.is-active { opacity: 1; padding-inline-end: 1.6rem; max-width: 3rem; box-shadow: -2px 1px 3px rgba(0, 0, 0, 0.4); }
            .tp-tabs a.is-active:hover,
            .tp-tabs a.is-active:focus-visible { max-width: 15rem; }
            .tp-tabs li:nth-child(6n + 2) a { --c: #d5e5f4; }
            .tp-tabs li:nth-child(6n + 3) a { --c: #fff5b8; }
            .tp-tabs li:nth-child(6n + 4) a { --c: #d7ebd2; }
            .tp-tabs li:nth-child(6n + 5) a { --c: #f4d680; }
            .tp-tabs li:nth-child(6n + 6) a { --c: #efe1c4; }
        }

        /* ---------------------------------------------------------------
           Title page, and the resume stapled to the headshot
           --------------------------------------------------------------- */
        .tp-hero { overflow: clip; padding-block: clamp(2.25rem, 5vw, 4.25rem) clamp(3.5rem, 7vw, 6rem); }
        .tp-hero-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 4rem; align-items: center; }
        @media (min-width: 1080px) {
            .tp-hero-grid { grid-template-columns: minmax(0, 36rem) minmax(0, 31.5rem); justify-content: center; gap: 3.25rem; }
        }
        .tp-stack { position: relative; min-width: 0; }
        .tp-fan {
            position: absolute;
            inset: 0;
            background: var(--tp-sheet) var(--tp-grain);
            box-shadow: 0 0 0 1px var(--tp-edge), 0 1.2rem 1.8rem -1.3rem var(--tp-drop);
            rotate: var(--r);
            translate: var(--x, 0) var(--y, 0);
        }
        @media (max-width: 719px) { .tp-fan { rotate: calc(var(--r) * 0.5); } }
        .tp-title {
            display: flex;
            flex-direction: column;
            min-height: min(46rem, 132vw);
            padding-bottom: 1.6rem;
            rotate: -1.1deg;
        }
        @media (max-width: 719px) { .tp-title { rotate: -0.6deg; } }
        .tp-title::before { display: none; }
        .tp-brad {
            position: absolute;
            left: calc(var(--tp-hx) - 0.62rem);
            top: var(--y);
            width: 1.24rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle at 34% 30%, #fbeeb9 0 6%, #dbb354 34%, #a67a26 70%, #6f4d12 100%);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.5), inset 0 0 0 1px rgba(80, 52, 8, 0.55);
        }
        .tp-brad::after { content: ""; position: absolute; inset: 46% 22% auto 22%; height: 1.5px; background: rgba(70, 46, 8, 0.6); rotate: -24deg; }
        @media (max-width: 719px) { .tp-brad { width: 0.9rem; left: calc(var(--tp-hx) - 0.45rem); } }
        .tp-title-in { container-type: inline-size; display: flex; flex-direction: column; flex: 1; text-align: center; }
        .tp-eyebrow {
            display: block;
            padding-top: 1.15rem;
            margin-bottom: clamp(3.25rem, 21cqi, 7.5rem);
            font-family: var(--tp-mono);
            font-size: 0.8125rem;
            letter-spacing: 0.03em;
            text-align: start;
            text-transform: uppercase;
            color: var(--tp-ink-3);
        }
        .tp-h1 {
            font-size: clamp(1.7rem, 10.5cqi, 4.2rem);
            line-height: 1.16;
            font-weight: 700;
            text-transform: uppercase;
        }
        .tp-line { position: relative; display: inline-block; white-space: nowrap; }
        .tp-type { display: inline-block; text-decoration: underline; text-decoration-thickness: 0.06em; text-underline-offset: 0.14em; }
        .tp-caret { display: none; }
        /* Typed on, a character a keystroke: in a monospaced face steps() is exact. */
        html.es-anim #tp .tp-l1 .tp-type { clip-path: inset(-0.3em 100% -0.3em -0.5ch); animation: tp-type 0.9s steps(15, end) 0.35s forwards; }
        html.es-anim #tp .tp-l2 .tp-type { clip-path: inset(-0.3em 100% -0.3em -0.5ch); animation: tp-type 0.85s steps(14, end) 1.4s forwards; }
        @keyframes tp-type { to { clip-path: inset(-0.3em -1ch -0.3em -0.5ch); } }
        html.es-anim #tp .tp-caret { display: block; position: absolute; top: 0.12em; bottom: 0.1em; left: 0; width: 0.5ch; background: var(--tp-ink); opacity: 0; }
        html.es-anim #tp .tp-l1 .tp-caret { animation: tp-run 0.9s steps(15, end) 0.35s both, tp-hide 0.01s linear 1.3s forwards; }
        html.es-anim #tp .tp-l2 .tp-caret { animation: tp-run 0.85s steps(14, end) 1.4s forwards, tp-blink 1.1s steps(1, end) 2.3s 5; }
        @keyframes tp-run { from { left: 0; opacity: 1; } to { left: 100%; opacity: 1; } }
        @keyframes tp-hide { to { opacity: 0; } }
        @keyframes tp-blink { 0%, 49% { opacity: 1; } 50%, 100% { opacity: 0; } }
        html.es-anim #tp .tp-h1 .tp-hl { background-size: 0% 82%; animation: tp-swipe 0.7s cubic-bezier(0.3, 0.7, 0.2, 1) 2.4s forwards; transition: none; }
        @keyframes tp-swipe { to { background-size: 100% 82%; } }
        .tp-by { margin-top: clamp(1.25rem, 6cqi, 2.25rem); text-transform: none; color: var(--tp-ink-3); }
        .tp-logline { margin: clamp(1.75rem, 9cqi, 3.5rem) auto 0; max-width: 46ch; text-align: center; color: var(--tp-ink-2); }
        .tp-cta { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 1rem 2.5ch; margin-top: 2rem; }
        .tp-title-foot { display: flex; justify-content: space-between; gap: 2ch; margin-top: auto; padding-top: 2.5rem; font-size: 0.8125rem; text-transform: uppercase; color: var(--tp-ink-3); }
        .tp-h1-wrap { position: relative; }
        .tp-title .tp-p1 { top: 3.3rem; right: 1.6rem; --r: 5deg; }
        .tp-title .tp-p2 { right: -2.1rem; bottom: -2.1rem; --r: -6deg; }

        .tp-cv { position: relative; justify-self: center; width: min(100%, 31.5rem); }
        /* The eight-by-ten behind it: only its edges show. */
        .tp-cv-photo {
            position: absolute;
            top: -1.5rem;
            left: -1.25rem;
            width: 64%;
            aspect-ratio: 8 / 10;
            border: 0.5rem solid #f6f4ee;
            background:
                linear-gradient(118deg, rgba(255, 255, 255, 0) 30%, rgba(255, 255, 255, 0.16) 42%, rgba(255, 255, 255, 0) 55%),
                linear-gradient(160deg, #46474d, #1d1e21 60%, #2c2d31);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.18), 0 1.4rem 2rem -1.2rem var(--tp-drop);
            rotate: -4deg;
        }
        .dark .tp-cv-photo { border-color: #cfccc3; }
        .tp-cv-sheet {
            position: relative;
            padding: 1.9rem 1.35rem 1.3rem;
            background: var(--tp-sheet) var(--tp-grain);
            box-shadow: 0 0 0 1px var(--tp-edge), 0 1.6rem 2.4rem -1.4rem var(--tp-drop);
            rotate: 1.3deg;
        }
        @media (min-width: 720px) { .tp-cv-sheet { padding: 2.1rem 1.9rem 1.5rem; } }
        .tp-staple {
            position: absolute;
            top: 0.95rem;
            left: 0.7rem;
            width: 1.5rem;
            height: 3px;
            border-radius: 1px;
            background: linear-gradient(#e2e4e6, #8d9298);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.4);
            rotate: -40deg;
        }
        .tp-cv-name { font-size: 1.3rem; font-weight: 700; letter-spacing: 0.06em; text-align: center; text-transform: uppercase; }
        .tp-cv-sub { text-align: center; font-size: 0.875rem; color: var(--tp-ink-2); }
        .tp-cv-url { margin-bottom: 1.25rem; text-align: center; font-size: 0.875rem; color: var(--tp-ink-3); }
        .tp-cv table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
        @media (min-width: 720px) { .tp-cv table { font-size: 0.8125rem; } }
        .tp-cv caption { padding-bottom: 0.3rem; border-bottom: 2px solid var(--tp-ink); font-weight: 700; letter-spacing: 0.08em; text-align: start; text-transform: uppercase; }
        .tp-cv th { padding: 0.55rem 1.5ch 0.3rem 0; font-weight: 400; text-align: start; text-decoration: underline; text-transform: uppercase; color: var(--tp-ink-3); }
        .tp-cv td { padding: 0.42rem 1.5ch 0.42rem 0; vertical-align: top; color: var(--tp-ink-2); }
        .tp-cv td:first-child { font-weight: 700; color: var(--tp-ink); }
        .tp-cv th:last-child,
        .tp-cv td:last-child { padding-inline-end: 0; text-align: end; }
        .tp-cv tbody tr + tr td { border-top: 1px dotted var(--tp-rule); }
        .tp-cv-role-sm { display: block; font-weight: 400; color: var(--tp-ink-2); }
        .tp-cv-live { display: inline-block; margin-inline-start: 0.6ch; padding-inline: 0.5ch; background: #fff04d; color: #1b1b1b; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        @media (max-width: 559px) {
            .tp-cv th:nth-child(2),
            .tp-cv td:nth-child(2) { display: none; }
        }
        @media (min-width: 560px) { .tp-cv-role-sm { display: none; } }
        /* The row a company added arrives after the rest, still wet. */
        html.es-anim #tp .tp-cv-new td { animation: tp-row-in 1.9s ease 2.5s both; }
        @keyframes tp-row-in {
            0% { opacity: 0; background-color: rgba(255, 240, 77, 0.5); }
            30% { opacity: 1; background-color: rgba(255, 240, 77, 0.5); }
            100% { opacity: 1; background-color: rgba(255, 240, 77, 0); }
        }
        .tp-cv-foot { position: relative; margin-top: 1rem; padding-inline-start: 2ch; font-size: 0.8125rem; color: var(--tp-ink-2); }
        .tp-cv-foot::before { content: "*"; position: absolute; left: 0; font-weight: 700; }

        /* ---------------------------------------------------------------
           The desk: a page, and whatever is clipped to it
           --------------------------------------------------------------- */
        .tp-scene { padding-block: clamp(1.5rem, 3.4vw, 3rem); }
        .tp-desk { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem; align-items: start; }
        .tp-aside { position: relative; min-width: 0; display: grid; justify-items: center; }
        @media (min-width: 1080px) {
            .tp-desk { grid-template-columns: minmax(0, 50rem) minmax(0, 1fr); gap: 0; }
            .tp-desk-flip { grid-template-columns: minmax(0, 1fr) minmax(0, 50rem); }
            .tp-desk-flip .tp-leaf { order: 2; }
            .tp-aside { z-index: 5; margin-top: 10.5rem; margin-inline-start: -2.4rem; justify-items: start; }
            .tp-desk-flip .tp-aside { margin-inline: 0 -2.4rem; justify-items: end; }
        }
        .tp-insert {
            position: relative;
            width: min(100%, 24rem);
            padding: 1.6rem 1.35rem 1.35rem;
            background: var(--tp-sheet) var(--tp-grain);
            color: var(--tp-ink);
            box-shadow: 0 0 0 1px var(--tp-edge), 0 1.5rem 2.2rem -1.4rem var(--tp-drop);
            rotate: var(--r, 1.6deg);
        }
        .tp-insert h3 { font-weight: 700; text-transform: uppercase; text-decoration: underline; text-underline-offset: 0.17em; }
        .tp-insert-pg { display: flex; justify-content: space-between; gap: 2ch; margin-bottom: 1.1rem; font-size: 0.8125rem; text-transform: uppercase; color: var(--tp-ink-3); }
        .tp-insert p { margin-top: 0.9rem; font-size: 0.9em; color: var(--tp-ink-2); }
        /* A paper clip: two wire loops, the long one in front. */
        .tp-clip {
            position: absolute;
            top: -1.15rem;
            left: 1.5rem;
            width: 0.95rem;
            height: 3rem;
            border: 2px solid #9aa0a7;
            border-radius: 0.55rem;
            rotate: var(--cr, 7deg);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.25);
        }
        .tp-clip::after { content: ""; position: absolute; inset: 0.42rem 0.1rem -0.12rem 0.1rem; border: 2px solid #c3c8ce; border-top-color: rgba(0, 0, 0, 0); border-radius: 0 0 0.4rem 0.4rem; }
        .tp-desk-flip .tp-clip { left: auto; right: 1.5rem; --cr: -8deg; }

        /* The phone on the table, showing the page the script is about. */
        .tp-phone {
            position: relative;
            width: min(100%, 19rem);
            padding: 0.6rem;
            border-radius: 2.1rem;
            background: #101113;
            box-shadow: inset 0 0 0 1px #34363b, 0 0 0 1px #000, 0 1.9rem 2.6rem -1.5rem var(--tp-drop);
            rotate: 3deg;
        }
        .tp-phone::before { content: ""; position: absolute; top: 1.05rem; left: 50%; width: 4.4rem; height: 0.9rem; border-radius: 1rem; background: #101113; translate: -50% 0; z-index: 1; }
        .tp-phone-in {
            padding: 2.6rem 0.95rem 1.2rem;
            border-radius: 1.55rem;
            background: #ffffff;
            color: #17181a;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            font-size: 0.8125rem;
            line-height: 1.35;
        }
        .dark .tp-phone-in { background: #191a1d; color: #ecebe6; }
        .tp-phone-top { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.9rem; }
        .tp-phone h3 { font-size: 0.95rem; font-weight: 700; }
        .tp-phone-chip { padding: 0.15rem 0.55rem; border-radius: 1rem; background: #eceae4; font-size: 0.6875rem; font-weight: 600; white-space: nowrap; }
        .dark .tp-phone-chip { background: #2b2d31; }
        .tp-phone-row { padding: 0.65rem 0.75rem; border-radius: 0.6rem; background: #f4f3ef; }
        .dark .tp-phone-row { background: #232529; }
        .tp-phone-row + .tp-phone-row { margin-top: 0.45rem; }
        .tp-phone-row div { display: flex; justify-content: space-between; gap: 0.6rem; }
        .tp-phone-row p:first-child { font-weight: 700; }
        .tp-phone-row > p { margin-top: 0.1rem; color: #55575c; font-size: 0.75rem; }
        .dark .tp-phone-row > p { color: #a9aaad; }
        .tp-phone-when { color: #55575c; font-size: 0.75rem; white-space: nowrap; }
        .dark .tp-phone-when { color: #a9aaad; }
        .tp-phone-now { background: #fff7a8; }
        .dark .tp-phone-now { background: #3a3610; }
        .tp-phone-div { display: flex; align-items: center; gap: 0.6rem; margin: 1rem 0 0.6rem; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #55575c; }
        .dark .tp-phone-div { color: #a9aaad; }
        .tp-phone-div i { flex: 1; height: 1px; background: currentColor; opacity: 0.35; }

        /* The price card from the box office window. */
        .tp-box-top { display: flex; align-items: baseline; justify-content: space-between; gap: 1.5ch; }
        .tp-box-rows { margin-top: 1.1rem; border-top: 2px solid var(--tp-ink); }
        .tp-box-row { display: grid; grid-template-columns: minmax(0, 1fr) auto 3ch 4ch; gap: 1.5ch; align-items: baseline; padding-block: 0.5rem; border-bottom: 1px dotted var(--tp-rule); font-size: 0.875em; }
        .tp-box-row span:first-child { font-weight: 700; }
        .tp-box-row span:nth-child(2) { font-size: 0.86em; color: var(--tp-ink-3); }
        .tp-box-row span:nth-child(3),
        .tp-box-row span:nth-child(4) { text-align: end; }
        #tp .tp-insert .tp-box-fine { font-size: 0.8em; }

        /* The programme, open at the biographies. Printed, so not Courier. */
        .tp-prog {
            width: min(100%, 22rem);
            padding: 1.6rem 1.5rem 1.4rem;
            background-color: var(--tp-sheet);
            background-image: linear-gradient(90deg, rgba(0, 0, 0, 0) 49.2%, rgba(0, 0, 0, 0.09) 50%, rgba(0, 0, 0, 0) 50.8%);
            color: var(--tp-ink);
            box-shadow: 0 0 0 1px var(--tp-edge), 0 1.5rem 2.2rem -1.4rem var(--tp-drop);
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 0.875rem;
            line-height: 1.5;
            rotate: -2.2deg;
        }
        .tp-prog-head { padding-bottom: 0.5rem; border-bottom: 3px double var(--tp-ink); font-size: 0.75rem; letter-spacing: 0.22em; text-align: center; text-transform: uppercase; }
        .tp-prog-name { margin-top: 0.9rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
        .tp-prog-name i { font-weight: 400; letter-spacing: 0; text-transform: none; }
        .tp-prog p + p { margin-top: 0.5rem; }
        .tp-prog-url { font-style: italic; }

        /* Two speaking at once: the format's own two-column layout. */
        .tp-dual { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0 4ch; }
        @media (min-width: 720px) {
            .tp-dual { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .tp-dual .tp-cue { margin-inline-start: 6ch; }
            .tp-dual .tp-paren { margin-inline-start: 3ch; }
            .tp-dual .tp-dia { margin-inline-start: 0; max-width: 31ch; }
        }

        /* ---------------------------------------------------------------
           The callboard: who it is for, as a casting breakdown
           --------------------------------------------------------------- */
        .tp-board {
            position: relative;
            margin-block: clamp(2rem, 4vw, 3.5rem);
            padding-block: clamp(3rem, 6vw, 5rem) clamp(3.5rem, 7vw, 5.5rem);
            border-block: 0.75rem solid #6a4a2c;
            background-color: #c7a57a;
            background-image:
                radial-gradient(circle at 22% 30%, rgba(86, 58, 28, 0.42) 0 1px, rgba(86, 58, 28, 0) 1.6px),
                radial-gradient(circle at 70% 62%, rgba(255, 238, 208, 0.42) 0 1px, rgba(255, 238, 208, 0) 1.6px),
                radial-gradient(circle at 44% 82%, rgba(70, 46, 20, 0.3) 0 1.6px, rgba(70, 46, 20, 0) 2.2px),
                radial-gradient(circle at 86% 14%, rgba(255, 244, 222, 0.3) 0 1.4px, rgba(255, 244, 222, 0) 2px);
            background-size: 23px 19px, 17px 29px, 31px 37px, 41px 23px;
            box-shadow: inset 0 0.9rem 1.1rem -0.8rem rgba(0, 0, 0, 0.45), inset 0 -0.9rem 1.1rem -0.8rem rgba(0, 0, 0, 0.35);
        }
        .dark .tp-board {
            border-block-color: #20160c;
            background-color: #3a2c1d;
            background-image:
                radial-gradient(circle at 22% 30%, rgba(0, 0, 0, 0.4) 0 1px, rgba(0, 0, 0, 0) 1.6px),
                radial-gradient(circle at 70% 62%, rgba(214, 178, 128, 0.2) 0 1px, rgba(214, 178, 128, 0) 1.6px),
                radial-gradient(circle at 44% 82%, rgba(0, 0, 0, 0.32) 0 1.6px, rgba(0, 0, 0, 0) 2.2px),
                radial-gradient(circle at 86% 14%, rgba(214, 178, 128, 0.16) 0 1.4px, rgba(214, 178, 128, 0) 2px);
        }
        .tp-pinned { position: relative; background: var(--tp-sheet) var(--tp-grain); color: var(--tp-ink); box-shadow: 0 0 0 1px var(--tp-edge), 0 0.9rem 1.3rem -0.7rem rgba(0, 0, 0, 0.6); rotate: var(--r, 0deg); }
        .tp-pinned::before {
            content: "";
            position: absolute;
            top: -0.5rem;
            left: var(--px, 50%);
            width: 1.1rem;
            aspect-ratio: 1;
            border-radius: 50%;
            translate: -50% 0;
            background: radial-gradient(circle at 34% 30%, #ffffff 0 9%, var(--pin, #c8402f) 34%, color-mix(in srgb, var(--pin, #c8402f) 55%, #000000) 100%);
            box-shadow: 0.12rem 0.4rem 0.4rem -0.1rem rgba(0, 0, 0, 0.55);
        }
        .tp-notice { width: min(100%, 38rem); margin-inline: auto; padding: 2.1rem 1.5rem 1.75rem; }
        @media (min-width: 720px) { .tp-notice { padding: 2.25rem 2.5rem 2rem; } }
        .tp-notice .tp-h2 { max-width: none; margin-top: 0.6rem; }
        .tp-notice-kicker { margin-top: 1.1rem; font-size: 0.8125rem; letter-spacing: 0.03em; text-transform: uppercase; color: var(--tp-ink-3); }
        .tp-cards { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.1rem 1.75rem; margin-top: 3rem; }
        @media (min-width: 720px) { .tp-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1080px) { .tp-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2.6rem 2.25rem; } }
        .tp-card { display: flex; flex-direction: column; padding: 1.75rem 1.4rem 1.4rem; transition: rotate 0.35s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.35s cubic-bezier(0.34, 1.4, 0.64, 1); }
        .tp-card:hover { rotate: 0deg; translate: 0 -0.3rem; }
        .tp-card h3 { font-weight: 700; text-transform: uppercase; }
        .tp-card h3::before { content: "["; }
        .tp-card h3::after { content: "]"; }
        .tp-card p { margin-top: 0.75rem; color: var(--tp-ink-2); }
        .tp-card a { align-self: flex-start; margin-top: auto; padding-top: 1.1rem; font-weight: 700; text-decoration: underline; text-underline-offset: 0.2em; }
        .tp-card a:hover span { background: #fff04d; color: #1b1b1b; }

        /* ---------------------------------------------------------------
           Blackout: three cues, found by glow tape. Dark in both modes.
           --------------------------------------------------------------- */
        .tp-dark {
            position: relative;
            overflow: clip;
            margin-block: clamp(2rem, 4vw, 3.5rem);
            padding-block: clamp(4rem, 8vw, 6.5rem);
            background-color: #09090a;
            background-image: radial-gradient(34rem 24rem at 86% 58%, rgba(255, 190, 110, 0.13), rgba(255, 190, 110, 0) 70%);
            color: #ece9e0;
        }
        .tp-dark .tp-sline,
        .tp-dark .tp-h2 { color: #ece9e0; }
        .tp-dark-kicker { margin-top: 0.4rem; font-size: 0.8125rem; letter-spacing: 0.03em; text-transform: uppercase; color: #b9b6ad; }
        #tp .tp-dark .tp-hl { background-image: linear-gradient(100deg, rgba(0, 0, 0, 0) 0.7%, rgba(215, 255, 107, 0.26) 2.4%, rgba(215, 255, 107, 0.26) 97.4%, rgba(0, 0, 0, 0) 99.2%); mix-blend-mode: normal; }
        .tp-dark .tp-trans { color: #b9b6ad; }
        .tp-cues { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem 3rem; margin-top: 3.25rem; }
        @media (min-width: 900px) { .tp-cues { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .tp-cue-mark { position: relative; width: 2.9rem; height: 2.9rem; rotate: -5deg; }
        .tp-cues > div:nth-child(2) .tp-cue-mark { rotate: 3deg; }
        .tp-cues > div:nth-child(3) .tp-cue-mark { rotate: -2deg; }
        .tp-cue-mark::before,
        .tp-cue-mark::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            border-radius: 1px;
            background: #d7ff6b;
            box-shadow: 0 0 0.5rem rgba(215, 255, 107, 0.75), 0 0 1.7rem rgba(215, 255, 107, 0.4);
        }
        .tp-cue-mark::before { width: 100%; height: 0.62rem; }
        .tp-cue-mark::after { width: 0.62rem; height: 100%; }
        .tp-cue-no { margin-top: 1.3rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #d7ff6b; }
        .tp-cues h3 { margin-top: 0.3rem; font-size: 1.2em; font-weight: 700; text-transform: uppercase; }
        .tp-cues p:last-child { margin-top: 0.7rem; max-width: 34ch; color: #cfccc3; }
        @supports (animation-timeline: view()) {
            html.es-anim #tp .tp-cue-mark { animation: tp-glow linear both; animation-timeline: view(); animation-range: entry 10% cover 42%; }
        }
        @keyframes tp-glow { from { opacity: 0.12; } to { opacity: 1; } }
        /* The ghost light: the one lamp left on a dark stage. */
        .tp-ghost { display: none; }
        @media (min-width: 1280px) {
            .tp-ghost { display: block; position: absolute; right: max(1.5rem, calc((100% - 76rem) / 2 - 3.5rem)); top: 5.5rem; bottom: 0; width: 2px; background: linear-gradient(rgba(255, 255, 255, 0) 0, #55524b 0.3rem, #2c2a26 100%); }
            .tp-ghost::before { content: ""; position: absolute; top: -0.9rem; left: 50%; width: 1.2rem; aspect-ratio: 1; border-radius: 50%; translate: -50% 0; background: radial-gradient(circle, #fff7e2 0 30%, #ffcf80 60%, rgba(255, 207, 128, 0) 72%); box-shadow: 0 0 1.4rem 0.4rem rgba(255, 200, 120, 0.5), 0 0 6rem 2rem rgba(255, 190, 110, 0.22); animation: tp-ghost 5s ease-in-out infinite; }
        }
        /* Up to 1440px the lamp stands exactly where the section tabs are, and they cover it. */
        @media (min-width: 1360px) and (max-width: 1439px) { .tp-ghost { display: none; } }
        @keyframes tp-ghost { 0%, 100% { opacity: 1; } 46% { opacity: 0.86; } 52% { opacity: 0.97; } }
        #tp .tp-dark a:focus-visible { outline-color: #d7ff6b; }

        /* ---------------------------------------------------------------
           The prop list and the cast list, side by side on the table
           --------------------------------------------------------------- */
        .tp-spread { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem; align-items: start; }
        @media (min-width: 1080px) {
            .tp-spread { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 3rem; }
            .tp-spread .tp-leaf { padding-inline: 5rem 2.25rem; }
            .tp-spread .tp-leaf:last-child { margin-top: 3.5rem; }
        }
        .tp-spread .tp-h2 { font-size: clamp(1.3rem, 1rem + 1.2vw, 1.75rem); }
        .tp-props { margin-top: 1.75rem; border-top: 2px solid var(--tp-ink); }
        .tp-prop { display: grid; grid-template-columns: 3ch minmax(0, 1fr) auto; gap: 1ch; align-items: baseline; padding: 0.95rem 0 0.9rem; border-bottom: 1px dotted var(--tp-rule); transition: background-color 0.2s ease, padding 0.25s ease; }
        .tp-prop:hover { background: #fff04d; color: #1b1b1b; padding-inline: 1ch; }
        .tp-prop strong { display: block; font-weight: 700; text-transform: uppercase; }
        .tp-prop small { display: block; margin-top: 0.2rem; font-size: 0.9em; color: var(--tp-ink-2); }
        .tp-prop:hover small { color: #1b1b1b; }
        .tp-prop svg { width: 1.1em; height: 1.1em; align-self: center; transition: translate 0.2s ease; }
        .tp-prop:hover svg { translate: 0.4ch 0; }
        .tp-castlist { margin-top: 1.75rem; }
        .tp-castrow { display: flex; align-items: baseline; gap: 1ch; padding-block: 0.7rem; transition: opacity 0.2s ease; }
        .tp-castrow span { flex: none; text-transform: uppercase; }
        .tp-castrow i { flex: 1; min-width: 2ch; height: 1em; background: radial-gradient(circle, currentColor 0 1px, rgba(0, 0, 0, 0) 1.3px) 0 96% / 1ch 1em repeat-x; opacity: 0.7; }
        .tp-castrow strong { flex: 0 1 auto; min-width: 0; font-weight: 700; text-align: end; text-transform: uppercase; }
        .tp-castrow:hover strong { background: #fff04d; color: #1b1b1b; box-shadow: 0 0 0 0.25ch #fff04d; }
        .tp-castlist:has(.tp-castrow:hover) .tp-castrow:not(:hover) { opacity: 0.5; }
        /* Padding on an inline link makes its box 24px tall and moves no line; the highlight
           on hover stays the height of the type. */
        .tp-cut a { padding-block: 0.25rem; font-weight: 700; text-decoration: underline; text-underline-offset: 0.2em; }
        .tp-cut a::before { content: "CUT TO: "; font-weight: 400; }
        .tp-cut a:hover { background: #fff04d content-box; color: #1b1b1b; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the typing changes.
           --------------------------------------------------------------- */
        #tp .tp-plans > section { background: none; }
        #tp .tp-plans h2 { color: var(--tp-ink); font-size: clamp(1.3rem, 1rem + 1.7vw, 2.1rem); font-weight: 700; letter-spacing: 0; line-height: 1.24; text-decoration: underline; text-decoration-thickness: 0.07em; text-underline-offset: 0.17em; text-transform: uppercase; }
        #tp .tp-plans h2 + p { color: var(--tp-ink-2); }
        #tp .tp-plans .grid > div { border: 0; border-radius: 0; background: var(--tp-sheet); color: var(--tp-ink); box-shadow: 0 0 0 1px var(--tp-edge), 0.28rem 0.32rem 0 -1px var(--tp-under), 0.28rem 0.32rem 0 0 var(--tp-edge), 0 1.6rem 2.2rem -1.5rem var(--tp-drop); }
        #tp .tp-plans .grid > div:nth-child(2) { --tp-sheet: #fff5b8; --tp-under: #f2e7a3; }
        #tp .tp-plans .grid > div:nth-child(3) { --tp-sheet: #d5e5f4; --tp-under: #c5d7ea; }
        .dark #tp .tp-plans .grid > div:nth-child(2) { --tp-sheet: #2b2814; --tp-under: #24210f; }
        .dark #tp .tp-plans .grid > div:nth-child(3) { --tp-sheet: #1a2735; --tp-under: #15202c; }
        #tp .tp-plans .grid > div span,
        #tp .tp-plans .grid > div p,
        #tp .tp-plans .grid > div li { color: var(--tp-ink-2); }
        #tp .tp-plans .grid > div .text-3xl { color: var(--tp-ink); font-weight: 700; }
        #tp .tp-plans .grid > div .uppercase { color: var(--tp-ink); }
        #tp .tp-plans .grid > div .rounded-full { border-radius: 0; background: #ffb84a; color: #1b1b1b; }
        #tp .tp-plans .grid > div svg { color: var(--tp-ink); }
        #tp .tp-plans a.font-medium { color: var(--tp-ink); font-weight: 700; text-decoration: underline; text-underline-offset: 0.2em; }
        #tp .tp-plans a.rounded-2xl { border-radius: 2px; background: var(--tp-ink); color: var(--tp-desk); box-shadow: none; font-weight: 700; letter-spacing: 0.02em; text-transform: uppercase; }
        #tp .tp-plans a.rounded-2xl:hover { background: #fff04d; color: #1b1b1b; }

        #tp .tp-keep > section { border-top: 1px solid var(--tp-rule); background: none; }
        #tp .tp-keep h2 { color: var(--tp-ink); font-weight: 700; text-decoration: underline; text-underline-offset: 0.17em; text-transform: uppercase; }
        #tp .tp-keep p.uppercase { color: var(--tp-ink-2); letter-spacing: 0.08em; }
        #tp .tp-keep .grid > a { border: 0; border-radius: 0; background: var(--tp-sheet); box-shadow: 0 0 0 1px var(--tp-edge), 0 1.2rem 1.8rem -1.3rem var(--tp-drop); }
        #tp .tp-keep .grid > a:hover { box-shadow: 0 0 0 1px var(--tp-edge), 0.28rem 0.32rem 0 -1px var(--tp-under), 0.28rem 0.32rem 0 0 var(--tp-edge), 0 1.5rem 2rem -1.3rem var(--tp-drop); }
        #tp .tp-keep .grid > a > span:first-child { display: none; }
        #tp .tp-keep .grid > a h3 { color: var(--tp-ink); text-transform: uppercase; }
        #tp .tp-keep .grid > a p { color: var(--tp-ink-2); }
        #tp .tp-keep .grid > a > span:last-child,
        #tp .tp-keep a.self-start { color: var(--tp-ink); font-weight: 700; text-decoration: underline; text-underline-offset: 0.2em; }

        /* ---------------------------------------------------------------
           The dressing room: your lines are the highlighted ones
           --------------------------------------------------------------- */
        .tp-qa { margin-top: 0.5rem; }
        .tp-qa details { border-bottom: 1px dotted var(--tp-rule); }
        .tp-qa summary { position: relative; display: block; padding: 1.4rem 0 1.25rem; cursor: pointer; }
        .tp-qa h3 { font-weight: 400; }
        .tp-qa-sign { position: absolute; top: 1.4rem; right: 0; padding-inline: 0.4ch; font-weight: 700; }
        .tp-qa-sign::before { content: "[+]"; }
        .tp-qa details[open] .tp-qa-sign::before { content: "[-]"; }
        .tp-qa-a { padding-bottom: 1.6rem; }
        .tp-qa-a .tp-dia { color: var(--tp-ink-2); }
        .tp-qa summary:hover .tp-qa-sign { background: #fff04d; color: #1b1b1b; }

        /* ---------------------------------------------------------------
           Last page: your line is the one you type
           --------------------------------------------------------------- */
        .tp-final { padding-block: clamp(2rem, 4vw, 3.5rem) clamp(4rem, 8vw, 7rem); }
        .tp-yours { position: relative; margin-top: 1.75rem; }
        .tp-claim-wrap { display: grid; gap: 1.5rem; justify-items: start; margin-top: 0.15rem; margin-inline-start: var(--tp-dia-in); max-width: calc(var(--tp-dia-w) + 6ch); }
        #tp .tp-claim {
            display: flex;
            align-items: baseline;
            width: 100%;
            min-width: 0;
            padding: 1rem 1.2ch;
            border: 0;
            border-radius: 0.2rem 0.6rem 0.3rem 0.5rem;
            background: #fff04d;
            color: #1b1b1b;
            font-weight: 700;
            box-shadow: none;
            transition: box-shadow 0.2s ease;
        }
        .dark #tp .tp-claim { background: #f3e23c; }
        #tp .tp-claim:focus-within { border-color: transparent; box-shadow: 0 0 0 3px var(--tp-ink); }
        #tp .tp-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            color: #1b1b1b;
            font: inherit;
            text-align: end;
            box-shadow: none;
            outline: none;
        }
        #tp .tp-claim input::placeholder { color: #5a5320; opacity: 1; }
        .tp-claim span { flex: none; color: #3d3a1c; user-select: none; }
        /* A phone. The box is typed at 16px, because iOS zooms the page when a smaller input
           takes focus, and the address after it is set a size down to leave it room. The box is
           never narrower than "your-name": where the two cannot share a line the address takes
           the next one, and on the common phones the line starts at the margin so they can. */
        @media (max-width: 719px) {
            #tp .tp-claim { flex-wrap: wrap; justify-content: flex-end; }
            #tp .tp-claim input { min-width: 9.2ch; font-size: 1rem; }
            .tp-claim span { font-size: 0.875rem; }
        }
        @media (max-width: 419px) {
            .tp-claim-wrap,
            #tp .tp-nocard { margin-inline-start: 0; }
        }
        .tp-yours .tp-pencil { left: calc(var(--tp-dia-in) + var(--tp-dia-w) + 8ch); top: 2.9rem; --r: -9deg; }
        .tp-nocard { margin-top: 1.1rem; margin-inline-start: var(--tp-dia-in); color: var(--tp-ink-2); }
        .tp-end { margin-top: 2.5rem; font-weight: 700; letter-spacing: 0.1em; text-align: center; text-transform: uppercase; }

        @media (prefers-reduced-motion: reduce) {
            .tp-hl,
            .tp-btn,
            .tp-btn svg,
            .tp-go,
            .tp-card,
            .tp-prop,
            .tp-prop svg,
            .tp-castrow,
            .tp-tabs a { transition: none; }
            .tp-ghost::before { animation: none; }
        }
    </style>

    @php
        // One performer's credits. 'live' marks the production playing
        // tonight; 'new' is the row that arrives on reveal. Years are the
        // only figures the table states, and they are ordered newest first.
        $credits = [
            ['Macbeth',        'Lady Macbeth',   'Bridge Theatre',   '2026', 'live'],
            ['The Seagull',    'Nina',           'Studio Four',      '2025', ''],
            ["A Doll's House", 'Kristine Linde', 'Bridge Theatre',   '2025', ''],
            ['Constellations', 'Marianne',       'Fringe Collective','2024', ''],
            ['Twelfth Night',  'Viola',          'Parkside Players', '2024', ''],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for theater performers?',
                'a' => 'The parts you use every day are free forever: your public schedule and its list layout, past productions kept and dated, sub-schedules, booking requests from companies that want to cast you, Drafts that keep auditions off the public page, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Free registration with a capacity is free as well, unlimited, and so is scanning the QR at the door. Charging for a seat on work you produce yourself is Pro at '.plan_price($proMonthly).' a month. Event Schedule charges zero platform fees on sales either way.',
            ],
            [
                'q' => 'How does my schedule become a credits list?',
                'a' => 'Set the default layout to List. The schedule then reads as a list of productions rather than a month grid, and past work sits under its own Past Events heading instead of disappearing. Every credit keeps its date, its venue and its link, so the page is current the day a run closes without you exporting anything.',
            ],
            [
                'q' => 'What happens when a theater casts me in a production?',
                'a' => 'If the company also uses Event Schedule, they can add you to the production and it arrives on your schedule as a request. It goes on your schedule only once you accept it, though their own event page lists the cast either way. What you accept carries their own dates and details rather than a second copy you have to keep in step, and you can decline anything you would rather not list.',
            ],
            [
                'q' => 'A company listed me before I joined. Is that page mine?',
                'a' => 'It can be. When a company names a performer who is not on Event Schedule, a page is created for them. It says which company made it and that you have not claimed it, credits each date to the schedule that added it, and stays out of search engines. Sign in with the email address the company entered for you and press "Claim this page": it becomes your schedule with those credits already on it, and the companies that were listing you keep listing you without a request each time. If it is not you, "This is not me" takes it down.',
            ],
            [
                'q' => 'Can I keep auditions and rehearsals off my public page?',
                'a' => 'Yes. Saving an event as a Draft keeps it members-only, so an audition or a rehearsal call sits on the same schedule without appearing publicly. Sub-schedules keep productions, workshops and teaching on separate strands of the same link.',
            ],
            [
                'q' => 'Can I sell tickets to my own show?',
                'a' => 'Yes, on the Pro plan at '.plan_price($proMonthly).' a month, which is what lets a seat carry a price. Named ticket types with their own prices, quantities and sales windows, payment through Stripe, PayPal, Invoice Ninja, Payfast (in rand), a payment link or cash, and the live check-in dashboard, promo codes, add-ons and per-attendee tickets come with it. Scanning the QR code at the door is free on any plan, and a free preview or a scratch night takes registrations without one. Event Schedule charges no platform fee on either plan.',
            ],
        ];

        $dotSections = [
            ['top', 'Your credits'],
            ['list', 'The list'],
            ['cast', 'Getting cast'],
            ['produce', 'Producing'],
            ['follow', 'Your audience'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    @php
        $tpArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $tpDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        // The casting breakdown: name, description, blog slug.
        $tpRoles = [
            ['Musical Theater Performers', 'Runs, concerts and cabaret nights, each keeping its dates and its venue once the run has closed.', 'for-musical-theater-performers'],
            ['Dramatic Actors', 'Straight plays at companies that book you months ahead. Their dates arrive on your page for you to accept.', 'for-drama-actors'],
            ['Community Theater', 'A company season and a volunteer cast, on one link that the whole town can follow and subscribe to.', 'for-community-theater-performers'],
            ['Improv & Sketch', 'A weekly night is one recurring event with the dates you are actually on, not forty separate entries.', 'for-improv-sketch-performers'],
            ['Experimental & Fringe', 'Short runs, site-specific work and shared bills. Publish only the dates that are ready and keep the rest as Drafts.', 'for-experimental-fringe-theater'],
            ["Children's & Youth Theater", 'School matinees and family weekends, with free registration and a capacity on each date.', 'for-childrens-youth-theater'],
        ];
        // How each notice hangs: paper colour, tilt, pin.
        $tpPins = [
            ['', '-1.6deg', '#c8402f'],
            ['tp-rev-blue', '1.2deg', '#e9b820'],
            ['tp-rev-yellow', '-0.7deg', '#3f9b57'],
            ['tp-rev-green', '1.5deg', '#e8862a'],
            ['tp-rev-gold', '-1.1deg', '#f2f2ee'],
            ['tp-rev-buff', '0.9deg', '#2a2a2a'],
        ];
        $tpProps = [
            ['Sub-schedules', 'Keep productions, teaching and workshops on separate strands of one link', marketing_url('/features/sub-schedules')],
            ['Recurring Events', 'A run set up once, with a closing performance', marketing_url('/features/recurring-events')],
            ['Ticketing', 'Named ticket types, QR check-in, and zero platform fees', marketing_url('/features/ticketing')],
            ['Newsletters', 'Email the people who follow you, with open and click rates', marketing_url('/features/newsletters')],
        ];
    @endphp

    <div id="tp">

        <nav class="tp-tabs es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as $tabIndex => [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}"><b aria-hidden="true">{{ $tabIndex === 0 ? 'T' : $tabIndex }}</b><span aria-hidden="true">{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- Title page, and the resume stapled to the headshot           -->
        <!-- ============================================================ -->
        <section id="top" class="tp-hero">
            <div class="tp-wrap tp-hero-grid">
                <div class="tp-stack es-fade-up es-d-1">
                <i class="tp-fan tp-rev-gold" style="--r: -5.4deg; --x: -0.6rem; --y: 0.9rem;" aria-hidden="true"></i>
                <i class="tp-fan tp-rev-green" style="--r: 4.4deg; --x: 0.9rem; --y: 0.5rem;" aria-hidden="true"></i>
                <i class="tp-fan tp-rev-yellow" style="--r: -3deg; --x: -0.2rem; --y: 0.7rem;" aria-hidden="true"></i>
                <i class="tp-fan tp-rev-blue" style="--r: 2.1deg; --x: 0.5rem; --y: 0.2rem;" aria-hidden="true"></i>
                <div class="tp-leaf tp-title">
                    <i class="tp-brad" style="--y: 4.4rem;" aria-hidden="true"></i>
                    <i class="tp-brad" style="--y: calc(50% - 0.6rem);" aria-hidden="true"></i>
                    <i class="tp-brad" style="--y: calc(100% - 5.6rem);" aria-hidden="true"></i>
                    <span class="tp-pencil tp-p1" aria-hidden="true">off book by Fri!</span>

                    <div class="tp-title-in">
                        <div class="tp-h1-wrap">
                            <h1 class="tp-h1">
                                <x-marketing.hero-eyebrow class="tp-eyebrow">Actor schedules, for theatre makers too</x-marketing.hero-eyebrow>
                                <span class="tp-line tp-l1"><span class="tp-type">Your r&eacute;sum&eacute; was</span><i class="tp-caret" aria-hidden="true"></i></span><br>
                                <span class="tp-line tp-l2"><span class="tp-type">true <span class="tp-hl">in March</span>.</span><i class="tp-caret" aria-hidden="true"></i></span>
                            </h1>
                            <span class="tp-pencil tp-p2" aria-hidden="true">(it is November)</span>
                        </div>

                        <p class="tp-by" aria-hidden="true">written by<br>the companies that cast you</p>

                        <p class="tp-logline">
                            A schedule set to List is a credits list that keeps itself: every production
                            dated, the closed ones still there, and the next one added by the company
                            that cast you.
                        </p>

                        <div class="tp-cta">
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="tp-btn">
                                Start your credits page
                                {!! $tpArrow !!}
                            </a>
                            <a href="#list" class="tp-go">
                                See how the list works
                                {!! $tpDown !!}
                            </a>
                        </div>

                        <div class="tp-title-foot" aria-hidden="true"><span>Rehearsal draft</span><span>White pages</span></div>
                    </div>
                </div>
                </div>

                <!-- The credits sheet. The top row arrives after the rest. -->
                <div class="tp-cv es-fade-up es-d-3">
                    <div class="tp-cv-photo" aria-hidden="true"></div>
                    <div class="tp-cv-sheet">
                        <i class="tp-staple" aria-hidden="true"></i>
                        <h2 class="tp-cv-name">Maya Okonkwo</h2>
                        <p class="tp-cv-sub">Stage &middot; London</p>
                        <p class="tp-cv-url">maya.eventschedule.com</p>

                        <table>
                            <caption>Credits</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Production</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($credits as $i => [$cProd, $cRole, $cCo, $cYear, $cState])
                                    <tr @class(['tp-cv-new' => $i === 0])>
                                        <td>
                                            {{ $cProd }}
                                            @if ($cState === 'live')
                                                <span class="tp-cv-live">On now</span>
                                            @endif
                                            <span class="tp-cv-role-sm">{{ $cRole }}</span>
                                        </td>
                                        <td>{{ $cRole }}</td>
                                        <td>{{ $cCo }}</td>
                                        <td>{{ $cYear }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p class="tp-cv-foot">
                            Nothing here was re-exported. The top line was added by the company that cast her.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 1. The list keeps itself (blue pages)                  -->
        <!-- ============================================================ -->
        <section id="list" class="tp-scene" style="scroll-margin-top: 5rem;">
            <div class="tp-wrap tp-desk">
                <article class="tp-leaf tp-rev-blue" data-reveal>
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker">The list</p>
                            <p class="tp-slug-rev" aria-hidden="true">Blue revised</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <p class="tp-sline" aria-hidden="true"><b>1</b><span>Int. casting office - November</span><b>1</b></p>
                    <h2 class="tp-h2">
                        A closed show is still <span class="tp-hl">a credit</span>.
                    </h2>

                    <p class="tp-action">
                        Most listings vanish when the run ends. Here the date stays, under its own
                        heading, with the venue and the production still attached - so the page a
                        casting director opens in November is right in <span class="tp-circle">November</span>.
                    </p>

                    @foreach ([
                        ['Set the layout to List', 'A schedule can render as a month grid or as a list. The list reads like a credits page rather than a calendar.'],
                        ['Past work has its own heading', 'Closed productions sit below a Past Events divider instead of dropping off the page.'],
                        ['Or hide it, if you would rather', 'One toggle removes past events from the public schedule entirely. The default is to keep them.'],
                    ] as $exIndex => [$t, $d])
                        <div @class(['tp-ex', 'tp-chg' => $exIndex === 0])>
                            <h3 class="tp-cue">{{ $t }}</h3>
                            <p class="tp-dia">{{ $d }}</p>
                            @if ($exIndex === 0)
                                <span class="tp-pencil" aria-hidden="true"><u>slower</u></span>
                            @endif
                        </div>
                    @endforeach

                    <p class="tp-note">
                        <span class="tp-tag">Free</span>
                        <span>The layout, the past-events divider and the toggle are all on the free plan.</span>
                    </p>

                    <p class="tp-cont" aria-hidden="true">(CONTINUED)</p>
                    <i class="tp-ear" aria-hidden="true"></i>
                </article>

                <aside class="tp-aside" data-reveal="right">
                    <div class="tp-phone">
                        <div class="tp-phone-in">
                            <div class="tp-phone-top">
                                <h3>Your public page</h3>
                                <span class="tp-phone-chip">List layout</span>
                            </div>
                            @foreach ([['Macbeth', 'Bridge Theatre', 'Tonight, 7:30pm']] as [$uName, $uCo, $uWhen])
                                <div class="tp-phone-row tp-phone-now">
                                    <div>
                                        <p>{{ $uName }}</p>
                                        <p class="tp-phone-when">{{ $uWhen }}</p>
                                    </div>
                                    <p>{{ $uCo }}</p>
                                </div>
                            @endforeach
                            <div class="tp-phone-div">
                                <span>Past events</span>
                                <i aria-hidden="true"></i>
                            </div>
                            @foreach ([
                                ['The Seagull', 'Studio Four', 'Mar 2025'],
                                ["A Doll's House", 'Bridge Theatre', 'Oct 2025'],
                                ['Constellations', 'Fringe Collective', 'Jun 2024'],
                            ] as [$pName, $pCo, $pWhen])
                                <div class="tp-phone-row">
                                    <div>
                                        <p>{{ $pName }}</p>
                                        <p class="tp-phone-when">{{ $pWhen }}</p>
                                    </div>
                                    <p>{{ $pCo }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 2. Someone else casts you (yellow pages)               -->
        <!-- ============================================================ -->
        <section id="cast" class="tp-scene" style="scroll-margin-top: 5rem;">
            <div class="tp-wrap tp-desk tp-desk-flip">
                <article class="tp-leaf tp-rev-yellow" data-reveal>
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker">Getting cast</p>
                            <p class="tp-slug-rev" aria-hidden="true">Yellow revised</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <p class="tp-sline" aria-hidden="true"><b>2</b><span>Int. rehearsal room - day</span><b>2</b></p>
                    <h2 class="tp-h2">
                        You do not put most of this <span class="tp-hl">on there yourself</span>.
                    </h2>

                    <p class="tp-action">
                        Other people decide your dates. So the companies that cast you can add the
                        production to your schedule, and you decide whether it goes up.
                    </p>
                    <p class="tp-action" aria-hidden="true">A COMPANY enters, holding dates.</p>

                    {{-- The three beats of a booking, each a cue and the speech under it. --}}
                    @foreach ([
                        ['They request', 'Turn on booking requests and a company can ask to put a production on your schedule, with their own dates, venue and details attached.'],
                        ['Nothing appears until you say yes', 'A booking sits as a request until you accept it, and no company can switch that step off for you, so nothing lands on your page that you did not agree to.'],
                        ['Accept it, or turn it down', 'Requests collect in one place for you to take or decline. What you accept keeps the company\'s own dates and details, so there is no second copy to keep in step.'],
                    ] as $exIndex => [$t, $d])
                        <div @class(['tp-ex', 'tp-chg' => $exIndex === 1])>
                            <h3 class="tp-cue">{{ $t }}</h3>
                            <p class="tp-dia">{{ $d }}</p>
                            @if ($exIndex === 1)
                                <span class="tp-pencil" style="left: auto; right: -1.5rem;" aria-hidden="true">look up on &ldquo;yes&rdquo;</span>
                            @endif
                        </div>
                    @endforeach

                    <p class="tp-note">
                        <span class="tp-tag">Free</span>
                        <span>All of it is on the free plan. Nobody needs a seat on your schedule to book you.</span>
                    </p>

                    <p class="tp-cont" aria-hidden="true">(CONTINUED)</p>
                </article>

                <aside class="tp-aside" data-reveal="left">
                    <div class="tp-insert" style="--r: -1.8deg;">
                        <i class="tp-clip" aria-hidden="true"></i>
                        <div class="tp-insert-pg" aria-hidden="true"><span>Insert</span><span>3A.</span></div>
                        <h3>The credit can get there before you do</h3>
                        <p>
                            A company that names you before you are on Event Schedule creates a page for you as it
                            does. The page says who made it and that you have not claimed it, credits each date to
                            the company that added it, and stays out of search engines. Sign in with the email
                            address they entered and claim it: the credits become yours, and the companies already
                            listing you keep doing so without a request each time. If it is not you, "This is not me"
                            takes it down. <x-link href="{{ marketing_url('/docs/creating-events#claim') }}">How claiming works</x-link>
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 3. When you produce it yourself (green pages)          -->
        <!-- ============================================================ -->
        <section id="produce" class="tp-scene" style="scroll-margin-top: 5rem;">
            <div class="tp-wrap tp-desk">
                <article class="tp-leaf tp-rev-green" data-reveal>
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker">Producing</p>
                            <p class="tp-slug-rev" aria-hidden="true">Green revised</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <p class="tp-sline" aria-hidden="true"><b>3</b><span>Int. fringe venue, box office - night</span><b>3</b></p>
                    <h2 class="tp-h2">
                        And when it is <span class="tp-hl">your own show</span>.
                    </h2>

                    <p class="tp-action">
                        A run of twelve nights is one recurring event with a closing performance, not
                        twelve entries. Sell it from the same page the credits are on.
                    </p>

                    @foreach ([
                        ['Named ticket types', 'Full price, concession, preview - each with its own price, quantity and sales window.', true],
                        ['QR check-in', 'Scan tickets at the door from any phone, on every plan. Pro adds the live check-in dashboard, so two people can work the queue and see the same count.', false],
                        ['Zero platform fees', 'You keep the ticket price minus what your payment provider charges, on every plan. There is no cut on top.', false],
                    ] as $exIndex => [$t, $d, $isPro])
                        <div @class(['tp-ex', 'tp-chg' => $exIndex === 2])>
                            <h3 class="tp-cue">{{ $t }}</h3>
                            <p class="tp-paren">{{ $isPro ? 'Pro' : 'Free' }}</p>
                            <p class="tp-dia">{{ $d }}</p>
                            @if ($exIndex === 2)
                                <span class="tp-pencil" aria-hidden="true">count the house</span>
                            @endif
                        </div>
                    @endforeach

                    <p class="tp-note">
                        <span>Setting the run up is free. Putting a price on the seats is Pro. <x-link href="{{ marketing_url('/for-theaters') }}">How a run is built</x-link>.</span>
                    </p>

                    <p class="tp-cont" aria-hidden="true">(CONTINUED)</p>
                </article>

                <aside class="tp-aside" data-reveal="right">
                    <div class="tp-insert" style="--r: 2deg;">
                        <i class="tp-clip" aria-hidden="true"></i>
                        <div class="tp-box-top">
                            <h3>Solo show, Fringe</h3>
                            <span class="tp-tag">Free</span>
                        </div>
                        <p>One recurring event, twelve nights, closing on the last.</p>

                        <div class="tp-box-rows">
                            @foreach ([
                                ['Full price', 'On sale now', '$18', '60'],
                                ['Concession', 'On sale now', '$12', '40'],
                                ['Preview', 'First two nights', '$8', '30'],
                            ] as [$tName, $tWindow, $tPrice, $tQty])
                                <div class="tp-box-row">
                                    <span>{{ $tName }}</span>
                                    <span>{{ $tWindow }}</span>
                                    <span>{{ $tQty }}</span>
                                    <span>{{ $tPrice }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="tp-box-fine">
                            Payment goes through your own Stripe or PayPal account, Invoice Ninja, Payfast (in rand), a payment link or cash at the door. Event Schedule takes none of it.
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 4. The people who follow you (goldenrod pages)         -->
        <!-- ============================================================ -->
        <section id="follow" class="tp-scene" style="scroll-margin-top: 5rem;">
            <div class="tp-wrap tp-desk tp-desk-flip">
                <article class="tp-leaf tp-rev-gold" data-reveal>
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker">Your audience</p>
                            <p class="tp-slug-rev" aria-hidden="true">Goldenrod revised</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <p class="tp-sline" aria-hidden="true"><b>4</b><span>Ext. stage door - after the show</span><b>4</b></p>
                    <h2 class="tp-h2">
                        One link for <span class="tp-hl">the bio and the programme</span>.
                    </h2>

                    <p class="tp-action">
                        The same address in your profile, your programme biography and your emails, and it
                        is never the out-of-date one.
                    </p>

                    {{-- Six voices, set two abreast the way a script sets people talking at once. --}}
                    <div class="tp-dual">
                        <div class="tp-ex">
                            <h3 class="tp-cue">Auditions stay off it</h3>
                            <p class="tp-paren">Free</p>
                            <p class="tp-dia">
                                Save an audition or a rehearsal call as a Draft and it stays members-only:
                                on your schedule, off your public page. Sub-schedules keep productions,
                                teaching and workshops on separate strands of the same link.
                            </p>
                        </div>
                        <div class="tp-ex">
                            <h3 class="tp-cue">Followers</h3>
                            <p class="tp-paren">Free</p>
                            <p class="tp-dia">
                                People follow your schedule and hear about the next production from you,
                                in their inbox, rather than from a feed that decides who sees it.
                            </p>
                        </div>
                        <div class="tp-ex">
                            <h3 class="tp-cue">Newsletters</h3>
                            <p class="tp-paren">Free</p>
                            <p class="tp-dia">
                                Write and send from the same place, with open and click rates afterwards. Ten
                                emails a month on the free plan, a hundred on Pro and a thousand on Enterprise, counted per recipient rather than per send.
                            </p>
                        </div>
                        <div class="tp-ex">
                            <h3 class="tp-cue">Calendar sync</h3>
                            <p class="tp-paren">Free</p>
                            <p class="tp-dia">
                                Two-way sync with Google, Outlook and CalDAV, so a call moved on your phone
                                moves on the schedule too.
                            </p>
                        </div>
                        <div class="tp-ex">
                            <h3 class="tp-cue">Embed and share</h3>
                            <p class="tp-paren">Free</p>
                            <p class="tp-dia">
                                Drop the list into your own site in an iframe, and generate a post-sized or
                                story-sized image for any date.
                            </p>
                        </div>
                        <div class="tp-ex">
                            <h3 class="tp-cue">Online work</h3>
                            <p class="tp-paren">Free</p>
                            <p class="tp-dia">
                                Mark an event as online and add the link people join on, from any platform
                                that gives you a URL. Ticket holders get it with their ticket.
                            </p>
                        </div>
                    </div>

                    <p class="tp-trans" aria-hidden="true">Cut to:</p>
                    <i class="tp-ear" aria-hidden="true"></i>
                </article>

                <aside class="tp-aside" data-reveal="left">
                    <div class="tp-prog" aria-hidden="true">
                        <p class="tp-prog-head">Who's who in the cast</p>
                        <p class="tp-prog-name">Maya Okonkwo <i>(Lady Macbeth)</i></p>
                        <p>Bridge Theatre: Kristine Linde in A Doll's House. Elsewhere: Nina in The Seagull (Studio Four), Marianne in Constellations (Fringe Collective), Viola in Twelfth Night (Parkside Players).</p>
                        <p class="tp-prog-url">Current credits: maya.eventschedule.com</p>
                    </div>
                </aside>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 5. Who it is for: the callboard                        -->
        <!-- ============================================================ -->
        <section id="who" class="tp-board" style="scroll-margin-top: 4rem;">
            <div class="tp-wrap">
                <div data-reveal>
                    <div class="tp-pinned tp-notice" style="--r: -0.8deg; --pin: #c8402f;">
                        <p class="tp-sline" aria-hidden="true"><b>5</b><span>Int. green room, the callboard - day</span><b>5</b></p>
                        <p class="tp-notice-kicker">Who it is for</p>
                        <h2 class="tp-h2">
                            Every kind of <span class="tp-hl">credit</span>.
                        </h2>
                    </div>
                </div>

                <div class="tp-cards" data-reveal-group="80">
                    @foreach ($tpRoles as $roleIndex => [$rName, $rDesc, $rSlug])
                        @php
                            $rPost = get_sub_audience_blog($rSlug);
                            [$rRev, $rRot, $rPin] = $tpPins[$roleIndex];
                        @endphp
                        <div data-reveal>
                            <article class="tp-pinned tp-card {{ $rRev }}" style="--r: {{ $rRot }}; --pin: {{ $rPin }};">
                                <h3>{{ $rName }}</h3>
                                <p>{{ $rDesc }}</p>
                                @if ($rPost)
                                    <a href="{{ blog_url('/' . $rPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $rName }}"><span>Learn more</span></a>
                                @endif
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Scene 6. How it works: three cues in a blackout              -->
        <!-- ============================================================ -->
        <section id="how" class="tp-dark" style="scroll-margin-top: 4rem;">
            <i class="tp-ghost" aria-hidden="true"></i>
            <div class="tp-wrap">
                <p class="tp-trans" style="margin-top: 0;" aria-hidden="true">Blackout.</p>
                <p class="tp-sline" aria-hidden="true"><b>6</b><span>Int. the wings - blackout</span><b>6</b></p>
                <p class="tp-dark-kicker" data-reveal>How it works</p>
                <h2 class="tp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Set it up once, then <span class="tp-hl">stop maintaining it</span>.
                </h2>

                <div class="tp-cues" data-reveal-group="140">
                    @foreach ([
                        ['01', 'Claim the address', 'One link that goes in your profile, your programme biography and every email you send.'],
                        ['02', 'Switch the layout to List', 'The schedule reads as credits, with past productions under their own heading.'],
                        ['03', 'Let the work arrive', 'Companies send requests and you accept the ones you want. If one listed you before you joined, it already made you a page: claim it and those credits come with you.'],
                    ] as [$n, $t, $d])
                        <div data-reveal>
                            <div class="tp-cue-mark" aria-hidden="true"></div>
                            <p class="tp-cue-no"><span aria-hidden="true">Cue </span>{{ $n }}<span aria-hidden="true"> &middot; go</span></p>
                            <h3>{{ $t }}</h3>
                            <p>{{ $d }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="tp-trans" aria-hidden="true">Lights up.</p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- The prop list (key features) and the cast list (related)     -->
        <!-- ============================================================ -->
        <section class="tp-scene">
            <div class="tp-wrap tp-spread">
                <article class="tp-leaf" data-reveal>
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker" aria-hidden="true">Property list</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <h2 class="tp-h2" style="margin-top: 0;">Key features</h2>

                    <div class="tp-props">
                        @foreach ($tpProps as $propIndex => [$pName, $pDesc, $pUrl])
                            <a href="{{ $pUrl }}" class="tp-prop">
                                <span aria-hidden="true">{{ $propIndex + 1 }}.</span>
                                <span>
                                    <strong>{{ $pName }}</strong>
                                    <small>{{ $pDesc }}</small>
                                </span>
                                {!! $tpArrow !!}
                            </a>
                        @endforeach
                    </div>

                    <p class="tp-trans tp-cut">
                        <a href="{{ marketing_url('/features') }}">See all features</a>
                    </p>
                </article>

                <article class="tp-leaf tp-rev-blue" data-reveal style="--reveal-delay: 0.12s;">
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker" aria-hidden="true">Cast list</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <h2 class="tp-h2" style="margin-top: 0;">Related pages</h2>

                    <div class="tp-castlist">
                        @foreach ([
                            ['/for-theaters', 'Theaters'],
                            ['/for-comedians', 'Comedians'],
                            ['/for-dance-groups', 'Dance Groups'],
                            ['/for-spoken-word', 'Spoken Word Artists'],
                        ] as [$relHref, $relName])
                            <a href="{{ marketing_url($relHref) }}" class="tp-castrow">
                                <span>Event Schedule for</span>
                                <i aria-hidden="true"></i>
                                <strong>{{ $relName }}</strong>
                            </a>
                        @endforeach
                    </div>

                    <p class="tp-trans tp-cut">
                        <a href="{{ marketing_url('/use-cases') }}">See all use cases</a>
                    </p>
                </article>
            </div>
        </section>

        <div class="tp-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- Scene 7. Questions, in the dressing room (buff pages)        -->
        <!-- ============================================================ -->
        <section id="faq" class="tp-scene" style="scroll-margin-top: 5rem;">
            <div class="tp-wrap">
                <article class="tp-leaf tp-leaf-c tp-rev-buff" data-reveal>
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker">Questions</p>
                            <p class="tp-slug-rev" aria-hidden="true">Buff revised</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <p class="tp-sline" aria-hidden="true"><b>7</b><span>Int. dressing room - half hour call</span><b>7</b></p>
                    <h2 class="tp-h2">
                        Asked <span class="tp-hl">in the dressing room</span>.
                    </h2>

                    <div class="tp-qa">
                        @foreach ($faqs as $faq)
                            <details name="faq">
                                <summary>
                                    <span class="tp-cue" aria-hidden="true">You</span>
                                    <h3 class="tp-dia"><span class="tp-hl">{{ $faq['q'] }}</span></h3>
                                    <span class="tp-qa-sign" aria-hidden="true"></span>
                                </summary>
                                <div class="tp-qa-a faq-answer">
                                    <p class="tp-cue" aria-hidden="true">Stage Manager</p>
                                    <p class="tp-dia">{{ $faq['a'] }}</p>
                                </div>
                            </details>
                        @endforeach
                    </div>
                </article>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- Scene 8. The last page: your line                            -->
        <!-- ============================================================ -->
        <section id="claim" class="tp-final" style="scroll-margin-top: 4rem;">
            <div class="tp-wrap">
                <article class="tp-leaf tp-leaf-c" data-reveal="panel">
                    <header class="tp-slug">
                        <div class="tp-slug-in">
                            <p class="tp-kicker">Free forever</p>
                            <p class="tp-slug-rev" aria-hidden="true">White pages</p>
                            <p class="tp-pg" aria-hidden="true"></p>
                        </div>
                    </header>

                    <p class="tp-sline" aria-hidden="true"><b>8</b><span>Int. the stage - places, please</span><b>8</b></p>
                    <h2 class="tp-h2">
                        Start the list <span class="tp-hl">that keeps itself</span>.
                    </h2>

                    <p class="tp-action">
                        Every production you have been in, dated, at one address you never have to
                        send a new version of.
                    </p>
                    <p class="tp-action" aria-hidden="true">YOU step into the light.</p>

                    <div class="tp-yours">
                        <p class="tp-cue" aria-hidden="true">You</p>
                        <p class="tp-paren" aria-hidden="true">typing</p>
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div class="tp-claim-wrap">
                            <div dir="ltr" class="es-claim tp-claim">
                                <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="tp-btn">
                                Start your credits page
                                {!! $tpArrow !!}
                            </a>
                        </div>
                        <span class="tp-pencil" aria-hidden="true">your line!</span>
                    </div>

                    <p class="tp-paren tp-nocard">No credit card required</p>

                    <p class="tp-trans" aria-hidden="true">Curtain.</p>
                    <p class="tp-end" aria-hidden="true">End of play</p>
                </article>
            </div>
        </section>

        <div class="tp-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
