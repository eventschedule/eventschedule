<x-marketing-layout>
    <x-slot name="title">{{ __('marketing.about_title') }}</x-slot>
    <x-slot name="description">{{ __('marketing.about_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">About</x-slot>

    <x-slot name="structuredData">
    {{-- The layout already emits the full Organization node at {app.url}/#organization: name,
         url, logo, description, foundingDate, sameAs and contactPoint. Repeating it here without
         that @id produced a SECOND, unrelated organization on this one page. Carrying the same
         @id makes this an extension of that node instead, so it only has to say what the layout
         does not - which on the About page is what the organization knows about. --}}
    @php
        $aboutOrganization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => config('app.url').'/#organization',
            'knowsAbout' => ['Event Management', 'Ticketing', 'Calendar Synchronization', 'Open Source Software'],
        ];
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {!! json_encode($aboutOrganization, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
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
           About "The Colophon" styles. A colophon is the note at the
           back of a book: who printed it, what it was set in, and the
           terms you may reuse it under. An About page is the same
           document, so this page is built as one - a paper leaf, a
           signature mark per gathering, and an imprint set as a real
           table of checkable facts.

           THE GATHERING LETTERS ARE THE NAVIGATION. Sections carry the
           binder's mark B to K, and the dot nav repeats the same boxed
           letters (.es-colo-navmark) so the sequence is the wayfinding.
           The first gathering and the back matter are deliberately
           UNSIGNED, which is the real convention: a title leaf does not
           carry a signature. Do not "fix" the missing A.

           SECTION G IS A SHEET, NOT A CARD ROW. Standing rules is set as
           one ruled leaf (.es-colo-standing-row) with the numeral hanging
           in the margin as a press figure, because three card rows in a
           row was the page's weakest rhythm. Keep it a sheet.

           WHY THIS CONCEPT ARGUES THE PRODUCT. The three genuinely
           verifiable claims here (the source is public, you can run it
           yourself, no platform fee is taken on ticket sales) are
           exactly the three lines a colophon carries: printer, stock,
           terms. The metaphor and the feature story are one sentence.

           TYPOGRAPHY IS THE SIGNATURE, NOT COLOUR. Every other rebuilt
           WP page is set in the sans stack with a gradient heading. This
           one is set in a system serif with FLAT ink and square corners,
           which is why it reads as a printed leaf rather than a card
           deck. That also removes the commonest contrast failure in this
           codebase: there is no gradient heading text to score.

           COLOUR: the page's inherited blue, spent as a letterpress
           SECOND INK - one flat blue, never a gradient, at most one word
           per heading. #1d4ed8 measures 6.15 on the paper ground and
           #7ea6ff measures 8.02 on the night ground. The shared
           blue -> sky -> cyan chrome gradient is deliberately NOT
           adopted as a page accent.

           NEVER use text-gray-500 here: 4.83 on pure white but only
           ~4.4 on this paper ground. Use .es-colo-muted (7.34 light,
           7.29 dark).

           NO decorative outline illustration anywhere: the printer's
           device is typographic (a ruled square with a monogram), the
           rules are abstract strokes, and the paper is a repeating
           gradient. See CLAUDE.md.

           BLADE RULE for this block: no @supports() probes - a "#" hex
           inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground, stock and ink ------------------------------------ */
        .es-colo-page {
            background-color: #f7f5f0;
            color: #191a1e;
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
        }
        .dark .es-colo-page { background-color: #0e0f12; color: #eceae4; }

        .es-colo-ink { color: #191a1e; }
        .dark .es-colo-ink { color: #eceae4; }
        .es-colo-muted { color: #4c5158; }
        .dark .es-colo-muted { color: #9aa0ab; }
        /* The second ink. One flat blue, no gradient. */
        .es-colo-second { color: #1d4ed8; }
        .dark .es-colo-second { color: #7ea6ff; }
        /* Always-lit second ink, for the bands that stay dark in both modes. */
        .es-colo-lit { color: #8fb3ff; }

        /* Laid paper: the fine chain lines of the stock. Sits under the
           content as its own layer so it never tints text. */
        .es-colo-laid {
            background-image: repeating-linear-gradient(
                0deg,
                rgba(25, 26, 30, 0.05) 0px,
                rgba(25, 26, 30, 0.05) 1px,
                transparent 1px,
                transparent 5px);
            opacity: 0.6;
        }
        .dark .es-colo-laid {
            background-image: repeating-linear-gradient(
                0deg,
                rgba(236, 234, 228, 0.05) 0px,
                rgba(236, 234, 228, 0.05) 1px,
                transparent 1px,
                transparent 5px);
            opacity: 0.5;
        }

        /* A leaf. Square corners on purpose: paper is cut, not rounded. */
        .es-colo-leaf {
            background-color: #fffefa;
            border: 1px solid rgba(25, 26, 30, 0.14);
            border-radius: 2px;
            box-shadow: 0 1px 0 rgba(25, 26, 30, 0.05), 0 14px 30px -22px rgba(25, 26, 30, 0.4);
        }
        .dark .es-colo-leaf {
            background-color: #16181c;
            border-color: rgba(236, 234, 228, 0.14);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.45);
        }

        /* A tipped-in slip: the errata, on different stock. */
        .es-colo-slip {
            background-color: #f1ece0;
            border: 1px dashed rgba(25, 26, 30, 0.3);
            border-radius: 2px;
        }
        .dark .es-colo-slip {
            background-color: #191b20;
            border-color: rgba(236, 234, 228, 0.28);
        }

        /* --- The pressroom: a band that stays dark in both modes.
               Only the GROUND lives here. Every ink override for the band is
               pinned at the very bottom of this block, because it has to
               outrank the `.dark` rules that follow, and equal-specificity
               rules are decided by source order. ---------------------- */
        .es-colo-press {
            background-color: #0c0d10;
            background-image: radial-gradient(120% 100% at 50% 0%, #191b21 0%, #101216 55%, #08090b 100%);
            /* A fixed rgba border, not a `dark:` utility: in dark mode the band and
               the page ground are close in value, and this is what keeps the edge
               of the object readable without making it differ between modes. */
            border: 1px solid rgba(236, 234, 228, 0.08);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(236, 234, 228, 0.05);
        }

        /* --- Type ---------------------------------------------------- */
        .es-colo-title {
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-weight: 700;
            letter-spacing: -0.015em;
        }
        .es-colo-h1 { font-size: 2.3rem; line-height: 1.08; }
        .es-colo-h2 { font-size: 1.75rem; line-height: 1.14; }

        .es-colo-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-colo-fig {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            font-size: 1.9rem;
            line-height: 1;
            letter-spacing: -0.02em;
        }

        /* Eyebrow, set as a compositor's slug. */
        .es-colo-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #4c5158;
        }
        .dark .es-colo-tag { color: #9aa0ab; }

        /* Signature mark: the letter printed at the foot of each gathering so
           the binder knows the order of the sections. Here it numbers the page. */
        .es-colo-sig {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4c5158;
        }
        .dark .es-colo-sig { color: #9aa0ab; }
        .es-colo-sig::after {
            content: "";
            flex: none;
            width: 2.5rem;
            height: 1px;
            background: rgba(25, 26, 30, 0.22);
        }
        .dark .es-colo-sig::after { background: rgba(236, 234, 228, 0.22); }
        .es-colo-sig-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: none;
            width: 1.7rem;
            height: 1.7rem;
            border: 1px solid rgba(25, 26, 30, 0.32);
            border-radius: 2px;
            font-size: 0.74rem;
            color: #191a1e;
        }
        .dark .es-colo-sig-mark { border-color: rgba(236, 234, 228, 0.3); color: #eceae4; }

        /* The dot nav carries the gathering letter too, boxed like the section
           mark. Colour is inherited from the tooltip, so it adds no new
           contrast pair to measure. */
        .es-colo-navmark {
            display: inline-block;
            min-width: 1.05rem;
            padding: 0 0.18rem;
            margin-inline-end: 0.1rem;
            border: 1px solid rgba(25, 26, 30, 0.32);
            border-radius: 2px;
            text-align: center;
            font-weight: 700;
        }
        .dark .es-colo-navmark { border-color: rgba(236, 234, 228, 0.3); }

        /* Standing rules, set as one ruled sheet rather than a third card row.
           The numeral hangs in the margin like a press figure: the small
           number printed at the foot of a sheet to identify who pulled it. */
        .es-colo-standing-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.3rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(25, 26, 30, 0.12);
        }
        .dark .es-colo-standing-row { border-top-color: rgba(236, 234, 228, 0.14); }
        .es-colo-standing-row:first-child { margin-top: 0; padding-top: 0; border-top: 0; }

        /* Printer's double rule: thick over thin. */
        .es-colo-rule {
            height: 3px;
            border-top: 2px solid rgba(25, 26, 30, 0.55);
            border-bottom: 1px solid rgba(25, 26, 30, 0.28);
        }
        .dark .es-colo-rule { border-top-color: rgba(236, 234, 228, 0.5); border-bottom-color: rgba(236, 234, 228, 0.25); }

        /* Hairline, for inside a leaf. */
        .es-colo-hair { height: 1px; background: rgba(25, 26, 30, 0.12); }
        .dark .es-colo-hair { background: rgba(236, 234, 228, 0.14); }

        /* The opening paragraph takes a drop cap, as a press note does. */
        .es-colo-drop::first-letter {
            float: left;
            margin-right: 0.07em;
            font-size: 3.4em;
            line-height: 0.84;
            font-weight: 700;
            color: #1d4ed8;
        }
        .dark .es-colo-drop::first-letter { color: #7ea6ff; }
        [dir="rtl"] .es-colo-drop::first-letter { float: right; margin-right: 0; margin-left: 0.07em; }

        /* --- The printer's device: typographic, not an illustration --- */
        .es-colo-mark {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex: none;
            width: 4.25rem;
            height: 4.25rem;
            border: 1px solid rgba(25, 26, 30, 0.32);
            border-radius: 2px;
            background-color: #fffefa;
        }
        .dark .es-colo-mark { border-color: rgba(236, 234, 228, 0.3); background-color: #16181c; }
        .es-colo-mark-glyph {
            font-family: ui-serif, Georgia, "Times New Roman", Times, serif;
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            line-height: 1;
            color: #1d4ed8;
        }
        .dark .es-colo-mark-glyph { color: #7ea6ff; }
        .es-colo-mark::after {
            content: "";
            width: 1.6rem;
            height: 1px;
            margin-top: 0.4rem;
            background: rgba(25, 26, 30, 0.35);
        }
        .dark .es-colo-mark::after { background: rgba(236, 234, 228, 0.32); }

        /* --- The imprint: a real table, because it is a record -------- */
        .es-colo-imprint { width: 100%; border-collapse: collapse; }
        .es-colo-imprint th,
        .es-colo-imprint td {
            padding: 0.9rem 0.7rem;
            vertical-align: top;
            text-align: start;
            border-top: 1px solid rgba(25, 26, 30, 0.12);
        }
        .dark .es-colo-imprint th,
        .dark .es-colo-imprint td { border-top-color: rgba(236, 234, 228, 0.13); }
        .es-colo-imprint thead th {
            border-top: 0;
            border-bottom: 2px solid rgba(25, 26, 30, 0.5);
            padding-bottom: 0.45rem;
        }
        .dark .es-colo-imprint thead th { border-bottom-color: rgba(236, 234, 228, 0.4); }
        .es-colo-imprint tbody th { width: 34%; font-weight: 700; }

        /* --- The gutter: two impressions of one book ------------------ */
        .es-colo-duplex { position: relative; }

        /* --- Pills, buttons, links ----------------------------------- */
        .es-colo-pill {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.42rem;
            border: 1px solid rgba(29, 78, 216, 0.45);
            border-radius: 2px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #1d4ed8;
        }
        .dark .es-colo-pill { border-color: rgba(126, 166, 255, 0.45); color: #7ea6ff; }
        .es-colo-pill-paid { border-color: rgba(25, 26, 30, 0.38); color: #191a1e; }
        .dark .es-colo-pill-paid { border-color: rgba(236, 234, 228, 0.38); color: #eceae4; }

        .es-colo-btn {
            background-color: #1d4ed8;
            border: 1px solid #1d4ed8;
            color: #ffffff;
            border-radius: 3px;
            box-shadow: 0 16px 32px -16px rgba(29, 78, 216, 0.55);
        }
        .es-colo-btn:hover { background-color: #1a3fae; border-color: #1a3fae; }
        .dark .es-colo-btn { background-color: #7ea6ff; border-color: #7ea6ff; color: #0e0f12; box-shadow: none; }
        .dark .es-colo-btn:hover { background-color: #9dbcff; border-color: #9dbcff; }

        .es-colo-ghost {
            background-color: #fffefa;
            border: 1px solid rgba(25, 26, 30, 0.24);
            color: #191a1e;
            border-radius: 3px;
        }
        .es-colo-ghost:hover { background-color: #efeade; border-color: rgba(25, 26, 30, 0.45); }
        .dark .es-colo-ghost { background-color: #16181c; border-color: rgba(236, 234, 228, 0.24); color: #eceae4; }
        .dark .es-colo-ghost:hover { background-color: #1e2127; border-color: rgba(236, 234, 228, 0.45); }

        .es-colo-link { color: #1d4ed8; text-underline-offset: 3px; }
        .es-colo-link:hover { text-decoration: underline; }
        .dark .es-colo-link { color: #7ea6ff; }

        .es-colo-hover:hover { border-color: rgba(29, 78, 216, 0.5); }
        .dark .es-colo-hover:hover { border-color: rgba(126, 166, 255, 0.5); }
        .es-colo-hover:hover .es-colo-hover-title,
        .es-colo-hover:hover .es-colo-hover-arrow { color: #1d4ed8; }
        .dark .es-colo-hover:hover .es-colo-hover-title,
        .dark .es-colo-hover:hover .es-colo-hover-arrow { color: #7ea6ff; }

        /* --- Recolour the shared systems away from brand blue-cyan ---- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.1), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(126, 166, 255, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(126, 166, 255, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #7ea6ff; }

        /* ==============================================================
           PINNING THE PRESSROOM. The band is one physical object, so it
           must render identically with `.dark` on and off. Every rule
           here therefore has to come AFTER the matching `.dark` rule
           above: they are equal specificity, so source order decides.
           `.grid-overlay`, `.animate-shimmer` and `.es-claim:focus-within`
           carry their own `.dark` rules in marketing.css and are pinned
           for the same reason. Verified with the verifier's --bands flag.
           ============================================================== */
        .es-colo-press .es-colo-leaf {
            background-color: #17191e;
            border-color: rgba(236, 234, 228, 0.14);
            box-shadow: none;
        }
        .es-colo-press .es-colo-ink { color: #eceae4; }
        .es-colo-press .es-colo-muted { color: #9aa0ab; }
        .es-colo-press .es-colo-tag { color: #8fb3ff; }
        .es-colo-press .es-colo-sig { color: #9aa0ab; }
        .es-colo-press .es-colo-sig::after { background: rgba(236, 234, 228, 0.22); }
        .es-colo-press .es-colo-sig-mark { border-color: rgba(236, 234, 228, 0.3); color: #eceae4; }
        .es-colo-press .es-colo-rule { border-top-color: rgba(236, 234, 228, 0.5); border-bottom-color: rgba(236, 234, 228, 0.25); }
        .es-colo-press .es-colo-link { color: #8fb3ff; }
        .es-colo-press .es-colo-btn { background-color: #8fb3ff; border-color: #8fb3ff; color: #0c0d10; box-shadow: none; }
        .es-colo-press .es-colo-btn:hover { background-color: #adc8ff; border-color: #adc8ff; }
        .es-colo-press .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 234, 228, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 234, 228, 0.05) 1px, transparent 1px);
        }
        .es-colo-press .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-colo-press .es-claim:focus-within {
            border-color: rgba(143, 179, 255, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 179, 255, 0.22);
        }

        /* --- Focus. No border-radius here: setting it reshapes the
               element on focus, and outlines already follow its shape. -- */
        #es-colo-page a:focus-visible,
        #es-colo-page summary:focus-visible,
        #es-colo-page button:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-colo-page a:focus-visible,
        .dark #es-colo-page summary:focus-visible,
        .dark #es-colo-page button:focus-visible { outline-color: #7ea6ff; }
        .es-colo-press a:focus-visible,
        .es-colo-press summary:focus-visible,
        .es-colo-press button:focus-visible { outline-color: #8fb3ff !important; }

        /* --- Motion. Two devices, both resting in their finished state
               for no-JS visitors, crawlers and reduced-motion users. ---- */
        /* 1. The device is struck onto the leaf. */
        html.es-anim .es-colo-mark {
            animation: es-colo-strike 0.9s cubic-bezier(0.22, 1, 0.36, 1) 0.35s both;
        }
        @keyframes es-colo-strike {
            from { opacity: 0; transform: scale(1.16); }
            to { opacity: 1; transform: none; }
        }
        /* 2. Each section's rule is drawn from the spine outwards. Rest state
              is DRAWN; only the undrawn pre-state is gated. */
        .es-colo-draw { transform-origin: left center; transition: transform 1s cubic-bezier(0.22, 1, 0.36, 1) 0.1s; }
        [dir="rtl"] .es-colo-draw { transform-origin: right center; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-colo-draw { transform: scaleX(0); }

        @media (min-width: 640px) {
            .es-colo-h1 { font-size: 3.4rem; }
            .es-colo-h2 { font-size: 2.2rem; }
            .es-colo-standing-row { grid-template-columns: 4rem 1fr; gap: 1.5rem; }
        }
        @media (min-width: 768px) {
            .es-colo-duplex::before {
                content: "";
                position: absolute;
                top: 0;
                bottom: 0;
                left: 50%;
                width: 1px;
                background: rgba(25, 26, 30, 0.18);
            }
            .dark .es-colo-duplex::before { background: rgba(236, 234, 228, 0.18); }
        }
        @media (min-width: 1024px) {
            .es-colo-h1 { font-size: 3.9rem; }
            .es-colo-h2 { font-size: 2.5rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            html.es-anim .es-colo-mark { animation: none; }
            .es-colo-draw { transition: none; transform: none !important; }
        }
    </style>

    @php
        // The imprint. Every row is a fact that can be checked somewhere else:
        // the LICENSE file in the repo, docs/FEATURES.md, the privacy policy, or
        // the git history. Nothing here is a number we made up.
        $sourceNote = 'Public on GitHub. Read it, fork it, or open an issue against it.';
        if ($githubStars ?? null) {
            $sourceNote .= ' ' . number_format($githubStars) . ' stars so far.';
        }

        $imprint = [
            [
                'Publisher',
                'Event Schedule, built by the team behind Invoice Ninja, a source-available invoicing platform.',
                'https://invoiceninja.com', 'invoiceninja.com', true,
            ],
            [
                'License',
                'The Attribution Assurance License. The full text is the LICENSE file in the repository, not a summary of it.',
                'https://github.com/eventschedule/eventschedule/blob/main/LICENSE', 'Read the license', true,
            ],
            [
                'Source',
                $sourceNote,
                'https://github.com/eventschedule/eventschedule', 'github.com/eventschedule', true,
            ],
            [
                'Set in',
                'PHP and Laravel on the server, Vue on the front end, MySQL underneath. No part of it is a black box you cannot open.',
                null, null, false,
            ],
            [
                'Impression',
                'Hosted at eventschedule.com, or selfhosted on hardware you control. A selfhosted install resolves to the Enterprise tier, so no paid plan holds anything back from it.',
                marketing_url('/selfhost'), 'How selfhosting works', false,
            ],
            [
                'Platform fee',
                'None, on any plan. Tickets are sold through your own Stripe account and settle into it directly, so past Stripe\'s own processing charge the money is yours.',
                marketing_url('/features/ticketing'), 'How ticketing works', false,
            ],
            [
                'Free plan',
                plan_price(0).', with no credit card and no expiry. One team member. Pro is '.plan_price($proMonthly).' a month and Enterprise is '.plan_price($entMonthly).'.',
                marketing_url('/pricing'), 'See the plans', false,
            ],
            [
                'Analytics',
                'Opt-in. Analytics, advertising and personalization signals are set to denied until you press Allow in the cookie banner, and declining sets no analytics cookies at all.',
                policy_url('privacy'), 'Privacy policy', false,
            ],
            [
                'First edition',
                'The first commit landed in July 2024, and the repository has carried every change since in public.',
                null, null, false,
            ],
        ];

        // Errata: the things this product does not do. A colophon that only
        // listed strengths would not be a colophon.
        $errata = [
            [
                'Seat maps are Enterprise only.',
                'On every other plan a ticket type carries a name, a price, a quantity and a sales window, and buyers are not choosing a specific seat. Drawing a room and selling the seats in it is the one part of ticketing that sits behind the top plan.',
            ],
            [
                'Followers are not emailed automatically.',
                'Nothing goes out when you add an event. Following gives you a list you can write to: you compose the newsletter and press send. Ticket buyers are the exception, and they are emailed when an event they bought for changes or is cancelled.',
            ],
            [
                'Nothing checks for double bookings.',
                'Two events at the same time in the same place will both simply appear. The calendar does not object, and it will not warn you.',
            ],
            [
                'Newsletter allowances count recipients.',
                'Not sends. One newsletter to a hundred followers uses a hundred of the allowance: 10 a month on Free, 100 on Pro, 1,000 on Enterprise.',
            ],
            [
                'The free plan is one team member.',
                'Multiple team members, capped at five, are an Enterprise feature. Selfhost and you have them, because a selfhosted install is Enterprise.',
            ],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule really free?',
                'a' => 'Yes, and not as a trial. Unlimited events and schedules, recurring events with date exceptions, sub-schedules, free registration with a capacity limit, two-way Google, Outlook and CalDAV sync, the embeddable calendar and built-in analytics are all on the free plan, along with selling up to 25 paid tickets a month and scanning them at the door, with no credit card and no expiry. Unlimited ticket sales and the check-in dashboard are on the Pro plan at '.plan_price($proMonthly).' a month, multiple team members are on Enterprise at '.plan_price($entMonthly).', and the free plan is one team member.',
            ],
            [
                'q' => 'What license is it under, and where is the source?',
                'a' => 'The Attribution Assurance License. The full text is the LICENSE file in the repository at github.com/eventschedule/eventschedule, which is public: you can read the code that runs the hosted service, fork it, or open an issue against it.',
            ],
            [
                'q' => 'Can I run it on my own server?',
                'a' => 'Yes. Selfhost it and the database sits on hardware you control. A selfhosted install resolves to the Enterprise tier, so unlimited ticket sales, the API, custom CSS, multiple team members and every other paid-tier feature is included, and two things exist only on a selfhosted install: one-click app updates and importing events from URLs or by city.',
            ],
            [
                'q' => 'Do you take a cut of ticket sales?',
                'a' => 'No. Event Schedule charges zero platform fees. You connect your own Stripe account and the money settles into it directly, so past Stripe\'s own processing charge nothing is taken. Invoice Ninja works as an alternative payment route on the Pro plan.',
            ],
            [
                'q' => 'Who is behind it?',
                'a' => 'Event Schedule is built by the team behind Invoice Ninja, a source-available invoicing platform. The attribution clause in the license names the author, which is an unusual thing to write into a license and the reason this page can be specific about who made it.',
            ],
            [
                'q' => 'What happens to my data?',
                'a' => 'It is never sold or shared with third parties. Site analytics are opt-in: analytics, advertising and personalization signals are denied until you press Allow in the cookie banner, and if you decline, no analytics cookies are set. When somebody follows your schedule or buys a ticket, you see their name and email so you can reach them, and that is the only sharing involved. Selfhost and none of it leaves your server.',
            ],
        ];

        // The dot nav carries the same gathering letters the sections are marked
        // with, so the binder's sequence is the page's navigation. The first
        // gathering and the back matter are unsigned, as they are in a real book.
        $dotSections = [
            ['top', 'Colophon', null],
            ['three', 'Three questions', 'B'],
            ['imprint', 'The imprint', 'C'],
            ['mission', 'Why it was set', 'D'],
            ['who', 'Who it is set for', 'E'],
            ['impressions', 'Two impressions', 'F'],
            ['rules', 'Standing rules', 'G'],
            ['errata', 'Errata', 'H'],
            ['makers', 'The makers', 'I'],
            ['source', 'The source', 'J'],
            ['faq', 'Questions', 'K'],
            ['claim', 'The last leaf', null],
        ];
    @endphp

    <div id="es-colo-page" class="es-colo-page">

    <!-- ============================================================ -->
    <!-- A. Hero: the title page and its imprint                      -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-colo-laid absolute inset-0"></div>
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(29, 78, 216, 0.16), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(126, 166, 255, 0.12), rgba(126, 166, 255, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 mb-8 flex items-center gap-4">
                        <span class="es-colo-mark" aria-hidden="true">
                            <span class="es-colo-mark-glyph">ES</span>
                        </span>
                        <span class="es-colo-tag">Colophon<br>Event Schedule</span>
                    </div>

                    <h1 class="es-balance es-colo-title es-colo-h1 mb-8">
                        <span class="es-mask"><span class="es-mask-line">Every colophon answers</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-colo-second">three questions.</span></span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-colo-muted mb-10 max-w-xl text-lg sm:text-xl">
                        A colophon is the note at the back of a book: who printed it, what it was set in, and the terms you may reuse it under. Software almost never prints one. Here is ours, and none of it has to be taken on trust. The source is public, you can run the whole thing on your own server, and we take nothing from your ticket sales.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#imprint" class="es-colo-ghost inline-flex items-center justify-center gap-2 whitespace-nowrap px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            Read the imprint
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up') }}" class="es-colo-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            Create your free schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The imprint stamp: the three questions, answered in one line each. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-colo-leaf p-7 sm:p-8">
                        <p class="es-colo-tag mb-1">Imprint</p>
                        <div class="es-colo-rule mb-5" aria-hidden="true"></div>

                        <dl class="space-y-4">
                            <div>
                                <dt class="es-colo-mono es-colo-second text-xs font-bold uppercase tracking-widest">Who made it</dt>
                                <dd class="es-colo-ink mt-1">The team behind Invoice Ninja.</dd>
                            </div>
                            <div>
                                <dt class="es-colo-mono es-colo-second text-xs font-bold uppercase tracking-widest">What it is made of</dt>
                                <dd class="es-colo-ink mt-1">Laravel, Vue and MySQL, all of it public.</dd>
                            </div>
                            <div>
                                <dt class="es-colo-mono es-colo-second text-xs font-bold uppercase tracking-widest">What you may do with it</dt>
                                <dd class="es-colo-ink mt-1">Read it, fork it, run it yourself, and sell tickets with no cut taken.</dd>
                            </div>
                        </dl>

                        <div class="es-colo-hair my-5" aria-hidden="true"></div>
                        <p class="es-colo-muted es-colo-mono text-xs">
                            Attribution Assurance License &middot; github.com/eventschedule
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- B. The three questions (pressroom band)                       -->
    <!-- ============================================================ -->
    <section id="three" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-colo-press noise relative overflow-hidden rounded-[2.5rem] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl" data-reveal>
                    <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">B</span> The three questions</p>
                    <h2 class="es-balance es-colo-title es-colo-h2 es-colo-ink mb-4">
                        Each one has an answer you can <span class="es-colo-lit">go and check.</span>
                    </h2>
                    <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-colo-leaf flex flex-col p-7" data-reveal="panel">
                        <p class="es-colo-tag mb-3">Question one</p>
                        <h3 class="es-colo-title es-colo-ink mb-3 text-xl">Who made it</h3>
                        <p class="es-colo-muted text-sm leading-relaxed">The same team that built Invoice Ninja. The license itself carries the attribution clause that names the author, which is why this page can be specific rather than vague about it.</p>
                        <a href="#makers" class="es-colo-link mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold">
                            Read about the makers
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                    <div class="es-colo-leaf flex flex-col p-7" data-reveal="panel">
                        <p class="es-colo-tag mb-3">Question two</p>
                        <h3 class="es-colo-title es-colo-ink mb-3 text-xl">What it is made of</h3>
                        <p class="es-colo-muted text-sm leading-relaxed">Laravel on the server, Vue on the front end, MySQL underneath, and all of it on GitHub. The code that runs the hosted service is the code you can download.</p>
                        <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="es-colo-link mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold">
                            Open the repository
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    </div>
                    <div class="es-colo-leaf flex flex-col p-7" data-reveal="panel">
                        <p class="es-colo-tag mb-3">Question three</p>
                        <h3 class="es-colo-title es-colo-ink mb-3 text-xl">What you may do with it</h3>
                        <p class="es-colo-muted text-sm leading-relaxed">Run it on your own server, where a selfhosted install is treated as Enterprise. Sell tickets through your own Stripe account, where the platform fee is <span class="es-colo-lit font-semibold">zero</span>.</p>
                        <a href="#imprint" class="es-colo-link mt-auto inline-flex items-center gap-1 pt-4 text-sm font-semibold">
                            See the terms in the imprint
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>

                <p class="es-colo-muted mt-10 text-center" data-reveal>
                    Three claims, three places to verify them. That is the whole argument of this page.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- C. The imprint: the record, set as a table                    -->
    <!-- ============================================================ -->
    <section id="imprint" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">C</span> The imprint</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    Set in Laravel. Licensed for reuse. <span class="es-colo-second">Sold with no cut taken.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
                <p class="es-colo-muted mt-5 text-lg">
                    The back page of a book prints the facts that outlast the marketing. These are ours, and every one of them points at somewhere you can check it.
                </p>
            </div>

            <div class="es-colo-leaf p-5 sm:p-8" data-reveal="panel">
                <table class="es-colo-imprint">
                    <caption class="sr-only">The Event Schedule imprint: publisher, license, source, stack, hosting, fees, plans, analytics and first edition</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="es-colo-tag">Field</th>
                            <th scope="col" class="es-colo-tag">Record</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($imprint as [$fName, $fValue, $fHref, $fLabel, $fExternal])
                            <tr>
                                <th scope="row" class="es-colo-mono es-colo-ink text-xs uppercase tracking-widest">{{ $fName }}</th>
                                <td class="es-colo-muted text-sm leading-relaxed">
                                    {{ $fValue }}
                                    @if ($fHref)
                                        <a href="{{ $fHref }}" @if ($fExternal) target="_blank" rel="noopener noreferrer" @endif class="es-colo-link ms-1 inline-flex items-center gap-1 whitespace-nowrap font-semibold">
                                            {{ $fLabel }}
                                            <svg aria-hidden="true" class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- D. Why it was set: the press note                             -->
    <!-- ============================================================ -->
    <section id="mission" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">D</span> Why it was set</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    Making event sharing <span class="es-colo-second">effortless.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
            </div>

            <div class="es-colo-leaf relative overflow-hidden p-7 sm:p-10" data-reveal="panel">
                <div class="es-colo-laid pointer-events-none absolute inset-0" aria-hidden="true"></div>
                <div class="relative space-y-6 text-lg leading-relaxed">
                    <p class="es-colo-drop es-colo-ink">Event Schedule was created to solve a simple problem: making it easy for anyone with events to share them with their audience.</p>
                    <p class="es-colo-muted">Whether you are a musician with upcoming shows, a venue with a packed calendar, a curator aggregating local happenings, or a food truck appearing at a different spot each day, you deserve a simple, professional way to let people know where you will be.</p>
                    <p class="es-colo-muted">Sharing your schedule should not require expensive software or technical expertise. That is why Event Schedule is free, fast and easy to use, and why the version you can download is the version we run.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- E. Who it is set for: the three schedule types                -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">E</span> Who it is set for</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    Three kinds of schedule, <span class="es-colo-second">one book.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
                <p class="es-colo-muted mt-5 text-lg">
                    However you share your events, Event Schedule is built to work for you. There are only three schedule types, and everything else is a variation on them.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="90">
                @php
                    $editions = [
                        ['/for-talent', 'Talent', 'Share your upcoming shows, appearances and locations with the people who follow you. Musicians, DJs, comedians, dancers, magicians, food trucks: anyone whose audience needs to know where to find them next.'],
                        ['/for-venues', 'Venues', 'Keep one calendar current and public, so visitors can see what is coming up and buy tickets straight from the schedule instead of hunting for a listing.'],
                        ['/for-curators', 'Curators', 'Aggregate events from several schedules and publish a guide to what is happening in your area or your niche, with the originals still credited.'],
                    ];
                @endphp
                @foreach ($editions as $eIndex => [$eHref, $eName, $eBody])
                    <a href="{{ marketing_url($eHref) }}" class="es-colo-hover es-colo-leaf group flex flex-col p-7 transition-all duration-200 hover:-translate-y-0.5" data-reveal="panel">
                        <div class="mb-4 flex items-baseline justify-between gap-3">
                            <h3 class="es-colo-hover-title es-colo-title es-colo-ink text-xl transition-colors">For {{ $eName }}</h3>
                            <span class="es-colo-mono es-colo-muted text-xs">{{ str_pad($eIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="es-colo-hair mb-4" aria-hidden="true"></div>
                        <p class="es-colo-muted text-sm leading-relaxed">{{ $eBody }}</p>
                        <span class="es-colo-hover-arrow es-colo-muted mt-auto inline-flex items-center gap-1 pt-5 text-sm font-semibold transition-colors">
                            Read the chapter
                            <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <p class="es-colo-muted mt-8 text-sm" data-reveal>
                Not sure which one fits?
                <a href="{{ marketing_url('/use-cases') }}" class="es-colo-link font-semibold">Browse every use case</a>
                and pick the closest.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- F. Two impressions: hosted or selfhosted                      -->
    <!-- ============================================================ -->
    <section id="impressions" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">F</span> Two impressions</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    The same book, printed on <span class="es-colo-second">two presses.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
                <p class="es-colo-muted mt-5 text-lg">
                    Open source only means something if you can actually run the thing. These two columns are the same application. The difference is whose machine it is on.
                </p>
            </div>

            <div class="es-colo-duplex grid grid-cols-1 gap-10 md:grid-cols-2 md:gap-14" data-reveal-group="120">
                <div data-reveal>
                    <p class="es-colo-tag mb-3">Recto &middot; we run it</p>
                    <h3 class="es-colo-title es-colo-ink mb-2 text-2xl">eventschedule.com</h3>
                    <p class="es-colo-muted mb-6">Sign up and you have a schedule at your own subdomain in a couple of minutes. Updates, backups and the mail queue are our problem.</p>
                    <ul class="space-y-3">
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">A free plan that sells 25 tickets a month, with no expiry and no credit card <span class="es-colo-pill">Free</span></span>
                        </li>
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">Unlimited ticket sales and the check-in dashboard for {{ plan_price($proMonthly) }} a month <span class="es-colo-pill es-colo-pill-paid">Pro</span></span>
                        </li>
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">Your own domain and up to five team members for {{ plan_price($entMonthly) }} <span class="es-colo-pill es-colo-pill-paid">Enterprise</span></span>
                        </li>
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">Zero platform fees on ticket sales, settling into your own Stripe account</span>
                        </li>
                    </ul>
                    <a href="{{ marketing_url('/pricing') }}" class="es-colo-link mt-6 inline-flex items-center gap-1 font-semibold">
                        Compare the plans
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <div data-reveal>
                    <p class="es-colo-tag mb-3">Verso &middot; you run it</p>
                    <h3 class="es-colo-title es-colo-ink mb-2 text-2xl">Your own server</h3>
                    <p class="es-colo-muted mb-6">Clone it, point it at a MySQL database and it is yours. The data never leaves the machine, and there is nobody to ask permission from.</p>
                    <ul class="space-y-3">
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">Every paid-tier feature, because a selfhosted install resolves to Enterprise</span>
                        </li>
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">One-click app updates, which only exist on selfhosted installs</span>
                        </li>
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">Importing events from a URL or by city, also selfhost-only</span>
                        </li>
                        <li class="flex gap-3">
                            <svg aria-hidden="true" class="es-colo-second mt-1 h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span class="es-colo-muted text-sm">No monthly newsletter allowance, because that limit only applies to hosted schedules</span>
                        </li>
                    </ul>
                    <a href="{{ marketing_url('/selfhost') }}" class="es-colo-link mt-6 inline-flex items-center gap-1 font-semibold">
                        How selfhosting works
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- G. Standing rules: what we hold to                            -->
    <!-- ============================================================ -->
    <section id="rules" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">G</span> Standing rules</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    What we believe in, <span class="es-colo-second">pinned to the press.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
                <p class="es-colo-muted mt-5 text-lg">
                    A press keeps standing instructions pinned by the machine: the things that do not change between jobs. These are ours.
                </p>
            </div>

            {{-- One ruled sheet, not a third card row: the standing instructions
                 pinned by the machine are a single document, and the numeral hangs
                 in the margin as a press figure. --}}
            <div class="es-colo-leaf relative overflow-hidden p-6 sm:p-9" data-reveal="panel">
                <div class="es-colo-laid pointer-events-none absolute inset-0" aria-hidden="true"></div>
                <div class="relative">
                    <p class="es-colo-tag mb-1">Pinned at the press &middot; do not take down</p>
                    <div class="es-colo-rule mb-7" aria-hidden="true"></div>
                    <ol>
                        <li class="es-colo-standing-row">
                            <p class="es-colo-fig es-colo-second" aria-hidden="true">01</p>
                            <div>
                                <h3 class="es-colo-title es-colo-ink mb-2 text-xl">Free is not a trial</h3>
                                <p class="es-colo-muted text-sm leading-relaxed">The free plan has no expiry date and asks for no card. Unlimited events and schedules, recurring events with date exceptions, sub-schedules, free registration with a capacity limit, two-way calendar sync, the embeddable calendar and built-in analytics all sit on it, and so does selling tickets, up to 25 paid ones a month.</p>
                            </div>
                        </li>
                        <li class="es-colo-standing-row">
                            <p class="es-colo-fig es-colo-second" aria-hidden="true">02</p>
                            <div>
                                <h3 class="es-colo-title es-colo-ink mb-2 text-xl">Your data is yours</h3>
                                <p class="es-colo-muted text-sm leading-relaxed">It is never sold and never shared with third parties. Site analytics are opt-in and stay denied until you press Allow. Export the whole schedule whenever you like, and if that is still not enough, selfhost and it never leaves your server.</p>
                            </div>
                        </li>
                        <li class="es-colo-standing-row">
                            <p class="es-colo-fig es-colo-second" aria-hidden="true">03</p>
                            <div>
                                <h3 class="es-colo-title es-colo-ink mb-2 text-xl">Say what it does not do</h3>
                                <p class="es-colo-muted text-sm leading-relaxed">A short product done properly beats a long list done badly. When something is missing we would rather print it than let you find out in the middle of an event, which is what the next section is for.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- H. The errata slip                                            -->
    <!-- ============================================================ -->
    <section id="errata" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">H</span> Errata</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    The slip that says <span class="es-colo-second">what is not here.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
                <p class="es-colo-muted mt-5 text-lg">
                    Books ship an errata slip tipped in at the front. Software rarely does. Five things Event Schedule does not do, so you can decide with the whole picture rather than half of it.
                </p>
            </div>

            <div class="es-colo-slip p-6 sm:p-9" data-reveal="panel">
                <p class="es-colo-tag mb-5">Errata &middot; please note</p>
                <ol class="space-y-6">
                    @foreach ($errata as $errIndex => [$errTitle, $errBody])
                        <li class="flex gap-4">
                            <span class="es-colo-mono es-colo-muted flex-none pt-1 text-xs font-bold" aria-hidden="true">{{ str_pad($errIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <h3 class="es-colo-title es-colo-ink text-lg">{{ $errTitle }}</h3>
                                <p class="es-colo-muted mt-1 text-sm leading-relaxed">{{ $errBody }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <div class="es-colo-hair my-6" aria-hidden="true"></div>
                <p class="es-colo-muted text-sm">
                    If one of these is a deal-breaker, better to know now.
                    <a href="{{ marketing_url('/features') }}" class="es-colo-link font-semibold">Read what it does do</a>
                    and judge the whole thing.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- I. The makers                                                 -->
    <!-- ============================================================ -->
    <section id="makers" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">I</span> The makers</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    Built by the team behind <span class="es-colo-second">Invoice Ninja.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
            </div>

            <div class="es-colo-leaf relative overflow-hidden p-7 sm:p-10" data-reveal="panel">
                <div class="es-colo-laid pointer-events-none absolute inset-0" aria-hidden="true"></div>
                <div class="relative">
                    <div class="mb-6 flex items-center gap-4">
                        <span class="es-colo-mark" aria-hidden="true">
                            <span class="es-colo-mark-glyph">IN</span>
                        </span>
                        <p class="es-colo-tag">Same people<br>Same approach</p>
                    </div>
                    <p class="es-colo-muted mb-4 text-lg leading-relaxed">
                        Event Schedule is made by the same team that built Invoice Ninja, a source-available invoicing platform used by businesses around the world. Both projects are run the same way: the source is public, selfhosting is a first-class option rather than an afterthought, and the paid plans exist to fund the work rather than to hold the useful parts hostage.
                    </p>
                    <p class="es-colo-muted mb-8">
                        That is also why the license here is unusual. The Attribution Assurance License requires that the author's name travels with the code, which means the answer to "who made this" is written into the software itself and not just onto a marketing page.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="https://invoiceninja.com" target="_blank" rel="noopener noreferrer" class="es-colo-ghost inline-flex items-center gap-2 px-5 py-3 font-semibold transition-all duration-200">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            invoiceninja.com
                        </a>
                        <a href="https://github.com/invoiceninja/invoiceninja" target="_blank" rel="noopener noreferrer" class="es-colo-ghost inline-flex items-center gap-2 px-5 py-3 font-semibold transition-all duration-200">
                            <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            Invoice Ninja on GitHub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- J. The source                                                -->
    <!-- ============================================================ -->
    <section id="source" class="relative scroll-mt-24 overflow-hidden py-20 lg:py-28">
        <div class="es-colo-laid pointer-events-none absolute inset-0" aria-hidden="true"></div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <div data-reveal>
                    <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">J</span> The source</p>
                    <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                        Free and <span class="es-colo-second">open source.</span>
                    </h2>
                    <div class="es-colo-rule es-colo-draw mx-auto max-w-xs" aria-hidden="true"></div>
                </div>

                <p class="es-colo-muted mx-auto mt-6 mb-8 max-w-2xl text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Event Schedule is open source under the Attribution Assurance License. Selfhost it on your own server, contribute to the codebase, or just use it free forever. The repository is public and the history goes back to the first commit.
                </p>

                <div class="flex justify-center" data-reveal>
                    @include('marketing.partials.github-star-badge')
                </div>

                <div class="flex flex-wrap justify-center gap-4" data-reveal>
                    <a href="https://github.com/eventschedule/eventschedule" target="_blank" rel="noopener noreferrer" class="es-colo-ghost inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all duration-200">
                        <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        View on GitHub
                    </a>
                    <a href="{{ marketing_url('/open-source') }}" class="es-colo-ghost inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all duration-200">
                        Why open source
                    </a>
                    <a href="{{ marketing_url('/features') }}" class="es-colo-btn inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all duration-200 hover:-translate-y-0.5">
                        Explore features
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- K. Questions                                                 -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10" data-reveal>
                <p class="es-colo-sig mb-5"><span class="es-colo-sig-mark">K</span> Questions</p>
                <h2 class="es-balance es-colo-title es-colo-h2 mb-4">
                    What people ask <span class="es-colo-second">before they trust it.</span>
                </h2>
                <div class="es-colo-rule es-colo-draw" aria-hidden="true"></div>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-colo-hover es-colo-leaf group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-colo-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-colo-mono es-colo-second flex-none pt-0.5 text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-colo-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-colo-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-colo-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>

            <p class="es-colo-muted mt-8 text-sm" data-reveal>
                Something not answered here?
                <a href="{{ marketing_url('/contact') }}" class="es-colo-link font-semibold">Ask us directly</a>
                or open an issue on GitHub, where the answer stays public.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- L. Related pages                                              -->
    <!-- ============================================================ -->
    <section class="py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8" data-reveal>
                <p class="es-colo-tag mb-3">Elsewhere in the book</p>
                <h2 class="es-colo-title es-colo-h2 text-2xl">Keep reading</h2>
                <div class="es-colo-rule es-colo-draw mt-4" aria-hidden="true"></div>
            </div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/open-source', 'Open source', 'The license, in plain terms'], ['/selfhost', 'Selfhosting', 'Run it on your own server'], ['/pricing', 'Pricing', 'Free, Pro and Enterprise'], ['/privacy', 'Privacy', 'What we do with your data']] as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-colo-hover es-colo-leaf group flex flex-col p-5 transition-all duration-200" data-reveal>
                        <span class="es-colo-hover-title es-colo-title es-colo-ink mb-2 text-base transition-colors">{{ $relName }}</span>
                        <span class="es-colo-muted mb-4 text-xs leading-relaxed">{{ $relBlurb }}</span>
                        <span class="es-colo-hover-arrow es-colo-muted mt-auto inline-flex items-center gap-1 text-xs font-semibold transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- M. The last leaf                                              -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-colo-press noise relative overflow-hidden rounded-[2.5rem] px-6 py-16 text-center sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-colo-tag mb-5" aria-hidden="true">* &nbsp;*&nbsp; *</p>
                    <h2 class="es-balance es-colo-title es-colo-h2 es-colo-ink mx-auto mb-6 max-w-3xl">
                        Now put your own name <span class="es-colo-lit">on a page.</span>
                    </h2>
                    <p class="es-colo-muted mx-auto mb-10 max-w-2xl text-lg">
                        Create your free schedule today. No credit card required, and nothing taken from your ticket sales.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="es-colo-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="es-colo-muted mt-6 text-sm">Free forever &middot; open source &middot; zero platform fees</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav, marked with the signature letters -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel, $sectionMark])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-500 dark:bg-white/30"></span>
                        <span class="es-colo-mono pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-gray-800 dark:text-gray-300">@if ($sectionMark)<span class="es-colo-navmark">{{ $sectionMark }}</span>@endif {{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
