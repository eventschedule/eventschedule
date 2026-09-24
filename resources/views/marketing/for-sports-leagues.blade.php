<x-marketing-layout>
    <x-slot name="title">Sports League Schedule | Fixtures, Teams, Season Passes</x-slot>
    <x-slot name="description">One fixture list per team and one season calendar for the league. Families subscribe once, a moved match updates itself, and training is set up once.</x-slot>
    <x-slot name="breadcrumbTitle">For Sports Leagues</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Sports Leagues"
        description="A season calendar for a league or club: each team or age group as a sub-schedule with its own link, weekly training as recurring events, match days at their grounds, and a live calendar feed families subscribe to once."
        audience="Sports Leagues, Amateur Sports Clubs & Youth Sports Organizations"
        keywords="sports league schedule, fixture list, youth sports calendar, club fixtures, season pass, sports club events" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to publish a sports league schedule with Event Schedule",
        "description": "One schedule for the league, a sub-schedule for each team, and a season that mostly sets itself.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set up the teams",
                "text": "Create one schedule for the league or club, then add a sub-schedule for each team, division or age group. Each one gets its own colour and its own link to send to its players and parents."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Put the season in",
                "text": "Add weekly training as a recurring event on the nights it runs, take out the weeks the pitch is closed, then add each match day as its own event at its ground."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the gate",
                "text": "Take free sign-ups with a capacity for trials and camps on any plan. Put a price on match-day tickets or a season pass on Pro, and scan them at the gate with a phone."
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
           For-sports-leagues "The Fixture List" styles.

           CONCEPT: a league's calendar is a GRID, not a list. Every team
           has its own week, the weeks line up into a season, and the one
           thing that happens every season is that a match moves. So the
           hero device is the fixture list itself - week, date, home, away,
           ground - with one row marked postponed and re-dated, because
           that is the moment the page is really about: the parents who
           subscribed to the feed never have to be told twice.

           CLAIM DISCIPLINE. The mock is a FIXTURE LIST, never a league
           table: there is no standings calculation, no score entry, no
           referee assignment and no per-player roster anywhere in the
           app, so no column here may show points, goals or a position.
           The team dots are sub-schedule colours (Group has a color
           column), and the "moved" row is an edited event date, which
           bumps the iCal sequence so subscribed calendars pick it up.

           COLOUR: kit red. Every green is taken by a neighbour
           (/for-fitness-and-yoga #0b6b52, /for-farmers-markets #3f6212,
           /for-community-centers #0b5b52), so the page wears a shirt
           colour instead. Accent #a51d24 measures about 6.8:1 on the
           #f5f4f1 ground; #ff9a8f about 9.3:1 on #0f1011. Muted text is
           #4d5359 light (7.1) and #9ba1a7 dark (7.4). NEVER
           text-gray-500 on this ground - use .es-league-muted.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-league-page { background-color: #f5f4f1; color: #15171a; }
        .dark .es-league-page { background-color: #0f1011; color: #eceef0; }
        .es-league-ink { color: #15171a; }
        .dark .es-league-ink { color: #eceef0; }
        .es-league-muted { color: #4d5359; }
        .dark .es-league-muted { color: #9ba1a7; }
        .es-league-accent { color: #a51d24; }
        .dark .es-league-accent { color: #ff9a8f; }
        /* Always-lit accent for the dark band and the hero panel. */
        .es-league-lit { color: #ff9a8f; }

        .es-league-grad {
            background-image: linear-gradient(100deg, #a51d24, #c2410c);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-league-grad,
        .es-league-band .es-league-grad {
            background-image: linear-gradient(100deg, #ffb4a8, #ff9a8f);
        }

        /* --- Surfaces --- */
        .es-league-card {
            background-color: #fcfbf9;
            border: 1px solid rgba(21, 23, 26, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-league-card {
            background-color: #18191b;
            border-color: rgba(236, 238, 240, 0.13);
        }
        .es-league-sub {
            background-color: rgba(21, 23, 26, 0.045);
            border-radius: 0.4rem;
        }
        .dark .es-league-sub { background-color: rgba(236, 238, 240, 0.05); }
        .es-league-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-league-hover:hover { border-color: rgba(165, 29, 36, 0.45); box-shadow: 0 10px 28px -18px rgba(21, 23, 26, 0.5); }
        .dark .es-league-hover:hover { border-color: rgba(255, 154, 143, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }
        .es-league-rule { border-color: rgba(21, 23, 26, 0.1); }
        .dark .es-league-rule { border-color: rgba(236, 238, 240, 0.1); }

        /* --- The fixture board (hero device) ------------------------
           A fixed object: floodlit charcoal in both colour modes, so it
           carries no .dark variant and nothing inside it changes. */
        .es-league-board { background-color: #1b1f24; color: #ffffff; border-radius: 0.75rem; }
        .es-league-cap {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            /* 0.72 composited over #1b1f24 is about 9:1. */
            color: rgba(255, 255, 255, 0.72);
        }
        .es-league-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-league-row { border-top: 1px solid rgba(255, 255, 255, 0.09); }
        .es-league-dot { display: inline-block; width: 0.55rem; height: 0.55rem; border-radius: 999px; flex-shrink: 0; }
        .es-league-dot-u12 { background-color: #38bdf8; }
        .es-league-dot-u14 { background-color: #fbbf24; }
        .es-league-dot-sen { background-color: #ff9a8f; }
        .es-league-moved {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.05rem 0.45rem;
            font-size: 0.58rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            background-color: rgba(251, 191, 36, 0.16);
            color: #fcd34d;
        }
        .es-league-was { text-decoration: line-through; color: rgba(255, 255, 255, 0.5); }

        /* --- Eyebrow, chips, plan tags --- */
        .es-league-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #a51d24;
        }
        .dark .es-league-tag { color: #ff9a8f; }
        .es-league-band .es-league-tag { color: #ff9a8f; }

        /* The chip is a shirt number: a bold numeral on a square patch. */
        .es-league-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.5rem;
            border: 2px solid #a51d24;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.95rem;
            font-weight: 800;
            color: #a51d24;
        }
        .dark .es-league-chip { border-color: #ff9a8f; color: #ff9a8f; }
        .es-league-band .es-league-chip { border-color: #ff9a8f; color: #ff9a8f; }

        /* Plan tiers ONLY - never reuse these for a fixture state. */
        .es-league-plan {
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
        .es-league-plan-free { border-color: rgba(21, 23, 26, 0.22); color: #4d5359; }
        .dark .es-league-plan-free { border-color: rgba(236, 238, 240, 0.26); color: #9ba1a7; }
        .es-league-plan-pro { border-color: rgba(165, 29, 36, 0.5); color: #a51d24; background: rgba(165, 29, 36, 0.07); }
        .dark .es-league-plan-pro { border-color: rgba(255, 154, 143, 0.42); color: #ff9a8f; background: rgba(255, 154, 143, 0.1); }
        .es-league-plan-ent { border-color: rgba(21, 23, 26, 0.4); color: #15171a; background: rgba(21, 23, 26, 0.06); }
        .dark .es-league-plan-ent { border-color: rgba(236, 238, 240, 0.4); color: #eceef0; background: rgba(236, 238, 240, 0.08); }

        /* --- Buttons --- */
        .es-league-btn {
            background-color: #a51d24;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-league-btn:hover { background-color: #86161c; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(165, 29, 36, 0.9); }
        .es-league-ghost {
            border: 1px solid rgba(21, 23, 26, 0.22);
            color: #15171a;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-league-ghost:hover { border-color: rgba(165, 29, 36, 0.5); background-color: rgba(165, 29, 36, 0.06); }
        .dark .es-league-ghost { border-color: rgba(236, 238, 240, 0.24); color: #eceef0; }
        .dark .es-league-ghost:hover { border-color: rgba(255, 154, 143, 0.45); background-color: rgba(255, 154, 143, 0.08); }

        /* --- The dark band --- */
        .es-league-band {
            background-color: #121315;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(165, 29, 36, 0.4), rgba(165, 29, 36, 0) 70%),
                linear-gradient(180deg, #191a1d, #121315);
        }

        /* Nothing inside the band may change between colour modes: two
           shared classes carry their own .dark rules in marketing.css. */
        .es-league-band .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 238, 240, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 238, 240, 0.05) 1px, transparent 1px);
        }
        .es-league-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-league-band .es-claim:focus-within {
            border-color: rgba(255, 154, 143, 0.75);
            box-shadow: 0 0 0 4px rgba(255, 154, 143, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(165, 29, 36, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(255, 154, 143, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #a51d24; }
        .dark .es-dot.is-active .es-dot-pip { background: #ff9a8f; }

        /* Focus rings. Never set border-radius here. */
        #es-league-page a:focus-visible,
        #es-league-page summary:focus-visible,
        #es-league-page button:focus-visible,
        #es-league-page input:focus-visible {
            outline: 2px solid #a51d24;
            outline-offset: 2px;
        }
        .dark #es-league-page a:focus-visible,
        .dark #es-league-page summary:focus-visible,
        .dark #es-league-page button:focus-visible,
        .dark #es-league-page input:focus-visible {
            outline-color: #ff9a8f;
        }
        .es-league-band a:focus-visible,
        .es-league-band summary:focus-visible,
        .es-league-band button:focus-visible,
        .es-league-band input:focus-visible {
            outline-color: #ff9a8f !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-league-btn:hover { transform: none; }
        }
    </style>

    @php
        // The fixture board. Teams are sub-schedules (the dot is the
        // sub-schedule colour). One Under-14 match is postponed and
        // re-dated: that row is the reason the page exists.
        $teams = [
            'u12' => 'Under-12s',
            'u14' => 'Under-14s',
            'sen' => 'Seniors',
        ];

        $fixtures = [
            ['wk' => 6, 'team' => 'u12', 'date' => 'Sat 11 Oct', 'time' => '10:00', 'home' => 'Riverside', 'away' => 'Millbrook', 'ground' => 'Riverside Park', 'moved' => null],
            ['wk' => 6, 'team' => 'u14', 'date' => 'Sat 11 Oct', 'time' => '11:30', 'home' => 'Northgate', 'away' => 'Riverside', 'ground' => 'Northgate Rec', 'moved' => 'Wed 15 Oct'],
            ['wk' => 6, 'team' => 'sen', 'date' => 'Sun 12 Oct', 'time' => '14:00', 'home' => 'Riverside', 'away' => 'Old Quarry', 'ground' => 'Riverside Park', 'moved' => null],
            ['wk' => 7, 'team' => 'u12', 'date' => 'Sat 18 Oct', 'time' => '10:00', 'home' => 'Ashford', 'away' => 'Riverside', 'ground' => 'Ashford Fields', 'moved' => null],
            ['wk' => 7, 'team' => 'u14', 'date' => 'Sat 18 Oct', 'time' => '11:30', 'home' => 'Riverside', 'away' => 'Ashford', 'ground' => 'Riverside Park', 'moved' => null],
            ['wk' => 7, 'team' => 'sen', 'date' => 'Sun 19 Oct', 'time' => '14:00', 'home' => 'Kingsway', 'away' => 'Riverside', 'ground' => 'Kingsway Road', 'moved' => null],
        ];

        $homeCount = count(array_filter($fixtures, fn ($f) => $f['home'] === 'Riverside'));
        $awayCount = count($fixtures) - $homeCount;
        $movedCount = count(array_filter($fixtures, fn ($f) => $f['moved'] !== null));

        $faqs = [
            [
                'q' => 'Should each team have its own calendar, or should the league have one?',
                'a' => 'Both, from one schedule. Each team, division or age group is a sub-schedule with its own colour and its own link, so the Under-12 parents only ever see Under-12 dates, while the league page shows every fixture at once. Sub-schedules are free, and there is no cap on how many you add. If each club in the league already runs its own schedule for its home ground, the league can be a curator schedule that lists those clubs as sources and picks up every fixture they publish, with nobody entering a date twice.',
            ],
            [
                'q' => 'What happens when a match is postponed?',
                'a' => 'Change the date on the event and save. The fixture moves on the league page, on the team\'s sub-schedule and in every calendar that subscribed to the live feed, because a subscribed calendar re-reads the feed rather than keeping a copy. For a one-off match, the save also offers to send a short note about the change to the people who registered or bought a ticket for it, so you decide whether anyone is emailed. On the hosted service that note goes out once you connect your own email settings. Recurring training works differently: take the date out with an exception, or add one, and the pattern around it is untouched.',
            ],
            [
                'q' => 'Do parents need an account to see the fixtures?',
                'a' => 'No. The league page and every team link are public, so a parent can read them on a phone without signing in. To keep the fixtures in their own calendar they subscribe to the live feed from the sign-up panel or any event\'s Add to Calendar menu, which costs them no email address at all. A parent who leaves an email address and confirms it gets a short digest when you put new dates up, at most one every few days.',
            ],
            [
                'q' => 'Can we collect season fees through Event Schedule?',
                'a' => 'Yes, as a season pass on the Pro plan, at '.plan_price($proMonthly).' a month. A pass is a multi-use ticket on one QR code, and you can point it at a single sub-schedule, so an Under-14 season pass covers every Under-14 home game, including the ones you add later. The money goes to your own Stripe or PayPal account, or through Invoice Ninja, a payment link or cash, with no platform fee on top. It is paid once rather than billed monthly, so there is no card on file to chase.',
            ],
            [
                'q' => 'How does the carpool for away games work?',
                'a' => 'Switch carpool matching on for the schedule, which is part of Pro, and a Carpool link appears on each match. A parent with a car offers seats, others ask for one, and the driver approves each rider before any email address or phone number is shared. Each date keeps its own rides, so a full car to the away game on the 18th has nothing to do with the next one. Everyone taking part signs in and confirms they are 18 or older, so for a youth team it is the parents arranging it, not the players.',
            ],
            [
                'q' => 'Can our coaches manage their own team\'s fixtures?',
                'a' => 'On the free plan a schedule has one team member. Adding more people, with admins who run the schedule day to day and viewers who can only look and scan tickets at the gate, is part of Enterprise. For a league of separate clubs there is another way that works on every plan: each club runs its own schedule for its home ground with its own login, and the league\'s curator schedule lists those clubs as sources, so every fixture a club publishes appears on the league page without the league secretary entering it.',
            ],
            [
                'q' => 'Is Event Schedule free for a sports league?',
                'a' => 'The parts a league uses every week are free forever: sub-schedules for every team, recurring training, match days with a map to each ground, free sign-ups with a capacity for trials, two-way calendar sync, the live feed, the embeddable calendar and QR scanning at the gate. Pro adds the parts that involve money or extras: priced match-day tickets and season passes, carpool matching, sponsor logos, polls and post-match feedback. There are zero platform fees on every plan.',
            ],
            [
                'q' => 'Can we cap the numbers at trials or a holiday camp?',
                'a' => 'Yes. Make the trial a free event with a capacity and people register instead of paying. The count is kept separately for each date, and it stops taking names when the places are gone. Ask for each child\'s details individually rather than one name per booking, and each gets their own confirmation and QR code; that per-guest registration is free when nothing is being charged.',
            ],
        ];

        $dotSections = [
            ['top', 'The fixture list'],
            ['teams', 'The teams'],
            ['season', 'Training'],
            ['moved', 'Postponed'],
            ['gate', 'The gate'],
            ['away', 'Away games'],
            ['club', 'The club'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-league-page" class="es-league-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the fixture list                                    -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 24% 30%, rgba(165, 29, 36, 0.16), rgba(165, 29, 36, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 62%, rgba(255, 154, 143, 0.14), rgba(255, 154, 143, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-league-tag es-fade-up es-d-1 mb-5">Sports league schedule, for clubs too</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Rain stops play.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="es-league-grad">group chat</span> does not have to.</span></span>
                    </h1>

                    <p class="es-league-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Every team gets its own fixture list, the league gets one page for the
                        whole season, and families add it to their phone once. When a match moves,
                        you change one date and every calendar that subscribed moves with it.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-league-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Set up the season
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#teams" class="es-league-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how teams work
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The fixture board. A list of dates, never a table of points. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-league-board p-5 sm:p-7">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <div>
                                <p class="es-league-cap">Fixtures</p>
                                <p class="mt-1 text-lg font-bold text-white">Riverside Youth &amp; Seniors</p>
                            </div>
                            <p class="es-league-num text-xs text-white/70">Weeks 6 to 7</p>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1.5">
                            @foreach ($teams as $tKey => $tName)
                                <span class="inline-flex items-center gap-1.5 text-xs text-white/80">
                                    <span class="es-league-dot es-league-dot-{{ $tKey }}" aria-hidden="true"></span>{{ $tName }}
                                </span>
                            @endforeach
                        </div>

                        <ul class="mt-4" aria-label="Fixtures for weeks 6 and 7">
                            @foreach ($fixtures as $f)
                                <li class="es-league-row flex items-center gap-3 py-2.5">
                                    <span class="es-league-num w-7 shrink-0 text-[0.7rem] text-white/60">W{{ $f['wk'] }}</span>
                                    <span class="es-league-dot es-league-dot-{{ $f['team'] }}" aria-hidden="true"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-white">{{ $f['home'] }} v {{ $f['away'] }}</span>
                                        <span class="block truncate text-[0.7rem] text-white/60">{{ $f['ground'] }}</span>
                                    </span>
                                    <span class="es-league-num shrink-0 text-right text-[0.72rem] leading-tight">
                                        @if ($f['moved'])
                                            <span class="es-league-was block">{{ $f['date'] }}</span>
                                            <span class="block font-semibold text-white">{{ $f['moved'] }}</span>
                                        @else
                                            <span class="block text-white/85">{{ $f['date'] }}</span>
                                            <span class="block text-white/60">{{ $f['time'] }}</span>
                                        @endif
                                    </span>
                                    @if ($f['moved'])
                                        <span class="es-league-moved shrink-0">Moved</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4 grid grid-cols-3 gap-3 border-t border-white/15 pt-4">
                            <div>
                                <p class="es-league-num text-2xl font-bold text-white">{{ $homeCount }}</p>
                                <p class="es-league-cap mt-1">Home</p>
                            </div>
                            <div>
                                <p class="es-league-num text-2xl font-bold text-white">{{ $awayCount }}</p>
                                <p class="es-league-cap mt-1">Away</p>
                            </div>
                            <div>
                                <p class="es-league-num text-2xl font-bold text-white">{{ $movedCount }}</p>
                                <p class="es-league-cap mt-1">Date changed</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-league-muted mt-5 text-sm">
                        {{ count($teams) }} teams, one link each. One date edited, every subscribed calendar updated.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The teams (01)                                            -->
    <!-- ============================================================ -->
    <section id="teams" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-league-chip mb-6" data-reveal aria-hidden="true">01</div>
                    <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The teams</p>
                    <h2 class="es-balance es-league-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One link per team. <span class="es-league-grad">One page for the lot</span>.
                    </h2>
                    <p class="es-league-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A parent with a child in the Under-12s does not want to scroll past the
                        Seniors' Sunday fixtures to find Saturday's kick-off. Put each team on its
                        own sub-schedule and they never have to: their link shows their team, and
                        the league page still shows everything.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['A colour for each team', 'The dot on every fixture tells you whose match it is before you read a word of it.'],
                            ['A link for each team', 'Send the Under-14 link to the Under-14 parents, and pin the whole-league page on the club website.'],
                            ['No limit on how many', 'Split by age group, by division, by men\'s and women\'s sides, or all three. Sub-schedules cost nothing.'],
                            ['Or pull clubs in as sources', 'Where each club already runs its own schedule for its ground, a league curator schedule lists them as sources and every fixture they publish appears on the league page.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-league-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-league-ink font-semibold">{{ $t }}</span> <span class="es-league-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-league-plan es-league-plan-free">Free plan</span>
                        <span class="es-league-muted ml-2 text-sm">Sub-schedules and curator sources are both on the free plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-league-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-league-ink text-lg font-bold">The links you send out</h3>
                            <span class="es-league-muted es-league-num text-xs">{{ count($teams) + 1 }} links</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['sen', 'Whole league', 'riverside'],
                                ['u12', 'Under-12s', 'riverside / under-12s'],
                                ['u14', 'Under-14s', 'riverside / under-14s'],
                                ['sen', 'Seniors', 'riverside / seniors'],
                            ] as $i => [$lDot, $lName, $lPath])
                                <div class="es-league-sub flex items-center gap-3 p-3.5">
                                    @if ($i === 0)
                                        <svg aria-hidden="true" class="es-league-accent h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                                    @else
                                        <span class="es-league-dot es-league-dot-{{ $lDot }}" aria-hidden="true"></span>
                                    @endif
                                    <span class="es-league-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $lName }}</span>
                                    <span class="es-league-muted es-league-num shrink-0 text-xs">{{ $lPath }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-league-muted es-league-rule mt-5 border-t pt-4 text-xs">
                            Every team link is a filtered view of the same schedule, so a fixture is
                            entered once and shows in both places.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Training sets itself (02)                                 -->
    <!-- ============================================================ -->
    <section id="season" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-league-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-league-ink text-lg font-bold">Under-12 training</h3>
                                <span class="es-league-muted es-league-num text-xs">1 event</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Repeats', 'Tue and Thu'],
                                    ['Time', '17:30 to 18:45'],
                                    ['Where', 'Riverside Park, pitch 2'],
                                    ['Taken out', 'Half-term week, 2 dates'],
                                    ['Added', 'Sat 1 Nov, pre-cup session'],
                                ] as [$fLabel, $fValue])
                                    <div class="es-league-sub flex items-baseline justify-between gap-3 p-3.5">
                                        <span class="es-league-muted w-24 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                        <span class="es-league-ink es-league-num min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-league-muted es-league-rule mt-5 border-t pt-4 text-xs">
                                Filled in once in September. It runs to the end of the season without
                                anyone touching it again.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-league-chip mb-6" data-reveal aria-hidden="true">02</div>
                    <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Training</p>
                    <h2 class="es-balance es-league-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Tuesday and Thursday, <span class="es-league-grad">all season</span>.
                    </h2>
                    <p class="es-league-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Training is the same two nights every week, which is exactly what a
                        recurring event is for. Pick the days, pick the time, and it appears on
                        every one of them. The weeks it does not happen are exceptions, not
                        deletions.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Any rhythm a club keeps', 'Weekly on chosen days, every other week, the second Saturday of the month or once a year for the club dinner.'],
                            ['Closed pitch, one click', 'Take a single date out for a waterlogged ground or a school holiday and the rest of the pattern stays exactly as it was.'],
                            ['An extra session', 'Add a one-off date to the pattern before a cup game without creating a separate event for it.'],
                            ['On the coach\'s own calendar', 'Two-way sync with Google, Outlook or any CalDAV calendar, so the sessions sit next to the rest of their week.'],
                        ] as [$t, $d])
                            <div class="es-league-card es-league-hover p-4" data-reveal>
                                <p class="es-league-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-league-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-7" data-reveal>
                        <span class="es-league-plan es-league-plan-free">Free plan</span>
                        <span class="es-league-muted ml-2 text-sm">Recurring events, date exceptions and calendar sync are all free.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. When a match moves (03)                                   -->
    <!-- ============================================================ -->
    <section id="moved" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-league-chip mb-6" data-reveal aria-hidden="true">03</div>
                <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Postponed</p>
                <h2 class="es-balance es-league-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Change it once. <span class="es-league-grad">Tell nobody twice</span>.
                </h2>
                <p class="es-league-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Friday night, the ground is under water, and Saturday's match is off. The old
                    way is a message in four group chats and a dozen parents who still turn up.
                    Here it is one edited date.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="es-league-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-league-ink text-lg font-bold">The pages</h3>
                        <span class="es-league-plan es-league-plan-free">Free plan</span>
                    </div>
                    <p class="es-league-muted mb-5 text-sm">What anyone with the link sees.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'The match moves on the league page and the team link at the same moment.',
                            'The embedded calendar on the club website moves with it, because it is the same schedule.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-league-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-league-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-league-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-league-ink text-lg font-bold">Their calendars</h3>
                        <span class="es-league-plan es-league-plan-free">Free plan</span>
                    </div>
                    <p class="es-league-muted mb-5 text-sm">What a family who subscribed sees.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'A subscribed calendar re-reads the live feed, so the old date disappears from their phone on its own.',
                            'Subscribing needs no account and no email address: one tap from the Add to Calendar menu.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-league-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-league-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-league-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-league-ink text-lg font-bold">The people coming</h3>
                        <span class="es-league-plan es-league-plan-free">Your call</span>
                    </div>
                    <p class="es-league-muted mb-5 text-sm">When a one-off match changes date.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Saving the new date offers to send a short note to the people who registered or bought a ticket, sent from your own email settings on the hosted service.',
                            'You choose whether it goes, and can add a line of your own to say why.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-league-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-league-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The gate (04)                                             -->
    <!-- ============================================================ -->
    <section id="gate" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-league-chip mb-6" data-reveal aria-hidden="true">04</div>
                <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gate</p>
                <h2 class="es-balance es-league-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Count them in, <span class="es-league-grad">charge where it counts</span>.
                </h2>
                <p class="es-league-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Most of what a club opens up is free to attend: trials, taster sessions, the
                    summer camp. You still need to know how many are coming. The season fee and
                    the cup final are the parts with a price.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-league-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-league-ink text-lg font-bold">Trials, tryouts and camps</h3>
                        <span class="es-league-plan es-league-plan-free">Free plan</span>
                    </div>
                    <p class="es-league-muted mb-5 text-sm">When you need names and a number, not money.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Free registration with a capacity, counted separately for each date.',
                            'It stops taking names when the places are gone, and a waitlist can hold the next ones in line.',
                            'Ask for each child individually, so every one gets their own confirmation and QR code.',
                            'Scan the codes at the gate with a phone to see who actually turned up.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-league-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-league-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="es-league-muted es-league-rule mt-auto border-t pt-4 text-xs">No limit on how many free sign-ups you take in a month.</p>
                </div>

                <div class="es-league-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-league-ink text-lg font-bold">Season passes and match days</h3>
                        <span class="es-league-plan es-league-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-league-muted mb-5 text-sm">When the money is part of the fixture.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'A season pass on one QR code, pointed at a team\'s sub-schedule so it covers every home game, including ones added later.',
                            'Match-day tickets with separate prices for adults, juniors and concessions.',
                            'Paid into your own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash, with no platform fee on top.',
                            'The live check-in dashboard shows the gate count filling up as the codes are scanned.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-league-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-league-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="es-league-muted es-league-rule mt-auto border-t pt-4 text-xs">Pro is {{ plan_price($proMonthly) }} a month. Zero platform fees on every plan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Away games (05)                                           -->
    <!-- ============================================================ -->
    <section id="away" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-league-chip mb-6" data-reveal aria-hidden="true">05</div>
                    <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Away games</p>
                    <h2 class="es-balance es-league-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Where is it, and <span class="es-league-grad">who has a seat</span>?
                    </h2>
                    <p class="es-league-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        The two questions every away fixture raises. The first is answered by the
                        ground on the event, with a map to it. The second is the carpool: families
                        with a spare seat offer it on the match page, and the ones without ask.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['A map to every ground', 'Each fixture carries its venue, so the directions are on the same page as the kick-off time.'],
                            ['Seats offered on the match', 'A driver says where they are leaving from, which way they are going and how many seats they have.'],
                            ['The driver says yes first', 'Nobody\'s email address or phone number is shared until the driver approves the rider.'],
                            ['Each date keeps its own rides', 'A full car to week 7 has nothing to do with week 8.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-league-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-league-ink font-semibold">{{ $t }}</span> <span class="es-league-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-league-plan es-league-plan-pro">Pro plan</span>
                        <span class="es-league-muted ml-2 text-sm">Carpool matching is Pro. Venue maps are free.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-league-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-league-ink text-lg font-bold">Ashford v Riverside, Sat 18 Oct</h3>
                            <span class="es-league-muted es-league-num text-xs">Rides</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['From Riverside', 'To the game', '2 of 3 seats left'],
                                ['From Millbrook', 'Round trip', '1 of 4 seats left'],
                                ['From the clubhouse', 'To the game', 'Full'],
                            ] as [$rFrom, $rDir, $rSeats])
                                <div class="es-league-sub flex items-center justify-between gap-3 p-3.5">
                                    <span class="min-w-0 flex-1">
                                        <span class="es-league-ink block truncate text-sm font-semibold">{{ $rFrom }}</span>
                                        <span class="es-league-muted block text-xs">{{ $rDir }}</span>
                                    </span>
                                    <span class="es-league-accent es-league-num shrink-0 text-xs font-semibold">{{ $rSeats }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-league-muted es-league-rule mt-5 border-t pt-4 text-xs">
                            Taking part needs a signed-in account and a one-time confirmation of being
                            18 or over, so on a youth team it is the parents who arrange the lifts.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The club around the fixtures (06)                         -->
    <!-- ============================================================ -->
    <section id="club" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-league-chip mb-6" data-reveal aria-hidden="true">06</div>
                <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The club</p>
                <h2 class="es-balance es-league-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Everything that happens <span class="es-league-grad">off the pitch</span>.
                </h2>
                <p class="es-league-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A club is more than its fixtures: the people who pay for the kit, the
                    end-of-season social, the photos from the final and the members who want to
                    hear about all of it.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ([
                    ['Sponsors on the page', 'The shirt sponsor and the local businesses behind the season, as a tiered logo wall on the league page.', 'Pro plan', 'pro'],
                    ['Pick the social date', 'Put a poll on the end-of-season event and let members vote on the night. Voting needs a signed-in account.', 'Pro plan', 'pro'],
                    ['Photos from the final', 'Parents and fans send in photos from match day and nothing appears until you approve it. 25 photos free, unlimited on Pro.', 'Free plan', 'free'],
                    ['How did the camp go?', 'Ask attendees for a star rating and a comment after a camp or a tournament, while it is still fresh.', 'Pro plan', 'pro'],
                ] as [$cTitle, $cBody, $cPlan, $cKey])
                    <div class="es-league-card es-league-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-league-ink text-lg font-bold">{{ $cTitle }}</h3>
                        <p class="es-league-muted mt-2 text-sm">{{ $cBody }}</p>
                        <p class="mt-auto pt-5">
                            <span class="es-league-plan es-league-plan-{{ $cKey }}">{{ $cPlan }}</span>
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <div class="es-league-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-league-ink text-lg font-bold">Who hears about new dates</h3>
                        <span class="es-league-plan es-league-plan-free">Free plan</span>
                    </div>
                    <p class="es-league-muted mb-4 text-sm">Two lists, and they work differently.</p>
                    <p class="es-league-muted text-sm leading-relaxed">
                        Parents and players who leave their email on the league page and confirm it
                        are sent a short digest when you publish new fixtures, at most one every few
                        days, and it does not count against your newsletter allowance. Anything more
                        than the dates, like the kit order deadline or the awards night, is a
                        newsletter you write and send yourself.
                    </p>
                </div>

                <div class="es-league-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-league-ink text-lg font-bold">What a newsletter costs you</h3>
                        <span class="es-league-muted es-league-num text-xs">per month</span>
                    </div>
                    <div class="space-y-2">
                        @foreach ([
                            ['Free plan', '10 recipients'],
                            ['Pro plan', '100 recipients'],
                            ['Enterprise', '1,000 recipients'],
                        ] as [$pName, $pAllow])
                            <div class="es-league-sub flex items-baseline justify-between gap-3 p-3">
                                <span class="es-league-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $pName }}</span>
                                <span class="es-league-muted es-league-num shrink-0 text-xs">{{ $pAllow }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-league-muted mt-4 text-xs">Counted per recipient: one email to eighty families uses eighty.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Who it is for (07)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-league-chip mb-6" data-reveal aria-hidden="true">07</div>
                <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-league-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Anyone with a season <span class="es-league-grad">to keep straight</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                <x-sub-audience-card
                    name="Youth Sports Leagues"
                    description="Training nights and match days per age group, a feed parents add to their phone once, and rides to the away game sorted between families."
                    icon-color="sky"
                    blog-slug="for-youth-sports-leagues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18zm0-13l3.5 2.5-1.3 4h-4.4l-1.3-4L12 8z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Adult Rec Leagues"
                    description="Weekly games at the same court, a season fee as a pass, and a newsletter to players when the playoff dates are set."
                    icon-color="amber"
                    blog-slug="for-adult-rec-leagues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V7a1 1 0 00-1-1H5a1 1 0 00-1 1v13a1 1 0 001 1zm4-6h2m2 0h2" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Amateur Sports Clubs"
                    description="Home fixtures with tickets at the gate, a social night after the final and a club page members follow for every date."
                    icon-color="emerald"
                    blog-slug="for-amateur-sports-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-3 8.5-7 10-4-1.5-7-5.5-7-10V6l7-3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Running & Cycling Clubs"
                    description="Weekly group runs and rides, race-day sign-ups with a field limit, and a route change pushed to every subscriber."
                    icon-color="teal"
                    blog-slug="for-running-cycling-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 18a3 3 0 100-6 3 3 0 000 6zm14 0a3 3 0 100-6 3 3 0 000 6zM5 15l4-7h5l5 7M9 8l3 7h2" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. How it works (08, dark band)                              -->
    <!-- ============================================================ -->
    <section id="how" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-league-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-league-chip mb-6" data-reveal aria-hidden="true">08</div>
                    <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set up in preseason, <span class="es-league-grad">left alone till the final</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Set up the teams', 'One schedule for the league or club, and a sub-schedule for each team, division or age group, each with its own colour and link.'],
                        ['02', 'Put the season in', 'Training as a recurring event with the closed weeks taken out, and every match day as its own event at its ground.'],
                        ['03', 'Send the links once', 'Families subscribe to their team\'s feed, confirmed subscribers get new fixtures in a digest, and the gate scans whatever you issued.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-league-lit es-league-num mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="text-sm text-gray-400">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Key features                                             -->
    <!-- ============================================================ -->
    <section class="es-league-rule scroll-mt-24 border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-league-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="A link and a colour for every team, division and age group" :url="marketing_url('/features/sub-schedules')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Training set once for the season, with the closed weeks taken out" :url="marketing_url('/features/recurring-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Passes & Subscriptions" description="A season pass on one QR code that covers a team's home games, on the Pro plan" :url="marketing_url('/features/passes')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Carpool" description="Seats to the away game, with the driver approving every rider, on the Pro plan" :url="marketing_url('/features/carpool')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13M5 13h14M5 13v4h2m12-4v4h-2M7 17a1 1 0 102 0 1 1 0 00-2 0zm8 0a1 1 0 102 0 1 1 0 00-2 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Calendar Sync" description="Two-way Google, Outlook and CalDAV sync for the coaches" :url="marketing_url('/features/calendar-sync')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-league-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 11. Related audience pages                                   -->
    <!-- ============================================================ -->
    <section class="es-league-rule border-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-league-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-schools', 'Schools'],
                    ['/for-fitness-and-yoga', 'Fitness & Yoga'],
                    ['/for-community-centers', 'Community Centers'],
                    ['/for-meetup-groups', 'Meetup Groups'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-league-card es-league-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-league-muted text-sm">Event Schedule for</div>
                            <div class="es-league-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-league-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-league-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 12. FAQ (09)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="es-league-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-league-chip mb-6" data-reveal aria-hidden="true">09</div>
                <p class="es-league-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-league-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at <span class="es-league-grad">the committee meeting</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-league-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-league-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-league-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-league-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 13. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-league-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-league-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Publish the season <span class="es-league-grad">before the first whistle</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Teams, training, match days, trials with a capacity and a feed every family
                        can subscribe to all cost nothing. Season passes, gate tickets and the
                        carpool are the parts that need Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-league" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-league-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Set up the season
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#18191b] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
