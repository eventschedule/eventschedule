<x-marketing-layout>
    <x-slot name="title">Meetup Group Events | Free RSVPs on a Page You Own</x-slot>
    <x-slot name="description">Run your meetup group events on a page the group owns: recurring dates set once, free RSVPs with a cap and a waitlist, and no organizer fee to keep it.</x-slot>
    <x-slot name="breadcrumbTitle">For Meetup Groups</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Meetup Groups",
        "description": "A group page with its own link, recurring meetups set once, free RSVPs with a capacity and a waitlist, and email to the members who sign up. Free forever, with no organizer subscription.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Meetup Groups, Clubs & Community Organizers"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Meetup Groups",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Meetup and Community Event Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "A group page with its own link that the group owns",
            "Recurring meetups: weekly, every other week, or monthly by weekday such as the first Tuesday",
            "Date exceptions to skip a week or add a one-off date",
            "Free RSVP with a capacity counted separately for every date, unlimited on every plan",
            "A waitlist for a full RSVP event, free",
            "Per-guest registration so each person in a party gets their own confirmation",
            "A digest of new dates to confirmed email subscribers, at most one every 72 hours",
            "Newsletters to members, within a monthly allowance counted per recipient",
            "Speakers and hosts listed by name, with a claimable page for anyone not on Event Schedule yet",
            "Online and hybrid meetups with the join link printed on the ticket",
            "Two-way Google, Outlook and CalDAV calendar sync, plus a live feed members subscribe to",
            "Embeddable calendar for the group's own website",
            "Event names and descriptions translated into one other language",
            "Member photos and comments held in an approval queue (25 photos on the free plan)",
            "Polls and carpool matching, on the Pro plan",
            "Paid tickets for the occasional workshop or dinner, with zero platform fees, on the Pro plan",
            "Backup and restore, open source, and selfhostable"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "meetup group events, meetup rsvp, free meetup page, community group calendar, recurring meetup schedule, meetup organizer tools",
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
        "name": "How to run meetup group events on Event Schedule",
        "description": "Set the rhythm once, put a cap on the room, and let members subscribe.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Make the group page",
                "text": "Create a schedule for the group. It gets its own link, which you share wherever the group already talks."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Set the rhythm",
                "text": "Add the meetup as a recurring event, such as every other Thursday or the first Tuesday of the month, and take out any date that does not happen."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Open the RSVPs",
                "text": "Switch on free registration with a capacity. Each date keeps its own count, and a waitlist can take over when the room is full."
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
           For-meetup-groups "The Headcount" styles.

           CONCEPT: a meetup group's whole question every fortnight is
           WHO IS COMING. Not who bought a ticket - almost nobody pays -
           but how many chairs, how much pizza, whether the room at the
           back of the bar is big enough. So the device is the RSVP card
           with its capacity meter, and the meter repeats down the page:
           every date keeps its own count.

           COLOUR: tangerine-red, the warm "sign-up sheet on the door"
           hue. Accent #a3361f (hue ~10) light; #ff9f85 dark. Deliberately
           darker and redder than /for-community-centers' kraft brown so
           the two never read as the same page. NEVER purple family.

           Text on ground: #a3361f on #f7f4f1 ~6.3:1; #ff9f85 on #121011
           ~9.5:1. Muted #57504c light (7.2) / #a9a19c dark (7.4).
           ============================================================== */

        .es-meet-page { background-color: #f7f4f1; color: #1b1715; }
        .dark .es-meet-page { background-color: #121011; color: #f1ecea; }
        .es-meet-ink { color: #1b1715; }
        .dark .es-meet-ink { color: #f1ecea; }
        .es-meet-muted { color: #57504c; }
        .dark .es-meet-muted { color: #a9a19c; }
        .es-meet-accent { color: #a3361f; }
        .dark .es-meet-accent { color: #ff9f85; }
        .es-meet-lit { color: #ff9f85; }

        .es-meet-grad {
            background-image: linear-gradient(100deg, #a3361f, #c2521f);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-meet-grad,
        .es-meet-band .es-meet-grad {
            background-image: linear-gradient(100deg, #ffc2a8, #ff9f85);
        }

        .es-meet-card {
            background-color: #fdfbf9;
            border: 1px solid rgba(27, 23, 21, 0.12);
            border-radius: 0.9rem;
        }
        .dark .es-meet-card {
            background-color: #1c1819;
            border-color: rgba(241, 236, 234, 0.12);
        }
        .es-meet-sub {
            background-color: rgba(27, 23, 21, 0.045);
            border-radius: 0.55rem;
        }
        .dark .es-meet-sub { background-color: rgba(241, 236, 234, 0.05); }
        .es-meet-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-meet-hover:hover { border-color: rgba(163, 54, 31, 0.45); box-shadow: 0 10px 28px -18px rgba(27, 23, 21, 0.5); }
        .dark .es-meet-hover:hover { border-color: rgba(255, 159, 133, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The RSVP card --------------------------------------------- */
        .es-meet-panel { background-color: #fdfbf9; border: 1px solid rgba(27, 23, 21, 0.14); border-radius: 1.1rem; }
        .dark .es-meet-panel { background-color: #1c1819; border-color: rgba(241, 236, 234, 0.14); }
        .es-meet-meter { height: 0.6rem; border-radius: 999px; background-color: rgba(27, 23, 21, 0.1); overflow: hidden; }
        .dark .es-meet-meter { background-color: rgba(241, 236, 234, 0.1); }
        .es-meet-fill { height: 100%; border-radius: 999px; background-color: #a3361f; }
        .dark .es-meet-fill { background-color: #ff9f85; }
        .es-meet-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-meet-going {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 999px;
            padding: 0.2rem 0.6rem;
            font-size: 0.7rem;
            font-weight: 700;
            background-color: rgba(163, 54, 31, 0.1);
            color: #8a2c18;
        }
        .dark .es-meet-going { background-color: rgba(255, 159, 133, 0.12); color: #ffb39d; }

        /* --- Eyebrow, chips, plan tags --- */
        .es-meet-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #a3361f;
        }
        .dark .es-meet-tag { color: #ff9f85; }
        .es-meet-band .es-meet-tag { color: #ff9f85; }

        /* The chip is a name badge: "HELLO, my number is". */
        .es-meet-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.6rem;
            height: 1.9rem;
            padding: 0 0.6rem;
            border-radius: 0.45rem;
            border-top: 5px solid #a3361f;
            background-color: #fdfbf9;
            box-shadow: 0 1px 0 rgba(27, 23, 21, 0.12), 0 6px 16px -10px rgba(27, 23, 21, 0.5);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 700;
            color: #1b1715;
        }
        .dark .es-meet-chip { background-color: #251f20; border-top-color: #ff9f85; color: #f1ecea; }
        .es-meet-band .es-meet-chip { background-color: #251f20; border-top-color: #ff9f85; color: #f1ecea; }

        /* Plan tiers ONLY. */
        .es-meet-plan {
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
        .es-meet-plan-free { border-color: rgba(27, 23, 21, 0.22); color: #57504c; }
        .dark .es-meet-plan-free { border-color: rgba(241, 236, 234, 0.26); color: #a9a19c; }
        .es-meet-plan-pro { border-color: rgba(163, 54, 31, 0.5); color: #a3361f; background: rgba(163, 54, 31, 0.08); }
        .dark .es-meet-plan-pro { border-color: rgba(255, 159, 133, 0.42); color: #ff9f85; background: rgba(255, 159, 133, 0.1); }
        .es-meet-plan-ent { border-color: rgba(27, 23, 21, 0.45); color: #1b1715; background: rgba(27, 23, 21, 0.06); }
        .dark .es-meet-plan-ent { border-color: rgba(241, 236, 234, 0.4); color: #f1ecea; background: rgba(241, 236, 234, 0.08); }

        /* --- Buttons --- */
        .es-meet-btn {
            background-color: #a3361f;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-meet-btn:hover { background-color: #862b18; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(163, 54, 31, 0.9); }
        .es-meet-ghost {
            border: 1px solid rgba(27, 23, 21, 0.22);
            color: #1b1715;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-meet-ghost:hover { border-color: rgba(163, 54, 31, 0.5); background-color: rgba(163, 54, 31, 0.06); }
        .dark .es-meet-ghost { border-color: rgba(241, 236, 234, 0.24); color: #f1ecea; }
        .dark .es-meet-ghost:hover { border-color: rgba(255, 159, 133, 0.45); background-color: rgba(255, 159, 133, 0.08); }

        /* --- The dark band --- */
        .es-meet-band {
            background-color: #161213;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(163, 54, 31, 0.45), rgba(163, 54, 31, 0) 70%),
                linear-gradient(180deg, #1c1718, #161213);
        }
        .es-meet-band .grid-overlay {
            background-image:
                linear-gradient(rgba(241, 236, 234, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(241, 236, 234, 0.05) 1px, transparent 1px);
        }
        .es-meet-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-meet-band .es-claim:focus-within {
            border-color: rgba(255, 159, 133, 0.75);
            box-shadow: 0 0 0 4px rgba(255, 159, 133, 0.22);
        }

        .es-dot:hover .es-dot-pip { background-color: rgba(163, 54, 31, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(255, 159, 133, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #a3361f; }
        .dark .es-dot.is-active .es-dot-pip { background: #ff9f85; }

        #es-meet-page a:focus-visible,
        #es-meet-page summary:focus-visible,
        #es-meet-page button:focus-visible,
        #es-meet-page input:focus-visible {
            outline: 2px solid #a3361f;
            outline-offset: 2px;
        }
        .dark #es-meet-page a:focus-visible,
        .dark #es-meet-page summary:focus-visible,
        .dark #es-meet-page button:focus-visible,
        .dark #es-meet-page input:focus-visible {
            outline-color: #ff9f85;
        }
        .es-meet-band a:focus-visible,
        .es-meet-band summary:focus-visible,
        .es-meet-band button:focus-visible,
        .es-meet-band input:focus-visible {
            outline-color: #ff9f85 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-meet-btn:hover { transform: none; }
        }
    </style>

    @php
        // Every other Thursday, four dates from the first. Each keeps its own
        // count, which is the point the card is making.
        $firstMeet = new DateTimeImmutable('2026-10-08'); // Thu
        $capacity = 40;
        $goingCounts = [38, 21, 9, 0];
        $meets = [];
        foreach ($goingCounts as $i => $going) {
            $meets[] = ['date' => $firstMeet->modify('+'.($i * 14).' days'), 'going' => $going];
        }

        $faqs = [
            [
                'q' => 'Does it cost anything to keep a group going?',
                'a' => 'No. The free plan is free forever and has no organizer subscription: the group page, recurring meetups, RSVPs with a capacity, the waitlist, calendar sync, the live feed and the embed all cost nothing, with no cap on how many events you post or how many people register. Pro, at '.plan_price($proMonthly).' a month, is for the extras some groups want: putting a price on a ticket, polls, carpool and a larger newsletter allowance. Ticket sales carry zero platform fees either way.',
            ],
            [
                'q' => 'How do I move an existing group over?',
                'a' => 'Create the group page, add your recurring meetup, and post the new link wherever your members already are. Members can follow the page, leave an email address to hear about new dates, or subscribe to the group calendar in their own calendar app. There is no way to import another platform\'s member list, and that is deliberate: everyone who joins has chosen to, so the list you build is one you can actually write to.',
            ],
            [
                'q' => 'What happens when a meetup is full?',
                'a' => 'Give the event a capacity and registration stops when the places are gone. Each date keeps its own count, so a full October night does not touch November. The RSVP waitlist is free: when someone cancels, the people waiting hear that a place has opened, so the room fills without you swapping names around by hand.',
            ],
            [
                'q' => 'Can more than one organizer run the group?',
                'a' => 'Each schedule has one team member on the free and Pro plans. Adding co-organizers with their own logins is an Enterprise feature, with two levels: an admin who runs the schedule day to day, and a viewer who can only look and scan tickets at the door. A schedule can also be handed over to someone else entirely if the group changes hands.',
            ],
            [
                'q' => 'Do online and hybrid meetups work?',
                'a' => 'Yes, on every plan. Paste the join link into the event, whether it is Zoom, Google Meet, Jitsi or a YouTube stream. People who register get the whole link on their ticket, and the public page shows only the domain, so the link is not sitting on the open web for anyone to drop into. A hybrid night is one event with both a venue and a link.',
            ],
            [
                'q' => 'What if the platform we use now shuts down or changes its prices?',
                'a' => 'Event Schedule is open source. You can export a backup of the group\'s schedule at any time and restore it, and if you would rather not depend on anyone, you can selfhost the whole thing on your own server. The calendar feed and the page link belong to the group, not to a paid account that lapses.',
            ],
            [
                'q' => 'How do I set up a meetup that is not simply weekly?',
                'a' => 'Recurring events cover the patterns groups actually use: weekly on chosen days, every two or three weeks, monthly by date, or monthly by weekday such as the first Tuesday or the last Saturday. Date exceptions take out the week the venue is closed and add a one-off date, like a summer social, without breaking the pattern.',
            ],
            [
                'q' => 'Can we charge for the occasional workshop or dinner?',
                'a' => 'Yes, with Pro. Putting a price on a ticket needs the Pro plan, and the money goes to your own Stripe or PayPal account, or through Invoice Ninja, a payment link or cash, with zero platform fees on top. The regular free nights stay free registration, so you only need Pro if the group starts charging.',
            ],
        ];

        $dotSections = [
            ['top', 'The headcount'],
            ['rhythm', 'The rhythm'],
            ['door', 'The door'],
            ['page', 'Your page'],
            ['people', 'The people up front'],
            ['extras', 'Beyond the RSVP'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-meet-page" class="es-meet-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the RSVP card                                       -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 24% 32%, rgba(163, 54, 31, 0.16), rgba(163, 54, 31, 0) 62%); opacity: 0.6;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 62%, rgba(255, 159, 133, 0.16), rgba(255, 159, 133, 0) 62%); opacity: 0.5;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-meet-tag es-fade-up es-d-1 mb-5">Meetup group events, on a page the group owns</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">Count the chairs,</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">not the <span class="es-meet-grad">fees</span>.</span></span>
                    </h1>

                    <p class="es-meet-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Set the rhythm once, put a cap on the room, and see who is coming to every
                        date. The page belongs to the group, the RSVPs are free, and there is no
                        organizer subscription to keep paying just so the group stays online.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-meet-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Start the group page
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#door" class="es-meet-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how RSVPs work
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The RSVP card. The meter is the argument. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-meet-panel p-6 shadow-xl sm:p-8">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="es-meet-tag">Every other Thursday</p>
                                <p class="es-meet-ink mt-2 text-xl font-bold">Riverside JS Night</p>
                                <p class="es-meet-muted mt-1 text-sm">7pm &middot; The Back Room, Mill Street</p>
                            </div>
                            <span class="es-meet-going">Free RSVP</span>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-baseline justify-between gap-3">
                                <p class="es-meet-ink text-sm font-semibold">{{ $meets[0]['date']->format('D j M') }}</p>
                                <p class="es-meet-num es-meet-accent text-sm font-bold">{{ $meets[0]['going'] }} of {{ $capacity }} going</p>
                            </div>
                            <div class="es-meet-meter mt-2" role="img" aria-label="{{ $meets[0]['going'] }} of {{ $capacity }} places taken">
                                <div class="es-meet-fill" style="width: {{ round($meets[0]['going'] / $capacity * 100) }}%;"></div>
                            </div>
                            <p class="es-meet-muted mt-2 text-xs">{{ $capacity - $meets[0]['going'] }} places left, then the waitlist opens.</p>
                        </div>

                        <div class="mt-6 space-y-2.5 border-t border-[rgba(27,23,21,0.1)] pt-5 dark:border-[rgba(241,236,234,0.12)]">
                            <p class="es-meet-tag mb-1">Next dates</p>
                            @foreach (array_slice($meets, 1) as $meet)
                                <div class="es-meet-sub flex items-center gap-3 p-3">
                                    <span class="es-meet-ink es-meet-num w-20 shrink-0 text-xs font-semibold">{{ $meet['date']->format('D j M') }}</span>
                                    <div class="es-meet-meter min-w-0 flex-1" role="img" aria-label="{{ $meet['going'] }} of {{ $capacity }} places taken">
                                        <div class="es-meet-fill" style="width: {{ round($meet['going'] / $capacity * 100) }}%;"></div>
                                    </div>
                                    <span class="es-meet-muted es-meet-num w-12 shrink-0 text-right text-xs">{{ $meet['going'] }}/{{ $capacity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <p class="es-meet-muted mt-5 text-sm">
                        One recurring event. {{ count($meets) }} dates, {{ count($meets) }} separate headcounts.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The rhythm (01)                                           -->
    <!-- ============================================================ -->
    <section id="rhythm" class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">01</div>
                    <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rhythm</p>
                    <h2 class="es-balance es-meet-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A group is a <span class="es-meet-grad">habit</span>. Set it once.
                    </h2>
                    <p class="es-meet-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Most groups meet on a pattern people can remember without looking it up:
                        every other Thursday, the first Tuesday, Saturday mornings in summer. Enter
                        that pattern as one recurring event and every date appears on the page,
                        in the feed and in members' calendars, without you posting each one.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Every other week, or every third', 'Pick the day and how many weeks apart. The fortnightly night never drifts onto the wrong week.'],
                            ['Monthly by weekday', 'The first Tuesday or the last Saturday of the month, which is how most monthly groups actually say it.'],
                            ['Exceptions without breaking it', 'Skip the week the bar is shut for a private party, or add a one-off summer social, and the pattern carries on around it.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-meet-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-meet-ink font-semibold">{{ $t }}</span> <span class="es-meet-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-meet-plan es-meet-plan-free">Free plan</span>
                        <span class="es-meet-muted ml-2 text-sm">Recurring events and date exceptions are on the free plan, with no cap on how many you post.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-meet-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-meet-ink text-lg font-bold">What you fill in, once</h3>
                            <span class="es-meet-muted es-meet-num text-xs">1 event</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Meetup', 'Riverside JS Night'],
                                ['Repeats', 'Every 2 weeks, Thursday'],
                                ['Starts', $firstMeet->format('D j M Y').', 7pm'],
                                ['Registration', 'Free, 40 places a date'],
                                ['Exceptions', 'Skip 24 Dec'],
                            ] as [$fLabel, $fValue])
                                <div class="es-meet-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-meet-muted w-24 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                    <span class="es-meet-ink es-meet-num min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-meet-muted mt-5 border-t border-[rgba(27,23,21,0.1)] pt-4 text-xs dark:border-[rgba(241,236,234,0.12)]">
                            That is the whole year of meetups. Change the time once and every future date follows.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The door (02)                                             -->
    <!-- ============================================================ -->
    <section id="door" class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">02</div>
                <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The door</p>
                <h2 class="es-balance es-meet-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Forty chairs means <span class="es-meet-grad">forty RSVPs</span>.
                </h2>
                <p class="es-meet-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The back room holds what it holds. A capacity on the event is how you stop
                    promising a seat to the forty-first person, and it costs nothing, because
                    nobody should have to pay to say they are coming to a free night.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['A cap per date', 'Registration closes itself when the places are gone. Every date in the series has its own count, so the busy night and the quiet one never borrow from each other.'],
                    ['A waitlist that fills the gap', 'When a full night gets a cancellation, the people waiting are told a place has opened, so it does not sit empty because nobody refreshed the page.'],
                    ['Each guest by name', 'Someone bringing two friends can register all three, and each gets a confirmation and a QR code of their own, so the headcount is people, not bookings.'],
                ] as [$t, $d])
                    <div class="es-meet-card es-meet-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-meet-ink text-lg font-bold">{{ $t }}</h3>
                        <p class="es-meet-muted mt-2 text-sm">{{ $d }}</p>
                        <p class="mt-auto pt-5"><span class="es-meet-plan es-meet-plan-free">Free plan</span></p>
                    </div>
                @endforeach
            </div>

            <div class="es-meet-card mt-4 grid gap-6 p-6 sm:p-7 md:grid-cols-2" data-reveal="panel">
                <div>
                    <h3 class="es-meet-ink text-lg font-bold">At the door, if you want it</h3>
                    <p class="es-meet-muted mt-2 text-sm">
                        Most groups never scan anything. The ones that meet in an office building
                        with a sign-in desk, or run a conference-sized annual night, can scan each
                        RSVP's QR code from a phone. Scanning is free; the live check-in dashboard
                        with a running count is part of Pro.
                    </p>
                </div>
                <div>
                    <h3 class="es-meet-ink text-lg font-bold">No limit you will hit</h3>
                    <p class="es-meet-muted mt-2 text-sm">
                        Free registration is unlimited, on every plan, and it never counts against
                        anything. A group of fifteen and a group of fifteen hundred use exactly the
                        same features for their regular nights.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The page is yours (03)                                    -->
    <!-- ============================================================ -->
    <section id="page" class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">03</div>
                    <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Your page</p>
                    <h2 class="es-balance es-meet-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The members are <span class="es-meet-grad">the group's</span>.
                    </h2>
                    <p class="es-meet-muted mb-6 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        On some platforms, a group exists only while an organizer keeps paying a
                        subscription, and the members come with the account rather than with the
                        group. Here the group page has its own link, the free plan is free forever,
                        and the people who sign up are a list you can write to.
                    </p>
                    <p class="es-meet-muted mb-8 max-w-xl text-base leading-relaxed" data-reveal>
                        If you are weighing a move, the side-by-side is on the
                        <x-link href="{{ marketing_url('/meetup-alternative') }}">Meetup alternative</x-link>
                        page, and nothing about your nights has to change on the way over.
                    </p>

                    <div class="es-meet-card p-5" data-reveal>
                        <p class="es-meet-ink text-sm font-bold">And if you ever want to leave</p>
                        <p class="es-meet-muted mt-1 text-sm">
                            Event Schedule is open source. Export a backup of the schedule whenever
                            you like, restore it, or selfhost the whole thing on a server the group
                            controls. No group is stuck on a platform because of where its dates live.
                        </p>
                    </div>
                </div>

                <div class="space-y-3" data-reveal-group="90">
                    @foreach ([
                        ['Follow, or leave an email', 'Visitors follow the group page, or give an email address from the sign-up panel without making an account. Both lists show on the followers tab.', 'Free plan', 'free'],
                        ['A digest of new dates', 'Confirmed email subscribers get a short digest when you publish new meetups, at most one every 72 hours, and it does not come out of your newsletter allowance.', 'Free plan', 'free'],
                        ['Newsletters you write', 'For the announcement with more in it than dates. The allowance counts recipients: 10 a month on the free plan, 100 on Pro and 1,000 on Enterprise.', 'Allowance by plan', 'free'],
                        ['A feed for their own calendar', 'Members who would rather not give an address can subscribe to the group calendar, and every new date lands in the calendar app they already check.', 'Free plan', 'free'],
                        ['On the group website too', 'Paste one embed into the site the group already runs and the calendar there is never out of date again.', 'Free plan', 'free'],
                    ] as [$t, $d, $plan, $kind])
                        <div class="es-meet-card es-meet-hover p-4" data-reveal>
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <p class="es-meet-ink text-sm font-bold">{{ $t }}</p>
                                <span class="es-meet-plan es-meet-plan-{{ $kind }}">{{ $plan }}</span>
                            </div>
                            <p class="es-meet-muted mt-1 text-sm">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The people up front (04)                                  -->
    <!-- ============================================================ -->
    <section id="people" class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">04</div>
                <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The people up front</p>
                <h2 class="es-balance es-meet-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Speakers, hosts and <span class="es-meet-grad">the rest of the crew</span>.
                </h2>
                <p class="es-meet-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A meetup is more than its organizer. The person giving the lightning talk, the
                    one leading the walk and the bar that gives you the room all deserve to be on
                    the page, and to get something out of being there.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="es-meet-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-meet-ink text-lg font-bold">Speakers by name</h3>
                        <span class="es-meet-plan es-meet-plan-free">Free plan</span>
                    </div>
                    <p class="es-meet-muted mt-2 text-sm">
                        Add this month's speaker to the event. If they already have a schedule, the
                        date is offered to them to accept, and it shows on their own page too. If
                        not, they get a page crediting your group, kept out of search engines until
                        they claim it by signing in with the email address you entered.
                    </p>
                </div>

                <div class="es-meet-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-meet-ink text-lg font-bold">The scene, pulled in</h3>
                        <span class="es-meet-plan es-meet-plan-free">Free plan</span>
                    </div>
                    <p class="es-meet-muted mt-2 text-sm">
                        A group that is really a guide to a local scene can list the venues and
                        performers it follows as sources. Every event they publish is linked onto
                        the group page automatically, beside your own meetups, so one link covers
                        everything worth going to.
                    </p>
                </div>

                <div class="es-meet-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-meet-ink text-lg font-bold">Co-organizers</h3>
                        <span class="es-meet-plan es-meet-plan-ent">Enterprise</span>
                    </div>
                    <p class="es-meet-muted mt-2 text-sm">
                        Plainly: a schedule has one team member on the free and Pro plans. Extra
                        logins for co-organizers, as admins who run things or viewers who only scan
                        at the door, are part of Enterprise. If the group changes hands, the whole
                        schedule can be transferred to the new organizer on any plan.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Beyond the RSVP (05)                                      -->
    <!-- ============================================================ -->
    <section id="extras" class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">05</div>
                <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Beyond the RSVP</p>
                <h2 class="es-balance es-meet-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    What different groups <span class="es-meet-grad">reach for next</span>.
                </h2>
                <p class="es-meet-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every group needs the rhythm and the headcount. After that it depends on what
                    you do when you meet. Each one is marked with the plan it needs.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ([
                    ['Online and hybrid nights', 'Paste the Zoom, Meet or stream link into the event. Registrants get the full link on their ticket while the public page shows only the domain.', 'Free plan', 'free'],
                    ['A page in two languages', 'Choose one other language and event names and descriptions are translated into it, with a switch on the page. Made for language exchanges.', 'Free plan', 'free'],
                    ['Photos from the night', 'Members add photos and comments with just a name and an email, held in a queue until you approve them. 25 photos on the free plan, no cap on Pro.', 'Free plan', 'free'],
                    ['Let members pick the topic', 'Put a poll on the next event to choose the talk, the trail or the game. Voting needs a signed-in account, so one person gets one vote.', 'Pro plan', 'pro'],
                    ['Rides to the trailhead', 'Carpool lets members offer and ask for seats to an event, with the driver approving each rider and contact details shared only once they do.', 'Pro plan', 'pro'],
                    ['The occasional paid night', 'A workshop with materials or the annual dinner: put a price on the ticket and the money lands in your own Stripe or PayPal account, with zero platform fees.', 'Pro plan', 'pro'],
                ] as [$t, $d, $plan, $kind])
                    <div class="es-meet-card es-meet-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-meet-ink text-lg font-bold">{{ $t }}</h3>
                        <p class="es-meet-muted mt-2 text-sm">{{ $d }}</p>
                        <p class="mt-auto pt-5"><span class="es-meet-plan es-meet-plan-{{ $kind }}">{{ $plan }}</span></p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Who it is for (06)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">06</div>
                <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-meet-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Any group that <span class="es-meet-grad">keeps showing up</span>.
                </h2>
                <p class="es-meet-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Rides between hikers are carpool, which is on the Pro plan. Everything else
                    below runs on the free plan.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                <x-sub-audience-card
                    name="Tech Meetups"
                    description="Monthly talks with a capped RSVP, speakers who get a page of their own and a join link for the people watching remotely."
                    icon-color="sky"
                    blog-slug="for-tech-meetups"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Hiking & Outdoor Groups"
                    description="A new trailhead every week with a group size limit, and rides between members for the ones that are hard to reach."
                    icon-color="emerald"
                    blog-slug="for-hiking-outdoor-groups"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 20l6-11 4 7 3-4 5 8H3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Language Exchanges"
                    description="The same bar every other Thursday, set once, with the event page translated for members who read the other language."
                    icon-color="amber"
                    blog-slug="for-language-exchanges"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Board Game Groups"
                    description="Weekly game nights, a monthly tournament with a table limit and a digest to confirmed subscribers when new nights go up."
                    icon-color="rose"
                    blog-slug="for-board-game-groups"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1zm3.5 4.5h.01m7 0h.01M12 12h.01m-3.5 3.5h.01m7 0h.01" />
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
        <div class="es-meet-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">07</div>
                    <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Ten minutes tonight, <span class="es-meet-grad">then just show up</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Make the group page', 'Name the group and pick its link. That link is what goes in the chat, on the flyer and in the bio from now on.'],
                        ['02', 'Set the rhythm', 'One recurring event for the regular night, with the venue, the time and any weeks you are skipping.'],
                        ['03', 'Open the RSVPs', 'Free registration with a cap per date. Members follow, subscribe by email or add the feed, and new dates reach them from there.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-meet-lit es-meet-num mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-meet-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Every other Thursday or the first Tuesday, set once with exceptions" :url="marketing_url('/features/recurring-events')" icon-color="orange">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to members within an allowance counted per recipient" :url="marketing_url('/features/newsletters')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Online Events" description="A join link on every ticket, for remote and hybrid nights" :url="marketing_url('/features/online-events')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Polls" description="Let members vote on the next topic or trail, on the Pro plan" :url="marketing_url('/features/polls')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Carpool" description="Members share seats to hard-to-reach meet points, on the Pro plan" :url="marketing_url('/features/carpool')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 17h8M5 17H4a1 1 0 01-1-1v-4l2-5h14l2 5v4a1 1 0 01-1 1h-1M7 17a2 2 0 104 0m2 0a2 2 0 104 0" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-meet-accent inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-[rgba(27,23,21,0.1)] py-16 dark:border-[rgba(241,236,234,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-meet-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-curators', 'Curators'],
                    ['/for-workshop-instructors', 'Workshop Instructors'],
                    ['/for-nonprofits', 'Nonprofits'],
                    ['/for-sports-leagues', 'Sports Leagues'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-meet-card es-meet-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-meet-muted text-sm">Event Schedule for</div>
                            <div class="es-meet-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-meet-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-meet-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(27,23,21,0.1)] py-20 dark:border-[rgba(241,236,234,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-meet-chip mb-6" data-reveal aria-hidden="true">08</div>
                <p class="es-meet-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-meet-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at <span class="es-meet-grad">the organizers' table</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-meet-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-meet-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-meet-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-meet-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-meet-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-meet-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        The next meetup, <span class="es-meet-grad">on a page you keep</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The group page, the recurring nights, capped RSVPs and the waitlist cost
                        nothing, with no organizer subscription. Paid tickets, polls and carpool
                        are the parts that need Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-group" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-meet-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Start the group page
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1c1819] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
