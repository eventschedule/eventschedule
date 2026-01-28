<x-marketing-layout>
    <x-slot name="title">Event Schedule for Art Galleries & Studios | Exhibition Calendar & Ticketing</x-slot>
    <x-slot name="description">Fill your gallery with collectors. Announce exhibitions, sell tickets to opening nights, and email your collectors directly. Free forever.</x-slot>
    <x-slot name="keywords">art gallery event calendar, exhibition management software, gallery opening tickets, art show calendar, artist studio events, gallery newsletter, art walk software, virtual gallery tours</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background with purple/violet and gold tones -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-purple-700/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-violet-800/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-40 right-1/3 w-[250px] h-[250px] bg-fuchsia-600/15 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 2s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">For Art Galleries & Studios</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Every opening night<br>
                <span class="text-gradient-gallery">deserves an audience.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                New exhibition opening? Your collectors know first. Email them directly - no paying Instagram to show your art to people who already follow you.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-purple-600 to-violet-700 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-purple-500/25">
                    Create your gallery's calendar
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Venue type tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-xs font-medium border border-purple-300 dark:border-purple-500/30">Contemporary</span>
                <span class="px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-xs font-medium border border-violet-300 dark:border-violet-500/30">Fine Art</span>
                <span class="px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-xs font-medium border border-fuchsia-300 dark:border-fuchsia-500/30">Photography</span>
                <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-xs font-medium border border-indigo-300 dark:border-indigo-500/30">Craft Studio</span>
                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-xs font-medium border border-amber-300 dark:border-amber-500/30">Collective</span>
                <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-xs font-medium border border-rose-300 dark:border-rose-500/30">Pop-up</span>
            </div>
        </div>
    </section>

    <!-- The Gallery Year Section - UNIQUE TO ART GALLERIES -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    The gallery year
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Exhibitions follow seasons. Spring solo shows, summer group exhibitions, fall art walks, and holiday markets. Your collectors want to know what's coming next.
                </p>
            </div>

            <!-- Seasonal Exhibition Calendar -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
                <!-- Winter -->
                <div class="bg-gradient-to-br from-indigo-100/60 to-violet-100/60 dark:from-indigo-900/40 dark:to-violet-900/40 rounded-2xl border border-indigo-300/40 dark:border-indigo-500/20 p-5 relative overflow-hidden group hover:border-indigo-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-indigo-400/30 text-4xl">&#10052;</div>
                    <div class="text-indigo-700 dark:text-indigo-300 text-xs font-semibold tracking-wider uppercase mb-3">Winter</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">New Year Group Show</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-violet-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm">Emerging Artists</span>
                        </div>
                    </div>
                </div>

                <!-- Spring -->
                <div class="bg-gradient-to-br from-emerald-100/60 to-teal-100/60 dark:from-emerald-900/40 dark:to-teal-900/40 rounded-2xl border border-emerald-300/40 dark:border-emerald-500/20 p-5 relative overflow-hidden group hover:border-emerald-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-emerald-400/30 text-4xl">&#127807;</div>
                    <div class="text-emerald-700 dark:text-emerald-300 text-xs font-semibold tracking-wider uppercase mb-3">Spring</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Solo Exhibitions</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-teal-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm">Art Walk Season</span>
                        </div>
                    </div>
                </div>

                <!-- Summer -->
                <div class="bg-gradient-to-br from-amber-100/60 to-yellow-100/60 dark:from-amber-900/40 dark:to-yellow-900/40 rounded-2xl border border-amber-300/40 dark:border-amber-500/20 p-5 relative overflow-hidden group hover:border-amber-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-amber-400/30 text-4xl">&#9728;</div>
                    <div class="text-amber-700 dark:text-amber-300 text-xs font-semibold tracking-wider uppercase mb-3">Summer</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Summer Group Show</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-yellow-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm">Artist Residencies</span>
                        </div>
                    </div>
                </div>

                <!-- Fall -->
                <div class="bg-gradient-to-br from-orange-100/60 to-red-100/60 dark:from-orange-900/40 dark:to-red-900/40 rounded-2xl border border-orange-300/40 dark:border-orange-500/20 p-5 relative overflow-hidden group hover:border-orange-500/40 transition-colors">
                    <div class="absolute top-2 right-2 text-orange-400/30 text-4xl">&#127810;</div>
                    <div class="text-orange-700 dark:text-orange-300 text-xs font-semibold tracking-wider uppercase mb-3">Fall</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Fall Solo Shows</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm">Open Studios</span>
                        </div>
                    </div>
                </div>

                <!-- Holiday -->
                <div class="bg-gradient-to-br from-purple-100/60 to-rose-100/60 dark:from-purple-900/40 dark:to-rose-900/40 rounded-2xl border border-purple-300/40 dark:border-purple-500/20 p-5 relative overflow-hidden group hover:border-purple-500/40 transition-colors col-span-2 lg:col-span-1">
                    <div class="absolute top-2 right-2 text-purple-400/30 text-4xl">&#127873;</div>
                    <div class="text-purple-700 dark:text-purple-300 text-xs font-semibold tracking-wider uppercase mb-3">Holiday</div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-purple-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Holiday Art Market</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div>
                            <span class="text-gray-600 dark:text-white/80 text-sm">Collector Events</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- First Fridays note -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <svg class="w-4 h-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Plus First Fridays, Art Walks, and recurring artist talks</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Exhibition Announcements - HERO FEATURE (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 border border-purple-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">New show opening? Your collectors know first.</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">Stop hoping your Instagram post gets seen. One click sends your exhibition announcement, opening reception invite, or artist talk straight to everyone who wants to know.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Skip the algorithm</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Your collectors, direct reach</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-purple-950 to-violet-950 rounded-2xl border border-purple-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white text-sm font-medium">Opening Reception</div>
                                            <div class="text-purple-600 dark:text-purple-300 text-xs">Sending to 1,847 collectors...</div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Sarah Chen: New Works</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Friday, 6-9 PM</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-200 dark:bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-fuchsia-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Artist in attendance</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Opening Night Tickets -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Exclusive receptions that sell out</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">VIP previews, opening night receptions, collector dinners. Sell tickets, limit capacity, scan at the door.</p>

                    <!-- Elegant ticket visual -->
                    <div class="flex justify-center">
                        <div class="bg-gradient-to-br from-amber-50 to-yellow-50 rounded-xl border border-amber-300/50 p-4 w-44 shadow-lg transform rotate-2 hover:rotate-0 transition-transform">
                            <div class="text-center">
                                <div class="text-amber-800 text-[10px] tracking-widest uppercase">VIP Preview</div>
                                <div class="text-amber-900 text-sm font-serif font-semibold mt-1">Opening Night</div>
                                <div class="text-amber-700 text-xl font-bold mt-2">$75</div>
                                <div class="text-amber-600 text-[10px] mt-1">Fri, Mar 15 &bull; 6:00 PM</div>
                                <div class="flex items-center justify-center gap-1 mt-2">
                                    <span class="text-amber-600 text-lg">&#127863;</span>
                                    <span class="text-amber-500 text-[9px]">Wine & Hors d'oeuvres</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Art Classes & Workshops -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-fuchsia-100 to-pink-100 dark:from-fuchsia-900 dark:to-pink-900 border border-fuchsia-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Recurring Events
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Painting. Pottery. Photography.</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Workshops and classes that repeat weekly. Set it once, your students always know when to show up.</p>

                    <!-- Class schedule visual -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-fuchsia-500/20 border border-fuchsia-500/30">
                            <div class="w-2 h-2 rounded-full bg-fuchsia-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Life Drawing</span>
                            <span class="ml-auto text-fuchsia-700 dark:text-fuchsia-300 text-xs">Tuesdays</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-pink-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Ceramics Studio</span>
                            <span class="ml-auto text-gray-500 text-xs">Saturdays</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Intro to Oils</span>
                            <span class="ml-auto text-gray-500 text-xs">Thursdays</span>
                        </div>
                    </div>
                </div>

                <!-- Artist Submissions (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900 dark:to-blue-900 border border-indigo-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Booking Inbox
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Artists come to you</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-4">Artists can submit exhibition proposals right from your calendar page. Review portfolios, approve or decline. No more scattered emails.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Portfolio links</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Exhibition proposals</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">One-click approve</span>
                            </div>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-5 border border-gray-200 dark:border-white/10">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">Artist Submissions</div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-indigo-500/20 border border-indigo-400/30">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white text-xs font-semibold">MK</div>
                                    <div class="flex-1">
                                        <div class="text-gray-900 dark:text-white text-sm font-medium">Maya Kim - Solo Show</div>
                                        <div class="text-indigo-600 dark:text-indigo-300 text-xs">Mixed media &bull; Spring 2025</div>
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
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center text-white text-xs font-semibold">JR</div>
                                    <div class="flex-1">
                                        <div class="text-gray-600 dark:text-gray-300 text-sm font-medium">James Rivera</div>
                                        <div class="text-gray-500 dark:text-gray-400 text-xs">Photography &bull; Group show</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Gallery Spaces -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Spaces
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Main gallery. Project room. Sculpture garden.</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Separate calendars for each space. Visitors see what's showing where.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-emerald-500/20 border border-emerald-500/30">
                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                            <span class="text-gray-900 dark:text-white text-sm">Main Gallery</span>
                            <span class="ml-auto text-emerald-700 dark:text-emerald-300 text-xs">2 shows</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Project Room</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">1 show</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                            <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Sculpture Garden</span>
                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-xs">3 events</span>
                        </div>
                    </div>
                </div>

                <!-- Virtual Gallery Tours -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-violet-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Online Events
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Tour the world from their screen</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Virtual exhibition tours, online artist talks. Sell tickets to viewers anywhere on earth.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-600 dark:text-gray-300 text-xs">Virtual Tour</span>
                            <div class="flex items-center gap-1">
                                <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                <span class="text-red-600 dark:text-red-400 text-[10px]">LIVE</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-center gap-2 text-gray-500 dark:text-white/70 text-xs">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>142 viewers worldwide</span>
                        </div>
                    </div>
                </div>

                <!-- Exhibition Graphics - BOTTOM RIGHT -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-pink-100 dark:from-rose-900 dark:to-pink-900 border border-rose-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Ready for Instagram</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Auto-generate promotional graphics for exhibitions. Download and post in seconds.</p>

                    <div class="flex justify-center">
                        <div class="relative w-32 h-32 bg-gradient-to-br from-purple-500/30 to-violet-500/30 rounded-xl border border-purple-400/30 p-2">
                            <div class="w-full h-full bg-gradient-to-br from-purple-600/40 to-fuchsia-700/40 rounded-lg flex flex-col items-center justify-center">
                                <div class="text-gray-900 dark:text-white text-[10px] font-semibold mb-1">NOW SHOWING</div>
                                <div class="text-amber-200 text-xs font-bold">New Works</div>
                                <div class="text-gray-500 dark:text-white/70 text-[8px] mt-1">Through April 30</div>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-rose-500 rounded-full flex items-center justify-center">
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

    <!-- Virtual Tours Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-purple-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 rounded-3xl border border-violet-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                Global Reach
                            </div>
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-violet-600 dark:group-hover:text-violet-300 transition-colors">Take your gallery global</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Virtual exhibition tours let collectors anywhere experience your shows. Online art classes reach students worldwide. Sell tickets to viewers who can't visit in person.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-4">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Virtual tours</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Online classes</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Artist talks</span>
                            </div>
                            <span class="inline-flex items-center text-violet-600 dark:text-violet-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more about online events
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-6 w-52">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Virtual Tour</span>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                                        <span class="text-red-400 text-[10px]">LIVE</span>
                                    </div>
                                </div>
                                <div class="bg-gradient-to-br from-purple-600/30 to-violet-600/30 rounded-lg p-4 text-center mb-3">
                                    <div class="text-2xl mb-1">&#127912;</div>
                                    <div class="text-gray-900 dark:text-white text-sm font-medium">Gallery Walkthrough</div>
                                    <div class="text-gray-500 dark:text-gray-400 text-xs">with Curator Lisa Park</div>
                                </div>
                                <div class="flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400 text-xs">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>234 viewers</span>
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
                    Perfect for all types of art spaces
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    From contemporary galleries to community studios, Event Schedule fits your creative space.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Contemporary Art Galleries -->
                <x-sub-audience-card
                    name="Contemporary Art Galleries"
                    description="Cutting-edge exhibitions, emerging artists, and collector events. Build your following in the contemporary art world."
                    icon-color="purple"
                    blog-slug="for-contemporary-art-galleries"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Fine Art Studios -->
                <x-sub-audience-card
                    name="Fine Art Studios"
                    description="Traditional painting, sculpture shows, and artist studio visits. Share your craft with collectors who appreciate fine art."
                    icon-color="violet"
                    blog-slug="for-fine-art-studios"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Photography Galleries -->
                <x-sub-audience-card
                    name="Photography Galleries"
                    description="Photo exhibitions, print sales events, and portfolio reviews. Connect photographers with collectors."
                    icon-color="indigo"
                    blog-slug="for-photography-galleries"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Craft & Maker Studios -->
                <x-sub-audience-card
                    name="Craft & Maker Studios"
                    description="Pottery, jewelry, textile arts, and maker workshops. Showcase handmade goods and teach your craft."
                    icon-color="amber"
                    blog-slug="for-craft-maker-studios"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Artist Collectives -->
                <x-sub-audience-card
                    name="Artist Collectives"
                    description="Shared studio spaces, group shows, and community events. Bring artists together and build creative community."
                    icon-color="fuchsia"
                    blog-slug="for-artist-collectives"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Pop-up & Alternative Spaces -->
                <x-sub-audience-card
                    name="Pop-up & Alternative Spaces"
                    description="Temporary exhibitions, warehouse shows, and unconventional venues. Create buzz for limited-time art experiences."
                    icon-color="rose"
                    blog-slug="for-popup-alternative-spaces"
                >
                    <x-slot:icon>
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
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
                    Get your gallery calendar online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-violet-700 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-purple-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Add your gallery</h3>
                    <p class="text-gray-600 text-sm">
                        Upload your logo, add your spaces (main gallery, project room, sculpture garden), and customize your branding.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-violet-700 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-purple-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Post exhibitions & events</h3>
                    <p class="text-gray-600 text-sm">
                        Opening receptions, artist talks, workshops, art walks. Add tickets if needed. Set recurring events once.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-violet-700 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-purple-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Reach your collectors</h3>
                    <p class="text-gray-600 text-sm">
                        Visitors follow your calendar. When you post a new exhibition, it goes straight to their inbox.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-purple-700 to-violet-900 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>
        <!-- Subtle purple glow -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-fuchsia-500/15 rounded-full blur-[100px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Stop paying to reach your own collectors
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Email your collectors directly. Fill your gallery. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-purple-700 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Art Galleries & Studios",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Art Gallery and Studio Event Management Software",
        "operatingSystem": "Web",
        "description": "Fill your gallery with collectors. Announce exhibitions, sell tickets to opening nights, and email your collectors directly. Reach your audience—no algorithm. Free forever.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Exhibition announcement newsletters",
            "Opening night ticketing",
            "Art classes and workshop scheduling",
            "Artist submission inbox",
            "Multiple gallery space calendars",
            "Virtual gallery tours",
            "Auto-generated social media graphics"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-gallery {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-marketing-layout>
