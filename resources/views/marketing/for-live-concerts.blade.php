<x-marketing-layout>
    <x-slot name="title">Event Schedule for Live Concerts | Stream, Schedule & Sell Virtual Tickets</x-slot>
    <x-slot name="description">Stream live concerts to fans worldwide. Sell virtual tickets alongside venue tickets, email fans directly, and manage your streaming schedule. Zero platform fees.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Live Concerts</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Live Concerts",
        "description": "Stream live concerts to fans worldwide. Sell virtual tickets alongside venue tickets, email fans directly, and manage your streaming schedule. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Live Concert Streamers"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section - Mesh Gradient -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute bottom-0 left-[-20%] w-[70%] h-[70%] bg-rose-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-0 right-[-10%] w-[50%] h-[60%] bg-amber-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-orange-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Grid overlay for texture -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge with music/streaming icon -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Artists, Bands & Promoters Streaming Live</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Stream live concerts to every screen.<br>
                <span class="concert-glow-text">No audience limit.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                From intimate acoustic sets to arena livestreams. Sell virtual tickets, reach fans worldwide, and keep 100% of the revenue.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-rose-600 to-amber-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-lg shadow-rose-500/25">
                    Create your concert schedule
                    <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-8 text-base text-gray-400 dark:text-gray-500 max-w-2xl mx-auto">
                The live concert streaming platform with built-in virtual ticket sales, fan email notifications, and scheduling for bands, solo artists, and promoters.
            </p>

            <!-- Type tags -->
            <div class="mt-14 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 text-xs font-medium border border-rose-200 dark:border-rose-800/50">Acoustic Sets</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-800/50">Rock Shows</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300 text-xs font-medium border border-orange-200 dark:border-orange-800/50">Jazz Nights</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 text-xs font-medium border border-red-200 dark:border-red-800/50">Festival Streams</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300 text-xs font-medium border border-rose-200 dark:border-rose-800/50">Album Release Shows</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 text-xs font-medium border border-amber-200 dark:border-amber-800/50">DJ Sets</span>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-rose-400 mb-2">73%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of music fans have watched a livestreamed concert</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-amber-400 mb-2">4x</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">wider reach with virtual + in-person tickets</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-orange-400 mb-2">$0</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">platform fees on virtual ticket sales</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Everything you need to stream live concerts
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Email fans before you go live (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-amber-100 dark:from-rose-900 dark:to-amber-900 border border-rose-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-rose-500/5 rounded-full blur-[100px]"></div>

                    <div class="relative flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 text-sm font-medium mb-5 border border-rose-200 dark:border-rose-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email Fans
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Email fans before your livestream</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Announce upcoming streams, send reminders, and follow up with recordings. Your fans, your inbox.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Show announcements</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Stream reminders</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Recording links</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-rose-100 to-amber-100 dark:from-rose-950 dark:to-amber-950 rounded-2xl border border-rose-300 dark:border-rose-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-rose-500 to-amber-500 flex items-center justify-center text-white text-sm font-semibold">LV</div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-semibold text-sm">Live Venue</div>
                                            <div class="text-rose-600 dark:text-rose-300 text-xs">Tonight's livestream</div>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-rose-600/30 to-amber-600/30 rounded-xl p-3 border border-rose-400/20">
                                        <div class="text-center">
                                            <div class="text-gray-900 dark:text-white text-xs font-semibold mb-1">TONIGHT AT 9 PM</div>
                                            <div class="text-rose-700 dark:text-rose-300 text-sm font-bold">Live from The Fillmore</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">Watch from home - $15</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-4 text-xs">
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-emerald-500 dark:text-emerald-400 font-semibold">72%</span> opened</div>
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-amber-500 dark:text-amber-400 font-semibold">48%</span> clicked</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sell virtual tickets alongside venue tickets -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-5 border border-emerald-200 dark:border-emerald-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Dual Ticketing
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell virtual tickets alongside venue tickets</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">In-person + virtual stream. 100% of Stripe payments go to you. See all <a href="{{ route('marketing.ticketing') }}" class="text-emerald-600 dark:text-emerald-400 underline hover:no-underline">ticketing features</a>.</p>

                        <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-4">
                            <div class="space-y-2 mb-3">
                                <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-400/20">
                                    <span class="text-gray-900 dark:text-white text-xs font-medium">Venue Ticket</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 text-xs font-semibold">$35</span>
                                </div>
                                <div class="flex items-center justify-between p-2 rounded-lg bg-amber-400/20">
                                    <span class="text-gray-900 dark:text-white text-xs font-medium">Virtual Stream</span>
                                    <span class="text-amber-600 dark:text-amber-400 text-xs font-semibold">$15</span>
                                </div>
                            </div>
                            <div class="border-t border-emerald-400/20 pt-3">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">$0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- One link for all your shows -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 text-sm font-medium mb-5 border border-sky-200 dark:border-sky-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Share Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for all your live shows</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Spotify bio, YouTube channel, social profiles. All shows in one place.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-sky-500/20 border border-sky-400/30 mb-3">
                            <svg aria-hidden="true" class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-xs font-mono">eventschedule.com/yourband</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-center">
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-700 dark:text-sky-300 text-[10px]">Spotify</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-700 dark:text-sky-300 text-[10px]">YouTube</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-sky-700 dark:text-sky-300 text-[10px]">Social</div>
                        </div>
                    </div>
                </div>

                <!-- Works with any streaming platform (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium mb-5 border border-amber-200 dark:border-amber-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Any Platform
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Works with any streaming platform</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">YouTube Live, Twitch, Instagram Live, custom RTMP. Add your streaming link, fans join from your schedule. Learn more about <a href="{{ route('marketing.online_events') }}" class="text-amber-600 dark:text-amber-400 underline hover:no-underline">online event features</a>.</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex items-center gap-4">
                                <div class="bg-amber-100 dark:bg-amber-500/20 rounded-xl border border-amber-400/30 p-4 w-36">
                                    <div class="text-amber-700 dark:text-amber-300 text-xs text-center mb-2 font-semibold">Your Schedule</div>
                                    <div class="space-y-1.5">
                                        <div class="h-2 bg-gray-300 dark:bg-white/20 rounded"></div>
                                        <div class="h-2 bg-amber-400/40 rounded w-3/4"></div>
                                    </div>
                                    <div class="mt-3 p-2 rounded-lg bg-amber-200 dark:bg-amber-400/20 border border-amber-400/30">
                                        <div class="text-[10px] text-gray-900 dark:text-white text-center font-medium">Live Concert</div>
                                        <div class="text-[8px] text-amber-700 dark:text-amber-300 text-center mt-0.5">Sat 9:00 PM</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <svg aria-hidden="true" class="w-6 h-6 text-amber-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="text-amber-600 dark:text-amber-400 text-[10px]">stream link</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-36">
                                    <div class="text-gray-600 dark:text-gray-300 text-xs text-center mb-2 font-semibold">Streaming</div>
                                    <div class="space-y-2 text-center">
                                        <div class="p-1.5 rounded bg-red-400/20 text-[10px] text-red-700 dark:text-red-300">YouTube Live</div>
                                        <div class="p-1.5 rounded bg-purple-400/20 text-[10px] text-purple-700 dark:text-purple-300">Twitch</div>
                                        <div class="p-1.5 rounded bg-pink-400/20 text-[10px] text-pink-700 dark:text-pink-300">Instagram</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recurring show series -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900 dark:to-rose-900 border border-red-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 text-sm font-medium mb-5 border border-red-200 dark:border-red-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Recurring show series</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Weekly live sessions, monthly showcases. Set once, fans follow.</p>

                    <div class="bg-red-500/20 rounded-xl border border-red-400/30 p-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 p-1.5 rounded bg-red-400/20">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                                <span class="text-gray-900 dark:text-white text-[10px] font-medium">Fri - Live Session</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-red-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Live Session</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-red-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Live Session</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-red-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Live Session</span>
                            </div>
                        </div>
                        <div class="mt-2 text-center text-red-600 dark:text-red-300 text-[10px]">Repeats weekly</div>
                    </div>
                </div>

                <!-- Google Calendar Sync -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-sm font-medium mb-5 border border-blue-200 dark:border-blue-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendar Sync
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sync live shows with Google Calendar</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Shows, soundchecks, rehearsals all synced. Two-way sync keeps everything in one place.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-700 dark:text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-rose-400/80 dark:bg-rose-400/40 rounded text-[6px] text-white px-1">Show</div>
                                <div class="h-1.5 bg-amber-400/80 dark:bg-amber-400/40 rounded text-[6px] text-white px-1">Rehearsal</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center gap-0.5">
                            <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg aria-hidden="true" class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                <!-- Fans follow your shows -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-rose-100 dark:from-orange-900 dark:to-rose-900 border border-orange-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300 text-sm font-medium mb-5 border border-orange-200 dark:border-orange-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Followers
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Fans follow your live shows</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Location-based notifications when you add shows near them.</p>

                    <div class="flex items-center justify-center">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-rose-500 to-amber-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-red-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-white/20 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-gray-600 dark:text-white text-xs">+2k</div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-orange-600 dark:text-orange-300 text-xs">2,147 fans following your schedule</div>
                </div>

            </div>
        </div>
    </section>

    <!-- Journey Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From bedroom streams to sold-out livestreams
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Event Schedule grows with your music career
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Living room sessions -->
                <div class="bg-gradient-to-br from-rose-100 to-rose-50 dark:from-[#1a0f0f] dark:to-[#0a0a0f] rounded-2xl p-6 border border-rose-200 dark:border-rose-900/20 hover:border-rose-300 dark:hover:border-rose-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Living room sessions</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">First streams from your living room. Share a link and start building your audience.</p>
                </div>

                <!-- Local venue streams -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a150f] dark:to-[#0a0a0f] rounded-2xl p-6 border border-amber-200 dark:border-amber-900/20 hover:border-amber-300 dark:hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Local venue streams</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Hybrid shows with in-person and online audiences. Sell both ticket types.</p>
                </div>

                <!-- Multi-camera productions -->
                <div class="bg-gradient-to-br from-orange-100 to-orange-50 dark:from-[#1a100f] dark:to-[#0a0a0f] rounded-2xl p-6 border border-orange-200 dark:border-orange-900/20 hover:border-orange-300 dark:hover:border-orange-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Multi-camera productions</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Professional streams with ticketed access. Charge what your production is worth.</p>
                </div>

                <!-- Pay-per-view concerts -->
                <div class="bg-gradient-to-br from-red-100 to-red-50 dark:from-[#1a0f10] dark:to-[#0a0a0f] rounded-2xl p-6 border border-red-200 dark:border-red-900/20 hover:border-red-300 dark:hover:border-red-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pay-per-view concerts</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Premium livestream events with global reach. Zero platform fees on every sale.</p>
                </div>

                <!-- Festival livestreams -->
                <div class="bg-gradient-to-br from-rose-100 to-rose-50 dark:from-[#1a0f12] dark:to-[#0a0a0f] rounded-2xl p-6 border border-rose-200 dark:border-rose-900/20 hover:border-rose-300 dark:hover:border-rose-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Festival livestreams</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Stream your festival sets to fans who couldn't make it. Reach a worldwide audience.</p>
                </div>

                <!-- Global tours, streamed -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a150f] dark:to-[#0a0a0f] rounded-2xl p-6 border border-amber-200 dark:border-amber-900/20 hover:border-amber-300 dark:hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Global tours, streamed</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Every tour stop available to fans worldwide. Build a global audience from every show.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for every genre and stage
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether it's an intimate acoustic set or a festival stream, Event Schedule works for you. Also see <a href="{{ route('marketing.for_musicians') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Event Schedule for Musicians</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Solo Acoustic Artists -->
                <x-sub-audience-card
                    name="Solo Acoustic Artists"
                    description="Intimate living room sessions and acoustic sets streamed to fans everywhere."
                    icon-color="cyan"
                    blog-slug="for-solo-acoustic-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Rock & Pop Bands -->
                <x-sub-audience-card
                    name="Rock & Pop Bands"
                    description="High-energy performances streamed from venues and studios to fans worldwide."
                    icon-color="teal"
                    blog-slug="for-rock-pop-bands-live"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Jazz & Blues Acts -->
                <x-sub-audience-card
                    name="Jazz & Blues Acts"
                    description="Club sessions and late-night sets for a worldwide audience."
                    icon-color="sky"
                    blog-slug="for-jazz-blues-acts"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- DJs & Electronic Artists -->
                <x-sub-audience-card
                    name="DJs & Electronic Artists"
                    description="Live DJ sets, producer sessions, and festival streams for dance music fans."
                    icon-color="blue"
                    blog-slug="for-djs-electronic-artists"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Classical & Orchestra -->
                <x-sub-audience-card
                    name="Classical & Orchestra"
                    description="Concert hall performances and recitals for remote audiences worldwide."
                    icon-color="amber"
                    blog-slug="for-classical-orchestra"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Cover & Tribute Bands -->
                <x-sub-audience-card
                    name="Cover & Tribute Bands"
                    description="Fan-favorite shows streamed from bars and venues to audiences everywhere."
                    icon-color="emerald"
                    blog-slug="for-cover-tribute-bands-live"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                    Three steps to a global audience
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-600 to-amber-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-rose-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Add your show</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Set the date, add your streaming link, and create virtual tickets.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-600 to-amber-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-rose-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your link</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Fans register and get notified when you go live.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-rose-600 to-amber-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-rose-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Go live</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Stream to fans everywhere. No platform fees, no middleman.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Frequently asked questions about streaming live concerts
            </h2>

            <div class="space-y-6">
                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Do I need special equipment to stream a live concert?</h3>
                    <p class="text-gray-500 dark:text-gray-400">No. Event Schedule works with any streaming platform. Stream from your phone with Instagram Live, use OBS with YouTube Live or Twitch, or set up a multi-camera production. Just add your streaming link to the event.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can I sell virtual tickets and venue tickets for the same show?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Create multiple ticket types for each event. Fans choose between attending in person or watching the livestream. You keep 100% of the revenue (minus standard Stripe processing fees).</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">What streaming platforms does Event Schedule work with?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Any platform that gives you a streaming URL. YouTube Live, Twitch, Instagram Live, Facebook Live, Vimeo, custom RTMP servers, and more. Event Schedule is platform-agnostic.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">How do fans get the streaming link?</h3>
                    <p class="text-gray-500 dark:text-gray-400">When fans register or purchase a virtual ticket, the streaming link appears on their ticket and event page. You can also email all registered fans directly from your dashboard before going live.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Is Event Schedule really free for streaming concerts?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. The free plan includes unlimited events, fan email notifications, and follower features. There are zero platform fees on ticket sales at any plan level. Stripe charges its standard processing fee (2.9% + $0.30).</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-20 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Key features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="Ticketing"
                    description="Sell tickets with QR check-in and zero platform fees"
                    :url="marketing_url('/features/ticketing')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Newsletters"
                    description="Send event updates directly to followers' inboxes"
                    :url="marketing_url('/features/newsletters')"
                    icon-color="green"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Calendar Sync"
                    description="Two-way sync with Google Calendar"
                    :url="marketing_url('/features/calendar-sync')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- Related Pages -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related pages</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ marketing_url('/for-musicians') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Musicians</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-music-venues') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Music Venues</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-djs') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">DJs</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-watch-parties') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Watch Parties</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-rose-200 dark:border-rose-900/20">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-rose-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-amber-600/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your stage reaches every screen. Zero fees.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                Stream your shows. Sell virtual tickets. Own your audience.<br class="hidden md:block">Free forever.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-rose-600 to-amber-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-rose-500/20">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        "name": "Event Schedule for Live Concerts",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Live Concert Streaming & Scheduling Software",
        "operatingSystem": "Web",
        "description": "Stream live concerts to fans worldwide. Sell virtual tickets alongside venue tickets, email fans directly, and manage your streaming schedule.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email fans before and after livestreams",
            "Virtual and in-person dual ticketing",
            "One link for all shows",
            "Works with YouTube Live, Twitch, Instagram Live",
            "Recurring show series scheduling",
            "Google Calendar two-way sync",
            "Fan follower notifications",
            "Zero platform fees on ticket sales"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "live concert streaming, virtual concert tickets, livestream concerts",
        "screenshot": "{{ asset('social/features.png') }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>

    <!-- FAQ Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Do I need special equipment to stream a live concert?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "No. Event Schedule works with any streaming platform. Stream from your phone with Instagram Live, use OBS with YouTube Live or Twitch, or set up a multi-camera production. Just add your streaming link to the event."
                }
            },
            {
                "@type": "Question",
                "name": "Can I sell virtual tickets and venue tickets for the same show?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Create multiple ticket types for each event. Fans choose between attending in person or watching the livestream. You keep 100% of the revenue (minus standard Stripe processing fees)."
                }
            },
            {
                "@type": "Question",
                "name": "What streaming platforms does Event Schedule work with?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a streaming URL. YouTube Live, Twitch, Instagram Live, Facebook Live, Vimeo, custom RTMP servers, and more. Event Schedule is platform-agnostic."
                }
            },
            {
                "@type": "Question",
                "name": "How do fans get the streaming link?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When fans register or purchase a virtual ticket, the streaming link appears on their ticket and event page. You can also email all registered fans directly from your dashboard before going live."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule really free for streaming concerts?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. The free plan includes unlimited events, fan email notifications, and follower features. There are zero platform fees on ticket sales at any plan level. Stripe charges its standard processing fee (2.9% + $0.30)."
                }
            }
        ]
    }
    </script>

    <style {!! nonce_attr() !!}>
        .concert-glow-text {
            background: linear-gradient(135deg, #e11d48, #f59e0b, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(225, 29, 72, 0.3);
        }

        .dark .concert-glow-text {
            background: linear-gradient(135deg, #fb7185, #fbbf24, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 113, 133, 0.3);
        }
    </style>
</x-marketing-layout>
