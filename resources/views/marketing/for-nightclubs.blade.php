<x-marketing-layout>
    <x-slot name="title">Event Schedule for Nightclubs | DJ Lineup & Event Calendar Software</x-slot>
    <x-slot name="description">Fill your club with DJ lineups and themed nights. Email your crowd directly - no algorithm. Sell tickets, accept DJ bookings. Free forever.</x-slot>
    <x-slot name="keywords">nightclub event calendar, club DJ lineup software, nightclub booking system, club event management, DJ scheduling software, nightclub newsletter, club promotion, nightclub ticketing</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-pink-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-violet-600/10 rounded-full blur-[150px]"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left: Main hero content -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                        <svg class="w-4 h-4 text-pink-600 dark:text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        <span class="text-sm text-gray-600 dark:text-gray-300">For Nightclubs & Dance Venues</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                        <span class="text-gradient-nightclub">Pack the dancefloor.</span><br>
                        Own your crowd.
                    </h1>

                    <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-xl mx-auto lg:mx-0 mb-12">
                        Stop paying to reach your own followers. Email your crowd directly, announce your lineups, and fill your club - without the algorithm getting in the way.
                    </p>

                    <div class="flex flex-wrap justify-center lg:justify-start gap-4">
                        <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-pink-600 to-fuchsia-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-pink-500/25">
                            Create your club's calendar
                            <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </a>
                    </div>

                    <!-- Genre tags -->
                    <div class="mt-12 flex flex-wrap justify-center lg:justify-start gap-2">
                        <span class="px-3 py-1 rounded-full bg-pink-100 text-pink-700 dark:bg-pink-500/20 dark:text-pink-300 text-xs font-medium border border-pink-200 dark:border-pink-500/30">EDM</span>
                        <span class="px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-xs font-medium border border-fuchsia-200 dark:border-fuchsia-500/30">Hip-Hop</span>
                        <span class="px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-xs font-medium border border-violet-200 dark:border-violet-500/30">Latin</span>
                        <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-xs font-medium border border-purple-200 dark:border-purple-500/30">Rooftop</span>
                        <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-500/30">Underground</span>
                        <span class="px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-500/30">Lounge</span>
                    </div>
                </div>

                <!-- Right: Tonight's Lineup Card -->
                <div class="hidden lg:block">
                    <div class="relative animate-float">
                        <div class="bg-gradient-to-br from-pink-100 to-fuchsia-100 dark:from-pink-950 dark:to-fuchsia-950 rounded-2xl border border-pink-300 dark:border-pink-400/30 p-6 max-w-sm mx-auto backdrop-blur-sm">
                            <!-- Header with "Tonight" badge -->
                            <div class="flex items-center justify-between mb-5">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded bg-pink-500 text-white text-[10px] font-bold uppercase tracking-wide">Tonight</span>
                                    </div>
                                    <div class="text-gray-900 dark:text-white text-lg font-bold">Club Vortex</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">Saturday, Jan 25</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-gray-500 text-[10px] uppercase tracking-wide">Doors</div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">10pm</div>
                                </div>
                            </div>

                            <!-- Headliner -->
                            <div class="bg-gradient-to-r from-pink-500/30 to-fuchsia-500/30 rounded-xl p-4 mb-3 border border-pink-400/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-500 to-fuchsia-500 flex items-center justify-center text-white text-sm font-bold shadow-lg shadow-pink-500/30">DN</div>
                                    <div class="flex-1">
                                        <div class="text-[10px] text-pink-600 dark:text-pink-300 uppercase tracking-wide font-semibold">Headliner</div>
                                        <div class="text-gray-900 dark:text-white font-bold">DJ Nova</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs">Deep House / Tech House</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Support DJs -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-[10px] font-semibold">MX</div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Max Luna</div>
                                        <div class="text-gray-500 text-[10px]">12am - 2am</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-[10px] font-semibold">KR</div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Kira B2B Echo</div>
                                        <div class="text-gray-500 text-[10px]">10pm - 12am</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guest list status -->
                            <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-white/10">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-medium">Guest List Open</span>
                                </div>
                                <span class="text-gray-500 text-xs">Free before 11pm</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Weekly Lineup Blast - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-100 to-fuchsia-100 dark:from-pink-900 dark:to-fuchsia-900 border border-pink-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-100 text-pink-700 dark:bg-pink-500/20 dark:text-pink-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Weekly Lineup Blast
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Drop your weekend lineup</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-6">Thursday rolls around, you blast out the weekend's DJs to everyone who follows you. No algorithm. No pay-to-play. Just your crowd, hyped and ready.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">One-click send</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Your crowd, direct reach</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-pink-100 to-fuchsia-100 dark:from-pink-950 dark:to-fuchsia-950 rounded-2xl border border-pink-300 dark:border-pink-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-fuchsia-500 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">This Weekend at Vortex</div>
                                            <div class="text-pink-600 dark:text-pink-300 text-xs">Sending to 2,341 followers...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-pink-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-fuchsia-500 dark:bg-fuchsia-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Thu - Industry Night (free entry)</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-pink-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-pink-500 dark:bg-pink-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Fri - DJ Nova (House)</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-pink-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-violet-500 dark:bg-violet-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Sat - Latin Nights with DJ Fuego</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guest List Signups -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Guest List
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Get on the list</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Fans sign up for your guest list right from your calendar. Reduced cover before 11pm - the status thing that fills your early crowd.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-emerald-200 dark:bg-emerald-500/20 border border-emerald-400/30">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-[10px] font-semibold">MR</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm">Marco R. +3</div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-300 dark:bg-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-[10px] font-medium">Confirmed</span>
                        </div>
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center text-white text-[10px] font-semibold">SK</div>
                            <div class="flex-1">
                                <div class="text-gray-700 dark:text-gray-300 text-sm">Sarah K. +1</div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-emerald-300 dark:bg-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-[10px] font-medium">Confirmed</span>
                        </div>
                    </div>
                </div>

                <!-- Themed Nights on Autopilot -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-100 to-purple-100 dark:from-fuchsia-900 dark:to-purple-900 border border-fuchsia-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Branded Nights
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Your signature nights</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Latin Fridays. 80s Saturdays. Techno Sundays. Set them up once - they show automatically every week. Build the brand.</p>

                    <!-- Mini week view with colors -->
                    <div class="grid grid-cols-7 gap-1">
                        <div class="text-center">
                            <div class="text-gray-500 text-[10px] mb-1">M</div>
                            <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 text-[10px] mb-1">T</div>
                            <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 text-[10px] mb-1">W</div>
                            <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-fuchsia-600 dark:text-fuchsia-300 text-[10px] mb-1 font-medium">T</div>
                            <div class="w-6 h-6 rounded-full bg-fuchsia-500 mx-auto" title="Industry Night"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-orange-600 dark:text-orange-300 text-[10px] mb-1 font-medium">F</div>
                            <div class="w-6 h-6 rounded-full bg-orange-500 mx-auto" title="Latin Fridays"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-pink-600 dark:text-pink-300 text-[10px] mb-1 font-medium">S</div>
                            <div class="w-6 h-6 rounded-full bg-pink-500 mx-auto" title="80s Saturdays"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-violet-600 dark:text-violet-300 text-[10px] mb-1 font-medium">S</div>
                            <div class="w-6 h-6 rounded-full bg-violet-500 mx-auto" title="Techno Sundays"></div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-gray-500 text-xs">Color-coded. Repeating. Memorable.</div>
                </div>

                <!-- VIP Tables & Bottle Service - HERO POSITION (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                VIP & Bottle Service
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Table bookings, handled</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg">VIP table requests, bottle service inquiries, birthday packages - all in one inbox. See party size, minimum spend, special requests. Your biggest revenue driver, organized.</p>
                            <div class="flex flex-wrap gap-3 mt-4">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Table reservations</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Bottle service</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Birthday packages</span>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="text-xs text-gray-500 mb-3">VIP Requests - Saturday</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-amber-500/20 border border-amber-400/30">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-[10px] font-semibold">MR</div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white text-sm font-medium">Marco R.</div>
                                        <div class="text-amber-600 dark:text-amber-300 text-xs">Table for 8 &bull; $500 min</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-amber-500/30 text-amber-600 dark:text-amber-300 text-[10px] font-medium">VIP</span>
                                </div>
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-100 dark:bg-white/5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-[10px] font-semibold">JT</div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">James T.</div>
                                        <div class="text-gray-500 text-xs">Birthday party &bull; 12 guests</div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-pink-500/30 text-pink-600 dark:text-pink-300 text-[10px] font-medium">BDAY</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DJ Booking Requests -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900 dark:to-violet-900 border border-indigo-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        DJ Inbox
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">DJs come to you</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Artists submit to play your club. See their SoundCloud, genre, and past gigs. Accept or pass - no endless email threads.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-200 dark:bg-indigo-500/20 border border-indigo-400/30">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white text-xs font-semibold">KR</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm font-medium">DJ Kira</div>
                                <div class="text-indigo-600 dark:text-indigo-300 text-[10px]">Tech House &bull; SoundCloud linked</div>
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
                    </div>
                </div>

                <!-- Lineup Graphics for Social -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-violet-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Social Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Insta-ready lineups</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Auto-generate lineup graphics for Instagram stories and posts. Your DJs, your branding, ready to share.</p>

                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-28 h-36 bg-gradient-to-br from-violet-600 to-purple-700 rounded-xl p-3 shadow-xl border border-violet-400/30">
                                <div class="text-[8px] text-violet-200 font-bold mb-1">SATURDAY</div>
                                <div class="text-[6px] text-violet-300/70 mb-2">CLUB VORTEX</div>
                                <div class="space-y-1">
                                    <div class="bg-white/20 rounded px-1 py-0.5 text-[6px] text-white font-medium">DJ NOVA</div>
                                    <div class="bg-white/10 rounded px-1 py-0.5 text-[6px] text-violet-200">MAX LUNA</div>
                                    <div class="bg-white/10 rounded px-1 py-0.5 text-[6px] text-violet-200">KIRA B2B ECHO</div>
                                </div>
                                <div class="absolute bottom-2 right-2">
                                    <svg class="w-4 h-4 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-gradient-to-br from-pink-500 to-fuchsia-500 rounded-lg flex items-center justify-center shadow-lg">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sold Out Saturdays (Ticketing) -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-pink-100 dark:from-rose-900 dark:to-pink-900 border border-rose-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sold out Saturdays</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">NYE. Headliner shows. Theme party takeovers. Sell tickets, scan QR codes at the door. Zero platform fees.</p>

                    <div class="flex items-center gap-3">
                        <div class="flex-1 bg-rose-200 dark:bg-rose-500/20 rounded-xl border border-rose-400/30 p-3">
                            <div class="text-[10px] text-rose-600 dark:text-rose-300 font-semibold mb-1">NYE {{ date('Y') + 1 }}</div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900 dark:text-white text-sm font-bold">342</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs">tickets sold</span>
                            </div>
                            <div class="w-full h-1.5 bg-rose-500/30 rounded-full mt-2 overflow-hidden">
                                <div class="w-[85%] h-full bg-gradient-to-r from-rose-500 to-pink-500 rounded-full"></div>
                            </div>
                            <div class="text-rose-600 dark:text-rose-300 text-[10px] mt-1">Almost sold out</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- The Club Weekend Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    The club weekend
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                    Clubs run on a rhythm. Thursday builds momentum, Friday brings the theme, Saturday's the headliner. Show your crowd what's coming.
                </p>
            </div>

            <!-- Thu-Fri-Sat progression -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Thursday -->
                <div class="bg-gradient-to-br from-fuchsia-100 to-purple-100 dark:from-fuchsia-900/40 dark:to-purple-900/40 rounded-2xl border border-fuchsia-200 dark:border-fuchsia-500/30 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-fuchsia-100 dark:bg-fuchsia-500/20 rounded-xl flex items-center justify-center">
                            <span class="text-fuchsia-600 dark:text-fuchsia-400 text-lg font-bold">THU</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Industry Night</h3>
                            <p class="text-fuchsia-600 dark:text-fuchsia-300 text-sm">Service industry</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-fuchsia-400"></div>
                            <span>Lower cover / free entry</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-fuchsia-400"></div>
                            <span>Late night crowd arrives late</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-fuchsia-400"></div>
                            <span>Resident DJs on rotation</span>
                        </div>
                    </div>
                </div>

                <!-- Friday -->
                <div class="bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900/40 dark:to-amber-900/40 rounded-2xl border border-orange-200 dark:border-orange-500/30 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-500/20 rounded-xl flex items-center justify-center">
                            <span class="text-orange-600 dark:text-orange-400 text-lg font-bold">FRI</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Themed Night</h3>
                            <p class="text-orange-600 dark:text-orange-300 text-sm">Latin, Hip-Hop, 80s</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span>Branded recurring night</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span>Loyal theme-specific crowd</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span>Guest list before 11pm</span>
                        </div>
                    </div>
                </div>

                <!-- Saturday -->
                <div class="bg-gradient-to-br from-pink-100 to-rose-100 dark:from-pink-900/40 dark:to-rose-900/40 rounded-2xl border border-pink-200 dark:border-pink-500/30 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-pink-100 dark:bg-pink-500/20 rounded-xl flex items-center justify-center">
                            <span class="text-pink-600 dark:text-pink-400 text-lg font-bold">SAT</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Headliner Night</h3>
                            <p class="text-pink-600 dark:text-pink-300 text-sm">Touring DJ, premium</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-pink-400"></div>
                            <span>Premium pricing / ticketed</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-pink-400"></div>
                            <span>VIP tables sell out</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-pink-400"></div>
                            <span>Guest headliner draw</span>
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
                    Perfect for all types of clubs
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From underground warehouses to rooftop lounges, Event Schedule fits your vibe.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Dance Clubs & EDM Venues -->
                <x-sub-audience-card
                    name="Dance Clubs & EDM Venues"
                    description="House, techno, trance crowds. Big rooms, bigger sound systems, and lineups that matter."
                    icon-color="pink"
                    blog-slug="for-dance-clubs-edm"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Hip-Hop & Urban Clubs -->
                <x-sub-audience-card
                    name="Hip-Hop & Urban Clubs"
                    description="Hip-hop nights, R&B showcases, urban music events. Build your scene's go-to spot."
                    icon-color="fuchsia"
                    blog-slug="for-hip-hop-clubs"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Latin Clubs -->
                <x-sub-audience-card
                    name="Latin Clubs"
                    description="Salsa, bachata, reggaeton communities. Themed nights that keep dancers coming back."
                    icon-color="orange"
                    blog-slug="for-latin-clubs"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Rooftop Clubs -->
                <x-sub-audience-card
                    name="Rooftop Clubs"
                    description="Sunset sessions, seasonal programming, skyline views. Weather-dependent vibes done right."
                    icon-color="violet"
                    blog-slug="for-rooftop-clubs"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Underground & Warehouse Venues -->
                <x-sub-audience-card
                    name="Underground & Warehouse"
                    description="Intimate sets, warehouse parties, curated crowds. Where the real heads gather."
                    icon-color="indigo"
                    blog-slug="for-underground-clubs"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- VIP Lounges -->
                <x-sub-audience-card
                    name="VIP Lounges"
                    description="Bottle service focused, upscale nightlife, exclusive atmosphere. Premium experiences only."
                    icon-color="amber"
                    blog-slug="for-vip-lounges"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- Stream to the World Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-pink-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-fuchsia-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-pink-900 to-fuchsia-900 rounded-3xl border border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-3 group-hover:text-pink-300 transition-colors">Stream to the world</h3>
                            <p class="text-gray-400 text-lg mb-4">Broadcast your DJ sets worldwide. Sell tickets to viewers anywhere - no capacity limits.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Twitch</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">YouTube Live</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Zoom</span>
                            </div>
                            <span class="inline-flex items-center text-pink-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Live Stream</span>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                        <span class="text-red-500 text-xs font-medium">LIVE</span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">2,341 watching</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Global audience</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
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
                    Get your club's calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-fuchsia-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Set up your club</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add your name, rooms (main floor, rooftop, VIP), and upload your logo. Takes two minutes.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-fuchsia-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your lineup</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Resident DJs, guest headliners, themed nights. Set recurring events or add one-offs as bookings come in.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-fuchsia-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Let your crowd follow</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Share your link. Clubbers follow. They get the week's lineup in their inbox - no checking Instagram required.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-pink-600 to-fuchsia-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <!-- Glow effects -->
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-violet-500/20 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-pink-500/20 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your crowd. Direct reach. Pack the floor.
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Email your crowd directly. Fill your dancefloor. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-pink-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Nightclubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Nightclub Event Management Software",
        "operatingSystem": "Web",
        "description": "Fill your club's calendar with DJ lineups and themed nights. Email your crowd directly - no paying for ads. Sell tickets with QR check-in, accept booking requests from DJs.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly lineup blast newsletter",
            "Guest list signups and management",
            "VIP table and bottle service requests",
            "DJ booking inbox with SoundCloud links",
            "Auto-generate lineup graphics for social",
            "Ticketed events with QR check-in",
            "Themed night scheduling"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-nightclub {
            background: linear-gradient(135deg, #ec4899, #d946ef, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

    </style>
</x-marketing-layout>
