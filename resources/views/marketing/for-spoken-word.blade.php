<x-marketing-layout>
    <x-slot name="title">Event Schedule for Poets & Spoken Word | Share Your Readings</x-slot>
    <x-slot name="description">Share your poetry readings, open mics, and workshops. Sell tickets directly, reach fans with newsletters. Built for spoken word artists, slam poets, and writers. Zero platform fees.</x-slot>
    <x-slot name="keywords">poetry reading schedule, spoken word events, slam poetry calendar, poet schedule, open mic schedule, poetry workshop, literary events, poetry readings</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Spoken Word</x-slot>

    <!-- Hero Section - Typewriter/Literary Theme -->
    <section class="relative bg-white dark:bg-[#0f0e0c] py-32 overflow-hidden">
        <!-- Paper texture overlay -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;100&quot; height=&quot;100&quot; viewBox=&quot;0 0 100 100&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cfilter id=&quot;noise&quot;%3E%3CfeTurbulence type=&quot;fractalNoise&quot; baseFrequency=&quot;0.8&quot; numOctaves=&quot;4&quot; stitchTiles=&quot;stitch&quot;/%3E%3C/filter%3E%3Crect width=&quot;100%25&quot; height=&quot;100%25&quot; filter=&quot;url(%23noise)&quot;/%3E%3C/svg%3E');"></div>

        <!-- Warm glow -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[600px] h-[600px] bg-amber-500/10 rounded-full blur-[150px]"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-rose-500/10 rounded-full blur-[120px]"></div>
        </div>

        <!-- Scattered words floating in background -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none select-none">
            <span class="absolute top-[15%] left-[8%] text-white/[0.04] text-6xl font-serif italic rotate-[-8deg]">verse</span>
            <span class="absolute top-[25%] right-[12%] text-white/[0.03] text-5xl font-serif rotate-[5deg]">stanza</span>
            <span class="absolute top-[45%] left-[5%] text-white/[0.04] text-4xl font-serif rotate-[12deg]">breath</span>
            <span class="absolute bottom-[30%] right-[8%] text-white/[0.03] text-7xl font-serif italic rotate-[-5deg]">rhythm</span>
            <span class="absolute bottom-[15%] left-[15%] text-white/[0.04] text-5xl font-serif rotate-[3deg]">voice</span>
            <span class="absolute top-[60%] right-[20%] text-white/[0.03] text-4xl font-serif italic rotate-[-10deg]">line break</span>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-100 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700/30 mb-8">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                <span class="text-sm text-amber-700 dark:text-amber-200/80">For Poets, Spoken Word Artists & Writers</span>
            </div>

            <!-- Poetic headline with line breaks -->
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-serif text-gray-900 dark:text-white mb-8 leading-tight">
                <span class="block">Your words</span>
                <span class="block text-amber-600 dark:text-amber-200/90 italic">deserve witnesses.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-stone-400 max-w-2xl mx-auto mb-4 font-light">
                Not followers. Not subscribers. Not engagement metrics.
            </p>
            <p class="text-lg text-stone-500 max-w-2xl mx-auto mb-12">
                Real people in real rooms, listening. One link shows them where to find you.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-medium text-stone-900 bg-gradient-to-r from-amber-200 to-amber-100 rounded-xl hover:scale-105 transition-all shadow-lg shadow-amber-500/20">
                    Create your reading schedule
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Poetry-specific tags styled like typewriter labels -->
            <div class="mt-12 flex flex-wrap justify-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-800 dark:bg-stone-800/50 dark:text-stone-400 text-xs font-mono tracking-wide border border-amber-300 dark:border-stone-700/50">SLAM</span>
                <span class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-800 dark:bg-stone-800/50 dark:text-stone-400 text-xs font-mono tracking-wide border border-amber-300 dark:border-stone-700/50">OPEN MIC</span>
                <span class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-800 dark:bg-stone-800/50 dark:text-stone-400 text-xs font-mono tracking-wide border border-amber-300 dark:border-stone-700/50">FEATURED READING</span>
                <span class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-800 dark:bg-stone-800/50 dark:text-stone-400 text-xs font-mono tracking-wide border border-amber-300 dark:border-stone-700/50">WORKSHOP</span>
                <span class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-800 dark:bg-stone-800/50 dark:text-stone-400 text-xs font-mono tracking-wide border border-amber-300 dark:border-stone-700/50">BOOK LAUNCH</span>
                <span class="inline-flex items-center px-3 py-1.5 bg-amber-100 text-amber-800 dark:bg-stone-800/50 dark:text-stone-400 text-xs font-mono tracking-wide border border-amber-300 dark:border-stone-700/50">LIT FEST</span>
            </div>
        </div>
    </section>

    <!-- The Reality Section - Speaks to poet struggles -->
    <section class="bg-white dark:bg-[#0f0e0c] py-16 border-t border-gray-200 dark:border-stone-800/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div>
                    <div class="text-3xl font-serif text-amber-600 dark:text-amber-200/80 mb-2">3 venues</div>
                    <div class="text-stone-500 dark:text-stone-500 text-sm">Different open mics. Different nights. One schedule.</div>
                </div>
                <div>
                    <div class="text-3xl font-serif text-amber-600 dark:text-amber-200/80 mb-2">0 fees</div>
                    <div class="text-stone-500 dark:text-stone-500 text-sm">Keep every dollar from ticket sales. Poetry doesn't pay enough to share.</div>
                </div>
                <div>
                    <div class="text-3xl font-serif text-amber-600 dark:text-amber-200/80 mb-2">Your fans</div>
                    <div class="text-stone-500 dark:text-stone-500 text-sm">Email readers directly. No algorithm between you and your audience.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features - Literary themed -->
    <section class="bg-white dark:bg-[#0f0e0c] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- The Sign-Up Sheet (spans 2 cols) - Unique to poetry -->
                <div class="lg:col-span-2 relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-stone-900/50 border border-gray-200 dark:border-stone-800 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300/80 text-sm font-medium mb-4 border border-amber-200 dark:border-amber-500/20">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Your Schedule
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-serif text-gray-900 dark:text-white mb-4">Every reading. One place.</h3>
                            <p class="text-gray-500 dark:text-stone-400 text-lg mb-6">Coffee shops on Tuesdays. The bar series on Thursdays. That bookstore feature next month. Stop telling people to check your Instagram - give them one link that's always current.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-200 dark:bg-stone-800 text-gray-500 dark:text-stone-400 text-sm border border-gray-300 dark:border-stone-700">Open mics</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-200 dark:bg-stone-800 text-gray-500 dark:text-stone-400 text-sm border border-gray-300 dark:border-stone-700">Features</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-200 dark:bg-stone-800 text-gray-500 dark:text-stone-400 text-sm border border-gray-300 dark:border-stone-700">Slams</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-200 dark:bg-stone-800 text-gray-500 dark:text-stone-400 text-sm border border-gray-300 dark:border-stone-700">Book events</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <!-- Stylized reading schedule mockup -->
                            <div class="bg-stone-950 rounded-xl border border-stone-800 p-5 max-w-xs font-mono text-sm">
                                <div class="text-stone-600 text-xs mb-4 uppercase tracking-wider">Upcoming Readings</div>
                                <div class="space-y-4">
                                    <div class="border-l-2 border-amber-500/50 pl-3">
                                        <div class="text-amber-200/80 font-medium">Thu, Jan 30</div>
                                        <div class="text-stone-400">Open Mic @ Brewhaus</div>
                                        <div class="text-stone-600 text-xs">7pm / sign-up 6:30</div>
                                    </div>
                                    <div class="border-l-2 border-rose-500/50 pl-3">
                                        <div class="text-rose-200/80 font-medium">Sat, Feb 1</div>
                                        <div class="text-stone-400">Featured @ City Lights</div>
                                        <div class="text-stone-600 text-xs">8pm / $10</div>
                                    </div>
                                    <div class="border-l-2 border-stone-600 pl-3">
                                        <div class="text-stone-400 font-medium">Wed, Feb 5</div>
                                        <div class="text-stone-500">Writing Workshop</div>
                                        <div class="text-stone-600 text-xs">6pm / 4 spots left</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Your Readers -->
                <div class="relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-stone-900/50 border border-gray-200 dark:border-stone-800 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300/80 text-sm font-medium mb-4 border border-rose-200 dark:border-rose-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Newsletter
                    </div>
                    <h3 class="text-2xl font-serif text-gray-900 dark:text-white mb-3">Your readers, direct reach</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">New chapbook? Upcoming feature? Email the people who actually want to hear from you. No algorithm deciding who sees it.</p>

                    <!-- Mini email mockup -->
                    <div class="bg-stone-950 rounded-lg border border-stone-800 p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-rose-500/30 to-amber-500/30 flex items-center justify-center">
                                <span class="text-amber-200/80 text-xs font-serif">A</span>
                            </div>
                            <div>
                                <div class="text-stone-300 text-sm">New Reading Announced</div>
                                <div class="text-stone-600 text-xs">to 847 subscribers</div>
                            </div>
                        </div>
                        <div class="text-stone-500 text-xs italic border-t border-stone-800 pt-3">"I'll be featuring at..."</div>
                    </div>
                </div>

                <!-- Workshop Income - Unique to poets -->
                <div class="relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-stone-900/50 border border-gray-200 dark:border-stone-800 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300/80 text-sm font-medium mb-4 border border-emerald-200 dark:border-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Workshops
                    </div>
                    <h3 class="text-2xl font-serif text-gray-900 dark:text-white mb-3">Fill your workshops</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Teaching pays. List your writing workshops, craft classes, and generative sessions. Let students find and register easily.</p>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center p-2 rounded bg-emerald-500/10 border border-emerald-500/20">
                            <span class="text-stone-300 text-sm">Persona Poems</span>
                            <span class="text-emerald-400/80 text-xs">2 spots</span>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded bg-stone-800/50">
                            <span class="text-stone-400 text-sm">Form & Constraint</span>
                            <span class="text-stone-600 text-xs">FULL</span>
                        </div>
                    </div>
                </div>

                <!-- Sell Tickets + Chapbooks (spans 2 cols) -->
                <div class="lg:col-span-2 relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-stone-900/50 border border-gray-200 dark:border-stone-800 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300/80 text-sm font-medium mb-4 border border-amber-200 dark:border-amber-500/20">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Tickets & Books
                            </div>
                            <h3 class="text-3xl font-serif text-gray-900 dark:text-white mb-4">Keep what you earn</h3>
                            <p class="text-gray-500 dark:text-stone-400 text-lg">Sell tickets to your readings. Bundle with your chapbook. Zero platform fees - because poetry doesn't pay enough to give away a percentage.</p>
                        </div>
                        <div class="bg-stone-950 rounded-xl border border-stone-800 p-5">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-stone-800/50 border border-stone-700">
                                    <div>
                                        <div class="text-stone-200 font-medium">Reading Only</div>
                                        <div class="text-stone-500 text-xs">General admission</div>
                                    </div>
                                    <div class="text-xl font-medium text-stone-300">$8</div>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-lg bg-amber-500/10 border border-amber-500/30">
                                    <div>
                                        <div class="text-amber-100 font-medium">Reading + Chapbook</div>
                                        <div class="text-amber-300/60 text-xs">Signed copy included</div>
                                    </div>
                                    <div class="text-xl font-medium text-amber-200">$20</div>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-stone-800 text-center">
                                <span class="text-stone-600 text-xs">Stripe processes payment. You keep 100%.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Virtual Readings -->
                <a href="{{ marketing_url('/features/online-events') }}" class="group relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-stone-900/50 border border-gray-200 dark:border-stone-800 p-8 hover:border-gray-300 dark:hover:border-stone-700 transition-all">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300/80 text-sm font-medium mb-4 border border-sky-200 dark:border-sky-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Online
                    </div>
                    <h3 class="text-2xl font-serif text-gray-900 dark:text-white mb-3 group-hover:text-sky-200 transition-colors">Read to anywhere</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-4">Host virtual readings and workshops. Your audience isn't just local - poetry travels well over Zoom.</p>

                    <span class="inline-flex items-center text-sky-400/80 text-sm font-medium group-hover:gap-2 gap-1 transition-all">
                        Learn more
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </a>

                <!-- Google Calendar Sync -->
                <div class="relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-stone-900/50 border border-gray-200 dark:border-stone-800 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300/80 text-sm font-medium mb-4 border border-blue-200 dark:border-blue-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Sync
                    </div>
                    <h3 class="text-2xl font-serif text-gray-900 dark:text-white mb-3">Google Calendar sync</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Two-way sync. Add a reading to your personal calendar, it shows up on your public schedule.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-stone-800 rounded-lg border border-stone-700 p-2 w-16 text-center">
                            <div class="text-[9px] text-stone-500 mb-1">YOURS</div>
                            <div class="h-1 bg-amber-500/50 rounded mb-1"></div>
                            <div class="h-1 bg-stone-600 rounded w-3/4"></div>
                        </div>
                        <svg class="w-4 h-4 text-stone-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <div class="bg-stone-800 rounded-lg border border-stone-700 p-2 w-16 text-center">
                            <div class="text-[9px] text-stone-500 mb-1">GOOGLE</div>
                            <div class="h-1 bg-blue-500/50 rounded mb-1"></div>
                            <div class="h-1 bg-stone-600 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>

                <!-- Promo Graphics -->
                <div class="relative overflow-hidden rounded-2xl bg-gray-100 dark:bg-stone-900/50 border border-gray-200 dark:border-stone-800 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300/80 text-sm font-medium mb-4 border border-orange-200 dark:border-orange-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-serif text-gray-900 dark:text-white mb-3">Post-ready images</h3>
                    <p class="text-gray-500 dark:text-stone-400 mb-6">Auto-generate promo graphics. Share to Instagram without opening Canva.</p>

                    <div class="flex justify-center">
                        <div class="relative">
                            <div class="w-28 h-28 bg-gradient-to-br from-stone-800 to-stone-900 rounded-lg border border-stone-700 p-3 flex flex-col items-center justify-center text-center">
                                <div class="text-[8px] text-stone-500 uppercase tracking-wider mb-1">Poetry Reading</div>
                                <div class="text-amber-200/80 text-xs font-serif">Maya Torres</div>
                                <div class="text-stone-500 text-[8px] mt-1">City Lights / 8pm</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-orange-500/80 rounded-full flex items-center justify-center">
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

    <!-- Where Poets Read - Literary Venues -->
    <section class="bg-white dark:bg-[#0f0e0c] py-24 border-t border-gray-200 dark:border-stone-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-serif text-gray-900 dark:text-white mb-4">
                    Where poetry happens
                </h2>
                <p class="text-lg text-stone-500">
                    From open mics to festival stages
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Coffee Shops -->
                <div class="group text-center p-6 rounded-xl bg-gray-100 dark:bg-stone-900/30 border border-gray-200 dark:border-stone-800 hover:border-amber-800/50 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-500/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm text-gray-600 dark:text-stone-300">Coffee Shops</h3>
                </div>

                <!-- Bookstores -->
                <div class="group text-center p-6 rounded-xl bg-gray-100 dark:bg-stone-900/30 border border-gray-200 dark:border-stone-800 hover:border-rose-800/50 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-rose-600 dark:text-rose-500/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-sm text-gray-600 dark:text-stone-300">Bookstores</h3>
                </div>

                <!-- Bars & Lounges -->
                <div class="group text-center p-6 rounded-xl bg-gray-100 dark:bg-stone-900/30 border border-gray-200 dark:border-stone-800 hover:border-blue-800/50 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-500/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z" />
                        </svg>
                    </div>
                    <h3 class="text-sm text-gray-600 dark:text-stone-300">Bars & Lounges</h3>
                </div>

                <!-- Universities -->
                <div class="group text-center p-6 rounded-xl bg-gray-100 dark:bg-stone-900/30 border border-gray-200 dark:border-stone-800 hover:border-blue-800/50 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-500/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        </svg>
                    </div>
                    <h3 class="text-sm text-gray-600 dark:text-stone-300">Universities</h3>
                </div>

                <!-- Festivals -->
                <div class="group text-center p-6 rounded-xl bg-gray-100 dark:bg-stone-900/30 border border-gray-200 dark:border-stone-800 hover:border-orange-800/50 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-orange-600 dark:text-orange-500/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <h3 class="text-sm text-gray-600 dark:text-stone-300">Lit Festivals</h3>
                </div>

                <!-- Virtual -->
                <div class="group text-center p-6 rounded-xl bg-gray-100 dark:bg-stone-900/30 border border-gray-200 dark:border-stone-800 hover:border-sky-800/50 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-sky-100 dark:bg-sky-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 text-sky-600 dark:text-sky-500/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm text-gray-600 dark:text-stone-300">Virtual</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section - 6 Sub-types -->
    <section class="bg-stone-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-serif text-stone-900 dark:text-white mb-4">
                    Built for how poets actually work
                </h2>
                <p class="text-lg text-stone-500 dark:text-gray-400">
                    Whether you're on the slam circuit or launching a collection
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Slam Poets -->
                <x-sub-audience-card
                    name="Slam Poets"
                    description="Competition circuit, team slams, regional bouts. Track your season and let fans follow your scores."
                    icon-color="rose"
                    blog-slug="for-slam-poets"
                >
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Spoken Word Artists -->
                <x-sub-audience-card
                    name="Spoken Word Artists"
                    description="Performance poetry with music, movement, multimedia. Share your theatrical shows and collaborations."
                    icon-color="amber"
                    blog-slug="for-spoken-word-artists"
                >
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Page Poets -->
                <x-sub-audience-card
                    name="Page Poets"
                    description="Book launches, literary readings, publication events. Promote your collections alongside appearances."
                    icon-color="purple"
                    blog-slug="for-page-poets"
                >
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Open Mic Hosts -->
                <x-sub-audience-card
                    name="Open Mic Hosts"
                    description="Running your own series? Build a following for your recurring nights and featured readers."
                    icon-color="orange"
                    blog-slug="for-poetry-open-mic-hosts"
                >
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Literary Curators -->
                <x-sub-audience-card
                    name="Literary Curators"
                    description="Organizing reading series, festivals, salon events. Aggregate your programming in one place."
                    icon-color="indigo"
                    blog-slug="for-literary-curators"
                >
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Storytellers -->
                <x-sub-audience-card
                    name="Storytellers"
                    description="Oral storytelling, narrative performance, and story slams. Share your upcoming shows and captivate new audiences."
                    icon-color="emerald"
                    blog-slug="for-storytellers"
                >
                    <x-slot:icon>
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works - Simple, poetic -->
    <section class="bg-white dark:bg-[#0f0e0c] py-24 border-t border-stone-100 dark:border-stone-800/50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-serif text-stone-900 dark:text-white mb-4">
                    Three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <div class="text-center">
                    <div class="w-12 h-12 bg-stone-900 text-amber-200 text-xl font-serif rounded-full flex items-center justify-center mx-auto mb-6">
                        1
                    </div>
                    <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">Add your readings</h3>
                    <p class="text-stone-500 dark:text-gray-400 text-sm">
                        Open mics, features, workshops, launches. Import from Google Calendar or add them yourself.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-stone-900 text-amber-200 text-xl font-serif rounded-full flex items-center justify-center mx-auto mb-6">
                        2
                    </div>
                    <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">Share one link</h3>
                    <p class="text-stone-500 dark:text-gray-400 text-sm">
                        Put it in your bio. Your website. The back of your chapbook. Done.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-stone-900 text-amber-200 text-xl font-serif rounded-full flex items-center justify-center mx-auto mb-6">
                        3
                    </div>
                    <h3 class="text-lg font-medium text-stone-900 dark:text-white mb-2">Build your audience</h3>
                    <p class="text-stone-500 dark:text-gray-400 text-sm">
                        Readers follow you. They get notified when you're reading near them.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section - Literary themed -->
    <section class="relative bg-stone-900 py-24 overflow-hidden">
        <!-- Subtle texture -->
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;100&quot; height=&quot;100&quot; viewBox=&quot;0 0 100 100&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cfilter id=&quot;noise&quot;%3E%3CfeTurbulence type=&quot;fractalNoise&quot; baseFrequency=&quot;0.8&quot; numOctaves=&quot;4&quot; stitchTiles=&quot;stitch&quot;/%3E%3C/filter%3E%3Crect width=&quot;100%25&quot; height=&quot;100%25&quot; filter=&quot;url(%23noise)&quot;/%3E%3C/svg%3E');"></div>

        <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-stone-500 text-sm uppercase tracking-wider mb-6">Free forever</p>

            <h2 class="text-3xl md:text-4xl lg:text-5xl font-serif text-white mb-6 leading-tight">
                Stop shouting into<br>
                <span class="text-amber-200/80 italic">the algorithm.</span>
            </h2>

            <p class="text-lg text-stone-400 mb-10 max-w-xl mx-auto">
                Your words already found their audience once. Help them find you again.
            </p>

            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-medium text-stone-900 bg-amber-200 rounded-xl hover:bg-amber-100 hover:scale-105 transition-all">
                Create your schedule
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
        "name": "Event Schedule for Poets & Spoken Word",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Poetry Reading Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your poetry readings, open mics, and workshops. Sell tickets directly, reach fans with newsletters. Built for spoken word artists, slam poets, and writers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Open mic and reading tracking across multiple venues",
            "Zero-fee ticket sales with chapbook bundling",
            "Direct newsletter communication with readers",
            "Virtual reading and workshop support",
            "Two-way Google Calendar sync",
            "Workshop scheduling and registration",
            "Auto-generated promotional graphics for social media"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
