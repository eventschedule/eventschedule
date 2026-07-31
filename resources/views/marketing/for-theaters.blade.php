<x-marketing-layout>
    <x-slot name="title">Theater Calendars | Runs, Season Passes and Ticketing</x-slot>
    <x-slot name="description">Set a production up once as a run - Tuesday to Sunday, dark Mondays, closing after fourteen performances - and sell every one from a single link with zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">For Theaters</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Theaters",
        "description": "Set a production up once as a run with a day-of-week pattern, dark days and a closing performance, then sell the whole run from one link with zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Theaters"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Theaters",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Theater Management Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Runs set up once with a day-of-week pattern, dark days and a closing performance",
            "Recurrences that end on a date or after a set number of performances",
            "Season passes valid for every performance of a run, once each",
            "Named ticket types with their own prices, quantities and sales windows",
            "Custom questions collected at checkout",
            "QR check-in with a real-time check-in dashboard",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Sub-schedules that keep mainstage, studio and family programming apart",
            "Direct newsletters with open and click rates",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for the website you already have"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "theater calendar, show run scheduling, season pass, theater ticketing, performance dates, matinee scheduling",
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
        "name": "How to put a theatrical run online with Event Schedule",
        "description": "Set the run up once and sell the whole thing from one link.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Set the run",
                "text": "Create the production as a recurring event, pick the days it plays, and give it an end: a closing date, or a number of performances."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Mark the dark days",
                "text": "Add date exceptions for the nights you are dark, and add the matinee as its own event on the days it plays."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Sell the run",
                "text": "Add named ticket types for each price, and a season pass valid for every performance of the run, once each."
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
           For-theaters "The Run" styles. A production is not a date, it
           is a finite block of performances: Tuesday to Sunday, dark
           Mondays, closing after fourteen.

           THE SIGNATURE DEVICE IS A SPAN, NOT A WEEK GRID. Two reasons,
           both found in review. (1) `grid-cols-7` already appears on
           eight marketing pages including recurring-events.blade.php,
           so a weeks-by-days grid would be house furniture, not a
           signature. (2) A recurring event carries ONE starts_at plus
           days_of_week, so it yields one performance per matching day -
           a Saturday matinee and evening CANNOT be the same event.
           Drawing a doubled Saturday cell would have taught a model the
           product does not have. So the run is a horizontal strip of
           day slots, and the matinee is its OWN strip beneath it, which
           is exactly how the product stores it.

           COLOUR: heritage green. After nine rebuilds the hue wheel is
           spent - green is the only unclaimed space left, and it has a
           real theatrical anchor in the green room. Deep greens
           (#14532d) are unused; /for-nightclubs uses green only as a
           bright exit-sign STATE signal on an achromatic page, so a
           bottle-green identity stays distinct. Spend it sparingly: the
           run, the pass, one word per heading.

           NEVER use text-gray-500 - it measures 4.83 on white but only
           4.2-4.5 on a tinted ground like this page's. Use
           .es-bill-muted (7.31).

           BLADE RULE for this block: never use @supports probes here.
           A "#" hex inside a parenthesized at-rule condition breaks
           Blade compilation of every later parenthesized directive.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-bill-page { background-color: #f6f7f6; color: #121814; }
        .dark .es-bill-page { background-color: #0b110e; color: #e8ece9; }
        .es-bill-ink { color: #121814; }
        .dark .es-bill-ink { color: #e8ece9; }
        .es-bill-muted { color: #4a5450; }
        .dark .es-bill-muted { color: #9aa8a1; }
        .es-bill-accent { color: #14532d; }
        .dark .es-bill-accent { color: #86efac; }
        /* Always-lit accent for the fixed-dark bands, in both colour modes. */
        .es-bill-lit { color: #86efac; }

        /* --- The run strip: one slot per day across the run --- */
        .es-bill-strip {
            display: flex;
            gap: 0.2rem;
            align-items: stretch;
        }
        .es-bill-day {
            flex: 1 1 0;
            min-width: 0;
            height: 2.1rem;
            border-radius: 0.2rem;
            background: rgba(18, 24, 20, 0.07);
        }
        .dark .es-bill-day { background: rgba(232, 236, 233, 0.08); }
        /* A performance. */
        .es-bill-day-on { background: #14532d; }
        .dark .es-bill-day-on { background: #86efac; }
        /* A dark day: deliberately hollow, not merely dimmer. */
        .es-bill-day-dark {
            background: transparent;
            border: 1px dashed rgba(18, 24, 20, 0.22);
        }
        .dark .es-bill-day-dark { border-color: rgba(232, 236, 233, 0.24); }
        /* The matinee strip sits under the run and is deliberately shorter,
           because it is a SEPARATE event, not a second cell on the same day. */
        .es-bill-strip-min .es-bill-day { height: 1.15rem; }

        /* Date ruler above a strip. */
        .es-bill-rule {
            display: flex;
            gap: 0.2rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.58rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #4a5450;
        }
        .dark .es-bill-rule { color: #9aa8a1; }
        .es-bill-rule span { flex: 1 1 0; min-width: 0; text-align: center; }

        /* --- The season chart: one production per row, a bar across months.
               Rows stack on every width; the bar never scrolls sideways. --- */
        .es-bill-track {
            position: relative;
            height: 1.5rem;
            border-radius: 0.3rem;
            background: rgba(18, 24, 20, 0.06);
        }
        .dark .es-bill-track { background: rgba(232, 236, 233, 0.07); }
        .es-bill-span {
            position: absolute;
            top: 0;
            bottom: 0;
            border-radius: 0.3rem;
            background: #14532d;
        }
        .dark .es-bill-span { background: #86efac; }
        .es-bill-span-soft { background: rgba(20, 83, 45, 0.45); }
        .dark .es-bill-span-soft { background: rgba(134, 239, 172, 0.45); }

        /* --- Cards --- */
        .es-bill-card {
            border: 1px solid rgba(18, 24, 20, 0.12);
            border-radius: 1rem;
            background: #ffffff;
        }
        .dark .es-bill-card {
            border-color: rgba(232, 236, 233, 0.12);
            background: rgba(232, 236, 233, 0.04);
        }
        .es-bill-band .es-bill-card {
            border-color: rgba(232, 236, 233, 0.14);
            background: rgba(232, 236, 233, 0.05);
        }

        /* --- Fixed-dark band --- */
        .es-bill-band {
            background-color: #0d1410;
            background-image: radial-gradient(120% 100% at 50% 0%, #16211a 0%, #101812 55%, #080d0a 100%);
            box-shadow: inset 0 0 90px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(232, 236, 233, 0.05);
        }
        /* Shared classes that flip with the colour mode inside a band. */
        .es-bill-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 236, 233, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 236, 233, 0.05) 1px, transparent 1px);
        }
        .es-bill-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-bill-band .es-claim:focus-within {
            border-color: rgba(134, 239, 172, 0.75);
            box-shadow: 0 0 0 4px rgba(134, 239, 172, 0.22);
        }

        /* --- Eyebrow / labels --- */
        .es-bill-tag {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: #4a5450;
        }
        .dark .es-bill-tag { color: #9aa8a1; }
        .es-bill-band .es-bill-tag { color: #86efac; }

        /* --- Plan tags --- */
        .es-bill-plan {
            display: inline-flex;
            align-items: center;
            flex: none;
            padding: 0.1rem 0.45rem;
            border-radius: 0.25rem;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            border: 1px solid rgba(20, 83, 45, 0.4);
            color: #14532d;
        }
        .dark .es-bill-plan { border-color: rgba(134, 239, 172, 0.42); color: #86efac; }
        .es-bill-band .es-bill-plan { border-color: rgba(134, 239, 172, 0.42); color: #86efac; }
        .es-bill-plan-pro { border-color: rgba(18, 24, 20, 0.35); color: #121814; }
        .dark .es-bill-plan-pro { border-color: rgba(232, 236, 233, 0.38); color: #e8ece9; }
        .es-bill-band .es-bill-plan-pro { border-color: rgba(232, 236, 233, 0.38); color: #e8ece9; }

        /* --- Section numeral --- */
        .es-bill-corner {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.85rem;
            border-radius: 0.3rem;
            border: 1px solid rgba(18, 24, 20, 0.18);
            background: #ffffff;
            color: #121814;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-variant-numeric: tabular-nums;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .dark .es-bill-corner { border-color: rgba(232, 236, 233, 0.2); background: rgba(232, 236, 233, 0.05); color: #e8ece9; }
        .es-bill-band .es-bill-corner { border-color: rgba(232, 236, 233, 0.2); background: rgba(232, 236, 233, 0.05); color: #e8ece9; }
        .es-bill-corner::before {
            content: "";
            width: 2px;
            align-self: stretch;
            border-radius: 1px;
            background: #14532d;
        }
        .dark .es-bill-corner::before { background: #86efac; }
        .es-bill-band .es-bill-corner::before { background: #86efac; }

        /* --- Links and buttons --- */
        .es-bill-link { color: #14532d; }
        .es-bill-link:hover { color: #121814; }
        .dark .es-bill-link { color: #86efac; }
        .dark .es-bill-link:hover { color: #e8ece9; }

        .es-bill-btn {
            background-color: #14532d;
            box-shadow: 0 18px 36px -14px rgba(20, 83, 45, 0.5);
        }
        .es-bill-btn:hover { background-color: #0f3d22; box-shadow: 0 22px 44px -14px rgba(20, 83, 45, 0.6); }
        .dark .es-bill-btn { background-color: #86efac; }
        .dark .es-bill-btn:hover { background-color: #a7f3c4; }

        /* --- FAQ / related hover --- */
        .es-bill-hover:hover { border-color: rgba(20, 83, 45, 0.45); }
        .dark .es-bill-hover:hover { border-color: rgba(134, 239, 172, 0.45); }
        .es-bill-hover:hover .es-bill-hover-title,
        .es-bill-hover:hover .es-bill-hover-arrow { color: #14532d; }
        .dark .es-bill-hover:hover .es-bill-hover-title,
        .dark .es-bill-hover:hover .es-bill-hover-arrow { color: #86efac; }

        /* --- Chips --- */
        .es-bill-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            border: 1px solid rgba(18, 24, 20, 0.16);
            background: rgba(255, 255, 255, 0.7);
            color: #4a5450;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        .dark .es-bill-chip {
            border-color: rgba(232, 236, 233, 0.16);
            background: rgba(232, 236, 233, 0.05);
            color: #b3bdb7;
        }

        /* --- Shared-system recolors (brand blue by default) --- */
        .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(20, 83, 45, 0.12), transparent 60%);
        }
        .dark .es-hero .es-spot {
            background: radial-gradient(560px circle at var(--mx, 50%) var(--my, 40%), rgba(134, 239, 172, 0.1), transparent 60%);
        }
        .es-dot:hover .es-dot-pip { background-color: rgba(20, 83, 45, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(134, 239, 172, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #14532d; }
        .dark .es-dot.is-active .es-dot-pip { background: #86efac; }

        /* --- Focus rings. No border-radius here: setting it changes the
               element's own shape on focus. Outlines already follow it. --- */
        #es-bill-page a:focus-visible,
        #es-bill-page summary:focus-visible,
        #es-bill-page button:focus-visible {
            outline: 2px solid #14532d;
            outline-offset: 3px;
        }
        .dark #es-bill-page a:focus-visible,
        .dark #es-bill-page summary:focus-visible,
        .dark #es-bill-page button:focus-visible {
            outline-color: #86efac;
        }
        .es-bill-band a:focus-visible,
        .es-bill-band summary:focus-visible,
        .es-bill-band button:focus-visible {
            outline-color: #86efac !important;
        }
    </style>

    @php
        // One production's run: 12 Sep - 4 Oct. Plays Tue-Sun, dark Mondays.
        // 'on' = a performance, 'dark' = a dark day. The matinee is a SEPARATE
        // event, so it gets its own strip rather than a doubled cell.
        $runDays = [];
        $matineeDays = [];
        // 12 Sep 2026 is a Saturday, so slot 0 is a Saturday and the weekday is
        // simply $i % 7: Sat 0, Sun 1, Mon 2. Sixteen days with two dark Mondays
        // is fourteen evening performances, which is the number the page states.
        foreach (range(0, 15) as $i) {
            $runDays[] = ($i % 7 === 2) ? 'dark' : 'on';        // dark Mondays
            $matineeDays[] = ($i % 7 === 0) ? 'on' : 'off';     // Saturdays
        }

        $season = [
            ['Macbeth',            'Sep 12 - Sep 27', '14 performances', 0,  13, false],
            ['The Seagull',        'Oct 17 - Nov 8',  '12 performances', 31, 19, false],
            ['A Christmas Carol',  'Dec 5 - Jan 3',   '22 performances', 74, 26, false],
            ['Studio: new work',   'Nov 14 - Nov 22', '6 performances',  56,  7, true],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for theaters?',
                'a' => 'Yes. Setting a production up as a run, marking dark days, splitting your spaces into sub-schedules, publishing your season and syncing two ways with Google, Outlook or CalDAV are all free forever, and so is selling: the free plan takes payment for up to 25 tickets a month per schedule, with free registration uncapped. The Pro plan at $5 a month lifts that ceiling and adds QR check-in, the check-in dashboard, season passes and custom checkout questions. Event Schedule charges zero platform fees on ticket sales on every plan, the free one included.',
            ],
            [
                'q' => 'How do I set up a multi-week run?',
                'a' => 'Create the production once as a recurring event, choose the days it plays, and give the recurrence an end: either a closing date or a number of performances. A run that closes after fourteen performances stops on its own rather than repeating until somebody remembers to switch it off. Add date exceptions for the nights you are dark.',
            ],
            [
                'q' => 'What about matinees?',
                'a' => 'A matinee is its own event. A recurring event has one start time, so it produces one performance on each day it plays, and a Saturday matinee needs a second event alongside the evening run. It is a little more setup and it keeps the two curtain times, and their tickets, properly separate.',
            ],
            [
                'q' => 'Can I sell a pass for the whole run?',
                'a' => 'Yes, on the Pro plan. A season pass is tied to the production\'s recurrence and is valid for every performance of the run, once each, and you can set how many seats it admits per performance. It is sold alongside single tickets rather than instead of them.',
            ],
            [
                'q' => 'Can I price different parts of the house differently?',
                'a' => 'Yes, with named ticket types. Create as many as the production needs, each with its own price, quantity and sales window, plus add-ons and a rate that applies when somebody buys several at once. To be clear about what this is: it is priced ticket types, not a seating chart. There is no seat map and buyers are not choosing a specific seat.',
            ],
            [
                'q' => 'Can I run more than one space?',
                'a' => 'Yes, on every plan. Sub-schedules keep the mainstage, the studio and the family programme apart on one link, so somebody looking for the studio season is not reading through the whole year to find it.',
            ],
        ];

        $dotSections = [
            ['top', 'The run'],
            ['why', 'Not a date'],
            ['run', 'Setting the run'],
            ['season', 'The season'],
            ['pass', 'The season pass'],
            ['tiers', 'Ticket types'],
            ['house', 'The house'],
            ['rest', 'Everything else'],
            ['who', 'Perfect for'],
            ['faq', 'Questions'],
            ['claim', 'Opening night'],
        ];
    @endphp

    <div id="es-bill-page" class="es-bill-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: one production's run                                -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden py-16">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(20, 83, 45, 0.18), rgba(20, 83, 45, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 70% 40%, rgba(134, 239, 172, 0.12), rgba(134, 239, 172, 0) 65%);"></div>
            <div class="es-spot absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-[1.05fr_1fr]">
                <div>
                    <div class="es-fade-up es-d-1 glass mb-8 inline-flex items-center gap-3 rounded-full px-5 py-2.5">
                        <svg aria-hidden="true" class="h-5 w-5 text-[#14532d] dark:text-[#86efac]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="es-bill-muted text-sm font-medium tracking-wide">For theaters and producing houses</span>
                    </div>

                    <h1 class="es-balance es-bill-ink mb-8 text-[2.4rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">A production is not a date.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">It is <span class="es-bill-accent">fourteen</span> of them.</span></span>
                    </h1>

                    <p class="es-fade-up es-d-2 es-bill-muted mb-10 max-w-xl text-lg sm:text-xl">
                        Set the run up once - Tuesday to Sunday, dark Mondays, closing after fourteen performances - and sell every one of them from a single link, with zero platform fees.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col items-start gap-4 sm:flex-row">
                        <a href="#run" class="glass group inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-lg font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                            See how a run is set up
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-bill-btn group inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] dark:text-[#0b110e]">
                            Create your theater's calendar
                            <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- The run. Two strips, because the matinee is a separate event. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-bill-card p-6 sm:p-7">
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="es-bill-ink text-lg font-bold">Macbeth</h2>
                            <span class="es-bill-muted font-mono text-xs">Sep 12 &ndash; Sep 27</span>
                        </div>
                        <p class="es-bill-muted mb-5 text-sm">Tuesday to Sunday &middot; dark Mondays &middot; 14 evening performances</p>

                        <div aria-hidden="true">
                            <div class="es-bill-rule mb-1">
                                @foreach (range(0, 15) as $i)
                                    <span>{{ $i % 7 === 0 ? 'S' : '' }}</span>
                                @endforeach
                            </div>
                            <div class="es-bill-strip">
                                @foreach ($runDays as $d)
                                    <div class="es-bill-day @if ($d === 'on') es-bill-day-on @else es-bill-day-dark @endif"></div>
                                @endforeach
                            </div>
                            <p class="es-bill-muted mt-1.5 mb-3 text-[0.65rem]">Evenings &middot; dashed cells are dark days</p>

                            <div class="es-bill-strip es-bill-strip-min">
                                @foreach ($matineeDays as $d)
                                    <div class="es-bill-day @if ($d === 'on') es-bill-day-on @endif"></div>
                                @endforeach
                            </div>
                            <p class="es-bill-muted mt-1.5 text-[0.65rem]">Saturday matinees &middot; a separate event, on its own run</p>
                        </div>

                        <p class="es-bill-muted mt-5 border-t border-[rgba(18,24,20,0.1)] pt-4 text-xs dark:border-[rgba(232,236,233,0.12)]">
                            One recurring event, ending after its last performance. The matinee is a second event, so the two curtain times keep their own tickets.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Genre marquee -->
            <div class="es-fade-up es-d-4 mx-auto mt-14 max-w-3xl">
                <div class="es-marquee-mask">
                    <div class="es-marquee" data-marquee="1">
                        <div class="es-marquee-track">
                            @for ($chipCopy = 0; $chipCopy < 2; $chipCopy++)
                                @foreach (['Drama', 'Musical', 'Comedy', 'Shakespeare', 'New Work', 'Panto', 'Opera', 'Studio', 'Family', 'Rep'] as $chip)
                                    <span @if ($chipCopy === 1) aria-hidden="true" @endif class="es-bill-chip">{{ $chip }}</span>
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. A production is not a date (fixed-dark band)              -->
    <!-- ============================================================ -->
    <section id="why" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-bill-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-bill-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The unit</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Most calendars think a show is <span class="es-bill-lit">one night.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    <div class="es-bill-card p-6" data-reveal="panel">
                        <p class="es-bill-tag mb-3">The run</p>
                        <h3 class="mb-2 text-lg font-bold text-[#e8ece9]">
                            <span data-count-to="14">14</span> performances
                        </h3>
                        <p class="text-sm text-[#9aa8a1]">Same set, same cast, sixteen days. Entering it as fourteen separate events is fourteen chances to mistype a time.</p>
                    </div>
                    <div class="es-bill-card p-6" data-reveal="panel">
                        <p class="es-bill-tag mb-3">The setup</p>
                        <h3 class="mb-2 text-lg font-bold text-[#e8ece9]">
                            <span data-count-to="1">1</span> event
                        </h3>
                        <p class="text-sm text-[#9aa8a1]">A day-of-week pattern, exceptions for the dark nights, and an end. Change the curtain time once and every performance follows.</p>
                    </div>
                    <div class="es-bill-card p-6" data-reveal="panel">
                        <p class="es-bill-tag mb-3">The close</p>
                        <h3 class="mb-2 text-lg font-bold text-[#e8ece9]">It stops itself</h3>
                        <p class="text-sm text-[#9aa8a1]">A run ends on a closing date or after a set number of performances, so it is never still selling tickets in February.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    The run is the unit. Everything else on this page hangs off it.
                    <a href="#run" class="es-bill-lit inline-flex items-center gap-1 font-semibold transition-all hover:gap-2">
                        Set one up
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                    </a>
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Setting the run                                           -->
    <!-- ============================================================ -->
    <section id="run" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                <p class="es-bill-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Setting the run</p>
                <h2 class="es-balance es-bill-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Days, dark nights, and a <span class="es-bill-accent">closing night.</span>
                </h2>
                <p class="es-bill-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Three settings turn one event into a run, and all three are on the free plan.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-bill-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-bill-ink text-lg font-bold">The days it plays</h3>
                        <span class="es-bill-plan">Free</span>
                    </div>
                    <p class="es-bill-muted text-sm">Pick the days of the week and the curtain time. Tuesday to Sunday is six ticks a week without entering six events.</p>
                </div>
                <div class="es-bill-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-bill-ink text-lg font-bold">The nights you are dark</h3>
                        <span class="es-bill-plan">Free</span>
                    </div>
                    <p class="es-bill-muted text-sm">Date exceptions take individual dates out, so a Monday off or a press night that moved does not need the run rebuilding.</p>
                </div>
                <div class="es-bill-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-bill-ink text-lg font-bold">The end</h3>
                        <span class="es-bill-plan">Free</span>
                    </div>
                    <p class="es-bill-muted text-sm">A closing date, or a number of performances. This is the setting that makes a run a run instead of a weekly night that never stops.</p>
                </div>
            </div>

            <p class="es-bill-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Matinees are their own event on their own run, because one recurring event has one curtain time. Two events, two sets of tickets, no ambiguity about which performance somebody bought.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The season: several runs, stacked                         -->
    <!-- ============================================================ -->
    <section id="season" class="scroll-mt-24 border-y border-[rgba(18,24,20,0.08)] py-20 dark:border-[rgba(232,236,233,0.08)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-bill-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The season</p>
                <h2 class="es-balance es-bill-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    A season is just runs, <span class="es-bill-accent">side by side.</span>
                </h2>
                <p class="es-bill-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Once each production is a run, the year draws itself, and the studio season sits in its own sub-schedule.
                </p>
            </div>

            <div class="es-bill-card p-6 sm:p-8" data-reveal="panel">
                <table class="w-full border-collapse text-left">
                    <caption class="sr-only">Season 2026 to 2027: each production with its dates and number of performances</caption>
                    <thead>
                        <tr class="es-bill-tag">
                            <th scope="col" class="pb-3 font-bold">Production</th>
                            <th scope="col" class="pb-3 font-bold">Dates</th>
                            <th scope="col" class="hidden pb-3 font-bold sm:table-cell">Run</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($season as [$sName, $sDates, $sCount, $sLeft, $sWidth, $sStudio])
                            <tr class="border-t border-[rgba(18,24,20,0.08)] dark:border-[rgba(232,236,233,0.08)]">
                                <th scope="row" class="es-bill-ink py-3 pe-3 align-middle text-sm font-bold">
                                    {{ $sName }}
                                    @if ($sStudio)<span class="es-bill-muted block text-[0.65rem] font-normal">Studio sub-schedule</span>@endif
                                </th>
                                <td class="es-bill-muted py-3 pe-3 align-middle font-mono text-xs">{{ $sDates }}</td>
                                <td class="es-bill-muted hidden py-3 align-middle font-mono text-xs sm:table-cell">{{ $sCount }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="pb-3">
                                    <div class="es-bill-track" aria-hidden="true">
                                        <div class="es-bill-span @if ($sStudio) es-bill-span-soft @endif" style="left: {{ $sLeft }}%; width: {{ $sWidth }}%;"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="es-bill-muted mt-4 text-xs">Each bar is one production's run. Sub-schedules keep the studio season on its own strand of the same link.</p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. The season pass                                           -->
    <!-- ============================================================ -->
    <section id="pass" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-bill-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The season pass</p>
                    <h2 class="es-balance es-bill-ink mb-5 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                        One pass, <span class="es-bill-accent">the whole run.</span>
                    </h2>
                    <p class="es-bill-muted mb-6 text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Because the run is one recurring event, a pass can be tied to it: valid for every performance, once each. Set how many seats it admits per performance, and sell it alongside single tickets rather than instead of them.
                    </p>
                    <ul class="es-bill-muted space-y-3" data-reveal-group="70">
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#14532d] dark:text-[#86efac]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Usage is tracked per performance, so you can see which nights the pass holders actually came to.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#14532d] dark:text-[#86efac]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>A cancellation deadline and a late-cancel policy can be set on the pass itself.</span>
                        </li>
                        <li class="flex gap-3" data-reveal>
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 flex-none text-[#14532d] dark:text-[#86efac]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            <span>Passes are a Pro feature. Publishing the run and its dates is not.</span>
                        </li>
                    </ul>
                </div>

                <div data-reveal="panel">
                    <div class="es-bill-card p-6 sm:p-7">
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            <h3 class="es-bill-ink text-lg font-bold">Season pass</h3>
                            <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                        </div>
                        <div aria-hidden="true">
                            <div class="es-bill-strip mb-2">
                                @foreach ($runDays as $d)
                                    <div class="es-bill-day @if ($d === 'on') es-bill-day-on @else es-bill-day-dark @endif"></div>
                                @endforeach
                            </div>
                            <p class="es-bill-muted text-[0.65rem]">The pass covers every one of these, once each.</p>
                        </div>
                        <div class="mt-5 space-y-2 border-t border-[rgba(18,24,20,0.1)] pt-4 dark:border-[rgba(232,236,233,0.12)]">
                            @foreach ([['Season pass', 'every performance', '$120'], ['Single ticket', 'one performance', '$22'], ['Preview', 'first three nights', '$14']] as [$tName, $tScope, $tPrice])
                                <div class="flex items-baseline gap-3 text-sm">
                                    <span class="es-bill-ink min-w-0 flex-1 truncate font-semibold">{{ $tName }}</span>
                                    <span class="es-bill-muted hidden truncate text-xs sm:inline">{{ $tScope }}</span>
                                    <span class="es-bill-ink font-mono">{{ $tPrice }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Ticket types (explicitly not a seat map)                  -->
    <!-- ============================================================ -->
    <section id="tiers" class="scroll-mt-24 border-t border-[rgba(18,24,20,0.08)] py-20 dark:border-[rgba(232,236,233,0.08)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-bill-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Ticket types</p>
                <h2 class="es-balance es-bill-ink text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.1s;">
                    Name your prices. <span class="es-bill-accent">Not your seats.</span>
                </h2>
                <p class="es-bill-muted mt-5 text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Create as many ticket types as the production needs, each with its own price, quantity and sales window. Being straight with you: this is priced ticket types, not a seating chart. There is no seat map, and buyers are not picking a specific seat.
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                <div class="es-bill-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-bill-ink text-lg font-bold">Tiers that close on time</h3>
                        <span class="es-bill-plan">Free</span>
                    </div>
                    <p class="es-bill-muted text-sm">Give each type a sales window so preview pricing stops when previews do, and concessions can open later without you editing anything.</p>
                </div>
                <div class="es-bill-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-bill-ink text-lg font-bold">Ask what you need</h3>
                        <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                    </div>
                    <p class="es-bill-muted text-sm">Custom questions on the ticket collect what the night actually requires - access needs, a dinner choice, a school's contact - at the point of purchase.</p>
                </div>
                <div class="es-bill-card p-7" data-reveal="panel">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <h3 class="es-bill-ink text-lg font-bold">Groups and add-ons</h3>
                        <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                    </div>
                    <p class="es-bill-muted text-sm">A rate that applies once somebody buys several at once, plus add-ons that attach to a booking. Discount codes for the people you want to bring back.</p>
                </div>
            </div>

            <p class="es-bill-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Connect Stripe and sell straight from the run. Event Schedule charges zero platform fees, so past Stripe's own processing the money is yours.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. The house (fixed-dark band)                               -->
    <!-- ============================================================ -->
    <section id="house" class="relative scroll-mt-24 px-2 py-14 sm:px-4 lg:py-20">
        <div class="es-bill-band noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 30%, rgba(134, 239, 172, 0.12), rgba(134, 239, 172, 0) 60%); opacity: 0.5;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-12 max-w-3xl text-center">
                    <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-bill-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The house</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Every ticket carries a code. <span class="es-bill-lit">Scan them in.</span>
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="100">
                    <div class="es-bill-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-[#e8ece9]">On the door</h3>
                            <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                        </div>
                        <p class="text-sm text-[#9aa8a1]">Scan on the way in from any phone. No extra hardware, and duplicates are caught rather than argued about.</p>
                    </div>
                    <div class="es-bill-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-[#e8ece9]">A live count</h3>
                            <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                        </div>
                        <p class="text-sm text-[#9aa8a1]">The check-in dashboard shows who is actually in, with a per-ticket-type breakdown, so you know before curtain rather than after.</p>
                    </div>
                    <div class="es-bill-card p-6" data-reveal="panel">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-[#e8ece9]">One each</h3>
                            <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                        </div>
                        <p class="text-sm text-[#9aa8a1]">Per-attendee tickets give everyone in a party their own confirmation and their own code, instead of one person holding six.</p>
                    </div>
                </div>

                <p class="mt-10 text-center text-gray-300" data-reveal>
                    For a free performance, registration with a capacity limit works on every plan.
                </p>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Everything else: bento                                    -->
    <!-- ============================================================ -->
    <section id="rest" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-bill-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Everything else</p>
                <h2 class="es-balance es-bill-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Between opening and closing.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="110">
                <!-- 1 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-bill-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-bill-ink text-xl font-bold">Tell the people who already come</h3>
                                <span class="es-bill-plan">Free</span>
                            </div>
                            <p class="es-bill-muted mb-4">Audiences follow your schedule and you email them when a season is announced or a run goes on sale. Open and click rates afterwards tell you whether the announcement landed.</p>
                            <p class="es-bill-muted text-sm">The numbers worth knowing first: 10 emails a month on Free, 100 on Pro and 1,000 on Enterprise, counted per recipient rather than per send.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-bill-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-bill-ink text-xl font-bold">When a night sells out</h3>
                                <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                            </div>
                            <p class="es-bill-muted">Turn on the waitlist and people join once that performance is gone. If a return comes back, they are notified automatically.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-bill-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-bill-ink text-xl font-bold">On the site you already have</h3>
                                <span class="es-bill-plan">Free</span>
                            </div>
                            <p class="es-bill-muted mb-4">Embed the calendar on your own site so the season lives where people look you up, and sync two ways with Google, Outlook and CalDAV.</p>
                            <p class="es-bill-muted text-sm">Built-in analytics show page views, the devices people are on, and where the traffic came from. That is what they measure, and nothing more.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-bill-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-bill-ink text-xl font-bold">Announce when you are ready</h3>
                                <span class="es-bill-plan">Free</span>
                            </div>
                            <p class="es-bill-muted">A production you have not announced sits on the calendar as a draft, visible to you and never published until you say so.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5 -->
                <div class="es-bento group relative lg:col-span-2" data-reveal="panel" data-tilt="3.5">
                    <div class="es-tilt-inner es-bill-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-bill-ink text-xl font-bold">The announcement image</h3>
                                <span class="es-bill-plan es-bill-plan-pro">Pro</span>
                            </div>
                            <p class="es-bill-muted mb-4">Generate a graphic from a production in a story, square, portrait or landscape crop. It is built from the event, so the title and the dates are already right.</p>
                            <p class="es-bill-muted text-sm">
                                Streaming a performance too? Mark it as an online event and paste the link to wherever you are streaming.
                                <a href="{{ marketing_url('/features/online-events') }}" class="es-bill-link font-medium hover:underline">How online events work</a>
                            </p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6 -->
                <div class="es-bento group relative" data-reveal="panel" data-tilt="5">
                    <div class="es-tilt-inner es-bill-card relative flex h-full flex-col overflow-hidden p-7">
                        <div class="relative z-10">
                            <div class="mb-4 flex flex-wrap items-center gap-2">
                                <h3 class="es-bill-ink text-xl font-bold">After the run</h3>
                                <span class="es-bill-plan">Free</span>
                            </div>
                            <p class="es-bill-muted">Audiences add photos, video and comments to a production, all held in an approval queue before anything appears. Free covers 25 photos per schedule.</p>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 9. Perfect for                                               -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(18,24,20,0.08)] py-20 dark:border-[rgba(232,236,233,0.08)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>09</span></div>
                <h2 class="es-balance es-bill-ink mb-4 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.05s;">
                    Built for every kind of <span class="es-bill-accent">house</span>
                </h2>
                <p class="es-bill-muted text-lg sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">
                    Eighty seats or eight hundred, a run is a run.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Community Theaters"
                    description="Local productions, volunteer casts and beloved classics. Publish the run once and reach your audience directly."
                    icon-color="rose"
                    blog-slug="for-community-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Regional Theaters"
                    description="Professional runs across a full season. Sell single performances and a pass that covers a whole run."
                    icon-color="red"
                    blog-slug="for-regional-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Black Box Theaters"
                    description="Intimate experimental work with short runs. Cap each performance and keep the studio on its own sub-schedule."
                    icon-color="slate"
                    blog-slug="for-black-box-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Dinner Theaters"
                    description="An evening that is more than the show. Ask for the course choice on the ticket itself with custom questions at checkout."
                    icon-color="amber"
                    blog-slug="for-dinner-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Children's Theaters"
                    description="Family productions and weekday matinees. Matinees run as their own event, so the school shows keep their own tickets."
                    icon-color="rose"
                    blog-slug="for-childrens-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Outdoor Amphitheaters"
                    description="Shakespeare in the park and summer stock. Take a washed-out night out of the run with a date exception."
                    icon-color="emerald"
                    blog-slug="for-outdoor-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 10. Three steps                                              -->
    <!-- ============================================================ -->
    <section class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance es-bill-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal>
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3" data-reveal-group="120">
                @foreach ([['01', 'Set the run', 'Create the production as a recurring event, pick the days it plays, and give it an end: a closing date or a number of performances.'], ['02', 'Mark the dark days', 'Add date exceptions for the nights you are dark, and add the matinee as its own event on the days it plays.'], ['03', 'Sell the run', 'Named ticket types for each price, and a season pass valid for every performance of the run, once each.']] as [$stepNum, $stepTitle, $stepBody])
                    <div class="es-bill-card p-7" data-reveal="panel">
                        <div class="es-bill-accent mb-3 font-mono text-2xl font-black">{{ $stepNum }}</div>
                        <h3 class="es-bill-ink mb-2 text-lg font-bold">{{ $stepTitle }}</h3>
                        <p class="es-bill-muted text-sm leading-relaxed">{{ $stepBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 11. Key features                                             -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(18,24,20,0.08)] py-20 dark:border-[rgba(232,236,233,0.08)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-bill-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Set a run once, with dark days and a closing performance" :url="marketing_url('/features/recurring-events')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Named ticket types, QR check-in, and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Keep mainstage, studio and family programming apart" :url="marketing_url('/features/sub-schedules')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Email the people who follow your theater, with open rates" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-bill-link inline-flex items-center font-medium hover:underline">
                    See all features
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    @include('marketing.partials.pricing-nudge')

    <!-- ============================================================ -->
    <!-- 12. Related pages                                            -->
    <!-- ============================================================ -->
    <section class="border-t border-[rgba(18,24,20,0.08)] py-16 dark:border-[rgba(232,236,233,0.08)]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-bill-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4" data-reveal-group="70">
                @foreach ([['/for-theater-performers', 'Theater Performers'], ['/for-venues', 'Venues'], ['/for-dance-groups', 'Dance Groups'], ['/for-community-centers', 'Community Centers']] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" class="es-bill-hover es-bill-card group flex flex-col p-5 transition-all duration-200 hover:shadow-md" data-reveal>
                        <span class="es-bill-hover-title es-bill-ink mb-3 text-sm font-semibold transition-colors">For {{ $relName }}</span>
                        <span class="es-bill-hover-arrow es-bill-muted mt-auto inline-flex items-center gap-1 text-xs font-medium transition-colors">
                            Read more
                            <svg aria-hidden="true" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-8 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-bill-link inline-flex items-center font-medium hover:underline">
                    See all use cases
                    <svg aria-hidden="true" class="ml-1 w-4 h-4 rtl:ml-0 rtl:mr-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 13. FAQ                                                      -->
    <!-- ============================================================ -->
    <x-seo.faq-schema :items="$faqs" />

    <section id="faq" class="scroll-mt-24 py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <div class="es-bill-corner mb-6" data-reveal aria-hidden="true"><span>10</span></div>
                <h2 class="es-balance es-bill-ink mb-4 text-3xl font-black tracking-tight md:text-4xl" data-reveal style="--reveal-delay: 0.05s;">
                    Frequently asked questions
                </h2>
                <p class="es-bill-muted text-lg" data-reveal style="--reveal-delay: 0.1s;">
                    What producers ask before they move a season across.
                </p>
            </div>

            <div class="space-y-3" data-reveal-group="80">
                @foreach ($faqs as $faqIndex => $faq)
                    <details name="faq" class="es-bill-hover es-bill-card group p-6 transition-all duration-200" data-reveal>
                        <summary class="es-bill-ink flex cursor-pointer items-start gap-3 font-semibold">
                            <span class="es-bill-accent flex-none font-mono text-sm font-bold" aria-hidden="true">{{ str_pad($faqIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="es-bill-hover-title flex-1 transition-colors">{{ $faq['q'] }}</span>
                            <svg aria-hidden="true" class="es-bill-muted mt-0.5 h-5 w-5 flex-none transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </summary>
                        <p class="faq-answer es-bill-muted mt-4 leading-relaxed ps-9">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 14. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 px-2 py-16 sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-bill-band noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>
                <div class="relative z-10">
                    <p class="es-bill-tag mb-4">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Set the run once. <span class="es-bill-lit">Sell all fourteen.</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-400">
                        Publishing your season and its dates is free forever, and so is selling your first twenty-five tickets a month. Five dollars a month lifts the ceiling and adds season passes and check-in, and nothing is taken from the door.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-theater" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-bill-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl px-8 py-4 text-lg font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] dark:text-[#0b110e]">
                            <span class="relative z-10 flex items-center gap-2">
                                Create your calendar
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

    <!-- Desktop dot nav -->
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#121814] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <x-marketing.related-pages />

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
