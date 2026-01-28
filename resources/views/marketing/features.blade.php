<x-marketing-layout>
    <x-slot name="title">Event Management Features | Ticketing, Calendar Sync & More - Event Schedule</x-slot>
    <x-slot name="description">Discover all the features that make Event Schedule the best way to manage events, sell tickets, and engage your audience.</x-slot>
    <x-slot name="keywords">event management features, online ticketing, QR code check-in, Google Calendar sync, AI event parsing, custom domain events, recurring events, event API</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <style>
        /* Parallax utility classes */
        .parallax-blob {
            will-change: transform;
            transition: transform 0.1s linear;
        }

        /* Respect reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            .parallax-blob {
                transform: none !important;
                will-change: auto;
            }
        }

        /* Banner hover effects */
        .feature-banner {
            transition: transform 0.3s ease-out;
        }
        .feature-banner:hover {
            transform: scale(1.01);
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.3"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.3" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-[150px] parallax-blob" data-parallax-speed="0.2"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-sm text-gray-600 dark:text-gray-300">Everything you need</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Built for people who<br>
                <span class="text-gradient">run events</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                One simple tool for calendars, tickets, and check-ins.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-violet-500/25">
                    Start for free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#features" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-gray-900 dark:text-white glass border border-gray-200 dark:border-white/10 rounded-2xl hover:bg-white/10 transition-all">
                    Explore features
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Feature Banners -->
    <div id="features" class="bg-white dark:bg-[#0a0a0f]">

        <!-- Banner 1: Ticketing (Text Left, Visual Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-fuchsia-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.4"></div>
                <div class="absolute bottom-10 right-1/3 w-[300px] h-[300px] bg-pink-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.3" style="animation-delay: 1s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/ticketing') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-500/20 dark:text-fuchsia-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                Ticketing
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-fuchsia-300 transition-colors">Sell tickets online</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Multiple ticket types, limits, and reservations. Accept payments with Stripe. Zero platform fees.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Zero fees</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">QR check-ins</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Stripe payments</span>
                            </div>
                            <span class="inline-flex items-center text-fuchsia-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-fuchsia-900/50 dark:to-pink-900/50 rounded-2xl border border-gray-200 dark:border-fuchsia-500/30 p-6 w-72 shadow-2xl shadow-fuchsia-500/10">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                            <div>
                                                <div class="text-gray-900 dark:text-white font-medium">Early Bird</div>
                                                <div class="text-emerald-400 text-xs">50 remaining</div>
                                            </div>
                                            <div class="text-xl font-bold text-gray-900 dark:text-white">$15</div>
                                        </div>
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                            <div>
                                                <div class="text-gray-900 dark:text-white font-medium">General</div>
                                                <div class="text-gray-500 dark:text-gray-400 text-xs">142 sold</div>
                                            </div>
                                            <div class="text-xl font-bold text-gray-900 dark:text-white">$25</div>
                                        </div>
                                        <div class="flex items-center justify-between p-3 rounded-xl bg-fuchsia-100 dark:bg-fuchsia-500/20 border border-fuchsia-200 dark:border-fuchsia-400/30">
                                            <div>
                                                <div class="text-gray-900 dark:text-white font-medium">VIP</div>
                                                <div class="text-fuchsia-600 dark:text-fuchsia-300 text-xs">38 sold</div>
                                            </div>
                                            <div class="text-xl font-bold text-gray-900 dark:text-white">$75</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 2: AI-Powered (Visual Left, Text Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-20 right-1/4 w-[400px] h-[400px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.35"></div>
                <div class="absolute bottom-20 left-1/3 w-[300px] h-[300px] bg-indigo-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.25" style="animation-delay: 1.2s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/ai') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row-reverse gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                AI-Powered
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-violet-300 transition-colors">Smart event import</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Paste text or drop an image. AI extracts title, date, time, venue, and description automatically.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Parse any format</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Instant translation</span>
                            </div>
                            <span class="inline-flex items-center text-violet-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 0.5s;">
                                <div class="flex flex-col items-center gap-2">
                                    <!-- Input -->
                                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 w-56">
                                        <div class="text-xs text-gray-500 dark:text-gray-500 mb-2">Paste or drop</div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300 font-mono leading-relaxed">
                                            Jazz Night at Blue Note<br>
                                            Friday, March 15 at 8pm<br>
                                            Tickets: $25
                                        </div>
                                    </div>
                                    <!-- Arrow -->
                                    <svg class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                    <!-- Output -->
                                    <div class="bg-gradient-to-br from-violet-100 to-indigo-100 dark:from-violet-500/20 dark:to-indigo-500/20 rounded-xl border border-violet-200 dark:border-violet-400/30 p-4 w-56">
                                        <div class="text-xs text-violet-600 dark:text-violet-300 mb-2">Extracted</div>
                                        <div class="space-y-1.5 text-sm">
                                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Name:</span><span class="text-gray-900 dark:text-white">Jazz Night</span></div>
                                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Date:</span><span class="text-gray-900 dark:text-white">Mar 15, 8 PM</span></div>
                                            <div class="flex justify-between"><span class="text-gray-500 dark:text-gray-400">Venue:</span><span class="text-gray-900 dark:text-white">Blue Note</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 3: Calendar Sync (Text Left, Visual Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-10 left-1/3 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.4"></div>
                <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.3" style="animation-delay: 0.8s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/calendar-sync') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Calendar Sync
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-blue-300 transition-colors">Two-way sync</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Sync with Google Calendar automatically. Changes flow in both directions via real-time webhooks.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Two-way sync</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Real-time updates</span>
                            </div>
                            <span class="inline-flex items-center text-blue-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 1s;">
                                <div class="flex items-center gap-4">
                                    <!-- Event Schedule box -->
                                    <div class="bg-blue-100 dark:bg-gradient-to-br dark:from-blue-500/20 dark:to-indigo-500/20 rounded-xl border border-blue-200 dark:border-blue-400/30 p-4 w-28">
                                        <div class="text-xs text-blue-600 dark:text-blue-300 mb-2 text-center">Event Schedule</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 bg-white/20 rounded"></div>
                                            <div class="h-2 bg-white/20 rounded w-3/4"></div>
                                            <div class="h-2 bg-white/20 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                    <!-- Sync arrows -->
                                    <div class="flex flex-col items-center gap-1">
                                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                        <svg class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                    </div>
                                    <!-- Google Calendar box -->
                                    <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-28">
                                        <div class="text-xs text-gray-600 dark:text-gray-300 mb-2 text-center">Google Calendar</div>
                                        <div class="space-y-1.5">
                                            <div class="h-2 bg-blue-400/40 rounded"></div>
                                            <div class="h-2 bg-green-400/40 rounded w-3/4"></div>
                                            <div class="h-2 bg-yellow-400/40 rounded w-1/2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 4: Analytics (Visual Left, Text Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-20 right-1/3 w-[400px] h-[400px] bg-emerald-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.35"></div>
                <div class="absolute bottom-20 left-1/4 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.25" style="animation-delay: 1.5s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/analytics') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row-reverse gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Analytics
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-emerald-300 transition-colors">Know your audience</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Track page views, device breakdown, and traffic sources. Privacy-first with no external services.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Privacy-first</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">No external services</span>
                            </div>
                            <span class="inline-flex items-center text-emerald-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 0.3s;">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-emerald-900/50 dark:to-teal-900/50 rounded-xl border border-gray-200 dark:border-emerald-500/30 p-5 w-64">
                                    <div class="text-xs text-gray-500 mb-3">Views this week</div>
                                    <div class="flex items-end justify-between h-24 gap-2">
                                        <div class="w-6 bg-emerald-500/40 rounded-t" style="height: 40%"></div>
                                        <div class="w-6 bg-emerald-500/50 rounded-t" style="height: 55%"></div>
                                        <div class="w-6 bg-emerald-500/60 rounded-t" style="height: 45%"></div>
                                        <div class="w-6 bg-emerald-500/70 rounded-t" style="height: 70%"></div>
                                        <div class="w-6 bg-emerald-500/80 rounded-t" style="height: 60%"></div>
                                        <div class="w-6 bg-emerald-500/90 rounded-t" style="height: 85%"></div>
                                        <div class="w-6 bg-emerald-500 rounded-t" style="height: 100%"></div>
                                    </div>
                                    <div class="flex justify-between mt-2 text-xs text-gray-500">
                                        <span>M</span>
                                        <span>T</span>
                                        <span>W</span>
                                        <span>T</span>
                                        <span>F</span>
                                        <span>S</span>
                                        <span>S</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 5: Custom Fields (Text Left, Visual Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.4"></div>
                <div class="absolute bottom-10 right-1/3 w-[300px] h-[300px] bg-orange-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.3" style="animation-delay: 1.2s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/custom-fields') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Custom Fields
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-amber-300 transition-colors">Collect buyer info</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Gather dietary preferences, t-shirt sizes, or any info you need from ticket buyers with flexible form fields.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Multiple field types</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Required or optional</span>
                            </div>
                            <span class="inline-flex items-center text-amber-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 0.7s;">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-amber-900/50 dark:to-orange-900/50 rounded-xl border border-gray-200 dark:border-amber-500/30 p-5 w-56 space-y-3">
                                    <!-- Text field -->
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Company Name</div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm border border-gray-200 dark:border-white/10">Acme Corp</div>
                                    </div>
                                    <!-- Dropdown field -->
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">T-Shirt Size</div>
                                        <div class="bg-white dark:bg-white/10 rounded-lg px-3 py-2 text-gray-900 dark:text-white text-sm border border-gray-200 dark:border-white/10 flex items-center justify-between">
                                            <span>Large</span>
                                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <!-- Yes/No field -->
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Vegetarian?</div>
                                        <div class="bg-amber-100 dark:bg-amber-500/20 rounded-lg px-3 py-2 text-amber-700 dark:text-amber-300 text-sm border border-amber-200 dark:border-amber-400/30 flex items-center justify-between">
                                            <span>Yes</span>
                                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 6: Team Scheduling (Visual Left, Text Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.35"></div>
                <div class="absolute bottom-20 left-1/3 w-[300px] h-[300px] bg-teal-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.25" style="animation-delay: 0.9s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/team-scheduling') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row-reverse gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Team Scheduling
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-cyan-300 transition-colors">Collaborate together</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Invite team members via email, assign roles, and manage events together seamlessly.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Invite members</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Custom permissions</span>
                            </div>
                            <span class="inline-flex items-center text-cyan-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 1.2s;">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-cyan-900/50 dark:to-teal-900/50 rounded-xl border border-gray-200 dark:border-cyan-500/30 p-4 w-56 space-y-2">
                                    <!-- Team member 1 -->
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-white/10">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white text-xs font-semibold">JD</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-gray-900 dark:text-white text-sm font-medium truncate">John Doe</div>
                                        </div>
                                        <span class="px-1.5 py-0.5 rounded bg-cyan-100 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-300 text-[10px]">Owner</span>
                                    </div>
                                    <!-- Team member 2 -->
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white text-xs font-semibold">AS</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-gray-600 dark:text-gray-300 text-sm font-medium truncate">Alice Smith</div>
                                        </div>
                                        <span class="px-1.5 py-0.5 rounded bg-teal-100 dark:bg-teal-500/20 text-teal-600 dark:text-teal-300 text-[10px]">Admin</span>
                                    </div>
                                    <!-- Team member 3 -->
                                    <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center text-white text-xs font-semibold">BJ</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-gray-600 dark:text-gray-300 text-sm font-medium truncate">Bob Jones</div>
                                        </div>
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 text-[10px]">Follower</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 7: Sub-schedules (Text Left, Visual Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-10 left-1/3 w-[400px] h-[400px] bg-rose-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.4"></div>
                <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-pink-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.3" style="animation-delay: 1.4s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/sub-schedules') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Sub-schedules
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-rose-300 transition-colors">Organize your events</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Create sub-schedules to categorize events by room, stage, series, or any way you like.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Multiple rooms</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Event categories</span>
                            </div>
                            <span class="inline-flex items-center text-rose-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 0.4s;">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-rose-900/50 dark:to-pink-900/50 rounded-xl border border-gray-200 dark:border-rose-500/30 p-4 w-56">
                                    <div class="text-xs text-gray-500 mb-3">Sub-schedules</div>
                                    <div class="space-y-2">
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-rose-100 dark:bg-rose-500/20 border border-rose-200 dark:border-rose-500/30">
                                            <div class="w-2 h-2 rounded-full bg-rose-400"></div>
                                            <span class="text-gray-900 dark:text-white text-sm">Main Stage</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-pink-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Acoustic Room</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-fuchsia-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Outdoor Patio</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                                            <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-sm">Jazz Lounge</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 8: Online Events (Visual Left, Text Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-20 right-1/3 w-[400px] h-[400px] bg-indigo-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.35"></div>
                <div class="absolute bottom-20 left-1/4 w-[300px] h-[300px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.25" style="animation-delay: 1.1s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/online-events') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row-reverse gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Online Events
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-indigo-300 transition-colors">Go live, anywhere</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Toggle any event to online and add your streaming URL. Works with Zoom, YouTube, or any platform.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Virtual events</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Any streaming platform</span>
                            </div>
                            <span class="inline-flex items-center text-indigo-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 0.8s;">
                                <div class="bg-gradient-to-br from-gray-100 to-gray-200 dark:from-indigo-900/50 dark:to-violet-900/50 rounded-xl border border-gray-200 dark:border-indigo-500/30 p-4 w-56">
                                    <!-- Toggle switch -->
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-gray-600 dark:text-gray-300 text-sm">Online Event</span>
                                        <div class="w-10 h-5 bg-indigo-500 rounded-full relative">
                                            <div class="absolute right-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow"></div>
                                        </div>
                                    </div>
                                    <!-- URL field -->
                                    <div>
                                        <div class="text-[10px] text-gray-500 mb-1">Streaming URL</div>
                                        <div class="bg-indigo-100 dark:bg-indigo-500/20 rounded-lg px-3 py-2 text-indigo-600 dark:text-indigo-300 text-sm border border-indigo-200 dark:border-indigo-400/30 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                                            </svg>
                                            <span class="truncate">zoom.us/j/123...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        <!-- Banner 9: Open Source (Text Left, Visual Right) -->
        <section class="relative py-24 lg:py-32 overflow-hidden">
            <!-- Parallax background blobs -->
            <div class="absolute inset-0">
                <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-gray-600/20 rounded-full blur-[120px] animate-pulse-slow parallax-blob" data-parallax-speed="0.4"></div>
                <div class="absolute bottom-10 right-1/3 w-[300px] h-[300px] bg-slate-600/20 rounded-full blur-[100px] animate-pulse-slow parallax-blob" data-parallax-speed="0.3" style="animation-delay: 1.3s;"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <a href="{{ marketing_url('/open-source') }}" class="feature-banner group block">
                    <div class="flex flex-col lg:flex-row gap-8 lg:gap-16 items-center">
                        <!-- Text side -->
                        <div class="flex-1 text-center lg:text-left">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-500/20 text-gray-600 dark:text-gray-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                                Open Source
                            </div>
                            <h2 class="text-3xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-4 group-hover:text-gray-300 transition-colors">Free and open source</h2>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">100% open source under the AAL license. Self-host on your own server or integrate with our REST API.</p>
                            <div class="flex flex-wrap gap-3 justify-center lg:justify-start mb-6">
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Self-host</span>
                                <span class="px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">REST API</span>
                            </div>
                            <span class="inline-flex items-center text-gray-500 dark:text-gray-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Visual side -->
                        <div class="flex-shrink-0">
                            <div class="animate-float" style="animation-delay: 0.6s;">
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-xl border border-gray-300 dark:border-white/20 overflow-hidden w-60">
                                    <!-- Terminal header -->
                                    <div class="flex items-center gap-1.5 px-3 py-2 bg-gray-100 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                                        <div class="w-2.5 h-2.5 rounded-full bg-red-500/70"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/70"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-green-500/70"></div>
                                    </div>
                                    <!-- Terminal content -->
                                    <div class="p-4 font-mono text-xs space-y-1">
                                        <div class="text-gray-400 dark:text-gray-500">$ git clone</div>
                                        <div class="text-cyan-400 break-all leading-tight">github.com/eventschedule</div>
                                        <div class="text-gray-400 dark:text-gray-500 pt-2">$ composer install</div>
                                        <div class="text-green-400">Done!</div>
                                        <div class="text-gray-400 dark:text-gray-500">$ php artisan serve</div>
                                        <div class="text-gray-500 dark:text-gray-400">Server running...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>

    </div>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-violet-600 to-indigo-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to get started?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your free event schedule in seconds. No credit card required.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-violet-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Start for free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <script {!! nonce_attr() !!}>
    (function() {
        // Respect reduced motion preference
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const blobs = document.querySelectorAll('.parallax-blob');
        let ticking = false;

        function updateParallax() {
            const scrollY = window.scrollY;

            blobs.forEach(el => {
                const speed = parseFloat(el.dataset.parallaxSpeed) || 0.3;
                const rect = el.parentElement.getBoundingClientRect();
                const offsetFromCenter = rect.top + rect.height / 2 - window.innerHeight / 2;
                el.style.transform = `translateY(${offsetFromCenter * speed * 0.5}px)`;
            });

            ticking = false;
        }

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });

        updateParallax();
    })();
    </script>
</x-marketing-layout>
