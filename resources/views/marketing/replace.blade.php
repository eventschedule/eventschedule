<x-marketing-layout>
    <x-slot name="title">Replace Google Forms, Canva & More | Event Schedule</x-slot>
    <x-slot name="description">Replace Google Forms, Mailchimp, Canva, Notion, and Trello with Event Schedule: purpose-built event management with ticketing, event pages, and AI.</x-slot>
    <x-slot name="breadcrumbTitle">Replace</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ItemList",
        "name": "Replace Your Event Tools with Event Schedule",
        "description": "General-purpose tools that Event Schedule can replace for event management.",
        "url": "{{ config('app.url') }}/replace",
        "numberOfItems": 12,
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Google Forms", "url": "{{ config('app.url') }}/google-forms-replacement"},
            {"@type": "ListItem", "position": 2, "name": "Mailchimp", "url": "{{ config('app.url') }}/mailchimp-replacement"},
            {"@type": "ListItem", "position": 3, "name": "Canva", "url": "{{ config('app.url') }}/canva-replacement"},
            {"@type": "ListItem", "position": 4, "name": "Linktree", "url": "{{ config('app.url') }}/linktree-replacement"},
            {"@type": "ListItem", "position": 5, "name": "Google Sheets", "url": "{{ config('app.url') }}/google-sheets-replacement"},
            {"@type": "ListItem", "position": 6, "name": "Calendly", "url": "{{ config('app.url') }}/calendly-replacement"},
            {"@type": "ListItem", "position": 7, "name": "SurveyMonkey", "url": "{{ config('app.url') }}/surveymonkey-replacement"},
            {"@type": "ListItem", "position": 8, "name": "Doodle", "url": "{{ config('app.url') }}/doodle-replacement"},
            {"@type": "ListItem", "position": 9, "name": "QR Code Generators", "url": "{{ config('app.url') }}/qr-code-generator-replacement"},
            {"@type": "ListItem", "position": 10, "name": "Squarespace", "url": "{{ config('app.url') }}/squarespace-replacement"},
            {"@type": "ListItem", "position": 11, "name": "Notion", "url": "{{ config('app.url') }}/notion-replacement"},
            {"@type": "ListItem", "position": 12, "name": "Trello", "url": "{{ config('app.url') }}/trello-replacement"}
        ]
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
           Replace "The Toolbelt" styles.

           THE CONCEPT: one strap, twelve loops. Every tool on this page
           was bought to hold one job - the sign-up form, the flyer, the
           mailing list, the link in bio, the spreadsheet, the door code.
           Twelve products describing ONE event. The belt is the product
           argument: the twelve jobs are not twelve records here, they
           are twelve readings of the same Event row, so the date is
           typed once and the flyer, the ticket, the newsletter and the
           calendar all follow it.

           SIGNATURE DEVICES
           1. The strap. A dark woven webbing rail carrying twelve
              numbered loops, one per replacement page - each loop is a
              LINK to that page, so the hover lift is an honest
              affordance and the strap is navigation. The L-numbers are
              the page's spine: they name the loops in the hero, the
              failures in section 03, the rows of the census and the
              plan mapping in section 06. It is a FIXED
              PHYSICAL OBJECT: `.es-belt-strap` renders identically with
              `.dark` on and off, so the band overrides for
              `.grid-overlay`, `.animate-shimmer` and
              `.es-claim:focus-within` sit AFTER the `.dark` rules and
              win by document order. Verified with the verifier's
              --bands=.es-belt-strap flag (expect 0 diffs).
           2. The collapse. Twelve job ticks funnel down one spine into
              a single event record. This is deliberately NOT an
              isometric stack: /saas owns "The Stack" and its isometric
              ownership device. Here the geometry is a funnel, drawn
              with abstract bars and one exit dot, never an outline
              illustration of a belt or a tool.
           3. The census. A real <table> of twelve rows - tool, the job
              it was doing, what does that job here, and which plan -
              because a list of twelve replacements is a record, and a
              record wants a table. Every row carries its link.

           NO INVENTED BILL. The counters in section 02 count logins,
           accounts and copies of the date. They deliberately do NOT
           count invoices or money: several of the twelve have free
           tiers, so "twelve invoices" would be an assumed competitor
           bill, which is the exact claim this page was rebuilt to
           remove. The only prices here are our own.

           COLOUR: the page keeps its existing blue family but drops
           the shared brand blue/sky/cyan gradient, which is site
           chrome rather than a page identity. What is left is a single
           flat workwear blue, #0f4c81 (8.18 on the #f4f6f8 ground) and
           #8ecbff in the dark (11.11 on #0b0f13), spent on one word
           per heading and on the loop rivets. Distinctiveness comes
           from the webbing material, the monospaced loop numbers and
           the funnel geometry, not from a new hue.

           NEVER use text-gray-500 here: 4.83 on pure white but only
           4.2-4.5 on this tinted ground. Use .es-belt-muted (7.41).

           NEVER put an .es-aurora inside a strap: the shared rule
           changes its opacity in dark mode and un-pins the object.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------- */
        .es-belt-page { background-color: #f4f6f8; color: #101418; }
        .dark .es-belt-page { background-color: #0b0f13; color: #e9eef3; }
        .es-belt-ink { color: #101418; }
        .dark .es-belt-ink { color: #e9eef3; }
        .es-belt-muted { color: #4b5158; }
        .dark .es-belt-muted { color: #9aa6b2; }
        .es-belt-accent { color: #0f4c81; }
        .dark .es-belt-accent { color: #8ecbff; }

        /* --- Type ---------------------------------------------------- */
        .es-belt-tag {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-belt-tag { color: #9aa6b2; }
        .es-belt-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.08em;
        }
        .es-belt-lead { font-size: 1.125rem; line-height: 1.7; }
        .es-belt-small { font-size: 0.8rem; line-height: 1.6; }
        .es-belt-xs { font-size: 0.7rem; line-height: 1.5; }

        /* --- Section mark: a stamped loop tab ------------------------- */
        .es-belt-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border: 1px solid rgba(16, 20, 24, 0.18);
            border-radius: 0.35rem;
            background: #ffffff;
            color: #101418;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
        }
        .dark .es-belt-tab { border-color: rgba(233, 238, 243, 0.2); background: #151b21; color: #e9eef3; }
        .es-belt-tab::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #0f4c81;
        }
        .dark .es-belt-tab::before { background: #8ecbff; }

        /* --- Cards --------------------------------------------------- */
        .es-belt-card {
            border: 1px solid rgba(16, 20, 24, 0.12);
            border-radius: 0.9rem;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        .dark .es-belt-card { border-color: rgba(233, 238, 243, 0.12); background: #151b21; }
        .es-belt-hover:hover { border-color: rgba(15, 76, 129, 0.45); }
        .dark .es-belt-hover:hover { border-color: rgba(142, 203, 255, 0.45); }
        .es-belt-hover:hover .es-belt-hover-title,
        .es-belt-hover:hover .es-belt-hover-arrow { color: #0f4c81; }
        .dark .es-belt-hover:hover .es-belt-hover-title,
        .dark .es-belt-hover:hover .es-belt-hover-arrow { color: #8ecbff; }

        /* --- THE STRAP: woven webbing, identical in both colour modes -- */
        .es-belt-strap {
            position: relative;
            background-color: #0e141a;
            background-image:
                repeating-linear-gradient(90deg, rgba(233, 238, 243, 0.030) 0 2px, rgba(0, 0, 0, 0) 2px 5px),
                repeating-linear-gradient(0deg, rgba(0, 0, 0, 0.22) 0 1px, rgba(0, 0, 0, 0) 1px 4px),
                linear-gradient(180deg, #171f27 0%, #0e141a 58%, #0a0f14 100%);
            box-shadow:
                inset 0 1px 0 rgba(233, 238, 243, 0.07),
                inset 0 -18px 34px rgba(0, 0, 0, 0.5);
        }
        /* Ink that lives ON the strap. Fixed values, no dark variants. */
        .es-belt-on { color: #e9eef3; }
        .es-belt-on-muted { color: #9aa6b2; }
        .es-belt-lit { color: #8ecbff; }
        .es-belt-strap .es-belt-tag { color: #8ecbff; }
        /* Row of stitching that holds the loops on. */
        .es-belt-stitch {
            border-top: 1px dashed rgba(233, 238, 243, 0.18);
            border-bottom: 1px dashed rgba(233, 238, 243, 0.18);
            padding: 0.9rem 0;
        }
        .es-belt-rail { border-radius: 0.55rem; padding: 0.85rem 0.9rem; }

        /* --- A loop: one tool, one job ------------------------------- */
        .es-belt-loops {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.4rem;
        }
        @media (min-width: 640px) { .es-belt-loops { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
        @media (min-width: 1024px) { .es-belt-loops { grid-template-columns: repeat(6, minmax(0, 1fr)); } }
        .es-belt-loop {
            position: relative;
            display: block;
            padding: 0.6rem 0.6rem 0.55rem;
            border: 1px solid rgba(233, 238, 243, 0.11);
            border-radius: 0.45rem;
            background: linear-gradient(180deg, #232d36 0%, #1a222a 100%);
            box-shadow: inset 0 1px 0 rgba(233, 238, 243, 0.06), 0 1px 2px rgba(0, 0, 0, 0.45);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .es-belt-loop::after {
            content: "";
            position: absolute;
            top: 0.45rem;
            right: 0.5rem;
            width: 5px;
            height: 5px;
            border-radius: 9999px;
            background: #8ecbff;
            opacity: 0.8;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.45);
        }
        .es-belt-loop:hover { transform: translateY(-2px); border-color: rgba(142, 203, 255, 0.5); }
        .es-belt-loop:hover .es-belt-loop-t { color: #8ecbff; }
        .es-belt-loop-n {
            display: block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            color: #9aa6b2;
        }
        .es-belt-loop-t {
            display: block;
            margin-top: 0.2rem;
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1.25;
            color: #e9eef3;
            transition: color 0.2s ease;
        }

        /* --- THE COLLAPSE: twelve jobs funnel into one record --------- */
        .es-belt-flow { display: grid; gap: 1.5rem; align-items: center; }
        @media (min-width: 1024px) {
            .es-belt-flow { grid-template-columns: minmax(0, 1fr) 2.75rem minmax(0, 19rem); }
        }
        .es-belt-jobs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.35rem;
        }
        @media (min-width: 640px) { .es-belt-jobs { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .es-belt-job {
            display: flex;
            align-items: baseline;
            gap: 0.45rem;
            padding: 0.4rem 0.55rem;
            border: 1px solid rgba(233, 238, 243, 0.09);
            border-radius: 0.35rem;
            background: rgba(233, 238, 243, 0.05);
        }
        .es-belt-job-n {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: #9aa6b2;
        }
        .es-belt-job-t { font-size: 0.74rem; font-weight: 600; color: #e9eef3; }
        /* The spine everything funnels down. Horizontal on small
           screens, vertical once the three columns appear. */
        .es-belt-spine {
            position: relative;
            height: 0.4rem;
            border-radius: 9999px;
            background: linear-gradient(90deg, rgba(142, 203, 255, 0.12), rgba(142, 203, 255, 0.75));
        }
        .es-belt-spine::after {
            content: "";
            position: absolute;
            top: 50%;
            inset-inline-end: -4px;
            width: 10px;
            height: 10px;
            margin-top: -5px;
            border-radius: 9999px;
            background: #8ecbff;
            box-shadow: 0 0 12px rgba(142, 203, 255, 0.75);
        }
        @media (min-width: 1024px) {
            .es-belt-spine {
                width: 0.4rem;
                height: 11rem;
                margin: 0 auto;
                background: linear-gradient(180deg, rgba(142, 203, 255, 0.12), rgba(142, 203, 255, 0.75) 50%, rgba(142, 203, 255, 0.12));
            }
            .es-belt-spine::after { inset-inline-end: -3px; }
            /* The two connectors that make the funnel read as one: jobs
               into the spine, spine out to the record. */
            .es-belt-spine::before {
                content: "";
                position: absolute;
                top: 50%;
                inset-inline-start: -1.6rem;
                width: 1.6rem;
                height: 1px;
                background: linear-gradient(90deg, rgba(142, 203, 255, 0), rgba(142, 203, 255, 0.6));
            }
            .es-belt-out { position: relative; }
            .es-belt-flow > .es-belt-out::before {
                content: "";
                position: absolute;
                top: 50%;
                inset-inline-start: -1.6rem;
                width: 1.6rem;
                height: 1px;
                background: rgba(142, 203, 255, 0.55);
            }
        }
        /* The one thing that comes out of the funnel. */
        .es-belt-out {
            border: 1px solid rgba(142, 203, 255, 0.35);
            border-radius: 0.9rem;
            background: rgba(233, 238, 243, 0.06);
            padding: 1.25rem;
            box-shadow: inset 0 1px 0 rgba(233, 238, 243, 0.06);
        }
        .es-belt-field {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.4rem 0;
            border-top: 1px dashed rgba(233, 238, 243, 0.12);
        }
        .es-belt-field-k {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.62rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #9aa6b2;
        }
        .es-belt-field-v { font-size: 0.78rem; font-weight: 600; text-align: end; color: #e9eef3; }
        .es-belt-count {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            color: #8ecbff;
        }

        /* --- THE CENSUS TABLE ---------------------------------------- */
        .es-belt-table { width: 100%; border-collapse: collapse; text-align: start; }
        /* text-align has to be restated on th: the UA stylesheet centres it
           and that beats inheritance from the table. */
        .es-belt-table th, .es-belt-table td { vertical-align: top; padding: 0.85rem 0.75rem; text-align: start; }
        .es-belt-table thead th {
            padding-top: 0;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-belt-table thead th { color: #9aa6b2; }
        .es-belt-row { border-top: 1px solid rgba(16, 20, 24, 0.1); transition: background-color 0.2s ease; }
        .dark .es-belt-row { border-top-color: rgba(233, 238, 243, 0.1); }
        .es-belt-row:hover { background-color: rgba(15, 76, 129, 0.05); }
        .dark .es-belt-row:hover { background-color: rgba(142, 203, 255, 0.06); }
        .es-belt-tool { font-size: 0.95rem; font-weight: 700; color: #0f4c81; }
        .dark .es-belt-tool { color: #8ecbff; }
        .es-belt-tool:hover { text-decoration: underline; }

        /* --- Plan pills ---------------------------------------------- */
        .es-belt-tier {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.42rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .es-belt-tier-free { border-color: rgba(15, 76, 129, 0.45); color: #0f4c81; }
        .dark .es-belt-tier-free { border-color: rgba(142, 203, 255, 0.45); color: #8ecbff; }
        .es-belt-tier-pro { border-color: rgba(16, 20, 24, 0.35); color: #101418; }
        .dark .es-belt-tier-pro { border-color: rgba(233, 238, 243, 0.38); color: #e9eef3; }
        .es-belt-tier-ent { border-color: rgba(16, 20, 24, 0.2); color: #4b5158; }
        .dark .es-belt-tier-ent { border-color: rgba(233, 238, 243, 0.2); color: #9aa6b2; }

        /* --- Links and buttons --------------------------------------- */
        .es-belt-link { color: #0f4c81; }
        .es-belt-link:hover { color: #101418; }
        .dark .es-belt-link { color: #8ecbff; }
        .dark .es-belt-link:hover { color: #e9eef3; }
        .es-belt-btn {
            background-color: #0f4c81;
            color: #ffffff;
            box-shadow: 0 18px 36px -16px rgba(15, 76, 129, 0.55);
        }
        .es-belt-btn:hover { background-color: #0b4272; }
        .dark .es-belt-btn { background-color: #8ecbff; color: #0b0f13; }
        .dark .es-belt-btn:hover { background-color: #aed9ff; }
        /* Inside a strap the button is part of the fixed object, so it
           is lit in both modes. This rule follows the .dark rule above
           and wins on document order at equal specificity. */
        .es-belt-strap .es-belt-btn { background-color: #8ecbff; color: #0e141a; }
        .es-belt-strap .es-belt-btn:hover { background-color: #aed9ff; }
        .es-belt-ghost {
            border: 1px solid rgba(16, 20, 24, 0.18);
            background: #ffffff;
            color: #101418;
        }
        .es-belt-ghost:hover { border-color: rgba(15, 76, 129, 0.5); }
        .dark .es-belt-ghost { border-color: rgba(233, 238, 243, 0.2); background: #151b21; color: #e9eef3; }
        .dark .es-belt-ghost:hover { border-color: rgba(142, 203, 255, 0.5); }

        /* --- The buckle: the plate the claim input sits in ------------ */
        .es-belt-buckle {
            border: 1px solid rgba(142, 203, 255, 0.32);
            border-radius: 1.15rem;
            background: rgba(233, 238, 243, 0.045);
            padding: 0.4rem;
            box-shadow: inset 0 1px 0 rgba(233, 238, 243, 0.08);
        }

        /* --- Shared classes that flip with the colour mode. These
               overrides pin the strap so it renders identically with
               .dark on and off. They must stay AFTER the base rules. --- */
        .es-belt-strap .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 238, 243, 0.05) 1px, rgba(0, 0, 0, 0) 1px),
                linear-gradient(90deg, rgba(233, 238, 243, 0.05) 1px, rgba(0, 0, 0, 0) 1px);
        }
        .es-belt-strap .animate-shimmer {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0), rgba(255, 255, 255, 0.15), rgba(0, 0, 0, 0));
            background-size: 200% 100%;
        }
        .es-belt-strap .es-claim:focus-within {
            border-color: rgba(142, 203, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(142, 203, 255, 0.22);
        }
        /* The stamped tab is riveted to the strap, so it is lit in both
           modes too. Same document-order trick as the button. */
        .es-belt-strap .es-belt-tab {
            border-color: rgba(233, 238, 243, 0.2);
            background: #1b232b;
            color: #e9eef3;
        }
        .es-belt-strap .es-belt-tab::before { background: #8ecbff; }

        /* --- Shared-system recolours (brand blue by default) ---------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(15, 76, 129, 0.13), rgba(0, 0, 0, 0) 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(142, 203, 255, 0.1), rgba(0, 0, 0, 0) 60%);
        }
        .es-belt-page .es-glare {
            background: radial-gradient(620px circle at var(--gx, 50%) var(--gy, 50%), rgba(15, 76, 129, 0.09), rgba(0, 0, 0, 0) 45%);
        }
        .dark .es-belt-page .es-glare {
            background: radial-gradient(620px circle at var(--gx, 50%) var(--gy, 50%), rgba(233, 238, 243, 0.08), rgba(0, 0, 0, 0) 45%);
        }
        .es-belt-page .es-ring-glow {
            background: radial-gradient(420px circle at var(--gx, 50%) var(--gy, 50%), rgba(15, 76, 129, 0.6), rgba(142, 203, 255, 0.22) 45%, rgba(0, 0, 0, 0) 70%);
        }
        .dark .es-belt-page .es-ring-glow {
            background: radial-gradient(420px circle at var(--gx, 50%) var(--gy, 50%), rgba(142, 203, 255, 0.6), rgba(142, 203, 255, 0.2) 45%, rgba(0, 0, 0, 0) 70%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(15, 76, 129, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(142, 203, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0f4c81; }
        .dark .es-dot.is-active .es-dot-pip { background: #8ecbff; }

        /* --- Hero height --------------------------------------------- */
        .es-belt-hero { min-height: calc(80svh - 4rem); }

        /* --- Dot-nav tooltip. Its colours live here rather than in
               `dark:bg-[...]` utilities: an arbitrary value that is not
               already in the built CSS silently does nothing, which is
               how the label ended up grey-on-white in dark mode. ----- */
        .es-belt-tip { border: 1px solid rgba(16, 20, 24, 0.14); background: #ffffff; color: #101418; }
        .dark .es-belt-tip { border-color: rgba(233, 238, 243, 0.14); background: #151b21; color: #e9eef3; }

        /* --- Focus rings. No border-radius here: setting it would
               change the element's own shape on focus. ---------------- */
        #es-belt-page a:focus-visible,
        #es-belt-page summary:focus-visible,
        #es-belt-page button:focus-visible {
            outline: 2px solid #0f4c81;
            outline-offset: 3px;
        }
        .dark #es-belt-page a:focus-visible,
        .dark #es-belt-page summary:focus-visible,
        .dark #es-belt-page button:focus-visible {
            outline-color: #8ecbff;
        }
        .es-belt-strap a:focus-visible,
        .es-belt-strap button:focus-visible {
            outline-color: #8ecbff !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-belt-loop,
            .es-belt-loop-t,
            .es-belt-card,
            .es-belt-row { transition: none !important; }
            .es-belt-loop:hover { transform: none; }
        }
    </style>

    @php
        // The twelve loops, in the order the ItemList above declares them, so
        // the structured data and the visible page cannot drift. Each row:
        // number, tool, route, bench it came off, the job it was doing, what
        // does that job here, and the plans that covers.
        $loops = [
            ['L01', 'Google Forms', 'marketing.replace_google_forms', 'Registration and forms',
                'A form to collect sign-ups',
                'Free RSVP with a capacity kept per date, or named ticket types with your own questions at checkout.',
                ['Free', 'Pro']],
            ['L02', 'Mailchimp', 'marketing.replace_mailchimp', 'Marketing and communication',
                'Emailing everyone who came last time',
                'Newsletters to your followers and ticket buyers, with open and click rates and an optional subject-line A/B test.',
                ['Free']],
            ['L03', 'Canva', 'marketing.replace_canva', 'Marketing and communication',
                'Making the flyer, again',
                'Event graphics generated from the event\'s own fields, so a changed time redraws instead of being retyped.',
                ['Pro']],
            ['L04', 'Linktree', 'marketing.replace_linktree', 'Marketing and communication',
                'The one link in the bio',
                'The schedule page itself: your actual dates, a follow button, and every event page one tap off the same link.',
                ['Free']],
            ['L05', 'Google Sheets', 'marketing.replace_google_sheets', 'Scheduling and tracking',
                'The attendee list and the takings',
                'An attendee list per event, a sales dashboard, built-in analytics, and a CSV export when you do want a spreadsheet.',
                ['Free', 'Pro']],
            ['L06', 'Calendly', 'marketing.replace_calendly', 'Scheduling and tracking',
                'One-to-one bookings',
                'Appointment types with weekly hours, per-date overrides, buffers and optional payment, booked on a public page.',
                ['Free', 'Pro']],
            ['L07', 'SurveyMonkey', 'marketing.replace_surveymonkey', 'Registration and forms',
                'A survey doing registration duty',
                'Purpose-built registration, plus post-event feedback with star ratings and comments from the people who came.',
                ['Free', 'Pro']],
            ['L08', 'Doodle', 'marketing.replace_doodle', 'Registration and forms',
                'Asking everyone which date works',
                'Polls attached to the event itself: you set the options, guests vote, and they can suggest options for you to approve.',
                ['Pro']],
            ['L09', 'QR Code Generators', 'marketing.replace_qr_code_generators', 'Scheduling and tracking',
                'A code for the door',
                'A QR code on every ticket with a check-in dashboard behind it, and an ungated QR for the schedule page itself.',
                ['Free', 'Pro']],
            ['L10', 'Squarespace', 'marketing.replace_squarespace', 'Planning and websites',
                'The public website',
                'A schedule page with your branding, an embeddable calendar for the site you already have, custom CSS, or your own domain.',
                ['Free', 'Pro', 'Ent']],
            ['L11', 'Notion', 'marketing.replace_notion', 'Planning and websites',
                'The internal plan',
                'Sub-schedules to sort the programme, an agenda of parts inside an event, and templates for the events you run again.',
                ['Free', 'Pro']],
            ['L12', 'Trello', 'marketing.replace_trello', 'Planning and websites',
                'The board of who has asked to play',
                'A Requests tab: public submissions land there, email you, and become real events when you accept them.',
                ['Free']],
        ];

        // The plan pills are page-local classes, not Tailwind colour classes,
        // and the markup declares them through @class() so they stay visible
        // to the dead-class audit even though the condition is dynamic.

        // The twelve jobs, stripped of vendor names: what the funnel eats.
        $jobs = [
            ['L01', 'sign-ups'],
            ['L02', 'the mailout'],
            ['L03', 'the flyer'],
            ['L04', 'the bio link'],
            ['L05', 'the spreadsheet'],
            ['L06', 'bookings'],
            ['L07', 'feedback'],
            ['L08', 'the date poll'],
            ['L09', 'the door code'],
            ['L10', 'the website'],
            ['L11', 'the plan'],
            ['L12', 'the requests'],
        ];

        $faqs = [
            [
                'q' => 'How many tools can Event Schedule actually replace?',
                'a' => 'The twelve on this page, for the event-shaped part of what each one was doing. That means registration and ticketing, the schedule page, the flyer, the newsletter, the attendee list, appointment bookings, the door code, polls, feedback and the requests queue. It does not mean everything those products do: read the "what stays on the bench" section, which is deliberately specific about where the belt stops.',
            ],
            [
                'q' => 'Is one platform really cheaper than five?',
                'a' => 'Publishing a schedule is free forever, and that free plan already covers the page, unlimited events, two-way Google, Outlook and CalDAV sync, RSVP with a capacity per date, selling up to 25 paid tickets a month, one appointment type, built-in analytics, the embeddable calendar and newsletters at ten emails a month. Pro is '.plan_price($proMonthly).' a month and takes the ceiling off the selling, then adds QR check-in, event graphics, more appointment types and the API. Enterprise is '.plan_price($entMonthly).'. Event Schedule charges zero platform fees on ticket sales on every plan, free included, so the door money is yours minus Stripe\'s processing fee.',
            ],
            [
                'q' => 'What does Event Schedule not replace?',
                'a' => 'There is no automation or drip-campaign builder, so a nurture sequence stays where it is. There is no blank design canvas: graphics are generated from the event record in fixed layouts. It is not a general website builder, and there is no task board or kanban. Seat maps exist, but only on Enterprise: on the other plans ticket types are priced by the number and buyers are not choosing a specific seat.',
            ],
            [
                'q' => 'Do I have to move everything at once?',
                'a' => 'No, and most people should not. Two-way calendar sync means the dates keep flowing to the calendar your team already reads, and the embeddable calendar drops the same schedule into the site you already have, so the old page can stay up while you move. Then you can retire one loop at a time.',
            ],
            [
                'q' => 'Can I keep using my own domain and my own look?',
                'a' => 'Custom CSS on schedule pages is on the Pro plan, and running the schedule on your own domain is an Enterprise feature. On any plan you can embed the calendar, or the ticket form on Pro, into the website you already own, which is the usual answer if the site is not the thing you wanted to replace.',
            ],
            [
                'q' => 'What happens to my data if I want out again?',
                'a' => 'Backup and restore is on the free plan and exports your schedule data, with images if you want them. Pro adds a sales CSV export, a REST API and webhooks. Event Schedule is also open source, so the last resort is to selfhost the whole thing, where every Pro and Enterprise feature is switched on.',
            ],
        ];

        $dotSections = [
            ['top', 'The belt'],
            ['record', 'One record'],
            ['retype', 'The retyping'],
            ['belt', 'The twelve'],
            ['bench', 'What stays'],
            ['plans', 'The plans'],
            ['rest', 'Also on the belt'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-belt-page" class="es-belt-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the strap, twelve loops                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero es-belt-hero noise relative flex scroll-mt-24 items-center overflow-hidden py-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(15, 76, 129, 0.22), rgba(15, 76, 129, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(142, 203, 255, 0.14), rgba(142, 203, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                    <svg aria-hidden="true" class="es-belt-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h5l4.5 6H21" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 18h5l4.5-6" />
                    </svg>
                    <span class="es-belt-muted text-sm font-medium tracking-wide">One strap, twelve loops</span>
                </div>

                <h1 class="es-balance es-belt-ink mb-7 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                    <span class="es-mask"><span class="es-mask-line">Twelve tools to describe</span></span>
                    <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-belt-accent">one</span> event.</span></span>
                </h1>

                <p class="es-fade-up es-d-2 es-belt-muted es-belt-lead mx-auto mb-9 max-w-2xl">
                    The sign-up form, the flyer, the mailing list, the link in the bio, the spreadsheet, the code on the door. Twelve subscriptions, all holding a copy of the same date. Event Schedule keeps the date once and does the twelve jobs off it.
                </p>

                <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="#belt" class="es-belt-ghost group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                        See all twelve
                        <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                    <a href="{{ app_url('/sign_up') }}" class="es-belt-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                        Get Started Free
                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- The strap itself. Same object in both colour modes. -->
            <div class="es-fade-up es-d-4 mt-14" data-reveal>
                <div class="es-belt-strap es-belt-rail">
                    <div class="es-belt-stitch">
                        <div class="es-belt-loops">
                            @foreach ($loops as [$lNum, $lTool, $lRoute, $lBench, $lJob, $lHere, $lTiers])
                                <a href="{{ route($lRoute) }}" class="es-belt-loop">
                                    <span class="es-belt-loop-n" aria-hidden="true">{{ $lNum }}</span>
                                    <span class="es-belt-loop-t">{{ $lTool }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <p class="es-belt-on-muted es-belt-xs mt-3 text-center">
                        Twelve loops on one strap. Free to publish and to sell your first 25 tickets a month, {{ plan_price($proMonthly) }} a month for the paid half, and zero platform fees on every plan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The collapse: twelve jobs, one record (fixed strap band)  -->
    <!-- ============================================================ -->
    <section id="record" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-belt-strap noise relative overflow-hidden rounded-[2.5rem] px-4 py-16 sm:px-6 lg:px-10 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-belt-tab mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-belt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The collapse</p>
                    <h2 class="es-balance es-belt-on text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Twelve jobs. <span class="es-belt-lit">One record.</span>
                    </h2>
                    <p class="es-belt-on-muted es-belt-lead mx-auto mt-5 max-w-2xl" data-reveal style="--reveal-delay: 0.15s;">
                        Nothing on the left is a product here. They are readings of one event row, which is why the belt holds all of them without twelve places to keep the date.
                    </p>
                </div>

                <div class="es-belt-flow" data-reveal>
                    <ul class="es-belt-jobs">
                        @foreach ($jobs as [$jNum, $jName])
                            <li class="es-belt-job">
                                <span class="es-belt-job-n">{{ $jNum }}</span>
                                <span class="es-belt-job-t">{{ $jName }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="es-belt-spine" aria-hidden="true"></div>

                    <div class="es-belt-out">
                        <p class="es-belt-tag mb-3">The event</p>
                        <p class="es-belt-on mb-3 text-lg font-bold">One row, edited once</p>
                        <div>
                            <div class="es-belt-field">
                                <span class="es-belt-field-k">Name</span>
                                <span class="es-belt-field-v">Saturday Session</span>
                            </div>
                            <div class="es-belt-field">
                                <span class="es-belt-field-k">Starts</span>
                                <span class="es-belt-field-v">Sat 8 Aug, 21:00</span>
                            </div>
                            <div class="es-belt-field">
                                <span class="es-belt-field-k">Venue</span>
                                <span class="es-belt-field-v">The Old Fire Station</span>
                            </div>
                            <div class="es-belt-field">
                                <span class="es-belt-field-k">Tickets</span>
                                <span class="es-belt-field-v">2 types, 60 places</span>
                            </div>
                            <div class="es-belt-field">
                                <span class="es-belt-field-k">Details</span>
                                <span class="es-belt-field-v">Description and image</span>
                            </div>
                        </div>
                        <p class="es-belt-on-muted es-belt-xs mt-4">
                            Move the start time and the page, the ticket, the graphic and the synced calendar entry all move with it.
                        </p>
                    </div>
                </div>

                <div class="mt-14 grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-belt-out" data-reveal="panel">
                        <p class="es-belt-count mb-2"><span data-count-to="12">12</span> &rarr; 1</p>
                        <h3 class="es-belt-on mb-2 text-lg font-bold">Tools to learn</h3>
                        <p class="es-belt-on-muted es-belt-small">One interface a volunteer has to be shown, instead of a tour of a dozen of them.</p>
                    </div>
                    <div class="es-belt-out" data-reveal="panel">
                        <p class="es-belt-count mb-2"><span data-count-to="12">12</span> &rarr; 1</p>
                        <h3 class="es-belt-on mb-2 text-lg font-bold">Accounts</h3>
                        <p class="es-belt-on-muted es-belt-small">Twelve sign-ups, twelve password resets, twelve sets of terms. Here it is one account, free to publish or {{ plan_price($proMonthly) }} a month.</p>
                    </div>
                    <div class="es-belt-out" data-reveal="panel">
                        <p class="es-belt-count mb-2"><span data-count-to="12">12</span> &rarr; 1</p>
                        <h3 class="es-belt-on mb-2 text-lg font-bold">Copies of the date</h3>
                        <p class="es-belt-on-muted es-belt-small">The expensive number. Every extra copy of Saturday is another place it can be wrong.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The retyping                                             -->
    <!-- ============================================================ -->
    <section id="retype" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-belt-tab mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-belt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The real bill</p>
                <h2 class="es-balance es-belt-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The subscriptions are not the <span class="es-belt-accent">expensive part.</span>
                </h2>
                <p class="es-belt-muted es-belt-lead mt-5" data-reveal style="--reveal-delay: 0.15s;">
                    Twelve tools is really twelve copies of the same event, kept in step by hand. Here is what that costs, and what the belt does instead.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-belt-card flex flex-col p-7" data-reveal="panel">
                    <p class="es-belt-mono es-belt-xs es-belt-accent mb-2 font-bold">L03 &middot; L04 &middot; L05</p>
                    <h3 class="es-belt-ink mb-2 text-lg font-bold">The date drifts</h3>
                    <p class="es-belt-muted es-belt-small mb-4">The flyer says nine, the bio link still says eight, and the spreadsheet was right on Tuesday. Whoever finds out first is a guest at the wrong door.</p>
                    <p class="es-belt-accent es-belt-small mt-auto font-semibold">On the belt: the graphic is generated from the event's fields, so it cannot disagree with the page.</p>
                </div>
                <div class="es-belt-card flex flex-col p-7" data-reveal="panel">
                    <p class="es-belt-mono es-belt-xs es-belt-accent mb-2 font-bold">L01 &middot; L02 &middot; L05</p>
                    <h3 class="es-belt-ink mb-2 text-lg font-bold">The list splits</h3>
                    <p class="es-belt-muted es-belt-small mb-4">Sign-ups in the form, subscribers in the email tool, and the people who actually turned up in a third file nobody exports.</p>
                    <p class="es-belt-accent es-belt-small mt-auto font-semibold">On the belt: followers and ticket buyers are one audience, and the newsletter is written against it.</p>
                </div>
                <div class="es-belt-card flex flex-col p-7" data-reveal="panel">
                    <p class="es-belt-mono es-belt-xs es-belt-accent mb-2 font-bold">L01 &middot; L05 &middot; L09</p>
                    <h3 class="es-belt-ink mb-2 text-lg font-bold">Nothing knows about the ticket</h3>
                    <p class="es-belt-muted es-belt-small mb-4">A form cannot stop the sixty-first sign-up, a spreadsheet does not know the date sold out, and a code generator cannot tell you whether that code has already come through the door.</p>
                    <p class="es-belt-accent es-belt-small mt-auto font-semibold">On the belt: inventory and RSVP capacity are counted per date, and the door scans the ticket that was sold.</p>
                </div>
            </div>

            <p class="es-belt-muted es-belt-small mx-auto mt-8 max-w-3xl text-center" data-reveal>
                Ticket inventory and RSVP capacity are held per occurrence date, so a weekly night does not share one pot of places across the whole run.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The census: twelve rows, twelve links                    -->
    <!-- ============================================================ -->
    <section id="belt" class="scroll-mt-24 border-y border-gray-200 py-20 dark:border-white/10 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-belt-tab mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-belt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The census</p>
                <h2 class="es-balance es-belt-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    What is <span class="es-belt-accent">on the belt.</span>
                </h2>
                <p class="es-belt-muted es-belt-lead mt-5" data-reveal style="--reveal-delay: 0.15s;">
                    Twelve tools, the job each one was doing, and what does that job here. Every row links to the detail.
                </p>
            </div>

            <div class="es-belt-card p-4 sm:p-7" data-reveal="panel">
                <table class="es-belt-table">
                    <caption class="sr-only">Twelve tools Event Schedule can absorb, the job each one was doing, what replaces it, and the plan that includes it</caption>
                    <thead>
                        <tr>
                            <th scope="col">Tool</th>
                            <th scope="col" class="hidden md:table-cell">The job it was doing</th>
                            <th scope="col">What does it here</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loops as [$lNum, $lTool, $lRoute, $lBench, $lJob, $lHere, $lTiers])
                            <tr class="es-belt-row">
                                <th scope="row" class="font-normal">
                                    <span class="es-belt-mono es-belt-xs es-belt-muted block font-bold">{{ $lNum }}</span>
                                    <a href="{{ route($lRoute) }}" class="es-belt-tool">{{ $lTool }}</a>
                                    <span class="es-belt-muted es-belt-xs mt-1 block">{{ $lBench }}</span>
                                </th>
                                <td class="es-belt-muted es-belt-small hidden md:table-cell">{{ $lJob }}</td>
                                <td>
                                    <span class="es-belt-ink es-belt-small block">{{ $lHere }}</span>
                                    <span class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($lTiers as $lTier)
                                            <span @class([
                                                'es-belt-tier',
                                                'es-belt-tier-free' => $lTier === 'Free',
                                                'es-belt-tier-pro' => $lTier === 'Pro',
                                                'es-belt-tier-ent' => $lTier === 'Ent',
                                            ])>{{ $lTier }}</span>
                                        @endforeach
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-belt-muted es-belt-xs mt-5">
                    Free is free forever. Pro is {{ plan_price($proMonthly) }} a month, Enterprise is {{ plan_price($entMonthly) }}, and a selfhosted install has every one of them switched on. Where a row shows more than one pill, the basic job is free and the paid pills mark the parts that are not.
                </p>
            </div>

            <div class="mx-auto mt-10 max-w-3xl" data-reveal>
                <a href="{{ route('marketing.compare') }}" class="es-belt-card es-belt-hover group flex items-center justify-between p-7 hover:-translate-y-1">
                    <div>
                        <h3 class="es-belt-hover-title es-belt-ink mb-2 text-xl font-bold transition-colors">Looking for direct platform comparisons?</h3>
                        <p class="es-belt-muted es-belt-small">These twelve were never event platforms. For the ones that are, see how Event Schedule compares to Eventbrite, Luma and Ticket Tailor.</p>
                    </div>
                    <svg aria-hidden="true" class="es-belt-hover-arrow es-belt-muted ms-6 h-6 w-6 shrink-0 transition-colors rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. What stays on the bench                                  -->
    <!-- ============================================================ -->
    <section id="bench" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-belt-tab mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-belt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Where the belt stops</p>
                <h2 class="es-balance es-belt-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Four things that <span class="es-belt-accent">stay on the bench.</span>
                </h2>
                <p class="es-belt-muted es-belt-lead mt-5" data-reveal style="--reveal-delay: 0.15s;">
                    A consolidation page that claims everything is not worth reading. These are the places where you should keep the specialist.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-belt-card p-7" data-reveal="panel">
                    <h3 class="es-belt-ink mb-2 text-lg font-bold">Automations and drip sequences</h3>
                    <p class="es-belt-muted es-belt-small">Newsletters here are written and sent by you, with open and click rates and an optional subject-line A/B test. There is no automation builder, no branching sequence and no drip. The one thing that does send itself is a digest of newly published events to people who left an email address and confirmed it, which is a fact about the calendar rather than a campaign you build. If you run nurture flows, keep the email platform.</p>
                </div>
                <div class="es-belt-card p-7" data-reveal="panel">
                    <h3 class="es-belt-ink mb-2 text-lg font-bold">A blank design canvas</h3>
                    <p class="es-belt-muted es-belt-small">Event graphics are generated from the event's own fields into set layouts, in the sizes the platforms want. That is what kills the retyping. It is not a place to design a poster from nothing, so an art department keeps its design tool.</p>
                </div>
                <div class="es-belt-card p-7" data-reveal="panel">
                    <h3 class="es-belt-ink mb-2 text-lg font-bold">A general website builder</h3>
                    <p class="es-belt-muted es-belt-small">You get a schedule page and event pages, an embeddable calendar, custom CSS on Pro and your own domain on Enterprise. There is no page builder for an "about us" or a shop, so a site that is more than its events stays where it is, with the calendar embedded in it.</p>
                </div>
                <div class="es-belt-card p-7" data-reveal="panel">
                    <h3 class="es-belt-ink mb-2 text-lg font-bold">Task boards and free-form databases</h3>
                    <p class="es-belt-muted es-belt-small">Planning here is event-shaped: sub-schedules, an agenda of parts inside an event, templates for the ones you repeat, and a Requests tab for what the public sends in. There is no kanban and no arbitrary table, so a production board with cards for the van hire stays on its board.</p>
                </div>
            </div>

            <p class="es-belt-muted es-belt-small mx-auto mt-8 max-w-3xl text-center" data-reveal>
                One more, because it gets asked: a seating chart is an Enterprise feature. Below that, ticket types are named and priced, and a buyer picks a type rather than a seat.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Where the twelve sit in the plans                        -->
    <!-- ============================================================ -->
    <section id="plans" class="scroll-mt-24 border-t border-gray-200 py-20 dark:border-white/10 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-belt-tab mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-belt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The plans</p>
                <h2 class="es-balance es-belt-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Which loop sits on <span class="es-belt-accent">which plan.</span>
                </h2>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-belt-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-baseline gap-2">
                        <span class="es-belt-ink text-3xl font-black">{{ plan_price(0) }}</span>
                        <span class="es-belt-tier es-belt-tier-free">Free</span>
                    </div>
                    <h3 class="es-belt-ink mb-3 text-lg font-bold">Publish, sell, be found</h3>
                    <ul class="es-belt-muted es-belt-small space-y-2">
                        <li>The schedule page and unlimited events</li>
                        <li>Two-way Google, Outlook and CalDAV sync</li>
                        <li>Free RSVP with a capacity per date</li>
                        <li>Selling, up to 25 paid tickets a month, no platform fee</li>
                        <li>One appointment type on a public booking page</li>
                        <li>Newsletters, ten emails a month, counting each recipient as one</li>
                        <li>Built-in analytics and the embeddable calendar</li>
                        <li>A QR code for the schedule page</li>
                        <li>Sub-schedules, agenda parts, recurring dates</li>
                        <li>Backup and restore, images included</li>
                    </ul>
                    <p class="es-belt-muted es-belt-xs mt-auto pt-5">Covers all of L02, L04 and L12, and the unpaid half of L01, L05, L06, L07, L09, L10 and L11.</p>
                </div>

                <div class="es-belt-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-baseline gap-2">
                        <span class="es-belt-ink text-3xl font-black">{{ plan_price($proMonthly) }}</span>
                        <span class="es-belt-tier es-belt-tier-pro">Pro</span>
                    </div>
                    <h3 class="es-belt-ink mb-3 text-lg font-bold">Sell without a ceiling</h3>
                    <ul class="es-belt-muted es-belt-small space-y-2">
                        <li>Unlimited ticket sales, QR check-in and a check-in dashboard</li>
                        <li>Passes, subscriptions and individual tickets</li>
                        <li>Event graphics generated from the event</li>
                        <li>As many appointment types as you need</li>
                        <li>Your own questions at checkout, promo codes, waitlist</li>
                        <li>Polls, post-event feedback, sales CSV export</li>
                        <li>REST API, webhooks, custom CSS, ticket widget embed</li>
                        <li>One hundred newsletter emails a month</li>
                    </ul>
                    <p class="es-belt-muted es-belt-xs mt-auto pt-5">Covers all of L03 and L08, and the paid half of L01, L05, L06, L07, L09, L10 and L11.</p>
                </div>

                <div class="es-belt-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-4 flex items-baseline gap-2">
                        <span class="es-belt-ink text-3xl font-black">{{ plan_price($entMonthly) }}</span>
                        <span class="es-belt-tier es-belt-tier-ent">Ent</span>
                    </div>
                    <h3 class="es-belt-ink mb-3 text-lg font-bold">Your domain, your team</h3>
                    <ul class="es-belt-muted es-belt-small space-y-2">
                        <li>Your own domain on the schedule</li>
                        <li>Up to five team members</li>
                        <li>Internal and unlisted event visibility</li>
                        <li>AI agenda scanning into event parts</li>
                        <li>One thousand newsletter emails a month</li>
                    </ul>
                    <p class="es-belt-muted es-belt-xs mt-auto pt-5">Finishes L10, and adds the parts a staffed organisation needs.</p>
                </div>
            </div>

            <p class="es-belt-muted es-belt-small mx-auto mt-8 max-w-3xl text-center" data-reveal>
                Selfhost it instead and every Pro and Enterprise feature is on, because a selfhosted install resolves to the top tier.
            </p>

            <div class="mt-6 text-center" data-reveal>
                <a href="{{ marketing_url('/pricing') }}" class="es-belt-link inline-flex items-center font-medium hover:underline">
                    See the plans in full
                    <svg aria-hidden="true" class="ms-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Also on the belt: bento                                  -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-belt-tab mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-belt-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Also on the belt</p>
                <h2 class="es-balance es-belt-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Things none of the twelve <span class="es-belt-accent">were doing at all.</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-belt-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <span class="es-belt-tier es-belt-tier-free mb-3 inline-flex">Free</span>
                            <h3 class="es-belt-ink mb-2 text-xl font-bold">Zero platform fees on ticket sales</h3>
                            <p class="es-belt-muted es-belt-small">Money moves through your own Stripe account and Event Schedule takes none of it, on every plan including the free one. The only cut is Stripe's processing fee, which is the same cut it would take anywhere else. This is usually the line that pays for the whole switch.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-belt-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <span class="es-belt-tier es-belt-tier-free mb-3 inline-flex">Free</span>
                            <h3 class="es-belt-ink mb-2 text-xl font-bold">Two-way calendar sync</h3>
                            <p class="es-belt-muted es-belt-small">Google, Outlook and CalDAV, both directions, on the free plan. The dates keep arriving in the calendar your team already reads.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-belt-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <span class="es-belt-tier es-belt-tier-free mb-3 inline-flex">Free</span>
                            <h3 class="es-belt-ink mb-2 text-xl font-bold">Embed the calendar</h3>
                            <p class="es-belt-muted es-belt-small">Drop your schedule into the website you already have, so the site does not need replacing for the dates to be right.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-belt-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <span class="es-belt-tier es-belt-tier-free mb-3 inline-flex">Free</span>
                            <h3 class="es-belt-ink mb-2 text-xl font-bold">Open source, and yours to selfhost</h3>
                            <p class="es-belt-muted es-belt-small">The whole platform is open source. Run it on your own server and every Pro and Enterprise feature is switched on, which is a different kind of answer to lock-in than an export button. Backup and restore is on the free plan either way.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-belt-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <span class="es-belt-tier es-belt-tier-free mb-3 inline-flex">Free</span>
                            <h3 class="es-belt-ink mb-2 text-xl font-bold">Analytics built in</h3>
                            <p class="es-belt-muted es-belt-small">Views, referrers and where your audience is reading from, on the schedule itself, with no tag to install.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-belt-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <span class="es-belt-tier es-belt-tier-pro mb-3 inline-flex">Pro</span>
                            <h3 class="es-belt-ink mb-2 text-xl font-bold">An API and webhooks, for the tool you keep</h3>
                            <p class="es-belt-muted es-belt-small">If one loop stays on the bench, wire it in instead of retyping into it: a REST API over events, schedules, sales and sub-schedules, plus webhooks that fire on sales, event changes and check-ins.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 8. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 border-t border-gray-200 py-20 dark:border-white/10 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-belt-tab mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <h2 class="es-balance es-belt-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-belt-muted es-belt-lead" data-reveal style="--reveal-delay: 0.1s;">
                    What people ask before they take a tool off the belt.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-belt-card es-belt-hover group p-6" data-reveal>
                        <summary class="es-belt-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-belt-accent es-belt-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-belt-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-belt-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-belt-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Finale: the buckle                                       -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-belt-strap noise relative overflow-hidden rounded-[2.5rem] px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-belt-tag mb-4">Free forever</p>
                    <h2 class="es-balance es-belt-on mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight md:text-5xl">
                        Twelve loops. <span class="es-belt-lit">One strap.</span>
                    </h2>
                    <p class="es-belt-on-muted es-belt-lead mx-auto mb-10 max-w-2xl">
                        Take the name, publish your dates, and move the rest across a loop at a time. No credit card, and nothing taken from the door.
                    </p>

                    <div class="es-belt-buckle mx-auto max-w-2xl">
                        <div class="flex flex-col items-stretch justify-center gap-3 sm:flex-row">
                            <label for="es-claim-input" class="sr-only">Your schedule name</label>
                            <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                                <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                                <span class="es-belt-on-muted shrink-0 select-none font-mono text-sm sm:text-base">.eventschedule.com</span>
                            </div>
                            <a href="{{ app_url('/sign_up') }}" class="es-belt-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                                <span class="relative z-10 flex items-center gap-2">
                                    Get Started Free
                                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </span>
                                <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>

                    <p class="es-belt-on-muted es-belt-xs mt-6">Zero platform fees on ticket sales. You only pay Stripe's processing fee.</p>
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
                        <span class="es-belt-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <!-- Local confetti (no CDN) + motion engines -->
    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
