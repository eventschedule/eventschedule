<x-marketing-layout>
    <x-slot name="title">Custom Fields | Ask Your Own Questions on Every Form - Event Schedule</x-slot>
    <x-slot name="description">Add your own questions to your ticket form, your registration form and your public event request form. Six field types, ten fields each on your schedule, your event and your ticket types, and every answer in your sales export.</x-slot>
    <x-slot name="breadcrumbTitle">Custom Fields</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Custom Fields",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Registration Forms",
        "operatingSystem": "Web",
        "description": "Define your own questions and have them asked on ticket forms, registration forms and public event request forms, with the answers filed on the order, the ticket and the sales export.",
        "featureList": [
            "Six field types: text, long text, Yes/No, date, dropdown and multi-select",
            "Ten fields per schedule, ten per event and ten per ticket type",
            "Fields asked once per order or once per ticket, or once per guest in a party",
            "Questions asked on the public event request form",
            "Required fields enforced in the browser and on the server",
            "Validation patterns with ready-made presets, a tester and a hint",
            "Private fields kept off the public schedule",
            "Answers on the request card, the sales table, the ticket and the CSV export",
            "Field values available to graphic templates and URL patterns as {custom_1} to {custom_10}",
            "Public dropdown and multi-select fields become filters on your guest calendar",
            "Field names and dropdown options translated automatically"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free plan available. Custom fields are included on the Pro plan."
        },
        "url": "{{ url()->current() }}",
        "keywords": "custom fields, event registration form, attendee questions, checkout questions, event request form, dietary requirements, form validation",
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
           For custom-fields "The Form" styles. The page IS a form: a
           definition on one side, an answer on the other. A custom field
           is written once and the product renders it as a question and
           files it as a column, so the whole page is built out of two
           marks - the RULE (a blank waiting to be filled) and the SLOT
           (the numbered register the answer lands in).

           THE SIGNATURE DEVICE IS A BASELINE RULE, NOT A BOXED INPUT.
           Boxed inputs are house furniture: every mock on this site
           already draws them, and /for-spoken-word owns the physical
           paper sign-up sheet. So controls here are drawn as underlines
           on a cool office grey, and an unanswered field is a hollow
           rule while an answered one is an inked rule. The section marks
           are {custom_1} to {custom_10}, which are the product's REAL
           template variables, so the page's numbering is also its
           argument.

           COLOUR: the page keeps its inherited amber family but spends
           it at the burnt end (#9a3412 stamped ink) on a cool grey
           ground rather than the warm gold-on-black the other amber
           pages use. Measured: #9a3412 on #f4f5f7 = 6.70, #fdba74 on
           #0c0f12 = 11.40, white on #9a3412 = 7.31, #0c0f12 on #fdba74
           = 11.40.

           NEVER use text-gray-500 here - it measures 4.83 on pure white
           but drops on this tinted ground. Use .es-form-muted (7.36 on
           the light ground, 7.51 on the dark one).

           NO GRADIENT HEADING TEXT on purpose: a gradient is scored at
           every stop and the bright amber stops fail on a light ground.
           Accent words get .es-form-fill instead, which is a filled-in
           blank and is on-concept.

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-form-page { background-color: #f4f5f7; color: #14181c; }
        .dark .es-form-page { background-color: #0c0f12; color: #e9edf1; }

        .es-form-ink { color: #14181c; }
        .dark .es-form-ink { color: #e9edf1; }
        .es-form-band .es-form-ink { color: #e9edf1; }

        .es-form-muted { color: #4b5158; }
        .dark .es-form-muted { color: #9aa3ac; }
        .es-form-band .es-form-muted { color: #9aa3ac; }

        .es-form-accent { color: #9a3412; }
        .dark .es-form-accent { color: #fdba74; }

        /* An accent word sitting on a filled-in blank. Drawn with
           text-decoration, NOT a background gradient: a contrast probe that
           walks up for the effective ground takes a gradient's first colour
           stop as the background, so an underline painted as a background
           image scores the accent against itself and fails at 1:1. An
           underline is also the truer mark here - it is the rule of the
           blank the word fills in. */
        .es-form-fill {
            color: #9a3412;
            text-decoration-line: underline;
            text-decoration-color: rgba(154, 52, 18, 0.55);
            text-decoration-thickness: 0.09em;
            text-underline-offset: 0.15em;
        }
        .dark .es-form-fill {
            color: #fdba74;
            text-decoration-color: rgba(253, 186, 116, 0.6);
        }
        .es-form-band .es-form-fill {
            color: #fdba74;
            text-decoration-color: rgba(253, 186, 116, 0.6);
        }

        /* --- Eyebrow labels and mono keys --- */
        .es-form-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-form-tag { color: #9aa3ac; }
        .es-form-band .es-form-tag { color: #fdba74; }

        .es-form-key {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            letter-spacing: 0.02em;
            color: #9a3412;
        }
        .dark .es-form-key { color: #fdba74; }

        /* --- Section mark: the slot number IS the template variable --- */
        .es-form-slot {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(20, 24, 28, 0.16);
            background: #ffffff;
            color: #14181c;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.75rem;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
        }
        .dark .es-form-slot { border-color: rgba(233, 237, 241, 0.18); background: rgba(233, 237, 241, 0.05); color: #e9edf1; }
        .es-form-band .es-form-slot { border-color: rgba(233, 237, 241, 0.18); background: rgba(233, 237, 241, 0.05); color: #e9edf1; }
        .es-form-slot::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #9a3412;
        }
        .dark .es-form-slot::before { background: #fdba74; }
        .es-form-band .es-form-slot::before { background: #fdba74; }

        /* --- Surfaces --- */
        .es-form-card {
            border: 1px solid rgba(20, 24, 28, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-form-card {
            border-color: rgba(233, 237, 241, 0.12);
            background: rgba(233, 237, 241, 0.04);
        }
        .es-form-band .es-form-card {
            border-color: rgba(233, 237, 241, 0.13);
            background: rgba(233, 237, 241, 0.05);
        }

        /* Secondary surface for the form mocks nested inside a card. */
        .es-form-well {
            border: 1px solid rgba(20, 24, 28, 0.1);
            border-radius: 0.75rem;
            background: #eceef1;
        }
        .dark .es-form-well { border-color: rgba(233, 237, 241, 0.1); background: #141a20; }

        /* --- Fixed-dark band --- */
        .es-form-band {
            background-color: #0e1318;
            background-image: radial-gradient(120% 100% at 50% 0%, #18202a 0%, #10161c 55%, #080b0e 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(233, 237, 241, 0.05);
        }
        /* Shared classes that flip with the colour mode: pin them so the band
           renders identically with .dark on and off. */
        .es-form-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 237, 241, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 237, 241, 0.05) 1px, transparent 1px);
        }
        .es-form-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-form-band .es-claim:focus-within {
            border-color: rgba(253, 186, 116, 0.75);
            box-shadow: 0 0 0 4px rgba(253, 186, 116, 0.22);
        }

        /* --- The control, drawn as a blank rule --- */
        .es-form-ctl {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.3rem 0.1rem;
            border-bottom: 2px solid rgba(20, 24, 28, 0.22);
            font-size: 0.85rem;
            color: #4b5158;
        }
        .dark .es-form-ctl { border-bottom-color: rgba(233, 237, 241, 0.22); color: #9aa3ac; }
        /* An answered blank: inked rule, ink value. */
        .es-form-ctl-on {
            border-bottom-color: #9a3412;
            color: #14181c;
            font-weight: 600;
        }
        .dark .es-form-ctl-on { border-bottom-color: #fdba74; color: #e9edf1; }

        /* --- The definition slip: a spec sheet, label left, value right --- */
        .es-form-spec { margin: 0; }
        .es-form-spec > div {
            display: grid;
            grid-template-columns: 8.5rem 1fr;
            gap: 0.75rem;
            padding: 0.45rem 0;
            border-top: 1px solid rgba(20, 24, 28, 0.09);
        }
        .dark .es-form-spec > div { border-top-color: rgba(233, 237, 241, 0.09); }
        .es-form-spec > div:first-child { border-top: 0; }
        .es-form-spec dt {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #4b5158;
            padding-top: 0.15rem;
        }
        .dark .es-form-spec dt { color: #9aa3ac; }
        .es-form-spec dd {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
            color: #14181c;
        }
        .dark .es-form-spec dd { color: #e9edf1; }
        @media (max-width: 400px) {
            .es-form-spec > div { grid-template-columns: 1fr; gap: 0.15rem; }
        }
        /* Stacked variant, for a spec slip sitting inside a narrow card well
           where an 8.5rem label column would leave the value two words wide. */
        .es-form-spec-tight > div { grid-template-columns: 1fr; gap: 0.15rem; }

        /* --- Flag pills: the four switches a field carries --- */
        .es-form-switch {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.25rem 0.7rem 0.25rem 0.5rem;
            border-radius: 9999px;
            border: 1px solid rgba(20, 24, 28, 0.16);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #4b5158;
        }
        .dark .es-form-switch { border-color: rgba(233, 237, 241, 0.16); color: #9aa3ac; }
        .es-form-switch::before {
            content: "";
            width: 0.55rem;
            height: 0.55rem;
            border-radius: 2px;
            border: 1.5px solid currentColor;
        }
        .es-form-switch-on {
            border-color: rgba(154, 52, 18, 0.45);
            color: #9a3412;
        }
        .dark .es-form-switch-on { border-color: rgba(253, 186, 116, 0.45); color: #fdba74; }
        .es-form-switch-on::before { background: currentColor; }

        /* --- The band texture: a row of blanks, some inked.
               Abstract strokes only - no outline illustration. --- */
        .es-form-blanks {
            display: flex;
            align-items: flex-end;
            gap: 0.6rem;
        }
        .es-form-blank {
            flex: 1 1 0;
            min-width: 0;
            height: 2px;
            border-radius: 1px;
            background: rgba(233, 237, 241, 0.16);
        }
        .es-form-blank-on {
            background: #fdba74;
            animation: es-form-ink var(--bk-dur, 3.4s) ease-in-out infinite;
            animation-delay: var(--bk-delay, 0s);
        }
        @keyframes es-form-ink {
            0%, 100% { opacity: 0.35; transform: scaleX(0.55); }
            50% { opacity: 1; transform: scaleX(1); }
        }
        .es-form-blank-on { transform-origin: left center; }

        /* --- The ten-slot register (real content, not ornament) --- */
        .es-form-reg {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .es-form-reg-slot {
            display: inline-flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1 1 4.5rem;
            min-width: 4.5rem;
            padding: 0.55rem 0.6rem;
            border-radius: 0.5rem;
            border: 1px dashed rgba(20, 24, 28, 0.2);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.65rem;
            font-variant-numeric: tabular-nums;
            color: #4b5158;
        }
        .dark .es-form-reg-slot { border-color: rgba(233, 237, 241, 0.2); color: #9aa3ac; }
        .es-form-reg-slot-on {
            border-style: solid;
            border-color: rgba(154, 52, 18, 0.5);
            background: rgba(154, 52, 18, 0.07);
            color: #9a3412;
        }
        .dark .es-form-reg-slot-on {
            border-color: rgba(253, 186, 116, 0.5);
            background: rgba(253, 186, 116, 0.08);
            color: #fdba74;
        }

        /* --- Real tables --- */
        .es-form-table { width: 100%; border-collapse: collapse; }
        .es-form-table th,
        .es-form-table td {
            text-align: start;
            padding: 0.7rem 0.9rem 0.7rem 0;
            vertical-align: top;
            border-top: 1px solid rgba(20, 24, 28, 0.1);
        }
        .dark .es-form-table th,
        .dark .es-form-table td { border-top-color: rgba(233, 237, 241, 0.1); }
        .es-form-table thead th {
            border-top: 0;
            padding-top: 0;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-form-table thead th { color: #9aa3ac; }
        .es-form-table tbody th {
            font-size: 0.92rem;
            font-weight: 700;
            color: #14181c;
            white-space: nowrap;
        }
        .dark .es-form-table tbody th { color: #e9edf1; }
        .es-form-table tbody td { font-size: 0.85rem; color: #4b5158; }
        .dark .es-form-table tbody td { color: #9aa3ac; }

        /* The export row, in tabular mono. */
        .es-form-csv {
            width: 100%;
            border-collapse: collapse;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.7rem;
            white-space: nowrap;
        }
        .es-form-csv th,
        .es-form-csv td {
            text-align: start;
            padding: 0.5rem 1.1rem 0.5rem 0;
            border-top: 1px solid rgba(20, 24, 28, 0.1);
        }
        .dark .es-form-csv th,
        .dark .es-form-csv td { border-top-color: rgba(233, 237, 241, 0.1); }
        .es-form-csv thead th {
            border-top: 0;
            padding-top: 0;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-form-csv thead th { color: #9aa3ac; }
        .es-form-csv tbody td { color: #14181c; }
        .dark .es-form-csv tbody td { color: #e9edf1; }
        .es-form-csv .es-form-csv-new { color: #9a3412; }
        .dark .es-form-csv .es-form-csv-new { color: #fdba74; }

        /* --- Section separators. Written as real rules rather than
               border-[rgba(...)] utilities, because an arbitrary Tailwind value
               that is not already in the built stylesheet renders as nothing. --- */
        .es-form-hr { border-top: 1px solid rgba(20, 24, 28, 0.09); }
        .dark .es-form-hr { border-top-color: rgba(233, 237, 241, 0.09); }
        .es-form-hr-y {
            border-top: 1px solid rgba(20, 24, 28, 0.09);
            border-bottom: 1px solid rgba(20, 24, 28, 0.09);
        }
        .dark .es-form-hr-y {
            border-top-color: rgba(233, 237, 241, 0.09);
            border-bottom-color: rgba(233, 237, 241, 0.09);
        }

        /* Dot-nav tooltip ground (the shared nav ships brand-blue defaults). */
        .es-form-tip {
            border: 1px solid rgba(20, 24, 28, 0.12);
            background: #ffffff;
            color: #14181c;
        }
        .dark .es-form-tip {
            border-color: rgba(233, 237, 241, 0.12);
            background: #141a20;
            color: #e9edf1;
        }

        /* --- The duplex rule: definition on one side, answer on the other --- */
        .es-form-divide { border-top: 1px solid rgba(20, 24, 28, 0.12); padding-top: 2rem; }
        .dark .es-form-divide { border-top-color: rgba(233, 237, 241, 0.12); }
        @media (min-width: 768px) {
            .es-form-divide {
                border-top: 0;
                padding-top: 0;
                border-inline-start: 1px solid rgba(20, 24, 28, 0.12);
                padding-inline-start: 2.5rem;
            }
            .dark .es-form-divide { border-inline-start-color: rgba(233, 237, 241, 0.12); }
        }

        /* --- Plan tags --- */
        .es-form-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(20, 24, 28, 0.35);
            color: #14181c;
        }
        .dark .es-form-plan { border-color: rgba(233, 237, 241, 0.38); color: #e9edf1; }
        .es-form-band .es-form-plan { border-color: rgba(233, 237, 241, 0.38); color: #e9edf1; }
        .es-form-plan-pro { border-color: rgba(154, 52, 18, 0.5); color: #9a3412; }
        .dark .es-form-plan-pro { border-color: rgba(253, 186, 116, 0.5); color: #fdba74; }
        .es-form-band .es-form-plan-pro { border-color: rgba(253, 186, 116, 0.5); color: #fdba74; }

        /* --- Links and buttons --- */
        .es-form-link { color: #9a3412; }
        .es-form-link:hover { color: #14181c; }
        .dark .es-form-link { color: #fdba74; }
        .dark .es-form-link:hover { color: #e9edf1; }
        .es-form-band .es-form-link { color: #fdba74; }
        .es-form-band .es-form-link:hover { color: #e9edf1; }

        .es-form-btn {
            background-color: #9a3412;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(154, 52, 18, 0.5);
        }
        .es-form-btn:hover { background-color: #7c2a0e; box-shadow: 0 22px 44px -14px rgba(154, 52, 18, 0.6); }
        .dark .es-form-btn { background-color: #fdba74; color: #0c0f12; }
        .dark .es-form-btn:hover { background-color: #fed7aa; }
        .es-form-band .es-form-btn { background-color: #fdba74; color: #0c0f12; }
        .es-form-band .es-form-btn:hover { background-color: #fed7aa; }

        /* --- Hover states on cards that are links or summaries --- */
        .es-form-hover:hover { border-color: rgba(154, 52, 18, 0.45); }
        .dark .es-form-hover:hover { border-color: rgba(253, 186, 116, 0.45); }
        .es-form-hover:hover .es-form-hover-title,
        .es-form-hover:hover .es-form-hover-arrow { color: #9a3412; }
        .dark .es-form-hover:hover .es-form-hover-title,
        .dark .es-form-hover:hover .es-form-hover-arrow { color: #fdba74; }

        /* --- Marquee chips: real questions people ask --- */
        .es-form-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.9rem;
            border-radius: 0.4rem;
            border: 1px solid rgba(20, 24, 28, 0.14);
            background: rgba(255, 255, 255, 0.75);
            color: #4b5158;
            font-size: 0.76rem;
            font-weight: 600;
        }
        .dark .es-form-chip {
            border-color: rgba(233, 237, 241, 0.14);
            background: rgba(233, 237, 241, 0.05);
            color: #9aa3ac;
        }

        /* --- Staged inner reveal. The pre-state is gated behind es-anim so a
               no-JS visitor, a crawler and a reduced-motion user all see the
               finished state. --- */
        .es-form-line {
            transition: opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1), transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
            transition-delay: calc(var(--i, 0) * 0.12s + 0.35s);
        }
        html.es-anim [data-reveal]:not(.is-revealed) .es-form-line {
            opacity: 0;
            transform: translateY(8px);
        }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(154, 52, 18, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(253, 186, 116, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(154, 52, 18, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(253, 186, 116, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #9a3412; }
        .dark .es-dot.is-active .es-dot-pip { background: #fdba74; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-form-page a:focus-visible,
        #es-form-page summary:focus-visible,
        #es-form-page button:focus-visible {
            outline: 2px solid #9a3412;
            outline-offset: 3px;
        }
        .dark #es-form-page a:focus-visible,
        .dark #es-form-page summary:focus-visible,
        .dark #es-form-page button:focus-visible {
            outline-color: #fdba74;
        }
        .es-form-band a:focus-visible,
        .es-form-band summary:focus-visible,
        .es-form-band button:focus-visible {
            outline-color: #fdba74 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-form-blank-on { animation: none !important; opacity: 1; transform: none; }
            .es-form-line { transition: none !important; }
        }
    </style>

    @php
        // The six field types that actually exist. Validated in
        // RoleUpdateRequest ('in:string,multiline_string,switch,date,dropdown,multiselect')
        // and rendered by components/custom-field-input.blade.php and
        // event/tickets.blade.php.
        $types = [
            ['Text', 'string', 'A single line to type in', 'Anything they type', 'Yes'],
            ['Long text', 'multiline_string', 'A box that takes paragraphs', 'Anything they type', 'On the server'],
            ['Yes / No', 'switch', 'A switch on a request form, a Yes or No menu at checkout', 'Fixed', 'No'],
            ['Date', 'date', 'A date picker', 'Any date', 'No'],
            ['Dropdown', 'dropdown', 'One choice from your list', 'A comma separated list you type', 'No'],
            ['Multi-select', 'multiselect', 'A checklist, any number of ticks', 'A comma separated list you type', 'No'],
        ];

        // The three places a field can be defined, named by the cadence of the
        // answer, because that is what choosing between them actually decides.
        // Every one of them is Pro.
        $homes = [
            [
                'Once per event',
                'Defined on the schedule: Customize, then Custom Fields',
                'Ten per schedule, answered once for each event. They appear on your own event form, and each one can also be asked of visitors on your public event request form. This is the set that carries the extra switches.',
                'All four switches',
                'You, or a visitor submitting an event to you',
            ],
            [
                'Once per order',
                'Defined on the event, with tickets or registration',
                'Ten per event, asked once for the whole order. The same set appears on your ticket form and on your registration form, so the question does not change with the way you are taking sign-ups.',
                'Required only',
                'Whoever is buying or registering, once',
            ],
            [
                'Once per ticket',
                'Defined on each ticket type',
                'Ten per ticket type, shown only while that ticket is in the basket. Turn on per guest fields and everyone in the party answers for themselves instead of one person answering for six.',
                'Required only',
                'The buyer, or each guest in the party',
            ],
        ];

        // The four per-field switches on a schedule field.
        $flags = [
            ['Required', 'on', 'The form will not submit without an answer. Checked in the browser and checked again on the server, so an empty answer cannot slip past either one.'],
            ['On request form', 'on', 'Ask the question of visitors submitting an event to you. On by default. Uncheck it to keep the field for your own use inside the admin portal.'],
            ['Private', 'off', 'Keep the answer off your public schedule. It stays visible to you, and it still fills {custom_N} in graphic templates and URL patterns.'],
            ['Validation pattern', 'on', 'Text fields can require a format. Pick email address, phone number, web address, numbers only or letters and numbers, or write your own, and test a sample value before you save it.'],
        ];

        // Where the answer to one field turns up. Every row is a real surface.
        $landings = [
            ['On the request card', 'A visitor answers on your event request form and the answer sits on the request in the admin portal, then travels with the event once you accept it.'],
            ['On the order', 'Checkout answers appear beside the sale in your sales table, per order and per ticket.'],
            ['On the ticket', 'The answers a buyer gave are printed on the ticket itself, so the person on the door reads the same thing you do.'],
            ['In the export', 'The sales CSV gains one column per field name, so a spreadsheet of dietary counts is a download rather than an afternoon.'],
        ];

        $faqs = [
            [
                'q' => 'What types of custom field can I create?',
                'a' => 'Six. Text, long text, Yes or No, date, dropdown and multi-select. Dropdown and multi-select take a comma separated list of choices that you type, and text fields can also require a format such as an email address or a reference code.',
            ],
            [
                'q' => 'Where do custom fields appear?',
                'a' => 'It depends on where you define them. Fields defined on the schedule appear on your own event form and, unless you turn it off, on your public event request form. Fields defined on an event are asked once per order on the ticket form and the free registration form. Fields defined on a ticket type are asked only when that ticket is being bought, and can be asked of each guest in the party.',
            ],
            [
                'q' => 'Are custom fields free?',
                'a' => 'The registration list itself is free forever, including a capacity limit and the count of places left on each date. The custom questions on that form are part of the Pro plan at '.plan_price($proMonthly).' a month, which comes with a 7 day free trial. Event Schedule charges zero platform fees on ticket sales.',
            ],
            [
                'q' => 'How many fields can I have?',
                'a' => 'Ten on the schedule, ten on an event and ten on each ticket type. Drag them to change the order they are asked in.',
            ],
            [
                'q' => 'Can I export the answers?',
                'a' => 'Yes. The sales CSV export adds one column for every custom field name it finds, filled with what each buyer answered. The answers also appear in your sales table and on the ticket itself.',
            ],
            [
                'q' => 'Can I collect an answer without publishing it?',
                'a' => 'Yes. Mark the field private and the value stays off your public schedule while remaining visible to you in the admin portal. A private field can still be dropped into an event graphic or a URL pattern with {custom_N}.',
            ],
            [
                'q' => 'What if my schedule is not in English?',
                'a' => 'Each field can carry a second name for your schedule\'s other language. Leave it blank and it is filled in for you, along with your dropdown choices, so a visitor reads the question in the language they came for.',
            ],
        ];

        $dotSections = [
            ['top', 'The form'],
            ['places', 'Three places'],
            ['types', 'Six types'],
            ['switches', 'Four switches'],
            ['free', 'Free list, Pro questions'],
            ['column', 'Question and column'],
            ['requests', 'On the request form'],
            ['slots', 'Ten slots'],
            ['rest', 'Everything else'],
            ['faq', 'Questions'],
            ['claim', 'Start asking'],
        ];
    @endphp

    <div id="es-form-page" class="es-form-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one field definition, and what it renders as        -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(154, 52, 18, 0.2), rgba(154, 52, 18, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(253, 186, 116, 0.14), rgba(253, 186, 116, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h13" />
                        </svg>
                        <span class="es-form-muted text-sm font-medium tracking-wide">For anyone who needs more than a name and an email</span>
                    </div>

                    <h1 class="es-balance es-form-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Ask the question.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Keep the <span class="es-form-fill">answer.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-form-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A custom field is one definition. Event Schedule renders it as a question on the forms it belongs on, and files the answer as a column you can read, print and export.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#places" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            Where a field lives
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-form-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create a schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The definition slip, and the control it becomes. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-form-card p-6 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-form-ink text-lg font-bold">One field, defined once</h2>
                            <span class="es-form-key">{custom_3}</span>
                        </div>

                        <dl class="es-form-spec">
                            <div class="es-form-line" style="--i: 0;">
                                <dt>Field name</dt>
                                <dd>Dietary requirements</dd>
                            </div>
                            <div class="es-form-line" style="--i: 1;">
                                <dt>Type</dt>
                                <dd>Dropdown</dd>
                            </div>
                            <div class="es-form-line" style="--i: 2;">
                                <dt>Choices</dt>
                                <dd>No preference, Vegetarian, Vegan, Gluten free</dd>
                            </div>
                            <div class="es-form-line" style="--i: 3;">
                                <dt>Switches</dt>
                                <dd class="flex flex-wrap gap-2">
                                    <span class="es-form-switch es-form-switch-on">Required</span>
                                    <span class="es-form-switch es-form-switch-on">On request form</span>
                                    <span class="es-form-switch">Private</span>
                                </dd>
                            </div>
                        </dl>

                        <p class="es-form-tag mb-3 mt-6">Renders as</p>
                        <div class="es-form-well p-4" aria-hidden="true">
                            <p class="es-form-tag mb-2">Dietary requirements *</p>
                            <div class="es-form-ctl es-form-ctl-on">
                                <span>Vegetarian</span>
                                <svg aria-hidden="true" class="h-4 w-4 self-center" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <p class="es-form-tag mb-2 mt-5">Access needs</p>
                            <div class="es-form-ctl">
                                <span>Optional</span>
                            </div>
                        </div>

                        <p class="es-form-muted mt-5 text-xs">
                            This one lives on the schedule, so it is asked on your own event form and on your public event request form. Write a field on an event or a ticket type instead and it is asked at checkout.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Questions people actually ask -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Dietary requirements', 'T-shirt size', 'Access needs', 'Company name', 'Emergency contact', 'Date of birth', 'Equipment needed', 'Reference number', 'How did you hear about us?', 'Expected head count'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-form-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Three places a field can live                             -->
    <!-- ============================================================ -->
    <section id="places" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_1}</span></div>
                <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Three places</p>
                <h2 class="es-balance es-form-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A field belongs to a schedule, an event, or <span class="es-form-fill">a ticket.</span>
                </h2>
                <p class="es-form-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Which one you pick decides who is asked and when. All three are on the Pro plan, and all three take up to ten fields.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                @foreach ($homes as [$homeName, $homeWhere, $homeBody, $homeSwitches, $homeWho])
                    <div class="es-form-card flex h-full flex-col p-7" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-form-ink text-lg font-bold">{{ $homeName }}</h3>
                            <span class="es-form-plan es-form-plan-pro">Pro</span>
                        </div>
                        <p class="es-form-key mb-4">{{ $homeWhere }}</p>
                        <p class="es-form-muted mb-6 text-sm">{{ $homeBody }}</p>
                        <div class="es-form-well mt-auto p-4">
                            <dl class="es-form-spec es-form-spec-tight">
                                <div>
                                    <dt>Answered by</dt>
                                    <dd>{{ $homeWho }}</dd>
                                </div>
                                <div>
                                    <dt>Switches</dt>
                                    <dd>{{ $homeSwitches }}</dd>
                                </div>
                            </dl>
                            <p class="es-form-muted mt-3 text-xs">All six field types, in any combination.</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <p class="es-form-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Worth being precise about: the extra switches live on schedule fields, along with an instruction for the AI importer. A checkout field carries a name, a type, its choices and whether it is required.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The six types: a record, so a table                       -->
    <!-- ============================================================ -->
    <section id="types" class="scroll-mt-24 es-form-hr-y py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_2}</span></div>
                <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Six types</p>
                <h2 class="es-balance es-form-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Six types. <span class="es-form-fill">No more, no fewer.</span>
                </h2>
                <p class="es-form-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Here is the whole list, what the person filling the form actually gets, and where the choices come from.
                </p>
            </div>

            <div class="es-form-card p-6 sm:p-8" data-reveal="panel">
                <div class="overflow-x-auto">
                    <table class="es-form-table">
                        <caption class="sr-only">The six custom field types, what each renders as, where its choices come from, and whether a validation pattern applies</caption>
                        <thead>
                            <tr>
                                <th scope="col">Type</th>
                                <th scope="col">What they get</th>
                                <th scope="col">Choices</th>
                                <th scope="col">Pattern</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($types as [$typeName, $typeKey, $typeRenders, $typeChoices, $typePattern])
                                <tr>
                                    <th scope="row">
                                        {{ $typeName }}
                                        <span class="es-form-key block font-normal">{{ $typeKey }}</span>
                                    </th>
                                    <td>{{ $typeRenders }}</td>
                                    <td>{{ $typeChoices }}</td>
                                    <td>{{ $typePattern }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="es-form-muted mt-5 text-xs">
                    A long text field is checked against its pattern on the server only, because a multi-line box has nowhere to hang a browser pattern. Everything else that can be pattern checked is checked in both places.
                </p>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2" data-reveal-group="90">
                <div class="es-form-card flex h-full flex-col p-6" data-reveal="panel">
                    <p class="es-form-tag mb-3">A dropdown</p>
                    <div class="es-form-well p-4" aria-hidden="true">
                        <p class="es-form-tag mb-2">T-shirt size *</p>
                        <div class="es-form-ctl es-form-ctl-on mb-5">
                            <span>Large</span>
                            <svg aria-hidden="true" class="h-4 w-4 self-center" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                        <p class="es-form-tag mb-2">Experience level *</p>
                        <div class="es-form-ctl">
                            <span>Select...</span>
                            <svg aria-hidden="true" class="h-4 w-4 self-center" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>
                    <p class="es-form-muted mt-auto pt-4 text-sm">You type <span class="es-form-key">Small, Medium, Large, XL</span> once and every buyer picks from exactly those. A required one will not let the form through unanswered.</p>
                </div>
                <div class="es-form-card flex h-full flex-col p-6" data-reveal="panel">
                    <p class="es-form-tag mb-3">A multi-select</p>
                    <div class="es-form-well p-4" aria-hidden="true">
                        <p class="es-form-tag mb-3">Equipment needed</p>
                        <div class="flex flex-col items-start gap-2">
                            <span class="es-form-switch es-form-switch-on">Projector</span>
                            <span class="es-form-switch">PA system</span>
                            <span class="es-form-switch es-form-switch-on">Two mics</span>
                            <span class="es-form-switch">Piano</span>
                        </div>
                    </div>
                    <p class="es-form-muted mt-auto pt-4 text-sm">The same comma separated list, ticked any number of times. This is how a request form becomes a checklist.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Four switches per field                                   -->
    <!-- ============================================================ -->
    <section id="switches" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_3}</span></div>
                    <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Four switches</p>
                    <h2 class="es-balance es-form-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        A question is not just <span class="es-form-fill">its wording.</span>
                    </h2>
                    <p class="es-form-muted mb-8 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Each field on your schedule carries four switches. They are what turn a box on a form into something you can rely on when the answers come back.
                    </p>

                    <div class="space-y-4" data-reveal-group="80">
                        @foreach ($flags as [$flagName, $flagState, $flagBody])
                            <div class="es-form-card p-6" data-reveal="panel">
                                <div class="mb-2 flex flex-wrap items-center gap-3">
                                    <span class="es-form-switch @if ($flagState === 'on') es-form-switch-on @endif">{{ $flagName }}</span>
                                    <span class="es-form-plan es-form-plan-pro">Pro</span>
                                </div>
                                <p class="es-form-muted text-sm leading-relaxed">{{ $flagBody }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div data-reveal="panel">
                    <div class="es-form-card p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-form-ink text-lg font-bold">A pattern, and a hint</h3>
                            <span class="es-form-key">string</span>
                        </div>

                        <div class="es-form-well p-4" aria-hidden="true">
                            <p class="es-form-tag mb-2">Preset</p>
                            <div class="es-form-ctl es-form-ctl-on mb-5">
                                <span>Phone number</span>
                                <svg aria-hidden="true" class="h-4 w-4 self-center" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <p class="es-form-tag mb-2">Pattern</p>
                            <div class="es-form-ctl es-form-ctl-on mb-5">
                                <span class="es-form-key">\+?[0-9 ()\-]{6,20}</span>
                            </div>
                            <p class="es-form-tag mb-2">Test a value</p>
                            <div class="es-form-ctl es-form-ctl-on">
                                <span>+44 20 7946 0018</span>
                                <svg aria-hidden="true" class="h-4 w-4 self-center es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                        </div>

                        <ul class="mt-6 space-y-3" data-reveal-group="70">
                            <li class="es-form-muted flex gap-3 text-sm" data-reveal>
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Five ready-made presets: email address, phone number, web address, numbers only, letters and numbers.</span>
                            </li>
                            <li class="es-form-muted flex gap-3 text-sm" data-reveal>
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Or write your own, and try a sample value against it before you save.</span>
                            </li>
                            <li class="es-form-muted flex gap-3 text-sm" data-reveal>
                                <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Your hint prints under the field, so nobody has to guess what format you meant.</span>
                            </li>
                        </ul>

                        <p class="es-form-muted mt-5 text-xs">
                            Drag the fields into the order you want them asked in. The order you set is the order on every form.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Free list, Pro questions (fixed-dark band)                -->
    <!-- ============================================================ -->
    <section id="free" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-form-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 26%, rgba(253, 186, 116, 0.12), rgba(253, 186, 116, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
                <div class="es-form-blanks absolute bottom-6 left-0 right-0 mx-auto h-10 max-w-4xl px-8 opacity-60" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($bk = 0; $bk < 18; $bk++)
                        <span class="es-form-blank @if ($bk % 3 === 0) es-form-blank-on @endif" style="--bk-dur: {{ 2.8 + ($bk % 5) * 0.34 }}s; --bk-delay: {{ ($bk % 9) * 0.22 }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_4}</span></div>
                    <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The honest line</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The list is free. <span class="es-form-fill">The questions are Pro.</span>
                    </h2>
                    <p class="es-form-muted mx-auto mt-5 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        We would rather you read that here than find it in the product. Here is exactly where the line falls.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-form-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-form-ink text-lg font-bold">Taking names</h3>
                            <span class="es-form-plan">Free</span>
                        </div>
                        <p class="es-form-muted text-sm leading-relaxed">Turn on registration and people sign up with their name and email. Set a capacity and each date shows how many places are left. Free forever, on every date of a recurring event.</p>
                    </div>
                    <div class="es-form-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-form-ink text-lg font-bold">Asking anything else</h3>
                            <span class="es-form-plan es-form-plan-pro">Pro</span>
                        </div>
                        <p class="es-form-muted text-sm leading-relaxed">Custom questions on that same form are Pro, at {{ plan_price($proMonthly) }} a month with a 7 day free trial. The form does not change shape, it just starts asking what you need.</p>
                    </div>
                    <div class="es-form-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-form-ink text-lg font-bold">Taking money</h3>
                            <span class="es-form-plan">Free</span>
                        </div>
                        <p class="es-form-muted text-sm leading-relaxed">Selling starts free: 25 paid tickets a month, through your own Stripe account, and scanning those tickets at the door is free too. Pro lifts that ceiling and adds the live check-in dashboard. Event Schedule charges zero platform fees on every plan, so past Stripe's own processing the money is yours.</p>
                    </div>
                </div>

                <p class="es-form-muted mt-10 text-center" data-reveal>
                    Nothing here needs a plugin, an integration or a second form tool.
                    <a href="{{ marketing_url('/pricing') }}" class="es-form-link inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        See the plans
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The question and the column (duplex)                      -->
    <!-- ============================================================ -->
    <section id="column" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_5}</span></div>
                <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Question and column</p>
                <h2 class="es-balance es-form-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One side asks. <span class="es-form-fill">The other side files.</span>
                </h2>
                <p class="es-form-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A field is only worth defining if the answer turns up somewhere useful. Answers turn up in four places, and one of them is a spreadsheet.
                </p>
            </div>

            <div class="grid gap-10 md:grid-cols-2" data-reveal-group="90">
                <div data-reveal="panel">
                    <p class="es-form-tag mb-4">The question you ask</p>
                    <div class="es-form-card p-6 sm:p-7">
                        <div class="es-form-well p-5" aria-hidden="true">
                            <p class="es-form-tag mb-3">Checkout</p>
                            <p class="es-form-tag mb-2">Name *</p>
                            <div class="es-form-ctl es-form-ctl-on mb-5"><span>Dana Ruiz</span></div>
                            <p class="es-form-tag mb-2">Dietary requirements *</p>
                            <div class="es-form-ctl es-form-ctl-on mb-5">
                                <span>Vegetarian</span>
                                <svg aria-hidden="true" class="h-4 w-4 self-center" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                            <p class="es-form-tag mb-2">Access needs</p>
                            <div class="es-form-ctl es-form-ctl-on"><span>Step-free access</span></div>
                        </div>
                        <p class="es-form-muted mt-5 text-sm">
                            Two extra questions on a form somebody was already filling in. Required ones will not let the order through empty.
                        </p>
                    </div>
                </div>

                <div class="es-form-divide" data-reveal="panel">
                    <p class="es-form-tag mb-4">The column you get</p>
                    <div class="es-form-card p-6 sm:p-7">
                        <div class="overflow-x-auto">
                            <table class="es-form-csv">
                                <caption class="sr-only">An extract of the sales CSV export, with one column added for each custom field</caption>
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Tickets</th>
                                        <th scope="col" class="es-form-csv-new">Dietary requirements</th>
                                        <th scope="col" class="es-form-csv-new">Access needs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Dana Ruiz</td>
                                        <td>Standard x2</td>
                                        <td class="es-form-csv-new">Vegetarian</td>
                                        <td class="es-form-csv-new">Step-free access</td>
                                    </tr>
                                    <tr>
                                        <td>Priya Anand</td>
                                        <td>Standard x1</td>
                                        <td class="es-form-csv-new">Gluten free</td>
                                        <td class="es-form-csv-new"></td>
                                    </tr>
                                    <tr>
                                        <td>Tom Okafor</td>
                                        <td>Concession x1</td>
                                        <td class="es-form-csv-new">No preference</td>
                                        <td class="es-form-csv-new">Sign language</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="es-form-muted mt-5 text-sm">
                            The sales export grows one column per field name. That is the whole trick: the caterer gets a count instead of a conversation.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                @foreach ($landings as [$landName, $landBody])
                    <div class="es-form-card flex h-full flex-col p-6" data-reveal="panel">
                        <h3 class="es-form-ink mb-2 text-base font-bold">{{ $landName }}</h3>
                        <p class="es-form-muted text-sm leading-relaxed">{{ $landBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. On the request form                                       -->
    <!-- ============================================================ -->
    <section id="requests" class="scroll-mt-24 es-form-hr-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_6}</span></div>
                    <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">On the request form</p>
                    <h2 class="es-balance es-form-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Ask before you <span class="es-form-fill">say yes.</span>
                    </h2>
                    <p class="es-form-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        If you take event requests from other people, every schedule field is also a question on that public form, on by default. So the things you always end up emailing about are answered before the request reaches you.
                    </p>
                    <ul class="space-y-3" data-reveal-group="70">
                        <li class="es-form-muted flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A multi-select becomes a kit checklist: which of your equipment they need, ticked.</span>
                        </li>
                        <li class="es-form-muted flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A pattern forces a reference number into the shape your own records use.</span>
                        </li>
                        <li class="es-form-muted flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Answers land on the request card in your admin portal, and stay with the event once you accept it.</span>
                        </li>
                        <li class="es-form-muted flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none es-form-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Uncheck one switch to keep a field for your own use and off the public form entirely.</span>
                        </li>
                    </ul>
                    <p class="es-form-muted mt-7 text-sm">
                        <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="es-form-link font-medium hover:underline">Read the Custom Fields guide</a>
                        for the field-by-field reference.
                    </p>
                </div>

                <div data-reveal="panel">
                    <div class="es-form-card p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-form-ink text-lg font-bold">Request from a visitor</h3>
                            <span class="es-form-key">Additional information</span>
                        </div>
                        <div class="es-form-well p-5" aria-hidden="true">
                            <p class="es-form-tag mb-2">Expected head count *</p>
                            <div class="es-form-ctl es-form-ctl-on mb-5"><span>120</span></div>
                            <p class="es-form-tag mb-3">Equipment needed</p>
                            <div class="mb-5 flex flex-col items-start gap-2">
                                <span class="es-form-switch es-form-switch-on">Projector</span>
                                <span class="es-form-switch">PA system</span>
                                <span class="es-form-switch es-form-switch-on">Two mics</span>
                            </div>
                            <p class="es-form-tag mb-2">Booking reference *</p>
                            <div class="es-form-ctl es-form-ctl-on mb-1"><span class="es-form-key">RQ-4417</span></div>
                            <p class="es-form-muted text-xs">Two letters, a hyphen and four digits.</p>
                        </div>
                        <p class="es-form-muted mt-5 text-xs">
                            The hint under the last field is yours to write. It is what stops the same correction happening twenty times.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Ten slots                                                 -->
    <!-- ============================================================ -->
    <section id="slots" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_7}</span></div>
                <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Ten slots</p>
                <h2 class="es-balance es-form-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Every field keeps a <span class="es-form-fill">numbered slot.</span>
                </h2>
                <p class="es-form-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A schedule field holds a stable slot from one to ten, and the slot is a variable you can use. The number in the margin of each section on this page is one of them.
                </p>
            </div>

            <div class="es-form-card p-6 sm:p-8" data-reveal="panel">
                <div class="es-form-reg" aria-hidden="true">
                    @foreach ([['Speaker', true], ['Topic', true], ['Dietary', true], ['Level', true], ['Kit list', true], ['Reference', true], ['Head count', true], ['', false], ['', false], ['', false]] as $slotIndex => [$slotLabel, $slotOn])
                        <span class="es-form-reg-slot @if ($slotOn) es-form-reg-slot-on @endif">
                            <span>{custom_{{ $slotIndex + 1 }}}</span>
                            <span class="text-[0.7rem] font-semibold">{{ $slotLabel !== '' ? $slotLabel : 'empty' }}</span>
                        </span>
                    @endforeach
                </div>
                <p class="es-form-muted mt-5 text-sm">Seven of ten slots in use on this schedule. The numbers do not shuffle when you reorder the fields.</p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3" data-reveal-group="90">
                    <div class="es-form-well p-5" data-reveal>
                        <h3 class="es-form-ink mb-2 text-base font-bold">In a graphic</h3>
                        <p class="es-form-muted text-sm leading-relaxed">Drop <span class="es-form-key">{custom_1}</span> into an event graphic template and the value prints on the artwork with the title and the date.</p>
                    </div>
                    <div class="es-form-well p-5" data-reveal>
                        <h3 class="es-form-ink mb-2 text-base font-bold">In a URL</h3>
                        <p class="es-form-muted text-sm leading-relaxed">The same variable works in your event URL pattern, so a value you already collect can shape the address.</p>
                    </div>
                    <div class="es-form-well p-5" data-reveal>
                        <h3 class="es-form-ink mb-2 text-base font-bold">As a filter</h3>
                        <p class="es-form-muted text-sm leading-relaxed">A public dropdown or multi-select becomes a filter on your guest calendar. Mark it private and the filter goes away with it.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 es-form-hr py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_8}</span></div>
                <p class="es-form-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-form-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the paperwork.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-form-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-1 flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-form-ink text-xl font-bold">Let the import fill it in</h3>
                                <span class="es-form-plan es-form-plan-pro">Pro</span>
                            </div>
                            <p class="es-form-muted mb-4">Every schedule field can carry an instruction for the importer: what this field means and where to look for it. Paste a flyer or a listing and the value arrives already in the box.</p>
                            <p class="es-form-muted mt-auto text-sm">Event import runs on every plan with a daily allowance, 10 a day on Free and 50 on Pro. The custom fields it fills are Pro.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-form-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-form-ink text-xl font-bold">In two languages</h3>
                                <span class="es-form-plan es-form-plan-pro">Pro</span>
                            </div>
                            <p class="es-form-muted">A field can hold a second name for your schedule's other language. Leave it blank and it is filled in for you, dropdown choices included.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-form-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-form-ink text-xl font-bold">On the door</h3>
                                <span class="es-form-plan es-form-plan-pro">Pro</span>
                            </div>
                            <p class="es-form-muted">The answers a buyer gave are printed on their ticket, so whoever is scanning reads the same course choice or access note that you do.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-form-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10 flex flex-1 flex-col">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-form-ink text-xl font-bold">One person, or all six</h3>
                                <span class="es-form-plan es-form-plan-pro">Pro</span>
                            </div>
                            <p class="es-form-muted mb-4">By default one buyer answers once for the whole order. Turn on individual tickets and then per guest fields, and each person in the party gets their own name, their own QR code and their own answers, which is the difference between a dietary count you can cook to and a note saying "two of us are vegetarian".</p>
                            <p class="es-form-muted mt-auto text-sm">
                                Ticket types, sales windows and QR check-in all sit alongside this.
                                <a href="{{ marketing_url('/features/ticketing') }}" class="es-form-link font-medium hover:underline">How ticketing works</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-form-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-form-ink text-xl font-bold">Bring a list with you</h3>
                                <span class="es-form-plan es-form-plan-pro">Pro</span>
                            </div>
                            <p class="es-form-muted">Importing attendees you already sold elsewhere? The importer accepts their answers to your ticket fields too, so the spreadsheet you have lands complete.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-form-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-form-ink text-xl font-bold">What custom fields are not</h3>
                            </div>
                            <p class="es-form-muted mb-4">They are questions and answers, not a workflow engine. A field cannot branch to another field, cannot price an order differently, and cannot hold a file upload. What it can do is ask a clear question everywhere it belongs and hand you the answer in a column.</p>
                            <p class="es-form-muted text-sm">If that is what you needed a separate form tool for, this replaces it. If you needed conditional logic, it does not.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Related features                                         -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-form-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Named ticket types, QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="orange">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Event Graphics" description="Print a field's value onto the artwork with {custom_1}" :url="marketing_url('/features/event-graphics')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put your schedule, and its filters, on your own site" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Team Scheduling" description="Enterprise: bring up to five people into one schedule" :url="marketing_url('/features/team-scheduling')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-form-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <h2 class="es-form-ink mb-6 mt-16 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Popular with</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" data-reveal-group="70">
                @foreach ([['/for-venues', 'Venues', 'Ask a visiting act what kit they need before you accept the date.'], ['/for-hotels-and-resorts', 'Hotels and Resorts', 'Take an arrival time or a dietary note with the booking.'], ['/for-fitness-and-yoga', 'Fitness and Yoga', 'Collect an injury note or an experience level with the sign-up.']] as [$popHref, $popName, $popBlurb])
                    <a href="{{ marketing_url($popHref) }}" class="es-form-hover es-form-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-form-hover-title es-form-ink mb-2 text-sm font-semibold transition-colors">For {{ $popName }}</span>
                        <span class="es-form-muted mb-4 text-xs leading-relaxed">{{ $popBlurb }}</span>
                        <span class="es-form-hover-arrow es-form-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 es-form-hr py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-form-slot mb-6" data-reveal aria-hidden="true"><span>{custom_9}</span></div>
                <h2 class="es-balance es-form-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-form-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything worth knowing before you start writing fields.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-form-hover es-form-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-form-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-form-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-form-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-form-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-form-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-form-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-form-blanks absolute bottom-6 left-0 right-0 mx-auto h-10 max-w-3xl px-8 opacity-50" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($fb = 0; $fb < 14; $fb++)
                            <span class="es-form-blank @if ($fb % 3 === 1) es-form-blank-on @endif" style="--bk-dur: {{ 3 + ($fb % 5) * 0.3 }}s; --bk-delay: {{ ($fb % 7) * 0.24 }}s;"></span>
                        @endfor
                    </div>
                </div>
                <div class="relative z-10">
                    <div class="es-form-slot mb-6" aria-hidden="true"><span>{custom_10}</span></div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Stop chasing answers. <span class="es-form-fill">Ask on the form.</span>
                    </h2>
                    <p class="es-form-muted mx-auto mb-10 max-w-2xl text-lg">
                        Registration and capacity are free forever. Custom questions come with Pro at {{ plan_price($proMonthly) }} a month, with a 7 day free trial and no platform fees on anything you sell.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-form-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your schedule
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-form-muted mt-6 text-sm">No credit card required to start</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full es-form-tip px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
