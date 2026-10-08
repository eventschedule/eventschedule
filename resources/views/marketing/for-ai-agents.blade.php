<x-marketing-layout>
    @php
        // $proMonthly, quoted in the JSON-LD offer and the FAQ, comes from the marketing.*
        // view composer - the same value /pricing renders.
    @endphp

    <x-slot name="title">REST API for AI Agents & Developers - Event Schedule</x-slot>
    <x-slot name="description">29 REST endpoints, an OpenAPI 3.0 spec, llms.txt and agents.json. One POST creates an event with its tickets; one PUT refunds a sale.</x-slot>
    <x-slot name="breadcrumbTitle">For AI Agents</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule API"
        description="A REST API over the whole of Event Schedule: schedules, sub-schedules, events, recurrences, ticket types, sales and refunds, post-event feedback and fan content, with an OpenAPI 3.0 spec, llms.txt and agents.json so an agent can discover it and drive it without a human in the loop."
        keywords="event API, scheduling API, AI agent event management, event automation API, REST API event scheduling, llms.txt, agents.json, OpenAPI" />
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
           For-ai-agents "The Man Page" styles.

           The page has two readers, a developer and the agent the
           developer will point at it, so it is set the way a manual
           page is set: one fixed-pitch face, one size, and every edge
           on the character grid. Columns are measured in ch, rows in
           the 1.5rem line, and the only other sizes are whole
           multiples of the base (2x for a section's sentence, 4x for
           the name) so that large letters still land on cells.

           By day it is the printed manual: paper, ink and one signal
           orange. By night it is an amber terminal. The syntax inks
           are spent as meaning, never as decoration: teal is an
           identifier or a read, green a value or a success, amber a
           number or an update, red a failure or a removal.

           Everything is scoped under #ax. The shared reveal system
           (marketing.css, marketing-home.js) still times the entrances.
           ============================================================== */

        @property --ax-pct {
            syntax: '<integer>';
            inherits: true;
            initial-value: 0;
        }

        #ax {
            --ax-bg: #f7f5ef;
            --ax-bg-2: #efece3;
            --ax-ink: #191816;
            --ax-ink-2: #45423b;
            --ax-dim: #6a665c;
            --ax-line: rgba(25, 24, 22, 0.24);
            --ax-line-2: rgba(25, 24, 22, 0.1);
            --ax-sig: #b93d0a;
            --ax-hot: #d9480f;
            --ax-key: #0f6b6b;
            --ax-str: #2d6a1e;
            --ax-num: #8a5a00;
            --ax-err: #b3261e;
            --ax-solid: #191816;
            --ax-on-solid: #f7f5ef;
            --ax-solid-dim: #b9b4a6;
            --ax-solid-sig: #ff9a5c;
            --ax-on-tag: #f7f5ef;
            --ax-l: 1.5rem;
            --ax-mono: ui-monospace, 'SF Mono', SFMono-Regular, 'Cascadia Mono', 'JetBrains Mono', Menlo, Consolas, 'Liberation Mono', 'DejaVu Sans Mono', monospace;
            position: relative;
            background: var(--ax-bg);
            color: var(--ax-ink);
            font-family: var(--ax-mono);
            font-size: 1rem;
            line-height: var(--ax-l);
            font-variant-ligatures: none;
            font-feature-settings: "liga" 0, "calt" 0;
            tab-size: 2;
            counter-reset: ax-sec;
        }
        .dark #ax {
            --ax-bg: #0c0c0b;
            --ax-bg-2: #161614;
            --ax-ink: #ecdfc6;
            --ax-ink-2: #d3c5a8;
            --ax-dim: #9c9381;
            --ax-line: rgba(255, 180, 84, 0.3);
            --ax-line-2: rgba(255, 180, 84, 0.13);
            --ax-sig: #ffb454;
            --ax-hot: #ffb454;
            --ax-key: #7fd4cf;
            --ax-str: #9bdc8f;
            --ax-num: #f2cc60;
            --ax-err: #ff8f85;
            --ax-solid: #ffb454;
            --ax-on-solid: #0c0c0b;
            --ax-solid-dim: #4a3410;
            --ax-solid-sig: #0c0c0b;
            --ax-on-tag: #0c0c0b;
        }

        /* The bar above takes the manual's stock, so the page reads as one sheet. */
        body > header.sticky {
            background-color: rgba(247, 245, 239, 0.9);
            border-bottom-color: rgba(25, 24, 22, 0.18);
        }
        .dark body > header.sticky {
            background-color: rgba(12, 12, 11, 0.9);
            border-bottom-color: rgba(255, 180, 84, 0.22);
        }

        #ax ::selection { background: var(--ax-solid); color: var(--ax-on-solid); }
        #ax a:focus-visible,
        #ax summary:focus-visible,
        #ax input:focus-visible {
            outline: 2px solid var(--ax-hot);
            outline-offset: 2px;
        }

        .ax-wrap { position: relative; width: min(100% - 3ch, 128ch); margin-inline: auto; }

        /* ---------------------------------------------------------------
           The voices. One size; weight and ink do the rest.
           --------------------------------------------------------------- */
        #ax h1, #ax h2, #ax h3 { font-family: inherit; font-weight: 700; }
        #ax .ax-k { color: var(--ax-key); }
        #ax .ax-s { color: var(--ax-str); }
        #ax .ax-n { color: var(--ax-num); }
        #ax .ax-p { color: var(--ax-dim); }
        .ax-dim { color: var(--ax-dim); }
        .ax-em { color: var(--ax-hot); }
        .dark .ax-em,
        .dark .ax-h1,
        .dark .ax-h2 { text-shadow: 0 0 0.9em rgba(255, 180, 84, 0.22); }
        .ax-link {
            color: var(--ax-sig);
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 0.2em;
        }
        .ax-link:hover { text-decoration-thickness: 2px; }
        /* A link on a line of its own is as tall as its glyphs, 19px. Padding on an inline box
           makes the target taller without moving a line. */
        p > .ax-link,
        .ax-ref a { padding-block: 0.25rem; }
        .ax-prose { max-width: 72ch; text-wrap: pretty; color: var(--ax-ink-2); }
        .ax-prose + .ax-prose { margin-top: var(--ax-l); }
        .ax-prose strong { color: var(--ax-ink); font-weight: 700; }

        .ax-h2 {
            max-width: 46ch;
            font-size: 2rem;
            line-height: 3rem;
            text-wrap: balance;
        }
        .ax-h2 + .ax-prose { margin-top: var(--ax-l); }
        @media (max-width: 760px) {
            .ax-h2 { font-size: 1.5rem; line-height: 2.25rem; }
        }

        /* Plan tags, in brackets, the way a manual marks an option. */
        .ax-tag { color: var(--ax-dim); white-space: nowrap; }
        .ax-tag::before { content: "["; }
        .ax-tag::after { content: "]"; }
        .ax-tag-pro { color: var(--ax-sig); font-weight: 700; }
        .ax-tag-free { color: var(--ax-str); font-weight: 700; }
        .ax-chips { display: flex; flex-wrap: wrap; gap: 0.75rem 1ch; margin-top: var(--ax-l); }
        .ax-chip { padding-inline: 1ch; box-shadow: inset 0 0 0 1px var(--ax-line); color: var(--ax-ink-2); white-space: nowrap; }

        /* Keys. The solid one is reverse video; the plain one is in brackets. */
        .ax-btn {
            display: inline-flex;
            align-items: center;
            gap: 1ch;
            padding: 0.75rem 2ch;
            background: var(--ax-solid);
            color: var(--ax-on-solid);
            font-weight: 700;
            white-space: nowrap;
            box-shadow: inset 0 0 0 1px var(--ax-solid);
            transition: background-color 0.12s steps(2), color 0.12s steps(2);
        }
        .ax-btn:hover { background: transparent; color: var(--ax-sig); }
        .ax-btn-alt { background: transparent; color: var(--ax-ink); box-shadow: none; padding-inline: 0; }
        .ax-btn-alt::before { content: "["; color: var(--ax-dim); }
        .ax-btn-alt::after { content: "]"; color: var(--ax-dim); }
        .ax-btn-alt:hover { color: var(--ax-sig); }
        .ax-btn i { font-style: normal; transition: translate 0.15s ease; }
        .ax-btn:hover i { translate: 0.5ch 0; }
        .ax-btn-alt:hover i { translate: 0 0.2rem; }

        /* ---------------------------------------------------------------
           Sections: a flush-left name, an indented body, a number.
           --------------------------------------------------------------- */
        .ax-sec {
            padding-block: calc(var(--ax-l) * 2) calc(var(--ax-l) * 4);
            border-top: 1px solid var(--ax-line);
            counter-increment: ax-sec;
            scroll-margin-top: 4rem;
        }
        .ax-sec-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 2ch;
            margin-bottom: calc(var(--ax-l) * 2);
        }
        .ax-name { display: block; font-weight: 700; color: var(--ax-ink); }
        .ax-ref { color: var(--ax-dim); white-space: nowrap; }
        .ax-ref a:hover { color: var(--ax-sig); }
        .ax-ref::after { content: "  \00a7" counter(ax-sec); }
        .ax-body { padding-inline-start: 8ch; }
        .ax-gap { margin-top: calc(var(--ax-l) * 2); }
        .ax-gap-1 { margin-top: var(--ax-l); }
        @media (max-width: 900px) {
            .ax-body { padding-inline-start: 0; }
            .ax-sec { padding-block: var(--ax-l) calc(var(--ax-l) * 3); }
            .ax-sec-head { margin-bottom: var(--ax-l); }
        }

        /* ---------------------------------------------------------------
           Code. Each line is its own span, so it can carry a number and
           arrive on its own.
           --------------------------------------------------------------- */
        .ax-box { background: var(--ax-bg-2); box-shadow: inset 0 0 0 1px var(--ax-line); min-width: 0; }
        .ax-box-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0 2ch;
            padding: 0.75rem 2ch;
            box-shadow: inset 0 -1px 0 var(--ax-line);
        }
        .ax-box-head > .ax-right { margin-inline-start: auto; color: var(--ax-dim); }
        .ax-path { font-weight: 700; overflow-wrap: anywhere; min-width: 0; }
        .ax-param { color: var(--ax-sig); }
        #ax .ax-code {
            margin: 0;
            padding: 0.75rem 2ch;
            overflow-x: auto;
            white-space: pre;
            font: inherit;
            color: var(--ax-ink);
            background: none;
            counter-reset: ax-ln;
        }
        .ax-num .ax-ln::before {
            counter-increment: ax-ln;
            content: counter(ax-ln);
            display: inline-block;
            width: 2ch;
            margin-inline-end: 2ch;
            text-align: end;
            color: var(--ax-dim);
            opacity: 0.75;
            -webkit-user-select: none;
            user-select: none;
        }
        .ax-arrow { color: var(--ax-dim); }
        .ax-verb {
            display: inline-block;
            width: 8ch;
            text-align: center;
            font-weight: 700;
            background: var(--ax-v, var(--ax-ink));
            color: var(--ax-on-tag);
        }
        .ax-get { --ax-v: var(--ax-key); }
        .ax-post { --ax-v: var(--ax-str); }
        .ax-put { --ax-v: var(--ax-num); }
        .ax-del { --ax-v: var(--ax-err); }
        .ax-sc { padding-inline: 1ch; font-weight: 700; color: var(--ax-str); box-shadow: inset 0 0 0 1px currentColor; white-space: nowrap; }
        .ax-sc-err { color: var(--ax-err); }

        .ax-caret {
            display: inline-block;
            width: 1ch;
            height: 1em;
            margin-inline-start: 0.25ch;
            vertical-align: -0.12em;
            background: var(--ax-hot);
            animation: ax-blink 1.1s steps(1) infinite;
        }
        @keyframes ax-blink { 50% { opacity: 0; } }

        /* Streaming: a block marked data-reveal="stream" shows its frame at once
           and lets its lines in one at a time. With no JavaScript, or with
           motion turned down, every line is simply there. */
        html.es-anim #ax [data-reveal="stream"]:not(.is-revealed) { opacity: 1; }
        html.es-anim #ax [data-reveal="stream"]:not(.is-revealed) .ax-ln,
        html.es-anim #ax [data-reveal="stream"]:not(.is-revealed) .ax-late { opacity: 0; }
        html.es-anim #ax [data-reveal="stream"].is-revealed { transition: none; }
        html.es-anim #ax [data-reveal="stream"].is-revealed .ax-ln {
            transition: opacity 0s linear calc(var(--i, 0) * 55ms + 120ms);
        }
        html.es-anim #ax [data-reveal="stream"].is-revealed .ax-late {
            transition: opacity 0.3s ease calc(var(--i, 0) * 55ms + 200ms);
        }
        /* The hero is on screen at load, so its lines run on a clock instead. */
        html.es-anim #ax .ax-hero .ax-ln { animation: ax-arrive 0.08s linear calc(var(--i, 0) * 60ms + 1.9s) both; }
        html.es-anim #ax .ax-hero .ax-late { animation: ax-arrive 0.3s ease calc(var(--i, 0) * 60ms + 2s) both; }
        @keyframes ax-arrive { from { opacity: 0; } to { opacity: 1; } }

        /* ---------------------------------------------------------------
           1. The head of the page
           --------------------------------------------------------------- */
        .ax-hero { position: relative; overflow: clip; padding-block: var(--ax-l) calc(var(--ax-l) * 4); }
        .ax-guides {
            position: absolute;
            inset: calc(var(--ax-l) * 3) 0 0 0;
            pointer-events: none;
            background: repeating-linear-gradient(90deg,
                transparent 0 calc(9.5ch - 0.5px),
                var(--ax-line-2) calc(9.5ch - 0.5px) calc(9.5ch + 0.5px),
                transparent calc(9.5ch + 0.5px) 10ch);
            -webkit-mask-image: linear-gradient(to bottom, black, transparent 78%);
            mask-image: linear-gradient(to bottom, black, transparent 78%);
        }
        .ax-manhead { position: relative; display: flex; justify-content: space-between; gap: 2ch; font-weight: 700; white-space: nowrap; }
        .ax-manhead span:nth-child(2) { color: var(--ax-dim); font-weight: 400; }
        .ax-ruler { position: relative; overflow: hidden; white-space: nowrap; color: var(--ax-dim); opacity: 0.7; -webkit-user-select: none; user-select: none; }
        @media (max-width: 760px) {
            .ax-manhead span:nth-child(2) { display: none; }
        }
        .ax-hero-grid { position: relative; display: grid; grid-template-columns: minmax(0, 1fr); gap: calc(var(--ax-l) * 2) 4ch; margin-top: calc(var(--ax-l) * 2); }
        @media (min-width: 1260px) {
            .ax-hero-grid { grid-template-columns: 68ch minmax(0, 1fr); }
        }
        .ax-h1 { font-size: 2rem; line-height: 3rem; }
        @media (min-width: 760px) { .ax-h1 { font-size: 3rem; line-height: 4.5rem; } }
        @media (min-width: 1260px) { .ax-h1 { font-size: 4rem; line-height: 4.5rem; } }
        #ax .ax-eyebrow {
            display: block;
            margin-block: var(--ax-l);
            font-family: var(--ax-mono);
            font-size: 1rem;
            line-height: var(--ax-l);
            color: var(--ax-ink-2);
        }
        #ax .ax-eyebrow::before { content: "eventschedule - "; color: var(--ax-dim); }
        .ax-type { display: block; white-space: nowrap; }
        html.es-anim #ax .ax-type-1 { animation: ax-typing 0.75s steps(17) 0.25s backwards; }
        html.es-anim #ax .ax-type-2 { animation: ax-typing 0.75s steps(17) 1.05s backwards; }
        @keyframes ax-typing { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0 0 0); } }
        .ax-hero-desc .ax-name { display: block; margin-bottom: var(--ax-l); }
        .ax-cta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem 3ch; margin-top: calc(var(--ax-l) * 2); }
        .ax-synopsis { position: relative; margin-top: calc(var(--ax-l) * 2.5); }
        .ax-synopsis > .ax-name { display: block; margin-bottom: var(--ax-l); }
        .ax-exchange { display: grid; grid-template-columns: minmax(0, 1fr); gap: var(--ax-l) 2ch; align-items: start; }
        /* Side by side from the width where the answer's longest line (its url) fits beside the
           call. From 1100 to 1260 that line was cut by its own box. */
        @media (min-width: 1260px) {
            .ax-exchange { grid-template-columns: 50ch minmax(0, 1fr); }
        }
        .ax-exchange-back { min-width: 0; }
        .ax-synopsis .ax-prose { margin-top: var(--ax-l); max-width: 72ch; }

        /* ---------------------------------------------------------------
           2. Conventions: four facts, set as a manual sets its options
           --------------------------------------------------------------- */
        .ax-facts { display: grid; gap: var(--ax-l) 0; max-width: 100ch; }
        .ax-fact { display: grid; grid-template-columns: minmax(0, 1fr); }
        .ax-fact dt { font-weight: 700; color: var(--ax-key); }
        .ax-fact dd strong { display: block; font-weight: 700; }
        .ax-fact dd p { max-width: 72ch; color: var(--ax-ink-2); text-wrap: pretty; }
        @media (min-width: 760px) {
            .ax-fact { grid-template-columns: 20ch minmax(0, 1fr); }
        }
        .ax-two { display: grid; grid-template-columns: minmax(0, 1fr); gap: var(--ax-l) 2ch; align-items: start; }
        /* Side by side only where both fit, and not as halves: the failure envelope's longest
           line is four characters wider than half the measure, and was cut by its own box. */
        @media (min-width: 1280px) {
            .ax-two { grid-template-columns: minmax(0, 9fr) minmax(0, 11fr); }
        }

        /* ---------------------------------------------------------------
           3. The ledger. A real table laid out on a grid, so the three
           columns hold across every group, a group's head stays under
           the site bar while its rows pass, and a narrow container
           re-lays each row as two lines. The method filter is radio
           inputs read with :has(); the count is a CSS counter, and a
           row that is not displayed does not count.
           --------------------------------------------------------------- */
        .ax-ledger { container-type: inline-size; }
        .ax-ledger-in { counter-reset: ax-row; }
        .ax-filter { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.75rem 2ch; margin: 0 0 var(--ax-l); padding: 0; border: 0; min-width: 0; }
        .ax-filter legend { float: left; padding: 0; margin-inline-end: 2ch; color: var(--ax-dim); }
        .ax-filter label { position: relative; padding-inline: 1ch; box-shadow: inset 0 0 0 1px var(--ax-line); cursor: pointer; white-space: nowrap; }
        #ax .ax-filter input { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; opacity: 0; cursor: pointer; }
        .ax-filter b { font-weight: 700; color: var(--ax-v, var(--ax-ink)); }
        .ax-filter label:has(input:checked) { background: var(--ax-solid); color: var(--ax-on-solid); box-shadow: none; }
        .ax-filter label:has(input:checked) b { color: inherit; }
        .ax-filter label:has(input:focus-visible) { outline: 2px solid var(--ax-hot); outline-offset: 2px; }

        .ax-table { display: grid; grid-template-columns: 10ch max-content minmax(0, 1fr); width: 100%; }
        .ax-table thead,
        .ax-table tbody,
        .ax-table tr { display: grid; grid-template-columns: subgrid; grid-column: 1 / -1; }
        .ax-table th,
        .ax-table td { padding: 0.375rem 0; text-align: start; font-weight: 400; vertical-align: top; }
        .ax-table thead th { padding-block: 0 0.75rem; color: var(--ax-dim); font-weight: 700; box-shadow: inset 0 -1px 0 var(--ax-ink); }
        .ax-grp { position: sticky; top: calc(4rem + 1px); z-index: 2; background: var(--ax-bg); box-shadow: inset 0 -1px 0 var(--ax-line); }
        .ax-table .ax-grp th { grid-column: 1 / -1; padding-block: 1.125rem 0.375rem; }
        .ax-grp b { display: inline-block; min-width: 16ch; font-weight: 700; text-transform: uppercase; }
        .ax-grp span { color: var(--ax-dim); }
        .ax-row { counter-increment: ax-row; }
        .ax-row:nth-child(odd) { background: var(--ax-bg-2); }
        .ax-table .ax-row th { padding-inline-end: 3ch; font-weight: 700; white-space: nowrap; }
        .ax-row td:last-child { color: var(--ax-ink-2); padding-inline-end: 1ch; }
        .ax-row:hover { box-shadow: inset 0 0 0 1px var(--ax-line); }
        .ax-ledger-foot { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 0 2ch; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--ax-ink); color: var(--ax-dim); }
        .ax-count::before { content: counter(ax-row); }

        #ax .ax-ledger:has(#ax-v-get:checked) .ax-row:not([data-verb="GET"]),
        #ax .ax-ledger:has(#ax-v-post:checked) .ax-row:not([data-verb="POST"]),
        #ax .ax-ledger:has(#ax-v-put:checked) .ax-row:not([data-verb="PUT"]),
        #ax .ax-ledger:has(#ax-v-del:checked) .ax-row:not([data-verb="DELETE"]),
        #ax .ax-ledger:has(#ax-v-get:checked) tbody:not(:has([data-verb="GET"])),
        #ax .ax-ledger:has(#ax-v-post:checked) tbody:not(:has([data-verb="POST"])),
        #ax .ax-ledger:has(#ax-v-put:checked) tbody:not(:has([data-verb="PUT"])),
        #ax .ax-ledger:has(#ax-v-del:checked) tbody:not(:has([data-verb="DELETE"])) { display: none; }

        @container (max-width: 96ch) {
            .ax-table,
            .ax-table thead,
            .ax-table tbody,
            .ax-table tr,
            .ax-table th,
            .ax-table td { display: block; }
            .ax-table thead th { position: absolute; width: 1px; height: 1px; padding: 0; overflow: hidden; clip-path: inset(50%); white-space: nowrap; box-shadow: none; }
            .ax-table .ax-row { display: grid; grid-template-columns: 9ch minmax(0, 1fr); padding: 0.375rem 1ch; }
            .ax-table .ax-row th { white-space: normal; overflow-wrap: anywhere; padding: 0; }
            .ax-table .ax-row td { padding: 0; }
            .ax-table .ax-row td:last-child { grid-column: 1 / -1; padding-top: 0.375rem; }
            .ax-table .ax-grp th { padding-inline: 1ch; }
            .ax-grp b { display: block; }
        }

        /* ---------------------------------------------------------------
           4. Files: a tree, drawn with borders that sit on the grid
           --------------------------------------------------------------- */
        .ax-tree { max-width: 104ch; }
        .ax-tree-root { font-weight: 700; }
        .ax-tree li { position: relative; padding: 0 0 var(--ax-l) 5ch; }
        .ax-tree li::before { content: ""; position: absolute; inset: 0 auto 0 1ch; border-inline-start: 1px solid var(--ax-ink); }
        .ax-tree li::after { content: ""; position: absolute; inset: 0.75rem auto auto 1ch; width: 3ch; border-top: 1px solid var(--ax-ink); }
        .ax-tree li:last-child { padding-bottom: 0; }
        .ax-tree li:last-child::before { bottom: auto; height: 0.75rem; }
        .ax-file { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0 2ch; }
        .ax-file a { font-weight: 700; color: var(--ax-sig); text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: 0.2em; overflow-wrap: anywhere; }
        .ax-file a:hover { text-decoration-thickness: 2px; }
        .ax-file .ax-fill { flex: 1; min-width: 2ch; height: 0; align-self: center; border-top: 1px dotted var(--ax-line); }
        .ax-file .ax-meta { color: var(--ax-ink); white-space: nowrap; }
        .ax-tree p { max-width: 86ch; margin-top: 0.375rem; color: var(--ax-ink-2); text-wrap: pretty; }

        /* ---------------------------------------------------------------
           5. Examples: one session, three tool calls
           --------------------------------------------------------------- */
        .ax-session-head { display: flex; flex-wrap: wrap; gap: 0 2ch; padding: 0.75rem 2ch; background: var(--ax-solid); color: var(--ax-on-solid); font-weight: 700; }
        .ax-session-head span:last-child { margin-inline-start: auto; font-weight: 400; color: var(--ax-solid-dim); }
        .ax-session { box-shadow: inset 0 0 0 1px var(--ax-line); padding: var(--ax-l) 2ch; }
        .ax-plan { color: var(--ax-dim); }
        .ax-plan b { color: var(--ax-sig); font-weight: 700; }
        .ax-call { display: grid; grid-template-columns: minmax(0, 1fr); gap: var(--ax-l) 4ch; align-items: start; margin-top: calc(var(--ax-l) * 2); }
        @media (min-width: 1100px) {
            .ax-call { grid-template-columns: 62ch minmax(0, 1fr); }
        }
        .ax-call-io { display: grid; gap: 0.75rem; min-width: 0; }
        .ax-call-note { color: var(--ax-ink-2); max-width: 72ch; }
        .ax-call-note::before { content: "# note " counter(ax-call); display: block; color: var(--ax-dim); }
        .ax-session { counter-reset: ax-call; }
        .ax-call { counter-increment: ax-call; }
        @media (min-width: 760px) {
            .ax-call { position: relative; padding-inline-start: 4ch; }
            .ax-call::before { content: "\25CF"; position: absolute; inset: 0.75rem auto auto 0; width: 2ch; text-align: center; color: var(--ax-sig); }
            .ax-call::after { content: ""; position: absolute; inset: 2.625rem auto calc(var(--ax-l) * -2 + 0.375rem) 1ch; border-inline-start: 1px solid var(--ax-line); }
            .ax-call:last-of-type::after { bottom: 0; }
        }
        .ax-aside { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0.375rem 2ch; max-width: 104ch; }
        @media (min-width: 900px) {
            .ax-aside { grid-template-columns: 22ch minmax(0, 1fr); }
        }

        /* ---------------------------------------------------------------
           6. Signals
           --------------------------------------------------------------- */
        .ax-band { background: var(--ax-bg-2); }
        .ax-band .ax-box { background: var(--ax-bg); }
        .ax-sig-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: calc(var(--ax-l) * 2) 4ch; align-items: start; }
        @media (min-width: 1100px) {
            .ax-sig-grid { grid-template-columns: 56ch minmax(0, 1fr); }
        }
        .ax-signal-box { max-width: 112ch; }
        .ax-signals { counter-reset: ax-signal; }
        .ax-signals li { display: grid; grid-template-columns: 4ch minmax(0, 1fr); padding: 0.375rem 2ch; counter-increment: ax-signal; }
        .ax-signals li + li { box-shadow: inset 0 1px 0 var(--ax-line-2); }
        .ax-signals li::before { content: counter(ax-signal, decimal-leading-zero); color: var(--ax-dim); }
        .ax-signals li > span { display: grid; grid-template-columns: minmax(0, 1fr); }
        .ax-signals b { font-weight: 700; color: var(--ax-str); }
        .ax-signals i { font-style: normal; color: var(--ax-ink-2); }
        @media (min-width: 700px) {
            .ax-signals li > span { grid-template-columns: 27ch minmax(0, 1fr); }
        }

        /* ---------------------------------------------------------------
           7. Notes
           --------------------------------------------------------------- */
        .ax-notes { display: grid; grid-template-columns: minmax(0, 1fr); gap: 0 6ch; }
        @media (min-width: 1100px) {
            .ax-notes { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .ax-note { padding-block: var(--ax-l) calc(var(--ax-l) * 1.5); border-top: 1px solid var(--ax-line); min-width: 0; }
        .ax-note-tags { display: flex; flex-wrap: wrap; gap: 0 1ch; }
        .ax-note h3 { margin-top: 0.375rem; }
        .ax-note h3::before { content: "-- "; color: var(--ax-dim); font-weight: 400; }
        .ax-note > p { margin-top: 0.75rem; color: var(--ax-ink-2); max-width: 72ch; }
        .ax-note .ax-box { margin-top: var(--ax-l); }
        .ax-exit { margin-top: var(--ax-l); }
        .ax-exit > span { display: block; color: var(--ax-dim); }
        .ax-exit dl div { display: grid; grid-template-columns: 6ch minmax(0, 1fr); padding-block: 0.1875rem; }
        .ax-exit dt { font-weight: 700; color: var(--ax-err); }
        .ax-exit dd { color: var(--ax-ink-2); }

        /* ---------------------------------------------------------------
           8. Callers, 9. Quick start, 10. See also
           --------------------------------------------------------------- */
        .ax-callers { display: grid; grid-template-columns: minmax(0, 1fr); gap: calc(var(--ax-l) * 1.5) 4ch; counter-reset: ax-caller; }
        @media (min-width: 760px) { .ax-callers { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1180px) { .ax-callers { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .ax-caller { display: grid; grid-template-columns: 4ch minmax(0, 1fr); align-content: start; counter-increment: ax-caller; }
        .ax-caller::before { content: "[" counter(ax-caller) "]"; color: var(--ax-sig); font-weight: 700; }
        .ax-caller p { margin-top: 0.375rem; color: var(--ax-ink-2); }
        .ax-caller a { display: inline-block; margin-top: 0.375rem; }

        .ax-steps { max-width: 112ch; border-bottom: 1px solid var(--ax-line); }
        .ax-step { display: grid; grid-template-columns: 4ch minmax(0, 1fr); gap: 0.75rem 0; align-items: start; padding-block: var(--ax-l); border-top: 1px solid var(--ax-line); }
        .ax-step-no { color: var(--ax-sig); font-weight: 700; }
        .ax-step p { margin-top: 0.375rem; max-width: 62ch; color: var(--ax-ink-2); }
        .ax-step .ax-box { grid-column: 2; }
        @media (min-width: 1100px) {
            .ax-step { grid-template-columns: 4ch minmax(0, 1fr) 46ch; gap: 0 4ch; }
            .ax-step .ax-box { grid-column: 3; }
        }
        #ax .ax-step .ax-code { color: var(--ax-str); white-space: pre-wrap; overflow-wrap: anywhere; }
        .ax-step .ax-code::before { content: "> "; color: var(--ax-dim); }

        .ax-also { display: grid; grid-template-columns: minmax(0, 1fr); gap: calc(var(--ax-l) * 2) 6ch; }
        @media (min-width: 1000px) { .ax-also { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .ax-also h2 { font-size: 1rem; line-height: var(--ax-l); margin-bottom: 0.75rem; }
        .ax-also li a { display: grid; grid-template-columns: minmax(0, 1fr); padding: 0.375rem 1ch; margin-inline: -1ch; }
        .ax-also li + li a { box-shadow: inset 0 1px 0 var(--ax-line-2); }
        .ax-also li a:hover { background: var(--ax-solid); color: var(--ax-on-solid); }
        .ax-also li a:hover * { color: inherit; }
        .ax-also b { font-weight: 700; color: var(--ax-sig); }
        .ax-also b small { font-size: 1em; font-weight: 400; color: var(--ax-dim); }
        .ax-also li span { color: var(--ax-ink-2); }
        @media (min-width: 640px) {
            .ax-also li a { grid-template-columns: 22ch minmax(0, 1fr); }
            .ax-also .ax-pages li a { grid-template-columns: minmax(0, 1fr) auto; }
        }
        .ax-also .ax-more { margin-top: var(--ax-l); }

        /* ---------------------------------------------------------------
           The plan band and the closing strip are shared partials. They
           keep their words and their prices; only the print changes.
           --------------------------------------------------------------- */
        #ax .ax-plans { counter-increment: ax-sec; border-top: 1px solid var(--ax-line); padding-top: calc(var(--ax-l) * 2); }
        #ax .ax-plans .ax-sec-head { margin-bottom: 0; }
        #ax .ax-plans > section { background: var(--ax-bg); padding-top: calc(var(--ax-l) * 2); }
        #ax .ax-plans * { font-family: var(--ax-mono); letter-spacing: 0; }
        #ax .ax-plans h2 { font-size: 2rem; line-height: 3rem; font-weight: 700; color: var(--ax-ink); }
        #ax .ax-plans h2 + p { color: var(--ax-ink-2); font-size: 1rem; }
        #ax .ax-plans .grid > div { background: var(--ax-bg-2); border: 0; border-radius: 0; box-shadow: inset 0 0 0 1px var(--ax-line); color: var(--ax-ink); }
        #ax .ax-plans .grid > div:hover { transform: none; box-shadow: inset 0 0 0 1px var(--ax-ink); }
        #ax .ax-plans .grid > div:nth-child(2) { box-shadow: inset 0 0 0 2px var(--ax-sig); }
        #ax .ax-plans .grid > div span,
        #ax .ax-plans .grid > div p,
        #ax .ax-plans .grid > div li { color: var(--ax-ink-2); font-size: 1rem; line-height: var(--ax-l); }
        #ax .ax-plans .grid > div .text-3xl { font-size: 2rem; line-height: 3rem; font-weight: 700; color: var(--ax-ink); }
        #ax .ax-plans .grid > div .uppercase { color: var(--ax-ink); font-size: 1rem; }
        #ax .ax-plans .grid > div .rounded-full { background: var(--ax-solid); color: var(--ax-on-solid); border-radius: 0; font-size: 1rem; padding: 0 1ch; }
        #ax .ax-plans .grid > div svg { color: var(--ax-str); }
        #ax .ax-plans a.font-medium { color: var(--ax-sig); text-decoration: underline; text-underline-offset: 0.2em; }
        #ax .ax-plans a.rounded-2xl { background: var(--ax-solid); color: var(--ax-on-solid); border-radius: 0; box-shadow: inset 0 0 0 1px var(--ax-solid); font-weight: 700; }
        #ax .ax-plans a.rounded-2xl:hover { transform: none; background: transparent; color: var(--ax-sig); }

        #ax .ax-keep > section { background: var(--ax-bg-2); border-top: 1px solid var(--ax-line); }
        #ax .ax-keep * { font-family: var(--ax-mono); letter-spacing: 0; }
        #ax .ax-keep h2 { font-size: 2rem; line-height: 3rem; font-weight: 700; color: var(--ax-ink); }
        #ax .ax-keep p.uppercase { color: var(--ax-sig); font-weight: 700; font-size: 1rem; }
        #ax .ax-keep .grid > a { background: var(--ax-bg); border: 0; border-radius: 0; box-shadow: inset 0 0 0 1px var(--ax-line); }
        #ax .ax-keep .grid > a:hover { transform: none; box-shadow: inset 0 0 0 1px var(--ax-ink); }
        #ax .ax-keep .grid > a > span:first-child { display: none; }
        #ax .ax-keep .grid > a h3 { color: var(--ax-ink); font-size: 1rem; }
        #ax .ax-keep .grid > a p { color: var(--ax-ink-2); font-size: 1rem; line-height: var(--ax-l); }
        #ax .ax-keep .grid > a > span:last-child,
        #ax .ax-keep a.self-start { color: var(--ax-sig); font-size: 1rem; }

        /* ---------------------------------------------------------------
           11. Questions
           --------------------------------------------------------------- */
        .ax-qa { max-width: 104ch; counter-reset: ax-q; border-top: 1px solid var(--ax-ink); }
        .ax-qa details { counter-increment: ax-q; border-bottom: 1px solid var(--ax-line); }
        .ax-qa summary { display: grid; grid-template-columns: 5ch minmax(0, 1fr) 3ch; gap: 0 1ch; padding-block: 0.75rem; cursor: pointer; }
        .ax-qa summary::before { content: "Q" counter(ax-q, decimal-leading-zero); color: var(--ax-sig); font-weight: 700; }
        .ax-qa summary::after { content: "[+]"; color: var(--ax-dim); }
        .ax-qa details[open] summary::after { content: "[-]"; color: var(--ax-sig); }
        .ax-qa summary:hover h3 { color: var(--ax-sig); }
        .ax-qa details p { padding: 0 4ch var(--ax-l) 6ch; max-width: 86ch; color: var(--ax-ink-2); text-wrap: pretty; }
        @media (max-width: 700px) {
            .ax-qa details p { padding-inline: 0; }
        }

        /* ---------------------------------------------------------------
           12. Run: reverse video, as a pager sets its last line
           --------------------------------------------------------------- */
        .ax-run-sec { background: var(--ax-solid); color: var(--ax-on-solid); border-top: 0; }
        .ax-run-sec .ax-name { color: var(--ax-on-solid); }
        .ax-run-sec .ax-ref,
        .ax-run-sec .ax-dim { color: var(--ax-solid-dim); }
        .ax-run-sec .ax-ref a:hover { color: var(--ax-on-solid); }
        .ax-run-sec .ax-h2,
        .dark .ax-run-sec .ax-h2,
        .dark .ax-run-sec .ax-em { text-shadow: none; }
        .ax-run-sec .ax-em { color: var(--ax-solid-sig); }
        .dark .ax-run-sec .ax-em { text-decoration: underline; text-decoration-thickness: 0.12em; text-underline-offset: 0.16em; }
        .ax-run-sec .ax-prose { color: var(--ax-on-solid); }
        .ax-run-sec .ax-link { color: var(--ax-on-solid); font-weight: 700; }
        #ax .ax-run-sec a:focus-visible { outline-color: var(--ax-on-solid); }
        .ax-run-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: calc(var(--ax-l) * 2) 4ch; align-items: start; }
        @media (min-width: 1180px) {
            .ax-run-grid { grid-template-columns: minmax(0, 1fr) 52ch; }
        }
        .ax-run-line { margin-top: calc(var(--ax-l) * 1.5); }
        #ax .ax-run-sec .ax-code { padding-inline: 1ch; }
        .ax-run-sec .ax-box-head,
        .ax-run-sec .ax-box-note { padding-inline: 1ch; }
        .ax-run { display: flex; flex-wrap: wrap; align-items: stretch; gap: 0.75rem 2ch; }
        #ax .ax-claim {
            display: flex;
            align-items: baseline;
            flex: 1 1 39ch;
            min-width: 0;
            max-width: 62ch;
            padding: 0.75rem 2ch;
            background: var(--ax-bg);
            color: var(--ax-ink);
            border: 0;
            border-radius: 0;
            box-shadow: inset 0 0 0 1px var(--ax-bg);
        }
        #ax .ax-claim:focus-within { border-color: transparent; box-shadow: inset 0 0 0 2px var(--ax-hot); }
        .ax-claim .ax-prompt { flex: none; color: var(--ax-dim); white-space: pre; -webkit-user-select: none; user-select: none; }
        .ax-claim .ax-prompt b { color: var(--ax-sig); font-weight: 700; }
        .ax-claim .ax-host { flex: none; color: var(--ax-dim); -webkit-user-select: none; user-select: none; }
        #ax .ax-claim input {
            flex: 1 1 11ch;
            min-width: 11ch;
            margin: -0.75rem 0;
            padding: 0.75rem 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            outline: none;
            font: inherit;
            font-weight: 700;
            color: var(--ax-ink);
            caret-color: var(--ax-hot);
            text-align: end;
        }
        #ax .ax-claim input::placeholder { color: var(--ax-dim); font-weight: 400; opacity: 1; }
        /* Where the browser can size a field to what is typed in it, the address
           reads as one unbroken command. */
        @supports (field-sizing: content) {
            #ax .ax-claim input { flex: 0 1 auto; field-sizing: content; min-width: 2ch; max-width: 31ch; text-align: start; }
            .ax-claim .ax-host { flex: 1 1 auto; }
        }
        @media (max-width: 640px) {
            .ax-claim .ax-prompt span { display: none; }
        }
        .ax-run .ax-btn { background: var(--ax-bg); color: var(--ax-ink); box-shadow: inset 0 0 0 1px var(--ax-bg); }
        .ax-run .ax-btn:hover { background: transparent; color: var(--ax-on-solid); box-shadow: inset 0 0 0 1px var(--ax-on-solid); }
        .ax-run .ax-btn kbd { font: inherit; color: var(--ax-sig); }
        .ax-run .ax-btn:hover kbd { color: inherit; }
        .ax-run-sec .ax-box { background: var(--ax-bg); color: var(--ax-ink); box-shadow: none; }
        .ax-run-sec .ax-box .ax-dim,
        .ax-run-sec .ax-box .ax-right { color: var(--ax-dim); }
        .ax-run-sec .ax-box-note { padding: 0.75rem 2ch; box-shadow: inset 0 1px 0 var(--ax-line); color: var(--ax-ink-2); }
        .ax-manfoot { display: flex; justify-content: space-between; gap: 2ch; margin-top: calc(var(--ax-l) * 3); font-weight: 700; white-space: nowrap; }
        .ax-manfoot span:nth-child(2) { font-weight: 400; color: var(--ax-solid-dim); }
        @media (max-width: 760px) {
            .ax-manfoot span:nth-child(2) { display: none; }
        }

        /* ---------------------------------------------------------------
           The pager's last line: where you are, and how far down. The
           box that holds it is clipped to the page, so the fixed line
           never rides over the site footer. The figure and the cells
           are one registered integer, driven by the scroll.
           --------------------------------------------------------------- */
        .ax-pin { display: none; }
        @media (min-width: 1280px) {
            .ax-pin { display: block; position: absolute; inset: 0; z-index: 30; clip-path: inset(0); pointer-events: none; }
            .ax-status {
                position: fixed;
                inset: auto 0 0 0;
                display: flex;
                align-items: baseline;
                gap: 3ch;
                padding: 0.1875rem 2ch;
                background: var(--ax-solid);
                color: var(--ax-on-solid);
                box-shadow: 0 -1px 0 var(--ax-bg);
                white-space: nowrap;
                pointer-events: auto;
            }
            .ax-status-name { font-weight: 700; }
            .ax-status ol { display: flex; gap: 0 1ch; min-width: 0; }
            #ax .ax-status a { display: block; padding: 0 1ch; margin: 0; color: var(--ax-solid-dim); }
            #ax .ax-status a:hover { color: var(--ax-on-solid); }
            #ax .ax-status a.is-active { background: var(--ax-bg); color: var(--ax-ink); font-weight: 700; }
            #ax .ax-status a:focus-visible { outline-color: var(--ax-on-solid); outline-offset: -2px; }
            .ax-status-pos { display: none; margin-inline-start: auto; }
        }
        @supports (animation-timeline: scroll()) {
            @media (min-width: 1280px) {
                .ax-status { animation: ax-pct linear both; animation-timeline: scroll(root block); }
                .ax-status-pos { display: flex; align-items: center; gap: 1ch; }
                .ax-bar {
                    position: relative;
                    width: 20ch;
                    height: 0.75rem;
                    background: repeating-linear-gradient(90deg, var(--ax-solid-dim) 0 calc(1ch - 2px), transparent calc(1ch - 2px) 1ch);
                    opacity: 0.9;
                }
                .ax-bar i {
                    position: absolute;
                    inset: 0 auto 0 0;
                    width: calc(var(--ax-pct) * 0.2ch);
                    width: calc(round(down, var(--ax-pct) / 5, 1) * 1ch);
                    background: repeating-linear-gradient(90deg, var(--ax-on-solid) 0 calc(1ch - 2px), var(--ax-solid) calc(1ch - 2px) 1ch);
                }
                .ax-pct { display: inline-block; min-width: 4ch; text-align: end; font-weight: 700; }
                /* Safari interpolates the registered integer without rounding it, and a fraction
                   is no value for a counter: the figure read 0% all the way down. calc() in an
                   integer's place rounds. */
                .ax-pct::after { counter-reset: ax-pct calc(var(--ax-pct) * 1); content: counter(ax-pct) "%"; }
            }
        }
        @keyframes ax-pct { from { --ax-pct: 0; } to { --ax-pct: 100; } }
        /* The name and the cells need about 162ch beside the section names: 1560px in Chrome,
           1620 in Safari, whose fixed-pitch face is wider. Under that, RUN ran into the cells. */
        @media (min-width: 1280px) and (max-width: 1639px) {
            #ax .ax-status-name,
            #ax .ax-bar { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            .ax-caret { animation: none; }
            .ax-btn, .ax-btn i { transition: none; }
        }
    </style>

    @php
        // Every row below is a route in routes/api.php. 27 of them, and the
        // count is quoted in the copy, so keep the two in step.
        $methodClass = [
            'GET' => 'es-cons-m-get',
            'POST' => 'es-cons-m-post',
            'PUT' => 'es-cons-m-put',
            'DELETE' => 'es-cons-m-del',
        ];

        $ledger = [
            ['Auth', 'No key required. Each of these has its own throttle.', [
                ['POST', '/api/register/send-code', 'Mail a six-digit verification code. Hosted mode only, five per address per hour.'],
                ['POST', '/api/register', 'Create the account and return a key. Three per IP per hour.'],
                ['POST', '/api/login', 'Mint a key for an account that has none. A live key returns 409 instead.'],
            ]],
            ['Schedules', 'A schedule is the tenant: a venue, a talent or a curator.', [
                ['GET', '/api/schedules', 'List the schedules you own or administer. Filter by name and by type.'],
                ['GET', '/api/schedules/{subdomain}', 'One schedule, with its sub-schedules inlined.'],
                ['POST', '/api/schedules', 'Create one. name and type are required; the subdomain is generated from the name.'],
                ['PUT', '/api/schedules/{subdomain}', 'Name, contact, description, timezone, language, address. Partial payloads are fine.'],
                ['DELETE', '/api/schedules/{subdomain}', 'Marks it deleted, so it and its pages go dark. Owner level only, not admin.'],
            ]],
            ['Sub-schedules', 'Named strands inside one schedule, each with its own colour.', [
                ['GET', '/api/schedules/{subdomain}/groups', 'id, name, slug and colour for each sub-schedule.'],
                ['POST', '/api/schedules/{subdomain}/groups', 'name is required, colour optional. The slug is generated.'],
                ['PUT', '/api/schedules/{subdomain}/groups/{group_id}', 'Rename it or recolour it.'],
                ['DELETE', '/api/schedules/{subdomain}/groups/{group_id}', 'Events survive; their sub-schedule reference is cleared.'],
            ]],
            ['Events', 'The big one. Tickets, agenda parts, members and recurrence all ride along.', [
                ['GET', '/api/events', 'Paginated, newest first. Twelve filters, including tickets_enabled, is_cancelled and your own external_id.'],
                ['GET', '/api/events/{id}', 'One event with its tickets, members and agenda parts.'],
                ['POST', '/api/events/{subdomain}', 'Create an event on a schedule, or with an external_id and upsert, update the one you made last time. Carries its own 30-per-minute throttle.'],
                ['PUT', '/api/events/{id}', 'Partial update. Recurrence, tickets and agenda parts survive being omitted.'],
                ['DELETE', '/api/events/{id}', 'Delete it, and withdraw it from any synced calendar.'],
                ['POST', '/api/events/{id}/cancel', 'Call it off and keep its sales. Optionally email the people registered, with a note.'],
                ['POST', '/api/events/{id}/restore', 'Undo a cancellation. Nobody is emailed.'],
                ['POST', '/api/events/flyer/{event_id}', 'Multipart upload of a flyer_image for an existing event.'],
            ]],
            ['Categories', 'Read-only lookups so you can send a category_id you know exists.', [
                ['GET', '/api/categories', 'Every system category, with its id and name.'],
                ['GET', '/api/categories/{subdomain}', 'The effective list for one schedule, including its own custom categories.'],
            ]],
            ['Sales', 'A sale is a buyer, a set of ticket quantities and a status.', [
                ['GET', '/api/sales', 'Filter by event, subdomain, status, buyer email or occurrence date.'],
                ['GET', '/api/sales/{id}', 'One sale with its ticket lines.'],
                ['POST', '/api/sales', 'Book a sale by hand. Created unpaid; free tickets are marked paid straight away.'],
                ['PUT', '/api/sales/{id}', 'mark_paid, cancel or refund. A refund sends Stripe or PayPal money back, in full or an amount you name; an idempotency_key makes a retry safe.'],
                ['DELETE', '/api/sales/{id}', 'Soft delete. It stops appearing in listings.'],
            ]],
            ['Feeds', 'Read-only, for pulling audience content onto a site you already run.', [
                ['GET', '/api/feedback', 'Post-event star ratings and comments. Filter by minimum rating and date range.'],
                ['GET', '/api/fan-content', 'Approved comments, photos and videos. Submitter email addresses are never included.'],
            ]],
        ];

        $endpointCount = collect($ledger)->sum(fn ($g) => count($g[2]));

        // Flattened for the hero ticker.
        $ticker = [];
        foreach ($ledger as [, , $rows]) {
            foreach ($rows as [$m, $p, ]) {
                $ticker[] = [$m, $p];
            }
        }

        // Counted from the files themselves: both are edited by hand, and a typed-in count went
        // stale the first time one of them grew.
        $lineCount = fn (string $file) => number_format(count(file(public_path($file)))).' lines';

        $discovery = [
            [
                'llms.txt', '/llms.txt', $lineCount('llms.txt'),
                'The short one. What Event Schedule is and what each plan includes, links to the main product, audience, comparison and documentation pages, and the API essentials: the auth header, which plan the API needs, the rate limits and a getting-started sequence, so an agent can decide in one fetch whether this API is relevant at all.',
            ],
            [
                'llms-full.txt', '/llms-full.txt', $lineCount('llms-full.txt'),
                'The whole reference in one file, so an agent never has to follow a link to finish a task. Every endpoint, every parameter, every error shape.',
            ],
            [
                '/.well-known/agents.json', '/.well-known/agents.json', '4 flows',
                'Named multi-step flows: register_and_setup, create_event_with_tickets, sell_tickets and manage_schedule. Each one lists its calls in order, so a plan is data rather than prompt engineering.',
            ],
            [
                '/api/openapi.json', '/api/openapi.json', 'OpenAPI 3.0',
                'The machine-readable spec. Generate a client, or generate tool definitions, in whatever language your agent is written in.',
            ],
        ];

        // The heading below counts these, so the two have to be changed together.
        $webhookEvents = [
            ['sale.created', 'A sale is created, still unpaid.'],
            ['sale.paid', 'Confirmed paid, whether by Stripe, PayPal, Payfast, Invoice Ninja, by hand or free.'],
            ['sale.refunded', 'A paid sale is refunded in full. A partial refund leaves it paid and fires nothing.'],
            ['sale.cancelled', 'A sale is cancelled.'],
            ['installment.paid', 'A payment of an installment plan is collected.'],
            ['installment.failed', 'A scheduled payment could not be collected. Read outcome for why.'],
            ['event.created', 'A new event exists.'],
            ['event.updated', 'An event changed.'],
            ['event.deleted', 'An event is gone.'],
            ['event.cancelled', 'An event is cancelled.'],
            ['ticket.scanned', 'A ticket QR code is scanned at the door.'],
            ['ticket.booked', 'A pass holder reserves a place on a date.'],
            ['ticket.booking_cancelled', 'A pass holder releases a reserved place.'],
            ['feedback.submitted', 'An attendee left a rating.'],
        ];

        $steps = [
            ['01', 'Get a key',
             'One unauthenticated POST to <span class="es-cons-mono es-cons-key">/api/register</span>, or to <span class="es-cons-mono es-cons-key">/api/register/send-code</span> first in hosted mode. The response body carries the key and its expiry.',
             'X-API-Key: es_live_...'],
            ['02', 'Create a schedule',
             '<span class="es-cons-mono es-cons-key">POST /api/schedules</span> with a name and a type. The subdomain is generated from the name and the public page exists immediately.',
             '{"name": "Synth Lab", "type": "venue"}'],
            ['03', 'Create events',
             '<span class="es-cons-mono es-cons-key">POST /api/events/{subdomain}</span>. Ticket types, agenda parts and recurrence go in the same body, so there is no second round trip. On eventschedule.com this one needs the schedule on Pro.',
             '{"name": "Analog Night", "duration": 3}'],
        ];

        $faqs = [
            [
                'q' => 'Is the API free to use?',
                'a' => 'The REST API is part of the Pro plan at '.plan_price($proMonthly).' a month, with a seven-day trial when you subscribe. Selfhosted installations are Pro by definition, so running your own copy unlocks every endpoint at no cost. Ticket sales carry zero platform fees on every plan and in both modes: you keep everything except your payment processor\'s cut.',
            ],
            [
                'q' => 'Which endpoints need the Pro plan, and what happens without it?',
                'a' => 'On eventschedule.com the list endpoints for schedules, events, sales and feedback return only rows from schedules on Pro, so a free schedule\'s data is missing rather than refused: check the plan before you read an empty list as nothing there. Reading one schedule, event or sale, updating a schedule, and writing to its events, sub-schedules or sales return 403 with "API usage is limited to Pro accounts". The exception worth planning around is POST /api/sales, which has no plan check of its own: a free schedule can record a sale against a zero-price ticket, while any row with a price on it needs the schedule to be Pro and answers 422 otherwise. On a selfhosted install every one of these checks passes.',
            ],
            [
                'q' => 'How does authentication work?',
                'a' => 'One header, X-API-Key. Get a key from POST /api/register or POST /api/login, or generate one in your account settings. Keys are valid for a year. Login only mints a key when the account has none, and returns 409 while one is still live, so store the key rather than calling login on every run. Accounts with two-factor authentication have to generate keys from the web UI. Every endpoint except register, send-code and login requires the header.',
            ],
            [
                'q' => 'What can I actually do with it?',
                'a' => $endpointCount.' endpoints across registration, schedules, sub-schedules, events, categories and sales, plus two read-only feeds for post-event feedback and fan-submitted content. Schedules, sub-schedules, events and sales have full create, read, update and delete; categories are read-only lookups. A single create call can carry ticket types, agenda parts, performing members, a venue and a recurrence pattern, so publishing a run of shows is one request rather than six.',
            ],
            [
                'q' => 'Can the API refund a sale?',
                'a' => 'Yes: PUT /api/sales/{id} with the action refund. On a Stripe or PayPal sale the money goes back through the provider before the status changes. Send an amount to return part of it, and the sale stays paid with its tickets valid; leave it out and the whole remaining balance goes back, the sale becomes refunded, its places return to stock and sale.refunded fires. Send an idempotency_key of your own so a retry returns the first attempt\'s outcome instead of refunding twice. A refund whose outcome could not be confirmed returns 409 and is never retried for you. Invoice Ninja, Payfast, payment-link and cash sales are only recorded as refunded, so return that money yourself, and a payment plan is refunded in full only.',
            ],
            [
                'q' => 'What is llms.txt, and why are there two of them?',
                'a' => 'llms.txt is an emerging convention for telling a language model what a site is and where its documentation lives. Event Schedule publishes both: llms.txt is a short routing summary an agent can read to decide whether this API is relevant, and llms-full.txt is the entire reference in one file, so an agent that has decided to proceed never needs to follow a link.',
            ],
            [
                'q' => 'What are the rate limits?',
                'a' => '300 GET requests a minute and 30 POST, PUT or DELETE requests a minute, counted per IP address. Creating an event carries its own 30-per-minute throttle on top of that, and the auth endpoints have their own tighter limits. Going over returns 429 with an error body.',
            ],
            [
                'q' => 'Are there webhooks, or do I have to poll?',
                'a' => 'There are webhooks, on the Pro plan. Fourteen event types cover sales, event changes, door scans, pass bookings and feedback. Each delivery is signed with HMAC-SHA256 in an X-Webhook-Signature header so you can verify it came from us, payloads match the shapes the API returns, and there is a delivery log in your settings for debugging.',
            ],
            [
                'q' => 'Which IDs does the API use?',
                'a' => 'Encoded strings, never sequential integers. An event, a ticket, a sale and a sub-schedule all identify themselves with a short opaque string, and an event\'s is the same string that appears in its public URL, so you can build a link straight from a response. Category IDs are the exception: they are small integers you read from the categories endpoint.',
            ],
            [
                'q' => 'Can I run this against my own installation?',
                'a' => 'Yes. Event Schedule is open source and the API is the same in both modes: same routes, same spec, same discovery files served from your own domain. On a selfhosted install the Pro gate returns true unconditionally, so nothing is held back.',
            ],
        ];

        $dotSections = [
            ['top', 'One call'],
            ['contract', 'The contract'],
            ['ledger', $endpointCount.' endpoints'],
            ['discovery', 'Discovery files'],
            ['calls', 'Three exchanges'],
            ['push', 'Push, not poll'],
            ['rest', 'Everything else'],
            ['who', 'What gets built'],
            ['start', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Your key'],
        ];
    @endphp

    @php
        // ---------------------------------------------------------------
        // The page's own furniture. Nothing above this line was changed.
        // ---------------------------------------------------------------

        $axVerb = ['GET' => 'ax-get', 'POST' => 'ax-post', 'PUT' => 'ax-put', 'DELETE' => 'ax-del'];

        // The manual's name for each section, beside the label the section nav already had.
        $axNames = [
            'top' => 'NAME', 'contract' => 'CONVENTIONS', 'ledger' => 'ENDPOINTS', 'discovery' => 'FILES',
            'calls' => 'EXAMPLES', 'push' => 'SIGNALS', 'rest' => 'NOTES', 'who' => 'CALLERS',
            'start' => 'QUICK START', 'faq' => 'QUESTIONS', 'claim' => 'RUN',
        ];

        // A column ruler, the kind printed across the top of a listing.
        $axRuler = '';
        for ($axCol = 1; $axCol <= 13; $axCol++) {
            $axRuler .= '----+----'.($axCol % 10);
        }

        // One span per line of code, so a line can carry a number and arrive on its own.
        // The newline between spans stays in the markup: the text of the block is unchanged.
        $axLines = function (string $html, int $from = 0): string {
            $out = [];
            foreach (explode("\n", $html) as $n => $line) {
                $out[] = '<span class="ax-ln" style="--i: '.($from + $n).';">'.$line.'</span>';
            }

            return implode("\n", $out);
        };

        // A path with its parameters tinted and a break allowed before each slash.
        $axPath = fn (string $path) => preg_replace(
            '/\{[a-z_]+\}/',
            '<span class="ax-param">$0</span>',
            ltrim(str_replace('/', '<wbr>/', e($path)), '<wbr>')
        );

        $axArrow = '<i aria-hidden="true">&rarr;</i>';

        // Every code sample on the page, character for character as it was.
        $axPre = [
            'hero_req' => <<<'HTML'
<span class="ax-p">{</span>
  <span class="ax-k">"name"</span><span class="ax-p">:</span> <span class="ax-s">"Analog Night"</span><span class="ax-p">,</span>
  <span class="ax-k">"starts_at"</span><span class="ax-p">:</span> <span class="ax-s">"2026-08-14 20:00:00"</span><span class="ax-p">,</span>
  <span class="ax-k">"duration"</span><span class="ax-p">:</span> <span class="ax-n">3</span><span class="ax-p">,</span>
  <span class="ax-k">"tickets_enabled"</span><span class="ax-p">:</span> <span class="ax-n">true</span><span class="ax-p">,</span>
  <span class="ax-k">"tickets"</span><span class="ax-p">: [{</span>
    <span class="ax-k">"type"</span><span class="ax-p">:</span> <span class="ax-s">"Advance"</span><span class="ax-p">,</span>
    <span class="ax-k">"price"</span><span class="ax-p">:</span> <span class="ax-n">18</span><span class="ax-p">,</span>
    <span class="ax-k">"quantity"</span><span class="ax-p">:</span> <span class="ax-n">120</span>
  <span class="ax-p">}]</span>
<span class="ax-p">}</span>
HTML,
            'hero_res' => <<<'HTML'
<span class="ax-p">{ </span><span class="ax-k">"data"</span><span class="ax-p">: {</span>
  <span class="ax-k">"id"</span><span class="ax-p">:</span> <span class="ax-s">"Kd3Vq7"</span><span class="ax-p">,</span>
  <span class="ax-k">"url"</span><span class="ax-p">:</span> <span class="ax-s">"https://synth-lab.eventschedule.com/analog-night/Kd3Vq7"</span><span class="ax-p">,</span>
  <span class="ax-k">"tickets"</span><span class="ax-p">: [{</span> <span class="ax-k">"id"</span><span class="ax-p">:</span> <span class="ax-s">"9pR3vB"</span><span class="ax-p">,</span> <span class="ax-k">"type"</span><span class="ax-p">:</span> <span class="ax-s">"Advance"</span> <span class="ax-p">}]</span>
<span class="ax-p">}, </span><span class="ax-k">"meta"</span><span class="ax-p">: {</span> <span class="ax-k">"message"</span><span class="ax-p">:</span> <span class="ax-s">"Event created successfully"</span> <span class="ax-p">} }</span>
HTML,
            'env_ok' => <<<'HTML'
<span class="ax-p">{</span>
  <span class="ax-k">"data"</span><span class="ax-p">: [ ... ],</span>
  <span class="ax-k">"meta"</span><span class="ax-p">: {</span> <span class="ax-k">"current_page"</span><span class="ax-p">:</span> <span class="ax-n">1</span><span class="ax-p">,</span> <span class="ax-k">"total"</span><span class="ax-p">:</span> <span class="ax-n">50</span> <span class="ax-p">}</span>
<span class="ax-p">}</span>
HTML,
            'env_err' => <<<'HTML'
<span class="ax-p">{</span>
  <span class="ax-k">"error"</span><span class="ax-p">:</span> <span class="ax-s">"Validation failed"</span><span class="ax-p">,</span>
  <span class="ax-k">"errors"</span><span class="ax-p">: {</span> <span class="ax-k">"starts_at"</span><span class="ax-p">: [</span><span class="ax-s">"must match Y-m-d H:i:s"</span><span class="ax-p">] }</span>
<span class="ax-p">}</span>
HTML,
            'read_req' => <<<'HTML'
<span class="ax-p">?</span><span class="ax-k">subdomain</span><span class="ax-p">=</span><span class="ax-s">synth-lab</span>
<span class="ax-p">&amp;</span><span class="ax-k">starts_after</span><span class="ax-p">=</span><span class="ax-s">2026-08-01</span>
<span class="ax-p">&amp;</span><span class="ax-k">tickets_enabled</span><span class="ax-p">=</span><span class="ax-n">1</span>
<span class="ax-p">&amp;</span><span class="ax-k">per_page</span><span class="ax-p">=</span><span class="ax-n">50</span>
HTML,
            'read_res' => <<<'HTML'
<span class="ax-k">"meta"</span><span class="ax-p">: {</span>
  <span class="ax-k">"current_page"</span><span class="ax-p">:</span> <span class="ax-n">1</span><span class="ax-p">,</span>
  <span class="ax-k">"last_page"</span><span class="ax-p">:</span> <span class="ax-n">2</span><span class="ax-p">,</span>
  <span class="ax-k">"total"</span><span class="ax-p">:</span> <span class="ax-n">63</span>
<span class="ax-p">}</span>
HTML,
            'recur_req' => <<<'HTML'
<span class="ax-k">"schedule_type"</span><span class="ax-p">:</span> <span class="ax-s">"recurring"</span><span class="ax-p">,</span>
<span class="ax-k">"recurring_frequency"</span><span class="ax-p">:</span> <span class="ax-s">"weekly"</span><span class="ax-p">,</span>
<span class="ax-k">"days_of_week"</span><span class="ax-p">:</span> <span class="ax-s">"0111110"</span><span class="ax-p">,</span>
<span class="ax-k">"recurring_end_type"</span><span class="ax-p">:</span> <span class="ax-s">"after_events"</span><span class="ax-p">,</span>
<span class="ax-k">"recurring_end_value"</span><span class="ax-p">:</span> <span class="ax-s">"14"</span>
HTML,
            'recur_res' => <<<'HTML'
<span class="ax-k">"schedule_type"</span><span class="ax-p">:</span> <span class="ax-s">"recurring"</span><span class="ax-p">,</span>
<span class="ax-k">"days_of_week"</span><span class="ax-p">:</span> <span class="ax-s">"0111110"</span>
HTML,
            'sale_req' => <<<'HTML'
<span class="ax-p">{</span> <span class="ax-k">"action"</span><span class="ax-p">:</span> <span class="ax-s">"mark_paid"</span> <span class="ax-p">}</span>
HTML,
            'sale_res' => <<<'HTML'
<span class="ax-k">"status"</span><span class="ax-p">:</span> <span class="ax-s">"paid"</span><span class="ax-p">,</span>
<span class="ax-k">"payment_amount"</span><span class="ax-p">:</span> <span class="ax-n">36</span><span class="ax-p">,</span>
<span class="ax-k">"total_quantity"</span><span class="ax-p">:</span> <span class="ax-n">2</span><span class="ax-p">,</span>
<span class="ax-k">"tickets"</span><span class="ax-p">: [{</span> <span class="ax-k">"type"</span><span class="ax-p">:</span> <span class="ax-s">"Advance"</span> <span class="ax-p">}]</span>
HTML,
            'hook' => <<<'HTML'
<span class="ax-k">X-Webhook-Event</span><span class="ax-p">:</span> <span class="ax-s">sale.paid</span>
<span class="ax-k">X-Webhook-Signature</span><span class="ax-p">:</span> <span class="ax-s">sha256=&lt;hex&gt;</span>
<span class="ax-k">X-Webhook-Timestamp</span><span class="ax-p">:</span> <span class="ax-s">2026-08-14T20:11:04+00:00</span>
<span class="ax-k">User-Agent</span><span class="ax-p">:</span> <span class="ax-s">EventSchedule-Webhook/1.0</span>

<span class="ax-p">{</span> <span class="ax-k">"event"</span><span class="ax-p">:</span> <span class="ax-s">"sale.paid"</span><span class="ax-p">,</span> <span class="ax-k">"data"</span><span class="ax-p">: {</span> ... <span class="ax-p">} }</span>
HTML,
            'flyer' => <<<'HTML'
curl -X POST <span class="ax-s">.../api/events/flyer/Kd3Vq7</span> \
  -H <span class="ax-s">"X-API-Key: $KEY"</span> \
  -F <span class="ax-s">"flyer_image=@night.jpg"</span>
HTML,
            'reg_req' => <<<'HTML'
<span class="ax-p">{</span>
  <span class="ax-k">"name"</span><span class="ax-p">:</span> <span class="ax-s">"Your Agent"</span><span class="ax-p">,</span>
  <span class="ax-k">"email"</span><span class="ax-p">:</span> <span class="ax-s">"you@example.com"</span><span class="ax-p">,</span>
  <span class="ax-k">"password"</span><span class="ax-p">:</span> <span class="ax-s">"..."</span>
<span class="ax-p">}</span>
HTML,
            'reg_res' => <<<'HTML'
<span class="ax-p">{ </span><span class="ax-k">"data"</span><span class="ax-p">: {</span>
  <span class="ax-k">"api_key"</span><span class="ax-p">:</span> <span class="ax-s">"your_new_api_key"</span><span class="ax-p">,</span>
  <span class="ax-k">"api_key_expires_at"</span><span class="ax-p">:</span> <span class="ax-s">"2027-07-30T00:00:00Z"</span>
<span class="ax-p">} }</span>
HTML,
        ];
    @endphp

    <div id="ax">

        {{-- The pager's last line. Wide screens only; the box is clipped to this page. --}}
        <div class="ax-pin">
            <nav class="ax-status es-dotnav" aria-label="Page sections">
                <span class="ax-status-name" aria-hidden="true">eventschedule(1)</span>
                <ol>
                    @foreach ($dotSections as [$sectionId, $sectionLabel])
                        <li><a href="#{{ $sectionId }}" class="es-dot" title="{{ $sectionLabel }}">{{ $axNames[$sectionId] }}</a></li>
                    @endforeach
                </ol>
                <span class="ax-status-pos" aria-hidden="true"><span class="ax-bar"><i></i></span><span class="ax-pct"></span></span>
            </nav>
        </div>

        <!-- ============================================================ -->
        <!-- 1. NAME, DESCRIPTION, SYNOPSIS                               -->
        <!-- ============================================================ -->
        <section id="top" class="ax-hero" style="scroll-margin-top: 4rem;">
            <div class="ax-wrap">
                <div class="ax-guides" aria-hidden="true"></div>
                <p class="ax-manhead" aria-hidden="true"><span>EVENTSCHEDULE(1)</span><span>Event Schedule API Manual</span><span>EVENTSCHEDULE(1)</span></p>
                <p class="ax-ruler" aria-hidden="true">{{ $axRuler }}</p>

                <div class="ax-hero-grid">
                    <div>
                        <span class="ax-name" aria-hidden="true">NAME</span>
                        <h1 class="ax-h1" dir="ltr">
                            <x-marketing.hero-eyebrow class="ax-eyebrow es-fade-up es-d-1">An API for AI agents and developers</x-marketing.hero-eyebrow>
                            <span class="ax-type ax-type-1">One POST, and the</span>
                            <span class="ax-type ax-type-2">show is <span class="ax-em">on sale.</span><span class="ax-caret" aria-hidden="true"></span></span>
                        </h1>
                        <div class="ax-cta es-fade-up es-d-3">
                            <a href="{{ route('marketing.docs.developer.api') }}" class="ax-btn">
                                Read the API reference
                                {!! $axArrow !!}
                            </a>
                            <a href="#ledger" class="ax-btn ax-btn-alt">
                                See all {{ $endpointCount }} endpoints
                                <i aria-hidden="true">&darr;</i>
                            </a>
                        </div>
                    </div>

                    <div class="ax-hero-desc">
                        <span class="ax-name" aria-hidden="true">DESCRIPTION</span>
                        <p class="ax-prose es-fade-up es-d-2">
                            {{ $endpointCount }} REST endpoints over the whole product: three to get a key, then twenty-six behind it covering schedules, sub-schedules, events, recurrences, ticket types, sales and refunds, feedback and fan content. JSON in, JSON out, one header.
                        </p>
                        <p class="ax-prose es-fade-up es-d-2">
                            An OpenAPI 3.0 spec, <span class="ax-k">llms.txt</span> and <span class="ax-k">agents.json</span> ship with it, so an agent can discover this API and drive it without a human reading the docs first.
                        </p>
                    </div>
                </div>

                <!-- The signature exchange: one call out, one answer back. -->
                <div class="ax-synopsis">
                    <span class="ax-name" aria-hidden="true">SYNOPSIS</span>
                    <div class="ax-exchange">
                        <div class="ax-box es-fade-up es-d-3">
                            <div class="ax-box-head" dir="ltr">
                                <span class="ax-arrow" aria-hidden="true">&#9656;</span>
                                <span class="ax-verb ax-post">POST</span>
                                <span class="ax-path">/api/events/synth-lab</span>
                                <span class="ax-right">X-API-Key</span>
                            </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['hero_req']) !!}</pre>
                        </div>
                        <div class="ax-exchange-back">
                            <div class="ax-box es-fade-up es-d-4">
                                <div class="ax-box-head" dir="ltr">
                                    <span class="ax-arrow" aria-hidden="true">&#9666;</span>
                                    <span class="ax-sc">201 CREATED</span>
                                    <span class="ax-right">application/json</span>
                                </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['hero_res'], 12) !!}</pre>
                            </div>
                            <p class="ax-prose es-fade-up es-d-5">
                                That one call also writes the event to Google, Outlook or CalDAV if the schedule is connected to one, and fires an <span class="ax-s">event.created</span> webhook.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 2. CONVENTIONS: the contract                                 -->
        <!-- ============================================================ -->
        <section id="contract" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">CONVENTIONS</span>
                    <span class="ax-ref"><a href="#contract">#contract</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        Four facts, and you can <span class="ax-em">start writing.</span>
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        No SDK to install, no OAuth dance, no sandbox to request. The whole surface behaves the same way, which is the only property an agent really needs.
                    </p>

                    <dl class="ax-facts ax-gap" data-reveal-group="70">
                        <div class="ax-fact" data-reveal>
                            <dt>X-API-Key</dt>
                            <dd>
                                <strong>One header</strong>
                                <p>Register, generate a key in your settings, or log in when you have none. Keys last a year. Nothing else is required.</p>
                            </dd>
                        </div>
                        <div class="ax-fact" data-reveal>
                            <dt>300 / 30</dt>
                            <dd>
                                <strong>Requests a minute</strong>
                                <p>300 reads and 30 writes a minute, per IP. Over the line you get a 429, not a silent drop.</p>
                            </dd>
                        </div>
                        <div class="ax-fact" data-reveal>
                            <dt>per_page &le; 500</dt>
                            <dd>
                                <strong>The big lists paginate</strong>
                                <p>100 by default, 500 at most, with a <span class="ax-k">meta</span> block carrying the page, the total and the bounds. Categories and sub-schedules come back whole.</p>
                            </dd>
                        </div>
                        <div class="ax-fact" data-reveal>
                            <dt>Kd3Vq7</dt>
                            <dd>
                                <strong>IDs are opaque strings</strong>
                                <p>Never a sequential integer, and an event's is the same string that appears in its public URL, so you can build a link from a response.</p>
                            </dd>
                        </div>
                    </dl>

                    <!-- The envelope: success and failure, side by side. -->
                    <div class="ax-two ax-gap">
                        <div class="ax-box" data-reveal="stream">
                            <div class="ax-box-head" dir="ltr">
                                <span class="ax-arrow" aria-hidden="true">&#9666;</span>
                                <span class="ax-sc">2xx</span>
                                <span class="ax-right">the success envelope</span>
                            </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['env_ok']) !!}</pre>
                        </div>
                        <div class="ax-box" data-reveal="stream">
                            <div class="ax-box-head" dir="ltr">
                                <span class="ax-arrow" aria-hidden="true">&#9666;</span>
                                <span class="ax-sc ax-sc-err">422</span>
                                <span class="ax-right">field-level errors, always in the same place</span>
                            </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['env_err'], 3) !!}</pre>
                        </div>
                    </div>
                    <p class="ax-prose ax-gap-1" data-reveal>
                        401 for a bad key, 403 when the plan or the permission is missing, 404, 409 when a key is already live or a refund's outcome is not yet confirmed, 422 with the offending fields named, 429 when throttled. A model can branch on that without guessing.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 3. ENDPOINTS: every endpoint, in one table                   -->
        <!-- ============================================================ -->
        <section id="ledger" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">ENDPOINTS</span>
                    <span class="ax-ref"><a href="#ledger">#ledger</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        The entire surface, <span class="ax-em">on one page.</span>
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        {{ $endpointCount }} endpoints. Not a summary of them, all of them. Everything past <span class="ax-k">/api/login</span> needs the key header, and API access is part of the Pro plan.
                    </p>

                    <div class="ax-ledger ax-gap">
                        <div class="ax-ledger-in">
                        <fieldset class="ax-filter">
                            <legend>Colour is the verb</legend>
                            <label><input type="radio" name="ax-verb" id="ax-v-all" checked> all</label>
                            <label class="ax-get"><input type="radio" name="ax-verb" id="ax-v-get"> <b>GET</b> read</label>
                            <label class="ax-post"><input type="radio" name="ax-verb" id="ax-v-post"> <b>POST</b> create</label>
                            <label class="ax-put"><input type="radio" name="ax-verb" id="ax-v-put"> <b>PUT</b> update</label>
                            <label class="ax-del"><input type="radio" name="ax-verb" id="ax-v-del"> <b>DELETE</b> remove</label>
                        </fieldset>

                        <table class="ax-table" role="table" dir="ltr">
                            <caption class="sr-only">Every Event Schedule API endpoint, grouped by resource, with its HTTP method, path and behaviour</caption>
                            <thead role="rowgroup">
                                <tr role="row">
                                    <th scope="col" role="columnheader">Method</th>
                                    <th scope="col" role="columnheader">Path</th>
                                    <th scope="col" role="columnheader">Behaviour</th>
                                </tr>
                            </thead>
                            @foreach ($ledger as [$groupName, $groupNote, $rows])
                                <tbody role="rowgroup">
                                    <tr class="ax-grp" role="row">
                                        <th scope="colgroup" colspan="3" role="columnheader"><b>{{ $groupName }}</b> <span>{{ $groupNote }}</span></th>
                                    </tr>
                                    @foreach ($rows as [$rMethod, $rPath, $rNote])
                                        <tr class="ax-row" role="row" data-verb="{{ $rMethod }}">
                                            <td role="cell"><span class="ax-verb {{ $axVerb[$rMethod] }}">{{ $rMethod }}</span></td>
                                            <th scope="row" role="rowheader">{!! $axPath($rPath) !!}</th>
                                            <td role="cell">{{ $rNote }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            @endforeach
                        </table>
                        <p class="ax-ledger-foot" aria-hidden="true">
                            <span><span class="ax-count"></span> of {{ $endpointCount }} shown</span>
                            <span>(END)</span>
                        </p>
                        </div>
                    </div>
                    <p class="ax-prose ax-gap-1" data-reveal>
                        Full documentation for each one, with a cURL example and a response body, is in the <a href="{{ route('marketing.docs.developer.api') }}" class="ax-link">API reference</a>.
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 4. FILES: discovery                                          -->
        <!-- ============================================================ -->
        <section id="discovery" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">FILES</span>
                    <span class="ax-ref"><a href="#discovery">#discovery</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        Four files an agent can <span class="ax-em">read first.</span>
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        Documentation written for a person is a bad input for a model. These four are written for the model, live at fixed paths, and are served from any installation, hosted or your own.
                    </p>

                    <div class="ax-tree ax-gap">
                        <p class="ax-tree-root" aria-hidden="true">/</p>
                        <ul data-reveal-group="80">
                            @foreach ($discovery as [$dName, $dHref, $dMeta, $dDesc])
                                <li data-reveal>
                                    <div class="ax-file" dir="ltr">
                                        <a href="{{ $dHref }}">{{ $dName }}</a>
                                        <span class="ax-fill" aria-hidden="true"></span>
                                        <span class="ax-meta">{{ $dMeta }}</span>
                                    </div>
                                    <p>{{ $dDesc }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 5. EXAMPLES: three exchanges, as one session                 -->
        <!-- ============================================================ -->
        <section id="calls" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">EXAMPLES</span>
                    <span class="ax-ref"><a href="#calls">#calls</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        Three calls you will <span class="ax-em">actually write.</span>
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        Filtering a calendar, standing up a weekly residency, and settling a sale. Everything else is a variation on these.
                    </p>

                    <div class="ax-gap">
                        <p class="ax-session-head" aria-hidden="true"><span>session</span><span>synth-lab</span><span>3 tool calls</span></p>
                        <div class="ax-session">
                            <p class="ax-plan" aria-hidden="true"><b>plan</b> 1 filter the calendar &nbsp; 2 stand up the residency &nbsp; 3 settle the sale</p>

                            <!-- a. Filtered read -->
                            <div class="ax-call">
                                <div class="ax-call-io" data-reveal="stream">
                                    <div class="ax-box">
                                        <div class="ax-box-head" dir="ltr">
                                            <span class="ax-arrow" aria-hidden="true">&#9656; http.request</span>
                                            <span class="ax-verb ax-get">GET</span>
                                            <span class="ax-path">/api/events</span>
                                        </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['read_req']) !!}</pre>
                                    </div>
                                    <div class="ax-box">
                                        <div class="ax-box-head ax-late" dir="ltr" style="--i: 5;">
                                            <span class="ax-arrow" aria-hidden="true">&#9666;</span>
                                            <span class="ax-sc">200 OK</span>
                                        </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['read_res'], 6) !!}</pre>
                                    </div>
                                </div>
                                <p class="ax-call-note" data-reveal>
                                    Twelve filters on the events list, including whether tickets or RSVP are switched on, whether an event is cancelled, a venue, a sub-schedule, a date window and the id your own system gave the event. You narrow server-side rather than pulling a year and filtering in the agent.
                                </p>
                            </div>

                            <!-- b. Recurrence as data -->
                            <div class="ax-call">
                                <div class="ax-call-io" data-reveal="stream">
                                    <div class="ax-box">
                                        <div class="ax-box-head" dir="ltr">
                                            <span class="ax-arrow" aria-hidden="true">&#9656; http.request</span>
                                            <span class="ax-verb ax-post">POST</span>
                                            <span class="ax-path">/api/events/synth-lab</span>
                                        </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['recur_req']) !!}</pre>
                                    </div>
                                    <div class="ax-box">
                                        <div class="ax-box-head ax-late" dir="ltr" style="--i: 6;">
                                            <span class="ax-arrow" aria-hidden="true">&#9666;</span>
                                            <span class="ax-sc">201 CREATED</span>
                                            <span class="ax-right">one event, fourteen dates</span>
                                        </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['recur_res'], 7) !!}</pre>
                                    </div>
                                </div>
                                <p class="ax-call-note" data-reveal>
                                    A seven-character mask, Sunday first, so Monday to Friday is <span class="ax-k">"0111110"</span>. Frequency is one of daily, weekly, every_n_weeks, monthly_date, monthly_weekday or yearly, and a run can end never, on a date, or after a set number of occurrences.
                                </p>
                            </div>

                            <!-- c. Settle a sale -->
                            <div class="ax-call">
                                <div class="ax-call-io" data-reveal="stream">
                                    <div class="ax-box">
                                        <div class="ax-box-head" dir="ltr">
                                            <span class="ax-arrow" aria-hidden="true">&#9656; http.request</span>
                                            <span class="ax-verb ax-put">PUT</span>
                                            <span class="ax-path">/api/sales/7bQx2m</span>
                                        </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['sale_req']) !!}</pre>
                                    </div>
                                    <div class="ax-box">
                                        <div class="ax-box-head ax-late" dir="ltr" style="--i: 2;">
                                            <span class="ax-arrow" aria-hidden="true">&#9666;</span>
                                            <span class="ax-sc">200 OK</span>
                                        </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['sale_res'], 3) !!}</pre>
                                    </div>
                                </div>
                                <p class="ax-call-note" data-reveal>
                                    Three actions, and which ones are legal depends on where the sale is: <span class="ax-k">mark_paid</span> from unpaid, <span class="ax-k">refund</span> from paid, <span class="ax-k">cancel</span> from either. On a Stripe or PayPal sale, <span class="ax-k">refund</span> sends the money back before the status moves, and an <span class="ax-k">amount</span> makes it partial, which leaves the sale paid. You can also create a sale outright for a buyer who paid you off-platform.
                                </p>
                            </div>

                            <p class="ax-plan ax-gap" aria-hidden="true"><b>done</b> 3 of 3<span class="ax-caret"></span></p>
                        </div>
                    </div>

                    <div class="ax-aside ax-gap" data-reveal>
                        <span class="ax-tag ax-tag-pro">how matching works</span>
                        <p class="ax-prose">
                            You can name a venue or a performer instead of looking up an ID: send <span class="ax-k">venue_name</span> with <span class="ax-k">venue_address1</span>, or <span class="ax-k">members</span> as a list of names and emails, and the API resolves them to schedules on your account. To be exact about what that is: it matches an existing schedule you own or follow, and returns a 422 naming the one it could not find. It does not invent a venue for you. Categories work the same way: send <span class="ax-k">category</span> as a name and it is matched against that schedule's category list.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 6. SIGNALS: push, not poll                                   -->
        <!-- ============================================================ -->
        <section id="push" class="ax-sec ax-band">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">SIGNALS</span>
                    <span class="ax-ref"><a href="#push">#push</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        Or stop asking, and <span class="ax-em">get told.</span>
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        Polling a sales endpoint every minute is a waste of both our time. Register an endpoint and the traffic reverses: we POST to you, signed, the moment something happens.
                    </p>

                    <div class="ax-sig-grid ax-gap">
                        <!-- the delivery -->
                        <div class="ax-box" data-reveal="stream">
                            <div class="ax-box-head" dir="ltr">
                                <span class="ax-arrow" aria-hidden="true">&#9666; incoming</span>
                                <span class="ax-verb ax-post">POST</span>
                                <span class="ax-path">https://your-app.example/hooks</span>
                            </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['hook']) !!}</pre>
                        </div>
                        <div>
                            <p class="ax-prose" data-reveal>
                                The signature is an HMAC-SHA256 of the raw body, keyed on a secret shown once when you add the hook. Verify it before you trust the payload. <strong>Key on <span class="ax-k">data.id</span> plus the event type</strong>, because a delivery can repeat and one sale fires several types. There is a delivery log in your settings when something goes wrong.
                            </p>
                            <p class="ax-gap-1" data-reveal>
                                <a href="{{ route('marketing.docs.developer.webhooks') }}" class="ax-link">Webhook reference, with verification snippets</a>
                            </p>
                        </div>
                    </div>

                    <!-- the fourteen types -->
                    <div class="ax-box ax-signal-box ax-gap" data-reveal>
                        <div class="ax-box-head">
                            <strong>Fourteen event types</strong>
                            <span class="ax-tag ax-tag-pro ax-right">pro</span>
                        </div>
                        <ul class="ax-signals" dir="ltr">
                            @foreach ($webhookEvents as [$wName, $wDesc])
                                <li><span><b>{{ $wName }}</b> <i>{{ $wDesc }}</i></span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 7. NOTES: everything else                                    -->
        <!-- ============================================================ -->
        <section id="rest" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">NOTES</span>
                    <span class="ax-ref"><a href="#rest">#rest</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        The parts that make it <span class="ax-em">safe to automate.</span>
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        Details that only matter once your code is running unattended, which is exactly when they matter most.
                    </p>

                    <div class="ax-notes ax-gap" data-reveal-group="70">

                        <!-- 1 -->
                        <article class="ax-note" data-reveal>
                            <div class="ax-note-tags">
                                <span class="ax-tag ax-tag-free">free and pro</span>
                                <span class="ax-tag">open source</span>
                            </div>
                            <h3>Your own install, same API</h3>
                            <p>
                                Event Schedule is open source, and the API does not change when you host it yourself: same routes, same OpenAPI spec, same discovery files, served from your own domain. On a selfhosted install the Pro gate returns true unconditionally, so no endpoint is held back and no key talks to anyone else's server.
                            </p>
                            <div class="ax-chips">
                                <span class="ax-chip">docker or bare metal</span>
                                <span class="ax-chip">your database</span>
                                <span class="ax-chip">no outbound calls required</span>
                            </div>
                            <p>
                                <a href="{{ marketing_url('/selfhost') }}" class="ax-link">How selfhosting works</a>
                            </p>
                        </article>

                        <!-- 2 -->
                        <article class="ax-note" data-reveal>
                            <div class="ax-note-tags">
                                <span class="ax-tag ax-tag-pro">pro</span>
                            </div>
                            <h3>Flyers, as a second call</h3>
                            <p>
                                Artwork is multipart, so it gets its own request. Create the event, then POST a <span class="ax-k">flyer_image</span> to the flyer endpoint with the returned ID.
                            </p>
                            <div class="ax-box">
<pre class="ax-code" dir="ltr" data-clip-ok>{!! $axPre['flyer'] !!}</pre>
                            </div>
                        </article>

                        <!-- 3 -->
                        <article class="ax-note" data-reveal>
                            <div class="ax-note-tags">
                                <span class="ax-tag ax-tag-free">free and pro</span>
                            </div>
                            <h3>Languages, made explicit</h3>
                            <p>
                                Set <span class="ax-k">language_code</span> on a schedule and its pages are served in that language; twelve are supported. A schedule can also nominate one translation target, and its own copy is machine-translated into it on a scheduled pass.
                            </p>
                            <div class="ax-chips" dir="ltr">
                                @foreach (['ar', 'de', 'en', 'es', 'et', 'fr', 'he', 'it', 'nl', 'pt', 'ro', 'ru'] as $lc)
                                    <span class="ax-chip">{{ $lc }}</span>
                                @endforeach
                            </div>
                        </article>

                        <!-- 4 -->
                        <article class="ax-note" data-reveal>
                            <div class="ax-note-tags">
                                <span class="ax-tag ax-tag-pro">pro</span>
                            </div>
                            <h3>Money, without a middleman</h3>
                            <p>
                                Ticket types created through the API sell through your own Stripe or PayPal account, or through Invoice Ninja, Payfast for rand prices, a payment URL, or by hand. Event Schedule takes zero platform fees on ticket sales: the only deduction is your processor's. Sales come back through the sales endpoints and through <span class="ax-s">sale.paid</span> webhooks, with the ticket lines attached, and a Stripe or PayPal refund goes back through the provider.
                            </p>
                            <div class="ax-chips" dir="ltr">
                                <span class="ax-chip">stripe</span>
                                <span class="ax-chip">paypal</span>
                                <span class="ax-chip">payfast</span>
                                <span class="ax-chip">invoiceninja</span>
                                <span class="ax-chip">payment_url</span>
                                <span class="ax-chip">cash</span>
                            </div>
                        </article>

                        <!-- 5 -->
                        <article class="ax-note" data-reveal>
                            <div class="ax-note-tags">
                                <span class="ax-tag ax-tag-pro">pro</span>
                            </div>
                            <h3>Read-only feeds</h3>
                            <p>
                                Two endpoints exist purely so you can pull audience content somewhere else: post-event ratings and comments, and approved fan photos, videos and comments. Fan submissions carry a display name only; the ratings feed names the attendee, so treat it as owner-facing.
                            </p>
                            <p>
                                Each kind of fan submission has its own ID sequence, so key on <span class="ax-k">type</span> and <span class="ax-k">id</span> together when you store a row.
                            </p>
                        </article>

                        <!-- 6 -->
                        <article class="ax-note" data-reveal>
                            <div class="ax-note-tags">
                                <span class="ax-tag ax-tag-pro">pro</span>
                            </div>
                            <h3>Partial writes that keep their nerve</h3>
                            <p>
                                <span class="ax-k">PUT</span> takes the same body as create and applies only what you send. Recurrence configuration, ticket types and agenda parts are preserved when they are absent, so an agent that only knows the new start time cannot quietly erase a run's ticket tiers. Every write is scoped to the schedules the key's owner owns or administers, and anything outside that returns 403 rather than silently doing nothing.
                            </p>
                            <div class="ax-exit" dir="ltr">
                                <span aria-hidden="true">EXIT STATUS</span>
                                <dl>
                                <div>
                                    <dt>401</dt>
                                    <dd>Key missing, wrong or expired.</dd>
                                </div>
                                <div>
                                    <dt>403</dt>
                                    <dd>Not your schedule, or the plan does not cover it.</dd>
                                </div>
                                <div>
                                    <dt>429</dt>
                                    <dd>Throttled. Back off and retry.</dd>
                                </div>
                                </dl>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 8. CALLERS: what people build with it                        -->
        <!-- ============================================================ -->
        <section id="who" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">CALLERS</span>
                    <span class="ax-ref"><a href="#who">#who</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        What people point at <span class="ax-em">this API.</span>
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        An HTTP API has no opinion about what is calling it, which is the point.
                    </p>

                    @php
                        $axCallers = [
                            ['AI Assistants', 'Turn a conversation into a published event. Register, create the schedule and create the event in three calls, then hand back the URL from the response.', 'for-ai-assistants'],
                            ['Developer Tools & Scripts', 'A cron job, a CLI, a one-off migration. Generate a client from the OpenAPI spec and the whole surface is typed for you.', 'for-developer-tools'],
                            ['Community Bots', 'A Discord, Slack or Telegram bot that creates the event when someone announces it in the channel, and posts the ticket link back.', 'for-community-bots'],
                            ['Booking Platforms', 'Keep your own front end and let Event Schedule hold the events, the ticket types and the sales. Webhooks push each paid sale straight back to you.', 'for-booking-platforms'],
                            ['Calendar Aggregators', 'Pull a date window with the events filters, or take the iCal feed and skip the API entirely. Both come off the same schedule.', 'for-calendar-aggregators'],
                            ['Custom Integrations', 'Anything that speaks HTTP and JSON. If you would rather not write the client, the OpenAPI spec will write it for you.', 'for-custom-integrations'],
                        ];
                    @endphp
                    <div class="ax-callers ax-gap" data-reveal-group="60">
                        @foreach ($axCallers as [$axWho, $axWhat, $axSlug])
                            @php $axPost = get_sub_audience_blog($axSlug); @endphp
                            <article class="ax-caller" data-reveal>
                                <div>
                                    <h3>{{ $axWho }}</h3>
                                    <p>{{ $axWhat }}</p>
                                    @if ($axPost)
                                        <a href="{{ blog_url('/' . $axPost->slug) }}" class="ax-link" aria-label="Learn more about Event Schedule for {{ $axWho }}">Learn more {!! $axArrow !!}</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 9. QUICK START: three steps                                  -->
        <!-- ============================================================ -->
        <section id="start" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">QUICK START</span>
                    <span class="ax-ref"><a href="#start">#start</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        Three requests from nothing to <span class="ax-em">a live page.</span>
                    </h2>

                    <ol class="ax-steps ax-gap" data-reveal-group="90">
                        @foreach ($steps as [$sNum, $sTitle, $sDesc, $sCode])
                            <li class="ax-step" data-reveal>
                                <span class="ax-step-no" aria-hidden="true">{{ $sNum }}</span>
                                <div>
                                    <h3>{{ $sTitle }}</h3>
                                    <p>{!! str_replace('es-cons-mono es-cons-key', 'ax-k', $sDesc) !!}</p>
                                </div>
                                <div class="ax-box">
<pre class="ax-code" dir="ltr" data-clip-ok>{{ $sCode }}</pre>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 10. SEE ALSO: related features and pages                     -->
        <!-- ============================================================ -->
        <section class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">SEE ALSO</span>
                    <span class="ax-ref" aria-hidden="true"></span>
                </div>
                <div class="ax-body ax-also">
                    <div data-reveal>
                        <h2>Related features</h2>
                        @php
                            $axFeatures = [
                                ['Ticketing', 'Ticket types, QR check-in and zero platform fees', marketing_url('/features/ticketing')],
                                ['Calendar Sync', 'Two-way Google, Outlook and CalDAV sync on every plan', marketing_url('/features/calendar-sync')],
                                ['Embed Calendar', 'Put the schedule on the site you already run', marketing_url('/features/embed-calendar')],
                                ['Analytics', 'Page views, devices and traffic sources, free on every plan', marketing_url('/features/analytics')],
                            ];
                        @endphp
                        <ul>
                            @foreach ($axFeatures as [$axFeature, $axFeatureDesc, $axFeatureUrl])
                                <li>
                                    <a href="{{ $axFeatureUrl }}">
                                        <b>{{ $axFeature }}<small aria-hidden="true">(7)</small></b>
                                        <span>{{ $axFeatureDesc }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="ax-more">
                            <a href="{{ marketing_url('/features') }}" class="ax-link">See all features {!! $axArrow !!}</a>
                        </p>
                    </div>

                    <div class="ax-pages" data-reveal style="--reveal-delay: 0.08s;">
                        <h2>Related pages</h2>
                        <ul>
                            @foreach ([['/for-webinars', 'Webinars'], ['/for-virtual-conferences', 'Virtual Conferences'], ['/for-curators', 'Curators'], ['/for-online-classes', 'Online Classes']] as [$relHref, $relName])
                                <li>
                                    <a href="{{ marketing_url($relHref) }}">
                                        <b>For {{ $relName }}</b>
                                        <span>Read more {!! $axArrow !!}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="ax-more">
                            <a href="{{ marketing_url('/use-cases') }}" class="ax-link">See all use cases {!! $axArrow !!}</a>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <div class="ax-plans">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">PLANS</span>
                    <span class="ax-ref" aria-hidden="true"></span>
                </div>
            </div>
            @include('marketing.partials.pricing-nudge')
        </div>

        <!-- ============================================================ -->
        <!-- 11. QUESTIONS                                                -->
        <!-- ============================================================ -->
        <x-seo.faq-schema :items="$faqs" />

        <section id="faq" class="ax-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">QUESTIONS</span>
                    <span class="ax-ref"><a href="#faq">#faq</a></span>
                </div>
                <div class="ax-body">
                    <h2 class="ax-h2" data-reveal>
                        Frequently asked questions
                    </h2>
                    <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                        What developers ask before they write the first request.
                    </p>

                    <div class="ax-qa ax-gap" data-reveal>
                        @foreach ($faqs as $faqIndex => $faq)
                            <details name="faq">
                                <summary>
                                    <h3>{{ $faq['q'] }}</h3>
                                </summary>
                                <p>{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- 12. RUN: the exchange that hands you a key                   -->
        <!-- ============================================================ -->
        <section id="claim" class="ax-sec ax-run-sec">
            <div class="ax-wrap">
                <div class="ax-sec-head">
                    <span class="ax-name" aria-hidden="true">RUN</span>
                    <span class="ax-ref"><a href="#claim">#claim</a></span>
                </div>
                <div class="ax-body ax-run-grid">
                    <div>
                        <h2 class="ax-h2" data-reveal>
                            The last call is the <span class="ax-em">first one.</span>
                        </h2>
                        <p class="ax-prose" data-reveal style="--reveal-delay: 0.06s;">
                            Pick a name and start, or register straight from your code. Publishing a schedule and its dates is free forever, and so is free registration; the API and any ticket with a price on it are {{ plan_price($proMonthly) }} a month, and Event Schedule takes nothing from the door.
                        </p>

                        <div class="ax-run-line">
                            <div class="ax-run" data-reveal style="--reveal-delay: 0.12s;">
                                <label for="es-claim-input" class="sr-only">Your schedule name</label>
                                <div dir="ltr" class="es-claim ax-claim">
                                    <span class="ax-prompt" aria-hidden="true"><b>$</b> <span>open </span></span>
                                    <input id="es-claim-input" type="text" placeholder="your-agent" autocomplete="off" spellcheck="false" maxlength="30">
                                    <span class="ax-host">.eventschedule.com</span>
                                </div>
                                <a href="{{ app_url('/sign_up') }}" class="ax-btn">
                                    Get started free
                                    <kbd aria-hidden="true">&crarr;</kbd>
                                </a>
                            </div>

                            <p class="ax-prose ax-gap-1" data-reveal style="--reveal-delay: 0.18s;">
                                No card to start. Or go straight to the <a href="{{ route('marketing.docs.developer.api') }}" class="ax-link">API reference</a> and the <a href="/api/openapi.json" class="ax-link">OpenAPI spec</a>.
                            </p>
                        </div>
                    </div>

                    <div class="ax-call-io" data-reveal="stream">
                        <div class="ax-box">
                            <div class="ax-box-head" dir="ltr">
                                <span class="ax-arrow" aria-hidden="true">&#9656;</span>
                                <span class="ax-verb ax-post">POST</span>
                                <span class="ax-path">/api/register</span>
                                <span class="ax-right">no key required</span>
                            </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['reg_req']) !!}</pre>
                        </div>
                        <div class="ax-box">
                            <div class="ax-box-head ax-late" dir="ltr" style="--i: 6;">
                                <span class="ax-arrow" aria-hidden="true">&#9666;</span>
                                <span class="ax-sc">201 CREATED</span>
                            </div>
<pre class="ax-code ax-num" dir="ltr" data-clip-ok>{!! $axLines($axPre['reg_res'], 7) !!}</pre>
                            <p class="ax-box-note">
                                In hosted mode, <span class="ax-k">POST /api/register/send-code</span> mails a six-digit code first, and you pass it as <span class="ax-k">verification_code</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="ax-manfoot" aria-hidden="true"><span>Event Schedule</span><span>REST API</span><span>EVENTSCHEDULE(1)</span></p>
            </div>
        </section>

        <div class="ax-keep">
            <x-marketing.related-pages />
        </div>
    </div>

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
