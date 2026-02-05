<x-marketing-layout>
    <x-slot name="title">Event Schedule vs Eventbrite, Luma, Ticket Tailor & Google Calendar | Comparison</x-slot>
    <x-slot name="description">Compare Event Schedule with Eventbrite, Luma, Ticket Tailor, and Google Calendar. See how our 0% platform fees, open source platform, and AI features stack up.</x-slot>
    <x-slot name="keywords">event schedule comparison, eventbrite alternative, luma alternative, ticket tailor alternative, google calendar alternative, free event platform, no platform fees ticketing</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">Compare</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": ["WebPage", "ItemPage"],
        "name": "Event Schedule vs Eventbrite, Luma, Ticket Tailor & Google Calendar",
        "description": "Compare Event Schedule with Eventbrite, Luma, Ticket Tailor, and Google Calendar. See how our 0% platform fees, open source platform, and AI features stack up.",
        "url": "{{ config('app.url') }}/compare",
        "mainEntity": {
            "@type": "SoftwareApplication",
            "name": "Event Schedule",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": ["Web", "Android", "iOS"]
        }
    }
    </script>
    </x-slot>

    <style>
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
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Platform comparison</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                How we<br>
                <span class="text-gradient">compare</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto">
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
                        @php
                            $sections = [
                                'Pricing & Fees' => [
                                    ['Free plan', 'Yes (forever)', 'Free to publish events', 'Yes (forever)', 'Free events only', 'Yes (forever)'],
                                    ['Paid plan price', '$5/mo (first year free)', 'Free (fees on tickets)', '$59/mo', 'From $0.28/ticket', 'Free'],
                                    ['Platform fees', '0%', '3.7% + $1.79/ticket', '5% (free plan), 0% (Plus)', '$0.28-$0.60/ticket', 'N/A (no ticketing)'],
                                    ['Payment processing', 'Stripe (2.9% + $0.30)', 'Built-in (included above)', 'Built-in', 'Stripe/PayPal/Square', 'No'],
                                ],
                                'Events & Ticketing' => [
                                    ['Ticketing', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                                    ['QR check-ins', 'Yes (Pro)', 'Yes', 'Yes', 'Yes', 'No'],
                                    ['Online events', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'No'],
                                ],
                                'Integrations' => [
                                    ['Google Calendar sync', 'Yes (Free)', 'No native 2-way sync', 'Yes', 'No', 'N/A'],
                                    ['CalDAV sync', 'Yes (Free)', 'No', 'No', 'No', 'No'],
                                    ['Newsletters', 'Yes (Free)', 'Yes', 'Yes', 'No', 'No'],
                                ],
                                'Customization' => [
                                    ['Custom domains', 'Yes (Pro)', 'No', 'Yes (Plus)', 'Yes (paid)', 'No'],
                                    ['Remove branding', 'Yes (Pro)', 'No', 'Yes (Plus)', 'Yes (paid)', 'No'],
                                    ['Custom fields', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'No'],
                                    ['Built-in analytics', 'Yes (Free)', 'Yes', 'Yes', 'Yes', 'No'],
                                ],
                                'Unique Features' => [
                                    ['AI event parsing (incl. agenda scanning)', 'Yes (Pro)', 'No', 'No', 'No', 'No'],
                                    ['Event graphics gen', 'Yes (Pro)', 'No', 'No', 'No', 'No'],
                                    ['Sub-schedules', 'Yes (Free)', 'No', 'No', 'No', 'Multiple calendars'],
                                    ['Fan videos & comments', 'Yes (Free)', 'No', 'No', 'No', 'No'],
                                    ['Team collaboration', 'Yes (Free)', 'Yes', '3 admins (free), 5 (Plus)', 'Yes', 'Yes'],
                                ],
                                'Platform' => [
                                    ['REST API', 'Yes (Pro)', 'Yes', 'Yes (Plus)', 'Yes', 'Yes'],
                                    ['Open source', 'Yes', 'No', 'No', 'No', 'No'],
                                    ['Selfhosting', 'Yes', 'No', 'No', 'No', 'No'],
                                ],
                            ];
                        @endphp
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
                                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[$i]) > 3)
                                                    <span class="text-emerald-600/70 dark:text-emerald-400/70 text-xs">{{ substr($row[$i], 4) }}</span>
                                                @endif
                                            </span>
                                        @elseif (str_starts_with($row[$i], 'No'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="{{ route('marketing.compare_eventbrite') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Eventbrite</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">0% platform fees vs 3.7% + $1.79/ticket. Plus custom domains, AI features, and open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_luma') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Luma</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">$5/mo vs $59/mo for comparable features. Zero platform fees and fully open source.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </a>

                <a href="{{ route('marketing.compare_ticket_tailor') }}" class="group p-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50/50 dark:hover:bg-blue-500/5 transition-all flex flex-col">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Event Schedule vs</div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3">Ticket Tailor</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex-grow">Flat $5/mo vs per-ticket fees. Plus calendar sync, newsletters, and selfhosting.</p>
                    <span class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:gap-2 transition-all mt-auto">
                        Compare
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <svg class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
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
                        <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">0% Platform Fees</h3>
                    <p class="text-gray-600 dark:text-gray-400">We never take a cut of your ticket sales. You only pay standard Stripe processing fees (2.9% + $0.30).</p>
                </div>

                <!-- Open Source -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-50 dark:from-blue-900/30 dark:to-blue-900/30 rounded-2xl p-8 border border-blue-200 dark:border-blue-500/20">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Open Source</h3>
                    <p class="text-gray-600 dark:text-gray-400">Fully open source and selfhostable. No vendor lock-in. Your data stays yours, always.</p>
                </div>

                <!-- Self-Hosting -->
                <div class="bg-gradient-to-br from-sky-50 to-blue-50 dark:from-sky-900/30 dark:to-blue-900/30 rounded-2xl p-8 border border-sky-200 dark:border-sky-500/20">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Self-Hosting</h3>
                    <p class="text-gray-600 dark:text-gray-400">Deploy on your own server for complete control. No other platform in this comparison offers that.</p>
                </div>

                <!-- AI Features -->
                <div class="bg-gradient-to-br from-sky-50 to-cyan-50 dark:from-sky-900/30 dark:to-cyan-900/30 rounded-2xl p-8 border border-sky-200 dark:border-sky-500/20">
                    <div class="w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">AI Event Parsing</h3>
                    <p class="text-gray-600 dark:text-gray-400">Paste event details in any format and our AI extracts dates, times, locations, and descriptions automatically.</p>
                </div>

                <!-- Calendar Sync -->
                <div class="bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/30 dark:to-yellow-900/30 rounded-2xl p-8 border border-amber-200 dark:border-amber-500/20">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Calendar Sync</h3>
                    <p class="text-gray-600 dark:text-gray-400">Two-way Google Calendar and CalDAV sync included free. No other platform offers both.</p>
                </div>

                <!-- Event Graphics -->
                <div class="bg-gradient-to-br from-rose-50 to-cyan-50 dark:from-rose-900/30 dark:to-cyan-900/30 rounded-2xl p-8 border border-rose-200 dark:border-rose-500/20">
                    <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Event Graphics</h3>
                    <p class="text-gray-600 dark:text-gray-400">Generate shareable event graphics automatically. No design skills required, unique to Event Schedule.</p>
                </div>
            </div>
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
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
