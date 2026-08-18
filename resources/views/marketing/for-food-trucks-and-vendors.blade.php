<x-marketing-layout>
    <x-slot name="title">Food Truck Schedules | One Link That Always Has Today's Stop</x-slot>
    <x-slot name="description">Your address changes every week. Put the whole route on one link that never goes stale, set the regular pitches up once, and turn a customer at the window into someone you can email. Free forever.</x-slot>
    <x-slot name="breadcrumbTitle">For Food Trucks and Vendors</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Food Trucks & Vendors",
        "description": "A public schedule that always carries today's stop, with the regular pitches set up once as recurring events and a QR code for the serving window.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Food Trucks, Vendors & Mobile Kitchens"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Food Trucks & Vendors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Food Truck Location and Schedule Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "One public link carrying every stop, opening on the next one",
            "A list layout that reads as a route rather than a month grid",
            "Regular pitches set up once as recurring events with their own hours",
            "Date exceptions for the weeks you lose a pitch",
            "A street address and map on every stop",
            "A downloadable QR code for the serving window that takes people to your schedule",
            "Followers you can email directly, with newsletters on the free plan",
            "Booking requests for catering and private hire, each waiting for your approval",
            "An email to you when a new booking request lands",
            "Sub-schedules that keep markets, festivals and private hire apart",
            "Auto-generated share graphics for any stop",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for your own website"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "food truck schedule, food truck locations, mobile vendor calendar, where is the food truck, catering booking requests, street food route",
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
           For-food-trucks-and-vendors "Today's Stop" styles.

           CONCEPT: a truck is not a restaurant - its address is the news,
           and it changes. So the page inverts the usual hierarchy: TODAY
           is the largest thing on it and the rest of the week sits beneath
           at small scale, because that is the only question a hungry
           person is actually asking. A "where to find us" post expires
           every week; a link does not.

           TWO DEVICES DROPPED BECAUSE THEY WOULD DEPICT FEATURES THAT DO
           NOT EXIST - the same error class as an invented feature:
             - AN "OPEN NOW" STATUS LIGHT. There is no live open/closed
               state in the product. What is real is the event's own start
               time and duration, and that the calendar marks today. The
               board shows hours; it does not claim a live indicator.
             - A STRUCK-THROUGH "no pitch" ROW. Cancelling HIDES an event
               (Event.php: "'cancel' -> hides the event via is_cancelled")
               and a date exception removes the date outright, so a guest
               sees the day simply absent. The week therefore has a GAP on
               Thursday and the copy carries the meaning.

           TREATMENT: die-cut vinyl. A truck is covered in decals, so the
           page is too - a light keyline around each panel, a soft drop
           shadow, and a slight rotation on the loose ones. Unclaimed by
           any page, and it pays off in the QR sticker section.

           COLOUR: deep forest green, and the trade-off is named rather
           than hidden. Blue 220-259 is the only other free band but its
           best candidate sits 2deg from brand blue, which appears on this
           very page in the nav and every "Read more". Green's problem is
           that /for-farmers-markets - the NEAREST audience, which this
           page links to - is already the green food page (#a3e635,
           #22c55e, #4ade80 at 141-142deg, #4d7c0f at 85deg). Mine is a
           deep forest at 28% lightness against their bright grass at
           45-65%, with 28deg clearance either side, and the vinyl
           treatment means the two pages share nothing but a family.
           LESSON: include UNREBUILT neighbours in a palette audit.

           Measured: #2d6b26 5.74 on ground / 6.22 on card, #9ada78 11.37
           on dark. NEVER text-gray-500 - use .es-stop-muted (6.87 / 7.31).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-stop-page { background-color: #eff2ee; color: #14181d; }
        .dark .es-stop-page { background-color: #0f1210; color: #e8ecf1; }
        .es-stop-ink { color: #14181d; }
        .dark .es-stop-ink { color: #e8ecf1; }
        .es-stop-muted { color: #4b535d; }
        .dark .es-stop-muted { color: #98a2ae; }
        .es-stop-accent { color: #2d6b26; }
        .dark .es-stop-accent { color: #9ada78; }
        /* Always-lit accent for the band, in both colour modes. */
        .es-stop-lit { color: #9ada78; }

        .es-stop-grad {
            background-image: linear-gradient(100deg, #2d6b26, #3f8433);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-stop-grad,
        .es-stop-band .es-stop-grad {
            background-image: linear-gradient(100deg, #b7e6a0, #9ada78);
        }

        /* --- Surfaces --- */
        .es-stop-card {
            background-color: #f9fbf8;
            border: 1px solid rgba(20, 24, 29, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-stop-card {
            background-color: #181c19;
            border-color: rgba(232, 236, 241, 0.13);
        }
        .es-stop-sub {
            background-color: rgba(20, 24, 29, 0.045);
            border-radius: 0.5rem;
        }
        .dark .es-stop-sub { background-color: rgba(232, 236, 241, 0.05); }
        .es-stop-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-stop-hover:hover { border-color: rgba(45, 107, 38, 0.45); box-shadow: 0 10px 28px -18px rgba(20, 24, 29, 0.5); }
        .dark .es-stop-hover:hover { border-color: rgba(154, 218, 120, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- Die-cut vinyl -------------------------------------------
           The keyline is the cut edge of the decal. It inverts with the
           page, because a decal is not a fixed object - only the bands are
           mode-stable. */
        .es-stop-decal {
            border: 3px solid #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 8px 22px -10px rgba(20, 24, 29, 0.45);
        }
        .dark .es-stop-decal {
            border-color: #2b322c;
            box-shadow: 0 8px 22px -10px rgba(0, 0, 0, 0.85);
        }
        .es-stop-tilt-l { transform: rotate(-1.1deg); }
        .es-stop-tilt-r { transform: rotate(0.9deg); }

        /* --- The today board -----------------------------------------
           Deliberately outsized: the hierarchy IS the concept. */
        .es-stop-today {
            background-color: #2d6b26;
            color: #ffffff;
            border-radius: 0.75rem;
        }
        .dark .es-stop-today { background-color: #24551e; }
        .es-stop-today-label {
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.34em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.78);
        }
        .es-stop-mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Eyebrow, numerals, plan tags --- */
        .es-stop-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #2d6b26;
        }
        .dark .es-stop-tag { color: #9ada78; }
        .es-stop-band .es-stop-tag { color: #9ada78; }

        .es-stop-corner {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 1.9rem;
            border: 1px solid rgba(20, 24, 29, 0.22);
            border-radius: 0.3rem;
            background: rgba(20, 24, 29, 0.035);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #14181d;
        }
        .dark .es-stop-corner { border-color: rgba(232, 236, 241, 0.22); background: rgba(232, 236, 241, 0.05); color: #e8ecf1; }
        .es-stop-band .es-stop-corner { border-color: rgba(232, 236, 241, 0.22); background: rgba(232, 236, 241, 0.05); color: #e8ecf1; }
        .es-stop-corner::before {
            content: "";
            position: absolute;
            left: 0.4rem;
            top: 0.4rem;
            bottom: 0.4rem;
            width: 2px;
            background: #2d6b26;
        }
        .dark .es-stop-corner::before { background: #9ada78; }
        .es-stop-band .es-stop-corner::before { background: #9ada78; }

        /* Plan tiers ONLY - never reuse these for a state badge. */
        .es-stop-plan {
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
        /* Only the Free modifier exists, because nothing this audience needs
           is gated: every section on this page is on the free plan. A Pro
           variant would be a dead class. */
        .es-stop-plan-free { border-color: rgba(20, 24, 29, 0.22); color: #4b535d; }
        .dark .es-stop-plan-free { border-color: rgba(232, 236, 241, 0.26); color: #98a2ae; }

        /* --- Buttons --- */
        .es-stop-btn {
            background-color: #2d6b26;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-stop-btn:hover { background-color: #24551e; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(45, 107, 38, 0.9); }
        .es-stop-ghost {
            border: 1px solid rgba(20, 24, 29, 0.22);
            color: #14181d;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-stop-ghost:hover { border-color: rgba(45, 107, 38, 0.5); background-color: rgba(45, 107, 38, 0.06); }
        .dark .es-stop-ghost { border-color: rgba(232, 236, 241, 0.24); color: #e8ecf1; }
        .dark .es-stop-ghost:hover { border-color: rgba(154, 218, 120, 0.45); background-color: rgba(154, 218, 120, 0.08); }

        /* --- The dark band ------------------------------------------
           A resolvable background-color under the gradients: it is what
           paints if they fail and what a contrast audit can read. */
        .es-stop-band {
            background-color: #121613;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(45, 107, 38, 0.3), rgba(45, 107, 38, 0) 70%),
                linear-gradient(180deg, #1a201b, #121613);
        }

        /* --- Nothing inside the band may change between colour modes --
           The band has no .dark variant, so any descendant that HAS one
           would render differently on an identical ground. Two shared
           classes carry their own .dark rules in marketing.css and are
           invisible to a grep of this file. */
        .es-stop-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 236, 241, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 236, 241, 0.05) 1px, transparent 1px);
        }
        .es-stop-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-stop-band .es-claim:focus-within {
            border-color: rgba(154, 218, 120, 0.75);
            box-shadow: 0 0 0 4px rgba(154, 218, 120, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(45, 107, 38, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(154, 218, 120, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #2d6b26; }
        .dark .es-dot.is-active .es-dot-pip { background: #9ada78; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-stop-page a:focus-visible,
        #es-stop-page summary:focus-visible,
        #es-stop-page button:focus-visible,
        #es-stop-page input:focus-visible {
            outline: 2px solid #2d6b26;
            outline-offset: 2px;
        }
        .dark #es-stop-page a:focus-visible,
        .dark #es-stop-page summary:focus-visible,
        .dark #es-stop-page button:focus-visible,
        .dark #es-stop-page input:focus-visible {
            outline-color: #9ada78;
        }
        .es-stop-band a:focus-visible,
        .es-stop-band summary:focus-visible,
        .es-stop-band button:focus-visible,
        .es-stop-band input:focus-visible {
            outline-color: #9ada78 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-stop-btn:hover { transform: none; }
        }
    </style>

    @php
        // The week is the single source of truth: the today board reads
        // from the SAME array the week list renders, so the two cannot
        // drift apart. Thursday is deliberately absent rather than struck
        // through - a date exception removes the date, and a cancelled
        // event is hidden, so a customer sees the day simply not there.
        $week = [
            ['Mon', 'Mill Lane Office Park', '11:30am', '2:00pm', true],
            ['Tue', 'Mill Lane Office Park', '11:30am', '2:00pm', false],
            ['Wed', 'Northgate Brewery',     '5:00pm',  '9:00pm', false],
            ['Fri', 'Riverside Market',      '12:00pm', '8:00pm', false],
            ['Sat', 'Riverside Market',      '10:00am', '8:00pm', false],
        ];

        $todayStop = null;
        foreach ($week as $row) {
            if ($row[4]) {
                $todayStop = $row;
                break;
            }
        }

        $faqs = [
            [
                'q' => 'Is Event Schedule free for food trucks?',
                'a' => 'The parts you use every week are free forever: your public schedule and its list layout, the regular pitches as recurring events, date exceptions for the weeks you lose a spot, an address and map on every stop, a QR code for the serving window, booking requests for catering, sub-schedules, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Selling tickets to a ticketed event is on the Pro plan at '.plan_price($proMonthly).' a month, with zero platform fees on sales.',
            ],
            [
                'q' => 'How do customers know where I am today?',
                'a' => 'They open the one link you have been sharing all along. Set the layout to List and it reads as a route, opening on the next stop with the ones you have already done below a divider. Nothing has to be reposted, so the link is right on a Tuesday in February without you touching it.',
            ],
            [
                'q' => 'Do my followers get an alert when I add a stop?',
                'a' => 'No, and it is worth being straight about that. Following does not fire off an automatic message. What it does is give you their email address with their consent, so you can send the week\'s route yourself as a newsletter - ten emails a month on the free plan and a hundred on Pro, counted per recipient rather than per send. That is the difference between a list you own and a feed that decides who sees you.',
            ],
            [
                'q' => 'What happens the week I lose a pitch?',
                'a' => 'Add a date exception for that date and the stop is simply not on the schedule that week. The rest of the recurring pattern carries on untouched, so you are not rebuilding the week around one cancellation.',
            ],
            [
                'q' => 'Can people book me for catering and private events?',
                'a' => 'Yes. Turn on booking requests and people can ask to book you, with their own date and details attached. Every request waits for you to accept it, and you get an email when a new one lands, so an enquiry does not sit unread in a comment thread.',
            ],
        ];

        $dotSections = [
            ['top', "Today's stop"],
            ['today', 'The link'],
            ['week', 'The week'],
            ['sticker', 'The window'],
            ['catering', 'Catering'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-stop-page" class="es-stop-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: today's stop                                        -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the board sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 26% 30%, rgba(45, 107, 38, 0.2), rgba(45, 107, 38, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 62%, rgba(154, 218, 120, 0.16), rgba(154, 218, 120, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-[1fr_1.05fr] lg:gap-16">
                <div>
                    <p class="es-stop-tag es-fade-up es-d-1 mb-5">For food trucks, carts and mobile kitchens</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Your address is</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-stop-grad">the news</span>.</span></span>
                    </h1>

                    <p class="es-stop-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        A restaurant has one for good. You have a new one every week, and you are
                        retyping it into a post that expires by Friday. Put the whole route on one
                        link instead.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-stop-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Put your route online
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#today" class="es-stop-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how the link works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The board. Today is outsized; the week sits beneath it. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-stop-decal es-stop-tilt-r es-stop-today p-6 sm:p-8">
                        <p class="es-stop-today-label mb-4">Today</p>
                        <p class="text-2xl font-black leading-tight tracking-tight text-white sm:text-3xl">{{ $todayStop[1] }}</p>
                        <p class="es-stop-mono mt-2 text-base text-white/85">{{ $todayStop[2] }} &ndash; {{ $todayStop[3] }}</p>
                    </div>

                    <div class="es-stop-decal es-stop-tilt-l es-stop-card mt-5 p-5 sm:p-6">
                        <p class="es-stop-tag mb-3">The rest of the week</p>
                        <div class="space-y-1">
                            @foreach ($week as [$wDay, $wPlace, $wFrom, $wTo, $wIsToday])
                                @continue($wIsToday)
                                <div class="es-stop-sub flex items-baseline gap-3 px-3 py-2">
                                    <span class="es-stop-muted es-stop-mono w-9 shrink-0 text-xs font-bold uppercase">{{ $wDay }}</span>
                                    <span class="es-stop-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $wPlace }}</span>
                                    <span class="es-stop-muted es-stop-mono shrink-0 text-xs">{{ $wFrom }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="es-stop-muted mt-3 text-xs">
                            No Thursday this week. It is not crossed out, it is just not there.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The link (01)                                             -->
    <!-- ============================================================ -->
    <section id="today" class="scroll-mt-24 border-t border-[rgba(20,24,29,0.1)] py-20 dark:border-[rgba(232,236,241,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-stop-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-stop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The link</p>
                <h2 class="es-balance es-stop-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    A post expires. <span class="es-stop-grad">A link does not</span>.
                </h2>
                <p class="es-stop-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    You already write the week out every Sunday. Write it once somewhere that keeps
                    it, and put that address on the truck, the socials and the receipt.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['One address, all year', 'The same link in every bio and on every flyer. It is never last week\'s post, because there is no post to go stale.'],
                    ['Reads as a route', 'Set the layout to List and the schedule reads as a run of stops with the next one at the top, rather than a month grid to decode.'],
                    ['The next stop is first', 'The list opens on what is coming, with everything you have already done tucked below a divider. Nobody has to work out which line is current.'],
                ] as [$t, $d])
                    <div class="es-stop-card es-stop-hover p-6" data-reveal>
                        <h3 class="es-stop-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-stop-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-stop-plan es-stop-plan-free">Free</span>
                <span class="es-stop-muted ml-2 text-sm">The schedule, the list layout and the address are all on the free plan.</span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The week that repeats (02)                                -->
    <!-- ============================================================ -->
    <section id="week" class="scroll-mt-24 border-t border-[rgba(20,24,29,0.1)] py-20 dark:border-[rgba(232,236,241,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-stop-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-stop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The week</p>
                    <h2 class="es-balance es-stop-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Most of your week <span class="es-stop-grad">already repeats</span>.
                    </h2>
                    <p class="es-stop-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        The office park every Monday and Tuesday, the brewery on Wednesday, the
                        market at the weekend. Each regular pitch is one recurring event with its
                        own hours, not fifty entries you retype.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['One pitch, one event', 'Pick the days it runs and the hours you serve. Change the hours once and every future date follows.'],
                            ['The week you lose it', 'Take that single date out with an exception. The stop is not on the schedule that week and the pattern carries on.'],
                            ['One-offs sit alongside', 'A festival or a private booking is just another event on the same link, so the route stays in one place.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-stop-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-stop-ink font-semibold">{{ $t }}</span> <span class="es-stop-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-stop-plan es-stop-plan-free">Free</span>
                        <span class="es-stop-muted ml-2 text-sm">Recurring stops and date exceptions are on the free plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-stop-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-stop-ink text-lg font-bold">Your pitches</h3>
                            <span class="es-stop-muted es-stop-mono text-xs">3 recurring events</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Mill Lane Office Park', 'Mon &amp; Tue', '11:30am &ndash; 2:00pm'],
                                ['Northgate Brewery', 'Wed', '5:00pm &ndash; 9:00pm'],
                                ['Riverside Market', 'Fri &amp; Sat', 'from 10:00am'],
                            ] as [$pName, $pDays, $pHours])
                                <div class="es-stop-sub p-3.5">
                                    <p class="es-stop-ink text-sm font-semibold">{{ $pName }}</p>
                                    <p class="es-stop-muted es-stop-mono text-xs">{!! $pDays !!} &middot; {!! $pHours !!}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 border-t border-[rgba(20,24,29,0.1)] pt-4 dark:border-[rgba(232,236,241,0.12)]">
                            <p class="es-stop-tag mb-2">Date exceptions</p>
                            <p class="es-stop-muted text-xs">
                                Thu 14 Aug taken out at Northgate. Every other Wednesday is unaffected.
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
    <!-- 4. The sticker in the window (03)                            -->
    <!-- ============================================================ -->
    <section id="sticker" class="scroll-mt-24 border-t border-[rgba(20,24,29,0.1)] py-20 dark:border-[rgba(232,236,241,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-stop-decal es-stop-tilt-l es-stop-card p-6 sm:p-8" data-reveal="panel">
                        <p class="es-stop-tag mb-4">In the window</p>
                        <div class="flex items-center gap-5">
                            <div class="es-stop-sub grid h-24 w-24 shrink-0 place-items-center" aria-hidden="true">
                                <svg class="es-stop-accent h-14 w-14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M3,11H5V13H3V11M11,5H13V9H11V5M9,11H13V15H11V13H9V11M15,11H17V13H19V11H21V13H19V15H21V19H19V21H17V19H13V21H11V17H15V15H17V13H15V11M19,19V15H17V19H19M15,3H21V9H15V3M17,5V7H19V5H17M3,3H9V9H3V3M5,5V7H7V5H5M3,15H9V21H3V15M5,17V19H7V17H5Z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="es-stop-ink text-base font-bold">Where are we tomorrow?</p>
                                <p class="es-stop-muted mt-1 text-sm">
                                    One scan and they have the whole route, and the option to follow.
                                </p>
                            </div>
                        </div>
                        <p class="es-stop-muted mt-5 border-t border-[rgba(20,24,29,0.1)] pt-4 text-xs dark:border-[rgba(232,236,241,0.12)]">
                            The QR downloads from your Followers page and points at your schedule.
                        </p>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-stop-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-stop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The window</p>
                    <h2 class="es-balance es-stop-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        They already <span class="es-stop-grad">came to you once</span>.
                    </h2>
                    <p class="es-stop-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Somebody standing at the window has found you at your hardest moment - when
                        they did not know where you were. A sticker beside the hatch is how that
                        becomes a second visit.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Print the QR, tape it up', 'Download it from your Followers page. It points at your schedule, so it never needs reprinting when the route changes.'],
                            ['They follow, you get the email address', 'With their consent, and they can unfollow whenever they like. It is your list, not a platform\'s.'],
                            ['You send the week out', 'Nothing goes automatically - you write the route and send it. Ten a month free and a hundred on Pro, counted per recipient rather than per send.'],
                        ] as [$t, $d])
                            <div class="es-stop-card es-stop-hover p-4" data-reveal>
                                <p class="es-stop-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-stop-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-6" data-reveal>
                        <span class="es-stop-plan es-stop-plan-free">Free</span>
                        <span class="es-stop-muted ml-2 text-sm">The QR and the followers cost nothing; the free newsletter allowance is ten emails a month, counted per recipient.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Catering (04)                                             -->
    <!-- ============================================================ -->
    <section id="catering" class="scroll-mt-24 border-t border-[rgba(20,24,29,0.1)] py-20 dark:border-[rgba(232,236,241,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-stop-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-stop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Catering</p>
                <h2 class="es-balance es-stop-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The bookings that <span class="es-stop-grad">pay for January</span>.
                </h2>
                <p class="es-stop-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A wedding or an office lunch is worth a fortnight of service, and it usually
                    arrives as a message somebody nearly missed.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['They ask through your page', 'Turn on booking requests and anyone can ask to book you, with their date and details attached, instead of a comment you scroll past.'],
                    ['You get an email', 'A new request is emailed to you, so an enquiry worth a fortnight of trading does not sit unread.'],
                    ['Nothing posts without you', 'Every request waits for you to accept it. Nothing appears on your public schedule that you did not agree to.'],
                ] as [$t, $d])
                    <div class="es-stop-card es-stop-hover p-6" data-reveal>
                        <h3 class="es-stop-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-stop-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-stop-plan es-stop-plan-free">Free</span>
                <span class="es-stop-muted ml-2 text-sm">
                    Booking requests are on the free plan. A confirmed private booking can stay a Draft if you would rather it did not show up on the public route.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Who it is for (05)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(20,24,29,0.1)] py-20 dark:border-[rgba(232,236,241,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-stop-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-stop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-stop-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Anything with <span class="es-stop-grad">wheels and a hatch</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Taco Trucks"
                    description="The same three pitches most weeks, with the late-night one that only runs in summer taken out for the winter."
                    icon-color="amber"
                    blog-slug="for-taco-trucks"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 14a9 9 0 0118 0M3 14h18M3 14l-1 3h20l-1-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Festival Vendors"
                    description="A summer of one-off weekends, each with its own dates and gates, on the same link as the regular pitches."
                    icon-color="orange"
                    blog-slug="for-festival-vendors"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 21V4m0 0l8 3-8 3m8-6l8 3-8 3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Pop-Up Restaurants"
                    description="A short residency in somebody else's room, sold by the seat, with the dates ending on their own."
                    icon-color="rose"
                    blog-slug="for-popup-kitchens"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v8a3 3 0 006 0V3M8 11v10M16 3c-1.5 2-2 4-2 6a2 2 0 004 0c0-2-.5-4-2-6zm0 8v10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Caterers"
                    description="Almost all of it is private hire, so the bookings arrive as requests and the public page stays a shop window."
                    icon-color="sky"
                    blog-slug="for-mobile-catering-businesses"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 15h16M4 15a8 8 0 0116 0M12 7V4m-9 14h18" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Coffee & Beverage Carts"
                    description="Early, short and every weekday. One recurring morning that people can put in their own calendar."
                    icon-color="teal"
                    blog-slug="for-coffee-beverage-carts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h12v6a4 4 0 01-4 4H8a4 4 0 01-4-4V8zm12 1h2a2 2 0 010 4h-2M5 21h14" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="BBQ & Smoker Trucks"
                    description="You sell out and go home. Say the hours, and let people follow so they know to come early."
                    icon-color="slate"
                    blog-slug="for-bbq-smoker-trucks"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c1.5 2.5 3 4 3 6a3 3 0 11-6 0c0-2 1.5-3.5 3-6zM6 14h12l-1.5 6h-9L6 14z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. How it works (06, dark band)                              -->
    <!-- ============================================================ -->
    <section id="how" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-stop-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-stop-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-stop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A Sunday evening, <span class="es-stop-grad">once</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Put the regular pitches in', 'One recurring event per spot, with the days and the hours you serve. The one-offs go in as you get them.'],
                        ['02', 'Share the one address', 'On the truck, in every bio, on the receipt. It is the last time you have to update where it points.'],
                        ['03', 'Tape the QR to the hatch', 'People who already found you once can follow, and you can tell them where you are next week.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-xl border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-stop-lit es-stop-mono mb-3 text-sm font-bold">{{ $n }}</p>
                            <h3 class="mb-2 text-lg font-bold text-white">{{ $t }}</h3>
                            <p class="text-sm text-gray-400">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Key features                                              -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 border-t border-[rgba(20,24,29,0.1)] py-20 dark:border-[rgba(232,236,241,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-stop-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A regular pitch set up once, with exceptions for the weeks you lose it" :url="marketing_url('/features/recurring-events')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Send the week's route to the people who follow you" :url="marketing_url('/features/newsletters')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep markets, festivals and private hire on their own strands" :url="marketing_url('/features/sub-schedules')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Drop the route into the site you already have" :url="marketing_url('/features/embed-calendar')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-stop-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 9. Keep exploring                                            -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(20,24,29,0.1)] py-16 dark:border-[rgba(232,236,241,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-stop-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-farmers-markets', 'Farmers Markets'],
                    ['/for-breweries-and-wineries', 'Breweries &amp; Wineries'],
                    ['/for-restaurants', 'Restaurants'],
                    ['/for-bars', 'Bars'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-stop-card es-stop-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-stop-muted text-sm">Event Schedule for</div>
                            <div class="es-stop-ink text-lg font-semibold">{!! $relName !!}</div>
                        </div>
                        <svg aria-hidden="true" class="es-stop-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-stop-accent inline-flex items-center font-medium hover:underline">
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
    <!-- 10. FAQ (07)                                                 -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(20,24,29,0.1)] py-20 dark:border-[rgba(232,236,241,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-stop-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-stop-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-stop-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-stop-grad">at the hatch</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-stop-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-stop-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-stop-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-stop-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$faqs" />

    <!-- ============================================================ -->
    <!-- 11. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-stop-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-stop-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Stop retyping <span class="es-stop-grad">the week</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        One address for the whole route, and a QR for the window that turns tonight's
                        queue into next week's.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-truck" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=talent') }}" class="es-stop-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Put your route online
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#181c19] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
