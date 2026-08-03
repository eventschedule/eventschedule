<x-marketing-layout>
    <x-slot name="title">Taproom Calendars | Most of What You Run Is Free</x-slot>
    <x-slot name="description">Nobody buys a ticket to trivia night. A taproom calendar exists to get people through the door, so almost all of it sits on the free plan - and ticketing is there for the tours that genuinely need it.</x-slot>
    <x-slot name="breadcrumbTitle">For Breweries and Wineries</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Breweries & Wineries",
        "description": "A taproom calendar of mostly free events - music, quizzes, visiting food trucks - with ticketing for the tours and tastings that need it.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Breweries, Wineries & Tasting Rooms"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Breweries & Wineries",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Taproom Event Scheduling Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "A weekly taproom rhythm set up once as recurring events",
            "Date exceptions for the weeks you are shut",
            "Free registration with a capacity, for tours that are limited but not paid",
            "Sub-schedules for music, tours and private hire, each with its own shareable link",
            "Booking requests from bands and food trucks that want a date",
            "Participants, so a visiting act gets the date on their own schedule",
            "Ticketed tours and tastings with QR check-in",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Followers you can email directly, within a monthly allowance counted per recipient",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for your own website",
            "Draft events that stay members-only until a release date is confirmed",
            "Online events with the link people join on"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "taproom calendar, brewery events, winery tasting schedule, brewery tour tickets, tasting room calendar, free brewery scheduling",
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
           For-breweries-and-wineries "Most Nights Are Free" styles.

           CONCEPT: a brewery's events exist to SELL BEER, NOT TICKETS.
           Nobody buys a ticket to trivia night. The taproom calendar's job
           is footfall on a Tuesday, which makes this the mirror image of
           /for-restaurants - that page is a prepaid, seat-limited count
           and is genuinely Pro; almost everything this audience needs is
           genuinely free, and the page says so.

           THE DEVICE IS THE WEEK BOARD WITH A PRICE COLUMN, where almost
           every row reads Free and one reads a price. The argument is in
           the column: one of these sells a ticket, the other four sell
           beer.

           NEWSLETTERS ARE DELIBERATELY NOT PART OF THE FREE ARGUMENT. The
           free allowance is 10 emails a month counted PER RECIPIENT, not
           per send ("a newsletter sent to 100 followers uses 100 of the
           monthly allowance"), so on Free you can reach ten people once.
           Listing it beside genuinely free capabilities would be the
           weakest claim on a page built on "stay free". It appears once,
           stated with its unit.

           COLOUR: bottle green, decided on numbers rather than taste. The
           wheel is spent; only green 120-139 and blue 240-259 were open.
           Blue was MEASURED AND REJECTED - the best candidate landed at
           237deg, six degrees from /for-comedy-clubs' #31396b with nearly
           the same saturation (34% vs 37%), and two desaturated navies
           that close on pages shipped two turns apart would read as one
           colour. Bottle green #235539 (146deg, 41% sat) wins the test
           this campaign actually uses:
             /for-food-trucks-and-vendors #2d6b26  NEIGHBOUR  33deg apart
             /for-theaters  #14532d   not a neighbour  3deg, 41% vs 61% sat
             /for-nightclubs #166534  not a neighbour  4deg, 41% vs 64% sat
             /for-dance-groups #115e59 not a neighbour 30deg apart
           The one green page in this neighbourhood is 33 degrees away, and
           the hue-adjacent pages are non-neighbours separated by
           saturation - the sepia-near-rust move. It is also materially
           honest: bottle glass.

           Measured: #235539 7.86 on ground / 8.28 on card; #9ad7b4 11.52 /
           10.41 / 11.14 dark. NEVER text-gray-500 - use .es-pour-muted
           (6.47 light / 7.37 dark).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-pour-page { background-color: #f5f4f1; color: #14170f; }
        .dark .es-pour-page { background-color: #0f110f; color: #ecefe9; }
        .es-pour-ink { color: #14170f; }
        .dark .es-pour-ink { color: #ecefe9; }
        .es-pour-muted { color: #545a4f; }
        .dark .es-pour-muted { color: #9ba498; }
        .es-pour-accent { color: #235539; }
        .dark .es-pour-accent { color: #9ad7b4; }
        /* Always-lit accent for the band, in both colour modes. */
        .es-pour-lit { color: #9ad7b4; }

        .es-pour-grad {
            background-image: linear-gradient(100deg, #235539, #2f6b49);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-pour-grad,
        .es-pour-band .es-pour-grad {
            background-image: linear-gradient(100deg, #b5e5c9, #9ad7b4);
        }

        /* --- Surfaces --- */
        .es-pour-card {
            background-color: #fbfaf8;
            border: 1px solid rgba(20, 23, 15, 0.12);
            border-radius: 0.65rem;
        }
        .dark .es-pour-card {
            background-color: #1a1c1a;
            border-color: rgba(236, 239, 233, 0.13);
        }
        .es-pour-sub {
            background-color: rgba(20, 23, 15, 0.045);
            border-radius: 0.4rem;
        }
        .dark .es-pour-sub { background-color: rgba(236, 239, 233, 0.05); }
        .es-pour-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-pour-hover:hover { border-color: rgba(35, 85, 57, 0.45); box-shadow: 0 10px 28px -18px rgba(20, 23, 15, 0.5); }
        .dark .es-pour-hover:hover { border-color: rgba(154, 215, 180, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The week board -----------------------------------------
           The price column is the argument, so it gets its own treatment:
           "Free" reads as a plain word, a price reads as a figure. */
        .es-pour-board { background-color: #235539; color: #ffffff; border-radius: 0.65rem; }
        .dark .es-pour-board { background-color: #1b4630; }
        .es-pour-row { border-top: 1px solid rgba(255, 255, 255, 0.14); }
        /* 0.70, not 0.62. Composited over the #235539 board, 0.62 paints 4.44:1 at
           10px - under AA. Translucent text has to be measured composited, never as
           if it were opaque white. 0.70 gives 5.18 light and 6.15 on the dark board. */
        .es-pour-day {
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.7);
        }
        .es-pour-free {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }
        .es-pour-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- Eyebrow, numerals, plan tags --- */
        .es-pour-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #235539;
        }
        .dark .es-pour-tag { color: #9ad7b4; }
        .es-pour-band .es-pour-tag { color: #9ad7b4; }

        .es-pour-corner {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 1.9rem;
            border: 1px solid rgba(20, 23, 15, 0.22);
            border-radius: 0.25rem;
            background: rgba(20, 23, 15, 0.035);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #14170f;
        }
        .dark .es-pour-corner { border-color: rgba(236, 239, 233, 0.22); background: rgba(236, 239, 233, 0.05); color: #ecefe9; }
        .es-pour-band .es-pour-corner { border-color: rgba(236, 239, 233, 0.22); background: rgba(236, 239, 233, 0.05); color: #ecefe9; }
        .es-pour-corner::before {
            content: "";
            position: absolute;
            left: 0.4rem;
            top: 0.4rem;
            bottom: 0.4rem;
            width: 2px;
            background: #235539;
        }
        .dark .es-pour-corner::before { background: #9ad7b4; }
        .es-pour-band .es-pour-corner::before { background: #9ad7b4; }

        /* Plan tiers ONLY - never reuse these for a state badge. */
        .es-pour-plan {
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
        .es-pour-plan-free { border-color: rgba(20, 23, 15, 0.22); color: #545a4f; }
        .dark .es-pour-plan-free { border-color: rgba(236, 239, 233, 0.26); color: #9ba498; }
        .es-pour-plan-pro { border-color: rgba(35, 85, 57, 0.5); color: #235539; background: rgba(35, 85, 57, 0.08); }
        .dark .es-pour-plan-pro { border-color: rgba(154, 215, 180, 0.42); color: #9ad7b4; background: rgba(154, 215, 180, 0.1); }

        /* --- Buttons --- */
        .es-pour-btn {
            background-color: #235539;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-pour-btn:hover { background-color: #1b4630; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(35, 85, 57, 0.9); }
        .es-pour-ghost {
            border: 1px solid rgba(20, 23, 15, 0.22);
            color: #14170f;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-pour-ghost:hover { border-color: rgba(35, 85, 57, 0.5); background-color: rgba(35, 85, 57, 0.06); }
        .dark .es-pour-ghost { border-color: rgba(236, 239, 233, 0.24); color: #ecefe9; }
        .dark .es-pour-ghost:hover { border-color: rgba(154, 215, 180, 0.45); background-color: rgba(154, 215, 180, 0.08); }

        /* --- The dark band ------------------------------------------
           A resolvable background-color under the gradients: it is what
           paints if they fail and what a contrast audit can read. */
        .es-pour-band {
            background-color: #131513;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(35, 85, 57, 0.42), rgba(35, 85, 57, 0) 70%),
                linear-gradient(180deg, #1a1c1a, #131513);
        }

        /* --- Nothing inside the band may change between colour modes --
           The band has no .dark variant, so any descendant that HAS one
           would render differently on an identical ground. Two shared
           classes carry their own .dark rules in marketing.css and are
           invisible to a grep of this file. */
        .es-pour-band .grid-overlay {
            background-image:
                linear-gradient(rgba(236, 239, 233, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(236, 239, 233, 0.05) 1px, transparent 1px);
        }
        .es-pour-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-pour-band .es-claim:focus-within {
            border-color: rgba(154, 215, 180, 0.75);
            box-shadow: 0 0 0 4px rgba(154, 215, 180, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(35, 85, 57, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(154, 215, 180, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #235539; }
        .dark .es-dot.is-active .es-dot-pip { background: #9ad7b4; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-pour-page a:focus-visible,
        #es-pour-page summary:focus-visible,
        #es-pour-page button:focus-visible,
        #es-pour-page input:focus-visible {
            outline: 2px solid #235539;
            outline-offset: 2px;
        }
        .dark #es-pour-page a:focus-visible,
        .dark #es-pour-page summary:focus-visible,
        .dark #es-pour-page button:focus-visible,
        .dark #es-pour-page input:focus-visible {
            outline-color: #9ad7b4;
        }
        .es-pour-band a:focus-visible,
        .es-pour-band summary:focus-visible,
        .es-pour-band button:focus-visible,
        .es-pour-band input:focus-visible {
            outline-color: #9ad7b4 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-pour-btn:hover { transform: none; }
        }
    </style>

    @php
        // The week board. `price` null means free; the counts in the copy are
        // derived from this array so the argument and the table cannot drift.
        $week = [
            ['Wednesday', 'Quiz night',        null, 'Runs itself, fills the room'],
            ['Thursday',  'Food truck visits', null, 'Their van, your taps'],
            ['Friday',    'Live music',        null, 'A duo in the corner'],
            ['Saturday',  'Brewery tour',      15,   'Twelve places, booked ahead'],
            ['Sunday',    'Yoga in the yard',  null, 'Ends at the bar'],
        ];
        // Spelled out so the sentence under the board reads as prose, and
        // derived from the board so the count and the table cannot drift.
        $words = [1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'four', 5 => 'five'];
        $paidCount = count(array_filter($week, fn ($r) => $r[2] !== null));
        $freeCount = count($week) - $paidCount;

        $faqs = [
            [
                'q' => 'Is Event Schedule free for a taproom?',
                'a' => 'Almost all of what a taproom runs is free forever: the weekly nights as recurring events, date exceptions for the weeks you are shut, free registration with a capacity for a tour that is limited but not paid, sub-schedules with their own shareable links, booking requests from bands and food trucks, two-way calendar sync and an embeddable calendar. Selling a ticket to a paid tour or tasting is on the Pro plan at $'.$proMonthly.' a month, with zero platform fees on sales.',
            ],
            [
                'q' => 'Most of our events are free. Is that a problem?',
                'a' => 'It is the normal case here, and nothing about it is second class. A free event still takes registrations up to a capacity, still appears on your public calendar, and still syncs to the calendars people keep on their phones. The events are how you fill the room on a Tuesday, not a revenue line in their own right.',
            ],
            [
                'q' => 'How do I run a tour that is free but limited?',
                'a' => 'Set the event as free and give it a capacity. People register rather than pay, the count is kept for each date separately, and it stops taking names when the places are gone. If you would rather charge, the same event becomes a ticket with a price, a quantity and QR check-in at the door.',
            ],
            [
                'q' => 'Can bands and food trucks book themselves in?',
                'a' => 'Turn on booking requests and they can ask for a date through your page rather than a direct message you lose. Every request waits for you to accept it, and you are emailed when new ones are pending. Once you accept, adding them to the event as a participant puts the date on their own schedule too, so you are not both keeping the same listing up to date.',
            ],
            [
                'q' => 'Can we email people about a release?',
                'a' => 'Followers give you their email with their consent, and you write and send the newsletter yourself - nothing goes out automatically. Be aware of the allowance before you plan around it: the free plan covers 10 emails a month and Pro raises it to 100, counted per recipient rather than per send, so a single newsletter to a hundred followers uses a hundred of them.',
            ],
        ];

        $dotSections = [
            ['top', 'The week'],
            ['why', 'Why it is free'],
            ['rhythm', 'The rhythm'],
            ['ticket', 'When you sell one'],
            ['guests', 'Other people'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-pour-page" class="es-pour-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the week board                                      -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the board sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(35, 85, 57, 0.22), rgba(35, 85, 57, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 62%, rgba(154, 215, 180, 0.16), rgba(154, 215, 180, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-pour-tag es-fade-up es-d-1 mb-5">For breweries, wineries and tasting rooms</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">Nobody buys a ticket</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">to <span class="es-pour-grad">trivia night</span>.</span></span>
                    </h1>

                    <p class="es-pour-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Your events are not the product. They are the reason somebody comes in on a
                        Tuesday and buys three pints. So most of the calendar is free, and most of
                        what you need to run it is free too.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-pour-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Put the week up
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#why" class="es-pour-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See what costs nothing
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The week board. The price column is the argument. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-pour-board p-6 sm:p-8">
                        <p class="text-[0.6rem] font-bold uppercase tracking-[0.22em] text-white/65">This week at the taproom</p>

                        <div class="mt-5">
                            @foreach ($week as $i => [$wDay, $wName, $wPrice, $wNote])
                                <div @class(['flex items-baseline gap-4 py-3.5', 'es-pour-row' => $i > 0])>
                                    <span class="es-pour-day w-24 shrink-0">{{ $wDay }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-base font-bold text-white">{{ $wName }}</p>
                                        <p class="text-xs text-white/65">{{ $wNote }}</p>
                                    </div>
                                    @if ($wPrice === null)
                                        <span class="es-pour-free shrink-0">Free</span>
                                    @else
                                        <span class="es-pour-num shrink-0 text-lg font-bold text-white">${{ $wPrice }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <p class="es-pour-muted mt-5 text-sm">
                        {{ $words[$paidCount] ?? $paidCount }} of these sells a ticket. The other {{ $words[$freeCount] ?? $freeCount }} sell beer.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Why it is free (01)                                       -->
    <!-- ============================================================ -->
    <section id="why" class="scroll-mt-24 border-t border-[rgba(20,23,15,0.1)] py-20 dark:border-[rgba(236,239,233,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pour-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-pour-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Why it is free</p>
                <h2 class="es-balance es-pour-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The calendar is the <span class="es-pour-grad">reason to come in</span>.
                </h2>
                <p class="es-pour-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    A restaurant sells the dinner. A comedy club sells the ticket. You sell what
                    people drink once they are here, which means the calendar has a different job -
                    and the parts that do that job cost nothing.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="100">
                @foreach ([
                    ['The weekly nights', 'Every repeating night is one recurring event, with the weeks you are shut taken out. Set it up once in January.'],
                    ['A free tour with a limit', 'Registration with a capacity, counted for each date on its own. Limited does not have to mean paid.'],
                    ['Its own link per strand', 'Sub-schedules keep music, tours and private hire apart, and each can be shared as a filtered link.'],
                    ['On the site you have', 'Embed the calendar in your own page, and sync both ways with Google, Outlook or CalDAV.'],
                ] as [$t, $d])
                    <div class="es-pour-card es-pour-hover p-6" data-reveal>
                        <h3 class="es-pour-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-pour-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-pour-plan es-pour-plan-free">Free plan</span>
                <span class="es-pour-muted ml-2 text-sm">
                    All four, on the free plan, with no cap on how many events you put up.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The rhythm (02)                                           -->
    <!-- ============================================================ -->
    <section id="rhythm" class="scroll-mt-24 border-t border-[rgba(20,23,15,0.1)] py-20 dark:border-[rgba(236,239,233,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-pour-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-pour-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The rhythm</p>
                    <h2 class="es-balance es-pour-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set the week once, <span class="es-pour-grad">not every week</span>.
                    </h2>
                    <p class="es-pour-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        The quiz is always Wednesday. Music is always Friday. Those are three or four
                        recurring events, not two hundred posts a year, and the only weeks you touch
                        are the ones you are closed.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['One night, one event', 'Choose the day and the time. Move the quiz to eight and every future Wednesday moves with it.'],
                            ['Take the shut weeks out', 'A date exception drops the week you are closed for the holidays without disturbing the pattern.'],
                            ['Strands, not one long list', 'Music on one sub-schedule, tours on another. Each one has a link you can send on its own.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-pour-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-pour-ink font-semibold">{{ $t }}</span> <span class="es-pour-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-pour-plan es-pour-plan-free">Free plan</span>
                        <span class="es-pour-muted ml-2 text-sm">Recurring events, date exceptions and sub-schedules are all on the free plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-pour-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-pour-ink text-lg font-bold">What you actually set up</h3>
                            <span class="es-pour-muted es-pour-num text-xs">4 events, once</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Quiz night', 'Every Wednesday, 8:00pm'],
                                ['Live music', 'Every Friday, 8:30pm'],
                                ['Brewery tour', 'Every Saturday, 2:00pm'],
                                ['Yoga in the yard', 'Every Sunday, 10:00am'],
                            ] as [$rName, $rWhen])
                                <div class="es-pour-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-pour-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $rName }}</span>
                                    <span class="es-pour-muted es-pour-num shrink-0 text-xs">{{ $rWhen }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 border-t border-[rgba(20,23,15,0.1)] pt-4 dark:border-[rgba(236,239,233,0.12)]">
                            <p class="es-pour-tag mb-2">Date exceptions</p>
                            <p class="es-pour-muted text-xs">
                                Closed the 25th and the 1st. Both taken out; every other week runs as normal.
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
    <!-- 4. When you sell one (03)                                    -->
    <!-- ============================================================ -->
    <section id="ticket" class="scroll-mt-24 border-t border-[rgba(20,23,15,0.1)] py-20 dark:border-[rgba(236,239,233,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pour-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-pour-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">When you sell one</p>
                <h2 class="es-balance es-pour-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The tour is the <span class="es-pour-grad">exception</span>.
                </h2>
                <p class="es-pour-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Twelve people, a glass at each tank, somebody has to be paid to run it. That one
                    is worth charging for - and it is the only row on the board that is.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-pour-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-pour-ink text-lg font-bold">Limited, and free</h3>
                        <span class="es-pour-plan es-pour-plan-free">Free plan</span>
                    </div>
                    <p class="es-pour-muted mb-5 text-sm">When you want the count but not the money.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'People register instead of paying.',
                            'A capacity per date, so a full Saturday leaves next Saturday open.',
                            'It stops taking names when the places are gone.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-pour-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-pour-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-pour-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-pour-ink text-lg font-bold">Paid, with a door to scan</h3>
                        <span class="es-pour-plan es-pour-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-pour-muted mb-5 text-sm">The same event, with a price on it.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'A price and a quantity, counted per date the same way.',
                            'QR check-in, so the person on the door is not holding a printout.',
                            'Payment through your own Stripe account, with no platform fee on top.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-pour-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-pour-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <p class="es-pour-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Selling that one tour is the only thing on this page that needs Pro. Everything
                above it on the board stays free.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Other people (04)                                         -->
    <!-- ============================================================ -->
    <section id="guests" class="scroll-mt-24 border-t border-[rgba(20,23,15,0.1)] py-20 dark:border-[rgba(236,239,233,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-pour-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-pour-ink text-lg font-bold">Waiting for you</h3>
                                <span class="es-pour-muted es-pour-num text-xs">4 requests</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Fri 14 Mar', 'Marlowe Duo', 'Live music, 8:30pm'],
                                    ['Sat 15 Mar', 'El Camion', 'Food truck, 12 to 8'],
                                    ['Thu 20 Mar', 'Slice Machine', 'Food truck, 5 to 9'],
                                    ['Sun 23 Mar', 'Ash Weller', 'Solo set, 4:00pm'],
                                ] as [$qWhen, $qWho, $qWhat])
                                    <div class="es-pour-sub flex items-baseline gap-3 p-3.5">
                                        <span class="es-pour-muted es-pour-num w-24 shrink-0 text-xs">{{ $qWhen }}</span>
                                        <div class="min-w-0 flex-1">
                                            <p class="es-pour-ink truncate text-sm font-semibold">{{ $qWho }}</p>
                                            <p class="es-pour-muted truncate text-xs">{{ $qWhat }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-pour-muted mt-5 border-t border-[rgba(20,23,15,0.1)] pt-4 text-xs dark:border-[rgba(236,239,233,0.12)]">
                                None of them are on the public calendar until you accept them.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-pour-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                    <p class="es-pour-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Other people</p>
                    <h2 class="es-balance es-pour-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Half the calendar is <span class="es-pour-grad">somebody else's van</span>.
                    </h2>
                    <p class="es-pour-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A duo on Friday, a van in the yard on Thursday, a quiz host who does it for beer.
                        You make the drink; a lot of the programme is other people's work, and right
                        now they are asking you for dates in four different apps.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['They ask through the page', 'Turn on booking requests and the asks arrive attached to the date they are for, instead of a message you scroll past.'],
                            ['Nothing posts without you', 'Every request waits for you to accept it, so the public calendar only shows what you agreed to.'],
                            ['One entry, two calendars', 'Add them to the event as a participant and the date lands on their own schedule too, if they run one here.'],
                        ] as [$t, $d])
                            <div class="es-pour-card es-pour-hover p-4" data-reveal>
                                <p class="es-pour-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-pour-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-6" data-reveal>
                        <span class="es-pour-plan es-pour-plan-free">Free plan</span>
                        <span class="es-pour-muted ml-2 text-sm">Requests and participants cost nothing, and neither does a schedule for the van.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Who it is for (05)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(20,23,15,0.1)] py-20 dark:border-[rgba(236,239,233,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pour-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-pour-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-pour-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Anywhere with <span class="es-pour-grad">a bar and a back room</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Craft Breweries"
                    description="A taproom open five nights with something on most of them, and one paid tour at the weekend."
                    icon-color="emerald"
                    blog-slug="for-craft-breweries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h12v10a2 2 0 01-2 2H6a2 2 0 01-2-2V8zm12 2h3a1 1 0 011 1v4a1 1 0 01-1 1h-3M7 4v4m3-4v4m3-4v4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Wineries"
                    description="Tastings booked ahead and a cellar door open at weekends, kept on separate strands of the same link."
                    icon-color="rose"
                    blog-slug="for-wineries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 3h8l-1 7a3 3 0 01-6 0L8 3zm4 10v8m-3 0h6" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Cideries"
                    description="A pressing day in autumn and a quiet room in February. Recurring nights plus the one-offs that follow the season."
                    icon-color="amber"
                    blog-slug="for-cideries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.6c-1-1-2.4-1.2-3.5-.5-1.5.9-2 2.7-1.5 4.6.5 1.9 1.6 3.7 2.8 4.9.8.8 1.7.6 2.2.3.5.3 1.4.5 2.2-.3 1.2-1.2 2.3-3 2.8-4.9.5-1.9 0-3.7-1.5-4.6-1.1-.7-2.5-.5-3.5.5zm0 0V6m0 0c0-1.2 1-2 2.2-2" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Distilleries"
                    description="Tours that need paying for and a capacity that matters, alongside a tasting room that does not."
                    icon-color="orange"
                    blog-slug="for-distilleries"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3h6v5l3 8a3 3 0 01-3 4H9a3 3 0 01-3-4l3-8V3zm-1 9h8" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Meaderies"
                    description="A small room and a loyal list. Followers you can email yourself, and a calendar people can subscribe to."
                    icon-color="yellow"
                    blog-slug="for-meaderies"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v10l-7 4-7-4V7l7-4zm0 0v18m7-14l-7 4-7-4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Brewpubs"
                    description="Food and beer under one roof, so the kitchen's ticketed nights and the free ones sit on the same calendar."
                    icon-color="slate"
                    blog-slug="for-brewpubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v8a3 3 0 006 0V3M8 11v10M16 3c-1.5 2-2 4-2 6a2 2 0 004 0c0-2-.5-4-2-6zm0 8v10" />
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
        <div class="es-pour-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-pour-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-pour-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        An hour in January, <span class="es-pour-grad">then the year runs</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Put the repeating nights up', 'One recurring event per night, with the weeks you are shut taken out. Most of them are free and stay free.'],
                        ['02', 'Open the requests', 'Bands and vans ask for dates through the page. You accept the ones you want and nothing else appears.'],
                        ['03', 'Charge for the one that needs it', 'The tour gets a price, a quantity and a QR code on the door. That is the only part on Pro.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-pour-lit es-pour-num mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="scroll-mt-24 border-t border-[rgba(20,23,15,0.1)] py-20 dark:border-[rgba(236,239,233,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-pour-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A weekly night set up once, with the shut weeks taken out" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Music, tours and private hire on their own strands and links" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="For the paid tour: a quantity, QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Put the taproom calendar on the site you already have" :url="marketing_url('/features/embed-calendar')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-pour-accent inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-[rgba(20,23,15,0.1)] py-16 dark:border-[rgba(236,239,233,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-pour-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-bars', 'Bars'],
                    ['/for-restaurants', 'Restaurants'],
                    ['/for-food-trucks-and-vendors', 'Food Trucks'],
                    ['/for-music-venues', 'Music Venues'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-pour-card es-pour-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-pour-muted text-sm">Event Schedule for</div>
                            <div class="es-pour-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-pour-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-pour-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(20,23,15,0.1)] py-20 dark:border-[rgba(236,239,233,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-pour-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-pour-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-pour-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-pour-grad">across the bar</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-pour-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-pour-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-pour-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-pour-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-pour-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-pour-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put the week up and <span class="es-pour-grad">leave it there</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The nights, the strands and the requests cost nothing. Pay only when you
                        actually sell a ticket.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-brewery" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-pour-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Put the week up
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1a1c1a] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
