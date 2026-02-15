<x-marketing-layout>
    <x-slot name="title">Event Schedule for Breweries & Wineries | Tasting Events</x-slot>
    <x-slot name="description">Fill your tasting room with fans. Announce releases, host tastings, and sell tickets to brewery events. Email your fans directly. Free forever.</x-slot>
    <x-slot name="keywords">brewery event calendar, winery events, tasting room software, beer release party, wine tasting tickets, brewery tour booking, taproom events, craft brewery calendar, vineyard event management</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Breweries & Wineries</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Breweries & Wineries",
        "description": "Fill your tasting room with fans. Announce releases, host tastings, and sell tickets to brewery events. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Breweries & Wineries"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section with Floating Release Card -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background with copper/amber and purple tones -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-amber-700/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-800/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-40 right-1/3 w-[250px] h-[250px] bg-amber-600/15 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="flex-1 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">For Breweries, Wineries & Tasting Rooms</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                        Every pour<br>
                        <span class="text-gradient-copper">deserves an audience.</span>
                    </h1>

                    <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-xl mb-12">
                        New release hitting taps? Hosting a tasting this weekend? Email your fans directly - no paying Facebook to reach people who already love your craft.
                    </p>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                        <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-amber-600 to-amber-800 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                            Create your tasting room calendar
                            <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <!-- Venue type tags -->
                    <div class="mt-12 flex flex-wrap justify-center lg:justify-start gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-500/30">Craft Brewery</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-xs font-medium border border-blue-500/30">Winery</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-xs font-medium border border-orange-500/30">Cidery</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-xs font-medium border border-yellow-500/30">Meadery</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-xs font-medium border border-rose-500/30">Distillery</span>
                    </div>
                </div>

                <!-- Floating Release Card Visual -->
                <div class="flex-shrink-0 hidden lg:block">
                    <div class="relative animate-float">
                        <!-- Main release card -->
                        <div class="bg-gradient-to-br from-amber-900/80 to-orange-900/80 rounded-2xl border border-amber-400/40 p-6 w-72 shadow-2xl shadow-amber-900/50 backdrop-blur-sm">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-amber-300 text-xs font-semibold tracking-wider uppercase">Limited Release</span>
                                <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-red-500/20 border border-red-500/30">
                                    <div class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></div>
                                    <span class="text-red-300 text-[10px] font-medium">SELLING FAST</span>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="text-white text-xl font-bold mb-1">Barrel-Aged Imperial Stout</div>
                                <div class="text-amber-200/70 text-sm">18 months in bourbon barrels</div>
                            </div>
                            <div class="flex items-center justify-between mb-4 p-3 rounded-xl bg-black/30 border border-white/10">
                                <div>
                                    <div class="text-gray-400 text-xs">Available</div>
                                    <div class="text-white text-lg font-bold">47 <span class="text-gray-400 text-sm font-normal">/ 200</span></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-gray-400 text-xs">Release Date</div>
                                    <div class="text-amber-300 text-sm font-medium">This Saturday</div>
                                </div>
                            </div>
                            <button class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-semibold rounded-xl hover:scale-[1.02] transition-transform">
                                Notify Me When Available
                            </button>
                        </div>
                        <!-- Decorative floating elements -->
                        <div class="absolute -top-4 -right-4 w-16 h-16 bg-blue-500/20 rounded-full blur-xl"></div>
                        <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-amber-500/20 rounded-full blur-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Release Cycle - UNIQUE TO PRODUCERS -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From grain to glass, vine to bottle
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    You don't just serve drinks - you create them. Your fans want to follow the journey, from first brew day to release party.
                </p>
            </div>

            <!-- The Release Cycle Journey -->
            <div class="relative">
                <!-- Connection line (desktop) -->
                <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-amber-500/50 via-blue-500/50 to-amber-500/50 -translate-y-1/2"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Phase 1: The Craft -->
                    <div class="relative bg-gradient-to-br from-amber-100/40 to-yellow-100/40 dark:from-amber-900/40 dark:to-yellow-900/40 rounded-2xl border border-amber-500/20 p-6 hover:border-amber-500/40 transition-colors">
                        <div class="lg:absolute lg:-top-3 lg:left-1/2 lg:-translate-x-1/2 w-8 h-8 bg-amber-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-4 lg:mb-0 mx-auto lg:mx-0">1</div>
                        <div class="text-center lg:pt-6">
                            <div class="text-3xl mb-3">&#127806;</div>
                            <div class="text-amber-700 dark:text-amber-300 text-sm font-semibold tracking-wider uppercase mb-2">The Craft</div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Share behind-the-scenes: harvest updates, brew day photos, barrel selection. Build anticipation from day one.</p>
                        </div>
                    </div>

                    <!-- Phase 2: The Wait -->
                    <div class="relative bg-gradient-to-br from-blue-100/40 to-sky-100/40 dark:from-blue-900/40 dark:to-sky-900/40 rounded-2xl border border-blue-500/20 p-6 hover:border-blue-500/40 transition-colors">
                        <div class="lg:absolute lg:-top-3 lg:left-1/2 lg:-translate-x-1/2 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-4 lg:mb-0 mx-auto lg:mx-0">2</div>
                        <div class="text-center lg:pt-6">
                            <div class="text-3xl mb-3">&#127866;</div>
                            <div class="text-blue-700 dark:text-blue-300 text-sm font-semibold tracking-wider uppercase mb-2">The Wait</div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Aging in barrels, fermenting in tanks. Tease your members with early samples and first-access previews.</p>
                        </div>
                    </div>

                    <!-- Phase 3: The Release -->
                    <div class="relative bg-gradient-to-br from-rose-100/40 to-orange-100/40 dark:from-rose-900/40 dark:to-orange-900/40 rounded-2xl border border-rose-500/20 p-6 hover:border-rose-500/40 transition-colors">
                        <div class="lg:absolute lg:-top-3 lg:left-1/2 lg:-translate-x-1/2 w-8 h-8 bg-rose-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-4 lg:mb-0 mx-auto lg:mx-0">3</div>
                        <div class="text-center lg:pt-6">
                            <div class="text-3xl mb-3">&#127881;</div>
                            <div class="text-rose-700 dark:text-rose-300 text-sm font-semibold tracking-wider uppercase mb-2">The Release</div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">The moment arrives. Email blast, release party, allocations ship. Your fans showed up because you told them first.</p>
                        </div>
                    </div>

                    <!-- Phase 4: The Community -->
                    <div class="relative bg-gradient-to-br from-emerald-100/40 to-teal-100/40 dark:from-emerald-900/40 dark:to-teal-900/40 rounded-2xl border border-emerald-500/20 p-6 hover:border-emerald-500/40 transition-colors">
                        <div class="lg:absolute lg:-top-3 lg:left-1/2 lg:-translate-x-1/2 w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-4 lg:mb-0 mx-auto lg:mx-0">4</div>
                        <div class="text-center lg:pt-6">
                            <div class="text-3xl mb-3">&#129346;</div>
                            <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold tracking-wider uppercase mb-2">The Community</div>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Taproom visits, club events, share nights. The relationship continues until the next release.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Producer focus note -->
            <div class="mt-10 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">You make what you sell. That story is worth sharing.</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Release Announcements - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Release Announcements
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Limited release? Your fans know first.</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">That bourbon barrel stout you've been aging for 18 months? The reserve Pinot from the best vintage in a decade? One click sends it straight to everyone who wants to know - before it's gone.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Skip the algorithm</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Straight from the source</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-amber-950 to-orange-950 rounded-2xl border border-amber-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-white text-sm font-medium">Barrel Room Release!</div>
                                            <div class="text-amber-300 text-xs">Sending to 2,341 fans...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-300 text-xs">Bourbon Barrel Stout</span>
                                            <span class="ml-auto text-amber-300 text-[10px]">47 left</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                            <span class="text-gray-300 text-xs">Reserve Pinot Noir</span>
                                            <span class="ml-auto text-blue-300 text-[10px]">12 cases</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                            <span class="text-gray-300 text-xs">Maple Porter</span>
                                            <span class="ml-auto text-orange-300 text-[10px]">SOLD OUT</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Club Members First / Allocation -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900 dark:to-cyan-900 border border-rose-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        First Access
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Club members get first dibs</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Limited releases sell out fast. Your loyal fans - mug club, wine club, followers - get the heads up before anyone else.</p>

                    <!-- Waitlist visual -->
                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">2024 Reserve Allocation</span>
                            <span class="text-rose-700 dark:text-rose-300 text-xs font-medium">47 spots left</span>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-[10px] font-bold">M</div>
                                <span class="text-gray-900 dark:text-white text-xs flex-1">Wine Club Member</span>
                                <span class="text-emerald-400 text-[10px]">Confirmed</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white text-[10px] font-bold">J</div>
                                <span class="text-gray-600 dark:text-gray-300 text-xs flex-1">Email Subscriber</span>
                                <span class="text-amber-400 text-[10px]">Waitlist #3</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticketed Tastings -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Tastings worth paying for</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Barrel room tours, vertical tastings, winemaker dinners. Sell tickets, limit capacity, scan at the door.</p>

                    <!-- Ticket card visual -->
                    <div class="flex justify-center">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl border border-blue-300/50 p-4 w-44 shadow-lg transform rotate-2 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-blue-800 text-[10px] tracking-widest uppercase">Exclusive Tasting</div>
                                <div class="text-blue-900 text-sm font-serif font-semibold mt-1">Barrel Room Tour</div>
                                <div class="text-blue-700 text-xl font-bold mt-2">$45<span class="text-xs font-normal">pp</span></div>
                                <div class="text-blue-600 text-[10px] mt-1">Sat, Oct 14 &bull; 2:00 PM</div>
                                <div class="text-blue-500 text-[9px] mt-1">Only 8 spots left</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Private Events & Tours (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Booking Inbox
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Tours and private events come to you</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Corporate team outings, bachelorette parties, private tastings. They submit the request, you approve. No email ping-pong.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Group tours</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Private parties</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Buyouts</span>
                            </div>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">Booking Requests</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-sky-500/20 border border-sky-400/30">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">AT</div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white text-sm font-medium">Acme Tech - Team Outing</div>
                                        <div class="text-sky-700 dark:text-sky-300 text-xs">Nov 3 &bull; 25 people &bull; Private tour</div>
                                    </div>
                                    <div class="flex gap-1">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="w-6 h-6 rounded-full bg-red-500/20 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100 dark:bg-white/5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-rose-500 flex items-center justify-center text-white text-xs font-semibold">JM</div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Jamie's Bachelorette</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs">Oct 28 &bull; 12 people</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Spaces -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Spaces
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Taproom. Patio. Barrel room.</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Separate calendars for each space. Visitors see what's happening where.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-500/20 border border-emerald-500/30">
                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Taproom</span>
                            <span class="ml-auto text-emerald-700 dark:text-emerald-300 text-xs">6 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Patio & Beer Garden</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">4 events</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Barrel Room</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">2 events</span>
                        </div>
                    </div>
                </div>

                <!-- What's Pouring Today - QR Code Feature -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-900 dark:to-red-900 border border-orange-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h2m10 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        Live Menu
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">What's pouring today</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">QR code for your current tap list. Visitors scan, you update. No printed menus to change.</p>

                    <!-- QR code visual -->
                    <div class="flex justify-center">
                        <div class="bg-white rounded-xl p-3 shadow-lg">
                            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center relative overflow-hidden">
                                <!-- Simplified QR pattern -->
                                <div class="grid grid-cols-5 gap-1">
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-300 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                    <div class="w-3 h-3 bg-gray-800 rounded-sm"></div>
                                </div>
                            </div>
                            <div class="text-center mt-2">
                                <div class="text-gray-800 text-[10px] font-semibold">SCAN FOR TAP LIST</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Event Graphics - BOTTOM RIGHT -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ready for social</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Auto-generate promo graphics for release parties. Download and post in seconds.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-amber-500/30 to-orange-500/30 rounded-xl border border-amber-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-amber-600/40 to-orange-700/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-white text-[10px] font-semibold mb-1">THIS SATURDAY</div>
                                <div class="text-amber-200 text-xs font-bold">IPA Release</div>
                                <div class="text-gray-400 text-[8px] mt-1">Taproom 4pm</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-cyan-500 rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Virtual Tastings Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-blue-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Virtual tastings go global</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-4">Ship your product, host a live tasting. Fans anywhere can join, pay, and taste along. Turn your taproom into a worldwide experience.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Live tastings</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Sell tickets worldwide</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Any platform</span>
                            </div>
                            <span class="inline-flex items-center text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Virtual Tasting</span>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                        <span class="text-red-400 text-[10px]">LIVE</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-amber-600/30 to-orange-600/30 rounded-lg p-4 text-center mb-3">
                                    <div class="text-2xl mb-1">&#127866;</div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">IPA Flight</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">with Brewmaster Mike</div>
                                </div>
                                <div class="flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>89 viewers</span>
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
                    Perfect for all types of craft beverage makers
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From small-batch breweries to estate wineries, Event Schedule fits your operation.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Craft Breweries -->
                <x-sub-audience-card
                    name="Craft Breweries"
                    description="Release parties, tap takeovers, and brewery tours. Your fans followed you for your IPAs - make sure they know when the new batch drops."
                    icon-color="amber"
                    blog-slug="for-craft-breweries"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Brewpubs -->
                <x-sub-audience-card
                    name="Brewpubs & Taprooms"
                    description="Live music, trivia nights, and food pop-ups. Turn your taproom into a destination every night of the week."
                    icon-color="orange"
                    blog-slug="for-brewpubs-taprooms"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Wineries -->
                <x-sub-audience-card
                    name="Wineries & Vineyards"
                    description="Wine releases, harvest dinners, and vineyard tours. From first crush to final pour, keep your wine club in the loop."
                    icon-color="purple"
                    blog-slug="for-wineries-vineyards"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cideries -->
                <x-sub-audience-card
                    name="Cideries & Orchards"
                    description="Apple picking events, seasonal releases, and cider tastings. The orchard-to-glass story your fans want to be part of."
                    icon-color="red"
                    blog-slug="for-cideries-orchards"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Meaderies & Distilleries -->
                <x-sub-audience-card
                    name="Meaderies & Distilleries"
                    description="Mead tastings, cocktail classes, and spirit releases. Educate visitors about your ancient craft or your small-batch spirits."
                    icon-color="yellow"
                    blog-slug="for-meaderies-distilleries"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Taproom-Only -->
                <x-sub-audience-card
                    name="Taproom-Only Breweries"
                    description="No distribution? No problem. Your taproom is your stage. Fill it with fans who came specifically to try your latest creation."
                    icon-color="emerald"
                    blog-slug="for-taproom-only-breweries"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Get your tasting room calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-600 to-amber-800 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your brewery or winery</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Upload your logo, add your spaces (taproom, patio, barrel room), and customize your branding.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-600 to-amber-800 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Post your events</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Release parties, tastings, live music. Add tickets if needed. Set recurring events once and forget about them.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-600 to-amber-800 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Grow your following</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Visitors follow your calendar. When you post a new release, it goes straight to their inbox. No middleman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-amber-700 to-amber-900 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>
        <!-- Subtle copper glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-orange-500/15 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your fans. Direct reach. No middleman.
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                You make the product. You own the relationship. Email your fans directly - no algorithm in the way.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-amber-700 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        "name": "Event Schedule for Breweries & Wineries",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Brewery and Winery Event Management Software",
        "operatingSystem": "Web",
        "description": "Fill your tasting room with fans. Announce new releases, host tastings, and sell tickets to brewery events. Email your fans directly. No algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Limited release announcements with email newsletters",
            "Club member first access and allocation management",
            "Ticketed tastings and barrel room tours",
            "Private event and tour booking inbox",
            "Multiple space calendars (taproom, patio, barrel room)",
            "Live tap list with QR code scanning",
            "Virtual tasting support",
            "Auto-generated social media graphics"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style {!! nonce_attr() !!}>
        .text-gradient-copper {
            background: linear-gradient(135deg, #d97706, #92400e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient-copper {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Float animation for cards */
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</x-marketing-layout>
