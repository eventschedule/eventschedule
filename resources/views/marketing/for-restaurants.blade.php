<x-marketing-layout>
    <x-slot name="title">Restaurant Event Ticketing | Know the Covers Before You Shop</x-slot>
    <x-slot name="description">A wine dinner for twenty-four means the kitchen buys for twenty-four. Sell the covers, close the door before you shop, and collect the allergies.</x-slot>
    <x-slot name="breadcrumbTitle">For Restaurants</x-slot>

    <x-slot name="headMeta">
        {{-- The page's own typefaces, from the fonts the app already bundles (never a CDN). --}}
        <link rel="stylesheet" href="{{ font_stylesheet_url('Cormorant Garamond') }}">
        <link rel="stylesheet" href="{{ font_stylesheet_url('Tenor Sans') }}">
    </x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Restaurants"
        description="Ticketed dinners with a fixed covers count, a sales cutoff set before you shop, and dietary questions answered at checkout."
        audience="Restaurants"
        keywords="restaurant event ticketing, wine dinner tickets, covers count, supper club booking, private dining enquiries, chef's table tickets" />
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
           For-restaurants "Prix Fixe" styles. The page is the menu for
           one evening, as a serious kitchen prints it: a tall card,
           courses centred, small caps, a great deal of white. And the
           room it describes, counted cover by cover.

           The argument has not changed: a restaurant already has a
           business, so its calendar is not the product. The few nights
           that need a page are seat-limited and prepaid, because the
           kitchen buys for a fixed number. The count is the product.

           The room in "The count" is a picture of that count, not a
           seating plan: nothing in it can be picked, and nothing on this
           page says a guest chooses a table. A restaurant sells covers.

           Everything is scoped under #rs with its own tokens. By night
           the wine stays a fill and the accent ink becomes candlelight,
           so the accent never has to be a lightened wine.
           ============================================================== */

        @property --rs-on { syntax: '<number>'; inherits: false; initial-value: 1; }
        @property --rs-n { syntax: '<integer>'; inherits: false; initial-value: 24; }
        @property --rs-glow { syntax: '<number>'; inherits: true; initial-value: 1; }

        #rs {
            --rs-linen: #f7f3ea;
            --rs-linen-2: #efe9dc;
            --rs-card: #fdfbf6;
            --rs-ink: #23201c;
            --rs-ink-2: #4a443c;
            --rs-ink-3: #675f54;
            --rs-line: rgba(35, 32, 28, 0.18);
            --rs-line-2: rgba(35, 32, 28, 0.09);
            --rs-weave: rgba(35, 32, 28, 0.017);
            --rs-accent: #6b1d2a;
            --rs-wine: #6b1d2a;
            --rs-wine-deep: #57141f;
            --rs-on-wine: #fbf6ec;
            --rs-olive: #55633a;
            --rs-brass: #a8854a;
            --rs-floor: #ebe4d4;
            --rs-cloth: #fffdf8;
            --rs-shade: rgba(35, 32, 28, 0.42);
            --rs-steel-1: #e4e3dd;
            --rs-steel-2: #8d8d86;
            --rs-steel-3: #c6c5be;
            --rs-serif: 'Cormorant Garamond', 'Cormorant', 'Hoefler Text', Garamond, 'Times New Roman', serif;
            --rs-sans: 'Tenor Sans', 'Gill Sans', 'Gill Sans MT', Optima, 'Segoe UI', sans-serif;
            --rs-it: 'Hoefler Text', Baskerville, 'Palatino Linotype', Palatino, Georgia, serif;
            position: relative;
            background-color: var(--rs-linen);
            /* The weave is one 4px tile. As two repeating gradients the length of the page, Safari
               could not keep the cross threads apart and drew them as bands of ruled lines. */
            background-image:
                linear-gradient(0deg, var(--rs-weave) 1px, transparent 1px),
                linear-gradient(90deg, var(--rs-weave) 1px, transparent 1px);
            background-size: 4px 4px;
            color: var(--rs-ink);
            font-family: var(--rs-serif);
            font-size: 1.3rem;
            line-height: 1.45;
            font-variant-numeric: oldstyle-nums;
        }
        .dark #rs {
            --rs-linen: #14100d;
            --rs-linen-2: #1a1511;
            --rs-card: #201a15;
            --rs-ink: #f1e8d8;
            --rs-ink-2: #d2c7b4;
            --rs-ink-3: #a99e8f;
            --rs-line: rgba(241, 232, 216, 0.2);
            --rs-line-2: rgba(241, 232, 216, 0.1);
            --rs-weave: rgba(241, 232, 216, 0.013);
            --rs-accent: #e6bd7a;
            --rs-wine: #7a2433;
            --rs-wine-deep: #641a28;
            --rs-olive: #b3c088;
            --rs-brass: #c9a565;
            --rs-floor: #1d1712;
            --rs-cloth: #e6dcc8;
            --rs-shade: rgba(0, 0, 0, 0.7);
            --rs-steel-1: #f1f0ea;
            --rs-steel-2: #a3a39c;
            --rs-steel-3: #dddcd5;
        }

        /* The bar above takes the linen, so the page reads as one cloth. */
        body > header.sticky {
            background-color: rgba(247, 243, 234, 0.9);
            border-bottom-color: rgba(35, 32, 28, 0.14);
        }
        .dark body > header.sticky {
            background-color: rgba(20, 16, 13, 0.9);
            border-bottom-color: rgba(241, 232, 216, 0.14);
        }

        #rs ::selection { background: var(--rs-wine); color: #fbf6ec; }
        #rs a:focus-visible,
        #rs summary:focus-visible,
        #rs input:focus-visible {
            outline: 2px solid var(--rs-accent);
            outline-offset: 3px;
        }
        #rs h2 { hanging-punctuation: first; }

        .rs-wrap { width: min(100% - 2.5rem, 72rem); margin-inline: auto; }
        .rs-narrow { width: min(100% - 2.5rem, 47rem); margin-inline: auto; }

        /* Voices: the serif sets everything, the sans is the small caps of the menu. */
        .rs-sc {
            font-family: var(--rs-sans);
            font-size: 0.72rem;
            font-weight: 400;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            line-height: 1.5;
            font-variant-numeric: lining-nums;
        }
        .rs-kick { font-family: var(--rs-it); font-style: italic; font-size: 1.05rem; color: var(--rs-ink-3); }
        .rs-tag { color: var(--rs-accent); }
        .rs-h2 {
            font-size: clamp(2.5rem, 5.6vw, 4.5rem);
            font-weight: 400;
            line-height: 1.02;
            letter-spacing: -0.012em;
            text-wrap: balance;
        }
        .rs-em { color: var(--rs-accent); }
        .rs-sub { max-width: 39rem; color: var(--rs-ink-2); font-size: 1.4rem; text-wrap: pretty; }
        #rs p { text-wrap: pretty; }

        .rs-sec { padding-block: clamp(4.5rem, 9vw, 8rem); border-top: 1px solid var(--rs-line); }
        .rs-alt { background-color: var(--rs-linen-2); }
        .rs-head { display: grid; justify-items: center; text-align: center; gap: 0.85rem; margin-bottom: clamp(2.5rem, 5vw, 4rem); }
        .rs-head-l { justify-items: start; text-align: start; margin-bottom: 1.6rem; }

        /* An ornament in place of a rule: two hairlines and a lozenge. */
        .rs-orn { display: flex; align-items: center; justify-content: center; gap: 0.9rem; color: var(--rs-brass); }
        .rs-orn::before,
        .rs-orn::after { content: ""; width: min(5.5rem, 22%); height: 1px; background: currentColor; }
        .rs-orn i { width: 0.42rem; aspect-ratio: 1; rotate: 45deg; background: var(--rs-accent); }

        /* The plate each course arrives on: porcelain in both modes. */
        .rs-dish {
            --d: 5.4rem;
            flex: none;
            width: var(--d);
            aspect-ratio: 1;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle closest-side, #fffdf8 0 61%, rgba(35, 32, 28, 0.13) 62% 63%, rgba(255, 253, 248, 0) 64%),
                radial-gradient(circle closest-side, rgba(168, 133, 74, 0) 0 91%, rgba(168, 133, 74, 0.75) 92% 94%, rgba(168, 133, 74, 0) 95%),
                radial-gradient(circle at 36% 28%, #ffffff 0, #f1e9da 78%);
            box-shadow: 0 0.55rem 1.1rem -0.5rem var(--rs-shade), inset 0 0 0 1px rgba(35, 32, 28, 0.1), inset 0 -0.25rem 0.5rem rgba(35, 32, 28, 0.07);
            color: #6b1d2a;
            font-family: var(--rs-sans);
            font-size: calc(var(--d) * 0.2);
            letter-spacing: 0.12em;
            font-variant-numeric: lining-nums;
        }

        /* Plan tiers, set like the small print at the foot of a menu. */
        .rs-tier {
            display: inline-block;
            padding: 0.22rem 0.55rem 0.16rem;
            border: 1px solid var(--rs-line);
            color: var(--rs-ink-2);
            font-size: 0.62rem;
            vertical-align: 0.18em;
        }
        .rs-tier-pro { border-color: var(--rs-wine); background: var(--rs-wine); color: var(--rs-on-wine); }
        .rs-note { max-width: 44rem; margin: 2.75rem auto 0; text-align: center; color: var(--rs-ink-2); font-size: 1.14rem; }
        .rs-note-l { margin-inline: 0; text-align: start; }
        .rs-note .rs-tier { margin-inline-end: 0.5rem; }
        #rs .rs-note a,
        #rs .rs-sub a {
            display: inline;
            color: var(--rs-accent);
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 0.2em;
        }
        #rs .rs-note a:hover { text-decoration-thickness: 2px; }

        /* Buttons: a ruled box, as a menu sets its one instruction. */
        .rs-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
            padding: 1.15rem 1.9rem 1.05rem;
            background: var(--rs-wine);
            color: var(--rs-on-wine);
            font-family: var(--rs-sans);
            font-size: 0.8rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            line-height: 1.2;
            box-shadow: inset 0 0 0 4px var(--rs-wine), inset 0 0 0 5px rgba(251, 246, 236, 0.45);
            transition: background-color 0.25s ease, translate 0.25s ease, box-shadow 0.25s ease;
        }
        .rs-btn:hover {
            background: var(--rs-wine-deep);
            translate: 0 -2px;
            box-shadow: inset 0 0 0 4px var(--rs-wine-deep), inset 0 0 0 5px rgba(251, 246, 236, 0.6), 0 1.1rem 1.6rem -1rem var(--rs-shade);
        }
        .rs-btn svg { width: 1.05rem; height: 1.05rem; transition: translate 0.25s ease; }
        .rs-btn:hover svg { translate: 0.3rem 0; }
        /* The top padding and its negative margin bring the link to 24px tall, the least a
           standalone link may be, without moving it or its rule. */
        .rs-more {
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            margin-top: -0.1rem;
            padding-top: 0.1rem;
            padding-bottom: 0.3rem;
            border-bottom: 1px solid var(--rs-accent);
            color: var(--rs-ink);
            transition: gap 0.25s ease, color 0.25s ease;
        }
        .rs-more:hover { gap: 1.1rem; color: var(--rs-accent); }
        .rs-more svg { width: 1rem; height: 1rem; }

        /* ---------------------------------------------------------------
           The courses: a quiet index down the edge, on wide screens only
           --------------------------------------------------------------- */
        .rs-courses { display: none; }
        @media (min-width: 1380px) {
            /* The nav is a box the size of the page that clips the rail, so the rail stays fixed
               to the screen and still ends where the page does, instead of riding on over the
               site footer. */
            .rs-courses { display: block; position: absolute; inset: 0; z-index: 40; clip-path: inset(0); pointer-events: none; }
            .rs-courses ol { position: fixed; right: 1.4rem; top: 50%; translate: 0 -50%; display: grid; gap: 0.2rem; pointer-events: auto; }
            .rs-courses a { display: flex; align-items: center; justify-content: flex-end; gap: 0.7rem; padding: 0.4rem 0.2rem; color: var(--rs-ink-3); }
            .rs-courses a span { opacity: 0; translate: 0.3rem 0; font-size: 0.6rem; white-space: nowrap; transition: opacity 0.25s ease, translate 0.25s ease; }
            .rs-courses a:hover span,
            .rs-courses a:focus-visible span { opacity: 1; translate: 0 0; }
            /* Every mark carries a ring of the linen itself: unseen on the page, and what keeps
               the marks (the wine one above all) from sinking into the wine cloth of "How it
               works" while the rail passes over it. */
            .rs-courses a i { width: 0.42rem; aspect-ratio: 1; rotate: 45deg; border: 1px solid currentColor; box-shadow: 0 0 0 1px var(--rs-linen); transition: scale 0.3s ease, background-color 0.3s ease, border-color 0.3s ease; }
            .rs-courses a.is-active i { scale: 1.4; background: var(--rs-accent); border-color: var(--rs-accent); }
        }

        /* ---------------------------------------------------------------
           Hero: the card for one evening
           --------------------------------------------------------------- */
        .rs-hero { position: relative; overflow: clip; }
        .dark .rs-hero::before {
            content: "";
            position: absolute;
            right: -6rem;
            top: -4rem;
            width: 58rem;
            aspect-ratio: 1;
            background: radial-gradient(circle closest-side, rgba(255, 190, 96, calc(0.17 * var(--rs-glow))), rgba(255, 190, 96, 0) 72%);
            pointer-events: none;
            animation: rs-flicker 9s ease-in-out infinite;
        }
        @keyframes rs-flicker {
            0%, 100% { --rs-glow: 1; }
            18% { --rs-glow: 0.86; }
            31% { --rs-glow: 0.97; }
            47% { --rs-glow: 0.8; }
            63% { --rs-glow: 0.95; }
            82% { --rs-glow: 0.88; }
        }
        .rs-masthead {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.4rem;
            padding-top: clamp(1.6rem, 3.4vw, 2.6rem);
            color: var(--rs-ink-3);
            font-size: 0.66rem;
            white-space: nowrap;
        }
        .rs-masthead::before,
        .rs-masthead::after { content: ""; flex: 1; height: 1px; background: var(--rs-line); }
        .rs-hero-grid {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 3.5rem;
            align-items: center;
            padding-block: clamp(2.6rem, 5vw, 4rem) clamp(4rem, 8vw, 6.5rem);
        }
        @media (min-width: 1024px) {
            .rs-hero-grid { grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr); gap: 4.5rem; min-height: calc(92svh - 4rem - 5rem); }
        }
        .rs-setting-hero { position: relative; display: grid; place-items: center; padding-block: clamp(1rem, 4vw, 3rem); }
        .rs-setting-hero .rs-charger { width: min(44rem, 150%); }
        .rs-copy { container-type: inline-size; min-width: 0; }
        .rs-eyebrow {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.6rem;
            font-family: var(--rs-sans);
            font-size: 0.76rem;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            line-height: 1.5;
            color: var(--rs-accent);
        }
        .rs-eyebrow::after { content: ""; flex: none; width: 3.5rem; height: 1px; background: var(--rs-brass); }
        @media (max-width: 480px) {
            .rs-eyebrow { font-size: 0.7rem; letter-spacing: 0.22em; }
            .rs-eyebrow::after { display: none; }
            .rs-btn { padding-inline: 1.2rem; letter-spacing: 0.17em; font-size: 0.76rem; gap: 0.6rem; }
        }
        .rs-h1 { font-size: clamp(2.7rem, 14cqi, 6.1rem); font-weight: 400; line-height: 0.98; letter-spacing: -0.015em; }
        .rs-h1 .es-mask-line { white-space: nowrap; }
        .rs-lede { max-width: 32rem; margin-top: 2rem; color: var(--rs-ink-2); font-size: clamp(1.3rem, 1.9vw, 1.5rem); }
        /* Two columns on a small laptop: the charger is half as wide again as its column and
           comes over the gap into this one, where the ends of these lines ran under its rim. */
        @media (min-width: 1024px) and (max-width: 1199px) {
            .rs-lede { max-width: min(32rem, 100% - 3.5rem); }
        }
        .rs-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 1.4rem 2.2rem; margin-top: 2.4rem; }

        .rs-menu {
            position: relative;
            width: min(100%, 28.5rem);
            margin-inline: auto;
            padding: 3rem 2.4rem 2.4rem;
            container-type: inline-size;
            background: var(--rs-card);
            text-align: center;
            box-shadow: 0 0 0 1px var(--rs-line), 0 2.6rem 3.6rem -2.2rem var(--rs-shade);
        }
        .rs-menu::before {
            content: "";
            position: absolute;
            inset: 0.6rem;
            border: 1px solid var(--rs-brass);
            opacity: 0.6;
            pointer-events: none;
        }
        .rs-menu > * { position: relative; }
        .rs-fleuron {
            display: block;
            margin-bottom: 0.7rem;
            font-family: 'Zapf Dingbats', 'Apple Symbols', 'Segoe UI Symbol', 'Noto Sans Symbols2', 'Noto Sans Symbols', serif;
            font-variant-emoji: text;
            font-size: 1.5rem;
            line-height: 1;
            color: var(--rs-accent);
        }
        .rs-menu-name { color: var(--rs-ink); font-size: 0.82rem; letter-spacing: 0.3em; }
        .rs-menu-when { margin-top: 0.35rem; color: var(--rs-ink-2); font-size: 1.22rem; }
        .rs-menu .rs-orn { margin-block: 1.4rem; }
        .rs-fig { font-size: 34cqi; font-weight: 400; line-height: 0.82; color: var(--rs-accent); font-variant-numeric: lining-nums; }
        .rs-fig span { display: inline-block; margin-inline-start: 0.5rem; color: var(--rs-ink-3); font-size: 0.82rem; vertical-align: 0.9em; }
        .rs-fig-note { margin-top: 0.9rem; color: var(--rs-ink-2); font-size: 1.16rem; }
        .rs-meter { position: relative; height: 5px; margin: 1.1rem 0.4rem 0; border-block: 1px solid var(--rs-line); }
        .rs-meter i { position: absolute; inset: 0 auto 0 0; background: var(--rs-accent); }
        .rs-lines { display: grid; grid-template-columns: max-content minmax(1.2rem, 1fr) max-content; row-gap: 0.55rem; margin-top: 0.4rem; text-align: start; font-size: 1.16rem; }
        .rs-lines > div { display: grid; grid-template-columns: max-content minmax(1.2rem, 1fr) max-content; grid-column: 1 / -1; align-items: baseline; }
        @supports (grid-template-columns: subgrid) {
            .rs-lines > div { grid-template-columns: subgrid; }
        }
        .rs-lines > div::before { content: ""; grid-column: 2; grid-row: 1; margin-inline: 0.55rem; border-bottom: 1px dotted var(--rs-ink-3); translate: 0 -0.28em; }
        .rs-lines dt { grid-column: 1; grid-row: 1; color: var(--rs-ink-3); font-size: 0.66rem; }
        .rs-lines dd { grid-column: 3; grid-row: 1; color: var(--rs-ink); }
        .rs-menu-foot { margin-top: 0.3rem; color: var(--rs-ink-2); font-family: var(--rs-it); font-style: italic; font-size: 0.98rem; line-height: 1.45; }

        /* ---------------------------------------------------------------
           The count: the room, cover by cover
           --------------------------------------------------------------- */
        .rs-tally { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.6rem 1.4rem; margin-bottom: 1.6rem; }
        /* The calc() is for Safari: while a view timeline animates a registered <integer>, Safari
           holds it unrounded (22.44), which is not a valid counter value, so the counter read 0
           the whole way down the room. Inside calc() it is rounded, as Chrome rounds it anyway. */
        .rs-tally-n {
            min-width: 1.15em;
            counter-reset: rs-n calc(var(--rs-n) * 1);
            color: var(--rs-accent);
            font-size: clamp(4rem, 9vw, 6.5rem);
            line-height: 0.8;
            text-align: end;
            font-variant-numeric: lining-nums tabular-nums;
        }
        .rs-tally-n::after { content: counter(rs-n); }
        .rs-tally-of { color: var(--rs-ink-2); }
        .rs-closed { padding: 0.5rem 0.9rem 0.42rem; background: var(--rs-wine); color: var(--rs-on-wine); }

        .rs-room {
            position: relative;
            max-width: 62rem;
            margin-inline: auto;
            aspect-ratio: 100 / 46;
            background-color: var(--rs-floor);
            background-image:
                repeating-linear-gradient(45deg, var(--rs-line-2) 0 1px, transparent 1px 1.1rem),
                repeating-linear-gradient(-45deg, var(--rs-line-2) 0 1px, transparent 1px 1.1rem);
            border: 3px solid var(--rs-ink);
            box-shadow: 0 2.2rem 3rem -2.4rem var(--rs-shade);
        }
        .rs-room-pass,
        .rs-room-door { position: absolute; display: grid; place-items: center; font-size: 0.54rem; letter-spacing: 0.3em; white-space: nowrap; }
        .rs-room-pass { top: -3px; left: 31%; width: 38%; height: 1.15rem; background: var(--rs-ink); color: var(--rs-linen); }
        .rs-room-door { bottom: -3px; right: 6%; width: 11%; height: 3px; background: var(--rs-floor); color: var(--rs-ink-2); }
        .rs-room-door span { translate: 0 1rem; }

        .rs-table {
            position: absolute;
            left: calc(var(--x) * 1%);
            top: calc(var(--y) * 1%);
            width: calc(var(--s) * 1%);
            aspect-ratio: 1;
            translate: -50% -50%;
            container-type: inline-size;
        }
        .rs-table-long { aspect-ratio: 3.1 / 1; }
        @media (max-width: 719px) {
            .rs-room { aspect-ratio: 100 / 126; }
            .rs-room-pass { left: 24%; width: 52%; }
            .rs-room-door { right: 9%; width: 20%; }
            .rs-table { left: calc(var(--mx) * 1%); top: calc(var(--my) * 1%); width: calc(var(--ms) * 1%); }
        }
        .rs-cloth {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: var(--rs-cloth);
            box-shadow: 0 0.4rem 0.9rem -0.3rem var(--rs-shade), inset 0 0 0 1px rgba(35, 32, 28, 0.09);
        }
        .rs-table-long .rs-cloth,
        .rs-table-square .rs-cloth { border-radius: 0.3rem; }
        .rs-chair {
            position: absolute;
            width: calc(var(--c) * 1cqi);
            aspect-ratio: 1 / 0.8;
            translate: -50% -50%;
            border: 1px solid color-mix(in srgb, var(--rs-wine) calc(var(--rs-on) * 100%), var(--rs-ink-3));
            border-top-width: 3px;
            border-radius: 24% 24% 34% 34%;
            background: color-mix(in srgb, var(--rs-wine) calc(var(--rs-on) * 100%), transparent);
        }
        .rs-plate {
            position: absolute;
            width: calc(var(--p) * 1cqi);
            aspect-ratio: 1;
            translate: -50% -50%;
            border-radius: 50%;
            background: radial-gradient(circle closest-side, #fffdf8 0 56%, #d9cfbc 58% 62%, #fffdf8 64%);
            box-shadow: 0 1px 2px rgba(35, 32, 28, 0.45);
            scale: var(--rs-on);
            opacity: var(--rs-on);
        }
        /* One candle to a table: a votive by day, lit after dark. */
        .rs-candle {
            position: absolute;
            left: var(--cx, 50%);
            top: 50%;
            width: max(0.36rem, calc(var(--k, 8) * 1cqi));
            aspect-ratio: 1;
            translate: -50% -50%;
            border-radius: 50%;
            background: radial-gradient(circle closest-side, #6b1d2a 0 30%, #f4ecdc 34% 78%, #cfc4ae 82%);
        }
        .dark .rs-candle {
            background: radial-gradient(circle closest-side, #fff6dc 0 34%, #ffcf7a 40% 78%, #c98a36 84%);
            box-shadow: 0 0 1.5rem 0.75rem rgba(255, 186, 92, calc(0.5 * var(--rs-glow))), 0 0 4.5rem 2.2rem rgba(255, 170, 70, calc(0.2 * var(--rs-glow)));
            animation: rs-flicker 7s ease-in-out infinite;
            animation-delay: calc(var(--i, 0) * -1.3s);
        }
        .rs-room-note { margin-top: 1.5rem; text-align: center; }

        @supports (animation-timeline: view()) {
            html.es-anim #rs .rs-floor { view-timeline-name: --rs-room; view-timeline-axis: block; }
            html.es-anim #rs .rs-chair,
            html.es-anim #rs .rs-plate {
                animation: rs-take linear both;
                animation-timeline: --rs-room;
                animation-range: cover calc(14% + var(--i) * 1.45%) cover calc(17.5% + var(--i) * 1.45%);
            }
            html.es-anim #rs .rs-tally-n {
                animation: rs-count linear both;
                animation-timeline: --rs-room;
                animation-range: cover 15.5% cover 51%;
            }
            html.es-anim #rs .rs-closed {
                animation: rs-shut linear both;
                animation-timeline: --rs-room;
                animation-range: cover 51% cover 55%;
            }
        }
        @keyframes rs-take { from { --rs-on: 0; } to { --rs-on: 1; } }
        /* Twenty-four, the same figure $sitting['seats'] prints beside it. */
        @keyframes rs-count { from { --rs-n: 0; } to { --rs-n: 24; } }
        @keyframes rs-shut { from { opacity: 0; scale: 0.9; } to { opacity: 1; scale: 1; } }

        /* Four lines of the menu, ruled like a card folded in four. */
        .rs-quad { display: grid; grid-template-columns: minmax(0, 1fr); margin-top: clamp(3rem, 6vw, 4.5rem); border-block: 1px solid var(--rs-line); }
        .rs-quad > div { padding: 2.1rem 0.5rem; text-align: center; }
        .rs-quad > div + div { border-top: 1px solid var(--rs-line-2); }
        @media (min-width: 760px) {
            .rs-quad { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .rs-quad > div { padding: 2.5rem 2.5rem; }
            .rs-quad > div + div { border-top: 0; }
            .rs-quad > div:nth-child(even) { border-inline-start: 1px solid var(--rs-line-2); }
            .rs-quad > div:nth-child(n + 3) { border-top: 1px solid var(--rs-line-2); }
        }
        .rs-item-name { font-size: 1.7rem; font-weight: 700; line-height: 1.15; text-wrap: balance; }
        .rs-item-desc { max-width: 27rem; margin: 0.6rem auto 0; color: var(--rs-ink-2); }

        /* ---------------------------------------------------------------
           The cutoff: a docket on the pass
           --------------------------------------------------------------- */
        .rs-duo { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem 5rem; align-items: center; }
        @media (min-width: 1000px) {
            .rs-duo { grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr); }
            .rs-duo-flip { grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr); }
            .rs-duo-flip > :first-child { order: 0; }
        }
        @media (max-width: 999px) {
            .rs-duo-flip > :first-child { order: 2; }
        }
        .rs-list { display: grid; gap: 1.5rem; margin-top: 2.3rem; }
        .rs-list li { display: grid; grid-template-columns: 0.42rem minmax(0, 1fr); column-gap: 1.1rem; }
        .rs-list li::before { content: ""; width: 0.42rem; aspect-ratio: 1; margin-top: 0.72rem; rotate: 45deg; background: var(--rs-accent); }
        .rs-list strong { display: block; font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
        .rs-list span { display: block; margin-top: 0.25rem; color: var(--rs-ink-2); }
        .rs-list .rs-tier { display: inline-block; margin-top: 0; margin-inline-start: 0.6rem; }
        .rs-list .rs-tier-pro { color: var(--rs-on-wine); }

        .rs-pass { width: min(100%, 25rem); margin-inline: auto; }
        .rs-rail {
            position: relative;
            z-index: 1;
            height: 1.15rem;
            margin-inline: -1.2rem;
            border-radius: 0.2rem;
            background: linear-gradient(180deg, #e3e3df, #a5a6a1 42%, #cfd0cb 58%, #84857f);
            box-shadow: 0 0.4rem 0.6rem -0.25rem rgba(0, 0, 0, 0.5);
        }
        .rs-docket {
            position: relative;
            margin-top: -0.45rem;
            rotate: -1.3deg;
            transform-origin: 50% 0;
            filter: drop-shadow(0 1.3rem 1.1rem rgba(35, 32, 28, 0.28));
        }
        .dark .rs-docket { filter: drop-shadow(0 1.3rem 1.1rem rgba(0, 0, 0, 0.6)); }
        .rs-docket-in {
            padding: 2.1rem 1.6rem 2rem;
            background: #fffdf6;
            color: #23201c;
            -webkit-mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50% / 0.9rem 100%;
            mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50% / 0.9rem 100%;
        }
        .dark .rs-docket-in { background: #efe6d3; }
        .rs-docket-top { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-bottom: 0.9rem; border-bottom: 1px solid rgba(35, 32, 28, 0.55); }
        .rs-docket-top h3 { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
        .rs-docket-top span { color: #5a5248; font-size: 0.6rem; }
        .rs-docket-row { display: grid; grid-template-columns: 2.7rem minmax(0, 1fr) auto; align-items: baseline; column-gap: 0.7rem; padding: 0.78rem 0.4rem 0.62rem; border-bottom: 1px dashed rgba(35, 32, 28, 0.28); }
        .rs-docket-row .rs-sc { color: #5a5248; font-size: 0.62rem; }
        .rs-docket-row b { font-size: 1.22rem; font-weight: 700; line-height: 1.2; }
        .rs-docket-row i { grid-column: 2 / -1; height: 3px; margin-top: 0.42rem; background: linear-gradient(90deg, #6b1d2a calc(var(--n) / 24 * 100%), rgba(35, 32, 28, 0.13) 0); font-style: normal; }
        .rs-docket-row i { transform-origin: 0 50%; transition: scale 0.9s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(0.35s + var(--r, 0) * 0.12s); }
        html.es-anim #rs .rs-pass:not(.is-revealed) .rs-docket-row i { scale: 0 1; }
        .rs-docket-cut { background: rgba(107, 29, 42, 0.09); }
        .rs-docket-cut b { color: #6b1d2a; }
        .rs-docket-foot { margin-top: 1.1rem; padding-bottom: 0.5rem; color: #4a443c; font-family: var(--rs-it); font-style: italic; font-size: 0.98rem; }

        /* ---------------------------------------------------------------
           The questions: the card at checkout, the sheet in the kitchen
           --------------------------------------------------------------- */
        .rs-papers { position: relative; width: min(100%, 27rem); margin-inline: auto; padding-bottom: 11.2rem; }
        .rs-chit {
            position: relative;
            padding: 2rem 1.8rem 1.7rem;
            background: var(--rs-card);
            box-shadow: 0 0 0 1px var(--rs-line), 0 2.2rem 3rem -2rem var(--rs-shade);
        }
        .rs-chit::before { content: ""; position: absolute; inset: 0.5rem; border: 1px solid var(--rs-brass); opacity: 0.55; pointer-events: none; }
        .rs-chit > * { position: relative; }
        .rs-chit-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .rs-chit-top h3 { font-size: 1.75rem; font-weight: 700; line-height: 1.1; }
        .rs-chit-lede { margin-top: 0.4rem; color: var(--rs-ink-2); font-size: 1.12rem; }
        .rs-chit-q { margin-top: 1.25rem; padding-bottom: 0.7rem; border-bottom: 1px solid var(--rs-line); }
        .rs-chit-q b { display: block; font-size: 1.3rem; font-weight: 700; line-height: 1.2; }
        .rs-chit-q span { display: block; margin-top: 0.3rem; color: var(--rs-ink-3); font-size: 0.62rem; }
        .rs-chit-foot { margin-top: 1.2rem; color: var(--rs-ink-2); font-family: var(--rs-it); font-style: italic; font-size: 0.98rem; }
        .rs-prep {
            position: absolute;
            right: -2.4rem;
            bottom: 0;
            width: 12.8rem;
            padding: 1.2rem 1.1rem 1rem;
            background: #fffdf6;
            color: #23201c;
            rotate: 3deg;
            box-shadow: 0 1.4rem 1.8rem -1.1rem var(--rs-shade), 0 0 0 1px rgba(35, 32, 28, 0.1);
            font-family: var(--rs-sans);
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            line-height: 1.75;
            font-variant-numeric: lining-nums tabular-nums;
        }
        .dark .rs-prep { background: #efe6d3; }
        .rs-prep::before { content: ""; position: absolute; top: -0.5rem; left: 50%; width: 3.6rem; height: 1.1rem; translate: -50% 0; rotate: -2deg; background: rgba(214, 198, 160, 0.72); }
        .rs-prep b { display: block; margin-bottom: 0.3rem; padding-bottom: 0.35rem; border-bottom: 1px solid rgba(35, 32, 28, 0.5); font-weight: 400; letter-spacing: 0.22em; text-transform: uppercase; font-size: 0.6rem; }
        .rs-prep span { display: grid; grid-template-columns: minmax(0, 1fr) auto; column-gap: 0.8rem; align-items: baseline; }
        .rs-prep i { font-style: normal; }
        .rs-prep em { display: block; margin-top: 0.35rem; padding-top: 0.35rem; border-top: 1px dashed rgba(35, 32, 28, 0.4); color: #6b1d2a; font-style: normal; }
        @media (max-width: 520px) {
            .rs-papers { padding-bottom: 0; }
            .rs-prep { position: relative; right: auto; margin: -0.9rem 0.4rem 0 auto; }
        }

        /* ---------------------------------------------------------------
           Private hire: the card on the table at the back
           --------------------------------------------------------------- */
        .rs-tent-stage { display: grid; justify-items: center; margin-bottom: clamp(3rem, 6vw, 4.5rem); perspective: 52rem; perspective-origin: 50% -30%; }
        .rs-tent { position: relative; width: min(100%, 21rem); transform-style: preserve-3d; }
        .rs-tent-back,
        .rs-tent-face { transform-origin: 50% 0; }
        .rs-tent-back { position: absolute; inset: 0; background: #d9cdb7; transform: rotateX(-24deg); }
        .rs-tent-face {
            position: relative;
            padding: 2.2rem 1.5rem 2rem;
            background: linear-gradient(180deg, #f3ebdb, #fffdf8 16%, #fbf6ea);
            color: #23201c;
            text-align: center;
            transform: rotateX(24deg);
            box-shadow: inset 0 0 0 1px rgba(35, 32, 28, 0.1);
            transition: transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .rs-tent-face::before { content: ""; position: absolute; inset: 0.55rem; border: 1px solid rgba(168, 133, 74, 0.7); pointer-events: none; }
        .rs-tent:hover .rs-tent-face { transform: rotateX(15deg); }
        .rs-tent-face strong { display: block; font-size: 3rem; font-weight: 400; line-height: 1; letter-spacing: -0.01em; }
        .rs-tent-face .rs-sc { display: block; color: #6b1d2a; }
        .rs-tent-face .rs-sc + strong { margin-top: 0.8rem; }
        .rs-tent-face strong + .rs-sc { margin-top: 0.9rem; color: #4a443c; font-size: 0.6rem; }
        .rs-tent-shadow { width: min(100%, 24rem); height: 1.6rem; margin-top: -0.4rem; background: radial-gradient(ellipse closest-side, var(--rs-shade), transparent); opacity: 0.55; }
        .rs-trio { display: grid; grid-template-columns: minmax(0, 1fr); border-block: 1px solid var(--rs-line); }
        .rs-trio > div { padding: 2.1rem 0.5rem; text-align: center; }
        .rs-trio > div + div { border-top: 1px solid var(--rs-line-2); }
        @media (min-width: 860px) {
            .rs-trio { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .rs-trio > div { padding: 2.5rem 2rem; }
            .rs-trio > div + div { border-top: 0; border-inline-start: 1px solid var(--rs-line-2); }
        }

        /* ---------------------------------------------------------------
           Who it is for: six names, set like a wine list
           --------------------------------------------------------------- */
        .rs-guests { display: grid; grid-template-columns: minmax(0, 1fr); border-top: 1px solid var(--rs-line); }
        @media (min-width: 860px) {
            .rs-guests { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 4.5rem; }
        }
        .rs-guest { display: grid; grid-template-columns: 2.4rem minmax(0, 1fr); column-gap: 1rem; padding: 1.9rem 0.25rem 1.8rem; border-bottom: 1px solid var(--rs-line); }
        .rs-guest-no { padding-top: 0.55rem; color: var(--rs-accent); font-size: 0.66rem; }
        .rs-guest h3 { font-size: 1.85rem; font-weight: 700; line-height: 1.12; }
        .rs-guest p { margin-top: 0.4rem; color: var(--rs-ink-2); }
        .rs-guest a { display: inline-flex; align-items: center; gap: 0.6rem; margin-top: 0.9rem; padding-bottom: 0.2rem; border-bottom: 1px solid var(--rs-accent); color: var(--rs-ink); font-size: 0.62rem; transition: gap 0.25s ease; }
        .rs-guest a:hover { gap: 0.95rem; }
        .rs-guest a svg { width: 0.9rem; height: 0.9rem; }

        /* ---------------------------------------------------------------
           How it works: three plates on a wine cloth, fixed in both modes
           --------------------------------------------------------------- */
        .rs-service {
            position: relative;
            overflow: clip;
            padding-block: clamp(4.5rem, 9vw, 8rem);
            background-color: #561521;
            background-image:
                radial-gradient(ellipse 70% 55% at 50% 0%, rgba(255, 214, 150, 0.13), rgba(255, 214, 150, 0) 72%),
                repeating-linear-gradient(0deg, rgba(255, 255, 255, 0.028) 0 1px, transparent 1px 3px),
                repeating-linear-gradient(90deg, rgba(255, 255, 255, 0.028) 0 1px, transparent 1px 3px);
            color: #fbf3e4;
        }
        .rs-service .rs-tag,
        .rs-service .rs-em { color: #f0cf96; }
        .rs-service .rs-kick { color: #e6d3bb; }
        .rs-service .rs-orn { color: rgba(240, 207, 150, 0.7); }
        .rs-service .rs-orn i { background: #f0cf96; }
        #rs .rs-service a:focus-visible { outline-color: #f0cf96; }
        .rs-steps { display: grid; grid-template-columns: minmax(0, 1fr); gap: 3.5rem 2rem; }
        @media (min-width: 860px) { .rs-steps { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .rs-step { text-align: center; }
        .rs-setting { display: flex; align-items: center; justify-content: center; gap: 1.2rem; margin-bottom: 1.9rem; }
        .rs-setting .rs-dish { --d: 9.4rem; font-size: 1.5rem; letter-spacing: 0.1em; box-shadow: 0 1.2rem 1.6rem -0.9rem rgba(0, 0, 0, 0.75), inset 0 0 0 1px rgba(35, 32, 28, 0.1), inset 0 -0.3rem 0.6rem rgba(35, 32, 28, 0.08); }
        .rs-fork,
        .rs-knife { position: relative; flex: none; width: 0.26rem; height: 7.6rem; border-radius: 0.2rem; background: linear-gradient(90deg, var(--rs-steel-1), var(--rs-steel-2) 55%, var(--rs-steel-3)); box-shadow: 0 0.5rem 0.6rem -0.3rem rgba(0, 0, 0, 0.6); }
        .rs-service { --rs-steel-1: #f4f3ee; --rs-steel-2: #aeaea8; --rs-steel-3: #e3e2dc; }
        .rs-fork::before {
            content: "";
            position: absolute;
            left: 50%;
            top: 0;
            width: 0.86rem;
            height: 2.3rem;
            translate: -50% 0;
            border-radius: 0.1rem 0.1rem 0.42rem 0.42rem;
            background: repeating-linear-gradient(90deg, var(--rs-steel-3) 0 0.14rem, rgba(0, 0, 0, 0) 0.14rem 0.36rem) top / 100% 62% no-repeat, linear-gradient(90deg, var(--rs-steel-1), var(--rs-steel-2)) bottom / 100% 40% no-repeat;
        }
        .rs-knife::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 0.62rem;
            height: 4.3rem;
            border-radius: 0.1rem 0.6rem 0.5rem 0.1rem / 0.1rem 2.6rem 0.5rem 0.1rem;
            background: linear-gradient(90deg, var(--rs-steel-1), var(--rs-steel-2) 70%, var(--rs-steel-3));
        }
        .rs-step h3 { font-size: 1.95rem; font-weight: 700; line-height: 1.15; }
        .rs-step p { max-width: 21rem; margin: 0.6rem auto 0; color: #ecdcc7; }

        /* ---------------------------------------------------------------
           Key features and neighbours: plain lines with an arrow
           --------------------------------------------------------------- */
        .rs-carte { border-top: 1px solid var(--rs-line); }
        .rs-carte a { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; column-gap: 1.5rem; padding: 1.45rem 0.4rem 1.35rem; border-bottom: 1px solid var(--rs-line); transition: padding 0.3s ease, background-color 0.3s ease; }
        .rs-carte a:hover { padding-inline: 1.1rem 0.4rem; background: var(--rs-line-2); }
        .rs-carte strong { display: block; color: var(--rs-accent); font-family: var(--rs-sans); font-weight: 400; font-size: 0.78rem; letter-spacing: 0.24em; text-transform: uppercase; }
        .rs-carte span { display: block; margin-top: 0.25rem; color: var(--rs-ink); font-size: 1.4rem; line-height: 1.25; }
        .rs-carte svg { width: 1.3rem; height: 1.3rem; color: var(--rs-accent); transition: translate 0.3s ease; }
        .rs-carte a:hover svg { translate: 0.35rem 0; }
        .rs-centre { margin-top: 2.2rem; text-align: center; }
        .rs-next { display: grid; grid-template-columns: minmax(0, 1fr); border-top: 1px solid var(--rs-line); }
        @media (min-width: 700px) { .rs-next { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 4rem; } }
        .rs-next a { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: end; column-gap: 1rem; padding: 1.5rem 0.4rem 1.35rem; border-bottom: 1px solid var(--rs-line); transition: padding 0.3s ease; }
        .rs-next a:hover { padding-inline: 1rem 0.4rem; }
        .rs-next small { display: block; color: var(--rs-ink-3); font-size: 0.62rem; }
        .rs-next strong { display: block; margin-top: 0.2rem; font-size: 1.9rem; font-weight: 700; line-height: 1.1; }
        .rs-next svg { width: 1.3rem; height: 1.3rem; margin-bottom: 0.35rem; color: var(--rs-accent); transition: translate 0.3s ease; }
        .rs-next a:hover svg { translate: 0.35rem 0; }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the setting changes.
           --------------------------------------------------------------- */
        #rs .rs-plans > section { background: var(--rs-linen-2); border-top: 1px solid var(--rs-line); }
        #rs .rs-plans h2 { font-family: var(--rs-serif); font-weight: 400; font-size: clamp(2.3rem, 4.6vw, 3.6rem); line-height: 1.05; letter-spacing: -0.012em; color: var(--rs-ink); }
        #rs .rs-plans h2 + p { color: var(--rs-ink-2); font-size: 1.3rem; }
        #rs .rs-plans .grid > div { background: var(--rs-card); border: 1px solid var(--rs-line); border-radius: 0; box-shadow: none; color: var(--rs-ink); }
        #rs .rs-plans .grid > div:hover { box-shadow: 0 1.8rem 2.4rem -1.8rem var(--rs-shade); }
        #rs .rs-plans .grid > div:nth-child(2) { border-color: var(--rs-wine); box-shadow: inset 0 0 0 4px var(--rs-card), inset 0 0 0 5px var(--rs-brass); }
        #rs .rs-plans .grid > div span,
        #rs .rs-plans .grid > div p,
        #rs .rs-plans .grid > div li { color: var(--rs-ink-2); }
        #rs .rs-plans .grid > div p,
        #rs .rs-plans .grid > div li { font-size: 1.12rem; line-height: 1.4; }
        #rs .rs-plans .grid > div p.text-xs { font-family: var(--rs-it); font-style: italic; font-size: 0.95rem; }
        #rs .rs-plans .grid > div .text-3xl { font-family: var(--rs-serif); font-weight: 400; font-size: 3.4rem; line-height: 1; color: var(--rs-accent); font-variant-numeric: lining-nums; }
        #rs .rs-plans .grid > div .uppercase { font-family: var(--rs-sans); font-weight: 400; font-size: 0.72rem; letter-spacing: 0.24em; color: var(--rs-ink); }
        #rs .rs-plans .grid > div .rounded-full { border-radius: 0; background: var(--rs-wine); color: var(--rs-on-wine); font-family: var(--rs-sans); font-weight: 400; letter-spacing: 0.16em; padding: 0.22rem 0.5rem 0.16rem; }
        #rs .rs-plans .grid > div .text-sm.font-normal { font-family: var(--rs-sans); font-size: 0.7rem; letter-spacing: 0.08em; color: var(--rs-ink-3); }
        #rs .rs-plans .grid > div svg { color: var(--rs-olive); margin-top: 0.3rem; }
        #rs .rs-plans a.font-medium { color: var(--rs-ink); font-size: 1.2rem; border-bottom: 1px solid var(--rs-accent); }
        #rs .rs-plans a.rounded-2xl { border-radius: 0; background: var(--rs-wine); color: var(--rs-on-wine); font-family: var(--rs-sans); font-weight: 400; font-size: 0.8rem; letter-spacing: 0.22em; text-transform: uppercase; box-shadow: inset 0 0 0 4px var(--rs-wine), inset 0 0 0 5px rgba(251, 246, 236, 0.45); }
        #rs .rs-plans a.rounded-2xl:hover { background: var(--rs-wine-deep); box-shadow: inset 0 0 0 4px var(--rs-wine-deep), inset 0 0 0 5px rgba(251, 246, 236, 0.6); transform: translateY(-2px); }

        #rs .rs-keep > section { background: var(--rs-linen-2); border-top: 1px solid var(--rs-line); }
        #rs .rs-keep h2 { font-family: var(--rs-serif); font-weight: 400; font-size: clamp(2.2rem, 4vw, 3.2rem); line-height: 1.05; color: var(--rs-ink); }
        #rs .rs-keep p.uppercase { font-family: var(--rs-sans); font-weight: 400; font-size: 0.72rem; letter-spacing: 0.24em; color: var(--rs-accent); }
        #rs .rs-keep .grid > a { background: var(--rs-card); border: 1px solid var(--rs-line); border-radius: 0; }
        #rs .rs-keep .grid > a:hover { border-color: var(--rs-accent); box-shadow: 0 1.6rem 2rem -1.6rem var(--rs-shade); }
        #rs .rs-keep .grid > a > span:first-child { display: none; }
        #rs .rs-keep .grid > a h3 { font-family: var(--rs-serif); font-weight: 700; font-size: 1.5rem; line-height: 1.15; color: var(--rs-ink); }
        #rs .rs-keep .grid > a p { font-size: 1.12rem; line-height: 1.4; color: var(--rs-ink-2); }
        #rs .rs-keep .grid > a > span:last-child,
        #rs .rs-keep a.self-start { font-family: var(--rs-sans); font-weight: 400; font-size: 0.68rem; letter-spacing: 0.18em; text-transform: uppercase; color: var(--rs-accent); }

        /* ---------------------------------------------------------------
           Questions, asked across the pass
           --------------------------------------------------------------- */
        .rs-faq { border-top: 1px solid var(--rs-line); }
        .rs-faq details { border-bottom: 1px solid var(--rs-line); }
        .rs-faq summary { display: grid; grid-template-columns: minmax(0, 1fr) 1.2rem; align-items: start; column-gap: 1.2rem; padding: 1.5rem 0.25rem 1.4rem; cursor: pointer; }
        .rs-faq h3 { font-size: 1.6rem; font-weight: 700; line-height: 1.2; text-wrap: balance; }
        .rs-faq summary i { position: relative; width: 1.2rem; height: 1.2rem; margin-top: 0.4rem; color: var(--rs-accent); }
        .rs-faq summary i::before,
        .rs-faq summary i::after { content: ""; position: absolute; inset: calc(50% - 0.5px) 0 auto 0; height: 1px; background: currentColor; transition: rotate 0.35s cubic-bezier(0.22, 1, 0.36, 1); }
        .rs-faq summary i::after { rotate: 90deg; }
        .rs-faq details[open] summary i::after { rotate: 0deg; }
        .rs-faq details[open] h3 { color: var(--rs-accent); }
        .rs-faq details p { max-width: 41rem; padding: 0 0.25rem 1.8rem; color: var(--rs-ink-2); }

        /* ---------------------------------------------------------------
           The finale: a place laid, and a card to put your name on
           --------------------------------------------------------------- */
        .rs-last { position: relative; overflow: clip; }
        .dark .rs-last::before {
            content: "";
            position: absolute;
            left: 50%;
            bottom: -6rem;
            width: 62rem;
            aspect-ratio: 1;
            translate: -50% 0;
            background: radial-gradient(circle closest-side, rgba(255, 190, 96, calc(0.14 * var(--rs-glow))), rgba(255, 190, 96, 0) 72%);
            pointer-events: none;
            animation: rs-flicker 8s ease-in-out infinite;
        }
        .rs-last > * { position: relative; }
        .rs-place { position: relative; display: grid; place-items: center; margin-top: clamp(2rem, 4vw, 3rem); padding-block: clamp(6rem, 11vw, 8.5rem); }
        .rs-place .rs-charger { width: auto; height: 100%; }
        .rs-charger {
            position: absolute;
            left: 50%;
            top: 50%;
            width: min(40rem, 128%);
            aspect-ratio: 1;
            translate: -50% -50%;
            border-radius: 50%;
            background:
                radial-gradient(circle closest-side, var(--rs-cloth) 0 67%, var(--rs-line) 67.6% 68.2%, rgba(0, 0, 0, 0) 68.8%),
                radial-gradient(circle closest-side, rgba(168, 133, 74, 0) 0 94%, var(--rs-brass) 94.6% 95.6%, rgba(168, 133, 74, 0) 96.2%),
                radial-gradient(circle at 36% 26%, var(--rs-card) 0, var(--rs-linen-2) 80%);
            box-shadow: 0 2.6rem 3.4rem -2.2rem var(--rs-shade), inset 0 0 0 1px var(--rs-line);
        }
        .dark .rs-charger { background:
                radial-gradient(circle closest-side, #241d17 0 67%, rgba(241, 232, 216, 0.16) 67.6% 68.2%, rgba(0, 0, 0, 0) 68.8%),
                radial-gradient(circle closest-side, rgba(201, 165, 101, 0) 0 94%, rgba(201, 165, 101, 0.7) 94.6% 95.6%, rgba(201, 165, 101, 0) 96.2%),
                radial-gradient(circle at 36% 26%, #2c241d 0, #1c1612 80%); }
        .rs-place .rs-fork,
        .rs-place .rs-knife { position: absolute; top: 50%; height: 15rem; width: 0.34rem; translate: 0 -50%; display: none; }
        .rs-place .rs-fork::before { width: 1.15rem; height: 4.4rem; background: repeating-linear-gradient(90deg, var(--rs-steel-2) 0 0.17rem, rgba(0, 0, 0, 0) 0.17rem 0.49rem) top / 100% 62% no-repeat, linear-gradient(90deg, var(--rs-steel-1), var(--rs-steel-2)) bottom / 100% 40% no-repeat; }
        .rs-place .rs-knife::before { width: 0.84rem; height: 8.4rem; }
        @media (min-width: 1040px) {
            .rs-place .rs-fork,
            .rs-place .rs-knife { display: block; }
            .rs-place .rs-fork { left: calc(50% - 21.5rem); }
            .rs-place .rs-knife { left: calc(50% + 21rem); }
        }
        .rs-book {
            position: relative;
            width: min(100%, 31rem);
            padding: 2.4rem 2rem 2rem;
            background: var(--rs-card);
            text-align: center;
            box-shadow: 0 0 0 1px var(--rs-line), 0 2.2rem 3rem -2rem var(--rs-shade);
        }
        .rs-book::before { content: ""; position: absolute; inset: 0.55rem; border: 1px solid var(--rs-brass); opacity: 0.6; pointer-events: none; }
        .rs-book > * { position: relative; }
        .rs-book-label { display: block; margin-bottom: 0.9rem; color: var(--rs-ink-3); }
        .rs-book-form { display: grid; gap: 1.2rem; }
        #rs .rs-claim {
            display: flex;
            align-items: baseline;
            min-width: 0;
            padding: 1rem 0.5rem;
            border: 0;
            border-bottom: 1px solid var(--rs-ink);
            background: transparent;
            font-family: var(--rs-serif);
            font-size: clamp(1.25rem, 5vw, 1.7rem);
            line-height: 1.2;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        /* Up to 400px the line is too short for "your-restaurant" and the suffix at 20px, and
           the placeholder was cut off mid-word. It stays above the 16px iOS needs. */
        @media (max-width: 400px) {
            #rs .rs-claim { font-size: max(1rem, 4.7vw); }
        }
        #rs .rs-claim:focus-within { border-color: var(--rs-accent); box-shadow: 0 1px 0 0 var(--rs-accent); }
        #rs .rs-claim input {
            flex: 1;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            text-align: right;
            font: inherit;
            font-weight: 700;
            color: var(--rs-ink);
            box-shadow: none;
            outline: none;
        }
        #rs .rs-claim input::placeholder { color: var(--rs-ink-3); font-weight: 400; opacity: 1; }
        .rs-claim span { flex: none; color: var(--rs-ink-2); user-select: none; }
        .rs-book-note { margin-top: 1.2rem; color: var(--rs-ink-3); font-family: var(--rs-it); font-style: italic; font-size: 0.98rem; }

        @media (prefers-reduced-motion: reduce) {
            .dark .rs-hero::before,
            .dark .rs-last::before,
            .dark .rs-candle { animation: none; }
            .rs-btn, .rs-btn svg, .rs-more, .rs-tent-face, .rs-carte a, .rs-carte svg, .rs-next a, .rs-next svg, .rs-guest a, .rs-docket-row i { transition: none; }
            .rs-btn:hover { translate: none; }
        }
    </style>

    @php
        // One sitting. Every figure the page states comes from here, and the
        // meter's width is computed from the same two numbers, so the bar and
        // the fraction cannot disagree. Asserted at build time: sold < seats,
        // and the cutoff day precedes the sitting.
        $sitting = [
            'name'      => 'Burgundy dinner',
            'day'       => 'Saturday',
            'time'      => '7:30pm',
            'seats'     => 24,
            'sold'      => 19,
            'cutoff'    => 'Thursday, 11:59pm',
            'shop'      => 'Friday morning',
            'price'     => 85,
        ];
        $remaining = $sitting['seats'] - $sitting['sold'];
        $fillPct   = (int) round($sitting['sold'] / $sitting['seats'] * 100);

        $faqs = [
            [
                'q' => 'Is Event Schedule free for restaurants?',
                'a' => 'The schedule itself is free forever: your public page and its link, sub-schedules for private dining or a supper club, enquiries for private hire, Drafts that keep an event off the public page until you announce it, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Selling covers at a price is the one part that needs Pro, at '.plan_price($proMonthly).' a month, and the cutoff and the questions on the ticket come with it. A free evening, a quiz or a tasting on the house, can take registrations up to a capacity per date on the free plan, and there is no monthly ceiling on how many names come through. Event Schedule charges zero platform fees on sales either way.',
            ],
            [
                'q' => 'How do I stop selling more covers than the kitchen can cook?',
                'a' => 'Give the ticket a quantity and that is the number of covers. It is counted per date, so a sold-out Saturday does not stop the following Saturday selling. When the quantity is gone the sitting closes itself, which means the number you are cooking for is the number that sold.',
            ],
            [
                'q' => 'Can sales stop before the night, so I know what to buy?',
                'a' => 'Yes. A ticket type takes a date and time to come off sale, so you can close Thursday at midnight and do the ordering on Friday against a final number. It is a single date set on that event rather than a rule that repeats, which is exactly what a one-off dinner wants.',
            ],
            [
                'q' => 'Can I announce a dinner before bookings open?',
                'a' => 'Yes. Switch on the free "Notify me" card, put the dinner up with a date for its tickets to go on sale, and anyone who wants a seat can leave an email address on the event page without an account. They get one email when bookings open, one if the dinner is cancelled, a reminder shortly before it, and any change notice you send, and nothing else. You can see how many people are waiting on the event\'s Tickets panel, it is never shown publicly, and it is free on every plan.',
            ],
            [
                'q' => 'Can I collect allergies and dietary requirements?',
                'a' => 'Yes, on every plan. Put the question on the ticket and it is asked at checkout, so the answers arrive with the sale rather than in a separate email thread you have to reconcile against the list. Ask for allergies, a course choice, or a wine pairing. On the free plan the person booking answers once for their party; on Pro, each guest can answer for themselves, and the sales CSV export carries every answer.',
            ],
            [
                'q' => 'What about private hire enquiries?',
                'a' => 'Turn on booking requests and people can ask about a date through your page. Every enquiry waits for you to accept it, so nothing appears publicly that you have not agreed to, and you are emailed when new ones are waiting. Keep private dining on its own sub-schedule and you can share a link that shows only those events.',
            ],
            [
                'q' => 'Can I refund a booking if a guest cancels?',
                'a' => 'Yes, from the Sales page, on Pro. A booking paid through Stripe or PayPal can be refunded in full or in part, and the money goes back through the provider before the sale is marked refunded. A full refund puts the cover back on sale for that date. A partial refund, if you keep a cancellation charge, leaves the booking and its cover in place. A booking paid in cash or through a payment link is recorded with Mark as Refunded, which moves no money. The guest is not emailed about a refund, so let them know yourself.',
            ],
        ];

        $dotSections = [
            ['top', 'The sitting'],
            ['count', 'The count'],
            ['cutoff', 'The cutoff'],
            ['ask', 'The questions'],
            ['hire', 'Private hire'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];

        // The room in "The count": six tables that seat the sitting between them. Each
        // row is the table's shape, its covers, where it stands on a wide screen
        // (x, y and width as a percentage of the room) and where it stands on a phone.
        // It is a picture of a number, so the covers are numbered in the order they go.
        $rsTables = [
            ['round', 4, 14, 30, 11, 27, 21, 22],
            ['round', 4, 68, 27, 11, 73, 21, 22],
            ['long', 8, 41, 50, 30, 50, 53, 60],
            ['square', 4, 68, 74, 10, 50, 85, 18],
            ['round', 2, 14, 76, 8.5, 17, 85, 16],
            ['round', 2, 88, 50, 8.5, 83, 85, 16],
        ];
        $rsRoom = [];
        $rsCover = 0;
        foreach ($rsTables as $rsIndex => [$rsKind, $rsSeats, $rsX, $rsY, $rsS, $rsMx, $rsMy, $rsMs]) {
            $rsMarks = [];
            for ($rsK = 0; $rsK < $rsSeats; $rsK++) {
                if ($rsKind === 'long') {
                    $rsAlong = 12.5 + 25 * ($rsK % 4);
                    $rsNear = $rsK < 4;
                    $rsMarks[] = [$rsCover++, $rsAlong, $rsNear ? -20 : 120, $rsNear ? 0 : 180, $rsAlong, $rsNear ? 27 : 73];
                } else {
                    // A two faces across the table; one of the fours sits on the diagonals.
                    $rsAngle = deg2rad(360 / $rsSeats * $rsK - ($rsSeats === 2 ? 0 : 90) + ($rsIndex === 1 ? 45 : 0));
                    $rsOut = $rsSeats === 2 ? 68 : 65;
                    $rsIn = $rsSeats === 2 ? 23 : 28;
                    $rsMarks[] = [
                        $rsCover++,
                        round(50 + $rsOut * cos($rsAngle), 1),
                        round(50 + $rsOut * sin($rsAngle), 1),
                        round(rad2deg($rsAngle) + 90),
                        round(50 + $rsIn * cos($rsAngle), 1),
                        round(50 + $rsIn * sin($rsAngle), 1),
                    ];
                }
            }
            // Chair and plate sizes, as a share of the table's own width.
            [$rsChair, $rsPlate] = match (true) {
                $rsKind === 'long' => [9.5, 8.5],
                $rsSeats === 2 => [34, 27],
                default => [28, 22],
            };
            $rsRoom[] = [$rsKind, $rsX, $rsY, $rsS, $rsMx, $rsMy, $rsMs, $rsChair, $rsPlate, $rsMarks];
        }

        $rsArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $rsDown = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
    @endphp

    <div id="rs">

        <nav class="rs-courses es-dotnav" aria-label="Page sections">
            <ol>
                @foreach ($dotSections as [$sectionId, $sectionLabel])
                    <li><a href="#{{ $sectionId }}" class="es-dot" aria-label="{{ $sectionLabel }}"><span class="rs-sc" aria-hidden="true">{{ $sectionLabel }}</span><i aria-hidden="true"></i></a></li>
                @endforeach
            </ol>
        </nav>

        <!-- ============================================================ -->
        <!-- 1. Hero: the sitting                                         -->
        <!-- ============================================================ -->
        <section id="top" class="rs-hero">
            <p class="rs-wrap rs-masthead rs-sc es-fade-up es-d-1" aria-hidden="true">A menu for one evening</p>
            <div class="rs-wrap rs-hero-grid">
                <div class="rs-copy">
                    <h1 class="rs-h1">
                        <x-marketing.hero-eyebrow class="rs-eyebrow es-fade-up es-d-1">Restaurant event ticketing</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">The kitchen buys</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">for <span class="rs-em">a number</span>.</span></span>
                    </h1>

                    <p class="rs-lede es-fade-up es-d-2">
                        Sell one cover too many and you are turning people away at the door. Sell one
                        too few and it goes in the bin. Every other night is service - these are the
                        nights that need a count you can trust.
                    </p>

                    <div class="rs-cta es-fade-up es-d-3">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="rs-btn">
                            Put a sitting on sale
                            {!! $rsArrow !!}
                        </a>
                        <a href="#count" class="rs-more rs-sc">
                            See how the count works
                            {!! $rsDown !!}
                        </a>
                    </div>
                </div>

                <!-- The sitting. The meter width is computed from the same
                     two figures the fraction prints. -->
                <div class="rs-setting-hero es-fade-up es-d-3" data-tilt="2.5">
                <span class="rs-charger" aria-hidden="true"></span>
                <div class="rs-menu es-tilt-inner">
                    <span class="rs-fleuron" aria-hidden="true">&#x2766;&#xFE0E;</span>
                    <p class="rs-sc rs-menu-name">{{ $sitting['name'] }}</p>
                    <p class="rs-menu-when">{{ $sitting['day'] }} &middot; {{ $sitting['time'] }} &middot; ${{ $sitting['price'] }}</p>

                    <div class="rs-orn" aria-hidden="true"><i></i></div>

                    <p class="rs-fig">{{ $sitting['sold'] }}<span class="rs-sc"> of {{ $sitting['seats'] }}</span></p>
                    <p class="rs-fig-note">covers sold &middot; {{ $remaining }} left</p>
                    <div class="rs-meter" aria-hidden="true"><i style="width: {{ $fillPct }}%;"></i></div>

                    <div class="rs-orn" aria-hidden="true"><i></i></div>

                    <dl class="rs-lines">
                        <div>
                            <dt class="rs-sc">Sales close</dt>
                            <dd>{{ $sitting['cutoff'] }}</dd>
                        </div>
                        <div>
                            <dt class="rs-sc">You shop</dt>
                            <dd>{{ $sitting['shop'] }}</dd>
                        </div>
                        <div>
                            <dt class="rs-sc">At checkout</dt>
                            <dd>&ldquo;Any allergies?&rdquo;</dd>
                        </div>
                    </dl>

                    <div class="rs-orn" aria-hidden="true"><i></i></div>

                    <p class="rs-menu-foot">
                        The number on the card is the number you cook for. Nothing is counted twice.
                    </p>
                </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. The count (I)                                             -->
        <!-- ============================================================ -->
        <section id="count" class="rs-sec" style="scroll-margin-top: 4.5rem;">
            <div class="rs-wrap">
                <div class="rs-head">
                    <span class="rs-dish" aria-hidden="true" data-reveal="zoom">I</span>
                    <span class="rs-kick" aria-hidden="true" data-reveal>To begin</span>
                    <p class="rs-sc rs-tag" data-reveal style="--reveal-delay: 0.05s;">The count</p>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.1s;">
                        Twenty-four covers means <span class="rs-em">twenty-four</span>.
                    </h2>
                    <p class="rs-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Set how many covers the sitting has and it will not sell past them. When they
                        are gone it closes itself, so the figure you shop against is the figure that sold.
                    </p>
                </div>

                <!-- The room, counted. A picture of the number: nothing here can be picked. -->
                <div class="rs-floor" aria-hidden="true">
                    <div class="rs-tally">
                        <span class="rs-tally-n"></span>
                        <span class="rs-sc rs-tally-of">of {{ $sitting['seats'] }} covers</span>
                        <span class="rs-sc rs-closed">Sitting closed</span>
                    </div>

                    <div class="rs-room">
                        <span class="rs-sc rs-room-pass">The pass</span>
                        <span class="rs-sc rs-room-door"><span>Door</span></span>

                        @foreach ($rsRoom as $rsTableNo => [$rsKind, $rsX, $rsY, $rsS, $rsMx, $rsMy, $rsMs, $rsChair, $rsPlate, $rsMarks])
                            <div class="rs-table rs-table-{{ $rsKind }}" style="--x: {{ $rsX }}; --y: {{ $rsY }}; --s: {{ $rsS }}; --mx: {{ $rsMx }}; --my: {{ $rsMy }}; --ms: {{ $rsMs }}; --c: {{ $rsChair }}; --p: {{ $rsPlate }};">
                                @foreach ($rsMarks as [$rsNo, $rsCx, $rsCy, $rsTurn, $rsPx, $rsPy])
                                    <i class="rs-chair" style="--i: {{ $rsNo }}; left: {{ $rsCx }}%; top: {{ $rsCy }}%; rotate: {{ $rsTurn }}deg;"></i>
                                @endforeach
                                <span class="rs-cloth"></span>
                                @foreach ($rsMarks as [$rsNo, $rsCx, $rsCy, $rsTurn, $rsPx, $rsPy])
                                    <i class="rs-plate" style="--i: {{ $rsNo }}; left: {{ $rsPx }}%; top: {{ $rsPy }}%;"></i>
                                @endforeach
                                @if ($rsKind === 'long')
                                    <i class="rs-candle" style="--i: {{ $rsTableNo }}; --k: 2.6; --cx: 31%;"></i>
                                    <i class="rs-candle" style="--i: {{ $rsTableNo + 3 }}; --k: 2.6; --cx: 69%;"></i>
                                @else
                                    <i class="rs-candle" style="--i: {{ $rsTableNo }};"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rs-quad" data-reveal-group="100">
                    @foreach ([
                        ['It closes itself', 'No watching an inbox and no doing sums at eleven at night. The last cover sells and the sitting stops taking money.'],
                        ['More than one price? One pool', 'Two ticket types are counted separately unless you say otherwise. Set the sitting to Combined Total and they share a single count, so twenty-four stays twenty-four however many ways you sell it.'],
                        ['Counted per date', 'A sold-out Saturday does not stop the following Saturday selling. Each date keeps its own count, which is what makes a repeating supper club work.'],
                        ['Paid before they sit', 'The money is in before the shopping goes out, so a no-show is somebody else\'s problem rather than a hole in your week.'],
                    ] as [$t, $d])
                        <div data-reveal>
                            <h3 class="rs-item-name">{{ $t }}</h3>
                            <p class="rs-item-desc">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="rs-note" data-reveal>
                    <span class="rs-sc rs-tier rs-tier-pro">Pro</span>
                    Selling covers at a price is Pro, at {{ plan_price($proMonthly) }} a month; a free sitting takes names on any plan and has no ceiling. The money goes through your own Stripe or <x-link href="{{ marketing_url('/paypal') }}">PayPal</x-link> account, with no platform fee on top of theirs on either plan.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. The cutoff (II)                                           -->
        <!-- ============================================================ -->
        <section id="cutoff" class="rs-sec rs-alt" style="scroll-margin-top: 4.5rem;">
            <div class="rs-wrap rs-duo">
                <div>
                    <div class="rs-head rs-head-l">
                        <span class="rs-dish" aria-hidden="true" data-reveal="zoom">II</span>
                        <span class="rs-kick" aria-hidden="true" data-reveal>To follow</span>
                        <p class="rs-sc rs-tag" data-reveal style="--reveal-delay: 0.05s;">The cutoff</p>
                        <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.1s;">
                            Close the door <span class="rs-em">before you shop</span>.
                        </h2>
                    </div>
                    <p class="rs-sub" data-reveal style="--reveal-delay: 0.15s;">
                        A late booking is worse than an empty chair once the order has gone in. Give
                        the ticket a date and time to come off sale, and the count is final while you
                        still have a morning to buy against it.
                    </p>

                    <ul class="rs-list" data-reveal-group="90">
                        @foreach ([
                            ['One date, set on the event', 'It is a single moment you choose for that sitting, not a rule that repeats each week. For a dinner that runs once, that is exactly right.'],
                            ['Sales stop on their own', 'You do not have to remember to switch anything off on Thursday night while you are on the pass.'],
                            ['The list is final', 'Whatever the total says on Friday morning is what you are cooking. Nothing can be added behind you.'],
                            ['It can open on a date, too', 'Announce the dinner before bookings open and give the ticket a date to go on sale. With the "Notify me" card switched on, anyone who leaves an email on the event page is told when it does.'],
                        ] as [$t, $d])
                            <li data-reveal>
                                <div><strong>{{ $t }}</strong> <span>{{ $d }}</span></div>
                            </li>
                        @endforeach
                    </ul>

                    <p class="rs-note rs-note-l" data-reveal>
                        <span class="rs-sc rs-tier">Free</span>
                        Opening and closing dates are part of selling tickets, and the <x-link href="{{ marketing_url('/docs/tickets#interest-list') }}">interest list</x-link> costs nothing on any plan.
                    </p>
                </div>

                <div class="rs-pass" data-reveal="panel">
                    <div class="rs-rail" aria-hidden="true"></div>
                    <div class="rs-docket">
                    <div class="rs-docket-in">
                        <div class="rs-docket-top">
                            <h3>The week before</h3>
                            <span class="rs-sc">one sitting</span>
                        </div>

                        @foreach ([
                            ['Mon', 'On sale', '12 covers'],
                            ['Wed', 'On sale', '17 covers'],
                            ['Thu', 'Sales close 11:59pm', '19 covers'],
                            ['Fri', 'You shop for 19', 'final'],
                            ['Sat', 'Service', '19 covers'],
                        ] as $dRow => [$dDay, $dWhat, $dCount])
                            <div class="rs-docket-row @if ($dRow === 2) rs-docket-cut @endif">
                                <span class="rs-sc">{{ $dDay }}</span>
                                <b>{{ $dWhat }}</b>
                                <span class="rs-sc">{{ $dCount }}</span>
                                <i aria-hidden="true" style="--n: {{ [12, 17, 19, 19, 19][$dRow] }}; --r: {{ $dRow }};"></i>
                            </div>
                        @endforeach

                        <p class="rs-docket-foot">
                            Nineteen sold, nineteen cooked. The two numbers were never allowed to drift apart.
                        </p>
                    </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. The questions (III)                                       -->
        <!-- ============================================================ -->
        <section id="ask" class="rs-sec" style="scroll-margin-top: 4.5rem;">
            <div class="rs-wrap rs-duo rs-duo-flip">
                <div class="rs-papers" data-reveal="panel">
                    <div class="rs-chit">
                        <div class="rs-chit-top">
                            <h3>At checkout</h3>
                            <span class="rs-sc rs-tier">Free</span>
                        </div>
                        <p class="rs-chit-lede">Attached to the ticket, so the answer arrives with the sale.</p>

                        @foreach ([
                            ['Any allergies or intolerances?', 'Free text'],
                            ['Main course', 'Beef / Halibut / Squash'],
                            ['Wine pairing?', 'Yes / No'],
                        ] as [$qLabel, $qKind])
                            <div class="rs-chit-q">
                                <b>{{ $qLabel }}</b>
                                <span class="rs-sc">{{ $qKind }}</span>
                            </div>
                        @endforeach

                        <p class="rs-chit-foot">
                            Answers come through with the sale, so the pass list and the guest list are the same list.
                        </p>
                    </div>

                    <!-- The kitchen's own sheet, written up from that list. -->
                    <div class="rs-prep" aria-hidden="true">
                        <b>Prep &middot; Saturday</b>
                        <span><i>Covers</i><i>{{ $sitting['sold'] }}</i></span>
                        <span><i>Beef</i><i>9</i></span>
                        <span><i>Halibut</i><i>7</i></span>
                        <span><i>Squash</i><i>3</i></span>
                        <span><i>Pairing</i><i>11</i></span>
                        <em>1 nut &middot; 1 shellfish</em>
                    </div>
                </div>

                <div>
                    <div class="rs-head rs-head-l">
                        <span class="rs-dish" aria-hidden="true" data-reveal="zoom">III</span>
                        <span class="rs-kick" aria-hidden="true" data-reveal>The main</span>
                        <p class="rs-sc rs-tag" data-reveal style="--reveal-delay: 0.05s;">The questions</p>
                        <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.1s;">
                            Ask about the nut allergy <span class="rs-em">in January</span>.
                        </h2>
                    </div>
                    <p class="rs-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Not at seven on the night, from a section that is already down. Questions can
                        sit on the ticket and be answered when people pay, so the kitchen has the
                        list before it writes the prep.
                    </p>

                    <ul class="rs-list" data-reveal-group="90">
                        @foreach ([
                            ['One list, not two', 'The answers are attached to the sale, so you are not reconciling an email thread against a booking list at four in the afternoon.', false],
                            ['Ask what you actually need', 'Allergies as free text, a course choice from a set of options, a yes or no on the pairing. It is your question, not a fixed field.', false],
                            ['Export it', 'Take the sales out as a CSV with the answers included, and hand the kitchen something it can read.', true],
                        ] as [$t, $d, $qPro])
                            <li data-reveal>
                                <div>
                                    <strong>{{ $t }}<span class="rs-sc rs-tier {{ $qPro ? 'rs-tier-pro' : '' }}">{{ $qPro ? 'Pro' : 'Free' }}</span></strong>
                                    <span>{{ $d }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <p class="rs-note rs-note-l" data-reveal>
                        <span class="rs-sc rs-tier">Free</span>
                        A question on the ticket works on every plan, with the person booking answering once for their party. On Pro, each guest can answer for themselves.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. Private hire (IV)                                         -->
        <!-- ============================================================ -->
        <section id="hire" class="rs-sec rs-alt" style="scroll-margin-top: 4.5rem;">
            <div class="rs-wrap">
                <div class="rs-head">
                    <span class="rs-dish" aria-hidden="true" data-reveal="zoom">IV</span>
                    <span class="rs-kick" aria-hidden="true" data-reveal>In the private room</span>
                    <p class="rs-sc rs-tag" data-reveal style="--reveal-delay: 0.05s;">Private hire</p>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.1s;">
                        The room at the back <span class="rs-em">has its own link</span>.
                    </h2>
                    <p class="rs-sub" data-reveal style="--reveal-delay: 0.15s;">
                        Private dining is a different conversation from a wine dinner, and it does not
                        belong in the same list as the things anyone can buy.
                    </p>
                </div>

                <div class="rs-tent-stage" aria-hidden="true" data-reveal>
                    <div class="rs-tent">
                        <div class="rs-tent-back"></div>
                        <div class="rs-tent-face">
                            <span class="rs-sc">The back room</span>
                            <strong>Reserved</strong>
                            <span class="rs-sc">Private dining &middot; by enquiry</span>
                        </div>
                    </div>
                    <div class="rs-tent-shadow"></div>
                </div>

                <div class="rs-trio" data-reveal-group="100">
                    @foreach ([
                        ['Enquiries come to the page', 'Turn on booking requests and people ask about a date through your schedule instead of a form that lands in a shared inbox nobody owns.'],
                        ['Nothing posts without you', 'Every enquiry waits for you to accept it, and you are emailed when new ones are waiting. The public page only ever shows what you agreed to.'],
                        ['Its own strand, its own link', 'Put private dining on a sub-schedule and you can share a link that shows only those events, without splitting the restaurant into two pages.'],
                    ] as [$t, $d])
                        <div data-reveal>
                            <h3 class="rs-item-name">{{ $t }}</h3>
                            <p class="rs-item-desc">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="rs-note" data-reveal>
                    <span class="rs-sc rs-tier">Free</span>
                    Enquiries, sub-schedules and the schedule itself cost nothing. An event can also stay a Draft until you are ready to announce it.
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. Who it is for (V)                                         -->
        <!-- ============================================================ -->
        <section id="who" class="rs-sec" style="scroll-margin-top: 4.5rem;">
            <div class="rs-wrap">
                <div class="rs-head">
                    <span class="rs-dish" aria-hidden="true" data-reveal="zoom">V</span>
                    <span class="rs-kick" aria-hidden="true" data-reveal>For the table</span>
                    <p class="rs-sc rs-tag" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.1s;">
                        Any night you have to <span class="rs-em">shop for</span>.
                    </h2>
                </div>

                @php
                    $rsGuests = [
                        ['Fine Dining', 'A tasting menu with a fixed number of covers, paid up front, with the dietaries in before the order goes out.', 'for-fine-dining-restaurants'],
                        ['Wine Bars & Tapas', 'A tasting on a Tuesday, priced per head. Small counts, sold in advance, closed off before the bottles are pulled.', 'for-wine-bars-tapas'],
                        ['Farm-to-Table', 'The menu depends on what came in, so the count has to be settled early. Close sales, then go to the market.', 'for-farm-to-table-restaurants'],
                        ['Supper Clubs', 'The same night every month, each one with its own count, so selling out in March leaves April untouched.', 'for-supper-clubs'],
                        ['Casual Dining', 'Quiz nights, live music, a set menu at Christmas. Most weeks nothing, and a page for the weeks there is something.', 'for-casual-dining-restaurants'],
                        ['Chef\'s Tables & Pop-Ups', 'Twelve seats in somebody else\'s room. Sell them all in advance and take the questions while you are at it.', 'for-chefs-tables'],
                    ];
                    $rsRoman = ['i', 'ii', 'iii', 'iv', 'v', 'vi'];
                @endphp

                <div class="rs-guests" data-reveal-group="70">
                    @foreach ($rsGuests as $rsG => [$rsName, $rsDesc, $rsSlug])
                        @php $rsPost = get_sub_audience_blog($rsSlug); @endphp
                        <article class="rs-guest" data-reveal>
                            <span class="rs-sc rs-guest-no" aria-hidden="true">{{ $rsRoman[$rsG] }}.</span>
                            <div>
                                <h3>{{ $rsName }}</h3>
                                <p>{{ $rsDesc }}</p>
                                @if ($rsPost)
                                    <a href="{{ blog_url('/' . $rsPost->slug) }}" class="rs-sc" aria-label="Learn more about Event Schedule for {{ $rsName }}">
                                        Learn more
                                        {!! $rsArrow !!}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. How it works: three plates on a wine cloth                -->
        <!-- ============================================================ -->
        <section id="how" class="rs-service" style="scroll-margin-top: 4.5rem;">
            <div class="rs-wrap">
                <div class="rs-head">
                    <span class="rs-kick" aria-hidden="true" data-reveal>The service</span>
                    <p class="rs-sc rs-tag" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.1s;">
                        Three decisions, <span class="rs-em">then it runs</span>.
                    </h2>
                    <div class="rs-orn" aria-hidden="true" style="width: 100%; margin-top: 0.6rem;"><i></i></div>
                </div>

                <div class="rs-steps" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Set the covers', 'The quantity is how many you will cook for. Selling at more than one price? Combined Total keeps them in one pool rather than one count each.'],
                        ['02', 'Set the cutoff', 'A date and time for sales to stop, chosen so you still have a morning to buy against a final number.'],
                        ['03', 'Set the questions', 'Allergies, a course choice, a pairing. Answered at checkout and attached to the sale.'],
                    ] as [$n, $t, $d])
                        <div class="rs-step" data-reveal="panel">
                            <div class="rs-setting" aria-hidden="true">
                                <i class="rs-fork"></i>
                                <span class="rs-dish">{{ $n }}</span>
                                <i class="rs-knife"></i>
                            </div>
                            <h3>{{ $t }}</h3>
                            <p>{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. Key features                                              -->
        <!-- ============================================================ -->
        <section class="rs-sec" style="border-top: 0;">
            <div class="rs-narrow">
                <div class="rs-head">
                    <span class="rs-kick" aria-hidden="true" data-reveal>From the kitchen</span>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.05s;">Key features</h2>
                </div>

                <div class="rs-carte" data-reveal-group="70">
                    @foreach ([
                        ['Ticketing', 'A fixed number of covers, a sales cutoff, and zero platform fees', marketing_url('/features/ticketing')],
                        ['Custom Fields', 'Ask for allergies or a course choice, answered at checkout', marketing_url('/features/custom-fields')],
                        ['Sub-schedules', 'Give private dining its own strand and its own link', marketing_url('/features/sub-schedules')],
                        ['Newsletters', 'Tell the regulars before the seats are gone', marketing_url('/features/newsletters')],
                    ] as [$rsFeature, $rsFeatureDesc, $rsFeatureUrl])
                        <a href="{{ $rsFeatureUrl }}" data-reveal>
                            <span>
                                <strong>{{ $rsFeature }}</strong>
                                <span>{{ $rsFeatureDesc }}</span>
                            </span>
                            {!! $rsArrow !!}
                        </a>
                    @endforeach
                </div>

                <p class="rs-centre" data-reveal>
                    <a href="{{ marketing_url('/features') }}" class="rs-more rs-sc">
                        See all features
                        {!! $rsArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <div class="rs-plans">
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 9. Related pages                                             -->
        <!-- ============================================================ -->
        <section class="rs-sec">
            <div class="rs-narrow">
                <div class="rs-head">
                    <span class="rs-kick" aria-hidden="true" data-reveal>Down the street</span>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.05s;">Related pages</h2>
                </div>

                <div class="rs-next" data-reveal-group="70">
                    @foreach ([
                        ['/for-bars', 'Bars'],
                        ['/for-breweries-and-wineries', 'Breweries &amp; Wineries'],
                        ['/for-food-trucks-and-vendors', 'Food Trucks'],
                        ['/for-hotels-and-resorts', 'Hotels &amp; Resorts'],
                    ] as [$relHref, $relName])
                        <a href="{{ marketing_url($relHref) }}" data-reveal>
                            <span>
                                <small class="rs-sc">Event Schedule for</small>
                                <strong>{!! $relName !!}</strong>
                            </span>
                            {!! $rsArrow !!}
                        </a>
                    @endforeach
                </div>

                <p class="rs-centre" data-reveal>
                    <a href="{{ marketing_url('/use-cases') }}" class="rs-more rs-sc">
                        See all use cases
                        {!! $rsArrow !!}
                    </a>
                </p>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. FAQ (VI)                                                 -->
        <!-- ============================================================ -->
        <section id="faq" class="rs-sec rs-alt" style="scroll-margin-top: 4.5rem;">
            <div class="rs-narrow">
                <div class="rs-head">
                    <span class="rs-dish" aria-hidden="true" data-reveal="zoom">VI</span>
                    <span class="rs-kick" aria-hidden="true" data-reveal>With the coffee</span>
                    <p class="rs-sc rs-tag" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.1s;">
                        Asked <span class="rs-em">across the pass</span>.
                    </h2>
                </div>

                <div class="rs-faq" data-reveal>
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
        <!-- 11. Finale: a place laid                                     -->
        <!-- ============================================================ -->
        <section id="claim" class="rs-sec rs-last" style="scroll-margin-top: 4.5rem;">
            <div class="rs-wrap">
                <div class="rs-head" style="margin-bottom: 0;">
                    <p class="rs-sc rs-tag" data-reveal>Free to start</p>
                    <h2 class="rs-h2" data-reveal style="--reveal-delay: 0.05s;">
                        Cook for the number <span class="rs-em">that actually sold</span>.
                    </h2>
                    <p class="rs-sub" data-reveal style="--reveal-delay: 0.1s;">
                        The schedule is free, and so is every free sitting you take names for. Pro at
                        {{ plan_price($proMonthly) }} a month is what puts a price on a cover, and none of the ticket price comes to us.
                    </p>
                </div>

                <div class="rs-place" data-reveal="panel">
                    <span class="rs-charger" aria-hidden="true"></span>
                    <i class="rs-fork" aria-hidden="true"></i>
                    <i class="rs-knife" aria-hidden="true"></i>

                    <div class="rs-book">
                        <label for="es-claim-input" class="rs-sc rs-book-label">Your schedule name</label>
                        <div class="rs-book-form">
                            <div dir="ltr" class="es-claim rs-claim">
                                <input id="es-claim-input" type="text" placeholder="your-restaurant" autocomplete="off" spellcheck="false" maxlength="30">
                                <span>.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up?type=venue') }}" class="rs-btn">
                                Put a sitting on sale
                                {!! $rsArrow !!}
                            </a>
                        </div>
                        <p class="rs-book-note">No credit card required</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="rs-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
