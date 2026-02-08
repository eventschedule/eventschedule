<x-marketing-layout>
    <x-slot name="title">Event Schedule for Fitness & Yoga Instructors | Share Your Class Schedule</x-slot>
    <x-slot name="description">Share your class schedule, sell drop-in passes, and reach students directly with newsletters. No algorithm blocking your content. Zero platform fees. Built for yoga teachers, personal trainers, and fitness instructors.</x-slot>
    <x-slot name="keywords">fitness class schedule, yoga class calendar, personal trainer booking, group fitness schedule, yoga studio app, pilates schedule, CrossFit class times, HIIT class booking, meditation class schedule, fitness instructor newsletter</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Fitness & Yoga</x-slot>

    <!-- Hero Section - Mesh Gradient -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute bottom-0 left-[-20%] w-[70%] h-[70%] bg-emerald-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-0 right-[-10%] w-[50%] h-[60%] bg-teal-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-green-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Flowing wave SVG pattern -->
        <div class="absolute inset-0 overflow-hidden opacity-[0.04] dark:opacity-[0.06]">
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 320" preserveAspectRatio="none" style="height: 40%;">
                <path fill="currentColor" class="text-emerald-600" d="M0,160L48,170.7C96,181,192,203,288,192C384,181,480,139,576,133.3C672,128,768,160,864,176C960,192,1056,192,1152,176C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 320" preserveAspectRatio="none" style="height: 35%;">
                <path fill="currentColor" class="text-teal-600" d="M0,224L48,213.3C96,203,192,181,288,186.7C384,192,480,224,576,234.7C672,245,768,235,864,213.3C960,192,1056,160,1152,165.3C1248,171,1344,213,1392,234.7L1440,256L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>

        <!-- Breathing animation circle -->
        <div class="absolute top-20 right-[15%] w-32 h-32 rounded-full border-2 border-emerald-500/10 dark:border-emerald-400/10 fitness-breathe hidden md:block"></div>
        <div class="absolute bottom-32 left-[10%] w-24 h-24 rounded-full border border-teal-500/8 dark:border-teal-400/8 fitness-breathe hidden md:block" style="animation-delay: 2s;"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Fitness & Yoga Instructors</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Your classes. Your students.<br>
                <span class="fitness-glow-text">No middleman.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                From morning flows to evening power classes. One link for all your sessions. Reach students directly - no algorithm burying your posts.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-lg shadow-emerald-500/25">
                    Create your schedule
                    <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Activity tags -->
            <div class="mt-14 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-medium border border-emerald-200 dark:border-emerald-800/50">Yoga</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300 text-xs font-medium border border-teal-200 dark:border-teal-800/50">Pilates</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 text-xs font-medium border border-green-200 dark:border-green-800/50">CrossFit</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-800/50">HIIT</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-800/50">Meditation</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-medium border border-blue-200 dark:border-blue-800/50">Barre</span>
            </div>
        </div>
    </section>

    <!-- The Struggle Stats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-emerald-400 mb-2">3%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of Instagram followers see your class posts</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-amber-400 mb-2">62%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of students forget class times without reminders</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-sky-400 mb-2">$50-100/mo</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">spent on booking platforms</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Class Update Newsletter (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/5 rounded-full blur-[100px]"></div>

                    <div class="relative flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-5 border border-emerald-200 dark:border-emerald-800/30">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Class Newsletter
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Send weekly class updates directly to students</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Schedule changes? New class added? Sub instructor this week? Send beautiful class schedules directly to your students' inbox. No algorithm gatekeeping.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Weekly schedule</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Class changes</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Workshop alerts</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-950 dark:to-teal-950 rounded-2xl border border-emerald-300 dark:border-emerald-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center text-white text-sm font-semibold">YS</div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-semibold text-sm">Your Studio</div>
                                            <div class="text-emerald-600 dark:text-emerald-300 text-xs">This Week's Classes</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="bg-gradient-to-br from-emerald-600/30 to-teal-600/30 rounded-lg p-2 border border-emerald-400/20">
                                            <div class="flex justify-between items-center">
                                                <div class="text-gray-900 dark:text-white text-xs font-medium">Morning Flow</div>
                                                <div class="text-gray-500 dark:text-gray-400 text-[10px]">Mon/Wed/Fri 7am</div>
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-br from-emerald-600/20 to-teal-600/20 rounded-lg p-2 border border-emerald-400/15">
                                            <div class="flex justify-between items-center">
                                                <div class="text-gray-900 dark:text-white text-xs font-medium">Power Yoga</div>
                                                <div class="text-gray-500 dark:text-gray-400 text-[10px]">Tue/Thu 6pm</div>
                                            </div>
                                        </div>
                                        <div class="bg-gradient-to-br from-emerald-600/20 to-teal-600/20 rounded-lg p-2 border border-emerald-400/15">
                                            <div class="flex justify-between items-center">
                                                <div class="text-gray-900 dark:text-white text-xs font-medium">HIIT & Stretch</div>
                                                <div class="text-gray-500 dark:text-gray-400 text-[10px]">Sat 9am</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-4 text-xs">
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-emerald-500 dark:text-emerald-400 font-semibold">78%</span> opened</div>
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-amber-500 dark:text-amber-400 font-semibold">42%</span> clicked</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Drop-in Ticketing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 border border-green-200 dark:border-white/10 p-8">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-green-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm font-medium mb-5 border border-green-200 dark:border-green-800/30">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Drop-in Ticketing
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Zero platform fees on class passes</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Sell drop-in passes and class packs. 100% of Stripe payments go to you. No monthly booking fees.</p>

                        <div class="bg-green-500/20 rounded-xl border border-green-400/30 p-4">
                            <div class="text-center mb-3">
                                <div class="text-green-300 text-xs">You keep</div>
                                <div class="text-gray-900 dark:text-white text-3xl font-bold">100%</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">of class payments</div>
                            </div>
                            <div class="border-t border-green-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="text-green-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- One Link -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 text-sm font-medium mb-5 border border-sky-200 dark:border-sky-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Share Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for all your classes</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Put it in your Instagram bio, website, or printed on your studio flyers. All your classes in one place.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-sky-500/20 border border-sky-400/30 mb-3">
                            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-xs font-mono">eventschedule.com/yourstudio</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-center">
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-300 text-[10px]">Instagram</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-300 text-[10px]">Website</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-300 text-[10px]">Flyers</div>
                        </div>
                    </div>
                </div>

                <!-- Recurring Classes (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-blue-100 dark:from-blue-900 dark:to-blue-900 border border-blue-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-medium mb-5 border border-blue-200 dark:border-blue-800/30">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Recurring Classes
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Set it once, runs every week</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">Create your weekly class schedule once. Recurring events auto-populate your calendar so students always know what's coming up.</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="bg-blue-500/10 rounded-xl border border-blue-400/20 p-3 w-full max-w-xs">
                                <div class="grid grid-cols-7 gap-1 text-center mb-2">
                                    <div class="text-blue-300 text-[10px] font-semibold">Mon</div>
                                    <div class="text-blue-300 text-[10px] font-semibold">Tue</div>
                                    <div class="text-blue-300 text-[10px] font-semibold">Wed</div>
                                    <div class="text-blue-300 text-[10px] font-semibold">Thu</div>
                                    <div class="text-blue-300 text-[10px] font-semibold">Fri</div>
                                    <div class="text-blue-300 text-[10px] font-semibold">Sat</div>
                                    <div class="text-blue-300 text-[10px] font-semibold">Sun</div>
                                </div>
                                <!-- Morning row -->
                                <div class="grid grid-cols-7 gap-1 mb-1">
                                    <div class="p-1 rounded bg-emerald-500/30 border border-emerald-400/30 text-[8px] text-gray-900 dark:text-white text-center">7am Flow</div>
                                    <div class="p-1 rounded bg-amber-500/30 border border-amber-400/30 text-[8px] text-gray-900 dark:text-white text-center">7am HIIT</div>
                                    <div class="p-1 rounded bg-emerald-500/30 border border-emerald-400/30 text-[8px] text-gray-900 dark:text-white text-center">7am Flow</div>
                                    <div class="p-1 rounded bg-amber-500/30 border border-amber-400/30 text-[8px] text-gray-900 dark:text-white text-center">7am HIIT</div>
                                    <div class="p-1 rounded bg-emerald-500/30 border border-emerald-400/30 text-[8px] text-gray-900 dark:text-white text-center">7am Flow</div>
                                    <div class="p-1 rounded bg-teal-500/30 border border-teal-400/30 text-[8px] text-gray-900 dark:text-white text-center">9am Power</div>
                                    <div class="p-1 rounded bg-blue-500/30 border border-blue-400/30 text-[8px] text-gray-900 dark:text-white text-center">10am Gentle</div>
                                </div>
                                <!-- Evening row -->
                                <div class="grid grid-cols-7 gap-1">
                                    <div class="p-1 rounded bg-teal-500/30 border border-teal-400/30 text-[8px] text-gray-900 dark:text-white text-center">6pm Power</div>
                                    <div class="p-1 rounded bg-cyan-500/30 border border-cyan-400/30 text-[8px] text-gray-900 dark:text-white text-center">6pm Pilates</div>
                                    <div class="p-1 rounded bg-teal-500/30 border border-teal-400/30 text-[8px] text-gray-900 dark:text-white text-center">6pm Power</div>
                                    <div class="p-1 rounded bg-cyan-500/30 border border-cyan-400/30 text-[8px] text-gray-900 dark:text-white text-center">6pm Pilates</div>
                                    <div class="p-1 rounded bg-cyan-500/30 border border-cyan-400/30 text-[8px] text-gray-900 dark:text-white text-center">6pm Stretch</div>
                                    <div class="p-1 rounded bg-gray-500/20 border border-gray-400/10 text-[8px] text-gray-400 text-center">-</div>
                                    <div class="p-1 rounded bg-gray-500/20 border border-gray-400/10 text-[8px] text-gray-400 text-center">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multi-Instructor Teams -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium mb-5 border border-amber-200 dark:border-amber-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Team
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Multi-instructor teams</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Invite your teaching team. Everyone can add classes and manage the schedule together.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-xs font-semibold">SR</div>
                            <div class="flex-1">
                                <div class="text-gray-900 dark:text-white text-sm">Sarah</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[10px]">Lead</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-xs font-semibold">JP</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-sm">Jamie</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-300 text-[10px]">Instructor</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-yellow-500 to-amber-500 flex items-center justify-center text-white text-xs font-semibold">MK</div>
                            <div class="flex-1">
                                <div class="text-gray-600 dark:text-gray-300 text-sm">Maya</div>
                            </div>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-yellow-500/20 text-yellow-300 text-[10px]">Sub</span>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-medium mb-5 border border-blue-200 dark:border-blue-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Google Calendar two-way sync</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Classes, private sessions, workshops - all synced with your personal calendar automatically.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-emerald-400/40 rounded text-[6px] text-white px-1">Class</div>
                                <div class="h-1.5 bg-amber-400/40 rounded text-[6px] text-white px-1">Private</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-600 dark:text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class Analytics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-sky-100 dark:from-cyan-900 dark:to-sky-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-5 border border-cyan-200 dark:border-cyan-800/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Analytics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">See which classes are most popular</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Track views, clicks, and ticket sales per class to optimize your schedule.</p>

                    <div class="space-y-2">
                        <div class="flex items-end gap-1.5 justify-center h-20">
                            <div class="w-6 bg-cyan-500/40 rounded-t border border-cyan-400/30" style="height: 90%">
                                <div class="text-[8px] text-gray-900 dark:text-white text-center mt-1">Flow</div>
                            </div>
                            <div class="w-6 bg-cyan-500/30 rounded-t border border-cyan-400/20" style="height: 65%">
                                <div class="text-[8px] text-gray-900 dark:text-white text-center mt-1">HIIT</div>
                            </div>
                            <div class="w-6 bg-cyan-500/40 rounded-t border border-cyan-400/30" style="height: 80%">
                                <div class="text-[8px] text-gray-900 dark:text-white text-center mt-1">Power</div>
                            </div>
                            <div class="w-6 bg-cyan-500/20 rounded-t border border-cyan-400/15" style="height: 45%">
                                <div class="text-[8px] text-gray-900 dark:text-white text-center mt-1">Pilates</div>
                            </div>
                            <div class="w-6 bg-cyan-500/30 rounded-t border border-cyan-400/20" style="height: 55%">
                                <div class="text-[8px] text-gray-900 dark:text-white text-center mt-1">Gentle</div>
                            </div>
                        </div>
                        <div class="text-center text-gray-500 dark:text-gray-400 text-[10px]">Class popularity this month</div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Weekly Class Schedule Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Your week at a glance
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Students see your full week - no more DMs asking "when's your next class?"
                </p>
            </div>

            <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-950/50 dark:to-teal-950/50 rounded-3xl border border-emerald-200 dark:border-emerald-800/30 p-6 lg:p-8">
                <div class="grid grid-cols-7 gap-3">
                    <!-- Monday -->
                    <div class="text-center">
                        <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-3">Mon</div>
                        <div class="bg-emerald-500/20 dark:bg-emerald-500/30 rounded-xl p-3 border border-emerald-400/30">
                            <div class="text-gray-900 dark:text-white text-xs font-semibold">Morning Flow</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">7:00 AM</div>
                            <div class="flex justify-center gap-0.5 mt-2" title="Low intensity">
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400/20"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-emerald-400/20"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Tuesday -->
                    <div class="text-center">
                        <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-3">Tue</div>
                        <div class="bg-amber-500/20 dark:bg-amber-500/30 rounded-xl p-3 border border-amber-400/30">
                            <div class="text-gray-900 dark:text-white text-xs font-semibold">Lunch Express</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">12:00 PM</div>
                            <div class="flex justify-center gap-0.5 mt-2" title="Medium intensity">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400/20"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Wednesday -->
                    <div class="text-center">
                        <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-3">Wed</div>
                        <div class="bg-teal-500/20 dark:bg-teal-500/30 rounded-xl p-3 border border-teal-400/30 ring-2 ring-teal-400/50">
                            <div class="text-gray-900 dark:text-white text-xs font-semibold">Power Yoga</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">6:00 PM</div>
                            <div class="flex justify-center gap-0.5 mt-2" title="High intensity">
                                <div class="w-1.5 h-1.5 rounded-full bg-teal-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-teal-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-teal-400"></div>
                            </div>
                            <div class="text-teal-600 dark:text-teal-300 text-[9px] mt-1 font-medium">Popular</div>
                        </div>
                    </div>
                    <!-- Thursday -->
                    <div class="text-center">
                        <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-3">Thu</div>
                        <div class="bg-gray-200/60 dark:bg-white/5 rounded-xl p-3 border border-gray-300 dark:border-white/10">
                            <div class="text-gray-500 dark:text-gray-400 text-xs font-semibold">Rest Day</div>
                            <div class="text-gray-400 dark:text-gray-500 text-[10px] mt-1">Recovery</div>
                            <div class="mt-2 w-full h-1 bg-gray-300/40 rounded-full"></div>
                        </div>
                    </div>
                    <!-- Friday -->
                    <div class="text-center">
                        <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-3">Fri</div>
                        <div class="bg-cyan-500/20 dark:bg-cyan-500/30 rounded-xl p-3 border border-cyan-400/30">
                            <div class="text-gray-900 dark:text-white text-xs font-semibold">HIIT & Stretch</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">5:30 PM</div>
                            <div class="flex justify-center gap-0.5 mt-2" title="High intensity">
                                <div class="w-1.5 h-1.5 rounded-full bg-cyan-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-cyan-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-cyan-400"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Saturday -->
                    <div class="text-center">
                        <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-3">Sat</div>
                        <div class="bg-blue-500/20 dark:bg-blue-500/30 rounded-xl p-3 border border-blue-400/30 ring-2 ring-blue-400/50">
                            <div class="text-gray-900 dark:text-white text-xs font-semibold">Weekend Workshop</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">9:00 AM</div>
                            <div class="flex justify-center gap-0.5 mt-2" title="Medium intensity">
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-400/20"></div>
                            </div>
                            <div class="text-blue-600 dark:text-blue-300 text-[9px] mt-1 font-medium">Popular</div>
                        </div>
                    </div>
                    <!-- Sunday -->
                    <div class="text-center">
                        <div class="text-emerald-700 dark:text-emerald-300 text-sm font-semibold mb-3">Sun</div>
                        <div class="bg-green-500/20 dark:bg-green-500/30 rounded-xl p-3 border border-green-400/30">
                            <div class="text-gray-900 dark:text-white text-xs font-semibold">Gentle Sunday</div>
                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">10:00 AM</div>
                            <div class="flex justify-center gap-0.5 mt-2" title="Low intensity">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400/20"></div>
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400/20"></div>
                            </div>
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
                    Perfect for all types of fitness professionals
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether you teach yoga or coach CrossFit, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Yoga Teachers -->
                <x-sub-audience-card
                    name="Yoga Teachers"
                    description="Share your flow classes, workshops, and retreats with your students. Build a dedicated community around your practice."
                    icon-color="emerald"
                    blog-slug="for-yoga-teachers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Personal Trainers -->
                <x-sub-audience-card
                    name="Personal Trainers"
                    description="Show your availability for one-on-one sessions and group training. Let clients book directly from your schedule."
                    icon-color="teal"
                    blog-slug="for-personal-trainers"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Pilates Instructors -->
                <x-sub-audience-card
                    name="Pilates Instructors"
                    description="Manage your mat and reformer classes in one place. Students always know your latest schedule."
                    icon-color="green"
                    blog-slug="for-pilates-instructors"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- CrossFit Coaches -->
                <x-sub-audience-card
                    name="CrossFit Coaches"
                    description="Post your WOD schedule and special events. Keep your box members informed and engaged."
                    icon-color="amber"
                    blog-slug="for-crossfit-coaches"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Group Fitness Instructors -->
                <x-sub-audience-card
                    name="Group Fitness Instructors"
                    description="Share your spin, Zumba, and bootcamp schedule across multiple gyms and studios."
                    icon-color="cyan"
                    blog-slug="for-group-fitness-instructors"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Meditation Guides -->
                <x-sub-audience-card
                    name="Meditation Guides"
                    description="List your guided sessions, sound baths, and mindfulness workshops. Build a calm, connected community."
                    icon-color="violet"
                    blog-slug="for-meditation-guides"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Three steps to a full class
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-600 to-teal-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your classes</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Import from Google Calendar or add classes manually. Set up recurring schedules and drop-in ticketing.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-600 to-teal-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your link</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add it to your Instagram bio, website, studio flyers, or anywhere students find you.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-emerald-600 to-teal-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-emerald-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Grow your community</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Students follow your schedule and get notified about new classes. Build your audience on your terms.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-emerald-200 dark:border-emerald-900/20">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-emerald-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-teal-600/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your classes. Your students. No middleman.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Stop posting into the void. Fill your classes.<br class="hidden md:block">Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-emerald-500/20">
                Get Started Free
                <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <p class="mt-6 text-gray-500 text-sm">No credit card required</p>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule for Fitness & Yoga Instructors",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Fitness Class Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your class schedule, sell drop-in passes, and reach students directly with newsletters. Built for yoga teachers, personal trainers, and fitness instructors.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Weekly class schedule newsletters to students",
            "Custom schedule URL for Instagram, website, flyers",
            "Zero-fee drop-in ticketing and class passes",
            "Google Calendar two-way sync",
            "Recurring class schedule management",
            "Multi-instructor team collaboration",
            "Student follower notifications"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style {!! nonce_attr() !!}>
        .fitness-glow-text {
            background: linear-gradient(135deg, #10b981, #14b8a6, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(16, 185, 129, 0.3);
        }
        .dark .fitness-glow-text {
            background: linear-gradient(135deg, #34d399, #2dd4bf, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(52, 211, 153, 0.3);
        }

        @keyframes breathe {
            0%, 100% { transform: scale(0.95); opacity: 0.5; }
            50% { transform: scale(1.08); opacity: 0.9; }
        }

        .fitness-breathe {
            animation: breathe 4s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .fitness-breathe {
                animation: none;
            }
        }
    </style>
</x-marketing-layout>
