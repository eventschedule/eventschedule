<x-marketing-layout>
    <x-slot name="title">Terms of Service - Event Schedule</x-slot>
    <x-slot name="description">Terms of Service for Event Schedule - the rules and guidelines for using our platform, including account eligibility, data ownership, and liability.</x-slot>
    <x-slot name="breadcrumbTitle">Terms of Service</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Terms of Service - Event Schedule",
        "description": "Terms of Service for Event Schedule - the rules and guidelines for using our platform, including account eligibility, data ownership, and liability.",
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "Terms of Service"
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
           Terms "The Fine Print" styles.

           CONCEPT: an executed legal instrument, given the apparatus a
           contract deserves. Not one legal word changes; what changes is
           the reading. Sixteen clauses get a section sign and a number
           you can cite and link to, the run-on warranties are unpacked
           into their own lettered limbs, the defined terms are set in
           small caps wherever they appear, and the whole schedule of
           contents rides alongside the text. "The fine print" is the
           joke and the argument: the apparatus is set small, the
           agreement itself is set large.

           WHY THIS ARGUES THE PRODUCT: it does not have to invent a
           feature. Clause 7 already says "The User owns all data
           generated in their eventschedule.com account" and "Event
           Schedule will not access, modify or distribute User account
           data." That is the product promise, in the document's own
           words, so it is the one clause the page sets as an endorsement
           panel. Every other capability claim was deliberately left out:
           a terms page that markets is a terms page nobody trusts.

           THE LEGAL TEXT IS DATA, NOT MARKUP. Every clause lives in the
           $clauses array below and is rendered through one loop, so the
           schedule of contents, the rail and the body cannot drift. DO
           NOT edit a character of it, do not reorder it, and do not add
           a plain-language gloss beside it - a summary that narrows a
           clause is worse than no summary. Markers like "(i)" stay
           inside the item text and are hung into the margin with a
           negative text-indent, so copying the page still yields the
           instrument verbatim.

           DEVICES DELIBERATELY AVOIDED, because sibling pages own them
           (checked on disk, treat as binding):
             - paper stock with laid/chain lines and a serif measure:
               /about "The Colophon" owns that, and it is also blue.
             - dotted TOC leaders: /faq notes four siblings already use
               leader dots, so the contents here is numerals and
               hairlines.
             - the printed legal-pad margin rule: /for-comedians,
               /for-spoken-word and /for-online-classes own it.
             - the printer's double rule: /about, /browse and /faq.
           What is left, and what this page is built on, is the section
           sign, the hanging roman limb, small-caps defined terms, a
           typographic watermark, an execution block and a real print
           edition. None of those appear anywhere else in the WP.

           THE EXECUTION BLOCK closes the instrument after clause 16 and
           reuses the cover sheet's own .es-fine-facts register, so the
           document is book-ended by one device instead of stopping mid
           air. Its rows are facts about the DOCUMENT (how many clauses,
           what sits beside it, that it prints) or pointers into it. Do
           not add a row that restates the substance of a clause: the
           hero's "Governing law" row is the only restatement on the page
           and it is a jurisdiction stamp, not a gloss.

           THE SET IS THE POINT. The four legal pages (terms, privacy,
           accessibility, self-hosting-terms) share this nickname and the
           es-fine- prefix on purpose: legal pages have to look like one
           set of paperwork, not four campaigns. So every token here is
           the family's, read off the siblings on disk rather than
           invented - ground #f5f5f2 / #0b0c0f, sheet #ffffff / #12141a,
           ink #16181c / #e9ebef, body #24272c / #d9dce2, muted #4b5158 /
           #9aa1ab, accent #1d4ed8 / #93c5fd, the 0.9rem radius, the
           64ch / 1.0625rem / 1.7 measure, and the shared class
           vocabulary this page draws on (-page / -ink / -muted / -accent
           / -mono / -tag / -title / -lede / -intro / -masthead / -clause
           / -num / -h / -link). If you re-ink one of the four, re-ink all
           four.

           COLOUR: the inherited blue, kept, but demoted from a three-stop
           display gradient to ONE functional ink - clause numbers, links,
           the index marker, the focus ring. The shared brand -> sky ->
           cyan chrome gradient is deliberately not adopted as a page
           accent, and there is no gradient heading text left to score.

           THE FACE IS THE INHERITED SANS, not a serif, even though the
           concept would take one: /about "The Colophon" is already a
           serif-on-laid-paper page in the same blue, and the other three
           legal pages set h1, h2 and body in the sans. Distinctiveness
           here comes from structure, not from the face.

           Measured with the campaign probe, on the family tokens:
             light  ink 16.27 on ground / 17.77 on sheet; body 13.72 /
                    14.98; muted 7.35 / 8.02 / 7.30 on the accent tint
                    #f1f4fd; accent 6.14 / 6.70 / 6.09.
             dark   ink 16.39 on ground / 15.42 on sheet; body 14.24 /
                    13.40; muted 7.51 / 7.07 / 6.60 on the tint #161b28;
                    accent 10.85 / 10.21 / 9.53.

           NO FIXED PHYSICAL OBJECT ANYWHERE. There is no always-dark or
           always-light band on this page, so nothing here has to survive
           the --bands mode diff. If you add one, pin .grid-overlay,
           .animate-shimmer and .es-claim:focus-within inside it.

           NEVER text-gray-500 or dark:text-gray-500: the grounds are
           tinted and both measure below AA on them. Use .es-fine-muted
           (7.35 light / 7.51 dark). MEASURE is 64ch on .es-fine-p and
           .es-fine-limbs; do not widen it.

           BLADE RULE for this block: no @supports() probes - a "#" hex
           inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground, stock and ink (family tokens) ------------------- */
        .es-fine-page {
            background-color: #f5f5f2;
            color: #16181c;
            --fine-hair: rgba(22, 24, 28, 0.12);
            --fine-hair-strong: rgba(22, 24, 28, 0.22);
        }
        .dark .es-fine-page {
            background-color: #0b0c0f;
            color: #e9ebef;
            --fine-hair: rgba(233, 235, 239, 0.12);
            --fine-hair-strong: rgba(233, 235, 239, 0.24);
        }

        .es-fine-ink { color: #16181c; }
        .dark .es-fine-ink { color: #e9ebef; }
        .es-fine-muted { color: #4b5158; }
        .dark .es-fine-muted { color: #9aa1ab; }
        .es-fine-accent { color: #1d4ed8; }
        .dark .es-fine-accent { color: #93c5fd; }

        /* A cut edge, never a shadow: this is a document, not a card deck. */
        .es-fine-edge { border-top: 1px solid var(--fine-hair); }

        /* --- The apparatus: set small, in the mono stack ------------- */
        .es-fine-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-fine-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-tag { color: #9aa1ab; }

        /* The watermark. Typographic, not an illustration: one section
           sign at display size, sitting under the cover sheet. */
        .es-fine-mark {
            position: absolute;
            inset-inline-end: -2rem;
            top: -7rem;
            font-size: 22rem;
            line-height: 1;
            font-weight: 700;
            color: #16181c;
            opacity: 0.04;
            user-select: none;
        }
        .dark .es-fine-mark { color: #e9ebef; opacity: 0.055; }
        @media (max-width: 640px) {
            .es-fine-mark { font-size: 13rem; top: -3.5rem; inset-inline-end: -1rem; }
        }

        /* --- Cover sheet -------------------------------------------
           The masthead rule, the title clamp, the mono party line and the
           46ch intro are the family's opening gesture: all four legal
           pages start on exactly this. */
        .es-fine-masthead {
            border-bottom: 2px solid var(--fine-hair-strong);
            padding-bottom: 1.75rem;
        }
        .es-fine-title {
            font-size: clamp(2.25rem, 5vw, 3rem);
            line-height: 1.05;
            letter-spacing: -0.02em;
            font-weight: 900;
            color: #16181c;
        }
        .dark .es-fine-title { color: #e9ebef; }
        .es-fine-lede {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.9rem;
            color: #4b5158;
        }
        .dark .es-fine-lede { color: #9aa1ab; }
        .es-fine-intro {
            max-width: 46ch;
            font-size: 1.0625rem;
            line-height: 1.7;
            color: #4b5158;
        }
        .dark .es-fine-intro { color: #9aa1ab; }

        /* The acceptance paragraph, on its own leaf. */
        .es-fine-recital {
            background-color: #ffffff;
            border: 1px solid var(--fine-hair);
            border-inline-start: 3px solid var(--fine-hair-strong);
            border-radius: 0 0.9rem 0.9rem 0;
        }
        .dark .es-fine-recital { background-color: #12141a; }

        /* Document control: four checkable facts, each a pointer into the
           instrument rather than a restatement of it. */
        .es-fine-facts { border-top: 1px solid var(--fine-hair); }
        .es-fine-fact {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.25rem 1rem;
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--fine-hair);
        }
        .es-fine-fact dt {
            flex: none;
            width: 9.5rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.64rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-fact dt { color: #9aa1ab; }
        .es-fine-fact dd {
            min-width: 0;
            flex: 1 1 14rem;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #16181c;
        }
        .dark .es-fine-fact dd { color: #e9ebef; }

        /* --- The instrument: contents rail beside the clause column -- */
        .es-fine-doc { display: grid; gap: 3rem; }
        @media (min-width: 1024px) {
            .es-fine-doc { grid-template-columns: 14rem minmax(0, 1fr); gap: 4rem; }
            .es-fine-rail {
                position: sticky;
                top: 6rem;
                align-self: start;
                max-height: calc(100vh - 8rem);
                overflow-y: auto;
                overscroll-behavior: contain;
            }
        }

        /* Contents: numerals and hairlines. No leader dots on purpose. */
        .es-fine-toc { column-count: 1; column-gap: 2.5rem; }
        @media (min-width: 640px) and (max-width: 1023px) {
            .es-fine-toc { column-count: 2; }
        }
        .es-fine-toc li { break-inside: avoid; }
        .es-fine-toc-item {
            display: flex;
            align-items: baseline;
            gap: 0.7rem;
            padding: 0.45rem 0;
            border-bottom: 1px solid var(--fine-hair);
            font-size: 0.86rem;
            line-height: 1.4;
            color: #4b5158;
            transition: color 0.2s ease, border-color 0.2s ease;
        }
        .dark .es-fine-toc-item { color: #9aa1ab; }
        .es-fine-toc-item:hover { color: #1d4ed8; border-color: var(--fine-hair-strong); }
        .dark .es-fine-toc-item:hover { color: #93c5fd; }
        .es-fine-toc-num {
            flex: none;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #1d4ed8;
        }
        .dark .es-fine-toc-num { color: #93c5fd; }

        /* --- A clause -------------------------------------------------
           The section sign hangs in its own gutter from md up, so the
           clause can be cited and linked the way a contract expects. */
        .es-fine-clause {
            display: grid;
            gap: 0.75rem;
            padding-top: 2.25rem;
            scroll-margin-top: 6rem;
        }
        .es-fine-clause + .es-fine-clause {
            margin-top: 2.25rem;
            border-top: 1px solid var(--fine-hair);
        }
        @media (min-width: 768px) {
            .es-fine-clause { grid-template-columns: 4.75rem minmax(0, 1fr); gap: 0 1.5rem; }
        }
        .es-fine-num {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            /* 0.72rem x 2 is 23px, one pixel under the WCAG 2.2 target minimum,
               and this is the clause index every reader taps to navigate. */
            line-height: 2;
            padding-block: 0.15rem;
            color: #1d4ed8;
            transition: color 0.2s ease;
        }
        .dark .es-fine-num { color: #93c5fd; }
        .es-fine-num:hover { color: #16181c; }
        .dark .es-fine-num:hover { color: #e9ebef; }

        .es-fine-h {
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1.3;
            letter-spacing: -0.01em;
            color: #16181c;
        }
        .dark .es-fine-h { color: #e9ebef; }

        /* The measure. This is the whole point of the page: the fine
           print, set at a size and a width that can be read. */
        .es-fine-p {
            max-width: 64ch;
            margin-top: 0.95rem;
            font-size: 1.0625rem;
            line-height: 1.7;
            color: #24272c;
        }
        .dark .es-fine-p { color: #d9dce2; }

        /* Defined terms, wherever they appear in the running text. */
        .es-fine-term {
            font-variant: small-caps;
            letter-spacing: 0.035em;
            font-weight: 600;
        }

        /* Limbs of a provision. The "(i)" is real text, hung into the
           margin - nothing is generated, so nothing is lost on copy. */
        .es-fine-limbs {
            list-style: none;
            max-width: 64ch;
            margin-top: 0.85rem;
        }
        .es-fine-limbs li {
            padding-inline-start: 2.6rem;
            text-indent: -2.6rem;
            font-size: 1.0625rem;
            line-height: 1.7;
            color: #24272c;
        }
        .dark .es-fine-limbs li { color: #d9dce2; }
        .es-fine-limbs li + li { margin-top: 0.6rem; }

        /* --- The endorsement: clause 7, and only clause 7 ------------ */
        .es-fine-endorse {
            max-width: 64ch;
            margin-top: 1rem;
            padding: 1.4rem 1.6rem;
            background-color: #f1f4fd;
            border-inline-start: 3px solid #1d4ed8;
            border-radius: 0 0.9rem 0.9rem 0;
        }
        .dark .es-fine-endorse { background-color: #161b28; border-inline-start-color: #93c5fd; }
        .es-fine-endorse-line {
            font-size: 1.2rem;
            line-height: 1.5;
            font-weight: 600;
            color: #16181c;
        }
        .dark .es-fine-endorse-line { color: #e9ebef; }
        .es-fine-endorse-line + .es-fine-endorse-line { margin-top: 0.7rem; }

        /* --- Execution: the closing apparatus ------------------------
           The cover sheet opens with a document-control register, so the
           close carries the same register rather than trailing off after
           clause 16. The endmark's own hairlines are the closing rule,
           which is why there is no border-top here as well. */
        .es-fine-close {
            max-width: 64ch;
            margin-top: 3.25rem;
        }
        @media (min-width: 768px) {
            /* An execution block spans the page, not the text column, so it
               starts under the numeral gutter. Add the gutter (4.75rem) and
               the gap (1.5rem) to the measure so its right edge still lands
               on the clause measure instead of stopping 6rem short. */
            .es-fine-close { max-width: calc(64ch + 6.25rem); }
        }
        .es-fine-endmark {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #4b5158;
        }
        .dark .es-fine-endmark { color: #9aa1ab; }
        .es-fine-endmark::before,
        .es-fine-endmark::after {
            content: "";
            flex: 1 1 auto;
            height: 1px;
            background-color: var(--fine-hair-strong);
        }

        /* --- Links and counterpart documents ------------------------ */
        .es-fine-link {
            color: #1d4ed8;
            text-decoration: underline;
            text-underline-offset: 2px;
        }
        .es-fine-link:hover { color: #16181c; }
        .dark .es-fine-link { color: #93c5fd; }
        .dark .es-fine-link:hover { color: #e9ebef; }

        .es-fine-card {
            background-color: #ffffff;
            border: 1px solid var(--fine-hair);
            border-radius: 0.9rem;
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .dark .es-fine-card { background-color: #12141a; }
        .es-fine-card:hover {
            border-color: var(--fine-hair-strong);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -22px rgba(22, 24, 28, 0.55);
        }
        .dark .es-fine-card:hover { box-shadow: 0 12px 28px -22px rgba(0, 0, 0, 0.9); }
        .es-fine-card:hover .es-fine-card-title { color: #1d4ed8; }
        .dark .es-fine-card:hover .es-fine-card-title { color: #93c5fd; }
        .es-fine-card-title { transition: color 0.2s ease; }

        /* --- Focus. No border-radius here: an outline already follows
               the element's own shape. --------------------------------- */
        #es-fine-page a:focus-visible {
            outline: 2px solid #1d4ed8;
            outline-offset: 3px;
        }
        .dark #es-fine-page a:focus-visible { outline-color: #93c5fd; }

        @media (prefers-reduced-motion: reduce) {
            .es-fine-card:hover { transform: none; }
        }

        /* --- The print edition. People print their terms. ------------ */
        @media print {
            .es-fine-page { background-color: #ffffff; color: #000000; }
            .es-fine-mark, .es-fine-rail { display: none; }
            .es-fine-ink, .es-fine-h, .es-fine-num, .es-fine-muted,
            .es-fine-tag, .es-fine-lede, .es-fine-intro, .es-fine-title,
            .es-fine-p, .es-fine-endorse-line, .es-fine-link,
            .es-fine-endmark, .es-fine-fact dt, .es-fine-fact dd { color: #000000; }
            .es-fine-endmark::before,
            .es-fine-endmark::after { background-color: #999999; }
            .es-fine-doc { display: block; }
            .es-fine-clause, .es-fine-close { break-inside: avoid; }
            .es-fine-p, .es-fine-limbs, .es-fine-endorse, .es-fine-close { max-width: none; font-size: 11pt; }
            .es-fine-endorse { background-color: transparent; border-inline-start-color: #000000; }
            .es-fine-recital, .es-fine-card { border-color: #999999; }
        }
    </style>

    @php
        // ==============================================================
        // THE INSTRUMENT. Verbatim from the published Terms of Service,
        // in the published order. Do not edit, reword, reorder or
        // summarise any of it - see the note at the top of the style
        // block. Block kinds:
        //   'p'     a paragraph
        //   'limbs' a lead-in plus the provision's own limbs, whose
        //           "(i)" markers are part of the text
        //   'raw'   a paragraph that carries a link, so it is authored
        //           as markup rather than escaped text
        // The numeral, the anchor, the contents rail and the body all
        // come from this one array, so they cannot disagree.
        // ==============================================================
        $clauses = [
            [
                'id' => 'definitions',
                'title' => 'Definitions',
                'blocks' => [
                    ['p', 'Users who create accounts to offer events and/or services on eventschedule.com are defined as "User Accounts" which include "Hosts", "Talent", "Venues", "Curators" or "Influences."'],
                    ['p', 'Clients who use eventschedule.com services to view and/or book events are defined as "Attendees" or "Guests".'],
                ],
            ],
            [
                'id' => 'account-eligibility',
                'title' => 'Account Eligibility',
                'blocks' => [
                    ['limbs', 'By agreeing to these Terms, you represent and warrant to us:', [
                        '(i) that you are at least eighteen (18) years of age;',
                        '(ii) that you have not previously been suspended or removed from the Website and',
                        '(iii) that your use of the Website is in compliance with any and all applicable laws and regulations.',
                    ]],
                ],
            ],
            [
                'id' => 'personal-responsibility',
                'title' => 'Personal Responsibility',
                'blocks' => [
                    ['p', 'User Accounts are responsible for all activity occurring within their account and saved profile and events including all laws relating to personal & public data, privacy, personal information, international copyright and trademark laws.'],
                ],
            ],
            [
                'id' => 'profile-event-legality',
                'title' => 'Profile & Event Legality',
                'blocks' => [
                    ['p', 'Your profile and event data and its transfer must not violate any applicable local, state, federal and international laws and regulations ("Laws") (including without limitation those relating to export control or electronic communications).'],
                ],
            ],
            [
                'id' => 'your-event-obligations',
                'title' => 'Your Event Obligations',
                'blocks' => [
                    ['p', 'You are solely responsible for, and Event Schedule disclaims all liability for, the provision of any goods or services promoted and/or sold to your customers and/or attendees as part of your use of the Event Schedule platform, and any obligations you may owe to your clients.'],
                ],
            ],
            [
                'id' => 'customer-service',
                'title' => 'Customer Service',
                'blocks' => [
                    ['p', 'Customer service for your own event is your responsibility. We provide customer service to you, the account user, for use of the Event Schedule platform.'],
                    ['p', 'You are solely responsible for all customer service policies and issues relating to your profile and events. In performing customer service for your profile or event, you will always present yourself as a separate entity from Event Schedule.'],
                ],
            ],
            [
                'id' => 'data-ownership-access',
                'title' => 'Data Ownership & Access',
                // The one clause set as an endorsement: it is the product
                // promise, in the instrument's own words.
                'endorse' => true,
                'blocks' => [
                    ['p', 'The User owns all data generated in their eventschedule.com account.'],
                    ['p', 'Event Schedule will not access, modify or distribute User account data.'],
                ],
            ],
            [
                'id' => 'platform-service-data-use',
                'title' => 'Platform Service & Data Use',
                'blocks' => [
                    ['p', 'You hereby grant Event Schedule a non-exclusive, fully sublicensable, worldwide, royalty-free right to collect, use, copy, store, and transmit data solely for the purpose of providing services to User Accounts.'],
                ],
            ],
            [
                'id' => 'limited-license-termination',
                'title' => 'Limited License & Termination of Use',
                'blocks' => [
                    ['p', 'Event Schedule grants Users & Clients a limited license to access eventschedule.com This limited license may be revoked if deemed legally necessary, without notice to the User or Client and penalty to Event Schedule.'],
                    ['p', 'You will lose your license to use the Service if you violate any provision of this Agreement. Event Schedule\'s policy is to investigate violations of this Agreement before terminating/deactivating accounts, however the decision to terminate any User account is the sole discretion of Event Schedule.'],
                ],
            ],
            [
                'id' => 'limitation-of-liability',
                'title' => 'Limitation of Liability',
                'blocks' => [
                    ['p', 'To the maximum extent permitted by applicable law, in no event shall Event Schedule or its suppliers be liable for any special, incidental, indirect, or consequential damages whatsoever (including, but not limited to, damages for loss of profits, loss of data or other information, for business interruption, for personal injury, loss of privacy arising out of or in any way related to the use of or inability to use the Service, third-party software and/or third-party hardware used with the Service, or otherwise in connection with any provision of this Terms), even if the Company or any supplier has been advised of the possibility of such damages and even if the remedy fails of its essential purpose.'],
                ],
            ],
            [
                'id' => 'as-is-disclaimer',
                'title' => '"As Is" and "As Available" Disclaimer',
                'blocks' => [
                    ['limbs', 'Without limiting the foregoing, neither Event Schedule nor any of the company\'s provider makes any representation or warranty of any kind, express or implied:', [
                        '(i) as to the operation or availability of the Service, or the information, content, and materials or products included thereon;',
                        '(ii) that the Service will be uninterrupted or error-free;',
                        '(iii) as to the accuracy, reliability, or currency of any information or content provided through the Service; or',
                        '(iv) that the Service, its servers, the content, or e-mails sent from or on behalf of the Company are free of viruses, scripts, trojan horses, worms, malware, timebombs or other harmful components.',
                    ]],
                    ['p', 'Some jurisdictions do not allow the exclusion of certain types of warranties or limitations on applicable statutory rights of a consumer, so some or all of the above exclusions and limitations may not apply to You. But in such a case the exclusions and limitations set forth in this section shall be applied to the greatest extent enforceable under applicable law.'],
                ],
            ],
            [
                'id' => 'us-legal-compliance',
                'title' => 'United States Legal Compliance',
                'blocks' => [
                    ['limbs', 'You represent and warrant that', [
                        '(i) You are not located in a country that is subject to the United States government embargo, or that has been designated by the United States government as a "terrorist supporting" country, and',
                        '(ii) You are not listed on any United States government list of prohibited or restricted parties.',
                    ]],
                ],
            ],
            [
                'id' => 'governing-law',
                'title' => 'Governing Law',
                'blocks' => [
                    ['p', 'The laws of the United States of America, State of Florida, shall govern these Terms of Service & Conditions of Use. Your use of Event Schedule may also be subject to other local, state, national, or international laws.'],
                ],
            ],
            [
                'id' => 'right-to-amend',
                'title' => 'Right to Amend',
                'blocks' => [
                    ['p', 'Event Schedule may amend this Agreement upon notice to you, which may be provided through email, your account dashboard, and/or the Event Schedule website. You agree that any changes to this Agreement will be binding on you 7 days after the amendment is made (or, if a longer period if required by applicable law). If you elect to not accept the changes to this Agreement, you must immediately cancel/cease using the Event Schedule platform.'],
                ],
            ],
            [
                'id' => 'accessibility',
                'title' => 'Accessibility',
                'blocks' => [
                    ['raw', 'Event Schedule is committed to making our platform accessible to everyone. For details on our accessibility standards and how to report any issues, see our <a href="' . marketing_url('/accessibility') . '" class="es-fine-link">Accessibility Statement</a>.'],
                ],
            ],
            [
                'id' => 'communication-resolution',
                'title' => 'Communication & Resolution',
                'blocks' => [
                    ['raw', 'Questions regarding the terms &amp; conditions of Event Schedule account(s), contact: <a href="mailto:legal@eventschedule.com" class="es-fine-link">legal@eventschedule.com</a>'],
                ],
            ],
        ];

        // Defined terms get the small-caps treatment wherever the
        // instrument uses them. Longest first, so "User Accounts" is
        // marked before any single word inside it, and case-sensitive,
        // so the lower-case "laws" in clause 4 is left alone while the
        // defined "Laws" is marked.
        $definedTerms = ['User Accounts', 'Attendees', 'Influences', 'Curators', 'Venues', 'Talent', 'Guests', 'Hosts', 'Laws'];
        $markTerms = function ($text) use ($definedTerms) {
            $html = e($text);
            foreach ($definedTerms as $term) {
                $html = str_replace($term, '<span class="es-fine-term">' . $term . '</span>', $html);
            }
            return $html;
        };
    @endphp

    <div id="es-fine-page" class="es-fine-page">

    <!-- ============================================================ -->
    <!-- 1. The cover sheet                                           -->
    <!-- ============================================================ -->
    {{-- No .es-hero here on purpose: that class is a JS hook in
         marketing-home.js that installs a pointermove + requestAnimationFrame
         loop to drive .es-spot / .es-wall-tilt. This cover sheet has neither,
         so the class carries no CSS (grepped: zero rules in marketing.css and
         in the built bundle) and only bought an idle rAF loop. --}}
    <section class="noise relative overflow-hidden py-20 sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 22% 58%, rgba(30, 58, 138, 0.2), rgba(30, 58, 138, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 32%, rgba(96, 133, 220, 0.15), rgba(96, 133, 220, 0) 65%);"></div>
            <div class="grid-pattern absolute inset-0"></div>
            <span class="es-fine-mark">&sect;</span>
        </div>

        {{-- The cover sheet takes the instrument's own left margin, so the
             document has one left edge from the title to the last clause. --}}
        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
            <div class="es-fine-masthead">
                <div class="es-fade-up es-d-1 glass mb-7 inline-flex items-center gap-2 rounded-full px-4 py-2">
                    <svg aria-hidden="true" class="es-fine-accent h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span class="es-fine-tag">Legal</span>
                </div>

                <h1 class="es-fine-title es-balance es-fade-up es-d-2">Terms of <span class="es-fine-accent">Service</span></h1>

                <p class="es-fine-lede es-fade-up es-d-3 mt-3">Event Schedule LLC</p>

                <p class="es-fine-intro es-fade-up es-d-3 mt-5">
                    The whole agreement: the acceptance paragraph, then sixteen numbered clauses. Every
                    clause carries a section number you can cite and link to, and nothing here has been
                    summarised or shortened.
                </p>
            </div>

            <div id="acceptance" class="es-fine-recital es-fade-up es-d-4 mt-9 scroll-mt-24 p-6 sm:p-7" data-reveal>
                <p class="es-fine-tag mb-3">Acceptance</p>
                <p class="es-fine-p" style="margin-top: 0;">
                    By Creating an Account with Event Schedule you are agreeing to the following terms. These Terms of Service apply to all websites, subdomains and URL extensions, including but not limited to eventschedule.com, owned by Event Schedule LLC. By utilizing the eventschedule.com website you are agreeing to the following terms of service &amp; conditions of use and constitute a binding agreement. If you do not agree to the below terms &amp; conditions, do not use the Event Schedule platform.
                </p>
            </div>

            <dl class="es-fine-facts es-fade-up es-d-4 mt-9">
                <div class="es-fine-fact">
                    <dt>Party</dt>
                    <dd>Event Schedule LLC</dd>
                </div>
                <div class="es-fine-fact">
                    <dt>Governing law</dt>
                    <dd>United States of America, State of Florida <a href="#governing-law" class="es-fine-mono es-fine-link text-xs" aria-label="Clause 13, Governing Law">&sect;&nbsp;13</a></dd>
                </div>
                <div class="es-fine-fact">
                    <dt>Questions</dt>
                    <dd><a href="mailto:legal@eventschedule.com" class="es-fine-link">legal@eventschedule.com</a> <a href="#communication-resolution" class="es-fine-mono es-fine-link text-xs" aria-label="Clause 16, Communication and Resolution">&sect;&nbsp;16</a></dd>
                </div>
                <div class="es-fine-fact">
                    <dt>Contents</dt>
                    <dd><a href="#contents" class="es-fine-link">Sixteen clauses, in the order they appear</a></dd>
                </div>
            </dl>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The instrument: contents rail + numbered clauses          -->
    <!-- ============================================================ -->
    <section id="contents" class="es-fine-edge scroll-mt-24 py-14 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="es-fine-doc">

                <aside class="es-fine-rail" data-reveal>
                    <nav aria-labelledby="es-fine-contents-h">
                        <p id="es-fine-contents-h" class="es-fine-tag mb-3">Contents</p>
                        <ol class="es-fine-toc list-none">
                            @foreach ($clauses as $tocIndex => $tocClause)
                                <li>
                                    <a href="#{{ $tocClause['id'] }}" class="es-fine-toc-item">
                                        <span class="es-fine-toc-num" aria-hidden="true">&sect;&nbsp;{{ str_pad($tocIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span>{{ $tocClause['title'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </aside>

                <div>
                    @foreach ($clauses as $clauseIndex => $clause)
                        @php $clauseNum = str_pad($clauseIndex + 1, 2, '0', STR_PAD_LEFT); @endphp
                        <article id="{{ $clause['id'] }}" class="es-fine-clause">
                            <a href="#{{ $clause['id'] }}" class="es-fine-num" aria-label="Clause {{ $clauseIndex + 1 }}, {{ $clause['title'] }}">&sect;&nbsp;{{ $clauseNum }}</a>
                            <div>
                                <h2 class="es-fine-h">{{ $clause['title'] }}</h2>

                                @if ($clause['endorse'] ?? false)
                                    <div class="es-fine-endorse">
                                        @foreach ($clause['blocks'] as $block)
                                            <p class="es-fine-endorse-line">{!! $markTerms($block[1]) !!}</p>
                                        @endforeach
                                    </div>
                                @else
                                    @foreach ($clause['blocks'] as $block)
                                        @if ($block[0] === 'p')
                                            <p class="es-fine-p">{!! $markTerms($block[1]) !!}</p>
                                        @elseif ($block[0] === 'raw')
                                            <p class="es-fine-p">{!! $block[1] !!}</p>
                                        @else
                                            <p class="es-fine-p">{!! $markTerms($block[1]) !!}</p>
                                            <ol class="es-fine-limbs" role="list">
                                                @foreach ($block[2] as $limb)
                                                    <li>{!! $markTerms($limb) !!}</li>
                                                @endforeach
                                            </ol>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </article>
                    @endforeach

                    {{-- The execution block. A real instrument closes with
                         one, and this page used to simply stop after clause
                         16. Every value here is a fact about the DOCUMENT or
                         a pointer into it: no clause is paraphrased, nothing
                         is glossed, and the register is the same device the
                         cover sheet opens with, so the instrument is
                         book-ended rather than trailing off. --}}
                    <div class="es-fine-close" data-reveal>
                        <p class="es-fine-endmark">End of terms</p>
                        <dl class="es-fine-facts mt-7">
                            <div class="es-fine-fact">
                                <dt>Instrument</dt>
                                <dd>Sixteen clauses, <span class="es-fine-mono">&sect;&nbsp;01</span> to <span class="es-fine-mono">&sect;&nbsp;16</span>, under the <a href="#acceptance" class="es-fine-link">acceptance paragraph</a></dd>
                            </div>
                            <div class="es-fine-fact">
                                <dt>Counterparts</dt>
                                <dd><a href="#counterparts" class="es-fine-link">Three further documents</a> sit beside this one</dd>
                            </div>
                            <div class="es-fine-fact">
                                <dt>Hard copy</dt>
                                <dd>Print this page for a black-on-white copy of the whole instrument</dd>
                            </div>
                        </dl>
                        <p class="mt-7">
                            <a href="#es-fine-page" class="es-fine-link es-fine-mono text-xs">Back to the top of the document</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Counterparts: the documents that sit beside this one      -->
    <!-- ============================================================ -->
    <section id="counterparts" class="es-fine-edge scroll-mt-24 py-16 lg:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <p class="es-fine-tag mb-3" data-reveal>Counterparts</p>
            <h2 class="es-balance es-fine-ink mb-3 text-2xl font-black tracking-tight" data-reveal>The documents that sit beside this one</h2>
            <p class="es-fine-intro mb-9" data-reveal>
                Three more, each doing one job. The privacy policy covers your data, the accessibility
                statement covers the interface, and the selfhosting terms cover running your own instance.
            </p>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['/privacy', 'Privacy Policy', 'What we collect, which third parties can see it, and the steps that purge it permanently.'],
                    ['/accessibility', 'Accessibility Statement', 'The standards we hold the interface to, and how to tell us when something falls short.'],
                    ['/self-hosting-terms-of-service', 'Selfhosting Terms', 'The terms that apply when you run Event Schedule on your own infrastructure instead of ours.'],
                ] as [$docHref, $docName, $docBlurb])
                    <a href="{{ marketing_url($docHref) }}" class="es-fine-card flex flex-col p-6" data-reveal>
                        <h3 class="es-fine-card-title es-fine-ink text-base font-bold">{{ $docName }}</h3>
                        <p class="es-fine-muted mt-2 text-sm leading-relaxed">{{ $docBlurb }}</p>
                        <span class="es-fine-accent es-fine-mono mt-auto inline-flex items-center gap-1.5 pt-5 text-xs font-bold">
                            Read it
                            <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <p class="es-fine-muted mt-10 text-sm" data-reveal>
                Anything in here you want explained, or think is wrong, goes to
                <a href="mailto:legal@eventschedule.com" class="es-fine-link">legal@eventschedule.com</a>.
                A real person reads it.
            </p>
        </div>
    </section>

    </div>

    <x-marketing.related-pages />

    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
