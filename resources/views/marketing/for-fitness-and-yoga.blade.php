<x-marketing-layout>
    <x-slot name="title">Free Yoga & Fitness Class Schedule, Drop-ins and Passes</x-slot>
    <x-slot name="description">Set each yoga or fitness class up once as a recurring event, sell drop-ins through Stripe or PayPal with zero platform fees, and add class passes on Pro.</x-slot>
    <x-slot name="breadcrumbTitle">For Fitness & Yoga</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typeface, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Outfit') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Fitness & Yoga"
        description="Publish a weekly class timetable once as recurring classes, then sell visits off a pass instead of seats at a single night. Zero platform fees."
        audience="Fitness & Yoga Instructors"
        keywords="fitness class schedule, yoga class calendar, class pass, studio timetable, fitness studio scheduling, free fitness scheduling" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a studio timetable online with Event Schedule",
        "description": "Set the week once, then sell visits rather than single nights.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the week",
                "text": "Create each class once as a recurring event: the days of the week it runs, one start time, a length, and date exceptions for the days you are closed."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Price the visit",
                "text": "Add a drop-in ticket and a pass. Choose how the pass is spent, how long it lasts, what it covers, and how many people it admits per class."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Share one link",
                "text": "Put your schedule link in your bio, embed the timetable on your site, and print the follower QR code for the studio door."
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
           For-fitness-and-yoga "Breathe In" styles. A studio's own site,
           organised by one idea: the day. Classes run from first light to
           the last mat rolled up, and so does the page.

           THE SKY. With no script and under reduced motion the ground is
           one tall gradient from dawn to dusk, so the light already
           changes as you read down. Where scroll timelines exist, a
           viewport-fixed sky takes over: its two colours and its sun
           position are registered custom properties run by the page's
           scroll, so the sun rises behind the hero, crosses at midday and
           sets by the finale (a dim moon after dark). The fixed layer is
           held inside a clip-path box the size of the page, which is what
           keeps it off the site footer.

           THE BREATH. One orb in the hero: in for four, hold for two, out
           for six. The words change with it, and the same twelve seconds
           pace the button's glow and the places-left chips.

           TEXT. Paragraphs sit on opaque panels. Only headings and
           short lines in the page's darkest ink sit on the sky, and that
           ink is measured against every colour the sky passes through.

           Everything is scoped under #fy. The shared es-* reveal system
           (marketing.css, marketing-home.js) still drives the entrances.
           ============================================================== */

        @property --fy-sky-a { syntax: '<color>'; inherits: false; initial-value: #dfe1d8; }
        @property --fy-sky-b { syntax: '<color>'; inherits: false; initial-value: #f7e6cf; }
        @property --fy-sun-c { syntax: '<color>'; inherits: false; initial-value: #fff0cf; }
        @property --fy-sun-g { syntax: '<color>'; inherits: false; initial-value: rgba(255, 214, 160, 0.7); }
        @property --fy-sun-x { syntax: '<percentage>'; inherits: false; initial-value: 16%; }
        @property --fy-sun-y { syntax: '<percentage>'; inherits: false; initial-value: 92%; }

        #fy {
            --fy-sand: #f3ede3;
            --fy-panel: #fbf8f2;
            --fy-ink: #20382f;
            --fy-ink-2: #3d5248;
            --fy-ink-3: #56685e;
            --fy-sage: #9fb49a;
            --fy-sage-soft: #dfe6d8;
            --fy-sage-ink: #47624d;
            --fy-clay: #c9714d;
            --fy-clay-ink: #944423;
            --fy-clay-soft: #f1d9c8;
            --fy-band: #d3dccb;
            --fy-line: rgba(32, 56, 47, 0.13);
            --fy-line-2: rgba(32, 56, 47, 0.3);
            --fy-shadow: 0 1.75rem 3rem -2rem rgba(32, 56, 47, 0.38);
            --fy-tod: #fbf8f2;
            --fy-tod-mix: 22%;
            --fy-sans: 'Outfit', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            --fy-serif: Georgia, 'Iowan Old Style', 'Palatino Linotype', 'Times New Roman', serif;
            --fy-mono: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
            --fy-day: linear-gradient(90deg, #f3d6b4 0%, #f3ead6 14%, #eef2e8 30%, #f7f6ee 47%, #f5ecd6 64%, #f1cfa6 82%, #d9b79a 100%);
            position: relative;
            isolation: isolate;
            color: var(--fy-ink);
            font-family: var(--fy-sans);
            font-size: 1.0625rem;
            line-height: 1.65;
            background-color: #f3ede3;
            background-image: linear-gradient(180deg, #dfe1d8 0%, #f6e8d2 6%, #f0efe4 20%, #f3f3eb 44%, #f5ecda 66%, #f2d9bd 86%, #ebc7a4 100%);
        }
        .dark #fy {
            --fy-sand: #121a17;
            --fy-panel: #18221e;
            --fy-ink: #ece6d9;
            --fy-ink-2: #c2cdbd;
            --fy-ink-3: #9bab9c;
            --fy-sage: #7f977c;
            --fy-sage-soft: #24322b;
            --fy-sage-ink: #a9c0a4;
            --fy-clay: #e08a63;
            --fy-clay-ink: #efa27e;
            --fy-clay-soft: #3a2a22;
            --fy-band: #1c2823;
            --fy-line: rgba(236, 230, 217, 0.13);
            --fy-line-2: rgba(236, 230, 217, 0.32);
            --fy-shadow: 0 1.75rem 3rem -2rem rgba(0, 0, 0, 0.75);
            --fy-tod: #18221e;
            --fy-tod-mix: 45%;
            --fy-day: linear-gradient(90deg, #3a2b22 0%, #2b2b24 14%, #1f2b26 30%, #24302b 47%, #2a2a22 64%, #3a2820 82%, #171c1b 100%);
            background-color: #121a17;
            background-image: linear-gradient(180deg, #131b22 0%, #1c2522 7%, #131c18 22%, #111916 45%, #161a15 66%, #1c1813 86%, #0b100f 100%);
        }

        /* The bar above takes the first light, so the page reads as one sky. */
        body > header.sticky {
            background-color: rgba(240, 235, 225, 0.84);
            border-bottom-color: rgba(32, 56, 47, 0.12);
        }
        .dark body > header.sticky {
            background-color: rgba(18, 26, 23, 0.84);
            border-bottom-color: rgba(236, 230, 217, 0.12);
        }

        #fy ::selection { background: var(--fy-sage); color: #14231d; }
        #fy a:focus-visible,
        #fy summary:focus-visible,
        #fy input:focus-visible {
            outline: 2px solid var(--fy-clay-ink);
            outline-offset: 4px;
            border-radius: 0.5rem;
        }

        /* ---------------------------------------------------------------
           The sky
           --------------------------------------------------------------- */
        .fy-skybox { display: none; position: absolute; inset: 0; z-index: -1; clip-path: inset(0); pointer-events: none; }
        .fy-sky {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at var(--fy-sun-x) var(--fy-sun-y), var(--fy-sun-c) 0, var(--fy-sun-c) 3.2rem, var(--fy-sun-g) 3.8rem, transparent 26rem),
                linear-gradient(180deg, var(--fy-sky-a), var(--fy-sky-b));
        }
        @supports (animation-timeline: scroll()) {
            html.es-anim #fy .fy-skybox { display: block; }
            html.es-anim #fy .fy-sky { animation: fy-day linear both; animation-timeline: scroll(root block); }
            html.es-anim.dark #fy .fy-sky { animation-name: fy-night; }
            html.es-anim #fy .fy-hero-sun { display: none; }
        }
        @keyframes fy-day {
            0%   { --fy-sky-a: #dfe1d8; --fy-sky-b: #f7e6cf; --fy-sun-x: 16%; --fy-sun-y: 92%;  --fy-sun-c: #fff0cf; --fy-sun-g: rgba(255, 214, 160, 0.7); }
            12%  { --fy-sky-a: #e7ebe2; --fy-sky-b: #f8eedb; --fy-sun-x: 22%; --fy-sun-y: 58%;  --fy-sun-c: #fff6de; --fy-sun-g: rgba(255, 228, 180, 0.6); }
            32%  { --fy-sky-a: #ecf1ea; --fy-sky-b: #f6f3e8; --fy-sun-x: 38%; --fy-sun-y: 24%;  --fy-sun-c: #fffdf3; --fy-sun-g: rgba(255, 244, 214, 0.55); }
            50%  { --fy-sky-a: #eef3ee; --fy-sky-b: #f5f4ec; --fy-sun-x: 52%; --fy-sun-y: 12%;  --fy-sun-c: #ffffff; --fy-sun-g: rgba(255, 250, 230, 0.5); }
            70%  { --fy-sky-a: #efeee2; --fy-sky-b: #f7ead6; --fy-sun-x: 70%; --fy-sun-y: 30%;  --fy-sun-c: #fff4d9; --fy-sun-g: rgba(255, 226, 176, 0.6); }
            88%  { --fy-sky-a: #e8dbc7; --fy-sky-b: #f3d2ad; --fy-sun-x: 85%; --fy-sun-y: 76%;  --fy-sun-c: #f8c092; --fy-sun-g: rgba(244, 170, 120, 0.55); }
            100% { --fy-sky-a: #d6cbbf; --fy-sky-b: #e8bb95; --fy-sun-x: 90%; --fy-sun-y: 108%; --fy-sun-c: #ee9f73; --fy-sun-g: rgba(232, 140, 96, 0.5); }
        }
        @keyframes fy-night {
            0%   { --fy-sky-a: #131b22; --fy-sky-b: #1f2824; --fy-sun-x: 16%; --fy-sun-y: 92%;  --fy-sun-c: #2e3d39; --fy-sun-g: rgba(190, 210, 200, 0.1); }
            12%  { --fy-sky-a: #121a1e; --fy-sky-b: #1a2420; --fy-sun-x: 22%; --fy-sun-y: 58%;  --fy-sun-c: #30403b; --fy-sun-g: rgba(190, 210, 200, 0.1); }
            32%  { --fy-sky-a: #111917; --fy-sky-b: #151e1a; --fy-sun-x: 38%; --fy-sun-y: 24%;  --fy-sun-c: #33433e; --fy-sun-g: rgba(196, 214, 204, 0.11); }
            50%  { --fy-sky-a: #0f1715; --fy-sky-b: #121a17; --fy-sun-x: 52%; --fy-sun-y: 12%;  --fy-sun-c: #36463f; --fy-sun-g: rgba(200, 216, 206, 0.12); }
            70%  { --fy-sky-a: #111714; --fy-sky-b: #181c16; --fy-sun-x: 70%; --fy-sun-y: 30%;  --fy-sun-c: #33423b; --fy-sun-g: rgba(200, 210, 196, 0.11); }
            88%  { --fy-sky-a: #13130f; --fy-sky-b: #1f1913; --fy-sun-x: 85%; --fy-sun-y: 76%;  --fy-sun-c: #3a332a; --fy-sun-g: rgba(224, 138, 99, 0.12); }
            100% { --fy-sky-a: #090e0d; --fy-sky-b: #0e1312; --fy-sun-x: 90%; --fy-sun-y: 108%; --fy-sun-c: #2b2823; --fy-sun-g: rgba(224, 138, 99, 0.06); }
        }

        /* ---------------------------------------------------------------
           Shared pieces
           --------------------------------------------------------------- */
        .fy-wrap { width: min(100% - 2.5rem, 72rem); margin-inline: auto; }
        .fy-section { padding-block: clamp(4.5rem, 10vw, 8.5rem); scroll-margin-top: 4.5rem; }
        .fy-dawn { --fy-tod: #f4d6b3; }
        .fy-morning { --fy-tod: #d9e6d4; }
        .fy-noon { --fy-tod: #f1f1e2; }
        .fy-afternoon { --fy-tod: #f1dcb9; }
        .fy-dusk { --fy-tod: #efbe96; }
        .dark .fy-dawn { --fy-tod: #3d2d24; }
        .dark .fy-morning { --fy-tod: #24382e; }
        .dark .fy-noon { --fy-tod: #1e2c26; }
        .dark .fy-afternoon { --fy-tod: #2f2a1f; }
        .dark .fy-dusk { --fy-tod: #38261d; }

        .fy-head { max-width: 46rem; }
        .fy-head-c { margin-inline: auto; text-align: center; }
        .fy-kick {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: var(--fy-ink);
        }
        .fy-clock {
            padding: 0.2rem 0.6rem;
            border: 1px solid var(--fy-line-2);
            border-radius: 999px;
            font-family: var(--fy-mono);
            font-weight: 400;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
        }
        .fy-h2 {
            margin-top: 1.4rem;
            font-size: clamp(2.1rem, 4.6vw, 3.6rem);
            font-weight: 400;
            line-height: 1.12;
            letter-spacing: 0.002em;
            text-wrap: balance;
        }
        .fy-h2 em,
        .fy-h1 em {
            font-family: var(--fy-serif);
            font-style: italic;
            color: var(--fy-clay-ink);
            letter-spacing: -0.01em;
        }
        .fy-sub { margin-top: 1.35rem; font-size: 1.125rem; color: var(--fy-ink); text-wrap: pretty; }
        .fy-head-c .fy-sub { margin-inline: auto; max-width: 40rem; }

        .fy-card {
            position: relative;
            background: var(--fy-panel);
            background: color-mix(in srgb, var(--fy-tod) var(--fy-tod-mix), var(--fy-panel));
            border: 1px solid var(--fy-line);
            border-radius: 1.75rem;
            box-shadow: var(--fy-shadow);
        }
        .fy-k {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--fy-ink-3);
        }
        .fy-tag {
            display: inline-block;
            flex: none;
            padding: 0.2rem 0.65rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.5;
            background: var(--fy-sage-soft);
            color: var(--fy-sage-ink);
        }
        .fy-tag-warm { background: var(--fy-clay-soft); color: var(--fy-clay-ink); }
        .fy-tag-deep { background: var(--fy-ink); color: var(--fy-sand); }
        .fy-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 700;
            color: var(--fy-ink);
            text-decoration: underline;
            text-decoration-color: var(--fy-clay);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.3em;
            transition: gap 0.5s ease;
        }
        .fy-link:hover { gap: 0.75rem; }
        .fy-link svg { width: 1rem; height: 1rem; flex: none; }
        .fy-after { margin-top: 2.5rem; text-align: center; color: var(--fy-ink); }
        .fy-after .fy-link { margin-inline-start: 0.5rem; }

        .fy-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 1rem 1.7rem;
            border-radius: 999px;
            background: var(--fy-ink);
            color: var(--fy-sand);
            font-weight: 700;
            font-size: 1.0625rem;
            letter-spacing: 0.01em;
            line-height: 1.2;
            transition: translate 0.6s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.6s ease;
        }
        .fy-btn:hover { translate: 0 -2px; box-shadow: 0 1.2rem 2rem -1.1rem rgba(32, 56, 47, 0.7); }
        .fy-btn svg { width: 1.1rem; height: 1.1rem; flex: none; }
        .fy-btn-quiet { background: transparent; color: var(--fy-ink); box-shadow: inset 0 0 0 1.5px var(--fy-line-2); }
        .fy-btn-quiet:hover { box-shadow: inset 0 0 0 1.5px var(--fy-ink); }

        /* ---------------------------------------------------------------
           Hero: first light, and one breath
           --------------------------------------------------------------- */
        .fy-hero { position: relative; overflow: clip; padding-block: clamp(0.75rem, 2vw, 1.5rem) clamp(3rem, 6vw, 5rem); }
        .fy-hero-sun {
            position: absolute;
            left: 16%;
            top: 30rem;
            width: 52rem;
            aspect-ratio: 1;
            translate: -50% -50%;
            border-radius: 50%;
            background: radial-gradient(circle, #fff0cf 0, #fff0cf 3.2rem, rgba(255, 214, 160, 0.7) 3.8rem, transparent 26rem);
            pointer-events: none;
        }
        .dark .fy-hero-sun { background: radial-gradient(circle, #2e3d39 0, #2e3d39 3.2rem, rgba(190, 210, 200, 0.1) 3.8rem, transparent 26rem); }
        .fy-stage { position: relative; isolation: isolate; display: grid; grid-template-columns: minmax(0, 1fr); justify-items: center; text-align: center; padding-block: clamp(2.5rem, 5vw, 4rem); }
        .fy-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.6rem;
            font-family: var(--fy-sans);
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: var(--fy-ink);
        }
        .fy-eyebrow::before { content: "06:30"; content: "06:30" / ""; padding: 0.2rem 0.6rem; border: 1px solid var(--fy-line-2); border-radius: 999px; font-family: var(--fy-mono); font-weight: 400; letter-spacing: 0.08em; }
        .fy-h1 {
            font-size: clamp(2.35rem, 5.5vw, 4.5rem);
            font-weight: 400;
            line-height: 1.12;
            letter-spacing: 0.002em;
        }
        .fy-h1 > span { display: block; }
        .fy-lede { margin-top: 1.75rem; max-width: 36rem; font-size: clamp(1.1rem, 1.5vw, 1.25rem); color: var(--fy-ink); text-wrap: pretty; }
        .fy-cta { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.9rem 1rem; margin-top: 2.25rem; }

        .fy-breath { position: absolute; left: 50%; top: 50%; z-index: -1; width: min(39rem, 96vw); aspect-ratio: 1; translate: -50% -50%; }
        .fy-ring { position: absolute; inset: var(--in); border: 1px solid var(--fy-line-2); border-radius: 50%; opacity: var(--o, 1); }
        .fy-orb {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(circle at 50% 38%, #f6f9f0 0%, rgba(196, 213, 190, 0.95) 40%, rgba(176, 196, 170, 0.7) 66%, rgba(176, 196, 170, 0) 71%);
            scale: 0.82;
        }
        .dark .fy-orb { background: radial-gradient(circle at 50% 38%, #3b5246 0%, rgba(47, 68, 57, 0.95) 40%, rgba(39, 58, 49, 0.7) 66%, rgba(39, 58, 49, 0) 71%); }
        .fy-breath-cap { display: grid; justify-items: center; gap: 0.35rem; margin-top: 2.25rem; }
        .fy-breath-words { display: grid; place-items: center; }
        .fy-breath-words span {
            grid-area: 1 / 1;
            font-family: var(--fy-serif);
            font-style: italic;
            font-size: 1.3rem;
            color: var(--fy-ink);
            opacity: 0;
        }
        .fy-breath-words span:first-child { opacity: 1; }
        .fy-breath-count { font-family: var(--fy-mono); font-size: 0.7rem; letter-spacing: 0.14em; color: var(--fy-ink); }
        @media (prefers-reduced-motion: no-preference) {
            .fy-orb { animation: fy-breathe 12s infinite; }
            .fy-breath-words span:nth-child(1) { animation: fy-word-in 12s infinite; }
            .fy-breath-words span:nth-child(2) { animation: fy-word-hold 12s infinite; }
            .fy-breath-words span:nth-child(3) { animation: fy-word-out 12s infinite; }
            #fy .fy-paced { animation: fy-pace 12s infinite; }
        }
        @keyframes fy-breathe {
            0% { scale: 0.62; animation-timing-function: cubic-bezier(0.45, 0.05, 0.35, 1); }
            33.33% { scale: 1; animation-timing-function: linear; }
            50% { scale: 1; animation-timing-function: cubic-bezier(0.4, 0, 0.3, 1); }
            100% { scale: 0.62; }
        }
        @keyframes fy-word-in { 0%, 29% { opacity: 1; } 33%, 96% { opacity: 0; } 100% { opacity: 1; } }
        @keyframes fy-word-hold { 0%, 30% { opacity: 0; } 34%, 46% { opacity: 1; } 50%, 100% { opacity: 0; } }
        @keyframes fy-word-out { 0%, 47% { opacity: 0; } 51%, 95% { opacity: 1; } 99%, 100% { opacity: 0; } }
        @keyframes fy-pace {
            0%, 100% { box-shadow: 0 0 0 0 rgba(201, 113, 77, 0); }
            33.33%, 50% { box-shadow: 0 0 0 0.55rem rgba(201, 113, 77, 0.16); }
        }

        /* Two events, seven classes: the whole idea in one panel. */
        .fy-two { margin-top: clamp(2.5rem, 5vw, 4rem); padding: clamp(1.5rem, 3vw, 2.5rem); }
        .fy-two-rows { display: grid; gap: 1.75rem 3rem; margin-top: 1.5rem; }
        @media (min-width: 860px) { .fy-two-rows { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .fy-two-top { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.25rem 1rem; }
        .fy-two-name { font-size: 1.35rem; line-height: 1.2; }
        .fy-two-time { font-family: var(--fy-mono); font-size: 0.8rem; letter-spacing: 0.06em; color: var(--fy-ink-2); }
        .fy-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.4rem; margin-top: 0.9rem; }
        .fy-days i {
            display: grid;
            place-items: center;
            height: 2.6rem;
            border-radius: 0.8rem;
            border: 1.5px dashed var(--fy-line-2);
            font-style: normal;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--fy-ink-3);
        }
        .fy-days i.is-on { border: 0; background: var(--c, var(--fy-sage)); color: #14231d; }
        .fy-two-note { margin-top: 0.75rem; font-size: 0.95rem; color: var(--fy-ink-2); }
        .fy-two-foot { margin-top: 1.75rem; padding-top: 1.4rem; border-top: 1px solid var(--fy-line); color: var(--fy-ink-2); max-width: 46rem; }
        .fy-chips { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem 0.6rem; margin-top: clamp(2rem, 4vw, 3rem); }
        .fy-chips li { padding: 0.35rem 0.95rem; border: 1px solid var(--fy-line-2); border-radius: 999px; font-size: 0.875rem; letter-spacing: 0.04em; color: var(--fy-ink); }

        /* ---------------------------------------------------------------
           The unit
           --------------------------------------------------------------- */
        .fy-trio { display: grid; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 860px) { .fy-trio { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fy-trio-item { padding: clamp(1.75rem, 3vw, 2.5rem); }
        .fy-trio-item + .fy-trio-item { border-top: 1px solid var(--fy-line); }
        @media (min-width: 860px) {
            .fy-trio-item + .fy-trio-item { border-top: 0; border-inline-start: 1px solid var(--fy-line); }
        }
        .fy-trio h3 { margin-top: 1rem; font-size: 1.35rem; font-weight: 400; line-height: 1.2; }
        .fy-num { display: block; margin-bottom: 0.2rem; font-size: clamp(3.4rem, 6vw, 4.6rem); line-height: 1; color: var(--fy-clay-ink); }
        .fy-trio p:not(.fy-k) { margin-top: 0.9rem; color: var(--fy-ink-2); }

        /* ---------------------------------------------------------------
           The class board: a real table, drawn on the day
           --------------------------------------------------------------- */
        .fy-board { container-type: inline-size; margin-top: clamp(2.5rem, 5vw, 4rem); view-timeline-name: --fy-board; }
        .fy-board-in { padding: clamp(1.1rem, 2.4vw, 2rem); }
        .fy-tt { width: 100%; border-collapse: collapse; }
        .fy-tt caption { caption-side: top; padding: 0 0.5rem 1.25rem; text-align: start; font-size: 0.95rem; color: var(--fy-ink-2); }
        .fy-tt th, .fy-tt td { padding: 0.95rem 0.5rem; text-align: start; vertical-align: middle; }
        .fy-tt thead th { padding-block: 0 0.6rem; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--fy-ink-3); vertical-align: bottom; }
        .fy-tt tbody tr { border-top: 1px solid var(--fy-line); transition: opacity 0.5s ease; }
        .fy-tt tbody:has(tr:hover) tr:not(:hover) { opacity: 0.55; }
        .fy-tt-name { font-size: 1.15rem; font-weight: 400; line-height: 1.25; white-space: nowrap; }
        .fy-tt-name small { display: block; font-family: var(--fy-mono); font-size: 0.72rem; letter-spacing: 0.06em; color: var(--fy-ink-3); }
        .fy-tt-time { font-family: var(--fy-mono); font-size: 0.85rem; letter-spacing: 0.04em; white-space: nowrap; color: var(--fy-ink-2); }
        .fy-pips { display: inline-grid; grid-template-columns: repeat(7, 0.62rem); gap: 0.22rem; vertical-align: middle; }
        .fy-pips i { height: 0.62rem; border-radius: 50%; border: 1.5px solid var(--fy-line-2); }
        .fy-pips i.is-on { border-color: var(--fy-ink); background: var(--fy-ink); }
        .fy-tt-day { width: 46%; }
        .fy-arc {
            position: relative;
            height: 2.4rem;
            margin-inline: 0.35rem;
            border: 1px dashed var(--fy-line-2);
            border-bottom: 0;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        .fy-arc-sun { position: absolute; left: 50%; top: 0; width: 0.8rem; height: 0.8rem; margin: -0.4rem; border-radius: 50%; background: var(--fy-clay); box-shadow: 0 0 0 0.3rem rgba(201, 113, 77, 0.18); }
        @supports (animation-timeline: view()) and (offset-path: ellipse(50% 100% at 50% 100%)) {
            html.es-anim #fy .fy-arc-sun {
                left: 0;
                offset-path: ellipse(50% 100% at 50% 100%);
                offset-rotate: 0deg;
                animation: fy-arc linear both;
                animation-timeline: --fy-board;
                animation-range: entry 35% exit 65%;
            }
        }
        @keyframes fy-arc { from { offset-distance: 50%; } to { offset-distance: 100%; } }
        .fy-hours { display: flex; justify-content: space-between; margin-top: 0.4rem; font-family: var(--fy-mono); font-size: 0.68rem; font-weight: 400; letter-spacing: 0.06em; text-transform: none; }
        .fy-rail {
            position: relative;
            height: 1.7rem;
            border-radius: 999px;
            background-image: repeating-linear-gradient(90deg, var(--fy-line) 0 1px, transparent 1px 20%), var(--fy-day);
            box-shadow: inset 0 0 0 1px var(--fy-line);
            overflow: hidden;
        }
        .fy-bar { position: absolute; top: 0.3rem; bottom: 0.3rem; min-width: 0.7rem; border-radius: 999px; background: #20382f; }
        .dark .fy-bar { background: #ece6d9; }
        .is-draft .fy-bar { background: transparent; border: 1.5px dashed #20382f; }
        .dark .is-draft .fy-bar { border-color: #ece6d9; }
        .fy-tt-draft { display: inline-block; margin-inline-start: 0.5rem; font-family: var(--fy-sans); }
        .fy-board-note { margin: 1.5rem auto 0; max-width: 46rem; text-align: center; color: var(--fy-ink); }
        @container (max-width: 44rem) {
            .fy-tt, .fy-tt tbody, .fy-tt tr, .fy-tt th, .fy-tt td { display: block; }
            .fy-tt thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
            .fy-tt caption { display: block; padding-inline: 0; }
            .fy-tt tbody tr { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.5rem 1rem; align-items: center; padding-block: 1.1rem; }
            .fy-tt th, .fy-tt td { padding: 0; }
            .fy-tt-name { white-space: normal; }
            .fy-tt-time { justify-self: end; }
            .fy-tt-day { grid-column: 1 / -1; width: auto; }
        }

        .fy-three { display: grid; gap: 1.25rem; margin-top: clamp(2rem, 4vw, 3rem); }
        @media (min-width: 860px) { .fy-three { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fy-mini { display: flex; flex-direction: column; padding: 1.6rem 1.6rem 1.7rem; border-radius: 1.5rem; }
        .fy-mini-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .fy-mini h3 { font-size: 1.25rem; font-weight: 400; line-height: 1.25; text-wrap: balance; }
        .fy-mini p { margin-top: 0.8rem; color: var(--fy-ink-2); }
        .fy-mini p + p { margin-top: 0.9rem; }
        .fy-mini a { color: var(--fy-ink); font-weight: 700; text-decoration: underline; text-decoration-color: var(--fy-clay); text-decoration-thickness: 2px; text-underline-offset: 0.25em; }

        /* ---------------------------------------------------------------
           The card: punched, on the studio floor
           --------------------------------------------------------------- */
        .fy-floorband { margin-inline: clamp(0.5rem, 2vw, 1.5rem); padding-block: clamp(4rem, 8vw, 7rem); border-radius: clamp(1.5rem, 4vw, 3rem); background: var(--fy-band); }
        .fy-pass-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 980px) { .fy-pass-grid { grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr); gap: 4rem; } }
        .fy-pass-grid .fy-sub { color: var(--fy-ink); }
        .fy-points { display: grid; gap: 1rem; margin-top: 2rem; }
        .fy-points li { display: grid; grid-template-columns: 0.7rem minmax(0, 1fr); gap: 1rem; color: var(--fy-ink); }
        .fy-points li::before { content: ""; width: 0.7rem; height: 0.7rem; margin-top: 0.5rem; border-radius: 50%; background: var(--fy-clay); }
        .fy-pcard-wrap { position: relative; width: min(100%, 27rem); margin: 0 auto 3rem; }
        .fy-pcard { position: relative; rotate: -2deg; color: #20382f; filter: drop-shadow(0 1.6rem 1.6rem rgba(20, 35, 29, 0.32)); }
        .fy-pcard-top,
        .fy-pcard-holes,
        .fy-pcard-body { background: #fbf4e6; }
        .fy-pcard-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.5rem 1.5rem 0.75rem; border-radius: 1.25rem 1.25rem 0 0; }
        .fy-pcard-top .fy-k { color: #56685e; }
        .fy-pcard-top h3 { margin-top: 0.3rem; font-size: 1.6rem; font-weight: 400; line-height: 1.15; }
        .fy-pcard-top .fy-tag { background: #f1d9c8; color: #944423; }
        .fy-pcard-strip { container-type: inline-size; }
        .fy-pcard-holes {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            aspect-ratio: 5 / 2;
            padding: 0;
            -webkit-mask-image: radial-gradient(circle 6.1cqi at 10% 25%, transparent 96%, #000 100%), radial-gradient(circle 6.1cqi at 30% 25%, transparent 96%, #000 100%), radial-gradient(circle 6.1cqi at 50% 25%, transparent 96%, #000 100%);
            mask-image: radial-gradient(circle 6.1cqi at 10% 25%, transparent 96%, #000 100%), radial-gradient(circle 6.1cqi at 30% 25%, transparent 96%, #000 100%), radial-gradient(circle 6.1cqi at 50% 25%, transparent 96%, #000 100%);
            -webkit-mask-composite: source-in;
            mask-composite: intersect;
        }
        .fy-pcard-holes i {
            place-self: center;
            display: grid;
            place-items: center;
            width: 69%;
            aspect-ratio: 1;
            border: 1.5px solid rgba(32, 56, 47, 0.55);
            border-radius: 50%;
            font-family: var(--fy-mono);
            font-style: normal;
            font-size: 0.72rem;
            color: #56685e;
        }
        .fy-pcard-holes i.is-out { color: transparent; border-style: dashed; }
        .fy-pcard-body { padding: 0.75rem 1.5rem 1.5rem; border-radius: 0 0 1.25rem 1.25rem; }
        .fy-pcard-count { font-size: 1.05rem; }
        .fy-pcard-count span { color: #944423; font-weight: 700; }
        .fy-pcard dl { margin-top: 1rem; border-top: 1px solid rgba(32, 56, 47, 0.16); }
        .fy-pcard dl div { display: flex; justify-content: space-between; gap: 1rem; padding-block: 0.6rem; border-bottom: 1px solid rgba(32, 56, 47, 0.16); font-size: 0.95rem; }
        .fy-pcard dt { color: #56685e; }
        .fy-pcard dd { text-align: end; font-weight: 700; }
        .fy-pcard-note { margin-top: 1rem; font-size: 0.9rem; color: #3d5248; }
        .fy-keytag {
            position: absolute;
            right: -0.75rem;
            bottom: -3.4rem;
            display: grid;
            gap: 0.1rem;
            padding: 0.9rem 1.2rem 0.9rem 2.6rem;
            border-radius: 999px;
            background: #20382f;
            color: #f3ede3;
            rotate: 5deg;
            box-shadow: 0 1rem 1.4rem -0.8rem rgba(20, 35, 29, 0.6);
        }
        .fy-keytag::before { content: ""; position: absolute; left: 0.9rem; top: 50%; width: 0.9rem; height: 0.9rem; translate: 0 -50%; border-radius: 50%; background: var(--fy-band); box-shadow: 0 0 0 2px #9fb49a; }
        .fy-keytag b { font-weight: 700; font-size: 0.95rem; letter-spacing: 0.04em; }
        .fy-keytag span { font-size: 0.78rem; color: #cdd8c8; }
        @media (max-width: 520px) { .fy-keytag { right: 0; } }

        /* ---------------------------------------------------------------
           Four shapes of pass
           --------------------------------------------------------------- */
        .fy-kinds { container-type: inline-size; margin-top: clamp(2.5rem, 5vw, 4rem); }
        .fy-kinds-in { padding: clamp(1.1rem, 2.4vw, 2rem); }
        .fy-kt { width: 100%; border-collapse: collapse; }
        .fy-kt caption { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); white-space: nowrap; }
        .fy-kt th, .fy-kt td { padding: 1.15rem 0.75rem; text-align: start; vertical-align: top; }
        .fy-kt thead th { padding-block: 0.25rem 0.75rem; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: var(--fy-ink-3); }
        .fy-kt tbody tr { border-top: 1px solid var(--fy-line); }
        .fy-kt tbody th { font-size: 1.2rem; font-weight: 400; line-height: 1.25; white-space: nowrap; }
        .fy-kt tbody th span { display: block; margin-top: 0.2rem; font-family: var(--fy-mono); font-size: 0.72rem; letter-spacing: 0.04em; color: var(--fy-ink-3); }
        .fy-kt td { color: var(--fy-ink-2); }
        .fy-kt td:last-child { color: var(--fy-ink); font-family: var(--fy-serif); font-style: italic; font-size: 1.1rem; }
        @container (max-width: 40rem) {
            .fy-kt, .fy-kt tbody, .fy-kt tr, .fy-kt th, .fy-kt td { display: block; }
            .fy-kt thead { position: absolute; width: 1px; height: 1px; overflow: hidden; clip-path: inset(50%); }
            .fy-kt tbody tr { padding-block: 1.2rem; }
            .fy-kt tbody tr:first-child { border-top: 0; padding-top: 0.25rem; }
            .fy-kt th, .fy-kt td { padding: 0; }
            .fy-kt tbody th { white-space: normal; }
            .fy-kt td { margin-top: 0.5rem; }
        }
        .fy-kinds-note { margin-top: 1.25rem; padding: 1.25rem 0.75rem 0.25rem; border-top: 1px solid var(--fy-line); color: var(--fy-ink-2); }
        .fy-pay { margin: 2.5rem auto 0; max-width: 44rem; text-align: center; color: var(--fy-ink); }
        .fy-pay .fy-link { margin-inline-start: 0.4rem; }

        /* ---------------------------------------------------------------
           The deadline, and the mats
           --------------------------------------------------------------- */
        .fy-duplex { position: relative; display: grid; gap: 1.25rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 860px) {
            .fy-duplex { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4.5rem; }
            .fy-duplex::before { content: ""; position: absolute; left: 50%; top: -1rem; bottom: -1rem; border-inline-start: 1.5px dashed var(--fy-line-2); }
            .fy-duplex::after {
                content: "cut-off";
                position: absolute;
                left: 50%;
                top: 50%;
                translate: -50% -50%;
                padding: 0.3rem 0.8rem;
                border-radius: 999px;
                background: var(--fy-ink);
                color: var(--fy-sand);
                font-family: var(--fy-mono);
                font-size: 0.7rem;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }
        }
        .fy-side { padding: clamp(1.6rem, 3vw, 2.25rem); }
        .fy-side-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .fy-side h3 { margin-top: 1.1rem; font-size: clamp(1.5rem, 2.6vw, 1.9rem); font-weight: 400; line-height: 1.2; text-wrap: balance; }
        .fy-side > p { margin-top: 0.9rem; color: var(--fy-ink-2); }
        .fy-ledger { margin-top: 1.5rem; border-radius: 1rem; background: var(--fy-sand); border: 1px solid var(--fy-line); }
        .fy-ledger div { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.8rem 1rem; font-size: 0.95rem; }
        .fy-ledger div + div { border-top: 1px solid var(--fy-line); }
        .fy-ledger span:first-child { color: var(--fy-ink-2); }
        .fy-ledger span:last-child { flex: none; font-family: var(--fy-mono); font-size: 0.82rem; color: var(--fy-ink); }
        .fy-ledger .fy-good { padding: 0.15rem 0.6rem; border-radius: 999px; background: var(--fy-sage-soft); color: var(--fy-sage-ink); font-family: var(--fy-sans); font-weight: 700; }

        .fy-fit { display: grid; gap: 2rem 3rem; margin-top: clamp(1.5rem, 3vw, 2.25rem); padding: clamp(1.6rem, 3vw, 2.5rem); }
        @media (min-width: 980px) { .fy-fit { grid-template-columns: minmax(0, 0.85fr) minmax(0, 1.15fr); align-items: center; } }
        .fy-fit h3 { font-size: clamp(1.5rem, 2.6vw, 1.9rem); font-weight: 400; line-height: 1.2; }
        .fy-fit-copy > p { margin-top: 0.9rem; color: var(--fy-ink-2); }
        .fy-floors { display: grid; gap: 1.1rem; container-type: inline-size; }
        .fy-floor-row { display: grid; grid-template-columns: 4.2rem minmax(0, 1fr); gap: 0.9rem; align-items: center; }
        @container (min-width: 34rem) { .fy-floor-row { grid-template-columns: 4.2rem minmax(0, 1fr) 8.2rem; } }
        .fy-floor-date { font-family: var(--fy-mono); font-size: 0.8rem; letter-spacing: 0.04em; color: var(--fy-ink); }
        .fy-floor {
            display: grid;
            grid-template-columns: repeat(16, minmax(0, 1fr));
            gap: 0.28rem;
            padding: 0.5rem;
            border-radius: 0.8rem;
            background-color: var(--fy-sand);
            background-image: repeating-linear-gradient(90deg, var(--fy-line) 0 1px, transparent 1px 12.5%);
            box-shadow: inset 0 0 0 1px var(--fy-line);
        }
        .fy-mat { aspect-ratio: 1 / 2.5; border-radius: 0.22rem; border: 1.5px dashed var(--fy-line-2); transition: opacity 0.7s ease, scale 0.7s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(var(--i) * 55ms + 0.25s); }
        .fy-mat.is-taken { border: 0; background: var(--fy-clay); }
        .fy-mat.is-you { border: 0; background: var(--fy-ink); }
        html.es-anim #fy [data-reveal]:not(.is-revealed) .fy-mat.is-taken,
        html.es-anim #fy [data-reveal]:not(.is-revealed) .fy-mat.is-you { opacity: 0; scale: 0.6; }
        .fy-floor-state { grid-column: 2; justify-self: start; padding: 0.2rem 0.7rem; border-radius: 999px; background: var(--fy-sage-soft); color: var(--fy-sage-ink); font-size: 0.8rem; font-weight: 700; white-space: nowrap; }
        @container (min-width: 34rem) { .fy-floor-state { grid-column: auto; justify-self: end; } }
        .fy-floor-state.is-full { background: var(--fy-ink); color: var(--fy-sand); }
        .fy-floor-key { display: flex; flex-wrap: wrap; gap: 0.4rem 1.25rem; margin-top: 0.4rem; font-size: 0.8rem; color: var(--fy-ink-2); }
        .fy-floor-key span { display: inline-flex; align-items: center; gap: 0.45rem; }
        .fy-floor-key i { width: 0.5rem; height: 1.1rem; border-radius: 0.15rem; border: 1.5px dashed var(--fy-line-2); }
        .fy-floor-key i.is-taken { border: 0; background: var(--fy-clay); }
        .fy-floor-key i.is-you { border: 0; background: var(--fy-ink); }

        /* ---------------------------------------------------------------
           One-to-ones: a single mat, and a time
           --------------------------------------------------------------- */
        .fy-solo { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: center; }
        @media (min-width: 980px) { .fy-solo { grid-template-columns: minmax(0, 1fr) minmax(0, 0.8fr); } }
        .fy-slotcard { display: grid; grid-template-columns: 5.2rem minmax(0, 1fr); gap: 1.4rem; padding: 1.5rem; width: min(100%, 26rem); margin-inline: auto; }
        .fy-onemat {
            position: relative;
            aspect-ratio: 1 / 2.3;
            border-radius: 0.6rem;
            background: var(--fy-sage);
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.28), rgba(255, 255, 255, 0) 30%), repeating-linear-gradient(180deg, rgba(20, 35, 29, 0.1) 0 1px, transparent 1px 0.5rem);
            display: grid;
            place-items: center;
            font-family: var(--fy-mono);
            font-size: 0.8rem;
            color: #14231d;
        }
        .fy-onemat::before { content: ""; position: absolute; inset: 12% auto 12% 50%; border-inline-start: 1px dashed rgba(20, 35, 29, 0.45); }
        .fy-onemat span { position: relative; padding: 0.15rem 0.4rem; border-radius: 0.3rem; background: #f3ede3; }
        .fy-slots { display: grid; gap: 0.5rem; align-content: center; }
        .fy-slots p { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: var(--fy-ink-3); }
        .fy-slot { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.6rem 0.9rem; border-radius: 0.8rem; border: 1px solid var(--fy-line-2); font-family: var(--fy-mono); font-size: 0.82rem; color: var(--fy-ink); }
        .fy-slot.is-picked { border-color: transparent; background: var(--fy-ink); color: var(--fy-sand); }
        .fy-slot.is-busy { border-style: dashed; color: var(--fy-ink-3); background-image: repeating-linear-gradient(135deg, var(--fy-line) 0 1px, transparent 1px 7px); }
        .fy-slot span { flex: none; white-space: nowrap; }
        .fy-slot small { font-family: var(--fy-sans); font-size: 0.78rem; text-align: end; }
        .fy-solo-after { margin: clamp(2.5rem, 5vw, 3.5rem) auto 0; max-width: 46rem; text-align: center; color: var(--fy-ink); }
        .fy-solo-after .fy-link { margin-inline-start: 0.4rem; }

        /* ---------------------------------------------------------------
           Everything else
           --------------------------------------------------------------- */
        .fy-rest { display: grid; gap: 1.25rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 720px) { .fy-rest { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) {
            .fy-rest { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .fy-rest > .is-wide { grid-column: span 2; }
        }
        html.es-anim #fy .fy-rollmat[data-reveal],
        html.es-anim #fy .fy-onward a[data-reveal] {
            transition: opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1), transform 0.9s cubic-bezier(0.22, 1, 0.36, 1), translate 0.7s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--reveal-delay, 0s), var(--reveal-delay, 0s), 0s;
        }
        .fy-pill-inline { display: inline-block; margin-inline-start: 0.2rem; vertical-align: baseline; }

        /* ---------------------------------------------------------------
           Perfect for: six mats, each part rolled
           --------------------------------------------------------------- */
        .fy-mats { display: grid; gap: 1.5rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 720px) { .fy-mats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .fy-mats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fy-rollmat {
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 13.5rem;
            padding: 1.7rem 3.6rem 1.6rem 1.7rem;
            border-radius: 1.1rem;
            background-color: var(--m);
            background-image: repeating-linear-gradient(90deg, rgba(20, 35, 29, 0.05) 0 1px, transparent 1px 0.45rem);
            color: #20382f;
            box-shadow: 0 1.5rem 2rem -1.6rem rgba(20, 35, 29, 0.55);
            transition: translate 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .fy-rollmat:hover { translate: 0 -4px; }
        /* The rolled end: a cylinder seen from above. */
        .fy-rollmat::after {
            content: "";
            position: absolute;
            inset: -0.3rem 0 -0.3rem auto;
            width: 2.2rem;
            border-radius: 1.1rem;
            background: linear-gradient(90deg, rgba(20, 35, 29, 0.28), rgba(255, 255, 255, 0.5) 38%, rgba(20, 35, 29, 0.12) 72%, rgba(20, 35, 29, 0.34)), var(--m);
            box-shadow: -0.5rem 0 0.8rem -0.5rem rgba(20, 35, 29, 0.5);
        }
        .fy-rollmat h3 { font-size: 1.4rem; font-weight: 400; line-height: 1.2; }
        .fy-rollmat p { margin-top: 0.75rem; font-size: 1rem; color: #2c4238; }
        .fy-rollmat a { margin-top: auto; padding-top: 1.1rem; align-self: flex-start; font-weight: 700; color: #20382f; }
        .fy-rollmat a span { display: inline-flex; align-items: center; gap: 0.4rem; border-bottom: 2px solid #20382f; transition: gap 0.5s ease; }
        .fy-rollmat a:hover span { gap: 0.7rem; }
        .fy-rollmat a svg { width: 1rem; height: 1rem; }
        #fy .fy-rollmat a:focus-visible { outline-color: #20382f; }

        /* ---------------------------------------------------------------
           Three steps: sunrise, noon, sunset
           --------------------------------------------------------------- */
        .fy-steps { display: grid; gap: 2.5rem 2rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 860px) { .fy-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fy-step { text-align: center; }
        .fy-step-sky {
            position: relative;
            width: 9rem;
            height: 5.25rem;
            margin-inline: auto;
            border-bottom: 1.5px solid var(--fy-ink);
            overflow: hidden;
        }
        .fy-step-sky i {
            position: absolute;
            left: var(--x);
            top: var(--y);
            width: 3rem;
            aspect-ratio: 1;
            translate: -50% -50%;
            border-radius: 50%;
            background: var(--fy-clay);
            box-shadow: 0 0 0 0.7rem rgba(201, 113, 77, 0.16), 0 0 0 1.5rem rgba(201, 113, 77, 0.08);
        }
        .fy-step-no { margin-top: 1.25rem; font-family: var(--fy-mono); font-size: 0.8rem; letter-spacing: 0.14em; color: var(--fy-ink); }
        .fy-step h3 { margin-top: 0.5rem; font-size: 1.6rem; font-weight: 400; line-height: 1.2; }
        .fy-step p { margin: 0.9rem auto 0; max-width: 20rem; color: var(--fy-ink); }

        /* ---------------------------------------------------------------
           Key features, and the way on
           --------------------------------------------------------------- */
        .fy-two-col { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 980px) { .fy-two-col { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); } }
        .fy-list { padding: 0.6rem clamp(1rem, 2.4vw, 1.75rem); }
        .fy-list a {
            display: grid;
            grid-template-columns: 0.7rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 1.1rem;
            padding: 1.15rem 0.25rem;
            transition: padding 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .fy-list a + a { border-top: 1px solid var(--fy-line); }
        .fy-list a::before { content: ""; width: 0.7rem; height: 0.7rem; border-radius: 50%; border: 1.5px solid var(--fy-clay); transition: background-color 0.5s ease; }
        .fy-list a:hover { padding-inline: 0.75rem 0.25rem; }
        .fy-list a:hover::before { background: var(--fy-clay); }
        .fy-list strong { display: block; font-size: 1.2rem; font-weight: 400; line-height: 1.3; }
        .fy-list small { display: block; margin-top: 0.15rem; font-size: 0.95rem; color: var(--fy-ink-2); }
        .fy-list svg { width: 1.2rem; height: 1.2rem; color: var(--fy-ink-2); }
        .fy-more { margin-top: 1.75rem; }

        .fy-onward { display: grid; gap: 1rem; margin-top: 2.5rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 900px) { .fy-onward { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .fy-onward a { display: flex; flex-direction: column; justify-content: space-between; gap: 2rem; min-height: 9rem; padding: 1.4rem; border-radius: 1.5rem; transition: translate 0.7s cubic-bezier(0.22, 1, 0.36, 1); }
        .fy-onward a:hover { translate: 0 -4px; }
        .fy-onward strong { font-size: 1.25rem; font-weight: 400; line-height: 1.25; }
        .fy-onward span { display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.9rem; font-weight: 700; color: var(--fy-ink-2); transition: gap 0.5s ease; }
        .fy-onward a:hover span { gap: 0.7rem; }
        .fy-onward svg { width: 0.95rem; height: 0.95rem; }
        .fy-onward-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1rem 2rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the light changes.
           --------------------------------------------------------------- */
        #fy .fy-plans > section { background: transparent; }
        #fy .fy-plans h2 { font-family: var(--fy-sans); font-weight: 400; font-size: clamp(1.9rem, 4vw, 3rem); line-height: 1.15; letter-spacing: 0.002em; color: var(--fy-ink); }
        #fy .fy-plans h2 + p { color: var(--fy-ink); font-size: 1.0625rem; }
        #fy .fy-plans .grid > div { background: var(--fy-panel); border: 1px solid var(--fy-line); border-radius: 1.75rem; box-shadow: var(--fy-shadow); color: var(--fy-ink); }
        #fy .fy-plans .grid > div:nth-child(2) { border-color: var(--fy-sage); box-shadow: 0 0 0 0.35rem color-mix(in srgb, var(--fy-sage) 30%, transparent), var(--fy-shadow); }
        #fy .fy-plans .grid > div span,
        #fy .fy-plans .grid > div p,
        #fy .fy-plans .grid > div li { color: var(--fy-ink-2); }
        #fy .fy-plans .grid > div .text-3xl { font-weight: 400; font-size: 2.6rem; color: var(--fy-ink); }
        #fy .fy-plans .grid > div .uppercase { color: var(--fy-ink); }
        #fy .fy-plans .grid > div .rounded-full { background: var(--fy-sage-soft); color: var(--fy-sage-ink); }
        #fy .fy-plans .grid > div svg { color: var(--fy-clay-ink); }
        #fy .fy-plans a.font-medium { color: var(--fy-ink); text-decoration: underline; text-decoration-color: var(--fy-clay); text-decoration-thickness: 2px; text-underline-offset: 0.3em; }
        #fy .fy-plans a.rounded-2xl { background: var(--fy-ink); color: var(--fy-sand); border-radius: 999px; box-shadow: none; }

        #fy .fy-keep > section { background: var(--fy-sand); border-top: 1px solid var(--fy-line); }
        #fy .fy-keep h2 { font-family: var(--fy-sans); font-weight: 400; color: var(--fy-ink); }
        #fy .fy-keep p.uppercase { color: var(--fy-clay-ink); letter-spacing: 0.22em; }
        #fy .fy-keep .grid > a { background: var(--fy-panel); border: 1px solid var(--fy-line); border-radius: 1.5rem; }
        #fy .fy-keep .grid > a:hover { border-color: var(--fy-sage); }
        #fy .fy-keep .grid > a > span:first-child { display: none; }
        #fy .fy-keep .grid > a h3 { color: var(--fy-ink); font-weight: 700; }
        #fy .fy-keep .grid > a p { color: var(--fy-ink-2); }
        #fy .fy-keep .grid > a > span:last-child,
        #fy .fy-keep a.self-start { color: var(--fy-clay-ink); }

        /* ---------------------------------------------------------------
           Questions
           --------------------------------------------------------------- */
        .fy-faq-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .fy-faq-grid { grid-template-columns: minmax(0, 0.68fr) minmax(0, 1.32fr); }
            .fy-faq-head { position: sticky; top: 6.5rem; }
        }
        .fy-qa { padding: 0.5rem clamp(1.1rem, 2.4vw, 2rem); }
        .fy-qa details + details { border-top: 1px solid var(--fy-line); }
        .fy-qa summary { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr) 1.3rem; align-items: start; gap: 0.75rem; padding: 1.35rem 0; cursor: pointer; }
        .fy-qa-no { padding-top: 0.2rem; font-family: var(--fy-mono); font-size: 0.8rem; letter-spacing: 0.06em; color: var(--fy-clay-ink); }
        .fy-qa h3 { font-size: 1.15rem; font-weight: 400; line-height: 1.35; }
        .fy-qa summary i { position: relative; width: 1.3rem; height: 1.3rem; margin-top: 0.15rem; border-radius: 50%; border: 1.5px solid var(--fy-line-2); transition: background-color 0.5s ease, border-color 0.5s ease; }
        .fy-qa summary i::before,
        .fy-qa summary i::after { content: ""; position: absolute; left: 50%; top: 50%; width: 0.55rem; height: 1.5px; translate: -50% -50%; background: var(--fy-ink); transition: rotate 0.5s cubic-bezier(0.22, 1, 0.36, 1), background-color 0.5s ease; }
        .fy-qa summary i::after { rotate: 90deg; }
        .fy-qa details[open] summary i { background: var(--fy-ink); border-color: var(--fy-ink); }
        .fy-qa details[open] summary i::before,
        .fy-qa details[open] summary i::after { background: var(--fy-sand); }
        .fy-qa details[open] summary i::after { rotate: 0deg; }
        .fy-qa details p { padding: 0 0 1.6rem 3.15rem; color: var(--fy-ink-2); max-width: 46rem; }
        @media (max-width: 560px) { .fy-qa details p { padding-inline-start: 0; } }

        /* ---------------------------------------------------------------
           Evening: the last mat rolled up
           --------------------------------------------------------------- */
        .fy-evening { padding: clamp(1.5rem, 4vw, 3rem) clamp(0.5rem, 2vw, 1.5rem) clamp(3rem, 6vw, 5rem); scroll-margin-top: 4.5rem; }
        .fy-night {
            position: relative;
            overflow: hidden;
            padding: clamp(3.5rem, 8vw, 6.5rem) clamp(1.25rem, 4vw, 3rem);
            border-radius: clamp(1.5rem, 4vw, 3rem);
            background-color: #16241f;
            background-image:
                radial-gradient(60rem 22rem at 50% 118%, rgba(224, 138, 99, 0.42), rgba(224, 138, 99, 0) 70%),
                radial-gradient(circle at 82% 16%, #e9e2d2 0, #e9e2d2 1.5rem, rgba(233, 226, 210, 0.16) 1.7rem, rgba(233, 226, 210, 0) 9rem),
                linear-gradient(180deg, #121d1b 0%, #1b2d27 62%, #263a31 100%);
            color: #f3ede3;
            text-align: center;
        }
        @media (max-width: 700px) {
            .fy-night {
                background-image:
                    radial-gradient(40rem 16rem at 50% 112%, rgba(224, 138, 99, 0.42), rgba(224, 138, 99, 0) 70%),
                    radial-gradient(circle at 88% 4.5%, #e9e2d2 0, #e9e2d2 0.9rem, rgba(233, 226, 210, 0.16) 1.05rem, rgba(233, 226, 210, 0) 6rem),
                    linear-gradient(180deg, #121d1b 0%, #1b2d27 62%, #263a31 100%);
            }
        }
        .fy-night .fy-kick { color: #f3ede3; }
        .fy-night .fy-clock { border-color: rgba(243, 237, 227, 0.45); }
        .fy-night .fy-h2 { margin-inline: auto; max-width: 18ch; font-size: clamp(2.3rem, 5.6vw, 4.2rem); }
        .fy-night .fy-h2 em { color: #f0a988; }
        .fy-night-sub { margin: 1.5rem auto 0; max-width: 38rem; font-size: 1.125rem; color: #dbe2d5; }
        .fy-claimbox { display: grid; gap: 0.9rem; width: min(100%, 31rem); margin: 2.5rem auto 0; }
        #fy .fy-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.4rem;
            border: 1px solid rgba(243, 237, 227, 0.4);
            border-radius: 999px;
            background: rgba(243, 237, 227, 0.08);
            font-family: var(--fy-mono);
            font-size: clamp(0.9rem, 3.2vw, 1.05rem);
            transition: border-color 0.5s ease, box-shadow 0.5s ease;
        }
        #fy .fy-claim:focus-within { border-color: #f3ede3; box-shadow: 0 0 0 4px rgba(243, 237, 227, 0.18); }
        #fy .fy-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; color: #f3ede3; box-shadow: none; outline: none; }
        #fy .fy-claim input::placeholder { color: #a9b8ac; }
        .fy-claim span { flex: none; color: #c9d3c6; user-select: none; }
        /* On a phone the field is 16px, the size below which iOS zooms the page on focus. The
           pair is let out into the card's padding so the placeholder still fits at 360px. */
        @media (max-width: 500px) {
            .fy-claimbox { width: auto; margin-inline: -0.5rem; }
            #fy .fy-claim { padding-inline: 1rem; font-size: 1rem; }
        }
        #fy .fy-night .fy-btn { background: #f3ede3; color: #20382f; }
        #fy .fy-night a:focus-visible { outline-color: #f3ede3; }
        .fy-night-note { margin-top: 1.1rem; font-size: 0.95rem; color: #c9d3c6; }

        /* ---------------------------------------------------------------
           The sundial: a section rail, on wide screens only
           --------------------------------------------------------------- */
        .fy-dial { display: none; }
        @media (min-width: 1560px) {
            /* The nav is a box the size of the page that clips the rail, so the rail stays fixed to
               the screen and still ends where the page does instead of riding over the site footer. */
            .fy-dial { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            /* Half a rem from the edge, so the resting marks stay on the strip of sky beside the
               night card: further in, they were dark ink on its dark ground. */
            .fy-dial ul { position: fixed; right: 0.5rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.1rem; justify-items: end; }
            .fy-dial a { position: relative; display: flex; align-items: center; justify-content: flex-end; gap: 0.6rem; padding: 0.32rem 0; color: var(--fy-ink); pointer-events: auto; }
            .fy-dial a::after { content: ""; width: 0.75rem; height: 1.5px; background: currentColor; opacity: 0.55; transition: width 0.5s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.5s ease, background-color 0.5s ease; }
            .fy-dial a span { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; opacity: 0; translate: 0.3rem 0; transition: opacity 0.4s ease, translate 0.4s ease; padding: 0.1rem 0.5rem; border-radius: 999px; background: var(--fy-panel); }
            .fy-dial a:hover span,
            .fy-dial a:focus-visible span,
            .fy-dial a.is-active span { opacity: 1; translate: 0 0; }
            .fy-dial a.is-active::after { width: 1.8rem; opacity: 1; background: var(--fy-clay); height: 2px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .fy-btn, .fy-link, .fy-rollmat, .fy-onward a, .fy-list a, .fy-mat { transition: none; }
        }
    </style>

    @php
        // ---- The timetable ------------------------------------------------
        // One row per recurring class, because one recurring event carries one
        // starts_at: the 6:30 AM flow and the 6:45 PM vinyasa are two events.
        // start = minutes past midnight, len = minutes, days = Mon..Sun.
        // The rail runs 6:00 AM to 9:00 PM, i.e. 900 minutes wide.
        $railFrom = 360;
        $railSpan = 900;
        $timetable = [
            ['Sunrise Flow',      '6:30 AM',  390,  60, [1, 1, 1, 1, 1, 0, 0], false],
            ['Reformer Pilates',  '9:00 AM',  540,  50, [0, 1, 0, 1, 0, 0, 0], false],
            ['Lunch Express',     '12:15 PM', 735,  30, [1, 0, 1, 0, 1, 0, 0], false],
            ['Strength Circuit',  '5:30 PM',  1050, 45, [1, 0, 1, 0, 0, 0, 0], false],
            ['Power Vinyasa',     '6:45 PM',  1125, 75, [0, 1, 0, 1, 0, 0, 0], false],
            ['Sound Bath',        '7:30 PM',  1170, 60, [0, 0, 0, 0, 1, 0, 0], true],
            ['Weekend Long Flow', '9:00 AM',  540,  90, [0, 0, 0, 0, 0, 1, 1], false],
        ];
        $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // The four pass shapes, straight off Ticket::pass_usage_type.
        $passKinds = [
            ['Visit pass', 'A set number of visits, counted down as they are used', 'The ten-class card', 'pass_max_uses'],
            ['Membership', 'No count at all, valid until the pass expires', 'A monthly unlimited', 'pass_valid_days'],
            ['Festival pass', 'One visit to each class the pass covers', 'A six-week course, one visit per session', 'pass_scope'],
            ['Season pass', 'Every occurrence of one recurring class, once each', 'A term of Tuesday 6:45 vinyasa', 'days_of_week'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for fitness and yoga instructors?',
                'a' => 'Yes. Publishing your timetable, setting classes up as recurring events, free registration with a capacity limit per date, two-way Google, Outlook and CalDAV sync, the embeddable timetable, built-in analytics and 10 newsletter emails a month (each recipient counts as one) are all free forever, however many students sign up. Scanning a QR code at the door is free on every plan too. Charging for a drop-in is the Pro half, along with class passes and the live check-in dashboard, on the Pro plan at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on what you sell, free plan included.',
            ],
            [
                'q' => 'Can I schedule recurring weekly classes?',
                'a' => 'Yes, on the free plan. Pick the days of the week a class runs, give it one start time and a length, and add date exceptions for the days you are closed. One recurring event carries one start time, so a 6:30 AM flow and a 6:45 PM vinyasa are two events on the timetable rather than two entries on one row. That is a little more setup, and it keeps each class its own thing with its own tickets.',
            ],
            [
                'q' => 'How do students find and follow my classes?',
                'a' => 'You get one link for the whole timetable, so it works in a bio, on a flyer, and as a QR code you can print for the studio door. Students who leave an email address are yours: you see their name and address, and when you add classes they get one digest covering the batch, never one message per class and never more than one every few days. Anything else you want to say is a newsletter you write and send. A student who would rather not give an address can subscribe to your timetable as a live calendar from your schedule page: the next 90 days of classes appear in their own calendar app, and a class you move moves there too.',
            ],
            [
                'q' => 'Can students be told when a workshop goes on sale?',
                'a' => 'Yes, free on every plan. Switch on the "Notify me" card and, on the page for a class date that is not on sale yet, a student can press "Tell me when tickets go on sale" and leave just an email address, with no account. They get one email when tickets go on sale, one if you cancel it, and a reminder 48 hours before it starts, plus any change notice you choose to send. On a date that is already selling, the same list sits beside the buy button as "Tell me if anything changes". Each date of a recurring class keeps its own list, you see how many people are waiting on the event\'s Tickets panel, and it does not count against your newsletter allowance.',
            ],
            [
                'q' => 'Can I sell class passes and drop-ins?',
                'a' => 'Yes. Take the money through your own Stripe or PayPal account, or as cash at the desk, a payment link, Invoice Ninja, or Payfast if you charge in rand. Putting a price on a drop-in needs the Pro plan at '.plan_price($proMonthly).' a month, and so do passes: alongside a single drop-in you can sell a visit pass with a set number of visits, a membership that is unlimited until it expires, a festival pass good for each covered class once, or a season pass covering every occurrence of one recurring class. Set how long the pass lasts, whether it covers the whole schedule, one sub-schedule or named classes, and how many people it admits at each class. Event Schedule charges zero platform fees whatever the plan, so past the processor\'s own fee the money is yours.',
            ],
            [
                'q' => 'What happens when somebody cancels at the last minute?',
                'a' => 'You set a cancellation deadline on the pass, measured in hours before the class starts. Cancel before it and the visit goes back on the pass. After it, your choice applies: either the booking can still be cancelled but the visit stays spent, which releases the mat without giving a no-show a free credit, or cancelling is not allowed at all. You can also cap how many mats pass holders may reserve in advance per date, so a walk-up can still get in.',
            ],
            [
                'q' => 'Can I refund a drop-in or a class pass?',
                'a' => 'Yes, on Pro, from the Sales page, whether you called a class off or a student is moving away. A Stripe or PayPal payment goes back through the provider, in full or in part, and a partial refund leaves the booking or the pass valid. One paid in cash, through a payment link, Payfast or Invoice Ninja is marked as refunded instead, which records it without moving any money. Event Schedule does not email the student about a refund, so that message is yours to send.',
            ],
            [
                'q' => 'Can students book a one-to-one with me?',
                'a' => 'Yes, and one appointment type is free. Appointment types are separate from classes: give one a length, a start-time interval, the hours you are open each week, buffers before and after, how much notice you need and how far ahead people may book, then students pick a slot on your public booking page. Each type says whether it happens in the studio, online or by phone, can require your approval before it is confirmed, and can be free or paid by Stripe, a payment link or cash. Pro lifts the one-type limit, so a private session, an assessment and a beginners consultation can sit side by side. Your classes already count as busy time, so nobody books a session on top of one.',
            ],
            [
                'q' => 'Can my other teachers log in?',
                'a' => 'The free plan is one login. Multiple team members, capped at five, are an Enterprise feature at '.plan_price($entMonthly).' a month, as is the availability tab that tracks which team members are around on which days. If you only need the timetable to say who is teaching, put the instructor in the class itself and stay on the free plan.',
            ],
        ];

        $dotSections = [
            ['top', 'The flow'],
            ['unit', 'Not a night'],
            ['week', 'The timetable'],
            ['pass', 'The card'],
            ['kinds', 'Four passes'],
            ['door', 'The deadline'],
            ['onetoone', 'One-to-ones'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Start'],
        ];
    @endphp

    @php
        $fyArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $fyDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $fyDays = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
    @endphp

    <div id="fy">

        {{-- The sky. Hidden unless scroll timelines exist; the page's own gradient is the default. --}}
        <div class="fy-skybox" aria-hidden="true"><div class="fy-sky"></div></div>

        <nav class="fy-dial es-dotnav" aria-label="Page sections">
            <ul>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li>
                        <a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}">
                            <span aria-hidden="true">{{ $sectionLabel }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: first light, and one breath                         -->
        <!-- ============================================================ -->
        <section id="top" class="fy-hero" style="scroll-margin-top: 4.5rem;">
            <div class="fy-hero-sun" aria-hidden="true"></div>
            <div class="fy-wrap">
                <div class="fy-stage">
                    <!-- The breath: in for four, hold for two, out for six -->
                    <div class="fy-breath es-fade-up es-d-1" aria-hidden="true">
                        <i class="fy-ring" style="--in: 0%;"></i>
                        <i class="fy-ring" style="--in: 9.5%; --o: 0.6;"></i>
                        <i class="fy-ring" style="--in: 19%; --o: 0.35;"></i>
                        <div class="fy-orb"></div>
                    </div>

                    <h1 class="fy-h1">
                        <x-marketing.hero-eyebrow class="fy-eyebrow es-fade-up es-d-1">Yoga &amp; fitness class schedule</x-marketing.hero-eyebrow>
                        <span class="es-fade-up es-d-1">A studio week is a sequence.</span>
                        <span class="es-fade-up es-d-2">What you sell is a <em>visit</em>.</span>
                    </h1>
                    <p class="fy-lede es-fade-up es-d-3">
                        Set each yoga or fitness class up once with the days it runs and one start time, then sell drop-ins, ten-class cards and memberships from a single link. Reach your students directly, with zero platform fees.
                    </p>
                    <div class="fy-cta es-fade-up es-d-4">
                        <a href="#week" class="fy-btn fy-btn-quiet">
                            See the week
                            {!! $fyDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="fy-btn fy-paced">
                            Create your schedule
                            {!! $fyArrow !!}
                        </a>
                    </div>
                    <div class="fy-breath-cap es-fade-up es-d-4" aria-hidden="true">
                        <div class="fy-breath-words"><span>Breathe in</span><span>Hold</span><span>Breathe out</span></div>
                        <span class="fy-breath-count">in 4 &middot; hold 2 &middot; out 6</span>
                    </div>
                </div>

                <!-- One recurring class, expanded across a week. Two strips,
                     because two start times are two events. -->
                <div class="fy-card fy-two fy-dawn es-fade-up es-d-5">
                    <p class="fy-k">two events, seven classes</p>
                    <div class="fy-two-rows">
                        <div>
                            <div class="fy-two-top">
                                <p class="fy-two-name">Sunrise Flow</p>
                                <span class="fy-two-time">6:30 AM &middot; 60 min</span>
                            </div>
                            <div class="fy-days" aria-hidden="true">
                                @foreach ([1, 1, 1, 1, 1, 0, 0] as $dayIndex => $on)
                                    <i @class(['is-on' => $on])>{{ $fyDays[$dayIndex] }}</i>
                                @endforeach
                            </div>
                            <p class="fy-two-note">Monday to Friday. Dashed cells are days it does not run.</p>
                        </div>
                        <div>
                            <div class="fy-two-top">
                                <p class="fy-two-name">Power Vinyasa</p>
                                <span class="fy-two-time">6:45 PM &middot; 75 min</span>
                            </div>
                            <div class="fy-days" aria-hidden="true" style="--c: var(--fy-clay);">
                                @foreach ([0, 1, 0, 1, 0, 0, 0] as $dayIndex => $on)
                                    <i @class(['is-on' => $on])>{{ $fyDays[$dayIndex] }}</i>
                                @endforeach
                            </div>
                            <p class="fy-two-note">A different start time is a different class, and a separate event.</p>
                        </div>
                    </div>
                    <p class="fy-two-foot">
                        Two recurring events, seven classes a week, and nothing retyped. Move a start time once and every class in the pattern follows.
                    </p>
                </div>

                <!-- Disciplines -->
                <ul class="fy-chips es-fade-up es-d-5">
                    @foreach (['Vinyasa', 'Pilates', 'HIIT', 'Barre', 'Spin', 'Bootcamp', 'Strength', 'Mobility', 'Sound bath', 'Prenatal', 'CrossFit', 'Meditation'] as $chip)
                        <li>{{ $chip }}</li>
                    @endforeach
                </ul>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. Not a night: the unit is a visit                          -->
        <!-- ============================================================ -->
        <section id="unit" class="fy-section fy-dawn">
            <div class="fy-wrap">
                <div class="fy-head fy-head-c">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">07:00</span> the unit</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Nobody buys a <em>Tuesday</em>.
                    </h2>
                    <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Event tools are built around a night: one date, one ticket, one door. A studio does not work that way. Students buy ten of something and spend them one class at a time, which is a different object with different rules.
                    </p>
                </div>

                <div class="fy-card fy-trio" data-reveal="panel">
                    <div class="fy-trio-item">
                        <p class="fy-k">the class</p>
                        <h3>
                            <span class="fy-num">1</span> recurring event
                        </h3>
                        <p>The days of the week, one start time, a length, and date exceptions for the days you are closed. Entering a whole term one class at a time is a hundred chances to mistype a time.</p>
                    </div>
                    <div class="fy-trio-item">
                        <p class="fy-k">the visit</p>
                        <h3>
                            <span class="fy-num">1</span> off the card
                        </h3>
                        <p>A pass is spent a visit at a time, and every use is recorded against the class it was spent on. You can see which sessions your card holders actually turn up to.</p>
                    </div>
                    <div class="fy-trio-item">
                        <p class="fy-k">the money</p>
                        <h3><span class="fy-num">{{ plan_price(0) }}</span> taken</h3>
                        <p>Payments land in your own Stripe or PayPal account, or in the till as cash. Event Schedule takes no cut of a drop-in, a pass or a membership, on any plan.</p>
                    </div>
                </div>

                <p class="fy-after" data-reveal>
                    Set the sequence, then price the visit.
                    <a href="#week" class="fy-link">
                        Start with the week
                        {!! $fyDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The class board: a real table, drawn on the day           -->
        <!-- ============================================================ -->
        <section id="week" class="fy-section fy-morning">
            <div class="fy-wrap">
                <div class="fy-head fy-head-c">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">08:00</span> the timetable</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Seven classes. Seven <em>events</em>.
                    </h2>
                    <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Each row below is one recurring event: its own start time, its own length, its own days. That is genuinely how it is stored, which is why the whole week is seven things to keep, not two hundred.
                    </p>
                </div>

                <div class="fy-board" data-reveal>
                    <div class="fy-card fy-board-in">
                        <table class="fy-tt" role="table">
                            <caption>
                                A studio week, drawn on a clock from 6:00 AM to 9:00 PM. Bar position is the start time, bar length is the class length.
                            </caption>
                            <thead role="rowgroup">
                                <tr role="row">
                                    <th scope="col" role="columnheader">class</th>
                                    <th scope="col" role="columnheader">starts</th>
                                    <th scope="col" role="columnheader">days</th>
                                    <th scope="col" role="columnheader" class="fy-tt-day">
                                        <div aria-hidden="true">
                                            <div class="fy-arc"><i class="fy-arc-sun"></i></div>
                                            <div class="fy-hours"><span>6a</span><span>9a</span><span>12p</span><span>3p</span><span>6p</span><span>9p</span></div>
                                        </div>
                                        <span class="sr-only">Where in the day the class falls</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody role="rowgroup">
                                @foreach ($timetable as [$cName, $cTime, $cStart, $cLen, $cDays, $cSoft])
                                    @php
                                        $left = round(($cStart - $railFrom) / $railSpan * 100, 2);
                                        $width = round($cLen / $railSpan * 100, 2);
                                        $dayList = collect($cDays)->filter()->keys()->map(fn ($k) => $dayNames[$k])->implode(', ');
                                    @endphp
                                    <tr role="row" @class(['is-draft' => $cSoft])>
                                        <th scope="row" role="rowheader" class="fy-tt-name">{{ $cName }}<small>{{ $cLen }} min</small></th>
                                        <td role="cell" class="fy-tt-time">{{ $cTime }}</td>
                                        <td role="cell" style="position: relative;">
                                            <span class="fy-pips" aria-hidden="true">
                                                @foreach ($cDays as $on)
                                                    <i @class(['is-on' => $on])></i>
                                                @endforeach
                                            </span>
                                            <span class="sr-only">{{ $dayList }}</span>
                                        </td>
                                        <td role="cell" class="fy-tt-day">
                                            <div class="fy-rail" aria-hidden="true">
                                                <div class="fy-bar" style="left: {{ $left }}%; width: {{ $width }}%;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <p class="fy-board-note" data-reveal>
                    The pale bar is a class held as a draft while you decide whether to keep it. A draft is how you hide a class: sub-schedules colour and group your timetable, but they cannot hide anything.
                </p>

                <div class="fy-three" data-reveal-group="110">
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>The days it runs</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Tick the days of the week and set the start time. Monday to Friday is five classes a week without entering five classes.</p>
                    </div>
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>The days you are closed</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Date exceptions take single dates out, so a public holiday or a week away does not need the class rebuilding. They can add a one-off date in, too.</p>
                    </div>
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>The end</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>A finishing date, or a number of sessions. This is what turns a six-week beginners course into a course instead of a Tuesday night that never stops.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The card: punched, on the studio floor                    -->
        <!-- ============================================================ -->
        <section id="pass" class="fy-noon" style="scroll-margin-top: 4.5rem; padding-block: clamp(1.5rem, 4vw, 3rem);">
            <div class="fy-floorband">
                <div class="fy-wrap fy-pass-grid">
                    <div class="fy-head">
                        <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">10:00</span> the card</p>
                        <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Ten visits, and the <em>count</em> looks after itself.
                        </h2>
                        <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                            A pass is a ticket type with a different job. It carries its own allowance, its own expiry, its own reach across your timetable and its own rules at the door, and the tally is kept for you instead of in a shoebox by the till.
                        </p>
                        <ul class="fy-points" data-reveal style="--reveal-delay: 0.24s;">
                            <li>
                                <span>Give the pass a life in days from purchase, or leave it blank and it never expires.</span>
                            </li>
                            <li>
                                <span>Set how many people it admits at each class, so a card that brings a friend is two through the door on one code.</span>
                            </li>
                            <li>
                                <span>Let holders reserve a mat for specific dates ahead of time, or keep the pass scan-at-the-door only.</span>
                            </li>
                            <li>
                                <span>Passes are a Pro feature, as is charging for a drop-in. The timetable they run against, and free registration on it, are not.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- The class card: three visits punched through to the floor behind it -->
                    <div class="fy-pcard-wrap" data-reveal="zoom">
                        <div class="fy-pcard">
                            <div class="fy-pcard-top">
                                <div>
                                    <p class="fy-k">riverbend studio</p>
                                    <h3>Ten-class card</h3>
                                </div>
                                <span class="fy-tag">Pro</span>
                            </div>
                            <div class="fy-pcard-strip" aria-hidden="true">
                                <div class="fy-pcard-holes">
                                    @for ($visit = 0; $visit < 10; $visit++)
                                        <i @class(['is-out' => $visit < 3])>{{ $visit + 1 }}</i>
                                    @endfor
                                </div>
                            </div>
                            <div class="fy-pcard-body">
                                <p class="fy-pcard-count">
                                    <span>3 spent</span> &middot; 7 visits left
                                </p>
                                <dl>
                                    <div>
                                        <dt>Valid for</dt>
                                        <dd>90 days from purchase</dd>
                                    </div>
                                    <div>
                                        <dt>Covers</dt>
                                        <dd>Every class on the schedule</dd>
                                    </div>
                                    <div>
                                        <dt>Admits</dt>
                                        <dd>1 per class, holder only</dd>
                                    </div>
                                    <div>
                                        <dt>Advance mats</dt>
                                        <dd>2 per date, all cards</dd>
                                    </div>
                                </dl>
                                <p class="fy-pcard-note">
                                    Every card carries a QR code. Scan it at the door from any phone, and the visit comes off the count.
                                </p>
                            </div>
                        </div>
                        <div class="fy-keytag" aria-hidden="true">
                            <b>{{ $passKinds[1][0] }}</b>
                            <span>{{ $passKinds[1][2] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Four passes: a real record, so a real table               -->
        <!-- ============================================================ -->
        <section id="kinds" class="fy-section fy-noon">
            <div class="fy-wrap">
                <div class="fy-head fy-head-c">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">12:00</span> four shapes</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Pick how the pass is <em>spent</em>.
                    </h2>
                    <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                        These are the four options as the app names them, and what each one turns into on a studio noticeboard. All four are on the Pro plan.
                    </p>
                </div>

                <div class="fy-kinds" data-reveal>
                    <div class="fy-card fy-kinds-in">
                        <table class="fy-kt" role="table">
                            <caption>The four class pass types, how each is spent, and the studio equivalent</caption>
                            <thead role="rowgroup">
                                <tr role="row">
                                    <th scope="col" role="columnheader">pass type</th>
                                    <th scope="col" role="columnheader">how it is spent</th>
                                    <th scope="col" role="columnheader">in a studio</th>
                                </tr>
                            </thead>
                            <tbody role="rowgroup">
                                @foreach ($passKinds as [$pName, $pSpend, $pStudio, $pField])
                                    <tr role="row">
                                        <th scope="row" role="rowheader">
                                            {{ $pName }}
                                            <span>{{ $pField }}</span>
                                        </th>
                                        <td role="cell">{{ $pSpend }}</td>
                                        <td role="cell">{{ $pStudio }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="fy-kinds-note">
                            Whatever the shape, you choose its reach: every class on the schedule including ones you add later, every class in one sub-schedule, or a named handful. A season pass is the exception and belongs to a single recurring class.
                        </p>
                    </div>
                </div>

                <p class="fy-pay" data-reveal>
                    Connect Stripe or PayPal and sell straight from the timetable, or take cash at the desk. Event Schedule charges zero platform fees, so past the processor's own fee the money is yours.
                    <a href="{{ marketing_url('/features/passes') }}" class="fy-link">How passes work {!! $fyArrow !!}</a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. The deadline: two sides of one rule, and the mats         -->
        <!-- ============================================================ -->
        <section id="door" class="fy-section fy-afternoon">
            <div class="fy-wrap">
                <div class="fy-head fy-head-c">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">14:00</span> the deadline</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The mat that goes <em>empty</em>.
                    </h2>
                    <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Set a cancellation deadline on the pass, in hours before the class starts. One rule, two outcomes, and you decide which side the late cancel falls on.
                    </p>
                </div>

                <div class="fy-duplex" data-reveal-group="140">
                    <div class="fy-card fy-side" data-reveal>
                        <div class="fy-side-top">
                            <p class="fy-k">before the deadline</p>
                            <span class="fy-tag fy-tag-warm">Pro</span>
                        </div>
                        <h3>The visit goes back on.</h3>
                        <p>Cancel in time and the credit returns to the pass, the mat is released, and nobody has to email you about it.</p>
                        <div class="fy-ledger" aria-hidden="true">
                            <div>
                                <span>Pass balance</span>
                                <span class="fy-good">7 visits left</span>
                            </div>
                        </div>
                    </div>
                    <div class="fy-card fy-side" data-reveal>
                        <div class="fy-side-top">
                            <p class="fy-k">after the deadline</p>
                            <span class="fy-tag fy-tag-warm">Pro</span>
                        </div>
                        <h3>Your call: spent, or locked.</h3>
                        <p>Either the booking can still be cancelled but the visit stays spent, which frees the mat without handing a no-show a free credit, or cancelling stops being possible and the booking stands.</p>
                        <div class="fy-ledger" aria-hidden="true">
                            <div>
                                <span>Cancel late, keep the visit spent</span>
                                <span>forfeit</span>
                            </div>
                            <div>
                                <span>No cancelling after the cut-off</span>
                                <span>block</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fy-card fy-fit" data-reveal>
                    <div class="fy-fit-copy">
                        <div class="fy-side-top">
                            <h3>How many mats fit</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>For a class you are not charging for, turn on registration and give it a capacity. The remaining places are counted per date, so this Wednesday filling up does not close next Wednesday. Pass holders get their own separate cap on how many of a date's places they may reserve between them.</p>
                    </div>
                    <!-- The studio floor, one row per date: sixteen mats, counted on their own each week -->
                    <div class="fy-floors" aria-hidden="true">
                        @foreach ([['Wed 15', 'full', true], ['Wed 22', '4 places left', false], ['Wed 29', '12 places left', false]] as $floorIndex => [$dLabel, $dState, $dFull])
                            @php $fyTaken = $dFull ? 16 : 16 - (int) $dState; @endphp
                            <div class="fy-floor-row">
                                <span class="fy-floor-date">{{ $dLabel }}</span>
                                <div class="fy-floor">
                                    @for ($mat = 0; $mat < 16; $mat++)
                                        <i @class(['fy-mat', 'is-taken' => $mat < $fyTaken && ! ($floorIndex === 1 && $mat === 11), 'is-you' => $floorIndex === 1 && $mat === 11]) style="--i: {{ $mat }};"></i>
                                    @endfor
                                </div>
                                <span @class(['fy-floor-state', 'is-full' => $dFull, 'fy-paced' => $floorIndex === 1])>{{ $dState }}</span>
                            </div>
                        @endforeach
                        <div class="fy-floor-key">
                            <span><i class="is-taken"></i> taken</span>
                            <span><i class="is-you"></i> yours</span>
                            <span><i></i> free</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. One-to-ones: a single mat, and a time                     -->
        <!-- ============================================================ -->
        <section id="onetoone" class="fy-section fy-afternoon">
            <div class="fy-wrap">
                <div class="fy-solo">
                    <div class="fy-head">
                        <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">15:30</span> one-to-ones</p>
                        <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                            A private session is not a <em>class</em>.
                        </h2>
                        <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                            So it is not built like one. Appointment types are their own thing: you describe when you are free, and students pick a slot on your public booking page.
                        </p>
                    </div>
                    <div class="fy-card fy-slotcard" aria-hidden="true" data-reveal="zoom">
                        <div class="fy-onemat"><span>1:1</span></div>
                        <div class="fy-slots">
                            <p>Thursday</p>
                            <div class="fy-slot"><span>4:00 PM</span><small>free</small></div>
                            <div class="fy-slot is-picked"><span>5:15 PM</span><small>yours</small></div>
                            <div class="fy-slot is-busy"><span>6:45 PM</span><small>Power Vinyasa</small></div>
                        </div>
                    </div>
                </div>

                <div class="fy-three" data-reveal-group="110">
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>The hours you teach</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Weekly hours per day, and per-date overrides for the days that differ. A public holiday or a workshop weekend is a change to one date, not to the pattern.</p>
                    </div>
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>Room to breathe</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>A length, the interval slots start on, buffers before and after, the notice you need, and how far ahead people may book. Nobody lands a 6:00 AM assessment at midnight.</p>
                    </div>
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>Studio, screen or phone</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Each type says where it happens, can ask for your approval before it is confirmed, and can be free or paid. If a time has to move, the booking moves with its payment and its private link rather than being cancelled and rebuilt.</p>
                    </div>
                </div>

                <p class="fy-solo-after" data-reveal>
                    Your classes count as busy time. The slot list already knows every recurring class on your timetable, so nobody books a one-to-one on top of Thursday's vinyasa. One appointment type is free; Pro is what lets several run side by side.
                    <a href="{{ marketing_url('/features/appointments') }}" class="fy-link">How appointments work {!! $fyArrow !!}</a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Everything else                                           -->
        <!-- ============================================================ -->
        <section id="rest" class="fy-section fy-afternoon">
            <div class="fy-wrap">
                <div class="fy-head fy-head-c">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">17:00</span> everything else</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Between the first mat and the last.
                    </h2>
                </div>

                <div class="fy-rest" data-reveal-group="90">
                    <!-- 1 -->
                    <div class="fy-card fy-mini is-wide" data-reveal>
                        <div class="fy-mini-top">
                            <h3>Write to the people who already come</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Students follow your schedule and you email them: the new term, a cover teacher, a workshop with places left. You compose it and you send it, so nothing goes out that you did not write. Open and click rates afterwards tell you whether it landed.</p>
                        <p>The numbers worth knowing first: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.</p>
                    </div>
                    <!-- 2 -->
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>A code for the door</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Download your schedule's QR code and put it on the studio door, the mat rack, the back of a card. A phone camera is all it takes to follow you, and it costs nothing on any plan.</p>
                    </div>
                    <!-- 3 -->
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>Strands, not silos</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Sub-schedules group and colour your timetable, so yoga, strength and workshops read apart at a glance on one link. They organise; they do not hide. Hiding a class is a draft.</p>
                    </div>
                    <!-- 4 -->
                    <div class="fy-card fy-mini is-wide" data-reveal>
                        <div class="fy-mini-top">
                            <h3>On the site you already have, and the calendar you already use</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Embed the timetable on your own site so the week lives where people look you up, and sync two ways with Google, Outlook or CalDAV so your teaching hours and your life are one calendar. Worth knowing: a recurring class syncs across as a single entry, not as a repeating one. To see every class date in your calendar app, subscribe to the schedule's feed instead, which unrolls the next 90 days one date at a time. Students can take the same feed from your schedule page, so the timetable sits in their calendar and keeps up when a class moves. Any single class date also downloads as an .ics file.</p>
                        <p>
                            Teaching online as well? Mark the class as an online event and paste the link to wherever you are streaming it.
                            <a href="{{ marketing_url('/features/online-events') }}">How online events work</a>
                        </p>
                    </div>
                    <!-- 5 -->
                    <div class="fy-card fy-mini" data-reveal>
                        <div class="fy-mini-top">
                            <h3>The teaching team</h3>
                            <span class="fy-tag fy-tag-deep">Enterprise</span>
                        </div>
                        <p>The free plan is one login, and there is no way around that. Multiple team members, capped at five, plus the availability tab that tracks who is around, are Enterprise. Naming the instructor on the class itself needs neither.</p>
                    </div>
                    <!-- 6 -->
                    <div class="fy-card fy-mini is-wide" data-reveal>
                        <div class="fy-mini-top">
                            <h3>Which classes people actually look at</h3>
                            <span class="fy-tag">Free</span>
                        </div>
                        <p>Built-in analytics rank your classes by views, show the devices people are on and where the traffic came from, and, once you are selling, which classes brought the money in. That is what they measure, and nothing more.</p>
                        <p>
                            When a class fills, a waitlist can take names and tell them the moment a place frees up. Free on a class you are not charging for, and on a paid ticket it is
                            <span class="fy-tag fy-tag-warm fy-pill-inline">Pro</span>.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Perfect for: six mats                                     -->
        <!-- ============================================================ -->
        <section id="who" class="fy-section fy-dusk">
            <div class="fy-wrap">
                <div class="fy-head fy-head-c">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">18:00</span> perfect for</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Perfect for all types of <em>fitness professionals</em>
                    </h2>
                    <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                        One mat in a hall or forty in a studio, a week is still a sequence.
                    </p>
                </div>

                @php
                    $fyWho = [
                        ['Yoga Teachers', 'Flows, workshops and retreats on one link. Sell a ten-class card that covers every class you teach, and let it expire when you say.', 'for-yoga-teachers', '#b9c9b3'],
                        ['Personal Trainers', 'Publish bookable appointment types with your real weekly hours, buffers and notice period, and let clients pick a slot themselves.', 'for-personal-trainers', '#e8c3ad'],
                        ['Pilates Instructors', 'Mat and reformer on separate strands of one timetable, with a per-date cap so a six-apparatus studio never oversells a Tuesday.', 'for-pilates-instructors', '#e6dcc6'],
                        ['CrossFit Coaches', 'A WOD at 6:00 AM and a WOD at 6:00 PM as two recurring classes, plus a membership that is simply unlimited until it expires.', 'for-crossfit-coaches', '#efd0a9'],
                        ['Group Fitness Instructors', 'Spin here, Zumba there, bootcamp in the park. Each venue is a class of its own, all on one link and one synced calendar.', 'for-group-fitness-instructors', '#a9bfa6'],
                        ['Meditation Guides', 'Sound baths and mindfulness courses. A festival pass covers each session of a six-week series once, so the whole run is paid for up front.', 'for-meditation-guides', '#d5d8cc'],
                    ];
                @endphp

                <div class="fy-mats" data-reveal-group="80">
                    @foreach ($fyWho as [$fyName, $fyDesc, $fySlug, $fyTone])
                        @php $fyPost = get_sub_audience_blog($fySlug); @endphp
                        <article class="fy-rollmat" style="--m: {{ $fyTone }};" data-reveal>
                            <h3>{{ $fyName }}</h3>
                            <p>{{ $fyDesc }}</p>
                            @if ($fyPost)
                                <a href="{{ blog_url('/' . $fyPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $fyName }}">
                                    <span>Learn more {!! $fyArrow !!}</span>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Three steps: sunrise, noon, sunset                       -->
        <!-- ============================================================ -->
        <section class="fy-section fy-dusk" style="padding-top: 0;">
            <div class="fy-wrap">
                <div class="fy-head fy-head-c">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">18:45</span> three steps</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three steps to a full class
                    </h2>
                </div>

                <div class="fy-steps" data-reveal-group="140">
                    @foreach ([
                        ['01', 'Set the week', 'Create each class once as a recurring event: the days it runs, one start time, a length, and date exceptions for the days you are closed.'],
                        ['02', 'Price the visit', 'Add a drop-in and a pass. Choose how the pass is spent, how long it lasts, what it covers, and how many it admits per class.'],
                        ['03', 'Share one link', 'Put it in your bio, embed the timetable on your site, print the QR code for the door, and write to the students who follow.'],
                    ] as $stepIndex => [$stepNum, $stepTitle, $stepBody])
                        <div class="fy-step" data-reveal>
                            <div class="fy-step-sky" aria-hidden="true"><i style="--x: {{ [24, 50, 76][$stepIndex] }}%; --y: {{ [100, 40, 100][$stepIndex] }}%;"></i></div>
                            <div class="fy-step-no">{{ $stepNum }}</div>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. Key features                                             -->
        <!-- ============================================================ -->
        <section class="fy-section fy-dusk" style="padding-top: 0;">
            <div class="fy-wrap fy-two-col">
                <div>
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">19:15</span> on the board</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                    <p class="fy-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="fy-link">
                            See all features
                            {!! $fyArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $fyFeatures = [
                        ['Recurring Events', 'One class, the days it runs, and date exceptions', marketing_url('/features/recurring-events')],
                        ['Ticketing', 'Drop-ins, class passes, QR check-in, zero platform fees', marketing_url('/features/ticketing')],
                        ['Appointments', 'Bookable one-to-ones on your own public booking page', marketing_url('/features/appointments')],
                        ['Newsletters', 'Email the students who follow you, with open rates', marketing_url('/features/newsletters')],
                        ['Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', marketing_url('/features/calendar-sync')],
                    ];
                @endphp
                <div class="fy-card fy-list" data-reveal>
                    @foreach ($fyFeatures as [$fyFeatName, $fyFeatDesc, $fyFeatUrl])
                        <a href="{{ $fyFeatUrl }}">
                            <span>
                                <strong>{{ $fyFeatName }}</strong>
                                <small>{{ $fyFeatDesc }}</small>
                            </span>
                            {!! $fyArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="fy-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 12. Related pages                                            -->
        <!-- ============================================================ -->
        <section class="fy-section fy-dusk">
            <div class="fy-wrap">
                <div class="fy-onward-head">
                    <div>
                        <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">19:45</span> next door</p>
                        <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="fy-link" data-reveal>
                        See all use cases
                        {!! $fyArrow !!}
                    </a>
                </div>
                <div class="fy-onward" data-reveal-group="80">
                    @foreach ([['/for-workshop-instructors', 'Workshop Instructors'], ['/for-dance-groups', 'Dance Groups'], ['/for-online-classes', 'Online Classes'], ['/for-community-centers', 'Community Centers']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="fy-card" data-reveal>
                            <strong>For {{ $relName }}</strong>
                            <span>
                                Read more
                                {!! $fyArrow !!}
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

        <section id="faq" class="fy-section fy-dusk" style="padding-top: 0;">
            <div class="fy-wrap fy-faq-grid">
                <div class="fy-faq-head">
                    <p class="fy-kick" data-reveal><span class="fy-clock" aria-hidden="true">20:15</span> questions</p>
                    <h2 class="fy-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="fy-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Everything fitness and yoga instructors ask before they move a timetable across.
                    </p>
                </div>

                <div class="fy-card fy-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq">
                            <summary>
                                <span class="fy-qa-no" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
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
        <!-- 14. Evening: the last mat rolled up                          -->
        <!-- ============================================================ -->
        <section id="claim" class="fy-evening">
            <div class="fy-night" data-reveal="panel">
                <p class="fy-kick"><span class="fy-clock" aria-hidden="true">21:00</span> free forever</p>
                <h2 class="fy-h2">
                    Your classes. Your students. <em>No middleman.</em>
                </h2>
                <p class="fy-night-sub">
                    Publishing the timetable is free forever, and so is taking unlimited free bookings and registrations. Charging for a drop-in, passes and the check-in dashboard are {{ plan_price($proMonthly) }} a month, and nothing is taken off the door.
                </p>

                <div class="fy-claimbox">
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div dir="ltr" class="es-claim fy-claim">
                        <input id="es-claim-input" type="text" placeholder="your-studio" autocomplete="off" spellcheck="false" maxlength="30">
                        <span>.eventschedule.com</span>
                    </div>
                    <a href="{{ app_url('/sign_up?type=talent') }}" class="fy-btn">
                        Get Started Free
                        {!! $fyArrow !!}
                    </a>
                </div>
                <p class="fy-night-note">No credit card required</p>
            </div>
        </section>

        <div class="fy-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
