<x-marketing-layout>
    <x-slot name="title">Event Schedule for Bars & Pubs | Fill Your Event Calendar</x-slot>
    <x-slot name="description">Fill your bar's calendar with great events. Email your regulars directly - no algorithm. Sell tickets, accept booking requests. Free forever.</x-slot>
    <x-slot name="keywords">bar event calendar, pub events, bar booking system, craft beer bar events, wine bar calendar, sports bar events, bar newsletter, bar email marketing, trivia night software</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <!-- Animated neon OPEN sign -->
        <div class="absolute top-16 right-8 md:right-16 animate-neon-flicker opacity-90 hidden sm:block">
            <div class="px-6 py-3 rounded-lg border-2 border-amber-500 dark:border-amber-400 bg-amber-500/10 shadow-lg shadow-amber-500/20">
                <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 tracking-widest neon-text">OPEN</span>
            </div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Bars, Pubs & Taprooms</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                <span class="text-gradient-amber">Your bar.</span><br>
                Your crowd.
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Stop paying to reach your own regulars. Email your regulars directly, announce what's on, and fill your bar - without begging the algorithm.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-amber-600 to-orange-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                    Create your bar's calendar
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Bar type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-500/30">Craft Beer</span>
                <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-xs font-medium border border-rose-200 dark:border-rose-500/30">Wine Bar</span>
                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300 text-xs font-medium border border-green-200 dark:border-green-500/30">Sports Bar</span>
                <span class="px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-xs font-medium border border-violet-200 dark:border-violet-500/30">Cocktail Lounge</span>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-xs font-medium border border-emerald-200 dark:border-emerald-500/30">Irish Pub</span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300 text-xs font-medium border border-slate-200 dark:border-slate-500/30">Dive Bar</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Weekly Regulars Email - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Tell your regulars what's on</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">No algorithm. No pay-to-play. Wednesday trivia's back? New band this Friday? One click sends the week's lineup straight to everyone who wants to know.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Skip the algorithm</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Your crowd, your reach</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-950 dark:to-orange-950 rounded-2xl border border-amber-300 dark:border-amber-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-500 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">This Week at The Tap Room</div>
                                            <div class="text-amber-600 dark:text-amber-300 text-xs">Sending to 847 followers...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Wed - Trivia Night</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Fri - Live Jazz</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Sat - IPA Release Party</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Your Bar's Rhythm -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-amber-100 dark:from-orange-900 dark:to-amber-900 border border-orange-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/20 text-orange-700 dark:text-orange-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring Events
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Trivia Wednesdays. Jazz Fridays.</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Bars run on weekly rhythms. Set up recurring events once, they show automatically.</p>

                    <!-- Mini week view -->
                    <div class="grid grid-cols-7 gap-1">
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">M</div>
                            <div class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/5 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">T</div>
                            <div class="w-6 h-6 rounded-full bg-amber-500/30 border border-amber-400/50 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-orange-700 dark:text-orange-300 text-[10px] mb-1 font-medium">W</div>
                            <div class="w-6 h-6 rounded-full bg-orange-500 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">T</div>
                            <div class="w-6 h-6 rounded-full bg-amber-500/30 border border-amber-400/50 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-orange-700 dark:text-orange-300 text-[10px] mb-1 font-medium">F</div>
                            <div class="w-6 h-6 rounded-full bg-orange-500 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-orange-700 dark:text-orange-300 text-[10px] mb-1 font-medium">S</div>
                            <div class="w-6 h-6 rounded-full bg-orange-500 mx-auto"></div>
                        </div>
                        <div class="text-center">
                            <div class="text-gray-500 dark:text-white/70 text-[10px] mb-1">S</div>
                            <div class="w-6 h-6 rounded-full bg-amber-500/30 border border-amber-400/50 mx-auto"></div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-gray-500 dark:text-white/70 text-xs">Weekly events auto-repeat</div>
                </div>

                <!-- Tap Takeovers & Specials -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Specials
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Announce what's pouring</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">New keg? Brewery collab? Whiskey tasting? Let your crowd know what's special this week.</p>

                    <!-- Tap handles visual -->
                    <div class="flex justify-center gap-3">
                        <div class="w-8 h-16 bg-gradient-to-b from-amber-600 to-amber-800 rounded-t-full rounded-b-lg border border-amber-500/50 relative">
                            <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-amber-400 rounded-full"></div>
                        </div>
                        <div class="w-8 h-16 bg-gradient-to-b from-orange-600 to-orange-800 rounded-t-full rounded-b-lg border border-orange-500/50 relative">
                            <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-orange-400 rounded-full"></div>
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-1.5 py-0.5 bg-emerald-500 rounded text-[8px] text-white font-bold">NEW</div>
                        </div>
                        <div class="w-8 h-16 bg-gradient-to-b from-yellow-600 to-yellow-800 rounded-t-full rounded-b-lg border border-yellow-500/50 relative">
                            <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-4 h-4 bg-yellow-400 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Watch Parties & Game Days (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 border border-green-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-500/20 text-green-700 dark:text-green-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Watch Parties
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Game on the big screen</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-4">NFL Sundays, UFC fight nights, World Cup matches. Your sports fans follow to know what's showing and when.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Live sports</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Watch parties</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Big screen events</span>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <!-- TV screen visual -->
                            <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border-4 border-gray-300 dark:border-gray-700 p-4 w-48">
                                <div class="bg-gradient-to-br from-green-600/30 to-emerald-600/30 rounded-lg p-3 text-center">
                                    <div class="text-3xl mb-1">&#9917;</div>
                                    <div class="text-green-600 dark:text-green-300 text-sm font-bold">LIVE</div>
                                    <div class="text-gray-500 dark:text-white/70 text-xs">Sunday 4pm</div>
                                </div>
                                <div class="flex justify-center gap-1 mt-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-600"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticketed Events -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-pink-100 dark:from-rose-900 dark:to-pink-900 border border-rose-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-700 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Charge at the door</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Comedy night? Special tasting? Sell tickets, scan QR codes at entry. Zero platform fees.</p>

                    <div class="flex justify-center">
                        <div class="w-20 h-20 bg-white rounded-xl p-2 relative">
                            <div class="w-full h-full bg-[url('data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2029%2029%22%3E%3Cpath%20d%3D%22M0%200h7v7H0zm2%202v3h3V2zm8%200h1v1h1v1h-1v1h-1V3h-1V2h1zm4%200h1v4h-1V4h-1V3h1V2zm4%200h3v1h-2v1h-1V2zm5%200h7v7h-7zm2%202v3h3V4zM2%2010h1v1h1v1H2v-1H1v-1h1zm4%200h1v1H5v1H4v-1h1v-1h1zm3%200h1v3h1v1h-1v-1H9v-1h1v-1H9v-1zm5%200h1v2h1v-2h1v3h-1v1h-1v-1h-1v-1h-1v-1h1v-1zm5%200h1v1h-1v1h-1v-1h1v-1zm3%200h1v2h1v-1h1v3h-1v-1h-1v2h-1v-3h-1v-1h1v-1zM0%2014h1v1h1v-1h2v1h-1v1h1v2H3v-2H2v-1H0v-1zm4%200h1v1H4v-1zm9%200h1v1h-1v-1zm8%200h2v1h-2v-1zm0%202v1h1v1h1v1h-1v1h1v1h-2v-2h-1v-1h1v-1h-1v-1h1zm4%200h1v1h-1v-1zM0%2018h1v1H0v-1zm2%200h2v1h1v2H4v-1H3v1H2v-2h1v-1H2v-1zm5%200h3v1h1v2h-1v1h-1v-2H8v1H7v-1H6v-1h1v-1zm6%200h2v1h1v-1h1v2h-2v1h-1v-2h-1v-1zm-5%202h1v1H8v-1zM0%2022h7v7H0zm2%202v3h3v-3zm9-2h1v1h-1v-1zm2%200h1v1h1v2h-2v-1h-1v-1h1v-1zm3%200h3v1h-2v2h2v1h2v2h-1v1h-2v-1h-1v1h-2v-2h1v-2h-1v-2h1v-1zm7%200h1v1h1v1h-1v3h1v-2h1v3h1v-1h1v2h-2v1h-1v-1h-1v-1h-1v1h-2v-1h1v-2h1v-1h-1v-2h1v-1zm-9%202h1v1h-1v-1zm-2%202h1v1h-1v-1zm7%200h1v1h-1v-1zm-5%202h1v1h-1v-1zm2%200h2v1h-2v-1z%22%2F%3E%3C%2Fsvg%3E')] bg-contain"></div>
                            <div class="absolute inset-x-0 top-2 h-0.5 bg-gradient-to-r from-rose-500 to-pink-500 animate-pulse"></div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-gray-500 dark:text-white/70 text-xs">Scan to check in</div>
                </div>

                <!-- Performers Request to Play -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900 dark:to-violet-900 border border-indigo-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Booking Inbox
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Bands come to you</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Musicians and DJs request to play. Review, approve, book - without the back-and-forth.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-500/20 border border-indigo-400/30">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white text-xs font-semibold">BT</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Blues Trio</div>
                                <div class="text-indigo-700 dark:text-indigo-300 text-xs">Requesting Sat night</div>
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

                <!-- What's Working -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">See what fills seats</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Which nights draw crowds? What events get shares? Know what's working.</p>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-white/70 text-xs">Trivia</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-2 bg-cyan-500/30 rounded-full overflow-hidden">
                                    <div class="w-[90%] h-full bg-cyan-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-white/70 text-xs">Live Music</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-2 bg-cyan-500/30 rounded-full overflow-hidden">
                                    <div class="w-[65%] h-full bg-cyan-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-white/70 text-xs">Karaoke</span>
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-2 bg-cyan-500/30 rounded-full overflow-hidden">
                                    <div class="w-[45%] h-full bg-cyan-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Your Week at a Glance Section - UNIQUE TO BARS -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Your week at a glance
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Bars run on rhythm. Weekly trivia, Friday bands, Sunday brunch - your regulars know the schedule by heart. Show them what's on, every week.
                </p>
            </div>

            <!-- Weekly Calendar Visual -->
            <div class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-3xl border border-amber-200 dark:border-white/10 p-6 md:p-8">
                <div class="grid grid-cols-7 gap-2 md:gap-4">
                    <!-- Monday -->
                    <div class="text-center">
                        <div class="text-gray-600 dark:text-gray-500 text-xs md:text-sm font-medium mb-2 md:mb-3">Mon</div>
                        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-2 md:p-3 min-h-[80px] md:min-h-[100px]">
                            <div class="text-amber-400 text-[10px] md:text-xs font-medium">Industry Night</div>
                            <div class="text-gray-600 dark:text-gray-500 text-[9px] md:text-[10px] mt-1">50% off for service workers</div>
                        </div>
                    </div>
                    <!-- Tuesday -->
                    <div class="text-center">
                        <div class="text-gray-600 dark:text-gray-500 text-xs md:text-sm font-medium mb-2 md:mb-3">Tue</div>
                        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-2 md:p-3 min-h-[80px] md:min-h-[100px]">
                            <div class="text-orange-400 text-[10px] md:text-xs font-medium">Taco Tuesday</div>
                            <div class="text-gray-600 dark:text-gray-500 text-[9px] md:text-[10px] mt-1">$2 tacos all night</div>
                        </div>
                    </div>
                    <!-- Wednesday - Highlighted -->
                    <div class="text-center">
                        <div class="text-amber-400 text-xs md:text-sm font-bold mb-2 md:mb-3">Wed</div>
                        <div class="bg-gradient-to-br from-amber-500/30 to-orange-500/30 rounded-xl p-2 md:p-3 min-h-[80px] md:min-h-[100px] border border-amber-500/50 relative">
                            <div class="absolute -top-2 right-1 md:right-2 px-1.5 py-0.5 bg-amber-500 rounded text-[8px] md:text-[9px] text-white font-bold">POPULAR</div>
                            <div class="text-amber-300 text-[10px] md:text-xs font-semibold">Trivia Night</div>
                            <div class="text-amber-200/70 text-[9px] md:text-[10px] mt-1">7pm start</div>
                        </div>
                    </div>
                    <!-- Thursday -->
                    <div class="text-center">
                        <div class="text-gray-600 dark:text-gray-500 text-xs md:text-sm font-medium mb-2 md:mb-3">Thu</div>
                        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-2 md:p-3 min-h-[80px] md:min-h-[100px]">
                            <div class="text-violet-400 text-[10px] md:text-xs font-medium">Open Mic</div>
                            <div class="text-gray-600 dark:text-gray-500 text-[9px] md:text-[10px] mt-1">Sign up 6pm</div>
                        </div>
                    </div>
                    <!-- Friday -->
                    <div class="text-center">
                        <div class="text-orange-300 text-xs md:text-sm font-bold mb-2 md:mb-3">Fri</div>
                        <div class="bg-gradient-to-br from-orange-950 to-rose-950 rounded-xl p-2 md:p-3 min-h-[80px] md:min-h-[100px] border border-orange-500/30">
                            <div class="text-orange-300 text-[10px] md:text-xs font-semibold">Live Music</div>
                            <div class="text-orange-200/70 text-[9px] md:text-[10px] mt-1">9pm-12am</div>
                        </div>
                    </div>
                    <!-- Saturday -->
                    <div class="text-center">
                        <div class="text-orange-300 text-xs md:text-sm font-bold mb-2 md:mb-3">Sat</div>
                        <div class="bg-gradient-to-br from-rose-950 to-pink-950 rounded-xl p-2 md:p-3 min-h-[80px] md:min-h-[100px] border border-rose-500/30">
                            <div class="text-rose-300 text-[10px] md:text-xs font-semibold">DJ Night</div>
                            <div class="text-rose-200/70 text-[9px] md:text-[10px] mt-1">10pm-2am</div>
                        </div>
                    </div>
                    <!-- Sunday -->
                    <div class="text-center">
                        <div class="text-gray-600 dark:text-gray-500 text-xs md:text-sm font-medium mb-2 md:mb-3">Sun</div>
                        <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-2 md:p-3 min-h-[80px] md:min-h-[100px]">
                            <div class="text-yellow-400 text-[10px] md:text-xs font-medium">Brunch</div>
                            <div class="text-gray-600 dark:text-gray-500 text-[9px] md:text-[10px] mt-1">Bloody Mary bar</div>
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
                    Perfect for all types of bars
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From craft beer taprooms to cocktail lounges, Event Schedule fits your vibe.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Craft Beer Bars -->
                <x-sub-audience-card
                    name="Craft Beer Bars"
                    description="Tap takeovers, brewery events, and beer release parties. Build a following of craft beer enthusiasts."
                    icon-color="amber"
                    blog-slug="for-craft-beer-bars"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Wine Bars -->
                <x-sub-audience-card
                    name="Wine Bars"
                    description="Wine tastings, vineyard dinners, and sommelier events. Educate and delight your wine-loving guests."
                    icon-color="rose"
                    blog-slug="for-wine-bars"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sports Bars -->
                <x-sub-audience-card
                    name="Sports Bars"
                    description="Game day watch parties, trivia nights, and UFC events. Let fans know what's on the big screen."
                    icon-color="green"
                    blog-slug="for-sports-bars"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cocktail Lounges -->
                <x-sub-audience-card
                    name="Cocktail Lounges"
                    description="Mixology classes, speakeasy nights, and cocktail competitions. Attract the craft cocktail crowd."
                    icon-color="violet"
                    blog-slug="for-cocktail-lounges"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Irish & British Pubs -->
                <x-sub-audience-card
                    name="Irish & British Pubs"
                    description="Pub quizzes, live traditional music, and St. Patrick's Day celebrations. Keep the craic alive."
                    icon-color="emerald"
                    blog-slug="for-irish-british-pubs"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Dive Bars & Neighborhood Bars -->
                <x-sub-audience-card
                    name="Dive Bars & Neighborhood Bars"
                    description="Open mics, karaoke nights, and local band showcases. Your neighborhood's living room."
                    icon-color="slate"
                    blog-slug="for-dive-bars"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
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
                    Get your bar's calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Set up your bar</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add your name, spaces (main bar, patio, back room), and upload your logo. Takes two minutes.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your weekly lineup</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Trivia night, live music, tap takeovers, watch parties. Set recurring events once, or add one-offs as they come.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Let regulars follow</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Share your link. Locals follow. They get the week's events in their inbox - no checking Facebook required.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-amber-600 to-orange-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Stop paying to reach your own regulars
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Email your regulars directly. Fill your bar. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-amber-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Bars & Pubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Bar Event Management Software",
        "operatingSystem": "Web",
        "description": "Fill your bar's calendar with great events. Email your regulars directly - no paying for Facebook ads. Sell tickets with QR check-in, accept booking requests from performers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Direct newsletter to regulars - no algorithm middleman",
            "Accept booking requests from performers",
            "Recurring weekly events",
            "Watch parties and sports events",
            "Tap takeovers and specials announcements",
            "QR code ticketing with zero platform fees",
            "Event analytics and tracking"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-amber {
            background: linear-gradient(135deg, #f59e0b, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Neon flicker animation for OPEN sign */
        @keyframes neon-flicker {
            0%, 19%, 21%, 23%, 25%, 54%, 56%, 100% {
                opacity: 1;
                text-shadow: 0 0 10px #f59e0b, 0 0 20px #f59e0b, 0 0 30px #f59e0b;
                box-shadow: 0 0 10px rgba(245, 158, 11, 0.3), 0 0 20px rgba(245, 158, 11, 0.2);
            }
            20%, 24%, 55% {
                opacity: 0.7;
                text-shadow: none;
                box-shadow: none;
            }
        }

        .animate-neon-flicker {
            animation: neon-flicker 3s infinite;
        }

        .neon-text {
            text-shadow: 0 0 10px #f59e0b, 0 0 20px #f59e0b, 0 0 30px #f59e0b;
        }
    </style>
</x-marketing-layout>
