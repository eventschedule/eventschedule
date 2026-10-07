<x-marketing-layout>
    <x-slot name="title">Food Truck Schedules | One Link That Always Has Today's Stop</x-slot>
    <x-slot name="description">Your address changes every week. Put the route on one link that never goes stale, and let customers subscribe to it in their own calendar. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Food Trucks and Vendors</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Lilita One') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Kaushan Script') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Cabin') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Food Trucks & Vendors"
        description="A public schedule that always carries today's stop, with the regular pitches set up once as recurring events, a QR code for the serving window and a live calendar feed customers subscribe to once."
        audience="Food Trucks, Vendors & Mobile Kitchens"
        keywords="food truck schedule, food truck locations, mobile vendor calendar, where is the food truck, catering booking requests, street food route" />
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
           For-food-trucks-and-vendors "Order Up" styles.

           The page is the truck and what comes out of it. The hero is
           the side of the truck: an awning, a serving hatch, and in the
           hatch the one thing a customer wants to know, today's stop,
           with the rest of the week small beneath it. After that: order
           tickets on the rail, the week as a route of stops, a paper
           tray on a checkered liner, a painted mustard sign, a street
           of stalls, two menu boards and the back of the truck with a
           number plate to fill in.

           Two things the product does not have are still not drawn: an
           "open now" light, and a struck-through day. A date exception
           removes the date, so Thursday is simply absent, on the board
           and on the route.

           Everything is scoped under #ft. The painted and printed
           objects (truck, tickets, tray, boards, the mustard band) keep
           their own colours in both modes, so they are set in the fixed
           tokens; the ground, the cards and the running text follow the
           theme.
           ============================================================== */

        @property --ft-wob {
            syntax: '<angle>';
            inherits: false;
            initial-value: 0deg;
        }

        #ft {
            /* Fixed paints: the same in daylight and at the night market. */
            --ft-k: #1c1512;
            --ft-c: #fff7e6;
            --ft-m: #ffc629;
            --ft-r: #e02d1b;
            --ft-truck: #d42816;
            --ft-t: #1fb6a6;
            --ft-slip: #fffdf7;
            /* Theme tokens. */
            --ft-ground: #fff7e6;
            --ft-ground-2: #ffecc4;
            --ft-card: #fffdf7;
            --ft-ink: #1c1512;
            --ft-ink-2: #4a3d35;
            --ft-ink-3: #6b5a4e;
            --ft-line: rgba(28, 21, 18, 0.16);
            --ft-edge: #1c1512;
            --ft-red: #e02d1b;
            --ft-red-ink: #b81f10;
            --ft-teal-ink: #0b6f65;
            --ft-display: 'Lilita One', 'Arial Rounded MT Bold', 'Trebuchet MS', system-ui, sans-serif;
            --ft-script: 'Kaushan Script', 'Brush Script MT', cursive;
            --ft-text: 'Cabin', 'Trebuchet MS', 'Helvetica Neue', Arial, sans-serif;
            --ft-mono: ui-monospace, 'SF Mono', Menlo, Consolas, 'Liberation Mono', monospace;
            position: relative;
            background: var(--ft-ground);
            color: var(--ft-ink);
            font-family: var(--ft-text);
            font-size: 1.0625rem;
            line-height: 1.55;
        }
        .dark #ft {
            --ft-ground: #16110f;
            --ft-ground-2: #1f1815;
            --ft-card: #271e1a;
            --ft-ink: #fff3dc;
            --ft-ink-2: #dccbb5;
            --ft-ink-3: #ab9a8c;
            --ft-line: rgba(255, 243, 220, 0.18);
            --ft-edge: #7a6859;
            --ft-red: #f0432f;
            --ft-red-ink: #ff8674;
            --ft-teal-ink: #5fe0d2;
        }

        /* The bar above is painted the same cream, so the truck pulls up under it. */
        body > header.sticky {
            background-color: rgba(255, 247, 230, 0.88);
            border-bottom-color: rgba(28, 21, 18, 0.14);
        }
        .dark body > header.sticky {
            background-color: rgba(22, 17, 15, 0.88);
            border-bottom-color: rgba(255, 243, 220, 0.14);
        }

        #ft ::selection { background: var(--ft-m); color: var(--ft-k); }
        #ft a:focus-visible,
        #ft summary:focus-visible,
        #ft input:focus-visible {
            outline: 3px solid var(--ft-ink);
            outline-offset: 3px;
            border-radius: 0.3rem;
        }
        #ft .ft-truck a:focus-visible,
        #ft .ft-board a:focus-visible,
        #ft .ft-rear a:focus-visible { outline-color: var(--ft-m); }

        .ft-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
        .ft-section { position: relative; padding-block: clamp(4rem, 8vw, 7rem); }
        .ft-alt { background: var(--ft-ground-2); }

        /* Voices: the painted sign, the brush word, the kitchen printer. */
        .ft-d { font-family: var(--ft-display); font-weight: 400; line-height: 1; letter-spacing: 0.005em; }
        .ft-h2 { font-size: clamp(2.3rem, 5.4vw, 4.1rem); line-height: 1.04; text-wrap: balance; }
        .ft-sub { max-width: 40rem; margin-top: 1.1rem; color: var(--ft-ink-2); font-size: 1.15rem; }
        .ft-head-center { text-align: center; }
        .ft-head-center .ft-sub { margin-inline: auto; }
        .ft-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.9rem;
            font-family: var(--ft-script);
            font-size: 1.6rem;
            line-height: 1.2;
            color: var(--ft-red-ink);
            rotate: -2deg;
        }
        .ft-no {
            padding: 0.32rem 0.55rem;
            border-radius: 0.3rem;
            background: var(--ft-ink);
            color: var(--ft-ground);
            font-family: var(--ft-mono);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            rotate: 2deg;
        }
        /* One swipe of mustard paint behind the words that matter. */
        .ft-swipe {
            padding: 0 0.16em;
            border-radius: 0.14em;
            background: var(--ft-m);
            color: var(--ft-k);
            -webkit-box-decoration-break: clone;
            box-decoration-break: clone;
        }
        .ft-free-row { display: flex; align-items: flex-start; gap: 0.8rem; margin-top: 2rem; }
        .ft-head-center + .ft-free-row,
        .ft-free-row.is-center { justify-content: center; }
        .ft-free {
            flex: none;
            padding: 0.28rem 0.7rem 0.22rem;
            border-radius: 999px;
            background: var(--ft-t);
            color: var(--ft-k);
            font-family: var(--ft-display);
            font-size: 0.9rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            rotate: -4deg;
        }
        .ft-free-note { max-width: 44rem; color: var(--ft-ink-2); font-size: 0.975rem; }

        /* Chunky buttons: a hard lower edge, and they squish when pressed. */
        .ft-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            padding: 0.95rem 1.5rem 0.85rem;
            border: 3px solid var(--ft-k);
            border-radius: 1rem;
            background: var(--ft-m);
            color: var(--ft-k);
            font-family: var(--ft-display);
            font-size: 1.25rem;
            line-height: 1.1;
            letter-spacing: 0.01em;
            box-shadow: 0 0.42rem 0 var(--ft-k);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .ft-btn:hover { transform: translateY(0.14rem); box-shadow: 0 0.28rem 0 var(--ft-k); }
        .ft-btn:active { transform: translateY(0.42rem); box-shadow: 0 0 0 var(--ft-k); }
        .ft-btn svg { width: 1.2rem; height: 1.2rem; }
        .ft-btn-cream { background: var(--ft-c); }
        .dark #ft .ft-btn { box-shadow: 0 0.42rem 0 #000; }
        .dark #ft .ft-btn:hover { box-shadow: 0 0.28rem 0 #000; }
        .ft-textlink {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 700;
            color: var(--ft-ink);
            text-decoration: underline;
            text-decoration-color: var(--ft-red);
            text-decoration-thickness: 3px;
            text-underline-offset: 0.3em;
            transition: gap 0.2s ease;
        }
        .ft-textlink:hover { gap: 0.8rem; }
        .ft-textlink svg { width: 1.05rem; height: 1.05rem; }

        /* Price-flash stickers: a burst cut by clip-path, wobbling on its own angle. */
        .ft-burst {
            display: grid;
            place-items: center;
            aspect-ratio: 1;
            padding: 0.9rem;
            background: var(--ft-truck);
            color: var(--ft-c);
            font-family: var(--ft-display);
            text-align: center;
            line-height: 0.95;
            clip-path: polygon(50.0% 0.0%, 58.0% 9.8%, 69.1% 3.8%, 72.8% 15.9%, 85.4% 14.6%, 84.1% 27.2%, 96.2% 30.9%, 90.2% 42.0%, 100.0% 50.0%, 90.2% 58.0%, 96.2% 69.1%, 84.1% 72.8%, 85.4% 85.4%, 72.8% 84.1%, 69.1% 96.2%, 58.0% 90.2%, 50.0% 100.0%, 42.0% 90.2%, 30.9% 96.2%, 27.2% 84.1%, 14.6% 85.4%, 15.9% 72.8%, 3.8% 69.1%, 9.8% 58.0%, 0.0% 50.0%, 9.8% 42.0%, 3.8% 30.9%, 15.9% 27.2%, 14.6% 14.6%, 27.2% 15.9%, 30.9% 3.8%, 42.0% 9.8%);
            rotate: calc(var(--r, -8deg) + var(--ft-wob, 0deg));
            animation: ft-wobble 3.8s ease-in-out infinite;
        }
        .ft-burst-teal { background: var(--ft-t); color: var(--ft-k); }
        @keyframes ft-wobble {
            0%, 100% { --ft-wob: -3deg; }
            50% { --ft-wob: 3deg; }
        }

        /* ---------------------------------------------------------------
           The route rail: the page's sections as stops, wide screens only
           --------------------------------------------------------------- */
        .ft-dotnav { display: none; }
        @media (min-width: 1500px) {
            /* The nav is a box the size of the page that clips the rail, so the rail stays fixed
               to the screen and still ends where the page does, instead of riding on over the
               site footer. */
            .ft-dotnav {
                display: block;
                position: absolute;
                inset: 0;
                z-index: 40;
                clip-path: inset(0);
                pointer-events: none;
            }
            .ft-dotnav ul {
                position: fixed;
                right: 1.5rem;
                top: 50%;
                translate: 0 -50%;
                display: grid;
                gap: 0.95rem;
                pointer-events: auto;
            }
            .ft-dotnav ul::before {
                content: "";
                position: absolute;
                top: 0.5rem;
                bottom: 0.5rem;
                right: 0.42rem;
                width: 0.22rem;
                border-radius: 1rem;
                background: var(--ft-ink);
                opacity: 0.35;
            }
            .ft-dotnav a { position: relative; display: flex; align-items: center; justify-content: flex-end; gap: 0.6rem; }
            .ft-dotnav a i {
                position: relative;
                width: 1.06rem;
                height: 1.06rem;
                border-radius: 50%;
                border: 0.22rem solid var(--ft-ink);
                background: var(--ft-ground);
                transition: scale 0.25s cubic-bezier(0.34, 1.5, 0.64, 1), background-color 0.25s ease;
            }
            .ft-dotnav a span {
                padding: 0.2rem 0.55rem;
                border-radius: 0.4rem;
                background: var(--ft-ink);
                color: var(--ft-ground);
                font-size: 0.78rem;
                font-weight: 700;
                white-space: nowrap;
                opacity: 0;
                translate: 0.4rem 0;
                transition: opacity 0.2s ease, translate 0.2s ease;
            }
            .ft-dotnav a:hover span,
            .ft-dotnav a:focus-visible span { opacity: 1; translate: 0 0; }
            .ft-dotnav a.is-active i { scale: 1.35; background: var(--ft-m); }
        }

        /* ---------------------------------------------------------------
           Hero: the side of the truck
           --------------------------------------------------------------- */
        .ft-hero { position: relative; overflow: clip; padding-block: clamp(1.5rem, 3vw, 2.5rem) clamp(4.6rem, 7vw, 6rem); }

        /* A string of bulbs: an ellipse's lower half for the wire, and each bulb set on it by sqrt(). */
        .ft-bulbs { position: relative; height: 3rem; --sag: 2.2rem; pointer-events: none; }
        .ft-swag { position: absolute; top: 0; left: 0; width: 50%; height: var(--sag); }
        .ft-swag + .ft-swag { left: 50%; }
        .ft-swag::before {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: calc(var(--sag) * -1);
            height: calc(var(--sag) * 2);
            border: 2px solid var(--ft-ink-3);
            border-radius: 50%;
            clip-path: inset(50% 0 0 0);
        }
        .ft-swag i {
            --t: calc((var(--i) + 1) / 10);
            --u: calc(2 * var(--t) - 1);
            position: absolute;
            left: calc(var(--t) * 100%);
            top: calc(var(--sag) * (1 - var(--u) * var(--u)) * 0.92);
            top: calc(var(--sag) * sqrt(1 - var(--u) * var(--u)));
            width: 0.8rem;
            height: 1rem;
            margin-left: -0.4rem;
            border-radius: 50% 50% 50% 50% / 40% 40% 60% 60%;
            background: var(--b, var(--ft-m));
            box-shadow: inset 0 0 0 1.5px rgba(28, 21, 18, 0.28);
        }
        .ft-swag i::before {
            content: "";
            position: absolute;
            top: -0.26rem;
            left: 50%;
            width: 0.42rem;
            height: 0.32rem;
            margin-left: -0.21rem;
            border-radius: 1px;
            background: var(--ft-ink-3);
        }
        .ft-swag i:nth-child(4n + 2) { --b: var(--ft-r); }
        .ft-swag i:nth-child(4n + 3) { --b: var(--ft-t); }
        .ft-swag i:nth-child(4n) { --b: var(--ft-c); }
        .dark .ft-swag i {
            box-shadow: 0 0 0.9rem 0.15rem color-mix(in srgb, var(--b, #ffc629) 70%, transparent), 0 0 2.6rem 0.5rem color-mix(in srgb, var(--b, #ffc629) 28%, transparent);
            animation: ft-twinkle 3.4s ease-in-out infinite;
            animation-delay: calc(var(--i) * -0.41s);
        }
        @keyframes ft-twinkle {
            0%, 100% { opacity: 1; }
            46% { opacity: 1; }
            50% { opacity: 0.55; }
            54% { opacity: 1; }
        }

        .ft-truck {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 2.5rem;
            margin-top: 0.4rem;
            padding: clamp(1.6rem, 3.2vw, 2.6rem) clamp(1.6rem, 3.6vw, 3.2rem);
            padding-bottom: clamp(5.2rem, 7vw, 5.9rem);
            border-radius: 2.2rem 2.2rem 1rem 1rem;
            background-color: var(--ft-truck);
            /* Sheen first, then the go-faster stripe, its pinstripe and a row of rivets. */
            background-image:
                linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0) 24%, rgba(0, 0, 0, 0) 68%, rgba(0, 0, 0, 0.18)),
                linear-gradient(#ffc629, #ffc629),
                linear-gradient(#fff7e6, #fff7e6),
                radial-gradient(circle, rgba(255, 255, 255, 0.42) 0 2px, rgba(255, 255, 255, 0) 2.6px);
            background-size: 100% 100%, 100% 0.85rem, 100% 0.2rem, 2.6rem 1rem;
            background-position: 0 0, 0 calc(100% - 2.5rem), 0 calc(100% - 3.75rem), 0.7rem 0.5rem;
            background-repeat: no-repeat, no-repeat, no-repeat, repeat-x;
            box-shadow: inset 0 0 0 3px rgba(0, 0, 0, 0.16), 0 2.4rem 3rem -1.8rem rgba(28, 21, 18, 0.6);
            color: var(--ft-c);
        }
        @media (min-width: 980px) {
            .ft-truck { grid-template-columns: minmax(0, 1fr) minmax(0, 1.04fr); align-items: center; gap: 3.2rem; }
        }
        /* Wheel arches cut into the body, the wheels, and the shadow on the road. */
        .ft-arch {
            position: absolute;
            bottom: 0;
            left: var(--x);
            width: 8.6rem;
            height: 4.3rem;
            border-radius: 8.6rem 8.6rem 0 0;
            background: #8a170b;
        }
        .ft-wheel {
            position: absolute;
            bottom: -3.2rem;
            left: calc(var(--x) + 1.1rem);
            width: 6.4rem;
            aspect-ratio: 1;
            border-radius: 50%;
            background: radial-gradient(circle, #eceff2 0 11%, #6f767e 12% 16%, #c6cbd1 17% 39%, #17120f 40% 100%);
            box-shadow: inset 0 0 0 0.3rem #2c231e;
        }
        .ft-road {
            position: absolute;
            left: 3%;
            right: 3%;
            bottom: -3.9rem;
            height: 1.4rem;
            border-radius: 50%;
            background: radial-gradient(closest-side, rgba(28, 21, 18, 0.4), rgba(28, 21, 18, 0));
        }
        .dark .ft-road { background: radial-gradient(closest-side, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0)); }
        @media (max-width: 640px) {
            .ft-arch { width: 6.2rem; height: 3.1rem; }
            .ft-wheel { width: 4.6rem; bottom: -2.3rem; left: calc(var(--x) + 0.8rem); }
            .ft-road { bottom: -2.9rem; }
        }

        .ft-copy { container-type: inline-size; min-width: 0; }
        .ft-eyebrow {
            display: inline-block;
            margin-bottom: 1.3rem;
            padding: 0.42rem 0.8rem 0.36rem;
            border-radius: 0.5rem;
            background: var(--ft-m);
            color: var(--ft-k);
            font-family: var(--ft-text);
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            line-height: 1.3;
            rotate: -1.5deg;
        }
        .ft-h1 { font-size: clamp(2.7rem, 13.4cqi, 6.4rem); line-height: 0.98; }
        /* Under about 388px the 2.7rem floor is wider than the panel and "is" drops to a line
           of its own: on those phones the line is fitted to the panel instead. */
        @container (max-width: 18.6rem) {
            #ft .ft-h1 { font-size: 14.4cqi; }
        }
        .ft-h1-line { display: block; }
        .ft-h1 .ft-swipe { display: inline-block; padding: 0.02em 0.18em 0.06em; rotate: -2.5deg; }
        .ft-lede { max-width: 31rem; margin-top: 1.6rem; font-size: clamp(1.1rem, 1.5vw, 1.28rem); font-weight: 700; }
        .ft-cta { display: flex; flex-wrap: wrap; gap: 1.1rem 0.9rem; margin-top: 2rem; }
        .ft-cta .ft-btn { padding-inline: 1.15rem; font-size: clamp(1.05rem, 1.5vw, 1.18rem); }

        /* The hatch: awning, window, ledge. */
        .ft-hatch { position: relative; container-type: inline-size; min-width: 0; }
        .ft-awning {
            position: relative;
            z-index: 2;
            height: 4.8rem;
            margin-inline: -3.5%;
            background-color: var(--ft-t);
            background-image:
                linear-gradient(180deg, rgba(255, 255, 255, 0.26), rgba(255, 255, 255, 0) 42%, rgba(0, 0, 0, 0.22)),
                linear-gradient(90deg, #1fb6a6 50%, #fff7e6 0);
            background-size: 100% 100%, 4.8rem 100%;
            background-repeat: no-repeat, round;
            border-radius: 0.5rem 0.5rem 0 0;
            /* A scalloped hem: one half-disc under every stripe. Stripes and scallops share one
               tile and both repeat with `round`, so the hem always ends on a whole scallop. The
               canvas above overlaps the scallops by a pixel: where the two only met, Safari left
               a hairline of truck showing through along the join. */
            -webkit-mask:
                radial-gradient(circle at 25% 0, #000 1.16rem, #0000 1.2rem) 0 100% / 4.8rem 1.2rem round,
                radial-gradient(circle at 75% 0, #000 1.16rem, #0000 1.2rem) 0 100% / 4.8rem 1.2rem round,
                linear-gradient(#000 0 0) 0 0 / 100% calc(100% - 1.2rem + 1px) no-repeat;
            mask:
                radial-gradient(circle at 25% 0, #000 1.16rem, #0000 1.2rem) 0 100% / 4.8rem 1.2rem round,
                radial-gradient(circle at 75% 0, #000 1.16rem, #0000 1.2rem) 0 100% / 4.8rem 1.2rem round,
                linear-gradient(#000 0 0) 0 0 / 100% calc(100% - 1.2rem + 1px) no-repeat;
        }
        .ft-window {
            position: relative;
            margin-top: -1.2rem;
            padding: 2.4rem clamp(1.1rem, 5cqi, 2rem) 1.4rem;
            border: 0.4rem solid #2a211c;
            border-bottom: 0;
            background: var(--ft-c);
            color: var(--ft-k);
            box-shadow: inset 0 0 0 0.2rem #cfd3d8, inset 0 2.2rem 2.4rem -1.6rem rgba(28, 21, 18, 0.45);
        }
        .dark .ft-window {
            background: #fff1c9;
            box-shadow: inset 0 0 0 0.2rem #cfd3d8, inset 0 2.2rem 2.4rem -1.6rem rgba(28, 21, 18, 0.45), 0 0 6rem rgba(255, 198, 41, 0.35);
        }
        .ft-ledge {
            position: relative;
            z-index: 1;
            height: 1.3rem;
            margin-inline: -2.5%;
            border-radius: 0.3rem;
            background: linear-gradient(#f4f6f8, #adb3ba 55%, #7b828a);
            box-shadow: 0 0.7rem 0.8rem -0.35rem rgba(0, 0, 0, 0.5);
        }
        .ft-today-tag { font-family: var(--ft-script); font-size: clamp(1.7rem, 8cqi, 2.4rem); line-height: 1; color: #b81f10; rotate: -4deg; transform-origin: 0 100%; }
        .ft-today-place { margin-top: 0.35rem; font-size: clamp(2.3rem, 12.8cqi, 4.7rem); line-height: 0.95; text-wrap: balance; }
        .ft-today-hours {
            display: inline-block;
            margin-top: 0.9rem;
            padding: 0.4rem 0.8rem;
            border-radius: 0.4rem;
            background: var(--ft-k);
            color: var(--ft-m);
            font-family: var(--ft-mono);
            font-size: clamp(0.95rem, 4.4cqi, 1.25rem);
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .ft-rest-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-top: 1.3rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #6b5a4e;
        }
        .ft-rest-title::after { content: ""; flex: 1; height: 2px; background: repeating-linear-gradient(90deg, rgba(28, 21, 18, 0.4) 0 6px, rgba(28, 21, 18, 0) 6px 11px); }
        .ft-rest { margin-top: 0.5rem; }
        .ft-rest li {
            display: grid;
            grid-template-columns: 3rem minmax(0, 1fr) auto;
            align-items: baseline;
            gap: 0.7rem;
            padding-block: 0.34rem;
            border-bottom: 1px solid rgba(28, 21, 18, 0.12);
            font-size: 0.975rem;
        }
        .ft-rest li span:first-child { font-family: var(--ft-display); font-size: 1.05rem; color: #b81f10; }
        .ft-rest li span:nth-child(2) { font-weight: 700; }
        .ft-rest li span:last-child { font-family: var(--ft-mono); font-size: 0.82rem; color: #4a3d35; }
        .ft-gap-note { margin-top: 0.75rem; padding-right: 4.5rem; font-size: 0.9rem; color: #4a3d35; }
        .ft-hatch .ft-burst {
            position: absolute;
            z-index: 3;
            right: -2.2rem;
            bottom: -2.4rem;
            width: 6.6rem;
            font-size: 1.15rem;
            --r: 12deg;
        }
        @media (max-width: 979px) {
            .ft-hatch .ft-burst { right: -0.6rem; bottom: -2.6rem; width: 5.6rem; font-size: 0.98rem; }
        }

        /* ---------------------------------------------------------------
           01 The link: three order tickets on the rail
           --------------------------------------------------------------- */
        .ft-pass { margin-top: clamp(2.5rem, 5vw, 4rem); }
        .ft-rail { display: none; }
        .ft-tickets { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.2rem; }
        @media (min-width: 820px) {
            .ft-rail {
                display: block;
                position: relative;
                z-index: 2;
                height: 0.9rem;
                border-radius: 0.45rem;
                background: linear-gradient(#f6f8fa, #b4bac1 52%, #7c838b);
                box-shadow: 0 0.55rem 0.7rem -0.3rem rgba(28, 21, 18, 0.45);
            }
            .ft-rail::before,
            .ft-rail::after {
                content: "";
                position: absolute;
                top: -0.45rem;
                width: 1.1rem;
                height: 1.8rem;
                border-radius: 0.25rem;
                background: linear-gradient(#4a3f38, #1c1512);
            }
            .ft-rail::before { left: 1.2rem; }
            .ft-rail::after { right: 1.2rem; }
            .ft-tickets { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2rem; padding-inline: clamp(1rem, 4vw, 3.5rem); margin-top: -0.5rem; }
        }
        .ft-hang {
            position: relative;
            transform-origin: 50% 0;
            rotate: var(--r, 0deg);
            filter: drop-shadow(0 1rem 0.85rem rgba(28, 21, 18, 0.24));
            transition: rotate 0.5s cubic-bezier(0.34, 1.7, 0.64, 1);
        }
        .dark .ft-hang { filter: drop-shadow(0 1rem 1rem rgba(0, 0, 0, 0.6)); }
        /* The hanger is also the element the reveal watches, and the shared reveal ends on
           filter: none, which outranks the two rules above: every slip lost its shadow once it
           had printed, and a cream slip on a cream ground lost its edge with it. The id puts the
           shadow back on top (a mask cuts the slip, so it cannot be a box-shadow). */
        #ft .ft-hang { filter: drop-shadow(0 1rem 0.85rem rgba(28, 21, 18, 0.24)); }
        .dark #ft .ft-hang { filter: drop-shadow(0 1rem 1rem rgba(0, 0, 0, 0.6)); }
        .ft-hang:hover { rotate: 0deg; }
        html.es-anim #ft [data-reveal="ticket"]:not(.is-revealed) { transform: none; }
        .ft-clip {
            position: absolute;
            z-index: 3;
            top: -0.55rem;
            left: 50%;
            width: 2.7rem;
            height: 1.5rem;
            margin-left: -1.35rem;
            border-radius: 0.3rem;
            background: linear-gradient(#4a3f38, #1c1512);
            box-shadow: inset 0 0.15rem 0 rgba(255, 255, 255, 0.18);
        }
        .ft-ticket {
            height: 100%;
            padding: 1.9rem 1.45rem 2.5rem;
            background: var(--ft-slip);
            color: var(--ft-k);
            /* The tear: a zigzag bitten out of the bottom edge. */
            -webkit-mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50% / 1rem 100%;
            mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50% / 1rem 100%;
            clip-path: inset(0 0 0 0);
        }
        /* It prints: the slip feeds out from under the clip in steps, like a kitchen printer. */
        html.es-anim #ft [data-reveal="ticket"] .ft-ticket { transition: clip-path 1.25s steps(18, end) var(--reveal-delay, 0s); }
        html.es-anim #ft [data-reveal="ticket"]:not(.is-revealed) .ft-ticket { clip-path: inset(0 0 94% 0); }
        .ft-ticket-top {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.05rem;
            padding-bottom: 0.7rem;
            border-bottom: 2px dashed rgba(28, 21, 18, 0.28);
            font-family: var(--ft-mono);
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #6b5a4e;
        }
        .ft-ticket h3 { font-size: 1.75rem; line-height: 1.05; text-wrap: balance; }
        .ft-ticket p { margin-top: 0.7rem; color: #4a3d35; }
        .ft-barcode {
            width: 62%;
            height: 1.7rem;
            margin-top: 1.4rem;
            background: repeating-linear-gradient(90deg, #1c1512 0 2px, #0000 2px 5px, #1c1512 5px 6px, #0000 6px 9px, #1c1512 9px 12px, #0000 12px 14px, #1c1512 14px 15px, #0000 15px 19px);
        }

        /* ---------------------------------------------------------------
           02 The week: the timetable and the route
           --------------------------------------------------------------- */
        .ft-week-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.75rem; align-items: start; }
        @media (min-width: 980px) { .ft-week-grid { grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr); gap: 4rem; } }
        .ft-notes { display: grid; gap: 1rem; margin-top: 1.9rem; }
        .ft-notes li { display: grid; grid-template-columns: 1.5rem minmax(0, 1fr); gap: 0.8rem; }
        .ft-pin {
            width: 1.2rem;
            height: 1.2rem;
            margin-top: 0.22rem;
            border-radius: 50%;
            border: 0.28rem solid var(--ft-ink);
            background: var(--ft-m);
        }
        .ft-note-t { font-weight: 700; }
        .ft-note-d { color: var(--ft-ink-2); }
        .ft-timetable {
            overflow: hidden;
            border: 3px solid var(--ft-edge);
            border-radius: 1.3rem;
            background: var(--ft-card);
            box-shadow: 0 0.45rem 0 var(--ft-edge);
            rotate: 1.2deg;
        }
        .ft-timetable-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem 1rem;
            padding: 1rem 1.3rem 0.85rem;
            background: var(--ft-m);
            color: var(--ft-k);
        }
        .ft-timetable-head h3 { font-size: 1.6rem; }
        .ft-timetable-head span { font-family: var(--ft-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.06em; }
        .ft-pitch {
            display: grid;
            grid-template-columns: 1.6rem minmax(0, 1fr);
            align-items: center;
            gap: 0.9rem;
            padding: 0.95rem 1.3rem;
            border-top: 2px solid var(--ft-line);
            transition: background-color 0.2s ease;
        }
        .ft-pitch:hover { background: var(--ft-ground-2); }
        .ft-pitch i { width: 1.5rem; height: 1.5rem; border-radius: 50%; border: 0.34rem solid var(--line); background: var(--ft-card); }
        .ft-pitch p:first-of-type { font-weight: 700; line-height: 1.25; }
        .ft-pitch p:last-of-type { font-family: var(--ft-mono); font-size: 0.82rem; color: var(--ft-ink-2); }
        .ft-except { padding: 1rem 1.3rem 1.2rem; border-top: 2px dashed var(--ft-line); }
        .ft-except p:first-child { font-size: 0.76rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: var(--ft-ink-3); }
        .ft-except p:last-child { margin-top: 0.3rem; font-size: 0.95rem; color: var(--ft-ink-2); }
        [data-line="a"] { --line: #e0a800; }
        [data-line="b"] { --line: #1fb6a6; }
        [data-line="c"] { --line: #e02d1b; }

        /* The route: a line, six day slots, a gap where Thursday is not, and a truck on it. */
        .ft-route { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); margin-top: clamp(3rem, 6vw, 4.5rem); padding-left: 3.6rem; }
        .ft-route::before {
            content: "";
            position: absolute;
            top: 1.6rem;
            bottom: 1.6rem;
            left: 0.95rem;
            width: 0.7rem;
            border-radius: 1rem;
            background: var(--ft-ink);
        }
        .ft-stop { position: relative; min-height: 4.6rem; padding-block: 0.7rem; }
        .ft-stop-dot {
            position: absolute;
            left: -3.45rem;
            top: 50%;
            width: 2.3rem;
            height: 2.3rem;
            margin-top: -1.15rem;
            border-radius: 50%;
            border: 0.45rem solid var(--line, var(--ft-ink));
            background: var(--ft-ground-2);
            box-shadow: 0 0 0 0.2rem var(--ft-ink);
            transition: scale 0.3s cubic-bezier(0.34, 1.6, 0.64, 1), background-color 0.3s ease;
        }
        .ft-stop.is-today .ft-stop-dot { scale: 1.25; background: var(--ft-m); animation: ft-pulse 2.6s ease-out infinite; }
        @keyframes ft-pulse {
            0% { box-shadow: 0 0 0 0.2rem var(--ft-ink), 0 0 0 0.2rem rgba(255, 198, 41, 0.7); }
            100% { box-shadow: 0 0 0 0.2rem var(--ft-ink), 0 0 0 1.5rem rgba(255, 198, 41, 0); }
        }
        .ft-stop.is-gap::before {
            content: "";
            position: absolute;
            left: -2.85rem;
            top: 0.2rem;
            bottom: 0.2rem;
            width: 1.1rem;
            background: repeating-linear-gradient(180deg, var(--ft-ground-2) 0 0.7rem, rgba(0, 0, 0, 0) 0.7rem 1.15rem);
        }
        .ft-stop-day { font-family: var(--ft-display); font-size: 1.5rem; line-height: 1; }
        .ft-stop.is-gap .ft-stop-day { color: var(--ft-ink-3); opacity: 0.6; }
        .ft-stop-place { margin-top: 0.3rem; font-weight: 700; line-height: 1.25; }
        .ft-stop-hours { font-family: var(--ft-mono); font-size: 0.8rem; color: var(--ft-ink-2); }
        .ft-stop-today {
            display: inline-block;
            margin-left: 0.5rem;
            font-family: var(--ft-script);
            font-size: 1.15rem;
            color: var(--ft-red-ink);
            rotate: -4deg;
        }
        /* The marker: a truck the size of a thumbnail, made of four boxes. */
        .ft-marker {
            position: absolute;
            z-index: 2;
            left: 0.2rem;
            top: 1.6rem;
            width: 2.2rem;
            height: 3rem;
            margin-top: -1.5rem;
            border-radius: 0.5rem;
            background: linear-gradient(90deg, rgba(0, 0, 0, 0) 0 62%, #ffc629 62% 80%, rgba(0, 0, 0, 0) 80%), #d42816;
            box-shadow: inset 0 0 0 0.16rem #1c1512, 0 0.4rem 0.6rem -0.2rem rgba(28, 21, 18, 0.55);
        }
        .ft-marker::before {
            content: "";
            position: absolute;
            left: 0.3rem;
            top: 0.4rem;
            width: 0.75rem;
            height: 1.3rem;
            border-radius: 0.15rem;
            background: #fff7e6;
            box-shadow: inset 0 0 0 0.12rem #1c1512;
        }
        @supports (animation-timeline: view()) {
            html.es-anim #ft .ft-marker {
                animation: ft-drive-down linear both;
                animation-timeline: view();
                animation-range: entry 35% exit 65%;
            }
        }
        @keyframes ft-drive-down {
            from { top: 1.6rem; }
            to { top: calc(100% - 1.6rem); }
        }
        @keyframes ft-drive-across {
            from { left: calc(100% / 12); }
            to { left: calc(100% * 11 / 12); }
        }
        @media (min-width: 820px) {
            .ft-route { grid-template-columns: repeat(6, minmax(0, 1fr)); padding-left: 0; padding-top: 3.6rem; }
            .ft-route::before { top: 0.95rem; bottom: auto; left: calc(100% / 12); right: calc(100% / 12); width: auto; height: 0.7rem; }
            .ft-stop { min-height: 0; padding: 0 0.5rem; text-align: center; }
            .ft-stop-dot { left: 50%; top: -3.45rem; margin-top: 0; margin-left: -1.15rem; }
            .ft-stop.is-gap::before {
                left: 14%;
                right: 14%;
                top: -2.85rem;
                bottom: auto;
                width: auto;
                height: 1.1rem;
                background: repeating-linear-gradient(90deg, var(--ft-ground-2) 0 0.7rem, rgba(0, 0, 0, 0) 0.7rem 1.15rem);
            }
            .ft-stop-today { display: block; margin: 0.2rem 0 0; }
            .ft-marker {
                left: calc(100% / 12);
                top: -0.75rem;
                width: 3.2rem;
                height: 2.1rem;
                margin: 0 0 0 -1.6rem;
                background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0 60%, #ffc629 60% 78%, rgba(0, 0, 0, 0) 78%), #d42816;
            }
            .ft-marker::before { left: 1.75rem; top: 0.3rem; width: 1.05rem; height: 0.75rem; }
            html.es-anim #ft .ft-marker { animation-name: ft-drive-across; }
        }
        /* Point at a pitch on the timetable and its stops on the route answer. */
        #ft .ft-week-body:has(.ft-pitch[data-line="a"]:hover) .ft-stop[data-line="a"] .ft-stop-dot,
        #ft .ft-week-body:has(.ft-pitch[data-line="b"]:hover) .ft-stop[data-line="b"] .ft-stop-dot,
        #ft .ft-week-body:has(.ft-pitch[data-line="c"]:hover) .ft-stop[data-line="c"] .ft-stop-dot { scale: 1.45; background: var(--line); }

        /* ---------------------------------------------------------------
           03 The window: a paper tray on the checkered liner
           --------------------------------------------------------------- */
        .ft-liner {
            position: relative;
            padding-block: clamp(4rem, 8vw, 6.5rem);
            background-color: #fff7e6;
            /* Gingham from one conic tile: both stripes, one stripe, none, one stripe. */
            background-image: conic-gradient(#f19b90 90deg, #fff7e6 0 180deg, #f19b90 0 270deg, #e02d1b 0);
            background-size: 3.2rem 3.2rem;
        }
        .dark .ft-liner {
            background-color: #241a16;
            background-image: conic-gradient(#6d2219 90deg, #241a16 0 180deg, #6d2219 0 270deg, #a8281a 0);
        }
        .ft-liner-in { position: relative; }
        .ft-greaseproof {
            position: absolute;
            inset: -1.6rem -1.2rem;
            border-radius: 0.4rem;
            background: rgba(255, 253, 247, 0.62);
            -webkit-backdrop-filter: blur(1.5px) saturate(0.85);
            backdrop-filter: blur(1.5px) saturate(0.85);
            box-shadow: 0 1rem 2rem -1rem rgba(28, 21, 18, 0.35);
            rotate: -1.3deg;
        }
        .dark .ft-greaseproof { background: rgba(255, 243, 220, 0.2); }
        .ft-tray {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 2.5rem;
            padding: clamp(1.6rem, 4vw, 3.4rem);
            border-radius: 1.8rem;
            background: var(--ft-slip);
            color: var(--ft-k);
            /* The folded rim of a paperboard tray. */
            box-shadow:
                inset 0 0 0 0.7rem #f3e7cf,
                inset 0 0 0 0.76rem rgba(28, 21, 18, 0.14),
                inset 0 1.6rem 1.6rem -1rem rgba(28, 21, 18, 0.14),
                0 1.6rem 2.4rem -1rem rgba(28, 21, 18, 0.5);
        }
        @media (min-width: 980px) {
            .ft-tray { grid-template-columns: minmax(0, 0.78fr) minmax(0, 1.22fr); gap: 3.6rem; align-items: start; }
            .ft-tray-side { position: sticky; top: 6.5rem; }
        }
        .ft-tray .ft-kicker { color: #b81f10; }
        .ft-tray .ft-no { background: #1c1512; color: #fff7e6; }
        .ft-tray .ft-sub,
        .ft-tray .ft-free-note { color: #4a3d35; }
        .ft-qr-sticker {
            position: relative;
            width: min(100%, 21rem);
            margin-inline: auto;
            padding: 1.5rem 1.4rem 1.4rem;
            border-radius: 1.5rem;
            background: #fff;
            border: 0.5rem solid #fff;
            outline: 3px solid #1c1512;
            outline-offset: -0.5rem;
            box-shadow: 0 1.1rem 1.6rem -0.7rem rgba(28, 21, 18, 0.5);
            text-align: center;
            rotate: -4deg;
            transition: rotate 0.4s cubic-bezier(0.34, 1.6, 0.64, 1);
        }
        .ft-qr-sticker:hover { rotate: 0deg; }
        .ft-qr-tag { font-family: var(--ft-script); font-size: 1.5rem; line-height: 1.1; color: #b81f10; rotate: -3deg; }
        .ft-qr {
            position: relative;
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 2px;
            width: 62%;
            aspect-ratio: 1;
            margin: 0.9rem auto 1rem;
        }
        .ft-qr i { border-radius: 1px; }
        .ft-qr i.on { background: #1c1512; }
        .ft-qr b {
            position: absolute;
            width: calc(100% / 3 - 2px);
            aspect-ratio: 1;
            border-radius: 12%;
            background: radial-gradient(circle, #e02d1b 0 30%, #fff 31% 54%, #1c1512 55%);
        }
        .ft-qr b:nth-of-type(1) { left: 0; top: 0; }
        .ft-qr b:nth-of-type(2) { right: 0; top: 0; }
        .ft-qr b:nth-of-type(3) { left: 0; bottom: 0; }
        .ft-qr-q { font-size: 1.6rem; }
        .ft-qr-a { margin-top: 0.5rem; font-size: 0.95rem; color: #4a3d35; }
        .ft-qr-cap { max-width: 20rem; margin: 1.7rem auto 0; font-size: 0.9rem; text-align: center; color: #4a3d35; }
        .ft-combo { display: grid; gap: 1.25rem; margin-top: 2rem; counter-reset: ft-combo; }
        .ft-combo li { display: grid; grid-template-columns: 2.5rem minmax(0, 1fr); gap: 1rem; counter-increment: ft-combo; }
        .ft-combo li::before {
            content: counter(ft-combo);
            display: grid;
            place-items: center;
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 50%;
            border: 3px solid #1c1512;
            background: var(--ft-m);
            color: #1c1512;
            font-family: var(--ft-display);
            font-size: 1.2rem;
            box-shadow: 0 0.22rem 0 #1c1512;
        }
        .ft-combo li:nth-child(even)::before { background: var(--ft-t); }
        .ft-combo-t { font-weight: 700; font-size: 1.1rem; line-height: 1.3; }
        .ft-combo-d { margin-top: 0.2rem; color: #4a3d35; }
        #ft .ft-tray .ft-free-note a {
            display: inline;
            padding: 0;
            margin: 0;
            color: #1c1512;
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: #e02d1b;
            text-decoration-thickness: 2px;
            text-underline-offset: 0.2em;
        }

        /* ---------------------------------------------------------------
           04 Catering: the painted mustard sign
           --------------------------------------------------------------- */
        .ft-mustard {
            position: relative;
            overflow: clip;
            padding-block: clamp(4rem, 8vw, 7rem);
            background-color: #ffc629;
            background-image: repeating-conic-gradient(from 0deg at 50% 118%, rgba(255, 255, 255, 0.2) 0 5deg, rgba(255, 255, 255, 0) 5deg 10deg);
            color: #1c1512;
        }
        .ft-mustard .ft-kicker { color: #8a170b; }
        .ft-mustard .ft-no { background: #1c1512; color: #ffc629; }
        .ft-mustard .ft-sub,
        .ft-mustard .ft-free-note { color: #2c2209; }
        .ft-mustard .ft-swipe { background: #1c1512; color: #ffc629; }
        .ft-asks { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.6rem 2.4rem; margin-top: clamp(3rem, 5vw, 4rem); }
        @media (min-width: 860px) { .ft-asks { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ft-ask {
            position: relative;
            padding: 2.5rem 1.5rem 1.6rem;
            border: 3px solid #1c1512;
            border-radius: 1.3rem;
            background: #fffdf7;
            box-shadow: 0 0.5rem 0 #1c1512;
        }
        .ft-ask .ft-burst { position: absolute; top: -1.9rem; left: 1.1rem; width: 4.2rem; padding: 0; font-size: 1.75rem; }
        .ft-ask h3 { font-size: 1.6rem; line-height: 1.08; }
        .ft-ask p { margin-top: 0.6rem; color: #4a3d35; }
        @media (min-width: 860px) {
            .ft-ask + .ft-ask::after {
                content: "\2192\FE0E";
                position: absolute;
                top: 50%;
                left: -2rem;
                font-family: var(--ft-display);
                font-size: 1.9rem;
                line-height: 1;
                translate: 0 -50%;
                font-variant-emoji: text;
            }
        }

        /* ---------------------------------------------------------------
           05 Who it is for: a street of stalls
           --------------------------------------------------------------- */
        .ft-stalls { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.4rem 1.8rem; margin-top: clamp(2.8rem, 5vw, 4rem); }
        @media (min-width: 680px) { .ft-stalls { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1020px) { .ft-stalls { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ft-stall { display: flex; flex-direction: column; transition: translate 0.3s cubic-bezier(0.34, 1.5, 0.64, 1); }
        .ft-stall:hover { translate: 0 -0.4rem; }
        .ft-stall-awning {
            position: relative;
            z-index: 1;
            height: 3rem;
            margin-inline: -0.5rem;
            border-radius: 0.4rem 0.4rem 0 0;
            background-color: var(--a);
            background-image:
                linear-gradient(180deg, rgba(255, 255, 255, 0.24), rgba(255, 255, 255, 0) 45%, rgba(0, 0, 0, 0.2)),
                linear-gradient(90deg, var(--a) 50%, #fff7e6 0);
            background-size: 100% 100%, 3.2rem 100%;
            background-repeat: no-repeat, round;
            /* The scallops repeat across only (`round no-repeat`). Rounded both ways, the row was
               also fitted to the awning's height, and Safari let a sliver of the next row show
               as a hairline under the hem. */
            -webkit-mask:
                radial-gradient(circle at 25% 0, #000 0.77rem, #0000 0.8rem) 0 100% / 3.2rem 0.8rem round no-repeat,
                radial-gradient(circle at 75% 0, #000 0.77rem, #0000 0.8rem) 0 100% / 3.2rem 0.8rem round no-repeat,
                linear-gradient(#000 0 0) 0 0 / 100% calc(100% - 0.8rem + 1px) no-repeat;
            mask:
                radial-gradient(circle at 25% 0, #000 0.77rem, #0000 0.8rem) 0 100% / 3.2rem 0.8rem round no-repeat,
                radial-gradient(circle at 75% 0, #000 0.77rem, #0000 0.8rem) 0 100% / 3.2rem 0.8rem round no-repeat,
                linear-gradient(#000 0 0) 0 0 / 100% calc(100% - 0.8rem + 1px) no-repeat;
        }
        .ft-stall-board {
            display: flex;
            flex: 1;
            flex-direction: column;
            margin-top: -0.8rem;
            padding: 2rem 1.4rem 1.4rem;
            border: 3px solid var(--ft-edge);
            border-radius: 0 0 1.2rem 1.2rem;
            background: var(--ft-card);
            box-shadow: 0 0.45rem 0 var(--ft-edge), inset 0 1.1rem 1rem -0.9rem rgba(28, 21, 18, 0.35);
        }
        .ft-stall-board h3 { font-size: 1.7rem; line-height: 1.05; }
        .ft-stall-board p { margin-top: 0.6rem; color: var(--ft-ink-2); }
        .ft-stall-board a { margin-top: auto; padding-top: 1.1rem; align-self: flex-start; }

        /* ---------------------------------------------------------------
           06 and Key features: two painted boards
           --------------------------------------------------------------- */
        .ft-board {
            position: relative;
            padding: clamp(1.8rem, 4.4vw, 3.6rem);
            border: 0.7rem solid #d42816;
            border-radius: 1.6rem;
            background-color: #1c1512;
            background-image: linear-gradient(160deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0) 40%);
            color: #fff7e6;
            box-shadow: inset 0 0 0 2px rgba(255, 247, 230, 0.22), 0 1.6rem 2.4rem -1.4rem rgba(28, 21, 18, 0.7);
        }
        .ft-board::before,
        .ft-board::after {
            content: "";
            position: absolute;
            bottom: 100%;
            width: 0.3rem;
            height: clamp(3rem, 6vw, 4.6rem);
            margin-bottom: 0.7rem;
            background: repeating-linear-gradient(180deg, #8d949c 0 0.5rem, #555c64 0.5rem 0.9rem);
        }
        .ft-board::before { left: 14%; }
        .ft-board::after { right: 14%; }
        .ft-board + .ft-board { margin-top: 2.2rem; }
        .ft-board + .ft-board::before,
        .ft-board + .ft-board::after { height: 2.2rem; }
        .ft-board .ft-kicker { color: #ffc629; }
        .ft-board .ft-no { background: #ffc629; color: #1c1512; }
        .ft-specials { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.2rem 2.4rem; margin-top: clamp(2rem, 4vw, 3rem); }
        @media (min-width: 860px) { .ft-specials { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ft-special-n { font-size: clamp(3.4rem, 7vw, 5rem); color: #ffc629; }
        .ft-specials h3 { margin-top: 0.6rem; font-size: 1.75rem; line-height: 1.08; }
        .ft-specials p { margin-top: 0.6rem; color: #dccbb5; }
        .ft-menu { margin-top: clamp(1.4rem, 3vw, 2.2rem); }
        .ft-menu a {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.3rem 1rem;
            padding-block: 1.05rem;
            border-bottom: 2px dotted rgba(255, 247, 230, 0.3);
            transition: padding 0.25s ease;
        }
        .ft-menu-name { font-size: 1.65rem; transition: color 0.2s ease; }
        .ft-menu a:hover .ft-menu-name { color: #ffc629; }
        .ft-menu-desc { color: #dccbb5; }
        .ft-menu-lead { display: none; }
        @media (min-width: 860px) {
            .ft-menu a { grid-template-columns: auto minmax(2rem, 1fr) auto; align-items: baseline; border-bottom: 0; padding-block: 0.85rem; }
            .ft-menu a:hover { padding-inline: 0.6rem 0; }
            .ft-menu-lead { display: block; height: 0.5rem; border-bottom: 3px dotted rgba(255, 247, 230, 0.45); }
            .ft-menu-desc { text-align: right; }
        }
        .ft-board .ft-textlink { margin-top: 1.6rem; color: #ffc629; text-decoration-color: #fff7e6; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the paint changes.
           --------------------------------------------------------------- */
        #ft .ft-plans > section { background: var(--ft-ground); }
        #ft .ft-plans h2 { font-family: var(--ft-display); font-weight: 400; font-size: clamp(2.1rem, 4.6vw, 3.4rem); line-height: 1.04; letter-spacing: 0.005em; color: var(--ft-ink); }
        #ft .ft-plans h2 + p { color: var(--ft-ink-2); font-size: 1.0625rem; }
        #ft .ft-plans .grid > div { border: 3px solid var(--ft-edge); border-radius: 1.3rem; background: var(--ft-card); box-shadow: 0 0.45rem 0 var(--ft-edge); color: var(--ft-ink); }
        #ft .ft-plans .grid > div:hover { box-shadow: 0 0.45rem 0 var(--ft-red); }
        #ft .ft-plans .grid > div span,
        #ft .ft-plans .grid > div p,
        #ft .ft-plans .grid > div li { color: var(--ft-ink-2); }
        #ft .ft-plans .grid > div .text-3xl { font-family: var(--ft-display); font-weight: 400; font-size: 2.7rem; color: var(--ft-ink); }
        #ft .ft-plans .grid > div .uppercase { color: var(--ft-ink); }
        #ft .ft-plans .grid > div .rounded-full { background: var(--ft-t); color: var(--ft-k); }
        #ft .ft-plans .grid > div svg { color: var(--ft-teal-ink); }
        #ft .ft-plans a.font-medium { color: var(--ft-ink); text-decoration: underline; text-decoration-color: var(--ft-red); text-decoration-thickness: 3px; text-underline-offset: 0.3em; }
        #ft .ft-plans a.rounded-2xl { border: 3px solid var(--ft-k); border-radius: 1rem; background: var(--ft-m); color: var(--ft-k); box-shadow: 0 0.4rem 0 var(--ft-k); font-family: var(--ft-display); font-weight: 400; font-size: 1.2rem; }
        #ft .ft-plans a.rounded-2xl:hover { transform: translateY(0.14rem); box-shadow: 0 0.26rem 0 var(--ft-k); }
        .dark #ft .ft-plans a.rounded-2xl { box-shadow: 0 0.4rem 0 #000; }

        #ft .ft-keep > section { background: var(--ft-ground-2); border-top: 3px solid var(--ft-edge); }
        #ft .ft-keep h2 { font-family: var(--ft-display); font-weight: 400; font-size: clamp(2rem, 4vw, 2.8rem); line-height: 1.05; color: var(--ft-ink); }
        #ft .ft-keep p.uppercase { font-family: var(--ft-script); font-size: 1.4rem; font-weight: 400; letter-spacing: 0; text-transform: none; color: var(--ft-red-ink); }
        #ft .ft-keep .grid > a { border: 3px solid var(--ft-edge); border-radius: 1.2rem; background: var(--ft-card); }
        #ft .ft-keep .grid > a:hover { border-color: var(--ft-edge); box-shadow: 0 0.4rem 0 var(--ft-red); }
        #ft .ft-keep .grid > a > span:first-child { display: none; }
        #ft .ft-keep .grid > a h3 { color: var(--ft-ink); }
        #ft .ft-keep .grid > a p { color: var(--ft-ink-2); }
        #ft .ft-keep .grid > a > span:last-child,
        #ft .ft-keep a.self-start { color: var(--ft-red-ink); }

        /* ---------------------------------------------------------------
           Related pages: the next stops down the road
           --------------------------------------------------------------- */
        .ft-next-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1.2rem; }
        .ft-next { position: relative; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 3.4rem 1.4rem; margin-top: 4.2rem; }
        @media (min-width: 900px) {
            .ft-next { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.8rem; }
            .ft-next::before {
                content: "";
                position: absolute;
                top: -2.4rem;
                left: 12.5%;
                right: 12.5%;
                height: 0.6rem;
                border-radius: 1rem;
                background: var(--ft-ink);
            }
        }
        .ft-next a {
            position: relative;
            display: block;
            padding: 1.3rem 1.2rem 1.1rem;
            border: 3px solid var(--ft-edge);
            border-radius: 1.2rem;
            background: var(--ft-card);
            box-shadow: 0 0.45rem 0 var(--ft-edge);
            text-align: center;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .ft-next a:hover { transform: translateY(0.16rem); box-shadow: 0 0.29rem 0 var(--ft-edge); }
        .ft-next a:active { transform: translateY(0.45rem); box-shadow: 0 0 0 var(--ft-edge); }
        .ft-next a::before {
            content: "";
            position: absolute;
            top: -3.15rem;
            left: 50%;
            width: 2rem;
            height: 2rem;
            margin-left: -1rem;
            border-radius: 50%;
            border: 0.4rem solid var(--ft-ink);
            background: var(--a);
        }
        .ft-next small { display: block; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ft-ink-3); }
        .ft-next strong { display: block; margin-top: 0.25rem; font-family: var(--ft-display); font-weight: 400; font-size: clamp(1.25rem, 2.4vw, 1.7rem); line-height: 1.1; }

        /* ---------------------------------------------------------------
           07 Questions: asked at the hatch
           --------------------------------------------------------------- */
        .ft-qa { width: min(100%, 52rem); margin: clamp(2.4rem, 5vw, 3.6rem) auto 0; display: grid; gap: 1.1rem; counter-reset: ft-q; }
        .ft-qa details {
            counter-increment: ft-q;
            border: 3px solid var(--ft-edge);
            border-radius: 1.2rem;
            background: var(--ft-card);
            box-shadow: 0 0.4rem 0 var(--ft-edge);
        }
        .ft-qa summary {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: center;
            gap: 0.9rem;
            padding: 1.05rem 1.2rem;
            cursor: pointer;
        }
        .ft-qa summary::before {
            content: "Q" counter(ft-q, decimal-leading-zero);
            font-family: var(--ft-mono);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: var(--ft-ink-3);
        }
        .ft-qa h3 { font-size: 1.14rem; font-weight: 700; line-height: 1.3; }
        .ft-qa summary i {
            position: relative;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 50%;
            border: 3px solid var(--ft-k);
            background: var(--ft-m);
            transition: rotate 0.3s cubic-bezier(0.34, 1.6, 0.64, 1), background-color 0.2s ease;
        }
        .ft-qa summary i::before,
        .ft-qa summary i::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0.95rem;
            height: 0.2rem;
            margin: -0.1rem 0 0 -0.475rem;
            border-radius: 1rem;
            background: var(--ft-k);
        }
        .ft-qa summary i::after { rotate: 90deg; }
        .ft-qa details[open] summary i { rotate: 45deg; background: var(--ft-t); }
        .ft-qa details p { padding: 0 1.3rem 1.4rem; color: var(--ft-ink-2); }

        /* ---------------------------------------------------------------
           Finale: the back of the truck, and a plate to fill in
           --------------------------------------------------------------- */
        .ft-finale { position: relative; overflow: clip; padding-block: clamp(3rem, 6vw, 5rem) clamp(5rem, 8vw, 7rem); }
        .ft-rear {
            position: relative;
            padding: clamp(3.2rem, 6vw, 5rem) clamp(1.3rem, 4vw, 3rem) clamp(5.6rem, 8vw, 6.8rem);
            border-radius: 2.2rem 2.2rem 1rem 1rem;
            background-color: var(--ft-truck);
            background-image:
                linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0) 24%, rgba(0, 0, 0, 0) 68%, rgba(0, 0, 0, 0.2)),
                linear-gradient(#ffc629, #ffc629),
                linear-gradient(#fff7e6, #fff7e6),
                radial-gradient(circle, rgba(255, 255, 255, 0.42) 0 2px, rgba(255, 255, 255, 0) 2.6px);
            background-size: 100% 100%, 100% 0.85rem, 100% 0.2rem, 2.6rem 1rem;
            background-position: 0 0, 0 calc(100% - 3.6rem), 0 calc(100% - 4.85rem), 0.7rem 0.5rem;
            background-repeat: no-repeat, no-repeat, no-repeat, repeat-x;
            box-shadow: inset 0 0 0 3px rgba(0, 0, 0, 0.16), 0 2.4rem 3rem -1.8rem rgba(28, 21, 18, 0.6);
            color: var(--ft-c);
            text-align: center;
        }
        .ft-rear > .ft-burst { position: absolute; top: -2.2rem; right: clamp(0.6rem, 6vw, 4rem); width: 7rem; font-size: 1.3rem; --r: 12deg; }
        .ft-rear-h2 { font-size: clamp(2.6rem, 7.4vw, 5.6rem); line-height: 1; text-wrap: balance; }
        .ft-rear-sub { max-width: 34rem; margin: 1.3rem auto 0; font-size: clamp(1.1rem, 1.6vw, 1.3rem); font-weight: 700; }
        .ft-plate-form { display: grid; gap: 1.3rem; justify-items: center; width: min(100%, 33rem); margin: 2.3rem auto 0; }
        #ft .ft-plate {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            min-width: 0;
            padding: 1rem 1.9rem;
            border: 3px solid #1c1512;
            border-radius: 0.7rem;
            background: #ffc629;
            color: #1c1512;
            font-family: var(--ft-mono);
            font-size: clamp(0.95rem, 3.6vw, 1.3rem);
            font-weight: 700;
            box-shadow: inset 0 0 0 0.22rem #ffc629, inset 0 0 0 0.34rem rgba(28, 21, 18, 0.55), 0 0.5rem 0 rgba(0, 0, 0, 0.35);
            transition: box-shadow 0.2s ease;
        }
        /* Two bolts hold the plate on. */
        #ft .ft-plate::before,
        #ft .ft-plate::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 0.5rem;
            height: 0.5rem;
            margin-top: -0.25rem;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, #f4f6f8, #7b828a);
            box-shadow: 0 0 0 1px rgba(28, 21, 18, 0.6);
        }
        #ft .ft-plate::before { left: 0.7rem; }
        #ft .ft-plate::after { right: 0.7rem; }
        #ft .ft-plate:focus-within {
            border-color: #1c1512;
            box-shadow: inset 0 0 0 0.22rem #ffc629, inset 0 0 0 0.34rem rgba(28, 21, 18, 0.55), 0 0 0 0.3rem rgba(255, 247, 230, 0.75);
        }
        #ft .ft-plate input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            color: #1c1512;
            box-shadow: none;
            outline: none;
        }
        #ft .ft-plate input::placeholder { color: #6d5200; opacity: 1; }
        .ft-plate span { flex: none; color: #4a3600; user-select: none; }
        .ft-rear-note { margin-top: 1.2rem; font-weight: 700; }
        .ft-bumper {
            position: absolute;
            left: -1.2%;
            right: -1.2%;
            bottom: 1.1rem;
            height: 1.5rem;
            border-radius: 0.5rem;
            background: linear-gradient(#f4f6f8, #adb3ba 55%, #7b828a);
            box-shadow: 0 0.6rem 0.8rem -0.3rem rgba(0, 0, 0, 0.55);
        }
        .ft-bumper i {
            position: absolute;
            bottom: 2.1rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: radial-gradient(circle at 40% 35%, #ffb3a6, #e02d1b 45%, #8a170b);
            box-shadow: inset 0 0 0 0.16rem rgba(0, 0, 0, 0.35);
        }
        .ft-bumper i:nth-child(1) { left: 5%; }
        .ft-bumper i:nth-child(2) { right: 5%; }
        .dark .ft-bumper i { box-shadow: inset 0 0 0 0.16rem rgba(0, 0, 0, 0.35), 0 0 1.4rem 0.3rem rgba(240, 67, 47, 0.75); }
        .ft-tyre {
            position: absolute;
            bottom: -1.5rem;
            left: var(--x);
            width: 3.4rem;
            height: 2.7rem;
            border-radius: 0 0 0.7rem 0.7rem;
            background: repeating-linear-gradient(90deg, #17120f 0 0.34rem, #2c231e 0.34rem 0.68rem);
        }
        .ft-finale .ft-road { bottom: -2.3rem; }
        @media (max-width: 640px) {
            .ft-rear { padding-top: 4.6rem; }
            .ft-rear > .ft-burst { top: -2.6rem; right: 0.5rem; width: 5.8rem; font-size: 1.05rem; }
            /* The box you type in stays at 16px, under which iOS zooms the page on focus. The
               suffix gives up the room instead, so "your-truck" is whole on a 360px phone, and
               the two sizes sit on one baseline (the input still fills the plate's height). */
            #ft .ft-plate { align-items: baseline; padding-inline: 1.25rem; font-size: clamp(0.75rem, 3.3vw, 1rem); }
            #ft .ft-plate input { align-self: baseline; font-size: 1rem; }
            #ft .ft-plate::before { left: 0.4rem; }
            #ft .ft-plate::after { right: 0.4rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .ft-burst,
            .ft-swag i,
            .ft-stop.is-today .ft-stop-dot { animation: none; }
            .ft-btn, .ft-hang, .ft-ticket, .ft-stall, .ft-next a, .ft-qr-sticker, .ft-stop-dot, .ft-qa summary i, .ft-menu a { transition: none; }
        }
    </style>

    @php
        // The week is the single source of truth: the today board reads
        // from the SAME array the week list renders, so the two cannot
        // drift apart. Thursday is deliberately absent rather than struck
        // through - a date exception removes the date, and a cancelled
        // event is hidden, so a customer sees the day simply not there.
        $week = [
            ['Mon', 'Mill Lane Office Park', '11:30am', '2:00pm', true],
            ['Tue', 'Mill Lane Office Park', '11:30am', '2:00pm', false],
            ['Wed', 'Northgate Brewery',     '5:00pm',  '9:00pm', false],
            ['Fri', 'Riverside Market',      '12:00pm', '8:00pm', false],
            ['Sat', 'Riverside Market',      '10:00am', '8:00pm', false],
        ];
        $todayStop = null;
        foreach ($week as $row) {
            if ($row[4]) {
                $todayStop = $row;
                break;
            }
        }
        $faqs = [
            [
                'q' => 'Is Event Schedule free for food trucks?',
                'a' => 'The parts you use every week are free forever: your public schedule and its list layout, the regular pitches as recurring events, date exceptions for the weeks you lose a spot, an address and map on every stop, a QR code for the serving window, booking requests for catering, sub-schedules, two-way calendar sync, a calendar feed your customers can subscribe to, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Free registration with a capacity is free as well, for a supper club you are not charging for. Putting a price on a seat, at a supper club or a collaboration night, is Pro at '.plan_price($proMonthly).' a month. Zero platform fees on sales either way.',
            ],
            [
                'q' => 'How do customers know where I am today?',
                'a' => 'They open the one link you have been sharing all along. Set the layout to List and it reads as a route, opening on the next stop with the ones you have already done below a divider. Nothing has to be reposted, so the link is right on a Tuesday in February without you touching it.',
            ],
            [
                'q' => 'Do my followers get an alert when I add a stop?',
                'a' => 'If they left you an email address, yes. New stops reach them as one digest covering the batch, so a week of pitches posted on a Sunday is one message rather than five, and it never comes more than once every few days. Somebody who followed you from their own account is a separate list, and that one only hears from a newsletter you write: ten emails a month on the free plan and a hundred on Pro, counted per recipient rather than per send. Either way it is a list you own rather than a feed that decides who sees you.',
            ],
            [
                'q' => 'Can customers put my stops in their own calendar?',
                'a' => 'Yes, without giving you an email address. Your page offers a subscription to your calendar in its sign-up panel, and every stop offers the same thing in its Add to Calendar menu. Subscribe once and each new stop turns up in their calendar, the regular pitches included, and a stop you move, cancel or take out with a date exception changes there too. It is a live feed rather than a one-off download, and it is free on every plan.',
            ],
            [
                'q' => 'What happens the week I lose a pitch?',
                'a' => 'Add a date exception for that date and the stop is simply not on the schedule that week. The rest of the recurring pattern carries on untouched, so you are not rebuilding the week around one cancellation.',
            ],
            [
                'q' => 'Can people book me for catering and private events?',
                'a' => 'Yes. Turn on booking requests and people can ask to book you, with their own date and details attached. Every request waits for you to accept it, and you get an email when a new one lands, so an enquiry does not sit unread in a comment thread.',
            ],
        ];
        $dotSections = [
            ['top', "Today's stop"],
            ['today', 'The link'],
            ['week', 'The week'],
            ['sticker', 'The window'],
            ['catering', 'Catering'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];

        // The route strip reads the same $week, slot by slot, so a day with no row is a gap
        // on the line rather than something drawn. Each pitch keeps one line colour.
        $ftDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $ftByDay = [];
        foreach ($week as $row) {
            $ftByDay[$row[0]] = $row;
        }
        $ftLines = [];
        foreach ($week as $row) {
            if (! isset($ftLines[$row[1]])) {
                $ftLines[$row[1]] = ['a', 'b', 'c'][count($ftLines) % 3];
            }
        }
        $ftArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $ftDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="ft">

        <!-- Section rail: the page as a route of stops -->
        <nav class="ft-dotnav es-dotnav" aria-label="Page sections">
            <ul>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li>
                        <a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}">
                            <span aria-hidden="true">{{ $sectionLabel }}</span>
                            <i aria-hidden="true"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the side of the truck, and today's stop in the hatch -->
        <!-- ============================================================ -->
        <section id="top" class="ft-hero">
            <div class="ft-wrap">
                <div class="ft-bulbs" aria-hidden="true">
                    <div class="ft-swag">@for ($i = 0; $i < 9; $i++)<i style="--i: {{ $i }};"></i>@endfor</div>
                    <div class="ft-swag">@for ($i = 0; $i < 9; $i++)<i style="--i: {{ $i }};"></i>@endfor</div>
                </div>

                <div class="ft-truck">
                    <div class="ft-arch" style="--x: 9%;" aria-hidden="true"></div>
                    <div class="ft-arch" style="--x: calc(91% - 8.6rem);" aria-hidden="true"></div>
                    <div class="ft-road" aria-hidden="true"></div>
                    <div class="ft-wheel" style="--x: 9%;" aria-hidden="true"></div>
                    <div class="ft-wheel" style="--x: calc(91% - 8.6rem);" aria-hidden="true"></div>

                    <div class="ft-copy">
                        <h1 class="ft-d ft-h1">
                            <x-marketing.hero-eyebrow class="ft-eyebrow es-fade-up es-d-1">Food truck schedule, for carts and mobile kitchens too</x-marketing.hero-eyebrow>
                            <span class="ft-h1-line es-fade-up es-d-1">Your address is</span>
                            <span class="ft-h1-line es-fade-up es-d-2"><span class="ft-swipe">the news</span>.</span>
                        </h1>

                        <p class="ft-lede es-fade-up es-d-3">
                            A restaurant has one for good. You have a new one every week, and you are
                            retyping it into a post that expires by Friday. Put the whole route on one
                            link instead.
                        </p>

                        <div class="ft-cta es-fade-up es-d-4">
                            <a href="{{ app_url('/sign_up?type=talent') }}" class="ft-btn">
                                Put your route online
                                {!! $ftArrow !!}
                            </a>
                            <a href="#today" class="ft-btn ft-btn-cream">
                                See how the link works
                                {!! $ftDown !!}
                            </a>
                        </div>
                    </div>

                    <!-- The hatch. Today is outsized; the week sits beneath it. -->
                    <div class="ft-hatch es-fade-up es-d-3">
                        <div class="ft-awning" aria-hidden="true"></div>
                        <div class="ft-window">
                            <p class="ft-today-tag">Today</p>
                            <p class="ft-d ft-today-place">{{ $todayStop[1] }}</p>
                            <p class="ft-today-hours">{{ $todayStop[2] }} &ndash; {{ $todayStop[3] }}</p>

                            <p class="ft-rest-title">The rest of the week</p>
                            <ul class="ft-rest">
                                @foreach ($week as [$wDay, $wPlace, $wFrom, $wTo, $wIsToday])
                                    @continue($wIsToday)
                                    <li>
                                        <span>{{ $wDay }}</span>
                                        <span>{{ $wPlace }}</span>
                                        <span>{{ $wFrom }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="ft-gap-note">
                                No Thursday this week. It is not crossed out, it is just not there.
                            </p>
                        </div>
                        <div class="ft-ledge" aria-hidden="true"></div>
                        <span class="ft-burst ft-burst-teal" aria-hidden="true">Order<br>here</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The link (01): three tickets on the rail                  -->
        <!-- ============================================================ -->
        <section id="today" class="ft-section" style="scroll-margin-top: 4.5rem; padding-top: clamp(3rem, 6vw, 5rem);">
            <div class="ft-wrap">
                <div class="ft-head-center">
                    <p class="ft-kicker" data-reveal><span class="ft-no" aria-hidden="true">No. 01</span>The link</p>
                    <h2 class="ft-d ft-h2" data-reveal style="--reveal-delay: 0.08s;">
                        A post expires. <span class="ft-swipe">A link does not</span>.
                    </h2>
                    <p class="ft-sub" data-reveal style="--reveal-delay: 0.16s;">
                        You already write the week out every Sunday. Write it once somewhere that keeps
                        it, and put that address on the truck, the socials and the receipt.
                    </p>
                </div>

                <div class="ft-pass">
                    <div class="ft-rail" aria-hidden="true"></div>
                    <div class="ft-tickets">
                        @foreach ([
                            ['One address, all year', 'The same link in every bio and on every flyer. It is never last week\'s post, because there is no post to go stale.'],
                            ['Reads as a route', 'Set the layout to List and the schedule reads as a run of stops with the next one at the top, rather than a month grid to decode.'],
                            ['The next stop is first', 'The list opens on what is coming, with everything you have already done tucked below a divider. Nobody has to work out which line is current.'],
                        ] as [$t, $d])
                            <article class="ft-hang" data-reveal="ticket" style="--r: {{ ['-2.2deg', '1.4deg', '-1deg'][$loop->index] }}; --reveal-delay: {{ $loop->index * 0.14 }}s;">
                                <i class="ft-clip" aria-hidden="true"></i>
                                <div class="ft-ticket">
                                    <p class="ft-ticket-top" aria-hidden="true"><span>Order {{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}</span><span>Order up</span></p>
                                    <h3 class="ft-d">{{ $t }}</h3>
                                    <p>{{ $d }}</p>
                                    <div class="ft-barcode" aria-hidden="true"></div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>

                <p class="ft-free-row is-center" data-reveal>
                    <span class="ft-free">Free</span>
                    <span class="ft-free-note">The schedule, the list layout and the address are all on the free plan.</span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The week that repeats (02): the timetable and the route   -->
        <!-- ============================================================ -->
        <section id="week" class="ft-section ft-alt" style="scroll-margin-top: 4.5rem;">
            <div class="ft-wrap ft-week-body">
                <div class="ft-week-grid">
                    <div>
                        <p class="ft-kicker" data-reveal><span class="ft-no" aria-hidden="true">No. 02</span>The week</p>
                        <h2 class="ft-d ft-h2" data-reveal style="--reveal-delay: 0.08s;">
                            Most of your week <span class="ft-swipe">already repeats</span>.
                        </h2>
                        <p class="ft-sub" data-reveal style="--reveal-delay: 0.16s;">
                            The office park every Monday and Tuesday, the brewery on Wednesday, the
                            market at the weekend. Each regular pitch is one recurring event with its
                            own hours, not fifty entries you retype.
                        </p>

                        <ul class="ft-notes">
                            @foreach ([
                                ['One pitch, one event', 'Pick the days it runs and the hours you serve. Change the hours once and every future date follows.'],
                                ['The week you lose it', 'Take that single date out with an exception. The stop is not on the schedule that week and the pattern carries on.'],
                                ['One-offs sit alongside', 'A festival or a private booking is just another event on the same link, so the route stays in one place.'],
                            ] as [$t, $d])
                                <li data-reveal>
                                    <span class="ft-pin" aria-hidden="true"></span>
                                    <span><span class="ft-note-t">{{ $t }}</span> <span class="ft-note-d">- {{ $d }}</span></span>
                                </li>
                            @endforeach
                        </ul>

                        <p class="ft-free-row" data-reveal>
                            <span class="ft-free">Free</span>
                            <span class="ft-free-note">Recurring stops and date exceptions are on the free plan.</span>
                        </p>
                    </div>

                    <div class="ft-timetable" data-reveal="panel">
                        <div class="ft-timetable-head">
                            <h3 class="ft-d">Your pitches</h3>
                            <span>3 recurring events</span>
                        </div>
                        @foreach ([
                            ['Mill Lane Office Park', 'Mon &amp; Tue', '11:30am &ndash; 2:00pm'],
                            ['Northgate Brewery', 'Wed', '5:00pm &ndash; 9:00pm'],
                            ['Riverside Market', 'Fri &amp; Sat', 'from 10:00am'],
                        ] as [$pName, $pDays, $pHours])
                            <div class="ft-pitch" data-line="{{ $ftLines[$pName] ?? '' }}">
                                <i aria-hidden="true"></i>
                                <div>
                                    <p>{{ $pName }}</p>
                                    <p>{!! $pDays !!} &middot; {!! $pHours !!}</p>
                                </div>
                            </div>
                        @endforeach
                        <div class="ft-except">
                            <p>Date exceptions</p>
                            <p>
                                Wed 12 Aug taken out at Northgate. Every other Wednesday is unaffected.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- The route: the same $week, as stops on a line. Thursday has no row, so it is a gap. -->
                <div class="ft-route" aria-hidden="true" dir="ltr">
                    <span class="ft-marker"></span>
                    @foreach ($ftDays as $ftDay)
                        @php $ftStop = $ftByDay[$ftDay] ?? null; @endphp
                        @if ($ftStop)
                            <div @class(['ft-stop', 'is-today' => $ftStop[4]]) data-line="{{ $ftLines[$ftStop[1]] ?? '' }}">
                                <span class="ft-stop-dot"></span>
                                <p class="ft-stop-day">{{ $ftDay }}@if ($ftStop[4])<span class="ft-stop-today">Today</span>@endif</p>
                                <p class="ft-stop-place">{{ $ftStop[1] }}</p>
                                <p class="ft-stop-hours">{{ $ftStop[2] }} &ndash; {{ $ftStop[3] }}</p>
                            </div>
                        @else
                            <div class="ft-stop is-gap">
                                <p class="ft-stop-day">{{ $ftDay }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The sticker in the window (03): a tray on the liner       -->
        <!-- ============================================================ -->
        <section id="sticker" class="ft-liner" style="scroll-margin-top: 4.5rem;">
            <div class="ft-wrap ft-liner-in">
                <div class="ft-greaseproof" aria-hidden="true"></div>
                <div class="ft-tray">
                    <div class="ft-tray-side">
                        <div class="ft-qr-sticker" data-reveal="zoom">
                            <p class="ft-qr-tag">In the window</p>
                            <div class="ft-qr" aria-hidden="true">
                                @for ($qrRow = 0; $qrRow < 12; $qrRow++)
                                    @for ($qrCol = 0; $qrCol < 12; $qrCol++)
                                        @php
                                            $qrFinder = ($qrRow < 4 && $qrCol < 4) || ($qrRow < 4 && $qrCol > 7) || ($qrRow > 7 && $qrCol < 4);
                                            $qrOn = ! $qrFinder && ((($qrRow * 7 + $qrCol * 13 + ($qrRow * $qrCol) % 5) % 3) !== 0);
                                        @endphp
                                        <i @class(['on' => $qrOn])></i>
                                    @endfor
                                @endfor
                                <b></b><b></b><b></b>
                            </div>
                            <p class="ft-d ft-qr-q">Where are we tomorrow?</p>
                            <p class="ft-qr-a">
                                One scan and they have the whole route, and the option to follow.
                            </p>
                        </div>
                        <p class="ft-qr-cap" data-reveal>
                            The QR downloads from your Followers page and points at your schedule.
                        </p>
                    </div>

                    <div>
                        <p class="ft-kicker" data-reveal><span class="ft-no" aria-hidden="true">No. 03</span>The window</p>
                        <h2 class="ft-d ft-h2" data-reveal style="--reveal-delay: 0.08s;">
                            They already <span class="ft-swipe">came to you once</span>.
                        </h2>
                        <p class="ft-sub" data-reveal style="--reveal-delay: 0.16s;">
                            Somebody standing at the window has found you at your hardest moment - when
                            they did not know where you were. A sticker beside the hatch is how that
                            becomes a second visit.
                        </p>

                        <ol class="ft-combo">
                            @foreach ([
                                ['Print the QR, tape it up', 'Download it from your Followers page. It points at your schedule, so it never needs reprinting when the route changes.'],
                                ['They leave an email, you get the address', 'With their consent, confirmed by a link, and they can leave whenever they like. It is your list, not a platform\'s.'],
                                ['New stops go out on their own', 'Put next week up and a short digest reaches everyone who confirmed, at most one every three days, and it does not touch your newsletter allowance.'],
                                ['You write the week when there is more to say', 'A newsletter is the one you send yourself, for the specials and the closures a list of dates cannot carry. Ten a month free and a hundred on Pro, counted per recipient rather than per send.'],
                                ['Or they put you in their calendar', 'The same page offers a subscription to your calendar, and it asks for no email. Every new stop appears in their own calendar, and one you move or take out changes there too.'],
                            ] as [$t, $d])
                                <li data-reveal>
                                    <div>
                                        <p class="ft-combo-t">{{ $t }}</p>
                                        <p class="ft-combo-d">{{ $d }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>

                        <p class="ft-free-row" data-reveal>
                            <span class="ft-free">Free</span>
                            <span class="ft-free-note">The QR, the followers and the <x-link href="{{ marketing_url('/docs/sharing#calendar-feeds') }}">calendar feed</x-link> cost nothing; the free newsletter allowance is ten emails a month, counted per recipient.</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Catering (04): the painted mustard sign                   -->
        <!-- ============================================================ -->
        <section id="catering" class="ft-mustard" style="scroll-margin-top: 4.5rem;">
            <div class="ft-wrap">
                <div class="ft-head-center">
                    <p class="ft-kicker" data-reveal><span class="ft-no" aria-hidden="true">No. 04</span>Catering</p>
                    <h2 class="ft-d ft-h2" data-reveal style="--reveal-delay: 0.08s;">
                        The bookings that <span class="ft-swipe">pay for January</span>.
                    </h2>
                    <p class="ft-sub" data-reveal style="--reveal-delay: 0.16s;">
                        A wedding or an office lunch is worth a fortnight of service, and it usually
                        arrives as a message somebody nearly missed.
                    </p>
                </div>

                <div class="ft-asks" data-reveal-group="120">
                    @foreach ([
                        ['They ask through your page', 'Turn on booking requests and anyone can ask to book you, with their date and details attached, instead of a comment you scroll past.'],
                        ['You get an email', 'A new request is emailed to you, so an enquiry worth a fortnight of trading does not sit unread.'],
                        ['Nothing posts without you', 'Every request waits for you to accept it. Nothing appears on your public schedule that you did not agree to.'],
                    ] as [$t, $d])
                        <article class="ft-ask" data-reveal>
                            <span class="ft-burst" style="--r: {{ ['-10deg', '8deg', '-6deg'][$loop->index] }};" aria-hidden="true">{{ $loop->iteration }}</span>
                            <h3 class="ft-d">{{ $t }}</h3>
                            <p>{{ $d }}</p>
                        </article>
                    @endforeach
                </div>

                <p class="ft-free-row is-center" data-reveal>
                    <span class="ft-free">Free</span>
                    <span class="ft-free-note">
                        Booking requests are on the free plan. A confirmed private booking can stay a Draft if you would rather it did not show up on the public route.
                    </span>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Who it is for (05): a street of stalls                    -->
        <!-- ============================================================ -->
        <section id="who" class="ft-section" style="scroll-margin-top: 4.5rem;">
            <div class="ft-wrap">
                <div class="ft-head-center">
                    <p class="ft-kicker" data-reveal><span class="ft-no" aria-hidden="true">No. 05</span>Who it is for</p>
                    <h2 class="ft-d ft-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Anything with <span class="ft-swipe">wheels and a hatch</span>.
                    </h2>
                </div>

                @php
                    // Name, description, blog slug, and the colour of the stall's awning.
                    $ftStalls = [
                        ['Taco Trucks', 'The same three pitches most weeks, with the late-night one that only runs in summer taken out for the winter.', 'for-taco-trucks', '#e02d1b'],
                        ['Festival Vendors', 'A summer of one-off weekends, each with its own dates and gates, on the same link as the regular pitches.', 'for-festival-vendors', '#1fb6a6'],
                        ['Pop-Up Restaurants', 'A short residency in somebody else\'s room, sold by the seat, with the dates ending on their own.', 'for-popup-kitchens', '#ffc629'],
                        ['Caterers', 'Almost all of it is private hire, so the bookings arrive as requests and the public page stays a shop window.', 'for-mobile-catering-businesses', '#1fb6a6'],
                        ['Coffee & Beverage Carts', 'Early, short and every weekday. One recurring morning that people can put in their own calendar.', 'for-coffee-beverage-carts', '#ffc629'],
                        ['BBQ & Smoker Trucks', 'You sell out and go home. Say the hours, and let people follow so they know to come early.', 'for-bbq-smoker-trucks', '#e02d1b'],
                    ];
                @endphp

                <div class="ft-stalls" data-reveal-group="80">
                    @foreach ($ftStalls as [$ftStallName, $ftStallDesc, $ftStallSlug, $ftStallAwning])
                        @php $ftStallPost = get_sub_audience_blog($ftStallSlug); @endphp
                        <article class="ft-stall" data-reveal style="--a: {{ $ftStallAwning }};">
                            <div class="ft-stall-awning" aria-hidden="true"></div>
                            <div class="ft-stall-board">
                                <h3 class="ft-d">{{ $ftStallName }}</h3>
                                <p>{{ $ftStallDesc }}</p>
                                @if ($ftStallPost)
                                    <a href="{{ blog_url('/' . $ftStallPost->slug) }}" class="ft-textlink" aria-label="Learn more about Event Schedule for {{ $ftStallName }}">
                                        Learn more
                                        {!! $ftArrow !!}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. How it works (06) and key features: the painted boards    -->
        <!-- ============================================================ -->
        <section id="how" class="ft-section ft-alt" style="scroll-margin-top: 4.5rem; padding-top: clamp(6rem, 10vw, 9rem);">
            <div class="ft-wrap">
                <div class="ft-board" data-reveal="panel">
                    <p class="ft-kicker"><span class="ft-no" aria-hidden="true">No. 06</span>How it works</p>
                    <h2 class="ft-d ft-h2">
                        A Sunday evening, <span class="ft-swipe">once</span>.
                    </h2>

                    <ol class="ft-specials">
                        @foreach ([
                            ['01', 'Put the regular pitches in', 'One recurring event per spot, with the days and the hours you serve. The one-offs go in as you get them.'],
                            ['02', 'Share the one address', 'On the truck, in every bio, on the receipt. It is the last time you have to update where it points.'],
                            ['03', 'Tape the QR to the hatch', 'People who already found you once can follow, and you can tell them where you are next week.'],
                        ] as [$n, $t, $d])
                            <li>
                                <p class="ft-d ft-special-n" aria-hidden="true">{{ $n }}</p>
                                <h3 class="ft-d">{{ $t }}</h3>
                                <p>{{ $d }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="ft-board" data-reveal="panel">
                    <p class="ft-kicker" aria-hidden="true">On the side</p>
                    <h2 class="ft-d ft-h2">Key features</h2>

                    @php
                        $ftMenu = [
                            ['Recurring Events', 'A regular pitch set up once, with exceptions for the weeks you lose it', marketing_url('/features/recurring-events')],
                            ['Newsletters', 'Send the week\'s route to the people who follow you', marketing_url('/features/newsletters')],
                            ['Sub-schedules', 'Keep markets, festivals and private hire on their own strands', marketing_url('/features/sub-schedules')],
                            ['Embed Calendar', 'Drop the route into the site you already have', marketing_url('/features/embed-calendar')],
                        ];
                    @endphp
                    <ul class="ft-menu">
                        @foreach ($ftMenu as [$ftMenuName, $ftMenuDesc, $ftMenuUrl])
                            <li>
                                <a href="{{ $ftMenuUrl }}">
                                    <span class="ft-d ft-menu-name">{{ $ftMenuName }}</span>
                                    <i class="ft-menu-lead" aria-hidden="true"></i>
                                    <span class="ft-menu-desc">{{ $ftMenuDesc }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ marketing_url('/features') }}" class="ft-textlink">
                        See all features
                        {!! $ftArrow !!}
                    </a>
                </div>
            </div>
        </section>

        <div class="ft-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 8. Related pages: the next stops down the road               -->
        <!-- ============================================================ -->
        <section class="ft-section ft-alt">
            <div class="ft-wrap">
                <div class="ft-next-head">
                    <div>
                        <p class="ft-kicker" aria-hidden="true" data-reveal>Next stops</p>
                        <h2 class="ft-d ft-h2" data-reveal style="--reveal-delay: 0.08s;">Related pages</h2>
                    </div>
                    <a href="{{ marketing_url('/use-cases') }}" class="ft-textlink" data-reveal>
                        See all use cases
                        {!! $ftArrow !!}
                    </a>
                </div>

                <div class="ft-next" data-reveal-group="80">
                    @foreach ([
                        ['/for-farmers-markets', 'Farmers Markets'],
                        ['/for-breweries-and-wineries', 'Breweries &amp; Wineries'],
                        ['/for-restaurants', 'Restaurants'],
                        ['/for-bars', 'Bars'],
                    ] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal style="--a: {{ ['#1fb6a6', '#ffc629', '#e02d1b', '#fff7e6'][$loop->index] }};">
                            <small>Event Schedule for</small>
                            <strong>{!! $relName !!}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. FAQ (07): asked at the hatch                              -->
        <!-- ============================================================ -->
        <section id="faq" class="ft-section" style="scroll-margin-top: 4.5rem;">
            <div class="ft-wrap">
                <div class="ft-head-center">
                    <p class="ft-kicker" data-reveal><span class="ft-no" aria-hidden="true">No. 07</span>Questions</p>
                    <h2 class="ft-d ft-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Asked <span class="ft-swipe">at the hatch</span>.
                    </h2>
                </div>

                <div class="ft-qa" data-reveal>
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
        <!-- 10. Finale: the back of the truck                            -->
        <!-- ============================================================ -->
        <section id="claim" class="ft-finale" style="scroll-margin-top: 4.5rem;">
            <div class="ft-wrap">
                <div class="ft-rear" id="ft-rear" data-reveal="panel">
                    <div class="ft-road" aria-hidden="true"></div>
                    <div class="ft-tyre" style="--x: 8%;" aria-hidden="true"></div>
                    <div class="ft-tyre" style="--x: calc(92% - 3.4rem);" aria-hidden="true"></div>

                    <p class="ft-burst ft-burst-teal">Free<br>forever</p>
                    <h2 class="ft-d ft-rear-h2">
                        Stop retyping <span class="ft-swipe">the week</span>.
                    </h2>
                    <p class="ft-rear-sub">
                        One address for the whole route, and a QR for the window that turns tonight's
                        queue into next week's.
                    </p>

                    <div class="ft-plate-form">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim ft-plate">
                            <input id="es-claim-input" type="text" placeholder="your-truck" autocomplete="off" spellcheck="false" maxlength="30">
                            <span>.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="ft-btn">
                            Put your route online
                            {!! $ftArrow !!}
                        </a>
                    </div>

                    <p class="ft-rear-note">No credit card required</p>

                    <div class="ft-bumper" aria-hidden="true"><i></i><i></i></div>
                </div>
            </div>
        </section>

        <div class="ft-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    {{-- The finale's confetti, in ketchup, mustard and truck teal rather than the site's blues. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            var rear = document.getElementById('ft-rear');
            if (!rear || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || typeof window.confetti !== 'function') {
                        return;
                    }
                    io.disconnect();
                    var paints = ['#e02d1b', '#ffc629', '#1fb6a6', '#fff7e6'];
                    // An instance of our own, drawn on the page's thread. The library's default
                    // cannon draws through a worker built from a blob, which the site's
                    // content security policy refuses without an error, so nothing was drawn.
                    var fire = window.confetti.create(null, { resize: true });
                    [[60, 0.06], [120, 0.94]].forEach(function (shot) {
                        fire({ particleCount: 70, angle: shot[0], spread: 58, startVelocity: 52, origin: { x: shot[1], y: 0.95 }, colors: paints, disableForReducedMotion: true });
                    });
                });
            }, { threshold: 0.55 });
            io.observe(rear);
        });
    </script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
