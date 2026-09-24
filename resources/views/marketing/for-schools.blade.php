<x-marketing-layout>
    <x-slot name="title">School Event Calendar | Term Dates, Shows, Parent Evenings</x-slot>
    <x-slot name="description">Clubs set once for the whole term, closure days taken out, the school play with a capacity or a ticket, and a calendar families add to their phone once.</x-slot>
    <x-slot name="breadcrumbTitle">For Schools</x-slot>

    <x-slot name="structuredData">
    <x-seo.webpage
        name="Event Schedule for Schools"
        description="A school event calendar where weekly clubs are set once for the term, closure days are date exceptions, and concerts, trips and parent evenings sit on top with free sign-ups or tickets."
        audience="Schools, Colleges, Universities & Parent-Teacher Associations"
        keywords="school event calendar, school calendar app, parent teacher conference booking, school play tickets, school club schedule, pta events" />
    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to put a school event calendar online with Event Schedule",
        "description": "Set the term's regular dates once, add the evenings on top, and give families one link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the term",
                "text": "Create each weekly club or fixture as a recurring event on its day, ending on the last day of term, and take out half term and closure days as date exceptions."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Add the evenings",
                "text": "Add the concert, the trip and the information evening as their own events, with a free sign-up and a capacity, or a priced ticket on Pro."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Hand families one link",
                "text": "Put the calendar on the school website, and let families subscribe to its live feed or leave an email address for new dates."
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
           For-schools "The Term Planner" styles.

           CONCEPT: a school does not plan in weeks or months, it plans in
           TERMS. The wall planner in every school office is a grid: the
           weeks of the term across, the five school days down, half term
           shaded out and a handful of evenings circled in pen. Almost all
           of the grid is the same thing repeating (the Tuesday club, the
           school day), and only a few cells are one-offs.

           THE DEVICE IS THAT GRID - fourteen weeks by five days, drawn
           from the constants defined further down. The argument is its
           shape: the Tuesday column is ONE recurring event with two dates
           taken out (half term and a training day), and the circled cells
           are the only entries made by hand.

           Prefix es-chalk, not es-term: .es-term is already a class on
           /selfhost.

           COLOUR: chalkboard green. Accent #1f5f4a (hue ~160, sat 51%) on
           the #f3f5f2 ground is about 7:1; #8fd6b8 on the #0c110f dark
           ground is about 11:1. Muted ink #4a5852 / #9fb2a9 both clear
           4.5:1 on their grounds and cards. Never text-gray-500 here.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-chalk-page { background-color: #f3f5f2; color: #121a16; }
        .dark .es-chalk-page { background-color: #0c110f; color: #e7eeea; }
        .es-chalk-ink { color: #121a16; }
        .dark .es-chalk-ink { color: #e7eeea; }
        .es-chalk-muted { color: #4a5852; }
        .dark .es-chalk-muted { color: #9fb2a9; }
        .es-chalk-accent { color: #1f5f4a; }
        .dark .es-chalk-accent { color: #8fd6b8; }
        .es-chalk-lit { color: #8fd6b8; }

        .es-chalk-grad {
            background-image: linear-gradient(100deg, #1f5f4a, #2f7a60);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-chalk-grad,
        .es-chalk-band .es-chalk-grad {
            background-image: linear-gradient(100deg, #b5e8d2, #8fd6b8);
        }

        /* --- Surfaces --- */
        .es-chalk-card {
            background-color: #fbfcfb;
            border: 1px solid rgba(18, 26, 22, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-chalk-card {
            background-color: #151c19;
            border-color: rgba(231, 238, 234, 0.12);
        }
        .es-chalk-sub {
            background-color: rgba(18, 26, 22, 0.045);
            border-radius: 0.4rem;
        }
        .dark .es-chalk-sub { background-color: rgba(231, 238, 234, 0.05); }
        .es-chalk-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-chalk-hover:hover { border-color: rgba(31, 95, 74, 0.45); box-shadow: 0 10px 28px -18px rgba(18, 26, 22, 0.5); }
        .dark .es-chalk-hover:hover { border-color: rgba(143, 214, 184, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }
        .es-chalk-rule { border-color: rgba(18, 26, 22, 0.1); }
        .dark .es-chalk-rule { border-color: rgba(231, 238, 234, 0.1); }

        /* --- The planner ----------------------------------------------
           A fixed chalkboard: the same dark green in both colour modes,
           so nothing inside it carries a .dark variant. Cell states are
           STATE, never a plan tier. */
        .es-chalk-board {
            background-color: #173d31;
            background-image: radial-gradient(ellipse 80% 60% at 30% 10%, rgba(143, 214, 184, 0.12), rgba(143, 214, 184, 0) 70%);
            color: #ffffff;
            border-radius: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .es-chalk-grid {
            display: grid;
            grid-template-columns: 1.6rem repeat(14, minmax(0, 1fr));
            gap: 3px;
            align-items: center;
        }
        .es-chalk-cell { aspect-ratio: 1 / 1; border-radius: 3px; min-width: 0; }
        .es-chalk-day { background-color: rgba(255, 255, 255, 0.16); }
        .es-chalk-closed { background-color: transparent; box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16); }
        .es-chalk-club { background-color: #8fd6b8; }
        .es-chalk-event { background-color: #ffffff; box-shadow: 0 0 0 2px #f2c14e; }
        .es-chalk-cap {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            /* 0.78 over #173d31 composites to about 7.5:1. */
            color: rgba(255, 255, 255, 0.78);
        }
        .es-chalk-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-chalk-key { display: inline-block; width: 0.7rem; height: 0.7rem; border-radius: 2px; vertical-align: -0.05rem; }

        /* --- Eyebrow, chips, plan tags --- */
        .es-chalk-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #1f5f4a;
        }
        .dark .es-chalk-tag { color: #8fd6b8; }
        .es-chalk-band .es-chalk-tag { color: #8fd6b8; }

        /* The chip is a chalk mark: a number with a short underline. */
        .es-chalk-chip {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #121a16;
        }
        .dark .es-chalk-chip { color: #e7eeea; }
        .es-chalk-band .es-chalk-chip { color: #e7eeea; }
        .es-chalk-chip::after {
            content: "";
            display: block;
            width: 1.6rem;
            height: 2px;
            border-radius: 2px;
            background: #1f5f4a;
        }
        .dark .es-chalk-chip::after { background: #8fd6b8; }
        .es-chalk-band .es-chalk-chip::after { background: #8fd6b8; }

        /* Plan tiers ONLY - never reuse these for a planner state. */
        .es-chalk-plan {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid transparent;
            padding: 0.1rem 0.5rem;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .es-chalk-plan-free { border-color: rgba(18, 26, 22, 0.22); color: #4a5852; }
        .dark .es-chalk-plan-free { border-color: rgba(231, 238, 234, 0.26); color: #9fb2a9; }
        .es-chalk-plan-pro { border-color: rgba(31, 95, 74, 0.5); color: #1f5f4a; background: rgba(31, 95, 74, 0.08); }
        .dark .es-chalk-plan-pro { border-color: rgba(143, 214, 184, 0.42); color: #8fd6b8; background: rgba(143, 214, 184, 0.1); }
        .es-chalk-plan-ent { border-color: rgba(18, 26, 22, 0.55); color: #121a16; background: rgba(18, 26, 22, 0.06); }
        .dark .es-chalk-plan-ent { border-color: rgba(231, 238, 234, 0.5); color: #e7eeea; background: rgba(231, 238, 234, 0.08); }

        /* --- Buttons --- */
        .es-chalk-btn {
            background-color: #1f5f4a;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-chalk-btn:hover { background-color: #164837; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(31, 95, 74, 0.9); }
        .es-chalk-ghost {
            border: 1px solid rgba(18, 26, 22, 0.22);
            color: #121a16;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-chalk-ghost:hover { border-color: rgba(31, 95, 74, 0.5); background-color: rgba(31, 95, 74, 0.06); }
        .dark .es-chalk-ghost { border-color: rgba(231, 238, 234, 0.24); color: #e7eeea; }
        .dark .es-chalk-ghost:hover { border-color: rgba(143, 214, 184, 0.45); background-color: rgba(143, 214, 184, 0.08); }

        /* --- The dark band --- */
        .es-chalk-band {
            background-color: #0f1613;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(31, 95, 74, 0.55), rgba(31, 95, 74, 0) 70%),
                linear-gradient(180deg, #141d19, #0f1613);
        }
        .es-chalk-band .grid-overlay {
            background-image:
                linear-gradient(rgba(231, 238, 234, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(231, 238, 234, 0.05) 1px, transparent 1px);
        }
        .es-chalk-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-chalk-band .es-claim:focus-within {
            border-color: rgba(143, 214, 184, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 214, 184, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(31, 95, 74, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(143, 214, 184, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1f5f4a; }
        .dark .es-dot.is-active .es-dot-pip { background: #8fd6b8; }

        #es-chalk-page a:focus-visible,
        #es-chalk-page summary:focus-visible,
        #es-chalk-page button:focus-visible,
        #es-chalk-page input:focus-visible {
            outline: 2px solid #1f5f4a;
            outline-offset: 2px;
        }
        .dark #es-chalk-page a:focus-visible,
        .dark #es-chalk-page summary:focus-visible,
        .dark #es-chalk-page button:focus-visible,
        .dark #es-chalk-page input:focus-visible {
            outline-color: #8fd6b8;
        }
        .es-chalk-band a:focus-visible,
        .es-chalk-band summary:focus-visible,
        .es-chalk-band button:focus-visible,
        .es-chalk-band input:focus-visible {
            outline-color: #8fd6b8 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-chalk-btn:hover { transform: none; }
        }
    </style>

    @php
        // The term. Every count on the page is derived from these constants,
        // so the copy and the planner cannot drift apart: 14 weeks of five
        // school days, one week of half term, two training days, a Tuesday
        // club, and five evenings circled by hand (none of them on a Tuesday).
        $termStart = new DateTimeImmutable('2026-09-07');   // Mon, week 1
        $termWeeks = 14;
        $halfTermStart = new DateTimeImmutable('2026-10-26');
        $halfTermEnd = new DateTimeImmutable('2026-10-30');
        $trainingDays = ['2026-09-07', '2026-11-17'];        // Mon, Tue
        $clubWeekday = 2;                                      // Tuesday (ISO-8601)

        $evenings = [
            '2026-09-24' => ['Welcome evening', 'Thu 6pm', 'Free, 150 places'],
            '2026-10-15' => ['Parent consultations', 'Thu 4-7pm', 'Pick a slot'],
            '2026-11-12' => ['Quiz night', 'Thu 7pm', 'Free, 12 tables'],
            '2026-12-09' => ['Winter concert', 'Wed 6:30pm', 'Tickets, 220 seats'],
            '2026-12-10' => ['Winter concert', 'Thu 6:30pm', 'Tickets, 220 seats'],
        ];

        $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $grid = [];            // [weekdayIndex][weekIndex] => state
        $schoolDays = 0;
        $clubDates = 0;
        $clubExceptions = 0;

        for ($w = 0; $w < $termWeeks; $w++) {
            for ($d = 0; $d < 5; $d++) {
                $date = $termStart->modify('+'.($w * 7 + $d).' days');
                $key = $date->format('Y-m-d');
                $closed = ($date >= $halfTermStart && $date <= $halfTermEnd) || in_array($key, $trainingDays, true);
                $isClubDay = (int) $date->format('N') === $clubWeekday;

                if ($closed) {
                    $state = 'closed';
                    $clubExceptions += $isClubDay ? 1 : 0;
                } elseif (isset($evenings[$key])) {
                    $state = 'event';
                } elseif ($isClubDay) {
                    $state = 'club';
                } else {
                    $state = 'day';
                }

                if (! $closed) {
                    $schoolDays++;
                    $clubDates += $isClubDay ? 1 : 0;
                }

                $grid[$d][$w] = ['state' => $state, 'label' => $date->format('D j M')];
            }
        }

        $eveningCount = count($evenings);
        $closedCount = $termWeeks * 5 - $schoolDays;
        $lastDay = $termStart->modify('+'.(($termWeeks - 1) * 7 + 4).' days');

        $faqs = [
            [
                'q' => 'Is Event Schedule free for a school?',
                'a' => 'The parts a school uses every week are free forever: recurring clubs and fixtures, date exceptions for closures, free sign-ups with a capacity, sub-schedules, one appointment type for parents to book, the live calendar feed, the website embed, two-way calendar sync and the translated page. Pro, at '.plan_price($proMonthly).' a month, is for putting a price on a ticket, the live check-in dashboard, more appointment types and the advanced booking rules. Enterprise adds more staff logins and the Internal and Unlisted event types. There are zero platform fees on ticket sales on every plan.',
            ],
            [
                'q' => 'Can parents book a slot for a parent-teacher conference?',
                'a' => 'Yes, through an appointment type. You set the hours it runs and how long each slot is, and parents pick an open time on its booking page; they get a confirmation, a reminder and the option to cancel or move it. On the free plan a schedule has one appointment type, and it must be free to book, which suits a standing slot like a head of year\'s weekly drop-in. A one-off consultation evening on a single date needs a date override, and date overrides, buffers between slots, a minimum notice period and an approval step are Pro, as are more types, for example one per teacher. On eventschedule.com the booking emails go out through the school\'s own email settings, so add those first.',
            ],
            [
                'q' => 'Can more than one member of staff run the calendar?',
                'a' => 'The free plan and Pro each include one team member on a schedule. Adding more staff is Enterprise: an admin can run the schedule day to day and see ticket sales and the check-in dashboard, and a viewer has read-only access but can still scan tickets at the door. Only the owner changes those levels. If you selfhost Event Schedule on the school\'s own server, every feature is included.',
            ],
            [
                'q' => 'Can some events stay private to staff?',
                'a' => 'A Draft event stays members-only until you publish it, on every plan, which is how most schools hold next term\'s dates before they are confirmed. Two more options are Enterprise: Internal events are for members only and never go public, and Unlisted events are hidden from the calendar but open to anyone with the direct link, with an optional password, which suits a staff training day or a page for one class\'s families.',
            ],
            [
                'q' => 'Do families have to download an app or make an account?',
                'a' => 'No. Anyone can open the calendar in a browser, on the school website where you embed it, or from a link in your newsletter. Families who want dates in their own calendar subscribe to its live feed from the Add to Calendar menu, which costs no email address at all. Families who want to hear about new dates leave an email address and confirm it, and from then on they get a short digest when you add events, at most one every 72 hours.',
            ],
            [
                'q' => 'How do we sell tickets for the school play?',
                'a' => 'Putting a price on a ticket needs Pro. Set a ticket type for each price, such as adult and child, give the night a capacity, and families pay into the school\'s own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash. Event Schedule takes no platform fee. Tickets carry a QR code, scanning them at the door works on every plan, and the live check-in dashboard is part of Pro. If the performance is free, give it free registration with a capacity instead, which needs no plan at all.',
            ],
            [
                'q' => 'What happens to weekly clubs over half term and closure days?',
                'a' => 'Each club is one recurring event on its day of the week that ends on the last day of term. For half term or a training day you add a date exception, which takes that single date out without touching the rest of the pattern. Exceptions work the other way too, so a one-off extra session can be added outside the usual day.',
            ],
            [
                'q' => 'Many of our families read another language. Can the calendar help?',
                'a' => 'Yes. A schedule is written in one language and can nominate one other language to translate into. Event names and descriptions are translated by AI, and a language switch appears on the public page. It is free, and it is one extra language per schedule rather than all of them at once.',
            ],
        ];

        $dotSections = [
            ['top', 'The term'],
            ['clubs', 'Clubs and fixtures'],
            ['home', 'At home'],
            ['show', 'The show'],
            ['parents', 'Parent evenings'],
            ['strands', 'Every strand'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-chalk-page" class="es-chalk-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the term planner                                    -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 24% 32%, rgba(31, 95, 74, 0.2), rgba(31, 95, 74, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 62%, rgba(143, 214, 184, 0.16), rgba(143, 214, 184, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-chalk-tag es-fade-up es-d-1 mb-5">School event calendar, for colleges and PTAs too</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Plan the term once.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Circle <span class="es-chalk-grad">the evenings</span>.</span></span>
                    </h1>

                    <p class="es-chalk-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Most of a school calendar repeats every week until the holidays. So the
                        Tuesday club is one entry for the whole term, half term comes out of it in
                        one step, and the concert and parent evenings are the {{ $eveningCount }} dates
                        you add by hand.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-chalk-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Plan this term
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#clubs" class="es-chalk-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how a term is set
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The planner. Weeks across, school days down. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-chalk-board p-5 sm:p-7">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="es-chalk-cap">Autumn term</p>
                            <p class="es-chalk-num text-xs text-white/75">{{ $termStart->format('j M') }} to {{ $lastDay->format('j M') }}</p>
                        </div>
                        <p class="mt-2 text-xl font-bold text-white">Riverside Primary</p>

                        <div class="es-chalk-grid mt-6" role="img"
                            aria-label="{{ $termWeeks }} weeks of term: {{ $schoolDays }} school days, {{ $closedCount }} closed for half term and training days, a Tuesday club on {{ $clubDates }} dates, and {{ $eveningCount }} evening events circled.">
                            <span></span>
                            @for ($w = 1; $w <= $termWeeks; $w++)
                                <span class="es-chalk-num text-center text-[0.55rem] text-white/60">{{ $w }}</span>
                            @endfor
                            @foreach ($grid as $d => $row)
                                <span class="es-chalk-cap text-[0.55rem]">{{ substr($weekdays[$d], 0, 2) }}</span>
                                @foreach ($row as $cell)
                                    <span @class([
                                        'es-chalk-cell',
                                        'es-chalk-day' => $cell['state'] === 'day',
                                        'es-chalk-closed' => $cell['state'] === 'closed',
                                        'es-chalk-club' => $cell['state'] === 'club',
                                        'es-chalk-event' => $cell['state'] === 'event',
                                    ])></span>
                                @endforeach
                            @endforeach
                        </div>

                        <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-xs text-white/80" aria-hidden="true">
                            <span><span class="es-chalk-key es-chalk-club"></span> Tuesday club</span>
                            <span><span class="es-chalk-key es-chalk-event"></span> Evening</span>
                            <span><span class="es-chalk-key es-chalk-closed"></span> Closed</span>
                        </div>

                        <div class="mt-6 grid grid-cols-3 gap-3 border-t border-white/15 pt-5">
                            <div>
                                <p class="es-chalk-num text-2xl font-bold text-white">{{ $clubDates }}</p>
                                <p class="es-chalk-cap mt-1">Club dates</p>
                            </div>
                            <div>
                                <p class="es-chalk-num text-2xl font-bold text-white">1</p>
                                <p class="es-chalk-cap mt-1">Recurring event</p>
                            </div>
                            <div>
                                <p class="es-chalk-num text-2xl font-bold text-white">{{ $eveningCount }}</p>
                                <p class="es-chalk-cap mt-1">Evenings</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-chalk-muted mt-5 text-sm">
                        {{ $clubDates }} club dates from one entry. {{ $clubExceptions }} taken out for half term and a training day.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Clubs and fixtures (01)                                   -->
    <!-- ============================================================ -->
    <section id="clubs" class="es-chalk-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Clubs and fixtures</p>
                    <h2 class="es-balance es-chalk-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The timetable, <span class="es-chalk-grad">typed in once</span>.
                    </h2>
                    <p class="es-chalk-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Choir on Monday, chess on Wednesday, netball training every Thursday. Each is a
                        recurring event on its day of the week that runs until the last day of term,
                        so the office enters it in September and does not open it again until the
                        spring.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Any rhythm a school keeps', 'Weekly on chosen days, every other week for a fortnightly fixture, or monthly by weekday, such as the first Friday assembly.'],
                            ['Half term in one step', 'A date exception takes a single date out of the pattern. Add one for each day of the break, and the rest of the term is untouched.'],
                            ['Training days and snow days', 'A closure is the same exception. A club that moves to Wednesday for one week gets that date added and the Tuesday taken out.'],
                            ['Ends when the term ends', 'Set the last date and the club stops appearing. Next term, clone it and change the dates.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-chalk-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-chalk-ink font-semibold">{{ $t }}</span> <span class="es-chalk-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-chalk-plan es-chalk-plan-free">Free plan</span>
                        <span class="es-chalk-muted ml-2 text-sm">Recurring events, date exceptions and event cloning are all free.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-chalk-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-chalk-ink text-lg font-bold">What the office fills in, once</h3>
                            <span class="es-chalk-muted es-chalk-num text-xs">1 event</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Event', 'Year 3 and 4 Coding Club'],
                                ['Repeats', 'Every Tuesday, 3:20pm'],
                                ['First date', $termStart->modify('+1 day')->format('D j M Y')],
                                ['Ends', 'On '.$lastDay->format('D j M Y')],
                                ['Exceptions', $clubExceptions.' dates out'],
                                ['Places', '24 each week'],
                            ] as [$fLabel, $fValue])
                                <div class="es-chalk-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-chalk-muted w-24 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                    <span class="es-chalk-ink es-chalk-num min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-chalk-muted es-chalk-rule mt-5 border-t pt-4 text-xs">
                            That is the whole club. It lands on {{ $clubDates }} Tuesdays, and the 24 places are counted again for each one.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The calendar at home (02)                                 -->
    <!-- ============================================================ -->
    <section id="home" class="es-chalk-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The calendar at home</p>
                <h2 class="es-balance es-chalk-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    On the fridge, <span class="es-chalk-grad">in the phone</span>.
                </h2>
                <p class="es-chalk-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A PDF of term dates is out of date the day a fixture moves. A calendar families
                    subscribe to is not: change the date once and it changes in every phone that
                    added it. Nobody has to install anything.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ([
                    ['Subscribe once', 'Families add the school\'s live calendar feed from the Add to Calendar menu. New dates and moved dates arrive on their own, and it costs them no email address.'],
                    ['On the school website', 'Paste one embed code into the page you already have, and the calendar there is always the current one. No web team needed for a date change.'],
                    ['In the office calendar', 'Two-way sync with Google Calendar, Outlook and CalDAV, so the calendar staff already use and the public one stay the same.'],
                    ['In the family\'s language', 'Nominate one other language and event names and descriptions are translated for you, with a switch on the public page.'],
                ] as [$t, $d])
                    <div class="es-chalk-card es-chalk-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-chalk-ink text-lg font-bold">{{ $t }}</h3>
                        <p class="es-chalk-muted mt-2 text-sm">{{ $d }}</p>
                        <p class="mt-auto pt-5"><span class="es-chalk-plan es-chalk-plan-free">Free plan</span></p>
                    </div>
                @endforeach
            </div>

            <p class="es-chalk-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Every schedule also has a QR code you can download, so the same calendar can go on
                the noticeboard by the gate and on the back page of the prospectus.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The show (03)                                             -->
    <!-- ============================================================ -->
    <section id="show" class="es-chalk-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The show, the trip, the fair</p>
                <h2 class="es-balance es-chalk-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Count the seats <span class="es-chalk-grad">before the night</span>.
                </h2>
                <p class="es-chalk-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The hall holds two hundred and twenty. Every family wants four seats for the
                    nativity. Whether you charge or not, the question is the same: how many are
                    coming, and who.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-chalk-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-chalk-ink text-lg font-bold">The free evening</h3>
                        <span class="es-chalk-plan es-chalk-plan-free">Free plan</span>
                    </div>
                    <p class="es-chalk-muted mb-5 text-sm">Open evenings, information sessions, trips and the sports day picnic.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Families register instead of paying, and the sign-up stops when the places run out.',
                            'The capacity is counted separately for each date, so two performances are two counts.',
                            'Ask for each guest\'s name, so a trip list names every child rather than "4 places".',
                            'A waitlist for a full free evening, offered to the next family when someone drops out.',
                            'Every registration carries a QR code, and scanning it at the door works on any plan.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-chalk-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-chalk-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-chalk-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-chalk-ink text-lg font-bold">The ticketed show</h3>
                        <span class="es-chalk-plan es-chalk-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-chalk-muted mb-5 text-sm">The school play, the summer concert, the PTA ball.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Adult, child and family ticket types, each with a price and a quantity.',
                            'Paid into the school\'s or the association\'s own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash.',
                            'No platform fee on top of what families pay. Only your payment provider\'s own fee applies.',
                            'The live check-in dashboard, so the front of house team can see the hall filling.',
                            'Refunds from the Sales page when a family cannot come, in full or in part through Stripe or PayPal.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-chalk-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-chalk-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <p class="es-chalk-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                A free ticket type can sit beside the paid ones on the same night, so staff and
                performers' siblings can take a seat at no charge without a separate event.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Parent evenings (04)                                      -->
    <!-- ============================================================ -->
    <section id="parents" class="es-chalk-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-chalk-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-chalk-ink text-lg font-bold">Mrs Okafor, Year 5</h3>
                                <span class="es-chalk-muted es-chalk-num text-xs">10 min slots</span>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                @foreach ([
                                    ['4:00', true], ['4:10', true], ['4:20', false],
                                    ['4:30', true], ['4:40', false], ['4:50', true],
                                    ['5:00', false], ['5:10', false], ['5:20', true],
                                ] as [$slot, $taken])
                                    <div @class(['es-chalk-sub es-chalk-num p-3 text-center text-sm font-semibold', 'es-chalk-muted line-through opacity-60' => $taken, 'es-chalk-accent' => ! $taken])>
                                        {{ $slot }}
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-chalk-muted es-chalk-rule mt-5 border-t pt-4 text-xs">
                                Parents see only the open times. Taking one sends a confirmation, and a
                                reminder before the day, with a private link to move or cancel it.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Parent evenings</p>
                    <h2 class="es-balance es-chalk-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        No more <span class="es-chalk-grad">slips in book bags</span>.
                    </h2>
                    <p class="es-chalk-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        An appointment type is a set of hours cut into slots. Parents pick an open
                        time on its booking page, and the grid of names on the classroom door is
                        already filled in before the evening starts.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        <div class="es-chalk-card es-chalk-hover p-4" data-reveal>
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="es-chalk-ink text-sm font-bold">One type, free</p>
                                <span class="es-chalk-plan es-chalk-plan-free">Free plan</span>
                            </div>
                            <p class="es-chalk-muted mt-1 text-sm">Weekly hours, the slot length, a public booking page and every confirmation, reminder and cancellation email. It must be free to book, which a parent meeting always is. Ideal for a standing slot, like a head of year's Thursday drop-in.</p>
                        </div>
                        <div class="es-chalk-card es-chalk-hover p-4" data-reveal>
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="es-chalk-ink text-sm font-bold">A consultation evening on one date</p>
                                <span class="es-chalk-plan es-chalk-plan-pro">Pro plan</span>
                            </div>
                            <p class="es-chalk-muted mt-1 text-sm">A single evening outside the weekly hours is a date override. Overrides, a gap between meetings, a minimum notice period, a booking window and an approval step are the advanced rules, and they are Pro, as are more types, such as one per teacher.</p>
                        </div>
                        <div class="es-chalk-card es-chalk-hover p-4" data-reveal>
                            <p class="es-chalk-ink text-sm font-bold">Sent from the school's address</p>
                            <p class="es-chalk-muted mt-1 text-sm">On eventschedule.com, booking emails go out through the schedule's own email settings, so connect the school's mail server first. Selfhosted, any configured mailer works.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Every strand, and who hears (05)                          -->
    <!-- ============================================================ -->
    <section id="strands" class="es-chalk-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Every strand</p>
                <h2 class="es-balance es-chalk-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Year 6 does not need <span class="es-chalk-grad">the under-9s fixtures</span>.
                </h2>
                <p class="es-chalk-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Sub-schedules split one school calendar into strands, each with its own colour
                    and its own link. A family follows the whole school, or only the parts their
                    children are in.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ([
                    ['Year groups', 'Trips, assemblies and the Year 6 residential on the pages those families actually open.'],
                    ['Clubs', 'Every after-school club on one strand, so a parent choosing for next term sees them all side by side.'],
                    ['Sports teams', 'Home and away fixtures for each team, with the venue on a map for the ones at another school.'],
                    ['The PTA', 'Fairs, discos and quiz nights on the association\'s own strand, with its tickets paid into its own account.'],
                ] as [$t, $d])
                    <div class="es-chalk-card es-chalk-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-chalk-ink text-lg font-bold">{{ $t }}</h3>
                        <p class="es-chalk-muted mt-2 text-sm">{{ $d }}</p>
                        <p class="es-chalk-accent mt-auto pt-5 text-xs font-semibold uppercase tracking-wider">Own link</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 grid gap-4 lg:grid-cols-2">
                <div class="es-chalk-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <h3 class="es-chalk-ink mb-1 text-lg font-bold">Who hears about new dates</h3>
                    <p class="es-chalk-muted mb-5 text-sm">Two ways in, and both are the family's choice.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'A family leaves an email address on the calendar and confirms it. When you add events, confirmed subscribers get a short digest of them, at most one every 72 hours, and it does not use your newsletter allowance.',
                            'Anything with more to say, such as a letter about the residential, is a newsletter the school writes and sends. The free plan covers 10 emails a month, Pro 100 and Enterprise 1,000, counted per recipient, so one letter to 90 families uses 90.',
                            'A school that selfhosts, or connects its own email settings on eventschedule.com, has no newsletter limit.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-chalk-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-chalk-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-chalk-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <h3 class="es-chalk-ink mb-1 text-lg font-bold">Who can see and edit what</h3>
                    <p class="es-chalk-muted mb-5 text-sm">Worth knowing before the first staff meeting.</p>
                    <div class="space-y-2.5">
                        @foreach ([
                            ['Draft events, members-only until announced', 'es-chalk-plan-free', 'Free'],
                            ['One team member on the schedule', 'es-chalk-plan-free', 'Free'],
                            ['More staff, as admins or read-only viewers', 'es-chalk-plan-ent', 'Enterprise'],
                            ['Internal events that never go public', 'es-chalk-plan-ent', 'Enterprise'],
                            ['Unlisted events, by direct link and password', 'es-chalk-plan-ent', 'Enterprise'],
                        ] as [$rLabel, $rClass, $rTier])
                            <div class="es-chalk-sub flex items-center justify-between gap-3 p-3.5">
                                <span class="es-chalk-ink min-w-0 flex-1 text-sm">{{ $rLabel }}</span>
                                <span class="es-chalk-plan {{ $rClass }} shrink-0">{{ $rTier }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="es-chalk-muted mt-auto pt-5 text-xs">A viewer sees no sales but can still scan tickets at the door, which is how a teacher on the hall doors helps without seeing the takings.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Who it is for (06)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="es-chalk-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-chalk-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    From the reception class <span class="es-chalk-grad">to the lecture hall</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                <x-sub-audience-card
                    name="Elementary Schools"
                    description="Assemblies, class trips and the winter concert, on one calendar families subscribe to so a changed date updates in their own phone."
                    icon-color="sky"
                    blog-slug="for-elementary-schools"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V7l8-4 8 4v12M4 19h16M9 19v-5h6v5M12 8.5v.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="High Schools"
                    description="Sports fixtures, the spring musical and exam-season evenings, each strand on its own link and the paid show with QR tickets at the door."
                    icon-color="amber"
                    blog-slug="for-high-schools"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Colleges & Universities"
                    description="Department talks, open days and society events on separate sub-schedules, with free registration and a cap on the rooms that fill."
                    icon-color="emerald"
                    blog-slug="for-colleges-universities"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m-6-9.5V16c0 1.657 2.686 3 6 3s6-1.343 6-3v-4.5" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Parent-Teacher Associations"
                    description="Fairs, quiz nights and volunteer sign-ups, with a newsletter to the families who signed up and ticket money paid straight to the association."
                    icon-color="rose"
                    blog-slug="for-parent-teacher-associations"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
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
        <div class="es-chalk-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Done in the first week, <span class="es-chalk-grad">right until the holidays</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Set the term', 'Each club and fixture is a recurring event that ends on the last day of term. Half term and closure days come out as date exceptions.'],
                        ['02', 'Add the evenings', 'The concert, the trip and the open evening, each with a capacity. A price only on the nights that have one, and a booking page for parent meetings.'],
                        ['03', 'Hand families one link', 'Put it on the school website, in the newsletter and on the noticeboard QR code. Families subscribe once and every change follows them.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-chalk-lit es-chalk-num mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="es-chalk-rule scroll-mt-24 border-t py-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-chalk-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A club or fixture for the whole term, with half term taken out" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Appointments" description="Parents pick an open slot to meet a teacher, with one type free" :url="marketing_url('/features/appointments')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Year groups, clubs, teams and the PTA on their own links" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="For the school play: ticket types, QR check-in and zero platform fees, on the Pro plan" :url="marketing_url('/features/ticketing')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="The current term on the school website, with no web team in the loop" :url="marketing_url('/features/embed-calendar')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-chalk-accent inline-flex items-center font-medium hover:underline">
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
    <section class="es-chalk-rule border-t py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-chalk-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-libraries', 'Libraries'],
                    ['/for-sports-leagues', 'Sports Leagues'],
                    ['/for-workshop-instructors', 'Workshop Instructors'],
                    ['/for-community-centers', 'Community Centers'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-chalk-card es-chalk-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-chalk-muted text-sm">Event Schedule for</div>
                            <div class="es-chalk-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-chalk-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-chalk-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="es-chalk-rule scroll-mt-24 border-t py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-chalk-chip mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-chalk-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-chalk-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked in <span class="es-chalk-grad">the school office</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-chalk-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-chalk-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-chalk-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-chalk-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-chalk-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-chalk-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Set the term <span class="es-chalk-grad">before the first bell</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Clubs, closures, free sign-ups, a booking page for parents and a calendar
                        families subscribe to cost nothing. A priced ticket for the show is the part
                        that needs Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-school" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-chalk-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Plan this term
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#151c19] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
