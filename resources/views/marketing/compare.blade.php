<x-marketing-layout>
    <x-slot name="title">Compare Event Schedule vs Eventbrite, Luma & More</x-slot>
    <x-slot name="description">Compare Event Schedule with Eventbrite, Luma, Meetup, and 13 more platforms. Feature-by-feature breakdowns, a fee calculator, and zero platform fees.</x-slot>
    <x-slot name="breadcrumbTitle">Compare</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": ["WebPage", "ItemPage"],
        "name": "Compare Event Schedule with 16+ Event Platforms",
        "description": "Compare Event Schedule with Eventbrite, Luma, Ticket Tailor, Google Calendar, Meetup, and 11 more platforms. Feature-by-feature comparisons, fee calculator, and zero platform fees.",
        "url": "{{ config('app.url') }}/compare",
        "mainEntity": {
            "@type": "SoftwareApplication",
            "name": "Event Schedule",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": ["Web", "Android", "iOS"]
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How does Event Schedule pricing compare to other platforms?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule starts at $5/month for Pro with zero platform fees. Competitors like Eventbrite charge 3.7% + $1.79 per ticket, Luma charges 5% or $59/month, and Ticket Tailor charges per-ticket fees. You only pay standard Stripe processing (2.9% + $0.30)."
                }
            },
            {
                "@type": "Question",
                "name": "Does Event Schedule charge platform fees on ticket sales?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Event Schedule charges zero platform fees at every plan level. You keep 100% of your ticket revenue minus standard Stripe payment processing fees (2.9% + $0.30 per transaction)."
                }
            },
            {
                "@type": "Question",
                "name": "Can I import events from other platforms to Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule supports auto-import from Eventbrite, transferring event details, ticket types, venues, and images automatically. For other platforms, you can use AI event parsing to paste event details in any format and have them extracted automatically."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule open source?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is fully open source and can be selfhosted on your own server. No other major event platform in this comparison offers both open source code and a selfhosting option."
                }
            },
            {
                "@type": "Question",
                "name": "What features does Event Schedule have that competitors don't?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Event Schedule uniquely offers AI event parsing and flyer generation, two-way Google Calendar and CalDAV sync, automatic event graphics generation, sub-schedules for organizing events, fan videos and comments, carpool matching, and WhatsApp event creation. It is also the only platform in its class that is fully open source with a selfhosting option."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-compare {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-compare {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-compare {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .compare-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .compare-table-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        .compare-table-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        .compare-table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        /* Sticky first column (feature names) - works at all screen sizes */
        .compare-table th:first-child,
        .compare-table td:first-child {
            position: sticky;
            left: 0;
            z-index: 10;
            background: white;
        }
        .dark .compare-table th:first-child,
        .dark .compare-table td:first-child {
            background: #0f0f14;
        }
        /* Section header rows need correct background for sticky */
        .compare-table .section-header td {
            background: rgb(249 250 251);
        }
        .dark .compare-table .section-header td {
            background: #17171c;
        }
        @media (max-width: 768px) {
            /* Make wrapper handle both scrolls with fixed height */
            .compare-table-wrapper {
                max-height: 70vh;
                overflow: auto;
            }
            /* Sticky header row (app names) when scrolling down */
            .compare-table thead th {
                position: sticky;
                top: 0;
                z-index: 20;
                background: white;
            }
            .dark .compare-table thead th {
                background: #0f0f14;
            }
            /* Keep Event Schedule column highlight - solid colors for scroll */
            .compare-table thead th:nth-child(2) {
                background: #eff6ff; /* solid bg-blue-50 */
            }
            .dark .compare-table thead th:nth-child(2) {
                background: #12121a; /* solid dark blue tint */
            }
            /* Event Schedule data cells also need solid background */
            .compare-table td:nth-child(2) {
                background: #eff6ff; /* solid bg-blue-50 */
            }
            .dark .compare-table td:nth-child(2) {
                background: #12121a; /* solid dark blue tint */
            }
            /* Corner cell needs highest z-index */
            .compare-table thead th:first-child {
                z-index: 30;
            }
        }

        /* Signature motif: a row of comparison verdict marks (check vs cross) */
        .es-verdict {
            flex: 0 0 auto;
            animation: es-verdict-pulse var(--vd-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--vd-delay, 0s);
        }
        @keyframes es-verdict-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-verdict, .animate-pulse-slow { animation: none !important; }
            .es-verdict { opacity: 0.55; transform: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- 1. Hero                                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(68svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Verdict motif along the bottom edge (check vs cross) -->
            <div class="es-verdicts absolute bottom-6 left-0 right-0 mx-auto hidden h-14 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 14; $i++)
                    @php $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.28; $win = $i % 3 !== 2; $sz = [20, 26, 18, 24][$i % 4]; @endphp
                    <span class="es-verdict {{ $win ? 'text-emerald-500' : 'text-gray-400 dark:text-gray-600' }}" style="--vd-dur: {{ $dur }}s; --vd-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            @if ($win)
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            @endif
                        </svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Platform comparison</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">How we</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-compare">compare</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                See how Event Schedule stacks up against Eventbrite, Luma, Ticket Tailor, and Google Calendar.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Comparison Table + Fee Calculator                        -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div data-reveal class="compare-table-wrapper rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">
                <table class="compare-table w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="min-w-[180px] bg-white px-6 py-5 text-sm font-semibold text-gray-900 dark:bg-[#0f0f14] dark:text-white">Feature</th>
                            <th class="min-w-[160px] bg-blue-50/50 px-6 py-5 text-sm font-semibold text-blue-600 dark:bg-blue-500/5 dark:text-blue-400">
                                Event Schedule
                            </th>
                            <th class="min-w-[160px] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white">Eventbrite</th>
                            <th class="min-w-[160px] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white">Luma</th>
                            <th class="min-w-[160px] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white">Ticket Tailor</th>
                            <th class="min-w-[160px] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white">Google Calendar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($sections as $sectionName => $rows)
                            <tr class="section-header">
                                <td class="bg-gray-50 px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                                    {{ $sectionName }}
                                </td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                            </tr>
                            @foreach ($rows as $row)
                                <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="bg-white px-6 py-4 text-sm font-medium text-gray-900 dark:bg-[#0f0f14] dark:text-white">{{ $row[0] }}</td>
                                    @for ($i = 1; $i <= 5; $i++)
                                    <td class="px-6 py-4 text-sm {{ $i === 1 ? 'bg-blue-50/50 dark:bg-blue-500/5' : '' }}">
                                        @if (str_starts_with($row[$i], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5 {{ $i === 1 ? 'font-medium' : '' }}">
                                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[$i]) > 3)
                                                    <span class="text-xs text-emerald-600/70 dark:text-emerald-400/70">{{ substr($row[$i], 4) }}</span>
                                                @endif
                                            </span>
                                        @elseif (str_starts_with($row[$i], 'No'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg aria-hidden="true" class="h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                @if (strlen($row[$i]) > 2)
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ trim(substr($row[$i], 2)) }}</span>
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

            <!-- Fee Calculator (vanilla JS, no Vue compiler dependency) -->
            <div id="fee-calculator" class="mt-16">
                <div class="mb-8 text-center">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white md:text-3xl">Fee Calculator</h3>
                    <p class="mt-3 text-gray-500 dark:text-gray-400">See how much you'd pay each month on each platform.</p>
                </div>

                <!-- Inputs -->
                <div class="mb-10 flex flex-col items-center justify-center gap-6 sm:flex-row">
                    <div class="flex items-center gap-3">
                        <label for="fc-price" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Ticket price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 dark:text-gray-500">$</span>
                            <input id="fc-price" type="number" value="10" min="1" max="10000" class="w-28 rounded-xl border border-gray-200 bg-white py-2.5 pl-7 pr-3 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-white">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label for="fc-tickets" class="whitespace-nowrap text-sm font-medium text-gray-700 dark:text-gray-300">Tickets per month</label>
                        <input id="fc-tickets" type="number" value="100" min="1" max="100000" class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-[var(--brand-blue)] dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>

                <!-- Results Grid -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="relative rounded-2xl border-2 border-blue-500 bg-blue-50/50 p-6 dark:border-blue-400 dark:bg-blue-500/5">
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-blue-500 px-3 py-0.5 text-xs font-semibold text-white">Best value</span>
                        <div class="mb-1 text-sm font-semibold text-blue-600 dark:text-blue-400">Event Schedule</div>
                        <div id="fc-es" class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">$0.00</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">$5/mo + Stripe fees</div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <div class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Eventbrite</div>
                        <div id="fc-eb" class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">$0.00</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">3.7% + $1.79/ticket</div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <div class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Luma</div>
                        <div id="fc-luma" class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">$0.00</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <span id="fc-luma-free">Free plan (5% + Stripe)</span>
                            <span id="fc-luma-plus" class="hidden">Plus plan ($59/mo + Stripe)</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
                        <div class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">Ticket Tailor</div>
                        <div id="fc-tt" class="mb-2 text-3xl font-bold text-gray-900 dark:text-white">$0.00</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">~$0.41/ticket + Stripe fees</div>
                    </div>
                </div>

                <!-- Savings Summary -->
                <div id="fc-savings-wrap" class="mt-6 text-center" style="display: none;">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-5 py-2.5 dark:border-emerald-500/20 dark:bg-emerald-500/10">
                        <svg aria-hidden="true" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                            You save up to <span id="fc-savings" class="font-bold">$0.00</span>/month with Event Schedule
                        </span>
                    </div>
                </div>

                <p class="mt-4 text-center text-xs text-gray-400 dark:text-gray-500">
                    Stripe fees (2.9% + $0.30/ticket) apply to Event Schedule, Luma, and Ticket Tailor. Eventbrite includes payment processing in their fees.
                </p>
            </div>
            <script {!! nonce_attr() !!}>
            (function () {
                var priceEl = document.getElementById('fc-price');
                var ticketsEl = document.getElementById('fc-tickets');
                if (!priceEl || !ticketsEl) return;
                function fmt(n) { return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
                function calc() {
                    var price = parseFloat(priceEl.value) || 0;
                    var tickets = parseFloat(ticketsEl.value) || 0;
                    var revenue = price * tickets;
                    var esTotal = 5 + (revenue * 0.029) + (tickets * 0.30);
                    var ebTotal = (revenue * 0.037) + (tickets * 1.79);
                    var lumaFree = (revenue * 0.05) + (revenue * 0.029) + (tickets * 0.30);
                    var lumaPlus = 59 + (revenue * 0.029) + (tickets * 0.30);
                    var lumaTotal = Math.min(lumaFree, lumaPlus);
                    var lumaIsFree = lumaFree <= lumaPlus;
                    var ttTotal = (tickets * 0.41) + (revenue * 0.029) + (tickets * 0.30);
                    var maxSavings = Math.max(ebTotal - esTotal, lumaTotal - esTotal, ttTotal - esTotal);
                    document.getElementById('fc-es').textContent = fmt(esTotal);
                    document.getElementById('fc-eb').textContent = fmt(ebTotal);
                    document.getElementById('fc-luma').textContent = fmt(lumaTotal);
                    document.getElementById('fc-tt').textContent = fmt(ttTotal);
                    document.getElementById('fc-luma-free').classList.toggle('hidden', !lumaIsFree);
                    document.getElementById('fc-luma-plus').classList.toggle('hidden', lumaIsFree);
                    document.getElementById('fc-savings').textContent = fmt(maxSavings);
                    document.getElementById('fc-savings-wrap').style.display = maxSavings > 0 ? '' : 'none';
                }
                priceEl.addEventListener('input', calc);
                ticketsEl.addEventListener('input', calc);
                calc();
            })();
            </script>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Detailed Comparisons                                     -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Detailed <span class="text-gradient-compare">comparisons</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Dive deeper into how Event Schedule compares with each platform.</p>
            </div>

            @php
                $comparisons = [
                    ['marketing.compare_eventbrite', 'Eventbrite', '0% platform fees vs 3.7% + $1.79/ticket. Plus custom domains, AI features, and open source.'],
                    ['marketing.compare_luma', 'Luma', 'From $5/mo vs $59/mo for comparable features. Zero platform fees and fully open source.'],
                    ['marketing.compare_ticket_tailor', 'Ticket Tailor', 'From $5/mo vs per-ticket fees. Plus calendar sync, newsletters, and selfhosting.'],
                    ['marketing.compare_google_calendar', 'Google Calendar', 'Purpose-built event platform vs personal scheduling tool. Ticketing, public pages, and AI features.'],
                    ['marketing.compare_meetup', 'Meetup', 'Free forever vs $14.99+/mo organizer fees. Custom domains, full branding, and open source.'],
                    ['marketing.compare_dice', 'DICE', 'Zero platform fees vs buyer service fees. Full page control, calendar sync, and open source.'],
                    ['marketing.compare_brown_paper_tickets', 'Brown Paper Tickets', 'Modern, reliable platform vs dated design. Zero fees, AI features, and active development.'],
                    ['marketing.compare_splash', 'Splash', '$5/mo vs enterprise pricing. Instant setup, AI features, and open source flexibility.'],
                    ['marketing.compare_sched', 'Sched', '$5/mo vs $50+/mo. Zero platform fees, calendar sync, and AI features included.'],
                    ['marketing.compare_whova', 'Whova', 'Transparent $5/mo vs custom quotes. Zero platform fees, open source, and full API access.'],
                    ['marketing.compare_tito', 'Tito', '$5/mo flat vs 3% per ticket. Calendar sync, newsletters, and selfhosting included.'],
                    ['marketing.compare_pretix', 'Pretix', 'Both open source, but Event Schedule adds calendar sync, newsletters, AI, and flat pricing.'],
                    ['marketing.compare_accelevents', 'Accelevents', '$5/mo vs $7,500+/year. Instant setup, zero fees, and open source flexibility.'],
                    ['marketing.compare_addevent', 'AddEvent', 'Complete event platform with ticketing vs calendar buttons only. $5/mo vs $29/mo.'],
                    ['marketing.compare_humanitix', 'Humanitix', '$5/mo flat vs 5% + $1.29/ticket. Zero platform fees, calendar sync, AI, and open source.'],
                    ['marketing.compare_eventzilla', 'Eventzilla', '$5/mo flat vs per-ticket fees. Zero platform fees, calendar sync, AI, and open source.'],
                ];
            @endphp

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4" data-reveal-group="60">
                @foreach ($comparisons as $c)
                    <a href="{{ route($c[0]) }}" data-reveal class="group flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-8 transition-all hover:-translate-y-1 hover:border-blue-300 hover:bg-blue-50/50 hover:shadow-lg dark:border-white/10 dark:bg-white/5 dark:hover:border-blue-500/30 dark:hover:bg-blue-500/5">
                        <div class="mb-2 text-sm text-gray-500 dark:text-gray-400">Event Schedule vs</div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">{{ $c[1] }}</h3>
                        <p class="mb-4 flex-grow text-sm text-gray-500 dark:text-gray-400">{{ $c[2] }}</p>
                        <span class="mt-auto inline-flex items-center text-sm font-medium text-blue-600 transition-all group-hover:gap-2 dark:text-blue-400">
                            Compare
                            <svg aria-hidden="true" class="ml-1 h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Cross-link to Replace -->
            <div class="mx-auto mt-16 max-w-3xl rounded-2xl border border-gray-200 bg-gray-50 p-8 text-center dark:border-white/10 dark:bg-white/5" data-reveal>
                <p class="mb-4 text-gray-600 dark:text-gray-400">
                    Using general-purpose tools for events? See what Event Schedule can replace.
                </p>
                <a href="{{ route('marketing.replace') }}" class="inline-flex items-center gap-2 font-medium text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                    View tools we replace
                    <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Why Event Schedule                                       -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Why <span class="text-gradient-compare">Event Schedule?</span></h2>
                <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Key advantages that set us apart from the competition.</p>
            </div>

            @php
                $advantages = [
                    ['from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30', 'border-emerald-200 dark:border-emerald-500/20', 'bg-emerald-100 dark:bg-emerald-500/20', 'text-emerald-600 dark:text-emerald-400', '0% Platform Fees', 'We never take a cut of your ticket sales. You only pay standard Stripe processing fees (2.9% + $0.30).', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30', 'border-blue-200 dark:border-blue-500/20', 'bg-blue-100 dark:bg-blue-500/20', 'text-blue-600 dark:text-blue-400', 'Open Source', 'Fully open source and selfhostable. No vendor lock-in. Your data stays yours, always.', 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                    ['from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30', 'border-sky-200 dark:border-sky-500/20', 'bg-blue-100 dark:bg-blue-500/20', 'text-blue-600 dark:text-blue-400', 'Self-Hosting', 'Deploy on your own server for complete control. No other platform in this comparison offers that.', 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01'],
                    ['from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30', 'border-sky-200 dark:border-sky-500/20', 'bg-sky-100 dark:bg-sky-500/20', 'text-sky-600 dark:text-sky-400', 'AI Event Parsing', 'Paste event details in any format and our AI extracts dates, times, locations, and descriptions automatically.', 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                    ['from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30', 'border-amber-200 dark:border-amber-500/20', 'bg-amber-100 dark:bg-amber-500/20', 'text-amber-600 dark:text-amber-400', 'Calendar Sync', 'Two-way Google Calendar and CalDAV sync included free. No other platform offers both.', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['from-cyan-50 to-blue-50 dark:from-cyan-900/30 dark:to-blue-900/30', 'border-cyan-200 dark:border-cyan-500/20', 'bg-cyan-100 dark:bg-cyan-500/20', 'text-cyan-600 dark:text-cyan-400', 'Event Graphics', 'Generate shareable event graphics automatically. No design skills required, unique to Event Schedule.', 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ];
            @endphp

            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @foreach ($advantages as $adv)
                    <div data-reveal class="rounded-2xl border bg-gradient-to-br p-8 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg {{ $adv[0] }} {{ $adv[1] }}">
                        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl {{ $adv[2] }}">
                            <svg aria-hidden="true" class="h-6 w-6 {{ $adv[3] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $adv[6] }}" />
                            </svg>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">{{ $adv[4] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $adv[5] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-compare">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about Event Schedule and how it compares.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['How does Event Schedule pricing compare to other platforms?', 'Event Schedule starts at $5/month for Pro with zero platform fees. Competitors like Eventbrite charge 3.7% + $1.79 per ticket, Luma charges 5% or $59/month, and Ticket Tailor charges per-ticket fees. You only pay standard Stripe processing (2.9% + $0.30).'],
                        ['Does Event Schedule charge platform fees on ticket sales?', 'No. Event Schedule charges zero platform fees at every plan level. You keep 100% of your ticket revenue minus standard Stripe payment processing fees (2.9% + $0.30 per transaction).'],
                        ['Can I import events from other platforms to Event Schedule?', 'Yes. Event Schedule supports auto-import from Eventbrite, transferring event details, ticket types, venues, and images automatically. For other platforms, you can use AI event parsing to paste event details in any format and have them extracted automatically.'],
                        ['Is Event Schedule open source?', 'Yes. Event Schedule is fully open source and can be selfhosted on your own server. No other major event platform in this comparison offers both open source code and a selfhosting option.'],
                        ['What features does Event Schedule have that competitors don\'t?', 'Event Schedule uniquely offers AI event parsing and flyer generation, two-way Google Calendar and CalDAV sync, automatic event graphics generation, sub-schedules for organizing events, fan videos and comments, carpool matching, and WhatsApp event creation. It is also the only platform in its class that is fully open source with a selfhosting option.'],
                    ];
                @endphp
                @foreach ($faqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Finale                                                   -->
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
                        Create your free schedule today. No credit card required, no platform fees ever.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
