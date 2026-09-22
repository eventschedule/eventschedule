<x-marketing-layout>
    <x-slot name="title">Church Event Calendar | Services, Groups and Sign-Ups</x-slot>
    <x-slot name="description">Set Sunday and every weekly group once, give youth, music and community their own links, and take free sign-ups with a cap. Free forever for churches.</x-slot>
    <x-slot name="breadcrumbTitle">For Churches</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Churches",
        "description": "A church event calendar where services and weekly groups are set once as recurring events, each ministry has its own link, and special services take free sign-ups with a capacity.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Churches, Parishes, Ministries & Congregations"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Churches",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Church Event Calendar Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly services and groups as recurring events: weekly on chosen days, every few weeks, monthly by weekday or yearly",
            "Date exceptions that take out a single week or add a one-off date",
            "Sub-schedules for worship, groups, youth, music and community work, each with its own link",
            "Free registration with a capacity, counted separately for each date",
            "Per-guest names on a free sign-up, so a family of five is five places",
            "Paid tickets for a weekend away, a concert or a fundraising dinner on the Pro plan, with zero platform fees",
            "Installment payments for a camp or trip, on the Pro plan through Stripe",
            "QR check-in at the door on any phone",
            "A live calendar feed members subscribe to once",
            "A short digest of new dates to confirmed email subscribers, at most one every 72 hours",
            "Newsletters you write and send to the congregation",
            "An embeddable calendar for the church website and a downloadable QR code for the notice board",
            "Event names and descriptions translated into one other language",
            "Hall-hire requests from outside groups that stay pending until you accept them",
            "One bookable appointment type with weekly hours",
            "Two-way Google, Outlook and CalDAV calendar sync"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "church event calendar, church calendar software, parish calendar, ministry schedule, church events sign up, church website calendar",
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
        "name": "How to put a church event calendar online with Event Schedule",
        "description": "Set the weekly pattern once, split it into ministries, then add the special services on top.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the regular week",
                "text": "Add each service and weekly group as a recurring event on the days it meets, with a date exception for any week it moves."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Give each ministry a link",
                "text": "Put worship, groups, youth, music and community work into sub-schedules, so a parent can bookmark youth nights without the rest."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Add the big dates",
                "text": "Christmas, Easter and the weekend away go on top as their own events, with a free sign-up and a capacity, or a ticket price on Pro."
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
           For-churches "The Week Around Sunday" styles.

           CONCEPT: a church's problem is not Sunday. Everybody knows when
           the service is. The problem is the other six days - the prayer
           group, the choir, youth night, the toddler group in the hall and
           the food bank on Saturday - which live on a notice sheet that only
           reaches the people who were there last week. So the hero is the
           WEEK, seven columns, with Sunday as one column among seven and
           the rest filled in. Every mark is one recurring event, set once.

           THE DEVICE IS THE WEEK TABLE. Its data drives the counts in the
           copy (recurring events, strands, days in use), so the words and
           the picture cannot drift apart.

           COLOUR: oxblood, hue ~357, the colour of a hymn-book binding or a
           cassock rather than a brand red. The warm neighbours are
           /for-community-centers (17deg) and /for-libraries (43deg); this
           sits the other side of red from both, deep and desaturated.
           NEVER a pink: the dark-mode accent is a warm salmon (#f0a196,
           hue 6) chosen for contrast on the near-black ground, not a rose.

           Contrast: #7d2a2e on #f6f3ef is about 9:1; #f0a196 on #120f0e is
           about 9.5:1. Muted text .es-nave-muted (#5b504a / #a99e97) clears
           6.5:1 on both grounds. Never text-gray-500 on these grounds.

           CLAIM DISCIPLINE. No rotas: Event Schedule has no volunteer rota
           or shift-assignment feature, so nothing here promises one. No
           giving, no member database, no SMS. Team members beyond one are
           Enterprise; Internal and Unlisted events are Enterprise; Draft is
           free. A recurring event has one start time, so a 9:30 and an
           11:00 service are two entries, and the page says so.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-nave-page { background-color: #f6f3ef; color: #1c1614; }
        .dark .es-nave-page { background-color: #120f0e; color: #efe9e5; }
        .es-nave-ink { color: #1c1614; }
        .dark .es-nave-ink { color: #efe9e5; }
        .es-nave-muted { color: #5b504a; }
        .dark .es-nave-muted { color: #a99e97; }
        .es-nave-accent { color: #7d2a2e; }
        .dark .es-nave-accent { color: #f0a196; }
        /* Always-lit accent for the band and the week panel, in both colour modes. */
        .es-nave-lit { color: #f0a196; }

        .es-nave-grad {
            background-image: linear-gradient(100deg, #7d2a2e, #9c3a31);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-nave-grad,
        .es-nave-band .es-nave-grad {
            background-image: linear-gradient(100deg, #f6c3b8, #f0a196);
        }

        /* --- Surfaces --- */
        .es-nave-card {
            background-color: #fcfaf8;
            border: 1px solid rgba(28, 22, 20, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-nave-card {
            background-color: #1b1716;
            border-color: rgba(239, 233, 229, 0.13);
        }
        .es-nave-sub {
            background-color: rgba(28, 22, 20, 0.045);
            border-radius: 0.45rem;
        }
        .dark .es-nave-sub { background-color: rgba(239, 233, 229, 0.05); }
        .es-nave-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-nave-hover:hover { border-color: rgba(125, 42, 46, 0.45); box-shadow: 0 10px 28px -18px rgba(28, 22, 20, 0.5); }
        .dark .es-nave-hover:hover { border-color: rgba(240, 161, 150, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }
        .es-nave-rule { border-color: rgba(28, 22, 20, 0.1); }
        .dark .es-nave-rule { border-color: rgba(239, 233, 229, 0.1); }

        /* --- The week panel ---------------------------------------
           A fixed object: it has no .dark variant and nothing inside it
           may have one, so it renders the same in both colour modes. */
        .es-nave-panel { background-color: #2a1416; color: #ffffff; border-radius: 0.9rem; }
        .es-nave-day {
            display: flex;
            min-width: 0;
            flex: 1 1 0%;
            flex-direction: column;
            gap: 0.3rem;
            border-radius: 0.4rem;
            background-color: rgba(255, 255, 255, 0.05);
            padding: 0.45rem 0.3rem 0.55rem;
        }
        .es-nave-day-sun { background-color: rgba(240, 161, 150, 0.16); box-shadow: inset 0 0 0 1px rgba(240, 161, 150, 0.35); }
        .es-nave-mark {
            display: block;
            height: 0.55rem;
            border-radius: 2px;
        }
        .es-nave-cap {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            /* 0.75 composited over #2a1416 is about 9:1. */
            color: rgba(255, 255, 255, 0.75);
        }
        .es-nave-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* Strand colours: the same five on the panel and in the strands
           section, so a colour means one ministry everywhere on the page.
           Measured against #2a1416 (panel) for the marks. */
        .es-nave-s-worship { background-color: #f0a196; }
        .es-nave-s-groups { background-color: #e6b85c; }
        .es-nave-s-youth { background-color: #8ccf7e; }
        .es-nave-s-music { background-color: #7fc4c9; }
        .es-nave-s-community { background-color: #c9c1bb; }

        /* --- Eyebrow, chips, plan tags --- */
        .es-nave-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #7d2a2e;
        }
        .dark .es-nave-tag { color: #f0a196; }
        .es-nave-band .es-nave-tag { color: #f0a196; }

        /* The chip is a hymn-board number: a figure on a short plate. */
        .es-nave-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.4rem;
            border-radius: 0.3rem;
            padding: 0.25rem 0.55rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #fcfaf8;
            background-color: #7d2a2e;
        }
        .dark .es-nave-chip { color: #120f0e; background-color: #f0a196; }
        .es-nave-band .es-nave-chip { color: #120f0e; background-color: #f0a196; }

        /* Plan tiers ONLY - never reuse these for a strand or a state. */
        .es-nave-plan {
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
        .es-nave-plan-free { border-color: rgba(28, 22, 20, 0.22); color: #5b504a; }
        .dark .es-nave-plan-free { border-color: rgba(239, 233, 229, 0.26); color: #a99e97; }
        .es-nave-plan-pro { border-color: rgba(125, 42, 46, 0.5); color: #7d2a2e; background: rgba(125, 42, 46, 0.08); }
        .dark .es-nave-plan-pro { border-color: rgba(240, 161, 150, 0.42); color: #f0a196; background: rgba(240, 161, 150, 0.1); }
        .es-nave-plan-ent { border-color: rgba(28, 22, 20, 0.4); color: #1c1614; background: rgba(28, 22, 20, 0.06); }
        .dark .es-nave-plan-ent { border-color: rgba(239, 233, 229, 0.4); color: #efe9e5; background: rgba(239, 233, 229, 0.08); }

        /* --- Buttons --- */
        .es-nave-btn {
            background-color: #7d2a2e;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-nave-btn:hover { background-color: #651f23; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(125, 42, 46, 0.9); }
        .es-nave-ghost {
            border: 1px solid rgba(28, 22, 20, 0.22);
            color: #1c1614;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-nave-ghost:hover { border-color: rgba(125, 42, 46, 0.5); background-color: rgba(125, 42, 46, 0.06); }
        .dark .es-nave-ghost { border-color: rgba(239, 233, 229, 0.24); color: #efe9e5; }
        .dark .es-nave-ghost:hover { border-color: rgba(240, 161, 150, 0.45); background-color: rgba(240, 161, 150, 0.08); }

        /* --- The dark band ------------------------------------------
           A resolvable background-color under the gradients: it is what
           paints if they fail and what a contrast audit can read. */
        .es-nave-band {
            background-color: #151110;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(125, 42, 46, 0.5), rgba(125, 42, 46, 0) 70%),
                linear-gradient(180deg, #1b1716, #151110);
        }

        /* --- Nothing inside the band may change between colour modes --
           Two shared classes carry their own .dark rules in marketing.css. */
        .es-nave-band .grid-overlay {
            background-image:
                linear-gradient(rgba(239, 233, 229, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(239, 233, 229, 0.05) 1px, transparent 1px);
        }
        .es-nave-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-nave-band .es-claim:focus-within {
            border-color: rgba(240, 161, 150, 0.75);
            box-shadow: 0 0 0 4px rgba(240, 161, 150, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(125, 42, 46, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(240, 161, 150, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #7d2a2e; }
        .dark .es-dot.is-active .es-dot-pip { background: #f0a196; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-nave-page a:focus-visible,
        #es-nave-page summary:focus-visible,
        #es-nave-page button:focus-visible,
        #es-nave-page input:focus-visible {
            outline: 2px solid #7d2a2e;
            outline-offset: 2px;
        }
        .dark #es-nave-page a:focus-visible,
        .dark #es-nave-page summary:focus-visible,
        .dark #es-nave-page button:focus-visible,
        .dark #es-nave-page input:focus-visible {
            outline-color: #f0a196;
        }
        .es-nave-band a:focus-visible,
        .es-nave-band summary:focus-visible,
        .es-nave-band button:focus-visible,
        .es-nave-band input:focus-visible {
            outline-color: #f0a196 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-nave-btn:hover { transform: none; }
        }
    </style>

    @php
        // The week. Every count in the hero is derived from this list, so the
        // copy and the table cannot drift apart. Day is ISO-8601 (1 = Monday).
        $strands = [
            'worship' => 'Worship',
            'groups' => 'Groups',
            'youth' => 'Youth & children',
            'music' => 'Music',
            'community' => 'Community',
        ];

        $week = [
            ['Toddler group', 'community', 1, '10am'],
            ['Prayer group', 'groups', 2, '7:30pm'],
            ['Midweek communion', 'worship', 3, '10:30am'],
            ['Choir rehearsal', 'music', 4, '7pm'],
            ['Bible study', 'groups', 4, '7:45pm'],
            ['Youth night', 'youth', 5, '7pm'],
            ['Food bank', 'community', 6, '10am'],
            ['Morning worship', 'worship', 7, '9:30am'],
            ['Evening service', 'worship', 7, '6pm'],
        ];

        $dayNames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $byDay = [];
        foreach ($dayNames as $n => $label) {
            $byDay[$n] = array_values(array_filter($week, fn ($w) => $w[2] === $n));
        }

        $recurringCount = count($week);
        $strandCount = count(array_unique(array_column($week, 1)));
        $sundayCount = count($byDay[7]);
        $otherDays = $recurringCount - $sundayCount;

        $faqs = [
            [
                'q' => 'Is Event Schedule free for a church?',
                'a' => 'The parts a church uses every week are free forever: the public calendar and its link, recurring services and groups with date exceptions, sub-schedules for each ministry, free sign-ups with a capacity, the calendar feed members subscribe to, the embeddable calendar for your website, email sign-ups and two-way calendar sync. Putting a price on a ticket, for a weekend away or a fundraising concert, is what needs Pro, at '.plan_price($proMonthly).' a month, and there are zero platform fees on those sales.',
            ],
            [
                'q' => 'We have two services on a Sunday morning. Is that one entry or two?',
                'a' => 'Two. A recurring event carries one start time, so the 9:30 and the 11:00 are each set once as their own weekly event. That is usually what you want anyway: they tend to have different styles, different leaders and a different crowd, and each can have its own description and its own link.',
            ],
            [
                'q' => 'Can a parent see only the youth events?',
                'a' => 'Yes. Put youth and children\'s work in its own sub-schedule and it gets its own link, which shows only those dates. Send that link to the youth parents, and send the music link to the choir. The full calendar still shows everything for anyone who wants the whole picture.',
            ],
            [
                'q' => 'Some of our members do not use apps. Does this still work for them?',
                'a' => 'There is nothing to install. The calendar is an ordinary web page, so it works in any browser and on the church website if you embed it. For the notice board, download the schedule\'s QR code and print it. Members who want the dates in their own phone can subscribe to the live calendar feed once, and anyone can leave an email address and confirm it to be sent a short digest of new dates, at most one every 72 hours.',
            ],
            [
                'q' => 'Can the church office, the youth leader and the music director all edit the calendar?',
                'a' => 'The free plan and Pro include one team member per schedule. Adding several, with an admin level for the people who run the calendar and a read-only viewer level that can still scan tickets at the door, is part of Enterprise. A multi-site church can also give each campus its own schedule, each with its own team.',
            ],
            [
                'q' => 'Can we keep some events off the public calendar?',
                'a' => 'Save an event as a Draft and it stays members-only until you publish it, which is on every plan and suits a date that is not confirmed yet. Events that should never be public, such as a leaders\' meeting, or an Unlisted event that only people with the link (and an optional password) can open, are Enterprise options.',
            ],
            [
                'q' => 'How do people pay for the church weekend away?',
                'a' => 'Put a price on the ticket, which needs Pro, and take payment into the church\'s own Stripe or PayPal account, by an Invoice Ninja invoice, a payment link or cash. On Stripe you can let families spread the cost over monthly installments. Event Schedule takes no platform fee, and if someone has to drop out you can refund them from the Sales page, in full or in part.',
            ],
            [
                'q' => 'Our congregation speaks two languages. Can the calendar show both?',
                'a' => 'Each schedule is written in one language, and you can nominate one other language to translate into. Event names and descriptions are then translated by AI, and visitors get a language switch on the public page. That is free. It is one target language at a time, so a church with services in three languages might run a second schedule for the third.',
            ],
        ];

        $dotSections = [
            ['top', 'The week'],
            ['pattern', 'Set once'],
            ['strands', 'Ministries'],
            ['seasons', 'The big dates'],
            ['reach', 'Reaching people'],
            ['requests', 'The hall'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-nave-page" class="es-nave-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the week                                            -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 24% 32%, rgba(125, 42, 46, 0.2), rgba(125, 42, 46, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 62%, rgba(240, 161, 150, 0.16), rgba(240, 161, 150, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-nave-tag es-fade-up es-d-1 mb-5">Church event calendar, for every congregation</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Sunday runs itself.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">The <span class="es-nave-grad">week</span> needs a calendar.</span></span>
                    </h1>

                    <p class="es-nave-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Everyone knows when the service is. It is the prayer group, the choir, youth
                        night and the food bank that people miss, because they live on a notice sheet.
                        Set the whole week once, give each ministry its own link, and put the one-off
                        dates on top.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-nave-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Set up your church calendar
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#pattern" class="es-nave-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how the week works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The week table. Sunday is one column of seven. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-nave-panel p-5 sm:p-7">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="es-nave-cap">A normal week at St Anne's</p>
                            <p class="es-nave-cap">Set once</p>
                        </div>

                        <div class="mt-5 flex gap-1.5 sm:gap-2" role="img"
                            aria-label="{{ $recurringCount }} recurring events across the week: {{ $sundayCount }} on Sunday and {{ $otherDays }} on the other six days, in {{ $strandCount }} ministries.">
                            @foreach ($dayNames as $n => $label)
                                <div @class(['es-nave-day', 'es-nave-day-sun' => $n === 7])>
                                    <span class="es-nave-cap text-center">{{ $label }}</span>
                                    <div class="mt-1 flex min-h-[3.2rem] flex-col gap-1">
                                        @foreach ($byDay[$n] as $item)
                                            <span class="es-nave-mark es-nave-s-{{ $item[1] }}"></span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <ul class="mt-5 grid grid-cols-1 gap-x-4 gap-y-1.5 text-sm sm:grid-cols-2">
                            @foreach ($week as [$wName, $wStrand, $wDay, $wTime])
                                <li class="flex min-w-0 items-center gap-2">
                                    <span class="es-nave-mark es-nave-s-{{ $wStrand }} w-2.5 shrink-0" aria-hidden="true"></span>
                                    <span class="min-w-0 flex-1 truncate text-white/90">{{ $wName }}</span>
                                    <span class="es-nave-num shrink-0 text-xs text-white/70">{{ $dayNames[$wDay] }} {{ $wTime }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6 grid grid-cols-3 gap-3 border-t border-white/15 pt-5">
                            <div>
                                <p class="es-nave-num text-2xl font-bold text-white">{{ $recurringCount }}</p>
                                <p class="es-nave-cap mt-1">Recurring events</p>
                            </div>
                            <div>
                                <p class="es-nave-num text-2xl font-bold text-white">{{ $strandCount }}</p>
                                <p class="es-nave-cap mt-1">Ministries</p>
                            </div>
                            <div>
                                <p class="es-nave-num text-2xl font-bold text-white">1</p>
                                <p class="es-nave-cap mt-1">Link to share</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-nave-muted mt-5 text-sm">
                        {{ $sundayCount }} of these are on a Sunday. The other {{ $otherDays }} are the ones people ask about.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Set once (01)                                             -->
    <!-- ============================================================ -->
    <section id="pattern" class="es-nave-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">01</div>
                    <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Set once</p>
                    <h2 class="es-balance es-nave-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The rhythm of the year, <span class="es-nave-grad">typed in one time</span>.
                    </h2>
                    <p class="es-nave-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A church calendar repeats more than almost any other. The same service every
                        Sunday, the same group every Tuesday, the men's breakfast every other Saturday
                        and the lunch club on the first Wednesday of the month. Each of those is one
                        entry that fills itself in for as long as it runs.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Weekly on the days it meets', 'Morning worship on Sundays, the prayer group on Tuesdays. Choose the days once and they appear every week.'],
                            ['Every other week, or every third', 'A fortnightly breakfast or a house group that meets every three weeks is one setting, not a list of dates.'],
                            ['The first Sunday, the second Tuesday', 'Monthly by weekday covers the communion lunch and the church council, and yearly covers harvest and the anniversary.'],
                            ['The weeks that are different', 'A date exception takes out the Sunday the service moves to the park, or adds a Wednesday in Holy Week, without disturbing the pattern.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-nave-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-nave-ink font-semibold">{{ $t }}</span> <span class="es-nave-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-nave-plan es-nave-plan-free">Free plan</span>
                        <span class="es-nave-muted ml-2 text-sm">Recurring events and date exceptions are on every plan, with no cap on how many.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-nave-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-nave-ink text-lg font-bold">What you fill in, once</h3>
                            <span class="es-nave-muted es-nave-num text-xs">1 event</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Event', 'Morning worship'],
                                ['Repeats', 'Weekly, on Sunday'],
                                ['Starts', '9:30am, for 90 minutes'],
                                ['Sub-schedule', 'Worship'],
                                ['Exceptions', 'Out: 12 Jul (park service)'],
                            ] as [$fLabel, $fValue])
                                <div class="es-nave-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-nave-muted w-24 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                    <span class="es-nave-ink es-nave-num min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-nave-muted es-nave-rule mt-5 border-t pt-4 text-xs">
                            A recurring event has one start time. The 11am service is a second entry,
                            set the same way, with its own description and its own link.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Ministries (02)                                           -->
    <!-- ============================================================ -->
    <section id="strands" class="es-nave-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">02</div>
                <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Ministries</p>
                <h2 class="es-balance es-nave-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One calendar, <span class="es-nave-grad">a link for each ministry</span>.
                </h2>
                <p class="es-nave-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A youth parent does not need the church council, and the choir does not need
                    the toddler group. Sub-schedules sort the week into ministries, each with its own
                    colour and its own link, while the main calendar still shows everything.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5" data-reveal-group="80">
                @foreach ([
                    ['worship', 'Services, communion, evening prayer and the special services of the year.'],
                    ['groups', 'House groups, prayer meetings, Bible study, Alpha and the men\'s breakfast.'],
                    ['youth', 'Youth night, Sunday school, holiday clubs and the camp in the summer.'],
                    ['music', 'Choir rehearsals, the worship band and the concerts you put on.'],
                    ['community', 'The toddler group, the food bank, the lunch club and the hall.'],
                ] as [$sKey, $sDesc])
                    <div @class(['es-nave-card es-nave-hover flex flex-col p-5', 'sm:col-span-2 lg:col-span-1' => $loop->last]) data-reveal>
                        <span class="es-nave-mark es-nave-s-{{ $sKey }} mb-4 w-10" aria-hidden="true"></span>
                        <h3 class="es-nave-ink text-base font-bold">{{ $strands[$sKey] }}</h3>
                        <p class="es-nave-muted mt-2 text-sm">{{ $sDesc }}</p>
                        <p class="es-nave-accent es-nave-num mt-auto pt-4 text-xs font-semibold">/{{ \Illuminate\Support\Str::slug($strands[$sKey]) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-2">
                <div class="es-nave-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-nave-ink text-lg font-bold">One church, one schedule</h3>
                        <span class="es-nave-plan es-nave-plan-free">Free plan</span>
                    </div>
                    <p class="es-nave-muted mb-5 text-sm">For a single congregation with several ministries.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Every ministry is a sub-schedule, filtered on the public page.',
                            'Each has a link you can send to the people it is for.',
                            'Everything still rolls up into one calendar and one feed.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-nave-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-nave-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-nave-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-nave-ink text-lg font-bold">Several sites, several schedules</h3>
                        <span class="es-nave-plan es-nave-plan-free">Free plan</span>
                    </div>
                    <p class="es-nave-muted mb-5 text-sm">For a multi-site church or a group of parishes.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Each campus or parish gets a schedule of its own, with its own address and link.',
                            'One account can own many schedules, so the office runs them all from one login.',
                            'A shared event, such as a joint Easter service, can be listed on more than one.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-nave-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-nave-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The big dates (03)                                        -->
    <!-- ============================================================ -->
    <section id="seasons" class="es-nave-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">03</div>
                <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The big dates</p>
                <h2 class="es-balance es-nave-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Christmas Eve fills up. <span class="es-nave-grad">Know by how much</span>.
                </h2>
                <p class="es-nave-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The crib service, the carol service and the Easter dawn service are the dates
                    visitors come to. They go on top of the weekly pattern as their own events, with
                    a free sign-up and a number of places, so the stewards are not guessing.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-nave-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-nave-ink text-lg font-bold">The crib service</h3>
                        <span class="es-nave-plan es-nave-plan-free">Free plan</span>
                    </div>
                    <p class="es-nave-muted mb-5 text-sm">When you want a count, not money.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'People sign up instead of paying, and the sign-ups are unlimited on every plan.',
                            'Give it a capacity and it stops taking names when the places are gone.',
                            'Ask for each person\'s name, so a family of five holds five places.',
                            'A waiting list on a full free event lets you offer a place that frees up.',
                            'Scan the QR on each sign-up at the door on any phone, or just count heads.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-nave-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-nave-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-nave-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-nave-ink text-lg font-bold">The weekend away</h3>
                        <span class="es-nave-plan es-nave-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-nave-muted mb-5 text-sm">Retreats, camps, concerts and the fundraising dinner.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Ticket types with prices: adult, child, and a family rate.',
                            'Paid into the church\'s own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash, with no platform fee on top.',
                            'Let families spread the cost of a summer camp over monthly installments, through Stripe.',
                            'Ask your own questions at booking, such as allergies or an emergency contact, with custom fields.',
                            'Refund someone who has to drop out from the Sales page, in full or in part.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-nave-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-nave-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="80">
                @foreach ([
                    ['Advent', 'Carol service, crib service, midnight mass'],
                    ['Lent', 'A weekly course, set as one recurring event'],
                    ['Holy Week', 'A service every evening, added as exceptions'],
                    ['Summer', 'Holiday club, camp and the church picnic'],
                ] as [$season, $what])
                    <div class="es-nave-sub flex flex-col p-4" data-reveal>
                        <p class="es-nave-accent text-xs font-bold uppercase tracking-widest">{{ $season }}</p>
                        <p class="es-nave-ink mt-2 text-sm font-semibold">{{ $what }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Reaching people (04)                                      -->
    <!-- ============================================================ -->
    <section id="reach" class="es-nave-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">04</div>
                    <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Reaching people</p>
                    <h2 class="es-balance es-nave-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Past the people <span class="es-nave-grad">who read the notices</span>.
                    </h2>
                    <p class="es-nave-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Notices read out on Sunday reach the people who were there on Sunday. The same
                        calendar can go out through the doors your congregation actually uses, and
                        most of them cost nothing.
                    </p>

                    <div class="es-nave-card p-6" data-reveal="panel">
                        <h3 class="es-nave-ink text-base font-bold">The emails, in plain numbers</h3>
                        <p class="es-nave-muted mt-2 text-sm">
                            A newsletter is one you write and send. The allowance counts each
                            recipient, so one letter to 80 members uses 80 of it.
                        </p>
                        <div class="mt-4 space-y-2">
                            @foreach ([
                                ['Free plan', '10 a month'],
                                ['Pro plan', '100 a month'],
                                ['Enterprise', '1,000 a month'],
                            ] as [$pName, $pAllow])
                                <div class="es-nave-sub flex items-baseline justify-between gap-3 px-3.5 py-2.5">
                                    <span class="es-nave-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $pName }}</span>
                                    <span class="es-nave-muted es-nave-num shrink-0 text-xs">{{ $pAllow }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-nave-muted mt-4 text-xs">
                            The digest of new dates sent to confirmed email subscribers is separate and
                            does not use the allowance. With your own email settings connected, the
                            newsletter limit comes off.
                        </p>
                    </div>
                </div>

                <div class="space-y-3" data-reveal-group="80">
                    @foreach ([
                        ['The church website', 'Paste the embeddable calendar into the site you already have. It updates when the calendar does, so nobody has to edit the web page again.', 'Free'],
                        ['Their own phone calendar', 'Members subscribe to the live calendar feed once, from any event\'s Add to Calendar menu or the sign-up panel, and a moved date updates itself.', 'Free'],
                        ['An email when new dates go up', 'Anyone can leave an email address on the church page and confirm it. They are then sent a short digest of new dates, at most one every 72 hours.', 'Free'],
                        ['The notice board and the pew sheet', 'Download the schedule\'s QR code and print it. A phone pointed at the board opens the live calendar.', 'Free'],
                        ['The projector slide', 'Generate a graphic of the coming week\'s events to show before the service or post on social media.', 'Free'],
                        ['A second language', 'Nominate one other language, and event names and descriptions are translated by AI with a language switch on the page.', 'Free'],
                    ] as [$t, $d, $plan])
                        <div class="es-nave-card es-nave-hover p-4" data-reveal>
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="es-nave-ink text-sm font-bold">{{ $t }}</p>
                                <span class="es-nave-plan es-nave-plan-free">{{ $plan }}</span>
                            </div>
                            <p class="es-nave-muted mt-1 text-sm">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. The hall and the office (05)                              -->
    <!-- ============================================================ -->
    <section id="requests" class="es-nave-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">05</div>
                <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The hall and the office</p>
                <h2 class="es-balance es-nave-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Requests through the page, <span class="es-nave-grad">not the answerphone</span>.
                </h2>
                <p class="es-nave-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The slimming club wants the hall on Mondays, a family wants to talk about a
                    baptism, and a visiting choir wants to sing in June. Each of those can arrive on
                    your church page rather than on a scrap of paper in the vestry.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="es-nave-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-nave-ink text-lg font-bold">Hall hire requests</h3>
                        <span class="es-nave-plan es-nave-plan-free">Free plan</span>
                    </div>
                    <p class="es-nave-muted mt-2 text-sm">
                        An outside group fills in the booking form with a date, a time and a
                        description. It waits on your Requests tab, and nothing appears publicly
                        until you accept it. The contact details they give stay visible to you only.
                    </p>
                    <p class="es-nave-muted mt-auto pt-4 text-xs">Your own questions on the form, such as insurance or numbers, are a Pro custom field.</p>
                </div>

                <div class="es-nave-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-nave-ink text-lg font-bold">A time with the minister</h3>
                        <span class="es-nave-plan es-nave-plan-free">Free plan</span>
                    </div>
                    <p class="es-nave-muted mt-2 text-sm">
                        One free appointment type with weekly hours: office hours, a baptism or
                        wedding enquiry, a pastoral visit. People pick an open time on a booking
                        page, and it goes into your calendar.
                    </p>
                    <p class="es-nave-muted mt-auto pt-4 text-xs">More types, an approval step and buffers between bookings are Pro. On the hosted service, the confirmation emails need your own email settings.</p>
                </div>

                <div class="es-nave-card flex flex-col p-6" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-nave-ink text-lg font-bold">Visiting choirs and speakers</h3>
                        <span class="es-nave-plan es-nave-plan-free">Free plan</span>
                    </div>
                    <p class="es-nave-muted mt-2 text-sm">
                        Add a guest preacher or a touring choir to the event by name. If they are
                        not on Event Schedule, they get a page that shows the date and says your
                        church listed them, kept out of search engines until they claim it.
                    </p>
                    <p class="es-nave-muted mt-auto pt-4 text-xs">If they are already on Event Schedule, the date is offered to their own schedule.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Who it is for (06)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="es-nave-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">06</div>
                <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-nave-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Any congregation <span class="es-nave-grad">with a week to share</span>.
                </h2>
                <p class="es-nave-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Written for churches, and just as useful to any faith community that meets on a
                    rhythm and wants the rest of the week to be as easy to find as the main service.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                <x-sub-audience-card
                    name="Parish Churches"
                    description="Sunday services, midweek groups and the parish hall on one calendar. Set the weekly pattern once and add the one-off dates on top."
                    icon-color="amber"
                    blog-slug="for-parish-churches"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v4m-2-2h4M6 21V11l6-4 6 4v10M4 21h16M10 21v-4a2 2 0 014 0v4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Multi-Site Churches"
                    description="One calendar per campus with its own link, and a shared view for the whole church so nobody has to ask which site is doing what."
                    icon-color="sky"
                    blog-slug="for-multi-site-churches"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Youth Ministries"
                    description="Friday youth nights, retreats and camp sign-ups, with a capacity on every trip and the details parents need collected up front."
                    icon-color="teal"
                    blog-slug="for-youth-ministries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 20l6-10 4 6 3-4 5 8H3zM16 7a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Synagogues, Mosques & Temples"
                    description="Weekly services, festival dates and study groups for any congregation, with the calendar translated for members who read another language."
                    icon-color="emerald"
                    blog-slug="for-houses-of-worship"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V10m14 11V10M3 10l9-6 9 6M9 21v-5a3 3 0 016 0v5" />
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
        <div class="es-nave-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">07</div>
                    <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        An afternoon in September, <span class="es-nave-grad">then the whole year</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Set the regular week', 'Every service and weekly group as a recurring event on the days it meets, with the weeks it moves taken out.'],
                        ['02', 'Sort it into ministries', 'Worship, groups, youth, music and community as sub-schedules, each with a link for the people it serves.'],
                        ['03', 'Add the big dates on top', 'Christmas, Easter and the weekend away, with a free sign-up and a number of places, or a ticket price on Pro.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-xl border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-nave-lit es-nave-num mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="es-nave-rule scroll-mt-24 border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-nave-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Every service and weekly group set once, with the weeks it moves taken out" :url="marketing_url('/features/recurring-events')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="A link for each ministry, rolled up into one church calendar" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the whole week on the church website you already have" :url="marketing_url('/features/embed-calendar')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to the congregation yourself, within an allowance counted per recipient" :url="marketing_url('/features/newsletters')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Appointments" description="Office hours and enquiries booked into open times, one type free" :url="marketing_url('/features/appointments')" icon-color="orange">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-nave-accent inline-flex items-center font-medium hover:underline">
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
    <section class="es-nave-rule border-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-nave-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-community-centers', 'Community Centers'],
                    ['/for-nonprofits', 'Nonprofits'],
                    ['/for-schools', 'Schools'],
                    ['/for-libraries', 'Libraries'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-nave-card es-nave-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-nave-muted text-sm">Event Schedule for</div>
                            <div class="es-nave-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-nave-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-nave-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="es-nave-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-nave-chip mb-6" data-reveal aria-hidden="true">08</div>
                <p class="es-nave-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-nave-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at <span class="es-nave-grad">the church council</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-nave-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-nave-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-nave-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-nave-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-nave-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-nave-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put the whole week <span class="es-nave-grad">where people can find it</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The weekly pattern, the ministries, the sign-ups with a capacity and the
                        calendar feed cost nothing. A ticket with a price is the part that needs Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-church" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-nave-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Set up your calendar
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1b1716] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
