<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Online Classes | Terms & Class Cards</x-slot>
    <x-slot name="description">Set an online course up once as a term, cap the seats per session, and sell single classes or class cards at zero platform fees. Any video platform works.</x-slot>
    <x-slot name="breadcrumbTitle">For Online Classes</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lexend') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('DM Serif Display') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Sacramento') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Online Classes"
        description="Set a course up once as a term: the night it meets, the weeks you skip, and the session it ends on. Sell the whole term from one link with zero platform fees."
        audience="Online Instructors"
        keywords="online class scheduling, virtual class platform, sell online classes, online teaching, class registration software" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a term of online classes online with Event Schedule",
        "description": "Set the term up once and take registrations for every session from one link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Write the term",
                "text": "Create the course as a recurring event, pick the night it meets, and end the recurrence after a set number of sessions or on a closing date."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Skip the weeks you are off",
                "text": "Add date exceptions for the holiday weeks, and paste your class link on the course so students join from the schedule."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the register",
                "text": "Set a seat cap counted per session date, then take free registrations or sell single sessions and multi-visit class cards."
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
           For-online-classes "The Course" styles.

           The page is a course and the reader is enrolled in it: a
           syllabus down the side, twelve lessons in four modules, a
           progress ring that fills, ticks that fill as each lesson is
           read, and a certificate at the end with the reader's own
           schedule name on it. Twelve, because that is the headline:
           a course is not one class, it is twelve of them.

           Two voices. The player is an interface (Lexend, rounded,
           calm). The certificate is engraved (DM Serif Display, a
           guilloche border, a pleated seal), and that engraving leaks
           back into the interface as the hero's lathe-work rosette
           and the module bands.

           Everything is scoped under #oc. The scroll-driven parts
           (ring, percentage, ticks, the phone's progress line) sit
           inside one @supports block behind html.es-anim, so without
           them the syllabus is a plain numbered list.
           ============================================================== */

        @property --oc-ang { syntax: '<angle>'; inherits: true; initial-value: 0deg; }
        @property --oc-pct { syntax: '<integer>'; inherits: true; initial-value: 0; }

        #oc {
            --oc-bg: #fbf8f1;
            --oc-bg-2: #f4efe2;
            --oc-card: #fffdf8;
            --oc-ink: #16242b;
            --oc-ink-2: #3b4d55;
            --oc-ink-3: #586a71;
            --oc-line: rgba(22, 36, 43, 0.15);
            --oc-line-2: rgba(22, 36, 43, 0.08);
            --oc-line-3: rgba(22, 36, 43, 0.32);
            --oc-teal: #0d5c63;
            --oc-fill: #0d5c63;
            --oc-fill-2: #2d7f84;
            --oc-on-fill: #fbf8f1;
            --oc-sea: #d9eae4;
            --oc-sea-2: #edf5f0;
            --oc-gold: #ffb703;
            --oc-eng: rgba(13, 92, 99, 0.13);
            --oc-eng-2: rgba(13, 92, 99, 0.32);
            --oc-btn-bg: #0d5c63;
            --oc-btn-ink: #fbf8f1;
            --oc-btn-shadow: rgba(13, 92, 99, 0.7);
            --oc-shadow: rgba(13, 60, 65, 0.42);
            --oc-display: 'DM Serif Display', 'Iowan Old Style', 'Palatino Linotype', Georgia, serif;
            --oc-text: 'Lexend', 'Avenir Next', 'Segoe UI', system-ui, sans-serif;
            position: relative;
            background: var(--oc-bg);
            color: var(--oc-ink);
            font-family: var(--oc-text);
            font-size: 1rem;
            line-height: 1.62;
        }
        .dark #oc {
            --oc-bg: #0b1a1d;
            --oc-bg-2: #0e2024;
            --oc-card: #10262a;
            --oc-ink: #f6f1e4;
            --oc-ink-2: #c7d3d1;
            --oc-ink-3: #9bacab;
            --oc-line: rgba(246, 241, 228, 0.16);
            --oc-line-2: rgba(246, 241, 228, 0.08);
            --oc-line-3: rgba(246, 241, 228, 0.36);
            --oc-teal: #7dd6cc;
            --oc-fill: #43b5aa;
            --oc-fill-2: #2b8d85;
            --oc-on-fill: #0b1a1d;
            --oc-sea: #17393d;
            --oc-sea-2: #132c30;
            --oc-gold: #ffc533;
            --oc-eng: rgba(125, 214, 204, 0.1);
            --oc-eng-2: rgba(125, 214, 204, 0.3);
            --oc-btn-bg: #ffc533;
            --oc-btn-ink: #16242b;
            --oc-btn-shadow: rgba(255, 197, 51, 0.5);
            --oc-shadow: rgba(0, 0, 0, 0.7);
        }

        /* The bar above takes the course's paper. */
        body > header.sticky {
            background-color: rgba(251, 248, 241, 0.9);
            border-bottom-color: rgba(22, 36, 43, 0.12);
        }
        .dark body > header.sticky {
            background-color: rgba(11, 26, 29, 0.9);
            border-bottom-color: rgba(246, 241, 228, 0.12);
        }

        #oc ::selection { background: #ffb703; color: #16242b; }
        #oc a:focus-visible,
        #oc summary:focus-visible {
            outline: 3px solid var(--oc-teal);
            outline-offset: 3px;
            border-radius: 0.5rem;
        }

        .oc-wrap { width: min(100% - 2rem, 80rem); margin-inline: auto; }
        .oc-icon { width: 1.1rem; height: 1.1rem; flex: none; }

        /* Buttons */
        .oc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.55rem;
            border-radius: 999px;
            background: var(--oc-btn-bg);
            color: var(--oc-btn-ink);
            font-weight: 700;
            font-size: 1.02rem;
            line-height: 1.2;
            box-shadow: 0 12px 24px -14px var(--oc-btn-shadow);
            transition: translate 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .oc-btn:hover { translate: 0 -2px; box-shadow: 0 18px 30px -14px var(--oc-btn-shadow); }
        .oc-btn .oc-icon { transition: translate 0.2s ease; }
        .oc-btn:hover .oc-icon { translate: 3px 0; }
        .oc-btn-ghost {
            background: transparent;
            color: var(--oc-ink);
            box-shadow: inset 0 0 0 1.5px var(--oc-line-3);
        }
        .oc-btn-ghost:hover { background: var(--oc-sea-2); box-shadow: inset 0 0 0 1.5px var(--oc-ink-3); }
        .oc-btn-ghost:hover .oc-icon { translate: 0 3px; }

        .oc-tag {
            display: inline-flex;
            align-items: center;
            padding: 0.16rem 0.55rem;
            border-radius: 999px;
            background: var(--oc-sea);
            color: var(--oc-teal);
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            line-height: 1.5;
            white-space: nowrap;
        }
        .oc-tag-paid { background: #ffb703; color: #16242b; }
        .oc-link {
            color: var(--oc-teal);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--oc-gold);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }
        .oc-link:hover { text-decoration-color: currentColor; }

        /* ---------------------------------------------------------------
           The course page header (hero)
           --------------------------------------------------------------- */
        .oc-hero { position: relative; overflow: clip; padding-block: clamp(2.25rem, 5vw, 4.25rem) clamp(1.5rem, 3vw, 2.25rem); }
        /* Lathe work, the way a diploma's rosette is engraved: fine rays crossed by
           fine rings, cut to an annulus. */
        .oc-hero::before {
            content: "";
            position: absolute;
            right: -11rem;
            top: -9rem;
            width: 50rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                repeating-conic-gradient(from 0deg, var(--oc-eng) 0 0.5deg, transparent 0.5deg 2.5deg),
                repeating-radial-gradient(circle, transparent 0 8px, var(--oc-eng) 8px 9px);
            -webkit-mask-image: radial-gradient(circle, transparent 0 20%, #000 20.5% 49.5%, transparent 50%);
            mask-image: radial-gradient(circle, transparent 0 20%, #000 20.5% 49.5%, transparent 50%);
            pointer-events: none;
        }
        html.es-anim #oc .oc-hero::before { animation: oc-turn 260s linear infinite; }
        @keyframes oc-turn { to { rotate: 360deg; } }
        @media (max-width: 999.98px) {
            .oc-hero::before { right: -13rem; top: -12rem; width: 30rem; }
        }
        .oc-hero-grid { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem; align-items: center; }
        @media (min-width: 1000px) {
            .oc-hero-grid { grid-template-columns: minmax(0, 1.06fr) minmax(0, 0.94fr); gap: 3.75rem; }
        }
        .oc-hero-copy { container-type: inline-size; min-width: 0; }
        .oc-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
            padding: 0.4rem 0.95rem 0.4rem 0.5rem;
            border-radius: 999px;
            background: var(--oc-card);
            border: 1px solid var(--oc-line);
            font-family: var(--oc-text);
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.4;
            color: var(--oc-ink-2);
        }
        /* A progress ring a third of the way round, as the pill's mark. */
        .oc-eyebrow::before {
            content: "";
            flex: none;
            width: 1.3rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: conic-gradient(var(--oc-gold) 0 120deg, var(--oc-line) 0);
            -webkit-mask-image: radial-gradient(circle, transparent 0 46%, #000 48%);
            mask-image: radial-gradient(circle, transparent 0 46%, #000 48%);
        }
        .oc-h1 {
            font-family: var(--oc-display);
            font-weight: 400;
            font-size: clamp(2.55rem, 11.6cqi, 4.75rem);
            line-height: 1.02;
            letter-spacing: -0.012em;
            text-wrap: balance;
            color: var(--oc-ink);
        }
        .oc-mark {
            padding: 0 0.14em;
            border-radius: 0.14em;
            background-color: #ffb703;
            color: #16242b;
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
        /* The marker goes over "twelve" once the line has risen. */
        html.es-anim #oc .oc-mark { animation: oc-mark 0.55s ease 1.05s both; }
        @keyframes oc-mark { from { background-color: transparent; color: inherit; } }
        .oc-lede { margin-top: 1.6rem; max-width: 36rem; font-size: clamp(1.08rem, 1.5vw, 1.2rem); line-height: 1.55; color: var(--oc-ink-2); }
        .oc-lede-2 { margin-top: 0.9rem; max-width: 36rem; font-size: 0.97rem; color: var(--oc-ink-3); }
        .oc-cta { display: flex; flex-wrap: wrap; gap: 0.9rem 1rem; margin-top: 1.9rem; }
        .oc-facts { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 1.9rem; font-size: 0.84rem; font-weight: 700; color: var(--oc-ink-2); }
        .oc-facts li { display: inline-flex; align-items: center; gap: 0.5rem; }
        .oc-facts li::before { content: ""; width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: var(--oc-gold); }

        /* The course card: the old syllabus, as the thing you would enrol on. */
        .oc-cc {
            position: relative;
            container-type: inline-size;
            overflow: hidden;
            background: var(--oc-card);
            border: 1px solid var(--oc-line);
            border-radius: 1.4rem;
            box-shadow: 0 34px 60px -38px var(--oc-shadow);
        }
        .oc-cc-top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.75rem 1.3rem 0.7rem;
            background: #0d5c63;
            color: #fbf8f1;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }
        .oc-cc-top span:last-child { color: #ffc94d; }
        /* An engraved band: two chains of small rings, half a link apart, so they interlock. */
        .oc-band {
            height: 1.05rem;
            background-color: #f1ead8;
            background-image:
                repeating-radial-gradient(circle at 50% 50%, transparent 0 2.3px, rgba(13, 92, 99, 0.62) 2.3px 3.1px),
                repeating-radial-gradient(circle at 50% 50%, transparent 0 2.3px, rgba(214, 143, 0, 0.7) 2.3px 3.1px);
            background-size: 24px 24px, 24px 24px;
            background-position: 0 50%, 12px 50%;
            border-bottom: 1px solid rgba(13, 92, 99, 0.5);
        }
        .oc-cc-body { padding: 1.35rem 1.35rem 1.3rem; }
        .oc-cc-title { font-family: var(--oc-display); font-size: clamp(1.5rem, 6.6cqi, 2.15rem); line-height: 1.1; color: var(--oc-ink); }
        .oc-cc-meta { margin-top: 0.55rem; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--oc-ink-3); }
        .oc-cc-note { margin-top: 1.2rem; padding: 0.9rem 1rem 0.95rem; border-radius: 0.9rem; background: var(--oc-sea-2); border: 1px solid var(--oc-line-2); }
        .oc-cc-note-tag { font-size: 0.66rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--oc-teal); }
        .oc-cc-note-big { margin-top: 0.2rem; font-weight: 700; color: var(--oc-ink); }
        .oc-cc-note p:last-child { margin-top: 0.25rem; font-size: 0.86rem; line-height: 1.5; color: var(--oc-ink-2); }
        .oc-cc-note b { color: var(--oc-ink); }
        .oc-cc-foot { margin-top: 1.1rem; padding-top: 0.95rem; border-top: 1px solid var(--oc-line-2); font-size: 0.86rem; color: var(--oc-ink-2); }

        /* The term, drawn: thirteen weeks, one of them hollow. */
        .oc-term { display: grid; grid-template-columns: repeat(13, minmax(0, 1fr)); gap: clamp(2px, 0.9cqi, 9px); }
        /* One column, held to the week's own width: left to size itself it took the width of the
           date under it, so on a narrow strip every square grew to its label and they ran together. */
        .oc-wk { display: grid; grid-template-columns: minmax(0, 1fr); justify-items: center; gap: 0.3rem; min-width: 0; }
        .oc-wk-w,
        .oc-wk-d { font-size: 0.62rem; font-weight: 700; letter-spacing: 0.04em; white-space: nowrap; color: var(--oc-ink-3); }
        .oc-wk-n {
            display: grid;
            place-items: center;
            width: 100%;
            aspect-ratio: 1;
            border-radius: 28%;
            background: var(--oc-fill);
            color: var(--oc-on-fill);
            font-weight: 700;
            font-size: clamp(0.56rem, 2.3cqi, 1.05rem);
            line-height: 1;
            transition: opacity 0.4s ease, scale 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
            transition-delay: calc(var(--i, 0) * 45ms + 150ms);
        }
        .oc-wk-off .oc-wk-n { background: transparent; color: var(--oc-ink-3); border: 1.5px dashed var(--oc-line-3); font-size: clamp(0.5rem, 1.5cqi, 0.72rem); }
        .oc-wk-off .oc-wk-d { color: var(--oc-ink); }
        html.es-anim #oc [data-reveal]:not(.is-revealed) .oc-wk-n { opacity: 0; scale: 0.5; }
        /* On the small strips only the two ends carry a label. */
        .oc-term-sm .oc-wk-w,
        .oc-term-sm .oc-wk-d { visibility: hidden; }
        .oc-term-sm .oc-wk:first-child .oc-wk-w,
        .oc-term-sm .oc-wk:first-child .oc-wk-d,
        .oc-term-sm .oc-wk:last-child .oc-wk-w,
        .oc-term-sm .oc-wk:last-child .oc-wk-d,
        .oc-term-sm .oc-wk-off .oc-wk-d { visibility: visible; }
        .oc-term-sm { margin-top: 1.3rem; }
        @container (max-width: 43.99rem) {
            .oc-term .oc-wk-w,
            .oc-term .oc-wk-d { visibility: hidden; }
            .oc-term .oc-wk:first-child .oc-wk-w,
            .oc-term .oc-wk:first-child .oc-wk-d,
            .oc-term .oc-wk:last-child .oc-wk-w,
            .oc-term .oc-wk:last-child .oc-wk-d,
            .oc-term .oc-wk-off .oc-wk-d { visibility: visible; }
            /* Under the page id, or the base rule further down outranks it and a phone keeps
               the wide padding. */
            #oc .oc-worked { padding-inline: 0.85rem; }
        }

        .oc-subjects {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            margin-top: clamp(2rem, 4.5vw, 3.25rem);
            padding-top: 1.25rem;
            border-top: 1px solid var(--oc-line);
        }
        .oc-subjects b { margin-inline-end: 0.5rem; font-size: 0.7rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--oc-ink-3); }
        .oc-subject { padding: 0.32rem 0.8rem; border-radius: 999px; border: 1px solid var(--oc-line); background: var(--oc-card); font-size: 0.84rem; color: var(--oc-ink-2); }

        /* ---------------------------------------------------------------
           The player: syllabus beside the lessons
           --------------------------------------------------------------- */
        .oc-course { position: relative; padding-top: clamp(1rem, 2.5vw, 2rem); }
        .oc-main { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; min-width: 0; padding-bottom: clamp(3.5rem, 7vw, 6rem); }
        @media (min-width: 1100px) {
            .oc-course { display: grid; grid-template-columns: 17.75rem minmax(0, 1fr); gap: 0 2.75rem; align-items: start; }
        }

        .oc-syllabus { position: sticky; z-index: 30; }
        .oc-syl-title { font-family: var(--oc-display); font-size: 1.3rem; line-height: 1.1; color: var(--oc-ink); }
        .oc-syl-title small { display: block; margin-top: 0.2rem; font-family: var(--oc-text); font-size: 0.74rem; font-weight: 400; color: var(--oc-ink-3); }
        .oc-ring {
            position: relative;
            flex: none;
            display: grid;
            place-items: center;
            width: 4rem;
            aspect-ratio: 1;
            --oc-ang: 0deg;
        }
        .oc-ring::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: conic-gradient(var(--oc-gold) var(--oc-ang), var(--oc-line) 0);
            -webkit-mask-image: radial-gradient(circle, transparent 0 57%, #000 59%);
            mask-image: radial-gradient(circle, transparent 0 57%, #000 59%);
        }
        .oc-ring-num { font-weight: 700; font-size: 0.92rem; font-variant-numeric: tabular-nums; color: var(--oc-ink); }
        .oc-ring-num::after { content: "12"; }
        .oc-syl a.es-dot {
            display: grid;
            grid-template-columns: 1.1rem 1.75rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.5rem;
            padding: 0.42rem 0.55rem;
            border-radius: 0.65rem;
            font-size: 0.86rem;
            line-height: 1.3;
            color: var(--oc-ink-2);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .oc-syl a.es-dot:hover { background: var(--oc-sea-2); color: var(--oc-ink); }
        .oc-syl a.es-dot.is-active { background: var(--oc-sea); color: var(--oc-ink); font-weight: 700; }
        .oc-syl-no { font-size: 0.74rem; font-variant-numeric: tabular-nums; color: var(--oc-ink-3); }
        .oc-syl-no:empty { padding: 0; }
        .oc-syl-min { font-size: 0.7rem; font-weight: 400; color: var(--oc-ink-3); white-space: nowrap; }
        .oc-syl a.is-active .oc-syl-no,
        .oc-syl a.is-active .oc-syl-min { color: var(--oc-ink-2); }
        .oc-syl-name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .oc-mod { margin-top: 0.85rem; padding: 0.2rem 0.55rem; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.13em; text-transform: uppercase; color: var(--oc-ink-3); }
        .oc-mod b { color: var(--oc-teal); margin-inline-end: 0.35rem; }
        /* The certificate's row: a small pleated seal where the tick would be. */
        .oc-syl-seal {
            width: 1.1rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, var(--oc-card) 0 28%, transparent 30%), repeating-conic-gradient(#ffb703 0 15deg, #d99500 15deg 30deg);
        }

        /* A tick: an empty ring that fills once the lesson has been read. */
        .oc-tick {
            position: relative;
            display: block;
            width: 1.1rem;
            aspect-ratio: 1;
            border-radius: 50%;
            border: 1.5px solid var(--oc-line-3);
        }
        .oc-tick::after {
            content: "";
            position: absolute;
            left: 34%;
            top: 16%;
            width: 30%;
            height: 52%;
            border: solid var(--oc-on-fill);
            border-width: 0 2px 2px 0;
            rotate: 45deg;
            opacity: 0;
        }

        @media (min-width: 1100px) {
            .oc-syllabus {
                top: 5.25rem;
                max-height: calc(100svh - 6.5rem);
                overflow-y: auto;
                scrollbar-width: thin;
                padding: 1.1rem 0.85rem 0.9rem;
                background: var(--oc-card);
                border: 1px solid var(--oc-line);
                border-radius: 1.3rem;
                box-shadow: 0 24px 44px -36px var(--oc-shadow);
            }
            .oc-progress { display: flex; align-items: center; gap: 0.9rem; padding: 0 0.4rem 0.9rem; border-bottom: 1px solid var(--oc-line-2); }
        }

        /* Below that the syllabus folds into one slim bar under the site header:
           the ring, the lesson you are in, and a line that fills. */
        @media (max-width: 1099.98px) {
            .oc-syllabus {
                top: 4rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                min-height: 2.9rem;
                margin-inline: -1rem;
                margin-bottom: 1.25rem;
                padding: 0.45rem 1rem;
                background: var(--oc-bg);
                border-bottom: 1px solid var(--oc-line);
            }
            .oc-syllabus::after {
                content: "";
                position: absolute;
                inset: auto 0 -1px 0;
                height: 3px;
                background: var(--oc-gold);
                transform-origin: 0 50%;
                scale: 0 1;
            }
            .oc-progress { display: contents; }
            .oc-ring { width: 1.65rem; }
            .oc-ring-num { display: none; }
            .oc-syl-title { font-family: var(--oc-text); font-size: 0.9rem; font-weight: 700; }
            .oc-syl-title small { display: inline; margin: 0 0 0 0.5rem; }
            .oc-syllabus:has(.is-active) .oc-syl-title { display: none; }
            .oc-syl { flex: 1; min-width: 0; }
            .oc-syl li { display: none; }
            .oc-syl li:has(> a.is-active) { display: block; }
            .oc-syl a.es-dot,
            .oc-syl a.es-dot.is-active { grid-template-columns: auto minmax(0, 1fr) auto; padding: 0.2rem 0; background: none; font-size: 0.9rem; }
            .oc-syl a.es-dot .oc-tick,
            .oc-syl a.es-dot .oc-syl-seal { display: none; }
            .oc-syl-no { padding: 0.05rem 0.5rem; border-radius: 999px; background: #ffb703; color: #16242b; font-weight: 700; }
            .oc-syl a.is-active .oc-syl-no { color: #16242b; }
        }

        /* Module bands in the lesson column */
        .oc-module { display: flex; align-items: center; gap: 1rem; margin-top: 1.25rem; }
        .oc-module:first-child { margin-top: 0; }
        .oc-module-no { flex: none; padding: 0.22rem 0.7rem; border-radius: 999px; background: var(--oc-ink); color: var(--oc-bg); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; }
        .oc-module-name { flex: none; font-family: var(--oc-display); font-size: 1.5rem; line-height: 1; color: var(--oc-ink); }
        .oc-module i {
            flex: 1;
            height: 1rem;
            background-image:
                repeating-radial-gradient(circle at 50% 50%, transparent 0 2.3px, var(--oc-eng-2) 2.3px 3.1px),
                repeating-radial-gradient(circle at 50% 50%, transparent 0 2.3px, var(--oc-eng-2) 2.3px 3.1px);
            background-size: 24px 24px, 24px 24px;
            background-position: 0 50%, 12px 50%;
            -webkit-mask-image: linear-gradient(90deg, #000 20%, transparent);
            mask-image: linear-gradient(90deg, #000 20%, transparent);
        }

        /* ---------------------------------------------------------------
           A lesson
           --------------------------------------------------------------- */
        .oc-lesson {
            position: relative;
            container-type: inline-size;
            scroll-margin-top: 5.25rem;
            padding: clamp(1.35rem, 3.4vw, 2.75rem);
            background: var(--oc-card);
            border: 1px solid var(--oc-line);
            border-radius: 1.6rem;
        }
        @media (max-width: 1099.98px) { .oc-lesson { scroll-margin-top: 7.5rem; } }
        .oc-lhead {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 0.75rem;
            margin-bottom: 1.15rem;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.11em;
            text-transform: uppercase;
            color: var(--oc-ink-3);
        }
        .oc-lno {
            display: inline-grid;
            place-items: center;
            min-width: 2.5rem;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            background: #ffb703;
            color: #16242b;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.02em;
            font-size: 0.82rem;
        }
        .oc-ldur { margin-inline-start: auto; display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 400; letter-spacing: 0.06em; }
        .oc-ldur::before { content: ""; width: 0.8rem; aspect-ratio: 1; border-radius: 50%; border: 1.5px solid currentColor; background: conic-gradient(currentColor 0 90deg, transparent 0); }
        .oc-h2 {
            font-family: var(--oc-display);
            font-weight: 400;
            font-size: clamp(1.8rem, 5.2cqi, 2.9rem);
            line-height: 1.08;
            letter-spacing: -0.008em;
            text-wrap: balance;
            color: var(--oc-ink);
        }
        .oc-h2 em {
            font-style: normal;
            color: var(--oc-teal);
            text-decoration: underline;
            text-decoration-color: var(--oc-gold);
            text-decoration-thickness: 0.09em;
            text-underline-offset: 0.16em;
        }
        .oc-lead { margin-top: 0.95rem; max-width: 46rem; font-size: 1.1rem; line-height: 1.58; color: var(--oc-ink-2); }
        .oc-lead a { color: var(--oc-teal); font-weight: 700; text-decoration: underline; text-decoration-color: var(--oc-gold); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        .oc-lfoot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem 1.5rem;
            margin-top: clamp(1.5rem, 3vw, 2.25rem);
            padding-top: 1.1rem;
            border-top: 1px solid var(--oc-line-2);
            font-size: 0.86rem;
            color: var(--oc-ink-3);
        }
        .oc-ldone { display: inline-flex; align-items: center; gap: 0.6rem; }
        /* The padding is handed back as margin: a 24px target, nothing moved. */
        .oc-lfoot a { display: inline-flex; align-items: center; gap: 0.5rem; margin-block: -0.1rem; padding-block: 0.1rem; color: var(--oc-ink-2); transition: gap 0.2s ease, color 0.2s ease; }
        .oc-lfoot a b { color: var(--oc-ink); }
        .oc-lfoot a:hover { gap: 0.8rem; color: var(--oc-teal); }
        .oc-lfoot a .oc-icon { width: 1rem; height: 1rem; }

        /* Parts a lesson is built from */
        .oc-trio { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: 1.75rem; }
        .oc-duo { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: 1.25rem; }
        @container (min-width: 44rem) {
            .oc-trio { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .oc-duo { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .oc-mini { display: flex; flex-direction: column; padding: 1.2rem 1.25rem 1.25rem; border: 1px solid var(--oc-line); border-radius: 1.1rem; background: var(--oc-bg); }
        .oc-mini-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem 0.6rem; margin-bottom: 0.5rem; }
        .oc-mini h3 { font-size: 1.04rem; font-weight: 700; line-height: 1.3; color: var(--oc-ink); }
        .oc-mini p { font-size: 0.93rem; line-height: 1.58; color: var(--oc-ink-2); }
        .oc-mini-foot { margin-top: auto; padding-top: 1.1rem; }
        .oc-mini-foot p { font-size: 0.82rem; color: var(--oc-ink-3); }
        /* A link standing on its own is a target: as a block it is as tall as its line. */
        .oc-mini-foot > .oc-link { display: inline-block; }

        .oc-worked {
            position: relative;
            margin-top: 2rem;
            padding: 1.6rem 1.25rem 1.3rem;
            border: 1.5px dashed var(--oc-line-3);
            border-radius: 1.2rem;
            background: var(--oc-bg);
        }
        .oc-worked-tag {
            position: absolute;
            top: -0.72rem;
            left: 1.1rem;
            padding: 0.18rem 0.7rem;
            border-radius: 999px;
            background: var(--oc-ink);
            color: var(--oc-bg);
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.5;
        }
        .oc-worked-cap { margin-top: 1.1rem; font-size: 0.92rem; color: var(--oc-ink-2); }
        .oc-worked-cap a { display: inline-flex; align-items: center; gap: 0.3rem; margin-inline-start: 0.35rem; color: var(--oc-teal); font-weight: 700; border-bottom: 2px solid var(--oc-gold); transition: gap 0.2s ease; }
        .oc-worked-cap a:hover { gap: 0.55rem; }
        .oc-worked-cap a .oc-icon { width: 0.95rem; height: 0.95rem; }

        .oc-note { margin-top: 1.25rem; padding: 1.25rem 1.35rem 1.3rem; border-radius: 1.1rem; background: var(--oc-sea-2); border: 1px solid var(--oc-line-2); }
        .oc-note-tag { display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: 0.5rem; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--oc-teal); }
        .oc-note-tag::before { content: "i"; display: grid; place-items: center; width: 1.05rem; aspect-ratio: 1; border-radius: 50%; background: var(--oc-fill); color: var(--oc-on-fill); font-family: Georgia, serif; font-style: italic; font-size: 0.75rem; letter-spacing: 0; text-transform: none; }
        .oc-note h3 { font-size: 1.08rem; font-weight: 700; color: var(--oc-ink); }
        .oc-note p { margin-top: 0.4rem; font-size: 0.95rem; color: var(--oc-ink-2); }
        .oc-smallprint { margin-top: 1.25rem; font-size: 0.9rem; color: var(--oc-ink-2); }

        /* Lesson 1.1 is the featured one: teal in both modes, with its own inks. */
        #oc .oc-lesson-feature {
            --oc-ink: #fbf8f1;
            --oc-ink-2: #dcece9;
            --oc-ink-3: #cfe5e1;
            --oc-line: rgba(251, 248, 241, 0.22);
            --oc-line-2: rgba(251, 248, 241, 0.14);
            --oc-line-3: rgba(251, 248, 241, 0.45);
            --oc-teal: #ffc94d;
            --oc-fill: #ffb703;
            --oc-on-fill: #16242b;
            --oc-bg: rgba(4, 44, 49, 0.42);
            --oc-gold: #ffb703;
            overflow: hidden;
            background-color: #0d5c63;
            background-image: radial-gradient(120% 90% at 100% 0%, #10686f 0%, rgba(13, 92, 99, 0) 60%);
            border-color: transparent;
            color: #fbf8f1;
        }
        .oc-lesson-feature::before {
            content: "";
            position: absolute;
            left: -12rem;
            bottom: -16rem;
            width: 34rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                repeating-conic-gradient(from 0deg, rgba(251, 248, 241, 0.07) 0 0.6deg, transparent 0.6deg 3deg),
                repeating-radial-gradient(circle, transparent 0 8px, rgba(251, 248, 241, 0.07) 8px 9px);
            -webkit-mask-image: radial-gradient(circle, transparent 0 22%, #000 22.5% 49.5%, transparent 50%);
            mask-image: radial-gradient(circle, transparent 0 22%, #000 22.5% 49.5%, transparent 50%);
            pointer-events: none;
        }
        .oc-lesson-feature > * { position: relative; }
        .oc-lesson-feature .oc-worked-tag { background: #ffb703; color: #16242b; }
        .oc-mini h3.oc-figure { display: block; margin: 0.35rem 0 0.5rem; font-family: var(--oc-display); font-weight: 400; font-size: 3rem; line-height: 1; color: #ffc94d; }
        .oc-mini h3.oc-figure small { font-family: var(--oc-text); font-size: 1.04rem; font-weight: 700; color: var(--oc-ink); }
        .oc-mini h3.oc-figure-word { font-size: 1.85rem; line-height: 1.62; }
        #oc .oc-lesson-feature a:focus-visible { outline-color: #ffb703; }

        /* Lesson 1.2: three settings, each with the control it is */
        .oc-ctl { display: flex; flex-wrap: wrap; align-items: center; gap: 0.3rem; margin-top: auto; padding-top: 1.1rem; font-size: 0.7rem; font-weight: 700; }
        .oc-ctl span { display: grid; place-items: center; min-width: 1.7rem; height: 1.7rem; padding-inline: 0.45rem; border-radius: 0.5rem; border: 1px solid var(--oc-line); color: var(--oc-ink-3); white-space: nowrap; }
        .oc-ctl span.is-on { background: var(--oc-fill); border-color: transparent; color: var(--oc-on-fill); }
        .oc-ctl span.is-out { border-style: dashed; border-color: var(--oc-line-3); text-decoration: line-through; }
        .oc-ctl b { margin-inline-start: 0.4rem; color: var(--oc-ink); }

        /* Lesson 2.1: the register */
        .oc-reg-wrap { margin-top: 1.9rem; }
        .oc-reg { width: 100%; border-collapse: collapse; font-size: 0.9rem; font-variant-numeric: tabular-nums; }
        .oc-reg th,
        .oc-reg td { padding: 0.5rem 0.55rem; text-align: start; vertical-align: middle; border-top: 1px solid var(--oc-line-2); }
        .oc-reg thead th { padding-top: 0; border-top: 0; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--oc-ink-3); }
        .oc-reg tbody th { font-weight: 700; color: var(--oc-ink); }
        .oc-reg td { color: var(--oc-ink-2); }
        .oc-reg .oc-reg-end { text-align: end; white-space: nowrap; }
        .oc-reg .oc-reg-bar { width: 48%; }
        .oc-reg b { color: var(--oc-ink); }
        .oc-reg-off th,
        .oc-reg-off td { color: var(--oc-ink-3); border-top-style: dashed; border-top-color: var(--oc-line-3); }
        .oc-reg-off + tr th,
        .oc-reg-off + tr td { border-top-style: dashed; border-top-color: var(--oc-line-3); }
        .oc-fill-track { height: 0.55rem; border-radius: 999px; background: var(--oc-line-2); overflow: hidden; }
        .oc-fill-bar { height: 100%; border-radius: inherit; background: var(--oc-fill); transform-origin: 0 50%; transition: scale 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.2s; }
        [dir="rtl"] .oc-fill-bar { transform-origin: 100% 50%; }
        .oc-fill-bar-full { background: #ffb703; }
        html.es-anim #oc [data-reveal]:not(.is-revealed) .oc-fill-bar { scale: 0 1; }
        .oc-full { display: inline-block; padding: 0.05rem 0.55rem; border-radius: 999px; background: #ffb703; color: #16242b; font-weight: 700; font-size: 0.78rem; }
        @container (max-width: 33rem) {
            .oc-reg .oc-reg-bar { display: none; }
            #oc .oc-reg th,
            #oc .oc-reg td { padding-inline: 0.35rem; }
        }
        /* A date stays on one line wherever the three columns have room for it: on a 375px
           phone "Sep 15" broke in two while "Oct 6" did not. */
        @container (min-width: 17rem) {
            .oc-reg td:nth-child(2) { white-space: nowrap; }
        }

        /* Lesson 2.2: the class card */
        .oc-split { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.75rem; margin-top: 1.75rem; }
        @container (min-width: 50rem) {
            .oc-split { grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr); gap: 2.5rem; align-items: start; }
        }
        .oc-checks { display: grid; gap: 0.85rem; }
        .oc-checks li { position: relative; padding-inline-start: 1.9rem; color: var(--oc-ink-2); font-size: 0.97rem; }
        .oc-checks li::before { content: ""; position: absolute; inset-inline-start: 0; top: 0.2rem; width: 1.15rem; aspect-ratio: 1; border-radius: 50%; background: var(--oc-fill); }
        .oc-checks li::after { content: ""; position: absolute; inset-inline-start: 0.4rem; top: 0.38rem; width: 0.33rem; height: 0.6rem; border: solid var(--oc-on-fill); border-width: 0 2px 2px 0; rotate: 45deg; }
        .oc-ids { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; }
        @container (min-width: 40rem) { .oc-ids { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; } }
        .oc-id {
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            border-radius: 1rem;
            background: var(--oc-card);
            border: 1px solid var(--oc-line);
            box-shadow: 0 22px 36px -26px var(--oc-shadow);
        }
        .oc-id-top { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; padding: 0.65rem 1.1rem 0.6rem; background: #0d5c63; color: #fbf8f1; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; }
        .oc-id-top span:last-child { font-size: 0.85rem; letter-spacing: 0.02em; color: #ffc94d; }
        .oc-id-body { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 0.9rem; padding: 1.1rem 1.1rem 0; }
        .oc-id-badge { display: grid; place-items: center; width: 3.4rem; aspect-ratio: 1; border-radius: 0.8rem; background: #ffb703; color: #16242b; font-family: var(--oc-display); font-size: 1.7rem; line-height: 1; }
        .oc-id-name { font-family: var(--oc-display); font-size: 1.5rem; line-height: 1.15; color: var(--oc-ink); }
        .oc-id-sub { font-size: 0.88rem; line-height: 1.35; color: var(--oc-ink-3); }
        .oc-id-rest { padding: 1rem 1.1rem 1rem; }
        .oc-cells { display: grid; grid-template-columns: repeat(10, minmax(0, 1fr)); gap: 4px; }
        .oc-cell { display: grid; place-items: center; aspect-ratio: 1; border-radius: 0.3rem; border: 1px solid var(--oc-line-3); font-size: clamp(0.56rem, 1.4cqi, 0.76rem); font-weight: 700; color: var(--oc-ink-3); }
        .oc-cell.is-used { background: var(--oc-fill); border-color: transparent; color: var(--oc-on-fill); }
        .oc-strip { height: clamp(1.45rem, 3.4cqi, 2.1rem); border-radius: 0.3rem; background: repeating-linear-gradient(135deg, var(--oc-fill) 0 7px, var(--oc-fill-2) 7px 14px); }
        .oc-id-count { margin-top: 0.6rem; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.13em; text-transform: uppercase; color: var(--oc-teal); }
        .oc-id-fine { margin-top: 0.7rem; padding-top: 0.7rem; border-top: 1px solid var(--oc-line-2); font-size: 0.84rem; line-height: 1.45; color: var(--oc-ink-3); }
        .oc-id-code { margin-top: auto; height: 1.5rem; margin-inline: 1.1rem; margin-bottom: 1rem; opacity: 0.75; background: repeating-linear-gradient(90deg, var(--oc-ink) 0 2px, transparent 2px 5px, var(--oc-ink) 5px 6px, transparent 6px 8px, var(--oc-ink) 8px 11px, transparent 11px 13px, var(--oc-ink) 13px 14px, transparent 14px 18px); }
        .oc-seats { padding: 1.1rem 1.15rem 1.15rem; border: 1px solid var(--oc-line); border-radius: 1rem; background: var(--oc-bg); }
        .oc-seats h3 { font-size: 1.04rem; font-weight: 700; line-height: 1.3; color: var(--oc-ink); }
        .oc-seat { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: baseline; gap: 0.9rem; padding-block: 0.4rem; border-top: 1px solid var(--oc-line-2); font-size: 0.93rem; }
        .oc-seat:first-of-type { border-top: 0; }
        .oc-seat span:first-child { font-weight: 700; color: var(--oc-ink); }
        .oc-seat span:nth-child(2) { font-size: 0.8rem; color: var(--oc-ink-3); }
        .oc-seat span:last-child { min-width: 2.6rem; text-align: end; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--oc-ink); }
        .oc-seats > p { margin-top: 0.8rem; padding-top: 0.8rem; border-top: 1px solid var(--oc-line-2); font-size: 0.84rem; line-height: 1.55; color: var(--oc-ink-2); }
        .oc-seats > p b { color: var(--oc-teal); }

        /* Lesson 2.3: the room */
        .oc-join { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; align-items: center; }
        @container (min-width: 44rem) { .oc-join { grid-template-columns: minmax(0, 0.9fr) auto minmax(0, 1.1fr); gap: 1.25rem; } }
        .oc-field { display: flex; align-items: center; gap: 0.6rem; padding: 0.7rem 0.9rem; border-radius: 0.8rem; border: 1px solid var(--oc-line-3); background: var(--oc-card); font-size: 0.9rem; color: var(--oc-ink-3); min-width: 0; }
        .oc-field i { flex: none; width: 0.6rem; aspect-ratio: 1; border-radius: 50%; background: var(--oc-gold); }
        .oc-field span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .oc-field-solid { color: var(--oc-ink); font-weight: 700; }
        /* The address is the point of this field, so it is set to fit: at the size of the others
           it was cut to "your-classes.eventsche..." in every three-column row. */
        .oc-field-solid { container-type: inline-size; }
        .oc-field-solid span { font-size: clamp(0.68rem, 5.5cqi, 0.9rem); }
        .oc-join-arrow { justify-self: center; color: var(--oc-ink-3); }
        .oc-join-arrow .oc-icon { width: 1.4rem; height: 1.4rem; }
        @container (max-width: 43.99rem) { .oc-join-arrow { rotate: 90deg; } }
        .oc-sessions { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 3px; }
        .oc-sessions span { display: grid; place-items: center; aspect-ratio: 1; border-radius: 28%; background: var(--oc-fill); color: var(--oc-on-fill); font-size: clamp(0.55rem, 1.5cqi, 0.8rem); font-weight: 700; }

        /* Lesson 3.1: the handouts */
        .oc-handouts { display: grid; grid-template-columns: minmax(0, 1fr); margin-top: 1.5rem; border-top: 1px solid var(--oc-line); }
        @container (min-width: 46rem) {
            .oc-handouts { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 2.5rem; }
        }
        .oc-handout { padding-block: 1.15rem 1.2rem; border-bottom: 1px solid var(--oc-line); }
        .oc-handout h3 { font-size: 1.02rem; font-weight: 700; line-height: 1.3; color: var(--oc-ink); }
        .oc-handout p { margin-top: 0.4rem; font-size: 0.93rem; line-height: 1.58; color: var(--oc-ink-2); }

        /* Lesson 3.2: the catalogue */
        .oc-catalogue { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: 1.75rem; }
        @container (min-width: 32rem) { .oc-catalogue { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @container (min-width: 52rem) { .oc-catalogue { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .oc-tile { display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--oc-line); border-radius: 1.1rem; background: var(--oc-bg); transition: translate 0.25s ease, box-shadow 0.25s ease; }
        .oc-tile:hover { translate: 0 -4px; box-shadow: 0 22px 34px -26px var(--oc-shadow); }
        .oc-cover { position: relative; height: 5.6rem; background-color: var(--c, #0d5c63); background-image: var(--p, none); background-size: var(--s, auto); }
        .oc-cover span { position: absolute; left: 0.8rem; bottom: 0.7rem; padding: 0.15rem 0.55rem; border-radius: 999px; background: #fbf8f1; color: #16242b; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.12em; }
        .oc-tile-body { display: flex; flex-direction: column; flex: 1; padding: 1rem 1.05rem 1.1rem; }
        .oc-tile h3 { font-size: 1.04rem; font-weight: 700; line-height: 1.3; color: var(--oc-ink); }
        .oc-tile p { margin-top: 0.4rem; font-size: 0.9rem; line-height: 1.55; color: var(--oc-ink-2); }
        .oc-tile a { margin-top: auto; padding-top: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.88rem; font-weight: 700; color: var(--oc-teal); }
        .oc-tile a .oc-icon { width: 0.95rem; height: 0.95rem; transition: translate 0.2s ease; }
        .oc-tile a:hover .oc-icon { translate: 3px 0; }

        /* Lesson 3.3: three steps on a line */
        .oc-steps { counter-reset: oc-step; display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; margin-top: 1.9rem; }
        .oc-step { position: relative; padding-inline-start: 3.6rem; }
        .oc-step-no { position: absolute; inset-inline-start: 0; top: 0; display: grid; place-items: center; width: 2.6rem; aspect-ratio: 1; border-radius: 50%; background: var(--oc-fill); color: var(--oc-on-fill); font-family: var(--oc-display); font-size: 1.15rem; }
        .oc-step h3 { font-size: 1.08rem; font-weight: 700; color: var(--oc-ink); }
        .oc-step p { margin-top: 0.35rem; font-size: 0.93rem; line-height: 1.58; color: var(--oc-ink-2); }
        @container (min-width: 44rem) {
            .oc-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.75rem; }
            .oc-step { padding-inline-start: 0; padding-top: 3.6rem; }
            /* The line the three stops sit on. */
            .oc-step::before { content: ""; position: absolute; top: 1.25rem; inset-inline: 2.6rem -1.75rem; height: 2px; background: repeating-linear-gradient(90deg, var(--oc-line-3) 0 6px, transparent 6px 12px); }
            .oc-step:last-child::before { display: none; }
        }

        /* Lesson 3.4 and 4.2: reading list and electives */
        .oc-reading { margin-top: 1.5rem; border-top: 1px solid var(--oc-line); }
        .oc-read {
            display: grid;
            grid-template-columns: 2.2rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.9rem;
            padding: 1rem 0.6rem;
            border-bottom: 1px solid var(--oc-line);
            border-radius: 0.6rem;
            transition: background-color 0.2s ease, padding 0.25s ease;
        }
        .oc-read:hover { background: var(--oc-sea-2); padding-inline: 1rem 0.6rem; }
        .oc-read-no { font-family: var(--oc-display); font-size: 1.35rem; line-height: 1; color: var(--oc-teal); }
        .oc-read strong { display: block; font-weight: 700; color: var(--oc-ink); }
        .oc-read small { display: block; margin-top: 0.1rem; font-size: 0.9rem; color: var(--oc-ink-2); }
        .oc-read .oc-icon { color: var(--oc-ink-3); transition: translate 0.2s ease, color 0.2s ease; }
        .oc-read:hover .oc-icon { translate: 4px 0; color: var(--oc-teal); }
        .oc-more { display: inline-flex; align-items: center; gap: 0.45rem; margin-top: 1.25rem; color: var(--oc-teal); font-weight: 700; border-bottom: 2px solid var(--oc-gold); padding-bottom: 0.1rem; transition: gap 0.2s ease; }
        .oc-more:hover { gap: 0.75rem; }
        .oc-more .oc-icon { width: 1rem; height: 1rem; }
        .oc-electives { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem; margin-top: 1.5rem; }
        @container (min-width: 48rem) { .oc-electives { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .oc-elective { position: relative; overflow: hidden; display: flex; flex-direction: column; min-height: 9rem; padding: 1.6rem 1rem 1rem; border: 1px solid var(--oc-line); border-radius: 1.1rem; background: var(--oc-bg); transition: translate 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease; }
        .oc-elective::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 0.7rem;
            background-color: #f1ead8;
            background-image:
                repeating-radial-gradient(circle at 50% 50%, transparent 0 2.3px, rgba(13, 92, 99, 0.62) 2.3px 3.1px),
                repeating-radial-gradient(circle at 50% 50%, transparent 0 2.3px, rgba(214, 143, 0, 0.7) 2.3px 3.1px);
            background-size: 24px 24px, 24px 24px;
            background-position: 0 50%, 12px 50%;
        }
        .oc-elective:hover { translate: 0 -4px; border-color: var(--oc-teal); box-shadow: 0 20px 30px -24px var(--oc-shadow); }
        .oc-elective small { font-size: 0.64rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--oc-ink-3); }
        .oc-elective strong { margin-top: 0.35rem; font-family: var(--oc-display); font-weight: 400; font-size: 1.3rem; line-height: 1.12; color: var(--oc-ink); }
        /* Two to a row on a narrow phone, "Conferences" was wider than its card. */
        @container (max-width: 19rem) { #oc .oc-elective strong { font-size: 1.1rem; } }
        .oc-elective span { margin-top: auto; padding-top: 0.8rem; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.84rem; font-weight: 700; color: var(--oc-teal); }
        .oc-elective span .oc-icon { width: 0.9rem; height: 0.9rem; transition: translate 0.2s ease; }
        .oc-elective:hover span .oc-icon { translate: 3px 0; }

        /* Lesson 4.1: tuition. The shared plan band keeps its words and prices. */
        #oc .oc-plans > section { background: transparent; padding: 0; }
        #oc .oc-plans > section > div { max-width: none; padding-inline: 0; }
        #oc .oc-plans .text-center { text-align: start; margin-inline: 0; max-width: 44rem; }
        #oc .oc-plans h2 { font-family: var(--oc-display); font-weight: 400; font-size: clamp(1.8rem, 5.2cqi, 2.9rem); line-height: 1.08; letter-spacing: -0.008em; color: var(--oc-ink); }
        #oc .oc-plans h2 + p { font-size: 1.05rem; color: var(--oc-ink-2); }
        #oc .oc-plans .grid { grid-template-columns: minmax(0, 1fr); }
        @container (min-width: 46rem) { #oc .oc-plans .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        #oc .oc-plans .grid > div { background: var(--oc-bg); border: 1px solid var(--oc-line); border-radius: 1.1rem; box-shadow: none; }
        #oc .oc-plans .grid > div.border-blue-300 { border: 2px solid var(--oc-fill); background: var(--oc-sea-2); }
        #oc .oc-plans .grid > div span,
        #oc .oc-plans .grid > div p,
        #oc .oc-plans .grid > div li { color: var(--oc-ink-2); }
        #oc .oc-plans .grid > div .text-3xl { font-family: var(--oc-display); font-weight: 400; font-size: 2.4rem; color: var(--oc-ink); }
        #oc .oc-plans .grid > div .uppercase { color: var(--oc-ink); }
        #oc .oc-plans .grid > div .rounded-full { background: #ffb703; color: #16242b; }
        #oc .oc-plans .grid > div svg { color: var(--oc-teal); }
        #oc .oc-plans .sm\:flex-row { justify-content: flex-start; }
        #oc .oc-plans a.font-medium { color: var(--oc-teal); }
        #oc .oc-plans a.rounded-2xl { background: var(--oc-btn-bg); color: var(--oc-btn-ink); border-radius: 999px; box-shadow: 0 12px 24px -14px var(--oc-btn-shadow); }

        /* Lesson 4.3: check your understanding */
        .oc-quiz { counter-reset: oc-q; display: grid; gap: 0.7rem; margin-top: 1.75rem; }
        .oc-q { border: 1px solid var(--oc-line); border-radius: 1rem; background: var(--oc-bg); transition: border-color 0.2s ease, background-color 0.2s ease; }
        .oc-q[open] { border-color: var(--oc-fill); background: var(--oc-sea-2); }
        .oc-q summary { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 0.85rem; padding: 0.95rem 1.05rem; cursor: pointer; }
        .oc-q-no { display: grid; place-items: center; min-width: 2.15rem; height: 2.15rem; border-radius: 50%; border: 1.5px solid var(--oc-line-3); font-size: 0.74rem; font-weight: 700; color: var(--oc-ink-2); }
        .oc-q[open] .oc-q-no { background: var(--oc-fill); border-color: transparent; color: var(--oc-on-fill); }
        .oc-q h3 { font-size: 1.02rem; font-weight: 700; line-height: 1.35; color: var(--oc-ink); }
        .oc-q-show { padding: 0.25rem 0.75rem; border-radius: 999px; border: 1px solid var(--oc-line-3); font-size: 0.72rem; font-weight: 700; color: var(--oc-ink-2); white-space: nowrap; }
        .oc-q-show::before { content: "Show answer"; }
        .oc-q[open] .oc-q-show::before { content: "Hide"; }
        .oc-q summary:hover .oc-q-show { border-color: var(--oc-teal); color: var(--oc-teal); }
        .oc-q > p { padding: 0 1.05rem 1.2rem 4.05rem; max-width: 50rem; font-size: 0.96rem; line-height: 1.65; color: var(--oc-ink-2); }
        @container (max-width: 34rem) {
            .oc-q-show { padding: 0; border: 0; width: 1.1rem; height: 1.1rem; position: relative; }
            .oc-q-show::before { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; }
            .oc-q-show::after { content: ""; position: absolute; inset: 0 calc(50% - 1px) 0 auto; width: 2px; background: currentColor; transition: opacity 0.2s ease; }
            .oc-q[open] .oc-q-show::before { content: ""; }
            .oc-q[open] .oc-q-show::after { opacity: 0; }
            .oc-q > p { padding-inline-start: 1.05rem; }
        }

        /* ---------------------------------------------------------------
           Graduation: the certificate. Teal hall and ivory paper in both
           modes, so every colour in here is literal.
           --------------------------------------------------------------- */
        .oc-grad {
            position: relative;
            overflow: clip;
            scroll-margin-top: 4rem;
            padding-block: clamp(4rem, 9vw, 7rem) clamp(6rem, 11vw, 9rem);
            background-color: #0a474d;
            background-image: radial-gradient(90% 70% at 50% 0%, #11686f 0%, rgba(10, 71, 77, 0) 70%);
            color: #fbf8f1;
        }
        .oc-grad::before,
        .oc-grad::after {
            content: "";
            position: absolute;
            width: 44rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                repeating-conic-gradient(from 0deg, rgba(251, 248, 241, 0.06) 0 0.6deg, transparent 0.6deg 3deg),
                repeating-radial-gradient(circle, transparent 0 8px, rgba(251, 248, 241, 0.06) 8px 9px);
            -webkit-mask-image: radial-gradient(circle, transparent 0 22%, #000 22.5% 49.5%, transparent 50%);
            mask-image: radial-gradient(circle, transparent 0 22%, #000 22.5% 49.5%, transparent 50%);
            pointer-events: none;
        }
        .oc-grad::before { left: -18rem; top: -14rem; }
        .oc-grad::after { right: -18rem; bottom: -18rem; }
        .oc-grad-in { position: relative; z-index: 1; text-align: center; }
        .oc-grad-tag { display: inline-flex; padding: 0.3rem 0.9rem; border-radius: 999px; background: #ffb703; color: #16242b; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; }
        .oc-grad-h2 { margin: 1.4rem auto 0; max-width: 46rem; font-family: var(--oc-display); font-weight: 400; font-size: clamp(2.3rem, 6vw, 4.2rem); line-height: 1.04; text-wrap: balance; color: #fbf8f1; }
        .oc-grad-h2 span { color: #ffc94d; }
        .oc-grad-sub { margin: 1.3rem auto 0; max-width: 40rem; font-size: 1.08rem; color: #d7e9e5; }

        .oc-cert {
            position: relative;
            container-type: inline-size;
            width: min(100%, 54rem);
            margin: clamp(2.5rem, 5vw, 3.75rem) auto 0;
            padding: clamp(0.7rem, 2vw, 1.4rem);
            /* Guilloche: rings struck from the four corners and from the middle, fine
               enough that where they cross they weave. */
            background-color: #f1ead8;
            background-image:
                repeating-radial-gradient(circle at 0 0, transparent 0 4px, rgba(13, 92, 99, 0.55) 4px 4.8px),
                repeating-radial-gradient(circle at 100% 0, transparent 0 4px, rgba(13, 92, 99, 0.55) 4px 4.8px),
                repeating-radial-gradient(circle at 0 100%, transparent 0 4px, rgba(13, 92, 99, 0.55) 4px 4.8px),
                repeating-radial-gradient(circle at 100% 100%, transparent 0 4px, rgba(13, 92, 99, 0.55) 4px 4.8px),
                repeating-radial-gradient(circle at 50% 50%, transparent 0 6px, rgba(226, 150, 0, 0.6) 6px 6.9px);
            border-radius: 0.5rem;
            box-shadow: inset 0 0 0 2px #0d5c63, 0 40px 70px -40px rgba(0, 0, 0, 0.75);
            color: #16242b;
            text-align: center;
        }
        .oc-cert-in {
            position: relative;
            padding: clamp(1.5rem, 5cqi, 3.25rem) clamp(1.1rem, 6cqi, 4rem) clamp(1.4rem, 4cqi, 2.4rem);
            background: #fbf8f1;
            box-shadow: 0 0 0 1px #0d5c63, inset 0 0 0 4px #fbf8f1, inset 0 0 0 5px rgba(13, 92, 99, 0.55);
        }
        /* The watermark: lathe work again, very faint, behind the words. */
        .oc-cert-in::before {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: min(78%, 30rem);
            aspect-ratio: 1;
            translate: -50% -50%;
            border-radius: 50%;
            background:
                repeating-conic-gradient(from 0deg, rgba(13, 92, 99, 0.07) 0 0.7deg, transparent 0.7deg 3deg),
                repeating-radial-gradient(circle, transparent 0 6px, rgba(13, 92, 99, 0.06) 6px 7px);
            -webkit-mask-image: radial-gradient(circle, transparent 0 16%, #000 16.5% 49.5%, transparent 50%);
            mask-image: radial-gradient(circle, transparent 0 16%, #000 16.5% 49.5%, transparent 50%);
            pointer-events: none;
        }
        .oc-cert-in > * { position: relative; }
        .oc-cert-corner {
            position: absolute;
            z-index: 1;
            width: clamp(1.9rem, 7cqi, 3.4rem);
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                radial-gradient(circle, #0d5c63 0 12%, #f1ead8 13% 22%, transparent 23%),
                repeating-conic-gradient(#0d5c63 0 4deg, #f1ead8 4deg 10deg);
            box-shadow: 0 0 0 2px #f1ead8, 0 0 0 3px #0d5c63;
        }
        .oc-cert-corner:nth-of-type(1) { left: 0; top: 0; translate: -18% -18%; }
        .oc-cert-corner:nth-of-type(2) { right: 0; top: 0; translate: 18% -18%; }
        .oc-cert-corner:nth-of-type(3) { left: 0; bottom: 0; translate: -18% 18%; }
        .oc-cert-corner:nth-of-type(4) { right: 0; bottom: 0; translate: 18% 18%; }
        .oc-cert-kicker { font-size: clamp(0.62rem, 2cqi, 0.8rem); font-weight: 700; letter-spacing: 0.34em; text-transform: uppercase; color: #0d5c63; }
        .oc-cert-title { margin-top: 0.5rem; font-family: var(--oc-display); font-size: clamp(1.7rem, 7cqi, 3.4rem); line-height: 1.05; color: #16242b; }
        .oc-cert-line { display: block; margin-top: clamp(1.1rem, 3.6cqi, 2rem); font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: clamp(0.95rem, 2.6cqi, 1.15rem); color: #3b4d55; }
        .oc-cert-form { display: grid; grid-template-columns: minmax(0, 1fr); justify-items: center; gap: clamp(0.9rem, 2.6cqi, 1.4rem); margin-top: 0.8rem; }
        #oc .oc-claim {
            display: flex;
            align-items: baseline;
            justify-content: center;
            width: min(100%, 36rem);
            padding: 0.35rem 0.5rem 0.45rem;
            border: 0;
            border-bottom: 2px solid #16242b;
            border-radius: 0.3rem 0.3rem 0 0;
            background: rgba(255, 183, 3, 0.14);
            font-family: var(--oc-display);
            font-size: clamp(1.02rem, 5cqi, 2.2rem);
            line-height: 1.2;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }
        #oc .oc-claim:focus-within { border-color: #16242b; background: rgba(255, 183, 3, 0.3); box-shadow: 0 0 0 3px rgba(13, 92, 99, 0.35); }
        #oc .oc-claim input {
            flex: 1 1 0;
            width: 0;
            min-width: 0;
            /* The field reaches over the row's own padding, so the target is the whole row
               (it was one 20px line on a phone) and nothing moves. */
            margin-block: -0.35rem -0.45rem;
            padding: 0.35rem 0 0.45rem;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            outline: none;
            text-align: right;
            font: inherit;
            color: #16242b;
        }
        #oc .oc-claim input::placeholder { color: #6b7a7f; opacity: 1; }
        .oc-claim span { flex: none; color: #3b4d55; user-select: none; }
        .oc-cert-form p { max-width: 34rem; font-size: clamp(0.92rem, 2.4cqi, 1.04rem); color: #3b4d55; }
        #oc .oc-cert .oc-btn { background: #0d5c63; color: #fbf8f1; box-shadow: 0 12px 24px -14px rgba(13, 92, 99, 0.7); }
        #oc .oc-cert a:focus-visible { outline-color: #0d5c63; }
        .oc-cert-note { margin-top: 0.75rem; font-size: 0.86rem; color: #586a71; }
        .oc-cert-foot { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); align-items: end; gap: 0.75rem; margin-top: clamp(1.4rem, 4cqi, 2.4rem); }
        /* The column is held to its third of the foot and the signature is let down to fit it:
           sized by its own words it pushed the registrar's line out through the certificate's
           frame on a phone under about 385px. */
        .oc-sign { display: grid; grid-template-columns: minmax(0, 1fr); justify-items: center; min-width: 0; }
        .oc-sign b { font-family: 'Sacramento', 'Snell Roundhand', 'Brush Script MT', cursive; font-weight: 400; font-size: clamp(1.1rem, 5.2cqi, 2.2rem); line-height: 1; color: #0d5c63; white-space: nowrap; }
        .oc-sign em { font-family: var(--oc-display); font-style: normal; font-size: clamp(1rem, 3.4cqi, 1.45rem); line-height: 1.5; color: #16242b; white-space: nowrap; }
        .oc-sign small { width: 100%; margin-top: 0.15rem; padding-top: 0.3rem; border-top: 1px solid #16242b; font-size: clamp(0.56rem, 1.6cqi, 0.68rem); font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #3b4d55; }
        /* The seal: pleated ribbon, two tails, a struck centre. */
        .oc-seal { position: relative; width: clamp(4.4rem, 15cqi, 7.2rem); aspect-ratio: 1; margin-bottom: clamp(-4.2rem, -8cqi, -2.4rem); filter: drop-shadow(0 8px 10px rgba(0, 0, 0, 0.3)); transition: opacity 0.5s ease 0.55s, scale 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.55s, rotate 0.6s ease 0.55s; }
        .oc-seal::before,
        .oc-seal::after { content: ""; position: absolute; top: 52%; width: 32%; height: 86%; background: #0d5c63; clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 80%, 0 100%); }
        .oc-seal::before { left: 14%; rotate: 13deg; }
        .oc-seal::after { right: 14%; rotate: -13deg; background: #0a474d; }
        .oc-seal-disc { position: absolute; inset: 0; z-index: 1; background: repeating-conic-gradient(from 0deg, #ffb703 0 6deg, #e39a00 6deg 12deg); }
        .oc-seal-core { position: absolute; inset: 19%; z-index: 2; display: grid; place-items: center; align-content: center; border-radius: 50%; background: #0d5c63; box-shadow: 0 0 0 2px #ffcf4d, inset 0 0 0 2px rgba(251, 248, 241, 0.25); color: #fbf8f1; line-height: 1; }
        .oc-seal-core b { font-family: var(--oc-display); font-weight: 400; font-size: clamp(1rem, 4.4cqi, 2rem); }
        .oc-seal-core small { margin-top: 0.15em; font-size: clamp(0.4rem, 1.2cqi, 0.56rem); font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #ffc94d; }
        html.es-anim #oc [data-reveal]:not(.is-revealed) .oc-seal { opacity: 0; scale: 1.6; rotate: -30deg; }

        /* The closing strip is a shared partial too. */
        #oc .oc-keep > section { background: var(--oc-bg-2); border-top: 1px solid var(--oc-line); }
        #oc .oc-keep h2 { font-family: var(--oc-display); font-weight: 400; font-size: clamp(1.9rem, 3.4vw, 2.6rem); line-height: 1.1; color: var(--oc-ink); }
        #oc .oc-keep p.uppercase { color: var(--oc-teal); letter-spacing: 0.14em; }
        #oc .oc-keep .grid > a { background: var(--oc-card); border: 1px solid var(--oc-line); border-radius: 1.1rem; }
        #oc .oc-keep .grid > a:hover { border-color: var(--oc-teal); box-shadow: 0 20px 30px -24px var(--oc-shadow); }
        #oc .oc-keep .grid > a > span:first-child { display: none; }
        #oc .oc-keep .grid > a h3 { color: var(--oc-ink); }
        #oc .oc-keep .grid > a p { color: var(--oc-ink-2); }
        #oc .oc-keep .grid > a > span:last-child,
        #oc .oc-keep a.self-start { color: var(--oc-teal); }

        /* ---------------------------------------------------------------
           Progress, driven by the scroll. The course grid is one view
           timeline (the ring, the percentage, the phone's line); each
           lesson is another, hoisted to the grid with timeline-scope so
           the tick beside its name in the syllabus can read it.
           --------------------------------------------------------------- */
        @supports (animation-timeline: view()) and (timeline-scope: --oc-a) {
            html.es-anim #oc .oc-course {
                view-timeline-name: --oc-course;
                timeline-scope: --oc-t1, --oc-t2, --oc-t3, --oc-t4, --oc-t5, --oc-t6, --oc-t7, --oc-t8, --oc-t9, --oc-t10, --oc-t11, --oc-t12;
            }
            html.es-anim #oc .oc-lesson { view-timeline-name: var(--oc-tl); }
            html.es-anim #oc .oc-ring {
                animation: oc-ring linear both;
                animation-timeline: --oc-course;
                animation-range: contain 0% contain 100%;
            }
            /* Safari carries a registered integer through fractions while a timeline animates it
               (24.9966), and a bare fraction is not a counter value: the ring filled while its
               figure read 0% until the very end. Inside calc() it is rounded, and it counts. */
            html.es-anim #oc .oc-ring-num::after { counter-reset: oc-pct calc(var(--oc-pct) * 1); content: counter(oc-pct) "%"; }
            html.es-anim #oc .oc-tick {
                animation: oc-tick linear both;
                animation-timeline: var(--oc-tl);
                animation-range: exit 0% exit 42%;
            }
            html.es-anim #oc .oc-tick::after {
                animation: oc-check linear both;
                animation-timeline: var(--oc-tl);
                animation-range: exit 0% exit 42%;
            }
            @media (max-width: 1099.98px) {
                html.es-anim #oc .oc-syllabus::after {
                    animation: oc-line linear both;
                    animation-timeline: --oc-course;
                    animation-range: contain 0% contain 100%;
                }
            }
        }
        @keyframes oc-ring { from { --oc-ang: 0deg; --oc-pct: 0; } to { --oc-ang: 360deg; --oc-pct: 100; } }
        @keyframes oc-tick { 0% { background-color: transparent; } 60%, 100% { background-color: var(--oc-fill); border-color: var(--oc-fill); } }
        @keyframes oc-check { 0%, 55% { opacity: 0; } 100% { opacity: 1; } }
        @keyframes oc-line { from { scale: 0 1; } to { scale: 1 1; } }

        /* Hovering a lesson lights its line in the syllabus. */
        #oc:has(#term:hover) .oc-syl a[href="#term"],
        #oc:has(#setup:hover) .oc-syl a[href="#setup"],
        #oc:has(#register:hover) .oc-syl a[href="#register"],
        #oc:has(#card:hover) .oc-syl a[href="#card"],
        #oc:has(#link:hover) .oc-syl a[href="#link"],
        #oc:has(#rest:hover) .oc-syl a[href="#rest"],
        #oc:has(#who:hover) .oc-syl a[href="#who"],
        #oc:has(#steps:hover) .oc-syl a[href="#steps"],
        #oc:has(#reading:hover) .oc-syl a[href="#reading"],
        #oc:has(#tuition:hover) .oc-syl a[href="#tuition"],
        #oc:has(#electives:hover) .oc-syl a[href="#electives"],
        #oc:has(#faq:hover) .oc-syl a[href="#faq"] { color: var(--oc-ink); box-shadow: inset 0 0 0 1.5px var(--oc-gold); }

        @media (prefers-reduced-motion: reduce) {
            .oc-btn, .oc-btn .oc-icon, .oc-tile, .oc-elective, .oc-read, .oc-wk-n, .oc-fill-bar, .oc-seal, .oc-q, .oc-syl a.es-dot { transition: none; }
        }
    </style>

    @php
        // One term. Thirteen calendar weeks, twelve sessions: the week of
        // Nov 24 is a date exception, which is why the spine has a hollow
        // tick and the register has a "no class" row.
        // 'on' = a session, 'off' = a skipped week.
        $termWeeks = [];
        foreach (range(0, 12) as $w) {
            $termWeeks[] = $w === 10 ? 'off' : 'on';
        }

        // The register: what rsvpRemaining() returns for each session date
        // when the seat cap is 14. 'skip' is the excluded date.
        $register = [
            ['01', 'Sep 15', 14, 0],
            ['02', 'Sep 22', 14, 2],
            ['03', 'Sep 29', 14, 5],
            ['04', 'Oct 6', 14, 9],
            ['05', 'Oct 13', 14, 11],
            ['06', 'Oct 20', 14, 14],
            ['07', 'Oct 27', 14, 12],
            ['08', 'Nov 3', 14, 8],
            ['09', 'Nov 10', 14, 6],
            ['10', 'Nov 17', 14, 4],
            ['--', 'Nov 24', 0, 0],
            ['11', 'Dec 1', 14, 3],
            ['12', 'Dec 8', 14, 1],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for teaching online classes?',
                'a' => 'Yes. Setting a course up as a term, skipping holiday weeks, ending the recurrence after a set number of sessions, taking free registrations with a seat cap per session date, publishing one link, embedding your schedule, syncing two ways with Google, Outlook or CalDAV, sending newsletters to the students who follow you and reading your analytics are all on the free plan. Charging for a seat is not: paid ticket types, plus class cards and custom checkout questions, are the Pro plan at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on payments at any plan level.',
            ],
            [
                'q' => 'How do I set up a twelve-week term?',
                'a' => 'Create the course once as a recurring event, pick the night it meets, and give the recurrence an end: after a set number of sessions, or on a closing date. A term that ends after twelve sessions stops on its own instead of repeating until somebody remembers to switch it off. Add date exceptions for the weeks you are off. Repeats can be daily, weekly, every few weeks, or monthly by date or by weekday.',
            ],
            [
                'q' => 'What video platforms can I use to teach?',
                'a' => 'Any platform that gives you a link. Zoom, Google Meet, Microsoft Teams, YouTube Live, or your own streaming setup. Being straight with you: this is one link field on the course, not an integration, and because the term is one recurring event that link is the same for all twelve sessions, the way a recurring meeting room already is. Event Schedule does not create the meeting, count who is in the room, or record anything. It publishes the sessions, takes the registrations, and puts your link in front of the people who signed up. If each week genuinely needs a different room, those weeks are separate events.',
            ],
            [
                'q' => 'Can I cap how many students join each session?',
                'a' => 'Yes, on the free plan. Set a seat cap on the course and it is counted per session date, so week three filling up does not close week four. When a session is full the register shows no seats left for that date and the sign-up button on that date becomes a waitlist instead. When somebody cancels, the first person waiting for that date is emailed and has twenty-four hours to take the seat before it passes to the next in line. The registration waitlist is free; the same waitlist on a sold-out paid date is a Pro feature.',
            ],
            [
                'q' => 'Can I sell a card that covers the whole term?',
                'a' => 'Yes, on the Pro plan. A class card is a pass, and you choose how it counts: a fixed number of visits, like a ten-visit card, or a membership with unlimited visits until it expires. Set how many days it is valid for from purchase, how many people it admits at each session, whether holders reserve a date in advance or just turn up, and a cancellation deadline with a policy for late cancels. A pass can also be scoped to one sub-schedule, so a beginner card does not open the advanced track.',
            ],
            [
                'q' => 'Can I charge for individual sessions?',
                'a' => 'Yes, on the Pro plan, which is what lets a seat carry a price. Create as many named ticket types as the course needs, each with its own price and quantity: a drop-in seat, a concession rate, a free trial session that stays free on any plan. Payments run through your own Stripe or PayPal account, or through Invoice Ninja, a payment link or cash, so you keep everything except the provider\'s own processing fee. Event Schedule takes nothing.',
            ],
            [
                'q' => 'Do my students get an email when I add a class?',
                'a' => 'Some do. A student who left an email address in the sign-up panel on your schedule page and confirmed it gets a digest of the new classes you publish, at most one every 72 hours, outside your newsletter allowance. Pressing Follow on its own sends nothing to an account follower automatically, so a student who did only that hears about a new class through a newsletter you write and send yourself: ten emails a month on the free plan, a hundred on Pro, a thousand on Enterprise, each recipient counting as one. Changes are a separate email. Change the class link and saving asks whether to tell everyone holding a seat on an upcoming session, and cancelling the course tells them as part of cancelling; those reach anyone who left an address on a session\'s page to hear about it, and registered students too, on eventschedule.com when your schedule sends through its own email settings. A new start time on a term does not trigger that question, because a term is one recurring event, so say it in a newsletter.',
            ],
            [
                'q' => 'Can students ask to hear when a course goes on sale?',
                'a' => 'Yes, on every plan. Switch on the "Notify me" card, publish the course before you add its tickets, and its page offers "Tell me when tickets go on sale": a student leaves an email address, with no account and no name, and gets one email when seats for that session date go on sale, a reminder 48 hours before it, word if it is cancelled, and any change notice you choose to send. Each session date keeps its own list, unsubscribing deletes the address, and the Tickets panel on the course shows how many people are waiting. To hear about every new course instead, a student signs up in the panel on your schedule page and gets the digest.',
            ],
            [
                'q' => 'Can I refund a student who drops out?',
                'a' => 'Yes, from the Sales page, on Pro. A payment taken through Stripe or PayPal goes back through the provider, in full or in part. A partial refund leaves their seat or class card valid, and only a full refund cancels it and puts the seat back on sale. Payments taken by Invoice Ninja, a payment link or cash are marked as refunded instead, which records the refund without moving any money.',
            ],
        ];

        $dotSections = [
            ['top', 'The syllabus'],
            ['term', 'Not one class'],
            ['setup', 'Writing the term'],
            ['register', 'The register'],
            ['card', 'The class card'],
            ['link', 'The room'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Week one'],
        ];
    @endphp

    @php
        // The page as a course: four modules, twelve lessons, then the certificate.
        // Lesson titles come from $dotSections where the section had a name there.
        $ocLabel = array_column($dotSections, 1, 0);
        $ocModules = [
            ['The term', [['term', $ocLabel['term'], '4 min'], ['setup', $ocLabel['setup'], '4 min']]],
            ['The class', [['register', $ocLabel['register'], '5 min'], ['card', $ocLabel['card'], '5 min'], ['link', $ocLabel['link'], '3 min']]],
            ['Around it', [['rest', $ocLabel['rest'], '6 min'], ['who', $ocLabel['who'], '2 min'], ['steps', 'Three steps', '1 min'], ['reading', 'Reading list', '1 min']]],
            ['Finals', [['tuition', 'Tuition', '2 min'], ['electives', 'Electives', '1 min'], ['faq', $ocLabel['faq'], 'Quiz']]],
        ];
        $ocL = [];
        $ocOrder = [];
        foreach ($ocModules as $ocMi => [$ocMName, $ocMLessons]) {
            foreach ($ocMLessons as $ocLi => [$ocId, $ocTitle, $ocMin]) {
                $ocOrder[] = $ocId;
                $ocL[$ocId] = ['no' => ($ocMi + 1).'.'.($ocLi + 1), 'title' => $ocTitle, 'min' => $ocMin, 'n' => count($ocOrder)];
            }
        }

        $ocArrow = '<svg aria-hidden="true" class="oc-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $ocDown = '<svg aria-hidden="true" class="oc-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';

        // A lesson's head (number, kicker, length) and its foot (the tick, and what is up next).
        $ocHead = function (string $id, string $kicker) use ($ocL) {
            return '<div class="oc-lhead" data-reveal><span class="oc-lno">'.e($ocL[$id]['no']).'</span><span>'.e($kicker).'</span><span class="oc-ldur">'.e($ocL[$id]['min']).'</span></div>';
        };
        $ocFoot = function (string $id) use ($ocL, $ocOrder, $ocArrow) {
            $next = $ocOrder[$ocL[$id]['n']] ?? 'claim';
            $label = $next === 'claim' ? 'Certificate' : $ocL[$next]['no'].' '.$ocL[$next]['title'];

            return '<div class="oc-lfoot"><span class="oc-ldone" aria-hidden="true"><i class="oc-tick"></i>End of lesson '.e($ocL[$id]['no']).'</span>'
                .'<a href="#'.e($next).'">Up next <b>'.e($label).'</b>'.$ocArrow.'</a></div>';
        };

        // The seal's pleated edge: sixty points, alternately out and in.
        $ocSealPoints = [];
        for ($ocP = 0; $ocP < 60; $ocP++) {
            $ocR = $ocP % 2 === 0 ? 50 : 45.5;
            $ocA = deg2rad($ocP * 6);
            $ocSealPoints[] = round(50 + $ocR * sin($ocA), 2).'% '.round(50 - $ocR * cos($ocA), 2).'%';
        }
        $ocSeal = implode(', ', $ocSealPoints);
    @endphp

    <div id="oc">

        <!-- ============================================================ -->
        <!-- The course page header                                       -->
        <!-- ============================================================ -->
        <section id="top" class="oc-hero">
            <div class="oc-wrap">
                <div class="oc-hero-grid">
                    <div class="oc-hero-copy">
                        <h1 class="oc-h1">
                            <x-marketing.hero-eyebrow class="oc-eyebrow es-fade-up es-d-1">
                                Event schedule for online classes, tutors and coaches
                            </x-marketing.hero-eyebrow>
                            <span class="es-mask"><span class="es-mask-line">A course is not one class.</span></span>
                            <span class="es-mask es-mask-2"><span class="es-mask-line">It is <span class="oc-mark">twelve</span> of them.</span></span>
                        </h1>

                        <p class="oc-lede es-fade-up es-d-2">
                            Write the term once - the night it meets, the weeks you are off, the session it finishes on - and take every registration for it from a single link, with zero platform fees.
                        </p>
                        <p class="oc-lede-2 es-fade-up es-d-2">
                            Online class scheduling with free registration and a seat cap counted per session date, multi-session class cards, recurring terms that end themselves, and payments through your own Stripe or PayPal account.
                        </p>

                        <div class="oc-cta es-fade-up es-d-3">
                            <a href="#setup" class="oc-btn oc-btn-ghost">
                                How a term works
                                {!! $ocDown !!}
                            </a>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="oc-btn">
                                Create your class schedule
                                {!! $ocArrow !!}
                            </a>
                        </div>

                        <ul class="oc-facts es-fade-up es-d-4" aria-hidden="true">
                            <li>12 lessons</li>
                            <li>4 modules</li>
                            <li>About 35 minutes</li>
                            <li>1 certificate</li>
                        </ul>
                    </div>

                    <!-- The course card: what the old syllabus said, as the thing you enrol on. -->
                    <div class="es-fade-up es-d-4">
                        <div class="oc-cc">
                            <div class="oc-cc-top"><span>Syllabus</span><span>Term 1</span></div>
                            <div class="oc-band" aria-hidden="true"></div>
                            <div class="oc-cc-body">
                                <p class="oc-cc-title">Conversational Spanish, Level 1</p>
                                <p class="oc-cc-meta">
                                    Tuesdays 6:00 PM &middot; online &middot; 12 sessions &middot; 14 seats a session
                                </p>

                                <div class="oc-term oc-term-sm" aria-hidden="true">
                                    @foreach ($termWeeks as $wi => $wState)
                                        <div class="oc-wk @if ($wState === 'off') oc-wk-off @endif">
                                            <span class="oc-wk-w">W{{ $wi + 1 }}</span>
                                            <span class="oc-wk-n">{{ $wState === 'off' ? 'off' : (int) $register[$wi][0] }}</span>
                                            <span class="oc-wk-d">{{ $register[$wi][1] }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="oc-cc-note">
                                    <p class="oc-cc-note-tag">Recurrence</p>
                                    <p class="oc-cc-note-big">Ends after 12 sessions.</p>
                                    <p>Thirteen Tuesdays, twelve sessions. The hollow week is <b>Nov 24</b>, taken out as a date exception.</p>
                                </div>

                                <p class="oc-cc-foot">
                                    One recurring event. Change the start time once and all twelve sessions follow.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="oc-subjects es-fade-up es-d-5">
                    <b aria-hidden="true">Taught here</b>
                    @foreach (['Languages', 'Cooking', 'Yoga', 'Coding', 'Drawing', 'Music', 'Tutoring', 'Masterclasses', 'Kids Classes', 'Coaching'] as $chip)
                        <span class="oc-subject">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- The player: the syllabus beside twelve lessons               -->
        <!-- ============================================================ -->
        <div class="oc-wrap">
            <div class="oc-course">

                <nav class="oc-syllabus es-dotnav" aria-label="Page sections">
                    <div class="oc-progress">
                        <div class="oc-ring" aria-hidden="true"><span class="oc-ring-num"></span></div>
                        <p class="oc-syl-title">{{ $ocLabel['top'] }} <small>12 lessons in 4 modules</small></p>
                    </div>
                    <ol class="oc-syl">
                        @foreach ($ocModules as $ocMi => [$ocMName, $ocMLessons])
                            <li class="oc-mod" aria-hidden="true"><b>Module {{ $ocMi + 1 }}</b>{{ $ocMName }}</li>
                            @foreach ($ocMLessons as [$ocId])
                                <li>
                                    <a href="#{{ $ocId }}" class="es-dot" style="--oc-tl: --oc-t{{ $ocL[$ocId]['n'] }};">
                                        <i class="oc-tick" aria-hidden="true"></i>
                                        <span class="oc-syl-no">{{ $ocL[$ocId]['no'] }}</span>
                                        <span class="oc-syl-name">{{ $ocL[$ocId]['title'] }}</span>
                                        <span class="oc-syl-min" aria-hidden="true">{{ $ocL[$ocId]['min'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @endforeach
                        <li>
                            <a href="#claim" class="es-dot">
                                <i class="oc-syl-seal" aria-hidden="true"></i>
                                <span class="oc-syl-no" aria-hidden="true"></span>
                                <span class="oc-syl-name">Certificate</span>
                                <span class="oc-syl-min" aria-hidden="true">{{ $ocLabel['claim'] }}</span>
                            </a>
                        </li>
                    </ol>
                </nav>

                <div class="oc-main">

                    <div class="oc-module" aria-hidden="true"><span class="oc-module-no">Module 1</span><span class="oc-module-name">The term</span><i></i></div>

                    <!-- 1.1 A course is not one class -->
                    <section id="term" class="oc-lesson oc-lesson-feature" style="--oc-tl: --oc-t1;">
                        {!! $ocHead('term', 'The unit') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Most calendars think a class is <em>one night.</em>
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            The thing you actually teach is a term. Twelve sessions, one topic, the same students each week, and a last night.
                        </p>

                        <div class="oc-trio" data-reveal-group="110">
                            <div class="oc-mini" data-reveal="panel">
                                <p class="oc-cc-note-tag">The term</p>
                                <h3 class="oc-figure"><span data-count-to="12">12</span> <small>sessions</small></h3>
                                <p>Same course, same students, thirteen weeks. Entering it as twelve separate events is twelve chances to mistype a time.</p>
                            </div>
                            <div class="oc-mini" data-reveal="panel">
                                <p class="oc-cc-note-tag">The setup</p>
                                <h3 class="oc-figure"><span data-count-to="1">1</span> <small>event</small></h3>
                                <p>A repeat pattern, exceptions for the weeks you are off, and an end. Move the class an hour later once and every session moves.</p>
                            </div>
                            <div class="oc-mini" data-reveal="panel">
                                <p class="oc-cc-note-tag">The close</p>
                                <h3 class="oc-figure oc-figure-word">It stops itself</h3>
                                <p>A term ends on a closing date or after a set number of sessions, so it is not still taking sign-ups for week nineteen in March.</p>
                            </div>
                        </div>

                        <div class="oc-worked" data-reveal>
                            <p class="oc-worked-tag">The term, drawn</p>
                            <div class="oc-term" aria-hidden="true">
                                @foreach ($termWeeks as $wi => $wState)
                                    <div class="oc-wk @if ($wState === 'off') oc-wk-off @endif" style="--i: {{ $wi }};">
                                        <span class="oc-wk-w">W{{ $wi + 1 }}</span>
                                        <span class="oc-wk-n">{{ $wState === 'off' ? 'off' : (int) $register[$wi][0] }}</span>
                                        <span class="oc-wk-d">{{ $register[$wi][1] }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="oc-worked-cap">
                                Thirteen weeks, twelve filled. The hollow one is a date exception, not a cancelled event.
                                <a href="#setup">
                                    Write one
                                    {!! $ocDown !!}
                                </a>
                            </p>
                        </div>

                        {!! $ocFoot('term') !!}
                    </section>

                    <!-- 1.2 Writing the term -->
                    <section id="setup" class="oc-lesson" style="--oc-tl: --oc-t2;">
                        {!! $ocHead('setup', 'Writing the term') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Three lines and you have a <em>course.</em>
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            All three are on the free plan. None of them are a spreadsheet.
                        </p>

                        <div class="oc-trio" data-reveal-group="100">
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>The night it meets</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                <p>Pick the days of the week and the start time. Repeats can be daily, weekly, every few weeks, or monthly by date or by weekday, so a fortnightly workshop is one setting rather than a second calendar.</p>
                                <div class="oc-ctl" aria-hidden="true">
                                    <span>M</span><span class="is-on">T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
                                    <b>6:00 PM</b>
                                </div>
                            </div>
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>The weeks you are off</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                <p>Date exceptions take single dates out, so a holiday week or a week you are travelling disappears from the schedule without rebuilding the term. You can add one-off dates back in the same way.</p>
                                <div class="oc-ctl" aria-hidden="true">
                                    <span class="is-on">Nov 10</span><span class="is-on">Nov 17</span><span class="is-out">Nov 24</span><span class="is-on">Dec 1</span>
                                </div>
                            </div>
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>The last session</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                <p>End the recurrence after a set number of sessions, or on a closing date, or never. This is the setting that makes a term a term instead of a weekly slot that runs forever.</p>
                                <div class="oc-ctl" aria-hidden="true">
                                    <span class="is-on">After 12 sessions</span><span>On a date</span><span>Never</span>
                                </div>
                            </div>
                        </div>

                        <!-- Honesty beat: one recurring event has one name. -->
                        <div class="oc-note" data-reveal="panel">
                            <p class="oc-note-tag">Worth knowing</p>
                            <h3>A term has one name, not twelve titles.</h3>
                            <p>
                                A recurring event carries one name and one description, so week four is not separately titled "the past tense". If the weeks really are different topics with different prices, make them separate events - cloning one is a click - and keep them together in a sub-schedule. If they are one course, the term is the right shape, and the week-by-week breakdown belongs in the description, because the agenda you set runs the same way in every session.
                            </p>
                        </div>

                        {!! $ocFoot('setup') !!}
                    </section>

                    <div class="oc-module" aria-hidden="true"><span class="oc-module-no">Module 2</span><span class="oc-module-name">The class</span><i></i></div>

                    <!-- 2.1 The register: seats counted per session date -->
                    <section id="register" class="oc-lesson" style="--oc-tl: --oc-t3;">
                        {!! $ocHead('register', 'The register') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Fourteen seats, <em>counted per date.</em>
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            The cap is set once on the course and counted separately for every session, so week three filling up does not close week four. Free registration, free plan.
                        </p>

                        <div class="oc-worked oc-reg-wrap" data-reveal="panel">
                            <p class="oc-worked-tag" aria-hidden="true">Worked example</p>
                            <table class="oc-reg">
                                <caption class="sr-only">Term register: seats taken and seats left for each session date, with a seat cap of fourteen</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Session</th>
                                        <th scope="col">Date</th>
                                        <th scope="col" class="oc-reg-bar">Taken</th>
                                        <th scope="col" class="oc-reg-end">Seats left</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($register as [$rNum, $rDate, $rCap, $rLeft])
                                        @php
                                            $rSkip = $rCap === 0;
                                            $rTaken = $rSkip ? 0 : $rCap - $rLeft;
                                            $rPct = $rSkip ? 0 : (int) round(($rTaken / $rCap) * 100);
                                            $rFull = ! $rSkip && $rLeft === 0;
                                        @endphp
                                        <tr @if ($rSkip) class="oc-reg-off" @endif>
                                            <th scope="row">
                                                @if ($rSkip)
                                                    off
                                                @else
                                                    {{ $rNum }}
                                                @endif
                                            </th>
                                            <td>{{ $rDate }}</td>
                                            <td class="oc-reg-bar">
                                                @if ($rSkip)
                                                    No class this week
                                                @else
                                                    <div class="oc-fill-track" role="img" aria-label="{{ $rTaken }} of {{ $rCap }} seats taken">
                                                        <div class="oc-fill-bar @if ($rFull) oc-fill-bar-full @endif" style="width: {{ $rPct }}%;"></div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="oc-reg-end">
                                                @if ($rSkip)
                                                    date exception
                                                @elseif ($rFull)
                                                    <span class="oc-full">full</span>
                                                @else
                                                    <b>{{ $rLeft }}</b> / {{ $rCap }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <p class="oc-worked-cap">
                                Week one is full and week six is empty, on the same course, at the same time. That is the point: the count lives on the date, not on the course. Students see the seats left for the date they are looking at, and a full date stops taking sign-ups without touching the others.
                            </p>
                        </div>

                        <div class="oc-duo" data-reveal-group="90">
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>Free registration</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                <p>A name and an email gets somebody a seat on a specific date, with an optional cap. No card, no checkout, and no plan to upgrade to first.</p>
                            </div>
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>A waitlist when a date fills up</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                <p>Once a registration date is full, the sign-up button on it becomes a waitlist. When somebody drops, the first person waiting for <em>that</em> date is emailed and has twenty-four hours to claim the seat before it moves to the next in line. Set the cap you can actually teach to and let the list do the rest. On a sold-out <em>paid</em> date the same waitlist is Pro.</p>
                            </div>
                        </div>

                        {!! $ocFoot('register') !!}
                    </section>

                    <!-- 2.2 The class card (passes) -->
                    <section id="card" class="oc-lesson" style="--oc-tl: --oc-t4;">
                        {!! $ocHead('card', 'The class card') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Nobody wants to buy <em>twelve tickets.</em>
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            They want one card that covers the term. A pass counts the way your course actually sells: a fixed number of visits, or unlimited visits until it runs out of days.
                        </p>

                        <!-- Two enrolment cards, then the rules beside the single seats. -->
                        <div class="oc-worked">
                            <p class="oc-worked-tag" aria-hidden="true">Two cards, one term</p>
                            <div class="oc-ids" data-reveal-group="100">
                                <div data-reveal="panel" data-tilt="5">
                                    <div class="oc-id es-tilt-inner">
                                        <div class="oc-id-top"><span>Visit card</span><span>$120</span></div>
                                        <div class="oc-id-body">
                                            <span class="oc-id-badge" aria-hidden="true">10</span>
                                            <div>
                                                <p class="oc-id-name">10 visits</p>
                                                <p class="oc-id-sub">Any Tuesday in the term.</p>
                                            </div>
                                        </div>
                                        <div class="oc-id-rest">
                                            <div class="oc-cells" aria-hidden="true">
                                                @foreach (range(1, 10) as $visit)
                                                    <span class="oc-cell @if ($visit <= 4) is-used @endif">{{ $visit }}</span>
                                                @endforeach
                                            </div>
                                            <p class="oc-id-count">4 used &middot; 6 left</p>
                                            <p class="oc-id-fine">Ten cells against twelve sessions: two Tuesdays can slip. Valid 120 days from purchase. Admits 1.</p>
                                        </div>
                                        <div class="oc-id-code" aria-hidden="true"></div>
                                    </div>
                                </div>

                                <div data-reveal="panel" data-tilt="5">
                                    <div class="oc-id es-tilt-inner">
                                        <div class="oc-id-top"><span>Membership</span><span>$45</span></div>
                                        <div class="oc-id-body">
                                            <span class="oc-id-badge" aria-hidden="true">&infin;</span>
                                            <div>
                                                <p class="oc-id-name">Unlimited</p>
                                                <p class="oc-id-sub">Every session, until it expires.</p>
                                            </div>
                                        </div>
                                        <div class="oc-id-rest">
                                            <div class="oc-strip" aria-hidden="true"></div>
                                            <p class="oc-id-count">Same strip, no cells</p>
                                            <p class="oc-id-fine">Valid 90 days. Scoped to the Beginner sub-schedule.</p>
                                        </div>
                                        <div class="oc-id-code" aria-hidden="true"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="oc-split">
                            <div>
                                <ul class="oc-checks" data-reveal-group="70">
                                    <li data-reveal>A visit card is good for a set number of visits across the sessions it covers. Ten visits, used whenever they can make it.</li>
                                    <li data-reveal>A membership is unlimited until it expires. Set how many days it is valid for from the day it is bought.</li>
                                    <li data-reveal>Scope it to everything you teach, to one sub-schedule, or to the specific courses you name, so a beginner card does not open the advanced track.</li>
                                    <li data-reveal>Holders can reserve a date in advance, or just turn up. Set a cancellation deadline and decide whether a late cancel gets the visit back.</li>
                                    <li data-reveal>Set admissions per session above one and a card lets somebody bring a partner. Usage is tracked, so you can see which cards are being used.</li>
                                </ul>
                                <p class="oc-smallprint" data-reveal>
                                    Class cards are Pro, at {{ plan_price($proMonthly) }} a month, along with anything that carries a price. Publishing the term and taking free registrations are not.
                                    <a href="{{ marketing_url('/features/ticketing') }}" class="oc-link">See what ticketing includes</a>.
                                </p>
                            </div>

                            <div class="oc-seats" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>And single seats, alongside</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                @foreach ([['Drop-in seat', 'one session', '$18'], ['Concession', 'one session', '$12'], ['First session', 'try it once', 'Free']] as [$tName, $tScope, $tPrice])
                                    <div class="oc-seat">
                                        <span>{{ $tName }}</span>
                                        <span>{{ $tScope }}</span>
                                        <span>{{ $tPrice }}</span>
                                    </div>
                                @endforeach
                                <p>
                                    Cards are sold next to single seats, not instead of them. Both need the Pro plan, because both carry a price; a free trial session does not. Payments run through your own Stripe or PayPal account, or Invoice Ninja, a payment link or cash, and Event Schedule takes <b>zero platform fees</b> at every plan level.
                                </p>
                            </div>
                        </div>

                        {!! $ocFoot('card') !!}
                    </section>

                    <!-- 2.3 The room and the link -->
                    <section id="link" class="oc-lesson" style="--oc-tl: --oc-t5;">
                        {!! $ocHead('link', 'The room') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Your link. <em>Any platform.</em>
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            Paste one meeting link on the course and every session in the term joins through it, the way a recurring meeting room already works. Zoom, Google Meet, Microsoft Teams, YouTube Live, your own setup: it is a link field, so all of them work and none of them own you.
                        </p>

                        <div class="oc-worked" data-reveal aria-hidden="true">
                            <p class="oc-worked-tag">One link, for the whole term</p>
                            <div class="oc-join">
                                <div class="oc-field"><i></i><span>Paste one meeting link</span></div>
                                <span class="oc-join-arrow">{!! $ocArrow !!}</span>
                                <div class="oc-sessions">
                                    @foreach (range(1, 12) as $ocSession)
                                        <span>{{ $ocSession }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="oc-trio" data-reveal-group="100">
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>One link for the whole schedule</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                <p>Your schedule lives at its own address. Put it in a bio, a signature, a course page, and it keeps being right when the term rolls over.</p>
                                <div class="oc-mini-foot">
                                    <div class="oc-field oc-field-solid" dir="ltr"><i aria-hidden="true"></i><span>your-classes.eventschedule.com</span></div>
                                </div>
                            </div>
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>Or on the site you already have</h3>
                                    <span class="oc-tag">Free</span>
                                </div>
                                <p>Embed the calendar in a page on your own site with an iframe. A list layout suits a term better than a month grid, and that is a setting.</p>
                                <div class="oc-mini-foot">
                                    <p>The registration form can be embedded too, free. The ticket purchase form is the Pro version of that widget.</p>
                                </div>
                            </div>
                            <div class="oc-mini" data-reveal="panel">
                                <div class="oc-mini-head">
                                    <h3>What this is not</h3>
                                </div>
                                <p>It is not a video platform and does not pretend to be. Event Schedule does not create the meeting, count who is in the room, take attendance from it, or hold recordings. It publishes the sessions, takes the registrations, and hands over your link. One link, for the whole term: if week four genuinely needs its own room, week four is a separate event.</p>
                                <div class="oc-mini-foot">
                                    <a href="{{ marketing_url('/features/online-events') }}" class="oc-link">How online events work</a>
                                </div>
                            </div>
                        </div>

                        {!! $ocFoot('link') !!}
                    </section>

                    <div class="oc-module" aria-hidden="true"><span class="oc-module-no">Module 3</span><span class="oc-module-name">Around it</span><i></i></div>

                    <!-- 3.1 Everything else -->
                @php
                    $rest = [
                        ['Newsletters to your students', 'Free', 'Students follow your schedule so you can write to them. Materials before, a recording link after, next term when it opens. Ten emails a month free, a hundred on Pro and a thousand on Enterprise, each recipient counting as one, with open and click rates.'],
                        ['Two-way calendar sync', 'Free', 'Google, Outlook and CalDAV, both directions, so your teaching hours and the rest of your week sit in one calendar. A recurring term syncs across as its next session rather than as a repeating series; to see all twelve dates in a calendar app, subscribe to your schedule\'s calendar feed instead. Students can subscribe to the same live feed from your schedule page, with no email address, and it updates itself when a date changes.'],
                        ['Analytics that are already on', 'Free', 'Views, devices and where the traffic came from, per schedule. Enough to know whether the term filled from your newsletter or from somebody else linking you.'],
                        ['A session agenda', 'Free', 'Break a class into named parts with their own times: warm-up, teaching, questions. It is the running order of a session, and on a term every session runs it.'],
                        ['Sub-schedules for levels', 'Free', 'Beginner, intermediate and advanced as separate strands of the same link, each with its own colour. They organise and filter; they do not hide anything, and a pass can be scoped to one of them.'],
                        ['Questions at checkout', 'Pro', 'Custom fields on the form collect what the course needs at the point of signing up: the level they think they are, dietary notes for a cooking class, a parent contact.'],
                        ['Reusable event templates', 'Pro', 'Save a term as a template and start next term from it, or clone last term outright. A template keeps the pattern and the twelve-session end; the holiday dates it deliberately does not keep, because those belong to the calendar and not to the course. A clone keeps them.'],
                        ['A follower QR code', 'Free', 'Every schedule has a QR code that points at it. Put it on the last slide of the deck and the people who liked the class can follow you before they close the tab.'],
                    ];
                @endphp
                    <section id="rest" class="oc-lesson" style="--oc-tl: --oc-t6;">
                        {!! $ocHead('rest', 'Everything else') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            The rest of the <em>syllabus.</em>
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            Marked Free or Pro, honestly. Six of these eight cost nothing.
                        </p>

                        <div class="oc-handouts" data-reveal-group="60">
                            @foreach ($rest as [$rTitle, $rPlan, $rBody])
                                <div class="oc-handout" data-reveal>
                                    <div class="oc-mini-head">
                                        <h3>{{ $rTitle }}</h3>
                                        <span class="oc-tag @if ($rPlan === 'Pro') oc-tag-paid @endif">{{ $rPlan }}</span>
                                    </div>
                                    <p>{{ $rBody }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="oc-note" data-reveal>
                            <p class="oc-note-tag">Which list is which</p>
                            <p>
                                Worth being precise about which list is which. Somebody who left an email address and confirmed it hears when you publish new classes, as one digest rather than a message per class. Somebody who pressed Follow from their own account is on the other list, and that one is reached only by a newsletter you write. There is no automation builder here either way: no branching sequence, no drip.
                            </p>
                        </div>

                        {!! $ocFoot('rest') !!}
                    </section>

                    <!-- 3.2 Perfect for: the catalogue -->
                    @php
                        // Department code, name, description, blog slug, and the cover it is printed on.
                        $ocCatalogue = [
                            ['FIT 110', 'Yoga & Fitness Instructors', 'Daily or weekly sessions with a cap per date, and a ten-visit card for the regulars who cannot make every one.', 'for-yoga-fitness-instructors-online',
                                '--c: #0d5c63; --p: repeating-radial-gradient(circle at 50% 130%, transparent 0 13px, rgba(251, 248, 241, 0.3) 13px 15px);'],
                            ['CUL 120', 'Cooking Instructors', 'Newsletter the ingredient list to the people who signed up for the course, teach live, then write once more with the recipe.', 'for-cooking-instructors-online',
                                '--c: #ffb703; --p: radial-gradient(circle, rgba(22, 36, 43, 0.9) 0 20%, transparent 22%); --s: 20px 20px;'],
                            ['ART 130', 'Art & Music Teachers', 'A drawing term and a guitar term as separate sub-schedules on one link, each ending on its own last session.', 'for-art-music-teachers-online',
                                '--c: #d9eae4; --p: linear-gradient(90deg, #0d5c63 0 9%, transparent 9% 14%, #16242b 14% 17%, transparent 17% 30%, #ffb703 30% 44%, transparent 44% 52%, #0d5c63 52% 55%, transparent 55% 63%, #16242b 63% 72%, transparent 72% 80%, #ffb703 80% 84%, transparent 84% 91%, #0d5c63 91% 100%);'],
                            ['LAN 140', 'Language Tutors', 'A twelve-week conversation class that stops after twelve, with holiday weeks taken out as date exceptions.', 'for-language-tutors',
                                '--c: #16242b; --p: repeating-linear-gradient(0deg, transparent 0 15px, rgba(251, 248, 241, 0.22) 15px 16px), linear-gradient(90deg, transparent 0 22%, rgba(255, 183, 3, 0.9) 22% 23%, transparent 23%);'],
                            ['CSC 150', 'Coding & Tech Educators', 'Bootcamps, workshops and study groups, kept in beginner and advanced strands so a card for one does not open the other.', 'for-coding-tech-educators',
                                '--c: #0a474d; --p: linear-gradient(rgba(251, 248, 241, 0.16) 1px, transparent 1px), linear-gradient(90deg, rgba(251, 248, 241, 0.16) 1px, transparent 1px), linear-gradient(135deg, transparent 0 62%, rgba(255, 183, 3, 0.95) 62% 100%); --s: 18px 18px, 18px 18px, auto;'],
                            ['BUS 160', 'Business Coaches', 'A cohort that meets fortnightly, sold as a term membership, with the intake question asked at checkout.', 'for-business-coaches-online',
                                '--c: #ffc94d; --p: repeating-linear-gradient(135deg, transparent 0 14px, rgba(13, 92, 99, 0.85) 14px 20px);'],
                        ];
                    @endphp
                    <section id="who" class="oc-lesson" style="--oc-tl: --oc-t7;">
                        {!! $ocHead('who', 'Perfect for') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Every kind of <em>online class</em>
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            A term is a term whether it is verbs or knife skills. Also see Event Schedule for <a href="{{ marketing_url('/for-webinars') }}">Webinars</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}">Virtual Conferences</a>.
                        </p>

                        <div class="oc-catalogue" data-reveal-group="70">
                            @foreach ($ocCatalogue as [$cCode, $cName, $cBody, $cSlug, $cCover])
                                @php $cPost = get_sub_audience_blog($cSlug); @endphp
                                <article class="oc-tile" data-reveal>
                                    <div class="oc-cover" style="{{ $cCover }}" aria-hidden="true"><span>{{ $cCode }}</span></div>
                                    <div class="oc-tile-body">
                                        <h3>{{ $cName }}</h3>
                                        <p>{{ $cBody }}</p>
                                        @if ($cPost)
                                            <a href="{{ blog_url('/' . $cPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $cName }}">
                                                Learn more
                                                {!! $ocArrow !!}
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        {!! $ocFoot('who') !!}
                    </section>

                    <!-- 3.3 Three steps -->
                    <section id="steps" class="oc-lesson" style="--oc-tl: --oc-t8;">
                        {!! $ocHead('steps', 'Homework') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Three steps to week one
                        </h2>

                        <ol class="oc-steps" data-reveal-group="120">
                            @foreach ([['01', 'Write the term', 'Create the course as a recurring event, pick the night it meets, and end the recurrence after a set number of sessions or on a closing date.'], ['02', 'Skip the weeks you are off', 'Add date exceptions for the holiday weeks, and paste your class link on the course so students join from the schedule.'], ['03', 'Open the register', 'Set a seat cap counted per date, then take free registrations, or sell single seats and class cards with nothing taken off the top.']] as [$stepNum, $stepTitle, $stepBody])
                                <li class="oc-step" data-reveal>
                                    <span class="oc-step-no" aria-hidden="true">{{ $stepNum }}</span>
                                    <h3>{{ $stepTitle }}</h3>
                                    <p>{{ $stepBody }}</p>
                                </li>
                            @endforeach
                        </ol>

                        {!! $ocFoot('steps') !!}
                    </section>

                    <!-- 3.4 Key features: the reading list -->
                    <section id="reading" class="oc-lesson" style="--oc-tl: --oc-t9;">
                        {!! $ocHead('reading', 'Reading list') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">Key features</h2>

                        <div class="oc-reading" data-reveal-group="70">
                            @foreach ([
                                ['Recurring Events', 'Set a term once, skip the holiday weeks, end after a set number of sessions', marketing_url('/features/recurring-events')],
                                ['Online Events', 'Publish sessions that meet on any platform, from one link field', marketing_url('/features/online-events')],
                                ['Newsletters', 'Email the students who follow you, with open and click rates', marketing_url('/features/newsletters')],
                                ['Analytics', 'Track page views, devices, and traffic sources', marketing_url('/features/analytics')],
                            ] as $ocReadIndex => [$ocReadName, $ocReadBody, $ocReadUrl])
                                <a href="{{ $ocReadUrl }}" class="oc-read" data-reveal>
                                    <span class="oc-read-no" aria-hidden="true">{{ $ocReadIndex + 1 }}</span>
                                    <span>
                                        <strong>{{ $ocReadName }}</strong>
                                        <small>{{ $ocReadBody }}</small>
                                    </span>
                                    {!! $ocArrow !!}
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ marketing_url('/features') }}" class="oc-more">
                            See all features
                            {!! $ocArrow !!}
                        </a>

                        {!! $ocFoot('reading') !!}
                    </section>

                    <div class="oc-module" aria-hidden="true"><span class="oc-module-no">Module 4</span><span class="oc-module-name">Finals</span><i></i></div>

                    <!-- 4.1 Tuition: the shared plan band -->
                    <section id="tuition" class="oc-lesson oc-plans" style="--oc-tl: --oc-t10;">
                        {!! $ocHead('tuition', 'Tuition') !!}
                        @include('marketing.partials.pricing-nudge')
                        {!! $ocFoot('tuition') !!}
                    </section>

                    <!-- 4.2 Related pages: the electives -->
                    <section id="electives" class="oc-lesson" style="--oc-tl: --oc-t11;">
                        {!! $ocHead('electives', 'Electives') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">Related pages</h2>

                        <div class="oc-electives" data-reveal-group="70">
                            @foreach ([['/for-workshop-instructors', 'Workshop Instructors'], ['/for-webinars', 'Webinars'], ['/for-fitness-and-yoga', 'Fitness & Yoga'], ['/for-virtual-conferences', 'Virtual Conferences']] as [$relHref, $relName])
                                <a href="{{ marketing_url($relHref) }}" class="oc-elective" data-reveal>
                                    <small aria-hidden="true">Elective</small>
                                    <strong>For {{ $relName }}</strong>
                                    <span>
                                        Read more
                                        {!! $ocArrow !!}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ marketing_url('/use-cases') }}" class="oc-more">
                            See all use cases
                            {!! $ocArrow !!}
                        </a>

                        {!! $ocFoot('electives') !!}
                    </section>

                    <!-- 4.3 FAQ: check your understanding -->
                    <x-seo.faq-schema :items="$faqs" />

                    <section id="faq" class="oc-lesson" style="--oc-tl: --oc-t12;">
                        {!! $ocHead('faq', 'Check your understanding') !!}
                        <h2 class="oc-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Frequently asked questions
                        </h2>
                        <p class="oc-lead" data-reveal style="--reveal-delay: 0.12s;">
                            What instructors ask before they move a term across.
                        </p>

                        <div class="oc-quiz" data-reveal-group="60">
                            @foreach ($faqs as $faqIndex => $faq)
                                <details name="faq" class="oc-q" data-reveal>
                                    <summary>
                                        <span class="oc-q-no" aria-hidden="true">Q{{ $faqIndex + 1 }}</span>
                                        <h3>{{ $faq['q'] }}</h3>
                                        <span class="oc-q-show" aria-hidden="true"></span>
                                    </summary>
                                    <p class="faq-answer">{{ $faq['a'] }}</p>
                                </details>
                            @endforeach
                        </div>

                        {!! $ocFoot('faq') !!}
                    </section>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- Graduation: the certificate                                  -->
        <!-- ============================================================ -->
        <section id="claim" class="oc-grad">
            <div class="oc-wrap oc-grad-in">
                <p class="oc-grad-tag" data-reveal>Free forever</p>
                <h2 class="oc-grad-h2" data-reveal style="--reveal-delay: 0.06s;">
                    Write the term once. <span>Teach all twelve.</span>
                </h2>
                <p class="oc-grad-sub" data-reveal style="--reveal-delay: 0.12s;">
                    Publishing your term, capping the seats and taking free registrations are free forever, with no monthly ceiling on any of them. Charging for a seat, and class cards, are {{ plan_price($proMonthly) }} a month, and nothing is taken off what you charge.
                </p>

                <div class="oc-cert" id="oc-cert" data-reveal="panel">
                    <i class="oc-cert-corner" aria-hidden="true"></i>
                    <i class="oc-cert-corner" aria-hidden="true"></i>
                    <i class="oc-cert-corner" aria-hidden="true"></i>
                    <i class="oc-cert-corner" aria-hidden="true"></i>
                    <div class="oc-cert-in">
                        <p class="oc-cert-kicker" aria-hidden="true">Certificate of completion</p>
                        <p class="oc-cert-title" aria-hidden="true">Twelve lessons, read.</p>
                        <label for="es-claim-input" class="oc-cert-line">This certifies that<span class="sr-only">: your schedule name</span></label>
                        <div class="oc-cert-form">
                            <div dir="ltr" class="es-claim oc-claim">
                                <input id="es-claim-input" type="text" placeholder="your-classes" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <p aria-hidden="true">has read the whole course, knows a term from a single night, and is ready for week one.</p>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="oc-btn">
                                Get Started Free
                                {!! $ocArrow !!}
                            </a>
                        </div>
                        <p class="oc-cert-note">No credit card required</p>

                        <div class="oc-cert-foot" aria-hidden="true">
                            <div class="oc-sign"><em>{{ $ocLabel['claim'] }}</em><small>Dated</small></div>
                            <div class="oc-seal">
                                <div class="oc-seal-disc" style="clip-path: polygon({{ $ocSeal }});"></div>
                                <div class="oc-seal-core"><b>12</b><small>of 12</small></div>
                            </div>
                            <div class="oc-sign"><b>Event Schedule</b><small>Registrar</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="oc-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- Graduation confetti, in the course's own colours rather than the site's blues. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var cert = document.getElementById('oc-cert');
            if (!cert || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var colors = ['#ffb703', '#fbf8f1', '#7dd6cc', '#ffc94d'];
                    {{-- The library's default cannon draws from a blob worker, which the site's
                         content policy refuses without throwing, so nothing was ever drawn.
                         One made here draws on the page instead. --}}
                    var fire = typeof window.confetti.create === 'function' ? window.confetti.create(null, { resize: true }) : window.confetti;
                    [[60, 0.05], [120, 0.95]].forEach(function (shot) {
                        fire({ particleCount: 70, angle: shot[0], spread: 60, startVelocity: 52, origin: { x: shot[1], y: 0.92 }, colors: colors, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.55 });
            io.observe(cert);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
