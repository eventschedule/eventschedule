<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Online Classes | Virtual Teaching</x-slot>
    <x-slot name="description">Schedule and sell online classes with built-in registration, recurring sessions, and newsletters you send your students. Works with any platform. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Online Classes</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Online Classes",
        "description": "Set a course up once as a term: the night it meets, the weeks you skip, and the session it ends on. Sell the whole term from one link with zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Online Instructors"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Online Classes",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Online Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "Set a course up once as a term with a repeat pattern, skipped weeks and an end after a set number of sessions. Take free registrations with a per-date seat cap, or sell single sessions and multi-session class cards with zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Terms set up once as a recurring event, with skipped weeks and an end after a set number of sessions",
            "Free registration with a seat cap counted per session date",
            "Class cards good for a set number of visits, or a membership valid until it expires",
            "Passes scoped to one sub-schedule, so a beginner card does not open the advanced track",
            "One link for the whole schedule, embeddable on the site you already have",
            "Any video platform: one class link on the course, joined from your schedule",
            "Zero platform fees on payments through your own Stripe account",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Newsletters to the students who follow you, with open and click rates",
            "Built-in analytics on views, devices and traffic sources"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "online class scheduling, virtual class platform, sell online classes, online teaching, class registration software",
        "screenshot": "{{ asset('images/social/for-online-classes.jpg') }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a term of online classes online with Event Schedule",
        "description": "Set the term up once and take registrations for every session from one link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Write the term",
                "text": "Create the course as a recurring event, pick the night it meets, and end the recurrence after a set number of sessions or on a closing date."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Skip the weeks you are off",
                "text": "Add date exceptions for the holiday weeks, and paste your class link on the course so students join from the schedule."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the register",
                "text": "Set a seat cap counted per session date, then take free registrations or sell single sessions and multi-visit class cards."
            }
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
           For-online-classes "The Syllabus" styles.

           THE CONCEPT. A syllabus is the one sheet a teacher hands out
           in week one: what the course is, which night it meets, how
           many sessions it runs, which weeks are off, how many seats
           there are, and what a seat costs. That sheet IS the product
           argument: a course is not an event, it is a TERM, and in
           Event Schedule a term is one recurring event whose
           recurrence ends after a set number of sessions
           (Event::$recurring_end_type = 'after_events'), whose holiday
           weeks are date exceptions (recurring_exclude_dates), whose
           seats are counted per session date
           (Event::rsvpRemaining($date)), and whose multi-session card
           is a pass (Ticket::$pass_usage_type). Every mark on the
           sheet is a column.

           THE SHEET IS A FIXED PHYSICAL OBJECT. .es-syl-sheet is
           manila paper and renders IDENTICALLY with .dark on and off:
           no dark: utilities inside it, no shared class that flips
           (.grid-overlay / .animate-shimmer / .es-claim never appear
           inside a sheet). Verified with the verifier's --bands flag.

           COLOUR. The page keeps its existing hue family - amber - but
           pushed down into ochre pencil on manila (#7d4e05 light,
           #eab945 dark) so it reads as graphite-and-highlighter rather
           than as the bright gold or highlighter-yellow other rebuilt
           pages already own. The red is not a brand accent: it is the
           margin rule that is printed on ruled paper, used only as a
           1.5px stroke and for a "no class" mark.

           CONTRAST. The light ground is tinted (#f7f4ec), which
           invalidates text-gray-500. Muted ink is .es-syl-muted
           (#4c4a54, 7.91 on the page ground) and never a Tailwind gray
           utility. Measured pairs, worst case each:
             ink   #17161b on #f7f4ec 16.37 / #eeecf1 on #101013 16.19
             muted #4c4a54 on #efeadd  7.24 / #a3a0aa on #1e1e25  6.44
             ochre #7d4e05 on #efeadd  5.90 / #eab945 on #1e1e25  9.09
             sheet #221f18 on #e0d5b9 11.27, #514b3e on #e0d5b9 5.93
             red   #a51c1c on #efe7d3  6.12 / #f87171 on #0d0d11 7.01
             btn   #ffffff on #2b2833 14.44 / #171410 on #eab945 10.07

           BLADE. No @supports probes in this block: a "#" hex inside a
           parenthesized at-rule condition breaks Blade compilation of
           every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-syl-page { background-color: #f7f4ec; color: #17161b; }
        .dark .es-syl-page { background-color: #101013; color: #eeecf1; }
        .es-syl-sub { background-color: #efeadd; }
        .dark .es-syl-sub { background-color: #16161a; }
        .es-syl-ink { color: #17161b; }
        .dark .es-syl-ink { color: #eeecf1; }
        .es-syl-muted { color: #4c4a54; }
        .dark .es-syl-muted { color: #a3a0aa; }
        .es-syl-accent { color: #7d4e05; }
        .dark .es-syl-accent { color: #eab945; }
        /* Always-lit inks, for the two fixed-dark bands in both colour
           modes. These are page-local classes rather than Tailwind
           arbitrary values (text-[#eab945]) on purpose: an arbitrary hex
           that no already-built page uses is not in the compiled bundle,
           so it silently resolves to the inherited colour. */
        .es-syl-lit { color: #eab945; }
        .es-syl-bright { color: #eeecf1; }
        .es-syl-dim { color: #a3a0aa; }
        .es-syl-hair { border-color: rgba(23, 22, 27, 0.12); }
        .dark .es-syl-hair { border-color: rgba(238, 236, 241, 0.12); }

        /* --- Cards --------------------------------------------------- */
        .es-syl-card {
            border: 1px solid rgba(23, 22, 27, 0.12);
            border-radius: 1rem;
            background-color: #fffdf7;
        }
        .dark .es-syl-card { border-color: rgba(238, 236, 241, 0.12); background-color: #1b1b21; }
        .es-syl-band .es-syl-card { border-color: rgba(238, 236, 241, 0.14); background-color: #1e1e25; }

        /* --- Fixed-dark band. Same in both colour modes, so the shared
               classes that flip inside it are pinned here. ------------- */
        .es-syl-band {
            background-color: #0d0d11;
            background-image: radial-gradient(125% 100% at 50% 0%, #1a1a21 0%, #121218 55%, #08080b 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(238, 236, 241, 0.05);
        }
        .es-syl-band .grid-overlay {
            background-image:
                linear-gradient(rgba(238, 236, 241, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(238, 236, 241, 0.05) 1px, transparent 1px);
        }
        .es-syl-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-syl-band .es-claim:focus-within {
            border-color: rgba(234, 185, 69, 0.75);
            box-shadow: 0 0 0 4px rgba(234, 185, 69, 0.22);
        }

        /* --- THE SHEET. Manila paper, pinned across colour modes. ----- */
        .es-syl-sheet {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(34, 31, 24, 0.2);
            border-radius: 0.5rem;
            background-color: #efe7d3;
            color: #221f18;
            box-shadow: 0 26px 50px -26px rgba(34, 31, 24, 0.5);
        }
        /* Ruled body. The rule pitch is the line height of the week list,
           so the sheet reads as written-on rather than textured. */
        .es-syl-ruled {
            background-image: repeating-linear-gradient(180deg,
                transparent 0, transparent 25px,
                rgba(34, 31, 24, 0.1) 25px, rgba(34, 31, 24, 0.1) 26px);
        }
        .es-syl-sheet-head {
            background-color: #e0d5b9;
            border-bottom: 1px solid rgba(34, 31, 24, 0.2);
        }
        .es-syl-sheet-note {
            background-color: #f8f2e2;
            border: 1px solid rgba(125, 78, 5, 0.4);
            border-radius: 0.4rem;
        }
        .es-syl-sheet-ink { color: #221f18; }
        .es-syl-sheet-muted { color: #514b3e; }
        .es-syl-sheet-accent { color: #7d4e05; }
        .es-syl-sheet-red { color: #a51c1c; }
        .es-syl-sheet-hair { border-color: rgba(34, 31, 24, 0.18); }
        /* The printed margin rule: the page's recurring stroke. */
        .es-syl-margin {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1.5px;
            background-color: rgba(165, 28, 28, 0.5);
        }

        /* --- THE TERM SPINE. One tick per calendar week of the term:
               filled = a session, hollow dashed = a week off. --------- */
        .es-syl-spine { display: flex; gap: 0.2rem; align-items: stretch; }
        .es-syl-tick {
            flex: 1 1 0;
            min-width: 0;
            height: 1.7rem;
            border-radius: 0.15rem;
            background-color: #7d4e05;
        }
        .dark .es-syl-tick { background-color: #eab945; }
        /* A week off is deliberately hollow, not merely dimmer: it is an
           excluded date, not a quieter session. */
        .es-syl-tick-off {
            background-color: rgba(23, 22, 27, 0.06);
            border: 1.5px dashed rgba(23, 22, 27, 0.55);
        }
        .dark .es-syl-tick-off { background-color: rgba(238, 236, 241, 0.05); border-color: rgba(238, 236, 241, 0.5); }
        /* Inside a sheet the spine is printed ink, identical in both modes. */
        .es-syl-sheet .es-syl-tick { background-color: #7d4e05; }
        .es-syl-sheet .es-syl-tick-off { background-color: rgba(34, 31, 24, 0.05); border-color: rgba(34, 31, 24, 0.6); }
        /* Inside a fixed-dark band it is always the lit ochre. */
        .es-syl-band .es-syl-tick { background-color: #eab945; }
        .es-syl-band .es-syl-tick-off { background-color: rgba(238, 236, 241, 0.05); border-color: rgba(238, 236, 241, 0.45); }
        .es-syl-spine-thin .es-syl-tick { height: 0.85rem; }
        /* Week ruler above a spine. */
        .es-syl-ruler {
            display: flex;
            gap: 0.2rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .es-syl-ruler span { flex: 1 1 0; min-width: 0; text-align: center; }

        /* --- THE CLASS CARD. The visit card is the term spine cut into
               visits: the same strip geometry as the spine above it, one
               cell per visit, inked when spent. Drawn as the sheet's own
               strip rather than as a punched circle on purpose - the
               spine is this page's mark, and reading the card with the
               same eye as the term makes the ten-against-twelve
               arithmetic visible instead of stated. Only ever used
               inside a sheet, so the colours are the sheet's and do not
               flip. --------------------------------------------------- */
        .es-syl-visit { display: flex; gap: 0.2rem; align-items: stretch; }
        .es-syl-visit-cell {
            display: flex;
            flex: 1 1 0;
            min-width: 0;
            align-items: center;
            justify-content: center;
            height: 1.6rem;
            border-radius: 0.15rem;
            border: 1.5px solid rgba(125, 78, 5, 0.5);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.55rem;
            font-weight: 700;
            color: #7d4e05;
        }
        .es-syl-visit-used {
            border-color: #7d4e05;
            background-color: #7d4e05;
            color: #efe7d3;
        }
        /* The membership card has no cells to count, so it gets a band
           of solid ink across the same footprint instead: unlimited
           until the expiry date. */
        .es-syl-unlimited {
            height: 1.6rem;
            border-radius: 0.3rem;
            background-image: repeating-linear-gradient(135deg,
                rgba(125, 78, 5, 0.9) 0, rgba(125, 78, 5, 0.9) 7px,
                rgba(125, 78, 5, 0.55) 7px, rgba(125, 78, 5, 0.55) 14px);
        }

        /* --- THE REGISTER. Seats left on one session date. ----------- */
        .es-syl-fill {
            position: relative;
            height: 0.45rem;
            border-radius: 9999px;
            overflow: hidden;
            background-color: rgba(23, 22, 27, 0.1);
        }
        .dark .es-syl-fill { background-color: rgba(238, 236, 241, 0.12); }
        .es-syl-fill-bar {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            border-radius: 9999px;
            background-color: #7d4e05;
            transform-origin: left center;
            transition: transform 0.9s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .dark .es-syl-fill-bar { background-color: #eab945; }
        .es-syl-fill-bar-full { background-color: #a51c1c; }
        .dark .es-syl-fill-bar-full { background-color: #f87171; }
        html.es-anim [data-reveal]:not(.is-revealed) .es-syl-fill-bar { transform: scaleX(0); }

        /* --- Clause mark: the syllabus paragraph numeral. ------------- */
        .es-syl-clause {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(23, 22, 27, 0.18);
            background-color: #fffdf7;
            color: #17161b;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            font-size: 0.8rem;
            letter-spacing: 0.06em;
        }
        .dark .es-syl-clause { border-color: rgba(238, 236, 241, 0.2); background-color: #1b1b21; color: #eeecf1; }
        .es-syl-band .es-syl-clause { border-color: rgba(238, 236, 241, 0.2); background-color: #1e1e25; color: #eeecf1; }
        .es-syl-clause::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background-color: #7d4e05;
        }
        .dark .es-syl-clause::before { background-color: #eab945; }
        .es-syl-band .es-syl-clause::before { background-color: #eab945; }

        /* --- Eyebrow --------------------------------------------------- */
        .es-syl-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4c4a54;
        }
        .dark .es-syl-tag { color: #a3a0aa; }
        .es-syl-band .es-syl-tag { color: #eab945; }

        /* --- Plan tags ------------------------------------------------- */
        .es-syl-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(125, 78, 5, 0.45);
            color: #7d4e05;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .dark .es-syl-plan { border-color: rgba(234, 185, 69, 0.45); color: #eab945; }
        .es-syl-plan-pro { border-color: rgba(23, 22, 27, 0.35); color: #17161b; }
        .dark .es-syl-plan-pro { border-color: rgba(238, 236, 241, 0.38); color: #eeecf1; }

        /* --- Chips ----------------------------------------------------- */
        .es-syl-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(23, 22, 27, 0.16);
            background-color: rgba(255, 253, 247, 0.75);
            color: #4c4a54;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-syl-chip {
            border-color: rgba(238, 236, 241, 0.16);
            background-color: #1b1b21;
            color: #a3a0aa;
        }

        /* --- Links and buttons ----------------------------------------- */
        .es-syl-link { color: #7d4e05; }
        .es-syl-link:hover { color: #17161b; }
        .dark .es-syl-link { color: #eab945; }
        .dark .es-syl-link:hover { color: #eeecf1; }

        .es-syl-btn {
            background-color: #2b2833;
            color: #ffffff;
            box-shadow: 0 18px 36px -14px rgba(43, 40, 51, 0.55);
        }
        .es-syl-btn:hover { background-color: #1d1b24; box-shadow: 0 22px 44px -14px rgba(43, 40, 51, 0.65); }
        .dark .es-syl-btn { background-color: #eab945; color: #171410; box-shadow: 0 18px 36px -14px rgba(234, 185, 69, 0.35); }
        .dark .es-syl-btn:hover { background-color: #f2c862; }

        /* --- FAQ / related hover --------------------------------------- */
        .es-syl-hover:hover { border-color: rgba(125, 78, 5, 0.5); }
        .dark .es-syl-hover:hover { border-color: rgba(234, 185, 69, 0.45); }
        .es-syl-hover:hover .es-syl-hover-title,
        .es-syl-hover:hover .es-syl-hover-arrow { color: #7d4e05; }
        .dark .es-syl-hover:hover .es-syl-hover-title,
        .dark .es-syl-hover:hover .es-syl-hover-arrow { color: #eab945; }

        /* --- Shared-system recolours (brand blue by default) ----------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(125, 78, 5, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(234, 185, 69, 0.12), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(125, 78, 5, 0.65); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(234, 185, 69, 0.65); }
        .es-dot.is-active .es-dot-pip { background-color: #7d4e05; }
        .dark .es-dot.is-active .es-dot-pip { background-color: #eab945; }
        .es-syl-tip {
            border-color: rgba(23, 22, 27, 0.14);
            background-color: #fffdf7;
            color: #17161b;
        }
        .dark .es-syl-tip {
            border-color: rgba(238, 236, 241, 0.14);
            background-color: #1b1b21;
            color: #eeecf1;
        }

        /* --- Focus rings. No border-radius here: setting it would
               change the element's own shape on focus. ---------------- */
        #es-syl-page a:focus-visible,
        #es-syl-page summary:focus-visible,
        #es-syl-page button:focus-visible {
            outline: 2px solid #7d4e05;
            outline-offset: 3px;
        }
        .dark #es-syl-page a:focus-visible,
        .dark #es-syl-page summary:focus-visible,
        .dark #es-syl-page button:focus-visible {
            outline-color: #eab945;
        }
        .es-syl-band a:focus-visible,
        .es-syl-band summary:focus-visible,
        .es-syl-band button:focus-visible {
            outline-color: #eab945 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-syl-fill-bar { transition: none; }
        }
    </style>

    @php
        // One term. Thirteen calendar weeks, twelve sessions: the week of
        // Nov 24 is a date exception, which is why the spine has a hollow
        // tick and the register has a "no class" row.
        // 'on' = a session, 'off' = a skipped week.
        $termWeeks = [];
        foreach (range(0, 12) as $w) {
            $termWeeks[] = $w === 10 ? 'off' : 'on';
        }

        // The register: what rsvpRemaining() returns for each session date
        // when the seat cap is 14. 'skip' is the excluded date.
        $register = [
            ['01', 'Sep 15', 14, 0],
            ['02', 'Sep 22', 14, 2],
            ['03', 'Sep 29', 14, 5],
            ['04', 'Oct 6', 14, 9],
            ['05', 'Oct 13', 14, 11],
            ['06', 'Oct 20', 14, 14],
            ['07', 'Oct 27', 14, 12],
            ['08', 'Nov 3', 14, 8],
            ['09', 'Nov 10', 14, 6],
            ['10', 'Nov 17', 14, 4],
            ['--', 'Nov 24', 0, 0],
            ['11', 'Dec 1', 14, 3],
            ['12', 'Dec 8', 14, 1],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for teaching online classes?',
                'a' => 'Yes. Setting a course up as a term, skipping holiday weeks, ending the recurrence after a set number of sessions, taking free registrations with a seat cap per session date, publishing one link, embedding your schedule, syncing two ways with Google, Outlook or CalDAV, sending newsletters to the students who follow you and reading your analytics are all on the free plan. Selling seats is free too, up to 25 paid tickets a month per schedule. Lifting that cap, plus class cards and custom checkout questions, is the Pro plan at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on payments at any plan level.',
            ],
            [
                'q' => 'How do I set up a twelve-week term?',
                'a' => 'Create the course once as a recurring event, pick the night it meets, and give the recurrence an end: after a set number of sessions, or on a closing date. A term that ends after twelve sessions stops on its own instead of repeating until somebody remembers to switch it off. Add date exceptions for the weeks you are off. Repeats can be daily, weekly, every few weeks, or monthly by date or by weekday.',
            ],
            [
                'q' => 'What video platforms can I use to teach?',
                'a' => 'Any platform that gives you a link. Zoom, Google Meet, Microsoft Teams, YouTube Live, or your own streaming setup. Being straight with you: this is one link field on the course, not an integration, and because the term is one recurring event that link is the same for all twelve sessions, the way a recurring meeting room already is. Event Schedule does not create the meeting, count who is in the room, or record anything. It publishes the sessions, takes the registrations, and puts your link in front of the people who signed up. If each week genuinely needs a different room, those weeks are separate events.',
            ],
            [
                'q' => 'Can I cap how many students join each session?',
                'a' => 'Yes, on the free plan. Set a seat cap on the course and it is counted per session date, so week three filling up does not close week four. When a session is full the register shows no seats left for that date and the sign-up button on that date becomes a waitlist instead. When somebody cancels, the first person waiting for that date is emailed and has twenty-four hours to take the seat before it passes to the next in line. The registration waitlist is free; the same waitlist on a sold-out paid date is a Pro feature.',
            ],
            [
                'q' => 'Can I sell a card that covers the whole term?',
                'a' => 'Yes, on the Pro plan. A class card is a pass, and you choose how it counts: a fixed number of visits, like a ten-visit card, or a membership with unlimited visits until it expires. Set how many days it is valid for from purchase, how many people it admits at each session, whether holders reserve a date in advance or just turn up, and a cancellation deadline with a policy for late cancels. A pass can also be scoped to one sub-schedule, so a beginner card does not open the advanced track.',
            ],
            [
                'q' => 'Can I charge for individual sessions?',
                'a' => 'Yes, and selling is on the free plan: 25 paid tickets a month per schedule, unlimited on Pro. Create as many named ticket types as the course needs, each with its own price and quantity: a drop-in seat, a concession rate, a free trial session. Payments run through your own Stripe account, so you keep everything except Stripe\'s standard processing fee. Event Schedule takes nothing.',
            ],
            [
                'q' => 'Do my students get an email when I add a class?',
                'a' => 'Not automatically, and no page here will tell you otherwise. Students follow your schedule so that you can email them, and you write and send that newsletter yourself: ten recipients a month on the free plan, a hundred on Pro, a thousand on Enterprise. There is one email that is not a newsletter: if your schedule sends through its own email settings, then when you change the course time or cancel it you are asked at the point of saving whether to tell the people already registered, and because a term is one recurring event that goes to everybody holding a seat on an upcoming session, not to one week only.',
            ],
        ];

        $dotSections = [
            ['top', 'The syllabus'],
            ['term', 'Not one class'],
            ['setup', 'Writing the term'],
            ['register', 'The register'],
            ['card', 'The class card'],
            ['link', 'The room'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Week one'],
        ];
    @endphp

    <div id="es-syl-page" class="es-syl-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the syllabus sheet                                  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(125, 78, 5, 0.2), rgba(125, 78, 5, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(234, 185, 69, 0.16), rgba(234, 185, 69, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-syl-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="es-syl-muted text-sm font-medium tracking-wide">For online instructors, tutors and coaches</span>
                    </div>

                    <h1 class="es-balance es-syl-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A course is not one class.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">It is <span class="es-syl-accent">twelve</span> of them.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-syl-muted mb-6 max-w-xl text-lg sm:text-xl">
                        Write the term once - the night it meets, the weeks you are off, the session it finishes on - and take every registration for it from a single link, with zero platform fees.
                    </p>
                    <p class="es-fade-up es-d-2 es-syl-muted mb-10 max-w-xl text-base">
                        Online class scheduling with free registration and a seat cap counted per session date, multi-session class cards, recurring terms that end themselves, and payments through your own Stripe account.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row sm:flex-wrap">
                        <a href="#setup" class="glass group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            How a term works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-syl-btn group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your class schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The syllabus. A fixed sheet of manila paper: identical
                     with .dark on and off. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-syl-sheet">
                        <div class="es-syl-sheet-head flex items-center justify-between gap-3 px-5 py-3">
                            <span class="es-syl-sheet-ink font-mono text-[0.7rem] font-extrabold uppercase tracking-[0.2em]">Syllabus</span>
                            <span class="es-syl-sheet-muted font-mono text-[0.7rem] font-bold">Term 1</span>
                        </div>

                        <div class="es-syl-ruled relative px-5 py-6 sm:px-7">
                            <span class="es-syl-margin left-3 sm:left-4" aria-hidden="true"></span>

                            <h2 class="es-syl-sheet-ink text-xl font-black leading-tight sm:text-2xl">Conversational Spanish, Level 1</h2>
                            <p class="es-syl-sheet-muted mt-1 font-mono text-[0.7rem] font-bold uppercase tracking-wider">
                                Tuesdays 6:00 PM &middot; online &middot; 12 sessions &middot; 14 seats a session
                            </p>

                            <div class="mt-6" aria-hidden="true">
                                <div class="es-syl-ruler es-syl-sheet-muted mb-1">
                                    @foreach ($termWeeks as $wi => $wState)
                                        <span>{{ $wi === 0 ? 'W1' : ($wi === 12 ? 'W13' : '') }}</span>
                                    @endforeach
                                </div>
                                <div class="es-syl-spine">
                                    @foreach ($termWeeks as $wState)
                                        <div class="es-syl-tick @if ($wState === 'off') es-syl-tick-off @endif"></div>
                                    @endforeach
                                </div>
                                <div class="es-syl-sheet-muted mt-1.5 flex justify-between font-mono text-[0.6rem] font-bold">
                                    <span>Sep 15</span>
                                    <span>Dec 8</span>
                                </div>
                            </div>

                            <div class="es-syl-sheet-note mt-6 px-4 py-3">
                                <p class="es-syl-sheet-accent font-mono text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Recurrence</p>
                                <p class="es-syl-sheet-ink mt-1 text-sm font-semibold">Ends after 12 sessions.</p>
                                <p class="es-syl-sheet-muted mt-1 text-xs">Thirteen Tuesdays, twelve sessions. The hollow week is <span class="es-syl-sheet-red font-semibold">Nov 24</span>, taken out as a date exception.</p>
                            </div>

                            <p class="es-syl-sheet-muted es-syl-sheet-hair mt-5 border-t pt-4 text-xs">
                                One recurring event. Change the start time once and all twelve sessions follow.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subject marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Languages', 'Cooking', 'Yoga', 'Coding', 'Drawing', 'Music', 'Tutoring', 'Masterclasses', 'Kids Classes', 'Coaching'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-syl-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. A course is not one class (fixed-dark band)                -->
    <!-- ============================================================ -->
    <section id="term" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-syl-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 02</span></div>
                    <p class="es-syl-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The unit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Most calendars think a class is <span class="es-syl-lit">one night.</span>
                    </h2>
                    <p class="mt-5 text-lg es-syl-dim" data-reveal style="--reveal-delay: 0.15s;">
                        The thing you actually teach is a term. Twelve sessions, one topic, the same students each week, and a last night.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-syl-card p-6" data-reveal="panel">
                        <p class="es-syl-tag mb-3">The term</p>
                        <h3 class="mb-2 text-lg font-bold es-syl-bright">
                            <span data-count-to="12">12</span> sessions
                        </h3>
                        <p class="text-sm es-syl-dim">Same course, same students, thirteen weeks. Entering it as twelve separate events is twelve chances to mistype a time.</p>
                    </div>
                    <div class="es-syl-card p-6" data-reveal="panel">
                        <p class="es-syl-tag mb-3">The setup</p>
                        <h3 class="mb-2 text-lg font-bold es-syl-bright">
                            <span data-count-to="1">1</span> event
                        </h3>
                        <p class="text-sm es-syl-dim">A repeat pattern, exceptions for the weeks you are off, and an end. Move the class an hour later once and every session moves.</p>
                    </div>
                    <div class="es-syl-card p-6" data-reveal="panel">
                        <p class="es-syl-tag mb-3">The close</p>
                        <h3 class="mb-2 text-lg font-bold es-syl-bright">It stops itself</h3>
                        <p class="text-sm es-syl-dim">A term ends on a closing date or after a set number of sessions, so it is not still taking sign-ups for week nineteen in March.</p>
                    </div>
                </div>

                <div class="mx-auto mt-12 max-w-3xl" data-reveal>
                    <p class="es-syl-tag mb-3 text-center">The term, drawn</p>
                    <div class="es-syl-spine es-syl-spine-thin" aria-hidden="true">
                        @foreach ($termWeeks as $wState)
                            <div class="es-syl-tick @if ($wState === 'off') es-syl-tick-off @endif"></div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-center text-sm es-syl-dim">
                        Thirteen weeks, twelve filled. The hollow one is a date exception, not a cancelled event.
                        <a href="#setup" class="es-syl-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                            Write one
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Writing the term                                          -->
    <!-- ============================================================ -->
    <section id="setup" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 03</span></div>
                <p class="es-syl-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Writing the term</p>
                <h2 class="es-balance es-syl-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Three lines and you have a <span class="es-syl-accent">course.</span>
                </h2>
                <p class="es-syl-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    All three are on the free plan. None of them are a spreadsheet.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-syl-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-lg font-bold">The night it meets</h3>
                        <span class="es-syl-plan">Free</span>
                    </div>
                    <p class="es-syl-muted text-sm">Pick the days of the week and the start time. Repeats can be daily, weekly, every few weeks, or monthly by date or by weekday, so a fortnightly workshop is one setting rather than a second calendar.</p>
                </div>
                <div class="es-syl-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-lg font-bold">The weeks you are off</h3>
                        <span class="es-syl-plan">Free</span>
                    </div>
                    <p class="es-syl-muted text-sm">Date exceptions take single dates out, so a holiday week or a week you are travelling disappears from the schedule without rebuilding the term. You can add one-off dates back in the same way.</p>
                </div>
                <div class="es-syl-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-lg font-bold">The last session</h3>
                        <span class="es-syl-plan">Free</span>
                    </div>
                    <p class="es-syl-muted text-sm">End the recurrence after a set number of sessions, or on a closing date, or never. This is the setting that makes a term a term instead of a weekly slot that runs forever.</p>
                </div>
            </div>

            <!-- Honesty beat: one recurring event has one name. -->
            <div class="es-syl-card mx-auto mt-8 max-w-3xl p-7" data-reveal="panel">
                <p class="es-syl-tag mb-3">Worth knowing</p>
                <h3 class="es-syl-ink mb-2 text-lg font-bold">A term has one name, not twelve titles.</h3>
                <p class="es-syl-muted text-sm leading-relaxed">
                    A recurring event carries one name and one description, so week four is not separately titled "the past tense". If the weeks really are different topics with different prices, make them separate events - cloning one is a click - and keep them together in a sub-schedule. If they are one course, the term is the right shape, and the week-by-week breakdown belongs in the description, because the agenda you set runs the same way in every session.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The register: seats counted per session date               -->
    <!-- ============================================================ -->
    <section id="register" class="es-syl-sub scroll-mt-24 border-y py-20 es-syl-hair lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 04</span></div>
                <p class="es-syl-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The register</p>
                <h2 class="es-balance es-syl-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Fourteen seats, <span class="es-syl-accent">counted per date.</span>
                </h2>
                <p class="es-syl-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The cap is set once on the course and counted separately for every session, so week three filling up does not close week four. Free registration, free plan.
                </p>
            </div>

            <div class="es-syl-card p-5 sm:p-8" data-reveal="panel">
                <table class="w-full border-collapse text-left">
                    <caption class="sr-only">Term register: seats taken and seats left for each session date, with a seat cap of fourteen</caption>
                    <thead>
                        <tr class="es-syl-tag">
                            <th scope="col" class="pb-3 font-bold">Session</th>
                            <th scope="col" class="pb-3 font-bold">Date</th>
                            <th scope="col" class="hidden pb-3 font-bold sm:table-cell">Taken</th>
                            <th scope="col" class="pb-3 text-right font-bold">Seats left</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($register as [$rNum, $rDate, $rCap, $rLeft])
                            @php
                                $rSkip = $rCap === 0;
                                $rTaken = $rSkip ? 0 : $rCap - $rLeft;
                                $rPct = $rSkip ? 0 : (int) round(($rTaken / $rCap) * 100);
                                $rFull = ! $rSkip && $rLeft === 0;
                            @endphp
                            <tr class="border-t es-syl-hair">
                                <th scope="row" class="es-syl-ink py-2.5 pe-3 align-middle font-mono text-xs font-bold">
                                    @if ($rSkip)
                                        <span class="es-syl-muted">off</span>
                                    @else
                                        {{ $rNum }}
                                    @endif
                                </th>
                                <td class="es-syl-muted py-2.5 pe-3 align-middle font-mono text-xs">{{ $rDate }}</td>
                                <td class="hidden w-1/2 py-2.5 pe-3 align-middle sm:table-cell">
                                    @if ($rSkip)
                                        <span class="es-syl-muted text-xs font-semibold">No class this week</span>
                                    @else
                                        <div class="es-syl-fill" role="img" aria-label="{{ $rTaken }} of {{ $rCap }} seats taken">
                                            <div class="es-syl-fill-bar @if ($rFull) es-syl-fill-bar-full @endif" style="width: {{ $rPct }}%;"></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-2.5 text-right align-middle font-mono text-xs">
                                    @if ($rSkip)
                                        <span class="es-syl-muted">date exception</span>
                                    @elseif ($rFull)
                                        <span class="es-syl-ink font-bold">full</span>
                                    @else
                                        <span class="es-syl-ink font-bold">{{ $rLeft }}</span><span class="es-syl-muted"> / {{ $rCap }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-syl-muted mt-5 text-xs leading-relaxed">
                    Week one is full and week six is empty, on the same course, at the same time. That is the point: the count lives on the date, not on the course. Students see the seats left for the date they are looking at, and a full date stops taking sign-ups without touching the others.
                </p>
            </div>

            <div class="mx-auto mt-8 grid max-w-4xl gap-6 md:grid-cols-2" data-reveal-group="90">
                <div class="es-syl-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-base font-bold">Free registration</h3>
                        <span class="es-syl-plan">Free</span>
                    </div>
                    <p class="es-syl-muted text-sm">A name and an email gets somebody a seat on a specific date, with an optional cap. No card, no checkout, and no plan to upgrade to first.</p>
                </div>
                <div class="es-syl-card p-6" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-base font-bold">A waitlist when a date fills up</h3>
                        <span class="es-syl-plan">Free</span>
                    </div>
                    <p class="es-syl-muted text-sm">Once a registration date is full, the sign-up button on it becomes a waitlist. When somebody drops, the first person waiting for <em>that</em> date is emailed and has twenty-four hours to claim the seat before it moves to the next in line. Set the cap you can actually teach to and let the list do the rest. On a sold-out <em>paid</em> date the same waitlist is Pro.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The class card (passes)                                   -->
    <!-- ============================================================ -->
    <section id="card" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-[1fr_1.05fr]">
                <div>
                    <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 05</span></div>
                    <p class="es-syl-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The class card</p>
                    <h2 class="es-balance es-syl-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nobody wants to buy <span class="es-syl-accent">twelve tickets.</span>
                    </h2>
                    <p class="es-syl-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        They want one card that covers the term. A pass counts the way your course actually sells: a fixed number of visits, or unlimited visits until it runs out of days.
                    </p>
                    <ul class="es-syl-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-syl-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A visit card is good for a set number of visits across the sessions it covers. Ten visits, used whenever they can make it.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-syl-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A membership is unlimited until it expires. Set how many days it is valid for from the day it is bought.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-syl-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Scope it to everything you teach, to one sub-schedule, or to the specific courses you name, so a beginner card does not open the advanced track.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-syl-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Holders can reserve a date in advance, or just turn up. Set a cancellation deadline and decide whether a late cancel gets the visit back.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-syl-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Set admissions per session above one and a card lets somebody bring a partner. Usage is tracked, so you can see which cards are being used.</span>
                        </li>
                    </ul>
                    <p class="es-syl-muted mt-6 text-sm">
                        Class cards are Pro, at {{ plan_price($proMonthly) }} a month. Publishing the term, taking free registrations and selling your first 25 paid tickets a month are not.
                        <a href="{{ marketing_url('/features/ticketing') }}" class="es-syl-link font-semibold underline hover:no-underline">See what ticketing includes</a>.
                    </p>
                </div>

                <!-- Two cards on the same sheet of manila. Fixed object. -->
                <div class="grid gap-5 sm:grid-cols-2" data-reveal-group="100">
                    <div class="es-syl-sheet" data-reveal="panel">
                        <div class="es-syl-sheet-head flex items-center justify-between gap-2 px-4 py-2.5">
                            <span class="es-syl-sheet-ink font-mono text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Visit card</span>
                            <span class="es-syl-sheet-muted font-mono text-[0.6rem] font-bold">$120</span>
                        </div>
                        <div class="es-syl-ruled relative px-4 py-5">
                            <span class="es-syl-margin left-2.5" aria-hidden="true"></span>
                            <p class="es-syl-sheet-ink text-sm font-bold">10 visits</p>
                            <p class="es-syl-sheet-muted mt-0.5 text-xs">Any Tuesday in the term.</p>
                            <div class="es-syl-visit mt-4" aria-hidden="true">
                                @foreach (range(1, 10) as $visit)
                                    <span class="es-syl-visit-cell @if ($visit <= 4) es-syl-visit-used @endif">{{ $visit }}</span>
                                @endforeach
                            </div>
                            <p class="es-syl-sheet-accent mt-3 font-mono text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">4 used &middot; 6 left</p>
                            <p class="es-syl-sheet-muted es-syl-sheet-hair mt-3 border-t pt-3 text-[0.65rem]">Ten cells against twelve sessions: two Tuesdays can slip. Valid 120 days from purchase. Admits 1.</p>
                        </div>
                    </div>

                    <div class="es-syl-sheet" data-reveal="panel">
                        <div class="es-syl-sheet-head flex items-center justify-between gap-2 px-4 py-2.5">
                            <span class="es-syl-sheet-ink font-mono text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Membership</span>
                            <span class="es-syl-sheet-muted font-mono text-[0.6rem] font-bold">$45</span>
                        </div>
                        <div class="es-syl-ruled relative px-4 py-5">
                            <span class="es-syl-margin left-2.5" aria-hidden="true"></span>
                            <p class="es-syl-sheet-ink text-sm font-bold">Unlimited</p>
                            <p class="es-syl-sheet-muted mt-0.5 text-xs">Every session, until it expires.</p>
                            <div class="es-syl-unlimited mt-4" aria-hidden="true"></div>
                            <p class="es-syl-sheet-accent mt-3 font-mono text-[0.6rem] font-extrabold uppercase tracking-[0.2em]">Same strip, no cells</p>
                            <p class="es-syl-sheet-muted es-syl-sheet-hair mt-3 border-t pt-3 text-[0.65rem]">Valid 90 days. Scoped to the Beginner sub-schedule.</p>
                        </div>
                    </div>

                    <div class="es-syl-card p-5 sm:col-span-2" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-syl-ink text-base font-bold">And single seats, alongside</h3>
                            <span class="es-syl-plan">Free</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ([['Drop-in seat', 'one session', '$18'], ['Concession', 'one session', '$12'], ['First session', 'try it once', 'Free']] as [$tName, $tScope, $tPrice])
                                <div class="flex items-baseline gap-3 text-sm">
                                    <span class="es-syl-ink min-w-0 flex-1 truncate font-semibold">{{ $tName }}</span>
                                    <span class="es-syl-muted hidden truncate text-xs sm:inline">{{ $tScope }}</span>
                                    <span class="es-syl-ink font-mono">{{ $tPrice }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-syl-muted mt-4 border-t pt-3 text-xs es-syl-hair">
                            Cards are sold next to single seats, not instead of them. Seats sell on the free plan up to 25 paid tickets a month, and Pro removes the cap. Payments run through your own Stripe account and Event Schedule takes <span class="es-syl-accent font-semibold">zero platform fees</span> at every plan level.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The room and the link                                     -->
    <!-- ============================================================ -->
    <section id="link" class="es-syl-sub scroll-mt-24 border-y py-20 es-syl-hair lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 06</span></div>
                <p class="es-syl-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The room</p>
                <h2 class="es-balance es-syl-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Your link. <span class="es-syl-accent">Any platform.</span>
                </h2>
                <p class="es-syl-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Paste one meeting link on the course and every session in the term joins through it, the way a recurring meeting room already works. Zoom, Google Meet, Microsoft Teams, YouTube Live, your own setup: it is a link field, so all of them work and none of them own you.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3" data-reveal-group="100">
                <div class="es-syl-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-lg font-bold">One link for the whole schedule</h3>
                        <span class="es-syl-plan">Free</span>
                    </div>
                    <p class="es-syl-muted text-sm">Your schedule lives at its own address. Put it in a bio, a signature, a course page, and it keeps being right when the term rolls over.</p>
                    <div class="mt-auto pt-5">
                        <div class="es-syl-card p-3">
                            <span class="es-syl-ink block truncate font-mono text-xs">your-classes.eventschedule.com</span>
                        </div>
                    </div>
                </div>
                <div class="es-syl-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-lg font-bold">Or on the site you already have</h3>
                        <span class="es-syl-plan">Free</span>
                    </div>
                    <p class="es-syl-muted text-sm">Embed the calendar in a page on your own site with an iframe. A list layout suits a term better than a month grid, and that is a setting.</p>
                    <p class="es-syl-muted mt-auto pt-5 text-xs">The registration form can be embedded too, free. The ticket purchase form is the Pro version of that widget.</p>
                </div>
                <div class="es-syl-card flex flex-col p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-syl-ink text-lg font-bold">What this is not</h3>
                    </div>
                    <p class="es-syl-muted text-sm">It is not a video platform and does not pretend to be. Event Schedule does not create the meeting, count who is in the room, take attendance from it, or hold recordings. It publishes the sessions, takes the registrations, and hands over your link. One link, for the whole term: if week four genuinely needs its own room, week four is a separate event.</p>
                    <p class="mt-auto pt-5 text-xs">
                        <a href="{{ marketing_url('/features/online-events') }}" class="es-syl-link font-semibold underline hover:no-underline">How online events work</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Everything else                                           -->
    <!-- ============================================================ -->
    @php
        $rest = [
            ['Newsletters to your students', 'Free', 'Students follow your schedule so you can write to them. Materials before, a recording link after, next term when it opens. Ten recipients a month free, a hundred on Pro, a thousand on Enterprise, with open and click rates.'],
            ['Two-way calendar sync', 'Free', 'Google, Outlook and CalDAV, both directions, so your teaching hours and the rest of your week sit in one calendar. A recurring term syncs across as its next session rather than as a repeating series; to see all twelve dates in a calendar app, subscribe to your schedule\'s calendar feed instead.'],
            ['Analytics that are already on', 'Free', 'Views, devices and where the traffic came from, per schedule. Enough to know whether the term filled from your newsletter or from somebody else linking you.'],
            ['A session agenda', 'Free', 'Break a class into named parts with their own times: warm-up, teaching, questions. It is the running order of a session, and on a term every session runs it.'],
            ['Sub-schedules for levels', 'Free', 'Beginner, intermediate and advanced as separate strands of the same link, each with its own colour. They organise and filter; they do not hide anything, and a pass can be scoped to one of them.'],
            ['Questions at checkout', 'Pro', 'Custom fields on the form collect what the course needs at the point of signing up: the level they think they are, dietary notes for a cooking class, a parent contact.'],
            ['Reusable event templates', 'Pro', 'Save a term as a template and start next term from it, or clone last term outright. A template keeps the pattern and the twelve-session end; the holiday dates it deliberately does not keep, because those belong to the calendar and not to the course. A clone keeps them.'],
            ['A follower QR code', 'Free', 'Every schedule has a QR code that points at it. Put it on the last slide of the deck and the people who liked the class can follow you before they close the tab.'],
        ];
    @endphp
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 07</span></div>
                <p class="es-syl-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-syl-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    The rest of the <span class="es-syl-accent">syllabus.</span>
                </h2>
                <p class="es-syl-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Marked Free or Pro, honestly. Six of these eight cost nothing.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                @foreach ($rest as [$rTitle, $rPlan, $rBody])
                    <div class="es-syl-card flex flex-col p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-syl-ink text-base font-bold">{{ $rTitle }}</h3>
                            <span class="es-syl-plan @if ($rPlan === 'Pro') es-syl-plan-pro @endif">{{ $rPlan }}</span>
                        </div>
                        <p class="es-syl-muted text-sm leading-relaxed">{{ $rBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="es-syl-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                One thing that is deliberately absent: nothing here emails your followers on its own when you add a session. Following you gives you permission to write to them, and you write it.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Perfect for (shared sub-audience cards)                   -->
    <!-- ============================================================ -->
    <section id="who" class="es-syl-sub scroll-mt-24 border-y py-20 es-syl-hair lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 08</span></div>
                <h2 class="es-balance es-syl-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Every kind of <span class="es-syl-accent">online class</span>
                </h2>
                <p class="es-syl-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    A term is a term whether it is verbs or knife skills. Also see Event Schedule for <a href="{{ marketing_url('/for-webinars') }}" class="es-syl-link underline hover:no-underline">Webinars</a> and <a href="{{ marketing_url('/for-virtual-conferences') }}" class="es-syl-link underline hover:no-underline">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Yoga & Fitness -->
                <x-sub-audience-card
                    name="Yoga & Fitness Instructors"
                    description="Daily or weekly sessions with a cap per date, and a ten-visit card for the regulars who cannot make every one."
                    icon-color="cyan"
                    blog-slug="for-yoga-fitness-instructors-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cooking Classes -->
                <x-sub-audience-card
                    name="Cooking Instructors"
                    description="Newsletter the ingredient list to the people who signed up for the course, teach live, then write once more with the recipe."
                    icon-color="teal"
                    blog-slug="for-cooking-instructors-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Art & Music Teachers -->
                <x-sub-audience-card
                    name="Art & Music Teachers"
                    description="A drawing term and a guitar term as separate sub-schedules on one link, each ending on its own last session."
                    icon-color="sky"
                    blog-slug="for-art-music-teachers-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Language Tutors -->
                <x-sub-audience-card
                    name="Language Tutors"
                    description="A twelve-week conversation class that stops after twelve, with holiday weeks taken out as date exceptions."
                    icon-color="blue"
                    blog-slug="for-language-tutors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Coding & Tech -->
                <x-sub-audience-card
                    name="Coding & Tech Educators"
                    description="Bootcamps, workshops and study groups, kept in beginner and advanced strands so a card for one does not open the other."
                    icon-color="amber"
                    blog-slug="for-coding-tech-educators"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Business & Professional -->
                <x-sub-audience-card
                    name="Business Coaches"
                    description="A cohort that meets fortnightly, sold as a term membership, with the intake question asked at checkout."
                    icon-color="emerald"
                    blog-slug="for-business-coaches-online"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Three steps                                               -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-syl-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal>
                    Three steps to week one
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Write the term', 'Create the course as a recurring event, pick the night it meets, and end the recurrence after a set number of sessions or on a closing date.'], ['02', 'Skip the weeks you are off', 'Add date exceptions for the holiday weeks, and paste your class link on the course so students join from the schedule.'], ['03', 'Open the register', 'Set a seat cap counted per date, then take free registrations, or sell single seats and class cards with nothing taken off the top.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-syl-card p-7" data-reveal="panel">
                        <div class="es-syl-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-syl-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-syl-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="es-syl-sub border-y py-20 es-syl-hair">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-syl-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set a term once, skip the holiday weeks, end after a set number of sessions" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="Publish sessions that meet on any platform, from one link field" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the students who follow you, with open and click rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-syl-link inline-flex items-center font-medium hover:underline">
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
    <!-- 11. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-syl-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-workshop-instructors', 'Workshop Instructors'], ['/for-webinars', 'Webinars'], ['/for-fitness-and-yoga', 'Fitness & Yoga'], ['/for-virtual-conferences', 'Virtual Conferences']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-syl-hover es-syl-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-syl-hover-title es-syl-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-syl-hover-arrow es-syl-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-syl-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-syl-sub scroll-mt-24 border-t py-20 es-syl-hair lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-syl-clause mb-6" data-reveal aria-hidden="true"><span>&sect; 09</span></div>
                <h2 class="es-balance es-syl-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-syl-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What instructors ask before they move a term across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-syl-hover es-syl-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-syl-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-syl-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-syl-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-syl-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-syl-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-syl-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-syl-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Write the term once. <span class="es-syl-lit">Teach all twelve.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg es-syl-dim">
                        Publishing your term, capping the seats, taking free registrations and selling your first 25 paid tickets a month are free forever. Unlimited sales and class cards are {{ plan_price($proMonthly) }} a month, and nothing is taken off what you charge.
                    </p>

                    <div class="mx-auto mb-10 max-w-md" aria-hidden="true">
                        <div class="es-syl-spine es-syl-spine-thin">
                            @foreach ($termWeeks as $wState)
                                <div class="es-syl-tick @if ($wState === 'off') es-syl-tick-off @endif"></div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-classes" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-syl-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm es-syl-dim">No credit card required</p>
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
                        <span class="es-syl-tip pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
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
