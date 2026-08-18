<x-marketing-layout>
    <x-slot name="title">Gallery Calendars | The Show Runs, the Evenings Do Not</x-slot>
    <x-slot name="description">A six-week hang is one recurring event that stops itself on the closing date, not thirty entries. The private view, the artist talk and the closing are the four evenings you add on top.</x-slot>
    <x-slot name="breadcrumbTitle">For Art Galleries</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Art Galleries",
        "description": "An exhibition calendar where the run is one recurring event and the private view, artist talk and closing are separate evenings.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Art Galleries, Project Spaces & Artist Cooperatives"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Art Galleries",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Exhibition Scheduling Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "A whole exhibition run as one recurring event that ends on the closing date",
            "Opening days set by day of week, with date exceptions that add or remove single dates",
            "Separate evening events for the private view, artist talk, curator tour and closing",
            "Free registration with a capacity, counted separately for each date",
            "Ticketed collector dinners and previews with QR check-in",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Followers you can email directly, within a monthly allowance counted per recipient",
            "Exhibition proposals submitted through your own page, pasted in or uploaded as a flyer",
            "Custom fields on the request form, so every proposal arrives with a portfolio link",
            "Participants, so a showing artist gets the dates on their own schedule",
            "Sub-schedules for exhibitions, talks and hire, each with its own shareable link",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for your own website",
            "Draft events that stay members-only until a show is announced",
            "Online events with the link people join on"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "gallery calendar, exhibition schedule, private view rsvp, art gallery events, artist talk booking, exhibition proposal form",
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
           For-art-galleries "Four Evenings" styles.

           CONCEPT: a gallery's calendar has a shape no other audience in
           this set has - THE SHOW IS CONTINUOUS AND THE EVENTS ARE NOT.
           A six-week hang is on the wall every open day; only a handful
           of nights are things you attend. Every other rebuilt page
           models a calendar as a list of separate nights. /for-theaters
           is the closest and is still the opposite: there, every date in
           the run IS a performance.

           THE DEVICE IS THE RUN BAR - forty cells, ten of them closed
           Mondays and Tuesdays, thirty open, four marked as evenings.
           The argument is the shape: the long thing is ONE recurring
           event that stops itself on the closing date
           (recurring_end_type = on_date), and the four marks are the
           only entries you make by hand.

           COLOUR: near-neutral, because every warm hue belongs to this
           page's own NEIGHBOURS - /for-visual-artists (32deg, sat 95%),
           /for-community-centers (17deg), /for-libraries (43deg). A
           gallery is the one audience where going quiet is the honest
           move: the white cube, where the colour is supposed to come
           from the work. Accent #2b3a45 is hue 205 at sat 23%; the
           nearest pages are /for-djs (193, sat 82%) and /for-spoken-word
           (216, sat 59%), neither a neighbour and both far in saturation.

           Measured: #2b3a45 10.56 on ground / 11.23 on card; #9fc3d4
           10.11 / 9.22 / 9.77 dark. Gradient second stops #3d5261 (7.35)
           and #c2dde9 (13.33) both clear their grounds. NEVER
           text-gray-500 - use .es-hang-muted (6.72 light / 7.30 dark).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-hang-page { background-color: #f4f3f1; color: #16181a; }
        .dark .es-hang-page { background-color: #101112; color: #eceef0; }
        .es-hang-ink { color: #16181a; }
        .dark .es-hang-ink { color: #eceef0; }
        .es-hang-muted { color: #4f565c; }
        .dark .es-hang-muted { color: #9aa2a8; }
        .es-hang-accent { color: #2b3a45; }
        .dark .es-hang-accent { color: #9fc3d4; }
        /* Always-lit accent for the band, in both colour modes. */
        .es-hang-lit { color: #9fc3d4; }

        .es-hang-grad {
            background-image: linear-gradient(100deg, #2b3a45, #3d5261);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-hang-grad,
        .es-hang-band .es-hang-grad {
            background-image: linear-gradient(100deg, #c2dde9, #9fc3d4);
        }

        /* --- Surfaces --- */
        .es-hang-card {
            background-color: #fbfaf9;
            border: 1px solid rgba(22, 24, 26, 0.12);
            border-radius: 0.5rem;
        }
        .dark .es-hang-card {
            background-color: #1a1b1d;
            border-color: rgba(236, 238, 240, 0.13);
        }
        .es-hang-sub {
            background-color: rgba(22, 24, 26, 0.045);
            border-radius: 0.3rem;
        }
        .dark .es-hang-sub { background-color: rgba(236, 238, 240, 0.05); }
        .es-hang-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-hang-hover:hover { border-color: rgba(43, 58, 69, 0.45); box-shadow: 0 10px 28px -18px rgba(22, 24, 26, 0.5); }
        .dark .es-hang-hover:hover { border-color: rgba(159, 195, 212, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The run bar ---------------------------------------------
           Forty cells: closed days recede, open days are solid, the four
           evenings stand proud of the row. The marks are STATE, never a
           plan tier - the tier pills below are a different shape and a
           different vocabulary on purpose. */
        .es-hang-panel { background-color: #2b3a45; color: #ffffff; border-radius: 0.5rem; }
        .dark .es-hang-panel { background-color: #22303a; }

        .es-hang-cell {
            flex: 1 1 0%;
            min-width: 0;
            border-radius: 1px;
        }
        .es-hang-closed { background-color: #5b7382; height: 0.55rem; }
        .es-hang-open { background-color: #c8dbe4; height: 1.1rem; }
        .es-hang-evening { background-color: #ffffff; height: 1.75rem; }
        .es-hang-cap {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            /* 0.75 composited over #2b3a45 = 5.05:1. Translucent text has to
               be measured composited, never as if it were opaque white. */
            color: rgba(255, 255, 255, 0.75);
        }
        .es-hang-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Eyebrow, chips, plan tags --- */
        .es-hang-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #2b3a45;
        }
        .dark .es-hang-tag { color: #9fc3d4; }
        .es-hang-band .es-hang-tag { color: #9fc3d4; }

        /* The chip is a wall-label plate: a hairline rule under a number. */
        .es-hang-chip {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #16181a;
        }
        .dark .es-hang-chip { color: #eceef0; }
        .es-hang-band .es-hang-chip { color: #eceef0; }
        .es-hang-chip::after {
            content: "";
            display: block;
            width: 1.6rem;
            height: 1px;
            background: #2b3a45;
        }
        .dark .es-hang-chip::after { background: #9fc3d4; }
        .es-hang-band .es-hang-chip::after { background: #9fc3d4; }

        /* Plan tiers ONLY - never reuse these for a run-bar state. */
        .es-hang-plan {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.1rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .es-hang-plan-free { border-color: rgba(22, 24, 26, 0.22); color: #4f565c; }
        .dark .es-hang-plan-free { border-color: rgba(236, 238, 240, 0.26); color: #9aa2a8; }
        .es-hang-plan-pro { border-color: rgba(43, 58, 69, 0.5); color: #2b3a45; background: rgba(43, 58, 69, 0.08); }
        .dark .es-hang-plan-pro { border-color: rgba(159, 195, 212, 0.42); color: #9fc3d4; background: rgba(159, 195, 212, 0.1); }

        /* --- Buttons --- */
        .es-hang-btn {
            background-color: #2b3a45;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-hang-btn:hover { background-color: #1e2a33; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(43, 58, 69, 0.9); }
        .es-hang-ghost {
            border: 1px solid rgba(22, 24, 26, 0.22);
            color: #16181a;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-hang-ghost:hover { border-color: rgba(43, 58, 69, 0.5); background-color: rgba(43, 58, 69, 0.06); }
        .dark .es-hang-ghost { border-color: rgba(236, 238, 240, 0.24); color: #eceef0; }
        .dark .es-hang-ghost:hover { border-color: rgba(159, 195, 212, 0.45); background-color: rgba(159, 195, 212, 0.08); }

        /* --- The dark band ------------------------------------------
           A resolvable background-color under the gradients: it is what
           paints if they fail and what a contrast audit can read. */
        .es-hang-band {
            background-color: #141517;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(43, 58, 69, 0.55), rgba(43, 58, 69, 0) 70%),
                linear-gradient(180deg, #1a1b1d, #141517);
        }

        /* --- Nothing inside the band may change between colour modes --
           The band has no .dark variant, so any descendant that HAS one
           would render differently on an identical ground. Two shared
           classes carry their own .dark rules in marketing.css and are
           invisible to a grep of this file. */
        .es-hang-band .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 238, 240, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 238, 240, 0.05) 1px, transparent 1px);
        }
        .es-hang-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-hang-band .es-claim:focus-within {
            border-color: rgba(159, 195, 212, 0.75);
            box-shadow: 0 0 0 4px rgba(159, 195, 212, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(43, 58, 69, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(159, 195, 212, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #2b3a45; }
        .dark .es-dot.is-active .es-dot-pip { background: #9fc3d4; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-hang-page a:focus-visible,
        #es-hang-page summary:focus-visible,
        #es-hang-page button:focus-visible,
        #es-hang-page input:focus-visible {
            outline: 2px solid #2b3a45;
            outline-offset: 2px;
        }
        .dark #es-hang-page a:focus-visible,
        .dark #es-hang-page summary:focus-visible,
        .dark #es-hang-page button:focus-visible,
        .dark #es-hang-page input:focus-visible {
            outline-color: #9fc3d4;
        }
        .es-hang-band a:focus-visible,
        .es-hang-band summary:focus-visible,
        .es-hang-band button:focus-visible,
        .es-hang-band input:focus-visible {
            outline-color: #9fc3d4 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-hang-btn:hover { transform: none; }
        }
    </style>

    @php
        // The run. Every count on the page is derived from these three
        // constants, so the copy and the bar cannot drift apart: the span
        // is 40 days, 30 of them open, with 4 evenings that all fall on
        // open days (asserted before this page was written).
        $runStart = new DateTimeImmutable('2026-01-28');   // Wed
        $runEnd = new DateTimeImmutable('2026-03-08');     // Sun
        $openWeekdays = [3, 4, 5, 6, 7];                   // Wed..Sun (ISO-8601)

        $evenings = [
            '2026-01-29' => ['Private view', '6-9pm', 'Free, 80 places'],
            '2026-02-07' => ['Artist talk', '3pm', 'Free, 40 places'],
            '2026-02-22' => ['Curator tour', '2pm', 'Free, 25 places'],
            '2026-03-08' => ['Closing party', '5-8pm', 'Free, no limit'],
        ];

        $runDays = [];
        for ($d = $runStart; $d <= $runEnd; $d = $d->modify('+1 day')) {
            $key = $d->format('Y-m-d');
            $isOpen = in_array((int) $d->format('N'), $openWeekdays, true);
            $runDays[] = [
                'date' => $d,
                'open' => $isOpen,
                'evening' => $isOpen && isset($evenings[$key]) ? $evenings[$key][0] : null,
            ];
        }

        $openCount = count(array_filter($runDays, fn ($d) => $d['open']));
        $eveningCount = count(array_filter($runDays, fn ($d) => $d['evening'] !== null));
        $closedCount = count($runDays) - $openCount;

        $numbers = [30 => 'Thirty', 4 => 'four'];
        $openWord = $numbers[$openCount] ?? $openCount;
        $eveningWord = $numbers[$eveningCount] ?? $eveningCount;

        $faqs = [
            [
                'q' => 'Do I have to create an event for every day of an exhibition?',
                'a' => 'No. The run is one recurring event: pick the days you open, and set it to end on the closing date. A six-week show open Wednesday to Sunday is a single entry that appears on all thirty open days, not thirty things to keep up to date. If you close for a holiday, a date exception takes that day out, and the same mechanism can add a one-off opening that falls outside your usual days.',
            ],
            [
                'q' => 'How do the private view and the artist talk fit in?',
                'a' => 'As their own events, on top of the run. A recurring event carries one start time, so anything happening at a different hour needs to be separate anyway - which is exactly right here, because each evening wants its own description, its own capacity and its own page to share. Most shows end up with three or four.',
            ],
            [
                'q' => 'Is Event Schedule free for a gallery?',
                'a' => 'The parts you use for every show are free forever: the run as a recurring event, date exceptions, separate evening events, free registration with a capacity for a private view, sub-schedules, exhibition proposals from artists, two-way calendar sync and an embeddable calendar. Selling a ticket to a collector dinner or a paid preview is on the Pro plan at '.plan_price($proMonthly).' a month, with zero platform fees on sales.',
            ],
            [
                'q' => 'Can I cap the private view without charging for it?',
                'a' => 'Yes. Set the event as free and give it a capacity. People register rather than pay, the count is kept for each date separately, and it stops taking names when the places are gone. Nothing about a private view has to involve money for you to know how many are coming.',
            ],
            [
                'q' => 'How do artists propose a show?',
                'a' => 'Through your page rather than your inbox. The default submission form takes a pasted proposal or an uploaded flyer and turns it into a request for you to review, which is free and covers ten submissions a day. If you would rather have fixed fields, switch the setting to the booking form and every proposal arrives with a date, a time and a description. On Pro you can add custom fields to either form, so nothing reaches you without a portfolio link.',
            ],
            [
                'q' => 'Will my collectors be told when a new show goes up?',
                'a' => 'Only when you tell them. People follow your gallery and give you their email with their consent, and you write and send the newsletter yourself - nothing goes out automatically. The free plan covers 10 emails a month and Pro raises it to 100, counted per recipient rather than per send, so one message to a hundred collectors uses a hundred of them.',
            ],
        ];

        $dotSections = [
            ['top', 'The run'],
            ['run', 'One event'],
            ['evenings', 'The evenings'],
            ['view', 'The private view'],
            ['collectors', 'Who hears first'],
            ['proposals', 'Proposals'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-hang-page" class="es-hang-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the run bar                                         -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the panel sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 26% 30%, rgba(43, 58, 69, 0.18), rgba(43, 58, 69, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 76% 60%, rgba(159, 195, 212, 0.15), rgba(159, 195, 212, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-hang-tag es-fade-up es-d-1 mb-5">For galleries, project spaces and cooperatives</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The show runs.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="es-hang-grad">evenings</span> do not.</span></span>
                    </h1>

                    <p class="es-hang-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        A six-week hang is on the wall every day you open. Only a handful of nights
                        are things anyone attends. So the run is one entry that stops itself on the
                        closing date, and the evenings are the {{ $eveningWord }} you add on top.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-hang-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Put the show up
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#run" class="es-hang-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how the run works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The run bar. The shape is the argument. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-hang-panel p-6 sm:p-8">
                        <p class="es-hang-cap">Now showing</p>
                        <p class="mt-2 text-xl font-bold text-white">Sarah Chen: New Works</p>
                        <p class="es-hang-num mt-1 text-sm text-white/70">
                            {{ $runStart->format('D j M') }} to {{ $runEnd->format('D j M') }} &middot; open Wed to Sun
                        </p>

                        <div class="mt-7 flex items-end gap-[2px]" role="img"
                            aria-label="{{ count($runDays) }} days of the run: {{ $openCount }} open days, {{ $closedCount }} closed, with {{ $eveningCount }} evening events marked.">
                            @foreach ($runDays as $day)
                                <span @class([
                                    'es-hang-cell',
                                    'es-hang-evening' => $day['evening'] !== null,
                                    'es-hang-open' => $day['open'] && $day['evening'] === null,
                                    'es-hang-closed' => ! $day['open'],
                                ])></span>
                            @endforeach
                        </div>

                        <div class="mt-6 grid grid-cols-3 gap-3 border-t border-white/15 pt-5">
                            <div>
                                <p class="es-hang-num text-2xl font-bold text-white">{{ $openCount }}</p>
                                <p class="es-hang-cap mt-1">Open days</p>
                            </div>
                            <div>
                                <p class="es-hang-num text-2xl font-bold text-white">1</p>
                                <p class="es-hang-cap mt-1">Recurring event</p>
                            </div>
                            <div>
                                <p class="es-hang-num text-2xl font-bold text-white">{{ $eveningCount }}</p>
                                <p class="es-hang-cap mt-1">Evenings</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-hang-muted mt-5 text-sm">
                        {{ $openWord }} open days. {{ $eveningCount }} entries you make by hand.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The run sets itself (01)                                  -->
    <!-- ============================================================ -->
    <section id="run" class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The run</p>
                    <h2 class="es-balance es-hang-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One entry, <span class="es-hang-grad">not thirty</span>.
                    </h2>
                    <p class="es-hang-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Pick the days you open and the date the show comes down. The exhibition then
                        appears on every open day between the two without you touching it again, and
                        it stops on its own.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Set the days you open', 'Wednesday to Sunday is one choice, not five. Mondays and Tuesdays never appear.'],
                            ['Set the closing date', 'The run ends on the day it ends. Nothing lingers on the calendar after the work comes off the wall.'],
                            ['Exceptions cut both ways', 'Take out the day you shut for an install, or add a bank-holiday Monday opening, without disturbing the pattern.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-hang-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-hang-ink font-semibold">{{ $t }}</span> <span class="es-hang-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-hang-plan es-hang-plan-free">Free plan</span>
                        <span class="es-hang-muted ml-2 text-sm">Recurring events, closing dates and date exceptions are all on the free plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-hang-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-hang-ink text-lg font-bold">What you fill in, once</h3>
                            <span class="es-hang-muted es-hang-num text-xs">1 event</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Exhibition', 'Sarah Chen: New Works'],
                                ['Repeats', 'Wed, Thu, Fri, Sat, Sun'],
                                ['First day', $runStart->format('D j M Y')],
                                ['Ends', 'On ' . $runEnd->format('D j M Y')],
                                ['Exceptions', '1 day out, 1 day added'],
                            ] as [$fLabel, $fValue])
                                <div class="es-hang-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-hang-muted w-24 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                    <span class="es-hang-ink es-hang-num min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-hang-muted mt-5 border-t border-[rgba(22,24,26,0.1)] pt-4 text-xs dark:border-[rgba(236,238,240,0.12)]">
                            That is the whole run. It lands on {{ $openCount }} dates and stops.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The evenings (02)                                         -->
    <!-- ============================================================ -->
    <section id="evenings" class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The evenings</p>
                <h2 class="es-balance es-hang-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The {{ $eveningCount }} nights <span class="es-hang-grad">people put in the diary</span>.
                </h2>
                <p class="es-hang-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A recurring event carries one start time, so anything at a different hour is its
                    own entry anyway. That is the right answer here: each of these wants its own
                    description, its own capacity and its own link to send.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ($evenings as $eDate => [$eName, $eTime, $eNote])
                    @php $eObj = new DateTimeImmutable($eDate); @endphp
                    <div class="es-hang-card es-hang-hover flex flex-col p-6" data-reveal>
                        <p class="es-hang-accent es-hang-num text-xs font-bold uppercase tracking-widest">{{ $eObj->format('D j M') }}</p>
                        <h3 class="es-hang-ink mt-2 text-lg font-bold">{{ $eName }}</h3>
                        <p class="es-hang-muted mt-1 text-sm">{{ $eTime }}</p>
                        <p class="es-hang-muted mt-auto pt-4 text-xs">{{ $eNote }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-hang-plan es-hang-plan-free">Free plan</span>
                <span class="es-hang-muted ml-2 text-sm">
                    Every one of them, with no cap on how many events you put up.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The private view (03)                                     -->
    <!-- ============================================================ -->
    <section id="view" class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The private view</p>
                <h2 class="es-balance es-hang-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Limited does not have to mean <span class="es-hang-grad">paid</span>.
                </h2>
                <p class="es-hang-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Nobody charges for an opening. You still need to know whether eighty people are
                    coming or three hundred, and that is a capacity, not a ticket.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-hang-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-hang-ink text-lg font-bold">The opening</h3>
                        <span class="es-hang-plan es-hang-plan-free">Free plan</span>
                    </div>
                    <p class="es-hang-muted mb-5 text-sm">When you want the count but not the money.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'People register instead of paying.',
                            'A capacity counted for each date on its own.',
                            'It stops taking names when the places are gone.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-hang-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-hang-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-hang-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-hang-ink text-lg font-bold">The collector dinner</h3>
                        <span class="es-hang-plan es-hang-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-hang-muted mb-5 text-sm">The evening that is worth charging for.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'A price and a quantity, counted per date the same way.',
                            'QR check-in, so the person on the door is not holding a printout.',
                            'Payment through your own Stripe account, with no platform fee on top.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-hang-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-hang-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Who hears first (04)                                      -->
    <!-- ============================================================ -->
    <section id="collectors" class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-hang-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-hang-ink text-lg font-bold">What a send costs you</h3>
                                <span class="es-hang-muted es-hang-num text-xs">per month</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Free plan', '10 recipients'],
                                    ['Pro plan', '100 recipients'],
                                    ['Enterprise', '1,000 recipients'],
                                ] as [$pName, $pAllow])
                                    <div class="es-hang-sub flex items-baseline justify-between gap-3 p-3.5">
                                        <span class="es-hang-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $pName }}</span>
                                        <span class="es-hang-muted es-hang-num shrink-0 text-xs">{{ $pAllow }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-hang-muted mt-5 border-t border-[rgba(22,24,26,0.1)] pt-4 text-xs dark:border-[rgba(236,238,240,0.12)]">
                                The allowance counts recipients, not sends. One message to a hundred
                                collectors uses a hundred of them.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who hears first</p>
                    <h2 class="es-balance es-hang-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Nothing goes out <span class="es-hang-grad">without you</span>.
                    </h2>
                    <p class="es-hang-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        People follow your gallery and give you their email with their consent. There
                        is no automatic announcement and no feed deciding who sees the show - when
                        the next one is worth an email, you write it and you send it.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['A list that is yours', 'Followers arrive from your own page, and you can see who they are on the followers tab.'],
                            ['Written and sent by you', 'No trigger, no automation. The announcement goes when you decide the hang is ready to be seen.'],
                            ['Know the unit before you plan', 'The allowance counts each recipient, so a list of two hundred is two sends on Pro, not two hundred.'],
                        ] as [$t, $d])
                            <div class="es-hang-card es-hang-hover p-4" data-reveal>
                                <p class="es-hang-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-hang-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Proposals (05)                                            -->
    <!-- ============================================================ -->
    <section id="proposals" class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Proposals</p>
                <h2 class="es-balance es-hang-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Artists ask through <span class="es-hang-grad">the page</span>.
                </h2>
                <p class="es-hang-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Not a submissions address you stop opening. Proposals arrive attached to your
                    schedule, and nothing appears publicly until you accept it.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-hang-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-hang-ink text-lg font-bold">Paste it or drop the flyer</h3>
                        <span class="es-hang-plan es-hang-plan-free">Free plan</span>
                    </div>
                    <p class="es-hang-muted mb-5 text-sm">The form your page starts with.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'An artist pastes their proposal text or uploads a flyer.',
                            'It is read into a request for you to review, not published.',
                            'Ten submissions a day on the free plan, fifty on Pro.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-hang-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-hang-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-hang-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-hang-ink text-lg font-bold">Or ask for exactly what you need</h3>
                        <span class="es-hang-plan es-hang-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-hang-muted mb-5 text-sm">Switch the form, then add your own fields.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'The booking form asks for a date, a time and a description.',
                            'Custom fields sit on either form, so nothing arrives without a portfolio link.',
                            'Accept, and adding the artist as a participant puts the dates on their schedule too.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-hang-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-hang-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Who it is for (06)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-hang-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Anywhere a show <span class="es-hang-grad">goes up and comes down</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Contemporary Galleries"
                    description="Six-week hangs with a private view at the front and a closing at the back, and a programme booked a year out."
                    icon-color="slate"
                    blog-slug="for-contemporary-galleries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v12H4V4zm3 3h4v6H7V7zm7 0h3v6h-3V7zM8 20h8m-4-4v4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Photography Galleries"
                    description="Editions on the wall and a talk in the middle of the run, with the capacity for each evening kept separately."
                    icon-color="sky"
                    blog-slug="for-photography-galleries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8a2 2 0 012-2h2.5l1.2-2h6.6l1.2 2H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm9 2.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Craft & Artisan Galleries"
                    description="A shop floor that is open most days and a maker demonstration once a fortnight, on one calendar."
                    icon-color="teal"
                    blog-slug="for-craft-galleries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a1 1 0 001-1v-6a6 6 0 00-12 0v6a1 1 0 001 1zm5-14V3m-3 4h6" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Pop-Up Galleries"
                    description="Three weeks in a borrowed unit. A run with a fixed closing date is exactly the shape of the lease."
                    icon-color="amber"
                    blog-slug="for-pop-up-galleries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l3-5h12l3 5M3 9h18M3 9v10a1 1 0 001 1h16a1 1 0 001-1V9M9 20v-6h6v6" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Artist Cooperatives"
                    description="Members take the wall in turn, and each of them can be added to their own show so the dates land on their schedule."
                    icon-color="emerald"
                    blog-slug="for-artist-cooperatives"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7a3 3 0 106 0 3 3 0 00-6 0zM4 21v-2a4 4 0 014-4h8a4 4 0 014 4v2M3 11a2 2 0 104 0 2 2 0 00-4 0zm14 0a2 2 0 104 0 2 2 0 00-4 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Museum Galleries"
                    description="A long run with a tour on it every other Sunday, and a curator talk that fills months before the show comes down."
                    icon-color="orange"
                    blog-slug="for-museum-galleries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-5 9 5M5 9v8m4-8v8m6-8v8m4-8v8M3 21h18" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. How it works (07, dark band)                              -->
    <!-- ============================================================ -->
    <section id="how" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-hang-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set at the install, <span class="es-hang-grad">left alone after</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Put the run up', 'One recurring event on the days you open, ending on the day the show comes down.'],
                        ['02', 'Add the evenings', 'The private view, the talk, the closing. A capacity on each, and a price only where there is one.'],
                        ['03', 'Send it once', 'Followers get the announcement when you write it. Artists send the next proposal through the same page.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-hang-lit es-hang-num mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="text-sm text-gray-400">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Key features                                              -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-hang-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A whole run as one entry, ending on the closing date" :url="marketing_url('/features/recurring-events')" icon-color="slate">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Exhibitions, talks and hire on their own strands and links" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Ask every proposal for a portfolio link before it reaches you" :url="marketing_url('/features/custom-fields')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the programme on the gallery site you already have" :url="marketing_url('/features/embed-calendar')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-hang-accent inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 10. Related audience pages                                   -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(22,24,26,0.1)] py-16 dark:border-[rgba(236,238,240,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-hang-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-visual-artists', 'Visual Artists'],
                    ['/for-libraries', 'Libraries'],
                    ['/for-community-centers', 'Community Centers'],
                    ['/for-workshop-instructors', 'Workshop Instructors'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-hang-card es-hang-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-hang-muted text-sm">Event Schedule for</div>
                            <div class="es-hang-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-hang-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-hang-accent inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- ============================================================ -->
    <!-- 11. FAQ (08)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(22,24,26,0.1)] py-20 dark:border-[rgba(236,238,240,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-hang-chip mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-hang-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-hang-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at <span class="es-hang-grad">the install</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-hang-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-hang-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-hang-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-hang-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 12. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-hang-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-hang-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put the show up <span class="es-hang-grad">and leave it there</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The run, the evenings and the proposals cost nothing. Pay only when an
                        evening is worth a ticket.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-gallery" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-hang-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Put the show up
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section dot navigation -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1a1b1d] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
