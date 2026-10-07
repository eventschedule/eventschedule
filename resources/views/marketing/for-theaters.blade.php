<x-marketing-layout>
    <x-slot name="title">Theater Calendars | Runs, Season Passes and Ticketing</x-slot>
    <x-slot name="description">Set a production up once as a run - Tuesday to Sunday, dark Mondays, closing after fourteen performances - and sell every one from a single link.</x-slot>
    <x-slot name="breadcrumbTitle">For Theaters</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Bodoni Moda') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Crimson Pro') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Theaters"
        description="Set a production up once as a run with a day-of-week pattern, dark days and a closing performance, then sell the whole run from one link with zero platform fees."
        audience="Theaters"
        keywords="theater calendar, show run scheduling, season pass, theater ticketing, performance dates, matinee scheduling" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a theatrical run online with Event Schedule",
        "description": "Set the run up once and sell the whole thing from one link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the run",
                "text": "Create the production as a recurring event, pick the days it plays, and give it an end: a closing date, or a number of performances."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Mark the dark days",
                "text": "Add date exceptions for the nights you are dark, and add the matinee as its own event on the days it plays."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Sell the run",
                "text": "Add named ticket types for each price, and a season pass valid for every performance of the run, once each."
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
           For-theaters "The Program" styles. The page is the programme
           you are handed at the door, and the house it was printed for:
           a proscenium whose curtains part on the title, the dates of
           performance, a season brochure, a seating plan, an interval,
           the front of house, and the curtain again at the end.

           Everything is scoped under #th. The data the pictures show is
           derived in the PHP block below exactly as it always was: the
           run strip from $runDays and $matineeDays, the season bars
           from $season. Only the print changed.

           BLADE RULE for this block: no hex colour inside the brackets
           of an at-rule condition, and no Blade echo anywhere in it.
           ============================================================== */

        @property --th-fold {
            syntax: '<length>';
            inherits: false;
            initial-value: 34px;
        }

        #th {
            --th-paper: #f6efe0;
            --th-paper-2: #efe5d0;
            --th-card: #fbf6ea;
            --th-ink: #1c1512;
            --th-ink-2: #4a3f37;
            --th-ink-3: #6b5d51;
            --th-line: rgba(28, 21, 18, 0.24);
            --th-hair: rgba(28, 21, 18, 0.12);
            --th-accent: #8e1b1f;
            --th-gold: #b08a3c;
            --th-bronze: #7a5a1c;
            --th-seat: #dccfb4;
            --th-seat-edge: rgba(28, 21, 18, 0.2);
            --th-sold: #8e1b1f;
            --th-display: 'Bodoni Moda', 'Bodoni 72', Didot, 'Playfair Display', Georgia, serif;
            --th-text: 'Crimson Pro', 'Iowan Old Style', 'Palatino Linotype', Palatino, Georgia, serif;
            --th-ital: Georgia, 'Times New Roman', serif;
            position: relative;
            background-color: var(--th-paper);
            /* The laid lines are one 4px tile, repeated. Written as a repeating gradient the
               height of the whole page, Safari draws them as broad grey bands. */
            background-image: linear-gradient(0deg, rgba(28, 21, 18, 0.022) 1px, transparent 1px);
            background-size: 100% 4px;
            color: var(--th-ink);
            font-family: var(--th-text);
            font-size: 1.1875rem;
            line-height: 1.5;
        }
        .dark #th {
            --th-paper: #120d0c;
            --th-paper-2: #19110f;
            --th-card: #1f1614;
            --th-ink: #f3e9d6;
            --th-ink-2: #d2c4ae;
            --th-ink-3: #a89886;
            --th-line: rgba(243, 233, 214, 0.26);
            --th-hair: rgba(243, 233, 214, 0.12);
            --th-accent: #d9b25f;
            --th-gold: #d9b25f;
            --th-bronze: #d9b25f;
            --th-seat: #3b2d28;
            --th-seat-edge: rgba(243, 233, 214, 0.16);
            --th-sold: #a8262b;
            background-image: linear-gradient(0deg, rgba(243, 233, 214, 0.016) 1px, transparent 1px);
        }
        /* Two fixed grounds that keep their own ink in either mode: anything printed on
           cream card under the lights, and anything lettered on the velvet itself. */
        #th .th-lit {
            --th-paper: #f6efe0;
            --th-card: #fbf6ea;
            --th-ink: #1c1512;
            --th-ink-2: #4a3f37;
            --th-ink-3: #6b5d51;
            --th-line: rgba(28, 21, 18, 0.24);
            --th-hair: rgba(28, 21, 18, 0.12);
            --th-accent: #8e1b1f;
            --th-gold: #b08a3c;
            --th-bronze: #7a5a1c;
            color: #1c1512;
        }
        #th .th-velvet {
            --th-ink: #f6efe0;
            --th-ink-2: #efdfc4;
            --th-ink-3: #e6cfa8;
            --th-line: rgba(246, 239, 224, 0.34);
            --th-hair: rgba(246, 239, 224, 0.2);
            --th-accent: #ecd08a;
            --th-gold: #e6c777;
            --th-bronze: #ecd08a;
            color: #f6efe0;
        }

        /* The bar above takes the programme's stock. */
        body > header.sticky {
            background-color: rgba(246, 239, 224, 0.9);
            border-bottom-color: rgba(28, 21, 18, 0.14);
        }
        .dark body > header.sticky {
            background-color: rgba(18, 13, 12, 0.9);
            border-bottom-color: rgba(243, 233, 214, 0.14);
        }

        #th ::selection { background: #8e1b1f; color: #f6efe0; }
        #th a:focus-visible,
        #th summary:focus-visible,
        #th input:focus-visible {
            outline: 2px solid var(--th-accent);
            outline-offset: 4px;
        }

        .th-wrap { width: min(100% - 2.5rem, 70rem); margin-inline: auto; }
        .th-section { padding-block: clamp(4.25rem, 8.5vw, 7.5rem); }
        .th-alt { background-color: var(--th-paper-2); }

        /* Voices: the Didone of the title page, letterspaced capitals, and a true italic. */
        .th-d { font-family: var(--th-display); font-weight: 400; line-height: 1.04; letter-spacing: -0.012em; }
        .th-caps { font-family: var(--th-text); font-weight: 700; font-size: 0.8125rem; letter-spacing: 0.22em; text-transform: uppercase; line-height: 1.35; }
        .th-ital { font-family: var(--th-ital); font-style: italic; }

        .th-head { max-width: 47rem; margin-inline: auto; text-align: center; }
        .th-kicker { color: var(--th-accent); }
        .th-h2 { margin-top: 1rem; font-size: clamp(2.3rem, 5.2vw, 4.1rem); text-wrap: balance; }
        .th-h2 em { font-style: normal; color: var(--th-accent); }
        .th-sub { color: var(--th-ink-2); font-size: clamp(1.2rem, 1.7vw, 1.375rem); text-wrap: pretty; }
        .th-orn {
            display: flex;
            align-items: center;
            gap: 1rem;
            max-width: 17rem;
            margin: 1.5rem auto;
            color: var(--th-gold);
            font-size: 1.05rem;
            line-height: 1;
            font-variant-emoji: text;
        }
        .th-orn::before,
        .th-orn::after { content: ""; flex: 1; height: 1px; background: currentColor; }

        .th-tier {
            display: inline-block;
            padding: 0.2rem 0.55rem 0.12rem;
            border: 1px solid var(--th-line);
            color: var(--th-ink-3);
            font-size: 0.6875rem;
            letter-spacing: 0.2em;
            vertical-align: middle;
            white-space: nowrap;
        }
        .th-tier-pro { background: var(--th-accent); border-color: var(--th-accent); color: var(--th-paper); }

        .th-a { color: var(--th-ink); font-weight: 700; text-decoration: underline; text-decoration-color: var(--th-gold); text-decoration-thickness: 1.5px; text-underline-offset: 0.2em; transition: color 0.2s ease; }
        .th-a:hover { color: var(--th-accent); }

        .th-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1.1rem 1.9rem 1rem;
            background: #8e1b1f;
            color: #f6efe0;
            font-family: var(--th-text);
            font-weight: 700;
            font-size: 0.9375rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            line-height: 1.2;
            text-align: center;
            box-shadow: inset 0 0 0 4px #8e1b1f, inset 0 0 0 5px rgba(230, 199, 119, 0.85), 0 0.9rem 1.6rem -1rem rgba(60, 9, 12, 0.9);
            transition: background-color 0.2s ease, translate 0.2s ease, box-shadow 0.2s ease;
        }
        .th-btn:hover { background: #a52429; translate: 0 -2px; box-shadow: inset 0 0 0 4px #a52429, inset 0 0 0 5px rgba(230, 199, 119, 0.95), 0 1.3rem 1.8rem -1rem rgba(60, 9, 12, 0.9); }
        .th-btn svg { width: 1.1rem; height: 1.1rem; flex: none; transition: translate 0.2s ease; }
        .th-btn:hover svg { translate: 3px 0; }
        .th-more {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: var(--th-ink);
            /* the top padding and its negative margin make the link 24px tall without moving it */
            padding-top: 0.2rem;
            margin-top: -0.2rem;
            padding-bottom: 0.2rem;
            border-bottom: 1px solid var(--th-gold);
            transition: gap 0.2s ease, color 0.2s ease;
        }
        .th-more:hover { gap: 0.9rem; color: var(--th-accent); }
        .th-more svg { width: 1rem; height: 1rem; }

        /* A page of the programme: card stock with a ruled border set in from the edge. */
        .th-leaf {
            position: relative;
            background: var(--th-card);
            box-shadow: inset 0 0 0 1px var(--th-line), inset 0 0 0 7px var(--th-card), inset 0 0 0 8px var(--th-hair), 0 1.6rem 2.6rem -1.8rem rgba(28, 21, 18, 0.55);
        }
        .dark .th-leaf { box-shadow: inset 0 0 0 1px var(--th-line), inset 0 0 0 7px var(--th-card), inset 0 0 0 8px var(--th-hair), 0 1.6rem 2.6rem -1.8rem #000; }

        /* ---------------------------------------------------------------
           Contents rail (wide screens only)
           --------------------------------------------------------------- */
        /* From 1536px, where the margin beside the proscenium has room for a label (at 1512 the
           label printed across the gold frame). The nav is a box the size of the page that clips
           the rail, so the rail stays fixed to the screen and still ends where the page does
           instead of riding over the site footer. */
        .th-rail { display: none; }
        @media (min-width: 1536px) {
            .th-rail { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .th-rail ol { position: fixed; right: 1.5rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.2rem; justify-items: end; pointer-events: auto; }
            /* By day the rail is printed in the programme's ink for its paper, and its red is the
               red of the velvet: the velvet band, the dark house and the closing curtain pass
               over it. By night its gold reads on all three, so it stays in front. */
            html:not(.dark) #th .th-band,
            html:not(.dark) #th .th-foh,
            html:not(.dark) #th .th-finale { z-index: 41; }
            .th-rail a { display: flex; align-items: center; gap: 0.7rem; padding: 0.3rem 0; color: var(--th-ink-3); }
            .th-rail a::after { content: ""; width: 0.45rem; height: 0.45rem; rotate: 45deg; border: 1px solid currentColor; transition: background-color 0.3s ease, scale 0.3s ease; }
            .th-rail a span { opacity: 0; translate: 0.3rem 0; font-size: 0.6875rem; transition: opacity 0.25s ease, translate 0.25s ease; }
            .th-rail a:hover span,
            .th-rail a:focus-visible span,
            .th-rail a.is-active span { opacity: 1; translate: 0 0; }
            .th-rail a.is-active { color: var(--th-accent); }
            .th-rail a.is-active::after { background: currentColor; scale: 1.25; }
        }

        /* ---------------------------------------------------------------
           The house: proscenium, valance, drapes, footlights
           --------------------------------------------------------------- */
        .th-stage { padding-top: clamp(0.9rem, 2.2vw, 1.75rem); }
        .th-pros {
            position: relative;
            width: min(100% - 1.5rem, 82rem);
            margin-inline: auto;
            padding: clamp(0.3rem, 0.7vw, 0.6rem) clamp(0.3rem, 0.7vw, 0.6rem) 0;
            background: linear-gradient(180deg, #ecd08a 0%, #b08a3c 16%, #8a6a25 50%, #b08a3c 84%, #dcbb6c 100%);
            box-shadow: 0 2.4rem 3.4rem -2.4rem rgba(28, 21, 18, 0.75);
        }
        .dark .th-pros { box-shadow: 0 2.4rem 3.4rem -2.2rem #000; }
        .th-opening {
            --th-drape: clamp(2.4rem, 15.5%, 12.5rem);
            position: relative;
            overflow: hidden;
            background-color: #f6efe0;
            background-image: radial-gradient(ellipse 70% 62% at 50% 40%, #fffaf0 0%, #f6efe0 52%, #e6d8bb 100%);
        }
        .dark .th-opening {
            background-color: #0d0908;
            background-image:
                conic-gradient(from 166deg at 50% -6%, rgba(255, 226, 170, 0) 0deg, rgba(255, 226, 170, 0.13) 7deg, rgba(255, 226, 170, 0.13) 21deg, rgba(255, 226, 170, 0) 28deg),
                radial-gradient(ellipse 52% 46% at 50% 44%, rgba(217, 178, 95, 0.2) 0%, rgba(217, 178, 95, 0.05) 55%, rgba(217, 178, 95, 0) 76%);
        }
        @media (max-width: 640px) { .th-opening { --th-drape: 1.5rem; } }

        .th-pelmet {
            position: absolute;
            inset: 0 0 auto 0;
            height: 0.8rem;
            z-index: 4;
            background: linear-gradient(180deg, #4a0c0f, #7a1519);
            border-bottom: 2px solid #d9b25f;
            box-shadow: 0 0.5rem 0.9rem rgba(0, 0, 0, 0.35);
        }
        /* Swags: one half-ellipse a tile, cut by a mask, with a gold edge showing beneath. */
        .th-valance {
            --th-sw: clamp(4.4rem, 11.2%, 10.5rem);
            position: absolute;
            inset: 0.6rem 0 auto 0;
            height: clamp(3rem, 6.2vw, 5.2rem);
            z-index: 3;
            filter: drop-shadow(0 0.5rem 0.5rem rgba(0, 0, 0, 0.35));
        }
        .th-valance::before,
        .th-valance::after {
            content: "";
            position: absolute;
            inset: 0;
            -webkit-mask: radial-gradient(ellipse 50% 100% at 50% 0, #000 98.5%, transparent 100%) 50% 0 / var(--th-sw) 100% repeat-x;
            mask: radial-gradient(ellipse 50% 100% at 50% 0, #000 98.5%, transparent 100%) 50% 0 / var(--th-sw) 100% repeat-x;
        }
        .th-valance::before { background: linear-gradient(180deg, #ecd08a, #9a7628); }
        .th-valance::after {
            bottom: 0.32rem;
            background:
                repeating-radial-gradient(ellipse 62% 118% at 50% -6%, rgba(0, 0, 0, 0) 0 15%, rgba(30, 0, 0, 0.26) 16% 18.5%, rgba(0, 0, 0, 0) 19.5% 31%) 50% 0 / var(--th-sw) 100% repeat-x,
                radial-gradient(ellipse 52% 100% at 50% 0, #b3262b 0%, #8e1b1f 52%, #5c0f13 100%) 50% 0 / var(--th-sw) 100% repeat-x;
        }

        .th-drape {
            --th-fold: 34px;
            position: absolute;
            top: 0;
            bottom: 0;
            width: var(--th-drape);
            z-index: 2;
            background-color: #8e1b1f;
            background-image:
                repeating-linear-gradient(90deg, #ecd08a 0 2px, #9a7628 2px 3.5px),
                linear-gradient(#d9b25f, #d9b25f),
                linear-gradient(180deg, rgba(0, 0, 0, 0.55) 0%, rgba(0, 0, 0, 0) 17%, rgba(0, 0, 0, 0) 58%, rgba(0, 0, 0, 0.34) 100%),
                linear-gradient(90deg, rgba(24, 0, 0, 0.52) 0%, rgba(0, 0, 0, 0) 26%, rgba(255, 172, 150, 0.24) 46%, rgba(0, 0, 0, 0) 68%, rgba(24, 0, 0, 0.52) 100%),
                linear-gradient(90deg, rgba(24, 0, 0, 0.24) 0%, rgba(0, 0, 0, 0) 40%, rgba(255, 172, 150, 0.1) 58%, rgba(24, 0, 0, 0.24) 100%);
            background-size: 100% 0.65rem, 100% 3px, 100% 100%, var(--th-fold) 100%, calc(var(--th-fold) * 1.7) 100%;
            background-position: 0 100%, 0 calc(100% - 0.65rem), 0 0, 0 0, 0 0;
            background-repeat: no-repeat, no-repeat, no-repeat, repeat-x, repeat-x;
        }
        @media (max-width: 640px) { .th-drape { --th-fold: 20px; } }
        .th-drape-l {
            left: 0;
            transform-origin: 0 50%;
            clip-path: polygon(0 0, 100% 0, 100% 8%, 97% 20%, 90% 32%, 78% 44%, 66% 54%, 58% 60%, 62% 66%, 70% 76%, 76% 88%, 78% 100%, 0 100%);
        }
        .th-drape-r {
            right: 0;
            transform-origin: 100% 50%;
            clip-path: polygon(100% 0, 0 0, 0 8%, 3% 20%, 10% 32%, 22% 44%, 34% 54%, 42% 60%, 38% 66%, 30% 76%, 24% 88%, 22% 100%, 100% 100%);
        }
        /* The tie-back: a gold cord where the cloth is gathered. */
        .th-drape::after {
            content: "";
            position: absolute;
            top: 58.6%;
            height: 0.6rem;
            width: 70%;
            border-radius: 99px;
            background: repeating-linear-gradient(115deg, #ecd08a 0 3px, #9a7628 3px 5.5px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.45);
        }
        .th-drape-l::after { left: -4%; rotate: -7deg; }
        .th-drape-r::after { right: -4%; rotate: 7deg; }

        /* The curtain goes up once on arrival. Open is the resting state without JS. */
        html.es-anim #th .th-drape-l { animation: th-part-l 1.9s cubic-bezier(0.66, 0, 0.18, 1) 0.2s both; }
        html.es-anim #th .th-drape-r { animation: th-part-r 1.9s cubic-bezier(0.66, 0, 0.18, 1) 0.2s both; }
        html.es-anim #th .th-drape::after { animation: th-tie 0.7s ease 1.7s both; }
        /* The folds start 62px wide. They are written out as the background-size they produce
           rather than as --th-fold: Safari stops animating width on an element as soon as any
           custom property is animated on it, and the curtains then never part there. */
        @keyframes th-part-l {
            from { width: 50.5%; background-size: 100% 0.65rem, 100% 3px, 100% 100%, 62px 100%, 105.4px 100%; clip-path: polygon(0 0, 100% 0, 100% 8%, 100% 20%, 100% 32%, 100% 44%, 100% 54%, 100% 60%, 100% 66%, 100% 76%, 100% 88%, 100% 100%, 0 100%); }
        }
        @keyframes th-part-r {
            from { width: 50.5%; background-size: 100% 0.65rem, 100% 3px, 100% 100%, 62px 100%, 105.4px 100%; clip-path: polygon(100% 0, 0 0, 0 8%, 0 20%, 0 32%, 0 44%, 0 54%, 0 60%, 0 66%, 0 76%, 0 88%, 0 100%, 100% 100%); }
        }
        @keyframes th-tie { from { opacity: 0; } }
        /* And gathers a little further as the stage scrolls away. */
        @supports (animation-timeline: scroll()) {
            html.es-anim #th .th-drape-l { animation: th-part-l 1.9s cubic-bezier(0.66, 0, 0.18, 1) 0.2s both, th-gather linear both; animation-timeline: auto, scroll(root); animation-range: normal, 0px 720px; }
            html.es-anim #th .th-drape-r { animation: th-part-r 1.9s cubic-bezier(0.66, 0, 0.18, 1) 0.2s both, th-gather linear both; animation-timeline: auto, scroll(root); animation-range: normal, 0px 720px; }
        }
        @keyframes th-gather { to { scale: 0.74 1; } }

        .th-drop {
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
            min-height: clamp(31rem, 80svh, 47rem);
            padding: clamp(5.2rem, 9.5vw, 8rem) calc(var(--th-drape) + clamp(0.7rem, 2.5vw, 2.25rem)) clamp(4.4rem, 7vw, 6rem);
            text-align: center;
        }
        .th-title { width: 100%; max-width: 54rem; container-type: inline-size; }
        .th-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            margin-bottom: clamp(1.1rem, 2.2vw, 1.9rem);
            color: var(--th-accent);
            font-family: var(--th-text);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            line-height: 1.4;
        }
        .th-eyebrow::before,
        .th-eyebrow::after { content: ""; flex: none; width: clamp(0.9rem, 3vw, 2.4rem); height: 1px; background: var(--th-gold); }
        .th-h1 { font-size: clamp(2.15rem, 12.4cqi, 5.4rem); line-height: 1; text-wrap: balance; }
        .th-h1 .es-mask { padding-bottom: 0.16em; margin-bottom: -0.16em; }
        .th-h1 em { font-style: normal; font-weight: 700; color: var(--th-accent); }
        html.es-anim #th .th-h1 .es-mask .es-mask-line { animation-delay: 0.85s; }
        html.es-anim #th .th-h1 .es-mask-2 .es-mask-line { animation-delay: 1s; }
        html.es-anim #th .th-drop .es-fade-up { animation-delay: 1.15s; }
        html.es-anim #th .th-drop .es-d-1 { animation-delay: 0.7s; }
        html.es-anim #th .th-drop .es-d-3 { animation-delay: 1.3s; }
        .th-lede { max-width: 37rem; margin-inline: auto; color: var(--th-ink-2); font-size: clamp(1.15rem, 1.8vw, 1.375rem); text-wrap: pretty; }
        .th-cta { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 1.1rem 2rem; margin-top: 1.9rem; }

        .th-boards {
            position: absolute;
            inset: auto 0 0 0;
            height: clamp(1.5rem, 3vw, 2.4rem);
            z-index: 1;
            background:
                linear-gradient(180deg, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0) 70%),
                repeating-linear-gradient(90deg, #6d4b2c 0 2.6rem, #55391f 2.6rem 2.7rem);
        }
        .dark .th-boards { background: linear-gradient(180deg, rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.15) 80%), repeating-linear-gradient(90deg, #3d2a19 0 2.6rem, #2a1c10 2.6rem 2.7rem); }
        .th-foots { position: absolute; inset: auto 0 0 0; height: 5rem; z-index: 3; pointer-events: none; }
        .th-foots::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 50% 100% at 50% 100%, rgba(255, 208, 132, 0.6), rgba(255, 208, 132, 0) 72%) 50% 100% / 5.4rem 100% repeat-x;
            opacity: 0.3;
        }
        .dark .th-foots::before { opacity: 1; animation: th-glow 5s ease-in-out infinite; }
        .th-foots::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 0.75rem;
            background: radial-gradient(ellipse 50% 100% at 50% 100%, #1c120c 0 96%, rgba(0, 0, 0, 0) 100%) 50% 100% / 5.4rem 100% repeat-x;
        }
        @keyframes th-glow { 50% { opacity: 0.82; } }

        /* The apron: what plays here, lettered along the front of the stage. */
        .th-apron {
            position: relative;
            border-top: 2px solid #d9b25f;
            background: linear-gradient(180deg, #3c2517, #25170e);
            color: #ecd08a;
            padding-block: 0.8rem 0.7rem;
        }
        .th-apron .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .th-genre { display: inline-flex; align-items: center; gap: 1.5rem; padding-inline-end: 1.5rem; font-size: 0.75rem; white-space: nowrap; }
        .th-genre::after { content: "\2726\FE0E"; font-size: 0.7rem; color: #b08a3c; font-variant-emoji: text; }

        /* ---------------------------------------------------------------
           Dates of performance: the run, and the matinee beside it
           --------------------------------------------------------------- */
        .th-dates { padding-block: clamp(3.5rem, 7vw, 6rem) clamp(4rem, 8vw, 7rem); }
        .th-bill { max-width: 52rem; margin-inline: auto; padding: clamp(1.9rem, 4.4vw, 3.4rem) clamp(1.25rem, 4.4vw, 3.6rem); text-align: center; }
        .th-bill-name { margin-top: 0.8rem; font-size: clamp(2.4rem, 6vw, 4rem); }
        .th-bill-when { margin-top: 0.7rem; color: var(--th-ink-2); }
        .th-bill-line { margin-top: 0.35rem; color: var(--th-ink-2); }
        .th-cal { --th-cg: clamp(2px, 0.55vw, 6px); margin-top: 1.9rem; }
        .th-cal-row { display: grid; grid-template-columns: repeat(16, minmax(0, 1fr)); gap: var(--th-cg); }
        .th-cal-dow span { text-align: center; font-size: 0.625rem; letter-spacing: 0.08em; color: var(--th-ink-3); }
        .th-cal-num span { text-align: center; font-family: var(--th-display); font-size: clamp(0.8rem, 2.5vw, 1.35rem); line-height: 1.3; }
        .th-cal-num span.is-dark { color: var(--th-ink-3); }
        .th-cal-eve { margin-top: 0.35rem; }
        .th-eve {
            position: relative;
            height: clamp(2.4rem, 6vw, 3.3rem);
            background: linear-gradient(180deg, #a52429, #7d171b);
            box-shadow: inset 0 0 0 1px rgba(236, 208, 138, 0.55);
        }
        .th-eve::after { content: ""; position: absolute; left: 50%; top: 50%; width: 5px; height: 5px; margin: -2.5px; rotate: 45deg; background: #ecd08a; }
        .th-eve-dark {
            display: grid;
            place-items: center;
            background: none;
            box-shadow: none;
            border: 1px dashed var(--th-line);
            color: var(--th-ink-3);
            font-size: 0.5rem;
            letter-spacing: 0.16em;
            writing-mode: vertical-rl;
            text-transform: uppercase;
            overflow: hidden;
        }
        .th-eve-dark::after { display: none; }
        .th-cal-mat { margin-top: 1rem; }
        .th-mat { height: 0.95rem; border-bottom: 1px solid var(--th-hair); }
        .th-mat-on { border: 0; background: linear-gradient(180deg, #ecd08a, #b08a3c); box-shadow: inset 0 0 0 1px rgba(28, 21, 18, 0.25); }
        .th-cal-cap { margin-top: 0.5rem; color: var(--th-ink-3); font-size: 0.9375rem; }
        .th-bill-foot { margin-top: 1.7rem; padding-top: 1.3rem; border-top: 1px solid var(--th-hair); color: var(--th-ink-2); text-wrap: pretty; }
        /* The run lights up a night at a time when the bill comes into view. */
        html.es-anim #th [data-reveal] .th-eve:not(.th-eve-dark) { transition: opacity 0.4s ease calc(var(--i) * 55ms + 0.25s), translate 0.4s ease calc(var(--i) * 55ms + 0.25s); }
        html.es-anim #th [data-reveal]:not(.is-revealed) .th-eve:not(.th-eve-dark) { opacity: 0; translate: 0 0.5rem; }

        /* ---------------------------------------------------------------
           The argument: a band of velvet
           --------------------------------------------------------------- */
        .th-band {
            position: relative;
            background-color: #7d171b;
            background-image:
                linear-gradient(180deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0) 22%, rgba(0, 0, 0, 0) 78%, rgba(0, 0, 0, 0.3) 100%),
                repeating-linear-gradient(90deg, rgba(20, 0, 0, 0.2) 0, rgba(0, 0, 0, 0) 2.2rem, rgba(255, 180, 160, 0.05) 3.6rem, rgba(0, 0, 0, 0) 5rem, rgba(20, 0, 0, 0.2) 7.2rem);
            border-block: 3px double #d9b25f;
        }
        .th-trio { display: grid; margin-top: clamp(2.5rem, 5vw, 4rem); border-block: 1px solid var(--th-line); }
        @media (min-width: 820px) { .th-trio { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .th-trio > div { padding: 2.2rem 1.5rem 2.1rem; text-align: center; }
        .th-trio > div + div { border-top: 1px solid var(--th-hair); }
        @media (min-width: 820px) { .th-trio > div + div { border-top: 0; border-inline-start: 1px solid var(--th-hair); } }
        .th-trio h3 { margin-top: 0.9rem; font-size: clamp(1.5rem, 2.4vw, 1.9rem); }
        .th-trio h3 b { display: block; font-weight: 700; font-size: clamp(4.2rem, 8vw, 6.2rem); line-height: 0.9; letter-spacing: -0.03em; }
        .th-trio h3 i { display: block; font-family: var(--th-ital); font-style: italic; font-size: clamp(3.4rem, 6.4vw, 5rem); line-height: 1.12; color: var(--th-accent); }
        .th-trio p { margin: 0.8rem auto 0; max-width: 19rem; color: var(--th-ink-2); }
        .th-band-foot { margin-top: 2.2rem; text-align: center; color: var(--th-ink-2); }
        .th-band-foot a { margin-inline-start: 0.5rem; }

        /* ---------------------------------------------------------------
           Setting the run: three settings, three small diagrams
           --------------------------------------------------------------- */
        .th-set { display: grid; gap: 1.25rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 860px) { .th-set { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .th-set article { display: flex; flex-direction: column; padding: 2.1rem 1.7rem 1.9rem; text-align: center; }
        .th-set h3 { font-size: 1.7rem; }
        .th-set h3 + span { align-self: center; margin-top: 0.7rem; }
        .th-set p { margin-top: 0.9rem; color: var(--th-ink-2); }
        .th-fig { display: flex; justify-content: center; gap: 0.3rem; margin-top: auto; padding-top: 1.6rem; }
        .th-fig span {
            display: grid;
            place-items: center;
            width: 1.9rem;
            height: 2.3rem;
            border: 1px solid var(--th-line);
            color: var(--th-ink-3);
            font-size: 0.625rem;
            letter-spacing: 0.06em;
        }
        .th-fig span.is-on { background: linear-gradient(180deg, #a52429, #7d171b); border-color: #7d171b; color: #f6efe0; }
        .th-fig span.is-out { border-style: dashed; text-decoration: line-through; }
        .th-fig-end span { width: auto; padding-inline: 0.7rem; }
        .th-set-note { max-width: 43rem; margin: 2.2rem auto 0; text-align: center; color: var(--th-ink-2); text-wrap: pretty; }

        /* ---------------------------------------------------------------
           The season brochure: a real table, one bar a run
           --------------------------------------------------------------- */
        .th-season { margin-top: clamp(2.5rem, 5vw, 3.75rem); padding: clamp(1.5rem, 4vw, 3rem) clamp(1.1rem, 4vw, 3rem); }
        .th-season table { width: 100%; border-collapse: collapse; text-align: start; }
        .th-season thead th { padding-bottom: 0.8rem; color: var(--th-ink-3); text-align: start; font-size: 0.6875rem; }
        .th-season tbody th { padding: 1.2rem 0.75rem 0.3rem 0; text-align: start; vertical-align: baseline; font-family: var(--th-display); font-weight: 400; font-size: clamp(1.35rem, 3vw, 2rem); line-height: 1.1; }
        .th-season tbody th small { display: block; margin-top: 0.3rem; font-family: var(--th-text); font-weight: 700; font-size: 0.6875rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--th-bronze); }
        .th-season td { padding: 1.2rem 0.75rem 0.3rem 0; vertical-align: baseline; color: var(--th-ink-2); white-space: nowrap; }
        .th-season tr.th-rule-top > * { border-top: 1px solid var(--th-hair); }
        .th-season td.th-run-cell { padding: 0.5rem 0 1.1rem; }
        .th-season td.th-axis-cell { padding: 0 0 0.2rem; }
        .th-axis { position: relative; height: 1.3rem; color: var(--th-ink-3); font-size: 0.625rem; }
        .th-axis span { position: absolute; top: 0; padding-inline-start: 0.35rem; border-inline-start: 1px solid var(--th-line); line-height: 1.3rem; }
        .th-track { position: relative; height: 0.7rem; background: var(--th-hair); }
        .th-span { position: absolute; top: 0; bottom: 0; background: linear-gradient(180deg, #a52429, #7d171b); box-shadow: inset 0 0 0 1px rgba(236, 208, 138, 0.5); transform-origin: 0 50%; }
        .th-span-soft { background: linear-gradient(180deg, #ecd08a, #b08a3c); box-shadow: inset 0 0 0 1px rgba(28, 21, 18, 0.25); }
        html.es-anim #th [data-reveal] .th-span { transition: scale 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.3s; }
        html.es-anim #th [data-reveal]:not(.is-revealed) .th-span { scale: 0 1; }
        .th-season-note { margin-top: 1.1rem; color: var(--th-ink-2); font-size: 1.0625rem; text-wrap: pretty; }
        @media (max-width: 620px) {
            .th-season .th-col-run { display: none; }
            .th-season td { white-space: normal; }
        }

        /* ---------------------------------------------------------------
           The season pass: a book of coupons, and the prices of admission
           --------------------------------------------------------------- */
        .th-pass { display: grid; gap: 3rem 4.5rem; align-items: center; }
        @media (min-width: 960px) { .th-pass { grid-template-columns: minmax(0, 1fr) minmax(0, 0.94fr); } }
        .th-pass-copy .th-h2 { font-size: clamp(2.3rem, 4.6vw, 3.6rem); }
        .th-pass-copy .th-orn { margin-inline: 0; }
        .th-pass-copy .th-sub { margin-top: 0; }
        .th-points { display: grid; gap: 0.9rem; margin-top: 1.6rem; color: var(--th-ink-2); }
        .th-points li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: 0.4rem; }
        .th-points li::before { content: "\2766\FE0E"; color: var(--th-gold); font-size: 1rem; line-height: 1.7; font-variant-emoji: text; }
        .th-book { padding: clamp(1.6rem, 3.6vw, 2.5rem) clamp(1.2rem, 3.6vw, 2.4rem); }
        .th-book-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
        .th-book-head h3 { font-size: 2rem; }
        .th-coupons { display: grid; grid-template-columns: repeat(16, minmax(0, 1fr)); gap: clamp(2px, 0.4vw, 4px); margin-top: 1.5rem; }
        .th-coupon {
            height: clamp(2.6rem, 6vw, 3.4rem);
            background: linear-gradient(180deg, #a52429, #7d171b);
            -webkit-mask: radial-gradient(circle at 50% 0, transparent 0.2rem, #000 0.23rem) top / 100% 51% no-repeat, radial-gradient(circle at 50% 100%, transparent 0.2rem, #000 0.23rem) bottom / 100% 51% no-repeat;
            mask: radial-gradient(circle at 50% 0, transparent 0.2rem, #000 0.23rem) top / 100% 51% no-repeat, radial-gradient(circle at 50% 100%, transparent 0.2rem, #000 0.23rem) bottom / 100% 51% no-repeat;
        }
        .th-coupon-dark { background: none; -webkit-mask: none; mask: none; border-inline: 1px dashed var(--th-line); }
        .th-book-cap { margin-top: 0.6rem; color: var(--th-ink-3); font-size: 0.9375rem; }
        .th-tariff { margin-top: 1.5rem; padding-top: 0.6rem; border-top: 1px solid var(--th-hair); }
        .th-tariff-row { display: flex; align-items: baseline; gap: 0.6rem; padding-block: 0.55rem; }
        .th-tariff-row + .th-tariff-row { border-top: 1px solid var(--th-hair); }
        .th-tariff-row b { font-family: var(--th-display); font-weight: 400; font-size: 1.35rem; white-space: nowrap; }
        .th-tariff-row i { flex: 1; min-width: 1rem; height: 0.35em; align-self: flex-end; margin-bottom: 0.42em; background: radial-gradient(circle, var(--th-ink-3) 1px, transparent 1.4px) 0 100% / 0.5rem 0.35em repeat-x; opacity: 0.7; }
        .th-tariff-row em { font-family: var(--th-ital); font-style: italic; font-size: 1rem; color: var(--th-ink-2); white-space: nowrap; }
        .th-tariff-row strong { font-family: var(--th-display); font-weight: 700; font-size: 1.35rem; }
        @media (max-width: 460px) { .th-tariff-row em { display: none; } }

        /* ---------------------------------------------------------------
           The seating plan: every seat a custom property on an arc
           --------------------------------------------------------------- */
        .th-plan { container-type: inline-size; max-width: 46rem; margin: clamp(2.5rem, 5vw, 3.75rem) auto 0; }
        .th-plan-in { --th-s: min(1.08rem, 2.5cqi); padding: clamp(1.4rem, 5cqi, 2.5rem) clamp(0.4rem, 2cqi, 1.5rem) clamp(1.3rem, 4cqi, 2rem); }
        .th-plan-stage {
            display: grid;
            place-items: center;
            width: 44%;
            height: calc(var(--th-s) * 2.5);
            margin: 0 auto calc(var(--th-s) * 3);
            border-radius: 0 0 50% 50% / 0 0 100% 100%;
            background: linear-gradient(180deg, #7d171b, #a52429);
            box-shadow: inset 0 -2px 0 #d9b25f;
            color: #f6efe0;
            font-size: clamp(0.5rem, 2cqi, 0.6875rem);
            letter-spacing: 0.3em;
        }
        .th-row { display: flex; justify-content: center; align-items: center; gap: calc(var(--th-s) * 0.28); margin-bottom: calc(var(--th-s) * 0.36); }
        .th-row-first { margin-top: calc(var(--th-s) * 1.7); }
        .th-row b {
            flex: none;
            width: calc(var(--th-s) * 1.7);
            text-align: center;
            font-family: var(--th-text);
            font-weight: 700;
            font-size: calc(var(--th-s) * 0.72);
            line-height: 1;
            color: var(--th-ink-3);
            translate: 0 calc(var(--d) * var(--d) * -0.034 * var(--th-s));
        }
        .th-seat {
            position: relative;
            flex: none;
            width: var(--th-s);
            height: var(--th-s);
            border-radius: 44% 44% 26% 26%;
            background: var(--th-seat);
            box-shadow: inset 0 0 0 1px var(--th-seat-edge);
            translate: 0 calc(var(--d) * var(--d) * -0.034 * var(--th-s));
            rotate: calc(var(--d) * -1.5deg);
            transition: background-color 0.45s ease calc(var(--k) * 5ms), scale 0.15s ease;
        }
        .th-seat-aisle { margin-inline-end: calc(var(--th-s) * 1.15); }
        .th-seat-sold { background: var(--th-sold); box-shadow: none; }
        .th-seat-held { background: repeating-linear-gradient(135deg, #d9b25f 0 2px, #8a6a25 2px 4px); box-shadow: none; }
        .th-seat-yours {
            z-index: 2;
            background: #f2cd68;
            box-shadow: 0 0 0 2px var(--th-card), 0 0 0 3.5px #b08a3c, 0 0 0.9rem 0.15rem rgba(242, 205, 104, 0.85);
            animation: th-yours 2.6s ease-in-out infinite;
        }
        @keyframes th-yours { 50% { box-shadow: 0 0 0 2px var(--th-card), 0 0 0 3.5px #b08a3c, 0 0 1.5rem 0.4rem rgba(242, 205, 104, 0.95); } }
        html.es-anim #th [data-reveal]:not(.is-revealed) .th-seat-sold { background: var(--th-seat); }
        @media (hover: hover) {
            #th .th-row .th-seat:hover { scale: 1.55; z-index: 3; box-shadow: 0 0 0 2px var(--th-card), 0 0 0 3.5px var(--th-accent); transition-delay: 0s; }
        }
        .th-plan-zone { margin-top: calc(var(--th-s) * 0.5); text-align: center; color: var(--th-ink-3); font-size: clamp(0.5rem, 1.9cqi, 0.6875rem); letter-spacing: 0.3em; }
        .th-legend { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.5rem 1.4rem; margin-top: calc(var(--th-s) * 1.6); color: var(--th-ink-2); font-size: 0.6875rem; }
        .th-legend span { display: inline-flex; align-items: center; gap: 0.5rem; }
        .th-legend .th-seat { width: 0.85rem; height: 0.85rem; animation: none; }
        .th-plan-cap { display: block; margin-top: 0.9rem; text-align: center; color: var(--th-ink-2); font-size: 1.0625rem; }
        /* Which seat is under the pointer is read by :has() into two strings, and the
           caption prints them. No script anywhere in the plan. */
        .th-plan-cap::after { content: "Row " var(--th-r, "C") ", seat " var(--th-n, "14"); }
        #th .th-plan:has([data-r="A"] .th-seat:hover) { --th-r: "A"; }
        #th .th-plan:has([data-r="B"] .th-seat:hover) { --th-r: "B"; }
        #th .th-plan:has([data-r="C"] .th-seat:hover) { --th-r: "C"; }
        #th .th-plan:has([data-r="D"] .th-seat:hover) { --th-r: "D"; }
        #th .th-plan:has([data-r="E"] .th-seat:hover) { --th-r: "E"; }
        #th .th-plan:has([data-r="F"] .th-seat:hover) { --th-r: "F"; }
        #th .th-plan:has([data-r="G"] .th-seat:hover) { --th-r: "G"; }
        #th .th-plan:has([data-r="H"] .th-seat:hover) { --th-r: "H"; }
        #th .th-plan:has([data-r="AA"] .th-seat:hover) { --th-r: "AA"; }
        #th .th-plan:has([data-r="BB"] .th-seat:hover) { --th-r: "BB"; }
        #th .th-plan:has([data-r="CC"] .th-seat:hover) { --th-r: "CC"; }
        #th .th-plan:has([data-n="1"]:hover) { --th-n: "1"; }
        #th .th-plan:has([data-n="2"]:hover) { --th-n: "2"; }
        #th .th-plan:has([data-n="3"]:hover) { --th-n: "3"; }
        #th .th-plan:has([data-n="4"]:hover) { --th-n: "4"; }
        #th .th-plan:has([data-n="5"]:hover) { --th-n: "5"; }
        #th .th-plan:has([data-n="6"]:hover) { --th-n: "6"; }
        #th .th-plan:has([data-n="7"]:hover) { --th-n: "7"; }
        #th .th-plan:has([data-n="8"]:hover) { --th-n: "8"; }
        #th .th-plan:has([data-n="9"]:hover) { --th-n: "9"; }
        #th .th-plan:has([data-n="10"]:hover) { --th-n: "10"; }
        #th .th-plan:has([data-n="11"]:hover) { --th-n: "11"; }
        #th .th-plan:has([data-n="12"]:hover) { --th-n: "12"; }
        #th .th-plan:has([data-n="13"]:hover) { --th-n: "13"; }
        #th .th-plan:has([data-n="14"]:hover) { --th-n: "14"; }
        #th .th-plan:has([data-n="15"]:hover) { --th-n: "15"; }
        #th .th-plan:has([data-n="16"]:hover) { --th-n: "16"; }
        #th .th-plan:has([data-n="17"]:hover) { --th-n: "17"; }
        #th .th-plan:has([data-n="18"]:hover) { --th-n: "18"; }
        #th .th-plan:has([data-n="19"]:hover) { --th-n: "19"; }
        #th .th-plan:has([data-n="20"]:hover) { --th-n: "20"; }
        #th .th-plan:has([data-n="21"]:hover) { --th-n: "21"; }
        #th .th-plan:has([data-n="22"]:hover) { --th-n: "22"; }
        #th .th-plan:has([data-n="23"]:hover) { --th-n: "23"; }
        #th .th-plan:has([data-n="24"]:hover) { --th-n: "24"; }

        .th-terms { display: grid; margin-top: clamp(2.5rem, 5vw, 3.75rem); border-block: 1px solid var(--th-line); }
        @media (min-width: 860px) { .th-terms { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .th-terms article { padding: 2rem 1.6rem; text-align: center; }
        .th-terms article + article { border-top: 1px solid var(--th-hair); }
        @media (min-width: 860px) { .th-terms article + article { border-top: 0; border-inline-start: 1px solid var(--th-hair); } }
        .th-terms h3 { font-size: 1.6rem; }
        .th-terms h3 + span { margin-top: 0.7rem; }
        .th-terms p { margin-top: 0.9rem; color: var(--th-ink-2); }
        .th-pay { max-width: 45rem; margin: 2.2rem auto 0; text-align: center; color: var(--th-ink-2); text-wrap: pretty; }

        /* ---------------------------------------------------------------
           The interval
           --------------------------------------------------------------- */
        .th-interval { padding-block: clamp(2.5rem, 5vw, 4rem); text-align: center; color: var(--th-ink-2); }
        .th-interval p { font-size: clamp(1.5rem, 3.4vw, 2.3rem); }
        .th-interval .th-orn { max-width: 24rem; margin-block: 0 1.2rem; }
        .th-interval .th-orn + p + .th-orn { margin-block: 1.2rem 0; }

        /* ---------------------------------------------------------------
           Front of house: three tickets, dark in both modes
           --------------------------------------------------------------- */
        .th-foh {
            --th-ink: #f3e9d6;
            --th-ink-2: #d2c4ae;
            --th-accent: #d9b25f;
            --th-gold: #d9b25f;
            position: relative;
            overflow: clip;
            background-color: #150d0c;
            background-image: radial-gradient(ellipse 70% 55% at 50% 0%, rgba(217, 178, 95, 0.16), rgba(217, 178, 95, 0) 70%);
            border-block: 3px double rgba(217, 178, 95, 0.55);
            color: #f3e9d6;
        }
        .th-tix { display: grid; gap: 1.5rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 900px) { .th-tix { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .th-tick { display: grid; grid-template-columns: 5.4rem minmax(0, 1fr); filter: drop-shadow(0 1.2rem 1.4rem rgba(0, 0, 0, 0.6)); }
        /* Restated under the page id: the shared reveal ends on `filter: none`, which outranks a plain
           class and took the shadow away as soon as the element was revealed. */
        #th .th-tick { filter: drop-shadow(0 1.2rem 1.4rem rgba(0, 0, 0, 0.6)); }
        .th-tick-stub,
        .th-tick-body { background: #fbf6ea; }
        .th-tick-stub {
            display: grid;
            justify-items: center;
            align-content: center;
            gap: 0.8rem;
            padding: 1.2rem 0.6rem;
            border-inline-end: 2px dashed rgba(28, 21, 18, 0.35);
            -webkit-mask: radial-gradient(circle at 100% 0, transparent 0.55rem, #000 0.58rem) top / 100% 51% no-repeat, radial-gradient(circle at 100% 100%, transparent 0.55rem, #000 0.58rem) bottom / 100% 51% no-repeat;
            mask: radial-gradient(circle at 100% 0, transparent 0.55rem, #000 0.58rem) top / 100% 51% no-repeat, radial-gradient(circle at 100% 100%, transparent 0.55rem, #000 0.58rem) bottom / 100% 51% no-repeat;
        }
        .th-tick-body {
            padding: 1.5rem 1.4rem 1.4rem 1.3rem;
            -webkit-mask: radial-gradient(circle at 0 0, transparent 0.55rem, #000 0.58rem) top / 100% 51% no-repeat, radial-gradient(circle at 0 100%, transparent 0.55rem, #000 0.58rem) bottom / 100% 51% no-repeat;
            mask: radial-gradient(circle at 0 0, transparent 0.55rem, #000 0.58rem) top / 100% 51% no-repeat, radial-gradient(circle at 0 100%, transparent 0.55rem, #000 0.58rem) bottom / 100% 51% no-repeat;
        }
        .th-tick-body h3 { font-size: 1.6rem; }
        .th-tick-body h3 + span { margin-top: 0.6rem; }
        .th-tick-body p { margin-top: 0.8rem; color: var(--th-ink-2); font-size: 1.0625rem; }
        .th-qr { position: relative; display: grid; grid-template-columns: repeat(13, 1fr); grid-template-rows: repeat(13, 1fr); width: 3.9rem; height: 3.9rem; overflow: hidden; }
        .th-qr i.is-on { background: #1c1512; }
        .th-qr::after {
            content: "";
            position: absolute;
            inset: 0 -3px auto -3px;
            height: 2px;
            background: #c8262c;
            box-shadow: 0 0 6px 1px rgba(200, 38, 44, 0.75);
            animation: th-scan 2.8s ease-in-out infinite;
        }
        @keyframes th-scan { 0%, 100% { translate: 0 0.2rem; } 50% { translate: 0 3.6rem; } }
        .th-admit { color: #6b5d51; font-size: 0.5625rem; letter-spacing: 0.24em; text-align: center; }
        .th-foh-foot { max-width: 46rem; margin: 2.5rem auto 0; text-align: center; color: #d2c4ae; text-wrap: pretty; }
        #th .th-foh a:focus-visible { outline-color: #d9b25f; }

        /* ---------------------------------------------------------------
           Programme notes: everything between opening and closing
           --------------------------------------------------------------- */
        .th-notes { position: relative; display: grid; counter-reset: th-note; margin-top: clamp(2.5rem, 5vw, 4rem); border-block: 1px solid var(--th-line); }
        @media (min-width: 900px) {
            .th-notes { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 5rem; }
            .th-notes::before { content: ""; position: absolute; top: 0; bottom: 0; left: 50%; width: 1px; background: var(--th-hair); }
        }
        .th-note { counter-increment: th-note; padding-block: 2.1rem; border-top: 1px solid var(--th-hair); }
        .th-note:first-child { border-top: 0; }
        @media (min-width: 900px) { .th-note:nth-child(2) { border-top: 0; } }
        .th-note-head { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr) auto; align-items: baseline; gap: 0.5rem; }
        .th-note-head::before { content: counter(th-note, lower-roman) "."; font-family: var(--th-ital); font-style: italic; color: var(--th-bronze); font-size: 1.25rem; }
        .th-note h3 { font-size: clamp(1.5rem, 2.4vw, 1.85rem); text-wrap: balance; }
        .th-note p { margin-top: 0.8rem; padding-inline-start: 2.9rem; color: var(--th-ink-2); }
        .th-note p.th-fine { font-size: 1.0625rem; color: var(--th-ink-3); }
        @media (max-width: 520px) { .th-note p { padding-inline-start: 0; } }

        /* ---------------------------------------------------------------
           Houses: a row of boxes
           --------------------------------------------------------------- */
        .th-boxes { display: grid; gap: 1.5rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 680px) { .th-boxes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1020px) { .th-boxes { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .th-box {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 4.4rem 1.7rem 2rem;
            text-align: center;
            background: var(--th-card);
            border-radius: 50% 50% 0 0 / 5.6rem 5.6rem 0 0;
            box-shadow: inset 0 0 0 1px var(--th-line), inset 0 0 0 7px var(--th-card), inset 0 0 0 8px var(--th-gold);
            transition: translate 0.3s ease, box-shadow 0.3s ease;
        }
        .th-box:hover { translate: 0 -0.35rem; box-shadow: inset 0 0 0 1px var(--th-line), inset 0 0 0 7px var(--th-card), inset 0 0 0 8px var(--th-gold), 0 1.6rem 2rem -1.6rem rgba(28, 21, 18, 0.7); }
        /* A small swag over each box, cut the same way as the house valance. */
        .th-box::before {
            content: "";
            position: absolute;
            top: 1.5rem;
            left: 50%;
            width: 5.4rem;
            height: 1.25rem;
            translate: -50% 0;
            background: radial-gradient(ellipse 52% 100% at 50% 0, #b3262b 0%, #8e1b1f 55%, #5c0f13 100%) 0 0 / 1.8rem 100% repeat-x;
            -webkit-mask: radial-gradient(ellipse 50% 100% at 50% 0, #000 98%, transparent 100%) 0 0 / 1.8rem 100% repeat-x;
            mask: radial-gradient(ellipse 50% 100% at 50% 0, #000 98%, transparent 100%) 0 0 / 1.8rem 100% repeat-x;
        }
        .th-box h3 { font-size: 1.75rem; text-wrap: balance; }
        .th-box p { margin-top: 0.8rem; color: var(--th-ink-2); }
        .th-box a { margin-top: auto; padding-top: 1.2rem; }
        .th-box a span { display: inline-flex; align-items: center; gap: 0.5rem; padding-bottom: 0.15rem; border-bottom: 1px solid var(--th-gold); transition: gap 0.2s ease; }
        .th-box a:hover span { gap: 0.85rem; }
        .th-box a svg { width: 0.95rem; height: 0.95rem; }

        /* ---------------------------------------------------------------
           Synopsis in three acts
           --------------------------------------------------------------- */
        .th-acts { display: grid; counter-reset: th-act; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 860px) { .th-acts { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .th-act { counter-increment: th-act; padding: 0.5rem 2rem 2.2rem; text-align: center; }
        .th-act + .th-act { border-top: 1px solid var(--th-hair); padding-top: 2.2rem; }
        @media (min-width: 860px) { .th-act + .th-act { border-top: 0; padding-top: 0.5rem; border-inline-start: 1px solid var(--th-hair); } }
        .th-act-no { color: var(--th-ink-3); }
        .th-act-no::after { content: " " counter(th-act, upper-roman); }
        .th-act-fig { display: block; margin-top: 0.4rem; font-family: var(--th-display); font-weight: 700; font-size: clamp(4.5rem, 8vw, 6.4rem); line-height: 1; color: var(--th-accent); }
        .th-act-fig::before { content: counter(th-act, upper-roman); }
        .th-act h3 { margin-top: 0.6rem; font-size: 1.9rem; }
        .th-act p { margin: 0.8rem auto 0; max-width: 20rem; color: var(--th-ink-2); }
        .th-act small { display: block; margin-top: 1.1rem; color: var(--th-ink-3); font-size: 0.6875rem; }

        /* ---------------------------------------------------------------
           The company: key features as a cast list
           --------------------------------------------------------------- */
        .th-cast { max-width: 52rem; margin: clamp(2.2rem, 4.5vw, 3.4rem) auto 0; border-block: 3px double var(--th-line); }
        .th-cast a { display: flex; align-items: baseline; gap: 0.8rem; padding: 1rem 0.5rem; transition: background-color 0.2s ease, padding 0.25s ease; }
        .th-cast a + a { border-top: 1px solid var(--th-hair); }
        .th-cast a:hover { background: var(--th-paper-2); padding-inline: 1.1rem; }
        .th-alt .th-cast a:hover { background: var(--th-paper); }
        .th-cast b { font-family: var(--th-display); font-weight: 700; font-size: 1.05rem; letter-spacing: 0.14em; text-transform: uppercase; white-space: nowrap; }
        .th-cast i { flex: 1; min-width: 1.5rem; height: 0.35em; align-self: flex-end; margin-bottom: 0.45em; background: radial-gradient(circle, var(--th-ink-3) 1px, transparent 1.4px) 0 100% / 0.5rem 0.35em repeat-x; opacity: 0.7; }
        .th-cast span { max-width: 62%; text-align: end; color: var(--th-ink-2); }
        .th-cast a:hover b { color: var(--th-accent); }
        @media (max-width: 640px) {
            .th-cast a { flex-direction: column; gap: 0.2rem; }
            .th-cast i { display: none; }
            .th-cast span { max-width: none; text-align: start; }
        }
        .th-centre { margin-top: 1.9rem; text-align: center; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the print changes.
           --------------------------------------------------------------- */
        #th .th-prices > section { background: transparent; }
        #th .th-prices h2 { font-family: var(--th-display); font-weight: 400; letter-spacing: -0.012em; line-height: 1.05; font-size: clamp(2.1rem, 4.6vw, 3.4rem); color: var(--th-ink); }
        #th .th-prices h2 + p { color: var(--th-ink-2); font-size: 1.1875rem; }
        #th .th-prices .grid > div { background: var(--th-card); border: 0; border-radius: 0; box-shadow: inset 0 0 0 1px var(--th-line), inset 0 0 0 7px var(--th-card), inset 0 0 0 8px var(--th-hair); padding: 2rem 1.8rem; color: var(--th-ink); }
        #th .th-prices .grid > div:nth-child(2) { box-shadow: inset 0 0 0 1px var(--th-gold), inset 0 0 0 7px var(--th-card), inset 0 0 0 8px var(--th-gold); }
        #th .th-prices .grid > div span,
        #th .th-prices .grid > div p,
        #th .th-prices .grid > div li { color: var(--th-ink-2); font-size: 1.0625rem; }
        #th .th-prices .grid > div .text-3xl { font-family: var(--th-display); font-weight: 700; font-size: 2.9rem; color: var(--th-ink); }
        #th .th-prices .grid > div .uppercase { font-family: var(--th-text); font-size: 0.75rem; letter-spacing: 0.22em; color: var(--th-ink); }
        #th .th-prices .grid > div .rounded-full { background: var(--th-accent); color: var(--th-paper); border-radius: 0; font-size: 0.625rem; letter-spacing: 0.16em; }
        #th .th-prices .grid > div p.text-xs { font-size: 0.9375rem; color: var(--th-ink-3); }
        #th .th-prices .grid > div svg { color: var(--th-bronze); }
        #th .th-prices a.font-medium { color: var(--th-ink); border-bottom: 1px solid var(--th-gold); }
        #th .th-prices a.rounded-2xl { background: #8e1b1f; color: #f6efe0; border-radius: 0; font-family: var(--th-text); font-weight: 700; font-size: 0.9375rem; letter-spacing: 0.18em; text-transform: uppercase; box-shadow: inset 0 0 0 4px #8e1b1f, inset 0 0 0 5px rgba(230, 199, 119, 0.85); }
        #th .th-prices a.rounded-2xl:hover { background: #a52429; box-shadow: inset 0 0 0 4px #a52429, inset 0 0 0 5px rgba(230, 199, 119, 0.95); transform: translateY(-2px); }

        #th .th-keep > section { background: var(--th-paper-2); border-top: 3px double var(--th-line); }
        #th .th-keep h2 { font-family: var(--th-display); font-weight: 400; font-size: clamp(2rem, 4vw, 2.8rem); line-height: 1.05; color: var(--th-ink); }
        #th .th-keep p.uppercase { font-family: var(--th-text); font-weight: 700; letter-spacing: 0.22em; color: var(--th-accent); }
        #th .th-keep .grid > a { background: var(--th-card); border: 1px solid var(--th-line); border-radius: 0; }
        #th .th-keep .grid > a:hover { border-color: var(--th-gold); box-shadow: 0 1.4rem 1.8rem -1.4rem rgba(28, 21, 18, 0.6); }
        #th .th-keep .grid > a > span:first-child { display: none; }
        #th .th-keep .grid > a h3 { font-family: var(--th-display); font-weight: 400; font-size: 1.4rem; color: var(--th-ink); }
        #th .th-keep .grid > a p { color: var(--th-ink-2); font-size: 1.0625rem; }
        #th .th-keep .grid > a > span:last-child,
        #th .th-keep a.self-start { color: var(--th-accent); }

        /* ---------------------------------------------------------------
           Also playing: four small bills
           --------------------------------------------------------------- */
        .th-also { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.1rem; margin-top: clamp(2.2rem, 4.5vw, 3.2rem); }
        @media (min-width: 900px) { .th-also { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem; } }
        .th-also a { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; min-height: 13rem; padding: 2rem 1rem 1.6rem; text-align: center; transition: translate 0.3s ease; }
        .th-also a:hover { translate: 0 -0.35rem; }
        .th-also small { color: var(--th-ink-3); font-size: 0.6875rem; }
        .th-also strong { font-family: var(--th-display); font-weight: 400; font-size: clamp(1.5rem, 2.8vw, 2rem); line-height: 1.08; text-wrap: balance; }
        .th-also em { margin-top: auto; display: inline-flex; align-items: center; gap: 0.4rem; font-style: normal; color: var(--th-accent); font-size: 0.6875rem; transition: gap 0.2s ease; }
        .th-also a:hover em { gap: 0.75rem; }
        .th-also svg { width: 0.9rem; height: 0.9rem; }

        /* ---------------------------------------------------------------
           Notes and queries
           --------------------------------------------------------------- */
        .th-qa { max-width: 50rem; margin: clamp(2.2rem, 4.5vw, 3.4rem) auto 0; counter-reset: th-q; border-block: 3px double var(--th-line); }
        .th-qa details { counter-increment: th-q; }
        .th-qa details + details { border-top: 1px solid var(--th-hair); }
        .th-qa summary { display: grid; grid-template-columns: 2.6rem minmax(0, 1fr) 1.25rem; align-items: baseline; gap: 0.6rem; padding: 1.25rem 0.4rem 1.15rem; cursor: pointer; }
        .th-qa summary::before { content: counter(th-q, decimal-leading-zero); font-family: var(--th-display); font-weight: 700; font-size: 0.9375rem; color: var(--th-bronze); }
        .th-qa h3 { font-family: var(--th-display); font-weight: 400; font-size: clamp(1.3rem, 2.4vw, 1.6rem); line-height: 1.2; }
        .th-qa summary i { position: relative; align-self: center; width: 1.1rem; height: 1.1rem; }
        .th-qa summary i::before,
        .th-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 0.5px) 0 auto 0; height: 1px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .th-qa summary i::after { rotate: 90deg; }
        .th-qa details[open] summary i::after { rotate: 0deg; }
        .th-qa details[open] h3 { color: var(--th-accent); }
        .th-qa details p { padding: 0 0.4rem 1.6rem 3.6rem; color: var(--th-ink-2); text-wrap: pretty; }
        @media (max-width: 520px) { .th-qa details p { padding-inline-start: 0.4rem; } }

        /* ---------------------------------------------------------------
           Opening night: the curtain again, and a card to sign
           --------------------------------------------------------------- */
        .th-finale {
            position: relative;
            overflow: clip;
            padding: clamp(6.5rem, 12vw, 10rem) 0 clamp(4.5rem, 9vw, 7.5rem);
            background-color: #8e1b1f;
            background-image:
                linear-gradient(180deg, rgba(0, 0, 0, 0.5) 0%, rgba(0, 0, 0, 0) 20%, rgba(0, 0, 0, 0) 64%, rgba(0, 0, 0, 0.42) 100%),
                linear-gradient(90deg, rgba(0, 0, 0, 0) calc(50% - 1px), rgba(20, 0, 0, 0.7) calc(50% - 1px), rgba(20, 0, 0, 0.7) calc(50% + 1px), rgba(0, 0, 0, 0) calc(50% + 1px)),
                repeating-linear-gradient(90deg, rgba(24, 0, 0, 0.5) 0, rgba(0, 0, 0, 0) 0.9rem, rgba(255, 172, 150, 0.2) 1.6rem, rgba(0, 0, 0, 0) 2.4rem, rgba(24, 0, 0, 0.5) 3.5rem),
                repeating-linear-gradient(90deg, rgba(24, 0, 0, 0.22) 0, rgba(0, 0, 0, 0) 2.3rem, rgba(255, 172, 150, 0.09) 3.4rem, rgba(24, 0, 0, 0.22) 5.9rem);
        }
        .th-finale::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 0.9rem;
            background: repeating-linear-gradient(90deg, #ecd08a 0 2px, #9a7628 2px 3.5px);
            border-top: 3px solid #d9b25f;
        }
        .th-card {
            position: relative;
            max-width: 49rem;
            margin-inline: auto;
            padding: clamp(2.2rem, 5vw, 3.75rem) clamp(1.25rem, 5vw, 3.75rem) clamp(2rem, 4vw, 3rem);
            text-align: center;
            background: #fbf6ea;
            box-shadow: inset 0 0 0 1px rgba(28, 21, 18, 0.3), inset 0 0 0 8px #fbf6ea, inset 0 0 0 9px #b08a3c, 0 2.5rem 3.5rem -1.5rem rgba(20, 0, 0, 0.85);
        }
        .th-card .th-sub { font-size: 1.1875rem; }
        .th-claim-row { display: grid; gap: 0.9rem; max-width: 34rem; margin: 2rem auto 0; }
        #th .th-claim {
            display: flex;
            align-items: baseline;
            min-width: 0;
            padding: 1rem 0.5rem 0.9rem;
            border-bottom: 2px solid #1c1512;
            font-family: var(--th-display);
            /* 1.1rem at the narrow end: at 1.15rem a 360px phone cut the last letter of "your-theater" */
            font-size: clamp(1.1rem, 4.2vw, 1.6rem);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        @media (max-width: 350px) { #th .th-claim { padding-inline: 0; font-size: 1rem; } }
        #th .th-claim:focus-within { border-color: #8e1b1f; box-shadow: 0 2px 0 #8e1b1f; }
        #th .th-claim input {
            flex: 1;
            min-width: 0;
            margin-block: -1rem;
            padding: 1rem 0;
            border: 0;
            background: transparent;
            box-shadow: none;
            outline: none;
            text-align: right;
            font: inherit;
            font-weight: 700;
            color: #8e1b1f;
        }
        #th .th-claim input::placeholder { color: #8a7a6a; font-weight: 400; }
        .th-claim span { flex: none; color: #4a3f37; user-select: none; }
        .th-card-fine { margin-top: 1.1rem; color: #6b5d51; }
        #th .th-finale a:focus-visible { outline-color: #8e1b1f; }

        @media (prefers-reduced-motion: reduce) {
            /* Standing still, the apron shows the list once, wrapped and centred. */
            .th-apron .th-genre[aria-hidden="true"] { display: none; }
            .th-apron .es-marquee-track { row-gap: 0.45rem; padding-inline: 1rem; }
            .th-foots::before,
            .th-seat-yours,
            .th-qr::after { animation: none; }
            .th-btn, .th-box, .th-also a, .th-cast a, .th-seat, .th-span, .th-eve { transition: none; }
        }
    </style>

    @php
        // One production's run: 12 Sep - 4 Oct. Plays Tue-Sun, dark Mondays.
        // 'on' = a performance, 'dark' = a dark day. The matinee is a SEPARATE
        // event, so it gets its own strip rather than a doubled cell.
        $runDays = [];
        $matineeDays = [];
        // 12 Sep 2026 is a Saturday, so slot 0 is a Saturday and the weekday is
        // simply $i % 7: Sat 0, Sun 1, Mon 2. Sixteen days with two dark Mondays
        // is fourteen evening performances, which is the number the page states.
        foreach (range(0, 15) as $i) {
            $runDays[] = ($i % 7 === 2) ? 'dark' : 'on';        // dark Mondays
            $matineeDays[] = ($i % 7 === 0) ? 'on' : 'off';     // Saturdays
        }

        $season = [
            ['Macbeth',            'Sep 12 - Sep 27', '14 performances', 0,  13, false],
            ['The Seagull',        'Oct 17 - Nov 8',  '12 performances', 31, 19, false],
            ['A Christmas Carol',  'Dec 5 - Jan 3',   '22 performances', 74, 26, false],
            ['Studio: new work',   'Nov 14 - Nov 22', '6 performances',  56,  7, true],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for theaters?',
                'a' => 'Yes. Setting a production up as a run, marking dark days, splitting your spaces into sub-schedules, publishing your season and syncing two ways with Google, Outlook or CalDAV are all free forever, as is free registration however many seats go out, and scanning tickets at the door is free on every plan. Putting a price on a seat is what the Pro plan at '.plan_price($proMonthly).' a month opens, and it adds the live check-in dashboard, season passes and custom checkout questions. Event Schedule charges zero platform fees on ticket sales on every plan, the free one included.',
            ],
            [
                'q' => 'How do I set up a multi-week run?',
                'a' => 'Create the production once as a recurring event, choose the days it plays, and give the recurrence an end: either a closing date or a number of performances. A run that closes after fourteen performances stops on its own rather than repeating until somebody remembers to switch it off. Add date exceptions for the nights you are dark.',
            ],
            [
                'q' => 'What about matinees?',
                'a' => 'A matinee is its own event. A recurring event has one start time, so it produces one performance on each day it plays, and a Saturday matinee needs a second event alongside the evening run. It is a little more setup and it keeps the two curtain times, and their tickets, properly separate.',
            ],
            [
                'q' => 'Can I sell a pass for the whole run?',
                'a' => 'Yes, on the Pro plan. A season pass is tied to the production\'s recurrence and is valid for every performance of the run, once each, and you can set how many seats it admits per performance. It is sold alongside single tickets rather than instead of them.',
            ],
            [
                'q' => 'Can I price different parts of the house differently?',
                'a' => 'Yes, two ways. Named ticket types price parts of the house by the number: create as many as the production needs, each with its own price, quantity and sales window, and a group rate that applies when somebody buys several at once (add-ons are on Pro). On Enterprise you can price the house by its actual seats instead: draw the auditorium once as a seating plan on your venue schedule and give each section a price band. Buyers get the best seats left together or pick their own off the map, and the box office console can pick several seats at once to hold back, book for a caller or release, and move a booking to another seat.',
            ],
            [
                'q' => 'Can I run more than one space?',
                'a' => 'Yes, on every plan. Sub-schedules keep the mainstage, the studio and the family programme apart on one link, so somebody looking for the studio season is not reading through the whole year to find it.',
            ],
            [
                'q' => 'Can people ask to be told when tickets go on sale?',
                'a' => 'Yes, free on every plan. Switch on the "Notify me" card, announce a production before tickets are ready, and each performance page offers "Tell me when tickets go on sale". A visitor leaves an email address, with no account, and hears when tickets go on sale, if the performance is cancelled, and again shortly before it starts, plus any change notice you choose to send. Each date of a run keeps its own list, the event editor shows how many people are waiting, and it is not a subscription to your schedule.',
            ],
            [
                'q' => 'Can I refund a ticket if a performance is cancelled?',
                'a' => 'Yes, from the Sales page, on Pro. A Stripe or PayPal sale goes back through the provider, in full or in part, and the sale only changes once the money has moved. A partial refund leaves the ticket valid; a full refund puts the seats back on sale, and on a reserved-seating date the seat returns to the map. A sale taken another way, such as cash at the box office or a payment link, is marked as refunded and you return the money yourself.',
            ],
        ];

        $dotSections = [
            ['top', 'The run'],
            ['why', 'Not a date'],
            ['run', 'Setting the run'],
            ['season', 'The season'],
            ['pass', 'The season pass'],
            ['tiers', 'Ticket types'],
            ['house', 'The house'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Opening night'],
        ];

        // ---- Print furniture for "The Program". Nothing below asserts anything new: the
        // weekday letters and dates are read off the same slot index as $runDays (slot 0 is
        // Saturday 12 September), and the month marks are placed on the same 113-day line
        // the season bars are positioned along (12 Sep to 3 Jan).
        $thDow = ['S', 'S', 'M', 'T', 'W', 'T', 'F'];
        $thSeasonStart = new \DateTimeImmutable('2026-09-12');
        $thSeasonDays = $thSeasonStart->diff(new \DateTimeImmutable('2027-01-03'))->days;
        $thMonths = [['Sep', 0]];
        foreach (['2026-10-01' => 'Oct', '2026-11-01' => 'Nov', '2026-12-01' => 'Dec', '2027-01-01' => 'Jan'] as $thDate => $thLabel) {
            $thMonths[] = [$thLabel, round($thSeasonStart->diff(new \DateTimeImmutable($thDate))->days / $thSeasonDays * 100, 1)];
        }

        // The seating plan: rows, seats in each, and where the circle begins.
        $thRows = [
            ['A', 14, false], ['B', 14, false], ['C', 16, false], ['D', 16, false],
            ['E', 18, false], ['F', 18, false], ['G', 20, false], ['H', 20, false],
            ['AA', 22, true], ['BB', 22, false], ['CC', 24, false],
        ];

        $thArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $thDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="th">

        <nav class="th-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot th-caps"><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. The house: curtain up on the title                        -->
        <!-- ============================================================ -->
        <section id="top" class="th-stage" style="scroll-margin-top: 5rem;">
            <div class="th-pros">
                <div class="th-opening">
                    <div class="th-pelmet" aria-hidden="true"></div>
                    <div class="th-valance" aria-hidden="true"></div>
                    <div class="th-drape th-drape-l" aria-hidden="true"></div>
                    <div class="th-drape th-drape-r" aria-hidden="true"></div>

                    <div class="th-drop">
                        <div class="th-title">
                            <h1 class="th-d th-h1">
                                <x-marketing.hero-eyebrow class="th-eyebrow es-fade-up es-d-1">
                                    A theater calendar for producing houses
                                </x-marketing.hero-eyebrow>
                                <span class="es-mask"><span class="es-mask-line">A production is not a date.</span></span>
                                <span class="es-mask es-mask-2"><span class="es-mask-line">It is <em>fourteen</em> of them.</span></span>
                            </h1>

                            <div class="th-orn es-fade-up es-d-2" aria-hidden="true">&#10086;&#xFE0E;</div>

                            <p class="th-lede es-fade-up es-d-2">
                                Set the run up once - Tuesday to Sunday, dark Mondays, closing after fourteen performances - and sell every one of them from a single link, with zero platform fees.
                            </p>

                            <div class="th-cta es-fade-up es-d-3">
                                <a href="#run" class="th-more th-caps">
                                    See how a run is set up
                                    {!! $thDown !!}
                                </a>
                                <a href="{{ app_url('/sign_up?type=venue') }}" class="th-btn">
                                    Create your theater's calendar
                                    {!! $thArrow !!}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="th-boards" aria-hidden="true"></div>
                    <div class="th-foots" aria-hidden="true"></div>
                </div>

                <!-- The apron: what plays here -->
                <div class="th-apron">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Drama', 'Musical', 'Comedy', 'Shakespeare', 'New Work', 'Panto', 'Opera', 'Studio', 'Family', 'Rep'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="th-caps th-genre">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 1b. Dates of performance. Two strips, because the matinee    -->
        <!--     is a separate event.                                     -->
        <!-- ============================================================ -->
        <section class="th-dates">
            <div class="th-wrap">
                <div class="th-leaf th-bill" data-reveal="panel">
                    <p class="th-caps th-kicker" aria-hidden="true">Dates of performance</p>
                    <h2 class="th-d th-bill-name">Macbeth</h2>
                    <p class="th-caps th-bill-when">Sep 12 &ndash; Sep 27</p>
                    <p class="th-bill-line">Tuesday to Sunday &middot; dark Mondays &middot; 14 evening performances</p>

                    <div class="th-cal" aria-hidden="true" id="th-cal">
                        <div class="th-cal-row th-cal-dow th-caps">
                            @foreach (range(0, 15) as $i)
                                <span>{{ $thDow[$i % 7] }}</span>
                            @endforeach
                        </div>
                        <div class="th-cal-row th-cal-num">
                            @foreach ($runDays as $i => $d)
                                <span @class(['is-dark' => $d !== 'on']) data-date="{{ 12 + $i }}">{{ 12 + $i }}</span>
                            @endforeach
                        </div>
                        <div class="th-cal-row th-cal-eve">
                            @foreach ($runDays as $i => $d)
                                <div class="th-eve @if ($d !== 'on') th-eve-dark @endif" data-eve="{{ $d }}" style="--i: {{ $i }};">@if ($d !== 'on')Dark @endif</div>
                            @endforeach
                        </div>
                        <p class="th-ital th-cal-cap">Evenings &middot; dashed cells are dark days</p>

                        <div class="th-cal-row th-cal-mat">
                            @foreach ($matineeDays as $d)
                                <div class="th-mat @if ($d === 'on') th-mat-on @endif" data-mat="{{ $d }}"></div>
                            @endforeach
                        </div>
                        <p class="th-ital th-cal-cap">Saturday matinees &middot; a separate event, on its own run</p>
                    </div>

                    <p class="th-bill-foot">
                        One recurring event, ending after its last performance. The matinee is a second event, so the two curtain times keep their own tickets.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. A production is not a date (a band of velvet)             -->
        <!-- ============================================================ -->
        <section id="why" class="th-band th-velvet th-section" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal>The unit</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Most calendars think a show is <em>one night.</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.14s;">&#10086;&#xFE0E;</div>
                </div>

                <div class="th-trio" data-reveal-group="110">
                    <div data-reveal>
                        <p class="th-caps th-kicker">The run</p>
                        <h3 class="th-d">
                            <b><span data-count-to="14">14</span></b> performances
                        </h3>
                        <p>Same set, same cast, sixteen days. Entering it as fourteen separate events is fourteen chances to mistype a time.</p>
                    </div>
                    <div data-reveal>
                        <p class="th-caps th-kicker">The setup</p>
                        <h3 class="th-d">
                            <b><span data-count-to="1">1</span></b> event
                        </h3>
                        <p>A day-of-week pattern, exceptions for the dark nights, and an end. Change the curtain time once and every performance follows.</p>
                    </div>
                    <div data-reveal>
                        <p class="th-caps th-kicker">The close</p>
                        <h3 class="th-d"><i aria-hidden="true">Fin.</i>It stops itself</h3>
                        <p>A run ends on a closing date or after a set number of performances, so it is never still selling tickets in February.</p>
                    </div>
                </div>

                <p class="th-band-foot" data-reveal>
                    The run is the unit. Everything else on this page hangs off it.
                    <a href="#run" class="th-more th-caps">
                        Set one up
                        {!! $thDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Setting the run                                           -->
        <!-- ============================================================ -->
        <section id="run" class="th-section" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal>Setting the run</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Days, dark nights, and a <em>closing night.</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10022;&#xFE0E;</div>
                    <p class="th-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Three settings turn one event into a run, and all three are on the free plan.
                    </p>
                </div>

                <div class="th-set" data-reveal-group="100">
                    <article class="th-leaf" data-reveal="panel">
                        <h3 class="th-d">The days it plays</h3>
                        <span class="th-caps th-tier">Free</span>
                        <p>Pick the days of the week and the curtain time. Tuesday to Sunday is six ticks a week without entering six events.</p>
                        <div class="th-fig th-caps" aria-hidden="true">
                            <span>M</span><span class="is-on">T</span><span class="is-on">W</span><span class="is-on">T</span><span class="is-on">F</span><span class="is-on">S</span><span class="is-on">S</span>
                        </div>
                    </article>
                    <article class="th-leaf" data-reveal="panel">
                        <h3 class="th-d">The nights you are dark</h3>
                        <span class="th-caps th-tier">Free</span>
                        <p>Date exceptions take individual dates out, so a Monday off or a press night that moved does not need the run rebuilding.</p>
                        <div class="th-fig th-caps" aria-hidden="true">
                            <span class="is-on">12</span><span class="is-on">13</span><span class="is-out">14</span><span class="is-on">15</span><span class="is-on">16</span><span class="is-on">17</span>
                        </div>
                    </article>
                    <article class="th-leaf" data-reveal="panel">
                        <h3 class="th-d">The end</h3>
                        <span class="th-caps th-tier">Free</span>
                        <p>A closing date, or a number of performances. This is the setting that makes a run a run instead of a weekly night that never stops.</p>
                        <div class="th-fig th-fig-end th-caps" aria-hidden="true">
                            <span>A closing date</span><span class="is-on">After 14</span>
                        </div>
                    </article>
                </div>

                <p class="th-set-note" data-reveal>
                    Matinees are their own event on their own run, because one recurring event has one curtain time. Two events, two sets of tickets, no ambiguity about which performance somebody bought.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The season: several runs, stacked                         -->
        <!-- ============================================================ -->
        <section id="season" class="th-section th-alt" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal>The season</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        A season is just runs, <em>side by side.</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10086;&#xFE0E;</div>
                    <p class="th-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Once each production is a run, the year draws itself, and the studio season sits in its own sub-schedule.
                    </p>
                </div>

                <div class="th-leaf th-season" data-reveal="panel">
                    <table id="th-season">
                        <caption class="sr-only">Season 2026 to 2027: each production with its dates and number of performances</caption>
                        <thead>
                            <tr class="th-caps">
                                <th scope="col">Production</th>
                                <th scope="col">Dates</th>
                                <th scope="col" class="th-col-run">Run</th>
                            </tr>
                            <tr aria-hidden="true">
                                <td colspan="3" class="th-axis-cell">
                                    <div class="th-axis th-caps">
                                        @foreach ($thMonths as [$thLabel, $thAt])
                                            @if ($thAt < 94)<span style="left: {{ $thAt }}%;">{{ $thLabel }}</span>@endif
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($season as [$sName, $sDates, $sCount, $sLeft, $sWidth, $sStudio])
                                <tr class="th-rule-top">
                                    <th scope="row">
                                        {{ $sName }}
                                        @if ($sStudio)<small>Studio sub-schedule</small>@endif
                                    </th>
                                    <td>{{ $sDates }}</td>
                                    <td class="th-col-run">{{ $sCount }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="th-run-cell">
                                        <div class="th-track" aria-hidden="true">
                                            <div class="th-span @if ($sStudio) th-span-soft @endif" style="left: {{ $sLeft }}%; width: {{ $sWidth }}%;"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="th-season-note">Each bar is one production's run. Sub-schedules keep the studio season on its own strand of the same link, and somebody booking three productions pays for them in one checkout.</p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The season pass                                           -->
        <!-- ============================================================ -->
        <section id="pass" class="th-section" style="scroll-margin-top: 4rem;">
            <div class="th-wrap th-pass">
                <div class="th-pass-copy">
                    <p class="th-caps th-kicker" data-reveal>The season pass</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        One pass, <em>the whole run.</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10022;&#xFE0E;</div>
                    <p class="th-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Because the run is one recurring event, a pass can be tied to it: valid for every performance, once each. Set how many seats it admits per performance, and sell it alongside single tickets rather than instead of them.
                    </p>
                    <ul class="th-points" data-reveal-group="70">
                        <li data-reveal>
                            <span>Usage is tracked per performance, so you can see which nights the pass holders actually came to.</span>
                        </li>
                        <li data-reveal>
                            <span>A cancellation deadline and a late-cancel policy can be set on the pass itself.</span>
                        </li>
                        <li data-reveal>
                            <span>Passes are a Pro feature. Publishing the run and its dates is not.</span>
                        </li>
                    </ul>
                </div>

                <div class="th-leaf th-book" data-reveal="panel">
                    <div class="th-book-head">
                        <h3 class="th-d">Season pass</h3>
                        <span class="th-caps th-tier th-tier-pro">Pro</span>
                    </div>
                    <div aria-hidden="true">
                        <div class="th-coupons">
                            @foreach ($runDays as $d)
                                <div class="th-coupon @if ($d !== 'on') th-coupon-dark @endif"></div>
                            @endforeach
                        </div>
                        <p class="th-ital th-book-cap">The pass covers every one of these, once each.</p>
                    </div>
                    <div class="th-tariff">
                        @foreach ([['Season pass', 'every performance', '$120'], ['Single ticket', 'one performance', '$22'], ['Preview', 'first three nights', '$14']] as [$tName, $tScope, $tPrice])
                            <div class="th-tariff-row">
                                <b>{{ $tName }}</b>
                                <i aria-hidden="true"></i>
                                <em>{{ $tScope }}</em>
                                <strong>{{ $tPrice }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Ticket types, by the number or by the seat                -->
        <!-- ============================================================ -->
        <section id="tiers" class="th-section th-alt" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal>Ticket types</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Name your prices. <em>Or your seats.</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10086;&#xFE0E;</div>
                    <p class="th-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Create as many ticket types as the production needs, each with its own price, quantity and sales window. That sells the house by the number. On Enterprise you can sell it by the seat instead: draw the auditorium once, and the buyer gets the best seats left together or takes row C seat 14 off a real chart.
                        <a href="{{ marketing_url('/features/allocated-seating') }}" class="th-a">How reserved seating works</a>
                    </p>
                </div>

                <!-- The seating plan. Decorative: the paragraph above carries the meaning. -->
                <div class="th-plan" aria-hidden="true" data-reveal>
                    <div class="th-leaf th-plan-in">
                        <div class="th-plan-stage th-caps">Stage</div>
                        @php $thSeatIndex = 0; @endphp
                        @foreach ($thRows as [$thRow, $thCount, $thCircle])
                            @if ($thCircle)
                                <p class="th-caps th-plan-zone">Dress circle</p>
                            @endif
                            <div class="th-row @if ($thCircle) th-row-first @endif" data-r="{{ $thRow }}">
                                <b style="--d: {{ -($thCount / 2 + 1) }};">{{ $thRow }}</b>
                                @for ($thN = 1; $thN <= $thCount; $thN++)
                                    @php
                                        $thSeatIndex++;
                                        $thState = 'free';
                                        if ($thRow === 'C' && $thN === 14) {
                                            $thState = 'yours';
                                        } elseif ($thRow === 'F' && $thN >= 8 && $thN <= 11) {
                                            $thState = 'held';
                                        } elseif (crc32($thRow.'-'.$thN) % 100 < 44) {
                                            $thState = 'sold';
                                        }
                                    @endphp
                                    <i class="th-seat th-seat-{{ $thState }} @if ($thN === intdiv($thCount, 2)) th-seat-aisle @endif" data-n="{{ $thN }}" style="--d: {{ $thN - ($thCount + 1) / 2 }}; --k: {{ $thSeatIndex }};"></i>
                                @endfor
                                <b style="--d: {{ $thCount / 2 + 1 }};">{{ $thRow }}</b>
                            </div>
                        @endforeach
                        <div class="th-legend th-caps">
                            <span><i class="th-seat" style="--d: 0; --k: 0;"></i>Available</span>
                            <span><i class="th-seat th-seat-sold" style="--d: 0; --k: 0;"></i>Sold</span>
                            <span><i class="th-seat th-seat-held" style="--d: 0; --k: 0;"></i>Held</span>
                            <span><i class="th-seat th-seat-yours" style="--d: 0; --k: 0;"></i>Yours</span>
                        </div>
                        <span class="th-ital th-plan-cap"></span>
                    </div>
                </div>

                <div class="th-terms" data-reveal-group="100">
                    <article data-reveal>
                        <h3 class="th-d">Tiers that close on time</h3>
                        <span class="th-caps th-tier">Free</span>
                        <p>Give each type a sales window so preview pricing stops when previews do, and concessions can open later without you editing anything. A group rate can kick in once somebody buys several.</p>
                    </article>
                    <article data-reveal>
                        <h3 class="th-d">Ask what you need</h3>
                        <span class="th-caps th-tier th-tier-pro">Pro</span>
                        <p>Custom questions on the ticket collect what the night actually requires - access needs, a dinner choice, a school's contact - at the point of purchase.</p>
                    </article>
                    <article data-reveal>
                        <h3 class="th-d">Add-ons and codes</h3>
                        <span class="th-caps th-tier th-tier-pro">Pro</span>
                        <p>Add-ons that attach to a booking, each with its own stock, and discount codes for the people you want to bring back.</p>
                    </article>
                </div>

                <p class="th-pay" data-reveal>
                    Pro opens every payment method: take the money through Stripe, PayPal, Invoice Ninja, Payfast (in rand), a payment link or cash at the box office. Event Schedule charges zero platform fees, so past the provider's own processing the money is yours. On Pro, a big booking can be split into <a href="{{ marketing_url('/features/installments') }}" class="th-a">monthly installments</a> by card through Stripe.
                </p>
            </div>
        </section>

        <!-- The interval -->
        <div class="th-interval" aria-hidden="true">
            <div class="th-wrap">
                <div class="th-orn">&#10022;&#xFE0E;</div>
                <p class="th-ital">There will be one interval</p>
                <div class="th-orn">&#10022;&#xFE0E;</div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- 7. The house (dark in both modes)                            -->
        <!-- ============================================================ -->
        <section id="house" class="th-foh th-section" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal>The house</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Every ticket carries a code. <em>Scan them in.</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10086;&#xFE0E;</div>
                </div>

                @php
                    // A code for each stub: three corner marks, their quiet margins, and a field of
                    // modules. Decoration, thirteen modules a side.
                    $thCode = function (int $seed): array {
                        $cells = [];
                        for ($y = 0; $y < 13; $y++) {
                            for ($x = 0; $x < 13; $x++) {
                                // Distance from the centre of the nearest corner mark, if this module is in one.
                                $cx = $x <= 5 ? 2 : ($x >= 7 ? 10 : null);
                                $cy = $y <= 5 ? 2 : ($y >= 7 ? 10 : null);
                                $inMark = $cx !== null && $cy !== null && ! ($cx === 10 && $cy === 10);
                                if ($inMark) {
                                    $ring = max(abs($x - $cx), abs($y - $cy));
                                    $cells[] = $ring === 2 || $ring === 0;
                                } else {
                                    $cells[] = hexdec(substr(md5($seed.':'.$x.':'.$y), 0, 2)) % 5 < 2;
                                }
                            }
                        }

                        return $cells;
                    };
                @endphp

                <div class="th-tix" data-reveal-group="100">
                    <article class="th-tick th-lit" data-reveal="panel">
                        <div class="th-tick-stub" aria-hidden="true">
                            <div class="th-qr">@foreach ($thCode(1) as $thOn)<i @class(['is-on' => $thOn])></i>@endforeach</div>
                            <span class="th-caps th-admit">Admit one</span>
                        </div>
                        <div class="th-tick-body">
                            <h3 class="th-d">On the door</h3>
                            <span class="th-caps th-tier">Free</span>
                            {{-- The wallet sentence only where the install can issue a pass: GoogleWalletService::isConfigured() gates the button itself. --}}
                            <p>Scan on the way in from any phone, on every plan. No extra hardware, and duplicates are caught rather than argued about.{{ \App\Services\Wallet\GoogleWalletService::isConfigured() ? ' Buyers can also add the ticket to Google Wallet, with the same code on it.' : '' }}</p>
                        </div>
                    </article>
                    <article class="th-tick th-lit" data-reveal="panel">
                        <div class="th-tick-stub" aria-hidden="true">
                            <div class="th-qr">@foreach ($thCode(2) as $thOn)<i @class(['is-on' => $thOn])></i>@endforeach</div>
                            <span class="th-caps th-admit">Admit one</span>
                        </div>
                        <div class="th-tick-body">
                            <h3 class="th-d">A live count</h3>
                            <span class="th-caps th-tier th-tier-pro">Pro</span>
                            <p>The check-in dashboard shows who is actually in, with a per-ticket-type breakdown, so you know before curtain rather than after.</p>
                        </div>
                    </article>
                    <article class="th-tick th-lit" data-reveal="panel">
                        <div class="th-tick-stub" aria-hidden="true">
                            <div class="th-qr">@foreach ($thCode(3) as $thOn)<i @class(['is-on' => $thOn])></i>@endforeach</div>
                            <span class="th-caps th-admit">Admit one</span>
                        </div>
                        <div class="th-tick-body">
                            <h3 class="th-d">One each</h3>
                            <span class="th-caps th-tier th-tier-pro">Pro</span>
                            <p>Per-attendee tickets give everyone in a party their own confirmation and their own code, instead of one person holding six.</p>
                        </div>
                    </article>
                </div>

                <p class="th-foh-foot" data-reveal>
                    For a free performance, registration with a capacity limit works on every plan. On a reserved-seating date, each scan also marks that seat as arrived on the box office map.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Everything else: programme notes                          -->
        <!-- ============================================================ -->
        <section id="rest" class="th-section" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal>Everything else</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Between opening and closing.
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10022;&#xFE0E;</div>
                </div>

                <div class="th-notes" data-reveal-group="90">
                    <!-- 1 -->
                    <article class="th-note" data-reveal>
                        <div class="th-note-head">
                            <h3 class="th-d">Tell the people who already come</h3>
                            <span class="th-caps th-tier">Free</span>
                        </div>
                        <p>Audiences follow your schedule and you email them when a season is announced or a run goes on sale. Open and click rates afterwards tell you whether the announcement landed.</p>
                        <p class="th-fine">The numbers worth knowing first: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.</p>
                    </article>

                    <!-- 2 -->
                    <article class="th-note" data-reveal>
                        <div class="th-note-head">
                            <h3 class="th-d">When a night sells out</h3>
                            <span class="th-caps th-tier th-tier-pro">Pro</span>
                        </div>
                        <p>Turn on the waitlist and people join once that performance is gone. If a return comes back, they are notified automatically.</p>
                    </article>

                    <!-- 3 -->
                    <article class="th-note" data-reveal>
                        <div class="th-note-head">
                            <h3 class="th-d">On the site you already have</h3>
                            <span class="th-caps th-tier">Free</span>
                        </div>
                        <p>Embed the calendar on your own site so the season lives where people look you up, and sync two ways with Google, Outlook and CalDAV.</p>
                        <p class="th-fine">Built-in analytics show page views, the devices people are on and where the traffic came from, and for each production how many tickets sold and how many people came through the door.</p>
                    </article>

                    <!-- 4 -->
                    <article class="th-note" data-reveal>
                        <div class="th-note-head">
                            <h3 class="th-d">Announce when you are ready</h3>
                            <span class="th-caps th-tier">Free</span>
                        </div>
                        <p>A production you have not announced sits on the calendar as a draft, visible to you and never published until you say so. Once it is public, and with the free "Notify me" card switched on, every performance offers "Tell me when tickets go on sale", so the people who saw the season announcement hear when you open sales. <a href="{{ marketing_url('/docs/tickets#interest-list') }}" class="th-a">How the interest list works</a></p>
                    </article>

                    <!-- 5 -->
                    <article class="th-note" data-reveal>
                        <div class="th-note-head">
                            <h3 class="th-d">The announcement image</h3>
                            <span class="th-caps th-tier">Free</span>
                        </div>
                        <p>Generate a graphic from a production in a story, square, portrait or landscape crop. It is built from the event, so the title and the dates are already right.</p>
                        <p class="th-fine">
                            Streaming a performance too? Mark it as an online event and paste the link to wherever you are streaming.
                            <a href="{{ marketing_url('/features/online-events') }}" class="th-a">How online events work</a>
                        </p>
                    </article>

                    <!-- 6 -->
                    <article class="th-note" data-reveal>
                        <div class="th-note-head">
                            <h3 class="th-d">After the run</h3>
                            <span class="th-caps th-tier">Free</span>
                        </div>
                        <p>Audiences add photos, video and comments to a production, all held in an approval queue before anything appears. Free covers 25 photos per schedule.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Perfect for: a row of boxes                               -->
        <!-- ============================================================ -->
        <section id="who" class="th-section th-alt" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <h2 class="th-d th-h2" data-reveal>
                        Built for every kind of <em>house</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.08s;">&#10086;&#xFE0E;</div>
                    <p class="th-sub" data-reveal style="--reveal-delay: 0.12s;">
                        Eighty seats or eight hundred, a run is a run.
                    </p>
                </div>

                @php
                    $thHouses = [
                        ['Community Theaters', 'Local productions, volunteer casts and beloved classics. Publish the run once and reach your audience directly.', 'for-community-theaters'],
                        ['Regional Theaters', 'Professional runs across a full season. Sell single performances and a pass that covers a whole run.', 'for-regional-theaters'],
                        ['Black Box Theaters', 'Intimate experimental work with short runs. Cap each performance and keep the studio on its own sub-schedule.', 'for-black-box-theaters'],
                        ['Dinner Theaters', 'An evening that is more than the show. Ask for the course choice on the ticket itself with custom questions at checkout.', 'for-dinner-theaters'],
                        ['Children\'s Theaters', 'Family productions and weekday matinees. Matinees run as their own event, so the school shows keep their own tickets.', 'for-childrens-theaters'],
                        ['Outdoor Amphitheaters', 'Shakespeare in the park and summer stock. Take a washed-out night out of the run with a date exception.', 'for-outdoor-theaters'],
                    ];
                @endphp

                <div class="th-boxes" data-reveal-group="70">
                    @foreach ($thHouses as [$thName, $thDesc, $thSlug])
                        @php $thPost = get_sub_audience_blog($thSlug); @endphp
                        <article class="th-box" data-reveal>
                            <h3 class="th-d">{{ $thName }}</h3>
                            <p>{{ $thDesc }}</p>
                            @if ($thPost)
                                <a href="{{ blog_url('/' . $thPost->slug) }}" class="th-caps" aria-label="Learn more about Event Schedule for {{ $thName }}">
                                    <span>Learn more {!! $thArrow !!}</span>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Three steps: a synopsis in three acts                    -->
        <!-- ============================================================ -->
        <section class="th-section">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal aria-hidden="true">Synopsis</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three steps
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10022;&#xFE0E;</div>
                </div>

                <div class="th-acts" data-reveal-group="120">
                    @foreach ([['01', 'Set the run', 'Create the production as a recurring event, pick the days it plays, and give it an end: a closing date or a number of performances.'], ['02', 'Mark the dark days', 'Add date exceptions for the nights you are dark, and add the matinee as its own event on the days it plays.'], ['03', 'Sell the run', 'Named ticket types for each price, and a season pass valid for every performance of the run, once each.']] as [$stepNum, $stepTitle, $stepBody])
                        <div class="th-act" data-reveal>
                            <span class="th-caps th-act-no" aria-hidden="true">Act</span>
                            <span class="th-act-fig" aria-hidden="true"></span>
                            <h3 class="th-d"><span class="sr-only">{{ $stepNum }}. </span>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. Key features: the company                                -->
        <!-- ============================================================ -->
        <section class="th-section th-alt">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal aria-hidden="true">The company</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                </div>

                @php
                    $thCompany = [
                        ['Recurring Events', 'Set a run once, with dark days and a closing performance', marketing_url('/features/recurring-events')],
                        ['Ticketing', 'Named ticket types, QR check-in, and zero platform fees', marketing_url('/features/ticketing')],
                        ['Allocated Seating', 'Draw the auditorium once and sell by the seat, on Enterprise', marketing_url('/features/allocated-seating')],
                        ['Passes', 'Season passes valid for every performance of a run, on Pro', marketing_url('/features/passes')],
                        ['Sub-schedules', 'Keep mainstage, studio and family programming apart', marketing_url('/features/sub-schedules')],
                        ['Newsletters', 'Email the people who follow your theater, with open rates', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <div class="th-cast" data-reveal>
                    @foreach ($thCompany as [$thPart, $thPlays, $thUrl])
                        <a href="{{ $thUrl }}">
                            <b>{{ $thPart }}</b>
                            <i aria-hidden="true"></i>
                            <span>{{ $thPlays }}</span>
                        </a>
                    @endforeach
                </div>
                <p class="th-centre" data-reveal>
                    <a href="{{ marketing_url('/features') }}" class="th-more th-caps">
                        See all features
                        {!! $thArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <div class="th-prices">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 12. Related pages: also playing                              -->
        <!-- ============================================================ -->
        <section class="th-section th-alt">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal aria-hidden="true">Also playing</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                </div>
                <div class="th-also" data-reveal-group="70">
                    @foreach ([['/for-theater-performers', 'Theater Performers'], ['/for-venues', 'Venues'], ['/for-dance-groups', 'Dance Groups'], ['/for-community-centers', 'Community Centers']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="th-leaf" data-reveal>
                            <span class="th-orn" aria-hidden="true" style="margin: 0; width: 5rem;">&#10022;&#xFE0E;</span>
                            <strong>For {{ $relName }}</strong>
                            <em class="th-caps">
                                Read more
                                {!! $thArrow !!}
                            </em>
                        </a>
                    @endforeach
                </div>
                <p class="th-centre" data-reveal>
                    <a href="{{ marketing_url('/use-cases') }}" class="th-more th-caps">
                        See all use cases
                        {!! $thArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 13. FAQ: notes and queries                                   -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="th-section" style="scroll-margin-top: 4rem;">
            <div class="th-wrap">
                <div class="th-head">
                    <p class="th-caps th-kicker" data-reveal aria-hidden="true">Notes and queries</p>
                    <h2 class="th-d th-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <div class="th-orn" aria-hidden="true" data-reveal style="--reveal-delay: 0.12s;">&#10086;&#xFE0E;</div>
                    <p class="th-sub" data-reveal style="--reveal-delay: 0.16s;">
                        What producers ask before they move a season across.
                    </p>
                </div>

                <div class="th-qa" data-reveal>
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

        <!-- ============================================================ -->
        <!-- 14. Finale: opening night                                    -->
        <!-- ============================================================ -->
        <section id="claim" class="th-finale" style="scroll-margin-top: 4rem;">
            <div class="th-pelmet" aria-hidden="true"></div>
            <div class="th-valance" aria-hidden="true"></div>
            <div class="th-wrap">
                <div class="th-card th-lit" data-reveal="panel">
                    <p class="th-caps th-kicker">Free forever</p>
                    <h2 class="th-d th-h2">
                        Set the run once. <em>Sell all fourteen.</em>
                    </h2>
                    <div class="th-orn" aria-hidden="true">&#10086;&#xFE0E;</div>
                    <p class="th-sub">
                        Publishing your season and its dates is free forever, as is free registration on every one of them and scanning people in at the door. {{ plan_price($proMonthly) }} a month is what puts a price on a seat, and it adds season passes and the live check-in dashboard, and nothing is taken from the door.
                    </p>

                    <div class="th-claim-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim th-claim">
                            <input id="es-claim-input" type="text" placeholder="your-theater" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="th-btn">
                            Create your calendar
                            {!! $thArrow !!}
                        </a>
                    </div>

                    <p class="th-ital th-card-fine">No credit card required</p>
                </div>
            </div>
        </section>

        <div class="th-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
