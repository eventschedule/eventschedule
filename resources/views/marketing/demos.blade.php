<x-marketing-layout :hp="true">
    @php
        // config/example_shots.php, which app:generate-example-shots writes: the schedules' own
        // pictures at the size the wall uses ($art), and the photographs of their pages with what
        // was measured on them ($shots, $xray). Without it the page still stands: the wall shows
        // each schedule's header image as it was listed, a picture pressed opens the live page,
        // and nothing on the page speaks of photographs.
        $shots ??= [];
        $xray ??= [];
        $art ??= [];
        $shotsDate ??= null;
        // The two walls, hung by App\Utils\ExampleSchedules (which the controller hands over).
        $walls ??= \App\Utils\ExampleSchedules::walls($categories, $art);
    @endphp
    {{-- SEO Slots --}}
    <x-slot name="title">Event Schedule Examples | Live Demo Schedules to Explore</x-slot>
    <x-slot name="description">Open {{ $scheduleCount }} live Event Schedule demos, from a yoga retreat and a pub lineup to woodworking classes and a model town, and see what visitors can do on each.</x-slot>
    <x-slot name="breadcrumbTitle">Examples</x-slot>

    {{-- Structured Data for Rich Results. Built with SeoUtils::jsonLd so the payload
         names what the wall names and so names with an apostrophe
         (Nate's Woodworking Shop) encode correctly. --}}
    <x-slot name="structuredData">
    @php
        $collectionPayload = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Event Schedule Examples',
            'description' => 'A gallery of ' . $scheduleCount . ' live demo schedules showcasing Event Schedule features for various industries',
            'url' => url('/examples'),
            'numberOfItems' => $scheduleCount,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'Event Schedule',
                'url' => config('app.url'),
            ],
        ];
        $itemListPayload = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Live Event Schedule Demos',
            'description' => 'Explore real examples of Event Schedule in action',
            'numberOfItems' => $scheduleCount,
            'itemListElement' => array_values(array_map(function ($index, $schedule) use ($shots) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    // The name on the schedule's own page, which is the name under its picture.
                    'name' => $shots[$schedule['subdomain']]['title']['text'] ?? $schedule['name'],
                    'url' => $schedule['url'],
                ];
            }, array_keys($allSchedules), $allSchedules)),
        ];
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! \App\Utils\SeoUtils::jsonLd($collectionPayload) !!}
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! \App\Utils\SeoUtils::jsonLd($itemListPayload) !!}
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
        {{-- ==============================================================
           Examples, "Try it on". The page is a wall of the demo schedules'
           own pictures, each shown whole in rows that end flush, the way
           /browse hangs its posters. A name typed in the box is set over every
           one, in that schedule's own typeface, and goes with the visitor down
           the page. A picture that is pressed brings that schedule's page
           forward, as a phone shows it. Under the wall one schedule is opened
           up and its parts are numbered.

           Plain CSS under #hp (the house style, partials/hp-kit) on purpose:
           a utility class that is not in the built stylesheet draws nothing.
           Every note here is a Blade comment, so none reaches a visitor.
           ============================================================== --}}

        {{-- ---------- hero ---------- --}}
        {{-- A short hero: the first row of the wall, names and all, is on the first screen. --}}
        #hp .ex-hero.is-short { padding-block: clamp(2rem, 5vh, 3.5rem) 0.5rem; }
        #hp .ex-hero .hp-eyebrow { margin-bottom: clamp(1rem, 2vh, 1.25rem); }
        #hp .ex-hero .hp-claimrow { max-width: 36rem; margin-top: clamp(1.25rem, 2.6vh, 1.6rem); }
        @media (min-width: 1024px) {
            #hp .ex-hero .hp-h1 { font-size: min(4.7vw, 4.9rem); }
        }
        .ex-phone-only { display: none; }
        {{-- The box takes a name as it is written (an apostrophe, a capital, another alphabet), not
           an address: the pictures show what was typed. The address it makes stands under it. --}}
        #hp .ex-namebox { font-family: var(--hp-display); font-size: 1.0625rem; }
        #hp .ex-namebox input { text-align: start; font-weight: 700; font-variation-settings: 'wght' 700; letter-spacing: -0.01em; }
        #hp .ex-namebox input::placeholder { font-weight: 400; font-variation-settings: 'wght' 450; letter-spacing: 0; }
        {{-- The field and, right under it, the address it makes: on a phone that line is the one
           thing that answers each letter above the keyboard. --}}
        .ex-namecol { display: grid; flex: 1 1 0; gap: 0.45rem; min-width: 0; }
        .ex-addrline { padding-inline: 0.35rem; text-align: start; font-family: var(--hp-mono); font-size: 0.84rem; line-height: 1.4; overflow-wrap: anywhere; color: var(--hp-ink-3); }
        @media (min-width: 640px) {
            #hp .ex-namerow { align-items: flex-start; }
        }
        {{-- A laptop's window is short: the hero gives up what it can, so the first row's names
           are in it. --}}
        @media (min-width: 1100px) and (max-height: 840px) {
            #hp .ex-hero.is-short { padding-top: 1.25rem; }
            #hp .ex-hero .hp-eyebrow { margin-bottom: 0.7rem; }
            #hp .ex-hero .hp-h1 { font-size: min(4.1vw, 4.1rem); }
            #hp .ex-hero .hp-sub { margin-top: 0.7rem; }
            #hp .ex-hero .hp-claimrow { margin-top: 1rem; }
            .ex-hero .hp-hero-foot { display: none; }
            .ex-wall { padding-top: 0.25rem; }
            .ex-status { margin-block: 0.5rem 0.9rem; }
            .ex-tile { margin-bottom: 1.5rem; }
        }
        .ex-hero.is-named .ex-addrline [data-ex-slug] { font-weight: 700; color: var(--hp-blue); }
        #hp .ex-seeall { display: none; }
        @media (max-width: 639.98px) {
            .ex-not-phone { display: none; }
            .ex-phone-only { display: inline; }
            {{-- On a phone the wall is below the keyboard: once there is a name, the way to it is a
               button of its own under the box. --}}
            #hp .ex-seeall:not([hidden]) { display: flex; width: min(100% - 2.5rem, 22rem); margin: 0.75rem auto 0; }
        }

        {{-- ---------- the wall ---------- --}}
        .ex-wall { position: relative; padding-block: 0.75rem clamp(1rem, 2vw, 1.5rem); }
        .ex-wall::before {
            content: "";
            position: absolute;
            inset: 12rem 0 0 0;
            z-index: 0;
            pointer-events: none;
            background: linear-gradient(to bottom, transparent, var(--hp-bg-3) 18rem, var(--hp-bg-3) calc(100% - 14rem), transparent);
        }
        {{-- :where() keeps this at one class's weight: heavier, it undid the pill's sticking. --}}
        .ex-wall > :where(:not(.sr-only)) { position: relative; z-index: 1; }
        .ex-rooms { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem; width: min(100% - 2.5rem, 76rem); margin: 0 auto; }
        {{-- Their row is kept before the script marks them, so the wall does not step down when it does. --}}
        .ex-rooms:not([data-ready]) { visibility: hidden; }
        .ex-room {
            display: inline-flex;
            flex: none;
            align-items: baseline;
            gap: 0.5rem;
            min-height: 2.75rem;
            padding: 0.55rem 1rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 999px;
            background: var(--hp-bg-2);
            font-size: 0.95rem;
            font-weight: 700;
            font-variation-settings: 'wght' 680;
            line-height: 1.4;
            color: var(--hp-ink-2);
            cursor: pointer;
            transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
        }
        .ex-room b { font-family: var(--hp-mono); font-size: 0.72rem; font-variation-settings: normal; letter-spacing: 0.06em; color: var(--hp-ink-3); }
        .ex-room:hover { border-color: var(--hp-blue); color: var(--hp-ink); transform: translateY(-1px); }
        .ex-room[aria-pressed="true"] { border-color: var(--hp-ink); background: var(--hp-ink); color: var(--hp-bg); }
        .ex-room[aria-pressed="true"] b { color: inherit; opacity: 0.72; }
        @media (max-width: 639.98px) {
            {{-- Six chips are three lines on a phone, and the wall would start a screen down: one
               row to swipe instead, thinning out at the edge it runs on from. --}}
            .ex-rooms {
                flex-wrap: nowrap;
                justify-content: flex-start;
                width: 100%;
                padding-inline: 1.25rem 2.5rem;
                overflow-x: auto;
                scrollbar-width: none;
                -webkit-mask-image: linear-gradient(90deg, #000 calc(100% - 3rem), transparent);
                mask-image: linear-gradient(90deg, #000 calc(100% - 3rem), transparent);
            }
            .ex-rooms::-webkit-scrollbar { display: none; }
        }
        {{-- One line under the chips: what to do, the name being tried on, or what the chosen room is for. --}}
        .ex-status { width: min(100% - 2.5rem, 52rem); margin: 0.9rem auto clamp(1.25rem, 2vw, 1.75rem); text-align: center; font-size: 1rem; line-height: 1.5; color: var(--hp-ink-2); text-wrap: balance; }
        .ex-status p { min-height: 1.5em; }
        .ex-status b { color: var(--hp-ink); }
        .ex-roomnote .hp-more { margin-inline-start: 0.4rem; }
        @media (max-width: 639.98px) {
            .ex-roomnote .hp-more { display: flex; justify-content: center; margin: 0.3rem 0 0; }
        }
        {{-- Every picture is as wide as its shape says, over a zero basis, so the pictures between
           two breaks always share one row whatever the window measures (App\Utils\PosterWall
           decides the rows; this is /browse's wall). Times a hundred: growth factors that sum
           to less than one would share out only that fraction of the row. --}}
        .ex-grid {
            --gap: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            column-gap: var(--gap);
            width: min(100% - 2.5rem, 112rem);
            margin-inline: auto;
        }
        @media (min-width: 1024px) {
            .ex-grid { --gap: 1rem; width: min(100% - 5rem, 112rem); }
        }
        .ex-tile { position: relative; flex: calc(var(--r) * 100) 1 0%; min-width: 0; margin-bottom: 1.9rem; container-type: inline-size; }
        .ex-frame { position: relative; display: block; aspect-ratio: var(--r); transition: opacity 0.35s ease; }
        {{-- The free space has no shape of its own: it takes the one its row needs at each width
           (said in the stylesheet, because a value set on the element itself would outrank these). --}}
        .ex-blank { --r: 2; }
        .ex-brk { display: none; flex: 0 0 100%; height: 0; }
        .ex-pad { display: none; flex: calc(var(--p) * 100) 1 0%; }
        @media (max-width: 639.98px) {
            .ex-brk-m, .ex-pad-m { display: block; }
            {{-- A picture alone in a phone's row: a long banner would be a strip, so it keeps a
               shape a phone can show and gives up its ends. --}}
            .ex-frame { aspect-ratio: var(--rm, var(--r)); }
            .ex-blank { --r: var(--bm); }
        }
        @media (min-width: 640px) and (max-width: 1023.98px) {
            .ex-brk-t, .ex-pad-t { display: block; }
            .ex-blank { --r: var(--bt); }
        }
        @media (min-width: 1024px) and (max-width: 1439.98px) {
            .ex-brk-l, .ex-pad-l { display: block; }
            .ex-blank { --r: var(--bl); }
        }
        @media (min-width: 1440px) and (max-width: 1919.98px) {
            .ex-brk-d, .ex-pad-d { display: block; }
            .ex-blank { --r: var(--bd); }
        }
        @media (min-width: 1920px) {
            .ex-brk-x, .ex-pad-x { display: block; }
            .ex-blank { --r: var(--bx); }
        }

        {{-- --- a picture --- --}}
        .ex-clip {
            position: absolute;
            inset: 0;
            overflow: hidden;
            border-radius: 0.65rem;
            background: var(--hp-bg-3);
            box-shadow: 0 1px 2px rgba(10, 16, 32, 0.12), 0 14px 30px -16px rgba(10, 16, 32, 0.42);
            transition: transform 0.4s cubic-bezier(0.2, 0.7, 0.2, 1), box-shadow 0.4s ease;
        }
        .dark .ex-clip { box-shadow: 0 18px 40px -18px rgba(0, 0, 0, 0.9); }
        {{-- A hairline over the picture, so a pale one still has an edge on a pale wall. --}}
        .ex-clip::after { content: ""; position: absolute; inset: 0; border-radius: inherit; box-shadow: inset 0 0 0 1px rgba(10, 16, 32, 0.1); pointer-events: none; }
        .dark .ex-clip::after { box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08); }
        .ex-img { position: relative; display: block; width: 100%; height: 100%; object-fit: cover; }
        {{-- The light a picture throws on the wall: itself again, out of focus, a little below, and
           kept inside its own width so two neighbours' lights do not meet and mix. --}}
        .ex-glow {
            position: absolute;
            inset: 14% 9% 3%;
            width: 82%;
            height: 83%;
            max-width: none;
            object-fit: cover;
            border-radius: 1.4rem;
            opacity: 0.46;
            filter: blur(18px) saturate(1.7);
            transition: opacity 0.4s ease, transform 0.4s ease;
            pointer-events: none;
        }
        .dark .ex-glow { opacity: 0.55; }
        {{-- The whole tile is the link, and the first thing in it for a keyboard. --}}
        .ex-link { position: absolute; inset: 0; z-index: 3; border-radius: 0.65rem; }
        #hp .ex-link:focus-visible { outline-offset: 4px; }
        @media (hover: hover) {
            .ex-unit:hover .ex-clip { transform: translateY(-9px) scale(1.02); box-shadow: 0 2px 4px rgba(10, 16, 32, 0.14), 0 30px 50px -20px rgba(10, 16, 32, 0.55); }
            .ex-unit:hover .ex-glow { opacity: 0.95; transform: scale(1.1); }
            #hp .ex-unit:hover .ex-name { color: var(--hp-blue); }
            {{-- The others stand back at night only. On a pale wall a faded picture looks switched off. --}}
            .dark .ex-grid:has(.ex-unit:hover) .ex-unit:not(:hover) .ex-frame { opacity: 0.62; }
        }
        .ex-unit:focus-within .ex-clip { transform: translateY(-9px) scale(1.02); }
        .ex-unit:focus-within .ex-glow { opacity: 1; }

        {{-- The name that is being tried on, set over the picture in that schedule's own typeface.
           Nothing is there until a name is typed: the pictures are clean, as /browse's are. --}}
        #hp .ex-worn {
            position: absolute;
            inset: 0;
            z-index: 1;
            display: grid;
            place-items: center;
            overflow: hidden;
            padding: 6cqi 7cqi;
            background: linear-gradient(rgba(5, 8, 20, 0.34), rgba(5, 8, 20, 0.62));
            text-align: center;
            font-family: var(--font, var(--hp-display)), var(--hp-display);
            font-size: calc(clamp(1.15rem, 9.5cqi, 3.1rem) * var(--fit, 1));
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: normal;
            line-height: 1.12;
            color: #fff;
            text-shadow: 0 2px 18px rgba(5, 8, 20, 0.55);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #hp .ex-grid.is-named .ex-worn { opacity: 1; }
        .ex-worn > span { display: block; max-width: 100%; overflow-wrap: anywhere; transition: opacity 0.18s ease, transform 0.18s ease; }
        .ex-worn.is-swap > span { opacity: 0; transform: translateY(18%); transition: none; }

        {{-- The name on a photograph of a page (the one brought forward, the two under "Look
           closer"): the one part of it that is type. It stands where the page's own heading stood,
           in the page's own typeface. All of its sizes are in the picture's width. --}}
        #hp .ex-title {
            position: absolute;
            left: calc(var(--x) / var(--pw, 390) * 100%);
            top: calc(var(--y) / var(--h) * 100%);
            width: calc(var(--w) / var(--pw, 390) * 100%);
            height: calc(var(--th) / var(--pw, 390) * 100cqw);
            display: flex;
            align-items: center;
            justify-content: var(--ta, center);
            overflow: hidden;
            text-align: var(--tx, center);
            font-family: var(--font, 'Inter'), 'Inter', ui-sans-serif, system-ui, sans-serif;
            font-size: calc(var(--fs) / var(--pw, 390) * 100cqw * var(--fit, 1));
            font-weight: var(--wt);
            font-variation-settings: normal;
            letter-spacing: normal;
            line-height: 1.25;
            color: var(--ink);
        }
        {{-- The rule above sets a display of its own, which the hidden attribute alone would not undo. --}}
        #hp .ex-title[hidden] { display: none; }
        .ex-title > span { display: block; max-width: 100%; overflow-wrap: anywhere; transition: opacity 0.18s ease, transform 0.18s ease; }
        {{-- A name that is one long word still breaks where it must. --}}
        [data-ex-name] { overflow-wrap: anywhere; }
        .ex-title.is-swap > span { opacity: 0; transform: translateY(18%); transition: none; }

        {{-- --- its caption: the schedule's logo over the picture's foot, then what it is, its
           name and its address. On the wall, never on the picture. --- --}}
        .ex-cap { position: relative; z-index: 2; display: flex; align-items: flex-start; gap: 0.7rem; padding: 0.75rem 0.1rem 0 0.6rem; }
        .ex-logo { flex: none; width: 3.25rem; height: 3.25rem; margin-top: -1.9rem; border-radius: 0.8rem; object-fit: cover; background: var(--hp-bg-3); box-shadow: 0 0 0 3px var(--hp-bg), 0 8px 18px -8px rgba(10, 16, 32, 0.5); }
        .ex-cap > div { min-width: 0; }
        .ex-kind { font-size: 0.8125rem; font-weight: 700; font-variation-settings: 'wght' 700; line-height: 1.3; color: var(--hp-blue); }
        #hp .ex-name { margin-top: 0.15rem; font-size: 1rem; font-weight: 700; font-variation-settings: 'wght' 660; letter-spacing: -0.01em; line-height: 1.25; overflow-wrap: anywhere; color: var(--hp-ink); transition: color 0.2s ease; }
        .ex-url { display: block; overflow: hidden; margin-top: 0.2rem; font-size: 0.8125rem; line-height: 1.45; text-overflow: ellipsis; white-space: nowrap; color: var(--hp-ink-3); }

        {{-- --- the made-up town, over its own wall --- --}}
        .ex-town { width: min(100% - 2.5rem, 48rem); margin: clamp(2.5rem, 5vw, 4.5rem) auto clamp(1.5rem, 2.5vw, 2.25rem); text-align: center; }
        #hp .ex-town .hp-h3 { margin-top: 0.8rem; }
        .ex-town p { margin-top: 0.75rem; font-size: clamp(1rem, 0.3vw + 0.95rem, 1.12rem); line-height: 1.55; color: var(--hp-ink-2); text-wrap: balance; }
        .ex-town .hp-more { margin-inline-start: 0.4rem; }

        {{-- --- the wall ends on a space that is free: yours --- --}}
        .ex-blank .ex-clip {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 0.2rem;
            padding: clamp(0.9rem, 6cqi, 1.5rem);
            border: 1px dashed var(--hp-line-2);
            background: radial-gradient(26rem 14rem at 90% 0%, var(--hp-glow), transparent 70%), var(--hp-bg-2);
            color: var(--hp-ink);
        }
        .ex-blank .ex-clip::after { content: none; }
        .ex-blank-k { font-size: clamp(0.72rem, 4.4cqi, 0.9rem); font-weight: 700; font-variation-settings: 'wght' 640; color: var(--hp-ink-3); }
        .ex-blank-t { font-size: clamp(1.15rem, 8.5cqi, 2.2rem); font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.035em; line-height: 1.05; overflow-wrap: anywhere; }
        .ex-blank-u { overflow: hidden; font-family: var(--hp-mono); font-size: clamp(0.7rem, 3.6cqi, 0.86rem); text-overflow: ellipsis; white-space: nowrap; color: var(--hp-ink-3); }
        .ex-blank-go { display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 0.5rem; font-size: clamp(0.82rem, 4.6cqi, 1rem); font-weight: 700; font-variation-settings: 'wght' 700; color: var(--hp-blue); }
        .ex-blank-go svg { width: 1.1em; height: 1.1em; transition: transform 0.2s ease; }
        .ex-blank:hover .ex-blank-go svg { transform: translateX(4px); }
        .ex-blank:hover .ex-clip { transform: translateY(-9px) scale(1.02); border-color: var(--hp-blue); }

        {{-- A room chosen: only its schedules stand, at one height, in the middle of the wall. --}}
        .ex-grid[data-room] { justify-content: center; }
        .ex-grid[data-room] .ex-brk,
        .ex-grid[data-room] .ex-pad,
        .ex-grid[data-room] .ex-blank,
        .ex-grid[data-room] .ex-unit:not(.is-in) { display: none; }
        .ex-grid[data-room] .ex-unit { flex: 0 1 calc(var(--r) * clamp(8.5rem, 12.5vw, 13.5rem)); }
        @media (max-width: 639.98px) {
            .ex-grid[data-room] .ex-unit { flex: 1 1 100%; }
        }

        {{-- The first pictures arrive in turn. A CSS animation, not a reveal: a tile waiting on the
           shared observer would be hidden until that script had run. --}}
        @keyframes ex-hang {
            from { opacity: 0; transform: translateY(1.1rem) scale(0.985); }
            to { opacity: 1; transform: none; }
        }
        html.es-anim .ex-unit[data-first] { animation: ex-hang 0.7s cubic-bezier(0.2, 0.7, 0.2, 1) both; animation-delay: calc(var(--i) * 55ms + 0.2s); }
        @keyframes ex-in {
            from { opacity: 0; transform: translateY(1.75rem); }
            to { opacity: 1; transform: none; }
        }
        {{-- A room's pictures glide to their places, where the browser can do it, and pass under
           the bar above and the pill, which are named so they are drawn over them. --}}
        ::view-transition-group(*) { animation-duration: 0.45s; animation-timing-function: cubic-bezier(0.2, 0.7, 0.2, 1); }
        body:has(#hp) > header.sticky { view-transition-name: ex-header; }
        .ex-town { view-transition-name: ex-town; }
        .ex-blank { view-transition-name: ex-blank; }

        {{-- The name that is being tried on, and the way to keep it. It rides along the foot of
           the window while the wall is on screen and the box at the top is not. It keeps its
           place when it is not shown, so nothing under it moves when it is. --}}
        .ex-bar { position: sticky; bottom: 1rem; z-index: 30; display: flex; justify-content: center; margin-top: 1.25rem; pointer-events: none; visibility: hidden; view-transition-name: ex-bar; }
        .ex-bar.is-on { visibility: visible; }
        .ex-bar-in {
            pointer-events: auto;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.5rem 1rem;
            max-width: min(100% - 1.5rem, 54rem);
            padding: 0.55rem 0.6rem 0.55rem 1.2rem;
            border: 1px solid rgba(125, 165, 255, 0.4);
            border-radius: 1.25rem;
            background: #0a1020;
            box-shadow: 0 24px 60px -18px rgba(10, 16, 32, 0.65);
            color: #eef2ff;
        }
        html.es-anim .ex-bar.is-on .ex-bar-in { animation: ex-in 0.35s ease both; }
        .ex-bar-txt { min-width: 0; font-size: 0.95rem; line-height: 1.35; color: #c5cde2; }
        .ex-bar-txt b { color: #fff; }
        .ex-bar-url { display: block; overflow: hidden; font-family: var(--hp-mono); font-size: 0.8rem; text-overflow: ellipsis; white-space: nowrap; color: #a9c3ff; }
        .ex-bar-x { display: grid; flex: none; place-items: center; width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; color: #c5cde2; cursor: pointer; transition: background-color 0.2s ease, color 0.2s ease; }
        .ex-bar-x:hover { background: rgba(255, 255, 255, 0.1); color: #fff; }
        .ex-bar-x svg { width: 1.1rem; height: 1.1rem; }
        @media (max-width: 639.98px) {
            {{-- One row on a phone: the name at the left, the button at the right. --}}
            .ex-bar { bottom: 0.6rem; }
            .ex-bar-in { flex-wrap: nowrap; justify-content: space-between; width: calc(100% - 1.25rem); padding: 0.4rem 0.4rem 0.4rem 0.9rem; gap: 0.25rem; }
            .ex-bar-txt { flex: 1 1 0; overflow: hidden; font-size: 0.86rem; text-overflow: ellipsis; white-space: nowrap; }
            .ex-bar-url { display: none; }
            .ex-bar-in .hp-btn { min-height: 2.75rem; padding-inline: 0.9rem; font-size: 0.9rem; }
            .ex-bar-x { width: 2.5rem; height: 2.75rem; }
        }

        {{-- ---------- one schedule, brought forward ---------- --}}
        {{-- A picture that is pressed brings that schedule's page forward as a phone shows it, at its
           real size, the whole page to scroll, wearing the name. Without the script, or where there
           is no photograph, the picture is a plain link to the live page. --}}
        #hp dialog.ex-fit { width: min(100vw - 2rem, 54rem); max-width: none; max-height: none; margin: auto; padding: 0; border: 0; background: transparent; color: var(--hp-ink); overflow: visible; }
        {{-- A browser that does not know the element lays its contents out in the page: not there. --}}
        #hp dialog.ex-fit:not([open]) { display: none; }
        #hp dialog.ex-fit::backdrop { background: rgba(5, 8, 20, 0.8); -webkit-backdrop-filter: blur(6px); backdrop-filter: blur(6px); }
        .ex-fit-in {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            grid-template-rows: auto 1fr;
            grid-template-areas: "phone top" "phone side";
            gap: 1rem 2rem;
            padding: 1.25rem;
            border: 1px solid var(--hp-line);
            border-radius: 2.3rem;
            background: var(--hp-bg-2);
            box-shadow: 0 50px 120px -40px rgba(0, 0, 0, 0.8);
        }
        .ex-fit-top { grid-area: top; display: flex; align-items: center; gap: 0.4rem; }
        .ex-fit-count { margin-inline: 0.35rem auto; font-family: var(--hp-mono); font-size: 0.78rem; font-weight: 700; letter-spacing: 0.08em; color: var(--hp-ink-3); }
        #hp .ex-fit-btn { display: grid; flex: none; place-items: center; width: 2.75rem; height: 2.75rem; border: 1px solid var(--hp-line-2); border-radius: 0.9rem; background: var(--hp-bg); color: var(--hp-ink); cursor: pointer; transition: border-color 0.2s ease, transform 0.2s ease; }
        #hp .ex-fit-btn:hover { border-color: var(--hp-blue); transform: translateY(-1px); }
        .ex-fit-btn svg { width: 1.15rem; height: 1.15rem; }
        .ex-fit-btn.is-back svg { transform: scaleX(-1); }
        .ex-fit-phone {
            grid-area: phone;
            width: min(398px, 46vw);
            padding: 0.25rem;
            border-radius: 2rem;
            background: #0a0f1e;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14), 0 40px 80px -36px rgb(var(--glow, 96 124 200) / 0.9);
        }
        html.es-anim dialog.ex-fit[open] .ex-fit-phone { animation: ex-in 0.45s cubic-bezier(0.2, 0.7, 0.2, 1) both; }
        .ex-fit-screen {
            position: relative;
            height: min(78vh, 46rem);
            height: min(78dvh, 46rem);
            overflow-y: auto;
            overscroll-behavior: contain;
            border-radius: 1.75rem;
            container-type: inline-size;
            background: #fff;
            scrollbar-width: none;
        }
        .ex-fit-screen::-webkit-scrollbar { display: none; }
        .ex-fit-page { position: relative; }
        .ex-fit-page img { display: block; width: 100%; height: auto; }
        .ex-fit-side { grid-area: side; display: flex; flex-direction: column; align-items: flex-start; min-width: 0; padding-bottom: 0.25rem; }
        #hp .ex-fit-name { margin-top: 0.4rem; font-size: clamp(1.5rem, 2.4vw, 2.1rem); font-weight: 700; font-variation-settings: 'wght' 820; letter-spacing: -0.035em; line-height: 1.08; }
        .ex-fit-host { margin-top: 0.5rem; font-family: var(--hp-mono); font-size: 0.82rem; overflow-wrap: anywhere; color: var(--hp-ink-3); }
        {{-- The name can be tried here too, and the other seventeen looks are one press away. --}}
        .ex-fit-try { display: grid; gap: 0.45rem; width: 100%; margin-top: 1.25rem; }
        .ex-fit-label { font-size: 0.86rem; font-weight: 700; font-variation-settings: 'wght' 680; color: var(--hp-ink-2); }
        #hp .ex-fit-try .hp-claim { padding-block: 0.8rem; box-shadow: none; }
        .ex-fit-hint { margin-top: 0.9rem; font-size: 0.86rem; line-height: 1.5; color: var(--hp-ink-3); }
        {{-- The kind of schedule, in the dialog: the small mono label it had before the wall changed. --}}
        .ex-fit-side .ex-kind { font-family: var(--hp-mono); font-size: 0.66rem; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-ink-3); }
        .ex-fit-looks { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.4rem; width: 100%; margin-top: 1.25rem; }
        #hp .ex-fit-look { position: relative; display: block; aspect-ratio: 1; overflow: hidden; padding: 0; border-radius: 0.7rem; background: var(--hp-bg-3); box-shadow: 0 0 0 1px var(--hp-line-2); cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .ex-fit-look img { display: block; width: 100%; height: 100%; object-fit: cover; }
        #hp .ex-fit-look:hover { transform: translateY(-2px); }
        #hp .ex-fit-look[aria-pressed="true"] { box-shadow: 0 0 0 3px var(--hp-blue); }
        .ex-fit-actions { display: flex; flex-wrap: wrap; gap: 0.6rem; width: 100%; margin-top: auto; padding-top: 1.5rem; }
        .ex-fit-actions .hp-btn { flex: 1 1 0; min-width: 0; padding-inline: 0.9rem; }
        @media (max-width: 719.98px) {
            {{-- On a phone it is the whole screen, and the page in it is the size it really is. --}}
            #hp dialog.ex-fit { width: 100%; height: 100vh; height: 100dvh; margin: 0; }
            .ex-fit-in { grid-template-columns: minmax(0, 1fr); grid-template-rows: auto minmax(0, 1fr) auto; grid-template-areas: "top" "phone" "side"; gap: 0.6rem; height: 100%; padding: 0.6rem; border: 0; border-radius: 0; }
            .ex-fit-phone { width: 100%; height: 100%; min-height: 0; padding: 0; border-radius: 1.1rem; overflow: hidden; }
            .ex-fit-screen { height: 100%; border-radius: 1.1rem; }
            .ex-fit-side { display: grid; grid-template-columns: minmax(0, 1fr); padding: 0 0.15rem 0.15rem; }
            #hp .ex-fit-name { margin-top: 0; font-size: 1.15rem; }
            .ex-fit-side .ex-kind,
            .ex-fit-host,
            .ex-fit-hint,
            .ex-fit-try { display: none; }
            {{-- The looks are one row to swipe, above the two buttons. --}}
            .ex-fit-looks { display: flex; gap: 0.35rem; margin-top: 0.5rem; overflow-x: auto; scrollbar-width: none; }
            .ex-fit-looks::-webkit-scrollbar { display: none; }
            #hp .ex-fit-look { flex: none; width: 2.75rem; }
            .ex-fit-actions { flex-wrap: nowrap; margin-top: 0; padding-top: 0.5rem; }
            .ex-fit-actions .hp-btn { padding-inline: 0.6rem; font-size: 0.9rem; }
        }

        {{-- ---------- look closer ---------- --}}
        .ex-wide { width: min(100% - 2.5rem, 92rem); margin-inline: auto; }
        {{-- The band's own colour, and the same colour clouded, for what stays on top of it while the
           list passes: said outright for each light, so nothing depends on mixing it in the browser. --}}
        #parts { --ex-band: #050814; --ex-veil: rgba(5, 8, 20, 0.86); }
        .dark #parts { --ex-band: #111d5a; --ex-veil: rgba(17, 29, 90, 0.86); }
        .ex-xray { --glow: 198 137 31; display: grid; gap: clamp(1.5rem, 4vw, 3.5rem); margin-top: clamp(2.5rem, 5vw, 4rem); }
        .ex-stage { position: relative; display: grid; gap: 0.5rem; width: min(100%, 34rem); margin-inline: auto; }
        .ex-browser { overflow: hidden; isolation: isolate; border: 1px solid rgba(125, 165, 255, 0.28); border-radius: 0.9rem; background: #101831; }
        .ex-browser-bar { position: relative; display: flex; align-items: center; gap: 0.4rem; padding: 0.55rem 0.7rem; }
        .ex-browser-bar > i { flex: none; width: 0.62rem; height: 0.62rem; border-radius: 999px; background: rgba(255, 255, 255, 0.2); }
        .ex-addr { position: relative; flex: 1 1 0; min-width: 0; overflow: hidden; margin-inline-start: 0.5rem; padding: 0.32rem 0.9rem 0.32rem 2.75rem; border-radius: 0.55rem; background: rgba(255, 255, 255, 0.07); font-family: var(--hp-mono); font-size: 0.8rem; text-overflow: ellipsis; white-space: nowrap; color: #c5cde2; }
        .ex-view { position: relative; overflow: hidden; background: #fff; }
        .ex-shot { position: relative; container-type: inline-size; }
        .ex-shot img { display: block; width: 100%; height: auto; }
        .ex-handset { position: relative; padding: 0.3rem; border-radius: 1.2rem; background: #0a0f1e; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.2), 0 40px 80px -30px rgba(0, 0, 0, 0.95); }
        .ex-handset .ex-view { border-radius: 0.95rem; }
        {{-- Below a laptop there is one frame, as wide as the column, and it shows the schedule or
           one of its events: whichever holds the part that was pressed. --}}
        .ex-tabs { display: flex; gap: 0.25rem; padding: 0.25rem; border-radius: 0.8rem; background: rgba(255, 255, 255, 0.07); }
        #hp .ex-tab { flex: 1 1 0; min-height: 2.5rem; border-radius: 0.6rem; font-size: 0.9rem; font-weight: 700; font-variation-settings: 'wght' 680; color: #c5cde2; cursor: pointer; transition: background-color 0.2s ease, color 0.2s ease; }
        #hp .ex-tab[aria-pressed="true"] { background: #eef2ff; color: #0a1020; }
        @media (max-width: 1099.98px) {
            .ex-browser > .ex-view { display: none; }
            .ex-handset { display: none; }
            .ex-handset.is-shown { display: block; }
            {{-- The frame is the column's width and about a third of the window tall, whatever the
               window: sized by its width alone it left a tablet no room for the list under it. --}}
            .ex-handset .ex-view { height: min(30vh, 14rem); }
            .ex-hold { display: grid; gap: 0.5rem; }
        }
        @media (max-width: 759.98px) {
            {{-- On a phone the stage is not a box of its own: the address goes by with the page, and
               what holds the tabs and the frame is a child of the whole section, so it can stay. --}}
            .ex-stage { display: contents; }
            .ex-browser,
            .ex-hold { width: min(100%, 34rem); margin-inline: auto; }
            .ex-hold { position: relative; padding-block: 0.5rem 1rem; }
            .ex-browser-bar > i { display: none; }
            .ex-addr { margin-inline-start: 0; }
        }
        @media (max-width: 759.98px) and (min-height: 600px) {
            {{-- The frame stays under the header while the list passes beneath it. Not in a window
               too short to hold it (a phone on its side): there it would cover the list. --}}
            .ex-xray[data-ready] .ex-hold { position: sticky; top: 4rem; z-index: 3; }
            {{-- Its ground is the band's own colour, clouded, so the band's light still shows through
               it and it has no hard edge; it runs a little past the frame and then thins out. --}}
            .ex-xray[data-ready] .ex-hold::before,
            .ex-xray[data-ready] .ex-hold::after { content: ""; position: absolute; inset-inline: -50vw; z-index: -1; }
            .ex-xray[data-ready] .ex-hold::before {
                inset-block: 0;
                background: var(--ex-veil);
                -webkit-backdrop-filter: blur(14px);
                backdrop-filter: blur(14px);
            }
            .ex-xray[data-ready] .ex-hold::after {
                top: 100%;
                height: 1.25rem;
                background: linear-gradient(to bottom, var(--ex-veil), transparent);
            }
        }
        @media (min-width: 760px) and (max-width: 1099.98px) {
            {{-- A tablet: the frame at a phone's own width on one side, the list on the other. --}}
            .ex-xray { grid-template-columns: minmax(0, 24.5rem) minmax(0, 1fr); align-items: start; }
            .ex-stage { width: 100%; margin: 0; }
            .ex-handset .ex-view { height: min(56vh, 30rem); }
        }
        @media (min-width: 760px) and (max-width: 1099.98px) and (min-height: 640px) {
            .ex-xray[data-ready] .ex-stage { position: sticky; top: 5rem; }
        }
        @media (max-width: 1099.98px) {
            {{-- Without the script both pictures stand, one under the other, and there is nothing to switch. --}}
            .ex-xray:not([data-ready]) .ex-handset { display: block; }
            .ex-xray:not([data-ready]) .ex-tabs { display: none; }
        }
        {{-- And at any width a picture can be read to its foot by scrolling it in its frame. --}}
        .ex-xray:not([data-ready]) .ex-view { overflow-y: auto; }
        #hp .ex-pin {
            position: absolute;
            z-index: 2;
            display: grid;
            place-items: center;
            width: 1.7rem;
            height: 1.7rem;
            translate: calc(-100% - 0.3rem) -50%;
            border: 2px solid #fff;
            border-radius: 999px;
            background: #0a1020;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.5);
            font-family: var(--hp-mono);
            font-size: 0.74rem;
            font-weight: 700;
            font-variation-settings: normal;
            line-height: 1;
            color: #fff;
            cursor: pointer;
            transition: scale 0.25s cubic-bezier(0.2, 0.7, 0.2, 1), background-color 0.2s ease, box-shadow 0.25s ease;
        }
        {{-- A number stands beside its part, never on it: before it, after it, or on its top edge. --}}
        #hp .ex-pin.is-after { translate: 0.3rem -50%; }
        #hp .ex-pin.is-top { translate: -50% -62%; }
        #hp .ex-addr .ex-pin { left: 1.05rem; top: 50%; width: 1.45rem; height: 1.45rem; translate: -50% -50%; font-size: 0.68rem; }
        #hp .ex-pin::before { content: ""; position: absolute; inset: -0.6rem; border-radius: 999px; }
        #hp .ex-pin:hover { scale: 1.12; }
        #hp .ex-pin.is-on { background: #2f66ea; scale: 1.28; box-shadow: 0 0 0 0.4rem rgba(78, 129, 250, 0.38), 0 8px 22px rgba(0, 0, 0, 0.55); }
        @keyframes ex-ping {
            from { box-shadow: 0 0 0 0 rgba(78, 129, 250, 0.7), 0 8px 22px rgba(0, 0, 0, 0.55); }
            to { box-shadow: 0 0 0 0.4rem rgba(78, 129, 250, 0.38), 0 8px 22px rgba(0, 0, 0, 0.55); }
        }
        html.es-anim #hp .ex-pin.is-on { animation: ex-ping 0.5s ease-out; }
        @media (min-width: 1100px) {
            .ex-xray { grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); align-items: start; }
            .ex-stage { display: block; width: auto; margin: 0; padding: 0 2.5rem 7rem 0; }
            .ex-browser { border-radius: 1rem; box-shadow: 0 50px 100px -40px rgba(0, 0, 0, 0.95), 0 0 140px -50px rgb(var(--glow) / 0.75); }
            .ex-browser-bar { padding: 0.65rem 0.9rem; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
            .ex-browser .ex-view { aspect-ratio: 1280 / 810; }
            .ex-tabs { display: none; }
            .ex-hold { display: contents; }
            .ex-handset { border-radius: 1.6rem; }
            .ex-handset .ex-view { aspect-ratio: 390 / 740; border-radius: 1.32rem; }
            .ex-handset.is-home { display: none; }
            .ex-handset.is-event { position: absolute; right: 0; bottom: 0; width: 30%; }
        }
        @media (min-width: 1500px) and (min-height: 900px) {
            {{-- Room for more of the page, and for more of its numbers at once. --}}
            .ex-browser .ex-view { aspect-ratio: 1280 / 930; }
        }
        @media (min-width: 1100px) and (min-height: 780px) {
            {{-- The pictures stay beside the list while it passes, in a window tall enough to hold them. --}}
            .ex-xray[data-ready] .ex-stage { position: sticky; top: 6rem; }
        }
        {{-- No pictures: the list alone, in a column of its own. --}}
        .ex-xray.is-bare { grid-template-columns: minmax(0, 46rem); justify-content: center; }

        @media (max-width: 1099.98px) {
            {{-- One column under the frame, no wider than reads well. (Two columns were tried: a row
               that opens either moves its neighbours to the other column or leaves a hole beside it.) --}}
            .ex-partscol { width: min(100%, 44rem); margin-inline: auto; }
        }
        @media (min-width: 760px) and (max-width: 1099.98px) {
            .ex-partscol { width: auto; margin: 0; }
        }
        {{-- The best thing the list has to say, said first and large. --}}
        #hp .ex-count { margin-bottom: 1rem; padding-inline: 0.8rem; font-size: clamp(1.3rem, 1vw + 1rem, 1.6rem); font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.025em; line-height: 1.2; color: #eef2ff; }
        #hp .ex-count b { background: var(--hp-grad-lit); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; font-variation-settings: 'wght' 840; }
        .ex-parts { border-bottom: 1px solid rgba(255, 255, 255, 0.12); }
        .ex-part { border-top: 1px solid rgba(255, 255, 255, 0.12); transition: background-color 0.25s ease; }
        .ex-parts[data-ready] .ex-part.is-on { border-top-color: transparent; border-radius: 0.9rem; background: rgba(125, 165, 255, 0.13); box-shadow: inset 0 0 0 1px rgba(125, 165, 255, 0.3); }
        .ex-parts[data-ready] .ex-part.is-on + .ex-part { border-top-color: transparent; }
        #hp .ex-part-btn {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            align-items: start;
            gap: 0.85rem;
            width: 100%;
            padding: 0.85rem 0.8rem;
            text-align: start;
            color: #eef2ff;
            cursor: pointer;
        }
        .ex-n { display: grid; place-items: center; width: 1.6rem; height: 1.6rem; border: 1.5px solid rgba(255, 255, 255, 0.4); border-radius: 999px; font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; color: #c5cde2; transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease; }
        @media (min-width: 1100px) {
            .ex-part.is-off-wide .ex-n { border-style: dashed; }
        }
        @media (max-width: 1099.98px) {
            .ex-part.is-off-narrow .ex-n { border-style: dashed; }
        }
        .ex-see { padding-top: 0.08rem; font-size: 1rem; font-weight: 700; font-variation-settings: 'wght' 700; letter-spacing: -0.01em; line-height: 1.35; transition: color 0.2s ease; }
        .ex-part-btn:hover .ex-see { color: #a9c3ff; }
        .ex-part.is-on .ex-n { border-color: #2f66ea; background: #2f66ea; color: #fff; }
        {{-- Only what costs something carries a plan: eleven "Free" marks in a column were a table. --}}
        .ex-plan { display: inline-flex; align-items: center; min-height: 1.5rem; margin-top: 0.05rem; padding: 0 0.55rem; border: 1px solid rgba(255, 255, 255, 0.6); border-radius: 999px; font-family: var(--hp-mono); font-size: 0.64rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #fff; }
        .ex-plan.is-ent { border-color: rgba(251, 191, 36, 0.6); color: #fcd34d; }
        .ex-part-body { display: grid; grid-template-rows: 1fr; }
        .ex-part-body > div { overflow: hidden; }
        .ex-part-body p { padding: 0 0.9rem 1.1rem 3.25rem; font-size: 1rem; line-height: 1.55; color: #dde4f5; }
        .ex-part-body .ex-where { display: none; margin-top: 0.4rem; font-family: var(--hp-mono); font-size: 0.68rem; letter-spacing: 0.08em; text-transform: uppercase; color: #9fb1d6; }
        @media (min-width: 1100px) {
            .ex-part-body .ex-where.is-wide { display: block; }
        }
        @media (max-width: 1099.98px) {
            .ex-part-body .ex-where.is-narrow { display: block; }
        }
        .ex-parts[data-ready] .ex-part-body { grid-template-rows: 0fr; transition: grid-template-rows 0.3s ease; }
        {{-- A closed row's sentence is not read out either. --}}
        .ex-parts[data-ready] .ex-part-body > div { visibility: hidden; transition: visibility 0s linear 0.3s; }
        .ex-parts[data-ready] .ex-part.is-on .ex-part-body > div { visibility: visible; transition-delay: 0s; }
        .ex-parts[data-ready] .ex-part.is-on .ex-part-body { grid-template-rows: 1fr; }
        .ex-note { max-width: 46rem; margin: clamp(2rem, 4vw, 3rem) auto 0; text-align: center; font-size: 0.98rem; line-height: 1.6; color: #c5cde2; }
        .ex-note small { display: block; margin-top: 0.75rem; font-size: 0.86rem; color: #9fb1d6; }

        {{-- ---------- the last panel: the three steps under the box ---------- --}}
        .ex-ends { display: grid; gap: 1rem 2rem; max-width: 52rem; margin: clamp(2rem, 4vw, 3rem) auto 0; padding-top: clamp(1.5rem, 3vw, 2.25rem); border-top: 1px solid rgba(255, 255, 255, 0.14); text-align: start; }
        @media (min-width: 800px) {
            .ex-ends { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        .ex-ends li { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.75rem; font-size: 0.95rem; line-height: 1.5; color: #c5cde2; }
        .ex-ends li > b { font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; letter-spacing: 0.08em; line-height: 1.9; color: #a9c3ff; }
        .ex-ends strong { display: block; color: #fff; }
        @media (max-width: 479.98px) {
            {{-- Room in the box for a name and its ending on one line. --}}
            #hp #claim .hp-finale { padding-inline: 0.75rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .ex-clip,
            .ex-glow,
            .ex-frame,
            .ex-worn,
            .ex-worn > span,
            .ex-unit,
            .ex-title > span,
            .ex-room,
            #hp .ex-pin,
            #hp .ex-fit-btn,
            .ex-parts[data-ready] .ex-part-body { transition: none !important; animation: none !important; }
            .ex-unit:hover .ex-clip,
            .ex-unit:focus-within .ex-clip,
            .ex-blank:hover .ex-clip { transform: none; }
            .ex-bar.is-on .ex-bar-in { animation: none !important; }
        }
    </style>

    @php
        $roomTitles = ['Springfield' => 'Springfield Demo Town'];

        // What each room is for, and the page written for that trade. Each note describes only
        // free parts: recurring dates, a capacity, a page per date, a calendar.
        $roomNotes = [
            'Fitness & Wellness' => ['Classes that repeat, a retreat with dates of its own, and a cap on how many can come.', '/for-fitness-and-yoga', 'Event Schedule for fitness and yoga'],
            'Music & Entertainment' => ['A lineup that changes every week, with a page per date worth linking to.', '/for-music-venues', 'Event Schedule for music venues'],
            'Community & Recreation' => ['Volunteer-run programs where the calendar is the whole website.', '/for-community-centers', 'Event Schedule for community centers'],
            'Creative & Workshops' => ['Small-group sessions where the date and the sign-up are the same page.', '/for-workshop-instructors', 'Event Schedule for workshops'],
            'Springfield' => ['One fictional town on six schedules: the bar, the bowling alley, the cinema, the donut shop, the amphitheater and the town\'s own page.', '/use-cases', 'See all use cases'],
        ];

        // What kind of thing each schedule is. Read off its own name and blurb,
        // never a claim about which plan or features that demo uses.
        $unitKinds = [
            'meditationclasses' => 'Mindfulness',
            'weekendyogaretreat' => 'Retreat',
            'hikingclub' => 'Outdoors',
            'battleofthebands' => 'Competition',
            'sufficientgroundscoffeemusic' => 'Cafe',
            'villageidiot' => 'Pub',
            'communityyouthgroup' => 'Youth',
            'karateclub' => 'Dojo',
            'countyfairgrounds' => 'Fairground',
            'nateswoodworkingshop' => 'Crafts',
            'painting' => 'Art studio',
            'pagesbooknookshop' => 'Bookshop',
            'simpsons' => 'Town',
            'demo-moestavern' => 'Bar',
            'demo-amphitheater' => 'Amphitheater',
            'demo-bowlarama' => 'Bowling',
            'demo-aztectheater' => 'Cinema',
            'demo-lardlad' => 'Donuts',
        ];

        // What each kind of schedule is called on the page, how many of it there are, and its line.
        $rooms = [];
        foreach ($categories as $roomName => $roomUnits) {
            $rooms[\Illuminate\Support\Str::slug($roomName)] = [
                'title' => $roomTitles[$roomName] ?? $roomName,
                'count' => count($roomUnits),
                'note' => $roomNotes[$roomName][0] ?? null,
                'href' => $roomNotes[$roomName][1] ?? null,
                'more' => $roomNotes[$roomName][2] ?? null,
            ];
        }
        // The made-up town stands on a wall of its own, under the hand-made examples.
        $townKey = \App\Utils\ExampleSchedules::townKey();
        // Every schedule on the page, in the order the two walls show them.
        $wall = array_merge($walls['examples']['units'] ?? [], $walls['town']['units'] ?? []);

        // How many pictures a name is set over, in the words the page uses for it: every one the
        // wall shows. Every sentence that counts them reads this.
        $tryCount = count($wall);
        $tryWords = 'all ' . $tryCount;
        // The page speaks of trying a name on whenever it has a wall, and of seeing a schedule's
        // page only when every one of them has its photograph.
        $tryOn = $tryCount > 0;
        $fitCount = collect($wall)->filter(fn ($wallUnit) => ! empty($shots[$wallUnit['subdomain']]['full']['file']))->count();
        $fitAll = $fitCount > 0 && $fitCount === count($wall);
        // With a photograph missing for even one, a press opens the live page for all of them:
        // otherwise the page would say one thing and do another.
        $fitting = $fitAll;
        // A photograph that is taken again keeps its address, and a browser keeps a picture for a
        // month: the day it was taken goes on the address, so the new measurements never stand
        // over an old picture.
        $shotVersion = ! empty($shotsDate) && preg_match('/^\d{4}-\d{2}-\d{2}\z/', (string) $shotsDate) ? '?v=' . $shotsDate : '';

        $claimHost = _base_domain();
        $claimSuffix = '.' . (str_contains($claimHost, '.') && ! filter_var($claimHost, FILTER_VALIDATE_IP) ? $claimHost : 'eventschedule.com');
        $shotDir = \App\Utils\ExampleSchedules::PAGES_DIR;
        $shotsTaken = ! empty($shotsDate) ? \Carbon\Carbon::parse($shotsDate)->format('F j, Y') : null;
        // What the script says instead once it can bring a page forward: without it, a picture is a link.
        $statusLine = 'Press any one to open the live page.';
        $statusFit = 'Press any one to see its page.';
        $takenLine = 'The page as a phone shows it' . ($shotsTaken ? ', photographed on ' . $shotsTaken : '') . '.';

        // Where each row of a wall ends, at each breakpoint: a mark the stylesheet shows at one
        // breakpoint only, as /browse's wall does (the rows themselves are decided in
        // App\Utils\ExampleSchedules, by App\Utils\PosterWall).
        $wallProfiles = array_keys(\App\Utils\PosterWall::PROFILES);
        $breaksAfter = function (array $set, int $index) use ($wallProfiles) {
            $marks = '';
            foreach ($wallProfiles as $profile) {
                if ($index === count($set['tiles']) - 1 && ($set['rows'][$profile]['pad'] ?? 0) > 0) {
                    $marks .= '<i class="ex-pad ex-pad-' . $profile . '" style="--p: ' . (float) $set['rows'][$profile]['pad'] . ';" aria-hidden="true"></i>';
                }
                if (in_array($index, $set['rows'][$profile]['ends'], true)) {
                    $marks .= '<i class="ex-brk ex-brk-' . $profile . '" aria-hidden="true"></i>';
                }
            }

            return $marks;
        };

        // The spec sheet: what is visible on any of these pages, what that
        // part is called in Event Schedule, and what it costs to build.
        // Every row is checked against docs/FEATURES.md.
        $spec = [
            ['The address in the bar, like hikingclub.eventschedule.com', 'A schedule of your own on a subdomain, with nothing to install or host', 'Free'],
            ['A month grid, or a plain list of what is coming up', 'The calendar or list layout, set once per schedule', 'Free'],
            ['A class every Tuesday that nobody retyped fifty times', 'Recurring dates, with exceptions for the days you skip', 'Free'],
            ['Color-coded strands inside one schedule', 'Sub-schedules', 'Free'],
            ['Every act on the bill, including the ones with no account', 'The lineup on each event. A performer or venue you name who is not on Event Schedule gets a page of its own that says you listed them, and they can claim it with the email address on it', 'Free'],
            ['Add to Google, Apple or Outlook, or subscribe to every event on the schedule', 'A calendar file per date, plus a live calendar feed of the whole schedule that updates itself when a date moves', 'Free'],
            ['Tell me when tickets go on sale, on a date that is not selling yet', 'The interest list, once you switch on the "Notify me" card: an email address and nothing else, then a message when tickets go on sale, if it is cancelled and shortly before it starts, plus any notice you choose to send if the date or venue changes. It does not use your newsletter allowance', 'Free'],
            ['The Follow button under the schedule name', 'An audience you can reach. A visitor who signs up with a name and email address and confirms it gets a digest of your new events automatically, plus any newsletter you write: 10 emails a month on Free, counted per recipient', 'Free'],
            ['Save me a place, and the count of places left', 'Free registration with an optional capacity, per date', 'Free'],
            ['Buy a ticket without leaving the page', 'Ticket types and checkout through Stripe or PayPal, with zero platform fees on every plan. A ticket type set at no charge goes out on Free; one with a price on it is Pro', 'Pro'],
            ['Photos and comments from the people who came', 'Fan photos, video and comments, held in an approval queue. Free covers 25 photos per schedule', 'Free'],
            ['The small "Event Schedule" chip in the corner of a free schedule', 'The free-plan credit, a small link back to eventschedule.com. Removing it is part of Pro', 'Pro'],
            ['A code shown at the door, and scanned on the way in', 'QR scanning, on every plan, whether the place was paid for or simply kept. The live check-in dashboard is the Pro half', 'Free'],
            ['A schedule on its own domain rather than a subdomain', 'Custom domains', 'Enterprise'],
        ];

        // Counted, not asserted: the sentences beside the list quote these,
        // so they cannot drift out of step with the rows.
        $specFree = count(array_filter($spec, fn ($specRow) => $specRow[2] === 'Free'));

        // Where each numbered part stands in the three photographs of one schedule: the row of
        // $spec (from one) and the name the photographing command measured it under. A number
        // stands beside its part and never on it: before the part, after it, or on its top edge
        // where both sides are taken. Unsaid, it is before, or after for a part that starts too
        // near the picture's left edge for a number to stand there. A part that is in no picture
        // keeps its row and has no pin.
        $pinMap = [
            'xr-desk-home' => [2 => ['layout', 'top'], 4 => 'chips', 5 => 'lineup', 8 => 'follow', 9 => ['free', 'after'], 11 => 'fan'],
            'xr-phone-home' => [4 => ['chips', 'top'], 8 => ['follow', 'top'], 9 => 'free'],
            'xr-phone-event' => [3 => ['weekly', 'after'], 5 => 'lineup', 6 => 'calendar', 10 => ['tickets', 'top']],
        ];
        $pinsOf = function (string $picture) use ($pinMap, $xray) {
            $pinsOut = [];
            $width = (float) ($xray[$picture]['w'] ?? 0);
            $height = (float) ($xray[$picture]['h'] ?? 0);
            if ($width <= 0 || $height <= 0) {
                return $pinsOut;
            }
            foreach ($pinMap[$picture] ?? [] as $partNo => $pin) {
                [$pinKey, $place] = array_pad((array) $pin, 2, null);
                $at = $xray[$picture]['pins'][$pinKey] ?? null;
                if (! is_array($at) || ! isset($at['x'], $at['y'], $at['w'], $at['h'])) {
                    continue;
                }
                $place ??= $at['x'] / $width < 0.17 ? 'after' : 'before';
                $x = match ($place) {
                    'after' => $at['x'] + $at['w'],
                    'top' => $at['x'] + min($at['w'] * 0.3, 40),
                    default => $at['x'],
                };
                $y = $place === 'top' ? $at['y'] : $at['y'] + $at['h'] / 2;
                $pinsOut[$partNo] = [
                    'left' => round(min(100, max(0, $x / $width * 100)), 2),
                    'top' => round(min(100, max(0, $y / $height * 100)), 2),
                    'place' => $place,
                ];
            }

            return $pinsOut;
        };
        $hasXray = ! empty($xray['xr-desk-home']['file']) && ! empty($xray['xr-phone-event']['file']);
        // From a laptop up the pictures are the browser and the event; below that, the address
        // and the two phones. A part pinned only in the browser's picture (the layout switch, fan
        // content) has no pin on a phone, and its row says so there.
        $pinnedWide = $hasXray ? [1 => true] + array_fill_keys(array_keys($pinsOf('xr-desk-home') + $pinsOf('xr-phone-event')), true) : [];
        $pinnedNarrow = $hasXray ? [1 => true] + array_fill_keys(array_keys($pinsOf('xr-phone-home') + $pinsOf('xr-phone-event')), true) : [];
        $xrayName = "Moe's Tavern";
        $xraySlug = 'demo-moestavern';

        // The name on a photograph: where the page's own heading stood, in its typeface. Every
        // value was read off a page somebody else can edit (the Springfield demos are open to
        // anyone), so each is a number, or is checked against the one shape it may have.
        $titleStyle = function (array $title, $pictureWidth, $pictureHeight) {
            $font = (string) ($title['font'] ?? '');
            $ink = (string) ($title['color'] ?? '');
            $weight = (int) ($title['weight'] ?? 700);

            return '--pw: ' . (float) $pictureWidth . '; --x: ' . (float) ($title['x'] ?? 0) . '; --y: ' . (float) ($title['y'] ?? 0) . '; --w: ' . (float) ($title['w'] ?? 0) . '; --th: ' . (float) ($title['h'] ?? 0) . '; --h: ' . (float) $pictureHeight
                . '; --fs: ' . (float) ($title['size'] ?? 32) . '; --wt: ' . ($weight >= 100 && $weight <= 900 ? $weight : 700)
                . '; --ink: ' . (preg_match('/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9., ]{5,30}\))\z/', $ink) ? $ink : '#151b26')
                . (preg_match('/^[A-Za-z0-9 ]{1,60}\z/', $font) && font_stylesheet_url($font) ? '; --font: \'' . $font . '\'' : '') . ';'
                . (($title['align'] ?? 'center') === 'center' ? '' : ' --ta: flex-start; --tx: start;');
        };
        $glowOf = fn ($shot) => preg_match('/^\d{1,3} \d{1,3} \d{1,3}\z/', (string) ($shot['glow'] ?? '')) ? $shot['glow'] : '96 124 200';

        // The three steps, a line each: they stand under the box in the last panel.
        $ends = [
            ['01', 'Claim the address.', 'No install, no hosting, no plugin to keep up to date.'],
            ['02', 'Put the events in.', 'By hand, by two-way calendar sync, or pasted in for AI to read.'],
            ['03', 'Open the doors.', 'Share the link, embed the calendar, or print the QR code.'],
        ];

        $faqs = [
            [
                'q' => 'Can I create a schedule like these?',
                'a' => 'Yes. Everything it takes to publish a schedule like the ones on this page is free forever: unlimited events, recurring dates, sub-schedules, your own colors and header image, free registration with a capacity and no ceiling on it, two-way calendar sync and an embeddable calendar. Scanning those codes at the door is free on every plan too. Putting a price on a ticket is what '.plan_price($proMonthly).' a month buys, along with the live check-in dashboard. Event Schedule charges no platform fees on ticket sales, on any plan. One team member is included on the free plan; adding more people to a schedule is part of Enterprise, capped at five.',
            ],
            [
                'q' => 'Are these real schedules?',
                'a' => 'They are real published pages, and they are demos. Event Schedule built them to show different kinds of programming side by side, from fitness and music to community groups and workshops.' . (count($wall) ? ' Each picture in the wall is that schedule\'s own header image, with its logo.' . ($fitAll ? ' Press one to see its page as a phone shows it, and a button there opens the live page at its own address.' : ' Pressing one opens the live page at its own address.') : ' Each one is live at its own address.'),
            ],
            ...($tryOn ? [[
                'q' => 'What happens when I type a name?',
                'a' => 'The name you type is set over every picture in the wall' . ($fitAll ? ', in that schedule\'s own typeface, and on its page when you press one' : '') . ', so you can see how yours would read. Nothing is saved and nothing is sent until you press the button, which takes the address made from the name with you to sign-up. A new schedule does not start with one of these looks: the header image, the colors and the typeface are settings you choose.',
            ]] : []),
            [
                'q' => 'How long does it take to set up?',
                'a' => 'Signing up, naming the schedule and publishing a first event takes a few minutes, and none of it needs technical skill. Add events by hand, or paste the details and let AI pull out the date, time and venue: AI parsing is on the free plan too, with a daily cap. If your events are already in Google Calendar, Outlook or any CalDAV calendar, connect it and they sync both ways instead of being retyped.',
            ],
            [
                'q' => 'Can I see how one of these looks on a bigger screen?',
                'a' => 'Open any of them on a laptop. These are the same public pages an audience gets, and the guest layout is responsive, so one schedule reflows to whatever screen it is opened on rather than being maintained twice.',
            ],
            [
                'q' => 'Can I put a calendar like this on my own website?',
                'a' => 'Yes, on every plan. The embeddable calendar drops your schedule into a page on the site you already have, so the calendar lives in two places without being maintained in two places. Embedding the registration form is free as well, and embedding the ticket purchase form is a Pro feature.',
            ],
        ];

        $arrow = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
        $down = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>';
        $away = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>';
        $cross = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
    @endphp

    {{-- ============================================================
         1. Hero: the claim, and the box the whole page listens to
         ============================================================ --}}
    <section id="top" class="es-hero hp-hero is-short ex-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>
        <div class="hp-hero-copy">
            <h1 class="hp-h1">
                <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">
                    <span class="hp-live" aria-hidden="true"><i></i></span>
                    <span>Event Schedule examples</span>
                </x-marketing.hero-eyebrow>
                <span class="es-mask"><span class="es-mask-line">{{ $scheduleCount }} real schedules.</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="hp-ink-grad">Try yours on.</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 hp-sub">
                @if ($tryOn)
                    <span class="ex-not-phone">Each one is a real events page, published with Event Schedule at its own address. Type the name of yours and see it on every one of them.</span>
                    <span class="ex-phone-only">Type your schedule's name and see it on {{ $tryWords }}.</span>
                @else
                    Each one is a published Event Schedule page at its own address. Open any of them, then build yours out of the same parts.
                @endif
            </p>

            <div class="es-fade-up es-d-3 hp-claimrow ex-namerow">
                <div class="ex-namecol">
                    <label for="ex-name" class="sr-only">Your schedule's name</label>
                    <div class="hp-claim ex-namebox">
                        <input id="ex-name" data-ex-namebox type="text" placeholder="Your schedule's name" autocomplete="off" autocapitalize="words" spellcheck="false" maxlength="40" enterkeyhint="go">
                    </div>
                    <p class="ex-addrline" dir="ltr"><span data-ex-slug>your-name</span>{{ $claimSuffix }}</p>
                </div>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary" data-claim-link>
                    Claim it free
                    {!! $arrow !!}
                </a>
            </div>

            @if ($tryOn)
                <a href="#wall" class="hp-btn hp-btn-ghost is-down ex-seeall" data-ex-seeall hidden>
                    See it on {{ $tryWords }}
                    {!! $down !!}
                </a>
            @endif

            <p class="es-fade-up es-d-4 hp-hero-foot ex-not-phone">
                <span>Free forever. No credit card required.</span>
            </p>
        </div>
    </section>

    {{-- ============================================================
         2. The walls: every demo's own picture
         ============================================================ --}}
    <section id="wall" class="ex-wall" aria-labelledby="wall-heading">
        <h2 id="wall-heading" class="sr-only">Example schedules</h2>

        @if (count($wall))
            <div class="ex-rooms" role="group" aria-label="Show one kind of schedule" data-ex-rooms>
                <button type="button" class="ex-room" data-ex-room="" aria-pressed="true"><b aria-hidden="true">{{ count($walls['examples']['units']) }}</b>All of them<span class="sr-only">, {{ count($walls['examples']['units']) }} schedules</span></button>
                @foreach (collect($rooms)->except($townKey) as $roomKey => $room)
                    <button type="button" class="ex-room" data-ex-room="{{ $roomKey }}" aria-pressed="false"><b aria-hidden="true">{{ $room['count'] }}</b>{{ $room['title'] }}<span class="sr-only">, {{ $room['count'] }} schedules</span></button>
                @endforeach
            </div>

            <noscript>
                <p class="ex-status">
                    @foreach (collect($rooms)->except($townKey)->filter(fn ($room) => ! empty($room['href']))->values() as $room)
                        <a href="{{ marketing_url($room['href']) }}" class="hp-inline">{{ $room['more'] }}</a>@if (! $loop->last) &middot; @endif
                    @endforeach
                </p>
            </noscript>

            <div class="ex-status">
                <p data-ex-status aria-live="polite" tabindex="-1" data-own="{{ $statusLine }}" @if ($fitAll) data-fit="{{ $statusFit }}" @endif>{{ $statusLine }}</p>
                @foreach (collect($rooms)->except($townKey) as $roomKey => $room)
                    @if (! empty($room['note']))
                        <p class="ex-roomnote" data-ex-note="{{ $roomKey }}" tabindex="-1" hidden>
                            {{ $room['note'] }}
                            @if (! empty($room['href']))
                                <a href="{{ marketing_url($room['href']) }}" class="hp-more">{{ $room['more'] }} {!! $arrow !!}</a>
                            @endif
                        </p>
                    @endif
                @endforeach
            </div>

            {{-- The typefaces the photographed schedules set their own names in, from the fonts the
                 app bundles. Asked for here and not in the head: they are for type that stands in
                 pictures, and the headline should not wait on them. --}}
            @foreach (collect($shots)->pluck('title.font')->merge(collect($xray)->pluck('title.font'))->filter()->unique() as $shotFont)
                @if (is_string($shotFont) && preg_match('/^[A-Za-z0-9 ]{1,60}\z/', $shotFont) && font_stylesheet_url($shotFont))
                    <link rel="stylesheet" href="{{ font_stylesheet_url($shotFont) }}">
                @endif
            @endforeach

            @foreach ($walls as $wallKey => $set)
                @continue (! count($set['units']))

                @if ($wallKey === 'town')
                    {{-- The made-up town: demo data of another kind, on a wall of its own. --}}
                    <div class="ex-town" id="town">
                        <span class="hp-kicker">The model town</span>
                        <h2 class="hp-h3">{{ $rooms[$townKey]['title'] ?? 'Springfield Demo Town' }}</h2>
                        <p>
                            {{ $rooms[$townKey]['note'] ?? '' }}
                            @if (! empty($rooms[$townKey]['href']))
                                <a href="{{ marketing_url($rooms[$townKey]['href']) }}" class="hp-more">{{ $rooms[$townKey]['more'] }} {!! $arrow !!}</a>
                            @endif
                        </p>
                    </div>
                @endif

                <div class="ex-grid" role="list" data-ex-grid="{{ $wallKey }}" data-try="{{ $tryWords }}">
                    @foreach ($set['units'] as $unit)
                        @php
                            $shot = $shots[$unit['subdomain']] ?? null;
                            $shot = is_array($shot) ? $shot : null;
                            $unitTitle = $shot['title'] ?? null;
                            // The name under a picture is the name on that schedule's own page.
                            $unitName = $unitTitle['text'] ?? $unit['name'];
                            $unitKind = $unitKinds[$unit['subdomain']] ?? 'Demo';
                            $unitFull = $shot['full'] ?? null;
                            $unitFull = $fitting && is_array($unitFull) && ! empty($unitFull['file']) ? $unitFull : null;
                            $unitFont = (string) ($unitTitle['font'] ?? '');
                            $unitFont = preg_match('/^[A-Za-z0-9 ]{1,60}\z/', $unitFont) && font_stylesheet_url($unitFont) ? $unitFont : null;
                            $picture = $unit['picture'];
                            $ratio = $set['tiles'][$loop->index]['ratio'];
                            // Only the first wall's first row is on screen as the page opens.
                            $eager = $wallKey === 'examples' && $loop->index <= $set['opening'];
                        @endphp
                        <article class="ex-tile ex-unit" role="listitem" data-room="{{ $unit['room'] }}" style="--r: {{ $ratio }}; --rm: {{ min($ratio, 2.1) }}; --i: {{ $loop->index }};" @if ($wallKey === 'examples' && $loop->index <= $set['opening'] + 4) data-first @endif>
                            <a href="{{ $unit['url'] }}" target="_blank" rel="noopener" class="ex-link" aria-label="{{ $unitName }}, {{ $unitKind }}, {{ $unit['subdomain'] }}.eventschedule.com"
                                @if ($unitFull)
                                    data-full="{{ asset($shotDir . $unitFull['file']) . $shotVersion }}" data-full-pw="{{ (int) ($unitFull['pw'] ?? 0) }}" data-full-ph="{{ (int) ($unitFull['ph'] ?? 0) }}"
                                    data-kind="{{ $unitKind }}" data-name="{{ $unitName }}" data-host="{{ $unit['subdomain'] }}.eventschedule.com" data-glow="{{ $glowOf($shot) }}"
                                    @if ($picture['logo']) data-logo="{{ $picture['logo'] }}" @endif
                                    @if ($unitTitle) data-title="{{ $unitTitle['text'] }}" data-title-style="{{ $titleStyle($unitTitle, 390, $unitFull['h'] ?? 1640) }}" @endif
                                @endif></a>
                            <div class="ex-frame">
                                {{-- The same picture twice costs one request: once whole, once out of focus
                                     behind it, which is the light it throws on the wall. --}}
                                <img class="ex-glow" src="{{ $picture['src'] }}" alt="" aria-hidden="true" loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async">
                                <div class="ex-clip">
                                    <img class="ex-img" src="{{ $picture['src'] }}" alt="The header picture of {{ $unitName }}" width="{{ $picture['w'] }}" height="{{ $picture['h'] }}" loading="{{ $eager ? 'eager' : 'lazy' }}" @if ($eager && $loop->first) fetchpriority="high" @endif decoding="async">
                                    <span class="ex-worn" aria-hidden="true" data-ex-title data-own="" @if ($unitFont) style="--font: '{{ $unitFont }}';" @endif><span dir="auto"></span></span>
                                </div>
                            </div>
                            <div class="ex-cap">
                                @if ($picture['logo'])
                                    <img class="ex-logo" src="{{ $picture['logo'] }}" alt="" width="52" height="52" loading="lazy" decoding="async">
                                @endif
                                <div>
                                    <p class="ex-kind">{{ $unitKind }}</p>
                                    <h3 class="ex-name">{{ $unitName }}</h3>
                                    <p class="ex-url" dir="ltr">{{ $unit['subdomain'] }}.eventschedule.com</p>
                                </div>
                            </div>
                        </article>
                        {!! $breaksAfter($set, $loop->index) !!}
                    @endforeach

                    @if ($set['blank'])
                        @php
                            $blankAt = count($set['tiles']) - 1;
                            $blankShapes = collect($wallProfiles)->map(fn ($profile) => '--b' . $profile . ': ' . (float) ($set['rows'][$profile]['ratio'][$blankAt] ?? 2.0))->implode('; ');
                        @endphp
                        <div class="ex-tile ex-blank" role="listitem" style="{{ $blankShapes }};">
                            <a href="{{ app_url('/sign_up') }}" class="ex-frame" data-claim-link>
                                <span class="ex-clip">
                                    <span class="ex-blank-k">This space is free</span>
                                    <span class="ex-blank-t" data-ex-name data-own="Your schedule here">Your schedule here</span>
                                    <span class="ex-blank-u" dir="ltr"><span data-ex-slug>your-name</span>{{ $claimSuffix }}</span>
                                    <span class="ex-blank-go">Claim it free {!! $arrow !!}</span>
                                </span>
                            </a>
                        </div>
                        {!! $breaksAfter($set, $blankAt) !!}
                    @endif
                </div>
            @endforeach

            @if ($tryOn)
                <div class="ex-bar" data-ex-bar>
                    <div class="ex-bar-in">
                        <span class="ex-bar-txt">
                            <span><b data-ex-name>Your name</b> on {{ $tryWords }}</span>
                            <span class="ex-bar-url" dir="ltr"><span data-ex-slug>your-name</span>{{ $claimSuffix }}</span>
                        </span>
                        <button type="button" class="ex-bar-x" data-ex-reset aria-label="Put the schedules' own names back">{!! $cross !!}</button>
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary is-small" data-claim-link>Claim it free {!! $arrow !!}</a>
                    </div>
                </div>
            @endif
        @else
            <p class="ex-status">Demo schedules coming soon.</p>
        @endif
    </section>

    @if ($fitting)
        {{-- One schedule's page brought forward. Filled by the script from the picture that was pressed. --}}
        <dialog class="ex-fit" data-ex-fitroom aria-labelledby="ex-fit-name">
            <div class="ex-fit-in">
                <div class="ex-fit-top">
                    <button type="button" class="ex-fit-btn is-back" data-ex-fit-prev aria-label="The schedule before">{!! $arrow !!}</button>
                    <button type="button" class="ex-fit-btn" data-ex-fit-next aria-label="The next schedule">{!! $arrow !!}</button>
                    <span class="ex-fit-count" data-ex-fit-count aria-hidden="true"></span>
                    <span class="sr-only" aria-live="polite" data-ex-fit-said></span>
                    <button type="button" class="ex-fit-btn" data-ex-fit-close aria-label="Close">{!! $cross !!}</button>
                </div>
                <div class="ex-fit-phone" data-ex-fit-phone>
                    <div class="ex-fit-screen" data-ex-fit-screen tabindex="0" autofocus role="group" aria-label="The page, as a phone shows it. Scroll to read it.">
                        <div class="ex-fit-page">
                            <img data-ex-fit-img alt="" width="780" height="3280" decoding="async">
                            <span class="ex-title" aria-hidden="true" data-ex-title data-ex-fit-title data-own=""><span dir="auto"></span></span>
                        </div>
                    </div>
                </div>
                <div class="ex-fit-side">
                    <p class="ex-kind" data-ex-fit-kind></p>
                    <h2 class="ex-fit-name" id="ex-fit-name" data-ex-fit-name></h2>
                    <p class="ex-fit-host" dir="ltr" data-ex-fit-host></p>
                    @if ($tryOn)
                        <div class="ex-fit-try">
                            <label for="ex-name-fit" class="ex-fit-label">Try your schedule's name on it</label>
                            <div class="hp-claim ex-namebox">
                                <input id="ex-name-fit" data-ex-namebox type="text" placeholder="Your schedule's name" autocomplete="off" autocapitalize="words" spellcheck="false" maxlength="40" enterkeyhint="done">
                            </div>
                            <p class="ex-addrline" dir="ltr"><span data-ex-slug>your-name</span>{{ $claimSuffix }}</p>
                        </div>
                    @endif
                    <div class="ex-fit-looks" role="group" aria-label="The other schedules" data-ex-fit-looks></div>
                    <p class="ex-fit-hint">{{ $takenLine }} The picture, the colors and the events stay each demo's own.</p>
                    <div class="ex-fit-actions">
                        <a href="#" class="hp-btn hp-btn-ghost is-small is-still" target="_blank" rel="noopener" data-ex-fit-live>Open the live page {!! $away !!}</a>
                        <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary is-small" data-claim-link>Claim it free {!! $arrow !!}</a>
                    </div>
                </div>
            </div>
        </dialog>
    @endif

    {{-- ============================================================
         3. Look closer: one schedule, its parts numbered
         ============================================================ --}}
    <section id="parts" class="hp-dark">
        <div class="ex-wide">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>Look closer</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">One real schedule. <span class="hp-ink-grad">{{ count($spec) }} parts.</span></h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.16s;">
                    @if ($hasXray)
                        @if (! empty($xray['xr-desk-home']['title']) || ! empty($xray['xr-phone-home']['title']))
                            <span data-ex-plain>This is {{ $xrayName }}, one of the {{ $scheduleCount }}.</span><span data-ex-worn hidden>This is {{ $xrayName }}'s page, with <b data-ex-name>your name</b> on it.</span>
                        @else
                            This is {{ $xrayName }}, one of the {{ $scheduleCount }}.
                        @endif
                        Press a number to see what that part is called in Event Schedule, and what it costs to build.
                    @else
                        Here is what a page like the ones above can show you, what that part is called in Event Schedule, and what it costs to build.
                    @endif
                </p>
            </div>

            <div @class(['ex-xray', 'is-bare' => ! $hasXray]) data-ex-xray>
                @if ($hasXray)
                    <div class="ex-stage" data-reveal="panel">
                        <figure class="ex-browser">
                            <div class="ex-browser-bar">
                                <i></i><i></i><i></i>
                                <span class="ex-addr" dir="ltr">
                                    <button type="button" class="ex-pin" data-ex-pin="1" aria-controls="ex-part-1" aria-expanded="false" aria-label="Part 1: {{ $spec[0][0] }}">1</button>
                                    <span data-ex-slug>{{ $xraySlug }}</span>.eventschedule.com
                                </span>
                            </div>
                            <div class="ex-view" data-ex-view>
                                <div class="ex-shot">
                                    <img src="{{ asset($shotDir . $xray['xr-desk-home']['file']) . $shotVersion }}" alt="The {{ $xrayName }} schedule on a laptop: its name and Follow button, its sub-schedules as a row of chips, and its coming events" width="{{ (int) $xray['xr-desk-home']['pw'] }}" height="{{ (int) $xray['xr-desk-home']['ph'] }}" loading="lazy" decoding="async">
                                    @if (! empty($xray['xr-desk-home']['title']))
                                        <span class="ex-title" aria-hidden="true" data-ex-title data-own="{{ $xray['xr-desk-home']['title']['text'] }}" style="{{ $titleStyle($xray['xr-desk-home']['title'], $xray['xr-desk-home']['w'], $xray['xr-desk-home']['h']) }}"><span dir="auto">{{ $xray['xr-desk-home']['title']['text'] }}</span></span>
                                    @endif
                                    @foreach ($pinsOf('xr-desk-home') as $partNo => $at)
                                        <button type="button" @class(['ex-pin', 'is-after' => $at['place'] === 'after', 'is-top' => $at['place'] === 'top']) data-ex-pin="{{ $partNo }}" aria-controls="ex-part-{{ $partNo }}" aria-expanded="false" style="left: {{ $at['left'] }}%; top: {{ $at['top'] }}%;" aria-label="Part {{ $partNo }}: {{ $spec[$partNo - 1][0] }}">{{ $partNo }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </figure>

                        <div class="ex-hold">
                        @if (! empty($xray['xr-phone-home']['file']))
                            <div class="ex-tabs" role="group" aria-label="Which page is shown" data-ex-tabs>
                                <button type="button" class="ex-tab" data-ex-tab="home" aria-pressed="true">The schedule</button>
                                <button type="button" class="ex-tab" data-ex-tab="event" aria-pressed="false">One of its events</button>
                            </div>
                        @endif

                        @php $shownPicture = ! empty($xray['xr-phone-home']['file']) ? 'home' : 'event'; @endphp
                        @foreach (['xr-phone-home' => ['home', 'The ' . $xrayName . ' schedule on a phone'], 'xr-phone-event' => ['event', 'One event of the ' . $xrayName . ' schedule on a phone, with its dates, its price and its ticket button']] as $pictureKey => [$pictureName, $pictureAlt])
                            @if (! empty($xray[$pictureKey]['file']))
                                <figure @class(['ex-handset', 'is-' . $pictureName, 'is-shown' => $pictureName === $shownPicture]) data-ex-pic="{{ $pictureName }}">
                                    <div class="ex-view" data-ex-view>
                                        <div class="ex-shot">
                                            <img src="{{ asset($shotDir . $xray[$pictureKey]['file']) . $shotVersion }}" alt="{{ $pictureAlt }}" width="{{ (int) $xray[$pictureKey]['pw'] }}" height="{{ (int) $xray[$pictureKey]['ph'] }}" loading="lazy" decoding="async">
                                            @if (! empty($xray[$pictureKey]['title']))
                                                <span class="ex-title" aria-hidden="true" data-ex-title data-own="{{ $xray[$pictureKey]['title']['text'] }}" style="{{ $titleStyle($xray[$pictureKey]['title'], $xray[$pictureKey]['w'], $xray[$pictureKey]['h']) }}"><span dir="auto">{{ $xray[$pictureKey]['title']['text'] }}</span></span>
                                            @endif
                                            @foreach ($pinsOf($pictureKey) as $partNo => $at)
                                                <button type="button" @class(['ex-pin', 'is-after' => $at['place'] === 'after', 'is-top' => $at['place'] === 'top']) data-ex-pin="{{ $partNo }}" aria-controls="ex-part-{{ $partNo }}" aria-expanded="false" style="left: {{ $at['left'] }}%; top: {{ $at['top'] }}%;" aria-label="Part {{ $partNo }}: {{ $spec[$partNo - 1][0] }}">{{ $partNo }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                </figure>
                            @endif
                        @endforeach
                        </div>
                    </div>
                @endif

                <div class="ex-partscol">
                    <p class="ex-count" data-reveal><b>{{ $specFree }} of these {{ count($spec) }}</b> cost nothing.</p>
                    <ol class="ex-parts" role="list" data-ex-parts>
                        @foreach ($spec as [$specSee, $specPart, $specPlan])
                            @php $partNo = $loop->iteration; @endphp
                            <li @class(['ex-part', 'is-off-wide' => $hasXray && empty($pinnedWide[$partNo]), 'is-off-narrow' => $hasXray && empty($pinnedNarrow[$partNo])]) data-ex-part="{{ $partNo }}">
                                <button type="button" class="ex-part-btn" aria-expanded="true" aria-controls="ex-part-{{ $partNo }}">
                                    <span class="ex-n" aria-hidden="true">{{ $partNo }}</span>
                                    <span class="ex-see">
                                        @if ($partNo === 1 && $hasXray)
                                            The address in the bar, like <span dir="ltr"><span data-ex-slug>{{ $xraySlug }}</span>.eventschedule.com</span>
                                        @else
                                            {{ $specSee }}
                                        @endif
                                    </span>
                                    @if ($specPlan === 'Free')
                                        <span class="sr-only">Free</span>
                                    @else
                                        <span @class(['ex-plan', 'is-ent' => $specPlan === 'Enterprise'])>{{ $specPlan }}</span>
                                    @endif
                                </button>
                                <div class="ex-part-body" id="ex-part-{{ $partNo }}">
                                    <div>
                                        <p>
                                            {{ $specPart }}
                                            @if ($hasXray && (empty($pinnedWide[$partNo]) || empty($pinnedNarrow[$partNo])))
                                                <span @class(['ex-where', 'is-wide' => empty($pinnedWide[$partNo]), 'is-narrow' => empty($pinnedNarrow[$partNo])])>Not in these pictures</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <p class="ex-note" data-reveal>
                The {{ count($spec) - $specFree }} that cost something are putting a price on a ticket, dropping the credit chip and a domain of your own. Free registration and scanning at the door are neither capped nor charged for, and Event Schedule takes nothing from the door on any plan.
                <a href="{{ marketing_url('/pricing') }}" class="hp-more">See the plans {!! $arrow !!}</a>
                <small>A part with no plan beside it is free. The plan beside the others is what that part costs to build; it is not a claim about which plan any one demo above is on.</small>
            </p>
        </div>
    </section>

    {{-- ============================================================
         4. Questions, the related strip, the claim
         ============================================================ --}}
    <x-seo.faq-schema :items="$faqs" />

    <x-marketing.hp-faq :items="$faqs" id="faq">
        Frequently asked <span class="hp-ink-grad">questions</span>
    </x-marketing.hp-faq>

    <x-marketing.related-pages />

    <x-marketing.hp-finale label="Claim it free" foot="No credit card required." :lead="'Publishing a schedule and its dates is free forever, and so is keeping places on them and scanning people in at the door. ' . plan_price($proMonthly) . ' a month is what puts a price on a ticket, and adds the live check-in dashboard. Nothing is taken from the door on any plan.'">
        There is a space here <span class="hp-ink-grad">with <span data-ex-name data-own="your name">your name</span> on it.</span>
        <x-slot name="after">
            <ol class="ex-ends" role="list">
                @foreach ($ends as [$endNo, $endTitle, $endBody])
                    <li><b aria-hidden="true">{{ $endNo }}</b><span><strong>{{ $endTitle }}</strong> {{ $endBody }}</span></li>
                @endforeach
            </ol>
        </x-slot>
    </x-marketing.hp-finale>

    <script {!! nonce_attr() !!}>
        (function () {
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var smooth = reduce ? 'auto' : 'smooth';
            var below = function (width) { return window.matchMedia('(max-width: ' + width + 'px)').matches; };
            var all = function (selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); };
            var one = function (selector, root) { return (root || document).querySelector(selector); };

            {{-- ---- the name ---- --}}
            var nameBox = document.getElementById('ex-name');
            var nameBoxes = all('[data-ex-namebox]');
            var hero = document.getElementById('top');
            var wall = document.getElementById('wall');
            {{-- The first wall is the examples', which the chips sort; the town's is the second. --}}
            var grid = one('[data-ex-grid]');
            var grids = all('[data-ex-grid]');
            var titles = all('[data-ex-title]');
            {{-- Is there a wall of pictures to draw a name on at all? --}}
            var tryOn = all('.ex-unit [data-ex-title]').length > 0;
            var bar = one('[data-ex-bar]');
            var status = one('[data-ex-status]');
            var seeAll = one('[data-ex-seeall]');
            var plain = one('[data-ex-plain]');
            var worn = one('[data-ex-worn]');
            var names = all('[data-ex-name]');
            var slugs = all('[data-ex-slug]');
            names.forEach(function (el) {
                if (!el.hasAttribute('data-own')) {
                    el.setAttribute('data-own', el.textContent);
                }
            });
            slugs.forEach(function (el) { el.setAttribute('data-own', el.textContent); });
            var raw = '';
            var slug = '';
            var room = '';
            var said = 0;
            var fitReady = false;

            {{-- The address a name makes: the same cleaning the shared address box does. --}}
            var slugOf = function (text) {
                return text.toLowerCase().replace(/['\u2019]/g, '').replace(/[^a-z0-9-]+/g, '-').replace(/-{2,}/g, '-').replace(/^-+|-+$/g, '').slice(0, 30).replace(/-+$/, '');
            };
            var titled = function (name) {
                return name.split('-').filter(Boolean).map(function (w) { return w.charAt(0).toUpperCase() + w.slice(1); }).join(' ');
            };
            {{-- A name longer than the lines its page allows is set smaller until it stands in them.
               A picture that is put away has no size to measure against, and is left for later. --}}
            var fit = function (el) {
                if (!el.clientWidth) {
                    return;
                }
                var size = 1;
                el.style.setProperty('--fit', '1');
                while (size > 0.5 && (el.scrollHeight > el.clientHeight + 1 || el.scrollWidth > el.clientWidth + 1)) {
                    size -= 0.05;
                    el.style.setProperty('--fit', size.toFixed(2));
                }
            };
            var draw = function (el, still) {
                var text = raw || el.getAttribute('data-own') || '';
                var inner = el.firstElementChild;
                if (!inner) {
                    return;
                }
                if (inner.textContent !== text) {
                    inner.textContent = text;
                    if (!reduce && !still) {
                        el.classList.add('is-swap');
                        requestAnimationFrame(function () {
                            requestAnimationFrame(function () { el.classList.remove('is-swap'); });
                        });
                    }
                }
                if (raw) {
                    fit(el);
                } else {
                    el.style.removeProperty('--fit');
                }
            };

            {{-- The pill is for when the box is out of sight; beside the box it would only cover the wall. --}}
            var boxSeen = true;
            var showBar = function () {
                if (bar) {
                    bar.classList.toggle('is-on', !!raw && !boxSeen);
                }
            };
            if (nameBox && 'IntersectionObserver' in window) {
                new IntersectionObserver(function (entries) {
                    boxSeen = entries[entries.length - 1].isIntersecting;
                    showBar();
                }).observe(nameBox);
            }

            var say = function () {
                if (!status) {
                    return;
                }
                {{-- With a room chosen its own sentence stands here instead, if it has one. --}}
                status.hidden = !!room && !!one('[data-ex-note="' + room + '"]');
                status.textContent = '';
                if (raw && tryOn) {
                    var strong = document.createElement('b');
                    strong.textContent = raw;
                    status.appendChild(strong);
                    status.appendChild(document.createTextNode(', tried on ' + ((grid && grid.getAttribute('data-try')) || 'these') + '.' + (status.getAttribute('data-fit') && fitReady ? ' Press one to see its page.' : '')));
                } else {
                    status.textContent = status.getAttribute('data-own');
                }
            };
            var wave = 0;
            var retitle = function () {
                var mine = ++wave;
                titles.forEach(function (el, i) {
                    if (reduce) {
                        draw(el);
                        return;
                    }
                    setTimeout(function () {
                        if (mine === wave) {
                            draw(el);
                        }
                    }, Math.min(i, 20) * 22);
                });
                names.forEach(function (el) { el.textContent = raw || el.getAttribute('data-own') || ''; });
                slugs.forEach(function (el) { el.textContent = slug || el.getAttribute('data-own') || ''; });
                if (hero) {
                    hero.classList.toggle('is-named', !!slug);
                }
                grids.forEach(function (each) { each.classList.toggle('is-named', !!raw); });
                if (seeAll) {
                    seeAll.hidden = !raw;
                }
                if (plain && worn) {
                    plain.hidden = !!raw;
                    worn.hidden = !raw;
                }
                showBar();
                {{-- The line under the chips is read out when it changes: once the typing has stopped,
                   not at every letter. --}}
                clearTimeout(said);
                said = setTimeout(say, 450);
            };

            {{-- The box at the top takes the name as it is written. The address boxes further down
               (the shared one in the last panel) take the address it makes, and a name typed there
               comes back up as words. --}}
            var addressBoxes = all('.es-claim input[type="text"]');
            var pushing = false;
            {{-- Did a person write the name, in a name box? Then it is theirs: correcting the address
               further down changes the address and leaves the name as they wrote it. --}}
            var written = false;
            var pushAddress = function () {
                pushing = true;
                addressBoxes.forEach(function (box) {
                    if (box.value !== slug) {
                        box.value = slug;
                        box.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
                pushing = false;
            };
            var setName = function (text, from) {
                raw = text.replace(/\s+/g, ' ').trim().slice(0, 40);
                slug = slugOf(raw);
                written = !!raw && from !== 'address';
                if (from !== 'address') {
                    pushing = true;
                    addressBoxes.forEach(function (box) {
                        if (box.value !== slug) {
                            box.value = slug;
                            box.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                    pushing = false;
                }
                {{-- Every box but the one being typed in shows the name; that one is left as the person has it. --}}
                nameBoxes.forEach(function (box) {
                    if (box !== from && box.value.replace(/\s+/g, ' ').trim() !== raw) {
                        box.value = raw;
                    }
                });
                retitle();
            };
            var toWall = function () {
                if (wall) {
                    wall.scrollIntoView({ block: 'start', behavior: smooth });
                }
            };
            nameBoxes.forEach(function (box) {
                box.addEventListener('input', function () { setName(box.value, box); });
            });
            if (nameBox) {
                {{-- Enter means "show me" while there are pictures to show it on; otherwise it is the button beside the box. --}}
                nameBox.addEventListener('keydown', function (e) {
                    {{-- Not the Enter that confirms a word being composed (Japanese, Chinese, Korean). --}}
                    if (e.key !== 'Enter' || e.isComposing || e.keyCode === 229) {
                        return;
                    }
                    e.preventDefault();
                    if (raw && tryOn) {
                        nameBox.blur();
                        toWall();
                    } else {
                        var button = nameBox.closest('.hp-claimrow');
                        button = button ? button.querySelector('a[href]') : null;
                        if (button) {
                            button.click();
                        }
                    }
                });
            }
            document.addEventListener('input', function (e) {
                if (pushing || !e.target || !e.target.closest || !e.target.closest('.es-claim')) {
                    return;
                }
                var typedSlug = slugOf(e.target.value);
                if (typedSlug === slug) {
                    return;
                }
                if (written) {
                    slug = typedSlug;
                    retitle();
                } else {
                    setName(titled(typedSlug), 'address');
                }
            });
            document.addEventListener('click', function (e) {
                if (!e.target || !e.target.closest || !e.target.closest('[data-ex-reset]')) {
                    return;
                }
                setName('', 'reset');
                {{-- The button that was pressed has just gone: the line that says what happened takes the focus. --}}
                clearTimeout(said);
                say();
                var heard = status && !status.hidden ? status : one('[data-ex-note]:not([hidden])');
                if (heard) {
                    heard.focus({ preventScroll: true });
                }
            });
            {{-- A typeface that arrives after a name was drawn changes how much room the name takes. --}}
            if (document.fonts && document.fonts.ready) {
                var refit = function () {
                    if (raw) {
                        titles.forEach(fit);
                    }
                };
                document.fonts.ready.then(refit);
                {{-- The wall's typefaces are asked for at the first letter typed, so a name pasted in
                   one go is measured before they are there: once more each time one has loaded. --}}
                if (document.fonts.addEventListener) {
                    document.fonts.addEventListener('loadingdone', refit);
                }
            }
            var sized = 0;
            window.addEventListener('resize', function () {
                clearTimeout(sized);
                sized = setTimeout(function () {
                    if (raw) {
                        titles.forEach(fit);
                    }
                }, 200);
            });
            {{-- A browser that kept the box's words across a reload: draw them. --}}
            if (nameBox && nameBox.value) {
                setName(nameBox.value, nameBox);
            }
            {{-- A name typed before the shared script had started never reached the sign-up links:
               it attaches its listeners as a module, which has run by the time the page says it is
               loaded. The address is handed over once more then. --}}
            document.addEventListener('DOMContentLoaded', function () {
                if (slug) {
                    addressBoxes.forEach(function (box) { box.value = ''; });
                    pushAddress();
                }
            });
            {{-- A page reloaded on the entry a brought-forward page left in the history: step off it. --}}
            try {
                if (history.state && history.state.exFit) {
                    history.back();
                }
            } catch (error) {}

            {{-- ---- one kind at a time ---- --}}
            var rooms = one('[data-ex-rooms]');
            var units = grid ? all('.ex-unit', grid) : [];
            {{-- Every schedule on the page, in the order it was written in. --}}
            all('.ex-unit').forEach(function (unit, at) {
                unit.__at = at;
                unit.style.viewTransitionName = 'ex-unit-' + at;
            });
            if (rooms && grid) {
                rooms.setAttribute('data-ready', '');
                rooms.addEventListener('click', function (e) {
                    var pick = e.target && e.target.closest ? e.target.closest('[data-ex-room]') : null;
                    if (!pick) {
                        return;
                    }
                    {{-- The arrival of the first row has played; moved, it must not play again. --}}
                    units.forEach(function (unit) { unit.removeAttribute('data-first'); });
                    room = pick.getAttribute('data-ex-room');
                    all('[data-ex-room]', rooms).forEach(function (other) {
                        other.setAttribute('aria-pressed', other === pick ? 'true' : 'false');
                    });
                    {{-- In a row that is swiped, the chip that was pressed stays in sight. --}}
                    if (rooms.scrollWidth > rooms.clientWidth) {
                        rooms.scrollTo({ left: pick.offsetLeft - (rooms.clientWidth - pick.offsetWidth) / 2, behavior: smooth });
                    }
                    {{-- Only the room's own schedules stand, in the order they stood in. --}}
                    var arrange = function () {
                        if (room) {
                            grid.setAttribute('data-room', room);
                        } else {
                            grid.removeAttribute('data-room');
                        }
                        units.forEach(function (unit) {
                            unit.classList.toggle('is-in', !!room && unit.getAttribute('data-room') === room);
                        });
                        all('[data-ex-note]').forEach(function (note) {
                            note.hidden = note.getAttribute('data-ex-note') !== room;
                        });
                        clearTimeout(said);
                        say();
                        {{-- A picture that was put away while a name was typed has not been measured for it. --}}
                        if (raw) {
                            titles.forEach(fit);
                        }
                    };
                    if (document.startViewTransition && !reduce && !below(639.98)) {
                        {{-- A second press before the first has finished skips it, which the browser reports
                           as a rejection: that is not an error. --}}
                        var moving = document.startViewTransition(arrange);
                        var quiet = function () {};
                        moving.ready.catch(quiet);
                        moving.finished.catch(quiet);
                    } else {
                        arrange();
                    }
                });
            }

            {{-- ---- one schedule, brought forward ---- --}}
            var fitRoom = one('[data-ex-fitroom]');
            if (fitRoom && typeof fitRoom.showModal === 'function' && grid) {
                var fitImg = one('[data-ex-fit-img]', fitRoom);
                var fitTitle = one('[data-ex-fit-title]', fitRoom);
                var fitScreen = one('[data-ex-fit-screen]', fitRoom);
                var fitPhone = one('[data-ex-fit-phone]', fitRoom);
                var fitCount = one('[data-ex-fit-count]', fitRoom);
                var current = null;
                var pushed = false;
                var lastShown = null;
                {{-- Every schedule that can be brought forward, in the order the page was written in. --}}
                var fitLinks = all('.ex-link[data-full]').sort(function (a, b) {
                    return a.closest('.ex-unit').__at - b.closest('.ex-unit').__at;
                });
                var standing = function () { return fitLinks; };
                var looks = one('[data-ex-fit-looks]', fitRoom);
                var lookButtons = [];
                fitReady = true;
                {{-- Until here a picture was a link to the live page, and said so. --}}
                fitLinks.forEach(function (link) { link.setAttribute('aria-haspopup', 'dialog'); });
                if (status && status.getAttribute('data-fit')) {
                    status.setAttribute('data-own', status.getAttribute('data-fit'));
                    say();
                }
                var fill = function (link) {
                    current = link;
                    lastShown = link;
                    var full = link.getAttribute('data-full');
                    fitImg.setAttribute('width', link.getAttribute('data-full-pw'));
                    fitImg.setAttribute('height', link.getAttribute('data-full-ph'));
                    fitImg.alt = (link.getAttribute('data-name') || 'This schedule') + ', as a phone shows it';
                    {{-- The name waits for the page it stands on: over an empty screen it would hang in the air. --}}
                    fitTitle.hidden = true;
                    var placed = link.getAttribute('data-title-style');
                    if (placed) {
                        fitTitle.style.cssText = placed;
                        fitTitle.setAttribute('data-own', link.getAttribute('data-title') || '');
                    } else {
                        fitTitle.removeAttribute('style');
                        fitTitle.setAttribute('data-own', '');
                    }
                    fitImg.onload = function () {
                        if (current === link && placed) {
                            fitTitle.hidden = false;
                            draw(fitTitle, true);
                        }
                    };
                    fitImg.src = full;
                    if (fitPhone) {
                        fitPhone.style.setProperty('--glow', link.getAttribute('data-glow') || '96 124 200');
                    }
                    one('[data-ex-fit-kind]', fitRoom).textContent = link.getAttribute('data-kind') || '';
                    one('[data-ex-fit-name]', fitRoom).textContent = link.getAttribute('data-name') || '';
                    one('[data-ex-fit-host]', fitRoom).textContent = link.getAttribute('data-host') || '';
                    one('[data-ex-fit-live]', fitRoom).setAttribute('href', link.getAttribute('href'));
                    var list = standing();
                    fitCount.textContent = (list.indexOf(link) + 1) + ' of ' + list.length;
                    var heardHere = one('[data-ex-fit-said]', fitRoom);
                    if (heardHere) {
                        heardHere.textContent = (link.getAttribute('data-name') || '') + ', ' + fitCount.textContent;
                    }
                    fitScreen.scrollTop = 0;
                    lookButtons.forEach(function (button, at) {
                        var on = fitLinks[at] === link;
                        button.setAttribute('aria-pressed', on ? 'true' : 'false');
                        if (on && looks && looks.scrollWidth > looks.clientWidth) {
                            looks.scrollTo({ left: button.offsetLeft - (looks.clientWidth - button.offsetWidth) / 2, behavior: smooth });
                        }
                    });
                };
                {{-- The other schedules, by their logos, built once. --}}
                if (looks) {
                    fitLinks.forEach(function (link) {
                        var small = link.getAttribute('data-logo');
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'ex-fit-look';
                        button.setAttribute('aria-pressed', 'false');
                        button.setAttribute('aria-label', link.getAttribute('data-name') || '');
                        if (small) {
                            var picture = document.createElement('img');
                            picture.alt = '';
                            picture.decoding = 'async';
                            picture.loading = 'lazy';
                            picture.src = small;
                            button.appendChild(picture);
                        }
                        button.addEventListener('click', function () {
                            fill(link);
                            draw(fitTitle, true);
                        });
                        looks.appendChild(button);
                        lookButtons.push(button);
                    });
                }
                var step = function (by) {
                    var list = standing();
                    if (!list.length) {
                        return;
                    }
                    var at = list.indexOf(current);
                    fill(list[(at + by + list.length) % list.length]);
                    draw(fitTitle, true);
                };
                (wall || document).addEventListener('click', function (e) {
                    var link = e.target && e.target.closest ? e.target.closest('.ex-link[data-full]') : null;
                    {{-- A press meant for a new tab or a new window is left to open the live page. --}}
                    if (!link || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                        return;
                    }
                    e.preventDefault();
                    fill(link);
                    fitRoom.showModal();
                    draw(fitTitle, true);
                    {{-- One entry in the history, so Back on a phone closes this and does not leave the page. --}}
                    try {
                        history.pushState({ exFit: true }, '');
                        pushed = true;
                    } catch (error) {
                        pushed = false;
                    }
                });
                fitRoom.addEventListener('close', function () {
                    current = null;
                    if (pushed) {
                        pushed = false;
                        history.back();
                    }
                    {{-- Back on the wall, at the picture that was last looked at, not the first one pressed. --}}
                    if (lastShown && lastShown.offsetParent !== null) {
                        lastShown.focus({ preventScroll: true });
                    }
                });
                window.addEventListener('popstate', function () {
                    if (fitRoom.open) {
                        pushed = false;
                        fitRoom.close();
                    }
                });
                fitRoom.addEventListener('click', function (e) {
                    if (e.target === fitRoom || (e.target.closest && e.target.closest('[data-ex-fit-close]'))) {
                        fitRoom.close();
                    } else if (e.target.closest && e.target.closest('[data-ex-fit-prev]')) {
                        step(-1);
                    } else if (e.target.closest && e.target.closest('[data-ex-fit-next]')) {
                        step(1);
                    }
                });
                fitRoom.addEventListener('keydown', function (e) {
                    {{-- The arrows walk the schedules, but not with a modifier held (Alt and Left is the
                       browser's Back) and not while the caret is in the name box. --}}
                    if (e.altKey || e.metaKey || e.ctrlKey || e.shiftKey || (e.target && e.target.tagName === 'INPUT')) {
                        return;
                    }
                    if (e.key === 'ArrowLeft') {
                        step(-1);
                    } else if (e.key === 'ArrowRight') {
                        step(1);
                    }
                });
            }

            {{-- ---- the numbered parts ---- --}}
            var xray = one('[data-ex-xray]');
            var parts = one('[data-ex-parts]');
            if (xray && parts) {
                var rows = all('[data-ex-part]', parts);
                var pins = all('[data-ex-pin]', xray);
                var stage = one('.ex-stage', xray);
                var tabs = one('[data-ex-tabs]', xray);
                var pictures = all('[data-ex-pic]', xray);
                {{-- Below a laptop one picture is shown at a time: the schedule, or one of its events. --}}
                var showPicture = function (name, seek) {
                    pictures.forEach(function (picture) {
                        var on = picture.getAttribute('data-ex-pic') === name;
                        picture.classList.toggle('is-shown', on);
                        {{-- From its tab, a picture opens where its first number is, not on a stretch with none. --}}
                        var view = on && seek ? one('[data-ex-view]', picture) : null;
                        var first = view ? all('[data-ex-pin]', picture).sort(function (a, b) { return a.offsetTop - b.offsetTop; })[0] : null;
                        if (view && first) {
                            view.scrollTo({ top: Math.max(0, first.offsetTop - view.clientHeight * 0.36), behavior: 'auto' });
                        }
                    });
                    if (tabs) {
                        all('[data-ex-tab]', tabs).forEach(function (tab) {
                            tab.setAttribute('aria-pressed', tab.getAttribute('data-ex-tab') === name ? 'true' : 'false');
                        });
                    }
                    if (raw) {
                        all('[data-ex-title]', xray).forEach(fit);
                    }
                };
                var show = function (no, fromPin) {
                    {{-- Below a laptop, the picture that holds this part is the one that is shown. --}}
                    if (below(1099.98)) {
                        var holder = pictures.filter(function (picture) { return !!one('[data-ex-pin="' + no + '"]', picture); });
                        if (holder.length && !holder.some(function (picture) { return picture.classList.contains('is-shown'); })) {
                            showPicture(holder[0].getAttribute('data-ex-pic'));
                        }
                    }
                    rows.forEach(function (row) {
                        var on = row.getAttribute('data-ex-part') === no;
                        row.classList.toggle('is-on', on);
                        one('.ex-part-btn', row).setAttribute('aria-expanded', on ? 'true' : 'false');
                        {{-- A pin's sentence may be below the window, or above it under the header (or,
                           below a laptop, under the picture that stays in sight). Once the rows have
                           finished opening and closing, it is brought clear of both. --}}
                        if (on && fromPin) {
                            setTimeout(function () {
                                var box = row.getBoundingClientRect();
                                {{-- On a phone the tabs and the frame stay under the header; on a tablet and up
                                   the pictures stand beside the list and only the header is above it. --}}
                                var holder = one('.ex-hold', xray);
                                var held = holder && getComputedStyle(holder).position === 'sticky';
                                {{-- Where the frame's foot is when it is stuck, which it may not be yet. --}}
                                var ceiling = held ? parseFloat(getComputedStyle(holder).top) + holder.offsetHeight + 12 : 96;
                                if (box.top < ceiling) {
                                    window.scrollBy({ top: box.top - ceiling, behavior: smooth });
                                } else if (box.bottom > window.innerHeight - 24) {
                                    window.scrollBy({ top: Math.min(box.top - ceiling, box.bottom - window.innerHeight + 24), behavior: smooth });
                                }
                            }, reduce ? 0 : 330);
                        }
                    });
                    pins.forEach(function (pin) {
                        var on = pin.getAttribute('data-ex-pin') === no;
                        pin.classList.toggle('is-on', on);
                        pin.setAttribute('aria-expanded', on ? 'true' : 'false');
                        var view = on ? pin.closest('[data-ex-view]') : null;
                        if (view && view.offsetParent !== null) {
                            {{-- The picture moves inside its frame until the part is a third of the way down. --}}
                            view.scrollTo({ top: Math.max(0, pin.offsetTop - view.clientHeight * 0.36), behavior: smooth });
                        }
                    });
                };
                parts.setAttribute('data-ready', '');
                xray.setAttribute('data-ready', '');
                parts.addEventListener('click', function (e) {
                    var row = e.target && e.target.closest ? e.target.closest('[data-ex-part]') : null;
                    if (row && e.target.closest('.ex-part-btn')) {
                        show(row.getAttribute('data-ex-part'), false);
                    }
                });
                xray.addEventListener('click', function (e) {
                    var pin = e.target && e.target.closest ? e.target.closest('[data-ex-pin]') : null;
                    var tab = e.target && e.target.closest ? e.target.closest('[data-ex-tab]') : null;
                    if (pin) {
                        show(pin.getAttribute('data-ex-pin'), true);
                    } else if (tab) {
                        showPicture(tab.getAttribute('data-ex-tab'), true);
                    }
                });
                show('1', false);
            }
        })();
    </script>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
