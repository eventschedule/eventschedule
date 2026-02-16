<x-marketing-layout>
    <x-slot name="title">Event Schedule for Farmers Markets | Grow Your Market Community</x-slot>
    <x-slot name="description">Grow your farmers market community. Email shoppers directly - no algorithm. Share vendor lineups, seasonal schedules, and special events. Free forever.</x-slot>
    <x-slot name="keywords">farmers market calendar, outdoor market events, farmers market newsletter, market vendor lineup, seasonal market schedule, farmers market software, community market events, artisan market calendar, flea market scheduling</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Farmers Markets</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Farmers Markets",
        "description": "Grow your farmers market community. Share vendor lineups, seasonal schedules, and special events. Email shoppers directly. Free forever.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Farmers Markets"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-lime-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-green-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Bunting/pennant banner -->
        <div class="absolute top-0 left-0 right-0 overflow-hidden h-20 hidden md:block">
            <svg aria-hidden="true" class="w-full" height="60" viewBox="0 0 1200 60" preserveAspectRatio="none" fill="none">
                <!-- String -->
                <path d="M0,10 Q300,25 600,10 Q900,25 1200,10" stroke="currentColor" stroke-width="1.5" class="text-gray-400 dark:text-gray-600" fill="none"/>
                <!-- Pennant flags -->
                <polygon points="80,12 100,50 120,12" class="fill-lime-500/20 dark:fill-lime-500/15"/>
                <polygon points="160,16 180,52 200,16" class="fill-amber-500/20 dark:fill-amber-500/15"/>
                <polygon points="240,14 260,50 280,14" class="fill-orange-500/20 dark:fill-orange-500/15"/>
                <polygon points="320,17 340,52 360,17" class="fill-red-500/15 dark:fill-red-500/10"/>
                <polygon points="400,13 420,50 440,13" class="fill-lime-500/20 dark:fill-lime-500/15"/>
                <polygon points="480,18 500,52 520,18" class="fill-amber-500/20 dark:fill-amber-500/15"/>
                <polygon points="560,12 580,50 600,12" class="fill-orange-500/20 dark:fill-orange-500/15"/>
                <polygon points="640,17 660,52 680,17" class="fill-red-500/15 dark:fill-red-500/10"/>
                <polygon points="720,13 740,50 760,13" class="fill-lime-500/20 dark:fill-lime-500/15"/>
                <polygon points="800,18 820,52 840,18" class="fill-amber-500/20 dark:fill-amber-500/15"/>
                <polygon points="880,12 900,50 920,12" class="fill-orange-500/20 dark:fill-orange-500/15"/>
                <polygon points="960,16 980,52 1000,16" class="fill-red-500/15 dark:fill-red-500/10"/>
                <polygon points="1040,14 1060,50 1080,14" class="fill-lime-500/20 dark:fill-lime-500/15"/>
                <polygon points="1120,17 1140,52 1160,17" class="fill-amber-500/20 dark:fill-amber-500/15"/>
            </svg>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <!-- Floating market icon - Rustic stamp style -->
        <div class="absolute top-16 right-8 md:right-16 animate-float opacity-90 hidden sm:block">
            <div class="rustic-stamp px-5 py-3">
                <span class="text-2xl font-bold text-lime-600 dark:text-lime-400 tracking-widest">FRESH</span>
            </div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg wood-grain-badge border border-amber-300/50 dark:border-amber-600/30 mb-8 backdrop-blur-sm">
                <svg aria-hidden="true" class="w-4 h-4 text-lime-600 dark:text-lime-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">For Farmers Markets & Outdoor Markets</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Your market. Your community.<br>
                <span class="text-gradient-lime">Fresh every week.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Stop losing shoppers to outdated flyers. Email your community directly, share what's fresh, and grow your market.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-lime-600 to-green-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-lime-500/25">
                    Create your market's calendar
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Market type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-300 text-xs font-medium border border-lime-200 dark:border-lime-500/30">Organic Produce</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-500/30">Artisan Crafts</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-xs font-medium border border-orange-200 dark:border-orange-500/30">Baked Goods</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-xs font-medium border border-yellow-200 dark:border-yellow-500/30">Local Honey</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-500/30">Cut Flowers</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300 text-xs font-medium border border-green-200 dark:border-green-500/30">Farm Fresh</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Market Updates Newsletter - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900 dark:to-green-900 border border-lime-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-lime-500/20 text-lime-700 dark:text-lime-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Tell shoppers what's fresh this week</h2>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">No algorithm. No pay-to-play. New vendor this Saturday? Strawberries are in season? One click sends the market update straight to everyone who wants to know.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Skip the algorithm</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Your shoppers, your reach</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-950 dark:to-green-950 rounded-2xl border border-lime-300 dark:border-lime-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-lime-500 to-green-500 rounded-xl flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">This Week at the Market</div>
                                            <div class="text-lime-600 dark:text-lime-300 text-xs">Sending to 1,243 followers...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-lime-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Green Acres Farm - Fresh Berries</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Baker's Dozen - Sourdough Bread</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Honey Bee Apiary - NEW VENDOR</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recurring Markets -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 border border-green-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/20 text-green-700 dark:text-green-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring Events
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Every Saturday. Rain or shine.</h2>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Markets run on weekly rhythms. Set up recurring market days once, they show automatically.</p>

                    <!-- Mini week view -->
                    <div class="grid grid-cols-7 gap-1">
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">M</div>
                            <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">T</div>
                            <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-green-700 dark:text-green-300 text-[10px] mb-1 font-medium">W</div>
                            <div class="w-6 h-6 rounded-full bg-green-500 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">T</div>
                            <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">F</div>
                            <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-green-700 dark:text-green-300 text-[10px] mb-1 font-medium">S</div>
                            <div class="w-6 h-6 rounded-full bg-green-500 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-green-700 dark:text-green-300 text-[10px] mb-1 font-medium">S</div>
                            <div class="w-6 h-6 rounded-full bg-lime-500/30 border border-lime-400/50 mx-auto"></div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-gray-500 dark:text-white/70 text-xs">Wed & Sat markets auto-repeat</div>
                </div>

                <!-- Vendor Lineup -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Vendors
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Show your vendor lineup</h2>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Shoppers want to know who's there before they come. Show them the full lineup.</p>

                    <!-- Vendor list visual -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-500/20 border border-amber-400/30">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-lime-500 to-green-500 flex items-center justify-center text-white text-[9px] font-semibold">GA</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-xs font-medium">Green Acres Farm</div>
                                <div class="text-amber-700 dark:text-amber-300 text-[10px]">Produce</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-500/20 border border-amber-400/30">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-[9px] font-semibold">BD</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-xs font-medium">Baker's Dozen</div>
                                <div class="text-amber-700 dark:text-amber-300 text-[10px]">Baked Goods</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-amber-500/20 border border-amber-400/30">
                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-yellow-500 to-amber-500 flex items-center justify-center text-white text-[9px] font-semibold">HB</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-xs font-medium">Honey Bee Apiary</div>
                                <div class="text-amber-700 dark:text-amber-300 text-[10px]">Honey & Preserves</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seasonal Calendar (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Seasonal Calendar
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Plan your whole season</h2>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-4">Spring opening to winter holiday markets. Lay out the full season so shoppers know what to expect all year long.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Full season view</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Special event dates</span>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <!-- Season cards visual -->
                            <div class="grid grid-cols-2 gap-2 w-full max-w-xs">
                                <div class="bg-green-500/20 border border-green-400/30 rounded-xl p-3 text-center">
                                    <div class="text-lg mb-1">&#127793;</div>
                                    <div class="text-green-700 dark:text-green-300 text-xs font-bold">Spring</div>
                                    <div class="text-gray-500 dark:text-white/70 text-[10px]">Apr - May</div>
                                </div>
                                <div class="bg-yellow-500/20 border border-yellow-400/30 rounded-xl p-3 text-center">
                                    <div class="text-lg mb-1">&#9728;&#65039;</div>
                                    <div class="text-yellow-700 dark:text-yellow-300 text-xs font-bold">Summer</div>
                                    <div class="text-gray-500 dark:text-white/70 text-[10px]">Jun - Aug</div>
                                </div>
                                <div class="bg-orange-500/20 border border-orange-400/30 rounded-xl p-3 text-center">
                                    <div class="text-lg mb-1">&#127810;</div>
                                    <div class="text-orange-700 dark:text-orange-300 text-xs font-bold">Fall</div>
                                    <div class="text-gray-500 dark:text-white/70 text-[10px]">Sep - Nov</div>
                                </div>
                                <div class="bg-blue-500/20 border border-blue-400/30 rounded-xl p-3 text-center">
                                    <div class="text-lg mb-1">&#10052;&#65039;</div>
                                    <div class="text-blue-700 dark:text-blue-300 text-xs font-bold">Winter</div>
                                    <div class="text-gray-500 dark:text-white/70 text-[10px]">Dec - Mar</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Events -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-orange-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/20 text-orange-700 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Special Events
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">More than just shopping</h2>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Cooking demos, live music, kids activities. Make your market a destination.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-orange-500/20 border border-orange-400/30">
                            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-xs">Chef Demo: Farm to Table</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-orange-500/20 border border-orange-400/30">
                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-xs">Live Bluegrass Band</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-orange-500/20 border border-orange-400/30">
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-xs">Kids Pumpkin Painting</span>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-700 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Google Calendar
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sync with Google Calendar</h2>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Shoppers add your market to their calendar with one click. Never miss a market day.</p>

                    <div class="flex justify-center">
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl p-3 w-full">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-3 h-3 rounded-sm bg-blue-500"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-xs font-medium">Farmers Market</span>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 text-[10px]">
                                    <span class="text-gray-500 dark:text-white/70">Sat 8am</span>
                                    <span class="text-blue-700 dark:text-blue-300 font-medium">Saturday Market</span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px]">
                                    <span class="text-gray-500 dark:text-white/70">Wed 3pm</span>
                                    <span class="text-blue-700 dark:text-blue-300 font-medium">Midweek Market</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">See what draws crowds</h2>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Which events get shares? What markets are most popular? Know what's working.</p>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-white/70 text-xs">Sat Market</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-2 bg-cyan-500/30 rounded-full overflow-hidden">
                                    <div class="w-[92%] h-full bg-cyan-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-white/70 text-xs">Chef Demo</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-2 bg-cyan-500/30 rounded-full overflow-hidden">
                                    <div class="w-[70%] h-full bg-cyan-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-white/70 text-xs">Wed Market</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-2 bg-cyan-500/30 rounded-full overflow-hidden">
                                    <div class="w-[50%] h-full bg-cyan-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Seasonal Market Calendar - UNIQUE TO FARMERS MARKETS -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Your market through the seasons
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    From the first spring asparagus to holiday gift markets, your calendar tells the story of the whole year. Shoppers always know what's next.
                </p>
            </div>

            <!-- Seasonal Timeline -->
            <div class="bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900/30 dark:to-green-900/30 rounded-3xl border border-lime-200 dark:border-white/10 p-6 md:p-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Spring Opening -->
                    <div class="text-center">
                        <div class="bg-green-500/20 border border-green-400/30 rounded-xl p-4 min-h-[160px] flex flex-col items-center justify-center relative">
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-green-500 rounded text-[9px] text-white font-bold">SPRING</div>
                            <!-- Carrot SVG -->
                            <svg aria-hidden="true" class="w-8 h-8 mb-2" viewBox="0 0 32 32" fill="none">
                                <path d="M16,8 L14,28 Q16,30 18,28 Z" fill="#f97316"/>
                                <path d="M14,10 L10,6 M16,8 L16,3 M18,10 L22,6" stroke="#22c55e" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <div class="text-green-700 dark:text-green-300 text-sm font-bold mb-1">Spring Opening</div>
                            <div class="text-gray-500 dark:text-white/70 text-xs">April kick-off</div>
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mt-2">Asparagus, greens, seedlings</div>
                        </div>
                    </div>
                    <!-- Summer Peak -->
                    <div class="text-center">
                        <div class="bg-yellow-500/20 border border-yellow-400/30 rounded-xl p-4 min-h-[160px] flex flex-col items-center justify-center relative">
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-yellow-500 rounded text-[9px] text-white font-bold">SUMMER</div>
                            <!-- Tomato SVG -->
                            <svg aria-hidden="true" class="w-8 h-8 mb-2" viewBox="0 0 32 32" fill="none">
                                <circle cx="16" cy="18" r="11" fill="#ef4444"/>
                                <ellipse cx="16" cy="18" rx="11" ry="10" fill="#dc2626"/>
                                <path d="M12,8 Q14,5 16,7 Q18,5 20,8" stroke="#22c55e" stroke-width="2" fill="none" stroke-linecap="round"/>
                                <path d="M16,7 L16,4" stroke="#22c55e" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <div class="text-yellow-700 dark:text-yellow-300 text-sm font-bold mb-1">Peak Season</div>
                            <div class="text-gray-500 dark:text-white/70 text-xs">June - August</div>
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mt-2">Berries, tomatoes, corn</div>
                        </div>
                    </div>
                    <!-- Fall Harvest -->
                    <div class="text-center">
                        <div class="bg-orange-500/20 border border-orange-400/30 rounded-xl p-4 min-h-[160px] flex flex-col items-center justify-center relative">
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-orange-500 rounded text-[9px] text-white font-bold">FALL</div>
                            <!-- Pumpkin SVG -->
                            <svg aria-hidden="true" class="w-8 h-8 mb-2" viewBox="0 0 32 32" fill="none">
                                <ellipse cx="16" cy="20" rx="12" ry="10" fill="#f97316"/>
                                <ellipse cx="16" cy="20" rx="5" ry="10" fill="#ea580c" opacity="0.5"/>
                                <path d="M16,10 L16,6" stroke="#22c55e" stroke-width="2" stroke-linecap="round"/>
                                <path d="M14,8 Q16,5 18,8" stroke="#22c55e" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            </svg>
                            <div class="text-orange-700 dark:text-orange-300 text-sm font-bold mb-1">Harvest Festival</div>
                            <div class="text-gray-500 dark:text-white/70 text-xs">September - November</div>
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mt-2">Pumpkins, apples, cider</div>
                        </div>
                    </div>
                    <!-- Winter Holiday -->
                    <div class="text-center">
                        <div class="bg-blue-500/20 border border-blue-400/30 rounded-xl p-4 min-h-[160px] flex flex-col items-center justify-center relative">
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-blue-500 rounded text-[9px] text-white font-bold">WINTER</div>
                            <!-- Snowflake SVG -->
                            <svg aria-hidden="true" class="w-8 h-8 mb-2" viewBox="0 0 32 32" fill="none" stroke="#60a5fa" stroke-width="1.5" stroke-linecap="round">
                                <line x1="16" y1="4" x2="16" y2="28"/>
                                <line x1="6" y1="10" x2="26" y2="22"/>
                                <line x1="6" y1="22" x2="26" y2="10"/>
                                <line x1="16" y1="4" x2="13" y2="7"/><line x1="16" y1="4" x2="19" y2="7"/>
                                <line x1="16" y1="28" x2="13" y2="25"/><line x1="16" y1="28" x2="19" y2="25"/>
                                <line x1="6" y1="10" x2="9" y2="8"/><line x1="6" y1="10" x2="8" y2="13"/>
                                <line x1="26" y1="22" x2="23" y2="24"/><line x1="26" y1="22" x2="24" y2="19"/>
                            </svg>
                            <div class="text-blue-700 dark:text-blue-300 text-sm font-bold mb-1">Holiday Market</div>
                            <div class="text-gray-500 dark:text-white/70 text-xs">December - March</div>
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mt-2">Gifts, preserves, wreaths</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for all types of markets
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From weekly farmers markets to holiday pop-ups, Event Schedule fits your market.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Weekly Farmers Markets -->
                <x-sub-audience-card
                    name="Weekly Farmers Markets"
                    description="Recurring market days, vendor lineups, and seasonal produce updates. Build a loyal community of local shoppers."
                    icon-color="lime"
                    blog-slug="for-weekly-farmers-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-lime-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Artisan & Craft Markets -->
                <x-sub-audience-card
                    name="Artisan & Craft Markets"
                    description="Handmade goods, pottery, jewelry, and art. Showcase your makers and attract craft-loving shoppers."
                    icon-color="amber"
                    blog-slug="for-artisan-craft-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Flea Markets & Swap Meets -->
                <x-sub-audience-card
                    name="Flea Markets & Swap Meets"
                    description="Vintage finds, collectibles, and one-of-a-kind treasures. Let bargain hunters know when you're open."
                    icon-color="orange"
                    blog-slug="for-flea-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Holiday & Seasonal Markets -->
                <x-sub-audience-card
                    name="Holiday & Seasonal Markets"
                    description="Christmas markets, harvest festivals, and spring pop-ups. Build anticipation for your seasonal events."
                    icon-color="red"
                    blog-slug="for-holiday-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Night Markets -->
                <x-sub-audience-card
                    name="Night Markets"
                    description="Street food, live entertainment, and late-night shopping. Create buzz for your after-dark events."
                    icon-color="violet"
                    blog-slug="for-night-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Specialty Food Markets -->
                <x-sub-audience-card
                    name="Specialty Food Markets"
                    description="Organic co-ops, cheese markets, and gourmet food halls. Connect food lovers with local producers."
                    icon-color="emerald"
                    blog-slug="for-specialty-food-markets"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A2.704 2.704 0 003 15.546M9 6v2m3-2v2m3-2v2M9 3h6m-7 8h8a1 1 0 011 1v4a1 1 0 01-1 1H8a1 1 0 01-1-1v-4a1 1 0 011-1z" />
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
                    Get your market online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-lime-500 to-green-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-lime-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Set up your market</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add your market name, location, and upload your logo. Takes two minutes.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-lime-500 to-green-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-lime-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your schedule</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Saturday morning market, Wednesday evening market, special events. Set recurring dates once, or add seasonal events as they come.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-lime-500 to-green-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-lime-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build your community</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Share your link. Shoppers follow. They get market updates in their inbox - no checking social media required.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-lime-600 to-green-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Stop losing shoppers to outdated flyers
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Email your community directly. Grow your market. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-lime-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Farmers Markets",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Farmers Market Management Software",
        "operatingSystem": "Web",
        "description": "Grow your farmers market community. Email shoppers directly - no algorithm. Share vendor lineups, seasonal schedules, and special events. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Direct newsletter to shoppers - no algorithm middleman",
            "Vendor lineup management",
            "Recurring weekly market schedules",
            "Seasonal calendar planning",
            "Special event announcements",
            "Google Calendar sync for shoppers",
            "Event analytics and tracking"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style {!! nonce_attr() !!}>
        .text-gradient-lime {
            background: linear-gradient(135deg, #84cc16, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-lime {
            background: linear-gradient(135deg, #a3e635, #4ade80);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .rustic-stamp {
            border: 3px solid currentColor;
            border-radius: 4px;
            color: rgb(101, 163, 13);
            transform: rotate(-3deg);
            background: rgba(101, 163, 13, 0.05);
            box-shadow: 0 4px 12px rgba(101, 163, 13, 0.15);
        }

        .dark .rustic-stamp {
            color: rgb(163, 230, 53);
            background: rgba(163, 230, 53, 0.05);
            box-shadow: 0 4px 12px rgba(163, 230, 53, 0.1);
        }

        .wood-grain-badge {
            background: linear-gradient(135deg, rgba(180, 140, 80, 0.1), rgba(160, 120, 60, 0.15), rgba(180, 140, 80, 0.1));
            position: relative;
        }

        .dark .wood-grain-badge {
            background: linear-gradient(135deg, rgba(120, 80, 30, 0.3), rgba(100, 70, 20, 0.4), rgba(120, 80, 30, 0.3));
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-float {
                animation: none;
            }
        }
    </style>
</x-marketing-layout>
