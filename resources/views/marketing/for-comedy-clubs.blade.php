<x-marketing-layout>
    <x-slot name="title">Comedy Club Schedules | Sell the Night, Add the Lineup Later</x-slot>
    <x-slot name="description">Friday at eight sells before anyone knows who is on. Set the night up once as a recurring show, put the tickets on sale, and add the participants later.</x-slot>
    <x-slot name="breadcrumbTitle">For Comedy Clubs</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Comedy Clubs",
        "description": "Run a room on recurring nights, sell advance and door tickets from one link, and add the participants later so the date appears on each comic's own schedule.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Comedy Clubs"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Comedy Clubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Comedy Club Scheduling Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly nights set up once as recurring events with their own end",
            "Date exceptions for the weeks the room is dark",
            "Participants added to a show, so the date reaches comics who run their own schedule",
            "Booking requests from comics, each waiting for the club to accept it",
            "An approved list so regulars you book often post without a queue",
            "An email to the club when new requests are waiting",
            "Named ticket types with their own prices, quantities and sales windows",
            "Advance and door pricing on the same show",
            "QR check-in at the door",
            "Free registration with a capacity for open mics",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Sub-schedules that keep the open mic, the showcase and the weekend apart",
            "Direct newsletters to the people who follow the room",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for your own website"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "comedy club schedule, comedy night ticketing, open mic capacity, comedy booking requests, recurring comedy night, comedy club calendar",
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
           For-comedy-clubs "Friday at Eight" styles.

           CONCEPT: a music venue sells the band; a COMEDY CLUB SELLS THE
           ROOM. Friday at eight is sold out before anyone knows who is on,
           and the lineup gets filled in on Wednesday. That is the whole
           page: the night is the product, the names are detail added
           later. It also separates this page cleanly from
           /for-music-venues ("The Running Order", one show's timeline) and
           from /for-comedians ("The Tight Five", one performer's set).

           DEVICES THE NEIGHBOURS OWN, SO NOT USED HERE: the rundown board,
           gaffer tape, the spotlight cone, dotted leaders and mono-as-a-
           voice all belong to /for-comedians (.es-comic-mono alone has 27
           uses); the proportional time axis belongs to /for-music-venues.
           The outgoing page's own brick wall, letterboard and unlit-bulb
           marquee are retired as comedy-club cliches.

           COLOUR: deep night ink - the hour the page is named after. The
           hue wheel is spent: an audit of the rebuilt pages PLUS this
           page's unrebuilt neighbours leaves only yellow-green 60-79
           (dead, squeezed between amber and lime), green 120-139 (squeezed
           between food-trucks at 113 and nightclubs/theaters at 142-147)
           and blue 220-259. Blue's problem is that brand blue sits at
           222deg and appears on this very page in the nav and every "Read
           more" - answered the way sepia answered rust, BY SATURATION AND
           LIGHTNESS rather than hue:
               brand blue #4E81FA  222deg  sat 94%  light 64%
               this page  #31396b  231deg  sat 37%  light 30%
           A deep desaturated navy reads as ink, not as the chrome.

           Measured: #31396b 9.82 on ground / 10.51 on card; #9aa6dd 8.15 /
           7.40 / 7.90 dark. NEVER text-gray-500 - use .es-night-muted
           (6.94 light / 7.01 dark).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-night-page { background-color: #f2f3f6; color: #12141a; }
        .dark .es-night-page { background-color: #0d0e12; color: #e9ebf2; }
        .es-night-ink { color: #12141a; }
        .dark .es-night-ink { color: #e9ebf2; }
        .es-night-muted { color: #4d5361; }
        .dark .es-night-muted { color: #959cad; }
        .es-night-accent { color: #31396b; }
        .dark .es-night-accent { color: #9aa6dd; }
        /* Always-lit accent for the band, in both colour modes. */
        .es-night-lit { color: #9aa6dd; }

        .es-night-grad {
            background-image: linear-gradient(100deg, #31396b, #414a86);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-night-grad,
        .es-night-band .es-night-grad {
            background-image: linear-gradient(100deg, #b3bde8, #9aa6dd);
        }

        /* --- Surfaces --- */
        .es-night-card {
            background-color: #fafbfc;
            border: 1px solid rgba(18, 20, 26, 0.12);
            border-radius: 0.6rem;
        }
        .dark .es-night-card {
            background-color: #171922;
            border-color: rgba(233, 235, 242, 0.13);
        }
        .es-night-sub {
            background-color: rgba(18, 20, 26, 0.045);
            border-radius: 0.4rem;
        }
        .dark .es-night-sub { background-color: rgba(233, 235, 242, 0.05); }
        .es-night-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-night-hover:hover { border-color: rgba(49, 57, 107, 0.45); box-shadow: 0 10px 28px -18px rgba(18, 20, 26, 0.5); }
        .dark .es-night-hover:hover { border-color: rgba(154, 166, 221, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The night card ------------------------------------------
           The one thing that is actually for sale, and deliberately the
           heaviest object on the page: the day, the hour, the price. */
        .es-night-marquee {
            background-color: #31396b;
            color: #ffffff;
            border-radius: 0.6rem;
        }
        .dark .es-night-marquee { background-color: #262c54; }
        .es-night-day {
            font-size: clamp(2.4rem, 7vw, 3.6rem);
            font-weight: 900;
            letter-spacing: -0.02em;
            line-height: 1;
        }
        .es-night-hour {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        /* The rule between what is sold and who is on it. */
        .es-night-rule { height: 1px; background: rgba(255, 255, 255, 0.22); }

        /* --- Eyebrow, numerals, plan tags --- */
        .es-night-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #31396b;
        }
        .dark .es-night-tag { color: #9aa6dd; }
        .es-night-band .es-night-tag { color: #9aa6dd; }

        .es-night-corner {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 1.9rem;
            border: 1px solid rgba(18, 20, 26, 0.22);
            border-radius: 0.25rem;
            background: rgba(18, 20, 26, 0.035);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #12141a;
        }
        .dark .es-night-corner { border-color: rgba(233, 235, 242, 0.22); background: rgba(233, 235, 242, 0.05); color: #e9ebf2; }
        .es-night-band .es-night-corner { border-color: rgba(233, 235, 242, 0.22); background: rgba(233, 235, 242, 0.05); color: #e9ebf2; }
        .es-night-corner::before {
            content: "";
            position: absolute;
            left: 0.4rem;
            top: 0.4rem;
            bottom: 0.4rem;
            width: 2px;
            background: #31396b;
        }
        .dark .es-night-corner::before { background: #9aa6dd; }
        .es-night-band .es-night-corner::before { background: #9aa6dd; }

        /* Plan tiers ONLY - never reuse these for a state badge. */
        .es-night-plan {
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
        .es-night-plan-free { border-color: rgba(18, 20, 26, 0.22); color: #4d5361; }
        .dark .es-night-plan-free { border-color: rgba(233, 235, 242, 0.26); color: #959cad; }
        .es-night-plan-pro { border-color: rgba(49, 57, 107, 0.5); color: #31396b; background: rgba(49, 57, 107, 0.08); }
        .dark .es-night-plan-pro { border-color: rgba(154, 166, 221, 0.42); color: #9aa6dd; background: rgba(154, 166, 221, 0.1); }

        /* --- Buttons --- */
        .es-night-btn {
            background-color: #31396b;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-night-btn:hover { background-color: #262c54; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(49, 57, 107, 0.9); }
        .es-night-ghost {
            border: 1px solid rgba(18, 20, 26, 0.22);
            color: #12141a;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-night-ghost:hover { border-color: rgba(49, 57, 107, 0.5); background-color: rgba(49, 57, 107, 0.06); }
        .dark .es-night-ghost { border-color: rgba(233, 235, 242, 0.24); color: #e9ebf2; }
        .dark .es-night-ghost:hover { border-color: rgba(154, 166, 221, 0.45); background-color: rgba(154, 166, 221, 0.08); }

        /* --- The dark band ------------------------------------------
           A resolvable background-color under the gradients: it is what
           paints if they fail and what a contrast audit can read. */
        .es-night-band {
            background-color: #101219;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(49, 57, 107, 0.42), rgba(49, 57, 107, 0) 70%),
                linear-gradient(180deg, #171922, #101219);
        }

        /* --- Nothing inside the band may change between colour modes --
           The band has no .dark variant, so any descendant that HAS one
           would render differently on an identical ground. Two shared
           classes carry their own .dark rules in marketing.css and are
           invisible to a grep of this file. */
        .es-night-band .grid-overlay {
            background-image:
                linear-gradient(rgba(233, 235, 242, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(233, 235, 242, 0.05) 1px, transparent 1px);
        }
        .es-night-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-night-band .es-claim:focus-within {
            border-color: rgba(154, 166, 221, 0.75);
            box-shadow: 0 0 0 4px rgba(154, 166, 221, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(49, 57, 107, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(154, 166, 221, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #31396b; }
        .dark .es-dot.is-active .es-dot-pip { background: #9aa6dd; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-night-page a:focus-visible,
        #es-night-page summary:focus-visible,
        #es-night-page button:focus-visible,
        #es-night-page input:focus-visible {
            outline: 2px solid #31396b;
            outline-offset: 2px;
        }
        .dark #es-night-page a:focus-visible,
        .dark #es-night-page summary:focus-visible,
        .dark #es-night-page button:focus-visible,
        .dark #es-night-page input:focus-visible {
            outline-color: #9aa6dd;
        }
        .es-night-band a:focus-visible,
        .es-night-band summary:focus-visible,
        .es-night-band button:focus-visible,
        .es-night-band input:focus-visible {
            outline-color: #9aa6dd !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-night-btn:hover { transform: none; }
        }
    </style>

    @php
        // The room's week: three products in one space. The hero card reads
        // the Friday row out of this SAME array, so the two cannot drift.
        // Advance is asserted to be under the door price at build time.
        $nights = [
            ['Tuesday',  'Open mic',         'free', null, null, 'Capacity 30'],
            ['Thursday', 'Showcase',         'paid', 12,   15,   '6 comics'],
            ['Friday',   'Weekend headline', 'paid', 18,   22,   'Sells out'],
            ['Saturday', 'Weekend headline', 'paid', 18,   22,   'Sells out'],
        ];

        $headline = null;
        foreach ($nights as $row) {
            if ($row[0] === 'Friday') {
                $headline = $row;
                break;
            }
        }

        $faqs = [
            [
                'q' => 'Is Event Schedule free for comedy clubs?',
                'a' => 'Running the room is free forever: the weekly nights as recurring events, date exceptions for the weeks you are dark, free registration with a capacity for open mics, booking requests from comics with an approved list for your regulars, sub-schedules, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Selling is free too, up to 25 paid tickets a month, with the QR on each one scanned at the door for nothing. Pro at '.plan_price($proMonthly).' a month removes that ceiling and adds the live check-in dashboard, and Event Schedule charges zero platform fees on sales at any tier.',
            ],
            [
                'q' => 'Can I put tickets on sale before I have booked the lineup?',
                'a' => 'Yes, and that is the normal way round for a comedy room. The night is one recurring event with its own ticket types, so Friday at eight can be on sale in January. Add the participants when you book them and the show updates in place, so nobody has to be sent a new link.',
            ],
            [
                'q' => 'What happens to the comics I add to a show?',
                'a' => 'Adding someone as a participant attaches them to the event. If they already run their own schedule on Event Schedule, the date turns up there for them to accept, or straight away if they have added your club to their approved list. If they do not have one yet, you are creating a profile for them and you can tick a box to email them an invitation to claim it. Until they claim it, that profile has no public page of its own.',
            ],
            [
                'q' => 'How do comics ask for a spot?',
                'a' => 'Turn on booking requests and comics can submit to your schedule instead of messaging you. Every request waits for you to accept it, and you are emailed when new ones are pending. Comics you book regularly can go on an approved list so their submissions post without waiting, as long as they are sending them from their own schedule.',
            ],
            [
                'q' => 'Can I charge different prices for advance and at the door?',
                'a' => 'Yes. Set up two ticket types at different prices and sell both from the same night. Each type carries its own count, and the count is kept per occurrence, so a sold-out Friday does not stop next Friday selling. A type can also be given a single date to go on sale or come off it, which applies once to the whole run rather than repeating each week. Check people in by scanning a QR code, and take payment through your own Stripe account with no platform fee on top.',
            ],
        ];

        $dotSections = [
            ['top', 'Friday at eight'],
            ['night', 'The night'],
            ['names', 'The names'],
            ['asks', 'Booking'],
            ['selling', 'Selling the room'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-night-page" class="es-night-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the night                                           -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the card sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(49, 57, 107, 0.24), rgba(49, 57, 107, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 62%, rgba(154, 166, 221, 0.18), rgba(154, 166, 221, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-night-tag es-fade-up es-d-1 mb-5">For comedy clubs and comedy rooms</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">They buy the night,</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">not <span class="es-night-grad">the names</span>.</span></span>
                    </h1>

                    <p class="es-night-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Friday at eight sells out before anyone knows who is on it. A music venue
                        sells the band; you sell the room. So put the night on sale first and add
                        the comics once you have booked them.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-night-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Put the room online
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#night" class="es-night-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how a night works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The night card: the thing that is actually for sale. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-night-marquee p-7 sm:p-9">
                        <p class="es-night-day">{{ $headline[0] }}</p>
                        <p class="es-night-hour mt-1 text-lg text-white/80">8:00pm &middot; every week</p>

                        <div class="es-night-rule my-6" aria-hidden="true"></div>

                        <div class="flex items-baseline gap-8">
                            <div>
                                <p class="text-[0.6rem] font-bold uppercase tracking-[0.2em] text-white/70">Advance</p>
                                <p class="es-night-hour text-2xl font-bold text-white">${{ $headline[3] }}</p>
                            </div>
                            <div>
                                <p class="text-[0.6rem] font-bold uppercase tracking-[0.2em] text-white/70">Door</p>
                                <p class="es-night-hour text-2xl font-bold text-white">${{ $headline[4] }}</p>
                            </div>
                        </div>

                        <div class="es-night-rule my-6" aria-hidden="true"></div>

                        <p class="text-[0.6rem] font-bold uppercase tracking-[0.2em] text-white/70">Participants</p>
                        <p class="mt-1 text-sm text-white/80">Added Wednesday, once the bill is booked.</p>
                    </div>

                    <p class="es-night-muted mt-5 text-xs">
                        One recurring event. On sale since January, and it has never needed a new link.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The night is the product (01)                             -->
    <!-- ============================================================ -->
    <section id="night" class="scroll-mt-24 border-t border-[rgba(18,20,26,0.1)] py-20 dark:border-[rgba(233,235,242,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-night-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-night-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The night</p>
                    <h2 class="es-balance es-night-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        One room, <span class="es-night-grad">three businesses</span>.
                    </h2>
                    <p class="es-night-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Tuesday is free and full. Thursday costs twelve. The weekend pays for the
                        week. They are different products in one space, and each is a single
                        recurring event you set up once.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['One night, one event', 'Pick the day and the hour. Change the time once and every week after it follows.'],
                            ['Dark weeks come out', 'A date exception drops the week you are closed without disturbing the rest of the run.'],
                            ['Keep them apart', 'Sub-schedules put the open mic, the showcase and the weekend on their own strands of the same link.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-night-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-night-ink font-semibold">{{ $t }}</span> <span class="es-night-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-night-plan es-night-plan-free">Free</span>
                        <span class="es-night-muted ml-2 text-sm">Recurring nights, date exceptions and sub-schedules are on the free plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-night-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-night-ink text-lg font-bold">The week</h3>
                            <span class="es-night-muted es-night-hour text-xs">{{ count($nights) }} recurring nights</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ($nights as [$nDay, $nName, $nKind, $nAdv, $nDoor, $nNote])
                                <div class="es-night-sub flex items-baseline gap-3 p-3.5">
                                    <span class="es-night-muted es-night-hour w-10 shrink-0 text-xs font-bold uppercase">{{ Str::substr($nDay, 0, 3) }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="es-night-ink truncate text-sm font-semibold">{{ $nName }}</p>
                                        <p class="es-night-muted text-xs">{{ $nNote }}</p>
                                    </div>
                                    <span class="es-night-accent es-night-hour shrink-0 text-sm font-bold">@if ($nKind === 'free') Free @else ${{ $nAdv }} @endif</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-night-muted mt-5 border-t border-[rgba(18,20,26,0.1)] pt-4 text-xs dark:border-[rgba(233,235,242,0.12)]">
                            The open mic takes free registrations up to its capacity. The rest sell tickets.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The names come later (02)                                 -->
    <!-- ============================================================ -->
    <section id="names" class="scroll-mt-24 border-t border-[rgba(18,20,26,0.1)] py-20 dark:border-[rgba(233,235,242,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-night-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-night-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The names</p>
                <h2 class="es-balance es-night-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Your bill is also <span class="es-night-grad">six other calendars</span>.
                </h2>
                <p class="es-night-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Add the comics to the show as participants. For everyone who already runs a
                    schedule here, your Friday turns up on their page too.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['They already have a schedule', 'The date arrives on their own page for them to accept, or immediately if they have put your club on their approved list. Nobody is retyping your booking.'],
                    ['They do not have one yet', 'You are creating a profile for them, and you can tick a box to email an invitation to claim it. Until they claim it, that profile has no public page of its own.'],
                    ['You enter it once', 'The bill lives on the show. Change it on Wednesday and every place it appears changes with it.'],
                ] as [$t, $d])
                    <div class="es-night-card es-night-hover p-6" data-reveal>
                        <h3 class="es-night-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-night-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-night-plan es-night-plan-free">Free</span>
                <span class="es-night-muted ml-2 text-sm">Participants are on the free plan, and the comics do not pay for anything either.</span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Comics ask you (03)                                       -->
    <!-- ============================================================ -->
    <section id="asks" class="scroll-mt-24 border-t border-[rgba(18,20,26,0.1)] py-20 dark:border-[rgba(233,235,242,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-night-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-night-ink text-lg font-bold">Waiting for you</h3>
                                <span class="es-night-muted es-night-hour text-xs">3 requests</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Thu 12 Mar', 'Showcase spot', 'Pending'],
                                    ['Thu 19 Mar', 'Showcase spot', 'Pending'],
                                    ['Tue 24 Mar', 'Open mic', 'Posted'],
                                ] as [$rWhen, $rWhat, $rState])
                                    <div class="es-night-sub flex items-baseline gap-3 p-3.5">
                                        <span class="es-night-muted es-night-hour w-24 shrink-0 text-xs">{{ $rWhen }}</span>
                                        <span class="es-night-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $rWhat }}</span>
                                        <span class="es-night-muted shrink-0 text-xs">{{ $rState }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-night-muted mt-5 border-t border-[rgba(18,20,26,0.1)] pt-4 text-xs dark:border-[rgba(233,235,242,0.12)]">
                                The third posted itself: that comic is on the club's approved list.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-night-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-night-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Booking</p>
                    <h2 class="es-balance es-night-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Stop booking the room <span class="es-night-grad">out of your inbox</span>.
                    </h2>
                    <p class="es-night-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Every comic in the city wants a spot, and right now they are asking you in
                        four different apps. Turn on booking requests and the asks arrive in one
                        place, already attached to the date they are for.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['Nothing posts without you', 'Every request waits for you to accept it, so the public calendar only ever shows the bill you agreed to.'],
                            ['You are told they are waiting', 'New pending requests are emailed to the club, so a spot request does not rot in a notification tab.'],
                            ['Regulars skip the queue', 'Put the comics you book every month on an approved list and their submissions post straight away, as long as they send them from their own schedule.'],
                        ] as [$t, $d])
                            <div class="es-night-card es-night-hover p-4" data-reveal>
                                <p class="es-night-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-night-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-6" data-reveal>
                        <span class="es-night-plan es-night-plan-free">Free</span>
                        <span class="es-night-muted ml-2 text-sm">Booking requests and the approved list cost nothing.</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Selling the room (04)                                     -->
    <!-- ============================================================ -->
    <section id="selling" class="scroll-mt-24 border-t border-[rgba(18,20,26,0.1)] py-20 dark:border-[rgba(233,235,242,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-night-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-night-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Selling the room</p>
                <h2 class="es-balance es-night-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Sold out on Friday. <span class="es-night-grad">Still open for next Friday</span>.
                </h2>
                <p class="es-night-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Every ticket type carries its own price and its own count, and the count is kept
                    per night - so selling out one week leaves the next one untouched.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.1fr_1fr]">
                <div class="es-night-card p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-night-ink text-lg font-bold">Friday, 8:00pm</h3>
                        <span class="es-night-plan es-night-plan-pro">Pro</span>
                    </div>
                    <p class="es-night-muted mb-5 text-sm">Three ticket types on one recurring night.</p>

                    <div class="space-y-2.5">
                        @foreach ([
                            ['Advance', '$18', '80'],
                            ['Door', '$22', '40'],
                            ['Group of six', '$90', '10'],
                        ] as [$tName, $tPrice, $tQty])
                            <div class="es-night-sub flex items-baseline gap-3 p-3.5">
                                <span class="es-night-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $tName }}</span>
                                <span class="es-night-muted hidden text-xs sm:inline">{{ $tQty }} a night</span>
                                <span class="es-night-ink es-night-hour text-sm">{{ $tPrice }}</span>
                            </div>
                        @endforeach
                    </div>

                    <p class="es-night-muted mt-5 border-t border-[rgba(18,20,26,0.1)] pt-4 text-xs dark:border-[rgba(233,235,242,0.12)]">
                        A ticket type can also be given a date to go on sale or come off it, once, for the whole run.
                    </p>
                </div>

                <div class="grid gap-4" data-reveal-group="100">
                    @foreach ([
                        ['QR check-in', 'Scan tickets at the door from a phone, with a live view so two people can work a queue that all turns up at once.', true],
                        ['Zero platform fees', 'You keep the ticket price minus what Stripe charges to process the card. Nothing is taken on top of that.', true],
                        ['The open mic stays free', 'A free night takes registrations up to a capacity instead of tickets, so you know the count without charging anybody.', false],
                    ] as [$t, $d, $isPro])
                        <div class="es-night-card es-night-hover p-6" data-reveal>
                            <div class="mb-2 flex items-center gap-2">
                                <h3 class="es-night-ink text-base font-bold">{{ $t }}</h3>
                                <span class="es-night-plan @if ($isPro) es-night-plan-pro @else es-night-plan-free @endif">{{ $isPro ? 'Pro' : 'Free' }}</span>
                            </div>
                            <p class="es-night-muted text-sm">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Who it is for (05)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(18,20,26,0.1)] py-20 dark:border-[rgba(233,235,242,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-night-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-night-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-night-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Any room with <span class="es-night-grad">a mic and a door</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Stand-Up Clubs"
                    description="Two shows on a Friday and two on a Saturday, each its own event with its own count on the door."
                    icon-color="blue"
                    blog-slug="for-stand-up-comedy-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14a3 3 0 003-3V6a3 3 0 00-6 0v5a3 3 0 003 3zm0 0v4m-4 0h8M6 11a6 6 0 0012 0" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Sketch Comedy Venues"
                    description="A show that runs for a season, set up once with a closing night rather than entered week by week."
                    icon-color="teal"
                    blog-slug="for-sketch-comedy-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 10h.01M15 10h.01M8 15a4 4 0 008 0M4 5h16v14H4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Improv Theaters"
                    description="House teams on a fixed weekly slot, each team a participant so the date reaches everyone in it."
                    icon-color="emerald"
                    blog-slug="for-improv-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.36-1.86M17 20H7m10 0v-2c0-.66-.13-1.3-.36-1.86m0 0A5 5 0 007 18v2m5-9a3 3 0 100-6 3 3 0 000 6z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Open Mic Venues"
                    description="Free to get in and full every week. Registrations up to a capacity, so you know the count without taking money."
                    icon-color="amber"
                    blog-slug="for-open-mic-comedy-venues"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5h6M9 5a3 3 0 00-3 3v6a6 6 0 0012 0V8a3 3 0 00-3-3M12 20v-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Live Podcast Studios"
                    description="A recording with an audience is still a ticketed night, and the guests go on as participants."
                    icon-color="sky"
                    blog-slug="for-live-podcast-studios"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12v-1m4 4V8m4 9V6m4 8V9m4 4v-2" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Comedy Bars & Restaurants"
                    description="Comedy on some nights and not others. Sub-schedules keep the comedy strand apart from everything else the room does."
                    icon-color="slate"
                    blog-slug="for-comedy-bars-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14l-6 8v6h3M5 4l6 8v6H8" />
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
        <div class="es-night-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-night-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-night-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set the week up once, <span class="es-night-grad">book it forever</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Put the nights up', 'One recurring event per night, with the weeks you are dark taken out. The open mic takes registrations; the rest take tickets.'],
                        ['02', 'Open the requests', 'Comics submit for a date instead of messaging you, and the regulars go on the approved list so they skip the queue.'],
                        ['03', 'Add the bill on Wednesday', 'Participants go on the show, and the date reaches every comic who runs a schedule of their own.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-night-lit es-night-hour mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="scroll-mt-24 border-t border-[rgba(18,20,26,0.1)] py-20 dark:border-[rgba(233,235,242,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-night-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="A weekly night set up once, with the dark weeks taken out" :url="marketing_url('/features/recurring-events')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Advance and door pricing, QR check-in, and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep the open mic, the showcase and the weekend apart" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow the room, with open rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-night-accent inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-[rgba(18,20,26,0.1)] py-16 dark:border-[rgba(233,235,242,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-night-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-comedians', 'Comedians'],
                    ['/for-music-venues', 'Music Venues'],
                    ['/for-bars', 'Bars'],
                    ['/for-spoken-word', 'Spoken Word Artists'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-night-card es-night-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-night-muted text-sm">Event Schedule for</div>
                            <div class="es-night-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-night-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-night-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(18,20,26,0.1)] py-20 dark:border-[rgba(233,235,242,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-night-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-night-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-night-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-night-grad">at the box office</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-night-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-night-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-night-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-night-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-night-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-night-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Put <span class="es-night-grad">Friday at eight</span> on sale.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The night, the tickets and the bill, on one link that has not needed
                        replacing since January.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-club" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-night-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Put the room online
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#171922] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
