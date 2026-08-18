<x-marketing-layout>
    <x-slot name="title">Restaurant Event Ticketing | Know the Covers Before You Shop</x-slot>
    <x-slot name="description">A wine dinner for twenty-four means the kitchen buys for twenty-four. Sell the covers, close the door before you shop, and collect the allergies at checkout instead of over email.</x-slot>
    <x-slot name="breadcrumbTitle">For Restaurants</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Restaurants",
        "description": "Ticketed dinners with a fixed covers count, a sales cutoff set before you shop, and dietary questions answered at checkout.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Restaurants"
        }
    }
    </script>
    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Restaurants",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Restaurant Event Ticketing Software",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "{{ platform_currency() }}",
            "description": "Free forever"
        },
        "featureList": [
            "A fixed number of covers per sitting, counted per date",
            "A sales cutoff set on the event, so the count is final before you shop",
            "Questions attached to the ticket and answered at checkout, for allergies and courses",
            "Named ticket types with their own prices and quantities",
            "QR check-in at the door",
            "Zero platform fees on ticket sales through your own Stripe account",
            "Private hire enquiries that wait for you to accept them",
            "Sub-schedules with their own shareable link, for private dining or a supper club",
            "Draft events that stay members-only until you are ready to announce",
            "Direct newsletters to the people who follow the restaurant",
            "Two-way Google, Outlook and CalDAV calendar sync",
            "Embeddable calendar for your own website",
            "Online events with the link people join on"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "restaurant event ticketing, wine dinner tickets, covers count, supper club booking, private dining enquiries, chef's table tickets",
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
           For-restaurants "Twenty-Four Covers" styles.

           CONCEPT: a restaurant is not a venue - it already has a
           business. Its calendar is not the product; most nights are just
           service. The few nights that need a page are seat-limited and
           prepaid, because THE KITCHEN BUYS FOR A FIXED NUMBER. Oversell
           and you turn people away; undersell and you throw food out. So
           the count is the product, and the page is built on it.

           THE DEVICE IS THE COUNT ITSELF - a large fraction with a simple
           fill. Deliberately NOT a row of discrete seat marks: 24 marks in
           a line would read as /for-theaters' day strip, and anything
           resembling a seating plan would imply a seat map, which the
           product does not have (no seat selection exists anywhere).

           THREE MECHANISMS BACK THE SPINE, all checked in code:
             - Covers = ticket quantity, and inventory is PER DATE
               (Ticket::soldKey($date) keys the sold tally by occurrence).
             - The cutoff is Ticket.sales_end_at, a single absolute
               datetime ("Sales will automatically stop at this date and
               time"). That was WRONG on the recurring comedy night and had
               to be removed there; for a one-off dinner it is exactly the
               feature. The page says plainly that it is one date, not a
               weekly rule.
             - Allergies at checkout are Ticket.custom_fields, rendered by
               the purchase view (event/tickets.blade.php) and gated isPro.

           TIER HONESTY: the covers story is genuinely PRO. The page says
           so rather than dressing paid features as free; the schedule
           itself and private-hire enquiries are the free part.

           COLOUR: wine, and the trade-off is named. The audit leaves green
           120-139 (squeezed against food-trucks at 113, which IS a food
           neighbour) and blue 240-259 (comedy-clubs just took 231), and
           neither is materially honest here. Wine is. The claimed roses
           are circus and comedians at 345-348deg / 73-83% saturation; this
           is #71243d at 340deg / 51% / 29% - deeper and far more muted.
           The hue gap is only 5-8deg, but NEITHER of those pages is a
           restaurant neighbour: the pages a reader reaches from here are
           bars, breweries, food trucks, farmers markets and hotels, all
           orange/amber/lime/green. That is the neighbour test that ruled
           green OUT for food trucks, applied the other way.

           Measured: #71243d 9.47 on ground / 10.05 on card; #e0a8b9 9.60 /
           8.80 / 9.37 dark. NEVER text-gray-500 - use .es-cover-muted
           (7.20 light / 7.08 dark).
           ============================================================== */

        /* --- Ground and ink --- */
        .es-cover-page { background-color: #f6f4f2; color: #16110f; }
        .dark .es-cover-page { background-color: #100e0e; color: #f0eae8; }
        .es-cover-ink { color: #16110f; }
        .dark .es-cover-ink { color: #f0eae8; }
        .es-cover-muted { color: #57504d; }
        .dark .es-cover-muted { color: #a49b98; }
        .es-cover-accent { color: #71243d; }
        .dark .es-cover-accent { color: #e0a8b9; }
        /* Always-lit accent for the band, in both colour modes. */
        .es-cover-lit { color: #e0a8b9; }

        .es-cover-grad {
            background-image: linear-gradient(100deg, #71243d, #8d3350);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dark .es-cover-grad,
        .es-cover-band .es-cover-grad {
            background-image: linear-gradient(100deg, #ecc0cd, #e0a8b9);
        }

        /* --- Surfaces --- */
        .es-cover-card {
            background-color: #fcfbfa;
            border: 1px solid rgba(22, 17, 15, 0.12);
            border-radius: 0.7rem;
        }
        .dark .es-cover-card {
            background-color: #1b1818;
            border-color: rgba(240, 234, 232, 0.13);
        }
        .es-cover-sub {
            background-color: rgba(22, 17, 15, 0.045);
            border-radius: 0.45rem;
        }
        .dark .es-cover-sub { background-color: rgba(240, 234, 232, 0.05); }
        .es-cover-hover { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .es-cover-hover:hover { border-color: rgba(113, 36, 61, 0.45); box-shadow: 0 10px 28px -18px rgba(22, 17, 15, 0.5); }
        .dark .es-cover-hover:hover { border-color: rgba(224, 168, 185, 0.4); box-shadow: 0 10px 28px -18px rgba(0, 0, 0, 0.8); }

        /* --- The count -----------------------------------------------
           The numerals do the work. The fill is a single bar, not a seat
           plan: its width is set inline from the same figures the text
           states, so the two cannot disagree. */
        .es-cover-figure {
            font-size: clamp(3rem, 9vw, 4.5rem);
            font-weight: 900;
            letter-spacing: -0.03em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .es-cover-of { font-size: 0.42em; font-weight: 700; letter-spacing: 0.04em; }
        .es-cover-meter {
            height: 0.6rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            overflow: hidden;
        }
        .es-cover-meter-fill {
            height: 100%;
            border-radius: 999px;
            background: #ecc0cd;
        }
        .es-cover-num {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, 'Liberation Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* --- The sitting card: the one thing being sold --- */
        .es-cover-sitting {
            background-color: #71243d;
            color: #ffffff;
            border-radius: 0.7rem;
        }
        .dark .es-cover-sitting { background-color: #5c1c31; }
        .es-cover-rule { height: 1px; background: rgba(255, 255, 255, 0.22); }

        /* --- Eyebrow, numerals, plan tags --- */
        .es-cover-tag {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #71243d;
        }
        .dark .es-cover-tag { color: #e0a8b9; }
        .es-cover-band .es-cover-tag { color: #e0a8b9; }

        .es-cover-corner {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 1.9rem;
            border: 1px solid rgba(22, 17, 15, 0.22);
            border-radius: 0.25rem;
            background: rgba(22, 17, 15, 0.035);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 0.78rem;
            font-weight: 700;
            color: #16110f;
        }
        .dark .es-cover-corner { border-color: rgba(240, 234, 232, 0.22); background: rgba(240, 234, 232, 0.05); color: #f0eae8; }
        .es-cover-band .es-cover-corner { border-color: rgba(240, 234, 232, 0.22); background: rgba(240, 234, 232, 0.05); color: #f0eae8; }
        .es-cover-corner::before {
            content: "";
            position: absolute;
            left: 0.4rem;
            top: 0.4rem;
            bottom: 0.4rem;
            width: 2px;
            background: #71243d;
        }
        .dark .es-cover-corner::before { background: #e0a8b9; }
        .es-cover-band .es-cover-corner::before { background: #e0a8b9; }

        /* Plan tiers ONLY - never reuse these for a state badge. */
        .es-cover-plan {
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
        .es-cover-plan-free { border-color: rgba(22, 17, 15, 0.22); color: #57504d; }
        .dark .es-cover-plan-free { border-color: rgba(240, 234, 232, 0.26); color: #a49b98; }
        .es-cover-plan-pro { border-color: rgba(113, 36, 61, 0.5); color: #71243d; background: rgba(113, 36, 61, 0.08); }
        .dark .es-cover-plan-pro { border-color: rgba(224, 168, 185, 0.42); color: #e0a8b9; background: rgba(224, 168, 185, 0.1); }

        /* --- Buttons --- */
        .es-cover-btn {
            background-color: #71243d;
            color: #ffffff;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .es-cover-btn:hover { background-color: #5c1c31; transform: translateY(-1px); box-shadow: 0 14px 28px -16px rgba(113, 36, 61, 0.9); }
        .es-cover-ghost {
            border: 1px solid rgba(22, 17, 15, 0.22);
            color: #16110f;
            transition: border-color 0.2s ease, background-color 0.2s ease;
        }
        .es-cover-ghost:hover { border-color: rgba(113, 36, 61, 0.5); background-color: rgba(113, 36, 61, 0.06); }
        .dark .es-cover-ghost { border-color: rgba(240, 234, 232, 0.24); color: #f0eae8; }
        .dark .es-cover-ghost:hover { border-color: rgba(224, 168, 185, 0.45); background-color: rgba(224, 168, 185, 0.08); }

        /* --- The dark band ------------------------------------------
           A resolvable background-color under the gradients: it is what
           paints if they fail and what a contrast audit can read. */
        .es-cover-band {
            background-color: #141111;
            background-image:
                radial-gradient(ellipse 70% 50% at 50% 0%, rgba(113, 36, 61, 0.4), rgba(113, 36, 61, 0) 70%),
                linear-gradient(180deg, #1b1818, #141111);
        }

        /* --- Nothing inside the band may change between colour modes --
           The band has no .dark variant, so any descendant that HAS one
           would render differently on an identical ground. Two shared
           classes carry their own .dark rules in marketing.css and are
           invisible to a grep of this file. */
        .es-cover-band .grid-overlay {
            background-image:
                linear-gradient(rgba(240, 234, 232, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(240, 234, 232, 0.05) 1px, transparent 1px);
        }
        .es-cover-band .animate-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            background-size: 200% 100%;
        }
        .es-cover-band .es-claim:focus-within {
            border-color: rgba(224, 168, 185, 0.75);
            box-shadow: 0 0 0 4px rgba(224, 168, 185, 0.22);
        }

        /* Shared chrome that is hard-coded brand blue. */
        .es-dot:hover .es-dot-pip { background-color: rgba(113, 36, 61, 0.6); }
        .dark .es-dot:hover .es-dot-pip { background-color: rgba(224, 168, 185, 0.6); }
        .es-dot.is-active .es-dot-pip { background: #71243d; }
        .dark .es-dot.is-active .es-dot-pip { background: #e0a8b9; }

        /* Focus rings. Never set border-radius here: an outline already
           follows the element's own radius. */
        #es-cover-page a:focus-visible,
        #es-cover-page summary:focus-visible,
        #es-cover-page button:focus-visible,
        #es-cover-page input:focus-visible {
            outline: 2px solid #71243d;
            outline-offset: 2px;
        }
        .dark #es-cover-page a:focus-visible,
        .dark #es-cover-page summary:focus-visible,
        .dark #es-cover-page button:focus-visible,
        .dark #es-cover-page input:focus-visible {
            outline-color: #e0a8b9;
        }
        .es-cover-band a:focus-visible,
        .es-cover-band summary:focus-visible,
        .es-cover-band button:focus-visible,
        .es-cover-band input:focus-visible {
            outline-color: #e0a8b9 !important;
        }

        @media (prefers-reduced-motion: reduce) {
            .es-cover-btn:hover { transform: none; }
        }
    </style>

    @php
        // One sitting. Every figure the page states comes from here, and the
        // meter's width is computed from the same two numbers, so the bar and
        // the fraction cannot disagree. Asserted at build time: sold < seats,
        // and the cutoff day precedes the sitting.
        $sitting = [
            'name'      => 'Burgundy dinner',
            'day'       => 'Saturday',
            'time'      => '7:30pm',
            'seats'     => 24,
            'sold'      => 19,
            'cutoff'    => 'Thursday, 11:59pm',
            'shop'      => 'Friday morning',
            'price'     => 85,
        ];
        $remaining = $sitting['seats'] - $sitting['sold'];
        $fillPct   = (int) round($sitting['sold'] / $sitting['seats'] * 100);

        $faqs = [
            [
                'q' => 'Is Event Schedule free for restaurants?',
                'a' => 'The schedule itself is free forever: your public page and its link, sub-schedules for private dining or a supper club, enquiries for private hire, Drafts that keep an event off the public page until you announce it, two-way calendar sync, an embeddable calendar and up to 10 newsletter emails a month, counted per recipient rather than per send. Selling covers is on the Pro plan at '.plan_price($proMonthly).' a month, and Event Schedule charges zero platform fees on sales.',
            ],
            [
                'q' => 'How do I stop selling more covers than the kitchen can cook?',
                'a' => 'Give the ticket a quantity and that is the number of covers. It is counted per date, so a sold-out Saturday does not stop the following Saturday selling. When the quantity is gone the sitting closes itself, which means the number you are cooking for is the number that sold.',
            ],
            [
                'q' => 'Can sales stop before the night, so I know what to buy?',
                'a' => 'Yes. A ticket type takes a date and time to come off sale, so you can close Thursday at midnight and do the ordering on Friday against a final number. It is a single date set on that event rather than a rule that repeats, which is exactly what a one-off dinner wants.',
            ],
            [
                'q' => 'Can I collect allergies and dietary requirements?',
                'a' => 'Yes, on the Pro plan. Questions can be attached to the ticket and answered at checkout, so the answers arrive with the sale rather than in a separate email thread you have to reconcile against the list. Ask for allergies, a course choice, or a wine pairing.',
            ],
            [
                'q' => 'What about private hire enquiries?',
                'a' => 'Turn on booking requests and people can ask about a date through your page. Every enquiry waits for you to accept it, so nothing appears publicly that you have not agreed to, and you are emailed when new ones are waiting. Keep private dining on its own sub-schedule and you can share a link that shows only those events.',
            ],
        ];

        $dotSections = [
            ['top', 'The sitting'],
            ['count', 'The count'],
            ['cutoff', 'The cutoff'],
            ['ask', 'The questions'],
            ['hire', 'Private hire'],
            ['who', 'Who it is for'],
            ['how', 'How it works'],
            ['faq', 'Questions'],
            ['claim', 'Get started'],
        ];
    @endphp

    <div id="es-cover-page" class="es-cover-page">

    <!-- ============================================================ -->
    <!-- 1. Hero: the sitting                                         -->
    <!-- ============================================================ -->
    {{-- The nav overlays the top of the page, so the hero carries extra
         top padding rather than letting the card sit under it. --}}
    <section id="top" class="es-hero noise relative flex min-h-[calc(88svh-4rem)] scroll-mt-24 items-center overflow-hidden pb-16 pt-28">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 28% 28%, rgba(113, 36, 61, 0.22), rgba(113, 36, 61, 0) 62%); opacity: 0.5;"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 74% 62%, rgba(224, 168, 185, 0.16), rgba(224, 168, 185, 0) 62%); opacity: 0.45;"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:72px_72px] [mask-image:radial-gradient(ellipse_72%_62%_at_50%_38%,black_22%,transparent_74%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div>
                    <p class="es-cover-tag es-fade-up es-d-1 mb-5">For restaurants and dining rooms</p>

                    <h1 class="es-balance mb-7 text-[2.6rem] font-black leading-[1.05] tracking-tight sm:text-6xl">
                        <span class="es-mask"><span class="es-mask-line">The kitchen buys</span></span>
                        <span class="es-mask es-mask-2"><span class="es-mask-line">for <span class="es-cover-grad">a number</span>.</span></span>
                    </h1>

                    <p class="es-cover-muted es-fade-up es-d-2 mb-9 max-w-xl text-lg sm:text-xl">
                        Sell one cover too many and you are turning people away at the door. Sell one
                        too few and it goes in the bin. Every other night is service - these are the
                        nights that need a count you can trust.
                    </p>

                    <div class="es-fade-up es-d-3 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-cover-btn inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            Put a sitting on sale
                            <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                        <a href="#count" class="es-cover-ghost inline-flex items-center justify-center gap-2 rounded-lg px-7 py-4 text-base font-semibold">
                            See how the count works
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </a>
                    </div>
                </div>

                <!-- The sitting. The meter width is computed from the same
                     two figures the fraction prints. -->
                <div class="es-fade-up es-d-4" data-reveal>
                    <div class="es-cover-sitting p-7 sm:p-9">
                        <p class="text-[0.6rem] font-bold uppercase tracking-[0.22em] text-white/70">{{ $sitting['name'] }}</p>
                        <p class="es-cover-num mt-1 text-sm text-white/80">{{ $sitting['day'] }} &middot; {{ $sitting['time'] }} &middot; ${{ $sitting['price'] }}</p>

                        <p class="es-cover-figure mt-7 text-white">
                            {{ $sitting['sold'] }}<span class="es-cover-of text-white/70"> of {{ $sitting['seats'] }}</span>
                        </p>
                        <p class="mt-2 text-sm text-white/80">covers sold &middot; {{ $remaining }} left</p>

                        <div class="es-cover-meter mt-4" aria-hidden="true">
                            <div class="es-cover-meter-fill" style="width: {{ $fillPct }}%;"></div>
                        </div>

                        <div class="es-cover-rule my-6" aria-hidden="true"></div>

                        <dl class="space-y-1.5 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-white/70">Sales close</dt>
                                <dd class="es-cover-num text-white">{{ $sitting['cutoff'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-white/70">You shop</dt>
                                <dd class="es-cover-num text-white">{{ $sitting['shop'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-white/70">At checkout</dt>
                                <dd class="text-white">&ldquo;Any allergies?&rdquo;</dd>
                            </div>
                        </dl>
                    </div>

                    <p class="es-cover-muted mt-5 text-xs">
                        The number on the card is the number you cook for. Nothing is counted twice.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. The count (01)                                            -->
    <!-- ============================================================ -->
    <section id="count" class="scroll-mt-24 border-t border-[rgba(22,17,15,0.1)] py-20 dark:border-[rgba(240,234,232,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cover-corner mb-6" data-reveal aria-hidden="true"><span>01</span></div>
                <p class="es-cover-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The count</p>
                <h2 class="es-balance es-cover-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Twenty-four covers means <span class="es-cover-grad">twenty-four</span>.
                </h2>
                <p class="es-cover-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Set how many covers the sitting has and it will not sell past them. When they
                    are gone it closes itself, so the figure you shop against is the figure that sold.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="100">
                @foreach ([
                    ['It closes itself', 'No watching an inbox and no doing sums at eleven at night. The last cover sells and the sitting stops taking money.'],
                    ['More than one price? One pool', 'Two ticket types are counted separately unless you say otherwise. Set the sitting to Combined Total and they share a single count, so twenty-four stays twenty-four however many ways you sell it.'],
                    ['Counted per date', 'A sold-out Saturday does not stop the following Saturday selling. Each date keeps its own count, which is what makes a repeating supper club work.'],
                    ['Paid before they sit', 'The money is in before the shopping goes out, so a no-show is somebody else\'s problem rather than a hole in your week.'],
                ] as [$t, $d])
                    <div class="es-cover-card es-cover-hover p-6" data-reveal>
                        <h3 class="es-cover-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-cover-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-cover-plan es-cover-plan-pro">Pro</span>
                <span class="es-cover-muted ml-2 text-sm">
                    Selling covers is on the Pro plan at {{ plan_price($proMonthly) }} a month, with no platform fee on top of what Stripe charges.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. The cutoff (02)                                           -->
    <!-- ============================================================ -->
    <section id="cutoff" class="scroll-mt-24 border-t border-[rgba(22,17,15,0.1)] py-20 dark:border-[rgba(240,234,232,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div>
                    <div class="es-cover-corner mb-6" data-reveal aria-hidden="true"><span>02</span></div>
                    <p class="es-cover-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The cutoff</p>
                    <h2 class="es-balance es-cover-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Close the door <span class="es-cover-grad">before you shop</span>.
                    </h2>
                    <p class="es-cover-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        A late booking is worse than an empty chair once the order has gone in. Give
                        the ticket a date and time to come off sale, and the count is final while you
                        still have a morning to buy against it.
                    </p>

                    <ul class="space-y-4" data-reveal-group="90">
                        @foreach ([
                            ['One date, set on the event', 'It is a single moment you choose for that sitting, not a rule that repeats each week. For a dinner that runs once, that is exactly right.'],
                            ['Sales stop on their own', 'You do not have to remember to switch anything off on Thursday night while you are on the pass.'],
                            ['The list is final', 'Whatever the total says on Friday morning is what you are cooking. Nothing can be added behind you.'],
                        ] as [$t, $d])
                            <li class="flex items-start gap-3" data-reveal>
                                <svg aria-hidden="true" class="es-cover-accent mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span><span class="es-cover-ink font-semibold">{{ $t }}</span> <span class="es-cover-muted">- {{ $d }}</span></span>
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-7" data-reveal>
                        <span class="es-cover-plan es-cover-plan-pro">Pro</span>
                        <span class="es-cover-muted ml-2 text-sm">Part of ticketing.</span>
                    </p>
                </div>

                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner es-cover-card overflow-hidden p-6 sm:p-7">
                        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
                            <h3 class="es-cover-ink text-lg font-bold">The week before</h3>
                            <span class="es-cover-muted es-cover-num text-xs">one sitting</span>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ([
                                ['Mon', 'On sale', '12 covers'],
                                ['Wed', 'On sale', '17 covers'],
                                ['Thu', 'Sales close 11:59pm', '19 covers'],
                                ['Fri', 'You shop for 19', 'final'],
                                ['Sat', 'Service', '19 covers'],
                            ] as [$dDay, $dWhat, $dCount])
                                <div class="es-cover-sub flex items-baseline gap-3 p-3.5">
                                    <span class="es-cover-muted es-cover-num w-10 shrink-0 text-xs font-bold uppercase">{{ $dDay }}</span>
                                    <span class="es-cover-ink min-w-0 flex-1 truncate text-sm font-semibold">{{ $dWhat }}</span>
                                    <span class="es-cover-muted es-cover-num shrink-0 text-xs">{{ $dCount }}</span>
                                </div>
                            @endforeach
                        </div>

                        <p class="es-cover-muted mt-5 border-t border-[rgba(22,17,15,0.1)] pt-4 text-xs dark:border-[rgba(240,234,232,0.12)]">
                            Nineteen sold, nineteen cooked. The two numbers were never allowed to drift apart.
                        </p>

                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. The questions (03)                                        -->
    <!-- ============================================================ -->
    <section id="ask" class="scroll-mt-24 border-t border-[rgba(22,17,15,0.1)] py-20 dark:border-[rgba(240,234,232,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-14 lg:grid-cols-2 lg:gap-16">
                <div class="order-2 lg:order-1">
                    <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                        <div class="es-tilt-inner es-cover-card overflow-hidden p-6 sm:p-7">
                            <div class="mb-1 flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="es-cover-ink text-lg font-bold">At checkout</h3>
                                <span class="es-cover-plan es-cover-plan-pro">Pro</span>
                            </div>
                            <p class="es-cover-muted mb-5 text-sm">Attached to the ticket, so the answer arrives with the sale.</p>

                            <div class="space-y-2.5">
                                @foreach ([
                                    ['Any allergies or intolerances?', 'Free text'],
                                    ['Main course', 'Beef / Halibut / Squash'],
                                    ['Wine pairing?', 'Yes / No'],
                                ] as [$qLabel, $qKind])
                                    <div class="es-cover-sub p-3.5">
                                        <p class="es-cover-ink text-sm font-semibold">{{ $qLabel }}</p>
                                        <p class="es-cover-muted es-cover-num text-xs">{{ $qKind }}</p>
                                    </div>
                                @endforeach
                            </div>

                            <p class="es-cover-muted mt-5 border-t border-[rgba(22,17,15,0.1)] pt-4 text-xs dark:border-[rgba(240,234,232,0.12)]">
                                Answers come through with the sale, so the pass list and the guest list are the same list.
                            </p>

                            <div class="es-glare" aria-hidden="true"></div>
                            <div class="es-ring-glow" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>

                <div class="order-1 lg:order-2">
                    <div class="es-cover-corner mb-6" data-reveal aria-hidden="true"><span>03</span></div>
                    <p class="es-cover-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">The questions</p>
                    <h2 class="es-balance es-cover-ink mb-6 text-3xl font-black leading-tight tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Ask about the nut allergy <span class="es-cover-grad">in January</span>.
                    </h2>
                    <p class="es-cover-muted mb-8 max-w-xl text-lg leading-relaxed" data-reveal style="--reveal-delay: 0.15s;">
                        Not at seven on the night, from a section that is already down. Questions can
                        sit on the ticket and be answered when people pay, so the kitchen has the
                        list before it writes the prep.
                    </p>

                    <div class="space-y-3" data-reveal-group="90">
                        @foreach ([
                            ['One list, not two', 'The answers are attached to the sale, so you are not reconciling an email thread against a booking list at four in the afternoon.'],
                            ['Ask what you actually need', 'Allergies as free text, a course choice from a set of options, a yes or no on the pairing. It is your question, not a fixed field.'],
                            ['Export it', 'Take the sales out as a CSV with the answers included, and hand the kitchen something it can read.'],
                        ] as [$t, $d])
                            <div class="es-cover-card es-cover-hover p-4" data-reveal>
                                <div class="flex items-center gap-2">
                                    <p class="es-cover-ink text-sm font-bold">{{ $t }}</p>
                                    <span class="es-cover-plan es-cover-plan-pro">Pro</span>
                                </div>
                                <p class="es-cover-muted mt-1 text-sm">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Private hire (04)                                         -->
    <!-- ============================================================ -->
    <section id="hire" class="scroll-mt-24 border-t border-[rgba(22,17,15,0.1)] py-20 dark:border-[rgba(240,234,232,0.1)] lg:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cover-corner mb-6" data-reveal aria-hidden="true"><span>04</span></div>
                <p class="es-cover-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Private hire</p>
                <h2 class="es-balance es-cover-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    The room at the back <span class="es-cover-grad">has its own link</span>.
                </h2>
                <p class="es-cover-muted text-lg" data-reveal style="--reveal-delay: 0.15s;">
                    Private dining is a different conversation from a wine dinner, and it does not
                    belong in the same list as the things anyone can buy.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="100">
                @foreach ([
                    ['Enquiries come to the page', 'Turn on booking requests and people ask about a date through your schedule instead of a form that lands in a shared inbox nobody owns.'],
                    ['Nothing posts without you', 'Every enquiry waits for you to accept it, and you are emailed when new ones are waiting. The public page only ever shows what you agreed to.'],
                    ['Its own strand, its own link', 'Put private dining on a sub-schedule and you can share a link that shows only those events, without splitting the restaurant into two pages.'],
                ] as [$t, $d])
                    <div class="es-cover-card es-cover-hover p-6" data-reveal>
                        <h3 class="es-cover-ink mb-2 text-lg font-bold">{{ $t }}</h3>
                        <p class="es-cover-muted text-sm">{{ $d }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 text-center" data-reveal>
                <span class="es-cover-plan es-cover-plan-free">Free</span>
                <span class="es-cover-muted ml-2 text-sm">
                    Enquiries, sub-schedules and the schedule itself cost nothing. An event can also stay a Draft until you are ready to announce it.
                </span>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Who it is for (05)                                        -->
    <!-- ============================================================ -->
    <section id="who" class="scroll-mt-24 border-t border-[rgba(22,17,15,0.1)] py-20 dark:border-[rgba(240,234,232,0.1)] lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cover-corner mb-6" data-reveal aria-hidden="true"><span>05</span></div>
                <p class="es-cover-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Who it is for</p>
                <h2 class="es-balance es-cover-ink mb-5 text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Any night you have to <span class="es-cover-grad">shop for</span>.
                </h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                <x-sub-audience-card
                    name="Fine Dining"
                    description="A tasting menu with a fixed number of covers, paid up front, with the dietaries in before the order goes out."
                    icon-color="rose"
                    blog-slug="for-fine-dining-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v8a3 3 0 006 0V3M8 11v10M16 3c-1.5 2-2 4-2 6a2 2 0 004 0c0-2-.5-4-2-6zm0 8v10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Wine Bars & Tapas"
                    description="A tasting on a Tuesday, priced per head. Small counts, sold in advance, closed off before the bottles are pulled."
                    icon-color="amber"
                    blog-slug="for-wine-bars-tapas"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 3h8l-1 7a3 3 0 01-6 0L8 3zm4 10v8m-3 0h6" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Farm-to-Table"
                    description="The menu depends on what came in, so the count has to be settled early. Close sales, then go to the market."
                    icon-color="emerald"
                    blog-slug="for-farm-to-table-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c0-6 3-9 8-9-1 6-4 9-8 9zm0 0c0-6-3-9-8-9 1 6 4 9 8 9zm0 0V9" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Supper Clubs"
                    description="The same night every month, each one with its own count, so selling out in March leaves April untouched."
                    icon-color="orange"
                    blog-slug="for-supper-clubs"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Casual Dining"
                    description="Quiz nights, live music, a set menu at Christmas. Most weeks nothing, and a page for the weeks there is something."
                    icon-color="sky"
                    blog-slug="for-casual-dining-restaurants"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <x-sub-audience-card
                    name="Chef's Tables & Pop-Ups"
                    description="Twelve seats in somebody else's room. Sell them all in advance and take the questions while you are at it."
                    icon-color="slate"
                    blog-slug="for-chefs-tables"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="h-6 w-6 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 15h16M4 15a8 8 0 0116 0M12 7V4m-9 14h18" />
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
        <div class="es-cover-band noise relative overflow-hidden rounded-[2rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="grid-overlay absolute inset-0 opacity-20"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-5xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <div class="es-cover-corner mb-6" data-reveal aria-hidden="true"><span>06</span></div>
                    <p class="es-cover-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">How it works</p>
                    <h2 class="es-balance text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                        Three decisions, <span class="es-cover-grad">then it runs</span>.
                    </h2>
                </div>

                <div class="grid gap-6 md:grid-cols-3" data-reveal-group="110">
                    @foreach ([
                        ['01', 'Set the covers', 'The quantity is how many you will cook for. Selling at more than one price? Combined Total keeps them in one pool rather than one count each.'],
                        ['02', 'Set the cutoff', 'A date and time for sales to stop, chosen so you still have a morning to buy against a final number.'],
                        ['03', 'Set the questions', 'Allergies, a course choice, a pairing. Answered at checkout and attached to the sale.'],
                    ] as [$n, $t, $d])
                        <div class="rounded-lg border border-white/10 bg-white/[0.05] p-7 backdrop-blur-sm" data-reveal="panel">
                            <p class="es-cover-lit es-cover-num mb-3 text-sm font-bold">{{ $n }}</p>
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
    <section class="scroll-mt-24 border-t border-[rgba(22,17,15,0.1)] py-20 dark:border-[rgba(240,234,232,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cover-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Key features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="A fixed number of covers, a sales cutoff, and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="rose">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Custom Fields" description="Ask for allergies or a course choice, answered at checkout" :url="marketing_url('/features/custom-fields')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Sub-schedules" description="Give private dining its own strand and its own link" :url="marketing_url('/features/sub-schedules')" icon-color="emerald">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Newsletters" description="Tell the regulars before the seats are gone" :url="marketing_url('/features/newsletters')" icon-color="green">
                        <x-slot:icon><svg aria-hidden="true" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/features') }}" class="es-cover-accent inline-flex items-center font-medium hover:underline">
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
    <section class="border-t border-[rgba(22,17,15,0.1)] py-16 dark:border-[rgba(240,234,232,0.1)]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="es-cover-ink mb-8 text-center text-2xl font-black tracking-tight md:text-3xl" data-reveal>Related pages</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2" data-reveal-group="70">
                @foreach ([
                    ['/for-bars', 'Bars'],
                    ['/for-breweries-and-wineries', 'Breweries &amp; Wineries'],
                    ['/for-food-trucks-and-vendors', 'Food Trucks'],
                    ['/for-hotels-and-resorts', 'Hotels &amp; Resorts'],
                ] as [$relHref, $relName])
                    <a href="{{ marketing_url($relHref) }}" data-reveal class="es-cover-card es-cover-hover group flex items-center justify-between p-5">
                        <div>
                            <div class="es-cover-muted text-sm">Event Schedule for</div>
                            <div class="es-cover-ink text-lg font-semibold">{!! $relName !!}</div>
                        </div>
                        <svg aria-hidden="true" class="es-cover-accent h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <a href="{{ marketing_url('/use-cases') }}" class="es-cover-accent inline-flex items-center font-medium hover:underline">
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
    <section id="faq" class="scroll-mt-24 border-t border-[rgba(22,17,15,0.1)] py-20 dark:border-[rgba(240,234,232,0.1)] lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <div class="es-cover-corner mb-6" data-reveal aria-hidden="true"><span>07</span></div>
                <p class="es-cover-tag mb-4" data-reveal style="--reveal-delay: 0.05s;">Questions</p>
                <h2 class="es-balance es-cover-ink text-3xl font-black tracking-tight md:text-5xl" data-reveal style="--reveal-delay: 0.1s;">
                    Asked <span class="es-cover-grad">across the pass</span>.
                </h2>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ($faqs as $faq)
                    <details name="faq" data-reveal class="es-cover-card group/faq overflow-hidden">
                        <summary class="flex cursor-pointer items-center justify-between gap-4 p-6">
                            <h3 class="es-cover-ink text-lg font-semibold">{{ $faq['q'] }}</h3>
                            <svg aria-hidden="true" class="es-cover-muted h-5 w-5 shrink-0 transition-transform duration-300 group-open/faq:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="es-cover-muted faq-answer px-6 pb-6">{{ $faq['a'] }}</p>
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
            <div class="es-cover-band noise relative overflow-hidden rounded-[2rem] border border-white/10 px-6 py-16 text-center shadow-2xl sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="grid-overlay absolute inset-0 opacity-25"></div>
                </div>

                <div class="relative z-10">
                    <p class="es-cover-tag mb-6">Free to start</p>
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black leading-tight tracking-tight text-white md:text-5xl">
                        Cook for the number <span class="es-cover-grad">that actually sold</span>.
                    </h2>
                    <p class="mx-auto mb-10 max-w-xl text-lg text-gray-300 sm:text-xl">
                        The schedule is free. Selling the covers is {{ plan_price($proMonthly) }} a month, and none of
                        the ticket price comes to us.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-lg border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-restaurant" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up?type=venue') }}" class="es-cover-btn group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-lg px-8 py-4 text-lg font-semibold">
                            <span class="relative z-10 flex items-center gap-2">
                                Put a sitting on sale
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
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#1b1818] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    </div>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
