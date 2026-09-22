<x-marketing-layout>
    <x-slot name="title">Museum Event Calendar | Tours, Talks and Family Days</x-slot>
    <x-slot name="description">Guided tours set once as recurring events, talks and family mornings with a place count per date, and memberships on one QR code. Free to start.</x-slot>
    <x-slot name="breadcrumbTitle">For Museums</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Museums",
        "description": "A museum event calendar for the programme on top of opening hours: recurring guided tours, lecture series, family sessions and late openings, each with its own capacity per date.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Museums, Science Centers, Heritage Sites & Historic Houses"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Museums",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Museum Programme Scheduling Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Guided tours as recurring events: daily, weekly on chosen days, or monthly by weekday such as the first Sunday",
            "Date exceptions for closure days and one-off extra dates",
            "Temporary exhibitions as a recurring event that ends on the closing date",
            "Free registration with a capacity, counted separately for each date, with a waitlist when a free session fills",
            "Sub-schedules for tours, talks, families, lates and learning, each with its own link",
            "One free appointment type for group and school visits, with weekly hours and a public booking page",
            "Ticketed late openings and special events with QR check-in, on the Pro plan",
            "Zero platform fees on ticket sales",
            "Memberships as passes redeemable across events on one QR code, on the Pro plan",
            "Event names and descriptions translated into one other language",
            "Embeddable calendar and a live calendar feed visitors subscribe to",
            "A digest of new events to confirmed email subscribers, and newsletters you write",
            "Post-event feedback from attendees, on the Pro plan",
            "Two-way Google, Outlook and CalDAV calendar sync"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "museum event calendar, museum tour booking, museum programme calendar, lecture series registration, museum membership pass, heritage site events",
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
        "name": "How to put a museum's programme online with Event Schedule",
        "description": "The galleries are open every day. The tours, talks and family sessions are the events, and each one is set up once.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set up the regular tours",
                "text": "Create each guided tour as a recurring event on the days it runs, and take out the dates the building is closed."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Add the talks and family sessions",
                "text": "Give each talk, family morning and late opening its own event, with a free place count per date or a ticket price on Pro."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Share one link",
                "text": "Embed the calendar on the museum website, give each strand its own link, and let visitors subscribe to the whole programme in their own calendar."
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
           For-museums "The Programme Card" styles.

           CONCEPT: a museum's opening hours are not events. Nobody puts
           "the galleries are open" in a diary. What people plan around is
           the printed programme card by the entrance: the 11am tour, the
           Thursday lecture, the Saturday family morning, the one late
           opening a month. So the hero IS that card, one week of it, and
           every row on it is an entry the museum makes once and leaves.

           CLAIM DISCIPLINE. No timed-entry general admission, no audio
           guides, no collections, no donations or Gift Aid: none of them
           exist in the app. A recurring event carries ONE start time, so
           two tour times are two recurring events, and the copy says so.
           Group visits use the one free appointment type; more types, a
           price on one and the approval step are Pro. Members-only
           previews are Unlisted (Enterprise), because Draft hides an
           event from everyone but the schedule's own team.

           COLOUR: verdigris, the green of weathered bronze. Accent
           #1d5c55 (hue 173) is 6.8:1 on the stone ground; #8fd3c7 is
           11:1 on the dark ground. Neighbours: /for-art-galleries is a
           quiet slate (205, sat 23%), /for-libraries is 43deg.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-muse-page { background-color: #f5f2ec; color: #171a19; }
        .dark .es-muse-page { background-color: #0f1211; color: #e9eeec; }
        .es-muse-ink { color: #171a19; }
        .dark .es-muse-ink { color: #e9eeec; }
        .es-muse-muted { color: #4d5550; }
        .dark .es-muse-muted { color: #9fa8a4; }
        .es-muse-accent { color: #1d5c55; }
        .dark .es-muse-accent { color: #8fd3c7; }
        .es-muse-lit { color: #8fd3c7; }

        .es-muse-grad {
            background-image: linear-gradient(100deg, #1d5c55, #2c7268);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-muse-grad,
        .es-muse-band .es-muse-grad {
            background-image: linear-gradient(100deg, #b5e6dc, #8fd3c7);
        }

        /* --- Surfaces --- */
        .es-muse-card {
            background-color: #fbfaf7;
            border: 1px solid rgba(23, 26, 25, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-muse-card {
            background-color: #171b1a;
            border-color: rgba(233, 238, 236, 0.12);
        }
        .es-muse-sub {
            background-color: rgba(23, 26, 25, 0.045);
            border-radius: 0.4rem;
        }
        .dark .es-muse-sub { background-color: rgba(233, 238, 236, 0.05); }
        .es-muse-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-muse-hover:hover { border-color: rgba(29, 92, 85, 0.45); box-shadow: 0 10px 28px -18px rgba(23, 26, 25, 0.5); }
        .dark .es-muse-hover:hover { border-color: rgba(143, 211, 199, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The programme card ---------------------------------------
           A printed card: cream stock, a verdigris header rule, rows in a
           tabular face. It is a fixed physical object, identical in both
           colour modes, so nothing inside it carries a .dark variant. */
        .es-muse-programme {
            background-color: #fdfbf6;
            color: #171a19;
            border-radius: 0.75rem;
            border: 1px solid rgba(23, 26, 25, 0.14);
            box-shadow: 0 30px 60px -30px rgba(15, 18, 17, 0.55);
        }
        .es-muse-programme-head {
            background-color: #1d5c55;
            color: #ffffff;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        .es-muse-programme-cap {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.82);
        }
        .es-muse-row { border-top: 1px solid rgba(23, 26, 25, 0.1); }
        .es-muse-day {
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #1d5c55;
        }
        .es-muse-row-muted { color: #4d5550; }
        .es-muse-closed { color: #6b716d; font-style: italic; }
        .es-muse-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* Strand marks. These are STATE (which sub-schedule an entry sits
           on), never a plan tier: the tier pills below are a different
           shape and vocabulary on purpose. */
        .es-muse-strand {
            display: inline-block;
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 2px;
            flex-shrink: 0;
        }
        .es-muse-s-tour { background-color: #1d5c55; }
        .es-muse-s-talk { background-color: #a0632a; }
        .es-muse-s-family { background-color: #3f7fa8; }
        .es-muse-s-late { background-color: #171a19; }

        /* --- Eyebrow, chips, plan tags --- */
        .es-muse-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #1d5c55;
        }
        .dark .es-muse-tag { color: #8fd3c7; }
        .es-muse-band .es-muse-tag { color: #8fd3c7; }

        /* The chip is a gallery room number: a square plate. */
        .es-muse-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.4rem;
            height: 2.4rem;
            border: 1px solid rgba(29, 92, 85, 0.45);
            border-radius: 0.35rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 700;
            color: #1d5c55;
        }
        .dark .es-muse-chip { border-color: rgba(143, 211, 199, 0.45); color: #8fd3c7; }
        .es-muse-band .es-muse-chip { border-color: rgba(143, 211, 199, 0.45); color: #8fd3c7; }

        /* Plan tiers ONLY. */
        .es-muse-plan {
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
        .es-muse-plan-free { border-color: rgba(23, 26, 25, 0.22); color: #4d5550; }
        .dark .es-muse-plan-free { border-color: rgba(233, 238, 236, 0.26); color: #9fa8a4; }
        .es-muse-plan-pro { border-color: rgba(29, 92, 85, 0.5); color: #1d5c55; background: rgba(29, 92, 85, 0.08); }
        .dark .es-muse-plan-pro { border-color: rgba(143, 211, 199, 0.42); color: #8fd3c7; background: rgba(143, 211, 199, 0.1); }
        .es-muse-plan-ent { border-color: rgba(23, 26, 25, 0.5); color: #171a19; background: rgba(23, 26, 25, 0.06); }
        .dark .es-muse-plan-ent { border-color: rgba(233, 238, 236, 0.45); color: #e9eeec; background: rgba(233, 238, 236, 0.08); }

        /* --- Buttons --- */
        .es-muse-btn {
            background-color: #1d5c55;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-muse-btn:hover { background-color: #154842; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(29, 92, 85, 0.9); }
        .es-muse-ghost {
            border: 1px solid rgba(23, 26, 25, 0.22);
            color: #171a19;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-muse-ghost:hover { border-color: rgba(29, 92, 85, 0.5); background-color: rgba(29, 92, 85, 0.06); }
        .dark .es-muse-ghost { border-color: rgba(233, 238, 236, 0.24); color: #e9eeec; }
        .dark .es-muse-ghost:hover { border-color: rgba(143, 211, 199, 0.45); background-color: rgba(143, 211, 199, 0.08); }

        /* --- The dark band --- */
        .es-muse-band {
            background-color: #111615;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(29, 92, 85, 0.55), rgba(29, 92, 85, 0) 70%),
                linear-gradient(180deg, #161c1b, #111615);
        }
        .es-muse-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 238, 236, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 238, 236, 0.05) 1px, transparent 1px);
        }
        .es-muse-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-muse-band .es-claim:focus-within {
            border-color: rgba(143, 211, 199, 0.75);
            box-shadow: 0 0 0 4px rgba(143, 211, 199, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(29, 92, 85, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(143, 211, 199, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #1d5c55; }
        .dark .es-dot.is-active .es-dot-pip { background: #8fd3c7; }

        #es-muse-page a:focus-visible,
        #es-muse-page summary:focus-visible,
        #es-muse-page button:focus-visible,
        #es-muse-page input:focus-visible {
            outline: 2px solid #1d5c55;
            outline-offset: 2px;
        }
        .dark #es-muse-page a:focus-visible,
        .dark #es-muse-page summary:focus-visible,
        .dark #es-muse-page button:focus-visible,
        .dark #es-muse-page input:focus-visible {
            outline-color: #8fd3c7;
        }
        .es-muse-band a:focus-visible,
        .es-muse-band summary:focus-visible,
        .es-muse-band button:focus-visible,
        .es-muse-band input:focus-visible {
            outline-color: #8fd3c7 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-muse-btn:hover { transform: none; }
        }
    </style>

    @php
        // One week of the programme card. Every count below is derived from
        // this array, so the hero copy and the card cannot drift apart.
        // 'rec' marks an entry that is one recurring event (made once);
        // anything without it is a one-off.
        $week = [
            ['Mon', null],
            ['Tue', [['11:00', 'Highlights tour', 'tour', 'rec', '20 places']]],
            ['Wed', [['11:00', 'Highlights tour', 'tour', 'rec', '20 places'], ['14:00', 'Curator\'s tour', 'tour', 'rec', '15 places']]],
            ['Thu', [['11:00', 'Highlights tour', 'tour', 'rec', '20 places'], ['18:30', 'Lecture: the river trade', 'talk', 'rec', '80 places']]],
            ['Fri', [['11:00', 'Highlights tour', 'tour', 'rec', '20 places'], ['19:00', 'Late opening', 'late', null, 'Ticketed']]],
            ['Sat', [['10:00', 'Family morning', 'family', 'rec', '30 places'], ['11:00', 'Highlights tour', 'tour', 'rec', '20 places']]],
            ['Sun', [['11:00', 'Highlights tour', 'tour', 'rec', '20 places'], ['14:00', 'Curator\'s tour', 'tour', 'rec', '15 places']]],
        ];

        $rows = [];
        foreach ($week as [$d, $items]) {
            foreach ($items ?? [] as $it) {
                $rows[] = $it;
            }
        }
        $sessionCount = count($rows);
        $madeOnce = count(array_unique(array_map(fn ($r) => $r[1], array_filter($rows, fn ($r) => $r[3] === 'rec'))));
        $oneOffs = count(array_filter($rows, fn ($r) => $r[3] === null));
        $entries = $madeOnce + $oneOffs;

        $strandLabels = [
            'tour' => 'Tours',
            'talk' => 'Talks',
            'family' => 'Families',
            'late' => 'Lates',
        ];

        $faqs = [
            [
                'q' => 'Does Event Schedule sell general admission to the museum?',
                'a' => 'It is built for the programme, not the front door. Tours, talks, family sessions, lates and special events are what it handles well: each one is an event with its own date, its own place count and its own page. If your galleries are free to walk into, that is the whole job. If you charge for admission, keep doing that the way you do now and use Event Schedule for everything that happens on top of it.',
            ],
            [
                'q' => 'We run the same tour at 11am and 2pm. Is that one event?',
                'a' => 'It is two. A recurring event carries a single start time, so each tour time is its own recurring event: set the 11am on the days it runs, set the 2pm on its days, and both then appear on every date without you touching them again. That is two entries for the whole season rather than two hundred, and each time keeps its own place count, so a full morning tour does not stop anyone booking the afternoon one.',
            ],
            [
                'q' => 'How do we handle closure days and the Christmas shutdown?',
                'a' => 'With date exceptions on the recurring event. Take out the day you close for an install, a bank holiday or the week between Christmas and New Year, and the tour simply does not appear on those dates. The same mechanism adds a one-off date outside the usual pattern, such as an extra tour during half term, without disturbing anything else.',
            ],
            [
                'q' => 'Can schools and groups book a visit through it?',
                'a' => 'Yes, through appointment booking. The free plan gives you one appointment type: write down the hours your learning team takes group visits, and a teacher picks an open time on a public booking page and gets a confirmation. Pro adds more types (say, one for schools and one for adult groups), a price on a type, and advanced rules such as buffers between visits, minimum notice and an approval step. On the hosted service the confirmation and reminder emails need the schedule\'s own email settings.',
            ],
            [
                'q' => 'Can members get into events free?',
                'a' => 'A membership can be sold as a pass on the Pro plan. It is used across events rather than for one, it lives on a single QR code, and each use is counted on the Subscriptions tab, so you can see how often a member actually comes. You can set a cancellation deadline for bookings made with it. Pro is '.plan_price($proMonthly).' a month, with zero platform fees on the passes and tickets you sell.',
            ],
            [
                'q' => 'Can we hold a members-only preview evening?',
                'a' => 'Two ways, depending on who should see it. A Draft event is free and visible only to the people on your schedule\'s own team, which suits planning an exhibition before it is announced. For a preview that members can reach but the public cannot find, the Unlisted setting keeps the event off your calendar while anyone with the link can open it, optionally behind a password. Unlisted and Internal events are on the Enterprise plan.',
            ],
            [
                'q' => 'Many of our visitors are tourists. Can the calendar be in two languages?',
                'a' => 'Yes. Your schedule is written in one language, and you can nominate one other language for event names and descriptions to be translated into by AI, with a language switch on your public page. That is free. It is one extra language at a time, so pick the one most of your visitors read.',
            ],
            [
                'q' => 'What does it cost a museum?',
                'a' => 'The programme itself is free: recurring tours, date exceptions, talks and family sessions with free registration and a place count, sub-schedules for each strand, one appointment type for groups, an embeddable calendar, a live calendar feed and calendar sync. Pro, at '.plan_price($proMonthly).' a month, is for putting a price on a ticket, selling memberships as passes, gift cards, the live check-in dashboard and post-event feedback. Enterprise adds members-only previews, reserved seating and more than one team member.',
            ],
        ];

        $dotSections = [
            ['top', 'The programme'],
            ['tours', 'Tours'],
            ['strands', 'Strands'],
            ['places', 'Places'],
            ['members', 'Members'],
            ['groups', 'Groups'],
            ['visitors', 'Visitors'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-muse-page" class="es-muse-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the programme card                                  -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 24% 32%, rgba(29, 92, 85, 0.2), rgba(29, 92, 85, 0) 62%); opacity: 0.55;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 62%, rgba(143, 211, 199, 0.16), rgba(143, 211, 199, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-muse-tag es-fade-up es-d-1 mb-5">Museum event calendar, for heritage sites too</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Nobody plans a day</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">around <span class="es-muse-grad">opening hours</span>.</span></span>
                    </h1>

                    <p class="es-muse-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        They plan around the 11 o'clock tour, the Thursday lecture and the Saturday
                        family morning. That programme is what goes in the diary, so it is what
                        belongs on a calendar people can find, book and subscribe to.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-muse-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Publish the programme
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#tours" class="es-muse-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how tours repeat
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The programme card: one week, every row an entry made once. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-muse-programme overflow-hidden">
                        <div class="es-muse-programme-head flex items-baseline justify-between gap-3 px-6 py-4">
                            <div>
                                <p class="es-muse-programme-cap">What's on this week</p>
                                <p class="mt-1 text-lg font-bold">Tours, talks &amp; families</p>
                            </div>
                            <p class="es-muse-num text-xs text-white/80">Galleries open daily</p>
                        </div>

                        <div class="px-6 pb-4">
                            @foreach ($week as [$day, $items])
                                <div class="es-muse-row flex gap-4 py-2.5 first:border-t-0">
                                    <p class="es-muse-day w-9 shrink-0 pt-0.5">{{ $day }}</p>
                                    <div class="min-w-0 flex-1 space-y-1">
                                        @if ($items === null)
                                            <p class="es-muse-closed text-sm">Closed</p>
                                        @else
                                            @foreach ($items as [$time, $name, $strand, $rec, $note])
                                                <div class="flex items-center gap-2 text-sm">
                                                    <span class="es-muse-strand es-muse-s-{{ $strand }}" aria-hidden="true"></span>
                                                    <span class="es-muse-num w-11 shrink-0 text-xs font-semibold">{{ $time }}</span>
                                                    <span class="min-w-0 flex-1 truncate font-semibold">{{ $name }}</span>
                                                    <span class="es-muse-row-muted es-muse-num hidden shrink-0 text-xs sm:inline">{{ $note }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-3 gap-3 border-t border-[rgba(23,26,25,0.12)] bg-[rgba(29,92,85,0.06)] px-6 py-4">
                            <div>
                                <p class="es-muse-num text-2xl font-bold">{{ $sessionCount }}</p>
                                <p class="es-muse-row-muted text-[0.65rem] font-bold uppercase tracking-wider">Sessions</p>
                            </div>
                            <div>
                                <p class="es-muse-num text-2xl font-bold">{{ $madeOnce }}</p>
                                <p class="es-muse-row-muted text-[0.65rem] font-bold uppercase tracking-wider">Recurring</p>
                            </div>
                            <div>
                                <p class="es-muse-num text-2xl font-bold">{{ $oneOffs }}</p>
                                <p class="es-muse-row-muted text-[0.65rem] font-bold uppercase tracking-wider">One-off</p>
                            </div>
                        </div>
                    </div>

                    <p class="es-muse-muted mt-5 text-sm">
                        {{ $sessionCount }} sessions this week, from {{ $entries }} entries. Next week they are all there again.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Tours (01)                                                -->
    <!-- ============================================================ -->
    <section id="tours" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">01</div>
                    <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Tours</p>
                    <h2 class="es-balance es-muse-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Write the tour once. <span class="es-muse-grad">It runs all season.</span>
                    </h2>
                    <p class="es-muse-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A daily highlights tour is one recurring event, not a line you copy into next
                        month's calendar. Pick the pattern, and every date appears on its own with its
                        own place count.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Daily, weekly or monthly', 'Every day, Tuesday to Sunday, every other Saturday, or the first Sunday of the month for the conservator\'s talk.'],
                            ['One start time per event', 'The 11am and the 2pm tour are two recurring events, so each keeps its own capacity and its own waiting list.'],
                            ['Closure days come out', 'Date exceptions remove the install week and the bank holiday, and add a half-term extra, without breaking the pattern.'],
                            ['Exhibitions have an end date', 'A temporary exhibition can be a recurring event that stops itself on the day it closes, with its tours alongside.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-muse-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-muse-ink font-semibold">{{ $t }}</span> <span class="es-muse-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-muse-plan es-muse-plan-free">Free plan</span>
                        <span class="es-muse-muted ml-2 text-sm">Recurring events, end dates and date exceptions are all free.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-muse-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-muse-ink text-lg font-bold">The highlights tour, set up once</h3>
                            <span class="es-muse-muted es-muse-num text-xs">1 event</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Event', 'Highlights tour'],
                                ['Starts', '11:00, for one hour'],
                                ['Repeats', 'Tue, Wed, Thu, Fri, Sat, Sun'],
                                ['Places', '20 per date, free to book'],
                                ['Not on', '24 Dec to 1 Jan'],
                            ] as [$fLabel, $fValue])
                                <div class="es-muse-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-muse-muted w-20 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                    <span class="es-muse-ink es-muse-num min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-muse-muted mt-5 border-t border-[rgba(23,26,25,0.1)] pt-4 text-xs dark:border-[rgba(233,238,236,0.12)]">
                            Six days a week, all year, minus the week you close. One entry.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Strands (02)                                              -->
    <!-- ============================================================ -->
    <section id="strands" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">02</div>
                <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Strands</p>
                <h2 class="es-balance es-muse-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One programme, <span class="es-muse-grad">four front doors</span>.
                </h2>
                <p class="es-muse-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A parent looking for Saturday morning does not want to scroll past the lecture
                    series. Sub-schedules keep each strand apart, each with a link of its own, while
                    the full calendar still shows everything together.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="90">
                @foreach ([
                    ['tour', 'Tours', 'The daily highlights, the curator\'s walk and the monthly behind-the-scenes, for visitors deciding when to come.'],
                    ['talk', 'Talks', 'The lecture series and book launches, on the link you send to the local history society and the university.'],
                    ['family', 'Families', 'Toddler mornings and holiday workshops, on the link that goes in the school newsletter and the parents\' group.'],
                    ['late', 'Lates', 'The adults-only evenings, with music, a bar and a ticket, on the link you give to the listings sites.'],
                ] as [$sKey, $sName, $sText])
                    <div class="es-muse-card es-muse-hover flex flex-col p-6" data-reveal>
                        <div class="flex items-center gap-2">
                            <span class="es-muse-strand es-muse-s-{{ $sKey }}" aria-hidden="true"></span>
                            <h3 class="es-muse-ink text-lg font-bold">{{ $sName }}</h3>
                        </div>
                        <p class="es-muse-muted mt-2 text-sm">{{ $sText }}</p>
                        <p class="es-muse-accent es-muse-num mt-auto pt-4 text-xs font-semibold">/{{ strtolower($sName) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['On your own website', 'Paste one embed code into the museum site and the calendar there is always the current one, with no web team in the loop.'],
                    ['In their own calendar', 'Visitors subscribe to the live calendar feed once, and a moved lecture moves in their phone too.'],
                    ['Synced both ways', 'Two-way sync with Google, Outlook or CalDAV, so the learning team\'s shared calendar and the public one agree.'],
                ] as [$t, $d])
                    <div class="es-muse-card flex flex-col p-5" data-reveal>
                        <p class="es-muse-ink text-sm font-bold">{{ $t }}</p>
                        <p class="es-muse-muted mt-1 text-sm">{{ $d }}</p>
                        <p class="mt-auto pt-3"><span class="es-muse-plan es-muse-plan-free">Free plan</span></p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Places (03)                                               -->
    <!-- ============================================================ -->
    <section id="places" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">03</div>
                <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Places</p>
                <h2 class="es-balance es-muse-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Most of it is free. <span class="es-muse-grad">All of it is counted.</span>
                </h2>
                <p class="es-muse-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A tour group of twenty is a limit set by the gallery floor, not by a price. The
                    lecture theatre holds eighty. The one evening a month with a bar is the one that
                    earns a ticket.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-muse-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-muse-ink text-lg font-bold">Tours, talks and family mornings</h3>
                        <span class="es-muse-plan es-muse-plan-free">Free plan</span>
                    </div>
                    <p class="es-muse-muted mb-5 text-sm">Free registration, as much of it as you like.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'A place count on each event, kept separately for every date of a recurring tour.',
                            'Booking closes on its own when the places are gone, and a waiting list takes the next names.',
                            'Each person who registers gets a QR code, and scanning it at the desk is free.',
                            'Ask for each guest\'s name on a family booking, so the register at the door is real.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-muse-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-muse-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-muse-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-muse-ink text-lg font-bold">The late opening and the gala</h3>
                        <span class="es-muse-plan es-muse-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-muse-muted mb-5 text-sm">The evenings worth putting a price on.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Named ticket types, such as standard, concession and member, each with its own price and stock.',
                            'Paid into your own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash, with zero platform fees.',
                            'A live check-in dashboard counting the room as people arrive, and a waitlist that offers a returned ticket to the next person.',
                            'Refunds from the Sales page, in full or in part, if the evening is moved.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-muse-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-muse-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <p class="es-muse-muted mx-auto mt-8 max-w-3xl text-center text-sm" data-reveal>
                A lecture theatre with numbered seats can use reserved seating, where people pick their
                own seat from the plan. That is on the
                <span class="es-muse-plan es-muse-plan-ent align-middle">Enterprise plan</span>.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Members (04)                                              -->
    <!-- ============================================================ -->
    <section id="members" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-muse-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-muse-ink text-lg font-bold">A member's year, on one code</h3>
                                <span class="es-muse-plan es-muse-plan-pro">Pro plan</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Mar', 'Lecture: the river trade', 'Used'],
                                    ['May', 'Curator\'s tour', 'Used'],
                                    ['Jul', 'Family morning', 'Used'],
                                    ['Oct', 'Late opening', 'Booked'],
                                ] as [$m, $what, $state])
                                    <div class="es-muse-sub flex items-baseline justify-between gap-3 p-3.5">
                                        <span class="es-muse-muted es-muse-num w-10 shrink-0 text-xs uppercase tracking-wider">{{ $m }}</span>
                                        <span class="es-muse-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $what }}</span>
                                        <span class="es-muse-muted es-muse-num shrink-0 text-xs">{{ $state }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-muse-muted mt-5 border-t border-[rgba(23,26,25,0.1)] pt-4 text-xs dark:border-[rgba(233,238,236,0.12)]">
                                Every use is counted on the Subscriptions tab, so you know which members actually come.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">04</div>
                    <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Members</p>
                    <h2 class="es-balance es-muse-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The Friends scheme <span class="es-muse-grad">in their pocket</span>.
                    </h2>
                    <p class="es-muse-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A membership is a pass, not a ticket: bought once, used across the year's
                        programme, and scanned at the door from the same QR code every time.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Passes across events', 'Sell an annual membership or a season of lectures as one pass that books into any event it covers.', 'pro'],
                            ['A cancellation rule you choose', 'Set how late a member may cancel a booking, and whether a late cancel forfeits the visit.', 'pro'],
                            ['Gift memberships', 'Gift cards carry a balance a recipient spends on tickets for any event on the schedule. On the hosted service they need the schedule\'s own email settings, since the email is the delivery.', 'pro'],
                            ['A preview only members can reach', 'An Unlisted event stays off the public calendar while anyone holding the link can open it, with an optional password.', 'ent'],
                        ] as [$t, $d, $tier])
                            <div class="es-muse-card es-muse-hover p-4" data-reveal>
                                <div class="flex flex-wrap items-baseline justify-between gap-2">
                                    <p class="es-muse-ink text-sm font-bold">{{ $t }}</p>
                                    @if ($tier === 'pro')
                                        <span class="es-muse-plan es-muse-plan-pro">Pro</span>
                                    @else
                                        <span class="es-muse-plan es-muse-plan-ent">Enterprise</span>
                                    @endif
                                </div>
                                <p class="es-muse-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Groups and schools (05)                                   -->
    <!-- ============================================================ -->
    <section id="groups" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">05</div>
                <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Groups and schools</p>
                <h2 class="es-balance es-muse-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Class visits, <span class="es-muse-grad">without the phone tag</span>.
                </h2>
                <p class="es-muse-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A teacher planning a trip wants to see an open slot and take it. Write down the
                    hours the learning team takes visits, and the booking page does the rest.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-muse-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-muse-ink text-lg font-bold">One kind of visit</h3>
                        <span class="es-muse-plan es-muse-plan-free">Free plan</span>
                    </div>
                    <p class="es-muse-muted mb-5 text-sm">Enough for a museum with one schools offer.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'One appointment type, such as a 90-minute school visit, with weekly hours and a slot interval.',
                            'A public booking page where a teacher picks an open time.',
                            'Confirmation, reminder and cancellation emails, and a private link to move the booking. On the hosted service these need the schedule\'s own email settings.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-muse-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-muse-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-muse-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-muse-ink text-lg font-bold">A full learning programme</h3>
                        <span class="es-muse-plan es-muse-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-muse-muted mb-5 text-sm">When schools, adult groups and handling sessions are all different.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'As many appointment types as you offer, each with its own hours and length.',
                            'A price on a type, paid by Stripe, a payment link or cash, with no platform fee.',
                            'Buffers to reset a room between groups, minimum notice, a booking window and an approval step before a slot is confirmed.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-muse-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-muse-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Visitors (06)                                             -->
    <!-- ============================================================ -->
    <section id="visitors" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">06</div>
                    <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Visitors</p>
                    <h2 class="es-balance es-muse-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Reach the people <span class="es-muse-grad">who came once</span>.
                    </h2>
                    <p class="es-muse-muted mb-6 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        The visitor who loved the Saturday talk is the one most likely to come to the
                        next. Give them a way to hear about it that does not depend on a social feed
                        deciding who sees what.
                    </p>
                    <p class="es-muse-muted max-w-xl text-base leading-relaxed" data-reveal>
                        Anyone who leaves an email address on your page and confirms it gets a short
                        digest when you publish new events, at most one every three days, and it does not
                        draw on your newsletter allowance. When there is more to say than dates, you write
                        a newsletter yourself. For the detail, see
                        <x-link href="{{ marketing_url('/features/newsletters') }}">how newsletters work</x-link>.
                    </p>
                </div>

                <div class="space-y-3" data-reveal-group="90">
                    @foreach ([
                        ['Two languages on one page', 'Nominate one other language and event names and descriptions are translated into it by AI, with a switch on your public page for the visitors who read it.', 'Free plan'],
                        ['A digest to confirmed subscribers', 'New events go out to confirmed email subscribers in one short message, at most every 72 hours.', 'Free plan'],
                        ['Newsletters you write', 'Counted per recipient: 10 a month on the free plan, 100 on Pro and 1,000 on Enterprise.', 'Free plan'],
                        ['Photos from the day', 'Visitors add photos and comments to an event, held for your approval. 25 photos on the free plan, no cap on Pro.', 'Free plan'],
                        ['What they thought', 'After an event, attendees are asked for a star rating and a comment, so the next season is planned on evidence.', 'Pro plan'],
                    ] as [$t, $d, $tier])
                        <div class="es-muse-card es-muse-hover p-4" data-reveal>
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="es-muse-ink text-sm font-bold">{{ $t }}</p>
                                <span class="es-muse-plan {{ $tier === 'Pro plan' ? 'es-muse-plan-pro' : 'es-muse-plan-free' }}">{{ $tier }}</span>
                            </div>
                            <p class="es-muse-muted mt-1 text-sm">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Who it is for (07)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">07</div>
                <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-muse-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Anywhere the building is open <span class="es-muse-grad">and the programme is the reason to come</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                <x-sub-audience-card
                    name="History Museums"
                    description="Guided tours on set days, a lecture series with capped places and an anniversary evening worth putting a price on."
                    icon-color="amber"
                    blog-slug="for-history-museums"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Science Centers"
                    description="Planetarium shows, demonstrations and adults-only late openings, each on its own strand with its own link to share."
                    icon-color="sky"
                    blog-slug="for-science-centers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6M10 3v6.5L4.5 19a1.5 1.5 0 001.3 2.2h12.4a1.5 1.5 0 001.3-2.2L14 9.5V3M7 15h10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Children's Museums"
                    description="Toddler mornings and holiday workshops with a cap on each session, and a calendar parents subscribe to once."
                    icon-color="rose"
                    blog-slug="for-childrens-museums"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 20h16M6 20V10h5v10M13 20v-6h5v6M6 10l2.5-4L11 10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Heritage Sites & Historic Houses"
                    description="Seasonal opening, costumed tours and open-air theatre in the grounds, with a membership pass that covers every visit."
                    icon-color="emerald"
                    blog-slug="for-heritage-sites"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-3 4-6 6-6 10a6 6 0 0012 0c0-4-3-6-6-10zm0 18v-6" />
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
        <div class="es-muse-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">08</div>
                    <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set up in a morning, <span class="es-muse-grad">good for the season</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Set up the regular tours', 'Each tour time is one recurring event on the days it runs. Take out the closure days and it looks after itself.'],
                        ['02', 'Add the talks and family sessions', 'Each gets a place count per date. The late opening gets a ticket price on Pro, and members book in with their pass.'],
                        ['03', 'Share one link', 'Embed it on the museum site, hand each strand its own link, and let visitors subscribe to the programme in their own calendar.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-muse-lit es-muse-num mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-muse-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Daily and weekly tours set once, with closure days taken out" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Tours, talks, families and lates, each on its own link" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Passes & Subscriptions" description="Memberships used across the year's programme on one QR code, on the Pro plan" :url="marketing_url('/features/passes')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Appointments" description="School and group visits booked into the hours you set" :url="marketing_url('/features/appointments')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="The programme on the museum website, always current" :url="marketing_url('/features/embed-calendar')" icon-color="slate">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-muse-accent inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-[rgba(23,26,25,0.1)] py-16 dark:border-[rgba(233,238,236,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-muse-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-art-galleries', 'Art Galleries'],
                    ['/for-libraries', 'Libraries'],
                    ['/for-schools', 'Schools'],
                    ['/for-community-centers', 'Community Centers'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-muse-card es-muse-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-muse-muted text-sm">Event Schedule for</div>
                            <div class="es-muse-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-muse-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-muse-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(23,26,25,0.1)] py-20 dark:border-[rgba(233,238,236,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-muse-chip mb-6" data-reveal aria-hidden="true">09</div>
                <p class="es-muse-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-muse-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked by <span class="es-muse-grad">the learning team</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-muse-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-muse-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-muse-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-muse-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-muse-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-muse-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Print the programme card <span class="es-muse-grad">for the last time</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Recurring tours, talks with a place count, family mornings, a booking page for
                        school visits and a calendar visitors subscribe to all cost nothing. Tickets with
                        a price and memberships are the parts that need Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-museum" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-muse-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Publish the programme
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#171b1a] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
