<x-marketing-layout>
    <x-slot name="title">Library Program Calendar | Story Time and Free Registration</x-slot>
    <x-slot name="description">Set story time up once as a recurring program, take out the weeks the branch is closed, and give every single date its own place count. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Libraries</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Libre Baskerville') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Old Standard TT') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lekton') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Libraries"
        description="Set a library program up once as a recurring event, exclude the dates the branch is closed, and take free registrations with a place limit counted separately for every date."
        audience="Libraries"
        keywords="library program calendar, library event schedule, story time scheduling, author event management, free library scheduling" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a library program calendar online with Event Schedule",
        "description": "Catalogue the program once and every date looks after itself.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Catalogue the program",
                "text": "Add the program once as a recurring event: the days it runs, the sub-schedule it belongs to, and an end date or a number of sessions."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Take out the closed days",
                "text": "Add date exceptions for public holidays and staff training days, and add extra dates for one-off sessions."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the places",
                "text": "Turn on free registration and set a place limit. The limit is counted separately for every date, so each session has its own list."
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
           For-libraries "The Stacks" styles.

           The page is made of what a library is made of: a bookcase of
           cloth spines, call numbers, the date-due slip, the call slip,
           the borrower's card. A program is a volume: catalogued once,
           shelved under its number, and stamped again each time it is
           due. The two shelves are the page's own furniture: the top
           one is the table of contents (each lettered spine is a
           section), and the second takes a volume down when you pick it,
           with radio inputs and :has() and no script.

           Everything is scoped under #lb with its own tokens. Paper
           objects (labels, slips, cards) keep their stock in both colour
           modes and are inked with the fixed --lb-o* colours; the room
           around them is what goes dark.
           ============================================================== */

        #lb {
            --lb-ground: #efe3c2;
            --lb-ground-2: #e6d8b2;
            --lb-card: #f8f1dc;
            --lb-ink: #1e1b16;
            --lb-ink-2: #473e30;
            --lb-ink-3: #655844;
            --lb-line: rgba(30, 27, 22, 0.24);
            --lb-hair: rgba(30, 27, 22, 0.12);
            --lb-accent: #6a2028;
            --lb-paper: #fbf6e6;
            --lb-paper-2: #dfcb96;
            --lb-oink: #1e1b16;
            --lb-oink-2: #473e30;
            --lb-oink-3: #655844;
            --lb-st-free: #1f5444;
            --lb-st-pro: #a8232a;
            --lb-st-ent: #5a3a12;
            --lb-red: #b3262b;
            --lb-blueblack: #25343b;
            --lb-foil: #e2c474;
            --lb-case-1: #2c1e11;
            --lb-case-2: #3b2917;
            --lb-wood-1: #b98a52;
            --lb-wood-2: #8d6238;
            --lb-wood-3: #5f4022;
            --lb-wood-4: #3e2913;
            --lb-serif: 'Libre Baskerville', Baskerville, 'Baskerville Old Face', Georgia, serif;
            --lb-ital: Baskerville, 'Baskerville Old Face', 'Hoefler Text', Georgia, 'Times New Roman', serif;
            --lb-spine: 'Old Standard TT', 'Libre Baskerville', Georgia, serif;
            --lb-typed: 'Lekton', 'Courier New', ui-monospace, monospace;
            --lb-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.5 0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)'/%3E%3C/svg%3E");
            --lb-inkmask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='140' height='140'%3E%3Cfilter id='m'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.7' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 -1.5 1.72'/%3E%3C/filter%3E%3Crect width='140' height='140' filter='url(%23m)'/%3E%3C/svg%3E");
            position: relative;
            background: var(--lb-ground);
            color: var(--lb-ink);
            font-family: var(--lb-serif);
            font-size: 1rem;
            line-height: 1.7;
        }
        .dark #lb {
            --lb-ground: #17120e;
            --lb-ground-2: #1d1711;
            --lb-card: #231b14;
            --lb-ink: #f1e6cc;
            --lb-ink-2: #d2c5a7;
            --lb-ink-3: #a99a7b;
            --lb-line: rgba(241, 230, 204, 0.22);
            --lb-hair: rgba(241, 230, 204, 0.1);
            --lb-accent: #e3c06e;
            --lb-paper: #ece1c4;
            --lb-paper-2: #cdb983;
            --lb-st-free: #8fd1b3;
            --lb-st-pro: #f2a05f;
            --lb-st-ent: #e3c06e;
            --lb-case-1: #100b07;
            --lb-case-2: #1c140c;
            --lb-wood-1: #8a6238;
            --lb-wood-2: #654524;
            --lb-wood-3: #3f2a15;
            --lb-wood-4: #24170b;
        }

        /* The bar above takes the manila of the room. */
        body > header.sticky {
            background-color: rgba(239, 227, 194, 0.9);
            border-bottom-color: rgba(30, 27, 22, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(23, 18, 14, 0.9);
            border-bottom-color: rgba(241, 230, 204, 0.12);
        }

        #lb ::selection { background: #6a2028; color: #f8f1dc; }
        #lb a:focus-visible,
        #lb summary:focus-visible,
        #lb input:focus-visible {
            outline: 3px solid var(--lb-accent);
            outline-offset: 3px;
        }
        .lb-vh {
            position: absolute;
            width: 1px;
            height: 1px;
            margin: -1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .lb-wrap { width: min(100% - 2.5rem, 74rem); margin-inline: auto; }
        .lb-section { padding-block: clamp(4rem, 8vw, 7rem); }
        .lb-alt { background: var(--lb-ground-2); }

        /* Typed matter: labels, call numbers, field names. */
        .lb-typed {
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.3;
        }
        /* The shelf mark that opens each section: a call-number label and the heading it files under. */
        .lb-mark { display: flex; align-items: center; gap: 0.9rem; margin-bottom: 1.4rem; }
        .lb-call {
            flex: none;
            padding: 0.35rem 0.55rem 0.2rem;
            background: var(--lb-paper);
            color: var(--lb-oink);
            box-shadow: 0 0 0 1px rgba(30, 27, 22, 0.6), 0 2px 0 rgba(30, 27, 22, 0.18);
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 0.78rem;
            line-height: 1.12;
            letter-spacing: 0.04em;
            text-align: center;
        }
        .lb-kicker { color: var(--lb-ink-3); }
        .lb-h2 {
            font-weight: 400;
            font-size: clamp(1.85rem, 3.5vw, 3rem);
            line-height: 1.16;
            letter-spacing: -0.005em;
            text-wrap: balance;
        }
        .lb-em {
            font-family: var(--lb-ital);
            font-style: italic;
            font-size: 1.08em;
            color: var(--lb-accent);
        }
        .lb-lede { margin-top: 1.25rem; max-width: 40rem; color: var(--lb-ink-2); font-size: 1.06rem; text-wrap: pretty; }
        .lb-head-c { text-align: center; }
        .lb-head-c .lb-mark { justify-content: center; }
        .lb-head-c .lb-lede { margin-inline: auto; }
        .lb-link {
            color: var(--lb-accent);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-thickness: 1.5px;
            text-underline-offset: 0.22em;
        }
        .lb-link:hover { text-decoration-thickness: 3px; }
        .lb-arrow-link { display: inline-flex; align-items: center; gap: 0.45rem; transition: gap 0.2s ease; }
        .lb-arrow-link:hover { gap: 0.75rem; }
        .lb-arrow-link svg { width: 1rem; height: 1rem; flex: none; }

        /* Rubber stamps: the plan a thing belongs to, in the ink of the desk. */
        .lb-stamp {
            display: inline-block;
            padding: 0.3rem 0.6rem 0.14rem;
            border: 2px solid currentColor;
            border-radius: 2px;
            color: var(--lb-st, var(--lb-st-free));
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 0.84rem;
            line-height: 1.2;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            white-space: nowrap;
            rotate: var(--r, -3deg);
            -webkit-mask-image: var(--lb-inkmask);
            mask-image: var(--lb-inkmask);
        }
        .lb-stamp-pro { --lb-st: var(--lb-st-pro); }
        .lb-stamp-ent { --lb-st: var(--lb-st-ent); }
        /* Paper keeps its stock, so a stamp on paper keeps its daytime ink. */
        .lb-obj {
            --lb-st-free: #1f5444;
            --lb-st-pro: #a8232a;
            --lb-st-ent: #5a3a12;
            color: var(--lb-oink);
        }

        /* Buttons: one bound in cloth with a gilt rule, one cut from label stock. */
        .lb-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 1rem 1.5rem 0.95rem;
            border-radius: 4px;
            font-family: var(--lb-serif);
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.2;
            transition: translate 0.2s ease, box-shadow 0.2s ease;
        }
        .lb-btn svg { width: 1.1rem; height: 1.1rem; flex: none; transition: translate 0.2s ease; }
        .lb-btn:hover { translate: 0 -2px; }
        .lb-btn-cloth {
            background:
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.05) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.08) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                #6a2028;
            color: #f8f1dc;
            box-shadow: inset 0 0 0 0.24rem #6a2028, inset 0 0 0 calc(0.24rem + 1px) rgba(226, 196, 116, 0.95), 0 0.7rem 1.2rem -0.7rem rgba(30, 20, 8, 0.75);
        }
        .lb-btn-cloth:hover svg { translate: 4px 0; }
        .lb-btn-paper { background: var(--lb-card); color: var(--lb-ink); box-shadow: inset 0 0 0 1.5px var(--lb-ink), 0 0.5rem 1rem -0.7rem rgba(30, 20, 8, 0.5); }
        .lb-btn-paper:hover svg { translate: 0 4px; }

        /* ---------------------------------------------------------------
           The bookcase: a bay of dark wood, and a shelf that lays its
           own boards however many times the books wrap.
           --------------------------------------------------------------- */
        .lb-case {
            position: relative;
            background: linear-gradient(180deg, var(--lb-case-1), var(--lb-case-2) 55%, var(--lb-case-1));
            border-top: 0.85rem solid var(--lb-wood-3);
            box-shadow: inset 0 0.9rem 1.2rem -0.6rem rgba(0, 0, 0, 0.8);
        }
        .lb-case::before {
            content: "";
            position: absolute;
            inset: -0.85rem 0 auto 0;
            height: 0.85rem;
            background: linear-gradient(180deg, var(--lb-wood-1), var(--lb-wood-2) 40%, var(--lb-wood-4));
        }
        .lb-shelf {
            --lb-row: 17.6rem;
            --lb-board: 1.1rem;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            column-gap: 1px;
            row-gap: var(--lb-board);
            padding: 0 max(1.25rem, calc((100% - 88rem) / 2)) var(--lb-board);
            background-image:
                linear-gradient(180deg, rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0) 1.6rem),
                linear-gradient(180deg, rgba(0, 0, 0, 0) calc(100% - var(--lb-board)), var(--lb-wood-1) calc(100% - var(--lb-board)), var(--lb-wood-2) calc(100% - var(--lb-board) + 2px), var(--lb-wood-3) calc(100% - 0.3rem), var(--lb-wood-4) 100%);
            background-size: 100% calc(var(--lb-row) + var(--lb-board));
            background-repeat: repeat-y;
        }
        .lb-spine {
            --h: 15rem;
            --w: 3rem;
            --cloth: #2f4a3c;
            --letter: #f4e7c3;
            --rule: var(--lb-foil);
            position: relative;
            flex: none;
            box-sizing: border-box;
            width: calc(var(--w) * var(--lb-k, 1));
            height: calc(var(--h) * var(--lb-k, 1));
            margin-top: calc(var(--lb-row) - var(--h) * var(--lb-k, 1));
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.45rem;
            padding: 1.45rem 0 0.5rem;
            border-radius: 4px 4px 1px 1px / 7px 7px 1px 1px;
            color: var(--letter);
            background:
                linear-gradient(90deg, rgba(0, 0, 0, 0.36) 0, rgba(255, 255, 255, 0.14) 16%, rgba(255, 255, 255, 0.03) 38%, rgba(0, 0, 0, 0) 62%, rgba(0, 0, 0, 0.34) 100%),
                linear-gradient(var(--rule), var(--rule)) 50% 0.7rem / calc(100% - 0.6rem) 1.5px no-repeat,
                linear-gradient(var(--rule), var(--rule)) 50% 0.98rem / calc(100% - 0.6rem) 1px no-repeat,
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.05) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.08) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                var(--cloth);
            box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.28);
            transform-origin: 50% 100%;
            transition: translate 0.4s cubic-bezier(0.3, 1.3, 0.5, 1), transform 0.4s cubic-bezier(0.3, 1.1, 0.5, 1), box-shadow 0.3s ease;
        }
        .lb-c-g { --cloth: #2f4a3c; }
        .lb-c-o { --cloth: #6a2028; }
        .lb-c-t { --cloth: #1f4a4f; }
        .lb-c-k { --cloth: #28231e; }
        .lb-c-r { --cloth: #8a4a1f; }
        .lb-c-m { --cloth: #5a5a2c; }
        .lb-c-n { --cloth: #b8946a; --letter: #231d14; --rule: #5d4521; }
        .lb-spine-title {
            flex: 1 1 auto;
            min-height: 0;
            writing-mode: vertical-rl;
            font-family: var(--lb-spine);
            font-weight: 700;
            font-size: var(--lb-sfs, 0.76rem);
            line-height: 1;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            text-align: center;
            white-space: nowrap;
        }
        .lb-spine-call {
            flex: none;
            width: calc(100% - 0.5rem);
            padding: 0.26rem 0 0.12rem;
            background: #f7f0da;
            color: #1e1b16;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.35);
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 0.62rem;
            line-height: 1.1;
            text-align: center;
        }
        /* Volumes with nothing lettered on them: they only fill the shelf. */
        .lb-fill { padding: 0; }
        .lb-bands {
            background:
                linear-gradient(180deg, rgba(0, 0, 0, 0.3), rgba(255, 255, 255, 0.16) 50%, rgba(0, 0, 0, 0.3)) 0 18% / 100% 0.34rem no-repeat,
                linear-gradient(180deg, rgba(0, 0, 0, 0.3), rgba(255, 255, 255, 0.16) 50%, rgba(0, 0, 0, 0.3)) 0 34% / 100% 0.34rem no-repeat,
                linear-gradient(180deg, rgba(0, 0, 0, 0.3), rgba(255, 255, 255, 0.16) 50%, rgba(0, 0, 0, 0.3)) 0 78% / 100% 0.34rem no-repeat,
                linear-gradient(var(--rule), var(--rule)) 50% 52% / calc(100% - 0.5rem) 2.2rem no-repeat,
                linear-gradient(90deg, rgba(0, 0, 0, 0.36) 0, rgba(255, 255, 255, 0.14) 16%, rgba(255, 255, 255, 0.03) 38%, rgba(0, 0, 0, 0) 62%, rgba(0, 0, 0, 0.34) 100%),
                var(--cloth);
        }
        .lb-lean { rotate: 9deg; transform-origin: 100% 100%; margin-inline: 1.3rem 0.1rem; }
        /* A marker card where a volume is out. */
        .lb-out {
            --h: 12.4rem;
            --w: 1.25rem;
            background: #e6d39c;
            border-radius: 2px 2px 0 0;
            color: #473e30;
            padding: 0.6rem 0 0.4rem;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.25);
        }
        .lb-out .lb-spine-title { font-family: var(--lb-typed); font-size: 0.6rem; letter-spacing: 0.2em; }
        /* Two volumes lying flat, the way the last ones in always are. */
        .lb-pile {
            flex: none;
            display: grid;
            justify-items: start;
            width: calc(8.6rem * var(--lb-k, 1));
            height: 3.9rem;
            margin-top: calc(var(--lb-row) - 3.9rem);
            margin-inline: 0.5rem;
        }
        .lb-pile i {
            display: block;
            border-radius: 2px 5px 5px 2px / 2px 9px 9px 2px;
            background:
                linear-gradient(var(--lb-foil), var(--lb-foil)) 1.1rem 50% / 1.5px calc(100% - 0.5rem) no-repeat,
                linear-gradient(var(--lb-foil), var(--lb-foil)) calc(100% - 1.4rem) 50% / 1.5px calc(100% - 0.5rem) no-repeat,
                linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(0, 0, 0, 0) 45%, rgba(0, 0, 0, 0.36)),
                var(--cloth);
        }
        .lb-pile i:first-child { width: 86%; height: 1.75rem; --cloth: #1f4a4f; margin-inline-start: 6%; }
        .lb-pile i:last-child { width: 100%; height: 2.15rem; --cloth: #8a4a1f; }
        /* The steel bookend. */
        .lb-end {
            flex: none;
            position: relative;
            width: 2.9rem;
            height: 8.6rem;
            margin-top: calc(var(--lb-row) - 8.6rem);
            background:
                linear-gradient(90deg, #6c7275, #c3c8ca 40%, #8b9193) 0 0 / 0.34rem 100% no-repeat,
                linear-gradient(180deg, #b6bbbd, #6c7275) 0 100% / 100% 0.3rem no-repeat;
        }

        /* ---------------------------------------------------------------
           Hero: the reading room. Copy and one volume on the wall side,
           then the top shelf, which is the contents of the page.
           --------------------------------------------------------------- */
        .lb-hero { position: relative; overflow: clip; }
        .lb-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: var(--lb-grain);
            opacity: 0.2;
            mix-blend-mode: multiply;
            pointer-events: none;
        }
        .dark .lb-hero::before {
            background-image: radial-gradient(60rem 34rem at 88% 100%, rgba(255, 205, 130, 0.16), rgba(255, 205, 130, 0) 70%);
            opacity: 1;
            mix-blend-mode: normal;
        }
        .lb-hero-grid {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 3rem;
            align-items: center;
            padding-block: clamp(2rem, 3.4vw, 2.9rem) clamp(2.2rem, 3.4vw, 2.9rem);
        }
        @media (min-width: 980px) {
            .lb-hero-grid { grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr); gap: 3.5rem; }
        }
        .lb-eyebrow {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.3rem;
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 0.84rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.3;
            color: var(--lb-ink-2);
        }
        .lb-eyebrow::before {
            content: "027.4";
            padding: 0.34rem 0.55rem 0.18rem;
            background: var(--lb-paper);
            color: #1e1b16;
            box-shadow: 0 0 0 1px rgba(30, 27, 22, 0.6), 0 2px 0 rgba(30, 27, 22, 0.18);
            letter-spacing: 0.04em;
        }
        .lb-h1 {
            font-weight: 400;
            font-size: clamp(2.2rem, 4.7vw, 4.15rem);
            line-height: 1.1;
            letter-spacing: -0.012em;
        }
        .lb-h1 b { font-weight: 700; color: var(--lb-accent); }
        .lb-hero .lb-lede { font-size: clamp(1.02rem, 1.35vw, 1.14rem); max-width: 41rem; margin-top: 1.3rem; }
        .lb-cta { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1.7rem; }

        /* The volume on the table: a cloth board, with the record on its label. */
        .lb-board {
            --lb-pick: #2f4a3c;
            position: relative;
            border-radius: 3px 7px 7px 3px;
            background:
                linear-gradient(90deg, rgba(0, 0, 0, 0.3) 0, rgba(255, 255, 255, 0.1) 0.5rem, rgba(0, 0, 0, 0.22) 1.15rem, rgba(0, 0, 0, 0) 1.6rem),
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.05) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.08) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                var(--lb-pick);
            box-shadow:
                0.14rem 0.14rem 0 #efe6cd, 0.28rem 0.28rem 0 #d8cdb0, 0.42rem 0.42rem 0 #efe6cd, 0.56rem 0.56rem 0 #c9bd9c,
                0.72rem 0.72rem 0 rgba(0, 0, 0, 0.55), 0 2rem 2.6rem -1.4rem rgba(24, 14, 4, 0.75);
            color: #f4e7c3;
            transition: background-color 0.4s ease;
        }
        .lb-board::before {
            content: "";
            position: absolute;
            inset: 1rem 1rem 1rem 2.1rem;
            border: 1.5px solid rgba(226, 196, 116, 0.9);
            outline: 1px solid rgba(226, 196, 116, 0.6);
            outline-offset: 3px;
            pointer-events: none;
        }
        .lb-hero-book { min-width: 0; }
        .lb-hero-board {
            width: min(100%, 21.5rem);
            margin-inline: auto;
            padding: 1.9rem 1.8rem 2rem 2.9rem;
            rotate: -2.5deg;
        }
        .lb-board-title {
            display: block;
            font-family: var(--lb-spine);
            font-weight: 700;
            font-size: 1.12rem;
            line-height: 1.15;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-align: center;
            color: #e2c474;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.45);
        }
        .lb-board-sub { display: block; margin-top: 0.4rem; text-align: center; font-size: 0.72rem; line-height: 1.5; color: #e9dcb8; text-wrap: balance; }
        .lb-plate {
            position: relative;
            margin-top: 1rem;
            padding: 0.8rem 0.9rem 0.75rem;
            background: var(--lb-paper);
            color: var(--lb-oink);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.3), 0 0.6rem 1rem -0.7rem rgba(0, 0, 0, 0.7);
            rotate: 1.2deg;
        }
        .lb-plate-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
        .lb-plate-call { font-family: var(--lb-typed); font-weight: 700; font-size: 0.8rem; line-height: 1.15; color: var(--lb-red); }
        .lb-plate-kind { font-family: var(--lb-typed); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--lb-oink-3); text-align: right; }
        .lb-plate dl { margin-top: 0.55rem; border-top: 1px solid rgba(30, 27, 22, 0.3); }
        .lb-plate dl div { display: flex; justify-content: space-between; gap: 1rem; padding-block: 0.24rem; border-bottom: 1px solid rgba(30, 27, 22, 0.16); font-size: 0.78rem; line-height: 1.35; }
        .lb-plate dt { font-family: var(--lb-typed); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--lb-oink-3); }
        .lb-plate dd { font-weight: 700; text-align: right; }
        .lb-plate-strands { display: flex; flex-wrap: wrap; gap: 0.3rem 0.9rem; margin-top: 0.7rem; font-family: var(--lb-typed); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.06em; color: var(--lb-oink-2); }
        .lb-plate-strands span { display: inline-flex; align-items: center; gap: 0.35rem; }
        .lb-plate-strands i { width: 0.42rem; height: 0.95rem; border-radius: 1px; background: var(--c); }
        .lb-hero-cap { max-width: 22rem; margin: 1.5rem auto 0; text-align: center; font-size: 0.84rem; line-height: 1.6; color: var(--lb-ink-3); }

        /* The top shelf. Each lettered spine is a section of the page. */
        .lb-shelf-hero a.lb-spine:hover,
        .lb-shelf-hero a.lb-spine:focus-visible { translate: 0 -0.7rem; box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.28), 0 1rem 0.8rem -0.6rem rgba(0, 0, 0, 0.7); }
        #lb .lb-shelf a.lb-spine:focus-visible { outline: 3px solid #f4e7c3; outline-offset: 2px; }
        .lb-pile, .lb-end { transform-origin: 50% 100%; }
        html.es-anim #lb .lb-shelf-hero > :not(.lb-lamp) {
            animation: lb-shelve 0.75s cubic-bezier(0.2, 0.9, 0.3, 1.1) both;
            animation-delay: calc(var(--i, 0) * 38ms + 0.35s);
        }
        @keyframes lb-shelve {
            from { opacity: 0; scale: 1 0.82; }
            to { opacity: 1; scale: 1 1; }
        }

        /* The banker's lamp at the end of the shelf: dark by day, lit after closing. */
        .lb-lamp {
            position: relative;
            flex: none;
            font-size: 1rem;
            width: 10.4em;
            height: 13.4em;
            margin-top: calc(var(--lb-row) - 13.4em);
            margin-inline-start: auto;
        }
        .lb-lamp i { position: absolute; left: 50%; translate: -50% 0; }
        .lb-lamp-base {
            bottom: 0;
            width: 6.2em;
            height: 0.95em;
            border-radius: 3em 3em 0.2em 0.2em / 1em 1em 0.2em 0.2em;
            background: linear-gradient(90deg, #6b4d17, #e2c474 32%, #f6e6ae 48%, #b8913c 70%, #5f4313);
        }
        .lb-lamp-stem { bottom: 0.85em; width: 0.5em; height: 8.6em; border-radius: 0.3em; background: linear-gradient(90deg, #6b4d17, #f0d98e 45%, #8e6c26); }
        .lb-lamp-arm { bottom: 9.2em; width: 8.4em; height: 0.42em; border-radius: 0.3em; background: linear-gradient(180deg, #f0d98e, #8e6c26); }
        .lb-lamp-shade {
            bottom: 9.35em;
            width: 9.8em;
            height: 3.7em;
            border-radius: 4.9em 4.9em 0.7em 0.7em / 3.5em 3.5em 0.7em 0.7em;
            background: linear-gradient(180deg, #62b08a, #2f7a58 46%, #1b4f37);
            box-shadow: inset 0 0.55em 0.6em -0.35em rgba(255, 255, 255, 0.5), inset 0 -0.45em 0.5em -0.25em rgba(0, 0, 0, 0.4);
        }
        .lb-lamp-pool {
            bottom: -3em;
            width: 54em;
            height: 34em;
            border-radius: 50%;
            background: radial-gradient(closest-side, rgba(255, 216, 146, 0.42), rgba(255, 200, 120, 0.14) 46%, rgba(255, 200, 120, 0) 72%);
            mix-blend-mode: screen;
            pointer-events: none;
            opacity: 0;
        }
        .dark .lb-lamp-pool { opacity: 1; }
        .dark .lb-lamp-shade {
            background: linear-gradient(180deg, #8fe0b6, #3fa277 46%, #216a49);
            box-shadow: inset 0 0.55em 0.6em -0.35em rgba(255, 255, 255, 0.7), inset 0 -0.45em 0.5em -0.25em rgba(0, 0, 0, 0.3), 0 0.5em 2.2em 0.2em rgba(255, 214, 140, 0.5);
        }
        html.es-anim.dark #lb .lb-lamp-pool { animation: lb-lamp-on 1.6s ease-out 1.1s both; }
        @keyframes lb-lamp-on {
            0% { opacity: 0; }
            12% { opacity: 0.8; }
            20% { opacity: 0.2; }
            34% { opacity: 1; }
            100% { opacity: 1; }
        }

        /* The shelf edge: brass label holders, one for each kind of program. */
        .lb-edge { position: relative; padding-block: 0.8rem 0.75rem; background: linear-gradient(180deg, var(--lb-wood-3), var(--lb-wood-4)); }
        .lb-edge .es-marquee-track { gap: 0.9rem; padding-right: 0.9rem; align-items: center; }
        .lb-holder {
            flex: none;
            padding: 0.42rem 0.9rem 0.26rem;
            background: #f7f0da;
            color: #1e1b16;
            border: 0.2rem solid;
            border-image: linear-gradient(180deg, #f1dc9c, #b8913c 50%, #7a5a1f) 1;
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.2;
            white-space: nowrap;
        }

        /* ---------------------------------------------------------------
           One volume, one program: pick a spine and it comes down.
           --------------------------------------------------------------- */
        .lb-vols { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.25rem; margin-top: clamp(2.5rem, 5vw, 4rem); align-items: end; }
        .lb-vols-case { width: fit-content; max-width: 100%; margin-inline: auto; }
        .lb-vols-case .lb-shelf { --lb-row: 23.4rem; --lb-sfs: 0.9rem; flex-wrap: nowrap; padding-inline: 1.4rem 1rem; }
        .lb-vol { cursor: pointer; }
        .lb-vol::after {
            content: "";
            position: absolute;
            left: 7%;
            right: 7%;
            top: -0.42rem;
            height: 0.48rem;
            border-radius: 2px 2px 0 0;
            background: repeating-linear-gradient(90deg, #efe6cd 0 1px, #cfc4a6 1px 2px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .lb-vol:hover { translate: 0 -0.45rem; }
        .lb-vol-input:checked + .lb-vol {
            translate: 0 -1.5rem;
            transform: perspective(42rem) rotateX(-7deg);
            box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.28), 0 1.6rem 1rem -0.8rem rgba(0, 0, 0, 0.75);
        }
        .lb-vol-input:checked + .lb-vol::after { opacity: 1; }
        #lb .lb-vol-input:focus-visible + .lb-vol { outline: 3px solid #f4e7c3; outline-offset: 3px; }
        .lb-vols-hint { display: none; margin-top: 1rem; text-align: center; color: var(--lb-ink-3); }

        /* Without a wide screen or :has(), every entry is simply listed. */
        .lb-entries { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; min-width: 0; }
        .lb-entry {
            position: relative;
            padding: 1.35rem 1.4rem 1.4rem;
            background: var(--lb-paper);
            box-shadow: 0 0 0 1px rgba(30, 27, 22, 0.2), 0 0.8rem 1.2rem -1rem rgba(30, 20, 8, 0.6);
        }
        .lb-entry-field { display: flex; align-items: center; gap: 0.6rem; color: var(--lb-oink-3); }
        .lb-entry-field i { flex: none; width: 0.55rem; height: 1.2rem; border-radius: 1px 1px 0 0; background: var(--c); }
        .lb-entry h3 { margin-top: 0.5rem; font-size: 1.45rem; font-weight: 700; line-height: 1.2; }
        .lb-entry p:last-child { margin-top: 0.7rem; color: var(--lb-oink-2); }
        @media (min-width: 640px) and (max-width: 979.98px) {
            .lb-entries { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 639.98px) {
            .lb-vols-case .lb-shelf { --lb-k: 0.72; --lb-row: 16rem; --lb-sfs: 0.64rem; padding-inline: 0.9rem 0.6rem; }
            .lb-vols-case .lb-spine { padding-top: 1.3rem; }
        }
        /* Under 390px the six volumes fill the shelf from side to side, so the bookend keeps
           its upright and gives up most of its foot, which otherwise sticks out of the case. */
        @media (max-width: 389.98px) {
            .lb-vols-case .lb-end { width: 1rem; }
        }
        @media (min-width: 980px) {
            @supports selector(:has(*)) {
                .lb-vols { grid-template-columns: auto minmax(0, 1fr); gap: 4rem; }
                .lb-vols-case { margin-inline: 0; }
                .lb-vols-hint { display: block; }
                .lb-entries {
                    position: relative;
                    gap: 0;
                    align-items: center;
                    min-height: 23rem;
                    padding: 2.6rem 2.4rem 2.7rem 3.5rem;
                    border-radius: 3px 7px 7px 3px;
                    background:
                        linear-gradient(90deg, rgba(0, 0, 0, 0.3) 0, rgba(255, 255, 255, 0.1) 0.5rem, rgba(0, 0, 0, 0.22) 1.15rem, rgba(0, 0, 0, 0) 1.6rem),
                        repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.05) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                        repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.08) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                        var(--lb-pick, #2f4a3c);
                    box-shadow:
                        0.14rem 0.14rem 0 #efe6cd, 0.28rem 0.28rem 0 #d8cdb0, 0.42rem 0.42rem 0 #efe6cd, 0.56rem 0.56rem 0 #c9bd9c,
                        0.72rem 0.72rem 0 rgba(0, 0, 0, 0.55), 0 2rem 2.6rem -1.4rem rgba(24, 14, 4, 0.75);
                    transition: background-color 0.45s ease;
                }
                .lb-entries::before {
                    content: "";
                    position: absolute;
                    inset: 1.1rem 1.1rem 1.1rem 2.2rem;
                    border: 1.5px solid rgba(226, 196, 116, 0.9);
                    outline: 1px solid rgba(226, 196, 116, 0.6);
                    outline-offset: 3px;
                    pointer-events: none;
                }
                .lb-entry {
                    grid-area: 1 / 1;
                    padding: 1.7rem 1.8rem 1.8rem;
                    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.3), 0 0.8rem 1.2rem -0.8rem rgba(0, 0, 0, 0.75);
                    opacity: 0;
                    translate: 0 0.7rem;
                    pointer-events: none;
                    transition: opacity 0.35s ease, translate 0.45s cubic-bezier(0.22, 1, 0.36, 1);
                }
                .lb-entry h3 { font-size: 1.75rem; }
                .lb-entry p:last-child { font-size: 1.04rem; }
                .lb-vols:has(#lb-vol-1:checked) #lb-entry-1,
                .lb-vols:has(#lb-vol-2:checked) #lb-entry-2,
                .lb-vols:has(#lb-vol-3:checked) #lb-entry-3,
                .lb-vols:has(#lb-vol-4:checked) #lb-entry-4,
                .lb-vols:has(#lb-vol-5:checked) #lb-entry-5,
                .lb-vols:has(#lb-vol-6:checked) #lb-entry-6 { opacity: 1; translate: 0 0; pointer-events: auto; }
                .lb-vols:has(#lb-vol-2:checked) { --lb-pick: #6a2028; }
                .lb-vols:has(#lb-vol-3:checked) { --lb-pick: #1f4a4f; }
                .lb-vols:has(#lb-vol-4:checked) { --lb-pick: #8a4a1f; }
                .lb-vols:has(#lb-vol-5:checked) { --lb-pick: #28231e; }
                .lb-vols:has(#lb-vol-6:checked) { --lb-pick: #5a5a2c; }
            }
        }
        @supports (animation-timeline: view()) {
            html.es-anim #lb .lb-vols-case .lb-shelf > .lb-spine,
            html.es-anim #lb .lb-vols-case .lb-shelf > .lb-end {
                animation: lb-shelve linear both;
                animation-timeline: view();
                animation-range: entry calc(8% + var(--i, 0) * 5%) entry calc(46% + var(--i, 0) * 5%);
            }
        }

        /* ---------------------------------------------------------------
           The date-due slip, in its pocket, on the endpaper
           --------------------------------------------------------------- */
        .lb-endpaper {
            background-color: var(--lb-ground-2);
            background-image:
                radial-gradient(circle, rgba(106, 32, 40, 0.16) 0 1.3px, rgba(0, 0, 0, 0) 1.7px),
                radial-gradient(circle, rgba(47, 74, 60, 0.16) 0 1.3px, rgba(0, 0, 0, 0) 1.7px);
            background-size: 1.2rem 1.2rem;
            background-position: 0 0, 0.6rem 0.6rem;
        }
        .dark .lb-endpaper {
            background-image:
                radial-gradient(circle, rgba(226, 196, 116, 0.13) 0 1.3px, rgba(0, 0, 0, 0) 1.7px),
                radial-gradient(circle, rgba(143, 209, 179, 0.1) 0 1.3px, rgba(0, 0, 0, 0) 1.7px);
        }
        .lb-slip-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; margin-top: clamp(2.5rem, 5vw, 4rem); align-items: start; }
        @media (min-width: 940px) { .lb-slip-grid { grid-template-columns: minmax(0, 24rem) minmax(0, 1fr); gap: 4.5rem; } }
        .lb-pocketed { position: relative; width: min(100%, 21.5rem); margin-inline: auto; padding-bottom: 0.4rem; }
        .lb-slip {
            position: relative;
            padding: 1.15rem 1.1rem 7.3rem;
            background: var(--lb-paper);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.25), 0 1.1rem 1.6rem -1.1rem rgba(30, 20, 8, 0.7);
            rotate: -1.2deg;
        }
        .lb-slip-head { display: flex; justify-content: space-between; padding-bottom: 0.45rem; border-bottom: 2px solid var(--lb-oink); }
        .lb-slip li { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 0.6rem; min-height: 2.75rem; border-bottom: 1px solid rgba(37, 52, 59, 0.3); }
        .lb-due {
            display: inline-block;
            min-width: 4.6rem;
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 1.22rem;
            line-height: 1;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--lb-red);
            rotate: var(--r, 0deg);
            opacity: var(--o, 1);
            -webkit-mask-image: var(--lb-inkmask);
            mask-image: var(--lb-inkmask);
        }
        .lb-due-extra { color: var(--lb-blueblack); }
        .lb-due-void { color: var(--lb-oink-3); font-size: 0.86rem; letter-spacing: 0.08em; text-decoration: line-through; -webkit-mask-image: none; mask-image: none; }
        .lb-due-day { font-family: var(--lb-typed); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--lb-oink-3); }
        .lb-due-count { text-align: right; line-height: 1.2; }
        .lb-due-count b { display: block; font-size: 0.86rem; }
        .lb-due-count small { display: block; font-family: var(--lb-typed); font-weight: 700; font-size: 0.68rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--lb-oink-3); }
        .lb-slip-foot { margin-top: 0.7rem; text-align: right; color: var(--lb-oink-3); font-size: 0.72rem; }
        .lb-pocket-shadow { position: absolute; inset: auto -0.9rem 0 -0.9rem; height: 6.9rem; filter: drop-shadow(0 -0.2rem 0.25rem rgba(30, 20, 8, 0.32)); pointer-events: none; }
        .lb-pocket {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            padding-top: 1.6rem;
            background: linear-gradient(180deg, var(--lb-paper-2), #cbb57c);
            color: var(--lb-oink-2);
            -webkit-mask-image: radial-gradient(ellipse 30% 2.3rem at 50% 0, rgba(0, 0, 0, 0) 97%, #000 100%);
            mask-image: radial-gradient(ellipse 30% 2.3rem at 50% 0, rgba(0, 0, 0, 0) 97%, #000 100%);
            font-size: 0.7rem;
            text-align: center;
        }
        .lb-slip-note { max-width: 21.5rem; margin: 1.6rem auto 0; font-size: 0.9rem; color: var(--lb-ink-2); }
        html.es-anim #lb [data-reveal]:not(.is-revealed) .lb-due:not(.lb-due-void) { opacity: 0; scale: 1.5; }
        .lb-due { transition: opacity 0.25s ease, scale 0.3s cubic-bezier(0.3, 1.4, 0.5, 1); transition-delay: calc(var(--n, 0) * 0.16s + 0.3s); }

        .lb-rules { border-bottom: 1px solid var(--lb-line); }
        .lb-rule { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.4rem 1.2rem; align-items: baseline; padding-block: 1.45rem 1.5rem; border-top: 1px solid var(--lb-line); }
        .lb-rule h3 { font-size: 1.25rem; font-weight: 700; line-height: 1.3; }
        .lb-rule p { grid-column: 1 / -1; color: var(--lb-ink-2); max-width: 38rem; }
        .lb-slip-after { margin-top: 1.75rem; color: var(--lb-ink-2); }

        /* ---------------------------------------------------------------
           The shelf list: a week of the branch in the register
           --------------------------------------------------------------- */
        .lb-ledger {
            position: relative;
            margin-top: clamp(2.25rem, 4vw, 3.5rem);
            background-color: var(--lb-card);
            background-image: linear-gradient(90deg, rgba(0, 0, 0, 0) 2.7rem, rgba(179, 38, 43, 0.5) 2.7rem, rgba(179, 38, 43, 0.5) calc(2.7rem + 1px), rgba(0, 0, 0, 0) calc(2.7rem + 1px), rgba(0, 0, 0, 0) calc(2.7rem + 4px), rgba(179, 38, 43, 0.5) calc(2.7rem + 4px), rgba(179, 38, 43, 0.5) calc(2.7rem + 5px), rgba(0, 0, 0, 0) calc(2.7rem + 5px));
            box-shadow: 0 0 0 1px var(--lb-line), 0 1.4rem 2rem -1.5rem rgba(30, 20, 8, 0.55);
        }
        .lb-ledger table { width: 100%; border-collapse: collapse; }
        .lb-ledger caption { caption-side: bottom; }
        .lb-ledger thead th { padding: 1.05rem 1rem 0.75rem; border-bottom: 2px solid var(--lb-ink); text-align: left; color: var(--lb-ink-3); }
        .lb-ledger tbody th,
        .lb-ledger tbody td { padding: 0.95rem 1rem 0.9rem; border-bottom: 1px solid var(--lb-line); text-align: left; vertical-align: baseline; }
        .lb-ledger tbody tr:last-child th,
        .lb-ledger tbody tr:last-child td { border-bottom: 0; }
        .lb-ledger th:first-child { padding-left: 3.5rem; }
        .lb-ledger tbody th { font-weight: 700; font-size: 1.05rem; }
        .lb-ledger tbody th span { display: none; }
        .lb-ledger td { font-family: var(--lb-typed); font-weight: 700; font-size: 0.94rem; color: var(--lb-ink-2); }
        .lb-strand { display: inline-flex; align-items: center; gap: 0.5rem; }
        .lb-strand i { flex: none; width: 0.5rem; height: 1.15rem; border-radius: 1px 1px 0 0; background: var(--c); box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.25); }
        .lb-signup { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; color: var(--lb-ink); }
        .lb-ledger tbody tr { transition: background-color 0.2s ease; }
        .lb-ledger tbody tr:hover { background-color: var(--lb-hair); }
        @media (max-width: 759.98px) {
            .lb-ledger { background-image: none; }
            .lb-ledger thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); }
            .lb-ledger thead th { position: absolute; left: 0; top: 0; width: 1px; height: 1px; padding: 0; overflow: hidden; }
            .lb-ledger table, .lb-ledger tbody { display: block; }
            .lb-ledger tbody tr { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.15rem 1rem; padding: 1rem 1.1rem 0.9rem; border-bottom: 1px solid var(--lb-line); }
            .lb-ledger tbody tr:last-child { border-bottom: 0; }
            .lb-ledger tbody th, .lb-ledger tbody td { padding: 0; border: 0; }
            .lb-ledger th:first-child { padding-left: 0; }
            .lb-ledger tbody th { grid-column: 1 / -1; }
            .lb-ledger tbody th span { display: block; font-family: var(--lb-typed); font-size: 0.74rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--lb-ink-3); }
            .lb-ledger td.lb-td-strand { display: none; }
            .lb-ledger td.lb-td-places { text-align: right; }
            .lb-ledger td.lb-td-places::before { content: "Places "; color: var(--lb-ink-3); }
            .lb-ledger td.lb-td-signup { grid-column: 1 / -1; }
        }
        .lb-notes { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem 3rem; margin-top: 2rem; color: var(--lb-ink-2); font-size: 0.94rem; }
        @media (min-width: 760px) { .lb-notes { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        /* ---------------------------------------------------------------
           The meeting room: a call slip from the pad on the desk
           --------------------------------------------------------------- */
        .lb-room-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 940px) { .lb-room-grid { grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); gap: 4.5rem; } }
        .lb-ticks { margin-top: 1.75rem; display: grid; gap: 0.95rem; }
        .lb-ticks li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: 0.6rem; color: var(--lb-ink-2); }
        .lb-ticks li::before { content: "\2713"; font-family: var(--lb-typed); font-weight: 700; color: var(--lb-st-free); line-height: 1.7; }
        .lb-not { margin-top: 1.9rem; padding: 1.1rem 1.25rem 1.15rem; background: var(--lb-card); box-shadow: 0 0 0 1px var(--lb-line); color: var(--lb-ink-2); font-size: 0.94rem; }
        .lb-pad {
            position: relative;
            width: min(100%, 23rem);
            margin-inline: auto;
            padding: 0 1.35rem 1.4rem;
            background: #f6edc6;
            box-shadow:
                0 0 0 1px rgba(30, 27, 22, 0.22),
                0.3rem 0.35rem 0 -1px #efe5bb, 0.3rem 0.35rem 0 0 rgba(30, 27, 22, 0.22),
                0.6rem 0.7rem 0 -1px #e8dcae, 0.6rem 0.7rem 0 0 rgba(30, 27, 22, 0.22),
                0 2rem 2.2rem -1.4rem rgba(30, 20, 8, 0.7);
            rotate: 1.4deg;
        }
        .dark .lb-pad { background: #e6dcb4; }
        .lb-pad::before { content: ""; display: block; height: 1.25rem; margin-inline: -1.35rem; background: linear-gradient(180deg, #7d2630, #5a1a22); }
        .lb-pad-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; padding-top: 1.1rem; }
        .lb-pad-call { font-family: var(--lb-typed); font-weight: 700; font-size: 0.82rem; line-height: 1.15; color: var(--lb-red); }
        .lb-pad h3 { margin-top: 0.9rem; font-size: 1.4rem; font-weight: 700; line-height: 1.2; }
        .lb-pad-when { margin-top: 0.25rem; font-size: 0.86rem; color: var(--lb-oink-2); }
        .lb-pad dl { margin-top: 1rem; border-top: 1px solid rgba(30, 27, 22, 0.35); }
        .lb-pad dl div { display: flex; justify-content: space-between; gap: 1rem; padding-block: 0.5rem; border-bottom: 1px solid rgba(37, 52, 59, 0.3); font-size: 0.9rem; }
        .lb-pad dt { font-family: var(--lb-typed); font-weight: 700; font-size: 0.78rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--lb-oink-3); }
        .lb-pad dd { font-weight: 700; text-align: right; }
        .lb-pad-acts { display: flex; justify-content: flex-end; align-items: center; gap: 1.1rem; margin-top: 1.2rem; }
        .lb-pad-acts .lb-stamp { font-size: 0.86rem; padding: 0.4rem 0.75rem 0.22rem; }
        .lb-pad-decline { --lb-st: #655844; --r: 0deg; border-style: dashed; -webkit-mask-image: none; mask-image: none; }
        .lb-pad-approve { --lb-st: #25343b; --r: -4deg; border-width: 2.5px; }

        /* ---------------------------------------------------------------
           Reaching patrons: the cloth cover, blocked in gold.
           Fixed in both modes: it is a book, and a book does not flip.
           --------------------------------------------------------------- */
        .lb-cover {
            position: relative;
            background:
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.035) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.07) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                #233a2f;
            color: #f4e7c3;
            --lb-st-free: #e2c474;
            --lb-accent: #e2c474;
        }
        .lb-cover::before {
            content: "";
            position: absolute;
            inset: clamp(0.8rem, 2vw, 1.5rem);
            border: 1.5px solid rgba(226, 196, 116, 0.85);
            outline: 1px solid rgba(226, 196, 116, 0.5);
            outline-offset: 4px;
            pointer-events: none;
        }
        .lb-cover .lb-kicker { color: #e2c474; }
        .lb-cover .lb-lede { color: #dcd1b0; }
        .lb-cover .lb-h2 { text-shadow: 0 1px 0 rgba(0, 0, 0, 0.35); }
        .lb-reach-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; margin-top: clamp(2.5rem, 5vw, 4rem); align-items: start; }
        @media (min-width: 980px) { .lb-reach-grid { grid-template-columns: minmax(0, 1fr) 15rem; gap: 4rem; } }
        .lb-ways { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3rem; }
        @media (min-width: 700px) { .lb-ways { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .lb-way { padding-top: 1.2rem; border-top: 1px solid rgba(226, 196, 116, 0.5); }
        .lb-way-head { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; }
        .lb-way h3 { font-size: 1.3rem; font-weight: 700; line-height: 1.3; }
        .lb-way p { margin-top: 0.6rem; color: #dcd1b0; font-size: 0.95rem; }
        .lb-bookmark-col { display: grid; justify-items: center; }
        .lb-bookmark {
            position: relative;
            width: 13.5rem;
            padding: 3.2rem 1.2rem 1.4rem;
            border-radius: 0.3rem 0.3rem 0 0;
            background: var(--lb-paper);
            text-align: center;
            box-shadow: 0 1.6rem 2rem -1.2rem rgba(0, 0, 0, 0.75);
            rotate: 2.2deg;
            -webkit-mask-image: radial-gradient(circle at 50% 1.4rem, rgba(0, 0, 0, 0) 0.38rem, #000 0.42rem);
            mask-image: radial-gradient(circle at 50% 1.4rem, rgba(0, 0, 0, 0) 0.38rem, #000 0.42rem);
        }
        .lb-ribbon { position: absolute; left: 50%; top: -3.4rem; width: 0.5rem; height: 5rem; translate: -50% 0; rotate: 7deg; transform-origin: 50% 100%; background: linear-gradient(90deg, #8e2a33, #b3262b 50%, #7d2630); border-radius: 2px 2px 0 0; z-index: 1; }
        .lb-bookmark-wrap { position: relative; margin-top: 3rem; }
        .lb-bookmark-call { display: inline-block; font-family: var(--lb-typed); font-weight: 700; font-size: 0.8rem; line-height: 1.15; color: var(--lb-red); }
        .lb-bookmark-title { margin-top: 0.7rem; font-weight: 700; font-size: 1.05rem; line-height: 1.25; }
        .lb-bookmark svg { width: 7rem; height: 7rem; margin: 1rem auto 0; }
        .lb-bookmark-scan { margin-top: 0.9rem; font-size: 0.8rem; line-height: 1.4; color: var(--lb-oink-2); }
        .lb-bookmark-foot { display: block; margin-top: 0.8rem; padding-top: 0.6rem; border-top: 1px solid rgba(30, 27, 22, 0.3); font-size: 0.64rem; color: var(--lb-oink-3); }
        .lb-bookmark-cap { margin-top: 1.6rem; max-width: 15rem; text-align: center; font-size: 0.86rem; color: #dcd1b0; }
        .lb-cover-foot { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.4rem 0.9rem; margin-top: clamp(2.25rem, 4vw, 3.25rem); padding-top: 1.4rem; border-top: 1px solid rgba(226, 196, 116, 0.5); color: #dcd1b0; }
        #lb .lb-cover a:focus-visible { outline-color: #e2c474; }

        /* ---------------------------------------------------------------
           The rest: six volumes lying on the table, and what is in each
           --------------------------------------------------------------- */
        .lb-rest-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem; margin-top: clamp(2.25rem, 4vw, 3.5rem); align-items: start; }
        @media (min-width: 1000px) { .lb-rest-grid { grid-template-columns: 21rem minmax(0, 1fr); gap: 4.5rem; } }
        .lb-flats { position: relative; display: grid; justify-items: start; width: fit-content; margin-inline: auto; padding-bottom: 1rem; }
        @media (min-width: 1000px) { .lb-flats { position: sticky; top: 7rem; } }
        .lb-flats::after { content: ""; position: absolute; inset: auto -1.5rem 0 -1.5rem; height: 1rem; border-radius: 50%; background: radial-gradient(closest-side, rgba(30, 20, 8, 0.45), rgba(30, 20, 8, 0)); }
        .lb-flat {
            --cloth: #2f4a3c;
            --letter: #f4e7c3;
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            width: var(--w, 17rem);
            max-width: 100%;
            height: var(--t, 2.7rem);
            margin-inline-start: var(--x, 0rem);
            padding-inline: 0.5rem 1rem;
            border-radius: 2px 6px 6px 2px / 2px 10px 10px 2px;
            color: var(--letter);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.17), rgba(0, 0, 0, 0) 42%, rgba(0, 0, 0, 0.38)),
                repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.07) 0 1px, rgba(0, 0, 0, 0) 1px 3px),
                var(--cloth);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.4);
            transition: translate 0.4s cubic-bezier(0.3, 1.3, 0.5, 1);
        }
        .lb-flat .lb-spine-call { width: 2.6rem; font-size: 0.6rem; }
        .lb-flat b { font-family: var(--lb-spine); font-size: 0.84rem; letter-spacing: 0.14em; text-transform: uppercase; white-space: nowrap; }
        .lb-flat em { margin-inline-start: auto; font-style: normal; font-family: var(--lb-typed); font-weight: 700; font-size: 0.62rem; letter-spacing: 0.16em; text-transform: uppercase; opacity: 0.85; }
        @supports selector(:has(*)) {
            .lb-rest-grid:has(.lb-item:nth-child(1):hover) .lb-flat:nth-child(1),
            .lb-rest-grid:has(.lb-item:nth-child(2):hover) .lb-flat:nth-child(2),
            .lb-rest-grid:has(.lb-item:nth-child(3):hover) .lb-flat:nth-child(3),
            .lb-rest-grid:has(.lb-item:nth-child(4):hover) .lb-flat:nth-child(4),
            .lb-rest-grid:has(.lb-item:nth-child(5):hover) .lb-flat:nth-child(5),
            .lb-rest-grid:has(.lb-item:nth-child(6):hover) .lb-flat:nth-child(6) { translate: -1.4rem 0; }
        }
        .lb-items { border-bottom: 1px solid var(--lb-line); min-width: 0; }
        .lb-item { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.5rem 1.5rem; padding-block: 1.7rem 1.8rem; border-top: 1px solid var(--lb-line); }
        @media (min-width: 640px) { .lb-item { grid-template-columns: 4.4rem minmax(0, 1fr); } }
        .lb-item-no { color: var(--lb-ink-3); padding-top: 0.35rem; }
        .lb-item-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.5rem 1.2rem; }
        .lb-item h3 { font-size: 1.3rem; font-weight: 700; line-height: 1.3; }
        .lb-item p { margin-top: 0.75rem; max-width: 44rem; color: var(--lb-ink-2); }

        /* ---------------------------------------------------------------
           Every branch: six bookplates
           --------------------------------------------------------------- */
        .lb-plates { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 680px) { .lb-plates { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1020px) { .lb-plates { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.75rem; } }
        .lb-exlibris {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 1.9rem 1.7rem 1.6rem;
            background: var(--lb-card);
            box-shadow: 0 0 0 1px var(--lb-line), 0 1rem 1.6rem -1.3rem rgba(30, 20, 8, 0.55);
            transition: translate 0.3s ease, rotate 0.3s ease;
        }
        .lb-exlibris::before { content: ""; position: absolute; inset: 0.5rem; border: 1px solid var(--lb-line); outline: 3px double var(--lb-line); outline-offset: -0.5rem; pointer-events: none; }
        .lb-exlibris:hover { translate: 0 -0.3rem; rotate: -0.5deg; }
        .lb-exlibris-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; min-height: 3.6rem; }
        .lb-exlibris-top .lb-typed { color: var(--lb-ink-3); padding-top: 0.4rem; }
        .lb-oval {
            flex: none;
            display: grid;
            place-items: center;
            width: 4.6rem;
            height: 3.1rem;
            border-radius: 50%;
            border: 2px solid currentColor;
            outline: 1px solid currentColor;
            outline-offset: 2px;
            color: var(--s, var(--lb-st-pro));
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.12em;
            rotate: var(--r, -6deg);
            -webkit-mask-image: var(--lb-inkmask);
            mask-image: var(--lb-inkmask);
        }
        .lb-exlibris h3 { margin-top: 1rem; font-size: 1.35rem; font-weight: 700; line-height: 1.25; }
        .lb-exlibris p { margin-top: 0.6rem; color: var(--lb-ink-2); font-size: 0.95rem; }
        .lb-exlibris a { margin-top: auto; padding-top: 1.1rem; align-self: flex-start; position: relative; }

        /* ---------------------------------------------------------------
           Three steps: three lines in the accessions register
           --------------------------------------------------------------- */
        .lb-register {
            margin-top: clamp(2.25rem, 4vw, 3.5rem);
            background-color: var(--lb-card);
            box-shadow: 0 0 0 1px var(--lb-line), 0 1.4rem 2rem -1.5rem rgba(30, 20, 8, 0.55);
        }
        .lb-register-head,
        .lb-step { display: grid; grid-template-columns: 5.2rem minmax(0, 1fr); gap: 0 1.5rem; padding-inline: 1.25rem; }
        @media (min-width: 860px) {
            .lb-register-head,
            .lb-step { grid-template-columns: 7.5rem minmax(0, 0.8fr) minmax(0, 1.6fr); gap: 0 2.25rem; padding-inline: 2rem; }
        }
        .lb-register-head { padding-block: 1rem 0.7rem; border-bottom: 2px solid var(--lb-ink); color: var(--lb-ink-3); }
        .lb-register-head span:last-child { display: none; }
        @media (min-width: 860px) { .lb-register-head span:last-child { display: block; } }
        .lb-step { align-items: center; padding-block: 1.6rem; border-bottom: 1px solid var(--lb-line); }
        .lb-step:last-child { border-bottom: 0; }
        .lb-step-no {
            justify-self: start;
            display: grid;
            place-items: center;
            width: 4.2rem;
            height: 4.2rem;
            border-radius: 50%;
            border: 2.5px solid currentColor;
            outline: 1px solid currentColor;
            outline-offset: 3px;
            color: var(--s, var(--lb-st-pro));
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: 1.7rem;
            line-height: 1;
            rotate: var(--r, -7deg);
            -webkit-mask-image: var(--lb-inkmask);
            mask-image: var(--lb-inkmask);
        }
        .lb-step h3 { font-size: 1.35rem; font-weight: 700; line-height: 1.3; }
        .lb-step p { grid-column: 1 / -1; margin-top: 0.9rem; color: var(--lb-ink-2); }
        @media (min-width: 860px) { .lb-step p { grid-column: 3; margin-top: 0; } }
        html.es-anim #lb [data-reveal]:not(.is-revealed) .lb-step-no { opacity: 0; scale: 1.6; }
        .lb-step-no { transition: opacity 0.25s ease, scale 0.35s cubic-bezier(0.3, 1.4, 0.5, 1); transition-delay: calc(var(--n, 0) * 0.22s + 0.25s); }

        /* ---------------------------------------------------------------
           See also: the key features, as cross-references
           --------------------------------------------------------------- */
        .lb-also-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 4rem; align-items: start; }
        @media (min-width: 940px) { .lb-also-grid { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .lb-also-more { margin-top: 1.5rem; }
        .lb-refs { border-top: 2px solid var(--lb-ink); }
        .lb-ref {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.2rem 1.25rem;
            padding: 1.1rem 0.4rem 1.05rem;
            border-bottom: 1px solid var(--lb-line);
            transition: padding 0.25s ease, background-color 0.25s ease;
        }
        @media (min-width: 700px) { .lb-ref { grid-template-columns: 5.4rem minmax(0, 0.8fr) minmax(0, 1.5fr) auto; gap: 1.25rem; } }
        .lb-ref:hover { background-color: var(--lb-hair); padding-inline: 0.9rem 0.4rem; }
        .lb-ref-see { display: none; color: var(--lb-ink-3); }
        @media (min-width: 700px) { .lb-ref-see { display: block; } }
        .lb-ref strong { font-size: 1.15rem; line-height: 1.3; }
        .lb-ref small { grid-column: 1; font-size: 0.92rem; line-height: 1.5; color: var(--lb-ink-2); }
        @media (min-width: 700px) { .lb-ref small { grid-column: auto; } }
        .lb-ref svg { grid-row: 1 / span 2; grid-column: 2; width: 1.3rem; height: 1.3rem; color: var(--lb-accent); transition: translate 0.25s ease; }
        @media (min-width: 700px) { .lb-ref svg { grid-row: auto; grid-column: auto; } }
        .lb-ref:hover svg { translate: 0.3rem 0; }

        /* The plan band and the closing strip are shared partials: their words and prices stay, the print changes. */
        #lb .lb-plans > section { background: var(--lb-ground-2); }
        #lb .lb-plans h2 { font-family: var(--lb-serif); font-weight: 400; font-size: clamp(1.8rem, 3.4vw, 2.7rem); line-height: 1.18; letter-spacing: -0.005em; color: var(--lb-ink); }
        #lb .lb-plans h2 + p { color: var(--lb-ink-2); font-size: 1.02rem; }
        #lb .lb-plans .grid > div { background: var(--lb-card); border: 1px solid var(--lb-line); border-radius: 3px; box-shadow: 0 1rem 1.6rem -1.3rem rgba(30, 20, 8, 0.55); }
        #lb .lb-plans .grid > div:nth-child(2) { border: 2px solid var(--lb-accent); }
        #lb .lb-plans .grid > div span,
        #lb .lb-plans .grid > div p,
        #lb .lb-plans .grid > div li { color: var(--lb-ink-2); }
        #lb .lb-plans .grid > div .text-3xl { font-family: var(--lb-serif); font-weight: 700; color: var(--lb-ink); }
        #lb .lb-plans .grid > div .uppercase { font-family: var(--lb-typed); font-weight: 700; color: var(--lb-ink); }
        #lb .lb-plans .grid > div .rounded-full { background: transparent; color: var(--lb-st-pro); border: 1.5px solid currentColor; border-radius: 2px; font-family: var(--lb-typed); rotate: -3deg; }
        #lb .lb-plans .grid > div svg { color: var(--lb-st-free); }
        #lb .lb-plans a.font-medium { color: var(--lb-accent); text-decoration: underline; text-underline-offset: 0.22em; }
        #lb .lb-plans a.rounded-2xl { background: #6a2028; color: #f8f1dc; border-radius: 4px; font-family: var(--lb-serif); font-weight: 700; box-shadow: inset 0 0 0 0.24rem #6a2028, inset 0 0 0 calc(0.24rem + 1px) rgba(226, 196, 116, 0.95), 0 0.7rem 1.2rem -0.7rem rgba(30, 20, 8, 0.75); }

        #lb .lb-keep > section { background: var(--lb-ground-2); border-top: 1px solid var(--lb-line); }
        #lb .lb-keep h2 { font-family: var(--lb-serif); font-weight: 400; color: var(--lb-ink); }
        #lb .lb-keep p.uppercase { font-family: var(--lb-typed); font-weight: 700; letter-spacing: 0.16em; color: var(--lb-accent); }
        #lb .lb-keep .grid > a { background: var(--lb-card); border: 1px solid var(--lb-line); border-radius: 3px; }
        #lb .lb-keep .grid > a:hover { border-color: var(--lb-accent); }
        #lb .lb-keep .grid > a > span:first-child { display: none; }
        #lb .lb-keep .grid > a h3 { font-family: var(--lb-serif); color: var(--lb-ink); }
        #lb .lb-keep .grid > a p { color: var(--lb-ink-2); }
        #lb .lb-keep .grid > a > span:last-child,
        #lb .lb-keep a.self-start { color: var(--lb-accent); }

        /* ---------------------------------------------------------------
           On the next shelf: four more volumes
           --------------------------------------------------------------- */
        .lb-next-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: end; }
        @media (min-width: 900px) { .lb-next-grid { grid-template-columns: minmax(0, 0.7fr) auto; } }
        .lb-next-case { width: fit-content; max-width: 100%; }
        .lb-next-case .lb-shelf { --lb-row: 21.4rem; --lb-sfs: 0.86rem; flex-wrap: nowrap; padding-inline: 1.3rem 1rem; }
        .lb-next-case a.lb-spine:hover,
        .lb-next-case a.lb-spine:focus-visible { translate: 0 -0.8rem; box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.28), 0 1rem 0.8rem -0.6rem rgba(0, 0, 0, 0.7); }
        .lb-next-case .lb-spine-call { font-size: 0.6rem; letter-spacing: 0.04em; padding-inline: 0.1rem; text-transform: uppercase; }
        /* The whole row is about 35rem of books and needs a screen of some 604px. Below that
           the fillers come off, or the two volumes lying flat hang out of the case and off
           the side of the screen (they did from 481px to 603px). */
        @media (max-width: 639.98px) {
            .lb-next-case .lb-fill,
            .lb-next-case .lb-pile { display: none; }
        }
        @media (max-width: 480px) {
            .lb-next-case .lb-shelf { --lb-k: 0.78; --lb-row: 16.8rem; --lb-sfs: 0.68rem; padding-inline: 0.8rem 0.5rem; }
            .lb-next-case .lb-spine-call { font-size: 0.5rem; }
            .lb-next-case .lb-fill,
            .lb-next-case .lb-pile { display: none; }
        }

        /* ---------------------------------------------------------------
           Ask a librarian: the questions
           --------------------------------------------------------------- */
        .lb-faq-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4.5rem; align-items: start; }
        @media (min-width: 1000px) {
            .lb-faq-grid { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
            .lb-faq-head { position: sticky; top: 7rem; }
        }
        .lb-brass {
            display: inline-block;
            margin-bottom: 1.5rem;
            padding: 0.6rem 1.1rem 0.42rem;
            background: linear-gradient(170deg, #f1dc9c, #c9a24b 45%, #9a7526 100%);
            color: #2a1f0a;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35), 0 0 0 1px #6b4d17, 0 0.5rem 0.8rem -0.5rem rgba(30, 20, 8, 0.7);
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.35);
        }
        .lb-qa { border-top: 2px solid var(--lb-ink); }
        .lb-qa details { border-bottom: 1px solid var(--lb-line); }
        .lb-qa summary { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr) 1.4rem; gap: 0.8rem; align-items: start; padding: 1.3rem 0.2rem 1.2rem; cursor: pointer; }
        .lb-qa-no { font-family: var(--lb-typed); font-weight: 700; font-size: 0.9rem; letter-spacing: 0.06em; color: var(--lb-ink-3); padding-top: 0.2rem; }
        .lb-qa h3 { font-size: 1.12rem; font-weight: 700; line-height: 1.4; }
        .lb-qa summary i { position: relative; width: 1.4rem; height: 1.4rem; margin-top: 0.15rem; color: var(--lb-accent); }
        .lb-qa summary i::before,
        .lb-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .lb-qa summary i::after { rotate: 90deg; }
        .lb-qa details[open] summary i::after { rotate: 0deg; }
        .lb-qa details p { padding: 0 0.2rem 1.6rem 3.4rem; max-width: 46rem; color: var(--lb-ink-2); }
        @media (max-width: 560px) { .lb-qa details p { padding-inline-start: 0.2rem; } }

        /* ---------------------------------------------------------------
           The circulation desk: a borrower's card to fill in.
           The desk is wood in both modes.
           --------------------------------------------------------------- */
        .lb-desk {
            position: relative;
            overflow: clip;
            padding-block: clamp(4.5rem, 9vw, 7.5rem);
            background:
                repeating-linear-gradient(92deg, rgba(0, 0, 0, 0.09) 0 2px, rgba(0, 0, 0, 0) 2px 13px, rgba(255, 255, 255, 0.03) 13px 15px, rgba(0, 0, 0, 0) 15px 41px),
                linear-gradient(180deg, #5a3c20, #44290f 55%, #331e0a);
            background-color: #44290f;
            color: #f4e7c3;
            --lb-accent: #e2c474;
        }
        .dark .lb-desk {
            background:
                repeating-linear-gradient(92deg, rgba(0, 0, 0, 0.12) 0 2px, rgba(0, 0, 0, 0) 2px 13px, rgba(255, 255, 255, 0.02) 13px 15px, rgba(0, 0, 0, 0) 15px 41px),
                radial-gradient(50rem 30rem at 70% 0%, rgba(255, 205, 130, 0.2), rgba(255, 205, 130, 0) 70%),
                linear-gradient(180deg, #2f1e0e, #201307 55%, #160c04);
            background-color: #201307;
        }
        .lb-desk-grid { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 960px) { .lb-desk-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 27rem); gap: 4.5rem; } }
        .lb-desk .lb-stamp { --lb-st: #e2c474; font-size: 0.86rem; padding: 0.38rem 0.7rem 0.2rem; }
        .lb-desk .lb-h2 { margin-top: 1.4rem; font-size: clamp(2.1rem, 4.4vw, 3.6rem); text-shadow: 0 2px 0 rgba(0, 0, 0, 0.35); }
        .lb-desk .lb-lede { color: #e2d6b4; }
        .lb-libcard {
            position: relative;
            padding: 1.5rem 1.5rem 1.4rem;
            border-radius: 0.5rem;
            background: var(--lb-paper);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.3), 0 2.2rem 2.6rem -1.4rem rgba(0, 0, 0, 0.85);
            rotate: -1.3deg;
        }
        .lb-libcard-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding-bottom: 0.8rem; border-bottom: 2px solid var(--lb-oink); }
        .lb-libcard-top strong { font-size: 1.2rem; }
        .lb-libcard-top span { color: var(--lb-red); }
        .lb-libcard label { display: block; margin-block: 1.2rem 0.45rem; color: var(--lb-oink-3); }
        .lb-libcard-form { display: grid; gap: 1rem; }
        #lb .lb-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 0.2rem 1rem;
            border: 0;
            border-bottom: 2px solid var(--lb-oink);
            background: transparent;
            font-family: var(--lb-typed);
            font-weight: 700;
            font-size: clamp(1rem, 3.6vw, 1.25rem);
            transition: box-shadow 0.2s ease, background-color 0.2s ease;
        }
        #lb .lb-claim:focus-within { border-color: var(--lb-oink); background-color: rgba(106, 32, 40, 0.07); box-shadow: 0 2px 0 var(--lb-oink); }
        #lb .lb-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: var(--lb-blueblack);
            box-shadow: none;
            outline: none;
        }
        #lb .lb-claim input::placeholder { color: #857761; }
        .lb-claim span { flex: none; color: var(--lb-oink-3); user-select: none; }
        .lb-libcard-foot { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; margin-top: 1.25rem; }
        .lb-barcode { width: 9.5rem; height: 2.4rem; background: repeating-linear-gradient(90deg, #1e1b16 0 2px, rgba(0, 0, 0, 0) 2px 4px, #1e1b16 4px 5px, rgba(0, 0, 0, 0) 5px 9px, #1e1b16 9px 12px, rgba(0, 0, 0, 0) 12px 14px, #1e1b16 14px 15px, rgba(0, 0, 0, 0) 15px 18px); }
        .lb-libcard-foot small { text-align: right; color: var(--lb-oink-3); font-size: 0.68rem; line-height: 1.5; }
        .lb-libcard-note { margin-top: 1rem; font-size: 0.9rem; color: var(--lb-oink-2); }
        #lb .lb-desk a:focus-visible { outline-color: #e2c474; }
        #lb .lb-libcard a:focus-visible,
        #lb .lb-libcard input:focus-visible { outline-color: #6a2028; }

        @media (max-width: 639.98px) {
            .lb-shelf-hero { --lb-k: 0.84; --lb-row: 14.9rem; --lb-sfs: 0.66rem; }
            .lb-shelf-hero .lb-spine { padding-top: 1.3rem; gap: 0.3rem; }
            .lb-shelf-hero .lb-fill,
            .lb-shelf-hero .lb-pile,
            .lb-shelf-hero .lb-out { display: none; }
            .lb-shelf-hero .lb-fill.lb-keep-sm { display: flex; }
            .lb-lamp { font-size: 0.66rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .lb-spine, .lb-btn, .lb-btn svg, .lb-flat, .lb-exlibris, .lb-entry, .lb-ref, .lb-due, .lb-step-no { transition: none; }
            html.es-anim.dark #lb .lb-lamp-pool { animation: none; }
        }
    </style>

    @php
        // The date-due slip: one line per occurrence of ONE recurring program.
        // [stamp, weekday, places, remaining, kind, rotation]
        $slip = [
            ['Sep 1',  'Tue', '24 places', 'full',    'stamped', '-2deg'],
            ['Sep 8',  'Tue', '24 places', '3 left',  'stamped', '1.5deg'],
            ['Sep 15', 'Tue', 'branch closed', 'no session', 'void', '0deg'],
            ['Sep 22', 'Tue', '24 places', '11 left', 'stamped', '-1deg'],
            ['Sep 29', 'Tue', '24 places', '19 left', 'stamped', '2deg'],
            ['Oct 3',  'Sat', '24 places', '24 left', 'extra',   '-1.5deg'],
            ['Oct 6',  'Tue', '24 places', '24 left', 'stamped', '1deg'],
        ];

        // The shelf list: one week of a branch, as a record.
        // [program, sub-schedule, colour, when, places, sign-up, plan]
        $shelfList = [
            ['Toddler Story Time', 'Children', '#8a4f0b', 'Tuesdays, 10:00', '24', 'Free registration', ''],
            ['Lego Club', 'Children', '#8a4f0b', 'Wednesdays, 16:00', '20', 'Free registration', ''],
            ['Teen Coding Club', 'Teens', '#2f5d50', 'Wednesdays, 16:30', '12', 'Free registration', ''],
            ['Book Club', 'Adults', '#1f3a5f', 'First Thursday, 19:00', '15', 'Free registration', ''],
            ['Tech Help Drop-in', 'Seniors', '#6b4226', 'Fridays, 13:00', 'No limit', 'Just turn up', ''],
            ['Author Reading: Jane Ahmad', 'Adults', '#1f3a5f', 'Sat Oct 3, 19:00', '90', 'Tickets, $6', ''],
            ['Local History Talk', 'Adults', '#1f3a5f', 'Not announced yet', '60', 'Draft', ''],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for libraries?',
                'a' => 'Yes. Publishing your program calendar, setting programs up as recurring, organising them into sub-schedules, taking free registrations with a place limit, embedding the calendar on your library site, syncing two ways with Google, Outlook or CalDAV, and the built-in analytics are all free forever. Newsletters are free too, with 10 emails a month counted per recipient; Pro raises that to 100 and Enterprise to 1,000. Free registration has no ceiling on any plan. Charging for a program is the Pro plan at '.plan_price($proMonthly).' a month, which also adds the live check-in count at the door. Event Schedule charges zero platform fees on ticket sales, on every plan.',
            ],
            [
                'q' => 'Can I manage story times, author events, and workshops together?',
                'a' => 'Yes. Sub-schedules organise one calendar into strands, so children\'s story times, teen clubs, adult talks and community meetings each sit on their own strand with their own colour, and a patron can filter the calendar down to just the strand they came for. To be exact about what a sub-schedule is: it organises and colour-codes, and it cannot hide anything. A program you are not ready to publish is a Draft instead.',
            ],
            [
                'q' => 'How do patrons find out about library programs?',
                'a' => 'Four ways, and none of them is an algorithm. Patrons follow your schedule and you email them a newsletter when you have something to say. Your calendar embeds on the library website you already have. Every schedule has a downloadable QR code you can print on a bookmark, a poster or a shelf label. And each date has an iCal download so it lands in the patron\'s own calendar, or a patron can subscribe to the whole calendar as a live feed that follows when a date moves or comes out. Being exact about the two kinds of email: programs you publish reach the list on their own, as one digest covering the batch, while a newsletter with anything else in it is written and sent by you. A community group\'s accepted request stays the group\'s own event, so it is not in your digest.',
            ],
            [
                'q' => 'Can patrons register for programs?',
                'a' => 'Yes, on the free plan. Turn on registration and set a place limit, and the limit is counted separately for every date, so this Tuesday filling up does not close next Tuesday. Patrons get a confirmation email with their own link. For a paid program, take payment through Stripe or PayPal, a payment link or cash at the desk, with zero platform fees past the provider\'s own processing. Named ticket types that carry a price are the Pro plan at '.plan_price($proMonthly).' a month.',
            ],
            [
                'q' => 'What happens when a story time fills up?',
                'a' => 'The Register button on that date becomes Join Waitlist, on the free plan. When a registration is cancelled and a place comes back, whoever joined the waitlist first is emailed. Each date keeps its own list, so a full Tuesday has a waitlist while next Tuesday is still open. The waitlist for a sold-out paid ticket is the Pro version of the same thing.',
            ],
            [
                'q' => 'Can patrons hear when tickets for an author event go on sale?',
                'a' => 'Yes, on every plan. Switch on the "Notify me" card, publish the author evening before tickets are on sale, and its page offers "Tell me when tickets go on sale". A patron leaves just an email address and gets one email when tickets go on sale, one if you cancel, and a reminder shortly before it starts, plus a change notice if you choose to send one when you move it. It is not a subscription to your schedule, it does not count against the newsletter allowance, and every email has a one-click unsubscribe. The on-sale email is about tickets: opening free registration on a program does not send one.',
            ],
            [
                'q' => 'What happens on the weeks the branch is closed?',
                'a' => 'You add a date exception and that date comes out of the run, so patrons simply do not see a session that day. Exceptions work the other way too: add a single extra date for a half-term session without setting up a second program. A recurrence can also be told to stop, either on a date or after a set number of sessions, so a school-year program is not still listed in August.',
            ],
            [
                'q' => 'Can community groups ask to use the meeting room?',
                'a' => 'Yes. Turn on event requests and your schedule gets a public form. A submission arrives as a pending request rather than a published event, you approve or decline it, and you can keep a list of schedules whose submissions are approved without review. Requests are free, and so is the approval queue.',
            ],
            [
                'q' => 'Can the whole staff have logins?',
                'a' => 'Not on the free plan, which is one team member. Multiple team members, up to five, are an Enterprise feature at '.plan_price($entMonthly).' a month, along with custom domains. Plenty of branches run the whole calendar from one shared account, so it is worth knowing which you need before you pay for it. On Enterprise, a viewer login is read-only and sees no sales, but can scan tickets at the door. And before whoever set the schedule up moves on, they can hand it to a colleague, on any plan.',
            ],
        ];

        $dotSections = [
            ['top', 'The catalogue card'],
            ['card', 'One card, one program'],
            ['slip', 'The date-due slip'],
            ['week', 'The shelf list'],
            ['room', 'The meeting room'],
            ['reach', 'Reaching patrons'],
            ['rest', 'Everything else'],
            ['who', 'Every branch'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Check it out'],
        ];
    @endphp

    @php
        $lbArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $lbDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';

        // Each section's label, by id, and the class it is shelved under.
        $lbLabels = array_column($dotSections, 1, 0);
        $lbCalls = [
            'card' => '025.3', 'slip' => '025.6', 'week' => '790.1', 'room' => '394.2', 'reach' => '021.7',
            'rest' => '658', 'who' => '027', 'steps' => '371.3', 'faq' => '025.5', 'claim' => '027.4',
        ];

        // The top shelf, left to right. A "nav" volume is a section of the page; the rest fill the shelf.
        // [kind, cloth, height, width, section id or modifier]
        $lbShelf = [
            ['fill', 'k', '12.2rem', '1.35rem', ''],
            ['fill', 'n', '14.1rem', '2.1rem', 'lb-bands lb-keep-sm'],
            ['nav', 'g', '17rem', '3.4rem', 'card'],
            ['fill', 'o', '11.3rem', '1.15rem', ''],
            ['nav', 'o', '15.6rem', '3.2rem', 'slip'],
            ['nav', 't', '15rem', '3.6rem', 'week'],
            ['fill', 'r', '12.8rem', '1.7rem', 'lb-bands'],
            ['fill', 'k', '14.8rem', '2.3rem', ''],
            ['nav', 'n', '15.4rem', '3.1rem', 'room'],
            ['out', '', '', '', ''],
            ['nav', 'g', '16rem', '3.5rem', 'reach'],
            ['fill', 't', '10.4rem', '1.25rem', ''],
            ['fill', 'o', '13.5rem', '1.9rem', 'lb-bands'],
            ['nav', 'k', '15.2rem', '3.3rem', 'rest'],
            ['nav', 'r', '14.6rem', '3.1rem', 'who'],
            ['pile', '', '', '', ''],
            ['nav', 'o', '14.2rem', '3.2rem', 'steps'],
            ['fill', 'g', '13.9rem', '1.5rem', ''],
            ['fill', 'n', '11.8rem', '1.3rem', ''],
            ['nav', 't', '15.4rem', '3rem', 'faq'],
            ['nav', 'g', '16.4rem', '3.6rem', 'claim'],
            ['fill', 'k', '12.6rem', '1.6rem', 'lb-bands'],
            ['fill', 'o', '14.4rem', '2rem', ''],
            ['fill', 'r', '13rem', '1.4rem', 'lb-lean lb-keep-sm'],
            ['end', '', '', '', ''],
        ];

        // The six volumes of one program record. [cloth, height, width, call, field, title, entry, entry colour]
        $lbVols = [
            ['g', '18.6rem', '4.2rem', '025.31', 'Call number', 'The sub-schedule', 'Children, Teens, Adults, Seniors, Community. Each sub-schedule has a name and a colour, and a patron can filter the calendar down to one of them.', '#2f4a3c'],
            ['o', '17.6rem', '3.9rem', '025.32', 'Main entry', 'The program itself', 'Name, description, an image, the branch address and a map drawn from it. Written once and it stands for every session.', '#6a2028'],
            ['t', '17.2rem', '4.5rem', '025.33', 'Collation', 'The recurrence', 'Weekly on chosen days, every other week, or the same weekday each month. First Thursday book club is one setting, not twelve events.', '#1f4a4f'],
            ['r', '18rem', '3.7rem', '025.34', 'Contents note', 'The running order', 'A workshop that is really four parts can list them, so patrons see the shape of the afternoon before they commit to it.', '#8a4a1f'],
            ['k', '19.6rem', '4.4rem', '025.35', 'Tracings', 'Where else it appears', 'Attach the visiting author\'s own schedule and, once they accept the request (or straight away, if they pre-approved your library), the program appears on their calendar as well as yours, the way one card generated a subject card and an author card.', '#28231e'],
            ['m', '16.8rem', '3.8rem', '025.36', 'Not yet catalogued', 'Drafts', 'A program you are still arranging sits on the calendar as a Draft, visible to you and never published until you say so.', '#5a5a2c'],
        ];

        // Six volumes lying flat for "the rest". [cloth, width, thickness, offset, call, word, plan]
        $lbFlats = [
            ['#1f4a4f', '15.5rem', '2.4rem', '1.6rem', '658.1', 'Analytics', 'Free'],
            ['#8a4a1f', '17.5rem', '2.7rem', '0.4rem', '658.2', 'Cloning', 'Free'],
            ['#2f4a3c', '16.2rem', '2.5rem', '1.1rem', '658.3', 'Photos', 'Free'],
            ['#6a2028', '19rem', '3.2rem', '0rem', '658.4', 'Tickets', 'Pro'],
            ['#28231e', '17rem', '2.6rem', '0.9rem', '658.5', 'Posters', 'Free'],
            ['#5a5a2c', '18.4rem', '3rem', '0.2rem', '658.6', 'Branches', 'Enterprise'],
        ];
    @endphp

    <div id="lb">

        <!-- ============================================================ -->
        <!-- 1. Hero: the reading room, and the top shelf                 -->
        <!-- ============================================================ -->
        <section id="top" class="lb-hero">
            <div class="lb-wrap lb-hero-grid">
                <div>
                    <h1 class="lb-h1">
                        <x-marketing.hero-eyebrow class="lb-eyebrow es-fade-up es-d-1">
                            Library program calendar
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Story time is not one Tuesday.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">It is <b data-count-to="38">38</b> of them.</span></span>
                    </h1>

                    <p class="lb-lede es-fade-up es-d-2">
                        Catalogue the program once: the days it runs, the dates the branch is shut, and how many places there are. Every date then keeps its own list, and the count starts again each week.
                    </p>

                    <div class="lb-cta es-fade-up es-d-3">
                        <a href="#slip" class="lb-btn lb-btn-paper">
                            See the date-due slip
                            {!! $lbDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="lb-btn lb-btn-cloth">
                            Create your calendar
                            {!! $lbArrow !!}
                        </a>
                    </div>
                </div>

                <!-- The volume on the table: one program, bound once. -->
                <div class="lb-hero-book es-fade-up es-d-4">
                    <div class="lb-board lb-hero-board" aria-hidden="true">
                        <span class="lb-board-title">Toddler Story Time</span>
                        <span class="lb-board-sub">Tuesdays, 10:00 &middot; Children's Room &middot; ages 0 to 3</span>
                        <div class="lb-plate lb-obj">
                            <div class="lb-plate-top">
                                <div class="lb-plate-call">
                                    027.4<br>STO<br>2026
                                </div>
                                <div class="lb-plate-kind">
                                    Recurring program
                                </div>
                            </div>
                            <dl>
                                <div>
                                    <dt>Runs</dt>
                                    <dd>Tuesdays, from Sep 1</dd>
                                </div>
                                <div>
                                    <dt>Ends</dt>
                                    <dd>after 38 sessions</dd>
                                </div>
                                <div>
                                    <dt>Places</dt>
                                    <dd>24, per date</dd>
                                </div>
                                <div>
                                    <dt>Not held</dt>
                                    <dd>6 closed days</dd>
                                </div>
                            </dl>
                            <div class="lb-plate-strands">
                                <span><i style="--c: #8a4f0b;"></i>Children &middot; this card</span>
                                <span><i style="--c: #2f5d50;"></i>Teens</span>
                                <span><i style="--c: #1f3a5f;"></i>Adults</span>
                            </div>
                        </div>
                    </div>
                    <p class="lb-hero-cap">
                        One record, filed on one sub-schedule. The others are the strands a patron can filter the calendar down to.
                    </p>
                </div>
            </div>

            <!-- The top shelf: every lettered spine is a section of this page. -->
            <nav class="lb-case" aria-label="Page sections">
                <div class="lb-shelf lb-shelf-hero">
                    @foreach ($lbShelf as $lbIndex => [$lbKind, $lbCloth, $lbH, $lbW, $lbExtra])
                        @if ($lbKind === 'nav')
                            <a href="#{{ $lbExtra }}" class="lb-spine lb-c-{{ $lbCloth }}" style="--h: {{ $lbH }}; --w: {{ $lbW }}; --i: {{ $lbIndex }};">
                                <span class="lb-spine-title">{{ $lbLabels[$lbExtra] }}</span>
                                <span class="lb-spine-call" aria-hidden="true">{{ $lbCalls[$lbExtra] }}</span>
                            </a>
                        @elseif ($lbKind === 'fill')
                            <span class="lb-spine lb-fill lb-c-{{ $lbCloth }} {{ $lbExtra }}" style="--h: {{ $lbH }}; --w: {{ $lbW }}; --i: {{ $lbIndex }};" aria-hidden="true"></span>
                        @elseif ($lbKind === 'out')
                            <span class="lb-spine lb-out" style="--i: {{ $lbIndex }};" aria-hidden="true"><span class="lb-spine-title">Out on loan</span></span>
                        @elseif ($lbKind === 'pile')
                            <span class="lb-pile" style="--i: {{ $lbIndex }};" aria-hidden="true"><i></i><i></i></span>
                        @elseif ($lbKind === 'end')
                            <span class="lb-end" style="--i: {{ $lbIndex }};" aria-hidden="true"></span>
                        @endif
                    @endforeach
                    <span class="lb-lamp" style="--i: {{ count($lbShelf) }};" aria-hidden="true">
                        <i class="lb-lamp-pool"></i>
                        <i class="lb-lamp-base"></i>
                        <i class="lb-lamp-stem"></i>
                        <i class="lb-lamp-arm"></i>
                        <i class="lb-lamp-shade"></i>
                    </span>
                </div>
            </nav>

            <!-- The shelf edge: one label holder for each kind of program -->
            <div class="lb-edge">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach (['Story Time', 'Book Clubs', 'Author Readings', 'Maker Space', 'Film Screenings', 'Local History', 'Tech Help', 'ESL Conversation', 'Summer Reading', 'Class Visits'] as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="lb-holder">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. One volume, one program: pick a spine and it comes down   -->
        <!-- ============================================================ -->
        <section id="card" class="lb-section" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap">
                <div class="lb-head-c">
                    <div class="lb-mark" data-reveal>
                        <span class="lb-call" aria-hidden="true">025.3<br>CAT</span>
                        <p class="lb-typed lb-kicker">One card, one program</p>
                    </div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                        A catalogue card never got <span class="lb-em">retyped</span> for every loan.
                    </h2>
                    <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Neither should a program. Everything a librarian would type on a card has a field on the record, and every line below is on the free plan.
                    </p>
                </div>

                <div class="lb-vols">
                    <div>
                        <div class="lb-case lb-vols-case" role="radiogroup" aria-label="The six parts of a program record">
                            <div class="lb-shelf">
                                @foreach ($lbVols as $lbIndex => [$lbCloth, $lbH, $lbW, $lbCall, $lbField, $lbTitle, $lbBody, $lbColour])
                                    <input type="radio" name="lb-vol" id="lb-vol-{{ $lbIndex + 1 }}" class="lb-vh lb-vol-input" @if ($lbIndex === 0) checked @endif>
                                    <label for="lb-vol-{{ $lbIndex + 1 }}" class="lb-spine lb-vol lb-c-{{ $lbCloth }}" style="--h: {{ $lbH }}; --w: {{ $lbW }}; --i: {{ $lbIndex }};">
                                        <span class="lb-spine-title">{{ $lbTitle }}</span>
                                        <span class="lb-spine-call" aria-hidden="true">{{ $lbCall }}</span>
                                    </label>
                                @endforeach
                                <span class="lb-end" style="--i: 6;" aria-hidden="true"></span>
                            </div>
                        </div>
                        <p class="lb-typed lb-vols-hint" aria-hidden="true">Take one down</p>
                    </div>

                    <div class="lb-entries">
                        @foreach ($lbVols as $lbIndex => [$lbCloth, $lbH, $lbW, $lbCall, $lbField, $lbTitle, $lbBody, $lbColour])
                            <article class="lb-entry lb-obj" id="lb-entry-{{ $lbIndex + 1 }}">
                                <p class="lb-typed lb-entry-field"><i style="--c: {{ $lbColour }};" aria-hidden="true"></i>{{ $lbField }}</p>
                                <h3>{{ $lbTitle }}</h3>
                                <p>{{ $lbBody }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The date-due slip, in its pocket                          -->
        <!-- ============================================================ -->
        <section id="slip" class="lb-section lb-endpaper" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap">
                <div>
                    <div class="lb-mark" data-reveal>
                        <span class="lb-call" aria-hidden="true">025.6<br>DUE</span>
                        <p class="lb-typed lb-kicker">The date-due slip</p>
                    </div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                        One record. <span class="lb-em">A separate list for every date.</span>
                    </h2>
                    <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                        A place limit belongs to the program, but it is counted per date. Sep 8 filling up has nothing to do with Sep 22, and neither of them needs a second event.
                    </p>
                </div>

                <div class="lb-slip-grid">
                    <div data-reveal>
                        <div class="lb-pocketed lb-obj" aria-hidden="true">
                            <div class="lb-slip">
                                <div class="lb-slip-head lb-typed">
                                    <span>Date due</span>
                                    <span>Places</span>
                                </div>
                                <ul>
                                    @foreach ($slip as $rowIndex => [$stampDate, $stampDay, $stampPlaces, $stampLeft, $stampKind, $stampRot])
                                        <li>
                                            @if ($stampKind === 'void')
                                                <span class="lb-due lb-due-void">{{ $stampDate }}</span>
                                            @else
                                                <span class="lb-due @if ($stampKind === 'extra') lb-due-extra @endif" style="--r: {{ $stampRot }}; --n: {{ $rowIndex }}; --o: {{ 0.7 + $rowIndex * 0.05 }};">{{ $stampDate }}</span>
                                            @endif
                                            <span class="lb-due-day">
                                                {{ $stampDay }}@if ($stampKind === 'extra') &middot; added @endif
                                            </span>
                                            <span class="lb-due-count">
                                                <b>{{ $stampLeft }}</b>
                                                <small>{{ $stampPlaces }}</small>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="lb-slip-foot lb-typed">
                                    6 of 38 sessions
                                </div>
                            </div>
                            <div class="lb-pocket-shadow"><div class="lb-pocket lb-typed">Toddler Story Time<br>027.4 STO</div></div>
                        </div>
                        <p class="lb-slip-note">
                            Sep 15 has no stamp. That Tuesday the branch was closed, so a date exception took it out of the run and patrons never see a session there.
                        </p>
                    </div>

                    <div>
                        <div class="lb-rules" data-reveal-group="90">
                            <div class="lb-rule" data-reveal>
                                <h3>The days it runs</h3>
                                <span class="lb-stamp" style="--r: -3deg;">Free</span>
                                <p>Tick the days of the week and set the time. Or every other week for a fortnightly club, or the same weekday each month for a first-Thursday book group.</p>
                            </div>
                            <div class="lb-rule" data-reveal>
                                <h3>The days you are shut</h3>
                                <span class="lb-stamp" style="--r: 2deg;">Free</span>
                                <p>Date exceptions take single dates out for a public holiday or a staff training day, and put single dates in for a half-term extra without a second program.</p>
                            </div>
                            <div class="lb-rule" data-reveal>
                                <h3>The end of the run</h3>
                                <span class="lb-stamp" style="--r: -2deg;">Free</span>
                                <p>A recurrence can stop on a date or after a set number of sessions. A school-year program that closes after 38 is not still on the calendar in August.</p>
                            </div>
                            <div class="lb-rule" data-reveal>
                                <h3>The places</h3>
                                <span class="lb-stamp" style="--r: 3deg;">Free</span>
                                <p>Registration with a limit, counted per date, and the remaining count is shown to the patron as they sign up. When a date fills, its button becomes a waitlist. Leave the limit off for a drop-in.</p>
                            </div>
                        </div>
                        <p class="lb-slip-after" data-reveal>
                            Change the time once and every remaining date follows.
                            <a href="{{ marketing_url('/features/recurring-events') }}" class="lb-link lb-arrow-link">
                                How recurring events work
                                {!! $lbArrow !!}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The shelf list: a real table                              -->
        <!-- ============================================================ -->
        <section id="week" class="lb-section" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap">
                <div class="lb-mark" data-reveal>
                    <span class="lb-call" aria-hidden="true">790.1<br>WEE</span>
                    <p class="lb-typed lb-kicker">The shelf list</p>
                </div>
                <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                    A branch week, <span class="lb-em">as a record.</span>
                </h2>
                <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                    Seven programs, four sub-schedules, one link. Six of these rows cost nothing.
                </p>

                <div class="lb-ledger" data-reveal>
                    <table>
                        <caption class="lb-vh">One week of library programs, with the sub-schedule each belongs to, when it runs, how many places it has, and how patrons sign up</caption>
                        <thead>
                            <tr class="lb-typed">
                                <th scope="col">Program</th>
                                <th scope="col">Sub-schedule</th>
                                <th scope="col">When</th>
                                <th scope="col">Places</th>
                                <th scope="col">Sign-up</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shelfList as [$rowName, $rowStrand, $rowColour, $rowWhen, $rowPlaces, $rowSignup, $rowPlan])
                                <tr>
                                    <th scope="row">
                                        {{ $rowName }}
                                        <span>{{ $rowStrand }}</span>
                                    </th>
                                    <td class="lb-td-strand">
                                        <span class="lb-strand">
                                            <i style="--c: {{ $rowColour }};" aria-hidden="true"></i>
                                            {{ $rowStrand }}
                                        </span>
                                    </td>
                                    <td>{{ $rowWhen }}</td>
                                    <td class="lb-td-places">{{ $rowPlaces }}</td>
                                    <td class="lb-td-signup">
                                        <span class="lb-signup">
                                            <span>{{ $rowSignup }}</span>
                                            @if ($rowPlan === 'Pro')
                                                <span class="lb-stamp lb-stamp-pro">Pro</span>
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="lb-notes" data-reveal>
                    <p>
                        The Local History Talk is a Draft: on your calendar, not on the public one, until the speaker confirms. Sub-schedules colour-code and organise, so a Draft is how a program hides, not a strand.
                    </p>
                    <p>
                        The author reading charges $6, and the price is what makes it a Pro program: {{ plan_price($proMonthly) }} a month, still with no platform fee. Everything free stays free: the free registration on the other four is unlimited, and it is not a trial.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The meeting room: requests                                -->
        <!-- ============================================================ -->
        <section id="room" class="lb-section lb-alt" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap lb-room-grid">
                <div>
                    <div class="lb-mark" data-reveal>
                        <span class="lb-call" aria-hidden="true">394.2<br>REQ</span>
                        <p class="lb-typed lb-kicker">The meeting room</p>
                    </div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Let the knitting group <span class="lb-em">fill in a slip.</span>
                    </h2>
                    <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Turn on event requests and your schedule gets a public form. What arrives is a pending request, not a published event, so the community can ask for the room without anyone getting posting rights to your calendar.
                    </p>

                    <ul class="lb-ticks" data-reveal-group="80">
                        <li data-reveal>
                            <span>Requests land in an approval queue. You accept, decline, or edit the details first.</span>
                        </li>
                        <li data-reveal>
                            <span>Community groups that run their own schedule can be listed as pre-approved, so the regulars stop waiting on you.</span>
                        </li>
                        <li data-reveal>
                            <span>Event Schedule emails you when requests are waiting, so the queue is not something you have to remember to open.</span>
                        </li>
                        <li data-reveal>
                            <span>Requests and the queue are free. Extra questions on the form are a Pro plan feature.</span>
                        </li>
                    </ul>

                    <p class="lb-not" data-reveal>
                        One thing this is not: room booking. Event Schedule does not hold an inventory of rooms and will not warn you that two groups asked for the same afternoon. The queue is where you catch that, with your own eyes.
                    </p>
                </div>

                <!-- The call slip: the top one of the pad on the desk. -->
                <div data-reveal="zoom">
                    <div class="lb-pad lb-obj" aria-hidden="true">
                        <div class="lb-pad-top">
                            <div class="lb-pad-call">
                                REQ<br>PEND<br>001
                            </div>
                            <div class="lb-plate-kind">
                                Awaiting approval
                            </div>
                        </div>
                        <h3>Riverside Knitting Circle</h3>
                        <p class="lb-pad-when">Thursdays, 18:30 &middot; Meeting Room B</p>
                        <dl>
                            <div>
                                <dt>Submitted by</dt>
                                <dd>A patron, via your form</dd>
                            </div>
                            <div>
                                <dt>Public now</dt>
                                <dd>No</dd>
                            </div>
                            <div>
                                <dt>Needs</dt>
                                <dd>One click from you</dd>
                            </div>
                        </dl>
                        <div class="lb-pad-acts">
                            <span class="lb-stamp lb-pad-decline">Decline</span>
                            <span class="lb-stamp lb-pad-approve">Approve</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Reaching patrons: the cloth cover (fixed in both modes)   -->
        <!-- ============================================================ -->
        <section id="reach" class="lb-section lb-cover" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap">
                <div class="lb-head-c">
                    <div class="lb-mark" data-reveal>
                        <span class="lb-call" aria-hidden="true">021.7<br>OUT</span>
                        <p class="lb-typed lb-kicker">Reaching patrons</p>
                    </div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The poster in the foyer only reaches <span class="lb-em">people already inside.</span>
                    </h2>
                    <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Four ways out of the building, none of them an algorithm deciding who deserves to hear about Tuesday.
                    </p>
                </div>

                <div class="lb-reach-grid">
                    <div class="lb-ways" data-reveal-group="100">
                        <div class="lb-way" data-reveal>
                            <div class="lb-way-head">
                                <h3>They follow you</h3>
                                <span class="lb-stamp" style="--r: -3deg;">Free</span>
                            </div>
                            <p>A patron follows your schedule and you have their name and email. That list is yours, it is visible only to your side of the calendar, and nobody rents it back to you.</p>
                        </div>
                        <div class="lb-way" data-reveal>
                            <div class="lb-way-head">
                                <h3>You write to them</h3>
                                <span class="lb-stamp" style="--r: 2deg;">Free</span>
                            </div>
                            <p>New programs you publish reach the list on their own, as one digest rather than one email per session. A newsletter is the other kind, written by you: the autumn program, a cancelled session, a note about the building. 10 emails a month free, 100 on Pro, 1,000 on Enterprise, counted per recipient, and the automatic digest is outside that count.</p>
                        </div>
                        <div class="lb-way" data-reveal>
                            <div class="lb-way-head">
                                <h3>On the library website</h3>
                                <span class="lb-stamp" style="--r: 3deg;">Free</span>
                            </div>
                            <p>Embed the calendar in the site you already have, and sync two ways with Google, Outlook or CalDAV. Worth knowing: a recurring program crosses to those calendars as a single entry, so it is the iCal feed, not the sync, that unrolls every Tuesday.</p>
                        </div>
                        <div class="lb-way" data-reveal>
                            <div class="lb-way-head">
                                <h3>Into their own calendar</h3>
                                <span class="lb-stamp" style="--r: -2deg;">Free</span>
                            </div>
                            <p>Every date has an iCal download, so a parent puts Tuesday 10:00 in the phone they actually check. Or they subscribe to the library's calendar once, and the next three months of programs sit there, following when a date moves or a closed day comes out.</p>
                        </div>
                    </div>

                    <!-- The bookmark, with the schedule's QR code. -->
                    <div class="lb-bookmark-col" data-reveal="zoom">
                        <div class="lb-bookmark-wrap" aria-hidden="true">
                            <i class="lb-ribbon"></i>
                            <div class="lb-bookmark lb-obj">
                                <div class="lb-bookmark-call">GUIDE<br>QR</div>
                                <p class="lb-bookmark-title">What's on at the branch</p>
                                <svg aria-hidden="true" viewBox="0 0 29 29" fill="#241d12">
                                    <rect x="0" y="0" width="9" height="9" fill="none" stroke="#241d12" stroke-width="2"></rect>
                                    <rect x="3" y="3" width="3" height="3"></rect>
                                    <rect x="20" y="0" width="9" height="9" fill="none" stroke="#241d12" stroke-width="2"></rect>
                                    <rect x="23" y="3" width="3" height="3"></rect>
                                    <rect x="0" y="20" width="9" height="9" fill="none" stroke="#241d12" stroke-width="2"></rect>
                                    <rect x="3" y="23" width="3" height="3"></rect>
                                    @foreach ([[11,1],[13,1],[11,3],[15,3],[12,5],[14,5],[11,7],[13,7],[1,11],[3,11],[5,11],[7,11],[11,11],[13,11],[16,11],[19,11],[22,11],[25,11],[2,13],[6,13],[12,13],[15,13],[18,13],[21,13],[24,13],[27,13],[1,15],[4,15],[7,15],[11,15],[14,15],[17,15],[20,15],[23,15],[26,15],[3,17],[5,17],[12,17],[16,17],[19,17],[22,17],[25,17],[11,19],[13,19],[15,19],[18,19],[21,19],[24,19],[27,19],[12,21],[14,21],[17,21],[20,21],[23,21],[26,21],[11,23],[15,23],[18,23],[22,23],[25,23],[12,25],[14,25],[16,25],[19,25],[23,25],[27,25],[11,27],[13,27],[17,27],[21,27],[24,27]] as [$qx, $qy])
                                        <rect x="{{ $qx }}" y="{{ $qy }}" width="2" height="2"></rect>
                                    @endforeach
                                </svg>
                                <p class="lb-bookmark-scan">Scan for every program, every date</p>
                                <span class="lb-typed lb-bookmark-foot">Your branch &middot; free to download</span>
                            </div>
                        </div>
                        <p class="lb-bookmark-cap">
                            Every schedule has a QR code you can download and print on a bookmark, a shelf label or the back of a receipt. It is on the free plan and it always points at your live calendar.
                        </p>
                    </div>
                </div>

                <p class="lb-cover-foot" data-reveal>
                    <span class="lb-stamp" style="--r: -3deg;">Free</span>
                    goes one step further still: embed the sign-up form itself, and a patron registers for Tuesday without ever leaving the library website.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Everything else: six volumes lying on the table           -->
        <!-- ============================================================ -->
        <section id="rest" class="lb-section" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap">
                <div class="lb-mark" data-reveal>
                    <span class="lb-call" aria-hidden="true">658<br>ETC</span>
                    <p class="lb-typed lb-kicker">Everything else</p>
                </div>
                <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                    The rest of the drawer.
                </h2>

                <div class="lb-rest-grid">
                    <div class="lb-flats" aria-hidden="true" data-reveal>
                        @foreach ($lbFlats as [$lbCloth, $lbW, $lbT, $lbX, $lbCall, $lbWord, $lbPlan])
                            <div class="lb-flat" style="--cloth: {{ $lbCloth }}; --w: {{ $lbW }}; --t: {{ $lbT }}; --x: {{ $lbX }};">
                                <span class="lb-spine-call">{{ $lbCall }}</span>
                                <b>{{ $lbWord }}</b>
                                <em>{{ $lbPlan }}</em>
                            </div>
                        @endforeach
                    </div>

                    <div class="lb-items">
                        <!-- 1 -->
                        <article class="lb-item" data-reveal>
                            <span class="lb-typed lb-item-no" aria-hidden="true">658.1</span>
                            <div>
                                <div class="lb-item-head">
                                    <h3>Which programs are actually being looked at</h3>
                                    <span class="lb-stamp" style="--r: -3deg;">Free</span>
                                </div>
                                <p>The built-in analytics show page views, the devices patrons are on, and where the traffic came from, per program and over time. Useful when the board asks whether the calendar is doing anything.</p>
                                <p>Worth saying plainly: that is what they measure. Attendance is what you count at the door, or what registration counted for you.</p>
                            </div>
                        </article>

                        <!-- 2 -->
                        <article class="lb-item" data-reveal>
                            <span class="lb-typed lb-item-no" aria-hidden="true">658.2</span>
                            <div>
                                <div class="lb-item-head">
                                    <h3>Next year's summer reading</h3>
                                    <span class="lb-stamp" style="--r: 2deg;">Free</span>
                                </div>
                                <p>Clone last year's program and change the dates. The description, the recurrence with its date exceptions, the venue and the place limit all come with it.</p>
                            </div>
                        </article>

                        <!-- 3 -->
                        <article class="lb-item" data-reveal>
                            <span class="lb-typed lb-item-no" aria-hidden="true">658.3</span>
                            <div>
                                <div class="lb-item-head">
                                    <h3>Photos from the craft table</h3>
                                    <span class="lb-stamp" style="--r: -2deg;">Free</span>
                                </div>
                                <p>Patrons add photos, video and comments to a program, and every submission waits in an approval queue. Free covers 25 photos per schedule, which matters when children are in them.</p>
                            </div>
                        </article>

                        <!-- 4 -->
                        <article class="lb-item" data-reveal>
                            <span class="lb-typed lb-item-no" aria-hidden="true">658.4</span>
                            <div>
                                <div class="lb-item-head">
                                    <h3>When a program costs money</h3>
                                    <span class="lb-stamp lb-stamp-pro" style="--r: 3deg;">Pro</span>
                                </div>
                                <p>An author evening, a paid workshop, a Friends of the Library fundraiser. Named ticket types with their own prices and quantities, sold on the Pro plan through your own Stripe or <a href="{{ marketing_url('/paypal') }}" class="lb-link">PayPal</a> account, a payment link or cash at the desk, and Event Schedule takes zero platform fees: past the provider's own processing, the money is yours. Announce it before tickets open, switch on the "Notify me" card, and patrons can leave an email address to hear when they do. If it is called off, Stripe and PayPal sales can be refunded from the Sales page, in full or in part, and the money goes back to the patron.</p>
                                <p>Pro at {{ plan_price($proMonthly) }} a month is what lets a ticket carry a price, and it brings the desk work with it: extra questions at checkout for access needs or a child's age, a waitlist once a ticket type sells out, and a live count as patrons check in. Scanning the QR on a ticket is free on every plan; it is the running total that is Pro. Free registration for free programs needs none of it, on any plan.</p>
                            </div>
                        </article>

                        <!-- 5 -->
                        <article class="lb-item" data-reveal>
                            <span class="lb-typed lb-item-no" aria-hidden="true">658.5</span>
                            <div>
                                <div class="lb-item-head">
                                    <h3>The poster for the noticeboard</h3>
                                    <span class="lb-stamp" style="--r: -3deg;">Free</span>
                                </div>
                                <p>Generate one graphic from your upcoming programs, up to twenty of them, in a story, square, portrait or landscape crop. Only programs carrying their own flyer image appear, and printing the date on each is a setting, off until you turn it on.</p>
                            </div>
                        </article>

                        <!-- 6 -->
                        <article class="lb-item" data-reveal>
                            <span class="lb-typed lb-item-no" aria-hidden="true">658.6</span>
                            <div>
                                <div class="lb-item-head">
                                    <h3>Branches, staff logins and your own address</h3>
                                    <span class="lb-stamp lb-stamp-ent" style="--r: 2deg;">Enterprise</span>
                                </div>
                                <p>Being honest about the shape of this: the free plan is one team member, so if two people need their own login you are looking at Enterprise, which allows up to five, and which also puts the calendar on your own domain.</p>
                                <p>
                                    A separate schedule per branch is free and unlimited, and plenty of library systems run one shared account per branch instead.
                                    <a href="{{ marketing_url('/pricing') }}" class="lb-link">Compare the plans</a>
                                </p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Every branch: six bookplates                              -->
        <!-- ============================================================ -->
        <section id="who" class="lb-section lb-alt" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap">
                <div class="lb-head-c">
                    <div class="lb-mark" data-reveal>
                        <span class="lb-call" aria-hidden="true">027<br>LIB</span>
                        <p class="lb-typed lb-kicker">Every branch</p>
                    </div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Perfect for all types of <span class="lb-em">libraries</span>
                    </h2>
                    <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                        From a single branch to a university collection to a van with a route.
                    </p>
                </div>

                @php
                    // [initials, name, description, blog slug, stamp ink, stamp tilt]
                    $lbBranches = [
                        ['PL', 'Public Libraries', 'Community programs, story times, workshops, and author events. Keep your neighborhood informed and engaged.', 'for-public-libraries', 'var(--lb-st-pro)', '-6deg'],
                        ['UL', 'University Libraries', 'Lectures, research workshops, study sessions, and academic events. Reach students and faculty directly.', 'for-university-libraries', 'var(--lb-st-free)', '5deg'],
                        ['RR', 'Community Reading Rooms', 'Reading groups, literacy programs, and neighborhood book exchanges. Build a culture of reading.', 'for-community-reading-rooms', 'var(--lb-st-ent)', '-4deg'],
                        ['CL', 'Children\'s Libraries', 'Story times, crafts, summer reading programs, and educational events. Make reading fun for every child.', 'for-childrens-libraries', 'var(--lb-st-free)', '7deg'],
                        ['AR', 'Archive & Research Centers', 'Exhibitions, lectures, guided tours, and research workshops. Share your collections with the public.', 'for-archive-research-centers', 'var(--lb-st-pro)', '-5deg'],
                        ['ML', 'Mobile Libraries', 'Bookmobile stops, pop-up reading events, and outreach programs. Bring the library to your community.', 'for-mobile-libraries', 'var(--lb-st-ent)', '4deg'],
                    ];
                @endphp

                <div class="lb-plates" data-reveal-group="80">
                    @foreach ($lbBranches as [$lbInitials, $lbName, $lbDesc, $lbSlug, $lbInk, $lbTilt])
                        @php $lbPost = get_sub_audience_blog($lbSlug); @endphp
                        <article class="lb-exlibris" data-reveal>
                            <div class="lb-exlibris-top" aria-hidden="true">
                                <span class="lb-typed">Ex libris</span>
                                <span class="lb-oval" style="--s: {{ $lbInk }}; --r: {{ $lbTilt }};">{{ $lbInitials }}</span>
                            </div>
                            <h3>{{ $lbName }}</h3>
                            <p>{{ $lbDesc }}</p>
                            @if ($lbPost)
                                <a href="{{ blog_url('/' . $lbPost->slug) }}" class="lb-link lb-arrow-link" aria-label="Learn more about Event Schedule for {{ $lbName }}">
                                    Learn more
                                    {!! $lbArrow !!}
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Three steps: three lines in the accessions register       -->
        <!-- ============================================================ -->
        <section id="steps" class="lb-section" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap">
                <div class="lb-mark" data-reveal>
                    <span class="lb-call" aria-hidden="true">371.3<br>HOW</span>
                    <p class="lb-typed lb-kicker" aria-hidden="true">Accessions</p>
                </div>
                <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Three steps
                </h2>

                <div class="lb-register" data-reveal>
                    <div class="lb-register-head lb-typed" aria-hidden="true">
                        <span>No.</span>
                        <span>Entry</span>
                        <span>Particulars</span>
                    </div>
                    @foreach ([
                        ['01', 'Catalogue the program', 'Sign up as a venue schedule, add the branch, and enter the program once: the days it runs, the sub-schedule it belongs to, and an end date or a number of sessions.'],
                        ['02', 'Take out the closed days', 'Add date exceptions for public holidays and training days, and add single extra dates for one-off sessions. Set up your sub-schedules for children, teens, adults and seniors.'],
                        ['03', 'Open the places', 'Turn on free registration with a place limit, embed the calendar on the library site, print the QR code, and email the patrons who follow you when there is news.'],
                    ] as [$stepNum, $stepTitle, $stepBody])
                        <div class="lb-step">
                            <div class="lb-step-no" style="--n: {{ $loop->index }}; --r: {{ ['-7deg', '5deg', '-4deg'][$loop->index] }}; --s: {{ ['var(--lb-st-pro)', 'var(--lb-st-free)', 'var(--lb-st-pro)'][$loop->index] }};">{{ $stepNum }}</div>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Key features: see also                                   -->
        <!-- ============================================================ -->
        <section class="lb-section lb-alt">
            <div class="lb-wrap lb-also-grid">
                <div>
                    <div class="lb-mark" data-reveal>
                        <span class="lb-call" aria-hidden="true">025.4<br>SEE</span>
                        <p class="lb-typed lb-kicker" aria-hidden="true">See also</p>
                    </div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                    <p class="lb-also-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="lb-link lb-arrow-link">
                            See all features
                            {!! $lbArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $lbRefs = [
                        ['Recurring Events', 'Weekly, fortnightly or monthly programs, with date exceptions and an end', marketing_url('/features/recurring-events')],
                        ['Sub-schedules', 'Keep children, teen, adult and senior programming on their own strands', marketing_url('/features/sub-schedules')],
                        ['Embed Calendar', 'Add your program calendar to the library website with one snippet', marketing_url('/features/embed-calendar')],
                        ['Newsletters', 'Write to the patrons who follow you, with open and click rates', marketing_url('/features/newsletters')],
                        ['Waitlist', 'A full story time queues patrons for the next place, free on registration', marketing_url('/features/waitlist')],
                    ];
                @endphp
                <div class="lb-refs" data-reveal-group="60">
                    @foreach ($lbRefs as [$lbRefName, $lbRefDesc, $lbRefUrl])
                        <a href="{{ $lbRefUrl }}" class="lb-ref" data-reveal>
                            <span class="lb-typed lb-ref-see" aria-hidden="true">see also</span>
                            <strong>{{ $lbRefName }}</strong>
                            <small>{{ $lbRefDesc }}</small>
                            {!! $lbArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="lb-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 11. Related pages: on the next shelf                         -->
        <!-- ============================================================ -->
        <section class="lb-section">
            <div class="lb-wrap lb-next-grid">
                <div>
                    <div class="lb-mark" data-reveal>
                        <span class="lb-call" aria-hidden="true">027.6<br>NXT</span>
                        <p class="lb-typed lb-kicker" aria-hidden="true">On the next shelf</p>
                    </div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                    <p class="lb-also-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/use-cases') }}" class="lb-link lb-arrow-link">
                            See all use cases
                            {!! $lbArrow !!}
                        </a>
                    </p>
                </div>

                <div class="lb-case lb-next-case" data-reveal>
                    <div class="lb-shelf">
                        @foreach ([['/for-community-centers', 'Community Centers'], ['/for-spoken-word', 'Spoken Word'], ['/for-workshop-instructors', 'Workshop Instructors'], ['/for-theaters', 'Theaters']] as [$relHref, $relName])
                            <a href="{{ marketing_url($relHref) }}" class="lb-spine lb-c-{{ ['o', 'g', 't', 'r'][$loop->index] }}" style="--h: {{ ['18.8rem', '15.4rem', '20.4rem', '16.6rem'][$loop->index] }}; --w: {{ ['4.4rem', '4rem', '4.6rem', '4.1rem'][$loop->index] }};">
                                <span class="lb-spine-title">For {{ $relName }}</span>
                                <span class="lb-spine-call">
                                    Read more
                                </span>
                            </a>
                        @endforeach
                        <span class="lb-spine lb-fill lb-c-k lb-bands" style="--h: 14.2rem; --w: 1.6rem;" aria-hidden="true"></span>
                        <span class="lb-spine lb-fill lb-c-n" style="--h: 17.2rem; --w: 1.3rem;" aria-hidden="true"></span>
                        <span class="lb-pile" aria-hidden="true"><i></i><i></i></span>
                        <span class="lb-end" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 12. FAQ: ask a librarian                                     -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="lb-section lb-alt" style="scroll-margin-top: 5rem;">
            <div class="lb-wrap lb-faq-grid">
                <div class="lb-faq-head">
                    <div data-reveal><span class="lb-typed lb-brass" aria-hidden="true">Ask a librarian</span></div>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                        What librarians ask before they move a program calendar across.
                    </p>
                </div>

                <div class="lb-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq">
                            <summary>
                                <span class="lb-qa-no" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
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
        <!-- 13. Finale: the circulation desk                             -->
        <!-- ============================================================ -->
        <section id="claim" class="lb-desk" style="scroll-margin-top: 4rem;">
            <div class="lb-wrap lb-desk-grid">
                <div>
                    <p data-reveal><span class="lb-stamp" style="--r: -4deg;">Free forever</span></p>
                    <h2 class="lb-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Catalogue it once. <span class="lb-em">Check it out all year.</span>
                    </h2>
                    <p class="lb-lede" data-reveal style="--reveal-delay: 0.16s;">
                        Your program calendar, recurring sessions, free registration with a place limit, and a newsletter to the patrons who follow you. All of it free, with no card.
                    </p>
                </div>

                <!-- The borrower's card. -->
                <div class="lb-libcard lb-obj" data-reveal="panel">
                    <div class="lb-libcard-top">
                        <strong aria-hidden="true">Borrower's card</strong>
                        <span class="lb-typed" aria-hidden="true">No. 000241</span>
                    </div>
                    <label for="es-claim-input" class="lb-typed">Your schedule name</label>
                    <div class="lb-libcard-form">
                        <div dir="ltr" class="es-claim lb-claim">
                            <input id="es-claim-input" type="text" placeholder="your-library" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="lb-btn lb-btn-cloth">
                            Create your calendar
                            {!! $lbArrow !!}
                        </a>
                    </div>
                    <div class="lb-libcard-foot" aria-hidden="true">
                        <i class="lb-barcode"></i>
                        <small class="lb-typed">Member since 2026<br>Keep this card</small>
                    </div>
                    <p class="lb-libcard-note">No credit card required</p>
                </div>
            </div>
        </section>

        <div class="lb-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
