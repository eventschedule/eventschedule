<x-marketing-layout :hp="true">
    <x-slot name="title">Open Source Event Calendar - Licence and Selfhosting</x-slot>
    <x-slot name="description">Event Schedule is open source under the Attribution Assurance License. Selfhost the whole thing on your own server, or drive it through the REST API.</x-slot>
    <x-slot name="breadcrumbTitle">Open Source</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareSourceCode",
        "name": "Event Schedule",
        "description": "Event Schedule is open source under the Attribution Assurance License (AAL), an OSI-approved licence adapted from the BSD licence. Selfhost it on your own server, where every feature resolves to the top tier, or drive the hosted version through the REST API.",
        "codeRepository": "https://github.com/eventschedule/eventschedule",
        "programmingLanguage": ["PHP", "JavaScript", "Vue.js"],
        "runtimePlatform": "Laravel 11",
        "license": "https://opensource.org/licenses/AAL",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "url": "{{ url()->current() }}"
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
        {{-- ==============================================================
           /open-source: "View source".

           THE CONCEPT. The page's own first sentence is "every claim here has a file path". So
           the page shows the file: each claim is quoted from this application's source, read
           off the disk of the server that is answering (App\Utils\SourceExcerpt), with the
           lines that prove it marked like a reader's highlighter. One line of configuration
           can be flipped to see what a selfhost changes, and the headline can be turned over
           to show the markup that drew it.

           NOT THIS PAGE'S. /selfhost owns the terminal (a window bar, a prompt, traffic-light
           dots, emerald). /for-ai-agents owns the man page (a ch grid, request and response
           boxes, a filterable ledger, signal orange). Nothing here has a window bar, a prompt
           or a request with its response. What is this page's own: the quotation with its
           line numbers, the marked line, the switch, the licence set as a document.

           The house style (partials/hp-kit) gives the type, the palette and the shared parts.
           Everything below is plain CSS on its tokens, because a utility class that is not
           already in the built stylesheet renders as nothing.
           ============================================================== --}}
        #hp {
            --os-mark: #fff0a6;
            --os-mark-key: #ffe680;
            --os-mark-ink: #6b5200;
            --os-code-bg: #ffffff;
            --os-code-ink: #18213a;
            --os-k: #2a55d9;
            --os-s: #0b7a53;
            --os-v: #a8440b;
            --os-c: #5b6785;
            --os-f: #0a66a8;
            --os-n: #a8440b;
            --os-t: #0f6e7d;
            --os-del: #c0263a;
            --os-amber: #a8440b;
        }
        {{-- A night band is dark in both modes, so inside it the kit's inks are the night's too. --}}
        .dark #hp,
        #hp .os-night,
        #hp .hp-finale {
            {{-- Yellow over navy is khaki. On a dark ground the mark is the page's own light, and
               the line number keeps the yellow. --}}
            --os-mark: rgba(56, 189, 248, 0.3);
            --os-mark-key: rgba(56, 189, 248, 0.75);
            --os-mark-ink: #ffe066;
            --os-code-bg: #0b1124;
            --os-code-ink: #dfe6fb;
            --os-k: #8db0ff;
            --os-s: #7ee0b8;
            --os-v: #ffc48a;
            --os-c: #93a0c2;
            --os-f: #7dd3fc;
            --os-n: #ffc48a;
            --os-t: #5eead4;
            --os-del: #ff9aa5;
            --os-amber: #fbbf24;
        }
        #hp .os-night,
        #hp .hp-finale {
            --hp-ink: #eef2ff;
            --hp-ink-2: #c5cde2;
            --hp-ink-3: #9fb1d6;
            --hp-line: rgba(255, 255, 255, 0.09);
            --hp-line-2: rgba(255, 255, 255, 0.17);
            --hp-blue: #8db0ff;
            --hp-bg-2: #0e1424;
            --hp-card-shadow: none;
        }

        {{-- ---- The quotation ---- --}}
        .os-code {
            margin: 0;
            border: 1px solid var(--hp-line-2);
            border-radius: 1.1rem;
            background: var(--os-code-bg);
            color: var(--os-code-ink);
            box-shadow: var(--hp-card-shadow);
            overflow: hidden;
            text-align: start;
        }
        .os-code-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.4rem 0.9rem;
            padding: 0.7rem 1rem;
            border-bottom: 1px solid var(--hp-line);
            font-family: var(--hp-mono);
            font-size: 0.75rem;
            font-variation-settings: normal;
            color: var(--hp-ink-3);
        }
        .os-code-live { display: inline-flex; align-items: center; gap: 0.45rem; margin-inline-start: auto; }
        .os-code-live i { width: 0.45rem; height: 0.45rem; border-radius: 999px; background: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.18); }
        .os-code-body { padding-block: 0.7rem; }
        .os-line { display: flex; padding-inline-end: 1.1rem; font-family: var(--hp-mono); font-size: 0.8125rem; line-height: 1.72; font-variation-settings: normal; transition: background-color 0.35s ease; }
        .os-ln { flex: none; width: 3.5rem; padding-inline-end: 1rem; text-align: end; font-style: normal; color: var(--hp-ink-3); user-select: none; font-variant-numeric: tabular-nums; transition: color 0.35s ease; }
        {{-- A long line folds under itself, two characters in from where it began. --}}
        #hp .os-line code {
            flex: 1 1 0;
            min-width: 0;
            padding: 0;
            padding-inline-start: calc((var(--in, 0) + 2) * 1ch);
            text-indent: -2ch;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            font-size: inherit;
            color: inherit;
            background: none;
        }
        .os-line.is-marked { background: var(--os-mark); }
        .dark .os-line.is-marked,
        #hp .os-night .os-line.is-marked { color: #ffffff; }
        .os-line.is-marked .os-ln { color: var(--os-mark-ink); font-weight: 700; }
        .os-k { color: var(--os-k); }
        .os-s { color: var(--os-s); }
        .os-v { color: var(--os-v); }
        .os-c { color: var(--os-c); }
        .os-f { color: var(--os-f); }
        .os-n { color: var(--os-n); }
        .os-t { color: var(--os-t); }
        .os-code-note {
            position: relative;
            margin: 0;
            padding-block: 0.8rem 0.9rem;
            padding-inline: 3.5rem 1rem;
            border-top: 1px solid var(--hp-line);
            font-size: 0.92rem;
            line-height: 1.5;
            color: var(--hp-ink-2);
        }
        {{-- The reader's mark beside the note: the same yellow as the line it speaks of. --}}
        .os-code-note::before {
            content: "";
            position: absolute;
            inset-inline-start: 1.15rem;
            top: 1.05rem;
            width: 1.5rem;
            height: 0.7rem;
            border-radius: 0.2rem;
            background: var(--os-mark-key);
        }

        {{-- A path, as a chip. The page's small signature. --}}
        {{-- Inline, so a long path folds at its slashes (the component offers a break after each). --}}
        .os-path {
            display: inline-block;
            max-width: 100%;
            padding: 0.12rem 0.5rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 0.4rem;
            background: var(--hp-bg);
            font-family: var(--hp-mono);
            font-size: 0.74rem;
            font-weight: 700;
            font-variation-settings: normal;
            line-height: 1.6;
            color: var(--hp-ink);
            overflow-wrap: anywhere;
        }
        .dark .os-path,
        #hp .os-night .os-path,
        #hp .hp-finale .os-path { background: rgba(255, 255, 255, 0.05); }
        .os-nb { white-space: nowrap; }
        a.os-path { min-height: 1.5rem; transition: border-color 0.2s ease, color 0.2s ease; }
        a.os-path:hover { border-color: var(--hp-blue); color: var(--hp-blue); }

        .os-pill {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 0.3rem;
            font-family: var(--hp-mono);
            font-size: 0.62rem;
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--hp-ink-2);
        }
        .os-pill.is-pro { border-color: var(--hp-blue); color: var(--hp-blue); }
        .os-pill.is-ent { border-color: var(--os-amber); color: var(--os-amber); }

        .os-mark-word { padding: 0.02em 0.18em; border-radius: 0.2em; background: var(--os-mark); box-decoration-break: clone; -webkit-box-decoration-break: clone; }
        #hp .os-inline { font-family: var(--hp-mono); font-size: 0.92em; font-variation-settings: normal; color: var(--hp-blue); }

        {{-- ---- 1. Hero: the headline, and the markup that drew it ---- --}}
        {{-- A step shorter than the kit's hero, so the files under it reach the first screen. --}}
        #hp .os-hero { padding-block: clamp(2.5rem, 5.5vh, 4.5rem) clamp(1.5rem, 3vh, 2.5rem); }
        #hp .os-hero .hp-sub { max-width: 51rem; }
        {{-- From a laptop up the hero is ruled like a page of a listing, where the other pages
           have a week of seven columns. On a phone the rules cut through the words, so there
           are none. --}}
        #hp .os-hero .hp-hero-sky::after { display: none; }
        @media (min-width: 1024px) {
            #hp .os-hero .hp-hero-sky::after {
                display: block;
                background-image: linear-gradient(var(--hp-line) 1px, transparent 1px);
                background-size: 100% 2.75rem;
                -webkit-mask-image: radial-gradient(ellipse 46rem 22rem at 50% 13rem, #000 10%, transparent 74%);
                mask-image: radial-gradient(ellipse 46rem 22rem at 50% 13rem, #000 10%, transparent 74%);
            }
        }
        {{-- Two faces of one card. On a phone the face that is not showing takes no room, and the
           script holds the button you pressed where it was. On a wide window, where the markup
           is no taller than the headline, they share one cell and nothing under them moves. --}}
        .os-flip { position: relative; perspective: 1600px; }
        .os-flip > * { -webkit-backface-visibility: hidden; backface-visibility: hidden; transition: transform 0.75s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.3s ease; }
        .os-flip-back { position: absolute; inset-inline: 0; top: 0; width: min(100%, 46rem); margin-inline: auto; transform: rotateX(-180deg); opacity: 0; visibility: hidden; transition: transform 0.75s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.3s ease, visibility 0s linear 0.75s; }
        .os-flip.is-source .os-flip-front { position: absolute; inset-inline: 0; top: 0; transform: rotateX(180deg); opacity: 0; pointer-events: none; }
        .os-flip.is-source .os-flip-back { position: relative; transform: rotateX(0deg); opacity: 1; visibility: visible; transition-delay: 0s; }
        .os-flip-back .os-line { line-height: 1.58; }
        .os-flip-back .os-code-body { padding-block: 0.55rem; }
        @media (min-width: 1440px) {
            .os-flip { display: grid; align-items: center; }
            .os-flip > * { grid-area: 1 / 1; min-width: 0; }
            .os-flip-back,
            .os-flip.is-source .os-flip-front,
            .os-flip.is-source .os-flip-back { position: relative; inset: auto; }
        }
        {{-- The headline says it has a file path. This is it, marked, and it can be pressed. --}}
        .os-own { display: flex; justify-content: center; margin-top: clamp(1rem, 2.2vh, 1.5rem); }
        .os-own[hidden] { display: none; }
        .os-own-btn {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.4rem 0.75rem;
            min-height: 2.75rem;
            padding: 0.35rem 1rem 0.35rem 0.4rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 999px;
            background: var(--hp-bg-2);
            box-shadow: var(--hp-card-shadow);
            font-size: 0.98rem;
            font-weight: 700;
            font-variation-settings: 'wght' 680;
            color: var(--hp-ink);
            cursor: pointer;
            transition: border-color 0.2s ease, transform 0.2s ease;
        }
        .os-own-btn:hover { border-color: var(--hp-blue); transform: translateY(-1px); }
        #hp .os-own-btn .os-path { border-color: transparent; border-radius: 999px; background: var(--os-mark); padding: 0.3rem 0.8rem; font-size: 0.8rem; }
        .os-own-btn svg { flex: none; width: 1.05rem; height: 1.05rem; color: var(--hp-blue); transition: transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1); }
        .os-own-btn.is-turned svg { transform: rotate(180deg); }
        {{-- Where the whole path would not sit on one line with its label, it starts at the folder
           everyone knows. --}}
        @media (max-width: 1099.98px) {
            .os-own-dir { display: none; }
        }
        @media (max-width: 1099.98px) {
            #hp .os-hero .hp-toc { gap: 0.4rem; }
            #hp .os-hero .hp-toc a { padding-inline: 0.7rem; font-size: 0.88rem; }
        }
        @media (max-width: 639.98px) {
            #hp .os-hero .hp-toc { display: none; }
        }

        {{-- ---- The line-up, of paths ---- --}}
        #hp .os-lineup .hp-act { font-family: var(--hp-mono); font-size: clamp(1.15rem, 2.1vw, 2.1rem); font-weight: 700; font-variation-settings: normal; letter-spacing: -0.03em; }
        #hp .os-lineup [data-marquee="-1"] .hp-act { font-weight: 400; }

        {{-- ---- 2. The switch (night) ----
           One value decides everything in this band, and a little of what comes after it: the
           script writes data-state ("hosted" or "self") on the page's wrapper. A rule without
           [data-state] is what a visitor with scripts off reads: both sides, labelled. --}}
        #hp .os-switch { padding-top: clamp(5.5rem, 8vw, 7rem); }
        .os-env { max-width: 62rem; margin: clamp(1.75rem, 3vw, 2.25rem) auto 0; }
        .os-env-file {
            position: relative;
            top: 1px;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-inline-start: 1.75rem;
            padding: 0.3rem 0.8rem;
            border: 1px solid var(--hp-line-2);
            border-bottom: 0;
            border-radius: 0.7rem 0.7rem 0 0;
            background: #0b1124;
            font-family: var(--hp-mono);
            font-size: 0.8rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: var(--hp-ink-3);
        }
        .os-env-card {
            position: relative;
            padding: clamp(1.5rem, 3vw, 2.25rem) clamp(1rem, 3vw, 2.5rem);
            text-align: center;
            border: 1px solid var(--hp-line-2);
            border-radius: 1.75rem;
            background:
                radial-gradient(36rem 14rem at 50% 0%, rgba(78, 129, 250, 0.28), transparent 70%),
                #0b1124;
        }
        .os-env-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.25em 0.12em;
            margin: 0;
            font-family: var(--hp-mono);
            font-size: clamp(1.45rem, 5vw, 3.9rem);
            font-weight: 700;
            font-variation-settings: normal;
            letter-spacing: -0.04em;
            line-height: 1.1;
            color: #eef2ff;
        }
        .os-env-eq { color: var(--hp-ink-3); }
        {{-- Without the script there is nothing to press: the line simply reads as a selfhost's. --}}
        .os-env-static { color: #8db0ff; }
        .os-env-static[hidden],
        .os-toggle[hidden] { display: none; }
        .os-toggle {
            position: relative;
            display: inline-grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            margin-inline-start: 0.12em;
            padding: 0.1em;
            border: 1px solid rgba(125, 165, 255, 0.45);
            border-radius: 0.36em;
            background: #050814;
            font: inherit;
            letter-spacing: inherit;
            color: var(--hp-ink-3);
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        {{-- The lit half. The line is written left to right in every language (dir="ltr" on it),
           so "left" here is the side "true" is on. --}}
        .os-toggle::before {
            content: "";
            position: absolute;
            top: 0.1em;
            bottom: 0.1em;
            left: 0.1em;
            width: calc(50% - 0.1em);
            border-radius: 0.27em;
            background: linear-gradient(100deg, #3a6df0, #1f8fe0);
            box-shadow: 0 0 2.4rem -0.3rem rgba(78, 129, 250, 0.95), inset 0 1px 0 rgba(255, 255, 255, 0.3);
            transition: transform 0.42s cubic-bezier(0.3, 1.35, 0.5, 1);
        }
        .os-toggle[aria-checked="false"]::before { transform: translateX(100%); }
        .os-toggle span { position: relative; padding: 0.06em 0.34em 0.1em; transition: color 0.25s ease; }
        .os-toggle[aria-checked="true"] [data-on],
        .os-toggle[aria-checked="false"] [data-off] { color: #ffffff; }
        .os-toggle:hover { border-color: #8db0ff; }
        .os-env-hint { max-width: 36rem; margin: 1.1rem auto 0; font-size: 1rem; line-height: 1.5; color: var(--hp-ink-2); }
        .os-env-hint[hidden] { display: none; }
        .os-env-hint b { color: #ffffff; }

        {{-- Two wordings of one thing, by which side is showing. With no state it is the
           selfhost's that is read, as on the line above. --}}
        [data-os-when="hosted"] { display: none; }
        [data-state="hosted"] [data-os-when="hosted"] { display: inline; }
        [data-state="hosted"] [data-os-when="self"] { display: none; }

        {{-- The switch again, small, so that it is pressed in front of what it changes. Where the
           rows and the method share the screen it lives in the method's own heading, and the
           method stays beside the rows. On one column it is a bar that fills the width under the
           site's own, solid, shown once the large switch has gone up out of the window. It never
           floats over words. --}}
        .os-mini { display: inline-flex; align-items: center; gap: 0.5rem; font-family: var(--hp-mono); font-size: 0.82rem; font-weight: 700; font-variation-settings: normal; white-space: nowrap; color: #eef2ff; }
        .os-mini[hidden] { display: none; }
        .os-code-head .os-mini { display: none; margin-inline-start: auto; }
        .os-dock { position: sticky; top: 4rem; z-index: 30; height: 0; margin-inline: calc(50% - 50vw); }
        .os-dock[hidden] { display: none; }
        .os-dock-in {
            position: absolute;
            inset-inline: 0;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 0.55rem 1rem;
            border-bottom: 1px solid rgba(125, 165, 255, 0.4);
            background: #070c1c;
            box-shadow: 0 16px 30px -18px rgba(0, 0, 0, 0.95);
            font-size: 0.95rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-100%);
            transition: opacity 0.2s ease, transform 0.25s ease, visibility 0s linear 0.25s;
        }
        .os-dock.is-on .os-dock-in { opacity: 1; visibility: visible; transform: none; transition-delay: 0s; }
        .os-dock-say { font-family: var(--hp-display); font-size: 0.86rem; font-weight: 400; font-variation-settings: 'wght' 520; color: var(--hp-ink-2); }
        @media (max-width: 479.98px) {
            .os-dock-say { display: none; }
        }
        @media (min-width: 1100px) {
            .os-dock { display: none; }
            .os-code-head .os-mini:not([hidden]) { display: inline-flex; }
        }

        .os-switch-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.25rem; margin-top: clamp(1.25rem, 2vw, 1.5rem); }
        @media (min-width: 1100px) {
            .os-switch-grid { grid-template-columns: minmax(0, 1.12fr) minmax(0, 1fr); align-items: start; }
            {{-- The method stays beside the rows while they are read. --}}
            .os-switch-side { position: sticky; top: 5.25rem; }
        }
        .os-ledger { border: 1px solid var(--hp-line-2); border-radius: 1.25rem; background: rgba(11, 17, 36, 0.86); overflow: hidden; }
        {{-- Which install the rows are showing. It is said, not pressed: the switch is the control. --}}
        .os-ledger-head { padding: 0.95rem 1.1rem; border-bottom: 1px solid var(--hp-line-2); font-size: 1.1rem; font-weight: 700; font-variation-settings: 'wght' 760; letter-spacing: -0.02em; color: #ffffff; }
        .os-ledger-head[hidden] { display: none; }

        .os-row { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.2rem 1.25rem; padding: 0.8rem 1.1rem; }
        @media (min-width: 640px) {
            .os-row { grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr); align-items: baseline; }
        }
        .os-row + .os-row { border-top: 1px solid var(--hp-line); }
        .os-row-name { font-size: 1rem; font-weight: 700; font-variation-settings: 'wght' 700; line-height: 1.35; color: #eef2ff; }
        .os-vals { display: grid; gap: 0.15rem; min-width: 0; }
        .os-cell { display: flex; align-items: baseline; gap: 0.5rem; min-width: 0; font-size: 0.95rem; line-height: 1.45; color: var(--hp-ink-2); transition: color 0.3s ease, font-size 0.3s ease; transition-delay: calc(var(--i, 0) * 45ms); }
        .os-cell-text { min-width: 0; }
        .os-cell-side { flex: none; width: 4.2rem; font-family: var(--hp-mono); font-size: 0.66rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        .os-cell-tick { flex: none; width: 1.05rem; height: 1.05rem; align-self: flex-start; margin-top: 0.22rem; color: #67e8f9; }
        {{-- Hosted: what a schedule here is on. Yours: that, struck out, and what it becomes. The
           plan does not grey out and stay; its badge comes apart and the new value arrives, each
           row a beat after the one above. --}}
        [data-state] .os-vals { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.15rem 1rem; }
        [data-state] .os-cell-side { display: none; }
        [data-state="hosted"] .os-cell.is-self { display: none; }
        [data-state="hosted"] .os-cell.is-hosted { color: #eef2ff; }
        [data-state="self"] .os-cell.is-self { order: -1; }
        [data-state="self"] .os-cell.is-hosted { font-size: 0.82rem; color: #8794b8; }
        [data-state="self"] .os-cell.is-hosted .os-cell-text,
        [data-state="self"] .os-cell.is-hosted .os-pill { text-decoration: line-through; text-decoration-color: rgba(135, 148, 184, 0.85); }
        [data-state="self"] .os-cell.is-hosted .os-pill { padding-inline: 0; border-color: transparent; color: inherit; }
        [data-state="self"] .os-cell.is-self { color: #8ee6ff; font-size: 1rem; font-weight: 700; font-variation-settings: 'wght' 760; animation: os-arrive 0.45s cubic-bezier(0.2, 0.9, 0.3, 1.2) both; animation-delay: calc(var(--i, 0) * 45ms + 0.1s); }
        @keyframes os-arrive {
            from { opacity: 0; transform: translateY(0.5rem); }
        }
        .os-pill { transition: border-color 0.3s ease, color 0.3s ease, padding 0.3s ease; }

        {{-- What goes the other way: one sentence, and whose it is. --}}
        .os-otherway { padding: 1.15rem 1.1rem 1.25rem; border-top: 1px solid var(--hp-line-2); }
        #hp .os-otherway h3 { font-size: 1.1rem; font-variation-settings: 'wght' 760; color: #ffffff; }
        .os-otherway p { margin-top: 0.5rem; font-size: 0.98rem; line-height: 1.55; color: var(--hp-ink-2); }
        #hp .os-otherway-who { display: block; margin-top: 0.35rem; font-size: clamp(1.6rem, 2.4vw, 2.1rem); font-weight: 700; font-variation-settings: 'wght' 800; letter-spacing: -0.035em; line-height: 1.1; color: #fde9b0; transition: color 0.3s ease; }
        #hp[data-state="hosted"] .os-otherway-who,
        [data-state="hosted"] #hp .os-otherway-who { color: #ffffff; }
        .os-sum { margin: 0; padding: 0.95rem 1.1rem; border-top: 1px solid var(--hp-line-2); background: rgba(78, 129, 250, 0.12); font-size: 0.98rem; line-height: 1.5; color: #eef2ff; }
        .os-sum[hidden] { display: none; }

        {{-- The method itself. Its first test is the selfhost; the rest is what a hosted schedule
           is asked. The side that is showing is the side that is marked. --}}
        .os-switch .os-line[data-mark="hosted"],
        [data-state="hosted"] .os-switch .os-line[data-mark="self"] { background: transparent; }
        .os-switch .os-line[data-mark="hosted"] .os-ln,
        [data-state="hosted"] .os-switch .os-line[data-mark="self"] .os-ln { color: var(--hp-ink-3); font-weight: 400; }
        [data-state="hosted"] .os-switch .os-line[data-mark="hosted"] { background: var(--os-mark); }
        [data-state="hosted"] .os-switch .os-line[data-mark="hosted"] .os-ln { color: var(--os-mark-ink); font-weight: 700; }

        {{-- A plan tag anywhere after the band answers to the same switch. --}}
        [data-os-plan="self"] { display: none; }
        [data-state="self"] [data-os-plan="self"] { display: inline-flex; }
        [data-state="self"] [data-os-plan="hosted"] { display: none; }
        .os-pill.is-yours { border-color: var(--os-s); color: var(--os-s); }

        {{-- This page's sections are files and lists, not posters: they stand closer together than
           the kit's, or a short one leaves a screen of nothing under it. --}}
        #hp .os-sec { padding-block: clamp(3.5rem, 6vw, 5.5rem); }

        {{-- ---- 3. Check our work ---- --}}
        .os-proof { margin-top: clamp(2rem, 4vw, 3.25rem); }
        .os-claim { border-top: 1px solid var(--hp-line-2); }
        .os-claim:last-child { border-bottom: 1px solid var(--hp-line-2); }
        #hp .os-claim-h { font-size: inherit; letter-spacing: inherit; }
        .os-claim-btn {
            display: flex;
            align-items: baseline;
            gap: 0.9rem;
            width: 100%;
            padding: 1.05rem 0.35rem;
            text-align: start;
            font-size: clamp(1.05rem, 0.4vw + 0.98rem, 1.22rem);
            font-weight: 700;
            font-variation-settings: 'wght' 700;
            letter-spacing: -0.02em;
            line-height: 1.3;
            color: var(--hp-ink);
            cursor: pointer;
            transition: color 0.2s ease, background-color 0.2s ease;
        }
        .os-claim-btn b { flex: none; font-family: var(--hp-mono); font-size: 0.78rem; font-variation-settings: normal; letter-spacing: 0.04em; color: var(--hp-ink-3); }
        {{-- Until the script is running it is a heading and looks like one. --}}
        .os-claim-btn:disabled { cursor: default; color: var(--hp-ink); }
        .os-claim-btn:not(:disabled):hover { color: var(--hp-blue); }
        {{-- A link to a claim stops clear of the site's bar. --}}
        .os-proof,
        .os-claim-btn,
        .os-claim-panel { scroll-margin-top: 5.5rem; }
        .os-claim-btn[aria-expanded="true"] b { color: var(--hp-blue); }
        .os-claim-panel { padding: 0.25rem 0 1.75rem; }
        .os-claim-panel[hidden] { display: none; }
        .os-claim-body { margin-top: 1.1rem; }
        .os-claim-body p { max-width: 44rem; font-size: 1.02rem; line-height: 1.6; color: var(--hp-ink-2); }
        .os-claim-body p + p { margin-top: 0.75rem; }
        .os-claim-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1.25rem; margin-top: 1rem; }
        {{-- Beside a real link a boxed tag reads as a second button. Here it is only said. --}}
        .os-claim-meta .os-pill { padding: 0; border: 0; }
        {{-- From a laptop up, once the script is running: the claims down one side, the open one's
           file beside them. The last row takes whatever the open panel needs, so the list keeps
           its own rhythm however long a quotation is. --}}
        @media (min-width: 1024px) {
            .os-proof.is-ready {
                display: grid;
                grid-template-columns: minmax(0, 23rem) minmax(0, 1fr);
                grid-template-rows: repeat(var(--n, 9), auto) 1fr;
                column-gap: clamp(2rem, 4vw, 4rem);
            }
            .os-proof.is-ready .os-claim { display: contents; }
            .os-proof.is-ready .os-claim-h { grid-column: 1; border-top: 1px solid var(--hp-line-2); }
            .os-proof.is-ready .os-claim:last-child .os-claim-h { border-bottom: 1px solid var(--hp-line-2); }
            .os-proof.is-ready .os-claim-panel { grid-column: 2; grid-row: 1 / -1; padding: 0; }
            .os-proof.is-ready .os-claim-btn { padding-inline: 0.9rem; border-radius: 0.9rem; }
            .os-proof.is-ready .os-claim-btn[aria-expanded="true"] { background: var(--hp-ink); color: var(--hp-bg); box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.35); }
            .os-proof.is-ready .os-claim-btn[aria-expanded="true"] b { color: var(--hp-bg); opacity: 0.7; }
        }
        #hp .os-claim-say { display: none; margin-bottom: 1.1rem; font-size: clamp(1.6rem, 1.4vw + 1.05rem, 2.25rem); font-weight: 700; font-variation-settings: 'wght' 790; letter-spacing: -0.035em; line-height: 1.08; }
        @media (min-width: 1024px) {
            #hp .os-proof.is-ready .os-claim-say { display: block; }
        }

        {{-- ---- 4. The licence, as the document it is ---- --}}
        .os-lic { display: grid; grid-template-columns: minmax(0, 1fr); gap: clamp(1.75rem, 4vw, 3.5rem); margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 1024px) {
            .os-lic { grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); align-items: start; }
            .os-lic-notes { position: sticky; top: 6.5rem; }
        }
        .os-sheet {
            position: relative;
            padding: clamp(1.5rem, 4vw, 3rem) clamp(1.25rem, 4.5vw, 3.5rem);
            border: 1px solid var(--hp-line-2);
            border-radius: 0.5rem;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(10, 16, 32, 0.05), 0 30px 60px -30px rgba(10, 16, 32, 0.35);
            font-family: var(--hp-mono);
            font-size: 0.82rem;
            font-variation-settings: normal;
            line-height: 1.75;
            color: #1f2942;
        }
        .dark .os-sheet { background: #0e1424; color: #c5cde2; box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 30px 60px -30px rgba(0, 0, 0, 0.9); }
        .os-sheet-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; margin-bottom: 1rem; padding-bottom: 0.9rem; border-bottom: 2px solid var(--hp-ink); font-size: 0.72rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); }
        .os-sheet p { margin: 0; overflow-wrap: anywhere; }
        .os-sheet p + p { margin-top: 0.95rem; }
        .os-sheet .is-head { text-align: center; color: var(--hp-ink-3); }
        .os-sheet .is-head + .is-head { margin-top: 0.15rem; }
        .os-sheet .is-title { margin-block: 1.4rem; text-align: center; font-weight: 700; letter-spacing: 0.04em; color: var(--hp-ink); }
        .os-sheet .is-sub { padding-inline-start: 1.75rem; }
        .os-sheet .is-sub + .is-sub,
        .os-sheet .is-clause + .is-sub { margin-top: 0.35rem; }
        .os-sheet .is-small { font-size: 0.76rem; line-height: 1.7; color: var(--hp-ink-3); }
        .os-sheet mark { padding: 0.12em 0; background: var(--os-mark); color: inherit; box-decoration-break: clone; -webkit-box-decoration-break: clone; }
        .dark .os-sheet mark { color: #f4f7ff; }
        .os-sheet .is-grant { text-decoration: underline; text-decoration-color: var(--hp-blue); text-decoration-thickness: 2px; text-underline-offset: 0.3em; }
        .os-note { padding-block: 1.35rem; border-top: 1px solid var(--hp-line-2); }
        .os-note:last-of-type { border-bottom: 1px solid var(--hp-line-2); }
        .os-note-tag { display: inline-flex; align-items: center; gap: 0.5rem; font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; text-transform: uppercase; color: var(--hp-ink-3); }
        .os-note-tag i { width: 1.4rem; height: 0.65rem; border-radius: 0.2rem; background: var(--os-mark-key); }
        .os-note-tag i.is-line { height: 2px; border-radius: 1px; background: var(--hp-blue); }
        {{-- What the two marks mean, said before the text they are on. --}}
        .os-sheet .os-sheet-key { display: flex; flex-wrap: wrap; gap: 0.4rem 1.5rem; margin-bottom: 1.6rem; font-family: var(--hp-display); font-size: 0.86rem; line-height: 1.4; color: var(--hp-ink-2); }
        .os-sheet-key span { display: inline-flex; align-items: center; gap: 0.55rem; }
        .os-sheet-key i { flex: none; width: 1.4rem; height: 0.65rem; border-radius: 0.2rem; background: var(--os-mark-key); }
        .os-sheet-key i.is-line { height: 2px; border-radius: 1px; background: var(--hp-blue); }
        #hp .os-note h3 { margin-top: 0.55rem; font-size: 1.3rem; font-variation-settings: 'wght' 760; }
        .os-note p { margin-top: 0.5rem; font-size: 1rem; line-height: 1.6; color: var(--hp-ink-2); }
        .os-lic-foot { margin-top: 1.25rem; font-size: 0.92rem; line-height: 1.55; color: var(--hp-ink-3); }

        {{-- ---- 5. The API, by what it is about ---- --}}
        .os-api-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem 1.5rem; margin-top: clamp(2rem, 4vw, 3rem); }
        #hp .os-api-count { font-family: var(--hp-mono); font-size: 0.78rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.14em; text-transform: uppercase; color: var(--hp-ink-3); }
        .os-api-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem 1rem; }
        .os-api-tools[hidden] { display: none; }
        {{-- By method: what does not match leaves the list. --}}
        .os-verbs { display: inline-flex; flex-wrap: wrap; gap: 0.3rem; }
        .os-verbs[hidden] { display: none; }
        .os-verbs button {
            min-height: 2.25rem;
            padding: 0 0.75rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 999px;
            background: var(--hp-bg-2);
            font-family: var(--hp-mono);
            font-size: 0.74rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: var(--hp-ink-2);
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        .os-verbs button span { margin-inline-start: 0.1rem; font-weight: 400; color: var(--hp-ink-3); }
        .os-verbs button:hover { border-color: var(--hp-blue); }
        .os-verbs button[aria-pressed="true"] { background: var(--hp-ink); border-color: var(--hp-ink); color: var(--hp-bg); box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.35); }
        .os-verbs button[aria-pressed="true"] span { color: var(--hp-bg); opacity: 0.75; }
        {{-- Said after the rule it changes, or it changes nothing. --}}
        @media (max-width: 479.98px) {
            .os-verbs { gap: 0.2rem; }
            .os-verbs button { padding-inline: 0.45rem; font-size: 0.68rem; }
        }
        {{-- As a list, or as the file the list was transcribed from. --}}
        .os-seg { display: inline-flex; padding: 0.2rem; border: 1px solid var(--hp-line-2); border-radius: 0.8rem; background: var(--hp-bg-2); }
        .os-seg button {
            min-height: 2.25rem;
            padding: 0 0.9rem;
            border-radius: 0.6rem;
            font-family: var(--hp-mono);
            font-size: 0.78rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: var(--hp-ink-2);
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }
        .os-seg button:hover { color: var(--hp-ink); }
        .os-seg button[aria-pressed="true"] { background: var(--hp-ink); color: var(--hp-bg); box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.35); }
        .os-api-file { margin-top: 1rem; }

        {{-- One column, then two, then three, and in each the groups stay where they are put: a
           filter that emptied a group must not reshuffle the others. --}}
        .os-api { margin-top: 1rem; column-gap: 1rem; columns: 1; }
        .os-api[hidden] { display: none; }
        @media (max-width: 719.98px) {
            .os-api { display: flex; flex-direction: column; }
            .os-api-out { order: 1; }
        }
        .os-api-col { display: contents; }
        @media (min-width: 720px) {
            .os-api { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 1rem; align-items: start; }
            .os-api-col { display: block; min-width: 0; }
            .os-api-col:last-child { grid-column: 1 / -1; columns: 2; column-gap: 1rem; }
        }
        @media (min-width: 1100px) {
            .os-api { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .os-api-col:last-child { grid-column: auto; columns: auto; }
        }
        .os-api-out { break-inside: avoid; margin: 0 0 1rem; padding: 1.15rem; border: 1px dashed var(--hp-line-2); border-radius: 1.25rem; font-size: 0.95rem; line-height: 1.55; color: var(--hp-ink-2); }
        .os-api-gate { max-width: 62rem; font-size: 0.98rem; line-height: 1.6; color: var(--hp-ink-2); }
        .os-api-gate .os-pill { margin-inline-end: 0.35rem; vertical-align: 0.1em; }
        {{-- In the file a method's routes are marked only while that method is asked for. --}}
        .os-api-file .os-line.is-marked { background: transparent; color: inherit; }
        .os-api-file .os-line.is-marked .os-ln { color: var(--hp-ink-3); font-weight: 400; }
        [data-os-verb-on="GET"] .os-line[data-mark="GET"],
        [data-os-verb-on="POST"] .os-line[data-mark="POST"],
        [data-os-verb-on="PUT"] .os-line[data-mark="PUT"],
        [data-os-verb-on="DELETE"] .os-line[data-mark="DELETE"] { background: var(--os-mark); }
        [data-os-verb-on="GET"] .os-line[data-mark="GET"] .os-ln,
        [data-os-verb-on="POST"] .os-line[data-mark="POST"] .os-ln,
        [data-os-verb-on="PUT"] .os-line[data-mark="PUT"] .os-ln,
        [data-os-verb-on="DELETE"] .os-line[data-mark="DELETE"] .os-ln { color: var(--os-mark-ink); font-weight: 700; }
        .os-res { break-inside: avoid; margin: 0 0 1rem; padding: 1.15rem 1.15rem 0.5rem; border: 1px solid var(--hp-line); border-radius: 1.25rem; background: var(--hp-bg-2); box-shadow: var(--hp-card-shadow); }
        .os-res[hidden] { display: none; }
        .os-res-top { display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 2px solid var(--hp-ink); list-style: none; }
        .os-res-top::-webkit-details-marker { display: none; }
        #hp .os-res h4 { font-size: 1.2rem; font-variation-settings: 'wght' 800; letter-spacing: -0.03em; }
        .os-res-top span { font-family: var(--hp-mono); font-size: 0.74rem; font-variation-settings: normal; color: var(--hp-ink-3); }
        {{-- A group can be folded on a phone, where twenty-six in a column is three screens. From a
           tablet up every group stands open and its heading is not a control. --}}
        @media (min-width: 720px) {
            .os-res-top { pointer-events: none; }
        }
        @media (max-width: 719.98px) {
            .os-res { padding-bottom: 0.2rem; }
            .os-res-top { cursor: pointer; align-items: center; min-height: 2.75rem; padding-bottom: 0.5rem; }
            .os-res:not([open]) .os-res-top { border-bottom-color: transparent; padding-bottom: 0.3rem; }
            .os-res-top span::after { content: " +"; }
            .os-res[open] .os-res-top span::after { content: " -"; }
        }
        .os-end { display: grid; grid-template-columns: 3.7rem minmax(0, 1fr); gap: 0.1rem 0.7rem; align-items: baseline; padding-block: 0.65rem; }
        .os-end[hidden] { display: none; }
        .os-end + .os-end { border-top: 1px solid var(--hp-line); }
        .os-verb { font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.06em; color: var(--os-k); }
        .os-verb.is-post { color: var(--os-s); }
        .os-verb.is-put { color: var(--os-v); }
        .os-verb.is-delete { color: var(--os-del); }
        #hp .os-end code { font-size: 0.8rem; font-weight: 700; color: var(--hp-ink); overflow-wrap: anywhere; background: none; padding: 0; }
        .os-end code i { font-style: normal; font-weight: 400; color: var(--hp-ink-3); }
        .os-end p { grid-column: 2; margin: 0; font-size: 0.9rem; line-height: 1.4; color: var(--hp-ink-2); }

        {{-- The limits, beside the lines that set them. --}}
        .os-limits { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: clamp(2.5rem, 5vw, 4rem); }
        @media (min-width: 1024px) {
            .os-limits { grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); align-items: stretch; }
        }
        .os-limits .hp-figs { height: 100%; align-items: center; }
        #hp .os-limits .hp-num { font-size: clamp(3rem, 5.2vw, 4.75rem); }
        #hp .os-limits .hp-fig { padding: clamp(1.25rem, 2.2vw, 2rem) clamp(0.75rem, 1.4vw, 1.25rem); }
        #hp .os-limits .hp-fig strong { font-size: 1.1rem; }
        .os-limits .os-code { display: flex; flex-direction: column; }
        .os-limits .os-code-body { flex: 1 1 auto; }

        .os-files { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.75rem; margin-top: 1.25rem; }
        @media (min-width: 640px) { .os-files { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1100px) { .os-files { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        .os-file { display: flex; flex-direction: column; gap: 0.6rem; padding: 1.15rem; border: 1px solid var(--hp-line); border-radius: 1.25rem; background: var(--hp-bg-2); transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; }
        .os-file:hover { transform: translateY(-3px); border-color: var(--hp-blue); box-shadow: var(--hp-card-shadow); }
        .os-file b { font-family: var(--hp-mono); font-size: 0.86rem; font-variation-settings: normal; color: var(--hp-blue); overflow-wrap: anywhere; }
        .os-file span { font-family: var(--hp-mono); font-size: 0.68rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); }
        .os-file p { margin: 0; font-size: 0.93rem; line-height: 1.5; color: var(--hp-ink-2); }
        #hp .os-sub-h { margin-top: clamp(2.5rem, 5vw, 4rem); }
        .os-sub-lead { max-width: 44rem; margin-top: 0.7rem; font-size: 1.05rem; line-height: 1.55; color: var(--hp-ink-2); }
        .os-api-foot { max-width: 52rem; margin-top: 1.25rem; font-size: 0.95rem; line-height: 1.55; color: var(--hp-ink-3); }

        {{-- ---- 6. Ways in, and the way out ---- --}}
        {{-- In the order they are numbered, on every screen and for a keyboard. From a laptop the
           two short ones stand in one column and the one with commands fills the other; what all
           three need runs under them. --}}
        .os-ways { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; margin-top: clamp(2rem, 4vw, 3.25rem); }
        @media (min-width: 900px) {
            .os-ways { grid-template-columns: minmax(0, 1fr) minmax(0, 1.3fr); }
            .os-way.is-first { grid-column: 1; grid-row: 1; }
            .os-way.is-second { grid-column: 2; grid-row: 1 / span 2; }
            .os-way.is-third { grid-column: 1; grid-row: 2; }
            .os-needs { grid-column: 1 / -1; }
            {{-- The wide one is as tall as the two beside it: its commands are set a size up. --}}
            .os-way.is-second .os-cmd { padding: 1.35rem 1.4rem; font-size: 0.95rem; line-height: 2.05; }
            .os-way.is-second .hp-more { margin-top: auto; padding-top: 1.25rem; }
        }
        .os-way { display: flex; flex-direction: column; padding: clamp(1.25rem, 2.4vw, 1.9rem); border: 1px solid var(--hp-line); border-radius: 1.5rem; background: var(--hp-bg); }
        .dark .os-way { background: var(--hp-bg-2); }
        .os-way-top { display: flex; align-items: center; gap: 0.6rem; font-family: var(--hp-mono); font-size: 0.74rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.1em; color: var(--hp-ink-3); }
        #hp .os-way h3 { margin-top: 0.9rem; font-size: 1.6rem; font-variation-settings: 'wght' 800; letter-spacing: -0.035em; }
        .os-way p { margin-top: 0.6rem; font-size: 1rem; line-height: 1.55; color: var(--hp-ink-2); }
        .os-way .hp-more { margin-top: 1.25rem; align-self: flex-start; }
        {{-- A command is never folded: a line that breaks is a line somebody types wrong. On a
           narrow screen the block scrolls sideways instead. --}}
        .os-cmd { position: relative; margin-top: 1.1rem; padding: 1rem 1.1rem; border: 1px solid var(--hp-line-2); border-radius: 0.9rem; background: var(--os-code-bg); font-family: var(--hp-mono); font-size: 0.82rem; font-variation-settings: normal; line-height: 1.85; color: var(--os-code-ink); overflow-x: auto; }
        #hp .os-cmd code { display: block; width: max-content; padding: 0; padding-inline-end: 4.5rem; white-space: pre; background: none; font-size: inherit; color: inherit; }
        @media (max-width: 639.98px) {
            #hp .os-cmd code { padding-inline-end: 0; }
        }
        #hp .os-cmd code.os-cmd-note { color: var(--os-c); }
        #hp .os-cmd code b { font-weight: 400; font-variation-settings: normal; color: var(--hp-ink-3); user-select: none; }
        .os-copy {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2rem;
            padding: 0 0.7rem;
            border: 1px solid var(--hp-line-2);
            border-radius: 0.55rem;
            background: var(--hp-bg-2);
            font-family: var(--hp-mono);
            font-size: 0.72rem;
            font-weight: 700;
            font-variation-settings: normal;
            color: var(--hp-ink-2);
            cursor: pointer;
            transition: border-color 0.2s ease, color 0.2s ease;
        }
        .os-copy[hidden] { display: none; }
        .os-copy:hover { border-color: var(--hp-blue); color: var(--hp-blue); }
        .os-cmd .os-copy { position: absolute; top: 0.6rem; inset-inline-end: 0.6rem; }
        @media (max-width: 639.98px) {
            .os-cmd .os-copy { position: static; display: flex; margin: 0 0 0.5rem auto; }
            .os-cmd .os-copy[hidden] { display: none; }
        }
        {{-- What it needs is a line of text, not a row of chips: nothing here can be pressed. --}}
        .os-needs { padding: 0.75rem clamp(1.25rem, 2.4vw, 1.9rem) 0; }
        .os-needs-top { font-family: var(--hp-mono); font-size: 0.72rem; font-weight: 700; font-variation-settings: normal; letter-spacing: 0.12em; text-transform: uppercase; color: var(--hp-ink-3); }
        .os-needs-line { margin-top: 0.5rem; font-family: var(--hp-mono); font-size: 0.86rem; font-variation-settings: normal; line-height: 1.7; color: var(--hp-ink); }
        .os-needs-note { margin-top: 0.6rem; font-size: 0.92rem; line-height: 1.55; color: var(--hp-ink-3); }
        .os-door { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem 4rem; margin-top: clamp(3rem, 7vw, 6rem); padding-top: clamp(2.5rem, 5vw, 4.5rem); border-top: 2px solid var(--hp-ink); }
        @media (min-width: 1024px) { .os-door { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); align-items: start; } }
        .os-door-list { display: grid; gap: 0; }
        .os-door-list li { padding-block: 1.15rem; border-top: 1px solid var(--hp-line-2); }
        .os-door-list li:last-child { border-bottom: 1px solid var(--hp-line-2); }
        #hp .os-door-list b { display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem; font-size: 1.2rem; font-variation-settings: 'wght' 760; letter-spacing: -0.02em; }
        .os-door-list p { margin-top: 0.4rem; font-size: 1rem; line-height: 1.55; color: var(--hp-ink-2); }

        {{-- ---- The clone line, under the claim box ---- --}}
        .os-clone { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.6rem; margin-top: 1.5rem; }
        .os-clone code { display: block; max-width: 100%; padding: 0.55rem 0.9rem; border: 1px solid rgba(125, 165, 255, 0.3); border-radius: 0.7rem; background: #0b1124; font-size: 0.82rem; white-space: pre; overflow-x: auto; color: #dfe6fb; }
        #hp .hp-finale .os-copy { min-height: 2.4rem; background: #101831; border-color: rgba(125, 165, 255, 0.4); color: #dfe6fb; }

        {{-- Motion off: everything of this page's stands still. Not the shared arrival
           (es-fade-up), which ends visible only by running: stopped part way it would leave the
           headline's path unseen. --}}
        @media (prefers-reduced-motion: reduce) {
            #hp [class^="os-"]:not(.es-fade-up),
            #hp [class*=" os-"]:not(.es-fade-up),
            #hp .os-own-btn svg,
            #hp .os-toggle::before { transition: none !important; animation: none !important; }
        }
        {{-- On a phone the headline comes down a step so the buttons stay on the first screen. --}}
        @media (max-width: 639.98px) {
            .os-line { font-size: 0.74rem; }
            .os-ln { width: 2.9rem; padding-inline-end: 0.7rem; }
            .os-code-live { margin-inline-start: 0; }
        }
    </style>

    @php
        // Every quotation on the page. Each is found by what its lines say (see SourceExcerpt);
        // one that cannot be found is null and its claim is printed without it.
        $src = \App\Utils\SourceExcerpt::class;

        $heroSource = $src::view('marketing.open-source', 'source:headline', [['hp-ink-grad', 1]]);

        // The method the whole page turns on. The three lines of its first test are the
        // selfhost; the rest is what a schedule on a hosted install is asked.
        $isPro = $src::method(\App\Models\Role::class, 'isPro', [
            ["config('app.hosted')", 3, 'self'],
            ['hasActiveSubscription()', 3, 'hosted'],
            ['onGenericTrial()', 3, 'hosted'],
            ['$this->isEnterprise()', 3, 'hosted'],
            ['return $this->plan_expires', 1, 'hosted'],
        ]);

        // The line of that method a selfhost stops at, as a number to say.
        $yesLine = collect($isPro['lines'] ?? [])->first(fn ($line) => $line['mark'] === 'self' && str_contains($line['html'], 'return'))['n'] ?? null;
        // How this server itself is set. An env value, the same for every visitor.
        $osHosted = (bool) config('app.hosted');

        // What changes when the install stops being ours: [what, the plan here, here, on yours].
        // Every row is backed by a gate that tests config('app.hosted').
        $rows = [
            ['Selling paid tickets', 'pro', plan_price($proMonthly).' a month', 'Included'],
            ['Free registration and RSVP', 'free', 'Unlimited', 'Included'],
            ['Check-in dashboard, waitlists, promo codes and passes', 'pro', plan_price($proMonthly).' a month', 'Included'],
            ['REST API and webhooks', 'pro', '', 'Included'],
            ['Custom domain', 'ent', '', 'The install is your domain'],
            ['Team members on one schedule', 'ent', 'Up to five', 'No cap'],
            ['Newsletter sends', null, '10, 100 or 1,000 emails a month, each recipient counting as one', 'No monthly cap'],
            ['The "Powered by" credit', 'pro', 'Removed', 'Gone, but one small licence credit stays on public pages'],
            ['Daily import from a list of event URLs', null, 'Not available', 'Selfhost only'],
            ['AI parsing and translation', null, 'Our key, with a daily cap per plan', 'Your own Gemini or OpenAI key, no daily cap'],
        ];
        $planNames = ['free' => 'Free', 'pro' => 'Pro', 'ent' => 'Enterprise'];

        // Nine claims, each with the lines that prove it.
        $claims = [
            [
                'key' => 'keys',
                'title' => 'An API key is never stored',
                'plan' => 'pro',
                'excerpt' => $src::take('app/Http/Middleware/ApiAuthentication.php', '$keyPrefix = substr(', 'break;', [['$keyPrefix = substr(', 1], ['Hash::check(', 1]], 2),
                'note' => 'The column holds eight characters of a SHA-256 of the key, enough to find the row. The key itself is checked against a bcrypt hash, so the database never holds a value that works.',
                'body' => [
                    'Generate a key in your account settings and send it in the <span class="os-inline">X-API-Key</span> header. A key expires a year after it is made.',
                ],
                'more' => ['API reference', marketing_url('/docs/developer/api')],
            ],
            [
                'key' => 'webhooks',
                'title' => 'Webhooks are signed',
                'plan' => 'pro',
                'excerpt' => $src::take('app/Jobs/SendWebhook.php', '$jsonBody = json_encode(', '$timestamp = now()', [["hash_hmac('sha256'", 1]]),
                'note' => 'An HMAC-SHA256 over the exact body that is sent, so you can verify a delivery came from your install and not from somebody who guessed your endpoint.',
                'body' => [
                    'Fourteen event types, from <span class="os-inline">sale.created</span> through <span class="os-inline">ticket.scanned</span> to <span class="os-inline">feedback.submitted</span>. Each delivery carries its signature in a header.',
                    'Up to three attempts, about thirty then sixty seconds apart, on a real queue; the default sync queue sends once. The secret is stored encrypted.',
                ],
                'more' => ['Webhook reference', marketing_url('/docs/developer/webhooks')],
            ],
            [
                'key' => 'fetches',
                'title' => 'A typed address cannot reach your network',
                'plan' => 'free',
                'excerpt' => $src::take('app/Utils/UrlUtils.php', 'public static function isBlockedIp(', 'return true;', [['FILTER_FLAG_NO_PRIV_RANGE', 2]], 1),
                'note' => 'Private, loopback and link-local ranges, the cloud metadata address among them, are refused before a request leaves.',
                'body' => [
                    'An address somebody types in (a webhook target, a calendar feed, a page to import an event from) is resolved first, and refused if it points inside your own network.',
                    'Inline scripts carry a nonce, and user markdown is purified before it renders.',
                ],
            ],
            [
                'key' => 'data',
                'title' => 'Your data leaves when you do',
                'plan' => 'free',
                'excerpt' => $src::take('app/Services/BackupService.php', "'role' => \$roleData,", "'appointment_types' =>", [["'events' => \$eventsData", 1]]),
                'note' => 'Ticket types, promo codes and sales travel inside each event.',
                'body' => [
                    'Backup and restore exports a schedule with its events, sub-schedules, ticket types, sales and appointment types, optionally with the images, and imports the same archive into another install.',
                    'It is on every plan. That is the honest test of no lock-in, and it is not behind a paywall.',
                ],
            ],
            [
                'key' => 'languages',
                'title' => 'Twelve languages, one array',
                'plan' => 'free',
                'excerpt' => $src::take('config/app.php', "'supported_languages' => [", '],'),
                'note' => null,
                'body' => [
                    'Arabic, German, English, Spanish, Estonian, French, Hebrew, Italian, Dutch, Portuguese, Romanian and Russian, with right-to-left handled properly.',
                    'The language list is one array in the config, so adding a thirteenth is a translation job, not a code change.',
                ],
            ],
            [
                'key' => 'quiet',
                'title' => 'No third-party script loads until you set one',
                'plan' => 'free',
                'excerpt' => $src::take('.env.example', '# OneSignal push notifications', 'ONESIGNAL_REST_API_KEY=', [['ONESIGNAL_APP_ID=', 2]]),
                'note' => 'Blank means off: no script is loaded and no call is made for it.',
                'body' => [
                    'Front-end libraries are vendored into the repository rather than pulled from a CDN, and every font, the typefaces a schedule can pick for its own page included, is served from your install. Each third-party script the app can load waits for a variable or an account you set (analytics, ads, web push, Stripe\'s checkout, Cloudflare\'s bot check and error reporting among them), so a fresh install sends a visitor\'s browser nowhere else.',
                    'SMTP, Stripe or PayPal, Google or Microsoft calendar credentials, an AI key: each is yours to set, or to leave unset, and the feature that needs it stays out of the way until you do.',
                    'The server itself is not silent, and it is in the open: once a day it asks GitHub whether there is a newer release, and once a month it downloads a GeoIP file. Both are entries in <span class="os-inline">routes/console.php</span>.',
                ],
            ],
            [
                'key' => 'federation',
                'title' => 'Federation is off until you turn it on',
                'plan' => 'free',
                'excerpt' => $src::method(\App\Services\FederationService::class, 'isEnabled', [["Setting::get('federation_enabled')", 1]]),
                'note' => 'A setting on the instance, not a plan tier, and false until an administrator changes it.',
                'body' => [
                    'A selfhosted install can share its public events with the eventschedule.com listings, and every listing links back to the event on your own site. A schedule is only listed once someone who manages it chooses to list it.',
                    'eventschedule.com is the receiving end and runs a moderation queue instead.',
                ],
            ],
            [
                'key' => 'updates',
                'title' => 'You choose when to update',
                'plan' => 'free',
                'excerpt' => $src::take('config/self-update.php', "'version_installed' =>", 1),
                'note' => 'The release this copy reports. On your server it moves when you say so.',
                'body' => [
                    'A selfhosted install can pull the next release from the admin area, or you can ignore the button and deploy from the tag yourself. Nobody moves your version for you.',
                ],
            ],
            [
                'key' => 'audit',
                'title' => 'It keeps its own log',
                'plan' => 'free',
                'excerpt' => $src::take('app/Services/AuditService.php', 'return AuditLog::create([', "'created_at' => now(),", [["'old_values' =>", 2]], 1),
                'note' => 'What changed and what it was before, with passwords and keys struck out first.',
                'body' => [
                    'Every schedule keeps a searchable log of what changed, filterable by date and category. A commit log for your calendar, in other words, and it is not a paid add-on.',
                ],
            ],
        ];

        // The licence, as it stands in the repository root.
        $licence = $src::paragraphs('LICENSE');

        // The API surface, transcribed from routes/api.php.
        $endpoints = [
            ['GET', '/api/schedules', 'Schedules', 'Every schedule the key can reach'],
            ['GET', '/api/schedules/{subdomain}', 'Schedules', 'One schedule, by subdomain'],
            ['POST', '/api/schedules', 'Schedules', 'Create a schedule'],
            ['PUT', '/api/schedules/{subdomain}', 'Schedules', 'Update it'],
            ['DELETE', '/api/schedules/{subdomain}', 'Schedules', 'Delete it'],
            ['GET', '/api/schedules/{subdomain}/groups', 'Sub-schedules', 'List the sub-schedules'],
            ['POST', '/api/schedules/{subdomain}/groups', 'Sub-schedules', 'Create one'],
            ['PUT', '/api/schedules/{subdomain}/groups/{group_id}', 'Sub-schedules', 'Rename or recolour it'],
            ['DELETE', '/api/schedules/{subdomain}/groups/{group_id}', 'Sub-schedules', 'Delete it'],
            ['GET', '/api/events', 'Events', 'List events, paginated'],
            ['GET', '/api/events/{id}', 'Events', 'One event'],
            ['POST', '/api/events/{subdomain}', 'Events', 'Create an event on a schedule, or update it by your own id'],
            ['PUT', '/api/events/{id}', 'Events', 'Update it'],
            ['DELETE', '/api/events/{id}', 'Events', 'Delete it'],
            ['POST', '/api/events/{id}/cancel', 'Events', 'Call it off and keep its sales'],
            ['POST', '/api/events/{id}/restore', 'Events', 'Undo a cancellation'],
            ['POST', '/api/events/flyer/{event_id}', 'Events', 'Attach a flyer image'],
            ['GET', '/api/sales', 'Sales', 'List ticket sales'],
            ['GET', '/api/sales/{id}', 'Sales', 'One sale'],
            ['POST', '/api/sales', 'Sales', 'Record a sale'],
            ['PUT', '/api/sales/{id}', 'Sales', 'Mark it paid, cancel it or refund it'],
            ['DELETE', '/api/sales/{id}', 'Sales', 'Delete it'],
            ['GET', '/api/feedback', 'Feedback', 'Post-event ratings and comments'],
            ['GET', '/api/fan-content', 'Feedback', 'Fan photos, video and comments'],
            ['GET', '/api/categories', 'Categories', 'The system category list'],
            ['GET', '/api/categories/{subdomain}', 'Categories', 'The effective list for one schedule'],
        ];
        $resources = collect($endpoints)->groupBy(fn ($row) => $row[2]);
        $verbCounts = collect($endpoints)->countBy(fn ($row) => $row[0]);
        // The two rate limits, on the line that sets them.
        $limits = $src::take('app/Http/Middleware/ApiAuthentication.php', '$isWriteOperation = in_array(', '$rateLimit = $isWriteOperation', [['$rateLimit = $isWriteOperation', 1]]);
        // The same list as the file it was transcribed from: the whole group behind the key.
        // Each route is marked by its method, so the method filter can light its lines there too.
        $routeFile = $src::take('routes/api.php', 'Route::middleware([ApiAuthentication::class])->group', '});', [
            ['Route::get(', 1, 'GET', true],
            ['Route::post(', 1, 'POST', true],
            ['Route::put(', 1, 'PUT', true],
            ['Route::delete(', 1, 'DELETE', true],
        ]);
        // Where each group stands from a laptop up. The star is the note about the three
        // routes that take no key, which fills the room under the longest group.
        $apiColumns = [['Schedules', 'Sub-schedules'], ['Events', '*'], ['Sales', 'Feedback', 'Categories']];
        $apiColumns[2] = array_merge($apiColumns[2], $resources->keys()->diff(collect($apiColumns)->flatten())->all());
        $blob = \App\Utils\SourceExcerpt::REPOSITORY.'/blob/main/';

        // Published surfaces, all served as static files out of public/.
        $specFiles = [
            ['/api/openapi.json', 'OpenAPI 3.0.3', 'Eighteen paths, twenty-eight operations, request and response schemas.'],
            ['/llms.txt', 'Short brief', 'The product in one page: schedule types, auth, plans, rate limits.'],
            ['/llms-full.txt', 'Long brief', 'The same, expanded, for a model with room to read.'],
            ['/.well-known/agents.json', 'Agent flows', 'Four named flows with their steps written out: register and set up, create an event with tickets, sell tickets, manage a schedule.'],
        ];

        $requirements = ['PHP 8.2 or newer', 'MySQL 5.7+ or MariaDB 10.3+', 'Apache or Nginx', 'HTTPS', 'One cron entry'];
        $dockerLines = ['git clone https://github.com/eventschedule/dockerfiles', 'cd dockerfiles', 'docker compose up --build -d'];

        // The files the page quotes, drifting under the hero. Each goes to where it is quoted.
        $lineup = [
            [
                ['LICENSE', '#license', '#eab308'],
                ['app/Models/Role.php', '#switch', '#4e81fa'],
                ['routes/api.php', '#api', '#22d3ee'],
                ['app/Jobs/SendWebhook.php', '#proof-webhooks', '#10b981'],
                ['app/Utils/UrlUtils.php', '#proof-fetches', '#f97316'],
                ['app/Services/BackupService.php', '#proof-data', '#0ea5e9'],
                ['config/app.php', '#proof-languages', '#14b8a6'],
            ],
            [
                ['app/Http/Middleware/ApiAuthentication.php', '#proof-keys', '#4e81fa'],
                ['.env.example', '#proof-quiet', '#eab308'],
                ['app/Services/FederationService.php', '#proof-federation', '#22d3ee'],
                ['config/self-update.php', '#proof-updates', '#10b981'],
                ['app/Services/AuditService.php', '#proof-audit', '#f97316'],
                ['composer.json', '#install', '#0ea5e9'],
                ['public/api/openapi.json', '#spec', '#14b8a6'],
            ],
        ];

        $faqs = [
            [
                'q' => 'What licence is Event Schedule under?',
                'a' => 'The Attribution Assurance License, an OSI-approved licence adapted from the BSD licence. composer.json declares it as AAL and the full text is the LICENSE file in the repository root. It is permissive rather than copyleft: use, modify and redistribute in source or binary form, provided the licence text travels with the code, and provided a binary redistribution displays the author name, "Event Schedule" and the project URL when the program launches. It is short, and it is printed in full further up this page. Read it rather than taking a paragraph on a marketing page for it.',
            ],
            [
                'q' => 'Do I get every feature if I selfhost?',
                'a' => 'All but one. Role::isPro() and Role::isEnterprise() both return true the moment config(\'app.hosted\') is false, so uncapped ticket sales, the REST API, webhooks, custom fields, unlimited team members and uncapped newsletter sends are simply on. The exception is a custom domain per schedule: ResolveCustomDomain only runs in hosted mode, and on a selfhost the whole install already sits on a domain you chose. Two things you supply yourself: an AI key if you want the parsing and translation features, and a Stripe or PayPal account for payouts.',
            ],
            [
                'q' => 'Is the REST API free on eventschedule.com?',
                'a' => 'No. API access is a Pro feature at '.plan_price($proMonthly).' a month, and the check runs on reads as well as writes: under app/Http/Controllers/Api a single-resource route answers 403 for a free schedule, and a list route filters non-Pro schedules out with the wherePro() scope. On a selfhosted install it is on by default, because a selfhost resolves to the top tier.',
            ],
            [
                'q' => 'What are the API rate limits?',
                'a' => 'Three hundred reads a minute per IP and thirty writes a minute per IP, counted in ApiAuthentication; a write is a POST, PUT or DELETE and everything else is a read. Ten failed attempts with the same key value block that key for fifteen minutes, and a key expires a year after it is made. Creating an event has its own throttle of thirty a minute.',
            ],
            [
                'q' => 'How do I install it on my own server?',
                'a' => 'Three ways. Softaculous does a one-click install on a cPanel host, the eventschedule/dockerfiles repository has images and a Compose file, or you install by hand: PHP 8.2 or newer, MySQL or MariaDB, Apache or Nginx with rewrites, HTTPS, and one cron entry running the scheduler every minute. The installation guide walks through all of it.',
            ],
            [
                'q' => 'Can I take my data out again?',
                'a' => 'Yes, on every plan. Backup and restore exports a schedule with its events, sub-schedules, ticket types, sales and appointment types, optionally with its images, and imports the same archive into another install. That is the honest test of no lock-in, and it is not behind a paywall.',
            ],
            [
                'q' => 'Do you accept contributions?',
                'a' => 'Issues and pull requests are open on GitHub. Read the code first: it is a fairly ordinary Laravel 11 application with Vue on the front end, and the parts most people want to change - views, translations, integrations - are where you would expect to find them.',
            ],
        ];
    @endphp

    {{-- ============================================================
         1. Hero
         ============================================================ --}}
    <section id="top" class="es-hero hp-hero os-hero">
        <div class="hp-hero-sky" aria-hidden="true"></div>

        <div class="hp-hero-copy">
            <div class="os-flip" data-os-flip>
                <div class="os-flip-front">
                    {{-- source:headline --}}
                    <h1 class="hp-h1">
                        <x-marketing.hero-eyebrow class="es-fade-up es-d-1 hp-eyebrow">Open source event calendar</x-marketing.hero-eyebrow>
                        <span class="es-mask">
                            <span class="es-mask-line">Open source. Every claim here</span>
                        </span>
                        <span class="es-mask es-mask-2">
                            <span class="es-mask-line">has a <span class="hp-ink-grad">file path.</span></span>
                        </span>
                    </h1>
                    {{-- /source:headline --}}
                </div>
                @if ($heroSource)
                    <div class="os-flip-back" data-os-flip-back>
                        <x-marketing.source-excerpt :excerpt="$heroSource" />
                    </div>
                @endif
            </div>

            @if ($heroSource)
                {{-- The headline's own path, under the words that promise one. Shown by the script
                     that makes it work. It carries the buttons' delay class (es-d-3) so the
                     link-preview card, which leaves a hero's buttons out by that class, leaves
                     this out with them. --}}
                <div class="es-fade-up es-d-3 os-own" data-os-own hidden>
                    <button type="button" class="os-own-btn" data-os-turn>
                        <span class="os-path"><span class="os-own-dir">resources/views/</span>marketing/open-source.blade.php:{{ collect($heroSource['lines'])->firstWhere('mark', 'proof')['n'] ?? $heroSource['first'] }}</span>
                        <span data-os-turn-label data-over="Turn the headline over" data-back="Turn it back">Turn the headline over</span>
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9a8 8 0 0114-3l2 2m0-4v4h-4M20 15a8 8 0 01-14 3l-2-2m0 4v-4h4" /></svg>
                    </button>
                </div>
            @endif

            <p class="es-fade-up es-d-2 hp-sub">
                Event Schedule is open source under the Attribution Assurance License. Selfhost the whole thing on your own server, or drive the hosted version through the REST API. Either way, the code you are trusting is code you can read.
            </p>

            <div class="es-fade-up es-d-3 hp-hero-actions">
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="hp-btn hp-btn-ghost is-still">
                    <svg aria-hidden="true" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    Read the source
                </a>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                    Start for free
                    <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            <nav class="es-fade-up es-d-4 hp-toc" aria-label="On this page">
                @foreach ([['switch', 'The switch'], ['proof', 'Check our work'], ['license', 'The licence'], ['api', 'The API'], ['install', 'Ways in']] as $tocIndex => [$tocId, $tocLabel])
                    <a href="#{{ $tocId }}"><b>{{ sprintf('%02d', $tocIndex + 1) }}</b>{{ $tocLabel }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    {{-- The files the page quotes, as its line-up. --}}
    <section class="hp-lineup os-lineup" aria-label="The files this page quotes">
        <h2 class="sr-only">The files this page quotes</h2>
        <div class="es-marquee-mask">
            @foreach ($lineup as $rowIndex => $row)
                <div class="es-marquee" data-marquee="{{ $rowIndex === 0 ? '1' : '-1' }}">
                    <div class="es-marquee-track">
                        @for ($i = 0; $i < 2; $i++)
                            @foreach ($row as [$label, $href, $dot])
                                <a href="{{ $href }}" @if ($i === 1) aria-hidden="true" tabindex="-1" data-loop-copy @endif class="hp-act">{{ $label }}<i style="background: {{ $dot }};" aria-hidden="true"></i></a>
                            @endforeach
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================================================
         2. The switch (night)
         ============================================================ --}}
    <section id="switch" class="hp-dark os-night os-switch" data-os-switch data-os-first="{{ $osHosted ? 'hosted' : 'self' }}">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>01 / The switch</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Selfhost, and the plan tiers <span class="hp-ink-grad">disappear.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    This is not a marketing promise, it is two early returns in one model. <span class="os-inline">isPro()</span> and <span class="os-inline">isEnterprise()</span> both return true the moment the app is not running hosted, so a selfhosted install resolves to the top tier and nothing on it sits behind a paywall.
                </p>
            </div>

            <div class="os-env" data-reveal="panel">
                <span class="os-env-file">.env</span>
                <div class="os-env-card" data-os-env>
                    <p class="os-env-line" dir="ltr">
                        <span>IS_HOSTED</span><span class="os-env-eq">=</span>
                        {{-- The word stands until the script swaps it for the switch. --}}
                        <span class="os-env-static" data-os-static>false</span>
                        <button type="button" class="os-toggle" role="switch" aria-checked="true" aria-label="IS_HOSTED" data-os-toggle hidden>
                            <span data-on>true</span><span data-off>false</span>
                        </button>
                    </p>
                    <p class="os-env-hint" data-os-hint hidden>
                        @if ($osHosted)
                            <span data-os-when="hosted">This server runs with it set to <b>true</b>. Press it to see what your own server would change.</span>
                            <span data-os-when="self">That is your own server. Every plan below is struck out, and the running of it is yours.</span>
                        @else
                            <span data-os-when="self">This server runs with it set to <b>false</b>. Press it to see what a hosted install puts on a plan.</span>
                            <span data-os-when="hosted">That is a hosted install, such as eventschedule.com. Each row below is on the plan it names.</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- The same switch, small, for when the large one has scrolled away. --}}
            <div class="os-dock" data-os-dock hidden>
                <div class="os-dock-in os-mini" dir="ltr">
                    <span>IS_HOSTED=</span>
                    <button type="button" class="os-toggle" role="switch" aria-checked="true" aria-label="IS_HOSTED" data-os-toggle>
                        <span data-on>true</span><span data-off>false</span>
                    </button>
                    <span class="os-dock-say"><span data-os-when="hosted">eventschedule.com</span><span data-os-when="self">your own server</span></span>
                </div>
            </div>

            <div class="os-switch-grid">
                <div class="os-ledger" data-reveal>
                    <p class="os-ledger-head" data-os-head hidden>
                        <span data-os-when="hosted">On eventschedule.com</span><span data-os-when="self">On your own server</span>
                    </p>
                    @foreach ($rows as $rowIndex => [$rowName, $rowPlan, $rowHere, $rowYours])
                        <div class="os-row" style="--i: {{ $rowIndex }};">
                            <p class="os-row-name">{{ $rowName }}</p>
                            <div class="os-vals">
                                <p class="os-cell is-hosted">
                                    <span class="os-cell-side">Hosted</span>
                                    @if ($rowPlan)
                                        <span @class(['os-pill', 'is-pro' => $rowPlan === 'pro', 'is-ent' => $rowPlan === 'ent'])>{{ $planNames[$rowPlan] }}</span>
                                    @endif
                                    <span class="os-cell-text">{{ $rowHere }}</span>
                                </p>
                                <p class="os-cell is-self">
                                    <span class="os-cell-side">Yours</span>
                                    <svg class="os-cell-tick" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                                    <span class="os-cell-text">{{ $rowYours }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach

                    <div class="os-otherway">
                        <h3>The line that goes the other way</h3>
                        <p>
                            The server, the database, the backups, the TLS certificate, the cron entry and the upgrade window are
                            <b class="os-otherway-who"><span data-os-when="hosted">ours to run.</span><span data-os-when="self">yours to run.</span></b>
                        </p>
                        <p>
                            On eventschedule.com the bill for that is {{ plan_price($proMonthly) }} a month, or {{ plan_price($entMonthly) }} for the two Enterprise lines above. Selfhosting is not free, it is differently priced, and pretending otherwise would be the first false claim on this page.
                        </p>
                        <p>
                            <a href="{{ marketing_url('/selfhost') }}" class="hp-more">
                                What selfhosting actually involves
                                <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </p>
                    </div>

                    {{-- One sentence, written by the script as the switch moves, so that it is said. --}}
                    <p class="os-sum" aria-live="polite" data-os-sum hidden
                        data-hosted="On eventschedule.com: free to publish, then {{ plan_price($proMonthly) }} or {{ plan_price($entMonthly) }} a month. We run it."
                        data-self="On your own server: every row is included, and the bill from us is nothing. You run it."></p>
                </div>

                <div class="os-switch-side" data-reveal style="--reveal-delay: 0.1s;">
                    <x-marketing.source-excerpt :excerpt="$isPro">
                        <x-slot name="tools">
                            {{-- The switch, in the heading of the method it decides. --}}
                            <span class="os-mini" dir="ltr" data-os-mini hidden>
                                <span>IS_HOSTED=</span>
                                <button type="button" class="os-toggle" role="switch" aria-checked="true" aria-label="IS_HOSTED" data-os-toggle>
                                    <span data-on>true</span><span data-off>false</span>
                                </button>
                            </span>
                        </x-slot>
                        <span data-os-when="self">Not hosted: the method answers yes {{ $yesLine ? 'on line '.number_format($yesLine) : 'at once' }}, and never reaches a plan.</span>
                        <span data-os-when="hosted">Hosted: the schedule is asked for a subscription, a trial or a plan. <span class="os-inline">isEnterprise()</span> opens the same way.</span>
                    </x-marketing.source-excerpt>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         3. Check our work
         ============================================================ --}}
    <section id="proof" class="hp-sec os-sec">
        <div class="hp-wrap">
            <div class="hp-head">
                <span class="hp-kicker" data-reveal>02 / Check our work</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Nine claims. <span class="hp-ink-grad">Nine files.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    Nothing on this page is behind a support ticket. Each claim is quoted from the file that proves it, read off the server that sent you this page, and when the proof leaves the file, our build fails.
                </p>
            </div>

            <div class="os-proof" data-os-proof data-reveal style="--n: {{ count($claims) }};">
                @foreach ($claims as $claimIndex => $claim)
                    <div class="os-claim">
                        <h3 class="os-claim-h">
                            <button type="button" class="os-claim-btn" id="proof-tab-{{ $claim['key'] }}" aria-controls="proof-{{ $claim['key'] }}" disabled>
                                <b aria-hidden="true">{{ sprintf('%02d', $claimIndex + 1) }}</b>
                                <span>{{ $claim['title'] }}</span>
                            </button>
                        </h3>
                        <div class="os-claim-panel" id="proof-{{ $claim['key'] }}">
                            <p class="os-claim-say" aria-hidden="true">{{ $claim['title'] }}.</p>
                            <x-marketing.source-excerpt :excerpt="$claim['excerpt']" :note="$claim['note']" />
                            <div class="os-claim-body">
                                @foreach ($claim['body'] as $paragraph)
                                    <p>{!! $paragraph !!}</p>
                                @endforeach
                            </div>
                            <div class="os-claim-meta">
                                @if ($claim['plan'] === 'pro')
                                    <span class="os-pill is-pro" data-os-plan="hosted">Pro on eventschedule.com</span>
                                    <span class="os-pill is-yours" data-os-plan="self">Included on your own server</span>
                                @else
                                    <span class="os-pill">On every plan</span>
                                @endif
                                @isset($claim['more'])
                                    <a href="{{ $claim['more'][1] }}" class="hp-more">
                                        {{ $claim['more'][0] }}
                                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                @endisset
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         4. The licence
         ============================================================ --}}
    <section id="license" class="hp-sec os-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head">
                <span class="hp-kicker" data-reveal>03 / The licence</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Permissive, with one condition: <span class="hp-ink-grad">keep the credit.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    The Attribution Assurance License is OSI-approved and adapted from the BSD licence. @if ($licence) It is {{ $licence['lines'] }} lines and {{ $licence['words'] }} words, @else It is short, @endif which is the best argument for reading it instead of a summary. So here it is.
                </p>
            </div>

            <div class="os-lic">
                @if ($licence)
                    <article class="os-sheet" data-reveal="panel" aria-label="The licence, in full" lang="en" dir="ltr">
                        <div class="os-sheet-top">
                            <span>LICENSE</span>
                            <span>{{ $licence['lines'] }} lines, {{ $licence['words'] }} words</span>
                        </div>
                        <p class="os-sheet-key">
                            <span><i aria-hidden="true"></i>The two conditions that ask something of you</span>
                            <span><i class="is-line" aria-hidden="true"></i>What you are given</span>
                        </p>
                        @foreach ($licence['paragraphs'] as $paragraph)
                            @php
                                // How each paragraph of the file is set. The two conditions that
                                // ask something of you are the ones a reader would mark.
                                $kind = match (true) {
                                    str_starts_with($paragraph, 'ATTRIBUTION ASSURANCE') => 'title',
                                    str_starts_with($paragraph, 'THIS FREE SOFTWARE') => 'small',
                                    (bool) preg_match('/^\((a|b|c)\)/', $paragraph) => 'credit',
                                    (bool) preg_match('/^\(\d\)/', $paragraph) => 'sub',
                                    (bool) preg_match('/^[12]\. /', $paragraph) => 'ask',
                                    (bool) preg_match('/^\d\. /', $paragraph) => 'clause',
                                    str_starts_with($paragraph, 'Redistribution and use') => 'grant',
                                    str_starts_with($paragraph, 'These conditions') => 'plain',
                                    default => 'head',
                                };
                            @endphp
                            @if ($kind === 'ask' || $kind === 'credit')
                                <p @class(['is-clause' => $kind === 'ask', 'is-sub' => $kind === 'credit'])><mark>{{ $paragraph }}</mark></p>
                            @elseif ($kind === 'grant')
                                <p><span class="is-grant">{{ $paragraph }}</span></p>
                            @else
                                <p @class(['is-title' => $kind === 'title', 'is-small' => $kind === 'small', 'is-sub' => $kind === 'sub', 'is-clause' => $kind === 'clause', 'is-head' => $kind === 'head'])>{{ $paragraph }}</p>
                            @endif
                        @endforeach
                    </article>
                @endif

                <div class="os-lic-notes" data-reveal-group="100">
                    <div class="os-note" data-reveal>
                        <span class="os-note-tag"><i class="is-line" aria-hidden="true"></i>The grant</span>
                        <h3>Use it and change it</h3>
                        <p>Redistribution and use in source and binary forms, with or without modification, are permitted. Commercially too. Fork it, strip out what you do not need, run it for a client.</p>
                    </div>
                    <div class="os-note" data-reveal>
                        <span class="os-note-tag"><i aria-hidden="true"></i>Conditions 1 and 2</span>
                        <h3>Carry the notice</h3>
                        <p>Redistributed source has to display the licence text. A binary redistribution carries it in the documentation and shows the author name, "Event Schedule" and the project URL when the program launches.</p>
                    </div>
                    <div class="os-note" data-reveal>
                        <span class="os-note-tag">What it is not</span>
                        <h3>Not MIT, not AGPL</h3>
                        <p>Permissive rather than copyleft, so your changes are yours to keep private. Also not MIT, which asks for no attribution display. If that distinction matters to a legal team, hand them the file.</p>
                    </div>
                    <p class="os-lic-foot" data-reveal>
                        The package manifest, <a href="{{ $blob }}composer.json" target="_blank" rel="noopener noreferrer" class="os-path">composer.json</a>, declares it as AAL, and
                        <a href="https://opensource.org/licenses/AAL" target="_blank" rel="noopener noreferrer" class="hp-inline">the licence is listed at opensource.org</a>.
                        None of this is legal advice, and none of it is a substitute for reading the file.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         5. The API
         ============================================================ --}}
    <section id="api" class="hp-sec os-sec">
        <div class="hp-wrap">
            <div class="hp-head is-center">
                <span class="hp-kicker" data-reveal>04 / The API</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Twenty-six endpoints, <span class="hp-ink-grad">one header.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    Send an <span class="os-inline">X-API-Key</span> header, get JSON back: schedules, sub-schedules, events, categories, sales and refunds, and read access to attendee feedback. Here is the whole surface, transcribed from the route file rather than described.
                </p>
            </div>

            <div class="os-api-bar" data-reveal>
                <h3 class="os-api-count">Twenty-six authenticated endpoints, six resources</h3>
                {{-- Shown by the script that makes them work. --}}
                <div class="os-api-tools" data-os-api-tools hidden>
                    <div class="os-verbs" role="group" aria-label="Show by method" data-os-verbs>
                        <button type="button" aria-pressed="true" data-os-verb="">All <span>{{ count($endpoints) }}</span></button>
                        @foreach (['GET', 'POST', 'PUT', 'DELETE'] as $verb)
                            <button type="button" aria-pressed="false" data-os-verb="{{ $verb }}">{{ $verb }} <span>{{ $verbCounts[$verb] ?? 0 }}</span></button>
                        @endforeach
                    </div>
                    @if ($routeFile)
                        <span class="os-seg" role="group" aria-label="Show the endpoints as">
                            <button type="button" aria-pressed="true" data-os-face="page">As a list</button>
                            <button type="button" aria-pressed="false" data-os-face="source">As the route file</button>
                        </span>
                    @endif
                </div>
            </div>
            @if ($routeFile)
                <div data-os-api-source hidden>
                    <x-marketing.source-excerpt :excerpt="$routeFile" note="The whole group behind the key, as it is declared. Press a method above and its routes are marked." class="os-api-file" />
                </div>
            @endif
            <div class="os-api" data-os-api-list data-reveal>
                @foreach ($apiColumns as $apiColumn)
                    <div class="os-api-col">
                        @foreach ($apiColumn as $resourceName)
                            @if ($resourceName === '*')
                                <p class="os-api-out">
                                    Three routes sit outside the list because they are how you get a key in the first place, so they take none themselves: send a verification code, register, log in.
                                    <a href="{{ $blob }}routes/api.php" target="_blank" rel="noopener noreferrer" class="os-path">routes/api.php</a>
                                </p>
                            @elseif ($resources->has($resourceName))
                                <details class="os-res" open>
                                    <summary class="os-res-top">
                                        <h4>{{ $resourceName }}</h4>
                                        <span data-os-res-count>{{ $resources[$resourceName]->count() }}</span>
                                    </summary>
                                    @foreach ($resources[$resourceName] as [$eVerb, $ePath, $eResource, $eWhat])
                                        <div class="os-end" data-os-end="{{ $eVerb }}">
                                            <span @class(['os-verb', 'is-post' => $eVerb === 'POST', 'is-put' => $eVerb === 'PUT', 'is-delete' => $eVerb === 'DELETE'])>{{ $eVerb }}</span>
                                            {{-- A break is offered after each slash, so a long path folds between segments. --}}
                                            <code dir="ltr">{!! preg_replace('/(\{[^}]+\})/', '<i>$1</i>', str_replace('/', '/<wbr>', e($ePath))) !!}</code>
                                            <p>{{ $eWhat }}</p>
                                        </div>
                                    @endforeach
                                </details>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
            <p class="os-api-gate" data-reveal>
                <span class="os-pill is-pro" data-os-plan="hosted">Pro</span>
                <span class="os-pill is-yours" data-os-plan="self">Included</span>
                On eventschedule.com the API is a Pro feature at {{ plan_price($proMonthly) }} a month, and the check runs on reads too: a single-resource route answers 403, a list route filters non-Pro schedules out. On a selfhost the same check passes by default.
                <a href="{{ marketing_url('/docs/developer/api') }}" class="hp-inline">API reference</a>
            </p>

            <div class="os-limits">
                <div class="hp-figs" data-reveal>
                    <div class="hp-fig">
                        <div class="hp-num"><span data-count-to="300">300</span></div>
                        <strong>Reads a minute</strong>
                        <p>Per IP. Everything that is not a write.</p>
                    </div>
                    <div class="hp-fig">
                        <div class="hp-num"><span data-count-to="30">30</span></div>
                        <strong>Writes a minute</strong>
                        <p>Per IP. Creating an event has its own throttle of thirty a minute.</p>
                    </div>
                    <div class="hp-fig">
                        <div class="hp-num"><span data-count-to="10">10</span></div>
                        <strong>Wrong tries</strong>
                        <p>With the same key value, and that value is blocked for fifteen minutes.</p>
                    </div>
                </div>
                <div data-reveal style="--reveal-delay: 0.1s;">
                    <x-marketing.source-excerpt :excerpt="$limits" note="Thirty and three hundred, on the line that sets them." />
                </div>
            </div>

            <div id="spec">
                <h3 class="hp-h3 os-sub-h" data-reveal>The surface is <span class="hp-ink-grad">published,</span> not described.</h3>
                <p class="os-sub-lead" data-reveal>
                    Four files, served straight out of the public directory. Point a client generator or an agent at them and skip the prose entirely.
                </p>
                <div class="os-files" data-reveal-group="80">
                    @foreach ($specFiles as [$sPath, $sKind, $sBody])
                        <a href="{{ url($sPath) }}" target="_blank" rel="noopener noreferrer" class="os-file" data-reveal>
                            <span>{{ $sKind }}</span>
                            <b>{{ $sPath }}</b>
                            <p>{{ $sBody }}</p>
                        </a>
                    @endforeach
                </div>
                <p class="os-api-foot" data-reveal>
                    Agents get their own page, with the flows written out.
                    <a href="{{ marketing_url('/for-ai-agents') }}" class="hp-inline">Event Schedule for AI agents</a>
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================================
         6. Ways in, and the way out
         ============================================================ --}}
    <section id="install" class="hp-sec os-sec hp-alt">
        <div class="hp-wrap">
            <div class="hp-head">
                <span class="hp-kicker" data-reveal>05 / Ways in</span>
                <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s;">
                    Three ways <span class="hp-ink-grad">in.</span>
                </h2>
                <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s;">
                    It is Laravel 11 on PHP 8.2, with MySQL for storage, Vue for the front end and Vite for the build. Nothing exotic to stand up. Pick by how much of the stack you want to touch.
                </p>
            </div>

            <div class="os-ways" data-reveal-group="100">
                <div class="os-way is-first" data-reveal>
                    <div class="os-way-top"><span>01</span><span class="os-pill">One click</span></div>
                    <h3>Softaculous</h3>
                    <p>If your host runs cPanel with Softaculous, Event Schedule is in the installer library. Database, files and permissions are handled for you.</p>
                    <a href="https://www.softaculous.com/apps/calendars/Event_Schedule" target="_blank" rel="noopener noreferrer" class="hp-more">
                        Open the listing
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="os-way is-second" data-reveal>
                    <div class="os-way-top"><span>02</span><span class="os-pill">Compose</span></div>
                    <h3>Docker</h3>
                    <p>Images and a Compose file live in their own repository, so the application repo stays free of deployment plumbing. The first build takes a few minutes while dependencies install and assets compile. On Alpine images the web user is the numeric UID 82, not www-data.</p>
                    <div class="os-cmd" dir="ltr">
                        <button type="button" class="os-copy" data-copy="{{ implode("\n", $dockerLines) }}" hidden>Copy</button>
                        @foreach ($dockerLines as $dockerLine)
                            <code><b aria-hidden="true">$ </b>{{ $dockerLine }}</code>
                        @endforeach
                        <code class="os-cmd-note"># then open http://localhost:8080</code>
                    </div>
                    <a href="https://github.com/eventschedule/dockerfiles" target="_blank" rel="noopener noreferrer" class="hp-more">
                        eventschedule/dockerfiles
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="os-way is-third" data-reveal>
                    <div class="os-way-top"><span>03</span><span class="os-pill">By hand</span></div>
                    <h3>Five steps</h3>
                    <p>Database, files, permissions, environment, cron. The guide names the PHP extensions, the ownership commands and the things that usually go wrong first.</p>
                    <a href="{{ marketing_url('/docs/selfhost/installation') }}" class="hp-more">
                        Installation guide
                        <svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="os-needs" data-reveal>
                    <span class="os-needs-top">What any of the three needs</span>
                    <p class="os-needs-line">{{ implode(' · ', $requirements) }}</p>
                    <p class="os-needs-note">
                        The PHP version and the extension list are not a recommendation, they are the requirements block of the package manifest.
                        <a href="{{ $blob }}composer.json" target="_blank" rel="noopener noreferrer" class="os-path">composer.json</a>
                    </p>
                </div>
            </div>

            <div id="exit" class="os-door">
                <div>
                    <span class="hp-kicker" data-reveal>And the way out</span>
                    <h2 class="hp-h2" data-reveal style="--reveal-delay: 0.08s; margin-top: 1rem;">
                        The proof of no lock-in is <span class="hp-ink-grad">the door.</span>
                    </h2>
                    <p class="hp-lead" data-reveal style="--reveal-delay: 0.14s; margin-top: 1.1rem;">
                        Every vendor says you are not locked in. The test is whether the way out is a feature you can use today, on the free plan, without asking.
                    </p>
                </div>
                <ul class="os-door-list" data-reveal-group="90">
                    <li data-reveal>
                        <b>Take the data <a href="{{ $blob }}app/Services/BackupService.php" target="_blank" rel="noopener noreferrer" class="os-path">app/Services/BackupService.php</a></b>
                        <p>One archive holds a schedule with its events, tickets and sales, and another install reads it back in. <a href="#proof-data" class="hp-inline">See the lines</a></p>
                    </li>
                    <li data-reveal>
                        <b>Take the code <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="os-path">eventschedule/eventschedule</a></b>
                        <p>What is on GitHub is the product, not a trimmed demo of it. eventschedule.com runs this application, which is why a selfhost gets the features rather than a subset of them.</p>
                    </li>
                    <li data-reveal>
                        <b>Keep the database</b>
                        <p>Underneath all of it is a MySQL database on hardware you chose, which is the part no export format can replace.</p>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    {{-- ============================================================
         FAQ, related pages, finale
         ============================================================ --}}
    <x-seo.faq-schema :items="$faqs" />
    <x-marketing.hp-faq :items="$faqs" id="faq" kicker="Before you clone it" lead="What developers ask before they clone it.">Frequently asked <span class="hp-ink-grad">questions</span></x-marketing.hp-faq>

    <x-marketing.related-pages />

    <x-marketing.hp-finale lead="Clone it and run it on your own hardware, or take a subdomain here and skip the server entirely. Publishing a calendar is free forever either way." placeholder="your-schedule" foot="No credit card, and no account needed to look.">
        Read it. Then <span class="hp-ink-grad">run it.</span>
        <x-slot name="after">
            <div class="os-clone" dir="ltr">
                <code>git clone https://github.com/eventschedule/eventschedule.git</code>
                <button type="button" class="os-copy" data-copy="git clone https://github.com/eventschedule/eventschedule.git" hidden>Copy</button>
            </div>
            <p class="hp-finale-foot">
                <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer">Clone it, read it, open an issue</a>
            </p>
        </x-slot>
    </x-marketing.hp-finale>

    {{-- The page's own moving parts: the headline's two faces, the switch, the claims, the copy
         buttons. Plain script, no inline handlers. Without it everything is still on the page:
         both sides of the switch read, and every claim stands open with its file. --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var each = function (list, fn) { Array.prototype.forEach.call(list, fn); };
            var press = function (group, button) {
                each(group.querySelectorAll('button'), function (b) {
                    b.setAttribute('aria-pressed', b === button ? 'true' : 'false');
                });
            };
            // matchMedia().addEventListener is missing from older Safari, which has addListener.
            var watch = function (query, fn) {
                if (query.addEventListener) query.addEventListener('change', fn);
                else if (query.addListener) query.addListener(fn);
            };

            // 1. The headline, turned over to the markup that drew it. Where the two faces differ
            //    in height, the page is moved by the difference, so the button that was pressed
            //    stays under the finger that pressed it.
            var flip = document.querySelector('[data-os-flip]');
            var own = document.querySelector('[data-os-own]');
            var turn = own ? own.querySelector('[data-os-turn]') : null;
            if (flip && turn && flip.querySelector('[data-os-flip-back]')) {
                var label = turn.querySelector('[data-os-turn-label]');
                own.hidden = false;
                turn.addEventListener('click', function () {
                    var source = !flip.classList.contains('is-source');
                    var before = turn.getBoundingClientRect().top;
                    flip.classList.toggle('is-source', source);
                    turn.classList.toggle('is-turned', source);
                    if (label) label.textContent = label.getAttribute(source ? 'data-back' : 'data-over');
                    var moved = turn.getBoundingClientRect().top - before;
                    if (moved) {
                        // At once: the site scrolls smoothly, and a glide here would be the jump made longer.
                        var page = document.documentElement;
                        var was = page.style.scrollBehavior;
                        page.style.scrollBehavior = 'auto';
                        window.scrollTo(window.pageXOffset, window.pageYOffset + moved);
                        page.style.scrollBehavior = was;
                    }
                });
            }

            // 2. The switch. "hosted" is how this server runs; "self" is your own. The state is
            //    written on the page's wrapper, because the plan tags further down answer to it.
            var band = document.querySelector('[data-os-switch]');
            var toggles = band ? band.querySelectorAll('[data-os-toggle]') : [];
            if (band && toggles.length) {
                var root = document.getElementById('hp') || band;
                var sum = band.querySelector('[data-os-sum]');
                var set = function (state) {
                    root.setAttribute('data-state', state);
                    each(toggles, function (toggle) {
                        toggle.setAttribute('aria-checked', state === 'hosted' ? 'true' : 'false');
                    });
                    if (sum) sum.textContent = sum.getAttribute('data-' + state) || '';
                };
                each(band.querySelectorAll('[data-os-static]'), function (el) { el.hidden = true; });
                each(band.querySelectorAll('[data-os-hint], [data-os-head], [data-os-sum], [data-os-dock], [data-os-mini]'), function (el) { el.hidden = false; });
                each(toggles, function (toggle) {
                    toggle.hidden = false;
                    toggle.addEventListener('click', function () {
                        set(root.getAttribute('data-state') === 'hosted' ? 'self' : 'hosted');
                    });
                });
                // It opens on how this server itself is set.
                set(band.getAttribute('data-os-first') === 'self' ? 'self' : 'hosted');

                // The bar shows once the large switch has gone up out of the window.
                var dock = band.querySelector('[data-os-dock]');
                var card = band.querySelector('[data-os-env]');
                if (dock && card && 'IntersectionObserver' in window) {
                    new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            dock.classList.toggle('is-on', !entry.isIntersecting && entry.boundingClientRect.top < 0);
                        });
                    }, { rootMargin: '-72px 0px 0px 0px' }).observe(card);
                }
            }

            // 3. The claims: one open at a time. From a laptop up one is always open, beside the list.
            var proof = document.querySelector('[data-os-proof]');
            if (proof) {
                var buttons = proof.querySelectorAll('.os-claim-btn');
                var wide = window.matchMedia('(min-width: 1024px)');
                var open = function (button, toggleIfOpen) {
                    var was = button.getAttribute('aria-expanded') === 'true';
                    var now = toggleIfOpen && !wide.matches ? !was : true;
                    each(buttons, function (b) {
                        var on = b === button ? now : false;
                        b.setAttribute('aria-expanded', on ? 'true' : 'false');
                        var panel = document.getElementById(b.getAttribute('aria-controls'));
                        if (panel) panel.hidden = !on;
                    });
                };
                proof.classList.add('is-ready');
                each(buttons, function (b, i) {
                    var panel = document.getElementById(b.getAttribute('aria-controls'));
                    if (panel) panel.hidden = i !== 0;
                    b.disabled = false;
                    b.setAttribute('aria-expanded', i === 0 ? 'true' : 'false');
                    b.addEventListener('click', function () { open(b, true); });
                });
                watch(wide, function () {
                    var any = proof.querySelector('.os-claim-btn[aria-expanded="true"]');
                    if (wide.matches && !any && buttons.length) open(buttons[0], false);
                });
                // A link to one claim (the paths under the hero, the door) opens it, every time it
                // is pressed: the address alone only changes once.
                var go = function (id) {
                    if (id.indexOf('proof-') !== 0) return false;
                    var button = document.getElementById('proof-tab-' + id.slice(6));
                    if (!button) return false;
                    open(button, false);
                    (wide.matches ? proof : button).scrollIntoView({ block: 'start' });
                    button.focus({ preventScroll: true });
                    return true;
                };
                document.addEventListener('click', function (e) {
                    if (e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                    var link = e.target.closest ? e.target.closest('a[href^="#proof-"]') : null;
                    if (link && go(link.getAttribute('href').slice(1))) {
                        e.preventDefault();
                        if (history.replaceState) history.replaceState(null, '', link.getAttribute('href'));
                    }
                });
                window.addEventListener('hashchange', function () { go((location.hash || '').slice(1)); });
                if (location.hash) go(location.hash.slice(1));
            }

            // 4. The endpoints: by method, and as the file they were transcribed from. On a phone
            //    all but the first group start folded; asking for a method opens what matched.
            var tools = document.querySelector('[data-os-api-tools]');
            var list = document.querySelector('[data-os-api-list]');
            if (tools && list) {
                var groups = list.querySelectorAll('.os-res');
                var verbs = tools.querySelector('[data-os-verbs]');
                var file = document.querySelector('[data-os-api-source]');
                var narrow = window.matchMedia('(max-width: 719.98px)');
                var verb = '';
                var fold = function () {
                    each(groups, function (group, i) { group.open = verb !== '' || !narrow.matches || i === 0; });
                };
                tools.hidden = false;
                fold();
                watch(narrow, fold);
                tools.addEventListener('click', function (e) {
                    var button = e.target.closest ? e.target.closest('button') : null;
                    if (!button) return;
                    if (button.hasAttribute('data-os-verb')) {
                        verb = button.getAttribute('data-os-verb');
                        press(verbs, button);
                        each(groups, function (group) {
                            var shown = 0;
                            each(group.querySelectorAll('[data-os-end]'), function (row) {
                                row.hidden = verb !== '' && row.getAttribute('data-os-end') !== verb;
                                if (!row.hidden) shown++;
                            });
                            group.hidden = shown === 0;
                            var count = group.querySelector('[data-os-res-count]');
                            if (count) count.textContent = shown;
                        });
                        fold();
                        // In the file, the same press marks that method's lines.
                        if (file) file.setAttribute('data-os-verb-on', verb);
                    } else if (button.hasAttribute('data-os-face') && file) {
                        var source = button.getAttribute('data-os-face') === 'source';
                        press(button.parentNode, button);
                        file.hidden = !source;
                        list.hidden = source;
                        // A list the scroll had not reached would otherwise come back empty.
                        list.classList.add('is-revealed');
                    }
                });
            }

            // 5. Copy.
            if (navigator.clipboard) {
                each(document.querySelectorAll('.os-copy'), function (button) { button.hidden = false; });
                document.addEventListener('click', function (e) {
                    var button = e.target.closest ? e.target.closest('.os-copy') : null;
                    if (!button) return;
                    navigator.clipboard.writeText(button.getAttribute('data-copy') || '').then(function () {
                        // Kept from the first press, so a second one inside two seconds cannot keep "Copied".
                        if (!button.hasAttribute('data-label')) button.setAttribute('data-label', button.textContent);
                        button.textContent = 'Copied';
                        setTimeout(function () { button.textContent = button.getAttribute('data-label'); }, 2000);
                    }).catch(function () {});
                });
            }
        })();
    </script>

    {{-- Motion engines (the finale brings its own confetti) --}}
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
