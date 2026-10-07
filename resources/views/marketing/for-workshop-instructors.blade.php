<x-marketing-layout>
    <x-slot name="title">Free Workshop Registration and Class Series for Instructors</x-slot>
    <x-slot name="description">Set a workshop up once as a weekly series, cap the seats per session, and sell spots through Stripe or PayPal with zero platform fees. Free to start.</x-slot>
    <x-slot name="breadcrumbTitle">For Workshop Instructors</x-slot>

    <x-slot name="headMeta">
        {{-- The bench's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Work Sans') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Cabin Sketch') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Workshop Instructors"
        description="Set a class up once as a weekly series, cap the bench per session, and sell the spots from one link with zero platform fees."
        audience="Workshop Instructors & Educators"
        keywords="workshop scheduling, class registration software, workshop calendar, teaching class management, free workshop scheduling" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a workshop series online with Event Schedule",
        "description": "Set the class up once, cap the bench, and let students book the session they want.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the class up as a series",
                "text": "Create the class once as a recurring event, pick the day it runs, and give it an end: a last date, or a number of sessions."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Cap the bench",
                "text": "Set the seat limit. It is counted per session date, so a full Saturday does not close the next one. Skip the weeks the studio is closed."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the sheet",
                "text": "Share one link. Take free registrations, or connect Stripe or PayPal and sell spots and multi-class cards with zero platform fees."
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
           For-workshop-instructors "The Workbench" styles.

           The page is the table a maker teaches at: a self-healing
           cutting mat, graph paper, plain kraft, a pegboard, a steel
           rule, masking tape. The wit is the dimension line: wherever
           the copy gives a number (10 sessions, 8 seats, 25 photos,
           3 hours) it is drawn onto the thing it measures, the way a
           plan is dimensioned.

           Everything is scoped under #wi. One unit, --wi-u, is the
           bench's centimetre: the mat's grid, the graph paper and the
           steel rule are all ruled from it, so they agree.

           Grounds re-declare the ink tokens for their own subtree
           (.wi-on-mat, .wi-on-peg, .wi-kraft, .wi-obj), so a card is
           always ink on paper and a mat is always white on green, in
           either colour mode, without a second set of rules.
           ============================================================== */

        #wi {
            --wi-u: 1.25rem;
            --wi-paper: #f5f6f1;
            --wi-paper-2: #ecefe6;
            --wi-line: rgba(31, 95, 82, 0.1);
            --wi-line-2: rgba(31, 95, 82, 0.22);
            --wi-ink: #1d2421;
            --wi-ink-2: #46524c;
            --wi-ink-3: #5d6a63;
            --wi-hair: rgba(29, 36, 33, 0.2);
            --wi-hot: #f26b21;
            --wi-hot-ink: #b0400a;
            --wi-green-ink: #1f5f52;
            --wi-mat: #1f5f52;
            --wi-mat-2: #17483e;
            --wi-yellow: #ffd23f;
            --wi-kraft: #d9c7a3;
            --wi-obj: #fffefa;
            --wi-obj-2: #f1eee2;
            --wi-tape: rgba(244, 234, 198, 0.93);
            --wi-board: #7d5c3a;
            --wi-lamp: transparent;
            --wi-display: 'Cabin Sketch', 'Work Sans', 'Trebuchet MS', sans-serif;
            --wi-text: 'Work Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --wi-mono: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
            position: relative;
            color: var(--wi-ink);
            font-family: var(--wi-text);
            font-size: 1.0625rem;
            line-height: 1.6;
            background-color: var(--wi-paper);
            background-image:
                linear-gradient(var(--wi-line-2) 1px, transparent 1px),
                linear-gradient(90deg, var(--wi-line-2) 1px, transparent 1px),
                linear-gradient(var(--wi-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--wi-line) 1px, transparent 1px);
            background-size:
                calc(var(--wi-u) * 5) calc(var(--wi-u) * 5),
                calc(var(--wi-u) * 5) calc(var(--wi-u) * 5),
                var(--wi-u) var(--wi-u),
                var(--wi-u) var(--wi-u);
        }
        .dark #wi {
            --wi-paper: #101815;
            --wi-paper-2: #141e1a;
            --wi-line: rgba(140, 210, 185, 0.05);
            --wi-line-2: rgba(140, 210, 185, 0.1);
            --wi-ink: #e8eee9;
            --wi-ink-2: #bdc9c2;
            --wi-ink-3: #98a79f;
            --wi-hair: rgba(232, 238, 233, 0.2);
            --wi-hot-ink: #ff9d5c;
            --wi-green-ink: #7fd1ba;
            --wi-mat: #184d42;
            --wi-mat-2: #103830;
            --wi-kraft: #1b1711;
            --wi-obj: #e9e5d8;
            --wi-obj-2: #dcd8ca;
            --wi-tape: rgba(226, 215, 178, 0.92);
            --wi-board: #46331f;
            --wi-lamp: rgba(255, 190, 110, 0.13);
        }
        @media (max-width: 640px) {
            #wi { --wi-u: 1rem; }
        }

        /* The site bar takes the paper. */
        body > header.sticky {
            background-color: rgba(245, 246, 241, 0.9);
            border-bottom-color: rgba(29, 36, 33, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(16, 24, 21, 0.9);
            border-bottom-color: rgba(232, 238, 233, 0.14);
        }

        /* Grounds: each one says what ink means on it. */
        #wi .wi-on-mat {
            --wi-ink: #f6faf7;
            --wi-ink-2: #d9e8e1;
            --wi-ink-3: #c2d6cd;
            --wi-hot-ink: #ffd23f;
            --wi-green-ink: #ffd23f;
            --wi-hair: rgba(255, 255, 255, 0.32);
            color: var(--wi-ink);
        }
        #wi .wi-on-peg {
            --wi-ink: #fff6e3;
            --wi-ink-2: #f3e6cc;
            --wi-ink-3: #f3e6cc;
            --wi-hot-ink: #ffd23f;
            --wi-green-ink: #ffd23f;
            --wi-hair: rgba(255, 246, 227, 0.3);
            color: var(--wi-ink);
        }
        #wi .wi-kraft {
            --wi-ink-3: #46524c;
            --wi-hot-ink: #8a3205;
            --wi-green-ink: #17483e;
        }
        .dark #wi .wi-kraft {
            --wi-ink-3: #98a79f;
            --wi-hot-ink: #ff9d5c;
            --wi-green-ink: #7fd1ba;
        }
        #wi .wi-obj {
            --wi-ink: #1d2421;
            --wi-ink-2: #46524c;
            --wi-ink-3: #55625b;
            --wi-hot-ink: #a23a08;
            --wi-green-ink: #1c5549;
            --wi-hair: rgba(29, 36, 33, 0.2);
            background-color: var(--wi-obj);
            color: var(--wi-ink);
        }

        #wi ::selection { background: var(--wi-hot); color: #1d2421; }
        #wi a:focus-visible,
        #wi summary:focus-visible,
        #wi input:focus-visible {
            outline: 3px solid var(--wi-hot);
            outline-offset: 3px;
        }
        #wi .wi-on-mat a:focus-visible,
        #wi .wi-on-peg a:focus-visible { outline-color: #ffd23f; }

        .wi-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
        .wi-sec { position: relative; padding-block: clamp(4rem, 8vw, 7rem); }
        .wi-sec > .wi-wrap { position: relative; }
        /* At night each section sits under its own pool of lamp light. */
        .wi-sec::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: min(34rem, 100%);
            background: radial-gradient(60rem 24rem at 50% 0, var(--wi-lamp), transparent 70%);
            pointer-events: none;
        }

        /* Kraft: the roll of paper pulled across the bench. */
        .wi-kraft {
            background-color: var(--wi-kraft);
            background-image:
                repeating-linear-gradient(90deg, rgba(120, 88, 38, 0.05) 0 2px, transparent 2px 9px),
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.05) 0 1px, transparent 1px 5px);
        }
        .dark .wi-kraft {
            background-image:
                repeating-linear-gradient(90deg, rgba(210, 170, 100, 0.03) 0 2px, transparent 2px 9px),
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.015) 0 1px, transparent 1px 5px);
        }

        /* Pegboard: hardboard with a hole every two units. */
        .wi-peg {
            background-color: var(--wi-board);
            background-image:
                radial-gradient(circle at 50% 56%, rgba(255, 255, 255, 0.13) 0 0.3rem, transparent 0.32rem),
                radial-gradient(circle at 50% 50%, rgba(22, 13, 5, 0.82) 0 0.22rem, transparent 0.24rem);
            background-size: calc(var(--wi-u) * 2) calc(var(--wi-u) * 2);
            box-shadow: inset 0 1.2rem 1.6rem -1.2rem rgba(0, 0, 0, 0.55), inset 0 -1.2rem 1.6rem -1.2rem rgba(0, 0, 0, 0.45);
        }

        /* ---------------------------------------------------------------
           Type
           --------------------------------------------------------------- */
        .wi-kick {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.3;
            color: var(--wi-ink-3);
        }
        /* The balloon: how a drawing numbers its parts. */
        .wi-balloon {
            flex: none;
            display: grid;
            place-items: center;
            width: 2.1rem;
            aspect-ratio: 1;
            border: 1.5px solid currentColor;
            border-radius: 50%;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0;
            color: var(--wi-ink);
        }
        .wi-h2 {
            margin-top: 0.9rem;
            font-family: var(--wi-display);
            font-weight: 700;
            font-size: clamp(2.3rem, 5.4vw, 4.1rem);
            line-height: 1.02;
            text-wrap: balance;
        }
        .wi-h2 span { color: var(--wi-hot-ink); }
        /* On the mat the lettering is printed, like the mat's own markings. */
        #wi .wi-on-mat .wi-h2,
        #wi .wi-on-mat .wi-h1,
        #wi .wi-on-mat .wi-final-h2 { font-family: var(--wi-text); font-weight: 700; letter-spacing: -0.03em; text-shadow: none; }
        #wi .wi-on-mat .wi-h2 { font-size: clamp(2rem, 4.6vw, 3.5rem); line-height: 1.04; }
        /* By day the section titles are pencilled onto the paper. After dark there is no paper
           ground to pencil on, and pale sketched letters on a dark ground would read as chalk,
           which this page is not: so at night they are set plain, and the pencil stays on the
           cards, which are still paper. */
        .dark #wi .wi-sec:not(.wi-peg) > .wi-wrap .wi-h2,
        .dark #wi .wi-plans h2,
        .dark #wi .wi-keep h2 { font-family: var(--wi-text); font-weight: 700; letter-spacing: -0.03em; }
        .dark #wi .wi-sec:not(.wi-peg) > .wi-wrap .wi-h2 { font-size: clamp(2rem, 4.6vw, 3.5rem); line-height: 1.04; }
        .wi-lede { margin-top: 1.1rem; max-width: 40rem; font-size: 1.15rem; color: var(--wi-ink-2); }
        .wi-head-c { display: grid; justify-items: center; text-align: center; }
        .wi-head-c .wi-lede { margin-inline: auto; }
        .wi-h3 { font-weight: 700; font-size: 1.2rem; line-height: 1.25; }
        .wi-p { margin-top: 0.6rem; color: var(--wi-ink-2); font-size: 1rem; }
        .wi-note { margin-top: 2.5rem; max-width: 44rem; color: var(--wi-ink-2); }
        .wi-note-c { margin-inline: auto; text-align: center; }
        #wi .wi-link {
            font-weight: 700;
            color: var(--wi-ink);
            text-decoration: underline;
            text-decoration-color: var(--wi-hot);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }
        #wi .wi-link:hover { color: var(--wi-hot-ink); }
        .wi-arrow { display: inline-block; width: 1em; height: 1em; vertical-align: -0.12em; margin-inline-start: 0.3em; transition: translate 0.2s ease; }
        a:hover > .wi-arrow { translate: 0.2em 0; }

        .wi-tier {
            display: inline-block;
            padding: 0.22rem 0.5rem 0.16rem;
            border: 1.5px solid currentColor;
            border-radius: 0.25rem;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.68rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            line-height: 1.2;
            color: var(--wi-green-ink);
            white-space: nowrap;
        }
        #wi .wi-tier-paid { background: #f26b21; border-color: #1d2421; color: #1d2421; }

        .wi-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.4rem;
            border: 2px solid #1d2421;
            border-radius: 0.6rem;
            background: #f26b21;
            color: #1d2421;
            font-weight: 700;
            font-size: 1.05rem;
            line-height: 1.15;
            box-shadow: 0 4px 0 #9c3f0d, 0 0.9rem 1.2rem -0.7rem rgba(0, 0, 0, 0.55);
            transition: translate 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }
        .wi-btn:hover { translate: 0 2px; box-shadow: 0 2px 0 #9c3f0d, 0 0.5rem 0.8rem -0.6rem rgba(0, 0, 0, 0.55); }
        .wi-btn:active { translate: 0 4px; box-shadow: 0 0 0 #9c3f0d; }
        .wi-btn svg { width: 1.1rem; height: 1.1rem; }
        .wi-btn-line { background: transparent; border-color: currentColor; color: var(--wi-ink); box-shadow: none; }
        .wi-btn-line:hover { translate: 0 0; box-shadow: none; background: rgba(255, 255, 255, 0.12); }

        /* ---------------------------------------------------------------
           The dimension line: extension ticks, arrowheads, the figure
           written on the line. Horizontal by default; .wi-dim-v stands
           it up from a tablet onward and lets it lie flat on a phone.
           --------------------------------------------------------------- */
        .wi-dim {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.05em;
            line-height: 1;
            color: var(--wi-hot-ink);
            white-space: nowrap;
            transition: clip-path 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.35s;
            clip-path: inset(-1rem 0 -1rem 0);
        }
        .wi-dim b { font-weight: 700; }
        .wi-dim::before,
        .wi-dim::after { content: ""; flex: 1; min-width: 1.1rem; height: 0.95rem; }
        .wi-dim::before {
            border-inline-start: 1.5px solid currentColor;
            background:
                conic-gradient(from 60deg at 0 50%, currentColor 0 60deg, transparent 0) 0 50% / 0.55rem 0.55rem no-repeat,
                linear-gradient(currentColor, currentColor) 0 50% / 100% 1.5px no-repeat;
        }
        .wi-dim::after {
            border-inline-end: 1.5px solid currentColor;
            background:
                conic-gradient(from 240deg at 100% 50%, currentColor 0 60deg, transparent 0) 100% 50% / 0.55rem 0.55rem no-repeat,
                linear-gradient(currentColor, currentColor) 0 50% / 100% 1.5px no-repeat;
        }
        html.es-anim #wi [data-reveal]:not(.is-revealed) .wi-dim,
        html.es-anim #wi .wi-dim[data-reveal]:not(.is-revealed) { clip-path: inset(-1rem 50% -1rem 50%); }
        @media (min-width: 641px) {
            .wi-dim-v { flex-direction: column; }
            .wi-dim-v b { writing-mode: vertical-rl; rotate: 180deg; }
            .wi-dim-v::before,
            .wi-dim-v::after { width: 0.95rem; height: auto; min-width: 0; min-height: 1.1rem; border-inline: 0; }
            .wi-dim-v::before {
                border-top: 1.5px solid currentColor;
                background:
                    conic-gradient(from 150deg at 50% 0, currentColor 0 60deg, transparent 0) 50% 0 / 0.55rem 0.55rem no-repeat,
                    linear-gradient(currentColor, currentColor) 50% 0 / 1.5px 100% no-repeat;
            }
            .wi-dim-v::after {
                border-bottom: 1.5px solid currentColor;
                background:
                    conic-gradient(from -30deg at 50% 100%, currentColor 0 60deg, transparent 0) 50% 100% / 0.55rem 0.55rem no-repeat,
                    linear-gradient(currentColor, currentColor) 50% 0 / 1.5px 100% no-repeat;
            }
            html.es-anim #wi [data-reveal]:not(.is-revealed) .wi-dim-v { clip-path: inset(50% -1rem 50% -1rem); }
        }

        /* ---------------------------------------------------------------
           Masking tape: translucent cream, torn at both ends.
           --------------------------------------------------------------- */
        .wi-tape {
            display: inline-block;
            padding: 0.4rem 1.2rem 0.32rem;
            background: var(--wi-tape);
            color: #2a2a22;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.78rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            line-height: 1.3;
            rotate: var(--r, -1.5deg);
            -webkit-mask:
                linear-gradient(#000 0 0) 6px 0 / calc(100% - 12px) 100% no-repeat,
                linear-gradient(to bottom right, transparent 50%, #000 51%) 0 0 / 6px 7px repeat-y,
                linear-gradient(to bottom left, transparent 50%, #000 51%) 100% 0 / 6px 5px repeat-y;
            mask:
                linear-gradient(#000 0 0) 6px 0 / calc(100% - 12px) 100% no-repeat,
                linear-gradient(to bottom right, transparent 50%, #000 51%) 0 0 / 6px 7px repeat-y,
                linear-gradient(to bottom left, transparent 50%, #000 51%) 100% 0 / 6px 5px repeat-y;
        }
        /* A bare strip, holding a corner down. */
        .wi-strip {
            position: absolute;
            z-index: 2;
            width: 5.2rem;
            height: 1.55rem;
            padding: 0;
            top: -0.75rem;
            pointer-events: none;
        }

        /* ---------------------------------------------------------------
           The cutting mat
           --------------------------------------------------------------- */
        .wi-mat {
            --wi-rule: 2.4rem;
            position: relative;
            border-radius: 1.2rem;
            background-color: var(--wi-mat);
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.3) 1.5px, transparent 1.5px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.3) 1.5px, transparent 1.5px),
                linear-gradient(rgba(255, 255, 255, 0.11) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.11) 1px, transparent 1px);
            background-size:
                calc(var(--wi-u) * 5) calc(var(--wi-u) * 5),
                calc(var(--wi-u) * 5) calc(var(--wi-u) * 5),
                var(--wi-u) var(--wi-u),
                var(--wi-u) var(--wi-u);
            background-position: var(--wi-rule) var(--wi-rule);
            box-shadow:
                inset 0 0 0 1px rgba(0, 0, 0, 0.35),
                inset 0 0 5rem rgba(0, 0, 0, 0.22),
                0 2.2rem 3rem -1.8rem rgba(10, 30, 25, 0.75),
                0 0 0 1px rgba(0, 0, 0, 0.25);
        }
        /* The printed border, and three old knife scores. */
        .wi-mat::before {
            content: "";
            position: absolute;
            inset: 0.5rem;
            border: 1.5px solid rgba(255, 255, 255, 0.4);
            border-radius: 0.8rem;
            background:
                linear-gradient(112deg, transparent calc(50% - 0.5px), rgba(255, 255, 255, 0.09) 50%, transparent calc(50% + 0.5px)) 62% 30% / 46% 22% no-repeat,
                linear-gradient(74deg, transparent calc(50% - 0.5px), rgba(0, 0, 0, 0.16) 50%, transparent calc(50% + 0.5px)) 20% 78% / 30% 30% no-repeat,
                linear-gradient(8deg, transparent calc(50% - 0.5px), rgba(255, 255, 255, 0.07) 50%, transparent calc(50% + 0.5px)) 88% 82% / 34% 8% no-repeat;
            pointer-events: none;
        }
        .wi-mat-top,
        .wi-mat-side {
            position: absolute;
            display: flex;
            overflow: hidden;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.62rem;
            line-height: 1;
            text-align: left;
            color: rgba(255, 255, 255, 0.78);
            pointer-events: none;
        }
        .wi-mat-top {
            top: 0.5rem;
            left: var(--wi-rule);
            right: 1.1rem;
            height: calc(var(--wi-rule) - 0.5rem);
            counter-reset: wi-cm -5;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.75) 1.5px, transparent 1.5px) 0 100% / calc(var(--wi-u) * 5) 46% repeat-x,
                linear-gradient(90deg, rgba(255, 255, 255, 0.5) 1px, transparent 1px) 0 100% / var(--wi-u) 24% repeat-x;
        }
        .wi-mat-side {
            left: 0.5rem;
            top: var(--wi-rule);
            bottom: 1.1rem;
            width: calc(var(--wi-rule) - 0.5rem);
            flex-direction: column;
            counter-reset: wi-cm -5;
            background:
                linear-gradient(rgba(255, 255, 255, 0.75) 1.5px, transparent 1.5px) 100% 0 / 46% calc(var(--wi-u) * 5) repeat-y,
                linear-gradient(rgba(255, 255, 255, 0.5) 1px, transparent 1px) 100% 0 / 24% var(--wi-u) repeat-y;
        }
        .wi-mat-top i,
        .wi-mat-side i { flex: 0 0 calc(var(--wi-u) * 5); counter-increment: wi-cm 5; font-style: normal; }
        .wi-mat-top i::before { content: counter(wi-cm); display: block; padding: 0.3rem 0 0 0.3rem; }
        .wi-mat-side i::before { content: counter(wi-cm); display: block; padding: 0.3rem 0 0 0.25rem; }
        /* The angle guides, struck from the origin corner. */
        .wi-mat-guides { position: absolute; inset: var(--wi-rule) 0.5rem 0.5rem var(--wi-rule); overflow: hidden; border-radius: 0 0 0.8rem 0; pointer-events: none; }
        .wi-mat-guides i {
            position: absolute;
            left: 0;
            top: 0;
            width: 240%;
            height: 0;
            border-top: 1.5px solid rgba(255, 210, 63, 0.22);
            transform-origin: 0 0;
            rotate: var(--a);
            font-style: normal;
        }
        .wi-mat-guides b {
            position: absolute;
            left: var(--at, 18rem);
            top: 0.3rem;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.62rem;
            color: rgba(255, 210, 63, 0.75);
        }
        .wi-mat-mark {
            position: absolute;
            right: 1.4rem;
            bottom: 0.95rem;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.6rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.55);
            pointer-events: none;
        }
        @media (max-width: 760px) {
            .wi-mat { --wi-rule: 1.7rem; border-radius: 0.9rem; }
            .wi-mat-top { display: none; }
            .wi-mat { background-position: var(--wi-rule) 0.5rem; }
            .wi-mat-side { top: 0.5rem; font-size: 0.5rem; }
            .wi-mat-side i::before { padding-left: 0.05rem; }
            .wi-mat-guides { inset: 0.5rem 0.5rem 0.5rem var(--wi-rule); }
            .wi-mat-mark { display: none; }
            .wi-mat-guides b { display: none; }
        }

        /* ---------------------------------------------------------------
           1. Hero: the mat, the headline, the sheet taped to it
           --------------------------------------------------------------- */
        .wi-hero { position: relative; z-index: 1; padding-block: clamp(1.25rem, 3vw, 2.5rem) 0; }
        .wi-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(70rem 34rem at 72% -6%, var(--wi-lamp), transparent 70%);
            pointer-events: none;
        }
        .wi-hero-grid {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 2.75rem;
            align-items: center;
            padding: calc(var(--wi-rule) + 1.6rem) clamp(1.1rem, 3vw, 2.75rem) clamp(2.5rem, 5vw, 3.75rem) calc(var(--wi-rule) + clamp(0.6rem, 2.4vw, 2rem));
        }
        @media (min-width: 1000px) {
            .wi-hero-grid { grid-template-columns: minmax(0, 1.28fr) minmax(0, 0.72fr); gap: 3.5rem; min-height: min(42rem, 80svh); }
        }
        .wi-hero-copy { container-type: inline-size; min-width: 0; }
        .wi-eyebrow { margin-bottom: 1.5rem; }
        .wi-h1 {
            font-family: var(--wi-display);
            font-weight: 700;
            font-size: clamp(2.5rem, 11.4cqi, 5.5rem);
            line-height: 0.98;
        }
        .wi-h1 .es-mask { padding-bottom: 0.16em; margin-bottom: -0.16em; }
        html.es-anim #wi .wi-mask-3 .es-mask-line { animation-delay: 0.42s; }
        .wi-yel { color: #ffd23f; }
        .wi-hero .wi-lede { max-width: 33rem; margin-top: 1.5rem; font-size: clamp(1.08rem, 1.5vw, 1.22rem); }
        .wi-cta { display: flex; flex-wrap: wrap; gap: 0.9rem 1rem; margin-top: 1.9rem; }
        /* Ten Saturdays, set out and measured. */
        .wi-ten { margin-top: 2.25rem; max-width: 26rem; }
        .wi-ten-row { display: grid; grid-template-columns: repeat(10, minmax(0, 1fr)); gap: 0.3rem; margin-bottom: 0.45rem; }
        .wi-ten-row i { aspect-ratio: 1; border: 1.5px solid rgba(255, 255, 255, 0.7); border-radius: 0.2rem; }
        .wi-ten-row i.is-done { background: rgba(255, 255, 255, 0.7); }
        .wi-ten-row i.is-now { background: #f26b21; border-color: #ffd23f; }

        /* The sheet: ruled lines are seats. */
        .wi-sheet-holder { position: relative; min-width: 0; }
        .wi-sheet {
            position: relative;
            padding: 1.5rem 1.35rem 1.2rem;
            rotate: var(--r, 1.4deg);
            box-shadow: 0 1.4rem 2rem -1.2rem rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(0, 0, 0, 0.08);
        }
        .wi-sheet-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.3rem 1rem; }
        .wi-sheet-head strong,
        .wi-sheet-head h3 { font-size: 1.15rem; font-weight: 700; line-height: 1.25; }
        .wi-sheet-head span { font-family: var(--wi-mono); font-weight: 700; font-size: 0.78rem; color: var(--wi-ink-3); }
        .wi-sheet-sub { margin-top: 0.2rem; font-family: var(--wi-mono); font-size: 0.78rem; color: var(--wi-ink-3); }
        .wi-sheet-body { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.7rem; margin-top: 0.9rem; }
        @media (min-width: 641px) { .wi-sheet-body { grid-template-columns: minmax(0, 1fr) auto; gap: 0.5rem; } }
        .wi-sheet-rows { border-top: 2px solid var(--wi-ink); }
        .wi-sheet-rows li {
            display: grid;
            grid-template-columns: 1.6rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.6rem;
            height: 2.15rem;
            border-bottom: 1px solid var(--wi-hair);
        }
        .wi-sheet-rows li > span:first-child { font-family: var(--wi-mono); font-weight: 700; font-size: 0.72rem; color: var(--wi-ink-3); }
        .wi-sheet-rows li > span:nth-child(2) { font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wi-sheet-rows li > span:nth-child(3) { font-family: var(--wi-mono); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--wi-green-ink); }
        .wi-sheet-rows li.is-open > span:nth-child(2) { font-weight: 400; font-family: var(--wi-mono); font-size: 0.82rem; color: var(--wi-ink-3); }
        .wi-sheet-foot { display: flex; justify-content: space-between; gap: 1rem; margin-top: 0.9rem; font-family: var(--wi-mono); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.08em; text-transform: uppercase; }
        .wi-sheet-foot span:last-child { color: var(--wi-hot-ink); }
        .wi-sheet-cap { margin-top: 1.4rem; font-size: 0.95rem; color: var(--wi-ink-2); max-width: 26rem; }

        /* A pencil left lying across the edge of the mat, and the roll the tape came off. */
        .wi-pencil,
        .wi-roll { display: none; }
        @media (min-width: 1100px) {
            .wi-pencil {
                display: block;
                position: absolute;
                z-index: 3;
                left: 41%;
                bottom: -0.55rem;
                width: 13rem;
                height: 0.9rem;
                rotate: -9deg;
                border-radius: 0.22rem 0 0 0.22rem;
                background:
                    linear-gradient(90deg, #e2a996 0 1rem, #b7bcc1 1rem 1.25rem, #8f959b 1.25rem 1.4rem, #b7bcc1 1.4rem 1.75rem, transparent 1.75rem),
                    linear-gradient(180deg, #ffd23f 0 34%, #f0b90f 34% 67%, #d39a06 67%);
                box-shadow: 0 0.55rem 0.45rem -0.25rem rgba(0, 0, 0, 0.5);
                pointer-events: none;
            }
            .wi-pencil::after {
                content: "";
                position: absolute;
                left: 100%;
                top: 0;
                width: 1.45rem;
                height: 100%;
                background: linear-gradient(90deg, #e9cfa6 0 66%, #23272a 66%);
                clip-path: polygon(0 0, 100% 50%, 0 100%);
            }
            .wi-roll {
                display: block;
                position: absolute;
                z-index: 3;
                right: 3.5%;
                top: -1.7rem;
                width: 5.8rem;
                aspect-ratio: 1;
                border-radius: 50%;
                background: radial-gradient(circle, transparent 0 33%, #b79f6a 34% 37%, #f1e6bd 38% 66%, #dccb93 67% 69%, transparent 70%);
                filter: drop-shadow(0 0.6rem 0.5rem rgba(0, 0, 0, 0.45));
                pointer-events: none;
            }
            .dark .wi-pencil,
            .dark .wi-roll { filter: brightness(0.86) drop-shadow(0 0.6rem 0.5rem rgba(0, 0, 0, 0.5)); }
        }

        /* What people teach: a run of tape labels. */
        .wi-crafts { position: relative; margin-top: clamp(1.75rem, 4vw, 2.75rem); min-width: 0; }
        .wi-crafts .es-marquee-track { gap: 1rem; padding-right: 1rem; align-items: center; padding-block: 0.5rem; }
        .wi-crafts .wi-tape { rotate: var(--r, -1.5deg); font-size: 0.85rem; }
        .wi-crafts .wi-tape:nth-child(3n) { --r: 1.6deg; }
        .wi-crafts .wi-tape:nth-child(3n + 1) { --r: -2.2deg; }
        .wi-crafts .wi-tape:nth-child(4n) { background: #f26b21; color: #1d2421; }

        /* ---------------------------------------------------------------
           The steel rule
           --------------------------------------------------------------- */
        .wi-steel {
            position: relative;
            display: flex;
            overflow: hidden;
            height: 2.5rem;
            border-radius: 0.2rem;
            counter-reset: wi-cm -5;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: 0.6rem;
            line-height: 1;
            color: #2b3136;
            background:
                linear-gradient(90deg, #262b30 1.5px, transparent 1.5px) 0 0 / calc(var(--wi-u) * 5) 58% repeat-x,
                linear-gradient(90deg, #262b30 1px, transparent 1px) 0 0 / var(--wi-u) 32% repeat-x,
                linear-gradient(90deg, rgba(38, 43, 48, 0.7) 1px, transparent 1px) calc(var(--wi-u) / 2) 0 / var(--wi-u) 18% repeat-x,
                repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.16) 0 1px, transparent 1px 3px),
                linear-gradient(180deg, #eef0f2 0%, #cfd4d8 42%, #aab1b7 58%, #d6dadd 100%);
            box-shadow: 0 0.7rem 1rem -0.6rem rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.7), inset 0 -1px 0 rgba(0, 0, 0, 0.3);
        }
        .dark .wi-steel { filter: brightness(0.8); }
        .wi-steel i { flex: 0 0 calc(var(--wi-u) * 5); counter-increment: wi-cm 5; font-style: normal; }
        .wi-steel i::before { content: counter(wi-cm); display: block; padding: 1.55rem 0 0 0.3rem; }
        .wi-steel::after {
            content: "";
            position: absolute;
            right: 0.7rem;
            top: 50%;
            width: 0.8rem;
            aspect-ratio: 1;
            translate: 0 -50%;
            border-radius: 50%;
            background: #3a4046;
            box-shadow: inset 0 2px 2px rgba(0, 0, 0, 0.7), 0 1px 0 rgba(255, 255, 255, 0.6);
        }
        .wi-steel-wrap { position: relative; z-index: 2; margin-top: clamp(1.5rem, 3vw, 2.25rem); margin-bottom: -1.25rem; rotate: -0.35deg; }
        .wi-steel-straddle { margin-top: calc(-1 * clamp(4rem, 8vw, 7rem) - 1.25rem); margin-bottom: calc(clamp(4rem, 8vw, 7rem) - 1.25rem); rotate: 0.3deg; }

        /* ---------------------------------------------------------------
           2. The unit: three figures, each on its dimension line
           --------------------------------------------------------------- */
        .wi-figs { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 2.5rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 860px) { .wi-figs { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wi-fig { min-width: 0; }
        .wi-fig-draw { min-height: 3rem; display: flex; align-items: flex-end; }
        @media (min-width: 860px) { .wi-fig-draw { height: 4.6rem; } }
        .wi-fig-ten { display: grid; grid-template-columns: repeat(10, minmax(0, 1fr)); gap: 0.28rem; width: 100%; }
        .wi-fig-ten i { aspect-ratio: 1; border: 1.5px solid var(--wi-ink); border-radius: 0.15rem; }
        .wi-fig-ten i:nth-child(-n + 1) { background: var(--wi-ink); }
        .wi-fig-bench { position: relative; width: 100%; display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: 0.4rem; padding-bottom: 1rem; }
        .wi-fig-bench i { aspect-ratio: 1; border: 1.5px solid var(--wi-ink); border-radius: 50%; }
        .wi-fig-bench::after { content: ""; position: absolute; inset: auto 0 0 0; height: 0.6rem; border: 1.5px solid var(--wi-ink); border-radius: 0.15rem; }
        .wi-fig-link {
            width: 100%;
            padding: 0.7rem 0.8rem 0.6rem;
            border: 1.5px solid var(--wi-ink);
            border-radius: 2rem;
            font-family: var(--wi-mono);
            font-weight: 700;
            font-size: clamp(0.66rem, 1.2vw, 0.8rem);
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .wi-fig .wi-dim { margin-top: 0.6rem; font-size: 1.05rem; }
        .wi-fig .wi-dim h3 { font: inherit; }
        .wi-fig .wi-dim h3 span { font-size: 1.5em; }
        .wi-fig .wi-kick { margin-bottom: 1rem; }
        .wi-fig > p:last-child { margin-top: 1rem; color: var(--wi-ink-2); }

        /* ---------------------------------------------------------------
           Cards on the bench
           --------------------------------------------------------------- */
        .wi-card {
            position: relative;
            padding: 1.6rem 1.35rem 1.4rem;
            rotate: var(--r, 0deg);
            box-shadow: 0 1.1rem 1.6rem -1.1rem rgba(20, 30, 25, 0.55), 0 0 0 1px rgba(29, 36, 33, 0.1);
        }
        /* Anything that can be picked up off the bench. */
        .wi-pick { transition: rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), box-shadow 0.3s ease, opacity 0.3s ease; }
        .wi-slot { display: flex; min-width: 0; }
        .wi-slot > * { flex: 1; min-width: 0; }
        .wi-card-top { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-start; gap: 0.5rem 0.9rem; }
        /* Pick one up: it straightens in the hand and the others wait. */
        @media (hover: hover) {
            .wi-pick:hover { rotate: 0deg; translate: 0 -0.45rem; z-index: 1; }
            .wi-card.wi-pick:hover { box-shadow: 0 1.8rem 2.2rem -1.3rem rgba(20, 30, 25, 0.6), 0 0 0 1px rgba(29, 36, 33, 0.1); }
            .wi-picks:has(.wi-pick:hover) .wi-pick:not(:hover) { opacity: 0.72; }
        }
        .wi-three { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.25rem 1.5rem; margin-top: clamp(2.5rem, 5vw, 3.5rem); }
        @media (min-width: 860px) { .wi-three { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

        /* Small diagrams on the three setting cards. */
        .wi-mini { margin-top: 1.1rem; padding-top: 0.9rem; border-top: 1px dashed var(--wi-hair); font-family: var(--wi-mono); font-weight: 700; font-size: 0.7rem; color: var(--wi-ink-3); }
        .wi-mini-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.25rem; text-align: center; }
        .wi-mini-days i { padding: 0.35rem 0 0.25rem; border: 1.5px solid var(--wi-hair); border-radius: 0.2rem; font-style: normal; }
        .wi-mini-days i.is-on { background: var(--wi-ink); border-color: var(--wi-ink); color: var(--wi-obj); }
        .wi-mini-weeks { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.25rem; }
        .wi-mini-weeks i { height: 1.55rem; border: 1.5px solid var(--wi-ink); border-radius: 0.2rem; }
        .wi-mini-weeks i.is-out {
            border-style: dashed;
            border-color: var(--wi-hot-ink);
            background: linear-gradient(to top right, transparent calc(50% - 1px), var(--wi-hot-ink) 50%, transparent calc(50% + 1px));
        }
        .wi-mini-end { display: flex; align-items: center; gap: 0.5rem; }
        .wi-mini-end i { flex: 1; height: 0; border-top: 1.5px dashed var(--wi-ink-3); }
        .wi-mini-end b { padding: 0.25rem 0.5rem 0.18rem; border: 1.5px solid var(--wi-ink); border-radius: 0.2rem; color: var(--wi-ink); }

        /* The cut list: one class, set down as a series. */
        .wi-cutlist { margin-top: clamp(2.75rem, 5vw, 4rem); max-width: 54rem; margin-inline: auto; padding: 1.8rem clamp(1.1rem, 3vw, 2rem) 2.4rem; --r: -0.5deg; }
        .wi-cutlist {
            -webkit-mask: linear-gradient(#000 0 0) 0 0 / 100% calc(100% - 9px) no-repeat, conic-gradient(from -45deg at 50% 100%, #000 90deg, transparent 0) 0 100% / 18px 9px repeat-x;
            mask: linear-gradient(#000 0 0) 0 0 / 100% calc(100% - 9px) no-repeat, conic-gradient(from -45deg at 50% 100%, #000 90deg, transparent 0) 0 100% / 18px 9px repeat-x;
            box-shadow: none;
        }
        .wi-cutlist-drop { filter: drop-shadow(0 1rem 1rem rgba(20, 30, 25, 0.3)); }
        .wi-cutlist-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.3rem 1.5rem; padding-bottom: 0.9rem; border-bottom: 2px solid var(--wi-ink); }
        .wi-cutlist-head h3 { font-family: var(--wi-display); font-weight: 700; font-size: 1.9rem; line-height: 1; }
        .wi-cutlist-head span { font-family: var(--wi-mono); font-weight: 700; font-size: 0.75rem; color: var(--wi-ink-3); }
        .wi-run li {
            display: grid;
            grid-template-columns: 2.4rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.5rem 0.9rem;
            padding: 0.8rem 0.2rem 0.7rem;
            border-bottom: 1px solid var(--wi-hair);
        }
        @media (min-width: 700px) { .wi-run li { grid-template-columns: 2.6rem minmax(0, 1fr) auto 8.5rem; } }
        .wi-run-no { display: grid; place-items: center; width: 2.1rem; aspect-ratio: 1; border: 1.5px solid var(--wi-ink); border-radius: 50%; font-family: var(--wi-mono); font-weight: 700; font-size: 0.74rem; }
        .wi-run-when strong { display: block; line-height: 1.2; }
        .wi-run-when small { font-family: var(--wi-mono); font-size: 0.76rem; color: var(--wi-ink-3); }
        .wi-run-pips { display: none; grid-template-columns: repeat(8, 0.62rem); gap: 0.22rem; }
        @media (min-width: 700px) { .wi-run-pips { display: grid; } }
        .wi-run-pips i { aspect-ratio: 1; border: 1.5px solid var(--wi-ink); border-radius: 50%; }
        .wi-run-pips i.is-taken { background: var(--wi-ink); }
        .wi-run-left { font-family: var(--wi-mono); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.06em; text-transform: uppercase; text-align: end; color: var(--wi-green-ink); }
        .wi-run li.is-full .wi-run-left { color: var(--wi-ink); }
        /* The week taken out: cut along the dashes. */
        .wi-run li.is-skip {
            position: relative;
            border-bottom: 0;
            margin-block: 0.15rem;
            border-block: 2px dashed var(--wi-hot-ink);
            background: repeating-linear-gradient(135deg, transparent 0 7px, rgba(162, 58, 8, 0.07) 7px 9px);
        }
        .wi-run li.is-skip::before {
            content: "\2702\FE0E";
            position: absolute;
            left: -0.2rem;
            top: -0.78rem;
            padding-inline: 0.25rem;
            background: var(--wi-obj);
            color: var(--wi-hot-ink);
            font-size: 1.05rem;
            line-height: 1;
            font-variant-emoji: text;
        }
        .wi-run li.is-skip .wi-run-no { border-style: dashed; color: var(--wi-ink-3); }
        .wi-run li.is-skip .wi-run-when strong { text-decoration: line-through; text-decoration-color: var(--wi-hot-ink); text-decoration-thickness: 2px; }
        .wi-run li.is-skip .wi-run-left { color: var(--wi-hot-ink); }
        .wi-cutlist-foot { margin-top: 1.3rem; font-size: 0.97rem; color: var(--wi-ink-2); }

        /* ---------------------------------------------------------------
           4. Two sides of one sheet, on the mat
           --------------------------------------------------------------- */
        .wi-band { position: relative; padding-block: clamp(1.5rem, 3vw, 2.5rem); }
        .wi-band .wi-mat { padding: calc(var(--wi-rule) + clamp(1.5rem, 4vw, 3rem)) clamp(1.1rem, 4vw, 3.5rem) clamp(2.75rem, 5vw, 4.5rem) calc(var(--wi-rule) + clamp(0.6rem, 3vw, 2.75rem)); }
        .wi-band .wi-mat > * { position: relative; }
        .wi-band .wi-mat > .wi-mat-top,
        .wi-band .wi-mat > .wi-mat-side,
        .wi-band .wi-mat > .wi-mat-guides,
        .wi-band .wi-mat > .wi-mat-mark { position: absolute; }
        .wi-duplex { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem 3.5rem; margin-top: clamp(2.5rem, 5vw, 3.75rem); align-items: start; }
        @media (min-width: 940px) { .wi-duplex { grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr); } }
        .wi-side-label { margin-bottom: 1.4rem; }
        .wi-chain { display: none; }
        @media (min-width: 641px) {
            .wi-chain { display: grid; grid-template-rows: 6fr 2fr; gap: 0; font-size: 0.74rem; }
        }
        .wi-sheet-note { margin-top: 1.1rem; padding-top: 0.9rem; border-top: 1px dashed var(--wi-hair); font-size: 0.92rem; color: var(--wi-ink-2); }
        .wi-public { --r: -1deg; }
        .wi-public-when { margin-top: 0.2rem; font-family: var(--wi-mono); font-size: 0.78rem; color: var(--wi-ink-3); }
        .wi-public-count { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.9rem; margin-top: 1rem; font-family: var(--wi-display); font-weight: 700; font-size: 2.1rem; line-height: 1; color: var(--wi-hot-ink); }
        .wi-public-count i { flex: none; display: grid; grid-template-columns: repeat(8, 0.7rem); gap: 0.2rem; }
        .wi-public-count i b { aspect-ratio: 1; border-radius: 50%; background: var(--wi-ink); }
        .wi-public-count i b:nth-child(n + 7) { background: transparent; border: 1.5px solid var(--wi-hot-ink); }
        .wi-public-form { margin-top: 1.1rem; padding: 0.9rem 1rem; border: 1.5px solid var(--wi-ink); border-radius: 0.4rem; background: var(--wi-obj-2); }
        .wi-public-form p:first-child { font-weight: 700; }
        .wi-public-form p + p { margin-top: 0.25rem; font-size: 0.93rem; color: var(--wi-ink-2); }
        .wi-ticks { margin-top: 2rem; display: grid; gap: 1.1rem; }
        .wi-ticks li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: 0.8rem; color: var(--wi-ink-2); }
        /* A tick drawn in the box, the way a list gets worked through. */
        .wi-tick { position: relative; width: 1.25rem; height: 1.25rem; margin-top: 0.2rem; border: 1.5px solid currentColor; border-radius: 0.2rem; color: var(--wi-ink); }
        .wi-tick::after { content: ""; position: absolute; left: 0.32rem; top: -0.2rem; width: 0.5rem; height: 0.95rem; border: solid var(--wi-hot-ink); border-width: 0 3px 3px 0; rotate: 40deg; }

        /* ---------------------------------------------------------------
           5. The class card: cut it out, punch it ten times
           --------------------------------------------------------------- */
        .wi-pass-wrap { position: relative; max-width: 56rem; margin: clamp(2.75rem, 5vw, 4rem) auto 0; padding: clamp(0.8rem, 2vw, 1.4rem); border: 2px dashed var(--wi-ink-3); border-radius: 0.5rem; }
        .wi-pass-wrap::before {
            content: "\2702\FE0E";
            position: absolute;
            left: 1.5rem;
            top: -0.85rem;
            padding-inline: 0.4rem;
            background: var(--wi-paper);
            color: var(--wi-ink-2);
            font-size: 1.3rem;
            line-height: 1;
            font-variant-emoji: text;
        }
        .wi-pass { padding: 1.5rem clamp(1rem, 3vw, 1.9rem) 1.6rem; box-shadow: 0 0 0 1px rgba(29, 36, 33, 0.12); }
        .wi-pass-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.5rem 1rem; }
        .wi-pass-head h3 { font-family: var(--wi-display); font-weight: 700; font-size: clamp(1.5rem, 3.4vw, 2.1rem); line-height: 1.05; }
        .wi-punch { margin-top: 1.4rem; }
        .wi-punch-row { display: grid; grid-template-columns: repeat(10, minmax(0, 1fr)); gap: clamp(0.25rem, 1.2vw, 0.8rem); }
        .wi-punch-row i { position: relative; aspect-ratio: 1; border: 1.5px solid var(--wi-ink); border-radius: 50%; display: grid; place-items: center; font-family: var(--wi-mono); font-weight: 700; font-style: normal; font-size: clamp(0.6rem, 1.5vw, 0.85rem); color: var(--wi-ink-3); }
        /* A punched hole shows the paper underneath. */
        .wi-punch-row i.is-used { border-color: rgba(29, 36, 33, 0.35); background-color: var(--wi-paper); background-image: linear-gradient(var(--wi-line-2) 1px, transparent 1px), linear-gradient(90deg, var(--wi-line-2) 1px, transparent 1px); background-size: 0.5rem 0.5rem; box-shadow: inset 0 3px 4px rgba(0, 0, 0, 0.5); color: transparent; }
        .wi-punch-row i.is-booked { border-color: var(--wi-hot-ink); border-width: 2.5px; color: var(--wi-hot-ink); }
        .wi-punch .wi-dim { margin-top: 0.7rem; }
        .wi-ledger { width: 100%; margin-top: 1.6rem; border-collapse: collapse; font-size: 0.95rem; }
        .wi-ledger caption { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
        .wi-ledger th,
        .wi-ledger td { padding: 0.65rem 0.5rem 0.55rem 0; text-align: start; vertical-align: top; border-bottom: 1px solid var(--wi-hair); }
        .wi-ledger thead th { font-family: var(--wi-mono); font-weight: 700; font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--wi-ink-3); border-bottom: 2px solid var(--wi-ink); }
        .wi-ledger tbody th { font-family: var(--wi-mono); font-weight: 700; width: 2.2rem; }
        .wi-ledger td:last-child,
        .wi-ledger th:last-child { text-align: end; padding-right: 0; font-family: var(--wi-mono); font-weight: 700; }
        .wi-ledger td:nth-child(2) { white-space: nowrap; }
        .wi-status { font-family: var(--wi-mono); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--wi-ink-3); }
        .wi-status.is-booked { color: var(--wi-hot-ink); }
        .wi-status.is-used { color: var(--wi-green-ink); }
        @media (max-width: 560px) {
            .wi-ledger { font-size: 0.85rem; }
            .wi-ledger td:nth-child(2) { white-space: normal; }
            .wi-ledger th,
            .wi-ledger td { padding-right: 0.4rem; }
        }
        .wi-pass-foot { margin-top: 1.1rem; font-size: 0.95rem; color: var(--wi-ink-2); }
        /* Three rules, each on a scrap with a stitched edge. */
        .wi-swatch {
            position: relative;
            padding: 1.7rem 1.4rem 1.5rem;
            background-color: var(--wi-obj-2);
            background-image:
                repeating-linear-gradient(0deg, rgba(29, 36, 33, 0.045) 0 1px, transparent 1px 3px),
                repeating-linear-gradient(90deg, rgba(29, 36, 33, 0.045) 0 1px, transparent 1px 3px);
            outline: 2px dashed #a23a08;
            outline-offset: -0.6rem;
            rotate: var(--r, 0deg);
            box-shadow: 0 1rem 1.4rem -1rem rgba(20, 30, 25, 0.5);
        }

        /* ---------------------------------------------------------------
           6. Getting paid: three swing tags on the kraft
           --------------------------------------------------------------- */
        .wi-tags { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 1.75rem; margin-top: clamp(2.75rem, 5vw, 3.75rem); }
        @media (min-width: 860px) { .wi-tags { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wi-tag-drop { position: relative; filter: drop-shadow(0 0.9rem 0.8rem rgba(40, 25, 5, 0.3)); rotate: var(--r, 0deg); }
        .wi-tag {
            position: relative;
            height: 100%;
            padding: 3.4rem 1.4rem 1.5rem;
            clip-path: polygon(1.5rem 0, calc(100% - 1.5rem) 0, 100% 1.5rem, 100% 100%, 0 100%, 0 1.5rem);
            -webkit-mask: radial-gradient(circle at 50% 1.5rem, transparent 0.36rem, #000 0.39rem);
            mask: radial-gradient(circle at 50% 1.5rem, transparent 0.36rem, #000 0.39rem);
        }
        /* The reinforcing ring round the hole. */
        .wi-tag::before { content: ""; position: absolute; left: 50%; top: 1.5rem; width: 1.5rem; aspect-ratio: 1; translate: -50% -50%; border-radius: 50%; border: 0.3rem solid #c9a56a; }
        /* The string: one loose curve up and away. */
        .wi-string { position: absolute; left: 50%; top: -1.9rem; width: 3.4rem; height: 3.5rem; translate: -4% 0; border: 2px solid #6b5231; border-color: #6b5231 #6b5231 transparent transparent; border-radius: 0 100% 0 0; pointer-events: none; }
        .dark .wi-string { border-color: #b89a6c #b89a6c transparent transparent; }

        /* ---------------------------------------------------------------
           7. After class: three things taped to the mat
           --------------------------------------------------------------- */
        .wi-qr { display: grid; grid-template-columns: repeat(9, 0.5rem); gap: 1px; padding: 0.45rem; background: #fff; border: 1.5px solid var(--wi-ink); width: max-content; rotate: -3deg; }
        .wi-qr i { aspect-ratio: 1; }
        .wi-qr i.is-on { background: #1d2421; }
        .wi-prop { margin-top: 1.2rem; padding-top: 1rem; border-top: 1px dashed var(--wi-hair); }
        .wi-shots { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0.35rem; }
        .wi-shots i { aspect-ratio: 1; border: 2px solid #fff; box-shadow: 0 0 0 1px rgba(29, 36, 33, 0.25); background: var(--g); }
        .wi-shots i.is-more { display: grid; place-items: center; background: none; border: 1.5px dashed var(--wi-ink-3); box-shadow: none; font-family: var(--wi-mono); font-weight: 700; font-style: normal; font-size: 0.7rem; color: var(--wi-ink-3); }
        .wi-prop .wi-dim { margin-top: 0.5rem; }
        .wi-stars { font-size: 1.5rem; letter-spacing: 0.15em; color: var(--wi-hot-ink); line-height: 1; }
        .wi-stars span { color: var(--wi-ink-3); }
        .wi-lines { margin-top: 0.7rem; display: grid; gap: 0.4rem; }
        .wi-lines i { height: 0.5rem; background: var(--wi-ink); opacity: 0.16; border-radius: 0.2rem; }
        .wi-lines i:last-child { width: 62%; }
        .wi-memo { position: relative; max-width: 46rem; margin: clamp(2.5rem, 5vw, 3.5rem) auto 0; padding: 1.7rem clamp(1.1rem, 3vw, 1.9rem) 1.5rem; --r: 0.6deg; }
        .wi-memo p { color: var(--wi-ink-2); }

        /* ---------------------------------------------------------------
           8. Everything else: six parts, ballooned
           --------------------------------------------------------------- */
        .wi-parts { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0; margin-top: clamp(2.5rem, 5vw, 3.75rem); border: 2px solid var(--wi-ink); border-radius: 0.5rem; background: var(--wi-paper); overflow: hidden; }
        @media (min-width: 760px) { .wi-parts { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .wi-parts { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wi-part { position: relative; display: flex; flex-direction: column; padding: 1.6rem 1.4rem 1.6rem; box-shadow: 0 0 0 1px var(--wi-ink); transition: background-color 0.25s ease; }
        .wi-part:hover { background: var(--wi-paper-2); }
        .wi-part-top { display: flex; align-items: flex-start; gap: 0.8rem; }
        .wi-part-top h3 { flex: 1; min-width: 0; padding-top: 0.25rem; }
        .wi-part .wi-tier { margin-top: 0.3rem; }
        .wi-part .wi-prop { margin-top: auto; }
        .wi-part > .wi-p:last-of-type { margin-bottom: 1.3rem; }
        .wi-cap { margin-top: 0.55rem; font-family: var(--wi-mono); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--wi-ink-3); }
        .wi-queue { display: flex; align-items: center; gap: 0.3rem; }
        .wi-queue i { flex: none; width: 0.85rem; aspect-ratio: 1; border-radius: 50%; border: 1.5px solid var(--wi-ink); background: var(--wi-ink); }
        .wi-queue i.is-free { background: transparent; border-style: dashed; border-color: var(--wi-hot-ink); }
        .wi-queue i.is-wait { background: transparent; }
        .wi-queue i.is-next { background: var(--wi-hot); }
        .wi-queue b { margin-inline: 0.35rem; font-family: var(--wi-mono); font-size: 1rem; line-height: 1; color: var(--wi-hot-ink); }
        .wi-strands { display: grid; gap: 0.4rem; }
        .wi-strands div { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.25rem; }
        .wi-strands i { height: 0.85rem; border-radius: 0.15rem; border: 1.5px solid var(--wi-hair); }
        .wi-strands i.is-a { background: var(--wi-green-ink); border-color: var(--wi-green-ink); }
        .wi-strands i.is-b { background: var(--wi-hot); border-color: var(--wi-hot); }
        .wi-embed { border: 1.5px solid var(--wi-ink); border-radius: 0.3rem; overflow: hidden; }
        .wi-embed-bar { display: flex; gap: 0.25rem; padding: 0.35rem 0.5rem; border-bottom: 1.5px solid var(--wi-ink); }
        .wi-embed-bar i { width: 0.4rem; aspect-ratio: 1; border-radius: 50%; background: var(--wi-ink-3); }
        .wi-embed-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 3px; padding: 0.45rem; }
        .wi-embed-grid i { height: 0.6rem; border-radius: 1px; background: var(--wi-hair); }
        .wi-embed-grid i:nth-child(7n + 6) { background: var(--wi-hot); }
        .wi-clone { display: flex; align-items: center; gap: 0.5rem; font-family: var(--wi-mono); font-weight: 700; font-size: 0.68rem; letter-spacing: 0.04em; text-transform: uppercase; color: var(--wi-ink-2); }
        .wi-clone span { flex: 1; min-width: 0; padding: 0.55rem 0.3rem 0.45rem; border: 1.5px solid var(--wi-ink); border-radius: 0.2rem; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .wi-clone span:last-child { border-style: dashed; border-color: var(--wi-hot-ink); color: var(--wi-hot-ink); }
        .wi-clone b { flex: none; font-size: 1rem; line-height: 1; color: var(--wi-hot-ink); }
        .wi-bar { height: 0.9rem; border: 1.5px solid var(--wi-ink); border-radius: 0.2rem; background: repeating-linear-gradient(90deg, var(--wi-ink) 0 1.5px, transparent 1.5px 2.5%); opacity: 0.85; }
        .wi-agenda { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; border: 1.5px solid var(--wi-ink); border-radius: 0.2rem; font-family: var(--wi-mono); font-weight: 700; font-size: 0.58rem; text-transform: uppercase; letter-spacing: 0.02em; color: var(--wi-ink-2); }
        .wi-agenda span { padding: 0.5rem 0.1rem 0.4rem; text-align: center; white-space: nowrap; }
        .wi-agenda span + span { border-inline-start: 1.5px solid var(--wi-ink); }

        /* ---------------------------------------------------------------
           9. Perfect for: six swing tags on the pegboard
           --------------------------------------------------------------- */
        .wi-hung { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.25rem 1.75rem; margin-top: clamp(3.25rem, 6vw, 4.5rem); }
        @media (min-width: 700px) { .wi-hung { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .wi-hung { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wi-hang { position: relative; display: flex; }
        .wi-hang .wi-tag-drop { flex: 1; transform-origin: 50% 1.5rem; }
        .wi-hang .wi-tag { display: flex; flex-direction: column; }
        .wi-hang .wi-tag a { margin-top: auto; padding-top: 1.1rem; align-self: flex-start; }
        /* The peg: a steel pin through the hole. */
        .wi-pin { position: absolute; left: 50%; top: 1.5rem; z-index: 2; width: 0.95rem; aspect-ratio: 1; translate: -50% -50%; border-radius: 50%; background: radial-gradient(circle at 34% 30%, #fff 0 14%, #cfd4d8 34%, #6d747b 78%, #3d4348 100%); box-shadow: 0 0.35rem 0.3rem rgba(0, 0, 0, 0.5); pointer-events: none; }

        /* ---------------------------------------------------------------
           10. Three steps: worked down and ticked
           --------------------------------------------------------------- */
        .wi-steps-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; margin-top: clamp(2.25rem, 4vw, 3rem); max-width: 56rem; margin-inline: auto; }
        @media (min-width: 641px) { .wi-steps-grid { grid-template-columns: auto minmax(0, 1fr); gap: 1.5rem; } }
        .wi-steps-dim { font-size: 0.9rem; order: 2; }
        @media (min-width: 641px) { .wi-steps-dim { order: 0; } }
        .wi-steps { border-top: 2px solid var(--wi-ink); }
        .wi-steps li { display: grid; grid-template-columns: 1.6rem 3.4rem minmax(0, 1fr); gap: 0.4rem 1rem; align-items: start; padding: 1.4rem 0.2rem 1.3rem; border-bottom: 1px solid var(--wi-hair); }
        .wi-steps-no { font-family: var(--wi-display); font-weight: 700; font-size: 2.3rem; line-height: 0.95; color: var(--wi-hot-ink); }
        .wi-steps li .wi-tick { margin-top: 0.5rem; }
        .wi-steps li p { margin-top: 0.35rem; color: var(--wi-ink-2); }
        @media (max-width: 480px) { .wi-steps li { grid-template-columns: 1.6rem minmax(0, 1fr); } .wi-steps-no { grid-column: 2; font-size: 1.8rem; } .wi-steps li > div { grid-column: 2; } }

        /* ---------------------------------------------------------------
           11. Key features: drawer labels
           --------------------------------------------------------------- */
        .wi-drawers-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3.5rem; align-items: start; }
        @media (min-width: 960px) { .wi-drawers-grid { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .wi-drawers { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        @media (min-width: 640px) { .wi-drawers { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        /* A pressed-steel label holder with a card slid into it. */
        .wi-drawer {
            position: relative;
            display: block;
            padding: 0.55rem 0.55rem 0.55rem 2.1rem;
            border-radius: 0.35rem;
            background:
                radial-gradient(circle at 1.05rem 50%, #3d4348 0 0.19rem, #eef0f2 0.2rem 0.3rem, transparent 0.32rem),
                linear-gradient(180deg, #e9ecee 0%, #c3c9ce 50%, #a9b0b6 52%, #d0d5d9 100%);
            box-shadow: 0 0.6rem 0.9rem -0.6rem rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.8), inset 0 -1px 0 rgba(0, 0, 0, 0.3);
        }
        .dark #wi .wi-swatch { outline-color: #a23a08; }
        .dark .wi-drawer {
            background:
                radial-gradient(circle at 1.05rem 50%, #24282c 0 0.19rem, #c3c8cc 0.2rem 0.3rem, transparent 0.32rem),
                linear-gradient(180deg, #c2c7cb 0%, #9ba2a8 50%, #848b91 52%, #aab0b5 100%);
        }
        .wi-drawer:hover { translate: 0 -0.2rem; box-shadow: 0 0.9rem 1.1rem -0.6rem rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.8), inset 0 -1px 0 rgba(0, 0, 0, 0.3); }
        .wi-drawer-card { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 0.8rem; min-height: 5.4rem; padding: 0.8rem 0.9rem 0.75rem 1rem; box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3); }
        .wi-drawer-card strong { display: block; font-size: 1.1rem; line-height: 1.2; }
        .wi-drawer-card small { display: block; margin-top: 0.25rem; font-size: 0.9rem; line-height: 1.4; color: var(--wi-ink-2); }
        .wi-drawer-card svg { width: 1.2rem; height: 1.2rem; color: var(--wi-hot-ink); transition: translate 0.2s ease; }
        .wi-drawer:hover svg { translate: 0.25rem 0; }
        .wi-more { margin-top: 1.5rem; }
        #wi .wi-more .wi-link { display: inline-block; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials: they
           keep their words and prices and take the bench's paper.
           --------------------------------------------------------------- */
        #wi .wi-plans > section { background: transparent; padding-top: 0.5rem; }
        #wi .wi-plans h2 { font-family: var(--wi-display); font-weight: 700; font-size: clamp(2.1rem, 4.6vw, 3.4rem); line-height: 1.03; letter-spacing: 0; color: var(--wi-ink); }
        #wi .wi-plans h2 + p { color: var(--wi-ink-2); font-size: 1.0625rem; }
        #wi .wi-plans .grid > div { --wi-ink: #1d2421; --wi-ink-2: #46524c; --wi-hot-ink: #a23a08; background: var(--wi-obj); border: 1.5px solid #1d2421; border-radius: 0.3rem; color: #1d2421; box-shadow: 0 1.1rem 1.5rem -1.1rem rgba(20, 30, 25, 0.5); }
        #wi .wi-plans .grid > div:nth-child(1) { rotate: -0.6deg; }
        #wi .wi-plans .grid > div:nth-child(3) { rotate: 0.6deg; }
        #wi .wi-plans .grid > div:hover { rotate: 0deg; box-shadow: 0 1.6rem 2rem -1.2rem rgba(20, 30, 25, 0.55); }
        #wi .wi-plans .grid > div span,
        #wi .wi-plans .grid > div p,
        #wi .wi-plans .grid > div li { color: #46524c; }
        #wi .wi-plans .grid > div .text-3xl { font-family: var(--wi-display); font-weight: 700; font-size: 2.6rem; color: #1d2421; }
        #wi .wi-plans .grid > div .uppercase { font-family: var(--wi-mono); color: #1d2421; }
        #wi .wi-plans .grid > div .rounded-full { background: #f26b21; color: #1d2421; border-radius: 0.2rem; font-family: var(--wi-mono); }
        #wi .wi-plans .grid > div svg { color: #a23a08; }
        #wi .wi-plans a.font-medium { color: var(--wi-ink); text-decoration: underline; text-decoration-color: var(--wi-hot); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        #wi .wi-plans a.rounded-2xl { background: #f26b21; color: #1d2421; border: 2px solid #1d2421; border-radius: 0.6rem; box-shadow: 0 4px 0 #9c3f0d; font-weight: 700; }
        #wi .wi-plans a.rounded-2xl:hover { transform: translateY(2px); box-shadow: 0 2px 0 #9c3f0d; }

        #wi .wi-keep > section { background: var(--wi-paper-2); border-top: 2px solid var(--wi-ink); }
        #wi .wi-keep h2 { font-family: var(--wi-display); font-weight: 700; font-size: clamp(1.9rem, 3.6vw, 2.6rem); line-height: 1.05; color: var(--wi-ink); }
        #wi .wi-keep p.uppercase { font-family: var(--wi-mono); font-weight: 700; letter-spacing: 0.14em; color: var(--wi-hot-ink); }
        #wi .wi-keep .grid > a { --wi-ink: #1d2421; background: var(--wi-obj); border: 1.5px solid #1d2421; border-radius: 0.3rem; }
        #wi .wi-keep .grid > a:hover { box-shadow: 0 1.2rem 1.4rem -1rem rgba(20, 30, 25, 0.55); border-color: #1d2421; }
        #wi .wi-keep .grid > a > span:first-child { display: none; }
        #wi .wi-keep .grid > a h3 { color: #1d2421; }
        #wi .wi-keep .grid > a p { color: #46524c; }
        #wi .wi-keep .grid > a > span:last-child { color: #a23a08; }
        #wi .wi-keep a.self-start { color: var(--wi-hot-ink); }

        /* ---------------------------------------------------------------
           12. Related pages: four colour chips
           --------------------------------------------------------------- */
        .wi-rel-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: end; gap: 1rem 2rem; margin-bottom: 2.25rem; }
        .wi-chips { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; }
        @media (min-width: 900px) { .wi-chips { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem; } }
        .wi-chip { display: flex; flex-direction: column; rotate: var(--r, 0deg); box-shadow: 0 1.1rem 1.4rem -1rem rgba(40, 25, 5, 0.55), 0 0 0 1px rgba(29, 36, 33, 0.12); }
        #wi .wi-chip,
        #wi .wi-drawer {
            transition:
                rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1),
                translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1),
                box-shadow 0.3s ease,
                opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1) var(--reveal-delay, 0s),
                transform 0.9s cubic-bezier(0.22, 1, 0.36, 1) var(--reveal-delay, 0s);
        }
        .wi-chip:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .wi-chip-colour { height: clamp(4.5rem, 11vw, 7rem); background: var(--c); }
        .wi-chip-label { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; padding: 0.9rem 1rem 0.9rem; }
        .wi-chip-label strong { font-size: 1.05rem; line-height: 1.25; }
        .wi-chip-label small { margin-top: auto; font-family: var(--wi-mono); font-weight: 700; font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--wi-hot-ink); }

        /* ---------------------------------------------------------------
           13. Questions
           --------------------------------------------------------------- */
        .wi-faq-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .wi-faq-grid { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
            .wi-faq-head { position: sticky; top: 6.5rem; }
        }
        .wi-qa { border-top: 2px solid var(--wi-ink); background: var(--wi-paper); }
        .wi-qa details { border-bottom: 1px solid var(--wi-hair); }
        .wi-qa summary { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr) 1.4rem; align-items: start; gap: 0.6rem; padding: 1.25rem 0.2rem 1.1rem; cursor: pointer; }
        .wi-qa summary > span { padding-top: 0.2rem; font-family: var(--wi-mono); font-weight: 700; font-size: 0.82rem; color: var(--wi-hot-ink); }
        .wi-qa h3 { font-weight: 700; font-size: 1.15rem; line-height: 1.3; }
        .wi-qa summary i { position: relative; width: 1.4rem; height: 1.4rem; margin-top: 0.1rem; }
        .wi-qa summary i::before,
        .wi-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .wi-qa summary i::after { rotate: 90deg; }
        .wi-qa details[open] summary i::after { rotate: 0deg; }
        .wi-qa details p { padding: 0 0.2rem 1.5rem 3.2rem; max-width: 46rem; color: var(--wi-ink-2); }
        @media (max-width: 560px) { .wi-qa details p { padding-inline-start: 0.2rem; } }

        /* ---------------------------------------------------------------
           14. Finale: a blank sheet, taped down, line 01 open
           --------------------------------------------------------------- */
        .wi-final { padding-block: clamp(3rem, 7vw, 6rem); }
        .wi-final .wi-mat { padding: calc(var(--wi-rule) + clamp(1.75rem, 5vw, 3.5rem)) clamp(1.1rem, 4vw, 3.5rem) clamp(3rem, 6vw, 5rem) calc(var(--wi-rule) + clamp(0.6rem, 3vw, 2.75rem)); text-align: center; }
        .wi-final .wi-mat > * { position: relative; }
        .wi-final .wi-mat > .wi-mat-top,
        .wi-final .wi-mat > .wi-mat-side,
        .wi-final .wi-mat > .wi-mat-guides,
        .wi-final .wi-mat > .wi-mat-mark { position: absolute; }
        .wi-final-h2 { margin: 1.4rem auto 0; font-family: var(--wi-display); font-weight: 700; font-size: clamp(2.6rem, 7.4vw, 5.6rem); line-height: 0.98; text-wrap: balance; text-shadow: 0 2px 0 rgba(0, 0, 0, 0.18); }
        .wi-final-h2 span { color: #ffd23f; }
        .wi-final .wi-lede { margin-inline: auto; max-width: 42rem; }
        .wi-blank { position: relative; width: min(100%, 34rem); margin: clamp(2.5rem, 5vw, 3.5rem) auto 0; padding: 1.7rem clamp(1rem, 3vw, 1.6rem) 1.4rem; text-align: start; --r: -0.8deg; rotate: var(--r); box-shadow: 0 1.5rem 2rem -1.2rem rgba(0, 0, 0, 0.6); }
        .wi-blank-top { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.8rem; border-bottom: 2px solid var(--wi-ink); font-family: var(--wi-mono); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.1em; text-transform: uppercase; }
        .wi-blank-top span:last-child { color: var(--wi-hot-ink); }
        .wi-blank label { display: block; margin-block: 1.1rem 0.45rem; font-family: var(--wi-mono); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--wi-ink-3); }
        .wi-blank-form { display: grid; gap: 1rem; }
        #wi .wi-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 0.9rem;
            border: 0;
            border-bottom: 2px solid #1d2421;
            background: repeating-linear-gradient(135deg, transparent 0 8px, rgba(29, 36, 33, 0.035) 8px 10px);
            font-family: var(--wi-mono);
            font-weight: 700;
            /* Never under 16px: below that iOS zooms the page when the field is focused. Where
               the name and the address cannot share a line at that size, the row wraps and the
               address takes the line below, so neither is ever cut off. */
            flex-wrap: wrap;
            font-size: clamp(1rem, 3.2vw, 1.15rem);
            transition: box-shadow 0.2s ease;
        }
        @media (max-width: 480px) {
            #wi .wi-claim { padding-inline: 0.5rem; }
            #wi .wi-claim::before { display: none; }
            .wi-blank .wi-btn { padding-inline: 0.75rem; }
        }
        #wi .wi-claim::before { content: "01"; flex: none; margin-inline-end: 0.8rem; font-size: 0.74rem; color: #55625b; }
        #wi .wi-claim:focus-within { border-color: #1d2421; box-shadow: 0 0 0 3px #f26b21; }
        #wi .wi-claim input { flex: 1 1 13.5ch; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #1d2421; box-shadow: none; outline: none; }
        #wi .wi-claim input::placeholder { color: #7c887f; }
        .wi-claim span { flex: none; margin-inline-start: auto; color: #55625b; user-select: none; }
        .wi-blank .wi-btn { width: 100%; }
        .wi-blank-note { margin-top: 0.9rem; font-size: 0.93rem; color: var(--wi-ink-3); text-align: center; }

        /* ---------------------------------------------------------------
           The gauge: a rule down the edge of a wide screen, with a brass
           stop that travels it as the page is read.
           --------------------------------------------------------------- */
        .wi-gauge { display: none; }
        @media (min-width: 1500px) {
            /* The nav is a box the size of the page that clips the rule, so the rule stays fixed
               to the screen and still ends where the page does instead of riding over the site
               footer. The rule itself is the list; the brass stop is fixed beside it. */
            .wi-gauge { --wi-gh: min(60vh, 34rem); display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .wi-gauge ol {
                position: fixed;
                left: 1.1rem;
                top: 50%;
                width: 1.5rem;
                height: var(--wi-gh);
                translate: 0 -50%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                border-radius: 0.15rem;
                background:
                    linear-gradient(#262b30 1.5px, transparent 1.5px) 0 0 / 60% calc(100% / 10) repeat-y,
                    linear-gradient(rgba(38, 43, 48, 0.8) 1px, transparent 1px) 0 0 / 34% calc(100% / 50) repeat-y,
                    linear-gradient(90deg, #eef0f2 0%, #cfd4d8 42%, #aab1b7 58%, #d6dadd 100%);
                box-shadow: 0 0.6rem 1rem -0.4rem rgba(0, 0, 0, 0.5), inset 1px 0 0 rgba(255, 255, 255, 0.7), inset -1px 0 0 rgba(0, 0, 0, 0.3);
                pointer-events: auto;
            }
            .dark .wi-gauge ol,
            .dark .wi-gauge-stop { filter: brightness(0.8); }
            /* A mark is a 24px target; the margin hands the extra height back, so the marks
               stay where the rule's own graduations put them. */
            .wi-gauge a { position: relative; display: block; width: 100%; height: 1.5rem; margin-block: -0.3rem; }
            .wi-gauge a span {
                position: absolute;
                left: calc(100% + 0.7rem);
                top: 50%;
                translate: -0.3rem -50%;
                padding: 0.3rem 0.6rem 0.22rem;
                background: #1d2421;
                color: #f5f6f1;
                font-family: var(--wi-mono);
                font-weight: 700;
                font-size: 0.68rem;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                white-space: nowrap;
                border-radius: 0.2rem;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease, translate 0.2s ease;
            }
            .wi-gauge a:hover span,
            .wi-gauge a:focus-visible span { opacity: 1; translate: 0 -50%; }
            .wi-gauge a.is-active::after { content: ""; position: absolute; left: calc(100% + 0.2rem); top: 50%; width: 0.4rem; aspect-ratio: 1; translate: 0 -50%; border-radius: 50%; background: #f26b21; }
            .wi-gauge-stop { display: none; }
            @supports (animation-timeline: scroll()) {
                .wi-gauge-stop {
                    display: block;
                    position: fixed;
                    z-index: 1;
                    left: 0.8rem;
                    top: calc(50% - var(--wi-gh) / 2);
                    width: 2.1rem;
                    height: 0.55rem;
                    margin-top: -0.27rem;
                    border-radius: 0.12rem;
                    background: linear-gradient(180deg, #f3d98a, #c79a3b 55%, #9a7323);
                    box-shadow: 0 2px 3px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.6);
                    animation: wi-gauge linear both;
                    animation-timeline: scroll(root block);
                    pointer-events: none;
                }
            }
        }
        @keyframes wi-gauge { from { top: calc(50% - var(--wi-gh) / 2); } to { top: calc(50% + var(--wi-gh) / 2); } }

        @media (prefers-reduced-motion: reduce) {
            .wi-btn, .wi-pick, .wi-dim, .wi-arrow { transition: none; }
            #wi .wi-chip, #wi .wi-drawer { transition: none; }
            .wi-gauge-stop { animation: none; display: none; }
        }
    </style>

    @php
        // The sheet: eight ruled lines, six written on. The line number IS the
        // seat number, so capacity reads as the small whole number it is.
        $sheet = [
            [1, 'Maya R.', 'Paid'],
            [2, 'Tom A.', 'Paid'],
            [3, 'Priya S.', 'Class card'],
            [4, 'Dan K.', 'Paid'],
            [5, 'Lena M.', 'Class card'],
            [6, 'Ivo P.', 'Paid'],
            [7, null, null],
            [8, null, null],
        ];

        // One class, one recurring event, so every session carries the SAME name
        // and the same start time - the date is what changes. Session 04 keeps
        // its number across the skipped week: an excluded date removes the date,
        // it does not renumber the course.
        $sessions = [
            ['01', 'Sat 7 Feb', '10:00', 'Full', false],
            ['02', 'Sat 14 Feb', '10:00', '2 seats left', false],
            ['03', 'Sat 21 Feb', '10:00', '5 seats left', false],
            ['--', 'Sat 28 Feb', 'Date taken out', 'Skipped', true],
            ['04', 'Sat 7 Mar', '10:00', '8 seats left', false],
            ['05', 'Sat 14 Mar', '10:00', '8 seats left', false],
        ];

        // A ten-class card, four sessions in. Same class every time, because the
        // card spans one recurring event.
        $cardRows = [
            ['1', 'Sat 7 Feb', 'Pottery Fundamentals', 'Used', '9'],
            ['2', 'Sat 14 Feb', 'Pottery Fundamentals', 'Used', '8'],
            ['3', 'Sat 21 Feb', 'Pottery Fundamentals', 'Used', '7'],
            ['4', 'Sat 7 Mar', 'Pottery Fundamentals', 'Booked', '6'],
            ['5', 'Not booked yet', 'Any session left in the term', 'Open', '6'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for workshop instructors?',
                'a' => 'Yes. Publishing your classes, running one as a weekly series, capping the seats with free registration, sorting strands into sub-schedules, emailing the students who follow you and syncing two ways with Google, Outlook or CalDAV are all free forever, with no limit on how many students sign up. So is scanning the QR at the door, on every plan. Putting a price on a spot is the Pro plan at '.plan_price($proMonthly).' a month, along with multi-class cards, custom questions at checkout and the live check-in screen, and Event Schedule charges zero platform fees on what you sell.',
            ],
            [
                'q' => 'Can I run different kinds of workshops on one schedule?',
                'a' => 'Yes. Sub-schedules keep cooking, pottery, photography and craft strands apart on one link, each with its own colour, and every class carries its own description, images, capacity and prices. To be straight about what a sub-schedule is: it sorts and colours, it does not hide. A class you have not announced yet is a draft, and drafts are what keep it off the public page until you say so.',
            ],
            [
                'q' => 'How do students find out about a new class?',
                'a' => 'Two ways, and they are not the same list. Somebody who leaves an email address on your page and confirms it is sent a short digest on its own when you put new classes up, batched so a whole term posted in one sitting is one message, and it costs nothing from your allowance. Somebody signed in who pressed Follow is on the other list, and that one only ever hears from a newsletter you write and send, with open and click rates afterwards. The allowance counts recipients rather than sends, at 10 a month on Free, 100 on Pro and 1,000 on Enterprise. A student who would rather not give an address at all can subscribe to your schedule as a live calendar from its page, and it keeps up as you add and move sessions. Alongside that, share your one link, embed the calendar on the studio site you already have, and download your schedule\'s QR code to tape to the bench.',
            ],
            [
                'q' => 'Can students hear when next term goes on sale?',
                'a' => 'Yes, free on every plan. Switch on the "Notify me" card and, on a session that is not on sale yet, a student can press "Tell me when tickets go on sale" and leave just an email address, with no account. They get one email when spots go on sale, one if you cancel, and a reminder 48 hours before the session, plus any change notice you choose to send. On a session that is already selling, the same list sits beside the buy button as "Tell me if anything changes". Each date keeps its own list, you see how many people are waiting on the event\'s Tickets panel, and it does not touch your newsletter allowance. It is not the waitlist, which is for a session that has already filled.',
            ],
            [
                'q' => 'Can I sell spots and cap the class?',
                'a' => 'Yes. Sell spots with named ticket types, each with its own price, quantity and sales window, and take the money through your own Stripe or PayPal account, or as cash, a payment link, Invoice Ninja, or Payfast if you charge in rand. Charging for a spot is a Pro feature; classes you run for nothing can use registration with a seat limit instead, unlimited on every plan. Either way the count is kept per session date, so a full Saturday does not close the next one, and students see the number of spots left rather than who is on the sheet.',
            ],
            [
                'q' => 'Can I refund a student?',
                'a' => 'Yes, on Pro, from the Sales page, for a student who drops out or a class you call off. A Stripe or PayPal payment goes back through the provider, in full or in part, and a partial refund leaves the spot booked. A spot paid in cash, through a payment link, Payfast or Invoice Ninja is marked as refunded instead, which records it without moving any money. Event Schedule does not email the student about a refund, so that message is yours to send.',
            ],
            [
                'q' => 'How does a multi-class card work?',
                'a' => 'A card is one purchase valid across the series, on the Pro plan. The holder gets a private link and books the session they want, and you can set how many people the card admits at each session. Give it a cancellation cutoff in hours before a session starts: cancel earlier than that and the seat goes back to the class, cancel later and the card\'s policy decides whether the visit is forfeited or the cancellation is blocked outright. Usage is tracked per session, so you can see which weeks a card holder actually turned up.',
            ],
            [
                'q' => 'What about a class that runs twice on the same day?',
                'a' => 'That is two events. One recurring event carries one start time, so a 10am beginners bench and a 2pm intermediate bench are two series, each with its own seat count and its own tickets. It is a little more setup and it keeps the two benches, and their money, properly apart.',
            ],
        ];

        $dotSections = [
            ['top', 'The sheet'],
            ['unit', 'The unit'],
            ['plan', 'The series'],
            ['sheet', 'Two sides'],
            ['card', 'The class card'],
            ['money', 'Getting paid'],
            ['after', 'After class'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Open the sheet'],
        ];
    @endphp

    @php
        // Bench furniture: none of it says anything new about the product.
        $wiArrow = '<svg aria-hidden="true" class="wi-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $wiDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $wiRight = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $wiCrafts = ['Cooking', 'Pottery', 'Photography', 'Woodworking', 'Painting', 'Music lessons', 'Sewing', 'Coding', 'Printmaking', 'Bread'];
        // A sticker for the bench: a stylised code, not a working one.
        $wiQr = ['111010111', '101001101', '111010111', '000110000', '110101011', '001011010', '111001101', '101010011', '111011101'];
    @endphp

    <div id="wi">

        <!-- The gauge: section rail for wide screens -->
        <nav class="wi-gauge es-dotnav" aria-label="Page sections">
            <i class="wi-gauge-stop" aria-hidden="true"></i>
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}"><span aria-hidden="true">{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the mat, and the sheet taped to it                  -->
        <!-- ============================================================ -->
        <section id="top" class="wi-hero" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div class="wi-mat wi-on-mat">
                    <div class="wi-mat-top" aria-hidden="true">@for ($wiTick = 0; $wiTick < 16; $wiTick++)<i></i>@endfor</div>
                    <div class="wi-mat-side" aria-hidden="true">@for ($wiTick = 0; $wiTick < 14; $wiTick++)<i></i>@endfor</div>
                    <div class="wi-mat-guides" aria-hidden="true">
                        <i style="--a: 30deg; --at: 47rem;"><b>30&deg;</b></i>
                        <i style="--a: 45deg; --at: 53rem;"><b>45&deg;</b></i>
                        <i style="--a: 60deg; --at: 45rem;"><b>60&deg;</b></i>
                    </div>

                    <div class="wi-hero-grid">
                        <div class="wi-hero-copy">
                            <h1 class="wi-h1">
                                <x-marketing.hero-eyebrow class="wi-eyebrow wi-tape es-fade-up es-d-1">
                                    Workshop registration for teachers
                                </x-marketing.hero-eyebrow>
                                <span class="es-mask"><span class="es-mask-line">One class.</span></span>
                                <span class="es-mask es-mask-2"><span class="es-mask-line">Ten Saturdays.</span></span>
                                <span class="es-mask wi-mask-3"><span class="es-mask-line"><span class="wi-yel">Eight</span> seats each.</span></span>
                            </h1>
                            <p class="wi-lede es-fade-up es-d-2">
                                Set the workshop up once as a series, cap the bench, and let the sheet fill itself. The seat count is kept per session, so a full Saturday never closes the next one.
                            </p>
                            <div class="wi-cta es-fade-up es-d-3">
                                <a href="#plan" class="wi-btn wi-btn-line">
                                    How a class is set up
                                    {!! $wiDown !!}
                                </a>
                                <a href="{{ app_url('/sign_up?type=talent') }}" class="wi-btn">
                                    Create your schedule
                                    {!! $wiRight !!}
                                </a>
                            </div>
                            <div class="wi-ten es-fade-up es-d-4" aria-hidden="true">
                                <div class="wi-ten-row">@for ($wiSat = 1; $wiSat <= 10; $wiSat++)<i @class(['is-done' => $wiSat === 1, 'is-now' => $wiSat === 2])></i>@endfor</div>
                                <div class="wi-dim"><b>10 Saturdays</b></div>
                            </div>
                        </div>

                        <!-- THE SHEET. Ruled lines are seats. -->
                        <div class="wi-sheet-holder es-fade-up es-d-3">
                            <div class="wi-sheet wi-obj">
                                <i class="wi-tape wi-strip" style="--r: -5deg; left: 9%;" aria-hidden="true"></i>
                                <i class="wi-tape wi-strip" style="--r: 4deg; right: 11%;" aria-hidden="true"></i>
                                <div class="wi-sheet-head">
                                    <strong>Wheel Throwing &middot; Beginners</strong>
                                    <span>Sat 14 Feb &middot; 10:00</span>
                                </div>
                                <p class="wi-sheet-sub">Bench of eight &middot; session 02 of 10</p>
                                <div class="wi-sheet-body">
                                    <ol class="wi-sheet-rows">
                                        @foreach ($sheet as $i => [$no, $who, $how])
                                            <li @class(['is-open' => ! $who])>
                                                <span>{{ $no }}</span>
                                                <span>{{ $who ?: 'open' }}</span>
                                                @if ($how)
                                                    <span>{{ $how }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                    <div class="wi-dim wi-dim-v" aria-hidden="true"><b>8 seats</b></div>
                                </div>
                                <div class="wi-sheet-foot">
                                    <span>6 of 8 taken</span>
                                    <span>2 seats left</span>
                                </div>
                            </div>
                            <p class="wi-sheet-cap">
                                Your side of the sheet. Students see the number, never the names.
                            </p>
                        </div>
                    </div>

                    <p class="wi-mat-mark" aria-hidden="true">Bench mat &middot; 1 square = 1 cm</p>
                    <i class="wi-pencil" aria-hidden="true"></i>
                    <i class="wi-roll" aria-hidden="true"></i>
                </div>
            </div>

            <!-- What people teach -->
            <div class="wi-crafts es-fade-up es-d-5">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach ($wiCrafts as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="wi-tape">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>

            <div class="wi-wrap wi-steel-wrap" aria-hidden="true">
                <div class="wi-steel">@for ($wiTick = 0; $wiTick < 16; $wiTick++)<i></i>@endfor</div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The unit is the bench                                     -->
        <!-- ============================================================ -->
        <section id="unit" class="wi-sec wi-kraft" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div class="wi-head-c">
                    <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">02</b>The unit</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Most calendars think a class is <span>one date.</span>
                    </h2>
                    <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s;">
                        It is a bench, run again next week, with the seats counted separately every time.
                    </p>
                </div>

                <div class="wi-figs" data-reveal-group="140">
                    <div class="wi-fig" data-reveal>
                        <p class="wi-kick">The term</p>
                        <div class="wi-fig-draw" aria-hidden="true">
                            <div class="wi-fig-ten">@for ($wiSat = 0; $wiSat < 10; $wiSat++)<i></i>@endfor</div>
                        </div>
                        <div class="wi-dim">
                            <h3><span>10</span> sessions</h3>
                        </div>
                        <p>One event, a weekly pattern, ending after ten. Typing ten classes in by hand is ten chances to fat-finger a start time.</p>
                    </div>
                    <div class="wi-fig" data-reveal>
                        <p class="wi-kick">The bench</p>
                        <div class="wi-fig-draw" aria-hidden="true">
                            <div class="wi-fig-bench">@for ($wiSeat = 0; $wiSeat < 8; $wiSeat++)<i></i>@endfor</div>
                        </div>
                        <div class="wi-dim">
                            <h3><span>8</span> seats, per session</h3>
                        </div>
                        <p>The cap is held per date, not per class. Fill this Saturday and the next one is still wide open, with its own count.</p>
                    </div>
                    <div class="wi-fig" data-reveal>
                        <p class="wi-kick">The link</p>
                        <div class="wi-fig-draw" aria-hidden="true">
                            <div class="wi-fig-link" dir="ltr">your-workshop.eventschedule.com</div>
                        </div>
                        <div class="wi-dim">
                            <h3>1 address</h3>
                        </div>
                        <p>Students see the whole term at one link and book the week that suits them. They can download that date, or subscribe once to a live calendar that keeps up with the term.</p>
                    </div>
                </div>

                <p class="wi-note wi-note-c" data-reveal>
                    The bench is the unit. Everything below hangs off it.
                    <a href="#plan" class="wi-link">
                        Set one up
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Setting the series                                        -->
        <!-- ============================================================ -->
        <section id="plan" class="wi-sec" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div>
                    <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">03</b>Setting the series</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The day, the weeks off, and <span>the last one.</span>
                    </h2>
                    <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Three settings turn one event into a whole term, and all three are on the free plan.
                    </p>
                </div>

                <div class="wi-three wi-picks" data-reveal-group="120">
                    <div class="wi-slot" data-reveal><div class="wi-card wi-obj wi-pick" style="--r: -1.2deg;">
                        <i class="wi-tape wi-strip" style="--r: -4deg; left: 38%;" aria-hidden="true"></i>
                        <div class="wi-card-top">
                            <h3 class="wi-h3">The day it runs</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">Pick the days of the week and the start time, or repeat every second week, monthly, or once a year. Saturdays is one entry, not ten.</p>
                        <div class="wi-mini" aria-hidden="true">
                            <div class="wi-mini-days"><i>M</i><i>T</i><i>W</i><i>T</i><i>F</i><i class="is-on">S</i><i>S</i></div>
                        </div>
                    </div>
                    </div>
                    <div class="wi-slot" data-reveal><div class="wi-card wi-obj wi-pick" style="--r: 0.8deg;">
                        <i class="wi-tape wi-strip" style="--r: 3deg; left: 30%;" aria-hidden="true"></i>
                        <div class="wi-card-top">
                            <h3 class="wi-h3">The weeks you are closed</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">Take single dates out and add one-off dates in. A closed studio or a kiln repair does not mean rebuilding the term.</p>
                        <div class="wi-mini" aria-hidden="true">
                            <div class="wi-mini-weeks"><i></i><i></i><i></i><i class="is-out"></i><i></i><i></i></div>
                        </div>
                    </div>
                    </div>
                    <div class="wi-slot" data-reveal><div class="wi-card wi-obj wi-pick" style="--r: -0.6deg;">
                        <i class="wi-tape wi-strip" style="--r: -2deg; left: 44%;" aria-hidden="true"></i>
                        <div class="wi-card-top">
                            <h3 class="wi-h3">The last session</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">End on a date, or after a set number of sessions. This is the setting that makes a term a term instead of a weekly class that runs forever.</p>
                        <div class="wi-mini" aria-hidden="true">
                            <div class="wi-mini-end"><span>01</span><i></i><b>10</b></div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- The session plan: numbers survive the skipped week -->
                <div data-reveal>
                <div class="wi-cutlist-drop">
                    <div class="wi-cutlist wi-card wi-obj">
                        <div class="wi-cutlist-head">
                            <h3>Pottery Fundamentals</h3>
                            <span>One recurring event &middot; Saturdays 10:00 &middot; ends after 10</span>
                        </div>
                        <ol class="wi-run">
                            @foreach ($sessions as [$sNo, $sDate, $sNote, $sSeats, $sSkip])
                                @php $wiLeft = $sSkip ? null : ($sSeats === 'Full' ? 0 : (int) $sSeats); @endphp
                                <li @class(['is-skip' => $sSkip, 'is-full' => $sSeats === 'Full'])>
                                    <span class="wi-run-no">{{ $sNo }}</span>
                                    <span class="wi-run-when">
                                        <strong>{{ $sDate }}</strong>
                                        <small>{{ $sNote }}</small>
                                    </span>
                                    <span class="wi-run-pips" aria-hidden="true">
                                        @if (! $sSkip)
                                            @for ($wiPip = 1; $wiPip <= 8; $wiPip++)<i @class(['is-taken' => $wiPip <= 8 - $wiLeft])></i>@endfor
                                        @endif
                                    </span>
                                    <span class="wi-run-left">{{ $sSeats }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <p class="wi-cutlist-foot">
                            The skipped week does not use up a session. A date you take out comes off the count as well, so a term set to end after ten still teaches ten. Change the start time once and every remaining session follows it.
                        </p>
                    </div>
                </div>
                </div>

                <p class="wi-note wi-note-c" data-reveal>
                    A class that runs twice on the same day is two events, because one recurring event carries one start time. Two benches, two seat counts, no argument about which one somebody booked.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Two sides of the same sheet                               -->
        <!-- ============================================================ -->
        <section id="sheet" class="wi-sec wi-band" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div class="wi-mat wi-on-mat">
                    <div class="wi-mat-top" aria-hidden="true">@for ($wiTick = 0; $wiTick < 16; $wiTick++)<i></i>@endfor</div>
                    <div class="wi-mat-side" aria-hidden="true">@for ($wiTick = 0; $wiTick < 22; $wiTick++)<i></i>@endfor</div>

                    <div>
                        <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">04</b>Two sides</p>
                        <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                            You see the sheet. They see <span>the count.</span>
                        </h2>
                        <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s;">
                            Names and email addresses belong to you. The public page shows spots remaining and nothing else: no roster, no addresses, not ever.
                        </p>
                    </div>

                    <div class="wi-duplex">
                        <!-- Your side -->
                        <div data-reveal="left">
                            <p class="wi-kick wi-side-label">Signed in as the instructor</p>
                            <div class="wi-sheet wi-obj" style="--r: -1.2deg;">
                                <i class="wi-tape wi-strip" style="--r: 5deg; left: 10%;" aria-hidden="true"></i>
                                <i class="wi-tape wi-strip" style="--r: -3deg; right: 12%;" aria-hidden="true"></i>
                                <div class="wi-sheet-head">
                                    <h3>Sat 14 Feb &middot; 10:00</h3>
                                    <span>6 of 8</span>
                                </div>
                                <div class="wi-sheet-body">
                                    <ol class="wi-sheet-rows">
                                        @foreach ($sheet as $i => [$no, $who, $how])
                                            <li @class(['is-open' => ! $who])>
                                                <span>{{ $no }}</span>
                                                <span>{{ $who ?: 'open' }}</span>
                                                @if ($how)
                                                    <span>{{ $how }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                    <div class="wi-chain" aria-hidden="true">
                                        <div class="wi-dim wi-dim-v"><b>6</b></div>
                                        <div class="wi-dim wi-dim-v"><b>2</b></div>
                                    </div>
                                </div>
                                <p class="wi-sheet-note">
                                    Every name arrived with an email address, from a sale or from a free registration. Sales export to a CSV on the Pro plan, when you want the list somewhere else.
                                </p>
                            </div>
                        </div>

                        <!-- Their side -->
                        <div data-reveal="right">
                            <p class="wi-kick wi-side-label">What a student sees</p>
                            <div class="wi-card wi-obj wi-public">
                                <i class="wi-tape wi-strip" style="--r: -3deg; left: 42%;" aria-hidden="true"></i>
                                <h3 class="wi-h3">Wheel Throwing &middot; Beginners</h3>
                                <p class="wi-public-when">Sat 14 Feb &middot; 10:00 &middot; Clay Lane Studio</p>
                                <p class="wi-public-count">2 spots remaining<i aria-hidden="true">@for ($wiSeat = 0; $wiSeat < 8; $wiSeat++)<b></b>@endfor</i></p>
                                <div class="wi-public-form">
                                    <p>Register</p>
                                    <p>Name and email, or a card if the class is paid. That is the whole form, unless you have added a question of your own.</p>
                                </div>
                            </div>
                            <ul class="wi-ticks">
                                <li>
                                    <i class="wi-tick" aria-hidden="true"></i>
                                    <span>The number comes off the same count you set, so it is right the moment somebody books.</span>
                                </li>
                                <li>
                                    <i class="wi-tick" aria-hidden="true"></i>
                                    <span>When the session is full, the form becomes a waitlist for that date instead of a dead end: free on registration, Pro once the spots are paid tickets.</span>
                                </li>
                                <li>
                                    <i class="wi-tick" aria-hidden="true"></i>
                                    <span>Free registration with a seat limit is unlimited on every plan; putting a price on the spots is Pro.</span>
                                </li>
                                <li>
                                    <i class="wi-tick" aria-hidden="true"></i>
                                    <span>Not on sale yet, or a student not ready to book? With the "Notify me" card switched on, they can leave just an email address for that date and hear when spots go on sale, if you cancel, and 48 hours before it starts, plus any change notice you send. Free on every plan.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The class card                                            -->
        <!-- ============================================================ -->
        <section id="card" class="wi-sec" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div class="wi-head-c">
                    <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">05</b>The class card</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Ten classes, <span>one purchase.</span>
                    </h2>
                    <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Because the term is one recurring event, a card can span it. The holder gets a private link, books the week that suits them, and the card keeps its own ledger.
                    </p>
                </div>

                <div class="wi-pass-wrap" data-reveal>
                    <div class="wi-pass wi-obj">
                        <div class="wi-pass-head">
                            <h3>Ten-class card &middot; Priya S.</h3>
                            <span class="wi-tier wi-tier-paid">Pro</span>
                        </div>
                        <div class="wi-punch" aria-hidden="true">
                            <div class="wi-punch-row">
                                @for ($wiHole = 1; $wiHole <= 10; $wiHole++)
                                    <i @class(['is-used' => $wiHole <= 3, 'is-booked' => $wiHole === 4])>{{ $wiHole }}</i>
                                @endfor
                            </div>
                            <div class="wi-dim"><b>10 classes</b></div>
                        </div>
                        <table class="wi-ledger">
                            <caption>A ten-class card, four sessions in: each booking with its date, the class it covers, whether it was used, and the number of classes still on the card</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Use</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Class</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cardRows as [$cNo, $cDate, $cClass, $cStatus, $cLeft])
                                    <tr>
                                        <th scope="row">{{ $cNo }}</th>
                                        <td>{{ $cDate }}</td>
                                        <td>{{ $cClass }}</td>
                                        <td>
                                            <span @class(['wi-status', 'is-used' => $cStatus === 'Used', 'is-booked' => $cStatus === 'Booked'])>{{ $cStatus }}</span>
                                        </td>
                                        <td>{{ $cLeft }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="wi-pass-foot">Usage is tracked per session, so you can see which weeks a card holder actually turned up to.</p>
                    </div>
                </div>

                <div class="wi-three wi-picks" data-reveal-group="120">
                    <div class="wi-slot" data-reveal><div class="wi-swatch wi-obj wi-pick" style="--r: 0.9deg;">
                        <div class="wi-card-top">
                            <h3 class="wi-h3">A cutoff you set</h3>
                            <span class="wi-tier wi-tier-paid">Pro</span>
                        </div>
                        <p class="wi-p">Give the card a cancellation cutoff in hours before a session starts. Cancel earlier than that and the seat goes back to the bench, and the waitlist for that date hears about it.</p>
                    </div>
                    </div>
                    <div class="wi-slot" data-reveal><div class="wi-swatch wi-obj wi-pick" style="--r: -0.7deg;">
                        <div class="wi-card-top">
                            <h3 class="wi-h3">A policy for late notice</h3>
                            <span class="wi-tier wi-tier-paid">Pro</span>
                        </div>
                        <p class="wi-p">Past the cutoff, the card decides: forfeit the class, or block the cancellation outright. Either way it is your rule, written down before anybody argues it.</p>
                    </div>
                    </div>
                    <div class="wi-slot" data-reveal><div class="wi-swatch wi-obj wi-pick" style="--r: 1.1deg;">
                        <div class="wi-card-top">
                            <h3 class="wi-h3">More than one seat</h3>
                            <span class="wi-tier wi-tier-paid">Pro</span>
                        </div>
                        <p class="wi-p">Set how many people a card admits at each session, so a parent-and-child card takes two places off the bench rather than one.</p>
                    </div>
                    </div>
                </div>

                <p class="wi-note wi-note-c" data-reveal>
                    Cards are sold alongside single spots, not instead of them. Somebody who wants one Saturday can still just buy one Saturday.
                    <a href="{{ marketing_url('/features/passes') }}" class="wi-link">How class cards work</a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Getting paid                                              -->
        <!-- ============================================================ -->
        <section id="money" class="wi-sec wi-kraft" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div>
                    <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">06</b>Getting paid</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                        What the bench earns is <span>what you keep.</span>
                    </h2>
                    <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s; max-width: 46rem;">
                        Spots are sold through your own Stripe or <a href="{{ marketing_url('/paypal') }}" class="wi-link">PayPal</a> account, or paid in cash on the day. Event Schedule charges zero platform fees on every plan, so past the processor's own fee the money is yours. Putting a price on a spot is the Pro plan; a class you run for nothing takes registrations on any plan.
                    </p>
                </div>

                <div class="wi-tags wi-picks" data-reveal-group="120">
                    <div class="wi-slot" data-reveal><div class="wi-tag-drop wi-pick" style="--r: -1.6deg;">
                        <i class="wi-string" aria-hidden="true"></i>
                        <div class="wi-tag wi-obj">
                            <div class="wi-card-top">
                                <h3 class="wi-h3">Prices, plainly</h3>
                                <span class="wi-tier">Free</span>
                            </div>
                            <p class="wi-p">Named ticket types, each with its own price, quantity and sales window: early bird, concession, materials included. A cheaper rate for booking several at once comes with them. Discount codes are Pro.</p>
                        </div>
                    </div>
                    </div>
                    <div class="wi-slot" data-reveal><div class="wi-tag-drop wi-pick" style="--r: 1.1deg;">
                        <i class="wi-string" aria-hidden="true"></i>
                        <div class="wi-tag wi-obj">
                            <div class="wi-card-top">
                                <h3 class="wi-h3">Ask before they arrive</h3>
                                <span class="wi-tier wi-tier-paid">Pro</span>
                            </div>
                            <p class="wi-p">Custom questions collect what the class actually needs: apron size, dietary needs, whether they are bringing their own camera or borrowing yours.</p>
                        </div>
                    </div>
                    </div>
                    <div class="wi-slot" data-reveal><div class="wi-tag-drop wi-pick" style="--r: -0.8deg;">
                        <i class="wi-string" aria-hidden="true"></i>
                        <div class="wi-tag wi-obj">
                            <div class="wi-card-top">
                                <h3 class="wi-h3">A class as a present</h3>
                                <span class="wi-tier wi-tier-paid">Pro</span>
                            </div>
                            <p class="wi-p">Sell a gift card that somebody sends to a recipient by email, with its balance redeemed toward any class on your schedule.</p>
                        </div>
                    </div>
                    </div>
                </div>

                <p class="wi-note wi-note-c" data-reveal>
                    Teaching for free, or asking for the money in the room? Registration with a seat limit needs none of this and is on the free plan.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. After the class                                           -->
        <!-- ============================================================ -->
        <section id="after" class="wi-sec wi-band" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div class="wi-mat wi-on-mat">
                    <div class="wi-mat-top" aria-hidden="true">@for ($wiTick = 0; $wiTick < 16; $wiTick++)<i></i>@endfor</div>
                    <div class="wi-mat-side" aria-hidden="true">@for ($wiTick = 0; $wiTick < 22; $wiTick++)<i></i>@endfor</div>
                    <div class="wi-mat-guides" aria-hidden="true">
                        <i style="--a: 45deg; --at: 44rem;"><b>45&deg;</b></i>
                    </div>

                    <div class="wi-head-c">
                        <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">07</b>After class</p>
                        <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                            The bit that fills <span>next term.</span>
                        </h2>
                    </div>

                    <div class="wi-three wi-picks" data-reveal-group="120">
                        <div class="wi-slot" data-reveal><div class="wi-card wi-obj wi-pick" style="--r: -1.3deg;">
                            <i class="wi-tape wi-strip" style="--r: 4deg; left: 36%;" aria-hidden="true"></i>
                            <div class="wi-card-top">
                                <h3 class="wi-h3">The code on the bench</h3>
                                <span class="wi-tier">Free</span>
                            </div>
                            <p class="wi-p">Download your schedule's QR code, print it, and tape it to the bench. People who liked the class scan it on the way out and follow you.</p>
                            <div class="wi-prop" aria-hidden="true">
                                <div class="wi-qr">
                                    @foreach ($wiQr as $wiQrRow)
                                        @foreach (str_split($wiQrRow) as $wiQrBit)<i @class(['is-on' => $wiQrBit === '1'])></i>@endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="wi-slot" data-reveal><div class="wi-card wi-obj wi-pick" style="--r: 0.9deg;">
                            <i class="wi-tape wi-strip" style="--r: -3deg; left: 30%;" aria-hidden="true"></i>
                            <div class="wi-card-top">
                                <h3 class="wi-h3">What they made</h3>
                                <span class="wi-tier">Free</span>
                            </div>
                            <p class="wi-p">Students add photos, video and comments to the class they came to, and nothing appears until you approve it. Free covers 25 photos per schedule.</p>
                            <div class="wi-prop" aria-hidden="true">
                                <div class="wi-shots">
                                    <i style="--g: radial-gradient(circle at 50% 62%, #b9774a 0 34%, #d8c2a0 35%);"></i>
                                    <i style="--g: radial-gradient(circle at 50% 50%, #e9e2d2 0 22%, #6f8f86 23% 44%, #cfc2a6 45%);"></i>
                                    <i style="--g: linear-gradient(160deg, #c9a37a 0 48%, #8a5a36 49%);"></i>
                                    <i style="--g: radial-gradient(circle at 42% 58%, #2f5c52 0 30%, #dcd0b6 31%);"></i>
                                    <i class="is-more">+21</i>
                                </div>
                                <div class="wi-dim"><b>25 photos</b></div>
                            </div>
                        </div>
                        </div>
                        <div class="wi-slot" data-reveal><div class="wi-card wi-obj wi-pick" style="--r: -0.7deg;">
                            <i class="wi-tape wi-strip" style="--r: 2deg; left: 44%;" aria-hidden="true"></i>
                            <div class="wi-card-top">
                                <h3 class="wi-h3">Whether it landed</h3>
                                <span class="wi-tier wi-tier-paid">Pro</span>
                            </div>
                            <p class="wi-p">Collect star ratings and written comments from the people who attended, so the second run of a class is better than the first.</p>
                            <div class="wi-prop" aria-hidden="true">
                                <div class="wi-stars">&#9733;&#9733;&#9733;&#9733;<span>&#9733;</span></div>
                                <div class="wi-lines"><i></i><i></i></div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="wi-memo wi-card wi-obj" data-reveal>
                        <i class="wi-tape wi-strip" style="--r: -2deg; left: 44%;" aria-hidden="true"></i>
                        <p>
                            Being straight about following: there are two lists. Somebody who confirmed an email address on your page gets a short digest when you add classes, and it costs nothing from your allowance. Somebody who only pressed Follow hears from you through a newsletter you write and send, and that allowance counts recipients: 10 a month on Free, 100 on Pro, 1,000 on Enterprise.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Everything else: six parts                                -->
        <!-- ============================================================ -->
        <section id="rest" class="wi-sec" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div>
                    <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">08</b>Everything else</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Between one Saturday and the next.
                    </h2>
                </div>

                <div class="wi-parts" data-reveal>
                    <!-- 1 -->
                    <div class="wi-part">
                        <div class="wi-part-top">
                            <b class="wi-balloon" aria-hidden="true">A</b>
                            <h3 class="wi-h3">Tell the students you already have</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">Everyone who found you from the bench, the link or the embed and then followed your schedule is a mailing list you own. Write a newsletter when the next term goes up, and read the open and click rates afterwards.</p>
                        <p class="wi-p">The number worth knowing: the allowance counts recipients, not sends, so one email to 40 students is 40 of the month's allowance.</p>
                        <div class="wi-prop" aria-hidden="true">
                            <div class="wi-bar"></div>
                            <div class="wi-dim"><b>40 students = 40</b></div>
                        </div>
                    </div>
                    <!-- 2 -->
                    <div class="wi-part">
                        <div class="wi-part-top">
                            <b class="wi-balloon" aria-hidden="true">B</b>
                            <h3 class="wi-h3">When a session fills</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">A full session turns its sign-up into a waitlist for that date rather than a dead end. Cancel a booking and the freed seat is offered to the first person waiting on that date, and if they do not take it the offer passes to the next one. Free when the class takes registrations; on paid tickets the waitlist is Pro.</p>
                        <div class="wi-prop" aria-hidden="true">
                            <div class="wi-queue"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i class="is-free"></i><b>&larr;</b><i class="is-next"></i><i class="is-wait"></i><i class="is-wait"></i></div>
                            <p class="wi-cap">Freed seat &middot; first in line</p>
                        </div>
                    </div>
                    <!-- 3 -->
                    <div class="wi-part">
                        <div class="wi-part-top">
                            <b class="wi-balloon" aria-hidden="true">C</b>
                            <h3 class="wi-h3">Beginners and advanced</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">Sub-schedules keep the strands apart on one link, each with its own colour. They sort and they colour; hiding a class until you announce it is what a draft is for.</p>
                        <div class="wi-prop" aria-hidden="true">
                            <div class="wi-strands">
                                <div><i class="is-a"></i><i></i><i class="is-a"></i><i></i><i class="is-a"></i><i></i></div>
                                <div><i></i><i class="is-b"></i><i></i><i class="is-b"></i><i></i><i class="is-b"></i></div>
                            </div>
                            <p class="wi-cap">Two strands &middot; one link</p>
                        </div>
                    </div>
                    <!-- 4 -->
                    <div class="wi-part">
                        <div class="wi-part-top">
                            <b class="wi-balloon" aria-hidden="true">D</b>
                            <h3 class="wi-h3">On the studio site you already have</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">Embed the calendar on your own site so the term lives where people look you up, and sync two ways with Google, Outlook and CalDAV. A recurring class crosses as a single entry rather than ten, so the term itself stays here where the seats are counted.</p>
                        <p class="wi-p">Built-in analytics show page views, the devices people are on and where the traffic came from. That is what they measure, and nothing more.</p>
                        <div class="wi-prop" aria-hidden="true">
                            <div class="wi-embed">
                                <div class="wi-embed-bar"><i></i><i></i><i></i></div>
                                <div class="wi-embed-grid">@for ($wiCell = 0; $wiCell < 21; $wiCell++)<i></i>@endfor</div>
                            </div>
                        </div>
                    </div>
                    <!-- 5 -->
                    <div class="wi-part">
                        <div class="wi-part-top">
                            <b class="wi-balloon" aria-hidden="true">E</b>
                            <h3 class="wi-h3">Next term, faster</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">Clone a class you have run before and change the dates. Keep it as a draft while you think about it, and publish when the term is settled.</p>
                        <div class="wi-prop" aria-hidden="true">
                            <div class="wi-clone"><span>This term</span><b>&rarr;</b><span>Clone &middot; draft</span></div>
                        </div>
                    </div>
                    <!-- 6 -->
                    <div class="wi-part">
                        <div class="wi-part-top">
                            <b class="wi-balloon" aria-hidden="true">F</b>
                            <h3 class="wi-h3">The running order of one class</h3>
                            <span class="wi-tier">Free</span>
                        </div>
                        <p class="wi-p">Break a session into its parts with times, so people know the three hours are wedging, centering, pulling and a clean-up rather than a mystery. It shows on the class page as an agenda.</p>
                        <p class="wi-p">
                            Already written the blurb somewhere else? Paste the text or drop in the flyer and the details are pulled out for you to check, on every plan.
                            <a href="{{ marketing_url('/features/ai') }}" class="wi-link">How the import works</a>
                        </p>
                        <div class="wi-prop" aria-hidden="true">
                            <div class="wi-agenda"><span>Wedging</span><span>Centering</span><span>Pulling</span><span>Clean-up</span></div>
                            <div class="wi-dim"><b>3 hours</b></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Perfect for: six tags on the pegboard                     -->
        <!-- ============================================================ -->
        <section id="who" class="wi-sec wi-peg wi-on-peg" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap">
                <div class="wi-head-c">
                    <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">09</b>On the pegboard</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Perfect for all <span>workshop instructors</span>
                    </h2>
                    <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Six seats or thirty, a bench is a bench.
                    </p>
                </div>

                @php
                    $wiWho = [
                        ['Cooking Class Instructors', 'From pasta making to pastry arts. Cap the kitchen per session, ask about dietary needs at checkout, and build a following of food lovers.', 'for-cooking-class-instructors', '-2deg'],
                        ['Pottery & Ceramics Teachers', 'Wheel throwing, hand building and glazing. Run the term as one series, hold the wheels to the number you actually have, and sell a ten-class card.', 'for-pottery-ceramics-teachers', '1.4deg'],
                        ['Photography Workshop Leaders', 'Photo walks, studio sessions and editing evenings. Ask what gear they are bringing at checkout, and take a washed-out date out of the series.', 'for-photography-workshop-leaders', '-0.8deg'],
                        ['Craft & Maker Instructors', 'Woodworking, metalwork, sewing and beyond. Put the materials list in the class description and hold the bench to the number of vices on it.', 'for-craft-maker-instructors', '1.8deg'],
                        ['Art Teachers', 'Painting, drawing and mixed media. Let students post what they made to the class page, all held for your approval first.', 'for-art-teachers', '-1.5deg'],
                        ['Music Lesson Instructors', 'Group lessons, masterclasses and jam sessions. Run the term as one weekly series and sell a card that covers the whole thing.', 'for-music-lesson-instructors', '0.9deg'],
                    ];
                @endphp

                <div class="wi-hung wi-picks" data-reveal-group="90">
                    @foreach ($wiWho as [$wiName, $wiDesc, $wiSlug, $wiTilt])
                        @php $wiPost = get_sub_audience_blog($wiSlug); @endphp
                        <div class="wi-hang" data-reveal>
                            <i class="wi-pin" aria-hidden="true"></i>
                            <div class="wi-tag-drop wi-pick" style="--r: {{ $wiTilt }};">
                                <div class="wi-tag wi-obj">
                                    <h3 class="wi-h3">{{ $wiName }}</h3>
                                    <p class="wi-p">{{ $wiDesc }}</p>
                                    @if ($wiPost)
                                        <a href="{{ blog_url('/' . $wiPost->slug) }}" class="wi-link" aria-label="Learn more about Event Schedule for {{ $wiName }}">Learn more{!! $wiArrow !!}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Three steps                                              -->
        <!-- ============================================================ -->
        <section class="wi-sec wi-kraft">
            <div class="wi-wrap">
                <div class="wi-head-c">
                    <h2 class="wi-h2" data-reveal>
                        Three steps
                    </h2>
                </div>
                <div class="wi-steps-grid" data-reveal>
                    <div class="wi-dim wi-dim-v wi-steps-dim" aria-hidden="true"><b>3 steps</b></div>
                    <ol class="wi-steps">
                        @foreach ([['01', 'Set the class up as a series', 'Create the class once as a recurring event, pick the day it runs, and give it an end: a last date, or a number of sessions.'], ['02', 'Cap the bench', 'Set the seat limit. It is counted per session date, so a full Saturday does not close the next one. Skip the weeks the studio is closed.'], ['03', 'Open the sheet', 'Share one link. Take free registrations, or connect Stripe or PayPal and sell spots and class cards with zero platform fees.']] as [$stepNum, $stepTitle, $stepBody])
                            <li>
                                <i class="wi-tick" aria-hidden="true"></i>
                                <div class="wi-steps-no" aria-hidden="true">{{ $stepNum }}</div>
                                <div>
                                    <h3 class="wi-h3">{{ $stepTitle }}</h3>
                                    <p>{{ $stepBody }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. Key features: drawer labels                              -->
        <!-- ============================================================ -->
        <section class="wi-sec">
            <div class="wi-wrap wi-drawers-grid">
                <div>
                    <p class="wi-kick" data-reveal>In the drawers</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                    <p class="wi-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="wi-link">
                            See all features{!! $wiArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $wiDrawers = [
                        ['Recurring Events', 'One class, a weekly pattern, ending after a set number of sessions', marketing_url('/features/recurring-events')],
                        ['Ticketing', 'Named ticket types, class cards, QR check-in and zero platform fees', marketing_url('/features/ticketing')],
                        ['Newsletters', 'Email the students who follow you, with open and click rates', marketing_url('/features/newsletters')],
                        ['Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', marketing_url('/features/calendar-sync')],
                    ];
                @endphp
                <div class="wi-drawers" data-reveal-group="70">
                    @foreach ($wiDrawers as [$wiDrawerName, $wiDrawerDesc, $wiDrawerUrl])
                        <a href="{{ $wiDrawerUrl }}" class="wi-drawer" data-reveal>
                            <span class="wi-drawer-card wi-obj">
                                <span>
                                    <strong>{{ $wiDrawerName }}</strong>
                                    <small>{{ $wiDrawerDesc }}</small>
                                </span>
                                {!! $wiRight !!}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="wi-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 12. Related pages: four colour chips                         -->
        <!-- ============================================================ -->
        <section class="wi-sec wi-kraft">
            <div class="wi-wrap">
                <div class="wi-rel-head">
                    <div>
                        <p class="wi-kick" data-reveal>Other benches</p>
                        <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="wi-link" data-reveal>
                        See all use cases{!! $wiArrow !!}
                    </a>
                </div>

                <div class="wi-chips" data-reveal-group="80">
                    @foreach ([['/for-online-classes', 'Online Classes'], ['/for-fitness-and-yoga', 'Fitness & Yoga'], ['/for-community-centers', 'Community Centers'], ['/for-libraries', 'Libraries']] as $wiRelIndex => [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="wi-chip wi-obj" data-reveal style="--c: {{ ['#1f5f52', '#f26b21', '#ffd23f', '#7d5c3a'][$wiRelIndex] }}; --r: {{ ['-1.4deg', '0.9deg', '-0.6deg', '1.5deg'][$wiRelIndex] }};">
                            <span class="wi-chip-colour" aria-hidden="true"></span>
                            <span class="wi-chip-label">
                                <strong>For {{ $relName }}</strong>
                                <small>
                                    Read more
                                </small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 13. FAQ                                                      -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="wi-sec" style="scroll-margin-top: 5rem;">
            <div class="wi-wrap wi-steel-straddle" aria-hidden="true">
                <div class="wi-steel">@for ($wiTick = 0; $wiTick < 16; $wiTick++)<i></i>@endfor</div>
            </div>
            <div class="wi-wrap wi-faq-grid">
                <div class="wi-faq-head">
                    <p class="wi-kick" data-reveal><b class="wi-balloon" aria-hidden="true">10</b>Before the first cut</p>
                    <h2 class="wi-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s;">
                        What instructors ask before they move a term across.
                    </p>
                </div>

                <div class="wi-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq">
                            <summary>
                                <span aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
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
        <!-- 14. Finale: a blank sheet, line 01 open                      -->
        <!-- ============================================================ -->
        <section id="claim" class="wi-final" style="scroll-margin-top: 4rem;">
            <div class="wi-wrap">
                <div class="wi-mat wi-on-mat">
                    <div class="wi-mat-top" aria-hidden="true">@for ($wiTick = 0; $wiTick < 16; $wiTick++)<i></i>@endfor</div>
                    <div class="wi-mat-side" aria-hidden="true">@for ($wiTick = 0; $wiTick < 16; $wiTick++)<i></i>@endfor</div>
                    <div class="wi-mat-guides" aria-hidden="true">
                        <i style="--a: 30deg; --at: 11rem;"><b>30&deg;</b></i>
                        <i style="--a: 60deg; --at: 26rem;"><b>60&deg;</b></i>
                    </div>

                    <p class="wi-tape" data-reveal style="--r: -2deg;">Free forever</p>
                    <h2 class="wi-final-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Pin up the sheet. <span>Fill the bench.</span>
                    </h2>
                    <p class="wi-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Publishing your classes, capping the seats with free registration and emailing the students who follow you are free forever, with no limit on sign-ups. Charging for a spot, and multi-class cards, are {{ plan_price($proMonthly) }} a month, and nothing is taken off the top.
                    </p>

                    <div class="wi-blank wi-obj" data-reveal="panel">
                        <i class="wi-tape wi-strip" style="--r: -5deg; left: 8%;" aria-hidden="true"></i>
                        <i class="wi-tape wi-strip" style="--r: 4deg; right: 9%;" aria-hidden="true"></i>
                        <div class="wi-blank-top" aria-hidden="true"><span>Sign-up sheet</span><span>8 seats open</span></div>
                        <label for="es-claim-input">Your schedule name</label>
                        <div class="wi-blank-form">
                            <div dir="ltr" class="es-claim wi-claim">
                                <input id="es-claim-input" type="text" placeholder="your-workshop" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="wi-btn">
                                <span>
                                    Create your schedule
                                </span>
                                {!! $wiRight !!}
                            </a>
                        </div>
                        <p class="wi-blank-note">No credit card required</p>
                    </div>

                    <p class="wi-mat-mark" aria-hidden="true">Bench mat &middot; 1 square = 1 cm</p>
                </div>
            </div>
        </section>

        <div class="wi-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
