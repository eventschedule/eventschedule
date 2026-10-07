<x-marketing-layout>
    <x-slot name="title">Comedy Club Schedules | Sell the Night, Add the Lineup Later</x-slot>
    <x-slot name="description">Friday at eight sells before anyone knows who is on. Set the night up once, sell tickets with zero platform fees, and add the comics when they are booked.</x-slot>
    <x-slot name="breadcrumbTitle">For Comedy Clubs</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Fugaz One') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Yellowtail') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Questrial') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Comedy Clubs"
        description="Run a room on recurring nights, sell advance and door tickets from one link, and add the participants later so the date appears on each comic's own schedule."
        audience="Comedy Clubs"
        keywords="comedy club schedule, comedy night ticketing, open mic capacity, comedy booking requests, recurring comedy night, comedy club calendar" />
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
           For-comedy-clubs "The Late Show" styles.

           A comedy club sells the room: Friday at eight is sold before
           anyone knows who is on. So the page is the room's own late
           show, set the way 1960 set things: a walnut television with
           the headline as its title card, the week as a listings page,
           table tents, message slips, a supper-club bill, matchbooks,
           cue cards, a channel selector and an APPLAUSE sign.

           Shapes are the design and all of them are CSS: stars cut by
           clip-path, boomerangs from two-sided borders, kidney blobs,
           harlequin diamonds, conic rays. Type is Fugaz One with the
           accent phrase of each heading in Yellowtail, and Questrial
           for reading. Everything is scoped under #cc.
           ============================================================== */

        @property --cc-turn {
            syntax: '<angle>';
            inherits: false;
            initial-value: 0deg;
        }

        #cc {
            --cc-ground: #f6efe0;
            --cc-ground-2: #efe4cc;
            --cc-card: #fffaf0;
            --cc-ink: #2b2a28;
            --cc-ink-2: #4b4741;
            --cc-ink-3: #686259;
            --cc-line: rgba(43, 42, 40, 0.2);
            --cc-teal: #1d7874;
            --cc-teal-ink: #145a57;
            --cc-orange: #ee6c2a;
            --cc-orange-hd: #d2531a;
            --cc-orange-ink: #a8420e;
            --cc-mustard: #f4b942;
            --cc-btn-edge: #a8420e;
            --cc-shadow: rgba(43, 42, 40, 0.22);
            --cc-display: 'Fugaz One', 'Arial Black', Impact, sans-serif;
            --cc-script: 'Yellowtail', 'Brush Script MT', 'Segoe Script', cursive;
            --cc-text: 'Questrial', 'Century Gothic', 'Avenir Next', Avenir, system-ui, sans-serif;
            --cc-crt: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' preserveAspectRatio='none'%3E%3Cpath d='M50 0C9 0 0 9 0 50s9 50 50 50 50-9 50-50S91 0 50 0z'/%3E%3C/svg%3E");
            /* What a block of copy is inked in. A coloured field re-sets these for itself. */
            --f-ink: var(--cc-ink);
            --f-ink-2: var(--cc-ink-2);
            --f-accent: var(--cc-orange-hd);
            --f-link: var(--cc-teal-ink);
            --k-bg: var(--cc-mustard);
            --k-fg: #2b2a28;
            --tag-bg: #145a57;
            --tag-fg: #f6efe0;
            position: relative;
            background: var(--cc-ground);
            color: var(--cc-ink);
            font-family: var(--cc-text);
            font-size: 1.0625rem;
            line-height: 1.6;
            font-synthesis: none;
        }
        .dark #cc {
            --cc-ground: #262523;
            --cc-ground-2: #1e1d1b;
            --cc-card: #34322f;
            --cc-ink: #f6efe0;
            --cc-ink-2: #d9d0be;
            --cc-ink-3: #b3a996;
            --cc-line: rgba(246, 239, 224, 0.2);
            --cc-teal: #2ba59f;
            --cc-teal-ink: #6fd3cc;
            --cc-orange: #f47a3a;
            --cc-orange-hd: #f9894c;
            --cc-orange-ink: #ffa372;
            --cc-btn-edge: #9c3d0d;
            --cc-shadow: rgba(0, 0, 0, 0.5);
            --tag-bg: #6fd3cc;
            --tag-fg: #1b1a18;
        }

        /* The bar above takes the room's cream, and its charcoal after dark. */
        body > header.sticky {
            background-color: rgba(246, 239, 224, 0.88);
            border-bottom-color: rgba(43, 42, 40, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(38, 37, 35, 0.88);
            border-bottom-color: rgba(246, 239, 224, 0.14);
        }

        #cc ::selection { background: #f4b942; color: #2b2a28; }
        #cc a:focus-visible,
        #cc summary:focus-visible,
        #cc input:focus-visible {
            outline: 3px solid var(--f-link);
            outline-offset: 3px;
        }
        #cc section { scroll-margin-top: 4.5rem; }

        .cc-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
        .cc-sec { position: relative; padding-block: clamp(4.5rem, 9vw, 7.5rem); overflow: clip; }
        .cc-alt { background: var(--cc-ground-2); }

        /* Coloured fields. Each one names its own inks, so the copy inside never guesses. */
        .cc-mustard {
            background: #f4b942;
            --f-ink: #2b2a28;
            --f-ink-2: #3f3828;
            --f-accent: #145a57;
            --f-link: #145a57;
            --k-bg: #2b2a28;
            --k-fg: #f4b942;
            --tag-bg: #145a57;
            --tag-fg: #f6efe0;
        }
        .dark #cc .cc-mustard {
            background: #2f2a1e;
            --f-ink: #f6efe0;
            --f-ink-2: #d9d0be;
            --f-accent: #f4b942;
            --f-link: #6fd3cc;
            --k-bg: #f4b942;
            --k-fg: #2b2a28;
            --tag-bg: #6fd3cc;
            --tag-fg: #1b1a18;
        }
        .cc-teal {
            background: #186b67;
            --f-ink: #f6efe0;
            --f-ink-2: #d9ebe5;
            --f-accent: #f4b942;
            --f-link: #f6efe0;
            --k-bg: #f4b942;
            --k-fg: #2b2a28;
            --tag-bg: #f6efe0;
            --tag-fg: #145a57;
        }
        .dark #cc .cc-teal { background: #123f3d; }
        .cc-studio {
            background-color: #1c1b19;
            background-image: radial-gradient(60rem 26rem at 50% -6rem, rgba(244, 185, 66, 0.2), rgba(244, 185, 66, 0) 70%);
            --f-ink: #f6efe0;
            --f-ink-2: #d9d0be;
            --f-accent: #f4b942;
            --f-link: #f6efe0;
            --k-bg: #f4b942;
            --k-fg: #2b2a28;
        }

        /* ---------------------------------------------------------------
           Type: the heavy slanted face, and the script that answers it
           --------------------------------------------------------------- */
        .cc-d { font-family: var(--cc-display); font-weight: 400; line-height: 1.04; letter-spacing: 0; }
        .cc-h2 {
            font-family: var(--cc-display);
            font-weight: 400;
            font-size: clamp(2.05rem, 4.5vw, 3.6rem);
            line-height: 1.06;
            color: var(--f-ink);
            text-wrap: balance;
        }
        .cc-sc {
            font-family: var(--cc-script);
            font-size: 1.3em;
            line-height: 0.8;
            color: var(--f-accent);
            padding-inline: 0.04em;
        }
        .cc-lead { margin-top: 1.25rem; max-width: 38rem; font-size: 1.15rem; color: var(--f-ink-2); }
        .cc-center { text-align: center; }
        .cc-center .cc-lead { margin-inline: auto; }
        .cc-center .cc-kick { justify-content: center; }
        .cc-kick {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.25rem;
            font-size: 0.8rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: var(--f-ink);
        }
        .cc-no {
            flex: none;
            display: grid;
            place-items: center;
            width: 3rem;
            aspect-ratio: 1;
            background: var(--k-bg);
            color: var(--k-fg);
            clip-path: var(--cc-star);
            font-family: var(--cc-display);
            font-size: 0.95rem;
            letter-spacing: 0;
        }
        .cc-tag {
            display: inline-block;
            padding: 0.24rem 0.75rem 0.18rem;
            border-radius: 999px;
            background: var(--tag-bg);
            color: var(--tag-fg);
            font-size: 0.7rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            line-height: 1.4;
            white-space: nowrap;
        }
        .cc-tag-pro { background: #ee6c2a; color: #2b2a28; }
        .cc-terms { display: flex; align-items: flex-start; gap: 0.75rem; margin-top: 2rem; font-size: 0.975rem; color: var(--f-ink-2); }
        .cc-terms .cc-tag { margin-top: 0.15rem; }
        .cc-center .cc-terms { justify-content: center; text-align: start; max-width: 46rem; margin-inline: auto; }
        #cc a.text-blue-600.hover\:underline {
            display: inline;
            color: var(--f-link);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 0.18em;
        }

        /* Buttons: a push-button with a base under it. */
        .cc-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.7rem 0.9rem;
            border-radius: 999px;
            background: var(--cc-orange);
            color: #2b2a28;
            font-family: var(--cc-display);
            font-size: 1.08rem;
            line-height: 1.1;
            box-shadow: 0 0.32rem 0 var(--cc-btn-edge);
            transition: translate 0.15s ease, box-shadow 0.15s ease;
        }
        .cc-btn:hover { translate: 0 0.14rem; box-shadow: 0 0.18rem 0 var(--cc-btn-edge); }
        .cc-btn:active { translate: 0 0.3rem; box-shadow: 0 0.02rem 0 var(--cc-btn-edge); }
        .cc-btn svg { width: 1.15rem; height: 1.15rem; transition: translate 0.2s ease; }
        .cc-btn:hover svg { translate: 0.2rem 0; }
        .cc-go {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding-bottom: 0.2rem;
            border-bottom: 2px solid var(--cc-teal);
            color: var(--f-ink);
            transition: gap 0.2s ease;
        }
        .cc-go:hover { gap: 0.8rem; }
        .cc-go svg { width: 1rem; height: 1rem; }

        /* The shapes behind things. */
        .cc-shapes { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .cc-shapes i { position: absolute; display: block; }
        .cc-boom {
            width: var(--w, 26rem);
            aspect-ratio: 1;
            border: var(--t, 5.6rem) solid transparent;
            border-top-color: var(--c, var(--cc-orange));
            border-right-color: var(--c, var(--cc-orange));
            border-radius: 50%;
            rotate: var(--r, -30deg);
        }
        .cc-star12 { width: var(--w, 10rem); aspect-ratio: 1; background: var(--c, var(--cc-mustard)); clip-path: var(--cc-star); }
        .cc-atom { width: var(--w, 2.4rem); aspect-ratio: 1; background: var(--c, var(--cc-ink)); clip-path: var(--cc-atom); }
        .cc-blob { width: var(--w, 24rem); height: calc(var(--w, 24rem) * 0.78); background: var(--c, var(--cc-teal)); border-radius: 58% 42% 46% 54% / 55% 48% 52% 45%; rotate: var(--r, -12deg); }
        @media (max-width: 979px) { .cc-shapes .cc-wide { display: none; } }
        @media (max-width: 760px) { .cc-shapes .cc-atom { display: none; } }
        /* Two columns on a small laptop: the copy fills its column from top to bottom there, and
           the two sparkles on that side sat on its first and last lines. */
        @media (min-width: 1024px) and (max-width: 1279px) { .cc-hero .cc-shapes .cc-atom:nth-child(n + 3) { display: none; } }
        .cc-spin { animation: cc-spin var(--dur, 90s) linear infinite; }
        @keyframes cc-spin { to { rotate: 360deg; } }
        @supports (animation-timeline: view()) {
            html.es-anim #cc .cc-drift {
                animation: cc-drift linear both;
                animation-timeline: view();
                animation-range: entry 0% exit 100%;
            }
        }
        @keyframes cc-drift {
            from { translate: 0 calc(var(--d, 44px) * -1); }
            to { translate: 0 var(--d, 44px); }
        }
        .cc-argyle {
            --s: 2.2rem;
            --c: #1d7874;
            height: var(--s);
            background:
                linear-gradient(135deg, var(--c) 25%, transparent 25%) calc(var(--s) / -2) 0 / var(--s) var(--s),
                linear-gradient(225deg, var(--c) 25%, transparent 25%) calc(var(--s) / -2) 0 / var(--s) var(--s),
                linear-gradient(315deg, var(--c) 25%, transparent 25%) 0 0 / var(--s) var(--s),
                linear-gradient(45deg, var(--c) 25%, transparent 25%) 0 0 / var(--s) var(--s);
            background-color: #f6efe0;
        }

        /* ---------------------------------------------------------------
           The dial: section rail on wide screens
           --------------------------------------------------------------- */
        .cc-dial { display: none; }
        @media (min-width: 1500px) {
            /* The nav is a box the size of the page that clips the rail, so the rail stays fixed
               to the screen and still ends where the page does, instead of riding on over the
               site footer. */
            .cc-dial { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .cc-dial ul { position: fixed; right: 1.4rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.35rem; justify-items: end; pointer-events: auto; }
            .cc-dial a { display: flex; align-items: center; gap: 0.7rem; padding: 0.25rem 0; font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--cc-ink-2); }
            .cc-dial a span { opacity: 0; translate: 0.4rem 0; padding: 0.15rem 0.6rem 0.1rem; border-radius: 999px; background: var(--cc-ink); color: var(--cc-ground); transition: opacity 0.2s ease, translate 0.2s ease; }
            /* Every mark carries a ring of the page ground: unseen on the cream, and what keeps
               the marks from vanishing while the rail passes over the teal and charcoal bands
               (grey on that teal is 1.05 to 1) and the orange one over the orange finale. */
            .cc-dial a::after { content: ""; width: 0.6rem; aspect-ratio: 1; rotate: 45deg; background: var(--cc-ink-3); box-shadow: 0 0 0 1.5px var(--cc-ground); transition: background-color 0.25s ease, scale 0.25s ease; }
            .cc-dial a:hover span, .cc-dial a:focus-visible span { opacity: 1; translate: 0 0; }
            .cc-dial a.is-active::after { background: #ee6c2a; scale: 1.5; }
        }

        /* ---------------------------------------------------------------
           Hero: the television
           --------------------------------------------------------------- */
        .cc-hero { position: relative; overflow: clip; padding-block: clamp(1.5rem, 3vw, 2.5rem) clamp(3.5rem, 7vw, 5.5rem); }
        .cc-hero-grid { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 1024px) {
            .cc-hero-grid { grid-template-columns: minmax(0, 1.14fr) minmax(0, 0.86fr); gap: 4.5rem; min-height: calc(90svh - 4rem - 6rem); }
        }
        .cc-tv { position: relative; width: min(100%, 41rem); margin-inline: auto; padding-top: 4.4rem; filter: drop-shadow(0 1.8rem 1.6rem var(--cc-shadow)); }
        .dark .cc-tv { filter: drop-shadow(0 0 3.2rem rgba(43, 165, 159, 0.32)) drop-shadow(0 1.8rem 1.6rem rgba(0, 0, 0, 0.55)); }
        /* Behind the set: a boomerang round its near corner and a star over its shoulder. */
        .cc-tv-back { position: absolute; inset: 0; z-index: -1; }
        .cc-tv-back i { position: absolute; display: block; }
        .cc-tv-back .cc-boom { left: clamp(-10rem, -14vw, -4.5rem); bottom: -2.6rem; --w: clamp(13rem, 34vw, 23rem); --t: clamp(3rem, 8vw, 5.4rem); --r: 180deg; }
        .cc-tv-back .cc-star12 { right: clamp(-2.6rem, -3vw, -1rem); top: 1.1rem; --w: clamp(6rem, 15vw, 9.5rem); }
        .cc-tv-ears { position: absolute; left: 50%; top: 4.6rem; width: 0; height: 0; }
        .cc-tv-ears i { position: absolute; left: -1.5px; bottom: 0; width: 3px; height: 4.3rem; border-radius: 2px; background: #2b2a28; transform-origin: 50% 100%; rotate: -36deg; }
        .cc-tv-ears i + i { height: 4.9rem; rotate: 31deg; }
        .cc-tv-ears i::before { content: ""; position: absolute; left: 50%; top: 0; width: 0.75rem; aspect-ratio: 1; border-radius: 50%; background: #f4b942; translate: -50% -50%; }
        .dark .cc-tv-ears i { background: #b3a996; }
        .cc-tv-cab {
            position: relative;
            padding: clamp(0.8rem, 3.2%, 1.4rem) clamp(0.8rem, 3.2%, 1.4rem) 0;
            border-radius: 2rem 2rem 1.1rem 1.1rem;
            background-color: #5d3f27;
            background-image:
                repeating-linear-gradient(93deg, rgba(0, 0, 0, 0.1) 0 2px, rgba(255, 255, 255, 0.03) 2px 7px, rgba(0, 0, 0, 0.04) 7px 12px),
                linear-gradient(180deg, #76502f, #4a311e);
            box-shadow: inset 0 0 0 2px rgba(255, 226, 170, 0.14), inset 0 -0.6rem 1rem rgba(0, 0, 0, 0.25);
        }
        .cc-tv-bezel {
            position: relative;
            padding: 3.4%;
            background: linear-gradient(155deg, #fff8e8, #d8caac);
            -webkit-mask: var(--cc-crt) center / 100% 100% no-repeat;
            mask: var(--cc-crt) center / 100% 100% no-repeat;
        }
        .cc-screen {
            position: relative;
            display: grid;
            place-items: center;
            aspect-ratio: 4 / 3;
            padding: 8% 9%;
            overflow: hidden;
            container-type: inline-size;
            background-color: #125652;
            background-image: radial-gradient(circle at 50% 46%, #1a6f6b 0, #125652 52%, #0a3432 100%);
            color: #f6efe0;
            -webkit-mask: var(--cc-crt) center / 100% 100% no-repeat;
            mask: var(--cc-crt) center / 100% 100% no-repeat;
        }
        .cc-screen-rays {
            position: absolute;
            inset: -40%;
            background: repeating-conic-gradient(from var(--cc-turn), rgba(244, 185, 66, 0.17) 0deg 5deg, rgba(244, 185, 66, 0) 5deg 15deg);
            -webkit-mask: radial-gradient(circle, #000 0 18%, transparent 60%);
            mask: radial-gradient(circle, #000 0 18%, transparent 60%);
            animation: cc-turn 90s linear infinite;
        }
        @keyframes cc-turn { to { --cc-turn: 360deg; } }
        .cc-screen-glass {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            background:
                radial-gradient(120% 80% at 16% 6%, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0) 42%),
                repeating-linear-gradient(180deg, rgba(0, 0, 0, 0.13) 0 1px, rgba(0, 0, 0, 0) 1px 4px),
                radial-gradient(circle at 50% 50%, rgba(0, 0, 0, 0) 56%, rgba(0, 0, 0, 0.4));
        }
        /* Switching on: a line of light opens to a flash, and the picture is under it. */
        html.es-anim #cc .cc-screen::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 3;
            background: #fffbe9;
            animation: cc-on 0.8s ease-out 0.1s both;
        }
        @keyframes cc-on {
            0% { clip-path: inset(49.7% 6% 49.7% 6%); opacity: 1; }
            40% { clip-path: inset(0 0 0 0); opacity: 0.92; }
            100% { clip-path: inset(0 0 0 0); opacity: 0; }
        }
        .cc-screen-in { position: relative; z-index: 1; width: 100%; text-align: center; }
        .cc-tonight { display: block; font-family: var(--cc-script); font-size: clamp(1.2rem, 8.2cqi, 3rem); line-height: 1; color: #f4b942; rotate: -5deg; margin-bottom: 0.1em; }
        .cc-h1 { font-family: var(--cc-display); font-weight: 400; font-size: clamp(1.7rem, 12.2cqi, 5.6rem); line-height: 1; color: #f6efe0; text-shadow: 0.04em 0.05em 0 rgba(6, 34, 33, 0.55); }
        .cc-h1 .es-mask { padding-inline: 0.1em; }
        .cc-h1 .es-mask-line { white-space: nowrap; }
        .cc-h1-names { color: #f4b942; }
        html.es-anim #cc .cc-mask-3 .es-mask-line { animation-delay: 0.42s; }
        #cc .cc-eyebrow {
            display: inline-block;
            margin-bottom: 0.9em;
            font-family: var(--cc-text);
            font-size: clamp(0.56rem, 2.5cqi, 0.82rem);
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1.4;
            color: #f6efe0;
            text-shadow: none;
        }
        /* On a 360px phone the line is wider than the screen at that tracking: it broke in two
           and rode up into "Tonight!". Closer tracking keeps it on one line. */
        @container (max-width: 15.4rem) {
            #cc .cc-eyebrow { letter-spacing: 0.14em; }
        }
        /* Channel two: turn the dial and the set shows the week instead of the title card. */
        .cc-ch2 {
            position: absolute;
            inset: 0;
            z-index: 1;
            display: grid;
            place-content: center;
            gap: 0.35em;
            padding: 9% 12%;
            background-color: #f4b942;
            background-image: radial-gradient(circle at 50% 46%, #f8ca63 0, #f4b942 55%, #c98f24 100%);
            color: #2b2a28;
            text-align: center;
            font-size: clamp(0.62rem, 3.5cqi, 1.2rem);
            opacity: 0;
            visibility: hidden;
        }
        .cc-ch2-no { font-size: 0.8em; letter-spacing: 0.24em; text-transform: uppercase; }
        .cc-ch2-t { font-family: var(--cc-script); font-size: 3em; line-height: 0.9; }
        .cc-ch2 ul { display: grid; gap: 0.15em; margin-top: 0.5em; font-family: var(--cc-display); font-size: 1.35em; line-height: 1.2; text-align: start; }
        .cc-ch2 li { display: grid; grid-template-columns: 3.1em minmax(0, 1fr); gap: 0.5em; }
        .cc-ch2 b { font-weight: 400; text-transform: uppercase; color: #145a57; }
        .cc-snow {
            position: absolute;
            inset: 0;
            z-index: 2;
            pointer-events: none;
            opacity: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='s'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='1.1' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 2.4 0 0 0 -0.9'/%3E%3C/filter%3E%3Crect width='180' height='180' fill='%23222'/%3E%3Crect width='180' height='180' filter='url(%23s)'/%3E%3C/svg%3E");
            background-size: 180px 180px;
        }
        .cc-ch-in:checked ~ .cc-tv-cab .cc-ch2 { opacity: 1; visibility: visible; }
        .cc-ch-in:checked ~ .cc-tv-cab .cc-screen-in,
        .cc-ch-in:checked ~ .cc-tv-cab .cc-screen-rays { opacity: 0; }
        .cc-ch-in:checked ~ .cc-tv-cab .cc-snow { animation: cc-snow-a 0.45s steps(5, end) 1; }
        .cc-ch-in:not(:checked) ~ .cc-tv-cab .cc-snow { animation: cc-snow-b 0.45s steps(5, end) 1; }
        @keyframes cc-snow-a {
            0% { opacity: 0.96; background-position: 0 0; }
            25% { background-position: 40px 70px; }
            50% { background-position: 90px 20px; }
            75% { opacity: 0.9; background-position: 20px 110px; }
            100% { opacity: 0; background-position: 60px 50px; }
        }
        @keyframes cc-snow-b {
            0% { opacity: 0.96; background-position: 50px 30px; }
            25% { background-position: 10px 90px; }
            50% { background-position: 70px 10px; }
            75% { opacity: 0.9; background-position: 30px 60px; }
            100% { opacity: 0; background-position: 0 0; }
        }
        .cc-knob-ch { cursor: pointer; }
        .cc-knob-ch::before { content: ""; position: absolute; inset: -0.4rem; border-radius: 50%; border: 2px solid #f4b942; opacity: 0; animation: cc-ping 2.4s ease-out 2.2s 3; }
        @keyframes cc-ping { 0% { opacity: 0.9; scale: 0.8; } 100% { opacity: 0; scale: 1.5; } }
        .cc-ch-in:checked ~ .cc-tv-cab .cc-knob-ch { rotate: 80deg; }
        .cc-ch-in:focus-visible ~ .cc-tv-cab .cc-knob-ch { outline: 3px solid #f4b942; outline-offset: 4px; }
        .cc-tv-panel { display: flex; align-items: center; gap: clamp(0.6rem, 2.4%, 1.1rem); padding: 0.85rem 0.5rem 0.95rem; }
        .cc-tv-brand { font-family: var(--cc-script); font-size: clamp(1.15rem, 3.4vw, 1.6rem); line-height: 1; color: #f4b942; white-space: nowrap; }
        .cc-tv-grille { flex: 1; min-width: 0; height: 1.5rem; border-radius: 0.4rem; background: repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.5) 0 3px, rgba(255, 226, 170, 0.28) 3px 6px); }
        .cc-knob {
            position: relative;
            flex: none;
            width: clamp(1.9rem, 6vw, 2.5rem);
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                radial-gradient(circle, #f7dc8f 0 26%, rgba(247, 220, 143, 0) 27%),
                conic-gradient(from 40deg, #b58a2e, #f7dc8f, #a67a22, #f1cf7a, #8f6a1c, #f7dc8f, #b58a2e);
            box-shadow: 0 0.15rem 0.3rem rgba(0, 0, 0, 0.45);
            transition: rotate 0.5s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .cc-knob::after { content: ""; position: absolute; left: calc(50% - 1.5px); top: 8%; width: 3px; height: 30%; border-radius: 2px; background: #2b2a28; }
        .cc-knob-b { rotate: 70deg; }
        .cc-tv:hover .cc-knob-b { rotate: 140deg; }
        .cc-tv-legs { display: flex; justify-content: space-between; height: 3.2rem; padding-inline: 13%; }
        .cc-tv-legs i { width: 1.15rem; height: 100%; background: linear-gradient(180deg, #4a311e 0 76%, #f4b942 76%); clip-path: polygon(10% 0, 90% 0, 64% 100%, 36% 100%); transform-origin: 50% 0; rotate: 13deg; }
        .cc-tv-legs i + i { rotate: -13deg; }

        .cc-hero-copy { position: relative; }
        .cc-lede { font-size: clamp(1.15rem, 1.7vw, 1.35rem); line-height: 1.5; color: var(--cc-ink-2); }
        .cc-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.25rem 1.75rem; margin-top: 1.9rem; }

        /* The night: the thing that is actually for sale, as the ticket for it. */
        .cc-night-wrap { margin-top: 2.6rem; max-width: 27rem; rotate: -1.6deg; filter: drop-shadow(0 0.9rem 1rem var(--cc-shadow)); }
        .cc-night {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 3.3rem;
            background: #f4b942;
            color: #2b2a28;
            border-radius: 0.9rem;
            -webkit-mask:
                radial-gradient(circle at 0 50%, transparent 0.72rem, #000 0.75rem) left / 51% 100% no-repeat,
                radial-gradient(circle at 100% 50%, transparent 0.72rem, #000 0.75rem) right / 51% 100% no-repeat;
            mask:
                radial-gradient(circle at 0 50%, transparent 0.72rem, #000 0.75rem) left / 51% 100% no-repeat,
                radial-gradient(circle at 100% 50%, transparent 0.72rem, #000 0.75rem) right / 51% 100% no-repeat;
        }
        .cc-night-main { padding: 1.35rem 1.2rem 1.25rem 1.9rem; }
        .cc-night-day { font-family: var(--cc-display); font-size: 2.5rem; line-height: 1; }
        .cc-night-hour { margin-top: 0.3rem; font-size: 0.8rem; letter-spacing: 0.2em; text-transform: uppercase; color: #3f3828; }
        .cc-night-rule { height: 0; margin-block: 0.95rem; border-top: 2px dashed rgba(43, 42, 40, 0.4); }
        .cc-night-figs { display: flex; gap: 2.2rem; }
        .cc-night-label { font-size: 0.68rem; letter-spacing: 0.22em; text-transform: uppercase; color: #3f3828; }
        .cc-night-fig { font-family: var(--cc-display); font-size: 1.9rem; line-height: 1.1; }
        .cc-night-who { margin-top: 0.15rem; font-size: 0.98rem; }
        .cc-night-stub { display: grid; place-items: center; border-inline-start: 2px dashed rgba(43, 42, 40, 0.45); }
        .cc-night-stub span { writing-mode: vertical-rl; rotate: 180deg; font-family: var(--cc-display); font-size: 1rem; letter-spacing: 0.14em; text-transform: uppercase; white-space: nowrap; }
        .cc-night-note { margin-top: 1.5rem; max-width: 27rem; font-size: 0.95rem; color: var(--cc-ink-3); }

        /* ---------------------------------------------------------------
           01. The week, as a listings page
           --------------------------------------------------------------- */
        .cc-two { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 980px) {
            .cc-two { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 4.5rem; }
            .cc-two-rev > :first-child { order: 2; }
        }
        .cc-points { display: grid; gap: 1.1rem; margin-top: 2rem; }
        .cc-points li { position: relative; padding-inline-start: 2.1rem; color: var(--f-ink-2); }
        .cc-points li::before { content: ""; position: absolute; left: 0; top: 0.2rem; width: 1.2rem; aspect-ratio: 1; background: var(--f-accent); clip-path: var(--cc-atom); }
        .cc-pt-t { font-family: var(--cc-display); font-size: 1.08rem; color: var(--f-ink); }
        .cc-guide {
            position: relative;
            background: var(--cc-card);
            color: var(--cc-ink);
            border-radius: 0.5rem;
            rotate: 1.2deg;
            box-shadow: 0 1.4rem 2rem -0.8rem rgba(43, 42, 40, 0.45), 0 0 0 1px rgba(43, 42, 40, 0.12);
        }
        .cc-guide-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.3rem 1rem;
            padding: 1.15rem 1.5rem 1rem;
            background: #186b67;
            color: #f6efe0;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .cc-guide-head h3 { font-family: var(--cc-display); font-size: 1.9rem; line-height: 1; }
        .cc-guide-head span { font-size: 0.74rem; letter-spacing: 0.2em; text-transform: uppercase; }
        .cc-guide-row { display: grid; grid-template-columns: 3.6rem minmax(0, auto) minmax(1rem, 1fr) auto; align-items: center; gap: 0.9rem; padding: 0.95rem 1.5rem; }
        .cc-guide-row + .cc-guide-row { border-top: 1px solid var(--cc-line); }
        .cc-guide-day { display: grid; place-items: center; height: 3.2rem; border-radius: 0.7rem 0.7rem 0.7rem 0.2rem; background: var(--bg); color: var(--fg); font-family: var(--cc-display); font-size: 1.05rem; text-transform: uppercase; }
        .cc-guide-what strong { display: block; font-family: var(--cc-display); font-weight: 400; font-size: 1.15rem; line-height: 1.2; }
        .cc-guide-what small { display: block; font-size: 0.9rem; color: var(--cc-ink-3); }
        .cc-guide-dots { align-self: end; margin-bottom: 0.9rem; border-bottom: 2px dotted var(--cc-line); }
        .cc-guide-fee { display: grid; place-items: center; width: 3.9rem; aspect-ratio: 1; background: #f4b942; color: #2b2a28; clip-path: var(--cc-star); font-family: var(--cc-display); font-size: 1.05rem; rotate: -8deg; }
        .cc-guide-fee-free { width: auto; aspect-ratio: auto; padding: 0.3rem 0.8rem 0.22rem; clip-path: none; rotate: none; border-radius: 999px; background: #145a57; color: #f6efe0; font-family: var(--cc-text); font-size: 0.72rem; letter-spacing: 0.18em; text-transform: uppercase; }
        .cc-guide-foot { padding: 0.95rem 1.5rem 1.2rem; border-top: 3px double var(--cc-line); font-size: 0.93rem; color: var(--cc-ink-3); }

        /* ---------------------------------------------------------------
           02. The names: six sets tuned to your Friday, and three table tents
           --------------------------------------------------------------- */
        .cc-sets { display: flex; justify-content: center; align-items: flex-end; gap: clamp(0.5rem, 2vw, 1.4rem); margin-top: 2.75rem; }
        .cc-set { position: relative; width: clamp(2.5rem, 10vw, 5rem); aspect-ratio: 4 / 3; border: 3px solid var(--cc-ink); border-radius: 26% / 30%; background: var(--bg); color: var(--fg); display: grid; place-items: center; font-family: var(--cc-display); font-size: clamp(0.5rem, 1.9vw, 0.95rem); line-height: 1; }
        .cc-set::before,
        .cc-set::after { content: ""; position: absolute; left: calc(50% - 1px); bottom: calc(100% + 2px); width: 2px; height: 0.95rem; border-radius: 1px; background: var(--cc-ink); transform-origin: 50% 100%; rotate: -32deg; }
        .cc-set::after { rotate: 30deg; height: 1.1rem; }
        .cc-set:nth-child(even) { translate: 0 -0.7rem; }
        .cc-tents { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.4rem 2rem; margin-top: 4.25rem; padding-inline: clamp(0rem, 2vw, 1.5rem); }
        .cc-tents > div { display: grid; }
        @media (min-width: 860px) { .cc-tents { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .cc-tent {
            position: relative;
            padding: 0 1.5rem 1.7rem;
            background: var(--cc-card);
            border-radius: 0.3rem 0.3rem 0.15rem 0.15rem;
            box-shadow: 0 0 0 1px var(--cc-line);
            height: 100%;
            transform: perspective(46rem) rotateX(13deg);
            transform-origin: 50% 100%;
            transition: transform 0.4s cubic-bezier(0.34, 1.4, 0.64, 1);
        }
        .cc-tent:hover { transform: perspective(46rem) rotateX(4deg); }
        /* The back leaf of the tent, seen over the fold, and its shadow on the table. */
        .cc-tent::before { content: ""; position: absolute; left: 2.5%; right: 2.5%; bottom: 100%; height: 0.8rem; background: var(--cc-ground-2); box-shadow: inset 0 -0.4rem 0.5rem -0.3rem rgba(0, 0, 0, 0.35); clip-path: polygon(3% 0, 97% 0, 100% 100%, 0 100%); }
        .dark .cc-tent::before { background: #1e1d1b; }
        .cc-tent::after { content: ""; position: absolute; left: 3%; right: 3%; top: 100%; height: 1.3rem; background: radial-gradient(ellipse at 50% 0, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0) 70%); }
        .cc-tent-band { display: flex; align-items: center; justify-content: space-between; margin-inline: -1.5rem; margin-bottom: 1.3rem; padding: 0.6rem 1.5rem 0.5rem; background: var(--bg); color: var(--fg); border-radius: 0.3rem 0.3rem 0 0; font-size: 0.72rem; letter-spacing: 0.22em; text-transform: uppercase; }
        .cc-tent-band i { width: 1.3rem; aspect-ratio: 1; background: currentColor; clip-path: var(--cc-atom); }
        .cc-tent h3 { font-family: var(--cc-display); font-size: 1.4rem; line-height: 1.12; color: var(--cc-ink); }
        .cc-tent p { margin-top: 0.7rem; color: var(--cc-ink-2); font-size: 1rem; }
        .cc-tabletop { height: 1rem; margin-top: 0.15rem; border-radius: 0.5rem; background: linear-gradient(180deg, #2ba59f, #1d7874 45%, #145a57); box-shadow: 0 0.9rem 1.2rem -0.5rem var(--cc-shadow); }

        /* ---------------------------------------------------------------
           03. Booking: the message slips
           --------------------------------------------------------------- */
        .cc-pad { position: relative; max-width: 30rem; margin-inline: auto; padding: 1.4rem 1.4rem 1.5rem; background: #fffaf0; color: #2b2a28; border-radius: 0.4rem; rotate: -1.4deg; box-shadow: 0 1.5rem 2rem -0.8rem rgba(0, 0, 0, 0.5); }
        .cc-pad-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.3rem 1rem; padding-bottom: 0.9rem; border-bottom: 3px double rgba(43, 42, 40, 0.3); }
        .cc-pad-head h3 { font-family: var(--cc-display); font-size: 1.6rem; line-height: 1; }
        .cc-pad-head span { font-size: 0.74rem; letter-spacing: 0.2em; text-transform: uppercase; color: #4b4741; }
        .cc-slips { display: grid; gap: 0.8rem; margin-top: 1.1rem; }
        .cc-slip { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 0.9rem; padding: 0.8rem 0.9rem 0.75rem; background: #f4b942; border-radius: 0.25rem; rotate: var(--r, 0deg); box-shadow: 0 0.25rem 0.5rem -0.2rem rgba(0, 0, 0, 0.35); }
        .cc-slip:nth-child(2) { --r: 0.9deg; }
        .cc-slip:nth-child(3) { --r: -0.7deg; background: #efe4cc; }
        .cc-slip-when { font-size: 0.74rem; letter-spacing: 0.14em; text-transform: uppercase; color: #3f3828; white-space: nowrap; }
        .cc-slip-what { font-family: var(--cc-display); font-size: 1.05rem; line-height: 1.2; }
        .cc-stamp { padding: 0.2rem 0.55rem 0.14rem; border: 2px solid currentColor; border-radius: 0.3rem; font-size: 0.68rem; letter-spacing: 0.2em; text-transform: uppercase; color: #8a360b; rotate: -5deg; }
        .cc-stamp-ok { color: #145a57; rotate: 4deg; }
        .cc-pad-note { margin-top: 1.1rem; font-size: 0.93rem; color: #4b4741; }
        /* On a phone the three columns leave the name less room than "Showcase" needs, and it
           ran under the stamp. The date goes above the name there, and the stamp stands beside both. */
        @media (max-width: 430px) {
            .cc-slip { grid-template-columns: minmax(0, 1fr) auto; gap: 0.1rem 0.9rem; }
            .cc-slip-when { grid-column: 1; grid-row: 1; }
            .cc-slip-what { grid-column: 1; grid-row: 2; }
            .cc-stamp { grid-column: 2; grid-row: 1 / 3; }
        }
        .cc-asks { display: grid; gap: 1.5rem; margin-top: 2.2rem; }
        .cc-asks li { position: relative; padding-inline-start: 2.3rem; }
        .cc-asks li::before { content: ""; position: absolute; left: 0; top: 0.15rem; width: 1.4rem; aspect-ratio: 1; background: #f4b942; clip-path: var(--cc-star); }
        .cc-asks h3 { font-family: var(--cc-display); font-size: 1.2rem; line-height: 1.2; color: var(--f-ink); }
        .cc-asks p { margin-top: 0.3rem; color: var(--f-ink-2); }
        #cc .cc-teal a:focus-visible { outline-color: #f4b942; }

        /* ---------------------------------------------------------------
           04. Selling the room: the bill of fare, and two Fridays
           --------------------------------------------------------------- */
        .cc-sell { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem 3rem; align-items: start; margin-top: clamp(3.9rem, 6vw, 4.5rem); }
        @media (min-width: 980px) { .cc-sell { grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr); } }
        .cc-bill { position: relative; padding: 0.55rem; background: var(--cc-card); border-radius: 0.4rem; box-shadow: 0 1.4rem 2rem -1rem var(--cc-shadow), 0 0 0 1px var(--cc-line); }
        .cc-bill-in { padding: clamp(1.4rem, 4vw, 2.2rem) clamp(1.1rem, 4vw, 2.1rem) 1.6rem; border: 3px double var(--cc-line); border-radius: 0.25rem; }
        .cc-bill-kick { display: inline-block; font-family: var(--cc-script); font-size: 1.7rem; line-height: 1.2; color: var(--cc-orange-hd); rotate: -3deg; transform-origin: 0 100%; }
        .cc-bill-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.9rem; margin-top: 0.7rem; }
        .cc-bill-head h3 { font-family: var(--cc-display); font-size: clamp(1.6rem, 3.4vw, 2.2rem); line-height: 1.05; color: var(--cc-ink); }
        .cc-bill-sub { margin-top: 0.5rem; color: var(--cc-ink-3); }
        .cc-fare { margin-top: 1.4rem; border-top: 2px solid var(--cc-ink); }
        .cc-fare li { display: grid; grid-template-columns: minmax(0, auto) minmax(0.75rem, 1fr) auto auto; align-items: baseline; gap: 0.7rem; padding-block: 0.85rem 0.75rem; border-bottom: 1px solid var(--cc-line); }
        .cc-fare-name { font-family: var(--cc-display); font-size: 1.2rem; color: var(--cc-ink); }
        .cc-fare-dots { border-bottom: 2px dotted var(--cc-line); translate: 0 -0.3rem; }
        .cc-fare-qty { font-size: 0.78rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--cc-ink-3); white-space: nowrap; }
        .cc-fare-fig { min-width: 3.1rem; text-align: end; font-family: var(--cc-display); font-size: 1.35rem; color: var(--cc-teal-ink); }
        .cc-bill-note { margin-top: 1.3rem; font-size: 0.95rem; color: var(--cc-ink-2); }
        /* Two leaves off the wall calendar: the count is kept per night. They hang 3.4rem above
           the bill, so the gap over it (.cc-sell) is never less than that plus the tilt, or they
           cover the last line of the paragraph above. They only stand out past the bill's corner
           once it has a column to itself: on one column that put the second leaf off the screen. */
        .cc-leaves { position: absolute; right: -0.6rem; top: -3.4rem; display: flex; gap: 0.5rem; }
        @media (min-width: 980px) { .cc-leaves { right: -1.6rem; top: -3rem; } }
        .cc-leaf { position: relative; width: 5.2rem; padding: 0 0 0.5rem; background: #fffaf0; color: #2b2a28; border-radius: 0.3rem; text-align: center; box-shadow: 0 0.6rem 0.9rem -0.4rem rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(43, 42, 40, 0.15); rotate: var(--r, -6deg); }
        .cc-leaf + .cc-leaf { --r: 5deg; translate: 0 0.7rem; }
        .cc-leaf b { display: block; padding: 0.22rem 0 0.15rem; border-radius: 0.3rem 0.3rem 0 0; background: #2b2a28; color: #f6efe0; font-family: var(--cc-text); font-weight: 400; font-size: 0.62rem; letter-spacing: 0.24em; text-transform: uppercase; }
        .cc-leaf strong { display: block; font-family: var(--cc-display); font-weight: 400; font-size: 2.1rem; line-height: 1.15; }
        .cc-leaf em { display: inline-block; padding: 0.1rem 0.4rem 0.05rem; border: 2px solid currentColor; border-radius: 0.25rem; font-style: normal; font-size: 0.56rem; letter-spacing: 0.16em; text-transform: uppercase; color: #a8420e; rotate: -7deg; }
        .cc-leaf + .cc-leaf em { color: #145a57; rotate: 0deg; }
        html.es-anim #cc [data-reveal]:not(.is-revealed) .cc-leaf em { scale: 2.2; opacity: 0; }
        .cc-leaf em { transition: scale 0.35s cubic-bezier(0.34, 1.6, 0.64, 1) 0.7s, opacity 0.2s ease 0.7s; }
        .cc-perks { display: grid; gap: 1.5rem; }
        .cc-perk { position: relative; padding: 1.5rem 1.5rem 1.4rem; background: var(--cc-card); border-radius: 1.4rem 1.4rem 1.4rem 0.3rem; box-shadow: 0 0 0 1px var(--cc-line); }
        /* The second colour, printed a touch out of register behind the card. */
        .cc-perk::before { content: ""; position: absolute; inset: 0; z-index: -1; border-radius: inherit; background: var(--c); translate: 0.7rem 0.7rem; }
        .cc-perks { isolation: isolate; }
        .cc-perk-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.8rem; }
        .cc-perk h3 { font-family: var(--cc-display); font-size: 1.3rem; line-height: 1.15; color: var(--cc-ink); }
        .cc-perk p { margin-top: 0.6rem; color: var(--cc-ink-2); }

        /* ---------------------------------------------------------------
           05. Who it is for: six books of matches
           --------------------------------------------------------------- */
        .cc-books { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2.2rem 1.1rem; margin-top: clamp(3rem, 6vw, 4.5rem); }
        @media (min-width: 900px) { .cc-books { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3rem 2rem; } }
        .cc-book { position: relative; display: flex; flex-direction: column; min-width: 0; perspective: 46rem; filter: drop-shadow(0 1rem 0.9rem var(--cc-shadow)); }
        .cc-book-flap { position: relative; height: clamp(6.4rem, 16vw, 7.6rem); transform-style: preserve-3d; transform-origin: 50% 100%; transform: rotateX(0deg); transition: transform 1s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(var(--reveal-delay, 0s) + 0.15s); }
        html.es-anim #cc .cc-book:not(.is-revealed) .cc-book-flap { transform: rotateX(-176deg); }
        /* The book is what the reveal watches, and the shared reveal ends on filter: none, which
           outranked the shadow above and took it off every book once it had closed. */
        #cc .cc-book { filter: drop-shadow(0 1rem 0.9rem var(--cc-shadow)); }
        @media (hover: hover) { .cc-book:hover .cc-book-flap { transform: rotateX(16deg); transition-delay: 0s; transition-duration: 0.45s; } }
        .cc-book-in,
        .cc-book-out { position: absolute; inset: 0; display: grid; place-items: center; padding: 0.8rem 0.9rem; border-radius: 0.5rem 0.5rem 0 0; background: var(--bg); color: var(--fg); backface-visibility: hidden; -webkit-backface-visibility: hidden; text-align: center; }
        .cc-book-out { transform: rotateX(180deg); border-radius: 0 0 0.5rem 0.5rem; }
        .cc-book-out i { width: 3.4rem; aspect-ratio: 1; background: currentColor; clip-path: var(--cc-atom); }
        .cc-book-in h3 { font-family: var(--cc-display); font-size: clamp(1.02rem, 2.9vw, 1.5rem); line-height: 1.1; text-wrap: balance; }
        .cc-book-in::after { content: ""; position: absolute; left: 0.6rem; right: 0.6rem; bottom: 0.45rem; border-bottom: 2px solid currentColor; opacity: 0.35; }
        .cc-comb { display: flex; justify-content: center; gap: clamp(0.2rem, 1.1vw, 0.45rem); height: 2.1rem; padding: 0.55rem 0.6rem 0; background: #e9dcc0; overflow: hidden; }
        .cc-comb i { position: relative; width: clamp(0.38rem, 1.5vw, 0.55rem); height: 100%; background: linear-gradient(90deg, #f3e7cd, #d4c29d); border-radius: 2px 2px 0 0; }
        .cc-comb i::before { content: ""; position: absolute; left: 50%; top: -0.32rem; width: 150%; aspect-ratio: 0.9; border-radius: 50% 50% 46% 46%; background: #ee6c2a; translate: -50% 0; transition: box-shadow 0.3s ease, background-color 0.3s ease; }
        @media (hover: hover) { .cc-book:hover .cc-comb i:nth-child(4)::before { background: #f4b942; box-shadow: 0 -0.3rem 0.8rem 0.25rem rgba(244, 185, 66, 0.9), 0 -0.7rem 1.1rem 0.2rem rgba(238, 108, 42, 0.6); } }
        .cc-book-body { flex: 1; display: flex; flex-direction: column; padding: 0.95rem 1rem 1rem; background: var(--cc-card); color: var(--cc-ink-2); font-size: 0.975rem; line-height: 1.5; }
        .cc-book-body a { align-self: flex-start; margin-top: auto; padding-top: 0.8rem; }
        .cc-book-body a span { display: inline-flex; align-items: center; gap: 0.35rem; border-bottom: 2px solid var(--cc-teal); color: var(--cc-ink); transition: gap 0.2s ease; }
        .cc-book-body a:hover span { gap: 0.65rem; }
        .cc-book-body a svg { width: 0.95rem; height: 0.95rem; }
        .cc-strike { position: relative; height: 1.7rem; border-radius: 0 0 0.5rem 0.5rem; background-color: #2b2a28; background-image: radial-gradient(rgba(246, 239, 224, 0.28) 0.6px, rgba(246, 239, 224, 0) 0.9px); background-size: 4px 4px; display: grid; place-items: center; }
        @media (max-width: 620px) { .cc-strike span { display: none; } }
        .cc-strike span { padding-inline: 0.5rem; background: #2b2a28; color: #d9d0be; font-size: 0.5rem; letter-spacing: 0.22em; text-transform: uppercase; white-space: nowrap; overflow: hidden; max-width: 92%; }
        .cc-book-body { position: relative; }
        .cc-book-body::after { content: ""; position: absolute; left: calc(50% - 0.75rem); bottom: -0.2rem; width: 1.5rem; height: 0.3rem; border-radius: 1px; background: linear-gradient(180deg, #f1ede4, #a9a397); box-shadow: 0 1px 1px rgba(0, 0, 0, 0.4); z-index: 1; }
        .dark .cc-strike, .dark .cc-strike span { background-color: #121110; }

        /* ---------------------------------------------------------------
           06. How it works: three cue cards on the studio floor
           --------------------------------------------------------------- */
        .cc-cues { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.6rem 2rem; margin-top: clamp(3rem, 6vw, 4.5rem); }
        @media (min-width: 860px) { .cc-cues { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .cc-cue { position: relative; padding: 2.6rem 1.6rem 1.7rem; background: #fffaf0; color: #2b2a28; border-radius: 0.35rem; rotate: var(--r, -1.5deg); box-shadow: 0 1.6rem 2rem -0.9rem rgba(0, 0, 0, 0.7); transition: rotate 0.35s cubic-bezier(0.34, 1.4, 0.64, 1), translate 0.35s cubic-bezier(0.34, 1.4, 0.64, 1); }
        .cc-cue:nth-child(2) { --r: 1.2deg; }
        .cc-cue:nth-child(3) { --r: -0.8deg; }
        .cc-cue:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .cc-cue-no { position: absolute; left: 1.3rem; top: -1.5rem; display: grid; place-items: center; width: 3.6rem; aspect-ratio: 1; border-radius: 50%; background: var(--bg); color: var(--fg); font-family: var(--cc-display); font-size: 1.3rem; box-shadow: 0 0.4rem 0.6rem -0.2rem rgba(0, 0, 0, 0.5); }
        .cc-cue h3 { font-family: var(--cc-display); font-size: 1.55rem; line-height: 1.1; }
        .cc-cue p { margin-top: 0.75rem; color: #4b4741; }
        #cc .cc-studio a:focus-visible { outline-color: #f4b942; }

        /* ---------------------------------------------------------------
           Key features: the channel selector
           --------------------------------------------------------------- */
        .cc-chans { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 980px) { .cc-chans { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .cc-chan-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.1rem; }
        @media (min-width: 640px) { .cc-chan-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.4rem; } }
        .cc-chan { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 1rem; padding: 1.1rem 1.2rem 1.05rem; background: var(--cc-card); color: var(--cc-ink); border-radius: 1.1rem; box-shadow: 0 0 0 1px var(--cc-line), 0 0.35rem 0 var(--cc-line); transition: translate 0.15s ease, box-shadow 0.15s ease; }
        .cc-chan:hover { translate: 0 0.2rem; box-shadow: 0 0 0 1px var(--cc-line), 0 0.15rem 0 var(--cc-line); }
        .cc-chan-no { display: grid; place-items: center; width: 3.2rem; aspect-ratio: 1; border-radius: 50%; background: var(--bg); color: var(--fg); font-family: var(--cc-display); font-size: 1.35rem; transition: rotate 0.4s cubic-bezier(0.34, 1.4, 0.64, 1); }
        .cc-chan:hover .cc-chan-no { rotate: 20deg; }
        .cc-chan strong { display: block; font-family: var(--cc-display); font-weight: 400; font-size: 1.2rem; line-height: 1.15; }
        .cc-chan small { display: block; margin-top: 0.2rem; font-size: 0.93rem; line-height: 1.4; color: var(--cc-ink-2); }
        .cc-chan svg { width: 1.2rem; height: 1.2rem; color: var(--cc-teal-ink); transition: translate 0.2s ease; }
        .cc-chan:hover svg { translate: 0.25rem 0; }
        .cc-chans-more { margin-top: 1.6rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the print changes.
           --------------------------------------------------------------- */
        #cc .cc-plans > section { background: var(--cc-ground-2); }
        #cc .cc-plans h2 { font-family: var(--cc-display); font-weight: 400; letter-spacing: 0; line-height: 1.08; font-size: clamp(1.9rem, 4vw, 3rem); color: var(--cc-ink); }
        #cc .cc-plans h2 + p { color: var(--cc-ink-2); font-size: 1.0625rem; }
        #cc .cc-plans .grid > div { background: var(--cc-card); border: 0; border-radius: 1.5rem 1.5rem 1.5rem 0.4rem; box-shadow: 0 0 0 1px var(--cc-line); color: var(--cc-ink); }
        #cc .cc-plans .grid > div:nth-child(2) { box-shadow: 0 0 0 3px var(--cc-teal); }
        #cc .cc-plans .grid > div span,
        #cc .cc-plans .grid > div p,
        #cc .cc-plans .grid > div li { color: var(--cc-ink-2); }
        #cc .cc-plans .grid > div .text-3xl { font-family: var(--cc-display); font-weight: 400; font-size: 2.5rem; color: var(--cc-ink); }
        #cc .cc-plans .grid > div .uppercase { color: var(--cc-ink); letter-spacing: 0.2em; }
        #cc .cc-plans .grid > div .rounded-full { background: #f4b942; color: #2b2a28; }
        #cc .cc-plans .grid > div svg { color: var(--cc-teal-ink); }
        #cc .cc-plans a.font-medium { color: var(--cc-ink); border-bottom: 2px solid var(--cc-teal); }
        #cc .cc-plans a.rounded-2xl { background: var(--cc-orange); color: #2b2a28; border-radius: 999px; box-shadow: 0 0.32rem 0 var(--cc-btn-edge); font-family: var(--cc-display); font-weight: 400; }
        #cc .cc-plans a.rounded-2xl:hover { transform: translateY(0.14rem); box-shadow: 0 0.18rem 0 var(--cc-btn-edge); }

        #cc .cc-keep > section { background: var(--cc-ground-2); border-top: 0; }
        #cc .cc-keep h2 { font-family: var(--cc-display); font-weight: 400; letter-spacing: 0; font-size: clamp(1.8rem, 3.6vw, 2.6rem); color: var(--cc-ink); }
        #cc .cc-keep p.uppercase { letter-spacing: 0.22em; color: var(--cc-orange-ink); }
        #cc .cc-keep .grid > a { background: var(--cc-card); border: 0; border-radius: 1.4rem 1.4rem 1.4rem 0.3rem; box-shadow: 0 0 0 1px var(--cc-line); }
        #cc .cc-keep .grid > a:hover { box-shadow: 0 0 0 3px var(--cc-teal); }
        #cc .cc-keep .grid > a > span:first-child { display: none; }
        #cc .cc-keep .grid > a h3 { font-family: var(--cc-display); font-weight: 400; color: var(--cc-ink); }
        #cc .cc-keep .grid > a p { color: var(--cc-ink-2); }
        #cc .cc-keep .grid > a > span:last-child,
        #cc .cc-keep a.self-start { color: var(--cc-teal-ink); }

        /* ---------------------------------------------------------------
           Related pages: four more sets, each on its own programme
           --------------------------------------------------------------- */
        .cc-also-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1rem 2rem; margin-bottom: 2.6rem; }
        .cc-also { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.4rem 1.1rem; }
        @media (min-width: 900px) { .cc-also { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 2rem; } }
        .cc-also a { display: block; padding: 0.55rem 0.55rem 0; border-radius: 1.2rem 1.2rem 0.7rem 0.7rem; background-color: #5d3f27; background-image: linear-gradient(180deg, #76502f, #4a311e); box-shadow: 0 1rem 1.2rem -0.6rem var(--cc-shadow); transition: translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1); }
        .cc-also a:hover { translate: 0 -0.4rem; }
        .cc-also-screen { display: flex; flex-direction: column; justify-content: center; gap: 0.3rem; aspect-ratio: 4 / 3; padding: 12% 12%; background: var(--bg); color: var(--fg); text-align: center; -webkit-mask: var(--cc-crt) center / 100% 100% no-repeat; mask: var(--cc-crt) center / 100% 100% no-repeat; container-type: inline-size; }
        .cc-also-screen small { font-size: clamp(0.56rem, 6cqi, 0.74rem); letter-spacing: 0.16em; text-transform: uppercase; }
        .cc-also-screen strong { font-family: var(--cc-display); font-weight: 400; font-size: clamp(1rem, 14cqi, 1.9rem); line-height: 1.05; text-wrap: balance; }
        .cc-also-dials { display: flex; justify-content: flex-end; gap: 0.4rem; padding: 0.45rem 0.4rem 0.5rem; }
        .cc-also-dials i { width: 0.8rem; aspect-ratio: 1; border-radius: 50%; background: conic-gradient(from 40deg, #b58a2e, #f7dc8f, #a67a22, #f1cf7a, #b58a2e); }

        /* Please stand by: the test card between programmes. */
        .cc-standby { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(7, 1fr); height: clamp(7.5rem, 14vw, 10rem); }
        .cc-standby > i { background: var(--c); }
        .cc-standby::after { content: ""; position: absolute; inset: auto 0 0 0; height: 0.9rem; background: repeating-linear-gradient(90deg, #2b2a28 0 2.4rem, #f6efe0 2.4rem 4.8rem); }
        .cc-standby-card { position: absolute; left: 50%; top: 50%; translate: -50% -50%; display: grid; place-items: center; width: clamp(5.2rem, 9.5vw, 7.2rem); aspect-ratio: 1; border-radius: 50%; background: #f6efe0; color: #2b2a28; text-align: center; box-shadow: 0 0 0 0.4rem #2b2a28, 0 0 0 0.62rem #f6efe0; font-family: var(--cc-display); font-size: clamp(0.62rem, 1.15vw, 0.86rem); line-height: 1.1; text-transform: uppercase; }
        .cc-standby-card::before { content: ""; position: absolute; inset: 0; border-radius: 50%; background: linear-gradient(#2b2a28, #2b2a28) 50% 0 / 2px 16% no-repeat, linear-gradient(#2b2a28, #2b2a28) 50% 100% / 2px 16% no-repeat, linear-gradient(#2b2a28, #2b2a28) 0 50% / 16% 2px no-repeat, linear-gradient(#2b2a28, #2b2a28) 100% 50% / 16% 2px no-repeat; }

        /* ---------------------------------------------------------------
           07. Questions, asked at the box office
           --------------------------------------------------------------- */
        .cc-faq { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .cc-faq { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
            .cc-faq-head { position: sticky; top: 6.5rem; }
        }
        /* The window the questions are asked at: an arch, a grille and a speaking hole. */
        .cc-window { display: none; }
        @media (min-width: 1000px) {
            .cc-window {
                position: relative;
                display: block;
                width: 13.5rem;
                margin-top: 2.6rem;
                padding: 1.1rem 1.1rem 0;
                border-radius: 7rem 7rem 0.5rem 0.5rem;
                background: #186b67;
                box-shadow: inset 0 0 0 0.45rem #f4b942, 0 1.2rem 1.4rem -0.8rem var(--cc-shadow);
            }
            .cc-window-pane {
                position: relative;
                height: 9.5rem;
                border-radius: 5.9rem 5.9rem 0 0;
                background-color: #0c4441;
                background-image: repeating-linear-gradient(90deg, rgba(244, 185, 66, 0.9) 0 3px, rgba(244, 185, 66, 0) 3px 1.55rem);
                background-position: 50% 0;
                overflow: hidden;
            }
            .cc-window-pane::after { content: ""; position: absolute; left: 50%; bottom: 1.2rem; width: 3.2rem; aspect-ratio: 1; translate: -50% 0; border-radius: 50%; background: #0c4441; box-shadow: 0 0 0 3px #f4b942, inset 0 0 0 0.5rem #0c4441, inset 0 0 0 0.62rem rgba(244, 185, 66, 0.9); }
            .cc-window-sill { margin-inline: -1.1rem; padding: 0.6rem 0 0.5rem; background: #2b2a28; color: #f4b942; border-radius: 0 0 0.5rem 0.5rem; text-align: center; font-family: var(--cc-display); font-size: 1.05rem; letter-spacing: 0.12em; text-transform: uppercase; }
        }
        .cc-qa { display: grid; gap: 0.9rem; }
        .cc-qa details { background: var(--cc-card); border-radius: 1.2rem 1.2rem 1.2rem 0.3rem; box-shadow: 0 0 0 1px var(--cc-line); transition: box-shadow 0.2s ease; }
        .cc-qa details[open] { box-shadow: 0 0 0 3px var(--cc-teal); }
        .cc-qa summary { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 1rem; padding: 1.15rem 1.25rem 1.1rem 1.4rem; cursor: pointer; border-radius: inherit; }
        .cc-qa h3 { font-family: var(--cc-display); font-size: 1.16rem; line-height: 1.25; color: var(--cc-ink); }
        .cc-qa summary i { position: relative; width: 2.1rem; aspect-ratio: 1; border-radius: 50%; background: #f4b942; transition: rotate 0.35s cubic-bezier(0.34, 1.4, 0.64, 1), background-color 0.25s ease; }
        .cc-qa summary i::before,
        .cc-qa summary i::after { content: ""; position: absolute; left: 26%; right: 26%; top: calc(50% - 1.5px); height: 3px; border-radius: 2px; background: #2b2a28; }
        .cc-qa summary i::after { rotate: 90deg; }
        .cc-qa details[open] summary i { rotate: 45deg; background: #ee6c2a; }
        .cc-qa details p { padding: 0 1.4rem 1.4rem; max-width: 46rem; color: var(--cc-ink-2); }

        /* ---------------------------------------------------------------
           The finale: the sign comes on
           --------------------------------------------------------------- */
        .cc-fin { position: relative; overflow: clip; padding-block: clamp(4.5rem, 9vw, 7.5rem); background: #ee6c2a; color: #2b2a28; text-align: center; --f-ink: #2b2a28; --f-ink-2: #2b2a28; --f-accent: #0c4441; --f-link: #2b2a28; }
        .cc-fin-rays { position: absolute; left: 50%; top: 42%; width: max(130vw, 84rem); aspect-ratio: 1; translate: -50% -50%; border-radius: 50%; background: repeating-conic-gradient(rgba(244, 185, 66, 0.62) 0deg 6deg, rgba(244, 185, 66, 0) 6deg 12deg); -webkit-mask: radial-gradient(circle, transparent 0 6%, #000 8% 46%, transparent 62%); mask: radial-gradient(circle, transparent 0 6%, #000 8% 46%, transparent 62%); animation: cc-spin 180s linear infinite; pointer-events: none; }
        .cc-fin-in { position: relative; }
        .cc-onair { display: flex; align-items: flex-end; justify-content: center; gap: clamp(0.8rem, 3vw, 1.8rem); margin-bottom: 2.2rem; }
        .cc-sign { position: relative; padding: 0.5rem; border-radius: 0.9rem; background: #1c1b19; box-shadow: 0 0.9rem 1.4rem -0.4rem rgba(43, 42, 40, 0.55); }
        .cc-sign::before,
        .cc-sign::after { content: ""; position: absolute; bottom: 100%; width: 3px; height: 4rem; background: #2b2a28; }
        .cc-sign::before { left: 18%; }
        .cc-sign::after { right: 18%; }
        .cc-sign span { display: block; padding: 0.5rem 1.5rem 0.4rem; border-radius: 0.5rem; background: linear-gradient(180deg, #fff6d6, #f7c95c); color: #2b2a28; font-family: var(--cc-display); font-size: clamp(1.5rem, 5vw, 2.9rem); letter-spacing: 0.1em; line-height: 1.15; text-transform: uppercase; box-shadow: 0 0 2.4rem 0.5rem rgba(255, 236, 170, 0.8), inset 0 0 0 2px rgba(255, 255, 255, 0.55); animation: cc-blink 3.2s steps(1, end) infinite; }
        @keyframes cc-blink {
            0%, 62%, 72%, 100% { background: linear-gradient(180deg, #fff6d6, #f7c95c); color: #2b2a28; box-shadow: 0 0 2.4rem 0.5rem rgba(255, 236, 170, 0.8), inset 0 0 0 2px rgba(255, 255, 255, 0.55); }
            56%, 66% { background: linear-gradient(180deg, #8a7440, #6f5b2c); color: #3a3220; box-shadow: 0 0 0 0 rgba(255, 236, 170, 0), inset 0 0 0 2px rgba(255, 255, 255, 0.1); }
        }
        .cc-wave { display: flex; align-items: flex-end; gap: 0.26rem; height: 2.9rem; }
        .cc-wave i { width: 0.36rem; height: 100%; border-radius: 2px; background: #2b2a28; transform-origin: 50% 100%; scale: 1 var(--h, 0.5); animation: cc-wave 0.9s steps(5, end) infinite alternate; animation-delay: calc(var(--i) * -0.17s); }
        @keyframes cc-wave { from { scale: 1 0.14; } to { scale: 1 1; } }
        /* Nine bars a side are wider than a phone: the outer ones were cut off by the edge of
           the screen. Five a side fit. */
        @media (max-width: 440px) {
            .cc-wave:first-child i:nth-child(-n + 4),
            .cc-wave:last-child i:nth-child(n + 6) { display: none; }
        }
        .cc-fin-kick { font-size: 0.82rem; letter-spacing: 0.26em; text-transform: uppercase; }
        .cc-fin-h2 { margin-top: 0.8rem; font-family: var(--cc-display); font-size: clamp(2.4rem, 7vw, 5.2rem); line-height: 1.02; text-wrap: balance; }
        .cc-fin-h2 .cc-sc { font-size: 1.22em; text-shadow: 0.03em 0.04em 0 rgba(255, 246, 223, 0.5); }
        .cc-fin-sub { margin: 1.3rem auto 0; max-width: 34rem; font-size: 1.2rem; }
        .cc-claim-card { position: relative; width: min(100%, 44rem); margin: 2.6rem auto 0; padding: clamp(1.1rem, 3vw, 1.6rem); background: #fffaf0; color: #2b2a28; border-radius: 1.8rem 1.8rem 1.8rem 0.4rem; box-shadow: 0 1.6rem 2.2rem -1rem rgba(100, 36, 4, 0.7); }
        .cc-claim-row { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; }
        @media (min-width: 720px) { .cc-claim-row { grid-template-columns: minmax(0, 1fr) auto; align-items: center; } }
        #cc .cc-claim { display: flex; align-items: center; min-width: 0; padding: 1rem 1.25rem; border: 2px solid #2b2a28; border-radius: 999px; background: #fff; font-size: clamp(1rem, 3.4vw, 1.1rem); transition: box-shadow 0.2s ease; }
        #cc .cc-claim:focus-within { border-color: #2b2a28; box-shadow: 0 0 0 4px rgba(29, 120, 116, 0.4); }
        #cc .cc-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #2b2a28; box-shadow: none; outline: none; }
        #cc .cc-claim input::placeholder { color: #7a746a; }
        .cc-claim span { flex: none; color: #4b4741; user-select: none; }
        .cc-claim-note { margin-top: 0.9rem; font-size: 0.95rem; color: #4b4741; }
        #cc .cc-fin a:focus-visible { outline-color: #2b2a28; }
        #cc .cc-fin .cc-btn { background: #186b67; color: #f6efe0; box-shadow: 0 0.32rem 0 #0f4d4a; }
        #cc .cc-fin .cc-btn:hover { box-shadow: 0 0.18rem 0 #0f4d4a; }

        @media (prefers-reduced-motion: reduce) {
            .cc-spin, .cc-screen-rays, .cc-fin-rays, .cc-sign span, .cc-wave i, .cc-knob-ch::before { animation: none; }
            #cc .cc-snow { animation: none; }
            .cc-btn, .cc-knob, .cc-tent, .cc-book-flap, .cc-cue, .cc-chan, .cc-chan-no, .cc-also a, .cc-leaf em, .cc-comb i::before { transition: none; }
        }
    </style>

    @php
        // The room's week: three products in one space. The hero card reads
        // the Friday row out of this SAME array, so the two cannot drift.
        // Advance is asserted to be under the door price at build time.
        $nights = [
            ['Tuesday',  'Open mic',         'free', null, null, 'Capacity 30'],
            ['Thursday', 'Showcase',         'paid', 12,   15,   '6 comics'],
            ['Friday',   'Weekend headline', 'paid', 18,   22,   'Sells out'],
            ['Saturday', 'Weekend headline', 'paid', 18,   22,   'Sells out'],
        ];

        $headline = null;
        foreach ($nights as $row) {
            if ($row[0] === 'Friday') {
                $headline = $row;
                break;
            }
        }

        $faqs = [
            [
                'q' => 'Is Event Schedule free for comedy clubs?',
                'a' => 'Running the room is free forever: the weekly nights as recurring events, date exceptions for the weeks you are dark, free registration with a capacity for open mics, booking requests from comics with an approved list for your regulars, sub-schedules, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Scanning the QR on a ticket at the door costs nothing either. Charging for a seat is Pro at '.plan_price($proMonthly).' a month, which also adds the live check-in dashboard, and Event Schedule charges zero platform fees on sales at any tier.',
            ],
            [
                'q' => 'Can I put tickets on sale before I have booked the lineup?',
                'a' => 'Yes, and that is the normal way round for a comedy room. The night is one recurring event with its own ticket types, so Friday at eight can be on sale in January. Add the participants when you book them and the show updates in place, so nobody has to be sent a new link.',
            ],
            [
                'q' => 'What happens to the comics I add to a show?',
                'a' => 'Adding someone as a participant attaches them to the show, and the night\'s page lists the whole bill, whether or not they have an account. If they already run their own schedule on Event Schedule, the date turns up there for them to accept, or straight away if they have added your club to their approved list. If they do not, adding them creates a public page that says your club made it and that they have not claimed it yet, and you can tick a box to email them a link to it. They claim it by signing in with the email address on it. Until then it stays out of search engines, and once they do, your dates stay on it and your future ones post without waiting for approval.',
            ],
            [
                'q' => 'How do comics ask for a spot?',
                'a' => 'Turn on booking requests and comics can submit to your schedule instead of messaging you. Every request waits for you to accept it, and you are emailed when new ones are pending. Comics you book regularly can go on an approved list so their submissions post without waiting, as long as they are sending them from their own schedule.',
            ],
            [
                'q' => 'Can I charge different prices for advance and at the door?',
                'a' => 'Yes. Set up two ticket types at different prices and sell both from the same night. Each type carries its own count, and the count is kept per occurrence, so a sold-out Friday does not stop next Friday selling. A type can also be given a single date to go on sale or come off it, which applies once to the whole run rather than repeating each week. Check people in by scanning the QR code on each ticket, and take payment through your own Stripe or PayPal account, a payment link or cash, with no platform fee on top.',
            ],
            [
                'q' => 'Can people ask to hear when a show goes on sale?',
                'a' => 'Yes, with nothing but an email address. Switch on the free "Notify me" card, announce the show, give its tickets a date to go on sale, and anyone who wants in can leave their address on the event page without an account. They get one email when tickets go on sale, one if the show is cancelled, a reminder shortly before it starts, and any change notice you send, and nothing else. It is kept per date, so asking about one Friday is not a sign-up for every Friday. You can see how many people are waiting on the event\'s Tickets panel, and it is free on every plan.',
            ],
            [
                'q' => 'Can I refund a ticket, or part of one?',
                'a' => 'Yes, from the Sales page, on Pro. A ticket paid through Stripe or PayPal can be refunded in full or in part, and the money goes back through the provider before the sale is marked refunded. A full refund puts that ticket back on sale for its night, and on Pro the waitlist for that night is told. A partial refund leaves the ticket valid. A sale taken in cash or through a payment link is recorded with Mark as Refunded, which moves no money. The buyer is not emailed about a refund, so let them know yourself.',
            ],
        ];

        $dotSections = [
            ['top', 'Friday at eight'],
            ['night', 'The night'],
            ['names', 'The names'],
            ['asks', 'Booking'],
            ['selling', 'Selling the room'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    @php
        // The stars are cut with clip-path, so their points are worked out once here and handed
        // to the stylesheet as custom properties: a twelve-point seal, and the thin eight-point
        // "atomic" sparkle with every other ray short.
        $ccPoly = function (int $points, float $inner, float $outer = 50, ?float $alternate = null) {
            $out = [];
            for ($i = 0; $i < $points * 2; $i++) {
                $radius = $i % 2 === 1 ? $inner : ($alternate !== null && intdiv($i, 2) % 2 === 1 ? $alternate : $outer);
                $angle = deg2rad($i * 180 / $points - 90);
                $out[] = round(50 + $radius * cos($angle), 2).'% '.round(50 + $radius * sin($angle), 2).'%';
            }

            return 'polygon('.implode(', ', $out).')';
        };
        $ccArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $ccDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        // Ground and type for anything that comes in the room's three colours plus charcoal.
        $ccInks = [
            ['#186b67', '#f6efe0'],
            ['#ee6c2a', '#2b2a28'],
            ['#f4b942', '#2b2a28'],
            ['var(--cc-ink)', 'var(--cc-ground)'],
        ];
    @endphp

    <div id="cc" style="--cc-star: {{ $ccPoly(12, 37) }}; --cc-atom: {{ $ccPoly(8, 8, 50, 30) }};">

        <nav class="cc-dial es-dotnav" aria-label="Page sections">
            <ul>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot"><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ul>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the television                                      -->
        <!-- ============================================================ -->
        <section id="top" class="cc-hero">
            <div class="cc-shapes" aria-hidden="true">
                <i class="cc-blob cc-drift" style="right: -8rem; bottom: -5rem; --w: 27rem; --d: -30px; opacity: 0.2;"></i>
                <i class="cc-atom" style="left: 4%; top: 9%; --w: 2.6rem; --c: var(--cc-teal);"></i>
                <i class="cc-atom" style="right: 2.2%; top: 3.5%; --w: 3.2rem; --c: var(--cc-orange);"></i>
                <i class="cc-atom" style="right: 38%; bottom: 7%; --w: 1.8rem;"></i>
            </div>

            <div class="cc-wrap cc-hero-grid">
                <div class="cc-tv es-fade-up es-d-1">
                    <div class="cc-tv-back" aria-hidden="true">
                        <i class="cc-boom"></i>
                        <i class="cc-star12 cc-spin"></i>
                    </div>
                    <div class="cc-tv-ears" aria-hidden="true"><i></i><i></i></div>
                    <input type="checkbox" id="cc-ch" class="cc-ch-in sr-only" aria-label="Change the channel">
                    <div class="cc-tv-cab">
                        <div class="cc-tv-bezel">
                            <div class="cc-screen">
                                <div class="cc-screen-rays" aria-hidden="true"></div>
                                <div class="cc-screen-in">
                                    <span class="cc-tonight" aria-hidden="true">Tonight!</span>
                                    <h1 class="cc-h1">
                                        <x-marketing.hero-eyebrow class="cc-eyebrow">Comedy club schedules and lineups</x-marketing.hero-eyebrow>
                                        <span class="es-mask"><span class="es-mask-line">They buy</span></span>
                                        <span class="es-mask es-mask-2"><span class="es-mask-line">the night,</span></span>
                                        <span class="es-mask cc-mask-3"><span class="es-mask-line">not <span class="cc-h1-names">the names</span>.</span></span>
                                    </h1>
                                </div>
                                <div class="cc-ch2" aria-hidden="true">
                                    <span class="cc-ch2-no">Channel 2</span>
                                    <span class="cc-ch2-t">The week</span>
                                    <ul>
                                        @foreach ($nights as [$nDay, $nName])
                                            <li><b>{{ Str::substr($nDay, 0, 3) }}</b><span>{{ $nName }}</span></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="cc-snow" aria-hidden="true"></div>
                                <div class="cc-screen-glass" aria-hidden="true"></div>
                            </div>
                        </div>
                        <div class="cc-tv-panel">
                            <span class="cc-tv-brand" aria-hidden="true">The Late Show</span>
                            <span class="cc-tv-grille" aria-hidden="true"></span>
                            <label for="cc-ch" class="cc-knob cc-knob-ch" title="Change the channel"></label>
                            <span class="cc-knob cc-knob-b" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="cc-tv-legs" aria-hidden="true"><i></i><i></i></div>
                </div>

                <div class="cc-hero-copy">
                    <p class="cc-lede es-fade-up es-d-2">
                        Friday at eight sells out before anyone knows who is on it. A music venue
                        sells the band; you sell the room. So put the night on sale first and add
                        the comics once you have booked them.
                    </p>

                    <div class="cc-cta es-fade-up es-d-3">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="cc-btn">
                            Put the room online
                            {!! $ccArrow !!}
                        </a>
                        <a href="#night" class="cc-go">
                            See how a night works
                            {!! $ccDown !!}
                        </a>
                    </div>

                    <!-- The night card: the thing that is actually for sale. -->
                    <div class="es-fade-up es-d-4">
                        <div class="cc-night-wrap">
                            <div class="cc-night">
                                <div class="cc-night-main">
                                    <p class="cc-night-day">{{ $headline[0] }}</p>
                                    <p class="cc-night-hour">8:00pm &middot; every week</p>

                                    <div class="cc-night-rule" aria-hidden="true"></div>

                                    <div class="cc-night-figs">
                                        <div>
                                            <p class="cc-night-label">Advance</p>
                                            <p class="cc-night-fig">${{ $headline[3] }}</p>
                                        </div>
                                        <div>
                                            <p class="cc-night-label">Door</p>
                                            <p class="cc-night-fig">${{ $headline[4] }}</p>
                                        </div>
                                    </div>

                                    <div class="cc-night-rule" aria-hidden="true"></div>

                                    <p class="cc-night-label">Participants</p>
                                    <p class="cc-night-who">Added Wednesday, once the bill is booked.</p>
                                </div>
                                <div class="cc-night-stub" aria-hidden="true"><span>Admit one</span></div>
                            </div>
                        </div>

                        <p class="cc-night-note">
                            One recurring event. On sale since January, and it has never needed a new link.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="cc-argyle" aria-hidden="true"></div>

        <!-- ============================================================ -->
        <!-- 2. The night is the product (01)                             -->
        <!-- ============================================================ -->
        <section id="night" class="cc-sec cc-mustard">
            <div class="cc-shapes" aria-hidden="true">
                <i class="cc-boom cc-drift cc-wide" style="right: -10rem; top: -9rem; --w: 28rem; --t: 5.4rem; --r: 150deg; --c: rgba(238, 108, 42, 0.5); --d: 30px;"></i>
            </div>
            <div class="cc-wrap cc-two" style="position: relative;">
                <div>
                    <p class="cc-kick" data-reveal><span class="cc-no" aria-hidden="true">01</span>The night</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        One room, <span class="cc-sc">three businesses</span>.
                    </h2>
                    <p class="cc-lead" data-reveal style="--reveal-delay: 0.16s;">
                        Tuesday is free and full. Thursday costs twelve. The weekend pays for the
                        week. They are different products in one space, and each is a single
                        recurring event you set up once.
                    </p>

                    <ul class="cc-points" data-reveal style="--reveal-delay: 0.24s;">
                        @foreach ([
                            ['One night, one event', 'Pick the day and the hour. Change the time once and every week after it follows.'],
                            ['Dark weeks come out', 'A date exception drops the week you are closed without disturbing the rest of the run.'],
                            ['Keep them apart', 'Sub-schedules put the open mic, the showcase and the weekend on their own strands of the same link.'],
                        ] as [$t, $d])
                            <li>
                                <span><span class="cc-pt-t">{{ $t }}</span> <span>- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="cc-terms" data-reveal style="--reveal-delay: 0.3s;">
                        <span class="cc-tag">Free</span>
                        <span>Recurring nights, date exceptions and sub-schedules are on the free plan.</span>
                    </p>
                </div>

                <div class="cc-guide" data-reveal="right">
                    <div class="cc-guide-head">
                        <h3>The week</h3>
                        <span>{{ count($nights) }} recurring nights</span>
                    </div>

                    <div>
                        @foreach ($nights as [$nDay, $nName, $nKind, $nAdv, $nDoor, $nNote])
                            <div class="cc-guide-row">
                                <span class="cc-guide-day" style="--bg: {{ $ccInks[$loop->index % 4][0] }}; --fg: {{ $ccInks[$loop->index % 4][1] }};">{{ Str::substr($nDay, 0, 3) }}</span>
                                <div class="cc-guide-what">
                                    <strong>{{ $nName }}</strong>
                                    <small>{{ $nNote }}</small>
                                </div>
                                <span class="cc-guide-dots" aria-hidden="true"></span>
                                <span @class(['cc-guide-fee', 'cc-guide-fee-free' => $nKind === 'free'])>@if ($nKind === 'free') Free @else ${{ $nAdv }} @endif</span>
                            </div>
                        @endforeach
                    </div>

                    <p class="cc-guide-foot">
                        The open mic takes free registrations up to its capacity. The rest sell tickets.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The names come later (02)                                 -->
        <!-- ============================================================ -->
        <section id="names" class="cc-sec">
            <div class="cc-shapes" aria-hidden="true">
                <i class="cc-atom" style="left: 7%; top: 14%; --w: 3rem; --c: var(--cc-orange);"></i>
                <i class="cc-atom" style="right: 9%; top: 22%; --w: 2.2rem; --c: var(--cc-teal);"></i>
            </div>
            <div class="cc-wrap" style="position: relative;">
                <div class="cc-center">
                    <p class="cc-kick" data-reveal><span class="cc-no" aria-hidden="true">02</span>The names</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Your bill is also <span class="cc-sc">six other calendars</span>.
                    </h2>
                    <p class="cc-lead" data-reveal style="--reveal-delay: 0.16s;">
                        Add the comics to the show as participants. For everyone who already runs a
                        schedule here, your Friday reaches their page too, for them to accept.
                    </p>
                </div>

                <!-- Six sets, all tuned to your Friday -->
                <div class="cc-sets" aria-hidden="true" data-reveal-group="70">
                    @for ($ccSet = 0; $ccSet < 6; $ccSet++)
                        <span class="cc-set" data-reveal="zoom" style="--bg: {{ $ccInks[$ccSet % 3][0] }}; --fg: {{ $ccInks[$ccSet % 3][1] }};">Fri 8</span>
                    @endfor
                </div>

                <div class="cc-tents" data-reveal-group="120">
                    @foreach ([
                        ['They already have a schedule', 'The date arrives on their own page for them to accept, or immediately if they have put your club on their approved list. Nobody is retyping your booking.'],
                        ['They do not have one yet', 'Adding them creates a public page that says your club made it. They claim it by signing in with the email you entered, it stays out of search engines until they do, and your dates stay on it.'],
                        ['You enter it once', 'The bill lives on the show, and the night\'s page lists every comic on it, account or not. Change it on Wednesday and every place it appears changes with it.'],
                    ] as [$t, $d])
                        <div data-reveal>
                            <div class="cc-tent">
                                <div class="cc-tent-band" aria-hidden="true" style="--bg: {{ $ccInks[$loop->index][0] }}; --fg: {{ $ccInks[$loop->index][1] }};"><span>Table {{ $loop->iteration }}</span><i></i></div>
                                <h3>{{ $t }}</h3>
                                <p>{{ $d }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="cc-tabletop" aria-hidden="true"></div>

                <div class="cc-center">
                    <p class="cc-terms" data-reveal>
                        <span class="cc-tag">Free</span>
                        <span>Participants and the pages they <x-link href="{{ marketing_url('/docs/creating-events#claim') }}">claim</x-link> are on the free plan, and the comics do not pay for anything either.</span>
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. Comics ask you (03)                                       -->
        <!-- ============================================================ -->
        <section id="asks" class="cc-sec cc-teal">
            <div class="cc-shapes" aria-hidden="true">
                <i class="cc-star12 cc-spin" style="left: -4rem; bottom: -4rem; --w: 15rem; --c: rgba(244, 185, 66, 0.22); --dur: 140s;"></i>
                <i class="cc-boom cc-drift" style="right: -9rem; top: -10rem; --w: 26rem; --t: 5rem; --r: 160deg; --c: rgba(246, 239, 224, 0.1); --d: 26px;"></i>
            </div>
            <div class="cc-wrap cc-two cc-two-rev" style="position: relative;">
                <div>
                    <p class="cc-kick" data-reveal><span class="cc-no" aria-hidden="true">03</span>Booking</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Stop booking the room <span class="cc-sc">out of your inbox</span>.
                    </h2>
                    <p class="cc-lead" data-reveal style="--reveal-delay: 0.16s;">
                        Every comic in the city wants a spot, and right now they are asking you in
                        four different apps. Turn on booking requests and the asks arrive in one
                        place, already attached to the date they are for.
                    </p>

                    <ul class="cc-asks" data-reveal-group="90">
                        @foreach ([
                            ['Nothing posts without you', 'Every request waits for you to accept it, so the public calendar only ever shows the bill you agreed to.'],
                            ['You are told they are waiting', 'New pending requests are emailed to the club, so a spot request does not rot in a notification tab.'],
                            ['Regulars skip the queue', 'Put the comics you book every month on an approved list and their submissions post straight away, as long as they send them from their own schedule.'],
                        ] as [$t, $d])
                            <li data-reveal>
                                <h3>{{ $t }}</h3>
                                <p>{{ $d }}</p>
                            </li>
                        @endforeach
                    </ul>

                    <p class="cc-terms" data-reveal>
                        <span class="cc-tag">Free</span>
                        <span>Booking requests and the approved list cost nothing.</span>
                    </p>
                </div>
                <div data-reveal="left">
                    <div class="cc-pad">
                        <div class="cc-pad-head">
                            <h3>Waiting for you</h3>
                            <span>3 requests</span>
                        </div>

                        <div class="cc-slips">
                            @foreach ([
                                ['Thu 12 Mar', 'Showcase spot', 'Pending'],
                                ['Thu 19 Mar', 'Showcase spot', 'Pending'],
                                ['Tue 24 Mar', 'Open mic', 'Posted'],
                            ] as [$rWhen, $rWhat, $rState])
                                <div class="cc-slip">
                                    <span class="cc-slip-when">{{ $rWhen }}</span>
                                    <span class="cc-slip-what">{{ $rWhat }}</span>
                                    <span @class(['cc-stamp', 'cc-stamp-ok' => $rState === 'Posted'])>{{ $rState }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="cc-pad-note">
                            The third posted itself: that comic is on the club's approved list.
                        </p>
                    </div>
                </div>

            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Selling the room (04)                                     -->
        <!-- ============================================================ -->
        <section id="selling" class="cc-sec cc-alt">
            <div class="cc-wrap">
                <div class="cc-center">
                    <p class="cc-kick" data-reveal><span class="cc-no" aria-hidden="true">04</span>Selling the room</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Sold out on Friday. <span class="cc-sc">Still open for next Friday</span>.
                    </h2>
                    <p class="cc-lead" data-reveal style="--reveal-delay: 0.16s;">
                        Every ticket type carries its own price and its own count, and the count is kept
                        per night - so selling out one week leaves the next one untouched.
                    </p>
                </div>

                <div class="cc-sell">
                    <div class="cc-bill" data-reveal="left">
                        <div class="cc-leaves" aria-hidden="true">
                            <span class="cc-leaf"><b>Fri</b><strong>13</strong><em>Sold out</em></span>
                            <span class="cc-leaf"><b>Fri</b><strong>20</strong><em>On sale</em></span>
                        </div>
                        <div class="cc-bill-in">
                            <span class="cc-bill-kick" aria-hidden="true">Tonight's bill</span>
                            <div class="cc-bill-head">
                                <h3>Friday, 8:00pm</h3>
                                <span class="cc-tag">Free</span>
                            </div>
                            <p class="cc-bill-sub">Three ticket types on one recurring night.</p>

                            <ul class="cc-fare">
                                @foreach ([
                                    ['Advance', '$18', '80'],
                                    ['Door', '$22', '40'],
                                    ['Group of six', '$90', '10'],
                                ] as [$tName, $tPrice, $tQty])
                                    <li>
                                        <span class="cc-fare-name">{{ $tName }}</span>
                                        <span class="cc-fare-dots" aria-hidden="true"></span>
                                        <span class="cc-fare-qty">{{ $tQty }} a night</span>
                                        <span class="cc-fare-fig">{{ $tPrice }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <p class="cc-bill-note">
                                A ticket type can also be given a date to go on sale or come off it, once, for the whole run. With the "Notify me" card switched on, anyone who joins the <x-link href="{{ marketing_url('/docs/tickets#interest-list') }}">interest list</x-link> on the event page before sales open gets one email when they do.
                            </p>
                        </div>
                    </div>

                    <div class="cc-perks" data-reveal-group="110">
                        @foreach ([
                            ['QR check-in', 'Scan the QR on each ticket at the door from a phone, on every plan. Each ticket admits once, and a second scan warns. The live check-in dashboard, with a running count, is the Pro part.', false],
                            ['Zero platform fees', 'You keep the ticket price minus what Stripe or PayPal charges to process the payment, on every plan. Nothing is taken on top of that.', false],
                            ['The open mic stays free', 'A free night takes registrations up to a capacity instead of tickets, so you know the count without charging anybody, and its waitlist is free as well.', false],
                        ] as [$t, $d, $isPro])
                            <div class="cc-perk" data-reveal style="--c: {{ $ccInks[$loop->index][0] }};">
                                <div class="cc-perk-head">
                                    <h3>{{ $t }}</h3>
                                    <span @class(['cc-tag', 'cc-tag-pro' => $isPro])>{{ $isPro ? 'Pro' : 'Free' }}</span>
                                </div>
                                <p>{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="cc-center">
                    <p class="cc-terms" data-reveal>
                        <span class="cc-tag cc-tag-pro">Pro</span>
                        <span>A ticket with a price on it is the Pro plan, at {{ plan_price($proMonthly) }} a month, taken through your own Stripe or <x-link href="{{ marketing_url('/paypal') }}">PayPal</x-link> account, a payment link or cash. A free open mic needs none of it: registration with a capacity is on every plan.</span>
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Who it is for (05): six books of matches                  -->
        <!-- ============================================================ -->
        <section id="who" class="cc-sec">
            <div class="cc-shapes" aria-hidden="true">
                <i class="cc-boom cc-drift cc-wide" style="left: -10rem; top: 4rem; --w: 24rem; --t: 4.6rem; --r: -60deg; --c: var(--cc-mustard); --d: 34px; opacity: 0.55;"></i>
                <i class="cc-atom" style="right: 6%; top: 10%; --w: 3.2rem; --c: var(--cc-teal);"></i>
            </div>
            <div class="cc-wrap" style="position: relative;">
                <div class="cc-center">
                    <p class="cc-kick" data-reveal><span class="cc-no" aria-hidden="true">05</span>Who it is for</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Any room with <span class="cc-sc">a mic and a door</span>.
                    </h2>
                </div>

                @php
                    $ccRooms = [
                        ['Stand-Up Clubs', 'Two shows on a Friday and two on a Saturday, each its own event with its own count on the door.', 'for-stand-up-comedy-clubs'],
                        ['Sketch Comedy Venues', 'A show that runs for a season, set up once with a closing night rather than entered week by week.', 'for-sketch-comedy-venues'],
                        ['Improv Theaters', 'House teams on a fixed weekly slot, each team a participant so the date reaches everyone in it.', 'for-improv-theaters'],
                        ['Open Mic Venues', 'Free to get in and full every week. Registrations up to a capacity, so you know the count without taking money.', 'for-open-mic-comedy-venues'],
                        ['Live Podcast Studios', 'A recording with an audience is still a ticketed night, and the guests go on as participants.', 'for-live-podcast-studios'],
                        ['Comedy Bars & Restaurants', 'Comedy on some nights and not others. Sub-schedules keep the comedy strand apart from everything else the room does.', 'for-comedy-bars-restaurants'],
                    ];
                @endphp

                <div class="cc-books" data-reveal-group="110">
                    @foreach ($ccRooms as [$ccRoom, $ccRoomDesc, $ccRoomSlug])
                        @php $ccPost = get_sub_audience_blog($ccRoomSlug); @endphp
                        <article class="cc-book" data-reveal style="--bg: {{ $ccInks[$loop->index % 4][0] }}; --fg: {{ $ccInks[$loop->index % 4][1] }};">
                            <div class="cc-book-flap">
                                <div class="cc-book-out" aria-hidden="true"><i></i></div>
                                <div class="cc-book-in"><h3>{{ $ccRoom }}</h3></div>
                            </div>
                            <div class="cc-comb" aria-hidden="true">@for ($ccMatch = 0; $ccMatch < 9; $ccMatch++)<i></i>@endfor</div>
                            <div class="cc-book-body">
                                <p>{{ $ccRoomDesc }}</p>
                                @if ($ccPost)
                                    <a href="{{ blog_url('/' . $ccPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $ccRoom }}">
                                        <span>Learn more {!! $ccArrow !!}</span>
                                    </a>
                                @endif
                            </div>
                            <div class="cc-strike" aria-hidden="true"><span>Close cover before striking</span></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. How it works (06): three cue cards                        -->
        <!-- ============================================================ -->
        <section id="how" class="cc-sec cc-studio">
            <div class="cc-shapes" aria-hidden="true">
                <i class="cc-atom" style="left: 8%; top: 16%; --w: 2.6rem; --c: #f4b942;"></i>
                <i class="cc-atom" style="right: 10%; bottom: 14%; --w: 3.4rem; --c: #2ba59f;"></i>
                <i class="cc-atom" style="right: 22%; top: 12%; --w: 1.6rem; --c: #ee6c2a;"></i>
            </div>
            <div class="cc-wrap" style="position: relative;">
                <div class="cc-center">
                    <p class="cc-kick" data-reveal><span class="cc-no" aria-hidden="true">06</span>How it works</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Set the week up once, <span class="cc-sc">book it forever</span>.
                    </h2>
                </div>

                <div class="cc-cues" data-reveal-group="140">
                    @foreach ([
                        ['01', 'Put the nights up', 'One recurring event per night, with the weeks you are dark taken out. The open mic takes registrations; the rest take tickets.'],
                        ['02', 'Open the requests', 'Comics submit for a date instead of messaging you, and the regulars go on the approved list so they skip the queue.'],
                        ['03', 'Add the bill on Wednesday', 'Participants go on the show, and the date reaches every comic who runs a schedule of their own.'],
                    ] as [$n, $t, $d])
                        <div data-reveal>
                            <div class="cc-cue">
                                <div class="cc-cue-no" style="--bg: {{ $ccInks[$loop->index][0] }}; --fg: {{ $ccInks[$loop->index][1] }};">{{ $n }}</div>
                                <h3>{{ $t }}</h3>
                                <p>{{ $d }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Key features: the channel selector                        -->
        <!-- ============================================================ -->
        <section class="cc-sec">
            <div class="cc-wrap cc-chans">
                <div>
                    <p class="cc-kick" data-reveal aria-hidden="true">Also on this set</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                    <p class="cc-chans-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="cc-go">
                            See all features
                            {!! $ccArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $ccChannels = [
                        ['2', 'Recurring Events', 'A weekly night set up once, with the dark weeks taken out', marketing_url('/features/recurring-events')],
                        ['4', 'Ticketing', 'Advance and door pricing, QR check-in, and zero platform fees', marketing_url('/features/ticketing')],
                        ['7', 'Sub-schedules', 'Keep the open mic, the showcase and the weekend apart', marketing_url('/features/sub-schedules')],
                        ['9', 'Newsletters', 'Email the people who follow the room, with open rates', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <div class="cc-chan-grid" data-reveal-group="80">
                    @foreach ($ccChannels as [$ccChNo, $ccChName, $ccChDesc, $ccChUrl])
                        <a href="{{ $ccChUrl }}" class="cc-chan" data-reveal>
                            <span class="cc-chan-no" aria-hidden="true" style="--bg: {{ $ccInks[$loop->index][0] }}; --fg: {{ $ccInks[$loop->index][1] }};">{{ $ccChNo }}</span>
                            <span>
                                <strong>{{ $ccChName }}</strong>
                                <small>{{ $ccChDesc }}</small>
                            </span>
                            {!! $ccArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="cc-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 9. Related pages: four more sets                             -->
        <!-- ============================================================ -->
        <section class="cc-sec">
            <div class="cc-wrap">
                <div class="cc-also-head">
                    <div>
                        <p class="cc-kick" data-reveal aria-hidden="true">Coming up next</p>
                        <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="cc-go" data-reveal>
                        See all use cases
                        {!! $ccArrow !!}
                    </a>
                </div>

                <div class="cc-also" data-reveal-group="80">
                    @foreach ([
                        ['/for-comedians', 'Comedians'],
                        ['/for-music-venues', 'Music Venues'],
                        ['/for-bars', 'Bars'],
                        ['/for-spoken-word', 'Spoken Word Artists'],
                    ] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal>
                            <span class="cc-also-screen" style="--bg: {{ $ccInks[$loop->index][0] }}; --fg: {{ $ccInks[$loop->index][1] }};">
                                <small>Event Schedule for</small>
                                <strong>{{ $relName }}</strong>
                            </span>
                            <span class="cc-also-dials" aria-hidden="true"><i></i><i></i></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="cc-standby" aria-hidden="true">
            <i style="--c: #1d7874;"></i><i style="--c: #f4b942;"></i><i style="--c: #ee6c2a;"></i><i style="--c: #f6efe0;"></i><i style="--c: #2b2a28;"></i><i style="--c: #f4b942;"></i><i style="--c: #1d7874;"></i>
            <span class="cc-standby-card">Please<br>stand by</span>
        </div>

        <!-- ============================================================ -->
        <!-- 10. FAQ (07)                                                 -->
        <!-- ============================================================ -->
        <section id="faq" class="cc-sec cc-alt">
            <div class="cc-wrap cc-faq">
                <div class="cc-faq-head">
                    <p class="cc-kick" data-reveal><span class="cc-no" aria-hidden="true">07</span>Questions</p>
                    <h2 class="cc-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Asked <span class="cc-sc">at the box office</span>.
                    </h2>
                    <div class="cc-window" aria-hidden="true" data-reveal style="--reveal-delay: 0.16s;">
                        <div class="cc-window-pane"></div>
                        <div class="cc-window-sill">Box office</div>
                    </div>
                </div>

                <div class="cc-qa" data-reveal>
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
        <!-- 11. Finale: the sign comes on                                -->
        <!-- ============================================================ -->
        <section id="claim" class="cc-fin">
            <div class="cc-fin-rays" aria-hidden="true"></div>
            <div class="cc-wrap cc-fin-in">
                <div class="cc-onair" aria-hidden="true" data-reveal="zoom">
                    <span class="cc-wave">@for ($ccBar = 0; $ccBar < 9; $ccBar++)<i style="--i: {{ $ccBar }}; --h: {{ [0.5, 0.8, 0.35, 1, 0.6, 0.9, 0.4, 0.7, 0.55][$ccBar] }};"></i>@endfor</span>
                    <span class="cc-sign" id="cc-sign"><span>Applause</span></span>
                    <span class="cc-wave">@for ($ccBar = 0; $ccBar < 9; $ccBar++)<i style="--i: {{ 8 - $ccBar }}; --h: {{ [0.55, 0.7, 0.4, 0.9, 0.6, 1, 0.35, 0.8, 0.5][$ccBar] }};"></i>@endfor</span>
                </div>

                <p class="cc-fin-kick" data-reveal>Free forever</p>
                <h2 class="cc-fin-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Put <span class="cc-sc">Friday at eight</span> on sale.
                </h2>
                <p class="cc-fin-sub" data-reveal style="--reveal-delay: 0.16s;">
                    The night, the tickets and the bill, on one link that has not needed
                    replacing since January.
                </p>

                <div class="cc-claim-card" data-reveal="panel">
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div class="cc-claim-row">
                        <div dir="ltr" class="es-claim cc-claim">
                            <input id="es-claim-input" type="text" placeholder="your-club" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="cc-btn">
                            Put the room online
                            {!! $ccArrow !!}
                        </a>
                    </div>
                    <p class="cc-claim-note">No credit card required</p>
                </div>
            </div>
        </section>

        <div class="cc-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- When the sign comes on the room answers: confetti in the room's own colours. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var sign = document.getElementById('cc-sign');
            if (!sign || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var inks = ['#1d7874', '#f4b942', '#f6efe0', '#2b2a28'];
                    // An instance of our own, drawn on the page's thread. The library's default
                    // cannon draws through a worker built from a blob, which the site's
                    // content security policy refuses without an error, so nothing was drawn.
                    var fire = window.confetti.create(null, { resize: true });
                    [[60, 0.08], [120, 0.92]].forEach(function (shot) {
                        fire({ particleCount: 60, angle: shot[0], spread: 55, startVelocity: 50, origin: { x: shot[1], y: 0.95 }, colors: inks, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.9 });
            io.observe(sign);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
