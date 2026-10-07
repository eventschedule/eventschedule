<x-marketing-layout>
    <x-slot name="title">Art Gallery Calendars | The Show Runs, the Evenings Do Not</x-slot>
    <x-slot name="description">A six-week exhibition is one recurring event that stops itself on the closing date, not thirty entries. The private view and artist talk go on top.</x-slot>
    <x-slot name="breadcrumbTitle">For Art Galleries</x-slot>

    <x-slot name="headMeta">
        {{-- The page's one typeface, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Jost') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Art Galleries"
        description="An exhibition calendar where the run is one recurring event and the private view, artist talk and closing are separate evenings."
        audience="Art Galleries, Project Spaces & Artist Cooperatives"
        keywords="gallery calendar, exhibition schedule, private view rsvp, art gallery events, artist talk booking, exhibition proposal form" />
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
           For-art-galleries "White Cube" styles. The page is the gallery:
           white walls, a concrete floor, works hung at eye level and a
           small label beside each. The type is kept almost silent (Jost
           at one weight, Georgia italic for titles) and all the colour
           is in the works, which are made of nothing but CSS: gradients,
           blur on inner layers, clip-paths and masks.

           Everything is scoped under #ag. The works keep their own
           colours in both modes, as objects do; only the room changes.
           ============================================================== */

        #ag {
            --ag-wall: #fbfaf7;
            --ag-wall-2: #f3f1eb;
            --ag-card: #ffffff;
            --ag-floor-a: #dedbd3;
            --ag-floor-b: #f1efe9;
            --ag-ink: #111111;
            --ag-ink-2: #3b3b39;
            --ag-label: #63635f;
            --ag-line: rgba(17, 17, 17, 0.16);
            --ag-hair: rgba(17, 17, 17, 0.09);
            --ag-red: #e10600;
            --ag-red-ink: #c00500;
            --ag-beam: rgba(255, 255, 255, 0.9);
            --ag-cast: rgba(17, 17, 17, 0.36);
            --ag-box: #161615;
            --ag-box-ink: #f2f0eb;
            --ag-box-2: #cfcdc7;
            --ag-box-label: #a5a39d;
            --ag-box-line: rgba(242, 240, 235, 0.2);
            --ag-box-beam: rgba(255, 238, 208, 0.1);
            --ag-box-cast: rgba(0, 0, 0, 0.8);
            --ag-sans: 'Jost', Futura, 'Century Gothic', 'Avenir Next', Avenir, 'Helvetica Neue', Arial, sans-serif;
            --ag-serif: Georgia, 'Times New Roman', Times, serif;
            position: relative;
            background: var(--ag-wall);
            color: var(--ag-ink);
            font-family: var(--ag-sans);
            font-size: 1.0625rem;
            font-weight: 400;
            line-height: 1.6;
        }
        .dark #ag {
            --ag-wall: #141414;
            --ag-wall-2: #1a1a19;
            --ag-card: #1f1f1e;
            --ag-floor-a: #080808;
            --ag-floor-b: #141414;
            --ag-ink: #f2f0eb;
            --ag-ink-2: #cfcdc7;
            --ag-label: #a09e98;
            --ag-line: rgba(242, 240, 235, 0.18);
            --ag-hair: rgba(242, 240, 235, 0.1);
            --ag-red: #ff3b30;
            --ag-red-ink: #ff6f64;
            --ag-beam: rgba(255, 238, 208, 0.085);
            --ag-cast: rgba(0, 0, 0, 0.8);
            --ag-box: #efede7;
            --ag-box-ink: #111111;
            --ag-box-2: #3b3b39;
            --ag-box-label: #5d5d59;
            --ag-box-line: rgba(17, 17, 17, 0.18);
            --ag-box-beam: rgba(255, 255, 255, 0.9);
            --ag-box-cast: rgba(17, 17, 17, 0.36);
        }

        /* The bar above is painted the colour of the wall it hangs over. */
        body > header.sticky {
            background-color: rgba(251, 250, 247, 0.9);
            border-bottom-color: rgba(17, 17, 17, 0.1);
        }
        .dark body > header.sticky {
            background-color: rgba(20, 20, 20, 0.9);
            border-bottom-color: rgba(242, 240, 235, 0.12);
        }

        #ag ::selection { background: var(--ag-ink); color: var(--ag-wall); }
        #ag a:focus-visible,
        #ag summary:focus-visible,
        #ag input:focus-visible,
        #ag [tabindex]:focus-visible {
            outline: 2px solid var(--ag-ink);
            outline-offset: 4px;
        }

        .ag-wrap { width: min(100% - 3rem, 76rem); margin-inline: auto; }

        /* ---------------------------------------------------------------
           Type. One sans at one weight; the italic is the title voice.
           --------------------------------------------------------------- */
        .ag-cap,
        .ag-kicker {
            font-size: 0.72rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            line-height: 1.5;
            color: var(--ag-label);
        }
        .ag-kicker { display: flex; align-items: baseline; gap: 1.1rem; }
        .ag-kicker span { color: var(--ag-ink); letter-spacing: 0.1em; font-variant-numeric: tabular-nums; }
        .ag-it { font-family: var(--ag-serif); font-style: italic; font-size: 0.95em; letter-spacing: 0; }
        .ag-h2 {
            margin-top: 1.6rem;
            font-size: clamp(2rem, 3.7vw, 3.15rem);
            font-weight: 400;
            line-height: 1.1;
            letter-spacing: -0.012em;
            text-wrap: balance;
        }
        .ag-p { margin-top: 1.6rem; max-width: 33rem; color: var(--ag-ink-2); font-size: 1.125rem; line-height: 1.6; text-wrap: pretty; }
        .ag-link {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.76rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 0.5em;
            /* A 24px target: the padding is taken back by the margin, so nothing moves. */
            padding-block: 0.2rem;
            margin-block: -0.2rem;
            transition: gap 0.25s ease;
        }
        .ag-link:hover { gap: 1.1rem; }
        .ag-link svg,
        .ag-btn svg { width: 1.05rem; height: 1.05rem; flex: none; }
        .ag-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.9rem;
            padding: 1.05rem 1.5rem;
            background: var(--ag-ink);
            color: var(--ag-wall);
            font-size: 0.76rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            line-height: 1;
            transition: gap 0.25s ease, padding 0.25s ease;
        }
        .ag-btn:hover { gap: 1.3rem; }
        .ag-tier {
            display: inline-block;
            padding: 0.22rem 0.5rem 0.18rem;
            border: 1px solid var(--ag-line);
            font-size: 0.66rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            line-height: 1.3;
            color: var(--ag-ink);
            white-space: nowrap;
        }
        .ag-tierline { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.6rem 0.9rem; margin-top: 2.25rem; font-size: 0.9375rem; color: var(--ag-ink-2); }

        /* ---------------------------------------------------------------
           Rooms
           --------------------------------------------------------------- */
        .ag-room { position: relative; isolation: isolate; overflow: clip; padding-block: clamp(5.5rem, 12vw, 11rem); }
        .ag-room-2 { background: var(--ag-wall-2); }
        .ag-room + .ag-room:not(.ag-room-2):not(.ag-box) { border-top: 1px solid var(--ag-hair); }
        .ag-head { max-width: 44rem; }
        .ag-pair { display: grid; grid-template-columns: minmax(0, 1fr); gap: 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .ag-pair { grid-template-columns: minmax(0, 1fr) minmax(0, 0.82fr); gap: clamp(4rem, 9vw, 9rem); }
            .ag-pair-flip { grid-template-columns: minmax(0, 0.82fr) minmax(0, 1fr); }
            .ag-pair-flip > .ag-text { order: 2; }
            .ag-pair-wide { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); }
        }
        .ag-side { min-width: 0; }
        .ag-side .ag-hang { width: min(100%, 27rem); }
        .ag-pair-flip .ag-side .ag-hang { width: min(100%, 30rem); }
        .ag-points { margin-top: 2.25rem; max-width: 33rem; border-top: 1px solid var(--ag-line); }
        .ag-points li { padding-block: 1rem; border-bottom: 1px solid var(--ag-hair); font-size: 1rem; color: var(--ag-ink-2); }
        .ag-points b { font-weight: 400; color: var(--ag-ink); }
        .ag-points-2 li b { display: block; margin-bottom: 0.2rem; }

        /* ---------------------------------------------------------------
           A work on the wall: the light from above, the canvas, its cast
           shadow. The inner layers are the painting.
           --------------------------------------------------------------- */
        .ag-hang { position: relative; margin: 0; }
        .ag-hang::before {
            content: "";
            position: absolute;
            inset: -24% -26% -16% -26%;
            z-index: -1;
            background: radial-gradient(ellipse 50% 50% at 50% 40%, var(--ag-beam) 0, var(--ag-beam) 36%, transparent 72%);
            pointer-events: none;
            animation: ag-glow 9s ease-in-out infinite alternate;
        }
        @keyframes ag-glow { from { opacity: 0.72; } to { opacity: 1; } }
        .ag-canvas {
            position: relative;
            z-index: 1;
            display: block;
            overflow: hidden;
            container-type: inline-size;
            aspect-ratio: var(--ar, 4 / 5);
            background: #dddad2;
            box-shadow:
                0 0 0 1px rgba(17, 17, 17, 0.06),
                0 1px 2px rgba(17, 17, 17, 0.14),
                0 1.4rem 1.8rem -1.1rem var(--ag-cast),
                0 2.8rem 3.2rem -2.2rem var(--ag-cast);
        }
        .ag-canvas i { position: absolute; z-index: 1; display: block; }
        /* The weave of the cloth, and after dark the fall-off of the lamp. */
        .ag-canvas::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            background:
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.05) 0 1px, transparent 1px 3px),
                repeating-linear-gradient(90deg, rgba(0, 0, 0, 0.035) 0 1px, transparent 1px 3px);
            mix-blend-mode: overlay;
            pointer-events: none;
        }
        .dark .ag-canvas::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 3;
            background: radial-gradient(ellipse 95% 85% at 50% 28%, rgba(0, 0, 0, 0.04) 30%, rgba(0, 0, 0, 0.46));
            pointer-events: none;
        }
        .ag-breathe i { animation: ag-breathe 12s ease-in-out infinite alternate; }
        .ag-breathe i:nth-child(2) { animation-duration: 15s; animation-delay: -6s; }
        @keyframes ag-breathe { from { scale: 1; opacity: 0.9; } to { scale: 1.04; opacity: 1; } }
        .ag-made { position: relative; z-index: 1; margin-top: 1rem; font-size: 0.66rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ag-label); }
        .ag-made + .ag-label, .ag-hang:has(.ag-made) + .ag-label { margin-top: 1.1rem; }

        /* 01 A field. Three soft rectangles on a brick ground. */
        .ag-w-field { --ar: 5 / 6; background: linear-gradient(#8d2c18, #6f1f13); }
        .ag-w-field i:nth-child(1) { inset: 7% 8% 47% 8%; background: #f2972a; filter: blur(2.6cqi); }
        .ag-w-field i:nth-child(2) { inset: 58% 8% 7% 8%; background: #3c0d0c; filter: blur(2.6cqi); }
        .ag-w-field i:nth-child(3) { inset: 50.4% 12% 44.6% 12%; background: #f7d596; filter: blur(2cqi); opacity: 0.55; animation: none; }

        /* 02 A pencil grid of thirty columns, and one band laid across all of them. */
        .ag-w-grid { --ar: 1 / 1; background: #f1ede2; }
        .ag-w-grid i:nth-child(1) {
            inset: 9%;
            border: 1px solid rgba(52, 64, 84, 0.36);
            background:
                repeating-linear-gradient(90deg, rgba(52, 64, 84, 0.36) 0 1px, transparent 1px calc(100% / 30)),
                repeating-linear-gradient(0deg, rgba(52, 64, 84, 0.18) 0 1px, transparent 1px 5%);
        }
        .ag-w-grid i:nth-child(2) { inset: 45.6% 9% 45.6% 9%; background: #b9532c; mix-blend-mode: multiply; opacity: 0.86; }

        /* 03 to 06 Four tondi, one for each evening. */
        .ag-tondo { --ar: 1 / 1; border-radius: 50%; }
        .ag-w-eve-1 { background: radial-gradient(circle at 30% 80%, #e8892f 0, rgba(232, 137, 47, 0) 48%), linear-gradient(#0b1b2b, #14304a 58%, #3a2826); }
        .ag-w-eve-2 { background: radial-gradient(circle at 62% 38%, #fffdf2 0 8%, rgba(255, 253, 242, 0) 44%), linear-gradient(#f1d489, #e7b352); }
        .ag-w-eve-3 { background: radial-gradient(ellipse 90% 38% at 50% 62%, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0) 70%), linear-gradient(#86bcc4, #d6e6dc 68%, #b4c9ae); }
        .ag-w-eve-4 { background: radial-gradient(circle at 50% 98%, #ffd27a 0, rgba(255, 210, 122, 0) 42%), linear-gradient(#13212f, #a3402a 62%, #e5762e 80%, #2a1612); }

        /* 07 and 08 A diptych: one zip each. */
        .ag-w-zip-a { background: linear-gradient(100deg, #ece7db, #e2dccd); }
        .ag-w-zip-a i { inset: 0 auto 0 66%; width: 1.7%; background: #0f4c5c; }
        .ag-w-zip-b { background: linear-gradient(100deg, #12322f, #0b2422); }
        .ag-w-zip-b i { inset: 0 auto 0 31%; width: 1.7%; background: #dcb35c; }

        /* 09 Stripes. */
        .ag-w-stripes {
            --ar: 5 / 4;
            background: linear-gradient(90deg,
                #1f6f78 0 7%, #efe6cf 7% 12%, #b5532a 12% 21%, #1a2327 21% 23%, #e6c36a 23% 34%, #efe6cf 34% 37%,
                #1f6f78 37% 52%, #b5532a 52% 55%, #efe6cf 55% 66%, #1a2327 66% 68%, #e6c36a 68% 74%, #1f6f78 74% 83%,
                #efe6cf 83% 88%, #b5532a 88% 100%);
        }

        /* 10 Hard edge, two cuts. */
        .ag-w-edge { background: #f2efe6; }
        .ag-w-edge i:nth-child(1) { inset: 0; background: #17834a; clip-path: polygon(0 100%, 0 34%, 58% 0, 100% 0, 100% 46%, 38% 100%); }
        .ag-w-edge i:nth-child(2) { inset: 0; background: #0d0d0d; clip-path: polygon(64% 100%, 100% 72%, 100% 100%); }

        /* 11 Squares inside squares. */
        .ag-w-squares { --ar: 1 / 1; background: #e9a72c; }
        .ag-w-squares i:nth-child(1) { inset: 9% 9% 5% 9%; background: #e28a1f; }
        .ag-w-squares i:nth-child(2) { inset: 19% 19% 10% 19%; background: #d96a1b; }
        .ag-w-squares i:nth-child(3) { inset: 30% 30% 16% 30%; background: #f3d36b; }

        /* 12 to 17 The six on the long wall. */
        .ag-w-arc { background: radial-gradient(circle at 0% 100%, #1648a8 0 70%, #f2efe6 70.3%); }
        .ag-w-photo { background: #f7f6f2; border: 0.5rem solid #161616; }
        .dark .ag-w-photo { outline: 1px solid var(--ag-hair); }
        .ag-w-photo i { inset: 13%; background: linear-gradient(#dcdcd8 0, #bcbcb8 49.7%, #3b3b3a 50%, #1b1b1b 100%); box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.12); }
        .ag-w-weave {
            background:
                repeating-linear-gradient(0deg, rgba(122, 62, 28, 0.58) 0 6%, transparent 6% 12%),
                repeating-linear-gradient(90deg, rgba(31, 111, 120, 0.58) 0 6%, transparent 6% 12%),
                #ead9b8;
        }
        .ag-w-target { --ar: 1 / 1; background: radial-gradient(circle at 50% 50%, #f4cf3d 0 12%, #e0432a 12.3% 27%, #f2efe6 27.3% 35%, #1f6f78 35.3% 50%, #111111 50.3% 53%, #f2efe6 53.3%); }
        .ag-w-chart { display: grid; grid-template-columns: repeat(6, 1fr); gap: 2.4%; align-content: center; padding: 8%; background: #f4f2ec; }
        .ag-w-chart i { position: static; aspect-ratio: 1; background: var(--c); }
        .ag-w-gilt {
            border: 0.95rem solid;
            border-image: linear-gradient(135deg, #f1d48a, #a67c2c 30%, #f6e2a6 50%, #8a6420 75%, #e3c069) 1;
            background: radial-gradient(ellipse 60% 50% at 38% 34%, #6f4c2b 0, #2a1a10 55%, #120b07 100%);
        }

        /* 18 to 20 Three monochromes, each a tone down. */
        .ag-w-mono-1 { --ar: 1 / 1; background: linear-gradient(140deg, #e3ece7, #cfe0d9); }
        .ag-w-mono-2 { --ar: 1 / 1; background: linear-gradient(140deg, #6aa8a2, #4f918b); }
        .ag-w-mono-3 { --ar: 1 / 1; background: linear-gradient(140deg, #1b4d52, #10363a); }

        /* 21 A long horizon. */
        .ag-w-pano { --ar: 5 / 4; background: linear-gradient(#0d343c, #124450 42%, #1c1410 80%); }
        @media (min-width: 700px) { .ag-w-pano { --ar: 16 / 6; } }
        .ag-w-pano i:nth-child(1) { inset: 43% -6% 39% -6%; background: #f08a24; filter: blur(1.7cqi); }
        .ag-w-pano i:nth-child(2) { inset: 49% 22% 47% 22%; background: #ffe2a6; filter: blur(0.9cqi); }

        /* ---------------------------------------------------------------
           The label beside a work.
           --------------------------------------------------------------- */
        .ag-label {
            position: relative;
            max-width: 24rem;
            margin-top: 1.9rem;
            padding: 1.15rem 1.25rem 1.2rem;
            background: var(--ag-card);
            box-shadow: 0 0 0 1px var(--ag-hair), 0 1px 1px rgba(17, 17, 17, 0.08), 0 0.6rem 0.9rem -0.7rem rgba(17, 17, 17, 0.35);
            font-size: 0.875rem;
            line-height: 1.55;
            color: var(--ag-ink-2);
        }
        .ag-label-bare { padding: 0; background: none; box-shadow: none; }
        .ag-label-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.4rem 1rem; }
        .ag-label-title { font-family: var(--ag-serif); font-style: italic; font-size: 1.125rem; line-height: 1.3; color: var(--ag-ink); }
        .ag-label-meta { color: var(--ag-label); text-wrap: balance; }
        .ag-label-note { margin-top: 0.9rem; padding-top: 0.8rem; border-top: 1px solid var(--ag-hair); color: var(--ag-label); }
        .ag-label-bare .ag-label-note { margin-top: 0.5rem; padding-top: 0; border-top: 0; }
        .ag-rows { margin-top: 0.8rem; }
        .ag-rows div { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-block: 0.5rem; border-top: 1px solid var(--ag-hair); }
        .ag-rows dt { flex: none; font-size: 0.68rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--ag-label); }
        .ag-rows dd { min-width: 0; text-align: end; color: var(--ag-ink); }
        .ag-rows-name dt { font-size: 0.875rem; letter-spacing: 0; text-transform: none; color: var(--ag-ink); }
        .ag-rows-name dd { color: var(--ag-label); font-variant-numeric: tabular-nums; }
        .ag-label ul { margin-top: 0.8rem; }
        .ag-label li { position: relative; padding: 0.5rem 0 0.5rem 1.1rem; border-top: 1px solid var(--ag-hair); }
        .ag-label li::before { content: ""; position: absolute; left: 0.1rem; top: 1.1rem; width: 0.3rem; height: 1px; background: var(--ag-ink); }
        .ag-dot { position: absolute; right: -0.45rem; top: -0.45rem; width: 0.9rem; height: 0.9rem; border-radius: 50%; background: var(--ag-red); box-shadow: 0 1px 2px rgba(17, 17, 17, 0.3); }

        /* ---------------------------------------------------------------
           Section index: a card of room numbers, wide screens only
           --------------------------------------------------------------- */
        .ag-nav { display: none; }
        @media (min-width: 1520px) {
            /* The nav is a box the size of the page that clips the card, so the card stays fixed
               to the screen and still ends where the page does instead of riding over the site
               footer. The long wall runs to the edge of the window, so it passes over the card. */
            .ag-nav { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .ag-nav ol { position: fixed; right: 1.4rem; top: 50%; translate: 0 -50%; pointer-events: auto; padding: 0.55rem 0.5rem; background: var(--ag-card); box-shadow: 0 0 0 1px var(--ag-hair), 0 0.6rem 0.9rem -0.7rem rgba(17, 17, 17, 0.35); }
            .ag-walk-room { z-index: 41; }
            .ag-nav a { position: relative; display: block; padding: 0.25rem 0.35rem 0.25rem 0.9rem; font-size: 0.66rem; letter-spacing: 0.1em; font-variant-numeric: tabular-nums; color: var(--ag-label); }
            .ag-nav a::before { content: ""; position: absolute; left: 0.2rem; top: 50%; width: 0.32rem; height: 0.32rem; margin-top: -0.16rem; border-radius: 50%; background: transparent; transition: background-color 0.3s ease; }
            .ag-nav a.is-active { color: var(--ag-ink); }
            .ag-nav a.is-active::before { background: var(--ag-red); }
            .ag-nav-name {
                position: absolute;
                right: calc(100% + 0.9rem);
                top: 50%;
                translate: 0.3rem -50%;
                padding: 0.3rem 0.6rem;
                background: var(--ag-ink);
                color: var(--ag-wall);
                letter-spacing: 0.16em;
                text-transform: uppercase;
                white-space: nowrap;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.2s ease, translate 0.2s ease;
            }
            .ag-nav a:hover .ag-nav-name,
            .ag-nav a:focus-visible .ag-nav-name { opacity: 1; translate: 0 -50%; }
        }

        /* ---------------------------------------------------------------
           Room 1: the title on the wall, one large work, the floor
           --------------------------------------------------------------- */
        .ag-hero { position: relative; display: flex; flex-direction: column; overflow: clip; min-height: calc(100svh - 4rem); --ag-d: 4.75rem; }
        .ag-hero-grid { position: relative; z-index: 1; flex: 1; display: grid; grid-template-columns: minmax(0, 1fr); gap: 4rem; align-items: center; padding-block: clamp(3.5rem, 7vw, 6rem) 3.5rem; }
        .ag-eyebrow { display: block; margin-bottom: 2.2rem; font-family: var(--ag-sans); font-size: 0.72rem; letter-spacing: 0.2em; text-transform: uppercase; line-height: 1.5; color: var(--ag-label); }
        .ag-h1 { font-size: clamp(2.75rem, 5.1vw, 4.6rem); font-weight: 400; line-height: 1.03; letter-spacing: -0.02em; }
        .ag-h1 .es-mask-line { text-wrap: balance; }
        .ag-lede { margin-top: 2.2rem; max-width: 31rem; font-size: clamp(1.0625rem, 1.3vw, 1.2rem); color: var(--ag-ink-2); text-wrap: pretty; }
        .ag-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.5rem 2rem; margin-top: 2.6rem; }
        .ag-hero-wall { min-width: 0; }
        .ag-hang-hero { width: min(100%, 25rem); }
        .ag-label-hero { max-width: 25rem; }
        .ag-label-hero .ag-label-title { margin-top: 0.5rem; font-size: 1.25rem; }
        @media (min-width: 960px) {
            .ag-hero-grid { grid-template-columns: minmax(0, 0.86fr) minmax(0, 1.14fr); gap: clamp(2.5rem, 5vw, 5rem); padding-bottom: 0; }
            .ag-hero-wall { align-self: end; display: grid; grid-template-columns: minmax(0, 1fr) clamp(14.25rem, 19vw, 15.5rem); gap: clamp(1.25rem, 2vw, 1.9rem); align-items: end; padding-bottom: var(--ag-d); }
            .ag-hang-hero { width: 100%; justify-self: end; max-width: 26rem; }
            .ag-label-hero { margin-top: 0; }
            .ag-reflect { -webkit-box-reflect: below calc(var(--ag-d) * 2) linear-gradient(to bottom, transparent 80%, rgba(0, 0, 0, 0.17)); }
        }
        /* The run, drawn as it is counted: a mark for each day, tall when the doors are open,
           and a red dot over each of the four evenings. */
        .ag-run { display: flex; align-items: flex-end; gap: 2px; height: 2.4rem; margin-top: 1.1rem; }
        .ag-day { position: relative; flex: 1; min-width: 0; }
        .ag-day-open,
        .ag-day-eve { height: 1.35rem; background: var(--ag-ink); }
        .ag-day-shut { height: 0.3rem; background: var(--ag-line); }
        .ag-day-eve::before { content: ""; position: absolute; left: 50%; bottom: calc(100% + 0.3rem); width: 0.42rem; height: 0.42rem; margin-left: -0.21rem; border-radius: 50%; background: var(--ag-red); }
        .ag-run-stats { display: grid; grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.3fr) minmax(0, 1fr); gap: 0.75rem; margin-top: 1rem; padding-top: 0.9rem; border-top: 1px solid var(--ag-hair); }
        .ag-run-stats dd { font-size: 1.5rem; line-height: 1.1; color: var(--ag-ink); font-variant-numeric: tabular-nums; }
        .ag-run-stats dt { margin-top: 0.25rem; font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase; line-height: 1.35; color: var(--ag-label); }
        .ag-floor {
            position: relative;
            z-index: 0;
            height: clamp(4.5rem, 9vw, 8.5rem);
            background: linear-gradient(to bottom, var(--ag-floor-a), var(--ag-floor-b));
            box-shadow: inset 0 1px 0 var(--ag-line), inset 0 0.5rem 0.9rem -0.6rem rgba(17, 17, 17, 0.25);
        }

        /* ---------------------------------------------------------------
           Walls of several works
           --------------------------------------------------------------- */
        .ag-row { display: grid; gap: 3.5rem 2.5rem; margin-top: clamp(3.5rem, 7vw, 6rem); }
        .ag-row-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 900px) { .ag-row-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 3.5rem clamp(2.5rem, 5vw, 5rem); } }
        .ag-row-4 .ag-hang { width: min(100%, 13rem); }
        .ag-row-4 .ag-label { margin-top: 1.6rem; }
        .ag-row-4 .ag-label-title { margin-top: 0.3rem; }
        .ag-piece { min-width: 0; transition: opacity 0.4s ease; }
        @media (hover: hover) {
            .ag-dim:has(.ag-piece:hover) .ag-piece:not(:hover) { opacity: 0.42; }
        }
        .ag-diptych { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; max-width: 46rem; }
        @media (min-width: 640px) { .ag-diptych { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.6rem; } }
        .ag-diptych .ag-label { margin-right: 1.25rem; }
        @media (min-width: 640px) {
            .ag-diptych .ag-piece:first-child .ag-hang::before { inset: -24% -128% -16% -26%; }
            .ag-diptych .ag-piece:last-child .ag-hang::before { display: none; }
        }
        .ag-salon { display: grid; grid-template-columns: minmax(0, 1fr); gap: 4rem; margin-top: clamp(3.5rem, 7vw, 6rem); }
        @media (min-width: 800px) {
            .ag-salon { grid-template-columns: minmax(0, 0.9fr) minmax(0, 1fr); gap: clamp(4rem, 12vw, 12rem); align-items: start; }
            .ag-salon .ag-piece:last-child { margin-top: 7rem; }
        }
        .ag-salon .ag-hang { width: min(100%, 22rem); }

        /* ---------------------------------------------------------------
           The long wall. A plain swipeable row everywhere; where scroll
           timelines exist and the window is wide, the room holds still
           and the wall walks past.
           --------------------------------------------------------------- */
        .ag-walk-head { margin-bottom: clamp(3rem, 6vw, 4.5rem); }
        .ag-walk-view { overflow-x: auto; scroll-snap-type: x proximity; scrollbar-width: thin; scrollbar-color: var(--ag-label) transparent; padding-block: 6rem 2rem; margin-top: -6rem; }
        .ag-walk { display: flex; align-items: flex-start; gap: clamp(2.75rem, 7vw, 6.5rem); width: max-content; padding-inline: max(1.5rem, calc((100vw - 76rem) / 2)); }
        .ag-walk .ag-piece { flex: none; width: min(72vw, 19.5rem); scroll-snap-align: center; }
        .ag-walk .ag-hang { display: grid; align-items: center; aspect-ratio: 4 / 5; }
        .ag-walk .ag-label-title { margin-top: 0.3rem; }
        .ag-walk .ag-label a { display: inline-block; margin-top: 0.7rem; }
        .ag-walk-bar { display: none; }
        .ag-walk-hint { margin-top: 1rem; }
        @media (min-width: 1024px) and (min-height: 700px) and (prefers-reduced-motion: no-preference) {
            @supports (animation-timeline: view()) {
                .ag-walk-room { padding-block: 0; overflow: visible; }
                .ag-walk-pin { height: calc(100vh + 84rem); view-timeline: --ag-pin block; view-timeline-inset: 4rem 0px; }
                .ag-walk-stick { position: sticky; top: 4rem; height: calc(100vh - 4rem); display: flex; flex-direction: column; justify-content: center; overflow: clip; container-type: inline-size; }
                .ag-walk-head { margin-bottom: clamp(2rem, 5vh, 4rem); }
                .ag-walk-view { overflow: visible; padding-block: 0; margin-top: 0; }
                .ag-walk .ag-piece { width: min(19.5rem, 29vh); }
                .ag-walk { animation: ag-walk linear both; animation-timeline: --ag-pin; animation-range: contain 0% contain 100%; }
                .ag-walk-bar { display: block; height: 1px; margin-top: clamp(1.75rem, 4vh, 3rem); background: var(--ag-line); }
                .ag-walk-bar i { display: block; height: 3px; margin-top: -1px; background: var(--ag-ink); transform-origin: 0 50%; animation: ag-bar linear both; animation-timeline: --ag-pin; animation-range: contain 0% contain 100%; }
                .ag-walk-hint { display: none; }
                .ag-walk-stick:focus-within .ag-walk-view { overflow-x: auto; }
                .ag-walk-stick:focus-within .ag-walk { animation: none; }
            }
        }
        @keyframes ag-walk { from { translate: 0 0; } to { translate: calc(100cqi - 100%) 0; } }
        @keyframes ag-bar { from { scale: 0 1; } to { scale: 1 1; } }

        /* ---------------------------------------------------------------
           The project room: dark by day, and the one lit room at night.
           --------------------------------------------------------------- */
        .ag-box { --ag-cast: var(--ag-box-cast); background: var(--ag-box); color: var(--ag-box-ink); }
        .ag-box .ag-canvas::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 3;
            background: radial-gradient(ellipse 95% 85% at 50% 28%, rgba(0, 0, 0, 0.04) 30%, rgba(0, 0, 0, 0.4));
            pointer-events: none;
        }
        .dark .ag-box .ag-canvas::before { content: none; }
        .ag-box .ag-kicker { color: var(--ag-box-label); }
        .ag-box .ag-kicker span { color: var(--ag-box-ink); }
        .ag-box .ag-hang::before { background: radial-gradient(ellipse 50% 50% at 50% 40%, var(--ag-box-beam) 0, var(--ag-box-beam) 36%, transparent 72%); }
        .ag-box .ag-label-title { color: var(--ag-box-ink); }
        .ag-box .ag-label,
        .ag-box .ag-label-note { color: var(--ag-box-2); }
        .ag-box .ag-cap { color: var(--ag-box-label); }
        #ag .ag-box a:focus-visible { outline-color: var(--ag-box-ink); }
        .ag-row-3 { position: relative; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 800px) { .ag-row-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: clamp(3rem, 7vw, 7rem); } }
        .ag-row-3 .ag-hang { width: min(100%, 14rem); }
        /* The hanging line every install starts from, pencilled across the wall. */
        .ag-centre { display: none; }
        @media (min-width: 800px) {
            .ag-centre { display: block; position: absolute; left: -3rem; right: -3rem; top: 7rem; height: 0; border-top: 1px dashed var(--ag-box-line); }
            .ag-centre span { display: none; position: absolute; right: 3rem; bottom: 0.5rem; font-size: 0.62rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--ag-box-label); }
        }

        @media (min-width: 1200px) { .ag-centre span { display: block; } }

        /* ---------------------------------------------------------------
           The checklist, as handed out at the desk
           --------------------------------------------------------------- */
        .ag-list-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; }
        @media (min-width: 1000px) { .ag-list-grid { grid-template-columns: minmax(0, 0.55fr) minmax(0, 1.45fr); gap: clamp(4rem, 9vw, 9rem); } }
        .ag-checklist { display: grid; grid-template-columns: auto minmax(0, 0.8fr) minmax(0, 1.5fr) auto; border-top: 1px solid var(--ag-ink); }
        .ag-checklist li,
        .ag-checklist a { display: grid; grid-column: 1 / -1; grid-template-columns: subgrid; }
        .ag-checklist a { column-gap: clamp(1rem, 3vw, 2.5rem); align-items: baseline; padding-block: 1.35rem; border-bottom: 1px solid var(--ag-line); transition: padding 0.3s ease, background-color 0.3s ease; }
        .ag-checklist a:hover { padding-inline: 0.9rem; background: var(--ag-card); }
        .ag-check-no { font-size: 0.72rem; letter-spacing: 0.1em; color: var(--ag-label); font-variant-numeric: tabular-nums; }
        .ag-check-title { font-family: var(--ag-serif); font-style: italic; font-size: 1.25rem; line-height: 1.25; }
        .ag-check-line { font-size: 0.9375rem; color: var(--ag-ink-2); }
        .ag-checklist svg { width: 1.05rem; height: 1.05rem; align-self: center; transition: translate 0.3s ease; }
        .ag-checklist a:hover svg { translate: 0.3rem 0; }
        @supports not (grid-template-columns: subgrid) {
            .ag-checklist li { display: block; }
            .ag-checklist a { grid-template-columns: 2rem minmax(0, 0.8fr) minmax(0, 1.5fr) 1.05rem; }
        }
        @media (max-width: 700px) {
            .ag-checklist { grid-template-columns: auto minmax(0, 1fr) auto; }
            .ag-check-line { grid-column: 2 / 3; grid-row: 2; margin-top: 0.3rem; }
            .ag-checklist svg { grid-row: 1; grid-column: 3; }
        }
        .ag-list-more { margin-top: 2rem; }

        /* Elsewhere: four small works by the door. */
        .ag-else { display: grid; grid-template-columns: minmax(0, 1fr); border-top: 1px solid var(--ag-ink); }
        @media (min-width: 700px) { .ag-else { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: clamp(2rem, 5vw, 5rem); } }
        .ag-else a { display: grid; grid-template-columns: 3.25rem minmax(0, 1fr) auto; align-items: center; gap: 1.25rem; padding-block: 1.25rem; border-bottom: 1px solid var(--ag-line); }
        .ag-else i { display: block; aspect-ratio: 1; background: var(--g); box-shadow: 0 0.5rem 0.6rem -0.45rem var(--ag-cast); transition: scale 0.35s ease; }
        .dark .ag-else i { outline: 1px solid var(--ag-hair); }
        .ag-else a:hover i { scale: 1.08; }
        .ag-else small { display: block; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ag-label); }
        .ag-else strong { display: block; font-family: var(--ag-serif); font-style: italic; font-weight: 400; font-size: 1.3rem; line-height: 1.25; }
        .ag-else svg { width: 1.05rem; height: 1.05rem; transition: translate 0.3s ease; }
        .ag-else a:hover svg { translate: 0.3rem 0; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. Their
           words and prices stay; they are re-hung in this room's manner.
           --------------------------------------------------------------- */
        #ag .ag-prices > section { background: var(--ag-wall-2); }
        #ag .ag-prices h2 { font-weight: 400; font-size: clamp(1.9rem, 3.4vw, 2.8rem); line-height: 1.1; letter-spacing: -0.012em; color: var(--ag-ink); }
        #ag .ag-prices h2 + p { color: var(--ag-ink-2); font-size: 1.0625rem; }
        #ag .ag-prices .grid > div { background: var(--ag-card); border: 0; border-radius: 0; box-shadow: 0 0 0 1px var(--ag-hair), 0 1px 1px rgba(17, 17, 17, 0.08), 0 0.9rem 1.2rem -1rem rgba(17, 17, 17, 0.4); }
        #ag .ag-prices .grid > div:nth-child(2) { box-shadow: 0 0 0 1px var(--ag-ink), 0 0.9rem 1.2rem -1rem rgba(17, 17, 17, 0.4); }
        #ag .ag-prices .grid > div span,
        #ag .ag-prices .grid > div p,
        #ag .ag-prices .grid > div li { color: var(--ag-ink-2); }
        #ag .ag-prices .grid > div .text-3xl { font-weight: 400; font-size: 2.5rem; letter-spacing: -0.02em; color: var(--ag-ink); }
        #ag .ag-prices .grid > div .uppercase { font-weight: 400; letter-spacing: 0.2em; font-size: 0.72rem; color: var(--ag-ink); }
        #ag .ag-prices .grid > div .rounded-full { background: transparent; color: var(--ag-ink); border: 1px solid var(--ag-line); border-radius: 0; font-weight: 400; letter-spacing: 0.14em; }
        #ag .ag-prices .grid > div svg { color: var(--ag-ink); }
        #ag .ag-prices a.font-medium { color: var(--ag-ink); font-weight: 400; text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.4em; }
        #ag .ag-prices a.rounded-2xl { background: var(--ag-ink); color: var(--ag-wall); border-radius: 0; box-shadow: none; font-weight: 400; font-size: 0.76rem; letter-spacing: 0.18em; text-transform: uppercase; }
        #ag .ag-prices a.rounded-2xl:hover { transform: none; box-shadow: none; }

        #ag .ag-more > section { background: var(--ag-wall); border-top: 1px solid var(--ag-hair); }
        #ag .ag-more h2 { font-weight: 400; font-size: clamp(1.7rem, 3vw, 2.4rem); letter-spacing: -0.012em; color: var(--ag-ink); }
        #ag .ag-more p.uppercase { font-weight: 400; font-size: 0.72rem; letter-spacing: 0.2em; color: var(--ag-label); }
        #ag .ag-more .grid > a { background: var(--ag-card); border: 0; border-radius: 0; box-shadow: 0 0 0 1px var(--ag-hair), 0 1px 1px rgba(17, 17, 17, 0.08); }
        #ag .ag-more .grid > a:hover { box-shadow: 0 0 0 1px var(--ag-ink), 0 0.9rem 1.2rem -1rem rgba(17, 17, 17, 0.4); }
        #ag .ag-more .grid > a > span:first-child { display: none; }
        #ag .ag-more .grid > a h3 { font-family: var(--ag-serif); font-style: italic; font-weight: 400; font-size: 1.15rem; color: var(--ag-ink); }
        #ag .ag-more .grid > a p { color: var(--ag-ink-2); }
        #ag .ag-more .grid > a > span:last-child,
        #ag .ag-more a.self-start { color: var(--ag-ink); font-weight: 400; }

        /* ---------------------------------------------------------------
           Questions
           --------------------------------------------------------------- */
        .ag-qa { counter-reset: ag-q; border-top: 1px solid var(--ag-ink); }
        .ag-qa details { counter-increment: ag-q; border-bottom: 1px solid var(--ag-line); }
        .ag-qa summary { display: grid; grid-template-columns: 2.5rem minmax(0, 1fr) 1.1rem; align-items: baseline; gap: 0.75rem; padding-block: 1.4rem; cursor: pointer; }
        .ag-qa summary::before { content: counter(ag-q, decimal-leading-zero); font-size: 0.72rem; letter-spacing: 0.1em; color: var(--ag-label); font-variant-numeric: tabular-nums; }
        .ag-qa h3 { font-size: 1.2rem; font-weight: 400; line-height: 1.35; }
        .ag-qa summary i { position: relative; align-self: center; width: 1.1rem; height: 1.1rem; }
        .ag-qa summary i::before,
        .ag-qa summary i::after { content: ""; position: absolute; left: 0; right: 0; top: 50%; height: 1px; background: currentColor; transition: rotate 0.35s cubic-bezier(0.22, 1, 0.36, 1); }
        .ag-qa summary i::after { rotate: 90deg; }
        .ag-qa details[open] summary i::after { rotate: 0deg; }
        .ag-qa details p { padding: 0 0 1.7rem 3.25rem; max-width: 45rem; color: var(--ag-ink-2); }
        @media (max-width: 560px) { .ag-qa details p { padding-inline-start: 0; } }

        /* ---------------------------------------------------------------
           The last room: a long horizon, and the invitation card
           --------------------------------------------------------------- */
        .ag-finale { padding-bottom: 0; }
        .ag-hang-pano { width: 100%; }
        .ag-hang-pano::before { inset: -34% -5% -24% -5%; }
        .ag-invite {
            position: relative;
            z-index: 2;
            width: min(100%, 39rem);
            margin: clamp(-9rem, -11vw, -3rem) auto clamp(4rem, 8vw, 7rem);
            padding: clamp(2rem, 5vw, 3.75rem);
            background: var(--ag-card);
            box-shadow: 0 0 0 1px var(--ag-hair), 0 2px 3px rgba(17, 17, 17, 0.14), 0 3rem 4rem -2.4rem var(--ag-cast);
            text-align: center;
        }
        .ag-invite::before { content: ""; position: absolute; inset: 0.7rem; border: 1px solid var(--ag-line); pointer-events: none; }
        .ag-invite .ag-h2 { margin-top: 1.4rem; font-size: clamp(1.9rem, 3.4vw, 2.75rem); }
        .ag-invite .ag-p { margin-inline: auto; font-size: 1.0625rem; }
        .ag-invite-form { position: relative; display: grid; gap: 1.5rem; margin-top: 2.4rem; text-align: start; }
        .ag-invite-form label { display: block; margin-bottom: -1rem; }
        #ag .ag-claim { display: flex; align-items: center; min-width: 0; padding-block: 1rem; border-bottom: 1px solid var(--ag-ink); font-size: clamp(1.05rem, 3.6vw, 1.35rem); transition: box-shadow 0.25s ease; }
        #ag .ag-claim:focus-within { border-color: var(--ag-ink); box-shadow: 0 2px 0 var(--ag-ink); }
        #ag .ag-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: var(--ag-ink); box-shadow: none; outline: none; }
        #ag .ag-claim input::placeholder { color: var(--ag-label); opacity: 1; }
        .ag-claim span { flex: none; color: var(--ag-label); user-select: none; }
        .ag-invite-note { position: relative; margin-top: 1.4rem; font-size: 0.875rem; color: var(--ag-label); }

        @media (prefers-reduced-motion: reduce) {
            .ag-hang::before,
            .ag-breathe i { animation: none; }
            .ag-btn, .ag-link, .ag-piece, .ag-checklist a, .ag-checklist svg, .ag-else i, .ag-else svg { transition: none; }
        }
    </style>

    @php
        // The run. Every count on the page is derived from these three
        // constants, so the copy and the bar cannot drift apart: the span
        // is 40 days, 30 of them open, with 4 evenings that all fall on
        // open days (asserted before this page was written).
        $runStart = new DateTimeImmutable('2026-01-28');   // Wed
        $runEnd = new DateTimeImmutable('2026-03-08');     // Sun
        $openWeekdays = [3, 4, 5, 6, 7];                   // Wed..Sun (ISO-8601)

        $evenings = [
            '2026-01-29' => ['Private view', '6-9pm', 'Free, 80 places'],
            '2026-02-07' => ['Artist talk', '3pm', 'Free, 40 places'],
            '2026-02-22' => ['Curator tour', '2pm', 'Free, 25 places'],
            '2026-03-08' => ['Closing party', '5-8pm', 'Free, no limit'],
        ];

        $runDays = [];
        for ($d = $runStart; $d <= $runEnd; $d = $d->modify('+1 day')) {
            $key = $d->format('Y-m-d');
            $isOpen = in_array((int) $d->format('N'), $openWeekdays, true);
            $runDays[] = [
                'date' => $d,
                'open' => $isOpen,
                'evening' => $isOpen && isset($evenings[$key]) ? $evenings[$key][0] : null,
            ];
        }

        $openCount = count(array_filter($runDays, fn ($d) => $d['open']));
        $eveningCount = count(array_filter($runDays, fn ($d) => $d['evening'] !== null));
        $closedCount = count($runDays) - $openCount;

        $numbers = [30 => 'Thirty', 4 => 'four'];
        $openWord = $numbers[$openCount] ?? $openCount;
        $eveningWord = $numbers[$eveningCount] ?? $eveningCount;

        $faqs = [
            [
                'q' => 'Do I have to create an event for every day of an exhibition?',
                'a' => 'No. The run is one recurring event: pick the days you open, and set it to end on the closing date. A six-week show open Wednesday to Sunday is a single entry that appears on all thirty open days, not thirty things to keep up to date. If you close for a holiday, a date exception takes that day out, and the same mechanism can add a one-off opening that falls outside your usual days.',
            ],
            [
                'q' => 'How do the private view and the artist talk fit in?',
                'a' => 'As their own events, on top of the run. A recurring event carries one start time, so anything happening at a different hour needs to be separate anyway - which is exactly right here, because each evening wants its own description, its own capacity and its own page to share. Most shows end up with three or four.',
            ],
            [
                'q' => 'Is Event Schedule free for a gallery?',
                'a' => 'The parts you use for every show are free forever: the run as a recurring event, date exceptions, separate evening events, free registration with a capacity for a private view, sub-schedules, exhibition proposals from artists, two-way calendar sync, a live calendar feed and an embeddable calendar. Charging for a collector dinner or a paid preview is what needs Pro, at '.plan_price($proMonthly).' a month. Zero platform fees on sales either way.',
            ],
            [
                'q' => 'Can I cap the private view without charging for it?',
                'a' => 'Yes. Set the event as free and give it a capacity. People register rather than pay, the count is kept for each date separately, and it stops taking names when the places are gone. Nothing about a private view has to involve money for you to know how many are coming.',
            ],
            [
                'q' => 'How do artists propose a show?',
                'a' => 'Through your page rather than your inbox. The default submission form takes a pasted proposal or an uploaded flyer and reads it into a request for you to review. That is free, and it reads up to 50 a day on the free plan and Pro alike (10 while a schedule is on a trial, 100 on Enterprise), from the same daily allowance as any events you import that way yourself. If you would rather have fixed fields, switch the setting to the booking form and every proposal arrives with a date, a time and a description. On Pro you can add custom fields to either form, so nothing reaches you without a portfolio link.',
            ],
            [
                'q' => 'Will my collectors be told when a new show goes up?',
                'a' => 'Anyone who leaves an email address on your gallery page and confirms it is sent a short digest when you put a new show up, at most one every few days, and it does not touch your newsletter allowance. Collectors who would rather not give an address can subscribe to the gallery\'s live calendar feed instead, and each new show appears in their own calendar. Anything with more in it than the dates is a newsletter you write: the free plan covers 10 emails a month and Pro raises it to 100, counted per recipient rather than per send, so one message to a hundred collectors uses a hundred of them.',
            ],
            [
                'q' => 'What if an exhibiting artist is not on Event Schedule?',
                'a' => 'Add them by name anyway. The show\'s page lists every artist, and one without an account gets a page of their own that shows the dates you added, says your gallery listed them and that they have not claimed it yet, and stays out of search engines until they do. Add their email address and they can claim the page by signing in with it; the dates you listed stay on it once it is theirs.',
            ],
            [
                'q' => 'How do collectors pay for a ticketed dinner?',
                'a' => 'Through your own Stripe or PayPal account, an Invoice Ninja invoice, a payment link or cash, chosen per event, and Event Schedule takes no platform fee on any of them. If a guest cannot come, refund them from the Sales page: a Stripe or PayPal sale goes back through the provider, in full or in part, and any other method is marked as refunded, which records it without moving money. Putting a price on the ticket is Pro, and the payment methods and refunds come with it; a private view with free registration and a capacity is not.',
            ],
        ];

        $dotSections = [
            ['top', 'The run'],
            ['run', 'One event'],
            ['evenings', 'The evenings'],
            ['view', 'The private view'],
            ['collectors', 'Who hears first'],
            ['proposals', 'Proposals'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    @php
        // Icons, and the things the new hang needs that are not copy: which generated work
        // hangs beside each of the six kinds of gallery, and the colour chart's chips.
        $agArrow = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m-6-6 6 6-6 6" /></svg>';
        $agDown = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m-6-6 6 6 6-6" /></svg>';
        $agChart = ['#e0432a', '#f4cf3d', '#1f6f78', '#f2efe6', '#111111', '#1648a8', '#e9a72c', '#17834a', '#b5532a', '#8fc3c9', '#d9d6cf', '#3c0d0c'];
        $agWalk = [
            ['Contemporary Galleries', 'Six-week hangs with a private view at the front and a closing at the back, and a programme booked a year out.', 'for-contemporary-galleries', 'arc', 'One radial gradient'],
            ['Photography Galleries', 'Editions on the wall and a talk in the middle of the run, with the capacity for each evening kept separately.', 'for-photography-galleries', 'photo', 'A linear gradient, matted'],
            ['Craft & Artisan Galleries', 'A shop floor that is open most days and a maker demonstration once a fortnight, on one calendar.', 'for-craft-galleries', 'weave', 'Two repeating gradients, crossed'],
            ['Pop-Up Galleries', 'Three weeks in a borrowed unit. A run with a fixed closing date is exactly the shape of the lease.', 'for-pop-up-galleries', 'target', 'Six hard stops'],
            ['Artist Cooperatives', 'Members take the wall in turn, and each can be added to their own show, which offers the dates to their schedule to accept.', 'for-artist-cooperatives', 'chart', 'Thirty-six chips on a grid'],
            ['Museum Galleries', 'A long run with a tour on it every other Sunday, and a curator talk that fills months before the show comes down.', 'for-museum-galleries', 'gilt', 'A border image and a glaze'],
        ];
    @endphp

    <div id="ag">

        <nav class="ag-nav es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as $sectionIndex => [$sectionId, $sectionLabel])
                    <li>
                        <a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}">
                            <span aria-hidden="true">{{ str_pad($sectionIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="ag-nav-name" aria-hidden="true">{{ $sectionLabel }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Room one: the title on the wall, one work, the run        -->
        <!-- ============================================================ -->
        <section id="top" class="ag-hero" style="scroll-margin-top: 5rem;">
            <div class="ag-wrap ag-hero-grid">
                <div>
                    <h1 class="ag-h1">
                        <x-marketing.hero-eyebrow class="ag-eyebrow es-fade-up es-d-1">Art gallery calendar, for project spaces too</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">The show runs.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="ag-it">evenings</span> do not.</span></span>
                    </h1>

                    <p class="ag-lede es-fade-up es-d-2">
                        A six-week hang is on the wall every day you open. Only a handful of nights
                        are things anyone attends. So the run is one entry that stops itself on the
                        closing date, and the evenings are the {{ $eveningWord }} you add on top.
                    </p>

                    <div class="ag-cta es-fade-up es-d-3">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="ag-btn">
                            Put the show up
                            {!! $agArrow !!}
                        </a>
                        <a href="#run" class="ag-link">
                            See how the run works
                            {!! $agDown !!}
                        </a>
                    </div>
                </div>

                <!-- The work, and beside it the label that carries the run. The shape is the argument. -->
                <div class="ag-hero-wall" data-reveal style="--reveal-delay: 0.25s;">
                    <figure class="ag-hang ag-hang-hero" aria-hidden="true">
                        <span class="ag-canvas ag-w-field ag-breathe ag-reflect"><i></i><i></i><i></i></span>
                    </figure>

                    <div class="ag-label ag-label-hero">
                        <p class="ag-cap">Now showing</p>
                        <p class="ag-label-title">Sarah Chen: New Works</p>
                        <p class="ag-label-meta">
                            {{ $runStart->format('D j M') }} to {{ $runEnd->format('D j M') }} &middot; open Wed to Sun
                        </p>

                        <div class="ag-run" role="img"
                            aria-label="{{ count($runDays) }} days of the run: {{ $openCount }} open days, {{ $closedCount }} closed, with {{ $eveningCount }} evening events marked.">
                            @foreach ($runDays as $day)
                                <span @class([
                                    'ag-day',
                                    'ag-day-eve' => $day['evening'] !== null,
                                    'ag-day-open' => $day['open'] && $day['evening'] === null,
                                    'ag-day-shut' => ! $day['open'],
                                ])></span>
                            @endforeach
                        </div>

                        <dl class="ag-run-stats">
                            <div>
                                <dd>{{ $openCount }}</dd>
                                <dt>Open days</dt>
                            </div>
                            <div>
                                <dd>1</dd>
                                <dt>Recurring event</dt>
                            </div>
                            <div>
                                <dd>{{ $eveningCount }}</dd>
                                <dt>Evenings</dt>
                            </div>
                        </dl>

                        <p class="ag-label-note">
                            {{ $openWord }} open days. {{ $eveningCount }} entries you make by hand.
                        </p>
                    </div>
                </div>
            </div>
            <div class="ag-floor" aria-hidden="true"></div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The run sets itself (01)                                  -->
        <!-- ============================================================ -->
        <section id="run" class="ag-room" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap ag-pair">
                <div class="ag-text">
                    <p class="ag-kicker" data-reveal><span aria-hidden="true">01</span> The run</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                        One entry, <span class="ag-it">not thirty</span>.
                    </h2>
                    <p class="ag-p" data-reveal style="--reveal-delay: 0.12s;">
                        Pick the days you open and the date the show comes down. The exhibition then
                        appears on every open day between the two without you touching it again, and
                        it stops on its own.
                    </p>

                    <ul class="ag-points" data-reveal-group="90">
                        @foreach ([
                            ['Set the days you open', 'Wednesday to Sunday is one choice, not five. Mondays and Tuesdays never appear.'],
                            ['Set the closing date', 'The run ends on the day it ends. Nothing lingers on the calendar after the work comes off the wall.'],
                            ['Exceptions cut both ways', 'Take out the day you shut for an install, or add a bank-holiday Monday opening, without disturbing the pattern.'],
                        ] as [$t, $d])
                            <li data-reveal><b>{{ $t }}</b> <span>- {{ $d }}</span></li>
                        @endforeach
                    </ul>

                    <p class="ag-tierline" data-reveal>
                        <span class="ag-tier">Free plan</span>
                        <span>Recurring events, closing dates and date exceptions are all on the free plan.</span>
                    </p>
                </div>

                <div class="ag-side" data-reveal>
                    <figure class="ag-hang" aria-hidden="true">
                        <span class="ag-canvas ag-w-grid"><i></i><i></i></span>
                        <figcaption class="ag-made">02 &middot; Thirty columns, one band</figcaption>
                    </figure>

                    <div class="ag-label">
                        <div class="ag-label-head">
                            <h3 class="ag-label-title">What you fill in, once</h3>
                            <span class="ag-label-meta">1 event</span>
                        </div>

                        <dl class="ag-rows">
                            @foreach ([
                                ['Exhibition', 'Sarah Chen: New Works'],
                                ['Repeats', 'Wed, Thu, Fri, Sat, Sun'],
                                ['First day', $runStart->format('D j M Y')],
                                ['Ends', 'On ' . $runEnd->format('D j M Y')],
                                ['Exceptions', '1 day out, 1 day added'],
                            ] as [$fLabel, $fValue])
                                <div>
                                    <dt>{{ $fLabel }}</dt>
                                    <dd>{{ $fValue }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <p class="ag-label-note">
                            That is the whole run. It lands on {{ $openCount }} dates and stops.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The evenings (02)                                         -->
        <!-- ============================================================ -->
        <section id="evenings" class="ag-room ag-room-2" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap">
                <div class="ag-head">
                    <p class="ag-kicker" data-reveal><span aria-hidden="true">02</span> The evenings</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                        The {{ $eveningCount }} nights <span class="ag-it">people put in the diary</span>.
                    </h2>
                    <p class="ag-p" data-reveal style="--reveal-delay: 0.12s;">
                        A recurring event carries one start time, so anything at a different hour is its
                        own entry anyway. That is the right answer here: each of these wants its own
                        description, its own capacity and its own link to send.
                    </p>
                </div>

                <div class="ag-row ag-row-4 ag-dim" data-reveal-group="90">
                    @foreach ($evenings as $eDate => [$eName, $eTime, $eNote])
                        @php $eObj = new DateTimeImmutable($eDate); @endphp
                        <div class="ag-piece" data-reveal>
                            <figure class="ag-hang" aria-hidden="true">
                                <span class="ag-canvas ag-tondo ag-w-eve-{{ $loop->iteration }}"></span>
                            </figure>
                            <div class="ag-label ag-label-bare">
                                <p class="ag-cap">{{ $eObj->format('D j M') }}</p>
                                <h3 class="ag-label-title">{{ $eName }}</h3>
                                <p class="ag-label-meta">{{ $eTime }}</p>
                                <p class="ag-label-note">{{ $eNote }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="ag-tierline" data-reveal>
                    <span class="ag-tier">Free plan</span>
                    <span>
                        Every one of them, with no cap on how many events you put up.
                    </span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The private view (03): a diptych                          -->
        <!-- ============================================================ -->
        <section id="view" class="ag-room" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap ag-pair ag-pair-wide">
                <div class="ag-text">
                    <p class="ag-kicker" data-reveal><span aria-hidden="true">03</span> The private view</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Limited does not have to mean <span class="ag-it">paid</span>.
                    </h2>
                    <p class="ag-p" data-reveal style="--reveal-delay: 0.12s;">
                        Nobody charges for an opening. You still need to know whether eighty people are
                        coming or three hundred, and that is a capacity, not a ticket.
                    </p>
                </div>

                <div class="ag-diptych ag-dim">
                    <div class="ag-piece" data-reveal>
                        <figure class="ag-hang" aria-hidden="true">
                            <span class="ag-canvas ag-w-zip-a"><i></i></span>
                            <figcaption class="ag-made">07 &middot; Diptych, left</figcaption>
                        </figure>
                        <div class="ag-label">
                            <div class="ag-label-head">
                                <h3 class="ag-label-title">The opening</h3>
                                <span class="ag-tier">Free plan</span>
                            </div>
                            <p class="ag-label-meta">When you want the count but not the money.</p>
                            <ul>
                                @foreach ([
                                    'People register instead of paying.',
                                    'A capacity counted for each date on its own.',
                                    'It stops taking names when the places are gone.',
                                ] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="ag-piece" data-reveal style="--reveal-delay: 0.12s;">
                        <figure class="ag-hang" aria-hidden="true">
                            <span class="ag-canvas ag-w-zip-b"><i></i></span>
                            <figcaption class="ag-made">08 &middot; Diptych, right</figcaption>
                        </figure>
                        <div class="ag-label">
                            <span class="ag-dot" aria-hidden="true"></span>
                            <div class="ag-label-head">
                                <h3 class="ag-label-title">The collector dinner</h3>
                                <span class="ag-tier">Pro plan</span>
                            </div>
                            <p class="ag-label-meta">The evening that is worth charging for.</p>
                            <ul>
                                @foreach ([
                                    'A price and a quantity, counted per date the same way.',
                                    'QR check-in, so the person on the door is not holding a printout.',
                                    'Paid into your own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash, with no platform fee on top.',
                                    'Announce it before it sells, switch on the "Notify me" card, and collectors can ask to be told when tickets go on sale.',
                                ] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Who hears first (04)                                      -->
        <!-- ============================================================ -->
        <section id="collectors" class="ag-room ag-room-2" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap ag-pair ag-pair-flip">
                <div class="ag-text">
                    <p class="ag-kicker" data-reveal><span aria-hidden="true">04</span> Who hears first</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Nothing goes out <span class="ag-it">without you</span>.
                    </h2>
                    <p class="ag-p" data-reveal style="--reveal-delay: 0.12s;">
                        People leave their email on your gallery page and confirm it, and from then on
                        a new show reaches them without an algorithm deciding who sees it. When the
                        opening is worth more than the dates, you write that one yourself.
                    </p>

                    <ul class="ag-points ag-points-2" data-reveal-group="90">
                        @foreach ([
                            ['A list that is yours', 'Followers arrive from your own page, and you can see who they are on the followers tab.'],
                            ['Written and sent by you', 'A newsletter never sends itself. The announcement goes when you decide the hang is ready to be seen.'],
                            ['Know the unit before you plan', 'The allowance counts each recipient, so one letter to a list of two hundred uses two hundred: more than a month on Pro, a fifth of a month on Enterprise.'],
                            ['Or no address at all', 'Collectors can subscribe to the gallery\'s live calendar feed from the sign-up panel or any event\'s Add to Calendar menu, and every new show and evening appears in their own calendar.'],
                        ] as [$t, $d])
                            <li data-reveal>
                                <b>{{ $t }}</b>
                                <span>{{ $d }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="ag-side" data-reveal>
                    <figure class="ag-hang" aria-hidden="true">
                        <span class="ag-canvas ag-w-stripes"></span>
                        <figcaption class="ag-made">09 &middot; One gradient, fourteen stops</figcaption>
                    </figure>

                    <div class="ag-label">
                        <div class="ag-label-head">
                            <h3 class="ag-label-title">What a send costs you</h3>
                            <span class="ag-label-meta">per month</span>
                        </div>

                        <dl class="ag-rows ag-rows-name">
                            @foreach ([
                                ['Free plan', '10 recipients'],
                                ['Pro plan', '100 recipients'],
                                ['Enterprise', '1,000 recipients'],
                            ] as [$pName, $pAllow])
                                <div>
                                    <dt>{{ $pName }}</dt>
                                    <dd>{{ $pAllow }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <p class="ag-label-note">
                            The allowance counts recipients, not sends. One message to a hundred
                            collectors uses a hundred of them.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Proposals (05): two works, hung salon style               -->
        <!-- ============================================================ -->
        <section id="proposals" class="ag-room" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap">
                <div class="ag-head">
                    <p class="ag-kicker" data-reveal><span aria-hidden="true">05</span> Proposals</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Artists ask through <span class="ag-it">the page</span>.
                    </h2>
                    <p class="ag-p" data-reveal style="--reveal-delay: 0.12s;">
                        Not a submissions address you stop opening. Proposals arrive attached to your
                        schedule, and nothing appears publicly until you accept it.
                    </p>
                </div>

                <div class="ag-salon ag-dim">
                    <div class="ag-piece" data-reveal>
                        <figure class="ag-hang" aria-hidden="true">
                            <span class="ag-canvas ag-w-edge"><i></i><i></i></span>
                            <figcaption class="ag-made">10 &middot; Two clip-paths</figcaption>
                        </figure>
                        <div class="ag-label">
                            <div class="ag-label-head">
                                <h3 class="ag-label-title">Paste it or drop the flyer</h3>
                                <span class="ag-tier">Free plan</span>
                            </div>
                            <p class="ag-label-meta">The form your page starts with.</p>
                            <ul>
                                @foreach ([
                                    'An artist pastes their proposal text or uploads a flyer.',
                                    'It is read into a request for you to review, not published.',
                                    'Fifty read a day on the free plan and Pro alike (ten during a trial), a hundred on Enterprise.',
                                ] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="ag-piece" data-reveal style="--reveal-delay: 0.12s;">
                        <figure class="ag-hang" aria-hidden="true">
                            <span class="ag-canvas ag-w-squares"><i></i><i></i><i></i></span>
                            <figcaption class="ag-made">11 &middot; Three boxes, nested</figcaption>
                        </figure>
                        <div class="ag-label">
                            <div class="ag-label-head">
                                <h3 class="ag-label-title">Or ask for exactly what you need</h3>
                                <span class="ag-tier">Pro plan</span>
                            </div>
                            <p class="ag-label-meta">Switch the form on any plan; your own fields are Pro.</p>
                            <ul>
                                @foreach ([
                                    'The booking form asks for a date, a time and a description.',
                                    'Custom fields sit on either form, so nothing arrives without a portfolio link.',
                                    'Accept, and adding the artist as a participant offers the dates to their own schedule too.',
                                ] as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Who it is for (06): the long wall                         -->
        <!-- ============================================================ -->
        <section id="who" class="ag-room ag-room-2 ag-walk-room" style="scroll-margin-top: 4rem;">
            <div class="ag-walk-pin">
                <div class="ag-walk-stick">
                    <div class="ag-wrap ag-walk-head">
                        <p class="ag-kicker" data-reveal><span aria-hidden="true">06</span> Who it is for</p>
                        <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                            Anywhere a show <span class="ag-it">goes up and comes down</span>.
                        </h2>
                    </div>

                    <div class="ag-walk-view" tabindex="0" role="region" aria-label="Who it is for: six kinds of gallery" data-clip-ok>
                        <div class="ag-walk ag-dim">
                            @foreach ($agWalk as $walkIndex => [$agName, $agDesc, $agSlug, $agWork, $agMade])
                                @php $agPost = get_sub_audience_blog($agSlug); @endphp
                                <article class="ag-piece">
                                    <figure class="ag-hang" aria-hidden="true">
                                        <span class="ag-canvas ag-w-{{ $agWork }}">
                                            @if ($agWork === 'chart')
                                                @for ($chip = 0; $chip < 36; $chip++)
                                                    <i style="--c: {{ $agChart[($chip * 7 + intdiv($chip, 6) * 5) % count($agChart)] }};"></i>
                                                @endfor
                                            @elseif ($agWork === 'photo')
                                                <i></i>
                                            @endif
                                        </span>
                                    </figure>
                                    <div class="ag-label ag-label-bare">
                                        <p class="ag-cap" aria-hidden="true">{{ str_pad($walkIndex + 12, 2, '0', STR_PAD_LEFT) }} &middot; {{ $agMade }}</p>
                                        <h3 class="ag-label-title">{{ $agName }}</h3>
                                        <p class="ag-label-note">{{ $agDesc }}</p>
                                        @if ($agPost)
                                            <a href="{{ blog_url('/' . $agPost->slug) }}" class="ag-link" aria-label="Learn more about Event Schedule for {{ $agName }}">
                                                Learn more
                                                {!! $agArrow !!}
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="ag-wrap">
                        <div class="ag-walk-bar" aria-hidden="true"><i></i></div>
                        <p class="ag-cap ag-walk-hint" aria-hidden="true">Six works &middot; walk the wall</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. How it works (07): the project room                       -->
        <!-- ============================================================ -->
        <section id="how" class="ag-room ag-box" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap">
                <div class="ag-head">
                    <p class="ag-kicker" data-reveal><span aria-hidden="true">07</span> How it works</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Set at the install, <span class="ag-it">left alone after</span>.
                    </h2>
                </div>

                <div class="ag-row ag-row-3" data-reveal-group="110">
                    <div class="ag-centre" aria-hidden="true"><span>145 cm</span></div>
                    @foreach ([
                        ['01', 'Put the run up', 'One recurring event on the days you open, ending on the day the show comes down.'],
                        ['02', 'Add the evenings', 'The private view, the talk, the closing. A capacity on each, and a price only where there is one.'],
                        ['03', 'Send it once', 'Subscribers get a short digest of the new show on their own, and the announcement you write goes when you send it. Artists send the next proposal through the same page.'],
                    ] as [$n, $t, $d])
                        <div class="ag-piece" data-reveal>
                            <figure class="ag-hang" aria-hidden="true">
                                <span class="ag-canvas ag-w-mono-{{ $loop->iteration }}"></span>
                            </figure>
                            <div class="ag-label ag-label-bare">
                                <p class="ag-cap">{{ $n }}</p>
                                <h3 class="ag-label-title">{{ $t }}</h3>
                                <p class="ag-label-note">{{ $d }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Key features: the checklist                               -->
        <!-- ============================================================ -->
        <section class="ag-room">
            <div class="ag-wrap ag-list-grid">
                <div>
                    <p class="ag-kicker" data-reveal>List of works</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">Key features</h2>
                    <p class="ag-list-more" data-reveal style="--reveal-delay: 0.12s;">
                        <a href="{{ marketing_url('/features') }}" class="ag-link">
                            See all features
                            {!! $agArrow !!}
                        </a>
                    </p>
                </div>

                <ol class="ag-checklist" data-reveal>
                    @foreach ([
                        ['Recurring Events', 'A whole run as one entry, ending on the closing date', marketing_url('/features/recurring-events')],
                        ['Sub-schedules', 'Exhibitions, talks and hire on their own strands and links', marketing_url('/features/sub-schedules')],
                        ['Ticketing', 'For the collector dinner: a price, QR check-in and zero platform fees, on the Pro plan', marketing_url('/features/ticketing')],
                        ['Custom Fields', 'Ask every proposal for a portfolio link before it reaches you', marketing_url('/features/custom-fields')],
                        ['Embed Calendar', 'Put the programme on the gallery site you already have', marketing_url('/features/embed-calendar')],
                    ] as [$agFeature, $agFeatureLine, $agFeatureUrl])
                        <li>
                            <a href="{{ $agFeatureUrl }}">
                                <span class="ag-check-no" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="ag-check-title">{{ $agFeature }}</span>
                                <span class="ag-check-line">{{ $agFeatureLine }}</span>
                                {!! $agArrow !!}
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <div class="ag-prices">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 10. Related audience pages: by the door                      -->
        <!-- ============================================================ -->
        <section class="ag-room">
            <div class="ag-wrap ag-list-grid">
                <div>
                    <p class="ag-kicker" data-reveal>Also showing</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">Related pages</h2>
                    <p class="ag-list-more" data-reveal style="--reveal-delay: 0.12s;">
                        <a href="{{ marketing_url('/use-cases') }}" class="ag-link">
                            See all use cases
                            {!! $agArrow !!}
                        </a>
                    </p>
                </div>

                <div class="ag-else" data-reveal>
                    @foreach ([
                        ['/for-visual-artists', 'Visual Artists', 'linear-gradient(#c8341f, #c8341f) 0 0 / 100% 58% no-repeat, #ead9b8'],
                        ['/for-libraries', 'Libraries', 'repeating-linear-gradient(90deg, #1f4d3a 0 14%, #7a2c28 14% 24%, #d8b25a 24% 30%, #20324f 30% 46%)'],
                        ['/for-community-centers', 'Community Centers', 'radial-gradient(circle at 30% 34%, #f4cf3d 0 24%, transparent 24.6%), radial-gradient(circle at 70% 68%, #1f6f78 0 26%, transparent 26.6%), #f2efe6'],
                        ['/for-museums', 'Museums', 'linear-gradient(#f2efe6, #f2efe6) 50% 50% / 46% 46% no-repeat, #1a1a1a'],
                    ] as [$relHref, $relName, $relArt])
                        <a href="{{ marketing_url($relHref) }}">
                            <i aria-hidden="true" style="--g: {{ $relArt }};"></i>
                            <span>
                                <small>Event Schedule for</small>
                                <strong>{{ $relName }}</strong>
                            </span>
                            {!! $agArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. FAQ (08)                                                 -->
        <!-- ============================================================ -->
        <section id="faq" class="ag-room ag-room-2" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap ag-list-grid">
                <div>
                    <p class="ag-kicker" data-reveal><span aria-hidden="true">08</span> Questions</p>
                    <h2 class="ag-h2" data-reveal style="--reveal-delay: 0.06s;">
                        Asked at <span class="ag-it">the install</span>.
                    </h2>
                </div>

                <div class="ag-qa" data-reveal>
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
        <!-- 12. The last room: the invitation                            -->
        <!-- ============================================================ -->
        <section id="claim" class="ag-room ag-finale" style="scroll-margin-top: 4rem;">
            <div class="ag-wrap">
                <figure class="ag-hang ag-hang-pano" aria-hidden="true">
                    <span class="ag-canvas ag-w-pano ag-breathe"><i></i><i></i></span>
                </figure>

                <div class="ag-invite" data-reveal="panel">
                    <p class="ag-cap">Free forever</p>
                    <h2 class="ag-h2">
                        Put the show up <span class="ag-it">and leave it there</span>.
                    </h2>
                    <p class="ag-p">
                        The run, the evenings, the proposals and a private view with a capacity
                        all cost nothing. A ticketed dinner is the part that needs Pro.
                    </p>

                    <div class="ag-invite-form">
                        <label for="es-claim-input" class="ag-cap">Your schedule name</label>
                        <div dir="ltr" class="es-claim ag-claim">
                            <input id="es-claim-input" type="text" placeholder="your-gallery" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="ag-btn">
                            Put the show up
                            {!! $agArrow !!}
                        </a>
                    </div>

                    <p class="ag-invite-note">No credit card required</p>
                </div>
            </div>
            <div class="ag-floor" aria-hidden="true"></div>
        </section>

        <div class="ag-more">
            <x-marketing.related-pages />
        </div>

    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
