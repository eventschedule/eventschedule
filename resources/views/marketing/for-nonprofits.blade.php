<x-marketing-layout>
    <x-slot name="title">Nonprofit Event Management | Zero Platform Fees</x-slot>
    <x-slot name="description">Nonprofit event management for galas, volunteer days and campaigns: capped free sign-ups, ticket sales with zero platform fees and a page supporters follow.</x-slot>
    <x-slot name="breadcrumbTitle">For Nonprofits</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Nonprofits",
        "description": "Event management for charities, volunteer programs and campaign groups: free registration with a capacity for volunteer days, ticketed galas with zero platform fees, and one page supporters subscribe to.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Nonprofits, Charities, Volunteer Programs & Advocacy Groups"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Nonprofits",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Nonprofit Event Management Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "Free registration with a capacity, counted separately for each date and unlimited on every plan",
            "Recurring volunteer shifts with date exceptions for the weeks they do not run",
            "Ticketed galas with several ticket types, on the Pro plan",
            "Zero platform fees on ticket sales, paid into the organization's own Stripe or PayPal account, or by Invoice Ninja, a payment link or cash, on the Pro plan",
            "Refunds from the Sales page, sent back through Stripe or PayPal in full or in part, on the Pro plan",
            "Promo codes and add-ons for a gala, on the Pro plan",
            "Sponsor and partner logos with tiers on the schedule page, on the Pro plan",
            "A short digest of new events to confirmed email subscribers, at most one every 72 hours",
            "Newsletters to supporters within a monthly allowance counted per recipient",
            "An interest list for an event announced before tickets go on sale",
            "Sub-schedules for programs, chapters and campaigns, each with its own link",
            "Embeddable calendar for the organization's own website",
            "Online events with the link people join on",
            "Open source, and selfhostable on your own server"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "nonprofit event management, charity event ticketing, volunteer sign-up, fundraising gala tickets, nonprofit event calendar",
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
        "name": "How to run a nonprofit's events with Event Schedule",
        "description": "One schedule for the organization, free sign-ups for the events that cost nothing, and tickets for the one night that raises money.",
        "step": [
            {
                "@type": "HowToStep",
                "position": 1,
                "name": "Start the schedule",
                "text": "Create a schedule for the organization and split programs, chapters or campaigns into sub-schedules, each with its own link."
            },
            {
                "@type": "HowToStep",
                "position": 2,
                "name": "Open the free events",
                "text": "Add volunteer days, info sessions and clean-ups with free registration and a capacity, counted separately for every date."
            },
            {
                "@type": "HowToStep",
                "position": 3,
                "name": "Sell the gala",
                "text": "On Pro, give the fundraiser its ticket types and prices, paid into your own Stripe or PayPal account with zero platform fees."
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
           For-nonprofits "Where It Goes" styles.

           CONCEPT: a nonprofit's calendar is two calendars. Most of it
           costs nothing to attend - the volunteer morning, the info
           session, the clean-up - and the one question is who is coming.
           Then there is the night that raises money, and there the one
           question is how much of the ticket reaches the work. The hero
           answers the second question with a receipt: the ticket, the
           payment provider's own fee, a platform line that reads 0, and
           the rest.

           CLAIM DISCIPLINE. Event Schedule sells tickets and takes
           registrations. It does not process standalone donations, issue
           tax receipts or keep a donor CRM, and the page says so in its
           own section rather than letting "fundraising" imply it. No
           Stripe or PayPal percentage is quoted: the provider sets it.
           Prices on tickets are Pro (Event::canSellPaidTickets), so the
           gateways and refunds are presented as Pro too.

           COLOUR: a deep harbour teal, #0f5e5a (hue 177). The nearest
           siblings are /for-djs (193, sat 82%) and /for-art-galleries
           (205, sat 23%). Dark accent #7fd3cc. NEVER text-gray-500 on
           these grounds - use .es-cause-muted.
           ============================================================== */

        /* --- Ground and ink --- */
        .es-cause-page { background-color: #f3f6f5; color: #13201f; }
        .dark .es-cause-page { background-color: #0b1111; color: #e8efee; }
        .es-cause-ink { color: #13201f; }
        .dark .es-cause-ink { color: #e8efee; }
        .es-cause-muted { color: #475654; }
        .dark .es-cause-muted { color: #9fb0ae; }
        .es-cause-accent { color: #0f5e5a; }
        .dark .es-cause-accent { color: #7fd3cc; }
        .es-cause-lit { color: #7fd3cc; }

        .es-cause-grad {
            background-image: linear-gradient(100deg, #0f5e5a, #17736d);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-cause-grad,
        .es-cause-band .es-cause-grad {
            background-image: linear-gradient(100deg, #a6e4de, #7fd3cc);
        }

        /* --- Surfaces --- */
        .es-cause-card {
            background-color: #fbfcfc;
            border: 1px solid rgba(19, 32, 31, 0.12);
            border-radius: 0.75rem;
        }
        .dark .es-cause-card {
            background-color: #141b1b;
            border-color: rgba(232, 239, 238, 0.12);
        }
        .es-cause-sub {
            background-color: rgba(15, 94, 90, 0.06);
            border-radius: 0.5rem;
        }
        .dark .es-cause-sub { background-color: rgba(127, 211, 204, 0.07); }
        .es-cause-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-cause-hover:hover { border-color: rgba(15, 94, 90, 0.45); box-shadow: 0 10px 28px -18px rgba(19, 32, 31, 0.5); }
        .dark .es-cause-hover:hover { border-color: rgba(127, 211, 204, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The receipt -------------------------------------------
           A fixed object: the same paper in both colour modes, so it
           has no .dark variant and nothing inside it carries one. */
        .es-cause-receipt {
            background-color: #fffdf8;
            color: #1b2322;
            border-radius: 0.6rem;
            box-shadow: 0 30px 60px -30px rgba(11, 17, 17, 0.55), 0 1px 0 rgba(11, 17, 17, 0.08);
        }
        .es-cause-receipt-muted { color: #4d5a58; }
        .es-cause-receipt-rule { border-top: 1px dashed rgba(27, 35, 34, 0.28); }
        .es-cause-zero { color: #0f5e5a; }
        .es-cause-bar { height: 0.7rem; border-radius: 999px; background-color: rgba(27, 35, 34, 0.08); overflow: hidden; display: flex; }
        .es-cause-bar-fee { background-color: #9aa8a6; }
        .es-cause-bar-cause { background-color: #0f5e5a; }
        .es-cause-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }
        .es-cause-cap {
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        /* --- Eyebrow, chips, plan tags --- */
        .es-cause-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #0f5e5a;
        }
        .dark .es-cause-tag { color: #7fd3cc; }
        .es-cause-band .es-cause-tag { color: #7fd3cc; }

        .es-cause-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 999px;
            border: 1.5px solid #0f5e5a;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f5e5a;
        }
        .dark .es-cause-chip { border-color: #7fd3cc; color: #7fd3cc; }
        .es-cause-band .es-cause-chip { border-color: #7fd3cc; color: #7fd3cc; }

        /* Plan tiers ONLY. */
        .es-cause-plan {
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
        .es-cause-plan-free { border-color: rgba(19, 32, 31, 0.22); color: #475654; }
        .dark .es-cause-plan-free { border-color: rgba(232, 239, 238, 0.26); color: #9fb0ae; }
        .es-cause-plan-pro { border-color: rgba(15, 94, 90, 0.5); color: #0f5e5a; background: rgba(15, 94, 90, 0.08); }
        .dark .es-cause-plan-pro { border-color: rgba(127, 211, 204, 0.42); color: #7fd3cc; background: rgba(127, 211, 204, 0.1); }
        .es-cause-plan-ent { border-color: rgba(19, 32, 31, 0.4); color: #13201f; background: rgba(19, 32, 31, 0.06); }
        .dark .es-cause-plan-ent { border-color: rgba(232, 239, 238, 0.4); color: #e8efee; background: rgba(232, 239, 238, 0.08); }

        /* --- Buttons --- */
        .es-cause-btn {
            background-color: #0f5e5a;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-cause-btn:hover { background-color: #0a4845; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(15, 94, 90, 0.9); }
        .es-cause-ghost {
            border: 1px solid rgba(19, 32, 31, 0.22);
            color: #13201f;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-cause-ghost:hover { border-color: rgba(15, 94, 90, 0.5); background-color: rgba(15, 94, 90, 0.06); }
        .dark .es-cause-ghost { border-color: rgba(232, 239, 238, 0.24); color: #e8efee; }
        .dark .es-cause-ghost:hover { border-color: rgba(127, 211, 204, 0.45); background-color: rgba(127, 211, 204, 0.08); }

        /* --- The dark band: identical in both colour modes. --- */
        .es-cause-band {
            background-color: #0d1515;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(15, 94, 90, 0.55), rgba(15, 94, 90, 0) 70%),
                linear-gradient(180deg, #121c1c, #0d1515);
        }
        .es-cause-band .grid-overlay {
            background-image:
                linear-gradient(rgba(232, 239, 238, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(232, 239, 238, 0.05) 1px, transparent 1px);
        }
        .es-cause-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-cause-band .es-claim:focus-within {
            border-color: rgba(127, 211, 204, 0.75);
            box-shadow: 0 0 0 4px rgba(127, 211, 204, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(15, 94, 90, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(127, 211, 204, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #0f5e5a; }
        .dark .es-dot.is-active .es-dot-pip { background: #7fd3cc; }

        #es-cause-page a:focus-visible,
        #es-cause-page summary:focus-visible,
        #es-cause-page button:focus-visible,
        #es-cause-page input:focus-visible {
            outline: 2px solid #0f5e5a;
            outline-offset: 2px;
        }
        .dark #es-cause-page a:focus-visible,
        .dark #es-cause-page summary:focus-visible,
        .dark #es-cause-page button:focus-visible,
        .dark #es-cause-page input:focus-visible {
            outline-color: #7fd3cc;
        }
        .es-cause-band a:focus-visible,
        .es-cause-band summary:focus-visible,
        .es-cause-band button:focus-visible,
        .es-cause-band input:focus-visible {
            outline-color: #7fd3cc !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-cause-btn:hover { transform: none; }
        }
    </style>

    @php
        // The hero receipt. Illustrative amounts with no currency symbol: this is a
        // ticket's money, in whatever currency the event is priced in. The provider's
        // share is deliberately not a number - Stripe and PayPal set their own rates.
        $ticketTypes = [
            ['Supporter', '75.00', '140 sold'],
            ['Table of eight', '560.00', '12 sold'],
            ['Student', '30.00', '38 sold'],
        ];

        $faqs = [
            [
                'q' => 'Is Event Schedule free for a nonprofit?',
                'a' => 'The parts most nonprofits use every week are free forever: unlimited events, free registration with a capacity for volunteer days and info sessions, recurring shifts, sub-schedules for programs or chapters, an embeddable calendar, two-way calendar sync and a live calendar feed supporters subscribe to. Putting a price on a ticket, for a gala or a paid workshop, needs Pro at '.plan_price($proMonthly).' a month. There is no separate nonprofit discount, but there is also no platform fee on ticket sales on any plan, so the price of the plan is the whole cost to you.',
            ],
            [
                'q' => 'Can we take donations through Event Schedule?',
                'a' => 'No, and it is better to know that now. Event Schedule sells tickets and takes registrations; it does not process standalone donations, issue tax receipts or keep a donor database. What it does well is the event side of fundraising: the gala with a supporter ticket and a table of eight, the sponsored walk with capped places, and the page people keep coming back to for the next date. If your donations run through another tool, link to it from the event description.',
            ],
            [
                'q' => 'Where does the ticket money go?',
                'a' => 'Straight to the organization. On Pro, a ticket is paid into your own Stripe or PayPal account, billed through your own Invoice Ninja company, taken by a payment link you control, or collected in cash at the door, chosen per event. Your payment provider charges its own processing fee, and Event Schedule adds nothing on top. If a guest cannot come, refund them from the Sales page: a Stripe or PayPal sale goes back through the provider in full or in part, and any other method is marked as refunded so your records stay straight.',
            ],
            [
                'q' => 'How do volunteer sign-ups work?',
                'a' => 'Make the shift an event with free registration and give it a capacity. Volunteers register instead of paying, the count is kept for each date on its own, and registration stops when the places are gone. A shift that runs every Saturday is one recurring event, and a date exception takes out the Saturday you are closed. Free registration is unlimited on every plan, and the QR code on each registration scans at the door on every plan too.',
            ],
            [
                'q' => 'How many supporters can we email?',
                'a' => 'Two different things reach supporters. Anyone who leaves an email address on your page and confirms it gets a short digest when you publish new events, at most one every 72 hours, and that does not touch your newsletter allowance. A newsletter you write yourself counts each recipient against a monthly allowance: 10 on the free plan, 100 on Pro and 1,000 on Enterprise. So one appeal to 300 supporters is more than a month of Pro. Connect the schedule\'s own email settings, or selfhost, and that allowance no longer applies.',
            ],
            [
                'q' => 'Can more than one person on staff manage events?',
                'a' => 'The free and Pro plans have one team member per schedule. Adding colleagues is Enterprise, at '.plan_price($entMonthly).' a month: an admin runs the schedule day to day and sees ticket sales, and a viewer is read-only but can scan tickets at the door, which suits volunteers working the entrance.',
            ],
            [
                'q' => 'Can we show our sponsors?',
                'a' => 'Yes, on Pro. Sponsor and partner logos sit on the schedule page with tiers, so the headline sponsor of the gala is not the same size as the local bakery that gave the raffle prize. Sponsors also get what every visitor gets: an event page they can share with their own staff.',
            ],
            [
                'q' => 'Who owns our supporters\' data?',
                'a' => 'You do. The people who register, subscribe or buy a ticket are listed on your schedule, and you can export a backup of the schedule at any time. Event Schedule is open source, so an organization that has to keep personal data on its own server can selfhost the whole platform, and a selfhosted install includes every feature with no plan limits.',
            ],
        ];

        $dotSections = [
            ['top', 'Where it goes'],
            ['gala', 'The gala'],
            ['free', 'The free half'],
            ['supporters', 'Supporters'],
            ['programs', 'Programs'],
            ['honest', 'What it is not'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-cause-page" class="es-cause-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the receipt                                         -->
    <!-- ============================================================ -->
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 24% 32%, rgba(15, 94, 90, 0.2), rgba(15, 94, 90, 0) 62%); opacity: 0.6;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 78% 58%, rgba(127, 211, 204, 0.16), rgba(127, 211, 204, 0) 62%); opacity: 0.5;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:76px_76px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <x-marketing.hero-eyebrow class="block es-cause-tag es-fade-up es-d-1 mb-5">Nonprofit event management, without the platform fee</x-marketing.hero-eyebrow>
                        <span class="es-mask"><span class="es-mask-line">The ticket pays for the cause.</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">Not for <span class="es-cause-grad">the platform</span>.</span></span>
                    </h1>

                    <p class="es-cause-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Most of what a nonprofit puts on costs nothing to attend, and the only question is
                        who is coming. Then there is the one night that raises money. Event Schedule runs
                        both from one page, and takes nothing from the ticket.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-cause-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Start your schedule
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#gala" class="es-cause-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See where the money goes
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The receipt. The platform line is the argument. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-cause-receipt p-6 sm:p-8">
                        <div class="flex items-baseline justify-between gap-3">
                            <p class="es-cause-cap es-cause-receipt-muted">Spring gala</p>
                            <p class="es-cause-num es-cause-receipt-muted text-xs">3 ticket types</p>
                        </div>
                        <p class="mt-2 text-xl font-bold">An evening for the food bank</p>

                        <div class="mt-5 space-y-2">
                            @foreach ($ticketTypes as [$tName, $tPrice, $tSold])
                                <div class="flex items-baseline justify-between gap-3 text-sm">
                                    <span class="min-w-0 flex-1 truncate font-semibold">{{ $tName }}</span>
                                    <span class="es-cause-num es-cause-receipt-muted shrink-0 text-xs">{{ $tSold }}</span>
                                    <span class="es-cause-num w-20 shrink-0 text-right">{{ $tPrice }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="es-cause-receipt-rule mt-5 space-y-2 pt-4 text-sm">
                            <div class="flex items-baseline justify-between gap-3">
                                <span>Payment provider</span>
                                <span class="es-cause-receipt-muted text-xs">their own rate</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="font-semibold">Event Schedule</span>
                                <span class="es-cause-num es-cause-zero text-lg font-bold">0.00</span>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="font-semibold">To the cause</span>
                                <span class="es-cause-receipt-muted text-xs">everything else</span>
                            </div>
                        </div>

                        <div class="es-cause-bar mt-5" role="img" aria-label="Where a ticket goes: a small share to the payment provider, nothing to Event Schedule, and the rest to the organization.">
                            <span class="es-cause-bar-fee" style="width: 4%;"></span>
                            <span class="es-cause-bar-cause" style="width: 96%;"></span>
                        </div>
                        <p class="es-cause-cap es-cause-receipt-muted mt-3">Paid into your own account</p>
                    </div>

                    <p class="es-cause-muted mt-5 text-sm">
                        Illustrative amounts. The provider share is set by Stripe or PayPal, not by us.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The gala (01)                                             -->
    <!-- ============================================================ -->
    <section id="gala" class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                    <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The gala</p>
                    <h2 class="es-balance es-cause-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        The night that <span class="es-cause-grad">pays for the year</span>.
                    </h2>
                    <p class="es-cause-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A fundraiser is rarely one price. There is the supporter ticket, the table a local
                        business buys for its staff, and the student rate that keeps the room from being
                        only donors. Each is its own ticket type with its own price and quantity, on one
                        event page you can send anywhere.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['Several ticket types', 'Supporter, table of eight and student, each with its own price and its own stock.'],
                            ['Your own account', 'Paid into your Stripe or PayPal account, by an Invoice Ninja invoice, a payment link or cash at the door.'],
                            ['Codes and extras', 'A promo code for last year\'s volunteers, and add-ons like the raffle strip or the coach from town.'],
                            ['Refunds you can do yourself', 'From the Sales page, back through Stripe or PayPal in full or in part when a guest cannot come.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-cause-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-cause-ink font-semibold">{{ $t }}</span> <span class="es-cause-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-cause-plan es-cause-plan-pro">Pro plan</span>
                        <span class="es-cause-muted ml-2 text-sm">A price on a ticket, and everything that moves the money, is Pro. The platform fee is zero on every plan.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-cause-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-cause-ink text-lg font-bold">What the gala page carries</h3>
                            <span class="es-cause-muted es-cause-num text-xs">1 event</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Tickets', 'Supporter, table, student'],
                                ['Group rate', 'Cheaper per head in fours'],
                                ['Add-on', 'Raffle strip, limited stock'],
                                ['Promo code', 'For last year\'s volunteers'],
                                ['At the door', 'QR scan on any phone'],
                            ] as [$fLabel, $fValue])
                                <div class="es-cause-sub flex items-baseline justify-between gap-3 p-3.5">
                                    <span class="es-cause-muted w-24 shrink-0 text-xs uppercase tracking-wider">{{ $fLabel }}</span>
                                    <span class="es-cause-ink min-w-0 flex-1 truncate text-right text-sm font-semibold">{{ $fValue }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-cause-muted mt-5 border-t border-[rgba(19,32,31,0.1)] pt-4 text-xs dark:border-[rgba(232,239,238,0.12)]">
                            Scanning tickets at the door works on every plan. The live check-in dashboard,
                            with a running count by ticket type, is Pro.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The free half (02)                                        -->
    <!-- ============================================================ -->
    <section id="free" class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The free half</p>
                <h2 class="es-balance es-cause-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Most of your calendar <span class="es-cause-grad">costs nothing to attend</span>.
                </h2>
                <p class="es-cause-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The Saturday clean-up, the new-volunteer induction, the info evening for families
                    who might use the service. Nobody pays, and you still need to know who is coming
                    before you buy the sandwiches.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @foreach ([
                    ['Capped free sign-ups', 'People register instead of paying. Give the event a capacity and it stops taking names when the places are gone, counted separately for every date.'],
                    ['Shifts that repeat', 'A Saturday shift is one recurring event. Weekly on chosen days, every other week or the second Tuesday of the month, with date exceptions for the weeks it does not run.'],
                    ['A waiting list for the full ones', 'When a free session fills, people can join its waiting list instead of emailing the office to ask whether anyone dropped out.'],
                    ['Names you can use', 'Ask for each guest\'s details when one person registers a group, so the induction list has every volunteer on it, not just whoever filled in the form.'],
                    ['A QR code at the door', 'Every registration carries one, and scanning it at the door works on any phone and on every plan.'],
                    ['Online sessions too', 'Paste the join link into an online event. It is printed on the registration, while the public page shows only where it is hosted.'],
                ] as [$t, $d])
                    <div class="es-cause-card es-cause-hover flex flex-col p-6" data-reveal>
                        <h3 class="es-cause-ink text-lg font-bold">{{ $t }}</h3>
                        <p class="es-cause-muted mt-2 text-sm">{{ $d }}</p>
                        <p class="mt-auto pt-5"><span class="es-cause-plan es-cause-plan-free">Free plan</span></p>
                    </div>
                @endforeach
            </div>

            <p class="es-cause-muted mx-auto mt-8 max-w-2xl text-center text-sm" data-reveal>
                Free registration is unlimited on every plan and never counts against anything. A
                volunteer program can run for years without the question of a subscription coming up.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Supporters (03)                                           -->
    <!-- ============================================================ -->
    <section id="supporters" class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-cause-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-cause-ink text-lg font-bold">Newsletter allowance</h3>
                                <span class="es-cause-muted es-cause-num text-xs">recipients per month</span>
                            </div>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Free plan', '10'],
                                    ['Pro plan', '100'],
                                    ['Enterprise', '1,000'],
                                    ['Your own email settings, or selfhosted', 'No limit'],
                                ] as [$pName, $pAllow])
                                    <div class="es-cause-sub flex items-baseline justify-between gap-3 p-3.5">
                                        <span class="es-cause-ink min-w-0 flex-1 text-sm font-semibold">{{ $pName }}</span>
                                        <span class="es-cause-muted es-cause-num shrink-0 text-xs">{{ $pAllow }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-cause-muted mt-5 border-t border-[rgba(19,32,31,0.1)] pt-4 text-xs dark:border-[rgba(232,239,238,0.12)]">
                                The count is per recipient, not per send. One appeal to 300 supporters uses 300,
                                which is three months of Pro. The digest of new events does not count at all.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Supporters</p>
                    <h2 class="es-balance es-cause-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        A list that is <span class="es-cause-grad">yours to keep</span>.
                    </h2>
                    <p class="es-cause-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Social posts reach whoever the feed decides. The people who leave their email on
                        your page reach you, and you reach them, without anything in between.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['The digest looks after itself', 'Supporters who confirm their email address get a short digest when you publish new events, at most one every 72 hours, outside your newsletter allowance.'],
                            ['The appeal is yours to write', 'A newsletter goes when you send it, to the people you choose, and counts each recipient against the monthly allowance.'],
                            ['Tell me when tickets go on sale', 'Switch on the "Notify me" card and people can leave an address on a gala announced months ahead. They hear when tickets open, if it is cancelled, and shortly before it starts.'],
                            ['Or no email at all', 'Anyone can subscribe to your live calendar feed, and every new volunteer day and event appears in their own calendar.'],
                        ] as [$t, $d])
                            <div class="es-cause-card es-cause-hover p-4" data-reveal>
                                <p class="es-cause-ink text-sm font-bold">{{ $t }}</p>
                                <p class="es-cause-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Programs, chapters and sponsors (04)                      -->
    <!-- ============================================================ -->
    <section id="programs" class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Programs</p>
                <h2 class="es-balance es-cause-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    One organization, <span class="es-cause-grad">many front doors</span>.
                </h2>
                <p class="es-cause-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    The youth program does not want the trustees' AGM on its page, and the north chapter
                    does not care about the south chapter's quiz night. Split them, and keep one calendar
                    for everyone who wants the lot.
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="es-cause-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-cause-ink text-lg font-bold">Sub-schedules and the website</h3>
                        <span class="es-cause-plan es-cause-plan-free">Free plan</span>
                    </div>
                    <p class="es-cause-muted mb-5 text-sm">Structure that costs nothing.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'A sub-schedule for each program, chapter or campaign, each with its own link to share.',
                            'The whole calendar embedded on the site you already run, so the events page is never a month out of date.',
                            'Two-way sync with Google, Outlook or CalDAV, so the staff calendar and the public one agree.',
                            'Draft events that stay members-only until the date is confirmed with the venue.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-cause-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-cause-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="es-cause-card flex flex-col p-6 sm:p-7" data-reveal="panel">
                    <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="es-cause-ink text-lg font-bold">Sponsors, feedback and the team</h3>
                        <span class="es-cause-plan es-cause-plan-pro">Pro plan</span>
                    </div>
                    <p class="es-cause-muted mb-5 text-sm">For the organization that reports back to funders.</p>
                    <ul class="space-y-3">
                        @foreach ([
                            'Sponsor and partner logos on the schedule page, in tiers, so the headline sponsor gets the headline spot.',
                            'Star ratings and comments collected from attendees after the event, for the report the funder asks for.',
                            'A sales export with every ticket and every answer, for the finance volunteer.',
                            'More than one colleague managing the schedule is Enterprise, with admins who see sales and viewers who scan at the door.',
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="es-cause-accent mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span class="es-cause-muted text-sm">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-auto pt-5"><span class="es-cause-plan es-cause-plan-ent">Enterprise</span> <span class="es-cause-muted ml-1 text-xs">for extra team members</span></p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. What it is not (05)                                       -->
    <!-- ============================================================ -->
    <section id="honest" class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                    <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">What it is not</p>
                    <h2 class="es-balance es-cause-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        An events tool, <span class="es-cause-grad">not a donor database</span>.
                    </h2>
                    <p class="es-cause-muted mb-6 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Better to say it here than after you have moved the gala over. Event Schedule sells
                        tickets and takes registrations. It does not process standalone donations, issue tax
                        receipts or keep a CRM of giving history. Keep the tools you use for those, and link
                        to your donation page from the event description.
                    </p>
                    <p class="es-cause-muted max-w-xl text-base leading-relaxed" data-reveal>
                        If you are weighing it against a platform built for nonprofit ticketing, the
                        side-by-side pages set out the differences: see the
                        <x-link href="{{ marketing_url('/zeffy-alternative') }}">Zeffy comparison</x-link>
                        and the
                        <x-link href="{{ marketing_url('/humanitix-alternative') }}">Humanitix comparison</x-link>.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2" data-reveal-group="90">
                    @foreach ([
                        ['Does', 'Ticketed fundraisers with several prices, paid into your own account.'],
                        ['Does', 'Free, capped registration for volunteers and open events.'],
                        ['Does not', 'Take a donation that is not attached to a ticket.'],
                        ['Does not', 'Issue tax receipts or track giving history.'],
                    ] as [$kind, $text])
                        <div class="es-cause-card flex flex-col p-5" data-reveal>
                            <p @class(['es-cause-cap', 'es-cause-accent' => $kind === 'Does', 'es-cause-muted' => $kind !== 'Does'])>{{ $kind }}</p>
                            <p class="es-cause-ink mt-2 text-sm font-semibold">{{ $text }}</p>
                        </div>
                    @endforeach

                    <div class="es-cause-card flex flex-col p-5 sm:col-span-2" data-reveal>
                        <p class="es-cause-cap es-cause-accent">Your data, your server</p>
                        <p class="es-cause-muted mt-2 text-sm">
                            Event Schedule is open source. An organization with a data-protection policy that
                            keeps personal data in-house can
                            <x-link href="{{ marketing_url('/selfhost') }}">selfhost the whole platform</x-link>,
                            with every feature included and no plan limits. On the hosted service, a backup
                            export of the schedule is always one click away.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Who it is for (06)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)] lg:py-28">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-cause-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Any group that <span class="es-cause-grad">runs on goodwill</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="70">
                <x-sub-audience-card
                    name="Charity Fundraisers"
                    description="Galas, sponsored walks and auction nights, with tiered tickets, sponsor logos on the page and no platform fee taken from what supporters pay."
                    icon-color="rose"
                    blog-slug="for-charity-fundraisers"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Volunteer Programs"
                    description="Clean-ups, shifts and training days with a capped free sign-up for each date, so you know who is coming before the van leaves."
                    icon-color="emerald"
                    blog-slug="for-volunteer-programs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11V7a2 2 0 114 0v4m0-1V5a2 2 0 114 0v5m0 0V7a2 2 0 114 0v7a7 7 0 01-7 7h-1a7 7 0 01-6.3-3.9L3 14.5a1.7 1.7 0 012.9-1.7L7 14.5V11z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Advocacy & Campaign Groups"
                    description="Town halls, phone banks and rallies on a page the group owns, with a digest to confirmed subscribers whenever new dates go up."
                    icon-color="amber"
                    blog-slug="for-advocacy-groups"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Mutual Aid Networks"
                    description="Weekly food distributions and skill shares set once as recurring events, shared by link and QR code with no cost to anyone."
                    icon-color="teal"
                    blog-slug="for-mutual-aid-networks"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
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
        <div class="es-cause-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                    <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Set up in an afternoon, <span class="es-cause-grad">run all year</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Start the schedule', 'One page for the organization, with a sub-schedule for each program, chapter or campaign.'],
                        ['02', 'Open the free events', 'Volunteer days and info sessions with free registration and a capacity on each date.'],
                        ['03', 'Sell the gala', 'On Pro, ticket types and prices paid into your own account, with nothing taken by the platform.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-cause-lit es-cause-num mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cause-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Gala ticket types, QR check-in and zero platform fees, with prices on the Pro plan" :url="marketing_url('/features/ticketing')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Recurring Events" description="Volunteer shifts set once, with the weeks they skip taken out" :url="marketing_url('/features/recurring-events')" icon-color="teal">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Write to supporters yourself, within an allowance counted per recipient" :url="marketing_url('/features/newsletters')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="A link for each program, chapter or campaign" :url="marketing_url('/features/sub-schedules')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Keep the events page on your own site up to date by itself" :url="marketing_url('/features/embed-calendar')" icon-color="slate">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-cause-accent inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-[rgba(19,32,31,0.1)] py-16 dark:border-[rgba(232,239,238,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cause-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-churches', 'Churches'],
                    ['/for-community-centers', 'Community Centers'],
                    ['/for-festivals', 'Festivals'],
                    ['/for-meetup-groups', 'Meetup Groups'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-cause-card es-cause-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-cause-muted text-sm">Event Schedule for</div>
                            <div class="es-cause-ink text-lg font-semibold">{{ $relName }}</div>
                        </div>
                        <svg aria-hidden="true" class="es-cause-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-cause-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(19,32,31,0.1)] py-20 dark:border-[rgba(232,239,238,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cause-chip mb-6" data-reveal aria-hidden="true"><span>08</span></div>
                <p class="es-cause-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-cause-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked at <span class="es-cause-grad">the board meeting</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-cause-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-cause-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-cause-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-cause-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-cause-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-cause-tag mb-6">Free forever</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Keep every ticket <span class="es-cause-grad">working for the cause</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        Volunteer days, info sessions and a page supporters follow cost nothing. Putting a
                        price on the gala ticket is the part that needs Pro.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-cause" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=curator') }}" class="es-cause-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Start your schedule
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#141b1b] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
