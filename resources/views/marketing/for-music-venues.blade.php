<x-marketing-layout>
    <x-slot name="title">Music Venue Calendars | Set Times, Tickets, and the Door</x-slot>
    <x-slot name="description">Put the whole show day on one link: set times and a page for every act on the bill, tickets with zero platform fees, and free QR scanning at the door.</x-slot>
    <x-slot name="breadcrumbTitle">For Music Venues</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Staatliches') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Oswald') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Libre Franklin') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Music Venues"
        description="Publish the whole show day on one link: set times for every band on the bill, tickets with zero platform fees, and QR check-in on the door."
        audience="Music Venues"
        keywords="music venue calendar, set times, concert listings, venue ticketing, QR check-in, band booking requests, live music schedule" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to publish a music venue's show day with Event Schedule",
        "description": "Get the whole show day, not just the doors time, onto one link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add the show",
                "text": "Create the event once, and use sub-schedules to keep the main room and the back room apart on the same link."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Add the running order",
                "text": "Give the show its parts. Each act gets a name and a start time, and they appear in order on the public event page."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the door",
                "text": "Add ticket types with their own sales windows, then scan QR codes on the night, free on every plan, and on Pro watch the check-in dashboard against your capacity."
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
           For-music-venues "The Marquee" styles. The page is the sign
           over the door of a music hall and the listings beneath it:
           a backlit changeable-letter board with a border of bulbs, the
           day sheet taped up under it, a soffit of lamps, the room's
           calendar set the way a venue sets its own, the stage door,
           the box office, and the sign again with your name on it.

           By day the street is pale and the bulbs are cold glass. By
           night the street is black, the board is the light source and
           the bulbs chase. Everything is scoped under #mv; the shared
           es-* reveal system still drives the entrances.
           ============================================================== */

        #mv {
            --mv-street: #ebe7de;
            --mv-street-2: #e1dcd1;
            --mv-wall: #f6f3ec;
            --mv-ink: #151412;
            --mv-ink-2: #46423b;
            --mv-ink-3: #5c5750;
            --mv-line: rgba(21, 20, 18, 0.17);
            --mv-red: #d0021b;
            --mv-red-ink: #b50218;
            --mv-steel: #1c1b19;
            --mv-steel-edge: #000;
            --mv-sign: 'Staatliches', 'Oswald', 'Arial Narrow', Impact, sans-serif;
            --mv-list: 'Oswald', 'Arial Narrow', 'Helvetica Neue', sans-serif;
            --mv-text: 'Libre Franklin', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --mv-mono: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
            position: relative;
            background: var(--mv-street);
            color: var(--mv-ink);
            font-family: var(--mv-text);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #mv {
            --mv-street: #0b0a09;
            --mv-street-2: #121110;
            --mv-wall: #1a1816;
            --mv-ink: #f4efe5;
            --mv-ink-2: #cbc5b9;
            --mv-ink-3: #a09a8f;
            --mv-line: rgba(244, 239, 229, 0.16);
            --mv-red: #ff4a43;
            --mv-red-ink: #ff6a60;
            --mv-steel: #2b2927;
            --mv-steel-edge: rgba(244, 239, 229, 0.2);
        }

        /* The bar above takes the colour of the street. */
        body > header.sticky {
            background-color: rgba(235, 231, 222, 0.88);
            border-bottom-color: rgba(21, 20, 18, 0.14);
        }
        .dark body > header.sticky {
            background-color: rgba(11, 10, 9, 0.88);
            border-bottom-color: rgba(244, 239, 229, 0.12);
        }

        #mv ::selection { background: #d0021b; color: #fff; }
        #mv a:focus-visible,
        #mv summary:focus-visible,
        #mv input:focus-visible {
            outline: 3px solid var(--mv-red);
            outline-offset: 3px;
        }
        #mv .mv-night a:focus-visible,
        #mv .mv-night input:focus-visible { outline-color: #ffb84d; }

        .mv-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
        .mv-section { padding-block: clamp(4.5rem, 9vw, 7.5rem); }
        .mv-alt { background: var(--mv-street-2); }

        /* Voices: the sign, the listings, the typed sheet. */
        .mv-kick {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1.2;
            color: var(--mv-red-ink);
        }
        .mv-kick::before {
            content: "";
            flex: none;
            width: 0.62rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle at 36% 30%, #fff 0 16%, #ffd58a 20% 60%, #e09a2c 100%);
            box-shadow: 0 0 0 1.5px var(--mv-ink);
        }
        .dark .mv-kick::before,
        .mv-night .mv-kick::before { box-shadow: 0 0 0 1.5px #000, 0 0 0.7rem 0.1rem rgba(255, 184, 77, 0.7); }
        .mv-h2 {
            margin-top: 0.9rem;
            font-family: var(--mv-sign);
            font-weight: 400;
            font-size: clamp(2.7rem, 6.4vw, 5.5rem);
            line-height: 0.93;
            letter-spacing: 0.012em;
            text-transform: uppercase;
            text-wrap: balance;
        }
        .mv-em { color: var(--mv-red); }
        .mv-sub { margin-top: 1.15rem; max-width: 40rem; font-size: 1.125rem; color: var(--mv-ink-2); }
        .mv-head { margin-bottom: clamp(2.25rem, 5vw, 3.75rem); }
        .mv-head-center { text-align: center; }
        .mv-head-center .mv-sub { margin-inline: auto; }
        #mv p a, #mv li a { color: var(--mv-ink); font-weight: 700; text-decoration: underline; text-decoration-color: var(--mv-red); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        #mv p a:hover, #mv li a:hover { color: var(--mv-red-ink); }

        /* Plan tags, cut like the little stickers on a listing. */
        .mv-tier {
            display: inline-block;
            padding: 0.24rem 0.5rem 0.16rem;
            border: 1.5px solid currentColor;
            border-radius: 0.2rem;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.2;
            color: var(--mv-ink-2);
            vertical-align: middle;
        }
        .mv-tier-paid { background: #d0021b; border-color: #d0021b; color: #fff; }
        .mv-feat-top { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 0.75rem; }
        .mv-feat-top h3 { font-family: var(--mv-list); font-weight: 700; font-size: 1.3rem; line-height: 1.2; letter-spacing: 0.01em; }
        .mv-feat p { margin-top: 0.7rem; color: var(--mv-ink-2); }
        .mv-feat p + p { font-size: 0.975rem; }

        /* Buttons: enamel plates. */
        .mv-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 1rem 1.5rem 0.9rem;
            border: 2px solid #d0021b;
            border-radius: 0.3rem;
            background: #d0021b;
            color: #fff;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            line-height: 1;
            transition: translate 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, color 0.18s ease;
        }
        .mv-btn:hover { translate: 0 -2px; box-shadow: 0 0.7rem 1.3rem -0.6rem rgba(208, 2, 27, 0.75); }
        .mv-btn svg { width: 1.1rem; height: 1.1rem; flex: none; transition: translate 0.18s ease; }
        .mv-btn:hover svg { translate: 3px 0; }
        .mv-btn-ghost { background: transparent; border-color: var(--mv-ink); color: var(--mv-ink); }
        .mv-btn-ghost:hover { background: var(--mv-ink); color: var(--mv-street); box-shadow: none; }
        .mv-btn-ghost:hover svg { translate: 0 3px; }
        /* On the narrowest phones the long label takes two lines: give them air between. */
        @media (max-width: 400px) { .mv-btn { line-height: 1.2; } }
        .mv-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--mv-ink);
            border-bottom: 2px solid var(--mv-red);
            padding-bottom: 0.2rem;
            transition: gap 0.2s ease;
        }
        .mv-more:hover { gap: 0.85rem; }
        .mv-more svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           The sign: a steel frame, a border of bulbs, a backlit board
           --------------------------------------------------------------- */
        .mv-sign {
            --mv-rim: clamp(1.15rem, 2.5vw, 1.9rem);
            --mv-bulb: clamp(0.46rem, 0.95vw, 0.7rem);
            position: relative;
            padding: var(--mv-rim);
            border-radius: 0.55rem;
            background: linear-gradient(#262422, #131211);
            box-shadow: 0 0 0 1px #000, inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 2.6rem 3.6rem -1.8rem rgba(21, 20, 18, 0.6);
        }
        .dark #mv .mv-sign,
        #mv .mv-sign-lit {
            box-shadow: 0 0 0 1px rgba(244, 239, 229, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.12), 0 0 4.5rem rgba(255, 196, 110, 0.2), 0 0 11rem rgba(255, 184, 77, 0.14);
        }
        .mv-bulbs { position: absolute; display: flex; justify-content: space-between; align-items: center; pointer-events: none; }
        .mv-bulbs-t { top: 0; left: var(--mv-rim); right: var(--mv-rim); height: var(--mv-rim); }
        .mv-bulbs-b { bottom: 0; left: var(--mv-rim); right: var(--mv-rim); height: var(--mv-rim); flex-direction: row-reverse; }
        .mv-bulbs-l { left: 0; top: var(--mv-rim); bottom: var(--mv-rim); width: var(--mv-rim); flex-direction: column-reverse; }
        .mv-bulbs-r { right: 0; top: var(--mv-rim); bottom: var(--mv-rim); width: var(--mv-rim); flex-direction: column; }
        .mv-bulbs i {
            position: relative;
            flex: none;
            width: var(--mv-bulb);
            aspect-ratio: 1;
            border-radius: 50%;
            /* cold glass: the filament is off */
            background: radial-gradient(circle at 36% 30%, #fff 0 14%, #ece1c8 17% 58%, #a99c82 100%);
            box-shadow: 0 0 0 1.5px #060605;
        }
        .dark #mv .mv-bulbs i,
        #mv .mv-sign-lit .mv-bulbs i { background: radial-gradient(circle at 50% 46%, #fff 0 24%, #ffe4ad 32% 62%, #ffb84d 100%); }
        .dark #mv .mv-bulbs i::after,
        #mv .mv-sign-lit .mv-bulbs i::after {
            content: "";
            position: absolute;
            inset: -120%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 200, 104, 0.8) 0 16%, rgba(255, 170, 60, 0.3) 36%, rgba(255, 170, 60, 0) 68%);
            animation: mv-chase 1.2s linear infinite;
        }
        #mv .mv-bulbs i:nth-child(3n + 2)::after { animation-delay: -0.8s; }
        #mv .mv-bulbs i:nth-child(3n)::after { animation-delay: -0.4s; }
        @keyframes mv-chase {
            0%, 33% { opacity: 1; }
            33.01%, 100% { opacity: 0.2; }
        }
        @media (max-width: 1023px) { .mv-bulbs-t i:nth-child(n + 25), .mv-bulbs-b i:nth-child(n + 25) { display: none; } }
        @media (max-width: 639px) {
            .mv-bulbs-t i:nth-child(n + 14), .mv-bulbs-b i:nth-child(n + 14) { display: none; }
            .mv-sign-small .mv-bulbs-l i:nth-child(n + 5), .mv-sign-small .mv-bulbs-r i:nth-child(n + 5) { display: none; }
        }

        .mv-board {
            position: relative;
            container: mvboard / inline-size;
            overflow: hidden;
            border-radius: 0.15rem;
            background-color: #fbf7ee;
            background-image: radial-gradient(120% 90% at 50% 35%, #fffdf7 0%, #f6efdf 70%, #ece2cc 100%);
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.5), inset 0 0 2.2rem rgba(150, 110, 50, 0.16);
            color: #141312;
        }
        .dark #mv .mv-board,
        #mv .mv-sign-lit .mv-board { box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.5), inset 0 0 2.2rem rgba(200, 140, 50, 0.2), 0 0 2.4rem rgba(255, 226, 170, 0.5); }
        html.es-anim #mv .mv-hero .mv-board { animation: mv-strike 1.5s steps(1) 0.15s 1 both; }
        @keyframes mv-strike {
            0% { filter: brightness(0.5); }
            7% { filter: brightness(1.04); }
            11% { filter: brightness(0.62); }
            17% { filter: brightness(1.02); }
            21% { filter: brightness(0.8); }
            26%, 100% { filter: none; }
        }
        /* The attraction strip along the top of the board. */
        .mv-strip {
            display: block;
            padding: 0.7rem 1rem 0.58rem;
            background: #d0021b;
            color: #fff;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: clamp(0.68rem, 1.55cqi, 0.98rem);
            letter-spacing: 0.26em;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.3;
        }

        /* Letters. Each is a tile on its own plate, hung between two rails. The pitch of
           the rails is the height of a word, so a row that wraps simply takes the next track. */
        .mv-row { display: block; }
        .mv-tiles {
            display: block;
            font-family: var(--mv-sign);
            line-height: 1;
            background-image:
                linear-gradient(to bottom, rgba(20, 19, 18, 0.2) 0.03em, rgba(20, 19, 18, 0) 0.03em),
                linear-gradient(to top, rgba(20, 19, 18, 0.34) 0.045em, rgba(20, 19, 18, 0) 0.045em);
            background-size: 100% 1.28em;
            background-origin: content-box;
            background-clip: content-box;
        }
        .mv-line { display: flex; flex-wrap: wrap; justify-content: center; column-gap: 0.4em; padding-inline: 0.3em; }
        .mv-word { display: inline-flex; align-items: center; gap: 0.035em; height: 1.28em; }
        .mv-t {
            display: grid;
            place-items: center;
            width: 0.62em;
            height: 1.06em;
            border-radius: 0.03em;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.3));
            box-shadow: inset 0 0 0 1px rgba(20, 19, 18, 0.08), 0 0.02em 0.035em rgba(20, 19, 18, 0.22);
            color: #141312;
            font-style: normal;
        }
        .mv-t::before { content: attr(data-l); translate: 0 0.035em; }
        .mv-t[data-l="I"], .mv-t[data-l="."], .mv-t[data-l="1"], .mv-t[data-l="-"] { width: 0.4em; }
        .mv-t[data-l="M"], .mv-t[data-l="W"] { width: 0.72em; }
        /* Hung by hand: a few a hair crooked, a few sitting low. */
        .mv-t:nth-child(5n + 2) { rotate: -1.5deg; translate: 0 0.012em; }
        .mv-t:nth-child(7n + 4) { rotate: 1.3deg; }
        .mv-word:nth-child(2n) .mv-t:nth-child(3) { rotate: 2.4deg; translate: 0 0.03em; }
        .mv-word:nth-child(3n + 1) .mv-t:nth-child(1) { rotate: -0.8deg; }
        .mv-t-old { background: linear-gradient(180deg, #f1dfae, #e4cd92); }
        .mv-t-num, .mv-word-red .mv-t { color: #d0021b; }
        .mv-h1 .mv-tiles { font-size: 11.3cqi; }
        .mv-h1 .es-hero-eyebrow + .mv-row .mv-tiles { padding-top: 0.3em; }
        .mv-h1 .mv-row + .mv-row .mv-tiles { padding-bottom: 0.34em; }
        @container mvboard (min-width: 620px) {
            .mv-h1 .mv-tiles { font-size: min(7.5cqi, 4.7rem); }
            .mv-h1 .mv-line { flex-wrap: nowrap; }
        }
        html.es-anim #mv .mv-h1 .mv-t {
            animation: mv-hang 0.5s cubic-bezier(0.2, 1.3, 0.4, 1) both;
            animation-delay: calc(0.35s + var(--i, 0) * 15ms);
        }
        @keyframes mv-hang { from { opacity: 0; translate: 0 -0.45em; rotate: -9deg; } }

        /* The lip of the sign: every kind of room, running past in amber. */
        .mv-lip {
            margin-top: 0.4rem;
            padding-block: 0.6rem 0.45rem;
            background: #0a0908;
            border-radius: 0.15rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.06);
            color: #ffb84d;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
        }
        .mv-lip .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .mv-lip-item { display: inline-flex; align-items: center; gap: 1.4rem; padding-inline-end: 1.4rem; white-space: nowrap; }
        .mv-lip-item::after { content: ""; width: 0.36rem; aspect-ratio: 1; border-radius: 50%; background: #d0021b; }
        @media (prefers-reduced-motion: reduce) { .mv-lip .es-marquee-track { row-gap: 0.35rem; } }

        /* ---------------------------------------------------------------
           Hero: the sign, and under it the day
           --------------------------------------------------------------- */
        .mv-hero { position: relative; padding-block: clamp(1.25rem, 2.4vw, 1.9rem) clamp(3.5rem, 7vw, 5.5rem); background: linear-gradient(#f3f0e9, var(--mv-street) 26rem); }
        .dark #mv .mv-hero { background: radial-gradient(62rem 36rem at 50% 17rem, rgba(255, 196, 110, 0.17), rgba(255, 196, 110, 0) 72%), var(--mv-street); }
        .mv-hero .mv-sign { max-width: 66rem; margin-inline: auto; }
        .mv-under { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem; margin-top: clamp(2rem, 4vw, 2.75rem); align-items: start; }
        @media (min-width: 960px) { .mv-under { grid-template-columns: minmax(0, 1fr) minmax(0, 28rem); gap: 4rem; } }
        .mv-lede { max-width: 34rem; font-size: clamp(1.15rem, 1.7vw, 1.38rem); line-height: 1.5; color: var(--mv-ink-2); }
        .mv-cta { display: flex; flex-wrap: wrap; gap: 0.9rem; margin-top: 1.75rem; }

        .mv-one { max-width: 30rem; margin-top: clamp(2.25rem, 4vw, 3.25rem); }
        .mv-one-label { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.55rem; border-bottom: 3px solid var(--mv-ink); font-family: var(--mv-list); font-weight: 700; font-size: 0.78rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--mv-ink-2); }
        .mv-one-row { display: grid; grid-template-columns: 5rem minmax(0, 1fr) auto; align-items: center; gap: 1.25rem; padding: 1.1rem 0.5rem 1rem; border-bottom: 1px solid var(--mv-line); }
        .mv-one-note { margin-top: 0.8rem; font-family: var(--mv-list); font-size: 0.85rem; letter-spacing: 0.14em; text-transform: uppercase; color: var(--mv-ink-3); }

        /* TONIGHT, hung from the sign and pointing at the sheet. */
        .mv-hang { position: relative; display: grid; justify-items: center; }
        .mv-tonight {
            position: relative;
            z-index: 2;
            width: 10.5rem;
            padding: 0.7rem 0.5rem 2.3rem;
            margin-bottom: -1.5rem;
            background: #d0021b;
            color: #fff;
            font-family: var(--mv-sign);
            font-size: 2.1rem;
            letter-spacing: 0.08em;
            line-height: 1;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 58%, 50% 100%, 0 58%);
            filter: drop-shadow(0 0.5rem 0.5rem rgba(0, 0, 0, 0.3));
        }
        .mv-tonight::after {
            content: "";
            position: absolute;
            inset: auto 0 0.5rem 0;
            height: 1.6rem;
            background:
                radial-gradient(circle, #ffe4ad 0 34%, transparent 38%) calc(50% - 1.1rem) 0 / 0.55rem 0.55rem no-repeat,
                radial-gradient(circle, #ffe4ad 0 34%, transparent 38%) 50% 0 / 0.55rem 0.55rem no-repeat,
                radial-gradient(circle, #ffe4ad 0 34%, transparent 38%) calc(50% + 1.1rem) 0 / 0.55rem 0.55rem no-repeat,
                radial-gradient(circle, #ffe4ad 0 34%, transparent 38%) calc(50% - 0.55rem) 0.62rem / 0.55rem 0.55rem no-repeat,
                radial-gradient(circle, #ffe4ad 0 34%, transparent 38%) calc(50% + 0.55rem) 0.62rem / 0.55rem 0.55rem no-repeat,
                radial-gradient(circle, #ffe4ad 0 34%, transparent 38%) 50% 1.2rem / 0.55rem 0.55rem no-repeat;
        }
        .dark #mv .mv-tonight::after { animation: mv-point 0.9s steps(1) infinite; }
        @keyframes mv-point { 0%, 49% { opacity: 1; } 50%, 100% { opacity: 0.35; } }
        .mv-hang::before,
        .mv-hang::after { content: ""; position: absolute; bottom: 100%; width: 2px; height: clamp(2rem, 4vw, 2.75rem); background: var(--mv-steel); }
        .mv-hang::before { left: calc(50% - 3.6rem); }
        .mv-hang::after { left: calc(50% + 3.6rem); }
        @media (max-width: 959px) { .mv-hang::before, .mv-hang::after { display: none; } }

        /* The day sheet, taped to the sound desk. Fixed paper in both modes. */
        .mv-sheet {
            position: relative;
            width: 100%;
            padding: 2.7rem 1.4rem 1.3rem;
            background-color: #fdfbf4;
            background-image: linear-gradient(rgba(255, 196, 110, 0), rgba(120, 100, 70, 0.07));
            color: #141312;
            rotate: 1.2deg;
            box-shadow: 0 0 0 1px rgba(20, 19, 18, 0.1), 0 1.6rem 2.2rem -1.3rem rgba(20, 19, 18, 0.55);
        }
        .dark #mv .mv-sheet { background-color: #efe8d8; }
        .mv-sheet::before,
        .mv-sheet::after {
            content: "";
            position: absolute;
            top: -0.55rem;
            width: 4.6rem;
            height: 1.35rem;
            background: rgba(28, 27, 25, 0.86);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.07);
        }
        .mv-sheet::before { left: -1rem; rotate: -33deg; }
        .mv-sheet::after { right: -1rem; rotate: 31deg; }
        .mv-sheet-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 0.7rem;
            border-bottom: 2px solid #141312;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
        }
        .mv-sheet-head span:last-child { color: #5c5750; }
        .mv-sheet-row {
            position: relative;
            display: grid;
            grid-template-columns: 3.5rem minmax(0, 1fr);
            column-gap: 0.9rem;
            padding: 0.62rem 0.5rem 0.55rem;
            border-bottom: 1px dashed rgba(20, 19, 18, 0.24);
        }
        .mv-sheet-time { grid-row: span 2; font-family: var(--mv-mono); font-weight: 700; font-size: 0.95rem; padding-top: 0.1rem; }
        .mv-sheet-name { font-family: var(--mv-list); font-weight: 700; font-size: 1.12rem; letter-spacing: 0.05em; text-transform: uppercase; line-height: 1.2; }
        .mv-sheet-row p { font-size: 0.875rem; line-height: 1.4; color: #5c5750; }
        /* The one line that makes it to the listing. */
        .mv-sheet-now { margin: 0.3rem -0.45rem; padding-inline: 0.95rem; border: 2.5px solid #d0021b; border-radius: 0.2rem; }
        .mv-sheet-now .mv-sheet-time, .mv-sheet-now .mv-sheet-name { color: #b50218; }
        .mv-stamp {
            position: absolute;
            right: 0.6rem;
            top: -0.8rem;
            padding: 0.2rem 0.5rem 0.1rem;
            border: 2px solid #b50218;
            border-radius: 0.2rem;
            background: #fdfbf4;
            color: #b50218;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.7rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            rotate: 4deg;
        }
        .dark #mv .mv-stamp { background: #efe8d8; }

        /* ---------------------------------------------------------------
           Night bands: literal colours, the same in both modes
           --------------------------------------------------------------- */
        .mv-night { position: relative; overflow: clip; background-color: #0c0b0a; color: #f4efe5; border-block: 1px solid rgba(244, 239, 229, 0.08); }
        .mv-night .mv-kick { color: #ffb84d; }
        .mv-night .mv-em { color: #ff5a52; }
        .mv-night .mv-sub { color: #cbc5b9; }
        .mv-night .mv-tier { color: #cbc5b9; }
        .mv-night .mv-tier-paid { color: #fff; }
        .mv-night .mv-feat p { color: #cbc5b9; }
        #mv .mv-night p a { color: #f4efe5; text-decoration-color: #ffb84d; }
        #mv .mv-night p a:hover { color: #ffb84d; }

        /* The soffit: the ceiling of lamps you stand under at the door. */
        .mv-soffit::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 300%;
            height: 72rem;
            background-image: radial-gradient(circle, #ffe9bd 0 7%, rgba(255, 184, 77, 0.6) 9% 17%, rgba(255, 160, 40, 0) 44%);
            background-size: 3.4rem 3.4rem;
            background-position: 50% 0;
            transform: perspective(40rem) rotateX(-62deg);
            transform-origin: 50% 0;
            -webkit-mask-image: linear-gradient(to bottom, #000 0, rgba(0, 0, 0, 0.55) 40%, transparent 78%);
            mask-image: linear-gradient(to bottom, #000 0, rgba(0, 0, 0, 0.55) 40%, transparent 78%);
            opacity: 0.85;
            pointer-events: none;
        }
        .mv-soffit .mv-wrap { position: relative; }
        .mv-soffit { padding-top: clamp(9rem, 15vw, 13rem); }
        .mv-stats { display: grid; grid-template-columns: minmax(0, 1fr); margin-top: clamp(2.5rem, 5vw, 4rem); border-block: 2px solid #f4efe5; }
        @media (min-width: 860px) { .mv-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mv-stat { padding: 2.2rem 1.5rem 2rem; text-align: center; }
        .mv-stat + .mv-stat { border-top: 1px solid rgba(244, 239, 229, 0.2); }
        @media (min-width: 860px) { .mv-stat + .mv-stat { border-top: 0; border-inline-start: 1px solid rgba(244, 239, 229, 0.2); } }
        .mv-stat-tag { font-family: var(--mv-list); font-weight: 700; font-size: 0.8rem; letter-spacing: 0.24em; text-transform: uppercase; color: #ffb84d; }
        .mv-stat h3 {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.9rem;
            margin-top: 1.1rem;
            font-family: var(--mv-sign);
            font-weight: 400;
            font-size: clamp(2rem, 3.6vw, 2.8rem);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1;
        }
        .mv-stat p { margin: 1.2rem auto 0; max-width: 19rem; color: #cbc5b9; }
        /* A split-flap figure: the old number falls away, the true one lands. */
        .mv-flap {
            --mv-fd: calc(var(--reveal-delay, 0s) + 0.6s);
            position: relative;
            display: inline-grid;
            place-items: center;
            flex: none;
            width: 1.1em;
            height: 1.36em;
            font-family: var(--mv-sign);
            font-size: clamp(4.2rem, 8vw, 6.2rem);
            line-height: 1;
            color: #f4efe5;
            background: linear-gradient(#262422, #161514);
            border-radius: 0.07em;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08), 0 0.05em 0 #000, 0 0.5em 0.7em -0.45em #000;
            perspective: 5em;
        }
        .mv-flap b { font-weight: 400; translate: 0 0.045em; }
        .mv-flap::after { content: ""; position: absolute; left: 0; right: 0; top: calc(50% - 1px); height: 2px; background: #050505; z-index: 4; }
        .mv-flap i {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            background: linear-gradient(#262422, #161514);
            border-radius: inherit;
            font-style: normal;
            opacity: 0;
            backface-visibility: hidden;
        }
        .mv-flap i::before { content: attr(data-d); translate: 0 0.045em; }
        .mv-flap-top { clip-path: inset(0 0 50% 0); transform: rotateX(-90deg); z-index: 3; }
        .mv-flap-bot { clip-path: inset(50% 0 0 0); z-index: 1; }
        .mv-flap-land { clip-path: inset(50% 0 0 0); z-index: 2; }
        html.es-anim #mv [data-reveal]:not(.is-revealed) .mv-flap-top { transform: rotateX(0deg); opacity: 1; }
        html.es-anim #mv [data-reveal]:not(.is-revealed) .mv-flap-bot { opacity: 1; }
        html.es-anim #mv [data-reveal]:not(.is-revealed) .mv-flap-land { transform: rotateX(90deg); opacity: 1; }
        html.es-anim #mv .mv-flap-top { transition: transform 0.32s cubic-bezier(0.5, 0, 1, 0.6) var(--mv-fd), opacity 0s linear calc(var(--mv-fd) + 0.32s); }
        html.es-anim #mv .mv-flap-bot { transition: opacity 0s linear calc(var(--mv-fd) + 0.62s); }
        html.es-anim #mv .mv-flap-land { transition: transform 0.3s cubic-bezier(0, 0.4, 0.5, 1.3) calc(var(--mv-fd) + 0.32s), opacity 0s linear calc(var(--mv-fd) + 0.72s); }
        .mv-gap-foot { margin-top: 2.5rem; text-align: center; color: #cbc5b9; }
        #mv .mv-gap-foot a { display: inline-flex; align-items: center; gap: 0.4rem; margin-inline-start: 0.4rem; text-decoration: none; border-bottom: 2px solid #ffb84d; transition: gap 0.2s ease; }
        #mv .mv-gap-foot a:hover { gap: 0.7rem; }
        .mv-gap-foot svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           The running order: the set times in the frame by the door
           --------------------------------------------------------------- */
        .mv-order { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: start; }
        @media (min-width: 960px) { .mv-order { grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr); gap: 4.5rem; } }
        .mv-frame {
            padding: 0.85rem;
            border-radius: 0.3rem;
            background: linear-gradient(135deg, #dedbd4, #8d8a83 38%, #ebe8e1 58%, #6f6c66);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.35), 0 1.8rem 2.6rem -1.4rem rgba(21, 20, 18, 0.6);
        }
        .mv-frame-sheet { position: relative; overflow: hidden; padding: 1.6rem 1.5rem 1.4rem; background-color: #fdfbf4; color: #141312; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.3); }
        .dark #mv .mv-frame-sheet { background-color: #efe8d8; }
        .mv-frame-sheet::after { content: ""; position: absolute; inset: 0; background: linear-gradient(112deg, rgba(255, 255, 255, 0) 38%, rgba(255, 255, 255, 0.5) 43%, rgba(255, 255, 255, 0) 51%); pointer-events: none; }
        .mv-frame-top { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; padding-bottom: 0.8rem; border-bottom: 3px solid #141312; font-family: var(--mv-list); font-weight: 700; font-size: 0.78rem; letter-spacing: 0.22em; text-transform: uppercase; }
        .mv-frame-top b { font-family: var(--mv-sign); font-weight: 400; font-size: 1.7rem; letter-spacing: 0.06em; }
        .mv-frame-row { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: baseline; column-gap: 1.25rem; padding-block: 0.85rem 0.6rem; border-bottom: 1px solid rgba(20, 19, 18, 0.2); font-family: var(--mv-sign); font-size: clamp(2.2rem, 5vw, 3.3rem); line-height: 1; letter-spacing: 0.02em; }
        .mv-frame-row span:first-child { color: #d0021b; font-variant-numeric: tabular-nums; }
        .mv-order-note { margin-top: 1.25rem; font-size: 0.95rem; color: var(--mv-ink-3); }
        .mv-notes { border-top: 3px solid var(--mv-ink); }
        .mv-notes .mv-feat { padding-block: 1.6rem 1.5rem; border-bottom: 1px solid var(--mv-line); }

        /* ---------------------------------------------------------------
           The bill: three sets, three stacks of prints
           --------------------------------------------------------------- */
        .mv-acts { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
        @media (min-width: 820px) { .mv-acts { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mv-act { padding: 1.5rem 1.4rem 1.4rem; background: var(--mv-wall); border: 1px solid var(--mv-line); border-radius: 0.3rem; }
        .mv-act-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; font-family: var(--mv-mono); font-weight: 700; font-size: 0.9rem; color: var(--mv-ink-2); }
        .mv-role { font-family: var(--mv-list); font-size: 0.8rem; letter-spacing: 0.22em; text-transform: uppercase; color: var(--mv-red-ink); }
        .mv-act h3 { margin-top: 0.5rem; font-family: var(--mv-sign); font-weight: 400; font-size: clamp(2.2rem, 3.6vw, 3rem); line-height: 1; letter-spacing: 0.02em; text-transform: uppercase; }
        .mv-prints { display: flex; padding: 1.6rem 0 1.3rem 9%; }
        .mv-print {
            flex: 1;
            min-width: 0;
            margin-inline-start: -9%;
            padding: 5% 5% 14%;
            background: #fbf9f3;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.14), 0 0.5rem 0.9rem -0.4rem rgba(0, 0, 0, 0.55);
            rotate: var(--r, 0deg);
            transition: rotate 0.4s cubic-bezier(0.3, 1.4, 0.5, 1), translate 0.4s cubic-bezier(0.3, 1.4, 0.5, 1);
        }
        .mv-print i {
            display: block;
            aspect-ratio: 1;
            /* a stage: two cans of light, and the heads of the front row */
            background:
                radial-gradient(circle at 50% 100%, #050505 0 58%, rgba(5, 5, 5, 0) 60%) 0 100% / 24% 26% repeat-x,
                radial-gradient(70% 100% at var(--x, 28%) 0%, var(--c) 0%, rgba(0, 0, 0, 0) 68%),
                radial-gradient(50% 80% at calc(100% - var(--x, 28%)) 0%, rgba(255, 255, 255, 0.5) 0%, rgba(0, 0, 0, 0) 66%),
                linear-gradient(#1b1612, #060505);
        }
        .mv-act:hover .mv-print { rotate: 0deg; translate: 0 -0.35rem; }
        .mv-act-count { font-size: 0.95rem; color: var(--mv-ink-2); }
        .mv-foot { margin: 2.25rem auto 0; max-width: 44rem; text-align: center; font-size: 0.975rem; color: var(--mv-ink-2); }

        /* ---------------------------------------------------------------
           The rooms: the listings, and two small signs that filter them
           --------------------------------------------------------------- */
        .mv-rooms-head { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 4rem; align-items: end; margin-bottom: clamp(2.25rem, 5vw, 3.5rem); }
        @media (min-width: 960px) { .mv-rooms-head { grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr); } }
        .mv-ticks { display: grid; gap: 0.9rem; }
        .mv-ticks li { display: grid; grid-template-columns: 1.4rem minmax(0, 1fr); gap: 0.6rem; color: var(--mv-ink-2); }
        .mv-ticks li::before { content: ""; width: 0.6rem; aspect-ratio: 1; margin-top: 0.5rem; border-radius: 50%; background: var(--mv-red); }
        .mv-listings { position: relative; }
        .mv-roomsigns { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem; border: 0; padding: 0; }
        .mv-roomsigns input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .mv-roomsigns label {
            display: block;
            padding: 0.6rem 1.1rem 0.45rem;
            border: 0.3rem solid #1c1b19;
            border-radius: 0.3rem;
            background: #1c1b19;
            color: #cbc5b9;
            font-family: var(--mv-sign);
            font-size: 1.45rem;
            letter-spacing: 0.06em;
            line-height: 1;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .dark #mv .mv-roomsigns label { border-color: #2b2927; }
        .mv-roomsigns label small { display: block; margin-top: 0.3rem; font-family: var(--mv-list); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.2em; text-transform: uppercase; }
        .mv-roomsigns input:checked + label { background: #fbf7ee; color: #141312; }
        .dark #mv .mv-roomsigns input:checked + label { box-shadow: 0 0 1.6rem rgba(255, 220, 160, 0.4); }
        .mv-roomsigns input:focus-visible + label { outline: 3px solid var(--mv-red); outline-offset: 3px; }
        .mv-month {
            position: sticky;
            top: 4rem;
            z-index: 3;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 1rem;
            padding: 0.55rem 1rem 0.35rem;
            background: #1c1b19;
            color: #fbf7ee;
            font-family: var(--mv-sign);
            font-size: 1.35rem;
            letter-spacing: 0.14em;
            line-height: 1.1;
        }
        .mv-month span { font-family: var(--mv-list); font-weight: 700; font-size: 0.72rem; letter-spacing: 0.2em; text-transform: uppercase; color: #ffb84d; }
        .mv-gigs { display: grid; grid-template-columns: 5.4rem minmax(0, 1.3fr) minmax(0, 1fr) auto; column-gap: clamp(1rem, 2.4vw, 2.25rem); }
        .mv-gig {
            position: relative;
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: subgrid;
            align-items: center;
            padding: 1.3rem clamp(0.5rem, 1.2vw, 1rem) 1.2rem;
            border-bottom: 1px solid var(--mv-line);
            transition: background-color 0.2s ease, opacity 0.2s ease;
        }
        .mv-date { text-align: center; font-family: var(--mv-list); font-weight: 700; font-size: 0.8rem; letter-spacing: 0.22em; text-transform: uppercase; line-height: 1.1; color: var(--mv-ink-2); }
        .mv-date b { display: block; margin-block: 0.12rem 0; font-family: var(--mv-sign); font-weight: 400; font-size: 3.5rem; letter-spacing: 0.02em; line-height: 0.92; color: var(--mv-ink); transition: color 0.2s ease; }
        .mv-gig-name { font-family: var(--mv-sign); font-size: clamp(2rem, 3.6vw, 3.1rem); line-height: 0.95; letter-spacing: 0.02em; text-transform: uppercase; }
        .mv-gig-support { margin-top: 0.3rem; font-family: var(--mv-list); font-size: 0.95rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--mv-ink-2); }
        .mv-gig-meta { font-family: var(--mv-list); font-size: 0.95rem; letter-spacing: 0.06em; text-transform: uppercase; line-height: 1.45; color: var(--mv-ink-2); }
        .mv-gig-meta b { display: block; font-weight: 700; color: var(--mv-ink); }
        .mv-gig-end { display: flex; align-items: center; justify-content: flex-end; gap: 1.1rem; }
        .mv-price { font-family: var(--mv-sign); font-size: 1.8rem; letter-spacing: 0.03em; line-height: 1; white-space: nowrap; }
        .mv-status {
            display: block;
            min-width: 9.6rem;
            padding: 0.5rem 0.7rem 0.38rem;
            border: 2px solid var(--mv-ink);
            border-radius: 0.2rem;
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.74rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.2;
            text-align: center;
            color: var(--mv-ink);
        }
        .mv-status-low { background: #ffb84d; border-color: #ffb84d; color: #141312; }
        .mv-status-sold { background: #d0021b; border-color: #d0021b; color: #fff; }
        .mv-status-new { background: var(--mv-ink); color: var(--mv-street); }
        .mv-tag-tonight { display: inline-block; margin-inline-start: 0.6rem; padding: 0.2rem 0.45rem 0.1rem; background: #d0021b; color: #fff; border-radius: 0.15rem; font-family: var(--mv-list); font-weight: 700; font-size: 0.68rem; letter-spacing: 0.18em; vertical-align: middle; }
        /* One status turns over as the row arrives, the way a flap board corrects itself. */
        .mv-flip { position: relative; display: block; transform-style: preserve-3d; perspective: 20rem; }
        .mv-flip > span { backface-visibility: hidden; }
        .mv-flip::before {
            content: attr(data-was);
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            border: 2px solid var(--mv-ink);
            border-radius: 0.2rem;
            background: var(--mv-street);
            color: var(--mv-ink);
            font-family: var(--mv-list);
            font-weight: 700;
            font-size: 0.74rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            backface-visibility: hidden;
            transform: rotateX(180deg);
        }
        html.es-anim #mv .mv-flip { transition: transform 0.7s cubic-bezier(0.3, 1.5, 0.5, 1) calc(var(--reveal-delay, 0s) + 1.1s); }
        html.es-anim #mv [data-reveal]:not(.is-revealed) .mv-flip { transform: rotateX(180deg); }
        @media (hover: hover) {
            #mv .mv-gigs:has(.mv-gig:hover) .mv-gig:not(:hover) { opacity: 0.5; }
            #mv .mv-gig:hover { background: var(--mv-wall); }
            #mv .mv-gig:hover .mv-date b { color: var(--mv-red); }
        }
        #mv .mv-listings:has(#mv-room-main:checked) .mv-gig[data-room="back"],
        #mv .mv-listings:has(#mv-room-back:checked) .mv-gig[data-room="main"] { display: none; }
        @supports not (grid-template-columns: subgrid) {
            .mv-gigs { display: block; }
            .mv-gig { grid-template-columns: 5.4rem minmax(0, 1.3fr) minmax(0, 1fr) auto; column-gap: clamp(1rem, 2.4vw, 2.25rem); }
        }
        @media (max-width: 719px) {
            .mv-gigs { display: block; }
            .mv-gig { grid-template-columns: 4.3rem minmax(0, 1fr); column-gap: 1rem; row-gap: 0.7rem; align-items: start; padding-inline: 0.25rem; }
            .mv-date b { font-size: 2.9rem; }
            .mv-gig-meta, .mv-gig-end { grid-column: 2; }
            .mv-gig-end { justify-content: flex-start; flex-wrap: wrap; gap: 0.6rem 0.9rem; }
            .mv-status { min-width: 0; }
        }

        /* ---------------------------------------------------------------
           Who plays: round the back, at the stage door
           --------------------------------------------------------------- */
        .mv-stage::before {
            content: "";
            position: absolute;
            top: 0;
            left: 50%;
            width: min(70rem, 140%);
            height: 100%;
            translate: -50% 0;
            background: radial-gradient(50% 60% at 50% 0%, rgba(255, 74, 67, 0.16), rgba(255, 74, 67, 0) 70%);
            pointer-events: none;
        }
        .mv-stage .mv-wrap { position: relative; }
        .mv-doorsign {
            display: inline-block;
            padding: 0.55rem 1.1rem 0.35rem;
            border: 2px solid #ff5a52;
            border-radius: 0.3rem;
            color: #ff5a52;
            font-family: var(--mv-sign);
            font-size: 1.5rem;
            letter-spacing: 0.16em;
            line-height: 1;
            text-shadow: 0 0 0.7rem rgba(255, 74, 67, 0.75);
            box-shadow: 0 0 1.4rem rgba(255, 74, 67, 0.4), inset 0 0 0.9rem rgba(255, 74, 67, 0.22);
        }
        .mv-stage-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem; align-items: start; }
        @media (min-width: 960px) { .mv-stage-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 4rem; } }
        .mv-stage .mv-feat { padding-block: 1.5rem 1.4rem; border-bottom: 1px solid rgba(244, 239, 229, 0.16); }
        .mv-stage .mv-feat:first-child { border-top: 3px solid #f4efe5; }
        .mv-ledger { background: #151412; border: 1px solid rgba(244, 239, 229, 0.14); border-radius: 0.35rem; overflow: hidden; }
        .mv-ledger-top { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.9rem 1.2rem 0.75rem; background: #1c1b19; font-family: var(--mv-list); font-weight: 700; font-size: 0.8rem; letter-spacing: 0.22em; text-transform: uppercase; color: #f4efe5; }
        .mv-ledger-top span:last-child { color: #ffb84d; }
        .mv-ask { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 1rem; align-items: center; padding: 1.1rem 1.2rem 1rem; border-top: 1px solid rgba(244, 239, 229, 0.12); }
        .mv-ask b { display: block; font-family: var(--mv-sign); font-weight: 400; font-size: 1.75rem; letter-spacing: 0.03em; line-height: 1; color: #f4efe5; }
        .mv-ask small { display: block; margin-top: 0.35rem; font-family: var(--mv-mono); font-size: 0.82rem; color: #a09a8f; }
        .mv-ask > span { padding: 0.45rem 0.8rem 0.33rem; border: 2px solid #ffb84d; border-radius: 0.2rem; color: #ffb84d; font-family: var(--mv-list); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.18em; text-transform: uppercase; line-height: 1.2; }

        /* ---------------------------------------------------------------
           The door: the box office window and what comes out of it
           --------------------------------------------------------------- */
        .mv-door { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3rem; align-items: center; }
        @media (min-width: 960px) { .mv-door { grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr); gap: 4.5rem; } }
        .mv-door .mv-h2 { font-size: clamp(3.4rem, 9vw, 8rem); }
        .mv-office { position: relative; width: min(100%, 27rem); margin-inline: auto; padding-bottom: 8.5rem; }
        .mv-office-plate {
            position: relative;
            z-index: 1;
            padding: 0.55rem 1rem 0.3rem;
            border: 0.4rem solid #1c1b19;
            border-radius: 0.3rem;
            background-color: #fbf7ee;
            color: #141312;
            font-family: var(--mv-sign);
            font-size: 2.1rem;
            letter-spacing: 0.2em;
            line-height: 1;
            text-align: center;
        }
        .dark #mv .mv-office-plate { border-color: #2b2927; box-shadow: 0 0 2rem rgba(255, 220, 160, 0.35); }
        .mv-window {
            position: relative;
            height: 14.5rem;
            margin: -0.2rem 1.4rem 0;
            border: 0.5rem solid #1c1b19;
            border-bottom: 0;
            border-radius: 9rem 9rem 0 0;
            background: linear-gradient(165deg, #33312e 0%, #121110 55%, #1f1d1b 100%);
            overflow: hidden;
        }
        .dark #mv .mv-window { border-color: #2b2927; }
        .mv-window::before { content: ""; position: absolute; inset: 0; background: linear-gradient(118deg, rgba(255, 255, 255, 0) 30%, rgba(255, 255, 255, 0.13) 36%, rgba(255, 255, 255, 0) 46%, rgba(255, 255, 255, 0) 58%, rgba(255, 255, 255, 0.07) 62%, rgba(255, 255, 255, 0) 68%); }
        /* the speak-through */
        .mv-window::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 44%;
            width: 4.6rem;
            aspect-ratio: 1;
            translate: -50% -50%;
            border-radius: 50%;
            border: 0.3rem solid #b08d57;
            background: radial-gradient(circle, #050505 0 26%, rgba(5, 5, 5, 0) 30%) 50% 50% / 0.72rem 0.72rem, #262422;
        }
        .mv-sill { position: relative; height: 1.3rem; border-radius: 0.2rem; background: linear-gradient(#d0ae73, #8a6a3a); box-shadow: 0 0.6rem 0.9rem -0.5rem rgba(0, 0, 0, 0.6); }
        .mv-sill::before { content: ""; position: absolute; left: 30%; right: 30%; top: -0.55rem; height: 0.55rem; border-radius: 0.3rem 0.3rem 0 0; background: #050505; }
        .mv-stub {
            position: absolute;
            left: 50%;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 4.9rem;
            width: min(92%, 21.5rem);
            color: #141312;
            filter: drop-shadow(0 0.9rem 0.9rem rgba(0, 0, 0, 0.4));
        }
        .mv-stub-1 { bottom: 1.1rem; translate: -44% 0; rotate: -4deg; z-index: 2; }
        .mv-stub-2 { bottom: -1.6rem; translate: -60% 0; rotate: 7deg; z-index: 1; }
        .mv-stub-main,
        .mv-stub-tear { background-color: #fbf7ee; padding: 0.85rem 0.9rem 0.75rem; }
        .mv-stub-main {
            -webkit-mask: radial-gradient(circle at 100% 0, transparent 0.5rem, #000 0.53rem) top right / 100% 51% no-repeat, radial-gradient(circle at 100% 100%, transparent 0.5rem, #000 0.53rem) bottom right / 100% 51% no-repeat;
            mask: radial-gradient(circle at 100% 0, transparent 0.5rem, #000 0.53rem) top right / 100% 51% no-repeat, radial-gradient(circle at 100% 100%, transparent 0.5rem, #000 0.53rem) bottom right / 100% 51% no-repeat;
        }
        .mv-stub-tear {
            display: grid;
            align-content: center;
            justify-items: center;
            gap: 0.4rem;
            border-inline-start: 2px dashed rgba(20, 19, 18, 0.5);
            -webkit-mask: radial-gradient(circle at 0 0, transparent 0.5rem, #000 0.53rem) top left / 100% 51% no-repeat, radial-gradient(circle at 0 100%, transparent 0.5rem, #000 0.53rem) bottom left / 100% 51% no-repeat;
            mask: radial-gradient(circle at 0 0, transparent 0.5rem, #000 0.53rem) top left / 100% 51% no-repeat, radial-gradient(circle at 0 100%, transparent 0.5rem, #000 0.53rem) bottom left / 100% 51% no-repeat;
        }
        .mv-stub small { display: block; font-family: var(--mv-list); font-weight: 700; font-size: 0.64rem; letter-spacing: 0.2em; text-transform: uppercase; line-height: 1.3; }
        .mv-stub-main small:first-child { color: #b50218; }
        .mv-stub-act { font-family: var(--mv-sign); font-size: 2.1rem; letter-spacing: 0.03em; line-height: 1; margin-block: 0.25rem 0.3rem; }
        .mv-stub-tear b { font-family: var(--mv-sign); font-weight: 400; font-size: 1.8rem; line-height: 1; }
        .mv-barcode { width: 100%; height: 1.5rem; background: repeating-linear-gradient(90deg, #141312 0 2px, rgba(0, 0, 0, 0) 2px 3px, #141312 3px 4px, rgba(0, 0, 0, 0) 4px 7px, #141312 7px 10px, rgba(0, 0, 0, 0) 10px 11px, #141312 11px 12px, rgba(0, 0, 0, 0) 12px 15px); }
        .mv-fee { display: flex; justify-content: space-between; align-items: baseline; gap: 1rem; margin-top: 2rem; padding: 0.9rem 0 0.75rem; border-block: 3px solid var(--mv-ink); font-family: var(--mv-list); font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; }
        .mv-fee b { font-family: var(--mv-sign); font-weight: 400; font-size: 2.6rem; letter-spacing: 0.03em; line-height: 1; color: var(--mv-red); }
        .mv-trio { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 820px) { .mv-trio { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mv-trio .mv-feat { padding: 1.5rem 1.4rem 1.4rem; background: var(--mv-wall); border: 1px solid var(--mv-line); border-radius: 0.3rem; }

        /* ---------------------------------------------------------------
           The rest of the week: six poster cases along the wall
           --------------------------------------------------------------- */
        .mv-cases { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.6rem; }
        @media (min-width: 700px) { .mv-cases { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1060px) { .mv-cases { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mv-case {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 3.6rem 1.5rem 1.6rem;
            overflow: hidden;
            background: var(--mv-wall);
            border: 0.42rem solid var(--mv-steel);
            border-radius: 0.3rem;
            box-shadow: 0 0 0 1px var(--mv-steel-edge), 0 1.4rem 2.2rem -1.5rem rgba(21, 20, 18, 0.6);
        }
        .dark #mv .mv-case { box-shadow: 0 0 0 1px var(--mv-steel-edge), inset 0 3.5rem 3rem -2.4rem rgba(255, 226, 170, 0.14); }
        .mv-case::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(116deg, rgba(255, 255, 255, 0) 38%, rgba(255, 255, 255, 0.34) 43%, rgba(255, 255, 255, 0) 50%);
            translate: -75% 0;
            opacity: 0;
            transition: translate 0.9s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.3s ease;
            pointer-events: none;
        }
        .dark #mv .mv-case::after { background: linear-gradient(116deg, rgba(255, 255, 255, 0) 38%, rgba(255, 255, 255, 0.09) 43%, rgba(255, 255, 255, 0) 50%); }
        .mv-case:hover::after { translate: 55% 0; opacity: 1; }
        .mv-case-day {
            position: absolute;
            inset: 0 0 auto 0;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 0.5rem 1.1rem 0.3rem;
            background: #1c1b19;
            color: #ffb84d;
            font-family: var(--mv-sign);
            font-size: 1.45rem;
            letter-spacing: 0.14em;
            line-height: 1.1;
        }
        .dark #mv .mv-case-day { background: #2b2927; }
        .mv-case-day span { font-family: var(--mv-list); font-weight: 700; font-size: 0.66rem; letter-spacing: 0.22em; text-transform: uppercase; color: #cbc5b9; }
        .mv-case .mv-feat-top h3 { font-family: var(--mv-sign); font-weight: 400; font-size: 1.95rem; letter-spacing: 0.03em; text-transform: uppercase; line-height: 1; }

        /* ---------------------------------------------------------------
           Every kind of room: six small signs
           --------------------------------------------------------------- */
        .mv-minis { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 1.75rem; }
        @media (min-width: 660px) { .mv-minis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .mv-minis { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mv-mini { display: flex; flex-direction: column; }
        .mv-mini-sign {
            container-type: inline-size;
            position: relative;
            display: grid;
            align-items: center;
            min-height: 7.2rem;
            padding: 1.15rem 0.6rem 0.6rem;
            border-radius: 0.35rem;
            background: linear-gradient(#262422, #131211);
            box-shadow: 0 0 0 1px #000, 0 1.1rem 1.6rem -1rem rgba(21, 20, 18, 0.6);
            transition: translate 0.3s cubic-bezier(0.3, 1.4, 0.5, 1);
        }
        .mv-mini:hover .mv-mini-sign { translate: 0 -0.3rem; }
        .mv-mini-sign::before {
            content: "";
            position: absolute;
            inset: 0.32rem 0.7rem auto 0.7rem;
            height: 0.5rem;
            background: radial-gradient(circle, #ece1c8 0 30%, #8f8571 34% 44%, rgba(0, 0, 0, 0) 48%) 50% 0 / 1.3rem 0.5rem repeat-x;
        }
        .dark #mv .mv-mini-sign::before { background-image: radial-gradient(circle, #fff 0 22%, #ffb84d 30% 44%, rgba(255, 170, 60, 0) 70%); }
        .dark #mv .mv-mini-sign { box-shadow: 0 0 0 1px rgba(244, 239, 229, 0.16), 0 0 2.2rem rgba(255, 196, 110, 0.16); }
        .mv-mini-board { background-color: #fbf7ee; border-radius: 0.1rem; box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.45); }
        .mv-mini .mv-tiles { font-size: 9.6cqi; padding-block: 0.22em; }
        .mv-mini p { margin-top: 1.15rem; color: var(--mv-ink-2); }
        .mv-notes .mv-mini-sign { min-height: 0; margin-bottom: 0.9rem; }
        .mv-notes .mv-mini-sign:hover { translate: none; }
        .mv-notes .mv-tiles { font-size: clamp(1.3rem, 5.8cqi, 2.3rem); padding-block: 0.2em; }
        .mv-mini a { margin-top: auto; padding-top: 1rem; align-self: flex-start; }

        /* ---------------------------------------------------------------
           Three steps: numbered the way the doors are
           --------------------------------------------------------------- */
        .mv-steps { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem 2rem; counter-reset: mv-step; }
        @media (min-width: 860px) { .mv-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mv-step { position: relative; }
        .mv-step-no {
            container-type: inline-size;
            display: inline-block;
            width: 7.4rem;
            padding: 0.42rem;
            border-radius: 0.3rem;
            background: linear-gradient(#262422, #131211);
            box-shadow: 0 0 0 1px #000;
        }
        .dark #mv .mv-step-no { box-shadow: 0 0 0 1px rgba(244, 239, 229, 0.16), 0 0 1.6rem rgba(255, 196, 110, 0.2); }
        .mv-step-no .mv-mini-board { display: block; }
        .mv-step-no .mv-tiles { font-size: 46cqi; padding-block: 0.1em; }
        /* a run of lamps from one door to the next */
        @media (min-width: 860px) {
            .mv-step + .mv-step::before {
                content: "";
                position: absolute;
                top: 2.1rem;
                right: calc(100% + 0.6rem);
                width: calc(100% - 8.6rem);
                height: 0.5rem;
                background: radial-gradient(circle, var(--mv-red) 0 32%, rgba(0, 0, 0, 0) 36%) 0 0 / 1.2rem 0.5rem repeat-x;
            }
        }
        .mv-step h3 { margin-top: 1.4rem; font-family: var(--mv-sign); font-weight: 400; font-size: 2.3rem; letter-spacing: 0.03em; line-height: 1; text-transform: uppercase; }
        .mv-step p { margin-top: 0.8rem; max-width: 23rem; color: var(--mv-ink-2); }

        /* ---------------------------------------------------------------
           Also showing: key features as short listings
           --------------------------------------------------------------- */
        .mv-also-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 4rem; align-items: start; }
        @media (min-width: 960px) { .mv-also-grid { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); } }
        .mv-also { border-top: 3px solid var(--mv-ink); }
        .mv-also a {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 1rem;
            padding: 1.15rem 0.75rem 1rem;
            border-bottom: 1px solid var(--mv-line);
            transition: background-color 0.2s ease, padding 0.25s ease;
        }
        .mv-also a:hover { background: var(--mv-wall); padding-inline: 1.25rem 0.75rem; }
        .mv-also strong { display: block; font-family: var(--mv-sign); font-weight: 400; font-size: 2rem; letter-spacing: 0.03em; line-height: 1; text-transform: uppercase; transition: color 0.2s ease; }
        .mv-also a:hover strong { color: var(--mv-red); }
        .mv-also small { display: block; margin-top: 0.35rem; font-size: 0.975rem; color: var(--mv-ink-2); }
        .mv-also svg { width: 1.4rem; height: 1.4rem; color: var(--mv-red); transition: translate 0.25s ease; }
        .mv-also a:hover svg { translate: 0.3rem 0; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the paint changes.
           --------------------------------------------------------------- */
        #mv .mv-plans > section { background: var(--mv-street-2); }
        #mv .mv-plans h2 { font-family: var(--mv-sign); font-weight: 400; text-transform: uppercase; letter-spacing: 0.015em; line-height: 0.95; font-size: clamp(2.3rem, 5vw, 3.8rem); color: var(--mv-ink); }
        #mv .mv-plans h2 + p { color: var(--mv-ink-2); font-size: 1.0625rem; }
        #mv .mv-plans .grid > div { background: var(--mv-wall); border: 2px solid var(--mv-ink); border-radius: 0.3rem; box-shadow: none; color: var(--mv-ink); }
        #mv .mv-plans .grid > div:nth-child(2) { border-color: #d0021b; box-shadow: 0 0 0 2px #d0021b; }
        #mv .mv-plans .grid > div:hover { box-shadow: 0 1.2rem 1.8rem -1.2rem rgba(21, 20, 18, 0.6); }
        #mv .mv-plans .grid > div:nth-child(2):hover { box-shadow: 0 0 0 2px #d0021b, 0 1.2rem 1.8rem -1.2rem rgba(208, 2, 27, 0.6); }
        #mv .mv-plans .grid > div span,
        #mv .mv-plans .grid > div p,
        #mv .mv-plans .grid > div li { color: var(--mv-ink-2); }
        #mv .mv-plans .grid > div .text-3xl { font-family: var(--mv-sign); font-weight: 400; font-size: 3.2rem; letter-spacing: 0.02em; color: var(--mv-ink); }
        #mv .mv-plans .grid > div .uppercase { font-family: var(--mv-list); color: var(--mv-ink); }
        #mv .mv-plans .grid > div .rounded-full { background: #d0021b; color: #fff; border-radius: 0.2rem; }
        #mv .mv-plans .grid > div svg { color: var(--mv-red-ink); }
        #mv .mv-plans a.font-medium { color: var(--mv-ink); border-bottom: 2px solid var(--mv-red); }
        #mv .mv-plans a.rounded-2xl { background: #d0021b; color: #fff; border-radius: 0.3rem; box-shadow: none; font-family: var(--mv-list); font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; }
        #mv .mv-plans a.rounded-2xl:hover { box-shadow: 0 0.7rem 1.3rem -0.6rem rgba(208, 2, 27, 0.75); }

        #mv .mv-keep > section { background: var(--mv-street-2); border-top: 3px solid var(--mv-ink); }
        #mv .mv-keep h2 { font-family: var(--mv-sign); font-weight: 400; text-transform: uppercase; letter-spacing: 0.02em; font-size: clamp(2.1rem, 4vw, 3rem); line-height: 1; color: var(--mv-ink); }
        #mv .mv-keep p.uppercase { font-family: var(--mv-list); font-weight: 700; letter-spacing: 0.22em; color: var(--mv-red-ink); }
        #mv .mv-keep .grid > a { background: var(--mv-wall); border: 1px solid var(--mv-line); border-radius: 0.3rem; }
        #mv .mv-keep .grid > a:hover { border-color: var(--mv-red); box-shadow: 0 1.2rem 1.8rem -1.2rem rgba(21, 20, 18, 0.6); }
        #mv .mv-keep .grid > a > span:first-child { display: none; }
        #mv .mv-keep .grid > a h3 { color: var(--mv-ink); font-family: var(--mv-list); font-weight: 700; font-size: 1.15rem; letter-spacing: 0.02em; }
        #mv .mv-keep .grid > a p { color: var(--mv-ink-2); }
        #mv .mv-keep .grid > a > span:last-child,
        #mv .mv-keep a.self-start { color: var(--mv-red-ink); }

        /* ---------------------------------------------------------------
           Down the street: the neighbours' signs
           --------------------------------------------------------------- */
        .mv-street-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: end; gap: 1.25rem; margin-bottom: 2.25rem; }
        .mv-blades { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.1rem; }
        @media (min-width: 900px) { .mv-blades { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.5rem; } }
        .mv-blade {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2.2rem;
            min-height: 9.5rem;
            padding: 1.1rem 1.1rem 0.95rem;
            border-radius: 0.35rem;
            background: linear-gradient(#262422, #131211);
            box-shadow: 0 0 0 1px #000, inset 0 0 0 0.3rem #1c1b19, inset 0 0 0 0.38rem rgba(255, 184, 77, 0.55);
            color: #fbf7ee;
            transition: translate 0.3s cubic-bezier(0.3, 1.4, 0.5, 1), box-shadow 0.3s ease;
        }
        .dark #mv .mv-blade { box-shadow: 0 0 0 1px rgba(244, 239, 229, 0.16), inset 0 0 0 0.3rem #1c1b19, inset 0 0 0 0.38rem rgba(255, 184, 77, 0.55); }
        #mv .mv-blade:hover { translate: 0 -0.35rem; box-shadow: 0 0 0 1px #000, inset 0 0 0 0.3rem #1c1b19, inset 0 0 0 0.38rem #ffb84d, 0 0 2rem rgba(255, 184, 77, 0.35); }
        .mv-blade strong { font-family: var(--mv-sign); font-weight: 400; font-size: clamp(1.6rem, 3vw, 2.3rem); letter-spacing: 0.04em; line-height: 0.95; text-transform: uppercase; }
        .mv-blade span { display: inline-flex; align-items: center; gap: 0.4rem; font-family: var(--mv-list); font-weight: 700; font-size: 0.74rem; letter-spacing: 0.2em; text-transform: uppercase; color: #ffb84d; }
        .mv-blade svg { width: 0.95rem; height: 0.95rem; transition: translate 0.2s ease; }
        .mv-blade:hover svg { translate: 0.25rem 0; }

        /* ---------------------------------------------------------------
           Questions at the window
           --------------------------------------------------------------- */
        .mv-faq-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .mv-faq-grid { grid-template-columns: minmax(0, 0.72fr) minmax(0, 1.28fr); }
            .mv-faq-head { position: sticky; top: 6.5rem; }
        }
        .mv-faq-head .mv-h2 { font-size: clamp(2.7rem, 5.2vw, 4.4rem); }
        .mv-qa { border-top: 3px solid var(--mv-ink); }
        .mv-qa details { border-bottom: 1px solid var(--mv-line); }
        .mv-qa summary { display: grid; grid-template-columns: 2.9rem minmax(0, 1fr) 1.4rem; align-items: start; gap: 0.75rem; padding: 1.3rem 0.25rem 1.15rem; cursor: pointer; }
        .mv-q-no { font-family: var(--mv-sign); font-size: 1.6rem; line-height: 1.05; color: var(--mv-red); }
        .mv-qa h3 { font-family: var(--mv-list); font-weight: 700; font-size: 1.22rem; line-height: 1.3; letter-spacing: 0.01em; }
        .mv-qa summary i { position: relative; width: 1.4rem; height: 1.4rem; margin-top: 0.15rem; }
        .mv-qa summary i::before,
        .mv-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .mv-qa summary i::after { rotate: 90deg; }
        .mv-qa details[open] summary i::after { rotate: 0deg; }
        .mv-qa details p { padding: 0 0.25rem 1.6rem 3.9rem; max-width: 46rem; color: var(--mv-ink-2); }
        @media (max-width: 560px) { .mv-qa details p { padding-inline-start: 0.25rem; } }

        /* ---------------------------------------------------------------
           The finale: the sign again, with your name on it
           --------------------------------------------------------------- */
        .mv-finale { padding-block: clamp(4.5rem, 9vw, 7.5rem); text-align: center; }
        .mv-finale::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(48rem 26rem at 50% 62%, rgba(255, 196, 110, 0.2), rgba(255, 196, 110, 0) 72%);
            pointer-events: none;
        }
        .mv-finale .mv-wrap { position: relative; }
        .mv-finale .mv-sub { margin-inline: auto; }
        .mv-finale .mv-sign { max-width: 52rem; margin: clamp(2.5rem, 5vw, 3.75rem) auto 0; }
        .mv-claim-tiles { padding-block: 0.3em 0.36em; font-size: min(10.5cqi, calc(100cqi / (var(--n, 10) * 0.7))); }
        .mv-claim-tiles .mv-line { flex-wrap: nowrap; }
        .mv-claim-suffix { padding: 0.55rem 1rem 0.5rem; border-top: 1px solid rgba(20, 19, 18, 0.25); font-family: var(--mv-mono); font-weight: 700; font-size: clamp(0.8rem, 2.2cqi, 1.05rem); letter-spacing: 0.08em; color: #46423b; }
        .mv-claim-row { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.9rem; max-width: 42rem; margin: 2.25rem auto 0; }
        @media (min-width: 640px) { .mv-claim-row { grid-template-columns: minmax(0, 1fr) auto; } }
        #mv .mv-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1.1rem;
            border: 2px solid rgba(244, 239, 229, 0.35);
            border-radius: 0.3rem;
            background: #161514;
            font-family: var(--mv-mono);
            font-weight: 700;
            /* never under 16px: iOS zooms the page when a smaller input takes focus */
            font-size: clamp(1rem, 3.2vw, 1.05rem);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        #mv .mv-claim:focus-within { border-color: #ffb84d; box-shadow: 0 0 0 4px rgba(255, 184, 77, 0.25); }
        #mv .mv-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: #f4efe5;
            box-shadow: none;
            outline: none;
        }
        #mv .mv-claim input::placeholder { color: #8d877c; }
        .mv-claim > span { flex: none; color: #a09a8f; user-select: none; }
        /* The narrowest phones: the address is set a size down, so "your-venue" is not cut short. */
        @media (max-width: 350px) { .mv-claim > span { font-size: 0.8125rem; } }
        .mv-fine { margin-top: 1.25rem; font-size: 0.95rem; color: #a09a8f; }

        @media (prefers-reduced-motion: reduce) {
            #mv .mv-bulbs i::after,
            #mv .mv-tonight::after { animation: none; }
            .mv-btn, .mv-print, .mv-case::after, .mv-mini-sign, .mv-blade, .mv-also a, .mv-gig { transition: none; }
        }
    </style>

    @php
        // The show day. Doors is the only stop the public ever gets.
        $showDay = [
            ['14:00', 'Load-in',    'The truck is outside and nobody has the code.', 180, false],
            ['17:00', 'Soundcheck', 'Three acts, one PA, and a support band running late.', 120, false],
            ['19:00', 'Doors',      'The only time most calendars ever show.',        60,  true],
            ['20:00', 'Oda',        'Opener. 30 minutes.',                            75,  false],
            ['21:15', 'The Fell',   'Headline. 70 minutes and a curfew to beat.',     105, false],
            ['23:00', 'Curfew',     'Lights up, load-out, do it again Thursday.',     0,   false],
        ];

        $bill = [
            ['20:00', 'Oda', 'Opener', '12'],
            ['21:15', 'The Fell', 'Headline', '47'],
            ['22:40', 'DJ set', 'Late', '5'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for music venues?',
                'a' => 'Yes. Publishing your listings, adding set times for every act on a bill, running recurring residencies, splitting rooms into sub-schedules, accepting booking requests, and two-way sync with Google, Outlook or CalDAV are all free forever, and so is free registration for a door that charges nothing. Scanning the QR on a ticket costs nothing on any plan either. Putting a price on a ticket is Pro at '.plan_price($proMonthly).' a month, which brings the live check-in dashboard for a busy door and passes with it.',
            ],
            [
                'q' => 'Can I publish set times for each band on the bill?',
                'a' => 'Yes, on every plan. Give a show its parts, and each act gets a name, an optional description and a start and end time. They appear in order on the public event page, so the bill answers the question instead of you answering it fourteen times on the day.',
            ],
            [
                'q' => 'Do the bands on the bill need their own account?',
                'a' => 'No. Name them on the show and the event page lists the whole lineup, with a link to each act that has a page. An act who is not on Event Schedule gets a page of its own, which says which schedule created it and that the act has not claimed it, and it stays out of search engines until they do. If you add their email address, they can claim it by signing in with that address, and the shows you listed stay on it.',
            ],
            [
                'q' => 'Can photos and video be attached to the band that played?',
                'a' => 'Yes. When a show has parts, fan photos, video and comments attach to the part rather than to the whole night, so a three-band bill ends up with three galleries instead of one pile. Everything waits in an approval queue before it appears.',
            ],
            [
                'q' => 'Can I run more than one room from the same page?',
                'a' => 'Yes, on every plan. Sub-schedules keep the main room and the back room apart on one link, so somebody looking for the small-room show is not scrolling through two months of everything else.',
            ],
            [
                'q' => 'Can bands ask to play at my venue?',
                'a' => 'Yes. Turn on Accept requests and artists can submit a show from your public page. Submissions collect on your Requests tab, where you accept or decline before anything reaches your calendar. On Pro you can add your own questions to that form, so the details you always end up chasing arrive with the request.',
            ],
            [
                'q' => 'What do you charge on ticket sales?',
                'a' => 'Nothing. Event Schedule takes zero platform fees. You connect your own Stripe or PayPal account, the money lands there, and the only deduction is the provider\'s own processing. A payment link or cash on the door works too. There is no per-ticket cut and no booking fee added on top of your price.',
            ],
            [
                'q' => 'Can fans ask to hear when tickets go on sale?',
                'a' => 'Yes, on every plan. Switch on the "Notify me" card, announce a show before tickets are ready, and the event page offers "Tell me when tickets go on sale". Fans leave an email address, with no account, and hear when tickets go on sale, if the show is cancelled, and again shortly before it starts, plus any change notice you choose to send. You see how many are waiting on the event\'s Tickets panel before you open sales, and it never counts against your newsletter allowance.',
            ],
            [
                'q' => 'Can I refund tickets if a show is cancelled?',
                'a' => 'Yes, from the Sales page, on Pro. A Stripe or PayPal sale goes back through the provider, in full or in part, and the sale only changes once the money has moved. A full refund puts those tickets back on sale, and a partial one leaves the ticket valid. A sale taken another way, like cash on the door or a payment link, is marked as refunded and you return the money yourself.',
            ],
        ];

        // Letters for a changeable-letter board. Each character becomes an empty tile whose face
        // is drawn by CSS from data-l, so the words a reader or a crawler gets are the plain
        // sentence beside it, once, and the tiles are free to be decoration (a red zero where
        // the box ran out of O's). --i staggers the hanging of the hero's letters.
        $mvTileIndex = 0;
        $mvTiles = function (string $text, array $opts = []) use (&$mvTileIndex) {
            $html = '';
            foreach (explode(' ', mb_strtoupper($text)) as $w => $word) {
                $html .= '<span class="mv-word'.(in_array($w, $opts['red'] ?? [], true) ? ' mv-word-red' : '').'">';
                foreach (mb_str_split($word) as $c => $ch) {
                    $class = 'mv-t';
                    if (isset($opts['swap'][$w][$c])) {
                        $ch = $opts['swap'][$w][$c];
                        $class .= ' mv-t-num';
                    }
                    if (($opts['old'][$w] ?? null) === $c) {
                        $class .= ' mv-t-old';
                    }
                    $html .= '<i class="'.$class.'" data-l="'.e($ch).'"'.(empty($opts['plain']) ? ' style="--i: '.($mvTileIndex++).';"' : '').'></i>';
                }
                $html .= '</span>';
            }

            return $html;
        };

        // The listings. The first four fields of the first three rows are the page's own sample
        // shows (room, capacity, show, when); the rest is set dressing for a venue calendar.
        $mvGigs = [
            ['Main room', '450 cap', 'The Fell', 'Thu 12', 'With Oda', 'Doors 19:00 · Show 20:00', '$22', 'low', 'main'],
            ['Back room', '80 cap', 'Oda solo', 'Fri 13', 'Seated, and early', 'Doors 19:30', '$18', 'sale', 'back'],
            ['Main room', '450 cap', 'Residency', 'Every Tue', 'The house band and a guest', 'Doors 20:00', 'Free entry', 'weekly', 'main'],
            ['Main room', '450 cap', 'Kestrel Hall', 'Fri 20', '4-piece', 'Doors 19:00 · Show 20:15', '$25', 'new', 'main'],
            ['Back room', '80 cap', 'DJ set', 'Sat 21', 'Late', 'From 22:40', '$18', 'sold', 'back'],
        ];
        $mvStatus = [
            'low' => ['Low tickets', 'mv-status-low'],
            'sale' => ['On sale', ''],
            'weekly' => ['Every week', ''],
            'new' => ['Just announced', 'mv-status-new'],
            'sold' => ['Sold out', 'mv-status-sold'],
        ];

        $mvArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $mvDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="mv">

        <!-- ============================================================ -->
        <!-- 1. Hero: the sign over the door                              -->
        <!-- ============================================================ -->
        <section class="mv-hero" id="top">
            <div class="mv-wrap">
                <div class="mv-sign">
                    <div class="mv-bulbs mv-bulbs-t" aria-hidden="true">@for ($mvB = 0; $mvB < 34; $mvB++)<i></i>@endfor</div>
                    <div class="mv-bulbs mv-bulbs-r" aria-hidden="true">@for ($mvB = 0; $mvB < 11; $mvB++)<i></i>@endfor</div>
                    <div class="mv-bulbs mv-bulbs-b" aria-hidden="true">@for ($mvB = 0; $mvB < 34; $mvB++)<i></i>@endfor</div>
                    <div class="mv-bulbs mv-bulbs-l" aria-hidden="true">@for ($mvB = 0; $mvB < 11; $mvB++)<i></i>@endfor</div>

                    <div class="mv-board">
                        <h1 class="mv-h1">
                            <x-marketing.hero-eyebrow class="mv-strip">
                                Music venue calendar for live rooms
                            </x-marketing.hero-eyebrow>
                            <span class="mv-row">
                                <span class="sr-only">Every show is nine hours long.</span>
                                <span class="mv-tiles" aria-hidden="true">
                                    <span class="mv-line">{!! $mvTiles('Every show is', ['old' => [1 => 2]]) !!}</span>
                                    <span class="mv-line">{!! $mvTiles('nine hours long.', ['swap' => [2 => [1 => '0']]]) !!}</span>
                                </span>
                            </span>
                            <span class="mv-row">
                                <span class="sr-only">Your calendar shows one of them.</span>
                                <span class="mv-tiles" aria-hidden="true">
                                    <span class="mv-line">{!! $mvTiles('Your calendar shows', ['old' => [1 => 5]]) !!}</span>
                                    <span class="mv-line">{!! $mvTiles('one of them.', ['red' => [0]]) !!}</span>
                                </span>
                            </span>
                        </h1>
                    </div>

                    <!-- Venue-type ticker, along the lip of the sign -->
                    <div class="mv-lip">
                        <div class="es-marquee" data-marquee="1">
                            <div class="es-marquee-track">
                                @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                    @foreach (['Listening Room', 'Small Club', 'Mid-Size', 'Concert Hall', 'Jazz Club', 'Amphitheater', 'Multi-Room', 'House Concert', 'Folk Club', 'Warehouse'] as $chip)
                                        <span @if ($chipCopy === 1) aria-hidden="true" @endif class="mv-lip-item">{{ $chip }}</span>
                                    @endforeach
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mv-under">
                    <div>
                        <p class="mv-lede es-fade-up es-d-2">
                            Put the whole show day on one link: set times for every act on the bill, tickets with zero platform fees, and a door that knows who is actually inside.
                        </p>
                        <div class="mv-cta es-fade-up es-d-3">
                            <a href="#order" class="mv-btn mv-btn-ghost">
                                See the running order
                                {!! $mvDown !!}
                            </a>
                            <a href="{{ app_url('/sign_up?type=venue') }}" class="mv-btn">
                                Create your venue calendar
                                {!! $mvArrow !!}
                            </a>
                        </div>
                        <div class="mv-one es-fade-up es-d-4" aria-hidden="true">
                            <div class="mv-one-label"><span>On the calendar</span><span>1 line</span></div>
                            <div class="mv-one-row">
                                <div class="mv-date">Thu <b>12</b> Jun</div>
                                <div>
                                    <p class="mv-gig-name">The Fell</p>
                                    <p class="mv-gig-support">Main room</p>
                                </div>
                                <span class="mv-status">Doors 19:00</span>
                            </div>
                            <p class="mv-one-note">On the day sheet: 6</p>
                        </div>
                    </div>

                    <!-- The day sheet: nine hours, and the one line that gets published -->
                    <div class="mv-hang es-fade-up es-d-4">
                        <div class="mv-tonight" aria-hidden="true">Tonight</div>
                        <div class="mv-sheet">
                            <div class="mv-sheet-head"><span>Thursday &middot; main room</span><span aria-hidden="true">Day sheet</span></div>
                            <ol>
                                @foreach ($showDay as [$time, $name, $note, $gap, $isNow])
                                    <li @class(['mv-sheet-row', 'mv-sheet-now' => $isNow])>
                                        <span class="mv-sheet-time">{{ $time }}</span>
                                        <span class="mv-sheet-name">{{ $name }}</span>
                                        @if ($isNow)<span class="mv-stamp">Published</span>@endif
                                        <p>{{ $note }}</p>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The gap: under the soffit                                 -->
        <!-- ============================================================ -->
        <section id="cost" class="mv-night mv-soffit mv-section" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-head mv-head-center" style="margin-bottom: 0;">
                    <span class="mv-kick" data-reveal>The gap</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        You run nine hours. You publish <span class="mv-em">one number.</span>
                    </h2>
                </div>

                <div class="mv-stats" data-reveal-group="140">
                    <div class="mv-stat" data-reveal>
                        <p class="mv-stat-tag">The day</p>
                        <h3>
                            <span class="mv-flap"><b>9</b><i class="mv-flap-top" data-d="1" aria-hidden="true"></i><i class="mv-flap-bot" data-d="1" aria-hidden="true"></i><i class="mv-flap-land" data-d="9" aria-hidden="true"></i></span> hours
                        </h3>
                        <p>Load-in at two, curfew at eleven. That is the job, and it is the same shape every show night.</p>
                    </div>
                    <div class="mv-stat" data-reveal>
                        <p class="mv-stat-tag">The listing</p>
                        <h3>
                            <span class="mv-flap"><b>1</b><i class="mv-flap-top" data-d="9" aria-hidden="true"></i><i class="mv-flap-bot" data-d="9" aria-hidden="true"></i><i class="mv-flap-land" data-d="1" aria-hidden="true"></i></span> time
                        </h3>
                        <p>Doors. Everything else lives in a group chat, a pinned message, and your head.</p>
                    </div>
                    <div class="mv-stat" data-reveal>
                        <p class="mv-stat-tag">The consequence</p>
                        <h3>
                            <span class="mv-flap"><b>3</b><i class="mv-flap-top" data-d="0" aria-hidden="true"></i><i class="mv-flap-bot" data-d="0" aria-hidden="true"></i><i class="mv-flap-land" data-d="3" aria-hidden="true"></i></span> bands asking
                        </h3>
                        <p>Plus a tour manager, the engineer, and everyone who bought a ticket for the opener.</p>
                    </div>
                </div>

                <p class="mv-gap-foot" data-reveal>
                    The running order already exists. It just is not anywhere the public can read it.
                    <a href="#order">
                        Put it on the page
                        {!! $mvDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The running order: the frame by the door                  -->
        <!-- ============================================================ -->
        <section id="order" class="mv-section" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-head">
                    <span class="mv-kick" data-reveal>The running order</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Give the show its <span class="mv-em">parts.</span>
                    </h2>
                    <p class="mv-sub" data-reveal style="--reveal-delay: 0.16s;">
                        A show is not one time. Add each act with its own start time and they publish in order on the event page, on every plan.
                    </p>
                </div>

                <div class="mv-order">
                    <div data-reveal="left">
                        <div class="mv-frame">
                            <div class="mv-frame-sheet">
                                <div class="mv-frame-top"><span>Public event page</span><b aria-hidden="true">Set times</b></div>
                                @foreach ([['19:00', 'Doors', 60], ['20:00', 'Oda', 75], ['21:15', 'The Fell', 0]] as [$oTime, $oName, $oGap])
                                    <div class="mv-frame-row">
                                        <span>{{ $oTime }}</span>
                                        <span>{{ $oName }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <p class="mv-order-note">
                            Times render publicly whenever an act has one. Leave them off and the bill still lists in order.
                        </p>
                    </div>

                    <div class="mv-notes" data-reveal-group="110">
                        <div class="mv-feat" data-reveal>
                            <div class="mv-feat-top">
                                <h3>Each act, its own time</h3>
                                <span class="mv-tier">Free</span>
                            </div>
                            <p>Name, start, end, and a description if the act needs one. Put them in order once and the public page shows the bill exactly as you set it.</p>
                        </div>
                        <div class="mv-feat" data-reveal>
                            <div class="mv-feat-top">
                                <h3>It answers the question for you</h3>
                                <span class="mv-tier">Free</span>
                            </div>
                            <p>"What time is the headline on" stops being a message you reply to and starts being a line on the page you already sent them.</p>
                        </div>
                        <div class="mv-feat" data-reveal>
                            <h3 class="mv-mini-sign">
                                <span class="sr-only">One link for the band too</span>
                                <span class="mv-mini-board" aria-hidden="true"><span class="mv-tiles"><span class="mv-line">{!! $mvTiles('One link for the band too', ['plain' => true, 'red' => [0]]) !!}</span></span></span>
                            </h3>
                            <span class="mv-tier">Free</span>
                            <p>
                                Name every act on the show, including ones who are not on Event Schedule. The event page lists the whole lineup and links each act that has a page, and an act new here gets a page of its own that stays out of search engines until they claim it. The show can surface on their schedule too, so their followers find your room through them.
                                <a href="{{ marketing_url('/docs/creating-events#claim') }}">How act pages work</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The bill: per-act galleries                               -->
        <!-- ============================================================ -->
        <section id="bill" class="mv-section mv-alt" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-head mv-head-center">
                    <span class="mv-kick" data-reveal>The bill</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three bands played. <span class="mv-em">Three galleries.</span>
                    </h2>
                    <p class="mv-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Once a show has parts, photos, video and comments attach to the act rather than to the night, so the opener's set is not buried under the headline's.
                    </p>
                </div>

                @php
                    // One light per set: the opener in amber, the headline in red, the late one in white.
                    $mvLights = ['#ffb84d', '#e3101f', '#f4efe5'];
                @endphp
                <div class="mv-acts" data-reveal-group="100">
                    @foreach ($bill as [$bTime, $bName, $bRole, $bCount])
                        <div class="mv-act" data-reveal>
                            <div class="mv-act-top">
                                <span>{{ $bTime }}</span>
                                <span class="mv-role">{{ $bRole }}</span>
                            </div>
                            <h3>{{ $bName }}</h3>
                            <div class="mv-prints" aria-hidden="true">
                                @for ($t = 0; $t < 4; $t++)
                                    <div class="mv-print" style="--r: {{ [-5, 3, -2, 6][$t] }}deg; --c: {{ $mvLights[$loop->index] }}; --x: {{ [24, 62, 40, 76][$t] }}%;"><i></i></div>
                                @endfor
                            </div>
                            <p class="mv-act-count">{{ $bCount }} photos from this set</p>
                        </div>
                    @endforeach
                </div>

                <p class="mv-foot" data-reveal>
                    Attendees add them with just a name and an email, and everything waits in an approval queue before it appears. Free covers 25 photos per schedule.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. The rooms: the listings                                   -->
        <!-- ============================================================ -->
        <section id="rooms" class="mv-section" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-rooms-head">
                    <div>
                        <span class="mv-kick" data-reveal>The rooms</span>
                        <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Main room and back room, <span class="mv-em">one link.</span>
                        </h2>
                        <p class="mv-sub" data-reveal style="--reveal-delay: 0.16s;">
                            Sub-schedules split one schedule into strands, so somebody who only cares about the 80-cap room is not scrolling past two months of main-room shows to find it. Free on every plan.
                        </p>
                    </div>
                    <ul class="mv-ticks" data-reveal>
                        <li>
                            <span>A residency is one recurring event with a day-of-week pattern, plus exceptions for the weeks you are dark.</span>
                        </li>
                        <li>
                            <span>Embed the calendar on the site you already have, so the listings live where people look you up.</span>
                        </li>
                        <li>
                            <span>Two-way sync with Google, Outlook and CalDAV keeps what is on the wall and what is in your phone from drifting apart.</span>
                        </li>
                    </ul>
                </div>

                <div class="mv-listings" data-reveal>
                    <fieldset class="mv-roomsigns">
                        <legend class="sr-only">Show the sample listings for one room</legend>
                        <input type="radio" name="mv-room" id="mv-room-all" checked>
                        <label for="mv-room-all">All rooms <small>One link</small></label>
                        <input type="radio" name="mv-room" id="mv-room-main">
                        <label for="mv-room-main">Main room <small>450 cap</small></label>
                        <input type="radio" name="mv-room" id="mv-room-back">
                        <label for="mv-room-back">Back room <small>80 cap</small></label>
                    </fieldset>

                    <div class="mv-month" aria-hidden="true">June <span>What's on</span></div>
                    <ol class="mv-gigs" aria-label="Sample listings">
                        @foreach ($mvGigs as [$rRoom, $rCap, $rShow, $rWhen, $rWith, $rTimes, $rPrice, $rState, $rKey])
                            @php
                                [$mvTop, $mvBig] = explode(' ', $rWhen);
                                [$mvStateLabel, $mvStateClass] = $mvStatus[$rState];
                            @endphp
                            <li class="mv-gig" data-room="{{ $rKey }}">
                                <div class="mv-date">
                                    @if (is_numeric($mvBig))
                                        {{ $mvTop }} <b>{{ $mvBig }}</b> Jun
                                    @else
                                        {{ $mvTop }} <b>{{ $mvBig }}</b>
                                    @endif
                                </div>
                                <div>
                                    <p class="mv-gig-name">{{ $rShow }}@if ($loop->first)<span class="mv-tag-tonight">Tonight</span>@endif</p>
                                    <p class="mv-gig-support">{{ $rWith }}</p>
                                </div>
                                <p class="mv-gig-meta"><b>{{ $rRoom }} &middot; {{ $rCap }}</b>{{ $rTimes }}</p>
                                <div class="mv-gig-end">
                                    <span class="mv-price">{{ $rPrice }}</span>
                                    @if ($loop->first)
                                        <span class="mv-flip" data-was="On sale"><span class="mv-status {{ $mvStateClass }}">{{ $mvStateLabel }}</span></span>
                                    @else
                                        <span class="mv-status {{ $mvStateClass }}">{{ $mvStateLabel }}</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Who plays: the stage door                                 -->
        <!-- ============================================================ -->
        <section id="playing" class="mv-night mv-stage mv-section" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-head">
                    <span class="mv-doorsign" aria-hidden="true" data-reveal>Stage door</span>
                    <div style="margin-top: 1.75rem;"><span class="mv-kick" data-reveal>Who plays</span></div>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Let bands come to you, <span class="mv-em">with the details attached.</span>
                    </h2>
                </div>

                <div class="mv-stage-grid">
                    <div data-reveal-group="110">
                        <div class="mv-feat" data-reveal>
                            <div class="mv-feat-top">
                                <h3>Accept requests</h3>
                                <span class="mv-tier">Free</span>
                            </div>
                            <p>Switch it on and artists submit a show from your public page. Everything waits on your Requests tab until you accept or decline it.</p>
                        </div>
                        <div class="mv-feat" data-reveal>
                            <div class="mv-feat-top">
                                <h3>Ask for what you always chase</h3>
                                <span class="mv-tier mv-tier-paid">Pro</span>
                            </div>
                            <p>Add your own questions to that form so set length, party size and a link arrive with the request instead of four emails later.</p>
                        </div>
                        <div class="mv-feat" data-reveal>
                            <div class="mv-feat-top">
                                <h3>Nothing lands unannounced</h3>
                                <span class="mv-tier">Free</span>
                            </div>
                            <p>A declined request never touches your calendar, and an accepted one arrives as a real event you can add the running order to.</p>
                        </div>
                    </div>

                    <div class="mv-ledger" aria-hidden="true" data-reveal="right">
                        <div class="mv-ledger-top">
                            <span>Requests</span>
                            <span>3 waiting</span>
                        </div>
                        @foreach ([['Kestrel Hall', 'Fri 2 May', '4-piece', '45 min'], ['Oda', 'Sat 10 May', 'Solo', '30 min'], ['The Fell', 'Thu 22 May', '5-piece', '70 min']] as [$qName, $qDate, $qSize, $qLen])
                            <div class="mv-ask">
                                <div>
                                    <b>{{ $qName }}</b>
                                    <small>{{ $qDate }} &middot; {{ $qSize }} &middot; {{ $qLen }}</small>
                                </div>
                                <span>Accept</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. The door: the box office                                  -->
        <!-- ============================================================ -->
        <section id="door" class="mv-section mv-alt" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-door">
                    <div>
                        <span class="mv-kick" data-reveal>The door</span>
                        <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                            We take <span class="mv-em">none of it.</span>
                        </h2>
                        <p class="mv-sub" data-reveal style="--reveal-delay: 0.16s;">
                            Event Schedule charges zero platform fees on ticket sales. You connect your own <a href="{{ marketing_url('/stripe') }}">Stripe</a> or <a href="{{ marketing_url('/paypal') }}">PayPal</a> account, the money lands in it, and the only deduction is the provider's own processing. A payment link or cash on the door works too.
                        </p>
                        <div class="mv-fee" aria-hidden="true" data-reveal style="--reveal-delay: 0.24s;">
                            <span>Platform fee</span>
                            <b>{{ plan_price(0) }}</b>
                        </div>
                    </div>

                    <div class="mv-office" aria-hidden="true" data-reveal="zoom">
                        <div class="mv-office-plate">Box office</div>
                        <div class="mv-window"></div>
                        <div class="mv-sill"></div>
                        <div class="mv-stub mv-stub-2" dir="ltr">
                            <div class="mv-stub-main">
                                <small>Admit one</small>
                                <div class="mv-stub-act">Oda solo</div>
                                <small>Fri 13 Jun &middot; Back room</small>
                            </div>
                            <div class="mv-stub-tear"><small>Door</small><b>$25</b></div>
                        </div>
                        <div class="mv-stub mv-stub-1" dir="ltr">
                            <div class="mv-stub-main">
                                <small>Admit one</small>
                                <div class="mv-stub-act">The Fell</div>
                                <small>Thu 12 Jun &middot; Main room &middot; Doors 19:00</small>
                                <div class="mv-barcode" style="margin-top: 0.6rem;"></div>
                            </div>
                            <div class="mv-stub-tear"><small>Advance</small><b>$22</b><small>No. 0451</small></div>
                        </div>
                    </div>
                </div>

                <div class="mv-trio" data-reveal-group="100">
                    <div class="mv-feat" data-reveal>
                        <div class="mv-feat-top">
                            <h3>Tiers that move on the clock</h3>
                            <span class="mv-tier">Free</span>
                        </div>
                        <p>Give a show more than one ticket type, each with its own sales window, and a rate that kicks in when someone buys several at once. Add-ons that attach to a ticket are on Pro.</p>
                    </div>
                    <div class="mv-feat" data-reveal>
                        <div class="mv-feat-top">
                            <h3>A QR on every ticket</h3>
                            <span class="mv-tier">Free</span>
                        </div>
                        <p>Scanning each code on the way in is free on every plan. On Pro the check-in dashboard counts who is actually inside, with a per-ticket breakdown so you can see which tier turned up.</p>
                    </div>
                    <div class="mv-feat" data-reveal>
                        <div class="mv-feat-top">
                            <h3>Everyone gets their own</h3>
                            <span class="mv-tier mv-tier-paid">Pro</span>
                        </div>
                        <p>Per-attendee tickets give each guest in a group their own confirmation and their own code, so one person is not stood at the door holding six.</p>
                    </div>
                </div>

                <p class="mv-foot" data-reveal>
                    For a free show, registration with a capacity limit works on every plan, so the room still has a real number attached to it. Announced before tickets are ready? Switch on the "Notify me" card and fans can leave an email address on the event page and hear when they go on sale, also on every plan.
                    <a href="{{ marketing_url('/docs/tickets#interest-list') }}">How the interest list works</a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Everything else: six poster cases                         -->
        <!-- ============================================================ -->
        <section id="rest" class="mv-section" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-head">
                    <span class="mv-kick" data-reveal>Everything else</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The rest of the week.
                    </h2>
                </div>

                <div class="mv-cases" data-reveal-group="80">
                    <!-- 1 -->
                    <div class="mv-case mv-feat" data-reveal>
                        <div class="mv-case-day" aria-hidden="true">Mon <span>Newsletters</span></div>
                        <div class="mv-feat-top">
                            <h3>Tell them yourself</h3>
                            <span class="mv-tier">Free</span>
                        </div>
                        <p>People follow your schedule and you email them when a show goes on sale. You can see open and click rates afterwards, so you know whether the announcement actually landed.</p>
                        <p>The numbers worth knowing before you plan around it: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.</p>
                    </div>
                    <!-- 2 -->
                    <div class="mv-case mv-feat" data-reveal>
                        <div class="mv-case-day" aria-hidden="true">Tue <span>Waitlist</span></div>
                        <div class="mv-feat-top">
                            <h3>When it sells out</h3>
                            <span class="mv-tier mv-tier-paid">Pro</span>
                        </div>
                        <p>Turn on the waitlist and people join once tickets are gone. If one is released they are notified automatically instead of you working back through replies.</p>
                    </div>
                    <!-- 3 -->
                    <div class="mv-case mv-feat" data-reveal>
                        <div class="mv-case-day" aria-hidden="true">Wed <span>Analytics</span></div>
                        <div class="mv-feat-top">
                            <h3>Know what is working</h3>
                            <span class="mv-tier">Free</span>
                        </div>
                        <p>Built-in analytics show page views, the devices people are on, and where the traffic came from. Enough to tell whether the announcement did anything, without installing a thing.</p>
                        <p>On Pro, add a poll to a show and let the room vote on the support slot or which night a residency should move to.</p>
                    </div>
                    <!-- 4 -->
                    <div class="mv-case mv-feat" data-reveal>
                        <div class="mv-case-day" aria-hidden="true">Thu <span>Passes</span></div>
                        <div class="mv-feat-top">
                            <h3>Passes for the regulars</h3>
                            <span class="mv-tier mv-tier-paid">Pro</span>
                        </div>
                        <p>Sell a multi-use pass or a membership that runs across a season of shows, with its own usage tracking and cancellation policy.</p>
                    </div>
                    <!-- 5 -->
                    <div class="mv-case mv-feat" data-reveal>
                        <div class="mv-case-day" aria-hidden="true">Fri <span>Graphics</span></div>
                        <div class="mv-feat-top">
                            <h3>The announcement post</h3>
                            <span class="mv-tier mv-tier-paid">Pro</span>
                        </div>
                        <p>Generate a graphic from a show in a story, square, portrait or landscape crop. It is built from the event, so the date, the room and the bill are already right.</p>
                        <p>
                            Running it online as well? Mark the show as an online event and paste the link to wherever you are streaming.
                            <a href="{{ marketing_url('/features/online-events') }}">How online events work</a>
                        </p>
                    </div>
                    <!-- 6 -->
                    <div class="mv-case mv-feat" data-reveal>
                        <div class="mv-case-day" aria-hidden="true">Sat <span>Drafts</span></div>
                        <div class="mv-feat-top">
                            <h3>Hold it back</h3>
                            <span class="mv-tier">Free</span>
                        </div>
                        <p>A show you have not announced yet can sit on the calendar as a draft, visible to you and never published to the public page.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Perfect for: six small signs                              -->
        <!-- ============================================================ -->
        <section id="who" class="mv-section mv-alt" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <div class="mv-head mv-head-center">
                    <span class="mv-kick" data-reveal>On every street</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Built for every kind of <span class="mv-em">music room</span>
                    </h2>
                    <p class="mv-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Eighty capacity or two thousand, the show day is the same shape.
                    </p>
                </div>

                @php
                    $mvRooms = [
                        ['Concert Halls', 'Seated performances, classical programmes and acoustic shows. Publish the interval and the running time, and sell a season pass.', 'for-concert-halls'],
                        ['Live Music Bars & Clubs', 'Standing-room venues with regular programming. Build a local following for weekly shows.', 'for-small-music-clubs'],
                        ['Jazz Clubs', 'Intimate sets, residencies and guest headliners. Two sets a night, each with its own start time on the page.', 'for-mid-size-music-venues'],
                        ['Folk & Acoustic Venues', 'Singer-songwriter nights, open mics and listening rooms. Create a space for acoustic performances.', 'for-house-concerts'],
                        ['Rock & Indie Venues', 'Touring bands, local acts and multi-band bills. Every act on the bill gets its own set time and its own photos.', 'for-multi-purpose-venues'],
                        ['Outdoor Amphitheaters', 'Seasonal programming and festival-style bills. Set the season up once as recurring dates and skip the weeks you are closed.', 'for-outdoor-amphitheaters'],
                    ];
                @endphp
                <div class="mv-minis" data-reveal-group="80">
                    @foreach ($mvRooms as [$mvRoomName, $mvRoomDesc, $mvRoomSlug])
                        @php $mvPost = get_sub_audience_blog($mvRoomSlug); @endphp
                        <article class="mv-mini" data-reveal>
                            <h3 class="mv-mini-sign">
                                <span class="sr-only">{{ $mvRoomName }}</span>
                                <span class="mv-mini-board" aria-hidden="true"><span class="mv-tiles"><span class="mv-line">{!! $mvTiles($mvRoomName, ['plain' => true]) !!}</span></span></span>
                            </h3>
                            <p>{{ $mvRoomDesc }}</p>
                            @if ($mvPost)
                                <a href="{{ blog_url('/' . $mvPost->slug) }}" class="mv-more" aria-label="Learn more about Event Schedule for {{ $mvRoomName }}">
                                    Learn more {!! $mvArrow !!}
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. Three steps                                              -->
        <!-- ============================================================ -->
        <section class="mv-section">
            <div class="mv-wrap">
                <div class="mv-head">
                    <span class="mv-kick" data-reveal>From load-in to doors</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three steps
                    </h2>
                </div>

                <div class="mv-steps" data-reveal-group="130">
                    @foreach ([['01', 'Add the show', 'Create the event once, and use sub-schedules to keep the main room and the back room apart on the same link.'], ['02', 'Add the running order', 'Give the show its parts. Each act gets a name and a start time, and they publish in order on the event page.'], ['03', 'Open the door', 'Ticket types with their own sales windows, then scan QR codes on the night, free on every plan, and on Pro watch the dashboard against your capacity.']] as [$stepNum, $stepTitle, $stepBody])
                        <div class="mv-step" data-reveal>
                            <div class="mv-step-no">
                                <span class="sr-only">{{ $stepNum }}</span>
                                <span class="mv-mini-board" aria-hidden="true"><span class="mv-tiles"><span class="mv-line">{!! $mvTiles($stepNum, ['plain' => true, 'red' => [0]]) !!}</span></span></span>
                            </div>
                            <h3>{{ $stepTitle }}</h3>
                            <p>{{ $stepBody }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. Key features: also showing                               -->
        <!-- ============================================================ -->
        <section class="mv-section mv-alt">
            <div class="mv-wrap mv-also-grid">
                <div>
                    <span class="mv-kick" data-reveal>Also showing</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">Key features</h2>
                    <div style="margin-top: 1.75rem; --reveal-delay: 0.16s;" data-reveal>
                        <a href="{{ marketing_url('/features') }}" class="mv-more">
                            See all features
                            {!! $mvArrow !!}
                        </a>
                    </div>
                </div>

                @php
                    $mvAlso = [
                        ['Ticketing', 'Ticket types, QR check-in, and zero platform fees', marketing_url('/features/ticketing')],
                        ['Sub-schedules', 'Keep each room\'s listings apart on one link', marketing_url('/features/sub-schedules')],
                        ['Recurring Events', 'Set a residency once, and skip the weeks you are dark', marketing_url('/features/recurring-events')],
                        ['Newsletters', 'Email the people who follow your venue, with open rates', marketing_url('/features/newsletters')],
                    ];
                @endphp
                <div class="mv-also" data-reveal-group="70">
                    @foreach ($mvAlso as [$mvAlsoName, $mvAlsoDesc, $mvAlsoUrl])
                        <a href="{{ $mvAlsoUrl }}" data-reveal>
                            <span>
                                <strong>{{ $mvAlsoName }}</strong>
                                <small>{{ $mvAlsoDesc }}</small>
                            </span>
                            {!! $mvArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="mv-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 12. Related pages: down the street                           -->
        <!-- ============================================================ -->
        <section class="mv-section">
            <div class="mv-wrap">
                <div class="mv-street-head">
                    <div>
                        <span class="mv-kick" data-reveal>Down the street</span>
                        <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="mv-more" data-reveal>
                        See all use cases
                        {!! $mvArrow !!}
                    </a>
                </div>

                <div class="mv-blades" data-reveal-group="80">
                    @foreach ([['/for-venues', 'Venues'], ['/for-nightclubs', 'Nightclubs'], ['/for-bars', 'Bars'], ['/for-musicians', 'Musicians']] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="mv-blade" data-reveal>
                            <strong>For {{ $relName }}</strong>
                            <span>
                                Read more
                                {!! $mvArrow !!}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 13. FAQ: questions at the window                             -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="mv-section mv-alt" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap mv-faq-grid">
                <div class="mv-faq-head">
                    <span class="mv-kick" data-reveal>At the window</span>
                    <h2 class="mv-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="mv-sub" data-reveal style="--reveal-delay: 0.16s;">
                        What venue bookers ask before they move a calendar.
                    </p>
                </div>

                <div class="mv-qa" data-reveal>
                    @foreach ($faqs as $faqIndex => $faq)
                        <details name="faq">
                            <summary>
                                <span class="mv-q-no" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
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
        <!-- 14. Finale: your name on the sign                            -->
        <!-- ============================================================ -->
        <section id="claim" class="mv-night mv-finale" style="scroll-margin-top: 4rem;">
            <div class="mv-wrap">
                <h2 class="mv-h2" data-reveal>
                    Load-in is at two. <span class="mv-em">The page can be ready by one.</span>
                </h2>
                <p class="mv-sub" data-reveal style="--reveal-delay: 0.08s;">
                    Put the whole day on one link, sell the door with no platform fee, and stop answering the same question fourteen times before soundcheck.
                </p>

                <div class="mv-sign mv-sign-lit mv-sign-small" data-reveal="panel">
                    <div class="mv-bulbs mv-bulbs-t" aria-hidden="true">@for ($mvB = 0; $mvB < 24; $mvB++)<i></i>@endfor</div>
                    <div class="mv-bulbs mv-bulbs-r" aria-hidden="true">@for ($mvB = 0; $mvB < 6; $mvB++)<i></i>@endfor</div>
                    <div class="mv-bulbs mv-bulbs-b" aria-hidden="true">@for ($mvB = 0; $mvB < 24; $mvB++)<i></i>@endfor</div>
                    <div class="mv-bulbs mv-bulbs-l" aria-hidden="true">@for ($mvB = 0; $mvB < 6; $mvB++)<i></i>@endfor</div>
                    <div class="mv-board">
                        <p class="mv-strip">Free forever</p>
                        <div class="mv-tiles mv-claim-tiles" aria-hidden="true">
                            <span class="mv-line" id="mv-claim-tiles" data-default="your-venue">{!! $mvTiles('your-venue', ['plain' => true]) !!}</span>
                        </div>
                        <div class="mv-claim-suffix" aria-hidden="true">.eventschedule.com</div>
                    </div>
                </div>

                <div class="mv-claim-row" data-reveal>
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div dir="ltr" class="es-claim mv-claim">
                        <input id="es-claim-input" type="text" placeholder="your-venue" autocomplete="off" spellcheck="false" maxlength="30">
                        <span>.eventschedule.com</span>
                    </div>
                    <a href="{{ app_url('/sign_up?type=venue') }}" class="mv-btn">
                        Create your calendar
                        {!! $mvArrow !!}
                    </a>
                </div>

                <p class="mv-fine">No credit card required</p>
            </div>
        </section>

        <div class="mv-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    {{-- The finale sign spells whatever is typed into the claim box, a tile a letter. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('es-claim-input');
            var line = document.getElementById('mv-claim-tiles');
            if (!input || !line) {
                return;
            }
            var fallback = line.getAttribute('data-default') || '';
            var draw = function () {
                var text = (input.value || fallback).toUpperCase().slice(0, 30);
                var word = document.createElement('span');
                word.className = 'mv-word';
                text.split('').forEach(function (letter) {
                    var tile = document.createElement('i');
                    tile.className = 'mv-t';
                    tile.setAttribute('data-l', letter);
                    word.appendChild(tile);
                });
                line.textContent = '';
                line.appendChild(word);
                line.parentElement.style.setProperty('--n', Math.max(text.length, 8));
            };
            input.addEventListener('input', function () {
                window.requestAnimationFrame(draw);
            });
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
