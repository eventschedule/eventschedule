<x-marketing-layout>
    <x-slot name="title">Event Schedule for Theaters | Ticketing & Show Calendar Software</x-slot>
    <x-slot name="description">Sell out every performance. Manage show runs from opening night to closing curtain, sell tickets with zero platform fees, and email your patrons directly. Built for community theaters, regional playhouses, and performing arts centers.</x-slot>
    <x-slot name="keywords">theater ticketing software, theater box office system, playhouse calendar, performing arts ticketing, theater show management, community theater software, regional theater ticketing, show run management</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Theaters</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Theaters",
        "description": "Manage show runs from opening night to closing curtain, sell tickets with zero platform fees, and email your patrons directly.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Theaters"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section with Theater Marquee Effect -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 lg:py-32 overflow-hidden">
        <!-- Stage curtain glow effect -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[400px] bg-gradient-to-b from-rose-600/25 via-rose-600/10 to-transparent blur-[80px]"></div>
            <div class="absolute top-10 left-1/4 w-[200px] h-[200px] bg-amber-500/15 rounded-full blur-[60px]"></div>
            <div class="absolute top-10 right-1/4 w-[200px] h-[200px] bg-amber-500/15 rounded-full blur-[60px]"></div>
        </div>

        <!-- Marquee lights effect - decorative dots along the top -->
        <div class="absolute top-0 left-0 right-0 h-2 flex justify-center gap-4 overflow-hidden">
            <div class="flex gap-4">
                @for ($i = 0; $i < 30; $i++)
                    <div class="w-2 h-2 rounded-full bg-amber-400/80 animate-pulse" style="animation-delay: {{ $i * 0.1 }}s;"></div>
                @endfor
            </div>
        </div>

        <div class="absolute inset-0 grid-pattern bg-[size:60px_60px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-rose-500/10 border border-gray-200 dark:border-white/10 mb-6">
                <svg aria-hidden="true" class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                </svg>
                <span class="text-sm text-rose-700 dark:text-rose-300">For Theaters & Performing Arts Venues</span>
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                Sell out every show.<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-400 via-red-400 to-amber-400">Build your audience.</span>
            </h1>

            <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-8">
                From opening night to closing curtain. Sell tickets directly, manage show runs, and email your patrons without paying for ads.
            </p>

            <!-- Genre tags -->
            <div class="flex flex-wrap justify-center gap-2 mb-10">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm">Drama</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 text-sm">Musical</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm">Comedy</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm">Dance</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 text-sm">Opera</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm">Improv</span>
            </div>

            <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-rose-600 to-red-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-rose-500/25">
                Create your theater's calendar
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Season Tickets & Show Runs (spans 2 cols) - HERO FEATURE -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-red-100 dark:from-rose-900 dark:to-red-900 border border-rose-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Show Runs
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Opening night to closing curtain</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Set up your production once with all performance dates. Thursday preview, weekend matinees, final Sunday - all linked together. Patrons see the full run at a glance.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Show runs</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Season passes</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Multi-performance</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-rose-950 to-red-950 rounded-2xl border border-rose-400/30 p-4 max-w-xs">
                                    <div class="text-center mb-3">
                                        <div class="text-gray-900 dark:text-white font-semibold">Hamlet</div>
                                        <div class="text-rose-300 text-sm">March 2024 Run</div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-amber-500/20 border border-amber-400/30">
                                            <div class="text-amber-300 text-xs font-mono w-12">Mar 7</div>
                                            <span class="text-gray-900 dark:text-white text-sm">Preview</span>
                                            <span class="ml-auto text-amber-300 text-xs">$25</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-rose-500/20 border border-rose-400/30">
                                            <div class="text-rose-300 text-xs font-mono w-12">Mar 8</div>
                                            <span class="text-gray-900 dark:text-white text-sm">Opening Night</span>
                                            <span class="ml-auto text-rose-300 text-xs">$45</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-12">Mar 9</div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Sat Matinee</span>
                                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">$35</span>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="text-gray-500 dark:text-gray-400 text-xs font-mono w-12">Mar 9</div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Sat Evening</span>
                                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">$40</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Newsletter to Patrons -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Newsletter
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Your audience. No middleman.</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">New show announced? One click emails every subscriber. No algorithm, no paying Facebook to reach your own patrons.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-amber-400/20">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">To: 1,847 patrons</div>
                        <div class="bg-gradient-to-r from-amber-950 to-yellow-950 rounded-lg p-3">
                            <div class="text-amber-300 text-xs font-medium mb-1">NOW ON SALE</div>
                            <div class="text-gray-900 dark:text-white font-semibold">Our Town</div>
                            <div class="text-gray-500 dark:text-gray-400 text-xs">April 12-28</div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Ticket Types -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900 dark:to-rose-900 border border-red-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Seating
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Orchestra. Mezzanine. Balcony.</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Different prices for different seats. Student rush, senior discounts, preview pricing - set it all up once.</p>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-red-500/20 border border-red-400/30">
                            <span class="text-gray-900 dark:text-white text-sm">Orchestra</span>
                            <span class="text-red-700 dark:text-red-300 font-semibold">$45</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Mezzanine</span>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">$35</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Balcony</span>
                            <span class="text-gray-500 dark:text-gray-400 font-semibold">$25</span>
                        </div>
                    </div>
                </div>

                <!-- QR Box Office (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900 dark:to-cyan-900 border border-rose-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                Box Office
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Scan tickets at the door</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Use any smartphone as a box office scanner. See who's checked in, catch duplicates, track attendance. No expensive hardware.</p>
                            <div class="flex flex-wrap gap-3 mt-6">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Will call</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">E-tickets</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Any device</span>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <div class="relative">
                                <div class="w-32 h-32 bg-white rounded-xl p-3 relative">
                                    <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20fill%3D%22%231f2937%22%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                                    <div class="absolute inset-x-0 top-3 h-0.5 bg-gradient-to-r from-rose-500 to-amber-500 animate-pulse"></div>
                                </div>
                                <div class="absolute -bottom-3 -right-3 flex gap-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-emerald-500/20 text-emerald-300 text-xs border border-emerald-500/30">Will Call</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-rose-500/20 text-rose-300 text-xs border border-rose-500/30">E-Ticket</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Spaces -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-red-100 dark:from-rose-950/50 dark:to-red-950/50 border border-rose-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Venues
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Main stage. Black box. Studio.</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Separate calendars for each theater space. Patrons filter by venue, you avoid double-booking.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-rose-500/20 border border-rose-500/30">
                            <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Main Stage</span>
                            <span class="ml-auto text-rose-700 dark:text-rose-300 text-xs">8 shows</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Black Box</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">12 shows</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Studio</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">5 shows</span>
                        </div>
                    </div>
                </div>

                <!-- Know Your Audience -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Which shows sell out?</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Comedies vs. dramas? Matinees vs. evenings? Data to plan your next season.</p>

                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">Musical</span>
                                <span class="text-amber-700 dark:text-amber-300">92%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-yellow-500 rounded-full" style="width: 92%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">Drama</span>
                                <span class="text-amber-700 dark:text-amber-300">78%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-yellow-500 rounded-full" style="width: 78%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">Comedy</span>
                                <span class="text-amber-700 dark:text-amber-300">85%</span>
                            </div>
                            <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-yellow-500 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Promo Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-yellow-100 to-amber-100 dark:from-yellow-900 dark:to-amber-900 border border-yellow-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Show posters, ready to share</h2>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Auto-generate promotional images sized for Instagram, Facebook, and your lobby display.</p>

                    <div class="flex justify-center">
                        <div class="relative w-28 h-36 bg-gradient-to-br from-rose-500/30 to-amber-500/30 rounded-lg border border-amber-400/30 p-2 shadow-lg">
                            <div class="w-full h-full bg-gradient-to-br from-rose-600/40 to-red-600/40 rounded flex flex-col items-center justify-center text-center">
                                <div class="text-yellow-300 text-[8px] font-bold mb-1">MARCH 8-24</div>
                                <div class="text-gray-900 dark:text-white text-sm font-bold leading-tight">HAMLET</div>
                                <div class="text-gray-600 dark:text-gray-300 text-[7px] mt-1">The Grand Theater</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center">
                                <svg aria-hidden="true" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Stream to the World Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" class="group block">
                <div class="bg-white dark:bg-[#12121a] rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:border-rose-500/30 transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-rose-300 transition-colors">Stream to the world</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Can't make it to the theater? Sell tickets to viewers at home. Live stream your productions worldwide. Virtual ticket buyers get the link, everyone else stays out.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Live streaming</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Virtual tickets</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Global reach</span>
                            </div>
                            <span class="inline-flex items-center text-rose-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-gradient-to-br from-rose-950 to-red-950 rounded-2xl border border-rose-400/30 p-5 w-56">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                    <span class="text-red-300 text-xs font-medium">LIVE</span>
                                    <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">247 watching</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-lg p-3 mb-3">
                                    <div class="text-gray-900 dark:text-white font-semibold text-sm">Hamlet - Opening Night</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">The Grand Theater</div>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-emerald-400">Virtual tickets: $20</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Built for every kind of theater
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From intimate black boxes to historic playhouses, Event Schedule adapts to your stage.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Community Theaters -->
                <x-sub-audience-card
                    name="Community Theaters"
                    description="Local productions, volunteer casts, and beloved classics. Reach your audience directly in your hometown."
                    icon-color="rose"
                    blog-slug="for-community-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Regional Theaters -->
                <x-sub-audience-card
                    name="Regional Theaters"
                    description="Professional productions with show runs. Manage season subscriptions and single tickets."
                    icon-color="red"
                    blog-slug="for-regional-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Black Box Theaters -->
                <x-sub-audience-card
                    name="Black Box Theaters"
                    description="Intimate experimental work. Flexible seating, limited capacity - every ticket matters."
                    icon-color="slate"
                    blog-slug="for-black-box-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Dinner Theaters -->
                <x-sub-audience-card
                    name="Dinner Theaters"
                    description="Combine show tickets with meal packages. Track dietary preferences alongside seat selections."
                    icon-color="amber"
                    blog-slug="for-dinner-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Children's Theaters -->
                <x-sub-audience-card
                    name="Children's Theaters"
                    description="Family-friendly productions and school group bookings. Manage matinee rushes with ease."
                    icon-color="pink"
                    blog-slug="for-childrens-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Outdoor Amphitheaters -->
                <x-sub-audience-card
                    name="Outdoor Amphitheaters"
                    description="Shakespeare in the park, summer stock. Handle weather-dependent scheduling and rain dates."
                    icon-color="emerald"
                    blog-slug="for-outdoor-theaters"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Get your theater online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-rose-500 to-red-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Set up your theater</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add your venue, performance spaces, and branding. Tell patrons what you're about.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-rose-500 to-red-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your productions</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Create show runs with all your performance dates. Set ticket types and prices.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-rose-500 to-red-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Fill every seat</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Share your calendar. Patrons subscribe. New show? They hear about it first.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-20 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Key features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="Ticketing"
                    description="Sell tickets with QR check-in and zero platform fees"
                    :url="marketing_url('/features/ticketing')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Analytics"
                    description="Track page views, devices, and traffic sources"
                    :url="marketing_url('/features/analytics')"
                    icon-color="emerald"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Fan Videos"
                    description="Let fans share videos and comments from your events"
                    :url="marketing_url('/features/fan-videos')"
                    icon-color="orange"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- Related Pages -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related pages</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ marketing_url('/for-venues') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Venues</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-theater-performers') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Theater Performers</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-community-centers') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Community Centers</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-libraries') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Libraries</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-rose-600 via-red-600 to-rose-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="absolute top-0 left-1/4 w-96 h-96 bg-white/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-amber-500/20 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your theater deserves a full house
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Sell tickets. Build your audience. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-rose-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <p class="mt-6 text-white/60 text-sm">No credit card required</p>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Theaters",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Theater Management Software",
        "operatingSystem": "Web",
        "description": "Sell out every performance. Manage show runs from opening night to closing curtain, sell tickets with zero platform fees, and email your patrons directly.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Show run management",
            "Multiple ticket types",
            "Zero-fee ticketing",
            "QR code box office",
            "Direct patron newsletter",
            "Multi-venue scheduling"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
