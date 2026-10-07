<x-marketing-layout>
    <x-slot name="title">Band Tour Dates Page, Free | For Musicians and Solo Artists</x-slot>
    <x-slot name="description">Put every gig and tour date on one link. Sell tickets with zero platform fees, email fans directly, and let venues add you to their bills. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Musicians</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Anton') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Archivo') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lekton') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Musicians"
        description="Put every gig and tour date on one link. Sell tickets with zero platform fees, email fans directly, and let venues add you to their bills. Free forever."
        audience="Musicians"
        keywords="musician schedule, band tour dates, share gig schedule, musician event calendar, band booking platform, free musician scheduling, band website with tour dates, residency schedule" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How musicians share their gig schedule with Event Schedule",
        "description": "Three steps from your first listed gig to a growing fanbase.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your gigs",
                "text": "Import from Google Calendar or add tour dates manually. Set up ticket sales if you want."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share your link",
                "text": "Add it to your Spotify bio, Bandcamp, EPK, or anywhere fans find you."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Grow your fanbase",
                "text": "Fans leave an email address, hear when you announce new dates, and share videos and comments after your gigs, all approved by you before they go live."
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
           For-musicians "Side A" styles. The page is a long-player: a
           sleeve with a record half out of it, tour dates as the track
           list, features as the six tracks of two sides, a career as a
           discography, and a test pressing to sign at the end.

           Everything is scoped under #mu and drawn with the page's own
           tokens, so nothing here leaks into the shared chrome. The
           shared es-* reveal system (marketing.css, marketing-home.js)
           still drives the entrances.
           ============================================================== */

        #mu {
            --mu-paper: #f2ead9;
            --mu-paper-2: #e8ddc6;
            --mu-card: #fbf6ea;
            --mu-ink: #15120f;
            --mu-ink-2: #473f35;
            --mu-ink-3: #675d50;
            --mu-line: rgba(21, 18, 15, 0.18);
            --mu-hot: #e8472c;
            --mu-hot-ink: #b02a11;
            --mu-gold: #e9b23a;
            --mu-teal: #0e5a6b;
            --mu-on-hot: #15120f;
            --mu-display: 'Anton', 'Impact', 'Haettenschweiler', 'Arial Narrow Bold', 'Arial Narrow', sans-serif;
            --mu-text: 'Archivo', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --mu-mono: 'Lekton', ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
            --mu-grain: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.55 0'/%3E%3C/filter%3E%3Crect width='220' height='220' filter='url(%23n)'/%3E%3C/svg%3E");
            position: relative;
            background: var(--mu-paper);
            color: var(--mu-ink);
            font-family: var(--mu-text);
            font-size: 1.0625rem;
            line-height: 1.55;
        }
        .dark #mu {
            --mu-paper: #100f0d;
            --mu-paper-2: #181613;
            --mu-card: #1e1b17;
            --mu-ink: #f2ead9;
            --mu-ink-2: #cdc3b0;
            --mu-ink-3: #a1968a;
            --mu-line: rgba(242, 234, 217, 0.2);
            --mu-hot: #ff5433;
            --mu-hot-ink: #ff8166;
            --mu-gold: #f0bd4a;
            --mu-teal: #58bfd1;
        }

        /* The bar above takes the sleeve's stock, so the page reads as one object. */
        body > header.sticky {
            background-color: rgba(242, 234, 217, 0.86);
            border-bottom-color: rgba(21, 18, 15, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(16, 15, 13, 0.86);
            border-bottom-color: rgba(242, 234, 217, 0.14);
        }

        #mu ::selection { background: var(--mu-hot); color: #fff; }
        #mu a:focus-visible,
        #mu summary:focus-visible,
        #mu input:focus-visible {
            outline: 3px solid var(--mu-hot);
            outline-offset: 3px;
        }

        .mu-wrap { width: min(100% - 2.5rem, 78rem); margin-inline: auto; }

        /* Type voices: the sleeve's condensed caps, and the typed catalogue line. */
        .mu-d {
            font-family: var(--mu-display);
            font-weight: 400;
            text-transform: uppercase;
            letter-spacing: 0.005em;
            line-height: 0.92;
        }
        .mu-m {
            font-family: var(--mu-mono);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .mu-h2 { font-size: clamp(2.6rem, 6.4vw, 5.25rem); text-wrap: balance; }
        .mu-sub { max-width: 38rem; color: var(--mu-ink-2); font-size: 1.125rem; }
        .mu-tag {
            display: inline-block;
            padding: 0.3rem 0.6rem 0.2rem;
            border: 2px solid currentColor;
            color: var(--mu-hot-ink);
        }
        .mu-section { padding-block: clamp(4.5rem, 9vw, 8rem); }
        .mu-alt { background: var(--mu-paper-2); }

        /* Buttons: a pressed-card feel, the shadow is the second ink. */
        .mu-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 1rem 1.6rem 0.9rem;
            border: 2px solid var(--mu-ink);
            background: var(--mu-ink);
            color: var(--mu-paper);
            font-family: var(--mu-display);
            font-size: 1.25rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            line-height: 1;
            box-shadow: 6px 6px 0 var(--mu-hot);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .mu-btn:hover { transform: translate(3px, 3px); box-shadow: 3px 3px 0 var(--mu-hot); }
        .mu-btn svg { width: 1.2rem; height: 1.2rem; transition: transform 0.18s ease; }
        .mu-btn:hover svg { transform: translateX(4px); }
        .mu-btn-ghost {
            background: transparent;
            color: var(--mu-ink);
            box-shadow: none;
        }
        .mu-btn-ghost:hover { background: var(--mu-ink); color: var(--mu-paper); transform: none; box-shadow: none; }
        .mu-btn-ghost:hover svg { transform: translateY(4px); }
        .mu-more-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--mu-ink);
            border-bottom: 2px solid var(--mu-hot);
            /* The top padding, taken back by the margin, makes the link a 24px target without moving it. */
            padding-block: 0.25rem 0.15rem;
            margin-top: -0.25rem;
            transition: gap 0.2s ease, color 0.2s ease;
        }
        .mu-more-link:hover { gap: 0.9rem; color: var(--mu-hot-ink); }
        .mu-more-link svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           Track index: the section rail, on wide screens only
           --------------------------------------------------------------- */
        /* From 1600px, where the margin beside the 78rem column is wider than the longest label.
           The nav is a box the size of the page that clips the rail, so the rail stays fixed to
           the screen and still ends where the page does instead of riding over the site footer. */
        .mu-index { display: none; }
        @media (min-width: 1600px) {
            .mu-index {
                display: block;
                position: absolute;
                inset: 0;
                z-index: 40;
                clip-path: inset(0);
                pointer-events: none;
            }
            .mu-index ol {
                position: fixed;
                left: 1.4rem;
                top: 50%;
                translate: 0 -50%;
                display: grid;
                gap: 0.15rem;
                pointer-events: auto;
            }
            /* The rail is printed for the paper. The full-bleed ink and red bands pass over it. */
            .mu-ticker, .mu-flip, .mu-press { z-index: 41; }
            .mu-index a {
                position: relative;
                display: flex;
                align-items: center;
                gap: 0.6rem;
                padding: 0.3rem 0;
                color: var(--mu-ink-3);
            }
            .mu-index a::before {
                content: "";
                width: 0.9rem;
                height: 2px;
                background: currentColor;
                transition: width 0.3s cubic-bezier(0.22, 1, 0.36, 1), background-color 0.3s ease;
            }
            .mu-index a span { opacity: 0; translate: -0.3rem 0; transition: opacity 0.25s ease, translate 0.25s ease; }
            .mu-index a:hover span,
            .mu-index a:focus-visible span,
            .mu-index a.is-active span { opacity: 1; translate: 0 0; }
            .mu-index a.is-active { color: var(--mu-hot-ink); }
            .mu-index a.is-active::before { width: 2.2rem; background: var(--mu-hot); }
        }

        /* ---------------------------------------------------------------
           Hero: the sleeve, with the record half out of it
           --------------------------------------------------------------- */
        .mu-hero { position: relative; overflow: clip; }
        .mu-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: var(--mu-grain);
            opacity: 0.16;
            mix-blend-mode: multiply;
            pointer-events: none;
        }
        .dark .mu-hero::before { mix-blend-mode: screen; opacity: 0.07; }
        .mu-hero-grid {
            position: relative;
            display: grid;
            gap: 3rem;
            align-items: center;
            padding-block: clamp(2.5rem, 6vw, 5rem) clamp(3rem, 6vw, 5rem);
        }
        @media (min-width: 1024px) {
            .mu-hero-grid {
                grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr);
                gap: 2rem;
                min-height: calc(92svh - 4rem - 13rem);
            }
        }
        /* The spine needs a margin of its own: from 1360px the column clears it, and from 1600px
           the section rail takes that margin instead. */
        .mu-spine { display: none; }
        @media (min-width: 1360px) and (max-width: 1599.98px) {
            .mu-spine {
                display: block;
                position: absolute;
                left: 1.4rem;
                top: 2.5rem;
                writing-mode: vertical-rl;
                rotate: 180deg;
                color: var(--mu-ink-3);
                white-space: nowrap;
            }
        }
        .mu-copy { container-type: inline-size; min-width: 0; }
        .mu-eyebrow {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.4rem;
            font-family: var(--mu-mono);
            font-weight: 700;
            font-size: 0.8125rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            line-height: 1.3;
            color: var(--mu-ink-2);
        }
        .mu-eyebrow::before {
            content: "ES-001";
            padding: 0.25rem 0.5rem 0.15rem;
            background: var(--mu-ink);
            color: var(--mu-paper);
        }
        /* Anton's capitals are 0.875em tall, so anything tighter than this sets one line on the next (Firefox and Safari show it first). */
        .mu-h1 { font-size: clamp(3.1rem, 16cqi, 9.6rem); line-height: 0.9; }
        .mu-h1 .es-mask-line { white-space: nowrap; }
        html.es-anim #mu .mu-mask-3 .es-mask-line { animation-delay: 0.42s; }
        /* The third line is the second plate, printed a hair off register. */
        .mu-hot { color: var(--mu-hot); text-shadow: 0.038em 0.034em 0 rgba(14, 90, 107, 0.5); }
        .dark .mu-hot { text-shadow: 0.038em 0.034em 0 rgba(240, 189, 74, 0.42); }
        .mu-lede { max-width: 33rem; margin-top: 1.75rem; font-size: clamp(1.1rem, 1.6vw, 1.3rem); color: var(--mu-ink-2); }
        .mu-cta { display: flex; flex-wrap: wrap; gap: 1.1rem 1.25rem; margin-top: 2rem; }

        /* Hype stickers, the kind stuck to the shrink wrap. */
        .mu-stickers { display: flex; flex-wrap: wrap; align-items: center; gap: 0.9rem; margin-top: 2.25rem; }
        .mu-sticker {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.9rem 0.4rem;
            font-family: var(--mu-display);
            font-size: 0.95rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            line-height: 1;
            rotate: var(--r, 0deg);
            transition: rotate 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), scale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .mu-sticker:hover { rotate: 0deg; scale: 1.06; }
        .mu-sticker-hot { background: var(--mu-hot); color: var(--mu-on-hot); border-radius: 999px; padding-inline: 1.1rem; }
        .mu-sticker-line { border: 2px solid var(--mu-ink); color: var(--mu-ink); }
        .mu-sticker-ink { background: var(--mu-ink); color: var(--mu-paper); }

        /* The art: a square sleeve, and behind it the record. */
        .mu-art {
            position: relative;
            width: min(100%, 33rem);
            aspect-ratio: 1;
            margin-inline: 0 auto;
        }
        @media (min-width: 1024px) { .mu-art { margin-inline: 0; } }
        .mu-slide { position: absolute; inset: 3%; left: 52%; width: 94%; }
        .mu-vinyl {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background:
                conic-gradient(from 20deg,
                    rgba(255, 255, 255, 0) 0deg, rgba(255, 255, 255, 0.2) 22deg, rgba(255, 255, 255, 0) 46deg,
                    rgba(255, 255, 255, 0) 180deg, rgba(255, 255, 255, 0.16) 202deg, rgba(255, 255, 255, 0) 226deg,
                    rgba(255, 255, 255, 0) 360deg),
                repeating-radial-gradient(circle, #1b1a18 0 1px, #070706 1px 3px);
            box-shadow: 0 0 0 1px #000, 0 1.6rem 3rem -1rem rgba(0, 0, 0, 0.55);
        }
        .dark .mu-vinyl { box-shadow: 0 0 0 1px rgba(242, 234, 217, 0.22), 0 1.6rem 3rem -1rem #000; }
        /* The dead wax: a smooth band between the last groove and the label. */
        .mu-vinyl::before {
            content: "";
            position: absolute;
            inset: 29%;
            border-radius: 50%;
            background: #0c0b0a;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.06);
        }
        .mu-label {
            position: absolute;
            inset: 32%;
            border-radius: 50%;
            background: var(--mu-gold);
            color: #15120f;
            display: grid;
            place-items: center;
            animation: mu-spin 9s linear infinite;
        }
        .mu-label::after {
            content: "";
            position: absolute;
            width: 9%;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--mu-paper);
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.35);
        }
        .mu-label svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .mu-label text { font-family: var(--mu-mono); font-weight: 700; font-size: 13.5px; letter-spacing: 0.14em; text-transform: uppercase; fill: #15120f; }
        .mu-label b { font-family: var(--mu-display); font-weight: 400; font-size: clamp(0.8rem, 2.6cqi, 1.3rem); translate: 0 -135%; letter-spacing: 0.06em; }
        @keyframes mu-spin { to { rotate: 360deg; } }
        html.es-anim #mu .mu-vinyl { animation: mu-eject 1.5s cubic-bezier(0.22, 1, 0.36, 1) 0.55s both; }
        @keyframes mu-eject { from { translate: -50% 0; } to { translate: 0 0; } }
        .mu-art:hover .mu-slide { translate: 7% 0; }
        .mu-slide { transition: translate 0.7s cubic-bezier(0.22, 1, 0.36, 1); }

        .mu-sleeve {
            position: absolute;
            inset: 0;
            container-type: inline-size;
            overflow: hidden;
            background: var(--mu-hot);
            box-shadow:
                inset -0.5rem 0 0.6rem -0.5rem rgba(0, 0, 0, 0.45),
                0 0 0 1px rgba(0, 0, 0, 0.25),
                0 2rem 3.5rem -1.5rem rgba(21, 18, 15, 0.6);
        }
        /* Rings off a low sun, then the grain, then the ring wear every loved record has. */
        .mu-sleeve::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 76% 104%, #15120f 0 17%, transparent 17.2%),
                repeating-radial-gradient(circle at 76% 104%, transparent 0 5.2%, rgba(21, 18, 15, 0.92) 5.2% 6.6%);
            -webkit-mask-image: linear-gradient(to bottom, black 0 66%, transparent 66%);
            mask-image: linear-gradient(to bottom, black 0 66%, transparent 66%);
        }
        .mu-sleeve::after {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 50% 50%, transparent 43%, rgba(255, 255, 255, 0.13) 44.5%, transparent 47%),
                var(--mu-grain);
            mix-blend-mode: soft-light;
            pointer-events: none;
        }
        .mu-sleeve-top {
            position: absolute;
            inset: 5% 6% auto 6%;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 3.3cqi;
            color: #15120f;
            z-index: 1;
        }
        .mu-sleeve-top span { padding: 0.3em 0.6em 0.15em; background: var(--mu-hot); }
        .mu-sleeve-top span:last-child { border: 0.14em solid currentColor; }
        .mu-sleeve-title {
            position: absolute;
            inset: 66% 0 0 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1.6cqi;
            padding-inline: 6%;
            background: #f2ead9;
            color: #15120f;
            z-index: 1;
        }
        .mu-sleeve-title small { font-size: 2.9cqi; color: #473f35; }
        .mu-sleeve-title strong { font-size: 11.7cqi; font-weight: 400; white-space: nowrap; }

        /* The track list: three dates set like the back of the sleeve. */
        .mu-dates { position: relative; border-top: 2px solid var(--mu-ink); display: grid; }
        @media (min-width: 900px) { .mu-dates { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mu-date {
            display: grid;
            grid-template-columns: auto auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.9rem;
            padding: 1.1rem 1.25rem 1rem;
            border-bottom: 1px solid var(--mu-line);
        }
        @media (min-width: 900px) {
            .mu-date { border-bottom: 0; }
            .mu-date + .mu-date { border-inline-start: 1px solid var(--mu-line); }
        }
        /* On the narrowest phones the venue column was narrower than "Crocodile", which ran up to the Sold out chip. */
        @media (max-width: 380px) { .mu-date { gap: 0.6rem; } }
        .mu-date-no { color: var(--mu-ink-3); }
        .mu-date-day { font-size: 1.6rem; color: var(--mu-hot); }
        .mu-date-where { min-width: 0; font-weight: 700; line-height: 1.2; }
        .mu-date-where small { display: block; font-weight: 400; font-size: 0.875rem; color: var(--mu-ink-3); }
        .mu-date-chip { padding: 0.3rem 0.5rem 0.2rem; border: 2px solid var(--mu-ink); font-size: 0.72rem; }
        .mu-date-chip-out { background: var(--mu-ink); color: var(--mu-paper); rotate: -3deg; }

        /* The ticker: every bin in the shop, sliding past. */
        .mu-ticker {
            position: relative;
            border-block: 2px solid var(--mu-ink);
            background: var(--mu-ink);
            color: var(--mu-paper);
            padding-block: 0.85rem 0.6rem;
        }
        .mu-ticker .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .mu-genre {
            display: inline-flex;
            align-items: center;
            gap: 1.6rem;
            padding-inline-end: 1.6rem;
            font-size: clamp(1.7rem, 3.6vw, 2.9rem);
            white-space: nowrap;
        }
        .mu-genre::after {
            content: "";
            width: 0.42em;
            aspect-ratio: 1;
            border-radius: 50%;
            background: var(--mu-hot);
        }
        .mu-genre:nth-child(even) {
            color: transparent;
            -webkit-text-stroke: 1.5px var(--mu-paper);
        }
        @media (prefers-reduced-motion: reduce) {
            .mu-ticker .es-marquee-track { row-gap: 0.4rem; }
        }

        /* ---------------------------------------------------------------
           The problem: the inner sleeve, black in both modes
           --------------------------------------------------------------- */
        .mu-flip {
            position: relative;
            overflow: clip;
            background-color: #0d0c0b;
            background-image: repeating-radial-gradient(circle at 50% 135%, transparent 0 26px, rgba(242, 234, 217, 0.05) 26px 27px);
            color: #f2ead9;
        }
        .mu-flip-head { text-align: center; }
        .mu-flip .mu-tag { color: #f0bd4a; }
        .mu-flip-h2 { margin-top: 1.5rem; font-size: clamp(3rem, 10.5vw, 9.5rem); line-height: 0.88; text-wrap: balance; }
        .mu-hollow { color: transparent; -webkit-text-stroke: 2px #f2ead9; }
        .mu-flip-sub { margin: 1.6rem auto 0; max-width: 34rem; color: #cdc3b0; font-size: 1.2rem; }
        .mu-stats { display: grid; margin-top: clamp(3rem, 6vw, 5rem); border-block: 2px solid #f2ead9; }
        @media (min-width: 820px) { .mu-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mu-stat { padding: 2.25rem 1.5rem 2rem; text-align: center; }
        .mu-stat + .mu-stat { border-top: 1px solid rgba(242, 234, 217, 0.22); }
        @media (min-width: 820px) {
            .mu-stat + .mu-stat { border-top: 0; border-inline-start: 1px solid rgba(242, 234, 217, 0.22); }
        }
        .mu-stat-big { font-size: clamp(3rem, 6.4vw, 5.4rem); color: var(--c); }
        .mu-stat-big.es-od { justify-content: center; }
        #mu .mu-stat .es-od-strip span { background: none; -webkit-text-fill-color: currentColor; color: var(--c); }
        .mu-stat p { margin: 1rem auto 0; max-width: 17rem; color: #cdc3b0; }
        .mu-stamp {
            display: inline-block;
            margin-top: 1.25rem;
            padding: 0.3rem 0.6rem 0.2rem;
            border: 2px solid var(--c);
            color: var(--c);
            rotate: var(--r, -2deg);
        }
        .mu-flip-foot { margin-top: 2.5rem; text-align: center; color: #cdc3b0; }
        .mu-flip-foot a {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-inline-start: 0.4rem;
            color: #f2ead9;
            font-weight: 700;
            border-bottom: 2px solid #ff5433;
            transition: gap 0.2s ease;
        }
        .mu-flip-foot a:hover { gap: 0.7rem; }
        .mu-flip-foot svg { width: 1rem; height: 1rem; }
        #mu .mu-flip a:focus-visible { outline-color: #f0bd4a; }

        /* ---------------------------------------------------------------
           Side A and Side B: the six tracks
           --------------------------------------------------------------- */
        .mu-side-head {
            display: grid;
            gap: 1.5rem;
            align-items: end;
            margin-bottom: clamp(2rem, 5vw, 4rem);
        }
        @media (min-width: 900px) { .mu-side-head { grid-template-columns: minmax(0, 1fr) auto; } }
        .mu-side-mark {
            font-size: clamp(4.5rem, 13vw, 11rem);
            line-height: 0.8;
            color: transparent;
            -webkit-text-stroke: 2px var(--mu-ink);
            white-space: nowrap;
        }
        .mu-side-mark b { font-weight: 400; color: var(--mu-hot); -webkit-text-stroke: 0; }
        .mu-track {
            display: grid;
            gap: 1.75rem 2.5rem;
            padding-block: clamp(2.25rem, 5vw, 4rem);
            border-top: 2px solid var(--mu-ink);
        }
        @media (min-width: 900px) {
            .mu-track { grid-template-columns: 6.5rem minmax(0, 1fr) minmax(0, 0.92fr); align-items: start; }
            .mu-track:nth-of-type(even) .mu-prop { order: -1; }
            .mu-track:nth-of-type(even) { grid-template-columns: minmax(0, 0.92fr) 6.5rem minmax(0, 1fr); }
        }
        .mu-track-no {
            font-size: clamp(3.4rem, 6vw, 4.8rem);
            line-height: 0.8;
            color: var(--mu-hot);
            -webkit-text-stroke: 2px var(--mu-hot);
        }
        .mu-track-kind { color: var(--mu-ink-3); }
        .mu-track h3 { margin-top: 0.7rem; font-size: clamp(2.1rem, 3.9vw, 3.3rem); text-wrap: balance; }
        .mu-track p { margin-top: 1rem; max-width: 34rem; color: var(--mu-ink-2); }
        .mu-track p a, .mu-notes p a { color: var(--mu-ink); font-weight: 700; text-decoration: underline; text-decoration-color: var(--mu-hot); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        .mu-track p a:hover { color: var(--mu-hot-ink); }
        .mu-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1.4rem; }
        .mu-chip { padding: 0.35rem 0.6rem 0.25rem; border: 1.5px solid var(--mu-line); color: var(--mu-ink-2); font-size: 0.72rem; }
        @supports (animation-timeline: view()) {
            html.es-anim #mu .mu-track-no {
                animation: mu-ink linear both;
                animation-timeline: view();
                animation-range: entry 10% cover 34%;
            }
        }
        @keyframes mu-ink {
            from { color: transparent; -webkit-text-stroke-color: var(--mu-ink); }
            to { color: var(--mu-hot); -webkit-text-stroke-color: var(--mu-hot); }
        }
        .mu-turn {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            padding-block: 1.4rem 1.2rem;
            border-top: 2px solid var(--mu-ink);
            color: var(--mu-ink-3);
        }
        .mu-turn::after { content: ""; flex: 1; height: 2px; background: repeating-linear-gradient(90deg, var(--mu-ink) 0 10px, transparent 10px 18px); opacity: 0.5; }
        .mu-turn i {
            flex: none;
            width: 2.6rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, var(--mu-paper-2) 0 9%, var(--mu-hot) 9.5% 34%, #0d0c0b 35%);
            animation: mu-spin 3.2s linear infinite;
        }

        /* Props: the things on the merch table, one per track. */
        .mu-prop { position: relative; min-width: 0; }
        .mu-cardboard {
            position: relative;
            background: var(--mu-card);
            border: 2px solid var(--mu-ink);
            box-shadow: 8px 8px 0 var(--mu-ink);
        }
        .mu-prop-stamp {
            position: absolute;
            z-index: 2;
            padding: 0.4rem 0.7rem 0.3rem;
            border: 2.5px solid var(--mu-hot-ink);
            color: var(--mu-hot-ink);
            background: var(--mu-card);
            rotate: var(--r, -7deg);
        }

        /* A1: the mailer */
        .mu-mail { max-width: 27rem; margin-inline: auto; rotate: 1.2deg; }
        .mu-mail-from { display: flex; align-items: center; gap: 0.8rem; padding: 1rem 1.1rem; border-bottom: 2px solid var(--mu-ink); }
        .mu-mail-av { flex: none; display: grid; place-items: center; width: 2.6rem; aspect-ratio: 1; border-radius: 50%; background: var(--mu-ink); color: var(--mu-paper); font-family: var(--mu-display); font-size: 1rem; }
        .mu-mail-from strong { display: block; line-height: 1.2; }
        .mu-mail-from small { display: block; color: var(--mu-ink-3); font-size: 0.875rem; }
        .mu-mail-body { padding: 1.6rem 1.25rem 1.4rem; background: var(--mu-hot); color: var(--mu-on-hot); }
        .mu-mail-body .mu-d { margin-block: 0.5rem 0.45rem; font-size: clamp(2rem, 4.4vw, 2.9rem); }
        .mu-mail-body p { margin: 0; color: inherit; font-weight: 700; }
        .mu-mail-stats { display: grid; grid-template-columns: 1fr 1fr; }
        .mu-mail-stats div { padding: 0.9rem 1.1rem 0.8rem; }
        .mu-mail-stats div + div { border-inline-start: 2px solid var(--mu-ink); }
        .mu-mail-stats .mu-d { display: block; font-size: 2.3rem; }
        .mu-mail-stats small { color: var(--mu-ink-3); }
        .mu-mail .mu-prop-stamp { right: -0.9rem; top: 3.1rem; --r: 9deg; }

        /* A2: the ticket, torn at the perforation */
        .mu-stub { display: grid; grid-template-columns: minmax(0, 1fr) 7.6rem; max-width: 28rem; margin-inline: auto; rotate: -1.6deg; filter: drop-shadow(8px 8px 0 var(--mu-ink)); }
        .mu-stub-main,
        .mu-stub-tear { background: var(--mu-card); padding: 1.4rem 1.25rem 1.2rem; }
        .mu-stub-main {
            -webkit-mask: radial-gradient(circle at 100% 0, transparent 0.6rem, black 0.63rem) top right / 100% 51% no-repeat, radial-gradient(circle at 100% 100%, transparent 0.6rem, black 0.63rem) bottom right / 100% 51% no-repeat;
            mask: radial-gradient(circle at 100% 0, transparent 0.6rem, black 0.63rem) top right / 100% 51% no-repeat, radial-gradient(circle at 100% 100%, transparent 0.6rem, black 0.63rem) bottom right / 100% 51% no-repeat;
        }
        .mu-stub-tear {
            display: grid;
            align-content: center;
            justify-items: center;
            text-align: center;
            border-inline-start: 2px dashed var(--mu-ink-3);
            -webkit-mask: radial-gradient(circle at 0 0, transparent 0.6rem, black 0.63rem) top left / 100% 51% no-repeat, radial-gradient(circle at 0 100%, transparent 0.6rem, black 0.63rem) bottom left / 100% 51% no-repeat;
            mask: radial-gradient(circle at 0 0, transparent 0.6rem, black 0.63rem) top left / 100% 51% no-repeat, radial-gradient(circle at 0 100%, transparent 0.6rem, black 0.63rem) bottom left / 100% 51% no-repeat;
        }
        .mu-stub-row { display: flex; justify-content: space-between; gap: 1rem; padding-block: 0.5rem; font-family: var(--mu-mono); font-weight: 700; font-size: 0.875rem; letter-spacing: 0.08em; border-bottom: 1px dashed var(--mu-line); }
        .mu-stub-keep { margin-top: 1rem; }
        .mu-stub-keep .mu-d { display: block; font-size: 4.2rem; color: var(--mu-hot); }
        .mu-stub-keep small { color: var(--mu-ink-3); }
        .mu-stub-tear .mu-d { font-size: 1.5rem; writing-mode: vertical-rl; rotate: 180deg; color: var(--mu-hot-ink); }

        /* A3: one address, four places it goes */
        .mu-link { max-width: 27rem; margin-inline: auto; }
        .mu-link-bar { display: flex; align-items: center; gap: 0.7rem; padding: 1rem 1.1rem 0.9rem; font-family: var(--mu-mono); font-weight: 700; font-size: clamp(0.85rem, 2.6vw, 1.05rem); }
        .mu-link-bar i { flex: none; width: 0.7rem; aspect-ratio: 1; border-radius: 50%; background: var(--mu-hot); }
        .mu-link-bar span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .mu-link-fan { display: grid; grid-template-columns: repeat(4, 1fr); height: 2.1rem; }
        .mu-link-fan i { border-inline-start: 2px solid var(--mu-ink); margin-inline-start: calc(50% - 1px); }
        .mu-link-to { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
        .mu-link-to span {
            padding: 0.7rem 0.2rem 0.55rem;
            text-align: center;
            border: 2px solid var(--mu-ink);
            background: var(--mu-card);
            font-size: clamp(0.62rem, 1.9vw, 0.76rem);
            letter-spacing: 0.08em;
            rotate: var(--r, 0deg);
        }
        .mu-link-to span:nth-child(odd) { background: var(--mu-ink); color: var(--mu-paper); }

        /* B1: two bills, one booking */
        .mu-bills { display: grid; grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr); align-items: center; gap: 0.75rem; max-width: 29rem; margin-inline: auto; }
        .mu-bill { padding: 1rem 0.9rem 1.1rem; }
        .mu-bill:first-child { rotate: -2deg; }
        .mu-bill:last-child { rotate: 2deg; background: var(--mu-gold); color: #15120f; }
        .mu-bill .mu-m { display: block; margin-bottom: 0.9rem; font-size: 0.68rem; }
        .mu-bill i { display: block; height: 0.55rem; margin-bottom: 0.5rem; background: currentColor; opacity: 0.22; }
        .mu-bill i:nth-of-type(2) { width: 72%; }
        .mu-bill i:nth-of-type(3) { width: 86%; }
        .mu-bill b { display: block; margin-top: 0.8rem; padding: 0.45rem 0.5rem 0.3rem; background: var(--mu-hot); color: var(--mu-on-hot); font-family: var(--mu-display); font-weight: 400; font-size: 1.05rem; text-transform: uppercase; letter-spacing: 0.04em; text-align: center; }
        .mu-bill:last-child b { background: #15120f; color: #f2ead9; }
        .mu-bills > svg { width: 2.2rem; height: 2.2rem; color: var(--mu-hot); }

        /* B2: the road book */
        .mu-road { max-width: 26rem; margin-inline: auto; rotate: 1deg; }
        .mu-road-row { display: grid; grid-template-columns: 4.2rem minmax(0, 1fr); align-items: center; border-bottom: 2px solid var(--mu-ink); }
        .mu-road-row .mu-d { padding: 0.95rem 0.75rem 0.8rem; font-size: 1.7rem; background: var(--mu-ink); color: var(--mu-paper); text-align: center; }
        .mu-road-row:nth-child(1) .mu-d { background: var(--mu-hot); color: var(--mu-on-hot); }
        .mu-road-row span:last-child { padding-inline: 1rem; font-weight: 700; }
        .mu-road-foot { padding: 0.8rem 1rem 0.7rem; color: var(--mu-ink-3); font-size: 0.72rem; }

        /* B3: the street team */
        .mu-crew { max-width: 26rem; margin-inline: auto; padding: 1.5rem 1.25rem 1.3rem; text-align: center; rotate: -1.3deg; }
        .mu-crew-faces { display: flex; justify-content: center; }
        .mu-crew-faces span {
            display: grid;
            place-items: center;
            width: 3.2rem;
            aspect-ratio: 1;
            border-radius: 50%;
            border: 2px solid var(--mu-ink);
            background: var(--bg, var(--mu-paper-2));
            color: var(--fg, var(--mu-ink));
            font-family: var(--mu-display);
            font-size: 1.1rem;
        }
        .mu-crew-faces span + span { margin-inline-start: -0.7rem; }
        .mu-crew .mu-d { display: block; margin-top: 1rem; font-size: 3.6rem; }
        .mu-crew small { display: block; margin-top: 0.35rem; color: var(--mu-ink-3); }

        /* ---------------------------------------------------------------
           The discography: a career, one release at a time
           --------------------------------------------------------------- */
        .mu-disco-head { display: grid; gap: 1.25rem; margin-bottom: clamp(2.5rem, 5vw, 4rem); }
        .mu-crate { display: grid; gap: 2.75rem 2rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        @media (min-width: 900px) { .mu-crate { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 3.5rem 2.5rem; } }
        .mu-rel { min-width: 0; }
        .mu-cover {
            position: relative;
            aspect-ratio: 1;
            container-type: inline-size;
        }
        .mu-cover-disc {
            position: absolute;
            inset: 4%;
            border-radius: 50%;
            background:
                radial-gradient(circle, var(--mu-paper) 0 3%, var(--lab, var(--mu-hot)) 3.4% 17%, transparent 17.4%),
                repeating-radial-gradient(circle, #1b1a18 0 1px, #070706 1px 3px);
            transition: translate 0.6s cubic-bezier(0.22, 1, 0.36, 1), rotate 0.9s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .mu-rel:hover .mu-cover-disc { translate: 30% 0; rotate: 120deg; }
        .mu-cover-face {
            position: absolute;
            inset: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 6.5cqi;
            background: var(--bg);
            color: var(--fg);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.3), 0 1.2rem 2rem -1.1rem rgba(21, 18, 15, 0.7);
        }
        .dark .mu-cover-face { box-shadow: 0 0 0 1px rgba(242, 234, 217, 0.2), 0 1.2rem 2rem -1.1rem #000; }
        .mu-cover-face::before { content: ""; position: absolute; inset: 0; background: var(--art); }
        .mu-cover-face::after { content: ""; position: absolute; inset: 0; background-image: var(--mu-grain); mix-blend-mode: soft-light; pointer-events: none; }
        .mu-cover-face > * { position: relative; z-index: 1; }
        .mu-cover-meta { display: flex; justify-content: space-between; gap: 0.5rem; font-size: clamp(0.6rem, 4.2cqi, 0.8rem); }
        /* Each mark sits on a chip of the sleeve's own stock, as on the hero sleeve, so the art never runs through it. */
        .mu-cover-meta span { margin: -0.3em -0.55em -0.15em; padding: 0.3em 0.55em 0.15em; background: var(--bg); }
        .mu-cover-title { font-size: clamp(1.25rem, 13cqi, 3.4rem); text-wrap: balance; }
        .mu-rel p { margin-top: 1.1rem; color: var(--mu-ink-2); font-size: 1rem; }
        .mu-rel-flag { display: inline-block; margin-top: 0.9rem; padding: 0.3rem 0.55rem 0.2rem; background: var(--mu-hot); color: var(--mu-on-hot); rotate: -2deg; }

        /* ---------------------------------------------------------------
           Filed under: the bin dividers
           --------------------------------------------------------------- */
        .mu-bins-head { text-align: center; display: grid; justify-items: center; gap: 1.1rem; margin-bottom: clamp(3.5rem, 6vw, 5rem); }
        .mu-bins { display: grid; gap: 3.25rem 1.75rem; }
        @media (min-width: 700px) { .mu-bins { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1040px) { .mu-bins { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mu-bin {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 1.75rem 1.5rem 1.5rem;
            background: var(--mu-card);
            border: 2px solid var(--mu-ink);
            transition: translate 0.35s cubic-bezier(0.34, 1.4, 0.64, 1), box-shadow 0.35s ease;
        }
        .mu-bin:hover { translate: 0 -0.7rem; box-shadow: 0 0.7rem 0 var(--mu-hot); }
        .mu-bin-tab {
            position: absolute;
            bottom: 100%;
            left: var(--x, 8%);
            padding: 0.5rem 0.9rem 0.3rem;
            background: var(--mu-ink);
            color: var(--mu-paper);
            border: 2px solid var(--mu-ink);
            border-bottom: 0;
        }
        .mu-bin h3 { font-size: 2rem; }
        .mu-bin p { margin-top: 0.75rem; color: var(--mu-ink-2); font-size: 1rem; }
        .mu-bin a { margin-top: auto; padding-top: 1.25rem; align-self: flex-start; }
        .mu-bin a span { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 700; border-bottom: 2px solid var(--mu-hot); transition: gap 0.2s ease; }
        .mu-bin a:hover span { gap: 0.7rem; }
        .mu-bin a svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           Three steps: three labels, each a quarter turn on
           --------------------------------------------------------------- */
        .mu-steps { display: grid; gap: 3rem 2rem; margin-top: clamp(2.5rem, 5vw, 4.5rem); }
        @media (min-width: 860px) { .mu-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .mu-step { text-align: center; }
        .mu-step-disc {
            position: relative;
            width: min(15rem, 62%);
            aspect-ratio: 1;
            margin-inline: auto;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle, var(--bg) 0 43%, transparent 43.6%),
                repeating-radial-gradient(circle, #1b1a18 0 1px, #070706 1px 3px);
            color: var(--fg);
            box-shadow: 0 1.4rem 2.2rem -1.2rem rgba(21, 18, 15, 0.75);
        }
        .dark .mu-step-disc { box-shadow: 0 0 0 1px rgba(242, 234, 217, 0.22), 0 1.4rem 2.2rem -1.2rem #000; }
        .mu-step-disc svg { position: absolute; inset: 0; width: 100%; height: 100%; }
        .mu-step-disc text { font-family: var(--mu-mono); font-weight: 700; font-size: 9.5px; letter-spacing: 0.26em; text-transform: uppercase; fill: currentColor; }
        .mu-step-disc b { font-family: var(--mu-display); font-weight: 400; font-size: clamp(2.2rem, 4.6vw, 3.2rem); line-height: 1; }
        @supports (animation-timeline: view()) {
            html.es-anim #mu .mu-step-disc svg {
                animation: mu-quarter linear both;
                animation-timeline: view();
                animation-range: entry 0% exit 100%;
            }
        }
        @keyframes mu-quarter { from { rotate: -140deg; } to { rotate: 140deg; } }
        .mu-step h3 { margin-top: 1.75rem; font-size: 2.1rem; }
        .mu-step p { margin: 0.8rem auto 0; max-width: 21rem; color: var(--mu-ink-2); }

        /* ---------------------------------------------------------------
           The back catalogue: key features as an order sheet
           --------------------------------------------------------------- */
        .mu-cat-grid { display: grid; gap: 2rem 4rem; align-items: start; }
        @media (min-width: 960px) { .mu-cat-grid { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1.3fr); } }
        .mu-cat-list { border-top: 2px solid var(--mu-ink); }
        .mu-cat-row {
            display: grid;
            grid-template-columns: 4.2rem minmax(0, 1fr) auto;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 0.9rem 1.1rem;
            border-bottom: 2px solid var(--mu-ink);
            transition: background-color 0.2s ease, color 0.2s ease, padding 0.25s ease;
        }
        .mu-cat-row:hover { background: var(--mu-ink); color: var(--mu-paper); padding-inline: 1.4rem 0.9rem; }
        .mu-cat-row .mu-m { color: var(--mu-hot-ink); }
        .mu-cat-row:hover .mu-m { color: var(--mu-gold); }
        .mu-cat-row strong { display: block; font-family: var(--mu-display); font-weight: 400; font-size: 1.7rem; text-transform: uppercase; line-height: 1; letter-spacing: 0.01em; }
        .mu-cat-row small { display: block; margin-top: 0.35rem; font-size: 0.95rem; color: var(--mu-ink-2); }
        .mu-cat-row:hover small { color: inherit; opacity: 0.82; }
        .mu-cat-row svg { width: 1.5rem; height: 1.5rem; transition: translate 0.25s ease; }
        .mu-cat-row:hover svg { translate: 0.3rem 0; }
        .mu-cat-more { margin-top: 1.75rem; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the print changes.
           --------------------------------------------------------------- */
        #mu .mu-plans > section { background: var(--mu-paper); }
        #mu .mu-plans h2 { font-family: var(--mu-display); font-weight: 400; text-transform: uppercase; letter-spacing: 0.005em; line-height: 0.95; font-size: clamp(2.2rem, 5vw, 3.8rem); color: var(--mu-ink); }
        #mu .mu-plans h2 + p { color: var(--mu-ink-2); font-size: 1.0625rem; }
        #mu .mu-plans .grid > div { background: var(--mu-card); border: 2px solid var(--mu-ink); border-radius: 0; box-shadow: 8px 8px 0 var(--mu-ink); color: var(--mu-ink); }
        #mu .mu-plans .grid > div:hover { box-shadow: 8px 8px 0 var(--mu-hot); }
        #mu .mu-plans .grid > div span,
        #mu .mu-plans .grid > div p,
        #mu .mu-plans .grid > div li { color: var(--mu-ink-2); }
        #mu .mu-plans .grid > div .text-3xl { font-family: var(--mu-display); font-weight: 400; font-size: 3rem; color: var(--mu-ink); }
        #mu .mu-plans .grid > div .uppercase { font-family: var(--mu-mono); color: var(--mu-ink); }
        #mu .mu-plans .grid > div .rounded-full { background: var(--mu-hot); color: var(--mu-on-hot); border-radius: 0; font-family: var(--mu-mono); }
        #mu .mu-plans .grid > div svg { color: var(--mu-hot-ink); }
        #mu .mu-plans a.font-medium { color: var(--mu-ink); border-bottom: 2px solid var(--mu-hot); }
        #mu .mu-plans a.rounded-2xl { background: var(--mu-ink); color: var(--mu-paper); border-radius: 0; box-shadow: 6px 6px 0 var(--mu-hot); font-family: var(--mu-display); font-weight: 400; font-size: 1.15rem; letter-spacing: 0.04em; text-transform: uppercase; }
        #mu .mu-plans a.rounded-2xl:hover { transform: translate(3px, 3px); box-shadow: 3px 3px 0 var(--mu-hot); }

        #mu .mu-keep > section { background: var(--mu-paper-2); border-top: 2px solid var(--mu-ink); }
        #mu .mu-keep h2 { font-family: var(--mu-display); font-weight: 400; text-transform: uppercase; font-size: clamp(2rem, 4vw, 3rem); line-height: 1; color: var(--mu-ink); }
        #mu .mu-keep p.uppercase { font-family: var(--mu-mono); font-weight: 700; letter-spacing: 0.16em; color: var(--mu-hot-ink); }
        #mu .mu-keep .grid > a { background: var(--mu-card); border: 2px solid var(--mu-ink); border-radius: 0; }
        #mu .mu-keep .grid > a:hover { box-shadow: 6px 6px 0 var(--mu-hot); border-color: var(--mu-ink); }
        #mu .mu-keep .grid > a > span:first-child { display: none; }
        #mu .mu-keep .grid > a h3 { color: var(--mu-ink); }
        #mu .mu-keep .grid > a p { color: var(--mu-ink-2); }
        #mu .mu-keep .grid > a > span:last-child,
        #mu .mu-keep a.self-start { color: var(--mu-hot-ink); }

        /* ---------------------------------------------------------------
           You might also dig: four more sleeves
           --------------------------------------------------------------- */
        .mu-also-head { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: end; gap: 1.25rem; margin-bottom: 2.5rem; }
        .mu-also { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; }
        @media (min-width: 900px) { .mu-also { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.75rem; } }
        .mu-also a {
            position: relative;
            aspect-ratio: 1;
            container-type: inline-size;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(0.9rem, 1.6vw, 1.5rem);
            overflow: hidden;
            background: var(--bg);
            color: var(--fg);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.3);
            transition: translate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), rotate 0.3s cubic-bezier(0.34, 1.4, 0.64, 1), box-shadow 0.3s ease;
        }
        .dark .mu-also a { box-shadow: 0 0 0 1px rgba(242, 234, 217, 0.22); }
        .mu-also a:hover { translate: 0 -0.5rem; rotate: -1.5deg; box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.3), 0 1.5rem 2rem -1rem rgba(21, 18, 15, 0.75); }
        .mu-also a::before { content: ""; position: absolute; inset: 0; background: var(--art); }
        .mu-also a > * { position: relative; }
        .mu-also small { align-self: flex-start; margin: -0.3em -0.55em -0.15em; padding: 0.3em 0.55em 0.15em; background: var(--bg); font-size: clamp(0.62rem, 5.4cqi, 0.8rem); }
        .mu-also strong { font-family: var(--mu-display); font-weight: 400; font-size: clamp(1.4rem, 17cqi, 3.2rem); text-transform: uppercase; line-height: 0.92; text-wrap: balance; }

        /* ---------------------------------------------------------------
           Liner notes: the questions
           --------------------------------------------------------------- */
        .mu-notes-grid { display: grid; gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 1000px) {
            .mu-notes-grid { grid-template-columns: minmax(0, 0.72fr) minmax(0, 1.28fr); }
            .mu-notes-head { position: sticky; top: 6.5rem; }
        }
        .mu-notes-head .mu-h2 { margin-block: 1.25rem 1.1rem; font-size: clamp(2.6rem, 5.2vw, 4.4rem); }
        .mu-qa { counter-reset: mu-q; border-top: 2px solid var(--mu-ink); }
        .mu-qa details { counter-increment: mu-q; border-bottom: 2px solid var(--mu-ink); }
        .mu-qa summary {
            display: grid;
            grid-template-columns: 2.6rem minmax(0, 1fr) 1.5rem;
            align-items: start;
            gap: 0.75rem;
            padding: 1.35rem 0.25rem 1.2rem;
            cursor: pointer;
        }
        .mu-qa summary::before {
            content: counter(mu-q, decimal-leading-zero);
            font-family: var(--mu-mono);
            font-weight: 700;
            font-size: 0.875rem;
            letter-spacing: 0.1em;
            color: var(--mu-hot-ink);
            padding-top: 0.25rem;
        }
        .mu-qa h3 { font-size: 1.2rem; font-weight: 700; line-height: 1.3; }
        .mu-qa summary i { position: relative; width: 1.5rem; height: 1.5rem; margin-top: 0.1rem; }
        .mu-qa summary i::before,
        .mu-qa summary i::after { content: ""; position: absolute; inset: calc(50% - 1px) 0 auto 0; height: 2px; background: currentColor; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .mu-qa summary i::after { rotate: 90deg; }
        .mu-qa details[open] summary i::after { rotate: 0deg; }
        .mu-qa details[open] summary { color: var(--mu-hot-ink); }
        .mu-qa details p { padding: 0 0.25rem 1.6rem 3.6rem; max-width: 46rem; color: var(--mu-ink-2); }
        @media (max-width: 560px) { .mu-qa details p { padding-inline-start: 0.25rem; } }

        /* ---------------------------------------------------------------
           The test pressing: sign the label
           --------------------------------------------------------------- */
        .mu-press {
            position: relative;
            overflow: clip;
            background: #e8472c;
            color: #15120f;
            padding-block: clamp(4.5rem, 10vw, 8.5rem);
        }
        .mu-press::before {
            content: "";
            position: absolute;
            inset: 0;
            background: var(--mu-grain);
            mix-blend-mode: multiply;
            opacity: 0.4;
            pointer-events: none;
        }
        .mu-press-disc {
            position: absolute;
            width: min(62rem, 120vw);
            aspect-ratio: 1;
            left: 50%;
            top: 58%;
            translate: -50% 0;
            border-radius: 50%;
            background:
                radial-gradient(circle, #e8472c 0 2%, #f2ead9 2.4% 15%, #0c0b0a 15.4% 19%, transparent 19.4%),
                conic-gradient(from 30deg, rgba(255, 255, 255, 0) 0deg, rgba(255, 255, 255, 0.14) 24deg, rgba(255, 255, 255, 0) 50deg, rgba(255, 255, 255, 0) 180deg, rgba(255, 255, 255, 0.1) 204deg, rgba(255, 255, 255, 0) 230deg, rgba(255, 255, 255, 0) 360deg),
                repeating-radial-gradient(circle, #1b1a18 0 1px, #070706 1px 3px);
        }
        .mu-press-in { position: relative; text-align: center; }
        .mu-press .mu-tag { color: #15120f; }
        .mu-press-h2 { margin-top: 1.5rem; font-size: clamp(3rem, 9.6vw, 8.5rem); line-height: 0.88; text-wrap: balance; }
        .mu-press-sub { margin: 1.5rem auto 0; max-width: 36rem; font-size: 1.2rem; font-weight: 700; }
        .mu-white {
            position: relative;
            width: min(100%, 35rem);
            margin: clamp(2.5rem, 5vw, 4rem) auto 0;
            padding: 1.5rem 1.4rem 1.4rem;
            background: #fbf6ea;
            color: #15120f;
            border: 2px solid #15120f;
            box-shadow: 10px 10px 0 #15120f;
            text-align: start;
        }
        .mu-white-top { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.9rem; border-bottom: 2px solid #15120f; }
        .mu-white-top span:last-child { color: #b02a11; }
        .mu-white-field { display: block; margin-block: 1.2rem 0.5rem; color: #675d50; }
        .mu-white-form { display: grid; gap: 1rem; }
        #mu .mu-claim {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 1rem 1rem;
            border: 2px solid #15120f;
            background: #fff;
            font-family: var(--mu-mono);
            font-weight: 700;
            font-size: clamp(1rem, 3.4vw, 1.2rem);
            transition: box-shadow 0.2s ease;
        }
        #mu .mu-claim:focus-within { border-color: #15120f; box-shadow: 0 0 0 4px rgba(21, 18, 15, 0.28); }
        #mu .mu-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: #15120f;
            box-shadow: none;
            outline: none;
        }
        #mu .mu-claim input::placeholder { color: #8a7f70; }
        .mu-claim span { flex: none; color: #675d50; user-select: none; }
        #mu .mu-white .mu-btn { background: #15120f; color: #f2ead9; border-color: #15120f; box-shadow: 6px 6px 0 #e8472c; }
        #mu .mu-white .mu-btn:hover { box-shadow: 3px 3px 0 #e8472c; }
        #mu .mu-press a:focus-visible { outline-color: #15120f; }
        .mu-white-note { margin-top: 1rem; color: #675d50; font-size: 0.95rem; }
        .mu-white-foot { margin-top: 1.1rem; padding-top: 0.9rem; border-top: 2px dashed rgba(21, 18, 15, 0.35); color: #473f35; font-size: 0.7rem; }

        @media (prefers-reduced-motion: reduce) {
            .mu-label,
            .mu-turn i { animation: none; }
            .mu-btn, .mu-slide, .mu-cover-disc, .mu-bin, .mu-sticker, .mu-also a, .mu-cat-row { transition: none; }
        }
    </style>

    @php
        $muTracks = [
            ['top', 'Cover'],
            ['features', 'Side A'],
            ['side-b', 'Side B'],
            ['discography', 'Discography'],
            ['filed-under', 'Filed under'],
            ['run-of-show', 'Run of show'],
            ['faq', 'Liner notes'],
            ['claim', 'Test pressing'],
        ];
        $muGenres = ['Rock', 'Jazz', 'Folk', 'Blues', 'Classical', 'Country', 'Indie', 'Metal', 'Hip-Hop', 'Electronic', 'Acoustic', 'Punk', 'Soul', 'Reggae'];
        $muArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $muDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="mu">

        <nav class="mu-index es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($muTracks as [$muId, $muLabel])
                    <li><a href="#{{ $muId }}" class="es-dot mu-m"><span>{{ $muLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the sleeve                                          -->
        <!-- ============================================================ -->
        <section class="mu-hero" id="top">
            <p class="mu-spine mu-m" aria-hidden="true">Event Schedule presents &middot; Side A &middot; Stereo &middot; 33&#8531; RPM</p>

            <div class="mu-wrap mu-hero-grid">
                <div class="mu-copy">
                    <h1 class="mu-d mu-h1">
                        <x-marketing.hero-eyebrow class="mu-eyebrow es-fade-up es-d-1">
                            Tour dates page for musicians, bands &amp; solo artists
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Your gigs.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Your fans.</span></span>
                        <span class="es-mask mu-mask-3"><span class="es-mask-line"><span class="mu-hot">No middleman.</span></span></span>
                    </h1>

                    <p class="mu-lede es-fade-up es-d-2">
                        One link for every show you play. Fans hear it from you, not from an algorithm. And when you sell tickets, the platform fee is zero.
                    </p>

                    <div class="mu-cta es-fade-up es-d-3">
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="mu-btn">
                            Create your schedule
                            {!! $muArrow !!}
                        </a>
                        <a href="#features" class="mu-btn mu-btn-ghost">
                            See the bill
                            {!! $muDown !!}
                        </a>
                    </div>

                    <ul class="mu-stickers es-fade-up es-d-4">
                        <li class="mu-sticker mu-sticker-hot" style="--r: -4deg;">Free forever</li>
                        <li class="mu-sticker mu-sticker-line" style="--r: 2deg;">No platform fees</li>
                        <li class="mu-sticker mu-sticker-ink" style="--r: -2deg;">All ages</li>
                    </ul>
                </div>

                <div class="mu-art es-fade-up es-d-2" aria-hidden="true">
                    <div class="mu-slide">
                        <div class="mu-vinyl">
                            <div class="mu-label">
                                <svg viewBox="0 0 200 200">
                                    <defs><path id="mu-label-ring" d="M100,100 m-74,0 a74,74 0 1,1 148,0 a74,74 0 1,1 -148,0" /></defs>
                                    <text><textPath href="#mu-label-ring">your-band.eventschedule.com &#183; side a &#183; 33&#8531; rpm &#183;</textPath></text>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="mu-sleeve">
                        <div class="mu-sleeve-top mu-m"><span>ES 4001</span><span>Stereo</span></div>
                        <div class="mu-sleeve-title">
                            <small class="mu-m">Summer tour &middot; Live</small>
                            <strong class="mu-d">The Midnight Hour</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The track list: three dates, set like the back of the sleeve -->
            <div class="mu-wrap es-fade-up es-d-4" aria-hidden="true">
                <div class="mu-dates" dir="ltr">
                    <div class="mu-date">
                        <span class="mu-m mu-date-no">A1</span>
                        <span class="mu-d mu-date-day">Jun 12</span>
                        <span class="mu-date-where">The Roxy <small>Los Angeles</small></span>
                        <span class="mu-m mu-date-chip">Tickets</span>
                    </div>
                    <div class="mu-date">
                        <span class="mu-m mu-date-no">A2</span>
                        <span class="mu-d mu-date-day">Jun 14</span>
                        <span class="mu-date-where">Mercury Lounge <small>New York</small></span>
                        <span class="mu-m mu-date-chip">Tickets</span>
                    </div>
                    <div class="mu-date">
                        <span class="mu-m mu-date-no">A3</span>
                        <span class="mu-d mu-date-day">Jun 20</span>
                        <span class="mu-date-where">The Crocodile <small>Seattle</small></span>
                        <span class="mu-m mu-date-chip mu-date-chip-out">Sold out</span>
                    </div>
                </div>
            </div>

            <!-- Genre ticker -->
            <div class="mu-ticker es-fade-up es-d-5">
                <div class="es-marquee" data-marquee="1">
                    <div class="es-marquee-track">
                        @for ($genreCopy = 0; $genreCopy < 2; $genreCopy++)
                            @foreach ($muGenres as $genre)
                                <span @if ($genreCopy === 1) aria-hidden="true" @endif class="mu-d mu-genre">{{ $genre }}</span>
                            @endforeach
                        @endfor
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The problem: great show, empty room                       -->
        <!-- ============================================================ -->
        <section class="mu-flip mu-section">
            <div class="mu-wrap">
                <div class="mu-flip-head">
                    <span class="mu-m mu-tag" data-reveal>The problem</span>
                    <h2 class="mu-d mu-flip-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Great show. <span class="mu-hollow">Empty room.</span>
                    </h2>
                    <p class="mu-flip-sub" data-reveal style="--reveal-delay: 0.16s;">
                        You played your heart out. Your fans found out on Monday.
                    </p>
                </div>

                <div class="mu-stats" data-reveal-group="120">
                    <div class="mu-stat" data-reveal style="--c: #ff5433;">
                        <div class="mu-d mu-stat-big">Most</div>
                        <p>of your social media followers never see your posts about shows</p>
                        <span class="mu-m mu-stamp" style="--r: -3deg;">Buried by the feed</span>
                    </div>
                    <div class="mu-stat" data-reveal style="--c: #f2ead9;">
                        <div class="mu-d mu-stat-big">Too late</div>
                        <p>fans only hear about your shows after they've happened</p>
                        <span class="mu-m mu-stamp" style="--r: 2deg;">Missed it</span>
                    </div>
                    <div class="mu-stat" data-reveal style="--c: #f0bd4a;">
                        <div class="es-od mu-d mu-stat-big" data-odometer="10-20%">10-20%</div>
                        <p>of ticket revenue lost to platform fees elsewhere. Event Schedule charges zero.</p>
                        <span class="mu-m mu-stamp" style="--r: -2deg;">Gone</span>
                    </div>
                </div>

                <p class="mu-flip-foot" data-reveal>
                    Your fans deserve a better flyer.
                    <a href="#features">
                        Here is the fix
                        {!! $muDown !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Side A and Side B: the six tracks                         -->
        <!-- ============================================================ -->
        <section id="features" class="mu-section" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-side-head">
                    <div>
                        <span class="mu-m mu-tag" data-reveal>On the bill</span>
                        <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.25rem;">
                            Everything a working musician needs
                        </h2>
                    </div>
                    <div class="mu-d mu-side-mark" aria-hidden="true" data-reveal="right">Side <b>A</b></div>
                </div>

                <!-- A1. Newsletters -->
                <article class="mu-track">
                    <div class="mu-d mu-track-no" aria-hidden="true">A1</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Newsletters</div>
                        <h3 class="mu-d">Direct to the fans</h3>
                        <p>New tour on sale? Say it straight to the inbox. Fans who sign up by email also get a digest of your new dates, batched so a run of shows arrives as one message, and every plan can generate social-ready show art from your upcoming gigs.</p>
                        <div class="mu-chips">
                            <span class="mu-m mu-chip">Tour announcements</span>
                            <span class="mu-m mu-chip">Album releases</span>
                            <span class="mu-m mu-chip">Show reminders</span>
                        </div>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-cardboard mu-mail">
                            <span class="mu-m mu-prop-stamp">No algorithm</span>
                            <div class="mu-mail-from">
                                <span class="mu-mail-av">MH</span>
                                <div>
                                    <strong>The Midnight Hour</strong>
                                    <small>Summer tour on sale now</small>
                                </div>
                            </div>
                            <div class="mu-mail-body">
                                <span class="mu-m">This Saturday</span>
                                <div class="mu-d">Live at The Roxy</div>
                                <p>Doors 7 PM &middot; $25</p>
                            </div>
                            <div class="mu-mail-stats">
                                <div><span class="mu-d"><span data-count-to="72">72</span>%</span><small class="mu-m">opened</small></div>
                                <div><span class="mu-d"><span data-count-to="31">31</span>%</span><small class="mu-m">clicked</small></div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- A2. Zero-fee tickets -->
                <article class="mu-track">
                    <div class="mu-d mu-track-no" aria-hidden="true">A2</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Ticketing</div>
                        <h3 class="mu-d">Zero-fee tickets</h3>
                        <p>Connect Stripe or <a href="{{ marketing_url('/paypal') }}">PayPal</a>, or take cash on the night, and sell pre-sales or door tickets with QR check-in. Pro adds promo codes for the fan club and waitlists for the sellouts.</p>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-stub" dir="ltr">
                            <div class="mu-stub-main">
                                <div class="mu-stub-row"><span>GA TICKET x2</span><span>$50.00</span></div>
                                <div class="mu-stub-row"><span>PLATFORM FEE</span><span>{{ plan_price(0) }}</span></div>
                                <div class="mu-stub-keep">
                                    <small class="mu-m">You keep</small>
                                    <span class="mu-d">100%</span>
                                    <small class="mu-m">of ticket sales</small>
                                </div>
                            </div>
                            <div class="mu-stub-tear"><span class="mu-d">No fees ever</span></div>
                        </div>
                    </div>
                </article>

                <!-- A3. One link for everything -->
                <article class="mu-track">
                    <div class="mu-d mu-track-no" aria-hidden="true">A3</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Share link</div>
                        <h3 class="mu-d">One link. Every show.</h3>
                        <p>Put it in your Spotify bio, Bandcamp page, and EPK. You can embed the calendar on your own website too.</p>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-link" dir="ltr">
                            <div class="mu-cardboard mu-link-bar"><i></i><span>yourband.eventschedule.com</span></div>
                            <div class="mu-link-fan"><i></i><i></i><i></i><i></i></div>
                            <div class="mu-link-to mu-m">
                                <span style="--r: -3deg;">Spotify</span>
                                <span style="--r: 2deg;">Bandcamp</span>
                                <span style="--r: -1deg;">EPK</span>
                                <span style="--r: 3deg;">Your site</span>
                            </div>
                        </div>
                    </div>
                </article>

                <div class="mu-turn mu-m" id="side-b" style="scroll-margin-top: 5rem;" aria-hidden="true"><i></i>Turn over for side B</div>

                <!-- B1. Venue bookings, team, AI -->
                <article class="mu-track" style="border-top: 0;">
                    <div class="mu-d mu-track-no" aria-hidden="true">B1</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Venue bookings &middot; Team</div>
                        <h3 class="mu-d">Booked once. Printed twice.</h3>
                        <p>When a venue puts you on its bill on Event Schedule, the gig comes to you as a request. Accept it and it lands on your poster too. One booking, both schedules.</p>
                        <p>Not signed up yet? The venue's listing makes a page with your name on it, each date credited to whoever added it, and kept out of search engines until you <a href="{{ marketing_url('/docs/creating-events#claim') }}">claim it</a> with the email address they entered.</p>
                        <p>On Enterprise, your band, manager, and booking agent get their own logins. And when a booking email lands, paste it in and AI turns it into a listed gig.</p>
                        <div class="mu-chips">
                            <span class="mu-m mu-chip">Lead</span>
                            <span class="mu-m mu-chip">Manager</span>
                            <span class="mu-m mu-chip">Agent</span>
                        </div>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-bills">
                            <div class="mu-cardboard mu-bill">
                                <span class="mu-m">The venue's bill</span>
                                <i></i><i></i><i></i>
                                <b>+ Your band</b>
                            </div>
                            {!! $muArrow !!}
                            <div class="mu-cardboard mu-bill">
                                <span class="mu-m">Your poster</span>
                                <i></i><i></i><i></i>
                                <b>New gig!</b>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- B2. Calendar sync and residencies -->
                <article class="mu-track">
                    <div class="mu-d mu-track-no" aria-hidden="true">B2</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Calendar sync</div>
                        <h3 class="mu-d">The road book</h3>
                        <p>Two-way sync with Google Calendar, Outlook, or CalDAV. Weekly residency? Set the pattern once and it repeats, minus the weeks you skip.</p>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-cardboard mu-road">
                            <div class="mu-road-row"><span class="mu-d">Thu</span><span>Gig &middot; Doors 8 PM</span></div>
                            <div class="mu-road-row"><span class="mu-d">Fri</span><span>Rehearsal</span></div>
                            <div class="mu-road-row"><span class="mu-d">Sun</span><span>Studio session</span></div>
                            <div class="mu-m mu-road-foot">Google &middot; Outlook &middot; CalDAV</div>
                        </div>
                    </div>
                </article>

                <!-- B3. Followers and fan content -->
                <article class="mu-track" style="border-bottom: 2px solid var(--mu-ink);">
                    <div class="mu-d mu-track-no" aria-hidden="true">B3</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Followers</div>
                        <h3 class="mu-d">The street team</h3>
                        <p>Fans sign up with their email and hear automatically when you add a date. Rather not hand over an address? They can <a href="{{ marketing_url('/docs/sharing#calendar-feeds') }}">subscribe to your calendar</a> instead, and a moved date updates itself. After the show they post clips, photos, and comments, and nothing goes live until you approve it.</p>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-cardboard mu-crew">
                            <div class="mu-crew-faces">
                                <span style="--bg: var(--mu-hot); --fg: var(--mu-on-hot);">A</span>
                                <span style="--bg: var(--mu-gold); --fg: #15120f;">B</span>
                                <span style="--bg: var(--mu-ink); --fg: var(--mu-paper);">C</span>
                                <span>+127</span>
                            </div>
                            <span class="mu-d"><span data-count-to="130">130</span> fans following</span>
                            <small class="mu-m">Emailed when you add dates</small>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The discography: a career, one release at a time          -->
        <!-- ============================================================ -->
        <section id="discography" class="mu-section mu-alt" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-disco-head">
                    <div><span class="mu-m mu-tag" data-reveal>The routing</span></div>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        From open mics to festival stages
                    </h2>
                    <p class="mu-sub" data-reveal style="--reveal-delay: 0.16s;">
                        One schedule that grows with your career.
                    </p>
                </div>

                @php
                    $tourStops = [
                        ['title' => 'Open mic nights', 'desc' => 'Coffee shops and local bars. Track your spots and let friends know where to catch you.'],
                        ['title' => 'Local gigging', 'desc' => 'Regular slots at venues around your city. Build a local following and start selling your own tickets.'],
                        ['title' => 'Regional tours', 'desc' => 'Weekend runs and opening slots. Fans in nearby cities follow along and know when you\'re coming through.'],
                        ['title' => 'Headlining', 'desc' => 'Your name at the top of the bill. Email your fans directly and sell out your own shows.'],
                        ['title' => 'National tours', 'desc' => 'Multi-city runs across the country. One link tells fans everywhere when you\'re hitting their town.'],
                        ['title' => 'Festivals & special events', 'desc' => 'Festival slots, album release shows, and one-off specials, all on one professional schedule.'],
                    ];
                    // One sleeve per stop: format, ground, type colour, label colour, and the art.
                    $muCovers = [
                        ['7" single', '#f2ead9', '#15120f', '#e8472c', 'radial-gradient(circle at 74% 34%, #e8472c 0 13%, transparent 13.4%), linear-gradient(#15120f, #15120f) 8% 58% / 46% 2px no-repeat'],
                        ['EP', '#15120f', '#f2ead9', '#e9b23a', 'repeating-linear-gradient(180deg, transparent 0 9%, rgba(242, 234, 217, 0.16) 9% 12%), linear-gradient(90deg, transparent 62%, #e9b23a 62% 70%, transparent 70%) 0 0 / 100% 72% no-repeat'],
                        ['LP', '#e9b23a', '#15120f', '#15120f', 'repeating-linear-gradient(135deg, transparent 0 7%, rgba(21, 18, 15, 0.9) 7% 9.5%) 0 0 / 100% 46% no-repeat'],
                        ['LP', '#e8472c', '#15120f', '#f2ead9', 'radial-gradient(circle at 78% 22%, #15120f 0 34%, transparent 34.4%), radial-gradient(circle at 78% 22%, transparent 0 40%, rgba(21, 18, 15, 0.9) 40% 42%, transparent 42.4%)'],
                        ['2xLP', '#0e5a6b', '#f2ead9', '#e9b23a', 'linear-gradient(#0e5a6b, #0e5a6b) 0 100% / 100% 42% no-repeat, radial-gradient(circle, rgba(242, 234, 217, 0.9) 0 16%, transparent 17%) 0 0 / 12.5% 12.5%'],
                        ['Live', '#f2ead9', '#15120f', '#e8472c', 'linear-gradient(#f2ead9, #f2ead9) 0 100% / 100% 40% no-repeat, repeating-radial-gradient(circle at 100% 0%, #e8472c 0 6%, #f2ead9 6% 9%, #e9b23a 9% 15%, #15120f 15% 18%, #f2ead9 18% 21%)'],
                    ];
                @endphp

                <div class="mu-crate" data-reveal-group="90">
                    @foreach ($tourStops as $stopIndex => $stop)
                        @php [$muFormat, $muBg, $muFg, $muLab, $muArt] = $muCovers[$stopIndex]; @endphp
                        <article class="mu-rel" data-reveal>
                            <div class="mu-cover" aria-hidden="true">
                                <div class="mu-cover-disc" style="--lab: {{ $muLab }};"></div>
                                <div class="mu-cover-face" style="--bg: {{ $muBg }}; --fg: {{ $muFg }}; --art: {{ $muArt }};">
                                    <div class="mu-m mu-cover-meta"><span>Stop {{ str_pad($stopIndex + 1, 2, '0', STR_PAD_LEFT) }}</span><span>{{ $muFormat }}</span></div>
                                    <div class="mu-d mu-cover-title">{{ $stop['title'] }}</div>
                                </div>
                            </div>
                            <h3 class="sr-only">{{ $stop['title'] }}</h3>
                            <p>{{ $stop['desc'] }}</p>
                            @if ($stopIndex === count($tourStops) - 1)
                                <span class="mu-m mu-rel-flag">Headliner</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Filed under: every kind of musician                       -->
        <!-- ============================================================ -->
        <section id="filed-under" class="mu-section" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-bins-head">
                    <span class="mu-m mu-tag" data-reveal>Filed under</span>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Perfect for every kind of musician
                    </h2>
                    <p class="mu-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Whether you're a solo artist or a touring band, Event Schedule works for you.
                    </p>
                </div>

                @php
                    $muBins = [
                        ['Solo', 'Solo Artists', 'Share your acoustic nights, open mics, and solo performances with your growing fanbase.', 'for-solo-artists', '8%'],
                        ['Rock / Pop', 'Rock & Pop Bands', 'Coordinate your tour dates across the whole band and let fans follow along.', 'for-rock-pop-bands', '36%'],
                        ['Jazz', 'Jazz Musicians', 'List your residencies, jam sessions, and special performances at clubs and festivals.', 'for-jazz-musicians', '66%'],
                        ['Covers', 'Cover Bands', 'Show your weekly bar gigs and private events all in one professional calendar.', 'for-cover-bands', '58%'],
                        ['Tribute', 'Tribute Acts', 'Build a dedicated fanbase for your tribute shows and special themed events.', 'for-tribute-acts', '10%'],
                        ['Session', 'Session Musicians', 'Show your availability and let bands know when you\'re free for gigs and recording sessions.', 'for-session-musicians', '40%'],
                    ];
                @endphp

                <div class="mu-bins" data-reveal-group="80">
                    @foreach ($muBins as [$muTab, $muName, $muDesc, $muSlug, $muX])
                        @php $muPost = get_sub_audience_blog($muSlug); @endphp
                        <article class="mu-bin" data-reveal>
                            <span class="mu-m mu-bin-tab" style="--x: {{ $muX }};" aria-hidden="true">{{ $muTab }}</span>
                            <h3 class="mu-d">{{ $muName }}</h3>
                            <p>{{ $muDesc }}</p>
                            @if ($muPost)
                                <a href="{{ blog_url('/' . $muPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $muName }}">
                                    <span>Learn more {!! $muArrow !!}</span>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Run of show: how it works                                 -->
        <!-- ============================================================ -->
        <section id="run-of-show" class="mu-section mu-alt" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-bins-head" style="margin-bottom: 0;">
                    <span class="mu-m mu-tag" data-reveal>Run of show</span>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Three steps to showtime
                    </h2>
                </div>

                <div class="mu-steps" data-reveal-group="140">
                    <div class="mu-step" data-reveal>
                        <div class="mu-step-disc" style="--bg: #e8472c; --fg: #15120f;" aria-hidden="true">
                            <svg viewBox="0 0 200 200"><defs><path id="mu-step-1" d="M100,100 m-49,0 a49,49 0 1,1 98,0 a49,49 0 1,1 -98,0" /></defs><text><textPath href="#mu-step-1">Doors &#183; Doors &#183; Doors &#183; Doors &#183; Doors &#183;</textPath></text></svg>
                            <b>01</b>
                        </div>
                        <h3 class="mu-d">Add your gigs</h3>
                        <p>Import from Google Calendar or add tour dates manually. Set up ticket sales if you want.</p>
                    </div>
                    <div class="mu-step" data-reveal>
                        <div class="mu-step-disc" style="--bg: #e9b23a; --fg: #15120f;" aria-hidden="true">
                            <svg viewBox="0 0 200 200"><defs><path id="mu-step-2" d="M100,100 m-49,0 a49,49 0 1,1 98,0 a49,49 0 1,1 -98,0" /></defs><text><textPath href="#mu-step-2">Soundcheck &#183; Soundcheck &#183; Soundcheck &#183;</textPath></text></svg>
                            <b>02</b>
                        </div>
                        <h3 class="mu-d">Share your link</h3>
                        <p>Add it to your Spotify bio, Bandcamp, EPK, or anywhere fans find you.</p>
                    </div>
                    <div class="mu-step" data-reveal>
                        <div class="mu-step-disc" style="--bg: #f2ead9; --fg: #15120f;" aria-hidden="true">
                            <svg viewBox="0 0 200 200"><defs><path id="mu-step-3" d="M100,100 m-49,0 a49,49 0 1,1 98,0 a49,49 0 1,1 -98,0" /></defs><text><textPath href="#mu-step-3">Showtime &#183; Showtime &#183; Showtime &#183;</textPath></text></svg>
                            <b>03</b>
                        </div>
                        <h3 class="mu-d">Grow your fanbase</h3>
                        <p>Fans leave an email address, hear when you announce new dates, and share videos and comments after your gigs, all approved by you before they go live. Build your audience on your terms.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. The back catalogue: key features                          -->
        <!-- ============================================================ -->
        <section class="mu-section">
            <div class="mu-wrap mu-cat-grid">
                <div>
                    <span class="mu-m mu-tag" data-reveal>Also on the bill</span>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.25rem;">Key features</h2>
                    <p class="mu-cat-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="mu-more-link mu-m">
                            See all features
                            {!! $muArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $muCatalogue = [
                        ['ES-101', 'Ticketing', 'Sell tickets with QR check-in and zero platform fees', marketing_url('/features/ticketing')],
                        ['ES-102', 'Newsletters', 'Send event updates directly to followers\' inboxes', marketing_url('/features/newsletters')],
                        ['ES-103', 'Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', marketing_url('/features/calendar-sync')],
                        ['ES-104', 'Boost', 'Promote events with Facebook and Instagram ads', marketing_url('/features/boost')],
                    ];
                @endphp
                <div class="mu-cat-list" data-reveal-group="70">
                    @foreach ($muCatalogue as [$muCatNo, $muCatName, $muCatDesc, $muCatUrl])
                        <a href="{{ $muCatUrl }}" class="mu-cat-row" data-reveal>
                            <span class="mu-m" aria-hidden="true">{{ $muCatNo }}</span>
                            <span>
                                <strong>{{ $muCatName }}</strong>
                                <small>{{ $muCatDesc }}</small>
                            </span>
                            {!! $muArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="mu-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 8. Related pages: four more sleeves                          -->
        <!-- ============================================================ -->
        <section class="mu-section mu-alt">
            <div class="mu-wrap">
                <div class="mu-also-head">
                    <div>
                        <span class="mu-m mu-tag" data-reveal>Other stages</span>
                        <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.25rem;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="mu-more-link mu-m" data-reveal>
                        See all use cases
                        {!! $muArrow !!}
                    </a>
                </div>

                <div class="mu-also" data-reveal-group="80">
                    <a href="{{ marketing_url('/for-comedians') }}" data-reveal style="--bg: #e9b23a; --fg: #15120f; --art: radial-gradient(circle at 100% 0%, #15120f 0 42%, transparent 42.4%);">
                        <small class="mu-m">Event Schedule for</small>
                        <strong>Comedians</strong>
                    </a>
                    <a href="{{ marketing_url('/for-djs') }}" data-reveal style="--bg: #15120f; --fg: #f2ead9; --art: repeating-radial-gradient(circle at 100% 0%, transparent 0 7%, #e8472c 7% 9.4%) 100% 0 / 100% 62% no-repeat;">
                        <small class="mu-m">Event Schedule for</small>
                        <strong>DJs</strong>
                    </a>
                    <a href="{{ marketing_url('/for-spoken-word') }}" data-reveal style="--bg: #f2ead9; --fg: #15120f; --art: linear-gradient(#e8472c, #e8472c) 0 0 / 100% 36% no-repeat;">
                        <small class="mu-m">Event Schedule for</small>
                        <strong>Spoken Word Artists</strong>
                    </a>
                    <a href="{{ marketing_url('/for-dance-groups') }}" data-reveal style="--bg: #0e5a6b; --fg: #f2ead9; --art: repeating-linear-gradient(115deg, transparent 0 11%, #e9b23a 11% 14.5%) 100% 0 / 52% 56% no-repeat;">
                        <small class="mu-m">Event Schedule for</small>
                        <strong>Dance Groups</strong>
                    </a>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Liner notes: the questions                                -->
        <!-- ============================================================ -->
        <section id="faq" class="mu-section mu-notes" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap mu-notes-grid">
                <div class="mu-notes-head">
                    <span class="mu-m mu-tag" data-reveal>The fine print</span>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="mu-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Everything musicians ask about Event Schedule.
                    </p>
                </div>

                <div class="mu-qa" data-reveal>
                    @php
                        $faqs = [
                            ['q' => 'Is Event Schedule free for musicians?', 'a' => 'Yes. Event Schedule is free forever for sharing your gig schedule, building a fan following, and syncing with Google Calendar. The free plan also takes unlimited free registrations and sends 10 newsletter emails a month, counted per recipient. Scanning tickets at the door is free too. Charging at the door is where Pro starts, and it brings the rest of the door kit with it: live check-in dashboard, promo codes, waitlists. Platform fees are zero on every plan.'],
                            ['q' => 'How do fans find out about my upcoming shows?', 'a' => 'A fan who leaves an email address on your page and confirms it gets a digest when you announce new shows, batched so a run of dates is one message rather than six, and never more than once every few days. Beyond that you write the newsletter yourself. Fans who would rather not give an address can subscribe to your calendar feed instead, which updates itself when a date moves. Your schedule link also goes anywhere a link goes: Spotify, Bandcamp, your EPK, any social profile.'],
                            ['q' => 'Can I sell tickets to my own shows?', 'a' => 'Yes. Connect your own Stripe or PayPal account, or take cash or a payment link, and sell tickets directly from your schedule. Every ticket includes a QR code for check-in at the door. Event Schedule charges zero platform fees - you only pay your payment provider\'s own processing fee. Refunds are built in too: from the Sales page, a Stripe or PayPal sale can be refunded in full or in part, and the money goes back through the provider.'],
                            ['q' => 'Can fans get told when tickets go on sale?', 'a' => 'Yes, on every plan. Switch on the "Notify me" card and, on a public event page, a fan can leave just an email address, with no account, and get one email when tickets go on sale, one if the show is cancelled, and a reminder shortly before it starts, plus any change notice you choose to send. Nothing else, and every one of those emails has a one-click unsubscribe. The event\'s Tickets panel shows you how many people are waiting.'],
                            ['q' => 'What happens when a venue books me for a show?', 'a' => 'When a venue adds you to its event on Event Schedule, the date arrives as a request on your schedule. Accept it and the gig shows on your page too, so you never enter the same show twice, and because both schedules share one event, a changed time shows on both. The event page lists the whole bill, whether or not every act on it has signed up.'],
                            ['q' => 'A venue listed me before I joined. Is that page mine?', 'a' => 'It can be. When a venue or promoter names an act who is not on Event Schedule, a page is created for them so the name can appear on the event. It says who created it and that the act has not claimed it, credits each date to the schedule that added it, and stays out of search engines. Press Claim this page and sign in with the email address it carries, and it becomes your schedule, with the venues that already list you still listing you. If it is not you, press This is not me.'],
                            ['q' => 'Can I list a weekly residency or recurring gigs?', 'a' => 'Yes. Recurring events are free. Set the day-of-week pattern once, like every Thursday at the same club, and Event Schedule fills in the dates. You can exclude the weeks you skip, and fans always see the next upcoming show.'],
                            ['q' => 'Can I use Event Schedule as my band website?', 'a' => 'Many musicians do. Your schedule lives at your own link, like your-band.eventschedule.com, with your bio, photos, and streaming links, and each of those links also answers at a short address of its own, like your-band.eventschedule.com/instagram, with every click counted in your analytics. You can also embed the calendar on an existing website, and the Enterprise plan supports a fully custom domain.'],
                        ];
                    @endphp
                    @foreach ($faqs as ['q' => $q, 'a' => $a])
                        <details name="faq">
                            <summary>
                                <h3>{{ $q }}</h3>
                                <i aria-hidden="true"></i>
                            </summary>
                            <p>{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- 10. The test pressing: sign the label                        -->
        <!-- ============================================================ -->
        <section id="claim" class="mu-press" style="scroll-margin-top: 4rem;">
            <div class="mu-press-disc" aria-hidden="true"></div>
            <div class="mu-wrap mu-press-in">
                <span class="mu-m mu-tag" data-reveal>Encore</span>
                <h2 class="mu-d mu-press-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Your name on the poster.
                </h2>
                <p class="mu-press-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Stop posting into the void. Put every show on one link and fill the room. Free forever.
                </p>

                <div class="mu-white" data-reveal="panel" id="mu-white">
                    <div class="mu-white-top mu-m"><span>Test pressing</span><span>All access</span></div>
                    <label for="es-claim-input" class="mu-white-field mu-m">Artist <span class="sr-only">: your schedule name</span></label>
                    <div class="mu-white-form">
                        <div dir="ltr" class="es-claim mu-claim">
                            <input id="es-claim-input" type="text" placeholder="your-band" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="mu-btn">
                            Get Started Free
                            {!! $muArrow !!}
                        </a>
                    </div>
                    <p class="mu-white-note">No credit card required</p>
                    <p class="mu-white-foot mu-m">Free forever &middot; Zero platform fees &middot; Only your processor's fee comes off</p>
                </div>
            </div>
        </section>

        <div class="mu-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- The finale's confetti, cut from the sleeve's own inks rather than the site's blues. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var panel = document.getElementById('mu-white');
            if (!panel || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    {{-- The library's ready-made cannon draws from a blob Worker, which the site's
                         policy refuses without an error, so nothing was ever drawn. A cannon made
                         here draws on the page itself. --}}
                    var fire = window.confetti.create(null, { resize: true });
                    var inks = ['#15120f', '#f2ead9', '#e9b23a', '#fbf6ea'];
                    [[60, 0.06], [120, 0.94]].forEach(function (shot) {
                        fire({ particleCount: 70, angle: shot[0], spread: 58, startVelocity: 52, origin: { x: shot[1], y: 0.95 }, colors: inks, shapes: ['circle'], disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.6 });
            io.observe(panel);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
