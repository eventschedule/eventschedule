<x-marketing-layout>
    <x-slot name="title">{{ $short_name ?? $name }} Replacement for Events | Event Schedule</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ $short_name ?? $name }} Replacement</x-slot>

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
            },
            {
                "@type": "Offer",
                "name": "Enterprise",
                "price": "15.00",
                "priceCurrency": "USD",
                "description": "Everything in Pro plus AI style generation, AI content generation, AI flyer generation, WhatsApp event creation, custom CSS, white-label branding, and priority support.",
                "availability": "https://schema.org/InStock"
            }
        ],
        "featureList": [
            "Zero platform fees on ticket sales",
            "AI-powered event import",
            "AI flyer generation",
            "AI style generation",
            "Two-way Google Calendar sync",
            "CalDAV sync",
            "iCal download",
            "Newsletter builder with A/B testing",
            "QR code ticketing and check-in",
            "Check-in dashboard",
            "Ticket waitlist",
            "Promo and discount codes",
            "Sale notification emails",
            "Sales CSV export",
            "Open source with selfhosting option",
            "Embeddable calendar and ticket widgets",
            "WhatsApp event creation",
            "Custom CSS styling",
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
    @php
        $howToSteps = $switch_steps ?? [
            ['title' => 'Create your schedule', 'description' => 'Sign up free and create your first schedule in under a minute.'],
            ['title' => 'Add your events', 'description' => 'Paste event details for AI import or create events manually.'],
            ['title' => 'Share and sell', 'description' => 'Share your schedule URL and start selling tickets.'],
        ];
    @endphp
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to switch from {{ str_replace('"', '\\"', $name) }} to Event Schedule",
        "step": [
            @foreach ($howToSteps as $index => $step)
            {
                "@type": "HowToStep",
                "position": {{ $index + 1 }},
                "name": "{{ str_replace('"', '\\"', $step['title']) }}",
                "text": "{{ str_replace('"', '\\"', $step['description']) }}"
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
    <div class="section-fade-to-white h-24"></div>

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
                            <x-marketing-icon :icon="$solution['icon']" :class="'w-6 h-6 ' . $solution['icon_color']" />
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
                        {{ $why_switch['intro'] ?? 'Event Schedule offers a unique combination of features that no other platform matches: zero platform fees, open source transparency, and powerful AI tools.' }}
                    </p>
                    <ul class="space-y-3">
                        @foreach (($why_switch['points'] ?? ['Zero platform fees on all ticket sales, at any plan level', 'Fully open source with selfhosting option for complete control', 'AI-powered event parsing, flyer generation, and automatic graphics', 'Two-way Google Calendar and CalDAV sync included free']) as $point)
                            <li class="flex items-start gap-3">
                                <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $point }}</span>
                            </li>
                        @endforeach
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

            <div id="faq-accordion" class="space-y-4">
                @foreach ($faq as $index => $item)
                    <div class="bg-gradient-to-br {{ $faqColors[$index % 3]['gradient'] }} rounded-2xl border {{ $faqColors[$index % 3]['border'] }} dark:border-white/10 shadow-sm overflow-hidden">
                        <button @click="open = open === {{ $index + 1 }} ? null : {{ $index + 1 }}" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $item['question'] }}
                            </h3>
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4 faq-chevron" :class="{ 'rotate-180': open === {{ $index + 1 }} }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-show="open === {{ $index + 1 }}" class="faq-answer">
                            <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                                {{ $item['answer'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <script {!! nonce_attr() !!}>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.Vue) {
                    window.Vue.createApp({
                        data() {
                            return { open: null };
                        },
                        mounted() {
                            this.$el.classList.add('vue-ready');
                        }
                    }).mount('#faq-accordion');
                }
            });
            </script>
            <style {!! nonce_attr() !!}>
                #faq-accordion:not(.vue-ready) .faq-answer { display: block !important; }
                #faq-accordion:not(.vue-ready) .faq-chevron { display: none; }
                #faq-accordion:not(.vue-ready) button { cursor: default; }
            </style>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-white h-24"></div>

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

    @if (!empty($related_features))
    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- Related Features -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Explore related features
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-3xl mx-auto">
                @foreach ($related_features as $feature)
                    <a href="{{ route($feature['route']) }}" class="group flex flex-col p-6 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-2">{{ $feature['name'] }}</div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto">{{ $feature['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Transition -->
    <div class="section-fade-to-{{ !empty($related_features) ? 'white' : 'gray' }} h-24"></div>

    <!-- Cross-link to Compare -->
    <section class="bg-{{ !empty($related_features) ? 'white dark:bg-[#0a0a0f]' : 'gray-100 dark:bg-[#0f0f14]' }} py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Looking for direct platform comparisons?
            </h2>
            <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">
                See how Event Schedule compares to other event platforms like Eventbrite, Luma, and Ticket Tailor.
            </p>
            <a href="{{ route('marketing.compare') }}" class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium text-lg transition-colors">
                View platform comparisons
                <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to replace {{ $name }}?
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
