<x-marketing-layout>
    <x-slot name="title">Event Schedule for Musicians & Bands | Share Your Tour Dates</x-slot>
    <x-slot name="description">Share your tour dates, sell tickets, and reach fans directly with newsletters. No algorithm blocking your content. Zero platform fees. Built for musicians, bands, and solo artists.</x-slot>
    <x-slot name="keywords">musician schedule, band tour dates, artist gig calendar, tour schedule app, band booking, sell concert tickets, musician newsletter, fan newsletter, artist mailing list</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm text-gray-300">For Musicians, Bands & Solo Artists</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Your gigs. Your fans.<br>
                <span class="text-gradient-cyan">No middleman.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                From coffee shop open mics to sold-out tours. One link for all your shows. Reach fans directly - no algorithm burying your posts.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-cyan-600 to-teal-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-cyan-500/25">
                    Create your schedule
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Genre tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-xs font-medium border border-cyan-500/30">Rock</span>
                <span class="px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-xs font-medium border border-teal-500/30">Jazz</span>
                <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-medium border border-emerald-500/30">Folk</span>
                <span class="px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-medium border border-blue-500/30">Blues</span>
                <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-medium border border-indigo-500/30">Classical</span>
                <span class="px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-xs font-medium border border-violet-500/30">Country</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Newsletter (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-900/50 to-teal-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Newsletter
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Announce tours directly to fans</h3>
                            <p class="text-gray-400 text-lg mb-6">New album dropping? Going on tour? Send beautiful show graphics directly to your fans' inbox. No algorithm gatekeeping. Your music, your audience.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Tour announcements</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Album releases</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Show reminders</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-cyan-500/20 to-teal-500/20 rounded-2xl border border-cyan-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white text-sm font-semibold">JB</div>
                                        <div>
                                            <div class="text-white font-semibold text-sm">The Midnight Hour</div>
                                            <div class="text-cyan-300 text-xs">Summer Tour 2025!</div>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-cyan-600/30 to-teal-600/30 rounded-xl p-3 border border-cyan-400/20">
                                        <div class="text-center">
                                            <div class="text-white text-xs font-semibold mb-1">THIS SATURDAY</div>
                                            <div class="text-cyan-300 text-sm font-bold">Live at The Roxy</div>
                                            <div class="text-gray-400 text-[10px] mt-1">Doors 7 PM • $25</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ticketing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-green-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Door sales & presales</h3>
                    <p class="text-gray-400 mb-6">Run presales for your fans. Zero platform fees - Stripe goes directly to your account. QR check-in at the door.</p>

                    <div class="flex justify-center">
                        <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-4 w-full max-w-[200px]">
                            <div class="text-center mb-3">
                                <div class="text-emerald-300 text-xs">You keep</div>
                                <div class="text-white text-3xl font-bold">100%</div>
                                <div class="text-gray-400 text-xs">of ticket sales</div>
                            </div>
                            <div class="border-t border-emerald-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-400">Platform fee</span>
                                    <span class="text-emerald-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Venue Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900/50 to-purple-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Venue Sync
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Clubs, theaters & festivals</h3>
                    <p class="text-gray-400 mb-6">When venues add your show to their calendar, it automatically appears on yours. One booking, both schedules.</p>

                    <div class="flex items-center justify-center gap-2">
                        <div class="bg-violet-500/20 rounded-lg border border-violet-400/30 p-2 w-16">
                            <div class="text-[10px] text-violet-300 text-center mb-1">Venue</div>
                            <div class="h-1.5 bg-white/20 rounded mb-1"></div>
                            <div class="h-1.5 bg-violet-400/40 rounded w-3/4"></div>
                        </div>
                        <svg class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <div class="bg-white/10 rounded-lg border border-white/20 p-2 w-16">
                            <div class="text-[10px] text-gray-300 text-center mb-1">You</div>
                            <div class="h-1.5 bg-white/20 rounded mb-1"></div>
                            <div class="h-1.5 bg-cyan-400/40 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>

                <!-- Share Link (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-blue-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Share Link
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-4">One link for all your gigs</h3>
                            <p class="text-gray-400 text-lg">Add it to your Spotify bio, Bandcamp, EPK, or website. Fans see all your upcoming shows in one place.</p>
                        </div>
                        <div class="bg-black/30 rounded-2xl p-5 border border-white/10">
                            <div class="text-xs text-gray-500 mb-2">Your schedule link</div>
                            <div class="flex items-center gap-2 p-3 rounded-xl bg-indigo-500/20 border border-indigo-400/30">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span class="text-white text-sm font-mono">eventschedule.com/yourband</span>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <div class="flex-1 text-center p-2 rounded-lg bg-white/5">
                                    <div class="text-indigo-300 text-xs">Spotify bio</div>
                                </div>
                                <div class="flex-1 text-center p-2 rounded-lg bg-white/5">
                                    <div class="text-indigo-300 text-xs">Bandcamp</div>
                                </div>
                                <div class="flex-1 text-center p-2 rounded-lg bg-white/5">
                                    <div class="text-indigo-300 text-xs">EPK</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900/50 to-indigo-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Gigs, rehearsals & sessions</h3>
                    <p class="text-gray-400 mb-6">Two-way Google sync. Add a gig, recording session, or rehearsal and it shows everywhere. Real-time webhook sync.</p>

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
                            <svg class="w-4 h-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <div class="bg-white/10 rounded-xl border border-white/20 p-3 w-20">
                            <div class="text-[10px] text-gray-300 mb-1 text-center">Google</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-blue-400/40 rounded"></div>
                                <div class="h-1.5 bg-green-400/40 rounded w-3/4"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Team Management -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-900/50 to-orange-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Team
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Band, manager & agent</h3>
                    <p class="text-gray-400 mb-6">Invite band members, your manager, or booking agent. Everyone can add gigs and see what's coming up.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-xs font-semibold">JM</div>
                            <div class="flex-1">
                                <div class="text-white text-sm">Jamie</div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[10px]">Lead</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-xs font-semibold">MK</div>
                            <div class="flex-1">
                                <div class="text-gray-300 text-sm">Mike</div>
                            </div>
                            <span class="px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-300 text-[10px]">Manager</span>
                        </div>
                    </div>
                </div>

                <!-- Followers -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-900/50 to-rose-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        Followers
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Fans follow your tour</h3>
                    <p class="text-gray-400 mb-6">Build your following on your own platform. When you add a show near them, they get notified automatically.</p>

                    <div class="flex items-center justify-center">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-500 to-rose-500 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-rose-500 to-red-500 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-red-500 to-orange-500 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-white/20 border-2 border-[#0a0a0f] flex items-center justify-center text-white text-xs">+47</div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-pink-300 text-xs">50 fans following your tour</div>
                </div>

            </div>
        </div>
    </section>

    <!-- Career Journey Section -->
    <section class="bg-[#0a0a0f] py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    From open mics to headlining tours
                </h2>
                <p class="text-xl text-gray-500">
                    Event Schedule grows with your music career
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Open Mics -->
                <div class="bg-gradient-to-br from-[#0f1a1a] to-[#0a0a0f] rounded-2xl p-6 border border-cyan-900/20 hover:border-cyan-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-900/30 mb-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Open mic nights</h3>
                    <p class="text-gray-400 text-sm">Playing coffee shops and local bars. Track your spots and let friends know where to catch you.</p>
                </div>

                <!-- Local Gigging -->
                <div class="bg-gradient-to-br from-[#0f1a17] to-[#0a0a0f] rounded-2xl p-6 border border-teal-900/20 hover:border-teal-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-900/30 mb-4">
                        <svg class="w-6 h-6 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Local gigging</h3>
                    <p class="text-gray-400 text-sm">Regular slots at venues in your city. Build a local following and start selling your own tickets.</p>
                </div>

                <!-- Regional Tours -->
                <div class="bg-gradient-to-br from-[#0f1520] to-[#0a0a0f] rounded-2xl p-6 border border-blue-900/20 hover:border-blue-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-900/30 mb-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Regional tours</h3>
                    <p class="text-gray-400 text-sm">Weekend runs and opening slots. Fans in neighboring cities follow to know when you're coming through.</p>
                </div>

                <!-- Headlining -->
                <div class="bg-gradient-to-br from-[#12101a] to-[#0a0a0f] rounded-2xl p-6 border border-indigo-900/20 hover:border-indigo-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-900/30 mb-4">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Headlining</h3>
                    <p class="text-gray-400 text-sm">Your name on the marquee. Email your fans directly and sell out your own shows.</p>
                </div>

                <!-- Touring -->
                <div class="bg-gradient-to-br from-[#15101a] to-[#0a0a0f] rounded-2xl p-6 border border-violet-900/20 hover:border-violet-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-900/30 mb-4">
                        <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">National tours</h3>
                    <p class="text-gray-400 text-sm">Multi-city runs across the country. One link shows fans everywhere when you're hitting their town.</p>
                </div>

                <!-- Festivals -->
                <div class="bg-gradient-to-br from-[#1a1510] to-[#0a0a0f] rounded-2xl p-6 border border-amber-900/20 hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-900/30 mb-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-2">Festivals & special events</h3>
                    <p class="text-gray-400 text-sm">Festival slots, album release shows, and special performances all in one professional calendar.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Perfect for all types of musicians
                </h2>
                <p class="text-xl text-gray-500">
                    Whether you're a solo artist or a touring band, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Solo Artists -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-cyan-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 mb-4">
                        <svg class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Solo Artists</h3>
                    <p class="text-gray-600 text-sm">Share your acoustic nights, open mics, and solo performances with your growing fanbase.</p>
                </div>

                <!-- Rock & Pop Bands -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-teal-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 mb-4">
                        <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Rock & Pop Bands</h3>
                    <p class="text-gray-600 text-sm">Coordinate your tour dates across the whole band and let fans follow along.</p>
                </div>

                <!-- Jazz Musicians -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-indigo-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Jazz Musicians</h3>
                    <p class="text-gray-600 text-sm">List your residencies, jam sessions, and special performances at clubs and festivals.</p>
                </div>

                <!-- Cover Bands -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-violet-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-100 mb-4">
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Cover Bands</h3>
                    <p class="text-gray-600 text-sm">Show your weekly bar gigs and private events all in one professional calendar.</p>
                </div>

                <!-- Tribute Acts -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Tribute Acts</h3>
                    <p class="text-gray-600 text-sm">Build a dedicated fanbase for your tribute shows and special themed events.</p>
                </div>

                <!-- Session Musicians -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-amber-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Session Musicians</h3>
                    <p class="text-gray-600 text-sm">Show your availability and let bands know when you're free for gigs and recording sessions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500">
                    Get your tour schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-teal-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-cyan-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Add Your Gigs</h3>
                    <p class="text-gray-600 text-sm">
                        Import from Google Calendar or add tour dates manually. Set up ticket sales if you want.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-teal-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-cyan-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Share Your Link</h3>
                    <p class="text-gray-600 text-sm">
                        Add your schedule to your Spotify bio, Bandcamp, EPK, or anywhere fans find you.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-teal-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-cyan-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Grow Your Fanbase</h3>
                    <p class="text-gray-600 text-sm">
                        Fans follow your schedule and get notified about shows near them. Build your audience on your terms.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-cyan-600 to-teal-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Your music. Your fans. No gatekeepers.
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Stop posting into the void. Fill your shows. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-cyan-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
        "name": "Event Schedule for Musicians",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Musician Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your tour dates, sell tickets, and reach fans directly with newsletters. Built for musicians, bands, and solo artists.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Tour announcements to fans",
            "Custom schedule URL for Spotify, Bandcamp, EPK",
            "Zero-fee ticket sales with door check-in",
            "Google Calendar sync for gigs, rehearsals, sessions",
            "Venue auto-linking for clubs, theaters, festivals",
            "Band and manager collaboration",
            "Fan notifications for nearby shows"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-cyan {
            background: linear-gradient(135deg, #06b6d4, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</x-marketing-layout>
