<x-marketing-layout>
    <x-slot name="title">Privacy-First Event Analytics | Track Views & Conversions - Event Schedule</x-slot>
    <x-slot name="description">Track page views, device breakdown, traffic sources, and conversion rates. Privacy-first analytics with no external services required.</x-slot>
    <x-slot name="breadcrumbTitle">Analytics</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Analytics",
        "description": "Track page views, device breakdown, traffic sources, and conversion rates. Privacy-first analytics with no external services required.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Analytics"
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Custom emerald gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 50%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient {
            background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #6ee7b7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* Page-specific animations */
        @keyframes count-up {
            0% { opacity: 0; transform: translateY(10px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes bar-grow {
            0% { width: 0%; }
            100% { width: var(--bar-width); }
        }
        .animate-count { animation: count-up 0.5s ease-out forwards; }
        .animate-bar { animation: bar-grow 1s ease-out forwards; }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Built-in Analytics</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Event<br>
                <span class="text-gradient">analytics</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Track page views, device breakdown, traffic sources, and conversion rates. No external services required.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-emerald-500/25">
                    Get started free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-gray-500 dark:text-gray-400 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ route('marketing.docs.analytics') }}" class="underline hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">Read the Analytics guide</a>
            </p>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Analytics at a glance</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Views Over Time (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                                Views Over Time
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Track your growth</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">See daily, weekly, or monthly view counts. Choose from 7 time ranges: last 7/30/90 days, this month, last month, this year, or all time.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Last 7 days</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Last 30 days</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">This year</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Chart mockup -->
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-64">
                                    <div class="flex items-end justify-between h-32 gap-2">
                                        <div class="w-6 bg-emerald-500/40 rounded-t" style="height: 40%"></div>
                                        <div class="w-6 bg-emerald-500/50 rounded-t" style="height: 55%"></div>
                                        <div class="w-6 bg-emerald-500/60 rounded-t" style="height: 45%"></div>
                                        <div class="w-6 bg-emerald-500/70 rounded-t" style="height: 70%"></div>
                                        <div class="w-6 bg-emerald-500/80 rounded-t" style="height: 60%"></div>
                                        <div class="w-6 bg-emerald-500/90 rounded-t" style="height: 85%"></div>
                                        <div class="w-6 bg-emerald-500 rounded-t" style="height: 100%"></div>
                                    </div>
                                    <div class="flex justify-between mt-2 text-xs text-gray-400 dark:text-gray-400">
                                        <span>Mon</span>
                                        <span>Wed</span>
                                        <span>Fri</span>
                                        <span>Sun</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Device Breakdown -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Device Breakdown
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Know your devices</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">See how visitors access your schedule: desktop, mobile, or tablet. Bot traffic is automatically filtered.</p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-cyan-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm flex-1">Mobile</span>
                            <span class="text-gray-900 dark:text-white font-medium">62%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm flex-1">Desktop</span>
                            <span class="text-gray-900 dark:text-white font-medium">35%</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-sky-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm flex-1">Tablet</span>
                            <span class="text-gray-900 dark:text-white font-medium">3%</span>
                        </div>
                    </div>
                </div>

                <!-- Traffic Sources -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Traffic Sources
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Where they come from</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Track direct visits, search engines, social media, email campaigns, and referrer domains.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-400 rounded-full" style="width: 45%"></div>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs w-16">Direct</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-400 rounded-full" style="width: 30%"></div>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs w-16">Social</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-sky-400 rounded-full" style="width: 15%"></div>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs w-16">Search</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-cyan-400 rounded-full" style="width: 10%"></div>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 text-xs w-16">Email</span>
                        </div>
                    </div>
                </div>

                <!-- Conversion Tracking (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Conversion Tracking
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Measure what matters</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Track revenue per event, conversion rates, and revenue per view. See which events perform best.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">4.2%</div>
                                <div class="text-amber-400 text-sm">Conversion Rate</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">$2.40</div>
                                <div class="text-amber-400 text-sm">Revenue/View</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">$1,248</div>
                                <div class="text-amber-400 text-sm">Total Revenue</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">52</div>
                                <div class="text-amber-400 text-sm">Tickets Sold</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Additional Features -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    More insights
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything you need to understand your audience.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Top Events -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-emerald-200 dark:border-emerald-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Top Events</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">See which events get the most views and drive the most ticket sales.</p>
                </div>

                <!-- Multi-Schedule -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-emerald-200 dark:border-emerald-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Multi-Schedule</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Filter analytics by schedule or view combined data across all your schedules.</p>
                </div>

                <!-- Privacy First -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-emerald-200 dark:border-emerald-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-500/20 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Privacy First</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No external tracking services. All data stored locally. No cookies required.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bot Filtering Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Accurate data only
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    We filter out bot traffic so you see real visitors.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Bot Filtering -->
                <div class="bg-gray-100 dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-red-500/20 mb-4">
                        <svg aria-hidden="true" class="w-8 h-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Bot Detection</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">60+ bot patterns filtered automatically. Googlebot, Bingbot, crawlers, and scrapers are excluded from your stats.</p>
                </div>

                <!-- Real Humans -->
                <div class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-2xl p-6 border border-emerald-400/30 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-500/20 mb-4">
                        <svg aria-hidden="true" class="w-8 h-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Real Visitors</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Only human visitors count. Get accurate data you can trust for making decisions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Recurring Events page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-lime-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-green-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <a href="{{ marketing_url('/features/recurring-events') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900 dark:to-green-900 rounded-3xl border border-lime-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-lime-600 dark:group-hover:text-lime-300 transition-colors">Recurring Events</h3>
                        <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Set events to repeat weekly on chosen days with flexible end conditions and per-occurrence tickets.</p>
                        <span class="inline-flex items-center text-lime-500 dark:text-lime-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 mb-6">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Venues</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-curators') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Curators</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-bars') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Bars</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-emerald-600 to-teal-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Understand your audience
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start tracking your event analytics today. No setup required.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-emerald-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Analytics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Analytics Software",
        "operatingSystem": "Web",
        "description": "Privacy-first event analytics. Track page views, device breakdown, traffic sources, and conversion rates with no external services required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Page view tracking",
            "Device breakdown",
            "Traffic source analysis",
            "Conversion tracking",
            "Privacy-first approach",
            "No external services"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
