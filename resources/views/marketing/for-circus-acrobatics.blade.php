<x-marketing-layout>
    <x-slot name="title">Event Schedule for Circus & Acrobatics | Share Your Performances</x-slot>
    <x-slot name="description">Share your circus performances, sell tickets directly, and reach your audience with newsletters. No social media algorithms. Zero platform fees. Built for aerialists, acrobats, and circus performers.</x-slot>
    <x-slot name="keywords">circus schedule, aerialist calendar, acrobat booking, circus performer schedule, aerial show schedule, circus troupe management, sell circus tickets, performer newsletter</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-pink-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-rose-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Aerial Silk Lines -->
        <div class="absolute top-0 left-8 md:left-16 lg:left-24 w-1 h-64 bg-gradient-to-b from-pink-500/60 via-pink-400/30 to-transparent rounded-full animate-silk-sway"></div>
        <div class="absolute top-0 left-12 md:left-24 lg:left-32 w-0.5 h-48 bg-gradient-to-b from-rose-400/50 via-rose-300/20 to-transparent rounded-full animate-silk-sway" style="animation-delay: 0.5s;"></div>
        <div class="absolute top-0 right-8 md:right-16 lg:right-24 w-1 h-64 bg-gradient-to-b from-fuchsia-500/60 via-fuchsia-400/30 to-transparent rounded-full animate-silk-sway" style="animation-delay: 0.3s;"></div>
        <div class="absolute top-0 right-12 md:right-24 lg:right-32 w-0.5 h-48 bg-gradient-to-b from-pink-400/50 via-pink-300/20 to-transparent rounded-full animate-silk-sway" style="animation-delay: 0.8s;"></div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                </svg>
                <span class="text-sm text-gray-300">For Aerialists, Acrobats & Circus Artists</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                <span class="text-gradient-pink">Defy gravity.</span><br>
                Fill every seat.
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                From the training studio to the big top. One link for all your shows. Venues book you, fans follow you, no algorithm decides who sees it.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-pink-600 to-rose-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-pink-500/25">
                    Create your performance schedule
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <!-- Performance tags -->
            <div class="mt-12 flex flex-wrap justify-center gap-2">
                <span class="px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 text-xs font-medium border border-pink-500/30">Aerial</span>
                <span class="px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-medium border border-rose-500/30">Fire</span>
                <span class="px-3 py-1 rounded-full bg-fuchsia-500/20 text-fuchsia-300 text-xs font-medium border border-fuchsia-500/30">Acrobatics</span>
                <span class="px-3 py-1 rounded-full bg-purple-500/20 text-purple-300 text-xs font-medium border border-purple-500/30">Juggling</span>
                <span class="px-3 py-1 rounded-full bg-violet-500/20 text-violet-300 text-xs font-medium border border-violet-500/30">Stilt Walking</span>
                <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-medium border border-indigo-500/30">Contortion</span>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Festival Circuit Tracker (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-900/50 to-orange-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                Festival Circuit
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Track your festival season</h3>
                            <p class="text-gray-400 text-lg mb-6">Renaissance faires, Burning Man, street festivals - circus performers live on the festival circuit. Show fans every stop on your summer tour.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Summer festivals</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Holiday faires</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Street performance</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-2xl border border-amber-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-amber-300 font-semibold mb-3 uppercase tracking-wide">Summer Tour 2025</div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                                            <span class="text-white text-sm flex-1">Burning Man</span>
                                            <span class="text-gray-400 text-xs">Aug 25</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-300 text-sm flex-1">Ren Faire</span>
                                            <span class="text-gray-500 text-xs">Sep 14</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
                                            <span class="text-gray-300 text-sm flex-1">Fringe Fest</span>
                                            <span class="text-gray-500 text-xs">Oct 3</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rigging & Tech Specs -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900/50 to-zinc-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-500/20 text-slate-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Tech Specs
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Share your rigging requirements</h3>
                    <p class="text-gray-400 mb-6">Ceiling height, rigging points, floor space - share technical specs with venues in your booking kit.</p>

                    <div class="flex justify-center">
                        <div class="bg-slate-800/50 rounded-xl border border-slate-600/30 p-4 w-full">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Ceiling</span>
                                    <span class="text-white font-mono">20ft min</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Rigging</span>
                                    <span class="text-white font-mono">2 points</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">Load</span>
                                    <span class="text-white font-mono">500 lbs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workshop Schedule -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-900/50 to-cyan-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Teaching
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Fill your workshops</h3>
                    <p class="text-gray-400 mb-6">Teaching aerial basics? Hosting a fire safety workshop? Share your class schedule with students.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-teal-500/20 border border-teal-400/20">
                            <div class="text-teal-300 text-xs font-semibold">SAT</div>
                            <div class="flex-1 text-white text-sm">Intro to Silks</div>
                            <span class="text-teal-300 text-xs">10am</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                            <div class="text-gray-400 text-xs font-semibold">SUN</div>
                            <div class="flex-1 text-gray-300 text-sm">Fire Safety</div>
                            <span class="text-gray-500 text-xs">2pm</span>
                        </div>
                    </div>
                </div>

                <!-- Troupe Coordination (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-900/50 to-pink-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Ensemble
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-4">Your troupe, one schedule</h3>
                            <p class="text-gray-400 text-lg">Aerialist, rigger, stage manager, booking agent - everyone sees the schedule. No more group chat chaos.</p>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center text-white text-sm font-semibold">AL</div>
                                <div class="flex-1">
                                    <div class="text-white font-semibold text-sm">Alex</div>
                                    <div class="text-gray-400 text-xs">Lead performer</div>
                                </div>
                                <span class="px-2 py-1 rounded bg-pink-500/20 text-pink-300 text-xs">Aerialist</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-500 to-gray-600 flex items-center justify-center text-white text-sm font-semibold">MK</div>
                                <div class="flex-1">
                                    <div class="text-gray-300 font-semibold text-sm">Mike</div>
                                    <div class="text-gray-500 text-xs">Technical</div>
                                </div>
                                <span class="px-2 py-1 rounded bg-slate-500/20 text-slate-300 text-xs">Rigger</span>
                            </div>
                            <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center text-white text-sm font-semibold">JR</div>
                                <div class="flex-1">
                                    <div class="text-gray-300 font-semibold text-sm">Jordan</div>
                                    <div class="text-gray-500 text-xs">Bookings</div>
                                </div>
                                <span class="px-2 py-1 rounded bg-amber-500/20 text-amber-300 text-xs">Manager</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sell Tickets Direct -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-900/50 to-green-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Ticketing
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Keep 100% of ticket sales</h3>
                    <p class="text-gray-400 mb-6">Your show, your revenue. Zero platform fees. QR tickets for door check-in.</p>

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

                <!-- Event Planner Kit -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/50 to-blue-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Booking
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">One link for event planners</h3>
                    <p class="text-gray-400 mb-6">Corporate bookers and wedding planners find your availability, videos, and specs all in one place.</p>

                    <div class="bg-indigo-500/20 rounded-xl border border-indigo-400/30 p-3">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-white text-xs font-semibold">Demo Reel</div>
                            </div>
                        </div>
                        <div class="flex gap-2 text-[10px]">
                            <span class="px-2 py-1 rounded bg-white/10 text-indigo-300">Availability</span>
                            <span class="px-2 py-1 rounded bg-white/10 text-indigo-300">Tech Rider</span>
                            <span class="px-2 py-1 rounded bg-white/10 text-indigo-300">Rates</span>
                        </div>
                    </div>
                </div>

                <!-- Show Announcements -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-pink-900/50 to-fuchsia-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-500/20 text-pink-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Newsletter
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Email fans directly</h3>
                    <p class="text-gray-400 mb-6">New show? Send beautiful promo graphics to everyone who wants to see you perform. No algorithm throttling.</p>

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

            </div>
        </div>
    </section>

    <!-- Where Circus Artists Perform -->
    <section class="bg-[#0a0a0f] py-24 border-t border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Where circus artists perform
                </h2>
                <p class="text-xl text-gray-500">
                    From street corners to the big top - one schedule for every stage
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Big Top Tents -->
                <div class="group relative bg-gradient-to-br from-[#1a0f17] to-[#0a0a0f] rounded-2xl p-6 border border-pink-900/20 hover:border-pink-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-pink-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Big Tops</h3>
                </div>

                <!-- Theaters -->
                <div class="group relative bg-gradient-to-br from-[#170f1a] to-[#0a0a0f] rounded-2xl p-6 border border-rose-900/20 hover:border-rose-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Theaters</h3>
                </div>

                <!-- Street & Busking -->
                <div class="group relative bg-gradient-to-br from-[#15101a] to-[#0a0a0f] rounded-2xl p-6 border border-fuchsia-900/20 hover:border-fuchsia-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-fuchsia-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-fuchsia-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Street</h3>
                </div>

                <!-- Festivals -->
                <div class="group relative bg-gradient-to-br from-[#1a1510] to-[#0a0a0f] rounded-2xl p-6 border border-amber-900/20 hover:border-amber-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Festivals</h3>
                </div>

                <!-- Corporate -->
                <div class="group relative bg-gradient-to-br from-[#12101a] to-[#0a0a0f] rounded-2xl p-6 border border-violet-900/20 hover:border-violet-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Corporate</h3>
                </div>

                <!-- Cruise Ships -->
                <div class="group relative bg-gradient-to-br from-[#0f1520] to-[#0a0a0f] rounded-2xl p-6 border border-indigo-900/20 hover:border-indigo-600/40 transition-all text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-900/30 mb-4 mx-auto group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-white">Cruise Ships</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Perfect for all types of circus performers
                </h2>
                <p class="text-xl text-gray-500">
                    Whether you're a solo aerialist or a touring troupe, Event Schedule works for you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Aerialists -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-pink-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-pink-100 mb-4">
                        <svg class="w-6 h-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aerialists</h3>
                    <p class="text-gray-600 text-sm">Share your aerial silk, trapeze, and hoop performances. Let fans know where to catch your next show.</p>
                </div>

                <!-- Circus Troupes -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-rose-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 mb-4">
                        <svg class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Circus Troupes</h3>
                    <p class="text-gray-600 text-sm">Coordinate your ensemble's schedule and let audiences follow your collective performances.</p>
                </div>

                <!-- Fire Performers -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-orange-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Fire Performers</h3>
                    <p class="text-gray-600 text-sm">Promote your fire dancing, breathing, and spinning shows at festivals and events.</p>
                </div>

                <!-- Contortionists -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-fuchsia-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-fuchsia-100 mb-4">
                        <svg class="w-6 h-6 text-fuchsia-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Contortionists</h3>
                    <p class="text-gray-600 text-sm">Showcase your flexibility performances and build a dedicated following.</p>
                </div>

                <!-- Jugglers & Prop Artists -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-purple-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-purple-100 mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Jugglers & Prop Artists</h3>
                    <p class="text-gray-600 text-sm">List your juggling, poi, and object manipulation shows and workshops.</p>
                </div>

                <!-- Stilt Walkers -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm hover:shadow-lg hover:border-violet-200 transition-all">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-100 mb-4">
                        <svg class="w-6 h-6 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Stilt Walkers</h3>
                    <p class="text-gray-600 text-sm">Share your larger-than-life performances at parades, festivals, and corporate events.</p>
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
                    Get your performance schedule online in three steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Add your acts</h3>
                    <p class="text-gray-600 text-sm">
                        Shows, workshops, festival appearances. Import from Google Calendar or add manually.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Share one link</h3>
                    <p class="text-gray-600 text-sm">
                        Add to your website, social bios, and booking portfolio. Planners see everything.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-pink-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Build your following</h3>
                    <p class="text-gray-600 text-sm">
                        Fans follow your schedule and get notified about performances in their area.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-pink-600 to-rose-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                The show must go on.<br>Make sure they know where.
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Your art deserves an audience. Free forever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-pink-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
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
            "Direct newsletter announcements to fans"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <style>
        .text-gradient-pink {
            background: linear-gradient(135deg, #ec4899, #f43f5e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

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
    </style>
</x-marketing-layout>
