<x-marketing-layout>
    <x-slot name="title">Free Event Schedule for Virtual Conferences</x-slot>
    <x-slot name="description">Schedule and sell virtual conferences with multi-day agendas, tiered ticketing, and attendee email notifications. Works with any platform. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Virtual Conferences</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Virtual Conferences",
        "description": "Run a virtual conference day as one event with its running order inside it: every session is a part with its own start and end time, published on one link with one join link and zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Virtual Conference Organizers"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Virtual Conferences",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Virtual Conference Scheduling Software",
        "operatingSystem": "Web",
        "description": "Enter a conference day as one event and its sessions as parts of the agenda, each with a name, a description and its own start and end time. The running order publishes on one link with one join link, and there are zero platform fees on tickets.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "A running order inside each event: named parts with start and end times, reorderable",
            "Per-part descriptions in markdown, with schedule-level switches for whether the agenda editor asks for times and descriptions",
            "Agenda scanning that reads a programme from a photo or text and fills in the parts (Enterprise)",
            "One join link per event for Zoom, Microsoft Teams, Google Meet, YouTube Live or any platform",
            "Named ticket types with their own prices, quantities and sales windows, free up to 25 paid tickets a month (unlimited on Pro)",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Free registration with a capacity limit, counted per date",
            "Two-way Google, Outlook and CalDAV calendar sync, plus iCal download",
            "Photos, video and comments that attach to the session they are about, behind an approval queue",
            "Newsletters you write and send to the people who follow your schedule",
            "Embeddable calendar for the website you already have",
            "Open source, with a selfhosted option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "virtual conference platform, online conference scheduling, conference agenda, virtual summit, conference ticketing",
        "screenshot": "{{ asset('images/social/for-virtual-conferences.png') }}",
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
        "name": "How to put a virtual conference agenda online with Event Schedule",
        "description": "A conference day is one event. The running order goes inside it.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Create the day",
                "text": "One event per conference day: its date, its start time, how long it runs, and the link people join."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Type the running order",
                "text": "Add each session as a part of the agenda with a name, a start time and an end time, then move the parts into order."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the doors",
                "text": "Free registration with a capacity limit, or named ticket types, free for the first 25 paid tickets a month. Share one link for the whole programme."
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
           For-virtual-conferences "The Agenda" styles.

           THE CONCEPT. A conference day is not fifteen events. It is ONE
           event with a running order printed inside it, and that is
           literally how the product stores it: an Event, plus ordered
           EventPart rows each carrying name, description, start_time and
           end_time. So the page IS a running order. A monospace time
           gutter runs down the signature card and comes back as every
           section's mark, and reading the page top to bottom is reading
           the day: 09:00 the agenda, 12:40 in session, 17:30 close. The
           spine is timed AGAINST the sample day printed in the hero, so
           when a mark says a session is live at 12:40 the sample day's
           lightning talks really are running (12:30 to 13:00), and every
           mark after 13:00 really is after that stream stopped.

           THE SIGNATURE DEVICE IS PROPORTIONAL, NOT A CALENDAR GRID.
           A part's block height IS its duration in minutes, because on
           an agenda the thing that matters is how long a thing takes.
           A fifty minute keynote is twice a twenty five minute welcome
           on the page, and a break is the same shape with nothing in it.

           WHAT IT REFUSES TO DRAW. No tracks, no rooms, no per-session
           stream links, no parallel columns. There is one running order
           per event and one event_url per event, so drawing a track
           lane would teach a model the product does not have. Parallel
           sessions are separate events on the same date; the page says
           so out loud in the FAQ.

           COLOUR. The page keeps its inherited hue family, navy plus
           electric cyan, but spends it as TWO FLAT INKS instead of the
           three stop gradient it used to carry: navy #123a72 is the
           structure, cyan is the single live signal. No gradient text
           anywhere, which also removes the commonest AA failure in this
           codebase. Measured: ink #101a2c on the #f2f5f9 ground 15.92,
           muted #4a5568 6.88, navy accent 10.25, deep cyan #0c6478
           6.18; in dark, ink #e8edf5 on #080b12 16.74, muted #98a6bd
           7.99, cyan #67e8f9 13.58.

           NEVER text-gray-500 here: 4.83 on pure white but only ~4.4 on
           this tinted ground. Use .es-agenda-muted (6.88).

           BLADE RULE for this block: no @supports probes, because a "#"
           hex inside a parenthesized at-rule condition breaks Blade
           compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink ------------------------------------------ */
        .es-agenda-page { background-color: #f2f5f9; color: #101a2c; }
        .dark .es-agenda-page { background-color: #080b12; color: #e8edf5; }
        .es-agenda-ink { color: #101a2c; }
        .dark .es-agenda-ink { color: #e8edf5; }
        .es-agenda-muted { color: #4a5568; }
        .dark .es-agenda-muted { color: #98a6bd; }
        .es-agenda-accent { color: #123a72; }
        .dark .es-agenda-accent { color: #67e8f9; }
        /* Always-lit cyan, for the fixed-dark session window in both modes. */
        .es-agenda-lit { color: #67e8f9; }
        /* Hairline separators. These are page-local rather than an arbitrary
           Tailwind border-[rgba(...)] utility on purpose: a colour utility
           Tailwind has never seen is not in the built stylesheet, so it paints
           nothing and the element silently keeps its inherited colour. */
        .es-agenda-hair { border-color: rgba(16, 26, 44, 0.09); }
        .dark .es-agenda-hair { border-color: rgba(232, 237, 245, 0.1); }

        /* --- Cards --------------------------------------------------- */
        .es-agenda-card {
            border: 1px solid rgba(16, 26, 44, 0.11);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-agenda-card {
            border-color: rgba(232, 237, 245, 0.11);
            background: rgba(232, 237, 245, 0.045);
        }

        /* --- The section mark: a clock reading, a pip, a hairline ---- */
        .es-agenda-slot { display: inline-flex; align-items: center; gap: 0.6rem; }
        .es-agenda-clock {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #123a72;
        }
        .dark .es-agenda-clock { color: #67e8f9; }
        .es-agenda-pip {
            width: 0.4rem; height: 0.4rem;
            flex: none;
            border-radius: 9999px;
            background: #123a72;
        }
        .dark .es-agenda-pip { background: #67e8f9; }
        .es-agenda-rule { width: 2.5rem; height: 1px; flex: none; background: rgba(16, 26, 44, 0.2); }
        .dark .es-agenda-rule { background: rgba(232, 237, 245, 0.2); }
        .es-agenda-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #4a5568;
        }
        .dark .es-agenda-tag { color: #98a6bd; }

        /* --- The running order: block height IS duration ------------- */
        .es-agenda-run { position: relative; display: flex; flex-direction: column; gap: 0.3rem; }
        /* The spine, drawn between the time gutter and the blocks. */
        .es-agenda-run::before {
            content: "";
            position: absolute;
            top: 0.25rem; bottom: 0.25rem;
            inset-inline-start: 3.45rem;
            width: 1px;
            background: rgba(16, 26, 44, 0.14);
        }
        .dark .es-agenda-run::before { background: rgba(232, 237, 245, 0.16); }
        .es-agenda-row { display: grid; grid-template-columns: 3.1rem 1fr; gap: 0.8rem; align-items: stretch; }
        .es-agenda-time {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.63rem;
            font-weight: 800;
            line-height: 1.5;
            text-align: end;
            color: #4a5568;
        }
        .dark .es-agenda-time { color: #98a6bd; }
        .es-agenda-block {
            position: relative;
            overflow: hidden;
            border-radius: 0.35rem;
            padding: 0.3rem 0.6rem;
            background: rgba(18, 58, 114, 0.09);
            border-inline-start: 2px solid #123a72;
        }
        .dark .es-agenda-block {
            background: rgba(103, 232, 249, 0.09);
            border-inline-start-color: #67e8f9;
        }
        /* A break is the same shape with nothing in it. */
        .es-agenda-block-gap {
            background: transparent;
            border-inline-start-style: dashed;
            border-inline-start-color: rgba(16, 26, 44, 0.3);
        }
        .dark .es-agenda-block-gap { border-inline-start-color: rgba(232, 237, 245, 0.28); }
        .es-agenda-name { font-size: 0.73rem; font-weight: 700; line-height: 1.2; }
        .es-agenda-dur {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.6rem;
            font-variant-numeric: tabular-nums;
        }
        /* The day advancing: one hairline travelling down the running order. */
        @keyframes es-agenda-sweep {
            0% { top: 0; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }
        .es-agenda-now {
            position: absolute;
            inset-inline: 3.45rem 0;
            height: 1px;
            background: linear-gradient(90deg, #0c6478, rgba(12, 100, 120, 0));
            animation: es-agenda-sweep 15s linear infinite;
        }
        .dark .es-agenda-now { background: linear-gradient(90deg, #67e8f9, rgba(103, 232, 249, 0)); }
        .es-agenda-now::before {
            content: "";
            position: absolute;
            top: -2px;
            inset-inline-start: -2px;
            width: 5px; height: 5px;
            border-radius: 9999px;
            background: #0c6478;
        }
        .dark .es-agenda-now::before { background: #67e8f9; }

        /* --- The live pip on the session window --------------------- */
        @keyframes es-agenda-pulse {
            0%, 100% { opacity: 0.4; transform: scale(0.85); }
            50% { opacity: 1; transform: scale(1.15); }
        }
        .es-agenda-live {
            display: inline-block;
            width: 0.45rem; height: 0.45rem;
            border-radius: 9999px;
            background: #67e8f9;
            animation: es-agenda-pulse 2.4s ease-in-out infinite;
        }

        /* --- The programme strip: one day per row, parts as ticks ---- */
        .es-agenda-track {
            position: relative;
            height: 1.4rem;
            border-radius: 0.35rem;
            background: rgba(16, 26, 44, 0.06);
        }
        .dark .es-agenda-track { background: rgba(232, 237, 245, 0.07); }
        .es-agenda-tick {
            position: absolute;
            top: 0.25rem; bottom: 0.25rem;
            min-width: 2px;
            border-radius: 0.15rem;
            background: #123a72;
        }
        .dark .es-agenda-tick { background: #67e8f9; }
        /* A break is a part, so it gets a tick like everything else, but the
           same hollow shape it has in the hero: countable, and legible as
           the thing where nothing is scheduled. */
        .es-agenda-tick-gap {
            background: transparent;
            border: 1px dashed rgba(16, 26, 44, 0.34);
        }
        .dark .es-agenda-tick-gap { border-color: rgba(232, 237, 245, 0.32); }
        .es-agenda-ruler {
            display: flex;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.56rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #4a5568;
        }
        .dark .es-agenda-ruler { color: #98a6bd; }
        .es-agenda-ruler span { flex: 1 1 0; min-width: 0; }

        /* --- One field of the event you actually type --------------- */
        .es-agenda-field {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.5rem 0;
            border-top: 1px solid rgba(16, 26, 44, 0.08);
        }
        .dark .es-agenda-field { border-top-color: rgba(232, 237, 245, 0.09); }
        .es-agenda-key {
            flex: none;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #4a5568;
        }
        .dark .es-agenda-key { color: #98a6bd; }
        .es-agenda-val {
            min-width: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.74rem;
            font-weight: 600;
            text-align: end;
            color: #101a2c;
        }
        .dark .es-agenda-val { color: #e8edf5; }

        /* --- Plan pills -------------------------------------------- */
        .es-agenda-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            border: 1px solid rgba(18, 58, 114, 0.4);
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            color: #123a72;
        }
        .dark .es-agenda-plan { border-color: rgba(103, 232, 249, 0.42); color: #67e8f9; }
        .es-agenda-plan-ent { border-color: rgba(12, 100, 120, 0.5); color: #0c6478; }
        .dark .es-agenda-plan-ent { border-color: rgba(103, 232, 249, 0.3); color: #98a6bd; }

        /* --- Chips ------------------------------------------------- */
        .es-agenda-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(16, 26, 44, 0.14);
            background: rgba(255, 255, 255, 0.75);
            color: #4a5568;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-agenda-chip {
            border-color: rgba(232, 237, 245, 0.14);
            background: rgba(232, 237, 245, 0.05);
            color: #98a6bd;
        }

        /* --- Links and buttons ------------------------------------- */
        .es-agenda-link { color: #123a72; }
        .es-agenda-link:hover { color: #0c6478; }
        .dark .es-agenda-link { color: #67e8f9; }
        .dark .es-agenda-link:hover { color: #e8edf5; }

        .es-agenda-btn {
            background-color: #123a72;
            box-shadow: 0 18px 36px -14px rgba(18, 58, 114, 0.5);
        }
        .es-agenda-btn:hover { background-color: #0d2c58; box-shadow: 0 22px 44px -14px rgba(18, 58, 114, 0.6); }
        /* Dark mode flips the button to the cyan signal, so the label has to flip
           to ink. White on #67e8f9 measures 1.45; #08111d on it measures 10.49. */
        .dark .es-agenda-btn { background-color: #67e8f9; color: #08111d; }
        .dark .es-agenda-btn:hover { background-color: #9defff; }

        /* --- Dot-nav tooltip ---------------------------------------- */
        .es-agenda-tip { background-color: #ffffff; color: #374151; }
        .dark .es-agenda-tip { background-color: #101a2c; color: #d1d5db; }

        /* --- Hover recolours on related cards and FAQ rows --------- */
        .es-agenda-hover:hover { border-color: rgba(18, 58, 114, 0.45); }
        .dark .es-agenda-hover:hover { border-color: rgba(103, 232, 249, 0.45); }
        .es-agenda-hover:hover .es-agenda-hover-title,
        .es-agenda-hover:hover .es-agenda-hover-arrow { color: #123a72; }
        .dark .es-agenda-hover:hover .es-agenda-hover-title,
        .dark .es-agenda-hover:hover .es-agenda-hover-arrow { color: #67e8f9; }

        /* --- The session window ------------------------------------
           A fixed physical object: the lights are down whichever colour
           mode the reader is in, so this band must render IDENTICALLY
           with .dark on and off. Shared classes carry their own .dark
           rules in marketing.css, so each one is pinned below and the
           whole band is verified with the --bands flag. ------------- */
        .es-agenda-screen {
            background-color: #070a11;
            background-image: radial-gradient(120% 110% at 50% 0%, #101a2c 0%, #0a1120 55%, #05080e 100%);
            box-shadow: inset 0 0 100px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(232, 237, 245, 0.05);
        }
        .es-agenda-screen .es-agenda-card {
            border-color: rgba(232, 237, 245, 0.13);
            background: rgba(232, 237, 245, 0.05);
        }
        .es-agenda-screen .es-agenda-ink { color: #e8edf5; }
        .es-agenda-screen .es-agenda-muted { color: #98a6bd; }
        .es-agenda-screen .es-agenda-rule { background: rgba(232, 237, 245, 0.2); }
        .es-agenda-screen .es-agenda-pip { background: #67e8f9; }
        .es-agenda-screen .es-agenda-tag { color: #67e8f9; }
        .es-agenda-screen .es-agenda-plan { border-color: rgba(103, 232, 249, 0.42); color: #67e8f9; }
        .es-agenda-screen .es-aurora { opacity: 0.5; }
        .es-agenda-screen .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 237, 245, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 237, 245, 0.05) 1px, transparent 1px);
        }
        .es-agenda-screen .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        /* The finale CTA lives INSIDE the band, so it cannot be allowed to
           follow the page's colour mode: outside, .es-agenda-btn is navy with
           a white label in light and cyan with an ink label in dark. On a band
           that is always near-black the cyan treatment is the correct one, so
           it is pinned here for both modes. Same specificity as
           `.dark .es-agenda-btn`, so this must stay AFTER it in source order,
           and it also outranks the element's own `text-white` utility. */
        .es-agenda-screen .es-agenda-btn { background-color: #67e8f9; color: #08111d; }
        .es-agenda-screen .es-agenda-btn:hover { background-color: #9defff; }
        .es-agenda-screen .es-claim:focus-within {
            border-color: rgba(103, 232, 249, 0.75);
            box-shadow: 0 0 0 4px rgba(103, 232, 249, 0.22);
        }

        /* --- Shared-system recolours (brand blue by default) ------- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(18, 58, 114, 0.13), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(103, 232, 249, 0.11), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(18, 58, 114, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(103, 232, 249, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #123a72; }
        .dark .es-dot.is-active .es-dot-pip { background: #67e8f9; }

        /* --- Focus rings. No border-radius here: setting it changes
               the element's own shape on focus. -------------------- */
        #es-agenda-page a:focus-visible,
        #es-agenda-page summary:focus-visible,
        #es-agenda-page input:focus-visible,
        #es-agenda-page button:focus-visible {
            outline: 2px solid #123a72;
            outline-offset: 3px;
        }
        .dark #es-agenda-page a:focus-visible,
        .dark #es-agenda-page summary:focus-visible,
        .dark #es-agenda-page input:focus-visible,
        .dark #es-agenda-page button:focus-visible {
            outline-color: #67e8f9;
        }
        .es-agenda-screen a:focus-visible,
        .es-agenda-screen summary:focus-visible,
        .es-agenda-screen input:focus-visible,
        .es-agenda-screen button:focus-visible {
            outline-color: #67e8f9 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-agenda-now { animation: none !important; opacity: 0; }
            .es-agenda-live { animation: none !important; opacity: 1; transform: none; }
        }
    </style>

    @php
        // ------------------------------------------------------------------
        // Day one's running order. The product shape: ONE Event, plus ordered
        // EventPart rows carrying name / description / start_time / end_time.
        // A block's height IS its duration, so 0.085rem per minute turns the
        // list into a proportional timetable rather than a stack of equals.
        // ------------------------------------------------------------------
        $minuteRem = 0.085;
        $run = [
            ['09:00', 'Doors, welcome and housekeeping', 25, false],
            ['09:25', 'Opening keynote', 50, false],
            ['10:15', 'Break', 15, true],
            ['10:30', 'Panel: shipping in the open', 45, false],
            ['11:15', 'Workshop: instrumenting your stack', 60, false],
            ['12:15', 'Break', 15, true],
            ['12:30', 'Lightning talks', 30, false],
        ];
        // A break is a part too, which the hero says out loud, so the count is all rows.
        $runParts = count($run);

        // The three-day programme. Each day is its own event on its own date,
        // with its own running order and its own join link. Offsets are
        // minutes from 09:00 across a 09:00-18:00 window (540 minutes), and
        // the third value marks a break, which is a part like any other and
        // so gets a tick - hollow, exactly as in the hero. Ticks are drawn
        // 3px short of their true width so that abutting parts stay
        // countable instead of fusing into one bar.
        $window = 540;
        $programme = [
            ['Day 1', 'Tue 3 Mar', '09:00 to 13:00', 7, [[0, 25, false], [25, 50, false], [75, 15, true], [90, 45, false], [135, 60, false], [195, 15, true], [210, 30, false]]],
            ['Day 2', 'Wed 4 Mar', '10:00 to 16:00', 9, [[60, 30, false], [90, 45, false], [135, 15, true], [150, 60, false], [210, 45, false], [255, 45, false], [300, 15, true], [315, 60, false], [375, 45, false]]],
            ['Day 3', 'Thu 5 Mar', '09:30 to 13:00', 5, [[30, 45, false], [75, 15, true], [90, 60, false], [150, 45, false], [195, 45, false]]],
        ];

        // The event itself: the fields you fill in once for the whole day.
        $dayFields = [
            ['Event', 'Cloud Summit, day 1'],
            ['Date', 'Tue 3 Mar 2026'],
            ['Starts', '09:00'],
            ['Runs for', '4 hours'],
            ['Online', 'yes, one join link'],
            ['Sub-schedule', 'Main programme'],
        ];

        $faqs = [
            [
                'q' => 'Can I schedule a multi-day virtual conference?',
                'a' => 'Yes. Each conference day is one event on its own date, with its own join link, and the day\'s sessions go inside it as parts of the agenda. Every part has a name, an optional description and its own start and end time, so attendees read the whole running order on one link. Sub-schedules can file the days under a strand and give that strand a color.',
            ],
            [
                'q' => 'Does each session get its own streaming link?',
                'a' => 'One join link per event. A day is one event, so the day has one link and every part of its running order sits behind that link. If two sessions genuinely need two different links, make them two events on the same date. Any platform works, because all Event Schedule stores is the URL: Zoom, Microsoft Teams, Google Meet, YouTube Live, or anything else that gives you one.',
            ],
            [
                'q' => 'What about tracks and rooms?',
                'a' => 'There are none, and it is better to say so before you move a programme across. Event Schedule has one running order per event. Parallel sessions are separate events on the same date, and a sub-schedule can keep a strand together and color it, which is organizing and color-coding rather than access control. There is no room inventory and nothing is hidden by a sub-schedule.',
            ],
            [
                'q' => 'Is the agenda free?',
                'a' => 'Yes. Adding parts, naming them, giving them start and end times, writing a description for each one, moving them into order and publishing the running order are all free forever, along with the join link, calendar sync and the embeddable calendar. Agenda scanning, which reads a printed or emailed programme and fills the parts in for you, is on the Enterprise plan. Typing them costs nothing.',
            ],
            [
                'q' => 'Can I sell different ticket types for my conference?',
                'a' => 'Yes, and selling starts on the free plan. Create as many named ticket types as the conference needs, each with its own price, quantity and sales window, and sell up to 25 paid tickets a month. Pro at $'.$proMonthly.' a month takes that ceiling off and adds discount codes, add-ons and individual tickets, which give every attendee their own confirmation email and QR code; custom questions collect what you need at checkout. Event Schedule charges zero platform fees at every plan level: you connect your own Stripe account and Stripe\'s processing fee is the only cut. For a free conference, registration with a capacity limit is unlimited on the free plan.',
            ],
            [
                'q' => 'How do attendees hear about the next edition?',
                'a' => 'They follow your schedule, and you write them a newsletter when the next programme is set. Nothing goes out on its own: a newsletter is something you compose and send, with 10 emails a month on the free plan, 100 on Pro and 1,000 on Enterprise, counted one per recipient. Followers also show up with their name and email on your followers tab, so the audience is yours rather than a platform\'s.',
            ],
        ];

        $dotSections = [
            ['top', 'The running order'],
            ['unit', 'One event'],
            ['run', 'Setting it'],
            ['programme', 'The programme'],
            ['session', 'In session'],
            ['tickets', 'The takings'],
            ['after', 'Afterwards'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Close'],
        ];
    @endphp

    <div id="es-agenda-page" class="es-agenda-page">

    <!-- ============================================================ -->
    <!-- 09:00  Hero: one day, one event, seven parts                 -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(18, 58, 114, 0.28), rgba(18, 58, 114, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(34, 211, 238, 0.16), rgba(34, 211, 238, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="es-agenda-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.008v.008H3.75V6.75zM3.75 12h.008v.008H3.75V12zm0 5.25h.008v.008H3.75v-.008z" />
                        </svg>
                        <span class="es-agenda-muted text-sm font-medium tracking-wide">For virtual conference and online summit organizers</span>
                    </div>

                    <h1 class="es-balance es-agenda-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A conference day is one event.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="es-agenda-accent">agenda</span> goes inside it.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-agenda-muted mb-8 max-w-xl text-lg sm:text-xl">
                        Enter each session as a part of the day, with its own name and its own start and end time. Your virtual conference agenda publishes as one running order, on one link, with one link to join, and it is free on every plan.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#run" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See how a day is built
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-agenda-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            Create your conference schedule
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The running order. Block height is duration, so the page is
                     proportional to the day rather than a stack of equal rows. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-agenda-card p-6 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-agenda-ink text-lg font-bold">Cloud Summit, day 1</h2>
                            <span class="es-agenda-clock">TUE 3 MAR</span>
                        </div>
                        <p class="es-agenda-muted mb-5 text-sm">One event &middot; {{ $runParts }} parts &middot; one link to join</p>

                        <div class="es-agenda-run" aria-hidden="true">
                            <span class="es-agenda-now"></span>
                            @foreach ($run as [$partTime, $partName, $partMins, $partIsGap])
                                <div class="es-agenda-row">
                                    <div class="es-agenda-time">{{ $partTime }}</div>
                                    <div class="es-agenda-block @if ($partIsGap) es-agenda-block-gap @endif" style="min-height: {{ $partIsGap ? '1.35rem' : '2.1rem' }}; height: {{ round($partMins * $minuteRem, 3) }}rem;">
                                        <div class="es-agenda-name @if ($partIsGap) es-agenda-muted @else es-agenda-ink @endif">{{ $partName }}</div>
                                        @if (! $partIsGap)
                                            <div class="es-agenda-dur es-agenda-muted">{{ $partMins }} min</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            <div class="es-agenda-row">
                                <div class="es-agenda-time">13:00</div>
                                <div class="es-agenda-dur es-agenda-muted leading-none">end of day</div>
                            </div>
                        </div>

                        <p class="es-agenda-muted mt-5 es-agenda-hair border-t pt-4 text-xs">
                            Each block is as tall as it is long. A break is a part with a name and a time too, which is why the gaps are on the page.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Conference-type marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Tech Summits', 'Industry Conferences', 'Company Retreats', 'Professional Summits', 'Annual Meetings', 'Panel Events', 'Developer Cons', 'Hybrid Events'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-agenda-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 09:40  The unit: what you enter vs what they read            -->
    <!-- ============================================================ -->
    <section id="unit" class="scroll-mt-24 es-agenda-hair border-y py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">09:40</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">The unit</span>
                </div>
                <h2 class="es-balance es-agenda-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Seven parts. <span class="es-agenda-accent">One event.</span>
                </h2>
                <p class="es-agenda-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Most calendars would have you enter that morning as seven separate events. Here you fill the day in once and type the running order underneath it, so the date, the join link and the tickets are stated once and cannot disagree with each other.
                </p>
            </div>

            <div class="grid items-start gap-6 lg:grid-cols-2" data-reveal-group="110">
                <!-- What you enter -->
                <div class="es-agenda-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-agenda-ink text-lg font-bold">What you enter</h3>
                        <span class="es-agenda-plan">Free</span>
                    </div>
                    <p class="es-agenda-muted mb-4 text-sm">The event: one date, one start, one duration, one link.</p>
                    <div>
                        @foreach ($dayFields as [$fieldKey, $fieldVal])
                            <div class="es-agenda-field">
                                <span class="es-agenda-key">{{ $fieldKey }}</span>
                                <span class="es-agenda-val">{{ $fieldVal }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-agenda-muted mt-4 text-xs">Move the date here and the whole running order moves with it, because the parts live inside the event. The clock times on the parts are the ones you typed, so those you edit yourself.</p>
                </div>

                <!-- What they read -->
                <div class="es-agenda-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <h3 class="es-agenda-ink text-lg font-bold">What they read</h3>
                        <span class="es-agenda-plan">Free</span>
                    </div>
                    <p class="es-agenda-muted mb-4 text-sm">The parts: the running order, in order, with times.</p>
                    <ol class="space-y-1.5">
                        @foreach ($run as $runIndex => [$partTime, $partName, $partMins, $partIsGap])
                            <li class="flex items-baseline gap-3">
                                <span class="es-agenda-time w-11 flex-none">{{ $partTime }}</span>
                                <span class="es-agenda-name min-w-0 flex-1 @if ($partIsGap) es-agenda-muted @else es-agenda-ink @endif">{{ $partName }}</span>
                                <span class="es-agenda-dur es-agenda-muted flex-none">{{ $partMins }}m</span>
                            </li>
                        @endforeach
                    </ol>
                    <p class="es-agenda-muted mt-4 text-xs">The first few parts show on the schedule page with their times, and the rest sit behind a "more" line so a long day does not swamp the calendar.</p>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3" data-reveal-group="90">
                <div class="es-agenda-card p-6" data-reveal="panel">
                    <p class="es-agenda-tag mb-3">Events to enter</p>
                    <h3 class="es-agenda-ink mb-2 text-2xl font-black"><span data-count-to="1">1</span></h3>
                    <p class="es-agenda-muted text-sm">One date, one start time, one duration. Not seven chances to mistype a time zone.</p>
                </div>
                <div class="es-agenda-card p-6" data-reveal="panel">
                    <p class="es-agenda-tag mb-3">Parts inside it</p>
                    <h3 class="es-agenda-ink mb-2 text-2xl font-black"><span data-count-to="7">7</span></h3>
                    <p class="es-agenda-muted text-sm">As many as the day has. Each one carries a name, a description, a start and an end.</p>
                </div>
                <div class="es-agenda-card p-6" data-reveal="panel">
                    <p class="es-agenda-tag mb-3">Links to share</p>
                    <h3 class="es-agenda-ink mb-2 text-2xl font-black"><span data-count-to="1">1</span></h3>
                    <p class="es-agenda-muted text-sm">The programme, the sign-up and the way in are the same page.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10:30  Setting the running order                             -->
    <!-- ============================================================ -->
    <section id="run" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">10:30</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">Setting the running order</span>
                </div>
                <h2 class="es-balance es-agenda-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Name it, time it, <span class="es-agenda-accent">move it into place.</span>
                </h2>
                <p class="es-agenda-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Three things make a session, and all three are on the free plan.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-agenda-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-agenda-ink text-lg font-bold">A name, and not much else</h3>
                        <span class="es-agenda-plan">Free</span>
                    </div>
                    <p class="es-agenda-muted text-sm">The name is the only thing a part needs. Add a description in markdown when the session deserves an abstract, and leave it empty when the title says it all.</p>
                </div>
                <div class="es-agenda-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-agenda-ink text-lg font-bold">A start and an end</h3>
                        <span class="es-agenda-plan">Free</span>
                    </div>
                    <p class="es-agenda-muted text-sm">Each part takes its own start time and its own end time, and both are optional. Two switches in the agenda editor, remembered for the whole schedule, decide whether it asks you for times and descriptions at all, so a programme that is still only titles stays a list of titles.</p>
                </div>
                <div class="es-agenda-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-agenda-ink text-lg font-bold">An order you can change</h3>
                        <span class="es-agenda-plan">Free</span>
                    </div>
                    <p class="es-agenda-muted text-sm">Every part card has an up and a down button, so a session moves one row at a time. Switch times and descriptions off and the editor collapses to a drag-and-drop list of titles. When a speaker swaps slot the morning is a click away from correct, not a re-typed agenda.</p>
                </div>
            </div>

            <div class="es-agenda-card mx-auto mt-8 max-w-3xl p-7" data-reveal="panel">
                <div class="mb-3 flex flex-wrap items-center gap-2">
                    <h3 class="es-agenda-ink text-lg font-bold">Already have the programme written somewhere</h3>
                    <span class="es-agenda-plan es-agenda-plan-ent">Enterprise</span>
                </div>
                <p class="es-agenda-muted text-sm">
                    Agenda scanning reads a programme from a photo or from pasted text and fills the parts in for you, times and all. Being straight about the tier: that one is on the Enterprise plan, and there is a daily limit on it. Typing the parts yourself is free and always will be, which is why the rest of this page does not depend on it.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11:45  The programme: three days, three events               -->
    <!-- ============================================================ -->
    <section id="programme" class="scroll-mt-24 es-agenda-hair border-y py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">11:45</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">The programme</span>
                </div>
                <h2 class="es-balance es-agenda-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">
                    Three days is three events, <span class="es-agenda-accent">stacked.</span>
                </h2>
                <p class="es-agenda-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    One event per date, each with its own running order and its own way in. Read down the strip and you are reading the whole conference at once.
                </p>
            </div>

            <div class="es-agenda-card p-6 sm:p-8" data-reveal="panel">
                <div class="mb-3 flex items-baseline justify-between gap-3">
                    <p class="es-agenda-tag">Cloud Summit 2026</p>
                    <p class="es-agenda-muted text-xs">21 parts across 3 days</p>
                </div>
                <table class="w-full border-collapse text-left">
                    <caption class="sr-only">Cloud Summit 2026: each conference day with its date, the hours it runs and the number of agenda parts inside it</caption>
                    <thead>
                        <tr class="es-agenda-tag">
                            <th scope="col" class="pb-3 font-bold">Day</th>
                            <th scope="col" class="pb-3 font-bold">Date</th>
                            <th scope="col" class="hidden pb-3 font-bold sm:table-cell">Runs</th>
                            <th scope="col" class="pb-3 text-end font-bold">Parts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr aria-hidden="true">
                            <td colspan="4" class="pb-1 pt-1">
                                <div class="es-agenda-ruler">
                                    @foreach (range(9, 17) as $hour)
                                        <span>{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @foreach ($programme as [$dayName, $dayDate, $dayHours, $dayParts, $dayTicks])
                            <tr class="es-agenda-hair border-t">
                                <th scope="row" class="es-agenda-ink py-3 pe-3 align-middle text-sm font-bold">{{ $dayName }}</th>
                                <td class="es-agenda-muted py-3 pe-3 align-middle font-mono text-xs">{{ $dayDate }}</td>
                                <td class="es-agenda-muted hidden py-3 pe-3 align-middle font-mono text-xs sm:table-cell">{{ $dayHours }}</td>
                                <td class="es-agenda-muted py-3 align-middle text-end font-mono text-xs">{{ $dayParts }}</td>
                            </tr>
                            <tr aria-hidden="true">
                                <td colspan="4" class="pb-3">
                                    <div class="es-agenda-track">
                                        @foreach ($dayTicks as [$tickAt, $tickFor, $tickIsGap])
                                            <span class="es-agenda-tick @if ($tickIsGap) es-agenda-tick-gap @endif" style="inset-inline-start: {{ round($tickAt / $window * 100, 2) }}%; width: calc({{ round($tickFor / $window * 100, 2) }}% - 3px);"></span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-agenda-muted mt-4 text-xs">Each mark is one part of that day's running order, as wide as the part is long, drawn where it falls between 09:00 and 18:00. The hollow ones are the breaks. Count them and you get the number in the Parts column.</p>
            </div>

            <p class="es-agenda-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Sub-schedules keep a strand of the programme together and give it a color, which is useful for a workshop day or a members-only strand you want to point people at. Worth saying plainly: a sub-schedule organizes and colors, it does not restrict who can see what, and there are no rooms and no tracks. To hide a day until you announce it, leave it as a draft.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 12:40  In session (fixed-dark: the lights are down)          -->
    <!-- ============================================================ -->
    <section id="session" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-agenda-screen noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(34, 211, 238, 0.14), rgba(34, 211, 238, 0) 60%);"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-agenda-slot mb-5" data-reveal>
                        <span class="es-agenda-live" aria-hidden="true"></span>
                        <span class="es-agenda-lit font-mono text-xs font-extrabold tracking-widest">12:40</span>
                        <span class="es-agenda-rule" aria-hidden="true"></span>
                        <span class="es-agenda-tag">In session, lightning talks</span>
                    </div>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                        One link. <span class="es-agenda-lit">Wherever you stream.</span>
                    </h2>
                    <p class="es-agenda-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                        Mark the day as an online event and paste the URL. Event Schedule stores a link, not an integration, so it has no opinion about where the conference actually happens.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-agenda-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-agenda-ink text-lg font-bold">Any platform</h3>
                            <span class="es-agenda-plan">Free</span>
                        </div>
                        <p class="es-agenda-muted text-sm">Zoom, Microsoft Teams, Google Meet, YouTube Live, a webinar tool, your own player. Anything that hands you a URL works, because the URL is the whole integration.</p>
                    </div>
                    <div class="es-agenda-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-agenda-ink text-lg font-bold">It lands in their calendar</h3>
                            <span class="es-agenda-plan">Free</span>
                        </div>
                        <p class="es-agenda-muted text-sm">Attendees download an .ics for the day, and your own side syncs two ways with Google, Outlook and CalDAV, so the conference sits next to the rest of your week.</p>
                    </div>
                    <div class="es-agenda-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="es-agenda-ink text-lg font-bold">Cap the room</h3>
                            <span class="es-agenda-plan">Free</span>
                        </div>
                        <p class="es-agenda-muted text-sm">Free registration with a capacity limit, and the number of places left is counted for each date separately, so day two filling up says nothing about day three.</p>
                    </div>
                </div>

                <p class="es-agenda-muted mt-10 text-center" data-reveal>
                    One join link belongs to one event, so it covers the whole running order.
                    <a href="{{ marketing_url('/features/online-events') }}" class="es-agenda-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        How online events work
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 14:20  The takings: registration and ticket types. Deliberately
         NOT called "at the door": there is no door on a virtual
         conference, and the spine has already gone live at 12:40, so a
         mark about money reads forward here and a mark about arrival
         would read backward.                                          -->
    <!-- ============================================================ -->
    <section id="tickets" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-agenda-slot mb-5" data-reveal>
                        <span class="es-agenda-clock">14:20</span>
                        <span class="es-agenda-pip" aria-hidden="true"></span>
                        <span class="es-agenda-rule" aria-hidden="true"></span>
                        <span class="es-agenda-tag">The takings</span>
                    </div>
                    <h2 class="es-balance es-agenda-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">
                        Name your prices. <span class="es-agenda-accent">Keep the money.</span>
                    </h2>
                    <p class="es-agenda-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A free conference needs nothing but registration and a capacity, and that is on the free plan. So is charging for one: named ticket types, each with its own price, quantity and sales window, and the first 25 paid tickets every month. Pro at ${{ $proMonthly }} takes the ceiling off and adds individual tickets, discount codes and add-ons. Event Schedule takes nothing from either.
                    </p>
                    <ul class="es-agenda-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-agenda-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Zero platform fees at every plan level. You connect your own Stripe account, and Stripe's own processing fee is the only cut anybody takes.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-agenda-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Individual tickets give everyone in a company's group booking their own confirmation email and their own QR code, instead of one person holding twelve.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-agenda-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Custom questions on the ticket, another Pro one, collect what the conference actually needs: a job title for the badge, an accessibility requirement, which workshop somebody picked.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="es-agenda-accent mt-0.5 h-5 w-5 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Quantities are counted for each date on its own, and on Pro a waitlist catches the people who arrive after a day has sold out.</span>
                        </li>
                    </ul>
                    <p class="es-agenda-muted mt-6 text-sm" data-reveal>
                        See the detail on the <a href="{{ marketing_url('/features/ticketing') }}" class="es-agenda-link font-medium hover:underline">ticketing page</a>.
                    </p>
                </div>

                <div data-reveal="panel">
                    <div class="es-agenda-card p-6 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-agenda-ink text-lg font-bold">Ticket types</h3>
                            <span class="es-agenda-plan">Free</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ([['Full pass', 'all three days', '$149'], ['Single day', 'any one day', '$59'], ['Early bird', 'sales window closes 31 Jan', '$99'], ['Community rate', 'limited quantity', '$25'], ['Student place', 'limited quantity', '$0']] as [$tierName, $tierScope, $tierPrice])
                                <div class="flex items-baseline gap-3 es-agenda-hair border-t pt-2 text-sm first:border-0 first:pt-0">
                                    <span class="es-agenda-ink min-w-0 flex-1 truncate font-semibold">{{ $tierName }}</span>
                                    <span class="es-agenda-muted hidden truncate text-xs sm:inline">{{ $tierScope }}</span>
                                    <span class="es-agenda-ink flex-none font-mono">{{ $tierPrice }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-5 flex items-baseline justify-between gap-3 es-agenda-hair border-t pt-4">
                            <span class="es-agenda-key">Platform fee</span>
                            <span class="es-agenda-accent font-mono text-lg font-black">$0</span>
                        </div>
                        <p class="es-agenda-muted mt-3 text-xs">
                            Every row here is on the free plan: the paid ones capped at 25 tickets a month, the $0 one never counted against that and still selling once the cap is reached. A conference with nothing to charge for turns the event over to free registration instead. To be clear about what this is not, there is no seat map and buyers are not choosing a seat.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 15:30  Afterwards: bento                                     -->
    <!-- ============================================================ -->
    <section id="after" class="scroll-mt-24 es-agenda-hair border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">15:30</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">Afterwards</span>
                </div>
                <h2 class="es-balance es-agenda-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    When the stream stops.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-agenda-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-agenda-ink text-xl font-bold">Feedback that knows which session it is about</h3>
                                <span class="es-agenda-plan">Free</span>
                            </div>
                            <p class="es-agenda-muted mb-4">Attendees add a photo, a video or a comment and pick the part of the running order it belongs to, so the note about the workshop is filed under the workshop rather than under a four hour day. Nothing appears until you approve it.</p>
                            <p class="es-agenda-muted text-sm">The free plan covers 25 photos per schedule; Pro lifts the cap and lets you download the lot as a zip. Star ratings collected after the event are a Pro feature.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-agenda-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-agenda-ink text-xl font-bold">Write to the people who came</h3>
                                <span class="es-agenda-plan">Free</span>
                            </div>
                            <p class="es-agenda-muted">Attendees follow your schedule and you send them a newsletter when next year's programme is set. Nothing goes out on its own: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted one per recipient.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-agenda-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-agenda-ink text-xl font-bold">What the programme page did</h3>
                                <span class="es-agenda-plan">Free</span>
                            </div>
                            <p class="es-agenda-muted">Built-in analytics show page views, the devices people read on, which countries they read from and where the traffic came from, right down to the referrer and the campaign tag. Enough to tell whether the programme page did its job.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-agenda-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-agenda-ink text-xl font-bold">On the conference site you already built</h3>
                                <span class="es-agenda-plan">Free</span>
                            </div>
                            <p class="es-agenda-muted mb-4">Embed the calendar in a page of your own so the programme lives where sponsors and speakers link to it, and switch the schedule to its list layout when a conference reads better as a list than as a month.</p>
                            <p class="es-agenda-muted text-sm">The ticket form embeds too, on Pro, so people can register without leaving your site.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-agenda-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-agenda-ink text-xl font-bold">Announce when you are ready</h3>
                                <span class="es-agenda-plan">Free</span>
                            </div>
                            <p class="es-agenda-muted">A day you have not announced sits on your calendar as a draft and never appears publicly until you say so. Internal and unlisted visibility, including a password on the link, are Enterprise.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-agenda-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-agenda-ink text-xl font-bold">Next year, from this year</h3>
                                <span class="es-agenda-plan">Free</span>
                            </div>
                            <p class="es-agenda-muted mb-4">Clone a day and you get its running order with it, which is most of the work of the next edition already done. On Pro you can save a day as a reusable template, and generate a share graphic that lays your next dates out from their flyer images, with the date printed on each if you switch that on.</p>
                            <p class="es-agenda-muted text-sm">A programme committee that needs more than one login is on Enterprise, which allows up to five team members. The free plan is a single member, so plan the handover if a colleague has to post the schedule.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 16:10  Perfect for                                           -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 es-agenda-hair border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">16:10</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">Perfect for</span>
                </div>
                <h2 class="es-balance es-agenda-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    Every kind of <span class="es-agenda-accent">virtual conference</span>
                </h2>
                <p class="es-agenda-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.15s;">
                    A tech summit or an annual meeting, a half day or a whole week. Also see Event Schedule for <a href="{{ marketing_url('/for-webinars') }}" class="es-agenda-link font-medium hover:underline">webinars</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <!-- Tech Companies -->
                <x-sub-audience-card
                    name="Tech Companies"
                    description="Product launches, developer conferences, hackathons. One event per day, the talks inside it, and a link that works wherever you stream."
                    icon-color="cyan"
                    blog-slug="for-tech-company-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Professional Associations -->
                <x-sub-audience-card
                    name="Professional Associations"
                    description="Annual meetings, certification events, member summits. Publish the running order with times so members can plan the day around one session."
                    icon-color="blue"
                    blog-slug="for-professional-association-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Nonprofits & NGOs -->
                <x-sub-audience-card
                    name="Nonprofits & NGOs"
                    description="Fundraising events, awareness conferences, volunteer summits. Reach supporters anywhere, and Event Schedule takes no cut of what the tickets raise."
                    icon-color="sky"
                    blog-slug="for-nonprofit-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Corporate Teams -->
                <x-sub-audience-card
                    name="Corporate Teams"
                    description="All-hands meetings, training summits, leadership offsites. One link your whole team follows, with the agenda for the day printed on it."
                    icon-color="teal"
                    blog-slug="for-corporate-team-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Academic Institutions -->
                <x-sub-audience-card
                    name="Academic Institutions"
                    description="Research symposiums, faculty conferences, student events. Every paper gets its own slot, with an abstract underneath it."
                    icon-color="amber"
                    blog-slug="for-academic-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Industry Groups -->
                <x-sub-audience-card
                    name="Industry Groups"
                    description="Trade shows, networking events, expert panels. Build a following, then write to them when the next programme is set."
                    icon-color="emerald"
                    blog-slug="for-industry-group-conferences"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 16:40  Three steps                                           -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">16:40</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">Three steps</span>
                </div>
                <h2 class="es-balance es-agenda-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.08s;">
                    From blank to published.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Create the day', 'One event per conference day: its date, its start time, how long it runs, and the link people join.'],
                    ['02', 'Type the running order', 'Add each session as a part with a name, a start and an end. Move the parts into order, and write an abstract where one helps.'],
                    ['03', 'Open the doors', 'Free registration with a capacity limit, or named ticket types, free to 25 paid tickets a month. Share one link for the whole programme.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-agenda-card p-7" data-reveal="panel">
                        <div class="es-agenda-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-agenda-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-agenda-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 17:00  Key features                                          -->
    <!-- ============================================================ -->
    <section class="es-agenda-hair border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">17:00</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">Key features</span>
                </div>
                <h2 class="es-agenda-ink text-2xl font-black tracking-tight md:text-3xl" data-reveal style="--reveal-delay: 0.08s;">What a conference actually leans on</h2>
            </div>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="One join link per event, on any platform that gives you a URL" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Named ticket types, QR codes, and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Analytics" description="Track page views, devices, and traffic sources" :url="marketing_url('/features/analytics')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to the people who follow your schedule" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-agenda-link inline-flex items-center font-medium hover:underline">
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
    <!-- Related pages                                                -->
    <!-- ============================================================ -->
    <section class="es-agenda-hair border-t py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-agenda-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-webinars', 'Webinars'], ['/for-online-classes', 'Online Classes'], ['/for-live-qa-sessions', 'Live Q&A Sessions'], ['/for-watch-parties', 'Watch Parties']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-agenda-hover es-agenda-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-agenda-hover-title es-agenda-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-agenda-hover-arrow es-agenda-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-agenda-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 17:10  FAQ                                                   -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-agenda-slot mb-5" data-reveal>
                    <span class="es-agenda-clock">17:10</span>
                    <span class="es-agenda-pip" aria-hidden="true"></span>
                    <span class="es-agenda-rule" aria-hidden="true"></span>
                    <span class="es-agenda-tag">Questions</span>
                </div>
                <h2 class="es-balance es-agenda-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.08s;">
                    Frequently asked questions
                </h2>
                <p class="es-agenda-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    What conference organizers ask before they move a programme across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-agenda-hover es-agenda-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-agenda-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-agenda-clock flex-none pt-0.5" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-agenda-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-agenda-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-agenda-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 17:30  Close                                                 -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-agenda-screen noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <div class="es-agenda-slot mb-5">
                        <span class="es-agenda-lit font-mono text-xs font-extrabold tracking-widest">17:30</span>
                        <span class="es-agenda-pip" aria-hidden="true"></span>
                        <span class="es-agenda-rule" aria-hidden="true"></span>
                        <span class="es-agenda-tag">Close</span>
                    </div>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        One event. <span class="es-agenda-lit">The whole day inside it.</span>
                    </h2>
                    <p class="es-agenda-muted mx-auto mb-10 max-w-2xl text-lg">
                        Publishing the running order, the join link and the calendar sync is free forever. So is selling, for the first 25 tickets a month; ${{ $proMonthly }} lifts the ceiling. Event Schedule takes nothing out of what you sell either way.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-summit" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono es-agenda-muted text-sm sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-agenda-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02]">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 es-agenda-muted text-sm">No credit card required</p>
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full es-agenda-tip border border-gray-200 px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10">{{ $sectionLabel }}</span>
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
