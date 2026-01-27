<x-marketing-layout>
    <x-slot name="title">Event Schedule for Circus & Acrobatics | Share Your Performances</x-slot>
    <x-slot name="description">Share your circus performances, sell tickets directly, and reach your audience with newsletters. No social media algorithms. Zero platform fees. Built for aerialists, acrobats, and circus performers.</x-slot>
    <x-slot name="keywords">circus schedule, aerialist calendar, acrobat booking, circus performer schedule, aerial show schedule, circus troupe management, sell circus tickets, performer newsletter</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section - The Big Top -->
    <section class="relative bg-circus-dark py-32 overflow-hidden">
        <!-- Tent stripe background -->
        <div class="absolute inset-0 opacity-[0.07]" style="background: repeating-linear-gradient(-45deg, #8b0000 0px, #8b0000 25px, #1a0505 25px, #1a0505 50px);"></div>

        <!-- Spotlight beams -->
        <div class="absolute top-0 left-1/4 w-[400px] h-[600px] spotlight-beam"></div>
        <div class="absolute top-0 right-1/4 w-[400px] h-[600px] spotlight-beam" style="animation-delay: 0.5s;"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[500px] h-[700px] spotlight-beam-center"></div>

        <!-- Aerial Silk Lines -->
        <div class="absolute top-0 left-8 md:left-16 lg:left-24 w-1 h-64 bg-gradient-to-b from-gold/60 via-gold/30 to-transparent rounded-full animate-silk-sway"></div>
        <div class="absolute top-0 left-12 md:left-24 lg:left-32 w-0.5 h-48 bg-gradient-to-b from-crimson/50 via-crimson/20 to-transparent rounded-full animate-silk-sway" style="animation-delay: 0.5s;"></div>
        <div class="absolute top-0 right-8 md:right-16 lg:right-24 w-1 h-64 bg-gradient-to-b from-gold/60 via-gold/30 to-transparent rounded-full animate-silk-sway" style="animation-delay: 0.3s;"></div>
        <div class="absolute top-0 right-12 md:right-24 lg:right-32 w-0.5 h-48 bg-gradient-to-b from-crimson/50 via-crimson/20 to-transparent rounded-full animate-silk-sway" style="animation-delay: 0.8s;"></div>

        <!-- Star sparkles -->
        <div class="absolute top-32 left-[15%] w-2 h-2 bg-gold rounded-full animate-twinkle"></div>
        <div class="absolute top-48 right-[20%] w-1.5 h-1.5 bg-gold rounded-full animate-twinkle" style="animation-delay: 0.7s;"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- "Now Performing" marquee badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2 rounded-sm marquee-badge mb-8">
                <div class="w-2 h-2 bg-gold rounded-full animate-pulse"></div>
                <span class="text-sm text-ivory font-medium tracking-widest uppercase">Now Performing</span>
                <div class="w-2 h-2 bg-gold rounded-full animate-pulse"></div>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-ivory mb-8 leading-tight">
                <span class="text-gradient-circus">Defy gravity.</span><br>
                Fill every seat.
            </h1>

            <p class="text-xl md:text-2xl text-ivory/80 max-w-3xl mx-auto mb-12">
                From the training studio to the big top. One link for all your shows. Venues book you, fans follow you, no algorithm decides who sees it.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-gray-900 rounded-sm hover:scale-105 transition-all shadow-lg shadow-gold/25 marquee-button" style="background: linear-gradient(to right, #FFD700, #fbbf24);">
                    Create your performance schedule
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Performance tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-3">
                <span class="px-3 py-1 rounded-sm bg-gold/15 text-gold text-xs font-medium border border-gold/30">Aerial</span>
                <span class="px-3 py-1 rounded-sm bg-gold/15 text-gold text-xs font-medium border border-gold/30">Fire</span>
                <span class="px-3 py-1 rounded-sm bg-gold/15 text-gold text-xs font-medium border border-gold/30">Acrobatics</span>
                <span class="px-3 py-1 rounded-sm bg-gold/15 text-gold text-xs font-medium border border-gold/30">Juggling</span>
                <span class="px-3 py-1 rounded-sm bg-gold/15 text-gold text-xs font-medium border border-gold/30">Stilt Walking</span>
                <span class="px-3 py-1 rounded-sm bg-gold/15 text-gold text-xs font-medium border border-gold/30">Contortion</span>
            </div>
        </div>
    </section>

    <!-- Stream Your Act - Online Events Section -->
    <section class="relative bg-circus-dark py-24 overflow-hidden border-t border-gold/10">
        <!-- Curtain effect -->
        <div class="absolute left-0 top-0 bottom-0 w-24 md:w-48 curtain-left"></div>
        <div class="absolute right-0 top-0 bottom-0 w-24 md:w-48 curtain-right"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-sm bg-crimson/20 border border-crimson/30 mb-6">
                    <svg class="w-5 h-5 text-crimson-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span class="text-crimson-light font-medium uppercase tracking-wider text-sm">The Show Must Go Online</span>
                </div>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-ivory mb-6">
                    Stream your act to the world
                </h2>
                <p class="text-xl text-ivory/70 max-w-2xl mx-auto mb-8">
                    Sell tickets to virtual audiences worldwide. No venue needed. Your performance, broadcast from anywhere.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 items-center">
                <!-- Visual: Stage with streaming screen -->
                <div class="relative">
                    <div class="bg-gradient-to-br from-circus-dark to-[#1a0808] rounded-lg border-2 border-gold/30 p-6 shadow-2xl">
                        <!-- Screen frame -->
                        <div class="bg-black rounded border border-gold/20 p-4 mb-4">
                            <div class="aspect-video bg-gradient-to-br from-crimson/30 to-circus-dark rounded flex items-center justify-center relative overflow-hidden">
                                <!-- Spotlight effect on stage -->
                                <div class="absolute inset-0 bg-gradient-radial from-gold/10 via-transparent to-transparent"></div>

                                <!-- Live indicator -->
                                <div class="absolute top-2 left-2 flex items-center gap-2 px-2 py-1 bg-red-600 rounded text-xs text-white font-bold">
                                    <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                    LIVE
                                </div>

                                <!-- Aerialist silhouette performing -->
                                <svg class="w-24 h-24 text-gold/50" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M50 5 L50 95" stroke="currentColor" stroke-width="1.5" opacity="0.6"/>
                                    <circle cx="50" cy="30" r="5" fill="currentColor"/>
                                    <path d="M50 35 L50 55" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M50 40 Q35 45, 30 35" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M50 40 Q55 30, 52 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M50 55 Q60 70, 70 65" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M50 55 Q45 65, 40 75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>

                                <!-- Viewer count -->
                                <div class="absolute bottom-2 right-2 flex items-center gap-1 px-2 py-1 bg-black/60 rounded text-xs text-ivory">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                                    </svg>
                                    847 watching
                                </div>

                                <!-- Heart/reaction floating up -->
                                <div class="absolute bottom-8 left-4 text-rose-500 text-sm animate-float-up opacity-70">❤️</div>
                            </div>
                        </div>

                        <!-- Chat messages preview -->
                        <div class="bg-black/50 rounded border border-gold/10 p-2 mb-3 space-y-1.5 text-xs max-h-20 overflow-hidden">
                            <div class="flex items-start gap-2">
                                <span class="text-amber-400 font-medium shrink-0">CircusFan:</span>
                                <span class="text-ivory/80">Amazing drop! 🎪</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-rose-400 font-medium shrink-0">AerialLover:</span>
                                <span class="text-ivory/80">The silk work is incredible</span>
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="text-emerald-400 font-medium shrink-0">NewViewer:</span>
                                <span class="text-ivory/80">First time seeing aerial live! 🔥</span>
                            </div>
                        </div>

                        <!-- Ticket sales indicator -->
                        <div class="flex items-center justify-between px-3 py-2 bg-gold/10 rounded border border-gold/20">
                            <span class="text-ivory text-sm">Virtual Tickets Sold</span>
                            <span class="text-gold font-bold">234</span>
                        </div>
                    </div>

                    <!-- Donation ticker badge -->
                    <div class="absolute -top-3 -right-3 px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-emerald-500 rounded-full text-white text-xs font-bold shadow-lg animate-pulse">
                        💰 $25 tip from Sarah!
                    </div>
                </div>

                <!-- Benefits list -->
                <div class="space-y-6">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-ivory">Perform for global audiences</h3>
                            <p class="text-ivory/70">Reach fans across continents without leaving your studio</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-ivory">Sell virtual tickets</h3>
                            <p class="text-ivory/70">Monetize your streams with paid access - keep 100%</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-ivory">Schedule recurring shows</h3>
                            <p class="text-ivory/70">Build a regular online audience with scheduled streams</p>
                        </div>
                    </div>

                    <a href="{{ url('/online-events') }}" class="inline-flex items-center gap-2 mt-4 px-6 py-3 bg-crimson hover:bg-crimson/80 text-ivory font-semibold rounded-sm transition-colors">
                        Learn about online events
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- The Lineup - Playbill Style Features -->
    <section class="bg-circus-dark py-24 border-t border-gold/10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Playbill header -->
            <div class="text-center mb-16">
                <div class="inline-block playbill-header px-8 py-4 mb-6">
                    <span class="text-gold text-sm font-medium tracking-[0.3em] uppercase">The Lineup</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-ivory mb-4 font-serif">
                    Tonight's Features
                </h2>
                <p class="text-ivory/70 text-lg">Everything you need to manage your circus career</p>
            </div>

            <!-- Acts grid - Playbill style -->
            <div class="space-y-6">
                <!-- Act I & II Row -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Act I: Festival Circuit -->
                    <div class="playbill-card group">
                        <div class="sparkle absolute top-3 right-12 text-gold text-xs">✦</div>
                        <div class="sparkle absolute bottom-4 right-16 text-gold/60 text-[10px]">✧</div>
                        <div class="flex items-start gap-4">
                            <div class="act-number">I</div>
                            <div class="flex-1">
                                <div class="text-gold/80 text-xs uppercase tracking-wider mb-1">Festival Circuit</div>
                                <h3 class="text-xl font-bold text-ivory mb-2 font-serif">Track Your Tour</h3>
                                <p class="text-ivory/70 text-sm mb-4">Renaissance faires, Burning Man, Fringe festivals - show fans every stop on your summer circuit.</p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-2 py-1 rounded-sm bg-amber-500/10 text-amber-400 text-xs border border-amber-500/20">Summer Tours</span>
                                    <span class="px-2 py-1 rounded-sm bg-amber-500/10 text-amber-400 text-xs border border-amber-500/20">Ren Faires</span>
                                    <span class="px-2 py-1 rounded-sm bg-amber-500/10 text-amber-400 text-xs border border-amber-500/20">Burns</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Act II: Rigging & Tech Specs -->
                    <div class="playbill-card group">
                        <div class="sparkle absolute top-4 right-10 text-gold/70 text-[10px]">✦</div>
                        <div class="sparkle absolute bottom-3 right-14 text-gold text-xs">✧</div>
                        <div class="flex items-start gap-4">
                            <div class="act-number">II</div>
                            <div class="flex-1">
                                <div class="text-gold/80 text-xs uppercase tracking-wider mb-1">Technical Requirements</div>
                                <h3 class="text-xl font-bold text-ivory mb-2 font-serif">Rigging & Tech Specs</h3>
                                <p class="text-ivory/70 text-sm mb-4">Ceiling height, rigging points, weight capacity, floor space - share specs venues actually need.</p>
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="p-2 rounded-sm bg-slate-800/50 border border-slate-600/30">
                                        <div class="text-ivory font-mono text-sm">20ft</div>
                                        <div class="text-slate-400 text-[10px]">Height</div>
                                    </div>
                                    <div class="p-2 rounded-sm bg-slate-800/50 border border-slate-600/30">
                                        <div class="text-ivory font-mono text-sm">500lb</div>
                                        <div class="text-slate-400 text-[10px]">Load</div>
                                    </div>
                                    <div class="p-2 rounded-sm bg-slate-800/50 border border-slate-600/30">
                                        <div class="text-ivory font-mono text-sm">2 pts</div>
                                        <div class="text-slate-400 text-[10px]">Rigging</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Act III & IV Row -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Act III: Workshop Scheduling -->
                    <div class="playbill-card group">
                        <div class="sparkle absolute top-3 right-14 text-gold text-xs">✧</div>
                        <div class="sparkle absolute bottom-5 right-10 text-gold/60 text-[10px]">✦</div>
                        <div class="flex items-start gap-4">
                            <div class="act-number">III</div>
                            <div class="flex-1">
                                <div class="text-gold/80 text-xs uppercase tracking-wider mb-1">Teaching</div>
                                <h3 class="text-xl font-bold text-ivory mb-2 font-serif">Fill Your Workshops</h3>
                                <p class="text-ivory/70 text-sm mb-4">Aerial basics, fire safety, acro fundamentals - share your class schedule with students.</p>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 p-2 rounded-sm bg-teal-500/10 border border-teal-500/20">
                                        <div class="text-teal-400 text-xs font-mono w-8">SAT</div>
                                        <div class="flex-1 text-ivory text-sm">Intro to Silks</div>
                                        <span class="text-teal-400 text-xs">10am</span>
                                    </div>
                                    <div class="flex items-center gap-2 p-2 rounded-sm bg-white/5 border border-white/10">
                                        <div class="text-slate-400 text-xs font-mono w-8">SUN</div>
                                        <div class="flex-1 text-slate-300 text-sm">Fire Safety</div>
                                        <span class="text-slate-500 text-xs">2pm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Act IV: Troupe Coordination -->
                    <div class="playbill-card group">
                        <div class="sparkle absolute top-4 right-12 text-gold/70 text-xs">✦</div>
                        <div class="sparkle absolute bottom-4 right-8 text-gold text-[10px]">✧</div>
                        <div class="flex items-start gap-4">
                            <div class="act-number">IV</div>
                            <div class="flex-1">
                                <div class="text-gold/80 text-xs uppercase tracking-wider mb-1">Ensemble</div>
                                <h3 class="text-xl font-bold text-ivory mb-2 font-serif">Coordinate Your Troupe</h3>
                                <p class="text-ivory/70 text-sm mb-4">Aerialist, rigger, stage manager - everyone sees the schedule. No more group chat chaos.</p>
                                <div class="flex -space-x-2">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-crimson to-rose-600 flex items-center justify-center text-ivory text-xs font-bold border-2 border-circus-dark">AL</div>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-500 to-gray-600 flex items-center justify-center text-ivory text-xs font-bold border-2 border-circus-dark">MK</div>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-ivory text-xs font-bold border-2 border-circus-dark">JR</div>
                                    <div class="w-8 h-8 rounded-full bg-gold/20 flex items-center justify-center text-gold text-xs font-bold border-2 border-circus-dark">+4</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Act V & VI Row -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Act V: Zero-Fee Ticketing -->
                    <div class="playbill-card group">
                        <div class="sparkle absolute top-3 right-10 text-gold text-[10px]">✧</div>
                        <div class="sparkle absolute bottom-3 right-16 text-gold/70 text-xs">✦</div>
                        <div class="flex items-start gap-4">
                            <div class="act-number">V</div>
                            <div class="flex-1">
                                <div class="text-gold/80 text-xs uppercase tracking-wider mb-1">Ticketing</div>
                                <h3 class="text-xl font-bold text-ivory mb-2 font-serif">Keep 100% of Sales</h3>
                                <p class="text-ivory/70 text-sm mb-4">Your show, your revenue. Zero platform fees. QR tickets for door check-in.</p>
                                <div class="flex items-center gap-4">
                                    <div class="text-center">
                                        <div class="text-3xl font-bold text-emerald-400">100%</div>
                                        <div class="text-emerald-400/60 text-xs">You keep</div>
                                    </div>
                                    <div class="flex-1 h-px bg-gradient-to-r from-emerald-500/50 to-transparent"></div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-slate-500">$0</div>
                                        <div class="text-slate-500 text-xs">Platform fee</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Act VI: Booking Kit -->
                    <div class="playbill-card group">
                        <div class="sparkle absolute top-4 right-14 text-gold/60 text-xs">✦</div>
                        <div class="sparkle absolute bottom-5 right-12 text-gold text-[10px]">✧</div>
                        <div class="flex items-start gap-4">
                            <div class="act-number">VI</div>
                            <div class="flex-1">
                                <div class="text-gold/80 text-xs uppercase tracking-wider mb-1">Booking</div>
                                <h3 class="text-xl font-bold text-ivory mb-2 font-serif">Event Planner Kit</h3>
                                <p class="text-ivory/70 text-sm mb-4">One link with your availability, videos, specs, and rates. Perfect for corporate bookers and wedding planners.</p>
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-2 py-1 rounded-sm bg-indigo-500/10 text-indigo-400 text-xs border border-indigo-500/20">Demo Reel</span>
                                    <span class="px-2 py-1 rounded-sm bg-indigo-500/10 text-indigo-400 text-xs border border-indigo-500/20">Tech Rider</span>
                                    <span class="px-2 py-1 rounded-sm bg-indigo-500/10 text-indigo-400 text-xs border border-indigo-500/20">Rates</span>
                                    <span class="px-2 py-1 rounded-sm bg-indigo-500/10 text-indigo-400 text-xs border border-indigo-500/20">Availability</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Where Circus Artists Perform -->
    <section class="bg-circus-dark py-24 border-t border-gold/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-ivory mb-4">
                    Where circus artists perform
                </h2>
                <p class="text-xl text-ivory/70">
                    From street corners to the big top - one schedule for every stage
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Big Top Tents -->
                <div class="group relative bg-gradient-to-br from-crimson/10 to-circus-dark rounded-lg p-6 border border-crimson/20 hover:border-gold/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-crimson/20 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-crimson-light" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-ivory">Big Tops</h3>
                </div>

                <!-- Theaters -->
                <div class="group relative bg-gradient-to-br from-rose-900/10 to-circus-dark rounded-lg p-6 border border-rose-900/20 hover:border-gold/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-rose-900/20 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-ivory">Theaters</h3>
                </div>

                <!-- Street & Busking -->
                <div class="group relative bg-gradient-to-br from-amber-900/10 to-circus-dark rounded-lg p-6 border border-amber-900/20 hover:border-gold/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-amber-900/20 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-ivory">Street</h3>
                </div>

                <!-- Festivals -->
                <div class="group relative bg-gradient-to-br from-orange-900/10 to-circus-dark rounded-lg p-6 border border-orange-900/20 hover:border-gold/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-orange-900/20 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-ivory">Festivals</h3>
                </div>

                <!-- Corporate -->
                <div class="group relative bg-gradient-to-br from-gold/10 to-circus-dark rounded-lg p-6 border border-gold/20 hover:border-gold/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-gold/20 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-ivory">Corporate</h3>
                </div>

                <!-- Cruise Ships -->
                <div class="group relative bg-gradient-to-br from-indigo-900/10 to-circus-dark rounded-lg p-6 border border-indigo-900/20 hover:border-gold/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-indigo-900/20 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-ivory">Cruise Ships</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-ivory py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-gray-900 mb-4">
                    Perfect for all types of circus performers
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-500">
                    Whether you're a solo aerialist or a touring troupe, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Aerialists -->
                <x-sub-audience-card
                    name="Aerialists"
                    description="Share your aerial silk, trapeze, and hoop performances. Let fans know where to catch your next show."
                    icon-color="red"
                    blog-slug="for-aerialists"
                >
                    <x-slot:icon>
                        <svg class="w-7 h-7 text-red-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2V22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.5"/>
                            <circle cx="12" cy="7" r="2" fill="currentColor"/>
                            <path d="M12 9V14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 11L8 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 11L14 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 14L16 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 14L10 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Circus Troupes -->
                <x-sub-audience-card
                    name="Circus Troupes"
                    description="Coordinate your ensemble's schedule and let audiences follow your collective performances."
                    icon-color="rose"
                    blog-slug="for-circus-troupes"
                >
                    <x-slot:icon>
                        <svg class="w-7 h-7 text-rose-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="7" cy="18" r="2" fill="currentColor"/>
                            <circle cx="17" cy="18" r="2" fill="currentColor"/>
                            <circle cx="12" cy="12" r="2" fill="currentColor"/>
                            <path d="M12 14V17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 16L7 18" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                            <path d="M14 16L17 18" stroke="currentColor" stroke-width="1" stroke-linecap="round" opacity="0.5"/>
                            <circle cx="12" cy="6" r="1.5" fill="currentColor"/>
                            <path d="M12 7.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 9L14 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Fire Performers -->
                <x-sub-audience-card
                    name="Fire Performers"
                    description="Promote your fire dancing, breathing, and spinning shows at festivals and events."
                    icon-color="orange"
                    blog-slug="for-fire-performers"
                >
                    <x-slot:icon>
                        <svg class="w-7 h-7 text-orange-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="10" r="2" fill="currentColor"/>
                            <path d="M12 12V18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 16L8 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M14 16L16 20" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 13L6 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 13L18 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M5 10C4.5 8.5 5.5 7 6 8C6.5 9 5.5 10 5 10Z" fill="currentColor"/>
                            <path d="M19 10C19.5 8.5 18.5 7 18 8C17.5 9 18.5 10 19 10Z" fill="currentColor"/>
                            <circle cx="5.5" cy="10" r="1.5" fill="currentColor" opacity="0.6"/>
                            <circle cx="18.5" cy="10" r="1.5" fill="currentColor" opacity="0.6"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Contortionists -->
                <x-sub-audience-card
                    name="Contortionists"
                    description="Showcase your flexibility performances and build a dedicated following."
                    icon-color="fuchsia"
                    blog-slug="for-contortionists"
                >
                    <x-slot:icon>
                        <svg class="w-7 h-7 text-fuchsia-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="18" cy="14" r="2" fill="currentColor"/>
                            <path d="M16 14C14 14 12 16 10 16C8 16 6 14 6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M6 12C6 10 7 8 9 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 8L10 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 16L10 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="10" cy="5.5" r="0.8" fill="currentColor"/>
                            <circle cx="10" cy="19.5" r="0.8" fill="currentColor"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Jugglers & Prop Artists -->
                <x-sub-audience-card
                    name="Jugglers & Prop Artists"
                    description="List your juggling, poi, and object manipulation shows and workshops."
                    icon-color="amber"
                    blog-slug="for-jugglers-prop-artists"
                >
                    <x-slot:icon>
                        <svg class="w-7 h-7 text-amber-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="14" r="2" fill="currentColor"/>
                            <path d="M12 16V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10 19L8 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M14 19L16 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 15L8 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 15L16 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <circle cx="6" cy="6" r="1.5" fill="currentColor"/>
                            <circle cx="12" cy="3" r="1.5" fill="currentColor"/>
                            <circle cx="18" cy="6" r="1.5" fill="currentColor"/>
                            <path d="M7 7C9 4 11 3 12 3" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" opacity="0.4" stroke-dasharray="1 1"/>
                            <path d="M17 7C15 4 13 3 12 3" stroke="currentColor" stroke-width="0.75" stroke-linecap="round" opacity="0.4" stroke-dasharray="1 1"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Stilt Walkers -->
                <x-sub-audience-card
                    name="Stilt Walkers"
                    description="Share your larger-than-life performances at parades, festivals, and corporate events."
                    icon-color="amber"
                    blog-slug="for-stilt-walkers"
                >
                    <x-slot:icon>
                        <svg class="w-7 h-7 text-amber-700" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="4" r="2" fill="currentColor"/>
                            <path d="M12 6V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 8L8 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 8L16 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M12 12L10 22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M12 12L14 22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M9 22H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M13 22H15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>
            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-gray-50 dark:bg-ivory py-24 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-gray-900 mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-500">
                    Get your performance schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1: Add your acts -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-crimson to-rose-500 rounded-2xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity"></div>
                    <div class="relative bg-white border border-gray-200 rounded-2xl p-8 h-full shadow-sm hover:shadow-lg transition-shadow">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#DC143C] to-rose-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-crimson/25">1</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-crimson/30 to-transparent"></div>
                        </div>
                        <!-- Calendar with events icon -->
                        <div class="w-16 h-16 mx-auto mb-4 text-rose-600">
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="8" y="12" width="48" height="44" rx="4" stroke="currentColor" stroke-width="2.5"/>
                                <path d="M8 24H56" stroke="currentColor" stroke-width="2.5"/>
                                <path d="M20 8V16M44 8V16" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                <!-- Event markers -->
                                <circle cx="24" cy="36" r="4" fill="currentColor" opacity="0.6"/>
                                <circle cx="40" cy="36" r="4" fill="currentColor"/>
                                <circle cx="32" cy="46" r="4" fill="currentColor" opacity="0.4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Add your acts</h3>
                        <p class="text-gray-600 text-sm text-center">
                            Shows, workshops, festival appearances. Import from Google Calendar or add manually.
                        </p>
                    </div>
                </div>

                <!-- Step 2: Share one link -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-gold to-amber-500 rounded-2xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity"></div>
                    <div class="relative bg-white border border-gray-200 rounded-2xl p-8 h-full shadow-sm hover:shadow-lg transition-shadow">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-gold flex items-center justify-center text-gray-900 font-bold text-xl shadow-lg shadow-gold/25">2</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-gold/30 to-transparent"></div>
                        </div>
                        <!-- Link/share icon with spotlight -->
                        <div class="w-16 h-16 mx-auto mb-4 text-amber-600">
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Chain link -->
                                <path d="M26 32H38M22 32C22 28 25 24 30 24H34C39 24 42 28 42 32C42 36 39 40 34 40H30C25 40 22 36 22 32Z" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                <!-- Share arrows radiating out -->
                                <path d="M32 16V8M32 8L28 12M32 8L36 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.7"/>
                                <path d="M48 32H56M56 32L52 28M56 32L52 36" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.7"/>
                                <path d="M16 32H8M8 32L12 28M8 32L12 36" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.7"/>
                                <path d="M32 48V56M32 56L28 52M32 56L36 52" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.7"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Share one link</h3>
                        <p class="text-gray-600 text-sm text-center">
                            Add to your website, social bios, and booking portfolio. Planners see everything.
                        </p>
                    </div>
                </div>

                <!-- Step 3: Build your following -->
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-rose-500 to-crimson rounded-2xl opacity-0 group-hover:opacity-20 blur-xl transition-opacity"></div>
                    <div class="relative bg-white border border-gray-200 rounded-2xl p-8 h-full shadow-sm hover:shadow-lg transition-shadow">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-crimson flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-rose-500/25">3</div>
                            <div class="h-px flex-1 bg-gradient-to-r from-rose-400/30 to-transparent"></div>
                        </div>
                        <!-- Audience/followers with hearts icon -->
                        <div class="w-16 h-16 mx-auto mb-4 text-rose-500">
                            <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Performer at top -->
                                <circle cx="32" cy="14" r="6" fill="currentColor"/>
                                <path d="M32 20V28" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                                <!-- Audience members -->
                                <circle cx="16" cy="44" r="5" fill="currentColor" opacity="0.5"/>
                                <circle cx="32" cy="48" r="5" fill="currentColor" opacity="0.6"/>
                                <circle cx="48" cy="44" r="5" fill="currentColor" opacity="0.5"/>
                                <!-- Connection lines -->
                                <path d="M32 28L16 40M32 28L32 44M32 28L48 40" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                                <!-- Heart notification -->
                                <path d="M50 18C50 14 54 14 54 18C54 14 58 14 58 18C58 22 54 26 54 26C54 26 50 22 50 18Z" fill="currentColor" opacity="0.8"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2 text-center">Build your following</h3>
                        <p class="text-gray-600 text-sm text-center">
                            Fans follow your schedule and get notified about performances in their area.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-[#8b0000] to-crimson py-24 overflow-hidden">
        <!-- Tent stripe overlay -->
        <div class="absolute inset-0 opacity-10" style="background: repeating-linear-gradient(-45deg, #000 0px, #000 20px, transparent 20px, transparent 40px);"></div>

        <!-- Spotlight effect -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] spotlight-beam-center opacity-30"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-ivory mb-6">
                The show must go on.<br>Make sure they know where.
            </h2>
            <p class="text-xl text-ivory/80 mb-10 max-w-2xl mx-auto">
                Your art deserves an audience. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-gray-900 hover:bg-amber-400 rounded-sm hover:scale-105 transition-all shadow-xl" style="background-color: #FFD700;">
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
        "name": "Event Schedule for Circus & Acrobatics",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Circus Performer Scheduling Software",
        "operatingSystem": "Web",
        "description": "Share your circus performances, sell tickets directly, and reach your audience with newsletters. Built for aerialists, acrobats, and circus performers.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Festival circuit tracking for summer tours",
            "Technical rigging specs for venue bookers",
            "Workshop scheduling for teaching",
            "Troupe and ensemble collaboration",
            "Zero-fee ticket sales with door check-in",
            "Event planner booking kit",
            "Direct newsletter announcements to fans",
            "Online event streaming and virtual tickets"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        /* Circus Color Palette */
        .bg-circus-dark { background-color: #0f0808; }
        .bg-crimson { background-color: #DC143C; }
        .bg-gold { background-color: #FFD700; }
        .bg-ivory { background-color: #FFFFF0; }
        .text-crimson { color: #DC143C; }
        .text-crimson-light { color: #ff4d6d; }
        .text-gold { color: #FFD700; }
        .text-ivory { color: #FFFFF0; }
        .border-crimson { border-color: #DC143C; }
        .border-gold { border-color: #FFD700; }
        .from-crimson {
            --tw-gradient-from: #DC143C var(--tw-gradient-from-position);
            --tw-gradient-to: rgb(220 20 60 / 0) var(--tw-gradient-to-position);
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
        }
        .to-crimson {
            --tw-gradient-to: #DC143C var(--tw-gradient-to-position);
        }
        .from-gold {
            --tw-gradient-from: #FFD700 var(--tw-gradient-from-position);
            --tw-gradient-to: rgb(255 215 0 / 0) var(--tw-gradient-to-position);
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
        }
        .to-gold {
            --tw-gradient-to: #FFD700 var(--tw-gradient-to-position);
        }

        .text-gradient-circus {
            background: linear-gradient(135deg, #FFD700, #FFA500, #DC143C);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Spotlight beams */
        .spotlight-beam {
            background: conic-gradient(from 180deg at 50% 0%, transparent 40%, rgba(255,215,0,0.08) 50%, transparent 60%);
            animation: spotlight-sway 8s ease-in-out infinite;
        }

        .spotlight-beam-center {
            background: conic-gradient(from 180deg at 50% 0%, transparent 35%, rgba(255,255,240,0.1) 50%, transparent 65%);
        }

        @keyframes spotlight-sway {
            0%, 100% { transform: rotate(-3deg); }
            50% { transform: rotate(3deg); }
        }

        /* Curtain effects */
        .curtain-left {
            background: linear-gradient(90deg, #8b0000 0%, #dc143c 60%, transparent 100%);
            opacity: 0.4;
        }

        .curtain-right {
            background: linear-gradient(-90deg, #8b0000 0%, #dc143c 60%, transparent 100%);
            opacity: 0.4;
        }

        /* Marquee badge */
        .marquee-badge {
            background: linear-gradient(135deg, rgba(139,0,0,0.8), rgba(220,20,60,0.6));
            border: 2px solid rgba(255,215,0,0.5);
            box-shadow: 0 0 20px rgba(255,215,0,0.2);
        }

        .marquee-button {
            box-shadow: 0 4px 20px rgba(255,215,0,0.3);
        }

        /* Playbill styling */
        .playbill-header {
            border: 3px double rgba(255,215,0,0.6);
            background: linear-gradient(135deg, rgba(139,0,0,0.3), rgba(15,8,8,0.8));
        }

        .playbill-card {
            background: linear-gradient(135deg, rgba(30,15,15,0.8), rgba(15,8,8,0.9));
            border: 1px solid rgba(255,215,0,0.15);
            border-radius: 4px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .playbill-card::before,
        .playbill-card::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,215,0,0);
            transition: all 0.3s ease;
        }

        .playbill-card::before {
            top: 8px;
            left: 8px;
            border-top-color: rgba(255,215,0,0.2);
            border-left-color: rgba(255,215,0,0.2);
        }

        .playbill-card::after {
            bottom: 8px;
            right: 8px;
            border-bottom-color: rgba(255,215,0,0.2);
            border-right-color: rgba(255,215,0,0.2);
        }

        .playbill-card:hover {
            border-color: rgba(255,215,0,0.4);
            box-shadow: 0 0 30px rgba(255,215,0,0.1);
        }

        .playbill-card:hover::before {
            border-top-color: rgba(255,215,0,0.5);
            border-left-color: rgba(255,215,0,0.5);
        }

        .playbill-card:hover::after {
            border-bottom-color: rgba(255,215,0,0.5);
            border-right-color: rgba(255,215,0,0.5);
        }

        /* Sparkle on hover */
        .playbill-card .sparkle {
            position: absolute;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .playbill-card:hover .sparkle {
            opacity: 1;
        }

        .act-number {
            font-family: 'Times New Roman', serif;
            font-size: 2rem;
            font-weight: bold;
            color: rgba(255,215,0,0.8);
            line-height: 1;
            min-width: 2.5rem;
            text-align: center;
        }

        /* Silk sway animation */
        @keyframes silk-sway {
            0%, 100% {
                transform: translateX(0) scaleY(1);
            }
            25% {
                transform: translateX(4px) scaleY(1.02);
            }
            50% {
                transform: translateX(-2px) scaleY(0.98);
            }
            75% {
                transform: translateX(3px) scaleY(1.01);
            }
        }

        .animate-silk-sway {
            animation: silk-sway 6s ease-in-out infinite;
        }

        /* Star twinkle */
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        .animate-twinkle {
            animation: twinkle 3s ease-in-out infinite;
        }

        /* Radial gradient for spotlight effect */
        .bg-gradient-radial {
            background: radial-gradient(ellipse at center, var(--tw-gradient-from) 0%, var(--tw-gradient-via) 40%, var(--tw-gradient-to) 70%);
        }

        /* Float up animation for reactions */
        @keyframes float-up {
            0% {
                opacity: 0.7;
                transform: translateY(0) scale(1);
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                transform: translateY(-30px) scale(1.2);
            }
        }

        .animate-float-up {
            animation: float-up 2s ease-out infinite;
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .animate-silk-sway,
            .animate-twinkle,
            .animate-float-up,
            .spotlight-beam {
                animation: none;
            }
        }
    </style>
</x-marketing-layout>
