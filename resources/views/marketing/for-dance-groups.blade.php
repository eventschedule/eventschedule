<x-marketing-layout>
    <x-slot name="title">Event Schedule for Dance Groups | Share Performances & Sell Tickets</x-slot>
    <x-slot name="description">Share your dance performances with fans worldwide. Email your fans directly and sell tickets to recitals and workshops with zero fees. Free forever.</x-slot>
    <x-slot name="keywords">dance group schedule, dance company calendar, dance performance tickets, dance recital software, dance studio events, dance ensemble scheduling, dance troupe calendar, dance workshop tickets</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Dance Groups</x-slot>

    <!-- Hero Section - Movement/Flow Theme -->
    <section class="relative bg-gradient-to-b from-white via-gray-50 to-white dark:from-stone-950 dark:via-stone-900 dark:to-stone-950 py-32 overflow-hidden">
        <!-- Flowing gradient shapes suggesting movement -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 -left-20 w-[500px] h-[800px] bg-gradient-to-br from-rose-500/20 via-cyan-500/10 to-transparent rounded-full blur-3xl transform -rotate-12"></div>
            <div class="absolute bottom-0 -right-20 w-[600px] h-[600px] bg-gradient-to-tl from-sky-500/15 via-rose-500/10 to-transparent rounded-full blur-3xl"></div>
            <div class="absolute top-1/3 right-1/4 w-[300px] h-[500px] bg-gradient-to-b from-cyan-400/10 to-transparent rounded-full blur-2xl transform rotate-45"></div>
        </div>

        <!-- Abstract flowing lines suggesting dance movement -->
        <div class="absolute inset-0 overflow-hidden opacity-20">
            <svg class="absolute top-20 left-10 w-64 h-64 text-rose-300" viewBox="0 0 200 200" fill="none">
                <path d="M20,100 Q60,20 100,100 T180,100" stroke="currentColor" stroke-width="1" fill="none" opacity="0.5"/>
                <path d="M20,120 Q70,40 110,120 T180,120" stroke="currentColor" stroke-width="1" fill="none" opacity="0.3"/>
            </svg>
            <svg class="absolute bottom-32 right-20 w-48 h-48 text-cyan-300" viewBox="0 0 200 200" fill="none">
                <path d="M100,20 Q180,60 100,100 T100,180" stroke="currentColor" stroke-width="1" fill="none" opacity="0.5"/>
            </svg>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <span class="text-sm text-rose-600 dark:text-rose-200/80">For Dance Companies, Troupes & Studios</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-light text-gray-900 dark:text-white mb-6 leading-tight tracking-tight">
                From studio<br>
                <span class="font-normal bg-gradient-to-r from-rose-300 via-cyan-300 to-sky-300 bg-clip-text text-transparent">to stage</span>
            </h1>

            <p class="text-xl md:text-2xl text-stone-500 dark:text-stone-400 max-w-2xl mx-auto mb-12 font-light">
                One schedule for rehearsals, performances, and classes. One link for your audience to find you.
            </p>

            <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-medium text-white bg-gradient-to-r from-rose-500 to-cyan-600 rounded-full hover:scale-105 transition-all shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40">
                Create your schedule
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- The Dance Life Section - Unique to dancers -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 text-center">
                <div class="p-6">
                    <div class="text-4xl mb-3">🩰</div>
                    <div class="text-stone-500 dark:text-stone-400 text-sm">Rehearsals sync to your public schedule</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl mb-3">🎭</div>
                    <div class="text-stone-500 dark:text-stone-400 text-sm">Sell tickets to performances directly</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl mb-3">📧</div>
                    <div class="text-stone-500 dark:text-stone-400 text-sm">Email your fans directly, own the relationship</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl mb-3">🎓</div>
                    <div class="text-stone-500 dark:text-stone-400 text-sm">Fill your classes and workshops</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Feature: Season Planning -->
    <section class="bg-white dark:bg-stone-900 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300 text-sm font-medium mb-6 border border-rose-500/20">
                        Season Planning
                    </div>
                    <h2 class="text-4xl md:text-5xl font-light text-gray-900 dark:text-white mb-6 leading-tight">
                        Your entire season,<br>
                        <span class="text-rose-300">one view</span>
                    </h2>
                    <p class="text-lg text-stone-500 dark:text-stone-400 mb-8 leading-relaxed">
                        Fall program. Winter showcase. Spring recital. Nutcracker run. Import from Google Calendar or add performances manually. Your audience sees everything in one place.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-stone-600 dark:text-stone-300">Two-way Google Calendar sync for rehearsals and shows</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-stone-600 dark:text-stone-300">Multiple venues - theater, studio, outdoor stages</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-stone-600 dark:text-stone-300">Share one link everywhere - programs, posters, social bios</span>
                        </li>
                    </ul>
                </div>
                <div class="relative">
                    <!-- Season calendar mockup -->
                    <div class="bg-gray-50 dark:bg-stone-950 rounded-2xl border border-gray-200 dark:border-stone-800 p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <span class="text-gray-900 dark:text-white font-medium">2025-26 Season</span>
                            <span class="text-xs text-stone-500">City Dance Company</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-amber-500/10 to-transparent border-l-2 border-amber-400">
                                <div class="text-center w-12">
                                    <div class="text-amber-700 dark:text-amber-300 text-xs">OCT</div>
                                    <div class="text-gray-900 dark:text-white font-medium">18-20</div>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Fall Repertory</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">Mainstage Theater</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-rose-500/10 to-transparent border-l-2 border-rose-400">
                                <div class="text-center w-12">
                                    <div class="text-rose-700 dark:text-rose-300 text-xs">DEC</div>
                                    <div class="text-gray-900 dark:text-white font-medium">6-22</div>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">The Nutcracker</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">Historic Opera House</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-cyan-500/10 to-transparent border-l-2 border-cyan-400">
                                <div class="text-center w-12">
                                    <div class="text-cyan-700 dark:text-cyan-300 text-xs">MAR</div>
                                    <div class="text-gray-900 dark:text-white font-medium">14-16</div>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Contemporary Showcase</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">Black Box Studio</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 p-3 rounded-xl bg-gradient-to-r from-sky-500/10 to-transparent border-l-2 border-sky-400">
                                <div class="text-center w-12">
                                    <div class="text-sky-700 dark:text-sky-300 text-xs">MAY</div>
                                    <div class="text-gray-900 dark:text-white font-medium">30-31</div>
                                </div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Spring Gala</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">City Amphitheater</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Classes & Workshops Section - Unique to dance -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24 border-t border-gray-200 dark:border-stone-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <!-- Class schedule mockup -->
                    <div class="bg-white dark:bg-stone-900 rounded-2xl border border-gray-200 dark:border-stone-800 p-6 shadow-2xl">
                        <div class="text-stone-500 dark:text-stone-400 text-xs uppercase tracking-wider mb-4">Weekly Classes</div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-100 dark:bg-stone-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                                    <span class="text-gray-900 dark:text-white text-sm">Ballet Fundamentals</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-stone-500 dark:text-stone-400 text-xs">Mon/Wed 6pm</div>
                                    <div class="text-emerald-400 text-xs">3 spots left</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-100 dark:bg-stone-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                    <span class="text-gray-900 dark:text-white text-sm">Contemporary Technique</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-stone-500 dark:text-stone-400 text-xs">Tue/Thu 7pm</div>
                                    <div class="text-emerald-400 text-xs">5 spots left</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-100 dark:bg-stone-800/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                    <span class="text-gray-900 dark:text-white text-sm">Hip-Hop Foundations</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-stone-500 dark:text-stone-400 text-xs">Sat 2pm</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">FULL</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-stone-700">
                            <div class="text-stone-500 dark:text-stone-400 text-xs uppercase tracking-wider mb-3">Upcoming Workshops</div>
                            <div class="p-3 rounded-lg bg-gradient-to-r from-rose-500/10 to-sky-500/10 border border-rose-500/20">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-gray-900 dark:text-white text-sm font-medium">Partnering Intensive</div>
                                        <div class="text-stone-500 dark:text-stone-400 text-xs">Feb 15-16 with Guest Artist</div>
                                    </div>
                                    <div class="text-rose-300 text-sm font-medium">$85</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300 text-sm font-medium mb-6 border border-sky-500/20">
                        Classes & Workshops
                    </div>
                    <h2 class="text-4xl md:text-5xl font-light text-gray-900 dark:text-white mb-6 leading-tight">
                        Teach and perform<br>
                        <span class="text-sky-300">from one schedule</span>
                    </h2>
                    <p class="text-lg text-stone-500 dark:text-stone-400 mb-8 leading-relaxed">
                        Most dance groups teach alongside performing. List your weekly classes, drop-ins, and intensive workshops. Sell registrations with zero platform fees - you keep everything.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 text-sm border border-gray-200 dark:border-stone-700">Weekly technique</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 text-sm border border-gray-200 dark:border-stone-700">Drop-in classes</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 text-sm border border-gray-200 dark:border-stone-700">Masterclasses</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 text-sm border border-gray-200 dark:border-stone-700">Summer intensives</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- The Algorithm Problem - Unique messaging -->
    <section class="bg-white dark:bg-stone-900 py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-light text-gray-900 dark:text-white mb-6">
                Stop relying on the algorithm
            </h2>
            <p class="text-xl text-stone-500 dark:text-stone-400 mb-12 max-w-2xl mx-auto">
                You post about your show. Facebook shows it to 3% of your followers. Unless you pay. <span class="text-rose-300">There's a better way.</span>
            </p>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-100 dark:bg-stone-800/50 rounded-2xl p-6 border border-gray-200 dark:border-stone-700">
                    <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-2">Email your fans directly</h3>
                    <p class="text-stone-500 dark:text-stone-400 text-sm">Fans follow your schedule. You email them directly - no algorithm in the way.</p>
                </div>
                <div class="bg-gray-100 dark:bg-stone-800/50 rounded-2xl p-6 border border-gray-200 dark:border-stone-700">
                    <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-2">Notify on new shows</h3>
                    <p class="text-stone-500 dark:text-stone-400 text-sm">New performance? Email goes out. Everyone who wants to know, knows.</p>
                </div>
                <div class="bg-gray-100 dark:bg-stone-800/50 rounded-2xl p-6 border border-gray-200 dark:border-stone-700">
                    <div class="w-12 h-12 rounded-full bg-rose-500/10 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-900 dark:text-white font-medium mb-2">Zero platform fees</h3>
                    <p class="text-stone-500 dark:text-stone-400 text-sm">Sell tickets directly. Stripe processes payment. You keep 100%.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Virtual Performances - Link to online events -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24 border-t border-gray-200 dark:border-stone-800/50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-gray-100 dark:from-stone-900 to-gray-50 dark:to-stone-800 rounded-3xl border border-gray-200 dark:border-stone-700 p-8 lg:p-12 hover:border-sky-500/30 transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300 text-sm font-medium mb-4 border border-sky-500/20">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Livestream
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-light text-gray-900 dark:text-white mb-3 group-hover:text-sky-200 transition-colors">
                                Perform for audiences anywhere
                            </h3>
                            <p class="text-stone-500 dark:text-stone-400 text-lg mb-4">
                                Livestream your showcase. Host a virtual masterclass. Reach audiences who can't make it to the theater - and sell tickets to viewers worldwide.
                            </p>
                            <span class="inline-flex items-center text-sky-400 text-sm font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn about online events
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-48 h-32 bg-gray-50 dark:bg-stone-950 rounded-xl border border-gray-200 dark:border-stone-700 flex items-center justify-center relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-br from-sky-500/10 to-sky-500/10"></div>
                                <div class="relative text-center">
                                    <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center mx-auto mb-2 animate-pulse">
                                        <div class="w-2 h-2 rounded-full bg-white"></div>
                                    </div>
                                    <div class="text-gray-900 dark:text-white text-xs font-medium">LIVE</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-[10px]">347 watching</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- Team Coordination -->
    <section class="bg-white dark:bg-stone-900 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300 text-sm font-medium mb-6 border border-blue-500/20">
                        Company Management
                    </div>
                    <h2 class="text-4xl md:text-5xl font-light text-gray-900 dark:text-white mb-6 leading-tight">
                        Coordinate your<br>
                        <span class="text-blue-300">entire company</span>
                    </h2>
                    <p class="text-lg text-stone-500 dark:text-stone-400 mb-8 leading-relaxed">
                        Invite your artistic director, choreographers, rehearsal directors, and company managers. Everyone can update the schedule. Changes sync everywhere instantly.
                    </p>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100 dark:bg-stone-800/50 border border-gray-200 dark:border-stone-700">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-sm font-medium">AD</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm">Sarah Chen</div>
                                <div class="text-stone-500 dark:text-stone-500 text-xs">Artistic Director</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-xs">Owner</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-stone-800/30 border border-gray-200/50 dark:border-stone-700/50">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-rose-500 to-cyan-600 flex items-center justify-center text-white text-sm font-medium">MR</div>
                            <div class="flex-1">
                                <div class="text-stone-600 dark:text-stone-300 text-sm">Marcus Rivera</div>
                                <div class="text-stone-500 dark:text-stone-500 text-xs">Choreographer</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-xs">Admin</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-stone-800/30 border border-gray-200/50 dark:border-stone-700/50">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-sm font-medium">JT</div>
                            <div class="flex-1">
                                <div class="text-stone-600 dark:text-stone-300 text-sm">Jamie Torres</div>
                                <div class="text-stone-500 dark:text-stone-500 text-xs">Company Manager</div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs">Admin</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center">
                    <div class="bg-gray-50 dark:bg-stone-950 rounded-2xl border border-gray-200 dark:border-stone-800 p-6 max-w-sm">
                        <div class="text-stone-500 dark:text-stone-400 text-xs uppercase tracking-wider mb-4">Recent Activity</div>
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="w-1 rounded-full bg-blue-500"></div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm">Sarah added "Spring Gala"</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">2 hours ago</div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-1 rounded-full bg-rose-500"></div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm">Marcus updated rehearsal times</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">Yesterday</div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-1 rounded-full bg-amber-500"></div>
                                <div>
                                    <div class="text-gray-900 dark:text-white text-sm">Jamie added ticket link</div>
                                    <div class="text-stone-500 dark:text-stone-500 text-xs">2 days ago</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section - 6 Dance Sub-types -->
    <section class="bg-stone-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-light text-stone-900 dark:text-white mb-4">
                    Built for every style of dance
                </h2>
                <p class="text-lg text-stone-500 dark:text-gray-400">
                    From classical technique to street dance, we understand how you work.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Ballet Companies -->
                <x-sub-audience-card
                    name="Ballet Companies"
                    description="Season planning for classical and contemporary repertoire. Nutcracker runs, spring galas, studio performances."
                    icon-color="rose"
                    blog-slug="for-ballet-companies"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🩰</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Hip-Hop Crews -->
                <x-sub-audience-card
                    name="Hip-Hop Crews"
                    description="Battles, showcases, cyphers, and workshops. Street dance, breaking, popping, locking - share where you're performing."
                    icon-color="violet"
                    blog-slug="for-hip-hop-crews"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🎤</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Ballroom & Latin Studios -->
                <x-sub-audience-card
                    name="Ballroom & Latin Studios"
                    description="Class schedules, social dances, showcases, and competition prep. Salsa nights to formal balls."
                    icon-color="amber"
                    blog-slug="for-ballroom-latin-studios"
                >
                    <x-slot:icon>
                        <span class="text-2xl">💃</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Contemporary & Modern Troupes -->
                <x-sub-audience-card
                    name="Contemporary & Modern"
                    description="Experimental work, site-specific performances, residencies, and immersive experiences. Art that moves."
                    icon-color="cyan"
                    blog-slug="for-contemporary-modern-dance"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🌊</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Folk & Cultural Ensembles -->
                <x-sub-audience-card
                    name="Folk & Cultural Ensembles"
                    description="Traditional dance, heritage celebrations, cultural festivals. Keeping traditions alive through movement."
                    icon-color="emerald"
                    blog-slug="for-folk-cultural-dance"
                >
                    <x-slot:icon>
                        <span class="text-2xl">🌍</span>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Dance Schools & Academies -->
                <x-sub-audience-card
                    name="Dance Schools & Academies"
                    description="Structured dance education, student recitals, and showcase performances. Manage class schedules and enrollment."
                    icon-color="pink"
                    blog-slug="for-dance-schools-academies"
                >
                    <x-slot:icon>
                        <span class="text-2xl">❤️‍🔥</span>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- Social Promo Graphics -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-stone-100 dark:border-stone-800/50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-600 text-sm font-medium mb-6 border border-orange-500/20">
                        Promo Graphics
                    </div>
                    <h2 class="text-3xl md:text-4xl font-light text-stone-900 dark:text-white mb-6">
                        Share-ready images for every performance
                    </h2>
                    <p class="text-lg text-stone-600 dark:text-gray-400 mb-6">
                        Auto-generate promotional graphics sized perfectly for Instagram, Facebook, and stories. No design skills needed - just download and post.
                    </p>
                    <ul class="space-y-3 text-stone-600 dark:text-gray-400">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Square posts for feed
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Vertical for stories
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Your branding, automatically applied
                        </li>
                    </ul>
                </div>
                <div class="flex justify-center gap-4">
                    <div class="w-36 h-36 bg-gradient-to-br from-rose-100 to-cyan-100 rounded-xl border border-rose-200 p-3 flex flex-col items-center justify-center text-center shadow-lg transform rotate-[-4deg]">
                        <div class="text-[10px] text-rose-600 uppercase tracking-wider mb-1">Spring Gala</div>
                        <div class="text-stone-800 text-sm font-semibold">City Dance Co.</div>
                        <div class="text-stone-500 text-[10px] mt-1">May 30 • 7:30pm</div>
                        <div class="text-rose-600 text-[10px] font-medium mt-2">Tickets Available</div>
                    </div>
                    <div class="w-24 h-40 bg-gradient-to-br from-sky-100 to-blue-100 rounded-xl border border-sky-200 p-2 flex flex-col items-center justify-center text-center shadow-lg transform rotate-[4deg] mt-8">
                        <div class="text-[8px] text-sky-600 uppercase tracking-wider mb-1">Story</div>
                        <div class="text-stone-800 text-xs font-semibold">Tonight!</div>
                        <div class="text-stone-500 text-[8px] mt-1">Nutcracker</div>
                        <div class="text-sky-600 text-[8px] mt-2">Swipe up</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-stone-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-light text-stone-900 dark:text-white mb-4">
                    From sign-up to sold-out
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-500 to-cyan-600 text-white text-xl font-medium rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">Add your season</h3>
                    <p class="text-stone-500 dark:text-gray-400 text-sm">
                        Import from Google Calendar or add performances manually. Classes, workshops, shows - all in one place.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-500 to-cyan-600 text-white text-xl font-medium rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">Share your link</h3>
                    <p class="text-stone-500 dark:text-gray-400 text-sm">
                        Put it on your website, in programs, on posters, in your social bios. One URL that's always current.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-500 to-cyan-600 text-white text-xl font-medium rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-rose-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">Fill your seats</h3>
                    <p class="text-stone-500 dark:text-gray-400 text-sm">
                        Fans subscribe and get notified about new performances. Sell tickets directly. Build your audience.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gray-50 dark:bg-stone-950 py-24 overflow-hidden">
        <!-- Flowing gradient background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-gradient-to-br from-rose-500/15 to-transparent rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-gradient-to-tl from-sky-500/15 to-transparent rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-stone-500 dark:text-stone-500 text-sm uppercase tracking-wider mb-6">Free forever</p>

            <h2 class="text-4xl md:text-5xl font-light text-gray-900 dark:text-white mb-6 leading-tight">
                Let audiences find<br>
                <span class="bg-gradient-to-r from-rose-300 via-cyan-300 to-sky-300 bg-clip-text text-transparent">your next performance</span>
            </h2>

            <p class="text-lg text-stone-500 dark:text-stone-400 mb-10 max-w-xl mx-auto">
                Join dance companies, studios, and troupes who've simplified how they share their schedule.
            </p>

            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-medium text-white bg-gradient-to-r from-rose-500 to-cyan-600 rounded-full hover:scale-105 transition-all shadow-lg shadow-rose-500/25 hover:shadow-rose-500/40">
                Create Your Schedule
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
        "name": "Event Schedule for Dance Groups",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Dance Performance Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your dance performances with fans worldwide. Email your audience directly - no algorithm. Sell tickets to recitals, workshops, and showcases with zero platform fees.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Season planning for dance companies",
            "Class and workshop scheduling",
            "Zero-fee ticket sales for performances",
            "Direct newsletter communication with audiences",
            "Virtual performance and livestream support",
            "Two-way Google Calendar sync",
            "Team collaboration for ensembles",
            "Auto-generated promotional graphics"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
