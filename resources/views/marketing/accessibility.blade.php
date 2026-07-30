<x-marketing-layout>
    <x-slot name="title">{{ __('accessibility.page_title') }}</x-slot>
    <x-slot name="description">{{ __('accessibility.meta_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ __('accessibility.breadcrumb') }}</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": @json(__('accessibility.page_title')),
        "description": @json(__('accessibility.meta_description')),
        "url": "{{ url()->current() }}",
        "dateModified": @json(config('accessibility.declaration_last_reviewed')),
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "Web accessibility"
        }
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           Accessibility "The Fine Print" styles.

           THE CONCEPT. A statement is a printed instrument, not a
           landing page: an issue block at the head, a contents
           register that names the nature of each clause, numbered
           clauses hanging in a ruled margin, a schedule of limitations
           set as a real table, and an undertaking at the foot that
           closes on the same register the head opened with. "The fine
           print" is the family treatment this page shares with
           /privacy, /terms and /self-hosting-terms, and the argument
           this one makes is that the fine print here is set LARGE and
           read easily, because this is the one page on the site whose
           subject is whether you can read it. Nothing is set below 13px
           (.es-fine-small), body copy is capped at a 68ch measure,
           links are underlined rather than colour-only, and focus is
           a 3px outline with a 3px offset.

           NO FIXED PHYSICAL OBJECT, deliberately. The sheet is warm
           paper in light mode and a dark proof in dark mode, so
           section 5 of the rebuild brief (pin the object, verify with
           --bands) does not apply: both modes are designed, neither
           is an override of the other.

           COLOUR: the page keeps its existing blue, which is correct
           for a legal surface and is spent only on clause numerals,
           links, the report button and one hairline. Distinctiveness
           comes from STRUCTURE (margin rule, hanging numerals, the
           nature column in the register, a real <table>, a <dl> issue
           block) and TYPOGRAPHY (tabular monospace register against a
           system serif-free text face), not from a new hue.

           RE-INKED TO THE FAMILY (review pass). /terms records the
           binding note "if you re-ink one of the four, re-ink all
           four", and /privacy, /terms and /self-hosting-terms already
           agree on every token to the hex. This page had drifted on
           eight of them, three of which were visible in dark mode
           (sheet, muted, accent). It now uses the family's own values.

           NO LEADER DOTS (review pass). /terms records that four
           siblings elsewhere in the WP already use dotted TOC leaders,
           so it chose numerals and hairlines instead; a check of the
           marketing directory found dotted leaders rendering on nine
           pages. The register's right-hand field is now the clause's
           NATURE - what kind of statement it makes, from "extent" to
           "admitted" - which is informative rather than ornamental and
           is the page's own argument: a declaration is worth reading
           because of which clauses confess.

           MEASURED, on the grounds this page actually paints (probe
           over every rendered text node inside #es-fine-page):
             light  ink #16181c on paper #f5f5f2 ......... 16.3
                    muted #4b5158 on paper ...............  7.4
                    muted #4b5158 on subsurface #ebebe6 ..  6.7
                    accent #1d4ed8 on paper ..............  6.1  <- floor (6.14)
                    white on button #1a3fb0 ..............  8.8
             dark   ink #e9ebef on ground #0b0c0f ........ 16.4
                    muted #9aa1ab on sheet #12141a .......  7.2
                    muted #9aa1ab on subsurface #17191f ..  6.7  <- floor (6.74)
                    accent #93c5fd on sheet #12141a ...... 10.4
                    ground #0b0c0f on button #93c5fd ..... 10.8
           The button keeps #1a3fb0 rather than the family's #1d4ed8
           on purpose: white on #1d4ed8 is 6.70 and white on #1a3fb0
           is 8.82, and this is the page that has to mean it.
           Never text-gray-500 here: 4.83 on pure white but 4.2-4.5
           on this page's tinted paper. Use .es-fine-muted.

           NO Tailwind arbitrary values for anything design-critical:
           this page ships without a build step, so an ungenerated
           `text-[#...]` would silently paint nothing.
           ============================================================== */

        /* --- Ground, ink, and the shared surfaces --- */
        .es-fine-page {
            background-color: #f5f5f2;
            color: #16181c;
            --es-fine-rule: rgba(22, 24, 28, 0.16);
            --es-fine-rule-soft: rgba(22, 24, 28, 0.09);
            --es-fine-sheet: #ffffff;
            --es-fine-sub: #ebebe6;
        }
        .dark .es-fine-page {
            background-color: #0b0c0f;
            color: #e9ebef;
            --es-fine-rule: rgba(233, 235, 239, 0.18);
            --es-fine-rule-soft: rgba(233, 235, 239, 0.09);
            --es-fine-sheet: #12141a;
            --es-fine-sub: #17191f;
        }
        .es-fine-ink { color: #16181c; }
        .dark .es-fine-ink { color: #e9ebef; }
        .es-fine-muted { color: #4b5158; }
        .dark .es-fine-muted { color: #9aa1ab; }
        .es-fine-accent { color: #1d4ed8; }
        .dark .es-fine-accent { color: #93c5fd; }

        /* --- Type. The smallest step on this page is 13px. --- */
        .es-fine-measure { max-width: 68ch; }
        .es-fine-small { font-size: 0.8125rem; line-height: 1.6; }
        .es-fine-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.02em;
        }
        .es-fine-label {
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-label { color: #9aa1ab; }

        /* The family eyebrow: a mono pill, as on /privacy, /terms and
           /self-hosting-terms. The first-wave accessibility page carried
           a pill with an icon too, so this is faithful both ways. */
        .es-fine-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border: 1px solid var(--es-fine-rule);
            border-radius: 999px;
            background-color: var(--es-fine-sub);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-tag { color: #9aa1ab; }

        /* --- Faint ruling behind the statement, like a ruled sheet.
               Decorative only: cards above it are opaque, and the
               section carries a real background-color underneath so
               text is never scored against the page ground. --- */
        .es-fine-rules {
            background-image: repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent calc(2.25rem - 1px),
                var(--es-fine-rule-soft) calc(2.25rem - 1px),
                var(--es-fine-rule-soft) 2.25rem);
            opacity: 0.55;
        }

        /* --- The margin rule and the hanging clause numerals --- */
        .es-fine-column { position: relative; }
        .es-fine-clause {
            position: relative;
            padding-block: 2.5rem;
            border-top: 1px solid var(--es-fine-rule-soft);
        }
        .es-fine-num {
            display: inline-block;
            margin-bottom: 0.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.8125rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            color: #1d4ed8;
        }
        .dark .es-fine-num { color: #93c5fd; }
        @media (min-width: 900px) {
            .es-fine-column { padding-inline-start: 5.25rem; }
            .es-fine-column::before {
                content: "";
                position: absolute;
                top: 0;
                bottom: 0;
                inset-inline-start: 3.6rem;
                width: 1px;
                background: var(--es-fine-rule-soft);
            }
            .es-fine-num {
                position: absolute;
                inset-inline-start: -5.25rem;
                top: 2.9rem;
                width: 3rem;
                margin-bottom: 0;
                text-align: end;
            }
        }

        /* --- Contents register. The right-hand field is the clause's
               nature, not a dotted leader: leaders render on nine other
               marketing pages and /terms records the family avoiding
               them, and a register that tells you which clauses promise
               and which confess is the whole argument of the page. --- */
        .es-fine-index { display: grid; gap: 0; }
        @media (min-width: 700px) {
            .es-fine-index {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 2.5rem;
            }
        }
        .es-fine-index-link {
            display: flex;
            align-items: baseline;
            gap: 0.7rem;
            padding: 0.6rem 0.2rem;
            border-bottom: 1px solid var(--es-fine-rule-soft);
            color: #16181c;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .dark .es-fine-index-link { color: #e9ebef; }
        .es-fine-index-link:hover { color: #1d4ed8; }
        .dark .es-fine-index-link:hover { color: #93c5fd; }
        .es-fine-index-n {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.8125rem;
            font-weight: 800;
            color: #1d4ed8;
        }
        .dark .es-fine-index-n { color: #93c5fd; }
        .es-fine-index-t { flex: 1 1 auto; }
        .es-fine-index-nature {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8125rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-index-nature { color: #9aa1ab; }

        /* --- The issue block at the head of the instrument --- */
        .es-fine-record { display: grid; gap: 0; }
        @media (min-width: 700px) {
            .es-fine-record {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 2.5rem;
            }
        }
        .es-fine-record-row {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.25rem 1rem;
            padding: 0.7rem 0.1rem;
            border-bottom: 1px solid var(--es-fine-rule-soft);
        }
        .es-fine-record-val { font-weight: 600; }
        /* Five entries in two columns leaves a hole, so the contact line
           runs the full width and reads as the instrument's last line. */
        @media (min-width: 700px) {
            .es-fine-record-wide { grid-column: 1 / -1; }
        }

        /* --- Cards, sheets, panels --- */
        .es-fine-sheet {
            background-color: var(--es-fine-sheet);
            border: 1px solid var(--es-fine-rule);
            border-radius: 0.9rem;
        }
        .es-fine-stamp {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.4rem 0.85rem;
            border: 1px solid var(--es-fine-rule);
            border-radius: 0.45rem;
            background-color: var(--es-fine-sub);
            font-size: 0.8125rem;
            font-weight: 600;
        }

        /* --- The counsel notice: a stamped caveat, warm and quiet --- */
        .es-fine-notice {
            background-color: #fbf6e9;
            border: 1px solid rgba(133, 100, 14, 0.3);
            border-radius: 0.9rem;
        }
        .dark .es-fine-notice {
            background-color: #1b1913;
            border-color: rgba(214, 178, 86, 0.34);
        }
        .es-fine-notice-mark { color: #6b5310; }
        .dark .es-fine-notice-mark { color: #e0c47f; }

        /* --- The specimen: the display panel's controls, row by row --- */
        .es-fine-spec {
            background-color: var(--es-fine-sheet);
            border: 1px solid var(--es-fine-rule);
            border-radius: 0.9rem;
            overflow: hidden;
        }
        .es-fine-spec-row {
            display: grid;
            gap: 0.3rem 1.5rem;
            padding: 1.1rem 1.2rem;
            border-top: 1px solid var(--es-fine-rule-soft);
        }
        .es-fine-spec-row:first-child { border-top: 0; }
        @media (min-width: 720px) {
            .es-fine-spec-row { grid-template-columns: 13.5rem minmax(0, 1fr); }
        }
        .es-fine-spec-key { font-weight: 700; }
        /* The same specimen rows, inset on the finale panel: the panel is
           already sheet-coloured, so the return record takes the
           subsurface to sit down into it rather than float on it. */
        .es-fine-spec-inset { background-color: var(--es-fine-sub); }

        /* --- The schedule of limitations: a record, so a real table --- */
        .es-fine-scroll { overflow-x: auto; }
        .es-fine-table {
            width: 100%;
            min-width: 33rem;
            border-collapse: collapse;
        }
        .es-fine-table th,
        .es-fine-table td {
            padding: 0.9rem 1rem;
            text-align: start;
            vertical-align: top;
            border-top: 1px solid var(--es-fine-rule-soft);
        }
        .es-fine-table thead th {
            border-top: 0;
            background-color: var(--es-fine-sub);
            font-size: 0.8125rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-table thead th { color: #9aa1ab; }
        .es-fine-table tbody th {
            min-width: 9.5rem;
            font-weight: 700;
        }

        /* --- Links: underlined by default, because colour alone is
               not a signal. This is the page that has to mean it. --- */
        .es-fine-link {
            color: #1d4ed8;
            text-decoration: underline;
            text-underline-offset: 2px;
            text-decoration-thickness: 1px;
        }
        .es-fine-link:hover {
            color: #16181c;
            text-decoration-thickness: 2px;
        }
        .dark .es-fine-link { color: #93c5fd; }
        .dark .es-fine-link:hover { color: #e9ebef; }

        .es-fine-btn {
            background-color: #1a3fb0;
            color: #ffffff;
            border: 1px solid transparent;
            transition: background-color 0.2s ease;
        }
        .es-fine-btn:hover { background-color: #142f86; }
        .dark .es-fine-btn { background-color: #93c5fd; color: #0b0c0f; }
        .dark .es-fine-btn:hover { background-color: #bfd8ff; }

        .es-fine-btn2 {
            background-color: transparent;
            border: 1px solid var(--es-fine-rule);
            color: #16181c;
            transition: background-color 0.2s ease;
        }
        .dark .es-fine-btn2 { color: #e9ebef; }
        .es-fine-btn2:hover { background-color: var(--es-fine-sub); }

        /* --- A disclosure, kept because it is also a specimen: it is
               the one widget on the page you can open by keyboard. --- */
        .es-fine-disclose {
            background-color: var(--es-fine-sub);
            border: 1px solid var(--es-fine-rule-soft);
            border-radius: 0.7rem;
        }
        .es-fine-disclose summary {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.8rem 1rem;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.8125rem;
        }
        .es-fine-caret { transition: transform 0.2s ease; }
        .es-fine-disclose[open] .es-fine-caret { transform: rotate(180deg); }

        /* --- Finale: the undertaking. A printed instrument closes on the
               same register it opened with, so the foot repeats the issue
               and the review date under a closing hairline, and the three
               things to tell us are set as a return record rather than
               named in a sentence. --- */
        .es-fine-finale {
            background-color: var(--es-fine-sheet);
            background-image: radial-gradient(115% 100% at 0% 0%, rgba(29, 78, 216, 0.1), rgba(29, 78, 216, 0) 62%);
            border: 1px solid var(--es-fine-rule);
            border-radius: 1.6rem;
        }
        .dark .es-fine-finale {
            background-image: radial-gradient(115% 100% at 0% 0%, rgba(147, 197, 253, 0.12), rgba(147, 197, 253, 0) 62%);
        }
        /* A single hairline, not a printer's double rule: /about, /browse and
           /faq own that, and /terms records the family avoiding it. */
        .es-fine-foot {
            margin-top: 2.5rem;
            padding-top: 1.1rem;
            border-top: 1px solid var(--es-fine-rule);
        }

        /* --- Related strip --- */
        .es-fine-rel {
            background-color: var(--es-fine-sheet);
            border: 1px solid var(--es-fine-rule);
            border-radius: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }
        .es-fine-rel:hover {
            border-color: rgba(29, 78, 216, 0.45);
            box-shadow: 0 14px 30px -20px rgba(22, 24, 28, 0.55);
            transform: translateY(-2px);
        }
        .dark .es-fine-rel:hover {
            border-color: rgba(147, 197, 253, 0.45);
            box-shadow: 0 14px 30px -20px rgba(0, 0, 0, 0.8);
        }

        /* --- Focus, drawn and not implied. No border-radius here: it
               would reshape the element itself; the outline already
               follows its shape. --- */
        #es-fine-page a:focus-visible,
        #es-fine-page button:focus-visible,
        #es-fine-page summary:focus-visible {
            outline: 3px solid #1a3fb0;
            outline-offset: 3px;
        }
        .dark #es-fine-page a:focus-visible,
        .dark #es-fine-page button:focus-visible,
        .dark #es-fine-page summary:focus-visible {
            outline-color: #93c5fd;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-fine-index-link,
            .es-fine-btn,
            .es-fine-btn2,
            .es-fine-caret,
            .es-fine-rel { transition: none; }
            .es-fine-rel:hover { transform: none; }
            /* The hover arrows carry Tailwind's transition-transform, which
               has no reduced-motion gate of its own in the shared bundle. */
            #es-fine-page svg { transition: none !important; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    @php
        $a11yEmail = config('accessibility.contact_email');
        $a11yStandard = config('accessibility.wcag_target_label');
        $a11yReviewed = config('accessibility.declaration_last_reviewed');
        $a11ySla = config('accessibility.response_sla_business_days');
        $a11ySubject = rawurlencode(__('accessibility.breadcrumb'));

        // One ordered register drives both the contents list and the hanging
        // numerals, so a clause can never be numbered twice or listed out of order.
        $clauses = [
            'scope' => __('accessibility.section_scope_title'),
            'commitment' => __('accessibility.section_commitment_title'),
            'status' => __('accessibility.section_status_title'),
            'measures' => __('accessibility.section_measures_title'),
            'third-party' => __('accessibility.section_third_party_title'),
            'limitations' => __('accessibility.known_gaps_title'),
            'feedback' => __('accessibility.section_feedback_title'),
            'updates' => __('accessibility.section_updates_title'),
        ];
        $clauseIds = array_keys($clauses);
        $clauseNum = fn ($id) => str_pad((int) array_search($id, $clauseIds, true) + 1, 2, '0', STR_PAD_LEFT);

        // The nature of each clause, in the register's right-hand field. Read
        // off the clause text itself, not decided for effect: "Conformance
        // status" opens "We do not claim full WCAG conformance for every
        // screen", so it is qualified; "Known limitations" is an admission.
        $clauseNature = [
            'scope' => 'extent',
            'commitment' => 'undertaking',
            'status' => 'qualified',
            'measures' => 'in force',
            'third-party' => 'qualified',
            'limitations' => 'admitted',
            'feedback' => 'undertaking',
            'updates' => 'undertaking',
        ];

        // The head of the instrument: who issued it, against what, when.
        $issueBlock = [
            ['Issued by', __('accessibility.company_lead')],
            ['Standard cited', $a11yStandard],
            ['Last reviewed', $a11yReviewed],
            ['Response target', $a11ySla . ' business days'],
        ];

        // The display panel's real controls. Labels are the widget's own
        // translation keys; the effects are what the code actually does
        // (resources/css/accessibility-widget.css and
        // resources/js/components/AccessibilityWidget.vue).
        $panelRows = [
            [
                __('accessibility.toolbar_font_size'),
                __('accessibility.toolbar_font_default') . ', ' . __('accessibility.toolbar_font_medium') . ', ' . __('accessibility.toolbar_font_large'),
                'The two larger steps raise the root text size to 106.25% and then 112.5%, so the whole interface grows with the words instead of one block of copy reflowing on its own.',
            ],
            [
                __('accessibility.toolbar_high_contrast'),
                'On or off',
                'Lays a contrast boost over the page so text and interface edges separate further from what is behind them.',
            ],
            [
                __('accessibility.toolbar_underline_links'),
                'On or off',
                'Underlines links so they are never identified by colour alone. Pill-shaped buttons are left as they are, because an underline there reads as a mistake.',
            ],
            [
                __('accessibility.toolbar_reduce_motion'),
                'On or off',
                'Cuts animation and transition durations to almost nothing and stops smooth scrolling, for anyone whose browser or system setting is not already asking for it.',
            ],
            [
                __('accessibility.toolbar_reset'),
                'One action',
                'Returns all four controls to their defaults in one go.',
            ],
        ];

        // Row labels summarise the limitation each row states; the limitation
        // itself is the declaration's own text, unchanged.
        $gapRows = [
            ['Calendar views', __('accessibility.gap_calendar')],
            ['Legacy menus and dialogs', __('accessibility.gap_legacy_ui')],
            ['Organizer embeds and outbound links', __('accessibility.gap_embeds')],
            ['Marketing video and audio', __('accessibility.gap_video')],
        ];

        // The return record at the foot: what a report has to contain for it
        // to be actionable, set as a record instead of listed in a sentence.
        $reportRows = [
            ['Where you were', 'The page or the address, so we can open the same thing you had open.'],
            ['What you were trying to do', 'And what happened instead, in as few words as you like.'],
            ['Browser and assistive technology', 'The names, and the versions if you happen to know them.'],
        ];

        $related = [
            ['/privacy', 'Privacy Policy', 'What we collect, who can see it, and how to have it erased.'],
            ['/terms-of-service', 'Terms of Service', 'The agreement that covers your use of Event Schedule.'],
            ['/docs/selfhost/accessibility', 'Accessibility when you selfhost', 'Operators who run their own installation publish their own declaration. The configuration keys are here.'],
            ['/contact', 'Contact', 'A form, if you would rather report a barrier that way, and everything else besides.'],
        ];
    @endphp

    <div id="es-fine-page" class="es-fine-page">

    <!-- ============================================================ -->
    <!-- Masthead: the head of the instrument                         -->
    <!-- ============================================================ -->
    <section class="es-hero noise relative overflow-hidden py-16 sm:py-20" aria-labelledby="es-fine-h1">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 35%, rgba(29, 78, 216, 0.16), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 40%, rgba(14, 165, 233, 0.12), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-fine-rules absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <p class="es-fade-up es-d-1 mb-5">
                <span class="es-fine-tag">
                    <svg class="h-3.5 w-3.5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5a3 3 0 100 6 3 3 0 000-6zM3.75 9.75l4.5 1.5m12-1.5l-4.5 1.5m-3.75 0v3m0 0l-2.25 6m2.25-6l2.25 6" />
                    </svg>
                    Declaration
                </span>
            </p>

            <h1 id="es-fine-h1" class="es-balance es-fade-up es-d-2 es-fine-ink text-4xl font-black tracking-tight sm:text-5xl">
                {{ __('accessibility.h1') }}
            </h1>

            <p class="es-fade-up es-d-3 es-fine-muted es-fine-measure mt-5 text-lg">
                This is the one page on the site whose subject is whether you can read it. So it is set large, ruled like the printed thing it is, and it says plainly what works, what does not yet, and how to tell us.
            </p>

            <dl class="es-fine-record es-fade-up es-d-4 mt-10">
                @foreach ($issueBlock as [$recordKey, $recordVal])
                    <div class="es-fine-record-row">
                        <dt class="es-fine-label">{{ $recordKey }}</dt>
                        <dd class="es-fine-record-val es-fine-ink es-fine-mono es-fine-small">{{ $recordVal }}</dd>
                    </div>
                @endforeach
                <div class="es-fine-record-row es-fine-record-wide">
                    <dt class="es-fine-label">Report a barrier</dt>
                    <dd class="es-fine-record-val es-fine-mono es-fine-small">
                        <a class="es-fine-link" href="mailto:{{ $a11yEmail }}?subject={{ $a11ySubject }}">{{ $a11yEmail }}</a>
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The statement: contents register, then eight clauses         -->
    <!-- ============================================================ -->
    <section class="es-fine-page relative py-4 sm:py-8" aria-labelledby="es-fine-contents-h">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-fine-rules absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="es-fine-column">

                <!-- Counsel notice -->
                <div class="es-fine-notice mb-12 p-4 sm:p-5" data-reveal>
                    <div class="flex gap-3">
                        <svg class="es-fine-notice-mark mt-0.5 h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="es-fine-label es-fine-notice-mark mb-1.5">Notice</p>
                            <p class="es-fine-ink es-fine-small es-fine-measure">{{ __('accessibility.counsel_notice') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contents register -->
                <nav aria-labelledby="es-fine-contents-h" data-reveal>
                    <div class="mb-3 flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
                        <h2 id="es-fine-contents-h" class="es-fine-label">Contents</h2>
                        <p class="es-fine-muted es-fine-small">Eight clauses. Three of them are undertakings, two are qualified, and one is an admission.</p>
                    </div>
                    <ol class="es-fine-index">
                        @foreach ($clauses as $clauseId => $clauseTitle)
                            <li>
                                <a class="es-fine-index-link" href="#{{ $clauseId }}">
                                    <span class="es-fine-index-n" aria-hidden="true">{{ $clauseNum($clauseId) }}</span>
                                    <span class="es-fine-index-t es-fine-small font-semibold">{{ $clauseTitle }}</span>
                                    <span class="es-fine-index-nature">{{ $clauseNature[$clauseId] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </nav>

                <!-- 01 Scope -->
                <section id="scope" class="es-fine-clause scroll-mt-24" aria-labelledby="scope-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('scope') }}</span>
                    <h2 id="scope-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['scope'] }}</h2>
                    <p class="es-fine-muted es-fine-measure">{{ __('accessibility.section_scope_body') }}</p>
                </section>

                <!-- 02 Commitment -->
                <section id="commitment" class="es-fine-clause scroll-mt-24" aria-labelledby="commitment-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('commitment') }}</span>
                    <h2 id="commitment-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['commitment'] }}</h2>
                    <p class="es-fine-muted es-fine-measure">{{ __('accessibility.section_commitment_body', ['wcag_target' => $a11yStandard]) }}</p>
                    @if (config('accessibility.reference_israeli_standard_5568'))
                        <p class="es-fine-muted es-fine-measure mt-3">{{ __('accessibility.section_commitment_is5568_note') }}</p>
                    @endif
                    <p class="es-fine-stamp es-fine-mono es-fine-ink mt-5">
                        <span class="es-fine-label">Standard cited</span>
                        <span>{{ $a11yStandard }}</span>
                    </p>
                </section>

                <!-- 03 Conformance status -->
                <section id="status" class="es-fine-clause scroll-mt-24" aria-labelledby="status-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('status') }}</span>
                    <h2 id="status-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['status'] }}</h2>
                    <p class="es-fine-muted es-fine-measure">{{ __('accessibility.section_status_body') }}</p>
                    {{-- Refer to clauses by LINK, never by their numeral: the hanging
                         numerals are decorative and aria-hidden, so "see clause 06"
                         would be a dead reference for anyone not looking at them. --}}
                    <p class="es-fine-muted es-fine-small es-fine-measure mt-3">
                        The honest version of that sentence is further down:
                        <a class="es-fine-link" href="#limitations">the areas we know are behind, named</a>.
                    </p>
                </section>

                <!-- 04 Measures we take, with the display panel spelled out -->
                <section id="measures" class="es-fine-clause scroll-mt-24" aria-labelledby="measures-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('measures') }}</span>
                    <h2 id="measures-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['measures'] }}</h2>
                    <p class="es-fine-muted es-fine-measure">{{ __('accessibility.section_measures_body') }}</p>

                    <h3 class="es-fine-ink mb-3 mt-9 text-lg font-bold">{{ __('accessibility.toolbar_heading') }}, spelled out</h3>
                    <p class="es-fine-muted es-fine-small es-fine-measure mb-4">
                        The panel is a first-party control, not a third-party overlay: four settings and a reset, each one a single change you can see. Here is every one of them and exactly what it does.
                    </p>

                    <div class="es-fine-spec" data-reveal>
                        @foreach ($panelRows as [$panelName, $panelStates, $panelEffect])
                            <div class="es-fine-spec-row">
                                <div>
                                    <p class="es-fine-spec-key es-fine-ink">{{ $panelName }}</p>
                                    <p class="es-fine-muted es-fine-mono es-fine-small">{{ $panelStates }}</p>
                                </div>
                                <p class="es-fine-muted es-fine-small">{{ $panelEffect }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-fine-muted es-fine-small es-fine-measure mt-4">
                        Where to find it: the panel appears on a schedule's public pages, its calendar and its event pages, and only where that schedule has switched it on, under Advanced settings when editing a schedule. It is off until somebody turns it on, and it carries a link straight back to this page.
                        <a class="es-fine-link" href="{{ marketing_url('/docs/creating-schedules#settings-advanced') }}">The setting is documented here.</a>
                    </p>

                    <details class="es-fine-disclose mt-4">
                        <summary class="es-fine-ink">
                            <svg class="es-fine-caret es-fine-accent h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                            Where those choices are kept
                        </summary>
                        <div class="es-fine-small es-fine-muted es-fine-measure px-4 pb-4">
                            <p>
                                Your choices live in your own browser, on that one device, and are read back on your next visit. They are not sent to us and not attached to your account, so a shared or borrowed computer keeps its own settings rather than inheriting yours.
                            </p>
                            <p class="mt-3">
                                Signed-in visitors can also hide the panel itself if they do not want it in the corner, and switch it back on from their profile settings.
                            </p>
                        </div>
                    </details>

                    <h3 class="es-fine-ink mb-3 mt-9 text-lg font-bold">Structure, keyboard and focus</h3>
                    <p class="es-fine-muted es-fine-small es-fine-measure mb-4">
                        Four of those measures are true of this page, right now, and you can check every one without taking our word for it.
                    </p>
                    <ul class="es-fine-muted es-fine-small es-fine-measure space-y-2.5">
                        <li class="flex gap-2.5">
                            <span class="es-fine-accent es-fine-mono flex-none font-bold" aria-hidden="true">01</span>
                            <span>Press Tab from the top and the first thing you reach is a skip link that jumps the whole navigation and lands on the page itself.</span>
                        </li>
                        <li class="flex gap-2.5">
                            <span class="es-fine-accent es-fine-mono flex-none font-bold" aria-hidden="true">02</span>
                            <span>Keep going and focus is drawn rather than implied: a three pixel outline, held three pixels clear of whatever it is around.</span>
                        </li>
                        <li class="flex gap-2.5">
                            <span class="es-fine-accent es-fine-mono flex-none font-bold" aria-hidden="true">03</span>
                            <span>Links are underlined, so nothing here is identified by colour alone, and the contents list above is a real ordered list of real anchors.</span>
                        </li>
                        <li class="flex gap-2.5">
                            <span class="es-fine-accent es-fine-mono flex-none font-bold" aria-hidden="true">04</span>
                            <span>Nothing in this statement is set below thirteen pixels, the clauses are one heading each in order, and the schedule of limitations further down is marked up as a table with real row and column headers, because it is a record.</span>
                        </li>
                    </ul>

                    {{-- These two figures are MEASURED, not asserted: the lowest ratio
                         any text on this page scores against the surface actually
                         painted behind it, rounded DOWN so drift can only make them
                         conservative. Measured 6.14 light (the link blue on paper)
                         and 6.74 dark (muted ink on the subsurface). Re-measure them
                         if the palette in the style block above ever changes. --}}
                    <p class="es-fine-muted es-fine-small es-fine-measure mt-6">
                        And the contrast is measured rather than assumed. Every piece of text here was checked against whatever is painted behind it, in both colour schemes, and the weakest pairing on the page still clears the standard by a wide margin.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="es-fine-stamp es-fine-mono es-fine-ink">
                            <span class="es-fine-label">Lowest, light</span>
                            <span>6.1 : 1</span>
                        </span>
                        <span class="es-fine-stamp es-fine-mono es-fine-ink">
                            <span class="es-fine-label">Lowest, dark</span>
                            <span>6.7 : 1</span>
                        </span>
                        <span class="es-fine-stamp es-fine-mono es-fine-ink">
                            <span class="es-fine-label">AA asks</span>
                            <span>4.5 : 1</span>
                        </span>
                    </div>
                </section>

                <!-- 05 Third-party content and services -->
                <section id="third-party" class="es-fine-clause scroll-mt-24" aria-labelledby="third-party-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('third-party') }}</span>
                    <h2 id="third-party-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['third-party'] }}</h2>
                    <p class="es-fine-muted es-fine-measure">{{ __('accessibility.section_third_party_body') }}</p>
                </section>

                <!-- 06 Known limitations, as a schedule -->
                <section id="limitations" class="es-fine-clause scroll-mt-24" aria-labelledby="limitations-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('limitations') }}</span>
                    <h2 id="limitations-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['limitations'] }}</h2>
                    <p class="es-fine-muted es-fine-measure mb-6">
                        A declaration is only worth reading if it admits something. These are the areas we know are behind, and they are set out as a record rather than a paragraph so you can find yours quickly.
                    </p>

                    <div class="es-fine-sheet es-fine-scroll p-4 sm:p-6" data-reveal>
                        <table class="es-fine-table">
                            {{-- A caption, not a heading: it names the record for anyone
                                 who reaches the table without seeing the clause above it.
                                 Hidden visually because the clause heading already says it,
                                 and kept out of the scrolling box so nothing is clipped. --}}
                            <caption class="sr-only">Known accessibility limitations, by area of the product.</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Area</th>
                                    <th scope="col">What may not work yet</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($gapRows as [$gapArea, $gapText])
                                    <tr>
                                        <th scope="row" class="es-fine-ink">{{ $gapArea }}</th>
                                        <td class="es-fine-muted es-fine-small">{{ $gapText }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="es-fine-muted es-fine-small es-fine-measure mt-4">{{ __('accessibility.audit_backlog_note') }}</p>
                </section>

                <!-- 07 Feedback and requests -->
                <section id="feedback" class="es-fine-clause scroll-mt-24" aria-labelledby="feedback-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('feedback') }}</span>
                    <h2 id="feedback-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['feedback'] }}</h2>
                    <p class="es-fine-muted es-fine-measure">{{ __('accessibility.section_feedback_body', ['email' => $a11yEmail, 'sla' => $a11ySla]) }}</p>
                    <p class="es-fine-measure mt-4">
                        <a class="es-fine-link es-fine-mono" href="mailto:{{ $a11yEmail }}?subject={{ $a11ySubject }}">{{ $a11yEmail }}</a>
                    </p>
                </section>

                <!-- 08 Updates -->
                <section id="updates" class="es-fine-clause scroll-mt-24" aria-labelledby="updates-h" data-reveal>
                    <span class="es-fine-num" aria-hidden="true">{{ $clauseNum('updates') }}</span>
                    <h2 id="updates-h" class="es-fine-ink mb-3 text-2xl font-bold tracking-tight">{{ $clauses['updates'] }}</h2>
                    <p class="es-fine-muted es-fine-measure">{{ __('accessibility.section_updates_body', ['date' => $a11yReviewed]) }}</p>
                    <p class="es-fine-stamp es-fine-mono es-fine-ink mt-5">
                        <span class="es-fine-label">Last reviewed</span>
                        <span>{{ $a11yReviewed }}</span>
                    </p>
                </section>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Finale: the undertaking, and the foot of the instrument       -->
    <!-- ============================================================ -->
    <section class="es-fine-page px-2 pb-16 pt-8 sm:px-4 lg:pb-24" aria-labelledby="es-fine-report-h">
        <div class="mx-auto max-w-4xl">
            <div class="es-fine-finale px-6 py-12 sm:px-10 lg:py-16" data-reveal="panel">
                <p class="mb-4"><span class="es-fine-tag">Undertaking</span></p>
                <h2 id="es-fine-report-h" class="es-balance es-fine-ink es-fine-measure mb-4 text-3xl font-black tracking-tight md:text-4xl">
                    If you hit a wall here, we would rather know than not.
                </h2>
                <p class="es-fine-muted es-fine-measure mb-6">
                    A barrier is far easier to fix when somebody says where it was. Three lines is enough, and it does not have to be tidy.
                </p>

                {{-- The same specimen rows as the display panel, because this is
                     the other half of the same argument: there the product tells
                     you what each control does, here we tell you what a report
                     needs so it can be acted on. --}}
                <dl class="es-fine-spec es-fine-spec-inset mb-6">
                    @foreach ($reportRows as [$reportKey, $reportVal])
                        <div class="es-fine-spec-row">
                            <dt class="es-fine-spec-key es-fine-ink">{{ $reportKey }}</dt>
                            <dd class="es-fine-muted es-fine-small">{{ $reportVal }}</dd>
                        </div>
                    @endforeach
                </dl>

                <p class="es-fine-stamp es-fine-mono es-fine-ink mb-8">
                    <span class="es-fine-label">Response target</span>
                    <span>{{ $a11ySla }} business days</span>
                </p>

                <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center">
                    <a href="{{ marketing_url('/contact') }}" class="es-fine-btn2 inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3.5 font-semibold">
                        Use the contact form
                    </a>
                    <a href="mailto:{{ $a11yEmail }}?subject={{ $a11ySubject }}" class="es-fine-btn group inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3.5 font-semibold">
                        Email {{ $a11yEmail }}
                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>

                {{-- The foot: the instrument closes on the register it opened
                     with, under the hairline a printed statement closes on. --}}
                <div class="es-fine-foot es-fine-muted es-fine-mono es-fine-small">
                    {{ __('accessibility.company_lead') }} &middot; {{ $a11yStandard }} &middot; last reviewed {{ $a11yReviewed }}
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Related documents                                            -->
    <!-- ============================================================ -->
    <section class="es-fine-page border-t py-16" style="border-color: var(--es-fine-rule-soft);" aria-labelledby="es-fine-related-h">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <p class="mb-2"><span class="es-fine-tag">The set</span></p>
            <h2 id="es-fine-related-h" class="es-fine-ink mb-8 text-2xl font-bold tracking-tight md:text-3xl">The rest of the paperwork</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($related as [$relHref, $relTitle, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-fine-rel group flex flex-col p-5" data-reveal>
                        <h3 class="es-fine-ink mb-2 text-base font-semibold">{{ $relTitle }}</h3>
                        <p class="es-fine-muted es-fine-small mb-4 flex-1">{{ $relBlurb }}</p>
                        <span class="es-fine-accent es-fine-small mt-auto inline-flex items-center gap-1 font-semibold">
                            Read
                            <svg aria-hidden="true" class="h-4 w-4 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    </div>

    <x-marketing.related-pages />

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
