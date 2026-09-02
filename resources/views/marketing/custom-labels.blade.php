<x-marketing-layout>
    <x-slot name="title">Custom Labels for Schedules - Event Schedule</x-slot>
    <x-slot name="description">Rename the words on your public schedule. "Events" can be Classes, Services, Openings or Sessions, across 34 labels, plus a form for your translation.</x-slot>
    <x-slot name="breadcrumbTitle">Custom Labels</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Custom Labels",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Management Software",
        "operatingSystem": "Web",
        "description": "Rename the built-in labels on your public schedule. Change 'Events' to 'Classes', 'Follow' to 'Subscribe' or 'Free entry' to 'No cover', across 34 labels. Each label keeps a second form for the language your schedule translates into. Pro plan.",
        "offers": {
            "@type": "Offer",
            "price": "{{ $proMonthly }}",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Included in the Pro plan at {{ plan_price($proMonthly) }} per month. Every Pro feature is included in a selfhosted install."
        },
        "featureList": [
            "34 renameable labels across the public schedule, event pages, photo gallery and appointment booking",
            "Your own wording, typed in, up to 200 characters per label",
            "Per-schedule configuration, so two schedules on one account can use different vocabularies",
            "A second form per label for the language your schedule translates into, filled in automatically or written by hand",
            "Applies to the embeddable calendar and to the Schedule tab in the admin portal",
            "Pro plan"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "custom labels, rename events, schedule terminology, label overrides, classes instead of events",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
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
           For custom-labels "The Rename" styles.

           CONCEPT: a proof sheet with correction tape. Every renameable
           string is a word already printed on your public schedule, and a
           custom label is a strip of tape laid over it. So the page's
           signature is not an illustration, it is a DUPLEX: the same
           interface rendered twice, once in the shipped vocabulary and
           once in yours, with both columns locked to the same baselines
           so the eye reads "nothing moved except the word".

           WHY NOT THE FIRST-WAVE TAG CLOUD: pulsing pills of random width
           said "labels" but argued nothing, and it repeated the chip and
           marquee furniture that already appears on a dozen WP pages. The
           duplex is the argument, and the 34-row specimen table is the
           record.

           NO FULL-TRANSLATION IMAGERY. A custom label carries exactly two
           forms (value and value_en); the first-wave file drew a wall of
           twelve language chips, which is a different product. The
           two-language moment on this page is deliberately a PAIR.

           COLOUR: teal, kept from the first-wave file per the campaign's
           palette rule, but pushed dark on the light ground. #0d9488
           measures 3.45 on this paper and was the single largest source
           of AA failures in the old file, so the light accent is #0f5f57
           (6.93) and the bright #5eead4 is reserved for the dark ground
           and the fixed-dark bands. There are no gradient headings at
           all: a gradient is scored per stop and this page gains nothing
           from one.

           DIFFERENTIATION: /for-djs and /for-dance-groups own teal as
           NEON and as MIRROR GLASS. Here it is printer's ink on paper, so
           the distinctiveness is material and typographic rather than
           hue: tabular monospace for the shipped strings, heavy grotesque
           for the replacements, and a strike that is a real ruled line
           rather than a text-decoration.

           NEVER text-gray-500 on this ground (4.23). Use .es-ren-muted,
           which measures 7.02 light and 7.69 dark.

           BLADE RULE for this block: no @supports probes. A "#" hex
           inside a parenthesised at-rule condition breaks Blade
           compilation of every later parenthesised directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-ren-page { background-color: #f4f6f6; color: #101817; }
        .dark .es-ren-page { background-color: #0a1212; color: #e6eceb; }
        .es-ren-ink { color: #101817; }
        .dark .es-ren-ink { color: #e6eceb; }
        .es-ren-muted { color: #4b5654; }
        .dark .es-ren-muted { color: #9aa8a6; }
        .es-ren-accent { color: #0f5f57; }
        .dark .es-ren-accent { color: #5eead4; }
        /* Always-lit accent, for the bands that stay dark in both modes. */
        .es-ren-lit { color: #5eead4; }
        .es-ren-rule { border-color: rgba(16, 24, 23, 0.1); }
        .dark .es-ren-rule { border-color: rgba(230, 236, 235, 0.12); }

        /* --- Cards --- */
        .es-ren-card {
            border: 1px solid rgba(16, 24, 23, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-ren-card {
            border-color: rgba(230, 236, 235, 0.12);
            background: rgba(230, 236, 235, 0.04);
        }
        .es-ren-band .es-ren-card {
            border-color: rgba(230, 236, 235, 0.14);
            background: rgba(230, 236, 235, 0.05);
        }

        /* --- Fixed-dark band: the desk the two screens sit on --- */
        .es-ren-band {
            background-color: #0b1413;
            background-image: radial-gradient(120% 100% at 50% 0%, #14211f 0%, #0e1a18 55%, #070d0c 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(230, 236, 235, 0.05);
        }
        /* Shared classes that otherwise flip with the colour mode inside a band. */
        .es-ren-band .grid-overlay {
            background-image:
                linear-gradient(rgba(230, 236, 235, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(230, 236, 235, 0.05) 1px, transparent 1px);
        }
        .es-ren-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-ren-band .es-claim:focus-within {
            border-color: rgba(94, 234, 212, 0.75);
            box-shadow: 0 0 0 4px rgba(94, 234, 212, 0.22);
        }
        /* Ink for text sitting directly on the band, in BOTH colour modes.
           Not .es-ren-muted: that resolves to #4b5654 in light mode, which is
           unreadable on #111d1c. */
        .es-ren-band-ink { color: #e6eceb; }
        .es-ren-band-muted { color: #9aa8a6; }

        /* --- Eyebrow --- */
        .es-ren-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #4b5654;
        }
        .dark .es-ren-tag { color: #9aa8a6; }
        .es-ren-band .es-ren-tag { color: #5eead4; }

        /* --- Section numeral --- */
        .es-ren-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.85rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(16, 24, 23, 0.18);
            background: #ffffff;
            color: #101817;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-ren-corner { border-color: rgba(230, 236, 235, 0.2); background: rgba(230, 236, 235, 0.05); color: #e6eceb; }
        .es-ren-band .es-ren-corner { border-color: rgba(230, 236, 235, 0.2); background: rgba(230, 236, 235, 0.05); color: #e6eceb; }
        .es-ren-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #0f5f57;
        }
        .dark .es-ren-corner::before { background: #5eead4; }
        .es-ren-band .es-ren-corner::before { background: #5eead4; }

        /* --- Plan tags --- */
        .es-ren-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(15, 95, 87, 0.42);
            color: #0f5f57;
        }
        .dark .es-ren-plan { border-color: rgba(94, 234, 212, 0.42); color: #5eead4; }
        .es-ren-plan-neutral { border-color: rgba(16, 24, 23, 0.35); color: #101817; }
        .dark .es-ren-plan-neutral { border-color: rgba(230, 236, 235, 0.38); color: #e6eceb; }

        /* --- THE SWAP: the shipped string, ruled through, and yours on tape.
               The strike is a real 2px rule so it reads as a proof mark and
               survives at small sizes where text-decoration disappears. --- */
        .es-ren-key {
            position: relative;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.82rem;
            letter-spacing: 0.01em;
            color: #4b5654;
            white-space: nowrap;
        }
        .dark .es-ren-key { color: #9aa8a6; }
        .es-ren-key::after {
            content: "";
            position: absolute;
            left: -0.12em;
            right: -0.12em;
            top: 52%;
            height: 2px;
            border-radius: 1px;
            background: rgba(15, 95, 87, 0.55);
        }
        .dark .es-ren-key::after { background: rgba(94, 234, 212, 0.5); }

        /* The tape's fill is on the tape ELEMENT, not on an absolutely-positioned
           child. A contrast probe walks ANCESTOR backgrounds, so a sibling fill
           left the tape's ink scored against the page ground: 15 dark-mode AA
           failures at 1.12:1, all of them phantom. The draw is therefore a
           clip-path on the tape itself, which also reads better because the word
           arrives with the tape instead of behind it. */
        .es-ren-tape {
            display: inline-block;
            padding: 0.1rem 0.45rem;
            border-radius: 0.2rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #0d2b28;
            background-color: #c8ece7;
            background-image: linear-gradient(to bottom, #d7f1ec, #c8ece7);
            clip-path: inset(0 0 0 0);
            transition: clip-path 0.75s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: var(--es-ren-tape-delay, 0s);
        }
        .dark .es-ren-tape {
            color: #062120;
            background-color: #7fe3d3;
            background-image: linear-gradient(to bottom, #96ecdf, #7fe3d3);
        }
        /* Inside a fixed-dark band the tape is the SAME physical strip in both
           colour modes, so it is pinned to the brighter stock rather than left
           to follow the mode. #062120 on #7fe3d3 measures 11.10. */
        .es-ren-band .es-ren-tape {
            color: #062120;
            background-color: #7fe3d3;
            background-image: linear-gradient(to bottom, #96ecdf, #7fe3d3);
        }
        /* Undrawn pre-state only, gated behind the motion class. */
        html.es-anim [data-reveal]:not(.is-revealed) .es-ren-tape { clip-path: inset(0 100% 0 0); }

        /* Display-size tape. The base padding is in rem so it holds its physical
           size in a body-copy row; at 48px that same strip crops the descender
           of a "y", so the headline variant switches to em and scales. */
        .es-ren-tape-lg {
            padding: 0.16em 0.28em;
            border-radius: 0.1em;
        }

        .es-ren-arrow { color: #0f5f57; flex: none; }
        .dark .es-ren-arrow { color: #5eead4; }

        /* Blinking caret after the renamed word in the hero: the gesture of
           typing your own word in, and the page's only keyframe. */
        .es-ren-caret {
            display: inline-block;
            width: 0.09em;
            height: 0.9em;
            vertical-align: -0.06em;
            border-radius: 1px;
            background: #0f5f57;
            animation: es-ren-blink 1.15s steps(1, end) infinite;
        }
        .dark .es-ren-caret { background: #5eead4; }
        @keyframes es-ren-blink { 0%, 49% { opacity: 1; } 50%, 100% { opacity: 0.12; } }

        /* --- THE DUPLEX: two lit screens on the dark desk. Both columns use
               the SAME element classes, because that is the whole argument. --- */
        .es-ren-duplex,
        .es-ren-dxhead { display: grid; grid-template-columns: 1fr; gap: 0.5rem 0; }
        .es-ren-dxhead { margin-bottom: 0.75rem; }
        @media (min-width: 700px) {
            .es-ren-duplex,
            .es-ren-dxhead { grid-template-columns: 1fr 2.5rem 1fr; gap: 0; }
        }
        .es-ren-screen {
            background: #ffffff;
            border: 1px solid rgba(16, 24, 23, 0.14);
            padding: 0.7rem 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 3.6rem;
        }
        /* border-bottom-WIDTH, never the `border-bottom` shorthand: the shorthand
           resets border-bottom-color to currentColor, which follows the colour
           mode. The width is 0 so nothing is drawn, but the screens are a fixed
           physical object and the band-diff check reads computed border colour,
           so ten invisible zero-width borders read as ten mode diffs. */
        .es-ren-screen-top { border-radius: 0.9rem 0.9rem 0 0; border-bottom-width: 0; }
        .es-ren-screen-mid { border-bottom-width: 0; }
        .es-ren-screen-end { border-radius: 0 0 0.9rem 0.9rem; }
        @media (max-width: 699px) {
            .es-ren-screen { border-radius: 0.6rem; border-bottom-width: 1px; flex-direction: column; }
            .es-ren-screen-top, .es-ren-screen-mid, .es-ren-screen-end { border-radius: 0.6rem; border-bottom-width: 1px; }
            /* The two column heads describe COLUMNS. Once the columns stack they
               describe nothing, so they are replaced by a label inside the first
               pair of screens and the arrow carries the rest. */
            .es-ren-dxhead { display: none; }
        }
        /* Mode-fixed: this sits on the lit white screen in both colour modes.
           #4b5654 on #ffffff measures 7.61. */
        .es-ren-dxmini {
            display: none;
            margin-bottom: 0.45rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #4b5654;
        }
        @media (max-width: 699px) {
            .es-ren-dxmini { display: block; }
        }
        .es-ren-gut {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5eead4;
        }
        @media (max-width: 699px) {
            .es-ren-gut { padding: 0.15rem 0; transform: rotate(90deg); }
        }

        /* Mock elements. Scored against #ffffff, so gray-400 is banned here. */
        .es-ren-s-btn {
            display: inline-flex;
            align-items: center;
            border-radius: 0.5rem;
            padding: 0.4rem 0.85rem;
            background: #0d5450;
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .es-ren-s-ghost {
            display: inline-flex;
            align-items: center;
            border-radius: 0.5rem;
            padding: 0.4rem 0.85rem;
            border: 1px solid rgba(13, 84, 80, 0.5);
            color: #0d5450;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .es-ren-s-field {
            display: block;
            width: 100%;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5654;
        }
        .es-ren-s-field-box {
            display: block;
            font-weight: 400;
            letter-spacing: normal;
            text-transform: none;
            margin-top: 0.3rem;
            border: 1px solid rgba(16, 24, 23, 0.16);
            border-radius: 0.4rem;
            padding: 0.3rem 0.5rem;
            font-size: 0.78rem;
            color: #101817;
        }
        .es-ren-s-quiet { font-size: 0.82rem; color: #4b5654; text-align: center; }
        .es-ren-s-strong { font-size: 0.9rem; font-weight: 800; color: #101817; }

        /* --- THE SPECIMEN SHEET: the real record of what is renameable --- */
        .es-ren-sheet { width: 100%; border-collapse: collapse; text-align: left; }
        .es-ren-sheet th, .es-ren-sheet td { padding: 0.5rem 0.6rem; vertical-align: baseline; }
        .es-ren-sheet thead th {
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b5654;
            border-bottom: 1px solid rgba(16, 24, 23, 0.18);
        }
        .dark .es-ren-sheet thead th { color: #9aa8a6; border-bottom-color: rgba(230, 236, 235, 0.2); }
        .es-ren-sheet-group th {
            padding-top: 1.4rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #0f5f57;
        }
        .dark .es-ren-sheet-group th { color: #5eead4; }
        .es-ren-sheet tbody tr + tr:not(.es-ren-sheet-group) th,
        .es-ren-sheet tbody tr + tr:not(.es-ren-sheet-group) td {
            border-top: 1px solid rgba(16, 24, 23, 0.08);
        }
        .dark .es-ren-sheet tbody tr + tr:not(.es-ren-sheet-group) th,
        .dark .es-ren-sheet tbody tr + tr:not(.es-ren-sheet-group) td {
            border-top-color: rgba(230, 236, 235, 0.08);
        }
        .es-ren-sheet-ships {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 600;
            color: #101817;
            white-space: nowrap;
        }
        .dark .es-ren-sheet-ships { color: #e6eceb; }
        .es-ren-sheet-idea { font-size: 0.85rem; color: #4b5654; }
        .dark .es-ren-sheet-idea { color: #9aa8a6; }
        /* Chrome makes a scrollable box keyboard-focusable on its own, so this
           wrapper lands in the tab order whether or not it is marked up for it.
           It is therefore given a real region role in the markup and the page's
           own ring here, instead of the UA's blue one which is nearly invisible
           on the dark ground. */
        .es-ren-sheet-wrap { overflow-x: auto; }
        #es-ren-page .es-ren-sheet-wrap:focus-visible {
            outline: 2px solid #0f5f57;
            outline-offset: 3px;
        }
        .dark #es-ren-page .es-ren-sheet-wrap:focus-visible { outline-color: #5eead4; }

        /* --- Chips --- */
        .es-ren-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 24, 23, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4b5654;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
        }
        .dark .es-ren-chip {
            border-color: rgba(230, 236, 235, 0.16);
            background: rgba(230, 236, 235, 0.05);
            color: #b0bcba;
        }

        /* --- The stop rule: where the rename ends --- */
        .es-ren-stop { border-top: 2px solid #0f5f57; }
        .dark .es-ren-stop { border-top-color: #5eead4; }

        /* --- Links and buttons --- */
        .es-ren-link { color: #0f5f57; }
        .es-ren-link:hover { color: #101817; }
        .dark .es-ren-link { color: #5eead4; }
        .dark .es-ren-link:hover { color: #e6eceb; }

        /* Button ink lives here rather than on a dark:text-[...] utility: an
           arbitrary Tailwind value that is not already in the built CSS is a
           silent no-op, and this page cannot run a build. */
        .es-ren-btn {
            background-color: #0d5450;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(13, 84, 80, 0.5);
        }
        .es-ren-btn:hover { background-color: #0a423f; box-shadow: 0 22px 44px -14px rgba(13, 84, 80, 0.6); }
        .dark .es-ren-btn { background-color: #5eead4; color: #0a1212; }
        .dark .es-ren-btn:hover { background-color: #86f3e2; }
        /* A button standing on a fixed-dark band must not follow the colour
           mode: in light mode the mode-following fill (#0d5450) is a dark blob
           on a near-black band, and it registered as the ONE real mode diff in
           the band once the diff snapshot was taken AFTER the 200ms
           transition settled - reading computed style immediately after the
           class flip returns the pre-transition value, so an un-settled band
           diff reports 0 for every transitioned property. Pinned to the lit
           fill: #0a1212 on #5eead4 measures 12.81. */
        .es-ren-band .es-ren-btn { background-color: #5eead4; color: #0a1212; }
        .es-ren-band .es-ren-btn:hover { background-color: #86f3e2; }

        /* --- FAQ / related hover --- */
        .es-ren-hover:hover { border-color: rgba(15, 95, 87, 0.45); }
        .dark .es-ren-hover:hover { border-color: rgba(94, 234, 212, 0.45); }
        .es-ren-hover:hover .es-ren-hover-title,
        .es-ren-hover:hover .es-ren-hover-arrow { color: #0f5f57; }
        .dark .es-ren-hover:hover .es-ren-hover-title,
        .dark .es-ren-hover:hover .es-ren-hover-arrow { color: #5eead4; }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(15, 95, 87, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(94, 234, 212, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(15, 95, 87, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(94, 234, 212, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0f5f57; }
        .dark .es-dot.is-active .es-dot-pip { background: #5eead4; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus, and outlines already follow it. --- */
        #es-ren-page a:focus-visible,
        #es-ren-page summary:focus-visible,
        #es-ren-page button:focus-visible {
            outline: 2px solid #0f5f57;
            outline-offset: 3px;
        }
        .dark #es-ren-page a:focus-visible,
        .dark #es-ren-page summary:focus-visible,
        .dark #es-ren-page button:focus-visible {
            outline-color: #5eead4;
        }
        .es-ren-band a:focus-visible,
        .es-ren-band summary:focus-visible,
        .es-ren-band button:focus-visible {
            outline-color: #5eead4 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-ren-caret { animation: none !important; opacity: 1; }
            .es-ren-tape { clip-path: none !important; transition: none !important; }
        }
    </style>

    @php
        // The hero swap list: the shipped string, then one house's replacement.
        // Every left-hand word is the real English default from
        // resources/lang/en/messages.php, keyed by Role::getCustomizableLabels().
        $heroSwaps = [
            ['Events', 'Classes'],
            ['Follow', 'Join the list'],
            ['Free entry', 'First one free'],
            ['Submit Event', 'Suggest a class'],
        ];

        // Three houses, three vocabularies. Each pair is a real renameable key.
        $houses = [
            [
                'who' => 'A yoga studio',
                'note' => 'Nobody books an event on a Tuesday morning.',
                'pairs' => [['Events', 'Classes'], ['Register', 'Book a mat'], ['Free entry', 'First class free']],
            ],
            [
                'who' => 'A conference',
                'note' => 'The programme has sessions in it, and a call for speakers.',
                'pairs' => [['Events', 'Sessions'], ['Agenda', 'Programme'], ['Submit Event', 'Propose a talk']],
            ],
            [
                'who' => 'A congregation',
                'note' => 'A service is not an event, and the hall is not a venue.',
                'pairs' => [['Events', 'Services'], ['Venue', 'Hall'], ['Follow', 'Get the bulletin']],
            ],
        ];

        // The duplex. One row per element of the same public schedule page:
        // shape, the shipped default, the studio's word.
        // 'shape' picks which mock element renders, and BOTH columns render the
        // same shape, because "only the words move" is the argument.
        $duplex = [
            ['ghost', 'Filter Events', 'Find a class'],
            ['btn', 'Submit Event', 'Suggest a class'],
            ['ghost', 'Follow', 'Join the list'],
            ['field', 'Venue', 'Studio'],
            ['quiet', 'No scheduled events', 'Nothing on the mat today'],
            ['strong', 'Free entry', 'First class free'],
        ];

        // Every renameable label, grouped by the surface it appears on. The keys
        // are Role::getCustomizableLabels() (34 of them) and the wording is the
        // English default from resources/lang/en/messages.php.
        $sheet = [
            [
                'Header and its buttons',
                'role/partials/headers/banner.blade.php, compact.blade.php and action-buttons.blade.php',
                [
                    ['Follow', 'Join the list'],
                    ['Submit Event', 'Suggest a class'],
                    ['Request to Book', 'Enquire'],
                    ['Book a Time', 'Book a slot'],
                    ['Show more', 'Read the rest'],
                    ['Show less', 'Fold it back up'],
                ],
            ],
            [
                'The calendar and its filters',
                'role/partials/calendar.blade.php, on the public page, the embed and your own Schedule tab',
                [
                    ['Events', 'Classes'],
                    ['Filter Events', 'Find a class'],
                    ['Clear Filters', 'Start again'],
                    ['Show All', 'Everything'],
                    ['Schedule', 'Timetable'],
                    ['Category', 'Style'],
                    ['Venue', 'Studio'],
                    ['Online', 'Streamed'],
                    ['Done', 'Close'],
                    ['Load More', 'Show me more'],
                    ['No scheduled events', 'Nothing on the mat today'],
                    ['Past Events', 'Previous classes'],
                    ['Show Past Events', 'Look back'],
                    ['Free entry', 'First class free'],
                ],
            ],
            [
                'An event page',
                'event/show-guest.blade.php',
                [
                    ['About', 'What to expect'],
                    ['Agenda', 'Running order'],
                    ['Read more', 'The long version'],
                    ['Add to Calendar', 'Save the date'],
                    ['Register', 'Book a mat'],
                    ['Get Tickets', 'Reserve a place'],
                    ['Buy Tickets', 'Pay and reserve'],
                    ['Share', 'Tell a friend'],
                    ['Back to Schedule', 'Back to the timetable'],
                ],
            ],
            [
                'Fan content and sponsors',
                'the calendar drawer, event/photo-gallery.blade.php and the sponsors panel',
                [
                    ['Add Photo', 'Share a photo'],
                    ['Add Video', 'Share a clip'],
                    ['Add Comment', 'Leave a note'],
                    ['Photo Gallery', 'From the room'],
                    ['Our Sponsors', 'With thanks to'],
                ],
            ],
        ];

        $sheetCount = array_sum(array_map(fn ($g) => count($g[2]), $sheet));

        $steps = [
            ['01', 'Pick the label', 'Customize, then Custom Labels. The dropdown is searchable and lists the ' . $sheetCount . ' labels you have not already overridden.'],
            ['02', 'Type your word', 'The replacement is free text, up to 200 characters. There is no fixed menu of alternatives, so "Community potluck" is as valid as "Classes".'],
            ['03', 'Save', 'It is live on your public schedule on the next page load. Remove the override and the shipped word comes straight back.'],
        ];

        $faqs = [
            [
                'q' => 'What are custom labels?',
                'a' => 'Custom labels let you rename the built-in words on your public schedule. There are ' . $sheetCount . ' of them, and each one is a string Event Schedule prints for you rather than something you typed: "Events", "Venue", "Follow", "Free entry", "Back to Schedule" and so on. Override "Events" with "Classes" and every place that word appears reads "Classes" instead.',
            ],
            [
                'q' => 'Which labels can I rename?',
                'a' => 'Exactly ' . $sheetCount . ', all of them listed on this page: six on the header and its buttons, fourteen on the calendar and its filters, nine on an event page, and five across fan content and the sponsors panel. Schedule types, plan names and the rest of the admin portal are not on the list.',
            ],
            [
                'q' => 'Do custom labels work with translations?',
                'a' => 'Each override holds two forms: the wording you typed, and a second form in the language your schedule translates into. If you have a translation language set and an AI key configured, the scheduled translation run fills that second form in for you, and on a schedule written in something other than English there is a field for writing it by hand instead. Be clear on what this is not: it is two forms per label, not a translation of the interface into every supported language. The shipped labels are already translated in all ' . count(config('app.supported_languages', [])) . ' languages Event Schedule speaks, and an override replaces that pair for your schedule.',
            ],
            [
                'q' => 'Can I invent a label that does not exist yet?',
                'a' => 'No. You rename one of the ' . $sheetCount . ' labels the pages already print; you cannot add a thirty-fifth. The replacement itself is yours to write, up to 200 characters, and a single save accepts up to 30 overrides at once.',
            ],
            [
                'q' => 'Where do the renamed words show up?',
                'a' => 'On your public schedule page and its calendar, on every event page beneath it, on the photo gallery, on the appointment booking page, in the embeddable calendar, and on the Schedule tab of your own admin portal, which renders the same calendar. Labels are set per schedule, so a studio and a conference on one account keep separate vocabularies.',
            ],
            [
                'q' => 'Is it free?',
                'a' => 'Custom labels are on the Pro plan, which is '.plan_price($proMonthly).' a month. Publishing a schedule, the calendar itself, recurring events, calendar sync, analytics and free registration stay free forever, as do newsletters at 10 emails a month, counted per recipient. A selfhosted install gets every Pro and Enterprise feature, custom labels included.',
            ],
        ];

        $dotSections = [
            ['top', 'The rename'],
            ['room', 'Your room\'s word'],
            ['duplex', 'The same page twice'],
            ['sheet', 'All ' . $sheetCount . ' labels'],
            ['languages', 'Two languages'],
            ['stop', 'Where it stops'],
            ['rest', 'Everything else'],
            ['steps', 'Three steps'],
            ['faq', 'Questions'],
            ['claim', 'Start'],
        ];
    @endphp

    <div id="es-ren-page" class="es-ren-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the swap                                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 32%, rgba(15, 95, 87, 0.18), rgba(15, 95, 87, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 72% 45%, rgba(94, 234, 212, 0.12), rgba(94, 234, 212, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-ren-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                        </svg>
                        <span class="es-ren-muted text-sm font-medium tracking-wide">Custom labels, on the Pro plan</span>
                    </div>

                    <h1 class="es-balance es-ren-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Rename the words.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-ren-accent">Move nothing else.</span><span class="es-ren-caret" aria-hidden="true"></span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-ren-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Your visitors do not call them events. They call them classes, services, gigs, openings or sessions. {{ $sheetCount }} of the words Event Schedule prints on your public schedule are yours to rewrite, and rewriting one moves nothing on the page except the word itself.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#duplex" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See the page twice
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-ren-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Get started free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The swap: four shipped strings, four replacements on tape. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-ren-card p-6 sm:p-7">
                        <p class="es-ren-tag mb-1">Custom labels</p>
                        <p class="es-ren-muted mb-5 text-sm">One studio's overrides. The struck words are what ships.</p>

                        <div class="space-y-3">
                            @foreach ($heroSwaps as $swapIndex => [$shipped, $yours])
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span class="es-ren-key">{{ $shipped }}</span>
                                    <svg aria-hidden="true" class="es-ren-arrow h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    <span class="es-ren-tape" style="--es-ren-tape-delay: {{ 0.12 * $swapIndex }}s;">{{ $yours }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-ren-muted es-ren-rule mt-6 border-t pt-4 text-xs">
                            Stored on the schedule, not on the account. Two schedules under one login can speak differently.
                        </p>
                    </div>
                </div>
            </div>

            <!-- What people actually call them. Labelled deliberately: an
                 unlabelled band of words reads as a menu to pick from, and there
                 is no menu - the replacement is a free-text field. -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <p class="es-ren-muted mb-4 text-center text-xs">Words other schedules typed in themselves. There is no list to pick from; you write the replacement.</p>
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Classes', 'Sessions', 'Services', 'Gigs', 'Openings', 'Screenings', 'Rehearsals', 'Meetups', 'Tastings', 'Shifts', 'Talks', 'Practices'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-ren-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Your room's word                                          -->
    <!-- ============================================================ -->
    <section id="room" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-ren-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The vocabulary</p>
                <h2 class="es-balance es-ren-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Nobody in your room says <span class="es-ren-accent">events.</span>
                </h2>
                <p class="es-ren-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    They say the word they have always said. When the page says something else, the page is the thing that looks wrong.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($houses as $house)
                    <div class="es-ren-card flex flex-col p-7" data-reveal="panel">
                        <h3 class="es-ren-ink mb-2 text-lg font-bold">{{ $house['who'] }}</h3>
                        <p class="es-ren-muted mb-5 text-sm">{{ $house['note'] }}</p>
                        <div class="mt-auto space-y-2.5">
                            @foreach ($house['pairs'] as $pairIndex => [$shipped, $yours])
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <span class="es-ren-key">{{ $shipped }}</span>
                                    <svg aria-hidden="true" class="es-ren-arrow h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    <span class="es-ren-tape text-sm" style="--es-ren-tape-delay: {{ 0.1 * $pairIndex }}s;">{{ $yours }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="es-ren-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                It is also the word they type into a search box when they go looking for you. Matching it costs one setting, not a redesign.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. THE DUPLEX: the same page under two vocabularies          -->
    <!-- ============================================================ -->
    <section id="duplex" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-ren-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(94, 234, 212, 0.12), rgba(94, 234, 212, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-ren-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The same page, twice</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nothing moves. <span class="es-ren-lit">Only the words.</span>
                    </h2>
                    <p class="mt-5 text-lg text-gray-300" data-reveal style="--reveal-delay: 0.15s;">
                        One schedule, rendered under the shipped vocabulary and under a studio's. Same layout, same buttons, same positions, six different strings.
                    </p>
                </div>

                <div data-reveal="panel">
                    <div class="es-ren-dxhead">
                        <p class="es-ren-tag">Ships as</p>
                        <span aria-hidden="true"></span>
                        <p class="es-ren-tag">Reads as</p>
                    </div>

                    <div class="es-ren-duplex">
                        @foreach ($duplex as $rowIndex => [$shape, $shipped, $yours])
                            @php $isFirst = $rowIndex === 0; $isLast = $rowIndex === count($duplex) - 1; @endphp

                            <div class="es-ren-screen @if ($isFirst) es-ren-screen-top @elseif ($isLast) es-ren-screen-end @else es-ren-screen-mid @endif">
                                @if ($isFirst)
                                    <span class="es-ren-dxmini">Ships as</span>
                                @endif

                                @if ($shape === 'btn')
                                    <span class="es-ren-s-btn">{{ $shipped }}</span>
                                @elseif ($shape === 'ghost')
                                    <span class="es-ren-s-ghost">{{ $shipped }}</span>
                                @elseif ($shape === 'field')
                                    <span class="es-ren-s-field">{{ $shipped }}<span class="es-ren-s-field-box">Show All</span></span>
                                @elseif ($shape === 'quiet')
                                    <span class="es-ren-s-quiet">{{ $shipped }}</span>
                                @else
                                    <span class="es-ren-s-strong">{{ $shipped }}</span>
                                @endif
                            </div>

                            <div class="es-ren-gut" aria-hidden="true">
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </div>

                            <div class="es-ren-screen @if ($isFirst) es-ren-screen-top @elseif ($isLast) es-ren-screen-end @else es-ren-screen-mid @endif">
                                @if ($isFirst)
                                    <span class="es-ren-dxmini">Reads as</span>
                                @endif

                                @if ($shape === 'btn')
                                    <span class="es-ren-s-btn">{{ $yours }}</span>
                                @elseif ($shape === 'ghost')
                                    <span class="es-ren-s-ghost">{{ $yours }}</span>
                                @elseif ($shape === 'field')
                                    <span class="es-ren-s-field">{{ $yours }}<span class="es-ren-s-field-box">Everything</span></span>
                                @elseif ($shape === 'quiet')
                                    <span class="es-ren-s-quiet">{{ $yours }}</span>
                                @else
                                    <span class="es-ren-s-strong">{{ $yours }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-ren-card p-6" data-reveal="panel">
                        <p class="es-ren-tag mb-3">What changed</p>
                        <h3 class="es-ren-band-ink mb-2 text-lg font-bold">
                            <span data-count-to="6">6</span> strings
                        </h3>
                        <p class="es-ren-band-muted text-sm">Stored as a small map on the schedule itself. No template was edited and no stylesheet was touched.</p>
                    </div>
                    <div class="es-ren-card p-6" data-reveal="panel">
                        <p class="es-ren-tag mb-3">What did not</p>
                        <h3 class="es-ren-band-ink mb-2 text-lg font-bold">Everything</h3>
                        <p class="es-ren-band-muted text-sm">Layout, order, links, colours, the calendar behaviour and every URL. A rename cannot break a page it does not restructure.</p>
                    </div>
                    <div class="es-ren-card p-6" data-reveal="panel">
                        <p class="es-ren-tag mb-3">Reversible</p>
                        <h3 class="es-ren-band-ink mb-2 text-lg font-bold">Remove and revert</h3>
                        <p class="es-ren-band-muted text-sm">Delete an override and the shipped word returns, already translated, with nothing left behind.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The specimen sheet: every renameable label                -->
    <!-- ============================================================ -->
    <section id="sheet" class="scroll-mt-24 border-y py-20 es-ren-rule lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-ren-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The whole list</p>
                <h2 class="es-balance es-ren-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    All <span class="es-ren-accent">{{ $sheetCount }}</span> of them, printed here.
                </h2>
                <p class="es-ren-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A renameable label is a word the app prints for you. Anything you typed yourself was always yours, so it is not on this list. The right column is one studio's wording, to show the shape of the thing rather than to offer you a menu.
                </p>
            </div>

            <div class="es-ren-card p-5 sm:p-8" data-reveal="panel">
                <div class="es-ren-sheet-wrap" role="region" aria-label="Every renameable label" tabindex="0">
                    <table class="es-ren-sheet">
                        <caption class="es-ren-muted mb-4 text-left text-sm">
                            Every label you can override, grouped by the surface it appears on. Left is the wording Event Schedule ships in English; right is an example replacement.
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col">Ships as</th>
                                <th scope="col">One studio's word</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sheet as [$groupName, $groupWhere, $groupRows])
                                <tr class="es-ren-sheet-group">
                                    <th scope="colgroup" colspan="2">{{ $groupName }} &middot; {{ count($groupRows) }}</th>
                                </tr>
                                @foreach ($groupRows as [$shipped, $yours])
                                    <tr>
                                        <th scope="row" class="es-ren-sheet-ships">{{ $shipped }}</th>
                                        <td class="es-ren-sheet-idea">{{ $yours }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="es-ren-rule mt-6 space-y-2 border-t pt-5">
                    @foreach ($sheet as [$groupName, $groupWhere, $groupRows])
                        <p class="es-ren-muted text-xs"><span class="es-ren-ink font-semibold">{{ $groupName }}:</span> {{ $groupWhere }}</p>
                    @endforeach
                </div>
            </div>

            <p class="es-ren-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                The list is fixed at {{ $sheetCount }}. You cannot add a new label, and a single save accepts up to 30 overrides at once.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Two languages, one label                                  -->
    <!-- ============================================================ -->
    <section id="languages" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-ren-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Two languages</p>
                    <h2 class="es-balance es-ren-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Your word, and <span class="es-ren-accent">your word translated.</span>
                    </h2>
                    <p class="es-ren-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A schedule is written in one language and can offer a second. Each override therefore holds two forms: the wording you typed, and the same wording in the language you translate into. Visitors who flip the language toggle read the right one.
                    </p>
                    <ul class="es-ren-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-ren-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Leave the second form empty and the scheduled translation run fills it in, provided an AI key is configured.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-ren-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>On a schedule written in something other than English, the second field sits directly under the first, so you can write that form yourself and nothing overwrites it. Trade names and in-jokes rarely survive a machine translation.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-ren-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Offering a translation is free on every plan. The rename itself is the Pro part.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-ren-card p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-center gap-2">
                            <h3 class="es-ren-ink text-lg font-bold">One override</h3>
                            <span class="es-ren-plan es-ren-plan-neutral">Pro</span>
                        </div>

                        <p class="es-ren-tag mb-2">Label</p>
                        <p class="mb-6"><span class="es-ren-key">Events</span></p>

                        <p class="es-ren-tag mb-2">Your wording</p>
                        <p class="mb-6 text-2xl">
                            <span class="es-ren-tape">Cours</span>
                        </p>

                        <p class="es-ren-tag mb-2">Translated into English</p>
                        <p class="mb-6 text-2xl">
                            <span class="es-ren-tape" style="--es-ren-tape-delay: 0.2s;">Classes</span>
                        </p>

                        <p class="es-ren-muted es-ren-rule border-t pt-4 text-xs">
                            Two forms, not {{ count(config('app.supported_languages', [])) }}. Event Schedule already ships every built-in label translated in all {{ count(config('app.supported_languages', [])) }} of its languages; an override replaces that pair for your schedule.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Where the rename stops                                    -->
    <!-- ============================================================ -->
    <section id="stop" class="scroll-mt-24 py-20 es-ren-rule border-t lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-ren-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The edges</p>
                <h2 class="es-balance es-ren-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Where the rename <span class="es-ren-accent">stops.</span>
                </h2>
                <p class="es-ren-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Worth knowing before you buy the plan for it, rather than after.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="100">
                <div class="es-ren-card p-7" data-reveal="panel">
                    <p class="es-ren-tag mb-4">It reaches</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Your public schedule page and its calendar.',
                            'Every event page under it, plus the photo gallery and the appointment booking page.',
                            'The embeddable calendar you put on your own site.',
                            'The Schedule tab of your admin portal, which renders that same calendar.',
                        ] as $reach)
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-ren-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-ren-muted text-sm">{{ $reach }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-ren-card es-ren-stop p-7" data-reveal="panel">
                    <p class="es-ren-tag mb-4">It does not reach</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'The rest of the admin portal. This is a rename of what visitors read, not a re-skin of the software you work in.',
                            'The words you already write yourself. Event titles, descriptions, ticket names and sub-schedule names were never on the list, because they were never ours.',
                            'A label that does not exist. You override one of the ' . $sheetCount . '; you cannot invent a thirty-fifth.',
                            'Anybody else\'s schedule. Overrides live on one schedule, so a curator page that lists your events reads in the curator\'s own words, not yours.',
                        ] as $limit)
                            <li class="flex gap-3">
                                <svg aria-hidden="true" class="es-ren-muted mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                <span class="es-ren-muted text-sm">{{ $limit }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-ren-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-ren-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of making it yours.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-ren-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-1 flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-ren-ink text-xl font-bold">Most of the words were already yours</h3>
                                <span class="es-ren-plan">Free</span>
                            </div>
                            <p class="es-ren-muted mb-4">Event titles, descriptions, category names and sub-schedule names are text you type on the free plan, so no plan has ever stood between you and them. Custom labels only cover the {{ $sheetCount }} strings the app supplies.</p>
                            <p class="es-ren-muted mt-auto text-sm">
                                Sub-schedules split one link into strands with their own names and colours.
                                <a href="{{ marketing_url('/features/sub-schedules') }}" class="es-ren-link font-medium hover:underline">How sub-schedules work</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-ren-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-ren-ink text-xl font-bold">Your own CSS</h3>
                                <span class="es-ren-plan es-ren-plan-neutral">Pro</span>
                            </div>
                            <p class="es-ren-muted">Labels change what the page says. Custom CSS changes how it looks, on the same public pages.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-ren-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-ren-ink text-xl font-bold">No Event Schedule branding</h3>
                                <span class="es-ren-plan es-ren-plan-neutral">Pro</span>
                            </div>
                            <p class="es-ren-muted">White label removes the "Powered by" credit, so the page carries your words and nobody else's name.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-ren-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-1 flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-ren-ink text-xl font-bold">Your own domain</h3>
                                <span class="es-ren-plan es-ren-plan-neutral">Enterprise</span>
                            </div>
                            <p class="es-ren-muted mb-4">Renaming the words and then serving them from your own domain is the last step in making a schedule read as part of your site rather than a page on somebody else's.</p>
                            <p class="es-ren-muted mt-auto text-sm">
                                <a href="{{ marketing_url('/features/custom-domain') }}" class="es-ren-link font-medium hover:underline">How custom domains work</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-ren-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-ren-ink text-xl font-bold">Ask in your own words</h3>
                                <span class="es-ren-plan es-ren-plan-neutral">Pro</span>
                            </div>
                            <p class="es-ren-muted">Custom fields let you write the questions on your event and request forms, label and hint included.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-ren-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-1 flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-ren-ink text-xl font-bold">Pick the language you publish in</h3>
                                <span class="es-ren-plan">Free</span>
                            </div>
                            <p class="es-ren-muted mb-4">Set the language your schedule is written in, and optionally a second one to translate into, from the {{ count(config('app.supported_languages', [])) }} Event Schedule speaks. Visitors get a toggle, and your custom labels come along.</p>
                            <p class="es-ren-muted mt-auto text-sm">
                                Details are in the schedule guide.
                                <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-labels" class="es-ren-link font-medium hover:underline">Read the Custom Labels guide</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Three steps                                               -->
    <!-- ============================================================ -->
    <section id="steps" class="scroll-mt-24 es-ren-rule border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-ren-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ($steps as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-ren-card p-7" data-reveal="panel">
                        <div class="es-ren-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-ren-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-ren-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="es-ren-rule border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-ren-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="White Label" description="Remove the Powered by credit from your public pages" :url="marketing_url('/features/white-label')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom CSS" description="Style your schedule pages with your own stylesheet" :url="marketing_url('/features/custom-css')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Write your own questions on event and request forms" :url="marketing_url('/features/custom-fields')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Split one link into named, colour-coded strands" :url="marketing_url('/features/sub-schedules')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-ren-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="es-ren-rule border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-ren-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/features/white-label', 'White Label'], ['/features/custom-css', 'Custom CSS'], ['/features/custom-domain', 'Custom Domain'], ['/features/sub-schedules', 'Sub-schedules']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-ren-hover es-ren-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-ren-hover-title es-ren-ink mb-3 text-sm font-semibold transition-colors">{{ $relName }}</span>
                        <span class="es-ren-hover-arrow es-ren-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-ren-link inline-flex items-center font-medium hover:underline">
                    Browse every feature
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-ren-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-ren-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-ren-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything you need to know about custom labels.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-ren-hover es-ren-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-ren-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-ren-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-ren-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-ren-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-ren-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="es-ren-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-30"></div>
            </div>
            <div class="relative z-10 mx-auto max-w-6xl">
                <p class="es-ren-tag mb-4">Free to start</p>
                <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                    Your schedule, <span class="es-ren-tape es-ren-tape-lg">your words.</span>
                </h2>
                <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300">
                    Claim the address, publish the calendar, and rename the {{ $sheetCount }} words when you upgrade. No credit card required.
                </p>

                <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                    <label for="es-claim-input" class="sr-only">Your schedule name</label>
                    <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                        <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                            class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                        <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                    </div>
                    <a href="{{ app_url('/sign_up') }}" class="es-ren-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                        <span class="relative z-10 flex items-center gap-2">
                            Start for free
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                        <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                    </a>
                </div>

                <p class="mt-6 text-sm text-gray-400">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#141d1b] dark:text-gray-300">{{ $sectionLabel }}</span>
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
