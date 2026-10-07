<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Comedians | Mics, Sets & Tickets</x-slot>
    <x-slot name="description">One link for every mic, guest set, and headline. Sell tickets with zero fees, email fans directly, and let clubs book you onto your schedule. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Comedians</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own hands, from the fonts the app already bundles (never a CDN):
             the marker, the ballpoint, and a plain face for the paragraphs. --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Permanent Marker') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Caveat') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Karla') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Comedians"
        description="One link for every mic, guest set, and headline. Sell tickets with zero fees, email fans directly, and let clubs book you onto your schedule. Free forever."
        audience="Comedians"
        keywords="comedian schedule, comedy show calendar, stand-up comedy booking, comedy event management, free comedian scheduling, open mic tracker, comedy tour schedule, comedian link in bio" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How comedians share their show dates with Event Schedule",
        "description": "Three steps. More butts in seats.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Add your sets",
                "text": "Import from Google Calendar or add your mics, guest sets, and headlining gigs."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Share one link",
                "text": "Drop it in your bio. Fans see all your upcoming shows in one place."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Fill the room",
                "text": "Fans sign up for email and hear from you directly. No more posting into the algorithm void."
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
           For-comedians "The Notebook" styles. The page is a working
           comic's material laid out on a desk: a yellow legal pad whose
           text sits on its own rules, a set list in marker on a strip of
           gaffer tape, index cards, bar napkins, a roll of tickets, and a
           name badge under the light at the end.

           Paper keeps its own colour in both modes; only the desk goes
           dark, the way a room does when the lamp is the only light on.
           Everything is scoped under #co. The shared es-* reveal system
           (marketing.css, marketing-home.js) drives the entrances.
           ============================================================== */

        @property --co-t {
            syntax: '<integer>';
            initial-value: 0;
            inherits: true;
        }

        #co {
            /* One line of the pad. Every block on a sheet is a whole number of these,
               which is what keeps the writing on the rules. */
            --co-r: 1.75rem;
            --co-mx: 2rem;
            --co-gutter: 2.9rem;
            --co-desk: #e6e1d6;
            --co-desk-2: #dbd5c8;
            --co-desk-line: rgba(60, 45, 25, 0.14);
            --co-on-desk: #1b1a17;
            --co-on-desk-2: #4a463d;
            --co-on-desk-red: #b8261c;
            --co-pad: #fff3a8;
            --co-pad-edge: #f0e184;
            --co-rule: rgba(88, 142, 204, 0.46);
            --co-margin: rgba(214, 72, 62, 0.72);
            --co-card: #fbf8ee;
            --co-tape: #f5f2ea;
            --co-board: #b9a98a;
            --co-napkin: #fdfcf8;
            --co-ticket: #f6a877;
            --co-ink: #1b1a17;
            --co-ink-2: #3a362e;
            --co-ink-3: #57524a;
            --co-red: #b8261c;
            --co-hi: rgba(255, 132, 24, 0.5);
            --co-shadow: rgba(46, 34, 16, 0.4);
            --co-lamp: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0));
            --co-sharpie: 'Permanent Marker', 'Marker Felt', 'Comic Sans MS', cursive;
            --co-pen: 'Caveat', 'Bradley Hand', 'Segoe Script', 'Comic Sans MS', cursive;
            --co-text: 'Karla', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            position: relative;
            background-color: var(--co-desk);
            background-image:
                repeating-linear-gradient(180deg, rgba(0, 0, 0, 0) 0 11px, rgba(60, 45, 25, 0.028) 11px 12px, rgba(0, 0, 0, 0) 12px 29px, rgba(60, 45, 25, 0.02) 29px 31px, rgba(0, 0, 0, 0) 31px 47px),
                radial-gradient(90rem 46rem at 50% 0, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0) 70%);
            color: var(--co-on-desk);
            font-family: var(--co-text);
            font-size: 1.125rem;
            line-height: var(--co-r);
        }
        .dark #co {
            --co-desk: #15110d;
            --co-desk-2: #1d1812;
            --co-desk-line: rgba(241, 233, 214, 0.12);
            --co-on-desk: #f1e9d6;
            --co-on-desk-2: #cbc1ab;
            --co-on-desk-red: #ff8370;
            --co-pad: #ead98a;
            --co-pad-edge: #cfbe6c;
            --co-rule: rgba(64, 110, 170, 0.44);
            --co-margin: rgba(176, 44, 36, 0.7);
            --co-card: #e9e4d4;
            --co-tape: #e4dfd2;
            --co-board: #a39474;
            --co-napkin: #e8e5da;
            --co-ticket: #e0976a;
            --co-ink-3: #4b473e;
            --co-red: #9a1c13;
            --co-shadow: rgba(0, 0, 0, 0.7);
            --co-lamp: radial-gradient(56rem 30rem at 34% 0, rgba(255, 250, 214, 0.34), rgba(255, 250, 214, 0) 72%);
            background-image:
                repeating-linear-gradient(180deg, rgba(0, 0, 0, 0) 0 11px, rgba(255, 220, 160, 0.022) 11px 12px, rgba(0, 0, 0, 0) 12px 29px, rgba(255, 220, 160, 0.016) 29px 31px, rgba(0, 0, 0, 0) 31px 47px),
                radial-gradient(70rem 44rem at 50% 6rem, rgba(255, 214, 140, 0.16), rgba(255, 214, 140, 0) 70%);
        }
        @media (min-width: 760px) {
            #co { --co-mx: 4.75rem; --co-gutter: 6.25rem; }
        }

        /* The bar above takes the desk, so the page reads as one surface. */
        body > header.sticky {
            background-color: rgba(230, 225, 214, 0.88);
            border-bottom-color: rgba(60, 45, 25, 0.16);
        }
        .dark body > header.sticky {
            background-color: rgba(21, 17, 13, 0.88);
            border-bottom-color: rgba(241, 233, 214, 0.12);
        }

        #co ::selection { background: #ff9a3c; color: #1b1a17; }
        #co a:focus-visible,
        #co summary:focus-visible,
        #co input:focus-visible {
            outline: 3px solid var(--co-red);
            outline-offset: 3px;
        }
        #co .co-on-dark a:focus-visible,
        #co .co-on-dark input:focus-visible { outline-color: #ffd27a; }

        /* The hands. */
        .co-sharpie { font-family: var(--co-sharpie); font-weight: 400; letter-spacing: 0.01em; }
        .co-pen { font-family: var(--co-pen); font-weight: 700; font-size: 1.55rem; line-height: var(--co-r); }
        .co-redink { color: var(--co-red); }
        .co-wavy {
            text-decoration: underline wavy var(--co-red);
            text-decoration-thickness: 0.07em;
            text-underline-offset: 0.16em;
            text-decoration-skip-ink: none;
        }
        .co-hl {
            padding: 0 0.14em;
            margin: 0 -0.14em;
            border-radius: 0.2em 0.5em 0.3em 0.4em;
            background: linear-gradient(100deg, rgba(255, 132, 24, 0) 1%, var(--co-hi) 4% 96%, rgba(255, 132, 24, 0) 99%);
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
        .co-circled { position: relative; display: inline-block; padding-inline: 0.3em; }
        .co-circled::after {
            content: "";
            position: absolute;
            inset: -0.08em -0.2em -0.14em;
            border: solid var(--co-red);
            border-width: 0.07em 0.1em 0.09em 0.08em;
            border-radius: 52% 48% 50% 50% / 58% 62% 42% 44%;
            rotate: -3deg;
            pointer-events: none;
        }
        .co-struck { text-decoration: line-through; text-decoration-color: var(--co-red); text-decoration-thickness: 0.09em; opacity: 0.62; }

        /* Tally marks: four strokes and the gate across them. */
        .co-tally { position: relative; display: inline-flex; align-items: flex-end; gap: 0.3em; height: 1em; margin-inline: 0.2em 0.45em; vertical-align: -0.1em; }
        .co-tally i { width: 0.11em; height: 100%; border-radius: 0.08em; background: currentColor; rotate: 3deg; }
        .co-tally i:nth-child(2) { height: 92%; rotate: -2deg; }
        .co-tally i:nth-child(3) { rotate: 5deg; }
        .co-tally i:nth-child(4) { height: 95%; rotate: -1deg; }
        .co-tally b { position: absolute; left: -0.22em; right: -0.22em; top: 46%; height: 0.11em; border-radius: 0.08em; background: currentColor; rotate: -24deg; }

        .co-arrow { display: inline-block; width: 2.2rem; height: 1.1rem; vertical-align: -0.1em; }
        .co-arrow-down { width: 1.1rem; height: 1.8rem; }

        .co-wrap { width: min(100% - 2rem, 62rem); margin-inline: auto; }
        .co-part { padding-block: clamp(3.5rem, 7vw, 6rem); }
        .co-kick { display: block; color: var(--co-on-desk-red); rotate: -2deg; transform-origin: 0 50%; }
        .co-h2 { font-size: clamp(2.2rem, 5.4vw, 3.7rem); line-height: 1.12; text-wrap: balance; }
        .co-h2 .co-redink { color: var(--co-on-desk-red); }
        .co-on-paper .co-h2 .co-redink { color: var(--co-red); }
        .co-on-paper .co-kick { color: var(--co-red); }
        .co-sub { max-width: 38rem; margin-top: 1rem; color: var(--co-on-desk-2); font-size: 1.2rem; }
        .co-head { margin-bottom: clamp(2.25rem, 5vw, 3.5rem); }
        .co-head-mid { text-align: center; }
        .co-head-mid .co-sub { margin-inline: auto; }
        .co-head-mid .co-kick { transform-origin: 50% 50%; }
        .co-h2 > .co-break { display: block; }

        /* ---------------------------------------------------------------
           Paper. A sheet of the pad: feint rules every line, the red
           double margin, and whatever is written on it.
           --------------------------------------------------------------- */
        .co-page { position: relative; isolation: isolate; width: min(100% - 1.5rem, 62rem); margin-inline: auto; }
        .co-page::before {
            content: "";
            position: absolute;
            inset: 1.4rem 0.75rem -0.6rem;
            z-index: -2;
            background: var(--co-shadow);
            filter: blur(20px);
        }
        .co-page::after {
            content: "";
            position: absolute;
            inset: 0.4rem -0.2rem -0.35rem 0.25rem;
            z-index: -1;
            background: var(--co-pad-edge);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.22);
            rotate: 0.5deg;
        }
        .co-sheet {
            position: relative;
            container-type: inline-size;
            padding: calc(var(--co-r) * 2) clamp(1rem, 4vw, 3rem) calc(var(--co-r) * 2) var(--co-gutter);
            background-color: var(--co-pad);
            background-image:
                linear-gradient(90deg,
                    rgba(0, 0, 0, 0) calc(var(--co-mx) - 4px),
                    var(--co-margin) calc(var(--co-mx) - 4px) calc(var(--co-mx) - 2.5px),
                    rgba(0, 0, 0, 0) calc(var(--co-mx) - 2.5px) var(--co-mx),
                    var(--co-margin) var(--co-mx) calc(var(--co-mx) + 1.5px),
                    rgba(0, 0, 0, 0) calc(var(--co-mx) + 1.5px)),
                repeating-linear-gradient(180deg,
                    rgba(0, 0, 0, 0) 0 calc(var(--co-r) - 6px),
                    var(--co-rule) calc(var(--co-r) - 6px) calc(var(--co-r) - 5px),
                    rgba(0, 0, 0, 0) calc(var(--co-r) - 5px) var(--co-r)),
                var(--co-lamp);
            color: var(--co-ink);
        }
        /* A page pulled off the pad: the perforation leaves a nibbled edge. */
        .co-torn::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: conic-gradient(from -45deg at 50% 100%, var(--co-desk) 90deg, rgba(0, 0, 0, 0) 0) 0 0 / 9px 4px repeat-x;
        }
        .co-sheet p { color: var(--co-ink-2); }
        .co-sheet p a,
        .co-napkin p a {
            color: var(--co-ink);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: var(--co-red);
            text-decoration-thickness: 2px;
            text-underline-offset: 0.18em;
        }
        .co-sheet p a:hover,
        .co-napkin p a:hover { color: var(--co-red); }
        .co-flow > * + * { margin-top: var(--co-r); }

        /* Notes in the margin, in red ballpoint. Decoration only. */
        .co-scribble { display: none; }
        @media (min-width: 760px) {
            .co-scribble {
                display: block;
                position: absolute;
                left: 0.3rem;
                width: calc(var(--co-mx) - 0.7rem);
                top: var(--y, 0);
                font-family: var(--co-pen);
                font-weight: 700;
                font-size: 1.2rem;
                line-height: 1.02;
                text-align: center;
                color: var(--co-red);
                rotate: var(--a, -7deg);
            }
        }

        /* Gaffer tape. Black for the thing you press, white for labels. */
        .co-gaff {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: calc(var(--co-r) * 2);
            padding: 0 1.6rem;
            background-color: #1b1a17;
            background-image: repeating-linear-gradient(0deg, rgba(0, 0, 0, 0) 0 2px, rgba(255, 255, 255, 0.05) 2px 3px), repeating-linear-gradient(90deg, rgba(0, 0, 0, 0) 0 2px, rgba(255, 255, 255, 0.04) 2px 3px);
            color: #f7f1e3;
            font-family: var(--co-sharpie);
            font-size: 1.3rem;
            line-height: 1;
            white-space: nowrap;
            rotate: var(--a, -1.2deg);
            box-shadow: 0 0.5rem 0.8rem -0.45rem rgba(20, 14, 6, 0.75);
            transition: rotate 0.25s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.25s ease, box-shadow 0.25s ease;
        }
        .co-gaff::before,
        .co-gaff::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            width: 7px;
            background: inherit;
        }
        .co-gaff::before {
            right: 100%;
            -webkit-mask: conic-gradient(from 45deg at 0 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 0 / 100% 14px repeat-y;
            mask: conic-gradient(from 45deg at 0 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 0 / 100% 14px repeat-y;
        }
        .co-gaff::after {
            left: 100%;
            -webkit-mask: conic-gradient(from 225deg at 100% 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 5px / 100% 14px repeat-y;
            mask: conic-gradient(from 225deg at 100% 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 5px / 100% 14px repeat-y;
        }
        .co-gaff:hover { rotate: 0deg; translate: 0 -3px; box-shadow: 0 0.9rem 1.1rem -0.5rem rgba(20, 14, 6, 0.75); }
        .co-gaff svg { width: 1.9rem; height: 0.95rem; transition: translate 0.2s ease; }
        .co-gaff:hover svg { translate: 4px 0; }
        .co-gaff-white {
            background-color: var(--co-tape);
            background-image: repeating-linear-gradient(0deg, rgba(0, 0, 0, 0) 0 2px, rgba(0, 0, 0, 0.04) 2px 3px), repeating-linear-gradient(90deg, rgba(0, 0, 0, 0) 0 2px, rgba(0, 0, 0, 0.035) 2px 3px);
            color: #1b1a17;
        }
        .co-gaff-white svg { width: 0.95rem; height: 1.5rem; }
        .co-gaff-white:hover svg { translate: 0 3px; }

        .co-tape {
            position: relative;
            background-color: var(--co-tape);
            background-image: repeating-linear-gradient(0deg, rgba(0, 0, 0, 0) 0 2px, rgba(0, 0, 0, 0.04) 2px 3px), repeating-linear-gradient(90deg, rgba(0, 0, 0, 0) 0 2px, rgba(0, 0, 0, 0.035) 2px 3px);
            color: var(--co-ink);
            box-shadow: 0 0.9rem 1.4rem -0.8rem var(--co-shadow);
        }

        /* Index cards: ruled, with the red line under the heading. */
        .co-card {
            --co-cr: 1.5rem;
            position: relative;
            padding: 0 1.15rem var(--co-cr);
            border-radius: 2px;
            background-color: var(--co-card);
            background-image:
                linear-gradient(180deg, rgba(0, 0, 0, 0) calc(var(--co-cr) * 2 + 2px), var(--co-margin) calc(var(--co-cr) * 2 + 2px) calc(var(--co-cr) * 2 + 4px), rgba(0, 0, 0, 0) calc(var(--co-cr) * 2 + 4px)),
                repeating-linear-gradient(180deg, rgba(0, 0, 0, 0) 0 calc(var(--co-cr) - 4px), var(--co-rule) calc(var(--co-cr) - 4px) calc(var(--co-cr) - 3px), rgba(0, 0, 0, 0) calc(var(--co-cr) - 3px) var(--co-cr));
            color: var(--co-ink);
            font-size: 1rem;
            line-height: var(--co-cr);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.14), 0 1rem 1.4rem -0.9rem var(--co-shadow);
        }
        .co-card-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 0.75rem; height: calc(var(--co-cr) * 2); padding-bottom: 0.2rem; font-size: 1.35rem; line-height: 1.2; }
        .co-card-head .co-pen { font-size: 1.35rem; line-height: 1.2; color: var(--co-red); }
        .co-card-head-long { font-size: min(1.3rem, 4.4vw); white-space: nowrap; }

        /* ---------------------------------------------------------------
           1. Hero: the top sheet of the pad
           --------------------------------------------------------------- */
        .co-hero { position: relative; padding-block: clamp(1.75rem, 4vw, 3.25rem) clamp(3rem, 6vw, 5rem); }
        .co-binding {
            position: relative;
            height: 2.5rem;
            background-color: #6c1b17;
            background-image: repeating-linear-gradient(90deg, rgba(0, 0, 0, 0) 0 3px, rgba(0, 0, 0, 0.12) 3px 4px), linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0) 45%, rgba(0, 0, 0, 0.22));
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.3);
            z-index: 1;
        }
        .co-binding + .co-sheet { padding-top: calc(var(--co-r) * 2); border-top: 2px dashed rgba(120, 100, 30, 0.45); }
        .co-hero .co-sheet { padding-bottom: calc(var(--co-r) * 3); }
        .co-eyebrow {
            display: inline-block;
            font-family: var(--co-pen);
            font-weight: 700;
            font-size: 1.7rem;
            line-height: var(--co-r);
            color: var(--co-red);
            text-decoration: underline;
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
            rotate: -1.4deg;
            transform-origin: 0 50%;
        }
        .co-hero .es-hero-eyebrow { margin-bottom: var(--co-r); line-height: var(--co-r); }
        .co-h1 { font-size: clamp(2.5rem, 9.4cqi, 5.25rem); line-height: calc(var(--co-r) * 2); text-wrap: balance; }
        @container (min-width: 40rem) {
            .co-h1 { line-height: calc(var(--co-r) * 3); }
        }
        .co-h1 > span { display: block; }
        /* Written on, left to right, the way a marker crosses a page. */
        html.es-anim #co .co-write { animation: co-write 0.9s cubic-bezier(0.3, 0.6, 0.3, 1) 0.15s both; }
        html.es-anim #co .co-write-2 { animation-delay: 0.85s; }
        @keyframes co-write {
            from { clip-path: inset(-20% 100% -20% -2%); }
            to { clip-path: inset(-20% -3% -20% -2%); }
        }
        .co-lede { max-width: 36rem; margin-top: var(--co-r); font-size: 1.2rem; }
        .co-cta { display: flex; flex-wrap: wrap; align-items: center; gap: var(--co-r) 2rem; margin-top: var(--co-r); padding-inline: 7px; min-height: calc(var(--co-r) * 2); }
        .co-ladder { display: flex; flex-wrap: wrap; align-items: baseline; column-gap: 0.5rem; margin-top: var(--co-r); color: var(--co-ink-2); }
        .co-ladder li { white-space: nowrap; }
        .co-ladder li + li::before { content: "\2192"; margin-inline-end: 0.5rem; font-family: var(--co-text); font-size: 1.1rem; color: var(--co-red); }
        .co-ladder li:last-child { color: var(--co-ink); }
        .co-date-note { position: absolute; top: calc(var(--co-r) * 2); right: clamp(1rem, 4vw, 3rem); color: var(--co-ink-3); rotate: 1.5deg; }
        @media (max-width: 759px) { .co-date-note { display: none; } }

        .co-doodle { display: none; }
        @container (min-width: 46rem) {
            .co-doodle {
                display: grid;
                justify-items: end;
                position: absolute;
                right: clamp(1rem, 4vw, 3rem);
                top: calc(var(--co-r) * 8);
                color: var(--co-ink-2);
                text-align: right;
                rotate: 2deg;
            }
            .co-doodle-tally { height: calc(var(--co-r) * 2); font-size: 2.6rem; line-height: calc(var(--co-r) * 2); color: var(--co-ink); }
            .co-doodle-one { color: var(--co-red); font-size: 2.1rem; }
        }
        .co-curl {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 3.4rem;
            height: 3.4rem;
            background: linear-gradient(315deg, var(--co-pad-edge) 0 49.2%, rgba(120, 100, 30, 0.5) 49.6%, #e9d87a 53%, #fffce0 78%, #fff6b8 100%);
            box-shadow: -4px -4px 7px -3px rgba(60, 45, 10, 0.35);
        }

        /* The coffee ring: somebody put the cup down on the good page. */
        .co-ring {
            position: absolute;
            right: 16%;
            bottom: calc(var(--co-r) * 1.2);
            width: clamp(7rem, 15cqi, 10.5rem);
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                radial-gradient(closest-side, rgba(0, 0, 0, 0) 80%, rgba(110, 64, 20, 0.34) 84%, rgba(110, 64, 20, 0.5) 88%, rgba(110, 64, 20, 0.2) 92%, rgba(0, 0, 0, 0) 96%),
                radial-gradient(closest-side at 46% 52%, rgba(110, 64, 20, 0.07) 76%, rgba(0, 0, 0, 0) 86%);
            -webkit-mask: conic-gradient(from 20deg, #000 0 31%, rgba(0, 0, 0, 0.35) 36% 41%, #000 46% 78%, rgba(0, 0, 0, 0.15) 83% 88%, #000 93%);
            mask: conic-gradient(from 20deg, #000 0 31%, rgba(0, 0, 0, 0.35) 36% 41%, #000 46% 78%, rgba(0, 0, 0, 0.15) 83% 88%, #000 93%);
            mix-blend-mode: multiply;
            pointer-events: none;
        }
        @media (max-width: 759px) { .co-ring { display: none; } }

        /* The marker itself, left on the desk beside the pad. */
        .co-marker { display: none; }
        @media (min-width: 1280px) {
            .co-marker {
                display: flex;
                position: absolute;
                top: 46%;
                left: calc(50% + 33rem);
                width: 15rem;
                height: 1.75rem;
                rotate: 74deg;
                transform-origin: 0 50%;
                filter: drop-shadow(0.5rem 0.6rem 0.5rem var(--co-shadow));
            }
            .co-marker i { display: block; height: 100%; }
            .co-marker-cap { width: 30%; border-radius: 0.5rem 0.2rem 0.2rem 0.5rem; background: linear-gradient(180deg, #4b4b50, #1c1c1f 38%, #0c0c0e); box-shadow: inset -0.5rem 0 0 -0.35rem rgba(255, 255, 255, 0.18); }
            .co-marker-body {
                flex: 1;
                display: grid !important;
                place-items: center;
                background: linear-gradient(180deg, #d8d8dc, #f4f4f6 30%, #9c9ca3 78%, #6f6f77);
                font-family: var(--co-text);
                font-weight: 700;
                font-size: 0.6rem;
                letter-spacing: 0.3em;
                font-style: normal;
                color: #26262a;
            }
            .co-marker-end { width: 9%; border-radius: 0.15rem 0.5rem 0.5rem 0.15rem; background: linear-gradient(180deg, #4b4b50, #1c1c1f 38%, #0c0c0e); }
        }

        /* ---------------------------------------------------------------
           2. The grind: tallied on the cardboard back of the pad
           --------------------------------------------------------------- */
        .co-board-page::after { background: #8f8064; }
        .co-board {
            position: relative;
            padding: clamp(2.25rem, 5vw, 3.75rem) clamp(1.25rem, 5vw, 3.75rem);
            background-color: var(--co-board);
            background-image:
                radial-gradient(rgba(0, 0, 0, 0) 55%, rgba(60, 44, 16, 0.14)),
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3CfeColorMatrix values='0 0 0 0 0.2 0 0 0 0 0.15 0 0 0 0 0.08 0 0 0 0.5 0'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.5'/%3E%3C/svg%3E");
            color: #1b1a17;
            text-align: center;
        }
        .co-board .co-kick { color: #1b1a17; transform-origin: 50% 50%; }
        .co-board .co-h2 { margin-top: 0.5rem; }
        .co-board .co-wavy { text-decoration-color: #7d130c; }
        .co-stats { display: grid; gap: 2.25rem 1.5rem; margin-top: clamp(2.25rem, 5vw, 3.5rem); }
        @media (min-width: 760px) { .co-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .co-stat-n { display: flex; align-items: center; justify-content: center; gap: 0.25em; font-size: clamp(3.6rem, 8vw, 5.4rem); line-height: 1; }
        .co-stat-n .co-tally { font-size: 0.62em; opacity: 0.85; }
        .co-stat p { max-width: 15rem; margin: 0.9rem auto 0; color: #2a2620; font-size: 1.15rem; font-weight: 700; line-height: 1.4; }
        .co-board-foot { margin-top: clamp(2.25rem, 5vw, 3.25rem); color: #2a2620; font-weight: 700; }
        .co-board-foot a { display: inline-flex; align-items: center; gap: 0.4rem; margin-inline-start: 0.3rem; color: #1b1a17; text-decoration: underline wavy #7d130c; text-decoration-thickness: 2px; text-underline-offset: 0.22em; }
        .co-board-foot a svg { width: 0.85rem; height: 1.3rem; transition: translate 0.2s ease; }
        .co-board-foot a:hover svg { translate: 0 3px; }
        #co .co-board a:focus-visible { outline-color: #1b1a17; }

        /* ---------------------------------------------------------------
           3. Tonight's set: marker on a strip of white gaffer tape
           --------------------------------------------------------------- */
        .co-set-grid { display: grid; gap: 3rem; align-items: center; }
        @media (min-width: 980px) { .co-set-grid { grid-template-columns: minmax(0, 0.82fr) minmax(0, 1.18fr); gap: 4rem; } }
        .co-set-grid .co-kick { margin-bottom: 0.4rem; }
        .co-allegedly { display: inline-block; rotate: -4deg; }
        .co-setlist { padding: 1.5rem clamp(1rem, 3.4vw, 2.1rem) 1.6rem; rotate: 1.1deg; }
        .co-setlist::before,
        .co-setlist::after { content: ""; position: absolute; left: 0; right: 0; height: 7px; background: inherit; }
        .co-setlist::before {
            bottom: 100%;
            -webkit-mask: conic-gradient(from 135deg at 50% 0, #000 90deg, rgba(0, 0, 0, 0) 0) 0 0 / 14px 100% repeat-x;
            mask: conic-gradient(from 135deg at 50% 0, #000 90deg, rgba(0, 0, 0, 0) 0) 0 0 / 14px 100% repeat-x;
        }
        .co-setlist::after {
            top: 100%;
            -webkit-mask: conic-gradient(from -45deg at 50% 100%, #000 90deg, rgba(0, 0, 0, 0) 0) 5px 0 / 14px 100% repeat-x;
            mask: conic-gradient(from -45deg at 50% 100%, #000 90deg, rgba(0, 0, 0, 0) 0) 5px 0 / 14px 100% repeat-x;
        }
        .co-setlist a {
            display: grid;
            grid-template-columns: 2.4rem minmax(0, 1fr) auto;
            align-items: baseline;
            gap: 0.6rem;
            padding: 0.42rem 0.3rem 0.3rem;
            font-family: var(--co-sharpie);
            font-size: clamp(1.2rem, 3.6vw, 1.6rem);
            line-height: 1.25;
            transition: translate 0.2s ease;
        }
        .co-setlist a:hover { translate: 0.35rem 0; }
        .co-setlist a:hover .co-set-name { text-decoration: underline wavy var(--co-red); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        .co-set-no { color: var(--co-ink-3); }
        .co-set-time { font-size: 1.5rem; line-height: 1; color: var(--co-red); white-space: nowrap; }
        .co-set-time-long { font-size: 1.3rem; }
        @media (max-width: 520px) {
            .co-setlist a { grid-template-columns: 2rem minmax(0, 1fr); }
            .co-set-time { grid-column: 2; }
        }
        .co-set-total { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-top: 0.9rem; padding: 0.85rem 0.3rem 0; border-top: 3px solid var(--co-ink); font-family: var(--co-sharpie); font-size: 1.15rem; }
        .co-set-total b { font-weight: 400; font-size: 1.7rem; }
        .co-set-tag { margin-top: 0.35rem; padding-inline: 0.3rem; color: var(--co-red); font-size: 1.45rem; line-height: 1.2; }

        /* ---------------------------------------------------------------
           4. The five bits: a page of the pad each
           --------------------------------------------------------------- */
        .co-bit + .co-bit { margin-top: clamp(2.5rem, 5vw, 4rem); }
        html.es-anim #co [data-reveal="flip"]:not(.is-revealed) {
            transform: perspective(80rem) rotateX(-24deg) translateY(1.5rem);
        }
        #co [data-reveal="flip"] { transform-origin: 50% 0; }
        .co-bit-no { font-size: 2.6rem; line-height: calc(var(--co-r) * 2); color: var(--co-red); }
        .co-bit-min { color: var(--co-red); }
        @media (min-width: 760px) {
            .co-sheet > .co-bit-no { position: absolute; left: 0; top: calc(var(--co-r) * 2); width: calc(var(--co-mx) - 5px); text-align: center; rotate: -5deg; }
        }
        .co-bit-grid { display: grid; gap: calc(var(--co-r) * 2) 2.5rem; align-items: start; }
        @media (min-width: 900px) {
            .co-bit-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 21rem); }
            .co-bit-obj { position: sticky; top: 6rem; margin-inline-end: calc(clamp(1rem, 4vw, 3rem) * -1 + 0.75rem); padding-top: var(--co-r); }
        }
        /* Wide enough for the card to hang off the page onto the desk. */
        @media (min-width: 1120px) {
            .co-bit-obj { margin-inline-end: calc(clamp(1rem, 4vw, 3rem) * -1 - 2.25rem); }
        }
        .co-setup { color: var(--co-ink); }
        #co .co-beat { color: var(--co-ink-3); }
        .co-bit h3 { font-size: clamp(1.9rem, 5.6cqi, 2.7rem); line-height: calc(var(--co-r) * 2); text-wrap: balance; }
        .co-checks li { position: relative; padding-inline-start: 1.9rem; color: var(--co-ink-2); }
        .co-checks li::before { content: "\2713"; position: absolute; left: 0.1rem; top: -0.08rem; font-family: var(--co-pen); font-weight: 700; font-size: 1.8rem; color: var(--co-red); rotate: -6deg; }
        #co .co-aside { color: var(--co-red); }
        .co-bit-obj { min-width: 0; }
        .co-bit-obj > * { max-width: 23rem; margin-inline: auto; }
        .co-tilt-l { rotate: -2.2deg; }
        .co-tilt-r { rotate: 2deg; }

        /* Bit 1: the week, on a card */
        .co-week-row { display: grid; grid-template-columns: 3rem minmax(0, 1fr); align-items: center; gap: 0.6rem; height: calc(var(--co-cr) * 2); }
        .co-day { font-family: var(--co-sharpie); font-size: 1.25rem; line-height: 1; text-align: center; }
        .co-day small { display: block; margin-bottom: 0.2rem; font-family: var(--co-text); font-weight: 700; font-size: 0.62rem; letter-spacing: 0.12em; color: var(--co-ink-3); }
        .co-week-row strong { display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem; height: var(--co-cr); line-height: var(--co-cr); white-space: nowrap; }
        /* Sized from the sheet the card sits on (the cqi term only bites under 375px), so the longest line still fits a 360px phone. */
        .co-week-row em { display: block; font-style: normal; font-size: min(0.9rem, 7.5cqi - 7.2px); line-height: var(--co-cr); color: var(--co-ink-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .co-week-row .co-pen { font-size: 1.3rem; line-height: 1; color: var(--co-red); white-space: nowrap; }

        /* Bit 2: the door sheet, clipped to a ticket */
        .co-stub {
            position: relative;
            padding: 1.2rem 1.25rem 1.1rem;
            background-color: var(--co-card);
            color: var(--co-ink);
            font-size: 1rem;
            line-height: 1.5;
            -webkit-mask: radial-gradient(circle 0.6rem at 0 4.1rem, rgba(0, 0, 0, 0) 97%, #000) 0 0 / 51% 100% no-repeat, radial-gradient(circle 0.6rem at 100% 4.1rem, rgba(0, 0, 0, 0) 97%, #000) 100% 0 / 51% 100% no-repeat;
            mask: radial-gradient(circle 0.6rem at 0 4.1rem, rgba(0, 0, 0, 0) 97%, #000) 0 0 / 51% 100% no-repeat, radial-gradient(circle 0.6rem at 100% 4.1rem, rgba(0, 0, 0, 0) 97%, #000) 100% 0 / 51% 100% no-repeat;
        }
        .co-stub-wrap { filter: drop-shadow(0 0.8rem 0.7rem var(--co-shadow)); }
        .co-stub-top { display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; height: 2.9rem; margin-bottom: 1.2rem; border-bottom: 2px dashed rgba(27, 26, 23, 0.4); }
        /* One line at every width: wrapped, the second line sat on the perforation (it did at 375px and under). */
        .co-stub-top .co-sharpie { font-size: min(1.2rem, 10cqi - 11px); white-space: nowrap; }
        .co-stub-top .co-pen { font-size: 1.35rem; color: var(--co-red); white-space: nowrap; }
        .co-stub-row { display: flex; justify-content: space-between; gap: 1rem; padding-block: 0.45rem; border-bottom: 1px dotted rgba(27, 26, 23, 0.35); font-weight: 700; }
        .co-stub-fee { border-bottom: 0; padding-top: 0.75rem; }
        .co-stub-fee span:last-child { font-family: var(--co-sharpie); font-size: 1.5rem; line-height: 1; color: var(--co-red); }

        /* Bit 3: three hand stamps, and where they all point */
        .co-stamps { display: flex; align-items: center; justify-content: center; gap: 0.8rem; }
        .co-stamp {
            display: grid;
            place-items: center;
            width: 4.4rem;
            aspect-ratio: 1;
            border: 3px double var(--c);
            border-radius: 50%;
            color: var(--c);
            font-family: var(--co-sharpie);
            font-size: 1.5rem;
            rotate: var(--a, -8deg);
            -webkit-mask: radial-gradient(circle at 30% 30%, #000 40%, rgba(0, 0, 0, 0.72) 62%, #000 80%);
            mask: radial-gradient(circle at 30% 30%, #000 40%, rgba(0, 0, 0, 0.72) 62%, #000 80%);
        }
        .co-stamps .co-pen { color: var(--co-ink-2); font-size: 1.4rem; }
        .co-points { display: grid; justify-items: center; gap: 0.4rem; margin-top: 0.9rem; color: var(--co-red); }
        .co-url { padding: 0.85rem 1.1rem 0.75rem; font-weight: 700; font-size: clamp(0.92rem, 4.2cqi, 1.08rem); letter-spacing: 0.01em; text-align: center; rotate: -1.5deg; }

        /* Bit 4: every Tuesday, and the one that is off */
        .co-tues-row { display: flex; justify-content: space-between; gap: 1rem; font-weight: 700; }
        .co-tues-row span:last-child { font-family: var(--co-pen); font-size: 1.3rem; color: var(--co-ink-2); }
        .co-tues-note { color: var(--co-red); font-size: 1.3rem; line-height: var(--co-cr); }
        .co-tues-skip { color: var(--co-ink-3); }

        /* Bit 5: the postcard that went out */
        .co-post { padding: 1.2rem 1.25rem 1.1rem; background-color: var(--co-napkin); color: var(--co-ink); font-size: 1rem; line-height: 1.5; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.14), 0 1rem 1.4rem -0.9rem var(--co-shadow); }
        .co-post-top { display: flex; justify-content: space-between; gap: 1rem; }
        .co-post-top .co-sharpie { font-size: 1.55rem; line-height: 1.15; }
        .co-postage {
            flex: none;
            display: grid;
            place-items: center;
            width: 2.9rem;
            height: 3.5rem;
            background-color: #1b1a17;
            color: #fbf8ee;
            font-family: var(--co-sharpie);
            font-size: 1.05rem;
            rotate: 4deg;
            -webkit-mask: radial-gradient(circle, rgba(0, 0, 0, 0) 2px, #000 2.4px) -3.5px -3.5px / 7px 7px;
            mask: radial-gradient(circle, rgba(0, 0, 0, 0) 2px, #000 2.4px) -3.5px -3.5px / 7px 7px;
        }
        .co-post .co-pen { display: block; margin-top: 0.35rem; color: var(--co-ink-2); font-size: 1.4rem; }
        .co-post-stats { display: grid; grid-template-columns: 1fr 1fr; margin-top: 0.9rem; border-top: 2px solid var(--co-ink); }
        .co-post-stats div { padding: 0.8rem 0.2rem 0; }
        .co-post-stats div + div { padding-inline-start: 1rem; border-inline-start: 2px solid var(--co-ink); }
        .co-post-stats b { display: block; font-family: var(--co-sharpie); font-weight: 400; font-size: 2.3rem; line-height: 1; }
        .co-post-stats small { font-weight: 700; font-size: 0.8rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--co-ink-3); }

        /* ---------------------------------------------------------------
           5. Crowd work: what the room sends back, on bar napkins
           --------------------------------------------------------------- */
        .co-napkins { display: grid; gap: 2.25rem 1.75rem; }
        @media (min-width: 900px) { .co-napkins { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .co-napkin {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 2.1rem 1.6rem 1.9rem;
            background-color: var(--co-napkin);
            color: var(--co-ink);
            outline: 2px dotted rgba(27, 26, 23, 0.16);
            outline-offset: -0.85rem;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.12), 0 1.1rem 1.5rem -1rem var(--co-shadow);
            rotate: var(--a, 0deg);
            transition: rotate 0.3s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.3s ease;
        }
        .co-napkin:hover { rotate: 0deg; translate: 0 -0.35rem; }
        .co-napkin h3 { font-family: var(--co-pen); font-weight: 700; font-size: 2.1rem; line-height: 1.1; }
        .co-napkin > p { margin-top: 0.6rem; color: var(--co-ink-2); font-size: 1.0625rem; line-height: 1.55; }
        .co-napkin-obj { margin-top: auto; padding-top: 1.5rem; font-size: 0.95rem; line-height: 1.4; }
        .co-snap { display: grid; grid-template-columns: 4.2rem minmax(0, 1fr); align-items: center; gap: 0.85rem; }
        .co-snap-img { aspect-ratio: 1; border: 0.3rem solid #fff; border-bottom-width: 0.8rem; background: radial-gradient(circle at 50% 30%, #ffd07a 0 18%, rgba(0, 0, 0, 0) 19%), linear-gradient(180deg, #2a2118 0 62%, #5a2d1c 62%); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.35); rotate: -4deg; }
        .co-snap strong { display: block; }
        .co-snap small { color: var(--co-ink-3); font-size: 0.9rem; }
        .co-ok { display: flex; gap: 0.75rem; margin-top: 1rem; }
        .co-ok span { padding: 0.2rem 0.75rem 0.1rem; border: 2px solid currentColor; border-radius: 0.3rem; font-family: var(--co-sharpie); font-size: 1rem; rotate: -3deg; }
        .co-ok span:first-child { color: #1d6b34; }
        .co-ok span:last-child { color: var(--co-ink-3); rotate: 2deg; }
        .co-vote-q { font-weight: 700; }
        .co-vote { position: relative; display: flex; justify-content: space-between; gap: 1rem; margin-top: 0.55rem; padding: 0.3rem 0.5rem 0.25rem; font-weight: 700; isolation: isolate; }
        .co-vote::before { content: ""; position: absolute; inset: 0 auto 0 0; width: var(--w); z-index: -1; border-radius: 0.2em 0.6em 0.3em 0.5em; background: var(--co-hi); }
        .co-stars { font-size: 1.5rem; letter-spacing: 0.12em; color: #c9730a; line-height: 1; }
        .co-quote { margin-top: 0.5rem; font-family: var(--co-pen); font-weight: 700; font-size: 1.5rem; line-height: 1.15; color: var(--co-ink); }

        /* ---------------------------------------------------------------
           6. The journey: the years, down the margin
           --------------------------------------------------------------- */
        .co-years { margin-top: var(--co-r); }
        .co-year { position: relative; padding-bottom: var(--co-r); }
        .co-year h3 { font-family: var(--co-sharpie); font-weight: 400; font-size: 1.7rem; line-height: calc(var(--co-r) * 2); }
        .co-year-tag { display: block; color: var(--co-red); rotate: -2deg; transform-origin: 0 50%; }
        @media (min-width: 760px) {
            .co-years { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 3rem; }
        }

        /* ---------------------------------------------------------------
           7. Every kind of comedy: six index cards
           --------------------------------------------------------------- */
        .co-types { position: relative; }
        .co-cards { display: grid; gap: 2rem 1.75rem; }
        @media (min-width: 700px) { .co-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1000px) { .co-cards { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .co-type { display: flex; flex-direction: column; min-height: calc(var(--co-cr) * 8); rotate: var(--a, 0deg); transition: rotate 0.3s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.3s ease; }
        .co-type:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .co-type h3 { height: calc(var(--co-cr) * 2); padding-top: calc(var(--co-cr) * 0.72); font-family: var(--co-sharpie); font-weight: 400; font-size: 1.4rem; line-height: calc(var(--co-cr) * 1.2); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .co-type p { margin-top: var(--co-cr); color: var(--co-ink-2); font-size: 1.0625rem; line-height: var(--co-cr); }
        .co-type a { margin-top: auto; padding-top: var(--co-cr); align-self: flex-start; font-family: var(--co-pen); font-weight: 700; font-size: 1.4rem; line-height: var(--co-cr); color: var(--co-red); }
        .co-type a span { display: inline-flex; align-items: center; gap: 0.4rem; border-bottom: 2px solid currentColor; }
        .co-type a svg { width: 1.6rem; height: 0.8rem; transition: translate 0.2s ease; }
        .co-type a:hover svg { translate: 4px 0; }
        .co-order { display: none; }
        @media (min-width: 1100px) {
            .co-order { display: block; position: absolute; right: 0; top: -1.5rem; width: 13.5rem; padding: 0.9rem 1rem 1rem; font-family: var(--co-sharpie); font-size: 1rem; line-height: 1.5; rotate: 3deg; }
            .co-order div:first-child { margin-bottom: 0.2rem; font-family: var(--co-text); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.16em; text-transform: uppercase; color: var(--co-ink-3); }
            .co-order span { color: var(--co-red); }
            .co-types .co-head { max-width: 44rem; }
        }

        /* ---------------------------------------------------------------
           8. Three steps: three tickets off the roll
           --------------------------------------------------------------- */
        .co-roll { display: grid; filter: drop-shadow(0 0.9rem 0.8rem var(--co-shadow)); }
        /* Restated under the page id: the shared reveal ends on `filter: none`, which outranks a plain
           class and took the shadow away as soon as the element was revealed. */
        #co .co-roll { filter: drop-shadow(0 0.9rem 0.8rem var(--co-shadow)); }
        @media (min-width: 860px) { .co-roll { grid-template-columns: repeat(3, minmax(0, 1fr)); rotate: -0.8deg; } }
        .co-ticket {
            position: relative;
            padding: 1.5rem 1.6rem 1.6rem;
            background-color: var(--co-ticket);
            color: #1b1a17;
            text-align: center;
            -webkit-mask: radial-gradient(circle 0.55rem at 50% 0, rgba(0, 0, 0, 0) 97%, #000) 0 0 / 100% 51% no-repeat, radial-gradient(circle 0.55rem at 50% 100%, rgba(0, 0, 0, 0) 97%, #000) 0 100% / 100% 51% no-repeat;
            mask: radial-gradient(circle 0.55rem at 50% 0, rgba(0, 0, 0, 0) 97%, #000) 0 0 / 100% 51% no-repeat, radial-gradient(circle 0.55rem at 50% 100%, rgba(0, 0, 0, 0) 97%, #000) 0 100% / 100% 51% no-repeat;
        }
        .co-ticket + .co-ticket { border-top: 2px dashed rgba(27, 26, 23, 0.45); }
        @media (min-width: 860px) {
            .co-ticket {
                -webkit-mask: radial-gradient(circle 0.55rem at 0 50%, rgba(0, 0, 0, 0) 97%, #000) 0 0 / 51% 100% no-repeat, radial-gradient(circle 0.55rem at 100% 50%, rgba(0, 0, 0, 0) 97%, #000) 100% 0 / 51% 100% no-repeat;
                mask: radial-gradient(circle 0.55rem at 0 50%, rgba(0, 0, 0, 0) 97%, #000) 0 0 / 51% 100% no-repeat, radial-gradient(circle 0.55rem at 100% 50%, rgba(0, 0, 0, 0) 97%, #000) 100% 0 / 51% 100% no-repeat;
            }
            .co-ticket + .co-ticket { border-top: 0; border-inline-start: 2px dashed rgba(27, 26, 23, 0.45); }
        }
        .co-ticket-top { display: flex; justify-content: space-between; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.16em; text-transform: uppercase; color: #3a2a1c; }
        .co-ticket-n { display: grid; place-items: center; width: 4.6rem; aspect-ratio: 1; margin: 1rem auto 0.9rem; border: 3px solid #1b1a17; border-radius: 50%; font-family: var(--co-sharpie); font-size: 2.6rem; line-height: 1; }
        .co-ticket h3 { font-family: var(--co-sharpie); font-weight: 400; font-size: 1.7rem; line-height: 1.15; }
        .co-ticket p { max-width: 19rem; margin: 0.6rem auto 0; color: #2c241b; font-size: 1.0625rem; line-height: 1.5; }

        /* ---------------------------------------------------------------
           9. Key features: four labels of tape
           --------------------------------------------------------------- */
        .co-keys-grid { display: grid; gap: 2.5rem 4rem; align-items: start; }
        @media (min-width: 940px) { .co-keys-grid { grid-template-columns: minmax(0, 0.6fr) minmax(0, 1.4fr); } }
        .co-labels { display: grid; gap: 1.4rem; }
        @media (min-width: 640px) { .co-labels { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.6rem 1.75rem; } }
        .co-label {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.9rem;
            margin-inline: 7px;
            padding: 1rem 1.1rem 0.95rem;
            rotate: var(--a, 0deg);
            transition: rotate 0.3s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.3s ease;
        }
        .co-label::before,
        .co-label::after { content: ""; position: absolute; top: 0; bottom: 0; width: 7px; background: inherit; }
        .co-label::before {
            right: 100%;
            -webkit-mask: conic-gradient(from 45deg at 0 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 0 / 100% 14px repeat-y;
            mask: conic-gradient(from 45deg at 0 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 0 / 100% 14px repeat-y;
        }
        .co-label::after {
            left: 100%;
            -webkit-mask: conic-gradient(from 225deg at 100% 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 6px / 100% 14px repeat-y;
            mask: conic-gradient(from 225deg at 100% 50%, #000 90deg, rgba(0, 0, 0, 0) 0) 0 6px / 100% 14px repeat-y;
        }
        .co-label:hover { rotate: 0deg; translate: 0 -0.3rem; }
        .co-label strong { display: block; font-family: var(--co-sharpie); font-weight: 400; font-size: 1.45rem; line-height: 1.2; }
        .co-label small { display: block; margin-top: 0.2rem; color: var(--co-ink-2); font-size: 1rem; line-height: 1.4; }
        .co-label svg { width: 2rem; height: 1rem; color: var(--co-red); transition: translate 0.2s ease; }
        .co-label:hover svg { translate: 4px 0; }
        .co-more { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 1.25rem; color: var(--co-on-desk); font-size: 1.6rem; line-height: 1.2; text-decoration: underline wavy var(--co-on-desk-red); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        .co-more svg { width: 1.9rem; height: 0.95rem; color: var(--co-on-desk-red); transition: translate 0.2s ease; }
        .co-more:hover svg { translate: 4px 0; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; here they are index cards.
           --------------------------------------------------------------- */
        #co .co-plans > section { background: transparent; }
        #co .co-plans h2 { font-family: var(--co-sharpie); font-weight: 400; letter-spacing: 0.01em; line-height: 1.15; font-size: clamp(2rem, 4.6vw, 3.1rem); color: var(--co-on-desk); }
        #co .co-plans h2 + p { color: var(--co-on-desk-2); font-size: 1.125rem; }
        #co .co-plans .grid > div { border: 0; border-radius: 2px; background: var(--co-card); color: var(--co-ink); box-shadow: 0 1px 1px rgba(0, 0, 0, 0.14), 0 1.1rem 1.5rem -1rem var(--co-shadow); }
        #co .co-plans .grid > div:nth-child(1) { rotate: -0.8deg; }
        #co .co-plans .grid > div:nth-child(2) { background: var(--co-pad); rotate: 0.6deg; }
        #co .co-plans .grid > div:nth-child(3) { rotate: -0.4deg; }
        #co .co-plans .grid > div span,
        #co .co-plans .grid > div p,
        #co .co-plans .grid > div li { color: var(--co-ink-2); }
        #co .co-plans .grid > div .text-3xl { font-family: var(--co-sharpie); font-weight: 400; font-size: 2.6rem; color: var(--co-ink); }
        #co .co-plans .grid > div .uppercase { color: var(--co-ink); }
        #co .co-plans .grid > div .rounded-full { padding: 0.2rem 0.6rem 0.1rem; border: 2px solid var(--co-red); border-radius: 50% / 60%; background: transparent; color: var(--co-red); rotate: -4deg; }
        #co .co-plans .grid > div svg { color: var(--co-red); }
        #co .co-plans a.font-medium { color: var(--co-on-desk); text-decoration: underline wavy var(--co-on-desk-red); text-decoration-thickness: 2px; text-underline-offset: 0.22em; }
        #co .co-plans a.rounded-2xl { border-radius: 0; background: #1b1a17; color: #f7f1e3; box-shadow: 0 0.5rem 0.8rem -0.45rem rgba(20, 14, 6, 0.75); font-family: var(--co-sharpie); font-weight: 400; font-size: 1.2rem; rotate: -1.2deg; }
        .dark #co .co-plans a.rounded-2xl { background: var(--co-tape); color: #1b1a17; }
        #co .co-plans a.rounded-2xl:hover { transform: translateY(-3px); rotate: 0deg; }

        #co .co-keep > section { background: var(--co-desk-2); border-top: 1px solid var(--co-desk-line); }
        #co .co-keep h2 { font-family: var(--co-sharpie); font-weight: 400; font-size: clamp(1.9rem, 4vw, 2.6rem); line-height: 1.15; color: var(--co-on-desk); }
        #co .co-keep p.uppercase { font-family: var(--co-pen); font-weight: 700; font-size: 1.5rem; letter-spacing: 0; text-transform: none; color: var(--co-on-desk-red); }
        #co .co-keep .grid > a { border: 0; border-radius: 2px; background: var(--co-card); box-shadow: 0 1px 1px rgba(0, 0, 0, 0.14), 0 1rem 1.4rem -1rem var(--co-shadow); }
        #co .co-keep .grid > a > span:first-child { display: none; }
        #co .co-keep .grid > a h3 { color: var(--co-ink); }
        #co .co-keep .grid > a p { color: var(--co-ink-2); }
        #co .co-keep .grid > a > span:last-child { color: var(--co-red); }
        #co .co-keep a.self-start { color: var(--co-on-desk); }

        /* ---------------------------------------------------------------
           10. Related pages: four notes off the same pad
           --------------------------------------------------------------- */
        .co-also-head { display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between; gap: 1rem 2rem; margin-bottom: 2.25rem; }
        .co-also-head .co-more { margin-top: 0; }
        .co-notes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.5rem; }
        @media (min-width: 900px) { .co-notes { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.75rem; } }
        .co-note {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 9.5rem;
            padding: 1.1rem 1.1rem 1rem;
            background-color: var(--co-pad);
            background-image: repeating-linear-gradient(180deg, rgba(0, 0, 0, 0) 0 calc(var(--co-r) - 6px), var(--co-rule) calc(var(--co-r) - 6px) calc(var(--co-r) - 5px), rgba(0, 0, 0, 0) calc(var(--co-r) - 5px) var(--co-r));
            color: var(--co-ink);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.14), 0 1rem 1.4rem -0.9rem var(--co-shadow);
            rotate: var(--a, 0deg);
            transition: rotate 0.3s cubic-bezier(0.34, 1.5, 0.64, 1), translate 0.3s ease;
        }
        .co-note:hover { rotate: 0deg; translate: 0 -0.4rem; }
        .co-note small { font-family: var(--co-pen); font-weight: 700; font-size: clamp(1.1rem, 4.5vw, 1.3rem); line-height: var(--co-r); color: var(--co-ink-3); white-space: nowrap; }
        .co-note strong { display: flex; align-items: flex-end; justify-content: space-between; gap: 0.5rem; font-family: var(--co-sharpie); font-weight: 400; font-size: clamp(1.25rem, 2.4vw, 1.6rem); line-height: 1.15; }
        .co-note svg { flex: none; width: 1.8rem; height: 0.9rem; margin-bottom: 0.25rem; color: var(--co-red); transition: translate 0.2s ease; }
        .co-note:hover svg { translate: 4px 0; }
        /* Two notes across a phone: the longest word and its arrow have to fit inside one. */
        @media (max-width: 480px) {
            .co-note { padding-inline: 0.9rem; }
            .co-note strong { font-size: min(1.25rem, 8.6vw - 15.5px); }
            .co-note svg { width: 1.5rem; height: 0.75rem; }
        }

        /* ---------------------------------------------------------------
           11. The questions, on the last page of the pad
           --------------------------------------------------------------- */
        .co-faq-h2 { font-size: clamp(2.1rem, 6.4cqi, 3.1rem); line-height: calc(var(--co-r) * 2); }
        .co-qa { counter-reset: co-q; margin-top: var(--co-r); }
        .co-qa details { counter-increment: co-q; }
        .co-qa summary {
            display: grid;
            grid-template-columns: 2.6rem minmax(0, 1fr) 1.5rem;
            align-items: start;
            gap: 0.5rem;
            padding-block: var(--co-r) 0;
            cursor: pointer;
        }
        .co-qa summary::before { content: "Q" counter(co-q); font-family: var(--co-sharpie); font-size: 1.3rem; line-height: var(--co-r); color: var(--co-red); }
        .co-qa h3 { font-weight: 700; font-size: 1.2rem; line-height: var(--co-r); }
        .co-qa summary i { position: relative; width: 1.25rem; height: var(--co-r); }
        .co-qa summary i::before,
        .co-qa summary i::after { content: ""; position: absolute; left: 0; right: 0; top: calc(50% - 2px); height: 3px; border-radius: 3px; background: var(--co-ink); rotate: -3deg; transition: rotate 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
        .co-qa summary i::after { rotate: 88deg; }
        .co-qa details[open] summary i::after { rotate: -3deg; }
        .co-qa details[open] h3 { text-decoration: underline wavy var(--co-red); text-decoration-thickness: 2px; text-underline-offset: 0.2em; }
        .co-qa details p { max-width: 46rem; padding-inline-start: 3.1rem; }
        @media (max-width: 600px) { .co-qa details p { padding-inline-start: 0; } }

        /* ---------------------------------------------------------------
           12. Encore: a name badge under the light. Dark in both modes.
           --------------------------------------------------------------- */
        .co-encore {
            position: relative;
            overflow: clip;
            padding-block: clamp(4.5rem, 9vw, 7.5rem);
            background-color: #0e0b08;
            background-image:
                radial-gradient(34rem 24rem at 50% 72%, rgba(255, 232, 180, 0.2), rgba(255, 232, 180, 0) 70%),
                conic-gradient(from 162deg at 50% -8rem, rgba(0, 0, 0, 0) 0deg, rgba(255, 236, 190, 0.13) 9deg 27deg, rgba(0, 0, 0, 0) 36deg);
            color: #f4ecd9;
            text-align: center;
        }
        .co-encore .co-kick { color: #ffb86a; transform-origin: 50% 50%; }
        .co-thats { margin-top: 0.4rem; color: #d9cfb8; font-size: 1.9rem; line-height: 1.2; }
        #co .co-encore .co-beat { color: #b5ab95; }
        .co-encore .co-h2 { margin-top: 0.6rem; font-size: clamp(2.3rem, 6.4vw, 4.4rem); }
        .co-encore .co-h2 span { display: block; color: #ffd27a; }
        .co-encore-sub { max-width: 34rem; margin: 1.2rem auto 0; color: #d9cfb8; font-size: 1.2rem; }
        .co-badge {
            width: min(100%, 31rem);
            margin: clamp(2.25rem, 5vw, 3.25rem) auto 0;
            overflow: hidden;
            border-radius: 1.1rem;
            background: #fbfaf5;
            color: #1b1a17;
            box-shadow: 0 2.2rem 3rem -1.4rem rgba(0, 0, 0, 0.9), 0 0 0 1px rgba(255, 255, 255, 0.1);
            rotate: -1.5deg;
        }
        .co-badge-top { padding: 1rem 1rem 0.85rem; background: #c22a1f; color: #fff; }
        .co-badge-top b { display: block; font-weight: 700; font-size: clamp(2.2rem, 8vw, 3rem); letter-spacing: 0.16em; line-height: 1; text-transform: uppercase; }
        .co-badge-top span { display: block; margin-top: 0.3rem; font-weight: 700; font-size: 1rem; letter-spacing: 0.08em; }
        .co-badge-body { padding: 1.5rem 1.25rem 1.6rem; }
        #co .co-claim {
            display: flex;
            align-items: baseline;
            min-width: 0;
            padding: 0.4rem 0.3rem 0.2rem;
            border: 0;
            border-bottom: 3px solid #1b1a17;
            border-radius: 0;
            font-family: var(--co-pen);
            font-weight: 700;
            font-size: clamp(1.5rem, 6vw, 2.1rem);
            line-height: 1.2;
            transition: box-shadow 0.2s ease;
        }
        #co .co-claim:focus-within { border-color: #c22a1f; box-shadow: 0 6px 0 -3px rgba(194, 42, 31, 0.35); }
        #co .co-badge:focus-within { border-color: transparent; box-shadow: 0 2.2rem 3rem -1.4rem rgba(0, 0, 0, 0.9), 0 0 0 4px rgba(255, 210, 122, 0.55); }
        #co .co-claim input {
            flex: 1;
            min-width: 0;
            margin-block: 0;
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
            outline: none;
            font: inherit;
            color: #1b1a17;
            text-align: right;
        }
        #co .co-claim input::placeholder { color: #847b6b; }
        .co-claim span { flex: none; color: #57524a; user-select: none; }
        .co-encore-form { display: grid; justify-items: center; gap: 1.9rem; }
        .co-encore-form .co-gaff { --a: 1.2deg; font-size: 1.45rem; padding-inline: 2rem; }
        .co-encore-notes { margin-top: 1.75rem; color: #d9cfb8; }
        .co-encore-notes p + p { margin-top: 0.2rem; color: #ffd27a; font-size: 1.6rem; line-height: 1.25; }

        /* ---------------------------------------------------------------
           The light. A club gives a comic the light when the time is
           nearly up; this one runs to five minutes down the page.
           Only where scroll timelines and the maths exist, never on a
           phone, and never for somebody who asked for less motion.
           --------------------------------------------------------------- */
        .co-light { display: none; }
        @supports (animation-timeline: scroll()) and (width: round(down, 5px, 1px)) and (width: mod(5px, 2px)) {
            @media (min-width: 1360px) and (prefers-reduced-motion: no-preference) {
                .co-light {
                    display: flex;
                    position: fixed;
                    left: 1.25rem;
                    bottom: 1.25rem;
                    z-index: 30;
                    align-items: center;
                    gap: 0.6rem;
                    padding: 0.55rem 0.85rem 0.5rem 0.7rem;
                    border-radius: 0.7rem;
                    background: #1b1a17;
                    color: #ffd9a0;
                    box-shadow: 0 0.7rem 1.3rem -0.5rem rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255, 255, 255, 0.14);
                    font-family: ui-monospace, 'SF Mono', Menlo, Consolas, monospace;
                    font-size: 1.15rem;
                    font-variant-numeric: tabular-nums;
                    line-height: 1;
                    animation: co-clock linear both, co-leave linear both;
                    animation-timeline: scroll(root), scroll(root);
                    animation-range: 0% 92%, 93.4% 94.4%;
                }
                .co-light-time { counter-reset: co-m round(down, var(--co-t) / 60) co-s mod(var(--co-t), 60); }
                .co-light-time::after { content: counter(co-m) ":" counter(co-s, decimal-leading-zero); }
                .co-light-lamp {
                    width: 0.8rem;
                    height: 0.8rem;
                    border-radius: 50%;
                    background: #3a1512;
                    animation: co-lamp linear both;
                    animation-timeline: scroll(root);
                    animation-range: 0% 92%;
                }
                .co-light-cap { font-family: var(--co-text); font-weight: 700; font-size: 0.62rem; letter-spacing: 0.16em; text-transform: uppercase; color: #a59c8b; }
                .dark .co-light { box-shadow: 0 0 0 1px rgba(241, 233, 214, 0.2), 0 0.7rem 1.3rem -0.5rem #000; }
            }
        }
        @keyframes co-clock { from { --co-t: 0; } to { --co-t: 300; } }
        @keyframes co-lamp {
            0%, 89.9% { background: #3a1512; box-shadow: none; }
            90%, 100% { background: #ff3b2f; box-shadow: 0 0 0.8rem 0.2rem rgba(255, 59, 47, 0.8); }
        }
        @keyframes co-leave { to { opacity: 0; visibility: hidden; } }

        @media (prefers-reduced-motion: reduce) {
            .co-gaff, .co-napkin, .co-type, .co-label, .co-note, .co-setlist a { transition: none; }
        }
    </style>

    @php
        $coArrow = '<svg aria-hidden="true" viewBox="0 0 48 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M3 13c10-5 23-6 39-1M33 4l10 8-11 7" /></svg>';
        $coDown = '<svg aria-hidden="true" viewBox="0 0 24 40" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3c-2 9 1 20 0 32M4 26l8 10 8-11" /></svg>';
        // Tally marks for a count: gates of five, then the strokes left over.
        $coTally = function (int $count): string {
            $marks = '';
            for ($gate = 0; $gate < intdiv($count, 5); $gate++) {
                $marks .= '<span class="co-tally"><i></i><i></i><i></i><i></i><b></b></span>';
            }
            if ($count % 5) {
                $marks .= '<span class="co-tally">' . str_repeat('<i></i>', $count % 5) . '</span>';
            }

            return $marks;
        };
    @endphp

    <div id="co">

        {{-- The light: a stage timer that runs with the page. Decoration, and only where the
             browser can drive it from the scroll position. --}}
        <div class="co-light" aria-hidden="true">
            <span class="co-light-lamp"></span>
            <span class="co-light-time"></span>
            <span class="co-light-cap">the light</span>
        </div>

        <!-- ============================================================ -->
        <!-- 1. Hero: the top sheet of the pad                            -->
        <!-- ============================================================ -->
        <section class="co-hero" id="top">
            <div class="co-page">
                <div class="co-binding" aria-hidden="true"></div>
                <div class="co-sheet">
                    <span class="co-scribble" style="--y: 5.5rem;" aria-hidden="true">new opener</span>
                    <span class="co-scribble" style="--y: 14.6rem; --a: 6deg;" aria-hidden="true">(slow down here)</span>
                    <span class="co-scribble" style="--y: 26.5rem; --a: -4deg;" aria-hidden="true">keep!</span>
                    <p class="co-pen co-date-note" aria-hidden="true">late show, 2nd spot</p>

                    <h1 class="co-sharpie co-h1">
                        <x-marketing.hero-eyebrow class="co-eyebrow">Event schedule for comedians</x-marketing.hero-eyebrow>
                        <span class="co-write">The grind is real.</span>
                        <span class="co-write co-write-2 co-redink">One link for every set.</span>
                    </h1>

                    <p class="co-lede es-fade-up es-d-3">
                        Open mic Monday. Barking Tuesday. Guest set Wednesday. Headlining Friday. One link shows fans every set - yours, not the algorithm's.
                    </p>

                    <div class="co-cta es-fade-up es-d-4">
                        <a href="#set" class="co-gaff co-gaff-white" style="--a: 1deg;">
                            See tonight's set
                            {!! $coDown !!}
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="co-gaff">
                            Get your link
                            {!! $coArrow !!}
                        </a>
                    </div>

                    <!-- The journey: open mic to headliner -->
                    <ol class="co-ladder co-pen es-fade-up es-d-5">
                        <li>Open Mic</li>
                        <li>Bringer</li>
                        <li>Guest Spot</li>
                        <li>Feature</li>
                        <li><span class="co-circled">Headliner</span></li>
                    </ol>

                    <div class="co-doodle" aria-hidden="true">
                        <span class="co-pen">mics this week</span>
                        <span class="co-sharpie co-doodle-tally">{!! $coTally(7) !!}</span>
                        <span class="co-pen co-struck">five links in bio</span>
                        <span class="co-pen co-doodle-one">one.</span>
                    </div>
                    <span class="co-ring" aria-hidden="true"></span>
                    <span class="co-curl" aria-hidden="true"></span>
                </div>
            </div>
            <div class="co-marker" aria-hidden="true"><i class="co-marker-cap"></i><i class="co-marker-body">PERMANENT</i><i class="co-marker-end"></i></div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The problem: the grind, tallied on the back of the pad    -->
        <!-- ============================================================ -->
        <section class="co-part" style="padding-top: 0;">
            <div class="co-page co-board-page" data-reveal>
                <div class="co-board">
                    <span class="co-pen co-kick">the grind</span>
                    <h2 class="co-sharpie co-h2">
                        You put in the work. The algorithm <span class="co-wavy">buries it.</span>
                    </h2>

                    <div class="co-stats">
                        <div class="co-stat">
                            <div class="co-sharpie co-stat-n">7<span aria-hidden="true">{!! $coTally(7) !!}</span></div>
                            <p>mics a week just to stay sharp</p>
                        </div>
                        <div class="co-stat">
                            <div class="co-sharpie co-stat-n">5<span aria-hidden="true">{!! $coTally(5) !!}</span></div>
                            <p>clubs where you're trying to get regular</p>
                        </div>
                        <div class="co-stat">
                            <div class="co-sharpie co-stat-n"><span class="co-circled" style="--co-red: #7d130c;">~3%</span></div>
                            <p>of your followers actually see your show posts</p>
                        </div>
                    </div>

                    <p class="co-board-foot">
                        There is a better way to fill the room.
                        <a href="#set">
                            Here is the set
                            {!! $coDown !!}
                        </a>
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. Tonight's set: the list on the tape (in-page nav)         -->
        <!-- ============================================================ -->
        <section id="set" class="co-part" style="scroll-margin-top: 4rem;">
            <div class="co-wrap co-set-grid">
                <div>
                    <span class="co-pen co-kick" data-reveal>tonight's set</span>
                    <h2 class="co-sharpie co-h2" data-reveal style="--reveal-delay: 0.08s;">
                        A tight five. <span class="co-redink co-allegedly">Allegedly.</span>
                    </h2>
                    <p class="co-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Five bits, some crowd work, an encore. Every bit is a thing Event Schedule does for working comedians.
                    </p>
                </div>

                <nav class="co-tape co-setlist" aria-label="Tonight's set" data-reveal="zoom">
                    <ol>
                        <li><a href="#bit-1"><span class="co-set-no">01</span><span class="co-set-name">The One-Link Bit</span><span class="co-pen co-set-time">5:00</span></a></li>
                        <li><a href="#bit-2"><span class="co-set-no">02</span><span class="co-set-name">The Zero-Fee Bit</span><span class="co-pen co-set-time">17:00</span></a></li>
                        <li><a href="#bit-3"><span class="co-set-no">03</span><span class="co-set-name">The Clubs-Book-You Bit</span><span class="co-pen co-set-time">27:00</span></a></li>
                        <li><a href="#bit-4"><span class="co-set-no">04</span><span class="co-set-name"><s class="co-struck" aria-hidden="true">Tuesdays</s> The Callback</span><span class="co-pen co-set-time">35:00</span></a></li>
                        <li><a href="#bit-5"><span class="co-set-no">05</span><span class="co-set-name">The Closer</span><span class="co-pen co-set-time">47:00</span></a></li>
                        <li><a href="#crowd-work"><span class="co-set-no">--</span><span class="co-set-name">Crowd Work</span><span class="co-pen co-set-time co-set-time-long">runs long</span></a></li>
                        <li><a href="#claim"><span class="co-set-no">--</span><span class="co-set-name">Encore</span><span class="co-pen co-set-time co-set-time-long">they always want one more</span></a></li>
                    </ol>
                    <div class="co-set-total">
                        <span>Total stage time</span>
                        <b><span class="co-circled"><span data-count-to="47">47</span> MIN</span></b>
                    </div>
                    <p class="co-pen co-set-tag">A tight five that runs 47 minutes. Every comic you know.</p>
                </nav>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The five bits: a page of the pad each                     -->
        <!-- ============================================================ -->
        <section id="features" class="co-part" style="scroll-margin-top: 4rem; padding-top: 1rem;">
            <div class="co-wrap co-head">
                <span class="co-pen co-kick" data-reveal>the tight five</span>
                <h2 class="co-sharpie co-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Five bits every working <span class="co-redink">comedian</span> needs
                </h2>
            </div>

            <!-- BIT 01: The One-Link Bit -->
            <article id="bit-1" class="co-page co-bit" style="scroll-margin-top: 5.5rem;" data-reveal="flip">
                <div class="co-sheet co-torn co-on-paper">
                    <div class="co-sharpie co-bit-no" aria-hidden="true">01</div>
                    <span class="co-scribble" style="--y: 8.2rem; --a: 5deg;" aria-hidden="true">opener</span>
                    <div class="co-bit-grid">
                        <div class="co-flow">
                            <p class="co-pen co-bit-min">5 MIN IN</p>
                            <p class="co-pen co-setup">You do seven mics a week. Your bio fits one link.</p>
                            <p class="co-pen co-beat" aria-hidden="true">(beat)</p>
                            <h3 class="co-sharpie">Good thing one link <span class="co-hl">is all it takes.</span></h3>
                            <p>Running between clubs? Texting yourself set times? One calendar shows every mic, every guest set, every headline, each with its start time and set length.</p>
                            <ul class="co-checks">
                                <li>Track set lengths (tight 5, 10, 15, feature, headline)</li>
                                <li>See the whole week of spots at a glance</li>
                                <li>Syncs with Google Calendar (both ways)</li>
                            </ul>
                            <p class="co-pen co-aside">Late nights welcome: 10:30 PM - 12:30 AM</p>
                        </div>
                        <div class="co-bit-obj" aria-hidden="true">
                            <div class="co-card co-tilt-r">
                                <div class="co-card-head"><span class="co-sharpie">This Week</span><span class="co-pen"><span data-count-to="5">5</span> sets {!! $coTally(5) !!}</span></div>
                                <div class="co-week-row"><div class="co-day"><small>MON</small>12</div><div><strong>Stand Up NY <span class="co-pen">5 min</span></strong><em>Open mic · 7 PM signup</em></div></div>
                                <div class="co-week-row"><div class="co-day"><small>WED</small>14</div><div><strong>Comedy Cellar <span class="co-pen">12 min</span></strong><em>Guest set · 9:30 PM</em></div></div>
                                <div class="co-week-row"><div class="co-day"><small>THU</small>15</div><div><strong>Gotham Comedy <span class="co-pen">10 min</span></strong><em>Late show · 11 PM</em></div></div>
                                <div class="co-week-row"><div class="co-day"><small>SAT</small>17</div><div><strong>Carolines <span class="co-pen co-circled">Headlining</span></strong><em>Two shows: 8 PM &amp; 10:30 PM</em></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- BIT 02: The Zero-Fee Bit -->
            <article id="bit-2" class="co-page co-bit" style="scroll-margin-top: 5.5rem;" data-reveal="flip">
                <div class="co-sheet co-torn co-on-paper">
                    <div class="co-sharpie co-bit-no" aria-hidden="true">02</div>
                    <span class="co-scribble" style="--y: 8.2rem;" aria-hidden="true">true story</span>
                    <div class="co-bit-grid">
                        <div class="co-flow">
                            <p class="co-pen co-bit-min">17 MIN IN</p>
                            <p class="co-pen co-setup">Ticket platforms take a cut of every seat you fill.</p>
                            <p class="co-pen co-beat" aria-hidden="true">(beat)</p>
                            <h3 class="co-sharpie">We take <span class="co-circled">{{ plan_price(0) }}.</span> That's the whole bit.</h3>
                            <p>Producing your own show? Sell tickets directly. Money goes straight to your own Stripe or <a href="{{ marketing_url('/paypal') }}">PayPal</a> account, or you take cash at the door - we don't take a cut. Your hustle, your earnings.</p>
                            <p>General admission, VIP, early bird - every ticket gets a QR code for the door. Pro adds promo codes for your regulars and waitlists for the sellouts.</p>
                        </div>
                        <div class="co-bit-obj" aria-hidden="true">
                            <div class="co-stub-wrap co-tilt-l">
                                <div class="co-stub">
                                    <div class="co-stub-top"><span class="co-sharpie">Saturday Late Show</span><span class="co-pen"><span data-count-to="73">73</span> sold</span></div>
                                    <div class="co-stub-row"><span>General Admission</span><span>$20</span></div>
                                    <div class="co-stub-row"><span>Front Row + Meet & Greet</span><span>$50</span></div>
                                    <div class="co-stub-row co-stub-fee"><span>Platform fee</span><span>{{ plan_price(0) }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- BIT 03: The Clubs-Book-You Bit -->
            <article id="bit-3" class="co-page co-bit" style="scroll-margin-top: 5.5rem;" data-reveal="flip">
                <div class="co-sheet co-torn co-on-paper">
                    <div class="co-sharpie co-bit-no" aria-hidden="true">03</div>
                    <span class="co-scribble" style="--y: 8.2rem; --a: 4deg;" aria-hidden="true">act out the text</span>
                    <div class="co-bit-grid">
                        <div class="co-flow">
                            <p class="co-pen co-bit-min">27 MIN IN</p>
                            <p class="co-pen co-setup">A club books you. You forget to post it. Nobody comes.</p>
                            <p class="co-pen co-beat" aria-hidden="true">(beat)</p>
                            <h3 class="co-sharpie">Now the club <span class="co-hl">posts it for you.</span></h3>
                            <p>When a club adds you to their lineup, it lands in your requests. Accept it and the set is on your schedule, with the club's time and address. No copy-paste. No 'wait, what time did they say?'</p>
                            <p>Not on Event Schedule yet? The club's listing makes a page with your name on it, credited to the club and kept out of search engines until you <a href="{{ marketing_url('/docs/creating-events#claim') }}">claim it</a> with the email address they entered. The event page lists the whole lineup either way.</p>
                        </div>
                        <div class="co-bit-obj" aria-hidden="true">
                            <div class="co-stamps">
                                <span class="co-stamp" style="--c: #1b1a17; --a: -9deg;">CC</span>
                                <span class="co-stamp" style="--c: var(--co-red); --a: 6deg;">GC</span>
                                <span class="co-stamp" style="--c: #1d5a30; --a: -3deg;">SU</span>
                                <span class="co-pen">+ more</span>
                            </div>
                            <div class="co-points">{!! str_replace('<svg ', '<svg class="co-arrow co-arrow-down" ', $coDown) !!}</div>
                            <div class="co-tape co-url" dir="ltr">your-name.eventschedule.com</div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- BIT 04: The Callback -->
            <article id="bit-4" class="co-page co-bit" style="scroll-margin-top: 5.5rem;" data-reveal="flip">
                <div class="co-sheet co-torn co-on-paper">
                    <div class="co-sharpie co-bit-no" aria-hidden="true">04</div>
                    <span class="co-scribble" style="--y: 8.2rem; --a: -9deg;" aria-hidden="true">CALL BACK!</span>
                    <div class="co-bit-grid">
                        <div class="co-flow">
                            <p class="co-pen co-bit-min"><span class="co-circled">The Callback</span> &nbsp;35 MIN IN</p>
                            <p class="co-pen co-setup">Remember bit one? One link is all it takes?</p>
                            <p class="co-pen co-beat" aria-hidden="true">(beat)</p>
                            <h3 class="co-sharpie">It also takes care of <span class="co-hl">every Tuesday. Forever.</span></h3>
                            <p>Set your weekly mic or monthly showcase once and it repeats on its day-of-week pattern, with date exceptions for the weeks the room is dark. <a href="{{ marketing_url('/features/recurring-events') }}">Recurring events</a> are free.</p>
                            <p class="co-pen co-setup">That was a callback. Comics love a callback. So do fans who always know where Tuesday is.</p>
                        </div>
                        <div class="co-bit-obj" aria-hidden="true">
                            <div class="co-card co-tilt-r">
                                <div class="co-card-head co-card-head-long"><span class="co-sharpie">Open Mic at The Basement</span></div>
                                <div class="co-pen co-tues-note">Repeats every Tuesday - 8 PM</div>
                                <div class="co-tues-row"><span>Tue, Dec 3</span><span>8 PM</span></div>
                                <div class="co-tues-row"><span>Tue, Dec 10</span><span>8 PM</span></div>
                                <div class="co-tues-row"><span>Tue, Dec 17</span><span>8 PM</span></div>
                                <div class="co-pen co-tues-note co-tues-skip"><span class="co-struck">Skipping Dec 24</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- BIT 05: The Closer -->
            <article id="bit-5" class="co-page co-bit" style="scroll-margin-top: 5.5rem;" data-reveal="flip">
                <div class="co-sheet co-torn co-on-paper">
                    <div class="co-sharpie co-bit-no" aria-hidden="true">05</div>
                    <span class="co-scribble" style="--y: 8.2rem; --a: 5deg;" aria-hidden="true">closer</span>
                    <div class="co-bit-grid">
                        <div class="co-flow">
                            <p class="co-pen co-bit-min"><span data-count-to="47">47</span> MIN IN · Still a tight five.</p>
                            <p class="co-pen co-setup">You are not famous yet. Your show page does not know that.</p>
                            <p class="co-pen co-beat" aria-hidden="true">(beat)</p>
                            <h3 class="co-sharpie">Look like the headliner <span class="co-hl">before you are one.</span></h3>
                            <p>Download Instagram-ready flyers for any set in one click. Stop begging your friend who 'knows Canva.'</p>
                            <p>And when the show is worth announcing, email it. You posted about your last one and 3% of your followers saw it. Email reaches everyone who signed up. No algorithm deciding who deserves to see it.</p>
                        </div>
                        <div class="co-bit-obj" aria-hidden="true">
                            <div class="co-post co-tilt-l">
                                <div class="co-post-top">
                                    <div>
                                        <div class="co-sharpie">Headlining Saturday!</div>
                                        <span class="co-pen">Sent to <span data-count-to="96">96</span> fans</span>
                                    </div>
                                    <span class="co-postage">SAT</span>
                                </div>
                                <div class="co-post-stats">
                                    <div><b><span data-count-to="68">68</span>%</b><small>opened</small></div>
                                    <div><b><span data-count-to="23">23</span>%</b><small>clicked</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Crowd work: what the room sends back                      -->
        <!-- ============================================================ -->
        <section id="crowd-work" class="co-part" style="scroll-margin-top: 4rem;">
            <div class="co-wrap">
                <div class="co-head co-head-mid">
                    <span class="co-pen co-kick" data-reveal>crowd work</span>
                    <h2 class="co-sharpie co-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The audience joins in. <span class="co-redink co-break">You keep the mic.</span>
                    </h2>
                    <p class="co-sub" data-reveal style="--reveal-delay: 0.16s;">
                        Fans add to the show. Nothing goes up without your OK.
                    </p>
                </div>

                <div class="co-napkins" data-reveal-group="110">
                    <div class="co-napkin" style="--a: -1.6deg;" data-reveal>
                        <h3>Fan photos and clips</h3>
                        <p>Fans post crowd shots and clips from the show. You approve every one before it goes live.</p>
                        <div class="co-napkin-obj" aria-hidden="true">
                            <div class="co-snap">
                                <div class="co-snap-img"></div>
                                <div>
                                    <strong>Saturday late show clip</strong>
                                    <small>From @frontrowfan</small>
                                </div>
                            </div>
                            <div class="co-ok"><span>Approve</span><span>Hide</span></div>
                        </div>
                    </div>

                    <div class="co-napkin" style="--a: 1.2deg;" data-reveal>
                        <h3>Polls</h3>
                        <p>On Pro, let the crowd pick the poster, the theme, or the next city. <a href="{{ marketing_url('/features/polls') }}">Polls</a> live right on the event.</p>
                        <div class="co-napkin-obj" aria-hidden="true">
                            <div class="co-vote-q">Which poster for Friday?</div>
                            <div class="co-vote" style="--w: 64%;"><span>The mugshot one</span><span>64%</span></div>
                            <div class="co-vote" style="--w: 36%;"><span>The serious one</span><span>36%</span></div>
                        </div>
                    </div>

                    <div class="co-napkin" style="--a: -0.8deg;" data-reveal>
                        <h3>Post-show feedback</h3>
                        <p>On Pro, fans leave star ratings and comments after the show. Find out which closer actually closed.</p>
                        <div class="co-napkin-obj" aria-hidden="true">
                            <div class="co-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                            <p class="co-quote">"The Tuesday callback destroyed. See you next week."</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. The journey: the years, down the margin                   -->
        <!-- ============================================================ -->
        @php
            $comedyJourney = [
                ['Grinding the mics', 'Track your spots across every open mic in the city. Know where you\'re signed up tonight.', 'bg-red-100 dark:bg-red-900/30', 'text-red-500 dark:text-red-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                ['Getting regular', 'Guest spots coming in? Track which rooms you\'re regular at and build your schedule.', 'bg-amber-100 dark:bg-amber-900/30', 'text-amber-500 dark:text-amber-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />'],
                ['Featuring', '20-30 minute sets opening for headliners. Start selling tickets to your own fans.', 'bg-orange-100 dark:bg-orange-900/30', 'text-orange-500 dark:text-orange-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />'],
                ['Headlining', 'Your name on the marquee. Email your fans directly and sell out your shows.', 'bg-rose-100 dark:bg-rose-900/30', 'text-rose-500 dark:text-rose-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />'],
                ['On the road', 'Touring clubs across the country? One link shows fans in every city when you\'re coming through.', 'bg-blue-100 dark:bg-blue-900/30', 'text-blue-500 dark:text-blue-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                ['Improv & Sketch', 'Coordinate your troupe\'s schedule. Everyone knows when the next Harold night is.', 'bg-teal-100 dark:bg-teal-900/30', 'text-teal-500 dark:text-teal-400', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />'],
            ];
            $journeyYears = ['YEAR 1', 'YEAR 2', 'YEAR 3', 'YEAR 4', 'YEAR 5+', 'ALWAYS'];
        @endphp
        <section class="co-part" style="padding-top: 1rem;">
            <div class="co-page" data-reveal="flip">
                <div class="co-sheet co-torn co-on-paper">
                    <span class="co-scribble" style="--y: 6.4rem; --a: -6deg;" aria-hidden="true">start here</span>
                    <span class="co-scribble" style="--y: 22rem; --a: 5deg;" aria-hidden="true">(you are here?)</span>
                    <h2 class="co-sharpie co-faq-h2">
                        From open mic to <span class="co-hl">headliner</span>
                    </h2>
                    <p class="co-pen" style="color: var(--co-red);">
                        Event Schedule grows with your career
                    </p>

                    <div class="co-years">
                        @foreach ($comedyJourney as $jIndex => [$jTitle, $jDesc, $jChip, $jText, $jIcon])
                            <div class="co-year">
                                <span class="co-pen co-year-tag" aria-hidden="true">{{ $journeyYears[$jIndex] }}</span>
                                <h3>{{ $jTitle }}</h3>
                                <p>{{ $jDesc }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. Perfect for: six index cards                              -->
        <!-- ============================================================ -->
        <section class="co-part">
            <div class="co-wrap co-types">
                <!-- Running order scribble (decorative) -->
                <div class="co-tape co-order" aria-hidden="true">
                    <div>Running order</div>
                    <div>1. Stand-up <span>&#10003;</span></div>
                    <div>2. Improv <span>&#10003;</span></div>
                    <div>3. Sketch <span>&#10003;</span></div>
                    <div>4. Open mic'ers <span>&#10003;</span></div>
                    <div>5. Road dogs <span>&#10003;</span></div>
                    <div>6. The MC <span>&#10003;</span></div>
                </div>

                <div class="co-head">
                    <h2 class="co-sharpie co-h2" data-reveal>
                        Perfect for all types of <span class="co-redink">comedy</span>
                    </h2>
                    <p class="co-sub" data-reveal style="--reveal-delay: 0.08s;">
                        Whether you're doing tight fives or touring theaters, Event Schedule has you sorted.
                    </p>
                </div>

                @php
                    $coTypes = [
                        ['Stand-Up Comics', 'Share your sets and build a following. One link shows fans everywhere you\'re performing.', 'for-stand-up-comics', '-1.4deg'],
                        ['Improv Performers', 'Promote weekly shows with your troupe. Coordinate Harold nights and jam sessions.', 'for-improv-performers', '0.9deg'],
                        ['Sketch Comedy Groups', 'Coordinate ensemble schedules and share show runs. Everyone knows when the next performance is.', 'for-sketch-comedy-groups', '-0.6deg'],
                        ['Open Mic Regulars', 'Track spots across multiple venues, with every mic night on one calendar.', 'for-open-mic-comics', '1.3deg'],
                        ['Touring Headliners', 'Share tour dates with fans across the country. One link for your entire run.', 'for-touring-comedians', '-1deg'],
                        ['Comedy Hosts & MCs', 'Showcase hosting gigs and show bookers your availability. Build your reputation as the go-to host.', 'for-comedy-podcasters', '0.7deg'],
                    ];
                @endphp
                <div class="co-cards" data-reveal-group="80">
                    @foreach ($coTypes as [$coName, $coDesc, $coSlug, $coTilt])
                        @php $coPost = get_sub_audience_blog($coSlug); @endphp
                        <article class="co-card co-type" style="--a: {{ $coTilt }};" data-reveal>
                            <h3>{{ $coName }}</h3>
                            <p>{{ $coDesc }}</p>
                            @if ($coPost)
                                <a href="{{ blog_url('/' . $coPost->slug) }}" aria-label="Learn more about Event Schedule for {{ $coName }}">
                                    <span>Learn more {!! $coArrow !!}</span>
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. How it works: three tickets off the roll                  -->
        <!-- ============================================================ -->
        <section class="co-part" style="padding-top: 1rem;">
            <div class="co-wrap">
                <div class="co-head co-head-mid">
                    <h2 class="co-sharpie co-h2" data-reveal>
                        Three steps. More butts in <span class="co-redink">seats.</span>
                    </h2>
                </div>

                <div class="co-roll" data-reveal>
                    <div class="co-ticket">
                        <div class="co-ticket-top" aria-hidden="true"><span>Admit one</span><span>No. 047001</span></div>
                        <div class="co-ticket-n" aria-hidden="true">1</div>
                        <h3>Add your sets</h3>
                        <p>Import from Google Calendar or add your mics, guest sets, and headlining gigs.</p>
                    </div>
                    <div class="co-ticket">
                        <div class="co-ticket-top" aria-hidden="true"><span>Admit one</span><span>No. 047002</span></div>
                        <div class="co-ticket-n" aria-hidden="true">2</div>
                        <h3>Share one link</h3>
                        <p>Drop it in your bio. Fans see all your upcoming shows in one place.</p>
                    </div>
                    <div class="co-ticket">
                        <div class="co-ticket-top" aria-hidden="true"><span>Admit one</span><span>No. 047003</span></div>
                        <div class="co-ticket-n" aria-hidden="true">3</div>
                        <h3>Fill the room</h3>
                        <p>Fans sign up for email and hear from you directly. No more posting into the algorithm void.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. Key features: four labels of tape                         -->
        <!-- ============================================================ -->
        <section class="co-part">
            <div class="co-wrap co-keys-grid">
                <div>
                    <h2 class="co-sharpie co-h2" data-reveal>Key <span class="co-redink">features</span></h2>
                    <a href="{{ marketing_url('/features') }}" class="co-pen co-more" data-reveal style="--reveal-delay: 0.08s;">
                        See all features
                        {!! $coArrow !!}
                    </a>
                </div>

                @php
                    $coKeys = [
                        ['Ticketing', 'Sell tickets with QR check-in and zero platform fees', marketing_url('/features/ticketing'), '-1.2deg'],
                        ['Event Graphics', 'Instagram-ready show flyers generated from your events', marketing_url('/features/event-graphics'), '0.9deg'],
                        ['Newsletters', 'Send event updates directly to followers\' inboxes', marketing_url('/features/newsletters'), '0.7deg'],
                        ['Calendar Sync', 'Two-way sync with Google, Outlook and CalDAV', marketing_url('/features/calendar-sync'), '-0.8deg'],
                    ];
                @endphp
                <div class="co-labels" data-reveal-group="80">
                    @foreach ($coKeys as [$coKeyName, $coKeyDesc, $coKeyUrl, $coKeyTilt])
                        <a href="{{ $coKeyUrl }}" class="co-tape co-label" style="--a: {{ $coKeyTilt }};" data-reveal>
                            <span>
                                <strong>{{ $coKeyName }}</strong>
                                <small>{{ $coKeyDesc }}</small>
                            </span>
                            {!! $coArrow !!}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="co-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 10. Related pages: four notes off the same pad               -->
        <!-- ============================================================ -->
        <section class="co-part" style="padding-top: 1rem;">
            <div class="co-wrap">
                <div class="co-also-head">
                    <h2 class="co-sharpie co-h2" data-reveal>Related <span class="co-redink">pages</span></h2>
                    <a href="{{ marketing_url('/use-cases') }}" class="co-pen co-more" data-reveal>
                        See all use cases
                        {!! $coArrow !!}
                    </a>
                </div>

                <div class="co-notes" data-reveal-group="80">
                    @foreach ([['/for-musicians', 'Musicians'], ['/for-magicians', 'Magicians'], ['/for-spoken-word', 'Spoken Word Artists'], ['/for-theater-performers', 'Theater Performers']] as $relIndex => [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" class="co-note" style="--a: {{ ['-1.6deg', '1.1deg', '-0.7deg', '1.5deg'][$relIndex] }};" data-reveal>
                            <small>Event Schedule for</small>
                            <strong>{{ $relName }} {!! $coArrow !!}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 11. FAQ: the last page of the pad                            -->
        <!-- ============================================================ -->
        <section class="co-part" style="padding-top: 1rem;">
            <div class="co-page" data-reveal="flip">
                <div class="co-sheet co-torn co-on-paper">
                    <span class="co-scribble" style="--y: 3.7rem; --a: -8deg;" aria-hidden="true">Q &amp; A</span>
                    <h2 class="co-sharpie co-faq-h2">
                        Frequently asked <span class="co-hl">questions</span>
                    </h2>
                    <p class="co-pen" style="color: var(--co-red);">
                        Everything comedians ask about Event Schedule.
                    </p>

                    <span class="co-curl" aria-hidden="true"></span>
                    <div class="co-qa">
                        @php
                            $faqs = [
                                ['q' => 'Is Event Schedule free for comedians?', 'a' => 'Yes. Event Schedule is free forever for sharing your show dates and building a fan following, and free door lists never run out. Newsletters are free too, at 10 a month counted per recipient. Charging for a ticket is the Pro part, and Pro lifts the newsletter ceiling with it. Zero platform fees on any ticket sale, on any plan.'],
                                ['q' => 'Can I sell tickets to my comedy shows?', 'a' => 'Yes. Connect your own Stripe or PayPal account, or take cash at the door, and sell tickets directly from your schedule. Create multiple ticket types like general admission, VIP, and early bird. Every ticket includes a QR code for check-in at the door. Zero platform fees - you only pay your payment provider\'s own processing fee. Refunds come with it: from the Sales page, a Stripe or PayPal sale can be refunded in full or in part, and the money goes back through the provider.'],
                                ['q' => 'Can fans get told when tickets for my show go on sale?', 'a' => 'Yes, on every plan. Switch on the "Notify me" card, announce the show before tickets exist, and a fan can leave just an email address on the event page, no account needed. They get one email when tickets go on sale, one if the show is cancelled, and a reminder shortly before it starts, plus any change notice you choose to send. Nothing else. The event\'s Tickets panel shows you how many people are waiting.'],
                                ['q' => 'How do fans hear about my new shows?', 'a' => 'Fans who sign up with their email get an automatic digest when you add new shows, batched so a run of new dates arrives as one email. You can also send newsletters with upcoming dates yourself. Fans who would rather not give an address can subscribe to your calendar feed, which updates itself when a show moves. And your schedule link goes in your social bios, on podcasts, or anywhere fans find you.'],
                                ['q' => 'Can comedy clubs add me to their lineup?', 'a' => 'Yes. When a comedy club adds you to their event on Event Schedule, it arrives as a request on your schedule. Accept it and the show appears there too, so you never add the same gig in two places, and because both schedules share one event, a changed start time shows on both. Not on Event Schedule yet? The club\'s listing creates a page with your name on it, kept out of search engines until you claim it by signing in with the email address it carries.'],
                                ['q' => 'Can I track open mics and bringer shows without announcing them?', 'a' => 'Yes. Save any set as a draft and it stays off your public schedule until you publish it. Drafts are free and unlimited, so you can plan a whole week of mics privately. On the Enterprise plan you can also make events internal or unlisted with an optional password for corporate and private gigs.'],
                                ['q' => 'Can I run stand-up, improv, and a podcast on one schedule?', 'a' => 'Yes. Sub-schedules let you split your calendar into separate lineups like stand-up sets, improv nights, and podcast tapings. Fans can see everything in one place, or you can share each lineup with its own link. Sub-schedules are included on the free plan.'],
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
            </div>
        </section>

        <x-seo.faq-schema :items="$faqs" />

        <!-- ============================================================ -->
        <!-- 12. Encore: a name badge under the light                     -->
        <!-- ============================================================ -->
        <section id="claim" class="co-encore co-on-dark" style="scroll-margin-top: 4rem;">
            <div class="co-wrap">
                <span class="co-pen co-kick" data-reveal>encore</span>
                <p class="co-pen co-thats" data-reveal style="--reveal-delay: 0.08s;">That's the set.</p>
                <p class="co-pen co-beat" aria-hidden="true" data-reveal style="--reveal-delay: 0.14s;">(beat)</p>
                <h2 class="co-sharpie co-h2" data-reveal style="--reveal-delay: 0.2s;">
                    Your fans want to see you. <span>Give them one link.</span>
                </h2>
                <p class="co-encore-sub" data-reveal style="--reveal-delay: 0.28s;">
                    No catch. Free forever. Pro waits in the wings for the night you outgrow it.
                </p>

                <div class="co-encore-form" data-reveal="panel">
                    <div class="co-badge es-claim">
                        <div class="co-badge-top" aria-hidden="true"><b>Hello</b><span>my name is</span></div>
                        <div class="co-badge-body">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="co-claim">
                                <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ app_url('/sign_up?type=talent') }}" class="co-gaff co-gaff-white">
                        Get your link
                        {!! $coArrow !!}
                    </a>
                </div>

                <div class="co-encore-notes">
                    <p>No credit card required</p>
                    <p class="co-pen">Total stage time: <span data-count-to="47">47</span> min. Some tight five.</p>
                </div>
            </div>
        </section>

        <div class="co-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
