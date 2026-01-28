<x-marketing-layout>
    <x-slot name="title">Event Schedule for Comedy Clubs | Lineup Calendar & Ticketing Software</x-slot>
    <x-slot name="description">Be the stage where comedy careers are made. Manage lineups, sell tickets with zero fees, and build your audience. Free forever.</x-slot>
    <x-slot name="keywords">comedy club software, comedy club calendar, comedy club ticketing, stand-up comedy scheduling, improv theater software, open mic management, comedy club newsletter, comedian booking system, comedy lineup builder</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section - Comedy Club with Neon & Stage Curtains -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Stage curtain effect - left side -->
        <div class="absolute left-0 top-0 bottom-0 w-32 md:w-48 bg-gradient-to-r from-red-950/80 via-red-900/20 to-transparent"></div>
        <div class="absolute left-0 top-0 bottom-0 w-16 md:w-24 bg-gradient-to-r from-red-950 to-transparent opacity-60"></div>

        <!-- Stage curtain effect - right side -->
        <div class="absolute right-0 top-0 bottom-0 w-32 md:w-48 bg-gradient-to-l from-red-950/80 via-red-900/20 to-transparent"></div>
        <div class="absolute right-0 top-0 bottom-0 w-16 md:w-24 bg-gradient-to-l from-red-950 to-transparent opacity-60"></div>

        <!-- Neon glow effects -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-amber-500/10 rounded-full blur-[150px]"></div>
        <div class="absolute top-1/3 left-1/3 w-[300px] h-[300px] bg-pink-500/10 rounded-full blur-[120px] animate-pulse-slow"></div>
        <div class="absolute top-1/3 right-1/3 w-[300px] h-[300px] bg-amber-500/10 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1s;"></div>

        <!-- Subtle stage floor reflection -->
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-amber-900/10 to-transparent"></div>

        <!-- Grid pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.015)_1px,transparent_1px)] bg-[size:60px_60px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Neon sign style badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-200 dark:bg-[#0f0f14] border border-gray-200 dark:border-white/10 mb-8 shadow-[0_0_15px_rgba(251,191,36,0.3)]">
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Comedy Clubs & Improv Theaters</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                <span class="text-gray-900 dark:text-white">Running a comedy club is hard.</span>
            </h1>

            <p class="text-2xl md:text-3xl text-gradient-comedy mb-8 font-semibold">
                Your software shouldn't be the joke.
            </p>

            <p class="text-lg md:text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-12">
                Two drink minimum. Zero booking hassle. From open mic to Netflix special.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-black bg-gradient-to-r from-amber-400 to-yellow-500 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                    Create your club's calendar
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Genre tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-500/30">Stand-up</span>
                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-xs font-medium border border-yellow-500/30">Improv</span>
                <span class="px-3 py-1 rounded-full bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-300 text-xs font-medium border border-lime-500/30">Open Mic</span>
                <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-xs font-medium border border-orange-500/30">Showcase</span>
                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300 text-xs font-medium border border-red-500/30">Headliner</span>
                <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-xs font-medium border border-purple-500/30">Recording Night</span>
            </div>
        </div>
    </section>

    <!-- The Comedy Club Week Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-3">The Comedy Club Week</h2>
                <p class="text-gray-500 dark:text-gray-400">Your weekly rhythm, automated. Set it once, it runs forever.</p>
            </div>

            <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-800/30 rounded-2xl border border-gray-200 dark:border-white/10 p-6 overflow-x-auto">
                <div class="flex gap-3 min-w-[700px]">
                    <!-- Monday -->
                    <div class="flex-1 text-center p-4 rounded-xl bg-gray-200/50 dark:bg-gray-800/30 border border-gray-300/30 dark:border-gray-700/30">
                        <div class="text-gray-500 text-xs font-semibold mb-2">MON</div>
                        <div class="w-3 h-3 rounded-full bg-gray-400 dark:bg-gray-700 mx-auto mb-2"></div>
                        <div class="text-gray-600 text-xs">Dark</div>
                    </div>
                    <!-- Tuesday -->
                    <div class="flex-1 text-center p-4 rounded-xl bg-lime-900/30 border border-lime-700/30">
                        <div class="text-lime-400 text-xs font-semibold mb-2">TUE</div>
                        <div class="w-3 h-3 rounded-full bg-lime-500 mx-auto mb-2 animate-pulse"></div>
                        <div class="text-lime-300 text-xs font-medium">Open Mic</div>
                        <div class="text-gray-500 text-[10px] mt-1">New comics cut their teeth</div>
                    </div>
                    <!-- Wednesday -->
                    <div class="flex-1 text-center p-4 rounded-xl bg-blue-900/30 border border-blue-700/30">
                        <div class="text-blue-400 text-xs font-semibold mb-2">WED</div>
                        <div class="w-3 h-3 rounded-full bg-blue-500 mx-auto mb-2"></div>
                        <div class="text-blue-300 text-xs font-medium">Improv Jam</div>
                        <div class="text-gray-500 text-[10px] mt-1">Ensemble magic</div>
                    </div>
                    <!-- Thursday -->
                    <div class="flex-1 text-center p-4 rounded-xl bg-amber-900/30 border border-amber-700/30">
                        <div class="text-amber-400 text-xs font-semibold mb-2">THU</div>
                        <div class="w-3 h-3 rounded-full bg-amber-500 mx-auto mb-2"></div>
                        <div class="text-amber-300 text-xs font-medium">New Talent</div>
                        <div class="text-gray-500 text-[10px] mt-1">Rising stars</div>
                    </div>
                    <!-- Friday -->
                    <div class="flex-1 text-center p-4 rounded-xl bg-yellow-900/40 border border-yellow-600/40">
                        <div class="text-yellow-400 text-xs font-semibold mb-2">FRI</div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400 mx-auto mb-2 shadow-lg shadow-yellow-500/50"></div>
                        <div class="text-yellow-300 text-xs font-medium">Headliner</div>
                        <div class="text-gray-500 text-[10px] mt-1">The draw</div>
                    </div>
                    <!-- Saturday -->
                    <div class="flex-1 text-center p-4 rounded-xl bg-yellow-900/40 border border-yellow-600/40">
                        <div class="text-yellow-400 text-xs font-semibold mb-2">SAT</div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400 mx-auto mb-2 shadow-lg shadow-yellow-500/50"></div>
                        <div class="text-yellow-300 text-xs font-medium">Headliner</div>
                        <div class="text-gray-500 text-[10px] mt-1">Sold out shows</div>
                    </div>
                    <!-- Sunday -->
                    <div class="flex-1 text-center p-4 rounded-xl bg-purple-900/30 border border-purple-700/30">
                        <div class="text-purple-400 text-xs font-semibold mb-2">SUN</div>
                        <div class="w-3 h-3 rounded-full bg-purple-500 mx-auto mb-2"></div>
                        <div class="text-purple-300 text-xs font-medium">Industry</div>
                        <div class="text-gray-500 text-[10px] mt-1">Podcast / Special</div>
                    </div>
                </div>
            </div>
            <p class="text-center text-gray-500 dark:text-gray-500 text-sm mt-4">Recurring events auto-populate your calendar. One setup, endless shows.</p>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Lineup Builder - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-amber-500/30 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Lineup Builder
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Announce your lineup, build the buzz</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Feature your headliner, middle act, and host. Add their videos, link their profiles. Fans see who's on stage before they buy.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Multiple performers</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Video embeds</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Profile links</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-amber-950 to-yellow-950 rounded-2xl border border-amber-400/30 p-4 max-w-xs">
                                    <div class="text-[10px] text-amber-300 mb-3 font-semibold tracking-wide">FRIDAY NIGHT LINEUP</div>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-amber-500/20 border border-amber-400/30">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-yellow-500 flex items-center justify-center text-white text-xs font-bold">SC</div>
                                            <div class="flex-1">
                                                <div class="text-gray-900 dark:text-white text-sm font-semibold">Sarah Chen</div>
                                                <div class="text-amber-300 text-xs">Headliner - 45 min</div>
                                            </div>
                                            <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-xs font-bold">MR</div>
                                            <div class="flex-1">
                                                <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Mike Rodriguez</div>
                                                <div class="text-gray-500 dark:text-gray-500 text-xs">Feature - 20 min</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-500 to-gray-600 flex items-center justify-center text-white text-xs font-bold">TK</div>
                                            <div class="flex-1">
                                                <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Tony Kim</div>
                                                <div class="text-gray-500 dark:text-gray-500 text-xs">MC / Host</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Open Mic Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900 dark:to-green-900 border border-lime-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-lime-100 text-lime-700 dark:bg-lime-500/20 dark:text-lime-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        Open Mic
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Tuesday is sacred</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Your open mic runs itself. Comics sign up, you approve the list, they get their 5 minutes.</p>

                    <!-- Sign-up list mockup -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-lime-500/20 border border-lime-500/30">
                            <span class="text-gray-600 dark:text-gray-300 text-xs flex-1">Jamie Lee</span>
                            <div class="flex gap-1">
                                <div class="w-5 h-5 rounded bg-emerald-500/30 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <div class="w-5 h-5 rounded bg-red-500/30 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <span class="text-gray-500 dark:text-gray-400 text-xs flex-1">Alex Torres</span>
                            <span class="text-lime-400 text-[10px]">Approved</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <span class="text-gray-500 dark:text-gray-400 text-xs flex-1">Sam Park</span>
                            <span class="text-lime-400 text-[10px]">Approved</span>
                        </div>
                    </div>
                </div>

                <!-- Comic Booking Requests with Video -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-teal-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Booking Inbox
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Comics pitch themselves</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">They submit their clips, you watch and decide. No cold emails, no back-and-forth.</p>

                    <div class="bg-teal-500/20 rounded-xl border border-teal-400/30 p-3">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center text-white text-xs font-semibold">JL</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Jamie Lee</div>
                                <div class="text-teal-300 text-xs">Wants a showcase spot</div>
                            </div>
                        </div>
                        <div class="relative bg-gray-200 dark:bg-[#0f0f14] rounded-lg overflow-hidden aspect-video mb-3">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <div class="absolute bottom-1 right-1 text-[10px] text-white/70 bg-black/50 px-1 rounded">3:42</div>
                        </div>
                        <button class="w-full py-1.5 text-xs font-medium text-teal-300 bg-teal-500/20 rounded-lg border border-teal-500/30 hover:bg-teal-500/30 transition-colors">
                            Watch Clip
                        </button>
                    </div>
                </div>

                <!-- Zero-Fee Ticketing (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 border border-green-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Zero-Fee Ticketing
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Keep 100% of ticket sales</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">GA, VIP front row, two-drink minimum - however you price it. Scan QR codes at the door. No platform taking a cut.</p>
                            <div class="flex flex-wrap gap-3 mt-4">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Multiple ticket types</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">QR scanning</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Direct to Stripe</span>
                            </div>
                        </div>
                        <div class="flex justify-center">
                            <div class="space-y-3 w-full max-w-xs">
                                <div class="bg-gradient-to-r from-green-950 to-emerald-950 rounded-xl p-4 border border-green-500/30">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-gray-900 dark:text-white font-medium">General Admission</span>
                                        <span class="text-green-300 font-bold">$20</span>
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-500 text-xs">Standard seating</div>
                                </div>
                                <div class="bg-gradient-to-r from-amber-950 to-yellow-950 rounded-xl p-4 border border-amber-500/30">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-gray-900 dark:text-white font-medium">VIP Front Row</span>
                                        <span class="text-amber-300 font-bold">$35</span>
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-500 text-xs">Best seats + drink voucher</div>
                                </div>
                                <div class="bg-gradient-to-r from-purple-950 to-pink-950 rounded-xl p-4 border border-purple-500/30">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-gray-900 dark:text-white font-medium">Recording Night</span>
                                        <span class="text-purple-300 font-bold">$40</span>
                                    </div>
                                    <div class="text-gray-500 dark:text-gray-500 text-xs">Special taping event</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Weekly Lineup Email -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-yellow-100 to-amber-100 dark:from-yellow-900 dark:to-amber-900 border border-yellow-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Newsletter
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One click, every fan knows</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Thursday afternoon: send this week's lineup to everyone who follows you. No algorithm decides who sees it.</p>

                    <div class="bg-yellow-500/20 rounded-xl border border-yellow-400/30 p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-6 h-6 bg-gradient-to-br from-yellow-500 to-amber-500 rounded flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" />
                                </svg>
                            </div>
                            <span class="text-gray-900 dark:text-white text-xs font-medium">This Week at The Comedy Cellar</span>
                        </div>
                        <div class="text-yellow-300 text-[10px] mb-2">Sending to 2,341 followers...</div>
                        <div class="h-1 bg-yellow-900/50 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full animate-pulse" style="width: 75%"></div>
                        </div>
                    </div>
                </div>

                <!-- Show Analytics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Know what fills seats</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Headliners vs. showcases. Friday vs. Saturday. See which comics draw crowds.</p>

                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-500 dark:text-gray-400 text-xs">Headliner Shows</span>
                                <span class="text-cyan-300 text-xs font-medium">94%</span>
                            </div>
                            <div class="w-full h-2 bg-cyan-500/20 rounded-full overflow-hidden">
                                <div class="w-[94%] h-full bg-gradient-to-r from-cyan-500 to-cyan-400 rounded-full"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-500 dark:text-gray-400 text-xs">Showcase Nights</span>
                                <span class="text-cyan-300 text-xs font-medium">76%</span>
                            </div>
                            <div class="w-full h-2 bg-cyan-500/20 rounded-full overflow-hidden">
                                <div class="w-[76%] h-full bg-gradient-to-r from-cyan-500 to-cyan-400 rounded-full"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-gray-500 dark:text-gray-400 text-xs">Open Mic</span>
                                <span class="text-cyan-300 text-xs font-medium">58%</span>
                            </div>
                            <div class="w-full h-2 bg-cyan-500/20 rounded-full overflow-hidden">
                                <div class="w-[58%] h-full bg-gradient-to-r from-cyan-500 to-cyan-400 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Rooms -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Multiple Rooms
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Main stage. Back room. Podcast corner.</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Separate calendars for each space. One venue, many vibes.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-500/20 border border-emerald-500/30">
                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Main Showroom</span>
                            <span class="ml-auto text-emerald-300 text-xs">8 shows</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">The Basement</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-500 text-xs">5 shows</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Podcast Studio</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-500 text-xs">3 shows</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- From Open Mic to Headliner - Simplified 3-Stage -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">From Open Mic to Headliner</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">Your club is where careers start.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Open Mic -->
                <div class="relative rounded-2xl bg-gradient-to-br from-lime-900/30 to-green-900/30 border border-lime-500/30 p-8 text-center">
                    <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-lime-500/20 flex items-center justify-center">
                        <div class="w-6 h-6 rounded-full bg-lime-400/60"></div>
                    </div>
                    <h3 class="text-xl font-bold text-lime-400 mb-2">Open Mic</h3>
                    <p class="text-gray-500 dark:text-gray-400">5 minutes to prove yourself</p>
                </div>

                <!-- Feature -->
                <div class="relative rounded-2xl bg-gradient-to-br from-amber-900/30 to-orange-900/30 border border-amber-500/30 p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-amber-500/20 flex items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-amber-400/70"></div>
                    </div>
                    <h3 class="text-xl font-bold text-amber-400 mb-2">Feature</h3>
                    <p class="text-gray-500 dark:text-gray-400">25 minutes. The crowd knows your name.</p>
                </div>

                <!-- Headliner -->
                <div class="relative rounded-2xl bg-gradient-to-br from-yellow-900/40 to-amber-900/40 border border-yellow-500/40 p-8 text-center shadow-lg shadow-yellow-500/10">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-yellow-500/30 flex items-center justify-center shadow-lg shadow-yellow-400/30">
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-yellow-400 to-amber-400"></div>
                    </div>
                    <h3 class="text-2xl font-bold text-yellow-400 mb-2">Headliner</h3>
                    <p class="text-gray-500 dark:text-gray-400">Your name on the marquee</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recording Night / Special Events Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-purple-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-red-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-purple-900 to-red-900 rounded-3xl border border-purple-500/30 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 text-red-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Recording Nights
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-purple-300 transition-colors">Album recordings. Netflix tapings. The big nights.</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Sell tickets to the live audience AND stream to fans worldwide. Your club, your special.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Live + Virtual tickets</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Global streaming</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No capacity limits</span>
                            </div>
                            <span class="inline-flex items-center text-purple-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more about online events
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-red-500/30 p-6 w-56">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs font-medium">RECORDING IN PROGRESS</span>
                                </div>
                                <div class="flex items-center justify-center gap-2 mb-4">
                                    <div class="w-3 h-3 rounded-full bg-red-500 animate-pulse"></div>
                                    <span class="text-red-400 text-sm font-bold tracking-wide">LIVE</span>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">2,847 watching</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">14 countries</span>
                                    </div>
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
                    Perfect for all comedy venues
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From intimate open mics to major comedy clubs, Event Schedule fits your stage.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Stand-up Comedy Clubs -->
                <x-sub-audience-card
                    name="Stand-up Comedy Clubs"
                    description="Headliners, features, and open mic nights. Build your audience of comedy fans."
                    icon-color="amber"
                    blog-slug="for-stand-up-comedy-clubs"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Improv Theaters -->
                <x-sub-audience-card
                    name="Improv Theaters"
                    description="Harold nights, improv jams, and sketch shows. Perfect for ensemble performances."
                    icon-color="lime"
                    blog-slug="for-improv-theaters"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-lime-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Open Mic Venues -->
                <x-sub-audience-card
                    name="Open Mic Venues"
                    description="New comic nights and amateur showcases. Help rising talent find their stage."
                    icon-color="green"
                    blog-slug="for-open-mic-comedy-venues"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Comedy Bars & Restaurants -->
                <x-sub-audience-card
                    name="Comedy Bars & Restaurants"
                    description="Dinner shows and drink-minimum events. Comedy plus cuisine."
                    icon-color="orange"
                    blog-slug="for-comedy-bars-restaurants"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sketch Comedy Venues -->
                <x-sub-audience-card
                    name="Sketch Comedy Venues"
                    description="Revues, variety shows, and ensemble performances. Where sketch troupes shine."
                    icon-color="emerald"
                    blog-slug="for-sketch-comedy-venues"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Live Podcast Studios -->
                <x-sub-audience-card
                    name="Live Podcast Studios"
                    description="Live recordings and audience participation shows. Bring podcasts to life."
                    icon-color="purple"
                    blog-slug="for-live-podcast-studios"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Get your comedy club online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-yellow-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Set up your room</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add your club, rooms (main stage, back room), and your open mic schedule. Set up recurring shows once.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-yellow-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build your lineup</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add headliners, approve open mic requests, link comedian profiles with their clips. Show fans who's on stage.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-yellow-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-amber-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Grow your audience</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Fans follow your club, get the weekly lineup, and buy tickets directly from you. No middleman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-amber-600 to-yellow-700 py-24 overflow-hidden">
        <!-- Brick wall texture overlay -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"60\" height=\"30\"><rect width=\"60\" height=\"30\" fill=\"none\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"30\" y1=\"0\" x2=\"30\" y2=\"15\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"0\" y1=\"15\" x2=\"60\" y2=\"15\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"15\" y1=\"15\" x2=\"15\" y2=\"30\" stroke=\"%23fff\" stroke-width=\"0.5\"/><line x1=\"45\" y1=\"15\" x2=\"45\" y2=\"30\" stroke=\"%23fff\" stroke-width=\"0.5\"/></svg>');"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <!-- Glow effects -->
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-yellow-500/20 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-amber-500/20 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Be the room where it happens
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Build your audience. Launch careers. Keep every dollar. Free forever.
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
        "name": "Event Schedule for Comedy Clubs",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Comedy Club Management Software",
        "operatingSystem": "Web",
        "description": "Be the stage where comedy careers are made. From open mic to headliner - manage lineups, sell tickets with zero fees, and build your audience. Built for stand-up clubs, improv theaters, and open mic venues.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Lineup builder with performer profiles",
            "Open mic sign-up management",
            "Accept booking requests from comedians with video clips",
            "Recurring show scheduling",
            "Multiple room/stage support",
            "QR code ticketing with zero fees",
            "Direct newsletter to comedy fans",
            "Event analytics"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-comedy {
            background: linear-gradient(135deg, #f59e0b, #eab308, #facc15);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-marketing-layout>
