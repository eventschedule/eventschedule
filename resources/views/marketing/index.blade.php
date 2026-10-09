<x-marketing-layout>
    {{-- Live poster wall data: real upcoming events (same /browse visibility
         rules as the rail), padded with decorative demo flyers. Decorative
         cards never carry names or links.

         Computed up here, before the slots, because <x-slot name="headMeta"> below needs the
         first image's origin to decide whether a preconnect is worth issuing. --}}
    @php
        // Up to 25 unique posters: the phone strip shows them all in a row, and the desktop wall
        // deals them into four columns, two to each wing (demo flyers only backfill any
        // remaining slots).
        $wallTarget = 25;
        // How many posters may load eagerly. Both breakpoints draw their eager set from the
        // SAME first cards, and the desktop set (one per column = cards 0-3) is a subset of
        // the mobile set (the first 6 of the strip) - so whichever of the two is displayed,
        // the browser is never asked for more than this many images before first paint.
        // Everything else, in both marquee copies, is lazy and low priority.
        $wallEagerCount = 6;
        $wallEventCards = [];
        foreach ($discoverEvents as $wallEvent) {
            if (count($wallEventCards) >= $wallTarget) {
                break;
            }
            $wallUrl = $wallEvent->getGuestUrl();
            // 480px WebP derivative when one exists: the slots here are 96 and 208 CSS px,
            // and the originals are up to 4MB PNGs.
            $wallImg = $wallEvent->getImageUrl(480);
            if (! $wallUrl || ! $wallImg) {
                continue;
            }
            $wallEventCards[] = [
                'url' => $wallUrl,
                'img' => $wallImg,
                'name' => $wallEvent->name,
                'date' => $wallEvent->starts_at
                    ? $wallEvent->getShortDateRangeDisplay('M j')
                    : __('messages.recurring'),
            ];
        }
        $wallCards = $wallEventCards;
        $wallDemoFlyers = ['jazz', 'party', 'rock', 'dj', 'comedy', 'openmic', 'special'];
        $wallDemoIndex = 0;
        while (count($wallCards) < $wallTarget) {
            $wallCards[] = [
                'url' => null,
                'img' => asset('images/demo/demo_flyer_' . $wallDemoFlyers[$wallDemoIndex % count($wallDemoFlyers)] . '.webp'),
                'name' => null,
                'date' => null,
            ];
            $wallDemoIndex++;
        }
        foreach ($wallCards as $wallIndex => $wallCard) {
            $wallCards[$wallIndex]['eager'] = $wallIndex < $wallEagerCount;
            // fetchpriority="high" on card 0 only. It is the first strip poster on a phone and
            // row 0 of column 0 on the wall, so it is the same URL in both breakpoints' markup:
            // marking it in both places still issues ONE high-priority request, whichever of the
            // two is displayed. Any more than one and "high" stops meaning anything against the
            // CSS and fonts the hero text needs.
            $wallCards[$wallIndex]['priority'] = $wallIndex === 0;
        }
        // Warm the connection to whichever host is actually serving the posters, derived from
        // the first card rather than hardcoded: a selfhost install serves them from its own
        // origin and must not be made to open a socket to our CDN.
        $wallPreconnect = null;
        foreach ($wallCards as $wallCard) {
            if (! str_starts_with($wallCard['img'], 'http')) {
                continue;
            }
            $wallImgHost = parse_url($wallCard['img'], PHP_URL_HOST);
            if ($wallImgHost && $wallImgHost !== request()->getHost()) {
                $wallPreconnect = (parse_url($wallCard['img'], PHP_URL_SCHEME) ?: 'https') . '://' . $wallImgHost;
            }
            break;
        }
        // Four columns, two to a wing. Row 0 of column N is card N, which is what keeps the
        // wall's eager set (cards 0-3) inside the strip's (cards 0-5).
        $wallColumns = [[], [], [], []];
        foreach ($wallCards as $wallIndex => $wallCard) {
            $wallColumns[$wallIndex % 4][] = $wallCard;
        }
        // Each looping half has to outrun the hero, which holds the headline AND the showreel
        // and so stands about two screens tall: seven posters is a little over 2,000px.
        // A column short of seven is topped up from its MIDDLE: topped up from its first poster,
        // the half ended on the poster the next half begins with, and the seam showed it twice
        // running.
        $wallColumns = array_map(function ($column) {
            $own = count($column);
            $half = $column;
            $i = 0;
            while (count($half) < 7) {
                $half[] = $column[(intdiv($own, 2) + $i) % $own];
                $i++;
            }
            return $half;
        }, $wallColumns);
        $wallDurations = [92, 118, 104, 84];

        // The page's typeface is one variable file for the Latin alphabet. Its name is read out of
        // the bundled stylesheet rather than typed here, so re-downloading the fonts cannot leave
        // a preload pointing at a file that is gone.
        $hpFontDir = 'vendor/fonts/Red_Hat_Display/';
        $hpFontCss = (string) @file_get_contents(public_path($hpFontDir . 'font.css'));
        $hpFontFile = preg_match('~/\* latin \*/.*?url\(([^)]+\.woff2)\)~s', $hpFontCss, $hpFontMatch) ? $hpFontMatch[1] : null;
    @endphp

    {{-- The search result is its own pair of strings and does NOT follow the headline test below.
         For four days in 2026-10 the title was the headline with the brand after it and the
         description was the subtitle; search clicks fell and it was put back. The title opens on
         the brand and says "Free Event Calendar", the page's keyword (config/marketing_keywords.php);
         HeroExperimentTest fails the build if the headline test reaches either tag again. --}}
    <x-slot name="title">{{ __('marketing.home_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.home_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">Home</x-slot>
    <x-slot name="preload">
        @if ($hpFontFile)
            {{-- The whole first screen is set in this one file, so it is asked for at once
                 instead of after the stylesheet that names it has arrived. --}}
            <link rel="preload" as="font" type="font/woff2" href="{{ asset($hpFontDir . $hpFontFile) }}" crossorigin>
        @endif
    </x-slot>
    <x-slot name="headMeta">
        @if ($wallPreconnect)
            <link rel="preconnect" href="{{ $wallPreconnect }}">
        @endif
        {{-- The page's own typeface, from the fonts the app already bundles (never a CDN). It is
             one variable file, so the heavier display weights below cost no second download. --}}
        @if (font_stylesheet_url('Red Hat Display'))
            <link rel="stylesheet" href="{{ font_stylesheet_url('Red Hat Display') }}">
        @endif
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    {{-- Every note in this stylesheet is a Blade comment, so none of them is sent to a visitor. --}}
    <style {!! nonce_attr() !!}>
        {{-- ==============================================================
           The homepage. One show, told in the film's three verbs (which
           are the headline's own, in its control arm): the hero stands
           between two walls of real posters, the features are that show's
           three acts (plan, promote, sell), and the page goes dark for the
           night of the show itself.

           Every class here is the page's own (hp-*) or is restated
           under #hp, and all of it is drawn with the page's own tokens,
           so nothing leaks into the shared chrome. The
           shared es-* motion system (marketing.css, marketing-home.js)
           still drives the entrances.
           ============================================================== --}}

        #hp {
            --hp-bg: #f4f6fb;
            --hp-bg-2: #ffffff;
            --hp-bg-3: #e9edf6;
            --hp-ink: #0a1020;
            --hp-ink-2: #36405a;
            --hp-ink-3: #56617c;
            --hp-line: rgba(10, 16, 32, 0.1);
            --hp-line-2: rgba(10, 16, 32, 0.17);
            --hp-blue: #2f66ea;
            --hp-soft: #4767b0;
            --hp-glow: rgba(78, 129, 250, 0.22);
            --hp-card-shadow: 0 1px 2px rgba(10, 16, 32, 0.05), 0 18px 40px -22px rgba(10, 16, 32, 0.28);
            --hp-pop-shadow: 0 2px 4px rgba(10, 16, 32, 0.06), 0 28px 60px -24px rgba(10, 16, 32, 0.4);
            --hp-display: 'Red Hat Display', 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            --hp-mono: ui-monospace, 'SF Mono', SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            position: relative;
            background: var(--hp-bg);
            color: var(--hp-ink);
            font-family: var(--hp-display);
            font-size: 1.0625rem;
            line-height: 1.6;
        }
        .dark #hp {
            --hp-bg: #070a14;
            --hp-bg-2: #0e1424;
            --hp-bg-3: #0a0f1c;
            --hp-ink: #eef2ff;
            --hp-ink-2: #c5cde2;
            --hp-ink-3: #97a3c0;
            --hp-line: rgba(255, 255, 255, 0.09);
            --hp-line-2: rgba(255, 255, 255, 0.17);
            --hp-blue: #8db0ff;
            --hp-soft: #9fb4e6;
            --hp-glow: rgba(78, 129, 250, 0.34);
            --hp-card-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 24px 50px -26px rgba(0, 0, 0, 0.9);
            --hp-pop-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.07), 0 30px 70px -24px rgba(0, 0, 0, 0.95);
        }

        {{-- The bar above takes the page's ground, so the hero starts at the top of the window. --}}
        body > header.sticky {
            background-color: rgba(244, 246, 251, 0.9);
            border-bottom-color: rgba(10, 16, 32, 0.08);
        }
        .dark body > header.sticky {
            background-color: rgba(7, 10, 20, 0.88);
            border-bottom-color: rgba(255, 255, 255, 0.08);
        }

        #hp ::selection { background: rgba(78, 129, 250, 0.32); }
        #hp a:focus-visible,
        #hp summary:focus-visible,
        #hp button:focus-visible {
            outline: 3px solid var(--hp-blue);
            outline-offset: 3px;
            border-radius: 0.5rem;
        }

        .hp-wrap { width: min(100% - 2.5rem, 76rem); margin-inline: auto; }
        {{-- An act hangs its tile in the margin where the window has one, so its text starts on
           the same line as every other section's. --}}
        @media (min-width: 99rem) {
            .hp-wrap.is-hung {
                width: auto;
                margin-inline-start: calc((100% - 76rem) / 2 - var(--hp-tile, 6.25rem) - 4rem);
                margin-inline-end: calc((100% - 76rem) / 2);
            }
            .hp-wrap.is-hung .hp-vrow,
            .hp-wrap.is-hung .hp-run,
            .hp-wrap.is-hung .hp-beat { column-gap: 4rem; }
            .hp-wrap.is-hung .hp-run::before,
            html.es-anim .hp-wrap.is-hung .hp-run::after { inset-inline-start: calc(var(--hp-tile) / 2); }
        }
        .hp-sec { position: relative; padding-block: clamp(4.5rem, 9vw, 8.5rem); }
        #hp section[id] { scroll-margin-top: 4.5rem; }
        .hp-alt { background: var(--hp-bg-2); }
        .dark .hp-alt { background: var(--hp-bg-3); }

        {{-- Type. One family; the display voice is the same file at a heavier cut. --}}
        .hp-h1,
        .hp-h2,
        .hp-h3,
        .hp-num {
            font-weight: 700;
            letter-spacing: -0.04em;
            text-wrap: balance;
        }
        .hp-h1 {
            font-variation-settings: 'wght' 840;
            font-size: clamp(2.45rem, 10.4vw, 4.25rem);
            line-height: 1.02;
        }
        .hp-h2 {
            font-variation-settings: 'wght' 820;
            font-size: clamp(2rem, 3.1vw + 0.9rem, 3.6rem);
            line-height: 1.04;
        }
        .hp-h3 {
            font-variation-settings: 'wght' 790;
            font-size: clamp(1.6rem, 1.4vw + 1.05rem, 2.25rem);
            line-height: 1.08;
            letter-spacing: -0.035em;
        }
        .hp-lead {
            font-size: clamp(1.1rem, 0.5vw + 1rem, 1.3rem);
            line-height: 1.55;
            color: var(--hp-ink-2);
            text-wrap: pretty;
        }
        .hp-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-family: var(--hp-mono);
            font-size: 0.78rem;
            font-weight: 700; font-variation-settings: 'wght' 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--hp-ink-3);
        }
        .hp-kicker::before {
            content: "";
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 0.15rem;
            background: linear-gradient(135deg, #4e81fa, #22d3ee);
        }
        .hp-ink-grad {
            background: linear-gradient(100deg, #2f66ea 0%, #0b8fd8 55%, #0aa5c4 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            padding-inline-end: 0.06em;
        }
        .dark .hp-ink-grad { background-image: linear-gradient(100deg, #7da5ff 0%, #38bdf8 55%, #5eead4 100%); }
        {{-- The gradient is for the page's big moments (the headline, the three verbs, the night, the
           figures, the last panel). A heading's second sentence is the same ink, quieter. --}}
        .hp-soft { color: var(--hp-soft); }

        .hp-head { max-width: 46rem; }
        .hp-head.is-center { margin-inline: auto; text-align: center; }
        #open-source > .hp-wrap > .hp-head { max-width: 62rem; }
        #open-source > .hp-wrap > .hp-head .hp-lead { max-width: 46rem; margin-inline: auto; }
        .hp-head .hp-h2 { margin-top: 1rem; }
        .hp-head .hp-lead { margin-top: 1.1rem; }

        {{-- Buttons and links --}}
        .hp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            min-height: 3.6rem;
            padding: 0 1.7rem;
            border-radius: 1rem;
            font-size: 1.0625rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            white-space: nowrap;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }
        .hp-btn svg { width: 1.15rem; height: 1.15rem; transition: transform 0.2s ease; }
        .hp-btn:hover svg { transform: translateX(3px); }
        [dir="rtl"] #hp .hp-btn svg,
        [dir="rtl"] #hp .hp-more svg { transform: scaleX(-1); }
        .hp-btn-primary {
            color: #fff;
            background: linear-gradient(100deg, #2b5fe3, #2f6fe9);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 14px 34px -12px rgba(47, 102, 234, 0.75), 0 0 0 1px rgba(47, 102, 234, 0.5);
        }
        .hp-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.28), 0 22px 44px -14px rgba(47, 102, 234, 0.9), 0 0 0 1px rgba(47, 102, 234, 0.6), 0 0 44px -6px rgba(34, 211, 238, 0.55);
        }
        .hp-btn-ghost {
            color: var(--hp-ink);
            background: var(--hp-bg-2);
            border: 1px solid var(--hp-line-2);
            box-shadow: var(--hp-card-shadow);
        }
        .hp-btn-ghost:hover { transform: translateY(-2px); border-color: var(--hp-blue); }
        .hp-more {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            min-height: 1.75rem;
            font-weight: 700;
            color: var(--hp-blue);
            transition: gap 0.2s ease;
        }
        .hp-more:hover { gap: 0.6rem; }
        .hp-more svg { width: 1rem; height: 1rem; }
        .hp-inline {
            font-weight: 700;
            color: var(--hp-blue);
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 0.2em;
        }

        .hp-ico {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.85rem;
        }
        .hp-ico svg { width: 1.45rem; height: 1.45rem; }

        {{-- ---------------------------------------------------------------
           1. Hero: the headline between two walls of real posters
           --------------------------------------------------------------- --}}

        #hp .hp-hero {
            --hp-col: 40rem;
            position: relative;
            isolation: isolate;
            overflow: hidden;
            overflow: clip;
            padding-top: clamp(2.75rem, 7vh, 5.25rem);
        }
        @media (min-width: 1024px) {
            #hp .hp-hero { --hp-col: min(56vw, 62rem); }
        }
        {{-- A week of seven columns behind the headline: the page is about a calendar. --}}
        .hp-hero-sky {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(62rem 34rem at 50% -9rem, rgba(78, 129, 250, 0.2), transparent 70%),
                radial-gradient(36rem 26rem at 8% 18rem, rgba(34, 211, 238, 0.14), transparent 70%),
                radial-gradient(36rem 26rem at 92% 22rem, rgba(14, 165, 233, 0.13), transparent 70%);
        }
        .dark .hp-hero-sky {
            background:
                radial-gradient(62rem 34rem at 50% -9rem, rgba(78, 129, 250, 0.34), transparent 70%),
                radial-gradient(36rem 26rem at 8% 18rem, rgba(34, 211, 238, 0.14), transparent 70%),
                radial-gradient(36rem 26rem at 92% 22rem, rgba(14, 165, 233, 0.16), transparent 70%);
        }
        .hp-hero-sky::after {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 46rem;
            background-image:
                linear-gradient(var(--hp-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--hp-line) 1px, transparent 1px);
            background-size: calc(var(--hp-col) / 7) 6.5rem;
            background-position: 50% 0;
            -webkit-mask-image: radial-gradient(ellipse 34rem 24rem at 50% 17rem, #000 10%, transparent 72%);
            mask-image: radial-gradient(ellipse 34rem 24rem at 50% 17rem, #000 10%, transparent 72%);
        }

        .hp-hero-copy {
            position: relative;
            z-index: 10;
            width: min(100% - 2.5rem, var(--hp-col));
            margin-inline: auto;
            text-align: center;
            pointer-events: none;
        }
        .hp-hero-copy a,
        .hp-hero-copy .es-claim,
        .hp-hero-copy .hp-strip { pointer-events: auto; }

        #hp .es-hero-eyebrow { font-family: var(--hp-display); font-variation-settings: normal; letter-spacing: normal; }
        .hp-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: clamp(1.4rem, 3vh, 2rem);
            padding: 0.45rem 1rem 0.45rem 0.8rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 999px;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            font-size: 0.92rem;
            font-weight: 400;
            color: var(--hp-ink-2);
            white-space: normal;
        }
        .hp-live { position: relative; display: inline-flex; width: 0.55rem; height: 0.55rem; }
        .hp-live i,
        .hp-live::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: #16a34a;
        }
        .hp-live::before { animation: hp-ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite; opacity: 0.6; }
        @keyframes hp-ping { 75%, 100% { transform: scale(2.6); opacity: 0; } }

        @media (min-width: 1024px) {
            {{-- One line each: .es-mask raises a line as a block, so a wrapped line would rise as a slab.
               The size follows the window so the longer line always fits between the wings. --}}
            .hp-h1 { font-size: min(4.6vw, 5.2rem); white-space: nowrap; line-height: 1; }
        }
        #hp .hp-h1 .text-gradient {
            background-image: linear-gradient(100deg, #2f66ea 0%, #0b8fd8 50%, #06b0cf 100%);
            padding-inline-end: 0.05em;
        }
        .dark #hp .hp-h1 .text-gradient { background-image: linear-gradient(100deg, #6f9bff 0%, #38bdf8 50%, #67e8f9 100%); }
        {{-- The mask hides the line below its own foot while it rises; sideways it must cut nothing,
           so a line that runs a few pixels long in a fallback font is still whole. --}}
        #hp .hp-h1 .es-mask { padding-bottom: 0.16em; margin-bottom: -0.16em; overflow-x: visible; overflow-y: clip; }

        .hp-sub {
            max-width: 41rem;
            margin: clamp(1.1rem, 2.4vh, 1.6rem) auto 0;
            font-size: clamp(1.075rem, 0.55vw + 0.95rem, 1.32rem);
            line-height: 1.5;
            color: var(--hp-ink-2);
            text-wrap: pretty;
        }

        {{-- The claim box: the name you type rides into sign-up. --}}
        .hp-claimrow {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-width: 38rem;
            margin: clamp(1.6rem, 3.4vh, 2.3rem) auto 0;
        }
        @media (min-width: 640px) {
            .hp-claimrow { flex-direction: row; }
        }
        #hp .hp-claim {
            display: flex;
            flex: 1 1 0;
            min-width: 0;
            align-items: center;
            padding: 1rem 1.2rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 1rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            font-family: var(--hp-mono);
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        #hp .hp-claim input {
            flex: 1 1 0;
            width: 0;
            min-width: 0;
            border: 0;
            background: transparent;
            padding-inline: 0;
            box-shadow: none;
            outline: 0;
            text-align: right;
            font-family: inherit;
            font-size: inherit;
            font-weight: 700;
            color: var(--hp-ink);
        }
        #hp .hp-claim:focus-within { border-color: var(--hp-blue); box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.3); }
        #hp .hp-finale .hp-claim:focus-within { border-color: #8db0ff; box-shadow: 0 0 0 4px rgba(141, 176, 255, 0.35); }
        #hp .hp-claim input::placeholder { color: var(--hp-ink-3); opacity: 1; font-weight: 400; }
        [data-claim-echo] { display: inline-block; max-width: 44vw; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }
        #hp .hp-claim input:focus { box-shadow: none; outline: 0; border: 0; }
        .hp-claim-suffix { flex: none; color: var(--hp-ink-3); user-select: none; }
        @media (max-width: 479px) {
            {{-- Room for the name: the field keeps its 16px (a smaller one makes a phone zoom in), the
               ending it cannot change gives way. --}}
            #hp .hp-claim { padding-inline: 1rem; }
            .hp-claim-suffix { font-size: 0.84rem; }
        }

        .hp-hero-foot {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.4rem 1.4rem;
            margin-top: 1.15rem;
            font-size: 0.95rem;
            color: var(--hp-ink-3);
        }
        .hp-demo {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            min-height: 1.75rem;
            font-weight: 700;
            color: var(--hp-ink);
        }
        .hp-demo i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 999px;
            background: var(--hp-ink);
            color: var(--hp-bg);
            transition: transform 0.2s ease, background-color 0.2s ease;
        }
        .hp-demo i svg { width: 0.7rem; height: 0.7rem; margin-left: 0.1rem; }
        .hp-demo:hover i { transform: scale(1.12); background: var(--hp-blue); }

        {{-- Phones and tablets: the posters as one strip under the claim box. --}}
        .hp-strip { margin-top: 2.5rem; }
        .hp-strip a,
        .hp-strip div.hp-strip-card {
            display: block;
            flex: none;
            width: 6rem;
            overflow: hidden;
            border-radius: 0.6rem;
            box-shadow: 0 8px 20px -10px rgba(10, 16, 32, 0.5);
        }
        .hp-strip img { display: block; width: 100%; aspect-ratio: 3 / 4; object-fit: cover; }

        {{-- The wall (desktop): two wings hinged beside the headline, their outer edges nearer
           the reader, so nothing ever sits behind the words. --}}
        #hp .es-wall {
            z-index: 1;
            {{-- clip, where it exists: a hidden box can still be scrolled by focusing a link in it. --}}
            overflow: hidden;
            overflow: clip;
        }
        #hp .es-wall-tilt {
            position: absolute;
            inset: 0;
            transform: translate3d(var(--tx, 0px), var(--ty, 0px), 0);
            will-change: transform;
        }
        .hp-wing {
            position: absolute;
            top: -7rem;
            display: flex;
            gap: 1.25rem;
        }
        .hp-wing-l {
            right: calc(50% + var(--hp-col) / 2 + 2rem);
            transform-origin: 100% 26rem;
            {{-- The perspective is part of each wing's own transform: handed down from the wall
               through a preserve-3d parent, Safari drew the wings with no depth at all. --}}
            transform: perspective(1000px) rotateY(52deg);
            -webkit-mask-image: linear-gradient(to left, transparent 0, #000 38%);
            mask-image: linear-gradient(to left, transparent 0, #000 38%);
        }
        .hp-wing-r {
            left: calc(50% + var(--hp-col) / 2 + 2rem);
            transform-origin: 0% 26rem;
            transform: perspective(1000px) rotateY(-52deg);
            -webkit-mask-image: linear-gradient(to right, transparent 0, #000 38%);
            mask-image: linear-gradient(to right, transparent 0, #000 38%);
        }
        {{-- Doors open: the two walls swing in from flat against the sides as the page arrives. --}}
        html.es-anim .hp-wing-l { animation: hp-door-l 1.5s cubic-bezier(0.2, 0.8, 0.2, 1) 0.1s both; }
        html.es-anim .hp-wing-r { animation: hp-door-r 1.5s cubic-bezier(0.2, 0.8, 0.2, 1) 0.1s both; }
        html.es-anim .hp-mine-plane { animation: hp-door-l 1.5s cubic-bezier(0.2, 0.8, 0.2, 1) 0.1s both; }
        @keyframes hp-door-l {
            from { transform: perspective(1000px) rotateY(89deg); opacity: 0; }
            25%  { opacity: 1; }
            to   { transform: perspective(1000px) rotateY(52deg); opacity: 1; }
        }
        @keyframes hp-door-r {
            from { transform: perspective(1000px) rotateY(-89deg); opacity: 0; }
            25%  { opacity: 1; }
            to   { transform: perspective(1000px) rotateY(-52deg); opacity: 1; }
        }

        {{-- Your poster. A plane laid exactly over the left wall, so the poster is in the wall's
           perspective, but with no fade of its own, so it is never washed out at the inner edge. --}}
        .hp-mine-plane {
            position: absolute;
            top: -7rem;
            right: calc(50% + var(--hp-col) / 2 + 2rem);
            z-index: 2;
            width: 27.25rem;
            height: 64rem;
            transform-origin: 100% 26rem;
            transform: perspective(1000px) rotateY(52deg);
            pointer-events: none;
        }
        .hp-mine-plane .hp-mine { position: absolute; top: 21.5rem; right: 1.75rem; width: 14.5rem; pointer-events: auto; cursor: text; }
        @media (max-width: 1439px) {
            {{-- A laptop's wall is narrower and the film's frame stands higher. --}}
            .hp-mine-plane .hp-mine { --size: 1.95rem; top: 18rem; width: 11.5rem; }
        }
        .hp-mine {
            --fit: 1;
            --size: 2.5rem;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            aspect-ratio: 3 / 4;
            padding: 1.1rem 1.15rem 1.2rem;
            border: 2px dashed rgba(47, 102, 234, 0.5);
            border-radius: 0.9rem;
            background: linear-gradient(160deg, #eef3ff, #e1eaff);
            color: #3559c7;
            box-shadow: 0 20px 44px -20px rgba(20, 40, 100, 0.5);
            transform: rotate(-3deg);
            transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.5s ease, background-color 0.4s ease, border-color 0.4s ease, color 0.4s ease;
        }
        .hp-mine-top { display: flex; justify-content: space-between; gap: 0.5rem; font-family: var(--hp-mono); font-size: 0.6rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; }
        .hp-mine-top i { font-style: normal; }
        .hp-mine strong {
            display: block;
            overflow-wrap: break-word;
            font-size: calc(var(--size) * var(--fit));
            font-weight: 700;
            font-variation-settings: 'wght' 880;
            letter-spacing: -0.045em;
            line-height: 0.95;
            text-transform: uppercase;
        }
        .hp-mine-foot { font-family: var(--hp-mono); font-size: 0.62rem; font-weight: 700; line-height: 1.35; overflow-wrap: anywhere; }
        .dark .hp-mine { border-color: rgba(125, 165, 255, 0.55); background: linear-gradient(160deg, #111a38, #0d1430); color: #9fb7ee; box-shadow: 0 20px 44px -20px rgba(0, 0, 0, 0.9); }
        {{-- The empty poster wakes when the box beside it is being typed in. --}}
        #top:has(#es-claim-hero:focus) .hp-mine:not(.is-named) { border-color: #2f66ea; box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.2), 0 0 54px -6px rgba(34, 211, 238, 0.7), 0 20px 44px -20px rgba(20, 40, 100, 0.5); transform: rotate(-3deg) scale(1.03); }
        #hp .hp-mine.is-named {
            border: 0;
            padding: calc(1.1rem + 2px) calc(1.15rem + 2px) calc(1.2rem + 2px);
            background: linear-gradient(150deg, #2b5fe3 0%, #0b8fd8 55%, #22d3ee 100%);
            color: #fff;
            transform: rotate(-3deg) scale(1.06);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.25) inset, 0 26px 60px -18px rgba(47, 102, 234, 0.9), 0 0 70px -6px rgba(34, 211, 238, 0.7);
        }
        .hp-mine.is-named .hp-mine-top,
        .hp-mine.is-named .hp-mine-foot { color: rgba(255, 255, 255, 0.92); }
        .hp-mine-phone { display: grid; justify-items: center; }
        @media (min-width: 1024px) {
            {{-- From a laptop up the poster is on the wall; this one is the phone's. --}}
            #hp .hp-mine-phone { display: none; }
        }
        .hp-mine-phone .hp-mine { --size: 2.15rem; display: none; width: 12.5rem; margin-top: 1.75rem; text-align: start; }
        .hp-mine-phone .hp-mine.is-named { display: flex; animation: hp-paste 0.5s cubic-bezier(0.22, 1, 0.36, 1) both; }
        @keyframes hp-paste { from { opacity: 0; transform: rotate(4deg) scale(0.8) translateY(12px); } to { opacity: 1; transform: rotate(-3deg) scale(1.06); } }
        .hp-mine.is-large { --size: 3.3rem; width: min(100%, 19rem); }
        .hp-mine.is-large .hp-mine-top { font-size: 0.7rem; }
        .hp-mine.is-large .hp-mine-foot { font-size: 0.74rem; }

        @media (min-width: 2000px) {
            .hp-wing-l { -webkit-mask-image: linear-gradient(to left, transparent 0, #000 38%, #000 70%, transparent 100%); mask-image: linear-gradient(to left, transparent 0, #000 38%, #000 70%, transparent 100%); }
            .hp-wing-r { -webkit-mask-image: linear-gradient(to right, transparent 0, #000 38%, #000 70%, transparent 100%); mask-image: linear-gradient(to right, transparent 0, #000 38%, #000 70%, transparent 100%); }
        }
        .es-wall-col { flex: 0 0 auto; }
        .es-wall-track {
            display: flex;
            flex-direction: column;
            animation: es-wall-drift var(--dur, 90s) linear infinite;
            will-change: transform;
        }
        .es-wall-col:nth-child(even) .es-wall-track { animation-direction: reverse; }
        .es-wall-col:hover .es-wall-track { animation-play-state: paused; }
        @keyframes es-wall-drift {
            from { transform: translateY(0); }
            to   { transform: translateY(-50%); }
        }
        #hp .es-wall-card {
            position: relative;
            display: block;
            width: 13rem;
            margin-bottom: 1.25rem;
            overflow: hidden;
            border-radius: 0.9rem;
            background: var(--hp-bg-3);
            box-shadow: 0 22px 46px -22px rgba(10, 16, 32, 0.6);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        {{-- The wall rests washed back toward the page (half its colour by day, grey by night). A
           poster comes fully up while a light is on it (the page's script switches .is-lit as
           the two lights move) and while it is pointed at. Each picture carries its own wash: one
           veil over the whole wall with a hole cut for the light only worked in Chrome, and had
           to be repainted on every frame. --}}
        #hp .es-wall-card img { display: block; width: 100%; aspect-ratio: 3 / 4; object-fit: cover; opacity: 0.52; filter: grayscale(0.55) contrast(0.95); transition: filter 0.6s ease, opacity 0.6s ease; }
        .dark #hp .es-wall-card img { opacity: 0.4; filter: grayscale(1) contrast(0.92); }
        #hp .es-wall-card.is-lit img,
        #hp a.es-wall-card:hover img { opacity: 1; filter: none; }
        #hp .es-wall-card.is-lit .hp-wall-cap { opacity: 1; }
        .hp-wall-cap {
            position: absolute;
            inset: auto 0 0 0;
            padding: 2.2rem 0.8rem 0.65rem;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.85), transparent);
            color: #fff;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .hp-wall-cap b { display: block; overflow: hidden; font-size: 0.78rem; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .hp-wall-cap span { display: block; font-size: 0.68rem; opacity: 0.8; }
        #hp a.es-wall-card:hover {
            transform: scale(1.05);
            outline: 0;
            box-shadow: 0 0 0 2px rgba(78, 129, 250, 0.95), 0 26px 60px -18px rgba(78, 129, 250, 0.55);
        }
        #hp a.es-wall-card:hover .hp-wall-cap { opacity: 1; }
        @media (max-width: 1199px) {
            {{-- A small laptop's walls stand close enough to the words that a name on a lit poster
               could run under them. --}}
            #hp .hp-wall-cap { display: none; }
        }

        #hp .hp-hero::after {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            z-index: 3;
            height: 16rem;
            background: linear-gradient(to top, var(--hp-bg) 12%, transparent);
            pointer-events: none;
        }

        {{-- The screen: the showreel rises out of the hero, tilted until it is scrolled to. --}}
        .hp-stage {
            position: relative;
            z-index: 10;
            width: min(100% - 2rem, 72rem);
            margin: clamp(2.5rem, 6vh, 4.25rem) auto 0;
            padding-bottom: clamp(4rem, 8vw, 7rem);
            scroll-margin-top: 5.5rem;
        }
        .es-persp { perspective: 1200px; }
        .es-frame { transform-origin: 50% 10%; will-change: transform; transition: transform 0.18s linear; }
        @media (min-width: 1024px) {
            html.es-anim .es-frame { transform: perspective(1200px) rotateX(16deg) scale(0.93); }
        }
        .es-frame-glow {
            position: absolute;
            left: 10%; right: 10%; bottom: -34px;
            height: 80px;
            border-radius: 9999px;
            background: linear-gradient(90deg, #4E81FA, #0EA5E9, #22D3EE);
            filter: blur(52px);
            opacity: 0;
            pointer-events: none;
        }
        .hp-stage-cap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            margin-top: clamp(2.5rem, 5vw, 4rem);
            text-align: center;
        }
        .hp-stage-cap .hp-h2 { font-size: clamp(1.7rem, 2vw + 0.9rem, 2.6rem); }

        {{-- ---------------------------------------------------------------
           2. The line-up: who it is for, set like the names on a bill
           --------------------------------------------------------------- --}}

        .hp-lineup {
            position: relative;
            overflow: hidden;
            padding-block: clamp(1.75rem, 3.5vw, 3rem);
            border-block: 1px solid var(--hp-line);
            background: var(--hp-bg-2);
        }
        .dark .hp-lineup { background: var(--hp-bg-3); }
        #hp .hp-lineup .es-marquee-track { gap: 0; padding-right: 0; align-items: center; }
        .hp-lineup .es-marquee + .es-marquee { margin-top: clamp(0.35rem, 1vw, 0.9rem); }
        .hp-act {
            display: inline-flex;
            flex: none;
            align-items: center;
            gap: clamp(1rem, 2.2vw, 2.25rem);
            padding-inline-end: clamp(1rem, 2.2vw, 2.25rem);
            font-size: clamp(1.7rem, 3.3vw, 3.3rem);
            font-weight: 700;
            font-variation-settings: 'wght' 820;
            letter-spacing: -0.04em;
            line-height: 1.15;
            white-space: nowrap;
            color: var(--hp-ink);
            transition: color 0.2s ease;
        }
        .hp-act:hover { color: var(--hp-blue); }
        .hp-act i { flex: none; width: 0.3em; height: 0.3em; border-radius: 999px; }
        [data-marquee="-1"] .hp-act { color: var(--hp-ink-3); font-variation-settings: 'wght' 560; }
        [data-marquee="-1"] .hp-act:hover { color: var(--hp-blue); }

        {{-- ---------------------------------------------------------------
           3. Plan, promote, sell: one show in three acts
           --------------------------------------------------------------- --}}

        {{-- The switch: whose show the mock-ups tell. --}}
        .hp-casts { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; margin-top: clamp(1.75rem, 3vw, 2.5rem); }
        .hp-casts-label { flex-basis: 100%; margin-bottom: 0.15rem; font-size: 1.05rem; font-weight: 700; letter-spacing: -0.01em; color: var(--hp-ink); }
        .hp-casts button {
            min-height: 3.1rem;
            padding: 0 1.35rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 999px;
            background: var(--hp-bg-2);
            font: inherit;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--hp-ink-2);
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .hp-casts:not([data-ready]) { display: none; }
        #hp .hp-casts button:focus-visible { border-radius: 999px; }
        .hp-casts button:hover { border-color: var(--hp-blue); color: var(--hp-ink); transform: translateY(-1px); }
        .hp-casts button[aria-pressed="true"] { border-color: transparent; background: var(--hp-ink); color: var(--hp-bg); box-shadow: 0 10px 24px -12px rgba(10, 16, 32, 0.6); }
        @media (max-width: 559px) {
            .hp-casts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hp-casts-label { grid-column: 1 / -1; }
            .hp-casts button { padding: 0 0.5rem; font-size: 0.98rem; }
        }
        {{-- The show, introduced: its poster and its ticket, under the switch that re-casts them,
           so a press is answered right where it is made. From a laptop up the three stand beside
           the section's heading. The ticket is the one that is scanned at the door further down
           (same number), seen from its other side. The block is its own container, so the
           poster's type is set from the room the block has. --}}
        .hp-meet { display: grid; grid-template-columns: minmax(0, 1fr); row-gap: clamp(1.5rem, 2.6vw, 2.25rem); }
        @media (min-width: 96rem) {
            {{-- A wide window has the room for the show to stand larger. --}}
            .hp-meet { --hp-show: 41rem; }
        }
        .hp-meet > .hp-casts { margin-top: 0; }
        .hp-show { position: relative; container-type: inline-size; width: 100%; max-width: 34rem; min-width: 0; }
        @media (min-width: 640px) and (max-width: 1023px) {
            .hp-show { max-width: 42rem; }
        }
        .hp-show-in { display: grid; grid-template-columns: minmax(0, 0.86fr) minmax(0, 1fr); align-items: center; }
        .hp-show .hp-poster { z-index: 1; width: 100%; transform: rotate(-4deg); }
        .hp-show .hp-poster-top { font-size: clamp(0.44rem, 1.75cqi, 0.68rem); }
        #hp .hp-show .hp-poster-type small { overflow: visible; white-space: normal; }
        {{-- The ticket is paper in both modes, like the one in the night, so its colours are literal. --}}
        {{-- The tear is a bite cut out of both halves where they meet, so it falls right however
           tall the name makes the head; a cut would take a box shadow with it, so the shadow is
           the wrapper's. --}}
        .hp-stub { position: relative; margin-inline-start: -1.6rem; color: #0a1020; filter: drop-shadow(0 0 26px rgba(78, 129, 250, 0.34)) drop-shadow(0 22px 26px rgba(10, 16, 32, 0.26)); }
        .dark .hp-show .hp-poster { box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.14), 0 30px 70px -24px rgba(0, 0, 0, 0.95); }
        .dark .hp-stub { filter: drop-shadow(0 0 40px rgba(78, 129, 250, 0.6)) drop-shadow(0 24px 30px rgba(0, 0, 0, 0.8)); }
        .hp-stub-head { padding-block: clamp(0.9rem, 4cqi, 1.3rem) clamp(0.85rem, 3.6cqi, 1.2rem); padding-inline: calc(1.6rem + clamp(0.9rem, 4.2cqi, 1.5rem)) clamp(0.9rem, 4.2cqi, 1.5rem); border-radius: 1.2rem 1.2rem 0 0; background: linear-gradient(120deg, #2b5fe3, #0ea5e9 70%, #22d3ee); color: #fff; -webkit-mask-image: radial-gradient(circle 0.8rem at 0 100%, transparent 98%, #000), radial-gradient(circle 0.8rem at 100% 100%, transparent 98%, #000); mask-image: radial-gradient(circle 0.8rem at 0 100%, transparent 98%, #000), radial-gradient(circle 0.8rem at 100% 100%, transparent 98%, #000); -webkit-mask-composite: source-in; mask-composite: intersect; }
        .hp-stub-head small { display: block; font-family: var(--hp-mono); font-size: 0.6rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; opacity: 0.88; }
        .hp-stub-head strong { display: block; margin-top: 0.3rem; font-size: clamp(1.45rem, 7.6cqi, 2.6rem); font-weight: 700; font-variation-settings: 'wght' 860; letter-spacing: -0.035em; line-height: 1.02; overflow-wrap: anywhere; }
        .hp-stub-head > span { display: block; margin-top: 0.3rem; font-size: clamp(0.78rem, 2.7cqi, 0.92rem); font-weight: 700; font-variation-settings: 'wght' 600; line-height: 1.35; opacity: 0.94; }
        .hp-stub-body { position: relative; padding-block: 0.35rem clamp(0.8rem, 3.4cqi, 1.1rem); padding-inline: calc(1.6rem + clamp(0.9rem, 4.2cqi, 1.5rem)) clamp(0.9rem, 4.2cqi, 1.5rem); border-top: 2px dashed rgba(10, 16, 32, 0.22); border-radius: 0 0 1.2rem 1.2rem; background: #f8fafc; -webkit-mask-image: radial-gradient(circle 0.8rem at 0 0, transparent 98%, #000), radial-gradient(circle 0.8rem at 100% 0, transparent 98%, #000); mask-image: radial-gradient(circle 0.8rem at 0 0, transparent 98%, #000), radial-gradient(circle 0.8rem at 100% 0, transparent 98%, #000); -webkit-mask-composite: source-in; mask-composite: intersect; }
        .hp-stub-rows { display: grid; }
        .hp-stub-rows > span { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-block: clamp(0.42rem, 1.9cqi, 0.62rem); border-bottom: 1px solid rgba(10, 16, 32, 0.1); }
        .hp-stub-rows i { font-family: var(--hp-mono); font-style: normal; font-size: 0.64rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #56617c; }
        .hp-stub-rows b { min-width: 0; overflow: hidden; font-size: clamp(0.9rem, 3.3cqi, 1.08rem); font-weight: 700; letter-spacing: -0.01em; text-align: end; text-overflow: ellipsis; white-space: nowrap; }
        .hp-stub-foot { display: flex; justify-content: space-between; margin-top: clamp(0.6rem, 2.4cqi, 0.85rem); font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; letter-spacing: 0.06em; color: #56617c; }
        {{-- What the block is, said in a line a reader can read: a label of a few points did not carry it. --}}
        .hp-meet-note { max-width: 36rem; font-size: 1.02rem; line-height: 1.5; color: var(--hp-ink-2); text-wrap: balance; }
        @container (max-width: 25rem) {
            #hp .hp-show-in { grid-template-columns: minmax(0, 0.7fr) minmax(0, 1fr); }
            #hp .hp-show .hp-poster-top span:last-child { display: none; }
            #hp .hp-stub { margin-inline-start: -1.1rem; }
            #hp .hp-stub-head,
            #hp .hp-stub-body { padding-inline-start: 1.9rem; }
        }
        @media (min-width: 1024px) {
            .hp-meet { grid-template-columns: minmax(0, 1fr) minmax(0, min(var(--hp-show, 37rem), 55%)); grid-template-areas: "head casts" "head show" "head note"; align-items: start; column-gap: clamp(2.25rem, 4.6vw, 5rem); }
            .hp-meet > .hp-head { grid-area: head; align-self: center; }
            .hp-meet > .hp-casts { grid-area: casts; }
            .hp-meet > .hp-show { grid-area: show; max-width: none; }
            .hp-meet > .hp-meet-note { grid-area: note; }
            .hp-meet .hp-casts button { min-height: 2.9rem; padding: 0 1.05rem; font-size: 1rem; }
        }
        @media (min-width: 1024px) and (max-width: 1359px) {
            {{-- Four names do not fit one line beside the heading here: two and two, never three and one. --}}
            .hp-meet .hp-casts { display: grid; grid-template-columns: repeat(2, minmax(0, max-content)); }
            .hp-meet .hp-casts-label { grid-column: 1 / -1; }
        }
        {{-- A press of the switch is answered where it is pressed: the poster is pasted up again. --}}
        html.es-anim .hp-show.is-swap .hp-poster { animation: hp-swap 0.55s cubic-bezier(0.22, 1, 0.36, 1) both; }
        html.es-anim .hp-show.is-swap .hp-stub { animation: hp-swap-stub 0.45s ease-out both; }
        @keyframes hp-swap { from { opacity: 0; transform: rotate(5deg) scale(0.86) translateY(14px); } to { opacity: 1; transform: rotate(-4deg); } }
        @keyframes hp-swap-stub { from { opacity: 0.35; transform: translateX(-0.6rem); } to { opacity: 1; transform: none; } }
        {{-- A cast with no photograph of its own gets a poster that is drawn: a low moon over water. --}}
        .hp-poster.is-plain {
            background:
                radial-gradient(circle at 70% 27%, #fff7d6 0, #fde68a 11.5%, rgba(253, 230, 138, 0.3) 12.5%, rgba(253, 230, 138, 0) 36%),
                radial-gradient(130% 55% at 50% 112%, rgba(34, 211, 238, 0.6), transparent 62%),
                linear-gradient(165deg, #052536 0%, #0a4a66 52%, #0e7490 100%);
        }
        .hp-poster.is-plain img { display: none; }
        .hp-poster.is-plain::after { background: linear-gradient(to top, rgba(4, 30, 44, 0.78) 6%, rgba(4, 30, 44, 0) 58%); }
        .hp-slots-type .hp-slots-meta { display: block; font-size: 0.8rem; font-weight: 400; color: var(--hp-ink-3); }
        #hp #features { --hp-tile: 6.25rem; padding-bottom: 0; }
        @media (min-width: 102rem) {
            #hp #features { --hp-tile: 7.75rem; }
        }

        {{-- An act opens on the card the film cuts to: which act of three, three bars, and the
           verb. On the page the card is a band the width of the window with a ground of its own,
           so the page cuts to it as the film does. --}}
        .hp-verb { scroll-margin-top: 4.5rem; }
        .hp-vband {
            position: relative;
            isolation: isolate;
            margin-top: clamp(1.5rem, 4vw, 3.5rem);
            border-block: 1px solid var(--hp-line-2);
            box-shadow: 0 1.25rem 2rem -1.75rem rgba(10, 16, 32, 0.28);
            background: var(--hp-bg-2);
            overflow: hidden;
            overflow: clip;
        }
        .hp-wrap + .hp-verb > .hp-vband { margin-top: clamp(3rem, 6vw, 5rem); }
        {{-- One light, over the far end of the column the verb reads along, at any width. --}}
        .hp-vband::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: radial-gradient(48rem 26rem at calc(50% + min(30rem, 38vw)) -10%, var(--hp-glow), transparent 70%);
            pointer-events: none;
        }
        [dir="rtl"] #hp .hp-vband::before { transform: scaleX(-1); }
        {{-- The card is its own container: the bars span it, and the verb is sized from the room
           it has, so "Promote." fits a phone on one line and never meets the list beside it. --}}
        .hp-vcard {
            position: relative;
            container-type: inline-size;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            grid-template-rows: auto auto minmax(1.5rem, 1fr) auto;
            grid-template-areas: "ix" "bars" "." "word";
            min-height: clamp(13rem, 30vh, 17rem);
            min-height: clamp(13rem, 30svh, 17rem);
            padding-block: clamp(2rem, 4.5vw, 3.5rem) clamp(1.4rem, 3.4vw, 2.6rem);
        }
        {{-- The wall's seven columns, drawn on the card's own width (so none of them stands just
           past the end of the bars) and fading toward the word. There is no line on the card's
           far edge: on a phone that was a hard rule down the side of the screen. --}}
        .hp-vcard::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background: linear-gradient(90deg, var(--hp-line) 1px, transparent 1px) 0 0 / calc(100% / 7) 100%;
            -webkit-mask-image: linear-gradient(to left, #000, transparent 74%);
            mask-image: linear-gradient(to left, #000, transparent 74%);
            pointer-events: none;
        }
        [dir="rtl"] #hp .hp-vcard::before { transform: scaleX(-1); }
        .hp-vcard > * { --hp-verb-size: 23cqi; --hp-has-room: 17rem; }
        .hp-vcard-ix {
            grid-area: ix;
            font-family: var(--hp-mono);
            font-size: 0.82rem;
            font-weight: 700; font-variation-settings: 'wght' 600;
            letter-spacing: 0.16em;
            color: var(--hp-ink-3);
        }
        .hp-vbars { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: clamp(0.4rem, 1cqi, 0.9rem); }
        .hp-vcard > .hp-vbars { grid-area: bars; margin-top: 0.9rem; }
        .hp-vbars i { position: relative; height: 0.36rem; border-radius: 999px; background: var(--hp-line-2); overflow: hidden; }
        .hp-vbars i.is-done::after,
        .hp-vbars i.is-now::after { content: ""; position: absolute; inset: 0; border-radius: inherit; }
        .hp-vbars i.is-done { background: transparent; }
        .hp-vbars i.is-done::after { background: var(--hp-blue); opacity: 0.5; }
        .hp-vbars i.is-now::after { background: linear-gradient(90deg, #4e81fa, #22d3ee); }
        .hp-vcard-word {
            grid-area: word;
            {{-- A capital P stands in from its own edge and an S nearly on it: each is pulled to
               the line the bars start on. --}}
            margin-inline-start: -0.058em;
            font-weight: 700;
            font-variation-settings: 'wght' 880;
            font-size: clamp(4rem, 19vw, 9rem);
            line-height: 0.92;
            letter-spacing: -0.055em;
        }
        @supports (width: 1cqi) {
            .hp-vcard-word { font-size: clamp(4rem, var(--hp-verb-size), 13.75rem); }
        }
        .hp-verb:last-child .hp-vcard-word { margin-inline-start: -0.008em; }
        .hp-vcard-word .hp-ink-grad { display: inline-block; padding-block-end: 0.05em; }
        {{-- What the act holds, as its running order and two ways down the page. A phone has the
           first of them right under the card, so it shows from a tablet up. --}}
        .hp-vcard-has { display: none; }
        .hp-vcard-has a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 2.5rem;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: -0.015em;
            white-space: nowrap;
            color: var(--hp-ink);
            transition: color 0.2s ease;
        }
        .hp-vcard-has a:hover { color: var(--hp-blue); }
        .hp-vcard-has svg { width: 1.1rem; height: 1.1rem; color: var(--hp-blue); transform: rotate(90deg); transition: transform 0.2s ease; }
        .hp-vcard-has a:hover svg { transform: rotate(90deg) translateX(3px); }
        @media (min-width: 768px) {
            .hp-vcard {
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas: "ix ix" "bars bars" ". ." "word has";
                column-gap: 2.5rem;
                {{-- Tall as a card on a wide window, never taller than a third of its width: a
                   tablet held upright would otherwise get a band that is mostly air. --}}
                min-height: clamp(17rem, min(44vh, 34vw), 30rem);
                min-height: clamp(17rem, min(44svh, 34vw), 30rem);
            }
            {{-- The longest verb is 4.2 times its own size wide; the list is given its room first. --}}
            .hp-vcard > * { --hp-verb-size: calc((100cqi - var(--hp-has-room)) / 4.2); }
            .hp-vcard-has { grid-area: has; align-self: end; display: grid; justify-items: end; padding-bottom: calc(clamp(4rem, var(--hp-verb-size), 13.75rem) * 0.14 - 0.65rem); }
        }
        @media (min-width: 1280px) {
            .hp-vcard > * { --hp-has-room: 19.5rem; }
            .hp-vcard-has a { font-size: 1.4rem; }
        }
        @media (min-width: 1024px) {
            .hp-vrow { display: grid; grid-template-columns: var(--hp-tile) minmax(0, 1fr); column-gap: clamp(1.75rem, 3.4vw, 4rem); }
            .hp-vrow > .hp-vcard { grid-column: 2; }
        }
        {{-- The card arrives once: its bars draw to the act it has reached, and the verb rises. --}}
        html.es-anim #hp .hp-vcard[data-reveal] { opacity: 1; transform: none; }
        html.es-anim .hp-vcard[data-reveal] .hp-vbars i::after { transform: scaleX(0); transform-origin: 0 50%; transition: transform 0.7s cubic-bezier(0.2, 0.8, 0.2, 1); }
        html.es-anim[dir="rtl"] .hp-vcard .hp-vbars i::after { transform-origin: 100% 50%; }
        html.es-anim .hp-vcard .hp-vbars i:nth-child(1)::after { transition-delay: 0.15s; }
        html.es-anim .hp-vcard .hp-vbars i:nth-child(2)::after { transition-delay: 0.4s; }
        html.es-anim .hp-vcard .hp-vbars i:nth-child(3)::after { transition-delay: 0.65s; }
        html.es-anim .hp-vcard[data-reveal].is-revealed .hp-vbars i::after { transform: scaleX(1); }
        html.es-anim .hp-vcard .hp-vcard-word .hp-ink-grad,
        html.es-anim .hp-vcard .hp-vcard-has { transition: opacity 0.7s ease, transform 0.9s cubic-bezier(0.2, 0.8, 0.2, 1); }
        html.es-anim .hp-vcard[data-reveal]:not(.is-revealed) .hp-vcard-word .hp-ink-grad { opacity: 0; transform: translateY(0.3em); }
        html.es-anim .hp-vcard[data-reveal]:not(.is-revealed) .hp-vcard-has { opacity: 0; transform: translateY(0.8rem); }
        html.es-anim .hp-vcard .hp-vcard-has { transition-delay: 0.35s; }

        {{-- What an act holds: its tile in the margin, pinned for as long as the act is on
           screen, and its features one under the other beside it. --}}
        .hp-run { position: relative; }
        .hp-beat {
            position: relative;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1.75rem;
            padding-block: clamp(2.25rem, 5vw, 4.5rem);
            scroll-margin-top: 5rem;
        }
        .hp-beat + .hp-beat { border-top: 1px solid var(--hp-line); }
        @media (min-width: 1024px) {
            .hp-run {
                display: grid;
                grid-template-columns: var(--hp-tile) minmax(0, 1fr);
                column-gap: clamp(1.75rem, 3.4vw, 4rem);
                align-items: start;
            }
            .hp-beat {
                grid-template-columns: minmax(0, 0.82fr) minmax(0, 1fr);
                column-gap: clamp(1.75rem, 3.4vw, 4rem);
                align-items: start;
            }
            .hp-beat + .hp-beat { border-top: 0; }
            {{-- The thread an act's features hang on. It comes down out of the card. --}}
            .hp-run::before {
                content: "";
                position: absolute;
                top: 0;
                bottom: 3rem;
                inset-inline-start: calc(var(--hp-tile) / 2);
                width: 2px;
                margin-inline-start: -1px;
                background: var(--hp-line-2);
            }
            .hp-step,
            .hp-objwrap { position: sticky; top: 6.25rem; }
            .hp-run > .hp-step { margin-top: clamp(2.25rem, 5vw, 4.5rem); }
        }
        @media (min-width: 1280px) {
            .hp-beat { grid-template-columns: minmax(0, 25rem) minmax(0, 1fr); }
        }
        @supports (animation-timeline: view()) {
            @media (min-width: 1024px) {
                html.es-anim .hp-run::after {
                    content: "";
                    position: absolute;
                    top: 0;
                    bottom: 3rem;
                    inset-inline-start: calc(var(--hp-tile) / 2);
                    width: 2px;
                    margin-inline-start: -1px;
                    background: linear-gradient(#4e81fa, #0ea5e9 60%, #22d3ee);
                    transform-origin: top;
                    animation: hp-thread linear both;
                    animation-timeline: view();
                    animation-range: entry 40% exit 60%;
                }
            }
        }
        @keyframes hp-thread { from { transform: scaleY(0); } to { transform: scaleY(1); } }
        @media (min-width: 1024px) {
            {{-- Sell is two pieces of markup, the evening and the night's band. The evening's
               thread runs on to the band and the band's own starts above it, so the act hangs on
               one line. --}}
            #hp .hp-eve .hp-run::before,
            #hp .hp-eve .hp-run::after { bottom: -4rem; }
            #hp .hp-night .hp-run::before,
            #hp .hp-night .hp-run::after { top: -12rem; }
        }

        {{-- The tile: the card again, small enough to ride the margin. It keeps the blue cap of the
           calendar page it replaces, with the three bars in it; a count in that cap ("01 / 03")
           read as a date. --}}
        .hp-step {
            z-index: 2;
            display: flex;
            flex-direction: column;
            width: var(--hp-tile);
            overflow: hidden;
            border: 1px solid var(--hp-line-2);
            border-radius: 1.1rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            text-align: center;
        }
        .hp-step .hp-vbars { gap: 0.22rem; padding: 0.62rem 0.7rem; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); }
        .hp-step .hp-vbars i { height: 0.28rem; background: rgba(255, 255, 255, 0.34); }
        .hp-step .hp-vbars i.is-done::after { background: #fff; opacity: 0.6; }
        .hp-step .hp-vbars i.is-now::after { background: #fff; }
        .hp-step > i {
            padding-top: 0.75rem;
            font-style: normal;
            font-size: 1.2rem;
            font-weight: 700;
            font-variation-settings: 'wght' 860;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .hp-step b {
            padding: 0.45rem 0 0.7rem;
            font-family: var(--hp-mono);
            font-size: 0.7rem;
            font-weight: 700; font-variation-settings: 'wght' 600;
            letter-spacing: 0.14em;
            line-height: 1;
            color: var(--hp-ink-2);
        }
        @media (min-width: 102rem) {
            .hp-step { border-radius: 1.25rem; }
            .hp-step .hp-vbars { gap: 0.28rem; padding: 0.75rem 0.85rem; }
            .hp-step .hp-vbars i { height: 0.34rem; }
            .hp-step > i { padding-top: 0.9rem; font-size: 1.5rem; }
            .hp-step b { padding: 0.5rem 0 0.85rem; font-size: 0.76rem; }
        }
        {{-- While a card is still on screen it says all of this itself, so the tile under it comes
           in as the card leaves, and the night's tile as Sell's first piece (with a tile of its
           own) leaves. Where motion is allowed it fades and settles; otherwise it is simply not
           there and then there. Where a browser cannot tie the two, the tile is always there.
           The change is over by the time the card is 60% gone: a card's own link stops with the
           card's last 5rem still under the bar, and the tile has to have arrived by then. --}}
        @supports (animation-timeline: view()) and (timeline-scope: --hp-card) {
            @media (min-width: 1024px) {
                .hp-verb { timeline-scope: --hp-card, --hp-piece; }
                .hp-vband { view-timeline: --hp-card block; }
                .hp-eve { view-timeline: --hp-piece block; }
                .hp-vband + .hp-wrap > .hp-run > .hp-step { animation: hp-step-on steps(1, end) both; animation-timeline: --hp-card; animation-range: exit 10% exit 60%; }
                .hp-vband + .hp-eve .hp-run > .hp-step { animation: hp-step-on steps(1, end) both; animation-timeline: --hp-card; animation-range: exit 10% exit 60%; }
                .hp-night .hp-run > .hp-step { animation: hp-step-on steps(1, end) both; animation-timeline: --hp-piece; animation-range: exit 92% exit 100%; }
                html.es-anim .hp-vband + .hp-wrap > .hp-run > .hp-step,
                html.es-anim .hp-vband + .hp-eve .hp-run > .hp-step,
                html.es-anim .hp-night .hp-run > .hp-step { animation-name: hp-step-in; animation-timing-function: linear; }
            }
        }
        @keyframes hp-step-on { from { opacity: 0; } to { opacity: 1; } }
        @keyframes hp-step-in { from { opacity: 0; transform: translateY(0.75rem) scale(0.92); } to { opacity: 1; transform: none; } }
        {{-- Below a laptop there is no margin to ride. The card stands right above an act's first
           feature; the second says which act it belongs to in front of its own name. --}}
        .hp-kicker-act { display: none; }
        @media (max-width: 1023px) {
            .hp-beat { row-gap: 0; }
            .hp-beat-copy { display: contents; }
            .hp-beat-copy > * { order: 3; }
            .hp-beat-copy > .hp-h3,
            .hp-beat-copy > .hp-h3 + p { order: 1; }
            .hp-beat > .hp-objwrap { order: 2; margin-block: 1.75rem 0.25rem; }
            .hp-run > .hp-step { display: none; }
            {{-- No fill: a solid chip that says PLAN reads as a price plan on a page that sells
               some. Three bars and the verb are the card's own marks. --}}
            #hp .hp-kicker-act {
                display: inline;
                margin-inline-end: 0.3rem;
                font-family: var(--hp-mono);
                font-size: 0.72rem;
                font-variation-settings: 'wght' 600;
                letter-spacing: 0.12em;
                text-transform: uppercase;
                color: var(--hp-ink-2);
            }
            #hp .hp-kicker-act::after { content: "/"; margin-inline-start: 0.3rem; color: var(--hp-ink-3); }
            #hp .hp-kicker-act .hp-vbars { display: inline-grid; width: 2rem; gap: 0.14rem; margin-inline-end: 0.4rem; vertical-align: 0.14em; }
            #hp .hp-kicker-act .hp-vbars i { height: 0.22rem; }
            #hp .hp-kicker-act .hp-vbars i:not([class]) { background: rgba(10, 16, 32, 0.3); }
            .dark #hp .hp-kicker-act .hp-vbars i:not([class]) { background: rgba(255, 255, 255, 0.32); }
            #hp .hp-night .hp-kicker-act { color: #c5cde2; }
            #hp .hp-night .hp-kicker-act::after { color: #9fb1d6; }
            #hp .hp-night .hp-kicker-act .hp-vbars i:not([class]) { background: rgba(159, 177, 214, 0.45); }
            #hp .hp-night .hp-kicker-act .hp-vbars i.is-done { background: transparent; }
            #hp .hp-night .hp-kicker-act .hp-vbars i.is-done::after { background: #8db0ff; opacity: 0.65; }
        }

        .hp-beat-copy .hp-kicker { display: block; margin-bottom: 0.8rem; font-family: var(--hp-display); font-size: 0.92rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.01em; line-height: 1.4; text-transform: none; color: var(--hp-blue); }
        .hp-beat-copy .hp-kicker::before { display: none; }
        .hp-night .hp-beat-copy .hp-kicker { color: #8db0ff; }
        .hp-btn.is-small { min-height: 3rem; padding-inline: 1.25rem; font-size: 0.98rem; border-radius: 0.85rem; }
        .hp-beat-copy .hp-actions { margin-top: 1.5rem; gap: 0.75rem 1.25rem; }
        .hp-beat-copy .hp-h3 { font-size: clamp(1.9rem, 1.9vw + 1.05rem, 2.9rem); }
        .hp-beat-copy .hp-h3 .hp-ink-grad,
        .hp-beat-copy .hp-h3 .hp-soft { display: block; }
        .hp-beat-copy > p { margin-top: 1rem; font-size: 1.125rem; color: var(--hp-ink-2); text-wrap: pretty; }
        .hp-blurbs { display: grid; gap: 1.5rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--hp-line); }
        .hp-blurb { display: grid; grid-template-columns: 2.75rem minmax(0, 1fr); gap: 1rem; }
        .hp-blurb h5 { font-size: 1.125rem; font-weight: 700; letter-spacing: -0.02em; line-height: 1.3; }
        .hp-blurb p { margin-top: 0.3rem; font-size: 0.98rem; line-height: 1.55; color: var(--hp-ink-2); }
        .hp-blurb .hp-more { margin-top: 0.45rem; font-size: 0.95rem; }
        .hp-fine { margin-top: 0.4rem; font-size: 0.84rem; color: var(--hp-ink-3); }
        .hp-paid {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin-top: 1.5rem;
            font-size: 0.92rem;
            color: var(--hp-ink-3);
        }
        .hp-paid svg { flex: none; width: 1.1rem; height: 1.1rem; margin-top: 0.2rem; }

        {{-- The stage each feature's object stands on. Its wrapper is a container, so what stands on the
           stage is laid out from the room the stage has, not from the window. --}}
        .hp-objwrap { container-type: inline-size; min-width: 0; }
        .hp-obj {
            position: relative;
            min-height: clamp(22rem, 36vw, 34rem);
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            background:
                radial-gradient(34rem 22rem at 78% 0%, var(--hp-glow), transparent 70%),
                var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            overflow: hidden;
        }
        .hp-obj::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(var(--hp-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--hp-line) 1px, transparent 1px);
            background-size: calc(100% / 7) 5.5rem;
            -webkit-mask-image: linear-gradient(160deg, #000, transparent 62%);
            mask-image: linear-gradient(160deg, #000, transparent 62%);
            opacity: 0.8;
            pointer-events: none;
        }
        {{-- The light of the day the acts are told across. Plan is the morning: paper, with the
           first light at one corner. Promote is the middle of the day: the stage is the sky
           itself, and what stands on it is white. Sell is the evening, further down. --}}
        #plan .hp-obj {
            background:
                radial-gradient(80% 78% at 10% 116%, rgba(34, 211, 238, 0.5), transparent 70%),
                radial-gradient(64% 58% at 94% 112%, rgba(78, 129, 250, 0.28), transparent 70%),
                linear-gradient(to bottom, #ffffff 0%, #f1faff 50%, #dff4fc 100%);
        }
        {{-- Low light, long shadows. --}}
        #plan .hp-obj .hp-poster,
        #plan .hp-obj .hp-card { box-shadow: 0 2px 4px rgba(10, 16, 32, 0.05), 0 48px 60px -32px rgba(12, 74, 110, 0.55); }
        #promote { --hp-soft: var(--hp-blue); }
        #promote .hp-obj {
            border-color: transparent;
            background:
                radial-gradient(38rem 24rem at 88% -12%, rgba(165, 243, 252, 0.55), transparent 68%),
                linear-gradient(155deg, #2456dc 0%, #1f78e4 52%, #0e9fe0 100%);
            box-shadow: 0 2px 4px rgba(10, 16, 32, 0.06), 0 34px 70px -34px rgba(36, 86, 220, 0.75);
        }
        #promote .hp-obj::before { background-image: linear-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.16) 1px, transparent 1px); opacity: 1; }
        .dark #plan .hp-obj {
            background:
                radial-gradient(70% 62% at 10% 114%, rgba(34, 211, 238, 0.44), transparent 70%),
                radial-gradient(60% 50% at 94% 110%, rgba(78, 129, 250, 0.22), transparent 70%),
                var(--hp-bg-2);
        }
        .dark #plan .hp-obj .hp-poster,
        .dark #plan .hp-obj .hp-card { box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.07), 0 40px 60px -30px rgba(0, 0, 0, 0.95); }
        .dark #promote .hp-obj {
            border-color: rgba(125, 165, 255, 0.28);
            background:
                radial-gradient(38rem 24rem at 88% -12%, rgba(56, 189, 248, 0.42), transparent 68%),
                linear-gradient(155deg, #0f3f8f 0%, #0b63a8 55%, #0a86b8 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08), 0 34px 70px -34px rgba(0, 0, 0, 0.9);
        }
        .hp-card {
            position: relative;
            border: 1px solid var(--hp-line);
            border-radius: 1.15rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-pop-shadow);
        }
        .dark .hp-card { background: #131a2e; }
        .hp-tile {
            display: inline-flex;
            flex: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3.15rem;
            border-radius: 0.7rem;
            background: rgba(78, 129, 250, 0.12);
            line-height: 1;
        }
        .hp-tile b { font-size: 0.6rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-blue); }
        .hp-tile i { margin-top: 0.15rem; font-style: normal; font-size: 1.2rem; font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.03em; }
        .hp-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .hp-pill-green { background: #dcfce7; color: #166534; }
        .hp-pill-blue { background: #dbeafe; color: #1e40af; }
        .dark .hp-pill-green { background: rgba(34, 197, 94, 0.18); color: #86efac; }
        .dark .hp-pill-blue { background: rgba(78, 129, 250, 0.22); color: #bfd3ff; }
        .hp-chip {
            position: absolute;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.9rem;
            border: 1px solid var(--hp-line);
            border-radius: 0.9rem;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-pop-shadow);
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .dark .hp-chip { background: #131a2e; }
        .hp-chip svg { width: 1rem; height: 1rem; }
        .hp-ok {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 1.4rem;
            height: 1.4rem;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
        }
        .dark .hp-ok { background: rgba(34, 197, 94, 0.2); color: #86efac; }
        .hp-ok svg { width: 0.8rem; height: 0.8rem; }

        {{-- Plan: the poster, read into an event --}}
        .hp-obj-ai {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            justify-items: center;
            align-items: center;
            gap: clamp(0.75rem, 2vw, 1.75rem);
            padding: clamp(1.25rem, 2.8vw, 2.6rem);
        }
        .hp-obj-ai .hp-poster { width: min(64%, 15rem); }
        .hp-obj-ai .hp-flow svg { transform: rotate(90deg); }
        .hp-obj-ai .hp-event { width: 100%; }
        @container (min-width: 33rem) {
            #hp .hp-obj-ai { grid-template-columns: minmax(0, 0.9fr) auto minmax(0, 1.1fr); column-gap: clamp(0.6rem, 2.2cqi, 1.5rem); justify-items: stretch; }
            #hp .hp-obj-ai .hp-synced { gap: 0.45rem; font-size: 0.76rem; white-space: nowrap; }
            #hp .hp-obj-ai .hp-event-top span { white-space: nowrap; }
            #hp .hp-obj-ai .hp-poster,
            #hp .hp-obj-ai .hp-event { min-width: 0; }
            #hp .hp-obj-ai .hp-poster { width: auto; }
            #hp .hp-obj-ai .hp-flow svg { transform: none; }
        }
        @container (min-width: 43rem) {
            #hp .hp-obj-ai .hp-field { padding: 0.75rem 0.85rem; font-size: 0.95rem; }
            #hp .hp-obj-ai .hp-event-top strong { font-size: 1.25rem; }
        }
        .hp-poster {
            position: relative;
            aspect-ratio: 3 / 4.1;
            overflow: hidden;
            border-radius: 0.9rem;
            background: #1a0f0a;
            box-shadow: var(--hp-pop-shadow);
            color: #fff5e6;
            transform: rotate(-3deg);
        }
        .hp-poster img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .hp-poster::after { content: ""; position: absolute; inset: 0; background: linear-gradient(to top, rgba(18, 8, 4, 0.94) 6%, rgba(18, 8, 4, 0.78) 34%, rgba(18, 8, 4, 0.3) 62%, rgba(18, 8, 4, 0.6)); }
        .hp-poster-type { position: absolute; inset: auto 0 0 0; z-index: 1; padding: 0 9% 9%; }
        .hp-poster-type small { display: block; font-family: var(--hp-mono); font-size: clamp(0.5rem, 1.5cqi, 0.68rem); font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #ffd28a; text-shadow: 0 1px 8px rgba(0, 0, 0, 0.7); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .hp-poster-type strong { display: block; margin-top: 0.3em; font-size: clamp(1.4rem, 5.6cqi, 2.9rem); font-weight: 700; font-variation-settings: 'wght' 880; letter-spacing: -0.05em; line-height: 0.92; text-transform: uppercase; }
        .hp-poster-type > span { display: block; margin-top: 0.7em; font-size: clamp(0.62rem, 1.75cqi, 0.82rem); font-weight: 700; line-height: 1.4; }
        .hp-poster-top { position: absolute; inset: 7% 9% auto 9%; z-index: 1; display: flex; justify-content: space-between; font-family: var(--hp-mono); font-size: clamp(0.5rem, 0.8vw, 0.68rem); font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; }
        .hp-scan {
            position: absolute;
            inset: 0 0 auto 0;
            z-index: 2;
            height: 34%;
            background: linear-gradient(to bottom, transparent, rgba(34, 211, 238, 0.22) 85%, rgba(34, 211, 238, 0.95) 100%);
            border-bottom: 2px solid #67e8f9;
            box-shadow: 0 6px 22px rgba(34, 211, 238, 0.7);
            transform: translateY(-100%);
        }
        html.es-anim [data-reveal].is-revealed .hp-scan { animation: hp-scan 3.4s cubic-bezier(0.45, 0, 0.3, 1) 0.3s 2 both; }
        @keyframes hp-scan {
            0% { transform: translateY(-100%); opacity: 1; }
            80% { transform: translateY(300%); opacity: 1; }
            100% { transform: translateY(300%); opacity: 0; }
        }
        {{-- A line of the poster that has been read: ringed, with its number at the corner. The
           ring is drawn outside the line's own box, so the poster's type does not move. --}}
        .hp-read { position: relative; z-index: 3; }
        #hp .hp-poster .hp-read { width: fit-content; max-width: 100%; overflow: visible; overflow-wrap: anywhere; white-space: normal; }
        [dir="rtl"] #hp .hp-read::after { transform: translate(-50%, -50%); }
        .hp-read::before { content: ""; position: absolute; inset: -0.32em -0.5em; border: 1.5px solid #67e8f9; border-radius: 0.5em; background: rgba(34, 211, 238, 0.14); box-shadow: 0 0 14px rgba(34, 211, 238, 0.45); pointer-events: none; }
        .hp-read::after { content: attr(data-n); position: absolute; top: -0.32em; inset-inline-end: -0.5em; display: grid; place-items: center; width: 1.25rem; height: 1.25rem; border-radius: 999px; background: #67e8f9; font-family: var(--hp-display); font-size: 0.7rem; font-weight: 700; letter-spacing: 0; line-height: 1; color: #04101f; transform: translate(50%, -50%); pointer-events: none; }
        .hp-read-n { display: grid; flex: none; place-items: center; width: 1.25rem; height: 1.25rem; margin-inline-start: auto; border-radius: 999px; background: var(--hp-blue); font-size: 0.7rem; font-weight: 700; line-height: 1; color: var(--hp-bg-2); }
        html.es-anim [data-reveal] .hp-read::before,
        html.es-anim [data-reveal] .hp-read::after { opacity: 0; transition: opacity 0.45s ease; transition-delay: calc(var(--i, 0) * 0.18s + 0.4s); }
        html.es-anim [data-reveal].is-revealed .hp-read::before,
        html.es-anim [data-reveal].is-revealed .hp-read::after { opacity: 1; }
        .hp-flow { display: flex; flex-direction: column; gap: 0.35rem; color: var(--hp-blue); }
        .hp-flow svg { width: 1.75rem; height: 1.75rem; }
        .hp-event { padding: clamp(0.9rem, 1.6vw, 1.35rem); }
        .hp-event-top { display: flex; align-items: center; gap: 0.8rem; }
        .hp-event-top strong { display: block; font-size: 1.1rem; letter-spacing: -0.02em; line-height: 1.2; }
        .hp-event-top span { font-size: 0.82rem; color: var(--hp-ink-3); }
        .hp-fields { display: grid; gap: 0.5rem; margin-top: 1rem; }
        .hp-field > span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .hp-field {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 0.75rem;
            border: 1px solid rgba(78, 129, 250, 0.3);
            border-radius: 0.7rem;
            background: rgba(78, 129, 250, 0.08);
            font-size: 0.88rem;
            font-weight: 700; font-variation-settings: 'wght' 600;
            white-space: nowrap;
        }
        .hp-field svg { flex: none; width: 1rem; height: 1rem; color: var(--hp-blue); }
        .hp-synced {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            margin-top: 0.9rem;
            padding-top: 0.9rem;
            border-top: 1px dashed var(--hp-line-2);
            font-size: 0.8rem;
            font-weight: 700; font-variation-settings: 'wght' 600;
            color: var(--hp-ink-3);
        }
        .hp-synced svg { width: 1.1rem; height: 1.1rem; }
        .hp-synced i { margin-inline-start: auto; width: 0.5rem; height: 0.5rem; border-radius: 999px; background: #22d3ee; box-shadow: 0 0 0 0 rgba(34, 211, 238, 0.7); animation: hp-pulse 2.4s ease-out infinite; }
        @keyframes hp-pulse { 70%, 100% { box-shadow: 0 0 0 0.6rem rgba(34, 211, 238, 0); } }

        {{-- Promote: one schedule in three places, each named on the thing itself. The calendar
           embedded in a site of their own, the page behind the link in a bio, and the poster that
           is printed with its code. One composition at every width: the browser at the back, the
           phone and the poster in front of it, in proportions that follow the stage's own room. --}}
        .hp-obj-share { display: flex; flex-direction: column; min-height: clamp(22rem, 31vw, 29.5rem); padding: clamp(3.6rem, 12cqi, 5rem) clamp(1rem, 3vw, 2.5rem) 0; }
        .hp-out { position: relative; }
        .hp-out > .hp-chip { z-index: 4; bottom: calc(100% + 0.55rem); inset-inline-start: 0; }
        .hp-out.is-site { width: 100%; margin-top: auto; }
        .hp-out.is-phone { position: absolute; z-index: 2; inset-inline-end: 4%; bottom: 0; width: 44%; }
        .hp-out.is-qr { position: absolute; z-index: 3; inset-inline-start: 4%; bottom: 6%; width: 32%; }
        .hp-site-nav { display: flex; align-items: center; gap: clamp(0.6rem, 2.4cqi, 1.25rem); padding: 0.7rem clamp(1rem, 2vw, 1.5rem); border-bottom: 1px solid var(--hp-line); font-size: 0.78rem; white-space: nowrap; color: var(--hp-ink-3); }
        .hp-site-nav b { flex: 0 1 auto; min-width: 0; margin-inline-end: auto; overflow: hidden; font-size: 0.98rem; font-variation-settings: 'wght' 840; letter-spacing: -0.02em; text-overflow: ellipsis; color: var(--hp-ink); }
        .hp-site-nav .is-on { font-weight: 700; color: var(--hp-blue); }
        .hp-site-nav span { flex: none; }
        {{-- The name is never the thing that gives way: the site's other pages are. --}}
        @container (max-width: 47rem) {
            #hp .hp-site-nav span:not(.is-on) { display: none; }
        }
        .hp-phone { padding: 0.4rem 0.4rem 0; border-radius: 1.75rem 1.75rem 0 0; background: #0a1020; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14), 0 34px 60px -22px rgba(4, 16, 31, 0.75); }
        .hp-phone-screen { display: flex; flex-direction: column; gap: 0.5rem; aspect-ratio: 9 / 17; padding: 0.8rem 0.65rem 1.9rem; border-radius: 1.4rem 1.4rem 0 0; background: var(--hp-bg-2); color: var(--hp-ink); }
        .dark .hp-phone-screen { background: #131a2e; }
        .hp-phone-bar { align-self: center; max-width: 100%; margin-bottom: 0.2rem; overflow: hidden; padding: 0.18rem 0.6rem; border-radius: 999px; background: var(--hp-bg); font-family: var(--hp-mono); font-size: 0.5rem; text-overflow: ellipsis; white-space: nowrap; color: var(--hp-ink-2); }
        .dark .hp-phone-bar { background: rgba(255, 255, 255, 0.07); }
        .hp-phone-who { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: center; gap: 0.3rem 0.5rem; padding-bottom: 0.3rem; }
        .hp-phone-who .hp-avatar { width: 2rem; height: 2rem; border-radius: 0.65rem; font-size: 0.9rem; }
        .hp-phone-who b { overflow: hidden; font-size: 0.78rem; letter-spacing: -0.01em; line-height: 1.2; text-overflow: ellipsis; white-space: nowrap; }
        .hp-phone-who .hp-follow { grid-column: 1 / -1; padding: 0.3rem; font-size: 0.66rem !important; text-align: center; }
        .hp-phone-row { display: flex; align-items: center; gap: 0.5rem; min-width: 0; padding: 0.4rem; border: 1px solid var(--hp-line); border-radius: 0.7rem; }
        .hp-phone-row .hp-tile { width: 2.1rem; height: 2.2rem; border-radius: 0.5rem; }
        .hp-phone-row .hp-tile b { font-size: 0.46rem; }
        .hp-phone-row .hp-tile i { font-size: 0.86rem; }
        .hp-phone-row > span:last-child { min-width: 0; }
        .hp-phone-row > span:last-child b { display: block; overflow: hidden; font-size: 0.72rem; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
        .hp-phone-row > span:last-child i { display: block; font-style: normal; font-size: 0.62rem; color: var(--hp-ink-3); }
        .hp-phone-cta { margin-top: auto; padding: 0.5rem; border-radius: 0.65rem; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); font-size: 0.7rem; font-weight: 700; text-align: center; color: #fff; }
        {{-- The printed poster is paper in both modes, so its colours are literal. --}}
        .hp-qrp { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 0.9rem 0.7rem 0.75rem; border-radius: 0.4rem; background: #fdfcf8; color: #0a1020; box-shadow: 0 26px 50px -18px rgba(4, 16, 31, 0.7); text-align: center; transform: rotate(-7deg); }
        .hp-qrp-top { max-width: 100%; overflow: hidden; font-family: var(--hp-mono); font-size: 0.5rem; font-weight: 700; letter-spacing: 0.16em; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; color: #56617c; }
        .hp-qrp strong { font-size: clamp(0.72rem, 2.7cqi, 1.3rem); font-weight: 700; font-variation-settings: 'wght' 880; letter-spacing: -0.04em; line-height: 0.95; text-transform: uppercase; }
        .hp-qrp svg { width: 76%; height: auto; }
        {{-- The address breaks before its dot or not at all, never at a hyphen inside the name. --}}
        .hp-qrp-url { display: none; max-width: 100%; overflow: hidden; font-family: var(--hp-mono); font-size: 0.5rem; font-weight: 700; line-height: 1.35; color: #36405a; }
        .hp-qrp-url [data-cast] { display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }
        {{-- A narrow stage (a phone, or the column beside the words on a small laptop) has no room
           to stand the three in front of each other: the browser stands whole at the top and the
           phone and the poster under it, so no name lies over another thing's words. --}}
        @container (max-width: 36.99rem) {
            #hp .hp-obj-share { min-height: 0; padding-bottom: 0; }
            #hp .hp-out.is-site { margin-top: 0; margin-bottom: clamp(14.5rem, 62cqi, 20rem); }
            {{-- The browser shows its first event whole; the phone and the poster hang side by
               side over its foot, each with its name on its own top edge. --}}
            #hp .hp-site { border-bottom: 1px solid var(--hp-line); border-radius: 1.15rem; }
            #hp .hp-site .hp-row:nth-child(n+2) { display: none; }
            #hp .hp-site .hp-sched { padding-bottom: 3.25rem; }
            #hp .hp-out > .hp-chip { padding: 0.42rem 0.7rem; font-size: 0.76rem; }
            #hp .hp-out.is-phone > .hp-chip { bottom: calc(100% - 0.95rem); inset-inline: auto -0.35rem; }
            #hp .hp-out.is-qr > .hp-chip { bottom: calc(100% - 0.7rem); inset-inline-start: -0.35rem; }
            #hp .hp-out.is-qr { bottom: 9%; width: 34%; }
        }
        @container (min-width: 37rem) {
            #hp .hp-out.is-site { width: 58%; margin-inline-start: 19.5%; }
            #hp .hp-out.is-phone { inset-inline-end: 4.5%; width: 26%; }
            #hp .hp-out.is-qr { inset-inline-start: 4.5%; bottom: 9%; width: 20%; }
            #hp .hp-qrp-url { display: block; }
        }
        {{-- Beside a stage that is the sky's own blue, a blue button is one more piece of sky. --}}
        #promote .hp-beat-copy .hp-btn-primary { background: var(--hp-ink); color: var(--hp-bg); box-shadow: 0 14px 30px -14px rgba(10, 16, 32, 0.75); }
        #promote .hp-beat-copy .hp-btn-primary:hover { box-shadow: 0 20px 38px -14px rgba(10, 16, 32, 0.85), 0 0 0 1px var(--hp-blue); }
        .hp-browser { overflow: hidden; border-bottom: 0; border-radius: 1.15rem 1.15rem 0 0; }
        .hp-browser-bar { display: flex; align-items: center; gap: 0.4rem; padding: 0.7rem 0.9rem; border-bottom: 1px solid var(--hp-line); background: var(--hp-bg); }
        .dark .hp-browser-bar { background: #0c1120; }
        .hp-browser-bar i { width: 0.65rem; height: 0.65rem; border-radius: 999px; background: #ff5f57; }
        .hp-browser-bar i:nth-child(2) { background: #febc2e; }
        .hp-browser-bar i:nth-child(3) { background: #28c840; }
        .hp-url { min-width: 0; margin-inline: auto; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding: 0.2rem 0.9rem; border-radius: 0.5rem; background: var(--hp-bg-2); font-family: var(--hp-mono); font-size: 0.74rem; color: var(--hp-ink-2); box-shadow: inset 0 0 0 1px var(--hp-line); }
        .dark .hp-url { background: rgba(255, 255, 255, 0.07); }
        @container (max-width: 21rem) {
            #hp .hp-url { padding-inline: 0.5rem; font-size: 0.66rem; }
        }
        .hp-sched { padding: clamp(1rem, 2vw, 1.5rem); }
        .hp-sched-head { display: flex; align-items: center; gap: 0.85rem; padding-bottom: 1.1rem; }
        .hp-avatar { display: inline-flex; flex: none; align-items: center; justify-content: center; width: 3rem; height: 3rem; border-radius: 0.9rem; background: linear-gradient(135deg, #4e81fa, #22d3ee); font-size: 1.25rem; font-weight: 700; font-variation-settings: 'wght' 860; color: #fff; }
        .hp-sched-head strong { display: block; font-size: 1.1rem; letter-spacing: -0.02em; line-height: 1.2; overflow-wrap: anywhere; }
        .hp-sched-head div { min-width: 0; }
        .hp-sched-head div span { display: block; font-size: 0.82rem; line-height: 1.35; color: var(--hp-ink-3); text-wrap: balance; }
        .hp-follow { margin-inline-start: auto; padding: 0.4rem 1rem; border-radius: 999px; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); font-size: 0.78rem !important; font-weight: 700; color: #fff !important; }
        .hp-rows { display: grid; gap: 0.6rem; }
        .hp-row { display: flex; align-items: center; gap: 0.8rem; padding: 0.65rem 0.75rem; border: 1px solid var(--hp-line); border-radius: 0.9rem; }
        .hp-row strong { display: block; font-size: 0.95rem; letter-spacing: -0.01em; line-height: 1.25; }
        .hp-row span { font-size: 0.8rem; color: var(--hp-ink-3); }
        .hp-row .hp-pill { margin-inline-start: auto; }
        html.es-anim [data-reveal] .hp-chip { opacity: 0; transform: translateY(10px) scale(0.94); transition: opacity 0.5s ease, transform 0.6s cubic-bezier(0.22, 1, 0.36, 1); transition-delay: calc(var(--i, 0) * 0.16s + 0.45s); }
        html.es-anim [data-reveal].is-revealed .hp-chip { opacity: 1; transform: none; }

        {{-- Sell: tickets on sale --}}
        .hp-obj-sell { display: grid; place-items: center; align-content: center; padding: 6.5rem clamp(1.25rem, 3vw, 2.75rem) clamp(1.25rem, 3vw, 2.75rem); }
        .hp-checkout { width: min(100%, 30rem); padding: clamp(1rem, 1.8vw, 1.6rem); }
        .hp-checkout-head { display: flex; align-items: center; gap: 0.8rem; padding-bottom: 1rem; border-bottom: 1px solid var(--hp-line); }
        .hp-checkout-head strong { display: block; font-size: 1.1rem; letter-spacing: -0.02em; line-height: 1.2; }
        .hp-checkout-head span { font-size: 0.82rem; color: var(--hp-ink-3); }
        .hp-tt { display: flex; align-items: center; gap: 0.75rem; padding: 0.8rem 0; border-bottom: 1px solid var(--hp-line); }
        .hp-tt strong { display: block; font-size: 0.95rem; line-height: 1.25; }
        .hp-tt small { font-size: 0.78rem; color: var(--hp-ink-3); }
        .hp-tt-price { margin-inline-start: auto; font-weight: 700; font-variant-numeric: tabular-nums; }
        .hp-qty { display: inline-flex; flex: none; align-items: center; gap: clamp(0.3rem, 1.4cqi, 0.55rem); padding: 0.2rem 0.3rem; border: 1px solid var(--hp-line-2); border-radius: 0.6rem; font-size: 0.85rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .hp-qty b { display: inline-flex; align-items: center; justify-content: center; width: 1.4rem; height: 1.4rem; border-radius: 0.4rem; background: var(--hp-bg-3); font-weight: 700; color: var(--hp-ink-2); }
        .hp-code { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.85rem; font-size: 0.82rem; color: var(--hp-ink-2); }
        .hp-code b { min-width: 6.5rem; min-height: 1.5rem; padding: 0.15rem 0.5rem; border: 1px dashed var(--hp-line-2); border-radius: 0.4rem; font-family: var(--hp-mono); font-size: 0.76rem; letter-spacing: 0.06em; }
        .hp-sum { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-top: 0.7rem; font-size: 0.9rem; color: var(--hp-ink-2); }
        .hp-sum b { font-weight: 700; font-variant-numeric: tabular-nums; color: var(--hp-ink); }
        .hp-sum.is-zero b { color: #15803d; }
        .dark .hp-sum.is-zero b { color: #86efac; }
        .hp-code + .hp-sum { margin-top: 0.85rem; border-top: 1px solid var(--hp-line); }
        .hp-payout { position: relative; display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: min(100%, 30rem); margin-top: 1rem; text-wrap: balance; font-size: 0.86rem; font-weight: 700; font-variation-settings: 'wght' 600; color: var(--hp-ink-2); text-align: center; }
        .hp-payout svg { flex: none; width: 1rem; height: 1rem; }
        .hp-pay { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem; padding: 0.8rem; border-radius: 0.8rem; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); font-size: 0.95rem; font-weight: 700; color: #fff; }
        .hp-zero {
            position: absolute;
            top: 0.7rem;
            right: 0.7rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 5.6rem;
            aspect-ratio: 1;
            border-radius: 999px;
            background: var(--hp-ink);
            color: var(--hp-bg);
            box-shadow: var(--hp-pop-shadow);
            transform: rotate(10deg);
            text-align: center;
            line-height: 1;
        }
        @container (min-width: 33rem) {
            #hp .hp-obj-sell { padding-top: clamp(1.25rem, 3vw, 2.75rem); }
            #hp .hp-zero { top: 7%; right: 4.5%; width: clamp(5.5rem, 18cqi, 7.5rem); }
            #hp .hp-zero b { font-size: clamp(1.7rem, 6cqi, 2.5rem); }
            #hp .hp-zero span { font-size: clamp(0.5rem, 1.5cqi, 0.62rem); }
        }
        .hp-zero b { font-size: 1.7rem; font-weight: 700; font-variation-settings: 'wght' 880; letter-spacing: -0.05em; }
        .hp-zero span { margin-top: 0.3em; font-family: var(--hp-mono); font-size: 0.625rem; letter-spacing: 0.06em !important; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }

        {{-- The evening: Sell's first piece, the full width of the window, and a thing of the day
           only. The sky over it runs from the page's own ground through the day's blue into blue
           hour, which the whole scene stands in, and reaches the night's black only at its foot,
           where the band takes over. What is written on it is light and what stands on it stands
           free, as the ticket does in the night, so every colour here is literal. At night, where
           the page is dark already, none of this applies: the scene is drawn as the other acts'
           are, and the band below it is the one lit room. --}}
        .hp-eve { position: relative; }
        html:not(.dark) #hp .hp-eve {
            isolation: isolate;
            padding-top: clamp(9.5rem, 17vh, 12.5rem);
            {{-- One line of the night first: the foot of the sky and the head of the band rarely
               meet on a whole pixel. The words start below the 11rem line at any width, where
               the sky is already the deep blue they are measured against. --}}
            background:
                linear-gradient(#050814, #050814) bottom / 100% 1px no-repeat,
                linear-gradient(to bottom, var(--hp-bg) 0, #cfe0ff 2rem, #8fb4fb 4.5rem, #4e81fa 7.25rem, #2a57cf 10rem, #183b9c 13.5rem, #102a78 18rem, #0c1e5a 25rem, #081340 calc(100% - 9rem), #050814 100%);
            color: #eef2ff;
        }
        html:not(.dark) #hp .hp-eve .hp-run::before { background: rgba(169, 198, 255, 0.34); }
        html:not(.dark) #hp .hp-eve .hp-beat-copy .hp-kicker { color: #e3ebff; }
        html:not(.dark) #hp .hp-eve .hp-soft { color: #a9c6ff; }
        html:not(.dark) #hp .hp-eve .hp-beat-copy > p { color: #dbe3f7; }
        html:not(.dark) #hp .hp-eve .hp-paid { color: #c9d6f5; }
        html:not(.dark) #hp .hp-eve .hp-more { color: #cfe0ff; }
        html:not(.dark) #hp .hp-eve .hp-obj { border: 0; background: transparent; box-shadow: none; overflow: visible; }
        html:not(.dark) #hp .hp-eve .hp-obj::before { display: none; }
        html:not(.dark) #hp .hp-eve .hp-card { color: var(--hp-ink); }
        html:not(.dark) #hp .hp-eve .hp-checkout { box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.14), 0 0 120px -14px rgba(141, 176, 255, 0.85), 0 40px 80px -30px rgba(2, 8, 32, 0.9); }
        html:not(.dark) #hp .hp-eve .hp-payout { color: #dbe3f7; }
        html:not(.dark) #hp .hp-eve .hp-zero { background: linear-gradient(135deg, #a5f3fc, #22d3ee); color: #04101f; box-shadow: 0 0 54px rgba(34, 211, 238, 0.6), 0 18px 40px -16px rgba(2, 8, 32, 0.9); }
        {{-- The night of the show, where Sell ends. The band is the full width of the window and
           dark in both modes (so its colours are literal). The page's light follows the story: by
           day the evening above it is its dusk, and a dawn below it breaks into the ground of the
           section that follows (--hp-dawn); at night, where the whole page is already dark, the
           band is the one lit room, with a dusk and a dawn of its own. Its lights are layers of
           their own, so the band itself is one flat colour under the text. --}}
        .hp-night {
            position: relative;
            isolation: isolate;
            z-index: 1;
            overflow: hidden;
            overflow: clip;
            --hp-dawn: var(--hp-bg-2);
            {{-- One pixel over the evening's foot, for the same reason as the line above. The
               door's own scene brings the room it needs above it. --}}
            margin-block: -1px 0;
            padding-block: 0 clamp(23.5rem, 23vw + 15.5rem, 31rem);
            {{-- The dark stops two pixels short of the band's bottom edge. The band rarely ends
               on a whole pixel, and a browser that draws the dark and then the dawn over it, each
               softened at that edge, leaves a grey hairline the width of the window. --}}
            background: linear-gradient(#050814, #050814) 0 0 / 100% calc(100% - 2px) no-repeat;
            color: #eef2ff;
        }
        .hp-night::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background:
                linear-gradient(to bottom, #050814 0, rgba(5, 8, 20, 0) 9rem) top / 100% 9rem no-repeat,
                radial-gradient(60% 9rem at 50% 100%, var(--hp-dawn), rgba(255, 255, 255, 0) 72%) bottom / 100% 9rem no-repeat,
                linear-gradient(to top, var(--hp-dawn) 0, var(--hp-dawn) 2px, #bfe3f6 2.6rem, #4f9fd0 5.8rem, #16306a 9.6rem, rgba(5, 8, 20, 0) 14.5rem) bottom / 100% 14.5rem no-repeat,
                radial-gradient(52rem 30rem at 72% 26%, rgba(47, 102, 234, 0.42), transparent 70%),
                radial-gradient(34rem 22rem at 14% 78%, rgba(34, 211, 238, 0.16), transparent 70%);
        }
        .dark .hp-night {
            --hp-dawn: var(--hp-bg-3);
            margin-block: clamp(1.5rem, 4vw, 3.5rem) 0;
            padding-top: clamp(8rem, 15vh, 11rem);
            background: linear-gradient(#111d5a, #111d5a) 0 2px / 100% calc(100% - 4px) no-repeat;
        }
        .dark .hp-night::before {
            background:
                linear-gradient(to bottom, var(--hp-bg) 0, var(--hp-bg) 2px, rgba(17, 29, 90, 0) 9rem) top / 100% 9rem no-repeat,
                linear-gradient(to top, var(--hp-dawn) 0, var(--hp-dawn) 2px, rgba(17, 29, 90, 0) 9rem) bottom / 100% 9rem no-repeat,
                radial-gradient(60rem 34rem at 72% 30%, rgba(78, 129, 250, 0.95), transparent 72%),
                radial-gradient(40rem 26rem at 14% 78%, rgba(34, 211, 238, 0.42), transparent 70%),
                radial-gradient(70rem 20rem at 50% 100%, rgba(14, 165, 233, 0.35), transparent 75%);
        }
        .hp-night .hp-run::before { background: rgba(125, 165, 255, 0.3); }
        .hp-night .hp-beat-copy .hp-h3 { font-size: clamp(2.5rem, 4.6vw, 4.9rem); line-height: 0.98; }
        .hp-night .hp-beat-copy > p { font-size: clamp(1.125rem, 0.4vw + 1.05rem, 1.3rem); }
        .hp-night .hp-beat-copy > p { color: #c5cde2; }
        :is(.hp-night, html:not(.dark) .hp-eve) .hp-step { border-color: rgba(125, 165, 255, 0.5); background: #0d1430; color: #eef2ff; box-shadow: 0 0 0 4px rgba(78, 129, 250, 0.16), 0 0 44px rgba(34, 211, 238, 0.4); }
        :is(.hp-night, html:not(.dark) .hp-eve) .hp-step b { color: #9fb1d6; }
        :is(.hp-night, html:not(.dark) .hp-eve) .hp-step .hp-vbars { background: linear-gradient(100deg, #4e81fa, #22d3ee); }
        :is(.hp-night, html:not(.dark) .hp-eve) .hp-step .hp-vbars i { background: rgba(4, 16, 31, 0.26); }
        :is(.hp-night, html:not(.dark) .hp-eve) .hp-step .hp-vbars i.is-done::after { background: #04101f; opacity: 0.55; }
        :is(.hp-night, html:not(.dark) .hp-eve) .hp-step .hp-vbars i.is-now::after { background: #04101f; }
        {{-- The room under the band is not the tile's to ride over: it lets go with the words. --}}
        .hp-night .hp-run > .hp-step { margin-bottom: clamp(8rem, 14vw, 15rem); }
        .hp-night .hp-more { color: #a9c3ff; }
        .hp-night .hp-ink-grad { background-image: linear-gradient(100deg, #7da5ff 0%, #38bdf8 55%, #67e8f9 100%); }
        .hp-doors { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.6rem; margin-top: 1.75rem; }
        .hp-doors > span { display: flex; flex-direction: column; gap: 0.2rem; min-width: 0; padding: 0.8rem 0.85rem; border: 1px solid rgba(125, 165, 255, 0.24); border-radius: 0.9rem; background: rgba(13, 20, 48, 0.7); font-family: var(--hp-mono); font-size: 0.62rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; white-space: nowrap; color: #9fb1d6; }
        .hp-doors b { font-family: var(--hp-display); font-size: clamp(1.1rem, 1.35vw, 1.5rem); font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.03em; line-height: 1; text-transform: none; white-space: nowrap; color: #67e8f9; text-shadow: 0 0 22px rgba(34, 211, 238, 0.6); }
        .hp-obj-door {
            display: grid;
            place-items: center;
            background: transparent;
            box-shadow: none;
            overflow: visible;
        }
        .hp-obj-door::before { display: none; }
        .hp-beams {
            position: absolute;
            inset: -12% 0 0 0;
            background: conic-gradient(from 0deg at 50% -8%, transparent 150deg, rgba(103, 232, 249, 0.26) 163deg, transparent 172deg, rgba(125, 165, 255, 0.4) 180deg, transparent 188deg, rgba(103, 232, 249, 0.24) 197deg, transparent 210deg);
            -webkit-mask-image: linear-gradient(to bottom, #000 30%, transparent 92%);
            mask-image: linear-gradient(to bottom, #000 30%, transparent 92%);
            pointer-events: none;
        }
        .hp-obj-door { min-height: clamp(26rem, 40vw, 38rem); border: 0; }
        html.es-anim .hp-obj-door.is-revealed .hp-beams { transform-origin: 50% 0; animation: hp-beams 11s ease-in-out infinite alternate; }
        @keyframes hp-beams { from { transform: rotate(-4deg); } to { transform: rotate(4deg); } }
        {{-- The room. Five rows of thirty, the far row narrow and faint and the near one the width of
           the window, so the lights stand back from it without a 3D transform to flatten them. --}}
        .hp-room { position: absolute; inset-inline: 0; bottom: clamp(11rem, 9vw + 6.5rem, 13rem); pointer-events: none; }
        .hp-room-cap { margin-bottom: clamp(1.6rem, 2.8vw, 2.75rem); font-family: var(--hp-mono); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.16em; text-align: center; text-transform: uppercase; color: #9fb1d6; }
        .hp-room-cap b { display: block; margin-bottom: 0.5rem; font-family: var(--hp-display); font-size: clamp(2.75rem, 5vw, 4.75rem); font-variation-settings: 'wght' 860; letter-spacing: -0.04em; line-height: 0.9; color: #67e8f9; text-shadow: 0 0 22px rgba(34, 211, 238, 0.6); vertical-align: -0.12em; }
        .hp-crowd { position: relative; display: grid; justify-items: center; }
        .hp-crowd::before { content: ""; position: absolute; top: -4.5rem; left: 50%; width: 52%; height: 7rem; background: radial-gradient(closest-side, rgba(103, 232, 249, 0.3), rgba(78, 129, 250, 0.12) 55%, transparent); transform: translateX(-50%); pointer-events: none; }
        .hp-crowd > span { display: flex; justify-content: space-between; width: var(--w); margin-top: var(--g); opacity: var(--o); }
        .hp-crowd > span:nth-child(1) { --w: 46%; --k: 0.4; --g: 0; --o: 0.45; }
        .hp-crowd > span:nth-child(2) { --w: 58%; --k: 0.53; --g: 0.7rem; --o: 0.6; }
        .hp-crowd > span:nth-child(3) { --w: 73%; --k: 0.68; --g: 1rem; --o: 0.78; }
        .hp-crowd > span:nth-child(4) { --w: 91%; --k: 0.84; --g: 1.4rem; --o: 0.92; }
        .hp-crowd > span:nth-child(5) { --w: 112%; --k: 1; --g: 1.9rem; --o: 1; }
        .hp-crowd i { width: calc(var(--k) * clamp(0.5rem, 1.3vw, 1.2rem)); aspect-ratio: 1; border-radius: 999px; background: #3a4a80; opacity: 0.55; }
        .hp-crowd i.is-in { background: #67e8f9; opacity: 1; box-shadow: 0 0 0.7rem rgba(34, 211, 238, 0.9); }
        html.es-anim .hp-room:not(.is-revealed) .hp-crowd i.is-in { background: #3a4a80; opacity: 0.55; box-shadow: none; }
        html.es-anim .hp-room.is-revealed .hp-crowd i.is-in { animation: hp-seat 0.6s ease-out both; animation-delay: calc(var(--i) * 14ms + 0.3s); }
        @keyframes hp-seat {
            0% { background: #3a4a80; opacity: 0.55; box-shadow: none; transform: scale(1); }
            50% { background: #fff; opacity: 1; transform: scale(1.9); }
            100% { background: #67e8f9; opacity: 1; box-shadow: 0 0 0.7rem rgba(34, 211, 238, 0.9); transform: scale(1); }
        }
        .hp-ticket-wrap { position: relative; transform: rotate(-4deg); filter: drop-shadow(0 0 46px rgba(78, 129, 250, 0.6)) drop-shadow(0 30px 40px rgba(0, 0, 0, 0.6)); }
        {{-- Foil: a band of light across the ticket that sits where the pointer is (the shared tilt
           script hands over --gx and --gy). Blue, cyan and white only. --}}
        .hp-ticket::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(115deg, transparent 22%, rgba(103, 232, 249, 0.5) 40%, rgba(255, 255, 255, 0.85) 50%, rgba(125, 165, 255, 0.5) 60%, transparent 78%);
            background-size: 260% 260%;
            background-position: var(--gx, 50%) var(--gy, 50%);
            mix-blend-mode: soft-light;
            opacity: 0;
            transition: opacity 0.35s ease;
            pointer-events: none;
        }
        .hp-ticket-wrap:hover .hp-ticket::after { opacity: 1; }
        html.es-anim [data-reveal] .hp-chip-in { transition-delay: 2.4s; }
        .hp-ticket {
            position: relative;
            width: clamp(14rem, 24vw, 23rem);
            border-radius: 1.3rem;
            background: #f8fafc;
            color: #0a1020;
            -webkit-mask-image: radial-gradient(circle 0.7rem at 0 var(--hp-tear), transparent 98%, #000), radial-gradient(circle 0.7rem at 100% var(--hp-tear), transparent 98%, #000);
            mask-image: radial-gradient(circle 0.7rem at 0 var(--hp-tear), transparent 98%, #000), radial-gradient(circle 0.7rem at 100% var(--hp-tear), transparent 98%, #000);
            -webkit-mask-composite: source-in;
            mask-composite: intersect;
            --hp-tear: 5.6rem;
        }
        .hp-ticket-head { padding: 1rem 1.15rem 1.1rem; border-radius: 1.3rem 1.3rem 0 0; background: linear-gradient(120deg, #2b5fe3, #0ea5e9 70%, #22d3ee); color: #fff; }
        .hp-ticket-head small { display: block; font-family: var(--hp-mono); font-size: 0.6rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; opacity: 0.85; }
        .hp-ticket-head strong { display: block; margin-top: 0.25rem; font-size: 1.45rem; font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.03em; line-height: 1.1; }
        .hp-ticket-head > span { display: block; margin-top: 0.2rem; font-size: 0.78rem; font-weight: 700; font-variation-settings: 'wght' 600; opacity: 0.92; }
        .hp-ticket-body { position: relative; padding: 1.3rem 1.15rem 1rem; border-top: 2px dashed rgba(10, 16, 32, 0.22); }
        .hp-ticket-qr { position: relative; width: 62%; margin-inline: auto; }
        .hp-ticket-qr svg { display: block; width: 100%; height: auto; }
        #hp .hp-ticket-qr .es-laser { left: -8%; right: -8%; }
        .hp-ticket-foot { display: flex; justify-content: space-between; margin-top: 0.9rem; font-family: var(--hp-mono); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.06em; color: #56617c; }
        .hp-night .hp-chip { border-color: rgba(125, 165, 255, 0.28); background: #101833; color: #eef2ff; box-shadow: 0 18px 40px -16px rgba(0, 0, 0, 0.9), 0 0 30px -8px rgba(34, 211, 238, 0.5); }
        .hp-chip-in { top: 5%; right: 5%; }
        @container (min-width: 33rem) {
            #hp .hp-chip-in { top: 14%; right: 9%; }
        }

        {{-- Promote: the digest that goes out by itself, beside the schedule's follower count and the
           week's visits. The digest goes to confirmed subscribers; the followers are a second audience. --}}
        .hp-obj-grow { display: grid; grid-template-columns: minmax(0, 1fr); align-content: center; gap: 1.1rem; padding: clamp(1.25rem, 3vw, 2.75rem); }
        @container (min-width: 35rem) {
            #hp .hp-obj-grow { grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr); align-items: center; gap: 0; }
            {{-- The three stand as things on a table do: the count over the letter's corner, the chart below its foot. --}}
            #hp .hp-grow-side { position: relative; z-index: 1; margin-inline-start: -2rem; }
            #hp .hp-fans { transform: translateY(-1.1rem); }
            #hp .hp-chart { transform: translateY(0.9rem); }
        }
        {{-- Later the same day: the light has come down to the foot of the stage. --}}
        #grow .hp-obj { background: radial-gradient(42rem 22rem at 8% 118%, rgba(165, 243, 252, 0.62), transparent 68%), linear-gradient(175deg, #1b49cc 0%, #1c6fdf 55%, #16a9e6 100%); }
        .dark #grow .hp-obj { background: radial-gradient(42rem 22rem at 8% 118%, rgba(56, 189, 248, 0.45), transparent 68%), linear-gradient(175deg, #10286e 0%, #0d4a8e 55%, #0a7ab0 100%); }
        .hp-grow-side { display: grid; gap: 1.1rem; min-width: 0; }
        @container (min-width: 26rem) and (max-width: 34.99rem) {
            {{-- One column of stage, but wide enough for the two small cards to stand side by side. --}}
            #hp .hp-grow-side { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); align-items: start; }
            #hp .hp-fans { flex-direction: column; align-items: flex-start; }
        }
        .hp-mail { display: flex; flex-direction: column; padding: clamp(1rem, 1.8vw, 1.4rem); }
        .hp-mail-head { display: flex; align-items: center; gap: 0.7rem; padding-bottom: 0.9rem; border-bottom: 1px solid var(--hp-line); }
        .hp-mail-head .hp-avatar { width: 2.4rem; height: 2.4rem; border-radius: 0.75rem; font-size: 1.05rem; }
        .hp-mail-head > span:nth-child(2) { min-width: 0; }
        .hp-mail-head b { display: block; overflow: hidden; font-size: 0.92rem; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
        .hp-mail-head i { display: block; font-style: normal; font-size: 0.76rem; color: var(--hp-ink-3); }
        .hp-mail-subject { margin-top: 0.9rem; font-size: clamp(1.05rem, 3cqi, 1.5rem); white-space: nowrap; font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.03em; line-height: 1.1; }
        .hp-mail-rows { display: grid; gap: 0.45rem; margin-top: 0.9rem; }
        .hp-mail-rows > span { display: flex; align-items: center; gap: 0.7rem; min-width: 0; padding: 0.5rem 0.6rem; border: 1px solid var(--hp-line); border-radius: 0.8rem; }
        .hp-mail-rows > span > span:last-child { min-width: 0; }
        .hp-mail-rows > span > span:last-child b { display: block; overflow: hidden; font-size: 0.9rem; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
        .hp-mail-rows > span > span:last-child i { display: block; font-style: normal; font-size: 0.76rem; color: var(--hp-ink-3); }
        .hp-mail .hp-pay { margin-top: 0.9rem; }
        .hp-mail-foot { display: flex; align-items: center; justify-content: center; gap: 0.45rem; margin-top: 0.75rem; font-size: 0.8rem; font-weight: 700; font-variation-settings: 'wght' 600; color: var(--hp-ink-3); }
        .hp-mail-foot .hp-ok { width: 1.15rem; height: 1.15rem; }
        .hp-chart { padding: clamp(0.9rem, 1.6vw, 1.25rem); }
        .hp-chart-head { display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; }
        .hp-chart-head strong { font-size: clamp(1.4rem, 2.2vw, 1.9rem); font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.04em; line-height: 1; }
        .hp-chart-head span { font-size: 0.72rem; line-height: 1.3; text-align: end; white-space: nowrap; color: var(--hp-ink-3); }
        .hp-bars { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); align-items: end; gap: clamp(0.3rem, 0.8vw, 0.6rem); height: clamp(4.75rem, 8vw, 6.75rem); margin-top: 1rem; }
        .hp-bars .es-bar { border-radius: 0.4rem 0.4rem 0.12rem 0.12rem; background: linear-gradient(to top, rgba(78, 129, 250, 0.35), rgba(78, 129, 250, 0.6)); }
        .hp-bars .es-bar.is-peak { background: linear-gradient(to top, #2f66ea, #22d3ee); box-shadow: 0 0 26px rgba(34, 211, 238, 0.5); }
        .hp-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: clamp(0.3rem, 0.8vw, 0.6rem); margin-top: 0.45rem; text-align: center; font-family: var(--hp-mono); font-size: 0.58rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--hp-ink-3); }
        .hp-days .is-peak { color: var(--hp-blue); }
        .hp-fans { display: flex; align-items: center; gap: 0.9rem; padding: 0.9rem 1.1rem; }
        .hp-faces { display: flex; }
        .hp-faces i { display: inline-flex; align-items: center; justify-content: center; width: 2.4rem; height: 2.4rem; margin-inline-start: -0.6rem; border: 2px solid var(--hp-bg-2); border-radius: 999px; font-style: normal; font-size: 0.8rem; font-weight: 700; color: #fff; }
        .dark .hp-faces i { border-color: #131a2e; }
        .hp-faces i:first-child { margin-inline-start: 0; }
        .hp-faces i.is-more { width: auto; min-width: 2.4rem; padding-inline: 0.5rem; background: var(--hp-bg-3); font-size: 0.72rem; color: var(--hp-ink-2); }
        .hp-fans strong { display: block; font-size: 1.15rem; font-variation-settings: 'wght' 820; letter-spacing: -0.02em; line-height: 1.2; }
        .hp-fans span { font-size: 0.8rem; color: var(--hp-ink-3); }

        {{-- ---------------------------------------------------------------
           4. The booking page's stage (appointments are the second half of Plan, above)
           --------------------------------------------------------------- --}}

        .hp-obj-book { display: grid; place-items: center; padding: clamp(1.25rem, 3vw, 2.75rem) clamp(1rem, 2.6vw, 2.5rem) clamp(2rem, 3.4vw, 3rem); }
        .hp-obj-book .hp-book-wrap { width: 100%; max-width: 37rem; }
        .hp-beat-copy > p strong { color: var(--hp-ink); }
        .hp-beat-copy .hp-checks { margin-top: 1.5rem; font-size: 1rem; }
        .hp-checks { display: grid; gap: 0.85rem; margin-top: 1.75rem; }
        .hp-checks li { display: flex; align-items: flex-start; gap: 0.75rem; color: var(--hp-ink-2); }
        .hp-checks .hp-ok { margin-top: 0.15rem; background: rgba(78, 129, 250, 0.14); color: var(--hp-blue); }
        .hp-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem 1.5rem; margin-top: 2.25rem; }
        .hp-book-wrap { position: relative; }
        .hp-book { overflow: hidden; border-radius: 1.4rem; }
        .hp-book-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; padding: clamp(1.1rem, 2.4vw, 1.9rem); }
        @container (min-width: 31rem) {
            #hp .hp-book-grid { grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); }
        }
        .hp-month-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.8rem; }
        .hp-month-head strong { font-size: 1.1rem; letter-spacing: -0.02em; }
        .hp-month-head span { display: flex; gap: 0.35rem; }
        .hp-month-head i { display: inline-flex; align-items: center; justify-content: center; width: 1.7rem; height: 1.7rem; border: 1px solid var(--hp-line-2); border-radius: 0.5rem; font-style: normal; color: var(--hp-ink-3); }
        .hp-month { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.2rem; text-align: center; font-variant-numeric: tabular-nums; }
        .hp-month b { padding-bottom: 0.3rem; font-family: var(--hp-mono); font-size: 0.66rem; font-weight: 700; letter-spacing: 0.08em; color: var(--hp-ink-3); }
        .hp-month span { position: relative; display: flex; align-items: center; justify-content: center; aspect-ratio: 1; border-radius: 0.6rem; font-size: 0.88rem; font-weight: 700; font-variation-settings: 'wght' 600; }
        .hp-month .is-off { color: var(--hp-ink-3); opacity: 0.55; }
        .hp-month .is-open::after { content: ""; position: absolute; bottom: 12%; left: 50%; width: 0.28rem; height: 0.28rem; margin-left: -0.14rem; border-radius: 999px; background: #0ea5e9; }
        .hp-month .is-pick { background: linear-gradient(120deg, #2b5fe3, #2f6fe9); color: #fff; font-weight: 700; box-shadow: 0 8px 18px -8px rgba(47, 102, 234, 0.9); }
        .hp-legend { display: flex; flex-wrap: wrap; gap: 0.4rem 1rem; margin-top: 0.9rem; padding-top: 0.8rem; border-top: 1px solid var(--hp-line); font-size: 0.76rem; color: var(--hp-ink-3); }
        .hp-legend span { display: inline-flex; align-items: center; gap: 0.4rem; }
        .hp-legend i { width: 0.55rem; height: 0.55rem; border-radius: 999px; background: #0ea5e9; }
        .hp-slots { display: flex; flex-direction: column; gap: 0.5rem; }
        .hp-slots-type { display: block; margin-bottom: 0.35rem; font-size: 1rem; letter-spacing: -0.01em; line-height: 1.25; }
        .hp-slots small { font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        .hp-slot { display: flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.55rem; border: 1px solid var(--hp-line-2); border-radius: 0.7rem; font-size: 0.86rem; font-weight: 700; }
        .hp-slot.is-pick { border-color: transparent; background: rgba(78, 129, 250, 0.14); color: var(--hp-blue); box-shadow: inset 0 0 0 2px var(--hp-blue); }
        .hp-slot svg { width: 0.85rem; height: 0.85rem; }
        .hp-slots .hp-pay { margin-top: auto; }
        .hp-chip-booked { bottom: -1rem; left: -0.75rem; }
        @media (max-width: 639px) {
            .hp-chip-booked { left: 0.5rem; }
        }

        {{-- ---------------------------------------------------------------
           5. Everything else
           --------------------------------------------------------------- --}}

        .hp-bill {
            position: relative;
            padding: clamp(1.5rem, 4vw, 3.25rem) clamp(1.25rem, 5vw, 4.5rem) clamp(1.75rem, 4vw, 3.25rem);
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            background:
                radial-gradient(46rem 20rem at 50% 0%, var(--hp-glow), transparent 70%),
                var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
        }
        .hp-bill-top { display: flex; justify-content: space-between; gap: 1rem; padding-bottom: 0.9rem; border-bottom: 2px solid var(--hp-ink); font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--hp-ink-3); }
        .hp-bill .hp-h2 { margin-top: clamp(1.5rem, 3vw, 2.5rem); font-size: clamp(1.2rem, 0.8vw + 1rem, 1.6rem); font-variation-settings: 'wght' 700; letter-spacing: -0.02em; line-height: 1.3; color: var(--hp-ink-2); }
        .hp-bill-list { margin-top: 1.25rem; }
        .hp-bill-list li { border-top: 1px solid var(--hp-line); }
        .hp-bill-list li.hp-bill-break { display: none; }
        .hp-bill-list a { display: block; padding-block: 0.95rem; }
        .hp-bill-name { display: block; font-size: 1.2rem; font-weight: 700; font-variation-settings: 'wght' 860; letter-spacing: -0.03em; line-height: 1.15; text-transform: uppercase; color: var(--hp-ink); transition: color 0.2s ease; }
        .hp-bill-desc { display: block; margin-top: 0.35rem; font-size: 0.95rem; line-height: 1.5; color: var(--hp-ink-2); }
        .hp-bill-list a:hover .hp-bill-name { color: var(--hp-blue); }
        {{-- Where there is a pointer and the room: the names set as a bill, in three sizes, and the
           sentence of the one being pointed at standing in one place under them. --}}
        @media (hover: hover) and (min-width: 768px) {
            .hp-bill { text-align: center; }
            .hp-bill-list {
                --b: clamp(1.5rem, 2.6vw, 2.75rem);
                position: relative;
                display: flex;
                flex-wrap: wrap;
                align-items: baseline;
                justify-content: center;
                gap: 0.1rem 1.6rem;
                padding-bottom: 6.25rem;
            }
            .hp-bill-list li { border-top: 0; }
            .hp-bill-list li.hp-bill-break { display: block; flex-basis: 100%; height: 0.35rem; }
            .hp-bill-list a { padding-block: 0.1rem; }
            .hp-bill-name { font-size: var(--b); line-height: 1.12; white-space: nowrap; }
            .hp-bill-list li:nth-child(-n+3) .hp-bill-name { font-size: calc(var(--b) * 1.35); letter-spacing: -0.045em; }
            .hp-bill-list li:nth-child(n+9) .hp-bill-name { font-size: calc(var(--b) * 0.8); }
            .hp-bill-list li:nth-child(even) .hp-bill-name { color: var(--hp-blue); }
            .hp-bill-list a:hover .hp-bill-name,
            .hp-bill-list a:focus-visible .hp-bill-name { color: var(--hp-ink); text-decoration: underline; text-decoration-thickness: 0.07em; text-underline-offset: 0.12em; }
            .hp-bill-list li:nth-child(odd) a:hover .hp-bill-name,
            .hp-bill-list li:nth-child(odd) a:focus-visible .hp-bill-name { color: var(--hp-blue); }
            .hp-bill-list::after,
            .hp-bill-desc {
                position: absolute;
                inset-inline: 0;
                bottom: 0;
                display: grid;
                place-items: center;
                height: 4.75rem;
                margin: 0;
                border-top: 1px solid var(--hp-line);
            }
            .hp-bill-list::after { content: attr(data-hint); font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--hp-ink-3); }
            .hp-bill-desc {
                z-index: 1;
                padding-inline: max(0rem, calc((100% - 46rem) / 2));
                background: var(--hp-bg-2);
                font-size: 1.05rem;
                text-wrap: balance;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.2s ease, visibility 0s linear 0.2s;
            }
            .hp-bill-list a:hover .hp-bill-desc,
            .hp-bill-list a:focus-visible .hp-bill-desc { opacity: 1; visibility: visible; transition-delay: 0s; }
        }

        {{-- ---------------------------------------------------------------
           6. Free and open source
           --------------------------------------------------------------- --}}

        .hp-figs {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            margin-top: clamp(2.25rem, 4vw, 3.5rem);
            border: 1px solid var(--hp-line);
            border-radius: 1.9rem;
            background:
                radial-gradient(40rem 18rem at 50% 0%, var(--hp-glow), transparent 70%),
                var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
        }
        .hp-fig { padding: clamp(1.75rem, 3.4vw, 3rem); text-align: center; }
        .hp-fig + .hp-fig { border-top: 1px solid var(--hp-line); }
        @media (min-width: 768px) {
            .hp-figs { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .hp-fig + .hp-fig { border-top: 0; border-inline-start: 1px solid var(--hp-line); }
        }
        {{-- Plain type, not the rolling odometer: its digit columns are all as wide as a nought, which
           set "100%" as "1 00%" in a face whose one is narrow. --}}
        .hp-num { display: inline-block; font-variation-settings: 'wght' 880; font-size: clamp(3.75rem, 7.5vw, 6.5rem); letter-spacing: -0.04em; line-height: 1; padding-inline: 0.08em; font-variant-numeric: proportional-nums; }
        #hp .hp-num { background-image: linear-gradient(120deg, #2f66ea 0%, #0b8fd8 55%, #0aa5c4 100%); }
        .dark #hp .hp-num { background-image: linear-gradient(120deg, #7da5ff 0%, #38bdf8 55%, #5eead4 100%); }
        .hp-fig strong { display: block; margin-top: 1rem; font-size: 1.3rem; letter-spacing: -0.02em; }
        .hp-fig p { max-width: 17rem; margin: 0.4rem auto 0; font-size: 0.95rem; color: var(--hp-ink-3); }
        {{-- The calculator's two number fields are driven by the sliders above it and are not shown. --}}
        #fees { scroll-margin-top: 5.5rem; }
        .hp-keep { margin-top: clamp(3.5rem, 7vw, 6rem); }
        .hp-keep:not([data-fee-ready]) .hp-sliders { display: none; }
        .hp-keep-head { display: grid; grid-template-columns: minmax(0, 1fr); align-items: end; gap: 1.5rem 3rem; margin-bottom: clamp(2.75rem, 4.4vw, 3.5rem); }
        @media (min-width: 900px) {
            .hp-keep-head { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); }
        }
        .hp-sliders { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem 2rem; }
        @media (min-width: 560px) {
            .hp-sliders { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .hp-slider span { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; font-size: 0.95rem; color: var(--hp-ink-2); }
        .hp-slider output { font-size: 1.6rem; font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.03em; font-variant-numeric: tabular-nums; color: var(--hp-ink); }
        #hp .hp-slider input[type="range"] { display: block; width: 100%; height: 2rem; margin-top: 0.35rem; accent-color: #2f66ea; cursor: pointer; }
        {{-- The sheet the slip and the calculator stand on, lit low like the morning it is. It is
           one grid: the slip, the caption, the rows, the calculator's closing sentence with its
           button, and its small print the full width of the sheet. The calculator is /compare's
           own component, left to do its sums and untouched; its boxes are dissolved here
           (display: contents) so its parts can take their places, and they are found by their
           order and by what they hold (the two number fields, the cards, the closing block). --}}
        .hp-after { --pad: clamp(1.25rem, 3.4vw, 3rem); display: grid; grid-template-columns: minmax(0, 1fr); align-items: start; padding: var(--pad); border: 1px solid var(--hp-line); border-radius: 1.9rem; background: radial-gradient(60% 70% at 4% 112%, rgba(34, 211, 238, 0.3), transparent 70%), radial-gradient(44rem 18rem at 90% 0%, var(--hp-glow), transparent 70%), var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        .hp-after-main,
        #hp .hp-calc,
        #hp .hp-calc > div:last-child { display: contents; }
        #hp .hp-calc > div:first-child { display: none; }
        {{-- The slip is paper in both modes, so its colours are literal. Its torn foot is a mask,
           which would cut a shadow off with it: the shadow is the wrapper's. It stands a little
           proud of the sheet's top edge, as a thing laid on it would. --}}
        .hp-slip-wrap { justify-self: center; width: min(100%, 20rem); margin-block: calc(-1 * var(--pad) - 1rem) 2.25rem; filter: drop-shadow(0 20px 22px rgba(10, 16, 32, 0.24)); transform: rotate(-2.5deg); }
        .dark .hp-slip-wrap { filter: drop-shadow(0 24px 30px rgba(0, 0, 0, 0.7)); }
        .hp-slip {
            display: flex;
            flex-direction: column;
            padding: 1.6rem 1.4rem 2.4rem;
            background: #fdfcf8;
            color: #0a1020;
            font-family: var(--hp-mono);
            -webkit-mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50% / 0.85rem 100%;
            mask: conic-gradient(from -45deg at bottom, #0000, #000 1deg 89deg, #0000 90deg) 50% / 0.85rem 100%;
        }
        .hp-slip-cap { font-size: 0.68rem; font-weight: 700; letter-spacing: 0.16em; text-align: center; text-transform: uppercase; color: #56617c; }
        .hp-slip-name { margin-top: 0.6rem; padding-bottom: 1.05rem; border-bottom: 1px dashed rgba(10, 16, 32, 0.32); font-family: var(--hp-display); font-size: 1.75rem; font-weight: 700; font-variation-settings: 'wght' 860; letter-spacing: -0.03em; line-height: 1.1; text-align: center; overflow-wrap: anywhere; }
        .hp-slip-row { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-top: 0.8rem; font-size: 0.88rem; }
        .hp-slip-row i,
        .hp-slip-total i { font-style: normal; color: #56617c; }
        .hp-slip-row b { font-weight: 700; font-variant-numeric: tabular-nums; }
        .hp-slip-total { display: flex; flex-direction: column; gap: 0.35rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px dashed rgba(10, 16, 32, 0.32); font-size: 0.88rem; }
        .hp-slip-total b { font-family: var(--hp-display); font-size: clamp(2.6rem, 3.6vw, 3.3rem); font-weight: 700; font-variation-settings: 'wght' 880; letter-spacing: -0.045em; line-height: 1; font-variant-numeric: tabular-nums; }
        .hp-after-cap { font-family: var(--hp-mono); font-size: 0.74rem; font-weight: 700; font-variation-settings: 'wght' 600; letter-spacing: 0.16em; text-transform: uppercase; color: var(--hp-ink-3); }
        {{-- Each card is drawn as one row: the platform and its rate, a bar as long as what
           selling there costs, and the figure. The bar is the comparison, so only our own bar is
           in colour and the other three figures step back. --}}
        #hp .hp-calc > div:nth-child(2) { display: block; margin-top: 0.4rem; }
        #hp .hp-calc > div:nth-child(2) > div { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 0.2rem 1rem; padding: 0.95rem 0; border: 0; border-bottom: 1px solid var(--hp-line); border-radius: 0; background: none; box-shadow: none; }
        #hp .hp-calc > div:nth-child(2) > div > div:not(:has(> [data-fee-total])) { grid-column: 1; margin: 0; }
        #hp .hp-calc > div:nth-child(2) > div > div:nth-of-type(1) { grid-row: 1; font-size: 1.05rem; font-weight: 700; letter-spacing: -0.01em; color: var(--hp-ink); }
        #hp .hp-calc > div:nth-child(2) > div > div:nth-of-type(2) { grid-row: 2; font-size: 0.84rem; line-height: 1.4; color: var(--hp-ink-3); text-wrap: balance; }
        #hp .hp-calc > div:nth-child(2) > div > span { position: static; grid-column: 1; grid-row: 1; justify-self: start; align-self: center; padding: 0.2rem 0.55rem; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); box-shadow: none; font-size: 0.62rem; letter-spacing: 0.1em; }
        #hp .hp-calc > div:nth-child(2) > div:has(> span) > div:nth-of-type(1) { padding-inline-start: 3.55rem; }
        #hp .hp-calc div:has(> [data-fee-total]) { display: contents; }
        #hp .hp-calc [data-fee-total] { grid-column: 2; grid-row: 1 / 3; align-self: center; justify-self: end; font-size: clamp(1.2rem, 0.6vw + 1.05rem, 1.6rem); font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.03em; line-height: 1.1; color: var(--hp-ink-2); }
        #hp #fees .hp-calc [data-fee-total="eventschedule"] { font-variation-settings: 'wght' 880; color: var(--hp-ink); }
        #hp .hp-calc div:has(> [data-fee-total]) > div:last-child { grid-column: 1 / -1; grid-row: 3; height: 0.85rem; margin-top: 0.45rem; overflow: visible; border-radius: 0.3rem; background: none; }
        #hp .hp-calc [data-fee-bar] { min-width: 0.5rem; border-radius: 0.3rem; background: rgba(86, 97, 124, 0.42); transition: width 0.35s cubic-bezier(0.22, 1, 0.36, 1), transform 1.1s cubic-bezier(0.22, 1, 0.36, 1); transform-origin: left; }
        [dir="rtl"] #hp .hp-calc [data-fee-bar] { transform-origin: right; }
        .dark #hp .hp-calc [data-fee-bar] { background: rgba(151, 163, 192, 0.45); }
        #hp #fees .hp-calc [data-fee-bar="eventschedule"] { background: linear-gradient(90deg, #2f66ea, #22d3ee); box-shadow: 0 0 18px rgba(34, 211, 238, 0.45); }
        {{-- The shared bar grows upward where it is a column of a chart; here it is a length. --}}
        #hp .hp-calc .es-bar { transform: scaleX(0.02); }
        #hp .hp-calc [data-reveal].is-revealed .es-bar, html:not(.es-anim) #hp .hp-calc .es-bar { transform: none; }
        @media (min-width: 768px) {
            #hp .hp-calc > div:nth-child(2) > div { grid-template-columns: minmax(0, 15rem) minmax(0, 1fr) minmax(6rem, auto); grid-template-rows: auto auto; column-gap: 1.75rem; padding: 1.1rem 0; }
            #hp .hp-calc > div:nth-child(2) > div > div:nth-of-type(1) { grid-row: 1; align-self: end; }
            #hp .hp-calc > div:nth-child(2) > div > div:nth-of-type(2) { grid-row: 2; align-self: start; }
            #hp .hp-calc > div:nth-child(2) > div > span { align-self: end; margin-bottom: 0.2rem; }
            #hp .hp-calc div:has(> [data-fee-total]) > div:last-child { grid-column: 2; grid-row: 1 / 3; height: 1.5rem; margin-top: 0; }
            #hp .hp-calc [data-fee-total] { grid-column: 3; grid-row: 1 / 3; }
        }
        @media (min-width: 1280px) {
            #hp .hp-calc > div:nth-child(2) > div { grid-template-columns: minmax(0, 17rem) minmax(0, 1fr) minmax(6rem, auto); }
        }
        {{-- The calculator's closing sentence is its answer: it stands under the bars with the
           button beside it, and the small print runs the width of the sheet under everything.
           Order and place only: the component's words are untouched. --}}
        #hp .hp-calc > div:last-child > p:first-child { margin: clamp(1.5rem, 3vw, 2.25rem) 0 0; font-size: clamp(1.15rem, 0.6vw + 1rem, 1.4rem); font-weight: 700; letter-spacing: -0.02em; text-align: start; color: var(--hp-ink); }
        #hp .hp-calc > div:last-child > a { justify-self: start; margin: 1.25rem 0 0; border-radius: 1rem; background: linear-gradient(100deg, #2b5fe3, #2f6fe9); }
        #hp .hp-calc > div:last-child > p:last-of-type { max-width: none; margin: clamp(1.5rem, 3vw, 2.25rem) 0 0; padding-top: 1.25rem; border-top: 1px solid var(--hp-line); font-size: 0.8rem; line-height: 1.6; text-align: start; text-wrap: pretty; color: var(--hp-ink-3); }
        @media (min-width: 900px) {
            .hp-after { grid-template-columns: minmax(0, 20rem) minmax(0, 1fr) auto; grid-template-areas: "slip cap cap" "slip rows rows" "slip say btn" "note note note"; column-gap: clamp(2rem, 4vw, 4rem); }
            .hp-slip-wrap { grid-area: slip; margin-bottom: 0; }
            .hp-after-cap { grid-area: cap; }
            #hp .hp-calc > div:nth-child(2) { grid-area: rows; }
            #hp .hp-calc > div:last-child > p:first-child { grid-area: say; align-self: center; }
            #hp .hp-calc > div:last-child > a { grid-area: btn; align-self: center; justify-self: end; margin-top: clamp(1.5rem, 3vw, 2.25rem); }
            #hp .hp-calc > div:last-child > p:last-of-type { grid-area: note; }
        }
        .hp-figs-links { display: flex; flex-direction: column; align-items: center; gap: 1.1rem; margin-top: 2.25rem; }
        .hp-figs-note { max-width: 40rem; margin: clamp(1.5rem, 3vw, 2.25rem) auto 0; font-size: 1.05rem; color: var(--hp-ink-2); text-align: center; text-wrap: balance; }
        .hp-figs-more { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.4rem 1.75rem; }

        {{-- ---------------------------------------------------------------
           7. Discover: the pinned rail of real events
           --------------------------------------------------------------- --}}

        .hp-discover { padding: 0.5rem clamp(0.5rem, 1vw, 1rem); }
        .hp-discover-head { margin-bottom: 2rem; padding-inline: 1.25rem; }
        .es-gallery-pin { padding-block: clamp(3.5rem, 7vw, 5.5rem); }
        .hp-discover-band { position: relative; border-radius: 2.5rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow), 0 0 0 1px var(--hp-line); }
        .hp-discover-foot { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; margin-top: 2rem; padding-inline: 1rem; color: var(--hp-ink-3); }
        .hp-discover-foot a.hp-plain { display: inline-flex; align-items: center; min-height: 1.75rem; font-size: 0.92rem; color: var(--hp-ink-3); text-decoration: underline; text-underline-offset: 0.2em; }
        .es-rail-clip { overflow-x: auto; -webkit-overflow-scrolling: touch; scroll-snap-type: x proximity; scrollbar-width: none; }
        .es-rail-clip::-webkit-scrollbar { display: none; }
        .es-rail { display: flex; width: max-content; will-change: transform; }
        .es-shot { scroll-snap-align: center; }
        .es-rail-progress-wrap { display: none; }
        {{-- Pinned only where the window is tall enough to hold the heading, the rail and the buttons
           under the site header: on a 720px laptop the pin used to cut its own heading off. --}}
        @media (min-width: 1024px) and (min-height: 700px) {
            html.es-anim .es-gallery { height: 240vh; }
            html.es-anim .es-gallery .es-gallery-pin {
                position: sticky;
                top: 4rem;
                height: calc(100vh - 4rem);
                padding-block: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                {{-- clip, where it exists: focusing a card must not scroll the band sideways. --}}
                overflow: hidden;
                overflow: clip;
            }
            html.es-anim .es-gallery .es-rail-clip { overflow: visible; }
            html.es-anim .es-gallery .es-rail-progress-wrap { display: block; }
        }
        #hp .es-shot .bg-gradient-to-t { background-image: linear-gradient(to top, rgba(0, 0, 0, 0.92) 0%, rgba(0, 0, 0, 0.74) 58%, rgba(0, 0, 0, 0) 100%); }
        {{-- Inside the pin everything is sized from the window's height, so the heading, the rail
           and the buttons always fit under the site header together. --}}
        @media (min-width: 1024px) and (min-height: 700px) {
            html.es-anim #hp .es-gallery .hp-discover-head { margin-bottom: 2.4vh; }
            html.es-anim #hp .es-gallery .hp-h2 { margin-top: 1.2vh; font-size: clamp(1.9rem, 5vh, 3.6rem); }
            html.es-anim #hp .es-gallery .hp-lead { margin-top: 1.2vh; font-size: clamp(1rem, 2vh, 1.3rem); }
            html.es-anim #hp .es-gallery .es-shot { width: clamp(15rem, 31vh, 20rem); }
            html.es-anim #hp .es-gallery .es-rail-progress-wrap { margin-top: 2.4vh; }
            html.es-anim #hp .es-gallery .hp-discover-foot { margin-top: 2vh; gap: 1vh; }
        }
        @media (min-width: 1024px) and (min-height: 700px) and (max-height: 839px) {
            html.es-anim #hp .es-gallery .hp-kicker,
            html.es-anim #hp .es-gallery .hp-lead { display: none; }
        }
        @media (min-width: 1024px) and (min-height: 700px) {
            html.es-anim #hp .es-gallery .es-shot { scale: var(--cs, 1); transition: scale 0.25s ease-out; }
        }
        .es-rail-progress { transform: scaleX(0); transform-origin: left; background: linear-gradient(90deg, #4E81FA, #0EA5E9, #22D3EE); }
        .hp-rail-end {
            position: relative;
            display: flex;
            width: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 2rem;
            border-radius: 1rem;
            background: radial-gradient(18rem 14rem at 50% 0%, rgba(47, 102, 234, 0.55), transparent 70%), #050814;
            color: #eef2ff;
            text-align: center;
        }
        .hp-rail-end strong { font-size: 1.6rem; font-weight: 700; font-variation-settings: 'wght' 840; letter-spacing: -0.03em; line-height: 1.1; }
        .hp-rail-end p { margin: 0.75rem 0 1.5rem; font-size: 0.92rem; color: #c5cde2; }

        {{-- ---------------------------------------------------------------
           8. Integrations: six names in a row
           --------------------------------------------------------------- --}}

        .hp-plug-grid { display: grid; grid-template-columns: minmax(0, 1fr); align-items: center; gap: clamp(2rem, 4vw, 3rem); }
        @media (min-width: 1024px) {
            .hp-plug-grid { grid-template-columns: minmax(0, 0.64fr) minmax(0, 1.36fr); gap: clamp(2rem, 3.4vw, 3.5rem); }
        }
        {{-- The wiring. Three columns from a tablet up: the calendars, the schedule, the money.
           Each name is a plug with a lead to a rail, and the rail meets the schedule at its
           middle; every line is a straight border, so the drawing holds at any width. On a
           phone the schedule stands first and the two groups under it, with no lines. --}}
        .hp-wire { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; }
        .hp-wire-cap { margin-bottom: 0.7rem; font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: 'wght' 600; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-ink); }
        .hp-wire-cap span { margin-inline-start: 0.5rem; color: var(--hp-ink-3); }
        {{-- On a phone there are no wires to carry the direction, so each caption carries its mark. --}}
        .hp-wire-cap svg { display: inline-block; width: 1.05rem; height: 1.05rem; margin-inline-end: 0.5rem; vertical-align: -0.22em; color: var(--hp-blue); }
        [dir="rtl"] #hp .hp-wire-cap svg { transform: scaleX(-1); }
        @media (max-width: 639px) {
            .hp-wire { gap: 0; }
            .hp-wire-side { position: relative; padding-top: 1.75rem; }
            .hp-wire-side::before { content: ""; position: absolute; top: 0; inset-inline-start: 0.48rem; height: 1.4rem; border-inline-start: 2px solid rgba(78, 129, 250, 0.5); }
            .hp-plug { gap: 0.55rem; padding: 0.6rem 0.7rem; font-size: 0.9rem; }
            .hp-plug-logo { width: 1.7rem; height: 1.7rem; }
        }
        .hp-plugs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.6rem; }
        .hp-plug {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            min-height: 4rem;
            padding: 0.7rem 1rem;
            border: 1px solid var(--hp-line);
            border-radius: 1rem;
            background: var(--hp-bg);
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.25;
            color: var(--hp-ink-2);
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }
        .dark .hp-plug { background: var(--hp-bg-2); }
        .hp-plug:hover { transform: translateY(-2px); border-color: var(--hp-blue); box-shadow: var(--hp-card-shadow); color: var(--hp-ink); }
        .hp-plug-logo { flex: none; width: 2rem; height: 2rem; }
        .hp-hub { order: -1; display: flex; flex-direction: column; align-items: center; gap: 0.3rem; padding: clamp(1.5rem, 3vw, 2.5rem) 1.25rem; border-radius: 1.5rem; background: radial-gradient(18rem 12rem at 50% -20%, rgba(78, 129, 250, 0.6), transparent 70%), #0a1020; color: #eef2ff; box-shadow: 0 0 0 1px rgba(125, 165, 255, 0.25), 0 30px 60px -28px rgba(47, 102, 234, 0.8); text-align: center; }
        .hp-hub .hp-avatar { width: 3.4rem; height: 3.4rem; margin-bottom: 0.5rem; border-radius: 1.05rem; font-size: 1.45rem; box-shadow: 0 0 34px rgba(34, 211, 238, 0.55); }
        .hp-hub strong { max-width: 100%; font-size: 1.15rem; letter-spacing: -0.02em; line-height: 1.2; overflow-wrap: anywhere; }
        {{-- The address breaks before its dot or not at all, never at a hyphen inside the name. --}}
        .hp-hub-url { max-width: 100%; overflow: hidden; font-family: var(--hp-mono); font-size: 0.68rem; line-height: 1.45; color: #9fb1d6; }
        .hp-hub-url [data-cast] { display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; vertical-align: bottom; }
        .hp-hub-row { display: flex; align-items: center; gap: 0.6rem; width: 100%; max-width: 15rem; margin-top: 0.95rem; padding: 0.5rem 0.6rem; border: 1px solid rgba(125, 165, 255, 0.26); border-radius: 0.85rem; background: rgba(255, 255, 255, 0.06); text-align: start; }
        .hp-hub-row .hp-tile { width: 2.5rem; height: 2.6rem; background: rgba(125, 165, 255, 0.2); }
        .hp-hub-row .hp-tile b { color: #a9c3ff; }
        .hp-hub-row .hp-tile i { font-size: 1.05rem; }
        .hp-hub-row > span:last-child { min-width: 0; }
        .hp-hub-row > span:last-child b { display: block; overflow: hidden; font-size: 0.88rem; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
        .hp-hub-row > span:last-child i { display: block; font-style: normal; font-size: 0.72rem; color: #9fb1d6; }
        .hp-wire-mark { display: none; }
        @media (min-width: 640px) {
            .hp-wire { --lead: clamp(1.1rem, 2.4vw, 1.9rem); grid-template-columns: minmax(0, 1fr) minmax(12.5rem, 0.95fr) minmax(0, 1fr); align-items: center; column-gap: calc(var(--lead) * 2); }
            .hp-wire-side { position: relative; }
            .hp-wire-cap { position: absolute; bottom: calc(100% + 0.2rem); inset-inline: 0 auto; margin: 0; white-space: nowrap; }
            .hp-wire-cap svg { display: none; }
            {{-- Hung from the far edge, so a caption longer than its column grows toward the middle. --}}
            .hp-wire-side.is-out .hp-wire-cap { inset-inline: auto 0; text-align: end; }
            .hp-plugs { position: relative; grid-template-columns: minmax(0, 1fr); }
            .hp-plugs li { position: relative; }
            .hp-hub { order: 0; position: relative; }
            {{-- A lead from each plug to the rail, the rail, and the rail's own lead to the schedule. --}}
            .hp-plugs li::after { content: ""; position: absolute; top: 50%; width: var(--lead); border-top: 2px solid rgba(78, 129, 250, 0.5); }
            .hp-wire-side.is-in .hp-plugs li::after { inset-inline-start: 100%; }
            .hp-wire-side.is-out .hp-plugs li::after { inset-inline-end: 100%; }
            .hp-plugs::after { content: ""; position: absolute; top: 2rem; bottom: 2rem; border-inline-start: 2px solid rgba(78, 129, 250, 0.5); }
            .hp-wire-side.is-in .hp-plugs::after { inset-inline-start: calc(100% + var(--lead) - 2px); }
            .hp-wire-side.is-out .hp-plugs::after { inset-inline-end: calc(100% + var(--lead)); }
            .hp-hub::before,
            .hp-hub::after { content: ""; position: absolute; top: 50%; width: var(--lead); height: 2px; background: linear-gradient(90deg, #4e81fa, #22d3ee); }
            .hp-hub::before { inset-inline-end: 100%; }
            .hp-hub::after { inset-inline-start: 100%; }
            {{-- Which way each wire carries: both ways on the calendars' side, one way on the money's. --}}
            .hp-wire-mark { position: absolute; top: 50%; z-index: 1; display: grid; place-items: center; width: 1.7rem; height: 1.7rem; border: 2px solid #4e81fa; border-radius: 999px; background: var(--hp-bg-2); color: var(--hp-blue); transform: translateY(-50%); }
            .dark .hp-wire-mark { background: var(--hp-bg-3); }
            .hp-wire-mark svg { width: 0.95rem; height: 0.95rem; }
            .hp-wire-mark.is-in { inset-inline-end: calc(100% + var(--lead) / 2 - 0.85rem); }
            .hp-wire-mark.is-out { inset-inline-start: calc(100% + var(--lead) / 2 - 0.85rem); border-color: #22d3ee; }
            [dir="rtl"] #hp .hp-wire-mark.is-out svg { transform: scaleX(-1); }
        }
        @media (min-width: 640px) and (max-width: 1179px) {
            {{-- Three columns in little room: smaller plugs, and a caption on two lines. --}}
            .hp-plug { gap: 0.5rem; min-height: 3.5rem; padding: 0.55rem 0.65rem; font-size: 0.82rem; }
            .hp-plug-logo { width: 1.6rem; height: 1.6rem; }
            .hp-wire-cap span { display: block; margin-inline-start: 0; }
            .hp-wire { grid-template-columns: minmax(0, 1fr) minmax(11rem, 0.9fr) minmax(0, 1fr); }
        }

        {{-- ---------------------------------------------------------------
           9. Questions
           --------------------------------------------------------------- --}}

        .hp-faq-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(2rem, 4vw, 4rem); }
        @media (min-width: 1024px) {
            .hp-faq-grid { grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.2fr); align-items: start; }
            .hp-faq-grid .hp-head { position: sticky; top: 7rem; }
        }
        .hp-faq { border-bottom: 1px solid var(--hp-line-2); }
        .hp-faq:first-child { border-top: 1px solid var(--hp-line-2); }
        .hp-faq summary { display: flex; cursor: pointer; align-items: center; justify-content: space-between; gap: 1.5rem; padding: 1.35rem 0.25rem; }
        .hp-faq h3 { font-size: clamp(1.1rem, 0.5vw + 1rem, 1.3rem); font-weight: 700; letter-spacing: -0.02em; line-height: 1.3; transition: color 0.2s ease; }
        .hp-faq summary:hover h3 { color: var(--hp-blue); }
        .hp-faq summary i { position: relative; flex: none; width: 2rem; height: 2rem; border: 1px solid var(--hp-line-2); border-radius: 999px; transition: transform 0.3s ease, background-color 0.2s ease, border-color 0.2s ease; }
        .hp-faq summary i::before,
        .hp-faq summary i::after { content: ""; position: absolute; top: 50%; left: 50%; width: 0.7rem; height: 2px; margin: -1px 0 0 -0.35rem; border-radius: 2px; background: currentColor; }
        .hp-faq summary i::after { transform: rotate(90deg); transition: transform 0.3s ease; }
        .hp-faq[open] summary i { background: var(--hp-ink); border-color: var(--hp-ink); color: var(--hp-bg); transform: rotate(180deg); }
        .hp-faq[open] summary i::after { transform: rotate(0deg); }
        .hp-faq .faq-answer { max-width: 44rem; padding: 0 0.25rem 1.5rem; color: var(--hp-ink-2); }

        {{-- ---------------------------------------------------------------
           10. Finale: claim your name (dark in both modes, literal colours)
           --------------------------------------------------------------- --}}

        .hp-finale {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            padding: clamp(3.5rem, 8vw, 7rem) clamp(1.25rem, 5vw, 4rem);
            border-radius: 2.5rem;
            background:
                radial-gradient(52rem 30rem at 50% -6rem, rgba(47, 102, 234, 0.62), transparent 70%),
                radial-gradient(30rem 20rem at 8% 100%, rgba(34, 211, 238, 0.2), transparent 70%),
                radial-gradient(30rem 20rem at 92% 100%, rgba(14, 165, 233, 0.2), transparent 70%),
                #050814;
            color: #eef2ff;
            text-align: center;
            box-shadow: 0 40px 100px -40px rgba(47, 102, 234, 0.7);
        }
        .hp-finale::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: calc(100% / 7) 6rem;
            -webkit-mask-image: radial-gradient(ellipse at 50% 0%, #000, transparent 72%);
            mask-image: radial-gradient(ellipse at 50% 0%, #000, transparent 72%);
        }
        .hp-finale { display: grid; grid-template-columns: minmax(0, 1fr); align-items: center; gap: clamp(2rem, 5vw, 4.5rem); }
        .hp-finale-poster { position: relative; display: grid; justify-items: center; }
        .hp-finale-poster .hp-mine { cursor: text; box-shadow: 0 30px 70px -20px rgba(0, 0, 0, 0.9); }
        #hp .hp-finale-poster .hp-mine:not(.is-named) { border-color: rgba(159, 177, 214, 0.6); background: #0d1430; color: #9fb1d6; box-shadow: 0 30px 70px -20px rgba(0, 0, 0, 0.9); }
        @media (max-width: 899px) {
            {{-- On a phone an empty poster would be a whole screen before the headline, and the wall
               behind it would sit under the words. The poster arrives once it has a name. --}}
            #hp .hp-finale-wall,
            #hp .hp-finale-poster:not(:has(.is-named)) { display: none; }
            .hp-finale-poster .hp-mine.is-large { --size: 2.25rem; width: 13rem; }
        }
        {{-- The wall behind it: the same posters as the hero, standing back in the dark. --}}
        .hp-finale-wall {
            position: absolute;
            inset: -12% auto -12% -2%;
            z-index: -1;
            display: grid;
            grid-template-columns: repeat(4, 8.5rem);
            gap: 0.9rem;
            align-content: center;
            opacity: 0.2;
            transform: rotate(-6deg);
            -webkit-mask-image: radial-gradient(ellipse 70% 60% at 40% 50%, #000 30%, transparent 100%);
            mask-image: radial-gradient(ellipse 70% 60% at 40% 50%, #000 30%, transparent 100%);
            pointer-events: none;
        }
        .hp-finale-wall img { display: block; width: 100%; aspect-ratio: 3 / 4; object-fit: cover; border-radius: 0.6rem; filter: grayscale(0.4); }
        .hp-finale .hp-h2 { max-width: 16em; margin-inline: auto; font-size: clamp(2.3rem, 3.4vw + 0.8rem, 4.2rem); }
        .hp-finale .hp-ink-grad { background-image: linear-gradient(100deg, #7da5ff 0%, #38bdf8 55%, #67e8f9 100%); }
        .hp-finale .hp-lead { max-width: 40rem; margin-top: 1.4rem; margin-inline: auto; color: #c5cde2; }
        #hp .hp-finale .hp-claim { border-color: rgba(125, 165, 255, 0.4); background: #101831; box-shadow: none; }
        #hp .hp-finale .hp-claim input { color: #fff; }
        #hp .hp-finale .hp-claim input::placeholder,
        .hp-finale .hp-claim-suffix { color: #b9c6e6; }
        .hp-finale .hp-btn-primary { background: linear-gradient(100deg, #3a6df0, #1f8fe0); }
        .hp-finale-foot { margin-top: 1.4rem; font-size: 0.92rem; color: #b9c6e6; }
        .dark .hp-finale { box-shadow: 0 0 0 1px rgba(125, 165, 255, 0.22), 0 40px 100px -40px rgba(47, 102, 234, 0.7); }
        @media (min-width: 900px) {
            .hp-finale { grid-template-columns: minmax(0, 0.62fr) minmax(0, 1fr); text-align: start; }
            #hp .hp-finale .hp-claimrow,
            #hp .hp-finale .hp-h2,
            #hp .hp-finale .hp-lead { margin-inline: 0; }
        }

        {{-- ---------------------------------------------------------------
           Motion off
           --------------------------------------------------------------- --}}

        @media (prefers-reduced-motion: reduce) {
            {{-- marketing.css lays a still marquee out as wrapped rows; the copy that only exists to
               make the loop seamless would then show everything twice, and the phone's poster
               strip would stand 2,400px tall between the claim box and the film. --}}
            #hp .es-marquee-track > [data-loop-copy] { display: none; }
            #hp .hp-strip .es-marquee { overflow-x: auto; scrollbar-width: none; }
            #hp .hp-strip .es-marquee-track { width: max-content; flex-wrap: nowrap; justify-content: flex-start; }
            .es-frame { transition: none; }
            .es-wall-track,
            .hp-live::before,
            .hp-synced i,
            .hp-scan,
            .hp-beams,
            .hp-wing-l,
            .hp-wing-r,
            .hp-mine-plane,
            .hp-crowd i {
                animation: none !important;
            }
            .hp-mine { transition: none; }
            #hp .hp-calc [data-fee-bar] { transition: none; }
            .hp-mine-phone .hp-mine.is-named { animation: none; }
            #hp .es-wall-card img { transition: none; }
            .hp-btn,
            .hp-btn svg,
            .hp-plug,
            .hp-bill-desc,
            #hp .es-wall-card { transition: none; }
        }
    </style>

    <x-slot name="structuredData">
    {{-- The product node ({site}/#software) is emitted once by the layout for every marketing page:
         SeoUtils::softwareApplication(). --}}
    <x-seo.howto-schema
        name="How to share your event schedule"
        description="Get your event schedule live and shared with your audience in three simple steps."
        :steps="[
            ['name' => 'Create your schedule', 'text' => 'Sign up free, then fill it however suits you: type an event in, connect a calendar, or paste a poster and let the AI read the details off it.'],
            ['name' => 'Share your link', 'text' => 'You get yourname.eventschedule.com. Put it in your bio, print the QR code on a poster, embed the calendar in your own site, or let guests subscribe to it from their own calendar app.'],
            ['name' => 'Grow your audience', 'text' => 'Visitors leave an email address and get a digest automatically when you publish new events. Write a newsletter yourself whenever there is more to say.'],
        ]"
    />
    {{-- FAQ JSON-LD is emitted alongside the visible FAQ section near the end of the page, driven by one $homeFaqs array so the markup always matches the rendered content. --}}
    {{-- Structured data: list the publicly visible events in the rail --}}
    @php
        $eventListItems = [];
        $eventListPos = 1;
        foreach ($discoverEvents as $discoverEvent) {
            $eventItemUrl = $discoverEvent->getGuestUrl();
            if (! $eventItemUrl) {
                continue;
            }
            $eventListItems[] = [
                '@type' => 'ListItem',
                'position' => $eventListPos++,
                'url' => $eventItemUrl,
                'name' => $discoverEvent->name,
            ];
        }
    @endphp
    @if (count($eventListItems))
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! \App\Utils\SeoUtils::jsonLd([
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => 'Upcoming events on Event Schedule',
        'url' => url('/'),
        'itemListElement' => $eventListItems,
    ]) !!}
    </script>
    @endif
    </x-slot>

    @php
        // Icons, by name. Kept as path data so a card can be described in one line below.
        $hpIcon = [
            'arrow' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />',
            'check' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />',
            'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
            'pin' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />',
            'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />',
            'mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
            'bulb' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />',
            'horn' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />',
            'chart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
            'lock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />',
            'link' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />',
            'qr' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z" />',
            'code' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />',
            'swap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />',
        ];
        $hpArrow = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">' . $hpIcon['arrow'] . '</svg>';
        $hpCheck = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">' . $hpIcon['check'] . '</svg>';
        $hpQr = 'M0 0h9v9H0V0zm2 2v5h5V2H2zm1 1h3v3H3V3zm17-3h9v9h-9V0zm2 2v5h5V2h-5zm1 1h3v3h-3V3zM0 20h9v9H0v-9zm2 2v5h5v-5H2zm1 1h3v3H3v-3zM12 0h2v2h-2V0zm3 0h2v4h-2V0zm-3 4h2v3h-2V4zm3 3h4v2h-4V7zm-3 3h3v2h-3v-2zm5 0h2v3h-2v-3zm7 1h2v2h-2v-2zm3-1h2v4h-2v-4zM0 12h2v2H0v-2zm3 0h4v2H3v-2zm5 1h2v4H8v-4zm3 3h2v2h-2v-2zm3-2h3v2h-3v-2zm5 1h2v3h-2v-3zm3 1h4v2h-4v-2zm5 1h2v2h-2v-2zm-15 4h4v2h-4v-2zm5 1h2v2h-2v-2zm3-2h2v4h-2v-4zm3 2h4v2h-4v-2zm-7 3h2v4h-2v-4zm-3 1h2v3h-2v-3zm8 0h3v2h-3v-2zm5-1h2v4h-2v-4z';

        // The show is always on the Saturday of the week after next, and the appointment on the
        // Tuesday before it: the show is never in the past, and a weekday can never disagree
        // with its date (the mock-ups used to carry "Tue, Jul 15" beside "Sat, Jul 18", two
        // different years).
        $wkMon = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->addWeeks(2);
        $wkTue = $wkMon->copy()->addDay();
        $wkSat = $wkMon->copy()->addDays(5);
        $wkLater = [$wkSat->copy()->addDays(6), $wkSat->copy()->addDays(14)];
        // The booking window's month, Monday first, around the Tuesday being booked. Open hours
        // fall on Tuesdays and Thursdays.
        $bookCells = [];
        $bookDay = $wkTue->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
        $bookLast = $wkTue->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $bookToday = \Carbon\Carbon::today();
        while ($bookDay->lte($bookLast)) {
            // Outside the month, or already gone: shown faint, and never as a day with times.
            $bookFaint = $bookDay->month !== $wkTue->month || $bookDay->lt($bookToday);
            $bookCells[] = [
                'day' => $bookDay->day,
                'class' => $bookFaint ? 'is-off' : ($bookDay->isSameDay($wkTue) ? 'is-pick' : (in_array($bookDay->dayOfWeekIso, [2, 4], true) ? 'is-open' : '')),
            ];
            $bookDay->addDay();
        }

        // Whose show the page tells. The jazz club is what the server renders; the switch above
        // the three acts re-casts every mock-up from this list in the browser. Names, times and
        // places only: the prices, the counts and every sentence about the product stay as they
        // are. The show the calculator opens on is the one the acts are about.
        $hpShowTickets = 150;
        $hpShowPrice = 25;
        // A ticket's price in the dollar calculator, which is in dollars on every install (see
        // the component's header), not one of our own prices: those go through plan_price().
        $hpShowPriceText = '$'.$hpShowPrice;
        $hpShowGrossText = '$'.number_format($hpShowTickets * $hpShowPrice);
        $hpFeePlatforms = ['eventschedule', 'eventbrite', 'luma', 'ticketleap'];

        $hpCasts = [
            'jazz' => ['label' => 'a jazz club', 'initial' => 'B', 'venue' => 'The Blue Note', 'slug' => 'blue-note', 'tagline' => 'Live jazz, five nights a week', 'event' => 'Jazz Night', 'line1' => 'Jazz', 'line2' => 'Night', 'tag' => 'Live', 'tier2' => 'VIP', 'tier2note' => 'Early entry', 'addon' => 'Parking', 'verb' => 'Doors', 'time' => '8:00 PM', 'next1' => 'Open Mic', 'next1time' => '7:30 PM', 'next2' => 'Blues & Brews', 'next2time' => '9:00 PM', 'appt' => 'Private hire viewing', 'img' => asset('images/demo/demo_flyer_jazz.webp')],
            'comedy' => ['label' => 'a comedy club', 'initial' => 'C', 'venue' => 'The Cellar Club', 'slug' => 'cellar-club', 'tagline' => 'Stand-up every Friday and Saturday', 'event' => 'Late Laughs', 'line1' => 'Late', 'line2' => 'Laughs', 'tag' => 'Live', 'tier2' => 'Front table', 'tier2note' => 'Seats four', 'addon' => 'Parking', 'verb' => 'Doors', 'time' => '9:30 PM', 'next1' => 'New Material Night', 'next1time' => '8:00 PM', 'next2' => 'Improv Jam', 'next2time' => '9:00 PM', 'appt' => 'Open-mic audition', 'img' => asset('images/demo/demo_flyer_comedy.webp')],
            'yoga' => ['label' => 'a yoga studio', 'initial' => 'S', 'venue' => 'Stillpoint Yoga', 'slug' => 'stillpoint', 'tagline' => 'Classes from sunrise to sundown', 'event' => 'Night Flow', 'line1' => 'Night', 'line2' => 'Flow', 'tag' => 'Candlelit', 'tier2' => 'Mat included', 'tier2note' => 'Bring nothing', 'addon' => 'Mat hire', 'verb' => 'Doors', 'time' => '7:00 PM', 'next1' => 'Community Class', 'next1time' => '6:00 PM', 'next2' => 'Sound Bath', 'next2time' => '8:00 PM', 'appt' => 'Private session', 'img' => null],
            'festival' => ['label' => 'a street festival', 'initial' => 'R', 'venue' => 'Riverside Fest', 'slug' => 'riverside-fest', 'tagline' => 'Music, food and colour by the river', 'event' => 'Summer Fest', 'line1' => 'Summer', 'line2' => 'Fest', 'tag' => 'Open air', 'tier2' => 'VIP', 'tier2note' => 'Front of stage', 'addon' => 'Parking', 'verb' => 'Gates', 'time' => '6:00 PM', 'next1' => 'Family Day', 'next1time' => '11:00 AM', 'next2' => 'Closing Party', 'next2time' => '9:00 PM', 'appt' => 'Stallholder call', 'img' => asset('images/demo/demo_flyer_special.webp')],
        ];

        // What follows the name in the claim box: this install's own domain. A white-label
        // install is not eventschedule.com, and the box must not promise an address on ours.
        // _base_domain() is what the schedule routes themselves are registered on.
        $claimHost = _base_domain();
        $claimSuffix = '.' . (str_contains($claimHost, '.') && ! filter_var($claimHost, FILTER_VALIDATE_IP) ? $claimHost : 'eventschedule.com');
    @endphp

    <div id="hp">

    <!-- ============================================================ -->
    <!-- 1. Hero: the headline between two walls of real posters      -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero hp-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <span class="hp-live" aria-hidden="true"><i></i></span>
                    <span>Free event calendar. No credit card.</span>
                </x-marketing.hero-eyebrow>
                {{-- 24 characters is the budget per line: .es-mask animates the whole line block,
                     so a line that wraps rises as a two-line slab instead of a crisp single line.
                     From a laptop up the type is sized from the window so that a 24-character
                     line always fits between the two wings of the wall. --}}
                <span class="es-mask"><span class="es-mask-line" data-hero="l1">{{ $hero['default']['line1'] }}</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient es-gradient-anim" data-hero="l2">{{ $hero['default']['line2'] }}</span></span></span>
            </h1>

            {{-- The headline and this subtitle are an A/B test that runs itself: the copy for every
                 variant, and the reasoning behind each constraint on it, lives in
                 App\Utils\HeroExperiment. The server renders the default (or the winner, once one is
                 locked); the script below picks a variant in the browser, because this page is
                 edge-cached on its URL alone and a server-side pick would be served to everyone
                 (docs/CACHING.md).

                 The rules every variant must keep, which HeroExperimentTest checks:
                 - 24 characters per headline line (see the note above the h1).
                 - "event calendar" somewhere in the fold: the <title> says "Free Event Calendar",
                   and an H1 + subhead that never confirm it is the usual trigger for Google
                   rewriting the title.
                 - It says the product takes bookings, and names no paid plan (MarketingHeroClaimTest). --}}
            <p class="es-fade-up es-d-2 hp-sub" data-hero="sub">{{ $hero['default']['subtitle'] }}</p>

            @guest
                @if ($hero['running'])
                    {{-- Runs before the entrance animations reveal the text, so the swap never
                         shows. With analytics consent the pick is remembered in sessionStorage
                         (es_hero, es_hero_clicked) so a visitor sees one headline and is counted
                         once per session; without it nothing is stored, the headline is picked
                         afresh on each view, and a view and a click are counted per page view.

                         The pick reaches sign-up on the app host two ways. It rides the link:
                         ?hero=<key> is added to a sign-up or sign-in link as it is pressed, and
                         CaptureUtmParameters keeps it in the session, so nothing is stored in the
                         browser and no consent is needed. And it is copied into the
                         es_attribution cookie by the layout's attribution script (which reads
                         window.esHero, and writes the cookie only with marketing consent), which
                         covers a visitor who signs up from another page. Writing that cookie from
                         here instead would make the attribution script think it already ran and
                         lose the landing page and utm_* values.

                         tag() is what puts the pick on the link:
                         - Only the two app URLs in `targets`, compared whole. A substring match
                           on /login would also tag a showcase card for a schedule called
                           "loginlounge", and send its page a query string it has no use for.
                         - Sign-in as well as sign-up: a social sign-in from there creates
                           accounts.
                         - As the link is pressed, not once on load: the links below this script
                           do not exist yet, and initClaim() (marketing-home.js) rebuilds the
                           claim button's href from its original each time the visitor types.
                         - On both events: pointerdown covers a middle click and a long press,
                           click covers the keyboard and the claim box's Enter, and a phone
                           keyboard can commit its text between the two.
                         - In its own listeners: the click-beacon listener stops acting after
                           the first click.
                         These notes are up here so they are not sent to every visitor. --}}
                    <script {!! nonce_attr() !!}>
                        (function () {
                            try {
                                var variants = @json($hero['variants']);
                                var weights = @json($hero['weights']);
                                var endpoint = @json(url('/marketing/hero'));
                                var targets = [@json(app_url('/sign_up')), @json(app_url('/login'))];
                                var param = @json(\App\Utils\HeroExperiment::LINK_PARAMETER);
                                var key = null;
                                var fresh = false;
                                var remember = !!(window.esConsent && window.esConsent.has('analytics'));
                                var clicked = false;

                                var match = document.cookie.match(/(?:^|;\s*)es_attribution=([^;]*)/);
                                if (match) {
                                    try {
                                        key = JSON.parse(decodeURIComponent(match[1])).hero || null;
                                    } catch (e) {}
                                }
                                if ((!key || !variants[key]) && remember) {
                                    try {
                                        key = sessionStorage.getItem('es_hero');
                                    } catch (e) {}
                                }
                                if (!key || !variants[key]) {
                                    var roll = Math.random();
                                    var keys = Object.keys(weights);
                                    key = keys[keys.length - 1];
                                    for (var i = 0; i < keys.length; i++) {
                                        roll -= weights[keys[i]];
                                        if (roll < 0) {
                                            key = keys[i];
                                            break;
                                        }
                                    }
                                    fresh = true;
                                }
                                if (remember) {
                                    try {
                                        sessionStorage.setItem('es_hero', key);
                                    } catch (e) {}
                                }

                                window.esHero = { key: key, fresh: fresh };

                                var copy = variants[key];
                                document.querySelector('[data-hero="l1"]').textContent = copy.line1;
                                document.querySelector('[data-hero="l2"]').textContent = copy.line2;
                                document.querySelector('[data-hero="sub"]').textContent = copy.subtitle;

                                var send = function (event) {
                                    var body = JSON.stringify({ variant: key, event: event });
                                    try {
                                        if (navigator.sendBeacon && navigator.sendBeacon(endpoint, new Blob([body], { type: 'application/json' }))) {
                                            return;
                                        }
                                    } catch (e) {}
                                    try {
                                        fetch(endpoint, { method: 'POST', keepalive: true, credentials: 'same-origin', headers: { 'Content-Type': 'application/json' }, body: body }).catch(function () {});
                                    } catch (e) {}
                                };

                                if (fresh) {
                                    send('view');
                                }

                                var tag = function (e) {
                                    try {
                                        var link = e.target && e.target.closest ? e.target.closest('a[href]') : null;
                                        if (!link) {
                                            return;
                                        }
                                        var url = new URL(link.getAttribute('href'), location.href);
                                        if (targets.indexOf(url.origin + url.pathname) !== -1 && url.searchParams.get(param) !== key) {
                                            url.searchParams.set(param, key);
                                            link.setAttribute('href', url.toString());
                                        }
                                    } catch (e) {}
                                };
                                document.addEventListener('pointerdown', tag, true);
                                document.addEventListener('click', tag, true);

                                // Any sign-up link on the page, not only the hero button: the
                                // headline is what is being tested, wherever the reader acts on it.
                                // First click per session with consent, so the rate is per
                                // visitor; per page view without it, matching how views count.
                                document.addEventListener('click', function (e) {
                                    var link = e.target && e.target.closest ? e.target.closest('a[href*="/sign_up"]') : null;
                                    if (!link || clicked) {
                                        return;
                                    }
                                    if (remember) {
                                        try {
                                            if (sessionStorage.getItem('es_hero_clicked') === key) {
                                                return;
                                            }
                                            sessionStorage.setItem('es_hero_clicked', key);
                                        } catch (e) {}
                                    }
                                    clicked = true;
                                    send('click');
                                }, true);
                            } catch (e) {}
                        })();
                    </script>
                @endif
            @endguest

            {{-- The name box that used to wait at the foot of the page. A name typed here rides
                 into sign-up (?schedule=, see initClaim() in marketing-home.js), and the same
                 name shows in the box in the finale. --}}
            <div class="es-fade-up es-d-3 hp-claimrow">
                <label for="es-claim-hero" class="sr-only">Your schedule name</label>
                <div dir="ltr" class="es-claim hp-claim">
                    <input id="es-claim-hero" type="text" placeholder="your-name" autocomplete="off" autocapitalize="none" spellcheck="false" maxlength="30">
                    <span class="hp-claim-suffix">{{ $claimSuffix }}</span>
                </div>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                    Claim it free
                    {!! $hpArrow !!}
                </a>
            </div>

            {{-- No capability chip row here. A row of four named features under the CTAs turns the
                 fold back into a table of contents, and two of the four (passes and gift cards on
                 Pro, reserved seating on Enterprise) are expansion features that belong to a reader
                 who has already decided and is scrolling. Both are still on this page, in the
                 ticketing feature and the "everything else" grid. --}}
            <p class="es-fade-up es-d-4 hp-hero-foot">
                <a href="#showcase" class="hp-demo" data-video-open>
                    <i aria-hidden="true"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></i>
                    Watch the 3-minute overview
                </a>
                <span>Set up in under 2 minutes. Open source, and yours to selfhost.</span>
            </p>

            {{-- Below a laptop there are no walls beside the headline, so the visitor's poster
                 arrives here instead, only once a name has been typed. --}}
            <div class="hp-mine-phone">
                <div class="hp-mine" data-claim-poster aria-hidden="true">
                    <span class="hp-mine-top"><i>On the wall</i><i>This week</i></span>
                    <strong data-claim-name="title">Your name</strong>
                    <span class="hp-mine-foot" data-claim-name="url">your-name{{ $claimSuffix }}</span>
                </div>
            </div>

            {{-- Compact clickable poster strip (mobile and tablets) --}}
            <div class="es-fade-up es-d-5 hp-strip lg:hidden">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($stripCopy = 0; $stripCopy < 2; $stripCopy++)
                                @foreach ($wallCards as $stripCard)
                                    @php $stripEager = $stripCopy === 0 && $stripCard['eager']; @endphp
                                    @if ($stripCard['url'])
                                        <a href="{{ $stripCard['url'] }}" target="_blank" rel="noopener" @if ($stripCopy === 1) aria-hidden="true" tabindex="-1" data-loop-copy @endif aria-label="{{ $stripCard['name'] }}">
                                            <img src="{{ $stripCard['img'] }}" alt="" width="96" height="128" loading="{{ $stripEager ? 'eager' : 'lazy' }}" decoding="async" fetchpriority="{{ $stripEager ? ($stripCard['priority'] ? 'high' : 'auto') : 'low' }}">
                                        </a>
                                    @else
                                        <div class="hp-strip-card" aria-hidden="true" @if ($stripCopy === 1) data-loop-copy @endif>
                                            <img src="{{ $stripCard['img'] }}" alt="" width="96" height="128" loading="{{ $stripEager ? 'eager' : 'lazy' }}" decoding="async" fetchpriority="{{ $stripEager ? ($stripCard['priority'] ? 'high' : 'auto') : 'low' }}">
                                        </div>
                                    @endif
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- The screen. It stands inside the hero so the two walls run on beside it, and it comes
             before the wall in the document. --}}
        <div id="showcase" class="hp-stage">
            <div class="es-persp relative" data-scene="showcase">
                    <div class="es-frame relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/15 dark:border-white/10 dark:bg-[#101016] dark:shadow-black/50">
                        <!-- Browser chrome -->
                        <div class="flex items-center gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <span class="flex w-14 gap-1.5" aria-hidden="true">
                                <span class="h-3 w-3 rounded-full bg-[#FF5F57]"></span>
                                <span class="h-3 w-3 rounded-full bg-[#FEBC2E]"></span>
                                <span class="h-3 w-3 rounded-full bg-[#28C840]"></span>
                            </span>
                            <span class="mx-auto flex items-center gap-1.5 rounded-lg bg-white px-4 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-slate-200 dark:bg-white/10 dark:text-gray-300 dark:ring-0" aria-hidden="true">
                                <svg aria-hidden="true" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                <span data-claim-echo>{{ ltrim($claimSuffix, '.') }}</span>
                            </span>
                            {{-- An auto-looping reel needs a way to stop it (WCAG 2.2.2). It sits up here,
                                 outside the facade link that covers the whole picture, and stays hidden
                                 until initShowreel() wires it, so it never shows as a dead control. --}}
                            <span class="flex w-14 justify-end">
                                <button type="button" data-showreel-toggle
                                        data-label-pause="Pause the showreel" data-label-play="Play the showreel"
                                        aria-label="Pause the showreel"
                                        class="hidden h-8 w-8 items-center justify-center rounded-lg text-gray-500 transition-all duration-200 hover:bg-slate-200/70 hover:text-gray-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white">
                                    <svg data-icon="pause" aria-hidden="true" class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="5" width="4" height="14" rx="1.2"/><rect x="14" y="5" width="4" height="14" rx="1.2"/></svg>
                                    <svg data-icon="play" aria-hidden="true" class="hidden h-3.5 w-3.5 ltr:ml-0.5 rtl:mr-0.5 rtl:rotate-180" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </button>
                            </span>
                        </div>
                    {{-- The showreel loops muted while the frame is on screen (see initShowreel() in
                         marketing-home.js); clicking anywhere swaps in the 3-minute YouTube overview.
                         Source: resources/promo/showreel. It comes in a dark and a light cut on one
                         timeline, stacked, and the dark: classes show the cut matching the theme, so
                         a theme switch mid-play carries on from the same moment. Phones get the 540p
                         cut through the media source.
                         The posters are lazy <img>s under the reels, not poster attributes: a browser
                         fetches every <video poster> on page load, even an invisible one, whereas a
                         lazy image waits until the frame is near and is never fetched while
                         display:none, so a visitor loads only their own theme's poster, and only if
                         they scroll this far.
                         Bump $showreelV whenever the reel is re-rendered: the files keep their names,
                         so the query string is the only thing that busts a cached copy.
                         The ground behind the reels is the page's own (--hp-bg), which is the
                         film's in both cuts, so nothing flashes before a poster arrives. --}}
                    @php
                        $showreelV = '2026-10-08';
                        $showreelCuts = ['light' => 'event-schedule-showreel-light', 'dark' => 'event-schedule-showreel'];
                    @endphp
                    <div class="relative aspect-video" style="background: var(--hp-bg);">
                        @foreach ($showreelCuts as $cut => $reel)
                            <img data-showreel-poster="{{ $cut }}" src="{{ asset('videos/' . $reel . '-poster.jpg') }}?v={{ $showreelV }}" alt=""
                                 width="1920" height="1080" loading="lazy" decoding="async"
                                 class="absolute inset-0 h-full w-full object-cover {{ $cut === 'light' ? 'dark:hidden' : 'hidden dark:block' }}">
                        @endforeach
                        @foreach ($showreelCuts as $cut => $reel)
                            <video data-showreel="{{ $cut }}" class="absolute inset-0 h-full w-full object-cover transition-opacity duration-300 {{ $cut === 'light' ? 'dark:opacity-0' : 'opacity-0 dark:opacity-100' }}" muted loop playsinline preload="none"
                                   width="1920" height="1080" aria-hidden="true" tabindex="-1">
                                <source src="{{ asset('videos/' . $reel . '-mobile.mp4') }}?v={{ $showreelV }}" type="video/mp4" media="(max-width: 767px)">
                                <source src="{{ asset('videos/' . $reel . '.mp4') }}?v={{ $showreelV }}" type="video/mp4">
                                <source src="{{ asset('videos/' . $reel . '.webm') }}?v={{ $showreelV }}" type="video/webm">
                            </video>
                        @endforeach
                        <a href="https://www.youtube-nocookie.com/embed/w1JLIvGmIjQ"
                           target="_blank"
                           rel="noopener"
                           data-video-facade
                           data-video-src="https://www.youtube-nocookie.com/embed/w1JLIvGmIjQ"
                           data-video-title="Event Schedule Overview"
                           class="group absolute inset-0 block transition-colors duration-200 hover:bg-black/10 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-[#0284C7] dark:focus-visible:ring-[#22D3EE]"
                           aria-label="Play the 3-minute Event Schedule overview video">
                            <span class="absolute inset-x-0 bottom-0 h-1/4 bg-gradient-to-t from-slate-900/15 to-transparent dark:from-black/35" aria-hidden="true"></span>
                            {{-- Compact on phones, where the full label covered most of the picture. --}}
                            <span class="absolute bottom-2 left-1/2 inline-flex -translate-x-1/2 items-center gap-2 whitespace-nowrap rounded-full bg-white/95 py-1 pe-3.5 ps-1 text-xs font-semibold text-gray-900 shadow-xl shadow-black/30 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:bg-white group-hover:shadow-2xl sm:bottom-6 sm:gap-2.5 sm:py-2 sm:pe-5 sm:ps-2 sm:text-base" aria-hidden="true">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-[#4E81FA] to-[#0EA5E9] text-white sm:h-8 sm:w-8">
                                    <svg aria-hidden="true" class="h-3 w-3 ltr:ml-0.5 rtl:mr-0.5 rtl:rotate-180 sm:h-3.5 sm:w-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                                <span class="sm:hidden">3-min overview</span>
                                <span class="hidden sm:inline">Watch the 3-minute overview</span>
                            </span>
                        </a>
                    </div>
                </div>
                    <div class="es-frame-glow" aria-hidden="true"></div>
                </div>
            <div class="hp-stage-cap">
                <h2 class="hp-h2" data-reveal>See it in action</h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.1s;">From first event to sold-out show, in one short tour.</p>
            </div>
        </div>

        {{-- The wall (desktop): two wings of drifting poster columns, hinged beside the headline.
             A pointer can open any poster. A keyboard does not stop at them: up to twenty-five
             moving links stood between the claim box and everything else. The first twelve of
             the same events stand still in the Discover rail, and all of them are behind
             Browse all events. --}}
        <div class="es-wall absolute inset-0 hidden lg:block" data-clip-ok aria-hidden="true">
            <div class="es-wall-tilt">
                {{-- The visitor's own poster, pasted over the others. It stands in the left wall's
                     plane but outside its fade, shows faintly until a name is typed in the claim
                     box, and then takes the name (the page's script fills data-claim-name). --}}
                <div class="hp-mine-plane">
                <div class="hp-mine" data-claim-poster aria-hidden="true">
                    <span class="hp-mine-top"><i>On the wall</i><i>This week</i></span>
                    <strong data-claim-name="title">Your name</strong>
                    <span class="hp-mine-foot" data-claim-name="url">your-name{{ $claimSuffix }}</span>
                </div>
                </div>
                @foreach ([0, 1] as $wingIndex)
                    <div class="hp-wing {{ $wingIndex === 0 ? 'hp-wing-l' : 'hp-wing-r' }}">
                        @foreach (array_slice($wallColumns, $wingIndex * 2, 2, true) as $wallColumnIndex => $wallColumnCards)
                            <div class="es-wall-col" style="--dur: {{ $wallDurations[$wallColumnIndex] }}s;">
                                <div class="es-wall-track">
                                    @for ($wallCopy = 0; $wallCopy < 2; $wallCopy++)
                                        @foreach ($wallColumnCards as $wallRowIndex => $wallCard)
                                            {{-- Row 0 of column N is card N, so the wall's eager set is
                                                 cards 0-3: the first four of the strip's first six. --}}
                                            @php
                                                $wallEager = $wallCopy === 0 && $wallRowIndex === 0 && $wallCard['eager'];
                                            @endphp
                                            @if ($wallCard['url'])
                                                <a href="{{ $wallCard['url'] }}" target="_blank" rel="noopener" tabindex="-1" class="es-wall-card">
                                                    <img src="{{ $wallCard['img'] }}" alt="" width="208" height="277" loading="{{ $wallEager ? 'eager' : 'lazy' }}" decoding="async" fetchpriority="{{ $wallEager ? ($wallCard['priority'] ? 'high' : 'auto') : 'low' }}">
                                                    <span class="hp-wall-cap">
                                                        <b>{{ $wallCard['name'] }}</b>
                                                        <span>{{ $wallCard['date'] }}</span>
                                                    </span>
                                                </a>
                                            @else
                                                <div class="es-wall-card" aria-hidden="true">
                                                    <img src="{{ $wallCard['img'] }}" alt="" width="208" height="277" loading="{{ $wallEager ? 'eager' : 'lazy' }}" decoding="async" fetchpriority="{{ $wallEager ? ($wallCard['priority'] ? 'high' : 'auto') : 'low' }}">
                                                </div>
                                            @endif
                                        @endforeach
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The line-up: who it's for                                 -->
    <!-- ============================================================ -->
    @php
        $marqueeRows = [
            [
                ['Musicians', 'bg-blue-500', marketing_url('/for-musicians')],
                ['Venues', 'bg-sky-500', marketing_url('/for-venues')],
                ['DJs', 'bg-cyan-500', marketing_url('/for-djs')],
                ['Promoters', 'bg-blue-400', marketing_url('/for-curators')],
                ['Food Trucks', 'bg-cyan-500', marketing_url('/for-food-trucks-and-vendors')],
                ['Theaters', 'bg-amber-500', marketing_url('/for-theaters')],
                ['Bands', 'bg-emerald-500', marketing_url('/for-live-concerts')],
                ['Festivals', 'bg-rose-500', marketing_url('/use-cases')],
            ],
            [
                ['Comedians', 'bg-amber-500', marketing_url('/for-comedians')],
                ['Nightclubs', 'bg-blue-500', marketing_url('/for-nightclubs')],
                ['Yoga Studios', 'bg-emerald-500', marketing_url('/for-fitness-and-yoga')],
                ['Art Galleries', 'bg-rose-500', marketing_url('/for-art-galleries')],
                ['Farmers Markets', 'bg-lime-500', marketing_url('/for-farmers-markets')],
                ['Restaurants', 'bg-orange-500', marketing_url('/for-restaurants')],
                ['Community Centers', 'bg-sky-500', marketing_url('/for-community-centers')],
                ['Magicians', 'bg-cyan-500', marketing_url('/for-magicians')],
            ],
        ];
    @endphp
    <section class="hp-lineup" aria-label="Who uses Event Schedule">
        <h2 class="sr-only">Who uses Event Schedule</h2>
        <div class="es-marquee-mask">
            @foreach ($marqueeRows as $rowIndex => $row)
                <div class="es-marquee" data-marquee="{{ $rowIndex === 0 ? '1' : '-1' }}">
                    <div class="es-marquee-track">
                        @for ($i = 0; $i < 2; $i++)
                            @foreach ($row as [$persona, $dot, $href])
                                <a href="{{ $href }}" @if ($i === 1) aria-hidden="true" tabindex="-1" data-loop-copy @endif class="hp-act">{{ $persona }}<i class="{{ $dot }}" aria-hidden="true"></i></a>
                            @endforeach
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Plan, promote, sell: one show in three acts               -->
    <!-- ============================================================ -->
    <section id="features" class="hp-sec">
        <div class="hp-wrap hp-meet">
            <div class="hp-head">
                <span class="hp-kicker" data-reveal>One show, start to finish</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Everything you need to fill seats
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    One platform for scheduling, ticketing, newsletters, and check-ins.
                </p>
            </div>
            {{-- Whose show to tell. The page is written around one jazz club; a yoga studio or
                 a festival should not have to read five screens of somebody else's show. --}}
            <div class="hp-casts" role="group" aria-labelledby="hp-casts-label" data-reveal style="--reveal-delay: 0.22s;">
                <span class="hp-casts-label" id="hp-casts-label" data-cast-label>See it as</span>
                @foreach ($hpCasts as $hpCastKey => $hpCast)
                    <button type="button" data-cast-pick="{{ $hpCastKey }}" aria-pressed="{{ $hpCastKey === 'jazz' ? 'true' : 'false' }}">{{ $hpCast['label'] }}</button>
                @endforeach
            </div>
            {{-- The show the three acts are about, introduced before the first of them and
                 standing beside the switch, so a press changes something that is on screen.
                 Until 2026-10 the first thing a press changed was two screens further down.
                 Its words are divs and spans on purpose: the acts count their own headings. --}}
            <div class="hp-show" data-reveal="panel" data-cast-stage aria-hidden="true">
                <div class="hp-show-in">
                <div class="hp-poster">
                    <img src="{{ asset('images/demo/demo_flyer_jazz.webp') }}" alt="" width="800" height="600" loading="lazy" decoding="async" data-cast-img>
                    <div class="hp-poster-top"><span data-cast="tag">Live</span><span>{{ $wkSat->format('D j M') }}</span></div>
                    <div class="hp-poster-type">
                        <small><span data-cast="venue">The Blue Note</span> presents</small>
                        <strong><span data-cast="line1">Jazz</span><br><span data-cast="line2">Night</span></strong>
                        <span><span data-cast="verb">Doors</span> <span data-cast="time">8:00 PM</span> · {{ $hpShowPriceText }}</span>
                    </div>
                </div>
                <div class="hp-stub">
                    <div class="hp-stub-head">
                        <small>Event Schedule</small>
                        <strong data-cast="event">Jazz Night</strong>
                        <span data-cast="tagline">Live jazz, five nights a week</span>
                    </div>
                    <div class="hp-stub-body">
                        <span class="hp-stub-rows">
                            <span><i>Where</i><b data-cast="venue">The Blue Note</b></span>
                            <span><i>When</i><b>{{ $wkSat->format('D, M j') }}</b></span>
                            <span><i data-cast="verb">Doors</i><b data-cast="time">8:00 PM</b></span>
                            <span><i>Tickets</i><b>{{ $hpShowTickets }} at {{ $hpShowPriceText }}</b></span>
                        </span>
                        <span class="hp-stub-foot"><span>GA x1</span><span>#0042</span></span>
                    </div>
                </div>
                </div>
            </div>
            <p class="hp-meet-note" data-reveal style="--reveal-delay: 0.3s;">The three acts below follow this one show, from its poster to its door.</p>
        </div>

        <!-- Act one of three: plan -->
        <div id="plan" class="hp-verb">
            <div class="hp-vband">
                <div class="hp-wrap is-hung">
                    <div class="hp-vrow">
                        <header class="hp-vcard" data-reveal>
                            <p class="hp-vcard-ix" aria-hidden="true">01 / 03</p>
                            <span class="hp-vbars" aria-hidden="true"><i class="is-now"></i><i></i><i></i></span>
                            <h3 class="hp-vcard-word"><span class="sr-only">Part 1 of 3: </span><span class="hp-ink-grad">Plan.</span></h3>
                            <ul class="hp-vcard-has">
                                <li><a href="#how-it-works">Create your schedule {!! $hpArrow !!}</a></li>
                                <li><a href="#appointments">Appointments {!! $hpArrow !!}</a></li>
                            </ul>
                        </header>
                    </div>
                </div>
            </div>
            <div class="hp-wrap is-hung">
                <div class="hp-run">
                    <div class="hp-step" aria-hidden="true"><span class="hp-vbars"><i class="is-now"></i><i></i><i></i></span><i>Plan</i><b>01 / 03</b></div>
                    <div class="hp-beats">

                        <article id="how-it-works" class="hp-beat">
                            <div class="hp-beat-copy">
                                <h4 class="hp-h3"><span class="hp-kicker">Create your schedule<span class="sr-only">: </span></span>Paste a poster. <span class="hp-soft">Get an event.</span></h4>
                                <p>Sign up free, then fill it however suits you: type an event in, connect a calendar, or paste a poster and let the AI read the details off it.</p>
                                <p class="hp-fine">Set up in under 2 minutes.</p>
                                <div class="hp-blurbs">
                                    <div class="hp-blurb">
                                        <span class="hp-ico bg-blue-100 dark:bg-blue-500/20"><svg class="text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['bulb'] !!}</svg></span>
                                        <div>
                                            <h5>AI-Powered</h5>
                                            <p>Paste a poster, a press release or a screenshot and get a filled-in event back. Generate flyers, descriptions and a whole brand style, and publish in 12 languages.</p>
                                            <a href="{{ marketing_url('/features/ai') }}" class="hp-more" aria-label="Learn more about AI-powered features">Learn more {!! $hpArrow !!}</a>
                                        </div>
                                    </div>
                                    <div class="hp-blurb">
                                        <span class="hp-ico bg-sky-100 dark:bg-sky-500/20"><svg class="text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['calendar'] !!}</svg></span>
                                        <div>
                                            <h5>Calendar Sync</h5>
                                            <p>Two-way sync with Google Calendar, Microsoft 365 and any CalDAV server. Edit an event in either place and the other follows.</p>
                                            <a href="{{ marketing_url('/features/calendar-sync') }}" class="hp-more" aria-label="Learn more about calendar sync">Learn more {!! $hpArrow !!}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hp-objwrap"><div class="hp-obj hp-obj-ai es-ai-replay" data-reveal="panel" aria-hidden="true">
                                <div class="hp-poster">
                                    <img src="{{ asset('images/demo/demo_flyer_jazz.webp') }}" alt="" width="800" height="600" loading="lazy" decoding="async" data-cast-img>
                                    {{-- Three lines of the poster are marked as they are read, and each
                                         mark's number stands on the field it became. --}}
                                    <div class="hp-poster-top"><span data-cast="tag">Live</span><span class="hp-read" style="--i: 0;" data-n="1">{{ $wkSat->format('D j M') }}</span></div>
                                    <div class="hp-poster-type">
                                        <small class="hp-read" style="--i: 1;" data-n="2"><span data-cast="venue">The Blue Note</span> presents</small>
                                        <strong><span data-cast="line1">Jazz</span><br><span data-cast="line2">Night</span></strong>
                                        <span class="hp-read" style="--i: 2;" data-n="3"><span data-cast="verb">Doors</span> <span data-cast="time">8:00 PM</span> · $25</span>
                                    </div>
                                    <span class="hp-scan"></span>
                                </div>
                                <div class="hp-flow">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['arrow'] !!}</svg>
                                </div>
                                <div class="hp-card hp-event">
                                    <div class="hp-event-top">
                                        <span class="hp-tile"><b>{{ $wkSat->format('M') }}</b><i>{{ $wkSat->format('j') }}</i></span>
                                        <div>
                                            <strong data-cast="event">Jazz Night</strong>
                                            <span>Read from your poster</span>
                                        </div>
                                    </div>
                                    <div class="hp-fields">
                                        <div class="es-ai-field hp-field" style="--i: 0;">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['calendar'] !!}</svg>
                                            <span>{{ $wkSat->format('D, M j') }}</span>
                                            <b class="hp-read-n">1</b>
                                        </div>
                                        <div class="es-ai-field hp-field" style="--i: 1;">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['pin'] !!}</svg>
                                            <span data-cast="venue">The Blue Note</span>
                                            <b class="hp-read-n">2</b>
                                        </div>
                                        <div class="es-ai-field hp-field" style="--i: 2;">
                                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['ticket'] !!}</svg>
                                            <span><span data-cast="verb">Doors</span> <span data-cast="time">8:00 PM</span> · $25</span>
                                            <b class="hp-read-n">3</b>
                                        </div>
                                    </div>
                                    <div class="hp-synced">
                                        @include('marketing.partials.integration-logo', ['name' => 'google', 'class' => ''])
                                        On your Google Calendar too
                                        <i></i>
                                    </div>
                                </div>
                            </div></div>
                        </article>

                        <article id="appointments" class="hp-beat">
                            <div class="hp-beat-copy">
                                <h4 class="hp-h3"><span class="hp-kicker"><span class="hp-kicker-act" aria-hidden="true"><span class="hp-vbars"><i class="is-now"></i><i></i><i></i></span>Plan</span> Appointments<span class="sr-only">: </span></span>Take bookings. <span class="hp-soft">Between shows.</span></h4>
                                {{-- Not "get booked": that is the hero's payoff, and it was Calendly's own pitch
                                     besides. The claim here is bigger than a booking link, and it is the one
                                     thing no competitor in either category can make: the same calendar holds
                                     the shows and the open hours, and will not let one be booked over the
                                     other. That is also why it stands in Plan, beside the shows, and not in a
                                     section of its own. The mock is the booking page as a guest sees it: open
                                     days only, never a named show (book-type.blade.php removes busy times,
                                     it does not explain them). --}}
                                <p><strong>Your open hours, on the same page.</strong> Appointment booking, built in. Guests pick an open time in their own timezone, and the booking lands on your schedule.</p>
                                <ul class="hp-checks">
                                    <li><span class="hp-ok">{!! $hpCheck !!}</span><span>Weekly hours with buffers and minimum notice</span></li>
                                    <li><span class="hp-ok">{!! $hpCheck !!}</span><span>Free or paid appointments with Stripe</span></li>
                                    <li><span class="hp-ok">{!! $hpCheck !!}</span><span>Never double-booked against your synced calendars</span></li>
                                </ul>
                                <p style="margin-top: 1.5rem;">
                                    <a href="{{ route('marketing.appointments') }}" class="hp-more">Learn more<span class="sr-only"> about appointments</span> {!! $hpArrow !!}</a>
                                </p>
                            </div>
                            <div class="hp-objwrap"><div class="hp-obj hp-obj-book" data-reveal="panel" aria-hidden="true">
                                <div class="hp-book-wrap">
                                <div class="hp-card hp-book">
                                <div class="hp-browser-bar"><i></i><i></i><i></i><span class="hp-url"><span data-cast="slug">blue-note</span>{{ $claimSuffix }}/book</span></div>
                                <div class="hp-book-grid">
                                    <div>
                                        <div class="hp-month-head">
                                            <strong>{{ $wkTue->format('F') }}</strong>
                                            <span><i>&lsaquo;</i><i>&rsaquo;</i></span>
                                        </div>
                                        <div class="hp-month">
                                            <b>M</b><b>T</b><b>W</b><b>T</b><b>F</b><b>S</b><b>S</b>
                                            @foreach ($bookCells as $bookCell)
                                                <span @class([$bookCell['class'] => $bookCell['class'] !== ''])>{{ $bookCell['day'] }}</span>
                                            @endforeach
                                        </div>
                                        <div class="hp-legend">
                                            <span><i></i>Open hours</span>
                                            <span>Times shown in your timezone</span>
                                        </div>
                                    </div>
                                    <div class="hp-slots">
                                        <strong class="hp-slots-type"><span data-cast="appt">Private hire viewing</span><span class="hp-slots-meta">30 min · Free</span></strong>
                                        <small>{{ $wkTue->format('D, M j') }}</small>
                                        <span class="hp-slot">9:00 AM</span>
                                        <span class="hp-slot">11:30 AM</span>
                                        <span class="hp-slot is-pick">{!! $hpCheck !!}3:00 PM</span>
                                        <span class="hp-slot">4:30 PM</span>
                                        <span class="hp-pay">Book</span>
                                    </div>
                                </div>
                            </div>
                                <span class="hp-chip hp-chip-booked"><span class="hp-ok">{!! $hpCheck !!}</span>Booked · Tue 3:00 PM</span>
                                </div>
                            </div></div>
                        </article>

                    </div>
                </div>
            </div>
        </div>

        <!-- Act two of three: promote -->
        <div id="promote" class="hp-verb">
            <div class="hp-vband">
                <div class="hp-wrap is-hung">
                    <div class="hp-vrow">
                        <header class="hp-vcard" data-reveal>
                            <p class="hp-vcard-ix" aria-hidden="true">02 / 03</p>
                            <span class="hp-vbars" aria-hidden="true"><i class="is-done"></i><i class="is-now"></i><i></i></span>
                            <h3 class="hp-vcard-word"><span class="sr-only">Part 2 of 3: </span><span class="hp-ink-grad">Promote.</span></h3>
                            <ul class="hp-vcard-has">
                                <li><a href="#share">Share your link {!! $hpArrow !!}</a></li>
                                <li><a href="#grow">Grow your audience {!! $hpArrow !!}</a></li>
                            </ul>
                        </header>
                    </div>
                </div>
            </div>
            <div class="hp-wrap is-hung">
                <div class="hp-run">
                    <div class="hp-step" aria-hidden="true"><span class="hp-vbars"><i class="is-done"></i><i class="is-now"></i><i></i></span><i>Promote</i><b>02 / 03</b></div>
                    <div class="hp-beats">

                        <article id="share" class="hp-beat">
                            <div class="hp-beat-copy">
                                <h4 class="hp-h3"><span class="hp-kicker">Share your link<span class="sr-only">: </span></span>One link. <span class="hp-soft">Everywhere.</span></h4>
                                <p>You get yourname.eventschedule.com. Put it in your bio, print the QR code on a poster, embed the calendar in your own site, or let guests subscribe to it from their own calendar app.</p>
                                <div class="hp-actions">
                                    <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary is-small" data-claim-link><span data-claim-label="Claim it free">Claim it free</span> {!! $hpArrow !!}</a>
                                </div>
                                <div class="hp-blurbs">
                                    <div class="hp-blurb">
                                        <span class="hp-ico bg-sky-100 dark:bg-sky-500/20"><svg class="text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['mail'] !!}</svg></span>
                                        <div>
                                            <h5>Newsletters</h5>
                                            <p>Send branded emails to followers and ticket buyers with a drag-and-drop editor and A/B testing.</p>
                                            <p class="hp-fine">Templates · Audience segments · A/B testing</p>
                                            <a href="{{ route('marketing.newsletters') }}" class="hp-more" aria-label="Learn more about newsletters">Learn more {!! $hpArrow !!}</a>
                                        </div>
                                    </div>
                                    <div class="hp-blurb">
                                        <span class="hp-ico bg-orange-100 dark:bg-orange-500/20"><svg class="text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['horn'] !!}</svg></span>
                                        <div>
                                            <h5>Boost</h5>
                                            <p>Turn any event into a Facebook or Instagram ad in minutes. Set your budget, pick your audience, and launch with no ad experience needed.</p>
                                            <a href="{{ route('marketing.boost') }}" class="hp-more" aria-label="Learn more about Boost">Learn more {!! $hpArrow !!}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hp-objwrap"><div class="hp-obj hp-obj-share" data-reveal="panel" aria-hidden="true">
                                {{-- The same schedule in the three places the sentence beside it names,
                                     each with its name pinned to it: the calendar embedded in a site
                                     of their own, the page behind the link in a bio, and a printed
                                     poster with its code. --}}
                                <div class="hp-out is-site">
                                    <span class="hp-chip" style="--i: 2;"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['code'] !!}</svg>Embed on your site</span>
                                    <div class="hp-card hp-browser hp-site">
                                        <div class="hp-browser-bar"><i></i><i></i><i></i><span class="hp-url">yourwebsite.com/events</span></div>
                                        <div class="hp-site-nav"><b data-cast="venue">The Blue Note</b><span>Home</span><span>About</span><span class="is-on">Events</span><span>Contact</span></div>
                                        <div class="hp-sched">
                                            <div class="hp-rows">
                                                <div class="hp-row">
                                                    <span class="hp-tile"><b>{{ $wkSat->format('M') }}</b><i>{{ $wkSat->format('j') }}</i></span>
                                                    <div><strong data-cast="event">Jazz Night</strong><span><span data-cast="time">8:00 PM</span> · $25</span></div>
                                                    <span class="hp-pill hp-pill-green">Tickets</span>
                                                </div>
                                                <div class="hp-row">
                                                    <span class="hp-tile"><b>{{ $wkLater[0]->format('M') }}</b><i>{{ $wkLater[0]->format('j') }}</i></span>
                                                    <div><strong data-cast="next1">Open Mic</strong><span><span data-cast="next1time">7:30 PM</span> · Free</span></div>
                                                    <span class="hp-pill hp-pill-blue">RSVP</span>
                                                </div>
                                                <div class="hp-row">
                                                    <span class="hp-tile"><b>{{ $wkLater[1]->format('M') }}</b><i>{{ $wkLater[1]->format('j') }}</i></span>
                                                    <div><strong data-cast="next2">Blues & Brews</strong><span><span data-cast="next2time">9:00 PM</span> · $18</span></div>
                                                    <span class="hp-pill hp-pill-green">Tickets</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="hp-out is-phone">
                                    <span class="hp-chip" style="--i: 0;"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['link'] !!}</svg>Link in bio</span>
                                    <div class="hp-phone">
                                        <div class="hp-phone-screen">
                                            <span class="hp-phone-bar"><span data-cast="slug">blue-note</span>{{ $claimSuffix }}</span>
                                            <span class="hp-phone-who">
                                                <span class="hp-avatar" data-cast="initial">B</span>
                                                <b data-cast="venue">The Blue Note</b>
                                                <span class="hp-follow">Follow</span>
                                            </span>
                                            <span class="hp-phone-row">
                                                <span class="hp-tile"><b>{{ $wkSat->format('M') }}</b><i>{{ $wkSat->format('j') }}</i></span>
                                                <span><b data-cast="event">Jazz Night</b><i data-cast="time">8:00 PM</i></span>
                                            </span>
                                            <span class="hp-phone-row">
                                                <span class="hp-tile"><b>{{ $wkLater[0]->format('M') }}</b><i>{{ $wkLater[0]->format('j') }}</i></span>
                                                <span><b data-cast="next1">Open Mic</b><i data-cast="next1time">7:30 PM</i></span>
                                            </span>
                                            <span class="hp-phone-cta">Get tickets</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="hp-out is-qr">
                                    <span class="hp-chip" style="--i: 1;"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['qr'] !!}</svg>QR poster</span>
                                    <div class="hp-qrp">
                                        <span class="hp-qrp-top"><span data-cast="venue">The Blue Note</span></span>
                                        <strong>Scan for<br>what's on</strong>
                                        <svg viewBox="0 0 29 29" fill="currentColor"><path d="{{ $hpQr }}"/></svg>
                                        <span class="hp-qrp-url"><span data-cast="slug">blue-note</span><wbr>{{ $claimSuffix }}</span>
                                    </div>
                                </div>
                            </div></div>
                        </article>

                        <article id="grow" class="hp-beat">
                            <div class="hp-beat-copy">
                                <h4 class="hp-h3"><span class="hp-kicker"><span class="hp-kicker-act" aria-hidden="true"><span class="hp-vbars"><i class="is-done"></i><i class="is-now"></i><i></i></span>Promote</span> Grow your audience<span class="sr-only">: </span></span>Keep them <span class="hp-soft">coming back.</span></h4>
                                {{-- app:send-event-announcements, hourly on both rails: CONFIRMED
                                     role_subscribers get one digest per batch of newly published
                                     public events. Account followers are NOT included - they are
                                     reached only by a newsletter the owner writes. --}}
                                <p>Visitors leave an email address and get a digest automatically when you publish new events. Write a newsletter yourself whenever there is more to say.</p>
                                <div class="hp-blurbs">
                                    <div class="hp-blurb">
                                        <span class="hp-ico bg-emerald-100 dark:bg-emerald-500/20"><svg class="text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['chart'] !!}</svg></span>
                                        <div>
                                            <h5>Built-in Analytics</h5>
                                            <p>Page views, traffic sources, devices, check-ins and revenue per event, on three tabs. Built in and first-party, so no Google Analytics account is required.</p>
                                            <a href="{{ route('marketing.analytics') }}" class="hp-more" aria-label="Learn more about built-in analytics">Learn more {!! $hpArrow !!}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hp-objwrap"><div class="hp-obj hp-obj-grow" data-reveal="panel" aria-hidden="true">
                                {{-- The digest, not a newsletter: app:send-event-announcements mails
                                     confirmed subscribers by itself when new public events are
                                     published, and does not draw on the newsletter allowance. Its
                                     three dates are the three on the schedule above. --}}
                                <div class="hp-card hp-mail">
                                    <span class="hp-mail-head">
                                        <span class="hp-avatar" data-cast="initial">B</span>
                                        <span><b data-cast="venue">The Blue Note</b><i>to subscribers</i></span>
                                    </span>
                                    <strong class="hp-mail-subject">New dates announced</strong>
                                    <span class="hp-mail-rows">
                                        <span><span class="hp-tile"><b>{{ $wkSat->format('M') }}</b><i>{{ $wkSat->format('j') }}</i></span><span><b data-cast="event">Jazz Night</b><i data-cast="time">8:00 PM</i></span></span>
                                        <span><span class="hp-tile"><b>{{ $wkLater[0]->format('M') }}</b><i>{{ $wkLater[0]->format('j') }}</i></span><span><b data-cast="next1">Open Mic</b><i data-cast="next1time">7:30 PM</i></span></span>
                                        <span><span class="hp-tile"><b>{{ $wkLater[1]->format('M') }}</b><i>{{ $wkLater[1]->format('j') }}</i></span><span><b data-cast="next2">Blues & Brews</b><i data-cast="next2time">9:00 PM</i></span></span>
                                    </span>
                                    <span class="hp-pay">See the schedule</span>
                                    <span class="hp-mail-foot"><span class="hp-ok">{!! $hpCheck !!}</span>Sent automatically</span>
                                </div>
                                <div class="hp-grow-side">
                                    <div class="hp-card hp-fans">
                                        <span class="hp-faces">
                                            <i style="background: #3b82f6;">M</i><i style="background: #0ea5e9;">J</i><i style="background: #06b6d4;">A</i><i style="background: #10b981;">S</i><i class="is-more">+936</i>
                                        </span>
                                        <div><strong data-count-to="940">940</strong><span>followers</span></div>
                                    </div>
                                    <div class="hp-card hp-chart">
                                        <div class="hp-chart-head">
                                            <strong data-count-to="12,480">12,480</strong>
                                            <span>page views this week</span>
                                        </div>
                                        <div class="hp-bars">
                                            <div class="es-bar" style="height: 24%; --bd: 0.2s;"></div>
                                            <div class="es-bar" style="height: 31%; --bd: 0.28s;"></div>
                                            <div class="es-bar" style="height: 48%; --bd: 0.36s;"></div>
                                            <div class="es-bar" style="height: 44%; --bd: 0.44s;"></div>
                                            <div class="es-bar" style="height: 72%; --bd: 0.52s;"></div>
                                            <div class="es-bar is-peak" style="height: 100%; --bd: 0.6s;"></div>
                                            <div class="es-bar" style="height: 58%; --bd: 0.68s;"></div>
                                        </div>
                                        <div class="hp-days"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span class="is-peak">Sat</span><span>Sun</span></div>
                                    </div>
                                </div>
                            </div></div>
                        </article>

                    </div>
                </div>
            </div>
        </div>

        <!-- Act three of three: sell, which ends on the night of the show -->
        <div id="sell" class="hp-verb">
            <div class="hp-vband">
                <div class="hp-wrap is-hung">
                    <div class="hp-vrow">
                        <header class="hp-vcard" data-reveal>
                            <p class="hp-vcard-ix" aria-hidden="true">03 / 03</p>
                            <span class="hp-vbars" aria-hidden="true"><i class="is-done"></i><i class="is-done"></i><i class="is-now"></i></span>
                            <h3 class="hp-vcard-word"><span class="sr-only">Part 3 of 3: </span><span class="hp-ink-grad">Sell.</span></h3>
                            <ul class="hp-vcard-has">
                                <li><a href="#tickets">Ticketing {!! $hpArrow !!}</a></li>
                                <li><a href="#doors">QR Check-ins {!! $hpArrow !!}</a></li>
                            </ul>
                        </header>
                    </div>
                </div>
            </div>
            {{-- The evening: tickets go on sale under a sky that darkens as it is read, and the
                 night band below takes over where it ends. --}}
            <div class="hp-eve">
            <div class="hp-wrap is-hung">
                <div class="hp-run">
                    <div class="hp-step" aria-hidden="true"><span class="hp-vbars"><i class="is-done"></i><i class="is-done"></i><i class="is-now"></i></span><i>Sell</i><b>03 / 03</b></div>
                    <div class="hp-beats">

                        <article id="tickets" class="hp-beat">
                            <div class="hp-beat-copy">
                                <h4 class="hp-h3"><span class="hp-kicker">Ticketing<span class="sr-only">: </span></span>Tickets on sale. <span class="hp-soft">No platform fees.</span></h4>
                                <p>Multiple ticket types, add-ons, promo codes and reserved seating. Switch on the "Notify me" card and, before tickets go on sale, visitors can ask to be told when they do.</p>
                                <p class="hp-paid">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['lock'] !!}</svg>
                                    Get paid by Stripe, PayPal, Payfast, Invoice Ninja, a payment link or cash
                                </p>
                                <div class="hp-actions">
                                    <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary is-small" data-claim-link><span data-claim-label="Get started">Get started</span> {!! $hpArrow !!}</a>
                                    <a href="#fees" class="hp-more">Compare the fees {!! $hpArrow !!}</a>
                                </div>
                            </div>
                            <div class="hp-objwrap"><div class="hp-obj hp-obj-sell" data-reveal="panel" aria-hidden="true">
                                <div class="hp-card hp-checkout">
                                    <div class="hp-checkout-head">
                                        <span class="hp-tile"><b>{{ $wkSat->format('M') }}</b><i>{{ $wkSat->format('j') }}</i></span>
                                        <div>
                                            <strong data-cast="event">Jazz Night</strong>
                                            <span>Sat <span data-cast="time">8:00 PM</span> · <span data-cast="venue">The Blue Note</span></span>
                                        </div>
                                    </div>
                                    <div class="hp-tt">
                                        <div><strong>General admission</strong><small>GA · 150 tickets</small></div>
                                        <span class="hp-tt-price">$25</span>
                                        <span class="hp-qty"><b>&minus;</b>2<b>+</b></span>
                                    </div>
                                    <div class="hp-tt">
                                        <div><strong data-cast="tier2">VIP</strong><small data-cast="tier2note">Early entry</small></div>
                                        <span class="hp-tt-price">$45</span>
                                        <span class="hp-qty"><b>&minus;</b>0<b>+</b></span>
                                    </div>
                                    <div class="hp-tt">
                                        <div><strong data-cast="addon">Parking</strong><small>Add-on</small></div>
                                        <span class="hp-tt-price">$8</span>
                                        <span class="hp-qty"><b>&minus;</b>1<b>+</b></span>
                                    </div>
                                    <div class="hp-code">Promo code <b></b></div>
                                    <div class="hp-sum"><span>Tickets and add-ons</span><b>$58</b></div>
                                    <div class="hp-sum is-zero"><span>Platform fee</span><b>0%</b></div>
                                    <div class="hp-pay">Checkout · $58</div>
                                </div>
                                <div class="hp-zero"><b>0%</b><span>platform<br>fees</span></div>
                                <p class="hp-payout">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['lock'] !!}</svg>
                                    Payments land in your own Stripe or PayPal account, not ours
                                </p>
                            </div></div>
                        </article>

                    </div>
                </div>
            </div>
            </div>

            <!-- The night of the show, the full width of the window -->
            <div class="hp-night">
                <div class="hp-wrap is-hung">
                    <div class="hp-run">
                        <div class="hp-step" aria-hidden="true"><span class="hp-vbars"><i class="is-done"></i><i class="is-done"></i><i class="is-now"></i></span><i>Sell</i><b>03 / 03</b></div>
                        <div class="hp-beats">

                            <article id="doors" class="hp-beat">
                                <div class="hp-beat-copy">
                                    <h4 class="hp-h3"><span class="hp-kicker"><span class="hp-kicker-act" aria-hidden="true"><span class="hp-vbars"><i class="is-done"></i><i class="is-done"></i><i class="is-now"></i></span>Sell</span> QR Check-ins<span class="sr-only">: </span></span>Doors <span class="hp-ink-grad">open.</span></h4>
                                    <p>Every ticket carries a QR code you scan at the door, on any plan, with zero platform fees.</p>
                                    <p class="hp-doors" aria-hidden="true"><span><span data-cast="verb">Doors</span><b data-cast="time">8:00 PM</b></span><span>Checked in<b data-count-to="142">142</b></span><span>Tickets<b>150</b></span></p>
                                    <p style="margin-top: 1.5rem;">
                                        <a href="{{ marketing_url('/features/ticketing') }}" class="hp-more" aria-label="Learn more about ticketing and QR check-ins">Learn more {!! $hpArrow !!}</a>
                                    </p>
                                </div>
                                <div class="hp-objwrap"><div class="hp-obj hp-obj-door" data-reveal="panel" aria-hidden="true">
                                    <div class="hp-beams"></div>
                                    <div class="hp-ticket-wrap" data-tilt="12">
                                        <div class="hp-ticket es-tilt-inner">
                                            <div class="hp-ticket-head">
                                                <small>Event Schedule</small>
                                                <strong data-cast="event">Jazz Night</strong>
                                                <span>{{ $wkSat->format('D, M j') }} · <span data-cast="time">8:00 PM</span></span>
                                            </div>
                                            <div class="hp-ticket-body">
                                                <div class="hp-ticket-qr">
                                                    <svg viewBox="0 0 29 29" fill="currentColor"><path d="{{ $hpQr }}"/></svg>
                                                    <div class="es-laser"></div>
                                                </div>
                                                <div class="hp-ticket-foot"><span>GA x1</span><span>#0042</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="hp-chip hp-chip-in" style="--i: 1;"><span class="hp-ok">{!! $hpCheck !!}</span>Checked in</span>
                                </div></div>
                            </article>

                        </div>
                    </div>
                </div>
                {{-- The room: one light for each of the 150 tickets, in rows that run the width of the
                     window and stand back from it, 142 of them coming on as the band arrives. Which
                     eight stay dark, and the order the rest come on in, are fixed sums, not chance:
                     the page is cached. --}}
                <div class="hp-room" data-reveal aria-hidden="true">
                    <p class="hp-room-cap"><b data-count-to="142">142</b> of 150 through the door</p>
                    <div class="hp-crowd">
                        @for ($row = 0; $row < 5; $row++)
                            <span>
                                @for ($seat = $row * 30; $seat < $row * 30 + 30; $seat++)
                                    <i @class(['is-in' => ! in_array($seat, [7, 23, 41, 58, 76, 97, 118, 139], true)]) style="--i: {{ ($seat * 37) % 150 }};"></i>
                                @endfor
                            </span>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The numbers: free and open source                         -->
    <!-- ============================================================ -->
    <section id="open-source" class="hp-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>What it costs</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Free and open source. <span class="hp-ink-grad">Forever.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    No hidden fees. No per-ticket charges. Past your payment provider's own fee, every sale is yours.
                </p>
            </div>

            <div class="hp-figs" data-reveal-group="140">
                <div class="hp-fig" data-reveal>
                    <div class="text-gradient hp-num">0%</div>
                    <strong>No platform fees</strong>
                    <p>Payments land in your own Stripe or PayPal account, not ours</p>
                </div>
                <div class="hp-fig" data-reveal>
                    <div class="text-gradient hp-num">{{ plan_price(0) }}</div>
                    <strong>Free forever</strong>
                    <p>Unlimited events, schedules and free registrations</p>
                </div>
                <div class="hp-fig" data-reveal>
                    <div class="text-gradient hp-num"><span data-count-to="100">100</span>%</div>
                    <strong>Open source</strong>
                    <p>AAL licensed. Selfhost it and every paid feature is included</p>
                </div>
            </div>

            {{-- The fee calculator from /compare, as it is (every figure is App\Utils\TicketFees),
                 opened on this page's own show: 150 tickets at $25. The two sliders are this
                 page's; they write into the calculator's own number fields, which do the sums. --}}
            {{-- The FAQ's own sentence, word for word. It stands between "Free forever" and a
                 calculator whose first card is a monthly price, so the two do not contradict
                 each other. --}}
            <p class="hp-figs-note" data-reveal>A ticket that carries a price needs the Pro plan, while free registration stays unlimited on every plan.</p>

            <div id="fees" class="hp-keep" data-reveal>
                <div class="hp-keep-head">
                    <h3 class="hp-h3">Run the numbers <span class="hp-soft">on your own show</span></h3>
                    {{-- The label is tied to the slider by id: an <output> inside a wrapping label
                         would take the label for itself and leave the slider with no name. The
                         figure beside it is for the eye; the slider says its own value. --}}
                    <div class="hp-sliders">
                        <div class="hp-slider">
                            <span><label for="hp-range-tickets">Tickets sold</label> <output for="hp-range-tickets" data-fee-out="tickets" aria-hidden="true">{{ $hpShowTickets }}</output></span>
                            <input id="hp-range-tickets" type="range" min="10" max="1000" step="10" value="{{ $hpShowTickets }}" autocomplete="off" data-fee-range="tickets">
                        </div>
                        <div class="hp-slider">
                            <span><label for="hp-range-price">Ticket price</label> <output for="hp-range-price" data-fee-out="price" aria-hidden="true">{{ $hpShowPriceText }}</output></span>
                            <input id="hp-range-price" type="range" min="5" max="150" step="1" value="{{ $hpShowPrice }}" autocomplete="off" aria-valuetext="{{ $hpShowPriceText }}" data-fee-range="price">
                        </div>
                    </div>
                </div>
                {{-- Three competitors whose rates were read off their own pricing pages (TicketFees
                     says which were and which were not): the default set carries one that could
                     not be re-checked, and this is the most visited page on the site. --}}
                {{-- The show's own takings, as the slip a till prints: plain arithmetic on the two
                     sliders, and no claim of its own. What selling them costs is the
                     calculator's, drawn here as one bar to a platform. --}}
                <div class="hp-after">
                    <div class="hp-slip-wrap">
                        <div class="hp-slip">
                            <span class="hp-slip-cap">The morning after</span>
                            {{-- A space the eye never sees: without it the two run together when read out. --}}
                            <strong class="hp-slip-name" data-fee-who><span data-cast="event">Jazz Night</span></strong>
                            <span class="hp-slip-row"><i>Tickets sold</i> <b data-fee-n>{{ $hpShowTickets }}</b></span>
                            <span class="hp-slip-row"><i>Ticket price</i> <b data-fee-p>{{ $hpShowPriceText }}</b></span>
                            <span class="hp-slip-total"><i>Ticket sales</i> <b data-fee-gross>{{ $hpShowGrossText }}</b></span>
                        </div>
                    </div>
                    <div class="hp-after-main">
                        <p class="hp-after-cap">What selling them costs</p>
                        <x-marketing.fee-calculator :platforms="$hpFeePlatforms" :tickets="$hpShowTickets" :price="$hpShowPrice" id="hp-fee" class="hp-calc" />
                    </div>
                </div>
            </div>

            <div class="hp-figs-links" data-reveal style="--reveal-delay: 0.2s;">
                <span class="hp-figs-more">
                    <a href="{{ marketing_url('/pricing') }}" class="hp-more">See the plans {!! $hpArrow !!}</a>
                    <a href="{{ marketing_url('/open-source') }}" class="hp-more">Read the licence and the code {!! $hpArrow !!}</a>
                    <a href="{{ marketing_url('/selfhost') }}" class="hp-more">Install it on your own server {!! $hpArrow !!}</a>
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Everything else                                           -->
    <!-- ============================================================ -->
    @php
        $moreFeatures = [
            [
                'href' => marketing_url('/features/online-events'),
                'aria' => 'Learn more about online events',
                'title' => 'Online Events',
                // One event_url field, not an integration: we never host or read back the
                // stream, so name platforms only as examples of a link.
                'desc' => 'Paste the link people join on: Zoom, Meet, Twitch, your own page. Toggle between in person and online, and the link prints on every ticket.',
                'chip' => 'bg-sky-100 dark:bg-sky-500/20',
                'text' => 'text-sky-700 dark:text-sky-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />',
            ],
            [
                'href' => marketing_url('/features/polls'),
                'aria' => 'Learn more about event polls',
                'title' => 'Event Polls',
                // EventController::votePoll returns results + total_votes in the response to
                // your own vote, and they are hidden until then. Not a live ticker.
                'desc' => 'Add multiple choice polls. Signed-in guests pick one option and the count comes back with their vote.',
                'chip' => 'bg-blue-100 dark:bg-blue-500/20',
                'text' => 'text-blue-600 dark:text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />',
            ],
            [
                'href' => route('marketing.allocated_seating'),
                'aria' => 'Learn more about reserved seating',
                'title' => 'Reserved Seating',
                // SeatingPlanController: reusable plans with levels, sections, rows, tables,
                // standing areas and wheelchair spaces; guests pick their own seat.
                'desc' => 'Draw your room once, then let guests pick their own seat. Rows, tables, standing areas and a box office console for phone bookings.',
                'chip' => 'bg-blue-100 dark:bg-blue-500/20',
                'text' => 'text-blue-600 dark:text-blue-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 18v-6a2 2 0 012-2h12a2 2 0 012 2v6M4 18h16M4 18v2m16-2v2M7 10V6a2 2 0 012-2h6a2 2 0 012 2v4M8 14h.01M12 14h.01M16 14h.01" />',
            ],
            [
                'href' => marketing_url('/features/fan-videos'),
                'aria' => 'Learn more about fan videos and comments',
                'title' => 'Fan Videos & Comments',
                'desc' => 'Fans add YouTube videos and comments to your events, with your approval before anything goes live.',
                'chip' => 'bg-rose-100 dark:bg-rose-500/20',
                'text' => 'text-rose-700 dark:text-rose-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />',
            ],
            [
                'href' => marketing_url('/features/sub-schedules'),
                'aria' => 'Learn more about sub-schedules',
                'title' => 'Sub-schedules',
                // A Group is fillable on name, name_en, slug and color: it sorts and colour-codes.
                // Rooms are not a feature, so do not imply per-room scheduling.
                'desc' => 'Give a run of events a name, a colour and a link of its own. Perfect for a weekly series or a season of shows.',
                'chip' => 'bg-rose-100 dark:bg-rose-500/20',
                'text' => 'text-rose-700 dark:text-rose-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />',
            ],
            [
                'href' => marketing_url('/features/event-graphics'),
                'aria' => 'Learn more about event graphics',
                'title' => 'Event Graphics',
                'desc' => 'Auto-generate shareable images and formatted text for your upcoming events. Ready for Instagram, WhatsApp, email, and more.',
                'chip' => 'bg-orange-100 dark:bg-orange-500/20',
                'text' => 'text-orange-700 dark:text-orange-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />',
            ],
            [
                'href' => marketing_url('/features/custom-fields'),
                'aria' => 'Learn more about custom fields',
                'title' => 'Custom Fields',
                'desc' => 'Six field types, from a yes/no toggle to a multi-select. Ask at checkout, or put the question on your public event request form.',
                'chip' => 'bg-amber-100 dark:bg-amber-500/20',
                'text' => 'text-amber-700 dark:text-amber-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
            ],
            [
                'href' => route('marketing.private_events'),
                'aria' => 'Learn more about private events',
                'title' => 'Private Events',
                'desc' => 'Four levels of visibility, from a public listing to an unlisted page behind a password. Hide an event from your calendar and still send someone the link.',
                'chip' => 'bg-yellow-100 dark:bg-yellow-500/20',
                'text' => 'text-yellow-700 dark:text-yellow-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />',
            ],
            [
                'href' => marketing_url('/features/recurring-events'),
                'aria' => 'Learn more about recurring events',
                'title' => 'Recurring Events',
                // Event::matchesFrequency() cases: daily, weekly, every_n_weeks, monthly_date,
                // monthly_weekday, yearly; recurring_include/exclude_dates add or skip one date;
                // Ticket::soldCountFor($date) counts per occurrence date.
                'desc' => 'Repeat daily, weekly, every few weeks, monthly or yearly. Skip or add single dates, and each date sells its own tickets.',
                'chip' => 'bg-lime-100 dark:bg-lime-500/20',
                'text' => 'text-lime-700 dark:text-lime-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
            ],
            [
                'href' => route('marketing.white_label'),
                'aria' => 'Learn more about white-label branding',
                'title' => 'White-label Branding',
                'desc' => 'Take our name off every page, ticket and email, then bring your own colours, fonts, favicon and CSS.',
                'chip' => 'bg-emerald-100 dark:bg-emerald-500/20',
                'text' => 'text-emerald-700 dark:text-emerald-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />',
            ],
            [
                'href' => marketing_url('/features/team-scheduling'),
                'aria' => 'Learn more about team scheduling',
                'title' => 'Team Scheduling',
                'desc' => 'Invite people instead of sharing a login. Admins run the schedule and see sales; viewers are read-only but can still scan tickets at the door.',
                'chip' => 'bg-cyan-100 dark:bg-cyan-500/20',
                'text' => 'text-cyan-700 dark:text-cyan-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
            ],
            [
                'href' => route('marketing.gift_cards'),
                'aria' => 'Learn more about gift cards and passes',
                'title' => 'Gift Cards & Passes',
                // GiftCardController (balance-tracked, emailed to a recipient) plus
                // PassBookingService (visit passes, memberships, season and festival passes).
                'desc' => 'Sell gift cards that arrive by email with a running balance, and passes that admit the same guest across a season or a whole festival.',
                'chip' => 'bg-teal-100 dark:bg-teal-500/20',
                'text' => 'text-teal-700 dark:text-teal-400',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zM5 12h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />',
            ],
        ];
    @endphp
    <section id="more-features" class="hp-sec">
        <div class="hp-wrap">
            {{-- The twelve, set like the names at the foot of a festival bill. Each name is the link
                 it always was; its sentence stands under the bill while the name is pointed at or
                 focused, and under the name itself on a touch screen, where nothing can be
                 pointed at. --}}
            <div class="hp-bill" data-reveal="panel">
                <p class="hp-bill-top" aria-hidden="true"><span>Also on the bill</span><span>Twelve more</span></p>
                <h2 class="hp-h2">And everything else you'd expect</h2>
                <ul class="hp-bill-list" data-hint="Point at a name">
                    @foreach ($moreFeatures as $feature)
                        <li>
                            <a href="{{ $feature['href'] }}">
                                <span class="hp-bill-name">{{ $feature['title'] }}</span>
                                <span class="hp-bill-desc">{{ $feature['desc'] }}</span>
                            </a>
                        </li>
                        {{-- Three sizes of name, as on a bill: three, then four, then five. --}}
                        @if (in_array($loop->index, [2, 6], true))
                            <li class="hp-bill-break" role="presentation"></li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Discover: upcoming events rail                            -->
    <!-- ============================================================ -->
    @php $discoverPinned = $discoverEvents->count() >= 4; @endphp
    <section id="discover" class="hp-discover">
        <div @class([
            'hp-discover-band 2xl:mx-auto 2xl:max-w-[100rem]',
            'es-gallery' => $discoverPinned,
        ]) data-scene="gallery">
            <div class="es-gallery-pin">
            <div class="hp-head is-center hp-discover-head">
                <span class="hp-kicker" data-reveal>Discover</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Discover events across the community
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    Upcoming events from across the community. Live music, fitness classes, comedy nights, community meetups, and more.
                </p>
            </div>

            @if ($discoverEvents->count() > 0)
                <div class="es-rail-clip w-full" tabindex="0" aria-label="Upcoming events across the community" data-clip-ok>
                    <div class="es-rail items-stretch gap-5 px-6 lg:gap-7 lg:px-[7vw]">
                        @foreach ($discoverEvents->take(12) as $event)
                            @include('marketing.partials.event-poster-card', ['event' => $event])
                        @endforeach

                        <!-- Rail finale card -->
                        <div class="es-shot flex w-[72vw] shrink-0 sm:w-[320px]">
                            <div class="hp-rail-end">
                                <strong>Your events could be here</strong>
                                <p>Create your schedule and get discovered by the community.</p>
                                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary" data-claim-link><span data-claim-label="Start for free">Start for free</span> {!! $hpArrow !!}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="es-rail-progress-wrap mx-auto mt-8 w-48" aria-hidden="true">
                    <div class="h-1 overflow-hidden rounded-full bg-gray-200">
                        <div class="es-rail-progress h-full w-full rounded-full"></div>
                    </div>
                </div>

                <div class="hp-discover-foot">
                    <a href="{{ marketing_url('/browse') }}" class="hp-btn hp-btn-ghost">Browse all events {!! $hpArrow !!}</a>
                    <a href="{{ marketing_url('/search') }}" class="hp-plain">or search for something specific</a>
                </div>
            @else
                {{-- No events yet: still offer a way in --}}
                <div class="hp-discover-foot">
                    <a href="{{ marketing_url('/browse') }}" class="hp-btn hp-btn-primary">Browse all events {!! $hpArrow !!}</a>
                </div>
            @endif

            <p class="hp-discover-foot" style="margin-top: 1.25rem; padding-bottom: 0.5rem; flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 0.4rem;">
                Run events of your own?
                <a href="{{ app_url('/sign_up') }}" class="hp-more" data-claim-link>Get discovered in community search {!! $hpArrow !!}</a>
            </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Integrations: the orbit                                   -->
    <!-- ============================================================ -->
    @php
        // The same six names and links as ever, in the two groups the sentence beside them
        // already speaks of: the calendars, and where the money goes.
        $hpPlugs = [
            'in' => [
                ['name' => 'google', 'label' => 'Google Calendar', 'href' => marketing_url('/google-calendar')],
                ['name' => 'outlook', 'label' => 'Outlook', 'href' => marketing_url('/features/calendar-sync')],
                ['name' => 'apple', 'label' => 'Apple Calendar', 'href' => marketing_url('/features/calendar-sync')],
                ['name' => 'caldav', 'label' => 'CalDAV', 'href' => marketing_url('/caldav')],
            ],
            'out' => [
                ['name' => 'stripe', 'label' => 'Stripe', 'href' => marketing_url('/stripe')],
                ['name' => 'invoiceninja', 'label' => 'Invoice Ninja', 'href' => marketing_url('/invoiceninja')],
            ],
        ];
    @endphp
    <section id="integrations" class="hp-sec hp-alt">
        <div class="hp-wrap hp-plug-grid">
            <div class="hp-head">
                <span class="hp-kicker" data-reveal>Integrates with</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    The tools you already use
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    Calendars sync both ways. Payments go straight to your own Stripe or
                    <a href="{{ route('marketing.paypal') }}" class="hp-inline">PayPal</a>
                    account. Existing events come across from
                    <a href="{{ route('marketing.switch_from_eventbrite') }}" class="hp-inline">Eventbrite</a>,
                    and a
                    <a href="{{ marketing_url('/docs/developer/api') }}" class="hp-inline">REST API and webhooks</a>
                    handle whatever is left.
                </p>
            </div>

            {{-- The six that used to circle in an orbit, and then stood in a row: same names, same
                 links, now drawn as what each is plugged into. The schedule in the middle is the
                 show's own (and the visitor's, once a name is typed). The two captions say only
                 what the sentence beside them says. --}}
            <div class="hp-wire" data-reveal="panel">
                <div class="hp-wire-side is-in">
                    <p class="hp-wire-cap" id="hp-wire-in"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['swap'] !!}</svg>Calendars <span>sync both ways</span></p>
                    <ul class="hp-plugs" aria-labelledby="hp-wire-in">
                        @foreach ($hpPlugs['in'] as $plug)
                            <li>
                                <a href="{{ $plug['href'] }}" class="hp-plug">
                                    @include('marketing.partials.integration-logo', ['name' => $plug['name'], 'class' => 'hp-plug-logo'])
                                    <span>{{ $plug['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="hp-hub" aria-hidden="true">
                    <span class="hp-wire-mark is-in"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['swap'] !!}</svg></span>
                    <span class="hp-avatar" data-cast="initial">B</span>
                    <strong data-cast="venue">The Blue Note</strong>
                    <span class="hp-hub-url"><span data-cast="slug">blue-note</span><wbr>{{ $claimSuffix }}</span>
                    <span class="hp-hub-row">
                        <span class="hp-tile"><b>{{ $wkSat->format('M') }}</b><i>{{ $wkSat->format('j') }}</i></span>
                        <span><b data-cast="event">Jazz Night</b><i data-cast="time">8:00 PM</i></span>
                    </span>
                    <span class="hp-wire-mark is-out"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $hpIcon['arrow'] !!}</svg></span>
                </div>
                <div class="hp-wire-side is-out">
                    <p class="hp-wire-cap" id="hp-wire-out"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">{!! $hpIcon['arrow'] !!}</svg>Payments <span>straight to you</span></p>
                    <ul class="hp-plugs" aria-labelledby="hp-wire-out">
                        @foreach ($hpPlugs['out'] as $plug)
                            <li>
                                <a href="{{ $plug['href'] }}" class="hp-plug">
                                    @include('marketing.partials.integration-logo', ['name' => $plug['name'], 'class' => 'hp-plug-logo'])
                                    <span>{{ $plug['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. FAQ                                                       -->
    <!-- ============================================================ -->
    @php
        $homeFaqs = [
            // Tiers verified in code, not in FEATURES.md: a ticket that carries a price needs
            // Pro or Enterprise, so "ticketing" is a paid feature again. Free registration and
            // ticket types priced at zero stay unlimited on every tier, which is why the free
            // claims here are written about registration rather than about selling.
            // TicketController::scan()/scanned() have no plan check, so scanning at the door is
            // free too. Add-ons, promo codes and the ticket waitlist ARE Pro (EventRepo::saveEvent()
            // $ticketExtrasAllowed scrub, and the ticket branch of WaitlistController::join()),
            // so they are named as what Pro adds on top. The gateways and refunds are named as Pro
            // too, because Event::canSellPaidTickets() is what makes them reachable: on Free there
            // is nothing to settle and nothing to reverse. The payments layer itself carries no
            // isPro() (none under app/Services/Payments/, none in SaleRefundService), so a
            // downgraded schedule can still refund what it already took - see docs/FEATURES.md.
            // The interest list (EventInterestController) carries no plan check either. CalDAV has no inbound
            // delete sync - applyInboundDeletion() is called only by the Google and Microsoft
            // services - so the deletion choice is stated for those two only.
            ['q' => 'Is Event Schedule free?', 'a' => 'Yes, Event Schedule is free to use with unlimited events, unlimited schedules and unlimited free registration. Pro and Enterprise plans add paid ticket sales, event boosting, custom branding, and AI image generation.'],
            ['q' => 'Can I sell tickets with Event Schedule?', 'a' => 'Yes, with zero platform fees. A ticket that carries a price needs the Pro plan, while free registration stays unlimited on every plan. Create as many ticket types as you need and scan the QR code on every ticket at the door, on any plan. Pro also adds extras like parking or merchandise, promo codes and a waitlist for sold-out tickets.'],
            ['q' => 'Can people get notified when tickets go on sale?', 'a' => 'Yes, on every plan. Switch on the "Notify me" card and, until tickets go on sale, an event page offers "Tell me when tickets go on sale". A visitor leaves an email address, with no account, and gets one email when tickets go on sale, one if it is cancelled, a reminder shortly before it starts, and any notice you choose to send if the date or venue changes. Every email has a one-click unsubscribe, and the event editor shows you how many people are waiting.'],
            ['q' => 'How do I get paid?', 'a' => 'Straight into your own account. You connect your Stripe or PayPal account and payments land there directly, so we never hold your money and never take a cut. Payfast (for events priced in South African rand), Invoice Ninja, a payment link of your own and cash at the door work too. Taking money for a ticket is the Pro plan, and it opens all six.'],
            ['q' => 'Can I refund a ticket?', 'a' => 'Yes, in full or in part, from the Sales page. Refunds come with paid ticketing on Pro, because that is where the money is taken in the first place. Refund a Stripe or PayPal sale and the money goes back to the buyer through that provider. A partial refund leaves the tickets valid, and a full refund puts them back on sale. A sale paid any other way, cash included, can be marked as refunded so your records match.'],
            ['q' => 'Does Event Schedule sync with my calendar?', 'a' => 'Yes. Google Calendar and Microsoft 365 both sync two ways, with webhook updates so a change made in either place shows up in the other, and you choose whether an event deleted there is kept, marked cancelled or deleted here. Any CalDAV server works as well. Guests can add a single event to Apple, Google or Outlook from the event page, or subscribe to your whole schedule as a live calendar feed that updates itself when a date changes.'],
            ['q' => 'Can I use my own domain?', 'a' => 'Yes. Every schedule gets a free subdomain such as yourname.eventschedule.com, and Enterprise schedules can serve the whole guest portal from a domain you own, with the certificate issued automatically. Selfhosted installs run on your own domain from day one.'],
            ['q' => 'Can I selfhost Event Schedule?', 'a' => 'Yes, Event Schedule is 100% open source. Selfhost it on your own server for full control over your data and every paid feature is included, or use the hosted platform at eventschedule.com.'],
            ['q' => 'Who is Event Schedule for?', 'a' => 'Anyone who keeps a schedule other people need to see: musicians, DJs, comedians, venues, bars, theaters, galleries, studios, markets, libraries and the curators who list them all in one place.'],
            // EventRepo::saveEvent() creates a Role for a typed-in act or venue; RoleController
            // renders it through role/show-guest-unclaimed (noindex) until User::claimSchedule(),
            // which needs a verified email or phone matching the row (userHoldsContactFor()), and
            // preserveExistingListers() keeps the schedules that listed them.
            ['q' => 'Do the performers and venues I list need an account?', 'a' => 'No. Name a performer or venue who is not on Event Schedule and the event page still shows them, and they get a page of their own that says who created it and that they have not claimed it yet. It stays out of search engines until they claim it by signing in with the email address or phone number you entered for them, and the schedules that already list them keep listing them.'],
        ];
    @endphp
    <x-seo.faq-schema :items="$homeFaqs" />
    <section class="hp-sec">
        <div class="hp-wrap">
            <div class="hp-faq-grid">
                <div class="hp-head">
                    <span class="hp-kicker" data-reveal>Before you ask</span>
                    <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                        Frequently asked questions
                    </h2>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                        Everything you need to know about sharing your events and selling tickets with Event Schedule.
                    </p>
                </div>
                <div data-reveal-group="60">
                    @foreach ($homeFaqs as $faq)
                        {{-- The one about selling stands open: it is the answer that says what a
                             priced ticket needs. --}}
                        <details name="faq" data-reveal class="hp-faq" @if ($loop->index === 1) open @endif>
                            <summary>
                                <h3>{{ $faq['q'] }}</h3>
                                <i aria-hidden="true"></i>
                            </summary>
                            <p class="faq-answer">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale: claim your name                                  -->
    <!-- ============================================================ -->
    <section id="claim" class="hp-sec" style="padding-top: 0;">
        <div class="hp-wrap">
            <div class="hp-finale" data-reveal="panel">
                <div class="hp-finale-wall">
                    @foreach (array_slice($wallCards, 0, 8) as $finaleCard)
                        <img src="{{ $finaleCard['img'] }}" alt="" width="208" height="277" loading="lazy" decoding="async">
                    @endforeach
                </div>
                <div class="hp-finale-poster">
                <div class="hp-mine is-large" data-claim-poster aria-hidden="true">
                    <span class="hp-mine-top"><i>On the wall</i><i>This week</i></span>
                    <strong data-claim-name="title">Your name</strong>
                    <span class="hp-mine-foot" data-claim-name="url">your-name{{ $claimSuffix }}</span>
                </div>
                </div>
                <div class="hp-finale-copy">
                <h2 class="hp-h2">
                    Ready to share <span class="hp-ink-grad">your schedule?</span>
                </h2>
                <p class="hp-lead">
                    Create your event page, sell tickets with zero platform fees, and email your audience directly. All in one free platform.
                </p>

                <div class="hp-claimrow">
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div dir="ltr" class="es-claim hp-claim">
                        <input id="es-claim-input" type="text" placeholder="your-name" autocomplete="off" autocapitalize="none" spellcheck="false" maxlength="30">
                        <span class="hp-claim-suffix">{{ $claimSuffix }}</span>
                    </div>
                    <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">Claim it free {!! $hpArrow !!}</a>
                </div>

                <p class="hp-finale-foot">No credit card required. Free forever.</p>
                </div>
            </div>
        </div>
    </section>

    </div>

    {{-- The page's own behaviours, each small.

         1. Two lights move over the poster wall, and the posters under them come up in colour
            (.is-lit). The lights keep to the part of the hero that is on screen, so there is
            always something lit while a wall can be seen, and a pointer takes them over for a
            couple of seconds whenever it moves: one light under it, its twin opposite on the
            other wall. The work is done a few times a second, never per frame, only while the
            wall is on screen (it is not drawn at all below a laptop's width) and the tab is in
            front, with every measurement read before anything is changed. With motion turned
            off the lights stand still and are placed once.
         2. A name typed in either claim box is seen to land: the browser bar of the showreel
            repeats it, the visitor's poster on the wall (and the large one in the finale) takes
            it, and so do the mock-ups that say whose schedule this is (data-cast="venue" and its
            address). Cleared, they go back to the cast that is showing.
         2b. The switch above the three acts re-casts the mock-ups (data-cast) from the list the server
            printed: a jazz club, a comedy night, a yoga studio, a street festival. Names, times
            and the poster's picture only.
         3. The two sliders above the fee calculator write into the calculator's own number
            fields, which do the sums.
         4. The cards of the pinned events rail grow a little as they pass the middle of the window.
         5. The finale throws confetti once. It is drawn without canvas-confetti's worker, which
            the worker-src policy refuses in silence. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var hero = document.getElementById('top');
            var wall = hero ? hero.querySelector('.es-wall') : null;
            if (wall && 'IntersectionObserver' in window) {
                var cards = Array.prototype.slice.call(wall.querySelectorAll('.es-wall-card'));
                var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                var pointer = null;
                var timer = null;
                var placed = false;
                if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                    hero.addEventListener('pointermove', function (e) {
                        pointer = { x: e.clientX, y: e.clientY, at: Date.now() };
                    });
                }
                var light = function () {
                    var led = pointer && Date.now() - pointer.at < 2500;
                    if (document.hidden || (still && placed && !led)) {
                        return;
                    }
                    placed = true;
                    var box = hero.getBoundingClientRect();
                    var top = Math.max(box.top, 0);
                    var tall = Math.max(0, Math.min(box.bottom, window.innerHeight) - top);
                    var lights;
                    if (led) {
                        lights = [[pointer.x, pointer.y], [box.left + box.right - pointer.x, pointer.y]];
                    } else {
                        var t = still ? 0 : Date.now() / 1000;
                        lights = [
                            [box.left + box.width * (0.1 + 0.04 * Math.sin(t / 7)), top + tall * (0.42 + 0.3 * Math.sin(t / 5))],
                            [box.left + box.width * (0.9 - 0.04 * Math.cos(t / 6)), top + tall * (0.5 + 0.3 * Math.cos(t / 4.3))],
                        ];
                    }
                    var reach = 13 * (parseFloat(getComputedStyle(document.documentElement).fontSize) || 16);
                    var lit = cards.map(function (card) {
                        var r = card.getBoundingClientRect();
                        return r.bottom > 0 && r.top < window.innerHeight && lights.some(function (at) {
                            var dx = Math.max(r.left - at[0], 0, at[0] - r.right);
                            var dy = Math.max(r.top - at[1], 0, at[1] - r.bottom);
                            return dx * dx + dy * dy < reach * reach;
                        });
                    });
                    cards.forEach(function (card, i) {
                        if (card.classList.contains('is-lit') !== lit[i]) {
                            card.classList.toggle('is-lit', lit[i]);
                        }
                    });
                };
                new IntersectionObserver(function (entries) {
                    var onScreen = entries[entries.length - 1].isIntersecting;
                    if (onScreen && !timer) {
                        light();
                        timer = setInterval(light, 160);
                    } else if (!onScreen && timer) {
                        clearInterval(timer);
                        timer = null;
                    }
                }).observe(wall);
            }

            var echo = document.querySelector('[data-claim-echo]');
            var plain = echo ? echo.textContent : '';
            var posters = Array.prototype.slice.call(document.querySelectorAll('[data-claim-poster]'));
            var casts = @json($hpCasts);
            var castKey = 'jazz';
            var typed = '';
            var castEls = Array.prototype.slice.call(document.querySelectorAll('[data-cast]'));
            var castImgs = Array.prototype.slice.call(document.querySelectorAll('[data-cast-img]'));
            var castStage = document.querySelector('[data-cast-stage]');
            var castLabel = document.querySelector('[data-cast-label]');
            if (castLabel) {
                castLabel.parentElement.setAttribute('data-ready', '');
            }
            posters.forEach(function (poster) {
                poster.querySelectorAll('[data-claim-name]').forEach(function (el) { el.setAttribute('data-default', el.textContent); });
                // Pressing your poster puts the cursor in the box it is waiting on.
                poster.addEventListener('click', function () {
                    var box = document.getElementById(poster.closest('.hp-finale') ? 'es-claim-input' : 'es-claim-hero');
                    if (box) {
                        box.focus();
                    }
                });
            });
            var titled = function (name) {
                return name.split('-').filter(Boolean).map(function (w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ');
            };
            var clip = function (text) {
                return text.length > 20 ? text.slice(0, 19) + '\u2026' : text;
            };
            var recast = function () {
                var cast = casts[castKey];
                var title = typed ? titled(typed) : cast.venue;
                // The mock-ups were drawn around a name the length of the cast's own.
                var shown = clip(title);
                castEls.forEach(function (el) {
                    var kind = el.getAttribute('data-cast');
                    var text = kind === 'venue' ? shown : (kind === 'slug' ? (typed ? clip(typed) : cast.slug) : (kind === 'initial' && typed ? title.charAt(0) : cast[kind]));
                    if (text !== undefined && el.textContent !== text) {
                        el.textContent = text;
                    }
                });
                if (castLabel) {
                    castLabel.textContent = typed ? 'See ' + title + ' as' : 'See it as';
                }
                castImgs.forEach(function (castImg) {
                    var plainPoster = !cast.img;
                    castImg.parentElement.classList.toggle('is-plain', plainPoster);
                    if (!plainPoster && castImg.getAttribute('src') !== cast.img) {
                        castImg.setAttribute('src', cast.img);
                    }
                });
            };
            document.addEventListener('click', function (e) {
                var pick = e.target && e.target.closest ? e.target.closest('[data-cast-pick]') : null;
                if (!pick) {
                    return;
                }
                castKey = pick.getAttribute('data-cast-pick');
                document.querySelectorAll('[data-cast-pick]').forEach(function (other) {
                    other.setAttribute('aria-pressed', other === pick ? 'true' : 'false');
                });
                recast();
                // The answer to the press, beside it: the show's poster is pasted up again.
                if (castStage) {
                    castStage.classList.remove('is-swap');
                    void castStage.offsetWidth;
                    castStage.classList.add('is-swap');
                }
            });
            document.addEventListener('input', function (e) {
                if (!e.target || !e.target.closest || !e.target.closest('.es-claim')) {
                    return;
                }
                // Cleaned as the shared script cleans it, which may not have started yet.
                var name = e.target.value.toLowerCase().replace(/['\u2019]/g, '').replace(/[^a-z0-9-]+/g, '-').replace(/-{2,}/g, '-').replace(/^-+|-+$/g, '').slice(0, 30);
                var words = name.split('-').filter(Boolean);
                var longest = words.reduce(function (n, w) { return Math.max(n, w.length); }, 0);
                typed = name;
                // A sign-up button away from the boxes says what it claims once there is a name to
                // say (beside a box the name is already on screen); a long one would break the
                // row, so past 20 letters the button keeps its own words.
                document.querySelectorAll('[data-claim-label]').forEach(function (label) {
                    label.textContent = name && name.length <= 20 ? 'Claim ' + name : label.getAttribute('data-claim-label');
                });
                if (echo) {
                    echo.textContent = name ? name + '.' + plain : plain;
                }
                posters.forEach(function (poster) {
                    poster.classList.toggle('is-named', !!name);
                    poster.style.setProperty('--fit', String(Math.max(0.45, Math.min(1, 6 / Math.max(longest, 1)))));
                    poster.querySelectorAll('[data-claim-name]').forEach(function (el) {
                        var kind = el.getAttribute('data-claim-name');
                        el.textContent = !name ? el.getAttribute('data-default') : (kind === 'title' ? titled(name) : name + '\u200B.' + plain);
                    });
                });
                recast();
            });

            // The calculator is a shared component and its button is its own. Marked here, before
            // the shared script starts, so a name typed in a claim box rides it like the others.
            var calcStart = document.querySelector('.hp-calc a[href*="sign_up"]');
            if (calcStart) {
                calcStart.setAttribute('data-claim-link', '');
            }
            // The takings over the calculator: the two sliders multiplied, and nothing else.
            var feeCount = document.querySelector('[data-fee-n]');
            var feeEach = document.querySelector('[data-fee-p]');
            var feeGross = document.querySelector('[data-fee-gross]');
            var takings = function () {
                var sold = document.querySelector('[data-fee-range="tickets"]');
                var each = document.querySelector('[data-fee-range="price"]');
                if (!sold || !each || !feeCount || !feeEach || !feeGross) {
                    return;
                }
                var count = parseInt(sold.value, 10) || 0;
                var price = parseInt(each.value, 10) || 0;
                // Once a slider has been moved the numbers are no longer the jazz night's.
                var who = document.querySelector('[data-fee-who]');
                if (who && who.firstElementChild) {
                    who.textContent = 'Your show';
                }
                var grouped = function (n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ','); };
                feeCount.textContent = grouped(count);
                feeEach.textContent = '$' + price;
                feeGross.textContent = '$' + grouped(count * price);
            };
            document.querySelectorAll('[data-fee-range]').forEach(function (range) {
                var kind = range.getAttribute('data-fee-range');
                var field = document.querySelector('.hp-calc [data-fee-input="' + kind + '"]');
                var out = document.querySelector('[data-fee-out="' + kind + '"]');
                if (!field) {
                    return;
                }
                // The sliders are shown only once they can do something.
                range.closest('.hp-keep').setAttribute('data-fee-ready', '');
                // A reload can hand the calculator's own field back a number the slider does not
                // show; the two start on the same one.
                field.value = range.value;
                range.addEventListener('input', function () {
                    field.value = range.value;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    takings();
                    if (out) {
                        out.textContent = (kind === 'price' ? '$' : '') + range.value;
                    }
                    if (kind === 'price') {
                        range.setAttribute('aria-valuetext', '$' + range.value);
                    }
                });
            });

            var rail = document.querySelector('.es-gallery .es-rail');
            var railPin = document.querySelector('.es-gallery .es-gallery-pin');
            if (rail && railPin && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                var shots = Array.prototype.slice.call(rail.querySelectorAll('.es-shot'));
                var turning = false;
                var railSeen = false;
                var turn = function () {
                    turning = false;
                    if (!railSeen || getComputedStyle(railPin).position !== 'sticky') {
                        return;
                    }
                    var mid = window.innerWidth / 2;
                    var places = shots.map(function (shot) {
                        var r = shot.getBoundingClientRect();
                        return Math.max(-1, Math.min(1, (r.left + r.width / 2 - mid) / mid));
                    });
                    shots.forEach(function (shot, i) {
                        shot.style.setProperty('--cs', (1.04 - Math.abs(places[i]) * 0.12).toFixed(3));
                    });
                };
                var askTurn = function () {
                    if (railSeen && !turning) {
                        turning = true;
                        requestAnimationFrame(turn);
                    }
                };
                new IntersectionObserver(function (entries) {
                    railSeen = entries[0].isIntersecting;
                    askTurn();
                }).observe(railPin);
                window.addEventListener('scroll', askTurn, { passive: true });
                window.addEventListener('resize', askTurn, { passive: true });
            }

            var finale = document.querySelector('.hp-finale');
            if (!finale || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }
            var seen = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting || !window.confetti || !window.confetti.create) {
                        return;
                    }
                    seen.disconnect();
                    var fire = window.confetti.create(null, { resize: true });
                    var colors = ['#4E81FA', '#0EA5E9', '#22D3EE', '#ffffff'];
                    fire({ particleCount: 80, angle: 60, spread: 60, startVelocity: 55, origin: { x: 0.06, y: 0.95 }, colors: colors, disableForReducedMotion: true });
                    fire({ particleCount: 80, angle: 120, spread: 60, startVelocity: 55, origin: { x: 0.94, y: 0.95 }, colors: colors, disableForReducedMotion: true });
                });
            }, { threshold: 0.5 });
            seen.observe(finale);
        })();
    </script>
    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
