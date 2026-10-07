<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Webinars | Registration & Join Links</x-slot>
    <x-slot name="description">Run webinars with free registration or paid tickets at zero platform fees, and send the join link only to registrants. Works with Zoom, Meet or any link.</x-slot>
    <x-slot name="breadcrumbTitle">For Webinars</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typeface, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('IBM Plex Sans') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Webinars"
        description="Publish a webinar on a public schedule, take free registrations or sell tickets with zero platform fees, and hand the join link only to the people who registered. One Event URL field, so any meeting or streaming platform works."
        audience="Webinar Hosts"
        keywords="webinar hosting, webinar scheduling, webinar registration, paid webinars, recurring webinar series" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to run a webinar with Event Schedule",
        "description": "Publish the session, take the registrations, and send the join link only to the people who registered.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Paste the link",
                "text": "Create the webinar and paste your meeting or streaming link into the Event URL field. Add a running order if the session has segments."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Open registration",
                "text": "Turn on free registration with a capacity limit, or add ticket types and sell through your own Stripe or PayPal account with zero platform fees."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Go on air",
                "text": "Everyone who registered gets their own registration page carrying the join link. Swap the link and Event Schedule offers to email them all before it saves (on eventschedule.com, once your schedule has its own email settings)."
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
           For-webinars "Control Room" styles. The page is the gallery of
           a television studio: a wall of monitors with the programme
           ringed red and the preview ringed green, lower-third captions
           for the headings, a running order, a prompter, a clock. A
           webinar is a small broadcast and its host is the director.

           Everything is scoped under #wb. Glass is dark in both modes
           (literal colours); the desk and walls follow the theme: house
           lights up by day, down by night. The shared es-* reveal system
           (marketing.css, marketing-home.js) drives the entrances.
           ============================================================== */

        #wb {
            --wb-desk: #e9ebee;
            --wb-desk-2: #dfe2e6;
            --wb-panel: #f7f8f9;
            --wb-ink: #14161a;
            --wb-ink-2: #3a4049;
            --wb-ink-3: #555c66;
            --wb-line: rgba(20, 22, 26, 0.14);
            --wb-line-2: rgba(20, 22, 26, 0.3);
            --wb-red-ink: #b81a11;
            --wb-ok: #1c8a35;
            --wb-l3-bg: #14161a;
            --wb-l3-fg: #f4f5f7;
            --wb-l3-hi: #ffb020;
            --wb-cap-1: #ffffff;
            --wb-cap-2: #d3d7dd;
            --wb-cap-ink: #14161a;
            --wb-cap-edge: #17191d;
            --wb-sans: 'IBM Plex Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --wb-mono: ui-monospace, 'SF Mono', SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            position: relative;
            background: var(--wb-desk);
            color: var(--wb-ink);
            font-family: var(--wb-sans);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #wb {
            --wb-desk: #0d0f12;
            --wb-desk-2: #111418;
            --wb-panel: #171a1f;
            --wb-ink: #eef0f3;
            --wb-ink-2: #c3c8d0;
            --wb-ink-3: #9aa2ae;
            --wb-line: rgba(238, 240, 243, 0.14);
            --wb-line-2: rgba(238, 240, 243, 0.3);
            --wb-red-ink: #ff6a60;
            --wb-ok: #32d74b;
            --wb-l3-bg: #eef0f3;
            --wb-l3-fg: #14161a;
            --wb-l3-hi: #b81a11;
            --wb-cap-1: #343942;
            --wb-cap-2: #22262c;
            --wb-cap-ink: #eef0f3;
            --wb-cap-edge: #000000;
        }

        /* The bar above takes the desk's grey, so the room starts at the top of the window. */
        body > header.sticky {
            background-color: rgba(233, 235, 238, 0.88);
            border-bottom-color: rgba(20, 22, 26, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(13, 15, 18, 0.88);
            border-bottom-color: rgba(238, 240, 243, 0.14);
        }

        #wb ::selection { background: #ff3b30; color: #14161a; }
        #wb a:focus-visible,
        #wb summary:focus-visible,
        #wb input:focus-visible {
            outline: 3px solid #ffb020;
            outline-offset: 3px;
        }

        .wb-wrap { width: min(100% - 2.5rem, 78rem); margin-inline: auto; }
        .wb-sec { position: relative; padding-block: clamp(4rem, 8vw, 7rem); border-top: 1px solid var(--wb-line); }
        .wb-alt { background: var(--wb-desk-2); }
        .wb-mono {
            font-family: var(--wb-mono);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .wb-sub { max-width: 44rem; color: var(--wb-ink-2); font-size: clamp(1.05rem, 1.5vw, 1.2rem); }
        .wb-hi { font-style: normal; color: var(--wb-l3-hi); }
        #wb .wb-a {
            color: inherit;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: #ff3b30;
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }
        #wb .wb-a:hover { text-decoration-thickness: 3px; }
        .wb-next {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            margin-inline-start: 0.35rem;
            transition: gap 0.2s ease;
        }
        .wb-next:hover { gap: 0.75rem; }
        .wb-next svg, .wb-btn svg, .wb-shot > svg { width: 1.05rem; height: 1.05rem; flex: none; }

        /* Plan tags: a small engraved label with a status LED. The colour never carries the text. */
        .wb-tier {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.24rem 0.5rem 0.2rem;
            border: 1px solid var(--wb-line-2);
            border-radius: 0.25rem;
            font-family: var(--wb-mono);
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            line-height: 1;
            color: var(--wb-ink-2);
            white-space: nowrap;
        }
        .wb-tier::before { content: ""; width: 0.45rem; aspect-ratio: 1; border-radius: 50%; background: #32d74b; box-shadow: 0 0 0.4rem #32d74b; }
        .wb-tier-pro::before { background: #ffb020; box-shadow: 0 0 0.4rem #ffb020; }
        .wb-tier-ent::before { background: #35c9e6; box-shadow: 0 0 0.4rem #35c9e6; }
        .wb-glass .wb-tier { color: #c3c8d0; border-color: rgba(238, 240, 243, 0.32); }

        /* ---------------------------------------------------------------
           Illuminated keys: the vision mixer's buttons
           --------------------------------------------------------------- */
        .wb-key {
            display: grid;
            place-items: center;
            flex: none;
            width: 3.25rem;
            aspect-ratio: 1;
            border-radius: 0.5rem;
            background-color: var(--k, #f1f3f5);
            background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.5), rgba(255, 255, 255, 0) 46%, rgba(0, 0, 0, 0.1));
            color: #14161a;
            font-family: var(--wb-mono);
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.15;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.65),
                inset 0 -3px 0 rgba(0, 0, 0, 0.2),
                0 0 0 3px #17191d,
                0 0.28rem 0 #17191d,
                0 0 1.2rem -0.2rem var(--k, transparent);
        }
        .wb-key-red { --k: #ff3b30; }
        .wb-key-green { --k: #32d74b; }
        .wb-key-amber { --k: #ffb020; }
        .wb-key-cyan { --k: #35c9e6; }

        .wb-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.95rem 1.4rem 0.9rem;
            border-radius: 0.5rem;
            background-color: #ff3b30;
            background-image: linear-gradient(180deg, #ff6a5e 0%, #ff3b30 50%, #f2352a 100%);
            color: #14161a;
            font-weight: 700;
            font-size: 1.02rem;
            line-height: 1.15;
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.55),
                inset 0 -3px 0 rgba(0, 0, 0, 0.2),
                0 0 0 3px #17191d,
                0 0.34rem 0 #17191d,
                0 0 1.7rem -0.3rem rgba(255, 59, 48, 0.85);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .wb-btn:hover { transform: translateY(-1px); }
        .wb-btn:active {
            transform: translateY(0.22rem);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55), inset 0 -2px 0 rgba(0, 0, 0, 0.2), 0 0 0 3px #17191d, 0 0.1rem 0 #17191d, 0 0 2.2rem -0.2rem rgba(255, 59, 48, 1);
        }
        .wb-btn svg { transition: transform 0.18s ease; }
        .wb-btn:hover svg { transform: translateX(3px); }
        .wb-btn-2 {
            background-color: var(--wb-cap-1);
            background-image: linear-gradient(180deg, var(--wb-cap-1), var(--wb-cap-2));
            color: var(--wb-cap-ink);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), inset 0 -3px 0 rgba(0, 0, 0, 0.14), 0 0 0 3px var(--wb-cap-edge), 0 0.34rem 0 var(--wb-cap-edge);
        }
        .wb-btn-2:active { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4), inset 0 -2px 0 rgba(0, 0, 0, 0.14), 0 0 0 3px var(--wb-cap-edge), 0 0.1rem 0 var(--wb-cap-edge); }
        .wb-btn-2:hover svg { transform: translateY(3px); }

        /* ---------------------------------------------------------------
           Monitors: a bezel, the glass, and the display label under it
           --------------------------------------------------------------- */
        .wb-mon {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 0.4rem 0.4rem 0;
            border-radius: 0.55rem;
            background: #0a0b0d;
            box-shadow: 0 0 0 1px #000, inset 0 1px 0 rgba(255, 255, 255, 0.07), 0 1.4rem 2.4rem -1.4rem rgba(10, 12, 16, 0.6);
        }
        .dark .wb-mon { box-shadow: 0 0 0 1px rgba(238, 240, 243, 0.13), inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 1.4rem 2.4rem -1.4rem #000; }
        .wb-screen {
            position: relative;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            border-radius: 0.25rem;
            background-color: #101216;
            background-image: linear-gradient(118deg, rgba(255, 255, 255, 0.05) 0 32%, rgba(255, 255, 255, 0) 32.2%);
            color: #eef0f3;
            box-shadow: 0 0 0 2px #1d2026;
            transition: box-shadow 0.25s ease;
        }
        .wb-umd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.5rem 0.35rem 0.45rem;
            font-family: var(--wb-mono);
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            line-height: 1.2;
            color: #aab1bc;
        }
        .wb-umd b { font-weight: 700; color: #eef0f3; transition: color 0.25s ease; }
        .wb-pgm .wb-screen { box-shadow: 0 0 0 3px #ff3b30, 0 0 2rem -0.3rem rgba(255, 59, 48, 0.75); }
        .wb-pgm .wb-umd b { color: #ff6a60; }
        .wb-pvw .wb-screen { box-shadow: 0 0 0 3px #32d74b, 0 0 2rem -0.4rem rgba(50, 215, 75, 0.6); }
        .wb-pvw .wb-umd b { color: #5ee67a; }
        .wb-sby .wb-screen { box-shadow: 0 0 0 3px #ffb020, 0 0 2rem -0.4rem rgba(255, 176, 32, 0.55); }
        .wb-sby .wb-umd b { color: #ffc44d; }

        /* ---------------------------------------------------------------
           Lower thirds: the caption that wipes in under a speaker,
           used here as every section's heading
           --------------------------------------------------------------- */
        .wb-l3 { display: grid; justify-items: start; margin-bottom: clamp(1.75rem, 4vw, 3rem); }
        .wb-l3-tc {
            padding: 0.34rem 0.6rem 0.26rem;
            background: #ff3b30;
            color: #14161a;
        }
        .wb-l3-name {
            max-width: 100%;
            padding: 0.6rem 1.1rem 0.7rem;
            background: var(--wb-l3-bg);
            color: var(--wb-l3-fg);
            font-size: clamp(1.8rem, 4.6vw, 3.4rem);
            font-weight: 700;
            line-height: 1.07;
            letter-spacing: -0.02em;
            text-wrap: balance;
        }
        .wb-l3-title {
            padding: 0.45rem 1.1rem 0.4rem;
            background: var(--wb-panel);
            color: var(--wb-ink-2);
            box-shadow: inset 0 0 0 1px var(--wb-line);
        }
        .wb-l3-tc, .wb-l3-name, .wb-l3-title {
            clip-path: inset(0 0 0 0);
            transition: clip-path 0.75s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .wb-l3-name { transition-delay: 0.1s; }
        .wb-l3-title { transition-delay: 0.26s; }
        html.es-anim #wb [data-reveal="l3"]:not(.is-revealed) .wb-l3-tc,
        html.es-anim #wb [data-reveal="l3"]:not(.is-revealed) .wb-l3-name,
        html.es-anim #wb [data-reveal="l3"]:not(.is-revealed) .wb-l3-title { clip-path: inset(0 100% 0 0); }

        /* ---------------------------------------------------------------
           Hero: the monitor wall
           --------------------------------------------------------------- */
        .wb-hero { padding-block: clamp(1.1rem, 2.4vw, 1.75rem) 0; }
        .wb-room { container: wb-room / inline-size; }
        .wb-wall { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; }
        .wb-side { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; align-content: start; }
        @container wb-room (min-width: 40rem) and (max-width: 57.99rem) {
            #wb .wb-side { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            #wb .wb-take { display: none; }
            #wb .wb-tiles { grid-column: 1 / -1; }
            #wb .wb-tile .wb-screen { min-height: 6.4rem; }
        }
        @container wb-room (min-width: 58rem) {
            .wb-wall { grid-template-columns: minmax(0, 1.62fr) minmax(0, 1fr); }
        }
        .wb-wall .wb-pgm .wb-screen {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.5rem;
            min-height: 20rem;
            padding: clamp(1.25rem, 3.4vw, 2.6rem);
            container-type: inline-size;
            background-image:
                radial-gradient(120% 90% at 12% 0%, rgba(255, 59, 48, 0.2), rgba(255, 59, 48, 0) 55%),
                radial-gradient(90% 80% at 100% 100%, rgba(53, 201, 230, 0.14), rgba(53, 201, 230, 0) 60%),
                linear-gradient(118deg, rgba(255, 255, 255, 0.05) 0 32%, rgba(255, 255, 255, 0) 32.2%);
        }
        /* Title-safe frame, as the vision mixer's overlay draws it. */
        .wb-wall .wb-pgm .wb-screen::after {
            content: "";
            position: absolute;
            inset: 0.9rem;
            border: 1px dashed rgba(238, 240, 243, 0.14);
            pointer-events: none;
        }
        .wb-bug { display: flex; align-items: center; justify-content: space-between; gap: 1rem; color: #aab1bc; }
        .wb-bug span { display: inline-flex; align-items: center; gap: 0.5rem; }
        .wb-bug i { width: 0.55rem; aspect-ratio: 1; border-radius: 50%; background: #ff3b30; box-shadow: 0 0 0.6rem #ff3b30; animation: wb-blink 1.6s steps(2, jump-none) infinite; }
        @keyframes wb-blink { to { opacity: 0.25; } }
        .wb-eyebrow {
            display: inline-block;
            margin-bottom: 1.1rem;
            padding: 0.3rem 0.6rem 0.24rem;
            background: #eef0f3;
            color: #14161a;
            font-family: var(--wb-mono);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .wb-h1 { font-size: clamp(2.15rem, 10.4cqi, 5.1rem); font-weight: 700; line-height: 1.02; letter-spacing: -0.025em; text-wrap: balance; }
        .wb-pgm-lede { max-width: 38rem; margin-top: 1.2rem; font-size: clamp(1rem, 2.75cqi, 1.16rem); line-height: 1.5; color: #c3c8d0; }
        .wb-h1 .wb-hi { color: #ffb020; }
        .wb-pgm-l3 { display: grid; justify-items: start; }
        .wb-pgm-l3 span:first-child { padding: 0.3rem 0.7rem 0.24rem; background: #ff3b30; color: #14161a; }
        .wb-pgm-l3 span:last-child { padding: 0.3rem 0.7rem 0.24rem; background: rgba(238, 240, 243, 0.12); color: #c3c8d0; }

        .wb-feed .wb-screen { padding: 0.95rem 1.05rem 1rem; }
        .wb-feed-head { display: flex; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.6rem; color: #aab1bc; }
        .wb-feed-title { font-size: 1.12rem; font-weight: 700; line-height: 1.25; }
        .wb-feed-meta { margin-top: 0.15rem; font-family: var(--wb-mono); font-size: 0.8rem; color: #c3c8d0; }
        .wb-feed-line { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.65rem; padding-top: 0.65rem; border-top: 1px solid rgba(238, 240, 243, 0.12); font-family: var(--wb-mono); font-size: 0.9rem; font-weight: 700; overflow-wrap: anywhere; }
        .wb-feed-line i { flex: none; width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: var(--c, #aab1bc); box-shadow: 0 0 0.5rem var(--c, transparent); }
        .wb-feed-note { margin-top: 0.45rem; font-size: 0.86rem; line-height: 1.45; color: #aab1bc; }
        .wb-feed-reg { display: inline-flex; align-items: center; gap: 0.45rem; color: #5ee67a; }
        .wb-feed-reg i { width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: #32d74b; box-shadow: 0 0 0.5rem #32d74b; }
        .wb-take { display: flex; align-items: center; gap: 0.75rem; color: var(--wb-ink-3); }
        .wb-take::before, .wb-take::after { content: ""; flex: 1; height: 1px; background: var(--wb-line-2); }
        .wb-take span { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.34rem 0.7rem 0.28rem; border-radius: 0.3rem; background: #ffb020; color: #14161a; box-shadow: 0 0 0 2px #17191d, 0 0.18rem 0 #17191d; }
        .wb-take svg { width: 0.8rem; height: 0.8rem; }

        .wb-tiles { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem; }
        .wb-tile .wb-screen { display: grid; place-items: center; align-content: center; gap: 0.45rem; padding: 0.8rem 0.5rem; min-height: 7.4rem; text-align: center; color: #aab1bc; }
        .wb-clock { position: relative; display: grid; place-items: center; width: 5.9rem; aspect-ratio: 1; }
        .wb-clock i {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            -webkit-mask: radial-gradient(closest-side, transparent 0 76%, #000 77% 96%, transparent 97%);
            mask: radial-gradient(closest-side, transparent 0 76%, #000 77% 96%, transparent 97%);
        }
        .wb-clock-ticks { background: repeating-conic-gradient(from -1.5deg, #4a505a 0 3deg, rgba(74, 80, 90, 0) 3deg 6deg); }
        .wb-clock .wb-clock-lit { display: none; }
        .wb-clock b { display: grid; justify-items: center; font-family: var(--wb-mono); font-size: 0.98rem; font-weight: 700; letter-spacing: 0.04em; line-height: 1.15; color: #eef0f3; }
        .wb-clock b::after { content: ":23"; font-size: 0.74rem; color: #ff6a60; }
        @property --wb-sec { syntax: '<angle>'; inherits: false; initial-value: 138deg; }
        @property --wb-s { syntax: '<integer>'; inherits: false; initial-value: 23; }
        @keyframes wb-sec { from { --wb-sec: 0deg; } to { --wb-sec: 360deg; } }
        @keyframes wb-s { from { --wb-s: 0; } to { --wb-s: 60; } }
        @supports ((mask-composite: intersect) or (-webkit-mask-composite: source-in)) {
            .wb-clock .wb-clock-lit {
                display: block;
                background: repeating-conic-gradient(from -1.5deg, #ff3b30 0 3deg, rgba(255, 59, 48, 0) 3deg 6deg);
                -webkit-mask: radial-gradient(closest-side, transparent 0 76%, #000 77% 96%, transparent 97%), conic-gradient(#000 var(--wb-sec), transparent 0);
                -webkit-mask-composite: source-in;
                mask: radial-gradient(closest-side, transparent 0 76%, #000 77% 96%, transparent 97%), conic-gradient(#000 var(--wb-sec), transparent 0);
                mask-composite: intersect;
            }
            html.es-anim #wb .wb-clock-lit { animation: wb-sec 60s steps(60) infinite; }
            html.es-anim #wb .wb-clock b::after { counter-reset: wb-s calc(var(--wb-s) * 1); content: ":" counter(wb-s, decimal-leading-zero); animation: wb-s 60s steps(60) infinite; }
        }
        .wb-tc { font-family: var(--wb-mono); font-size: clamp(1rem, 1.9vw, 1.3rem); font-weight: 700; letter-spacing: 0.04em; color: #eef0f3; }
        @property --wb-t { syntax: '<integer>'; inherits: false; initial-value: 847; }
        @property --wb-f { syntax: '<integer>'; inherits: false; initial-value: 12; }
        @keyframes wb-t { from { --wb-t: 847; } to { --wb-t: 0; } }
        @keyframes wb-f { from { --wb-f: 24; } to { --wb-f: 0; } }
        /* Safari animates a registered integer through fractions (839.571), and a bare fraction is
           not a counter value, so the whole line was dropped there and the clock read 00:00:00:00.
           Each figure is rounded to a whole number before it is used. */
        @supports (width: mod(5px, 2px)) {
            html.es-anim #wb .wb-tc span { display: none; }
            html.es-anim #wb .wb-tc::after {
                counter-reset: wb-m round(down, round(nearest, var(--wb-t), 1) / 60, 1) wb-ss mod(round(nearest, var(--wb-t), 1), 60) wb-ff round(nearest, var(--wb-f), 1);
                content: "00:" counter(wb-m, decimal-leading-zero) ":" counter(wb-ss, decimal-leading-zero) ":" counter(wb-ff, decimal-leading-zero);
                animation: wb-t 847s linear forwards, wb-f 1s steps(25) infinite;
            }
        }
        .wb-sign {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.6rem 0.24rem;
            border-radius: 0.25rem;
            background: #ffb020;
            color: #14161a;
            font-family: var(--wb-mono);
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.2;
            box-shadow: 0 0 1.1rem -0.2rem rgba(255, 176, 32, 0.9);
        }
        .wb-sign-air { background: #ff3b30; box-shadow: 0 0 1.4rem -0.1rem rgba(255, 59, 48, 0.95); animation: wb-air 2.6s ease-in-out infinite; }
        @keyframes wb-air { 50% { box-shadow: 0 0 0.5rem -0.2rem rgba(255, 59, 48, 0.6); } }

        /* Cutting a feed to programme: the tally follows the pointer, with no script. */
        @media (hover: hover) {
            #wb .wb-wall:has(.wb-feed:hover) .wb-pgm .wb-screen,
            #wb .wb-wall:has(.wb-feed:hover) .wb-pvw:not(:hover) .wb-screen { box-shadow: 0 0 0 2px #1d2026; }
            #wb .wb-wall:has(.wb-feed:hover) .wb-pgm .wb-umd b,
            #wb .wb-wall:has(.wb-feed:hover) .wb-pvw:not(:hover) .wb-umd b { color: #eef0f3; }
            #wb .wb-wall .wb-feed:hover .wb-screen { box-shadow: 0 0 0 3px #ff3b30, 0 0 2rem -0.3rem rgba(255, 59, 48, 0.75); }
            #wb .wb-wall .wb-feed:hover .wb-umd b { color: #ff6a60; }
        }

        .wb-desk { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem 3rem; padding-block: 1.75rem clamp(2.25rem, 5vw, 3.75rem); }
        @media (min-width: 960px) { .wb-desk { grid-template-columns: minmax(0, 1.62fr) minmax(0, 1fr); align-items: start; } }
        .wb-lede-2 { max-width: 42rem; margin-top: 1.5rem; color: var(--wb-ink-2); }
        .wb-cta { display: flex; flex-wrap: wrap; gap: 1.1rem 1.25rem; }
        .wb-cap { padding-inline-start: 1rem; border-inline-start: 1px solid var(--wb-line-2); color: var(--wb-ink-2); font-size: 1rem; }
        .wb-cap .wb-mono { display: block; margin-bottom: 0.5rem; color: var(--wb-ink-3); }

        .wb-ticker { background: #0a0b0d; color: #eef0f3; padding-block: 0.75rem 0.65rem; }
        .wb-ticker .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .wb-chip { display: inline-flex; align-items: center; gap: 0.6rem; padding-inline-end: 1.75rem; font-family: var(--wb-mono); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; white-space: nowrap; }
        .wb-chip::before { content: ""; width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: #32d74b; box-shadow: 0 0 0.5rem #32d74b; }
        .wb-chip:nth-child(3n + 2)::before { background: #ffb020; box-shadow: 0 0 0.5rem #ffb020; }
        .wb-chip:nth-child(3n)::before { background: #ff3b30; box-shadow: 0 0 0.5rem #ff3b30; }
        @media (prefers-reduced-motion: reduce) { .wb-ticker .es-marquee-track { row-gap: 0.5rem; } }

        /* ---------------------------------------------------------------
           The signal path: three outputs, in order
           --------------------------------------------------------------- */
        .wb-outs { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        @media (min-width: 900px) { .wb-outs { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wb-out .wb-screen { padding: 1.3rem 1.3rem 1.5rem; min-height: 14rem; }
        .wb-out-no { margin-bottom: 1rem; color: #aab1bc; }
        .wb-out h3 { font-size: 1.32rem; font-weight: 700; line-height: 1.2; }
        .wb-out p { margin-top: 0.7rem; font-size: 1rem; color: #c3c8d0; }
        .wb-reads { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-top: 1rem; }
        .wb-read { padding: 1.1rem 0.6rem 0.95rem; border-radius: 0.55rem; background: #0a0b0d; text-align: center; color: #aab1bc; box-shadow: 0 0 0 1px #000; }
        .dark .wb-read { box-shadow: 0 0 0 1px rgba(238, 240, 243, 0.13); }
        .wb-read b { display: block; margin-bottom: 0.5rem; font-family: var(--wb-mono); font-size: clamp(2rem, 5.4vw, 3.4rem); font-weight: 700; line-height: 1; color: #eef0f3; text-shadow: 0 0 1.1rem rgba(238, 240, 243, 0.35); }
        .wb-note { margin-top: 1.75rem; max-width: 52rem; color: var(--wb-ink-2); }

        /* ---------------------------------------------------------------
           The patch bay: twelve sources, one destination
           --------------------------------------------------------------- */
        .wb-two { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3rem; align-items: start; }
        @media (min-width: 980px) { .wb-two { grid-template-columns: minmax(0, 1.04fr) minmax(0, 1fr); } }
        .wb-router { padding: 1.1rem 1.1rem 1.25rem; border-radius: 0.7rem; background: #0a0b0d; color: #aab1bc; box-shadow: 0 0 0 1px #000, 0 1.4rem 2.4rem -1.4rem rgba(10, 12, 16, 0.6); }
        .dark .wb-router { box-shadow: 0 0 0 1px rgba(238, 240, 243, 0.13); }
        .wb-router-head { display: flex; justify-content: space-between; gap: 1rem; }
        .wb-srcs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.55rem; margin-top: 0.9rem; }
        @media (min-width: 520px) { .wb-srcs { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wb-src {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            min-width: 0;
            padding: 0.72rem 0.7rem 0.66rem;
            border-radius: 0.4rem;
            background: linear-gradient(180deg, #2c3038, #1c1f24);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1), 0 2px 0 #000;
            color: #eef0f3;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.2;
            transition: background 0.2s ease, box-shadow 0.2s ease;
        }
        .wb-src i { flex: none; width: 0.6rem; aspect-ratio: 1; border-radius: 50%; background: var(--led); box-shadow: 0 0 0.6rem var(--led); }
        .wb-src:hover { background: linear-gradient(180deg, #3a4049, #262a31); box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16), 0 2px 0 #000, 0 0 0 2px #32d74b; }
        .wb-bus { position: relative; height: 1.5rem; margin: 0.9rem 12% 0; border: 2px solid #3a3f47; border-top: 0; border-radius: 0 0 0.6rem 0.6rem; }
        .wb-bus::after { content: ""; position: absolute; left: calc(50% - 1px); top: 100%; width: 2px; height: 0.9rem; background: #3a3f47; }
        .wb-outp { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.6rem 0.9rem; margin-top: 0.9rem; padding: 0.9rem 1rem; border-radius: 0.45rem; background: #101216; box-shadow: 0 0 0 2px #32d74b, 0 0 1.6rem -0.4rem rgba(50, 215, 75, 0.6); }
        .wb-outp code { font-family: var(--wb-mono); font-size: 1.3rem; font-weight: 700; color: #eef0f3; }

        /* The talkback panel: one key, one line of legend, the real copy beside it. */
        .wb-tb { border-top: 1px solid var(--wb-line-2); }
        .wb-tb-row { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.35rem 1.15rem; padding: 1.3rem 0 1.4rem; border-bottom: 1px solid var(--wb-line); }
        .wb-tb-row .wb-key { grid-row: 1 / span 2; margin-top: 0.15rem; }
        .wb-tb-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.7rem; min-height: 1.9rem; }
        .wb-tb h3 { font-size: 1.2rem; font-weight: 700; line-height: 1.25; }
        .wb-tb p { grid-column: 2; color: var(--wb-ink-2); }
        @media (max-width: 520px) {
            .wb-tb-row .wb-key { grid-row: 1; width: 2.7rem; }
            .wb-tb p { grid-column: 1 / -1; }
        }

        /* ---------------------------------------------------------------
           The rundown
           --------------------------------------------------------------- */
        .wb-run { padding: 1rem 1rem 0.6rem; border-radius: 0.7rem; background: #0a0b0d; color: #c3c8d0; box-shadow: 0 0 0 1px #000, 0 1.4rem 2.4rem -1.4rem rgba(10, 12, 16, 0.6); }
        .dark .wb-run { box-shadow: 0 0 0 1px rgba(238, 240, 243, 0.13); }
        .wb-run-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem 1rem; padding: 0.25rem 0.5rem 1rem; }
        .wb-run-head h3 { font-size: 1.3rem; font-weight: 700; line-height: 1.2; color: #eef0f3; }
        .wb-run-head p { margin-top: 0.2rem; color: #aab1bc; }
        .wb-run table { width: 100%; border-collapse: collapse; counter-reset: wb-item; }
        .wb-run thead th { padding: 0.5rem 0.6rem; border-bottom: 1px solid #2a2e35; text-align: start; color: #8d95a1; }
        .wb-run thead th:last-child { text-align: end; }
        .wb-run tbody tr { counter-increment: wb-item; border-bottom: 1px solid #1d2026; }
        .wb-run tbody tr:last-child { border-bottom: 0; }
        .wb-run td, .wb-run tbody th { padding: 0.9rem 0.6rem; vertical-align: middle; }
        .wb-run-in { font-family: var(--wb-mono); font-size: 0.95rem; font-weight: 700; color: #eef0f3; white-space: nowrap; }
        .wb-run-in::before { content: counter(wb-item, decimal-leading-zero); margin-inline-end: 0.9rem; color: #8d95a1; }
        .wb-run tbody th { width: 100%; text-align: start; font-weight: 700; color: #eef0f3; }
        .wb-run-seg { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem 0.7rem; }
        .wb-run-src { padding: 0.16rem 0.4rem 0.12rem; border-radius: 0.2rem; background: #23262b; color: #aab1bc; font-size: 0.6rem; }
        .wb-run-track { position: relative; display: block; height: 0.5rem; margin-top: 0.55rem; border-radius: 1rem; background: #1d2026; overflow: hidden; }
        .wb-run-bar { position: absolute; inset-block: 0; left: var(--at); width: var(--w); border-radius: 1rem; background: #32d74b; transform-origin: left center; transition: transform 1s cubic-bezier(0.22, 1, 0.36, 1) 0.3s; }
        html.es-anim #wb [data-reveal]:not(.is-revealed) .wb-run-bar { transform: scaleX(0); }
        .wb-run-dur { font-family: var(--wb-mono); font-size: 0.9rem; white-space: nowrap; text-align: end; color: #c3c8d0; }
        @media (max-width: 560px) {
            .wb-run { padding-inline: 0.6rem; }
            .wb-run td, .wb-run tbody th { padding-inline: 0.4rem; }
            .wb-run-in::before { display: none; }
        }
        /* Each row takes the tally as it crosses the middle of the window: the rundown plays as you read it. */
        @keyframes wb-cue { 0%, 100% { background-color: rgba(255, 59, 48, 0); } 12%, 88% { background-color: rgba(255, 59, 48, 0.22); } }
        @supports (animation-timeline: view()) {
            html.es-anim #wb .wb-run tbody tr { animation: wb-cue linear both; animation-timeline: view(); animation-range: cover 45.5% cover 54.5%; }
        }
        .wb-cards { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: 1.25rem; }
        @media (min-width: 860px) { .wb-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wb-card { display: flex; flex-direction: column; padding: 1.25rem 1.25rem 1.35rem; border: 1px solid var(--wb-line); border-radius: 0.6rem; background: var(--wb-panel); }
        .wb-card-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 0.75rem; margin-bottom: 0.6rem; }
        .wb-card h3, .wb-card h4 { font-size: 1.14rem; font-weight: 700; line-height: 1.25; }
        .wb-card p { color: var(--wb-ink-2); font-size: 1rem; }
        .wb-foot { margin-top: 1rem; color: var(--wb-ink-3); font-size: 0.95rem; }

        /* ---------------------------------------------------------------
           The series: a prompter and a playout timeline
           --------------------------------------------------------------- */
        .wb-stack { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; align-content: start; }
        .wb-prompter { padding: 0.6rem; border-radius: 0.7rem; background: #0a0b0d; box-shadow: 0 0 0 1px #000, 0 1.4rem 2.4rem -1.4rem rgba(10, 12, 16, 0.6); }
        .dark .wb-prompter { box-shadow: 0 0 0 1px rgba(238, 240, 243, 0.13); }
        .wb-prompter-head { display: flex; justify-content: space-between; gap: 1rem; padding: 0.3rem 0.5rem 0.65rem; color: #aab1bc; }
        .wb-prompter-glass { position: relative; padding: 1.6rem 2.2rem; border-radius: 0.35rem; background: #050607; overflow: clip; }
        .wb-prompter-glass::before, .wb-prompter-glass::after { content: ""; position: absolute; top: calc(50% - 0.5rem); border: 0.5rem solid transparent; z-index: 1; }
        .wb-prompter-glass::before { left: 0.25rem; border-left-color: #ff3b30; }
        .wb-prompter-glass::after { right: 0.25rem; border-right-color: #ff3b30; }
        .wb-prompter-text { font-size: clamp(1.3rem, 2.3vw, 1.7rem); font-weight: 700; line-height: 1.42; color: #f4f5f7; }
        @keyframes wb-roll { from { translate: 0 6.6rem; } to { translate: 0 calc(-100% + 7.4rem); } }
        @supports (animation-timeline: view()) {
            html.es-anim #wb .wb-prompter-glass {
                height: 14rem;
                padding-block: 0;
                view-timeline-name: --wb-prompt;
                -webkit-mask-image: linear-gradient(180deg, transparent 0, #000 24%, #000 76%, transparent 100%);
                mask-image: linear-gradient(180deg, transparent 0, #000 24%, #000 76%, transparent 100%);
            }
            html.es-anim #wb .wb-prompter-text { animation: wb-roll linear both; animation-timeline: --wb-prompt; animation-range: cover 18% cover 82%; }
        }
        .wb-playout { padding: 1.25rem; border: 1px solid var(--wb-line); border-radius: 0.7rem; background: var(--wb-panel); }
        .wb-playout-head { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.4rem 1rem; margin-bottom: 1rem; }
        .wb-playout-head h3 { font-size: 1.3rem; font-weight: 700; }
        .wb-playout-head span { color: var(--wb-ink-3); }
        .wb-tl { padding: 0.8rem 0.75rem 0.7rem; border-radius: 0.5rem; background: #0a0b0d; }
        .wb-tl-ruler { height: 0.55rem; margin-bottom: 0.45rem; background: repeating-linear-gradient(90deg, #4a505a 0 1px, rgba(74, 80, 90, 0) 1px calc(100% / 28)); }
        .wb-tl-cells { display: grid; grid-template-columns: repeat(14, minmax(0, 1fr)); gap: 0.28rem; height: 2.7rem; }
        .wb-tl-cells span { border-radius: 0.2rem; transform-origin: left center; transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(var(--i) * 45ms + 200ms); }
        html.es-anim #wb [data-reveal]:not(.is-revealed) .wb-tl-cells span { transform: scaleX(0); }
        .wb-tl-on { background: linear-gradient(180deg, #f4f5f7, #c3c8d0); }
        .wb-tl-skip { border: 2px dashed #6f7783; }
        .wb-tl-out { justify-self: start; width: 3px; border-radius: 0; background: #ff3b30; box-shadow: 0 0 0.7rem #ff3b30; }
        .wb-legend { display: flex; flex-wrap: wrap; gap: 0.5rem 1.4rem; margin-top: 0.9rem; color: var(--wb-ink-3); }
        .wb-legend span { display: inline-flex; align-items: center; gap: 0.5rem; }
        .wb-legend i { flex: none; width: 1rem; height: 0.65rem; border-radius: 0.12rem; background: var(--wb-ink); }
        .wb-legend span:nth-child(2) i { background: transparent; border: 1.5px dashed var(--wb-ink-3); }
        .wb-legend span:nth-child(3) i { width: 3px; height: 0.85rem; border-radius: 0; background: #ff3b30; }
        .wb-playout .wb-cards { grid-template-columns: minmax(0, 1fr); margin-top: 1.25rem; gap: 0; border-top: 1px solid var(--wb-line); }
        .wb-playout .wb-card { padding: 1rem 0 1.05rem; border: 0; border-bottom: 1px solid var(--wb-line); border-radius: 0; background: none; }
        .wb-playout .wb-card:last-child { padding-bottom: 0; border-bottom: 0; }
        .wb-playout .wb-card-head { justify-content: flex-start; margin-bottom: 0.35rem; }

        /* ---------------------------------------------------------------
           Registration: the house counter
           --------------------------------------------------------------- */
        .wb-counter { padding: 1.3rem 1.3rem 1.4rem; border-radius: 0.7rem; background: #0a0b0d; color: #aab1bc; box-shadow: 0 0 0 1px #000, 0 1.4rem 2.4rem -1.4rem rgba(10, 12, 16, 0.6); }
        .dark .wb-counter { box-shadow: 0 0 0 1px rgba(238, 240, 243, 0.13); }
        @media (min-width: 980px) { .wb-follow { position: sticky; top: 5.5rem; } }
        .wb-counter-big { margin-block: 0.5rem 1rem; font-family: var(--wb-mono); font-size: clamp(2.6rem, 7vw, 4.4rem); font-weight: 700; line-height: 1; color: #eef0f3; text-shadow: 0 0 1.2rem rgba(238, 240, 243, 0.3); }
        .wb-seats { position: relative; height: 1.15rem; border-radius: 0.15rem; background: linear-gradient(90deg, #262a31 64%, rgba(38, 42, 49, 0) 64%) 0 0 / calc(100% / 120) 100%; }
        .wb-seats i { position: absolute; inset: 0 auto 0 0; width: 70%; background: linear-gradient(90deg, #32d74b 64%, rgba(50, 215, 75, 0) 64%) 0 0 / calc(100% / 84) 100%; transform-origin: left center; transition: transform 1.2s cubic-bezier(0.22, 1, 0.36, 1) 0.3s; }
        html.es-anim #wb [data-reveal]:not(.is-revealed) .wb-seats i { transform: scaleX(0); }
        .wb-counter-row { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 0.4rem 1rem; margin-top: 0.7rem; }
        .wb-counter-row span:first-child { color: #5ee67a; }
        .wb-counter-next { margin-top: 1.1rem; padding-top: 1rem; border-top: 1px solid #23262b; }

        /* ---------------------------------------------------------------
           Everything else: a multiview beside its shot list
           --------------------------------------------------------------- */
        .wb-rest { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 3rem; align-items: start; }
        .wb-mv { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; padding: 0.6rem; border-radius: 0.7rem; background: #0a0b0d; box-shadow: 0 0 0 1px #000, 0 1.4rem 2.4rem -1.4rem rgba(10, 12, 16, 0.6); }
        .dark .wb-mv { box-shadow: 0 0 0 1px rgba(238, 240, 243, 0.13); }
        @media (min-width: 1000px) {
            .wb-rest { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); }
            .wb-mv { position: sticky; top: 5.5rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .wb-mv .wb-mon { padding: 0; background: none; box-shadow: none; }
        .wb-mv .wb-screen { aspect-ratio: 16 / 10; container-type: inline-size; display: grid; place-items: center; background-image: radial-gradient(120% 120% at 0% 0%, var(--a1), rgba(0, 0, 0, 0) 62%), radial-gradient(120% 120% at 100% 100%, var(--a2), rgba(0, 0, 0, 0) 62%); }
        .wb-mv .wb-screen span { font-family: var(--wb-mono); font-size: clamp(0.9rem, 20cqi, 2.1rem); font-weight: 700; letter-spacing: 0.04em; color: #eef0f3; }
        .wb-mv .wb-umd { padding: 0.35rem 0.15rem 0.3rem; font-size: 0.56rem; letter-spacing: 0.1em; }
        .wb-list { border-top: 1px solid var(--wb-line-2); }
        .wb-item { padding: 1.4rem 0.75rem 1.5rem; border-bottom: 1px solid var(--wb-line); transition: background-color 0.25s ease; }
        .wb-item-head { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.75rem; margin-bottom: 0.6rem; }
        .wb-item-no { padding: 0.24rem 0.45rem 0.2rem; border-radius: 0.2rem; background: #14161a; color: #eef0f3; }
        .dark .wb-item-no { background: #eef0f3; color: #14161a; }
        .wb-item h3 { font-size: 1.25rem; font-weight: 700; line-height: 1.25; }
        .wb-item p { color: var(--wb-ink-2); }
        .wb-item p + p { margin-top: 0.7rem; }
        @media (hover: hover) {
            .wb-item:hover { background-color: var(--wb-panel); }
            .wb-item:hover .wb-item-no { background: #ff3b30; color: #14161a; }
            #wb .wb-rest:has(.wb-item:nth-child(1):hover) .wb-mv .wb-mon:nth-child(1) .wb-screen,
            #wb .wb-rest:has(.wb-item:nth-child(2):hover) .wb-mv .wb-mon:nth-child(2) .wb-screen,
            #wb .wb-rest:has(.wb-item:nth-child(3):hover) .wb-mv .wb-mon:nth-child(3) .wb-screen,
            #wb .wb-rest:has(.wb-item:nth-child(4):hover) .wb-mv .wb-mon:nth-child(4) .wb-screen,
            #wb .wb-rest:has(.wb-item:nth-child(5):hover) .wb-mv .wb-mon:nth-child(5) .wb-screen,
            #wb .wb-rest:has(.wb-item:nth-child(6):hover) .wb-mv .wb-mon:nth-child(6) .wb-screen { box-shadow: 0 0 0 3px #ff3b30, 0 0 1.6rem -0.3rem rgba(255, 59, 48, 0.8); }
            #wb .wb-rest:has(.wb-mon:nth-child(1):hover) .wb-item:nth-child(1),
            #wb .wb-rest:has(.wb-mon:nth-child(2):hover) .wb-item:nth-child(2),
            #wb .wb-rest:has(.wb-mon:nth-child(3):hover) .wb-item:nth-child(3),
            #wb .wb-rest:has(.wb-mon:nth-child(4):hover) .wb-item:nth-child(4),
            #wb .wb-rest:has(.wb-mon:nth-child(5):hover) .wb-item:nth-child(5),
            #wb .wb-rest:has(.wb-mon:nth-child(6):hover) .wb-item:nth-child(6) { background-color: var(--wb-panel); }
            #wb .wb-mv .wb-mon:hover .wb-screen { box-shadow: 0 0 0 3px #ff3b30, 0 0 1.6rem -0.3rem rgba(255, 59, 48, 0.8); }
        }

        /* ---------------------------------------------------------------
           Perfect for: six cameras, each with its caption up
           --------------------------------------------------------------- */
        .wb-cams { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.1rem; }
        @media (min-width: 680px) { .wb-cams { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .wb-cams { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wb-cam { display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--wb-line); border-radius: 0.6rem; background: var(--wb-panel); transition: translate 0.25s ease, box-shadow 0.25s ease; }
        .wb-cam:hover { translate: 0 -3px; box-shadow: 0 0 0 3px rgba(255, 59, 48, 0.85), 0 1.2rem 2rem -1.2rem rgba(10, 12, 16, 0.6); }
        .wb-cam-shot {
            position: relative;
            display: flex;
            align-items: flex-end;
            aspect-ratio: 16 / 8;
            padding-bottom: 0.9rem;
            color: #aab1bc;
            background-color: #14161a;
            background-image:
                radial-gradient(58% 80% at 28% 42%, hsl(var(--h1) 62% 46% / 0.92), hsl(var(--h1) 62% 46% / 0) 72%),
                radial-gradient(50% 72% at 82% 26%, hsl(var(--h2) 70% 52% / 0.8), hsl(var(--h2) 70% 52% / 0) 70%),
                radial-gradient(40% 50% at 62% 90%, rgba(244, 245, 247, 0.22), rgba(244, 245, 247, 0) 70%);
        }
        /* The camera's focus brackets: eight short strokes, two to a corner. */
        .wb-cam-shot::after {
            content: "";
            position: absolute;
            inset: 16% 35% 36% 35%;
            --b: linear-gradient(rgba(255, 255, 255, 0.82) 0 0);
            background:
                var(--b) 0 0 / 24% 2px no-repeat, var(--b) 0 0 / 2px 30% no-repeat,
                var(--b) 100% 0 / 24% 2px no-repeat, var(--b) 100% 0 / 2px 30% no-repeat,
                var(--b) 0 100% / 24% 2px no-repeat, var(--b) 0 100% / 2px 30% no-repeat,
                var(--b) 100% 100% / 24% 2px no-repeat, var(--b) 100% 100% / 2px 30% no-repeat,
                var(--b) 50% 50% / 0.9rem 2px no-repeat, var(--b) 50% 50% / 2px 0.9rem no-repeat;
            transition: inset 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .wb-cam:hover .wb-cam-shot::after { inset: 20% 38% 40% 38%; }
        .wb-cam-id { position: absolute; top: 0.7rem; left: 0.8rem; color: rgba(255, 255, 255, 0.9); text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6); }
        .wb-cam-l3 { position: relative; display: grid; grid-template-columns: 0.4rem auto; max-width: 92%; }
        .wb-cam-l3::before { content: ""; background: #ff3b30; }
        .wb-cam-l3 h3 { padding: 0.4rem 0.8rem 0.42rem; background: #f4f5f7; color: #14161a; font-size: 1.08rem; font-weight: 700; line-height: 1.2; }
        .wb-cam-body { display: flex; flex: 1; flex-direction: column; padding: 1rem 1.1rem 1.15rem; }
        .wb-cam-body p { color: var(--wb-ink-2); font-size: 1rem; }
        .wb-cam-body a { align-self: flex-start; margin-top: auto; padding-top: 0.9rem; }
        .wb-cam-body a span { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; border-bottom: 2px solid #ff3b30; transition: gap 0.2s ease; }
        .wb-cam-body a:hover span { gap: 0.7rem; }
        .wb-cam-body svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           Countdown: standing by, cue, on air
           --------------------------------------------------------------- */
        .wb-steps { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.1rem; }
        @media (min-width: 860px) { .wb-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wb-step { padding: 1.1rem 1.1rem 1.4rem; border: 1px solid var(--wb-line); border-radius: 0.7rem; background: var(--wb-panel); }
        .wb-step-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.2rem; padding: 0.75rem 1rem 0.7rem; border-radius: 0.45rem; background: #0a0b0d; }
        .wb-step-top b { font-family: var(--wb-mono); font-size: 3.1rem; font-weight: 700; line-height: 1; color: #eef0f3; text-shadow: 0 0 1.1rem rgba(238, 240, 243, 0.35); }
        .wb-step:last-child .wb-step-top b { color: #ff6a60; text-shadow: 0 0 1.2rem rgba(255, 59, 48, 0.7); }
        .wb-step h3 { font-size: 1.3rem; font-weight: 700; line-height: 1.2; }
        .wb-step p { margin-top: 0.6rem; color: var(--wb-ink-2); }

        /* ---------------------------------------------------------------
           Key features: the shot box
           --------------------------------------------------------------- */
        .wb-feat { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 4rem; align-items: start; }
        @media (min-width: 960px) { .wb-feat { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1.38fr); } }
        .wb-feat-h { font-size: clamp(1.9rem, 4vw, 2.9rem); font-weight: 700; line-height: 1.08; letter-spacing: -0.02em; }
        .wb-feat-more { margin-top: 1.4rem; }
        .wb-shots { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; }
        @media (min-width: 700px) { .wb-shots { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .wb-shot { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 1.1rem; padding: 1rem 1.1rem 1.1rem; border: 1px solid var(--wb-line); border-radius: 0.6rem; background: var(--wb-panel); transition: box-shadow 0.2s ease, border-color 0.2s ease; }
        .wb-shot:hover { border-color: transparent; box-shadow: 0 0 0 3px rgba(255, 59, 48, 0.85); }
        .wb-shot:hover .wb-key { --k: #ff3b30; }
        .wb-shot strong { display: block; font-size: 1.14rem; line-height: 1.25; }
        .wb-shot small { display: block; margin-top: 0.2rem; font-size: 0.95rem; line-height: 1.4; color: var(--wb-ink-2); }
        .wb-shot > svg { color: var(--wb-ink-3); transition: translate 0.2s ease; }
        .wb-shot:hover > svg { translate: 0.25rem 0; }

        /* ---------------------------------------------------------------
           Related pages: the other studios
           --------------------------------------------------------------- */
        .wb-rel-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1rem; margin-bottom: 1.75rem; }
        .wb-studios { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        @media (min-width: 900px) { .wb-studios { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .wb-studio .wb-screen {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 0.35rem;
            aspect-ratio: 16 / 10;
            padding: 0.9rem;
        }
        .wb-studio .wb-screen::before { content: ""; position: absolute; inset: 0; background: radial-gradient(90% 90% at 100% 0%, hsl(var(--h1) 62% 44% / 0.7), hsl(var(--h1) 62% 44% / 0) 70%); }
        .wb-studio .wb-screen > * { position: relative; }
        .wb-studio strong { font-size: clamp(1rem, 1.5vw, 1.2rem); line-height: 1.2; }
        .wb-studio small { display: inline-flex; align-items: center; gap: 0.35rem; color: #c3c8d0; }
        .wb-studio svg { width: 0.9rem; height: 0.9rem; }
        @media (max-width: 560px) { .wb-studio .wb-umd span { display: none; } }
        .wb-studio:hover .wb-screen { box-shadow: 0 0 0 3px #ff3b30, 0 0 2rem -0.3rem rgba(255, 59, 48, 0.75); }
        .wb-studio:hover .wb-umd b { color: #ff6a60; }

        /* ---------------------------------------------------------------
           Questions
           --------------------------------------------------------------- */
        .wb-faq { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .wb-faq { grid-template-columns: minmax(0, 0.74fr) minmax(0, 1.26fr); }
            .wb-faq-head { position: sticky; top: 6.5rem; }
        }
        .wb-qa { border-top: 1px solid var(--wb-line-2); counter-reset: wb-q; }
        .wb-qa details { counter-increment: wb-q; border-bottom: 1px solid var(--wb-line); }
        .wb-qa summary { display: grid; grid-template-columns: 2.9rem minmax(0, 1fr) 1.4rem; align-items: start; gap: 0.75rem; padding: 1.2rem 0.25rem 1.15rem; cursor: pointer; }
        .wb-qa summary::before { content: "Q" counter(wb-q, decimal-leading-zero); padding-top: 0.3rem; font-family: var(--wb-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em; color: var(--wb-red-ink); }
        .wb-qa h3 { font-size: 1.14rem; font-weight: 700; line-height: 1.35; }
        .wb-qa summary i { position: relative; width: 1.4rem; height: 1.4rem; margin-top: 0.15rem; }
        .wb-qa summary i::before, .wb-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .wb-qa summary i::after { rotate: 90deg; }
        .wb-qa details[open] summary i::after { rotate: 0deg; }
        .wb-qa details p { padding: 0 0.25rem 1.5rem 3.9rem; max-width: 48rem; color: var(--wb-ink-2); }
        @media (max-width: 560px) { .wb-qa details p { padding-inline-start: 0.25rem; } }

        /* ---------------------------------------------------------------
           Bars, and the last monitor
           --------------------------------------------------------------- */
        .wb-bars {
            height: 3rem;
            background:
                linear-gradient(90deg, #f4f5f7 0 14.28%, #ffd60a 0 28.57%, #35c9e6 0 42.85%, #32d74b 0 57.14%, #ff3b30 0 71.42%, #0f4c4a 0 85.71%, #050607 0) top / 100% 68% no-repeat,
                linear-gradient(90deg, #050607 0 12.5%, #23262b 0 25%, #41464e 0 37.5%, #5f6670 0 50%, #7e8691 0 62.5%, #9da4ae 0 75%, #bcc2ca 0 87.5%, #f4f5f7 0) bottom / 100% 32% no-repeat;
        }
        .wb-final { padding-block: clamp(3rem, 7vw, 5.5rem); }
        .wb-final .wb-mon { width: min(100%, 64rem); margin-inline: auto; }
        .wb-final .wb-screen {
            padding: clamp(2.25rem, 6vw, 4.5rem) clamp(1.1rem, 5vw, 4rem);
            text-align: center;
            background-image:
                radial-gradient(110% 80% at 50% 0%, rgba(255, 59, 48, 0.24), rgba(255, 59, 48, 0) 60%),
                linear-gradient(118deg, rgba(255, 255, 255, 0.05) 0 32%, rgba(255, 255, 255, 0) 32.2%);
        }
        .wb-final .wb-umd span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wb-final h2 { margin-top: 1.4rem; font-size: clamp(2.1rem, 6vw, 4.3rem); font-weight: 700; line-height: 1.05; letter-spacing: -0.022em; text-wrap: balance; color: #f4f5f7; }
        .wb-final h2 .wb-hi { color: #ffb020; }
        .wb-final-sub { margin: 1.3rem auto 0; max-width: 40rem; color: #c3c8d0; font-size: 1.1rem; }
        .wb-claim-wrap { display: grid; gap: 1rem; width: min(100%, 34rem); margin: 2.1rem auto 0; text-align: start; }
        .wb-claim-wrap label { color: #aab1bc; }
        #wb .wb-claim {
            --wb-claim-fs: clamp(0.9rem, 3.2vw, 1.15rem);
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1rem;
            border: 0;
            border-radius: 0.45rem;
            background: #050607;
            box-shadow: inset 0 0 0 2px #3a3f47;
            font-family: var(--wb-mono);
            font-size: var(--wb-claim-fs);
            font-weight: 700;
            color: #eef0f3;
            transition: box-shadow 0.2s ease;
        }
        #wb .wb-claim:focus-within { border-color: transparent; box-shadow: inset 0 0 0 2px #32d74b, 0 0 0 4px rgba(50, 215, 75, 0.28); }
        /* iOS zooms the page when a field under 16px takes focus, so what is typed is never
           smaller than that. The placeholder and the suffix keep the row's own size, and on a
           narrow phone that size comes down so the whole address fits. */
        #wb .wb-claim input { flex: 1; min-width: 0; border: 0; background: transparent; padding-inline: 0; text-align: right; font: inherit; font-size: max(1rem, var(--wb-claim-fs)); color: #eef0f3; box-shadow: none; outline: none; }
        #wb .wb-claim input::placeholder { color: #8d95a1; font-size: var(--wb-claim-fs); }
        @media (max-width: 420px) {
            #wb .wb-claim { --wb-claim-fs: clamp(0.78rem, 3.4vw, 0.9rem); padding-inline: 0.75rem; }
        }
        .wb-claim span { flex: none; color: #aab1bc; user-select: none; }
        .wb-final-note { margin-top: 1.1rem; color: #aab1bc; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and prices; only the lighting changes.
           --------------------------------------------------------------- */
        #wb .wb-plans > section { background: var(--wb-desk-2); border-top: 1px solid var(--wb-line); }
        #wb .wb-plans h2 { font-family: var(--wb-sans); font-weight: 700; letter-spacing: -0.02em; color: var(--wb-ink); }
        #wb .wb-plans h2 + p { color: var(--wb-ink-2); font-size: 1.0625rem; }
        #wb .wb-plans .grid > div { background: var(--wb-panel); border: 1px solid var(--wb-line-2); border-radius: 0.6rem; color: var(--wb-ink); }
        #wb .wb-plans .grid > div:nth-child(2) { border-color: transparent; box-shadow: 0 0 0 3px #ff3b30, 0 0 2.2rem -0.5rem rgba(255, 59, 48, 0.7); }
        #wb .wb-plans .grid > div span,
        #wb .wb-plans .grid > div p,
        #wb .wb-plans .grid > div li { color: var(--wb-ink-2); }
        #wb .wb-plans .grid > div .text-3xl { font-family: var(--wb-mono); color: var(--wb-ink); }
        #wb .wb-plans .grid > div .uppercase { font-family: var(--wb-mono); color: var(--wb-ink); }
        #wb .wb-plans .grid > div .rounded-full { border-radius: 0.2rem; background: #ff3b30; color: #14161a; font-family: var(--wb-mono); }
        #wb .wb-plans .grid > div svg { color: var(--wb-ok); }
        #wb .wb-plans a.font-medium { color: var(--wb-ink); text-decoration: underline; text-decoration-color: #ff3b30; text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        #wb .wb-plans a.rounded-2xl {
            border-radius: 0.5rem;
            background-color: #ff3b30;
            background-image: linear-gradient(180deg, #ff6a5e 0%, #ff3b30 50%, #f2352a 100%);
            color: #14161a;
            font-weight: 700;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.55), inset 0 -3px 0 rgba(0, 0, 0, 0.2), 0 0 0 3px #17191d, 0 0.34rem 0 #17191d, 0 0 1.7rem -0.3rem rgba(255, 59, 48, 0.85);
        }
        #wb .wb-plans a.rounded-2xl:hover { transform: translateY(-1px); }

        #wb .wb-keep > section { background: var(--wb-desk); border-top: 1px solid var(--wb-line); }
        #wb .wb-keep h2 { font-family: var(--wb-sans); font-weight: 700; letter-spacing: -0.02em; color: var(--wb-ink); }
        #wb .wb-keep p.uppercase { font-family: var(--wb-mono); font-weight: 700; letter-spacing: 0.14em; color: var(--wb-red-ink); }
        #wb .wb-keep .grid > a { background: var(--wb-panel); border: 1px solid var(--wb-line-2); border-radius: 0.6rem; }
        #wb .wb-keep .grid > a:hover { border-color: transparent; box-shadow: 0 0 0 3px rgba(255, 59, 48, 0.85); }
        #wb .wb-keep .grid > a > span:first-child { display: none; }
        #wb .wb-keep .grid > a h3 { color: var(--wb-ink); }
        #wb .wb-keep .grid > a p { color: var(--wb-ink-2); }
        #wb .wb-keep .grid > a > span:last-child,
        #wb .wb-keep a.self-start { color: var(--wb-red-ink); }

        /* ---------------------------------------------------------------
           The tally rail: section index, on very wide rooms only
           --------------------------------------------------------------- */
        /* The nav is a box the size of the page that clips the rail, so the rail stays fixed to the
           screen and still ends where the page does instead of riding over the site footer. */
        .wb-rail { display: none; }
        @media (min-width: 1580px) {
            .wb-rail { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .wb-rail ol { position: fixed; left: 1.1rem; top: 50%; translate: 0 -50%; display: grid; pointer-events: auto; }
            .wb-rail a { display: flex; align-items: center; gap: 0.55rem; min-height: 1.5rem; color: var(--wb-ink-3); font-size: 0.6rem; }
            .wb-rail a::before { content: ""; width: 0.5rem; aspect-ratio: 1; border-radius: 50%; background: var(--wb-line-2); transition: background-color 0.3s ease, box-shadow 0.3s ease; }
            .wb-rail a span { opacity: 0; translate: -0.25rem 0; transition: opacity 0.25s ease, translate 0.25s ease; }
            .wb-rail a:hover span, .wb-rail a:focus-visible span, .wb-rail a.is-active span { opacity: 1; translate: 0 0; }
            .wb-rail a.is-active { color: var(--wb-ink); }
            .wb-rail a.is-active::before { background: #ff3b30; box-shadow: 0 0 0.6rem #ff3b30; }
        }

        @media (prefers-reduced-motion: reduce) {
            .wb-bug i, .wb-sign-air, .wb-clock-lit { animation: none; }
            .wb-btn, .wb-btn svg, .wb-cam, .wb-shot, .wb-run-bar, .wb-seats i, .wb-tl-cells span, .wb-l3-tc, .wb-l3-name, .wb-l3-title, .wb-screen, .wb-item { transition: none; }
        }
    </style>

    @php
        // A one-hour product webinar's running order. These are event parts:
        // name plus an optional start and end time, typed in on the Agenda
        // section of the event, ungated. Each bar is a percentage of the SAME
        // track width, so the number is minutes/60 rounded and equal durations
        // must carry equal widths - the two five-minute segments both read 8.
        $rundown = [
            ['14:00', 'Welcome and housekeeping', '5 min', 8],
            ['14:05', 'What shipped this quarter', '15 min', 25],
            ['14:20', 'Live walkthrough', '20 min', 33],
            ['14:40', 'Questions from the room', '15 min', 25],
            ['14:55', 'Where to go next', '5 min', 8],
        ];

        // Twelve labels, one field. The LED is each platform's own brand
        // colour; the label is page ink, so the colour never has to carry
        // any text contrast. Event Schedule holds no account on any of them.
        $jacks = [
            ['Zoom', '#2D8CFF'],
            ['Google Meet', '#00832D'],
            ['Microsoft Teams', '#6264A7'],
            ['Webex', '#00BCEB'],
            ['YouTube Live', '#FF0000'],
            ['Twitch', '#9146FF'],
            ['Instagram Live', '#E1306C'],
            ['Vimeo', '#1AB7EA'],
            ['Whereby', '#39D2C0'],
            ['Jitsi Meet', '#1D76BA'],
            ['StreamYard', '#EE4B4B'],
            ['A page you host yourself', '#0b6b60'],
        ];

        // A weekly series: thirteen Thursdays with the sixth skipped for a
        // holiday, which is the twelve sessions the copy states, plus a hard
        // out so the recurrence ends itself instead of running on.
        $weeks = [];
        foreach (range(1, 14) as $w) {
            if ($w === 14) {
                $weeks[] = 'out';
            } elseif ($w === 6) {
                $weeks[] = 'skip';
            } else {
                $weeks[] = 'on';
            }
        }

        $faqs = [
            [
                'q' => 'What video platforms does Event Schedule work with?',
                'a' => 'Any platform that gives you a meeting or streaming link. Zoom, Google Meet, Microsoft Teams, Webex, YouTube Live, Twitch, Vimeo, something you host yourself. An online event carries one Event URL field, and Event Schedule stores whatever you paste into it. To be plain about what that means: there is no account connected to a platform, nothing signs in on your behalf and no meeting is started for you, so there is also nothing to reconnect and nothing that breaks when a platform changes its API. The one exception runs the other way: if you sync a Microsoft 365 calendar you can ask it to create a Teams meeting for online events, and the join link it returns is written back into the field for you.',
            ],
            [
                'q' => 'Is the join link visible to the public?',
                'a' => 'No. The public event page shows the domain of your link as the location, as plain text rather than something to click, and neither the downloadable calendar file nor your schedule\'s live calendar feed carries it: an online session goes out in both with no location at all. The full link sits on the registration page each attendee is given after they sign up, which is reachable only through the private address in their confirmation. A public listing is not an open door.',
            ],
            [
                'q' => 'Can I charge for webinars?',
                'a' => 'Yes, on the Pro plan at '.plan_price($proMonthly).' a month, which is what lets a ticket carry a price. Take payment through your own Stripe or PayPal account, or through Invoice Ninja, a payment link or cash, add as many named ticket types as the session needs, each with its own price, quantity and sales window, and Event Schedule charges zero platform fees on every plan. The provider charges its own processing fee; Stripe\'s standard rate is approximately 2.9% plus $0.30 a transaction. Scanning a ticket\'s QR code is free on every plan, for the sessions you also run in a room. Pro brings the rest of the door tooling with it: the live check-in dashboard, the sold-out ticket waitlist, promo codes and add-ons. Free registration with a capacity limit is unlimited on every plan, including free.',
            ],
            [
                'q' => 'Can I schedule a recurring webinar series?',
                'a' => 'Yes, on the free plan. Set the days of the week it runs, add date exceptions for the weeks you are skipping, and give the recurrence an end: either a closing date or a number of sessions, so a series that is meant to be twelve weeks long stops after twelve. Registration capacity is counted per session date, so next week starts empty even though it is the same event.',
            ],
            [
                'q' => 'Do people who registered find out if I move the session?',
                'a' => 'Yes, once you say so. Change the join link or the venue and Event Schedule stops on the way to saving and asks whether to email everyone who registered, with a short note you can write into it; cancelling a session emails them as part of cancelling, with the same kind of note, and on eventschedule.com registrants get both when your schedule sends through its own email settings. On a one-off session moving the date or the time asks too, though on a recurring series the prompt covers the link and the venue rather than the weekly time. Free registrations are on the list either way, and so is anyone who left only an email address on the event page to hear about it, whose copy never carries the join link. Followers are different, and there are two of them. Somebody signed in who pressed Follow is on a list only a newsletter you write reaches; pressing Follow on its own sends nothing to an account follower automatically when you add a session. Somebody who left an email address on your page and confirmed it is on the other list, and a new session does reach them on its own, as a digest rather than a message per webinar.',
            ],
            [
                'q' => 'Can people get a reminder without registering?',
                'a' => 'Yes, on every plan, once you switch on the "Notify me" card. A public session\'s page then carries a short form: somebody leaves an email address, with no account and no name, and gets a reminder 48 hours before the session starts, word if it is cancelled, and any change notice you choose to send. On a paid session that is not on sale yet the form reads "Tell me when tickets go on sale", and they also get one email when it is. None of those emails carries the join link; each one points at the public page, so registering is still the way into the room. The list is not a subscription to your schedule, unsubscribing deletes the address, and the event editor\'s Tickets panel shows how many people are waiting.',
            ],
            [
                'q' => 'Can I refund a webinar ticket?',
                'a' => 'Yes, from the Sales page, on Pro. A Stripe or PayPal sale goes back through the provider, in full or in part, and its status changes only once the money has moved. A partial refund leaves the ticket valid, so the attendee\'s page and its join link keep working; only a full refund cancels it and returns the place to the session. Sales taken by Invoice Ninja, a payment link or cash are marked as refunded instead, which records the refund without moving any money.',
            ],
            [
                'q' => 'Is Event Schedule free for hosting webinars?',
                'a' => 'Yes. Unlimited webinars, the running order on each one, recurring series, free registration with a capacity limit, two-way calendar sync, the embeddable calendar and built-in analytics are all free forever, with no monthly ceiling, and so is scanning a ticket in at the door. Charging for a seat is Pro at '.plan_price($proMonthly).' a month, which also adds the live check-in dashboard, custom questions on the registration form and the sold-out ticket waitlist, extra team members are on Enterprise, and there are zero platform fees on ticket sales at every plan level. On the hosted service, attendee email goes out through your own SMTP details, which you add once in the integrations tab on any plan.',
            ],
        ];

        $dotSections = [
            ['top', 'On air'],
            ['path', 'The signal path'],
            ['platform', 'The patch bay'],
            ['rundown', 'The rundown'],
            ['series', 'The series'],
            ['register', 'Registration'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Go live'],
        ];
    @endphp

    @php
        // Page furniture for the control room: icons, the six "cameras" of the
        // sub-audiences, and the shot box. None of it states anything about the product
        // beyond the names, descriptions and links carried over from the cards it replaces.
        $wbArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $wbDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';

        $wbChips = ['Product demos', 'Training sessions', 'Workshops', 'Panel discussions', 'All-hands', 'Lectures', 'Q&A sessions', 'Onboarding', 'Office hours', 'Launch briefings'];

        // Name, description, blog slug, and the two hues of that camera's picture.
        $wbCams = [
            ['Product Demos', 'Show the product on a standing slot, cap the room, and keep the join link off the public listing.', 'for-product-demos', 176, 200],
            ['Training & Onboarding', 'One recurring event covers the whole intake, with capacity counted separately for every week.', 'for-training-onboarding', 36, 14],
            ['Educational Lectures', 'Publish the running order so students can see what each session covers before they sign up.', 'for-educational-lectures', 204, 168],
            ['Industry Panels', 'Put the panellists in the running order so people can read who is on before they sign up.', 'for-industry-panels', 140, 176],
            ['Company All-Hands', 'Keep the standing slot on a schedule, and hold the ones you have not announced as drafts.', 'for-company-all-hands', 12, 40],
            ['Customer Workshops', 'Charge for the hands-on ones through your own Stripe or PayPal account, and keep every penny past processing.', 'for-customer-workshops', 50, 150],
        ];

        $wbShots = [
            ['F1', 'Online Events', 'One link field, so any meeting or streaming platform works', marketing_url('/features/online-events')],
            ['F2', 'Recurring Events', 'A weekly series as one event, with skipped dates and an end', marketing_url('/features/recurring-events')],
            ['F3', 'Analytics', 'Track page views, devices, and traffic sources', marketing_url('/features/analytics')],
            ['F4', 'Newsletters', 'Write to the people who follow your schedule, with open rates', marketing_url('/features/newsletters')],
        ];
    @endphp

    <div id="wb">

        <nav class="wb-rail es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot wb-mono"><span>{{ $sectionLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the monitor wall. Programme, preview, and the clock -->
        <!-- ============================================================ -->
        <section id="top" class="wb-hero" style="scroll-margin-top: 5rem;">
            <div class="wb-wrap wb-room">
                <div class="wb-wall">
                    <div class="wb-mon wb-pgm es-fade-up es-d-1">
                        <div class="wb-screen">
                            <div class="wb-bug wb-mono" aria-hidden="true"><span><i></i>Live</span><span>Gallery &middot; Studio A</span></div>
                            <div>
                            <h1 class="wb-h1">
                                <x-marketing.hero-eyebrow class="wb-eyebrow">Event schedule for webinars</x-marketing.hero-eyebrow>
                                <span class="es-mask"><span class="es-mask-line">Announce the session.</span></span>
                                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="wb-hi">Not the link.</span></span></span>
                            </h1>
                                <p class="wb-pgm-lede es-fade-up es-d-2">
                                    Your public schedule shows the webinar, the time and the bare domain of wherever it is happening. The join link itself rides on the registration each attendee gets, so publishing a session is not the same as leaving the room open.
                                </p>
                            </div>
                            <div class="wb-pgm-l3 wb-mono" aria-hidden="true"><span>Two outputs, one field</span><span>Event URL</span></div>
                        </div>
                        <div class="wb-umd" aria-hidden="true"><b>PGM</b><span>Programme</span></div>
                    </div>

                    <div class="wb-side es-fade-up es-d-3">
                        <div class="wb-mon wb-feed wb-pvw">
                            <div class="wb-screen">
                                <div class="wb-feed-head wb-mono"><span>PVW &middot; public listing</span><span>ANYONE</span></div>
                                <p class="wb-feed-title">Product Deep Dive</p>
                                <p class="wb-feed-meta">Thu 14:00 &middot; 60 min</p>
                                <p class="wb-feed-line"><i aria-hidden="true"></i><span>zoom.us</span></p>
                                <p class="wb-feed-note">The domain, as plain text. Nothing to click.</p>
                            </div>
                            <div class="wb-umd" aria-hidden="true"><b>PVW</b><span>Out 1</span></div>
                        </div>

                        <div class="wb-take wb-mono" aria-hidden="true"><span>Take {!! $wbDown !!}</span></div>

                        <div class="wb-mon wb-feed">
                            <div class="wb-screen">
                                <div class="wb-feed-head wb-mono"><span>PGM &middot; after registering</span><span>THEM ONLY</span></div>
                                <p class="wb-feed-reg wb-mono"><i aria-hidden="true"></i>Registered</p>
                                <p class="wb-feed-line" style="--c: #32d74b;"><i aria-hidden="true"></i><span>https://zoom.us/j/8814920733</span></p>
                                <p class="wb-feed-note">On their own registration page, linked from their confirmation email.</p>
                            </div>
                            <div class="wb-umd" aria-hidden="true"><b>AUX</b><span>Out 2</span></div>
                        </div>

                        <div class="wb-tiles" aria-hidden="true">
                            <div class="wb-mon wb-tile">
                                <div class="wb-screen">
                                    <span class="wb-clock"><i class="wb-clock-ticks"></i><i class="wb-clock-lit"></i><b>14:00</b></span>
                                </div>
                                <div class="wb-umd"><b>Clock</b><span>UTC</span></div>
                            </div>
                            <div class="wb-mon wb-tile">
                                <div class="wb-screen">
                                    <span class="wb-mono">On air in</span>
                                    <b class="wb-tc"><span>00:14:07:12</span></b>
                                    <span class="wb-sign">Standby</span>
                                </div>
                                <div class="wb-umd"><b>Count</b><span>To air</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wb-desk">
                    <div>
                        <div class="wb-cta es-fade-up es-d-3">
                            <a href="#path" class="wb-btn wb-btn-2">
                                Follow the signal path
                                {!! $wbDown !!}
                            </a>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="wb-btn">
                                Create your webinar schedule
                                {!! $wbArrow !!}
                            </a>
                        </div>
                        <p class="wb-lede-2 es-fade-up es-d-4">
                            Webinar scheduling with built-in registration, paid ticketing at zero platform fees, recurring series and two-way calendar sync, for educators, marketers and internal comms teams.
                        </p>
                    </div>
                    <p class="wb-cap es-fade-up es-d-4">
                        <span class="wb-mono" aria-hidden="true">Out 1 + Out 2</span>
                        One Event URL field on the event feeds both. Add a venue as well and the session goes out hybrid: the address in public, the link on the registration.
                    </p>
                </div>
            </div>

            <!-- Session types, on the ticker under the wall -->
            <div class="wb-ticker es-fade-up es-d-5">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                            @foreach ($wbChips as $chip)
                                <span @if ($chipCopy === 1) aria-hidden="true" @endif class="wb-chip">{{ $chip }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The signal path: three outputs, in order                  -->
        <!-- ============================================================ -->
        <section id="path" class="wb-sec" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">02:00:00:00</span>
                    <h2 class="wb-l3-name">Where the join link actually <em class="wb-hi">goes.</em></h2>
                    <p class="wb-l3-title wb-mono">The signal path</p>
                </div>
                <p class="wb-sub" data-reveal style="margin-bottom: 2rem;">
                    Three surfaces, in the order a webinar hits them.
                </p>

                <div class="wb-outs wb-glass" data-reveal-group="120">
                    <div class="wb-mon wb-out wb-pvw" data-reveal>
                        <div class="wb-screen">
                            <p class="wb-out-no wb-mono">01 &middot; Public</p>
                            <h3>The listing anyone can read</h3>
                            <p>Title, date, time, description, running order, and the domain of your link shown as plain text. The downloadable calendar file for an online session carries no location at all.</p>
                        </div>
                        <div class="wb-umd" aria-hidden="true"><b>Out 1</b><span>Anyone</span></div>
                    </div>
                    <div class="wb-mon wb-out wb-pgm" data-reveal>
                        <div class="wb-screen">
                            <p class="wb-out-no wb-mono">02 &middot; Registered</p>
                            <h3>The page only they have</h3>
                            <p>Registering hands each attendee their own page, at a private address, carrying the join link. Their confirmation email links straight back to it, so nobody has to keep a message to find the room.</p>
                        </div>
                        <div class="wb-umd" aria-hidden="true"><b>Out 2</b><span>Them only</span></div>
                    </div>
                    <div class="wb-mon wb-out wb-sby" data-reveal>
                        <div class="wb-screen">
                            <p class="wb-out-no wb-mono">03 &middot; Changed</p>
                            <h3>When you move it</h3>
                            <p>Change the join link or the venue and Event Schedule stops on the way to saving to ask whether to email everyone who registered. Free registrations count, and cancelling emails them as part of cancelling. People who only left an email address to hear about the session get the notice too, still without the link.</p>
                        </div>
                        <div class="wb-umd" aria-hidden="true"><b>Out 3</b><span>On change</span></div>
                    </div>
                </div>

                <!-- Three numbers that are true, on the readouts under the wall -->
                <div class="wb-reads" data-reveal>
                    <div class="wb-read">
                        <b>1</b>
                        <span class="wb-mono">Link field per event</span>
                    </div>
                    <div class="wb-read">
                        <b>{{ plan_price(0) }}</b>
                        <span class="wb-mono">Platform fee on sales</span>
                    </div>
                    <div class="wb-read">
                        <b>3</b>
                        <span class="wb-mono">Calendars synced two ways</span>
                    </div>
                </div>

                <p class="wb-note" data-reveal>
                    On the hosted service the confirmation goes out either way, but change and cancellation notices reach registrants only once your own SMTP details are in the integrations tab, which any plan can do.
                    <a href="#platform" class="wb-a wb-next">
                        Next, the patch bay
                        {!! $wbDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The patch bay: any link, and no integration to break      -->
        <!-- ============================================================ -->
        <section id="platform" class="wb-sec wb-alt" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">03:00:00:00</span>
                    <h2 class="wb-l3-name">Twelve labels. <em class="wb-hi">One field.</em></h2>
                    <p class="wb-l3-title wb-mono">The patch bay</p>
                </div>
                <p class="wb-sub" data-reveal style="margin-bottom: 2.25rem;">
                    Everything below terminates in the same place: the Event URL box on your webinar. Which is another way of saying Event Schedule does not hold an account on any of them.
                </p>

                <div class="wb-two">
                    <div class="wb-router wb-follow wb-glass" data-reveal>
                        <div class="wb-router-head wb-mono">
                            <p>Inputs</p>
                            <p>LED = brand</p>
                        </div>
                        <div class="wb-srcs">
                            @foreach ($jacks as [$jackName, $jackColor])
                                <div class="wb-src" style="--led: {{ $jackColor }};">
                                    <i aria-hidden="true"></i>
                                    {{ $jackName }}
                                </div>
                            @endforeach
                        </div>
                        <div class="wb-bus" aria-hidden="true"></div>
                        <div class="wb-outp">
                            <p class="wb-mono">Output</p>
                            <code>event_url</code>
                            <span class="wb-tier">Free</span>
                        </div>
                    </div>

                    <div class="wb-tb" data-reveal-group="100">
                        <div class="wb-tb-row" data-reveal>
                            <span class="wb-key wb-key-green" aria-hidden="true">No<br>login</span>
                            <div class="wb-tb-head">
                                <h3>Nothing to reconnect</h3>
                            </div>
                            <p>No sign-in, no token, no account linked to your meeting provider. Event Schedule stores the string you paste and hands it to the people who registered, which means a platform changing its API cannot break your schedule.</p>
                        </div>
                        <div class="wb-tb-row" data-reveal>
                            <span class="wb-key wb-key-cyan" aria-hidden="true">Teams</span>
                            <div class="wb-tb-head">
                                <h3>One exception, and it works backwards</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>Sync a Microsoft 365 calendar and you can ask it to create a Teams meeting for your online sessions. The join link it returns is written back into the Event URL field for you, so you never copy it by hand.</p>
                        </div>
                        <div class="wb-tb-row" data-reveal>
                            <span class="wb-key wb-key-amber" aria-hidden="true">Hybrid</span>
                            <div class="wb-tb-head">
                                <h3>Half online, half in a room</h3>
                            </div>
                            <p>Give the session a venue as well as a link and it is published as hybrid, with the address for the people in the room and the link for the people who are not. Learn how <a href="{{ marketing_url('/features/online-events') }}" class="wb-a">online events</a> are put together.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The rundown: the running order, as a real table           -->
        <!-- ============================================================ -->
        <section id="rundown" class="wb-sec" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">04:00:00:00</span>
                    <h2 class="wb-l3-name">An hour is not <em class="wb-hi">one block.</em></h2>
                    <p class="wb-l3-title wb-mono">The rundown</p>
                </div>
                <p class="wb-sub" data-reveal style="margin-bottom: 2.25rem;">
                    A webinar has segments, and people decide whether to come by reading them. Type the running order onto the event and it publishes with it, on the free plan.
                </p>

                <div class="wb-run wb-glass" data-reveal>
                    <div class="wb-run-head">
                        <div>
                            <h3>Product Deep Dive</h3>
                            <p class="wb-mono">Thu 14:00 &middot; 60 min &middot; 5 segments</p>
                        </div>
                        <span class="wb-tier">Free</span>
                    </div>
                    <table>
                        <caption class="sr-only">Running order for a one-hour product webinar, with each segment's start time and duration</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="wb-mono">In</th>
                                <th scope="col" class="wb-mono">Segment</th>
                                <th scope="col" class="wb-mono">Dur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rundown as $rowIndex => [$rIn, $rName, $rDur, $rShare])
                                @php
                                    // Where the segment starts along the hour, from its own In time.
                                    [$rHour, $rMin] = array_map('intval', explode(':', $rIn));
                                    $rAt = round((($rHour * 60 + $rMin) - 14 * 60) / 60 * 100, 2);
                                    $rSrc = ['Live', 'GFX', 'Live', 'Live', 'GFX'][$rowIndex] ?? 'Live';
                                @endphp
                                <tr>
                                    <td class="wb-run-in">{{ $rIn }}</td>
                                    <th scope="row">
                                        <span class="wb-run-seg">
                                            {{ $rName }}
                                            <span class="wb-run-src wb-mono" aria-hidden="true">{{ $rSrc }}</span>
                                        </span>
                                        <span class="wb-run-track" aria-hidden="true"><span class="wb-run-bar" style="--at: {{ $rAt }}%; --w: {{ $rShare }}%;"></span></span>
                                    </th>
                                    <td class="wb-run-dur">{{ $rDur }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="wb-foot" data-reveal>Each segment is a named part with an optional start and end time, moved up or down into order. The bar is its share of the hour.</p>

                <div class="wb-cards" data-reveal-group="100">
                    <div class="wb-card" data-reveal>
                        <div class="wb-card-head">
                            <h3>Paste the agenda instead</h3>
                            <span class="wb-tier wb-tier-ent">Enterprise</span>
                        </div>
                        <p>Hand a written agenda to the scanner and the segments are created for you. Typing them yourself is free, so this buys back the typing and nothing else.</p>
                    </div>
                    <div class="wb-card" data-reveal>
                        <div class="wb-card-head">
                            <h3>Let the room vote</h3>
                            <span class="wb-tier wb-tier-pro">Pro</span>
                        </div>
                        <p>Add a poll to the session and let people pick which of two walkthroughs the second half should be. One thing to know before you plan around it: casting a vote needs the voter signed in to Event Schedule, so a name and an email at registration is not enough.</p>
                    </div>
                    <div class="wb-card" data-reveal>
                        <div class="wb-card-head">
                            <h3>Ask afterwards</h3>
                            <span class="wb-tier wb-tier-pro">Pro</span>
                        </div>
                        <p>Collect a star rating and a comment from attendees once the session is over, so the next rundown is written from something better than a hunch.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The series: recurrence with a hard out                    -->
        <!-- ============================================================ -->
        <section id="series" class="wb-sec wb-alt" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">05:00:00:00</span>
                    <h2 class="wb-l3-name">Twelve Thursdays, and then it <em class="wb-hi">stops.</em></h2>
                    <p class="wb-l3-title wb-mono">The series</p>
                </div>

                <div class="wb-two">
                    <!-- The section's own script, on the prompter -->
                    <div class="wb-stack">
                        <div class="wb-prompter" data-reveal>
                            <div class="wb-prompter-head wb-mono" aria-hidden="true"><span>Prompter</span><span>Read line</span></div>
                            <div class="wb-prompter-glass">
                                <p class="wb-prompter-text">
                                    A weekly webinar is one recurring event, not twelve entries. Set the days it runs, take out the weeks you are skipping, and give the recurrence an end so it is not still advertising itself in March.
                                </p>
                            </div>
                        </div>
                        <div class="wb-card" data-reveal>
                            <div class="wb-card-head">
                                <h3>A format you run again and again</h3>
                                <span class="wb-tier wb-tier-pro">Pro</span>
                            </div>
                            <p>Save a session as a template and start the next one from it. On any plan you can also clone an existing webinar, which covers most of the same ground for the price of nothing.</p>
                        </div>
                        <div class="wb-card" data-reveal>
                            <div class="wb-card-head">
                                <h3>Several strands on one link</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>Sub-schedules sort and colour-code your sessions, so a customer-training strand reads separately from the launch briefings. They organise; they do not hide. To keep something unpublished, leave it a draft.</p>
                        </div>
                    </div>

                    <div class="wb-playout" data-reveal>
                        <div class="wb-playout-head">
                            <h3>Onboarding Live</h3>
                            <span class="wb-mono">Weekly &middot; Thursdays 14:00 &middot; 12 sessions</span>
                        </div>

                        <div aria-hidden="true">
                            <div class="wb-tl">
                                <div class="wb-tl-ruler"></div>
                                <div class="wb-tl-cells">
                                    @foreach ($weeks as $wIndex => $wState)
                                        <span class="@if ($wState === 'on') wb-tl-on @elseif ($wState === 'skip') wb-tl-skip @else wb-tl-out @endif" style="--i: {{ $wIndex }};"></span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="wb-legend wb-mono">
                                <span><i></i>Solid = a session</span>
                                <span><i></i>Dashed = date exception</span>
                                <span><i></i>Line = hard out</span>
                            </div>
                        </div>

                        <div class="wb-cards">
                            <div class="wb-card">
                                <div class="wb-card-head">
                                    <h4>The days it runs</h4>
                                    <span class="wb-tier">Free</span>
                                </div>
                                <p>Pick the days of the week and one start time. Thursdays at two is a single event that keeps producing Thursdays.</p>
                            </div>
                            <div class="wb-card">
                                <div class="wb-card-head">
                                    <h4>The weeks you skip</h4>
                                    <span class="wb-tier">Free</span>
                                </div>
                                <p>Date exceptions take individual dates out, and a removed date is simply absent from the calendar rather than shown crossed through.</p>
                            </div>
                            <div class="wb-card">
                                <div class="wb-card-head">
                                    <h4>The end</h4>
                                    <span class="wb-tier">Free</span>
                                </div>
                                <p>A closing date, or a number of sessions. This is the setting that makes a series a series instead of a weekly slot nobody remembers to switch off.</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Registration: the house counter and the talkback panel    -->
        <!-- ============================================================ -->
        <section id="register" class="wb-sec" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">06:00:00:00</span>
                    <h2 class="wb-l3-name">Counted per session, <em class="wb-hi">not per series.</em></h2>
                    <p class="wb-l3-title wb-mono">Registration</p>
                </div>
                <p class="wb-sub" data-reveal style="margin-bottom: 2.25rem;">
                    Free registration takes a name and an email, holds a place, and caps the room if you want it capped. The count belongs to the date, so a full session this Thursday does not make next Thursday look full.
                </p>

                <div class="wb-two">
                    <div class="wb-follow" data-reveal>
                        <div class="wb-counter wb-glass" aria-hidden="true">
                            <p class="wb-mono">Thu 14:00 &middot; places taken</p>
                            <p class="wb-counter-big">84 / 120</p>
                            <div class="wb-seats"><i></i></div>
                            <div class="wb-counter-row wb-mono">
                                <span>36 left on this date</span>
                            </div>
                            <div class="wb-counter-next">
                                <div class="wb-counter-row wb-mono" style="margin: 0 0 0.6rem;">
                                    <span style="color: #aab1bc;">Next Thu: 0 / 120</span>
                                </div>
                                <div class="wb-seats"></div>
                            </div>
                        </div>
                        <p class="wb-foot">Each registration becomes its own page carrying the join link, and its confirmation email links back to it.</p>
                    </div>

                    <div class="wb-tb" data-reveal-group="90">
                        <div class="wb-tb-row" data-reveal>
                            <span class="wb-key wb-key-green" aria-hidden="true">Cap</span>
                            <div class="wb-tb-head">
                                <h3>Free registration with a cap</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>Turn it on, set the limit, and Event Schedule stops taking names when the date is full. No plan, no card, no platform fee, because nothing changed hands.</p>
                        </div>
                        <div class="wb-tb-row" data-reveal>
                            <span class="wb-key wb-key-amber" aria-hidden="true">Paid</span>
                            <div class="wb-tb-head">
                                <h3>Charging for the session</h3>
                                <span class="wb-tier wb-tier-pro">Pro</span>
                            </div>
                            <p>Take payment through your own <a href="{{ marketing_url('/stripe') }}" class="wb-a">Stripe</a> or <a href="{{ marketing_url('/paypal') }}" class="wb-a">PayPal</a> account, or Invoice Ninja, a payment link or cash, and add named ticket types, each with its own price, quantity and sales window. A ticket with a price on it is Pro; scanning its QR code is free on any plan if the session also has a room, and Pro adds the live check-in dashboard. Event Schedule takes zero platform fees either way, so past the provider's own processing the money is yours. See <a href="{{ marketing_url('/features/ticketing') }}" class="wb-a">how ticketing works</a>.</p>
                        </div>
                        <div class="wb-tb-row" data-reveal>
                            <span class="wb-key" aria-hidden="true">Ask</span>
                            <div class="wb-tb-head">
                                <h3>Ask what you need to know</h3>
                                <span class="wb-tier wb-tier-pro">Pro</span>
                            </div>
                            <p>Custom questions on the form collect their job title, their team or the one thing they want covered, answered at the point of registering rather than chased afterwards.</p>
                        </div>
                        <div class="wb-tb-row" data-reveal>
                            <span class="wb-key wb-key-red" aria-hidden="true">Full</span>
                            <div class="wb-tb-head">
                                <h3>When a session fills up</h3>
                                <span class="wb-tier">Free</span>
                                <span class="wb-tier wb-tier-pro">Pro</span>
                            </div>
                            <p>A free session that hits its cap turns its own form into a waitlist, on every plan; the waitlist behind sold-out paid tickets is the Pro one. Either way, when a place comes back the person who has waited longest is emailed automatically and has 24 hours to take it.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Everything else: a multiview beside its shot list         -->
        <!-- ============================================================ -->
        <section id="rest" class="wb-sec wb-alt" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">07:00:00:00</span>
                    <h2 class="wb-l3-name">The rest of the webinar rack</h2>
                    <p class="wb-l3-title wb-mono">Everything else</p>
                </div>

                <div class="wb-rest">
                    <div class="wb-mv" aria-hidden="true" data-reveal>
                        <div class="wb-mon"><div class="wb-screen" style="--a1: rgba(50, 215, 75, 0.5); --a2: rgba(53, 201, 230, 0.3);"><span>@</span></div><div class="wb-umd"><b>MV 1</b><span>Mail</span></div></div>
                        <div class="wb-mon"><div class="wb-screen" style="--a1: rgba(53, 201, 230, 0.5); --a2: rgba(255, 176, 32, 0.25);"><span>&lt;/&gt;</span></div><div class="wb-umd"><b>MV 2</b><span>Embed</span></div></div>
                        <div class="wb-mon"><div class="wb-screen" style="--a1: rgba(255, 176, 32, 0.45); --a2: rgba(50, 215, 75, 0.3);"><span>CAL</span></div><div class="wb-umd"><b>MV 3</b><span>Sync</span></div></div>
                        <div class="wb-mon"><div class="wb-screen" style="--a1: rgba(244, 245, 247, 0.3); --a2: rgba(255, 59, 48, 0.4);"><span>QR</span></div><div class="wb-umd"><b>MV 4</b><span>GFX</span></div></div>
                        <div class="wb-mon"><div class="wb-screen" style="--a1: rgba(255, 176, 32, 0.3); --a2: rgba(244, 245, 247, 0.16);"><span>STBY</span></div><div class="wb-umd"><b>MV 5</b><span>Draft</span></div></div>
                        <div class="wb-mon"><div class="wb-screen" style="--a1: rgba(255, 59, 48, 0.4); --a2: rgba(53, 201, 230, 0.35);"><span>{&nbsp;}</span></div><div class="wb-umd"><b>MV 6</b><span>API</span></div></div>
                    </div>

                    <div class="wb-list">
                        <!-- 1 -->
                        <div class="wb-item" data-reveal>
                            <div class="wb-item-head">
                                <span class="wb-item-no wb-mono" aria-hidden="true">MV 1</span>
                                <h3>Email the people who already turn up</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>People follow your schedule, which puts them on a list you can write to: the next series announced, the recording posted, the thing you promised to send. A list can also be cut down to just the people who registered for one particular session, which is usually the list you actually wanted. Open and click rates afterwards tell you whether it landed.</p>
                            <p>The number worth knowing first: 10 emails a month on Free, 100 on Pro, 1,000 on Enterprise, counted per recipient rather than per send. A newsletter never sends itself. What does go out on its own is a digest of the new sessions you publish, to the people who confirmed an email sign-up on your page, at most one every 72 hours and outside that allowance; pressing Follow alone signs nobody up for it.</p>
                        </div>
                        <!-- 2 -->
                        <div class="wb-item" data-reveal>
                            <div class="wb-item-head">
                                <span class="wb-item-no wb-mono" aria-hidden="true">MV 2</span>
                                <h3>On the site you already have</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>Embed the calendar on your own pages so the series lives where people look you up. The registration form embeds too, on the free plan; the ticket purchase form is on Pro.</p>
                        </div>
                        <!-- 3 -->
                        <div class="wb-item" data-reveal>
                            <div class="wb-item-head">
                                <span class="wb-item-no wb-mono" aria-hidden="true">MV 3</span>
                                <h3>In the calendar you live in</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>Two-way sync with Google, Outlook and CalDAV. Move a session in either place and the other one follows. A recurring series syncs across as one entry; the subscribe feed is what unrolls the individual dates. Your audience can subscribe to that feed from your schedule's public pages, with no email address, and it carries each session's public page, never the join link.</p>
                        </div>
                        <!-- 4 -->
                        <div class="wb-item" data-reveal>
                            <div class="wb-item-head">
                                <span class="wb-item-no wb-mono" aria-hidden="true">MV 4</span>
                                <h3>The closing slide, and the announcement image</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>Download a QR code for your schedule and put it on the last slide, so the people already watching can follow the series before they close the tab. That one costs nothing on any plan.</p>
                            <p>So does the next part: generate one share graphic of your next sessions, up to twenty of them, in a story, square, portrait or landscape crop. It is built from the sessions that carry their own image, so the titles and the times are already correct. Built-in analytics, free on every plan, then show page views, devices and where the traffic came from, which is what they measure and nothing more.</p>
                        </div>
                        <!-- 5 -->
                        <div class="wb-item" data-reveal>
                            <div class="wb-item-head">
                                <span class="wb-item-no wb-mono" aria-hidden="true">MV 5</span>
                                <h3>Announce when you are ready</h3>
                                <span class="wb-tier">Free</span>
                            </div>
                            <p>A session you have not announced sits on your calendar as a draft, yours to see and nobody else's, until you publish it. Internal and unlisted sessions, including a password, are on Enterprise.</p>
                        </div>
                        <!-- 6 -->
                        <div class="wb-item" data-reveal>
                            <div class="wb-item-head">
                                <span class="wb-item-no wb-mono" aria-hidden="true">MV 6</span>
                                <h3>More than one host, and the wiring underneath</h3>
                                <span class="wb-tier wb-tier-ent">Enterprise</span>
                                <span class="wb-tier wb-tier-pro">Pro</span>
                            </div>
                            <p>Being straight about this one: Free and Pro are a single team member. Extra people who can create and edit sessions are an Enterprise thing, capped at five, along with your own domain on the schedule.</p>
                            <p>On Pro there is a full REST API for events, schedules and sales, plus webhooks that fire on a sale, an event change or a check-in, if your registrations need to land somewhere else as well.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Perfect for: six cameras, each with its caption up        -->
        <!-- ============================================================ -->
        <section id="who" class="wb-sec" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">08:00:00:00</span>
                    <h2 class="wb-l3-name">Perfect for all types of <em class="wb-hi">webinars</em></h2>
                    <p class="wb-l3-title wb-mono" aria-hidden="true">Six cameras</p>
                </div>
                <p class="wb-sub" data-reveal style="margin-bottom: 2.25rem;">
                    A product demo and a company all-hands are the same shape: a time, a link, and a list of people who said they were coming. Running a whole multi-day programme? See Event Schedule for <a href="{{ marketing_url('/for-virtual-conferences') }}" class="wb-a">virtual conferences</a>.
                </p>

                <div class="wb-cams" data-reveal-group="70">
                    @foreach ($wbCams as $camIndex => [$camName, $camDesc, $camSlug, $camHue1, $camHue2])
                        @php $camPost = get_sub_audience_blog($camSlug); @endphp
                        <article class="wb-cam" data-reveal>
                            <div class="wb-cam-shot" style="--h1: {{ $camHue1 }}; --h2: {{ $camHue2 }};">
                                <span class="wb-cam-id wb-mono" aria-hidden="true">Cam {{ $camIndex + 1 }}</span>
                                <div class="wb-cam-l3"><h3>{{ $camName }}</h3></div>
                            </div>
                            <div class="wb-cam-body">
                                <p>{{ $camDesc }}</p>
                                @if ($camPost)
                                    <a href="{{ blog_url('/' . $camPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $camName }}">
                                        <span>Learn more {!! $wbArrow !!}</span>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Three steps: the countdown                                -->
        <!-- ============================================================ -->
        <section class="wb-sec wb-alt">
            <div class="wb-wrap">
                <div class="wb-l3" data-reveal="l3">
                    <span class="wb-l3-tc wb-mono" aria-hidden="true">09:00:00:00</span>
                    <h2 class="wb-l3-name">Three steps to a webinar people show up to</h2>
                    <p class="wb-l3-title wb-mono">Countdown</p>
                </div>

                <div class="wb-steps" data-reveal-group="140">
                    @foreach ([
                        ['03', 'Paste the link', 'Create the session, paste your meeting or streaming link into the Event URL field, and type the running order if it has segments.'],
                        ['02', 'Open registration', 'Free registration with a capacity limit, or named ticket types through your own Stripe or PayPal account. Either way the platform fee is zero.'],
                        ['01', 'Go on air', 'Everyone who registered has their own page with the join link. Swap the link and you are asked whether to email them all, which on eventschedule.com needs your schedule to have its own email settings.'],
                    ] as $stepIndex => [$stepNum, $stepTitle, $stepBody])
                        <div class="wb-step" data-reveal>
                            <div class="wb-step-top" aria-hidden="true">
                                <b>{{ $stepNum }}</b>
                                <span class="wb-sign @if ($stepIndex === 2) wb-sign-air @endif" @if ($stepIndex === 1) style="background: #32d74b; box-shadow: 0 0 1.1rem -0.2rem rgba(50, 215, 75, 0.9);" @endif>{{ ['Standby', 'Cue', 'On air'][$stepIndex] }}</span>
                            </div>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Key features: the shot box                               -->
        <!-- ============================================================ -->
        <section class="wb-sec">
            <div class="wb-wrap wb-feat">
                <div>
                    <h2 class="wb-feat-h" data-reveal>Key features</h2>
                    <p class="wb-feat-more" data-reveal style="--reveal-delay: 0.1s;">
                        <a href="{{ marketing_url('/features') }}" class="wb-a wb-next" style="margin: 0;">
                            See all features
                            {!! $wbArrow !!}
                        </a>
                    </p>
                </div>
                <div class="wb-shots" data-reveal-group="70">
                    @foreach ($wbShots as [$shotKey, $shotName, $shotDesc, $shotUrl])
                        <a href="{{ $shotUrl }}" class="wb-shot" data-reveal>
                            <span class="wb-key" aria-hidden="true">{{ $shotKey }}</span>
                            <span>
                                <strong>{{ $shotName }}</strong>
                                <small>{{ $shotDesc }}</small>
                            </span>
                            {!! $wbArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="wb-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 11. Related pages: the other studios                         -->
        <!-- ============================================================ -->
        <section class="wb-sec">
            <div class="wb-wrap">
                <div class="wb-rel-head">
                    <h2 class="wb-feat-h" data-reveal>Related pages</h2>
                    <a href="{{ marketing_url('/use-cases') }}" class="wb-a wb-next" data-reveal>
                        See all use cases
                        {!! $wbArrow !!}
                    </a>
                </div>
                <div class="wb-studios wb-glass" data-reveal-group="80">
                    @foreach ([['/for-virtual-conferences', 'Virtual Conferences'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions'], ['/for-workshop-instructors', 'Workshop Instructors']] as $relIndex => [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="wb-mon wb-studio" data-reveal style="--h1: {{ [176, 36, 204, 140][$relIndex] }};">
                            <span class="wb-screen">
                                <strong>For {{ $relName }}</strong>
                                <small class="wb-mono">Read more {!! $wbArrow !!}</small>
                            </span>
                            <span class="wb-umd" aria-hidden="true"><b>Studio {{ ['B', 'C', 'D', 'E'][$relIndex] }}</b><span>Next door</span></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 12. FAQ                                                      -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="wb-sec wb-alt" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap wb-faq">
                <div class="wb-faq-head">
                    <div class="wb-l3" data-reveal="l3" style="margin-bottom: 1.25rem;">
                        <span class="wb-l3-tc wb-mono" aria-hidden="true">10:00:00:00</span>
                        <h2 class="wb-l3-name" style="font-size: clamp(1.8rem, 3.6vw, 2.7rem);">Frequently asked questions</h2>
                        <p class="wb-l3-title wb-mono" aria-hidden="true">Talkback</p>
                    </div>
                    <p class="wb-sub" data-reveal>
                        What webinar hosts ask before they move a series across.
                    </p>
                </div>

                <div class="wb-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
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

        <!-- ============================================================ -->
        <!-- 13. Finale: the tally comes on                               -->
        <!-- ============================================================ -->
        <div class="wb-bars" aria-hidden="true"></div>

        <section id="claim" class="wb-final" style="scroll-margin-top: 4rem;">
            <div class="wb-wrap">
                <div class="wb-mon wb-pgm" data-reveal="panel">
                    <div class="wb-screen">
                        <span class="wb-sign wb-sign-air" aria-hidden="true">On air</span>
                        <h2>
                            Publish the session. <span class="wb-hi">Keep the room.</span>
                        </h2>
                        <p class="wb-final-sub">
                            Unlimited webinars, the running order, recurring series and free registration are free forever, with no monthly ceiling on any of them. {{ plan_price($proMonthly) }} a month is what puts a price on a seat, and nothing is ever taken off the top.
                        </p>

                        <div class="wb-claim-wrap">
                            <label for="es-claim-input" class="wb-mono">Your schedule name</label>
                            <div dir="ltr" class="es-claim wb-claim">
                                <input id="es-claim-input" type="text" placeholder="your-webinars" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="wb-btn">
                                Get started free
                                {!! $wbArrow !!}
                            </a>
                        </div>
                        <p class="wb-final-note wb-mono">No credit card required</p>
                    </div>
                    <div class="wb-umd" aria-hidden="true"><b>PGM</b><span id="wb-umd-name" data-idle="Programme">Programme</span></div>
                </div>
            </div>
        </section>

        <div class="wb-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    {{-- The last monitor's display label takes the name as it is typed, the way a gallery
         labels a source. It reads the field after the shared script has tidied the value. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var field = document.getElementById('es-claim-input');
            var label = document.getElementById('wb-umd-name');
            if (!field || !label) {
                return;
            }
            field.addEventListener('input', function () {
                window.requestAnimationFrame(function () {
                    var name = field.value.replace(/-+$/, '');
                    label.textContent = name ? name + '.eventschedule.com' : label.getAttribute('data-idle');
                });
            });
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
