<x-marketing-layout>
    <x-slot name="title">Custom CSS | Advanced Schedule Styling - Event Schedule</x-slot>
    <x-slot name="description">Write your own CSS to customize every pixel of your schedule. Override defaults, add animations, and create a look that's uniquely yours.</x-slot>
    <x-slot name="breadcrumbTitle">Custom CSS</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Custom CSS",
        "description": "Write your own CSS to customize every pixel of your schedule. Your rules are written into the same stylesheet as the built-in styles, immediately after them, so a tie in the cascade goes to you.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Write real CSS against your public schedule pages",
            "Your rules are emitted after the built-in styles in the same stylesheet",
            "10,000 characters per schedule",
            "Sanitized on save: script hooks, @import, @font-face and external url() are stripped",
            "No property allowlist, so modern CSS passes through untouched",
            "Applies to the schedule page, event pages, the embedded calendar and the ticket widget",
            "Layers on top of the free visual styling settings",
            "Included on every schedule in a selfhosted install"
        ],
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Available on Pro plan"
        }
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
           For custom-css "The Stylesheet" styles.

           THE CONCEPT IS THE CASCADE, NOT AN EDITOR. Read the code and
           the feature turns out to be one sentence: everything your
           styling settings generate is written into one style block in
           layouts/app-guest.blade.php, and your custom_css is emitted
           immediately AFTER it, in the same sheet. Equal specificity, so
           a tie goes to the lower rule, and the lower rule is yours.
           That is the metaphor and the product argument at once, so the
           page is built out of the two things a stylesheet has: SOURCE
           ORDER and a SEAM where one author hands over to the next. The
           seam is drawn twice, on purpose: across the hero sheet, and
           again, centred, immediately above the finale CTA, so the page
           itself ends where the reader's stylesheet begins.

           DEVICES THIS PAGE MUST NOT BUILD. /for-ai-agents owns "The
           Console" (a request/response ledger on an always-dark code
           surface); /open-source owns "The Commit Log" (a spine, path
           chips, a unified diff); /embed-calendar owns "The Paste" (a
           slip of paper that is white in both modes); /selfhost owns
           "The Terminal". So there is NO window chrome with three
           traffic lights (the first-wave version of this page had one,
           and it was a costume), no prompt, no +/- diff and no line
           numbers. The sheet here is not an object sitting on the page,
           it IS the page's own surface: ruled monospace text behind a
           hairline rail, with a labelled seam across it.

           MATERIAL: typography. Line rules at the text baseline, a
           left rail, `ch` indents, tabular numerals. Deliberately NOT
           pinned to one colour: a stylesheet is text, not paper, so the
           sheet is authored twice (light and dark) instead of being a
           fixed physical object. The only fixed-dark surface is
           .es-sheet2-band, which carries the shared finale.

           COLOUR: the page keeps the blue family it already had, but
           spends it as ONE flat accent instead of the brand
           blue -> sky -> cyan ramp, which is shared chrome. No gradient
           headline anywhere: the emphasis in a stylesheet is a single
           value that won, so accent is used for exactly that (winning
           declarations, properties, links). #1d4ed8 on the light ground
           (6.15) and #8ab4ff on the dark (9.32). No second hue: the
           filter table marks removals with a label and a dotted rule,
           never with red, so nothing depends on colour alone.

           NEVER text-gray-500 here: #6b7280 measures 4.83 on pure white
           but only ~4.4 on this page's #f4f5f8 ground. Use
           .es-sheet2-muted (7.22 on the ground, 7.87 on a white sheet).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-sheet2-page { background-color: #f4f5f8; color: #11151c; }
        .dark .es-sheet2-page { background-color: #0a0d12; color: #e9edf4; }
        .es-sheet2-ink { color: #11151c; }
        .dark .es-sheet2-ink { color: #e9edf4; }
        .es-sheet2-muted { color: #4a5260; }
        .dark .es-sheet2-muted { color: #98a2b3; }
        .es-sheet2-accent { color: #1d4ed8; }
        .dark .es-sheet2-accent { color: #8ab4ff; }
        /* Always-lit accent, for the fixed-dark band in both colour modes. */
        .es-sheet2-lit { color: #8fb8ff; }
        .es-sheet2-hair { border-top: 1px solid rgba(17, 21, 28, 0.1); }
        .dark .es-sheet2-hair { border-top-color: rgba(233, 237, 244, 0.1); }

        /* --- Cards --- */
        .es-sheet2-card {
            border: 1px solid rgba(17, 21, 28, 0.11);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-sheet2-card {
            border-color: rgba(233, 237, 244, 0.12);
            background: #151a21;
        }

        /* --- The sheet: ruled monospace text, not a window --- */
        .es-sheet2-sheet {
            border: 1px solid rgba(17, 21, 28, 0.11);
            border-radius: 1rem;
            background: #ffffff;
            overflow: hidden;
        }
        .dark .es-sheet2-sheet {
            border-color: rgba(233, 237, 244, 0.12);
            background: #151a21;
        }
        .es-sheet2-sheet-head {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.6rem;
            padding: 0.7rem 1rem;
            border-bottom: 1px solid rgba(17, 21, 28, 0.09);
            background: rgba(17, 21, 28, 0.025);
        }
        .dark .es-sheet2-sheet-head {
            border-bottom-color: rgba(233, 237, 244, 0.1);
            background: rgba(233, 237, 244, 0.03);
        }
        /* The rail: a single hairline the whole sheet hangs off. */
        .es-sheet2-rail {
            padding: 0.85rem 1rem 0.85rem 0;
            margin-inline-start: 1rem;
            border-inline-start: 1px solid rgba(17, 21, 28, 0.13);
            overflow-x: auto;
        }
        .dark .es-sheet2-rail { border-inline-start-color: rgba(233, 237, 244, 0.14); }

        .es-sheet2-line {
            position: relative;
            padding: 0.16rem 0.6rem 0.16rem 0.9rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            line-height: 1.55;
            /* nowrap, not pre: the indent is done with padding, so template
               newlines must collapse instead of printing as whitespace. */
            white-space: nowrap;
            border-bottom: 1px solid rgba(17, 21, 28, 0.045);
        }
        .dark .es-sheet2-line { border-bottom-color: rgba(233, 237, 244, 0.05); }
        .es-sheet2-line:last-child { border-bottom: 0; }

        /* Token inks. Selector and value carry the page ink; the property is
           the accent, because the property is the thing being decided. */
        .es-sheet2-sel { color: #11151c; font-weight: 700; }
        .dark .es-sheet2-sel { color: #e9edf4; }
        .es-sheet2-prop { color: #1d4ed8; }
        .dark .es-sheet2-prop { color: #8ab4ff; }
        .es-sheet2-val { color: #11151c; }
        .dark .es-sheet2-val { color: #e9edf4; }
        .es-sheet2-com { color: #5b6472; }
        .dark .es-sheet2-com { color: #939cab; }
        .es-sheet2-bang { color: #1d4ed8; font-weight: 700; }
        .dark .es-sheet2-bang { color: #8ab4ff; }

        /* The seam: where one author hands over to the next. */
        .es-sheet2-seam {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            margin: 0.5rem 0.6rem 0.5rem 0;
            padding-inline-start: 0.9rem;
        }
        .es-sheet2-seam::after {
            content: "";
            flex: 1 1 auto;
            height: 1px;
            background: repeating-linear-gradient(90deg, rgba(29, 78, 216, 0.55) 0 5px, transparent 5px 10px);
        }
        .dark .es-sheet2-seam::after {
            background: repeating-linear-gradient(90deg, rgba(138, 180, 255, 0.55) 0 5px, transparent 5px 10px);
        }
        .es-sheet2-seam-label {
            flex: 0 0 auto;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #1d4ed8;
        }
        .dark .es-sheet2-seam-label { color: #8ab4ff; }
        /* The finale is the same seam, centred: the page is the built-in half,
           and everything under the last rule is the reader's. */
        .es-sheet2-seam-center {
            justify-content: center;
            max-width: 34rem;
            margin-inline: auto;
            padding-inline-start: 0;
        }
        .es-sheet2-seam-center::before {
            content: "";
            flex: 1 1 auto;
            height: 1px;
            background: repeating-linear-gradient(90deg, rgba(29, 78, 216, 0.55) 0 5px, transparent 5px 10px);
        }
        .dark .es-sheet2-seam-center::before {
            background: repeating-linear-gradient(90deg, rgba(138, 180, 255, 0.55) 0 5px, transparent 5px 10px);
        }

        /* The winning declaration. The bar draws in on reveal and RESTS drawn,
           so no-JS and reduced-motion visitors see the finished state. */
        .es-sheet2-win { background: rgba(29, 78, 216, 0.07); }
        .dark .es-sheet2-win { background: rgba(138, 180, 255, 0.1); }
        .es-sheet2-win::before {
            content: "";
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #1d4ed8;
            transform: scaleY(1);
            transform-origin: top;
            transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dark .es-sheet2-win::before { background: #8ab4ff; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-sheet2-win::before { transform: scaleY(0); }
        /* A trailing state label. Deliberately inline rather than floated right:
           these lines can scroll sideways on a narrow screen, and a float sits
           at the container edge where the code slides underneath it. */
        .es-sheet2-wintag {
            margin-inline-start: 1.4rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-sheet2-wintag { color: #8ab4ff; }

        /* A removed fragment: labelled AND ruled, never colour alone. */
        .es-sheet2-off {
            color: #5b6472;
            text-decoration: line-through;
            text-decoration-style: solid;
            text-decoration-thickness: 1px;
        }
        .dark .es-sheet2-off { color: #939cab; }

        /* --- Inline code token --- */
        .es-sheet2-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8em;
            white-space: nowrap;
            color: #11151c;
        }
        .dark .es-sheet2-code { color: #e9edf4; }

        /* --- Section mark: a mono eyebrow with a hairline lead-in --- */
        .es-sheet2-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4a5260;
        }
        .dark .es-sheet2-mark { color: #98a2b3; }
        .es-sheet2-mark::before {
            content: "";
            width: 1.7rem;
            height: 2px;
            border-radius: 1px;
            background: #1d4ed8;
        }
        .dark .es-sheet2-mark::before { background: #8ab4ff; }
        .es-sheet2-num { font-variant-numeric: tabular-nums; color: #1d4ed8; }
        .dark .es-sheet2-num { color: #8ab4ff; }

        /* --- Plan chips --- */
        .es-sheet2-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(17, 21, 28, 0.3);
            color: #11151c;
        }
        .dark .es-sheet2-plan { border-color: rgba(233, 237, 244, 0.34); color: #e9edf4; }
        .es-sheet2-plan-pro { border-color: rgba(29, 78, 216, 0.45); color: #1d4ed8; }
        .dark .es-sheet2-plan-pro { border-color: rgba(138, 180, 255, 0.45); color: #8ab4ff; }

        /* --- Order pills for the ledger table --- */
        .es-sheet2-ord {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.1rem 0.5rem;
            border-radius: 0.25rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            border: 1px solid rgba(29, 78, 216, 0.35);
            color: #1d4ed8;
        }
        .dark .es-sheet2-ord { border-color: rgba(138, 180, 255, 0.38); color: #8ab4ff; }
        .es-sheet2-ord-under { border-color: rgba(17, 21, 28, 0.28); color: #4a5260; }
        .dark .es-sheet2-ord-under { border-color: rgba(233, 237, 244, 0.3); color: #98a2b3; }

        /* --- The character budget gauge --- */
        .es-sheet2-gauge {
            position: relative;
            height: 0.65rem;
            border-radius: 0.35rem;
            background: rgba(17, 21, 28, 0.08);
            overflow: hidden;
        }
        .dark .es-sheet2-gauge { background: rgba(233, 237, 244, 0.09); }
        .es-sheet2-gauge-fill {
            position: absolute;
            inset-block: 0;
            inset-inline-start: 0;
            width: var(--fill, 0%);
            min-width: 3px;
            border-radius: 0.35rem;
            background: #1d4ed8;
            transition: width 1.1s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dark .es-sheet2-gauge-fill { background: #8ab4ff; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-sheet2-gauge-fill { width: 0; }

        /* A two-column split whose children must be allowed to be narrower than
           their own content. A grid item defaults to `min-width: auto`, so the
           nowrap code lines inside a sheet set a min-content FLOOR on the track:
           measured at a 390px viewport, that pushed the whole document to
           407px and gave the page a horizontal scrollbar. The sheet's rail
           already scrolls on its own, so clamp the item instead. */
        .es-sheet2-duo > * { min-width: 0; }

        /* --- Tables. The min-widths live here, not in an arbitrary Tailwind
               class, so the horizontal scroll works without a rebuild. --- */
        .es-sheet2-table {
            width: 100%;
            min-width: 40rem;
            border-collapse: collapse;
        }
        /* th defaults to centre in the UA sheet, and a table-level text-align
           does not reach it, so say it on the cells. */
        .es-sheet2-table th,
        .es-sheet2-table td { text-align: start; }
        /* Inside a table the code column must wrap, or a long rule pushes the
           last column off the edge of the scroll container. */
        .es-sheet2-table .es-sheet2-code { white-space: normal; }
        .es-sheet2-table-sm { min-width: 28rem; }
        .es-sheet2-th {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4a5260;
        }
        .dark .es-sheet2-th { color: #98a2b3; }
        .es-sheet2-td { border-top: 1px solid rgba(17, 21, 28, 0.09); }
        .dark .es-sheet2-td { border-top-color: rgba(233, 237, 244, 0.09); }

        /* --- Fixed-dark band: the same surface with .dark on and off --- */
        .es-sheet2-band {
            background-color: #0c1016;
            background-image: radial-gradient(120% 100% at 50% 0%, #141b25 0%, #0e131a 55%, #070a0e 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(233, 237, 244, 0.05);
        }
        /* Shared classes carry their own .dark rules in marketing.css, so pin
           every one of them inside the band, AFTER the base rule. */
        .es-sheet2-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 237, 244, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 237, 244, 0.05) 1px, transparent 1px);
        }
        .es-sheet2-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-sheet2-band .es-claim:focus-within {
            border-color: rgba(143, 184, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 184, 255, 0.22);
        }
        .es-sheet2-band .es-sheet2-card {
            border-color: rgba(233, 237, 244, 0.13);
            background: rgba(233, 237, 244, 0.05);
        }
        .es-sheet2-band .es-sheet2-ink { color: #e9edf4; }
        .es-sheet2-band .es-sheet2-muted { color: #98a2b3; }
        .es-sheet2-band .es-sheet2-code { color: #e9edf4; }
        .es-sheet2-band .es-sheet2-mark { color: #98a2b3; }
        .es-sheet2-band .es-sheet2-mark::before { background: #8fb8ff; }
        .es-sheet2-band .es-sheet2-num { color: #8fb8ff; }
        .es-sheet2-band .es-sheet2-plan { border-color: rgba(233, 237, 244, 0.34); color: #e9edf4; }
        .es-sheet2-band .es-sheet2-plan-pro { border-color: rgba(143, 184, 255, 0.45); color: #8fb8ff; }
        .es-sheet2-band .es-sheet2-seam-label { color: #8fb8ff; }
        .es-sheet2-band .es-sheet2-seam::before,
        .es-sheet2-band .es-sheet2-seam::after {
            background: repeating-linear-gradient(90deg, rgba(143, 184, 255, 0.55) 0 5px, transparent 5px 10px);
        }

        /* --- Links and buttons --- */
        .es-sheet2-link { color: #1d4ed8; }
        .es-sheet2-link:hover { color: #11151c; }
        .dark .es-sheet2-link { color: #8ab4ff; }
        .dark .es-sheet2-link:hover { color: #e9edf4; }

        /* Button ink lives on the class, not on a Tailwind arbitrary value, so
           the light-on-dark and dark-on-light pairings cannot silently vanish. */
        .es-sheet2-btn {
            background-color: #1b3fae;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(27, 63, 174, 0.5);
        }
        .es-sheet2-btn:hover { background-color: #16348f; box-shadow: 0 22px 44px -14px rgba(27, 63, 174, 0.6); }
        .dark .es-sheet2-btn { background-color: #8ab4ff; color: #0a0d12; }
        .dark .es-sheet2-btn:hover { background-color: #a6c6ff; }
        .es-sheet2-band .es-sheet2-btn { background-color: #8fb8ff; color: #0c1016; }
        .es-sheet2-band .es-sheet2-btn:hover { background-color: #aecfff; }

        /* Dot-nav tooltip: its own surface, so no arbitrary dark: value. */
        .es-sheet2-tip {
            border: 1px solid rgba(17, 21, 28, 0.12);
            background: #ffffff;
            color: #11151c;
        }
        .dark .es-sheet2-tip {
            border-color: rgba(233, 237, 244, 0.12);
            background: #151a21;
            color: #e9edf4;
        }

        /* --- Hover treatment for FAQ rows and related cards --- */
        .es-sheet2-hover:hover { border-color: rgba(29, 78, 216, 0.45); }
        .dark .es-sheet2-hover:hover { border-color: rgba(138, 180, 255, 0.45); }
        .es-sheet2-hover:hover .es-sheet2-hover-title,
        .es-sheet2-hover:hover .es-sheet2-hover-arrow { color: #1d4ed8; }
        .dark .es-sheet2-hover:hover .es-sheet2-hover-title,
        .dark .es-sheet2-hover:hover .es-sheet2-hover-arrow { color: #8ab4ff; }

        /* --- Marquee chips: real CSS property names --- */
        .es-sheet2-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0.75rem;
            border-radius: 0.4rem;
            border: 1px solid rgba(17, 21, 28, 0.14);
            background: rgba(255, 255, 255, 0.7);
            color: #4a5260;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .dark .es-sheet2-chip {
            border-color: rgba(233, 237, 244, 0.14);
            background: rgba(233, 237, 244, 0.05);
            color: #98a2b3;
        }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(138, 180, 255, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(138, 180, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #8ab4ff; }

        /* --- Focus rings. No border-radius: outlines already follow the shape. --- */
        #es-sheet2-page a:focus-visible,
        #es-sheet2-page summary:focus-visible,
        #es-sheet2-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-sheet2-page a:focus-visible,
        .dark #es-sheet2-page summary:focus-visible,
        .dark #es-sheet2-page button:focus-visible {
            outline-color: #8ab4ff;
        }
        .es-sheet2-band a:focus-visible,
        .es-sheet2-band summary:focus-visible,
        .es-sheet2-band button:focus-visible {
            outline-color: #8fb8ff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-sheet2-win::before,
            .es-sheet2-gauge-fill { transition: none !important; }
        }
    </style>

    @php
        // The hero sheet. Everything above the seam is generated by
        // layouts/app-guest.blade.php from the schedule's styling settings;
        // everything below it is the schedule owner's custom_css, emitted
        // immediately after. `ind` is the indent in ch, `win` marks the
        // declaration that renders because it is the later of two equals.
        $sheetTop = [
            [0, '<span class="es-sheet2-com">/* the guest layout and your styling settings */</span>', false],
            [0, '<span class="es-sheet2-sel">main</span> <span class="es-sheet2-com">{</span>', false],
            [2, '<span class="es-sheet2-prop">height</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">100%;</span>', false],
            [0, '<span class="es-sheet2-com">}</span>', false],
            [0, '<span class="es-sheet2-sel">body</span> <span class="es-sheet2-com">{</span>', false],
            [2, '<span class="es-sheet2-prop">color</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">#33383C</span> <span class="es-sheet2-bang">!important;</span>', false],
            [2, '<span class="es-sheet2-prop">font-family</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">-apple-system,</span> <span class="es-sheet2-com">&hellip;</span> <span class="es-sheet2-bang">!important;</span>', false],
            [2, '<span class="es-sheet2-prop">background-image</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">linear-gradient(&hellip;);</span>', false],
            [0, '<span class="es-sheet2-com">}</span>', false],
        ];
        $sheetYours = [
            [0, '<span class="es-sheet2-sel">#calendar-app</span> <span class="es-sheet2-com">{</span>', false],
            [2, '<span class="es-sheet2-prop">letter-spacing</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">-0.01em;</span>', false],
            [0, '<span class="es-sheet2-com">}</span>', false],
            [0, '<span class="es-sheet2-sel">body</span> <span class="es-sheet2-com">{</span>', false],
            [2, '<span class="es-sheet2-prop">color</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">#f5efe3</span> <span class="es-sheet2-bang">!important;</span><span class="es-sheet2-wintag">renders</span>', true],
            [0, '<span class="es-sheet2-com">}</span>', false],
        ];

        // What a submitted paste looks like after CssUtils::sanitizeCss().
        // Measured, not guessed: the seven patterns delete the matched FRAGMENT
        // only, so `@import url("theme.css");` really does leave
        // ` url("theme.css");` behind, and `url(https:` takes the `url(` with
        // it, leaving `background-image: //cdn.example.com/x.png);`. Both
        // remnants are invalid, so the browser drops them. @font-face is the
        // one pattern that removes a whole block.
        $filterDemo = [
            [0, '<span class="es-sheet2-off">@import</span> <span class="es-sheet2-val">url("theme.css");</span>', 'broken'],
            [0, '<span class="es-sheet2-off">@font-face { &hellip; }</span>', 'removed'],
            [0, '<span class="es-sheet2-sel">.gp-banner a</span> <span class="es-sheet2-com">{</span>', ''],
            [2, '<span class="es-sheet2-prop">background-image</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-off">url(https:</span><span class="es-sheet2-val">//&hellip;);</span>', 'broken'],
            [2, '<span class="es-sheet2-prop">text-decoration</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">none;</span>', 'kept'],
            [2, '<span class="es-sheet2-prop">border-radius</span><span class="es-sheet2-com">:</span> <span class="es-sheet2-val">0.75rem;</span>', 'kept'],
            [0, '<span class="es-sheet2-com">}</span>', ''],
        ];

        // Every rule the guest layout already writes for the surfaces you are
        // restyling, and whether it sits above or below your block.
        // Source: the <style> block in resources/views/layouts/app-guest.blade.php
        // (rows 1-8 above your block, row 9 the language switcher after it) and
        // the later block in resources/views/role/show-guest.blade.php (row 10).
        $ledger = [
            [
                'rule' => 'main { height: 100%; }',
                'from' => 'The guest layout, always',
                'order' => 'above',
                'beat' => 'A plain override. Yours is later, so yours renders.',
            ],
            [
                'rule' => 'body { min-height: 100vh; display: flex; flex-direction: column; }',
                'from' => 'The guest layout, always',
                'order' => 'above',
                'beat' => 'A plain override, but worth knowing the body is a flex column before you rework the layout.',
            ],
            [
                'rule' => '.gp-banner a { text-decoration: underline; font-weight: 600; }',
                'from' => 'The guest layout, always',
                'order' => 'above',
                'beat' => 'A plain override.',
            ],
            [
                'rule' => 'body { color: #33383C !important; }',
                'from' => 'The guest layout, for both colour schemes',
                'order' => 'above',
                'beat' => 'Repeat the !important. Same weight, lower down, so yours renders.',
            ],
            [
                'rule' => 'body { font-family: -apple-system, ... !important; }',
                'from' => 'The typeface setting',
                'order' => 'above',
                'beat' => 'Repeat the !important. You can restyle the type, but you cannot load a new font.',
            ],
            [
                'rule' => 'body { background-image: linear-gradient(...); }',
                'from' => 'Background set to Gradient',
                'order' => 'above',
                'beat' => 'A plain override.',
            ],
            [
                'rule' => 'body { background-color: #hex !important; }',
                'from' => 'Background set to Solid',
                'order' => 'above',
                'beat' => 'Repeat the !important.',
            ],
            [
                'rule' => 'body { background-image: url(...); background-size: cover; }',
                'from' => 'Background set to Image',
                'order' => 'above',
                'beat' => 'A plain override. On your schedule page this one is written for wide screens only.',
            ],
            [
                'rule' => '.gp-lang-switcher { background-color: #f3f4f6; }',
                'from' => 'The guest layout, just after your block',
                'order' => 'below',
                'beat' => 'Written after you, so a plain rule loses. Add !important. The dark-mode variants are .dark-prefixed and already outrank a bare class.',
            ],
            [
                'rule' => '.calendar-panel-border { background: rgba(255,255,255,0.95) !important; }',
                'from' => 'The schedule page, in a later block',
                'order' => 'below',
                'beat' => 'The hardest one. Needs !important AND a more specific selector, such as #calendar-panel-wrapper.calendar-panel-border.',
            ],
        ];

        // CssUtils::removeDangerousCss(), in the order it runs.
        $filters = [
            ['strip_tags()', 'Any HTML tag', 'Runs first, so the stylesheet cannot be closed early and a script tag smuggled in behind it.'],
            ['\\6a avascript', 'CSS escape sequences', 'Decoded before anything is matched, so an encoded version of a blocked word is caught too.'],
            ['expression( )', 'Legacy script execution', 'An old Internet Explorer hook for running script from a stylesheet.'],
            ['javascript:', 'Script URLs', 'Removed wherever it appears.'],
            ['@import', 'Pulling in another stylesheet', 'Which is also why custom CSS cannot import a font sheet.'],
            ['behavior: binding: -moz-binding:', 'Legacy browser code hooks', 'All three property names are stripped.'],
            ['url(http: https: data: //)', 'Anything fetched from elsewhere', 'External and protocol-relative URLs and data URIs, so nothing on your page phones out.'],
            ['@font-face { }', 'Loading a typeface', 'The whole block is removed, not just the source line.'],
            ['@charset', 'Character-set switching', 'Removed outright.'],
        ];

        // Guest-facing surfaces rendered by layouts/app-guest.blade.php, which is
        // where custom_css is emitted, so one sheet covers all of them.
        $surfaces = [
            ['Your schedule page', 'The calendar or list your visitors land on.'],
            ['Every event page', 'One page per event, per date.'],
            ['The embedded calendar', 'The iframe on your own website.'],
            ['The ticket widget', 'The purchase or RSVP form you embed.'],
            ['The event request form', 'Where other people submit to your schedule.'],
            ['Booking, gallery and feedback pages', 'Appointments, fan photos, post-event feedback.'],
        ];

        // Real ids and classes in the guest markup today. Deliberately framed as
        // "look these up", not as a versioned API, because they are not one.
        $hooks = [
            ['#calendar-app', 'The calendar itself'],
            ['#calendar-panel-wrapper', 'The panel the calendar sits in'],
            ['#events-carousel', 'The upcoming-events strip'],
            ['#month-year-title', 'The month heading'],
            ['#event-popup', 'The day popup'],
            ['.gp-banner a', 'Links inside your banner message'],
        ];

        $budget = [
            ['A colour and a corner radius', '180 characters', 2],
            ['A full re-skin: type scale, cards, spacing', '2,400 characters', 24],
            ['The ceiling', '10,000 characters', 100],
        ];

        $faqs = [
            [
                'q' => 'How do I add Custom CSS to my schedule?',
                'a' => 'Edit your schedule, open the Style section and then the Advanced tab. Custom CSS is a plain text area there. It saves with the rest of your styling settings and applies from the next page load.',
            ],
            [
                'q' => 'Where does my CSS land in the cascade?',
                'a' => 'In the same style block as the rules your styling settings generate, immediately after them. Specificity is equal, so a tie goes to the later rule, and the later rule is yours. Three things need more than source order from you: the body colour and font family, which the guest layout declares with !important, the guest language switcher, whose rules are written just after your block, and the calendar panel frame, which is declared further down the page and also needs a more specific selector.',
            ],
            [
                'q' => 'Is Custom CSS secure?',
                'a' => 'Your CSS is sanitized on save, before it is stored. HTML tags are stripped first, then CSS escape sequences are decoded so an encoded blocked word cannot slip through, and then seven patterns are removed: expression(), javascript:, @import, behavior, binding and -moz-binding, url() pointing at http, https, a data URI or a protocol-relative //, @font-face blocks, and @charset. Everything else passes: there is no property allowlist.',
            ],
            [
                'q' => 'Can I load a web font with Custom CSS?',
                'a' => 'No. @font-face blocks and @import are both removed, and url() cannot reach another server, so a stylesheet cannot fetch a font file. Choose your typeface in the font setting, which loads it properly, then use Custom CSS for size, weight, spacing and line height.',
            ],
            [
                'q' => 'Can I use Custom CSS with the built-in styling options?',
                'a' => 'Yes, and that is the intended order. Set the accent colour, typeface, background and header style with the visual controls, all of which are free, and use Custom CSS for the details they do not reach: spacing, borders, shadows, transitions and animations. Your CSS is the last layer, not a replacement for the first one.',
            ],
            [
                'q' => 'How much CSS can I save?',
                'a' => '10,000 characters per schedule, which is roughly 300 lines of ordinary CSS. It is stored with the schedule and travels with your backup export, where it is sanitized again on import.',
            ],
            [
                'q' => 'Does the live preview show my Custom CSS?',
                'a' => 'No. The preview panel on the edit page redraws from the colour, typeface, background and header fields, and never reads the CSS box. Save, then open your schedule page to see your CSS applied, which is also how visitors will see it.',
            ],
            [
                'q' => 'Which plan includes Custom CSS?',
                'a' => 'Pro, and every schedule on a selfhosted install. Free plans keep the whole visual styling suite: accent colour, typeface, solid, gradient or image backgrounds, header style and the list or calendar layout. If a hosted schedule stops being Pro, the CSS stays saved and stops rendering until the plan is active again.',
            ],
        ];

        $dotSections = [
            ['top', 'The sheet'],
            ['cascade', 'The cascade'],
            ['ledger', 'Rules above yours'],
            ['filter', 'What is removed'],
            ['reach', 'Where it lands'],
            ['rest', 'Everything else'],
            ['plan', 'The plan'],
            ['faq', 'Questions'],
            ['claim', 'Start'],
        ];
    @endphp

    <div id="es-sheet2-page" class="es-sheet2-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the sheet, and the seam across it                   -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(29, 78, 216, 0.2), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(138, 180, 255, 0.14), rgba(138, 180, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="es-sheet2-duo grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-sheet2-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span class="es-sheet2-muted text-sm font-medium tracking-wide">Custom CSS, on the Pro plan</span>
                    </div>

                    <h1 class="es-balance es-sheet2-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The built-in styles go first.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-sheet2-accent">Yours go last.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-sheet2-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Your CSS is written into the same stylesheet as everything your styling settings generate, immediately after it. Equal specificity, lower down. In the cascade, that is the whole feature.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#cascade" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the cascade
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-sheet2-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The sheet: one style block, two authors, one seam. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-sheet2-sheet">
                        <div class="es-sheet2-sheet-head">
                            <span class="es-sheet2-code es-sheet2-ink font-bold">&lt;style&gt;</span>
                            <span class="es-sheet2-muted text-xs">one block, on every page of your schedule</span>
                        </div>
                        <div class="es-sheet2-rail" aria-hidden="true">
                            @foreach ($sheetTop as [$ind, $html, $win])
                                <div class="es-sheet2-line" style="padding-inline-start: {{ 0.9 + $ind * 0.55 }}rem;">{!! $html !!}</div>
                            @endforeach

                            <div class="es-sheet2-seam">
                                <span class="es-sheet2-seam-label">your Custom CSS starts here</span>
                            </div>

                            @foreach ($sheetYours as [$ind, $html, $win])
                                <div class="es-sheet2-line @if ($win) es-sheet2-win @endif" style="padding-inline-start: {{ 0.9 + $ind * 0.55 }}rem;">{!! $html !!}</div>
                            @endforeach
                        </div>
                    </div>
                    <p class="es-sheet2-muted mt-4 text-sm">
                        The same declaration, twice. Both carry <span class="es-sheet2-code">!important</span>, so the browser keeps the lower one, and the lower one is yours.
                    </p>
                </div>
            </div>

            <!-- Property marquee: real CSS, not decoration -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['border-radius', 'letter-spacing', 'box-shadow', 'clip-path', 'grid-template-columns', 'transition', 'backdrop-filter', 'text-transform', 'aspect-ratio', 'gap'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-sheet2-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The cascade (fixed-dark band)                             -->
    <!-- ============================================================ -->
    <section id="cascade" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-sheet2-band noise relative mx-auto max-w-7xl overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-sheet2-mark mb-5" data-reveal><span class="es-sheet2-num">02</span> the cascade</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Three rules decide it. <span class="es-sheet2-lit">Order is already yours.</span>
                    </h2>
                    <p class="es-sheet2-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Nothing here is special to Event Schedule. It is the ordinary CSS cascade, and knowing where your block sits in it is most of the job.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-sheet2-card p-6" data-reveal="panel">
                        <p class="es-sheet2-mark mb-4"><span class="es-sheet2-num">01</span> order</p>
                        <h3 class="es-sheet2-ink mb-2 text-lg font-bold">Later wins a tie</h3>
                        <p class="es-sheet2-muted text-sm">Two declarations of equal specificity: the browser keeps the one written further down. Your block is written after everything the styling settings generate, so you win every tie without doing anything.</p>
                    </div>
                    <div class="es-sheet2-card p-6" data-reveal="panel">
                        <p class="es-sheet2-mark mb-4"><span class="es-sheet2-num">02</span> weight</p>
                        <h3 class="es-sheet2-ink mb-2 text-lg font-bold">Match an !important</h3>
                        <p class="es-sheet2-muted text-sm">The body text colour and font family are declared with <span class="es-sheet2-code">!important</span>, so a plain rule of yours will not take. Add <span class="es-sheet2-code">!important</span> and order takes over again, because yours is the later of two equals.</p>
                    </div>
                    <div class="es-sheet2-card p-6" data-reveal="panel">
                        <p class="es-sheet2-mark mb-4"><span class="es-sheet2-num">03</span> specificity</p>
                        <h3 class="es-sheet2-ink mb-2 text-lg font-bold">Specificity still outranks order</h3>
                        <p class="es-sheet2-muted text-sm">An id beats a class no matter where it sits. If a rule refuses to apply, the usual answer is a more specific selector rather than a longer stylesheet.</p>
                    </div>
                </div>

                <p class="es-sheet2-muted mx-auto mt-10 max-w-3xl text-center text-sm" data-reveal>
                    One honest caveat: two things are written after you. The guest language switcher's own rules sit immediately below your block in the same sheet, and the calendar panel's frame is declared with <span class="es-sheet2-code">!important</span> further down the page. Both are in the table below, with what it takes to beat each one.
                    <a href="#ledger" class="es-sheet2-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Read the table
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The rules above yours: a real record                      -->
    <!-- ============================================================ -->
    <section id="ledger" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-sheet2-mark mb-5" data-reveal><span class="es-sheet2-num">03</span> the rules above yours</p>
                <h2 class="es-balance es-sheet2-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything you are <span class="es-sheet2-accent">overriding</span>, listed.
                </h2>
                <p class="es-sheet2-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every rule in that sheet that can get between you and your design. Eight sit above your block, where source order already settles it. Two are written after you, and those two need a lever.
                </p>
            </div>

            <div class="es-sheet2-card overflow-hidden p-2 sm:p-4" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-sheet2-table">
                        <caption class="sr-only">Rules the guest layout already declares, where each comes from, whether it sits above or below your Custom CSS, and what it takes to override</caption>
                        <thead>
                            <tr>
                                <th scope="col" class="es-sheet2-th px-3 pb-3">The rule already in the sheet</th>
                                <th scope="col" class="es-sheet2-th px-3 pb-3">Written by</th>
                                <th scope="col" class="es-sheet2-th px-3 pb-3">Order</th>
                                <th scope="col" class="es-sheet2-th px-3 pb-3">To override</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ledger as $row)
                                <tr>
                                    <th scope="row" class="es-sheet2-td es-sheet2-code es-sheet2-ink px-3 py-4 align-top font-normal">{{ $row['rule'] }}</th>
                                    <td class="es-sheet2-td es-sheet2-muted px-3 py-4 align-top text-sm">{{ $row['from'] }}</td>
                                    <td class="es-sheet2-td px-3 py-4 align-top">
                                        <span class="es-sheet2-ord @if ($row['order'] === 'below') es-sheet2-ord-under @endif">{{ $row['order'] }} yours</span>
                                    </td>
                                    <td class="es-sheet2-td es-sheet2-muted px-3 py-4 align-top text-sm">{{ $row['beat'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-sheet2-muted mt-3 px-3 text-xs sm:hidden">Scroll the table sideways for the order and the override.</p>
            </div>

            <p class="es-sheet2-muted mx-auto mt-6 max-w-3xl text-center text-sm" data-reveal>
                Worth saying plainly: the row about the typeface is a real limit, not a footnote. Custom CSS can restyle type but cannot load it, so the font itself is chosen in the styling settings.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What the filter removes                                   -->
    <!-- ============================================================ -->
    <section id="filter" class="es-sheet2-hair scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-sheet2-mark mb-5" data-reveal><span class="es-sheet2-num">04</span> what is removed</p>
                <h2 class="es-balance es-sheet2-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Seven patterns out. <span class="es-sheet2-accent">No allowlist.</span>
                </h2>
                <p class="es-sheet2-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Your CSS is sanitized when you save it, before it is stored: two preparatory passes, then seven patterns removed. There is no list of approved properties to check your work against, which is the useful part. Grid, custom properties, container queries, <span class="es-sheet2-code">clip-path</span>, keyframes and transitions all pass through untouched.
                </p>
            </div>

            <div class="es-sheet2-duo grid gap-8 lg:grid-cols-[1.15fr_1fr] lg:items-start">
                <div class="es-sheet2-card overflow-hidden p-2 sm:p-4" data-reveal="panel">
                    <div class="overflow-x-auto">
                        <table class="es-sheet2-table es-sheet2-table-sm">
                            <caption class="sr-only">Everything the sanitizer does when Custom CSS is saved, in order: two preparatory passes and seven removed patterns</caption>
                            <thead>
                                <tr>
                                    <th scope="col" class="es-sheet2-th px-3 pb-3">Step</th>
                                    <th scope="col" class="es-sheet2-th px-3 pb-3">What it is</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($filters as [$pattern, $what, $why])
                                    <tr>
                                        <th scope="row" class="es-sheet2-td es-sheet2-code es-sheet2-ink px-3 py-3.5 align-top font-normal">{{ $pattern }}</th>
                                        <td class="es-sheet2-td px-3 py-3.5 align-top">
                                            <span class="es-sheet2-ink block text-sm font-semibold">{{ $what }}</span>
                                            <span class="es-sheet2-muted block text-sm">{{ $why }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="es-sheet2-muted mt-3 px-3 text-xs sm:hidden">Scroll the table sideways for the full description.</p>
                </div>

                <div data-reveal>
                    <div class="es-sheet2-sheet">
                        <div class="es-sheet2-sheet-head">
                            <span class="es-sheet2-code es-sheet2-ink font-bold">after saving</span>
                            <span class="es-sheet2-muted text-xs">what your paste looks like once it is stored</span>
                        </div>
                        <div class="es-sheet2-rail" aria-hidden="true">
                            @foreach ($filterDemo as [$ind, $html, $tag])
                                <div class="es-sheet2-line" style="padding-inline-start: {{ 0.9 + $ind * 0.55 }}rem;">{!! $html !!}@if ($tag)<span class="es-sheet2-wintag">{{ $tag }}</span>@endif</div>
                            @endforeach
                        </div>
                    </div>
                    <p class="es-sheet2-muted mt-4 text-sm">
                        Only the matched fragment is deleted, not the rule around it, and what is left over is invalid, so the browser drops it: an <span class="es-sheet2-code">@import</span> without its at-keyword is not a rule, and a <span class="es-sheet2-code">background-image</span> without its <span class="es-sheet2-code">url(</span> is not a value. <span class="es-sheet2-code">@font-face</span> is the one pattern removed as a whole block. Everything else in the rule still applies.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Where it lands, and how much of it there is               -->
    <!-- ============================================================ -->
    <section id="reach" class="es-sheet2-hair scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <p class="es-sheet2-mark mb-5" data-reveal><span class="es-sheet2-num">05</span> where it lands</p>
                <h2 class="es-balance es-sheet2-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One sheet. <span class="es-sheet2-accent">Every guest page.</span>
                </h2>
                <p class="es-sheet2-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Custom CSS is emitted by the layout every guest-facing page shares, so you write it once and it is already on all of them.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ($surfaces as [$sName, $sBody])
                    <div class="es-sheet2-card flex flex-col p-6" data-reveal>
                        <h3 class="es-sheet2-ink mb-2 text-base font-bold">{{ $sName }}</h3>
                        <p class="es-sheet2-muted text-sm">{{ $sBody }}</p>
                    </div>
                @endforeach
            </div>

            <div class="es-sheet2-duo mt-8 grid gap-6 lg:grid-cols-2">
                <!-- What you select -->
                <div class="es-sheet2-card p-7" data-reveal="panel">
                    <h3 class="es-sheet2-ink mb-2 text-xl font-bold">What you are selecting</h3>
                    <p class="es-sheet2-muted mb-5 text-sm">A few of the ids and classes in the guest markup. Treat these as a starting point rather than a published interface: the fastest way to find the right selector is your browser's element inspector on your own page.</p>
                    <dl class="space-y-2">
                        @foreach ($hooks as [$hSel, $hWhat])
                            <div class="es-sheet2-hair flex flex-wrap items-baseline gap-x-3 gap-y-1 pt-2">
                                <dt class="es-sheet2-code es-sheet2-accent font-semibold">{{ $hSel }}</dt>
                                <dd class="es-sheet2-muted text-sm">{{ $hWhat }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <!-- The budget -->
                <div class="es-sheet2-card flex flex-col p-7" data-reveal="panel">
                    <h3 class="es-sheet2-ink mb-2 text-xl font-bold">How much you get</h3>
                    <p class="es-sheet2-muted mb-6 text-sm">10,000 characters per schedule, which is roughly 300 lines of ordinary CSS. For scale, here is what typical work costs against that ceiling.</p>
                    <div class="mt-auto space-y-5">
                        @foreach ($budget as [$bName, $bSize, $bPct])
                            <div>
                                <div class="mb-1.5 flex flex-wrap items-baseline justify-between gap-2">
                                    <span class="es-sheet2-ink text-sm font-semibold">{{ $bName }}</span>
                                    <span class="es-sheet2-code es-sheet2-muted">{{ $bSize }}</span>
                                </div>
                                <div class="es-sheet2-gauge" aria-hidden="true">
                                    <div class="es-sheet2-gauge-fill" style="--fill: {{ $bPct }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="es-sheet2-hair scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <p class="es-sheet2-mark mb-5" data-reveal><span class="es-sheet2-num">06</span> everything else</p>
                <h2 class="es-balance es-sheet2-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The parts nobody tells you.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-sheet2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sheet2-ink text-xl font-bold">The visual settings do the heavy lifting first</h3>
                                <span class="es-sheet2-plan">Free</span>
                            </div>
                            <p class="es-sheet2-muted mb-4">Accent colour, typeface, a solid or gradient or image background, the header style and whether events show as a calendar or a list are all free and all ungated. They are what write the rules above your block.</p>
                            <p class="es-sheet2-muted text-sm">Which means the sensible order is: get as far as you can with the controls, then spend your CSS on the things they do not reach.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-sheet2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <h3 class="es-sheet2-ink mb-4 text-xl font-bold">The preview will not show it</h3>
                            <p class="es-sheet2-muted">The preview panel beside the styling settings redraws from the colour, typeface, background and header fields. It never reads the CSS box. Save, then open your own schedule page in another tab and reload as you go.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-sheet2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sheet2-ink text-xl font-bold">It travels with your backup</h3>
                                <span class="es-sheet2-plan">Free</span>
                            </div>
                            <p class="es-sheet2-muted">Your stylesheet is part of the schedule export, and it is sanitized again on the way back in. Moving a schedule between installs does not mean re-authoring it.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-sheet2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <h3 class="es-sheet2-ink mb-4 text-xl font-bold">Selfhosted installs have it already</h3>
                            <p class="es-sheet2-muted mb-4">The plan check returns true for every schedule on an install that is not the hosted service, so Custom CSS is simply on. Nothing to buy, nothing to switch on.</p>
                            <p class="es-sheet2-muted text-sm">
                                The sanitizer still runs there, which is deliberate: it also catches the paste that would have broken your own page.
                                <a href="{{ marketing_url('/selfhost') }}" class="es-sheet2-link font-medium hover:underline">How selfhosting works</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-sheet2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-sheet2-ink text-xl font-bold">It goes into the embeds too</h3>
                                <span class="es-sheet2-plan es-sheet2-plan-pro">Pro</span>
                            </div>
                            <p class="es-sheet2-muted">The embedded calendar and the ticket widget are rendered by the same guest layout, so your sheet is inside the iframe on your own site without a second copy to maintain.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-sheet2-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <h3 class="es-sheet2-ink mb-4 text-xl font-bold">What it pairs with</h3>
                            <p class="es-sheet2-muted mb-4">CSS decides how the page looks. Two other settings decide whose page it reads as: removing the Event Schedule footer line, on Pro, and serving the whole thing from your own domain, on Enterprise.</p>
                            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm">
                                <a href="{{ marketing_url('/features/white-label') }}" class="es-sheet2-link font-medium hover:underline">White label</a>
                                <a href="{{ marketing_url('/features/custom-domain') }}" class="es-sheet2-link font-medium hover:underline">Custom domains</a>
                                <a href="{{ route('marketing.docs.schedule_styling') }}#custom-css" class="es-sheet2-link font-medium hover:underline">The styling guide</a>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The plan (fixed-dark band)                                -->
    <!-- ============================================================ -->
    <section id="plan" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-sheet2-band noise relative mx-auto max-w-7xl overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <p class="es-sheet2-mark mb-5" data-reveal><span class="es-sheet2-num">07</span> the plan</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        {{ plan_price($proMonthly) }} a month, <span class="es-sheet2-lit">or selfhost it.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-sheet2-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-sheet2-ink text-lg font-bold">On Free</h3>
                            <span class="es-sheet2-plan">Free</span>
                        </div>
                        <p class="es-sheet2-muted text-sm">The whole visual styling suite. Accent colour, typeface, background, header style, layout, and the schedule itself with calendar sync, followers and newsletters.</p>
                    </div>
                    <div class="es-sheet2-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-sheet2-ink text-lg font-bold">On Pro</h3>
                            <span class="es-sheet2-plan es-sheet2-plan-pro">Pro</span>
                        </div>
                        <p class="es-sheet2-muted text-sm">Custom CSS, alongside ticketing with QR check-in, event graphics, the ticket widget and the removal of our footer line. One price for all of it.</p>
                    </div>
                    <div class="es-sheet2-card p-6" data-reveal="panel">
                        <h3 class="es-sheet2-ink mb-2 text-lg font-bold">Selfhosted</h3>
                        <p class="es-sheet2-muted text-sm">Every schedule on your install has it, because the plan check is true off the hosted service. The app is open source and the styling is not held back.</p>
                    </div>
                </div>

                <p class="es-sheet2-muted mx-auto mt-10 max-w-3xl text-center text-sm" data-reveal>
                    Being straight about the edge case: on the hosted service, a stylesheet is only rendered while the schedule is on Pro. If a plan lapses your CSS stays saved and read-only, and the page falls back to your styling settings until the plan is active again.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Related features                                          -->
    <!-- ============================================================ -->
    <section class="es-sheet2-hair py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-sheet2-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="White Label"
                        description="Remove Event Schedule branding for a fully branded experience"
                        :url="marketing_url('/features/white-label')"
                        icon-color="blue"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Embed Calendar"
                        description="Embed your schedule on any website with an iframe"
                        :url="marketing_url('/features/embed-calendar')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Event Graphics"
                        description="Generate shareable images for social media"
                        :url="marketing_url('/features/event-graphics')"
                        icon-color="orange"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 9. FAQ                                                       -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-sheet2-hair scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <p class="es-sheet2-mark mb-5" data-reveal><span class="es-sheet2-num">08</span> questions</p>
                <h2 class="es-balance es-sheet2-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-sheet2-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What designers ask before they start writing.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-sheet2-hover es-sheet2-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-sheet2-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-sheet2-accent es-sheet2-code flex-none font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-sheet2-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-sheet2-hover-arrow es-sheet2-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-sheet2-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-sheet2-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    {{-- The hero's seam again, closing the page: everything above this
                         line is what we write for you, and everything below it is yours. --}}
                    <div class="es-sheet2-seam es-sheet2-seam-center mb-6">
                        <span class="es-sheet2-seam-label">your Custom CSS starts here</span>
                    </div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Claim the name. <span class="es-sheet2-lit">Then write the sheet.</span>
                    </h2>
                    <p class="es-sheet2-muted mx-auto mb-10 max-w-2xl text-lg">
                        Publishing a schedule is free forever. Custom CSS arrives with Pro at {{ plan_price($proMonthly) }} a month, and it is included on every selfhosted install.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-sheet2-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-sheet2-muted mt-6 text-sm">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-sheet2-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
