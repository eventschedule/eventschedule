<x-marketing-layout>
    <x-slot name="title">Event Schedule for Watch Parties | Host, Schedule & Manage Virtual Watch Parties</x-slot>
    <x-slot name="description">Free, open-source watch party scheduling software with registration, ticketing, and email notifications. Works with any streaming platform. Zero platform fees.</x-slot>
    <x-slot name="keywords">watch party platform, schedule watch parties, virtual watch party, online watch party hosting, group streaming events, watch party ticketing, movie night scheduling, premiere screening platform</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">For Watch Parties</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule for Watch Parties",
        "description": "Free, open-source watch party scheduling software with registration, ticketing, and email notifications. Works with any streaming platform. Zero platform fees.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Management",
        "audience": {
            "@type": "Audience",
            "audienceType": "Watch Party Hosts"
        }
    }
    </script>
    </x-slot>

    <!-- Hero Section - Mesh Gradient -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute bottom-0 left-[-20%] w-[70%] h-[70%] bg-indigo-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-0 right-[-10%] w-[50%] h-[60%] bg-cyan-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-sky-600/10 rounded-full blur-[100px]"></div>
        </div>

        <!-- Grid overlay for texture -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-3 px-5 py-2.5 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 backdrop-blur-sm">
                <div class="relative">
                    <svg aria-hidden="true" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-sm text-gray-600 dark:text-gray-300 font-medium tracking-wide">For Hosts, Communities & Creators</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Schedule watch parties that fill up.<br>
                <span class="watchparty-glow-text">Free. No platform fees.</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                The free watch party platform for premiere screenings, movie nights, and group viewing events. Manage registrations, send viewer emails, and share one link with your community.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="group inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-lg shadow-indigo-500/25">
                    Create your watch party schedule
                    <svg aria-hidden="true" class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-8 text-base text-gray-400 dark:text-gray-500 max-w-2xl mx-auto">
                Event Schedule is a free, open-source watch party platform with built-in registration, viewer email notifications, paid ticketing, and Google Calendar sync. Host watch parties online with zero platform fees.
            </p>

            <!-- Type tags -->
            <div class="mt-14 flex flex-wrap justify-center gap-2">
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-800/50">Premiere Screenings</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-800/50">Movie Nights</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-800/50">Sports Watch Parties</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 text-xs font-medium border border-indigo-200 dark:border-indigo-800/50">Series Finales</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300 text-xs font-medium border border-cyan-200 dark:border-cyan-800/50">Documentary Screenings</span>
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 text-xs font-medium border border-sky-200 dark:border-sky-800/50">Gaming Events</span>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-16 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div class="p-6">
                    <div class="text-4xl font-bold text-indigo-400 mb-2">~92%</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">of viewers prefer watching with others over solo</div>
                </div>
                <div class="p-6 border-x border-gray-200 dark:border-white/5">
                    <div class="text-4xl font-bold text-cyan-400 mb-2">2x</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">more engagement with scheduled group viewings vs solo</div>
                </div>
                <div class="p-6">
                    <div class="text-4xl font-bold text-sky-400 mb-2">$0</div>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">platform fees on paid watch party events</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Everything you need to host watch parties
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Email viewers before screenings (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-cyan-100 dark:from-indigo-900 dark:to-cyan-900 border border-indigo-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500/5 rounded-full blur-[100px]"></div>

                    <div class="relative flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-5 border border-indigo-200 dark:border-indigo-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email Viewers
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Email viewers before screenings</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-6">Send reminders, share viewing guides, and follow up with discussion prompts. Your audience, your inbox.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Session reminders</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Viewing guides</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Discussion prompts</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gradient-to-br from-indigo-100 to-cyan-100 dark:from-indigo-950 dark:to-cyan-950 rounded-2xl border border-indigo-300 dark:border-indigo-400/30 p-4 max-w-xs">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-cyan-500 flex items-center justify-center text-white text-sm font-semibold">WP</div>
                                        <div>
                                            <div class="text-gray-900 dark:text-white font-semibold text-sm">Watch Party Host</div>
                                            <div class="text-indigo-600 dark:text-indigo-300 text-xs">Screening reminder</div>
                                        </div>
                                    </div>
                                    <div class="bg-gradient-to-br from-indigo-600/30 to-cyan-600/30 rounded-xl p-3 border border-indigo-400/20">
                                        <div class="text-center">
                                            <div class="text-gray-900 dark:text-white text-xs font-semibold mb-1">FRIDAY AT 8 PM</div>
                                            <div class="text-indigo-700 dark:text-indigo-300 text-sm font-bold">Movie Night</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-[10px] mt-1">Grab your snacks and join us</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex gap-4 text-xs">
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-emerald-500 dark:text-emerald-400 font-semibold">72%</span> opened</div>
                                        <div class="text-gray-500 dark:text-gray-400"><span class="text-amber-500 dark:text-amber-400 font-semibold">38%</span> clicked</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sell tickets to premium screenings -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="absolute bottom-0 right-0 w-64 h-64 bg-emerald-500/5 rounded-full blur-[80px]"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-sm font-medium mb-5 border border-emerald-200 dark:border-emerald-800/30">
                            <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            Ticketing
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sell tickets to premium screenings</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Charge for paid premieres or exclusive screenings. 100% of Stripe payments go to you. See all <a href="{{ route('marketing.ticketing') }}" class="text-emerald-600 dark:text-emerald-400 underline hover:no-underline">ticketing features</a>.</p>

                        <div class="bg-emerald-500/20 rounded-xl border border-emerald-400/30 p-4">
                            <div class="text-center mb-3">
                                <div class="text-emerald-700 dark:text-emerald-300 text-xs">You keep</div>
                                <div class="text-gray-900 dark:text-white text-3xl font-bold">100%</div>
                                <div class="text-gray-500 dark:text-gray-400 text-xs">of ticket sales</div>
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

                <!-- One link for all your watch parties -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-indigo-100 dark:from-cyan-900 dark:to-indigo-900 border border-cyan-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-300 text-sm font-medium mb-5 border border-cyan-200 dark:border-cyan-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Share Link
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">One link for all your watch parties</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Put it on your website, social profiles, or community channels. All your watch parties in one place.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-cyan-500/20 border border-cyan-400/30 mb-3">
                            <svg aria-hidden="true" class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            <span class="text-gray-900 dark:text-white text-xs font-mono">eventschedule.com/yourparty</span>
                        </div>
                        <div class="grid grid-cols-3 gap-1 text-center">
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-cyan-700 dark:text-cyan-300 text-[10px]">Website</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-cyan-700 dark:text-cyan-300 text-[10px]">Social</div>
                            <div class="p-1.5 rounded bg-gray-100 dark:bg-white/5 text-cyan-700 dark:text-cyan-300 text-[10px]">Community</div>
                        </div>
                    </div>
                </div>

                <!-- Works with any streaming platform (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-indigo-100 dark:from-sky-900 dark:to-indigo-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 text-sm font-medium mb-5 border border-sky-200 dark:border-sky-800/30">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Any Platform
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Works with any streaming platform</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-lg">YouTube, Twitch, Discord, or any custom streaming link. Add your URL and viewers join from your schedule. Learn more about <a href="{{ route('marketing.online_events') }}" class="text-sky-600 dark:text-sky-400 underline hover:no-underline">online event features</a>.</p>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="flex items-center gap-4">
                                <div class="bg-indigo-100 dark:bg-indigo-500/20 rounded-xl border border-indigo-400/30 p-4 w-36">
                                    <div class="text-indigo-700 dark:text-indigo-300 text-xs text-center mb-2 font-semibold">Your Schedule</div>
                                    <div class="space-y-1.5">
                                        <div class="h-2 bg-gray-300 dark:bg-white/20 rounded"></div>
                                        <div class="h-2 bg-indigo-400/40 rounded w-3/4"></div>
                                    </div>
                                    <div class="mt-3 p-2 rounded-lg bg-indigo-200 dark:bg-indigo-400/20 border border-indigo-400/30">
                                        <div class="text-[10px] text-gray-900 dark:text-white text-center font-medium">Movie Night</div>
                                        <div class="text-[8px] text-indigo-700 dark:text-indigo-300 text-center mt-0.5">Fri 8:00 PM</div>
                                    </div>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <svg aria-hidden="true" class="w-6 h-6 text-indigo-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                    <span class="text-indigo-600 dark:text-indigo-400 text-[10px]">join link</span>
                                </div>
                                <div class="bg-gray-200 dark:bg-white/10 rounded-xl border border-gray-300 dark:border-white/20 p-4 w-36">
                                    <div class="text-gray-600 dark:text-gray-300 text-xs text-center mb-2 font-semibold">Streaming</div>
                                    <div class="space-y-2 text-center">
                                        <div class="p-1.5 rounded bg-red-400/20 text-[10px] text-red-700 dark:text-red-300">YouTube</div>
                                        <div class="p-1.5 rounded bg-purple-400/20 text-[10px] text-purple-700 dark:text-purple-300">Twitch</div>
                                        <div class="p-1.5 rounded bg-indigo-400/20 text-[10px] text-indigo-700 dark:text-indigo-300">Discord</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recurring watch party series -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-sm font-medium mb-5 border border-amber-200 dark:border-amber-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Recurring
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Recurring watch party series</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Weekly movie nights or monthly screenings. Set it once and let viewers follow along.</p>

                    <div class="bg-amber-500/20 rounded-xl border border-amber-400/30 p-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/20">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-900 dark:text-white text-[10px] font-medium">Fri - Movie Night</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Movie Night</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Movie Night</span>
                            </div>
                            <div class="flex items-center gap-2 p-1.5 rounded bg-amber-400/10">
                                <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                <span class="text-gray-600 dark:text-gray-300 text-[10px]">Fri - Movie Night</span>
                            </div>
                        </div>
                        <div class="mt-2 text-center text-amber-600 dark:text-amber-300 text-[10px]">Repeats weekly</div>
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
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Google Calendar sync</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Two-way sync. Screenings appear on your calendar automatically.</p>

                    <div class="flex items-center justify-center gap-3">
                        <div class="bg-blue-500/20 rounded-xl border border-blue-400/30 p-3 w-20">
                            <div class="text-[10px] text-blue-700 dark:text-blue-300 mb-1 text-center">Schedule</div>
                            <div class="space-y-1">
                                <div class="h-1.5 bg-indigo-400/80 dark:bg-indigo-400/40 rounded text-[6px] text-white px-1">Film</div>
                                <div class="h-1.5 bg-amber-400/80 dark:bg-amber-400/40 rounded text-[6px] text-white px-1">Sports</div>
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

                <!-- Viewers follow your schedule -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-100 to-sky-100 dark:from-indigo-900 dark:to-sky-900 border border-indigo-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 text-sm font-medium mb-5 border border-indigo-200 dark:border-indigo-800/30">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Followers
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Viewers follow your schedule</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Followers get notified when you schedule new watch parties.</p>

                    <div class="flex items-center justify-center">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-cyan-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">A</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-sky-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">B</div>
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-sky-500 to-indigo-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">C</div>
                            <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-white/20 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-gray-600 dark:text-white text-xs">+156</div>
                        </div>
                    </div>
                    <div class="text-center mt-3 text-indigo-600 dark:text-indigo-400 text-xs">159 following your watch parties</div>
                </div>

            </div>
        </div>
    </section>

    <!-- Journey Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    From your first watch party to a community screening series
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Event Schedule grows with your watch party program
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- First watch party -->
                <div class="bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-[#10101a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-indigo-200 dark:border-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">First watch party</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Share a link and host your first group screening. Free registration gets your viewers in the door.</p>
                </div>

                <!-- Recurring movie nights -->
                <div class="bg-gradient-to-br from-cyan-100 to-cyan-50 dark:from-[#101518] dark:to-[#0a0a0f] rounded-2xl p-6 border border-cyan-200 dark:border-cyan-900/20 hover:border-cyan-300 dark:hover:border-cyan-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Recurring movie nights</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Set up weekly or monthly screenings. Your viewers follow along and get notified.</p>
                </div>

                <!-- Paid premiere screenings -->
                <div class="bg-gradient-to-br from-sky-100 to-sky-50 dark:from-[#10121a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-sky-200 dark:border-sky-900/20 hover:border-sky-300 dark:hover:border-sky-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-100 dark:bg-sky-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Paid premiere screenings</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Start charging for premium access. Sell tickets with zero platform fees.</p>
                </div>

                <!-- Multi-timezone events -->
                <div class="bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-[#10101a] dark:to-[#0a0a0f] rounded-2xl p-6 border border-indigo-200 dark:border-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Multi-timezone events</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Viewers see screening times in their local timezone. No confusion, no missed showings.</p>
                </div>

                <!-- Community series -->
                <div class="bg-gradient-to-br from-cyan-100 to-cyan-50 dark:from-[#101518] dark:to-[#0a0a0f] rounded-2xl p-6 border border-cyan-200 dark:border-cyan-900/20 hover:border-cyan-300 dark:hover:border-cyan-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Community series</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Build a following. Followers get notified when you announce new screenings.</p>
                </div>

                <!-- Festival watch parties -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-50 dark:from-[#1a1510] dark:to-[#0a0a0f] rounded-2xl p-6 border border-amber-200 dark:border-amber-900/20 hover:border-amber-300 dark:hover:border-amber-800/40 transition-colors">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 mb-4">
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Festival watch parties</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Multi-day film festivals or marathon screening events. Group events by category with sub-schedules.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Perfect For Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Watch party software for every community
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Whether it's a film club or a sports viewing party, Event Schedule works for you. Also see Event Schedule for <a href="{{ route('marketing.for_live_concerts') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Live Concerts</a> and <a href="{{ route('marketing.for_virtual_conferences') }}" class="text-gray-600 dark:text-gray-300 underline hover:no-underline">Virtual Conferences</a>.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Film Clubs & Cinephiles -->
                <x-sub-audience-card
                    name="Film Clubs & Cinephiles"
                    description="Use Event Schedule for classic film screenings, director retrospectives, and themed movie nights. Build a community around your love of cinema."
                    icon-color="indigo"
                    blog-slug="for-film-clubs-cinephiles"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Content Creators & YouTubers -->
                <x-sub-audience-card
                    name="Content Creators & YouTubers"
                    description="Premiere new videos with your audience using Event Schedule. Host reaction watch-alongs, sell tickets to exclusive screenings, and celebrate milestones together."
                    icon-color="cyan"
                    blog-slug="for-content-creators-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Sports Fan Communities -->
                <x-sub-audience-card
                    name="Sports Fan Communities"
                    description="Game day watch parties, playoff screenings, and draft night events. Schedule group viewing events and bring fans together for every big moment."
                    icon-color="sky"
                    blog-slug="for-sports-fan-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Gaming Communities -->
                <x-sub-audience-card
                    name="Gaming Communities"
                    description="Esports watch parties, game launch events, and tournament screenings. Watch and chat with your gaming community."
                    icon-color="amber"
                    blog-slug="for-gaming-community-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Corporate & Team Building -->
                <x-sub-audience-card
                    name="Corporate & Team Building"
                    description="Virtual team movie nights, company screening events, and remote team bonding activities."
                    icon-color="emerald"
                    blog-slug="for-corporate-team-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot:icon>
                </x-sub-audience-card>

                <!-- Education & Documentary Groups -->
                <x-sub-audience-card
                    name="Education & Documentary Groups"
                    description="Documentary screenings with discussion, educational film series, and classroom viewing events."
                    icon-color="rose"
                    blog-slug="for-education-documentary-watch-parties"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-6 h-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
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
                    How to host a watch party online in three steps
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 to-cyan-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-indigo-600/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Create your screening</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Add your date, streaming link, and tickets. Set up free or paid registration.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 to-cyan-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-indigo-600/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Share your link</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Viewers register. Send viewing guides before the screening.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 to-cyan-600 text-white text-xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-indigo-600/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Watch together</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Stream to your community. Discuss, react, and enjoy together.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white text-center mb-12">
                Frequently asked questions about hosting watch parties
            </h2>

            <div class="space-y-6">
                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">What streaming platforms work with watch parties?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Any platform that gives you a streaming or meeting link. YouTube, Twitch, Discord, Zoom, and custom streaming solutions all work. Event Schedule is platform-agnostic - just paste your link and viewers join from your schedule. Learn more about <a href="{{ route('marketing.online_events') }}" class="text-indigo-600 dark:text-indigo-400 underline hover:no-underline">online event features</a>.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can I charge for watch party access?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Set up paid registration with Stripe for premium screenings, exclusive premieres, or VIP watch parties. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30). See all <a href="{{ route('marketing.ticketing') }}" class="text-indigo-600 dark:text-indigo-400 underline hover:no-underline">ticketing features</a>.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Can I schedule recurring watch parties?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Set up weekly movie nights, monthly documentary screenings, or any recurring schedule. Viewers can follow your schedule and get notified whenever you add new screenings. Your full watch party calendar lives on one shareable page. Screenings also sync with <a href="{{ route('marketing.calendar_sync') }}" class="text-indigo-600 dark:text-indigo-400 underline hover:no-underline">Google Calendar</a>.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">How do viewers get the streaming link?</h3>
                    <p class="text-gray-500 dark:text-gray-400">When viewers register or purchase a ticket, the streaming link appears on their registration confirmation and event page. You can also email all registrants directly from your dashboard before the screening starts.</p>
                </div>

                <div class="bg-white dark:bg-[#0a0a0f] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Is Event Schedule free for hosting watch parties?</h3>
                    <p class="text-gray-500 dark:text-gray-400">Yes. Event Schedule is free, open-source watch party hosting software. The free plan includes unlimited events, viewer email notifications, follower features, and Google Calendar sync. There are zero platform fees on ticket sales at any plan level. You only pay Stripe's standard processing fee if you sell tickets. You can also <a href="https://github.com/eventschedule/eventschedule" class="text-indigo-600 dark:text-indigo-400 underline hover:no-underline">selfhost Event Schedule</a> on your own server.</p>
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
                    name="Online Events"
                    description="Host virtual events with any streaming platform"
                    :url="marketing_url('/features/online-events')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Analytics"
                    description="Track page views, devices, and traffic sources"
                    :url="marketing_url('/features/analytics')"
                    icon-color="emerald"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
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
            </div>
        </div>
    </section>

    <!-- Related Pages -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related pages</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="{{ marketing_url('/for-live-concerts') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Live Concerts</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-bars') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Bars</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-online-classes') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Online Classes</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ marketing_url('/for-live-qa-sessions') }}" class="group flex items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule for</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Live Q&A Sessions</div>
                    </div>
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-24 overflow-hidden border-t border-indigo-200 dark:border-indigo-900/20">
        <!-- Mesh gradient background -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-[-10%] w-[50%] h-[60%] bg-indigo-600/15 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-[-10%] w-[50%] h-[60%] bg-cyan-600/15 rounded-full blur-[120px]"></div>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Your screenings. Your community. No middleman.
            </h2>
            <p class="text-xl text-gray-500 dark:text-gray-400 mb-10 max-w-2xl mx-auto">
                The free watch party app with registration, ticketing, and viewer notifications.<br class="hidden md:block">No platform fees. Open source.
            </p>
            <a href="{{ route('sign_up') }}" class="group inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-2xl hover:scale-105 transition-transform duration-150 will-change-transform shadow-xl shadow-indigo-500/20">
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
        "name": "Event Schedule for Watch Parties",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Watch Party Scheduling Software",
        "operatingSystem": "Web",
        "description": "Schedule watch parties with built-in registration, viewer notifications, ticketing, and streaming link integration. Works with YouTube, Twitch, Discord, and any platform.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free forever"
        },
        "featureList": [
            "Email notifications to registered viewers",
            "One registration link for all watch parties",
            "Zero-fee ticket sales for paid screenings",
            "Google Calendar two-way sync",
            "Works with YouTube, Twitch, Discord, any platform",
            "Recurring watch party series scheduling",
            "Viewer registration management",
            "Follower notifications for new screenings",
            "Open source watch party platform",
            "Selfhosted watch party scheduling option"
        ],
        "url": "{{ url()->current() }}",
        "keywords": "watch party platform, schedule watch parties, virtual watch party, online watch party hosting, group streaming events, watch party ticketing, movie night scheduling, free watch party app",
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
                "name": "What streaming platforms work with watch parties?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Any platform that gives you a streaming or meeting link. YouTube, Twitch, Discord, Zoom, and custom streaming solutions all work. Event Schedule is platform-agnostic - just paste your link and viewers join from your schedule. Learn more about online event features."
                }
            },
            {
                "@type": "Question",
                "name": "Can I charge for watch party access?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up paid registration with Stripe for premium screenings, exclusive premieres, or VIP watch parties. You keep 100% of the ticket revenue - Event Schedule charges zero platform fees. Stripe charges its standard processing fee (2.9% + $0.30). See all ticketing features."
                }
            },
            {
                "@type": "Question",
                "name": "Can I schedule recurring watch parties?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Set up weekly movie nights, monthly documentary screenings, or any recurring schedule. Viewers can follow your schedule and get notified whenever you add new screenings. Your full watch party calendar lives on one shareable page. Screenings also sync with Google Calendar."
                }
            },
            {
                "@type": "Question",
                "name": "How do viewers get the streaming link?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "When viewers register or purchase a ticket, the streaming link appears on their registration confirmation and event page. You can also email all registrants directly from your dashboard before the screening starts."
                }
            },
            {
                "@type": "Question",
                "name": "Is Event Schedule free for hosting watch parties?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule is free, open-source watch party hosting software. The free plan includes unlimited events, viewer email notifications, follower features, and Google Calendar sync. There are zero platform fees on ticket sales at any plan level. You only pay Stripe's standard processing fee if you sell tickets. You can also selfhost Event Schedule on your own server."
                }
            }
        ]
    }
    </script>

    <!-- HowTo Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "HowTo",
        "name": "How to host a watch party with Event Schedule",
        "description": "Three steps to schedule and host your watch party online.",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Create your screening",
                "text": "Add your date, streaming link, and tickets. Set up free or paid registration."
            },
            {
                "@type": "HowToStep",
                "name": "Share your link",
                "text": "Viewers register. Send viewing guides before the screening."
            },
            {
                "@type": "HowToStep",
                "name": "Watch together",
                "text": "Stream to your community. Discuss, react, and enjoy together."
            }
        ]
    }
    </script>

    <style {!! nonce_attr() !!}>
        .watchparty-glow-text {
            background: linear-gradient(135deg, #4f46e5, #06b6d4, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(79, 70, 229, 0.3);
        }
        .dark .watchparty-glow-text {
            background: linear-gradient(135deg, #818cf8, #22d3ee, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(129, 140, 248, 0.3);
        }
    </style>
</x-marketing-layout>
