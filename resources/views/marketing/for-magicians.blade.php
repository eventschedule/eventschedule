<x-marketing-layout>
    <x-slot name="title">Event Schedule for Magicians | Share Your Shows & Get Booked</x-slot>
    <x-slot name="description">Share your magic shows, sell tickets directly, and reach your audience with newsletters. Built for magicians, mentalists, illusionists, and variety performers. Zero platform fees.</x-slot>
    <x-slot name="keywords">magician schedule, magic show calendar, illusionist booking, mentalist events, variety performer schedule, magic show tickets, corporate magician, wedding magician</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Magic Sparkle Effects -->
        <div class="absolute top-16 left-16 animate-sparkle">
            <svg class="w-6 h-6 text-amber-400/60" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            </svg>
        </div>
        <div class="absolute top-32 right-24 animate-sparkle" style="animation-delay: 0.5s;">
            <svg class="w-4 h-4 text-amber-300/50" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            </svg>
        </div>
        <div class="absolute bottom-40 left-32 animate-sparkle" style="animation-delay: 1s;">
            <svg class="w-5 h-5 text-amber-400/40" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            </svg>
        </div>
        <div class="absolute top-48 left-1/2 animate-sparkle" style="animation-delay: 1.5s;">
            <svg class="w-3 h-3 text-amber-200/50" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            </svg>
        </div>
        <div class="absolute bottom-24 right-16 animate-sparkle" style="animation-delay: 0.8s;">
            <svg class="w-5 h-5 text-amber-300/60" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            </svg>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Magicians, Mentalists & Variety Artists</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                <span class="text-gradient-purple">Make your bookings appear.</span><br>
                Like magic.
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                One link for all your shows. Event planners find your availability, fans follow your performances, no algorithm makes you invisible.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-violet-500/25">
                    Create your performance schedule
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Performance tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-xs font-medium border border-violet-200 dark:border-violet-500/30">Close-up</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-xs font-medium border border-purple-200 dark:border-purple-500/30">Stage</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-500/30">Mentalism</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-xs font-medium border border-fuchsia-200 dark:border-fuchsia-500/30">Illusion</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-500/30">Corporate</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-pink-100 text-pink-700 dark:bg-pink-500/20 dark:text-pink-300 text-xs font-medium border border-pink-200 dark:border-pink-500/30">Kids Shows</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Private Event Bookings (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-violet-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Bookings
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">One link for event planners</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg mb-6">Wedding planners, corporate event coordinators, and party hosts find your availability, videos, and rates. Professional booking made simple.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Corporate events</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Weddings</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Private parties</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-950 dark:to-purple-950 rounded-2xl border border-violet-300 dark:border-violet-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-violet-600 dark:text-violet-300 font-semibold mb-3 uppercase tracking-wide">Booking Inquiry</div>
                                    <div class="space-y-3">
                                        <div class="p-3 rounded-lg bg-violet-200 dark:bg-white/10 border border-violet-300 dark:border-white/10">
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="w-2 h-2 rounded-full bg-amber-500 dark:bg-amber-400"></div>
                                                <span class="text-gray-900 dark:text-white text-sm font-medium">Corporate Gala</span>
                                            </div>
                                            <div class="text-gray-500 dark:text-gray-400 text-xs">Dec 15 - Marriott Ballroom</div>
                                        </div>
                                        <div class="p-3 rounded-lg bg-violet-100 dark:bg-white/5">
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="w-2 h-2 rounded-full bg-pink-500 dark:bg-pink-400"></div>
                                                <span class="text-gray-600 dark:text-gray-300 text-sm font-medium">Wedding Reception</span>
                                            </div>
                                            <div class="text-gray-500 dark:text-gray-400 text-xs">Jan 8 - Private Venue</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-100 to-fuchsia-100 dark:from-pink-900 dark:to-fuchsia-900 border border-pink-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-100 text-pink-700 dark:bg-pink-500/20 dark:text-pink-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Newsletter
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Reach fans directly</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">New show? Email your audience directly. No paying Facebook to reach your own followers.</p>

                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-fuchsia-500 rounded-xl flex items-center justify-center">
                                <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="absolute -top-1 -right-1 w-5 h-5 bg-pink-400 rounded-full flex items-center justify-center">
                                <span class="text-white text-[10px] font-bold">!</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Virtual Shows -->
                <a href="{{ marketing_url('/online-events') }}" class="bento-card group relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900 dark:to-blue-900 border border-indigo-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Virtual Magic
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors">Stream shows worldwide</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Perform for audiences anywhere. Sell tickets globally for your virtual magic shows.</p>

                    <div class="flex justify-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500/30 to-blue-500/30 rounded-xl flex items-center justify-center border border-indigo-400/30">
                            <svg class="w-6 h-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <span class="inline-flex items-center text-indigo-400 text-sm font-medium mt-4 group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Sell Tickets (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Ticketing
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Keep 100% of ticket sales</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-lg">Your show, your revenue. Zero platform fees. Stripe goes directly to you. QR tickets for easy check-in.</p>
                        </div>
                        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-gray-100 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                    <div>
                                        <div class="text-gray-900 dark:text-white font-medium">General Admission</div>
                                        <div class="text-emerald-600 dark:text-emerald-400 text-xs">120 remaining</div>
                                    </div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-white">$25</div>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-500/20 border border-emerald-400/30">
                                    <div>
                                        <div class="text-gray-900 dark:text-white font-medium">VIP Front Row</div>
                                        <div class="text-emerald-600 dark:text-emerald-300 text-xs">8 remaining</div>
                                    </div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sync with Google Calendar</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Two-way sync keeps everything updated. Add a show anywhere, see it everywhere.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-white/20 rounded"></div>
                                <div class="h-1.5 bg-white/20 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-white/10 rounded-xl border border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-600 dark:text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Coordination -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 border border-purple-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Team
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Coordinate your crew</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Assistants, agents, technicians - everyone sees the schedule. No double-bookings.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-500 to-violet-500 flex items-center justify-center text-white text-xs font-semibold">DM</div>
                            <div class="flex-1">
                                <div class="text-white text-sm">David</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px]">Magician</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white text-xs font-semibold">SK</div>
                            <div class="flex-1">
                                <div class="text-gray-300 text-sm">Sarah</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-violet-500/20 text-violet-300 text-[10px]">Assistant</span>
                        </div>
                    </div>
                </div>

                <!-- Promo Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Share-ready images</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">Auto-generate promotional graphics for social media. Perfect for Instagram and Facebook.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-amber-500/30 to-orange-500/30 rounded-xl border border-amber-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-violet-600/40 to-purple-600/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-white text-[10px] font-semibold mb-1">MAGIC SHOW</div>
                                <div class="text-amber-300 text-xs font-bold">The Amazing David</div>
                                <div class="text-gray-400 text-[8px] mt-1">Sat 8PM - Grand Theater</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center">
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

    <!-- Where Magic Happens -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Where magic happens
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    One schedule for every venue and event type
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Corporate Events -->
                <div class="group relative bg-gradient-to-br from-[#12101a] to-[#0a0a0f] rounded-2xl p-6 border border-violet-900/20 hover:border-violet-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Corporate</h3>
                </div>

                <!-- Weddings -->
                <div class="group relative bg-gradient-to-br from-[#170f17] to-[#0a0a0f] rounded-2xl p-6 border border-pink-900/20 hover:border-pink-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-pink-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Weddings</h3>
                </div>

                <!-- Private Parties -->
                <div class="group relative bg-gradient-to-br from-[#1a1510] to-[#0a0a0f] rounded-2xl p-6 border border-amber-900/20 hover:border-amber-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Parties</h3>
                </div>

                <!-- Theaters -->
                <div class="group relative bg-gradient-to-br from-[#15101a] to-[#0a0a0f] rounded-2xl p-6 border border-purple-900/20 hover:border-purple-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Theaters</h3>
                </div>

                <!-- Restaurants & Bars -->
                <div class="group relative bg-gradient-to-br from-[#101518] to-[#0a0a0f] rounded-2xl p-6 border border-teal-900/20 hover:border-teal-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Restaurants</h3>
                </div>

                <!-- Virtual/Online -->
                <div class="group relative bg-gradient-to-br from-[#0f1520] to-[#0a0a0f] rounded-2xl p-6 border border-indigo-900/20 hover:border-indigo-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Virtual</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for all types of magic performers
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether you're doing close-up magic or grand illusions, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Close-Up Magicians -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-violet-200 dark:hover:border-violet-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-100 dark:bg-violet-500/20 mb-4">
                        <svg class="w-6 h-6 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Close-Up Magicians</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Card tricks, coin magic, sleight of hand for intimate gatherings and table-hopping at events.</p>
                </div>

                <!-- Stage Illusionists -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-purple-200 dark:hover:border-purple-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-500/20 mb-4">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Stage Illusionists</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Large-scale illusions and theatrical magic shows that fill theaters and wow audiences.</p>
                </div>

                <!-- Mentalists -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-indigo-200 dark:hover:border-indigo-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 mb-4">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Mentalists</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Mind reading, predictions, and psychological entertainment that leaves audiences amazed.</p>
                </div>

                <!-- Children's Entertainers -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-pink-200 dark:hover:border-pink-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-500/20 mb-4">
                        <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Children's Entertainers</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Birthday parties, school shows, and family events with fun, interactive magic for kids.</p>
                </div>

                <!-- Corporate Magicians -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-500/20 mb-4">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Corporate Magicians</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Trade shows, conferences, and product launches with customized magic presentations.</p>
                </div>

                <!-- Variety Artists -->
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg hover:border-fuchsia-200 dark:hover:border-fuchsia-500/30 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-500/20 mb-4">
                        <svg class="w-6 h-6 text-fuchsia-600 dark:text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Variety Artists</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Ventriloquists, escape artists, hypnotists, and specialty acts that defy categorization.</p>
                </div>
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
                    Get your performance schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-violet-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your shows</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Performances, private bookings, recurring gigs. Import from Google Calendar or add manually.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-violet-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share one link</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add to your website, social bios, and EPK. Planners see your availability instantly.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-violet-500 to-purple-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-violet-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Build your audience</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Fans follow your schedule and get notified about shows near them.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Online Events Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-indigo-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-indigo-900 to-violet-900 rounded-3xl border border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Virtual Magic Shows
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-3 group-hover:text-indigo-300 transition-colors">Stream to audiences worldwide</h3>
                            <p class="text-gray-400 text-lg mb-4">Sell tickets to viewers anywhere. Virtual magic became huge and stays relevant - reach fans who can't make it to your live shows.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Zoom shows</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Global ticket sales</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Interactive magic</span>
                            </div>
                            <span class="inline-flex items-center text-indigo-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more about online events
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Online Event</span>
                                    <div class="w-10 h-5 bg-indigo-500 rounded-full relative">
                                        <div class="absolute right-0.5 top-0.5 w-4 h-4 bg-white rounded-full"></div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Zoom</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-red-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">YouTube Live</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-2 py-1.5 bg-gray-100 dark:bg-white/5 rounded-lg">
                                        <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                                        <span class="text-gray-600 dark:text-gray-300 text-xs">Twitch</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-violet-600 to-purple-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <!-- Magic sparkles in CTA -->
        <div class="absolute top-12 left-20 animate-sparkle">
            <svg class="w-5 h-5 text-amber-300/40" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            </svg>
        </div>
        <div class="absolute bottom-16 right-24 animate-sparkle" style="animation-delay: 0.7s;">
            <svg class="w-4 h-4 text-amber-200/50" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            </svg>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                The secret to more bookings?<br>Let them find you.
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Your magic deserves an audience. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-violet-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Magicians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Magician Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your magic shows, sell tickets directly, and reach your audience with newsletters. Built for magicians, mentalists, illusionists, and variety performers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Private event booking management for corporate and wedding bookings",
            "Zero-fee ticket sales with QR check-in",
            "Direct newsletter communication with fans",
            "Virtual show streaming support",
            "Two-way Google Calendar sync",
            "Team coordination for assistants and agents",
            "Auto-generated promotional graphics for social media"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-purple {
            background: linear-gradient(135deg, #8b5cf6, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes sparkle {
            0%, 100% {
                opacity: 0.3;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }

        .animate-sparkle {
            animation: sparkle 2s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-pulse-slow,
            .animate-sparkle,
            .animate-float {
                animation: none;
            }
        }
    </style>
</x-marketing-layout>
