<x-marketing-layout>
    <x-slot name="title">Replace {{ $name }} for Events | {{ $name }} Replacement</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="breadcrumbTitle">Replace {{ $name }}</x-slot>

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
                <a href="{{ route('marketing.replace') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    &larr; All tools
                </a>
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Replace your workaround</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Replace <span class="text-gradient">{{ $name }}</span><br>
                for Events
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto animate-reveal delay-200" style="opacity: 0;">
                {{ $tagline }}
            </p>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- Pain Points -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Why {{ $name }} falls short for events
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    {{ $name }} was not built for event management. Here is where it leaves you stuck.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                @foreach ($pain_points as $pain)
                    <div class="flex items-start gap-4 p-6 rounded-2xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10">
                        <svg aria-hidden="true" class="w-6 h-6 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">{{ $pain }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-from-gray h-24"></div>

    <!-- Solutions -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    What Event Schedule gives you instead
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Purpose-built features that replace the workarounds.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
                @foreach ($es_solutions as $solution)
                    <div class="bg-gradient-to-br {{ $solution['gradient'] }} rounded-2xl p-8 border {{ $solution['border'] }}">
                        <div class="w-12 h-12 rounded-xl {{ $solution['icon_bg'] }} flex items-center justify-center mb-5">
                            @if ($solution['icon'] === 'dollar')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif ($solution['icon'] === 'globe')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            @elseif ($solution['icon'] === 'ai')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            @elseif ($solution['icon'] === 'code')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            @elseif ($solution['icon'] === 'calendar')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @elseif ($solution['icon'] === 'image')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @elseif ($solution['icon'] === 'mail')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            @elseif ($solution['icon'] === 'ticket')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                            @elseif ($solution['icon'] === 'chart')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            @elseif ($solution['icon'] === 'qr')
                                <svg aria-hidden="true" class="w-6 h-6 {{ $solution['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $solution['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $solution['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- About Tool -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- About the tool -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        About {{ $name }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        {{ $about }}
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $name }}'s strengths</h3>
                    <ul class="space-y-3">
                        @foreach ($tool_strengths as $strength)
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
                        Why switch to Event Schedule?
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
    <div class="section-fade-from-gray h-24"></div>

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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mx-auto mb-5">
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">1</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create your schedule</h3>
                    <p class="text-gray-500 dark:text-gray-400">Sign up free and create your first schedule in under a minute.</p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mx-auto mb-5">
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">2</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your events</h3>
                    <p class="text-gray-500 dark:text-gray-400">Paste event details for AI import or create events manually.</p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center mx-auto mb-5">
                        <span class="text-lg font-bold text-blue-600 dark:text-blue-400">3</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share and sell</h3>
                    <p class="text-gray-500 dark:text-gray-400">Share your schedule URL and start selling tickets.</p>
                </div>
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
    <div class="section-fade-from-gray h-24"></div>

    <!-- Cross-links -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Also replace
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-3xl mx-auto">
                @foreach ($cross_links as $link)
                    <a href="{{ route($link['route']) }}" class="group flex items-center justify-between p-6 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Replace</div>
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
