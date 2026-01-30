<x-marketing-layout>
    <x-slot name="title">Event Schedule for Workshop Instructors | Class Scheduling & Ticketing Software</x-slot>
    <x-slot name="description">Teach what you love. Fill every seat. From pottery to photography, cooking to coding. One link for all your workshops. Reach students directly. Free forever.</x-slot>
    <x-slot name="keywords">workshop scheduling software, class booking system, workshop ticketing, workshop instructor tools, cooking class software, pottery class scheduling, photography workshop booking, craft workshop management, workshop series software, workshop newsletter</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section - Mesh Gradient Background -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient blobs -->
        <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] bg-indigo-500/15 rounded-full blur-[150px]"></div>
        <div class="absolute top-1/3 right-1/4 w-[400px] h-[400px] bg-purple-500/15 rounded-full blur-[130px] animate-pulse-slow"></div>
        <div class="absolute bottom-1/4 left-1/3 w-[350px] h-[350px] bg-violet-500/10 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1s;"></div>

        <!-- Grid pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.015)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.015)_1px,transparent_1px)] bg-[size:60px_60px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Workshop Instructors & Educators</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">
                Teach what you love.
                <br>
                <span class="text-gradient-workshop">Fill every seat.</span>
            </h1>

            <p class="text-lg md:text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-12">
                From pottery to photography, cooking to coding. One link for all your workshops. Reach students directly.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-indigo-500/25">
                    Create your schedule
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Workshop type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300 text-xs font-medium border border-orange-500/30">Cooking</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-500/30">Pottery</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-xs font-medium border border-blue-500/30">Photography</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300 text-xs font-medium border border-yellow-500/30">Woodworking</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-xs font-medium border border-fuchsia-500/30">Painting</span>
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-xs font-medium border border-teal-500/30">Music Lessons</span>
            </div>
        </div>
    </section>

    <!-- Stats Section - Chalkboard Style -->
    <section class="bg-gray-100 dark:bg-[#1e293b] py-16 border-t border-gray-200 dark:border-white/5 relative overflow-hidden">
        <!-- Chalk dust texture overlay -->
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.05]">
            <svg width="100%" height="100%">
                <defs>
                    <pattern id="chalk-dust" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                        <circle cx="10" cy="15" r="0.5" fill="white"/>
                        <circle cx="45" cy="8" r="0.4" fill="white"/>
                        <circle cx="78" cy="32" r="0.6" fill="white"/>
                        <circle cx="25" cy="55" r="0.3" fill="white"/>
                        <circle cx="60" cy="70" r="0.5" fill="white"/>
                        <circle cx="90" cy="85" r="0.4" fill="white"/>
                        <circle cx="35" cy="92" r="0.3" fill="white"/>
                        <circle cx="70" cy="48" r="0.5" fill="white"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#chalk-dust)"/>
            </svg>
        </div>
        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="text-4xl md:text-5xl font-bold chalk-text mb-2">68%</div>
                    <p class="text-gray-500 dark:text-gray-400">workshop spots go unsold without direct marketing</p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold chalk-text mb-2">$0</div>
                    <p class="text-gray-500 dark:text-gray-400">platform fees on ticket sales</p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold chalk-text mb-2">1-click</div>
                    <p class="text-gray-500 dark:text-gray-400">email to your entire student list</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Workshop Announcements - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900 dark:to-purple-900 border border-indigo-200 dark:border-indigo-500/30 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Workshop Announcements
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Announce new workshops, fill seats instantly</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">One click sends your upcoming workshops to every student who follows you. No algorithm decides who sees it.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Email newsletters</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Direct to inbox</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No social media needed</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-950 dark:to-purple-950 rounded-2xl border border-indigo-300 dark:border-indigo-400/30 p-4 max-w-xs">
                                    <div class="text-[10px] text-indigo-600 dark:text-indigo-300 mb-3 font-semibold tracking-wide">UPCOMING WORKSHOPS</div>
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-indigo-500/20 border border-indigo-400/30">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-xs font-bold">SAT</div>
                                            <div class="flex-1">
                                                <div class="text-gray-900 dark:text-white text-sm font-semibold">Italian Pasta Making</div>
                                                <div class="text-indigo-600 dark:text-indigo-300 text-xs">Sat, Feb 8 - 3 spots left</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-500 to-yellow-500 flex items-center justify-center text-white text-xs font-bold">SUN</div>
                                            <div class="flex-1">
                                                <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Wheel Throwing Basics</div>
                                                <div class="text-gray-600 dark:text-gray-400 text-xs">Sun, Feb 9 - 6 spots left</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white text-xs font-bold">WED</div>
                                            <div class="flex-1">
                                                <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Night Photography Walk</div>
                                                <div class="text-gray-600 dark:text-gray-400 text-xs">Wed, Feb 12 - 8 spots left</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zero Fee Ticketing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Zero-Fee Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Keep 100% of every sale</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">No platform fees. No per-ticket charges. Every dollar your students pay goes to you.</p>

                    <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-4 text-center">
                        <div class="text-5xl font-bold text-emerald-400 mb-1">100%</div>
                        <div class="text-emerald-300 text-sm font-medium">you keep</div>
                        <div class="mt-3 flex items-center justify-center gap-2">
                            <div class="h-1 flex-1 bg-emerald-400 rounded-full"></div>
                            <span class="text-emerald-300 text-xs">$0 platform fees</span>
                        </div>
                    </div>
                </div>

                <!-- One Link -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        One Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for everything</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Share one URL. Students see all your upcoming workshops, book, and pay.</p>

                    <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-4">
                        <div class="flex items-center gap-2 bg-gray-200 dark:bg-[#0f0f14] rounded-lg px-3 py-2 border border-gray-300 dark:border-white/10">
                            <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-blue-400 text-sm font-mono truncate">eventschedule.com/yourworkshop</span>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <div class="flex-1 h-2 bg-blue-400/40 rounded-full"></div>
                            <div class="flex-1 h-2 bg-blue-400/25 rounded-full"></div>
                            <div class="flex-1 h-2 bg-blue-400/15 rounded-full"></div>
                        </div>
                    </div>
                </div>

                <!-- Workshop Series / Curriculum (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-violet-200 dark:border-violet-500/30 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Workshop Series
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Build a curriculum that keeps students coming back</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Create multi-session series with progressive skill building. Offer series discounts to encourage full enrollment.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Multi-session series</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Progressive levels</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Bundle pricing</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-950 dark:to-purple-950 rounded-2xl border border-violet-300 dark:border-violet-400/30 p-4 max-w-xs">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="text-[10px] text-violet-600 dark:text-violet-300 font-semibold tracking-wide">POTTERY FUNDAMENTALS</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-300 text-[10px] font-medium border border-green-300 dark:border-green-500/30">Series Discount</span>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-violet-200 dark:bg-violet-500/20 border border-violet-300 dark:border-violet-400/30">
                                            <div class="w-8 h-8 rounded-full bg-violet-500/40 flex items-center justify-center text-violet-600 dark:text-violet-200 text-xs font-bold">1</div>
                                            <div class="flex-1">
                                                <div class="text-gray-900 dark:text-white text-sm font-medium">Hand Building</div>
                                            </div>
                                            <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-violet-200 dark:bg-violet-500/20 border border-violet-300 dark:border-violet-400/30">
                                            <div class="w-8 h-8 rounded-full bg-violet-500/40 flex items-center justify-center text-violet-600 dark:text-violet-200 text-xs font-bold">2</div>
                                            <div class="flex-1">
                                                <div class="text-gray-900 dark:text-white text-sm font-medium">Wheel Throwing</div>
                                            </div>
                                            <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-violet-100 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-400/20">
                                            <div class="w-8 h-8 rounded-full bg-violet-500/30 flex items-center justify-center text-violet-600 dark:text-violet-300 text-xs font-bold">3</div>
                                            <div class="flex-1">
                                                <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">Glazing</div>
                                            </div>
                                            <div class="text-violet-600 dark:text-violet-400 text-[10px]">Next</div>
                                        </div>
                                        <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                                            <div class="w-8 h-8 rounded-full bg-violet-500/20 flex items-center justify-center text-violet-500 dark:text-violet-400 text-xs font-bold">4</div>
                                            <div class="flex-1">
                                                <div class="text-gray-600 dark:text-gray-400 text-sm font-medium">Advanced Forms</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Capacity Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Capacity Management
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Never overbook a class</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Set seat limits per workshop. Students see availability in real time.</p>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-red-500/20 border border-red-400/30">
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Pasta Making</span>
                            <span class="text-red-300 text-xs font-medium">SOLD OUT</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-amber-500/20 border border-amber-400/30">
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Wheel Throwing</span>
                            <span class="text-amber-300 text-xs font-medium">2 seats left</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-green-500/20 border border-green-400/30">
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Photography Walk</span>
                            <span class="text-green-300 text-xs font-medium">8 seats left</span>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Google Calendar
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Two-way calendar sync</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Your workshops appear in Google Calendar. Changes sync both ways automatically.</p>

                    <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                                <svg class="w-5 h-5" viewBox="0 0 24 24">
                                    <rect x="2" y="3" width="20" height="18" rx="2" fill="#4285F4" />
                                    <rect x="4" y="8" width="16" height="11" rx="1" fill="white" />
                                    <text x="12" y="17" text-anchor="middle" font-size="8" font-weight="bold" fill="#4285F4">31</text>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Google Calendar</div>
                                <div class="text-green-400 text-xs">Connected</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-center gap-3">
                            <div class="text-blue-300 text-xs">Event Schedule</div>
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <div class="text-blue-300 text-xs">Google Calendar</div>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Know what sells</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">See which workshops fill fastest, which days work best, and how your audience grows.</p>

                    <div class="flex items-end gap-2 h-24">
                        <div class="flex-1 bg-cyan-400/30 rounded-t-lg" style="height: 40%"></div>
                        <div class="flex-1 bg-cyan-400/40 rounded-t-lg" style="height: 55%"></div>
                        <div class="flex-1 bg-cyan-400/50 rounded-t-lg" style="height: 45%"></div>
                        <div class="flex-1 bg-cyan-400/60 rounded-t-lg" style="height: 70%"></div>
                        <div class="flex-1 bg-cyan-400/70 rounded-t-lg" style="height: 85%"></div>
                        <div class="flex-1 bg-cyan-400/80 rounded-t-lg" style="height: 65%"></div>
                        <div class="flex-1 bg-gradient-to-t from-cyan-500 to-cyan-400 rounded-t-lg" style="height: 100%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-gray-500 dark:text-gray-500 text-[10px]">Mon</span>
                        <span class="text-cyan-400 text-[10px] font-medium">This week: 47 bookings</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Workshop Series Progression Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Build workshop series that keep students coming back</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">Progressive skill building. Multi-session enrollment. Loyal students.</p>
            </div>

            <div class="relative bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900/60 dark:to-violet-900/60 rounded-3xl border border-indigo-200 dark:border-indigo-500/30 p-8 lg:p-10">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Pottery Fundamentals</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">4-session course - Saturdays in February</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-green-500/20 text-green-600 dark:text-green-300 text-sm font-medium border border-green-500/30">
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                        Series Discount: 15% off
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Session 1 - Post-it note style -->
                    <div class="relative postit-card bg-gradient-to-br from-yellow-100 to-amber-100 dark:from-indigo-800/60 dark:to-indigo-700/40 border border-yellow-300 dark:border-indigo-500/30 p-6 text-center" style="transform: rotate(-1.5deg);">
                        <!-- Fold corner -->
                        <div class="absolute top-0 right-0 w-6 h-6 bg-gradient-to-bl from-yellow-200/80 to-transparent dark:from-indigo-600/40 dark:to-transparent"></div>
                        <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-indigo-500/30 flex items-center justify-center">
                            <span class="text-indigo-700 dark:text-indigo-200 text-lg font-bold">1</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Hand Building</h4>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Feb 1 - Pinch & coil techniques</p>
                    </div>

                    <!-- Arrow connector (hidden on mobile) -->
                    <div class="hidden md:flex absolute top-1/2 left-[24%] transform -translate-y-1/2 z-10">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    </div>

                    <!-- Session 2 -->
                    <div class="relative postit-card bg-gradient-to-br from-orange-100 to-yellow-100 dark:from-violet-800/60 dark:to-violet-700/40 border border-orange-300 dark:border-violet-500/30 p-6 text-center" style="transform: rotate(1deg);">
                        <div class="absolute top-0 right-0 w-6 h-6 bg-gradient-to-bl from-orange-200/80 to-transparent dark:from-violet-600/40 dark:to-transparent"></div>
                        <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-violet-500/30 flex items-center justify-center">
                            <span class="text-violet-700 dark:text-violet-200 text-lg font-bold">2</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Wheel Throwing</h4>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Feb 8 - Centering & pulling</p>
                    </div>

                    <!-- Session 3 -->
                    <div class="relative postit-card bg-gradient-to-br from-lime-100 to-green-100 dark:from-purple-800/60 dark:to-purple-700/40 border border-lime-300 dark:border-purple-500/30 p-6 text-center" style="transform: rotate(-0.5deg);">
                        <div class="absolute top-0 right-0 w-6 h-6 bg-gradient-to-bl from-lime-200/80 to-transparent dark:from-purple-600/40 dark:to-transparent"></div>
                        <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-purple-500/30 flex items-center justify-center">
                            <span class="text-purple-700 dark:text-purple-200 text-lg font-bold">3</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Glazing</h4>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Feb 15 - Color & finish</p>
                    </div>

                    <!-- Session 4 -->
                    <div class="relative postit-card bg-gradient-to-br from-sky-100 to-blue-100 dark:from-fuchsia-800/60 dark:to-fuchsia-700/40 border border-sky-300 dark:border-fuchsia-500/30 p-6 text-center" style="transform: rotate(1.5deg);">
                        <div class="absolute top-0 right-0 w-6 h-6 bg-gradient-to-bl from-sky-200/80 to-transparent dark:from-fuchsia-600/40 dark:to-transparent"></div>
                        <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-fuchsia-500/30 flex items-center justify-center">
                            <span class="text-fuchsia-700 dark:text-fuchsia-200 text-lg font-bold">4</span>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Advanced Forms</h4>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Feb 22 - Creative projects</p>
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
                    Perfect for all workshop instructors
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whatever you teach, Event Schedule helps you fill every class.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Cooking Class Instructors -->
                <x-sub-audience-card
                    name="Cooking Class Instructors"
                    description="From pasta making to pastry arts. Share your recipes, sell spots, and build a community of food lovers."
                    icon-color="indigo"
                    blog-slug="for-cooking-class-instructors"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Pottery & Ceramics Teachers -->
                <x-sub-audience-card
                    name="Pottery & Ceramics Teachers"
                    description="Wheel throwing, hand building, and glazing workshops. Manage studio capacity and skill levels."
                    icon-color="violet"
                    blog-slug="for-pottery-ceramics-teachers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Photography Workshop Leaders -->
                <x-sub-audience-card
                    name="Photography Workshop Leaders"
                    description="Photo walks, studio sessions, and editing workshops. Collect gear requirements from students upfront."
                    icon-color="blue"
                    blog-slug="for-photography-workshop-leaders"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Craft & Maker Instructors -->
                <x-sub-audience-card
                    name="Craft & Maker Instructors"
                    description="Woodworking, metalwork, sewing, and beyond. List materials needed and manage workshop capacities."
                    icon-color="amber"
                    blog-slug="for-craft-maker-instructors"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Art Teachers -->
                <x-sub-audience-card
                    name="Art Teachers"
                    description="Painting, drawing, and mixed media classes. Showcase student work and build a creative community."
                    icon-color="fuchsia"
                    blog-slug="for-art-teachers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Music Lesson Instructors -->
                <x-sub-audience-card
                    name="Music Lesson Instructors"
                    description="Group lessons, masterclasses, and jam sessions. Schedule recurring classes and manage student enrollment."
                    icon-color="teal"
                    blog-slug="for-music-lesson-instructors"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
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
                    Get your workshops online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-indigo-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">List your workshops</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Add your classes with descriptions, materials needed, skill levels, and pricing. Set capacity limits and recurring schedules.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-indigo-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your link</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        One URL for all your workshops. Put it in your bio, share it with your audience, and let students browse and book directly.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-indigo-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Fill your classes</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Students follow you and get notified of new workshops. Send newsletters to fill spots. Keep 100% of ticket revenue.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-indigo-600 to-purple-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <!-- Glow effects -->
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-indigo-500/20 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-purple-500/20 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your workshop deserves a full room
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Stop losing students to clunky booking systems. One link, zero fees, full classes. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-indigo-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Workshop Instructors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Workshop Scheduling Software",
        "operatingSystem": "Web",
        "description": "Teach what you love. Fill every seat. From pottery to photography, cooking to coding. One link for all your workshops. Reach students directly with zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Workshop series with progressive curriculum",
            "Zero-fee ticketing with Stripe integration",
            "Capacity management with real-time availability",
            "Custom fields for skill levels and materials",
            "One-click newsletter to students",
            "Google Calendar two-way sync",
            "Analytics and booking insights",
            "One link for all workshops"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-workshop {
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chalk-text {
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .chalk-text {
            background: linear-gradient(135deg, #c7d2fe, #e0e7ff, #ddd6fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 8px rgba(199, 210, 254, 0.3);
            filter: blur(0.2px);
        }

        .postit-card {
            box-shadow: 2px 3px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .postit-card:hover {
            transform: rotate(0deg) !important;
        }

        .dark .postit-card {
            box-shadow: 2px 3px 12px rgba(0,0,0,0.3);
        }
    </style>
</x-marketing-layout>
