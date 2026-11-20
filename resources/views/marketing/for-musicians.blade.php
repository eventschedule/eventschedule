<x-marketing-layout>
    <x-slot name="title">Tour Dates Page for Your Band Website, Free to Embed</x-slot>
    <x-slot name="description">Add tour dates to your band website with a free embed that updates itself. Sync from Google Calendar, link tickets, and email fans new shows.</x-slot>
    <x-slot name="breadcrumbTitle">For Musicians</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Anton') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Archivo') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lekton') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Tour dates page for bands and solo artists"
        description="Add tour dates to your band website with a free embed that updates itself. Sync from Google Calendar, link tickets, and email fans new shows."
        audience="Musicians"
        keywords="tour dates page, add tour dates to website, tour dates widget, upcoming shows page, band website tour dates, gig calendar for musicians, embed tour dates" />
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to add tour dates to your band website",
        "description": "Three steps from your first show to a tour dates page embedded on your own website.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your shows",
                "text": "Sign up as a performer and add each show with its date, venue and ticket link, or connect the Google Calendar you book gigs in."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Paste the embed",
                "text": "Choose Embed Schedule from your schedule's Actions menu, pick the List or Calendar layout, and paste the code into your website."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Share one link",
                "text": "Put your schedule link in your bios, your EPK and your email signature. New dates appear on the page, the embed and the link when you publish them."
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
           list, the three steps as labels, the jobs as the tracks of
           side A, and a test pressing to sign at the end.

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
            .mu-ticker, .mu-press { z-index: 41; }
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
           Side A: the tracks
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
        /* The id outranks the colours the x-link component brings, so its links print in the sleeve's inks. */
        #mu .mu-track p a, #mu .mu-notes p a { color: var(--mu-ink); font-weight: 700; text-decoration: underline; text-decoration-color: var(--mu-hot); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        #mu .mu-track p a:hover { color: var(--mu-hot-ink); }
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

        /* A3: the mailer */
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

        /* A1: two bills, one booking */
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

        /* Section heads: a tag, a heading and a line under it. */
        .mu-disco-head { display: grid; gap: 1.25rem; margin-bottom: clamp(2.5rem, 5vw, 4rem); }

        /* A centred section head, for the steps. */
        .mu-bins-head { text-align: center; display: grid; justify-items: center; gap: 1.1rem; margin-bottom: clamp(3.5rem, 6vw, 5rem); }

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

        /* ---------------------------------------------------------------
           The track listing and the pressings: what each line of a tour
           dates page says, set as the back of the sleeve (credits), and
           where every copy of a date comes from, set as the pressing
           plant's run sheet. Tokens only, so dark mode follows.
           --------------------------------------------------------------- */
        .mu-credits { border-top: 2px solid var(--mu-ink); }
        .mu-credit {
            display: grid;
            gap: 0.6rem 2rem;
            padding: 1.6rem 0.25rem 1.5rem;
            border-bottom: 1px solid var(--mu-line);
        }
        @media (min-width: 900px) {
            .mu-credit { grid-template-columns: 4.2rem minmax(0, 0.75fr) minmax(0, 1.6fr); align-items: start; }
        }
        .mu-credit > .mu-m { color: var(--mu-hot-ink); padding-top: 0.4rem; }
        .mu-credit h3 { font-size: clamp(1.7rem, 2.8vw, 2.3rem); text-wrap: balance; }
        .mu-credit p { color: var(--mu-ink-2); max-width: 46rem; }
        .mu-credit p + p { margin-top: 0.65rem; }
        #mu .mu-credit a,
        #mu .mu-inline a {
            display: inline;
            color: var(--mu-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--mu-hot);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }
        #mu .mu-credit a:hover,
        #mu .mu-inline a:hover { color: var(--mu-hot-ink); }

        .mu-run { border-top: 2px solid var(--mu-ink); }
        .mu-run-row {
            display: grid;
            gap: 0.75rem 2rem;
            padding: 1.5rem 0.25rem 1.4rem;
            border-bottom: 2px solid var(--mu-ink);
        }
        @media (min-width: 900px) {
            .mu-run-row { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.4fr) minmax(0, 0.9fr); align-items: start; }
        }
        .mu-run-row h3 { font-size: clamp(1.5rem, 2.4vw, 1.9rem); text-wrap: balance; }
        .mu-run-row p { color: var(--mu-ink-2); font-size: 1rem; }
        .mu-run-row small { display: block; margin-bottom: 0.35rem; color: var(--mu-hot-ink); }
        .mu-run-auto { color: var(--mu-teal); }

        @media (prefers-reduced-motion: reduce) {
            .mu-label { animation: none; }
            .mu-btn, .mu-slide, .mu-sticker, .mu-cat-row { transition: none; }
        }
    </style>

    @php
        $muTracks = [
            ['top', 'Cover'],
            ['how', 'Three steps'],
            ['each-line', 'Each line'],
            ['features', 'Side A'],
            ['upkeep', 'Pressings'],
            ['faq', 'Liner notes'],
            ['claim', 'Test pressing'],
        ];
        $muGenres = ['Rock', 'Jazz', 'Folk', 'Blues', 'Classical', 'Country', 'Indie', 'Metal', 'Hip-Hop', 'Electronic', 'Acoustic', 'Punk', 'Soul', 'Reggae'];
        $muArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $muDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    {{-- Claims on this page, checked against docs/FEATURES.md and the code on 2026-11-18:
         - Embed: "Embed calendar on website" is a Free feature; Actions > Embed Schedule, List or
           Calendar layout, Light/Dark (docs/sharing#embed). Free hosted snippets carry a
           "Powered by Event Schedule" line; removing branding is Pro.
         - Google Calendar sync: Free, To / From / Bidirectional (docs/creating-schedules#integrations-google).
         - A venue adding a talent sends a request, because a Talent schedule always requires
           approval; accepted, it is one event on both schedules (docs/managing-schedules#requests).
           The talent Booking Form ("Request to Book", /request) is free on every plan.
         - Claiming a page created for you: FEATURES.md "Claim a page created for you".
         - Digest to CONFIRMED email subscribers of public events the schedule created itself, at
           most 25 per email (SendEventAnnouncements::MAX_EVENTS_PER_DIGEST), floored at 72 hours,
           outside the newsletter allowance; account followers get newsletters only. On hosted an
           owner without a verified phone or the schedule's own mail settings reaches 50.
         - Newsletter allowance per month: 10 / 100 / 1,000 recipient emails (Role::newsletterLimit()).
         - Tickets elsewhere and free registration: every plan. A priced ticket: Pro
           (Event::canSellPaidTickets()), no platform fee. Door scanning has no plan check.
         - Hide Past Events (Style > Events) has no plan gate. --}}

    <div id="mu">

        <nav class="mu-index es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($muTracks as [$muId, $muLabel])
                    <li><a href="#{{ $muId }}" class="es-dot mu-m"><span>{{ $muLabel }}</span></a></li>
                @endforeach
            </ol>
        </nav>

        {{-- 1. Hero: the sleeve, and the answer --}}
        <section class="mu-hero" id="top">
            <p class="mu-spine mu-m" aria-hidden="true">Event Schedule presents &middot; Side A &middot; Stereo &middot; 33&#8531; RPM</p>

            <div class="mu-wrap mu-hero-grid">
                <div class="mu-copy">
                    <h1 class="mu-d mu-h1">
                        <x-marketing.hero-eyebrow class="mu-eyebrow es-fade-up es-d-1">
                            Tour dates page for bands &amp; solo artists
                        </x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Every show.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">One page.</span></span>
                        <span class="es-mask mu-mask-3"><span class="es-mask-line"><span class="mu-hot">Up to date.</span></span></span>
                    </h1>

                    <p class="mu-lede es-fade-up es-d-2">
                        A tour dates page lists your upcoming shows, each with its date, city, venue and a way in. Event Schedule gives you one free, at your own link, with an embed for your band website and a sync from Google Calendar, so a date you add once shows up everywhere.
                    </p>

                    <div class="mu-cta es-fade-up es-d-3">
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="mu-btn">
                            Make your tour page
                            {!! $muArrow !!}
                        </a>
                        <a href="#how" class="mu-btn mu-btn-ghost">
                            How it works
                            {!! $muDown !!}
                        </a>
                    </div>
                    <x-marketing.example-link subdomain="battleofthebands" ink="var(--mu-ink-2)" class="es-fade-up es-d-3">See an example act: Battle of the Bands</x-marketing.example-link>

                    <ul class="mu-stickers es-fade-up es-d-4">
                        <li class="mu-sticker mu-sticker-hot" style="--r: -4deg;">Free embed</li>
                        <li class="mu-sticker mu-sticker-line" style="--r: 2deg;">Google Calendar sync</li>
                        <li class="mu-sticker mu-sticker-ink" style="--r: -2deg;">No platform fees</li>
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

            {{-- The track list: three dates, set like the back of the sleeve --}}
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

            {{-- Genre ticker --}}
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

        {{-- 2. Three steps: how to add tour dates to your website --}}
        <section id="how" class="mu-section mu-alt" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-bins-head" style="margin-bottom: 0;">
                    <span class="mu-m mu-tag" data-reveal>Run of show</span>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Add tour dates to your website in three steps
                    </h2>
                    <p class="mu-sub" data-reveal style="--reveal-delay: 0.16s;">
                        No plugin, and no copy of your dates to keep up by hand on the site.
                    </p>
                </div>

                <div class="mu-steps" data-reveal-group="140">
                    <div class="mu-step" data-reveal>
                        <div class="mu-step-disc" style="--bg: #e8472c; --fg: #15120f;" aria-hidden="true">
                            <svg viewBox="0 0 200 200"><defs><path id="mu-step-1" d="M100,100 m-49,0 a49,49 0 1,1 98,0 a49,49 0 1,1 -98,0" /></defs><text><textPath href="#mu-step-1">Doors &#183; Doors &#183; Doors &#183; Doors &#183; Doors &#183;</textPath></text></svg>
                            <b>01</b>
                        </div>
                        <h3 class="mu-d">Add your shows</h3>
                        <p>Sign up as a performer and name your schedule. Add each show with its date, venue and ticket link, or connect the Google Calendar you book gigs in and let them come in by themselves.</p>
                    </div>
                    <div class="mu-step" data-reveal>
                        <div class="mu-step-disc" style="--bg: #e9b23a; --fg: #15120f;" aria-hidden="true">
                            <svg viewBox="0 0 200 200"><defs><path id="mu-step-2" d="M100,100 m-49,0 a49,49 0 1,1 98,0 a49,49 0 1,1 -98,0" /></defs><text><textPath href="#mu-step-2">Soundcheck &#183; Soundcheck &#183; Soundcheck &#183;</textPath></text></svg>
                            <b>02</b>
                        </div>
                        <h3 class="mu-d">Paste the embed</h3>
                        <p>Choose Embed Schedule from your schedule's Actions menu, pick the List or Calendar layout and a light or dark theme, and paste the code into any site that takes an HTML block. Free on every plan.</p>
                    </div>
                    <div class="mu-step" data-reveal>
                        <div class="mu-step-disc" style="--bg: #f2ead9; --fg: #15120f;" aria-hidden="true">
                            <svg viewBox="0 0 200 200"><defs><path id="mu-step-3" d="M100,100 m-49,0 a49,49 0 1,1 98,0 a49,49 0 1,1 -98,0" /></defs><text><textPath href="#mu-step-3">Showtime &#183; Showtime &#183; Showtime &#183;</textPath></text></svg>
                            <b>03</b>
                        </div>
                        <h3 class="mu-d">Share one link</h3>
                        <p>Put your-band.eventschedule.com in your Instagram and Bandcamp bios, your EPK and your email signature. A new date appears on the page, the embed and the link the moment you publish it.</p>
                    </div>
                </div>

                <p class="mu-sub mu-inline" style="margin: 2.5rem auto 0; text-align: center; font-size: 1rem;" data-reveal>
                    The user guide covers <x-link href="{{ marketing_url('/docs/sharing#embed') }}">getting the embed code</x-link> and <x-link href="{{ marketing_url('/docs/creating-schedules#integrations-google') }}">connecting Google Calendar</x-link>.
                </p>
            </div>
        </section>

        {{-- 3. The track listing: what each line of the page says --}}
        <section id="each-line" class="mu-section" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-disco-head">
                    <div><span class="mu-m mu-tag" data-reveal>The track listing</span></div>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        What an upcoming shows page has to say
                    </h2>
                    <p class="mu-sub" data-reveal style="--reveal-delay: 0.16s;">
                        A fan opens your page looking for one city. Each line has to tell them when, where, who else is playing, how to get in, and whether there is still room.
                    </p>
                </div>

                <ol class="mu-credits" data-reveal-group="70">
                    <li class="mu-credit" data-reveal>
                        <span class="mu-m" aria-hidden="true">01</span>
                        <h3 class="mu-d">Date, doors and set times</h3>
                        <div>
                            <p>The date and start time go on the event. Doors, support and your set go on its Agenda tab as parts, free on every plan, and show on the event page in that order. A weekly residency is one recurring event, with the weeks you skip left out.</p>
                        </div>
                    </li>
                    <li class="mu-credit" data-reveal>
                        <span class="mu-m" aria-hidden="true">02</span>
                        <h3 class="mu-d">City and venue</h3>
                        <div>
                            <p>Pick the venue, or type a new one with its address. Each show gets its own page at its own address, so you can send Glasgow the Glasgow link and its preview uses that night's flyer.</p>
                        </div>
                    </li>
                    <li class="mu-credit" data-reveal>
                        <span class="mu-m" aria-hidden="true">03</span>
                        <h3 class="mu-d">The way in</h3>
                        <div>
                            <p>On the event's Tickets tab, choose Tickets elsewhere to link to the venue's or promoter's ticket page with the price beside it, take free registrations, or sell your own tickets. <x-link href="{{ marketing_url('/docs/tickets#external') }}">How Tickets elsewhere works</x-link></p>
                        </div>
                    </li>
                    <li class="mu-credit" data-reveal>
                        <span class="mu-m" aria-hidden="true">04</span>
                        <h3 class="mu-d">Every act on the bill</h3>
                        <div>
                            <p>The opener's name sells tickets in the opener's town. Add every act on the Participants tab: each one with a schedule here is linked, and one that is not gets a page it can claim. <x-link href="{{ marketing_url('/features/lineup') }}">More on lineups</x-link></p>
                        </div>
                    </li>
                    <li class="mu-credit" data-reveal>
                        <span class="mu-m" aria-hidden="true">05</span>
                        <h3 class="mu-d">Sold out, cancelled, over</h3>
                        <div>
                            <p>Sell through Event Schedule with a quantity on each ticket type and the show says Sold Out by itself. Cancel a show from its Actions menu rather than deleting it, so the page says so. Switch on <x-link href="{{ marketing_url('/docs/schedule-styling#hide-past-events') }}">Hide Past Events</x-link> and last month's shows leave the public page on their own.</p>
                        </div>
                    </li>
                </ol>
            </div>
        </section>

        {{-- 4. Side A: venues, tickets and fans --}}
        <section id="features" class="mu-section mu-alt" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-side-head">
                    <div>
                        <span class="mu-m mu-tag" data-reveal>On the bill</span>
                        <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.25rem;">
                            The page fills itself in
                        </h2>
                    </div>
                    <div class="mu-d mu-side-mark" aria-hidden="true" data-reveal="right">Side <b>A</b></div>
                </div>

                {{-- A1. Venues and promoters --}}
                <article class="mu-track">
                    <div class="mu-d mu-track-no" aria-hidden="true">A1</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Venues &middot; Promoters</div>
                        <h3 class="mu-d">Booked once. Listed twice.</h3>
                        <p>When a venue on Event Schedule adds you to its event, the date arrives on your <x-link href="{{ marketing_url('/docs/managing-schedules#requests') }}">Requests tab</x-link>. Accept it and the show is on your page too. It is one event on both schedules, so a changed set time changes on both.</p>
                        <p>A promoter who is not on Event Schedule can press Request to Book on your page and send you a date. The <x-link href="{{ marketing_url('/booking-requests') }}">booking form</x-link> is free, and nothing goes up until you accept it.</p>
                        <p>Did a venue list you before you joined? It made a page with your name and those dates. <x-link href="{{ marketing_url('/docs/creating-events#claim') }}">Claim it</x-link> with the email address the venue entered and it becomes your schedule, with the venues that list you still listing you.</p>
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
                                <span class="mu-m">Your page</span>
                                <i></i><i></i><i></i>
                                <b>New date</b>
                            </div>
                        </div>
                    </div>
                </article>

                {{-- A2. Tickets --}}
                <article class="mu-track">
                    <div class="mu-d mu-track-no" aria-hidden="true">A2</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Tickets</div>
                        <h3 class="mu-d">Your tickets or the venue's</h3>
                        <p>Most club shows are sold by the venue, so link to its ticket page: free on every plan. For a show of your own, sell tickets here. A ticket with a price needs Pro, the money goes to your own Stripe or PayPal account, and the platform fee is zero. Free shows take unlimited registrations on any plan.</p>
                        <p>Every ticket carries a QR code that any phone on the team scans at the door, on any plan. Selling here, but not on sale yet? Switch on the Notify me card and fans leave an email address to hear the moment it is. <x-link href="{{ marketing_url('/features/ticketing') }}">Ticketing</x-link></p>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-stub" dir="ltr">
                            <div class="mu-stub-main">
                                <div class="mu-stub-row"><span>GA TICKET x2</span><span>$50.00</span></div>
                                <div class="mu-stub-row"><span>PLATFORM FEE</span><span>{{ plan_price(0) }}</span></div>
                                <div class="mu-stub-keep">
                                    <small class="mu-m">Platform fee</small>
                                    <span class="mu-d">0%</span>
                                    <small class="mu-m">on every plan</small>
                                </div>
                            </div>
                            <div class="mu-stub-tear"><span class="mu-d">Admit one</span></div>
                        </div>
                    </div>
                </article>

                {{-- A3. Fans and the mailing list --}}
                <article class="mu-track" style="border-bottom: 2px solid var(--mu-ink);">
                    <div class="mu-d mu-track-no" aria-hidden="true">A3</div>
                    <div data-reveal>
                        <div class="mu-m mu-track-kind">Mailing list</div>
                        <h3 class="mu-d">New dates reach the inbox</h3>
                        <p>Fans sign up with their email address on your page, on a form you embed on your site, or from the QR code on your Followers tab printed for the merch table. Once they confirm, each batch of new shows you add and publish yourself reaches them as one email, with nothing for you to send: up to 25 dates in it, and never more than one every 72 hours.</p>
                        <p>That email does not count against your newsletter allowance. For the big news, write a newsletter yourself: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, each recipient counting as one. Fans who would rather not give an address can <x-link href="{{ marketing_url('/docs/sharing#calendar-feeds') }}">subscribe to your calendar</x-link> instead. <x-link href="{{ marketing_url('/docs/sharing#followers') }}">Building your list</x-link></p>
                    </div>
                    <div class="mu-prop" aria-hidden="true" data-reveal="zoom">
                        <div class="mu-cardboard mu-mail">
                            <span class="mu-m mu-prop-stamp">No algorithm</span>
                            <div class="mu-mail-from">
                                <span class="mu-mail-av">MH</span>
                                <div>
                                    <strong>The Midnight Hour</strong>
                                    <small>3 new dates</small>
                                </div>
                            </div>
                            <div class="mu-mail-body">
                                <span class="mu-m">Just announced</span>
                                <div class="mu-d">Leeds &middot; Glasgow &middot; Bristol</div>
                                <p>Tickets on each show's page</p>
                            </div>
                            <div class="mu-mail-stats">
                                <div><span class="mu-d">25</span><small class="mu-m">dates per email</small></div>
                                <div><span class="mu-d">72h</span><small class="mu-m">between emails</small></div>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        {{-- 5. The pressings: one master copy --}}
        <section id="upkeep" class="mu-section" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap">
                <div class="mu-disco-head">
                    <div><span class="mu-m mu-tag" data-reveal>The pressings</span></div>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Enter each date once
                    </h2>
                    <p class="mu-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Your dates live in several places. Typed into each by hand, one of them is always wrong. Here is which copies keep themselves current, and which still need you.
                    </p>
                </div>

                <div class="mu-run" data-reveal-group="60">
                    @php
                        // [where, kept current by itself?, how it stays current, your job]
                        $muRun = [
                            ['Your schedule page', false, 'The master copy. Every new date, change and cancellation is made here.', 'Put its link in every bio.'],
                            ['Your band website', true, 'The embed shows whatever the schedule shows. On the Free plan the copied code adds a small Powered by Event Schedule line under it; Pro removes it.', 'Paste the code into the site once.'],
                            ['Your Google Calendar', true, 'Sync to Google, from it, or both ways, free on every plan. '.(outlook_sync_enabled() ? 'Outlook and CalDAV calendars sync too.' : 'CalDAV calendars sync too.'), 'Keep gigs in a calendar of their own, not with rehearsals.'],
                            ['The venue\'s calendar', true, 'A show a venue added you to is one event on both pages.', 'Accept the request.'],
                            ['Fans\' inboxes and phones', true, 'Confirmed subscribers get new dates by email, and a calendar feed moves a changed date by itself.', 'Tell fans the sign-up exists when you post a tour.'],
                            ['Bandsintown and Songkick', false, 'Event Schedule has no connection to them, so dates are not passed either way.', 'Enter dates there too, and give them your event page as the ticket link.'],
                        ];
                    @endphp
                    @foreach ($muRun as $muRunIndex => [$muRunWhere, $muRunAuto, $muRunHow, $muRunJob])
                        <div class="mu-run-row" data-reveal>
                            <div>
                                <small class="mu-m">Copy {{ str_pad($muRunIndex + 1, 2, '0', STR_PAD_LEFT) }}</small>
                                <h3 class="mu-d">{{ $muRunWhere }}</h3>
                            </div>
                            <div>
                                <small class="mu-m {{ $muRunAuto ? 'mu-run-auto' : '' }}">{{ $muRunAuto ? 'Updates itself' : 'Kept by hand' }}</small>
                                <p>{{ $muRunHow }}</p>
                            </div>
                            <div>
                                <small class="mu-m">Your job</small>
                                <p>{{ $muRunJob }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mu-sub mu-inline" style="margin-top: 1.5rem; font-size: 1rem;" data-reveal>
                    Already listed on one of them? See how a schedule here sits beside <x-link href="{{ marketing_url('/bandsintown-alternative') }}">Bandsintown</x-link> and <x-link href="{{ marketing_url('/songkick-alternative') }}">Songkick</x-link>.
                </p>
            </div>
        </section>

        {{-- 6. The back catalogue: the feature pages behind each job --}}
        <section class="mu-section mu-alt">
            <div class="mu-wrap mu-cat-grid">
                <div>
                    <span class="mu-m mu-tag" data-reveal>Also on the bill</span>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1.25rem;">The parts, in detail</h2>
                    <p class="mu-cat-more" data-reveal style="--reveal-delay: 0.16s;">
                        <a href="{{ marketing_url('/features') }}" class="mu-more-link mu-m">
                            See all features
                            {!! $muArrow !!}
                        </a>
                    </p>
                </div>

                @php
                    $muCatalogue = [
                        ['ES-101', 'Embed calendar', 'Your dates on your own website, free', marketing_url('/features/embed-calendar')],
                        ['ES-102', 'Google Calendar sync', 'Gigs in, dates out, or both ways', marketing_url('/google-calendar')],
                        ['ES-103', 'Booking requests', 'Promoters ask you for a date', marketing_url('/booking-requests')],
                        ['ES-104', 'Newsletters', 'Write to your list for the big news', marketing_url('/features/newsletters')],
                        ['ES-105', 'Ticketing', 'Sell your own shows with zero platform fees', marketing_url('/features/ticketing')],
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

        {{-- 7. Liner notes: the questions --}}
        <section id="faq" class="mu-section mu-alt mu-notes" style="scroll-margin-top: 5rem;">
            <div class="mu-wrap mu-notes-grid">
                <div class="mu-notes-head">
                    <span class="mu-m mu-tag" data-reveal>The fine print</span>
                    <h2 class="mu-d mu-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="mu-sub" data-reveal style="--reveal-delay: 0.16s;">
                        What musicians ask before they put their dates here.
                    </p>
                </div>

                <div class="mu-qa" data-reveal>
                    @php
                        $faqs = [
                            ['q' => 'How do I add tour dates to my band website?', 'a' => 'Make a free schedule, add your shows (or connect Google Calendar), then choose Embed Schedule from the schedule\'s Actions menu and copy the iframe code into your site. WordPress, Squarespace, Wix and most site builders take it in an HTML or embed block. The embed loads live, so a date you add, move or cancel changes on your site with no further edits.'],
                            ['q' => 'Is the tour dates page free?', 'a' => 'Yes. The page, the embed, Google Calendar sync, booking requests, free registration, door scanning and the new-date emails to your subscribers are free on every plan, along with 10 newsletter emails a month. Selling a ticket with a price needs Pro, at '.plan_price($proMonthly).' a month, which also raises newsletters to 100 a month and removes the Powered by Event Schedule line from the embed. There are no platform fees on any plan.'],
                            ['q' => 'Can it sync with my Google Calendar?', 'a' => 'Yes, on every plan. In Edit Schedule, open Integrations and the Google Calendar row, pick a calendar and choose a direction: to Google, from Google, or both ways. A calendar kept for gigs alone works best, so rehearsals and holds stay private. '.(outlook_sync_enabled() ? 'Outlook and CalDAV calendars can be synced' : 'CalDAV calendars can be synced').' the same way.'],
                            ['q' => 'How do fans hear about new shows?', 'a' => 'A fan who leaves an email address on your page or your embedded sign-up form and confirms it gets one email when you publish new public shows of your own: up to 25 dates in it, and no more than one every 72 hours. It does not use your newsletter allowance. Fans who press Follow while signed in get only the newsletters you send. On eventschedule.com, reaching more than 50 people at once needs a verified phone number on your account or the schedule\'s own email settings.'],
                            ['q' => 'What happens when a venue adds me to its event?', 'a' => 'The date arrives as a request on your Requests tab, because a performer\'s schedule always reviews dates first. Accept it and the show appears on your page; it is one event on both schedules, so a change the venue makes shows on yours too. A venue you have already approved can add you without the wait.'],
                            ['q' => 'A venue listed me before I joined. Is that page mine?', 'a' => 'It can be. Naming an act who is not on Event Schedule creates a page so the name can appear on the event. It says who created it, credits each date to the schedule that added it, and stays out of search engines. Press Claim this page and sign in with the email address it carries, and it becomes your schedule. If it is not you, press This is not me.'],
                            ['q' => 'Does Event Schedule send my dates to Bandsintown, Songkick or Spotify?', 'a' => 'No. There is no connection to Bandsintown, Songkick or streaming apps, so dates are not passed either way. What it publishes is your own page, the embed and a calendar feed. If you keep listings there for their fan alerts, enter each date there too and use your event page as the ticket link, so fans land on your page.'],
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

        {{-- 8. The test pressing: sign the label --}}
        <section id="claim" class="mu-press" style="scroll-margin-top: 4rem;">
            <div class="mu-press-disc" aria-hidden="true"></div>
            <div class="mu-wrap mu-press-in">
                <span class="mu-m mu-tag" data-reveal>Encore</span>
                <h2 class="mu-d mu-press-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Your dates, on your site tonight.
                </h2>
                <p class="mu-press-sub" data-reveal style="--reveal-delay: 0.16s;">
                    Pick your link, add your next show, paste the embed. Free forever.
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
