<x-marketing-layout>
    <x-slot name="title">Free Community Center Calendar | Programs, RSVPs, Hall Hire</x-slot>
    <x-slot name="description">The lobby timetable, online: weekly programs set once, free sign-ups with a cap, hall-hire requests you approve and a calendar members subscribe to.</x-slot>
    <x-slot name="breadcrumbTitle">For Community Centers</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Fredoka One') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Nunito') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Community Centers"
        description="The lobby timetable, online: recurring programs, free sign-ups with a capacity, hall-hire requests you approve, a live calendar feed and email to members who sign up. Free forever."
        audience="Community Centers & Recreation Facilities"
        keywords="community center calendar, recreation program schedule, facility booking software, community events, free community center scheduling" />
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
           For-community-centers "Everyone's Room" styles. A center's own
           site, made the way its noticeboard is made: paper cut with
           scissors. Flat shapes in four friendly colours, big round
           type, doors that open, and a week you can read at a glance.

           Everything is scoped under #cm. The four papers keep their
           colour in both modes; the wall behind them goes dark at
           night. Text on a paper is always the dark ink (--cm-on),
           which measures above 4.5:1 on all four.
           ============================================================== */

        @property --cm-b {
            syntax: '<percentage>';
            inherits: false;
            initial-value: 50%;
        }

        #cm {
            --cm-ground: #fffaf2;
            --cm-wall: #fff0cf;
            --cm-surface: #ffffff;
            --cm-ink: #1f2430;
            --cm-ink-2: #3b4150;
            --cm-ink-3: #596070;
            --cm-line: rgba(31, 36, 48, 0.16);
            --cm-faint: rgba(31, 36, 48, 0.05);
            --cm-sun: #ffc83d;
            --cm-tomato: #ff5a4e;
            --cm-teal: #1bb5a4;
            --cm-leaf: #6bbf59;
            --cm-on: #1f2430;
            --cm-slip: #fffdf8;
            --cm-slip-2: #4b5160;
            --cm-shade: rgba(31, 36, 48, 0.14);
            --cm-display: 'Fredoka One', 'Nunito', ui-rounded, 'Arial Rounded MT Bold', system-ui, sans-serif;
            --cm-text: 'Nunito', ui-rounded, 'Segoe UI', system-ui, sans-serif;
            position: relative;
            background: var(--cm-ground);
            color: var(--cm-ink);
            font-family: var(--cm-text);
            font-size: 1.125rem;
            line-height: 1.6;
        }
        .dark #cm {
            --cm-ground: #1b1f27;
            --cm-wall: #222732;
            --cm-surface: #272d39;
            --cm-ink: #fffaf2;
            --cm-ink-2: #d9dce3;
            --cm-ink-3: #adb2bd;
            --cm-line: rgba(255, 250, 242, 0.18);
            --cm-faint: rgba(255, 250, 242, 0.05);
            --cm-sun: #f2b92f;
            --cm-tomato: #f0564b;
            --cm-teal: #19a595;
            --cm-leaf: #62b052;
            --cm-on: #171a21;
            --cm-slip: #f1e9da;
            --cm-slip-2: #3c4150;
            --cm-shade: rgba(0, 0, 0, 0.4);
        }

        /* The bar above takes the wall's colour, so the page reads as one room. */
        body > header.sticky {
            background-color: rgba(255, 250, 242, 0.9);
            border-bottom-color: rgba(31, 36, 48, 0.12);
        }
        .dark body > header.sticky {
            background-color: rgba(27, 31, 39, 0.9);
            border-bottom-color: rgba(255, 250, 242, 0.12);
        }

        #cm ::selection { background: var(--cm-sun); color: #1f2430; }
        #cm a:focus-visible,
        #cm summary:focus-visible,
        #cm .cm-chips input:focus-visible + label {
            outline: 3px solid var(--cm-ink);
            outline-offset: 3px;
            box-shadow: 0 0 0 9px var(--cm-sun);
        }

        .cm-wrap { width: min(100% - 2.5rem, 74rem); margin-inline: auto; }
        .cm-sec { padding-block: clamp(4rem, 8vw, 7rem); }
        .cm-wall { background: var(--cm-wall); }

        .cm-h {
            font-family: var(--cm-display);
            font-weight: 400;
            line-height: 1.08;
            letter-spacing: 0.005em;
            text-wrap: balance;
        }
        .cm-h2 { font-size: clamp(1.95rem, 4.6vw, 3.5rem); }
        .cm-h3 { font-size: clamp(1.3rem, 2.2vw, 1.6rem); line-height: 1.15; }
        .cm-lede { max-width: 40rem; color: var(--cm-ink-2); font-size: clamp(1.125rem, 1.5vw, 1.3rem); }
        .cm-body { color: var(--cm-ink-2); }
        .cm-small { color: var(--cm-ink-3); font-size: 1rem; }

        /* A word the heading leans on: a strip of paper laid under it. */
        .cm-hl {
            display: inline-block;
            padding: 0 0.26em 0.04em;
            border-radius: 0.3em 0.24em 0.32em 0.22em;
            background: var(--hl, var(--cm-sun));
            color: var(--cm-on);
            rotate: -1.4deg;
        }

        /* Wayfinding: the center's own signs. A number, an arrow, where you are. */
        .cm-sign {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.34rem 1rem 0.34rem 0.38rem;
            border-radius: 0.95rem 0.8rem 0.9rem 0.85rem;
            background: var(--paper, var(--cm-sun));
            color: var(--cm-on);
            font-family: var(--cm-display);
            font-size: 1.05rem;
            line-height: 1.2;
            rotate: -1.2deg;
        }
        .cm-sign b {
            display: inline-grid;
            place-items: center;
            min-width: 3.1rem;
            padding: 0.2rem 0.5rem;
            border-radius: 0.62rem;
            background: var(--cm-on);
            color: var(--paper, var(--cm-sun));
            font-weight: 400;
            font-size: 0.95rem;
            white-space: nowrap;
        }
        .cm-head { display: grid; gap: 1.25rem; margin-bottom: clamp(2.25rem, 5vw, 3.75rem); }
        .cm-head-row { display: grid; gap: 2rem; align-items: end; grid-template-columns: minmax(0, 1fr); }
        .cm-head-copy { display: grid; gap: 1.25rem; justify-items: start; }
        .cm-cluster { display: none; position: relative; }
        @media (min-width: 960px) {
            .cm-head-row { grid-template-columns: minmax(0, 1fr) 13em; }
            .cm-cluster { display: block; height: 9.5em; isolation: isolate; }
        }
        @media (min-width: 1200px) {
            .cm-head-row { grid-template-columns: minmax(0, 1fr) 17rem; }
            .cm-cluster { font-size: 1.3rem; }
        }

        .cm-tag {
            display: inline-block;
            padding: 0.12rem 0.7rem 0.14rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.875rem;
            letter-spacing: 0.03em;
            line-height: 1.5;
            color: var(--cm-on);
            vertical-align: middle;
        }
        .cm-tag-free { background: var(--cm-leaf); }
        .cm-tag-pro { background: var(--cm-tomato); }

        /* Buttons: a round pill of paper with a second sheet peeking out behind it. */
        .cm-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            min-height: 3.5rem;
            padding: 0.8rem 1.7rem;
            border-radius: 999px;
            background: var(--cm-tomato);
            color: var(--cm-on);
            font-family: var(--cm-display);
            font-size: 1.2rem;
            line-height: 1.2;
            text-align: center;
            transition: translate 0.2s ease;
        }
        .cm-btn::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            border-radius: inherit;
            background: var(--cm-sun);
            rotate: -2.5deg;
            translate: 0.25rem 0.35rem;
            transition: rotate 0.25s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.25s ease;
        }
        .cm-btn:hover { translate: 0 -2px; }
        .cm-btn:hover::before { rotate: 3deg; translate: 0.45rem 0.5rem; }
        .cm-btn svg { width: 1.25rem; height: 1.25rem; flex: none; }
        .cm-btn-ghost { background: transparent; color: var(--cm-ink); box-shadow: inset 0 0 0 0.19rem var(--cm-ink); }
        .cm-btn-ghost::before { display: none; }
        .cm-btn-ghost:hover { background: var(--cm-ink); color: var(--cm-ground); }
        #cm .cm-btn-ghost:focus-visible { box-shadow: inset 0 0 0 0.19rem var(--cm-ink), 0 0 0 9px var(--cm-sun); }
        .cm-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            color: var(--cm-ink);
            text-decoration: underline;
            text-decoration-color: var(--cm-tomato);
            text-decoration-thickness: 0.18rem;
            text-underline-offset: 0.28em;
        }
        .cm-link svg { width: 1.1rem; height: 1.1rem; transition: translate 0.2s ease; }
        .cm-link:hover svg { translate: 0.25rem 0; }

        /* ---------------------------------------------------------------
           Cut paper. One class for the scissors, one per shape. Where
           two sheets overlap they darken a little, as tissue does.
           --------------------------------------------------------------- */
        .cm-cut {
            position: absolute;
            display: block;
            background: var(--c);
            rotate: var(--rot, 0deg);
            box-shadow: 0 2px 5px var(--cm-shade);
            mix-blend-mode: multiply;
        }
        .dark #cm .cm-cut { mix-blend-mode: normal; }
        .cm-circle { aspect-ratio: 1; border-radius: 49% 51% 52% 48% / 51% 48% 52% 49%; }
        .cm-half { aspect-ratio: 2 / 1; border-radius: 50% 50% 2% 3% / 100% 100% 4% 5%; }
        .cm-arch { aspect-ratio: 3 / 4; border-radius: 50% 50% 5% 6% / 38% 38% 4% 5%; }
        .cm-petal { aspect-ratio: 1 / 1.7; border-radius: 4% 96% 6% 94% / 5% 95% 4% 96%; }
        .cm-quarter { aspect-ratio: 1; border-radius: 100% 5% 6% 4%; }
        .cm-stairs {
            aspect-ratio: 4 / 3;
            box-shadow: none;
            clip-path: polygon(0 100%, 0 76%, 25% 75%, 25% 51%, 50% 50%, 50% 26%, 75% 25%, 75% 1%, 100% 0, 100% 100%);
        }
        /* One sheet that never quite sits still. */
        .cm-blob {
            aspect-ratio: 1;
            border-radius: var(--cm-b) calc(100% - var(--cm-b)) calc(var(--cm-b) + 6%) calc(94% - var(--cm-b)) / calc(100% - var(--cm-b)) var(--cm-b) calc(96% - var(--cm-b)) calc(var(--cm-b) + 4%);
        }
        html.es-anim #cm .cm-blob { animation: cm-breathe 9s ease-in-out infinite alternate; }
        @keyframes cm-breathe {
            from { --cm-b: 40%; }
            to { --cm-b: 60%; }
        }
        /* The cut-outs lean a little as they pass, and the hero's settle onto the wall. */
        @keyframes cm-lean {
            from { rotate: calc(var(--rot, 0deg) - var(--lean, 5deg)); translate: 0 1rem; }
            to { rotate: calc(var(--rot, 0deg) + var(--lean, 5deg)); translate: 0 -1rem; }
        }
        @keyframes cm-settle {
            from { opacity: 0; transform: translate(var(--fx, 0), var(--fy, -2.5rem)) rotate(var(--fr, -18deg)) scale(0.7); }
            to { opacity: 1; transform: none; }
        }
        html.es-anim #cm .cm-hero .cm-cut:not(.cm-blob) {
            animation: cm-settle 0.9s cubic-bezier(0.2, 1.25, 0.35, 1) calc(var(--i, 0) * 80ms + 0.15s) both;
        }
        @supports (animation-timeline: view()) {
            html.es-anim #cm .cm-cut:not(.cm-blob) {
                animation: cm-lean 1ms linear both;
                animation-timeline: view();
            }
            html.es-anim #cm .cm-hero .cm-cut:not(.cm-blob) {
                animation: cm-settle 0.9s cubic-bezier(0.2, 1.25, 0.35, 1) calc(var(--i, 0) * 80ms + 0.15s) both, cm-lean 1ms linear both;
                animation-timeline: auto, view();
            }
        }

        /* ---------------------------------------------------------------
           Hero: the board, cut from one sheet, and what is pinned to it
           --------------------------------------------------------------- */
        .cm-hero { position: relative; overflow: clip; padding-block: clamp(2.5rem, 6vw, 5rem) clamp(3rem, 6vw, 5rem); }
        .cm-hero-grid { display: grid; gap: 3rem; align-items: center; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1000px) {
            .cm-hero-grid { grid-template-columns: minmax(0, 1.22fr) minmax(0, 0.78fr); gap: 2.5rem; }
        }
        .cm-eyebrow {
            display: inline-block;
            margin-bottom: 1.4rem;
            padding: 0.4rem 0.95rem;
            border-radius: 0.9rem 0.75rem 0.85rem 0.8rem;
            background: var(--cm-teal);
            color: var(--cm-on);
            font-family: var(--cm-text);
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.35;
            rotate: -1deg;
        }
        .cm-hero-copy { container-type: inline-size; min-width: 0; }
        .cm-h1 { font-size: clamp(2.2rem, 9.5cqi, 4.9rem); line-height: 1.05; }
        .cm-h1 > span { display: block; }
        .cm-hero .cm-lede { margin-top: 1.6rem; }
        .cm-cta { display: flex; flex-wrap: wrap; gap: 1rem 1.25rem; margin-top: 2.25rem; isolation: isolate; }

        .cm-art { position: relative; isolation: isolate; width: min(100%, 35rem); margin-inline: auto; padding: 7% 12% 6%; }
        .cm-art .cm-front { z-index: 2; }
        .cm-board {
            position: relative;
            z-index: 1;
            container-type: inline-size;
            padding: min(7.4rem, 27vw) 1.1rem 1.25rem;
            background: var(--cm-sun);
            border-radius: 50% 50% 1.7rem 1.5rem / min(9.5rem, 34vw) min(9.5rem, 34vw) 1.6rem 1.7rem;
            box-shadow: 0 0.6rem 1.4rem -0.6rem var(--cm-shade);
        }
        .cm-board-cap {
            position: absolute;
            inset: min(3.1rem, 11vw) 0 auto 0;
            text-align: center;
            font-family: var(--cm-display);
            font-size: clamp(1.15rem, 7.5cqi, 1.9rem);
            line-height: 1;
            color: var(--cm-on);
        }
        .cm-slips { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.7rem; }
        .cm-slip {
            display: block;
            padding: 0.7rem 0.75rem 0.75rem;
            border-radius: 0.95rem 0.75rem 1rem 0.8rem;
            background: var(--cm-slip);
            color: #1f2430;
            rotate: var(--rot, 0deg);
            box-shadow: 0 2px 4px rgba(31, 36, 48, 0.16);
            transition: rotate 0.3s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.3s ease;
        }
        .cm-board:hover .cm-slip { rotate: 0deg; }
        .cm-slip-day { display: flex; align-items: center; gap: 0.4rem; font-weight: 700; font-size: clamp(0.66rem, 3.1cqi, 0.82rem); letter-spacing: 0.05em; text-transform: uppercase; color: var(--cm-slip-2); }
        .cm-slip-name { display: block; margin-top: 0.3rem; font-family: var(--cm-display); font-size: clamp(0.9rem, 4.7cqi, 1.22rem); line-height: 1.12; }
        .cm-slip-sub { display: block; margin-top: 0.3rem; font-size: clamp(0.7rem, 3.3cqi, 0.86rem); line-height: 1.3; color: var(--cm-slip-2); }
        .cm-slip-link { background: var(--cm-tomato); color: var(--cm-on); }
        .cm-slip-link .cm-slip-day,
        .cm-slip-link .cm-slip-sub { color: var(--cm-on); }
        .cm-slip-url { display: block; margin-top: 0.35rem; font-weight: 700; font-size: clamp(0.66rem, 3.25cqi, 0.86rem); line-height: 1.25; overflow-wrap: anywhere; }
        .cm-art-cap { margin-top: 1.5rem; text-align: center; color: var(--cm-ink-3); font-size: 1rem; text-wrap: balance; }

        /* The strand marks: a shape as well as a colour, so nothing rests on colour alone. */
        .cm-mk { display: inline-block; flex: none; width: var(--mk, 1.6rem); aspect-ratio: 1; background: var(--c, var(--cm-teal)); }
        .cm-k-wellbeing { --c: var(--cm-teal); }
        .cm-k-youth { --c: var(--cm-sun); }
        .cm-k-learning { --c: var(--cm-tomato); }
        .cm-k-sport { --c: var(--cm-leaf); }
        .cm-k-wellbeing .cm-mk, .cm-mk.cm-k-wellbeing { border-radius: 50%; }
        .cm-k-youth .cm-mk, .cm-mk.cm-k-youth { border-radius: 50% 50% 10% 12% / 62% 62% 10% 12%; }
        .cm-k-learning .cm-mk, .cm-mk.cm-k-learning { border-radius: 6% 94% 8% 92% / 8% 92% 6% 94%; }
        .cm-k-sport .cm-mk, .cm-mk.cm-k-sport { border-radius: 24% 20% 26% 22%; }

        /* ---------------------------------------------------------------
           The week: programs down the side, days across, strands as shapes
           --------------------------------------------------------------- */
        .cm-week { container-type: inline-size; }
        .cm-week-card { background: var(--cm-surface); border-radius: 2rem 1.8rem 2.1rem 1.9rem; box-shadow: 0 0 0 2px var(--cm-line); }
        .cm-week-pad { padding: clamp(1rem, 3vw, 2rem); }
        .cm-chips { position: relative; display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; margin: 0 0 1.25rem; padding: 0; border: 0; }
        .cm-chips-label { font-weight: 700; color: var(--cm-ink-3); font-size: 1rem; }
        .cm-chips label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 2.75rem;
            padding: 0.3rem 1rem;
            border-radius: 999px;
            box-shadow: inset 0 0 0 2px var(--cm-line);
            font-weight: 700;
            font-size: 1rem;
            color: var(--cm-ink);
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .cm-chips label:hover { box-shadow: inset 0 0 0 2px var(--cm-ink); }
        .cm-chips input:checked + label { background: var(--cm-ink); color: var(--cm-ground); box-shadow: none; }
        .cm-chips .cm-mk { --mk: 1.05rem; }

        .cm-tt { position: relative; width: 100%; display: grid; grid-template-columns: minmax(12.5rem, 2fr) repeat(7, minmax(2.5rem, 1fr)) minmax(10rem, 1.5fr); text-align: start; }
        .cm-tt thead,
        .cm-tt tbody,
        .cm-tt tr { display: grid; grid-column: 1 / -1; grid-template-columns: minmax(12.5rem, 2fr) repeat(7, minmax(2.5rem, 1fr)) minmax(10rem, 1.5fr); }
        @supports (grid-template-columns: subgrid) {
            .cm-tt thead,
            .cm-tt tbody,
            .cm-tt tr { grid-template-columns: subgrid; }
        }
        .cm-tt th,
        .cm-tt td { position: relative; display: block; min-width: 0; }
        .cm-tt thead { position: sticky; top: 4rem; z-index: 2; background: var(--cm-surface); border-radius: 0.9rem; }
        .cm-tt thead th { padding: 0.75rem 0.5rem; font-family: var(--cm-display); font-weight: 400; font-size: 1rem; color: var(--cm-ink-3); text-align: center; }
        .cm-tt thead th:first-child,
        .cm-tt thead th:last-child { text-align: start; padding-inline: 1rem; }
        .cm-tt tbody tr { align-items: center; border-radius: 1.1rem 1rem 1.15rem 0.95rem; transition: background-color 0.2s ease; }
        .cm-tt tbody tr:nth-child(odd) { background: var(--cm-faint); }
        .cm-tt tbody tr:hover { background: color-mix(in srgb, var(--c) 22%, transparent); }
        .cm-tt th[scope="row"] { padding: 0.9rem 1rem; font-weight: 400; text-align: start; }
        .cm-tt-name { display: block; font-family: var(--cm-display); font-size: 1.15rem; line-height: 1.2; color: var(--cm-ink); }
        .cm-tt-strand { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.2rem; font-weight: 700; font-size: 0.875rem; color: var(--cm-ink-3); }
        .cm-tt-strand .cm-mk { --mk: 0.8rem; }
        .cm-tt td[data-day] { display: grid; place-items: center; padding-block: 0.8rem; }
        .cm-tt .cm-off { display: block; width: 0.42rem; aspect-ratio: 1; border-radius: 50%; background: var(--cm-line); }
        .cm-tt-sign { padding: 0.9rem 1rem; font-weight: 700; font-size: 1rem; color: var(--cm-ink-2); }
        html.es-anim #cm [data-reveal]:not(.is-revealed) .cm-tt td .cm-mk { scale: 0; }
        .cm-tt td .cm-mk { transition: scale 0.5s cubic-bezier(0.34, 1.6, 0.64, 1) calc(var(--n, 0) * 45ms + 0.2s); }

        /* A strand has a link of its own that shows only its programs: the chips do the same here. */
        #cm .cm-week:has(#cm-s-wellbeing:checked) tr[data-strand]:not([data-strand="Wellbeing"]),
        #cm .cm-week:has(#cm-s-youth:checked) tr[data-strand]:not([data-strand="Youth"]),
        #cm .cm-week:has(#cm-s-learning:checked) tr[data-strand]:not([data-strand="Learning"]),
        #cm .cm-week:has(#cm-s-sport:checked) tr[data-strand]:not([data-strand="Sport"]) { display: none; }

        /* On a narrow sheet the grid folds into one card a program, a strip of seven days in each. */
        @container (max-width: 46rem) {
            .cm-tt,
            .cm-tt tbody,
            .cm-tt tr { grid-template-columns: repeat(7, minmax(0, 1fr)); }
            .cm-tt thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
            .cm-tt tbody { row-gap: 0.6rem; }
            .cm-tt tbody tr { padding: 0.95rem 0.8rem 0.9rem; row-gap: 0.55rem; background: var(--cm-faint); }
            .cm-tt th[scope="row"] { grid-column: 1 / -1; display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.25rem 0.75rem; padding: 0 0.2rem; }
            .cm-tt-strand { margin-top: 0; }
            .cm-tt td[data-day] { padding: 0; gap: 0.3rem; }
            .cm-tt td[data-day]::before { content: attr(data-day); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.03em; color: var(--cm-ink-3); }
            .cm-tt td .cm-mk { --mk: 1.45rem; }
            .cm-tt .cm-off { margin-block: 0.5rem; }
            .cm-tt-sign { grid-column: 1 / -1; padding: 0.15rem 0.2rem 0; }
        }

        .cm-notes { display: grid; gap: 1rem; margin-top: 1.5rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 820px) { .cm-notes { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .cm-note { padding: 1.1rem 1.2rem 1.2rem; border-radius: 1.3rem 1.1rem 1.35rem 1.15rem; background: color-mix(in srgb, var(--c) 24%, var(--cm-surface)); }
        .cm-note strong { display: block; font-family: var(--cm-display); font-weight: 400; font-size: 1.15rem; line-height: 1.2; color: var(--cm-ink); }
        .cm-note span { display: block; margin-top: 0.35rem; color: var(--cm-ink-2); font-size: 1rem; }

        /* A plain-spoken notice: a tinted sheet with a border all the way round. */
        .cm-notice { margin-top: 1.75rem; padding: 1.25rem 1.4rem 1.35rem; border-radius: 1.4rem 1.25rem 1.5rem 1.2rem; background: color-mix(in srgb, var(--cm-sun) 24%, var(--cm-ground)); box-shadow: inset 0 0 0 2px color-mix(in srgb, var(--cm-sun) 70%, var(--cm-ink)); }
        .cm-notice strong { display: block; font-family: var(--cm-display); font-weight: 400; font-size: 1.2rem; color: var(--cm-ink); }
        .cm-notice span { display: block; margin-top: 0.35rem; color: var(--cm-ink-2); font-size: 1.0625rem; max-width: 52rem; }
        .cm-freeline { margin-top: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.75rem; color: var(--cm-ink-2); font-size: 1.0625rem; }

        /* ---------------------------------------------------------------
           The doors: six of them down the corridor, each on its hinge
           --------------------------------------------------------------- */
        .cm-doors { display: grid; gap: 3rem 2rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 720px) { .cm-doors { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1080px) { .cm-doors { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .cm-door { display: flex; flex-direction: column; min-width: 0; }
        .cm-door-top { position: relative; display: flex; align-items: flex-end; gap: 1rem; padding: 0 0.5rem 0 1.4rem; margin-bottom: 1.4rem; }
        .cm-door-top::after { content: ""; position: absolute; inset: auto 0 -0.5rem 0; height: 0.5rem; border-radius: 999px; background: var(--cm-ink); }
        .cm-doorway { position: relative; flex: none; width: 8.6rem; aspect-ratio: 3 / 4.15; perspective: 62rem; perspective-origin: 20% 100%; }
        .cm-room {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            border-radius: 50% 50% 0.3rem 0.3rem / 34% 34% 0.3rem 0.3rem;
            background: radial-gradient(120% 80% at 50% 100%, #fff8dc 0%, #ffe89c 55%, #ffd45c 100%);
            box-shadow: 0 0 0 0.36rem var(--cm-ink);
            color: #1f2430;
        }
        .cm-room svg { width: 32%; height: auto; justify-self: end; margin-inline-end: 10%; translate: 0 12%; transition: translate 0.5s cubic-bezier(0.3, 1.25, 0.4, 1), scale 0.5s cubic-bezier(0.34, 1.6, 0.64, 1); }
        .cm-leaf {
            position: absolute;
            inset: 0;
            border-radius: 50% 50% 0.3rem 0.3rem / 34% 34% 0.3rem 0.3rem;
            background: linear-gradient(90deg, rgba(31, 36, 48, 0.16), rgba(31, 36, 48, 0) 38%), var(--paper);
            color: var(--cm-on);
            transform-origin: 0 50%;
            transform: rotateY(-64deg);
            transition: transform 0.7s cubic-bezier(0.3, 1.25, 0.4, 1);
            box-shadow: 0.25rem 0 0.6rem -0.2rem rgba(31, 36, 48, 0.35);
        }
        .cm-leaf::before { content: ""; position: absolute; left: 50%; top: 17%; width: 36%; aspect-ratio: 1; translate: -50% 0; border-radius: 50%; background: rgba(255, 255, 255, 0.42); }
        .cm-leaf::after { content: ""; position: absolute; right: 11%; top: 57%; width: 0.75rem; aspect-ratio: 1; border-radius: 50%; background: var(--cm-on); }
        .cm-leaf b { position: absolute; left: 0; right: 0; bottom: 9%; text-align: center; font-family: var(--cm-display); font-weight: 400; font-size: 1.5rem; line-height: 1; }
        .cm-door-mat { flex: 1; align-self: flex-end; height: 0.55rem; margin-bottom: 0.05rem; border-radius: 999px 999px 0 0; background: var(--paper); opacity: 0.9; }
        @media (hover: hover) {
            html #cm .cm-door:hover .cm-leaf { animation: none; transform: rotateY(-82deg); }
            .cm-door:hover .cm-room svg { translate: -42% 12%; scale: 1.22; }
        }
        @supports (animation-timeline: view()) {
            html.es-anim #cm .cm-leaf {
                animation: cm-open 1ms cubic-bezier(0.3, 1, 0.4, 1) both;
                animation-timeline: view();
                animation-range: entry 15% cover 40%;
            }
        }
        @keyframes cm-open {
            from { transform: rotateY(-4deg); }
            to { transform: rotateY(-64deg); }
        }
        .cm-door h3 { color: var(--cm-ink); }
        .cm-door-body { margin-top: 0.6rem; color: var(--cm-ink-2); font-size: 1.0625rem; }
        .cm-door-foot { margin-top: auto; padding-top: 1rem; }
        .cm-doors-note { max-width: 46rem; margin: clamp(2.5rem, 5vw, 3.5rem) auto 0; padding: 1.2rem 1.4rem; border-radius: 1.4rem 1.2rem 1.45rem 1.25rem; background: var(--cm-surface); box-shadow: 0 0 0 2px var(--cm-line); color: var(--cm-ink-2); font-size: 1.0625rem; }

        /* ---------------------------------------------------------------
           Hall hire: the tray on the desk, and what has been pinned up
           --------------------------------------------------------------- */
        .cm-hall { display: grid; gap: 3rem; align-items: start; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1000px) { .cm-hall { grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr); gap: 4rem; } }
        .cm-hall-copy { display: grid; gap: 1.25rem; justify-items: start; }
        @media (min-width: 1000px) { .cm-hall > .cm-card { order: -1; } }
        .cm-card { background: var(--cm-surface); border-radius: 2rem 1.8rem 2.1rem 1.9rem; box-shadow: 0 0 0 2px var(--cm-line); padding: clamp(1.25rem, 3vw, 2rem); }
        .cm-card-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; margin-bottom: 1.25rem; }
        .cm-card-top h3 { color: var(--cm-ink); }
        .cm-kicker { display: block; margin-bottom: 0.6rem; font-weight: 700; font-size: 0.875rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--cm-ink-3); }
        .cm-pinboard { position: relative; padding: 1.5rem 1.1rem 1.1rem; border-radius: 1.4rem 1.2rem 1.5rem 1.25rem; background: var(--cm-teal); }
        .cm-pin { position: absolute; left: 50%; top: -0.55rem; width: 1.1rem; aspect-ratio: 1; translate: -50% 0; border-radius: 50%; background: var(--cm-tomato); box-shadow: 0 2px 3px rgba(31, 36, 48, 0.35); }
        .cm-req { position: relative; display: block; padding: 0.85rem 1rem 0.9rem; border-radius: 1rem 0.85rem 1.05rem 0.9rem; background: var(--cm-slip); color: #1f2430; box-shadow: 0 2px 4px rgba(31, 36, 48, 0.16); }
        .cm-req-state { display: inline-block; padding: 0 0.6rem; border-radius: 999px; font-weight: 700; font-size: 0.8125rem; letter-spacing: 0.04em; text-transform: uppercase; background: var(--cm-leaf); color: var(--cm-on); }
        .cm-req-wait .cm-req-state { background: var(--cm-sun); }
        .cm-req-name { display: block; margin-top: 0.4rem; font-family: var(--cm-display); font-size: 1.15rem; line-height: 1.2; }
        .cm-req-when { display: block; margin-top: 0.15rem; font-size: 0.95rem; color: var(--cm-slip-2); }
        .cm-tray { position: relative; display: grid; gap: 0.6rem; padding: 1rem 1.1rem 1.5rem; border-radius: 1.2rem 1.2rem 1.5rem 1.4rem; background: color-mix(in srgb, var(--cm-ink) 9%, var(--cm-surface)); }
        .cm-tray::after { content: ""; position: absolute; inset: auto 0 0 0; height: 0.9rem; border-radius: 0 0 1.5rem 1.4rem; background: color-mix(in srgb, var(--cm-ink) 22%, var(--cm-surface)); }
        .cm-tray .cm-req:nth-child(1) { rotate: -0.8deg; }
        .cm-tray .cm-req:nth-child(2) { rotate: 0.9deg; }
        .cm-card-foot { margin-top: 1.25rem; color: var(--cm-ink-3); font-size: 1rem; }
        .cm-list { display: grid; gap: 1.1rem; margin-top: 0.5rem; }
        .cm-list li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: 0.9rem; align-items: start; }
        .cm-list li > .cm-mk { --mk: 1.35rem; margin-top: 0.2rem; }
        .cm-list strong { display: block; font-weight: 700; color: var(--cm-ink); }
        .cm-list span span { display: block; color: var(--cm-ink-2); }

        /* ---------------------------------------------------------------
           Sign-ups: sixteen cushions on the floor, and the price list
           --------------------------------------------------------------- */
        .cm-two { display: grid; gap: 1.75rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 960px) { .cm-two { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .cm-class { display: flex; flex-direction: column; }
        .cm-class-intro { color: var(--cm-ink-2); }
        .cm-mock { margin-block: 1.5rem; padding: 1.25rem 1.3rem 1.3rem; border-radius: 1.4rem 1.25rem 1.5rem 1.2rem; background: color-mix(in srgb, var(--c) 20%, var(--cm-surface)); }
        .cm-count { display: flex; align-items: baseline; gap: 0.6rem; color: var(--cm-ink); }
        .cm-count b { font-family: var(--cm-display); font-weight: 400; font-size: 3.4rem; line-height: 0.9; }
        .cm-count span { font-weight: 700; color: var(--cm-ink-2); }
        .cm-seats { display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: 0.5rem; max-width: 24rem; margin-top: 1.1rem; }
        .cm-seat { aspect-ratio: 1; border-radius: 50% 48% 52% 49%; box-shadow: inset 0 0 0 2px var(--cm-line); }
        .cm-seat-on { background: var(--cm-teal); box-shadow: none; transition: scale 0.45s cubic-bezier(0.34, 1.7, 0.64, 1) calc(var(--n, 0) * 55ms + 0.25s); }
        html.es-anim #cm [data-reveal]:not(.is-revealed) .cm-seat-on { scale: 0; }
        .cm-mock-note { margin-top: 0.9rem; color: var(--cm-ink-2); font-size: 1rem; }
        .cm-prices { display: grid; gap: 0.15rem; }
        .cm-price { display: flex; align-items: baseline; gap: 0.75rem; padding-block: 0.55rem; border-bottom: 2px dotted var(--cm-line); color: var(--cm-ink); }
        .cm-price span:first-child { flex: 1; min-width: 0; font-weight: 700; }
        .cm-price span:last-child { font-family: var(--cm-display); font-size: 1.25rem; }
        .cm-ticks { display: grid; gap: 0.7rem; margin-top: auto; }
        .cm-ticks li { display: grid; grid-template-columns: 1.6rem minmax(0, 1fr); gap: 0.7rem; align-items: start; color: var(--cm-ink-2); font-size: 1.0625rem; }
        .cm-ticks i { display: grid; place-items: center; width: 1.6rem; aspect-ratio: 1; margin-top: 0.1rem; border-radius: 50%; background: var(--c, var(--cm-leaf)); color: var(--cm-on); }
        .cm-ticks svg { width: 0.95rem; height: 0.95rem; }
        .cm-proline { max-width: 48rem; margin: 2rem auto 0; text-align: center; color: var(--cm-ink-2); font-size: 1.0625rem; }

        /* ---------------------------------------------------------------
           Who it is for: six centers, each with its own handful of paper
           --------------------------------------------------------------- */
        .cm-who { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 680px) { .cm-who { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .cm-who { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .cm-who-card { display: flex; flex-direction: column; overflow: hidden; background: var(--cm-surface); border-radius: 2rem 1.8rem 2.1rem 1.9rem; box-shadow: 0 0 0 2px var(--cm-line); transition: translate 0.25s ease; }
        .cm-who-card:hover { translate: 0 -0.3rem; }
        .cm-who-art { position: relative; height: 8.5rem; isolation: isolate; background: color-mix(in srgb, var(--tint) 20%, var(--cm-surface)); }
        .cm-who-card:hover .cm-who-art .cm-cut { rotate: calc(var(--rot, 0deg) + 8deg); }
        .cm-who-art .cm-cut { transition: rotate 0.5s cubic-bezier(0.34, 1.5, 0.64, 1); animation: none !important; }
        .cm-who-text { display: flex; flex: 1; flex-direction: column; padding: 1.3rem 1.4rem 1.5rem; }
        .cm-who-text h3 { color: var(--cm-ink); }
        .cm-who-text p { margin-top: 0.5rem; color: var(--cm-ink-2); font-size: 1.0625rem; }
        .cm-who-text .cm-link { margin-top: auto; padding-top: 1rem; align-self: flex-start; }

        /* ---------------------------------------------------------------
           Three steps up
           --------------------------------------------------------------- */
        .cm-steps { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); align-items: end; }
        .cm-step { position: relative; display: flex; flex-direction: column; padding: 1.5rem 1.5rem 1.6rem; border-radius: 1.9rem 1.7rem 0.6rem 0.6rem; background: var(--paper); color: var(--cm-on); }
        .cm-step b { font-family: var(--cm-display); font-weight: 400; font-size: 3.6rem; line-height: 0.9; }
        .cm-step h3 { margin-top: 0.8rem; }
        .cm-step p { margin-top: 0.5rem; font-size: 1.0625rem; }
        @media (min-width: 900px) {
            .cm-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0; }
            .cm-step { min-height: calc(15rem + var(--up) * 3.25rem); border-radius: 1.9rem 1.7rem 0 0; }
            .cm-step + .cm-step { margin-inline-start: -0.4rem; }
        }
        .cm-steps-floor { height: 0.6rem; border-radius: 999px; background: var(--cm-ink); }

        /* ---------------------------------------------------------------
           The directory by the lift: key features
           --------------------------------------------------------------- */
        .cm-dir-grid { display: grid; gap: 2.5rem 4rem; align-items: start; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 980px) { .cm-dir-grid { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .cm-dir-side { display: grid; gap: 1.25rem; justify-items: start; }
        .cm-dir { display: grid; gap: 0.7rem; }
        .cm-dir a {
            display: grid;
            grid-template-columns: 3rem minmax(0, 1fr) 1.5rem;
            gap: 1rem;
            align-items: center;
            padding: 0.85rem 1.1rem 0.85rem 0.85rem;
            border-radius: 1.3rem 1.15rem 1.35rem 1.2rem;
            background: var(--cm-surface);
            box-shadow: 0 0 0 2px var(--cm-line);
            transition: translate 0.2s ease, box-shadow 0.2s ease;
        }
        .cm-dir a:hover { translate: 0.4rem 0; box-shadow: 0 0 0 2px var(--cm-ink); }
        .cm-dir i { display: grid; place-items: center; width: 3rem; aspect-ratio: 1; border-radius: 0.9rem 0.8rem 0.95rem 0.75rem; background: var(--paper); color: var(--cm-on); font-family: var(--cm-display); font-style: normal; font-size: 1.3rem; }
        .cm-dir strong { display: block; font-family: var(--cm-display); font-weight: 400; font-size: 1.2rem; line-height: 1.2; color: var(--cm-ink); }
        .cm-dir small { display: block; margin-top: 0.15rem; font-size: 1rem; color: var(--cm-ink-2); }
        .cm-dir svg { width: 1.4rem; height: 1.4rem; color: var(--cm-ink); }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the paper changes.
           --------------------------------------------------------------- */
        #cm .cm-plans > section { background: var(--cm-wall); }
        #cm .cm-plans h2 { font-family: var(--cm-display); font-weight: 400; letter-spacing: 0.005em; line-height: 1.1; font-size: clamp(1.9rem, 4.2vw, 3rem); color: var(--cm-ink); }
        #cm .cm-plans h2 + p { color: var(--cm-ink-2); font-size: 1.0625rem; }
        #cm .cm-plans .grid > div { background: var(--cm-surface); border: 0; border-radius: 2rem 1.8rem 2.1rem 1.9rem; box-shadow: 0 0 0 2px var(--cm-line); color: var(--cm-ink); }
        #cm .cm-plans .grid > div span,
        #cm .cm-plans .grid > div p,
        #cm .cm-plans .grid > div li { color: var(--cm-ink-2); }
        #cm .cm-plans .grid > div .text-3xl { font-family: var(--cm-display); font-weight: 400; font-size: 2.6rem; color: var(--cm-ink); }
        #cm .cm-plans .grid > div .uppercase { color: var(--cm-ink); }
        #cm .cm-plans .grid > div svg { color: var(--cm-ink); }
        #cm .cm-plans .grid > div:nth-child(2) { background: var(--cm-sun); box-shadow: none; }
        #cm .cm-plans .grid > div:nth-child(2) span,
        #cm .cm-plans .grid > div:nth-child(2) p,
        #cm .cm-plans .grid > div:nth-child(2) li,
        #cm .cm-plans .grid > div:nth-child(2) svg,
        #cm .cm-plans .grid > div:nth-child(2) .text-3xl { color: var(--cm-on); }
        #cm .cm-plans .grid > div:nth-child(2) .rounded-full { background: var(--cm-on); color: var(--cm-sun); }
        #cm .cm-plans a.font-medium { color: var(--cm-ink); font-weight: 700; text-decoration: underline; text-decoration-color: var(--cm-tomato); text-decoration-thickness: 0.18rem; text-underline-offset: 0.28em; }
        #cm .cm-plans a.rounded-2xl { background: var(--cm-tomato); color: var(--cm-on); border-radius: 999px; box-shadow: none; font-family: var(--cm-display); font-weight: 400; font-size: 1.15rem; }

        #cm .cm-keep > section { background: var(--cm-wall); border-top: 0; }
        #cm .cm-keep h2 { font-family: var(--cm-display); font-weight: 400; font-size: clamp(1.8rem, 3.6vw, 2.6rem); line-height: 1.1; color: var(--cm-ink); }
        #cm .cm-keep p.uppercase { color: var(--cm-ink-3); font-weight: 700; }
        #cm .cm-keep .grid > a { background: var(--cm-surface); border: 0; border-radius: 1.6rem 1.45rem 1.7rem 1.5rem; box-shadow: 0 0 0 2px var(--cm-line); }
        #cm .cm-keep .grid > a:hover { box-shadow: 0 0 0 2px var(--cm-ink); }
        #cm .cm-keep .grid > a > span:first-child { display: none; }
        #cm .cm-keep .grid > a h3,
        #cm .cm-keep .grid > a:hover h3 { color: var(--cm-ink); font-family: var(--cm-display); font-weight: 400; font-size: 1.15rem; }
        #cm .cm-keep .grid > a p { color: var(--cm-ink-2); }
        #cm .cm-keep .grid > a > span:last-child,
        #cm .cm-keep a.self-start { color: var(--cm-ink); font-weight: 700; }

        /* ---------------------------------------------------------------
           Down the road: four more doors
           --------------------------------------------------------------- */
        .cm-near-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1.25rem; margin-bottom: 2.25rem; }
        .cm-near { display: grid; gap: 1.25rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 900px) { .cm-near { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.75rem; } }
        .cm-near a {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            min-height: 12.5rem;
            padding: 1.25rem 1.1rem 1.2rem;
            border-radius: 50% 50% 1.2rem 1.1rem / 5.5rem 5.5rem 1.2rem 1.1rem;
            background: var(--paper);
            color: var(--cm-on);
            transition: translate 0.25s cubic-bezier(0.34, 1.5, 0.64, 1), rotate 0.25s cubic-bezier(0.34, 1.5, 0.64, 1);
        }
        .cm-near a:hover { translate: 0 -0.4rem; rotate: -1.5deg; }
        .cm-near small { font-weight: 700; font-size: 0.875rem; }
        .cm-near strong { font-family: var(--cm-display); font-weight: 400; font-size: clamp(1.15rem, 2.4vw, 1.6rem); line-height: 1.12; }
        .cm-near svg { width: 1.4rem; height: 1.4rem; margin-top: 0.6rem; }

        /* ---------------------------------------------------------------
           Asked at the front desk
           --------------------------------------------------------------- */
        .cm-faq-grid { display: grid; gap: 2.5rem 4rem; align-items: start; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 1000px) {
            .cm-faq-grid { grid-template-columns: minmax(0, 0.66fr) minmax(0, 1.34fr); }
            .cm-faq-side { position: sticky; top: 6.5rem; }
        }
        .cm-faq-side { display: grid; gap: 1.25rem; justify-items: start; }
        .cm-faq-side .cm-h2 { font-size: clamp(1.95rem, 3.3vw, 2.75rem); }
        .cm-desk { display: none; }
        @media (min-width: 1000px) { .cm-desk { display: block; position: relative; width: 13em; height: 9em; margin-top: 1rem; font-size: 1.25rem; isolation: isolate; } }
        .cm-qs { display: grid; gap: 0.9rem; }
        .cm-qs details { background: var(--cm-surface); border-radius: 1.5rem 1.35rem 1.6rem 1.4rem; box-shadow: 0 0 0 2px var(--cm-line); }
        .cm-qs details[open] { box-shadow: 0 0 0 2px var(--cm-ink); }
        .cm-qs summary { display: grid; grid-template-columns: minmax(0, 1fr) 2.4rem; gap: 1rem; align-items: center; padding: 1.1rem 1.1rem 1.1rem 1.4rem; cursor: pointer; border-radius: inherit; }
        .cm-qs h3 { font-family: var(--cm-display); font-weight: 400; font-size: 1.2rem; line-height: 1.25; color: var(--cm-ink); }
        .cm-qs summary i { position: relative; width: 2.4rem; aspect-ratio: 1; border-radius: 50%; background: var(--cm-sun); transition: rotate 0.3s cubic-bezier(0.34, 1.5, 0.64, 1), background-color 0.2s ease; }
        .cm-qs summary i::before,
        .cm-qs summary i::after { content: ""; position: absolute; inset: calc(50% - 0.09rem) 28% auto 28%; height: 0.18rem; border-radius: 999px; background: var(--cm-on); }
        .cm-qs summary i::after { rotate: 90deg; transition: rotate 0.3s ease; }
        .cm-qs details[open] summary i { background: var(--cm-teal); rotate: 180deg; }
        .cm-qs details[open] summary i::after { rotate: 0deg; }
        .cm-qs details p { padding: 0 1.4rem 1.4rem; color: var(--cm-ink-2); font-size: 1.0625rem; max-width: 46rem; }

        /* ---------------------------------------------------------------
           The newest card: pin yours up. A sunflower sheet in both modes.
           --------------------------------------------------------------- */
        .cm-fin { position: relative; overflow: clip; isolation: isolate; background: #ffc83d; color: #1f2430; padding-block: clamp(4rem, 9vw, 7.5rem); --cm-on: #1f2430; }
        #cm .cm-fin .cm-cut { mix-blend-mode: multiply; z-index: -1; }
        .cm-fin-in { position: relative; display: grid; justify-items: center; text-align: center; }
        .cm-fin .cm-sign { --paper: #fffaf2; }
        .cm-fin .cm-h2 { margin-top: 1.5rem; max-width: 46rem; color: #1f2430; }
        .cm-fin .cm-hl { --hl: #ff5a4e; }
        .cm-fin-lede { margin-top: 1.25rem; max-width: 36rem; font-size: clamp(1.125rem, 1.6vw, 1.3rem); color: #1f2430; }
        .cm-newcard {
            position: relative;
            width: min(100%, 22rem);
            margin-bottom: 2.25rem;
            padding: 1.35rem 1.2rem 1.1rem;
            border-radius: 1.2rem 1rem 1.3rem 1.05rem;
            background: #fffdf8;
            color: #1f2430;
            text-align: start;
            rotate: -2.5deg;
            box-shadow: 0 0.5rem 1.2rem -0.5rem rgba(31, 36, 48, 0.45);
        }
        .cm-newcard small { display: block; font-weight: 700; font-size: 0.8125rem; letter-spacing: 0.06em; text-transform: uppercase; color: #4b5160; }
        .cm-newcard strong { display: block; margin-top: 0.25rem; font-family: var(--cm-display); font-weight: 400; font-size: clamp(0.95rem, 4.2vw, 1.2rem); line-height: 1.2; }
        .cm-newcard strong span { white-space: nowrap; }
        .cm-claim-row { display: grid; gap: 0.9rem; width: min(100%, 40rem); margin-top: 2.25rem; grid-template-columns: minmax(0, 1fr); isolation: isolate; }
        @media (min-width: 640px) { .cm-claim-row { grid-template-columns: minmax(0, 1fr) auto; } }
        #cm .cm-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.4rem;
            border-radius: 999px;
            border: 0;
            background: #fffdf8;
            box-shadow: inset 0 0 0 0.19rem #1f2430;
            font-weight: 700;
            font-size: clamp(1rem, 3.6vw, 1.15rem);
            transition: box-shadow 0.2s ease;
        }
        #cm .cm-claim:focus-within { border-color: transparent; box-shadow: inset 0 0 0 0.19rem #1f2430, 0 0 0 0.4rem #fffaf2; }
        #cm .cm-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: #1f2430;
            box-shadow: none;
            outline: none;
        }
        #cm .cm-claim input::placeholder { color: #6b7180; }
        .cm-claim span { flex: none; color: #4b5160; user-select: none; }
        #cm .cm-fin .cm-btn { background: #1f2430; color: #fffaf2; }
        #cm .cm-fin .cm-btn::before { background: #ff5a4e; }
        .cm-fin-note { margin-top: 1.5rem; font-weight: 700; font-size: 1rem; color: #1f2430; }
        #cm .cm-fin a:focus-visible { box-shadow: 0 0 0 9px #fffaf2; outline-color: #1f2430; }

        /* ---------------------------------------------------------------
           The floor plan by the door: section rail, on wide screens only
           --------------------------------------------------------------- */
        .cm-rail { display: none; }
        @media (min-width: 1400px) {
            /* The nav is a box the size of the page that clips the rail, so the rail stays fixed to
               the screen and still ends where the page does instead of riding over the site footer.
               A link is its dot and nothing else: the label hangs beside it, out of the link's box,
               so a label nobody can see never takes a click meant for the page under it. */
            .cm-rail { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .cm-rail ol { position: fixed; right: 0.925rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.8rem; justify-items: end; }
            .cm-rail a { position: relative; display: flex; align-items: center; padding: 0.375rem; border-radius: 999px; color: var(--cm-ink); font-weight: 700; font-size: 0.875rem; pointer-events: auto; }
            .cm-rail a::after { content: ""; width: 0.8rem; aspect-ratio: 1; border-radius: 50%; background: var(--cm-ink-3); opacity: 0.45; transition: scale 0.25s cubic-bezier(0.34, 1.6, 0.64, 1), background-color 0.2s ease, opacity 0.2s ease; }
            .cm-rail a span { position: absolute; right: 100%; top: 50%; margin-right: 0.125rem; padding: 0.1rem 0.6rem; border-radius: 999px; background: var(--cm-surface); box-shadow: 0 0 0 2px var(--cm-line); white-space: nowrap; pointer-events: none; opacity: 0; translate: 0.3rem -50%; transition: opacity 0.2s ease, translate 0.2s ease; }
            .cm-rail a:hover span,
            .cm-rail a:focus-visible span { opacity: 1; translate: 0 -50%; }
            .cm-rail a.is-active::after { scale: 1.5; opacity: 1; background: var(--dot, var(--cm-tomato)); }
            /* The last mark is lit over the sunflower finale, where its own sunflower would vanish. */
            .cm-rail li:last-child a.is-active::after { background: var(--cm-tomato); }
        }

        @media (prefers-reduced-motion: reduce) {
            .cm-cut,
            .cm-blob,
            .cm-leaf { animation: none !important; }
            .cm-leaf,
            .cm-btn,
            .cm-btn::before,
            .cm-slip,
            .cm-who-card,
            .cm-who-art .cm-cut,
            .cm-near a,
            .cm-dir a,
            .cm-seat-on,
            .cm-tt td .cm-mk,
            .cm-qs summary i,
            .cm-qs summary i::after { transition: none; }
            html #cm .cm-door:hover .cm-leaf { transform: rotateY(-64deg); }
            .cm-room svg { transition: none; }
        }
    </style>

    @php
        // ---------------------------------------------------------------
        // The week as it hangs on the board. Each program is ONE event
        // with the days it repeats on (day-of-week recurrence), and a
        // strand is a sub-schedule, which colour-codes and groups only.
        // ---------------------------------------------------------------
        // Four strands, four papers, and four SHAPES, so the colour-coding
        // never has to carry the meaning alone: a circle, an arch, a petal
        // and a square, cut from teal, sunflower, tomato and leaf. The key
        // is the class that carries both (.cm-k-*).
        $strands = [
            'Wellbeing' => 'wellbeing',
            'Youth'     => 'youth',
            'Learning'  => 'learning',
            'Sport'     => 'sport',
        ];

        $dayNames = ['Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday', 'Sun' => 'Sunday'];

        // [program, strand, days it runs, sign-up]
        $week = [
            ['Senior fitness',   'Wellbeing', ['Mon', 'Wed', 'Fri'], 'RSVP, 24 places'],
            ['Youth basketball', 'Youth',     ['Tue', 'Thu'],        'RSVP, 30 places'],
            ['Toddler group',    'Wellbeing', ['Wed'],               'RSVP, 16 places'],
            ['Pottery class',    'Learning',  ['Wed'],               'Ticketed, $45'],
            ['Community lunch',  'Wellbeing', ['Thu'],               'Just turn up'],
            ['Film night',       'Learning',  ['Fri'],               'RSVP, 60 places'],
            ['Open gym',         'Sport',     ['Sat', 'Sun'],        'Just turn up'],
        ];

        // The cards actually pinned to the board in the hero. The second
        // line is the time and the sign-up, which are the fields an event
        // really has: there is no room or space field anywhere in the app,
        // so nothing on a card here pretends there is one.
        $pinned = [
            ['Mon', 'Senior fitness', '9:30am, 24 places', -3.2],
            ['Tue', 'Youth basketball', '6:00pm, 30 places', 2.4],
            ['Wed', 'Toddler group', '10:00am, 16 places', -1.6],
            ['Thu', 'Community lunch', '12:30pm, just turn up', 2.8],
            ['Sat', 'Open gym', '8:00am, just turn up', -2.2],
        ];

        // The six ways out of the one board. Every row is free.
        $doors = [
            [
                'Its own link',
                'The center gets a page at its own address, with every program and every date on it, readable on a phone. That link is the thing you print, text and put in the newsletter.',
                'M13.828 10.172a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656 0M10.172 13.828a4 4 0 01-5.656-5.656l3-3a4 4 0 015.656 0',
            ],
            [
                'Embedded on the town website',
                'One snippet drops the same calendar into the site the city already runs. Nobody is keeping two lists in step, because there is only one list.',
                'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
            ],
            [
                'On the calendar they already check',
                'Every event page\'s Add to Calendar menu, and the sign-up panel, offer the center\'s live calendar feed. Subscribe once and the next ninety days of every weekly program land in the phone\'s own calendar, and move when you move them. Two-way Google, Outlook and CalDAV sync is for your own calendar, one entry per program.',
                'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            ],
            [
                'In the inboxes that asked',
                'Anyone who leaves an email address and confirms it gets a short digest of the new dates you put up yourself, never more than one every 72 hours. A newsletter is the letter you write and send yourself: ten emails a month free, counted per recipient, 100 on Pro and 1,000 on Enterprise.',
                'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            [
                'Printed on the sheet itself',
                'Your schedule has a QR code you can download and print. The laminated sheet on the actual board becomes a way into the online one, which is the whole trick.',
                'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
            ],
            [
                'And back onto the board',
                'Members can add photos and comments to a program, and every submission waits in an approval queue before anyone sees it. Twenty-five photos on the free plan, and Pro lifts the cap. The board fills itself back up.',
                'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z',
            ],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for community centers?',
                'a' => 'Yes, and most of what a center needs is on the free plan: the public program calendar and its own link, recurring programs with date exceptions, sub-schedules, free RSVP sign-up with an optional capacity, the embeddable calendar, two-way Google, Outlook and CalDAV sync, a live calendar feed, iCal downloads, the downloadable QR code, built-in analytics, member photos and comments with an approval queue (25 photos on the free plan), and 10 newsletter emails a month. Newsletter allowances count each recipient as one email, so ten emails means ten people; Pro raises it to 100 a month and Enterprise to 1,000. Charging for a class is the part that needs Pro at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on the sale whatever plan you are on.',
            ],
            [
                'q' => 'Can I organize classes, meetings, and events by category?',
                'a' => 'Yes. Sub-schedules group and color-code the strands, so wellbeing, youth, learning and sport read apart at a glance, and each strand has its own shareable link, which means you can send a family a link that shows only the youth programs. Being straight about one thing: a sub-schedule has no visibility setting of its own and cannot hide anything. To keep a program off the public calendar until you are ready, save it as a Draft.',
            ],
            [
                'q' => 'How do community members stay informed about programs?',
                'a' => 'Through as many doors as you care to open. People who leave an email address and confirm it hear about the new dates you put up, on their own, as one digest rather than a message per program; the newsletter is the one you compose and press send on, for when there is something to say beyond the listing. The calendar embeds into the website you already have. Anyone can subscribe to the center\'s live calendar feed from an event page\'s Add to Calendar menu or the sign-up panel, and it carries the next ninety days of every weekly program, moving a date when you move it. Your own calendar syncs both ways with Google, Outlook and CalDAV, one entry per event. And your schedule has a QR code you can download and print for the board in the lobby.',
            ],
            [
                'q' => 'Can we handle event registration and payments?',
                'a' => 'Yes. Free sign-up with an optional capacity is on the free plan, and the capacity is counted per date, so a full Monday session does not stop the following Monday filling up. For a paid class, take payment through your own Stripe or PayPal account, an Invoice Ninja invoice, a payment link or cash at the desk: the money goes to you, Event Schedule takes no cut, and every ticket carries a QR code you can scan at the door on any plan. Charging for a place is a Pro feature; free sign-up stays free however many people come. Pro adds the rest of the paid-class kit too: asking your own questions at checkout, and selling one pass that covers a whole term of a class.',
            ],
            [
                'q' => 'Can we refund a class?',
                'a' => 'Yes, from the Sales page, on Pro. A Stripe or PayPal sale is refunded through the provider, in full or in part, and its status only changes once the money has gone back; a partial refund leaves the ticket valid. Every other method (cash, a payment link, Invoice Ninja or Payfast) shows Mark as Refunded instead, which records the refund without moving money, so you hand that one back yourself. Event Schedule does not email the buyer about a refund, so let them know.',
            ],
            [
                'q' => 'Can people ask to hear when a class opens for booking?',
                'a' => 'Yes. Switch on the free "Notify me" card, put the class or the summer camp up before it sells, and its event page offers "Tell me when tickets go on sale". A parent leaves only an email address, and hears once when booking opens, once if it is cancelled and again shortly before it starts, plus any change notice you choose to send. It is free on every plan and it is not a sign-up to the center\'s emails, so it never touches the newsletter allowance. The Tickets panel in the event editor shows how many people are waiting.',
            ],
            [
                'q' => 'Can outside groups request the hall?',
                'a' => 'Yes. Turn on requests and a group can ask for a date through your page, either with a short booking form or by pasting a listing for us to read. Keep approval on and nothing appears publicly until you accept it, and you are emailed when requests are waiting. It is a request inbox rather than a room-booking system: there is no per-room availability and nothing warns you that two bookings overlap, so you are still the person who checks.',
            ],
        ];

        $dotSections = [
            ['top', 'The board'],
            ['week', 'The week'],
            ['doors', 'The doors'],
            ['hall', 'Hall hire'],
            ['signup', 'Sign-ups'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];

        // Page furniture. The four papers in the order they are handed out, the strand each
        // pinned card belongs to (read off $week, so the two cannot drift), and two icons.
        $cmPapers = ['var(--cm-sun)', 'var(--cm-tomato)', 'var(--cm-teal)', 'var(--cm-leaf)'];
        $cmStrandOf = [];
        foreach ($week as [$cmProgram, $cmStrandName]) {
            $cmStrandOf[$cmProgram] = $strands[$cmStrandName];
        }
        $cmArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $cmDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $cmTick = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
    @endphp

    <div id="cm">

        <nav class="cm-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as $cmDotIndex => [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot" style="--dot: {{ $cmPapers[$cmDotIndex % 4] }};"><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the board in the lobby, cut from paper              -->
        <!-- ============================================================ -->
        <section id="top" class="cm-hero" style="scroll-margin-top: 5rem;">
            <div class="cm-wrap cm-hero-grid">
                <div class="cm-hero-copy">
                    <h1 class="cm-h cm-h1">
                        <x-marketing.hero-eyebrow class="cm-eyebrow es-fade-up es-d-1">Community center calendar, for recreation facilities too</x-marketing.hero-eyebrow>
                        <span class="es-fade-up es-d-1">The board reaches</span>
                        <span class="es-fade-up es-d-2">whoever <span class="cm-hl">walks past it</span>.</span>
                    </h1>

                    <p class="cm-lede es-fade-up es-d-3">
                        Your week already runs like clockwork. It is the timetable that never leaves the
                        lobby. Put the same board on a link, in an inbox and on a phone, and the people
                        who could not get through the door still see what you run.
                    </p>

                    <div class="cm-cta es-fade-up es-d-4">
                        <a href="#doors" class="cm-btn cm-btn-ghost">
                            See the six doors
                            {!! $cmDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="cm-btn">
                            Put your board online
                            {!! $cmArrow !!}
                        </a>
                    </div>
                </div>

                <div>
                    <div class="cm-art" aria-hidden="true">
                        <i class="cm-cut cm-circle" style="--c: var(--cm-tomato); width: 37%; right: -4%; top: -2%; --i: 1; --fx: 5rem; --fy: -3rem;"></i>
                        <i class="cm-cut cm-half" style="--c: var(--cm-teal); width: 58%; left: -8%; bottom: 0; --rot: -7deg; --lean: 3deg; --i: 2; --fx: -5rem; --fy: 3rem;"></i>
                        <i class="cm-cut cm-quarter" style="--c: var(--cm-leaf); width: 22%; left: 0; top: 3%; --i: 3; --fx: -3rem;"></i>
                        <i class="cm-cut cm-stairs" style="--c: var(--cm-ink); width: 31%; right: -5%; bottom: -1%; --lean: 2deg; --i: 4; --fx: 4rem; --fy: 3rem; --fr: 8deg;"></i>

                        <div class="cm-board es-fade-up es-d-2">
                            <span class="cm-board-cap">This week</span>
                            <div class="cm-slips">
                                @foreach ($pinned as $pi => [$pDay, $pName, $pWhere, $pRot])
                                    <span class="cm-slip" style="--rot: {{ $pRot }}deg;">
                                        <span class="cm-slip-day"><i class="cm-mk cm-k-{{ $cmStrandOf[$pName] }}" style="--mk: 0.8rem;"></i>{{ $pDay }}</span>
                                        <span class="cm-slip-name">{{ $pName }}</span>
                                        <span class="cm-slip-sub">{{ $pWhere }}</span>
                                    </span>
                                @endforeach

                                <span class="cm-slip cm-slip-link" style="--rot: 2.2deg;">
                                    <span class="cm-slip-day">And the newest card</span>
                                    <span class="cm-slip-url">riverside.eventschedule.com</span>
                                </span>
                            </div>
                        </div>

                        <i class="cm-cut cm-petal cm-front" style="--c: var(--cm-leaf); width: 13%; right: -2%; top: 43%; --rot: 26deg; --i: 5; --fx: 4rem;"></i>
                        <i class="cm-cut cm-petal cm-front" style="--c: var(--cm-leaf); width: 11%; right: 7%; top: 35%; --rot: -16deg; --i: 6; --fx: 4rem;"></i>
                    </div>

                    <p class="cm-art-cap es-fade-up es-d-5">
                        Six cards, one link. The board does not change. What changes is how many ways there are to read it.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The week (01): programs against days, a real table        -->
        <!-- ============================================================ -->
        <section id="week" class="cm-sec" style="scroll-margin-top: 5rem;">
            <div class="cm-wrap">
                <div class="cm-head cm-head-row">
                    <div class="cm-head-copy">
                        <p class="cm-sign" style="--paper: var(--cm-teal);" data-reveal><b aria-hidden="true">01 &rarr;</b> The week</p>
                        <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Set it once. The week <span class="cm-hl">draws itself</span>.
                        </h2>
                        <p class="cm-lede" data-reveal style="--reveal-delay: 0.16s;">
                            Every program that repeats is one entry with the days it runs on, not fifty-two
                            copies of the same class. Add the weeks it skips as date exceptions and those
                            dates come off the calendar, so nobody walks up to a hall that is shut.
                        </p>
                    </div>
                    <div class="cm-cluster" aria-hidden="true">
                        <i class="cm-cut cm-circle" style="--c: var(--cm-sun); width: 6.5em; right: 0.5em; top: 0;"></i>
                        <i class="cm-cut cm-arch" style="--c: var(--cm-teal); width: 5em; right: 5.2em; top: 2.4em; --rot: -6deg;"></i>
                        <i class="cm-cut cm-quarter" style="--c: var(--cm-tomato); width: 3.6em; right: 1.6em; top: 5.4em; --rot: 180deg;"></i>
                    </div>
                </div>

                <div class="cm-week" data-reveal="panel">
                    <div class="cm-week-card">
                        <div class="cm-week-pad">
                            <fieldset class="cm-chips">
                                <legend class="sr-only">Show one strand of the week</legend>
                                <span class="cm-chips-label" aria-hidden="true">Show</span>
                                <input type="radio" name="cm-strand" id="cm-s-all" class="sr-only" checked>
                                <label for="cm-s-all">Everything</label>
                                @foreach ($strands as $cmStrandName => $cmStrandKey)
                                    <input type="radio" name="cm-strand" id="cm-s-{{ $cmStrandKey }}" class="sr-only">
                                    <label for="cm-s-{{ $cmStrandKey }}"><i class="cm-mk cm-k-{{ $cmStrandKey }}" aria-hidden="true"></i>{{ $cmStrandName }}</label>
                                @endforeach
                            </fieldset>

                            {{-- The roles are spelled out because the table is laid out as a grid, and a
                                 browser may drop a table's meaning along with its display. --}}
                            <table class="cm-tt" role="table">
                                <caption class="sr-only">A community center week: each recurring program, the days it runs, and how people sign up</caption>
                                <thead role="rowgroup">
                                    <tr role="row">
                                        <th scope="col" role="columnheader">Program</th>
                                        @foreach ($dayNames as $dShort => $dLong)
                                            <th scope="col" role="columnheader">{{ $dShort }}</th>
                                        @endforeach
                                        <th scope="col" role="columnheader">Sign-up</th>
                                    </tr>
                                </thead>
                                <tbody role="rowgroup">
                                    @foreach ($week as [$wName, $wStrand, $wDays, $wSignup])
                                        <tr role="row" class="cm-k-{{ $strands[$wStrand] }}" data-strand="{{ $wStrand }}">
                                            <th scope="row" role="rowheader">
                                                <span class="cm-tt-name">{{ $wName }}</span>
                                                <span class="cm-tt-strand"><i class="cm-mk" aria-hidden="true"></i>{{ $wStrand }}</span>
                                            </th>
                                            @foreach ($dayNames as $dShort => $dLong)
                                                <td role="cell" data-day="{{ $dShort }}">
                                                    @if (in_array($dShort, $wDays))
                                                        <span class="sr-only">Runs {{ $dLong }}</span>
                                                        <i class="cm-mk" style="--n: {{ $loop->parent->index + $loop->index }};" aria-hidden="true"></i>
                                                    @else
                                                        <i class="cm-off" aria-hidden="true"></i>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td role="cell" class="cm-tt-sign">{{ $wSignup }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="cm-notes">
                                @foreach ([
                                    ['One entry, every week', 'Pick the days and it repeats on them. Editing the class edits every date of it.'],
                                    ['The weeks it does not run', 'A date exception takes a single date out. Guests do not see a crossed-out line, they see the day simply absent.'],
                                    ['A strand is a sub-schedule', 'It groups and color-codes, and it has a link of its own that shows only those programs.'],
                                ] as $cmNoteIndex => [$nTitle, $nBody])
                                    <p class="cm-note" style="--c: {{ $cmPapers[($cmNoteIndex + 2) % 4] }};">
                                        <strong>{{ $nTitle }}</strong>
                                        <span>{{ $nBody }}</span>
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <p class="cm-notice" data-reveal>
                    <strong>Being straight with you</strong>
                    <span>
                        A sub-schedule cannot hide anything. It has a name, a slug and a color, and that is
                        all, so it is not a permission or a room. When you want a program off the public
                        calendar until you are ready, that is what Draft is for.
                    </span>
                </p>

                <p class="cm-freeline" data-reveal>
                    <span class="cm-tag cm-tag-free">Free</span>
                    <span>Recurring programs, date exceptions and sub-schedules cost nothing.</span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The doors (02): six of them, each on its hinge            -->
        <!-- ============================================================ -->
        <section id="doors" class="cm-sec cm-wall" style="scroll-margin-top: 4rem;">
            <div class="cm-wrap">
                <div class="cm-head cm-head-row">
                    <div class="cm-head-copy">
                        <p class="cm-sign" style="--paper: var(--cm-sun);" data-reveal><b aria-hidden="true">02 &rarr;</b> The doors</p>
                        <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                            One board, <span class="cm-hl" style="--hl: var(--cm-teal);">six doors out</span>.
                        </h2>
                        <p class="cm-lede" data-reveal style="--reveal-delay: 0.16s;">
                            The cork stays where it is. Everything below is the same week, leaving the
                            building by a different route, and every one of them is on the free plan.
                        </p>
                    </div>
                    <div class="cm-cluster" aria-hidden="true">
                        <i class="cm-cut cm-arch" style="--c: var(--cm-tomato); width: 5.4em; right: 6em; top: 1.6em; --rot: -5deg;"></i>
                        <i class="cm-cut cm-arch" style="--c: var(--cm-leaf); width: 4.4em; right: 2.4em; top: 3.2em; --rot: 6deg;"></i>
                        <i class="cm-cut cm-blob" style="--c: var(--cm-teal); width: 3.4em; right: 0; top: 0;"></i>
                    </div>
                </div>

                <div class="cm-doors" data-reveal-group="90">
                    @foreach ($doors as $dIndex => [$dTitle, $dBody, $dIcon])
                        <article class="cm-door" style="--paper: {{ $cmPapers[$dIndex % 4] }};" data-reveal>
                            <div class="cm-door-top" aria-hidden="true">
                                <div class="cm-doorway">
                                    <div class="cm-room">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $dIcon }}" /></svg>
                                    </div>
                                    <div class="cm-leaf"><b>{{ str_pad($dIndex + 1, 2, '0', STR_PAD_LEFT) }}</b></div>
                                </div>
                                <span class="cm-door-mat"></span>
                            </div>
                            <h3 class="cm-h cm-h3">{{ $dTitle }}</h3>
                            <p class="cm-door-body">{{ $dBody }}</p>
                            <p class="cm-door-foot">
                                <span class="cm-tag cm-tag-free">Free</span>
                            </p>
                        </article>
                    @endforeach
                </div>

                <p class="cm-doors-note" data-reveal>
                    Worth saying plainly, because software often implies otherwise: a newsletter goes out
                    only when you write one and send it, and the digest reaches only people who confirmed
                    their email address. Pressing Follow on its own signs nobody up for the digest, and
                    you can switch it off in your settings.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Hall hire (03)                                            -->
        <!-- ============================================================ -->
        <section id="hall" class="cm-sec" style="scroll-margin-top: 4rem;">
            <div class="cm-wrap cm-hall">
                <div class="cm-hall-copy">
                    <p class="cm-sign" style="--paper: var(--cm-tomato);" data-reveal><b aria-hidden="true">03 &rarr;</b> Hall hire</p>
                    <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The slip waits for <span class="cm-hl" style="--hl: var(--cm-leaf);">a pushpin</span>.
                    </h2>
                    <p class="cm-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Turn on requests and an outside group asks for a date through your page instead
                        of leaving a note at reception that somebody has to type up. Nothing appears
                        publicly until you accept it.
                    </p>

                    <ul class="cm-list" data-reveal-group="80">
                        @foreach ([
                            ['Nothing posts without you', 'Keep approval on and every request sits pending. The public calendar only ever shows what you agreed to.'],
                            ['A short form or a pasted listing', 'Ask for the details on a booking form, or let people paste what they already wrote and have it read for you.'],
                            ['Your conditions on the form', 'The terms a hirer has to agree to go on the request itself, so they are answered before the conversation starts.'],
                            ['Groups you already trust', 'Name the schedules whose requests you want posted without review, and leave everyone else pending.'],
                            ['You are told, not the followers', 'A scheduled check emails you when requests are waiting. Nobody on your follower list hears about it.'],
                        ] as $cmHallIndex => [$hTitle, $hBody])
                            <li data-reveal>
                                <i class="cm-mk cm-k-{{ array_values($strands)[$cmHallIndex % 4] }}" aria-hidden="true"></i>
                                <span><strong>{{ $hTitle }}</strong> <span>{{ $hBody }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="cm-notice" data-reveal>
                        <strong>What this is not</strong>
                        <span>
                            It is a request inbox, not a room-booking system. There is no per-room
                            availability grid, and nothing will warn you that two bookings overlap. You
                            are still the person who checks, which is worth knowing before you rely on it.
                        </span>
                    </p>
                </div>

                <div class="cm-card" data-reveal="panel">
                    <div class="cm-card-top">
                        <h3 class="cm-h cm-h3">Requests waiting</h3>
                        <span class="cm-tag cm-tag-free">Free</span>
                    </div>

                    <div aria-hidden="true">
                        <span class="cm-kicker">Pinned up</span>
                        <div class="cm-pinboard">
                            <span class="cm-req">
                                <span class="cm-pin"></span>
                                <span class="cm-req-state">Accepted</span>
                                <span class="cm-req-name">Lincoln PTA quiz night</span>
                                <span class="cm-req-when">Tue 15 Oct, 7:00pm to 9:00pm</span>
                            </span>
                        </div>

                        <span class="cm-kicker" style="margin-top: 1.5rem;">Still in the tray</span>
                        <div class="cm-tray">
                            <span class="cm-req cm-req-wait">
                                <span class="cm-req-state">Pending</span>
                                <span class="cm-req-name">Scout Troop 42 meeting</span>
                                <span class="cm-req-when">Wed 23 Oct, 6:30pm to 8:00pm</span>
                            </span>
                            <span class="cm-req cm-req-wait">
                                <span class="cm-req-state">Pending</span>
                                <span class="cm-req-name">Allotment Society AGM</span>
                                <span class="cm-req-when">Sat 26 Oct, 10:00am to noon</span>
                            </span>
                        </div>
                    </div>

                    <p class="cm-card-foot">
                        The pin is the public calendar. Accepting a request is what moves a slip out
                        of the tray and onto the board, and nothing else does.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Sign-ups (04)                                             -->
        <!-- ============================================================ -->
        <section id="signup" class="cm-sec cm-wall" style="scroll-margin-top: 4rem;">
            <div class="cm-wrap">
                <div class="cm-head cm-head-row">
                    <div class="cm-head-copy">
                        <p class="cm-sign" style="--paper: var(--cm-leaf);" data-reveal><b aria-hidden="true">04 &rarr;</b> Sign-ups</p>
                        <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Sixteen places, <span class="cm-hl" style="--hl: var(--cm-tomato);">counted per date</span>.
                        </h2>
                        <p class="cm-lede" data-reveal style="--reveal-delay: 0.16s;">
                            A toddler group that fits sixteen fits sixteen. You set that number once on the
                            program, and it is counted separately for every date it runs, so a full Wednesday
                            leaves the following Wednesday alone.
                        </p>
                    </div>
                    <div class="cm-cluster" aria-hidden="true">
                        <i class="cm-cut cm-circle" style="--c: var(--cm-teal); width: 4.2em; right: 7.4em; top: 3.6em;"></i>
                        <i class="cm-cut cm-circle" style="--c: var(--cm-sun); width: 5.6em; right: 3em; top: 0.6em;"></i>
                        <i class="cm-cut cm-circle" style="--c: var(--cm-tomato); width: 3.2em; right: 0.4em; top: 4.8em;"></i>
                    </div>
                </div>

                <div class="cm-two" data-reveal-group="110">
                    <div class="cm-card cm-class" data-reveal="panel">
                        <div class="cm-card-top">
                            <h3 class="cm-h cm-h3">The free class</h3>
                            <span class="cm-tag cm-tag-free">Free</span>
                        </div>
                        <p class="cm-class-intro">
                            Turn on sign-up, give the program a capacity, and people put their name down
                            from your page. No card, no checkout, no plan.
                        </p>

                        <div class="cm-mock" style="--c: var(--cm-teal);" aria-hidden="true">
                            <span class="cm-kicker">Toddler group, Wednesday</span>
                            <div class="cm-count">
                                <b>11</b>
                                <span>of 16 places taken</span>
                            </div>
                            <div class="cm-seats">
                                @for ($s = 0; $s < 16; $s++)
                                    <span class="{{ $s < 11 ? 'cm-seat cm-seat-on' : 'cm-seat' }}" style="--n: {{ $s }};"></span>
                                @endfor
                            </div>
                            <p class="cm-mock-note">Next Wednesday starts again at nought of sixteen.</p>
                        </div>

                        <ul class="cm-ticks">
                            @foreach ([
                                'The capacity is optional. Leave it off and sign-up just stays open.',
                                'A full date stops taking names by itself.',
                                'Attendees can download the date to their own calendar.',
                            ] as $fItem)
                                <li><i aria-hidden="true">{!! $cmTick !!}</i><span>{{ $fItem }}</span></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="cm-card cm-class" data-reveal="panel">
                        <div class="cm-card-top">
                            <h3 class="cm-h cm-h3">The paid class</h3>
                            <span class="cm-tag cm-tag-pro">Pro</span>
                        </div>
                        <p class="cm-class-intro">
                            Pottery costs money to run, so it costs money to join. Take it through your own
                            Stripe or PayPal account, an Invoice Ninja invoice, a payment link or cash at the
                            desk, and the money lands with you. Event Schedule charges zero platform fees, so
                            past what the processor takes, the fee is yours. Charging for a place is the one
                            part that needs Pro; free sign-up never does.
                        </p>

                        <div class="cm-mock" style="--c: var(--cm-tomato);" aria-hidden="true">
                            <span class="cm-kicker">Pottery class, Wednesday</span>
                            <div class="cm-prices">
                                @foreach ([['Single session', '$45'], ['Term pass, one purchase', '$180'], ['Concession', '$28']] as [$tName, $tPrice])
                                    <div class="cm-price">
                                        <span>{{ $tName }}</span>
                                        <span>{{ $tPrice }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <p class="cm-mock-note">A pass is one purchase valid across every date of the class, once each. Passes are a Pro option.</p>
                        </div>

                        <ul class="cm-ticks" style="--c: var(--cm-tomato);">
                            @foreach ([
                                'Every ticket carries a QR code you scan at the door, on any plan.',
                                'Passes, discount codes and a waitlist when a class fills are Pro.',
                                'So are your own questions at checkout and a CSV of the whole list.',
                            ] as $pItem)
                                <li><i aria-hidden="true">{!! $cmTick !!}</i><span>{{ $pItem }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <p class="cm-proline" data-reveal>
                    <span class="cm-tag cm-tag-pro">Pro</span>
                    <span>At {{ plan_price($proMonthly) }} a month Pro opens paid tickets and adds
                    passes, discount codes, the waitlist and the sales CSV. Free sign-up with a capacity never
                    needs it, and for a lot of centers that is the whole requirement.</span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Who it is for (05)                                        -->
        <!-- ============================================================ -->
        <section id="who" class="cm-sec" style="scroll-margin-top: 4rem;">
            <div class="cm-wrap">
                <div class="cm-head cm-head-row">
                    <div class="cm-head-copy">
                        <p class="cm-sign" style="--paper: var(--cm-teal);" data-reveal><b aria-hidden="true">05 &rarr;</b> Who it is for</p>
                        <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Anywhere with a board <span class="cm-hl">by the door</span>.
                        </h2>
                        <p class="cm-lede" data-reveal style="--reveal-delay: 0.16s;">
                            From recreation facilities to neighborhood gathering spaces, every kind of community center runs on a board like this one.
                        </p>
                    </div>
                    <div class="cm-cluster" aria-hidden="true">
                        <i class="cm-cut cm-half" style="--c: var(--cm-leaf); width: 8em; right: 3.4em; top: 4.4em;"></i>
                        <i class="cm-cut cm-petal" style="--c: var(--cm-tomato); width: 2.6em; right: 5.4em; top: 0.4em; --rot: -24deg;"></i>
                        <i class="cm-cut cm-petal" style="--c: var(--cm-sun); width: 2.6em; right: 2.2em; top: 0.9em; --rot: 22deg;"></i>
                    </div>
                </div>

                @php
                    // [name, description, blog slug, tint, the handful of paper on its card]
                    $cmWho = [
                        ['Recreation Centers', 'Leagues, fitness classes and holiday camps, each one entry on the week with its own sign-up and a capacity per date.', 'for-recreation-centers', 'var(--cm-teal)', [
                            ['cm-circle', 'var(--cm-sun)', 'width: 5.4rem; left: 1.5rem; top: 1.4rem;'],
                            ['cm-half', 'var(--cm-teal)', 'width: 8rem; left: 4.4rem; top: 3.4rem; --rot: -8deg;'],
                            ['cm-circle', 'var(--cm-tomato)', 'width: 2.4rem; right: 2rem; top: 1.2rem;'],
                        ]],
                        ['Senior Centers', 'Lunch clubs, chair exercise and trips out, on the board by the door and on a printed QR code that opens the week on a phone.', 'for-senior-centers', 'var(--cm-sun)', [
                            ['cm-arch', 'var(--cm-tomato)', 'width: 4.4rem; left: 2rem; top: 1.5rem; --rot: -4deg;'],
                            ['cm-circle', 'var(--cm-leaf)', 'width: 4.6rem; left: 5.2rem; top: 2.6rem;'],
                            ['cm-quarter', 'var(--cm-sun)', 'width: 3.4rem; right: 1.8rem; top: 1.4rem; --rot: 90deg;'],
                        ]],
                        ['Youth Centers', 'After-school clubs and summer camps on a youth strand, with a link for parents that shows only those programs.', 'for-youth-centers', 'var(--cm-leaf)', [
                            ['cm-stairs', 'var(--cm-ink)', 'width: 6.4rem; left: 1.6rem; top: 2.6rem;'],
                            ['cm-circle', 'var(--cm-sun)', 'width: 3.6rem; left: 6.6rem; top: 0.9rem;'],
                            ['cm-petal', 'var(--cm-leaf)', 'width: 2.4rem; right: 2.4rem; top: 2.2rem; --rot: 28deg;'],
                        ]],
                        ['Cultural Centers', 'Language classes every week and a festival night once a year, the free programs and the ticketed ones on one board.', 'for-cultural-centers', 'var(--cm-tomato)', [
                            ['cm-half', 'var(--cm-sun)', 'width: 8.4rem; left: 1.4rem; top: 3.2rem;'],
                            ['cm-half', 'var(--cm-tomato)', 'width: 5.6rem; left: 4.6rem; top: 1.4rem; --rot: 180deg;'],
                            ['cm-circle', 'var(--cm-teal)', 'width: 2.8rem; right: 1.8rem; top: 3.6rem;'],
                        ]],
                        ['Neighborhood Centers', 'Residents\' meetings, block parties and outside groups asking for the hall, every request waiting for your yes.', 'for-neighborhood-centers', 'var(--cm-teal)', [
                            ['cm-arch', 'var(--cm-teal)', 'width: 3.8rem; left: 1.8rem; top: 2.2rem;'],
                            ['cm-arch', 'var(--cm-sun)', 'width: 4.6rem; left: 4.8rem; top: 1.2rem;'],
                            ['cm-arch', 'var(--cm-tomato)', 'width: 3.4rem; left: 8.6rem; top: 2.8rem;'],
                        ]],
                        ['Faith-Based Centers', 'Weekly gatherings, study groups and outreach, with requests from the groups you already trust posted without a review.', 'for-faith-based-centers', 'var(--cm-sun)', [
                            ['cm-quarter', 'var(--cm-leaf)', 'width: 5rem; left: 1.8rem; top: 2rem;'],
                            ['cm-quarter', 'var(--cm-sun)', 'width: 5rem; left: 6.4rem; top: 2rem; --rot: 90deg;'],
                            ['cm-circle', 'var(--cm-tomato)', 'width: 2.6rem; left: 5.5rem; top: 0.8rem;'],
                        ]],
                    ];
                @endphp

                <div class="cm-who" data-reveal-group="70">
                    @foreach ($cmWho as [$cmWhoName, $cmWhoDesc, $cmWhoSlug, $cmWhoTint, $cmWhoCuts])
                        @php $cmWhoPost = get_sub_audience_blog($cmWhoSlug); @endphp
                        <article class="cm-who-card" data-reveal>
                            <div class="cm-who-art" style="--tint: {{ $cmWhoTint }};" aria-hidden="true">
                                @foreach ($cmWhoCuts as [$cmCutShape, $cmCutPaper, $cmCutPlace])
                                    <i class="cm-cut {{ $cmCutShape }}" style="--c: {{ $cmCutPaper }}; {{ $cmCutPlace }}"></i>
                                @endforeach
                            </div>
                            <div class="cm-who-text">
                                <h3 class="cm-h cm-h3">{{ $cmWhoName }}</h3>
                                <p>{{ $cmWhoDesc }}</p>
                                @if ($cmWhoPost)
                                    <a href="{{ blog_url('/' . $cmWhoPost->slug) }}" class="cm-link" aria-label="Learn more about Event Schedule for {{ $cmWhoName }}">
                                        Learn more
                                        {!! $cmArrow !!}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. How it works (06): three steps up                         -->
        <!-- ============================================================ -->
        <section id="how" class="cm-sec cm-wall" style="scroll-margin-top: 4rem;">
            <div class="cm-wrap">
                <div class="cm-head">
                    <p style="justify-self: start;"><span class="cm-sign" style="--paper: var(--cm-sun);" data-reveal><b aria-hidden="true">06 &rarr;</b> How it works</span></p>
                    <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Put it up, pin it, <span class="cm-hl" style="--hl: var(--cm-teal);">open the doors</span>.
                    </h2>
                </div>

                <div class="cm-steps" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Put the board up', 'Sign up as a venue schedule and name the center. You have a public page and a link before you have added anything to it.'],
                        ['02', 'Pin the week', 'Add each program once with the days it repeats on, group the strands into sub-schedules, and knock out the weeks it skips.'],
                        ['03', 'Open the doors', 'Embed the calendar in the site you already have, sync it, print the QR code for the lobby, and write to the people who follow.'],
                    ] as $cmStepIndex => [$sNum, $sTitle, $sBody])
                        <div class="cm-step" style="--paper: {{ $cmPapers[($cmStepIndex + 2) % 4] }}; --up: {{ $cmStepIndex }};" data-reveal>
                            <b aria-hidden="true">{{ $sNum }}</b>
                            <h3 class="cm-h cm-h3">{{ $sTitle }}</h3>
                            <p>{{ $sBody }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="cm-steps-floor" aria-hidden="true"></div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Key features: the directory by the door                   -->
        <!-- ============================================================ -->
        <section class="cm-sec">
            <div class="cm-wrap cm-dir-grid">
                <div class="cm-dir-side">
                    <p class="cm-sign" style="--paper: var(--cm-leaf);" data-reveal><b aria-hidden="true">&rarr;</b> Directory</p>
                    <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                    <p data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="cm-link">
                            See all features
                            {!! $cmArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $cmDirectory = [
                        ['Recurring Events', 'One entry with the days it repeats on, and exceptions for the weeks it skips', marketing_url('/features/recurring-events')],
                        ['Sub-schedules', 'Group and color-code the strands, each with its own shareable link', marketing_url('/features/sub-schedules')],
                        ['Embed Calendar', 'Add your program calendar to the website you already have', marketing_url('/features/embed-calendar')],
                        ['Online Events', 'One link people join on, for a virtual town hall or a class from home', marketing_url('/features/online-events')],
                        ['Event Graphics', 'Generate a shareable image for a program to post or print, free on every plan', marketing_url('/features/event-graphics')],
                        ['Newsletters', 'Write to the people who follow the center, ten emails a month free, counted per recipient', marketing_url('/features/newsletters')],
                        ['PayPal', 'Take class fees through the center\'s own PayPal account, on the Pro plan', marketing_url('/paypal')],
                    ];
                @endphp
                <div class="cm-dir" data-reveal-group="60">
                    @foreach ($cmDirectory as $cmDirIndex => [$cmDirName, $cmDirDesc, $cmDirUrl])
                        <a href="{{ $cmDirUrl }}" data-reveal>
                            <i aria-hidden="true" style="--paper: {{ $cmPapers[$cmDirIndex % 4] }};">{{ $cmDirIndex + 1 }}</i>
                            <span>
                                <strong>{{ $cmDirName }}</strong>
                                <small>{{ $cmDirDesc }}</small>
                            </span>
                            {!! $cmArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="cm-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 9. Related pages: four more doors down the road              -->
        <!-- ============================================================ -->
        <section class="cm-sec">
            <div class="cm-wrap">
                <div class="cm-near-head">
                    <div style="display: grid; gap: 1.25rem; justify-items: start;">
                        <p class="cm-sign" style="--paper: var(--cm-tomato);" data-reveal><b aria-hidden="true">&rarr;</b> Down the road</p>
                        <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="cm-link" data-reveal>
                        See all use cases
                        {!! $cmArrow !!}
                    </a>
                </div>

                <div class="cm-near" data-reveal-group="70">
                    @foreach ([['/for-libraries', 'Libraries'], ['/for-churches', 'Churches'], ['/for-workshop-instructors', 'Workshop Instructors'], ['/for-fitness-and-yoga', 'Fitness & Yoga']] as $cmNearIndex => [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal style="--paper: {{ $cmPapers[($cmNearIndex + 2) % 4] }};">
                            <small>Event Schedule for</small>
                            <strong>{{ $relName }}</strong>
                            {!! $cmArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. FAQ (07)                                                 -->
        <!-- ============================================================ -->
        <section id="faq" class="cm-sec cm-wall" style="scroll-margin-top: 4rem;">
            <div class="cm-wrap cm-faq-grid">
                <div class="cm-faq-side">
                    <p class="cm-sign" style="--paper: var(--cm-tomato);" data-reveal><b aria-hidden="true">07 &rarr;</b> Questions</p>
                    <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Asked at <span class="cm-hl">the front desk</span>.
                    </h2>
                    <div class="cm-desk" aria-hidden="true">
                        <i class="cm-cut cm-half" style="--c: var(--cm-teal); width: 9em; left: 0.4em; top: 4.2em;"></i>
                        <i class="cm-cut cm-circle" style="--c: var(--cm-sun); width: 4em; left: 2.9em; top: 1.1em;"></i>
                        <i class="cm-cut cm-blob" style="--c: var(--cm-tomato); width: 2.6em; left: 8.6em; top: 2.6em;"></i>
                    </div>
                </div>

                <div class="cm-qs" data-reveal-group="60">
                    @foreach ($faqs as $faq)
                        <details name="faq" data-reveal>
                            <summary>
                                <h3>{{ $faq['q'] }}</h3>
                                <i aria-hidden="true"></i>
                            </summary>
                            <p>{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- 11. Finale: the newest card on the board                     -->
        <!-- ============================================================ -->
        <section id="claim" class="cm-fin" style="scroll-margin-top: 4rem;">
            <i class="cm-cut cm-circle" style="--c: #ff5a4e; width: clamp(9rem, 22vw, 19rem); left: -4rem; top: -4rem;" aria-hidden="true"></i>
            <i class="cm-cut cm-half" style="--c: #1bb5a4; width: clamp(12rem, 30vw, 26rem); right: -3rem; bottom: -1rem; --rot: -6deg; --lean: 3deg;" aria-hidden="true"></i>
            <i class="cm-cut cm-petal" style="--c: #6bbf59; width: clamp(2.6rem, 6vw, 5rem); right: 9%; top: 9%; --rot: 28deg;" aria-hidden="true"></i>
            <i class="cm-cut cm-petal" style="--c: #6bbf59; width: clamp(2.2rem, 5vw, 4.2rem); right: 15%; top: 5%; --rot: -14deg;" aria-hidden="true"></i>
            <i class="cm-cut cm-stairs" style="--c: #1f2430; width: clamp(7rem, 16vw, 13rem); left: -1rem; bottom: -0.2rem; --lean: 2deg;" aria-hidden="true"></i>

            <div class="cm-wrap cm-fin-in" id="cm-fin-panel">
                <div class="cm-newcard" data-reveal aria-hidden="true">
                    <span class="cm-pin"></span>
                    <small>Pin this one up</small>
                    <strong><span id="cm-new-name">your-center</span><wbr>.eventschedule.com</strong>
                </div>

                <p class="cm-sign" data-reveal><b aria-hidden="true">&rarr;</b> Free to start</p>
                <h2 class="cm-h cm-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Put the board where <span class="cm-hl">people can find it</span>.
                </h2>
                <p class="cm-fin-lede" data-reveal style="--reveal-delay: 0.16s;">
                    Everything the center already runs, on a link you can print, embed, sync and
                    email. Free forever, and no cut of anything you sell.
                </p>

                <div class="cm-claim-row" data-reveal style="--reveal-delay: 0.22s;">
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div dir="ltr" class="es-claim cm-claim">
                        <input id="es-claim-input" type="text" placeholder="your-center" autocomplete="off" spellcheck="false" maxlength="30">
                        <span>.eventschedule.com</span>
                    </div>
                    <a href="{{ app_url('/sign_up?type=venue') }}" class="cm-btn">
                        Get Started Free
                        {!! $cmArrow !!}
                    </a>
                </div>

                <p class="cm-fin-note">No credit card required</p>
            </div>
        </section>

        <div class="cm-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- The newest card takes the name as it is typed, and the finale's confetti is cut from the
         page's own four papers rather than the site's blues. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('es-claim-input');
            var name = document.getElementById('cm-new-name');
            if (input && name) {
                input.addEventListener('input', function () {
                    requestAnimationFrame(function () {
                        name.textContent = input.value.replace(/-+$/, '') || 'your-center';
                    });
                });
            }

            var panel = document.getElementById('cm-fin-panel');
            if (!panel || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var papers = ['#ff5a4e', '#1bb5a4', '#6bbf59', '#fffaf2', '#1f2430'];
                    {{-- Drawn on the page's own thread: the default cannon starts a blob worker,
                         which the site's Content Security Policy refuses and logs on every visit. --}}
                    var fire = window.confetti.create(null, { resize: true });
                    [[60, 0.05], [120, 0.95]].forEach(function (shot) {
                        fire({ particleCount: 60, angle: shot[0], spread: 62, startVelocity: 50, origin: { x: shot[1], y: 0.92 }, colors: papers, shapes: ['circle', 'square'], scalar: 1.25, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.5 });
            io.observe(panel);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
