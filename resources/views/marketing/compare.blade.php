<x-marketing-layout>
    <x-slot name="title">Compare Event Schedule | vs Eventbrite, Luma, Meetup & 13 More</x-slot>
    <x-slot name="description">Compare Event Schedule with Eventbrite, Luma, Ticket Tailor, Google Calendar, Meetup, and 11 more platforms. Feature-by-feature comparisons, fee calculator, and zero platform fees.</x-slot>
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
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Platform comparison</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                How we<br>
                <span class="text-gradient">compare</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto animate-reveal delay-200" style="opacity: 0;">
                See how Event Schedule stacks up against Eventbrite, Luma, Ticket Tailor, and Google Calendar.
            </p>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- Comparison Table -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="compare-table-wrapper rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5">
                <table class="compare-table w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="bg-white dark:bg-[#0f0f14] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[180px]">Feature</th>
                            <th class="px-6 py-5 text-sm font-semibold text-blue-600 dark:text-blue-400 min-w-[160px] bg-blue-50/50 dark:bg-blue-500/5">
                                Event Schedule
                            </th>
                            <th class="px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[160px]">Eventbrite</th>
                            <th class="px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[160px]">Luma</th>
                            <th class="px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[160px]">Ticket Tailor</th>
                            <th class="px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[160px]">Google Calendar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($sections as $sectionName => $rows)
                            <tr class="section-header">
                                <td class="bg-gray-50 dark:bg-white/[0.03] px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ $sectionName }}
                                </td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                                <td class="bg-gray-50 dark:bg-white/[0.03]"></td>
                            </tr>
                            @foreach ($rows as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="bg-white dark:bg-[#0f0f14] px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $row[0] }}</td>
                                    @for ($i = 1; $i <= 5; $i++)
                                    <td class="px-6 py-4 text-sm {{ $i === 1 ? 'bg-blue-50/50 dark:bg-blue-500/5' : '' }}">
                                        @if (str_starts_with($row[$i], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5 {{ $i === 1 ? 'font-medium' : '' }}">
                                                <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[$i]) > 3)
                                                    <span class="text-emerald-600/70 dark:text-emerald-400/70 text-xs">{{ substr($row[$i], 4) }}</span>
                                                @endif
                                            </span>
                                        @elseif (str_starts_with($row[$i], 'No'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                @if (strlen($row[$i]) > 2)
                                                    <span class="text-gray-400 dark:text-gray-500 text-xs">{{ trim(substr($row[$i], 2)) }}</span>
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

            <!-- Fee Calculator -->
            <div id="fee-calculator" class="mt-16">
                <div class="text-center mb-8">
                    <h3 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-3">Fee Calculator</h3>
                    <p class="text-gray-500 dark:text-gray-400">See how much you'd pay each month on each platform.</p>
                </div>

                <!-- Inputs -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 mb-10">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Ticket price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-sm">$</span>
                            <input type="number" v-model.number="price" min="1" max="10000" class="w-28 pl-7 pr-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-transparent">
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Tickets per month</label>
                        <input type="number" v-model.number="tickets" min="1" max="100000" class="w-28 px-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-[var(--brand-blue)] focus:border-transparent">
                    </div>
                </div>

                <!-- Results Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Event Schedule -->
                    <div class="relative rounded-2xl border-2 border-blue-500 dark:border-blue-400 bg-blue-50/50 dark:bg-blue-500/5 p-6">
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-blue-500 text-white text-xs font-semibold whitespace-nowrap">Best value</span>
                        <div class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-1">Event Schedule</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2" v-text="fmt(esTotal)"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">$5/mo + Stripe fees</div>
                    </div>

                    <!-- Eventbrite -->
                    <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Eventbrite</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2" v-text="fmt(ebTotal)"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">3.7% + $1.79/ticket</div>
                    </div>

                    <!-- Luma -->
                    <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Luma</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2" v-text="fmt(lumaTotal)"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <span v-show="lumaIsFree">Free plan (5% + Stripe)</span>
                            <span v-show="!lumaIsFree">Plus plan ($59/mo + Stripe)</span>
                        </div>
                    </div>

                    <!-- Ticket Tailor -->
                    <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Ticket Tailor</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2" v-text="fmt(ttTotal)"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">~$0.41/ticket + Stripe fees</div>
                    </div>
                </div>

                <!-- Savings Summary -->
                <div v-show="maxSavings > 0" class="mt-6 text-center">
                    <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20">
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                            You save up to <span class="font-bold" v-text="fmt(maxSavings)"></span>/month with Event Schedule
                        </span>
                    </div>
                </div>

                <p class="text-xs text-gray-400 dark:text-gray-500 text-center mt-4">
                    Stripe fees (2.9% + $0.30/ticket) apply to Event Schedule, Luma, and Ticket Tailor. Eventbrite includes payment processing in their fees.
                </p>
            </div>
            <script {!! nonce_attr() !!}>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.Vue) {
                    window.Vue.createApp({
                        data() {
                            return {
                                price: 10,
                                tickets: 100,
                            };
                        },
                        computed: {
                            revenue() { return this.price * this.tickets; },
                            esTotal() { return 5 + (this.revenue * 0.029) + (this.tickets * 0.30); },
                            ebTotal() { return (this.revenue * 0.037) + (this.tickets * 1.79); },
                            lumaFree() { return (this.revenue * 0.05) + (this.revenue * 0.029) + (this.tickets * 0.30); },
                            lumaPlus() { return 59 + (this.revenue * 0.029) + (this.tickets * 0.30); },
                            lumaTotal() { return Math.min(this.lumaFree, this.lumaPlus); },
                            lumaIsFree() { return this.lumaFree <= this.lumaPlus; },
                            ttTotal() { return (this.tickets * 0.41) + (this.revenue * 0.029) + (this.tickets * 0.30); },
                            maxSavings() { return Math.max(this.ebTotal - this.esTotal, this.lumaTotal - this.esTotal, this.ttTotal - this.esTotal); },
                        },
                        methods: {
                            fmt(n) { return '$' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); },
                        },
                    }).mount('#fee-calculator');
                }
            });
            </script>
        </div>
    </section>

    <!-- Detailed Comparisons -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Detailed Comparisons
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Dive deeper into how Event Schedule compares with each platform.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <a href="{{ route('marketing.compare_eventbrite') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Eventbrite</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">0% platform fees vs 3.7% + $1.79/ticket. Plus custom domains, AI features, and open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_luma') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Luma</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">From $5/mo vs $59/mo for comparable features. Zero platform fees and fully open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_ticket_tailor') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Ticket Tailor</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">From $5/mo vs per-ticket fees. Plus calendar sync, newsletters, and selfhosting.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_google_calendar') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Google Calendar</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Purpose-built event platform vs personal scheduling tool. Ticketing, public pages, and AI features.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <a href="{{ route('marketing.compare_meetup') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Meetup</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Free forever vs $14.99+/mo organizer fees. Custom domains, full branding, and open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_dice') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">DICE</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Zero platform fees vs buyer service fees. Full page control, calendar sync, and open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_brown_paper_tickets') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Brown Paper Tickets</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Modern, reliable platform vs dated design. Zero fees, AI features, and active development.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_splash') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Splash</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">$5/mo vs enterprise pricing. Instant setup, AI features, and open source flexibility.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <a href="{{ route('marketing.compare_sched') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Sched</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">$5/mo vs $50+/mo. Zero platform fees, calendar sync, and AI features included.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_whova') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Whova</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Transparent $5/mo vs custom quotes. Zero platform fees, open source, and full API access.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_tito') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Tito</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">$5/mo flat vs 3% per ticket. Calendar sync, newsletters, and selfhosting included.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_pretix') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Pretix</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Both open source, but Event Schedule adds calendar sync, newsletters, AI, and flat pricing.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('marketing.compare_accelevents') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Accelevents</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">$5/mo vs $7,500+/year. Instant setup, zero fees, and open source flexibility.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_addevent') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">AddEvent</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Complete event platform with ticketing vs calendar buttons only. $5/mo vs $29/mo.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_humanitix') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Humanitix</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">$5/mo flat vs 5% + $1.29/ticket. Zero platform fees, calendar sync, AI, and open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_eventzilla') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Eventzilla</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">$5/mo flat vs per-ticket fees. Zero platform fees, calendar sync, AI, and open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg aria-hidden="true" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>
            </div>

            <!-- Cross-link to Replace -->
            <div class="max-w-3xl mx-auto mt-16 text-center p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                <p class="text-gray-600 dark:text-gray-400 mb-4">
                    Using general-purpose tools for events? See what Event Schedule can replace.
                </p>
                <a href="{{ route('marketing.replace') }}" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors">
                    View tools we replace
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- Why Event Schedule -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Why Event Schedule?
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Key advantages that set us apart from the competition.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- 0% Platform Fees -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-2xl p-8 border border-emerald-200 dark:border-emerald-500/20">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">0% Platform Fees</h3>
                    <p class="text-gray-600 dark:text-gray-400">We never take a cut of your ticket sales. You only pay standard Stripe processing fees (2.9% + $0.30).</p>
                </div>

                <!-- Open Source -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30 rounded-2xl p-8 border border-blue-200 dark:border-blue-500/20">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Open Source</h3>
                    <p class="text-gray-600 dark:text-gray-400">Fully open source and selfhostable. No vendor lock-in. Your data stays yours, always.</p>
                </div>

                <!-- Self-Hosting -->
                <div class="bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-8 border border-sky-200 dark:border-sky-500/20">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Self-Hosting</h3>
                    <p class="text-gray-600 dark:text-gray-400">Deploy on your own server for complete control. No other platform in this comparison offers that.</p>
                </div>

                <!-- AI Features -->
                <div class="bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30 rounded-2xl p-8 border border-sky-200 dark:border-sky-500/20">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">AI Event Parsing</h3>
                    <p class="text-gray-600 dark:text-gray-400">Paste event details in any format and our AI extracts dates, times, locations, and descriptions automatically.</p>
                </div>

                <!-- Calendar Sync -->
                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30 rounded-2xl p-8 border border-amber-200 dark:border-amber-500/20">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Calendar Sync</h3>
                    <p class="text-gray-600 dark:text-gray-400">Two-way Google Calendar and CalDAV sync included free. No other platform offers both.</p>
                </div>

                <!-- Event Graphics -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-8 border border-rose-200 dark:border-rose-500/20">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center mb-5">
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Event Graphics</h3>
                    <p class="text-gray-600 dark:text-gray-400">Generate shareable event graphics automatically. No design skills required, unique to Event Schedule.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-white h-24"></div>

    <!-- FAQ Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Common questions about Event Schedule and how it compares.
                </p>
            </div>

            <div id="compare-faq" class="space-y-4">
                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How does Event Schedule pricing compare to other platforms?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="open === 1" class="faq-answer">
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Event Schedule starts at $5/month for Pro with zero platform fees. Competitors like Eventbrite charge 3.7% + $1.79 per ticket, Luma charges 5% or $59/month, and Ticket Tailor charges per-ticket fees. You only pay standard Stripe processing (2.9% + $0.30).
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Does Event Schedule charge platform fees on ticket sales?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="open === 2" class="faq-answer">
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            No. Event Schedule charges zero platform fees at every plan level. You keep 100% of your ticket revenue minus standard Stripe payment processing fees (2.9% + $0.30 per transaction).
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 rounded-2xl border border-amber-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can I import events from other platforms to Event Schedule?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="open === 3" class="faq-answer">
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule supports auto-import from Eventbrite, transferring event details, ticket types, venues, and images automatically. For other platforms, you can use AI event parsing to paste event details in any format and have them extracted automatically.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Is Event Schedule open source?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="open === 4" class="faq-answer">
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule is fully open source and can be selfhosted on your own server. No other major event platform in this comparison offers both open source code and a selfhosting option.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 5 ? null : 5" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What features does Event Schedule have that competitors don't?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 5 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div v-show="open === 5" class="faq-answer">
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Event Schedule uniquely offers AI event parsing and flyer generation, two-way Google Calendar and CalDAV sync, automatic event graphics generation, sub-schedules for organizing events, fan videos and comments, carpool matching, and WhatsApp event creation. It is also the only platform in its class that is fully open source with a selfhosting option.
                        </p>
                    </div>
                </div>
            </div>
            <script {!! nonce_attr() !!}>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.Vue) {
                    window.Vue.createApp({
                        data() {
                            return { open: null };
                        }
                    }).mount('#compare-faq');
                }
            });
            </script>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to switch?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your free schedule today. No credit card required, no platform fees ever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
