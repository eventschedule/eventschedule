<x-marketing-layout>
    <x-slot name="title">Compare Event Schedule vs Eventbrite, Luma &amp; More</x-slot>
    <x-slot name="description">Compare Event Schedule with Eventbrite, Luma, Meetup, and 13 more platforms. Feature-by-feature breakdowns, a fee calculator, and zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">Compare</x-slot>

    @php
        // Both the visible FAQ and its JSON-LD read this one array, so the two
        // can no longer drift. The fee question deliberately does not restate
        // /pricing's FAQ answer; it points at the calculator on this page.
        $compareFaqs = [
            [
                'q' => 'Does Event Schedule really charge no platform fees?',
                'a' => 'Correct. We never take a percentage of your ticket sales on any plan. You pay your payment processor directly, and on Stripe that is '.$rates['stripe']['label'].'. Money from ticket sales goes straight to your own Stripe account, not through us.',
            ],
            [
                'q' => 'How is this different from the pricing page?',
                'a' => 'The pricing page explains what Event Schedule costs. This page puts those costs next to what other platforms charge for the same event, using their published rates, so you can see the difference rather than take our word for it.',
            ],
            [
                'q' => 'Can I move my events over from another platform?',
                'a' => 'Yes. Events can be imported from Eventbrite directly, attendees can be bulk imported from a CSV, and a full backup and restore is built in. Nothing has to be retyped.',
            ],
            [
                'q' => 'Which of these platforms are open source?',
                'a' => 'Event Schedule and Pretix. Both can be selfhosted, and both publish their source. Event Schedule runs on standard PHP and MySQL hosting, while Pretix expects Docker, PostgreSQL and Redis.',
            ],
            [
                'q' => 'Do I have to pay to use Event Schedule?',
                'a' => 'No. The free plan is free forever with unlimited events and schedules, and it sells up to 25 paid tickets a month. Pro at '.plan_price($rates['eventschedule']['monthly']).'/mo lifts that to unlimited ticket sales and adds the API, and selfhosted installs get every paid feature at no cost.',
            ],
        ];

        // Server-rendered calculator defaults, so the figures are correct with
        // JS off and for crawlers. The script recomputes with the same maths.
        $calcTickets = 100;
        $calcPrice = 10;
        $calcRevenue = $calcTickets * $calcPrice;
        $stripeCost = ($calcRevenue * $rates['stripe']['percent']) + ($calcTickets * $rates['stripe']['fixed']);

        $costOf = function (array $rate) use ($calcRevenue, $calcTickets, $stripeCost) {
            $own = ($calcRevenue * $rate['percent']) + ($calcTickets * $rate['fixed']) + ($rate['monthly'] ?? 0);

            return $own + (($rate['stripe'] ?? true) ? $stripeCost : 0);
        };

        $esCost = $costOf($rates['eventschedule']);
        $ebCost = $costOf($rates['eventbrite']);
        $lumaCost = min($costOf($rates['luma']), $rates['luma']['monthly'] + $stripeCost);
        $ttCost = $costOf($rates['ticket-tailor']);
        $worstCost = max($ebCost, $lumaCost, $ttCost);
        $calcSaving = max(0, $worstCost - $esCost);
    @endphp

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "@id": "{{ config('app.url') }}/compare#page",
        "name": "Compare Event Schedule with 16 event platforms",
        "url": "{{ config('app.url') }}/compare",
        "description": {!! json_encode('Feature-by-feature comparisons of Event Schedule against 16 event and ticketing platforms, with a fee calculator using published rates.', JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},
        "mainEntity": {
            "@type": "ItemList",
            "name": "Event platform comparisons",
            "numberOfItems": {{ count($headToHead) }},
            "itemListElement": [
                @foreach ($headToHead as $c)
                {
                    "@type": "ListItem",
                    "position": {{ $loop->iteration }},
                    "name": {!! json_encode('Event Schedule vs '.$c['name'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!},
                    "url": "{{ route($c['route']) }}"
                }@if (! $loop->last),@endif
                @endforeach
            ]
        },
        "about": {
            "@type": "SoftwareApplication",
            "name": "Event Schedule",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web",
            "offers": [
                {
                    "@type": "Offer",
                    "name": "Free",
                    "price": "0",
                    "priceCurrency": "{{ platform_currency() }}",
                    "description": "Unlimited events and schedules, free forever, with no platform fees"
                },
                {
                    "@type": "Offer",
                    "name": "Pro",
                    "price": "{{ $rates['eventschedule']['monthly'] }}",
                    "priceCurrency": "{{ platform_currency() }}",
                    "description": "Unlimited ticket sales, the check-in dashboard and API access, with 0% platform fees on ticket sales"
                }
            ]
        }
    }
    </script>
    </x-slot>
    <x-seo.faq-schema :items="$compareFaqs" />

    <style {!! nonce_attr() !!}>
        /* ==============================================================
           /compare page theme: "Head to Head".

           There are no competitor logos in this repo and none will be
           added (trademarks, and the no-new-asset rule), so the page's
           identity is typographic: the `vs` mark, set in the accent
           gradient, between the two columns of every head-to-head and
           on the picker chips. A type device, not decoration - nothing
           here animates on its own.

           Colour rules:
             blue/sky/cyan = the page accent, and our side of every
                             comparison.
             rose          = money that leaves your pocket, and NOTHING
                             else. Only competitor fees are rose, so the
                             cost argument reads at a glance instead of
                             hiding inside neutral table cells.
             emerald       = what you keep, matching /pricing.

           The matrix's emerald tick / grey cross verdict glyphs are
           semantic data and are left alone.

           Shared es-* primitives live in marketing.css; everything below
           is page-exclusive.
           ============================================================== */

        /* Page accent gradient (blue to sky to cyan). The light-mode stops are the
           darkened set .text-gradient-docs already uses: the old #0ea5e9/#06b6d4
           end measured 2.77 and 2.43 on white and failed AA for large text, which
           needs 3:1. Dark mode keeps the bright stops, which measure 7.5 and up on
           the near-black ground. */
        .text-gradient-compare {
            background: linear-gradient(135deg, #2563eb 0%, #0284c7 50%, #0e7490 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-compare,
        .es-finale-panel .text-gradient-compare,
        .es-band-dark .text-gradient-compare {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Money rule: every figure that leaves your pocket is rose */
        .es-cost { color: #e11d48; }
        .dark .es-cost, .es-band-dark .es-cost { color: #fb7185; }
        .es-keep { color: #047857; }
        .dark .es-keep, .es-band-dark .es-keep { color: #6ee7b7; }

        /* The savings odometer is a "what you keep" figure, so its digits are
           emerald. The shared es-od strip hardcodes the brand-blue gradient on
           each digit, and background-image (not the `background` shorthand) is
           required here: the shorthand resets background-clip to border-box,
           which un-clips the digits while -webkit-text-fill-color: transparent
           stays set, rendering each one as a solid block. */
        .es-od-strip span {
            background-image: linear-gradient(135deg, #059669 0%, #10b981 50%, #0d9488 100%);
        }
        .dark .es-od-strip span {
            background-image: linear-gradient(135deg, #6ee7b7 0%, #34d399 50%, #2dd4bf 100%);
        }

        /* The signature: a typographic vs mark. It also appears at 14px inside the
           "full comparison" link, so every light-mode stop has to clear 4.5:1 on
           white, not the 3:1 large-text bar - hence a darker middle stop than the
           heading gradient above. */
        .es-vs {
            font-weight: 900;
            font-style: italic;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #2563eb 0%, #0369a1 50%, #0e7490 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .es-vs, .es-band-dark .es-vs {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ---- Platform picker ----------------------------------------
           Sixteen chips will not fit in a horizontal tab strip at any
           width, so the tablist wraps into a grid. Panels ship visible
           and the script hides the inactive ones, so no-JS visitors and
           crawlers get all sixteen head-to-heads. */
        .es-chip {
            transition: border-color 0.2s, background-color 0.2s, color 0.2s, transform 0.2s;
        }
        .es-chip[aria-selected="true"] {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
        }
        .dark .es-chip[aria-selected="true"] {
            border-color: rgba(96, 165, 250, 0.6);
            background: rgba(37, 99, 235, 0.16);
            color: #93c5fd;
        }
        .es-chip:hover { transform: translateY(-1px); }

        /* Arrow keys move focus with a programmatic .focus(), which Chrome
           does not reliably treat as :focus-visible, so the script flags a
           keyboard state and plain :focus is honoured in it. */
        .es-chip:focus-visible,
        .es-picker.is-kbd .es-chip:focus,
        .es-more:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
        .dark .es-chip:focus-visible,
        .dark .es-picker.is-kbd .es-chip:focus,
        .dark .es-more:focus-visible { outline-color: #60a5fa; }

        /* Below sm only the first eight chips show until "show all" is used.
           Without JS every chip is visible and the button is hidden. */
        .es-more { display: none; }
        .es-picker.is-ready .es-more { display: inline-flex; }
        @media (max-width: 639px) {
            .es-picker.is-ready:not(.is-expanded) .es-chip[data-overflow="1"] { display: none; }
        }

        {{-- The full matrix --}}
        .compare-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .compare-table-wrapper::-webkit-scrollbar { height: 6px; }
        .compare-table-wrapper::-webkit-scrollbar-track { background: transparent; }
        .compare-table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        .compare-table th:first-child,
        .compare-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 10;
            background: white;
        }
        .dark .compare-table th:first-child,
        .dark .compare-table td:first-child { background: #0f0f14; }
        .compare-table .section-header td { background: rgb(249 250 251); }
        .dark .compare-table .section-header td { background: #17171c; }

        @media (max-width: 1279px) {
            /* Below xl the table cannot fit, so it scrolls inside its wrapper
               and the header sticks to the wrapper. */
            .compare-table-wrapper { max-height: 75vh; overflow: auto; }
            .compare-table thead th {
                position: sticky;
                top: 0;
                z-index: 20;
                background: white;
            }
            .dark .compare-table thead th { background: #0f0f14; }
            .compare-table thead th:nth-child(2),
            .compare-table td:nth-child(2) { background: #eff6ff; }
            .dark .compare-table thead th:nth-child(2),
            .dark .compare-table td:nth-child(2) { background: #12121a; }
            .compare-table thead th:first-child { z-index: 30; }
        }

        @media (min-width: 1280px) {
            /* At xl the table fits inside max-w-7xl, so the scroll container
               is dropped entirely. That matters: `overflow-x: auto` makes an
               element a scroll container on BOTH axes, so a sticky thead
               inside it would anchor to a box that never scrolls vertically
               and would never actually stick. With the container gone the
               header sticks to the page instead, below the site header. */
            .compare-table-wrapper { overflow: visible; max-height: none; }
            .compare-table thead th {
                position: sticky;
                top: 4rem;
                z-index: 20;
                background: white;
            }
            .dark .compare-table thead th { background: #0f0f14; }
            .compare-table thead th:nth-child(2) { background: #eff6ff; }
            .dark .compare-table thead th:nth-child(2) { background: #12121a; }
        }

        /* The hero used to carry a pulsing row of check/cross verdict marks
           along its bottom edge. Removed by request - the pulsing read as
           distracting. Do not reintroduce it. */
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- Hero                                                        -->
    <!-- ============================================================ -->
    {{-- Deliberately short. /pricing runs a 46svh hero because "on a pricing
         page the cards are the CTA"; the same holds here - someone arriving
         from a search for "eventbrite alternative" wants the comparison, not
         a screenful of hero. --}}
    <section id="top" class="es-hero relative flex min-h-[calc(58svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-6 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">{{ count($headToHead) }} platforms compared</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Event Schedule</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="es-vs">vs</span> <span class="text-gradient-compare">everyone else</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-8 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Feature by feature against {{ count($headToHead) }} platforms, with their published rates and ours. No platform fees on our side, on any plan.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="#head-to-head" class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Compare your platform
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Start free
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Head to head: the picker                                    -->
    <!-- ============================================================ -->
    {{-- This replaces both the old six-column matrix as lead content and the
         separate "Detailed comparisons" card grid: the chips are the directory
         of all 16 pages, so the same 16 are no longer listed twice. --}}
    <section id="head-to-head" class="relative scroll-mt-24 overflow-hidden bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Which are you <span class="text-gradient-compare">using today?</span>
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    Pick a platform for the short version. Every one has a full comparison behind it.
                </p>
            </div>

            <div class="es-picker" data-reveal="panel">
                <div class="mb-3 flex flex-wrap justify-center gap-2" role="tablist" aria-label="Choose a platform to compare">
                    @foreach ($headToHead as $slug => $c)
                        <button type="button"
                                class="es-chip rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300"
                                role="tab"
                                id="chip-{{ $slug }}"
                                aria-controls="vs-{{ $slug }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                tabindex="{{ $loop->first ? '0' : '-1' }}"
                                @if ($loop->index >= 8) data-overflow="1" @endif>{{ $c['name'] }}</button>
                    @endforeach
                </div>

                <div class="mb-8 text-center">
                    <button type="button" class="es-more items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-500/10 sm:!hidden" aria-expanded="false">
                        <span data-more-label>Show all {{ count($headToHead) }}</span>
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                </div>

                {{-- No data-reveal inside these panels: an element in a hidden
                     panel never intersects, so it would stay at opacity 0 the
                     first time its panel is shown. --}}
                <div class="space-y-10">
                    @foreach ($headToHead as $slug => $c)
                        <div class="es-vs-panel" role="tabpanel" id="vs-{{ $slug }}" aria-labelledby="chip-{{ $slug }}" tabindex="0">
                            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                                <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 border-b border-gray-200 px-4 py-4 dark:border-white/10 sm:px-6">
                                    <div class="text-center">
                                        <div class="text-base font-bold text-gray-900 dark:text-white sm:text-lg">Event Schedule</div>
                                        <div class="es-keep text-xs font-semibold uppercase tracking-wider">0% platform fee</div>
                                    </div>
                                    <div class="es-vs px-2 text-xl sm:text-2xl" aria-hidden="true">vs</div>
                                    <div class="text-center">
                                        <div class="text-base font-bold text-gray-900 dark:text-white sm:text-lg">{{ $c['name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $c['rows'][0]['theirs'] }}</div>
                                    </div>
                                </div>

                                <dl class="divide-y divide-gray-100 dark:divide-white/5">
                                    @foreach ($c['rows'] as $row)
                                        {{-- Rose is only for the platform fee. It was on "Paid plan
                                             price" too, which rendered values like "Free (fees on
                                             tickets)" in red and read as a contradiction. --}}
                                        @php $isMoney = $row['feature'] === 'Platform fees'; @endphp
                                        <div class="grid grid-cols-[1fr_auto_1fr] items-start gap-2 px-4 py-3.5 sm:px-6">
                                            <dd class="text-end text-sm font-medium text-gray-800 dark:text-gray-200">{{ $row['ours'] }}</dd>
                                            <dt class="px-2 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $row['feature'] }}</dt>
                                            <dd class="text-sm {{ $isMoney ? 'es-cost font-semibold' : 'text-gray-600 dark:text-gray-400' }}">{{ $row['theirs'] }}</dd>
                                        </div>
                                    @endforeach
                                </dl>

                                <div class="border-t border-gray-200 px-4 py-4 text-center dark:border-white/10 sm:px-6">
                                    <a href="{{ route($c['route']) }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-blue-600 transition-all hover:gap-3 dark:text-blue-400">
                                        Full Event Schedule <span class="es-vs">vs</span> {{ $c['name'] }} comparison
                                        <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- What it actually costs                                      -->
    <!-- ============================================================ -->
    {{-- Not a duplicate of /pricing's calculator: that one compares us with
         "a typical platform" at a blended rate, this one names real platforms
         at their published rates. The maths is shared so the two cannot
         disagree, and every rate comes from getHubFeeRates(). --}}
    @php
        $calcCards = [
            // Deliberately still a dollar sign, and NOT plan_price(). This row sits inside the
            // fee calculator, whose other rows are competitors' published US pricing; rendering
            // ours in the platform currency would put "R9/mo" beside a "$247.50" saving and
            // compare two different currencies. The calculator is a USD unit or nothing.
            ['key' => 'eventschedule', 'id' => 'fc-es', 'value' => $esCost, 'best' => true, 'note' => '$'.$rates['eventschedule']['monthly'].'/mo + Stripe, 0% platform fee'],
            ['key' => 'eventbrite', 'id' => 'fc-eb', 'value' => $ebCost, 'best' => false, 'note' => $rates['eventbrite']['label']],
            ['key' => 'luma', 'id' => 'fc-luma', 'value' => $lumaCost, 'best' => false, 'note' => $rates['luma']['label']],
            ['key' => 'ticket-tailor', 'id' => 'fc-tt', 'value' => $ttCost, 'best' => false, 'note' => $rates['ticket-tailor']['label']],
        ];
    @endphp
    <section id="fees" class="relative scroll-mt-24 overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    What one event <span class="text-gradient-compare">actually costs</span>
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    Published rates, same event, run the numbers yourself.
                </p>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-10"
                 data-reveal="panel" data-pro-monthly="{{ $rates['eventschedule']['monthly'] }}">

                <div class="mb-10 flex flex-col items-center justify-center gap-6 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <label for="fc-tickets" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Tickets sold</label>
                        <input id="fc-tickets" type="number" value="{{ $calcTickets }}" min="1" max="100000" class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                    <div class="flex items-center gap-3">
                        <label for="fc-price" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Ticket price</label>
                        <div class="relative">
                            <span class="absolute top-1/2 -translate-y-1/2 text-sm text-gray-400 ltr:left-3 rtl:right-3">$</span>
                            <input id="fc-price" type="number" value="{{ $calcPrice }}" min="1" max="10000" class="w-28 rounded-xl border border-gray-200 bg-white py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500 dark:border-white/10 dark:bg-white/5 dark:text-white ltr:pl-7 ltr:pr-3 rtl:pr-7 rtl:pl-3">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" data-reveal-group="70">
                    @foreach ($calcCards as $card)
                        <div data-reveal class="relative flex flex-col rounded-2xl border p-5 {{ $card['best']
                            ? 'border-blue-300 bg-white ring-2 ring-blue-500/25 dark:border-blue-500/40 dark:bg-white/[0.06] dark:ring-blue-400/20'
                            : 'border-gray-200 bg-white dark:border-white/10 dark:bg-white/5' }}">
                            @if ($card['best'])
                                <span class="absolute -top-3 rounded-full bg-gradient-to-r from-blue-600 to-sky-500 px-3 py-1 text-[11px] font-bold uppercase tracking-wide text-white shadow-lg shadow-blue-500/30 ltr:right-4 rtl:left-4">Ours</span>
                            @endif
                            <div class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $rates[$card['key']]['name'] }}</div>
                            <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">{{ $card['note'] }}</div>
                            <div id="{{ $card['id'] }}" class="text-3xl font-black tabular-nums {{ $card['best'] ? 'es-keep' : 'es-cost' }}">${{ number_format($card['value'], 2) }}</div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div id="{{ $card['id'] }}-bar" class="es-bar h-full rounded-full {{ $card['best'] ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ $worstCost > 0 ? max(2, round(($card['value'] / $worstCost) * 100)) : 0 }}%;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 text-center">
                    <p class="text-lg text-gray-700 dark:text-gray-300">
                        On this event you keep up to
                        <span id="fc-savings" class="es-od es-keep mx-1 justify-center align-middle text-4xl font-black tabular-nums sm:text-5xl" data-odometer="${{ number_format($calcSaving, 0) }}">${{ number_format($calcSaving, 0) }}</span>
                        more.
                    </p>
                    <a href="{{ app_url('/sign_up') }}" class="group mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-7 py-3.5 font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                        Start free
                        <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </a>
                    <p class="mx-auto mt-5 max-w-2xl text-xs text-gray-500 dark:text-gray-400">
                        Estimates from each platform's published rates. Stripe processing ({{ $rates['stripe']['label'] }}) is included for Event Schedule, Luma and Ticket Tailor; Eventbrite quotes its fee as inclusive of processing. Ticket Tailor publishes {{ $rates['ticket-tailor']['range'] }} depending on volume, so the midpoint is used here. Luma is shown at whichever of its free and Plus plans is cheaper for the event. Our free plan carries no monthly cost and covers up to 25 paid tickets a month.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- The full matrix, in a disclosure                            -->
    <!-- ============================================================ -->
    @php
        $matrixColumns = ['Event Schedule', 'Eventbrite', 'Luma', 'Ticket Tailor', 'Google Calendar'];
        // Feature rows only. COUNT_RECURSIVE was used here and counted every cell
        // as well as every row, so the button offered "294 rows" of a 42-row table.
        $matrixRowCount = array_sum(array_map('count', $sections));
    @endphp
    <section id="matrix" class="scroll-mt-24 bg-gray-50 py-16 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- Ships open, and the script closes it below md. It must not start
                 closed on desktop: a closed <details> has zero-size boxes, which
                 defeats getBoundingClientRect and any reveal measurement inside. --}}
            <details class="matrix-disc group/matrix" open>
                <summary class="mb-8 flex cursor-pointer flex-col items-center text-center">
                    <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl">
                        The <span class="text-gradient-compare">whole grid</span>
                    </h2>
                    <span class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-600 dark:text-blue-400">
                        <span class="group-open/matrix:hidden">Show all {{ $matrixRowCount }} rows</span>
                        <span class="hidden group-open/matrix:inline">Hide the full grid</span>
                        <svg aria-hidden="true" class="h-4 w-4 transition-transform duration-300 group-open/matrix:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                    </span>
                </summary>

                <div class="compare-table-wrapper rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5"
                     tabindex="0" role="region" aria-label="Full feature comparison, scrollable">
                    <table class="compare-table w-full text-left">
                        <caption class="sr-only">Event Schedule compared with Eventbrite, Luma, Ticket Tailor and Google Calendar across {{ count($sections) }} feature groups</caption>
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10">
                                <th scope="col" class="min-w-[180px] bg-white px-6 py-5 text-sm font-semibold text-gray-900 dark:bg-[#0f0f14] dark:text-white">Feature</th>
                                @foreach ($matrixColumns as $col)
                                    <th scope="col" class="min-w-[160px] px-6 py-5 text-sm font-semibold {{ $loop->first ? 'bg-blue-50/50 text-blue-600 dark:bg-blue-500/5 dark:text-blue-400' : 'text-gray-900 dark:text-white' }}">{{ $col }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($sections as $sectionName => $rows)
                                <tr class="section-header">
                                    <th scope="colgroup" class="bg-gray-50 px-6 py-3 text-start text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                                        {{ $sectionName }}
                                    </th>
                                    {{-- Empty cells rather than a colspan: a colspan breaks the
                                         sticky first column. aria-hidden keeps screen readers
                                         from announcing five blank cells per group. --}}
                                    @foreach ($matrixColumns as $col)
                                        <td class="bg-gray-50 dark:bg-white/[0.03]" aria-hidden="true"></td>
                                    @endforeach
                                </tr>
                                @foreach ($rows as $row)
                                    <tr>
                                        <th scope="row" class="bg-white px-6 py-4 text-start text-sm font-medium text-gray-900 dark:bg-[#0f0f14] dark:text-white">{{ $row[0] }}</th>
                                        @for ($i = 1; $i <= count($matrixColumns); $i++)
                                            <td class="px-6 py-4 text-sm {{ $i === 1 ? 'bg-blue-50/50 dark:bg-blue-500/5' : '' }}">
                                                @if (str_starts_with($row[$i], 'Yes'))
                                                    <span class="inline-flex items-center gap-1.5 {{ $i === 1 ? 'font-medium' : '' }}">
                                                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                        <span class="sr-only">Yes.</span>
                                                        @if (strlen($row[$i]) > 3)
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ substr($row[$i], 4) }}</span>
                                                        @endif
                                                    </span>
                                                @elseif (str_starts_with($row[$i], 'No'))
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                        <span class="sr-only">No.</span>
                                                        @if (strlen($row[$i]) > 2)
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ trim(substr($row[$i], 2)) }}</span>
                                                        @endif
                                                    </span>
                                                @else
                                                    <span class="text-gray-700 dark:text-gray-300">{{ $row[$i] }}</span>
                                                @endif
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Why Event Schedule: the illustrated block                   -->
    <!-- ============================================================ -->
    {{-- These six used to be icon-and-prose tiles in six competing gradients,
         showing nothing. Each now carries a mock built from a primitive that
         already exists, and the gradients collapse to one tint. --}}
    <section id="why" class="relative scroll-mt-24 overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Where we <span class="text-gradient-compare">actually differ</span>
                </h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400" data-reveal style="--reveal-delay: 0.1s;">
                    Six things the grid above does not make obvious.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- 1. Zero platform fees: the bars say it faster than the words -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Zero platform fees</h3>
                        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">We never take a cut of a ticket sale, on any plan. You pay your processor and nothing else.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Event Schedule</span>
                                    <span class="es-keep font-bold">0%</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100 dark:bg-white/10"></div>
                            </div>
                            <div>
                                <div class="mb-1 flex items-center justify-between text-xs">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Typical platform</span>
                                    <span class="es-cost font-bold">3.7% + $1.79</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="es-bar h-full w-[78%] rounded-full bg-rose-500" style="--bd: 0.2s;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 2. Open source: the real star count -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Open source, actually</h3>
                        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">The whole application is public under the Attribution Assurance License. Read it, fork it, or run your own copy.</p>
                        <div class="mt-auto">
                            @include('marketing.partials.github-star-badge')
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 3. Selfhosting: corrected claim, and the stack is the proof -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">The simpler one to selfhost</h3>
                        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Event Schedule and Pretix are the two platforms here you can run yourself. Ours needs standard PHP hosting.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            @foreach ([['Event Schedule', ['PHP', 'MySQL'], true], ['Pretix', ['Docker', 'PostgreSQL', 'Redis'], false]] as [$stackName, $parts, $isOurs])
                                <div>
                                    <div class="mb-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">{{ $stackName }}</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($parts as $part)
                                            <span class="rounded-md border px-2 py-1 font-mono text-[11px] {{ $isOurs
                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
                                                : 'border-gray-200 bg-gray-50 text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400' }}">{{ $part }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 4. AI parsing: the shared paste-in, fields-out primitive -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Paste it, do not type it</h3>
                        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Paste the text of an email or listing, or drop in a flyer image, and the date, time, venue and description are filled in for you.</p>
                        <div class="mt-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-black/20" aria-hidden="true">
                            <div class="mb-3 rounded-lg border border-gray-200 bg-white px-3 py-2 text-[11px] italic text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                                "Friday Night Jazz, Aug 14 at 8pm, The Blue Room"
                            </div>
                            <div class="space-y-1.5">
                                @foreach ([['Event', 'Friday Night Jazz'], ['Date', 'Fri 14 Aug, 8:00 PM'], ['Venue', 'The Blue Room']] as $fi => $field)
                                    <div class="es-ai-field flex items-center justify-between gap-2 text-[11px]" style="--i: {{ $fi }};">
                                        <span class="text-gray-400 dark:text-gray-500">{{ $field[0] }}</span>
                                        <span class="truncate font-medium text-gray-800 dark:text-gray-200">{{ $field[1] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 5. Calendar sync: the two-way pulse -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Two-way calendar sync</h3>
                        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Google Calendar, Outlook and any CalDAV server, syncing both directions, included free on every plan.</p>
                        <div class="mt-auto rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-black/20" aria-hidden="true">
                            <div class="flex items-center justify-between gap-2">
                                <div class="w-[38%] rounded-xl border border-blue-200 bg-blue-50 px-2 py-2.5 text-center dark:border-blue-500/30 dark:bg-blue-500/10">
                                    <div class="text-[11px] font-semibold text-blue-700 dark:text-blue-300">Your schedule</div>
                                </div>
                                <div class="relative h-px flex-1">
                                    <div class="h-px w-full" style="background-image: linear-gradient(to right, rgba(59, 130, 246, 0.5) 45%, transparent 45%); background-size: 7px 1px;"></div>
                                    <span class="es-sync-dot"></span>
                                </div>
                                <div class="w-[38%] rounded-xl border border-gray-200 bg-white px-2 py-2.5 text-center dark:border-white/10 dark:bg-white/5">
                                    <div class="text-[11px] font-semibold text-gray-700 dark:text-gray-300">Their calendar</div>
                                </div>
                            </div>
                            <div class="mt-3 text-center text-[10px] text-gray-400 dark:text-gray-500">changes flow both ways</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- 6. Event graphics: the flyers you already have, composed into one
                     image. The mock used to be a single invented poster, which the
                     generator has no concept of: it lays out the flyer images on your
                     upcoming events, up to 20, and never draws an event from scratch. -->
                <div class="es-bento group relative" data-tilt="3" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <h3 class="mb-2 text-xl font-bold text-gray-900 dark:text-white">Share graphics, generated</h3>
                        <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">The flyers on your next events, up to 20 of them, laid out as one ready-to-post image in your own colours.</p>
                        <div class="mt-auto flex justify-center" aria-hidden="true">
                            <div class="w-32 overflow-hidden rounded-xl border border-gray-200 shadow-md dark:border-white/10">
                                <div class="bg-gradient-to-br from-blue-600 via-sky-600 to-cyan-500 px-3 py-3">
                                    <div class="text-center text-[9px] font-semibold uppercase tracking-[0.2em] text-white/70">This week</div>
                                    <div class="mt-2 grid grid-cols-2 gap-1.5">
                                        @foreach (['Fri 14', 'Sat 15', 'Sat 15', 'Sun 16'] as $tile)
                                            <div class="flex h-10 items-end rounded-md bg-white/20 p-1">
                                                <span class="text-[7px] font-bold leading-none text-white/90">{{ $tile }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="bg-white px-3 py-1.5 text-center text-[8px] font-medium text-gray-400 dark:bg-[#101016] dark:text-gray-500">yourschedule.com</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Switching over (the one dark band)                          -->
    <!-- ============================================================ -->
    {{-- Every claim here maps to a real capability in docs/FEATURES.md:
         Eventbrite import, bulk attendee import from CSV, and backup/restore. --}}
    <section id="switch" class="es-band-dark relative scroll-mt-24 overflow-hidden py-16 lg:py-24 noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(37, 99, 235, 0.28), rgba(37, 99, 235, 0) 62%);"></div>
            <div class="grid-overlay absolute inset-0 opacity-30"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 max-w-2xl">
                <h2 class="es-balance mb-3 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>
                    Moving over is <span class="text-gradient-compare">not a retype</span>
                </h2>
                <p class="text-lg text-gray-300" data-reveal style="--reveal-delay: 0.1s;">
                    Bring the events and the attendees with you, then keep a copy of everything.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-3" data-reveal-group="90">
                @php
                    $switchSteps = [
                        ['Import your events', 'Events come across from Eventbrite directly. Anywhere else, paste the listing text or drop in a flyer image and the AI reads the details out of it.'],
                        ['Bring the attendees', 'Existing attendees import in bulk from a CSV, up to 5,000 rows at a time, so your lists arrive intact.'],
                        ['Keep your own copy', 'Backup and restore is built in, images included. Export whenever you like, selfhost it, or walk away with everything.'],
                    ];
                @endphp
                @foreach ($switchSteps as $sIndex => [$sTitle, $sBody])
                    <div data-reveal class="rounded-2xl border border-white/10 bg-white/[0.04] p-6">
                        <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-500/20 text-sm font-bold text-blue-300">{{ $sIndex + 1 }}</div>
                        <h3 class="mb-2 font-semibold text-white">{{ $sTitle }}</h3>
                        <p class="text-sm text-gray-400">{{ $sBody }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mt-8 text-sm" data-reveal>
                <a href="{{ route('marketing.replace') }}" class="group inline-flex items-center gap-2 font-medium text-blue-300 transition-all hover:gap-3 hover:text-blue-200">
                    Replacing a workaround rather than a platform?
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ                                                         -->
    <!-- ============================================================ -->
    <section id="faq" class="scroll-mt-24 bg-white py-16 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>
                    Frequently asked <span class="text-gradient-compare">questions</span>
                </h2>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/[0.04] sm:p-6" data-reveal="panel">
                <div class="space-y-3">
                    @foreach ($compareFaqs as $faq)
                        <details name="faq" class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-colors hover:border-blue-300 dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-blue-400/40">
                            <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 sm:p-6">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white sm:text-lg">{{ $faq['q'] }}</h3>
                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <p class="faq-answer px-5 pb-5 text-gray-600 dark:text-gray-400 sm:px-6 sm:pb-6">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- Finale                                                      -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-compare">switch?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Free forever on the free plan, and no platform fees on any of them.
                    </p>
                    <div class="flex flex-col flex-wrap items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get started free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                        <a href="{{ marketing_url('/pricing') }}" class="inline-flex items-center justify-center rounded-2xl border-2 border-white/30 px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-white/10">
                            See the plans
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-marketing.related-pages />

    <!-- Section dot navigation (desktop) -->
    @php
        $dotSections = [
            ['top', 'Top'],
            ['head-to-head', 'Head to head'],
            ['fees', 'What it costs'],
            ['matrix', 'The whole grid'],
            ['why', 'Where we differ'],
            ['switch', 'Switching over'],
            ['faq', 'FAQ'],
            ['claim', 'Get started'],
        ];
    @endphp
    <nav class="es-dotnav fixed top-1/2 z-40 hidden -translate-y-1/2 lg:block ltr:right-5 rtl:left-5" aria-label="Page sections">
        <ul class="glass flex flex-col items-center gap-1.5 rounded-full px-2 py-3">
            @foreach ($dotSections as [$sectionId, $sectionLabel])
                <li class="relative">
                    <a href="#{{ $sectionId }}" class="es-dot group block rounded-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500" aria-label="{{ $sectionLabel }}">
                        <span class="es-dot-pip block h-2 w-2 rounded-full bg-gray-400/60 dark:bg-white/30"></span>
                        <span class="pointer-events-none absolute top-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-gray-200 bg-white px-3 py-1 text-xs font-medium text-gray-700 opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 group-focus-visible:opacity-100 ltr:right-full ltr:mr-3 rtl:left-full rtl:ml-3 dark:border-white/10 dark:bg-[#15151c] dark:text-gray-300">{{ $sectionLabel }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <!-- Platform picker, matrix disclosure and fee calculator (no inline handlers) -->
    <script {!! nonce_attr() !!}>
        (function () {
            var wrap = document.querySelector('.es-picker');
            if (!wrap) return;

            var chips = Array.prototype.slice.call(wrap.querySelectorAll('[role="tab"]'));
            var panels = Array.prototype.slice.call(wrap.querySelectorAll('[role="tabpanel"]'));
            var more = wrap.querySelector('.es-more');
            if (chips.length !== panels.length || !chips.length) return;

            function select(index, focus) {
                chips.forEach(function (chip, i) {
                    var active = i === index;
                    chip.setAttribute('aria-selected', active ? 'true' : 'false');
                    chip.setAttribute('tabindex', active ? '0' : '-1');
                    if (active && focus) chip.focus();
                });
                panels.forEach(function (panel, i) {
                    if (i === index) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', '');
                    }
                });
            }

            chips.forEach(function (chip, i) {
                chip.addEventListener('click', function () {
                    wrap.classList.remove('is-kbd');
                    select(i, false);
                });
                chip.addEventListener('keydown', function (e) {
                    var next = null;
                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = (i + 1) % chips.length;
                    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') next = (i - 1 + chips.length) % chips.length;
                    if (e.key === 'Home') next = 0;
                    if (e.key === 'End') next = chips.length - 1;
                    if (next === null) return;
                    e.preventDefault();
                    // Arrow keys move focus programmatically, which Chrome does not
                    // reliably treat as :focus-visible, so flag the keyboard state.
                    wrap.classList.add('is-kbd');
                    select(next, true);
                });
            });

            if (more) {
                more.addEventListener('click', function () {
                    var expanded = wrap.classList.toggle('is-expanded');
                    more.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    var label = more.querySelector('[data-more-label]');
                    if (label) label.textContent = expanded ? 'Show fewer' : 'Show all ' + chips.length;
                });
            }

            // Only now do panels start hiding: without JS all sixteen stay visible.
            wrap.classList.add('is-ready');
            select(0, false);
        })();

        (function () {
            // The full grid ships open so its boxes are measurable; collapse it
            // on small screens where 42 rows is not a reasonable default.
            var disc = document.querySelector('details.matrix-disc');
            if (!disc || !window.matchMedia) return;
            var mq = window.matchMedia('(max-width: 767px)');
            function sync() {
                if (mq.matches) { disc.removeAttribute('open'); } else { disc.setAttribute('open', ''); }
            }
            if (mq.addEventListener) { mq.addEventListener('change', sync); }
            sync();
        })();

        (function () {
            var ticketsEl = document.getElementById('fc-tickets');
            var priceEl = document.getElementById('fc-price');
            if (!ticketsEl || !priceEl) return;

            var panel = ticketsEl.closest('[data-pro-monthly]');
            // The fallback is rendered from config rather than written as a literal. A hardcoded
            // number here survived the price raise and kept quoting the old $5.
            var proMonthly = parseFloat(panel && panel.getAttribute('data-pro-monthly')) || {{ $proMonthly }};

            var out = {
                es: document.getElementById('fc-es'),
                eb: document.getElementById('fc-eb'),
                luma: document.getElementById('fc-luma'),
                tt: document.getElementById('fc-tt'),
                saving: document.getElementById('fc-savings'),
            };

            function fmt(n) { return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
            function fmt0(n) { return '$' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

            // Same rates the server rendered with, from getHubFeeRates().
            function calc() {
                var tickets = parseFloat(ticketsEl.value) || 0;
                var price = parseFloat(priceEl.value) || 0;
                var revenue = tickets * price;
                var stripe = (revenue * 0.029) + (tickets * 0.30);

                var es = proMonthly + stripe;
                var eb = (revenue * 0.037) + (tickets * 1.79);
                var luma = Math.min((revenue * 0.05) + stripe, 59 + stripe);
                var tt = (tickets * 0.44) + stripe;

                var worst = Math.max(eb, luma, tt);
                var saving = Math.max(0, worst - es);

                if (out.es) out.es.textContent = fmt(es);
                if (out.eb) out.eb.textContent = fmt(eb);
                if (out.luma) out.luma.textContent = fmt(luma);
                if (out.tt) out.tt.textContent = fmt(tt);
                if (out.saving) out.saving.textContent = fmt0(saving);

                [['fc-es', es], ['fc-eb', eb], ['fc-luma', luma], ['fc-tt', tt]].forEach(function (pair) {
                    var bar = document.getElementById(pair[0] + '-bar');
                    if (bar) bar.style.width = (worst > 0 ? Math.max(2, Math.round((pair[1] / worst) * 100)) : 0) + '%';
                });
            }

            ticketsEl.addEventListener('input', calc);
            priceEl.addEventListener('input', calc);
        })();
    </script>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
