<x-marketing-layout>
    {{-- SEO Slots --}}
    <x-slot name="title">Search Schedules & Events | Event Schedule</x-slot>
    <x-slot name="description">Search for schedules and upcoming events on Event Schedule. Find fitness classes, music venues, community groups, and more.</x-slot>
    <x-slot name="breadcrumbTitle">{{ __('messages.search') }}</x-slot>

    @if($query)
    <x-slot name="robots">noindex, follow</x-slot>
    @endif

    {{-- Structured Data --}}
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SearchResultsPage",
        "name": "Search Event Schedule",
        "url": "{{ url('/search') }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
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
           Search "The Lookup" styles.

           THE CONCEPT IS A PRINTED CROSS-REFERENCE TABLE. You arrive
           with a word; the table has columns; the cell where your word
           crosses a column is a real record somebody published. That is
           also the product argument, in one sentence: a lookup can only
           ever return what has been published, publishing is free, and
           so an empty cell is an invitation rather than a dead end.

           The devices, all of them the same grammar:
             1. THE CROSSING - the search field sits at the meeting of a
                row rule and a column rule, inside a ticked plate with
                corner marks and a monospace readout of q.
             2. THE CROSS-REFERENCE TABLE - a real <table> of what a
                query is actually matched against, filled cells for a
                hit and hollow dashed cells for a miss. Taken from
                MarketingController::search(), not invented: subdomain
                is a starts-with, name / city / short_description are
                contains, and an event is matched on its own name and
                short_description only.
             3. THE EMPTY CELL - the zero-result and too-short states
                are drawn as an unfilled cell of the same table, which
                is where the create-a-schedule offer belongs. The
                finale is the last unfilled cell: the hero's plate
                again, comb and stub and crop marks included, but
                drawn over the band instead of filled, because that
                one is still waiting for the reader.
             4. THE REGISTER MARKS - section marks are index tabs: an
                accent rule, a monospace label, a dashed leader.

           NOT A CARD CATALOGUE. /for-libraries owns "The Catalog": the
           manila catalogue card, the date-due slip and the oak drawer,
           pinned identical in both colour modes. Nothing here is a
           card, a drawer or a stamped slip. This page is the printed
           cross-reference table, and it INVERTS with the colour mode
           (ink on paper, paper on ink) rather than being a fixed
           physical object, for the same reason /for-curators inverts
           its newsprint: a full-bleed page cannot float a cream slab in
           a dark room. So there is deliberately no pinned band here.

           COLOUR: the page keeps the blue family it was born with,
           pushed to a deep ledger blue and set on a cool paper. Blue is
           the instrument colour of a lookup, and it stays away from the
           bright cyan chrome gradient by never using a cyan stop in
           text. Measured, foreground on ground:
             #101725 ink        16.55 on paper #f3f6fb, 17.93 on #ffffff
             #4b5567 muted       6.94 on paper, 7.51 on #ffffff
             #1d4ed8 accent      6.19 on paper, 6.70 on #ffffff
             #ffffff on #1d4ed8  6.70 (the light-mode button)
             #e8eef8 ink        16.89 on ground #080b12, 15.81 on card
             #9aa6ba muted       8.00 on ground, 7.49 on card
             #93c5fd accent     10.91 on ground, 10.22 on card
             #101725 on #93c5fd  9.94 (the dark-mode button)
             band: #e8eef8 13.51 / #9aa6ba 6.40 / #93c5fd 8.74 on the
             band card #1b2332, and better on the band grounds
             (#101828 -> #0b1220 -> #05080d).
           NEVER text-gray-500 here: 4.83 on pure white is only ~4.4 on
           this cool paper. Muted ink is .es-look-muted.

           NO ARBITRARY TAILWIND VALUES in this page's markup. The
           marketing bundle is only rebuilt on a real build, so a class
           like rounded-[2rem] or text-[0.7rem] silently does nothing.
           Every colour, radius and odd size below is a real rule here.
           ============================================================== */

        #es-look-page {
            --esl-paper: #f3f6fb;
            --esl-alt: #eef2f9;
            --esl-card: #ffffff;
            --esl-ink: #101725;
            --esl-muted: #4b5567;
            --esl-accent: #1d4ed8;
            --esl-rule: rgba(16, 23, 37, 0.13);
            --esl-rule-soft: rgba(16, 23, 37, 0.07);
            --esl-rule-strong: rgba(16, 23, 37, 0.26);
            --esl-wash: rgba(29, 78, 216, 0.07);
            background-color: var(--esl-paper);
            color: var(--esl-ink);
        }
        .dark #es-look-page {
            --esl-paper: #080b12;
            --esl-alt: #0b0f18;
            --esl-card: #11141b;
            --esl-ink: #e8eef8;
            --esl-muted: #9aa6ba;
            --esl-accent: #93c5fd;
            --esl-rule: rgba(232, 238, 248, 0.14);
            --esl-rule-soft: rgba(232, 238, 248, 0.08);
            --esl-rule-strong: rgba(232, 238, 248, 0.3);
            --esl-wash: rgba(147, 197, 253, 0.1);
        }

        /* --- Ink --- */
        .es-look-ink { color: var(--esl-ink); }
        .es-look-muted { color: var(--esl-muted); }
        .es-look-accent { color: var(--esl-accent); }
        .es-look-alt { background-color: var(--esl-alt); }
        .es-look-hr { border-top: 1px solid var(--esl-rule); }
        .es-look-fine { font-size: 0.72rem; line-height: 1.55; }

        .es-look-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Register marks: an accent rule, a label, a dashed leader --- */
        .es-look-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: var(--esl-muted);
        }
        .es-look-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--esl-muted);
        }
        .es-look-mark::before {
            content: "";
            width: 1.75rem;
            height: 2px;
            border-radius: 1px;
            background: var(--esl-accent);
        }
        .es-look-mark::after {
            content: "";
            width: 3rem;
            border-top: 1px dashed var(--esl-rule-strong);
        }

        /* --- Surfaces --- */
        .es-look-card {
            background-color: var(--esl-card);
            border: 1px solid var(--esl-rule);
            border-radius: 1rem;
        }
        .es-look-plate {
            position: relative;
            overflow: hidden;
            background-color: var(--esl-card);
            border: 1px solid var(--esl-rule);
            border-radius: 1.35rem;
            box-shadow: 0 26px 60px -32px rgba(16, 23, 37, 0.4);
        }
        .dark .es-look-plate { box-shadow: 0 26px 60px -30px rgba(0, 0, 0, 0.75); }
        /* Corner marks: the crop ticks of a printed cell. */
        .es-look-plate::before,
        .es-look-plate::after {
            content: "";
            position: absolute;
            width: 14px;
            height: 14px;
            border: 2px solid var(--esl-accent);
            opacity: 0.5;
            pointer-events: none;
        }
        .es-look-plate::before { bottom: 11px; inset-inline-start: 11px; border-inline-end: 0; border-top: 0; }
        .es-look-plate::after { bottom: 11px; inset-inline-end: 11px; border-inline-start: 0; border-top: 0; }

        /* --- The crossing: the stub column and the header rule of a printed
               table cell, in flow rather than absolutely placed, so the two
               rules meet on the same pixel at every width. --- */
        .es-look-ticks-row {
            height: 10px;
            border-bottom: 1px solid var(--esl-rule);
            background-image: repeating-linear-gradient(90deg, var(--esl-rule-strong) 0 1px, transparent 1px 11px);
        }
        .es-look-stubrow {
            display: flex;
            align-items: stretch;
            border-bottom: 1px solid var(--esl-rule);
        }
        .es-look-stub {
            display: flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            border-inline-end: 1px solid var(--esl-rule);
            background-color: var(--esl-wash);
            color: var(--esl-muted);
            font-size: 0.85rem;
            font-weight: 800;
        }

        /* --- The readout --- */
        .es-look-read {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.6rem;
            border-radius: 0.45rem;
            border: 1px solid var(--esl-rule);
            background-color: var(--esl-wash);
            color: var(--esl-muted);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .es-look-caret {
            display: inline-block;
            width: 0.5rem;
            height: 0.95em;
            border-radius: 1px;
            background: var(--esl-accent);
            vertical-align: -0.12em;
        }
        html.es-anim .es-look-caret { animation: es-look-blink 1.15s steps(1, end) infinite; }
        @keyframes es-look-blink {
            0%, 49% { opacity: 1; }
            50%, 100% { opacity: 0.18; }
        }
        .es-look-quote {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            color: var(--esl-ink);
            background-color: var(--esl-wash);
            border: 1px solid var(--esl-rule);
            border-radius: 0.35rem;
            padding: 0.05rem 0.4rem;
            display: inline-block;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: bottom;
        }

        /* --- The field and the buttons --- */
        .es-look-field {
            width: 100%;
            border-radius: 0.85rem;
            border: 1px solid var(--esl-rule);
            background-color: var(--esl-card);
            color: var(--esl-ink);
        }
        .es-look-field::placeholder { color: var(--esl-muted); opacity: 1; }
        .es-look-field:focus {
            outline: none;
            border-color: var(--esl-accent);
            box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.18);
        }
        .dark .es-look-field:focus { box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.22); }

        .es-look-btn {
            background-color: #1d4ed8;
            color: #ffffff;
            border-radius: 0.85rem;
            box-shadow: 0 18px 36px -18px rgba(29, 78, 216, 0.6);
        }
        .es-look-btn:hover { background-color: #1740b8; }
        .dark .es-look-btn { background-color: #93c5fd; color: #101725; }
        .dark .es-look-btn:hover { background-color: #bfd6f7; }
        .es-look-band .es-look-btn { background-color: #93c5fd; color: #101725; }
        .es-look-band .es-look-btn:hover { background-color: #bfd6f7; }

        .es-look-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem;
            border-radius: 9999px;
            border: 1px solid var(--esl-rule);
            background-color: var(--esl-card);
            color: var(--esl-muted);
            font-size: 0.8rem;
            font-weight: 600;
        }
        .es-look-chip:hover { border-color: var(--esl-accent); color: var(--esl-ink); }

        .es-look-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            border: 1px solid var(--esl-rule);
            background-color: var(--esl-wash);
            color: var(--esl-accent);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 800;
        }

        /* --- Plan tags --- */
        .es-look-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid var(--esl-accent);
            color: var(--esl-accent);
        }
        .es-look-plan-pro { border-color: var(--esl-rule-strong); color: var(--esl-ink); }

        /* --- The table: filled cell = a hit, dashed cell = a miss --- */
        /* Below ~34rem the three columns are squeezed to nothing, so the table
           keeps its width and scrolls inside its own overflow-x panel. */
        .es-look-table { width: 100%; min-width: 33rem; border-collapse: collapse; }
        .es-look-table th,
        .es-look-table td {
            padding: 0.8rem 0.75rem;
            text-align: start;
            vertical-align: top;
            border-top: 1px solid var(--esl-rule);
        }
        .es-look-table thead th {
            border-top: 0;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--esl-muted);
        }
        .es-look-hit,
        .es-look-miss {
            display: inline-flex;
            align-items: baseline;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        .es-look-hit { color: var(--esl-ink); font-weight: 600; }
        .es-look-miss { color: var(--esl-muted); }
        .es-look-hit::before,
        .es-look-miss::before {
            content: "";
            flex: none;
            width: 0.6rem;
            height: 0.6rem;
            border-radius: 2px;
            transform: translateY(0.1rem);
        }
        .es-look-hit::before { background: var(--esl-accent); }
        .es-look-miss::before { border: 1px dashed var(--esl-rule-strong); }

        /* --- The empty cell: the zero-result state, drawn as an unfilled cell --- */
        .es-look-slot {
            position: relative;
            background-color: var(--esl-card);
            border: 2px dashed var(--esl-rule-strong);
            border-radius: 1.35rem;
            background-image: repeating-linear-gradient(135deg, var(--esl-rule-soft) 0 6px, transparent 6px 15px);
        }

        /* --- Result rows --- */
        .es-look-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease; }
        .es-look-hover:hover { border-color: var(--esl-accent); }
        .es-look-hover:hover .es-look-hover-title,
        .es-look-hover:hover .es-look-hover-arrow { color: var(--esl-accent); }
        .es-look-avatar {
            background-color: #1d4ed8;
            color: #ffffff;
        }
        .es-look-meta {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8rem;
            color: var(--esl-muted);
        }

        /* --- The fixed-dark band. Not a physical object: it is the ledger's
               reverse, dark in both colour modes because it is the list of
               what the index never returns. --- */
        .es-look-band {
            --esl-card: #1b2332;
            --esl-ink: #e8eef8;
            --esl-muted: #9aa6ba;
            --esl-accent: #93c5fd;
            --esl-rule: rgba(232, 238, 248, 0.14);
            --esl-rule-soft: rgba(232, 238, 248, 0.08);
            --esl-rule-strong: rgba(232, 238, 248, 0.3);
            --esl-wash: rgba(147, 197, 253, 0.1);
            /* currentColor has to be pinned too, or anything inside the band that
               inherits it (the zero-width edges of the crop marks, a stroke="currentColor"
               icon without its own class) still flips with the colour mode. */
            color: var(--esl-ink);
            border-radius: 2rem;
            background-color: #0b1220;
            background-image: radial-gradient(120% 100% at 50% 0%, #101828 0%, #0b1220 55%, #05080d 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(232, 238, 248, 0.05);
        }
        /* Shared classes that flip themselves with the colour mode: pin them so
           the band reads the same with .dark on and off. */
        .es-look-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 238, 248, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 238, 248, 0.05) 1px, transparent 1px);
        }
        .es-look-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-look-band .es-claim:focus-within {
            border-color: rgba(147, 197, 253, 0.75);
            box-shadow: 0 0 0 4px rgba(147, 197, 253, 0.22);
        }
        /* The finale's cell is DRAWN, not filled: same comb, stub and crop marks as
           the hero's, but the ground shows through, because this is the one cell on
           the page still waiting for somebody to write in it. */
        .es-look-band .es-look-plate {
            background-color: transparent;
            box-shadow: none;
        }

        /* --- Links --- */
        .es-look-link { color: var(--esl-accent); }
        .es-look-link:hover { color: var(--esl-ink); }

        /* --- Shared-system recolours (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(29, 78, 216, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(147, 197, 253, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 78, 216, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(147, 197, 253, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d4ed8; }
        .dark .es-dot.is-active .es-dot-pip { background: #93c5fd; }

        /* --- Focus rings. No border-radius here: setting it would change the
               element's own shape on focus, and outlines already follow it. --- */
        #es-look-page a:focus-visible,
        #es-look-page summary:focus-visible,
        #es-look-page button:focus-visible {
            outline: 2px solid var(--esl-accent);
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-look-caret { animation: none !important; opacity: 1; }
            .es-look-hover { transition: none !important; }
        }
    </style>

    @php
        // What a query is actually matched against, straight out of
        // MarketingController::search(): the schedule subdomain is a starts-with,
        // name / city / short_description are contains, and an event is matched on
        // its own name and short_description only.
        $lookupRows = [
            ['blue note', 'a name', 'Schedule name, anywhere in it', true, 'Event name, anywhere in it', true],
            ['bluenote', 'a web address', 'Web address, from the start', true, 'Not matched on the address', false],
            ['leeds', 'a city', 'City, anywhere in it', true, 'Not matched on the venue city', false],
            ['swing lesson', 'words from a blurb', 'Short description, anywhere in it', true, 'Short description, anywhere in it', true],
        ];

        // Starting points. Each one is a real lookup on this page.
        $starters = ['jazz', 'yoga', 'comedy', 'market', 'workshop'];

        $faqs = [
            [
                'q' => 'Why can I not find a schedule I know exists?',
                'a' => 'A schedule joins this index once its owner has confirmed an email address or a phone number, and it stays in until the schedule is deleted. Demo schedules are filtered out. So a brand new schedule whose owner has not confirmed their contact details yet will not be here, and neither will its events, because an event only reaches the index through a schedule that is already in it.',
            ],
            [
                'q' => 'What does a search actually match?',
                'a' => 'Schedules are matched on their web address, which is a starts-with, and on their name, city and short description, which are contains. Events are matched on their own name and short description only, so a city name finds the venue rather than the individual nights. Two characters is the minimum.',
            ],
            [
                'q' => 'How do I get my own events in here?',
                'a' => 'Create a schedule, confirm your email address or phone number, and publish events as public rather than keeping them as drafts. That is the whole requirement, and all of it is free. Public upcoming events on a listed schedule arrive in the index alongside the schedule itself.',
            ],
            [
                'q' => 'Why only twelve results?',
                'a' => 'A lookup returns up to twelve schedules, alphabetically by name, and up to twelve upcoming events, soonest first. It is a lookup rather than a directory listing: narrow the wording, or type the schedule web address, to land on the record you want. Browse is the wider view if you would rather read across everything that is on.',
            ],
            [
                'q' => 'Can I search past events?',
                'a' => 'No. Only upcoming dates and recurring events come back, so a finished one-off drops out of the index on its own. A cancelled event drops out too.',
            ],
            [
                'q' => 'Does it cost anything to be listed?',
                'a' => 'No. Publishing a schedule and its events is free forever, and that is all being findable takes. Selling tickets is also included, up to 25 paid tickets a month on the Free plan, and Event Schedule charges zero platform fees on ticket sales at any tier, so past your payment processor\'s own fee the money is yours.',
            ],
        ];

        $dotSections = [];
        if ($query) {
            $dotSections[] = ['results', 'Results'];
        }
        $dotSections = array_merge($dotSections, [
            ['reads', 'What it reads'],
            ['never', 'What it never returns'],
            ['listed', 'Getting listed'],
            ['free', 'Free to publish'],
            ['faq', 'Questions'],
            ['claim', 'Your address'],
        ]);
    @endphp

    <div id="es-look-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the crossing                                        -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative overflow-hidden py-16 sm:py-20">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 68%, rgba(29, 78, 216, 0.22), rgba(29, 78, 216, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 76% 30%, rgba(37, 99, 235, 0.18), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 glass mb-7 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                <svg aria-hidden="true" class="es-look-accent h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="es-look-muted text-sm font-medium tracking-wide">{{ __('messages.discover') }}</span>
            </div>

            <h1 class="es-balance es-fade-up es-d-2 es-look-ink mb-5 text-4xl font-black leading-tight tracking-tight sm:text-6xl">
                {{ __('messages.find_schedules_and_events') }}
            </h1>

            <p class="es-fade-up es-d-3 es-look-muted mx-auto mb-10 max-w-2xl text-lg sm:text-xl">
                {{ __('messages.search_page_subtitle') }}
            </p>

            {{-- The crossing: the comb along the top edge, the stub column and the
                 header rule of a printed cell, and the field inside it. The form
                 itself is untouched: GET, one field named q, autofocused. --}}
            <div class="es-fade-up es-d-4 es-look-plate mx-auto max-w-2xl text-start">
                <div class="es-look-ticks-row" aria-hidden="true"></div>
                <div class="es-look-stubrow">
                    <span class="es-look-stub es-look-mono" aria-hidden="true">q</span>
                    <div class="flex flex-1 flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <span class="es-look-tag">The lookup</span>
                        <span class="es-look-read">
                            @if($query)
                                q = "{{ $query }}"
                            @else
                                q = <span class="es-look-caret" aria-hidden="true"></span>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="relative z-10 p-5 sm:p-7">
                    <form action="{{ marketing_url('/search') }}" method="GET">
                        <label for="es-look-q" class="sr-only">{{ __('messages.search') }}</label>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="relative flex-1">
                                <svg aria-hidden="true" class="es-look-muted absolute top-1/2 h-5 w-5 -translate-y-1/2 ltr:left-4 rtl:right-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    id="es-look-q"
                                    type="search"
                                    name="q"
                                    value="{{ $query }}"
                                    placeholder="{{ __('messages.search') }}..."
                                    class="es-look-field py-4 text-lg ltr:pl-12 ltr:pr-4 rtl:pl-4 rtl:pr-12"
                                    autofocus
                                >
                            </div>
                            <button type="submit" class="es-look-btn px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                                {{ __('messages.search') }}
                            </button>
                        </div>
                    </form>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <span class="es-look-tag ltr:mr-1 rtl:ml-1">Try</span>
                        @foreach($starters as $starter)
                            <a href="{{ marketing_url('/search?q=' . urlencode($starter)) }}" class="es-look-chip es-look-mono">{{ $starter }}</a>
                        @endforeach
                    </div>

                    <p class="es-look-muted es-look-fine es-look-hr mt-5 pt-4">
                        Two characters minimum. A lookup returns up to twelve schedules, alphabetically, and up to twelve upcoming events, soonest first.
                        Would you rather read across everything that is on? <a href="{{ marketing_url('/browse') }}" class="es-look-link font-semibold hover:underline">Browse events</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @if($query)
    <!-- ============================================================ -->
    <!-- 2. Results: the cell your word crossed                       -->
    <!-- ============================================================ -->
    <section id="results" class="es-look-alt es-look-hr scroll-mt-24 py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            <div class="es-fade-up es-d-1 mb-10 flex flex-wrap items-center justify-between gap-4">
                <span class="es-look-mark">The crossing</span>
                <span class="es-look-read">
                    q = "{{ $query }}"
                    @if($searched)
                        &middot; {{ $schedules->count() }} {{ \Illuminate\Support\Str::plural('schedule', $schedules->count()) }}
                        &middot; {{ $events->count() }} {{ \Illuminate\Support\Str::plural('event', $events->count()) }}
                    @endif
                </span>
            </div>

            @if($searched && $schedules->count() > 0)
            {{-- Schedules Results --}}
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <h2 class="es-look-ink text-2xl font-black tracking-tight">{{ __('messages.schedules') }}</h2>
                    <span class="es-look-pill">{{ $schedules->count() }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($schedules as $schedule)
                    @php $scheduleUrl = $schedule->getGuestUrl(); @endphp
                    @if($scheduleUrl)
                    <a href="{{ $scheduleUrl }}" target="_blank" rel="noopener" class="es-look-card es-look-hover group flex flex-col overflow-hidden hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex flex-1 flex-col p-6">
                            <div class="mb-4 flex items-center gap-4">
                                @if($schedule->profile_image_url)
                                    <img src="{{ $schedule->profile_image_url }}" alt="{{ $schedule->name }}" class="h-12 w-12 rounded-full object-cover" width="48" height="48" loading="lazy" decoding="async">
                                @else
                                    <div class="es-look-avatar flex h-12 w-12 flex-none items-center justify-center rounded-full text-lg font-bold" aria-hidden="true">
                                        {{ strtoupper(substr($schedule->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h3 class="es-look-hover-title es-look-ink truncate text-lg font-bold transition-colors">{{ $schedule->name }}</h3>
                                    @if($schedule->city)
                                        <p class="es-look-muted truncate text-sm">{{ $schedule->city }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($schedule->short_description)
                                <p class="es-look-muted mb-4 line-clamp-2 text-sm">{{ $schedule->short_description }}</p>
                            @endif
                            @if($schedule->type)
                                <div class="mt-auto">
                                    <span class="es-look-chip es-look-mono">{{ $schedule->type }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if($searched && $events->count() > 0)
            {{-- Events Results --}}
            <div class="mb-16">
                <div class="flex items-center gap-3 mb-8">
                    <h2 class="es-look-ink text-2xl font-black tracking-tight">{{ __('messages.upcoming_events') }}</h2>
                    <span class="es-look-pill">{{ $events->count() }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($events as $event)
                    @php $eventUrl = $event->getGuestUrl(); @endphp
                    @if($eventUrl)
                    <a href="{{ $eventUrl }}" target="_blank" rel="noopener" class="es-look-card es-look-hover group flex flex-col overflow-hidden hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex flex-1 flex-col p-6">
                            <h3 class="es-look-hover-title es-look-ink mb-2 text-lg font-bold transition-colors">{{ $event->name }}</h3>
                            @if($event->short_description)
                                <p class="es-look-muted mb-4 line-clamp-2 text-sm">{{ $event->short_description }}</p>
                            @endif
                            <div class="mt-auto flex flex-col gap-2">
                                @if($event->starts_at)
                                    <div class="es-look-meta">
                                        <svg aria-hidden="true" class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="es-look-mono">{{ $event->getShortDateRangeDisplay('M j, Y g:ia') }}</span>
                                    </div>
                                @elseif($event->days_of_week)
                                    <div class="es-look-meta">
                                        <svg aria-hidden="true" class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span>{{ __('messages.recurring') }}</span>
                                    </div>
                                @endif
                                @if($event->roles->first())
                                    <div class="es-look-meta">
                                        <svg aria-hidden="true" class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <span class="truncate">{{ $event->roles->first()->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            @if(!$searched)
            {{-- Query too short: an unfilled cell, and what to put in it --}}
            <div class="es-look-slot mx-auto max-w-2xl p-8 text-center sm:p-10">
                <span class="es-look-tag">Two characters</span>
                <p class="es-look-ink mt-4 text-xl font-bold">
                    {{ __('messages.search_min_length') }}
                </p>
                <p class="es-look-muted mt-3 text-sm">
                    A single letter would cross every column at once. Two is enough to aim.
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                    @foreach($starters as $starter)
                        <a href="{{ marketing_url('/search?q=' . urlencode($starter)) }}" class="es-look-chip es-look-mono">{{ $starter }}</a>
                    @endforeach
                </div>
            </div>
            @elseif($schedules->count() === 0 && $events->count() === 0)
            {{-- No Results: the empty cell, which is the offer --}}
            <div class="es-look-slot mx-auto max-w-3xl p-8 sm:p-12">
                <div class="text-center">
                    <span class="es-look-tag">Empty cell</span>
                    <h2 class="es-look-ink es-balance mt-4 text-3xl font-black tracking-tight">{{ __('messages.no_results_found') }}</h2>
                    <p class="es-look-muted mx-auto mt-4 max-w-xl">
                        {{ __('messages.no_results_message', ['query' => $query]) }}
                    </p>
                </div>

                <div class="es-look-hr mt-8 grid gap-6 pt-8 sm:grid-cols-2">
                    <div>
                        <p class="es-look-tag mb-3">Three things to try</p>
                        <ul class="es-look-muted space-y-3 text-sm">
                            <li class="flex gap-3">
                                <span class="es-look-accent es-look-mono flex-none font-bold" aria-hidden="true">01</span>
                                <span>Fewer words. <span class="es-look-quote">{{ \Illuminate\Support\Str::limit($query, 18) }}</span> is matched as one string, not as separate terms.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="es-look-accent es-look-mono flex-none font-bold" aria-hidden="true">02</span>
                                <span>A city or a schedule name. Events themselves are only matched on their own name and blurb.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="es-look-accent es-look-mono flex-none font-bold" aria-hidden="true">03</span>
                                <span>The web address, from the start, because that match is a starts-with: <span class="es-look-quote">blue</span> finds <span class="es-look-quote">bluenote</span></span>
                            </li>
                        </ul>
                    </div>
                    <div class="flex flex-col">
                        <p class="es-look-tag mb-3">Or fill the cell yourself</p>
                        <p class="es-look-muted mb-5 text-sm">
                            An index only returns what somebody published. If the thing you were looking for is yours, publishing it takes a schedule, a confirmed email address, and no money at all.
                        </p>
                        <div class="mt-auto flex flex-wrap gap-3">
                            <a href="{{ marketing_url('/browse') }}" class="es-look-chip px-5 py-3 text-base">
                                Browse what is on
                            </a>
                            <a href="{{ app_url('/sign_up') }}" class="es-look-btn group inline-flex items-center gap-2 px-6 py-3 text-base font-semibold transition-all duration-200 hover:-translate-y-0.5">
                                {{ __('messages.create_your_schedule') }}
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
    @endif

    <!-- ============================================================ -->
    <!-- 3. What the lookup reads: the cross-reference table           -->
    <!-- ============================================================ -->
    <section id="reads" class="es-look-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="mb-5 flex justify-center" data-reveal>
                    <span class="es-look-mark">01 &middot; What it reads</span>
                </div>
                <h2 class="es-balance es-look-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Your word, and the columns it can <span class="es-look-accent">cross.</span>
                </h2>
                <p class="es-look-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    A lookup is not one search box against one pile of text. Two kinds of record are read, and they are read differently. Filled cell, a hit. Hollow cell, a miss.
                </p>
            </div>

            <div class="es-look-card overflow-x-auto p-5 sm:p-8" data-reveal="panel">
                <table class="es-look-table">
                    <caption class="es-look-muted es-look-fine mb-4 text-start">
                        What a query is matched against, by record type. Public records only.
                    </caption>
                    <thead>
                        <tr>
                            <th scope="col">You type</th>
                            <th scope="col">Schedules</th>
                            <th scope="col">Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lookupRows as [$rowQuery, $rowKind, $rowSchedule, $rowScheduleHit, $rowEvent, $rowEventHit])
                            <tr>
                                <th scope="row" class="es-look-ink align-top font-semibold">
                                    <span class="es-look-quote es-look-fine">{{ $rowQuery }}</span>
                                    <span class="es-look-muted es-look-fine mt-2 block font-normal">{{ $rowKind }}</span>
                                </th>
                                <td>
                                    <span class="@if ($rowScheduleHit) es-look-hit @else es-look-miss @endif">{{ $rowSchedule }}</span>
                                </td>
                                <td>
                                    <span class="@if ($rowEventHit) es-look-hit @else es-look-miss @endif">{{ $rowEvent }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="es-look-muted es-look-fine mt-3 sm:hidden">The table scrolls sideways.</p>

            <div class="mt-6 grid gap-4 sm:grid-cols-3" data-reveal-group="90">
                <div class="es-look-card p-5" data-reveal="panel">
                    <p class="es-look-tag mb-2">Order</p>
                    <p class="es-look-muted text-sm">Schedules come back alphabetically by name. Events come back soonest first, with undated recurring nights after them.</p>
                </div>
                <div class="es-look-card p-5" data-reveal="panel">
                    <p class="es-look-tag mb-2">Depth</p>
                    <p class="es-look-muted text-sm">Twelve of each. A lookup is meant to land you on a record, not to page through a directory.</p>
                </div>
                <div class="es-look-card p-5" data-reveal="panel">
                    <p class="es-look-tag mb-2">Scope</p>
                    <p class="es-look-muted text-sm">Upcoming and recurring events only, and only from schedules that are publicly listed.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What a lookup never returns (fixed-dark band)              -->
    <!-- ============================================================ -->
    <section id="never" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-look-band noise relative overflow-hidden px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="mb-5 flex justify-center" data-reveal>
                        <span class="es-look-mark">02 &middot; The reverse</span>
                    </div>
                    <h2 class="es-balance es-look-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                        An index is also a promise about <span class="es-look-accent">what stays out.</span>
                    </h2>
                    <p class="es-look-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                        Being findable is a choice you make per event, not a setting you have to keep policing.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-look-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-look-ink text-lg font-bold">Drafts stay yours</h3>
                            <span class="es-look-plan">Free</span>
                        </div>
                        <p class="es-look-muted text-sm">An event kept as a draft is visible to you and to nobody else. It is how a season gets built weeks before it is announced, and it is never in this index.</p>
                    </div>
                    <div class="es-look-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-look-ink text-lg font-bold">Unlisted keeps its link</h3>
                            <span class="es-look-plan es-look-plan-pro">Enterprise</span>
                        </div>
                        <p class="es-look-muted text-sm">An unlisted event stays off the schedule and out of the index while its direct link keeps working, with an optional password. Internal is the other Enterprise option: your own members see it, the public never does.</p>
                    </div>
                    <div class="es-look-card p-6" data-reveal="panel">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <h3 class="es-look-ink text-lg font-bold">Yesterday drops out</h3>
                            <span class="es-look-plan">Free</span>
                        </div>
                        <p class="es-look-muted text-sm">A finished one-off leaves the index by itself, and a cancelled event leaves it immediately. Nothing has to be tidied up after the night is over.</p>
                    </div>
                </div>

                <p class="es-look-muted mt-10 text-center" data-reveal>
                    Publish it and it is findable. Keep it a draft and it is not.
                    <a href="{{ marketing_url('/features/private-events') }}" class="es-look-accent inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Private and password-protected events
                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Getting into the index: three rows                         -->
    <!-- ============================================================ -->
    <section id="listed" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="mb-5 flex justify-center" data-reveal>
                    <span class="es-look-mark">03 &middot; Getting listed</span>
                </div>
                <h2 class="es-balance es-look-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Three rows, and you are <span class="es-look-accent">in the table.</span>
                </h2>
                <p class="es-look-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    There is no submission queue and no listing fee. There is a schedule, a confirmed way to reach you, and events that are actually published.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="120">
                @foreach ([
                    ['01', 'Take an address', 'Your schedule lives at its own web address, and that address is the string this lookup matches from the start. Pick the word people would type.'],
                    ['02', 'Confirm your email or phone', 'One confirmed contact detail is what puts a schedule into the public index. It is also how somebody who finds you can get an answer.'],
                    ['03', 'Publish public events', 'Upcoming dates and recurring nights arrive in the index alongside the schedule. Anything you are not ready to announce stays a draft.'],
                ] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-look-card p-7" data-reveal="panel">
                        <div class="es-look-accent es-look-mono mb-3 text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-look-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-look-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. What being published gives you: bento                      -->
    <!-- ============================================================ -->
    <section id="free" class="es-look-alt es-look-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="mb-5 flex justify-center" data-reveal>
                    <span class="es-look-mark">04 &middot; Free to publish</span>
                </div>
                <h2 class="es-balance es-look-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Findable is the start of it.
                </h2>
                <p class="es-look-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    Everything on this row is on the free plan unless it says otherwise.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-look-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-look-ink text-xl font-bold">One address people can type</h3>
                                <span class="es-look-plan">Free</span>
                            </div>
                            <p class="es-look-muted mb-4">Your schedule gets its own web address and a page that reads properly on a phone. Every event on it gets its own page, a downloadable calendar file, and a link preview that carries the event's own image.</p>
                            <p class="es-look-muted text-sm">There is a QR code for the same address, free on every plan, for the poster in the window or the flyer on the table.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-look-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-look-ink text-xl font-bold">On the site you already have</h3>
                                <span class="es-look-plan">Free</span>
                            </div>
                            <p class="es-look-muted">Embed the calendar on your own website so the same dates appear wherever people look you up.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-look-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-look-ink text-xl font-bold">Sync both ways</h3>
                                <span class="es-look-plan">Free</span>
                            </div>
                            <p class="es-look-muted">Two-way sync with Google Calendar, Outlook and CalDAV, so the calendar you already keep stays the one you keep.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative md:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-look-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-look-ink text-xl font-bold">The people who found you once</h3>
                                <span class="es-look-plan">Free</span>
                            </div>
                            <p class="es-look-muted mb-4">Visitors leave a name and an email address, and get a digest automatically the next time you publish events. Anything more than that is a newsletter you write yourself, with open and click rates back afterwards.</p>
                            <p class="es-look-muted text-sm">The number worth knowing first: 10 emails a month on Free, 100 on Pro, 1,000 on Enterprise, counted per recipient rather than per send.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-look-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-look-ink text-xl font-bold">Free nights still need a count</h3>
                                <span class="es-look-plan">Free</span>
                            </div>
                            <p class="es-look-muted">Turn on registration with a capacity limit and each date keeps its own remaining count.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-look-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-look-ink text-xl font-bold">And when you sell a ticket</h3>
                                <span class="es-look-plan">Free</span>
                            </div>
                            <p class="es-look-muted mb-4">Named ticket types with their own prices, quantities and sales windows, QR check-in at the door, and Stripe connected to your own account. Zero platform fees on every plan, including this one: Free sells 25 paid tickets a month per schedule and Pro lifts the ceiling.</p>
                            <p class="es-look-muted text-sm">
                                Built-in analytics are free: page views, devices and where the traffic came from.
                                <a href="{{ marketing_url('/features') }}" class="es-look-link font-semibold hover:underline">See all features</a>
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
    <!-- 7. FAQ                                                        -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="es-look-hr scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="mb-5 flex justify-center" data-reveal>
                    <span class="es-look-mark">05 &middot; Questions</span>
                </div>
                <h2 class="es-balance es-look-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-look-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    How the index is built, and what it will and will not tell you.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-look-card es-look-hover group p-6" data-reveal>
                        <summary class="es-look-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-look-accent es-look-mono flex-none text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-look-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-look-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-look-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Related pages                                              -->
    <!-- ============================================================ -->
    <section class="es-look-hr py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-look-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Keep looking</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([
                    ['/browse', 'Browse events', 'Read across what is on'],
                    ['/use-cases', 'Use cases', 'Who runs a schedule here'],
                    ['/features', 'Features', 'Everything the platform does'],
                    ['/pricing', 'Pricing', 'Free forever, Pro, Enterprise'],
                ] as [$relHref, $relName, $relBlurb])
                    <a href="{{ marketing_url($relHref) }}" class="es-look-card es-look-hover group flex flex-col p-5 hover:shadow-md" data-reveal>
                        <span class="es-look-hover-title es-look-ink mb-2 text-sm font-bold transition-colors">{{ $relName }}</span>
                        <span class="es-look-muted es-look-fine mb-3">{{ $relBlurb }}</span>
                        <span class="es-look-hover-arrow es-look-muted es-look-fine mt-auto inline-flex items-center gap-1 font-semibold transition-colors">
                            Open
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Finale: the word you claim is the word people look up      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-look-band noise relative overflow-hidden px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-look-tag mb-4">Free forever</p>
                    <h2 class="es-balance es-look-ink mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight md:text-5xl">
                        {{ __('messages.create_your_own_schedule') }}
                    </h2>
                    <p class="es-look-muted mx-auto mb-10 max-w-2xl text-lg">
                        {{ __('messages.share_events_cta') }} The address you take here is the word somebody types in the box at the top of this page.
                    </p>

                    {{-- The last cell. The same drawn cell as the hero, comb, stub and
                         crop marks included, but this one is still unfilled and the
                         reader is the one holding the pen. --}}
                    <div class="es-look-plate mx-auto max-w-2xl text-start">
                        <div class="es-look-ticks-row" aria-hidden="true"></div>
                        <div class="es-look-stubrow">
                            <span class="es-look-stub es-look-mono" aria-hidden="true">q</span>
                            <div class="flex flex-1 flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <span class="es-look-tag">The cell you fill</span>
                                <span class="es-look-read">q = "your-schedule"</span>
                            </div>
                        </div>

                        <div class="relative z-10 p-5 sm:p-6">
                            <div class="flex flex-col items-stretch justify-center gap-3 sm:flex-row">
                                <label for="es-claim-input" class="sr-only">Your schedule address</label>
                                <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 transition-all">
                                    <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                        class="es-look-mono min-w-0 flex-1 border-0 bg-transparent p-0 text-right text-sm font-semibold text-white placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-base">
                                    <span class="es-look-muted es-look-mono shrink-0 select-none text-sm sm:text-base">.eventschedule.com</span>
                                </div>
                                <a href="{{ app_url('/sign_up') }}" class="es-look-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden px-8 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5">
                                    <span class="relative z-10 flex items-center gap-2">
                                        {{ __('messages.get_started_free') }}
                                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </span>
                                    <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                                </a>
                            </div>

                            <p class="es-look-muted es-look-fine es-look-hr mt-5 pt-4">
                                No credit card required. Whatever you type here is the string a lookup matches from the start, so pick the word people would already say out loud.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            <li class="relative">
                <a href="#top" class="es-dot group block rounded-full" aria-label="The lookup">
                    <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                    <span class="es-look-card pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">The lookup</span>
                </a>
            </li>
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="es-look-card pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap px-3 py-1 text-xs font-medium opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
