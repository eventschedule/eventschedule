<x-marketing-layout>
    <x-slot name="title">{{ $name }} Alternative | Event Schedule vs {{ $name }}</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $name }} Alternative</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule",
        "description": "Open-source event management platform for sharing events, selling tickets, and bringing communities together. Zero platform fees.",
        "url": "{{ config('app.url') }}",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "offers": [
            {
                "@type": "Offer",
                "name": "Free",
                "price": "0",
                "priceCurrency": "USD",
                "description": "Unlimited events, team collaboration, Google Calendar sync, newsletters, and fan engagement features.",
                "availability": "https://schema.org/InStock"
            },
            {
                "@type": "Offer",
                "name": "Pro",
                "price": "5.00",
                "priceCurrency": "USD",
                "description": "Everything in Free plus ticketing with QR check-ins and live dashboard, ticket waitlist, sale notifications, sales CSV export, Stripe payments, remove branding, event graphics, REST API, and webhooks.",
                "availability": "https://schema.org/InStock"
            }
        ],
        "featureList": [
            "Zero platform fees on ticket sales",
            "AI-powered event import",
            "Two-way Google Calendar sync",
            "Newsletter builder with A/B testing",
            "QR code ticketing and check-in",
            "Check-in dashboard",
            "Ticket waitlist",
            "Sale notification emails",
            "Sales CSV export",
            "Open source with selfhosting option",
            "Embeddable calendar widget",
            "Fan videos and comments",
            "Team collaboration"
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            @foreach ($faq as $index => $item)
            {
                "@type": "Question",
                "name": "{{ str_replace('"', '\\"', $item['question']) }}",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "{{ str_replace('"', '\\"', $item['answer']) }}"
                }
            }@if (!$loop->last),@endif

            @endforeach
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
        .dark .compare-table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
        }
        @media (max-width: 768px) {
            .compare-table th:first-child,
            .compare-table td:first-child {
                position: sticky;
                left: 0;
                z-index: 10;
            }
            .compare-table th:first-child {
                z-index: 20;
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
            <!-- Breadcrumb -->
            <div class="mb-6">
                <a href="{{ route('marketing.compare') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    &larr; Compare all platforms
                </a>
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Platform comparison</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Event Schedule<br>
                <span class="text-gradient">vs {{ $name }}</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto animate-reveal delay-200" style="opacity: 0;">
                {{ $tagline }}
            </p>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    @if (!empty($auto_import))
    <!-- Auto-Import Section -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] pt-24 pb-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column -->
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 text-sm font-medium mb-6">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Import from {{ $name }}
                    </span>

                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ $auto_import['title'] }}
                    </h2>

                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8">
                        {{ $auto_import['description'] }}
                    </p>

                    <ul class="space-y-3">
                        @foreach ($auto_import['bullets'] as $bullet)
                        <li class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $bullet }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Right Column: Steps -->
                <div class="space-y-4">
                    @foreach ($auto_import['steps'] as $index => $step)
                    <div class="relative flex items-start gap-5 bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ $step['title'] }}</h3>
                            <p class="text-gray-600 dark:text-gray-400">{{ $step['description'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Comparison Table -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="compare-table-wrapper rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5">
                <table class="compare-table w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="bg-white dark:bg-[#0f0f14] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[180px]">Feature</th>
                            <th class="px-6 py-5 text-sm font-semibold text-blue-600 dark:text-blue-400 min-w-[180px] bg-blue-50/50 dark:bg-blue-500/5">
                                Event Schedule
                            </th>
                            <th class="px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[180px]">{{ $name }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($sections as $sectionName => $rows)
                            <tr>
                                <td colspan="3" class="bg-gray-50 dark:bg-white/[0.03] px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ $sectionName }}
                                </td>
                            </tr>
                            @foreach ($rows as $row)
                                @php
                                    $esWins = $row[3] ?? false;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="bg-white dark:bg-[#0f0f14] px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $row[0] }}</td>
                                    <td class="px-6 py-4 text-sm {{ $esWins ? 'bg-emerald-50/50 dark:bg-emerald-500/5' : 'bg-blue-50/50 dark:bg-blue-500/5' }}">
                                        @if (str_starts_with($row[1], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5 font-medium">
                                                <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[1]) > 3)
                                                    <span class="text-emerald-600/70 dark:text-emerald-400/70 text-xs">{{ substr($row[1], 4) }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-300">{{ $row[1] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if (str_starts_with($row[2], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[2]) > 3)
                                                    <span class="text-emerald-600/70 dark:text-emerald-400/70 text-xs">{{ substr($row[2], 4) }}</span>
                                                @endif
                                            </span>
                                        @elseif (str_starts_with($row[2], 'No'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                @if (strlen($row[2]) > 2)
                                                    <span class="text-gray-400 dark:text-gray-500 text-xs">{{ trim(substr($row[2], 2)) }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-300">{{ $row[2] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Where Event Schedule Shines -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Where Event Schedule Shines
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Key advantages over {{ $name }} that make Event Schedule a strong alternative.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
                @foreach ($key_advantages as $advantage)
                    <div class="bg-gradient-to-br {{ $advantage['gradient'] }} rounded-2xl p-8 border {{ $advantage['border'] }}">
                        <div class="w-12 h-12 rounded-xl {{ $advantage['icon_bg'] }} flex items-center justify-center mb-5">
                            <x-marketing-icon :icon="$advantage['icon']" :class="'w-6 h-6 ' . $advantage['icon_color']" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $advantage['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $advantage['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- About Competitor -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- About the competitor -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        About {{ $name }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        {{ $about }}
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $name }}'s strengths</h3>
                    <ul class="space-y-3">
                        @foreach ($competitor_strengths as $strength)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $strength }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Why switch to ES -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        Why choose Event Schedule?
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Event Schedule offers a unique combination of features that no other platform matches: zero platform fees, open source transparency, and powerful AI tools.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">Zero platform fees on all ticket sales, at any plan level</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">Fully open source with selfhosting option for complete control</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">AI-powered event parsing, flyer generation, and automatic graphics</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">Two-way Google Calendar and CalDAV sync included free</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-white h-24"></div>

    <!-- How to Switch -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How to switch in 3 steps
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Get started in minutes. No migration or data import needed.
                </p>
            </div>

            @php
                $steps = $switch_steps ?? [
                    ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
                    ['title' => 'Add your events', 'description' => 'Paste event details for AI import or create events manually.'],
                    ['title' => 'Share and sell', 'description' => 'Share your schedule URL and start selling tickets.'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                @foreach ($steps as $index => $step)
                    <div class="text-center">
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mx-auto mb-5">
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $index + 1 }}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $step['title'] }}</h3>
                        <p class="text-gray-500 dark:text-gray-400">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- FAQ Section -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Common questions about switching from {{ $name }}.
                </p>
            </div>

            @php
                $faqColors = [
                    ['gradient' => 'from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900', 'border' => 'border-blue-200'],
                    ['gradient' => 'from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900', 'border' => 'border-emerald-200'],
                    ['gradient' => 'from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900', 'border' => 'border-amber-200'],
                ];
            @endphp

            <div class="space-y-4" x-data="{ open: null }">
                @foreach ($faq as $index => $item)
                    <div class="bg-gradient-to-br {{ $faqColors[$index % 3]['gradient'] }} rounded-2xl border {{ $faqColors[$index % 3]['border'] }} dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = open === {{ $index + 1 }} ? null : {{ $index + 1 }}" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $item['question'] }}
                            </h3>
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === {{ $index + 1 }} }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open === {{ $index + 1 }}" x-collapse>
                            <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                                {{ $item['answer'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-white h-24"></div>

    <!-- Cross-links -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Also compare with
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-3xl mx-auto">
                @foreach ($cross_links as $link)
                    <a href="{{ route($link['route']) }}" class="group flex items-center justify-between p-6 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule vs</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $link['name'] }}</div>
                        </div>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
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
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
