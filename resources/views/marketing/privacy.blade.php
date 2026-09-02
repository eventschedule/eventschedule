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
           The words ARE the product here, so not one of them has been
           changed, softened, strengthened, reordered, added or removed in
           this rebuild - every clause below is verbatim from the first-wave
           page. What changed is the apparatus around the words: this is now
           laid out as a legal instrument, with a masthead register, six
           numbered parts, sixteen hanging clause numbers on a continuous
           margin rule, a standing contents rail, and schedules where the
           source was already a list.

           THE ARGUMENT IS THE FORM. The product's privacy position is that
           everything is enumerable: a named list of processors, a named list
           of browser identifiers, a numbered erasure procedure that ends in
           a permanent delete. A page that can print those as schedules is
           making the claim by being able to make it. So the devices are:

             1. THE MARGIN RULE - a continuous hairline down the left of the
                text block with a monospace clause number hanging in the
                gutter. It is what makes sixteen paragraphs a document
                rather than a scroll. The number is an ANCHOR, not an
                ornament, carrying its own aria-label ("Clause 8,
                Restriction/Erasure...") so the citation the masthead
                promises is real and reaches assistive tech. /terms does the
                same; if you demote it back to a decorative <p>, delete the
                masthead sentence about citing a single line.
             2. THE DOCKET - a masthead register of three cells (scope,
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
                NOTE FOR WHOEVER KNOWS THE ANSWER: there is still no
                effective date on this policy, because the first-wave page
                carried none and inventing one would fabricate a legal fact.
                A fourth cell drops straight in here; the grid is
                repeat(3, ...) at md and would become repeat(4, ...).
             3. THE CONTENTS RAIL - a standing index, six roman-numeral
                parts over sixteen clauses, sticky on desktop and printed at
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

        /* --- Schedule: the identifier register ----------------------- */
        .es-fine-aside {
            margin-top: 1.2rem;
            padding: 0.9rem 1rem;
            border: 1px solid rgba(22, 24, 28, 0.12);
            border-inline-start: 2px solid #1d4ed8;
            border-radius: 0.35rem;
            background-color: rgba(22, 24, 28, 0.03);
        }
        .dark .es-fine-aside {
            border-color: rgba(233, 235, 239, 0.12);
            border-inline-start-color: #93c5fd;
            background-color: rgba(233, 235, 239, 0.035);
        }
        .es-fine-aside-k {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-aside-k { color: #9aa1ab; }
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
           NOTE: this button only renders when consent_required() is true, so it is
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
            .es-fine-docket { grid-template-columns: repeat(3, minmax(0, 1fr)); }
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
        // The document's own structure lives here once, so the contents rail,
        // the part dividers and the clause headings can never drift apart.
        // The ORDER AND WORDING OF EVERY HEADING IS THE FIRST-WAVE PAGE'S,
        // unchanged - clause 10's heading is still the translation key it
        // always was. Bodies are switched in below by id, also verbatim.
        $doc = [
            [
                'roman' => 'I',
                'part' => 'Scope and safeguards',
                'items' => [
                    ['n' => '01', 'id' => 'consent-to-process', 't' => 'Privacy Policy, Consent to Process'],
                    ['n' => '02', 'id' => 'security', 't' => 'Security Procedures & Encryption'],
                ],
            ],
            [
                'roman' => 'II',
                'part' => 'Google Calendar data',
                'items' => [
                    ['n' => '03', 'id' => 'google-calendar-use', 't' => 'Use of Google Calendar Data'],
                    ['n' => '04', 'id' => 'google-calendar-retention', 't' => 'Google Calendar Data Storage & Retention'],
                    ['n' => '05', 'id' => 'google-calendar-limited-use', 't' => 'Google Calendar Limited Use Compliance'],
                ],
            ],
            [
                'roman' => 'III',
                'part' => 'What we collect, who accesses it, how to erase it',
                'items' => [
                    ['n' => '06', 'id' => 'pii-collected', 't' => 'Consent: PII Data We Collect'],
                    ['n' => '07', 'id' => 'third-party-access', 't' => 'Third Party Data Access'],
                    ['n' => '08', 'id' => 'erasure', 't' => 'Restriction/Erasure: Purging PII Data'],
                ],
            ],
            [
                'roman' => 'IV',
                'part' => 'Analytics, cookies and your choice',
                'items' => [
                    ['n' => '09', 'id' => 'analytics-cookies', 't' => 'Analytics & Cookies'],
                    ['n' => '10', 'id' => 'cookie-preferences', 't' => __('messages.cookie_consent_privacy_heading')],
                ],
            ],
            [
                'roman' => 'V',
                'part' => 'Other sites, schedule owners and email',
                'items' => [
                    ['n' => '11', 'id' => 'external-links', 't' => 'Links to Other Websites'],
                    ['n' => '12', 'id' => 'follower-data', 't' => 'Follower & Engagement Data Visibility'],
                    ['n' => '13', 'id' => 'newsletter', 't' => 'Newsletter & Removal'],
                ],
            ],
            [
                'roman' => 'VI',
                'part' => 'Minors, amendment and contact',
                'items' => [
                    ['n' => '14', 'id' => 'age-of-consent', 't' => 'Age of Consent Privacy'],
                    ['n' => '15', 'id' => 'changes', 't' => 'Changes to This Privacy Policy'],
                    ['n' => '16', 'id' => 'contact', 't' => 'Communication & Resolution'],
                ],
            ],
        ];

        $clauseCount = collect($doc)->sum(fn ($p) => count($p['items']));
        $partCount = count($doc);

        // Clause 07 verbatim, split on the dash the source list already used.
        $processors = [
            ['Cloudflare', 'Content delivery and security'],
            ['Google Apps', 'Email and productivity services'],
            ['Stripe', 'Payment processing'],
            ['SendGrid/Twilio', 'Email delivery'],
            ['Stay22', 'Accommodation search, on event pages where the schedule has enabled the accommodation map, and only once the map has been loaded'],
        ];

        // Companion documents. Navigation, not policy.
        $companions = [
            ['/terms-of-service', 'Terms of Service', 'The agreement you accept when you use the hosted service.'],
            ['/accessibility', 'Accessibility statement', 'The standard we work to, and how to tell us where we fall short.'],
            ['/self-hosting-terms-of-service', 'Selfhosting Terms of Service', 'The terms that apply when you run Event Schedule on your own server.'],
            ['/docs', 'Documentation', 'How the product actually works, section by section.'],
        ];
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
                        EventSchedule.com and all associated subdomains
                        <a href="#consent-to-process" class="es-fine-cite" aria-label="Clause 1, Privacy Policy, Consent to Process">&sect;&nbsp;01</a>
                    </dd>
                </div>
                <div>
                    <dt class="es-fine-docket-key">Erasure</dt>
                    <dd class="es-fine-docket-val">
                        Final, total, and irreversible
                        <a href="#erasure" class="es-fine-cite" aria-label="Clause 8, Restriction and Erasure, Purging PII Data">&sect;&nbsp;08</a>
                    </dd>
                </div>
                <div>
                    <dt class="es-fine-docket-key">Privacy contact</dt>
                    <dd class="es-fine-docket-val">
                        <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a>
                        <a href="#contact" class="es-fine-cite" aria-label="Clause 16, Communication and Resolution">&sect;&nbsp;16</a>
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

                                            @case('consent-to-process')
                                                <p>
                                                    This <dfn class="es-fine-dfn">DPA</dfn> (Data Privacy Addendum) applies to EventSchedule.com and all associated subdomains owned and operated by Event Schedule. <dfn class="es-fine-dfn">PII</dfn> (Personally Identifiable Information) is collected directly through account registration. This policy describes your options in deleting/purging your data permanently from Event Schedule in compliance with GDPR.
                                                </p>
                                                @break

                                            @case('security')
                                                <p>
                                                    We implement technical safeguards to protect your data from unauthorized access. All data transmitted between our systems and users is encrypted using industry-standard encryption protocols (HTTPS/TLS). Sensitive information is also encrypted at rest. Access to user data is restricted through authentication mechanisms and role-based access controls.
                                                </p>
                                                <p>
                                                    Data obtained through Google APIs is handled in accordance with Google's policies and is never sold or shared with third parties. Such data is retained only as long as necessary for service provision and is deleted upon revocation of access.
                                                </p>
                                                @break

                                            @case('google-calendar-use')
                                                <p>
                                                    Users must explicitly authorize access to their Google Calendar data through Google's OAuth authorization process. We access Google Calendar data solely to provide and improve the core functionality of our services, including:
                                                </p>
                                                <ul class="es-fine-list">
                                                    <li>Viewing, creating, updating, or deleting calendar events as requested by the user</li>
                                                    <li>Synchronizing events between Google Calendar and Event Schedule</li>
                                                    <li>Sending notifications and reminders related to calendar events</li>
                                                </ul>
                                                <p>
                                                    We do not use Google Calendar data for advertising, marketing, or profiling purposes.
                                                </p>
                                                @break

                                            @case('google-calendar-retention')
                                                <p>
                                                    We retain Google Calendar data only for as long as necessary to provide our services. If you revoke access to your Google Calendar data through your Google Account settings, we will stop accessing your data and delete any stored calendar data within a reasonable timeframe, unless retention is required for legal or security purposes.
                                                </p>
                                                @break

                                            @case('google-calendar-limited-use')
                                                <p>
                                                    Our use of Google Calendar data complies with the Google API Services User Data Policy, including the Limited Use requirements. We only access, use, store, and share Google Calendar data as permitted by these policies and only for the features and services explicitly requested by the user.
                                                </p>
                                                @break

                                            @case('pii-collected')
                                                <p>
                                                    We collect the following personal information:
                                                </p>
                                                <ul class="es-fine-list">
                                                    <li>Account name and email address</li>
                                                    <li>Optional company information (name, website, ID, VAT, phone, address, industry)</li>
                                                    <li>Geolocation based on IP address</li>
                                                    <li>For paid accounts: billing information including the last four digits of your card, expiration date, and billing address</li>
                                                </ul>
                                                @break

                                            @case('third-party-access')
                                                <p>
                                                    Per GDPR requirements, we disclose the third-party vendors that may access your data to operate our system:
                                                </p>
                                                <div class="es-fine-scroll">
                                                    <table class="es-fine-table">
                                                        <caption>Schedule of third-party vendors</caption>
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
                                                @break

                                            @case('erasure')
                                                <p>
                                                    To permanently delete your account and all associated data:
                                                </p>
                                                <ol class="es-fine-steps">
                                                    <li>Log in to your account</li>
                                                    <li>Click "Profile" from the top right menu</li>
                                                    <li>Scroll down to find the "Delete Account" option</li>
                                                    <li>Click "Delete Account" to permanently remove your data</li>
                                                </ol>
                                                <p>
                                                    The above method of data purge is final, total, and irreversible.
                                                </p>
                                                @break

                                            @case('analytics-cookies')
                                                <p>
                                                    We use Google Analytics 4 to understand how visitors use the site. Tracking is opt-in: when you first visit, all analytics, advertising, and personalization signals are set to <em>denied</em> via Google Consent Mode v2. Nothing is read or written to your browser until you click "Allow" in the cookie banner. If you click "Decline", or never respond, we do not set any analytics cookies and only cookieless pings are sent.
                                                </p>
                                                <p>
                                                    Separately from Google, we keep our own visit statistics, and those are deliberately built so that no individual can be picked out of them. We store only daily totals: views per device type, per referring source, per country, per campaign tag. There is no per-visitor record anywhere in the system. To avoid counting the same person twice in a day, and to filter out bots, your IP address and browser user-agent are combined into a one-way hash using a secret key and a salt that changes every day; that hash lives only in a temporary cache entry that expires at midnight and is never written to our database. Because none of this reads or writes anything on your device, it needs no cookie and no consent.
                                                </p>
                                                <p>
                                                    We honor the <a href="https://globalprivacycontrol.org/" target="_blank" rel="noopener" class="es-fine-link">Global Privacy Control<svg class="es-fine-ext" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5h6v6M19 5L9 15M15 19H5V9" /></svg></a> signal: if your browser sends GPC, we treat that as a "Decline" automatically and the banner does not appear.
                                                </p>
                                                <p>
                                                    Your choice is stored in your browser's <code class="es-fine-code">localStorage</code> under the key <code class="es-fine-code">cookie_consent</code> (values: <code class="es-fine-code">granted</code> or <code class="es-fine-code">denied</code>), and mirrored into a cookie of the same name so our server can honor it too. It records nothing but the choice itself, and we do not store it against any account.
                                                </p>
                                                <p>
                                                    If you accept, Google Analytics sets first-party cookies named <code class="es-fine-code">_ga</code> and <code class="es-fine-code">_ga_&lt;measurement-id&gt;</code>, used to distinguish unique visitors and sessions. We also set <code class="es-fine-code">ads_data_redaction</code> on every page so any beacons that do fire are stripped of advertising identifiers.
                                                </p>
                                                <p>
                                                    Accepting also lets us set three first-party <strong>attribution</strong> cookies, <code class="es-fine-code">utm_params</code>, <code class="es-fine-code">utm_referrer_url</code> and <code class="es-fine-code">utm_landing_page</code>, which last 30 days. They remember which link, campaign or referring site brought you here, so that if you later create an account or buy a ticket we can credit it to the right source. They hold campaign tags and page addresses, never anything you typed. If you decline, or never answer, they are not set, and any copy left from an earlier visit is deleted on your next request. Attribution then lasts only for the current browsing session, carried by the session cookie that signs you in and keeps your cart, which is required for the site to work and so is not part of this choice.
                                                </p>

                                                {{-- A reading aid, not a clause: the exact strings named above,
                                                     collected so they can be found in a browser inspector. It asserts
                                                     nothing the paragraphs do not already say. --}}
                                                <div class="es-fine-aside">
                                                    <p class="es-fine-aside-k">Identifiers named in this clause</p>
                                                    <p class="mt-2 flex flex-wrap gap-2">
                                                        <span class="es-fine-chip">cookie_consent</span>
                                                        <span class="es-fine-chip">_ga</span>
                                                        <span class="es-fine-chip">_ga_&lt;measurement-id&gt;</span>
                                                        <span class="es-fine-chip">ads_data_redaction</span>
                                                        <span class="es-fine-chip">utm_params</span>
                                                        <span class="es-fine-chip">utm_referrer_url</span>
                                                        <span class="es-fine-chip">utm_landing_page</span>
                                                    </p>
                                                </div>

                                                <p>
                                                    Some event pages show an <strong>accommodation map</strong> of hotels and rentals near the venue, provided by <a href="https://www.stay22.com" target="_blank" rel="noopener" class="es-fine-link">Stay22<svg class="es-fine-ext" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5h6v6M19 5L9 15M15 19H5V9" /></svg></a>. It appears only where the schedule owner has switched it on, and bookings made through it may earn a commission for that schedule or for us. Stay22 sets its own third-party cookies to attribute those bookings, so the map is <em>never</em> loaded until you have either accepted cookies or explicitly asked to see it. If you have already clicked "Allow", it loads with the page. Otherwise you see a short notice and a button, and nothing at all is requested from Stay22 until you click that button. If your browser sends Global Privacy Control, the map is never loaded and no button is offered.
                                                </p>
                                                <p>
                                                    You can withdraw consent at any time. Use the "Cookie preferences" button in the next section to reopen the banner and change your choice. Withdrawing consent is as easy as giving it (GDPR Article 7(3)); it also removes any accommodation map already loaded on the page, and clears the attribution cookies described above.
                                                </p>
                                                @break

                                            @case('cookie-preferences')
                                                <p>
                                                    {{ __('messages.cookie_consent_privacy_body') }}
                                                </p>
                                                @if (consent_required())
                                                    <button type="button" data-cookie-consent-reopen class="es-fine-btn">
                                                        <svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                        {{ __('messages.cookie_consent_manage') }}
                                                    </button>
                                                @endif
                                                @break

                                            @case('external-links')
                                                <p>
                                                    Our website may contain links to other sites. Once you leave our site via these links, we have no control over that other website. We are not responsible for the protection and privacy of any information you provide on those sites, and they are not governed by this privacy policy or our terms of service.
                                                </p>
                                                @break

                                            @case('follower-data')
                                                <p>
                                                    When you follow a schedule, purchase a ticket from a schedule, or submit content (such as a comment, photo, or video) to an event, the schedule owner can see your name and email address so they can keep you informed and reach out if needed. This data is shared only with the specific schedule owner you interact with; it is never sold or shared with third parties. You can stop following a schedule at any time from your "Following" page.
                                                </p>
                                                @break

                                            @case('newsletter')
                                                <p>
                                                    We periodically send newsletters announcing new features to the email address registered with your account. You can request your email address purged from newsletters by contacting <a href="mailto:privacy@eventschedule.com" class="es-fine-link">privacy@eventschedule.com</a> or clicking the "unsubscribe" link in any newsletter. Note that we may still send legally required notifications to your registered email.
                                                </p>
                                                @break

                                            @case('age-of-consent')
                                                <p>
                                                    Our Service does not address anyone under the age of 18. We do not knowingly collect personally identifiable information from anyone under the age of 18. If you are a parent or guardian and you are aware that your child has provided us with personal data, please contact us immediately. If we become aware that we have collected personal data from anyone under the age of 18 without verification of parental consent, we will take steps to remove that information.
                                                </p>
                                                @break

                                            @case('changes')
                                                <p>
                                                    We may update this privacy policy from time to time to reflect changes in our practices. If we make material changes, we will notify you by email and newsletter to your registered email address. We encourage you to periodically review this page for the latest information on our privacy practices.
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
