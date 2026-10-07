<x-marketing-layout>
    <x-slot name="title">Dance Schedules | Classes, Rehearsals, Shows and Class Cards</x-slot>
    <x-slot name="description">Run weekly classes as recurring events with per-class capacity, and sell 10-visit cards, unlimited memberships and show tickets with zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Dance Groups</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Italiana') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Josefin Sans') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Mulish') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Dance Groups"
        description="One schedule for the class, the rehearsal and the show. Weekly classes run as recurring events with per-class capacity, rehearsal calls stay members-only, and passes cover a set number of visits."
        audience="Dance Groups"
        keywords="dance studio schedule, dance class calendar, class card, dance company rehearsal schedule, recital ticketing, dance recurring classes" />
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
           For-dance-groups "Eight Counts" styles. A company's own site:
           almost nothing on the page but type, and the type moves like
           a body. Five, six, seven, eight into the piece, then eight
           counts of content, each numbered on the studio floor.

           Display words are set one letter to a span (see $dgSet below)
           so letters can arrive in canon, lift in a wave and leave a
           long-exposure trail. Bone, ink and one strip of yellow spike
           tape. Everything is scoped under #dg; every moving thing rests
           in its finished pose without JS and under reduced motion,
           because all of it is gated on html.es-anim.
           ============================================================== */

        @property --dg-p {
            syntax: '<number>';
            inherits: true;
            initial-value: 0;
        }
        @property --dg-sweep {
            syntax: '<percentage>';
            inherits: false;
            initial-value: 0%;
        }

        #dg {
            --dg-ground: #f3efe8;
            --dg-ground-2: #eae5db;
            --dg-lit: #faf7f2;
            --dg-ink: #0f0f10;
            --dg-ink-2: #45433f;
            --dg-ink-3: #68645d;
            --dg-line: rgba(15, 15, 16, 0.16);
            --dg-hair: rgba(15, 15, 16, 0.5);
            --dg-ghost: 15, 15, 16;
            --dg-tape: #ffc21a;
            --dg-on-tape: #0f0f10;
            /* What a marked word wears: a strip of tape under ink by day, the tape's own yellow by night. */
            --dg-mark-ink: #0f0f10;
            --dg-mark-strip: linear-gradient(#ffc21a, #ffc21a);
            --dg-display: 'Italiana', 'Didot', 'Bodoni 72', 'Bodoni MT', Georgia, serif;
            --dg-caps: 'Josefin Sans', 'Avenir Next', 'Century Gothic', Futura, sans-serif;
            --dg-text: 'Mulish', 'Avenir Next', 'Segoe UI', system-ui, sans-serif;
            position: relative;
            background: var(--dg-ground);
            color: var(--dg-ink);
            font-family: var(--dg-text);
            font-size: 1.0625rem;
            line-height: 1.65;
        }
        .dark #dg {
            --dg-ground: #0f0f10;
            --dg-ground-2: #18181a;
            --dg-lit: #f3efe8;
            --dg-ink: #f3efe8;
            --dg-ink-2: #cbc6bc;
            --dg-ink-3: #9d988f;
            --dg-line: rgba(243, 239, 232, 0.18);
            --dg-hair: rgba(243, 239, 232, 0.55);
            --dg-ghost: 243, 239, 232;
            --dg-mark-ink: #ffc21a;
            --dg-mark-strip: none;
        }

        /* The bar above takes the floor's colour, so the page starts at the very top. */
        body > header.sticky {
            background-color: rgba(243, 239, 232, 0.9);
            border-bottom-color: rgba(15, 15, 16, 0.1);
        }
        .dark body > header.sticky {
            background-color: rgba(15, 15, 16, 0.9);
            border-bottom-color: rgba(243, 239, 232, 0.12);
        }

        #dg ::selection { background: #ffc21a; color: #0f0f10; }
        #dg a:focus-visible,
        #dg summary:focus-visible,
        #dg input:focus-visible {
            outline: 2px solid var(--dg-ink);
            outline-offset: 4px;
        }
        #dg .dg-band a:focus-visible,
        #dg .dg-band input:focus-visible,
        #dg .dg-wings a:focus-visible { outline-color: #ffc21a; }

        .dg-wrap { width: min(100% - 3rem, 80rem); margin-inline: auto; }
        .dg-sec { position: relative; padding-block: clamp(5.5rem, 12vw, 11rem); }
        .dg-sec + .dg-sec { border-top: 1px solid var(--dg-line); }

        /* ---------------------------------------------------------------
           The voices: a hairline display face, letterspaced caps, a text face
           --------------------------------------------------------------- */
        .dg-d { font-family: var(--dg-display); font-weight: 400; letter-spacing: -0.01em; line-height: 1.04; }
        .dg-label {
            font-family: var(--dg-caps);
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.32em;
            text-transform: uppercase;
            line-height: 1.4;
            color: var(--dg-ink-2);
        }
        .dg-num { font-family: var(--dg-caps); font-weight: 400; font-variant-numeric: lining-nums tabular-nums; }
        .dg-tier {
            display: inline-block;
            padding: 0.34rem 0.6rem 0.2rem;
            border: 1px solid var(--dg-hair);
            font-family: var(--dg-caps);
            font-weight: 700;
            font-size: 0.64rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1.2;
            color: var(--dg-ink);
            vertical-align: middle;
            white-space: nowrap;
        }
        .dg-tier-pro { background: var(--dg-ink); border-color: var(--dg-ink); color: var(--dg-ground); }
        .dg-tier-ent { background: var(--dg-tape); border-color: var(--dg-tape); color: var(--dg-on-tape); }

        /* Letters. A word never breaks inside itself; a letter is a box so it can move alone. */
        .dg-w { white-space: nowrap; }
        .dg-l { display: inline-block; }
        .dg-m {
            color: var(--dg-mark-ink);
            background-image: var(--dg-mark-strip);
            background-repeat: no-repeat;
            background-position: 0 90%;
            background-size: 100% 0.075em;
            transition: background-size 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.25s;
        }
        .dg-amp { font-family: Georgia, 'Times New Roman', serif; font-style: italic; font-size: 0.8em; }
        .dg-br { display: none; }
        @media (min-width: 900px) { .dg-br { display: inline; } }

        /* Canon: a heading arrives one letter after another, on to tape that is already down. */
        html.es-anim #dg [data-reveal="canon"],
        html.es-anim #dg [data-reveal="turn"] { opacity: 1; }
        .dg-l { transition: opacity 0.7s ease, translate 1s cubic-bezier(0.22, 1, 0.36, 1), rotate 1s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(var(--i, 0) * 26ms); }
        html.es-anim #dg [data-reveal="canon"]:not(.is-revealed) .dg-l { opacity: 0; translate: 0 0.42em; rotate: 7deg; }
        html.es-anim #dg [data-reveal="canon"]:not(.is-revealed) .dg-m { background-size: 0% 0.075em; }

        /* ---------------------------------------------------------------
           Spike tape: the marks a stage manager leaves on the floor
           --------------------------------------------------------------- */
        .dg-x,
        .dg-t { position: relative; display: inline-block; width: 1.1rem; height: 1.1rem; flex: none; }
        .dg-x::before,
        .dg-x::after,
        .dg-t::before,
        .dg-t::after {
            content: "";
            position: absolute;
            left: 0;
            top: calc(50% - 2px);
            width: 100%;
            height: 4px;
            background: var(--dg-tape);
            box-shadow: 0 1px 1.5px rgba(15, 15, 16, 0.3);
        }
        .dg-x::before { rotate: 43deg; }
        .dg-x::after { rotate: -48deg; }
        .dg-t::before { top: 0; }
        .dg-t::after { rotate: 90deg; top: calc(50% - 1px); scale: 0.82 1; }

        /* Two corners of a set piece, taped where it stands. */
        .dg-spike { position: relative; }
        .dg-spike::before,
        .dg-spike::after {
            content: "";
            position: absolute;
            width: 1.6rem;
            height: 1.6rem;
            border: 0 solid var(--dg-tape);
            filter: drop-shadow(0 1px 1px rgba(15, 15, 16, 0.3));
            pointer-events: none;
        }
        .dg-spike::before { top: 0; left: 0; border-top-width: 4px; border-left-width: 4px; }
        .dg-spike::after { bottom: 0; right: 0; border-bottom-width: 4px; border-right-width: 4px; }

        .dg-marks { display: grid; gap: 1.6rem; }
        .dg-marks li { display: grid; grid-template-columns: 1.1rem minmax(0, 1fr); gap: 1.2rem; align-items: start; }
        .dg-marks li > i { margin-top: 0.3rem; }
        .dg-marks strong { font-weight: 700; color: var(--dg-ink); }
        .dg-marks span { color: var(--dg-ink-2); }

        /* ---------------------------------------------------------------
           Buttons and links
           --------------------------------------------------------------- */
        .dg-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            padding: 1.25rem 1.7rem 1.05rem;
            background: var(--dg-tape);
            color: var(--dg-on-tape);
            font-family: var(--dg-caps);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            line-height: 1;
            box-shadow: 0 1px 2px rgba(15, 15, 16, 0.35);
            transition: gap 0.35s cubic-bezier(0.22, 1, 0.36, 1), padding 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dg-btn:hover { gap: 1.5rem; }
        .dg-btn svg { width: 1.05rem; height: 1.05rem; margin-top: -0.2rem; flex: none; }
        .dg-link {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            /* the top padding and its negative margin make the link 24px tall without moving it */
            padding-top: 0.2rem;
            margin-top: -0.2rem;
            padding-bottom: 0.35rem;
            border-bottom: 1px solid var(--dg-ink);
            font-family: var(--dg-caps);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            line-height: 1.2;
            color: var(--dg-ink);
            transition: gap 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dg-link:hover { gap: 1.2rem; }
        .dg-link svg { width: 1rem; height: 1rem; margin-top: -0.2rem; flex: none; }

        /* ---------------------------------------------------------------
           The count rail: one to eight down the edge of a wide screen,
           with a beat marker that keeps time with the scroll
           --------------------------------------------------------------- */
        .dg-rail,
        .dg-rail-beat { display: none; }
        @media (min-width: 1420px) {
            /* White set to difference against the page, so one rail reads on bone and on the dark floor alike.
               The nav is a box the size of the page that clips the list, so the list stays fixed to the
               screen and still ends where the page does: without the scroll-driven fade below (Firefox,
               reduced motion) it rode over the site footer. The box is the stacking context the fixed
               list lives in, so the blend has to sit on the box itself. */
            .dg-rail {
                display: block;
                position: absolute;
                inset: 0;
                z-index: 40;
                clip-path: inset(0);
                mix-blend-mode: difference;
                pointer-events: none;
            }
            .dg-rail ol { position: fixed; left: 1.5rem; top: calc(50% - 7.95rem); display: grid; gap: 0.1rem; padding-inline-start: 0.9rem; border-inline-start: 1px solid #474747; pointer-events: auto; }
            .dg-rail a { position: relative; display: flex; align-items: center; min-width: 1.5rem; height: 1.5rem; }
            .dg-rail b { width: 0.9rem; font-family: var(--dg-caps); font-weight: 700; font-size: 0.8125rem; line-height: 1; text-align: center; color: #a3a3a3; transition: color 0.3s ease; }
            .dg-rail a:hover b,
            .dg-rail a:focus-visible b,
            .dg-rail a.is-active b { color: #fff; }
            #dg .dg-rail a:focus-visible { outline-color: #fff; }
            .dg-rail span {
                position: absolute;
                left: 1.7rem;
                top: 50%;
                translate: -0.3rem -50%;
                padding: 0.45rem 0.7rem 0.3rem;
                background: #fff;
                color: #000;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease, translate 0.3s ease;
            }
            .dg-rail a:hover span,
            .dg-rail a:focus-visible span { opacity: 1; translate: 0 -50%; }
            /* The beat is tape, so it stays out of the blend: its own fixed mark, travelling the same 15.9rem. */
            .dg-rail-beat { position: fixed; left: calc(1.5rem - 3px); top: calc(50% - 7.95rem + var(--dg-p) * (15.9rem - 3px)); z-index: 41; width: 7px; height: 3px; background: var(--dg-tape); box-shadow: 0 1px 1px rgba(15, 15, 16, 0.3); pointer-events: none; }
        }
        @supports (animation-timeline: scroll()) {
            html.es-anim #dg .dg-rail { animation: dg-rail-out linear both; animation-timeline: scroll(root block); }
            html.es-anim #dg .dg-rail-beat { animation: dg-p linear both, dg-rail-out linear both; animation-timeline: scroll(root block), scroll(root block); }
            @media (min-width: 1420px) {
                html.es-anim #dg .dg-rail-beat { display: block; }
            }
        }
        @keyframes dg-rail-out { 0%, 94.5% { opacity: 1; visibility: visible; } 96.5%, 100% { opacity: 0; visibility: hidden; } }
        @keyframes dg-p { from { --dg-p: 0; } to { --dg-p: 1; } }

        /* ---------------------------------------------------------------
           Hero: five, six, seven, eight, and the headline on the floor
           --------------------------------------------------------------- */
        .dg-hero { position: relative; overflow: clip; padding-block: clamp(2.25rem, 5vw, 4rem) clamp(4rem, 8vw, 7rem); }
        .dg-hero-in { container-type: inline-size; }
        .dg-countin { display: flex; align-items: flex-end; gap: clamp(1.1rem, 2.4vw, 2rem); margin-bottom: clamp(2rem, 5vw, 3.5rem); }
        .dg-countin b {
            position: relative;
            padding-bottom: 0.7rem;
            font-family: var(--dg-caps);
            font-weight: 400;
            font-size: 1.25rem;
            line-height: 1;
            color: var(--dg-ink-3);
        }
        .dg-countin b::after {
            content: "";
            position: absolute;
            inset: auto -0.2rem 0 -0.2rem;
            height: 3px;
            background: var(--dg-tape);
            box-shadow: 0 1px 1px rgba(15, 15, 16, 0.3);
            scale: 0 1;
        }
        .dg-countin b:last-of-type { color: var(--dg-ink); }
        .dg-countin b:last-of-type::after { scale: 1 1; }
        html.es-anim #dg .dg-countin b { animation: dg-count 2.4s linear infinite; animation-delay: calc(var(--b) * 0.6s); }
        html.es-anim #dg .dg-countin b::after { animation: dg-count-mark 2.4s linear infinite; animation-delay: calc(var(--b) * 0.6s); }
        @keyframes dg-count { 0%, 24.9% { color: var(--dg-ink); } 25%, 100% { color: var(--dg-ink-3); } }
        @keyframes dg-count-mark { 0%, 24.9% { scale: 1 1; } 25%, 100% { scale: 0 1; } }
        /* The metronome: a hairline arm with a weight on it, one swing to the beat. */
        .dg-metro { position: relative; width: 2.4rem; height: 2.6rem; margin-inline-start: 0.4rem; }
        .dg-metro i { position: absolute; left: 50%; bottom: 0; width: 1px; height: 100%; background: var(--dg-ink); transform-origin: 50% 100%; }
        .dg-metro i::after { content: ""; position: absolute; left: -4px; top: 26%; width: 9px; height: 6px; background: var(--dg-tape); box-shadow: 0 1px 1px rgba(15, 15, 16, 0.3); }
        .dg-metro::after { content: ""; position: absolute; left: 20%; right: 20%; bottom: 0; height: 1px; background: var(--dg-ink); }
        html.es-anim #dg .dg-metro i { animation: dg-swing 1.2s ease-in-out infinite; }
        @keyframes dg-swing { 0%, 100% { rotate: -15deg; } 50% { rotate: 15deg; } }

        #dg .dg-eyebrow { display: block; margin-bottom: clamp(1.25rem, 3vw, 2rem); font-family: var(--dg-caps); font-weight: 700; font-size: 0.75rem; letter-spacing: 0.32em; text-transform: uppercase; line-height: 1.4; color: var(--dg-ink-2); }
        .dg-h1 { font-family: var(--dg-display); font-weight: 400; }
        .dg-h1-rows { position: relative; display: block; font-size: clamp(3.6rem, 11.2cqi, 10.25rem); line-height: 0.98; letter-spacing: -0.012em; --dg-base: 0.15em; }
        .dg-row { display: block; }
        /* The second row stands on the floor: its box ends at the baseline, and the floor gives it back. */
        .dg-stand {
            position: relative;
            width: fit-content;
            margin-inline-start: 8cqi;
            -webkit-box-reflect: below -0.3em linear-gradient(transparent 52%, rgba(255, 255, 255, 0.26));
        }
        .dg-h1 .dg-m { background-image: none; }
        .dg-floor {
            position: absolute;
            inset-inline: calc((100% - 100vw) / 2);
            bottom: calc(var(--dg-base) - clamp(4px, 0.05em, 7px));
            height: clamp(4px, 0.05em, 7px);
            background: var(--dg-tape);
            box-shadow: 0 1px 2px rgba(15, 15, 16, 0.28);
            transform-origin: 0 50%;
        }
        html.es-anim #dg .dg-floor { animation: dg-lay 1.4s cubic-bezier(0.22, 1, 0.36, 1) 0.1s both; }
        @keyframes dg-lay { from { scale: 0 1; } to { scale: 1 1; } }

        /* Entrance in canon, then a slow wave through the marked words with a long-exposure trail. */
        html.es-anim #dg .dg-h1 .dg-l {
            animation: dg-enter 1.15s cubic-bezier(0.22, 1, 0.36, 1) both;
            animation-delay: calc(var(--i) * 46ms + 0.45s);
            transition: none;
        }
        html.es-anim #dg .dg-h1 .dg-m .dg-l {
            animation: dg-enter 1.15s cubic-bezier(0.22, 1, 0.36, 1) both, dg-wave 7.5s cubic-bezier(0.45, 0, 0.55, 1) infinite;
            animation-delay: calc(var(--i) * 46ms + 0.45s), calc(var(--i) * 130ms + 0.4s);
        }
        @keyframes dg-enter { from { opacity: 0; translate: 0 0.55em; rotate: 9deg; } to { opacity: 1; translate: 0 0; rotate: 0deg; } }
        @keyframes dg-wave {
            0%, 16%, 100% { translate: 0 0; rotate: 0deg; text-shadow: 0 0 0 rgba(var(--dg-ghost), 0), 0 0 0 rgba(var(--dg-ghost), 0), 0 0 0 rgba(var(--dg-ghost), 0); }
            7% { translate: 0.015em -0.075em; rotate: -3deg; text-shadow: -0.035em 0.03em 0 rgba(var(--dg-ghost), 0.2), -0.07em 0.06em 0 rgba(var(--dg-ghost), 0.11), -0.105em 0.09em 0 rgba(var(--dg-ghost), 0.05); }
        }
        .dark #dg .dg-h1 .dg-m .dg-l { --dg-ghost: 255, 194, 26; }

        .dg-hero-foot { display: grid; gap: 2rem 3rem; align-items: end; margin-top: clamp(5.5rem, 10cqi, 9rem); }
        @media (min-width: 900px) { .dg-hero-foot { grid-template-columns: minmax(0, 34rem) minmax(0, 1fr); } }
        .dg-hero-lede { font-size: clamp(1.1rem, 1.5vw, 1.3rem); line-height: 1.55; color: var(--dg-ink-2); }
        .dg-hero-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem 2.25rem; }
        @media (min-width: 900px) { .dg-hero-cta { justify-content: flex-end; } }

        /* A phone: one step to a line, travelling across the floor. */
        @media (max-width: 719px) {
            .dg-h1-rows { font-size: clamp(3rem, 19.2cqi, 5.2rem); line-height: 0.96; }
            .dg-step { display: block; margin-inline-start: var(--s, 0%); }
            .dg-stand { width: auto; margin-inline-start: 0; -webkit-box-reflect: below -0.28em linear-gradient(transparent 80%, rgba(255, 255, 255, 0.26)); }
            .dg-hero-foot { margin-top: clamp(4.25rem, 20cqi, 6rem); }
        }

        /* ---------------------------------------------------------------
           Three strands: a word, and the echoes it leaves as it crosses
           --------------------------------------------------------------- */
        .dg-strands { position: relative; padding-bottom: clamp(5rem, 10vw, 9rem); }
        .dg-strand { position: relative; padding-block: clamp(1.1rem, 2.2vw, 2rem); border-top: 1px solid var(--dg-line); }
        .dg-echo { display: flex; justify-content: center; overflow: clip; overflow-clip-margin: 2rem; padding-block: 0.4rem 0; }
        .dg-echo-row {
            flex: none;
            display: flex;
            align-items: baseline;
            gap: 0.3em;
            font-family: var(--dg-display);
            font-size: clamp(5.5rem, 16vw, 15rem);
            line-height: 0.92;
            letter-spacing: -0.012em;
            white-space: nowrap;
            translate: var(--x0, 0) 0;
        }
        .dg-echo-row span { color: rgba(var(--dg-ghost), var(--o)); }
        .dg-echo-row .dg-on { position: relative; color: var(--dg-mark-ink); }
        .dg-echo-row .dg-on::after {
            content: "";
            position: absolute;
            left: -0.05em;
            right: -0.05em;
            bottom: 0.13em;
            height: 0.05em;
            background: var(--dg-tape);
            box-shadow: 0 1px 2px rgba(15, 15, 16, 0.28);
            rotate: -0.8deg;
        }
        @supports (animation-timeline: view()) {
            html.es-anim #dg .dg-echo-row {
                animation: dg-drift linear both;
                animation-timeline: view();
                animation-range: entry 0% exit 100%;
            }
        }
        @keyframes dg-drift {
            from { translate: calc(var(--x0, 0px) + var(--dir, 1) * -11vw) 0; }
            to { translate: calc(var(--x0, 0px) + var(--dir, 1) * 11vw) 0; }
        }
        .dg-strand-cap { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.35rem 2.5rem; align-items: baseline; padding-top: 0.6rem; }
        @media (min-width: 760px) { .dg-strand-cap { grid-template-columns: 9rem minmax(0, 1.2fr) minmax(0, 1fr) minmax(0, 1fr); } }
        .dg-strand-cap strong { font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.5rem, 2.4vw, 2rem); line-height: 1.1; }
        .dg-strand-when { font-family: var(--dg-caps); font-size: 0.875rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--dg-ink-2); }
        .dg-strand-note { font-weight: 700; color: var(--dg-ink); }
        @media (min-width: 760px) { .dg-strand-note { text-align: end; } }
        .dg-strand-note.is-draft { font-weight: 400; color: var(--dg-ink-3); }
        .dg-strands-foot { margin-top: clamp(3rem, 6vw, 5rem); padding-top: clamp(2.5rem, 5vw, 4rem); border-top: 1px solid var(--dg-line); }
        .dg-strands-foot p { max-width: 30ch; font-family: var(--dg-display); font-size: clamp(1.7rem, 3.2vw, 2.7rem); line-height: 1.16; letter-spacing: -0.01em; text-wrap: balance; }

        /* ---------------------------------------------------------------
           A count: the numeral on the floor, its place in the eight, the heading
           --------------------------------------------------------------- */
        .dg-head { display: grid; gap: 1.25rem 3rem; margin-bottom: clamp(3rem, 7vw, 6rem); }
        @media (min-width: 900px) { .dg-head { grid-template-columns: minmax(0, 13.5rem) minmax(0, 1fr); align-items: start; } }
        .dg-count { font-family: var(--dg-caps); font-weight: 400; font-size: clamp(6.5rem, 14vw, 12.5rem); line-height: 0.74; letter-spacing: -0.03em; padding-bottom: 0.3em; }
        .dg-count-n { display: inline-block; padding-top: 0.14em; -webkit-box-reflect: below -0.24em linear-gradient(transparent 58%, rgba(255, 255, 255, 0.22)); }
        html.es-anim #dg [data-reveal="turn"]:not(.is-revealed) .dg-count-n { opacity: 0; }
        html.es-anim #dg [data-reveal="turn"].is-revealed .dg-count-n { animation: dg-turn 1.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
        @keyframes dg-turn { from { opacity: 0; transform: perspective(40rem) rotateY(-200deg); } to { opacity: 1; transform: perspective(40rem) rotateY(0deg); } }
        .dg-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 1.5rem; margin-bottom: clamp(1.25rem, 2.5vw, 2rem); }
        .dg-bar { display: inline-flex; align-items: flex-end; gap: 0.5rem; height: 1rem; }
        .dg-bar i { width: 1px; height: 0.5rem; background: var(--dg-hair); opacity: 0.55; }
        .dg-bar i.is-past { opacity: 1; background: var(--dg-ink); }
        .dg-bar i.is-on { width: 4px; height: 1rem; opacity: 1; background: var(--dg-tape); box-shadow: 0 1px 1px rgba(15, 15, 16, 0.3); }
        .dg-h2 { font-family: var(--dg-display); font-weight: 400; font-size: clamp(2.5rem, 6.1vw, 5.5rem); line-height: 1.03; letter-spacing: -0.012em; text-wrap: balance; }
        .dg-lede { margin-top: clamp(1.5rem, 3vw, 2.25rem); max-width: 37rem; font-size: 1.125rem; color: var(--dg-ink-2); }
        .dg-h3 { font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.6rem, 2.5vw, 2rem); line-height: 1.12; letter-spacing: -0.005em; }
        .dg-two { display: grid; gap: clamp(3rem, 6vw, 5rem) clamp(3rem, 7vw, 7rem); }
        @media (min-width: 960px) { .dg-two { grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr); align-items: start; } }
        .dg-note { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.6rem 0.9rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--dg-line); font-size: 0.95rem; color: var(--dg-ink-2); }

        /* 1. The week: a timetable set as type, every place a mark */
        .dg-tt { padding: clamp(1.75rem, 3.5vw, 2.75rem); }
        .dg-tt-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: baseline; gap: 0.5rem 1rem; padding-bottom: 1.1rem; border-bottom: 1px solid var(--dg-ink); }
        .dg-tt-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.3rem 1.5rem; align-items: end; padding-block: 1.5rem; border-bottom: 1px solid var(--dg-line); }
        /* On a phone the name takes the whole line and the count sits under it: beside each other,
           "Contemporary" was wider than its column and ran into the figure. */
        @media (max-width: 480px) {
            #dg .dg-tt-row { grid-template-columns: minmax(0, 1fr); }
            #dg .dg-tt-left { display: flex; align-items: baseline; gap: 0.9rem; text-align: start; }
            #dg .dg-tt-left p + p { margin-top: 0; }
        }
        .dg-tt-when { margin-top: 0.4rem; font-family: var(--dg-caps); font-size: 0.8125rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--dg-ink-2); }
        .dg-tt-left { text-align: end; }
        .dg-tt-left p:first-child { font-family: var(--dg-caps); font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; line-height: 1; }
        .dg-tt-left p:first-child b { display: inline-block; margin-inline-end: 0.2rem; font-weight: 400; font-size: 2.4rem; letter-spacing: -0.02em; vertical-align: -0.12em; }
        .dg-tt-left .is-full { color: var(--dg-ink-3); }
        .dg-tt-left p + p { margin-top: 0.45rem; font-family: var(--dg-caps); font-size: 0.75rem; letter-spacing: 0.14em; color: var(--dg-ink-3); }
        .dg-cap { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 5px; margin-top: 0.7rem; }
        .dg-cap i { width: 3px; height: 1.05rem; background: var(--dg-ink); }
        .dg-cap i.is-free { background: var(--dg-tape); box-shadow: 0 1px 1px rgba(15, 15, 16, 0.3); }
        html.es-anim #dg [data-reveal]:not(.is-revealed) .dg-cap i { scale: 1 0; }
        .dg-cap i { transform-origin: 50% 100%; transition: scale 0.5s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(var(--k, 0) * 22ms + 0.3s); }
        .dg-closed { padding-top: 1.5rem; }
        .dg-closed ul { display: flex; flex-wrap: wrap; gap: 0.75rem 2rem; margin-top: 0.9rem; }
        .dg-closed li { display: inline-flex; align-items: center; gap: 0.7rem; font-family: var(--dg-caps); font-size: 0.875rem; letter-spacing: 0.12em; text-transform: uppercase; }
        .dg-closed li i { width: 0.85rem; height: 0.85rem; }
        .dg-closed p:last-child { margin-top: 1rem; font-size: 0.9rem; color: var(--dg-ink-2); }

        /* 2. The rehearsal: the wings are dark, the house is lit */
        .dg-duplex { position: relative; display: grid; }
        @media (min-width: 900px) { .dg-duplex { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }
        .dg-duplex > i { display: none; }
        @media (min-width: 900px) {
            .dg-duplex > i { display: block; position: absolute; left: 50%; top: -0.55rem; z-index: 2; translate: -50% 0; width: 1.4rem; height: 1.4rem; }
        }
        .dg-side { padding: clamp(1.75rem, 4vw, 3.5rem); }
        .dg-wings {
            background-color: #0f0f10;
            background-image: linear-gradient(104deg, transparent 0 34%, rgba(255, 255, 255, 0.045) 46%, transparent 60% 100%);
            color: #f3efe8;
        }
        .dark .dg-wings { background-color: #1b1b1e; }
        .dg-house { background-color: var(--dg-lit); color: #0f0f10; box-shadow: inset 0 0 0 1px rgba(15, 15, 16, 0.14); }
        .dg-side-kick { display: block; font-family: var(--dg-caps); font-weight: 400; font-size: 0.75rem; letter-spacing: 0.32em; text-transform: uppercase; }
        .dg-wings .dg-side-kick { color: #b9b4aa; }
        .dg-house .dg-side-kick { color: #5c5852; }
        .dg-side-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.4rem 1rem; margin-top: 0.5rem; padding-bottom: 1.1rem; border-bottom: 1px solid currentColor; }
        .dg-side-head .dg-label { color: inherit; }
        .dg-wings .dg-tier { border-color: rgba(243, 239, 232, 0.6); color: #f3efe8; }
        .dg-side-url { font-family: var(--dg-caps); font-size: 0.8125rem; letter-spacing: 0.08em; color: #5c5852; }
        .dg-ev { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 0.2rem 1rem; padding-block: 1rem; border-bottom: 1px solid rgba(243, 239, 232, 0.16); }
        .dg-house .dg-ev { border-bottom-color: rgba(15, 15, 16, 0.14); }
        .dg-ev-name { font-family: var(--dg-display); font-size: clamp(1.35rem, 2vw, 1.7rem); line-height: 1.15; }
        .dg-ev-when { margin-top: 0.3rem; font-family: var(--dg-caps); font-size: 0.75rem; letter-spacing: 0.16em; text-transform: uppercase; }
        .dg-wings .dg-ev-when { color: #b9b4aa; }
        .dg-house .dg-ev-when { color: #5c5852; }
        .dg-state { padding: 0.36rem 0.6rem 0.22rem; border: 1px solid currentColor; font-family: var(--dg-caps); font-weight: 700; font-size: 0.64rem; letter-spacing: 0.24em; text-transform: uppercase; line-height: 1.2; }
        .dg-state-public { background: #f3efe8; border-color: #f3efe8; color: #0f0f10; }
        .dg-state-draft { border-style: dashed; color: #cbc6bc; }
        .dg-ev.is-draft .dg-ev-name { color: #cbc6bc; }
        .dg-side-foot { margin-top: 1.4rem; font-size: 0.9rem; }
        .dg-wings .dg-side-foot { color: #cbc6bc; }
        .dg-house .dg-side-foot { color: #45433f; }
        @media (min-width: 900px) {
            .dg-ev { height: 5.75rem; padding-block: 0; }
            /* The house prints the same four rows; the two calls are not on them. */
            .dg-house-list {
                display: grid;
                grid-template-rows: repeat(4, 5.75rem);
                background: repeating-linear-gradient(to bottom, transparent 0 calc(5.75rem - 1px), rgba(15, 15, 16, 0.14) calc(5.75rem - 1px) 5.75rem);
            }
            .dg-house-list .dg-ev { border-bottom: 0; }
            .dg-house-list .dg-ev:nth-child(2) { grid-row: 4; }
        }
        .dg-after { display: grid; gap: 2.5rem clamp(3rem, 7vw, 7rem); margin-top: clamp(3.5rem, 7vw, 6rem); }
        @media (min-width: 800px) { .dg-after { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .dg-after-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 1rem; margin-bottom: 0.9rem; }
        .dg-after p { color: var(--dg-ink-2); max-width: 34rem; }

        /* 3. The card: ten counts, punched one at a time */
        .dg-punch-wrap { container-type: inline-size; max-width: 31rem; }
        .dg-punch {
            position: relative;
            display: grid;
            grid-template-rows: auto 1fr auto;
            gap: 1.25rem;
            aspect-ratio: 1.72;
            padding: clamp(1.1rem, 5cqi, 1.75rem);
            background: var(--dg-ink);
            color: var(--dg-ground);
            rotate: -2.2deg;
            box-shadow: 0 1.5rem 2.5rem -1.5rem rgba(15, 15, 16, 0.55);
            transition: rotate 0.8s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dg-punch-wrap:hover .dg-punch { rotate: 0.6deg; }
        .dg-punch::before { content: ""; position: absolute; left: -1.1rem; top: 0.9rem; width: 3.6rem; height: 1.15rem; background: var(--dg-tape); box-shadow: 0 1px 2px rgba(15, 15, 16, 0.35); rotate: -38deg; }
        .dg-punch-top,
        .dg-punch-foot { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; font-family: var(--dg-caps); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.28em; text-transform: uppercase; }
        .dg-punch-top span:first-child { font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.4rem, 7cqi, 2rem); letter-spacing: 0; text-transform: none; line-height: 1; }
        .dg-punch-holes { display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.6rem; align-content: center; justify-items: center; }
        .dg-punch-holes i {
            display: grid;
            place-items: center;
            width: min(100%, 2.7rem);
            aspect-ratio: 1;
            border-radius: 50%;
            box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--dg-ground) 55%, transparent);
            font-family: var(--dg-caps);
            font-style: normal;
            font-size: 0.8125rem;
            line-height: 1;
            padding-top: 0.15rem;
        }
        .dg-punch-holes i.is-out { background: var(--dg-ground); box-shadow: inset 0 2px 3px rgba(0, 0, 0, 0.4); color: transparent; }
        .dg-punch-holes i.is-out:nth-child(2) { translate: 1px -1px; }
        .dg-punch-holes i.is-out:nth-child(3) { translate: -1px 2px; }
        .dg-buy { margin-top: clamp(2.75rem, 6vw, 4rem); }
        .dg-buy-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1.1rem; border-bottom: 1px solid var(--dg-ink); }
        .dg-buy-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.2rem 1.5rem; align-items: baseline; padding-block: 1.2rem; border-bottom: 1px solid var(--dg-line); }
        .dg-buy-name { font-family: var(--dg-display); font-size: clamp(1.45rem, 2.2vw, 1.85rem); line-height: 1.15; }
        .dg-buy-price { font-family: var(--dg-caps); font-size: 1.5rem; line-height: 1; }
        .dg-buy-kind { grid-column: 1 / -1; font-size: 0.95rem; color: var(--dg-ink-2); }
        .dg-buy-kind b { margin-inline-end: 0.6rem; font-family: var(--dg-caps); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.24em; text-transform: uppercase; color: var(--dg-ink); }
        .dg-buy-foot { margin-top: 1.2rem; font-size: 0.9rem; color: var(--dg-ink-2); }
        .dg-facts { display: grid; gap: 2.25rem; }
        .dg-facts h3 { font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.5rem, 2.3vw, 1.9rem); line-height: 1.15; }
        .dg-facts p { margin-top: 0.5rem; color: var(--dg-ink-2); }
        .dg-facts li { display: grid; grid-template-columns: 1.1rem minmax(0, 1fr); gap: 1.4rem; }
        .dg-facts li > i { margin-top: 0.55rem; }

        /* 4. The show: the house list for the night */
        .dg-bill { padding: clamp(1.75rem, 3.5vw, 2.75rem); }
        .dg-bill-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.4rem 1rem; }
        .dg-bill-head h3 { font-size: clamp(2.2rem, 4vw, 3.4rem); line-height: 1; }
        .dg-bill-sub { margin-top: 0.6rem; padding-bottom: 1.1rem; border-bottom: 1px solid var(--dg-ink); color: var(--dg-ink-2); }
        .dg-bill-row { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 0.2rem 1.5rem; align-items: baseline; padding-block: 1.15rem; border-bottom: 1px solid var(--dg-line); }
        .dg-bill-name { font-family: var(--dg-display); font-size: clamp(1.4rem, 2.1vw, 1.8rem); line-height: 1.15; }
        .dg-bill-win { grid-column: 1 / -1; grid-row: 2; font-family: var(--dg-caps); font-size: 0.75rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--dg-ink-2); }
        .dg-bill-qty { font-family: var(--dg-caps); font-size: 0.875rem; letter-spacing: 0.08em; color: var(--dg-ink-3); }
        .dg-bill-price { min-width: 3.2rem; text-align: end; font-family: var(--dg-caps); font-size: 1.5rem; line-height: 1; }
        .dg-bill-foot { margin-top: 1.3rem; font-size: 0.9rem; color: var(--dg-ink-2); }
        .dg-notes { display: grid; gap: 2.5rem; }
        .dg-notes-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 1rem; margin-bottom: 0.7rem; }
        .dg-notes p { color: var(--dg-ink-2); }
        .dg-notes li { display: grid; grid-template-columns: 1.1rem minmax(0, 1fr); gap: 1.4rem; }
        .dg-notes li > i { margin-top: 0.6rem; }

        /* 5. In public: one address, and what hangs from it */
        .dg-url { margin-bottom: clamp(3rem, 6vw, 5rem); border-block: 1px solid var(--dg-line); }
        .dg-url .dg-echo-row { font-size: clamp(2.6rem, 7.4vw, 7rem); gap: 0.6em; padding-block: 0.28em 0.2em; }
        .dg-url .dg-echo-row .dg-on::after { bottom: 0.02em; }
        .dg-six { display: grid; gap: 0 clamp(2.5rem, 5vw, 5rem); }
        @media (min-width: 720px) { .dg-six { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .dg-six { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .dg-six article { padding-block: 2rem 2.25rem; border-top: 1px solid var(--dg-line); }
        .dg-six-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 1rem; margin-bottom: 0.8rem; }
        .dg-six p { color: var(--dg-ink-2); }

        /* 6. Who it is for: the company list */
        .dg-who { border-bottom: 1px solid var(--dg-line); }
        .dg-who-row { display: grid; gap: 0.6rem 2.5rem; padding-block: clamp(1.5rem, 3vw, 2.4rem); border-top: 1px solid var(--dg-line); }
        @media (min-width: 900px) { .dg-who-row { grid-template-columns: 3rem minmax(0, 1.25fr) minmax(0, 1fr); align-items: baseline; } }
        .dg-who-no { font-family: var(--dg-caps); font-size: 0.8125rem; letter-spacing: 0.2em; color: var(--dg-ink-3); }
        .dg-who-name { position: relative; width: fit-content; font-family: var(--dg-display); font-weight: 400; font-size: clamp(2rem, 4.7vw, 4.1rem); line-height: 1.04; letter-spacing: -0.012em; transform-origin: 0 100%; transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1); }
        .dg-who-name::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0.02em; height: 0.06em; background: var(--dg-tape); box-shadow: 0 1px 1.5px rgba(15, 15, 16, 0.28); scale: 0 1; transform-origin: 0 50%; transition: scale 0.7s cubic-bezier(0.22, 1, 0.36, 1); }
        @media (hover: hover) {
            .dg-who-row:hover .dg-who-name { transform: skewX(-9deg) translateX(0.12em); }
            .dg-who-row:hover .dg-who-name::after { scale: 1 1; }
        }
        .dg-who-row p { color: var(--dg-ink-2); }
        .dg-who-row a { display: inline-flex; margin-top: 0.9rem; }

        /* 7. How it works: the dark band, a marley floor under work light */
        .dg-band {
            position: relative;
            overflow: clip;
            background-color: #0f0f10;
            background-image:
                linear-gradient(103deg, transparent 0 36%, rgba(255, 255, 255, 0.04) 47%, transparent 60% 100%),
                repeating-linear-gradient(90deg, transparent 0 calc(25% - 1px), rgba(255, 255, 255, 0.05) calc(25% - 1px) 25%);
            color: #f3efe8;
            border-top: 0;
        }
        .dark .dg-band { background-color: #18181a; }
        #dg .dg-band + .dg-sec,
        #dg .dg-sec + .dg-band { border-top: 0; }
        .dg-band .dg-label { color: #cbc6bc; }
        .dg-band .dg-m { color: #ffc21a; background-image: none; }
        .dg-band .dg-bar i { background: rgba(243, 239, 232, 0.55); }
        .dg-band .dg-bar i.is-past { background: #f3efe8; }
        .dg-band .dg-bar i.is-on { background: #ffc21a; }
        .dg-steps { position: relative; display: grid; gap: 3.5rem 3rem; }
        @media (min-width: 860px) { .dg-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .dg-steps .dg-step-n { display: inline-block; margin: 0; max-width: none; font-family: var(--dg-caps); font-weight: 400; font-size: clamp(4.5rem, 8vw, 7rem); line-height: 0.74; letter-spacing: -0.03em; color: #ffc21a; padding-top: 0.14em; -webkit-box-reflect: below -0.24em linear-gradient(transparent 55%, rgba(255, 255, 255, 0.24)); }
        .dg-steps h3 { margin-top: 3.25rem; font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.8rem, 2.8vw, 2.4rem); line-height: 1.1; }
        .dg-steps p { margin-top: 0.9rem; max-width: 24rem; color: #cbc6bc; }
        /* The beat crossing the floor: one hairline, eased, that the three steps keep time with. */
        .dg-sweep { display: none; }
        @media (min-width: 860px) {
            .dg-sweep { display: block; position: absolute; top: -1.5rem; bottom: -1.5rem; left: var(--dg-sweep); width: 1px; background: linear-gradient(to bottom, transparent, #ffc21a 18%, #ffc21a 82%, transparent); opacity: 0; }
            html.es-anim #dg .dg-sweep { animation: dg-sweep 7.2s cubic-bezier(0.65, 0, 0.35, 1) infinite, dg-sweep-show 7.2s linear infinite; }
        }
        /* Two sets of keyframes on purpose: Safari stops animating an ordinary property that
           shares its keyframes with a custom property, which left the hairline at opacity 0. */
        @keyframes dg-sweep {
            0% { --dg-sweep: 0%; }
            30% { --dg-sweep: 33.4%; }
            38% { --dg-sweep: 33.4%; }
            60% { --dg-sweep: 66.8%; }
            68% { --dg-sweep: 66.8%; }
            100% { --dg-sweep: 100%; }
        }
        @keyframes dg-sweep-show {
            0% { opacity: 0; }
            8% { opacity: 0.8; }
            92% { opacity: 0.8; }
            100% { opacity: 0; }
        }

        /* Between counts: the repertoire, the plans, the neighbours */
        .dg-aside-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1.5rem 2rem; margin-bottom: clamp(2rem, 4vw, 3rem); }
        .dg-aside-head h2 { font-family: var(--dg-display); font-weight: 400; font-size: clamp(2.2rem, 4.6vw, 3.8rem); line-height: 1.04; letter-spacing: -0.012em; }
        .dg-rep { border-bottom: 1px solid var(--dg-line); }
        .dg-rep a { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.3rem 2rem; align-items: baseline; padding-block: 1.5rem; border-top: 1px solid var(--dg-line); transition: padding 0.5s cubic-bezier(0.22, 1, 0.36, 1); }
        @media (min-width: 900px) { .dg-rep a { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr) auto; } }
        .dg-rep a:hover { padding-inline-start: 1.25rem; }
        .dg-rep strong { font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.7rem, 3vw, 2.6rem); line-height: 1.1; }
        .dg-rep span { grid-column: 1; color: var(--dg-ink-2); }
        @media (min-width: 900px) { .dg-rep span { grid-column: 2; } }
        .dg-rep svg { grid-column: 2; grid-row: 1; width: 1.4rem; height: 1.4rem; align-self: center; transition: translate 0.5s cubic-bezier(0.22, 1, 0.36, 1); }
        @media (min-width: 900px) { .dg-rep svg { grid-column: 3; } }
        .dg-rep a:hover svg { translate: 0.4rem 0; }
        .dg-next { display: grid; gap: 0 clamp(2rem, 4vw, 4rem); border-bottom: 1px solid var(--dg-line); }
        @media (min-width: 640px) { .dg-next { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .dg-next { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .dg-next a { display: block; padding-block: 1.6rem 1.75rem; border-top: 1px solid var(--dg-line); }
        .dg-next small { display: block; font-family: var(--dg-caps); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.24em; text-transform: uppercase; color: var(--dg-ink-3); }
        .dg-next strong { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-top: 0.6rem; font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.6rem, 2.3vw, 2.1rem); line-height: 1.1; }
        .dg-next svg { width: 1.2rem; height: 1.2rem; flex: none; transition: translate 0.5s cubic-bezier(0.22, 1, 0.36, 1); }
        .dg-next a:hover svg { translate: 0.4rem 0; }

        /* The plan band and the closing strip are shared partials: same words, same prices, this floor. */
        #dg .dg-plans > section { background: var(--dg-ground); border-top: 1px solid var(--dg-line); }
        #dg .dg-plans h2 { font-family: var(--dg-display); font-weight: 400; font-size: clamp(2.2rem, 4.6vw, 3.8rem); line-height: 1.06; letter-spacing: -0.012em; color: var(--dg-ink); }
        #dg .dg-plans h2 + p { color: var(--dg-ink-2); font-size: 1.0625rem; }
        #dg .dg-plans .grid > div { background: transparent; border: 0; border-top: 1px solid var(--dg-ink); border-radius: 0; box-shadow: none; color: var(--dg-ink); padding: 1.75rem 0.25rem 1.5rem; }
        #dg .dg-plans .grid > div:hover { transform: none; box-shadow: none; }
        #dg .dg-plans .grid > div:nth-child(2) { border-top: 4px solid var(--dg-tape); padding-top: calc(1.75rem - 3px); }
        #dg .dg-plans .grid > div span,
        #dg .dg-plans .grid > div p,
        #dg .dg-plans .grid > div li { color: var(--dg-ink-2); }
        #dg .dg-plans .grid > div .text-3xl { font-family: var(--dg-caps); font-weight: 400; font-size: 3.2rem; letter-spacing: -0.02em; color: var(--dg-ink); }
        #dg .dg-plans .grid > div .uppercase { font-family: var(--dg-caps); letter-spacing: 0.28em; color: var(--dg-ink); }
        #dg .dg-plans .grid > div .rounded-full { background: var(--dg-tape); color: #0f0f10; border-radius: 0; font-family: var(--dg-caps); letter-spacing: 0.18em; padding: 0.3rem 0.5rem 0.15rem; }
        #dg .dg-plans .grid > div svg { color: var(--dg-ink); }
        #dg .dg-plans a.font-medium { color: var(--dg-ink); border-bottom: 1px solid var(--dg-ink); font-family: var(--dg-caps); font-weight: 700; font-size: 0.8125rem; letter-spacing: 0.24em; text-transform: uppercase; padding-bottom: 0.3rem; }
        #dg .dg-plans a.rounded-2xl { background: var(--dg-tape); color: #0f0f10; border-radius: 0; box-shadow: 0 1px 2px rgba(15, 15, 16, 0.35); font-family: var(--dg-caps); font-weight: 700; font-size: 0.8125rem; letter-spacing: 0.26em; text-transform: uppercase; padding: 1.25rem 1.7rem 1.05rem; }
        #dg .dg-plans a.rounded-2xl:hover { transform: none; box-shadow: 0 1px 2px rgba(15, 15, 16, 0.35); }

        #dg .dg-keep > section { background: var(--dg-ground-2); border-top: 1px solid var(--dg-line); }
        #dg .dg-keep h2 { font-family: var(--dg-display); font-weight: 400; font-size: clamp(2rem, 4vw, 3rem); line-height: 1.05; color: var(--dg-ink); }
        #dg .dg-keep p.uppercase { font-family: var(--dg-caps); font-weight: 700; letter-spacing: 0.32em; color: var(--dg-ink-2); }
        #dg .dg-keep .grid > a { background: transparent; border: 0; border-top: 1px solid var(--dg-ink); border-radius: 0; padding: 1.4rem 0.25rem 1rem; }
        #dg .dg-keep .grid > a:hover { transform: none; box-shadow: none; border-top-color: var(--dg-tape); }
        #dg .dg-keep .grid > a > span:first-child { display: none; }
        #dg .dg-keep .grid > a h3 { font-family: var(--dg-display); font-weight: 400; font-size: 1.6rem; line-height: 1.15; color: var(--dg-ink); }
        #dg .dg-keep .grid > a p { color: var(--dg-ink-2); }
        #dg .dg-keep .grid > a > span:last-child,
        #dg .dg-keep a.self-start { color: var(--dg-ink); }

        /* 8. Questions */
        .dg-qa { border-top: 1px solid var(--dg-ink); counter-reset: dg-q; }
        .dg-qa details { border-bottom: 1px solid var(--dg-line); counter-increment: dg-q; }
        .dg-qa summary { display: grid; grid-template-columns: 2.6rem minmax(0, 1fr) 1.4rem; gap: 1rem; align-items: baseline; padding: 1.7rem 0.25rem 1.5rem; cursor: pointer; }
        .dg-qa summary::before { content: counter(dg-q); font-family: var(--dg-caps); font-size: 1.5rem; line-height: 1; color: var(--dg-ink-3); }
        .dg-qa h3 { font-family: var(--dg-display); font-weight: 400; font-size: clamp(1.45rem, 2.4vw, 2rem); line-height: 1.18; }
        .dg-qa summary i { position: relative; align-self: center; width: 1.1rem; height: 1.1rem; }
        .dg-qa summary i::before,
        .dg-qa summary i::after { content: ""; position: absolute; left: 0; top: calc(50% - 2px); width: 100%; height: 4px; background: var(--dg-tape); box-shadow: 0 1px 1.5px rgba(15, 15, 16, 0.3); transition: rotate 0.5s cubic-bezier(0.22, 1, 0.36, 1); }
        .dg-qa summary i::after { rotate: 90deg; }
        .dg-qa details[open] summary i::before { rotate: 45deg; }
        .dg-qa details[open] summary i::after { rotate: -45deg; }
        .dg-qa details p { padding: 0 0.25rem 2rem 3.85rem; max-width: 50rem; color: var(--dg-ink-2); }
        @media (max-width: 560px) { .dg-qa details p { padding-inline-start: 0.25rem; } }

        /* Finale: a pool of light on the floor, and the headline crossing it */
        .dg-end { padding-block: clamp(6rem, 13vw, 12rem); isolation: isolate; }
        /* The pool is measured from the headline it lights, so it can never spill on to the line below. */
        .dg-end-head { position: relative; width: fit-content; max-width: 100%; }
        .dg-pool {
            position: absolute;
            z-index: -1;
            inset: 20% -7% -5% auto;
            width: 64%;
            border-radius: 50%;
            background: #ffc21a;
            translate: -8% 0;
        }
        html.es-anim #dg .dg-pool { animation: dg-follow 12s ease-in-out infinite alternate; }
        @keyframes dg-follow { from { translate: -42% -3%; } to { translate: 6% 2%; } }
        /* The type is the tape's own yellow, set to difference: yellow on the floor, ink inside the light. */
        .dg-end .dg-h2 { font-size: clamp(3rem, 9.2vw, 8.6rem); line-height: 0.98; max-width: 14ch; color: #ffc21a; mix-blend-mode: difference; }
        .dg-end .dg-m { color: inherit; }
        .dg-end-sub { margin-top: clamp(2rem, 4vw, 3rem); max-width: 30rem; font-size: 1.2rem; color: #f3efe8; }
        .dg-claim-row { display: grid; gap: 1.5rem 2.5rem; align-items: end; margin-top: clamp(3rem, 6vw, 5rem); }
        @media (min-width: 560px) { .dg-claim-row .dg-btn { justify-self: start; } }
        @media (min-width: 820px) { .dg-claim-row { grid-template-columns: minmax(0, 1fr) auto; max-width: 62rem; } }
        #dg .dg-claim {
            display: flex;
            align-items: baseline;
            min-width: 0;
            padding-block: 1rem 0.6rem;
            border-bottom: 1px solid rgba(243, 239, 232, 0.6);
            font-family: var(--dg-display);
            font-size: clamp(1.5rem, 3.6vw, 2.6rem);
            line-height: 1.1;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        /* The narrowest phones: a size down, so "your-studio" is not cut short. */
        @media (max-width: 350px) {
            #dg .dg-claim { font-size: 1.3rem; }
            .dg-btn { line-height: 1.25; }
        }
        #dg .dg-claim:focus-within { border-color: #ffc21a; box-shadow: 0 2px 0 0 #ffc21a; }
        #dg .dg-claim input {
            flex: 1;
            min-width: 0;
            margin-block: -1rem -0.6rem;
            padding: 1rem 0 0.6rem;
            border: 0;
            background: transparent;
            box-shadow: none;
            outline: none;
            text-align: right;
            font: inherit;
            color: #f3efe8;
        }
        #dg .dg-claim input::placeholder { color: #9d988f; }
        .dg-claim span { flex: none; color: #b9b4aa; user-select: none; }
        .dg-end-note { margin-top: 1.5rem; font-size: 0.95rem; color: #b9b4aa; }

        @media (prefers-reduced-motion: reduce) {
            #dg .dg-l,
            #dg .dg-m,
            #dg .dg-cap i,
            #dg .dg-btn,
            #dg .dg-link,
            #dg .dg-punch,
            #dg .dg-who-name,
            #dg .dg-who-name::after,
            #dg .dg-rep a,
            #dg .dg-rep svg,
            #dg .dg-next svg { transition: none; }
        }
    </style>

    @php
        // The studio's week. One recurring event per class: a day-of-week
        // pattern, a curtain time, and a capacity that applies to each
        // occurrence. "Spots left" is capacity minus signed-up, and every
        // number below is asserted rather than eyeballed.
        $classes = [
            ['Ballet I',              'Tue &amp; Thu',  '6:00pm', 18, 15],
            ['Contemporary Technique','Wed',            '7:00pm', 20, 12],
            ['Hip-Hop Foundations',   'Sat',            '2:00pm', 24, 24],
        ];

        // 24 and 31 Dec 2026 are both Thursdays, so they fall on the
        // Ballet I pattern and are genuine date exceptions for it.
        $closures = ['Thu 24 Dec', 'Thu 31 Dec'];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for dance groups?',
                'a' => 'The parts you use every week are free forever: weekly classes as recurring events, date exceptions for the weeks you are closed, free registration with a capacity per class, sub-schedules, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. A free class takes as many names as it has places for, every month, with nothing counting them. What Pro buys at '.plan_price($proMonthly).' a month is charging for a place at all, and the pass, which is the part a studio actually needs: a 10-visit card, a membership or a season pass. Event Schedule charges zero platform fees on any of it.',
            ],
            [
                'q' => 'How do I set up a weekly class?',
                'a' => 'Create the class once as a recurring event, choose the days of the week it runs and the time, then add date exceptions for the weeks the studio is closed. Set a capacity and the class shows how many spots are left, counted separately for each date rather than across the whole term.',
            ],
            [
                'q' => 'Can I sell a 10-class card instead of single classes?',
                'a' => 'Yes, on the Pro plan. A visit pass covers a set number of visits across the classes you attach it to, so a 10-visit card is one purchase that the dancer uses ten times. A membership gives unlimited visits until it expires, and a season pass covers every occurrence of one recurring event. Usage is tracked per visit, and each pass can carry its own cancellation deadline and late-cancel policy.',
            ],
            [
                'q' => 'Can I keep rehearsal calls off the public page?',
                'a' => 'Yes. Saving an event as a Draft keeps it members-only, so a rehearsal call sits on the same schedule as the class it belongs to without appearing on your public page. The Enterprise plan adds two more states: Internal, which is never public, and Unlisted, which is hidden from the schedule but reachable by direct link with an optional password.',
            ],
            [
                'q' => 'Can my choreographers and company manager edit the schedule?',
                'a' => 'A schedule includes one team member on the free plan. The Enterprise plan raises that to multiple team members and adds availability tracking, so you can record who is available before you set a rehearsal call.',
            ],
            [
                'q' => 'Can parents be told when recital tickets go on sale?',
                'a' => 'Yes, free on every plan. Switch on the "Notify me" card, put the show on the schedule before tickets are ready, and its page offers "Tell me when tickets go on sale". A parent leaves an email address, with no account, and hears when tickets go on sale, if the show is cancelled, and again shortly before it starts, plus any change notice you choose to send. The event\'s Tickets panel shows how many people are waiting, and it is not a subscription to your schedule.',
            ],
            [
                'q' => 'Can dancers add the class timetable to their own calendar?',
                'a' => 'Yes. Your schedule page offers a live calendar feed that Google Calendar, Apple Calendar, Outlook or any other calendar app can subscribe to. It carries the next 90 days of classes and the app re-reads it, so a moved class or a closure turns up without anyone downloading a new file. It needs no account and no email address.',
            ],
        ];

        $dotSections = [
            ['top', 'The wall'],
            ['week', 'The week'],
            ['rehearsal', 'The rehearsal'],
            ['card', 'The card'],
            ['show', 'The show'],
            ['public', 'In public'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];

        // Display type, one letter to a span so each can move on its own. A part given as an
        // array is a marked phrase (the strip of tape); "\n" is a break on wide screens. The words
        // reach assistive tech once, through the heading's aria-label, and the letters are
        // aria-hidden; a crawler still reads the same words in the same order. Words are glued so a
        // line can never start with a full stop.
        $dgSet = function (array $parts, int $from = 0): array {
            $label = '';
            $chars = [];
            foreach ($parts as $part) {
                [$text, $marked] = is_array($part) ? [$part[0], true] : [$part, false];
                if ($text === "\n") {
                    $chars[] = ["\n", false];
                    $label = rtrim($label).' ';

                    continue;
                }
                $label .= $text;
                foreach (mb_str_split($text) as $ch) {
                    $chars[] = [$ch, $marked];
                }
            }

            $html = '';
            $i = $from;
            $inWord = false;
            $inMark = false;
            foreach ($chars as $k => [$ch, $marked]) {
                if ($ch === ' ' || $ch === "\n") {
                    if ($inMark) {
                        $html .= '</span>';
                        $inMark = false;
                    }
                    if ($inWord) {
                        $html .= '</span>';
                        $inWord = false;
                    }
                    if ($ch === "\n") {
                        $html .= ' <br class="dg-br">';
                    } else {
                        $html .= ($marked && ($chars[$k + 1][1] ?? false)) ? '<span class="dg-m"> </span>' : ' ';
                    }

                    continue;
                }
                if (! $inWord) {
                    $html .= '<span class="dg-w">';
                    $inWord = true;
                }
                if ($marked && ! $inMark) {
                    $html .= '<span class="dg-m">';
                    $inMark = true;
                } elseif (! $marked && $inMark) {
                    $html .= '</span>';
                    $inMark = false;
                }
                $html .= '<span class="dg-l" style="--i:'.$i.'">'.e($ch).'</span>';
                $i++;
            }
            if ($inMark) {
                $html .= '</span>';
            }
            if ($inWord) {
                $html .= '</span>';
            }

            return ['label' => trim($label), 'html' => $html];
        };

        // Where a count sits in the eight: eight ticks, this one taped.
        $dgBar = function (int $n): string {
            $out = '<span class="dg-bar" aria-hidden="true">';
            for ($b = 1; $b <= 8; $b++) {
                $out .= '<i'.($b === $n ? ' class="is-on"' : ($b < $n ? ' class="is-past"' : '')).'></i>';
            }

            return $out.'</span>';
        };

        $dgArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H4" /></svg>';
        $dgDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $dgEchoes = [0.06, 0.13, 0.28, 1, 0.28, 0.13, 0.06];
        $dgAmp = fn (string $html): string => str_replace('&amp;', '<span class="dg-amp">&amp;</span>', $html);
    @endphp

    <div id="dg">

        <!-- The count rail (wide screens): top, the eight counts, and the bow -->
        <span class="dg-rail-beat" aria-hidden="true"></span>
        <nav class="dg-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as $railIndex => [$sectionId, $sectionLabel])
                    <li>
                        <a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}">
                            <b aria-hidden="true">{{ $railIndex === 0 ? '·' : ($railIndex === 9 ? '&' : $railIndex) }}</b>
                            <span class="dg-label" aria-hidden="true">{{ $sectionLabel }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- Hero: five, six, seven, eight                                -->
        <!-- ============================================================ -->
        <section id="top" class="dg-hero">
            <div class="dg-wrap dg-hero-in">
                <div class="dg-countin" aria-hidden="true">
                    <b style="--b: 0;">5</b><b style="--b: 1;">6</b><b style="--b: 2;">7</b><b style="--b: 3;">8</b>
                    <span class="dg-metro"><i></i></span>
                </div>

                <h1 class="dg-h1" aria-label="Dance schedules for studios and crews. Three things happen at the same wall.">
                    <x-marketing.hero-eyebrow class="dg-eyebrow es-fade-up es-d-1">Dance schedules for studios and crews</x-marketing.hero-eyebrow>
                    <span class="dg-h1-rows" aria-hidden="true">
                        <span class="dg-row"><span class="dg-step" style="--s: 0%;">{!! $dgSet(['Three'], 0)['html'] !!}</span> <span class="dg-step" style="--s: 17%;">{!! $dgSet(['things'], 5)['html'] !!}</span> <span class="dg-step" style="--s: 36%;">{!! $dgSet(['happen'], 11)['html'] !!}</span></span>
                        <span class="dg-row dg-stand"><span class="dg-step" style="--s: 5%;">{!! $dgSet(['at ', ['the']], 17)['html'] !!}</span> <span class="dg-step" style="--s: 27%;">{!! $dgSet([['same']], 22)['html'] !!}</span> <span class="dg-step" style="--s: 50%;">{!! $dgSet([['wall'], '.'], 26)['html'] !!}</span></span>
                        <span class="dg-floor"></span>
                    </span>
                </h1>

                <div class="dg-hero-foot">
                    <p class="dg-hero-lede es-fade-up es-d-3">
                        The class, the rehearsal, the show. Three different audiences, one schedule -
                        and only the parts you choose are public.
                    </p>
                    <div class="dg-hero-cta es-fade-up es-d-4">
                        <a href="#week" class="dg-link">
                            See how the week works
                            {!! $dgDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="dg-btn">
                            Create your schedule
                            {!! $dgArrow !!}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- The three strands: a word, and the echoes it leaves as it crosses the floor -->
        <section class="dg-strands">
            @foreach ([
                ['Class', 'Ballet I', 'Tue &amp; Thu &middot; 6:00pm', '3 spots left', false],
                ['Rehearsal', 'Spring Gala, act two', 'Sat &middot; 10:00am', 'Draft &middot; members only', true],
                ['Show', 'Spring Gala', 'Sat 30 May &middot; 7:30pm', 'Tickets from $12', false],
            ] as $strandIndex => [$strand, $name, $when, $note, $isDraft])
                <div class="dg-strand" style="--dir: {{ $strandIndex % 2 === 0 ? -1 : 1 }}; --x0: {{ ['-9vw', '7vw', '-3vw'][$strandIndex] }};">
                    <div class="dg-echo" aria-hidden="true">
                        <div class="dg-echo-row">@foreach ($dgEchoes as $echo)<span @class(['dg-on' => $echo == 1]) style="--o: {{ $echo }};">{{ strtolower($strand) }}</span>@endforeach</div>
                    </div>
                    <div class="dg-wrap dg-strand-cap" data-reveal>
                        <span class="dg-label">{{ $strand }}</span>
                        <strong>{!! $name !!}</strong>
                        <span class="dg-strand-when">{!! $when !!}</span>
                        <span class="dg-strand-note @if ($isDraft) is-draft @endif">{!! $note !!}</span>
                    </div>
                </div>
            @endforeach

            <div class="dg-wrap dg-strands-foot">
                <p data-reveal>
                    One schedule. The class takes sign-ups, the rehearsal never reaches the public page,
                    and the show sells tickets.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 1. The week                                                  -->
        <!-- ============================================================ -->
        <section id="week" class="dg-sec" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['A weekly class is ', ['one event'], ', not forty.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">1</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>The week</p>{!! $dgBar(1) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                        <p class="dg-lede" data-reveal>
                            Set the class up once as a recurring event: the days it runs, the time it starts, and
                            date exceptions for the weeks the studio is closed. Change the time in September and
                            every Tuesday after it follows.
                        </p>
                    </div>
                </header>

                <div class="dg-two">
                    <div>
                        <ul class="dg-marks" data-reveal-group="90">
                            @foreach ([
                                ['Day-of-week patterns', 'Tuesday and Thursday, or just Saturdays. The pattern is the event.'],
                                ['Date exceptions', 'Take individual dates out for a closure, or add a one-off extra date in.'],
                                ['Capacity per class', 'A limit applies to each date on its own, so a full Tuesday does not close the Thursday.'],
                            ] as [$t, $d])
                                <li data-reveal>
                                    <i class="dg-x" aria-hidden="true"></i>
                                    <span><strong>{{ $t }}</strong> <span>- {{ $d }}</span></span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="dg-note" data-reveal>
                            <span class="dg-tier">Free</span>
                            <span>Recurring events, date exceptions and registration are all on the free plan.</span>
                        </p>
                    </div>

                    <div class="dg-tt dg-spike" data-reveal="panel">
                        <div class="dg-tt-head">
                            <h3 class="dg-h3">This week</h3>
                            <span class="dg-label">3 recurring events</span>
                        </div>

                        @foreach ($classes as [$cName, $cDays, $cTime, $cCap, $cTaken])
                            @php $left = $cCap - $cTaken; @endphp
                            <div class="dg-tt-row">
                                <div>
                                    <p class="dg-h3">{{ $cName }}</p>
                                    <p class="dg-tt-when">{!! $cDays !!} &middot; {{ $cTime }}</p>
                                </div>
                                <div class="dg-tt-left">
                                    @if ($left > 0)
                                        <p><b>{{ $left }}</b> spots left</p>
                                    @else
                                        <p class="is-full">Full</p>
                                    @endif
                                    <p>{{ $cTaken }} of {{ $cCap }}</p>
                                </div>
                                <div class="dg-cap" aria-hidden="true">@for ($place = 0; $place < $cCap; $place++)<i @class(['is-free' => $place >= $cTaken]) style="--k: {{ $place }};"></i>@endfor</div>
                            </div>
                        @endforeach

                        <div class="dg-closed">
                            <p class="dg-label">Date exceptions</p>
                            <ul>
                                @foreach ($closures as $closed)
                                    <li><i class="dg-x" aria-hidden="true"></i>{{ $closed }} &middot; closed</li>
                                @endforeach
                            </ul>
                            <p>
                                Both fall on a Thursday, so Ballet I skips them and the Tuesday runs as normal.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The rehearsal: the wings and the house                    -->
        <!-- ============================================================ -->
        <section id="rehearsal" class="dg-sec" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['The wall the company sees.', "\n", 'The wall ', ['the audience sees'], '.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">2</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>The rehearsal</p>{!! $dgBar(2) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                        <p class="dg-lede" data-reveal>
                            A rehearsal call belongs on the same schedule as the show it is for. It just does not
                            belong on your public page.
                        </p>
                    </div>
                </header>

                <div class="dg-duplex" data-reveal="panel">
                    <i class="dg-t" aria-hidden="true"></i>

                    <!-- Company side -->
                    <div class="dg-side dg-wings">
                        <span class="dg-side-kick" aria-hidden="true">The wings</span>
                        <div class="dg-side-head">
                            <p class="dg-label">Signed in</p>
                            <span class="dg-tier">Free</span>
                        </div>
                        <div>
                            @foreach ([
                                ['Ballet I', 'Tue & Thu 6:00pm', 'Public'],
                                ['Spring Gala, act two', 'Sat 10:00am', 'Draft'],
                                ['Spacing call, main stage', 'Fri 4:00pm', 'Draft'],
                                ['Spring Gala', 'Sat 30 May 7:30pm', 'Public'],
                            ] as [$eName, $eWhen, $eState])
                                <div class="dg-ev @if ($eState === 'Draft') is-draft @endif">
                                    <div>
                                        <p class="dg-ev-name">{!! $eName !!}</p>
                                        <p class="dg-ev-when">{{ $eWhen }}</p>
                                    </div>
                                    <span class="dg-state @if ($eState === 'Draft') dg-state-draft @else dg-state-public @endif">{{ $eState }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="dg-side-foot">Everything the company needs, in one place.</p>
                    </div>

                    <!-- Audience side -->
                    <div class="dg-side dg-house">
                        <span class="dg-side-kick" aria-hidden="true">Front of house</span>
                        <div class="dg-side-head">
                            <p class="dg-label">Your public page</p>
                            <span class="dg-side-url">yourstudio.eventschedule.com</span>
                        </div>
                        <div class="dg-house-list">
                            @foreach ([
                                ['Ballet I', 'Tue & Thu 6:00pm'],
                                ['Spring Gala', 'Sat 30 May 7:30pm'],
                            ] as [$pName, $pWhen])
                                <div class="dg-ev">
                                    <div>
                                        <p class="dg-ev-name">{!! $pName !!}</p>
                                        <p class="dg-ev-when">{{ $pWhen }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="dg-side-foot">The two rehearsal calls are simply not here.</p>
                    </div>
                </div>

                <div class="dg-after" data-reveal-group="100">
                    <div data-reveal>
                        <div class="dg-after-head">
                            <h3 class="dg-h3">Two more states</h3>
                            <span class="dg-tier dg-tier-ent">Enterprise</span>
                        </div>
                        <p>
                            Internal events are never public at all, and Unlisted events are hidden from the
                            schedule but still reachable by direct link, with an optional password - useful for a
                            preview you want the board to see and nobody else.
                        </p>
                    </div>
                    <div data-reveal>
                        <div class="dg-after-head">
                            <h3 class="dg-h3">Who can edit</h3>
                            <span class="dg-tier dg-tier-ent">Enterprise</span>
                        </div>
                        <p>
                            A schedule includes one team member on the free plan. Enterprise raises that to
                            multiple team members and adds availability tracking, so you can see who is free
                            before you call the rehearsal.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The card                                                  -->
        <!-- ============================================================ -->
        <section id="card" class="dg-sec" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['Nobody buys ', ['one ballet class'], '.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">3</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>The card</p>{!! $dgBar(3) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                        <p class="dg-lede" data-reveal>
                            They buy ten of them, or a month of them. A pass is one purchase that covers many
                            visits, so the dancer books in without paying again and you are not reconciling a
                            punch card at the desk.
                        </p>
                    </div>
                </header>

                <div class="dg-two">
                    <div>
                        <div class="dg-punch-wrap" aria-hidden="true" data-reveal="zoom">
                            <div class="dg-punch">
                                <div class="dg-punch-top"><span>Class card</span><span>Ten counts</span></div>
                                <div class="dg-punch-holes">@for ($hole = 1; $hole <= 10; $hole++)<i @class(['is-out' => $hole <= 4])>{{ $hole }}</i>@endfor</div>
                                <div class="dg-punch-foot"><span>Punch one each visit</span><span>No. 0412</span></div>
                            </div>
                        </div>

                        <div class="dg-buy" data-reveal>
                            <div class="dg-buy-head">
                                <h3 class="dg-h3">What people buy</h3>
                                <span class="dg-tier dg-tier-pro">Pro</span>
                            </div>

                            @foreach ([
                                ['Ten-class card', 'Visit pass', '10 visits, used one at a time', '$180'],
                                ['Unlimited month', 'Membership', 'Every class until it expires', '$140'],
                                ['Spring Gala season pass', 'Season pass', 'Every performance of the run, once each', '$95'],
                                ['Drop-in', 'Single ticket', 'One class', '$22'],
                            ] as [$pName, $pKind, $pScope, $pPrice])
                                <div class="dg-buy-row">
                                    <p class="dg-buy-name">{{ $pName }}</p>
                                    <p class="dg-buy-price">{{ $pPrice }}</p>
                                    <p class="dg-buy-kind"><b>{{ $pKind }}</b> <span>{{ $pScope }}</span></p>
                                </div>
                            @endforeach

                            <p class="dg-buy-foot">
                                Usage is tracked per visit, so you can see how much of a card is left without
                                anyone keeping a paper tally.
                            </p>
                        </div>
                    </div>

                    <ul class="dg-facts" data-reveal-group="90">
                        @foreach ([
                            ['A set number of visits', 'A visit pass covers a fixed count across the classes you attach it to - a ten-visit card is one purchase used ten times.'],
                            ['Unlimited until it expires', 'A membership covers every covered class until the expiry date, with no per-visit counting at all.'],
                            ['Every date of one class', 'A season pass covers each occurrence of a single recurring event, once per occurrence.'],
                            ['Cancellation, decided in advance', 'Each pass can carry its own cancellation deadline and a late-cancel policy - forfeit the visit, or block the cancellation.'],
                        ] as [$t, $d])
                            <li data-reveal>
                                <i class="dg-x" aria-hidden="true"></i>
                                <div>
                                    <h3>{{ $t }}</h3>
                                    <p>{{ $d }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The show                                                  -->
        <!-- ============================================================ -->
        <section id="show" class="dg-sec" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['The night the whole year ', ['points at'], '.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">4</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>The show</p>{!! $dgBar(4) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                        <p class="dg-lede" data-reveal>
                            Named ticket types, each with its own price, quantity and sales window - so the family
                            rate closes when you want it to and the door price does not open early. Announce the
                            show before tickets are ready, switch on the "Notify me" card, and parents can ask to hear when they go on sale.
                        </p>
                    </div>
                </header>

                <div class="dg-two">
                    <div class="dg-bill dg-spike" data-reveal="panel">
                        <div class="dg-bill-head">
                            <h3 class="dg-d">Spring Gala</h3>
                            <span class="dg-label">Sat 30 May &middot; 7:30pm</span>
                        </div>
                        <p class="dg-bill-sub">Four ticket types on one event.</p>

                        @foreach ([
                            ['Adult', 'On sale now', '$28', '120'],
                            ['Student &amp; senior', 'On sale now', '$18', '80'],
                            ['Under 12', 'On sale now', '$12', '60'],
                            ['Family of four', 'Closes 7 days before', '$72', '40'],
                        ] as [$tName, $tWindow, $tPrice, $tQty])
                            <div class="dg-bill-row">
                                <span class="dg-bill-name">{!! $dgAmp($tName) !!}</span>
                                <span class="dg-bill-win">{{ $tWindow }}</span>
                                <span class="dg-bill-qty">{{ $tQty }}</span>
                                <span class="dg-bill-price">{{ $tPrice }}</span>
                            </div>
                        @endforeach

                        <p class="dg-bill-foot">
                            Payment goes through your own Stripe or PayPal account, Invoice Ninja, Payfast (in rand), a payment link or cash at the door. Event Schedule takes no cut of it.
                        </p>
                    </div>

                    <ul class="dg-notes" data-reveal-group="100">
                        @foreach ([
                            ['Zero platform fees', 'You keep the whole ticket price minus what your payment provider charges to process it. There is no per-ticket cut on top, on any plan.', 'free'],
                            ['Live check-in view', 'Scanning tickets from a phone is free on every plan. Pro adds the running count and the per-ticket breakdown, so two people can work the queue and both see the same total.', 'pro'],
                            ['Questions at checkout', 'Ask for the dancer\'s name, the class they are in, or a photo consent - collected with the sale instead of chased afterwards.', 'pro'],
                        ] as [$t, $d, $tier])
                            <li data-reveal>
                                <i class="dg-t" aria-hidden="true"></i>
                                <div>
                                    <div class="dg-notes-head">
                                        <h3 class="dg-h3">{{ $t }}</h3>
                                        <span class="dg-tier {{ $tier === 'pro' ? 'dg-tier-pro' : '' }}">{{ $tier === 'pro' ? 'Pro' : 'Free' }}</span>
                                    </div>
                                    <p>{{ $d }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. In public                                                 -->
        <!-- ============================================================ -->
        <section id="public" class="dg-sec" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['One link, on ', ['everything you print'], '.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">5</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>In public</p>{!! $dgBar(5) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                        <p class="dg-lede" data-reveal>
                            The studio window, the programme, the bio on your profile. It is the same address all
                            year and it is never out of date.
                        </p>
                    </div>
                </header>
            </div>

            <div class="dg-url" style="--dir: 1; --x0: 4vw;">
                <div class="dg-echo" aria-hidden="true">
                    <div class="dg-echo-row">@foreach ([0.08, 0.2, 1, 0.2, 0.08] as $echo)<span @class(['dg-on' => $echo == 1]) style="--o: {{ $echo }};">yourstudio.eventschedule.com</span>@endforeach</div>
                </div>
            </div>

            <div class="dg-wrap">
                <div class="dg-six" data-reveal-group="80">
                    <article data-reveal>
                        <div class="dg-six-head">
                            <h3 class="dg-h3">Embed it on the site you already have</h3>
                            <span class="dg-tier">Free</span>
                        </div>
                        <p>
                            Drop the calendar into your existing website in an iframe. It keeps itself current,
                            so the term timetable on your homepage stops being a screenshot somebody has to
                            remember to replace. Dancers can also subscribe to it in their own calendar app,
                            as a live feed that picks up a moved class or a closure by itself.
                        </p>
                    </article>

                    <article data-reveal>
                        <div class="dg-six-head">
                            <h3 class="dg-h3">Followers</h3>
                            <span class="dg-tier">Free</span>
                        </div>
                        <p>
                            People follow your schedule and hear about a new date from you, in their inbox,
                            rather than from a feed that decides who sees it.
                        </p>
                    </article>

                    <article data-reveal>
                        <div class="dg-six-head">
                            <h3 class="dg-h3">Newsletters</h3>
                            <span class="dg-tier">Free</span>
                        </div>
                        <p>
                            Write and send from the same place, with open and click rates afterwards. Ten emails a
                            month on the free plan, a hundred on Pro and a thousand on Enterprise, counted per recipient rather than per send.
                        </p>
                    </article>

                    <article data-reveal>
                        <div class="dg-six-head">
                            <h3 class="dg-h3">Calendar sync</h3>
                            <span class="dg-tier">Free</span>
                        </div>
                        <p>
                            Two-way sync with Google, Outlook and CalDAV, so a rehearsal moved on your phone moves
                            on the schedule too.
                        </p>
                    </article>

                    <article data-reveal>
                        <div class="dg-six-head">
                            <h3 class="dg-h3">Share graphics</h3>
                            <span class="dg-tier">Free</span>
                        </div>
                        <p>
                            Every event can generate a post-sized and a story-sized image with your own branding
                            on it, ready to download.
                        </p>
                    </article>

                    <article data-reveal>
                        <div class="dg-six-head">
                            <h3 class="dg-h3">Online classes</h3>
                            <span class="dg-tier">Free</span>
                        </div>
                        <p>
                            Mark an event as online and add the link people join on - any platform that gives you
                            a URL. Ticket holders get it with their ticket.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Who it is for                                             -->
        <!-- ============================================================ -->
        <section id="who" class="dg-sec" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['Every kind of room with ', ['a mirror in it'], '.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">6</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>Who it is for</p>{!! $dgBar(6) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                    </div>
                </header>

                @php
                    $dgCompany = [
                        ['Ballet Companies', 'A repertory season, a Nutcracker run and a studio showcase, each set up once and sold from the same link.', 'for-ballet-companies'],
                        ['Hip-Hop Crews', 'Battles, showcases and cyphers. Post the date, take sign-ups with a capacity, sell at the door with QR check-in.', 'for-hip-hop-crews'],
                        ['Ballroom & Latin Studios', 'Weekly technique, a social every month and a showcase in the spring - three sub-schedules, one public page.', 'for-ballroom-latin-studios'],
                        ['Contemporary & Modern', 'Residencies, site-specific work and shared bills. Keep the making private and publish only the dates that are ready.', 'for-contemporary-modern-dance'],
                        ['Folk & Cultural Ensembles', 'Festival appearances, heritage nights and community performances, in a calendar people can subscribe to.', 'for-folk-cultural-dance'],
                        ['Dance Schools & Academies', 'A full timetable of graded classes with a capacity on each, and a recital at the end of it that sells its own tickets.', 'for-dance-schools-academies'],
                    ];
                @endphp
                <div class="dg-who" data-reveal-group="70">
                    @foreach ($dgCompany as $companyIndex => [$companyName, $companyDesc, $companySlug])
                        @php $companyPost = get_sub_audience_blog($companySlug); @endphp
                        <article class="dg-who-row" data-reveal>
                            <span class="dg-who-no" aria-hidden="true">{{ ['i', 'ii', 'iii', 'iv', 'v', 'vi'][$companyIndex] }}</span>
                            <h3 class="dg-who-name">{!! $dgAmp(e($companyName)) !!}</h3>
                            <div>
                                <p>{{ $companyDesc }}</p>
                                @if ($companyPost)
                                    <a href="{{ blog_url('/' . $companyPost->slug) }}" class="dg-link" aria-label="Learn more about Event Schedule for {{ $companyName }}">
                                        Learn more
                                        {!! $dgArrow !!}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. How it works: the dark floor                              -->
        <!-- ============================================================ -->
        <section id="how" class="dg-sec dg-band" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['A term takes ', ['an afternoon'], ' to set up.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">7</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>How it works</p>{!! $dgBar(7) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                    </div>
                </header>

                <div class="dg-steps" data-reveal-group="110">
                    <span class="dg-sweep" aria-hidden="true"></span>
                    @foreach ([
                        ['01', 'Put the timetable up', 'One recurring event per class, with the days it runs and the weeks you are closed. Sub-schedules keep classes, rehearsals and shows on their own strands.'],
                        ['02', 'Decide what is public', 'Classes and shows go out. Rehearsal calls stay as Drafts, on the same schedule, members-only.'],
                        ['03', 'Sell the card, not the class', 'A ten-visit card, an unlimited month, or a ticket to the gala - all from the one link you already share.'],
                    ] as [$n, $t, $d])
                        <div data-reveal>
                            <p class="dg-step-n">{{ $n }}</p>
                            <h3>{{ $t }}</h3>
                            <p>{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- Between counts: key features                                 -->
        <!-- ============================================================ -->
        <section class="dg-sec">
            <div class="dg-wrap">
                <div class="dg-aside-head">
                    <h2 data-reveal>Key features</h2>
                    <a href="{{ marketing_url('/features') }}" class="dg-link" data-reveal>
                        See all features
                        {!! $dgArrow !!}
                    </a>
                </div>

                @php
                    $dgRepertoire = [
                        ['Recurring Events', 'A weekly class set up once, with date exceptions for the weeks you are closed', marketing_url('/features/recurring-events')],
                        ['Ticketing', 'Class cards, memberships and show tickets with zero platform fees', marketing_url('/features/ticketing')],
                        ['Passes', 'Ten-class cards, unlimited memberships and season passes, on Pro', marketing_url('/features/passes')],
                        ['Sub-schedules', 'Keep classes, rehearsals and performances on their own strands', marketing_url('/features/sub-schedules')],
                        ['Newsletters', 'Email the people who follow your studio, with open and click rates', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <div class="dg-rep" data-reveal-group="70">
                    @foreach ($dgRepertoire as [$repName, $repDesc, $repUrl])
                        <a href="{{ $repUrl }}" data-reveal>
                            <strong>{{ $repName }}</strong>
                            <span>{{ $repDesc }}</span>
                            {!! $dgArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="dg-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- Between counts: the neighbours                               -->
        <!-- ============================================================ -->
        <section class="dg-sec">
            <div class="dg-wrap">
                <div class="dg-aside-head">
                    <h2 data-reveal>Related pages</h2>
                    <a href="{{ marketing_url('/use-cases') }}" class="dg-link" data-reveal>
                        See all use cases
                        {!! $dgArrow !!}
                    </a>
                </div>

                <div class="dg-next" data-reveal-group="70">
                    @foreach ([
                        ['/for-theaters', 'Theaters'],
                        ['/for-theater-performers', 'Theater Performers'],
                        ['/for-fitness-and-yoga', 'Fitness &amp; Yoga'],
                        ['/for-workshop-instructors', 'Workshop Instructors'],
                    ] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal>
                            <small>Event Schedule for</small>
                            <strong><span>{!! $dgAmp($relName) !!}</span>{!! $dgArrow !!}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Questions                                                 -->
        <!-- ============================================================ -->
        <section id="faq" class="dg-sec" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['Asked in ', ['the studio office'], '.']); @endphp
                <header class="dg-head">
                    <div class="dg-count" aria-hidden="true" data-reveal="turn"><span class="dg-count-n">8</span></div>
                    <div>
                        <div class="dg-meta"><p class="dg-label" data-reveal>Questions</p>{!! $dgBar(8) !!}</div>
                        <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                    </div>
                </header>

                <div class="dg-qa" data-reveal>
                    @foreach ($faqs as $faq)
                        <details name="faq">
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
        <!-- Finale: from the top                                         -->
        <!-- ============================================================ -->
        <section id="claim" class="dg-sec dg-band dg-end" style="scroll-margin-top: 4rem;">
            <div class="dg-wrap">
                @php $dgHead = $dgSet(['Put the timetable ', ['where people look'], '.']); @endphp
                <div class="dg-meta"><p class="dg-label" data-reveal>Free forever</p><span class="dg-label" aria-hidden="true">&middot; From the top</span></div>
                <div class="dg-end-head">
                    <div class="dg-pool" aria-hidden="true"></div>
                    <h2 class="dg-h2" data-reveal="canon" aria-label="{{ $dgHead['label'] }}"><span aria-hidden="true">{!! $dgHead['html'] !!}</span></h2>
                </div>
                <p class="dg-end-sub" data-reveal>
                    Classes, rehearsal calls and the gala, on one schedule with one address.
                </p>

                <div class="dg-claim-row" data-reveal>
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div dir="ltr" class="es-claim dg-claim">
                        <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30">
                        <span>.eventschedule.com</span>
                    </div>
                    <a href="{{ app_url('/sign_up?type=talent') }}" class="dg-btn">
                        Create your schedule
                        {!! $dgArrow !!}
                    </a>
                </div>

                <p class="dg-end-note">No credit card required</p>
            </div>
        </section>

        <div class="dg-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
