<x-marketing-layout>
    <x-slot name="title">Privacy Policy - Event Schedule</x-slot>
    <x-slot name="description">Privacy Policy for Event Schedule - how we collect, use, and protect your data, who can access it, the cookies we set, and how to have your data erased.</x-slot>
    <x-slot name="breadcrumbTitle">Privacy Policy</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Privacy Policy - Event Schedule",
        "description": "Privacy Policy for Event Schedule - how we collect, use, and protect your data, who can access it, the cookies we set, and how to have your data erased.",
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "Privacy Policy"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: the only motion on this page is the masthead's shared
         .es-fade-up, which is pure CSS and rests in its finished state when this
         class is absent. Nothing here is [data-reveal], so no-JS visitors,
         crawlers and reduced-motion users read the whole document at rest. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Privacy "The Fine Print" styles.

           CONCEPT: THE INSTRUMENT. A privacy policy is not a landing page.
           The words ARE the product here. They were rewritten on 2026-10-04
           to describe what the code does (the $doc comment below), and every
           change to them is a change of policy, made in the same commit as
           the code it describes. The apparatus around the words lays the
           page out as a legal instrument: a masthead register, roman-numeral
           parts, hanging clause numbers on a continuous margin rule, a
           standing contents rail, and schedules where the content is a list.

           THE ARGUMENT IS THE FORM. The product's privacy position is that
           everything is enumerable: a named list of processors, a named list
           of browser identifiers, a numbered erasure procedure that ends in
           a permanent delete. A page that can print those as schedules is
           making the claim by being able to make it. So the devices are:

             1. THE MARGIN RULE - a continuous hairline down the left of the
                text block with a monospace clause number hanging in the
                gutter. It is what makes the clauses a document
                rather than a scroll. The number is an ANCHOR, not an
                ornament, carrying its own aria-label ("Clause 8,
                Restriction/Erasure...") so the citation the masthead
                promises is real and reaches assistive tech. /terms does the
                same; if you demote it back to a decorative <p>, delete the
                masthead sentence about citing a single line.
             2. THE DOCKET - a masthead register of cells (scope,
                erasure, privacy contact) under a single 2px rule, the way a
                statute states its own extent before its first section. Each
                cell ends in a cross-reference to the clause that governs it
                (&sect; 01 / &sect; 08 / &sect; 16), so the masthead
                demonstrates the citation form rather than describing it, and
                the register carries the document's strongest promise instead
                of restating its own table of contents. NOT a printer's
                double rule: /about, /browse and /faq own that, and the
                sibling /terms header records avoiding it. Treat that as
                binding.
                The fourth cell is the date the policy last changed, which
                is a fact about the document: $lastUpdated in the php block
                below, moved only in a commit that changes a clause.
             3. THE CONTENTS RAIL - a standing index, the roman-numeral
                parts over their clauses ($partCount and $clauseCount below), sticky on desktop and printed at
                the head of the document on mobile.
             4. SCHEDULES - the third-party processors become a real
                <table> (vendor / purpose, split on the dash the source
                already used), the erasure steps a numbered procedure, and
                the browser identifiers a scannable register of the exact
                strings named in that clause.

           TYPOGRAPHY IS THE APPARATUS, not the differentiator. Two system
           stacks: the inherited sans at a 64ch measure for the instrument,
           and monospace for every identifier, clause number and docket
           label. A serif document face was built and then REMOVED, because
           all three sibling legal pages measure Inter for h1, h2 and body -
           see the note on the stacks below. No aurora, no rays, no gradient
           display type, no dark band, no hero art, no reveal animation.
           Legibility is the whole win.

           NOT A FIXED PHYSICAL OBJECT. The sheet is a document, not a piece
           of paper: someone reads this for minutes, so dark mode gets a dark
           document rather than a lit rectangle. Both modes are designed and
           there is nothing to pin with --bands.

           COLOUR: the page's existing blue family, kept. But it is demoted
           from a three-stop display gradient to ONE ink used functionally -
           clause numbers, links, the index marker, the focus ring. The
           shared brand->sky->cyan chrome gradient is deliberately not
           adopted as a page accent.

           THE SET IS THE POINT, so every token here was taken from the three
           sibling legal pages already on disk rather than invented: ground
           #f5f5f2 / #0b0c0f and sheet #ffffff / #12141a and muted #4b5158 /
           #9aa1ab from /self-hosting-terms, accent #1d4ed8 (which
           /accessibility and /self-hosting-terms both use) with #93c5fd at
           night, ink #16181c / #e9ebef, body #24272c / #d9dce2, the 0.9rem
           radius, the 1.0625rem body size, the 64ch measure /terms fixed,
           the pill-and-shield eyebrow, and the class names themselves
           (es-fine-page / -sheet / -tag / -docket / -index-num / -measure /
           -table / -scroll / -num / -link / -muted). If you re-ink one of
           the four, re-ink all four.

           Measured with the campaign probe:
             light  ink #16181c 17.77 on sheet #ffffff, 16.27 on ground
                    #f5f5f2, 16.03 on the chip tint #f3f3f4, 15.74 on the
                    code tint #f1f1f1; body #24272c 14.98 / 14.11 on the
                    aside #f8f8f8; muted #4b5158 8.02 / 7.35 / 7.56 / 7.30
                    on the accent tint #f1f4fd; accent #1d4ed8 6.70 / 6.14 /
                    6.09 / 6.31; white on a #1d4ed8 fill 6.70.
             dark   ink #e9ebef 15.42 on sheet #12141a, 16.39 on ground
                    #0b0c0f, 13.48 on chip #1f2127, 12.83 on code #23252b;
                    body #d9dce2 13.40 / 12.41; muted #9aa1ab 7.07 / 7.51 /
                    6.54 / 5.98; accent #93c5fd 10.21 / 10.85 / 8.65 / 9.45.
           NEVER text-gray-500 or dark:text-gray-500 here - the grounds are
           tinted and both measure below AA on them.

           The four legal pages (privacy, terms, accessibility,
           self-hosting-terms) share this one restrained family under this
           nickname and the es-fine- prefix, on purpose: legal pages must
           look like a set. Do not give one of them its own motif.
           ============================================================== */

        /* --- Ground, stock and ink ---------------------------------- */
        .es-fine-page { background-color: #f5f5f2; color: #16181c; }
        .dark .es-fine-page { background-color: #0b0c0f; color: #e9ebef; }

        .es-fine-muted { color: #4b5158; }
        .dark .es-fine-muted { color: #9aa1ab; }

        /* The sheet the instrument is set on. Its vertical padding is set
           here rather than with sm:pb-12, which is not in the built Tailwind
           bundle and would have silently done nothing. */
        .es-fine-sheet {
            background-color: #ffffff;
            border: 1px solid rgba(22, 24, 28, 0.12);
            border-radius: 0.9rem;
            padding-top: 0.25rem;
            padding-bottom: 2.5rem;
        }
        @media (min-width: 640px) {
            .es-fine-sheet { padding-bottom: 3rem; }
        }
        .dark .es-fine-sheet {
            background-color: #12141a;
            border-color: rgba(233, 235, 239, 0.12);
        }

        /* --- The two stacks ----------------------------------------
           The document face is the inherited sans, because the three
           sibling legal pages all measure Inter for h1, h2 AND body: a
           serif document here would have been the one page in the set with
           a different face, which is exactly the "separate motif" the set
           is meant to avoid. Distinctiveness on this page comes from
           structure (margin rule, parts, index, schedules), not the face.
           Monospace is the family's identifier voice, applied by the classes
           that carry an identifier: -tag, -lede, -num, -cite, -chip, -code,
           -part-num, -index-num and the erasure step markers. */

        /* --- Masthead ----------------------------------------------- */
        /* Grid paper, faded out before it reaches the docket. .grid-pattern
           carries its own .dark rule in marketing.css, which is correct here
           because the masthead is mode-adaptive rather than a pinned object. */
        .es-fine-grid {
            opacity: 0.7;
            background-size: 46px 46px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.9), transparent 78%);
            -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.9), transparent 78%);
        }
        .es-fine-masthead {
            border-bottom: 2px solid rgba(22, 24, 28, 0.22);
            padding-bottom: 1.75rem;
        }
        .dark .es-fine-masthead { border-bottom-color: rgba(233, 235, 239, 0.24); }

        /* The family eyebrow: a pill with a shield, as on /terms and
           /self-hosting-terms. The first-wave privacy page had this pill too,
           so restoring it is faithful in both directions. */
        .es-fine-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border: 1px solid rgba(22, 24, 28, 0.14);
            border-radius: 999px;
            background-color: rgba(22, 24, 28, 0.03);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-tag {
            border-color: rgba(233, 235, 239, 0.14);
            background-color: rgba(233, 235, 239, 0.04);
            color: #9aa1ab;
        }

        .es-fine-title {
            font-size: clamp(2.25rem, 5vw, 3rem);
            line-height: 1.05;
            letter-spacing: -0.02em;
            font-weight: 900;
            color: #16181c;
        }
        .dark .es-fine-title { color: #e9ebef; }
        .es-fine-accent { color: #1d4ed8; }
        .dark .es-fine-accent { color: #93c5fd; }

        /* Mono, exactly as /terms sets the same line. */
        .es-fine-lede {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.9rem;
            color: #4b5158;
        }
        .dark .es-fine-lede { color: #9aa1ab; }
        .es-fine-intro { font-size: 1.0625rem; line-height: 1.7; color: #4b5158; max-width: 46ch; }
        .dark .es-fine-intro { color: #9aa1ab; }

        /* The register: scope, contact, structure. */
        .es-fine-docket {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem 2.5rem;
        }
        .es-fine-docket-key {
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5158;
            margin-bottom: 0.2rem;
        }
        .dark .es-fine-docket-key { color: #9aa1ab; }
        .es-fine-docket-val { font-size: 0.9rem; line-height: 1.5; color: #16181c; }
        .dark .es-fine-docket-val { color: #e9ebef; }

        /* --- Shell: contents rail beside the instrument -------------- */
        .es-fine-shell { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2.25rem; }

        .es-fine-index-title {
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-index-title { color: #9aa1ab; }

        .es-fine-index-part {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #16181c;
            margin: 1.1rem 0 0.4rem;
            padding-bottom: 0.35rem;
            border-bottom: 1px solid rgba(22, 24, 28, 0.1);
        }
        .dark .es-fine-index-part { color: #e9ebef; border-bottom-color: rgba(233, 235, 239, 0.1); }

        .es-fine-index-link {
            display: grid;
            grid-template-columns: 1.7rem minmax(0, 1fr);
            gap: 0.4rem;
            padding: 0.28rem 0.45rem;
            border-radius: 0.25rem;
            border-inline-start: 2px solid transparent;
            font-size: 0.82rem;
            line-height: 1.35;
            color: #4b5158;
            transition: color 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
        }
        .dark .es-fine-index-link { color: #9aa1ab; }
        .es-fine-index-link:hover {
            color: #1d4ed8;
            background-color: rgba(29, 78, 216, 0.07);
            border-inline-start-color: #1d4ed8;
        }
        .dark .es-fine-index-link:hover {
            color: #93c5fd;
            background-color: rgba(147, 197, 253, 0.1);
            border-inline-start-color: #93c5fd;
        }
        .es-fine-index-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
            color: #1d4ed8;
        }
        .dark .es-fine-index-num { color: #93c5fd; }

        /* --- Part divider inside the instrument ---------------------- */
        .es-fine-part {
            display: flex;
            align-items: baseline;
            gap: 0.7rem;
            padding-top: 2.75rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-part { color: #9aa1ab; }
        .es-fine-part-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #1d4ed8;
        }
        .dark .es-fine-part-num { color: #93c5fd; }
        .es-fine-part-rule {
            flex: 1 1 auto;
            height: 1px;
            background-color: rgba(22, 24, 28, 0.14);
        }
        .dark .es-fine-part-rule { background-color: rgba(233, 235, 239, 0.14); }

        /* --- Clause: number in the gutter, text on the margin rule --- */
        .es-fine-clause {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0.3rem;
            scroll-margin-top: 6rem;
        }
        /* The clause number is a LINK, not an ornament, because the masthead
           claims a single line of this policy can be cited on its own. It
           carries its own aria-label, so it reads as "Clause 8, Restriction /
           Erasure" rather than "section zero eight". /terms does the same. */
        .es-fine-num {
            display: block;
            width: fit-content;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #1d4ed8;
            padding-top: 1.6rem;
            text-decoration: none;
            transition: color 0.18s ease;
        }
        .dark .es-fine-num { color: #93c5fd; }
        .es-fine-num:hover {
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 3px;
        }

        /* A cross-reference in the docket: the same citation form, used to
           point a register cell at the clause that governs it. */
        .es-fine-cite {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.7rem;
            font-weight: 700;
            white-space: nowrap;
            color: #1d4ed8;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 2px;
        }
        .dark .es-fine-cite { color: #93c5fd; }
        .es-fine-cite:hover { text-decoration-thickness: 2px; }
        .es-fine-clause-body { padding: 1.5rem 0 0.25rem; }

        .es-fine-h {
            font-size: 1.3rem;
            line-height: 1.3;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: #16181c;
        }
        .dark .es-fine-h { color: #e9ebef; }

        /* --- Body of the instrument: the comfortable measure ---------
           64ch, the value /terms fixed for the same sans at the same size.
           Do not widen it. */
        .es-fine-measure { max-width: 64ch; }
        .es-fine-measure p {
            margin-top: 0.95rem;
            font-size: 1.0625rem;
            line-height: 1.7;
            color: #24272c;
        }
        .dark .es-fine-measure p { color: #d9dce2; }
        .es-fine-measure em { font-style: italic; }
        .es-fine-measure strong { font-weight: 700; color: #16181c; }
        .dark .es-fine-measure strong { color: #e9ebef; }

        .es-fine-list {
            list-style: disc;
            margin-top: 0.9rem;
            padding-inline-start: 1.4rem;
        }
        .es-fine-list li {
            margin-top: 0.5rem;
            font-size: 1.01rem;
            line-height: 1.65;
            color: #24272c;
        }
        .dark .es-fine-list li { color: #d9dce2; }
        .es-fine-list li::marker { color: #1d4ed8; }
        .dark .es-fine-list li::marker { color: #93c5fd; }

        /* Erasure procedure: a numbered instruction set, not a bullet list. */
        .es-fine-steps {
            list-style: none;
            margin-top: 1rem;
            counter-reset: es-fine-step;
        }
        .es-fine-steps li {
            counter-increment: es-fine-step;
            position: relative;
            padding: 0.55rem 0 0.55rem 2.6rem;
            font-size: 1.01rem;
            line-height: 1.6;
            color: #24272c;
            border-top: 1px solid rgba(22, 24, 28, 0.09);
        }
        .dark .es-fine-steps li { color: #d9dce2; border-top-color: rgba(233, 235, 239, 0.09); }
        .es-fine-steps li:first-child { border-top: 0; }
        .es-fine-steps li::before {
            content: counter(es-fine-step, decimal-leading-zero);
            position: absolute;
            inset-inline-start: 0;
            top: 0.72rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.72rem;
            font-weight: 700;
            color: #1d4ed8;
        }
        .dark .es-fine-steps li::before { color: #93c5fd; }

        /* --- Schedule: the processor table --------------------------- */
        .es-fine-scroll { overflow-x: auto; margin-top: 1.1rem; }
        .es-fine-table {
            width: 100%;
            border-collapse: collapse;
            text-align: start;
        }
        /* No min-width on a phone: the Purpose column is a legal disclosure,
           so it must WRAP rather than sit clipped behind a scroll container a
           reader has no reason to suspect is there. The 30rem floor comes back
           at 640px, where the sheet is already wider than that and the table
           reads as columns again. Measured at 390px: document scrollWidth
           equals clientWidth, no clipping. */
        @media (min-width: 640px) {
            .es-fine-table { min-width: 30rem; }
        }
        .es-fine-table caption {
            caption-side: top;
            text-align: start;
            padding-bottom: 0.55rem;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-table caption { color: #9aa1ab; }
        .es-fine-table th {
            text-align: start;
            padding: 0.45rem 0.9rem 0.45rem 0;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #4b5158;
            border-bottom: 1px solid rgba(22, 24, 28, 0.2);
            white-space: nowrap;
        }
        .dark .es-fine-table th { color: #9aa1ab; border-bottom-color: rgba(233, 235, 239, 0.2); }
        .es-fine-table td {
            padding: 0.7rem 0.9rem 0.7rem 0;
            vertical-align: top;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #24272c;
            border-bottom: 1px solid rgba(22, 24, 28, 0.09);
        }
        .dark .es-fine-table td { color: #d9dce2; border-bottom-color: rgba(233, 235, 239, 0.09); }
        .es-fine-vendor {
            font-weight: 700;
            color: #16181c;
            white-space: nowrap;
        }
        .dark .es-fine-vendor { color: #e9ebef; }
        /* The same weight for a cell that holds a sentence, which must wrap: legal text never
           scrolls sideways on a phone. */
        .es-fine-label {
            font-weight: 700;
            color: #16181c;
        }
        .dark .es-fine-label { color: #e9ebef; }

        /* --- Schedule: the identifier register ----------------------- */
        .es-fine-chip {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(22, 24, 28, 0.16);
            background-color: rgba(22, 24, 28, 0.05);
            border-radius: 0.28rem;
            padding: 0.15rem 0.45rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.78rem;
            color: #16181c;
        }
        .dark .es-fine-chip {
            border-color: rgba(233, 235, 239, 0.18);
            background-color: rgba(233, 235, 239, 0.06);
            color: #e9ebef;
        }

        /* Inline literals and defined terms inside the prose. */
        .es-fine-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.88em;
            padding: 0.05em 0.3em;
            border-radius: 0.2rem;
            background-color: rgba(22, 24, 28, 0.06);
            color: #16181c;
        }
        .dark .es-fine-code { background-color: rgba(233, 235, 239, 0.08); color: #e9ebef; }
        .es-fine-dfn {
            font-style: normal;
            font-weight: 700;
            color: #16181c;
            border-bottom: 1px dotted rgba(29, 78, 216, 0.6);
        }
        .dark .es-fine-dfn { color: #e9ebef; border-bottom-color: rgba(147, 197, 253, 0.6); }

        /* --- Links, buttons, endmark -------------------------------- */
        .es-fine-link {
            color: #1d4ed8;
            text-decoration: underline;
            text-decoration-thickness: 1px;
            text-underline-offset: 2px;
        }
        .dark .es-fine-link { color: #93c5fd; }
        .es-fine-link:hover { text-decoration-thickness: 2px; }
        .es-fine-ext {
            display: inline-block;
            width: 0.72em;
            height: 0.72em;
            margin-inline-start: 0.22em;
            vertical-align: baseline;
        }

        /* The only real control in the instrument. Its border is its affordance,
           so it has to clear WCAG 1.4.11's 3:1 for non-text contrast, which the
           campaign probe does NOT measure (it scores text nodes only). At 0.45
           alpha the edge computed 2.16:1 on the white sheet; 0.7 computes
           3.58:1 on #ffffff and 0.6 computes 4.45:1 on the #12141a sheet.
           NOTE: this button only renders when cookie_banner_visible() is true, so it is
           absent from a local render and cannot be probed here.
           If you re-ink it, recompute both edges by hand. */
        .es-fine-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1.1rem;
            padding: 0.6rem 1rem;
            border: 1px solid rgba(29, 78, 216, 0.7);
            border-radius: 0.4rem;
            background-color: rgba(29, 78, 216, 0.06);
            color: #1d4ed8;
            font-size: 0.9rem;
            font-weight: 600;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .es-fine-btn:hover { background-color: rgba(29, 78, 216, 0.12); border-color: #1d4ed8; }
        .dark .es-fine-btn {
            border-color: rgba(147, 197, 253, 0.6);
            background-color: rgba(147, 197, 253, 0.09);
            color: #93c5fd;
        }
        .dark .es-fine-btn:hover { background-color: rgba(147, 197, 253, 0.16); border-color: #93c5fd; }

        .es-fine-endmark {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 3.25rem;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-endmark { color: #9aa1ab; }
        .es-fine-endmark::before,
        .es-fine-endmark::after {
            content: "";
            flex: 1 1 auto;
            height: 1px;
            background-color: rgba(22, 24, 28, 0.14);
        }
        .dark .es-fine-endmark::before,
        .dark .es-fine-endmark::after { background-color: rgba(233, 235, 239, 0.14); }

        /* --- Companion documents ------------------------------------ */
        .es-fine-card {
            display: flex;
            flex-direction: column;
            padding: 1.15rem 1.25rem;
            background-color: #ffffff;
            border: 1px solid rgba(22, 24, 28, 0.12);
            border-radius: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .es-fine-card:hover {
            border-color: rgba(29, 78, 216, 0.45);
            box-shadow: 0 10px 26px -18px rgba(22, 24, 28, 0.45);
        }
        .dark .es-fine-card {
            background-color: #12141a;
            border-color: rgba(233, 235, 239, 0.12);
        }
        .dark .es-fine-card:hover {
            border-color: rgba(147, 197, 253, 0.45);
            box-shadow: 0 10px 26px -18px rgba(0, 0, 0, 0.85);
        }
        .es-fine-card-t { font-size: 1rem; font-weight: 700; color: #16181c; }
        .dark .es-fine-card-t { color: #e9ebef; }
        .es-fine-card-d { margin-top: 0.35rem; font-size: 0.88rem; line-height: 1.55; color: #4b5158; }
        .dark .es-fine-card-d { color: #9aa1ab; }
        .es-fine-card-go {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: auto;
            padding-top: 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #1d4ed8;
        }
        .dark .es-fine-card-go { color: #93c5fd; }
        .es-fine-card-arrow { width: 0.85rem; height: 0.85rem; transition: transform 0.2s ease; }
        .es-fine-card:hover .es-fine-card-arrow { transform: translateX(2px); }

        /* --- Wider viewports: the margin rule and the standing rail -- */
        @media (min-width: 768px) {
            .es-fine-docket { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .es-fine-clause { grid-template-columns: 3.6rem minmax(0, 1fr); gap: 0; }
            .es-fine-num { padding-top: 1.85rem; }
            /* The continuous hairline down the instrument: it lives on the
               text cell, and because consecutive clauses share an edge it
               reads as one rule from the first clause to the last. */
            .es-fine-clause-body {
                border-inline-start: 1px solid rgba(22, 24, 28, 0.14);
                padding: 1.75rem 0 0.5rem 1.75rem;
            }
            .dark .es-fine-clause-body { border-inline-start-color: rgba(233, 235, 239, 0.14); }
        }

        @media (min-width: 1024px) {
            .es-fine-shell { grid-template-columns: 15rem minmax(0, 1fr); gap: 3rem; }
            .es-fine-index {
                position: sticky;
                top: 5.5rem;
                max-height: calc(100vh - 8rem);
                overflow-y: auto;
            }
        }

        /* Focus rings. No border-radius here: an outline already follows the
           element's own radius. */
        #es-fine-page a:focus-visible,
        #es-fine-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 2px;
        }
        .dark #es-fine-page a:focus-visible,
        .dark #es-fine-page button:focus-visible { outline-color: #93c5fd; }

        @media (prefers-reduced-motion: reduce) {
            .es-fine-card,
            .es-fine-btn,
            .es-fine-num,
            .es-fine-card-arrow,
            .es-fine-index-link { transition: none; }
            .es-fine-card:hover .es-fine-card-arrow { transform: none; }
        }
    </style>

    @php
        // The document's own structure lives here once, so the contents rail, the part dividers and
        // the clause headings can never drift apart. Bodies are switched in below by id.
        //
        // Rewritten 2026-10-04 to describe what the code does (GDPR review): who the controller is,
        // legal bases, AI, every service provider, transfers, retention, rights, and the two cookie
        // categories. Every number and every name in it is read from, or pinned to, the code it
        // describes - change the code and change this page in the same commit.
        $doc = [
            [
                'roman' => 'I',
                'part' => 'Who we are and what this covers',
                'items' => [
                    ['n' => '01', 'id' => 'who-we-are', 't' => 'Who We Are and What This Covers'],
                    ['n' => '02', 'id' => 'security', 't' => 'Security Procedures & Encryption'],
                ],
            ],
            [
                'roman' => 'II',
                'part' => 'What we collect and why',
                'items' => [
                    ['n' => '03', 'id' => 'pii-collected', 't' => 'What We Collect'],
                    ['n' => '04', 'id' => 'legal-bases', 't' => 'Why We Use It, and on What Basis'],
                    ['n' => '05', 'id' => 'ai-features', 't' => 'AI Features'],
                    ['n' => '06', 'id' => 'follower-data', 't' => 'What Schedule Owners Can See'],
                ],
            ],
            [
                'roman' => 'III',
                'part' => 'Calendar data',
                'items' => [
                    ['n' => '07', 'id' => 'google-calendar-use', 't' => 'Use of Google and Outlook Calendar Data'],
                    ['n' => '08', 'id' => 'google-calendar-retention', 't' => 'Calendar Data Storage & Retention'],
                    ['n' => '09', 'id' => 'google-calendar-limited-use', 't' => 'Google Calendar Limited Use Compliance'],
                ],
            ],
            [
                'roman' => 'IV',
                'part' => 'Who processes it, and where',
                'items' => [
                    ['n' => '10', 'id' => 'third-party-access', 't' => 'Service Providers'],
                    ['n' => '11', 'id' => 'transfers', 't' => 'Where Your Data Is Processed'],
                ],
            ],
            [
                'roman' => 'V',
                'part' => 'Cookies, analytics and your choice',
                'items' => [
                    ['n' => '12', 'id' => 'analytics-cookies', 't' => 'Analytics & Cookies'],
                    ['n' => '13', 'id' => 'cookie-preferences', 't' => __('messages.cookie_consent_privacy_heading')],
                ],
            ],
            [
                'roman' => 'VI',
                'part' => 'Keeping, deleting and your rights',
                'items' => [
                    ['n' => '14', 'id' => 'retention', 't' => 'How Long We Keep It'],
                    ['n' => '15', 'id' => 'erasure', 't' => 'Deleting Your Account'],
                    ['n' => '16', 'id' => 'your-rights', 't' => 'Your Rights'],
                    ['n' => '17', 'id' => 'newsletter', 't' => 'Product Email & Unsubscribing'],
                ],
            ],
            [
                'roman' => 'VII',
                'part' => 'Minors, other sites, amendment and contact',
                'items' => [
                    ['n' => '18', 'id' => 'age-of-consent', 't' => 'Age of Consent Privacy'],
                    ['n' => '19', 'id' => 'external-links', 't' => 'Links to Other Websites'],
                    ['n' => '20', 'id' => 'changes', 't' => 'Changes to This Privacy Policy'],
                    ['n' => '21', 'id' => 'contact', 't' => 'Communication & Resolution'],
                ],
            ],
        ];

        $clauseCount = collect($doc)->sum(fn ($p) => count($p['items']));
        $partCount = count($doc);

        // The date of the last change to what this policy says. A fact about the document: move it
        // only in a commit that changes a clause.
        $lastUpdated = 'October 8, 2026';

        // The AI providers this install actually calls (GeminiUtils, OpenAIUtils).
        $aiProviders = array_values(array_filter([
            config('services.google.gemini_key') ? 'Google (Gemini)' : null,
            config('services.openai.api_key') ? 'OpenAI' : null,
        ]));

        // The map of venues on a schedule page (App\Services\VenueMap) uses two services, and an
        // operator may point them at different providers, so each is named from its own helper
        // and only where this install has it set: map_lookup() is the address search our servers
        // ask, map_tiles() is where a visitor's browser fetches street images. One row when both
        // are the same provider. Both helpers read config only, which is what makes them safe in
        // this edge-cached page.
        $mapLookup = map_lookup();
        $mapTiles = map_tiles();
        $mapLookupLine = 'Our servers send it the address of a venue to find where it is, and nothing about you.';
        $mapTilesLine = 'Your browser fetches the street images from it, so it sees your IP address, and only if you allow marketing cookies or press the button that shows the map.';
        $mapProcessors = match (true) {
            $mapLookup && $mapTiles && $mapLookup['name'] === $mapTiles['name'] => [[$mapLookup['name'], 'The map of venues on a schedule page. '.$mapLookupLine.' '.$mapTilesLine]],
            default => array_values(array_filter([
                $mapLookup ? [$mapLookup['name'], 'Address search for the map of venues on a schedule page. '.$mapLookupLine] : null,
                $mapTiles ? [$mapTiles['name'], 'Street images for the map of venues on a schedule page. '.$mapTilesLine] : null,
            ])),
        };

        // Clause 10. A row appears only where this install can actually send that provider data,
        // read from the same predicate the feature itself uses, so the schedule describes what this
        // deployment does rather than what the software supports. Meta's app review and Google's
        // verification read this page: keep the Wallet and Facebook Login rows' wording exact.
        $processors = array_values(array_filter([
            ['DigitalOcean', 'Hosting, database and file storage, in New York, United States. Everything described in this policy is stored here.'],
            ['Cloudflare', 'Content delivery and security for every page, which means it sees every visitor\'s IP address; and the bot check on the sign-up, sign-in, password reset, checkout, booking, gift card and contact forms.'],
            config('sentry.dsn') || browser_error_reporting() ? ['Sentry', 'Error reports. When something breaks: the address of the page or request, with ticket, reset and unsubscribe secrets removed and email addresses masked, your browser, the IP address it connects from, and the error. Never what you typed into a form.'] : null,
            ['Google Apps', 'Email and productivity services'],
            ['SendGrid/Twilio', 'Email delivery'],
            ['Stripe', 'Payment processing: paid plans and Boost, and ticket sales on schedules whose owner has connected Stripe, where Stripe receives the buyer\'s name and email address. Card details are entered with Stripe and never reach us.'],
            ['PayPal', 'Payment processing, on schedules whose owner has connected a PayPal account'],
            ['Payfast', 'Payment processing, on schedules whose owner has connected a Payfast account'],
            ['Invoice Ninja', 'Invoicing, on schedules whose owner has connected Invoice Ninja: the buyer\'s name, email address and what they bought.'],
            google_analytics_enabled() ? ['Google Analytics', 'Site statistics, and only if you allow analytics cookies (clause 12).'] : null,
            config('services.google.backend') || config('services.google.maps') ? ['Google Maps', 'Placing venues on a map from their address. The interactive map on an event page loads only if you allow marketing cookies or ask to see it.'] : null,
            $aiProviders ? [implode(', ', $aiProviders), 'AI features (clause 05): the text and images a feature is asked to read, translate or write from.'] : null,
            config('services.google.client_id') || config('services.microsoft.client_id') ? [
                implode(' and ', array_filter([config('services.google.client_id') ? 'Google' : null, config('services.microsoft.client_id') ? 'Microsoft' : null])),
                config('services.google.client_id') ? 'Sign-in with Google, and calendar sync, only when you connect your calendar (clauses 07 to 09).' : 'Calendar sync, only when you connect your calendar (clauses 07 to 09).',
            ] : null,
            ['YouTube', 'Videos added to schedules and events. On public pages they load only when you press play or allow marketing cookies, and their thumbnails are fetched by us, not by your browser.'],
            // Listed only when the install can actually issue a pass, so the register describes
            // what this deployment does rather than what the software supports. The same predicate
            // gates every button, the route handler and the confirmation email.
            // The field list is GoogleWalletService::classPayload() plus objectPayload() and
            // textModules(), read field by field: re-check it whenever a field is added there.
            \App\Services\Wallet\GoogleWalletService::isConfigured()
                ? ['Google Wallet', 'Ticket passes, and only when a buyer chooses to add a ticket to their wallet. The pass carries the attendee name, the ticket type and number, how many it admits, any seat labels, the event name and a link to its page, the venue with its address and map coordinates, the start and end time, up to 200 characters of the ticket notes the organizer wrote for the event, the schedule name, color and logo, the event image, and the ticket link, which includes the secret code of that ticket because that is what the door scanner reads. Google keeps a saved pass; it can be expired but not deleted']
                : null,
            // Same rule: listed only while facebook_login_enabled(), the predicate behind every
            // Facebook button and route. Meta's app review reads this page for exactly this.
            facebook_login_enabled()
                ? ['Meta (Facebook Login)', 'Sign-in, and only when you choose Continue with Facebook. Facebook tells us your name, email address and Facebook account ID; we keep the name and email on your account and store the ID to recognize you next time. We never receive your password and never store a Facebook access token. You can disconnect Facebook in Settings at any time, and deleting your account removes all of it']
                : null,
            config('services.meta.access_token') || config('services.meta.pixel_id')
                ? ['Meta (Boost)', 'Advertising for events whose organizer buys a Boost. On those event pages, the Meta Pixel; and when someone buys a ticket to one, the purchase with a one-way hash of the buyer\'s email address, which Meta matches to a Meta account if there is one, so the organizer can see what the campaign sold. Both only for visitors and buyers who allowed marketing cookies.']
                : null,
            config('ads.enabled') && config('ads.adsense_enabled')
                ? ['Google AdSense', 'Ads on the pages of free schedules, loaded only if you allow marketing cookies.']
                : null,
            config('services.twilio.sid')
                ? ['Twilio', 'Text messages: phone verification codes, invitations to claim a page, and invitations to join a schedule\'s team. On Enterprise schedules, WhatsApp messages sent to add events.']
                : null,
            \App\Services\OneSignalService::isConfigured()
                ? ['OneSignal', 'Push notifications, only for a browser that has turned them on. Nothing is sent to OneSignal from a browser that has not.']
                : null,
            ['Stay22', 'Accommodation search, on event pages where the schedule has enabled the accommodation map, and only once the map has been loaded'],
            ...$mapProcessors,
            config('app.growth_data_token')
                ? ['Anthropic', 'Help analyzing product usage statistics that carry no names, email addresses, IP addresses or anything you wrote.']
                : null,
        ]));

        // Companion documents. Navigation, not policy.
        $companions = [
            ['/terms-of-service', 'Terms of Service', 'The agreement you accept when you use the hosted service.'],
            ['/accessibility', 'Accessibility statement', 'The standard we work to, and how to tell us where we fall short.'],
            ['/self-hosting-terms-of-service', 'Selfhosting Terms of Service', 'The terms that apply when you run Event Schedule on your own server.'],
            ['/docs', 'Documentation', 'How the product actually works, section by section.'],
        ];

        $sessionCookie = (string) config('session.cookie');
        $sessionHours = max(1, (int) round(((int) config('session.lifetime')) / 60));
    @endphp

    <div id="es-fine-page" class="es-fine-page">

    <!-- ============================================================ -->
    <!-- Masthead: the register                                       -->
    <!-- ============================================================ -->
    <section id="es-fine-masthead" class="relative px-4 pb-10 pt-28 sm:px-6 lg:px-8">
        {{-- The family's shared texture, at the family's strength. No aurora,
             no rays, no light beam: a legal page gets grid paper and nothing
             else. It adapts with the colour mode, so it is not a pinned object. --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-fine-grid grid-pattern absolute inset-0"></div>
        </div>
        <div class="relative mx-auto max-w-5xl">
            <div class="es-fine-masthead">
                <p class="es-fade-up es-d-1">
                    <span class="es-fine-tag">
                        <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        Privacy
                    </span>
                </p>
                <h1 class="es-fine-title es-balance es-fade-up es-d-2 mt-4">Privacy <span class="es-fine-accent">Policy</span></h1>
                <p class="es-fine-lede es-fade-up es-d-3 mt-3">Event Schedule LLC</p>
                <p class="es-fine-intro es-fade-up es-d-3 mt-4">
                    Set out in {{ $partCount }} parts and {{ $clauseCount }} numbered clauses. Every section
                    number is a link of its own, so a single line of this policy can be cited
                    without quoting the whole page.
                </p>
            </div>

            {{-- The register. Each cell names the clause that governs it, in the
                 same citation form the instrument itself uses, so the masthead
                 demonstrates the claim the intro makes. --}}
            <dl class="es-fine-docket mt-7">
                <div>
                    <dt class="es-fine-docket-key">Scope</dt>
                    <dd class="es-fine-docket-val">
                        EventSchedule.com, its subdomains, and the schedules it serves on their owners' own domains
                        <a href="#who-we-are" class="es-fine-cite" aria-label="Clause 1, Who We Are and What This Covers">&sect;&nbsp;01</a>
                    </dd>
                </div>
                <div>
                    <dt class="es-fine-docket-key">Erasure</dt>
                    <dd class="es-fine-docket-val">
                        Final and irreversible
                        <a href="#erasure" class="es-fine-cite" aria-label="Clause 15, Deleting Your Account">&sect;&nbsp;15</a>
                    </dd>
                </div>
                <div>
                    <dt class="es-fine-docket-key">Privacy contact</dt>
                    <dd class="es-fine-docket-val">
                        <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a>
                        <a href="#contact" class="es-fine-cite" aria-label="Clause 21, Communication and Resolution">&sect;&nbsp;21</a>
                    </dd>
                </div>
                <div>
                    <dt class="es-fine-docket-key">Last updated</dt>
                    <dd class="es-fine-docket-val">
                        {{ $lastUpdated }}
                        <a href="#changes" class="es-fine-cite" aria-label="Clause 20, Changes to This Privacy Policy">&sect;&nbsp;20</a>
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The instrument, with its standing contents rail              -->
    <!-- ============================================================ -->
    <section class="px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl">
            <div class="es-fine-shell">

                <!-- Contents: sticky on desktop, printed at the head of the
                     document on narrow screens. -->
                <nav class="es-fine-index" aria-label="Contents">
                    <p class="es-fine-index-title">Contents</p>
                    @foreach ($doc as $part)
                        <p class="es-fine-index-part">{{ $part['roman'] }}. {{ $part['part'] }}</p>
                        <ul>
                            @foreach ($part['items'] as $item)
                                <li>
                                    <a href="#{{ $item['id'] }}" class="es-fine-index-link">
                                        <span class="es-fine-index-num">{{ $item['n'] }}</span>
                                        <span>{{ $item['t'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </nav>

                <!-- The sheet -->
                <div class="es-fine-sheet px-5 sm:px-8">
                    @foreach ($doc as $part)
                        <p class="es-fine-part">
                            <span class="es-fine-part-num">{{ $part['roman'] }}</span>
                            <span>{{ $part['part'] }}</span>
                            <span class="es-fine-part-rule" aria-hidden="true"></span>
                        </p>

                        @foreach ($part['items'] as $item)
                            <section id="{{ $item['id'] }}" class="es-fine-clause">
                                <a href="#{{ $item['id'] }}" class="es-fine-num" aria-label="Clause {{ (int) $item['n'] }}, {{ $item['t'] }}">&sect;&nbsp;{{ $item['n'] }}</a>
                                <div class="es-fine-clause-body">
                                    <h2 class="es-fine-h">{{ $item['t'] }}</h2>
                                    <div class="es-fine-measure">

                                        @switch($item['id'])

                                            @case('who-we-are')
                                                <p>
                                                    This policy explains how Event Schedule LLC ("Event Schedule", "we") handles personal data on EventSchedule.com, its subdomains, and the schedules it serves on their owners' own domains. <dfn class="es-fine-dfn">PII</dfn> (Personally Identifiable Information) means anything that identifies you, directly or together with other details.
                                                </p>
                                                <p>
                                                    For the data of the people who create accounts and visit the site, we decide what is collected and why, so we are its <strong>controller</strong>. The details an organizer collects through their schedule are different: the people who buy tickets, RSVP, book an appointment, follow, or sign up for its emails are that organizer's customers and audience. The organizer is the controller of those details, and we process them on the organizer's behalf to run the service. Questions about them are best put to the organizer; we help organizers answer them, and you can always write to us.
                                                </p>
                                                <p>
                                                    A selfhosted Event Schedule is run by whoever installed it, on their own servers. This policy does not cover it, and we have no access to it.
                                                </p>
                                                @break

                                            @case('security')
                                                <p>
                                                    We implement technical safeguards to protect your data from unauthorized access. All data transmitted between our systems and users is encrypted using industry-standard encryption protocols (HTTPS/TLS). Passwords are stored only as one-way hashes, and the access tokens and credentials of the services you connect are encrypted at rest. Access to user data is restricted through authentication mechanisms and role-based access controls, and our administrators must use two-factor authentication to reach the admin tools.
                                                </p>
                                                <p>
                                                    Data obtained through Google APIs is handled in accordance with Google's policies. It is used only for the features you asked for, is never sold or used for advertising, and is shared only with the service providers in clause 10 as needed to run them. When you revoke access we delete the tokens and our sync records (clause 08); events already copied into your schedule stay there until you delete them.
                                                </p>
                                                @break

                                            @case('pii-collected')
                                                <p>
                                                    We collect the following personal information:
                                                </p>
                                                <ul class="es-fine-list">
                                                    <li><strong>Your account:</strong> your name and email address, your password (stored only as a one-way hash), your language and timezone, and, if you add them, a profile photo and a phone number. If you sign in with Google or Facebook, the name, email address and account ID that service shares, and with Google your profile photo.</li>
                                                    <li><strong>What you publish:</strong> your schedules and events, with their descriptions, images, venues and addresses. For a performer or venue page an organizer creates while listing an event: the name, and any email address or phone number the organizer enters, which is never shown in full.</li>
                                                    <li><strong>Asking a schedule to list an event or take a booking:</strong> the event you describe, your name and email address, a phone number if the schedule asks for one, your answers to the schedule's own questions, and your message.</li>
                                                    <li><strong>Buying, booking and RSVPs:</strong> the name, email address, and phone number if the organizer asks for one, answers to the organizer's questions, what was bought or booked, and whether it was paid. Card details are entered with the payment provider and never reach us; for a ticket paid in installments we keep the card type and its last four digits.</li>
                                                    <li><strong>Following and email lists:</strong> which schedules you follow. For a schedule's email sign-up: the address and name, the page language, the IP address it was entered from and the time it was confirmed, which are the record of your consent. For updates about a single event, asked for without an account: the email address you enter, the IP address it came from, and the language of the page. Schedule newsletters record whether each one was opened and which links were clicked.</li>
                                                    <li><strong>What you send us:</strong> support chat messages, with the page you wrote from and your country; and comments, photos and videos you post to events.</li>
                                                    <li><strong>Paid plans:</strong> billing is handled by Stripe, and we keep the card type, its last four digits, and the history of your plan.</li>
                                                    <li><strong>How you found us:</strong> when you create an account, the page you first landed on, the site that sent you and any campaign tags, if you allowed marketing cookies or signed up in the same visit. If you came through the sign-up or sign-in button on our homepage, also which headline the homepage was showing.</li>
                                                    <li><strong>Security records:</strong> while you are signed in, your session with its IP address and browser; and a log of sensitive actions on your account with the IP address and browser, kept for 90 days.</li>
                                                    <li><strong>Days you used the app:</strong> the dates on which you opened the app while signed in, and nothing about what you did there. We use them to count how many people use the service, and keep them for 120 days.</li>
                                                    <li><strong>Visit statistics:</strong> your country, worked out from your IP address, which itself is not stored. If you accept analytics cookies while signed in: the pages you view, and those you viewed in this browser just before signing in, kept for about one hour after your last activity (see "Analytics &amp; Cookies").</li>
                                                    <li><strong>Connected services:</strong> if you connect a calendar, the access tokens Google or Microsoft issue, stored encrypted, and which events are synced. If you turn on push notifications, the identifier the push service gives your browser. On Enterprise schedules, WhatsApp messages sent to the schedule's number to add events, with their images.</li>
                                                </ul>

                                                {{-- Round 3 (2026-09-10): two capture points that shipped after this clause was
                                                     written, neither of them an account. EventInterestController::store() writes the
                                                     email, ip_address and locale; EventRepo::saveEvent() stores a named act's or
                                                     venue's email and phone, which role/show-guest-unclaimed.blade.php never prints
                                                     and the claim page masks (RoleController::maskContact()). Who can SEE an
                                                     interest-list address is left unsaid on purpose: the event editor shows only a
                                                     count, but BackupService::exportInterests() puts the addresses in the backup any
                                                     editor of the schedule can export. That is the owner's call, not this page's. --}}
                                                <p>
                                                    An address left for event updates ("Tell me when tickets go on sale" or "Tell me if anything changes") belongs to that one event and date. It does not create an account and is not a subscription to the schedule. It is used to email you about that event: when its tickets go on sale, a reminder shortly before it starts, a notice if it is cancelled, and any notice the organizer chooses to send if its date or venue changes. Every one of those emails has an unsubscribe link, and unsubscribing deletes the address. It is also deleted along with the event, and 30 days after the event at the latest.
                                                </p>
                                                <p>
                                                    A page an organizer creates for a performer or venue that is not on Event Schedule shows the name and the dates listed for it, says that it has not been claimed, and stays out of search engines until it is. If the organizer asks us to, we send the email address or phone number they entered an invitation to claim the page. Claiming it means signing in with that same address or number.
                                                </p>
                                                @break

                                            @case('legal-bases')
                                                <p>
                                                    The law asks us to name a reason for each use of your data. These are ours:
                                                </p>
                                                <div class="es-fine-scroll">
                                                    <table class="es-fine-table">
                                                        <caption>Purposes and legal bases (GDPR Article 6)</caption>
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">What we do</th>
                                                                <th scope="col">Basis</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ([
                                                                ['Run your account and the features you use; sell, deliver and check in tickets; send receipts, reminders and the notices a booking needs', 'Contract'],
                                                                ['Keep sales records for organizers and for tax', 'Contract, and legal obligation'],
                                                                ['Keep the service secure, stop fraud and abuse, fix errors, keep the security log', 'Legitimate interests'],
                                                                ['Count visits as daily totals, and the anonymous version of our live view', 'Legitimate interests'],
                                                                ['Count how many signed-in people use the app, from the dates each account was used. No cookie is involved', 'Legitimate interests'],
                                                                [(google_analytics_enabled() ? 'Google Analytics, the identified live view' : 'The identified version of our live view').', and remembering in your browser which homepage headline you saw', 'Your consent (analytics cookies)'],
                                                                ...(\App\Utils\RealtimeTracker::ownerViewEnabled() ? [['Show a schedule\'s organizer your visit to their own pages as a row with no name (country, device type, page, time on page)', 'Your consent (analytics cookies)']] : []),
                                                                ['Record the page or site that brought you when you create an account or buy in the same visit, and which homepage headline was showing when you create an account from our homepage', 'Legitimate interests'],
                                                                ['Campaign attribution kept across visits, the Meta Pixel and Conversions API, ads, and maps, videos and booking widgets from other sites', 'Your consent (marketing cookies), or your click on that one item'],
                                                                ['Push notifications', 'Your consent (your browser\'s permission)'],
                                                                ['Send account holders product news, tips and schedule digests', 'Legitimate interests, with a way to say no on the sign-up page and in every email'],
                                                                ['A schedule\'s newsletters and email updates', 'The organizer\'s basis, which is normally your consent: a confirmed sign-up, a Follow, or the unticked box at checkout'],
                                                                ['AI features you use, and translations of what organizers publish', 'Contract'],
                                                            ] as [$purpose, $basis])
                                                                <tr>
                                                                    <td>{{ $purpose }}</td>
                                                                    <td class="es-fine-label">{{ $basis }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p>
                                                    Where we rely on legitimate interests you can object, and where we rely on consent you can withdraw it at any time (clause 16). No decision about you is made by automated means alone.
                                                </p>
                                                @break

                                            @case('ai-features')
                                                <p>
                                                    Some features send content to an AI provider to do their job: reading an event from text or a flyer image that someone pastes or uploads, including events visitors submit to a schedule that accepts submissions, or from the text of a web page whose link a schedule's editor pastes; translating a schedule's and its events' text into other languages; writing event descriptions and email text when asked to; and generating images.
                                                    @if ($aiProviders)
                                                        On this service the provider is {{ implode(' or ', $aiProviders) }}, depending on the feature.
                                                    @endif
                                                    A provider receives only what the feature needs, such as the text or the image being read, and never your password or payment details. Translations are published as they come back, and an event sent by WhatsApp message is created straight away; everything else the provider returns is shown to be checked and edited before it is published.
                                                </p>
                                                @break

                                            @case('follower-data')
                                                <p>
                                                    When you follow a schedule, sign up for its emails, buy a ticket, RSVP, book with it, or submit content (such as a comment, photo or video) to its events, the schedule owner can see your name and email address so they can keep you informed and reach out if needed. If you open one of its newsletters, the owner can see that you did and which links you clicked. Owners can export their sales records to keep their own books. We never sell any of it. You can stop following a schedule at any time from your "Following" page, and leave its email list from any of its emails.
                                                </p>
                                                <p>
                                                    When you ask a schedule to list an event or take a booking, the schedule's team sees what you sent on its requests page, and its owners and admins also receive it in the email that tells them a request has arrived, which goes to the schedule's shared team mailbox too if it has set one up. If the schedule accepts, the event you described is published. Your answers to the schedule's own questions can be published with it: unless the schedule keeps a question private, an answer can be found through the search and filters on the schedule's page, and the schedule can choose to show it on the event's page.
                                                </p>
                                                {{-- Round 3 (2026-09-10): RoleSubscriberController::confirm() now calls linkAccount(),
                                                     so confirming a schedule's sign-up panel creates a passwordless account that
                                                     follows the schedule wherever Role::willCreateAccountOnConfirm() allows it (on
                                                     this hosted install, any claimed schedule that is not a demo). The address is
                                                     listed on the owner's Followers tab as an account-less subscriber from the
                                                     moment it is entered (role/show-admin-followers.blade.php).
                                                     2026-10-04: buying, RSVPing, posting content and asking to add an event no
                                                     longer make anyone a follower, and an organizer's contact import creates no
                                                     accounts. Still a follow: the curator guest-submit form, which says so
                                                     (EventController), and a venue or performer schedule an organizer lists on
                                                     their own event (EventRepo), so it is offered in their lists next time. --}}
                                                <p>
                                                    Signing up for a schedule's email updates works the same way: the schedule owner sees the address you enter, and confirming the link we send also sets up an account for you that follows the schedule. Submitting an event to a curator's schedule makes you a follower too, as the submission form says, and so does listing another schedule's venue or performer on an event you create, so it appears in your lists. Buying a ticket, RSVPing, or posting a comment, photo or video does not make you a follower.
                                                </p>
                                                {{-- The owner's Realtime tab. Same predicate as the live-view paragraph under
                                                     "Cookies and analytics", which says what is recorded and for how long. --}}
                                                @if (\App\Utils\RealtimeTracker::ownerViewEnabled())
                                                <p>
                                                    A schedule owner can also see live traffic to their own schedule's pages: how many times they were viewed in the last half hour, from which countries and kinds of device, and from which sites visits to them began; and, for visitors who allowed analytics cookies on a notice that mentions organizers, a row with no name showing a country, a device type, which page is open and for how long. The row stays in their list for up to half an hour after you leave. That view never shows your name, email address or account, and what it holds about your visit is deleted about an hour after your last activity. Beside it the owner sees their own records of the last day, which they keep in any case: sales, RSVPs, bookings, new followers and newsletter subscribers, events submitted to the schedule, waitlist joins, requests for updates about an event, and what guests posted on an event, listed there without names. If you buy a ticket, RSVP, book, follow a schedule or do anything else on that list while you are on its page, the organizer has that record as before and may be able to tell which unnamed visit was yours.
                                                </p>
                                                @endif
                                                @break

                                            @case('google-calendar-use')
                                                <p>
                                                    Users must explicitly authorize access to their Google Calendar or Microsoft Outlook calendar through that provider's OAuth authorization process. We access calendar data solely to provide and improve the core functionality of our services, including:
                                                </p>
                                                <ul class="es-fine-list">
                                                    <li>Viewing, creating, updating, or deleting calendar events as requested by the user</li>
                                                    <li>Synchronizing events between the user's calendar and Event Schedule</li>
                                                    <li>Sending notifications and reminders related to calendar events</li>
                                                </ul>
                                                <p>
                                                    We do not use calendar data for advertising, marketing, or profiling purposes.
                                                </p>
                                                @break

                                            @case('google-calendar-retention')
                                                <p>
                                                    The access tokens are stored encrypted. If you disconnect a calendar in Settings, we stop syncing at once and delete the tokens and our record of which events are synced; for Google we also ask Google to revoke our access. If you remove our access in your Google Account or Microsoft account settings instead, we notice the next time we use the connection, which for a calendar we sync from is usually within a couple of hours. For Google we then delete the same things. For Outlook we delete the tokens and stop syncing, but keep the record of which events are synced, because Microsoft reports a password change the same way and reconnecting should not copy every event again; that record goes when you disconnect in Settings or delete your account. Events already copied into your schedule stay there as your events until you delete them.
                                                </p>
                                                @break

                                            @case('google-calendar-limited-use')
                                                <p>
                                                    Our use of Google Calendar data complies with the Google API Services User Data Policy, including the Limited Use requirements. We only access, use, store, and share Google Calendar data as permitted by these policies and only for the features and services explicitly requested by the user.
                                                </p>
                                                @break

                                            @case('third-party-access')
                                                <p>
                                                    Per GDPR requirements, we disclose the service providers that may process your data to operate our system. Each one receives only what its job needs, and none of them may use it for anything else:
                                                </p>
                                                <div class="es-fine-scroll">
                                                    <table class="es-fine-table">
                                                        <caption>Schedule of service providers</caption>
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Vendor</th>
                                                                <th scope="col">Purpose</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($processors as [$vendor, $purpose])
                                                                <tr>
                                                                    <td class="es-fine-vendor">{{ $vendor }}</td>
                                                                    <td>{{ $purpose }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p>
                                                    When a schedule owner connects their own payment account, calendar or email server, the data needed for that runs through the service they chose, under their agreement with it.
                                                </p>
                                                @break

                                            @case('transfers')
                                                <p>
                                                    Event Schedule is run from the United States, and the service and its data are hosted there. Most of the providers above are based there too. If you use the service from the European Economic Area, the United Kingdom or Switzerland, your data is therefore transferred to the United States. Write to <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a> for a copy of the safeguards that apply to those transfers.
                                                </p>
                                                @break

                                            @case('analytics-cookies')
                                                {{-- Two consent categories (resources/js/consent-state.js, cookie-consent.js,
                                                     partials/cookie-banner.blade.php). Every item named below waits for its category
                                                     in the browser; ConsentCategoriesTest and ConsentEmbedTest pin that.

                                                     Google Analytics is named only where this install loads it
                                                     (google_analytics_enabled(), the predicate behind the tag itself): here, in the
                                                     legal bases, in the provider schedule and in the cookie table. Without it the
                                                     Analytics category is the realtime beacon's identified mode and the homepage
                                                     headline test's memory, which is everything that reads consent.has('analytics').
                                                     GoogleAnalyticsDisclosureTest pins both states. --}}
                                                <p>
                                                    When you first visit, a banner asks about two kinds of optional cookies and similar storage, and nothing optional is stored, or loaded from another company, until you choose:
                                                </p>
                                                <ul class="es-fine-list">
                                                    <li>
                                                        <strong>Analytics:</strong>
                                                        @if (google_analytics_enabled())
                                                            Google Analytics 4, the identified version of our live view (below), and remembering in your browser, until the tab closes, which homepage headline you saw. Google Analytics is not loaded at all until you allow this: no script and no request to Google.
                                                        @else
                                                            the identified version of our live view (below), and remembering in your browser, until the tab closes, which homepage headline you saw.
                                                        @endif
                                                    </li>
                                                    <li><strong>Marketing and embedded content:</strong> campaign attribution cookies;@if (google_analytics_enabled()) Google Analytics' advertising features;@endif on events whose organizer runs a Boost, the Meta Pixel, and telling Meta about a ticket you buy as a one-way hash of your email address; ads on free schedules, where they are switched on; and maps, videos and booking widgets from other sites (Google Maps, YouTube, Stay22), which set their own cookies{{ $mapTiles ? '; and the street images of a schedule\'s map of venues, which your browser fetches from '.$mapTiles['name'] : '' }}.</li>
                                                </ul>
                                                <p>
                                                    "Allow all" turns on both, "Decline" neither, and "Choose" lets you pick. Your choice is kept for twelve months and then asked again. Without marketing consent, a map or a video shows a button instead, and pressing it loads that one item and nothing else. Stay22, which pays a commission on bookings made through its map, is never loaded if your browser sends Global Privacy Control.
                                                </p>
                                                <p>
                                                    {{ google_analytics_enabled() ? 'Separately from Google, we' : 'Separately, we' }} keep our own visit statistics, and those are deliberately built so that no individual can be picked out of them. We store only daily totals: views per device type, per referring source, per country, per campaign tag. These daily totals contain no per-visitor record. To avoid counting the same person twice in a day, and to filter out bots, your IP address and browser user-agent are combined into a one-way hash using a secret key and a salt that changes every day; that hash exists only as the key of a short-lived cache entry that expires by midnight, and is never stored in a record of your visit. Because none of this reads or writes anything on your device, it needs no cookie and no consent.
                                                </p>

                                                {{-- /admin/realtime (RealtimeTracker, RealtimeBeaconController). The two modes are
                                                     enforced server-side: a count-only page view is stored with no visitor key, user,
                                                     title, browser or OS whatever the browser sends (and a page view that names no
                                                     mode is count-only), and a withdrawal strips those (and swaps a path that names
                                                     the visitor for its route template) from every row this browser still has on its
                                                     network, plus the account's when the withdrawing page is signed in. Rows are
                                                     pruned about an hour after last activity (realtime:prune on both cron rails, plus
                                                     a request-time backstop). Keep this paragraph in step with that code.

                                                     Who sees it follows RealtimeTracker::ownerViewEnabled(), the predicate behind a
                                                     schedule owner's own Realtime tab (/analytics, App\Services\ScheduleRealtime):
                                                     where that page exists this says what an organizer sees of it, and where it does
                                                     not, that they see none. An organizer's page lists a visitor only from a row that
                                                     is owner_visible, i.e. whose analytics choice was made after the cookie notice
                                                     began to name organizers. PrivacyLiveViewTest fails the build if this page says
                                                     organizers never see the live view on an install where they do. --}}
                                                <p>
                                                    @if (\App\Utils\RealtimeTracker::ownerViewEnabled())
                                                    We also keep a short-lived record for a live view of the site. Our administrators can see all of it. The organizer of a schedule can see the part about that schedule's own pages, and never who you are: how many times its pages were viewed, from which countries and kinds of device, and from which sites visits to it began; and, if you allowed analytics, a row with no name showing your country, your device type, which of their pages you have open and for how long, kept in their list for up to half an hour after you leave. In that view an organizer never sees your name, email address or account, your browser or operating system, or any page outside their own schedules. If you buy a ticket, RSVP, book, follow a schedule or leave any other record with its organizer (clause 06) while you are on its page, the organizer has that record as before and may be able to tell which unnamed visit was yours. A choice you made before our cookie notice began to mention organizers does not put you in that list: you are then only counted.
                                                    @else
                                                    We also keep a short-lived record for a live view of the site that only our administrators can see, never schedule owners.
                                                    @endif
                                                    What it holds depends on your analytics choice. If you allow analytics, each page you view is recorded with the page, the referring site and campaign tag, your country (looked up from your IP address, which itself is not stored), your device type, browser and operating system, and how long the page stays open, under a one-way hash of your IP address, browser and language settings that changes every day; while you are signed in it is linked to your account, including the pages you viewed in this browser just before signing in. If you decline, do not answer, or your browser sends Global Privacy Control, each page view is still counted, but with no identifier and nothing that links it to you or to your other page views: only the page (for pages behind sign-in, just which kind of page), your country, your device type and, when you arrive from another site or a tagged link, that site and campaign tag. Either way, records are deleted about an hour after your last activity, and if you withdraw your consent, the identifiers are removed from what this browser still has on its current network and, if you are signed in when you withdraw, from your account's records.
                                                </p>
                                                <p>
                                                    We honor the <a href="https://globalprivacycontrol.org/" target="_blank" rel="noopener" class="es-fine-link">Global Privacy Control<svg class="es-fine-ext" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5h6v6M19 5L9 15M15 19H5V9" /></svg></a> signal: if your browser sends GPC, we treat it as declining both categories, and the banner does not appear.
                                                </p>
                                                <p>
                                                    Your choice is stored in a cookie named <code class="es-fine-code">cookie_consent</code>, with the categories you allowed and when, so one choice holds across EventSchedule.com and its subdomains and our server can honor it too; your browser keeps a copy in <code class="es-fine-code">localStorage</code> under the same name. A schedule on its own domain asks separately. It records nothing but the choice itself, and we do not store it against any account.
                                                </p>

                                                <div class="es-fine-scroll">
                                                    <table class="es-fine-table">
                                                        <caption>Cookies and browser storage</caption>
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Name</th>
                                                                <th scope="col">What it is for, and how long it lasts</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach (array_filter([
                                                                ['Always', $sessionCookie, 'Keeps you signed in, holds your cart and checkout, and protects forms. '.$sessionHours.' hours after your last request.'],
                                                                ['Always', 'XSRF-TOKEN', 'Protects forms against requests forged by other sites. As long as the session.'],
                                                                ['Always', 'remember_web_*', 'Keeps you signed in on this device. Set when you sign in or create an account; up to 400 days, or until you log out.'],
                                                                ['Always', 'last_login_method', 'Whether you last signed in with a password, Google or Facebook, to show that option first. 1 year.'],
                                                                ['Always', 'browser_timezone, browser_language', 'Sets up a new account in your timezone and language. 1 hour, on the sign-in and sign-up pages only.'],
                                                                ['Always', 'cookie_consent', 'Your choice in the cookie banner. 12 months.'],
                                                                ['Always', '__cf_bm', 'Bot protection by Cloudflare, which may set it on any page. 30 minutes.'],
                                                                ['Always', 'Stripe (__stripe_mid, __stripe_sid)', 'Fraud prevention by the payment provider, on payment pages only. __stripe_mid 1 year, __stripe_sid 30 minutes.'],
                                                                ['Push', 'OneSignal (browser storage)', 'Only if you turn on push notifications: the identifier the push service gives this browser. Until you turn them off.'],
                                                                google_analytics_enabled() ? ['Analytics', '_ga, _ga_<measurement-id>', 'Google Analytics: tells visits and visitors apart. Up to 2 years, and deleted when you withdraw.'] : null,
                                                                ['Analytics', 'es_hero, es_hero_clicked', 'Session storage: which homepage headline you saw and whether you then clicked sign up. Until the tab closes.'],
                                                                ['Marketing', 'utm_params, utm_referrer_url, utm_landing_page', 'Which link, campaign or site brought you here, credited if you later create an account or buy a ticket. 30 days.'],
                                                                ['Marketing', 'es_attribution', 'The same, written by your browser for the step from our marketing pages to sign-up: the page you landed on, the site that sent you, campaign and referral tags, and the homepage headline you saw. Until the browser closes; at most 2 KB.'],
                                                                ['Marketing', '_fbp, _fbc', 'The Meta Pixel, on the pages of Boosted events. Up to 90 days, and deleted when you withdraw.'],
                                                                ['Marketing', '__gads, __gpi, __eoi', 'Google AdSense, on free schedules where ads are switched on. Up to 13 months, and deleted when you withdraw.'],
                                                                ['Marketing', 'Third-party cookies', 'Set by Google Maps, YouTube and Stay22 when one of them loads. Their own policies apply.'],
                                                            ]) as [$kind, $name, $what])
                                                                <tr>
                                                                    <td><span class="es-fine-chip">{{ $name }}</span><br><span class="es-fine-muted text-xs">{{ $kind }}</span></td>
                                                                    <td>{{ $what }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p>
                                                    Your browser also keeps a few things for your convenience, in <code class="es-fine-code">localStorage</code>, which stay in your browser until you use them: your theme and accessibility settings, the calendar view you picked, a schedule's map of venues that you hid, unsent form drafts, your cart, and the name, email and phone you entered at checkout, so you do not have to type them again. Those details are forgotten after 30 days, or once everything in the cart has been bought. A support chat started before signing in is remembered by a random identifier.
                                                </p>
                                                <p>
                                                    You can withdraw consent at any time, as easily as you gave it (GDPR Article 7(3)): use "Cookie preferences" at the bottom of the page (in the app, under About in the menu), or the button in the next section. Withdrawing deletes the cookies the withdrawn category set on this site, unloads any map, video or accommodation search already on the page, and clears the attribution cookies.
                                                </p>
                                                @break

                                            @case('cookie-preferences')
                                                <p>
                                                    {{ __('messages.cookie_consent_privacy_body') }}
                                                </p>
                                                @if (cookie_banner_visible())
                                                    <button type="button" data-cookie-consent-reopen class="es-fine-btn">
                                                        <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        {{ __('messages.cookie_consent_manage') }}
                                                    </button>
                                                @endif
                                                @break

                                            @case('retention')
                                                {{-- The periods are App\Console\Commands\PrunePersonalData's constants (ACTIVE_DAY_DAYS among them), audit:prune,
                                                     RealtimeTracker::RETENTION_MINUTES, config('session.lifetime'), the backup and
                                                     webhook-delivery cleanups. Change one and change this table with it. --}}
                                                <div class="es-fine-scroll">
                                                    <table class="es-fine-table">
                                                        <caption>How long each kind of data is kept</caption>
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Data</th>
                                                                <th scope="col">Kept</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ([
                                                                ['Your account, and the schedules and events you publish', 'Until you delete them, or your account'],
                                                                ['Sales, bookings and RSVPs', 'As long as the organizer keeps them. When an organizer deletes one, the buyer\'s name, email, phone and answers are removed 30 days later; the amounts stay in their books.'],
                                                                ['Email sign-ups that were never confirmed', '30 days after the last confirmation email'],
                                                                ['Confirmed sign-ups and follows', 'Until you leave, or delete your account'],
                                                                ['A schedule\'s newsletters: who they were sent to, and opens and clicks', 'As long as the schedule keeps its newsletters'],
                                                                ['The list of addresses that unsubscribed', 'Kept, so the unsubscribe keeps working'],
                                                                ['Interest lists for an event', 'Until you unsubscribe, the event is deleted, or 30 days after the event'],
                                                                ['Waitlists for an event', 'Until the organizer removes you, the event is deleted, or 30 days after the event'],
                                                                ['Support chats started without an account', '12 months after the last message'],
                                                                ['Live view records', 'About an hour after your last activity'],
                                                                ['Sessions', $sessionHours.' hours after your last request'],
                                                                ['Security log', '90 days. For records of plan changes, schedule claims and connected payment or calendar accounts, which are kept, the IP address and browser are removed after 90 days.'],
                                                                ['The dates you used the app while signed in', '120 days. After that only daily totals remain, which contain nothing about you'],
                                                                ['Background tasks that failed, which can contain an email address', '30 days'],
                                                                ['Exports you download', '7 days'],
                                                                ['Records of webhooks sent to organizers\' systems', '30 days'],
                                                                ['Visit statistics', 'Kept as daily totals, which contain nothing about you'],
                                                            ] as [$what, $kept])
                                                                <tr>
                                                                    <td class="es-fine-label">{{ $what }}</td>
                                                                    <td>{{ $kept }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <p>
                                                    Error reports, server logs and database backups are kept for limited periods, by us and by our providers, and then deleted or overwritten.
                                                </p>
                                                @break

                                            @case('erasure')
                                                <p>
                                                    To permanently delete your account and all associated data:
                                                </p>
                                                <ol class="es-fine-steps">
                                                    <li>Log in to your account</li>
                                                    <li>Click "Settings" in the main menu</li>
                                                    <li>Scroll down to find the "Delete Account" option</li>
                                                    <li>Click "Delete Account" to permanently remove your data</li>
                                                </ol>
                                                {{-- App\Services\AccountDeletionService, called by ProfileController::destroy(), and
                                                     the database cascades it prepares for. sales.user_id is nullOnDelete since
                                                     2026_10_04_000001. --}}
                                                <p>
                                                    Deleting your account removes it at once, together with the schedules you own and their events, your images and other uploads, the comments, photos and videos you posted while signed in (and your email address on any you posted without signing in), your follows and email sign-ups, any interest list or waitlist entry under your address, your support chats, your sessions on every device, and your calendar connections, whose access we ask Google to revoke. The above method of data purge is final and irreversible.
                                                </p>
                                                <p>
                                                    Some records stay, because they are other people's or because we need them. A ticket, booking or gift card you bought stays in the organizer's records under the name and email you used, because it is their record of a sale; ask the organizer to remove your details. An event or newsletter you created for a schedule someone else owns stays with that schedule. A schedule keeps its record of the newsletters it sent you, including whether you opened them, and any mailing list an organizer imported your address into. If you unsubscribed from a schedule's emails, that opt-out is kept so the schedule cannot email you again. The security log keeps its entries for the account, with the IP address and browser, for up to 90 days, no longer linked to the account; records of plan changes, schedule claims and connected payment and calendar accounts are kept after that, without the IP address and browser.
                                                </p>
                                                {{-- Round 3 (2026-09-10). Step 2 said "Profile" in a top right menu: the only link
                                                     to this screen is the main-menu entry labelled "Settings"
                                                     (layouts/navigation.blade.php), and the top right menu holds only Log out.
                                                     The paragraph below covers the two new routes out for people without an
                                                     account. EventInterestController::unsubscribe() deletes the row;
                                                     RoleController::claimNotMeSubmit() takes a page down for a verified matching
                                                     contact (userHoldsContactFor()) and only audits anyone else. A takedown is
                                                     ScheduleDeletionService::markDeleted(), a soft delete, which is why this says
                                                     "comes down" and never "erased". --}}
                                                <p>
                                                    If you left your email address to hear about an event, unsubscribing from any email about it deletes the address. If an organizer created a page in your name, choose "This is not me" on it. You sign in or create an account first, so we know who is asking: if the verified email address or phone number on that account matches the one on the page, the page comes down straight away, and otherwise your request is recorded for review.
                                                </p>
                                                @break

                                            @case('your-rights')
                                                <p>
                                                    Wherever you are, you can ask us to:
                                                </p>
                                                <ul class="es-fine-list">
                                                    <li><strong>Give you a copy</strong> of your data. "Download my data" in Settings emails you a link to a file with the personal data held about your account and email address, in a format other services can read. Your schedules' own content and images are in each schedule's backup.</li>
                                                    <li><strong>Correct it.</strong> Most of it you can change yourself in Settings.</li>
                                                    <li><strong>Delete it</strong> (clause 15), or <strong>restrict</strong> what we do with it while a question about it is settled.</li>
                                                    <li><strong>Stop</strong> a use based on our legitimate interests, including product email (clause 17).</li>
                                                    <li><strong>Withdraw consent</strong> you gave, such as for cookies, at any time, without affecting what was done before.</li>
                                                </ul>
                                                <p>
                                                    Write to <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a> for anything you cannot do in Settings. We answer within one month, and may ask you to confirm it is you. For details an organizer holds, such as a ticket you bought, write to the organizer, or to us and we will pass it on. If you are in the European Economic Area, the United Kingdom or Switzerland, you also have the right to complain to your data protection authority.
                                                </p>
                                                @break

                                            @case('newsletter')
                                                <p>
                                                    We send account holders occasional product news, tips, and digests about their own schedules to the email address on the account. You can say no on the sign-up page, switch them off under Settings, or use the "unsubscribe" link in any of them, which your email app may also show as a button; you can also write to <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a>. Note that we may still send legally required notifications, and the emails your account needs, such as receipts, password resets and security notices, to your registered email.
                                                </p>
                                                @break

                                            @case('age-of-consent')
                                                <p>
                                                    Our Service does not address anyone under the age of 18. We do not knowingly collect personally identifiable information from anyone under the age of 18. If you are a parent or guardian and you are aware that your child has provided us with personal data, please contact us immediately. If we become aware that we have collected personal data from anyone under the age of 18 without verification of parental consent, we will take steps to remove that information.
                                                </p>
                                                @break

                                            @case('external-links')
                                                <p>
                                                    Our website may contain links to other sites. Once you leave our site via these links, we have no control over that other website. We are not responsible for the protection and privacy of any information you provide on those sites, and they are not governed by this privacy policy or our terms of service.
                                                </p>
                                                @break

                                            @case('changes')
                                                <p>
                                                    We may update this privacy policy from time to time to reflect changes in our practices. The date at the top of this page shows when it last changed. If we make material changes, we will notify you by email and newsletter to your registered email address. We encourage you to periodically review this page for the latest information on our privacy practices.
                                                </p>
                                                @break

                                            @case('contact')
                                                <p>
                                                    If you have any questions about your privacy, data usage, or how to purge your data, please contact us at <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a>
                                                </p>
                                                @break

                                        @endswitch

                                    </div>
                                </div>
                            </section>
                        @endforeach
                    @endforeach

                    <p class="es-fine-endmark">End of policy</p>

                    <p class="mt-6 text-center">
                        <a href="#es-fine-masthead" class="es-fine-link">Back to the top of the document</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Companion documents                                          -->
    <!-- ============================================================ -->
    <section class="px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl">
            <p class="mb-3"><span class="es-fine-tag">The set</span></p>
            <h2 class="es-balance es-fine-h mb-2">The other documents</h2>
            <p class="es-fine-muted mb-6 max-w-2xl text-sm">
                Three more instruments sit beside this one: the Terms of Service cover the
                agreement, the accessibility statement covers the interface, and the selfhosting
                terms cover running Event Schedule on your own server. The documentation covers
                what the features named above actually do.
            </p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($companions as [$href, $name, $blurb])
                    <a href="{{ marketing_url($href) }}" class="es-fine-card">
                        <span class="es-fine-card-t">{{ $name }}</span>
                        <span class="es-fine-card-d">{{ $blurb }}</span>
                        <span class="es-fine-card-go">
                            Read it
                            <svg class="es-fine-card-arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <p class="es-fine-muted mt-9 max-w-2xl text-sm">
                Anything in this policy you want explained, or think is wrong, goes to
                <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a>.
                A real person reads it.
            </p>
        </div>
    </section>

    <x-marketing.related-pages />

    </div>
</x-marketing-layout>
